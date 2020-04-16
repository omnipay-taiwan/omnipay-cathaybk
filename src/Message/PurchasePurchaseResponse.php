<?php

namespace Omnipay\Cathaybk\Message;

class PurchasePurchaseResponse extends AbstractPurchaseResponse
{
    /**
     * Gets the redirect target url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->request->getTestMode()
            ? 'https://sslpayment.cathaybkdev.com.tw/EPOSService/Payment/OrderInitial.aspx'
            : 'https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx';
    }

    /**
     * @return array
     */
    protected function prepareRedirectData()
    {
        return [
            'CAVALUE' => $this->data['CAVALUE'],
            'MSGID' => $this->data['MSGID'],
            'ORDERINFO' => $this->getData(),
        ];
    }
}
