<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasAmount;
use Omnipay\Cathaybk\Traits\HasLanguage;
use Omnipay\Cathaybk\Traits\HasOrderNumber;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

class PurchaseRequest extends AbstractRequest
{
    use HasAmount;
    use HasLanguage;
    use HasOrderNumber;
    use HasSignCaValue;
    use HasStore;

    /**
     * @param  int|string  $periodNumber
     * @return AbstractRequest
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
     * @param  int|string  $installment
     * @return AbstractRequest
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
     *
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('STOREID', 'CUBKEY', 'transactionId', 'amount');

        return $this->mergeCaValue([
            'MSGID' => $this->hasPeriodNumber() ? 'TRS0005' : 'TRS0004',
            'ORDERINFO' => array_merge($this->appendPeriodNumber([
                'STOREID' => $this->getStoreId(),
                'ORDERNUMBER' => $this->getTransactionId(),
                'AMOUNT' => $this->getAmount(),
            ]), ['LANGUAGE' => $this->getLanguage()]),
        ]);
    }

    /**
     * @param  mixed  $data
     * @return PurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    /**
     * @return array
     */
    protected function getSignKeys()
    {
        return $this->hasPeriodNumber()
            ? ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'PERIODNUMBER', 'LANGUAGE', 'CUBKEY']
            : ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'LANGUAGE', 'CUBKEY'];
    }

    /**
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
