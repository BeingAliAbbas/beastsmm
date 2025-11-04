-- Verification Script for Multi-Currency Setup
-- Run this to verify the currencies table is set up correctly

-- Check if currencies table exists
SELECT 
    'currencies table exists' AS check_name,
    CASE 
        WHEN COUNT(*) > 0 THEN 'PASS'
        ELSE 'FAIL'
    END AS status
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'currencies';

-- Verify PKR is base currency with rate = 1.0
SELECT 
    'PKR is base currency' AS check_name,
    CASE 
        WHEN COUNT(*) > 0 AND MAX(rate) = 1.000000 THEN 'PASS'
        ELSE 'FAIL'
    END AS status
FROM currencies 
WHERE code = 'PKR';

-- Verify PKR is default currency
SELECT 
    'PKR is default currency' AS check_name,
    CASE 
        WHEN COUNT(*) > 0 AND MAX(is_default) = 1 THEN 'PASS'
        ELSE 'FAIL'
    END AS status
FROM currencies 
WHERE code = 'PKR';

-- List all currencies
SELECT 
    'Currency list' AS info,
    code,
    symbol,
    name,
    rate,
    is_default,
    enabled
FROM currencies
ORDER BY is_default DESC, code ASC;

-- Count enabled currencies
SELECT 
    'Total enabled currencies' AS info,
    COUNT(*) AS count
FROM currencies 
WHERE enabled = 1;
