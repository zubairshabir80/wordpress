<?php
/**
 * Class LP_Course.
 *
 * @author  ThimPress
 * @package LearnPress/Classes
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'LP_Course' ) ) {

	/**
	 * Class LP_Course
	 */
	class LP_Course extends LP_Abstract_Course {
		protected $key_info_extra_fast_query = '_lp_info_extra_fast_query';

		/**
		 * LP_Course constructor.
		 *
		 * @param mixed $course
		 */
		public function __construct( $course ) {
			parent::__construct( $course );
		}

		/**
		 * Debug log.
		 *
		 * @param $data
		 *
		 * @return array
		 */
		public static function log( $data ) {
			return $data;
		}

		/**
		 * Set item is viewing in single course.
		 *
		 * @param LP_Course_Item $item
		 *
		 * @return int|LP_Course_Item
		 */
		public function set_viewing_item( $item ) {
			if ( $this->_viewing_item && $this->_viewing_item->get_id() == $item->get_id() ) {
				return 0;
			}

			$user = learn_press_get_current_user();

			$this->_viewing_item = $item;
			$item->set_course( $this );

			$user->maybe_update_item( $item->get_id(), $this->get_id() );

			return $item;
		}

		/**
		 * Get default course meta.
		 *
		 * @return mixed
		 * @since 3.0.0
		 */
		public static function get_default_meta() {
			$meta = array(
				'duration'                 => '10 weeks',
				'max_students'             => 1000,
				'students'                 => 0,
				'retake_count'             => 0,
				'featured'                 => 'no',
				'block_lesson_content'     => 'no',
				'external_link_buy_course' => '',
				'course_result'            => 'evaluate_lesson',
				'passing_condition'        => 80,
				'price'                    => '',
				'sale_price'               => '',
				'sale_start'               => '',
				'sale_end'                 => '',
				'required_enroll'          => 'yes',
				'course_author'            => learn_press_get_current_user_id(),
			);

			return apply_filters( 'learn-press/course/default-meta', $meta );
		}

		/**
		 * Get LP Course.
		 *
		 * @param int   $course_id
		 * @param array $args
		 *
		 * @return mixed|bool|LP_Course
		 */
		public static function get_course( $course_id = 0 ) {
			if ( isset( LP_Global::$courses[ $course_id ] ) ) {
				return LP_Global::$courses[ $course_id ];
			}

			$the_course = self::get_course_object( $course_id );

			if ( ! $the_course ) {
				return false;
			}

			$key_args = wp_parse_args(
				array(
					'id'   => $the_course->ID,
					'type' => $the_course->post_type,
				)
			);

			$key = LP_Helper::array_to_md5( $key_args );

			if ( empty( LP_Global::$courses[ $key ] ) ) {
				$class_name = self::get_course_class( $the_course );
				if ( is_string( $class_name ) && class_exists( $class_name ) ) {
					$course = new $class_name( $the_course->ID );
				} elseif ( $class_name instanceof LP_Abstract_Course ) {
					$course = $class_name;
				} else {
					$course = new self( $the_course->ID );
				}
				LP_Global::$courses[ $key ] = $course;
			} else {
				$course = LP_Global::$courses[ $key ];
			}

			/**
			 * Force to reload course data into cache if it is not
			 * loaded or has been deleted for some reasons.
			 */
			$course->load();

			return $course;
		}

		/**
		 * @param string $course_type
		 *
		 * @return string|false
		 */
		private static function get_class_name_from_course_type( $course_type ) {
			return LP_COURSE_CPT === $course_type ? __CLASS__ : 'LP_Course_' . implode(
				'_',
				array_map( 'ucfirst', explode( '-', $course_type ) )
			);
		}

		/**
		 * Get the course class name
		 *
		 * @param WP_Post $the_course
		 * @param array   $args (default: array())
		 *
		 * @return string
		 */
		private static function get_course_class( $the_course, $args = array() ) {
			$course_id = absint( $the_course->ID );
			$type      = $the_course->post_type;

			$class_name = self::get_class_name_from_course_type( $type );

			// Filter class name so that the class can be overridden if extended.
			return apply_filters( 'learn-press/course/object-class', $class_name, $type, $course_id );
		}

		/**
		 * Get the course object
		 *
		 * @param mixed $the_course
		 *
		 * @return WP_Post|bool false on failure
		 * @uses   WP_Post
		 */
		private static function get_course_object( $the_course ) {
			$the_course_passed = $the_course;
			if ( false === $the_course ) {
				$the_course = get_post_type() === LP_COURSE_CPT ? $GLOBALS['post'] : false;
			} elseif ( is_numeric( $the_course ) ) {
				$the_course = get_post( $the_course );
			} elseif ( $the_course instanceof LP_Abstract_Course ) {
				$the_course = get_post( $the_course->get_id() );
			} elseif ( ! ( $the_course instanceof WP_Post ) ) {
				$the_course = false;
			}

			if ( $the_course && $the_course->post_type !== LP_COURSE_CPT ) {
				$the_course = false;
			}

			return apply_filters( 'learn-press/course/post-object', $the_course, $the_course_passed );
		}

		/**
		 * Check time remaining course when enable duration expire
		 * Value: -1 is no limit (default)
		 * Value: 0 is block
		 * Administrator || (is instructor && is author course) will be not block.
		 *
		 * @return int second
		 * @since 4.0.0
		 * @author tungnx
		 * @version 1.0.1
		 */
		public function timestamp_remaining_duration(): int {
			$timestamp_remaining = - 1;
			$user                = learn_press_get_user( get_current_user_id() );

			if ( current_user_can( 'administrator' ) ||
				 ( current_user_can( LP_TEACHER_ROLE ) &&
				   $this->get_author()->get_id() === $user->get_id() )
			) {
				return $timestamp_remaining;
			}

			if ( 0 === absint( $this->get_data( 'duration' ) ) ) {
				return $timestamp_remaining;
			}

			if ( 'yes' !== $this->get_data( 'block_course_duration_expire' ) ) {
				return $timestamp_remaining;
			}

			$course_item_data = $user->get_course_data( $this->get_id() );

			$course_start_time   = $course_item_data->get_start_time()->get_raw_date();
			$duration            = $this->get_data( 'duration' );
			$timestamp_expire    = strtotime( $course_start_time . ' +' . $duration );
			$timestamp_current   = strtotime( current_time( 'mysql' ) );
			$timestamp_remaining = $timestamp_expire - $timestamp_current;

			if ( $timestamp_remaining < 0 ) {
				$timestamp_remaining = 0;
			}

			return apply_filters( 'learnpress/course/block_duration_expire/timestamp_remaining', $timestamp_remaining );
		}

		/**
		 * Get option enable block course when finish course
		 *
		 * @return bool
		 */
		public function enable_block_item_when_finish(): bool {
			return 'yes' === $this->get_data( 'block_course_finished' );
		}

		public function allow_repurchase() : bool {
			return 'yes' === $this->get_data( 'allow_repurchase' );
		}

		public function allow_repurchase_course_option() : string {
			return $this->get_data( 'allow_repurchase_course_option', 'reset' );
		}

		/**
		 * Get first item of course
		 *
		 * @author tungnx
		 * @since 4.0.0
		 * @modify 4.1.3
		 * @version 1.0.1
		 * @return int
		 */
		public function get_first_item_id(): int {
			$course_id = $this->get_id();

			try {
				// Get cache
				$lp_course_cache = LP_Course_Cache::instance();
				$key_cache       = "$course_id/first_item_id";
				$first_item_id   = $lp_course_cache->get_cache( $key_cache );

				if ( ! $first_item_id ) {
					$extra_info = $this->get_info_extra_for_fast_query();

					if ( ! $extra_info->first_item_id ) {
						$first_item_id             = LP_Course_DB::getInstance()->get_first_item_id( $course_id );
						$extra_info->first_item_id = $first_item_id;

						// Save post meta
						$this->set_info_extra_for_fast_query( $extra_info );
					} else {
						$first_item_id = $extra_info->first_item_id;
					}
				}
			} catch ( Throwable $e ) {
				$first_item_id = 0;
			}

			return $first_item_id;
		}

		/**
		 * Get redirect url after enroll course
		 *
		 * @author tungnx
		 * @version 1.0.0
		 * @since 4.0.0
		 * @return false|string|WP_Error
		 */
		public function get_redirect_url_after_enroll() {
			$first_item_id = $this->get_first_item_id();
			$redirect      = $first_item_id ? $this->get_item_link( $first_item_id ) : get_the_permalink( $this->get_id() );

			return apply_filters( 'learnpress/rest-api/enroll-course/redirect', $redirect );
		}

		/**
		 * Get info extra on post meta to query fast
		 *
		 * @since 4.1.3
		 * @author tungnx
		 * @version 1.0.0
		 * @return LP_Course_Extra_Info_Fast_Query_Model
		 */
		public function get_info_extra_for_fast_query(): LP_Course_Extra_Info_Fast_Query_Model {
			$extra_info = new LP_Course_Extra_Info_Fast_Query_Model();

			try {
				$extra_info_str = get_post_meta( $this->get_id(), $this->key_info_extra_fast_query, true );

				if ( $extra_info_str ) {
					$extra_info_stdclass = json_decode( $extra_info_str );

					if ( JSON_ERROR_NONE !== json_last_error() ) {
						throw new Exception( 'Error json decode on ' . __METHOD__ );
					}

					$extra_info = $extra_info->map_stdclass( $extra_info_stdclass );
				}
			} catch ( Throwable $e ) {
				error_log( $e->getMessage() );
			}

			return $extra_info;
		}

		/**
		 * Set extra info for query fast on post meta
		 *
		 * @since 4.1.3
		 * @author tungnx
		 * @version 1.0.0
		 * @param LP_Course_Extra_Info_Fast_Query_Model $data_object
		 */
		public function set_info_extra_for_fast_query( LP_Course_Extra_Info_Fast_Query_Model $data_object ) {
			try {
				$extra_info_json = json_encode( $data_object );

				if ( JSON_ERROR_NONE !== json_last_error() ) {
					throw new Exception( 'Error encode on ' . __METHOD__ );
				}

				update_post_meta( $this->get_id(), $this->key_info_extra_fast_query, $extra_info_json );
			} catch ( Throwable $e ) {
				error_log( $e->getMessage() );
			}
		}
	}
}
