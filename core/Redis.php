<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 09/01/2019
 * Time: 20:54
 */

namespace PitouFW\Core;

class Redis extends \Redis {
    public function __construct() {
        parent::__construct();
        $this->connect(REDIS_HOST, REDIS_PORT);

        if (REDIS_PASS !== '') {
            $this->auth(REDIS_PASS);
        }
    }

    public function set($key, $value, $ttl = null): bool {
        if (!is_string($value)) {
            $value = json_encode($value);
        }

        return parent::set($key, $value, $ttl);
    }

    public function get($key, bool $assoc = false) {
        $value = parent::get($key);
        $try_json = json_decode($value, $assoc);

        if ($try_json !== null) {
            return $try_json;
        }

        return $value;
    }


}
