-- Verification Script for Multi-Currency Implementation
-- Run this script to verify the currencies table exists and is properly seeded

-- Check if currencies table exists
SELECT 'Checking currencies table...' AS Status;
SELECT COUNT(*) AS table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'currencies';

-- Verify PKR is the base currency with rate = 1.0
SELECT 'Verifying PKR base currency...' AS Status;
SELECT code, symbol, name, rate, is_default, enabled 
FROM currencies 
WHERE code = 'PKR' 
AND rate = 1.00000000 
AND is_default = 1;

-- List all currencies
SELECT 'Listing all currencies...' AS Status;
SELECT code, symbol, name, rate, is_default, enabled, created_at, updated_at 
FROM currencies 
ORDER BY is_default DESC, code ASC;

-- Count enabled currencies
SELECT 'Counting enabled currencies...' AS Status;
SELECT COUNT(*) AS enabled_count 
FROM currencies 
WHERE enabled = 1;
