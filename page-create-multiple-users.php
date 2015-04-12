<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false); ?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 

  <title>CODE: adding multiple users to WordPress</title>
  <style type="text/css">
  /* feedback to the user, taken from http://aviaryan.in/blog/css-notification-bubble-box.html */
    .symbol {
        font-size: 0.9em;
        font-family: Times New Roman;
        border-radius: 1em;
        padding: .1em .6em .1em .6em;
        font-weight: bolder;
        color: white;
        background-color: #4E5A56;
    }

    .icon-info { background-color: #3229CF; }
    .icon-error { background: #e64943; font-family: Consolas; }
    .icon-tick { background: #13c823; }
    .icon-excl { background: #ffd54b; color: black; }

    .icon-info:before { content: 'i'; }
    .icon-error:before { content: 'x'; }
    .icon-tick:before { content: '\002713'; }
    .icon-excl:before { content: '!'; }

    .notify {
        background-color:#e3f7fc; 
        color:#555; 
        border:.1em solid;
        border-color: #8ed9f6;
        border-radius:10px;
        font-family:Tahoma,Geneva,Arial,sans-serif;
        font-size:1.1em;
        padding:10px 10px 10px 10px;
        margin:10px;
        cursor: default;
    }

    .notify-yellow { background: #fff8c4; border-color: #f7deae; }
    .notify-red { background: #ffecec; border-color: #fad9d7; }
    .notify-green { background: #e9ffd9; border-color: #D1FAB6; }
  </style>

<?

/* example from http://php.net/manual/en/language.types.array.php
// Simple array:
$array = array(1, 2);
$count = count($array);
for ($i = 0; $i < $count; $i++) {
    echo "\nChecking $i: \n";
    echo "Bad: " . $array['$i'] . "\n";
    echo "Good: " . $array[$i] . "\n";
    echo "Bad: {$array['$i']}\n";
    echo "Good: {$array[$i]}\n";
}
*/



function show_myrecy_message($style, $message)
{
   // based on http://aviaryan.in/blog/css-notification-bubble-box.html
   switch($style) {
     case "error":
	echo "<div class=\"notify notify-red\"><span class=\"symbol icon-error\"></span> $message</div>";
	break;

     case "good":
     case "tick":
	echo "<div class=\"notify notify-green\"><span class=\"symbol icon-tick\"></span> $message</div>";
	break;

     case "excl":
     case "warning":
	echo "<div class=\"notify notify-yellow\"><span class=\"symbol icon-excl\"></span> $message</div>";
	break;

     default:
	echo "<div class=\"notify\"><span class=\"symbol icon-info\"></span> $message</div>";
   }
}


	require_once("secure/db.php");




    $CSVrows = 0;
    if (($handle = fopen("/tmp/users.csv", "r")) !== FALSE)
    {

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
        {
            $ophaalpunten[] = $data[1];
            $usernames[] =  $data[7];
            $emails[] = $data[4];
            $passwords[] = $data[8];
            //echo "ophaalpunt_id {$data[1]}, gebruiker {$data[7]}, wachtwoord {$data[8]}, email {$data[4]}<br>\n";
            $CSVrows++;

        }
        //echo "handled $CSVrows rows";
        fclose($handle);
    }
/*
$usernames = array('Ariel', 'Bert', 'Chloe', 'Dirk');
$emails = array('ar@hotmail.com', 'bro@gmail.com', 'clowntje@skynet.be', 'd.evers@posthofvzw.be');
$passwords = array('aaaaa', 'bbbb', 'ccccc', 'dddd');
$ophaalpunten = array(1,2,3,4);
*/

$count_u = count($usernames);
$count_e = count($emails);
$count_p = count($passwords);
$count_o = count($ophaalpunten);

if( ($count_u != $count_e) || ($count_u != $count_p) || ($count_u != $count_o) || ($count_u != $CSVrows) )
{
    echo "ERROR: different counts U $count_u / E $count_e / P $count_p / O $count_o / CSV $CSVrows";
    exit;
}
else
{
    echo "same numbers of U $count_u / E $count_e / P $count_p / O $count_o / CSV $CSVrows";
}

$count = $count_u;


echo "<p>\n";

for ($i = 0; $i < $count; $i++) {
    echo "New user {$usernames[$i]} with email {$emails[$i]} and password {$passwords[$i]}, from ophaalpunt {$ophaalpunten[$i] } <br>\n";
    
    
    $user_name = $usernames[$i];
    $user_email = $emails[$i];
    $user_password = $passwords[$i];
    $ophaalpunt_id_from_form = $ophaalpunten[$i];

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

            $user_id = username_exists( $user_name ); //      This function returns the user ID if the user exists or null if the user does not exist.  http://codex.wordpress.org/Function_Reference/username_exists
            if ( !$user_id and email_exists($user_email) == false ) {
                $user_id = wp_create_user( $user_name, $user_password, $user_email ); // wp_create_user == wp_insert_user : http://wordpress.stackexchange.com/a/66566/67607
            } else {
                show_myrecy_message("error", sprintf(_("Gebruiker %s ( %s ) bestaat al."),$user_name,$user_email));
                // see http://www.computerhope.com/issues/ch000317.htm
                echo "\n<form><input type=\"button\" value=\"Terug\" onClick=\"history.go(-1);return true;\"></form>";
                exit;
            }


            /////////////// special setting for my personal use: MYRECY
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
                show_myrecy_message("good", sprintf(_("Nieuwe gebruiker <strong>%s</strong> (id %d) aangemaakt en gelinkt aan ophaalpunt %d: <em>%s</em>. Het wachtwoord is <em>%s</em>"),$user_name,$user_id, $ophaalpunt_id_from_form, $ophaalpunt_from_db->naam, $user_password));
            }
            else
            {
                show_myrecy_message("error", sprintf(_("Probleem met de databank (error %d) bij het linken van gebruiker %s (id %d) aan ophaalpunt %d: <em>%s</em>: %s"),$MYRECY_mysqli->errno,$user_name,$user_id,$ophaalpunt_id_from_form, $ophaalpunt_from_db->naam,$MYRECY_mysqli->error));
                exit;
            }
            $statement->close();
    /* </ difference with page-beheer-ophaalpunt-linken-uzelf.php> */
    }

?>
