Drupal Bundle by Ekino
======================

[![Build Status](https://secure.travis-ci.org/ekino/EkinoDrupalBundle.png?branch=master)](http://travis-ci.org/ekino/EkinoDrupalBundle)

The bundle tries to deeply integrate Drupal with Symfony2. Of course this is done without
altering the Drupal's core.

Install
-------

### Install the last Drupal 8 version

### In the ``core`` directory, add the following dependency 

``` 
    "require": {
        // ...
        "ekino/drupal-bundle": "drupal_8-dev"
    }
```

### Execute a composer update

``` 
$ composer update ekino/drupal-bundle
```

### Create an ``app`` directory at the root of your project

You can directly copy it from a Symfony standard installation

    Drupal install Root directory
        - app
            - config
                config.yml
                config_dev.yml
                ...
            AppKernel.php
        + core
        + modules
        + ...

### Change the AppKernel file by adding

``` php
<?php
use Ekino\Bundle\DrupalBundle\Kernel\EkinoDrupalKernelTrait;
// ...

class AppKernel extends Kernel
{
    use EkinoDrupalKernelTrait;
    
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Ekino\Bundle\DrupalBundle\EkinoDrupalBundle(),
        );

        // ...

        return $bundles;
    }
    
    // ...
}

```

### Update the ``index.php`` file

``` php
<?php
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once __DIR__ . '/core/vendor/autoload.php';

require_once __DIR__.'/app/AppKernel.php';

$request = Request::createFromGlobals();

// init Symfony kernel
$kernel = new AppKernel('dev', true);
// init Drupal kernel
$kernel->initDrupalKernel($request, $autoloader, 'prod');
$kernel->loadClassCache();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
```

### Configuration (optional)

By default, the delivery strategy is set to ``symfony`` which means that 
request will be handled by Symfony before falling back to Drupal if 
``HttpExceptionInterface`` is thrown (NotFound or AccessDenied http exceptions).

If you want Drupal to handle request first,
edit the Symfony ``config.yml`` file and add the following lines:

    ekino_drupal:
        delivery_strategy: drupal