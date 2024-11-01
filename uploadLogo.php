<?php
if(!empty($_FILES)) {
	
	
	$filename = basename($_FILES['Filedata']['name']);
	$ext = substr($filename, strrpos($filename, '.') + 1);  
 	$ext = strtolower($ext);
 	if($ext=='jpeg' || $ext=='jpg' || $ext=='png' || $ext=='gif'){

 		if (($_FILES["Filedata"]["size"] < 4000000)) {//4MB
 			include "../../../wp-config.php";

			$db_host = DB_HOST;
			$db_host = explode(":", $db_host);
			if( !empty( $db_host ) ) $db_host = $db_host[0];
			else $db_host = DB_HOST;
			

 			$db = new mysqli($db_host, DB_USER, DB_PASSWORD, DB_NAME);
 			if (mysqli_connect_errno())exit("Couldn't connect to the database: ".mysqli_connect_error());
 		
 			if(isset($_REQUEST['target'])){
 				if($_REQUEST['target'] == "roomLogo") $imgPath = 'images/RoomImg/';		
 				if($_REQUEST['target'] == "businessLogo") $imgPath = 'images/businessImg/';		
		
	 			$imageName = $_REQUEST['bID'].'_'.time().'.'.$ext;
				$imageSize = 600;
				$imageKind = "fixed_resize";

				$ifHasLogoQ = mysqli_query($db, "select meta_value from ".$table_prefix."postmeta where post_id = ".$_REQUEST['bID']." and meta_key = 'eb_logo'") or die(mysqli_error($db));
				$oldImage = mysqli_fetch_row($ifHasLogoQ);
				if(!empty($oldImage)){
				
			$newLogoStr = $oldImage[0].'|'.$imageName;
					mysqli_query($db, "update ".$table_prefix."postmeta set meta_value = '".$newLogoStr."' where post_id = ".$_REQUEST['bID']." and meta_key='eb_logo'") or die(mysqli_error($db));
					
					//if(is_file($imgPath.$oldImage[0]))
					//unlink($imgPath.$oldImage[0]);	
					//if(is_file($imgPath.'thumbs/'.$oldImage[0]))
					//unlink($imgPath.'thumbs/'.$oldImage[0]);
					//echo '-----Old Image: ' . $oldImage[0];
					//mysqli_query($db, "delete from wp_postmeta where meta_key = 'eb_logo' and post_id = ".$_REQUEST['bID']." and meta_value = '".$oldImage[0]."'") or die(mysqli_error($db));
				}
				else{
					mysqli_query($db, "insert into ".$table_prefix."postmeta (meta_key, post_id, meta_value) values('eb_logo', ".$_REQUEST['bID'].", '".$imageName."')") or die(mysqli_error($db));
				}
				mysqli_free_result($ifHasLogoQ);
 				createThumb($_FILES['Filedata']['tmp_name'], $imgPath.$imageName, $imageSize, $imageKind, $ext);
 				createThumb($_FILES['Filedata']['tmp_name'], $imgPath.'thumbs/'.$imageName, 150, "small", $ext);
 				
 				$result = 1;
 			}
	 		
 			mysqli_close($db);
 		}
 		else $result = 4;//To large file
 	}
 	else $result = 3;//Bad extension
}
else $result= 5;//No file

/*

*/
function createThumb($spath, $dpath, $maxd, $imgKind, $imgext) {
 if($imgext == "jpeg" || $imgext == "jpg"){
 	$src=@imagecreatefromjpeg($spath);
 }
  if($imgext == "png" ){
  	$src = @imagecreatefrompng($spath);
  }
  if($imgext == "gif" ){
  	$src = @imagecreatefromgif($spath);
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
  if($imgext == "gif" ){
  	imagegif($thumb, $dpath);
  }
  return true;
 }
}
//----
?>

<script language="javascript" type="text/javascript">
	var uploadresult = "<?php if ($result == 1) echo '1|'.$imageName; else echo $result.'|error'?>";
	//var uploadresult = "test|Duude";
   window.top.window.stopUpload(uploadresult);
</script>   
 