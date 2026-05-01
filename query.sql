ALTER TABLE `products`
	ADD COLUMN `company_id` BIGINT UNSIGNED NOT NULL AFTER `id`,
	ADD INDEX `company_id` (`company_id`),
	ADD CONSTRAINT `FK_products_companys` FOREIGN KEY (`company_id`) REFERENCES `companys` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
