<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\DelhiveryTrackingSyncService;
use Illuminate\Console\Command;

class SyncDelhiveryTrackingCommand extends Command
{
    protected $signature = 'delhivery:sync-tracking
        {--order= : Order number or id to refresh now}
        {--force : Ignore the last-attempt window}';

    protected $description = 'Poll Delhivery for in-transit orders that have not been updated recently';

    public function handle(DelhiveryTrackingSyncService $sync): int
    {
        $orderRef = trim((string) $this->option('order'));
        if ($orderRef !== '') {
            $order = Order::query()
                ->where(function ($query) use ($orderRef) {
                    $query->where('id', $orderRef)->orWhere('order_number', $orderRef);
                })
                ->first();

            if (! $order) {
                $this->error('Order not found.');

                return self::FAILURE;
            }

            if (! $order->awb()) {
                $this->warn('Order has no AWB / tracking ID.');

                return self::SUCCESS;
            }

            $result = $sync->pollOrder($order, true);
            if ($result['ok']) {
                $this->info('Tracking refreshed from Delhivery'.($result['status'] ? ': '.$result['status'] : '.'));
            } else {
                $this->warn($sync->pollErrorMessage($result['error']));
            }

            return self::SUCCESS;
        }

        $orders = $this->option('force')
            ? Order::query()
                ->whereNotNull('tracking_id')
                ->where('tracking_id', '!=', '')
                ->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
                ->limit(40)
                ->get()
            : $sync->staleInTransitOrders();

        $updated = 0;
        foreach ($orders as $order) {
            if ($sync->pollOrder($order, (bool) $this->option('force'))['ok']) {
                $updated++;
            }
        }

        $this->info('Checked '.$orders->count().' in-transit order(s); updated '.$updated.'.');

        return self::SUCCESS;
    }
}
