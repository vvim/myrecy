<?php

/* from http://codex.wordpress.org/Child_Themes - Using functions.php

Unlike style.css, the functions.php of a child theme does not override its counterpart from the parent. Instead, it is loaded in addition to the parent’s functions.php. (Specifically, it is loaded right before the parent’s file.) 

*/

add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_style' );
function enqueue_parent_theme_style() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}


//to translate with gettext: see https://pippinsplugins.com/introduction-gettext-filter-wordpress/
function pippin_gettext_demo($translated_text, $text , $domain)
{
	if( $translated_text == 'Voer a.u.b. je gebruikersnaam of e-mailadres in. Je ontvangt per e-mail een link waarmee je een nieuw wachtwoord kunt aanmaken.')
	{
		$translated_text = 'Tekst kan je aanpassen via functions.php';
	}

	if( $translated_text == 'Gebruikersnaam')
	{
		$translated_text = 'zzzzzzzzzzzzz';
	}

	if( $translated_text == 'Gebruikersnaam of e-mailadres:')
	{
		$translated_text = 'zzzzzzzzzzzzz';
	}

	if( $translated_text == 'Nieuw wachtwoord aanmaken')
	{
		$translated_text = 'zzzzzzzzzzzzz';
	}

	if( $translated_text == 'Lost Password')
	{
		$translated_text = 'Wachtwoord vergeten?';
	}

	return $translated_text;
}

add_filter('gettext','pippin_gettext_demo',20,3);


function translate_title( $title, $id = null ) {
    global $user_ID;

    if ( ($title == 'Lost Password') ) {
        return 'TRANSLATE ME';
    }

    if ( ($title == 'Contact') && ($user_ID == 'FRENCH SPEAKING') ) {
        return 'TRANSLATE ME';
    }

    return $title;
}

add_filter( 'the_title', 'translate_title', 10, 2 );

/* redirect users to front page after login */
//    code not working for WP4? outdated?
//    https://wordpress.org/support/topic/how-can-i-redirect-users-to-the-front-page-after-log-in
