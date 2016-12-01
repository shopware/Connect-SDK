<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Gateway;

use Shopware\Connect\Gateway;
use Shopware\Connect\Struct;
use Shopware\Connect\ShippingCosts\Rules;

/**
 * Default MySQLi implementation of the storage gateway
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
class MySQLi extends Gateway
{
    /**
     * MySQLi connection
     *
     * @var \Shopware\Connect\MySQLi
     */
    protected $connection;

    /**
     * Struct classes used for operations
     *
     * @var array
     */
    protected $operationStruct = array(
        self::PRODUCT_INSERT => '\\Shopware\\Connect\\Struct\\Change\\FromShop\\Insert',
        self::PRODUCT_UPDATE => '\\Shopware\\Connect\\Struct\\Change\\FromShop\\Update',
        self::PRODUCT_DELETE => '\\Shopware\\Connect\\Struct\\Change\\FromShop\\Delete',
        self::PRODUCT_STOCK => '\\Shopware\\Connect\\Struct\\Change\\FromShop\\Availability',
        self::STREAM_ASSIGNMENT => '\\Shopware\\Connect\\Struct\\Change\\FromShop\\StreamAssignment',
        self::STREAM_DELETE => '\\Shopware\\Connect\\Struct\\Change\\FromShop\\StreamDelete',
        self::MAIN_VARIANT => '\\Shopware\\Connect\\Struct\\Change\\FromShop\\MakeMainVariant',
        self::PAYMENT_UPDATE => '\\Shopware\\Connect\\Struct\\Change\\FromShop\\UpdatePaymentStatus',
    );

    /**
     * @var array
     */
    protected $types = array(
        self::TYPE_PRODUCT => array(
            self::PRODUCT_INSERT,
            self::PRODUCT_UPDATE,
            self::PRODUCT_DELETE,
            self::PRODUCT_STOCK,
            self::STREAM_ASSIGNMENT,
            self::STREAM_DELETE,
            self::MAIN_VARIANT,
        ),
        self::TYPE_PAYMENT => array(
            self::PAYMENT_UPDATE
        ),
    );

    /**
     * Construct from MySQL connection
     *
     * @param \Shopware\Connect\MySQLi $connection
     */
    public function __construct(\Shopware\Connect\MySQLi $connection)
    {
        $this->connection = $connection;
    }

    protected function doNextChange($offset, $limit, array $types)
    {
        $offset = $offset ?: 0;
        // Float type cast does NOT work here, since the inaccuracy of floating
        // point representations otherwise omit changes. Yes, this actually
        // really happens.
        if (!preg_match('(^[\\d\\.]+$)', $offset)) {
            throw new \InvalidArgumentException("Offset revision must be a numeric string.");
        }

        $inStatement = implode("','", $types);

        $result = $this->connection->query(
            "SELECT
                `c_entity_id`,
                `c_operation`,
                `c_revision`,
                `c_payload`
            FROM
                `sw_connect_change`
            WHERE
                `c_revision` > $offset
            AND
                `c_operation` IN('$inStatement')
            ORDER BY `c_revision` ASC
            LIMIT
                " . ((int) $limit)
        );

        $changes = array();
        while ($row = $result->fetch_assoc()) {
            $class = $this->operationStruct[$row['c_operation']];
            $changes[] = $change = new $class(
                array(
                    'sourceId' => $row['c_entity_id'],
                    'revision' => $row['c_revision'],
                )
            );

            if ($row['c_payload'] !== null) {
                switch ($row['c_operation']) {
                    case self::PRODUCT_STOCK:
                        $change->availability = intval($row['c_payload']);
                        break;
                    case self::STREAM_ASSIGNMENT:
                        $payload = unserialize($row['c_payload']);
                        $change->supplierStreams = $payload['supplierStreams'];
                        $change->groupId = $payload['groupId'];
                        break;
                    case self::PAYMENT_UPDATE:
                        $change->paymentStatus = unserialize($row['c_payload']);
                        break;
                    case self::MAIN_VARIANT:
                        $payload = unserialize($row['c_payload']);
                        $change->groupId = $payload['groupId'];
                        break;
                    default:
                        $change->product = $this->ensureUtf8(unserialize($row['c_payload']));
                }
            }
        }

        return $changes;
    }

    /**
     * Get next change
     *
     * The offset specified the revision to start from
     *
     * May remove all pending changes, which are prior to the last requested
     * revision.
     *
     * @param string $offset
     * @param int $limit
     * @return Struct\Change[]
     */
    public function getNextChanges($offset, $limit)
    {
        return $this->doNextChange($offset, $limit, $this->types[self::TYPE_PRODUCT]);
    }

    public function getNextPaymentStatusChanges($offset, $limit)
    {
        return $this->doNextChange($offset, $limit, $this->types[self::TYPE_PAYMENT]);
    }

    public function cleanChangesUntil($offset)
    {
        $offset = $offset ?: 0;
        // Float type cast does NOT work here, since the inaccuracy of floating
        // point representations otherwise omit changes. Yes, this actually
        // really happens.
        if (!preg_match('(^[\\d\\.]+$)', $offset)) {
            throw new \InvalidArgumentException("Offset revision must be a numeric string.");
        }

        // Disable cleanup for the first betas for debuggability and easier re-runs.
        $this->connection->query(
            'DELETE FROM
                sw_connect_change
            WHERE
                c_revision <= ' . $offset
        );
    }

    private function ensureUtf8($product)
    {
        foreach (get_object_vars($product) as $name => $value) {
            if (!is_string($value)) {
                continue;
            }
            if (@iconv('UTF-8', 'UTF-8', $value)) {
                continue;
            }
            $product->$name = @iconv("UTF-8", "UTF-8//TRANSLIT", $value);
        }
        return $product;
    }

    /**
     * Get unprocessed changes count
     *
     * The offset specified the revision to start from
     *
     * @param string $offset
     * @param int $limit
     * @return int
     */
    public function getUnprocessedChangesCount($offset, $limit)
    {
        $offset = $offset ?: 0;
        $inStatement = implode("','", $this->types['product']);

        $result = $this->connection->query(
            "EXPLAIN SELECT
                *
            FROM
                `sw_connect_change`
            WHERE
                `c_revision` > " . $this->connection->real_escape_string($offset) . "
            AND
                  `c_operation` IN('$inStatement')"
        );

        $row = $result->fetch_assoc();
        return max(0, $row['rows'] - $limit);
    }

    /**
     * Record product insert
     *
     * @param string $id
     * @param string $hash
     * @param string $revision
     * @param Struct\Product $product
     * @return void
     */
    public function recordInsert($id, $hash, $revision, Struct\Product $product)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_change (
                    `c_entity_id`,
                    `c_operation`,
                    `c_revision`,
                    `c_payload`
                )
            VALUES (
                "' . $this->connection->real_escape_string($id) . '",
                "' . self::PRODUCT_INSERT . '",
                "' . $this->connection->real_escape_string($revision) . '",
                "' . $this->connection->real_escape_string(serialize($product)) . '"
            );'
        );

        $this->updateHash($id, $hash);
    }

    /**
     * Record product update
     *
     * @param string $id
     * @param string $hash
     * @param string $revision
     * @param Struct\Product $product
     * @return void
     */
    public function recordUpdate($id, $hash, $revision, Struct\Product $product)
    {
        $sql = 'SELECT p_hash FROM sw_connect_product ' .
               'WHERE p_source_id = "' . $this->connection->real_escape_string($id) . '"';

        $row = $this->connection
            ->query($sql)
            ->fetch_assoc();
        $currentHash = $row['p_hash'];

        if ($currentHash === $hash) {
            return;
        }

        $this->connection->query(
            'INSERT INTO
                sw_connect_change (
                    `c_entity_id`,
                    `c_operation`,
                    `c_revision`,
                    `c_payload`
                )
            VALUES (
                "' . $this->connection->real_escape_string($id) . '",
                "' . self::PRODUCT_UPDATE . '",
                "' . $this->connection->real_escape_string($revision) . '",
                "' . $this->connection->real_escape_string(serialize($product)) . '"
            );'
        );

        $this->updateHash($id, $hash);
    }

    /**
     * Record product availability update
     *
     * @param string $id
     * @param string $hash
     * @param string $revision
     * @param Struct\Product $product
     * @return void
     */
    public function recordAvailabilityUpdate($id, $hash, $revision, Struct\Product $product)
    {
        $sql = 'SELECT p_hash FROM sw_connect_product ' .
            'WHERE p_source_id = "' . $this->connection->real_escape_string($id) . '"';

        $row = $this->connection
            ->query($sql)
            ->fetch_assoc();
        $currentHash = $row['p_hash'];

        if ($currentHash === $hash) {
            return;
        }

        $this->connection->query(
            'INSERT INTO
                sw_connect_change (
                    `c_entity_id`,
                    `c_operation`,
                    `c_revision`,
                    `c_payload`
                )
            VALUES (
                "' . $this->connection->real_escape_string($id) . '",
                "' . self::PRODUCT_STOCK . '",
                "' . $this->connection->real_escape_string($revision) . '",
                "' . $this->connection->real_escape_string($product->availability) . '"
            );'
        );

        $this->updateHash($id, $hash);
    }

    /**
     * Record stream assignment
     *
     * @param string $productId
     * @param string $revision
     * @param array $supplierStreams
     * @param string|null $groupId
     */
    public function recordStreamAssignment($productId, $revision, array $supplierStreams, $groupId = null)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_change (
                    `c_entity_id`,
                    `c_operation`,
                    `c_revision`,
                    `c_payload`
                )
            VALUES (
                "' . $this->connection->real_escape_string($productId) . '",
                "' . self::STREAM_ASSIGNMENT . '",
                "' . $this->connection->real_escape_string($revision) . '",
                "' . $this->connection->real_escape_string(serialize(array('groupId' => $groupId, 'supplierStreams' => $supplierStreams))) . '"
            );'
        );
    }

    /**
     * @param $streamId
     * @param $revision
     */
    public function recordStreamDelete($streamId, $revision)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_change (
                    `c_entity_id`,
                    `c_operation`,
                    `c_revision`
                )
            VALUES (
                "' . $this->connection->real_escape_string($streamId) . '",
                "' . self::STREAM_DELETE . '",
                "' . $this->connection->real_escape_string($revision) . '"
            );'
        );
    }

    /**
     * @param $productId
     * @param $revision
     * @param $groupId
     */
    public function makeMainVariant($productId, $revision, $groupId)
    {
        $this->connection->query(
            'INSERT INTO
                 sw_connect_change (
                     `c_entity_id`,
                     `c_operation`,
                     `c_revision`,
                     `c_payload`
                 )
             VALUES (
                 "' . $this->connection->real_escape_string($productId) . '",
                 "' . self::MAIN_VARIANT . '",
                 "' . $this->connection->real_escape_string($revision) . '",
                 "' . $this->connection->real_escape_string(serialize(array('groupId' => $groupId))) . '"
             );'
        );
    }

    /**
     * Update payment status
     *
     * @param $revision
     * @param Struct\PaymentStatus $paymentStatus
     * @return void
     */
    public function updatePaymentStatus($revision, Struct\PaymentStatus $paymentStatus)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_change (
                    `c_entity_id`,
                    `c_operation`,
                    `c_revision`,
                    `c_payload`
                )
            VALUES (
                "' . $this->connection->real_escape_string($paymentStatus->localOrderId) . '",
                "' . self::PAYMENT_UPDATE . '",
                "' . $this->connection->real_escape_string($revision) . '",
                "' . $this->connection->real_escape_string(serialize($paymentStatus)) . '"
            );'
        );
    }

    /**
     * Update hash for product
     *
     * Updates the hash of exisitng products or inserts the hash, if product is
     * not yet in database.
     *
     * @param string $productId
     * @param string $hash
     * @return void
     */
    protected function updateHash($productId, $hash)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_product
                (p_source_id, p_hash)
            VALUES
                (
                    "' . $this->connection->real_escape_string($productId) . '",
                    "' . $this->connection->real_escape_string($hash) . '"
                )
            ON DUPLICATE KEY UPDATE
                p_hash = "' . $this->connection->real_escape_string($hash) . '";'
        );
    }

    /**
     * Record product delete
     *
     * @param string $id
     * @param string $hash
     * @param string $revision
     * @return void
     */
    public function recordDelete($id, $revision)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_change (
                    `c_entity_id`,
                    `c_operation`,
                    `c_revision`
                )
            VALUES (
                "' . $this->connection->real_escape_string($id) . '",
                "' . self::PRODUCT_DELETE . '",
                "' . $this->connection->real_escape_string($revision) . '"
            );'
        );

        $this->connection->query(
            'DELETE FROM
                sw_connect_product
            WHERE
                p_source_id = "' . $this->connection->real_escape_string($id) . '"
            ;'
        );
    }

    /**
     * Check if product has changed
     *
     * Return true, if product chenged since last check.
     *
     * @param string $id
     * @param string $hash
     * @return boolean
     */
    public function hasChanged($id, $hash)
    {
        $result = $this->connection->query(
            'SELECT
                `p_hash`
            FROM
                `sw_connect_product`
            WHERE
                p_source_id = "' . $this->connection->real_escape_string($id) . '"'
        );

        $row = $result->fetch_assoc();
        return $row['p_hash'] !== $hash;
    }

    /**
     * Get IDs of all recorded products
     *
     * @return string[]
     */
    public function getAllProductIDs()
    {
        $result = $this->connection->query(
            'SELECT
                `p_source_id`
            FROM
                `sw_connect_product`'
        );

        return array_map(
            function ($row) {
                return $row['p_source_id'];
            },
            $result->fetch_all(\MYSQLI_ASSOC)
        );
    }

    /**
     * Get last processed import revision
     *
     * @return string
     */
    public function getLastRevision()
    {
        $result = $this->connection->query(
            'SELECT
                `d_value`
            FROM
                `sw_connect_data`
            WHERE
                `d_key` = "revision"'
        );

        $rows = $result->fetch_all(\MYSQLI_ASSOC);
        if (!count($rows)) {
            return null;
        }

        return $rows[0]['d_value'];
    }

    /**
     * Store last processed import revision
     *
     * @param string $revision
     * @return void
     */
    public function storeLastRevision($revision)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_data (
                    `d_key`,
                    `d_value`
                )
            VALUES (
                "revision",
                "' . $this->connection->real_escape_string($revision) . '"
            )
            ON DUPLICATE KEY UPDATE
                `d_value` = "' . $this->connection->real_escape_string($revision) . '"
            ;'
        );
    }

    /**
     * Update shop configuration
     *
     * @param string $shopId
     * @param Struct\ShopConfiguration $configuration
     * @return void
     */
    public function setShopConfiguration($shopId, Struct\ShopConfiguration $configuration)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_shop_config (
                    `s_shop`,
                    `s_config`
                )
            VALUES (
                "' . $this->connection->real_escape_string($shopId) . '",
                "' . $this->connection->real_escape_string(serialize($configuration)) . '"
            )
            ON DUPLICATE KEY UPDATE
                `s_config` = "' . $this->connection->real_escape_string(serialize($configuration)) . '"
            ;'
        );
    }

    /**
     * Get configuration for the given shop
     *
     * @param string $shopId
     * @throws \RuntimeException If shop does not exist in configuration.
     * @return Struct\ShopConfiguration
     */
    public function getShopConfiguration($shopId)
    {
        $result = $this->connection->query(
            'SELECT
                `s_config`
            FROM
                `sw_connect_shop_config`
            WHERE
                `s_shop` = "' . $this->connection->real_escape_string($shopId) . '"'
        );

        $rows = $result->fetch_all(\MYSQLI_ASSOC);

        if (!count($rows)) {
            throw new \RuntimeException(
                'You are not connected to shop ' . $shopId . '.'
            );
        }

        return unserialize($rows[0]['s_config']);
    }

    /**
     * Set own shop ID
     *
     * @param string $shopId
     * @return void
     */
    public function setShopId($shopId)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_shop_config (
                    `s_shop`,
                    `s_config`
                )
            VALUES (
                "_self_",
                "' . $this->connection->real_escape_string($shopId) . '"
            )
            ON DUPLICATE KEY UPDATE
                `s_config` = "' . $this->connection->real_escape_string($shopId) . '"
            ;'
        );

        $this->connection->query(
            'INSERT INTO
                sw_connect_shop_config (
                    `s_shop`,
                    `s_config`
                )
            VALUES (
                "_last_update_",
                ' . $this->connection->real_escape_string(time()) . '
            )
            ON DUPLICATE KEY UPDATE
                `s_config` = "' . $this->connection->real_escape_string(time()) . '"
            ;'
        );
    }

    /**
     * Get own shop ID
     *
     * Returns null if the shop ID is not set, yet.
     *
     * @return string|null
     */
    public function getShopId()
    {
        $result = $this->connection->query(
            'SELECT
                `s_config`
            FROM
                `sw_connect_shop_config`
            WHERE
                `s_shop` = "_self_"'
        );

        $rows = $result->fetch_all(\MYSQLI_ASSOC);
        if (!count($rows)) {
            return null;
        }

        return $rows[0]['s_config'];
    }

    /**
     * Get all connected shop ids.
     *
     * @return array<string>
     */
    public function getConnectedShopIds()
    {
        $result = $this->connection->query(
            'SELECT
                `s_shop`
            FROM
                `sw_connect_shop_config`'
        );

        return array_filter(
            array_map(
                function ($row) {
                    return $row['s_shop'];
                },
                $result->fetch_all(\MYSQLI_ASSOC)
            ),
            'is_numeric'
        );
    }

    /**
     * Get last shop verification date as Unix UTC timestamp
     *
     * @return int
     */
    public function getLastVerificationDate()
    {
        $result = $this->connection->query(
            'SELECT
                `s_config`
            FROM
                `sw_connect_shop_config`
            WHERE
                `s_shop` = "_last_update_"'
        );

        $rows = $result->fetch_all(\MYSQLI_ASSOC);
        if (!count($rows)) {
            return false;
        }

        return $rows[0]['s_config'];
    }

    /**
     * Create and store reservation
     *
     * Returns the reservation Id
     *
     * @param Struct\Order $order
     * @return string
     */
    public function createReservation(Struct\Order $order)
    {
        $reservationId = md5(microtime());
        $this->connection->query(
            'INSERT INTO
                `sw_connect_reservations` (
                    `r_id`,
                    `r_state`,
                    `r_order`
                )
            VALUES (
                "' . $this->connection->real_escape_string($reservationId) . '",
                "new",
                "' . $this->connection->real_escape_string(serialize($order)) . '"
            );'
        );

        return $reservationId;
    }

    /**
     * Get order for reservation Id
     *
     * @param string $reservationId
     * @return Struct\Order
     */
    public function getOrder($reservationId)
    {
        $result = $this->connection->query(
            'SELECT
                `r_order`
            FROM
                `sw_connect_reservations`
            WHERE
                `r_id` = "' . $this->connection->real_escape_string($reservationId) . '";'
        );

        $rows = $result->fetch_all();
        if (!count($rows)) {
            throw new \OutOfBoundsException("Reservation $reservationId not found.");
        }

        return unserialize($rows[0][0]);
    }

    /**
     * Set reservation as bought
     *
     * @param string $reservationId
     * @param Struct\Order $order
     * @return void
     */
    public function setBought($reservationId, Struct\Order $order)
    {
        $this->connection->query(
            'UPDATE
                `sw_connect_reservations`
            SET
                `r_state` = "bought",
                `r_order` = "' . $this->connection->real_escape_string(serialize($order)) . '"
            WHERE
                `r_id` = "' . $this->connection->real_escape_string($reservationId) . '"
            ;'
        );

        if ($this->connection->affected_rows !== 1) {
            throw new \OutOfBoundsException("Reservation $reservationId not found.");
        }
    }

    /**
     * Set reservation as confirmed
     *
     * @param string $reservationId
     * @return void
     */
    public function setConfirmed($reservationId)
    {
        $this->connection->query(
            'UPDATE
                `sw_connect_reservations`
            SET
                `r_state` = "confirmed"
            WHERE
                `r_id` = "' . $this->connection->real_escape_string($reservationId) . '"
            ;'
        );

        if ($this->connection->affected_rows !== 1) {
            throw new \OutOfBoundsException("Reservation $reservationId not found.");
        }
    }

    /**
     * Get last revision
     *
     * @return string
     */
    public function getLastShippingCostsRevision()
    {
        $result = $this->connection->query(
            'SELECT
                MAX(`sc_revision`)
            FROM
                `sw_connect_shipping_costs`'
        );

        $rows = $result->fetch_all();
        if (!count($rows)) {
            return null;
        }

        return $rows[0][0];
    }

    /**
     * Store shop shipping costs
     *
     * @param string $fromShop
     * @param string $toShop
     * @param string $revision
     * @param \Shopware\Connect\ShippingCosts\Rules $intershopCosts
     * @param \Shopware\Connect\ShippingCosts\Rules $customerCosts
     * @return void
     */
    public function storeShippingCosts($fromShop, $toShop, $revision, Rules $intershopCosts, Rules $customerCosts)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_shipping_costs (
                    `sc_from_shop`,
                    `sc_to_shop`,
                    `sc_revision`,
                    `sc_customer_costs`
                )
            VALUES (
               "' . $this->connection->real_escape_string($fromShop) . '",
               "' . $this->connection->real_escape_string($toShop) . '",
               "' . $this->connection->real_escape_string($revision) . '",
               "' . $this->connection->real_escape_string(serialize($intershopCosts)) . '"
               "' . $this->connection->real_escape_string(serialize($customerCosts)) . '"
            )
            ON DUPLICATE KEY UPDATE
                `sc_revision` = "' . $this->connection->real_escape_string($revision) . '",
                `sc_shipping_costs` = "' . $this->connection->real_escape_string(serialize($intershopCosts)) . '",
                `sc_customer_costs` = "' . $this->connection->real_escape_string(serialize($customerCosts)) . '"
            ;'
        );
    }

    /**
     * Get shop shipping costs
     *
     * @param string $fromShop
     * @param string $toShop
     * @return \Shopware\Connect\ShippingCosts\Rules
     */
    public function getShippingCosts($fromShop, $toShop, $type = self::SHIPPING_COSTS_INTERSHOP)
    {
        $column = ($type === self::SHIPPING_COSTS_CUSTOMER)
            ? 'sc_customer_costs'
            : 'sc_shipping_costs';

        $result = $this->connection->query(
            'SELECT `' . $column . '`
            FROM
                `sw_connect_shipping_costs`
            WHERE
                `sc_from_shop` = "' . $this->connection->real_escape_string($fromShop) . '" AND
                `sc_to_shop` = "' . $this->connection->real_escape_string($toShop) . '"
            ORDER BY `sc_revision` DESC
            LIMIT 1'
        );

        $rows = $result->fetch_all();

        if (!count($rows)) {
            throw new \OutOfBoundsException("Shipping costs for shops $fromShop-$toShop not found.");
        }

        return unserialize($rows[0][0]);
    }

    /**
     * Set the shops billing address used in orders.
     *
     * @param \Shopware\Connect\Struct\Address $address
     */
    public function setBillingAddress(Struct\Address $address)
    {
        $this->setConfig('_billing_address_', serialize($address));
    }

    /**
     * @return \Shopware\Connect\Struct\Address
     */
    public function getBillingAddress()
    {
        $address = $this->getConfig('_billing_address_');

        if ($address) {
            return unserialize($address);
        }

        return null;
    }

    public function setConfig($name, $value)
    {
        $this->connection->query(
            'INSERT INTO
                sw_connect_shop_config (
                    `s_shop`,
                    `s_config`
                )
            VALUES (
                "' . $this->connection->real_escape_string($name) . '",
                "' . $this->connection->real_escape_string($value) . '"
            )
            ON DUPLICATE KEY UPDATE
                `s_config` = VALUES(`s_config`)
            ;'
        );
    }

    public function getConfig($name)
    {
        $result = $this->connection->query(
            'SELECT
                `s_config`
            FROM
                `sw_connect_shop_config`
            WHERE
                `s_shop` = "' . $this->connection->real_escape_string($name) . '"'
        );

        $rows = $result->fetch_all(\MYSQLI_ASSOC);
        if (!count($rows)) {
            return null;
        }

        return $rows[0]['s_config'];
    }
}
