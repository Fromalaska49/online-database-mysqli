<?php
require('connect.php');
require('session_handler.php');
$tables_row_result=mysqli_query($db, "SELECT * FROM `tables`");
while($tables_row=mysqli_fetch_array($tables_row_result)){
	$table_name=$tables_row['name'];
	$escaped_table_name=mysqli_real_escape_string($db, $table_name);
	$table_result = mysqli_query($db, "SELECT * FROM `".$escaped_table_name."`");
	$num_fields=mysqli_num_fields($table_result);
	$table_id_result=mysqli_query($db, "SELECT * FROM `tables` WHERE name='$escaped_table_name'");
	$table_id_row = mysqli_fetch_array($db, $table_id_result);
	$escaped_table_id = (int) $table_id_row['id'];
	//begin table
	
	
	//th
	$field_name=array();
	$field_name[]=$num_fields;
	$escaped_field_name=array();
	$escaped_field_name[]=$num_fields;
	for($index=0;$index<$num_fields;$index++){
		$field_name[$index] = mysqli_fetch_field($table_result,$index)->name;
		$escaped_field_name[$index] = mysqli_real_escape_string($db, $field_name[$index]);
	}
	
	//end th
	
	
	$escaped_r=1;
	while($row=mysqli_fetch_array($table_result)){
		//tr
		//$company_name=$row['Company'];
		//echo('$company_name='.$company_name.'<br />');
		//$escaped_company_name=mysql_real_escape_string($company_name);
		$escaped_record_id=(int)$row['id'];
		for($index=0;$index<$num_fields;$index++){
			//td
			if(strlen($row[$index])>0){
				$description=$row[$index];
				$description=strtolower($description);
				$maxlen=strlen($description);
				if($maxlen>122){
					//fit description into (128 character string) - 2*(elipse i.e. the "..." at the beginning and end)
					$maxlen=122;
				}
				$escaped_keyword=mysqli_real_escape_string($db, $description);
				$cropped_description=substr($description,0,$maxlen);
				$escaped_cropped_description = mysqli_real_escape_string($db, $cropped_description);
				//echo('$escaped_field_name[$index]='.$escaped_field_name[$index].'<br />');
				$sql="INSERT INTO `search_rank` (`r`,`keyword`,`table_id`,`record`,`field`,`field_name`,`company_name`,`description`) VALUES ('".$escaped_r."','".$escaped_keyword."','".$escaped_table_id."','".$escaped_record_id."','".$index."','".$escaped_field_name[$index]."','".$escaped_table_name."','".$escaped_cropped_description."')";
				//echo($sql.'<br />');
				mysqli_query($db, $sql);
			}
			//end td
		}
		//end tr
	}
	//end table
}
?>


