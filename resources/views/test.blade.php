<?php

$stripe_secret_key = "sk_test_0QxKwa0bnx3WPPcsdJk0uzDm003rCWjviD";

\Stripe\Stripe::setApiKey($stripe_secret_key);

$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => "http://localhost/success.php",
    "cancel_url" => "http://localhost/index.php",
    "locale" => "auto",
    "line_items" => [
        [
            "quantity" => 1,
            "price_data" => [
                "currency" => "usd",
                "unit_amount" => 2000,
                "product_data" => [
                    "name" => "T-shirt"
                ]
            ]
        ],
        [
            "quantity" => 2,
            "price_data" => [
                "currency" => "usd",
                "unit_amount" => 700,
                "product_data" => [
                    "name" => "Hat"
                ]
            ]
        ]        
    ]
]);
dd($checkout_session);
http_response_code(303);
header("Location: " . $checkout_session->url);
?>

<!DOCTYPE html>
<html>

<head>
	<title>Stripe Example</title>
	<meta charset="UTF-8" />
</head>

<body>

	<h1>Stripe Example</h1>
	<form method="get" action="">
		<p>T-shirt</p>
		<p><strong>US$20.00</strong></p>
		<button>Pay</button>
	</form>
</body>

</html>