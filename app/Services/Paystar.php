<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Paystar
{
    /**
     * @var array
     */
    private array $response;

    private function __construct(){}

    /**
     * @param Payment $payment
     * @return Paystar
     * @throws Exception
     */
    public static function create(Payment $payment): Paystar
    {
        $paystar = new Paystar();

        try {
            $paystar->response = Http::withToken(config('paystar.gateway_id'))
                ->withBody(
                    content: json_encode($payment->only(['amount', 'order_id']) + [
                            'callback' => route('api.orders.callback'),
                            'sign' => self::generateSign('create', $payment),
                        ]),
                    contentType: 'application/json')
                ->post(config('paystar.url') . '/create')
                ->json();
        } catch (Exception $ignored) {
            throw new Exception('خطا در برقراری ارتباط');
        }

        $payment->addLog([
            'activity' => 'create',
            'data' => $paystar->getResponse()
        ]);

        if ($paystar->response['status'] != '1') {
            throw new Exception(self::getStatusMessage($paystar->response['status']));
        }

        return $paystar;
    }

    /**
     * @return string
     */
    public function gateway(): string
    {
        return config('paystar.url').'/payment?token='.$this->response['data']['token'];
    }

    /**
     * @param Payment $payment
     * @return void
     * @throws Exception
     */
    public static function verify(Payment $payment): void
    {
        try {
            $response = Http::withToken(config('paystar.gateway_id'))->timeout(15)
                ->withBody(
                    content: json_encode($payment->only(['amount', 'ref_num']) + [
                            'sign' => self::generateSign('verify', $payment)
                        ]),
                    contentType: 'application/json')
                ->post(config('paystar.url') . '/verify')
                ->json();
        } catch (Exception $ignored) {
            throw new Exception('خطا در برقراری ارتباط');
        }

        $payment->addLog([
            'activity' => 'verify',
            'data' => $response
        ]);

        if (! in_array($response['status'], ['1', '-6'])) {
            throw new Exception(self::getStatusMessage($response['status']));
        }
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public static function getStatusMessage(string $status)
    {
        return match ($status) {
            '-1' => 'درخواست نامعتبر (خطا در پارامترهای ورودی)',
            '-2' => 'درگاه فعال نیست',
            '-3' => 'توکن تکراری است',
            '-4' => 'مبلغ بیشتر از سقف مجاز درگاه است',
            '-5' => 'شناسه ref_num معتبر نیست',
            '-6' => 'تراکنش قبلا وریفای شده است',
            '-7' => 'پارامترهای ارسال شده نامعتبر است',
            '-8' => 'تراکنش را نمیتوان وریفای کرد',
            '-9' => 'تراکنش وریفای نشد',
            '-98' => 'تراکنش ناموفق',
            '-99' => 'خطای سامانه',
            default => 'خطایی رخ داده است'
        };
    }

    /**
     * @param string $type
     * @param Payment $payment
     * @return string
     */
    private static function generateSign(string $type, Payment $payment): string
    {
        $cardNumber = substr_replace(User::value('card_number'), '******', 6, 6);
        $data = $type === 'create'
            ? ($payment->amount.'#'.$payment->order_id.'#'.route('api.orders.callback'))
            : ($payment->amount.'#'.$payment->ref_num.'#'.$cardNumber.'#'.$payment->tracking_code);

        return hash_hmac(algo: 'sha512', data: $data, key: config('paystar.encryption_key'));
    }
}
