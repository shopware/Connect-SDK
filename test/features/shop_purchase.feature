Feature: Interactions between shops on a purchase

    Scenario: Successful purchase
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product is available in 1 shop
         When The Customer checks out
         Then The customer will receive the product
          And The remote shop recieves an anonymized email

    Scenario: Successful purchase from multiple shops
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And A customer adds a product from remote shop 2 to basket
          And The products are available in 2 shops
         When The Customer checks out
         Then The customer will receive the products

    Scenario: Product is not available any more in remote shop
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product is not available in remote shop
         When The Customer views the order overview
         Then The customer is informed about the unavailability
          And The product availability is updated in the local shop

    Scenario: Product fixed price changed in remote shop
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product price has changed in the remote shop
         When The Customer views the order overview
         Then The customer is informed about the unavailability
          And The product availability is updated in the local shop
          And No transaction is logged

    Scenario: Product non-fixed price changed in remote shop
        Given The product does not have a fixed price
          And A customer adds a product from remote shop 1 to basket
          And The product price has changed in the remote shop
         When The Customer checks out
         Then The customer will receive the product

    Scenario: Product was removed in remote shop
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product was deleted in the remote shop
         When The Customer views the order overview
         Then The customer is informed about the deleted product
          And The product is deleted in the local shop
          And No transaction is logged

    Scenario: Product is reserved in remote shop
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product data is still valid
         When The Customer views the order overview
         Then The product is reserved in the remote shop

    Scenario: Remote shop is asked for shipping costs on reservation
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product data is still valid
         When The Customer views the order overview
         Then The remote shop is asked for shipping costs
          And The shipping costs are contained in the reservation

    Scenario: The Buy process fails in checkout, because of availability change
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product changes availability between check and purchase
         When The Customer checks out
         Then The buy process fails and customer is informed about this
          And The product availability is updated in the local shop
          And No transactions are confirmed

    Scenario: The Buy process fails in checkout because of random error
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product is available in 1 shop
          And The remote shop denies the buy
         When The Customer checks out
         Then The buy process fails
          And No transactions are confirmed

    Scenario: The Buy process fails in checkout because transaction logging failed in remote shop
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product is available in 1 shop
          And The remote shop transaction logging fails
         When The Customer checks out
         Then The buy process fails
          And No transactions are confirmed

    Scenario: The Buy process fails in checkout because transaction logging failed in local shop
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product is available in 1 shop
          And The local shop transaction logging fails
         When The Customer checks out
         Then The buy process fails
          And No transactions are confirmed

    Scenario: The Buy process fails in checkout because transaction confirmation failed in remote shop
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product is available in 1 shop
          And The remote shop transaction confirmation fails
         When The Customer checks out
         Then The buy process fails
          And No transactions are confirmed

    Scenario: The Buy process fails in checkout because transaction confirmation failed in local shop
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product is available in 1 shop
          And The local shop transaction confirmation fails
         When The Customer checks out
         Then The buy process fails

    Scenario: The Buy succeeds and everything is logged
        Given The product is listed as available
          And A customer adds a product from remote shop 1 to basket
          And The product is available in 1 shop
         When The Customer checks out
         Then The customer will receive the product
          And The remote shop logs the transaction with Bepado
          And The local shop logs the transaction with Bepado
          And The remote shop confirms the transaction with Bepado
          And The local shop confirms the transaction with Bepado
