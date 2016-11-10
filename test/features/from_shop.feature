Feature: Updates are executed using the shop admin interface or using the ERP.

    Scenario: Initial full import
        Given I have 87 products in my shop
          And I configured the update interval to 100 products per hour
         When Import is triggered for the 1. time
         Then 87 products are synchronized

    Scenario: Initial import with limited updated interval
        Given I have 123 products in my shop
          And I configured the update interval to 100 products per hour
         When Import is triggered for the 1. time
         Then 100 products are synchronized

    Scenario: Initial import from empty product set
        Given I have 0 products in my shop
          And I configured the update interval to 100 products per hour
         When Import is triggered for the 1. time
         Then 0 products are synchronized

    Scenario: Second import updates remaining products
        Given I have 123 products in my shop
          And I configured the update interval to 100 products per hour
         When Import is triggered for the 2. time
         Then All products are synchronized

    Scenario: Second import does nothing after full initial import
        Given I have 87 products in my shop
          And I configured the update interval to 100 products per hour
         When Import is triggered for the 2. time
         Then 0 products are synchronized

    Scenario: Remaining product imports and product updates are handled both
        Given I have 123 products in my shop
          And I configured the update interval to 100 products per hour
          And I update 40 products
         When Import is triggered for the 2. time
         Then All products are synchronized

    Scenario: Product updates are executed partially depending on interval constraints
        Given I have 123 products in my shop
          And I configured the update interval to 100 products per hour
          And All products are already syncronized
          And I update 123 products
         When Import is triggered
         Then 100 products are synchronized

    Scenario: Product deletes are synchronzied
        Given I have 123 products in my shop
          And I configured the update interval to 100 products per hour
          And All products are already syncronized
          And I remove 42 products
         When Import is triggered
         Then 42 products are deleted

    Scenario: Product deletes are synchronzied partially depending on interval constraints
        Given I have 123 products in my shop
          And I configured the update interval to 100 products per hour
          And All products are already syncronized
          And I remove 121 products
         When Import is triggered
         Then 100 products are deleted
            # Should actually all 121 delete operations be synchronized?

    Scenario: Additional products are synchronzied partially depending on interval constraints
        Given I have 123 products in my shop
          And I configured the update interval to 100 products per hour
          And All products are already syncronized
          And I add 121 products
         When Import is triggered
         Then 100 products are synchronized

    Scenario: Product availability changes are synchronzied
      Given I have 123 products in my shop
      And I configured the update interval to 100 products per hour
      And All products are already syncronized
      And I change availability of 42 products
      When Import is triggered
      Then 42 products availability is synchronized

    Scenario: Product availability changes are synchronized partially depending on interval constraints
      Given I have 123 products in my shop
      And I configured the update interval to 100 products per hour
      And All products are already syncronized
      And I change availability of 121 products
      When Import is triggered
      Then 100 products availability is synchronized
