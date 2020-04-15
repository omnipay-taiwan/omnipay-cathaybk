<?php

namespace Omnipay\Cathaybk\Message;

class UnionPayPurchaseResponse extends AbstractPurchaseResponse
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

    /**
     * @return array
     */
    protected function getSignatureKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY'];
    }
}
