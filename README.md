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
	$paypalService = $this->get('tps_paypal.paypal_service');
	$transaction = new PaypalTransaction();
	$amount = new Amount();
	$amount->setTotal(number_format(180.00, 2));
	$amount->setCurrency('USD');
	$transaction->setAmount($amount);
	$transaction->setDescription('Purchase for ' . $sellingOrder->getAmount() . ' ' . $amount->getCurrency());

	$itemList = new ItemList();
	$item = new Item();
	$item->setName('My sold item')
		->setCurrency('USD')
		->setPrice($amount->getTotal())
		->setQuantity(1);

	$itemList->setItems(array($item));
	$transaction->setItemList($itemList);
	$redirectUrls = new RedirectUrls();
	$redirectUrls->setReturnUrl('http://myshop/returnAfterCheckoutUrl');
	$redirectUrls->setCancelUrl('http://myshop/cancelCheckoutUrl');
	$payment = $paypalService->setupPayment($redirectUrls, $paypalTransaction);
	$payment->create();
	save_checkout_id($payment->getId());
	redirect($transaction->getPaypalUrlApproval());
}</code></pre>

Yea, thats still a lot of code, i'm working on getting it into the bundle ^^
This will create a payment. Save the payment-id before redirecting the user, you will need this id later to actually execute the payment.

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