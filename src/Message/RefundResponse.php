<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

class RefundResponse extends AbstractResponse
{
    private $messages = [
        'L001' => '資料格式有誤',
        'L100' => '資料格式有誤',
        'L002' => '檢核商家認證資料失敗',
        'L003' => '無符合的已請款資料',
        'L004' => '商家交易網址錯誤',
        'L005' => '退款金額有誤',
        'L006' => '今日結帳作業已完成',
        'L007' => '請洽系統管理員',
        'E001' => '系統忙碌中',
        'E002' => '系統維護中',
        'E100' => '系統維護中',
    ];

    /**
     * @var string
     */
    private $section;

    /**
     * Constructor.
     *
     * @param  RequestInterface  $request  the initiating request.
     * @param  mixed  $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        $this->section = ! $this->isCancelled() ? 'REFUNDORDERINFO' : 'CANCELREFUNDINFO';
    }

    /**
     * Is the response successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() === '0000';
    }

    /**
     * Is the transaction cancelled by the user?
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->data['CUBXML']['MSGID'] === 'ORD0004';
    }

    /**
     * Response code.
     *
     * @return null|string A response code from the payment gateway
     */
    public function getCode()
    {
        return $this->data['CUBXML'][$this->section]['STATUS'];
    }

    /**
     * Gateway Reference.
     *
     * @return null|string A reference provided by the gateway to represent this transaction
     */
    public function getTransactionReference()
    {
        return $this->data['CUBXML'][$this->section]['AUTHCODE'];
    }

    /**
     * Get the transaction ID as generated by the merchant website.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->data['CUBXML'][$this->section]['ORDERNUMBER'];
    }

    /**
     * Response Message.
     *
     * @return string A response message from the payment gateway
     */
    public function getMessage()
    {
        return array_key_exists($this->getCode(), $this->messages)
            ? $this->messages[$this->getCode()] : '未定義錯誤';
    }
}
