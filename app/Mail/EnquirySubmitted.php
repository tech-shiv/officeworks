<?php

namespace App\Mail;

use App\Catalogue\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnquirySubmitted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name: string, email: string, phone: ?string, message: string}  $enquiry
     */
    public function __construct(
        public Product $product,
        public array $enquiry,
    ) {
        $this->replyTo($enquiry['email'], $enquiry['name']);
    }

    public function build(): self
    {
        return $this->subject("New enquiry: {$this->product->title}")
            ->view('emails.enquiry-submitted');
    }
}
