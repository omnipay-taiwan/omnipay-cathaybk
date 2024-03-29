<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasAmount;
use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasCallApi;
use Omnipay\Cathaybk\Traits\HasOrderNumber;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

class FetchTransactionRequest extends AbstractRequest
{
    use HasAmount;
    use HasAssertCaValue;
    use HasCallApi;
    use HasOrderNumber;
    use HasSignCaValue;
    use HasStore;

    /**
     * @param  array  $data
     * @return ResponseInterface
     *
     * @throws InvalidResponseException
     */
    public function sendData($data)
    {
        $returnValues = $this->callApi($data);

        $this->assertCaValue($returnValues);

        return $this->response = new FetchTransactionResponse($this, $returnValues);
    }

    /**
     * @return array
     *
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('STOREID', 'CUBKEY', 'transactionId', 'amount');

        return array_merge(
            ['MSGID' => 'ORD0001'],
            $this->mergeCaValue([
                'ORDERINFO' => [
                    'STOREID' => $this->getStoreId(),
                    'ORDERNUMBER' => $this->getTransactionId(),
                    'AMOUNT' => $this->getAmount(),
                ],
            ])
        );
    }

    /**
     * @return array
     */
    protected function getSignKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY'];
    }

    /**
     * @return string[]
     */
    protected function getAssertKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY'];
    }
}
