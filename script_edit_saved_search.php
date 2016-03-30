<?php
require('session_handler.php');
require('connect.php');
$sql_query;
if(isset($_GET['change'])&&isset($_GET['id'])){
	$sanitized_id=(int) $_GET['id'];
	$result=mysqli_query($db, "SELECT `authuid` FROM `saved_searches` WHERE `id`='$sanitized_id'");
	$row=mysqli_fetch_array($db, $result);
	if($row['authuid']==$_SESSION['uid']){
		//user is authorized to modify this search
		if($_GET['change']==2&&isset($_GET['text'])){
			//rename save
			$sanitized_name=mysqli_real_escape_string($db, $_GET['text']);
			if($_GET['text']==''){
				$sanitized_name=mysqli_real_escape_string($db, 'Untitled Search');
			}
			mysqli_query($db, "UPDATE `saved_searches` SET `name`='$sanitized_name' WHERE `id`='$sanitized_id'");
		}
		else if($_GET['change']==1){
			//delete save
			mysqli_query($db, "DELETE FROM `saved_searches` WHERE `id`='$sanitized_id'");
			mysqli_query($db, "DELETE FROM `saved_search_terms` WHERE `search_id`='$sanitized_id'");
		}
		else{
			//invalid change
		}
	}
	else{
		//user is not authorized to modify this save
	}
}
?>