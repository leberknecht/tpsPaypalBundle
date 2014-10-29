[![Build Status](https://travis-ci.org/leberknecht/tpsPaypalBundle.png)](https://travis-ci.org/leberknecht/tpsPaypalBundle)
[![Coverage Status](https://coveralls.io/repos/leberknecht/tpsPaypalBundle/badge.png?branch=master)](https://coveralls.io/r/leberknecht/tpsPaypalBundle?branch=master)

tpsPaypalBundle
================================

This bundle intends to be a wrapper for both the RESTful-API and the classic-API.
Currently in progress ^^
What we've got so far:

Installation
------------
Add the following line to your composer.json:

```yaml
require: "tps/paypal-bundle": "dev-master"
```

And run composer install

Configuration
-------------
```yaml
tps_paypal:
    client: HjaksuIHAsuhhamisecretKLJSisduijhdfJKHsdhiohdjklsjd90sdfjsdj
    secret: KLJsd9f0jfiammuchmoresecretindeedKJLSKdjs890dfjij2309sdujifj
    mode: live
    http:
        ConnectionTimeOut: 30
        Retry: 1
    log:
        LogEnabled: true
        FileName: PayPal.log
        LogLevel:  FINE
    classic_api:
        acct1:
            Username: yourPayPaylClassicApiUser
            Password: yourPayPaylClassicApiPass
            Signature: yourPayPaylClassicApiSignature
        mode: live
```

Usage
-----

### Checkout

```php
public function checkoutAction()
{
    $returnUrl = 'http://myapp/success';
    $cancelUrl = 'http://myapp/cancel/order123';

    $payment = $this->get('tps_paypal.paypal_service')->setupPayment();
    $orderItems = array(
        new TransactionItem('Something sold', 18.99, 'USD', 1)
    );
    $payment->addTransaction($orderItems, 'USD', 'Order no. 123');

    $payment->setUrls($returnUrl, $cancelUrl);
    $payment->createPaypalPayment();
    save_checkout_id($payment->getCheckoutId());

    redirect($payment->getApprovalUrl());
}
```



This will create a payment. Save the payment-id before redirecting the user, you will need this id later to actually execute the payment.
Note: you can create an instance of "tps\PaypalBundle\Entity\Payment" by yourself instead of calling the service,
but if you do, you will have to care about the API context yourself

### Transaction execution
```php
public function returnAfterCheckoutUrlAction()
{
	$checkoutId = load_checkoutId(); //to be implemented elsewhere
	$paypalService = $this->get('tps_paypal.paypal_service');
	$paypalService->executeTransaction($checkoutId, $payerId);
}
```

### list payments
```php
public function paypalOverviewAction()
{
	$transactions = $this->paypalService->listTransactions();
	return $this->render('Acme:PaypalAdmin:overview.html.twig',
		array('payments' => $transactions->getPayments())
	);
}
```

Next steps
----------
- Nicing the part
