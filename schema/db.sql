CREATE TABLE `product` (
    `jan_code` char(255) NOT NULL,
    `name` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`jan_code`)
);

CREATE TABLE `brand` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(2000) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`) USING HASH
);

CREATE TABLE `maker` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(2000) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`) USING HASH
);

CREATE TABLE `product_tag` (
    `jan_code` char(255) DEFAULT NULL,
    `tags` varchar(5000) DEFAULT NULL,
    `extracted_from` enum('description', 'review') DEFAULT NULL,
    KEY `jan_code` (`jan_code`),
    CONSTRAINT `product_tag_ibfk_1` FOREIGN KEY (`jan_code`) REFERENCES `product` (`jan_code`)
);

CREATE TABLE `product_attribute` (
    `jan_code` char(255) DEFAULT NULL,
    `attributes` varchar(5000) DEFAULT NULL,
    KEY `jan_code` (`jan_code`),
    CONSTRAINT `product_attribute_ibfk_1` FOREIGN KEY (`jan_code`) REFERENCES `product` (`jan_code`)
);

CREATE TABLE `product_brand` (
    `jan_code` char(255) DEFAULT NULL,
    `brand_id` int(11) DEFAULT NULL,
    KEY `jan_code` (`jan_code`),
    KEY `brand_id` (`brand_id`),
    CONSTRAINT `product_brand_ibfk_1` FOREIGN KEY (`jan_code`) REFERENCES `product` (`jan_code`),
    CONSTRAINT `product_brand_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`id`)
);

CREATE TABLE `product_maker` (
    `jan_code` char(255) DEFAULT NULL,
    `maker_id` int(11) DEFAULT NULL,
    KEY `jan_code` (`jan_code`),
    KEY `maker_id` (`maker_id`),
    CONSTRAINT `product_maker_ibfk_1` FOREIGN KEY (`jan_code`) REFERENCES `product` (`jan_code`),
    CONSTRAINT `product_maker_ibfk_2` FOREIGN KEY (`maker_id`) REFERENCES `maker` (`id`)
);