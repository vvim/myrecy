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
        if((wp_verify_nonce( $_POST["_wpnonce"], 'myrecygebruikeraanmaken_'.get_current_user_id().$_SERVER['REQUEST_URI'] )) && ($ophaalpunt_id_from_form == $_POST['telinkenophaalpunt']))
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

    /* <difference with page-beheer-ophaalpunt-linken-uzelf.php> */
            // [C]  nieuwe gebruiker aanmaken in WordPress
            $user_name = $_POST['username'];
            $user_email = $_POST['email'];
            $user_password = $_POST['password'];

            $user_id = username_exists( $user_name ); //      This function returns the user ID if the user exists or null if the user does not exist.  http://codex.wordpress.org/Function_Reference/username_exists
            if ( !$user_id and email_exists($user_email) == false ) {
                $user_id = wp_create_user( $user_name, $user_password, $user_email ); // wp_create_user == wp_insert_user : http://wordpress.stackexchange.com/a/66566/67607
            } else {
                show_myrecy_message("error", sprintf(_("Gebruiker %s ( %s ) bestaat al."),$user_name,$user_email));
                // see http://www.computerhope.com/issues/ch000317.htm
                echo "\n<form><input type=\"button\" value=\"Terug\" onClick=\"history.go(-1);return true;\"></form>";
                get_footer();
                exit;
            }

            if(!update_user_meta( $user_id, 'show_admin_bar_front', 'false' ))
            {
                show_myrecy_message("error", sprintf(_("Kan setting <em>%s</em> voor gebruiker %s niet op <em>false</em> zetten. Contacteer Wim aub."),$user_name,"'show_admin_bar_front'"));
            }

            $query = "INSERT INTO wordpress_link (ophaalpunt_id, wordpress_userid)
                             VALUES (?,?)";

            $statement = $MYRECY_mysqli->prepare($query);

            // [C] bind parameters for markers, where (s = string, i = integer, d = double,  b = blob)
            $statement->bind_param('ii', $ophaalpunt_id_from_form, $user_id);

            if($statement->execute())
            {
                show_myrecy_message("good", sprintf(_("Gebruiker %s is nu gelinkt aan ophaalpunt %d: <em>%s</em>"),$user_name,$user_id, $ophaalpunt_from_db->naam));
            }
            else
            {
                show_myrecy_message("error", sprintf(_("Probleem met de databank (error %d) bij het linken van gebruiker %s aan ophaalpunt %d: <em>%s</em>: %s"),$MYRECY_mysqli->errno,$user_name,$ophaalpunt_id_from_form, $ophaalpunt_from_db->naam,$MYRECY_mysqli->error));
                exit;
            }
            $statement->close();
    /* </ difference with page-beheer-ophaalpunt-linken-uzelf.php> */

        }

        /* DIFFERENCE WITH
                use 'SELECT ophaalpunten.* FROM ophaalpunten ORDER BY naam'
                    if you want to show ALL ophaalpunten
                use 'SELECT ophaalpunten.* FROM ophaalpunten WHERE ophaalpunten.id not in (select `ophaalpunt_id` from wordpress_link)  ORDER BY naam'
                    if you only want to show the ophaalpunten that have not been linked to a user yet
                        (WATCH OUT, what if an ophaalpunt has been linked to an ADMINISTRATOR, then it will also not show!
                            --> for this we can use:
                            SELECT ophaalpunten.* FROM ophaalpunten WHERE ophaalpunten.id not in (select `ophaalpunt_id` from wordpress_link where wordpress_userid <> 1 AND wordpress_userid <> 4)  ORDER BY naam
        */

        /* dirty hack: only two admin's: Wim & Geert */
        global $ADMIN_Wim;
        global $ADMIN_Geert;

        if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.* FROM ophaalpunten WHERE ophaalpunten.id not in (select `ophaalpunt_id` from wordpress_link where wordpress_userid <> $ADMIN_Wim AND wordpress_userid <> $ADMIN_Geert)  ORDER BY naam"))
        {
/* debug*/ printf("<!-- <vvim> Select returned %d rows.-->\n", $result->num_rows);
            if($result->num_rows < 1)
            {
                // no results found, so why even bother? quit! + show error message for users to contact adminstration
                show_myrecy_message("error", _("Geen ophaalpunten gevonden."));
                $result->close();
                exit;
            }
    ?>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

            <p>Gegevens voor de nieuwe MyRecy-gebruiker:</p>
            <table class="form-table-myrecy">
                    <tr>
                        <th><label for="username">Gebruikersnaam</label></th>
                        <td><input type="text" name="username" id="username" class="extra-long" /></td>
                    </tr>
                    <tr>
                        <th><label for="email">Email</label></th>
                        <td><input type="text" name="email" id="email" class="extra-long" /></td>
                    </tr>
                    <tr>
                        <th><label for="email">Wachtwoord</label></th>
                        <td><input type="text" name="password" id="password" class="extra-long" /></td>
                    </tr>
                    <tr>
                        <th><label for="telinkenophaalpunt">Ophaalpunt</label></th>
                        <td>

            <select  name="telinkenophaalpunt" id="telinkenophaalpunt">

    <?php
            while ($ophaalpunt_from_db = $result->fetch_object())
            {
                echo "\t<option value=\"$ophaalpunt_from_db->id\">$ophaalpunt_from_db->naam</option>\n";
            }

            echo "\n</select>\n";

            $result->close();

            wp_nonce_field( 'myrecygebruikeraanmaken_'.get_current_user_id().$_SERVER['REQUEST_URI'] );
        ?>

                        </td>
                    </tr>
                </table>

                <input type="submit" value="<?php echo _("Nieuwe gebruiker aanmaken"); ?>">
    </form>

	<?php
        }

    ?>

        </div>
	</div>
<?php get_footer() ?>
