<?php
 
  if(MYRECY_Administrator())
  {
?>
     <div id="frame-bottom">
        <div class="frame-spacer">
            <div id="frame-bottom-share">
                <a href="<?php echo home_url();?>/beheer/"><img class="right" src="http://www.devlaspit.be/Content/Images/x011.gif" alt="Key"/></a>                
            </div>
        </div>
    </div>

<?php
  }

  require_once('origami-footer.php');

?>
