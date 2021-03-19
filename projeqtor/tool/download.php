<?php
use Composer\Autoload\includeFile;
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

/** ===========================================================================
 * Download a file descripbde in the correcponding object 
 * @param class=class of object containing file description
 * @param id = id of object
 * @param display = bolean (existence is enough) to enable display, either download is forced
 */

require_once "../tool/projeqtor.php";
// include_once '../external/mailParser/Parser.php';
// include_once '../external/mailParser/Contracts/CharsetManager.php';
// include_once '../external/mailParser/Contracts/Middleware.php';
// include_once'../external/mailParser/MimePart.php';
// include_once '../external/mailParser/Charset.php';
// include_once '../external/mailParser/Attachment.php';
// include_once '../external/mailParser/Middleware.php';
// include_once '../external/mailParser/MiddlewareStack.php';
// include_once '../external/mailParser/Exception.php';

//use PhpMimeMailParser;

scriptLog('   ->/tool/download.php');
$id=Security::checkValidId($_REQUEST['id']);
if ($_REQUEST['class']=='Logfile') { // Specific class that can be downloaded even if not a SqlElement
  $class='Logfile';
  $list=getSessionValue('logFilesList');
  $log=$list[$id];
  $id=$log['name'];
} else {
  $class=Security::checkValidClass($_REQUEST['class']);
}

$paramFilenameCharset=Parameter::getGlobalParameter('filenameCharset');

$obj=new $class($id);
$preserveFileName=Parameter::getGlobalParameter('preserveUploadedFileName');
if (!$preserveFileName) $preserveFileName="NO";

Security::checkValidAccessForUser($obj); // Enforce security for access to files

if ($class=='Attachment') {
  $path = str_replace('${attachmentDirectory}', Parameter::getGlobalParameter('paramAttachmentDirectory'), $obj->subDirectory);
  $name = $obj->fileName;
  if ($paramFilenameCharset) {
  	$name = iconv("UTF-8",$paramFilenameCharset.'//TRANSLIT//IGNORE',$name);
  }
  $size = $obj->fileSize;
  $type = $obj->mimeType;
  $file = $path . $name;
  if (! is_file($file)) {
    $file=addslashes($file);
  }
} else if ($class=='DocumentVersion') {
  $name = ($preserveFileName!="YES" and $obj->fullName and  pathinfo($obj->fullName, PATHINFO_EXTENSION)==pathinfo($obj->fileName, PATHINFO_EXTENSION))?$obj->fullName:$obj->fileName;
  $size = $obj->fileSize;
  $type = $obj->mimeType;
  $file = $obj->getUploadFileName();
} else if ($class=='Document') {
	if (!$obj->idDocumentVersion) return;
	$obj=new DocumentVersion($obj->idDocumentVersion);
	$name = ($preserveFileName!="YES" and $obj->fullName and  pathinfo($obj->fullName, PATHINFO_EXTENSION)==pathinfo($obj->fileName, PATHINFO_EXTENSION))?$obj->fullName:$obj->fileName;
  $size = $obj->fileSize;
  $type = $obj->mimeType;
  $file = $obj->getUploadFileName();
} else if ($class=='Logfile') {
  $name=$obj->name;
  $size=$obj->size;
  $type=$obj->type;
  $file=$obj->filePath;
}
$contentType="";
if(!isset($_REQUEST['nodl'])) {
  $contentType="application/force-download";
}
if ($type) {$contentType=$type;}
//if (array_key_exists('display',$_REQUEST)) {
//  $contentType=$type;
//}
if (substr($name, -10)=='.projeqtor') {
	$name=substr($name,0,strlen($name)-10);
} 
$name=str_replace(array("\n","\r"),array('',''),$name);
if (($file != "") && (file_exists($file))) { 
	header("Pragma: public"); 
  header("Content-Type: " . $contentType . "; name=\"" . $name . "\"");   
  header("Content-Transfer-Encoding: binary"); 
  header("Content-Length: $size"); 
  if (!array_key_exists('showHtml', $_REQUEST)) {
    //header("Content-Disposition: attachment; filename=\"" .$name . "\"");
    header("Content-Disposition: ".(!isset($_REQUEST['nodl'])?"attachment":"inline")."; filename=\"" .$name . "\"");
  } else {
    header("Content-Disposition: inline; filename=\"" . $name . "\"");
  }
  header("Expires: 0"); 
  header("Cache-Control: no-cache, must-revalidate");
  header("Cache-Control: private",false);
  header("Pragma: no-cache");
  if (ob_get_length()){   
    ob_clean();
  }
  flush();
  // gautier #3033
//   $findme   = '.msg';
//   $pos = strpos($name, $findme);
//   if($pos == true){
//     $fileName = $path.$name;
//     $Parser = new PhpMimeMailParser\Parser();
//     $Parser->setPath($fileName);
//      $to = $Parser->getHeader('to');
//      //$text = $Parser->getMessageBody('text');
//   }
  readfile($file);  
} else {
	errorLog("download.php : ".$file . ' not found');
}

?>