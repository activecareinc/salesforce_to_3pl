CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `salesforce_order_id` VARCHAR(45) NOT NULL,
  `tracking_number` VARCHAR(255) NULL,
  `order_expiration_date` DATETIME NULL,
  `lot_code` VARCHAR(255) NULL,
  `imei_number` VARCHAR(255) NULL,
  `carrier` VARCHAR(255) NULL,
  `shipping_service` VARCHAR(255) NULL,
  `customer` VARCHAR(255) NOT NULL,
  `order_ref_number` VARCHAR(255) NULL,
  `ship_to_name` VARCHAR(255) NULL,
  `order_date_created` DATETIME NULL,
  `is_import_3pl` TINYINT NOT NULL DEFAULT 0,
  `date_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`));


