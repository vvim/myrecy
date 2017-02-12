<?php 
require_once("myrecy-func.php");

get_header(); the_post();

global $user_ID; echo "user id: $user_ID - $user_id en ".get_current_user_id();


   $current_user = wp_get_current_user();
   
   if ($current_user->wp_capabilities['administrator']==1)
   {
       echo "<p>current user: ADMIN YO!</p>";
   }


    echo "<p>current user capabilities:</p>";
    print_r(array_keys($current_user->wp_capabilities,"1"));
?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
    <strong>vvim: voor de content</strong>
	<div class="content">
        <?php
            //http://codex.wordpress.org/Function_Reference/wp_set_password
            $password = 'ophaalpunt1';
            //wp_set_password( $password, $user_ID );
        ?>
    Your password has now been <strong>CHANGED</strong>! :evil:laugh...
    </div>
</div>
<?php get_footer() ?>