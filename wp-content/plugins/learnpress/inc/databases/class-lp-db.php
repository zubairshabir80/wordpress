<?php
/**
 * Class LP_Database
 *
 * @author tungnx
 * @since 3.2.7.5
 * @version 2.0.1
 */
defined( 'ABSPATH' ) || exit();

//require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class LP_Database {
	private static $_instance;
	public $wpdb;
	public $tb_lp_user_items, $tb_lp_user_itemmeta;
	public $tb_posts, $tb_postmeta, $tb_options;
	public $tb_lp_order_items, $tb_lp_order_itemmeta;
	public $tb_lp_sections, $tb_lp_section_items;
	public $tb_lp_quiz_questions;
	public $tb_lp_user_item_results;
	public $tb_lp_question_answers;
	public $tb_lp_question_answermeta;
	public $tb_lp_upgrade_db;
	public $tb_lp_sessions;
	private $collate         = '';
	public $max_index_length = '191';

	protected function __construct() {
		/**
		 * @global wpdb
		 */
		global $wpdb;
		$prefix = $wpdb->prefix;

		$this->wpdb                      = $wpdb;
		$this->tb_users                  = $wpdb->users;
		$this->tb_posts                  = $wpdb->posts;
		$this->tb_postmeta               = $wpdb->postmeta;
		$this->tb_options                = $wpdb->options;
		$this->tb_lp_user_items          = $prefix . 'learnpress_user_items';
		$this->tb_lp_user_itemmeta       = $prefix . 'learnpress_user_itemmeta';
		$this->tb_lp_order_items         = $prefix . 'learnpress_order_items';
		$this->tb_lp_order_itemmeta      = $prefix . 'learnpress_order_itemmeta';
		$this->tb_lp_section_items       = $prefix . 'learnpress_section_items';
		$this->tb_lp_sections            = $prefix . 'learnpress_sections';
		$this->tb_lp_quiz_questions      = $prefix . 'learnpress_quiz_questions';
		$this->tb_lp_user_item_results   = $prefix . 'learnpress_user_item_results';
		$this->tb_lp_question_answers    = $prefix . 'learnpress_question_answers';
		$this->tb_lp_question_answermeta = $prefix . 'learnpress_question_answermeta';
		$this->tb_lp_upgrade_db          = $prefix . 'learnpress_upgrade_db';
		$this->tb_lp_sessions            = $prefix . 'learnpress_sessions';
		$this->wpdb->hide_errors();
		$this->set_collate();
	}

	/**
	 * Get Instance
	 *
	 * @return LP_Database
	 */
	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function set_collate() {
		$collate = '';

		if ( $this->wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $this->wpdb->charset ) ) {
				$collate .= 'DEFAULT CHARACTER SET ' . $this->wpdb->charset;
			}

			if ( ! empty( $this->wpdb->collate ) ) {
				$collate .= ' COLLATE ' . $this->wpdb->collate;
			}
		}

		$this->collate = $collate;
	}

	public function get_collate(): string {
		return $this->collate;
	}

	/**
	 * Get list Item by post type and user id
	 *
	 * @param string $post_type .
	 * @param int    $user_id .
	 *
	 * @return array
	 */
	public function getListItem( $post_type = '', $user_id = 0 ) {
		$query = $this->wpdb->prepare(
			"
			SELECT ID FROM $this->tb_posts
			WHERE post_type = %s
			AND post_author = %d",
			$post_type,
			$user_id
		);

		return $this->wpdb->get_col( $query );
	}

	/**
	 * Get total Item by post type and user id
	 *
	 * @param LP_Post_Type_Filter $filter
	 *
	 * @return int
	 * @since 3.2.8
	 */
	public function get_count_post_of_user( LP_Post_Type_Filter $filter ): int {
		$query_append = '';

		$cache_key = _count_posts_cache_key( $filter->post_type );

		// Get cache
		$counts = wp_cache_get( $cache_key );
		if ( false !== $counts ) {
			return $counts;
		}

		if ( ! empty( $filter->post_status ) ) {
			$query_append .= ' AND post_status = \'' . $filter->post_status . '\'';
		}

		$query = $this->wpdb->prepare(
			"
			SELECT Count(ID) FROM $this->tb_posts
			WHERE post_type = '%s'
			AND post_author = %d
			{$query_append}",
			$filter->post_type,
			$filter->post_author
		);

		$query = apply_filters( 'learnpress/query/get_total_post_of_user', $query );

		$counts = (int) $this->wpdb->get_var( $query );

		// Set cache
		wp_cache_set( $cache_key, $counts );

		return $counts;
	}

	/**
	 * Get post by post_type and slug
	 *
	 * @param string $post_type .
	 * @param string $slug .
	 *
	 * @return string
	 */
	public function getPostAuthorByTypeAndSlug( $post_type = '', $slug = '' ) {
		$query = $this->wpdb->prepare(
			"
			SELECT post_author FROM $this->tb_posts
			WHERE post_type = %s
			AND post_name = %s",
			$post_type,
			$slug
		);

		return $this->wpdb->get_var( $query );
	}

	/**
	 * Check table exists.
	 *
	 * @param string $name_table
	 *
	 * @return bool|int
	 */
	public function check_table_exists( string $name_table ) {
		return $this->wpdb->query( $this->wpdb->prepare( "SHOW TABLES LIKE '%s'", $name_table ) );
	}

	/**
	 * Clone table
	 *
	 * @param string $name_table .
	 *
	 * @throws Exception
	 */
	public function clone_table( string $name_table ):bool {
		if ( ! current_user_can( ADMIN_ROLE ) ) {
			throw new Exception( 'You don\'t have permission' );
		}

		$table_bk = $name_table . '_bk';

		// Drop table bk if exists.
		$this->drop_table( $table_bk );

		// Clone table
		$this->wpdb->query( "CREATE TABLE {$table_bk} LIKE {$name_table}" );
		$this->wpdb->query( "INSERT INTO {$table_bk} SELECT * FROM {$name_table}" );

		/*dbDelta(
			"CREATE TABLE $table_bk LIKE $name_table;
			INSERT INTO $table_bk SELECT * FROM $name_table;"
		);*/

		$this->check_execute_has_error();

		return true;
	}

	/**
	 * Check column table
	 *
	 * @param string $name_table .
	 * @param string $name_col .
	 *
	 * @return bool|int
	 */
	public function check_col_table( string $name_table = '', string $name_col = '' ) {
		$query = $this->wpdb->prepare( "SHOW COLUMNS FROM $name_table LIKE '%s'", $name_col );

		return $this->wpdb->query( $query );
	}

	/**
	 * Drop Column of Table
	 *
	 * @param string $name_table .
	 * @param string $name_col .
	 *
	 * @return bool|int
	 * @throws Exception
	 */
	public function drop_col_table( string $name_table = '', string $name_col = '' ) {
		if ( ! current_user_can( 'administrator' ) ) {
			return false;
		}

		$check_table = $this->check_col_table( $this->tb_lp_user_items, $name_col );

		if ( $check_table ) {
			$execute = $this->wpdb->query( "ALTER TABLE $name_table DROP COLUMN $name_col" );

			$this->check_execute_has_error();

			return $execute;
		}

		return true;
	}

	/**
	 * Add Column of Table
	 *
	 * @param string $name_table .
	 * @param string $name_col .
	 * @param string $type .
	 * @param string $after_col .
	 *
	 * @return bool|int
	 * @throws Exception
	 */
	public function add_col_table( string $name_table, string $name_col, string $type, string $after_col = '' ) {
		if ( ! current_user_can( ADMIN_ROLE ) ) {
			throw new Exception( 'You don\'t have permission' );
		}

		$query_add = '';

		$col_exists = $this->check_col_table( $name_table, $name_col );

		if ( ! empty( $after_col ) ) {
			$query_add .= "AFTER $after_col";
		}

		if ( ! $col_exists ) {
			$execute = $this->wpdb->query( "ALTER TABLE $name_table ADD COLUMN $name_col $type $query_add" );

			$this->check_execute_has_error();

			return $execute;
		}

		return true;
	}

	/**
	 * Drop Index of Table
	 *
	 * @param string $name_table .
	 *
	 * @return bool|int
	 * @throws Exception
	 */
	public function drop_indexs_table( string $name_table ) {
		$show_index = "SHOW INDEX FROM $name_table";
		$indexs     = $this->wpdb->get_results( $show_index );

		foreach ( $indexs as $index ) {
			if ( 'PRIMARY' === $index->Key_name || '1' !== $index->Seq_in_index ) {
				continue;
			}

			$query = $this->wpdb->prepare( "ALTER TABLE $name_table DROP INDEX $index->Key_name", 1 );

			$this->wpdb->query( $query );
			$this->check_execute_has_error();
		}
	}

	/**
	 * Add Index of Table
	 *
	 * @param string $name_table .
	 * @param array  $indexs.
	 *
	 * @return bool|int
	 * @throws Exception
	 */
	public function add_indexs_table( string $name_table, array $indexs ) {
		$add_index    = '';
		$count_indexs = count( $indexs ) - 1;

		// Drop indexs .
		$this->drop_indexs_table( $name_table );

		foreach ( $indexs as $index ) {
			if ( $count_indexs === array_search( $index, $indexs ) ) {
				$add_index .= ' ADD INDEX ' . $index . ' (' . $index . ')';
			} else {
				$add_index .= ' ADD INDEX ' . $index . ' (' . $index . '),';
			}
		}

		$execute = $this->wpdb->query(
			"
			ALTER TABLE {$name_table}
			$add_index
			"
		);

		$this->check_execute_has_error();

		return $execute;
	}

	/**
	 * Drop table
	 *
	 * @param string $name_table .
	 *
	 * @return bool|int
	 * @throws Exception
	 */
	public function drop_table( string $name_table = '' ) {
		if ( ! current_user_can( ADMIN_ROLE ) ) {
			throw new Exception( 'You don\'t have permission' );
		}

		// Check table exists.
		$tb_exists = $this->check_table_exists( $name_table );
		if ( $tb_exists ) {
			$execute = $this->wpdb->query( "DROP TABLE $name_table" );

			$this->check_execute_has_error();

			return $execute;
		}

		return true;
	}

	/**
	 * Create table learnpress_user_item_results
	 *
	 * @return bool|int
	 * @throws Exception
	 */
	public function create_tb_lp_user_item_results() {
		$collate = $this->get_collate();

		$execute = $this->wpdb->query(
			"
			CREATE TABLE IF NOT EXISTS $this->tb_lp_user_item_results(
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_item_id bigint(20) unsigned NOT NULL,
				result longtext,
				PRIMARY KEY (id),
				KEY user_item_id (user_item_id)
			) $collate
			"
		);

		$this->check_execute_has_error();

		return $execute;
	}

	/**
	 * Create table learnpress_upgrade_db
	 *
	 * @return bool|int
	 * @throws Exception
	 */
	public function create_tb_lp_upgrade_db() {
		$collate = $this->get_collate();

		$execute = $this->wpdb->query(
			"
			CREATE TABLE IF NOT EXISTS {$this->tb_lp_upgrade_db}(
				step varchar(50) PRIMARY KEY UNIQUE,
				status varchar(10),
				KEY status (status)
			) $collate
			"
		);

		$this->check_execute_has_error();

		return $execute;
	}

	/**
	 * Set step completed.
	 *
	 * @param string $step .
	 * @param string $status .
	 *
	 * @return int|bool
	 */
	public function set_step_complete( string $step, string $status ) {
		if ( ! current_user_can( 'administrator' ) ) {
			return false;
		}

		return $this->wpdb->insert(
			$this->tb_lp_upgrade_db,
			array(
				'step'   => $step,
				'status' => $status,
			),
			array( '%s', '%s' )
		);
	}

	/**
	 * Get steps completed.
	 *
	 * @return array|object|null
	 */
	public function get_steps_completed() {
		return $this->wpdb->get_results( "SELECT step, status FROM {$this->tb_lp_upgrade_db}", OBJECT_K );
	}

	/**
	 * Check execute current has any errors.
	 *
	 * @throws Exception
	 */
	public function check_execute_has_error() {
		if ( $this->wpdb->last_error ) {
			throw new Exception( $this->wpdb->last_error );
		}
	}

	/**
	 * Important: Reason need set again indexes for table options of WP
	 * because if want change value of "option_name" will error "database error Duplicate entry"
	 * So before set must drop and add when done all
	 *
	 * @author tungnx
	 * @version 1.0.0
	 * @since 4.0.3
	 * @throws Exception
	 */
	public function create_indexes_tb_options() {
		$this->drop_indexs_table( $this->tb_options );
		$result = $this->wpdb->query(
			"
			ALTER TABLE $this->tb_options
			ADD UNIQUE option_name (option_name),
			ADD INDEX autoload (autoload)
			"
		);

		$this->check_execute_has_error();

		return $result;
	}

	/**
	 * Rename table
	 *
	 * @author tungnx
	 * @version 1.0.0
	 * @since 4.0.3
	 * @throws Exception
	 */
	public function rename_table( string $name_table = '', string $new_name = '' ) {
		if ( ! current_user_can( ADMIN_ROLE ) ) {
			throw new Exception( 'You don\'t have permission' );
		}

		$tb_exists = $this->check_table_exists( $name_table );

		if ( ! $tb_exists ) {
			throw new Exception( 'Table not exists' );
		}

		$result = $this->wpdb->query(
			"
			ALTER TABLE $name_table
			RENAME $new_name
			"
		);
		$this->check_execute_has_error();

		return $result;
	}
}
