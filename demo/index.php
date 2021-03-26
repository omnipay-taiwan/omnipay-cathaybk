<?php

use Omnipay\Omnipay;

$config = require __DIR__.'/config.php';
$gateway = Omnipay::create('Cathaybk');
$gateway->initialize($config);
$request = $gateway->purchase([
    'amount' => 1000,
    'transaction_id' => uniqid('', true),
]);

$response = $request->send();

require __DIR__.'/response.php';
