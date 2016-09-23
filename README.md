# Laravel Viddler Upload
[![Latest Version](https://img.shields.io/github/release/zenapply/laravel-viddler-upload.svg?style=flat-square)](https://github.com/zenapply/laravel-viddler-upload/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/zenapply/laravel-viddler-upload.svg?branch=master)](https://travis-ci.org/zenapply/laravel-viddler-upload)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zenapply/laravel-viddler-upload/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zenapply/laravel-viddler-upload/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/zenapply/laravel-viddler-upload/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/zenapply/laravel-viddler-upload/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/56f3252c35630e0029db0187/badge.svg?style=flat)](https://www.versioneye.com/user/projects/56f3252c35630e0029db0187)
[![Total Downloads](https://img.shields.io/packagist/dt/zenapply/laravel-viddler-upload.svg?style=flat-square)](https://packagist.org/packages/zenapply/laravel-viddler-upload)

This package makes uploading videos to [Viddler](http://www.viddler.com/) easier

## Installation

Install via [composer](https://getcomposer.org/) - In the terminal:
```bash
composer require zenapply/laravel-viddler-upload
```

Now add the following to the `providers` array in your `config/app.php`
```php
Zenapply\Viddler\Upload\Providers\Viddler::class
```

and this to the `aliases` array in `config/app.php`
```php
"Viddler" => Zenapply\Viddler\Upload\Facades\Viddler::class,
```

Then you will need to run these commands in the terminal in order to copy the config and migration files
```bash
php artisan vendor:publish --provider="Zenapply\Viddler\Upload\Providers\Viddler"
```

Before you run the migration you may want to take a look at `config/viddler.php` and change the `table` property to a table name that you would like to use. After that run the migration 
```bash
php artisan migrate
```

## Usage

```php
function upload(Request $request)
{
	$file = $request->file('file');
	$name = "My Video Title";

	/*
	 * Returns an Eloquent Model
	 */
	$model = Viddler::create($file, $name);

	// Convert the file
	$model->convert();

	// Upload it
	$model->upload();

	// Check the encoding status
	$model->check();

	return $model;
}
```

## Contributing
Contributions are always welcome!
