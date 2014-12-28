<?php

/* from http://codex.wordpress.org/Child_Themes - Using functions.php

Unlike style.css, the functions.php of a child theme does not override its counterpart from the parent. Instead, it is loaded in addition to the parent’s functions.php. (Specifically, it is loaded right before the parent’s file.) 

*/

add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_style' );
function enqueue_parent_theme_style() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}




/* redirect users to front page after login */
//    code not working for WP4? outdated?
//    https://wordpress.org/support/topic/how-can-i-redirect-users-to-the-front-page-after-log-in
