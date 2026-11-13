<?php

use App\Catalogue\Contracts\ProductProvider;
use App\Mail\EnquirySubmitted;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakeProductProvider;

beforeEach(function () {
    $this->app->bind(ProductProvider::class, FakeProductProvider::class);
});

it('emails the sales inbox and redirects back on a valid enquiry', function () {
    Mail::fake();
    config(['catalogue.sales_inbox' => 'sales@officeworks.test']);

    $response = $this->from('/products/1')->post('/enquiries', [
        'product_id' => 1,
        'name' => 'Jane Buyer',
        'email' => 'jane@example.com',
        'phone' => '0400000000',
        'message' => 'Is this in stock?',
    ]);

    $response->assertRedirect('/products/1');
    $response->assertSessionDoesntHaveErrors();

    Mail::assertSent(EnquirySubmitted::class, function (EnquirySubmitted $mail) {
        return $mail->hasTo('sales@officeworks.test')
            && $mail->product->id === 1
            && $mail->enquiry['email'] === 'jane@example.com';
    });
});

it('does not send mail and shows validation errors for an invalid enquiry', function () {
    Mail::fake();

    $response = $this->from('/products/1')->post('/enquiries', [
        'product_id' => 1,
        'name' => '',
        'email' => 'not-an-email',
        'message' => '',
    ]);

    $response->assertRedirect('/products/1');
    $response->assertSessionHasErrors(['name', 'email', 'message']);

    Mail::assertNothingSent();
});

it('returns 404 when enquiring about an unknown product', function () {
    Mail::fake();

    $response = $this->post('/enquiries', [
        'product_id' => 999,
        'name' => 'Jane Buyer',
        'email' => 'jane@example.com',
        'message' => 'Is this in stock?',
    ]);

    $response->assertNotFound();
    Mail::assertNothingSent();
});

it('shows an error and does not claim success when mail dispatch fails', function () {
    Mail::shouldReceive('to->send')->andThrow(new RuntimeException('SMTP down'));

    $response = $this->from('/products/1')->post('/enquiries', [
        'product_id' => 1,
        'name' => 'Jane Buyer',
        'email' => 'jane@example.com',
        'message' => 'Is this in stock?',
    ]);

    $response->assertRedirect('/products/1');
    $response->assertSessionHasErrors(['message']);
});
