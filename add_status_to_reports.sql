ALTER TABLE reports
ADD COLUMN status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending';

-- Update all existing reports to completed if they have a result
UPDATE reports SET status = 'completed' WHERE result IS NOT NULL; 