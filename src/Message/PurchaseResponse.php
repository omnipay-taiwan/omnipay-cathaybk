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
    protected function getSignatureKeys()
    {
        return $this->hasPeriodNumber()
            ? ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'PERIODNUMBER', 'LANGUAGE', 'CUBKEY']
            : ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'LANGUAGE', 'CUBKEY'];
    }

    /**
     * @return array
     */
    protected function getMsgId()
    {
        return ['MSGID' => $this->hasPeriodNumber() ? 'TRS0005' : 'TRS0004'];
    }

    /**
     * @return bool
     */
    private function hasPeriodNumber()
    {
        return array_key_exists('PERIODNUMBER', $this->data) && (int) $this->data['PERIODNUMBER'] > 1;
    }
}
