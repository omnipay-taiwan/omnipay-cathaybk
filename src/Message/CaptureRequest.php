<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasAmount;
use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasAuthCode;
use Omnipay\Cathaybk\Traits\HasCallApi;
use Omnipay\Cathaybk\Traits\HasCancel;
use Omnipay\Cathaybk\Traits\HasOrderNumber;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractRequest;

class CaptureRequest extends AbstractRequest
{
    use HasAmount;
    use HasAssertCaValue;
    use HasAuthCode;
    use HasCallApi;
    use HasCancel;
    use HasOrderNumber;
    use HasSignCaValue;
    use HasStore;

    /**
     * @return array
     *
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('STOREID', 'CUBKEY', 'transactionId', 'transactionReference');

        $isRequest = $this->isRequest();
        $section = $isRequest ? 'CAPTUREORDERINFO' : 'CANCELCAPTUREINFO';

        return array_merge(
            ['MSGID' => $isRequest ? 'ORD0005' : 'ORD0006'],
            $this->mergeCaValue([
                $section => [
                    'STOREID' => $this->getStoreId(),
                    'ORDERNUMBER' => $this->getTransactionId(),
                    'AMOUNT' => $this->getAmount(),
                    'AUTHCODE' => $this->getTransactionReference(),
                ],
            ])
        );
    }

    /**
     * @param  mixed  $data
     * @return CaptureResponse
     *
     * @throws InvalidResponseException
     */
    public function sendData($data)
    {
        $returnValues = $this->callApi($data);

        $this->assertCaValue($returnValues);

        return $this->response = new CaptureResponse($this, $returnValues);
    }

    protected function getSignKeys()
    {
        return $this->isRequest()
            ? ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHCODE', 'CUBKEY']
            : ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY'];
    }

    protected function getAssertKeys()
    {
        return $this->isRequest()
            ? ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY']
            : ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY'];
    }

    /**
     * @return bool
     */
    private function isRequest()
    {
        return ! $this->getCancel();
    }
}
