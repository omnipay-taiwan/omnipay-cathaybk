<?php

namespace Omnipay\Cathaybk\Traits;

use Omnipay\Cathaybk\Support\Helper;

trait HasCallApi
{
    /**
     * @return array
     */
    public function callApi(array $data)
    {
        $url = 'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl';
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $body = http_build_query(['strRqXML' => Helper::array2xml($data)]);
        $response = $this->httpClient->request('POST', $url, $headers, $body);

        return Helper::xml2array((string) $response->getBody());
    }
}
