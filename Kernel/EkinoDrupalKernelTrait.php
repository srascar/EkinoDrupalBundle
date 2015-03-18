<?php
/*
 * This file is part of the Ekino Drupal package.
 *
 * (c) 2014 Ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\DrupalBundle\Kernel;

use Drupal\Core\DrupalKernel;
use Drupal\Core\DrupalKernelInterface;
use Symfony\Component\HttpFoundation\Request;

trait EkinoDrupalKernelTrait
{
    /**
     * @var DrupalKernelInterface
     */
    protected $drupalKernel;

    /**
     * Create a Drupal kernel and set it in your Symfony kernel
     *
     * @param Request $request
     * @param         $classLoader
     * @param         $environment
     * @param bool    $allowDumping
     */
    public function initDrupalKernel(Request $request, $classLoader, $environment, $allowDumping = true)
    {
        // Get DrupalKernel instance
        $this->drupalKernel = DrupalKernel::createFromRequest($request, $classLoader, $environment, $allowDumping);

        // Load legacy includes
        $this->drupalKernel->loadLegacyIncludes();

        // Boot the kernel to init container
        $this->drupalKernel->boot();

        // Load all legacy modules
        $this->drupalKernel->getContainer()->get('module_handler')->loadAll();

        // set the current request in DrupalKernel
        $this->drupalKernel->getContainer()->get('request_stack')->push($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildContainer()
    {
        $container = parent::buildContainer();
        $container->set('drupal', $this->drupalKernel);

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        parent::initializeContainer();
        $this->container->set('drupal', $this->drupalKernel);
    }
}