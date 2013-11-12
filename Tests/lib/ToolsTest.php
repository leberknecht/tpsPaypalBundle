<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 12.11.13
 * Time: 19:54
 */

namespace tps\PaypalBundle\Tests\lib;

use tps\PaypalBundle\lib\Tools;

class ToolsTest extends \PHPUnit_Framework_TestCase
{
    public function testArraySliceAssoc()
    {
        $arr = array('test' => 23, 'test2' => 42, 'sweet' => 'onion');
        $arr = Tools::arraySliceAssoc($arr, array('test2', 'sweet'));
        $this->assertEquals(array('test2' => 42, 'sweet' => 'onion'), $arr);
    }

    public function testArraySliceAssocMoreKeys()
    {
        $arr = array('test' => 1, 'test2' => array('dudel', 'kitty'), 'sweet' => 'onion');
        $arr = Tools::arraySliceAssoc($arr, array('test2', 'sweet'));
        $this->assertEquals(array('test2' => array('dudel', 'kitty'), 'sweet' => 'onion'), $arr);
    }

    public function testArraySliceAssocNumericKeys()
    {
        $arr = array(1 => 'test', 'test2' => array('dudel', 'kitty'), 'sweet' => 'onion');
        $arr = Tools::arraySliceAssoc($arr, array(1, 'test2', 'sweet'));
        $this->assertEquals(array(1 => 'test', 'test2' => array('dudel', 'kitty'), 'sweet' => 'onion'), $arr);
    }

    public function testFlatenArraySimple()
    {
        $arr = array(
            'a' => 1,
            'b' => 2,
            'c' => array(
                'a' => 1,
                'b' => 2
            )
        );
        $arr = Tools::flatenArray($arr, '.');
        $this->assertEquals(array('a' => 1, 'b' => 2, 'c.a' => 1, 'c.b' => 2), $arr);
    }

    public function testFlatenArrayComplex()
    {
        $arr = array(
            'a' => 1,
            'b' => array(
                'a' => 1,
                'b' => array('sweet' => 'testing', 'onion' => 'wings'),
                'c' => 'tasty'
            )
        );
        $arr = Tools::flatenArray($arr, '.');
        $this->assertEquals(
            array(
                'a' => 1,
                'b.a' => 1,
                'b.b.sweet' => 'testing',
                'b.b.onion' => 'wings',
                'b.c' => 'tasty'
            ), $arr
        );
    }
} 