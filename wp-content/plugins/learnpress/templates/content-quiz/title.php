<?php
/**
 * Template for displaying title of quiz.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/content-quiz/title.php.
 *
 * @author   ThimPress
 * @package  Learnpress/Templates
 * @version  3.0.0
 */

defined( 'ABSPATH' ) || exit();

$quiz   = LP_Global::course_item_quiz();
$course = LP_Global::course();
$title  = $quiz->get_heading_title( 'display' );

if ( ! $title ) {
	return;
}
?>

<h3 class="course-item-title quiz-title"><?php echo esc_html( $title ); ?></h3>
