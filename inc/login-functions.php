<?
// Шорткод для единой страницы входа, регистрации и выхода
function custom_login_register_logout_form() {
    // Проверяем, авторизован ли пользователь
    if ( is_user_logged_in() ) {
        // Если пользователь авторизован, выводим кнопку выхода
        $logout_url = wp_logout_url( home_url() );
        return '<p>' . __( 'You are logged in.', 'twentytwentyone' ) . '</p>
                <p><a href="' . esc_url( $logout_url ) . '" class="btn">' . __( 'Logout', 'twentytwentyone' ) . '</a></p>';
    } else {
        // Если пользователь не авторизован, выводим формы входа и регистрации
        return custom_login_form_html() . custom_registration_form_html();
    }
}

// Форма входа
function custom_login_form_html() {
    return '
        <h2>' . __( 'Login', 'twentytwentyone' ) . '</h2>
        <form method="post" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">
            <p>
                <label for="username">' . __( 'Username or Email', 'twentytwentyone' ) . '</label>
                <input type="text" name="username" id="username" required />
            </p>
            <p>
                <label for="password">' . __( 'Password', 'twentytwentyone' ) . '</label>
                <input type="password" name="password" id="password" required />
            </p>
            <p>
                <input type="submit" name="submit_login" value="' . __( 'Login', 'twentytwentyone' ) . '"/>
            </p>
        </form>';
}

// Форма регистрации
function custom_registration_form_html() {
    return '
        <h2>' . __( 'Register', 'twentytwentyone' ) . '</h2>
        <form method="post" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">
            <p>
                <label for="username">' . __( 'Username', 'twentytwentyone' ) . '</label>
                <input type="text" name="username" id="username" required />
            </p>
            <p>
                <label for="email">' . __( 'Email', 'twentytwentyone' ) . '</label>
                <input type="email" name="email" id="email" required />
            </p>
            <p>
                <label for="password">' . __( 'Password', 'twentytwentyone' ) . '</label>
                <input type="password" name="password" id="password" required />
            </p>
            <p>
                <input type="submit" name="submit_registration" value="' . __( 'Register', 'twentytwentyone' ) . '"/>
            </p>
        </form>';
}

// Обработка регистрации
function custom_registration_handler() {
    if ( isset( $_POST['submit_registration'] ) ) {
        $username   = sanitize_user( $_POST['username'] );
        $email      = sanitize_email( $_POST['email'] );
        $password   = esc_attr( $_POST['password'] );

        // Проверяем ошибки
        if ( ! username_exists( $username ) && ! email_exists( $email ) ) {
            // Создаём пользователя
            $user_id = wp_create_user( $username, $password, $email );

            if ( ! is_wp_error( $user_id ) ) {
                // Отключаем верхнюю панель для новых пользователей
                update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

                // Автоматическая авторизация пользователя после регистрации
                wp_set_current_user( $user_id );
                wp_set_auth_cookie( $user_id );
                wp_redirect( home_url() ); // Перенаправление на главную после регистрации
                exit;
            } else {
                echo '<p>' . __( 'Error during registration.', 'twentytwentyone' ) . '</p>';
            }
        } else {
            echo '<p>' . __( 'Username or email already exists.', 'twentytwentyone' ) . '</p>';
        }
    }
}

// Обработка входа
function custom_login_handler() {
    if ( isset( $_POST['submit_login'] ) ) {
        $username = sanitize_user( $_POST['username'] );
        $password = esc_attr( $_POST['password'] );

        $credentials = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        );

        // Пытаемся авторизовать пользователя
        $user = wp_signon( $credentials, false );

        if ( ! is_wp_error( $user ) ) {
            wp_redirect( home_url() ); // Перенаправление на главную после входа
            exit;
        } else {
            echo '<p>' . __( 'Invalid username or password.', 'twentytwentyone' ) . '</p>';
        }
    }
}

// Подключаем шорткод и обработчики форм
add_shortcode( 'custom_login_register_form', 'custom_login_register_logout_form' );
add_action( 'init', 'custom_registration_handler' );
add_action( 'init', 'custom_login_handler' );


add_action( 'admin_init', 'restrict_admin_access_for_subscribers' );
function restrict_admin_access_for_subscribers() {
    // Проверяем, авторизован ли пользователь
    if ( is_user_logged_in() ) {
        // Получаем данные о текущем пользователе
        $user = wp_get_current_user();

        // Проверяем, есть ли у пользователя роль "subscriber" и находится ли он в панели администратора
        if ( in_array( 'subscriber', (array) $user->roles ) && is_admin() ) {
            // Перенаправляем на главную страницу
            wp_redirect( home_url() );
            exit;
        }
    }
}

// Изменяем ссылку на авторизацию в комментариях
function custom_must_log_in_text( $defaults ) {
    // Указываем нужную ссылку на страницу авторизации
    $login_url = home_url( '/my-account/' ); // Укажите правильную ссылку на страницу личного кабинета или авторизации

    // Формируем кастомное сообщение с новой ссылкой
    $defaults['must_log_in'] = '<p class="must-log-in">' . __( 'To send a comment, you need to' ) . ' <a href="' . esc_url( $login_url ) . '">' . __( 'log in', 'twentytwentyone' ) . '</a>.</p>';

    return $defaults;
}

// Применяем фильтр для изменения сообщения
add_filter( 'comment_form_defaults', 'custom_must_log_in_text' );

// Убираем сообщение "Вы вошли как" в форме комментариев
add_filter( 'comment_form_defaults', 'remove_logged_in_message' );
function remove_logged_in_message( $defaults ) {
    $defaults['logged_in_as'] = ''; // Убираем сообщение полностью
    return $defaults;
}

// Убираем ссылки из сообщения "Вы вошли как" в форме комментариев
function custom_logged_in_message( $defaults ) {
    // Получаем имя текущего пользователя
    $user = wp_get_current_user();
    // Оставляем только текст без ссылок
    $defaults['logged_in_as'] = '<p class="logged-in-as">' . sprintf( __( 'You entered as %1$s.', 'twentytwentyone' ), esc_html( $user->display_name ) ) . '</p>';
    return $defaults;
}

add_filter( 'comment_form_defaults', 'custom_logged_in_message' );

// Применяем фильтр для изменения ссылки
add_filter( 'comment_reply_link', 'custom_comment_reply_link', 10, 4 );
function custom_comment_reply_link( $link, $args, $comment, $post ) {
    // Указываем ссылку на страницу личного кабинета
    $login_url = home_url( '/my-account/' ); // Укажите правильную ссылку на страницу авторизации/личного кабинета

    // Заменяем стандартную ссылку на нашу страницу
    $link = str_replace( wp_login_url( get_permalink() ), esc_url( $login_url ), $link );
    
    return $link;
}