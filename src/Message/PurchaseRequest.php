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
     * @param mixed $data
     * @return PurchaseResponse|ResponseInterface
     */
    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    protected function prepareData()
    {
        return array_merge($this->appendPeriodNumber(parent::prepareData()), [
            'LANGUAGE' => strtoupper($this->getLanguage()),
            'MSGID' => $this->hasPeriodNumber() ? 'TRS0005' : 'TRS0004',
        ]);
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
     * @param array $data
     * @return array
     */
    private function appendPeriodNumber(array $data = [])
    {
        return ! $this->hasPeriodNumber() ? $data : array_merge($data, [
            'PERIODNUMBER' => $this->getPeriodNumber(),
        ]);
    }

    /**
     * @return bool
     */
    private function hasPeriodNumber()
    {
        $periodNumber = $this->getPeriodNumber();

        return $periodNumber && (int) $periodNumber > 1;
    }
}
