<?php

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\ScenarioEvent;

use Shopware\Connect\MySQLi;

require __DIR__ . '/../../../vendor/autoload.php';

require __DIR__ . '/FromShopContext.php';
require __DIR__ . '/ToShopContext.php';
require __DIR__ . '/ShopPurchaseContext.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->useContext('fromShop', new \Shopware\Connect\FromShopContext());
        $this->useContext('toShop', new \Shopware\Connect\ToShopContext());
        $this->useContext('shopPurchase', new \Shopware\Connect\ShopPurchaseContext());
        $this->useContext('category', new \Shopware\Connect\CategoryContext());
    }

    /**
     * @BeforeScenario
     */
    public function setupDatabase(ScenarioEvent $event)
    {
        $connection = $this->createConnection();
        foreach ($this->getSubcontexts() as $context) {
            $context->initSDK($connection);
        }
    }

    private function createConnection()
    {
        $config = @parse_ini_file(__DIR__ . '/../../../build.properties');
        $storage = getenv('STORAGE') ?: 'InMemory';

        switch ($storage) {
            case 'MySQLi':
                $connection = new MySQLi(
                    $config['db.hostname'],
                    $config['db.userid'],
                    $config['db.password'],
                    $config['db.name']
                );
                break;

            case 'PDO':
                $connection = new \PDO(
                    sprintf(
                        'mysql:dbname=%s;host=%s',
                        $config['db.name'],
                        $config['db.hostname']
                    ),
                    $config['db.userid'],
                    $config['db.password']
                );
                $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                break;

            default:
                $connection = null;
                break;
        }

        if ($connection) {
            $connection->query('TRUNCATE TABLE sw_connect_change;');
            $connection->query('TRUNCATE TABLE sw_connect_product;');
            $connection->query('TRUNCATE TABLE sw_connect_data;');
            $connection->query('TRUNCATE TABLE sw_connect_reservations;');
            $connection->query('TRUNCATE TABLE sw_connect_shipping_costs;');
        }

        return $connection;
    }
}
