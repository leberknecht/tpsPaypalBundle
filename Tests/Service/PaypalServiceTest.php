<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 06.10.13
 * Time: 23:01
 */

namespace tps\PaypalBundle\Tests\Services;

use PayPal\Api\RedirectUrls;
use PayPal\PayPalAPI\MassPayResponseType;
use tps\PaypalBundle\Entity\Payment;
use tps\PaypalBundle\Entity\TransactionItem;
use tps\PaypalBundle\Services\PaypalService;

class PaypalServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');
        $paypalService = new PaypalService(array(), array(), array());
        $this->assertInstanceOf('tps\PaypalBundle\Services\PaypalService', $paypalService);
    }

    public function testConstructValid()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $this->assertInstanceOf('tps\PaypalBundle\Services\PaypalService', $paypalService);
    }

    public function testInitTransaction()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $result = $paypalService->setupPayment();
        $apiContext = $result->getApiContext();
        $this->assertInstanceOf('PayPal\Common\PPApiContext', $apiContext);
        $this->assertInstanceOf('tps\PaypalBundle\Entity\Payment', $result);
    }

    public function testSendPaymentValid()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $payment->addTransaction(array(new TransactionItem('testing', 23.00, 'USD', 1)),'USD', 'testing');
        $apiMock = $this->getMockBuilder('PayPal\Service\PayPalAPIInterfaceServiceService')->getMock();
        $expected = new MassPayResponseType();
        $apiMock->expects($this->once())
            ->method('MassPay')
            ->will($this->returnValue($expected));
        $paypalService->setClassicApiInterface($apiMock);
        $result = $paypalService->sendPaymentWithMassPay($payment, 'test@test.test');
        $this->assertEquals($expected, $result);
    }

    public function testSendPaymentPaymentEmpty()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $this->setExpectedException('tps\PaypalBundle\Exception\NoTransactionException');
        $paypalService->sendPaymentWithMassPay($payment, 'test@test.test');
    }

    public function testSendPaymentRecipientEmpty()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $payment->addTransaction(array(new TransactionItem('testing', 23.00, 'USD', 1)),'USD', 'testing');
        $this->setExpectedException('InvalidArgumentException');
        $paypalService->sendPaymentWithMassPay($payment, '');
    }

    public function testCreateChainPaymentNoTransaction()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $this->setExpectedException('tps\PaypalBundle\Exception\NoTransactionException');
        $paypalService->createChainPayment($payment, 'test@test.org', 'test@test.org');
    }

    public function testCreateChainPaymentPrimaryReceipientEmpty()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $payment->addTransaction(array(new TransactionItem('testing', 23.00, 'USD', 1)),'USD', 'testing');
        $this->setExpectedException('InvalidArgumentException');
        $paypalService->createChainPayment($payment, null, 'test@test.org');
    }

    public function testCreateChainPaymentSecondaryReceipientEmpty()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $payment->addTransaction(array(new TransactionItem('testing', 23.00, 'USD', 1)),'USD', 'testing');
        $this->setExpectedException('InvalidArgumentException');
        $paypalService->createChainPayment($payment, 'test@test.org', null);
    }

    public function testCreateChainPaymentCredentialsInvalid()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $payment->addTransaction(array(new TransactionItem('testing', 23.00, 'USD', 1)),'USD', 'testing');
        $this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
        $paypalService->createChainPayment($payment, 'test@test.org', 'test@test.org');
    }

    public function testCreateChainPaymentCredentialsValid()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'),
            array(
                'acct1.UserName' => 'test',
                'acct1.Signature' => 'test234234',
                'acct1.Password' => 'testing',
                'acct1.AppId' => 'APP-80W284485P519543T'
            )
        );
        $payment = new Payment();
        $paypalPayment = new \PayPal\Api\Payment();
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setCancelUrl('http://test/cancel');
        $redirectUrls->setReturnUrl('http://test/return');
        $paypalPayment->setRedirectUrls($redirectUrls);
        $payment->setPaypalPayment($paypalPayment);
        $payment->addTransaction(array(new TransactionItem('testing', 23.00, 'USD', 1)),'USD', 'testing');
        $serviceMock = $this->getMockBuilder('AdaptivePaymentsService')->disableOriginalConstructor()->getMock();
        $response = new \stdClass();
        $response->payKey = 42;
        $serviceMock->expects($this->once())
            ->method('Pay')
            ->will($this->returnValue($response));
        $paypalService->setAdaptivePaymentsService($serviceMock);
        $ret = $paypalService->createChainPayment($payment, 'test@test.org', 'test@test.org');
        $this->assertEquals(42, $ret);
    }

    public function testExecuteTransaction()
    {
        /**
         * hard to test..damn statics :(
         */
    }
}
