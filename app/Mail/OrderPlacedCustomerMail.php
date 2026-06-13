<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\SiteLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedCustomerMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public Order $order;

    public ?string $logoUrl;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->logoUrl = SiteLogo::url();
    }

    public function build()
    {
        $this->order->loadMissing(['items', 'shippingAddress', 'address', 'user']);

        return $this->subject(__('Order Confirmation - Order #:number', ['number' => $this->order->order_number]))
            ->view('emails.customer.order-confirmation');
    }
}
