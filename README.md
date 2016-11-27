# middlewares/cors

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-sensiolabs]][link-sensiolabs]

Middleware to implement Cross-Origin Resource Sharing (CORS) using [neomerx/cors-psr7](https://github.com/neomerx/cors-psr7).

## Requirements

* PHP >= 5.6
* A [PSR-7](https://packagist.org/providers/psr/http-message-implementation) http mesage implementation ([Diactoros](https://github.com/zendframework/zend-diactoros), [Guzzle](https://github.com/guzzle/psr7), [Slim](https://github.com/slimphp/Slim), etc...)
* A [PSR-15 middleware dispatcher](https://github.com/middlewares/awesome-psr15-middlewares#dispatcher)

## Installation

This package is installable and autoloadable via Composer as [middlewares/cors](https://packagist.org/packages/middlewares/cors).

```sh
composer require middlewares/cors
```

## Example

```php
use Neomerx\Cors\Strategies\Settings;

$settings = new Settings();
$settings->setServerOrigin([
    'scheme' => 'http',
    'host' => 'example.com',
    'port' => '123',
]);

$dispatcher = new Dispatcher([
	new Middlewares\Cors($settings)
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

## Options

#### `__construct(Neomerx\Cors\Contracts\Strategies\SettingsStrategyInterface $settings)`

The CORS setting used. See [neomerx/cors-psr7](https://github.com/neomerx/cors-psr7) for more info about the available strategy options.

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/cors.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/middlewares/cors/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/g/middlewares/cors.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/cors.svg?style=flat-square
[ico-sensiolabs]: https://img.shields.io/sensiolabs/i/189702d3-2578-40c6-9700-6f351c859a7a.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/cors
[link-travis]: https://travis-ci.org/middlewares/cors
[link-scrutinizer]: https://scrutinizer-ci.com/g/middlewares/cors
[link-downloads]: https://packagist.org/packages/middlewares/cors
[link-sensiolabs]: https://insight.sensiolabs.com/projects/189702d3-2578-40c6-9700-6f351c859a7a
