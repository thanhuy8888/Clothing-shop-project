ALTER TABLE products ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending';
