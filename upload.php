<?php
$result='';
if(!empty($_FILES)) {
  $filename = basename($_FILES['Filedata']['name']);

 $ext = substr($filename, strrpos($filename, '.') + 1);  
 $ext = strtolower($ext);
  if (($_FILES["Filedata"]["size"] < 4000000)) {//4MB
   $largename = '';  
   $imageSize = '';
   $imgPath = '';
   if(isset($_REQUEST["tablePref"])) $table_prefix = addslashes($_REQUEST["tablePref"]);
  	if(isset($_REQUEST['target']) && isset($_REQUEST['fID'])){
  		if($_REQUEST['target'] == 'facility'){
  			$largename = 'images/icons/'.$filename;
  			$imgPath = 'images/icons/';
  			$imageSize = 25;
  			include "../../../wp-config.php";
  			
  			$db_host = DB_HOST;
			$db_host = explode(":", $db_host);
			if( !empty( $db_host ) ) $db_host = $db_host[0];
			else $db_host = DB_HOST;
			
			$db = new mysqli($db_host, DB_USER, DB_PASSWORD, DB_NAME);
			// Test the connection:
			if (mysqli_connect_errno()){
    		// Connection Error
    			exit("Couldn't connect to the database: ".mysqli_connect_error());
			}
			//****remove old image if exists****

			$oldImgQ = mysqli_query($db, "select image from ".$table_prefix."eb_facilities where facility_id = ".$_REQUEST['fID']) or die(mysqli_error($db));

			$oldImage = mysqli_fetch_row($oldImgQ);
			
			$imgExistInDB_Q = mysqli_query($db, "select image from ".$table_prefix."eb_facilities where image = '".$filename."'") or die(mysqli_error($db));

			$imgExistInDB = mysqli_fetch_row($imgExistInDB_Q);
			if($oldImage[0] != '' && $imgExistInDB[0] != $filename && ($ext=='jpeg' || $ext=='jpg' || $ext=='png' || $ext=='gif'))
				unlink("images/icons/".$oldImage[0]);
			if($imgExistInDB[0] != $filename && ($ext=='jpeg' || $ext=='jpg' || $ext=='png' || $ext=='gif'))
			mysqli_query($db, "update ".$table_prefix."eb_facilities SET image='".$filename."' where facility_id = ".$_REQUEST['fID']) or die(mysqli_error($db));
			
			mysqli_free_result($oldImgQ);
			mysqli_free_result($imgExistInDB_Q);
			mysqli_close($db);
  		}//end if target=facility
  	}//end if isset target 
  	else{
	  $largename = $filename;
	  $imageSize = 1500;
	 }
		if(is_file("images/icons/".$filename)) $result=2;
		else {
   	if($ext=='jpeg' || $ext=='jpg' || $ext=='png'){
			if (!file_exists("images/icons/".$filename)) {//ayto den xreiazetai, douleyei me to is_file kalytera
				$imageKind = "small";
				//createThumb($_FILES['Filedata']['tmp_name'], $smallname, $max_thumb_dimension, $imageKind, $ext);
				$imageKind = "fixed_resize";
				createThumb($_FILES['Filedata']['tmp_name'], $largename, $imageSize, $imageKind, $ext);	
				$result=1;
			}
			else  $result=2;//Exist already
		}
		elseif($ext =='gif') {
			move_uploaded_file($_FILES["Filedata"]["tmp_name"],$imgPath . $_FILES["Filedata"]["name"]);
			$result=1.1;
		}
		else $result=3;//not supported type
		
		}  
	}
	else $result = 4;//To large
	
}
else $result = '4';//Empty


function createThumb($spath, $dpath, $maxd, $imgKind, $imgext) {
 if($imgext == "jpeg" || $imgext == "jpg"){
 	$src=@imagecreatefromjpeg($spath);
 }
  if($imgext == "png" ){
  	$src = @imagecreatefrompng($spath);
  }
 if (!$src) {return false;} else {
  $srcw=imagesx($src);
  $srch=imagesy($src);
  
  if ($imgKind != "small" && $imgKind != "fixed_resize")
  	 {$width=$srcw;
	 $height=$srch;}
  else{  
	  if ($srcw<$srch) {
	  $height=$maxd;
	  $width=floor($srcw*$height/$srch);
	  }
  	  else {
	  $width=$maxd;
	  $height=floor($srch*$width/$srcw);
	  }
	  if ($width>$srcw && $height>$srch) {$width=$srcw;$height=$srch;}  //An h eikona einai mikroterh apo to $maxd diathrei tis idies diastaseis gia na mhn aliwnetai..
  }  
  
  $thumb=imagecreatetruecolor($width, $height);
  imagecopyresized($thumb, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));
  if($imgext == "jpeg" || $imgext == "jpg"){
  	imagejpeg($thumb, $dpath);
  }
  if($imgext == "png" ){
  	imagepng($thumb, $dpath);
  }
  /*if($imgext == "png" ){
  	imagepng($thumb, $dpath);
  }*/
  return true;
 }
}
//----
?>

<script language="javascript" type="text/javascript">
	var uploadresult = "<?php if ($result == 1) echo '1|'.$filename; else echo $result.'|error'?>";
   window.top.window.stopUpload(uploadresult);
</script>   
 