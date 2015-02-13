<?php
require_once("myrecy-func.php");

get_header(); the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
	<?php
		require_once("secure/db.php");
/*
        // ipv uit de DB te halen (table FREQUENTIE), hier de drie mogelijkheden:
        function which_frequency( $frequency  )
        {
            switch($frequency)
            {
                case 1:
                    return "maandelijks";
                    break;
                case 2:
                    return "kwartaal";
                    break;
                default:
                    return "jaarlijks";
                    break;
            }
        }
*/
        function print_attest_frequency_select( $frequency, $materiaal )
        {
            //// TODO: select MaxDate / MinDate of Ophaalhistoriek for this ophaalpunt: http://stackoverflow.com/questions/8727936/mysql-get-mindate-and-maxdate-in-one-query
            if (($materiaal != _("kurk")) && ($materiaal != _("kaarsresten")))
            {
                echo "<!-- [vvim][DEBUG][page-ophaalhistoriek::print_attest_frequency_select()] wrong material: ".htmlspecialchars($materiaal)." , exit function -->\n";
                return;
            }

            global $DB_materiaal;
            $DB_materiaal = "kurk";

            if ($materiaal == _("kaarsresten")) // behalve als het kaarsresten zijn :-)
            {
                $DB_materiaal = "kaarsresten";
            }
            // for some reason, requiring db.php once outside of the function call, does not count?
            // require("secure/db.php"); --> not needed: https://wordpress.org/support/topic/global-variables-not-working

            global $user_ID;
            global $MYRECY_mysqli;

            $result = $MYRECY_mysqli->query("SELECT min(ophalinghistoriek.ophalingsdatum),  max(ophalinghistoriek.ophalingsdatum) FROM wordpress_link, ophalinghistoriek WHERE wordpress_userid = $user_ID AND ophaalpunt_id = ophalinghistoriek.ophaalpunt AND (kg_$DB_materiaal > 0 OR zakken_$DB_materiaal > 0)");
            
            if (!$result)
            {
                echo "<!-- [vvim][DEBUG][page-ophaalhistoriek::print_attest_frequency_select()] no DB connection: ".$MYRECY_mysqli->error." , exit function -->\n";
                return;
            }

            //printf("Select returned %d rows.\n", $result->num_rows);
            if($result->num_rows < 1)
            {
                // normally we would never get here, if there is no collectionhistory, the min() and max() will return NULL
                echo sprintf(_("geen historiek voor %s\n"),$materiaal);
                return;
            }

            $row = $result->fetch_row();
            if($row[0] == null)
            {
                // better than checking for num_rows < 1 , see comment above
                echo sprintf(_("geen historiek voor %s\n"),$materiaal);
                return;
            }

            // solution to date intervals by @elviejo : http://stackoverflow.com/a/1449514/707700
            $startDate = strtotime($row[0]);
            $endDate = strtotime($row[1]);


/*
            $startDate = strtotime("01 Sept 2010");
            $endDate = time(); // "now"
*/
            echo "<select onchange=\"showUser(this.value,this.name)\" name=\"$DB_materiaal\">\n";
            echo "                <option></option>\n";

            switch ($frequency)
            {
                case 1:
                    $currentDate = $endDate;
                    $startDate = strtotime( date('Y/m/01',$startDate));
                    $interval = " -1 month"; // montly
                    $representation = '%b %Y';

                    while ($currentDate >= $startDate)
                    {
                        echo "\n";
                        echo "<!-- [vvim] currentdate:".date('Y/m/d',$currentDate)." >= ".date('Y/m/d',$startDate)."-->";
                        echo "\n";
                        echo "                <option value=\"".$currentDate."\">".utf8_encode(strftime($representation,$currentDate)) . "</option>\n";
                        $currentDate = strtotime( date('Y/m/d',$currentDate).$interval);
                    }

                    break;

                case 2:
                    $currentDate = $endDate; // --> DEZE KLOPT NIET , zie code gethistoriek for testing
                    $startDate = strtotime( date('Y/m/01',$startDate));
                    $interval = " -3 months"; // quarterly
                    // maybe interesting: http://stackoverflow.com/questions/21185924/get-startdate-and-enddate-for-current-quarter-php
                    $representation = '%Y';

                    while ($currentDate >= $startDate)
                    {
                        echo "\n";
                        echo "<!-- [vvim] currentdate:".date('Y/m/d',$currentDate)." >= ".date('Y/m/d',$startDate)."-->";
                        echo "\n";
                        echo "                <option value=\"".$currentDate."\">".sprintf(_("%de kwartaal %s"),quarter($currentDate),strftime($representation,$currentDate))."</option>\n";
                        $currentDate = strtotime( date('Y/m/d',$currentDate).$interval);
                    }
                    
                    break;
                
                default:
                    $currentDate = $endDate;
                    $startDate = strtotime( date('Y/01/01',$startDate));
                    $interval = " -1 year"; // yearly
                    $representation = '%Y';

                    while ($currentDate >= $startDate)
                    {
                        echo "\n";
                        echo "<!-- [vvim] currentdate:".date('Y/m/d',$currentDate)." >= ".date('Y/m/d',$startDate)."-->";
                        echo "\n";
                        echo "                <option value=\"".$currentDate."\">".strftime($representation,$currentDate) . "</option>\n";
                        $currentDate = strtotime( date('Y/m/d',$currentDate).$interval);
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
	?>
    <script>
        function showUser(str,materiaal) {
            if ((str == "")||(materiaal == "")) {
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
                xmlhttp.open("GET","gethistoriek/?q="+str+"&materiaal="+materiaal+"&frequentie="+<?php echo $ophaalpunt_from_db->frequentie_attest; ?>,true);
                xmlhttp.send();
            }
        }
    </script>
    <!--
    <table class="form-table-myrecy">
        <tr>
            <th><label for="naam_ophaalpunt"><?php echo _("Naam ophaalpunt"); ?></label></th>
            <td><?php global $MYRECY_ophaalpunt_naam; global $MYRECY_ophaalpunt_plaats; echo $MYRECY_ophaalpunt_naam; ?></td>
        <tr>
        </tr>
            <th><label for="plaats_ophaalpunt"><?php echo _("Plaats"); ?></label></th>
            <td><?php echo $MYRECY_ophaalpunt_plaats; ?></td>
        </tr>
    </table>
    -->
    <p><strong><label for="naam_ophaalpunt"><?php echo _("Naam ophaalpunt").":"; ?></label></strong> <?php global $MYRECY_ophaalpunt_naam; global $MYRECY_ophaalpunt_plaats; echo $MYRECY_ophaalpunt_naam; ?><br/>
    <strong><label for="plaats_ophaalpunt"><?php echo _("Plaats").":"; ?></label></strong> <?php echo $MYRECY_ophaalpunt_plaats; ?></p>

    <table class="form-table-myrecy">
        <tr>
            <?php
                    if($ophaalpunt_from_db->kurk == 1)
                    { ?>
            <th><?php echo _("kurk"); ?></th>
            <?php
                    }
                    if($ophaalpunt_from_db->parafine == 1)
                    { ?>
            <th><?php echo _("kaarsresten"); ?></th>
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
                        print_attest_frequency_select( $ophaalpunt_from_db->frequentie_attest, _("kurk"));
                        echo "            </form></td>\n";
                    }
                    if($ophaalpunt_from_db->parafine == 1)
                    { ?>
            <td><form><input type="hidden" value="kaarsresten">
            <?php
                        // startdatum en einddatum nog bepalen!
                        print_attest_frequency_select( $ophaalpunt_from_db->frequentie_attest, _("kaarsresten"));
                        echo "            </form></td>\n";
                    } ?>
        </tr>
    </table>
    
    <div id="txtHint"><strong><?php echo _("Geef een periode op voor kurk en/of kaarsresten en daarna zal de ophaalhistoriek verschijnen."); ?></strong></div></div>

	</div>
<br>
<?php get_footer() ?>
