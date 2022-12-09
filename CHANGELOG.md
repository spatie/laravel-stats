# Changelog

All notable changes to `laravel-stats` will be documented in this file

## 2.1.0 - 2022-12-09

### What's Changed

- Add Group By Minute Period by @DexterHarrison in https://github.com/spatie/laravel-stats/pull/31

### New Contributors

- @DexterHarrison made their first contribution in https://github.com/spatie/laravel-stats/pull/31

**Full Changelog**: https://github.com/spatie/laravel-stats/compare/2.0.4...2.1.0

## 2.0.4 - 2022-08-31

### What's Changed

- Performance Improvement by removing get queries by @noamanahmed-omniful in https://github.com/spatie/laravel-stats/pull/28

### New Contributors

- @noamanahmed-omniful made their first contribution in https://github.com/spatie/laravel-stats/pull/28

**Full Changelog**: https://github.com/spatie/laravel-stats/compare/2.0.3...2.0.4

## 2.0.3 - 2022-07-29

### What's Changed

- fix typo in README.md. by @fsamapoor in https://github.com/spatie/laravel-stats/pull/23
- Support PostgreSQL by @skollro in https://github.com/spatie/laravel-stats/pull/27

### New Contributors

- @fsamapoor made their first contribution in https://github.com/spatie/laravel-stats/pull/23
- @skollro made their first contribution in https://github.com/spatie/laravel-stats/pull/27

**Full Changelog**: https://github.com/spatie/laravel-stats/compare/2.0.2...2.0.3

## 2.0.2 - 2022-06-01

### What's Changed

- Add table prefix to StatsQuery by @digitalkreativ in https://github.com/spatie/laravel-stats/pull/22

### New Contributors

- @digitalkreativ made their first contribution in https://github.com/spatie/laravel-stats/pull/22

**Full Changelog**: https://github.com/spatie/laravel-stats/compare/2.0.1...2.0.2

## 2.0.1 - 2022-04-06

## What's Changed

- Support SQLite by @bumbummen99 in https://github.com/spatie/laravel-stats/pull/19

## New Contributors

- @bumbummen99 made their first contribution in https://github.com/spatie/laravel-stats/pull/19

**Full Changelog**: https://github.com/spatie/laravel-stats/compare/2.0.0...2.0.1

## 2.0.0 - 2022-03-04

## What's Changed

Add support for relationships by @christoph-kluge in https://github.com/spatie/laravel-stats/pull/17

See [upgrading.md](./upgrading.md) for an upgrading guide.

### Added

- Added `StatsWriter` with classname support (`StatsWriter::for(MyModel::class)`)
- Added `StatsWriter` with eloquent-model support (`StatsWriter::for($eloquent)`)
- Added `StatsWriter` with "has-many"-relationship support (`StatsWriter::for($model->relationship())`) - other relationships are untested yet
- Added `StatsWriter` with custom-attribute support (`StatsWriter::for(MyModel::class, ['custom_column' => 'orders])`)
- Extended `StatsQuery` with relationship-support (`StatsQuery::for($model->relationship())`)
- Extended `StatsQuery` with additional attributes (`StatsQuery::for(StatsEvent::class, ['name' => 'OrderStats'])`)
- Extended `BaseStats` with direct writer access (`OrderStats::writer()` as addition to `OrderStats::query()`)

### Breaking changes

- Changed visibility of `StatsQuery::for($model)->generatePeriods()` from `public` to `protected`
- Changed visibility of `StatsQuery::getPeriodTimestampFormat()` from `public` to `protected`
- Replaced `StatsQuery::for($model)->getStatistic()` with `StatsQuery::for($model)->getAttributes()`
- Removed `BaseStats->createEvent()`

### Migrations

- Replace `StatsQuery::for(OrderStats::class)` with `OrderStats::query()`
- Replace `StatsEvent::TYPE_SET` with `DataPoint::TYPE_SET`
- Replace `StatsEvent::TYPE_CHANGE` with `DataPoint::TYPE_CHANGE`

## New Contributors

- @christoph-kluge made their first contribution in https://github.com/spatie/laravel-stats/pull/17

**Full Changelog**: https://github.com/spatie/laravel-stats/compare/1.0.1...2.0.0

## 2.0.0 - 2022-03-04

### Added

- Added `StatsWriter` with classname support (`StatsWriter::for(MyModel::class)`)
- Added `StatsWriter` with eloquent-model support (`StatsWriter::for($eloquent)`)
- Added `StatsWriter` with "has-many"-relationship support (`StatsWriter::for($model->relationship())`) - other relationships are untested yet
- Added `StatsWriter` with custom-attribute support (`StatsWriter::for(MyModel::class, ['custom_column' => 'orders])`)
- Extended `StatsQuery` with relationship-support (`StatsQuery::for($model->relationship())`)
- Extended `StatsQuery` with additional attributes (`StatsQuery::for(StatsEvent::class, ['name' => 'OrderStats'])`)
- Extended `BaseStats` with direct writer access (`OrderStats::writer()` as addition to `OrderStats::query()`)

### Breaking changes

- Changed visibility of `StatsQuery::for($model)->generatePeriods()` from `public` to `protected`
- Changed visibility of `StatsQuery::getPeriodTimestampFormat()` from `public` to `protected`
- Replaced `StatsQuery::for($model)->getStatistic()` with `StatsQuery::for($model)->getAttributes()`
- Removed `BaseStats->createEvent()`

### Migrations

- Replace `StatsQuery::for(OrderStats::class)` with `OrderStats::query()`
- Replace `StatsEvent::TYPE_SET` with `DataPoint::TYPE_SET`
- Replace `StatsEvent::TYPE_CHANGE` with `DataPoint::TYPE_CHANGE`

## 1.0.1 - 2022-02-02

- Add support for Laravel 9

## 1.0.0 - 2021-04-14

- initial release
