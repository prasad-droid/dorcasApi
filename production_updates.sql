-- Production Database Updates
-- Date: 2026-05-08
-- Purpose: Sync production with development schema (Push Notifications support)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- 1. Add device_token to vendors table (Required for Push Notifications)
-- This column exists in development but is missing in production.
ALTER TABLE `vendors` 
ADD `device_token` TEXT DEFAULT NULL 
AFTER `referred_by`;

-- Note: The following columns exist in Production but are missing in Development. 
-- They are likely part of the live payment/commission system.
-- DO NOT remove them from production.
/*
  Table: vendors
  - vendor_wallet
  - commission_due

  Table: vendor_payments
  - transaction_id

  Table: vendor_redeem_requests
  - payment_id
  - admin_order_id
*/

COMMIT;
