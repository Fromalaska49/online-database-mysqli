<?php
require('session_handler.php');
require('connect.php');
if(isset($_GET['table_name'])&&isset($_GET['field_name'])&&isset($_GET['new_field_name'])){
	$table_name = rawurldecode($_GET['table_name']);
	$sanitized_table_name = mysqli_real_escape_string($db, $table_name);
	$field_name = rawurldecode($_GET['field_name']);
	$sanitized_field_name = mysqli_real_escape_string($db, $field_name);
	$new_field_name = rawurldecode($_GET['new_field_name']);
	$sanitized_new_field_name = mysqli_real_escape_string($db, $new_field_name);
	$sanitized_uid = (int) $_SESSION['uid'];
	$sql="SELECT `admin_access` FROM `table_permissions` INNER JOIN `tables` ON `table_permissions`.`table_id`=`tables`.`id` WHERE `name`='$sanitized_table_name' AND `uid`=$sanitized_uid";
	$row = mysqli_fetch_array(mysqli_query($db, $sql));
	if($row['admin_access']==1&&$field_name!='id'){
		//authorized to add field
		unset($row);
		$row=mysqli_fetch_array(mysqli_query($db, "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = '$sanitized_table_name' AND COLUMN_NAME = '$sanitized_field_name'"));
		$sanitized_definition = mysqli_real_escape_string($db, $row['COLUMN_TYPE']);
		if(mysqli_query($db, "ALTER TABLE `$sanitized_table_name` CHANGE COLUMN `$sanitized_field_name` `$sanitized_new_field_name` $sanitized_definition")){
			$name_row = mysqli_fetch_array(mysqli_query($db, "SELECT `fname`,`lname` FROM `users` WHERE `uid`='$sanitized_uid'"));
			$user_name = $name_row['fname'] . ' ' . $name_row['lname'];
			unset($name_row);
			$sanitized_description = mysqli_real_escape_string($db, $user_name.' renamed the field "'.$field_name.'" to "'.$new_field_name.'" in table '.$table_name);
			unset($user_name);
			$sanitized_target_table_id = (int) mysqli_fetch_object(mysqli_query($db, "SELECT `id` FROM `tables` WHERE `name`='$sanitized_table_name'"));
			$sanitized_time = (int) time();
			$sanitized_action_id = 3;
			mysqli_query($db, "INSERT INTO `table_edit_log` (`time`,`uid`,`action_id`,`target_table_id`,`target_field_name`,`original_value`,`new_value`,`description`) VALUES ('$sanitized_time','$sanitized_uid','$sanitized_action_id','$sanitized_target_table_id','$sanitized_field_name','$sanitized_field_name','$sanitized_new_field_name','$sanitized_description')");
			
			header('Location: manage_table.php?table_name='.rawurlencode($table_name));
		}
		else{
			//Query failed, probably because the field already exists
			die('<script type="text/javascript">alert("Could not add field: A field with that name may already exist.");window.history.back();</script>');
		}
	}
	else{
		//user is not authorized to modify this save
		die('insufficient privileges\n');
	}
}
?>