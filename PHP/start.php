<?php
    //Create database connection
	$connection = new PDO('mysql:host=localhost;dbname=DB', "user", "password");

	$DeviceNotifyUrlArr= array();
	foreach($connection->query('SELECT * FROM NotificheUWP') as $row) {
        $DeviceNotifyUrlArr[] = $row['URL'];
    }

    require("wns.php");
 
    $wns= new Wns();//Create Wns class object
    
    $title = "Example";
    $message= "Hello World " . date("H:i:s d/m/Y");
    $xml_string = $wns->buildTileXml($title, $message);
	for($i=0; $i< count($DeviceNotifyUrlArr);$i++)
	{
		$response = $wns->sendWindowsNotification($DeviceNotifyUrlArr[$i], $xml_string);
		$response->ToString();
	}
    
?>