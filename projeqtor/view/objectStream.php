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

/* ============================================================================
 * Presents an object. 
 */
  require_once "../tool/projeqtor.php";
  require_once "../tool/formatter.php";
  scriptLog('   ->/view/objectStream.php');
  global $print,$user;
  
  $showOnlyNotes=Parameter::getUserParameter('showOnlyNotes');
  if($showOnlyNotes=='')$showOnlyNotes='NO';
  
  $historyInfoLst=array();
  if (! isset($objectClass) ) $objectClass=RequestHandler::getClass('objectClass');
  if (! isset($objectId)) $objectId=RequestHandler::getId('objectId');
  if($objectClass=='ResourcePlanning')$objectClass='PlanningElement';
  if(RequestHandler::getValue('productVersionsListId')!=null and $objectClass=='')$objectClass='PlanningElement';
  if ($objectClass=='TicketSimple') $objectClass='Ticket';
  $obj=new $objectClass($objectId);
  $objectIsClosed=(isset($obj) and property_exists($obj, 'idle') and $obj->idle)?true:false;
  $canUpdate=securityGetAccessRightYesNo('menu' . $objectClass, 'update', $obj) == "YES";
  if ($objectIsClosed) $canUpdate=false;
  if (!property_exists($obj, 'idle') or $obj->idle == 1) {
    $canUpdate=false;
  }
  $canUpdateStream=$canUpdate;
  if($objectClass=="PlanningElement"){
    $noData = '<br/><i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . i18n('noItemSelected') . '</i>';
  } else {
    $noData=htmlGetNoDataMessage($objectClass);    
  }
  
  $enterTextHere = '<p style="color:red;">'.i18n("textareaEnterText").'</p>';
  $noNotes = "<div style='padding:10px'>".i18n("noNoteToDisplay").'</div>';
  // get the modifications (from request)
  $note=new Note();
  $obj=new $objectClass($objectId,true);
  $objectIsClosed=(isset($obj) and property_exists($obj, 'idle') and $obj->idle)?true:false;
  $history=($objectIsClosed)?new HistoryArchive():new History();
  $order = "COALESCE (updateDate,creationDate) ASC";
  $notes=$note->getSqlElementsFromCriteria(array('refType'=>$objectClass,'refId'=>$objectId),null,null,$order);
  $historyInfoLst=array();
  $mailsSend=array();
  
  if($showOnlyNotes=='NO'){
    
    //if(securityCheckDisplayMenu(null,'Mail') and securityGetAccessRightYesNo('menu'.'Mail', 'read', '')=="YES"){
      $mailable= new Mailable();
      $idType=SqlElement::getSingleSqlElementFromCriteria('Mailable',array("name"=>$objectClass));
      $clause=array('refType'=>$idType->id,'refId'=>$objectId);
      $mail= new Mail();
      $mailsSend=$mail->getSqlElementsFromCriteria($clause,null,null,"mailDateTime ASC");
    //}
    
    $clauseWhere="refType='$objectClass' and refId=$objectId and ((operation='update' and colName='idStatus')  or (operation='insert' and (colName is null  or colName like ('Link|%'))) or (operation='delete'  and colName not like ('Link|%'))) ";
    if($objectId)$historyInfo=$history->getSqlElementsFromCriteria(null,null,$clauseWhere,"operationDate ASC");
    $obj=new $objectClass($objectId);
    if($obj->idle==1){
      $historyArchive=new HistoryArchive();
      $historyInfoArchive=$historyArchive->getSqlElementsFromCriteria(null,null,$clauseWhere,"operationDate ASC");
      if(!empty($historyInfoArchive)){
        foreach ($historyInfoArchive as $histArch){
          foreach ($historyInfo as $hist){
            if($histArch->operationDate>$hist->operationDate){
              $historyInfoLst[]=$histArch;
            }else{
              $historyInfoLst[]=$hist;
            }
          }
        }
      }else{
        $historyInfoLst=$historyInfo;
      }
    }else{
       $historyInfoLst=($objectId)?$historyInfo:$historyInfoLst;
    }
  }
  SqlElement::resetCurrentObjectTimestamp();
  $ress=new Resource($user->id);
  //$userId=$note->idUser;
  //$userName=SqlList::getNameFromId('User', $userId);
  $creationDate=$note->creationDate;
  $updateDate=$note->updateDate;
  if ($updateDate == null) {
    $updateDate='';
  }
  if (!$objectId) {  
      if(Parameter::getUserParameter('paramRightDiv')!='bottom'){
        echo "<div onclick='switchModeLayout(\"bottom\");' class='imageColorNewGui changeActivityStreamBotClass' style='position:absolute;top:2px;right:2px'></div>";
      }else{
        echo "<div onclick='switchModeLayout(\"trailing\");' class='imageColorNewGui changeActivityStreamClass' style='position:absolute;top:2px;right:2px'></div>";
      }
    
    echo "</br></br>";
    echo $noData; 
    exit;
  }
  $countIdNote=count($notes);
  $countHist=count($historyInfoLst);
  $countMail= count($mailsSend);
  $onlyCenter=(RequestHandler::getValue('onlyCenter')=='true')?true:false;
  $privacyNotes=Parameter::getUserParameter('privacyNotes'.$objectClass);
  $positionActivityStream=Parameter::getUserParameter('paramRightDiv');
