<?php
/*
 * This file is part of the Ekino Drupal package.
 *
 * (c) 2014 Ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\DrupalBundle\EventListener;

use Drupal\Core\DrupalKernelInterface;
use Ekino\Bundle\DrupalBundle\Delivery\DeliveryStrategies;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class DrupalListener
{
    /**
     * @var DrupalKernelInterface
     */
    protected $drupalKernel;

    /**
     * @var string $deliveryStrategy
     */
    protected $deliveryStrategy;

    /**
     * @param DrupalKernelInterface $drupalKernel
     * @param string                $deliveryStrategy
     */
    public function __construct(DrupalKernelInterface $drupalKernel, $deliveryStrategy = DeliveryStrategies::SYMFONY_DELIVERY_STRATEGY)
    {
        $this->drupalKernel = $drupalKernel;
        $this->drupalKernel->boot();
        $this->deliveryStrategy = $deliveryStrategy;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->deliveryStrategy !== DeliveryStrategies::SYMFONY_DELIVERY_STRATEGY) {
            return;
        }

        $exception = $event->getException();
        $request = $event->getRequest();

        // Prevent listener from processing exceptions thrown when request has been handled
        if ($exception instanceof HttpExceptionInterface && $request->attributes->get('_controller') === null) {
            $response = $this->drupalKernel->handle($event->getRequest());
            $event->setResponse($response);
        }
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Prevent the listener from trying to handle profiler toolbar request
        if ($this->deliveryStrategy !== DeliveryStrategies::DRUPAL_DELIVERY_STRATEGY
            || preg_match('/^\/_wdt\/.*/', $request->getRequestUri())
            || preg_match('/^\/_profiler\/.*/', $request->getRequestUri())
        ) {
            return;
        }

        $response = $this->drupalKernel->handle($event->getRequest());
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
