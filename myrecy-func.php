<?php

// TODO: Later there has to be a check which language the user speaks and adapt the locale accordingly
// check for WordPress language user dependent or bilingual
setlocale(LC_ALL, 'nl_NL');

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

?>
