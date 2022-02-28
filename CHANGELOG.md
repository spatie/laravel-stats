# Changelog

All notable changes to `laravel-stats` will be documented in this file

## 2.0.0 - 2022-02-20

### Added
 
- Added `StatsWriter` with classname support (`StatsWriter::for(MyModel::class)`) 
- Added `StatsWriter` with eloquent-model support (`StatsWriter::for($eloquent)`) 
- Added `StatsWriter` with "has-many"-relationship support (`StatsWriter::for($model->relationship())`) - other relationships are untested yet  
- Added `StatsWriter` with custom-attribute support (`StatsWriter::for(MyModel::class, ['custom_column' => 'orders])`)
- Extended `StatsQuery` with relationship-support (`StatsQuery::for($model->relationship())`)
- Extended `StatsQuery` with additional attributes (`StatsQuery::for(StatsEvent::class, ['name' => 'OrderStats'])`)

### BC breaks

- Replaced `StatsQuery::for($model)->getStatistic()` with `StatsQuery::for($model)->getAttributes()` 
- Removed `BaseStats->createEvent()` 

Migrations:

- Replace `StatsQuery::for(OrderStats::class)` with `OrderStats::query()`
- Replace `StatsEvent::TYPE_SET` use `DataPoint::TYPE_SET` instead
- Replace `StatsEvent::TYPE_CHANGE` use `DataPoint::TYPE_CHANGE` instead

## 1.0.1 - 2022-02-02

- Add support for Laravel 9

## 1.0.0 - 2021-04-14

- initial release
