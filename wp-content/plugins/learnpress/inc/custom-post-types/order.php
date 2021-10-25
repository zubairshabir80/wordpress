<?php
/**
 * @class LP_Order_Post_Type
 *
 * @author  ThimPress
 * @package LearnPress/Classes
 * @version 1.0.1
 */

if ( ! class_exists( 'LP_Order_Post_Type' ) ) {
	final class LP_Order_Post_Type extends LP_Abstract_Post_Type {
		/**
		 * Type of post
		 *
		 * @var string
		 */
		protected $_post_type = LP_ORDER_CPT;
		/**
		 * @var null
		 */
		protected static $_instance = null;

		/**
		 * LP_Order_Post_Type constructor.
		 *
		 * @param $post_type
		 */
		public function __construct( $post_type ) {
			add_action( 'init', array( $this, 'register_post_statues' ) );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			add_action( 'admin_init', array( $this, 'remove_box' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			//add_action( 'transition_post_status', array( $this, 'restore_order' ), 1, 3 );
			add_filter( 'wp_untrash_post_status', array( $this, 'restore_status_order' ), 11, 3 );
			//add_action( 'save_post', array( $this, 'recount_enrolled_users' ), 11, 3 );

			add_filter( 'admin_footer', array( $this, 'admin_footer' ) );

			//$this->add_map_method( 'save', 'save_order' );

			//Hungkv => Fix error child order not show in trash
			//          add_filter( 'wp_count_posts', array( $this, 'filter_count_posts' ), 100, 3 );
			add_filter( 'views_edit-lp_order', array( $this, 'filter_views' ) );
			//          add_filter( 'posts_where_paged', array( $this, 'filter_orders' ) );

			parent::__construct( $post_type );
		}

		/**
		 * Re-count enrolled users to the courses in current order
		 * is being changed status
		 *
		 * @param int $post_id
		 * @since 3.0.10
		 * @editor tungnx
		 * @reason not use
		 */
		/*public function recount_enrolled_users( int $post_id ) {
			$order = learn_press_get_order( $post_id );
			$curd  = new LP_Course_CURD();
			$items = $order->get_items();

			if ( $items ) {
				foreach ( $items as $item ) {
					if ( ! isset( $item['course_id'] ) ) {
						continue;
					}

					$course_id = $item['course_id'];
					LP_Repair_Database::instance()->sync_course_orders( $course_id );
					$count = $curd->count_enrolled_users_by_orders( $course_id );
					update_post_meta( $course_id, 'count_enrolled_users', $count );
				}
			}
		}*/

		/**
		 * Filter the counts of posts when wp counting orders by statuses.
		 * Maybe there are some orders are created for multiple users,
		 * and each user in main order will be assigned to a separated
		 * order with post_parent is ID of main order. And, we do not
		 * want to show these orders in the list.
		 *
		 * @param array $counts
		 * @param string $type
		 * @param string $perm
		 *
		 * @return array|object
		 */
		public function filter_count_posts( $counts, $type, $perm ) {
			if ( LP_ORDER_CPT === $type ) {
				$cache_key = 'lp-' . _count_posts_cache_key( $type, $perm );

				$counts = LP_Object_Cache::get( $cache_key, 'counts' );

				if ( false !== $counts ) {
					return $counts;
				}

				global $wpdb;
				$query = "
				        SELECT post_status, COUNT( ID ) AS num_posts
                        FROM {$wpdb->posts}
                        WHERE post_type = %s
                        AND post_parent = %d
				    ";

				if ( 'readable' == $perm && is_user_logged_in() ) {
					$post_type_object = get_post_type_object( $type );
					if ( ! current_user_can( $post_type_object->cap->read_private_posts ) ) {
						$query .= $wpdb->prepare(
							" AND (post_status != 'private' OR ( post_author = %d AND post_status = 'private' ))",
							get_current_user_id()
						);
					}
				}
				$query  .= ' GROUP BY post_status';
				$query   = $wpdb->prepare( $query, $type, 0 );
				$results = (array) $wpdb->get_results( $query, ARRAY_A );
				$counts  = array_fill_keys( get_post_stati(), 0 );

				foreach ( $results as $row ) {
					$counts[ $row['post_status'] ] = $row['num_posts'];
				}

				$counts = (object) $counts;
				LP_Object_Cache::set( $cache_key, $counts, 'counts' );
			}

			return $counts;
		}

		/**
		 * Unset value in 'mine' key in views of LP Orders.
		 * The 'mine' is present in some case when 'user_posts_count'
		 * is not the same with total posts then wp add it to the views
		 * of WP Posts List table.
		 *
		 * @param array $views
		 *
		 * @return mixed
		 */
		public function filter_views( $views ) {
			if ( isset( $views['mine'] ) ) {
				unset( $views['mine'] );
			}

			return $views;
		}

		/**
		 * Filter to hide orders are created by one order for multiple users.
		 *
		 * @param string $where
		 *
		 * @return string
		 */
		public function filter_orders( string $where ): string {
			if ( ! $this->is_page_list_posts_on_backend() ) {
				return $where;
			}

			global $wpdb;

			if ( isset( $_REQUEST['parent'] ) ) {
				$where .= sprintf( ' AND post_parent = %d ', absint( $_REQUEST['parent'] ) );
			} else {
				// $where .= $wpdb->prepare( " AND (post_parent = 0 OR {$wpdb->posts}.ID IN( SELECT post_parent FROM {$wpdb->posts} X WHERE X.post_parent <> 0 AND X.post_type = %s) )", LP_ORDER_CPT );
				$where .= ' AND post_parent = 0 ';
			}

			return $where;
		}

		public function enqueue_scripts() {
			if ( get_post_type() != $this->_post_type ) {
				return;
			}
			wp_enqueue_script( 'user-suggest' );
		}

		/**
		 * Restore user course item when the order is stored (usually from trash).
		 *
		 * @param string $new
		 * @param string $old
		 * @param WP_Post $post
		 *
		 * @editor tungnx
		 * @modify 4.1.3 - commnet - not use
		 */
		/*public function restore_order( $new, $old, $post ) {

			if ( ! ( 'trash' === $old ) ) {
				return;
			}

			$order = learn_press_get_order( $post->ID );
			if ( ! $order ) {
				return;
			}

			$user_item_data = get_post_meta( $post->ID, '_lp_user_data', true );
			if ( ! $user_item_data ) {
				return;
			}

			$items = $order->get_items();
			if ( ! $items ) {
				return;
			}

			$users = $order->get_users();
			if ( ! $users ) {
				return;
			}

			// Restore child order if current order is for multi users
			$child_orders = $order->get_child_orders();
			if ( $order->is_multi_users() && $child_orders ) {
				foreach ( $child_orders as $child_order ) {
					wp_untrash_post( $child_order );
				}
			}

			$user_curd = new LP_User_CURD();

			foreach ( $user_item_data as $user_item_id => $data ) {
				$item_course = $user_curd->get_user_item_by_id( $user_item_id );

				if ( ! $item_course ) {
					continue;
				}

				$order_status = $order->get_order_status();
				$last_status  = ( $order_status != '' && $order_status != 'completed' ) ? 'pending' : 'in-progress';
				$user_curd->update_user_item_status( $user_item_id, $last_status );
				// Restore data
				$user_curd->update_user_item_by_id(
					$user_item_id,
					$data
				);
			}

			// Delete data
			delete_post_meta( $post->ID, '_lp_user_data' );
		}*/

		/**
		 * Restore user course item when the order is stored (usually from trash).
		 *
		 * @param string $new_status
		 * @param int $post_id
		 * @param string $previous_status
		 * @return string
		 */
		public function restore_status_order( string $new_status, int $post_id, string $previous_status ): string {
			if ( LP_ORDER_CPT != get_post_type( $post_id ) ) {
				return $new_status;
			}

			return $previous_status;
		}

		/**
		 * @param LP_Order $order
		 * @param array $user_ids
		 * @param bool $trigger_action
		 *
		 * @throws Exception
		 * @editor tungnx
		 * @reason comment - not use
		 */
		/*protected function _update_child( $order, $user_ids, $trigger_action = false ) {
			$new_orders   = array();
			$child_orders = $order->get_child_orders( true );

			if ( $child_orders ) {
				foreach ( $child_orders as $child_id ) {
					$child_order         = learn_press_get_order( $child_id );
					$child_order_user_id = $child_order->get_user( 'id' );

					if ( ! in_array( $child_order_user_id, $user_ids ) ) {
						wp_delete_post( $child_id );
						continue;
					}

					$order->cln_items( $child_order->get_id() );
					$new_orders[ $child_order_user_id ] = $child_order;
				}
			}

			foreach ( $user_ids as $uid ) {
				if ( empty( $new_orders[ $uid ] ) ) {
					$new_order          = $order->cln();
					$new_orders[ $uid ] = $new_order;
				} else {
					$new_order = $new_orders[ $uid ];
				}

				$old_status = get_post_status( $new_order->get_id() );
				$new_order->set_order_date( $order->get_order_date( 'edit' ) );
				$new_order->set_parent_id( $order->get_id() );
				$new_order->set_user_id( $uid );
				$new_order->set_total( $order->get_total() );
				$new_order->set_subtotal( $order->get_subtotal() );

				$new_order->set_status( learn_press_get_request( 'order-status' ) );
				$new_order->save();
				$new_status = get_post_status( $new_order->get_id() );

				if ( ( $new_status == $old_status ) && $trigger_action ) {
					$status     = str_replace( 'lp-', '', $new_status );
					$old_status = str_replace( 'lp-', '', $new_status );
					do_action( 'learn-press/order/status-' . $status, $new_order->get_id(), $status );
					do_action( 'learn-press/order/status-' . $old_status . '-to-' . $status, $new_order->get_id() );
					do_action( 'learn-press/order/status-changed', $new_order->get_id(), $status, $old_status );
				}
			}
		}*/

		/**
		 * Save order post.
		 *
		 * @param int $post_id
		 * @param WP_Post $post
		 * @throws Exception
		 * @editor tungnx
		 * @version 1.0.1
		 */
		public function save( int $post_id, WP_Post $post ) {
			global $action;

			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( $action == 'editpost' ) {
				$order = learn_press_get_order( $post_id );

				$created_via = $order->get_created_via();
				if ( empty( $created_via ) ) {
					$user_id = learn_press_get_request( 'order-customer' );
					$order->set_created_via( 'manual' );
					$order->set_user_id( $user_id );
				}

				$order->set_status( learn_press_get_request( 'order-status' ) );
				$order->save();
			}
		}

		/**
		 * Remove unused boxes
		 */
		public function remove_box() {
			remove_post_type_support( LP_ORDER_CPT, 'title' );
			remove_post_type_support( LP_ORDER_CPT, 'editor' );
		}

		public function admin_footer() {
			if ( ! $this->is_page_list_posts_on_backend() ) {
				return;
			}
			?>
			<script type="text/javascript">
				jQuery(function ($) {
					$('#post-search-input').prop('placeholder',
						'<?php esc_attr_e( 'Order number, user name, user email, course name etc...', 'learnpress' ); ?>').css('width', 400)
				})
			</script>
			<?php
		}

		/**
		 * Hook to filter LP orders by some conditions.
		 *
		 * @param string $where
		 *
		 * @return mixed
		 */
		public function posts_where_paged( $where ) {
			global $wpdb, $wp_query;
			if ( is_admin() && $this->is_page_list_posts_on_backend() &&
				 ( ! isset( $wp_query->query['post_status'] ) || ! $wp_query->query['post_status'] ) ) {
				$statuses = array_keys( learn_press_get_register_order_statuses() );
				$search   = "{$wpdb->posts}.post_status = 'publish' ";
				$tmps     = array( $search );
				$tmp      = "{$wpdb->posts}.post_status = %s ";
				foreach ( $statuses as $status ) {
					$tmps[] = $wpdb->prepare( $tmp, $status );
				}
				$replace = implode( ' OR ', $tmps );
				$where   = str_replace( $search, $replace, $where );
			}

			if ( ! $this->is_page_list_posts_on_backend() || ! $this->_is_search() ) {
				return $where;
			}

			# filter by user id
			preg_match( "#{$wpdb->posts}\.post_author IN\s*\((\d+)\)#", $where, $matches );
			if ( ! empty( $matches ) && isset( $matches[1] ) ) {
				$author_id = intval( $matches[1] );
				$sql       = ' ( pm1.meta_value = %d OR pm1.meta_value LIKE %s)';

				$sql = " {$wpdb->posts}.ID IN ( SELECT
						IF( p.post_parent >0, p.post_parent, p.ID)
					FROM
						{$wpdb->posts} AS p
							INNER JOIN
						{$wpdb->postmeta} m ON p.ID = m.post_id and p.post_type = %s
								AND m.meta_key = %s
							INNER JOIN
						{$wpdb->users} u on m.meta_value = u.ID
					WHERE
						p.post_type = 'lp_order'
							AND u.ID = %d ) ";

				$sql   = $wpdb->prepare( $sql, array( LP_ORDER_CPT, '_user_id', $author_id ) );
				$where = str_replace( $matches[0], $sql, $where );
			}

			$s = $wp_query->get( 's' );

			if ( $s ) {
				$s = '%' . $wpdb->esc_like( $s ) . '%';
				preg_match( "#{$wpdb->posts}\.post_title LIKE#", $where, $matches2 );
				$sql = " {$wpdb->posts}.ID IN (
					SELECT
						IF( p.post_parent >0, p.post_parent, p.ID)
					FROM
						{$wpdb->posts} AS p
							INNER JOIN
						{$wpdb->postmeta} m ON p.ID = m.post_id and p.post_type = %s
								AND m.meta_key = %s
							INNER JOIN
						{$wpdb->users} u on m.meta_value = u.ID
					WHERE
						u.user_login LIKE %s
						OR u.user_nicename LIKE %s
						OR u.user_email LIKE %s
						OR u.display_name LIKE %s
						OR {$wpdb->posts}.ID LIKE %s
					) ";
				$sql = $wpdb->prepare( $sql, array( LP_ORDER_CPT, '_user_id', $s, $s, $s, $s, $s ) );
				# search order via course name
				$sql .= ' OR ' . $wpdb->prepare(
					" {$wpdb->posts}.ID IN (
						SELECT DISTINCT order_id FROM {$wpdb->learnpress_order_items} loi
						INNER JOIN {$wpdb->learnpress_order_itemmeta} loim ON loi.order_item_id = loim.learnpress_order_item_id AND loim.meta_key LIKE %s
						WHERE `order_item_name` LIKE %s OR loim.meta_value LIKE %s
					)",
					array( '_course_id', $s, $s )
				);
				if ( ! empty( $matches2 ) && isset( $matches2[0] ) ) {
					$sql  .= $wpdb->prepare( ' OR loi.order_item_name LIKE %s', $s );
					$where = str_replace( $matches2[0], $sql . ' OR ' . $matches2[0], $where );
				} else {
					$where .= ' AND ' . $sql;
				}
			}

			return $where;
		}

		public function posts_fields( $fields ) {
			global $wp_query;

			if ( ! $this->is_page_list_posts_on_backend() || ! $this->_is_search() ) {
				return $fields;
			}
			$fields .= ', uu.ID as user_ID, uu.display_name as user_display_name';

			return $fields;
		}

		public function posts_orderby( $orderby ) {
			global $wpdb;

			if ( ! $this->is_page_list_posts_on_backend() ) {
				return $orderby;
			}
			global $wpdb;

			$order = $this->get_order_sort();

			switch ( $this->get_order_by() ) {
				case 'title':
					$orderby = "{$wpdb->posts}.ID {$order}";
					break;
				case 'student':
					$orderby = "uu.user_login {$order}";
					break;
				case 'date':
					$orderby = "{$wpdb->posts}.post_date {$order}";
					break;
				case 'order_total':
					$orderby = " pm2.meta_value {$order}";
					break;
			}

			return $orderby;
		}

		public function posts_join_paged( $join ) {
			global $wpdb, $wp_query;
			if ( ! $this->is_page_list_posts_on_backend() ) {
				return $join;
			}
			$s     = $wp_query->get( 's' );
			$join .= " INNER JOIN {$wpdb->postmeta} pm1 ON {$wpdb->posts}.ID = pm1.post_id AND pm1.meta_key = '_user_id'";
			$join .= " INNER JOIN {$wpdb->postmeta} pm2 ON {$wpdb->posts}.ID = pm2.post_id AND pm2.meta_key = '_order_total'";
			if ( $s ) {
				$join .= " INNER JOIN {$wpdb->learnpress_order_items} loi ON {$wpdb->posts}.ID = loi.order_id";
			}
			$join .= " LEFT JOIN {$wpdb->users} uu ON pm1.meta_value = uu.ID";

			return $join;
		}

		/**
		 * Make our custom columns can be sortable
		 *
		 * @param $columns
		 *
		 * @return mixed
		 */
		public function sortable_columns( $columns ) {
			$columns['order_student'] = 'student';
			$columns['order_date']    = 'date';
			$columns['order_total']   = 'order_total';

			return $columns;
		}

		public function update_status() {
			$order_id = ! empty( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : 0;
			$status   = ! empty( $_REQUEST['status'] ) ? LP_Helper::sanitize_params_submitted( $_REQUEST['status'] ) : 'Pending';

			learn_press_update_order_status( $order_id, $status );

			wp_send_json(
				array(
					'status' => $status,
					'class'  => sanitize_title( $status ),
				)
			);
		}

		/**
		 * Custom row's actions.
		 *
		 * @param array $actions
		 * @param WP_Post $post
		 *
		 * @return mixed
		 * @since 2.1.7
		 *
		 */
		public function row_actions( $actions, $post ) {
			if ( ! empty( $actions['inline hide-if-no-js'] ) ) {
				unset( $actions['inline hide-if-no-js'] );
			}
			if ( ! empty( $actions['edit'] ) ) {
				$actions['edit'] = preg_replace( '/>(.*?)<\/a>/', '>' . __( 'View Order', 'learnpress' ) . '</a>', $actions['edit'] );
			}

			$order = learn_press_get_order( $post->ID );
			if ( $order->is_multi_users() ) {
				$actions['child-orders'] = sprintf(
					'<a href="%s">%s</a>',
					add_query_arg(
						array(
							'post_type' => LP_ORDER_CPT,
							'parent'    => $post->ID,
						),
						admin_url( 'edit.php' )
					),
					__( 'View child orders', 'learnpress' )
				);
			}

			return $actions;
		}

		/**
		 * re-order the orders by newest
		 *
		 * @param $wp_query
		 * @editor tungnx
		 * @reason comment this function - because default sort by id
		 *
		 * @return mixed
		 */
		public function pre_get_posts( $wp_query ) {
			if ( is_admin() && isset( $wp_query->query['post_type'] ) && LP_ORDER_CPT == $wp_query->query['post_type'] ) {
				$wp_query->set( 'orderby', 'date' );
				$wp_query->set( 'order', 'desc' );
			}

			return $wp_query;
		}

		/**
		 *
		 */
		public function columns_head( $existing ) {

			// Remove Checkbox - adding it back below
			if ( isset( $existing['cb'] ) ) {
				$check = $existing['cb'];
				unset( $existing['cb'] );
			}

			// Remove Title - adding it back below
			if ( isset( $existing['title'] ) ) {
				unset( $existing['title'] );
			}

			// Remove Format
			if ( isset( $existing['format'] ) ) {
				unset( $existing['format'] );
			}

			// Remove Author
			if ( isset( $existing['author'] ) ) {
				unset( $existing['author'] );
			}

			// Remove Comments
			if ( isset( $existing['comments'] ) ) {
				unset( $existing['comments'] );
			}

			// Remove Date
			if ( isset( $existing['date'] ) ) {
				unset( $existing['date'] );
			}

			// Remove Builder
			if ( isset( $existing['builder_layout'] ) ) {
				unset( $existing['builder_layout'] );
			}

			add_filter( 'the_title', array( $this, 'order_title' ), 5, 2 );

			$columns['cb']            = '<input type="checkbox" />';
			$columns['title']         = esc_html__( 'Order', 'learnpress' );
			$columns['order_student'] = esc_html__( 'Student', 'learnpress' );
			$columns['order_items']   = esc_html__( 'Purchased', 'learnpress' );
			$columns['order_date']    = esc_html__( 'Date', 'learnpress' );
			$columns['order_total']   = esc_html__( 'Total', 'learnpress' );
			$columns['order_status']  = '<span class="status_head tips" data-tip="' . esc_attr__( 'Status', 'learnpress' ) . '">' . esc_attr__( 'Status', 'learnpress' ) . '</span>';

			$columns = array_merge( $columns, $existing );

			return $columns;
		}

		public function order_title( $title, $post_id ) {
			$order = learn_press_get_order( $post_id );

			if ( $order ) {
				$title = $order->get_order_number();
			}

			return $title;
		}

		/**
		 * Render column data
		 *
		 * @param string
		 * @param int
		 */
		public function columns_content( $column, $post_id = 0 ) {
			global $post;
			$the_order = learn_press_get_order( $post->ID );

			switch ( $column ) {
				case 'order_student':
					$user_ids = $the_order->get_users();
					if ( $user_ids ) {
						$outputs = array();
						foreach ( $user_ids as $user_id ) {
							if ( get_user_by( 'id', $user_id ) ) {
								$user      = learn_press_get_user( $user_id );
								$outputs[] = sprintf(
									'<a href="user-edit.php?user_id=%d">%s (%s)</a><span>%s</span>',
									$user_id,
									$user->get_data( 'user_login' ),
									$user->get_data( 'display_name' ),
									$user->get_data( 'user_email' )
								);
							} else {
								if ( sizeof( $user_ids ) == 1 ) {
									$outputs[] = $the_order->get_customer_name();
								}
							}
						}
						echo join( ', ', $outputs );
					} else {
						echo esc_html__( '(Guest)', 'learnpress' );
					}
					break;
				case 'order_status':
					$icon = '';
					switch ( $the_order->get_status() ) {
						case 'pending':
							$icon = '<i class="fas fa-flag"></i>';
							break;
						case 'processing':
							$icon = '<i class="far fa-clock"></i>';
							break;
						case 'completed':
							$icon = '<i class="far fa-check-circle"></i>';
							break;
						case 'failed':
							$icon = '<i class="far fa-times-circle"></i>';
							break;
						case 'cancelled':
							$icon = '<i class="fas fa-ban"></i>';
							break;
					}

					$icon  = apply_filters( 'learn-press/order-status-icon', $icon, $the_order->get_status() );
					$label = learn_press_get_order_status_label( $the_order->get_id() );
					echo sprintf(
						'<span class="learn-press-tooltip %s" data-tooltip="%s">%s %s</span>',
						$the_order->get_status(),
						$label,
						$icon,
						$label
					);
					break;
				case 'order_date':
					$t_time = get_the_time( 'Y/m/d g:i:s a' );
					$m_time = $post->post_date;
					$time   = get_post_time( 'G', true, $post );

					$time_diff = current_time( 'timestamp' ) - $time;

					if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
						$h_time = sprintf( __( '%s ago', 'learnpress' ), human_time_diff( $time ) );
					} else {
						$h_time = mysql2date( 'Y/m/d', $m_time );
					}

					echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'learn_press_order_column_time', $h_time, $the_order ) ) . '</abbr>';

					break;
				case 'order_items':
					$links = array();
					$items = $the_order->get_items();
					$count = sizeof( $items );

					foreach ( $items as $item ) {
						if ( empty( $item['course_id'] ) || get_post_type( $item['course_id'] ) !== LP_COURSE_CPT ) {
							$links[] = apply_filters( 'learn-press/order-item-not-course-id', esc_html__( 'Course does not exist', 'learnpress' ), $item );
						} elseif ( get_post_status( $item['course_id'] ) !== 'publish' ) {
							$links[] = get_the_title( $item['course_id'] ) . sprintf( ' (#%d - %s)', $item['course_id'], esc_html__( 'Deleted', 'learnpress' ) );
						} else {
							$link = '<a href="' . get_the_permalink( $item['course_id'] ) . '">' . get_the_title( $item['course_id'] ) . ' (#' . $item['course_id'] . ')' . '</a>';
							if ( $count > 1 ) {
								$link = sprintf( '<li>%s</li>', $link );
							}
							$links[] = apply_filters( 'learn-press/order-item-link', $link, $item );

						}
					}

					if ( $count > 1 ) {
						echo sprintf( '<ol>%s</ol>', join( '', $links ) );
					} elseif ( 1 == $count ) {
						echo join( '', $links );
					} else {
						echo esc_html__( '(No item)', 'learnpress' );
					}
					break;
				case 'order_total':
					echo $the_order->get_formatted_order_total();// learn_press_format_price( $the_order->order_total, learn_press_get_currency_symbol( $the_order->order_currency ) );
					if ( $title = $the_order->get_payment_method_title() ) {
						?>
						<div class="payment-method-title">
							<?php echo $the_order->order_total == 0 ? $title : sprintf( __( 'Pay via <strong>%s</strong>', 'learnpress' ), apply_filters( 'learn-press/order-payment-method-title', $title, $the_order ), $the_order ); ?>
						</div>
						<?php
					}
					break;
			}
		}

		private function _is_search() {
			return is_search();
		}

		/**
		 * Register order post type
		 */
		public function args_register_post_type(): array {
			return array(
				'labels'             => array(
					'name'               => __( 'Orders', 'learnpress' ),
					'menu_name'          => __( 'Orders', 'learnpress' ),
					'singular_name'      => __( 'Order', 'learnpress' ),
					'add_new_item'       => __( 'Add New Order', 'learnpress' ),
					'edit_item'          => __( 'Order Details', 'learnpress' ),
					'all_items'          => __( 'Orders', 'learnpress' ),
					'view_item'          => __( 'View Order', 'learnpress' ),
					'add_new'            => __( 'Add New', 'learnpress' ),
					'update_item'        => __( 'Update Order', 'learnpress' ),
					'search_items'       => __( 'Search Orders', 'learnpress' ),
					'not_found'          => __( 'No order found', 'learnpress' ),
					'not_found_in_trash' => __( 'No order found in Trash', 'learnpress' ),
				),
				'public'             => false,
				'show_ui'            => true,
				'show_in_nav_menus'  => false,
				'show_in_admin_bar'  => false,
				'publicly_queryable' => false,
				'show_in_menu'       => 'learn_press',
				'map_meta_cap'       => true,
				'capability_type'    => LP_ORDER_CPT,
				'hierarchical'       => true,
				'rewrite'            => array(
					'slug'         => LP_ORDER_CPT,
					'hierarchical' => true,
					'with_front'   => true,
				),
				'supports'           => array(
					'title',
					'custom-fields',
				),
			);
		}

		/**
		 * Remove some unwanted metaboxes
		 */
		public static function register_metabox() {
			// Remove Publish metabox
			remove_meta_box( 'submitdiv', LP_ORDER_CPT, 'side' );
			remove_meta_box( 'commentstatusdiv', LP_ORDER_CPT, 'normal' );
		}

		/**
		 * Order details view.
		 *
		 * @param WP_Post $post
		 */
		public static function order_details( $post ) {
			learn_press_admin_view( 'meta-boxes/order/details.php', array( 'order' => new LP_Order( $post ) ) );
		}

		/**
		 * Order actions view.
		 *
		 * @param WP_Post $post
		 */
		public static function order_actions( $post ) {
			learn_press_admin_view( 'meta-boxes/order/actions.php', array( 'order' => new LP_Order( $post ) ) );
		}

		public function preparing_to_trash_order( $post_id ) {
			if ( LP_ORDER_CPT != learn_press_get_post_type( $post_id ) ) {
				return;
			}
		}

		/**
		 * Register new post status for order
		 */
		public function register_post_statues() {
			$statuses = learn_press_get_register_order_statuses();
			foreach ( $statuses as $status => $args ) {
				register_post_status( $status, $args );
			}
		}

		public static function instance() {
			if ( ! self::$_instance ) {
				self::$_instance = new self( LP_ORDER_CPT );
			}

			return self::$_instance;
		}

		/**
		 * Order export view.
		 *
		 * @param WP_Post $post
		 *
		 * @throws Exception
		 * @since 3.2.7.8
		 *
		 * @author hungkv
		 */
		public static function order_exports( $post ) {
			learn_press_admin_view( 'meta-boxes/order/exports-invoice.php', array( 'order' => new LP_Order( $post ) ) );
		}
	}

	// end LP_Order_Post_Type

	$order_post_type = LP_Order_Post_Type::instance();

	//Todo: Nhamdv see to rewrite
	$order_post_type
		->add_meta_box( 'order_details', esc_html__( 'Order Details', 'learnpress' ), 'order_details', 'normal', 'high' )
		->add_meta_box( 'submitdiv', esc_html__( 'Order Actions', 'learnpress' ), 'order_actions', 'side', 'high' )
		->add_meta_box( 'order_export', esc_html__( 'Order Exports', 'learnpress' ), 'order_exports', 'side', 'high' );
}
