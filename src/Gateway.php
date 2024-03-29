<?php

namespace Omnipay\Cathaybk;

use Omnipay\Cathaybk\Message\AcceptNotificationRequest;
use Omnipay\Cathaybk\Message\CaptureRequest;
use Omnipay\Cathaybk\Message\CompletePurchaseRequest;
use Omnipay\Cathaybk\Message\FetchTransactionRequest;
use Omnipay\Cathaybk\Message\PurchaseRequest;
use Omnipay\Cathaybk\Message\RefundRequest;
use Omnipay\Cathaybk\Message\VoidRequest;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Message\RequestInterface;

/**
 * Cathaybk Gateway.
 *
 * This gateway is useful for testing. It implements all the functions listed in \Omnipay\Common\GatewayInterface
 * and allows both successful and failed responses based on the input.
 *
 * For authorize(), purchase(), and createCard() functions ...
 *
 *    Any card number which passes the Luhn algorithm and ends in an even number is authorized,
 *    for example: 4242424242424242
 *
 *    Any card number which passes the Luhn algorithm and ends in an odd number is declined,
 *    for example: 4111111111111111
 *
 * For capture(), completeAuthorize(), completePurchase(), refund(), and void() functions...
 *    A transactionReference option is required. If the transactionReference contains 'fail', the
 *    request fails. For any other values, the request succeeds
 *
 * For updateCard() and deleteCard() functions...
 *    A cardReference field is required. If the cardReference contains 'fail', the
 *    request fails. For all other values, it succeeds.
 *
 * ### Example
 * <code>
 * // Create a gateway for the Cathaybk Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('Cathaybk');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'testMode' => true, // Doesn't really matter what you use here.
 * ));
 *
 * // Create a credit card object
 * // This card can be used for testing.
 * $card = new CreditCard(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4242424242424242',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'amount'                   => '10.00',
 *     'currency'                 => 'AUD',
 *     'card'                     => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 *
 * @method RequestInterface authorize(array $options = [])
 * @method RequestInterface completeAuthorize(array $options = [])
 * @method RequestInterface createCard(array $options = [])
 * @method RequestInterface updateCard(array $options = [])
 * @method RequestInterface deleteCard(array $options = [])
 */
class Gateway extends AbstractGateway
{
    use HasStore;

    public function getName()
    {
        return 'Cathaybk';
    }

    public function getDefaultParameters()
    {
        return ['STOREID' => '', 'CUBKEY' => '', 'RETURL' => ''];
    }

    /**
     * @return RequestInterface
     */
    public function capture(array $options = [])
    {
        return $this->createRequest(CaptureRequest::class, $options);
    }

    /**
     * @return RequestInterface
     */
    public function purchase(array $options = [])
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    /**
     * @return RequestInterface
     */
    public function completePurchase(array $options = [])
    {
        return array_key_exists('strRsXML', $this->httpRequest->request->all())
            ? $this->acceptNotification($options)
            : $this->createRequest(CompletePurchaseRequest::class, $options);
    }

    /**
     * @return RequestInterface|NotificationInterface
     */
    public function acceptNotification(array $options = [])
    {
        return $this->createRequest(AcceptNotificationRequest::class, $options);
    }

    /**
     * @return RequestInterface
     */
    public function refund(array $options = [])
    {
        return $this->createRequest(RefundRequest::class, $options);
    }

    /**
     * @return RequestInterface
     */
    public function void(array $options = [])
    {
        return $this->createRequest(VoidRequest::class, $options);
    }

    /**
     * @return RequestInterface
     */
    public function fetchTransaction(array $options = [])
    {
        return $this->createRequest(FetchTransactionRequest::class, $options);
    }
}
