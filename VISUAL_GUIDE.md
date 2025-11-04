# Multi-Currency Visual Guide

## Overview

This guide provides visual examples and step-by-step instructions for using the multi-currency feature.

## User Interface

### Currency Selector in Sidebar

The currency selector appears in the sidebar, directly below the balance display.

**Location**: Sidebar > Below Balance
**Visibility**: Non-admin users only

```
┌─────────────────────────────────┐
│  Hi, USERNAME                   │
│  Balance: Rs 1,000.00           │
│  ┌───────────────────────────┐  │
│  │ PKR - Pakistani Rupee   ▼ │  │ ← Currency Selector
│  └───────────────────────────┘  │
└─────────────────────────────────┘
```

### Selecting a Currency

**Step 1**: Click on the currency dropdown
```
┌───────────────────────────┐
│ PKR - Pakistani Rupee   ▼ │ ← Click here
└───────────────────────────┘
```

**Step 2**: Choose your preferred currency
```
┌───────────────────────────┐
│ PKR - Pakistani Rupee   ▼ │
├───────────────────────────┤
│ PKR - Pakistani Rupee   ✓ │ ← Currently selected
│ USD - US Dollar           │
│ EUR - Euro                │
│ GBP - British Pound       │
│ INR - Indian Rupee        │
│ AUD - Australian Dollar   │
│ CAD - Canadian Dollar     │
└───────────────────────────┘
```

**Step 3**: Page reloads and all amounts update

### Before and After Examples

#### Example 1: PKR to USD

**Before (PKR selected)**:
```
Balance: Rs 1,000.00
Order Charge: Rs 250.00
Transaction: Rs 500.00
```

**After (USD selected)**:
```
Balance: $3.57
Order Charge: $0.89
Transaction: $1.79
```

#### Example 2: PKR to EUR

**Before (PKR)**:
```
Total Spent: Rs 5,000.00
Last 30 Days Profit: Rs 1,500.00
Today's Profit: Rs 300.00
```

**After (EUR)**:
```
Total Spent: €16.34
Last 30 Days Profit: €4.90
Today's Profit: €0.98
```

## Admin Interface

### Accessing Currency Management

**Navigation**: Settings > Currencies

```
Settings Menu
├── Website Setting
├── Currency (old single-currency)
├── Currencies ← New multi-currency management
├── Payment
└── ...
```

### Currency Management Dashboard

```
┌─────────────────────────────────────────────────────────────┐
│ Multi-Currency Management                                   │
│                                                             │
│ [+ Add New Currency]                                        │
│                                                             │
├──────┬────────┬──────────────┬────────┬─────────┬─────────┤
│ Code │ Symbol │ Name         │  Rate  │ Default │ Status  │
├──────┼────────┼──────────────┼────────┼─────────┼─────────┤
│ PKR  │   Rs   │Pakistani Rupee│ 1.0000│ Default │ Enabled │
│ USD  │   $    │ US Dollar    │ 0.0036│[Set Default]│ Enabled │
│ EUR  │   €    │ Euro         │ 0.0033│[Set Default]│ Enabled │
│ GBP  │   £    │British Pound │ 0.0028│[Set Default]│ Enabled │
└──────┴────────┴──────────────┴────────┴─────────┴─────────┘
```

### Adding a New Currency

**Step 1**: Click "Add New Currency"

**Step 2**: Fill in the form
```
┌────────────────────────────────────┐
│ Add New Currency                   │
├────────────────────────────────────┤
│ Currency Code*                     │
│ ┌────────────────────────────────┐ │
│ │ USD                            │ │
│ └────────────────────────────────┘ │
│                                    │
│ Symbol*                            │
│ ┌────────────────────────────────┐ │
│ │ $                              │ │
│ └────────────────────────────────┘ │
│                                    │
│ Name*                              │
│ ┌────────────────────────────────┐ │
│ │ US Dollar                      │ │
│ └────────────────────────────────┘ │
│                                    │
│ Exchange Rate (to PKR)*            │
│ ┌────────────────────────────────┐ │
│ │ 0.00357143                     │ │
│ └────────────────────────────────┘ │
│ (1 PKR = 0.0036 USD)              │
│                                    │
│ ☑ Enable Currency                 │
│                                    │
│ [Cancel]  [Add Currency]           │
└────────────────────────────────────┘
```

**Step 3**: Currency appears in the list

### Editing a Currency

**Step 1**: Click "Edit" on the currency row

