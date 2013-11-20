<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 06.10.13
 * Time: 23:01
 */

namespace tps\PaypalBundle\Services;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\PayPalAPI\MassPayReq;
use PayPal\PayPalAPI\MassPayRequestItemType;
use PayPal\PayPalAPI\MassPayRequestType;
use PayPal\Rest\ApiContext;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use tps\PaypalBundle\Entity\Payment as tpsPayment;
use tps\PaypalBundle\Exception\NoTransactionException;

class PaypalService
{
    /**
     * @var array
     */
    private $restConfig;

    /**
     * @var ApiContext
     */
    private $apiContext;

    /**
     * @var PayPalAPIInterfaceServiceService
     */
    private $classicApiInterface;

    /**
     * @param array $restConfig
     * @param array $apiConfig
     * @param array $classicApiConfig
     * @throws \InvalidArgumentException
     */
    public function __construct(array $restConfig, array $apiConfig, array $classicApiConfig)
    {
        if (!defined('PP_CONFIG_PATH')) {
            define('PP_CONFIG_PATH', __DIR__ . '/../Resources/config');
        }
        if (count($apiConfig) != 2) {
            throw new \InvalidArgumentException('expected $apiConfig to be array("client" => [...], "secret" => [...]');
        }
        $this->restConfig = $restConfig;
        list ($client, $secret) = array_values($apiConfig);
        $this->apiContext = new ApiContext(new OAuthTokenCredential($client, $secret));
        $this->apiContext->setConfig($restConfig);
        $this->classicApiInterface = new PayPalAPIInterfaceServiceService($classicApiConfig);
    }

    /**
     * @param PayPalAPIInterfaceServiceService $interface
     */
    public function setClassicApiInterface(PayPalAPIInterfaceServiceService $interface)
    {
        $this->classicApiInterface = $interface;
    }

    /**
     * @param string $checkoutId
     * @param string $payerId
     * @return Payment
     */
    public function executeTransaction($checkoutId, $payerId)
    {
        $payment = Payment::get($checkoutId, $this->apiContext);
        $paymentExecution = new PaymentExecution();
        $paymentExecution->setPayerId($payerId);
        return $payment->execute($paymentExecution, $this->apiContext);
    }

    /**
     * @param tpsPayment $payment
     * @param $recipientAddress
     * @throws \tps\PaypalBundle\Exception\NoTransactionException
     * @throws \InvalidArgumentException
     * @return \PayPal\PayPalAPI\MassPayResponseType
     */
    public function sendPayment(tpsPayment $payment, $recipientAddress)
    {
        if (0 == count($payment->getTransactions())) {
            throw new NoTransactionException('the payment has no transactions definied');
        }
        if (empty($recipientAddress)) {
            throw new \InvalidArgumentException('recipient is empty');
        }
        $transactions = $payment->getTransactions();
        $currency = $transactions[0]->getAmount()->getCurrency();
        $paymentValue = $payment->getTotalTransactionAmout();
        $massPayRequest = new MassPayRequestType();
        $massPayRequest->MassPayItem = array();
        $masspayItem = new MassPayRequestItemType();
        $masspayItem->Amount = new BasicAmountType($currency, $paymentValue);
        $masspayItem->ReceiverEmail = $recipientAddress;
        $massPayRequest->MassPayItem[] = $masspayItem;
        $massPayReq = new MassPayReq();
        $massPayReq->MassPayRequest = $massPayRequest;
        return $this->classicApiInterface->MassPay($massPayReq);
    }

    /**
     * @return \PayPal\Api\PaymentHistory
     */
    public function listTransactions()
    {
        return Payment::all(array('count' => 8, 'start_index' => 0), $this->apiContext);
    }

    /**
     * @param string $intent
     * @param string $paymentMethod
     * @return tpsPayment
     */
    public function setupPayment($intent = 'sale', $paymentMethod = 'paypal')
    {
        $payment = new tpsPayment($intent, $paymentMethod);
        $payment->setApiContext($this->apiContext);
        return $payment;
    }
}
