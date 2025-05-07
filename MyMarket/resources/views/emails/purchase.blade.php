<!-- resources/views/emails/purchase_details.blade.php -->
<html>
    <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
        <h1 style="text-align: center; color: #333;">Thank You for Your Purchase!</h1>

        <p style="text-align: center; color: #555;">Here are the details of your purchase:</p>

        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <th style="text-align: left; padding: 10px; background-color: #f8f8f8;">Product</th>
                <th style="text-align: left; padding: 10px; background-color: #f8f8f8;">Details</th>
            </tr>

            @foreach ($orders as $order)
                <tr>
                    <td style="padding: 10px; vertical-align: top;">
                        @foreach ($order['product_image'] as $image_url)
                            <img src="{{ $image_url }}" alt="{{ $order['name'] }}" style="width: 100px; height: auto; margin-right: 10px;">
                        @endforeach
                    </td>
                    <td style="padding: 10px; vertical-align: top;">
                        <strong>{{ $order['name'] }}</strong><br>
                        Quantity: {{ $order['quantity'] }}<br>
                        Size: {{ $order['size'] }}<br>
                        Color: {{ $order['color'] }}<br>
                        Retail Price: ${{ $order['retail_price'] }}<br>
                        Total Price: ${{ $order['total_price'] }}
                    </td>
                </tr>
            @endforeach
        </table>

        <p style="text-align: center; color: #555;">Thank you for shopping with us!</p>
    </body>
</html>