**Step 2**: Modify fields (code is read-only)
```
┌────────────────────────────────────┐
│ Edit Currency                      │
├────────────────────────────────────┤
│ Currency Code                      │
│ ┌────────────────────────────────┐ │
│ │ USD              (read-only)   │ │
│ └────────────────────────────────┘ │
│                                    │
│ Symbol*                            │
│ ┌────────────────────────────────┐ │
│ │ $                              │ │
│ └────────────────────────────────┘ │
│                                    │
│ Name*                              │
│ ┌────────────────────────────────┐ │
│ │ US Dollar                      │ │
│ └────────────────────────────────┘ │
│                                    │
│ Exchange Rate (to PKR)*            │
│ ┌────────────────────────────────┐ │
│ │ 0.00360000                     │ │ ← Updated rate
│ └────────────────────────────────┘ │
│                                    │
│ ☑ Enable Currency                 │
│                                    │
│ [Cancel]  [Update Currency]        │
└────────────────────────────────────┘
```

## Dashboard Examples

### Statistics Dashboard - PKR View

```
┌──────────────────────┬──────────────────────┐
│ Your Balance         │ Total Amount Spent   │
│ Rs 2,500.00          │ Rs 15,000.00         │
└──────────────────────┴──────────────────────┘

┌──────────────────────┬──────────────────────┐
│ Total Users Balance  │ Providers Balance    │
│ Rs 50,000.00         │ Rs 25,000.00         │
└──────────────────────┴──────────────────────┘

┌──────────────────────┬──────────────────────┐
│ Last 30 Days Profit  │ Today's Profit       │
│ Rs 8,000.00          │ Rs 350.00            │
└──────────────────────┴──────────────────────┘
```

### Statistics Dashboard - USD View

```
┌──────────────────────┬──────────────────────┐
│ Your Balance         │ Total Amount Spent   │
│ $8.93                │ $53.57               │
└──────────────────────┴──────────────────────┘

┌──────────────────────┬──────────────────────┐
│ Total Users Balance  │ Providers Balance    │
│ $178.57              │ $89.29               │
└──────────────────────┴──────────────────────┘

┌──────────────────────┬──────────────────────┐
│ Last 30 Days Profit  │ Today's Profit       │
│ $28.57               │ $1.25                │
└──────────────────────┴──────────────────────┘
```

## Transaction Examples

### Transaction List - PKR View

```
┌────┬────────────┬─────────┬───────────┬──────────────┐
│ No │ Payment    │ Amount  │ Fee       │ Created      │
├────┼────────────┼─────────┼───────────┼──────────────┤
│ 1  │ Easypaisa  │Rs500.00 │ 0.00      │ 2024-01-15  │
│ 2  │ JazzCash   │Rs1000.00│ 0.00      │ 2024-01-14  │
│ 3  │ Bank       │Rs2500.00│ 0.00      │ 2024-01-13  │
└────┴────────────┴─────────┴───────────┴──────────────┘
```

### Transaction List - USD View

```
┌────┬────────────┬─────────┬───────────┬──────────────┐
│ No │ Payment    │ Amount  │ Fee       │ Created      │
├────┼────────────┼─────────┼───────────┼──────────────┤
│ 1  │ Easypaisa  │ $1.79   │ 0.00      │ 2024-01-15  │
│ 2  │ JazzCash   │ $3.57   │ 0.00      │ 2024-01-14  │
│ 3  │ Bank       │ $8.93   │ 0.00      │ 2024-01-13  │
└────┴────────────┴─────────┴───────────┴──────────────┘
```

## Order Examples

### Order Logs - PKR View

```
┌────────┬──────────┬─────────┬────────────┬─────────┐
│Order ID│ Service  │Quantity │   Charge   │ Status  │
├────────┼──────────┼─────────┼────────────┼─────────┤
│ 12345  │Instagram │ 1000    │ Rs 150.00  │Complete │
│ 12346  │Facebook  │ 500     │ Rs 75.00   │Pending  │
│ 12347  │YouTube   │ 2000    │ Rs 300.00  │Complete │
└────────┴──────────┴─────────┴────────────┴─────────┘

Total Sell: Rs 525.00 PKR
Total Profit: Rs 125.00 PKR
Today's Profit: Rs 45.00 PKR
```

### Order Logs - EUR View

```
┌────────┬──────────┬─────────┬────────────┬─────────┐
│Order ID│ Service  │Quantity │   Charge   │ Status  │
├────────┼──────────┼─────────┼────────────┼─────────┤
│ 12345  │Instagram │ 1000    │ €0.49      │Complete │
│ 12346  │Facebook  │ 500     │ €0.25      │Pending  │
│ 12347  │YouTube   │ 2000    │ €0.98      │Complete │
└────────┴──────────┴─────────┴────────────┴─────────┘

Total Sell: €1.72 EUR
Total Profit: €0.41 EUR
Today's Profit: €0.15 EUR
```

