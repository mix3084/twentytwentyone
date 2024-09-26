<?
// Удаляем поле "Сайт" из формы комментариев
add_filter( 'comment_form_default_fields', 'remove_comment_url_field' );
function remove_comment_url_field( $fields ) {
	unset( $fields['url'] );
	return $fields;
}

/**
 * Функция для генерации HTML-разметки звёздочек на основе числовой оценки.
 *
 * @param float $rating Числовая оценка, которую нужно преобразовать в звездочки 
 * (значение от 0 до 5).
 * Может быть целым числом или иметь половинное значение (например, 4.5).
 *
 * @return string $output Возвращает строку HTML-кода с соответствующими звёздочками:
 * полные звёзды — для целых чисел, половинные — для дробных значений, 
 * пустые — для отсутствующих баллов.
 * Вывод содержит до 5 звёзд в зависимости от переданной оценки.
 *
 * Логика:
 * - Целое число звёзд (полные звёзды) выводится для каждой единицы в диапазоне от 1 до 5.
 * - Половинная звезда выводится, если значение оценки находится между целыми числами,
 * например, 4.5.
 * - Пустые звёзды выводятся, если значение меньше требуемого для полной 
 * или половинной звезды.
 */
function get_star_rating_html( $rating ) {
	$full_star 	= '<span class="star-emoji">★</span>';
	$half_star 	= '<span class="star-emoji half-star">☆</span>';
	$empty_star = '<span class="star-emoji">☆</span>';
	
	$output = '';

	for ( $i = 1; $i <= 5; $i++ ) {
		if ( $rating >= $i ) {
			$output .= $full_star;
		} elseif ( $rating >= $i - 0.5 ) {
			$output .= $half_star;
		} else {
			$output .= $empty_star;
		}
	}

	return $output;
}

/**
 * Кастомная функция для вывода комментариев с рейтингами, сохранёнными через ACF.
 *
 * @param object $comment Объект комментария, содержащий данные о пользователе 
 * и тексте комментария.
 * @param array  $args Аргументы для управления выводом комментариев, 
 * такие как глубина комментария и аватар.
 * @param int    $depth Глубина текущего комментария в цепочке 
 * (для поддержки вложенных комментариев).
 *
 * Функция проверяет наличие плагина ACF и выводит три оценки 
 * (удобство, функциональность, кастомизируемость), если они были добавлены к комментарию.
 * Оценки отображаются в виде звёзд, которые генерируются через функцию 
 * get_star_rating_html().
 */
function custom_comment_template( $comment, $args, $depth ) {
	// Проверяем, доступен ли ACF
	if ( function_exists( 'get_field' ) ) {
		// Получаем оценки из ACF
		$usability 			= get_field( 'comments_usability', $comment );
		$functionality 		= get_field( 'comments_functionality', $comment );
		$customizability 	= get_field( 'comments_customizability', $comment );
	} else {
		// Если ACF не доступен, присваиваем пустые значения
		$usability 			= '';
		$functionality 		= '';
		$customizability 	= '';
	}

	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php echo get_avatar( $comment, 60 ); ?>
					<?php printf( '<b class="fn">%s</b>', get_comment_author_link() ); ?>
				</div><!-- .comment-author -->

				<div class="comment-metadata">
					<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
						<?php
						/* translators: 1: comment date, 2: comment time */
						printf( __( '%1$s at %2$s' ), get_comment_date(), get_comment_time() );
						?>
					</a>
					<?php edit_comment_link( __( '(Edit)' ), '  ', '' ); ?>
				</div><!-- .comment-metadata -->
			</footer><!-- .comment-meta -->

			<div class="comment-content">
				<?php comment_text(); ?>

				<?php if ( $usability || $functionality || $customizability ) : ?>
					<div class="comment-ratings">
						<h4><?php _e( 'Ratings:', 'twentytwentyone' ); ?></h4>
						<ul>
							<?php if ( $usability ) : ?>
								<li>
									<strong><?php _e( 'Usability:', 'twentytwentyone' ); ?></strong> 
									<?php echo get_star_rating_html( $usability ); ?>
								</li>
							<?php endif; ?>
							<?php if ( $functionality ) : ?>
								<li>
									<strong><?php _e( 'Functionality:', 'twentytwentyone' ); ?></strong> 
									<?php echo get_star_rating_html( $functionality ); ?>
								</li>
							<?php endif; ?>
							<?php if ( $customizability ) : ?>
								<li>
									<strong><?php _e( 'Customizability:', 'twentytwentyone' ); ?></strong>
									<?php echo get_star_rating_html( $customizability ); ?>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div><!-- .comment-content -->

			<div class="reply">
				<?php
				comment_reply_link(
					array_merge(
						$args,
						array(
							'reply_text' => __( 'Reply', 'twentytwentyone' ),
							'depth'      => $depth,
							'max_depth'  => $args['max_depth'],
						)
					)
				);
				?>
			</div><!-- .reply -->
		</article><!-- .comment-body -->
	</li>
	<?php
}

/**
 * Округление до ближайшего 0.5
 */
function round_to_half( $value ) {
	return round( $value * 2 ) / 2;
}

/**
 * Универсальная функция для обновления средних оценок при создании, редактировании или удалении комментариев.
 *
 * @param int        $comment_id      The comment ID.
 * @param WP_Comment $comment Comment object.
 */
