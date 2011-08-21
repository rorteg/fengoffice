<?php

define('APC_PREFIX', trim(preg_replace('@.*//[^/]*@', '', ROOT_URL), '/') . "_");
define('APC_TTL', 60 * 60 * 24); // 1 day

class APCWrapper {
	
	static function isAvailable() {
		return function_exists('apc_sma_info');
	}
	
	static function add($key, $value, $ttl = null) {
		if (!$ttl) $ttl = APC_TTL;
		if (self::isAvailable()) {
			return apc_add(APC_PREFIX . $key, $value, $ttl) === TRUE;
		} else {
			return false;
		}
	}
	
	static function update($key, $value, $ttl = null) {
		if (self::isAvailable()) {
			self::delete($key);
			return self::add($key, $value);
		} else {
			return false;
		}
	}
	
	static function get($key, &$success) {
		if (self::isAvailable()) {
			$value = apc_fetch(APC_PREFIX . $key, $success);
			if ($success) return $value;
			else return null;
		} else {
			$success = false;
			return null;
		}
	}
	
	static function delete($key) {
		if (self::isAvailable()) {
			return apc_delete(APC_PREFIX . $key);
		} else {
			return false;
		}
	}
	
	static function key_exists($key) {
		if (self::isAvailable()) {
			if (function_exists('apc_exists')) {
				return apc_exists(APC_PREFIX . $key);
			} else {
				$success = false;
				apc_fetch(APC_PREFIX . $key, $success);
				return $success;
			}
		} else {
			return false;
		}
	}
}