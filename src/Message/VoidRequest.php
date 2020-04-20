<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasAuthCode;
use Omnipay\Cathaybk\Traits\HasCallApi;
use Omnipay\Cathaybk\Traits\HasOrderNumber;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

class VoidRequest extends AbstractRequest
{
    use HasStore;
    use HasOrderNumber;
    use HasAuthCode;
    use HasSignCaValue;
    use HasAssertCaValue;
    use HasCallApi;

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('transactionId', 'transactionReference');

        return array_merge(
            ['MSGID' => 'ORD0007'],
            $this->mergeCaValue([
                'CANCELORDERINFO' => [
                    'STOREID' => $this->getStoreId(),
                    'ORDERNUMBER' => $this->getOrderNumber(),
                    'AUTHCODE' => $this->getTransactionReference(),
                ],
            ])
        );
    }

    /**
     * @param mixed $data
     * @return VoidResponse
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $returnValues = $this->callApi($data);

        $this->assertCaValue($returnValues, ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY']);

        return $this->response = new VoidResponse($this, $returnValues);
    }

    /**
     * @return string[]
     */
    protected function getSignKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY'];
    }
}
