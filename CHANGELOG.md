# Changelog

All notable changes to RocketLMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-02-13

### Added - Milestone 1: Foundation & Core Infrastructure

#### Exchange Rate System
- **Dual API Integration**: Primary (exchangeratesapi.io) and backup (exchangerate.host) APIs
- **Automatic Updates**: Exchange rates update every 12 hours via Laravel Scheduler
- **Database Storage**: Historical exchange rates stored in `exchange_rates` table
- **Caching Layer**: 1-hour cache for improved performance
- **Fallback Mechanism**: Automatic failover to backup API when primary fails
- **Admin Controls**: Manual update button and settings in admin panel
- **API Endpoints**: 
  - `POST /admin/exchange-rates/update` - Manual update
  - `GET /admin/exchange-rates/settings` - Get current settings
- **Artisan Command**: `php artisan exchange:update` for manual updates
- **Helper Functions**: `convertCurrency()`, `formatCurrency()`, `getUserCurrency()`

#### Unit Conversion System
- **Length Units**: km, mi, m, ft, cm, in
- **Mass Units**: kg, lbs, g, oz, ton
- **Area Units**: sqm, sqft, sqkm, acre, hectare
- **User Preferences**: Added columns to `users` table for unit preferences
- **Automatic Conversion**: Helper functions for seamless conversion in views
- **Helper Functions**: `convertUnit()`, `formatUnit()`, `getUserUnit()`
- **Configuration**: Comprehensive config files with conversion factors

#### Testing & Quality
- **PHPUnit Tests**: Complete test coverage for both services
  - ExchangeRateServiceTest: 8 test cases
  - UnitConversionServiceTest: 13 test cases
- **Error Handling**: Comprehensive logging and fallback mechanisms
- **Validation**: Input validation for all conversions

#### Documentation
- **Setup Guide**: Complete installation and configuration guide
- **API Documentation**: Detailed API usage examples
- **Troubleshooting**: Common issues and solutions
- **Code Comments**: Inline documentation for all methods

#### Configuration Files
- `config/exchange.php` - Exchange rate configuration
- `config/units.php` - Unit conversion configuration
- Environment variables for easy customization

#### Database Migrations
- `2026_02_13_000001_create_exchange_rates_table.php`
- `2026_02_13_000002_add_unit_preferences_to_users_table.php`

### Changed
- Updated `.env` with new configuration options
- Enhanced `app/Console/Kernel.php` with exchange rate scheduler
- Extended `app/Helpers/helper.php` with conversion functions
- Modified `app/Providers/RouteServiceProvider.php` to load new routes

### Technical Details

#### Services Created
- `App\Services\ExchangeRateService` - Handles currency conversion and API integration
- `App\Services\UnitConversionService` - Handles unit conversions

#### Models Created
- `App\Models\ExchangeRate` - Exchange rate data model

#### Controllers Updated
- `App\Http\Controllers\Admin\SettingsController` - Added exchange rate methods

#### Routes Added
- `routes/exchange_units.php` - New routes for exchange rate management

### Performance Improvements
- Implemented caching for exchange rates (1-hour TTL)
- Database indexing on currency pairs and timestamps
- Automatic cleanup of old exchange rates (90-day retention)
- Lazy loading of services

### Security
- API keys stored in environment variables
- Input validation on all conversions
- Rate limiting considerations for API calls
- Secure fallback mechanisms

---

## [1.9.0] - Previous Version

### Features
- Base RocketLMS functionality
- Course management
- User management
- Payment gateways
- Meeting systems
- Certificate generation
- Quiz system
- Bundle system
- Affiliate system

---

## Future Releases

### [2.1.0] - Planned
- Multi-language support for unit labels
- Additional currency providers
- Real-time exchange rate updates
- User dashboard for preference management
- Bulk currency conversion tools
- Exchange rate charts and analytics

---

## Notes

### Breaking Changes
None in this release. All new features are backward compatible.

### Upgrade Path
1. Run migrations: `php artisan migrate`
2. Update `.env` with new configuration
3. Clear caches: `php artisan config:clear`
4. Run initial exchange rate update: `php artisan exchange:update`
5. Configure cron job for scheduler

### Dependencies
- Laravel 9.19+
- PHP 8.1+
- MySQL 5.7+
- Guzzle HTTP client (already included)

---

**Last Updated**: February 13, 2026  
**Version**: 2.0.0  
**Status**: Stable
