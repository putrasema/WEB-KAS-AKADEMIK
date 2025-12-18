-- Migration: Add payment_method column to transactions table
-- Date: 2025-11-30
-- Description: Adds payment method tracking to transactions

-- Add payment_method column
ALTER TABLE transactions 
ADD COLUMN payment_method enum('cash','bank_transfer','credit_card','debit_card','e_wallet') 
NOT NULL DEFAULT 'cash' COMMENT 'Payment method used' 
AFTER description;

-- Update existing records to have default payment method
UPDATE transactions 
SET payment_method = 'cash' 
WHERE payment_method IS NULL;
