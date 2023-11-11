<?php

use Omnipay\Omnipay;

$config = require __DIR__.'/config.php';
$gateway = Omnipay::create('Cathaybk');
$gateway->initialize($config);
$request = $gateway->acceptNotification();
$response = $request->send();

echo $response->getMessage();