## Conversion Examples

### Understanding Rates

**PKR to USD Example**:
```
PKR Rate: 1.0 (base)
USD Rate: 0.00357143

Conversion: 1000 PKR to USD
= 1000 × (0.00357143 ÷ 1.0)
= 1000 × 0.00357143
= 3.57 USD
```

**USD to PKR Example**:
```
USD Rate: 0.00357143
PKR Rate: 1.0 (base)

Conversion: 10 USD to PKR
= 10 × (1.0 ÷ 0.00357143)
= 10 × 280
= 2800 PKR
```

**EUR to GBP Example**:
```
EUR Rate: 0.00326797
GBP Rate: 0.00277778

Conversion: 100 EUR to GBP
= 100 ÷ 0.00326797 (EUR to PKR)
= 30,600 PKR
= 30,600 × 0.00277778 (PKR to GBP)
= 85 GBP
```

## Payment Gateway Views

### Easypaisa Payment - Before/After

**Before (Hardcoded)**:
```
Amount: 1000 PKR
Transaction ID: EP123456
```

**After (With Symbol)**:
```
Amount: Rs 1000 PKR
Transaction ID: EP123456
```

**User View (if USD selected)**:
```
Your payment of $3.57 USD is being processed
(equivalent to Rs 1000 PKR)
```

## Mobile View

### Mobile Currency Selector

```
┌─────────────────────┐
│ ≡  SMM Panel        │
├─────────────────────┤
│ Hi, John            │
│ Balance: Rs1,000.00 │
│                     │
│ ┌─────────────────┐ │
│ │PKR - Pakistani▼ │ │ ← Responsive dropdown
│ └─────────────────┘ │
│                     │
│ ☰ Dashboard         │
│ ⊕ New Order         │
│ ☷ Orders            │
└─────────────────────┘
```

## Common Scenarios

### Scenario 1: User Switches from PKR to USD

1. User's balance: Rs 5,000.00
2. User selects USD from dropdown
3. Page reloads
4. User's balance now shows: $17.86

### Scenario 2: Admin Updates Exchange Rate

1. Admin navigates to Settings > Currencies
2. Admin clicks Edit on USD currency
3. Admin changes rate from 0.00357143 to 0.00360000
4. Admin clicks Update
5. All users viewing in USD see updated amounts

### Scenario 3: New Currency Added

1. Admin adds JPY (Japanese Yen)
2. Admin sets rate: 0.50 (1 PKR = 0.5 JPY)
3. Admin enables currency
4. Users can now select JPY from dropdown
5. Amounts display in yen: ¥500.00 for Rs 1000

## Testing Checklist

Use this visual checklist to verify multi-currency functionality:

- [ ] Currency selector appears in sidebar
- [ ] Dropdown shows all enabled currencies
- [ ] Selected currency persists after page reload
- [ ] Balance updates when currency changes
- [ ] Dashboard statistics convert correctly
- [ ] Transaction amounts convert correctly
- [ ] Order charges convert correctly
- [ ] Payment amounts show correctly
- [ ] Admin can add new currency
- [ ] Admin can edit existing currency
- [ ] Admin can set default currency
- [ ] Admin can disable currency
- [ ] Disabled currency doesn't appear in selector
- [ ] PKR cannot be deleted
- [ ] PKR cannot be disabled
- [ ] Conversion formula is accurate
- [ ] Decimal places display correctly
- [ ] Currency symbols display correctly

## Tips for Best User Experience

1. **Clear Labeling**: Always show both symbol and code (e.g., "$3.57 USD")
2. **Consistent Placement**: Keep currency display format consistent
3. **Visual Feedback**: Highlight selected currency in dropdown
4. **Responsive Design**: Ensure dropdown works on mobile devices
5. **Loading States**: Show loading indicator during currency switch

## Troubleshooting Visual Guide

### Issue: Currency Selector Not Visible

**Check**:
```
1. Are you logged in as admin? 
   → Admins don't see selector
   
2. Are any currencies enabled?
   → Go to Settings > Currencies
   → Check "Status" column
   
3. Clear browser cache and reload
```

### Issue: Amounts Not Updating

**Check**:
```
1. Open browser developer tools
2. Check console for JavaScript errors
3. Verify AJAX request completed
4. Clear cookies and re-select currency
```

### Issue: Incorrect Conversion

**Check**:
```
1. Go to Settings > Currencies
2. Verify exchange rates are correct
3. Confirm PKR rate = 1.0
4. Test with calculator:
   Amount × (Target Rate ÷ Base Rate)
```

---

**Note**: Screenshots and actual UI may vary based on theme customization. This guide shows the standard layout.
