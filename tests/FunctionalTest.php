<?php

namespace Pacolmg\SymfonyFilterBundle\Tests;

use Pacolmg\SymfonyFilterBundle\Service\externalParametersService;
use PHPUnit\Framework\TestCase;
use Pacolmg\SymfonyFilterBundle\PacolmgSymfonyFilterBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
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

        $filterService = $container->get('pacolmg_symfony_filter_external_parameters_service');
        $this->assertInstanceOf(externalParametersService::class, $filterService);
        $this->assertGreaterThan(0, $filterService->getDefaultLimit());
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