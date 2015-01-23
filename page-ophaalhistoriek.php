<?php
require_once("myrecy-func.php");

get_header(); the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">
	<?php
		require_once("secure/db.php");

        // ipv uit de DB te halen (table FREQUENTIE), hier de drie mogelijkheden:
        $frequentie_maandelijks = 1;
        $frequentie_trimester = 2;
        $frequentie_jaarlijks = 3;

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
    <h3>Materiaal</h3>
    <?php
            if($ophaalpunt_from_db->kurk == 1)
            { ?>
    <input type="checkbox" name="toon_kurk" value="1" > kurk<br/>
    <?php
            }
            else
            { ?>
    <input class="ophaalhistoriek-disabled" type="checkbox" name="toon_kurk" value="0" disabled > kurk </input> (gedesactiveerd want in uw profiel staat dat er bij u geen kurk wordt opgehaald).<br/>
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
		echo "<p>Ophaalpunt $ophaalpunt_from_db->naam haalt kurk op (<b>$ophaalpunt_from_db->kurk</b>) parafine (<b>$ophaalpunt_from_db->parafine</b>)."
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
	
<?php get_footer() ?>
