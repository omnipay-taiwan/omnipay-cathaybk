<?php

use Omnipay\Omnipay;

$config = require __DIR__.'/config.php';
$gateway = Omnipay::create('Cathaybk');
$gateway->initialize($config);
$notification = $gateway->acceptNotification($_POST);