?>
<!-- Titre et listes de notes -->
<?php if (!$onlyCenter) {?>
<div class="container" dojoType="dijit.layout.BorderContainer" liveSplitters="false">
	<div id="activityStreamTop" dojoType="dijit.layout.ContentPane" region="top" style="text-align:center" class="dijitAccordionTitle">
	<?php
      if($positionActivityStream!="bottom"){
         echo "<div onclick='switchModeLayout(\"bottom\");' class='imageColorNewGui changeActivityStreamBotClass' style='position:absolute;top:2px;right:2px'></div>";
      }else{
        echo "<div onclick='switchModeLayout(\"trailing\");'  class='imageColorNewGui changeActivityStreamClass' style='position:absolute;top:2px;right:2px'></div>";
      }
    
    ?>
	   <div style="text-align:left"><span class="title" ><?php echo i18n("titleStream");?></span></div>
	</div>
	<div id="activityStreamCenter" dojoType="dijit.layout.ContentPane" region="center" style="overflow-x:hidden;">
	<script type="dojo/connect" event="onLoad" args="evt">
        scrollInto();
	  </script><?php }?>
	  <table id="objectStream" style="width:100%;"> 
	    <?php 
	    $noteDiscussionMode = Parameter::getUserParameter('userNoteDiscussionMode');
	    if($noteDiscussionMode == null){
	    	$noteDiscussionMode = Parameter::getGlobalParameter('globalNoteDiscussionMode');
	    }
	    if($noteDiscussionMode == 'YES'){
  	    $result = array();
  	    sortNotes($notes, $result, null);
  	    $notes = $result;
	    }
	    //florent ticket 4728	    
          $all=array();
          foreach ($notes as $idNote=>$note){
            $date=$note->creationDate;
            $key=$date.'-2-'.$idNote;
            $all[$key]=array('type'=>'note','object'=>$note);
          }
          foreach ($historyInfoLst as $id=>$hist ){
            $date=$hist->operationDate;
            $key=$date.'-1-'.$id;
            $all[$key]=array('type'=>'histo','object'=>$hist);
          }
          foreach ($mailsSend as $idMail=>$mail){
            $date=substr($mail->mailDateTime,0,-2).'60';
            $key=$date.'-3-'.$idMail;
            $all[$key]=array('type'=>'mail','object'=>$mail);
          }
          krsort($all);
          $all=array_reverse($all);
          foreach ($all as $item) {
            $type=$item['type'];
            $object=$item['object'];
            if ($type=='note') {
              $resNot=activityStreamDisplayNote($object,"objectStream");
              if($resNot){
                echo $resNot;
              }
            } else if ($type=='histo') {
              $resultHist= activityStreamDisplayHist($object,"objectStream");
              if($resultHist){
                echo $resultHist;
              }
            } else if ($type=='mail') {
              $resMail=activityStreamDisplayMail($object,'objectStream');
              if($resMail){
                echo $resMail;
              }
            } else {
              // ERROR ;)
            }
          }
	    
	    ?>
	    <tr><td><div id="scrollToBottom" style="display:block"></div></td></tr>
	  </table>
	   
