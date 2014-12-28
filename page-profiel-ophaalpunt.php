<?php
require_once("myrecy-func.php");

get_header(); the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
		<?php
			require_once("secure/db.php");

		    // zie http://php.net/manual/en/mysqli.query.php
    		if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.* FROM wordpress_link, ophaalpunten WHERE wordpress_userid = $user_ID and ophaalpunt_id = ophaalpunten.id"))
    		{
                //printf("Select returned %d rows.\n", $result->num_rows);
                if($result->num_rows < 1)
                {
                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                    show_myrecy_message("error", "Geen ophaalpunt gelinkt aan je gebruikersnaam, contacteer ons voor hulp.");
                    $result->close();
                    exit;
                }

                $ophaalpunt_from_db = $result->fetch_object();

                $result->close();
            }
            else
            {
                // could not query DB, so why even bother? quit! + show error message for users to contact adminstration
                show_myrecy_message("error", "De MyRecy-databank is momenteel niet bereikbaar, gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp.");
                exit;
            }
		?>

		<h3>Adres ophaalpunt</h3>
		<table class="form-table-myrecy">
		<tr>
			<th><label for="naam_ophaalpunt">Naam ophaalpunt</label></th>
			<td><input type="text" name="naam_ophaalpunt" id="naam_ophaalpunt" value="<?php echo $ophaalpunt_from_db->naam; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="straat_ophaalpunt">Straat</label></th>
			<td><input type="text" name="straat_ophaalpunt" id="straat_ophaalpunt" value="<?php echo $ophaalpunt_from_db->straat; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="huisnr_ophaalpunt">Huisnummer</label></th>
			<td><input type="text" name="huisnr_ophaalpunt" id="huisnr_ophaalpunt" value="<?php echo $ophaalpunt_from_db->nr; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="bus_ophaalpunt">Bus</label></th>
			<td><input type="text" name="bus_ophaalpunt" id="bus_ophaalpunt" value="<?php echo $ophaalpunt_from_db->bus; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="postcode_ophaalpunt">Postcode</label></th>
			<td><input type="text" name="postcode_ophaalpunt" id="postcode_ophaalpunt" value="<?php echo $ophaalpunt_from_db->postcode; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="plaats_ophaalpunt">Plaats</label></th>
			<td><input type="text" name="plaats_ophaalpunt" id="plaats_ophaalpunt" value="<?php echo $ophaalpunt_from_db->plaats; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="land_ophaalpunt">Land</label></th>
			<td><input type="text" name="land_ophaalpunt" id="land_ophaalpunt" value="<?php echo $ophaalpunt_from_db->land; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="openingsuren_ophaalpunt">Openingsuren</label></th>
			<td><textarea name="openingsuren_ophaalpunt" id="openingsuren_ophaalpunt" rows="5" cols="30"><?php echo $ophaalpunt_from_db->openingsuren; ?></textarea></td>
		</tr>
		</table>
		<h3>Contactgegevens</h3>
		<table class="form-table-myrecy">
		<tr>
			<th><label for="contactpersoon">Contactpersoon</label></th>
			<td><input type="text" name="contactpersoon" id="contactpersoon" value="<?php echo $ophaalpunt_from_db->contactpersoon; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="taalvoorkeur">Taalvoorkeur</label></th>
			<td>
				<select name="taalvoorkeur" id="taalvoorkeur"><?php
				    		if ($result = $MYRECY_mysqli->query("SELECT * FROM talen"))
                    		{
                                //printf("Select returned %d rows.\n", $result->num_rows);
                                if($result->num_rows < 1)
                                {
                                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                                    show_myrecy_message("error", "Geen taalvoorkeuren gevonden in de databank, contacteer ons voor hulp.");
                                    $result->close();
                                    exit;
                                }
                                while($taalvoorkeuren = $result->fetch_object())
                                {
                                    if($taalvoorkeuren->id == $ophaalpunt_from_db->taalvoorkeur)
                                        printf("\n\t\t\t\t\t<option value=\"%d\" selected>%s</option>", $taalvoorkeuren->id, $taalvoorkeuren->taal);
                                    else
                                        printf("\n\t\t\t\t\t<option value=\"%d\">%s</option>", $taalvoorkeuren->id, $taalvoorkeuren->taal);
                                }
                                $result->close();
                            }
                            else
                            {
                                // could not query DB, so why even bother? no languages to choose from, but form can maybe continue?
                                show_myrecy_message("error", "De MyRecy-databank is momenteel niet bereikbaar, dus kan de taalvoorkeuren niet opzoeken. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp.)";
                                exit; // still continue? Doesn't this give trouble with trying to commit the form?
                            }
                ?>

				</select>
			</td>
		</tr>
		<tr>
			<th><label for="telefoon1">Telefoon</label></th>
			<td><input type="text" name="telefoon1" id="telefoon1" value="<?php echo $ophaalpunt_from_db->telefoonnummer1; ?>" class="regular-text" /></td>
<!-- Seems more reasonable, no? Or does this give trouble when uploading the data?
		</tr>
		<tr>
			<th><label for="telefoon2">of</label></th>  -->
			<td><input type="text" name="telefoon2" id="telefoon2" value="<?php echo $ophaalpunt_from_db->telefoonnummer2; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="email1">Email</label></th>
			<td><input type="text" name="email1" id="email1" value="<?php echo $ophaalpunt_from_db->email1; ?>" class="regular-text" /></td>
<!--
		</tr>
		<tr>
			<th><label for="email2">of</label></th> -->
			<td><input type="text" name="email2" id="email2" value="<?php echo $ophaalpunt_from_db->email2; ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="preferredcontact">Bijvoorkeur te contacteren per</label></th>
			<td>
				<select name="preferredcontact" id="preferredcontact"><?php
				    		if ($result = $MYRECY_mysqli->query("SELECT * FROM contacteren"))
                    		{
                                //printf("Select returned %d rows.\n", $result->num_rows);
                                if($result->num_rows < 1)
                                {
                                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                                    show_myrecy_message("error", "Geen contactvoorkeuren gevonden in de databank, contacteer ons voor hulp.");
                                    $result->close();
                                    exit;
                                }
                                while($contacteren = $result->fetch_object())
                                {
                                    if($contacteren->id == $ophaalpunt_from_db->preferred_contact)
                                        printf("\n\t\t\t\t\t<option value=\"%d\" selected>%s</option>", $contacteren->id, $contacteren->medium);
                                    else
                                        printf("\n\t\t\t\t\t<option value=\"%d\">%s</option>", $contacteren->id, $contacteren->medium);
                                }
                                $result->close();
                            }
                            else
                            {
                                // could not query DB, so why even bother? no languages to choose from, but form can maybe continue?
                                show_myrecy_message("error", "De MyRecy-databank is momenteel niet bereikbaar, dus kan de contacteervoorkeuren niet opzoeken. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp.");
                                exit; // still continue? Doesn't this give trouble with trying to commit the form?
                            }
                ?>

				</select>
			</td>
		</tr>
		</table>

    	<h3>Soort ophaalpunt</h3>
		<table class="form-table-myrecy">
		<tr>
			<th><label for="soortophaalpunt">Soort ophaalpunt</label></th>
			<td>
				<select name="soortophaalpunt" id="soortophaalpunt"><?php
				    		if ($result = $MYRECY_mysqli->query("SELECT * FROM soort_ophaalpunt"))
                    		{
                                //printf("Select returned %d rows.\n", $result->num_rows);
                                if($result->num_rows < 1)
                                {
                                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                                    show_myrecy_message("error", "Geen soorten gevonden in de databank, contacteer ons voor hulp.)";
                                    $result->close();
                                    exit;
                                }
                                while($soorten = $result->fetch_object())
                                {
                                    if($soorten->code == $ophaalpunt_from_db->code)
                                        printf("\n\t\t\t\t\t<option value=\"%d\" selected>%s</option>", $soorten->code, $soorten->soort);
                                    else
                                        printf("\n\t\t\t\t\t<option value=\"%d\">%s</option>", $soorten->code, $soorten->soort);
                                }
                                $result->close();
                            }
                            else
                            {
                                // could not query DB, so why even bother? no languages to choose from, but form can maybe continue?
                                show_myrecy_message("error", "De MyRecy-databank is momenteel niet bereikbaar, dus kan de soorten ophaalpunten niet opzoeken. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp.");
                                exit; // still continue? Doesn't this give trouble with trying to commit the form?
                            }
                ?>

				</select>
			</td>
		</tr>
	</table>

<p><strong>TODO Wim:</strong>voor 'intercommunales' zie BH-XMLHttpRequest om mogelijkheden weer te geven</p>

	</div>

	<?php if(is_singular()) comments_template(); ?>
</div>
	
<?php get_footer() ?>
