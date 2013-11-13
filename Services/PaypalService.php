<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 06.10.13
 * Time: 23:01
 */

namespace tps\PaypalBundle\Services;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction as PaypalTransaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use tps\PaypalBundle\Entity\Payment as tpsPayment;

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
     * @param array $restConfig
     * @param array $apiConfig
     * @throws \InvalidArgumentException
     */
    public function __construct(array $restConfig, array $apiConfig)
    {
        if (!defined('PP_CONFIG_PATH')) {
            define('PP_CONFIG_PATH', __DIR__ . '/../Resources/config');
        }
        if (count($apiConfig) != 2) {
            throw new \InvalidArgumentException('expected $apiConfig to be array("client" => [...], "secret" => [...]');
        }
        $this->restConfig = $restConfig;
        list ($this->client, $this->secret) = array_values($apiConfig);
        $this->apiContext = new ApiContext(new OAuthTokenCredential($this->client, $this->secret));
        $this->apiContext->setConfig($restConfig);
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