<?php if (!$onlyCenter) {?>   
<?php 
     $objectClassStream=$objectClass;
     if (RequestHandler::isCodeSet('objectClassList')) $objectClassStream=RequestHandler::getValue('objectClassList');
     $paramHeightStream=Parameter::getUserParameter('contentPaneRightDetailDivHeight'.$objectClassStream);
     //$paramWidthStream=Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClassStream);
     if(empty($paramHeightStream)){
        $paramHeightStream=140;
      }
      $paramHeightStream=$paramHeightStream-38;
    if($countIdNote==0 and $countHist==0 and $countMail==0){ echo "<div style='padding:10px'>".$noNotes."</div>";}	
?>
	</div>
	<div id="activityStreamBottom" dojoType="dijit.layout.ContentPane" region="<?php echo ($positionActivityStream=='bottom')?'trailing':'bottom';?>" style="<?php if($positionActivityStream=='bottom'){echo "width:30%;height:140px;overflow-x:hidden;padding-right:8px;";}else{echo "height:70px;overflow-x:hidden;padding-right:8px;";}?><?php if ($canUpdateStream!=true) echo 'display:none;';?>">
	  <form id='noteFormStream' name='noteFormStream' onSubmit="return false;" >
       <input id="noteId" name="noteId" type="hidden" value="" />
       <input id="noteRefType" name="noteRefType" type="hidden" value="<?php echo $objectClass;?>" />
       <input id="noteRefId" name="noteRefId" type="hidden" value="<?php echo $objectId;?>" />
       <input id="noteEditorTypeStream" name="noteEditorTypeStream" type="hidden" value="<?php echo getEditorType();?>" />
       <div style="width:100%;position:relative;">
         <textarea rows="4"  name="noteNoteStream" id="noteNoteStream" dojoType="dijit.form.SimpleTextarea"
         style="resize: none;width:99%;<?php if($positionActivityStream=='bottom'){echo "height:".$paramHeightStream."px";}else{echo "height:60px";}?>;overflow-x:hidden;overflow-y:auto;border:1px solid grey;margin-top:2px;" onfocus="focusStream();"><?php echo i18n("textareaEnterText");?></textarea>
         <?php
         $privacyClass="";
         $privacyLabel=i18n("public");
         if ($privacyNotes=="3") { // Team privacy
           $privacyClass="imageColorBlack iconFixed16 iconFixed iconSize16";
           $privacyLabel=i18n("private");
         } else if ($privacyNotes=="2") { // Private
           $privacyClass="imageColorBlack iconTeam16 iconTeam iconSize16";
           $privacyLabel=i18n("team");
         }?>
         <div title="<?php echo i18n("colIdPrivacy").' = '.$privacyLabel;?>" id="notePrivacyStreamDiv" class="<?php echo $privacyClass;?>" onclick="switchNotesPrivacyStream();" style="<?php if (! isNewGui()) echo 'border-radius:8px;background-color: #E0E0E0;'; else //echo 'border:1px solid #dddddd;'?>width:16px; height:16px;position:absolute;bottom:2px;right:-2px;opacity:1;color:#A0A0A0;cursor:pointer;text-align:center">...</div>
         <input type="hidden" id="notePrivacyStream" name="notePrivacyStream" value="<?php echo $privacyNotes?>" />
         <input type="hidden" id="notePrivacyStreamUserTeam" name="notePrivacyStreamUserTeam" value="<?php echo $ress->idTeam;?>" />
       </div>
     </form>
   </div>
</div>
<?php }
function sortNotes(&$listNotes, &$result, $parent){
  foreach ($listNotes as $note){
    if($note->idNote == $parent){
      $result[] = $note;
      sortNotes($listNotes, $result, $note->id);
    }
  }
}

?>

