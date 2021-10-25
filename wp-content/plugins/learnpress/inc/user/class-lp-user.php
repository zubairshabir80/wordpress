<?php

/**
 * Class LP_User
 *
 * @author  ThimPress
 * @package LearnPress/Classes
 * @version 1.0.1
 */
class LP_User extends LP_Abstract_User {
	/**
	 * Check the user can view content items' course
	 *
	 * @param int $course_id
	 *
	 * @return LP_Model_User_Can_View_Course_Item
	 * @throws Exception
	 */
	public function can_view_content_course( int $course_id = 0 ): LP_Model_User_Can_View_Course_Item {
		$view          = new LP_Model_User_Can_View_Course_Item();
		$view->message = esc_html__(
			'This content is protected, please enroll course to view this content!',
			'learnpress'
		);

		$course = learn_press_get_course( $course_id );

		if ( ! $course ) {
			return $view;
		}

		if ( $this->is_admin() || $this->is_author_of( $course_id ) ) {
			$view->flag    = true;
			$view->message = '';

			return $view;
		}

		// Set view->flag is true if course is no required enroll
		if ( $course->is_no_required_enroll() ) {
			$view->flag = true;

			return $view;
		}

		if ( $course->is_publish() ) {
			$is_enrolled_or_finished = $this->has_enrolled_or_finished( $course_id );

			if ( $is_enrolled_or_finished ) {
				$is_finished_course            = $this->has_finished_course( $course_id );
				$enable_block_item_when_finish = $course->enable_block_item_when_finish();

				if ( $is_finished_course && $enable_block_item_when_finish ) {
					$view->key     = LP_BLOCK_COURSE_FINISHED;
					$view->message = __(
						'You finished this course. This content is protected, please enroll course to view this content!',
						'learnpress'
					);
				} elseif ( 0 === $course->timestamp_remaining_duration() ) {
					$view->key     = LP_BLOCK_COURSE_DURATION_EXPIRE;
					$view->message = __(
						'Content of this item has blocked because the course has exceeded duration.',
						'learnpress'
					);
				} elseif ( $this->get_course_status( $course_id ) === LP_COURSE_PURCHASED ) {
					$view->key     = LP_BLOCK_COURSE_PURCHASE;
					$view->message = __(
						'This content is protected, please enroll course to view this content!',
						'learnpress'
					);
				} else {
					$view->key     = 'can_view_course';
					$view->flag    = true;
					$view->message = '';
				}
			}
		}

		// Todo: set cache - tungnx

		return apply_filters( 'learnpress/course/can-view-content', $view, $this->get_id(), $course );
	}

	/**
	 * Check the user can access to an item inside course.
	 *
	 * @param int                                $item_id Course's item Id.
	 * @param LP_Model_User_Can_View_Course_Item $view Course Id.
	 *
	 * @author  tungnx
	 * @return LP_Model_User_Can_View_Course_Item
	 * @since 4.0.0
	 */
	public function can_view_item( int $item_id = 0, $view = null ): LP_Model_User_Can_View_Course_Item {
		$view_new = null;

		if ( ! $view instanceof LP_Model_User_Can_View_Course_Item ) {
			return new LP_Model_User_Can_View_Course_Item();
		}

		$item = LP_Course_Item::get_item( $item_id );

		if ( ! $item ) {
			return $view;
		}

		if ( $item instanceof LP_Course_Item && $item->is_preview() ) {
			$view_new          = clone $view; // or create new LP_Model_User_Can_View_Course_Item()
			$view_new->flag    = true;
			$view_new->key     = 'lesson_preview';
			$view_new->message = '';
		}

		if ( $view_new ) {
			$view = $view_new;
		}

		return apply_filters( 'learnpress/course/item/can-view', $view, $item );
	}

