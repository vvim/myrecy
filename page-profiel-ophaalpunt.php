<?php
require_once("myrecy-func.php");

get_header(); the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
		<?php
			require_once("secure/db.php");

            // 1. info ophaalpunt uit databank halen (want ophaalpunt_id nodig voor verifiëren van wpnonce)

            // zie http://php.net/manual/en/mysqli.query.php
    		if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.* FROM wordpress_link, ophaalpunten WHERE wordpress_userid = $user_ID and ophaalpunt_id = ophaalpunten.id"))
    		{
                //printf("Select returned %d rows.\n", $result->num_rows);
                if($result->num_rows < 1)
                {
                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                    show_myrecy_message("error", _("Geen ophaalpunt gelinkt aan je gebruikersnaam, contacteer ons voor hulp."));
                    $result->close();
                    exit;
                }

                $ophaalpunt_from_db = $result->fetch_object();

                $result->close();
            }
            else
            {
                // could not query DB, so why even bother? quit! + show error message for users to contact adminstration
                show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
                exit;
            }

            // 2. bestaat er een $_POST, dan heeft de gebruiker waarschijnlijk een nieuwe aanmelding gedaan. Security check:
            //          -> als dat zo is : info wegschrijven naar DB en $ophaalpunt_from_db herladen met nieuwe informatie
            if(wp_verify_nonce( $_POST["_wpnonce"], 'profiel_wijziging_'.get_current_user_id().$ophaalpunt_from_db->id ))
            {
                // [A]  _wpnonce klopt => steek de waarden van het formulier in de databank
                $ophaalpunt_ingevuld_naam = $_POST["naam_ophaalpunt"];
                $ophaalpunt_ingevuld_kurk = $_POST["kurk_ophaalpunt"]; // als kurk_ophaalpunt niet is aangevinkt dan is de waarde "", maar in SQL wordt dat dan automatisch "0"
                $ophaalpunt_ingevuld_parafine = $_POST["parafine_ophaalpunt"];
                $ophaalpunt_ingevuld_code = $_POST["soortophaalpunt"];
                $ophaalpunt_ingevuld_code_intercommunale = $_POST["code_intercommunale"];
                $ophaalpunt_ingevuld_straat = $_POST["straat_ophaalpunt"];
                $ophaalpunt_ingevuld_nr = $_POST["huisnr_ophaalpunt"];
                $ophaalpunt_ingevuld_bus = $_POST["bus_ophaalpunt"];
                $ophaalpunt_ingevuld_postcode = $_POST["postcode_ophaalpunt"];
                $ophaalpunt_ingevuld_plaats = $_POST["plaats_ophaalpunt"];
                $ophaalpunt_ingevuld_land = $_POST["land_ophaalpunt"];
                $ophaalpunt_ingevuld_openingsuren = $_POST["openingsuren_ophaalpunt"];
                $ophaalpunt_ingevuld_contactpersoon = $_POST["contactpersoon"];
                $ophaalpunt_ingevuld_telefoonnummer1 = $_POST["telefoon1"];
                $ophaalpunt_ingevuld_telefoonnummer2 = $_POST["telefoon2"];
                $ophaalpunt_ingevuld_email1 = $_POST["email1"];
                $ophaalpunt_ingevuld_email2 = $_POST["email2"];
                $ophaalpunt_ingevuld_taalvoorkeur = $_POST["taalvoorkeur"];
                $ophaalpunt_ingevuld_preferred_contact = $_POST["preferredcontact"];
                $ophaalpunt_ingevuld_attest_nodig = $_POST["attest_nodig"];
                $ophaalpunt_ingevuld_frequentie_attest = $_POST["attest_frequentie"];

                // [B]  query aanmaken
                $query = "UPDATE ophaalpunten SET naam = ?, kurk = ?, parafine = ?, code = ?, code_intercommunale = ?, 
                                 straat = ?, nr = ?, bus = ?, postcode = ?, plaats = ?, land = ?, openingsuren = ?, 
                                 contactpersoon = ?, telefoonnummer1 = ?, telefoonnummer2 = ?, email1 = ?, email2 = ?, 
                                 taalvoorkeur = ?, preferred_contact = ?, attest_nodig = ?, frequentie_attest = ? 
                                 WHERE  id = ".$ophaalpunt_from_db->id;

                $statement = $MYRECY_mysqli->prepare($query);
                
                // [C] bind parameters for markers, where (s = string, i = integer, d = double,  b = blob)
                $statement->bind_param('siiiissssssssssssiiii', $ophaalpunt_ingevuld_naam, $ophaalpunt_ingevuld_kurk, $ophaalpunt_ingevuld_parafine, $ophaalpunt_ingevuld_code, $ophaalpunt_ingevuld_code_intercommunale, $ophaalpunt_ingevuld_straat, $ophaalpunt_ingevuld_nr, $ophaalpunt_ingevuld_bus, $ophaalpunt_ingevuld_postcode, $ophaalpunt_ingevuld_plaats, $ophaalpunt_ingevuld_land, $ophaalpunt_ingevuld_openingsuren, $ophaalpunt_ingevuld_contactpersoon, $ophaalpunt_ingevuld_telefoonnummer1, $ophaalpunt_ingevuld_telefoonnummer2, $ophaalpunt_ingevuld_email1, $ophaalpunt_ingevuld_email2, $ophaalpunt_ingevuld_taalvoorkeur, $ophaalpunt_ingevuld_preferred_contact, $ophaalpunt_ingevuld_attest_nodig, $ophaalpunt_ingevuld_frequentie_attest);

                if($statement->execute())
                {
                    show_myrecy_message("good",_("Wijzigingen in het profiel zijn doorgegeven aan De Vlaspit."));
                }
                else
                {
                    show_myrecy_message("error", _("De wijzigingen zijn niet doorgegeven aan De Vlaspit. De databank gaf volgende foutmelding: ".$MYRECY_mysqli->errno .": ". $MYRECY_mysqli->error."<br />Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp"));
                    exit;
                }
                $statement->close();

                // [D] now that database has changed: reload the ophaalpunten-object opnieuw opzoeken!
                if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.* FROM wordpress_link, ophaalpunten WHERE wordpress_userid = $user_ID and ophaalpunt_id = ophaalpunten.id"))
                {
                    //printf("Select returned %d rows.\n", $result->num_rows);
                    if($result->num_rows < 1)
                    {
                        // no results found, so why even bother? quit! + show error message for users to contact adminstration
                        show_myrecy_message("error", _("Geen ophaalpunt gelinkt aan je gebruikersnaam, contacteer ons voor hulp."));
                        $result->close();
                        exit;
                    }

                    $ophaalpunt_from_db = $result->fetch_object();

                    $result->close();
                }
                else
                {
                    // could not query DB, so why even bother? quit! + show error message for users to contact adminstration
                    show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
                    exit;
                }


            }

		?>

	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<h3><?php echo _("Adres ophaalpunt"); ?></h3>
		<table class="form-table-myrecy">
		<tr>
			<th><label for="naam_ophaalpunt"><?php echo _("Naam ophaalpunt"); ?></label></th>
			<td><input type="text" name="naam_ophaalpunt" id="naam_ophaalpunt" value="<?php echo htmlspecialchars($ophaalpunt_from_db->naam); ?>" class="extra-long" /></td>
		</tr>
		<tr>
			<th><label for="straat_ophaalpunt"><?php echo _("Straat"); ?></label></th>
			<td><input type="text" name="straat_ophaalpunt" id="straat_ophaalpunt" value="<?php echo htmlspecialchars($ophaalpunt_from_db->straat); ?>" class="extra-long" /></td>
		</tr>
		<tr>
			<th><label for="huisnr_ophaalpunt"><?php echo _("Huisnummer"); ?></label></th>
			<td><input type="text" name="huisnr_ophaalpunt" id="huisnr_ophaalpunt" value="<?php echo htmlspecialchars($ophaalpunt_from_db->nr); ?>" class="extra-long" /></td>
		</tr>
		<tr>
			<th><label for="bus_ophaalpunt"><?php echo _("Bus"); ?></label></th>
			<td><input type="text" name="bus_ophaalpunt" id="bus_ophaalpunt" value="<?php echo htmlspecialchars($ophaalpunt_from_db->bus); ?>" class="extra-long" /></td>
		</tr>
		<tr>
			<th><label for="postcode_ophaalpunt"><?php echo _("Postcode"); ?></label></th>
			<td><input type="text" name="postcode_ophaalpunt" id="postcode_ophaalpunt" value="<?php echo htmlspecialchars($ophaalpunt_from_db->postcode); ?>" class="extra-long" /></td>
		</tr>
		<tr>
			<th><label for="plaats_ophaalpunt"><?php echo _("Plaats"); ?></label></th>
			<td><input type="text" name="plaats_ophaalpunt" id="plaats_ophaalpunt" value="<?php echo htmlspecialchars($ophaalpunt_from_db->plaats); ?>" class="extra-long" /></td>
		</tr>
		<tr>
			<th><label for="land_ophaalpunt"><?php echo _("Land"); ?></label></th>
			<td><input type="text" name="land_ophaalpunt" id="land_ophaalpunt" value="<?php echo htmlspecialchars($ophaalpunt_from_db->land); ?>" class="extra-long" /></td>
		</tr>
		<tr>
			<th><label for="openingsuren_ophaalpunt"><?php echo _("Openingsuren"); ?></label></th>
			<td><textarea name="openingsuren_ophaalpunt" id="openingsuren_ophaalpunt" rows="5" cols="30"><?php echo htmlspecialchars($ophaalpunt_from_db->openingsuren); ?></textarea></td>
		</tr>
		</table>
		<h3><?php echo _("Contactgegevens"); ?></h3>
		<table class="form-table-myrecy">
		<tr>
			<th><label for="contactpersoon"><?php echo _("Contactpersoon"); ?></label></th>
			<td colspan="2"><input type="text" name="contactpersoon" id="contactpersoon" value="<?php echo htmlspecialchars($ophaalpunt_from_db->contactpersoon); ?>" class="extra-long" /></td>
		</tr>
		<tr>
			<th><label for="taalvoorkeur"><?php echo _("Taalvoorkeur"); ?></label></th>
			<td>
				<select name="taalvoorkeur" id="taalvoorkeur"><?php
				    		if ($result = $MYRECY_mysqli->query("SELECT * FROM talen"))
                    		{
                                //printf("Select returned %d rows.\n", $result->num_rows);
                                if($result->num_rows < 1)
                                {
                                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                                    show_myrecy_message("error", _("Geen taalvoorkeuren gevonden in de databank, contacteer ons voor hulp."));
                                    $result->close();
                                    exit;
                                }
                                while($taalvoorkeuren = $result->fetch_object())
                                {
                                    if($taalvoorkeuren->id == $ophaalpunt_from_db->taalvoorkeur)
                                        printf("\n\t\t\t\t\t<option value=\"%d\" selected>%s</option>", htmlspecialchars($taalvoorkeuren->id), _(htmlspecialchars($taalvoorkeuren->taal)));
                                    else
                                        printf("\n\t\t\t\t\t<option value=\"%d\">%s</option>", htmlspecialchars($taalvoorkeuren->id), _(htmlspecialchars($taalvoorkeuren->taal)));
                                }
                                $result->close();
                            }
                            else
                            {
                                // could not query DB, so why even bother? no languages to choose from, but form can maybe continue?
                                show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, dus kan de taalvoorkeuren niet opzoeken. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
                                exit; // still continue? Doesn't this give trouble with trying to commit the form?
                            }
                ?>

				</select>
			</td>
			<td><?php echo _("De voorkeurtaal bepaalt de taal van deze pagina’s."); ?></td>
		</tr>
		<tr>
			<th><label for="telefoon1"><?php echo _("Telefoon");?></label></th>
			<td><input type="text" name="telefoon1" id="telefoon1" value="<?php echo htmlspecialchars($ophaalpunt_from_db->telefoonnummer1); ?>" class="regular-text" /></td>
			<td><input type="text" name="telefoon2" id="telefoon2" value="<?php echo htmlspecialchars($ophaalpunt_from_db->telefoonnummer2); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="email1"><?php echo _("Email");?></label></th>
			<td><input type="text" name="email1" id="email1" value="<?php echo htmlspecialchars($ophaalpunt_from_db->email1); ?>" class="regular-text" /></td>
			<td><input type="text" name="email2" id="email2" value="<?php echo htmlspecialchars($ophaalpunt_from_db->email2); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="preferredcontact"><?php echo _("Bij voorkeur te contacteren per");?></label></th>
			<td>
				<select name="preferredcontact" id="preferredcontact"><?php
				    		if ($result = $MYRECY_mysqli->query("SELECT * FROM contacteren"))
                    		{
                                //printf("Select returned %d rows.\n", $result->num_rows);
                                if($result->num_rows < 1)
                                {
                                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                                    show_myrecy_message("error", _("Geen contactvoorkeuren gevonden in de databank, contacteer ons voor hulp."));
                                    $result->close();
                                    exit;
                                }
                                while($contacteren = $result->fetch_object())
                                {
                                    if($contacteren->id == $ophaalpunt_from_db->preferred_contact)
                                        printf("\n\t\t\t\t\t<option value=\"%d\" selected>%s</option>", htmlspecialchars($contacteren->id), _(htmlspecialchars($contacteren->medium)));
                                    else
                                        printf("\n\t\t\t\t\t<option value=\"%d\">%s</option>", htmlspecialchars($contacteren->id), _(htmlspecialchars($contacteren->medium)));
                                }
                                $result->close();
                            }
                            else
                            {
                                // could not query DB, so why even bother? no languages to choose from, but form can maybe continue?
                                show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, dus kan de contacteervoorkeuren niet opzoeken. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
                                exit; // still continue? Doesn't this give trouble with trying to commit the form?
                            }
                ?>

				</select>
			</td>
		</tr>
		</table>

    	<h3><?php echo _("Soort ophaalpunt");?></h3>
		<table class="form-table-myrecy">
		<tr>
			<th><label for="soortophaalpunt"><?php echo _("Soort ophaalpunt");?></label></th>
			<td>
				<select name="soortophaalpunt" id="soortophaalpunt" onclick="show_types_of_intercommunale()"><?php
				    		if ($result = $MYRECY_mysqli->query("SELECT * FROM soort_ophaalpunt"))
                    		{
                                //printf("Select returned %d rows.\n", $result->num_rows);
                                if($result->num_rows < 1)
                                {
                                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                                    show_myrecy_message("error", _("Geen soorten gevonden in de databank, contacteer ons voor hulp."));
                                    $result->close();
                                    exit;
                                }
                                $intercommunale_code = 1;
                                while($soorten = $result->fetch_object())
                                {
                                    if($soorten->soort == "intercommunale")
                                        $intercommunale_code = $soorten->code;
                                    if($soorten->code == $ophaalpunt_from_db->code)
                                        printf("\n\t\t\t\t\t<option value=\"%d\" selected>%s</option>", htmlspecialchars($soorten->code), _(htmlspecialchars($soorten->soort)));
                                    else
                                        printf("\n\t\t\t\t\t<option value=\"%d\">%s</option>", htmlspecialchars($soorten->code), _(htmlspecialchars($soorten->soort)));
                                }
                                $result->close();
                            }
                            else
                            {
                                // could not query DB, so why even bother? no languages to choose from, but form can maybe continue?
                                show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, dus kan de soorten ophaalpunten niet opzoeken. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
                                exit; // still continue? Doesn't this give trouble with trying to commit the form?
                            }
                ?>

				</select>
			</td>
            <td>
				<select name="code_intercommunale" id="code_intercommunale" onclick="show_types_of_intercommunale()"><?php
				    		if ($result = $MYRECY_mysqli->query("SELECT * FROM intercommunales"))
                    		{
                                //printf("Select returned %d rows.\n", $result->num_rows);
                                if($result->num_rows < 1)
                                {
                                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                                    show_myrecy_message("error", _("Geen intercommunales gevonden in de databank, contacteer ons voor hulp."));
                                    $result->close();
                                    exit;
                                }
                                while($intercommunales = $result->fetch_object())
                                {
                                    if($intercommunales->id == $ophaalpunt_from_db->code_intercommunale)
                                        printf("\n\t\t\t\t\t<option value=\"%d\" selected>%s</option>", htmlspecialchars($intercommunales->id), htmlspecialchars($intercommunales->naam_intercommunale));
                                    else
                                        printf("\n\t\t\t\t\t<option value=\"%d\">%s</option>", htmlspecialchars($intercommunales->id), htmlspecialchars($intercommunales->naam_intercommunale));
                                }
                                $result->close();
                            }
                            else
                            {
                                // could not query DB, so why even bother? no languages to choose from, but form can maybe continue?
                                show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, dus kan de soorten ophaalpunten niet opzoeken. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
                                exit; // still continue? Doesn't this give trouble with trying to commit the form?
                            }
                ?>

				</select>
            </td>
		</tr>
		<tr>
			<th><label for="kurk_ophaalpunt"><?php echo _("Ingezamelde afvalstof");?></label></th>
            <td>
                <input type="checkbox" name="kurk_ophaalpunt" value="1" <?php if($ophaalpunt_from_db->kurk > 0) echo "checked"; ?> /> <?php echo _("kurk"); ?>
            </td>
            <td>
                <input type="checkbox" name="parafine_ophaalpunt" value="1" <?php if($ophaalpunt_from_db->parafine > 0) echo "checked"; ?> /> <?php echo _("kaarsresten"); ?>
            </td>
		</tr>
		<tr>
			<th><label for="attest_nodig"><?php echo _("Attest nodig?"); ?></label></th>
            <td>
                <input type="checkbox" name="attest_nodig" value="1" <?php if($ophaalpunt_from_db->attest_nodig > 0) echo "checked"; ?>  onclick="show_attest_frequency()" /> <?php echo _("ja"); ?>
            </td>
            <td>
				<select name="attest_frequentie" id="attest_frequentie" onclick="show_attest_frequency()"><?php
				    		if ($result = $MYRECY_mysqli->query("SELECT * FROM frequentie"))
                    		{
                                //printf("Select returned %d rows.\n", $result->num_rows);
                                if($result->num_rows < 1)
                                {
                                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                                    show_myrecy_message("error", _("Geen frequenties gevonden in de databank, contacteer ons voor hulp."));
                                    $result->close();
                                    exit;
                                }
                                while($frequencies = $result->fetch_object())
                                {
                                    if($frequencies->id == $ophaalpunt_from_db->frequentie_attest)
                                        printf("\n\t\t\t\t\t<option value=\"%d\" selected>%s</option>", htmlspecialchars($frequencies->id), _(htmlspecialchars($frequencies->frequentie)));
                                    else
                                        printf("\n\t\t\t\t\t<option value=\"%d\">%s</option>", htmlspecialchars($frequencies->id), _(htmlspecialchars($frequencies->frequentie)));
                                }
                                $result->close();
                            }
                            else
                            {
                                // could not query DB, so why even bother? no languages to choose from, but form can maybe continue?
                                show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, dus kan de soorten ophaalpunten niet opzoeken. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
                                exit; // still continue? Doesn't this give trouble with trying to commit the form?
                            }
                ?>

                </select>
            </td>
		</tr>
	</table>
    <?php wp_nonce_field( 'profiel_wijziging_'.get_current_user_id().$ophaalpunt_from_db->id ); ?>

    <input type="submit" value="<?php echo _("Wijzigingen opslaan"); ?>">
    </form>

    <script>
            <!--
            
            // script to show/hide the types of intercommunales or attest frequency

            function show_attest_frequency()
            {
                if(document.all.attest_nodig.checked)
                    document.all.attest_frequentie.style.visibility="visible"
                else
                    document.all.attest_frequentie.style.visibility="hidden"
            }
            
            function show_types_of_intercommunale()
            {
                intercommunale = <?php echo $intercommunale_code; ?>;
                
                if (document.all.soortophaalpunt.value == intercommunale)
                    document.all.code_intercommunale.style.visibility="visible"
                else
                    document.all.code_intercommunale.style.visibility="hidden"
            }
           
           /* window.load handling, see http://www.htmlgoodies.com/beyond/javascript/article.php/3724571/Using-Multiple-JavaScript-Onload-Functions.htm
           and http://blog.simonwillison.net/post/57956760515/addloadevent (Simon Willison)
           */
            function addLoadEvent(func)
            {
                var oldonload = window.onload;
                if (typeof window.onload != 'function')
                {
                    window.onload = func;
                }
                else
                {
                    window.onload = function()
                    {
                        if (oldonload)
                        {
                            oldonload();
                        }
                        func();
                    }
                }
            }
            
            addLoadEvent(show_types_of_intercommunale);
            addLoadEvent(show_attest_frequency);

            //-->
    </script>
	</div>

</div>
	
<?php get_footer() ?>
