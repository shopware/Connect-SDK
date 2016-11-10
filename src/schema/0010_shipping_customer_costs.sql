ALTER TABLE `bepado_shipping_costs`
    ADD COLUMN `sc_customer_costs` LONGBLOB NOT NULL AFTER `sc_shipping_costs`
;
