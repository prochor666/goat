<?php
namespace GoatCore\Events;

use GoatCore\Base\Store;

/**
* Queue - runtime event buffer
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class Queue {

    protected $buffer;

    protected $container;

    protected $store;

    /**
    * Initialize event buffer
    * @return void
    */
    public function __construct()
    {
        $this->container = 'runtime';
        $this->buffer = [
            $this->container => [],
        ];
        $this->store = false;
        return $this;
    }


    /**
    * Add buffer event
    * @param array $event
    * @return bool
    */
    public function add($event): bool
    {
        if (isset($event['class']) && isset($event['method']) && isset($event['data'])) {

            $this->buffer[$this->container][] = $event;
            return true;
        }

        return false;
    }


    /**
    * Fire all events from container
    * @param string $container
    * @return array
    */
    public function fire($container = 'runtime'): array
    {
        $output = [];
        foreach ($this->selectContainer($container) as $event) {

            $output[] = $this->fireEvent($event);
        }

        return $output;
    }


    /**
    * Fire event, optionally can colllect instances fromn local store
    * @param string $event
    * @param object $store
    * @return mixed
    */
    protected function fireEvent($event)
    {
        if (is_object($this->store) && method_exists($this->store, 'getEntries')) {

            $fromStore = ark($this->store->getEntries(), $event['class'], false);

            if ($fromStore !== false) {

                return call_user_func_array([$this->store->entry($event['class']), $event['method']], [$event['data']]);
            }
        }

        return call_user_func_array([$event['class'], $event['method']], $event['data']);
    }


    /**
    * Get events container
    * @param string $container
    * @return array
    */
    protected function selectContainer($container = false): array
    {
        if ($container !== false) {

            if (ark($this->buffer, $container, false) !== false) {

                return $this->buffer[$container];
            }

            return [];
        }

        return $this->buffer['runtime'];
    }


    /**
    * Get all events, from container optionally
    * @param string $container
    * @return array
    */
    public function list($container = false): array
    {
        return $this->selectContainer($container);
    }


    /**
    * Get all containers
    * @return array
    */
    public function listContainers(): array
    {
        return array_keys($this->buffer);
    }


    /**
    * Set optional object store
    * @param object $store
    * @return void
    */
    public function setStore(Store $store): void
    {
        $this->store = $store;
    }
}
