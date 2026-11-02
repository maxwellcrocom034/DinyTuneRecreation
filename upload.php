<?php
require __DIR__.'/config.php'; require_login(); $error='';$ok='';
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
 $title=trim($_POST['title']??'');$description=trim($_POST['description']??'');$file=$_FILES['video']??null;
 if($title==='')$error='Enter a title.';
 elseif(!$file||$file['error']!==UPLOAD_ERR_OK)$error='Choose a video file to upload.';
 elseif($file['size']>500*1024*1024)$error='Maximum upload size is 500 MB.';
 else {
  $finfo=new finfo(FILEINFO_MIME_TYPE);$mime=$finfo->file($file['tmp_name']);
  $allowed=['video/mp4'=>'mp4','video/webm'=>'webm','video/quicktime'=>'mov','video/x-msvideo'=>'avi','video/x-flv'=>'flv','video/mpeg'=>'mpeg'];
  if(!isset($allowed[$mime]))$error='Unsupported video format. Try MP4, WebM, MOV, AVI, FLV or MPEG.';
  else {
   $id=bin2hex(random_bytes(12));$input=UPLOAD_DIR.'/'.$id.'.'.$allowed[$mime];$output=UPLOAD_DIR.'/'.$id.'.mp4';$thumb=$id.'.jpg';
   if(!move_uploaded_file($file['tmp_name'],$input))$error='Could not save uploaded file.';
   else {
    $converted=($allowed[$mime]==='mp4');
    if($converted){$final=$input;$format='mp4';} else {$final=$output;$format='mp4';if(!transcode_video($input,$output)){$error='FFmpeg conversion failed. Check that FFmpeg is installed and executable.';$final='';}}
    if(!$error){if($final!==$input&&is_file($input))@unlink($input);make_thumbnail($final,THUMB_DIR.'/'.$thumb);$q=db()->prepare('INSERT INTO videos(user_id,title,description,filename,thumbnail,format) VALUES(?,?,?,?,?,?)');$q->execute([current_user()['id'],$title,$description,basename($final),$thumb,$format]);redirect('watch.php?id='.db()->lastInsertId());}
   }
  }
 }
}
require __DIR__.'/includes/header.php'; ?>
<div class="form-box"><h3>Upload Videos</h3><p>MP4 is recommended. Conversion and thumbnails require FFmpeg on the server.</p><?php if($error):?><p class="error"><?=h($error)?></p><?php endif;?>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
<div><label for="title">Title:</label><input id="title" name="title" maxlength="150" required></div>
<div><label for="description">Description:</label><textarea id="description" name="description" rows="4"></textarea></div>
<div><label for="video">Video:</label><input id="video" type="file" name="video" accept="video/*" required></div><button type="submit">Upload Video</button></form></div>
<?php require __DIR__.'/includes/footer.php'; ?>
