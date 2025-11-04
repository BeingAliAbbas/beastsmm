# Multi-Currency Support Implementation Guide

## Overview

This SMM panel now supports multiple currencies with automatic conversion and display. PKR (Pakistani Rupee) is the base currency with a rate of 1.0, and all stored amounts in the database remain in PKR for backward compatibility.

## Key Features

- **Base Currency**: PKR (rate = 1.0) is the base and default currency
- **Multiple Currencies**: Support for USD, EUR, GBP, INR, AUD, CAD, and more
- **Dynamic Conversion**: All amounts are converted in real-time for display
- **User Preference**: Currency selection persists via session and 30-day cookie
- **Admin Management**: Full CRUD interface for managing currencies and exchange rates

## Database Structure

### Currencies Table

```sql
CREATE TABLE `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rate` decimal(15,8) NOT NULL DEFAULT '1.00000000',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Default Currencies

The system comes pre-seeded with these currencies:

- **PKR** (Pakistani Rupee) - Rate: 1.0 - Base currency
- **USD** (US Dollar) - Rate: ~0.0036
- **EUR** (Euro) - Rate: ~0.0033
- **GBP** (British Pound) - Rate: ~0.0028
- **INR** (Indian Rupee) - Rate: ~0.30
- **AUD** (Australian Dollar) - Rate: ~0.0056
- **CAD** (Canadian Dollar) - Rate: ~0.0050

## Installation

### 1. Run Database Migration

Execute the migration SQL file:

```bash
mysql -u your_username -p your_database < database/multi-currency.sql
```

### 2. Verify Installation

Run the verification script:

```bash
mysql -u your_username -p your_database < database/verify-currencies.sql
```

Expected output:
- Currencies table exists
- PKR is the base currency with rate = 1.0
- All default currencies are seeded

## Helper Functions

### get_current_currency()

Returns the currently selected currency for the user.

```php
$currency = get_current_currency();
// Returns: ['code' => 'PKR', 'symbol' => 'Rs', 'rate' => 1.0, 'name' => 'Pakistani Rupee', 'enabled' => true]
```

### convert_currency($amount, $from_code, $to_code)

Converts an amount from one currency to another.

```php
$pkr_amount = 1000;
$usd_amount = convert_currency($pkr_amount, 'PKR', 'USD');
// Result: ~3.57 USD
```

**Formula**: `converted_amount = original_amount × (target_rate ÷ base_rate)`

Since PKR is the base currency (rate = 1.0), the formula simplifies to:
- PKR to USD: `amount × USD_rate`
- USD to PKR: `amount ÷ USD_rate`

### format_currency($amount, $currency)

Formats an amount with the currency symbol and proper decimal places.

```php
$formatted = format_currency(1000, 'PKR');
// Result: "Rs1,000.00"

