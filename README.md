# Laravel Inline Translation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beyondcode/laravel-inline-translation.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-inline-translation)
[![Build Status](https://img.shields.io/travis/beyondcode/laravel-inline-translation/master.svg?style=flat-square)](https://travis-ci.org/beyondcode/laravel-inline-translation)
[![Quality Score](https://img.shields.io/scrutinizer/g/beyondcode/laravel-inline-translation.svg?style=flat-square)](https://scrutinizer-ci.com/g/beyondcode/laravel-inline-translation)
[![Total Downloads](https://img.shields.io/packagist/dt/beyondcode/laravel-inline-translation.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-inline-translation)

This package lets you add inline translation to your Laravel application. Just click on a translation variable, change it's value and save the new value.

![Example output](https://beyondco.de/github/inline-translation/example.png)


## Installation

You can install the package via composer as a dev dependency:

```bash
composer require beyondcode/laravel-inline-translation --dev
```

The package is enabled by default - so all you need to do is visit your application in the browser and look for translation keys.

Please do **NOT** use this package in production. Updating translation keys will save the updated values in the filesystem.
This package is only intended during the development.

## Disabling Inline Translation

You can disable inline translation by setting an environment variable called `INLINE_TRANSLATION_ENABLED` to `false`.

### Disclaimer

I tested this package with a couple of our client projects as well as with some open source Laravel projects. 
Translation variables appear throughout very different parts of your application, so there is a chance that this is not working for your specific setup.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email marcel@beyondco.de instead of using the issue tracker.

## Credits

- [Marcel Pociot](https://github.com/mpociot)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
