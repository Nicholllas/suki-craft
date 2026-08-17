<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:expire-unpaid')]
#[Description('Cancel unpaid orders once their delivery time slot starts')]
class ExpireUnpaidOrders extends Command
{
    public function handle(PaymentService $paymentService): int
    {
        $expiredOrders = 0;

        Order::query()
            ->where('status', OrderStatus::PENDING_PAYMENT)
            ->whereDate('delivery_date', '<=', today())
            ->chunkById(100, function ($orders) use ($paymentService, &$expiredOrders): void {
                foreach ($orders as $order) {
                    $expiredOrders += $paymentService->expireIfPaymentDeadlinePassed($order) ? 1 : 0;
                }
            });

        $this->info("{$expiredOrders} pesanan belum dibayar dibatalkan.");

        return self::SUCCESS;
    }
}
