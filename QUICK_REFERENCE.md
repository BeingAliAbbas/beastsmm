# Multi-Currency Quick Reference

## Essential Commands

### Database
```bash
# Install migration
mysql -u username -p database_name < database/multi-currency.sql

# Verify installation
mysql -u username -p database_name < database/verify-currencies.sql
```

### Check Syntax
```bash
# Validate all PHP files
bash validate-multicurrency.sh
```

## Helper Functions

### get_current_currency()
```php
$curr = get_current_currency();
// ['code' => 'PKR', 'symbol' => 'Rs', 'rate' => 1.0, 'name' => 'Pakistani Rupee', 'enabled' => true]
```

### convert_currency($amount, $from, $to)
```php
$usd = convert_currency(1000, 'PKR', 'USD');  // 1000 PKR → ~3.57 USD
$pkr = convert_currency(10, 'USD', 'PKR');    // 10 USD → ~2800 PKR
```

### format_currency($amount, $currency)
```php
echo format_currency(1000);              // Uses current currency
echo format_currency(1000, 'USD');       // $1,000.00
echo format_currency(1000, 'PKR');       // Rs1,000.00
```

### get_active_currencies()
```php
$currencies = get_active_currencies();   // Returns array of enabled currencies
```

## Common Tasks

### Display Amount in Current Currency
```php
$current_currency = get_current_currency();
$amount_pkr = 1000;  // Amount stored in PKR
$converted = convert_currency($amount_pkr, 'PKR', $current_currency['code']);
echo $current_currency['symbol'] . number_format($converted, 2);
```

### Display Amount with Formatting
```php
$amount_pkr = 1500;
$current_currency = get_current_currency();
$converted = convert_currency($amount_pkr, 'PKR', $current_currency['code']);
echo format_currency($converted, $current_currency);
```

### Check if Amount Needs Conversion
```php
// If stored in PKR and displaying in user's currency
if ($current_currency['code'] !== 'PKR') {
    $amount = convert_currency($amount_pkr, 'PKR', $current_currency['code']);
}
```

## Admin URLs

- **Currency Management**: `/setting/currencies`
- **Add Currency**: Click "Add New Currency" button
- **Edit Currency**: Click "Edit" on currency row
- **Set Default**: Click "Set Default" on currency row

## Conversion Formula

```
converted_amount = original_amount × (target_rate ÷ base_rate)
```

Since PKR is base (rate = 1.0):
- **PKR → Other**: `amount × target_rate`
- **Other → PKR**: `amount ÷ from_rate`
- **Other → Other**: `(amount ÷ from_rate) × target_rate`

## Default Rates (Example)

| Currency | Rate to PKR | Example Conversion |
|----------|-------------|--------------------|
| PKR      | 1.0         | 1000 PKR = 1000 PKR |
| USD      | 0.00357143  | 1000 PKR ≈ 3.57 USD |
| EUR      | 0.00326797  | 1000 PKR ≈ 3.27 EUR |
| GBP      | 0.00277778  | 1000 PKR ≈ 2.78 GBP |
| INR      | 0.29762     | 1000 PKR ≈ 297.62 INR |

**Note**: Update rates regularly for accuracy.

## Currency Selector

**Location**: Sidebar, below balance (non-admin users only)

**Usage**:
1. Select currency from dropdown
2. Page reloads automatically
3. All amounts update to selected currency

**Persistence**: 30-day cookie + session storage

## Files Modified

### Core Files
- `app/helpers/currency_helper.php` - Helper functions
- `app/modules/currencies/*` - Currency module (model, controller, views)
- `app/modules/setting/controllers/setting.php` - Currency tab integration
- `app/modules/setting/views/currencies.php` - Admin management UI

### Views Updated
- `app/modules/blocks/views/header.php` - Currency selector
- `app/modules/statistics/views/index.php` - Dashboard stats
- `app/modules/transactions/views/index.php` - Transaction list
- `app/modules/transactions/views/ajax_search.php` - Transaction search
- `app/modules/order/views/logs/logs.php` - Order logs

### Payment Controllers
- `app/modules/add_funds/controllers/easypaisa.php`
- `app/modules/add_funds/controllers/jazzcash.php`
- `app/modules/add_funds/controllers/faysalbank.php`
- `app/modules/add_funds/controllers/sadapay.php`

### Database
- `database/multi-currency.sql` - Migration
- `database/verify-currencies.sql` - Verification

### Documentation
- `MULTI_CURRENCY_GUIDE.md` - Full guide
- `QUICK_REFERENCE.md` - This file
- `VISUAL_GUIDE.md` - Screenshots and examples
- `IMPLEMENTATION_SUMMARY.md` - Technical summary

## Validation

```bash
# Run validation script
bash validate-multicurrency.sh

# Expected output:
# ✓ Currencies table exists
# ✓ PKR base currency configured
# ✓ Helper functions working
# ✓ Conversion formula correct
# ✓ All PHP files syntax valid
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Selector not visible | Check: user is not admin, currencies table exists, currencies enabled |
| Wrong conversion | Verify: rates are correct, currencies enabled, PKR rate = 1.0 |
| Amounts not updating | Clear cookies, re-select currency, check session |
| Can't delete currency | Check: it's not PKR (base currency), it's not in use |

## Security Checklist

- ✓ CSRF tokens on all forms
- ✓ Input sanitization (code, symbol, rate)
- ✓ Admin-only access to management
- ✓ PKR cannot be deleted/disabled
- ✓ Positive rate validation
- ✓ Unique currency codes enforced

## Quick Test

### After Installation
1. Navigate to Settings > Currencies
2. Verify PKR exists with rate = 1.0
3. Add a test currency (e.g., TEST)
4. Set rate (e.g., 0.5)
5. Go to dashboard
6. Select TEST from sidebar
7. Verify amounts are halved (1000 PKR → 500 TEST)
8. Delete TEST currency

### Conversion Test
```php
// In any controller or view:
$test_amount = 1000;
$usd = convert_currency($test_amount, 'PKR', 'USD');
var_dump($usd);  // Should be ~3.57

$back_to_pkr = convert_currency($usd, 'USD', 'PKR');
var_dump($back_to_pkr);  // Should be ~1000
```

## Tips

1. **Update Rates**: Set reminder to update exchange rates weekly/monthly
2. **Test Conversions**: Always test after changing rates
3. **Backup Before**: Backup database before running migration
4. **Monitor Usage**: Check which currencies users prefer
5. **Performance**: Currency operations are cached; no performance impact

## Support Contacts

- **Technical Issues**: System Administrator
- **Rate Updates**: Finance Team
- **User Questions**: Support Team
