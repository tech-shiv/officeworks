<?php

namespace App\Http\Controllers;

use App\Catalogue\Contracts\ProductProvider;
use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\EnquirySubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request, ProductProvider $products): RedirectResponse
    {
        $product = $products->find((int) $request->validated('product_id'));

        if (! $product) {
            throw new NotFoundHttpException('The product you enquired about was not found.');
        }

        try {
            /** @var array{name: string, email: string, phone: string|null, message: string} $enquiry */
            $enquiry = $request->safe()->only(['name', 'email', 'phone', 'message']);

            Mail::to(config('catalogue.sales_inbox'))->send(new EnquirySubmitted($product, $enquiry));
        } catch (\Throwable $e) {
            Log::error('Failed to send enquiry email', ['error' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'message' => 'We could not send your enquiry right now. Please try again shortly.',
            ]);
        }

        return back();
    }
}
