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

	require_once("secure/db.php");

    $timestamp = intval($_GET['q']);
    $materiaal = $_GET['materiaal'];
    $interval = " + 1 month";
    $startDate = date("Y-m-d",$timestamp);
    $endDate = date("Y-m-d",strtotime( $startDate.$interval));

    $query = "SELECT ophalinghistoriek.* FROM wordpress_link, ophalinghistoriek WHERE wordpress_userid = $user_ID AND ophaalpunt_id = ophalinghistoriek.ophaalpunt AND ophalingsdatum > \"$startDate\" AND ophalingsdatum < \"$endDate\" AND ";

    if($materiaal == "kurk")
    {
        $query .= "(kg_kurk > 0 OR zakken_kurk > 0) "; // enkel kurk zakken / kg
    }
    else
    {
        $query .= "(kg_kaarsresten > 0 OR zakken_kaarsresten > 0) "; // enkel kaarsresten zakken / kg
    }
    
    echo $query;
    exit;

    if ($result = $MYRECY_mysqli->query($query))
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

    echo "Interval ".$timestamp." ofte ".date('01 M Y',$timestamp)." voor ".$materiaal;

?>
</body>
</html>