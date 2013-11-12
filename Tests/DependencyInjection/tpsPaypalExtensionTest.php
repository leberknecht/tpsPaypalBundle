<?php

namespace tps\PaypalBundle\Tests\DependencyInjection;

use tps\PaypalBundle\DependencyInjection\tpsPaypalExtension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class tpsPaypalExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $tpsPaypalExtension = new tpsPaypalExtension();
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();
        $tpsPaypalExtension->load(array(array('log' => array('FileName' => 'test'))), $containerMock);
    }

    public function testContructLogPath()
    {
        $tpsPaypalExtension = new tpsPaypalExtension();
        $containerMock = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();
        $containerMock->expects($this->at(0))
            ->method('getParameter')
            ->with('kernel.logs_dir')
            ->will($this->returnValue('testing'));
        $containerMock->expects($this->at(1))
            ->method('setParameter')
            ->with('tps_paypal.restConfig');
        $containerMock->expects($this->at(2))
            ->method('setParameter')
            ->with('tps_paypal.apiConfig');
        $tpsPaypalExtension->load(array(array('log' => array('FileName' => 'test'))), $containerMock);
    }
}
