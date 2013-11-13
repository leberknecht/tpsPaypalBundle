<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 13.11.13
 * Time: 15:27
 */

namespace tps\PaypalBundle\Entity;

class TransactionItemTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $transactionItem = new TransactionItem('test', 1.2, 'TESTDOLLAR', 42);
        $this->assertEquals('test', $transactionItem->getName());
        $this->assertEquals(1.2, $transactionItem->getPrice());
        $this->assertEquals('TESTDOLLAR', $transactionItem->getCurrency());
        $this->assertEquals(42, $transactionItem->getQuantity());
    }

    public function testGetTotal()
    {
        $transactionItem = new TransactionItem('test', 1.2, 'TESTDOLLAR', 42);
        $this->assertEquals(50.4, $transactionItem->getTotal());
    }
}