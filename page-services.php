<?php
/**
 * Шаблон страницы "Services" для вывода списка категорий и сервисов
 *
 * Template Name: Services
 */

get_header(); 
// Выводим хлебные крошки
custom_breadcrumbs_services();
?>

<div class="default-max-width">
	<h1 class="page-title"><?php the_title(); ?></h1>

	<div class="service-categories">
		<?php
		// Получаем все категории таксономии service-category
		$categories = get_terms( array(
			'taxonomy' => 'service-category',
			'hide_empty' => true,
		) );

		// Проходим по каждой категории
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
			foreach ( $categories as $category ) :
				?>
				<div class="service-category">
					<h2 class="category-title">
						<a href="<?php echo get_term_link( $category ); ?>">
							<?php echo esc_html( $category->name ); ?>
						</a>
					</h2>
					<div class="category-description">
						<?php echo esc_html( $category->description ); ?>
					</div>

					<div class="service-list">
						<?php
						// Получаем 5 последних сервисов в этой категории
						$services = new WP_Query( array(
							'post_type' 		=> 'services',
							'posts_per_page' 	=> 5,
							'tax_query' => array(
								array(
									'taxonomy' 	=> 'service-category',
									'field' 	=> 'term_id',
									'terms' 	=> $category->term_id,
								),
							),
						) );

						// Выводим сервисы, если они есть
						if ( $services->have_posts() ) :
							while ( $services->have_posts() ) : $services->the_post();
								?>
								<div class="service-item">
									<h3 class="service-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>
									<p><a href="<?the_field('service_url');?>">САЙТ (ссылка)</a></p>

									<?php
									// Выводим миниатюру записи (логотип сервиса)
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
										$average_usability 			= get_field( 'average_usability' );
										$average_functionality 		= get_field( 'average_functionality' );
										$average_customizability 	= get_field( 'average_customizability' );
										$average_overall 			= get_field( 'average_overall' );

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
								</div>
								<?php
							endwhile;
							wp_reset_postdata();
						else :
							echo '<p>' . __( 'No services found in this category.', 'twentytwentyone' ) . '</p>';
						endif;
						?>
					</div>

					<!-- Кнопка "Посмотреть все" -->
					<a class="btn" href="<?php echo get_term_link( $category ); ?>">
						<?php _e( 'View all services in this category', 'twentytwentyone' ); ?>
					</a>
				</div>
				<?php
			endforeach;
		else :
			echo '<p>' . __( 'No categories found.', 'twentytwentyone' ) . '</p>';
		endif;
		?>
	</div>
</div>

<?php
get_footer();
?>
