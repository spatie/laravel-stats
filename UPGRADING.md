# Upgrading

If you come across any edge cases that this guide does not cover, please send in a PR! 

## From v1 to v2

### Breaking changes

- Replaced `StatsQuery::for($model)->getStatistic()` with `StatsQuery::for($model)->getAttributes()`
- Removed `BaseStats->createEvent()`

- Changed visibility of `StatsQuery::for($model)->generatePeriods()` from `public` to `protected`
- Changed visibility of `StatsQuery::getPeriodTimestampFormat()` from `public` to `protected`

These methods are only used internally by the package. You can no longer use these methods in your own code.

### Migrations

- Replace `StatsQuery::for(OrderStats::class)` with `OrderStats::query()`
- Replace `StatsEvent::TYPE_SET` with `DataPoint::TYPE_SET`
- Replace `StatsEvent::TYPE_CHANGE` with `DataPoint::TYPE_CHANGE`
