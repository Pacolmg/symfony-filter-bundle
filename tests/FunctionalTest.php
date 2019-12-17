<?php

namespace Pacolmg\SymfonyFilterBundle\Tests;

use Pacolmg\SymfonyFilterBundle\Service\ExternalParametersService;
use Pacolmg\SymfonyFilterBundle\Service\FilterService;
use PHPUnit\Framework\TestCase;
use Pacolmg\SymfonyFilterBundle\PacolmgSymfonyFilterBundle;
use Pacolmg\SymfonyFilterBundle\Repository\BaseRepository;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Mapping as ORM;

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

/**
 * @ORM\Entity(repositoryClass="PostRepository")
 */
class Post {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title ?? '';
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }
}