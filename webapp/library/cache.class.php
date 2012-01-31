<?php
class Cache {

	static function put($key, $var, $ttl=0) {
		return apc_store(md5($key), serialize($var), $ttl);
	}

	static function get($key) {
		return unserialize(apc_fetch(md5($key)));
	}

}