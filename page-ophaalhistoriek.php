<?php
require_once("myrecy-func.php");

get_header(); the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
	<?php
		require_once("secure/db.php");

        // ipv uit de DB te halen (table FREQUENTIE), hier de drie mogelijkheden:
        function which_frequency( $frequency  )
        {
            switch($frequency)
            {
                case 1:
                    return "maandelijks";
                    break;
                case 2:
                    return "trimester";
                    break;
                case 3:
                    return "jaarlijks";
                    break;
            }
        }

        function print_attest_frequency_select( $frequency )
        {
            //// TODO: select MaxDate / MinDate of Ophaalhistoriek for this ophaalpunt: http://stackoverflow.com/questions/8727936/mysql-get-mindate-and-maxdate-in-one-query
            
            // solution to date intervals by @elviejo : http://stackoverflow.com/a/1449514/707700
            $startDate = strtotime("01 Sept 2010");
            $endDate = time(); // "now"

            $currentDate = $endDate;

            echo "<select onchange=\"showUser(this.value)\">\n";

            switch ($frequency)
            {
                case 1:
                    $interval = " -1 month"; // montly
                    $representation = '%b %Y';

                    while ($currentDate >= $startDate)
                    {
                        echo "                <option value=\"".$currentDate."\">".strftime($representation,$currentDate) . "</option>\n";
                        $currentDate = strtotime( date('Y/m/01/',$currentDate).$interval);
                    }

                    break;

                case 2:
                    $interval = " -3 months"; // quarterly
                    // maybe interesting: http://stackoverflow.com/questions/21185924/get-startdate-and-enddate-for-current-quarter-php
                    $representation = '%Y';

                    while ($currentDate >= $startDate)
                    {
                        echo "                <option value=\"".$currentDate."\">".quarter($currentDate). "e trimester ". strftime($representation,$currentDate) . "</option>\n";
                        $currentDate = strtotime( date('Y/m/01/',$currentDate).$interval);
                    }
                    
                    break;
                
                default:
                    $interval = " -1 year"; // yearly
                    $representation = '%Y';

                    while ($currentDate >= $startDate)
                    {
                        echo "                <option value=\"".$currentDate."\">".strftime($representation,$currentDate) . "</option>\n";
                        $currentDate = strtotime( date('Y/m/01/',$currentDate).$interval);
                    }

                    break;
            }

            echo "            </select>\n";

        }


        // zie http://php.net/manual/en/mysqli.query.php
		if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.* FROM wordpress_link, ophaalpunten WHERE wordpress_userid = $user_ID AND ophaalpunt_id = ophaalpunten.id"))
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
    <script>
        function showUser(str) {
            materiaal = "kurk";
            if (str == "") {
                document.getElementById("txtHint").innerHTML = "";
                return;
            } else { 
                if (window.XMLHttpRequest) {
                    // code for IE7+, Firefox, Chrome, Opera, Safari
                    xmlhttp = new XMLHttpRequest();
                } else {
                    // code for IE6, IE5
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                        document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
                    }
                }
                xmlhttp.open("GET","gethistoriek/?q="+str+"&materiaal="+materiaal,true);
                xmlhttp.send();
            }
        }
    </script>
    <table class="form-table-myrecy">
        <tr>
            <?php
                    if($ophaalpunt_from_db->kurk == 1)
                    { ?>
            <th>kurk</th>
            <?php
                    }
                    if($ophaalpunt_from_db->parafine == 1)
                    { ?>
            <th>kaarsresten</th>
            <?php
                    } ?>
        </tr>
        <tr>
            <?php
                    if($ophaalpunt_from_db->kurk == 1)
                    { ?>
            <td><form><input type="hidden" value="kurk">
            <?php
                        // startdatum en einddatum nog bepalen!
                        print_attest_frequency_select( $ophaalpunt_from_db->frequentie_attest);
                        echo "            </form></td>\n";
                    }
                    if($ophaalpunt_from_db->parafine == 1)
                    { ?>
            <td><form><input type="hidden" value="kaarsresten">
            <?php
                        // startdatum en einddatum nog bepalen!
                        print_attest_frequency_select( $ophaalpunt_from_db->frequentie_attest);
                        echo "            </form></td>\n";
                    } ?>
        </tr>
    </table>
    <?php
            if($ophaalpunt_from_db->kurk == 1)
            { ?>
    <input type="checkbox" name="toon_kurk" value="1" > kurk<br/>
    <?php
            }
            else
            { ?>
    <span class="ophaalhistoriek-disabled">kurk</span> (gedesactiveerd want in uw profiel staat dat er bij u geen kurk wordt opgehaald).<br/>
    <?php
            }
            if($ophaalpunt_from_db->parafine == 1)
            { ?>
    <input type="checkbox" name="toon_kaarsresten" value="1" > kaarsresten<br/>
    <?php
            }
            else
            { ?>
    <span class="ophaalhistoriek-disabled">kaarsresten</span> (gedesactiveerd want in uw profiel staat dat er bij u geen kaarsresten wordt opgehaald).<br/>
    <?php
            }
        if($ophaalpunt_from_db->attest_nodig == 1)
        {
            // START <attesten>:
            ?>
            <p>Uw ophaalpunt vraagt attesten op <?php echo which_frequency($ophaalpunt_from_db->frequentie_attest); ?> basis:</p>
            <?php print_attest_frequency_select($ophaalpunt_from_db->frequentie_attest); ?>
            <?php
            // EINDE </attesten>:
        }
        else
            echo "<p>Uw ophaalpunt heeft geen attesten aangevraagd. U kan dit aanpassen in de profielinstellingen</p>";
    ?>
	</div>

<pre>
(eerste ophaling ooit volgens db: september 2010)

attest_nodig
frequentie_attest

 ** if attest_nodig AND frequentie == MAANDELIJKS
 => voorstellen per maand
 ** if attest_nodig AND frequentie == TRIMESTER
 => voorstellen per maand
 ** if attest_nodig AND frequentie == JAARLIJKS
 => voorstellen per maand

// eventueel ook een termijn à la routetool geven?

// resultaten tonen met AJAX zoals in BH
</pre>

<?php
// 
		if ($result = $MYRECY_mysqli->query("SELECT ophalinghistoriek.* FROM ophalinghistoriek, wordpress_link WHERE ophalinghistoriek.ophaalpunt = wordpress_link.ophaalpunt_id AND wordpress_link.wordpress_userid = $user_ID"))
		{
			//printf("Select returned %d rows.\n", $result->num_rows);
			if($result->num_rows < 1)
			{
				// no results found, so why even bother? quit! + show error message for users to contact adminstration
				show_myrecy_message("error", "Geen ophaalhistoriek gelinkt aan je gebruikersnaam, contacteer ons voor hulp.");
				$result->close();
				//exit;
			}
			else
			{
			    // if kurk_zakken <> 0 OR kurk_kg <> 0 then add row to KURK
			    // if kaars_zakken <> 0 OR kaars_kg <> 0 then add row to KAARS
				$ophaalpunt_from_db = $result->fetch_object();
				$result->close();
			}
		}
?>
<br>
<div id="txtHint"><b>De ophaalhistoriek zal hier verschijnen...</b></div></div>
<?php get_footer() ?>
