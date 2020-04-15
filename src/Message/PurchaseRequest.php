<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasLangParams;
use Omnipay\Cathaybk\Traits\HasStoreParams;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

class PurchaseRequest extends AbstractRequest
{
    use HasStoreParams;
    use HasLangParams;

    /**
     * @param string $orderNumber
     * @return PurchaseRequest
     */
    public function setOrderNumber($orderNumber)
    {
        return $this->setTransactionId($orderNumber);
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->getTransactionId();
    }

    /**
     * @param int|string $periodNumber
     * @return PurchaseRequest
     */
    public function setPeriodNumber($periodNumber)
    {
        return $this->setParameter('period_number', $periodNumber);
    }

    /**
     * @return int|string
     */
    public function getPeriodNumber()
    {
        return $this->getParameter('period_number');
    }

    /**
     * @param int|string $installment
     * @return PurchaseRequest
     */
    public function setInstallment($installment)
    {
        return $this->setParameter('period_number', $installment);
    }

    /**
     * @return int|string
     */
    public function getInstallment()
    {
        return $this->getParameter('period_number');
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('store_id', 'cub_key', 'amount', 'language');

        $data = [
            'STOREID' => $this->getStoreId(),
            'CUBKEY' => $this->getCubKey(),
            'ORDERNUMBER' => $this->getOrderNumber() ?: uniqid(),
            'AMOUNT' => $this->getAmount(),
            'LANGUAGE' => strtoupper($this->getLanguage()),
        ];

        $periodNumber = $this->getPeriodNumber();
        if ($periodNumber && (int) $periodNumber > 1) {
            $data = array_merge($data, [
                'PERIODNUMBER' => $periodNumber,
            ]);
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @return PurchaseResponse|ResponseInterface
     */
    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}
