-- Table: Shipping Costs (sc)
CREATE TABLE IF NOT EXISTS `bepado_shipping_costs` (
  `sc_shop` VARCHAR(32) NOT NULL,
  `sc_revision` VARCHAR(32) NOT NULL,
  `sc_shipping_costs` LONGBLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sc_shop`),
  INDEX (`sc_revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
