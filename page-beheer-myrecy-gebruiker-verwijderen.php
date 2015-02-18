<?php
require_once("myrecy-func.php");



  require_once('login-and-language-check.php');

   if (!MYRECY_Administrator() )
   {
       // non-admins have no business here, back to base!
        wp_redirect( home_url() );
        exit;
   }
   
   $current_user = wp_get_current_user();

get_header();

the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
	<?php
		require_once("secure/db.php");
    ?>

 (... nog geen inhoud)

        </div>
	</div>
<?php get_footer() ?>
