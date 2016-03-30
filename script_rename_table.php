<?php
require('session_handler.php');
require('connect.php');
$sql_query;
$table_name='';
if(isset($_GET['table_name'])){
	$table_name=rawurldecode($_GET['table_name']);
	$sanitized_table_name=mysqli_real_escape_string($db, $table_name);
	$sanitized_uid=(int)$_SESSION['uid'];
	$sql = "SELECT `admin_access` FROM `table_permissions` INNER JOIN `tables` ON `table_permissions`.`table_id`=`tables`.`id` WHERE `table_permissions`.`uid`='$sanitized_uid' AND `tables`.`name`='$sanitized_table_name'";
	$result=mysqli_query($db, $sql);
	$row=mysqli_fetch_array($result);
	if($row['admin_access']==1){
		$new_table_name=rawurldecode($_GET['new_table_name']);
		$sanitized_new_table_name=mysqli_real_escape_string($db, $new_table_name);
		$num_tables_result=mysqli_fetch_array(mysqli_query($db, "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'mainall' AND table_name = '$sanitized_new_table_name'"));
		if($num_tables_result['COUNT(*)']>0){
			//duplicate table name
			/*
			$duplicate_table_number=1;
			do{
				$sanitized_new_table_name=mysql_real_escape_string($new_table_name.' '.$duplicate_table_number);
				$duplicate_table_number++;
				$num_tables_result=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'mainall' AND table_name = '$sanitized_new_table_name'"));
				$num_tables=$num_tables_result['COUNT(*)'];
			}while(!($num_tables==0));
			*/
			echo('<script type="text/javascript">alert("Could not rename: A table with that name already exists.");window.history.back();</script>');
			die();//Table already exists
		}
		$name_row = mysqli_fetch_array(mysqli_query($db, "SELECT `fname`,`lname` FROM `users` WHERE `uid`='$sanitized_uid'"));
		$user_name = $name_row['fname'] . ' ' . $name_row['lname'];
		unset($name_row);
		$sanitized_description = mysqli_real_escape_string($db, $user_name.' renamed the table "'.$table_name.'" to "'.$new_table_name.'"');
		unset($user_name);
		$sanitized_time = (int) time();
		$sanitized_action_id = 1;
		mysqli_query($db, "INSERT INTO `table_edit_log` (`time`,`uid`,`action_id`,`target_table_id`,`original_value`,`new_value`,`description`) VALUES ('$sanitized_time','$sanitized_uid','$sanitized_action_id','$sanitized_target_table_id','$sanitized_table_name','$sanitized_new_table_name','$sanitized_description')");
		mysqli_query($db, "UPDATE `tables` SET `name`='$sanitized_new_table_name' WHERE `name`='$sanitized_table_name'") or error_log(mysqli_error());
		mysqli_query($db, "RENAME TABLE `$sanitized_table_name` TO `$sanitized_new_table_name`") or error_log(mysqli_error());
		header('Location: manage_table.php?table_name='.rawurlencode($new_table_name));
		//echo url redirect for ajax response
		//echo('manage_table.php?table_name='.rawurlencode($new_table_name));
	}
	else{
		//user is not authorized to modify this table
	}
}
else{
	//!isset($_GET['table_name']);
}
?>