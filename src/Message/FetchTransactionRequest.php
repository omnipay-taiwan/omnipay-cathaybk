<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasOrderNumber;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

class FetchTransactionRequest extends AbstractRequest
{
    use HasStore;
    use HasOrderNumber;
    use HasSignCaValue;
    use HasAssertCaValue;

    /**
     * @param mixed $data
     * @return ResponseInterface|void
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $url = 'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl';
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $body = http_build_query(['strRqXML' => Helper::array2xml($data)]);
        $response = $this->httpClient->request('POST', $url, $headers, $body);

        $data = Helper::xml2array($response->getBody()->getContents());

        $this->assertCaValue($data, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY']);

        return $this->response = new CompleteFetchTransactionResponse($this, $data);
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('transactionId', 'amount');

        return array_merge(
            ['MSGID' => 'ORD0001'],
            $this->mergeCaValue([
                'ORDERINFO' => [
                    'STOREID' => $this->getStoreId(),
                    'ORDERNUMBER' => strtoupper($this->getOrderNumber() ?: uniqid()),
                    'AMOUNT' => (int) $this->getAmount(),
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
}
