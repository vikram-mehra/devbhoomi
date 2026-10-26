<?php

namespace App\Mail;

use App\Models\User;
use App\Support\SiteLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AbandonedCartReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $customer;

    /** @var Collection<int, array<string, mixed>> */
    public Collection $items;

    public float $subtotal;

    public ?string $logoUrl;

    public string $checkoutUrl;

    public ?float $total;

    public string $supportEmail;

    public function __construct(
        User $customer,
        Collection $items,
        float $subtotal,
        string $checkoutUrl,
        ?float $total = null
    ) {
        $this->customer = $customer;
        $this->items = $items;
        $this->subtotal = $subtotal;
        $this->logoUrl = SiteLogo::url();
        $this->checkoutUrl = $checkoutUrl;
        $this->total = $total;
        $this->supportEmail = (string) config('verification.support_email');
    }

    public function build()
    {
        $mail = $this->subject(__('You left something in your cart!'))
            ->view('emails.customer.abandoned-cart-reminder');

        if ($this->supportEmail !== '') {
            $mail->replyTo($this->supportEmail, config('app.name'));
        }

        return $mail;
    }
}
