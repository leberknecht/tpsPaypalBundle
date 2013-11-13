tpsPaypalBundle
================================

This bundle intends to be a wrapper for both the RESTful-API and the classic-API.
Currently in progress ^^ 
What we've got so far:

Installation
------------
Add the following line to your composer.json:

<pre><code>require: "tps/paypal-bundle": "dev-master"</code></pre>

And run composer install

Configuration
-------------
<pre><code>
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
</pre></code>

Usage
-----
### Checkout

<pre><code>public function checkoutAction()
{
	$returnUrl = 'http://myapp/success';
	$cancelUrl = ''http://myapp/cancel/order123';

    $payment = $this->get('tps_paypal.paypal_service')->setupPayment();
    $orderItems = array(
        new TransactionItem('Something sold', 18.99, 'USD', 1)
    );
    $payment->addTransaction($orderItems, 'USD', 'Order no. 123');

    $payment->setUrls($returnUrl, $cancelUrl);
    $payment->createPaypalPayment();
	save_checkout_id($payment->getCheckoutId());

	redirect($payment->getApprovalUrl());
}</code></pre>

This will create a payment. Save the payment-id before redirecting the user, you will need this id later to actually execute the payment.
Note: you can create an instance of "tps\PaypalBundle\Entity\Payment" by yourself instead of calling the service,
but if you do, you will have to care about the API context yourself

### transaction execution
<pre><code>public function returnAfterCheckoutUrlAction()
{
	$checkoutId = load_checkoutId();
	$paypalService = $this->get('tps_paypal.paypal_service');
	$paypalService->executeTransaction($checkoutId, $payerId);
}</code></pre>

### list payments
<pre><ocde>public function paypalOverviewAction()
{
	$transactions = $this->paypalService->listTransactions();
	return $this->render('Acme:PaypalAdmin:overview.html.twig',
		array('payments' => $transactions->getPayments())
	);
}</pre></code>

Next steps
----------
- Nicing the part
- Classic-API MassPayment support

 [![Build Status](https://travis-ci.org/leberknecht/tpsPaypalBundle.png)](https://travis-ci.org/leberknecht/tpsPaypalBundle)