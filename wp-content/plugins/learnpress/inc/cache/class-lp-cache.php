<?php

/**
 * Class LP_Cache
 *
 * @author tungnx
 * @since 4.0.8
 * @version 1.0.1
 */
defined( 'ABSPATH' ) || exit();

class LP_Cache {
	protected static $instance;
	/**
	 * @var string Key group parent
	 */
	protected $key_group_parent = 'learn_press/';
	/**
	 * @var string Key group child(external)
	 */
	protected $key_group_child = '';
	/**
	 * @var string Add key group parent with key group child
	 */
	protected $key_group = '';
	/**
	 * @var float|int default expire
	 */
	protected $expire = DAY_IN_SECONDS;

	/**
	 * Get instance
	 *
	 * @return LP_Cache
	 */
	public static function instance(): LP_Cache {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		$this->key_group = $this->key_group_parent . $this->key_group_child;
	}

	/**
	 * Set cache
	 * $expire = -1 is  get default expire time on one day(DAY_IN_SECONDS)
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $expire
	 */
	public function set_cache( string $key, $data, int $expire = -1 ) {
		if ( -1 === $expire ) {
			$expire = $this->expire;
		}
		wp_cache_set( $key, $data, $this->key_group, $expire );
	}

	/**
	 * Get cache
	 *
	 * @param string $key
	 * @return false|mixed
	 */
	public function get_cache( string $key ) {
		return wp_cache_get( $key, $this->key_group );
	}

	/**
	 * Clear cache by key
	 *
	 * @param $key
	 */
	public function clear( $key ) {
		wp_cache_delete( $key, $this->key_group );
	}

	public function clear_all() {
		wp_cache_flush();
	}
}