	/**
	 * Check if user can retry course.
	 *
	 * @param int $course_id .
	 *
	 * @return int
	 * @throws Exception .
	 * @since 4.0.0
	 * @author tungnx
	 */
	public function can_retry_course( int $course_id = 0 ): int {
		$flag = 0;

		try {
			$course = learn_press_get_course( $course_id );

			if ( ! $course ) {
				throw new Exception( 'Course is not available' );
			}

			$retake_option = (int) $course->get_data( 'retake_count' );

			if ( $retake_option > 0 ) {
				/**
				 * Check course is finished
				 * Check duration is blocked
				 */
				if ( ! $this->has_finished_course( $course->get_id() ) ) {
					if ( 0 !== $course->timestamp_remaining_duration() ) {
						throw new Exception();
					}
				}

				$user_course_data = $this->get_course_data( $course_id );

				if ( $user_course_data instanceof LP_User_Item_Course ) {
					$retaken          = $user_course_data->get_retaken_count();
					$can_retake_times = $retake_option - $retaken;

					if ( $can_retake_times > 0 ) {
						$flag = $can_retake_times;
					}
				}
			}
		} catch ( Exception $e ) {

		}

		return apply_filters( 'learn-press/user/course/can-retry', $flag, $this->get_id(), $course_id );
	}

	/**
	 * Return true if user has already purchased course
	 *
	 * @param int $course_id
	 *
	 * @return bool
	 * @editor tungnx
	 * @modify 4.1.3
	 * @throws Exception
	 */
	public function has_purchased_course( int $course_id ): bool {
		$user_course = $this->get_course_data( $course_id );

		return apply_filters( 'learn-press/user-purchased-course', $user_course && $user_course->is_purchased(), $course_id, $this->get_id() );
	}

	/**
	 * Check course is enrolled
	 *
	 * @param integer $course_id Course ID
	 * @param boolean $return_bool
	 * @return bool|object
	 * @editor tungnx
	 * @since 4.1.2
	 * @version 1.0.2
	 *
	 * @author Nhamdv
	 */
	public function has_enrolled_course( int $course_id, bool $return_bool = true ) {
		$result_check          = new stdClass();
		$result_check->check   = true;
		$result_check->message = '';

		try {
			$order = $this->get_course_order( $course_id );

			if ( ! $order || ! $order->is_completed() ) {
				throw new Exception( esc_html__( 'Order is not completed', 'learnpress' ) );
			}

			$user_course = $this->get_course_data( $course_id );

			if ( ! $user_course || ! $user_course->is_enrolled() ) {
				throw new Exception( esc_html__( 'Course is not enrolled', 'learnpress' ) );
			}
		} catch ( Throwable $th ) {
			$result_check->check   = false;
			$result_check->message = $th->getMessage();
		}

		return apply_filters( 'learn-press/user/is-course-enrolled', $return_bool ? $result_check->check : $result_check, $course_id, $return_bool );
	}

	/**
	 * Return true if user has finished a course
	 *
	 * @param int $course_id .
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function has_finished_course( int $course_id ) : bool {
		$user_course = $this->get_course_data( $course_id );

		return apply_filters( 'learn-press/user-has-finished-course', $user_course && $user_course->is_finished(), $this->get_id(), $course_id );
	}

	/**
	 * Check course of user is enrolled or finished
	 *
	 * @param int $course_id
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function has_enrolled_or_finished( int $course_id ): bool {
		$status = true;

		$user_course = $this->get_course_data( $course_id );

		if ( ! $user_course ) {
			$status = false;
		} elseif ( ! $user_course->is_enrolled() && ! $user_course->is_finished() ) {
			$status = false;
		}

		return apply_filters( 'learn-press/user-has-finished-course', $status, $this->get_id(), $course_id );
	}

	/**
	 * Check user can enroll course
	 *
	 * @param int  $course_id
	 * @param bool $return_bool
	 * @return mixed|object|bool
	 */
	public function can_enroll_course( int $course_id, bool $return_bool = true ) {
		$course          = learn_press_get_course( $course_id );
		$output          = new stdClass();
		$output->check   = true;
		$output->message = '';

		try {
			if ( ! $course ) {
				throw new Exception( esc_html__( 'No Course or User available', 'learnpress' ) );
			}

			if ( ! $course->is_publish() ) {
				throw new Exception( esc_html__( 'Course is not public', 'learnpress' ) );
			}

			if ( $course->get_external_link() ) {
				throw new Exception( esc_html__( 'Course is External', 'learnpress' ) );
			}

			if ( ! $course->is_in_stock() ) {
				throw new Exception( esc_html__( 'Course is full students', 'learnpress' ) );
			}

			if ( $course->is_no_required_enroll() ) {
				throw new Exception( esc_html__( 'Course is not require enrolling.', 'learnpress' ) );
			}

			if ( ! $course->is_free() && ! $this->has_purchased_course( $course_id ) ) {
				throw new Exception( esc_html__( 'Course is not purchased.', 'learnpress' ) );
			}

			if ( $this->has_enrolled_course( $course_id ) ) {
				throw new Exception( esc_html__( 'This course is already enrolled.', 'learnpress' ) );
			}
		} catch ( \Throwable $th ) {
			$output->check   = false;
			$output->message = $th->getMessage();
		}

		if ( $return_bool ) {
			$output = $output->check;
		}

		return apply_filters( 'learn-press/user/can-enroll-course', $output, $course, $return_bool );
	}

