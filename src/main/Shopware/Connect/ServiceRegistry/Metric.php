<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\ServiceRegistry;

use Shopware\Connect\Rpc;
use Shopware\Connect\Struct;

use Shopware\Connect\SDK;

/**
 * Service registry, which measures calls and wraps responses
 */
class Metric extends Rpc\ServiceRegistry
{
    /**
     * Inner service registry
     *
     * @var Rpc\ServiceRegistry
     */
    protected $serviceRegistry;

    /**
     * Metric callbacks for certain RPC calls
     *
     * @var array
     */
    protected $metrics = [];

    /**
     * @var string
     */
    protected $pluginSoftwareVersion;

    /**
     * Construct from inner service registry
     *
     * @param Rpc\ServiceRegistry $serviceRegistry
     * @param string|null $pluginSoftwareVersion
     */
    public function __construct(Rpc\ServiceRegistry $serviceRegistry, $pluginSoftwareVersion = null)
    {
        $this->serviceRegistry = $serviceRegistry;
        $this->pluginSoftwareVersion = $pluginSoftwareVersion;
    }

    /**
     * @param string $name
     * @param array $commands
     * @param object $provider
     */
    public function registerService($name, array $commands, $provider)
    {
        return $this->serviceRegistry->registerService($name, $commands, $provider);
    }

    /**
     * @param string $name
     * @param string $command
     * @throws \UnexpectedValueException
     * @return array
     */
    public function getService($name, $command)
    {
        return $this->serviceRegistry->getService($name, $command);
    }

    /**
     * @param string $name
     * @param string $command
     * @param object $provider
     */
    public function registerMetric($name, $command, $provider)
    {
        $this->metrics[$name][$command] = $provider;
    }

    /**
     * Get registered metrics for given RPC call
     *
     * @param Struct\RpcCall $rpcCall
     * @return Struct\Metric[]
     */
    protected function getMetrics(Struct\RpcCall $rpcCall)
    {
        if (!isset($this->metrics[$rpcCall->service]) ||
            !isset($this->metrics[$rpcCall->service][$rpcCall->command])) {
            return [];
        }

        return call_user_func_array(
            [
                $this->metrics[$rpcCall->service][$rpcCall->command],
                $rpcCall->command
            ],
            $rpcCall->arguments
        );
    }

    /**
     * Dispatch RPC call
     *
     * Dispatches RPC call to involved service. Returns the return value from
     * the given service.
     *
     * @param Struct\RpcCall $rpcCall
     * @return mixed
     */
    public function dispatch(Struct\RpcCall $rpcCall)
    {
        $start = microtime(true);
        $version = strpos(SDK::VERSION, '$') === 0 ? 'dev' : SDK::VERSION;

        $response = new Struct\Response(
            [
                'result' => $this->serviceRegistry->dispatch($rpcCall),
                'metrics' => $this->getMetrics($rpcCall),
                'version' => sprintf('%s/%s', $version, $this->pluginSoftwareVersion)
            ]
        );

        $response->metrics[] = new Struct\Metric\Time(
            [
                'name' => 'sdk.' . $rpcCall->service . '.' . $rpcCall->command,
                'time' => microtime(true) - $start,
            ]
        );

        return $response;
    }
}
