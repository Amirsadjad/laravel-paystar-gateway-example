<?php

namespace App\Http\Controllers\Front;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Paystar;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @return View
     */
    public function show(): View
    {
        return view('front.order.show', [
            'user' => User::first(),
            'product' => Product::first()
        ]);
    }

    /**
     * @return RedirectResponse
     */
    public function pay(): RedirectResponse
    {
        $user = User::first();

        //create a new order and payment if it doesn't exist
        $order = Order::latest()->first();
        $order = $order?->isNotPaid() ? $order : $user->addOrder();
        $payment = $order->payment ?: $order->addPayment();

        try {
            //make request to create endpoint
            $paystar = Paystar::create($payment);

            //update payment
            $payment->update(['ref_num'=>$paystar->getResponse()['data']['ref_num']]);

            //redirect user to the gateway
            return redirect()->to($paystar->gateway());
        } catch (Exception $exception) {
            return redirect()->route('orders.show')->with('message', $exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return View
     * @throws Exception
     */
    public function callback(Request $request): View
    {
        $payment = Payment::where('order_id', $request->order_id)->first();

        if (! $payment) {
            abort(404);
        }
        if ($payment->status === PaymentStatusEnum::Succeed) {
            abort(403, 'تراکنش تکراری');
        }

        $payment->addLog([
            'activity' => 'callback',
            'data' => $request->all()
        ]);

        if ($request->status != '1') {
           $message = Paystar::getStatusMessage($request->status);
           $payment->update(['status' => PaymentStatusEnum::Failed]);
        }
        else {
            try {
                $cardNumber = substr_replace(User::value('card_number'), '******', 6, 6);
                if ($request->card_number != $cardNumber) {
                    throw new Exception('شماره کارت پرداخت کننده با شماره کارت شما مغایرت دارد.');
                }

                $payment->update(['tracking_code' => $request->tracking_code]);
                //verify payment
                Paystar::verify($payment);

                //update payment
                $payment->update(['status' => PaymentStatusEnum::Succeed]);

                $message = 'پرداخت با موفقیت انجام شد' . '<br>' .
                    'شماره سفارش: ' . $payment->order_id . '<br>' .
                    'کد رهگیری: ' . $payment->tracking_code;

            } catch (Exception $exception) {
                $payment->update(['status' => PaymentStatusEnum::Failed]);
                $message = $exception->getMessage() . '<br>' .
                    'در صورتی که مبلغی از حساب شما کسر شده باشد طی ۷۲ ساعت آینده بازگردانده خواهد شد.';
            }
        }

        return view('front.order.callback', compact('message'));
    }
}
