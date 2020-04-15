<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasLangParams;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

class PurchaseRequest extends AbstractPurchaseRequest
{
    use HasLangParams;

    /**
     * @param int|string $periodNumber
     * @return AbstractPurchaseRequest
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
     * @return AbstractPurchaseRequest
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
        return array_merge($this->appendPeriodNumber(parent::getData()), [
            'LANGUAGE' => strtoupper($this->getLanguage()),
        ]);
    }

    /**
     * @param mixed $data
     * @return PurchaseResponse|ResponseInterface
     */
    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    /**
     * @param array $data
     * @return array
     */
    private function appendPeriodNumber(array $data = [])
    {
        $periodNumber = $this->getPeriodNumber();
        if ($periodNumber && (int) $periodNumber > 1) {
            $data = array_merge($data, [
                'PERIODNUMBER' => $periodNumber,
            ]);
        }

        return $data;
    }
}
