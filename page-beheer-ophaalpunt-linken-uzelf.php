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

        // 1. bestaat er een $_POST, dan heeft de beheerder zich aan een ophaalpunt gelinkt
        //          -> als dat zo is : info wegschrijven naar DB

        $ophaalpunt_id_from_form = intval($_POST['telinkenophaalpunt']);
        if((wp_verify_nonce( $_POST["_wpnonce"], 'beheer_link_uzelf_'.get_current_user_id().$_SERVER['REQUEST_URI'] )) && ($ophaalpunt_id_from_form == $_POST['telinkenophaalpunt']))
        {
            // [A]  _wpnonce klopt én het id-nummer uit het form is een integer
            //     => bestaat er wel een ophaalpunt met dat id_nr?

            if(!($result = $MYRECY_mysqli->query("SELECT * FROM ophaalpunten WHERE id = ".$ophaalpunt_id_from_form)))
            {
                // ERROR: probleem met databank
                show_myrecy_message("error", sprintf(_("Probleem met de databank (error %d) bij opzoeken van ophaalpunt %d : %s."),$MYRECY_mysqli->errno,$ophaalpunt_id_from_form,$MYRECY_mysqli->error));
                $result->close();
                exit;
            }

            if($result->num_rows < 1)
            {
                // ERROR: geen ophaalpunt met dat id_nr gevonden
                show_myrecy_message("error", sprintf(_("Ophaalpunt %d bestaat niet."),$ophaalpunt_id_from_form));
                $result->close();
                exit;
            }

            // [B-1]  ophaalpunt met dat id_nr: gevonden!
            //     => haal gegevens van dat ophaalpunt uit de db
            $ophaalpunt_from_db = $result->fetch_object();
            $result->close();

            // [B-2]  ophaalpunt met dat id_nr: gevonden!
            //     => link de beheerder aan dat ophaalpunt
            $query = "UPDATE wordpress_link SET ophaalpunt_id = ?
                             WHERE  wordpress_userid = ?";

            $statement = $MYRECY_mysqli->prepare($query);

            // [C] bind parameters for markers, where (s = string, i = integer, d = double,  b = blob)
            $statement->bind_param('ii', $ophaalpunt_id_from_form, $current_user->ID);

            if($statement->execute())
            {
                show_myrecy_message("good", sprintf(_("Gebruiker %s is nu gelinkt aan ophaalpunt %d: <em>%s</em>"),$current_user->user_login,$ophaalpunt_id_from_form, $ophaalpunt_from_db->naam));
            }
            else
            {
                show_myrecy_message("error", sprintf(_("Probleem met de databank (error %d) bij het linken van gebruiker %s aan ophaalpunt %d: <em>%s</em>: %s"),$MYRECY_mysqli->errno,$current_user->user_login,$ophaalpunt_id_from_form, $ophaalpunt_from_db->naam,$MYRECY_mysqli->error));
                exit;
            }
            $statement->close();

        }

        if ($result = $MYRECY_mysqli->query("SELECT * FROM ophaalpunten ORDER BY naam"))
        {
            //printf("Select returned %d rows.\n", $result->num_rows);
            if($result->num_rows < 1)
            {
                // no results found, so why even bother? quit! + show error message for users to contact adminstration
                show_myrecy_message("error", _("Geen ophaalpunten gevonden."));
                $result->close();
                exit;
            }
    ?>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

            <p>Gebruiker <strong><?php echo $current_user->user_login;?></strong> linken aan ophaalpunt:</p>

            <select  name="telinkenophaalpunt" id="telinkenophaalpunt">

    <?php
            while ($ophaalpunt_from_db = $result->fetch_object())
            {
                echo "\t<option value=\"$ophaalpunt_from_db->id\">$ophaalpunt_from_db->naam</option>\n";
            }

            echo "\n</select>\n";

            $result->close();

            wp_nonce_field( 'beheer_link_uzelf_'.get_current_user_id().$_SERVER['REQUEST_URI'] );
        ?>
    <p>
    <input type="submit" value="<?php echo _("Ophaalpunt linken"); ?>">
    </form>

	<?php
        }

    ?>

        </div>
	</div>
<?php get_footer() ?>