	/**
	 * Check can show purchase course button
	 *
	 * @param int $course_id
	 * @return bool
	 * @throws Exception
	 * @author nhamdv
	 * @editor tungnx
	 * @modify 4.1.3
	 */
	public function can_purchase_course( int $course_id ): bool {
		$can_purchase = true;
		$course       = learn_press_get_course( $course_id );

		try {
			if ( ! $course ) {
				throw new Exception( 'Course is unavailable' );
			}

			if ( ! $course->is_publish() ) {
				throw new Exception( 'Course is not publish' );
			}

			if ( $course->is_free() ) {
				throw new Exception( 'Course is free' );
			}

			if ( $this->can_retry_course( $course_id ) ) {
				throw new Exception( 'Course is has retake' );
			}

			// If course is reached limitation.
			if ( ! $course->is_in_stock() ) {
				$message = apply_filters(
					'learn-press/maximum-students-reach',
					esc_html__( 'This course is out of stock', 'learnpress' )
				);

				if ( $message ) {
					learn_press_display_message( $message );
				}

				throw new Exception( $message );
			}

			if ( $this->has_purchased_course( $course_id ) ) {
				// If the order contains course is processing
				$order = $this->get_course_order( $course_id );
				if ( $order && $order->get_status() === 'processing' ) {
					$message = apply_filters(
						'learn-press/order-processing-message',
						__( 'Your order is waiting for processing', 'learnpress' )
					);

					if ( $message ) {
						learn_press_display_message( $message );
					}

					throw new Exception( $message );
				} else {
					throw new Exception( 'Course is purchased' );
				}
			}

			$is_blocked_course  = 0 === $course->timestamp_remaining_duration();
			$is_enrolled_course = $this->has_enrolled_course( $course_id );
			$is_finished_course = $this->has_finished_course( $course_id );

			if ( $course->allow_repurchase() ) {
				if ( $is_enrolled_course && ! $is_blocked_course ) {
					throw new Exception( 'Course is enrolled' );
				}
			} else {
				if ( $this->has_enrolled_or_finished( $course_id ) ) {
					throw new Exception( 'Course is enrolled' );
				}
			}
		} catch ( Exception $e ) {
			$can_purchase = false;
		}

		return apply_filters( 'learn-press/user/can-purchase-course', $can_purchase, $this->get_id(), $course_id );
	}

