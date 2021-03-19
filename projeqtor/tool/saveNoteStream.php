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

/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";
require_once "../external/parsedown/Parsedown.php";

// Get the note info
$refType=RequestHandler::getValue("noteRefType",false);
if ($refType=='TicketSimple') {
  $refType='Ticket';    
}

$refId=RequestHandler::getId("noteRefId",false);
$noteNote=RequestHandler::getValue("noteNoteStream",false);
if (1) { // Use Markdown on notes from activity stream
  $Parsedown = new Parsedown();
  $Parsedown->setSafeMode(true);
  $noteNote=$Parsedown->text($noteNote);
  $noteNote=str_replace(array('<p>','</p>',"<ul>\n","</li>\n"),array('','','<ul>','</li>'),$noteNote);
} else { // standard behavior (simple text)
  $noteNote=htmlEncode($noteNote,"htmlNoNl2br"); // Encode for security
}

$noteNote='<div>'.$noteNote.'</div>'; // Encode for security

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
  	  $result=substr($result, 0,$pos).' - ' . Mail::getResultMessage($mailResult) .substr($result, $pos);
  	}
  }
}

// Message of correct saving
displayLastOperationStatus($result);
?>