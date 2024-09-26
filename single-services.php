<?php
/**
 * The template for displaying all single service posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();

// Выводим хлебные крошки
custom_breadcrumbs_services();

/* Start the Loop */
while ( have_posts() ) :
	the_post();

	// Подключаем часть шаблона для вывода основного контента
	get_template_part( 'template-parts/content/content-single' );

    // Выводим ссылку на сайт сервиса, если поле 'service_url' заполнено
	if ( get_field( 'service_url' ) ) : ?>
		<p class="default-max-width"><a href="<?php the_field( 'service_url' ); ?>" target="_blank">Ссылка на сайт сервиса</a></p>
	<?php endif;

	// Проверяем, открыты ли комментарии или есть хотя бы один комментарий
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

	// Предыдущий/следующий пост навигация
	$twentytwentyone_next = is_rtl() ? twenty_twenty_one_get_icon_svg( 'ui', 'arrow_left' ) : twenty_twenty_one_get_icon_svg( 'ui', 'arrow_right' );
	$twentytwentyone_prev = is_rtl() ? twenty_twenty_one_get_icon_svg( 'ui', 'arrow_right' ) : twenty_twenty_one_get_icon_svg( 'ui', 'arrow_left' );

	$twentytwentyone_next_label     = esc_html__( 'Next post', 'twentytwentyone' );
	$twentytwentyone_previous_label = esc_html__( 'Previous post', 'twentytwentyone' );

	the_post_navigation(
		array(
			'next_text' => '<p class="meta-nav">' . $twentytwentyone_next_label . $twentytwentyone_next . '</p><p class="post-title">%title</p>',
			'prev_text' => '<p class="meta-nav">' . $twentytwentyone_prev . $twentytwentyone_previous_label . '</p><p class="post-title">%title</p>',
		)
	);

endwhile; // End of the loop.

get_footer();