$formatted = format_currency(3.57, 'USD');
// Result: "$3.57"
```

### get_active_currencies()

Returns all enabled currencies.

```php
$currencies = get_active_currencies();
// Returns array of currency objects
```

## Admin Interface

### Accessing Currency Management

Navigate to: **Settings > Currencies**

### Managing Currencies

#### Add New Currency
1. Click "Add New Currency"
2. Enter currency code (e.g., "JPY")
3. Enter symbol (e.g., "¥")
4. Enter name (e.g., "Japanese Yen")
5. Enter exchange rate relative to PKR
6. Toggle enabled/disabled status
7. Click "Add Currency"

#### Edit Currency
1. Click "Edit" on the currency row
2. Modify symbol, name, or rate
3. Toggle enabled status
4. Click "Update Currency"

**Note**: Currency code cannot be changed after creation.

#### Set Default Currency
1. Click "Set Default" on any enabled currency
2. Confirm the action
3. The previous default will be unset automatically

#### Delete Currency
1. Click "Delete" on the currency row
2. Confirm the deletion

**Note**: The base currency (PKR) cannot be deleted.

## User Interface

### Currency Selector

The currency selector appears in the sidebar, below the balance display, for non-admin users.

**Usage**:
1. Click the dropdown in the sidebar
2. Select your preferred currency
3. The page will reload automatically
4. All amounts throughout the application will display in the selected currency

**Persistence**:
- Selection is saved in the user's session
- A 30-day cookie is set for persistence across sessions

## Currency Conversion Areas

The following areas automatically display amounts in the selected currency:

### Dashboard (Statistics)
- User balance
- Total amount spent/received
- Users balance (admin)
- Providers balance (admin)
- Last 30 days profit (admin)
- Today's profit (admin)
- Order charges in tables

### Transactions
- Transaction amounts in list view
- Transaction amounts in AJAX search
- Last 5 transactions widget

### Orders
- Order charges
- Provider charges (admin)
- Profit calculations (admin)
- Total sell, total profit, and today's profit summary

### Add Funds
- Payment gateway displays (Easypaisa, JazzCash, Faysal Bank, SadaPay)
- WhatsApp notifications show amounts in PKR with "Rs" symbol

### Services
- Service prices (rate per 1000)
- Service costs in all views

## Backward Compatibility

### Database Values
All monetary values stored in the database remain unchanged in PKR. Currency conversion is applied only during:
- Display in views
- Calculations that require converted values
- User-facing interfaces

### Existing Functionality
- All existing payment gateways continue to work
- Transaction records maintain PKR amounts
- Admin can still view raw PKR values in reports

## Security

### Input Validation
- All currency codes are sanitized and validated
- Exchange rates must be positive decimal values
- CSRF tokens protect all form submissions

### Access Control
- Only admins can manage currencies
- Currency module endpoints verify admin role
- Non-admin users can only select currencies, not modify them

### Data Integrity
- Base currency (PKR) cannot be deleted or disabled
- Exchange rates are validated before saving
- Duplicate currency codes are prevented

## Troubleshooting

### Currency Not Displaying Correctly

**Check**:
1. Verify the currencies table exists
2. Ensure PKR has rate = 1.0
3. Check that the selected currency is enabled
4. Clear browser cookies and re-select currency

### Conversion Errors

**Check**:
1. Verify exchange rates are set correctly
2. Ensure both currencies exist in the database
3. Check that currencies are enabled
4. Review conversion formula implementation

### Currency Selector Not Appearing

**Check**:
1. Verify user is not an admin (selector only shows for non-admin users)
2. Check that active currencies exist
3. Verify header.php includes are loaded correctly

## Best Practices

### Setting Exchange Rates
1. Use reliable sources for exchange rates (e.g., XE.com, OANDA)
2. Update rates regularly to maintain accuracy
3. Consider using an automated rate update system (future enhancement)

### Managing Currencies
1. Only enable currencies you actively support
2. Set realistic min/max values for payment gateways
3. Test currency conversions after updating rates
4. Keep the default currency set to the most commonly used one

### Performance
1. Currency conversions are lightweight operations
2. Helper functions use caching where appropriate
3. Database queries are optimized with proper indexing

## Future Enhancements

Potential improvements for the multi-currency system:

1. **Automatic Rate Updates**: Integration with currency exchange rate APIs
2. **Currency History**: Track rate changes over time
3. **Rate Alerts**: Notify admins when rates change significantly
4. **User Preferences**: Allow users to set preferred currency in profile
5. **Multi-Currency Payments**: Accept payments in multiple currencies
6. **Reports by Currency**: Generate financial reports in specific currencies

## Support

For issues or questions:
1. Check this guide and QUICK_REFERENCE.md
2. Review the validation script output
3. Verify database structure matches specifications
4. Check PHP error logs for detailed error messages
5. Contact system administrator for database-related issues

## Version History

- **v1.0** (2024): Initial multi-currency implementation
  - Base currency: PKR
  - Default currencies: PKR, USD, EUR, GBP, INR, AUD, CAD
  - User currency selection with persistence
  - Admin management interface
  - Conversion across all views
