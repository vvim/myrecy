<?php 
require_once("myrecy-func.php");

get_header(); the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
	<?php
	    // read http://codex.wordpress.org/WordPress_Nonces and stackoverflow.com/questions/1924939/php-request-vs-get-and-post
        $aanmelding_id = -1;
  	    $zakken_kurk = 0;
	    $kg_kurk = 0;
	    $zakken_kaarsresten = 0;
	    $kg_kaarsresten = 0;
        $opmerkingen_form = "";
        $handtekening = " -- ingegeven via MyRecy";

  	    require_once("secure/db.php");

        // 1. bestaat er een $_POST, dan heeft de gebruiker waarschijnlijk een nieuwe aanmelding gedaan. Security check:
    	if(wp_verify_nonce( $_POST["_wpnonce"], 'nieuwe_aanmelding_'.get_current_user_id().$_POST["aanmelding_id"] ))
    	{
            $user_ID = get_current_user_id();
            
    	    $zakken_kurk = $MYRECY_mysqli->real_escape_string($_POST["zakken_kurk"]);
    	    $kg_kurk = $MYRECY_mysqli->real_escape_string($_POST["kg_kurk"]);
    	    $zakken_kaarsresten = $MYRECY_mysqli->real_escape_string($_POST["zakken_kaarsresten"]);
    	    $kg_kaarsresten = $MYRECY_mysqli->real_escape_string($_POST["kg_kaarsresten"]);
    	    $opmerkingen_form = $MYRECY_mysqli->real_escape_string($_POST["opmerkingen"]);
    	    $opmerkingen_met_handtekening = $MYRECY_mysqli->real_escape_string($_POST["opmerkingen"].$handtekening);
    	    
    	    if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.id, ophaalpunten.contactpersoon FROM wordpress_link, ophaalpunten WHERE wordpress_userid = $user_ID and ophaalpunt_id = ophaalpunten.id"))
    	    {
                //printf("Select returned %d rows.\n", $result->num_rows);
                if($result->num_rows < 1)
                {
                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
                    show_myrecy_message("error", _("Geen ophaalpunt gevonden dat overeenkomt met uw gebruikersnaam, stock is niet doorgegeven aan De Vlaspit. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
                    $result->close();
                    exit;
                }

                $resultobject = $result->fetch_object();
        	    $ophaalpunt = $resultobject->id;
        	    $contactpersoon = $resultobject->contactpersoon;

                $result->close();

        	// nonce seems right => do a SQL insert if one of the values is > 0
        	if(($_POST["zakken_kurk"] > 0) || ($_POST["kg_kurk"] > 0) || ($_POST["zakken_kaarsresten"] > 0) || ($_POST["kg_kaarsresten"] > 0) )
        	{
        	    // on query preparation: http://www.sanwebe.com/2013/03/basic-php-mysqli-usage and http://www.mustbebuilt.co.uk/php/insert-update-and-delete-with-mysqli/

                if($_POST["aanmelding_id"]  < 0)
                {
                    // "ophaalpunt zonder aanmeldingen, want ".$_POST["aanmelding_id"] < 0
                    $query = "INSERT INTO aanmelding (id, timestamp, ophaalpunt, contactpersoon, datum, zakken_kurk, kg_kurk, zakken_kaarsresten, kg_kaarsresten, opmerkingen, ophaalronde_datum, volgorde)
                                          VALUES(NULL, CURRENT_TIMESTAMP, ?, ?, CURRENT_DATE, ?, ?, ?, ?, ?, NULL, NULL)";
                }
                else
                {
                    // bestaande aanmelding wijzigen want $_POST["aanmelding_id"] >= 0
                    $query = "UPDATE aanmelding SET ophaalpunt = ?, contactpersoon = ?, zakken_kurk = ?, kg_kurk = ?,
                                    zakken_kaarsresten = ?, kg_kaarsresten = ?, opmerkingen = ?, datum = CURRENT_DATE
                                    WHERE id = ".$_POST['aanmelding_id'];
                }



                    $statement = $MYRECY_mysqli->prepare($query);

                    //bind parameters for markers, where (s = string, i = integer, d = double,  b = blob)
                    $statement->bind_param('isiiiis', $ophaalpunt, $contactpersoon, $zakken_kurk, $kg_kurk, $zakken_kaarsresten, $kg_kaarsresten, $opmerkingen_met_handtekening);

                    if($statement->execute())
                    {
                      	show_myrecy_message("good",sprintf(_("Hoeveelheden zijn doorgegeven aan De Vlaspit: %d zakken kurk (%d kg) en %d zakken kaarsresten (%d kg)."),$zakken_kurk,$kg_kurk,$zakken_kaarsresten,$kg_kaarsresten));
                        // ID of last inserted record is : ' .$statement->insert_id ';
                    }
                    else
                    {
                       	show_myrecy_message("error", _("De hoeveelheden zijn niet doorgegeven aan De Vlaspit. De databank gaf volgende foutmelding: ".$MYRECY_mysqli->errno .": ". $MYRECY_mysqli->error."<br />Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp"));
                        exit;

                    }
                    $statement->close();
                    $aanmelding_id = mysqli_insert_id($MYRECY_mysqli);

                    // to really insert: http://stackoverflow.com/questions/16835753/inserting-data-to-table-mysqli-insert
        	}
        	else
        	{
            	    show_myrecy_message("error",_("Geen hoeveelheden opgegeven, stock is niet doorgegeven aan De Vlaspit."));
            	}
    	    }
    	    else
    	    {
        	    show_myrecy_message("error",_("Geen verbinding kunnen maken met de databank, de hoeveelheden zijn niet doorgegeven aan De Vlaspit.  Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
        	}
    	}

        // 2. bestaat er al een eerdere "aanmelding"? vul deze waarden dan in in het formulier
        if ($result = $MYRECY_mysqli->query("SELECT aanmelding.* FROM wordpress_link, aanmelding WHERE wordpress_userid = $user_ID and ophaalpunt_id = ophaalpunt and volgorde is null ORDER BY timestamp DESC"))
        {
            //printf("Select returned %d rows.\n", $result->num_rows);
            if($result->num_rows < 1)
            {
                show_myrecy_message("info", _("Nog geen aanmelding gevonden, je kan hieronder een nieuwe aanmelding ingeven."));
                $result->close();
            }
            else
            {
                $vorige_aanmelding = $result->fetch_object();
                
                $aanmelding_id = $vorige_aanmelding->id;
                $kg_kurk = $vorige_aanmelding->kg_kurk;
                $zakken_kurk = $vorige_aanmelding->zakken_kurk;
                $kg_kaarsresten = $vorige_aanmelding->kg_kaarsresten;
                $zakken_kaarsresten= $vorige_aanmelding->zakken_kaarsresten;
                $opmerkingen_form = str_replace($handtekening,"",$vorige_aanmelding->opmerkingen); // haal de $handtekening uit de opmerkingen
                $datum = $vorige_aanmelding->datum;
                $contactpersoon = $vorige_aanmelding->contactpersoon;
                show_myrecy_message("info", sprintf(_("Op %s heeft u %d zakken kurk en %d zakken kaarsresten aangemeld, maar deze zijn nog niet gepland in een ophaalronde. Wilt u deze aantallen nog actualiseren?"),utf8_encode(strftime("%e %B %Y",strtotime($datum))),$zakken_kurk,$zakken_kaarsresten));
                $result->close();
            }
        }
        else
        {
            // could not query DB, so why even bother? quit! + show error message for users to contact adminstration
            show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
            exit;
        }

	  /// $_POST   mysqliescape() zowel HTML als PHP als SQL
	  /// insert t_aanmelding;
	  /// 		<p class="message">    Profile updated.<br />
	  
	  //     wp_verify_nonce( $_REQUEST['my_nonce'], 'process-comment'.$comment_id );
	?>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

    <?php wp_nonce_field( 'nieuwe_aanmelding_'.get_current_user_id().$aanmelding_id ); ?>
    <input type="hidden" id="aanmelding_id" name="aanmelding_id" value="<?php echo $aanmelding_id; ?>" />


    <h3><?php global $MYRECY_ophaalpunt_naam; echo sprintf(_("%s heeft momenteel het volgende in stock:"),$MYRECY_ophaalpunt_naam);?></h3>

    <table class="form-table-myrecy">
		<tr>
			<th><?php echo _("Kurk");?></th>
			<td><input class="number-input-slim" type="number" value="<?php echo /* check if is empty => '0'*/ $zakken_kurk; ?>" min="0" name="zakken_kurk" id="zakken_kurk" /> <?php echo _("zakken");?></td>
			<td><input class="number-input-slim" type="number" value="<?php echo $kg_kurk; ?>" min="0" name="kg_kurk" id="kg_kurk" /> <?php echo _("kilogram");?></td>
		</tr>
		<tr>
			<th><?php echo _("Kaarsresten");?></th>
			<td><input class="number-input-slim" type="number" value="<?php echo $zakken_kaarsresten; ?>" min="0" name="zakken_kaarsresten" id="zakken_kaarsresten" /> <?php echo _("zakken");?></td>
			<td><input class="number-input-slim" type="number" value="<?php echo $kg_kaarsresten; ?>" min="0" name="kg_kaarsresten" id="kg_kaarsresten" /> <?php echo _("kilogram");?></td>
		</tr>
		<tr>
			<th><label for="opmerkingen"><?php echo _("Opmerkingen");?></label></th>
			<td colspan="2"><textarea name="opmerkingen" id="opmerkingen" rows="5" cols="30"><?php /* to strip the MyRecy remark to avoid dubbels, thank you! */  echo htmlspecialchars($opmerkingen_form); ?></textarea></td>
		</tr>
	</table>

    <input type="submit" value="<?php echo _("Stock doorgeven aan De Vlaspit");?>">

    </form>
    <p><?php echo _("U kunt ofwel het aantal zakken ofwel het aantal kilo invoeren, of beide."); ?></p>
    </div>
</div>
<?php get_footer() ?>
