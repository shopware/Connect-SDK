# Behat tests

Rough inner workings description:

Through test/features/bootstrap/ShopGateway/DirectAccess.php and
test/features/bootstrap/ShopFactory/DirectAccess.php an SDK is created for the
remote shop within the local shop SDK during the tests. Both parts of the
local+remote stack during a transaction are tested in a single process this
way.
