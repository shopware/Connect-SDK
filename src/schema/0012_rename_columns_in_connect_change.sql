ALTER TABLE `sw_connect_change` CHANGE `c_source_id` `c_entity_id` VARCHAR(64) NOT NULL;
ALTER TABLE `sw_connect_change` CHANGE `c_product` `c_payload` LONGBLOB NULL DEFAULT NULL;