	/**
	 * Check condition show finish course button
	 *
	 * @param $course
	 * @return array
	 * @author nhamdv
	 * @editor tungnx
	 * @version 1.0.1
	 */
	public function can_show_finish_course_btn( $course ): array {
		$return = array(
			'status'  => 'fail',
			'message' => '',
		);

		try {
			if ( ! $course ) {
				throw new Exception( esc_html__( 'Error: No Course or User available.', 'learnpress' ) );
			}

			$course_id = $course->get_id();

			/**
			 * Re-calculate result course of user
			 */
			$course_data    = $this->get_course_data( $course_id );
			$course_results = $course_data->calculate_course_results();
			// End

			// Get result to check
			$is_all_completed = $this->is_completed_all_items( $course_id );

			if ( ! $this->is_course_in_progress( $course_id ) ) {
				throw new Exception( esc_html__( 'Error: Course is not in-progress.', 'learnpress' ) );
			}

			$has_finish = get_post_meta( $course_id, '_lp_has_finish', true ) ? get_post_meta( $course_id, '_lp_has_finish', true ) : 'yes';
			$is_passed  = $this->has_reached_passing_condition( $course_id );

			if ( ! $is_passed && $has_finish === 'no' ) {
				throw new Exception( esc_html__( 'Error: Course is not has finish.', 'learnpress' ) );
			}

			if ( ! $is_all_completed && $has_finish === 'yes' && ! $is_passed ) {
				throw new Exception( esc_html__( 'Error: Cannot finish course.', 'learnpress' ) );
			}

			if ( ! apply_filters( 'lp_can_finish_course', true ) ) {
				throw new Exception( esc_html__( 'Error: Filter disable finish course.', 'learnpress' ) );
			}

			$return['status'] = 'success';
		} catch ( Exception $e ) {
			$return['message'] = $e->getMessage();
		}

		return $return;
	}

	/**
	 * Start quiz for the user.
	 *
	 * @param int  $quiz_id
	 * @param int  $course_id
	 * @param bool $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @return LP_User_Item_Quiz|bool|WP_Error
	 * @throws Exception
	 */
	public function start_quiz( int $quiz_id, int $course_id = 0, bool $wp_error = false ) {
		try {
			$item_id = learn_press_get_request( 'lp-preview' );

			if ( $item_id ) {
				learn_press_add_message( __( 'You cannot start a quiz in preview mode.', 'learnpress' ), 'error' );
				wp_safe_redirect( learn_press_get_preview_url( $item_id ) );
				exit();
			}

			// Validate course and quiz
			$course_id = $this->_verify_course_item( $quiz_id, $course_id );
			if ( ! $course_id ) {
				throw new Exception(
					__( 'Course does not exist or does not contain the quiz', 'learnpress' ),
					LP_INVALID_QUIZ_OR_COURSE
				);
			}

			$course = learn_press_get_course( $course_id );
			//$access_level = $this->get_course_access_level( $course_id );

			// If user has already finished the course
			if ( $this->has_finished_course( $course_id ) ) {
				throw new Exception(
					__( 'You have already finished the course of this quiz', 'learnpress' ),
					LP_COURSE_IS_FINISHED
				);
			}

			if ( ! $this->has_enrolled_course( $course_id ) || ! $this->is_course_in_progress( $course_id ) ) {
				if ( ! $course->is_no_required_enroll() ) {
					throw new Exception(
						__( 'Please enroll course before starting quiz.', 'learnpress' ),
						LP_COURSE_IS_FINISHED
					);
				}
			}

			// Check if user has already started or completed quiz
			if ( $this->has_item_status( array( 'started', 'completed' ), $quiz_id, $course_id ) ) {
				throw new Exception(
					__( 'User has started or completed quiz', 'learnpress' ),
					LP_QUIZ_HAS_STARTED_OR_COMPLETED
				);
			}
			$user = LP_Global::user();

			if ( $user->is_guest() ) {
				// if course required enroll => print message "You have to login for starting quiz"
				if ( ! $course->is_no_required_enroll() ) {
					throw new Exception( __( 'You have to login for starting quiz.', 'learnpress' ), LP_REQUIRE_LOGIN );
				}
			}

			/**
			 * Hook can start quiz
			 *
			 * @see learn_press_hk_before_start_quiz
			 */
			$can_start_quiz = apply_filters(
				'learn-press/before-start-quiz',
				true,
				$quiz_id,
				$course_id,
				$this->get_id()
			);

			if ( ! $can_start_quiz ) {
				return false;
			}

			$user_quiz = learn_press_user_start_quiz( $quiz_id, false, $course_id, $wp_error );

			/**
			 * Hook quiz started
			 *
			 * @since 3.0.0
			 */
			do_action( 'learn-press/user/quiz-started', $quiz_id, $course_id, $this->get_id() );

			// $return = $user_quiz->get_mysql_data();
			$return = $user_quiz;
		} catch ( Exception $ex ) {
			$return = $wp_error ? new WP_Error( $ex->getCode(), $ex->getMessage() ) : false;
		}

		return $return;
	}

