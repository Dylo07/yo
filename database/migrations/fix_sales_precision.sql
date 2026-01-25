-- Fix sales table column precision to support values over 1,000,000
ALTER TABLE sales MODIFY COLUMN total_price DECIMAL(12,2) DEFAULT 0;
ALTER TABLE sales MODIFY COLUMN total_recieved DECIMAL(12,2) DEFAULT 0;
ALTER TABLE sales MODIFY COLUMN `change` DECIMAL(12,2) DEFAULT 0;

-- Fix sale_details table to use decimal instead of integer
ALTER TABLE sale_details MODIFY COLUMN menu_price DECIMAL(12,2);
