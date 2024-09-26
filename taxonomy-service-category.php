<?php
/**
 * Шаблон для отображения записей в категориях сервисов
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();
// Выводим хлебные крошки
custom_breadcrumbs_services();

$description = get_the_archive_description();
?>

<div class="default-max-width">
	<?php if ( have_posts() ) : ?>

		<header class="page-header alignwide">
			<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
			<?php if ( $description ) : ?>
				<div class="archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
			<?php endif; ?>
		</header><!-- .page-header -->

		<div class="service-list">
			<?php
			// Цикл для вывода всех записей в текущей категории сервиса
			while ( have_posts() ) : the_post();
				?>
				<div class="service-item">
					<h2 class="service-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<p><a href="<?the_field('service_url');?>">САЙТ (ссылка)</a></p>
					<?php
					// Вывод миниатюры записи (логотипа сервиса)
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'thumbnail' );
					}
					?>

					<div class="service-excerpt">
						<?php the_excerpt(); ?>
					</div>

					<!-- Вывод рейтинга сервиса -->
					<div class="service-ratings">
						<?php
						// Получаем данные рейтинга из полей ACF
						$average_usability = get_field( 'average_usability' );
						$average_functionality = get_field( 'average_functionality' );
						$average_customizability = get_field( 'average_customizability' );
						$average_overall = get_field( 'average_overall' );

						if ( $average_overall ) :
							?>
							<ul>
								<li><strong><?php _e( 'Overall Rating:', 'twentytwentyone' ); ?></strong> <?php echo get_star_rating_html( $average_overall ); ?></li>
								<li><strong><?php _e( 'Usability:', 'twentytwentyone' ); ?></strong> <?php echo get_star_rating_html( $average_usability ); ?></li>
								<li><strong><?php _e( 'Functionality:', 'twentytwentyone' ); ?></strong> <?php echo get_star_rating_html( $average_functionality ); ?></li>
								<li><strong><?php _e( 'Customizability:', 'twentytwentyone' ); ?></strong> <?php echo get_star_rating_html( $average_customizability ); ?></li>
							</ul>
						<?php endif; ?>
					</div>
				</div><!-- .service-item -->
				<?php
			endwhile;
			?>
		</div><!-- .service-list -->

		<?php twenty_twenty_one_the_posts_navigation(); ?>

	<?php else : ?>
		<?php get_template_part( 'template-parts/content/content-none' ); ?>
	<?php endif; ?>
</div><!-- .default-max-width -->

<?php
get_footer();
