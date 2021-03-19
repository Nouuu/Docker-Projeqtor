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
<body onload="parent.saveDocumentVersionAck();">
<?php } ?>
<?php 
$error=false;
$documentVersionLink="";
$uploadedFile=false;
projeqtor_set_time_limit(3600); // 60mn


$documentVersionId=null;
if (array_key_exists('documentVersionId',$_REQUEST)) {
    $documentVersionId=$_REQUEST['documentVersionId'];
}
$attachmentMaxSize=Parameter::getGlobalParameter('paramAttachmentMaxSize');
if (! $documentVersionId) { // Get file only on insert
	if (array_key_exists('documentVersionLink',$_REQUEST)) {
		$documentVersionLink=$_REQUEST['documentVersionLink'];
	}
	if (array_key_exists('documentVersionFile',$_FILES)) {
	  $uploadedFile=$_FILES['documentVersionFile'];
	} else if ($documentVersionLink!='') {
		// OK Link instead of file
	} else if (isset($_REQUEST['MAX_FILE_SIZE']) ) {
    // OK : no file	
	} else {
	  //$error=htmlGetErrorMessage(i18n('errorTooBigFile',array($attachmentMaxSize,'$paramAttachmentMaxSize')));
	  //errorLog("[1] ".i18n('errorTooBigFile',array($attachmentMaxSize,'$paramAttachmentMaxSize')));
	  $error=htmlGetErrorMessage(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
	  errorLog("[1] ".i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
	  //$error=true; 
	}
	if ($uploadedFile and $documentVersionLink and $uploadedFile['name']) {
		$error=htmlGetWarningMessage(i18n('errorFileOrLink',null));
		//$error=true; 
	}
	if (! $error and $uploadedFile and $uploadedFile['name']) {
	  if ( $uploadedFile['error']!=0 ) {
	  	//$error="[".$uploadedFile['error']."] ";
      errorLog("[".$uploadedFile['error']."] saveDocumentVersion.php");
	    switch ($uploadedFile['error']) {
	      case 1:
	        $error.=htmlGetErrorMessage(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
	        errorLog("[2] ".i18n("[".$uploadedFile['error']."] ".'errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
	        break; 
	      case 2:
	        $error.=htmlGetErrorMessage(i18n('errorTooBigFile',array($attachmentMaxSize,'$paramAttachmentMaxSize')));
	        errorLog("[3] ".i18n("[".$uploadedFile['error']."] ".'errorTooBigFile',array($attachmentMaxSize,'$paramAttachmentMaxSize')));
	        break;  
	      case 4:
	        $error.=htmlGetWarningMessage(i18n('errorNoFile'));
	        errorLog(i18n('errorNoFile'));
	        break;  
	      default:
	        $error.=htmlGetErrorMessage("[".$uploadedFile['error']."] ".i18n('errorUploadFile',array($uploadedFile['error'])));
	        errorLog(i18n('errorUploadFile',array($uploadedFile['error'])));
	        break;
	    }
	    //$error=true; 
	  }
	}
	if ( (! $error and ! $uploadedFile and !$documentVersionLink) or ( $uploadedFile and !$uploadedFile['name']) ) {
	    $error=htmlGetWarningMessage(i18n('errorNoFile'));
	    errorLog(i18n('errorNoFile'));
	    //$error=true; 
	}
}
$documentVersionNewVersion=null;
if (!$error) {
	if (! array_key_exists('documentVersionNewVersion',$_REQUEST)) {
	    $error=htmlGetErrorMessage('documentVersionNewVersion parameter not found in REQUEST');
	    //$error=true;
	} else {
	  $documentVersionNewVersion=$_REQUEST['documentVersionNewVersion'];
  }
}

$documentVersionNewRevision=null;
if (!$error) {
	if (! array_key_exists('documentVersionNewRevision',$_REQUEST)) {
	    $error=htmlGetErrorMessage('documentVersionNewRevision parameter not found in REQUEST');
	    //$error=true;
	} else {
	  $documentVersionNewRevision=$_REQUEST['documentVersionNewRevision'];
	}
}

$documentVersionNewDraft=null;
if (!$error) {
	if (! array_key_exists('documentVersionNewDraft',$_REQUEST)) {
	    $error=htmlGetErrorMessage('documentVersionNewDraft parameter not found in REQUEST');
	    //$error=true;
	} else {
	  $documentVersionNewDraft=$_REQUEST['documentVersionNewDraft'];
	}
}

$documentId=null;
if (!$error) {
	if (! array_key_exists('documentId',$_REQUEST)) {
	    $error=htmlGetErrorMessage('documentId parameter not found in REQUEST');
	    //$error=true;
	} else {
	  $documentId=$_REQUEST['documentId'];
	}
}

Sql::beginTransaction();
// Verify that user has rights to update the document
$doc=new Document($documentId);
//damian
$documentLocked = $doc->locked;
if($doc->idLocker == $user->id){
  $documentLocked = 0;
}
if (securityGetAccessRightYesNo('menuDocument', 'update', $doc)!="YES" or $documentLocked == 1) {
  $error=htmlGetWarningMessage(i18n('msgNotGranted'));
  //$error=true;
}

$documentVersionDate=null;
if (!$error) {
	if (! array_key_exists('documentVersionDate',$_REQUEST)) {
	    $error=htmlGetErrorMessage('documentVersionDate parameter not found in REQUEST');
	    //$error=true;
	} else {
	  $documentVersionDate=$_REQUEST['documentVersionDate'];
	}
}
$documentVersionIsRef=0;
if (array_key_exists('documentVersionIsRef',$_REQUEST)) {
  $documentVersionIsRef=1;
}

$documentVersionIdStatus=null;
if (!$error) {
	if (! array_key_exists('documentVersionIdStatus',$_REQUEST)) {
	    $error=htmlGetErrorMessage('documentVersionIdStatus parameter not found in REQUEST');
	    //$error=true;
	} else {
	  $documentVersionIdStatus=$_REQUEST['documentVersionIdStatus'];
	}
}

$documentVersionDescription=null;
if (!$error) {
	if (! array_key_exists('documentVersionDescription',$_REQUEST)) {
	    $error=htmlGetErrorMessage('documentVersionDescription parameter not found in REQUEST');
	    //$error=true;
	} else {
	  $documentVersionDescription=$_REQUEST['documentVersionDescription'];
	}
}

$documentVersionNewVersionDisplay=null;
if (!$error) {
	if (! array_key_exists('documentVersionNewVersionDisplay',$_REQUEST)) {
	    $error=htmlGetErrorMessage('documentVersionNewVersionDisplay parameter not found in REQUEST');
	    //$error=true;
	} else {
	  $documentVersionNewVersionDisplay=$_REQUEST['documentVersionNewVersionDisplay'];
	}
}

$invalidControl=false;
if (! $error) {
	$dv=new DocumentVersion($documentVersionId);
  $dv->idDocument=$documentId;
  $dv->idAuthor=$user->id;
  $dv->versionDate=$documentVersionDate;
  if (! $documentVersionId) {
	  if ($documentVersionLink) {
	  	
	  } else {
	    $dv->fileName=$uploadedFile['name'];
	    $dv->mimeType=$uploadedFile['type'];
	    $dv->fileSize=$uploadedFile['size'];
	  }
  }
  $dv->description=$documentVersionDescription;
  $dv->version=$documentVersionNewVersion;
  $dv->revision=$documentVersionNewRevision;
  $dv->draft=$documentVersionNewDraft;
  $dv->idStatus=$documentVersionIdStatus;
  $dv->isRef=$documentVersionIsRef;
  $dv->name=$documentVersionNewVersionDisplay;
  $result=$dv->save();
  if (getLastOperationStatus($result)=='INVALID') {
    $invalidControl=$result;
  }
  $newId= $dv->id;
}

$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
if (! $documentVersionId and ! $error and !$invalidControl) {
	if (! $error and !$documentVersionLink ) {
		$uploadfile = $dv->getUploadFileName();
		$split=explode($pathSeparator,$uploadfile);
		unset($split[count($split)-1]);
		$dir='';
		foreach ($split as $dirElt) { 
			$dir.=$dirElt.$pathSeparator;
	    if (! file_exists($dir)) {
	      mkdir($dir,0777,true);
	    }
		}
	  if ( ! move_uploaded_file($uploadedFile['tmp_name'], $uploadfile)) {
	     $error=htmlGetErrorMessage(i18n('errorUploadFile',array('hacking')));
	     errorLog(i18n('errorUploadFile',array('hacking')));
	     //$error=true;
	     $dv->delete(); 
	  } else {
	    //$dv->subDirectory=$uploaddir;
	    //$otherResult=$dv->save();
	  }
	}
}
if (! $error) {
  // Message of correct saving
  if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
  	Sql::rollbackTransaction();
    $message='<div class="messageERROR" >' . $result . '</div>';
  } else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
  	Sql::commitTransaction();
    $message='<div class="messageOK" >' . $result . '</div>';
  } else { 
  	Sql::rollbackTransaction();
    $message='<div class="messageWARNING" >' . $result . '</div>';
  }
} else {
	 Sql::rollbackTransaction();
	 $message=$error;
   //$message='<input type="hidden" id="lastSaveId" value="" />';
   //$message.='<input type="hidden" id="lastOperation" value="file upload" />';
   //$message.='<input type="hidden" id="lastOperationStatus" value="ERROR" />';
}

if (!isset($dv)) $dv=new DocumentVersion();
$jsonReturn='{"file":"'.htmlEncodeJson($dv->fileName).'",'
 .'"name":"'.htmlEncodeJson($dv->fileName).'",'
 .'"type":"'.htmlEncodeJson($dv->mimeType).'",'
 .'"size":"'.htmlEncodeJson($dv->fileSize).'"  ,'
 .'"message":"'.str_replace('"',"'",$message).'"}';

if ($isIE and $isIE<=9) {
  echo $message;
  echo '</body>';
  echo '</html>';
} else {
  ob_clean();
  echo $jsonReturn;
}?>