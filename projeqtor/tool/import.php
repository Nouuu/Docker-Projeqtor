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
scriptLog('   ->/tool/import.php');
header ('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="shortcut icon" href="../view/img/logo.ico" type="../view/image/x-icon" />
  <link rel="icon" href="../view/img/logo.ico" type="../view/image/x-icon" />
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtor.css" />
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtorFlat.css" />
  <?php if (isNewGui()) {?><link rel="stylesheet" type="text/css" href="../view/css/projeqtorNew.css" /> <?php }?>
  <script type="text/javascript" src="../view/js/dynamicCss.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../view/js/projeqtorDialog.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <?php if (isNewGui()) {?>
  <script type="text/javascript">
    //var isNewGui=true;
    //setColorTheming('<?php echo '#'.Parameter::getUserParameter('newGuiThemeColor');?>','<?php echo '#'.Parameter::getUserParameter('newGuiThemeColorBis');?>');
  </script>
  <?php }?>
</head>

<body class="white <?php if (0 and isNewGui()) echo 'ProjeQtOrNewGui';?>" onLoad="window.top.hideWait();//showInfo('<?php echo i18n('ImportCompleted')?>');" style="overflow: auto; ">
<?php 
$class='';
$dateFormat='dd/mm/yyyy';

if (! array_key_exists('elementType',$_REQUEST)) {
	throwError('elementType parameter not found in REQUEST');
}
$elementType = $_REQUEST['elementType'];
Security::checkValidId($elementType); // elementType is id in Importable table

$class=SqlList::getNameFromId('Importable',$elementType,false);
Security::checkValidClass($class); 
///
/// Upload file
$error=false;
if (array_key_exists('importFile',$_FILES)) {
  $uploadedFile=$_FILES['importFile'];
} else {
  echo htmlGetErrorMessage(i18n('errorNotFoundFile'));
  errorLog(i18n('errorNotFoundFile'));
  exit;
}
$attachmentMaxSize=Parameter::getGlobalParameter('paramAttachmentMaxSize');
if ( $uploadedFile['error']!=0 ) {
  switch ($uploadedFile['error']) {
    case 1:
      echo htmlGetErrorMessage(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
      errorLog(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
      exit;
      break; 
    case 2:  	
      echo htmlGetErrorMessage(i18n('errorTooBigFile',array($attachmentMaxSize,'$paramAttachmentMaxSize')));
      errorLog(i18n('errorTooBigFile',array($attachmentMaxSize,'$paramAttachmentMaxSize')));
      exit;
      break;  
    case 4:
      echo htmlGetWarningMessage(i18n('errorNoFile'));
      errorLog(i18n('errorNoFile'));
      exit;
      break;  
    default:
      echo htmlGetErrorMessage(i18n('errorUploadFile',array($uploadedFile['error'])));
      errorLog(i18n('errorUploadFile',array($uploadedFile['error'])));
      exit;
      break;
  }
  }
if (! $uploadedFile['name']) {
  echo htmlGetWarningMessage(i18n('errorNoFile'));
  errorLog(i18n('errorNoFile'));
  $error=true; 
}
$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
$attachmentDirectory=Parameter::getGlobalParameter('paramAttachmentDirectory');
$uploaddir = $attachmentDirectory . $pathSeparator . "import" . $pathSeparator;
if (! file_exists($uploaddir)) {
  traceLog("must create import folder : ".$uploaddir);
  mkdir($uploaddir,0777,true);
}
$uploadfile = $uploaddir . basename($uploadedFile['name']);
if ( ! move_uploaded_file($uploadedFile['tmp_name'], $uploadfile)) {
   echo htmlGetErrorMessage(i18n('errorUploadFile',array('hacking')));
   errorLog(i18n('errorUploadFile',array('hacking')));
   exit; 
}
$ext = strtolower ( pathinfo ( $uploadfile, PATHINFO_EXTENSION ) );
if ($ext!='csv' and $ext!='xlsx') {
   echo htmlGetErrorMessage(i18n('msgInvalidFileFormat',array('csv,xlsx')));
   errorLog(i18n('msgInvalidFileFormat',array('csv,xlsx')));
   kill($uploadfile);
   if (substr($ext,0,3)=='php' or substr($ext,0,3)=='pht' or substr($ext,0,3)=='sht' or $ext=='htaccess' or $ext=='htpasswd') {
     traceHack("Try to upload non image file as image in CKEditor");
   }
   exit; 
}
//// V2.6 : extracted the import function to Importable class to use it from Cron
$result=Importable::import($uploadfile, $class);

echo Importable::$importResult;
//echo $result;
?>
</body>
</html>