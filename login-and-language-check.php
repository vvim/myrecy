<?php

   if (!is_user_logged_in() && ! (is_page("login") || is_page("lostpassword") ) )
   {
	wp_redirect( home_url()."/login" );
	exit;
   }

function user_language()
{
    global $user_ID;
    
    // why don't we make $ophaalpunt_from_db a global var???
    global $MYRECY_ophaalpunt_naam;
    global $MYRECY_ophaalpunt_plaats;

	require_once("secure/db.php");

    if ($result = $MYRECY_mysqli->query("SELECT ophaalpunten.* FROM wordpress_link, ophaalpunten WHERE wordpress_userid = $user_ID and ophaalpunt_id = ophaalpunten.id"))
    {
        $ophaalpunt_from_db = $result->fetch_object();
        $result->close();

        $MYRECY_ophaalpunt_naam = $ophaalpunt_from_db->naam;
        $MYRECY_ophaalpunt_plaats = $ophaalpunt_from_db->plaats;
        
        
        /***     if preferred_language is changed in the profile, that change is not
                   yet put in the DB when this check is performed. So the first reload
                   of the profile-page will still be in the previous preferred_language.

         dirty hack:  check if the language is being changed, and return that output

        consequences: also the value of the global vars $MYRECY_ophaalpunt_naam
                                 and $MYRECY_ophaalpunt_plaats should be adjusted
        ***/        
        $pref_lang = $ophaalpunt_from_db->taalvoorkeur;
        
         if(wp_verify_nonce( $_POST["_wpnonce"], 'profiel_wijziging_'.get_current_user_id().$ophaalpunt_from_db->id ))
         {
                // now we know a change has been made in the profile, so re-instate the lang_pref and the global variables:
                $pref_lang = $_POST["taalvoorkeur"];
                $MYRECY_ophaalpunt_naam = $_POST["naam_ophaalpunt"];
                $MYRECY_ophaalpunt_plaats = $_POST["plaats_ophaalpunt"];
         }
        /*** </dirty_pref_lang_hack> ***/
        
        // ipv uit de DB te halen (table TALEN), hier de vier mogelijkheden:
        switch($pref_lang)
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

function MYRECY_Administrator()
{
    global $user_ID;
    global $ADMIN_Wim;
    global $ADMIN_Geert;
    $ADMIN_Wim = 1;
    $ADMIN_Geert = 4;
	return (($user_ID == $ADMIN_Wim) || ($user_ID == $ADMIN_Geert));
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
