<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1b1b18;">
    <h1 style="font-size: 18px;">New product enquiry</h1>

    <p><strong>Product:</strong> {{ $product->title }} (#{{ $product->id }})</p>
    <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>

    <hr>

    <p><strong>From:</strong> {{ $enquiry['name'] }} &lt;{{ $enquiry['email'] }}&gt;</p>
    @if (!empty($enquiry['phone']))
        <p><strong>Phone:</strong> {{ $enquiry['phone'] }}</p>
    @endif

    <p><strong>Message:</strong></p>
    <p>{{ $enquiry['message'] }}</p>
</body>
</html>
