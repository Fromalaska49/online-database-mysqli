<?php
session_start();
require('connect.php');
$key=$_POST['key'];
$sanitized_key=mysqli_real_escape_string($db, $key);
echo("SELECT * FROM `pw_reset_requests` WHERE `activation_key`='$sanitized_key' LIMIT 1");
$result = mysqli_query($db, "SELECT * FROM `pw_reset_requests` WHERE `activation_key`='$sanitized_key' LIMIT 1") or die(mysqli_error());
if(mysqli_num_rows($db, $result)==1){
	$request_data = mysqli_fetch_array($result);
	unset($result);
	$sanitized_email = mysqli_real_escape_string($db, $request_data['email']);
	$sanitized_password = mysqli_real_escape_string($db, md5($_POST['password']));
	mysqli_query($db, "UPDATE `users` SET `password`='$sanitized_password' WHERE `email`='$sanitized_email'");
	sleep(1);
	$result=mysqli_query($db, "SELECT * FROM `users` WHERE `email`='$sanitized_email' AND `password`='$sanitized_password'") or die(mysqli_error());
	if(mysqli_num_rows($result)>0){
		mysql("DELETE FROM `pw_reset_requests` WHERE `activation_key`='$sanitized_key' OR `email`='$sanitized_email'");
		$row=mysqli_fetch_array($result);
		$_SESSION['uid']=$row['uid'];
		$_SESSION['email']=$row['email'];
		$_SESSION['fname']=$row['fname'];
		$_SESSION['lname']=$row['lname'];
		$redirect_url='http://'.$_SERVER['HTTP_HOST'].'/home.php';
		header('Location: '.$redirect_url);
		//echo('<script type="text/javascript">window.location.replace("'.$redirect_url.'");</script>');
	}
}
else{
	
}
mysqli_query($db, "DELETE FROM `pw_reset_requests` WHERE `activation_key`='$sanitized_key'");
?>