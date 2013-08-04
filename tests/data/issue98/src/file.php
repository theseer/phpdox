<?php
namespace TNT\Producer;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\ConfigCache;
use TNT\Loader\TNTFileLoader;

use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

use Knp\Bundle\MenuBundle\KnpMenuBundle;

class ProducerKernel extends BaseKernel
{

public function __construct($env, $debug = false, $rootDir = __DIR__)
{
    $this->rootDir = $rootDir;
    parent::__construct($env, $debug);
}

/**
 * (non-PHPdoc)
 * @see \Symfony\Component\HttpKernel\KernelInterface::registerBundles()
 */
public function registerBundles()
{
    $bundles = array(
        new FrameworkBundle(),
        new TwigBundle(),
        new KnpMenuBundle(),
        new \Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
        new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
    );

    if ( $this->isDebug() ) {
        $bundles[] = new WebProfilerBundle();
    }
    return $bundles;
}
/**
 * Returns a loader for the container.
 *
 * @param ContainerInterface $container The service container
 *
 * @return DelegatingLoader The loader
 */
protected function getContainerLoader(ContainerInterface $container)
{
    $locator = new FileLocator($this);
    $resolver = new LoaderResolver(array(
        new TNTFileLoader($container, $locator),
        new YamlFileLoader($container, $locator),
        new XmlFileLoader($container, $locator)
    ));

    return new DelegatingLoader($resolver);
}
/**
 * (non-PHPdoc)
 * @see \Symfony\Component\HttpKernel\KernelInterface::registerContainerConfiguration()
 */
public function registerContainerConfiguration(LoaderInterface $loader)
{
    $loader->load($this->rootDir.'/projectConfig.tnt');
    $loader->load(__DIR__.'/config.xml');
    $loader->load($this->rootDir.'/gaufrette.yml');
}

/**
 * Dumps the service container to PHP code in the cache.
 *
 * @param ConfigCache      $cache     The config cache
 * @param ContainerBuilder $container The service container
 * @param string           $class     The name of the class to generate
 * @param string           $baseClass The name of the container's base class
 */
protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
{
    // cache the container
    $dumper = new PhpDumper($container);
    $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass, 'optimize_strings' => false));
    $cache->write($content, $container->getResources());
}
}


