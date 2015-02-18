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

    <div id="info">
        <p>Deze module is enkel toegankelijk voor <strong><?php echo  $current_user->user_login; ?></strong>. Hier kan u:</p>
        <ul>
            <li>Een nieuwe gebruiker van MyRecy <a href="../beheer-myrecy-gebruiker-aanmaken/">aanmaken</a>.</li>
            <li>Een gebruiker van MyRecy <a href="../beheer-myrecy-gebruiker-verwijderen">verwijderen</a>.</li>
            <li>Uzelf aan een ophaalpunt naar keuze <a href="../beheer-ophaalpunt-linken-uzelf">linken</a>.</li>
        </ul>
    </div>

        </div>
	</div>
<?php get_footer() ?>
