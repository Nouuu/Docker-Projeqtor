<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
*
* This file is an add-on to ProjeQtOr, packaged as a plug-in module.
* It is NOT distributed under an open source license.
* It is distributed in a proprietary mode, only to the customer who bought
* corresponding licence.
* The company ProjeQtOr remains owner of all add-ons it delivers.
* Any change to an add-ons without the explicit agreement of the company
* ProjeQtOr is prohibited.
* The diffusion (or any kind if distribution) of an add-on is prohibited.
* Violators will be prosecuted.
*
*** DO NOT REMOVE THIS NOTICE ************************************************/

/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";

// Get the note info
$refType=RequestHandler::getValue("noteRefType",false);
if ($refType=='TicketSimple') {
  $refType='Ticket';    
}

$refId=RequestHandler::getId("noteRefId",false);

$noteNote=RequestHandler::getValue("noteStreamKanban",false);
$noteNote='<div>'.htmlEncode($noteNote,"html").'</div>'; // Encode for security

$notePrivacy=null;
$notePrivacy=RequestHandler::getValue("notePrivacyStream",false);
$noteId=null;
$noteId=RequestHandler::getId("noteId",false);
$noteId=trim($noteId);
if ($noteId=='') {
  $noteId=null;
} 
Sql::beginTransaction();
// get the modifications (from request)
$note=new Note();
$user=getSessionUser();
if (! $note->id) {
  $note->idUser=$user->id;
  $ress=new Resource($user->id);
  $note->idTeam=$ress->idTeam;
}

$note->refId=$refId;
$note->refType=$refType;
if ($note->creationDate==null) {
  $note->creationDate=date("Y-m-d H:i:s");
} else if ($note->note!=$noteNote) {
    $note->updateDate=date("Y-m-d H:i:s");
}
$note->note=nl2br($noteNote);
if ($notePrivacy) {
  $note->idPrivacy=$notePrivacy;
} else {
	$note->idPrivacy=1;
}
$result=$note->save();

if ($note->idPrivacy==1) { // send mail if new note is public
  $elt=new $refType($refId);
  $mailResult="";
  if ($noteId) {
  	$mailResult=$elt->sendMailIfMailable(false,false,false,false,false,false,true,false,false,false,false,true);
  } else {
	  $mailResult=$elt->sendMailIfMailable(false,false,false,false,true,false,false,false,false,false,false,true);
  }
  if ($mailResult) {
  	$pos=strpos($result,'<input type="hidden"');
  	if ($pos) {
  	  $result=substr($result, 0,$pos).' - ' . Mail::getResultMessage($mailResult).substr($result, $pos);
  	}
  }
}

$note=new Note();
$notes=$note->getSqlElementsFromCriteria(array('refType'=>$refType,'refId'=>$refId), false, null);?>
<table id="objectStreamKanban" style="width:100%;">
<?php foreach ( $notes as $note ) {
	echo activityStreamDisplayNote ($note,"objectStreamKanban");
	    };?>
	    
	    <tr><td><div id="scrollToBottom" style="display:block"></div></td></tr>
	  </table>
	  <div id="resultKanbanStreamDiv" style="display:block;position:fixed;z-index:9999;top:70px;left:50%;margin-left:-50px;">
<?php displayLastOperationStatus($result);
?>
</div>