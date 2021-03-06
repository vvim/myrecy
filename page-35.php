<?php 
require_once("myrecy-func.php");

get_header(); the_post(); ?>

<div <?php post_class('post') ?>>
	<h1 class="entry-title noinfo"><?php the_title(); ?></h1>
	
	<div class="content">

		<div id="info">
            <p><?php echo _('<b>MyRecy</b> is de gepersonaliseerde website voor ophaalpunten van <b>kurk</b> en/of <b>kaarsresten</b>.'); ?></p>
            <p><?php echo _('U kan door deze website navigeren via het menu dat u bovenaan deze pagina vindt:'); ?></p>
            <ul>
                <li><?php echo sprintf(_('indien u uw persoonlijke gegevens wilt nakijken of aanpassen, ga dan naar %s;'),'<a title="'._('Profiel').'" href="'.home_url().'/profiel-ophaalpunt/">'._('Profiel').'</a>'); ?></li>
                <li><?php echo sprintf(_('om door te geven hoeveel zakken kurk en/of kaarsresten u momenteel in stock heeft, klikt u op %s;'),'<a title="'._('Stock').'" href="'.home_url().'/stock/">'._('Stock').'</a>'); ?></li>
                <li><?php echo sprintf(_('als u een overzicht wenst van de eigen ingezamelde/opgehaalde hoeveelheden (en eventueel attesten) : %s;'),'<a title="'._('Historiek').'" href="'.home_url().'/ophaalhistoriek/">'._('Historiek').'</a>'); ?></li>
                <li><?php echo sprintf(_('u kan ook De Vlaspit rechtstreeks contacteren via het %s;'),'<a title="'._('Contactformulier').'" href="'.home_url().'/contact/">'._('Contactformulier').'</a>'); ?></li>
                <li><?php echo sprintf(_('wanneer u klaar bent, klikt u op %s.'),'<a title="'._('Afmelden').'" href="'.home_url().'/logout/">'._('Afmelden').'</a>'); ?></li>
            </ul>
        </div>
    </div>
</div>
<?php get_footer() ?>
