<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 06.10.13
 * Time: 23:01
 */

namespace tps\PaypalBundle\Services;

use AdaptivePaymentsService;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Core\PPCredentialManager;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\PayPalAPI\MassPayReq;
use PayPal\PayPalAPI\MassPayRequestItemType;
use PayPal\PayPalAPI\MassPayRequestType;
use PayPal\Rest\ApiContext;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayRequest;
use Receiver;
use RequestEnvelope;
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
     * @var \AdaptivePaymentsService
     */
    private $adaptivePaymentsService;

    /**
     * @var array
     */
    private $classicApiConfig;

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
        $this->classicApiConfig = $classicApiConfig;
        $this->restConfig = $restConfig;
        list ($client, $secret) = array_values($apiConfig);

        $this->apiContext = new ApiContext(new OAuthTokenCredential($client, $secret));
        $this->apiContext->setConfig($restConfig);
        $this->classicApiInterface = new PayPalAPIInterfaceServiceService($classicApiConfig);
        $this->setAdaptivePaymentsService(new AdaptivePaymentsService($classicApiConfig));
    }

    /**
     * @param PayPalAPIInterfaceServiceService $interface
     */
    public function setClassicApiInterface(PayPalAPIInterfaceServiceService $interface)
    {
        $this->classicApiInterface = $interface;
    }

    /**
     * @param \AdaptivePaymentsService $adaptivePaymentsService
     */
    public function setAdaptivePaymentsService($adaptivePaymentsService)
    {
        $this->adaptivePaymentsService = $adaptivePaymentsService;
    }

    /**
     * @return \AdaptivePaymentsService
     */
    public function getAdaptivePaymentsService()
    {
        return $this->adaptivePaymentsService;
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
     * @param string $primaryReceiverAccount
     * @param string $secondaryReceiverAccount
     * @return string
     * @throws \tps\PaypalBundle\Exception\NoTransactionException
     * @throws \InvalidArgumentException
     */
    public function createChainPayment(tpsPayment $payment, $primaryReceiverAccount, $secondaryReceiverAccount)
    {
        if (0 == count($payment->getTransactions())) {
            throw new NoTransactionException('the payment has no transactions definied');
        }
        if (empty($primaryReceiverAccount) || empty($secondaryReceiverAccount)) {
            throw new \InvalidArgumentException('primary/secondary recipient is empty');
        }

        $PPCredentialManager = PPCredentialManager::getInstance($this->classicApiConfig);
        $credentials = $PPCredentialManager->getCredentialObject();

        $receiverList = new \ReceiverList(array(
                $this->constructReceiver($payment, $secondaryReceiverAccount),
                $this->constructReceiver($payment, $primaryReceiverAccount, true)
            )
        );

        $redirectUrls = $payment->getPaypalPayment()->getRedirectUrls();
        $payRequest = new PayRequest(
            new RequestEnvelope("en_US"),
            'PAY',
            $redirectUrls->getCancelUrl(),
            $this->getCurrencyFromPayment($payment),
            $receiverList,
            $redirectUrls->getReturnUrl()
        );

        $payRequest->feesPayer = 'SECONDARYONLY';
        $payRequest->reverseAllParallelPaymentsOnError = false;

        $response = $this->adaptivePaymentsService->Pay($payRequest, $credentials);
        return $response->payKey;
    }

    /**
     * @param tpsPayment $payment
     * @param $recipientAddress
     * @throws \tps\PaypalBundle\Exception\NoTransactionException
     * @throws \InvalidArgumentException
     * @return \PayPal\PayPalAPI\MassPayResponseType
     */
    public function sendPaymentWithMassPay(tpsPayment $payment, $recipientAddress)
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
        $massPayRequest = $this->getMassPaymentRequest($recipientAddress, $currency, $paymentValue);
        $massPayReq = new MassPayReq();
        $massPayReq->MassPayRequest = $massPayRequest;
        return $this->classicApiInterface->MassPay($massPayReq);
    }

    /**
     * @param tpsPayment $payment
     * @return string
     */
    private function getCurrencyFromPayment(tpsPayment $payment)
    {
        $transactions = $payment->getTransactions();
        $items = $transactions[0]->getItemList()->getItems();
        return $items[0]->getCurrency();
    }

    /**
     * @param tpsPayment $payment
     * @param string $recipientAddress
     * @param bool $primary
     * @return Receiver
     */
    private function constructReceiver(tpsPayment $payment, $recipientAddress, $primary = false)
    {
        $receiver = new Receiver();
        $receiver->email = $recipientAddress;
        $receiver->amount = $payment->getTotalTransactionAmout();
        $receiver->primary = $primary;
        $receiver->invoiceId = $payment->getOrderId();
        return $receiver;
    }

    /**
     * @return \PayPal\Api\PaymentHistory
     */
    public function listTransactions()
    {
        return Payment::all(array('count' => 1, 'start_index' => 2), $this->apiContext);
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

    /**
     * @param string $recipientAddress
     * @param string$currency
     * @param float $paymentValue
     * @return MassPayRequestItemType
     */
    private function getMassPaymentItem($recipientAddress, $currency, $paymentValue)
    {
        $masspayItem = new MassPayRequestItemType();
        $masspayItem->Amount = new BasicAmountType($currency, $paymentValue);
        $masspayItem->ReceiverEmail = $recipientAddress;
        return $masspayItem;
    }

    /**
     * @param string $recipientAddress
     * @param string $currency
     * @param float $paymentValue
     * @return MassPayRequestType
     */
    private function getMassPaymentRequest($recipientAddress, $currency, $paymentValue)
    {
        $massPayRequest = new MassPayRequestType();
        $massPayRequest->MassPayItem = array(
            $this->getMassPaymentItem($recipientAddress, $currency, $paymentValue)
        );
        return $massPayRequest;
    }
}
