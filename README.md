**THIS PACKAGE IS IN DEVELOPMENT, DO NOT USE YET**

# Track application stat changes over time

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-stats.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-stats)
[![Tests](https://github.com/spatie/laravel-stats/actions/workflows/run-tests.yml/badge.svg)](https://github.com/spatie/laravel-stats/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-stats.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-stats)

This package is a lightweight solution to summarize changes in your database over time. Here's a quick example where we are going to track the number of subscriptions and cancellations over time.

First, you should create a stats class.

```php
use Spatie\Stats\BaseStats;

class SubscriptionStats extends BaseStats {}
```

Next, you can call `increase` on it when somebody subscribes, and `decrease` when somebody cancels their plan.

```php
SubscriptionStats::increase(); // execute whenever somebody subscribes
SubscriptionStats::decrease() // execute whenever somebody cancels the subscription;
```

With this in place, you can query the stats. Here's how you can get the subscription stats for the past two months,
grouped by week.

```php
use Spatie\Stats\StatsQuery;

$stats = StatsQuery::for(SubscriptionStats::class)
    ->start(now()->subMonths(2))
    ->end(now()->subSecond())
    ->groupByWeek()
    ->get();
```

This will return an array like this one:

```php 
[
    [
        'start' => '2020-01-01',
        'end' => '2020-01-08',
        'value' => 102,
        'increments' => 32,
        'decrements' => 20,
        'difference' => 12,
    ],
    [
        'start' => '2020-01-08',
        'end' => '2020-01-15',
        'value' => 114,
        'increments' => 63,
        'decrements' => 30,
        'difference' => 33,
    ],
]
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-stats.jpg?t=2" width="419px" />](https://spatie.be/github-ad-click/laravel-stats)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can
support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.
You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards
on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-stats
```

You must publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Spatie\Stats\StatsServiceProvider" --tag="stats-migrations"
php artisan migrate
```

## Usage

### Step 1: create a stats class

First, you should create a stats class. This class is responsible for configuration how a particular statistic is
stored. By default, it needs no configuration at all.

```php
use Spatie\Stats\BaseStats;

class SubscriptionStats extends BaseStats {}
```

By default, the name of the class will be used to store the statistics in the database. To customize the used key, use `getName`

```php
use Spatie\Stats\BaseStats;

class SubscriptionStats extends BaseStats
{
    public function getName() : string{
        return 'my-custom-name'; // stats will be stored with using name `my-custom-name`
    }
}
```

## Step 2: call increase and decrease or set a fixed value

Next, you can call `increase`, `decrease` when the stat should change.  In this particular case, you should call `increase` on it when somebody subscribes, and `decrease` when somebody cancels their plan.

```php
SubscriptionStats::increase(); // execute whenever somebody subscribes
SubscriptionStats::decrease(); // execute whenever somebody cancels the subscription;
```

Instead of manually increasing and decreasing the stat, you can directly set it. This is useful when you particular stat does not get calculated by your own app, but lives elsewhere.  Using the subscription example, let's image that subscriptions live elsewhere, and that there's an API call to get the count.

```php
$count = AnAPi::getSubscriptionCount(); 

SubscriptionStats::set($count);
```

By default, that `increase`, `decrease` and `sets` methods assume that the event that caused your stats to change, happened right now. Optionally, you can pass a date time as a second parameter to these methods. Your stat change will be recorded as if it happened on that moment.

```php
SubscriptionStats::increase(1, $subscription->created_at); 
```

### Step 3: query the stats

With this in place, you can query the stats. You can fetch stats for a certain period and group it by day, week, month. 

Here's how you can get the subscription stats for the past two months,
grouped by week.

```php
$stats = SubscriptionStats::query()
    ->start(now()->subMonths(2))
    ->end(now()->subSecond())
    ->groupByWeek()
    ->get();
```

This will return an array containing arrayable `Spatie\Stats\DataPoint` objects. These objects can be cast to arrays like this:

```php 
// output of $stats->toArray():
[
    [
        'start' => '2020-01-01',
        'end' => '2020-01-08',
        'value' => 102,
        'increments' => 32,
        'decrements' => 20,
        'difference' => 12,
    ],
    [
        'start' => '2020-01-08',
        'end' => '2020-01-15',
        'value' => 114,
        'increments' => 63,
        'decrements' => 30,
        'difference' => 33,
    ],
]
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alex Vanderbist](https://github.com/AlexVanderbist)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
