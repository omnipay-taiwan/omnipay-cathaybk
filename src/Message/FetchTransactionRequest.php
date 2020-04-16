<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Common\Exception\InvalidRequestException;

class FetchTransactionRequest extends AbstractRequest
{
    /**
     * {@inheritdoc}
     */
    public function sendData($data)
    {
        $this->httpClient->request(
            'POST',
            'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl'
        );
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    protected function getData()
    {
        return array_merge(parent::getData(), [
            'MSGID' => 'ORDER001',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSignatureKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY'];
    }
}
