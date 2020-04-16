<?php

namespace Omnipay\Cathaybk\Message;

class UnionPayPurchaseResponse extends PurchaseResponse
{
    /**
     * Gets the redirect target url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return 'https://sslpayment.uwccb.com.tw/EPOSService/UPOPPayment/OrderInitial.aspx';
    }
}
