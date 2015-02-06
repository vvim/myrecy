<?php

   if (!is_user_logged_in() && ! (is_page("login") || is_page("lostpassword") ) )
   {
	wp_redirect( home_url()."/login" );
	exit;
   }

function user_language()
{
    global $user_ID;

	require_once("secure/db.php");

    if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.* FROM wordpress_link, ophaalpunten WHERE wordpress_userid = $user_ID and ophaalpunt_id = ophaalpunten.id"))
    {
        $ophaalpunt_from_db = $result->fetch_object();
        
        // ipv uit de DB te halen (table TALEN), hier de vier mogelijkheden:
        switch($ophaalpunt_from_db->taalvoorkeur)
        {
            case 1: // Dutch
                return "NL";
                break;
            case 2: // French
                return "FR";
                break;
            case 3: // German
                return "DE";
                break;
            case 4: // English
                return "EN";
                break;
        }
    }
    
    // nothing found?
    return "geen taalvoorkeur";
}

// language specifications:
        global $MYRECY_locale;

        switch (user_language())
        {
                case 'FR':
                        $MYRECY_locale = "fr_BE";
                        break;
                default:
                        $MYRECY_locale = "nl_BE";
                        break;
        }


        putenv("LC_ALL=$MYRECY_locale");
        putenv("LANG=" . $MYRECY_locale);
        setlocale(LC_ALL, $MYRECY_locale);
        $MYRECY_domain = "messages";
        bindtextdomain($MYRECY_domain, "/home/devlaspit/public_html/gettext/locale");
        bind_textdomain_codeset($MYRECY_domain, 'UTF-8');
        textdomain($MYRECY_domain);


  require_once('origami-header.php');

?>
