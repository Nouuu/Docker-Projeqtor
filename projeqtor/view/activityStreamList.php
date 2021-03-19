<?php
use PhpOffice\PhpPresentation\Shape\RichText\Paragraph;
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

/*
 * ============================================================================ Presents an object.
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog ( '   ->/view/objectStream.php' );
global $print, $user;
$user = getSessionUser ();

$showOnlyNotes=Parameter::getUserParameter('showOnlyNotes');
if($showOnlyNotes=='')$showOnlyNotes='NO';
if (RequestHandler::isCodeSet('activityStreamNumberElement')) {
	$activityStreamNumberElement=RequestHandler::getValue("activityStreamNumberElement");
	Parameter::storeUserParameter("activityStreamNumberElement", $activityStreamNumberElement);
} else {
	$activityStreamNumberElement=Parameter::getUserParameter("activityStreamNumberElement");
	if($activityStreamNumberElement=='')$activityStreamNumberElement=100;
}

if (RequestHandler::isCodeSet('activityStreamAuthorFilter')) {
	$paramAuthorFilter=RequestHandler::getId("activityStreamAuthorFilter");
	Parameter::storeUserParameter("activityStreamAuthorFilter", trim($paramAuthorFilter));
} else {
	$paramAuthorFilter=Parameter::getUserParameter("activityStreamAuthorFilter");
}

if (RequestHandler::isCodeSet('activityStreamTeamFilter')) {
  $paramTeamFilter=RequestHandler::getId("activityStreamTeamFilter");
  Parameter::storeUserParameter("activityStreamTeamFilter", trim($paramTeamFilter));
} else {
  $paramTeamFilter=Parameter::getUserParameter("activityStreamTeamFilter");
}

if (RequestHandler::isCodeSet('activityStreamTypeNote')) {
  $paramTypeNote=RequestHandler::getId("activityStreamTypeNote");
  Parameter::storeUserParameter("activityStreamElementType", $paramTypeNote);
} else {
  $paramTypeNote=Parameter::getUserParameter("activityStreamElementType");
}
$typeNote = SqlList::getNameFromId('Importable', $paramTypeNote,false);

if (RequestHandler::isCodeSet('activityStreamIdNote')) {
  $paramStreamIdNote=RequestHandler::getId("activityStreamIdNote");
  Parameter::storeUserParameter("activityStreamIdNote", $paramStreamIdNote);
} else {
  $paramStreamIdNote=Parameter::getUserParameter("activityStreamIdNote");
}

if (RequestHandler::isCodeSet('activityStreamNumberDays')) {
  $activityStreamNumberDays=RequestHandler::getNumeric("activityStreamNumberDays");
  Parameter::storeUserParameter("activityStreamNumberDays", $activityStreamNumberDays);
} else {
  $activityStreamNumberDays=Parameter::getUserParameter("activityStreamNumberDays");
}

if (RequestHandler::isCodeSet('activityStreamShowClosed')) {
  $activityStreamShowClosed=RequestHandler::getValue("activityStreamShowClosed");
  Parameter::storeUserParameter("activityStreamShowClosed", $activityStreamShowClosed);
} else {
  $activityStreamShowClosed=Parameter::getUserParameter("activityStreamShowClosed");
}

$activityStreamAddedRecently=null;
  if (RequestHandler::isCodeSet('activityStreamAddedRecently')) {
    $activityStreamAddedRecently=RequestHandler::getValue("activityStreamAddedRecently");
    Parameter::storeUserParameter("activityStreamAddedRecently", $activityStreamAddedRecently);
  } else {
    $activityStreamAddedRecently=Parameter::getUserParameter("activityStreamAddedRecently");
  }

$activityStreamUpdatedRecently=null;
  if (RequestHandler::isCodeSet('activityStreamUpdatedRecently')) {
    $activityStreamUpdatedRecently=RequestHandler::getValue("activityStreamUpdatedRecently");
    Parameter::storeUserParameter("activityStreamUpdatedRecently", $activityStreamUpdatedRecently);
  } else {
    $activityStreamUpdatedRecently=Parameter::getUserParameter("activityStreamUpdatedRecently");
  }
    
$paramProject=getSessionValue('project');
if(strpos($paramProject, ",")){
	$paramProject="*";
}

$note = new Note ();
$hist=new History();
$histTable=$hist->getDatabaseTableName();
$critWhere="1=1";
$max=($activityStreamAddedRecently or $activityStreamUpdatedRecently)?10000:2000;
$where=" id>(select max(id)-$max from $histTable)";
$clause="1=1";
if (trim($paramAuthorFilter)!="") {
	$critWhere.=" and idUser=$paramAuthorFilter";
	$where.=" and idUser=$paramAuthorFilter";
	$clause.=" and idUser=$paramAuthorFilter";
}

if (trim($paramTeamFilter)!="") {
	$team = new Resource();
	$teamResource=$team->getDatabaseTableName();
	$critWhere.=" and idUser in (select id from $teamResource where idTeam=$paramTeamFilter)";
	$where.=" and idUser in (select id from $teamResource where idTeam=$paramTeamFilter)";
	$clause.=" and idUser in (select id from $teamResource where idTeam=$paramTeamFilter)";
}

$import=new Importable();
$importTableName=$import->getDatabaseTableName();
if (trim($paramTypeNote)!="") {
  $critWhere.=" and refType='$typeNote'";
  $where.=" and refType='$typeNote' ";
  $clause.=" and refType='$paramTypeNote' ";
}else{
  $where.=" and refType in (select name from $importTableName)";
}

if (trim($paramStreamIdNote)!="") {
  $critWhere.=" and refId=$paramStreamIdNote";
  $where.=" and refId=$paramStreamIdNote";
  $clause.=" and refId=$paramStreamIdNote";
}

if ($paramProject!='*') {
	$critWhere.=" and (idProject in ".getVisibleProjectsList(true).')';
	$where.=" and (idProject in ".getVisibleProjectsList(true).')';
	$clause.=" and (idProject in ".getVisibleProjectsList(true).')';
} else {
	$critWhere.=" and (idProject is null or idProject in ".getVisibleProjectsList($paramProject).')';
	$where.=" and (idProject is null or idProject in ".getVisibleProjectsList($paramProject).')';
	$clause.=" and (idProject is null or idProject in ".getVisibleProjectsList($paramProject).')';
}

if (Sql::isPgsql()) {
  if ($activityStreamAddedRecently and $activityStreamUpdatedRecently and $activityStreamNumberDays!=="") {  //////////////// added + updated
    $critWhere.=" AND creationDate>=CURRENT_DATE - INTERVAL '" . intval($activityStreamNumberDays) . " day'";
    $critWhere.=" OR updateDate>=CURRENT_DATE - INTERVAL '" . intval($activityStreamNumberDays) . " day ' ";
    $where.=" AND ((operation='update' AND colName='idStatus')  OR (operation='insert' AND (colName IS NULL OR colName LIKE ('|Attachment|%') OR colName LIKE ('Link|%'))) OR (operation='delete' AND colName NOT LIKE ('|Attachment|%') AND colName NOT LIKE ('Link|%') AND reftype NOT IN ('Assignment','Affectation')))";
    $where.=" AND operationDate>=CURRENT_DATE - INTERVAL '" . intval($activityStreamNumberDays) . " day'";
    $clause.= " AND mailDateTime>=CURRENT_DATE - INTERVAL '" . intval($activityStreamNumberDays) . " day'";
    
  } else if ($activityStreamAddedRecently=="added" && trim($activityStreamNumberDays)!="" and $activityStreamNumberDays!==""){ //////////////// added 
    $critWhere.=" AND creationDate>=CURRENT_DATE -INTERVAL '" . intval($activityStreamNumberDays) . " day ' ";
    $where.=" AND (operation='insert' AND (colName IS NULL OR colName LIKE ('|Attachment|%') OR colName LIKE ('Link|%')) AND refType NOT IN ('Assignment','Affectation')) ";
    $where.=" AND operationDate>=CURRENT_DATE -INTERVAL '" . intval($activityStreamNumberDays) . " day ' ";
    $clause.= " AND mailDateTime>=CURRENT_DATE - INTERVAL '" . intval($activityStreamNumberDays) . " day'";
    
  } else if ($activityStreamUpdatedRecently=="updated" and $activityStreamNumberDays!==""){         //////////// updated
    $critWhere.=" AND updateDate>=CURRENT_DATE - INTERVAL '" . intval($activityStreamNumberDays) . " day ' ";
    $where.=" AND ((operation='update' AND colName='idStatus') OR (operation='delete' AND colName not like ('|Attachment|%'))) and operationDate>=CURRENT_DATE - INTERVAL '" . intval($activityStreamNumberDays) . " day ' ";
  
  }else{
    $where.=" AND ((operation='update' AND colName='idStatus')  OR (operation='insert' AND (colName IS NULL OR colName LIKE ('|Attachment|%') OR colName LIKE ('Link|%'))) OR (operation='delete' AND colName NOT LIKE ('|Attachment|%') AND colName NOT LIKE ('Link|%') AND reftype NOT IN ('Assignment','Affectation')))";
  }   
} else {
  if ($activityStreamAddedRecently and $activityStreamUpdatedRecently and $activityStreamNumberDays!=="") {   //////////////// added + updated
    $critWhere.=" and ( creationDate>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY) ";
    $critWhere.=" or updateDate>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY) )";
    $where.=" and ((operation='update' and colName='idStatus')  or (operation='insert' and (colName is null or colName like ('|Attachment|%') or colName like ('Link|%'))) or (operation='delete' and colName not like ('|Attachment|%') and colName not like ('Link|%') and reftype not In ('Assignment','Affectation')))";
    $where.=" and operationDate>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY)";
    $clause.=" and  mailDateTime>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY) ";
    
  } else if ($activityStreamAddedRecently=="added" && trim($activityStreamNumberDays)!="" and $activityStreamNumberDays!==""){ //////////////// added 
    $critWhere.=" and creationDate>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY) ";
    $where.=" and (operation='insert' and (colName is null or colName like ('|Attachment|%') or colName like ('Link|%')) and refType not In ('Assignment','Affectation'))";
    $where.=" and operationDate>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY) ";
    $clause.=" and  mailDateTime>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY) ";
    
  } else if ($activityStreamUpdatedRecently=="updated" and $activityStreamNumberDays!==""){   //////////// updated
    $critWhere.=" and updateDate>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY) ";
    $where.=" and ((operation='update' and colName='idStatus') or (operation='delete' and colName not like ('|Attachment|%'))) and operationDate>=ADDDATE(CURDATE(), INTERVAL (-" . intval($activityStreamNumberDays) . ") DAY)  ";
  
  }else{
    $where.=" and ((operation='update' and colName='idStatus')  or (operation='insert' and (colName is null or colName like ('|Attachment|%') or colName like ('Link|%'))) or (operation='delete' and colName not like ('|Attachment|%') and colName not like ('Link|%') and reftype not In ('Assignment','Affectation')))";
  }
}

if ($activityStreamShowClosed!='1') {
	$critWhere.=" and idle=0";
	$clause.=" and idle=0";
}

$activityStreamNumberElement=intval($activityStreamNumberElement);
$multipleLimit=100; // To avoid reading all elements, while taking into account fact that retreived data may not be display depending on access right, multiplicated retreived max number
$limitQuery=$activityStreamNumberElement*$multipleLimit;
if ($limitQuery<10000) $limitQuery=10000;
//echo '<br/>';
$order = "COALESCE (updateDate,creationDate) DESC";
$notes=$note->getSqlElementsFromCriteria(null,false,$critWhere,$order,null,true,$limitQuery);
$historyInfoLst=array();
$mailsSend=array();

if($showOnlyNotes=='NO'){
  //if(securityCheckDisplayMenu(null,'Mail') and securityGetAccessRightYesNo('menu'.'Mail', 'read', null)=="YES"){
    $mail= new Mail();
    $mailsSend=$mail->getSqlElementsFromCriteria(null,false,$clause,"mailDateTime DESC",null,true,$limitQuery);
  //}
  
  ///// search elements in history  for display on ActivityStream 
  $history= new History();
  $historyInfo=$history->getSqlElementsFromCriteria(null,null,$where,"operationDate DESC, id desc",null,true,$limitQuery);
  if($activityStreamShowClosed =='1'){
    $historyArchive=new HistoryArchive();
    $historyInfoArchive=$historyArchive->getSqlElementsFromCriteria(null,null,$where,"operationDate DESC, id desc",null,null,$limitQuery);
    if(!empty($historyInfoArchive)){
      foreach ($historyInfoArchive as $histArch){
        foreach ($historyInfo as $hist){
          if($hist->operationDate>$histArch->operationDate){
            $historyInfoLst[]=$hist;
          }else{
            $historyInfoLst[]=$histArch;
          }
        }
      }
    }else{
      $historyInfoLst=$historyInfo;
    }
  }else{
    $historyInfoLst=$historyInfo;
  }
  /////
}
$countDisplay=0;
$countIdNote = count ( $notes );
$nbHistInfo= count($historyInfoLst);
$countMail= count($mailsSend);
$sumArray=$countIdNote+$countMail+$nbHistInfo;
if ($countIdNote == 0 and $nbHistInfo==0 and $countMail==0) {
  echo "<div style='padding:10px'>".i18n ( "noNoteToDisplay" )."</div>";
  exit ();
}
$onlyCenter = (RequestHandler::getValue ( 'onlyCenter' ) == 'true') ? true : false;
?>
<div dojo-type="dijit.layout.BorderContainer" class="container" style="overflow-y:auto;">
	<table id="objectStream" style="width: 100%;font-size:100% !important;"> 
	<?php
  	function sortNotes(&$listNotes, &$result, $parent){
  		foreach ($listNotes as $note){
  			if($note->idNote == $parent){
  				$result[] = $note;
  				sortNotes($listNotes, $result, $note->id);
  			}
  		}
  	}
	$noteDiscussionMode = Parameter::getUserParameter('userNoteDiscussionMode');
    if($noteDiscussionMode == null){
    	$noteDiscussionMode = Parameter::getGlobalParameter('globalNoteDiscussionMode');
    }
    if($noteDiscussionMode == 'YES'){
	    $result = array();
	    sortNotes($notes, $result, null);
	    $notes = $result;
    }
  	///
//  	 $cp=1;
//   	   foreach ($notes as $idNote=>$note){
//          if($cp<=$activityStreamNumberElement ){
//           if(!empty($historyInfoLst)){
//            foreach ($historyInfoLst as $id=>$hist ){ // search for each note if there is a history information to display before
//               if($cp<=$activityStreamNumberElement){
//                 if($hist->operationDate > $note->creationDate){
//                   if(!empty($mailsSend)){
//                	    foreach ($mailsSend as $idMail=>$mail){ // search for each history element if there is a mail information to display before
//                       if($cp<=$activityStreamNumberElement){
//                         if($mail->mailDateTime > $hist->operationDate){
//                           $resMail= activityStreamDisplayMail($mail,'activityStream');
//                           unset($mailsSend[$idMail]);
//                           if($resMail!=''){
//                             echo $resMail;
//                             $cp++;
//                             $countDisplay++;
//                           }
//                           continue;
//                         }
//                       }else{
//                         break;
//                       }
//                     }
//                     if($cp<=$activityStreamNumberElement){
//                       $resultHist= activityStreamDisplayHist($hist,"activityStream");
//                       unset($historyInfoLst[$id]);
//                       if($resultHist!=''){
//                         echo $resultHist;
//                         $cp++;
//                         $countDisplay++;
//                         continue;
//                       }
//                     }
//                   }else{
//                     if($cp<=$activityStreamNumberElement){
//                       $resultHist= activityStreamDisplayHist($hist,"activityStream");
//                       unset($historyInfoLst[$id]);
//                       if($resultHist!=''){
//                         echo $resultHist;
//                         $cp++;
//                         $countDisplay++;
//                       }
//                       continue;
//                     }else{
//                       break;
//                     }
//                   }
//                 }
//               }else{
//                 break;
//               }
//            }
//            if($cp<=$activityStreamNumberElement){
//          	  $resNote=activityStreamDisplayNote($note,"activityStream");
//          	  unset($notes[$idNote]);
//          	  if($resNote!=''){
//          	  	echo $resNote;
//          	  	$cp++;
//          	  	$countDisplay++;
//          	  }
//          	  continue;
//            }
//          }else{
//              $resNot=activityStreamDisplayNote($note,"activityStream");
//              unset($notes[$idNote]);
//              if($resNot!=''){
//               echo $resNot;
//               $cp++;
//               $countDisplay++;
//              }
//          }
//        }else{
//         break;
//        }
//      }
	 
//      if($cp<=$activityStreamNumberElement and !empty($historyInfoLst)){
//        foreach ($historyInfoLst as $id=>$hist){
//         if( $cp<=$activityStreamNumberElement ){
//           if(!empty($mailsSend)){
//           	foreach ($mailsSend as $idMail=>$mail){
//               if($cp<=$activityStreamNumberElement){
//         		if($mail->mailDateTime > $hist->operationDate){
//                   $resMail=activityStreamDisplayMail($mail,'activityStream');
//                   unset($mailsSend[$idMail]);
//                   if($resMail!=''){
//                     echo $resMail;
//                 	$cp++;
//                 	$countDisplay++;
//                   }
//                   continue;
//                 }
//               }else{
//                 break;
//               }
//           	}
//           	if($cp<=$activityStreamNumberElement){
//               $resultHist= activityStreamDisplayHist($hist,"activityStream");
//               unset($historyInfoLst[$id]);
//               if($resultHist!=''){
//                 echo $resultHist;
//                 $cp++;
//                 $countDisplay++;
//               }
//             }
//           }else{
//             $resultHist= activityStreamDisplayHist($hist,"activityStream");
//             unset($historyInfoLst[$id]);
//             if($resultHist!=''){
//               echo $resultHist;
//               $cp++;
//               $countDisplay++;
//             }
//           }
//         }
//        }
//      }
     
//      if($cp<=$activityStreamNumberElement and !empty($mailsSend)){
//         foreach ($mailsSend as $idMail=>$mail){
//           if($cp<=$activityStreamNumberElement){
//               $res= activityStreamDisplayMail($mail,'activityStream');
//               unset($mailsSend[$idMail]);
//               if($res!=''){
//                 echo $res;
//                 $cp++;
//                 $countDisplay++;
//               }
//           }else{
//             break;
//           }
//        }
//      }
  	 $all=array();
  	 foreach ($notes as $idNote=>$note){
  	   $date=$note->creationDate;
  	   $key=$date.'-2-'.$note->id;
  	   $all[$key]=array('type'=>'note','object'=>$note);
  	 }
  	 foreach ($historyInfoLst as $id=>$hist ){
  	   $date=$hist->operationDate;
  	   $key=$date.'-1-'.$hist->id;
  	   $all[$key]=array('type'=>'histo','object'=>$hist);
  	 }
  	 foreach ($mailsSend as $idMail=>$mail){
  	   $date=substr($mail->mailDateTime,0,-2).'60';
  	   $key=$date.'-3-'.$mail->id;
  	   $all[$key]=array('type'=>'mail','object'=>$mail);
  	 }
  	 krsort($all);
  	 foreach ($all as $idItem=>$item) {
  	   $type=$item['type'];
  	   $object=$item['object'];
  	   if ($type=='note') {
  	     $resNot=activityStreamDisplayNote($object,"activityStream");
  	     if($resNot){
  	       echo $resNot;
  	       $countDisplay++;
  	     }  	     
  	   } else if ($type=='histo') {
  	     $resultHist= activityStreamDisplayHist($object,"activityStream");
  	     if($resultHist){
  	       echo $resultHist;
  	       $countDisplay++;  	       
  	     }
  	   } else if ($type=='mail') {
  	     $resMail=activityStreamDisplayMail($object,'activityStream',$activityStreamShowClosed); 	    
  	     if($resMail){
  	       echo $resMail;
  	       $countDisplay++;
  	     }
  	   } else {
  	     // ERROR ;)
  	   }
  	   if ($countDisplay>=$activityStreamNumberElement) {
  	     break;
  	   }
  	 }
  	 
  	 
     if($countDisplay==0 ){
      echo "<div style='padding:10px'>".i18n ( "noNoteToDisplay" )."</div>";
     }
	  ?>
	</table>
	<div id="scrollToBottom" type="hidden"></div>
</div>