<?php
require_once("myrecy-func.php");
require_once('login-and-language-check.php');
$current_user = wp_get_current_user();

        $show_myrecy_message_type = "";
        $myrecy_message_to_show = "";

        // 1. bestaat er een $_POST, dan heeft de gebruiker zijn wachtwoord willen wijzigen een nieuwe gebruiker aangemaakt
        //          -> als dat zo is : info wegschrijven naar DB
        $pw_from_form = $_POST['new_password'];
        $pw_from_form_confirm = $_POST['new_password_confirm'];

        if((wp_verify_nonce( $_POST["_wpnonce"], 'wachtwoordwijzigen_'.get_current_user_id().$_SERVER['REQUEST_URI'] )) && (strcmp($pw_from_form, $pw_from_form_confirm) == 0))
        {
            // nonce verified and both passwords are identical
            //check old password: http://codex.wordpress.org/Function_Reference/wp_check_password
            
            global $current_user;
            get_currentuserinfo();
            
            /*
                $user = get_user_by( 'login', $username );
                if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID) )
                   echo "That's it";
                else
                   echo "Nope"; // error
            */
            // http://stackoverflow.com/a/28987625/707700
            global $wpdb;

            $profile_id = get_current_user_id();//$_POST['prof_id'];
            $username = $current_user->user_login;
            $md5password = wp_hash_password($pw_from_form);
            
            $wpdb->query( $wpdb->prepare( 
                "
                    UPDATE $wpdb->users SET user_pass = %s WHERE ID = %d
                ", 
                $md5password, 
                $profile_id 
            ) );

            // Here is the magic:
            wp_cache_delete($profile_id, 'users');
            wp_cache_delete($username, 'userlogins'); // This might be an issue for how you are doing it. Presumably you'd need to run this for the ORIGINAL user login name, not the new one.
            wp_logout();
            $error_or_user = wp_signon(array('user_login' => $username, 'user_password' => $pw_from_form));
            if (is_wp_error($error_or_user))  // return value of wp_signon is WP_Error on failure, or WP_User on success. http://codex.wordpress.org/Function_Reference/wp_signon#Return_Value
            {
                $show_myrecy_message_type = "error";
                $myrecy_message_to_show = sprintf(_("Kon wachtwoord voor gebruiker %s niet wijzigen, gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp en meld ons de volgende foutboodschap: <em>%s</em>"),$username,$error_or_user->get_error_message());
            }
            else
            {
                $show_myrecy_message_type = "good";
                $myrecy_message_to_show = sprintf(_("Wachtwoord voor gebruiker %s gewijzigd."),$username);
            }
        }

get_header();

the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
<?php
    if (($show_myrecy_message_type != '') && ($myrecy_message_to_show != ''))
    {
        show_myrecy_message($show_myrecy_message_type, $myrecy_message_to_show);
        ?>
	<div class="content">
    </div>
	</div>
<?php get_footer() ?>

        <?php
        exit;
    }
?>
	<div class="content">
        <script>
                <!--
                // http://stackoverflow.com/questions/9094706/form-confirmation-on-submit
                function check_passwords_equal() {
                        //Store the password field objects into variables ...
                    var pass1 = document.getElementById('new_password');
                    var pass2 = document.getElementById('new_password_confirm');
                    if (pass1.value == pass2.value)
                        return true; // continue with changing the password
                    else
                    {
                        alert('<?php echo _("De wachtwoorden komen niet overeen, probeer opnieuw."); ?>');
                        return false;
                    }
                }
                //-->
        </script>
    	<form name="changepassword" action="<?php echo $_SERVER['REQUEST_URI']; ?>"  onsubmit="return check_passwords_equal();" method="post">
            <table class="form-table-myrecy">
                    <tr>
                        <th><label for="username"><?php echo _("Nieuw wachtwoord"); ?></label></th>
                        <td><input type="password" name="new_password" id="new_password" class="extra-long" /></td>
                    </tr>
                    <tr>
                        <th><label for="username"><?php echo _("Bevestig het nieuwe wachtwoord"); ?></label></th>
                        <td><input type="password" name="new_password_confirm" id="new_password_confirm" class="extra-long" /></td>
                    </tr>
            </table>
	<?php
            wp_nonce_field( 'wachtwoordwijzigen_'.get_current_user_id().$_SERVER['REQUEST_URI'] );
    ?>
                <input type="submit" value="<?php echo _("Wachtwoord wijzigen"); ?>">
    </form>

        </div>
	</div>
<?php get_footer() ?>
