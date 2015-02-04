<!DOCTYPE html>
<html>
<head>
<?php /*
<style>
table {
    width: 100%;
    border-collapse: collapse;
}

table, td, th {
    border: 1px solid black;
    padding: 5px;
}

th {text-align: left;}
*/ ?>
</style>
</head>
<body>

<?php
    require_once("myrecy-func.php");

	require_once("secure/db.php");

    // source: http://stackoverflow.com/a/21186249/707700 by @SKRocks
    // array return: http://stackoverflow.com/a/3451954/707700 by @dockeryZ
	function getquarters( $bigtimestamp )
	{
          $current_month = date('m',$bigtimestamp);
          $current_year = date('Y',$bigtimestamp);
          
          if($current_month>=1 && $current_month<=3)
          {
          	$quarter = sprintf(_("1e kwartaal %d"),$current_year);
            $start_date = strtotime('1-January-'.$current_year);  // timestamp or 1-Janauray 12:00:00 AM
            $end_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM means end of 31 March
          }
          else  if($current_month>=4 && $current_month<=6)
          {
          	$quarter = sprintf(_("2e kwartaal %d"),$current_year);
            $start_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM
            $end_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM means end of 30 June
          }
          else  if($current_month>=7 && $current_month<=9)
          {
          	$quarter = sprintf(_("3e kwartaal %d"),$current_year);
            $start_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM
            $end_date = strtotime('1-October-'.$current_year);  // timestamp or 1-October 12:00:00 AM means end of 30 September
          }
          else  if($current_month>=10 && $current_month<=12)
          {
          	$quarter = sprintf(_("4e kwartaal %d"),$current_year);
            $start_date = strtotime('1-October-'.$current_year);  // timestamp or 1-October 12:00:00 AM
            $end_date = strtotime('1-January-'.($current_year+1));  // timestamp or 1-January Next year 12:00:00 AM means end of 31 December this year
          }

        return array($start_date, $end_date,$quarter);
    }

    // ipv uit de DB te halen (table FREQUENTIE), hier de drie mogelijkheden:
    function calculate_daterange( $frequency, $timestamp  )
    {
        switch($frequency)
        {
            case 1:
                // "maandelijks";
                $representation = '%B %Y';
                $startDate = date("Y-m-01",$timestamp);
                $interval = " + 1 month";
                $endDate = date("Y-m-01",strtotime( $startDate.$interval));
                return array(strtotime($startDate), strtotime($endDate), strftime($representation,$timestamp));
                break;
            case 2:
                // "kwartaal";
                return getquarters($timestamp);
                break;
            default:
                // "jaarlijks";
                $representation = '%Y';
                $startDate = date("Y-01-01",$timestamp);
                $interval = " + 1 year";
                $endDate = date("Y-m-d",strtotime( $startDate.$interval));
                return array(strtotime($startDate), strtotime($endDate), strftime($representation,$timestamp));
                break;
        }
    }

    $timestamp = intval($_GET['q']);
    $frequency = intval($_GET['frequentie']);
    $materiaal = $_GET['materiaal'];

    $array_of_results = calculate_daterange($frequency,$timestamp);
    $startDate = date("Y-m-d",$array_of_results[0]);
    $endDate = date("Y-m-d",$array_of_results[1]);
    $daterange = $array_of_results[2];

    $query = "SELECT ophalinghistoriek.* FROM wordpress_link, ophalinghistoriek WHERE wordpress_userid = $user_ID AND ophaalpunt_id = ophalinghistoriek.ophaalpunt AND ophalingsdatum >= \"$startDate\" AND ophalingsdatum < \"$endDate\" AND ";

    if($materiaal == _("kurk"))
    {
        $query .= "(kg_kurk > 0 OR zakken_kurk > 0) "; // enkel kurk zakken / kg
    }
    else
    {
        $query .= "(kg_kaarsresten > 0 OR zakken_kaarsresten > 0) "; // enkel kaarsresten zakken / kg
    }
    
    // for debugging purposes only: show_myrecy_message("info", $query." voor ".$materiaal." met frequentie ".$frequency);

    if ($result = $MYRECY_mysqli->query($query))
    {
        echo "<p><strong>".sprintf(_("Historiek ophalingen %s voor %s."),htmlspecialchars($materiaal),htmlspecialchars($daterange))."</strong></p>";
        //printf("Select returned %d rows.\n", $result->num_rows);
        if($result->num_rows < 1)
        {
            // no results found, so why even bother? quit! + show error message for users to contact adminstration
            echo "<p>"._("Geen ophalingen gevonden.")."</p>";
            $result->close();
            exit;
        }

        $nr_ophalingen = 0;
        $totaal_zakken = 0;
        $totaal_kg = 0;
        echo "\n<table>\n";
        echo "\n<tr><th>"._("ophalingsdatum")."</th><th>"._("zakken")."</th><th>"._("kg")."</th></tr>\n";
        
        if($materiaal == _("kurk"))
        {
            while ($historiek_from_db = $result->fetch_object())
            {
                printf ("<tr><td>%s</td><td>%d</td><td>%d</td></tr>\n", strftime("%e %b %Y",strtotime($historiek_from_db->ophalingsdatum)), $historiek_from_db->zakken_kurk, $historiek_from_db->kg_kurk);
                $nr_ophalingen++;
                $totaal_zakken += intval($historiek_from_db->zakken_kurk);
                $totaal_kg += intval($historiek_from_db->kg_kurk);
            }
        }
        else
        {
            while ($historiek_from_db = $result->fetch_object())
            {
                printf ("<tr><td>%s</td><td>%d</td><td>%d</td></tr>\n", strftime("%e %b %Y",strtotime($historiek_from_db->ophalingsdatum)), $historiek_from_db->zakken_kaarsresten, $historiek_from_db->kg_kaarsresten);
                $nr_ophalingen++;
                $totaal_zakken += intval($historiek_from_db->zakken_kaarsresten);
                $totaal_kg += intval($historiek_from_db->kg_kaarsresten);
            }
        }
        echo "\n</table>\n";
        $result->close();
        printf(_("<p>%d ophalingen in totaal voor <strong>%d</strong> zakken en %d kilogram.</p>\n"),$nr_ophalingen, $totaal_zakken, $totaal_kg);
    }
    else
    {
        // could not query DB, so why even bother? quit! + show error message for users to contact adminstration
        show_myrecy_message("error", _("De MyRecy-databank is momenteel niet bereikbaar, gelieve even te wachten en opnieuw te proberen. Indien het probleem zich blijft voordoen, contacteer ons voor hulp."));
        exit;
    }

?>
</body>
</html>