ALTER TABLE `bepado_shipping_costs`
    CHANGE COLUMN `sc_shop` `sc_from_shop` VARCHAR(32) NOT NULL,
    ADD COLUMN `sc_to_shop` VARCHAR(32) NOT NULL  AFTER `sc_from_shop`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`sc_from_shop`, `sc_to_shop`)
;
