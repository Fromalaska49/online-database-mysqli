<?php
require('session_handler.php');
require('connect.php');
$sanitized_uid = (int) $_SESSION['uid'];
$table_name = rawurldecode($_GET['table_name']);
$sanitized_table_name = mysqli_real_escape_string($db, $table_name);
if(isset($_POST['record_id']) && isset($_POST['new_record_id'])){
	if(mysqli_num_rows(mysqli_query($db, "SELECT * FROM `table_permissions` INNER JOIN `tables` ON `table_permissions`.`table_id`=`tables`.`id` WHERE `table_permissions`.`uid`='$sanitized_uid' AND `tables`.`name`='$sanitized_table_name' AND `write_access`>0"))>0){
		//user has permissions to write to table
		$old_id = $_POST['record_id'];
		$new_id = $_POST['new_record_id'];
		$sanitized_old_id = (int) $old_id;
		$sanitized_new_id = (int) $new_id;
		if($new_id > $old_id){
			mysqli_query($db, "START TRANSACTION;");
			mysqli_query($db, "UPDATE `$sanitized_table_name` SET `id`=`id`+1 WHERE `id`>$sanitized_new_id;");
			mysqli_query($db, "UPDATE `$sanitized_table_name` SET `id`=$sanitized_new_id+1 WHERE `id`=$sanitized_old_id");
			mysqli_query($db, "UPDATE `$sanitized_table_name` SET `id`=`id`-1 WHERE `id`>$sanitized_old_id");
			mysqli_query($db, "COMMIT;");
		}
		else if($new_id < $old_id){
			mysqli_query($db, "START TRANSACTION;");
			mysqli_query($db, "UPDATE `$sanitized_table_name` SET `id`=`id`+1 WHERE `id`>=$sanitized_new_id;");
			mysqli_query($db, "UPDATE `$sanitized_table_name` SET `id`=$sanitized_new_id WHERE `id`=$sanitized_old_id+1");
			mysqli_query($db, "UPDATE `$sanitized_table_name` SET `id`=`id`-1 WHERE `id`>$sanitized_old_id");
			mysqli_query($db, "COMMIT;");
		}
		else{
			//old and new record ids are equall
			//do nothing
		}
		//send user to location of null record so that they can enter data
		$page_size = 30;
		$num_records = mysqli_num_rows(mysqli_query($db, "SELECT `id` FROM `$sanitized_table_name`"));
		$page = floor($num_records/$page_size);
		$url_redirect = 'edit_table.php?table_name='.rawurlencode($table_name);//.'&result_page='.$page;
		header('Location: '.$url_redirect);
		echo('<script type="text/javascript">$(document).ready(function(){window.location.href=\''.$url_redirect.'\'});</script>');
	}
}
?>
