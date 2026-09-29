<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public Order $order;

    public ?string $previousStatus;

    public function __construct(Order $order, ?string $previousStatus = null)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
    }

    public function build()
    {
        return $this->subject('Order '.$this->order->order_number.' status updated')
            ->view('emails.order-status-updated');
    }
}
