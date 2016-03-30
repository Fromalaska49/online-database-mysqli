<?php
session_start();
require('connect.php');
$key=urldecode($_GET['key']);
$sanitized_key=mysqli_real_escape_string($db, $_GET['key']);
$sql="SELECT * FROM `preusers` WHERE `activation_key`='$sanitized_key'";
$result=mysqli_query($db, $sql) or die(mysqli_error());
if(mysqli_num_rows($result)==1){
	while($row=mysqli_fetch_array($result)){
		$sanitized_email=mysqli_real_escape_string($db, $row['email']);
		$sanitized_password=mysqli_real_escape_string($db, $row['password']);
		$sanitized_fname=mysqli_real_escape_string($db, $row['fname']);
		$sanitized_lname=mysqli_real_escape_string($db, $row['lname']);
		if(mysqli_query($db, "INSERT INTO `users` (`email`,`password`,`fname`,`lname`) VALUES ('$sanitized_email','$sanitized_password','$sanitized_fname','$sanitized_lname')")){
			$fetched_row=mysqli_fetch_array(mysqli_query($db, "SELECT * FROM `users` WHERE `email`='$sanitized_email'"));
			$_SESSION['uid']=$fetched_row['uid'];
			$_SESSION['email']=$fetched_row['email'];
			$_SESSION['fname']=$fetched_row['fname'];
			$_SESSION['lname']=$fetched_row['lname'];
			mysqli_query($db, "DELETE FROM `preusers` WHERE 'activation_key'='$sanitized_key'");
			
			$sanitized_uid=(int)$_SESSION['uid'];
			$tables_result=mysqli_query($db, "SELECT `id` FROM `tables`");
			while($tables_row=mysqli_fetch_array($tables_result)){
				$sanitized_table_id=(int)$tables_row['id'];
				mysqli_query($db, "INSERT INTO `table_permissions` (`uid`,`table_id`,`read_access`,`write_access`,`admin_access`) VALUES ('$sanitized_uid','$sanitized_table_id','0','0','0')");
			}
			mysqli_query($db, "INSERT INTO `user_permissions` (`uid`,`table_permissions`,`user_permissions`) VALUES ('$sanitized_uid','0','0')");
			
			$url_redirect='home.php';
			header('Location: '.$url_redirect);
		}
	}
}
else{
	echo('mysql_num_rows($result)='.mysqli_num_rows($result));
}
?>