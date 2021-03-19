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

/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";

if (! isset($objectId) ) $objectId=RequestHandler::getId('objectId');
if (! isset($objectClass) ) $objectClass=RequestHandler::getClass('objectClass');

$type='';
$note=new Note();
$notes=$note->getSqlElementsFromCriteria(array('refType'=>$objectClass,'refId'=>$objectId), false, null);
if(RequestHandler::isCodeSet('type')) $type=RequestHandler::getClass('type');
//===============================

$enterTextHere = '<p style="color:red;">'.i18n("textareaEnterText").'</p>';
$noNotes = '<p"><br/><i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.i18n("noNote").'</p>';

$order = "COALESCE (updateDate,creationDate) ASC";

SqlElement::resetCurrentObjectTimestamp();
$ress=new Resource($user->id);
$creationDate=$note->creationDate;
$updateDate=$note->updateDate;
if ($updateDate == null) {
	$updateDate='';
}

$countIdNote=count($notes);
$onlyCenter=(RequestHandler::getValue('onlyCenter')=='true')?true:false;
$privacyNotes=Parameter::getUserParameter('privacyNotes'.$objectClass);

//======================================================
?>

<!-- Titre et listes de notes -->
<div class="container" dojoType="dijit.layout.BorderContainer" liveSplitters="false" style="width:800px; height:500px;">
	<div id="activityStreamTopKanban" dojoType="dijit.layout.ContentPane" region="top" style="text-align:center" class="dijitAccordionTitle">
	  <span class="title" ><?php echo i18n("titleStream");?></span>
	</div>
	<div id="activityStreamCenterKanban" dojoType="dijit.layout.ContentPane" region="center" style="overflow-x:hidden;">
	  <script type="dojo/connect" event="onLoad" args="evt">
        scrollInto();
	  </script>
	  <table id="objectStreamKanban" style="width:100%;"> 
	    <?php foreach ( $notes as $note ) {
	      echo activityStreamDisplayNote ($note,"objectStreamKanban");
	    };?>
	    
	    <tr><td><div id="scrollToBottom" style="display:block"></div></td></tr>
	  </table>
	</div>
	<div id="activityStreamBottomKanban" dojoType="dijit.layout.ContentPane" region="bottom" style="height:<?php echo (isNewGui())?'80':'70';?>px;overflow-x:hidden;">
	  <form id='noteFormStreamKanban' name='noteFormStreamKanban' onSubmit="return false;" >
       <input id="noteId" name="noteId" type="hidden" value="" />
       <input id="noteRefType" name="noteRefType" type="hidden" value="<?php echo $objectClass;?>" />
       <input id="noteRefId" name="noteRefId" type="hidden" value="<?php echo $objectId;?>" />
       <input id="kanbanRefType" name="kanbanRefType" type="hidden" value="<?php echo $type;?>" />
       <input id="noteEditorTypeStreamKanban" name="noteEditorTypeStreamKanban" type="hidden" value="<?php echo getEditorType();?>" />
       <div style="width:99%;position:relative">
       <textarea rows="4"  name="noteStreamKanban" id="noteStreamKanban" dojoType="dijit.form.SimpleTextarea"
         style="width:98.5%;height:60px;overflow-x:hidden;overflow-y:auto;border:1px solid grey;margin-top:2px;" tabIndex="-1" onclick="focusStream();" ><?php echo i18n("textareaEnterText");?></textarea>
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
         <div title="<?php echo i18n("colIdPrivacy").' = '.$privacyLabel;?>" id="notePrivacyStreamDiv" class="<?php echo $privacyClass;?>" onclick="switchNotesPrivacyStream();" style="<?php if (! isNewGui()) echo 'border-radius:8px;background-color: #E0E0E0;bottom:2px;right:-2px;'; else echo 'bottom:8px;right:3px;'?>width:16px; height:16px;position:absolute;opacity:1;color:#A0A0A0;cursor:pointer;text-align:center">...</div>
         <input type="hidden" id="notePrivacyStream" name="notePrivacyStream" value="<?php echo $privacyNotes?>" />
         <input type="hidden" id="notePrivacyStreamUserTeam" name="notePrivacyStreamUserTeam" value="<?php echo $ress->idTeam;?>" />
        </div>
     </form>  
  </div>
</div>