<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 13.11.13
 * Time: 15:20
 */

namespace tps\PaypalBundle\Tests\Entity;

use tps\PaypalBundle\Entity\Payment;
use tps\PaypalBundle\Entity\TransactionItem;
use Paypal\Api\Links;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $payment = new Payment();
        $this->assertEquals('sale', $payment->getPaypalPayment()->getIntent());
        $this->assertEquals('paypal', $payment->getPaypalPayment()->getPayer()->getPaymentMethod());
    }

    public function testSetPaypalPayment()
    {
        $payment = new Payment();
        $paypalPayment = new \PayPal\Api\Payment();
        $payment->setPaypalPayment($paypalPayment);
        $this->assertEquals($paypalPayment, $payment->getPaypalPayment());
    }

    public function testAddTransactionValid()
    {
        $payment = new Payment();
        $transactions = $payment->getTransactions();
        $this->assertEmpty($transactions);
        $payment->addTransaction(array(new TransactionItem('test', 0.50, 'USD', 1)), 'USD', 'none');
        $this->assertEquals(1, count($payment->getTransactions()));
    }

    public function testAddTransactionNoItems()
    {
        $this->setExpectedException('tps\PaypalBundle\Exception\NoTransactionitemsException');
        $payment = new Payment();
        $transactions = $payment->getTransactions();
        $this->assertEmpty($transactions);
        $payment->addTransaction(array(), 'USD', 'none');
        $this->assertEquals(1, count($payment->getTransactions()));
    }

    public function testAddTransactionInvalidCurrency()
    {
        $this->setExpectedException('tps\PaypalBundle\Exception\InvalidCurrencyCodeException');
        $payment = new Payment();
        $transactions = $payment->getTransactions();
        $this->assertEmpty($transactions);
        $payment->addTransaction(array(new TransactionItem('test', 0.50, 'USD', 1)), null, 'none');
        $this->assertEquals(1, count($payment->getTransactions()));
    }

    public function testSetUrls()
    {
        $payment = new Payment();
        $payment->setUrls('http://test/return', 'http://test/cancel');
        $redirectUrls = $payment->getPaypalPayment()->getRedirectUrls();
        $this->assertEquals('http://test/return', $redirectUrls->getReturnUrl());
        $this->assertEquals('http://test/cancel', $redirectUrls->getCancelUrl());
    }

    public function testCreatePaypalPayment()
    {
        $payment = new Payment();
        $apiContext = $this->getMockBuilder('PayPal\Rest\ApiContext')->disableOriginalConstructor()->getMock();
        $payment->setApiContext($apiContext);

    }

    public function testSetApiContext()
    {
        $payment = new Payment();
        $paypalPaymentMock = $this->getMockBuilder('\PayPal\Api\Payment')->getMock();
        $apiContext = $this->getMockBuilder('PayPal\Rest\ApiContext')->disableOriginalConstructor()->getMock();
        $paypalPaymentMock->expects($this->once())
            ->method('create')
            ->with($apiContext)
            ->will($this->returnValue(true));
        $payment->setPaypalPayment($paypalPaymentMock);
        $payment->setApiContext($apiContext);
        $this->assertTrue($payment->createPaypalPayment());
    }

    public function testGetTotal()
    {
        $payment = new Payment();
        $payment->addTransaction(
            array(
                new TransactionItem('test', 0.50, 'USD', 1),
                new TransactionItem('test', 0.50, 'USD', 1)
            ),
            'USD',
            'none'
        );
        $transactions = $payment->getTransactions();
        $amount = $transactions[0]->getAmount();
        $this->assertEquals(1.00, $amount->getTotal());
    }

    public function testRedirectUrls()
    {
        $paypalPaymentMock = $this->getMockBuilder('\PayPal\Api\Payment')->getMock();
        $linkMock = $this->getMockBuilder('\PayPal\Api\Links')->getMock();
        $linkMock->expects($this->at(0))->method('getHref')->will($this->returnValue('test1'));
        $linkMock->expects($this->at(1))->method('getHref')->will($this->returnValue('test2'));
        $linkMock->expects($this->at(2))->method('getHref')->will($this->returnValue('test3'));

        $paypalPaymentMock->expects($this->exactly(3))
            ->method('getLinks')
            ->will(
                $this->returnValue(
                    array(
                        $linkMock,
                        $linkMock,
                        $linkMock
                    )
                )
            );
        $payment = new Payment();
        $payment->setPaypalPayment($paypalPaymentMock);
        $this->assertEquals('test1', $payment->getSelfUrl());
        $this->assertEquals('test2', $payment->getApprovalUrl());
        $this->assertEquals('test3', $payment->getExecuteUrl());
    }

    public function testGetCheckoutId()
    {
        $paypalPaymentMock = $this->getMockBuilder('\PayPal\Api\Payment')->getMock();
        $paypalPaymentMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(42));
        $payment = new Payment();
        $payment->setPaypalPayment($paypalPaymentMock);
        $this->assertEquals(42, $payment->getCheckoutId());
    }

    public function testGetTotalTransactionAmout()
    {
        $payment = new Payment();
        $payment->addTransaction(
            array(
                new TransactionItem('sweet', 12.00, 'USD', 2),
                new TransactionItem('sweet', 4.00, 'USD', 1)
            ), 'USD', 'test'
        );
        $payment->addTransaction(
            array(
                new TransactionItem('sweet', 12.00, 'USD', 2),
                new TransactionItem('sweet', 3.00, 'USD', 1)
            ), 'USD', 'test'
        );
        $this->assertEquals(55.00, $payment->getTotalTransactionAmout());
    }
} 