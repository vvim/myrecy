<?php 
require_once("myrecy-func.php");

get_header(); the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
	<?php
	    // read http://codex.wordpress.org/WordPress_Nonces and stackoverflow.com/questions/1924939/php-request-vs-get-and-post
  	    $zakken_kurk = 0;
	    $kg_kurk = 0;
	    $zakken_kaarsresten = 0;
	    $kg_kaarsresten = 0;

    	if(wp_verify_nonce( $_POST["_wpnonce"], 'nieuwe_aanmelding_'.get_current_user_id() ))
    	{
    	    require_once("secure/db.php");
            $user_ID = get_current_user_id();

    	    $zakken_kurk = $MYRECY_mysqli->real_escape_string($_POST["zakken_kurk"]);
    	    $kg_kurk = $MYRECY_mysqli->real_escape_string($_POST["kg_kurk"]);
    	    $zakken_kaarsresten = $MYRECY_mysqli->real_escape_string($_POST["zakken_kaarsresten"]);
    	    $kg_kaarsresten = $MYRECY_mysqli->real_escape_string($_POST["kg_kaarsresten"]);
    	    $opmerkingen = $MYRECY_mysqli->real_escape_string($_POST["opmerkingen"]);
    	    $opmerkingen_met_handtekening = $MYRECY_mysqli->real_escape_string($_POST["opmerkingen"]." -- ingegeven via MyRecy");
    	    
    	    if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.id, ophaalpunten.contactpersoon FROM wordpress_link, ophaalpunten WHERE wordpress_userid = $user_ID and ophaalpunt_id = ophaalpunten.id"))
    	    {
                //printf("Select returned %d rows.\n", $result->num_rows);
                if($result->num_rows < 1)
                {
                    // no results found, so why even bother? quit! + show error message for users to contact adminstration
		    show_myrecy_message("error", "Geen ophaalpunt gevonden dat overeenkomt met uw gebruikersnaam, stock is niet doorgegeven aan De Vlaspit. Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp.");
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
        	    $query = "INSERT INTO aanmelding (id, timestamp, ophaalpunt, contactpersoon, datum, zakken_kurk, kg_kurk, zakken_kaarsresten, kg_kaarsresten, opmerkingen, ophaalronde_datum, volgorde)
        	                          VALUES(NULL, CURRENT_TIMESTAMP, ?, ?, CURRENT_DATE, ?, ?, ?, ?, ?, NULL, NULL)";
                    $statement = $MYRECY_mysqli->prepare($query);

                    //bind parameters for markers, where (s = string, i = integer, d = double,  b = blob)
                    $statement->bind_param('isiiiis', $ophaalpunt, $contactpersoon, $zakken_kurk, $kg_kurk, $zakken_kaarsresten, $kg_kaarsresten, $opmerkingen_met_handtekening);

                    if($statement->execute())
                    {
                      	show_myrecy_message("good","Hoeveelheden zijn doorgegeven aan De Vlaspit: ".$zakken_kurk." zakken kurk en ".$zakken_kaarsresten." zakken kaarsresten.");
                        // ID of last inserted record is : ' .$statement->insert_id ';
                    }
                    else
                    {
                       	show_myrecy_message("error", "Geen hoeveelheden opgegeven, stock is niet doorgegeven aan De Vlaspit. De databank gaf volgende foutmelding: ".$MYRECY_mysqli->errno .": ". $MYRECY_mysqli->error."<br />Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp");
                        exit;

                    }
                    $statement->close();

                    // to really insert: http://stackoverflow.com/questions/16835753/inserting-data-to-table-mysqli-insert
        	}
        	else
        	{
            	    show_myrecy_message("error","Geen hoeveelheden opgegeven, stock is niet doorgegeven aan De Vlaspit.");
            	}
    	    }
    	    else
    	    {
        	    show_myrecy_message("error","Geen verbinding kunnen maken met de databank, de hoeveelheden zijn niet doorgegeven aan De Vlaspit.  Gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp.");
        	}
    	}

	  /// $_POST   mysqliescape() zowel HTML als PHP als SQL
	  /// insert t_aanmelding;
	  /// 		<p class="message">    Profile updated.<br />
	  
	  //     wp_verify_nonce( $_REQUEST['my_nonce'], 'process-comment'.$comment_id );
	?>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

    <?php wp_nonce_field( 'nieuwe_aanmelding_'.get_current_user_id() ); ?>


    <h3>Momenteel heeft ons ophaalpunt het volgende in stock</h3>

    <table class="form-table-myrecy">
		<tr>
			<th>Kurk</th>
			<td><input class="number-input-slim" type="number" value="<?php echo /* check if is empty => '0'*/ $zakken_kurk; ?>" min="0" name="zakken_kurk" id="zakken_kurk" /> zakken</td>
			<td><input class="number-input-slim" type="number" value="<?php echo $kg_kurk; ?>" min="0" name="kg_kurk" id="kg_kurk" /> kilogram</td>
		</tr>
		<tr>
			<th>Kaarsresten</th>
			<td><input class="number-input-slim" type="number" value="<?php echo $zakken_kaarsresten; ?>" min="0" name="zakken_kaarsresten" id="zakken_kaarsresten" /> zakken</td>
			<td><input class="number-input-slim" type="number" value="<?php echo $kg_kaarsresten; ?>" min="0" name="kg_kaarsresten" id="kg_kaarsresten" /> kilogram</td>
		</tr>
		<tr>
			<th><label for="opmerkingen">Opmerkingen</label></th>
			<td colspan="2"><textarea name="opmerkingen" id="opmerkingen" rows="5" cols="30"><?php /* to strip the MyRecy remark to avoid dubbels, thank you! */  echo $_POST["opmerkingen"]; ?></textarea></td>
		</tr>
	</table>

    <input type="submit" value="Stock doorgeven aan De Vlaspit">

    </form>
    </div>
	
<?php get_footer() ?>
