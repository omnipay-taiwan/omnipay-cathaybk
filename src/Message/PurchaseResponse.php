<?php

namespace Omnipay\Cathaybk\Message;

class PurchaseResponse extends AbstractPurchaseResponse
{
    /**
     * Gets the redirect target url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return 'https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx';
    }

    /**
     * @return array
     */
    protected function prepareRedirectData()
    {
        return [
            'CAVALUE' => $this->data['CAVALUE'],
            'MSGID' => $this->data['MSGID'],
            'ORDERINFO' => $this->getData()
        ];
    }
}
