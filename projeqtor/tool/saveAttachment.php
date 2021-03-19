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
scriptLog("saveAttachment.php");
header ('Content-Type: text/html; charset=UTF-8');

error_reporting(E_ERROR);
/** ===========================================================================
 * Save an attachment (file) : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

// ATTENTION, this PHP script returns its result into an iframe (the only way to submit a file)
// then the iframe returns the result to resultDiv to reproduce expected behaviour
$isIE=false;
if (array_key_exists('isIE',$_REQUEST)) {
  $isIE=$_REQUEST['isIE'];
} 
if ($isIE and $isIE<=9) {?>
<html>
<head>   
</head>
<body onload="parent.saveAttachmentAck();">
<?php } else { ob_start();}?>
<?php 
$error=false;
$type='file';
if (! array_key_exists('attachmentType',$_REQUEST)) {
    //$error=htmlGetErrorMessage('attachmentType parameter not found in REQUEST');
    //errorLog('attachmentType parameter not found in REQUEST');
    //$error=true;
} else {
  $type=$_REQUEST['attachmentType']; // compared against fixed values. (file|link).
}
$attachmentMaxSize=Parameter::getGlobalParameter('paramAttachmentMaxSize');
$uploadedFileArray=array();
if ($type=='file') {
  if (array_key_exists('attachmentFile',$_FILES)) {
    $uploadedFileArray[]=$_FILES['attachmentFile'];
  } else if (array_key_exists('uploadedfile0',$_FILES)) {
  	$cnt = 0;
  	while(isset($_FILES['uploadedfile'.$cnt])){
  		$uploadedFileArray[]=$_FILES['uploadedfile'.$cnt];
  		$cnt++;
  	}
  } else if (array_key_exists('attachmentFiles',$_FILES) and array_key_exists('name',$_FILES['attachmentFiles'])) {
    for ($i=0;$i<count($_FILES['attachmentFiles']['name']);$i++) {
    	$uf=array();
    	$uf['name']=$_FILES['attachmentFiles']['name'][$i];
    	$uf['type']=$_FILES['attachmentFiles']['type'][$i];
    	$uf['tmp_name']=$_FILES['attachmentFiles']['tmp_name'][$i];
    	$uf['error']=$_FILES['attachmentFiles']['error'][$i];
    	$uf['size']=$_FILES['attachmentFiles']['size'][$i];
      $uploadedFileArray[$i]=$uf;
    }
  } else {
    if (RequestHandler::getValue('uploadType')=='html5' and count($_FILES)==0) {
      $jsonReturn='{"file":"text",'
                  .'"name":"text",'
                  .'"type":"text",'
                  .'"size":"0"  ,'
                  .'"message":"text"}';
      echo $jsonReturn;
      exit;
    }
    $error=htmlGetErrorMessage(i18n('errorTooBigFile',array($attachmentMaxSize,'paramAttachmentMaxSize')));
    errorLog(i18n('errorTooBigFile',array($attachmentMaxSize,'paramAttachmentMaxSize')));
    //$error=true;
  }
  foreach ($uploadedFileArray as $uploadedFile) {
	  if (! $error) {
	    if ( $uploadedFile['error']!=0) {
	      //$error="[".$uploadedFile['error']."] ";
	      errorLog("[".$uploadedFile['error']."] saveAttachment.php");
	      //$error=true;
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
	      //$error=true;
	    }
	  }
  }
} else if ($type=='link') {
  if (! array_key_exists('attachmentLink',$_REQUEST)) {
    $error=htmlGetWarningMessage(i18n('attachmentLink parameter not found in REQUEST'));
    errorLog(i18n('attachmentLink parameter not found in REQUEST'));
    //$error=true;
  } else {
    $link=$_REQUEST['attachmentLink'];
    $link=Security::checkValidUrl($link);
	  if($link===false) {
	    $error=htmlGetWarningMessage(i18n('errorInvalidUrl'));
	  }
  }
  $uploadedFileArray[]="link";
} else {
  $error=htmlGetWarningMessage(i18n('error : unknown type '));
  errorLog(i18n('error : unknown type '.$type));
  traceHack("invalid type value - [$type]");
  exit;
  //$error=true;
}
$obj=null;
$refType="";
$refId="";

$obj=SqlElement::getCurrentObject(null,null,false,false);

if (! $error) {
  if (! array_key_exists('attachmentRefType',$_REQUEST)) {
  	if (!$obj) {
      $error=htmlGetErrorMessage('attachmentRefType parameter not found in REQUEST');
      errorLog('attachmentRefType parameter not found in REQUEST');
  	} else {
  		$refType=get_class($obj);
  	} 
  } else {
    $refType=$_REQUEST['attachmentRefType'];
	  Security::checkValidClass($refType);
  }
}
if ($refType=='TicketSimple') {
  $refType='Ticket';    
}

if (! $error) {
  if (array_key_exists('attachmentRefId',$_REQUEST)) { // Retrieve from request
    $refId=$_REQUEST['attachmentRefId'];
  }
  if (! $refId) { // Not set from request, retreive from current object
  	if (!$obj) {
      $error=htmlGetErrorMessage('attachmentRefId parameter not found in REQUEST');
      errorLog('attachmentRefId parameter not found in REQUEST');
  	} else {
  		$refId=$obj->id;
  	} 
  }
}

if ($refType=='User' or $refType=='Contact' or $refType=='ResourceTeam' ) {
  if ($refId==getCurrentUserId()) {
    // OK save Photo for current user
  } else {
    $userToUpdate=new $refType($refId);
    if (! Security::checkValidAccessForUser($userToUpdate,'update') ) {
      traceHack("Update photo on $refType $refId without rights to do this for current user ".getCurrentUserId());
    }
  }
  $refType='Resource'; // Attache to resource for Photo
}

if (! $error) {    
  if (! array_key_exists('attachmentDescription',$_REQUEST)) {
    $attachmentDescription="";
  } else {
    $attachmentDescription=$_REQUEST['attachmentDescription'];
  }
}
if (! array_key_exists('attachmentPrivacy',$_REQUEST)) {
  $idPrivacy=1;
} else  {
  $idPrivacy=$_REQUEST['attachmentPrivacy'];
}

$result="";
$user=getSessionUser();
Sql::beginTransaction();
$attachment=new Attachment();
foreach ($uploadedFileArray as $uploadedFile) {
  $attachment=new Attachment();
	if (! $error) {
		if ($refType=="Resource") {
			// To avoid dupplicate image (if 2 users save picture on same time)
	    $attachment->purge("refType='Resource' and refId=".$refId);
	  }
	  $attachment->refId=$refId;
	  $attachment->refType=$refType;
	  $attachment->idUser=$user->id;
	  $ress=new Resource($user->id);
	  $attachment->idTeam=$ress->idTeam;
		if ($idPrivacy) {
		  $attachment->idPrivacy=$idPrivacy;
		} else if (! $attachment->idPrivacy) {
		  $attachment->idPrivacy=1;
		}
	  $attachment->creationDate=date("Y-m-d H:i:s");
	  if ($type=='file') {
	    $attachment->fileName=trim($uploadedFile['name']);
	    $ext = strtolower ( pathinfo ( $attachment->fileName, PATHINFO_EXTENSION ) );
	    if (substr($ext,0,3)=='php' or substr($ext,0,3)=='pht' or substr($ext,0,3)=='sht' or $ext=='htaccess' or $ext=='htpasswd') {
	    	$attachment->fileName.=".projeqtor";
	    }
	    $attachment->mimeType=$uploadedFile['type'];
	    $attachment->fileSize=$uploadedFile['size'];
	  } else if ($type=='link') {
	    $attachment->link=$link;
	    $attachment->fileName=urldecode(basename($link));
	  }
	  $attachment->type=$type;
	  $attachment->description=$attachmentDescription;
	  $subResult=$attachment->save();
	  $newId=$attachment->id;
	  if (! $result) {
	  	$result=$subResult;
	  } else {
	  	$pos=strpos($result, '#');
	  	if ($pos) {
	  	  $result=substr_replace($result, '#'.$newId.', #', $pos,1);
	  	} 
	  } 
	} 
	$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
	$attachmentDirectory=Parameter::getGlobalParameter('paramAttachmentDirectory');
	if (! $error and $type=='file') {
	  $uploaddir = $attachmentDirectory . $pathSeparator . "attachment_" . $newId . $pathSeparator;
	  if (! file_exists($uploaddir)) {
	    mkdir($uploaddir,0777,true);
	  }
	  $paramFilenameCharset=Parameter::getGlobalParameter('filenameCharset');
	  if ($paramFilenameCharset) {
	  	$uploadfile = $uploaddir . iconv("UTF-8", $paramFilenameCharset.'//TRANSLIT//IGNORE',$attachment->fileName);
	  } else {
	    $uploadfile = $uploaddir . $attachment->fileName;
	  }
	  if ( ! move_uploaded_file($uploadedFile['tmp_name'], $uploadfile)) {
	     $error = htmlGetErrorMessage(i18n('errorUploadFile',array('hacking')));
	     errorLog(i18n('errorUploadFile','hacking ?'));
	     //$error=true;
	     $attachment->delete(); 
	  } else {
	    $attachment->subDirectory=str_replace(Parameter::getGlobalParameter('paramAttachmentDirectory'),'${attachmentDirectory}',$uploaddir);
	    $otherResult=$attachment->save();
	  }
	}
	
	if (! $error and $attachment->idPrivacy==1) { // send mail if new attachment is public
	  $elt=new $refType($refId);
		$mailResult=$elt->sendMailIfMailable(false,false,false,false,false,true,false,false,false,false,false,true);
		if ($mailResult) {
		  $pos=strpos($result,'<input type="hidden"');
		  if ($pos) {
  		  $result=substr($result, 0,$pos).' - ' . Mail::getResultMessage($mailResult).substr($result, $pos);
		  }
		}
		if ($refType=='Resource') { // Also Includes User and Contact thanks to line ~156 
          Affectable::generateThumbs($refType, intval($refId), $attachment->subDirectory.$attachment->fileName);
        }
	}
}
if (! $error) {
  // Message of correct saving
  $status = getLastOperationStatus ( $result );
  if ($status == "OK") {
    Sql::commitTransaction ();
  } else {
    Sql::rollbackTransaction ();
  }
  $message = '<div class="message' . $status . '" >' . $result . '</div>';
} else {
	Sql::rollbackTransaction();
	$message = $error;
	$attachment=new Attachment();
}
$jsonReturn='{"file":"'.htmlEncodeJson($attachment->fileName).'",'
 .'"name":"'.htmlEncodeJson($attachment->fileName).'",'
 .'"type":"'.$type.'",'
 .'"size":"'.htmlEncodeJson($attachment->fileSize).'"  ,'
 .'"message":"'.str_replace('"',"'",$message).'"}';

if ($isIE and $isIE<=9) {
	echo $message;
  echo '</body>';
  echo '</html>';
} else {
  $result=ob_get_clean();
  echo $jsonReturn;
}
?>