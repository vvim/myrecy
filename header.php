<?php

   if (!is_user_logged_in() && ! (is_page("login") || is_page("lostpassword") ) )
   {
	wp_redirect( home_url()."/login" );
	exit;
   }

  require_once('origami-header.php');

/* we could add language specifics here as well (user is Dutchspeaking, Frenchspeaking, ... )

	switch (user_speaks())
	{
		case 'NL':
			change language to Dutch;
			break;
		case 'FR':
			...
	}
		
*/
?>
