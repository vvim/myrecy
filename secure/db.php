<?php
	$MYRECY_DB_HOST =  "YOUR DB HOSTNAME"; // does _not_ have to be the same db as your WordPress db
	$MYRECY_DB_USER =  "YOUR DB USER";
	$MYRECY_DB_PASSWORD = "YOUR DB PASSWORD";
	$MYRECY_DB_NAME = "YOUR DB NAME";

	$MYRECY_mysqli = new mysqli($MYRECY_DB_HOST,$MYRECY_DB_USER,$MYRECY_DB_PASSWORD,$MYRECY_DB_NAME);

	/* check connection */
	if ($MYRECY_mysqli->connect_errno) {
	    printf("Connection to MyRecy database failed: %s\n", $MYRECY_mysqli->connect_error);
	    exit();
	}
	//else
	//	echo "myrecy-db connected , woohoo!";

	// set char-set
	if (!$MYRECY_mysqli->set_charset("utf8"))
	{
	    printf("Error loading character set utf8: %s\n", $MYRECY_mysqli->error);
	}
?>
