<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 13.11.13
 * Time: 15:20
 */

namespace tps\PaypalBundle\Entity;

use PayPal\Api\Amount;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction as PayPalTransaction;
use PayPal\Api\Payment as PaypalPayment;
use PayPal\Rest\ApiContext;
use tps\PaypalBundle\Exception\InvalidCurrencyCodeException;
use tps\PaypalBundle\Exception\NoTransactionItemsException;

class Payment
{
    const REDIRECT_URL_SELF_INDEX= 0;
    const REDIRECT_URL_APPROVAL_INDEX = 1;
    const REDIRECT_URL_EXECUTE_INDEX = 2;
    /**
     * @var PaypalPayment $paypalPayment
     */
    private $paypalPayment;

    /**
     * @var array
     */
    private $transactions = array();

    /**
     * @var ApiContext
     */
    private $apiContext;

    /**
     * @param string $itent
     * @param string $paymentMethod
     */
    public function __construct($itent = 'sale', $paymentMethod = 'paypal')
    {
        $this->paypalPayment = new PaypalPayment();
        $this->paypalPayment->setIntent($itent);
        $payer = new Payer();
        $payer->setPaymentMethod($paymentMethod);
        $this->paypalPayment->setPayer($payer);
    }

    /**
     * @param PaypalPayment $paypalPayment
     */
    public function setPaypalPayment(PaypalPayment $paypalPayment)
    {
        $this->paypalPayment = $paypalPayment;
    }

    /**
     * @return PaypalPayment
     */
    public function getPaypalPayment()
    {
        return $this->paypalPayment;
    }

    /**
     * @param ApiContext $apiContext
     */
    public function setApiContext(ApiContext $apiContext)
    {
        $this->apiContext = $apiContext;
    }

    /**
     * @return ApiContext
     */
    public function getApiContext()
    {
        return $this->apiContext;
    }

    /**
     * @return PayPalTransaction[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param array $items
     * @param $currency
     * @param $description
     * @throws \tps\PaypalBundle\Exception\NoTransactionItemsException
     * @throws \tps\PaypalBundle\Exception\InvalidCurrencyCodeException
     */
    public function addTransaction(array $items, $currency, $description)
    {
        if (empty($items)) {
            throw new NoTransactionItemsException();
        }
        if (empty($currency)) {
            throw new InvalidCurrencyCodeException();
        }

        $transaction = new PayPalTransaction();
        $itemList = new ItemList();
        $amount = new Amount();

        $totalAmount = $this->getTotalTransactionAmount($items);
        $amount->setTotal(number_format($totalAmount, 2));
        $amount->setCurrency($currency);

        $itemList->setItems($items);
        $transaction->setItemList($itemList);
        $transaction->setDescription($description);
        $transaction->setAmount($amount);
        $this->transactions[] = $transaction;
    }

    /**
     * @param string $returnUrl
     * @param string $cancelUrl
     */
    public function setUrls($returnUrl, $cancelUrl)
    {
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl);
        $redirectUrls->setCancelUrl($cancelUrl);
        $this->paypalPayment->setRedirectUrls($redirectUrls);
    }

    /**
     * @return PaypalPayment
     */
    public function createPaypalPayment()
    {
        $this->paypalPayment->setTransactions($this->transactions);
        return $this->paypalPayment->create($this->apiContext);
    }

    /**
     * @return string
     */
    public function getApprovalUrl()
    {
        /** @var \PayPal\Api\Links[] $redirectUrls */
        $redirectUrls = $this->paypalPayment->getLinks();
        return $redirectUrls[self::REDIRECT_URL_APPROVAL_INDEX]->getHref();
    }

    /**
     * @return string
     */
    public function getSelfUrl()
    {
        /** @var \PayPal\Api\Links[] $redirectUrls */
        $redirectUrls = $this->paypalPayment->getLinks();
        return $redirectUrls[self::REDIRECT_URL_SELF_INDEX]->getHref();
    }

    /**
     * @return string
     */
    public function getExecuteUrl()
    {
        /** @var \PayPal\Api\Links[] $redirectUrls */
        $redirectUrls = $this->paypalPayment->getLinks();
        return $redirectUrls[self::REDIRECT_URL_EXECUTE_INDEX]->getHref();
    }

    /**
     * @param TransactionItem[] $items
     * @return int
     */
    private function getTotalTransactionAmount(array $items)
    {
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item->getTotal();
        }
        return $totalAmount;
    }

    /**
     * @return string
     */
    public function getCheckoutId()
    {
        return $this->paypalPayment->getId();
    }
}