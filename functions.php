<?php

/* from http://codex.wordpress.org/Child_Themes - Using functions.php

Unlike style.css, the functions.php of a child theme does not override its counterpart from the parent. Instead, it is loaded in addition to the parent’s functions.php. (Specifically, it is loaded right before the parent’s file.) 

*/

add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_style' );
function enqueue_parent_theme_style() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

//to translate with gettext: see https://pippinsplugins.com/introduction-gettext-filter-wordpress/
function pippin_gettext_demo($translated_text, $text , $domain)
{
    // mogen deze vertaling niet gewoon in de GETTEXT opgenomen worden via POEDIT?
	switch($translated_text)
	{
        case  'Je bent nu uitgelogd.':
            return 'Je bent nu uitgelogd.<br/>Vous n\'êtes plus en ligne (déconnecté)';
            break;
        case 'Online module voor ophaalpunten De Vlaspit':
            return _('Online module voor ophaalpunten De Vlaspit');
            break;
        case 'Gebruikersnaam':
            return 'Gebruikersnaam / Nom de l\'utilisateur';  // gettext is not useful as the locale is not known at this point.
            break;
        case 'Wachtwoord':
            return 'Wachtwoord / Mot de passe';
            break;
        case 'Gegevens onthouden':
            return 'Gegevens onthouden / Retenir les données';
            break;
        case 'Inloggen':
            return 'Inloggen / Connectez-vous';
            break;
        case 'Lost Password':
            return 'Wachtwoord verloren / Mot de passe oublié';
            break;
        case 'Voer a.u.b. je gebruikersnaam of e-mailadres in. Je ontvangt per e-mail een link waarmee je een nieuw wachtwoord kunt aanmaken.':
            return 'Voer a.u.b. je gebruikersnaam of e-mailadres in. Je ontvangt per e-mail een link waarmee je een nieuw wachtwoord kunt aanmaken.<br/>Introduisez votre nom d\'utilisateur ou votre adresse email. Vous recevrez un lien par mail qui vous permettra de former un mot de passe nouveau';
            break;
        case 'Gebruikersnaam of e-mailadres:':
            return 'Gebruikersnaam of e-mailadres / Nom d\'utilisateur ou adresse email';
            break;
        case 'Nieuw wachtwoord aanmaken':
            return 'Nieuw wachtwoord aanvragen / Nouveau mot de passe';
            break;
        case '<strong>MISLUKT</strong>: Ongeldige gebruikersnaam of e-mailadres.':
            return '<strong>MISLUKT</strong>: Ongeldige gebruikersnaam of e-mailadres.<br/><strong>ERREUR</strong>: le nom d\'utilisateur ou l\'adresse email n\'est pas correcte';
            break;
        case 'Controleer je e-mail voor de bevestigingslink.':
            return 'Controleer je e-mail voor de bevestigingslink.<br/>Controlez vos mails pour retrouver le lien pour affirmer votre mot de passe';
            break;
        case 'Nederlands':
            return _('Nederlands');
            break;
        case 'Frans':
            return _('Frans');
            break;
        case 'Duits':
            return _('Duits');
            break;
        case 'Engels':
            return _('Engels');
            break;
        case 'mail':
            return _('mail');
            break;
        case 'telefoon':
            return _('telefoon');
            break;
        case 'sms':
            return _('sms');
            break;
        case 'post':
            return _('post');
            break;
        case 'intercommunale':
            return _('intercommunale');
            break;
        case 'gemeente':
            return _('gemeente');
            break;
        case 'kantoor/administratie/bedrijf':
            return _('kantoor/administratie/bedrijf');
            break;
        case 'organisatie':
            return _('organisatie');
            break;
        case 'winkel':
            return _('winkel');
            break;
        case 'school/universiteit':
            return _('school/universiteit');
            break;
        case 'particulier':
            return _('particulier');
            break;
        case 'horeca':
            return _('horeca');
            break;
        case 'maandelijks':
            return _('maandelijks');
            break;
        case 'per kwartaal':
            return _('per kwartaal');
            break;
        case 'jaarlijks':
            return _('jaarlijks');
            break;
	}

	return $translated_text;
}

add_filter('gettext','pippin_gettext_demo',20,3);

function translate_title( $title, $id = null ) {
    global $MYRECY_locale;
    global $user_ID;

    // translating Theme My Login plugin
	switch($title)
	{
        case 'Log Out':
            // wis deze switch en laat gettext het oplossen!!!
            /*
            switch($MYRECY_locale)
            {
                case 'fr_BE':
                    return 'Déconnecter';
                    break;
                default:
                    return 'Afmelden';
                    break;
            }*/

            // met gettext:
            return _('Afmelden');
            break;
        case 'Log In':
            return "Log in / Connection"; // gettext is not useful as the locale is not known at this point.
            break;
        case 'Lost Password':
            return 'Wachtwoord verloren / Mot de passe oublié';
            break;
        case 'Lost Password':
            return 'Wachtwoord verloren / Mot de passe oublié';
            break;
        case 'Welkom bij MyRecy':
            return _('Welkom bij MyRecy');
            break;
        case 'Profiel':
            return _('Profiel');
            break;
        case 'Ophaalhistoriek':
            return _('Ophaalhistoriek');
            break;
        case 'Stock':
            return _('Stock');
            break;
        case 'Contact':
            return _('Contact');
            break;
        case 'Wachtwoord wijzigen':
            return _('Wachtwoord wijzigen');
            break;
    }

    return $title;
}

add_filter( 'the_title', 'translate_title', 10, 2 );

/* redirect users to front page after login */
//    code not working for WP4? outdated?
//    https://wordpress.org/support/topic/how-can-i-redirect-users-to-the-front-page-after-log-in
