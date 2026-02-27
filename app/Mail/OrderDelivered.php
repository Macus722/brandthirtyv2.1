<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;

class OrderDelivered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Order is Complete - #' . $this->order->order_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_delivered',
        );
    }

    public function attachments(): array
    {
        if (!empty($this->order->report_file) && Storage::disk('public')->exists($this->order->report_file)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromStorageDisk('public', $this->order->report_file)
                    ->as('report_' . $this->order->order_id . '.' . pathinfo($this->order->report_file, PATHINFO_EXTENSION)),
            ];
        }

        return [];
    }
}
