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
    <?php include("beheer-menu.php"); ?>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
	<?php
		require_once("secure/db.php");

        // 1. bestaat er een $_POST, dan heeft de beheerder een gebruiker willen verwijderen
        //          -> als dat zo is : wp_delete_user()
        $user_id_from_form = intval($_POST['teverwijderengebruiker']);
        if((wp_verify_nonce( $_POST["_wpnonce"], 'myrecygebruikerverwijderen_'.get_current_user_id().$_SERVER['REQUEST_URI'] )) && ($user_id_from_form == $_POST['teverwijderengebruiker']))
        {
            // [A]  _wpnonce klopt én het id-nummer uit het form is een integer
            $deleted_user_login = get_userdata($user_id_from_form)->user_login;
            require_once(ABSPATH.'wp-admin/includes/user.php' );
            if(   (wp_delete_user($user_id_from_form))   &&   ($MYRECY_mysqli->query("DELETE FROM wordpress_link WHERE wordpress_userid = $user_id_from_form"))   )
                show_myrecy_message("good", sprintf(_("Gebruiker %s verwijderd"),$deleted_user_login));
            else
                show_myrecy_message("error", sprintf(_("Er ging iets mis bij het verwijderen van gebruiker %s - met id %d  , contacteer Wim met deze foutmelding."),$deleted_user_login,$user_id_from_form));
        }


         if ($result = $MYRECY_mysqli->query("SELECT wordpress_link.*, ophaalpunten.naam FROM wordpress_link, ophaalpunten WHERE ophaalpunten.id = wordpress_link.ophaalpunt_id"))
        {
/* debug*/ printf("<!-- <vvim> Select returned %d rows.-->\n", $result->num_rows);
            if($result->num_rows < 1)
            {
                // no results found, so why even bother? quit! + show error message for users to contact adminstration
                show_myrecy_message("error", _("Geen gebruikers gevonden."));
                $result->close();
                exit;
            }
    ?>

    <script>
            <!--
            // http://stackoverflow.com/questions/9094706/form-confirmation-on-submit
            function confirm_remove_user() {
                return confirm("Bent u zeker dat u deze gebruiker de toegang tot MyRecy wilt ontzeggen?");
            }
            //-->
    </script>

    <p>Selecteer de gebruiker die u wenst te wissen:</p>

    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>"  method="POST">
    <?php /* if user_id , then niet schrijven*/ MYRECY_Administrator(); ?>

            <select  name="teverwijderengebruiker" id="teverwijderengebruiker">

    <?php
            $replace_me = array("\'", "\n");
            $replace_by = array("'", " ");

            // DIRTY HACK: we do not want to show our ADMIN-accounts to be deleted
            // maybe we should also add our DEMO-accounts?
            global $ADMIN_Wim;
            global $ADMIN_Geert;

            while ($gebruiker_from_db = $result->fetch_object()) // ophaalpunt_id, wordpress_userid
            {
                $user_id = $gebruiker_from_db->wordpress_userid;

                if (!  (($user_id == $ADMIN_Wim) || ($user_id == $ADMIN_Geert))  )
                {
                    // DIRTY HACK: we do not want to show our ADMIN-accounts to be deleted, so we do not show them in the list

                    $user_info = get_userdata($user_id);
                    if(!$user_info)
                    {
                        show_myrecy_message("error", sprintf(_("Geen gebruikersdata gevonden voor gebruiker %d , contacteer Wim met deze foutmelding."),$user_id));
                    }
                    else
                    {
                        echo "\t<option value=\"$user_id\">$user_info->user_login ( ".htmlspecialchars(str_replace($replace_me, $replace_by, $gebruiker_from_db->naam))." )</option>\n";
                    }
                }
            }

            echo "\n</select>\n";

            $result->close();

            wp_nonce_field( 'myrecygebruikerverwijderen_'.get_current_user_id().$_SERVER['REQUEST_URI'] );
        ?>
        <p>
        <input type="submit" value="<?php echo _("Verwijder gebruiker"); ?>">
    </form>

    <?php
        }

    ?>

        </div>
	</div>
<?php get_footer() ?>
