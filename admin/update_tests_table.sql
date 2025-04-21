-- Add missing columns to tests table
ALTER TABLE tests
ADD COLUMN description TEXT NULL AFTER method,
ADD COLUMN preparation TEXT NULL AFTER sample_type,
ADD COLUMN reporting_time VARCHAR(50) NULL AFTER preparation; 