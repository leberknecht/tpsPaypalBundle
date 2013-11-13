<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 06.10.13
 * Time: 23:01
 */

namespace tps\PaypalBundle\Tests\Services;

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
        $result = $paypalService->sendPayment($payment, 'test@test.test');
        $this->assertEquals($expected, $result);
    }

    public function testSendPaymentPaymentEmpty()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $this->setExpectedException('tps\PaypalBundle\Exception\NoTransactionException');
        $paypalService->sendPayment($payment, 'test@test.test');
    }

    public function testSendPaymentRecipientEmpty()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'), array());
        $payment = new Payment();
        $payment->addTransaction(array(new TransactionItem('testing', 23.00, 'USD', 1)),'USD', 'testing');
        $this->setExpectedException('InvalidArgumentException');
        $paypalService->sendPayment($payment, '');
    }

    public function testExecuteTransaction()
    {
        /**
         * hard to test..damn statics :(
         */
    }
}
