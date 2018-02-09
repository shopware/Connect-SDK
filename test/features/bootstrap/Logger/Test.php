<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Logger;

use Shopware\Connect\Logger;
use Shopware\Connect\Struct;

/**
 * Base class for logger implementations
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
class Test extends Logger
{
    /**
     * Log messages
     *
     * @var array
     */
    protected $logMessages = [];

    protected $counter = 0;

    protected $breaks = [];

    /**
     * Log order
     *
     * @param Struct\Order $order
     * @return void
     */
    protected function doLog(Struct\Order $order)
    {
        $this->counter += 1;
        if (isset($this->breaks[$this->counter])) {
            throw new \RuntimeException('Break logging.');
        }

        $this->logMessages[$this->counter] = $order;

        return 'confirm-' . $this->counter;
    }

    /**
     * Confirm logging
     *
     * @param string $logTransactionId
     * @return void
     */
    public function confirm($logTransactionId)
    {
        $this->counter += 1;
        if (isset($this->breaks[$this->counter])) {
            throw new \RuntimeException('Break logging.');
        }

        $this->logMessages[$this->counter] = $logTransactionId;
    }

    public function breakOnLogMessage($number)
    {
        $this->breaks[$number] = true;
    }

    /**
     * Get log messages occured during test run
     *
     * @return array
     */
    public function getLogMessages()
    {
        return $this->logMessages;
    }
}
