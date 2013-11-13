<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 13.11.13
 * Time: 15:27
 */

namespace tps\PaypalBundle\Entity;

use PayPal\Api\Item;

class TransactionItem extends Item
{
    /**
     * @param $name
     * @param float $price
     * @param string $currency
     * @param int $quantity
     */
    public function __construct($name, $price, $currency, $quantity)
    {
        $this->setName($name)
            ->setCurrency($currency)
            ->setPrice($price)
            ->setQuantity($quantity);
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return (float)$this->getPrice() * (float)$this->getQuantity();
    }
}