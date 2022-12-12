<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Services\Paystar;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['user_id', 'product_id'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasOne
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * @return PaymentStatusEnum
     */
    public function getStatusAttribute(): PaymentStatusEnum
    {
        return $this->payment->status;
    }

    /**
     * @return bool
     */
    public function isNotPaid(): bool
    {
        return $this->status !== PaymentStatusEnum::Succeed;
    }

    public function addPayment(): Payment
    {
        return $this->payment()->Create([
            'user_id' => User::value('id'),
            'amount' => Product::value('price'),
        ]);
    }
}
