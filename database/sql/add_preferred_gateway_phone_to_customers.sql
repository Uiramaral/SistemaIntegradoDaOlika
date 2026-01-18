ALTER TABLE `customers` 
ADD COLUMN `preferred_gateway_phone` varchar(20) NULL AFTER `phone`,
ADD INDEX `idx_preferred_gateway_phone` (`preferred_gateway_phone`);

