<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PosOrderReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $store;

    public function __construct($order, $store)
    {
        $this->order = $order;
        $this->store = $store;
        
        // FIX: Load the product and variant relationships
        // This ensures $item->product->name works in the view
        $this->order->load(['items.product', 'items.variant']);
    }

    public function build()
    {
        return $this->subject('Receipt #' . substr($this->order->id ?? '', -6))
                    ->view('emails.pos_receipt');
    }
}