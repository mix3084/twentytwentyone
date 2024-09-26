<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password,
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

$twenty_twenty_one_comment_count = get_comments_number();
?>

<div id="comments" class="comments-area default-max-width <?php echo get_option( 'show_avatars' ) ? 'show-avatars' : ''; ?>">

	<?php
	if ( have_comments() ) :
		// Получаем средние оценки из полей ACF для текущего поста (сервиса)
		$average_usability 			= get_field( 'average_usability' );
		$average_functionality 		= get_field( 'average_functionality' );
		$average_customizability	= get_field( 'average_customizability' );
		$average_overall 			= get_field( 'average_overall' );
		?>

		<?php 
		if ( 
			$average_usability > 0 || 
			$average_functionality > 0 || 
			$average_customizability > 0 ||
			$average_overall > 0 
		) : 
		?>
			<div class="average-ratings">
				<h3><?php _e( 'Average Ratings:', 'twentytwentyone' ); ?></h3>
				<ul>
					<li>
						<strong><?php _e( 'Overall Rating:', 'twentytwentyone' ); ?></strong> 
						<?php echo get_star_rating_html( $average_overall ); ?>
					</li>
					<li>
						<strong><?php _e( 'Usability:', 'twentytwentyone' ); ?></strong> 
						<?php echo get_star_rating_html( $average_usability ); ?>
					</li>
					<li>
						<strong><?php _e( 'Functionality:', 'twentytwentyone' ); ?></strong> 
						<?php echo get_star_rating_html( $average_functionality ); ?>
					</li>
					<li>
						<strong><?php _e( 'Customizability:', 'twentytwentyone' ); ?></strong> 
						<?php echo get_star_rating_html( $average_customizability ); ?>
					</li>
				</ul>
			</div>
		<?php endif; ?>

		<h2 class="comments-title">
			<?php if ( '1' === $twenty_twenty_one_comment_count ) : ?>
				<?php esc_html_e( '1 comment', 'twentytwentyone' ); ?>
			<?php else : ?>
				<?php
				printf(
					/* translators: %s: Comment count number. */
					esc_html( _nx( '%s comment', '%s comments', $twenty_twenty_one_comment_count, 'Comments title', 'twentytwentyone' ) ),
					esc_html( number_format_i18n( $twenty_twenty_one_comment_count ) )
				);
				?>
			<?php endif; ?>
		</h2><!-- .comments-title -->

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'avatar_size' => 60,
					'style'       => 'ol',
					'short_ping'  => true,
					'callback'    => 'custom_comment_template',
				)
			);
			?>
		</ol><!-- .comment-list -->

		<?php
		the_comments_pagination(
			array(
				'before_page_number' => esc_html__( 'Page', 'twentytwentyone' ) . ' ',
				'mid_size'           => 0,
				'prev_text'          => sprintf(
					'%s <span class="nav-prev-text">%s</span>',
					is_rtl() ? twenty_twenty_one_get_icon_svg( 'ui', 'arrow_right' ) : twenty_twenty_one_get_icon_svg( 'ui', 'arrow_left' ),
					esc_html__( 'Older comments', 'twentytwentyone' )
				),
				'next_text'          => sprintf(
					'<span class="nav-next-text">%s</span> %s',
					esc_html__( 'Newer comments', 'twentytwentyone' ),
					is_rtl() ? twenty_twenty_one_get_icon_svg( 'ui', 'arrow_left' ) : twenty_twenty_one_get_icon_svg( 'ui', 'arrow_right' )
				),
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'twentytwentyone' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => esc_html__( 'Leave a comment', 'twentytwentyone' ),
			'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title">',
			'title_reply_after'  => '</h2>',
		)
	);
	?>

</div><!-- #comments -->
