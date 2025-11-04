#!/bin/bash

# Multi-Currency Validation Script
# This script validates the multi-currency implementation

echo "========================================="
echo "Multi-Currency Validation Script"
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counter for pass/fail
PASS=0
FAIL=0

# Function to print success
success() {
    echo -e "${GREEN}✓${NC} $1"
    ((PASS++))
}

# Function to print failure
failure() {
    echo -e "${RED}✗${NC} $1"
    ((FAIL++))
}

# Function to print warning
warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

echo "1. Checking PHP syntax for all modified files..."
echo "-------------------------------------------"

PHP_FILES=(
    "app/helpers/currency_helper.php"
    "app/modules/currencies/controllers/currencies.php"
    "app/modules/currencies/models/currencies_model.php"
    "app/modules/setting/views/currencies.php"
    "app/modules/setting/controllers/setting.php"
    "app/modules/blocks/views/header.php"
    "app/modules/statistics/views/index.php"
    "app/modules/transactions/views/index.php"
    "app/modules/transactions/views/ajax_search.php"
    "app/modules/order/views/logs/logs.php"
    "app/modules/add_funds/controllers/easypaisa.php"
    "app/modules/add_funds/controllers/jazzcash.php"
    "app/modules/add_funds/controllers/faysalbank.php"
    "app/modules/add_funds/controllers/sadapay.php"
)

ALL_SYNTAX_VALID=true

for file in "${PHP_FILES[@]}"; do
    if [ -f "$file" ]; then
        if php -l "$file" > /dev/null 2>&1; then
            success "Syntax valid: $file"
        else
            failure "Syntax error: $file"
            ALL_SYNTAX_VALID=false
        fi
    else
        warning "File not found: $file"
    fi
done

echo ""
echo "2. Checking database migration files..."
echo "-------------------------------------------"

if [ -f "database/multi-currency.sql" ]; then
    success "Migration file exists: database/multi-currency.sql"
    
    # Check if file contains required tables
    if grep -q "CREATE TABLE.*currencies" database/multi-currency.sql; then
        success "Migration contains currencies table definition"
    else
        failure "Migration missing currencies table definition"
    fi
    
    # Check if file contains PKR seed
    if grep -q "PKR" database/multi-currency.sql; then
        success "Migration contains PKR currency seed"
    else
        failure "Migration missing PKR currency seed"
    fi
else
    failure "Migration file not found: database/multi-currency.sql"
fi

if [ -f "database/verify-currencies.sql" ]; then
    success "Verification script exists: database/verify-currencies.sql"
else
    failure "Verification script not found: database/verify-currencies.sql"
fi

echo ""
echo "3. Checking helper functions..."
echo "-------------------------------------------"

if [ -f "app/helpers/currency_helper.php" ]; then
    # Check for required functions
    if grep -q "function get_current_currency" app/helpers/currency_helper.php; then
        success "get_current_currency() function exists"
    else
        failure "get_current_currency() function missing"
    fi
    
    if grep -q "function convert_currency" app/helpers/currency_helper.php; then
        success "convert_currency() function exists"
    else
        failure "convert_currency() function missing"
    fi
    
    if grep -q "function format_currency" app/helpers/currency_helper.php; then
        success "format_currency() function exists"
    else
        failure "format_currency() function missing"
    fi
    
    if grep -q "function get_active_currencies" app/helpers/currency_helper.php; then
        success "get_active_currencies() function exists"
    else
        failure "get_active_currencies() function missing"
    fi
else
    failure "currency_helper.php not found"
fi

echo ""
echo "4. Checking currencies module..."
echo "-------------------------------------------"

if [ -f "app/modules/currencies/models/currencies_model.php" ]; then
    success "Currency model exists"
else
    failure "Currency model not found"
fi

if [ -f "app/modules/currencies/controllers/currencies.php" ]; then
    success "Currency controller exists"
    
    # Check for required methods
    if grep -q "function set_currency" app/modules/currencies/controllers/currencies.php; then
        success "set_currency() method exists in controller"
    else
        failure "set_currency() method missing in controller"
    fi
else
    failure "Currency controller not found"
fi

echo ""
echo "5. Checking admin UI..."
echo "-------------------------------------------"

if [ -f "app/modules/setting/views/currencies.php" ]; then
    success "Admin currency management view exists"
    
    # Check for key UI elements
    if grep -q "addCurrencyModal" app/modules/setting/views/currencies.php; then
        success "Add currency modal present"
    else
        failure "Add currency modal missing"
    fi
    
    if grep -q "editCurrencyModal" app/modules/setting/views/currencies.php; then
        success "Edit currency modal present"
    else
        failure "Edit currency modal missing"
    fi
else
    failure "Admin currency management view not found"
fi

echo ""
echo "6. Checking currency selector in sidebar..."
echo "-------------------------------------------"

if [ -f "app/modules/blocks/views/header.php" ]; then
    if grep -q "currencySelector" app/modules/blocks/views/header.php; then
        success "Currency selector present in header"
    else
        failure "Currency selector missing in header"
    fi
    
    if grep -q "get_current_currency()" app/modules/blocks/views/header.php; then
        success "Header uses get_current_currency() function"
    else
        failure "Header not using get_current_currency() function"
    fi
else
    failure "Header file not found"
fi

echo ""
echo "7. Checking currency conversion in views..."
echo "-------------------------------------------"

VIEWS_TO_CHECK=(
    "app/modules/statistics/views/index.php"
    "app/modules/transactions/views/index.php"
    "app/modules/order/views/logs/logs.php"
)

for view in "${VIEWS_TO_CHECK[@]}"; do
    if [ -f "$view" ]; then
        if grep -q "convert_currency" "$view"; then
            success "Currency conversion used in: $view"
        else
            warning "No currency conversion found in: $view"
        fi
    fi
done

echo ""
echo "8. Checking for hardcoded PKR strings..."
echo "-------------------------------------------"

# Check if PKR strings are properly formatted (should be "Rs X PKR" not just "PKR")
FILES_WITH_PKR=$(grep -l " PKR" app/modules/add_funds/controllers/*.php 2>/dev/null | wc -l)

if [ "$FILES_WITH_PKR" -gt 0 ]; then
    # Check if they use the proper format (Rs ... PKR)
    PROPER_FORMAT=$(grep -l "Rs.*PKR" app/modules/add_funds/controllers/*.php 2>/dev/null | wc -l)
    if [ "$PROPER_FORMAT" -eq "$FILES_WITH_PKR" ]; then
        success "Payment controllers use proper PKR format (Rs X PKR)"
    else
        warning "Some payment controllers may have improper PKR format"
    fi
else
    success "No hardcoded PKR strings in payment controllers"
fi

echo ""
echo "9. Checking documentation..."
echo "-------------------------------------------"

DOCS=(
    "MULTI_CURRENCY_GUIDE.md"
    "QUICK_REFERENCE.md"
)

for doc in "${DOCS[@]}"; do
    if [ -f "$doc" ]; then
        success "Documentation exists: $doc"
    else
        failure "Documentation missing: $doc"
    fi
done

echo ""
echo "========================================="
echo "Validation Summary"
echo "========================================="
echo -e "${GREEN}Passed: $PASS${NC}"
echo -e "${RED}Failed: $FAIL${NC}"
echo ""

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}✓ All validation checks passed!${NC}"
    exit 0
else
    echo -e "${RED}✗ Some validation checks failed. Please review the errors above.${NC}"
    exit 1
fi
