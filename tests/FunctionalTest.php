<?php

namespace Pacolmg\SymfonyFilterBundle\Tests;

use Pacolmg\SymfonyFilterBundle\Service\ExternalParametersService;
use Pacolmg\SymfonyFilterBundle\Service\FilterService;
use PHPUnit\Framework\TestCase;
use Pacolmg\SymfonyFilterBundle\PacolmgSymfonyFilterBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class FunctionalTest
 *
 * @package Pacolmg\SymfonyFilterBundle\Tests
 */
class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new PacolmgTestingKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->assertEquals(1,1);

        $service = $container->get('pacolmg_symfony_filter_external_parameters_service');
        $this->assertInstanceOf(ExternalParametersService::class, $service);
        $this->assertGreaterThan(0, $service->getDefaultLimit());

        $service = $container->get('pacolmg_symfony_filter_service');
        $this->assertInstanceOf(FilterService::class, $service);
    }
}

/**
 * Create an App to Test the integration of the bundle with it
 * */
class PacolmgTestingKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new PacolmgSymfonyFilterBundle(),
        ];
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}