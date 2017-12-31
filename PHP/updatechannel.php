<?php
    if(isset($_POST['newchannel']) && $_POST['newchannel']!="" )
    {
        /* dichiariamo alcune importanti variabili per collegarci al database */
        $connection = new PDO('mysql:host=localhost;dbname=DB', "user", "password");
        $DBtable = "NotificheUWP";

        if(isset($_POST['oldchannel']) && $_POST['oldchannel']!="")
        {
            $newchannel = $_POST['newchannel'];
            $oldchannel = $_POST['oldchannel'];
            $sql = "UPDATE $DBtable SET URL = '$newchannel' WHERE BINARY URL ='$oldchannel'";
        }
        else
        {
            $newchannel = $_POST['newchannel'];
            $time = date("Y-m-d H-i-s");
            $sql = "INSERT INTO $DBtable (URL,Registration) VALUES ('$newchannel','$time');";
        }
        $connection->exec($sql);
        $connection = NULL;
	    echo "Succeed";
    }
    else
    {
	    echo "Not succeed";
    } 
?>