	/**
	 * Finish a quiz for the user and save all data needed
	 *
	 * @param int  $quiz_id
	 * @param int  $course_id
	 * @param bool $wp_error
	 *
	 * @return LP_User_Item_Quiz|bool|WP_Error
	 */
	public function finish_quiz( int $quiz_id, int $course_id, bool $wp_error = false ) {
		$return = false;

		try {
			// Validate course and quiz
			/*_verify_course_item = $this->_verify_course_item( $quiz_id, $course_id );
			if ( $course_id ) {
				throw new Exception(
					__( 'Course is not exists or does not contain the quiz', 'learnpress' ),
					LP_INVALID_QUIZ_OR_COURSE
				);
			}*/

			// If user has already finished the course
			if ( $this->has_finished_course( $course_id ) ) {
				throw new Exception(
					__( 'User has already finished course of this quiz', 'learnpress' ),
					LP_COURSE_IS_FINISHED
				);

			}

			// Check if user has already started or completed quiz
			if ( $this->has_item_status( array( 'completed' ), $quiz_id, $course_id ) ) {
				throw new Exception(
					__( 'User has completed quiz', 'learnpress' ),
					LP_QUIZ_HAS_STARTED_OR_COMPLETED
				);
			}

			$user_quiz = $this->get_item_data( $quiz_id, $course_id );

			$user_quiz->finish();

			do_action( 'learn-press/user/quiz-finished', $quiz_id, $course_id, $this->get_id() );
		} catch ( Exception $ex ) {
			$return = $wp_error ? new WP_Error( $ex->getCode(), $ex->getMessage() ) : false;
		}

		return $return;
	}

	/**
	 * Retake a quiz for the user
	 *
	 * @param int  $quiz_id
	 * @param int  $course_id
	 * @param bool $wp_error
	 *
	 * @return bool|WP_Error|LP_User_Item_Quiz
	 *
	 * @throws Exception
	 */
	public function retake_quiz( int $quiz_id, int $course_id, bool $wp_error = false ) {
		$return = false;

		try {
			$course_id = $this->_verify_course_item( $quiz_id, $course_id );

			if ( false === $course_id ) {
				throw new Exception(
					sprintf(
						__(
							'Course does not exist or does not contain the quiz.',
							'learnpress'
						),
						__CLASS__,
						__FUNCTION__
					),
					LP_INVALID_QUIZ_OR_COURSE
				);
			}

			// If user has already finished the course.
			if ( $this->has_finished_course( $course_id ) ) {
				throw new Exception(
					sprintf(
						__( 'You can not redo a quiz in a finished course.', 'learnpress' ),
						__CLASS__,
						__FUNCTION__
					),
					LP_COURSE_IS_FINISHED
				);

			}

			// Check if user has already started or completed quiz
			if ( ! $this->has_item_status( array( 'completed' ), $quiz_id, $course_id ) ) {
				throw new Exception(
					sprintf(
						__( '%1$s::%2$s - User has not completed quiz.', 'learnpress' ),
						__CLASS__,
						__FUNCTION__
					),
					LP_QUIZ_HAS_STARTED_OR_COMPLETED
				);
			}

			$allow_attempts = learn_press_get_quiz_max_retrying( $quiz_id, $course_id );

			if ( ! $this->has_retake_quiz( $quiz_id, $course_id ) ) {
				throw new Exception(
					sprintf(
						__( '%1$s::%2$s - Your Quiz can\'t retake.', 'learnpress' ),
						__CLASS__,
						__FUNCTION__
					),
					LP_QUIZ_HAS_STARTED_OR_COMPLETED
				);
			}

			$return = learn_press_user_retake_quiz( $quiz_id, false, $course_id, $wp_error );

			do_action( 'learn-press/user/quiz-retried', $quiz_id, $course_id, $this->get_id() );
		} catch ( Exception $ex ) {
			$return = $wp_error ? new WP_Error( $ex->getCode(), $ex->getMessage() ) : false;
			do_action( 'learn-press/user/retake-quiz-failure', $quiz_id, $course_id, $this->get_id() );
		}

		return $return;
	}
}
