<?php

namespace Evo\Cache;

use APCUIterator;

final class APCU implements CacheInterface {

    function get(string $name)
    {
        return apcu_fetch($name);
    }

    function set(string $name, $data)
    {
        return apcu_store($name, $data);
    }

    function has(string $name): bool
    {
        return apcu_exists($name);
    }

    function delete(string $name): int
    {
        return apcu_delete($name);
    }

    function clear($name = null): int
    {
        $iterator = new APCUIterator(is_string($name) ? '#^'.$name.'#' : null);
        $count = $iterator->getTotalCount();
        if(!apcu_clear_cache()) {
            return false;
        }
        return $count;
    }

    function getAll(): array
    {
        $iter = new APCUIterator();
        $data = [];
        foreach ($iter as $item) {
            $data[$item['key']] = $item['value'];
        }
        return $data;
    }
}