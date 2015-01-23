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

// as taken from 'elviejo' at http://stackoverflow.com/questions/1449167/list-of-all-months-and-year-between-two-dates-in-php
function GetMonthsFromDate($myDate)
{
  $year = (int) date('Y',$myDate);
  $months = (int) date('m', $myDate);
  $dateAsMonths = 12*$year + $months;
  return $dateAsMonths;
}

// as taken from 'elviejo' at http://stackoverflow.com/questions/1449167/list-of-all-months-and-year-between-two-dates-in-php
function GetDateFromMonths($months)
{
  $years = (int) $months / 12;
  $month = (int) $months % 12;
  $myDate = strtotime("$years/$month/01"); //makes a date like 2009/12/01
  return $myDate;
}

?>
