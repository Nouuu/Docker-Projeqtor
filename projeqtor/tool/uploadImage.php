<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

include_once "../tool/projeqtor.php";
header ('Content-Type: text/html; charset=UTF-8');
/** ===========================================================================
 * Save a document version (file) : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

ob_start();
$error=false;
$uploadedFile=false;
$targetDir=$targetDirImageUpload;
projeqtor_set_time_limit(3600); // 60mn
$attachmentMaxSize=Parameter::getGlobalParameter('paramAttachmentMaxSize');

if (array_key_exists('upload',$_FILES)) {
  if(!is_dir($targetDir)){
	  //Directory does not exist, so lets create it.
	  mkdir('../files/images');
  }
  $uploadedFile=$_FILES['upload'];
} else {
  $error=htmlGetErrorMessage(i18n('errorTooBigFile',array($attachmentMaxSize,'paramAttachmentMaxSize')));
  errorLog(i18n('errorTooBigFile',array($attachmentMaxSize,'paramAttachmentMaxSize')));
  //$error=true;
}

if (! $error) {
  if ( $uploadedFile['error']!=0) {
    errorLog("[".$uploadedFile['error']."] uploadImage.php");
    switch ($uploadedFile['error']) {
    	case 1:
    	  $error.=htmlGetErrorMessage("[".$uploadedFile['error']."] ".i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
    	  errorLog(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
    	  break;
    	case 2:
    	  $error.=htmlGetErrorMessage("[".$uploadedFile['error']."] ".i18n('errorTooBigFile',array($attachmentMaxSize,'paramAttachmentMaxSize')));
    	  errorLog(i18n('errorTooBigFile',array($attachmentMaxSize,'paramAttachmentMaxSize')));
    	  break;
    	case 4:
    	  $error.=htmlGetWarningMessage("[".$uploadedFile['error']."] ".i18n('errorNoFile'));
    	  errorLog(i18n('errorNoFile'));
    	  break;
    	case 3:
    	  $error.=htmlGetErrorMessage("[".$uploadedFile['error']."] ".i18n('errorUploadNotComplete'));
    	  errorLog(i18n('errorUploadNotComplete'));
    	  break;
    	default:
    	  $error.=htmlGetErrorMessage($error="[".$uploadedFile['error']."] ".i18n('errorUploadFile',array($uploadedFile['error'])));
    	  errorLog(i18n('errorUploadFile',array($uploadedFile['error'])));
    	  break;
    }
  }
}
if (! $error) {
  if (! $uploadedFile['name']) {
    $error=htmlGetWarningMessage(i18n('errorNoFile'));
    errorLog(i18n('errorNoFile'));
  }
}
$ext=strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
if (!$error) {
  $fileName=date('YmdHis').'_'.getSessionUser()->id.'_'.$uploadedFile['name'];
  $mimeType=$uploadedFile['type'];
  $fileSize=$uploadedFile['size'];
  $uploaddir = $targetDir;
  $paramFilenameCharset=Parameter::getGlobalParameter('filenameCharset');
  if ($paramFilenameCharset) {
    $uploadfile = $uploaddir . $pathSeparator . iconv("UTF-8", $paramFilenameCharset.'//TRANSLIT//IGNORE',$fileName);
  } else {
    $uploadfile = $uploaddir . $pathSeparator . $fileName;
  }
  $allowedExtensions=array('jpg','jpeg','gif','tiff','png','bmp','svg','ico');
  if (! in_array($ext,$allowedExtensions)) {
    if (substr($ext,0,3)=='php' or substr($ext,0,3)=='pht' or substr($ext,0,3)=='sht' or $ext=='htaccess' or $ext=='htpasswd') {
      traceHack("Try to upload non image file as image in CKEditor");
      exit;
    }
    $error=htmlGetWarningMessage(i18n('msgInvalidFileFormat',array(implode(',',$allowedExtensions))));
    errorLog(i18n('msgInvalidFileFormat',array(implode(',',$allowedExtensions))));
  } else {
    if ( ! move_uploaded_file($uploadedFile['tmp_name'], $uploadfile)) {
      $error = htmlGetErrorMessage(i18n('errorUploadFile',array('hacking')));
      errorLog(i18n('errorUploadFile',array('hacking')));
    } 
  }
}
if (!$error) {
  if(@!getimagesize($uploadfile)) {
    $error=i18n('errorNotAnImage');
    kill($uploadfile);
  }
}

$url="";
if ($error) {
  $jsonReturn='{"uploaded": 0, "error": { "message": "'.$error.'" } }';
} else {
  $url=$targetDir.'/'.$fileName;
  $jsonReturn='{"uploaded": 1, "filename": "'.$fileName.'", "url":"'.$url.'"}';
}

ob_end_clean();
if (isset($_GET['CKEditorFuncNum'])) { // Using image dialog
  $funcNum = $_GET['CKEditorFuncNum'] ;
  $message=$error;
  if ($error) echo '<div style="margin:0;padding:0;font-family:Arial, Verdana, sans-serif; font-size:12px; color:red;">'.$error.'</div>';
  echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";  
} else { // Using paste from clipboard or drag & drop
  echo $jsonReturn;
}

?>
