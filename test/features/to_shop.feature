Feature: Product receives product updates from Bepado

    Scenario: Initial import
        Given The shop did not synchronize any products
          And Bepado has been configured to export 43 products
         When Export is triggered
         Then 43 updates are triggered

    Scenario: Chunked import
        Given Bepado has been configured to export 167 products
          And The shop already synchronized 100 exported products
         When Export is triggered
         Then 67 updates are triggered

    Scenario: Product delete
        Given Bepado has been configured to export 23 products
          And The shop already synchronized 23 exported products
          And 15 products have been updated
         When Export is triggered
         Then 15 updates are triggered

    Scenario: Product delete
        Given Bepado has been configured to export 23 products
          And The shop already synchronized 23 exported products
          And 15 products have been deleted
         When Export is triggered
         Then 15 updates are triggered
