<?php

class Cache {
    private $cache;
    public function __construct() { $this->cache = array(); }
    public function storeItem($item_key, $item) { $this->cache[$item_key] = $item; }
    public function getItem($item_key) { return isset($this->cache[$item_key]) ? $this->cache[$item_key] : null; }
}

class DataStore {
    public function getItem($item_key) {
        $data = array(
            '222'=>array('val'=>'my item'),
            '123'=>array('val'=>'another item'));
        return isset($data[$item_key]) ? $data[$item_key] : null;
    }
}



$cache = new Cache();
$cache->storeItem('555', array('val'=>'cached item'));

function getItem($item_id, $cache) {
    $data_store = new DataStore();
    $item = $cache->getItem($item_id);
    if (is_null($item)) {
        echo "retriving from $item_id from data store\n";
        $item = $data_store->getItem($item_id);
        echo "item is {$item['val']}\n";
    } else {
        echo "$item_id was retrived from cache\n";
        echo "item is {$item['val']}\n";
    }
}


getItem('123', $cache);
getItem('555', $cache);
getItem('123', $cache);

class CacheAside {
    public function __construct(Cache $cache, DataStore $data_store, $timeout) {
        $this->cache = $cache;
        $this->data_store = $data_store;
        $this->cache_timeout = $timeout; // out timeout needs to match the cache
    }

    public function getItem($item_id) {
        $item = $this->cache->getItem($item_id);
        if (is_null($item)) {
            echo "retriving from $item_id from data store and storing in cache\n";
            $item = $this->data_store->getItem($item_id);
            echo "item is {$item['val']}\n";
            $this->cache->storeItem($item_id, $item);
        } else {
            echo "$item_id was retrived from cache\n";
            echo "item is {$item['val']}\n";
        }
    }

    public function storeItem($item_id, $item) {
        $this->cache->storeItem($item_id, $item);
    }
}

$data_store = new DataStore();
$cache_aside = new CacheAside($cache, $data_store, $time_out);
$cache_aside->getItem('222', $cache);
$cache_aside->getItem('222', $cache);

// https://msdn.microsoft.com/en-us/library/dn589799.aspx
