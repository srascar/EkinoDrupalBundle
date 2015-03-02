<?php

/*
 * This file is part of the Ekino Drupal package.
 *
 * (c) 2011 Ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\DrupalBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Definition\Processor;
use Ekino\Bundle\DrupalBundle\Delivery\DeliveryStrategies;

/**
 * @author Thomas Rabaix <thomas.rabaix@ekino.com>
 */
class EkinoDrupalExtension extends Extension
{
    /**
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('listeners.xml');

        $deliveryStrategy = $config['delivery_strategy'];

        if ($deliveryStrategy !== DeliveryStrategies::DRUPAL_DELIVERY_STRATEGY && $deliveryStrategy !== DeliveryStrategies::SYMFONY_DELIVERY_STRATEGY) {
            $invalidConfigurationException = new InvalidConfigurationException(
                sprintf('Invalid delivery strategy %s provided for "ekino_drupal" configuration.', $deliveryStrategy)
            );
            $invalidConfigurationException->addHint(
                sprintf(
                    'allowed strategies are %s and %s',
                    DeliveryStrategies::SYMFONY_DELIVERY_STRATEGY,
                    DeliveryStrategies::DRUPAL_DELIVERY_STRATEGY
                )
            );

            throw $invalidConfigurationException;
        }

        $container->setParameter('ekino_drupal_delivery_strategy', $deliveryStrategy);
    }
}