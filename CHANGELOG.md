# Changelog

All notable changes to `laravel-stats` will be documented in this file

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
