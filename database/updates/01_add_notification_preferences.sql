-- Update script to add notification preference columns to users table
-- Run this if you already have the database installed

ALTER TABLE users
ADD COLUMN email_notifications BOOLEAN DEFAULT 1 AFTER created_at,
ADD COLUMN reminder_notifications BOOLEAN DEFAULT 1 AFTER email_notifications,
ADD COLUMN update_notifications BOOLEAN DEFAULT 1 AFTER reminder_notifications;
