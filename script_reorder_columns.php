<?php
require('session_handler.php');
require('connect.php');
$table_name=$_GET['table_name'];
$sanitized_table_name=mysqli_real_escape_string($db, $table_name);
$sanitized_uid = (int) $_SESSION['uid'];
$log='At '.time().': ';
$size_of_GET=count($_GET);




function fix(&$target, $source, $keep = false) {                        
    if (!$source) {                                                            
        return;                                                                
    }                                                                          
    $keys = array();                                                           

    $source = preg_replace_callback(                                           
        '/                                                                     
        # Match at start of string or &                                        
        (?:^|(?<=&))                                                           
        # Exclude cases where the period is in brackets, e.g. foo[bar.blarg]
        [^=&\[]*                                                               
        # Affected cases: periods and spaces                                   
        (?:\.|%20)                                                             
        # Keep matching until assignment, next variable, end of string or   
        # start of an array                                                    
        [^=&\[]*                                                               
        /x',                                                                   
        function ($key) use (&$keys) {                                         
            $keys[] = $key = base64_encode(urldecode($key[0]));                
            return urlencode($key);                                            
        },                                                                     
    $source                                                                    
    );                                                                         

    if (!$keep) {                                                              
        $target = array();                                                     
    }                                                                          

    parse_str($source, $data);                                                 
    foreach ($data as $key => $val) {                                          
        // Only unprocess encoded keys                                      
        if (!in_array($key, $keys)) {                                          
            $target[$key] = $val;                                              
            continue;                                                          
        }                                                                      

        $key = base64_decode($key);                                            
        $target[$key] = $val;                                                  

        if ($keep) {                                                           
            // Keep a copy in the underscore key version                       
            $key = preg_replace('/(\.| )/', '_', $key);                        
            $target[$key] = $val;                                              
        }                                                                      
    }                                                                          
}      



fix($_GET, $_SERVER['QUERY_STRING']);


$field_name_array = array();
$i=0;
foreach ($_GET as $key){
	$field_name_array[$i]=$key;
	$i++;
}

$field_name_array=array_keys($_GET);
$sql="ALTER TABLE `$sanitized_table_name`";

$sanitized_field_name=mysqli_real_escape_string($db, $field_name_array[2]);
$sanitized_previous_field_name=mysqli_real_escape_string($db, $field_name_array[2-1]);
$row=mysqli_fetch_array(mysqli_query($db, "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = '$sanitized_table_name' AND COLUMN_NAME = '$sanitized_field_name'"));
$sanitized_definition=mysqli_real_escape_string($db, $row['COLUMN_TYPE']);
$sql.=" MODIFY `$sanitized_field_name` $sanitized_definition AFTER `$sanitized_previous_field_name`";
for($i=3;$i<$size_of_GET;$i++){
	$field_name = rawurldecode($field_name_array[$i]);
	$sanitized_field_name=mysqli_real_escape_string($db, $field_name);
	$previous_field_name = $field_name_array[$i-1];
	$sanitized_previous_field_name=mysqli_real_escape_string($db, $previous_field_name);
	$definition=mysqli_fetch_object(mysqli_query($db, "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = '$sanitized_table_name' AND COLUMN_NAME = '$sanitized_field_name'"))->COLUMN_TYPE;
	$sanitized_definition=mysqli_real_escape_string($db, $definition);
	if(strlen($definition)>0){
		//echo(htmlentities($definition).'<br />');
	}
	else{
		//echo("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = '$sanitized_table_name' AND COLUMN_NAME = '$sanitized_field_name'".'<br />');
	}
	$sql.=", MODIFY `$field_name` $definition AFTER `$previous_field_name`";//using unsanitized values due to mysql conflicts
	unset($row);
}
$name_row = mysqli_fetch_array(mysqli_query($db, "SELECT `fname`,`lname` FROM `users` WHERE `uid`='$sanitized_uid'"));
$user_name = $name_row['fname'] . ' ' . $name_row['lname'];
unset($name_row);
$sanitized_description = mysqli_real_escape_string($db, $user_name.' reordered the columns in "'.$table_name.'"');
unset($user_name);
$sanitized_target_table_id = (int) mysqli_fetch_object(mysqli_query($db, "SELECT `id` FROM `tables` WHERE `name`='$sanitized_table_name'"))->id;
$sanitized_time = (int) time();
$sanitized_action_id = 2;
mysqli_query($db, "INSERT INTO `table_edit_log` (`time`,`uid`,`action_id`,`target_table_id`,`description`) VALUES ('$sanitized_time','$sanitized_uid','$sanitized_action_id','$sanitized_target_table_id','$sanitized_description')");
if(mysqli_query($db, $sql)){
	echo('<script type="text/javascript">window.location.replace("manage_table.php?table_name='.rawurlencode($table_name).'");</script>');
}
else{
	die(mysqli_error().'<br /><br />'.$sql);
}
/*
$sanitized_entry=mysql_real_escape_string($log);
$sql="INSERT INTO `log` (`entry`) VALUES ('".$sanitized_entry."')";

mysql_query($sql);
*/
?>