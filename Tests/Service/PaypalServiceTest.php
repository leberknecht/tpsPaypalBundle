<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 06.10.13
 * Time: 23:01
 */

namespace tps\PaypalBundle\Tests\Services;

use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use tps\PaypalBundle\Services\PaypalService;

class PaypalServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');
        $paypalService = new PaypalService(array(), array());
        $this->assertInstanceOf('tps\PaypalBundle\Services\PaypalService', $paypalService);
    }

    public function testConstructValid()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'));
        $this->assertInstanceOf('tps\PaypalBundle\Services\PaypalService', $paypalService);
    }

    public function testInitTransaction()
    {
        $paypalService = new PaypalService(array(), array('testing', 'test'));
        $result = $paypalService->setupPayment();
        $apiContext = $result->getApiContext();
        $this->assertInstanceOf('PayPal\Common\PPApiContext', $apiContext);
        $this->assertInstanceOf('tps\PaypalBundle\Entity\Payment', $result);
    }

    public function testExecuteTransaction()
    {
        /**
         * hard to test..damn statics :(
         */
    }
}
