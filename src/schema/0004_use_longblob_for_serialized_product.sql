ALTER TABLE `bepado_change` MODIFY `c_product` LONGBLOB NULL;

--//@UNDO

ALTER TABLE `bepado_change` MODIFY `c_product` BLOB NULL;