function update_service_ratings_universal( $comment_id, $comment ) {
	$comment = get_comment( $comment_id );
	if ( ! $comment ) {
		return;
	}

	$post_id = $comment->comment_post_ID;

	if ( get_post_type( $post_id ) !== 'services' ) {
		return;
	}

	$comments = get_comments( array(
		'post_id'   => $post_id,
		'status'    => 'approve',
	) );

	// Инициализируем переменные для сумм оценок и количество оценённых комментариев по каждому критерию
	$total_usability        = 0;
	$total_functionality    = 0;
	$total_customizability  = 0;
	$usability_count        = 0;
	$functionality_count    = 0;
	$customizability_count  = 0;

	// Проходим по каждому комментарию и суммируем оценки, если они существуют
	foreach ( $comments as $comment ) {
		$usability 			= get_field( 'comments_usability', $comment );
		$functionality 		= get_field( 'comments_functionality', $comment );
		$customizability 	= get_field( 'comments_customizability', $comment );

		// Проверяем наличие каждой оценки и добавляем её в сумму, если она задана
		if ( $usability ) {
			$total_usability += (float) $usability;
			$usability_count++;
		}
		if ( $functionality ) {
			$total_functionality += (float) $functionality;
			$functionality_count++;
		}
		if ( $customizability ) {
			$total_customizability += (float) $customizability;
			$customizability_count++;
		}
	}

	// Рассчитываем средние значения только для тех критериев, где есть оценки
	$average_usability = $usability_count > 0 ? $total_usability / $usability_count : 0;
	$average_functionality = $functionality_count > 0 ? $total_functionality / $functionality_count : 0;
	$average_customizability = $customizability_count > 0 ? $total_customizability / $customizability_count : 0;
	
	// Рассчитываем общую оценку только если есть хотя бы одна оценка
	$average_overall = 0;
	$rating_criteria_count = 0;

	if ( $usability_count > 0 ) {
		$average_overall += $average_usability;
		$rating_criteria_count++;
	}
	if ( $functionality_count > 0 ) {
		$average_overall += $average_functionality;
		$rating_criteria_count++;
	}
	if ( $customizability_count > 0 ) {
		$average_overall += $average_customizability;
		$rating_criteria_count++;
	}

	$average_overall = $rating_criteria_count > 0 ? $average_overall / $rating_criteria_count : 0;

	update_field( 'average_usability',          round_to_half( $average_usability ), $post_id );
	update_field( 'average_functionality',      round_to_half( $average_functionality ), $post_id );
	update_field( 'average_customizability',    round_to_half( $average_customizability ), $post_id );
	update_field( 'average_overall',            round_to_half( $average_overall ), $post_id );
}
add_action( 'wp_insert_comment', 'update_service_ratings_universal', 10, 2 );
add_action( 'edit_comment', 'update_service_ratings_universal', 10, 2 );
add_action( 'delete_comment', 'update_service_ratings_universal', 10, 1 );

// Функция для вывода хлебных крошек для раздела сервисов
function custom_breadcrumbs_services() {
    // Настройки
    $separator = ' &gt; '; // Разделитель
    $home_title = 'Главная'; // Название для главной страницы
    $services_title = 'Сервисы'; // Название для страницы "Сервисы"
    $services_url = home_url( '/services/' ); // URL страницы архива сервисов

    // Получаем объект текущей страницы
    global $post;
    $home_url = home_url('/');

	// Получаем slug и ID страницы
    $current_page_slug = $post->post_name;
    $current_page_id = $post->ID;
    
    // Начало хлебных крошек
    echo '<ul class="breadcrumbs">';

    // Ссылка на главную
    echo '<li class="breadcrumbs-item"><a href="' . $home_url . '">' . $home_title . '</a></li>';
    echo '<li class="breadcrumbs-separator">' . $separator . '</li>';

	// Логика отображения страницы "Сервисы" в хлебных крошках
	if ( $current_page_id == 21 || $current_page_slug == 'services' ) {
        // Если текущая страница имеет ID 21 или slug 'services', выводим текст
        echo '<li class="breadcrumbs-item">' . $services_title . '</li>';
    } else {
        // В остальных случаях выводим ссылку на "Сервисы"
        echo '<li class="breadcrumbs-item"><a href="' . $services_url . '">' . $services_title . '</a></li>';
    }

    // Если это архив всех сервисов
    if ( is_post_type_archive( 'services' ) ) {
        // Страница архива сервисов как текущая
        echo '<li class="breadcrumbs-item">' . $services_title . '</li>';
    } elseif ( is_singular( 'services' ) ) {
        // Для записи типа "services" (например, отдельная страница сервиса)

        echo '<li class="breadcrumbs-separator">' . $separator . '</li>';

        // Получаем таксономию "service-category"
        $terms = get_the_terms( $post->ID, 'service-category' );
        if ( $terms && ! is_wp_error( $terms ) ) {
            $term = array_shift( $terms ); // Берём первую категорию
            echo '<li class="breadcrumbs-item"><a href="' . get_term_link( $term ) . '">' . esc_html( $term->name ) . '</a></li>';
            echo '<li class="breadcrumbs-separator">' . $separator . '</li>';
        }
        // Название текущего сервиса
        echo '<li class="breadcrumbs-item">' . get_the_title() . '</li>';
    } elseif ( is_tax( 'service-category' ) ) {
        // Если это архив таксономии "service-category" (категория сервисов)

        echo '<li class="breadcrumbs-separator">' . $separator . '</li>';

        // Название категории сервиса
        $term = get_queried_object();
        echo '<li class="breadcrumbs-item">' . esc_html( $term->name ) . '</li>';
    }

    echo '</ul>';
}
