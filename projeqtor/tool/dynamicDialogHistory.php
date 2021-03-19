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

include_once '../tool/formatter.php';

if (! isset ($print)) {
	$print=false;
}
if (! array_key_exists('objectClass',$_REQUEST)) {
	throwError('Parameter objectClass not found in REQUEST');
}
$objectClass=$_REQUEST['objectClass'];

if (! array_key_exists('objectId',$_REQUEST)) {
	throwError('Parameter objectId not found in REQUEST');
}
$objectId=$_REQUEST['objectId'];

$dynamicDialogHistory=true;
$_REQUEST['refreshHistory']=true;
$showWorkHistory=false;
if (getSessionValue('showWorkHistory')) {
  $showWorkHistory=true;
}
$showArchive=false;
if(getSessionValue('showArchive')==1){ $showArchive=true; }
?>



<?php showCloseButton();?>

<div style="position:relative;">
  <div style="position:absolute; z-index:3;<?php echo (isNewGui())?'top:-32px; left:50%; ':'margin-top:-1px;right:12px;';?>">
  <?php if($showArchive==true){?>
      <button id="historyArchive" title="<?php echo i18n('helpCloseHistoryArchive');?>" region="center" dojoType="dijit.form.Button"  iconClass="imageColorNewGui iconButtonMark16 iconButtonMark iconHistArchiveNo iconSize16" >
  <?php }else{?>
      <button id="historyArchive" title="<?php echo i18n('helpShowHistoryArchive');?>" region="center" dojoType="dijit.form.Button"  iconClass="imageColorNewGui iconHistArchive16 iconHistArchive iconSize16" >
  <?php } ?>
     <script type="dojo/connect" event="onClick" args="evt">
       saveDataToSession("showArchive",<?php echo ($showArchive)?0:1;?>);
       var param="&objectClass=<?php echo $objectClass;?>&objectId=<?php echo $objectId;?>";
       loadDialog("dialogHistory", null, true,param);
     </script>
   </button>
 </div>
<div style="">
<?php include '../view/objectDetail.php';?>
</div>
</div>
<?php // Centralise button, to be displayed on top and bottom 
function showCloseButton() {
  global $objectClass,$objectId,$showWorkHistory,$showArchive;
  ?> 
  
  <table style="width: 100%;">
   <tr>
     <td style="width: 50%;" align="left">
       <?php if (property_exists($objectClass, $objectClass.'PlanningElement')) {?>
       <button dojoType="dijit.form.Button" type="button" onclick="historyShowHideWork();">
         <?php if ($showWorkHistory) echo i18n("hideWork");
           else echo i18n("showWork");?>
       </button>
       <?php }?>
     </td>
     <td style="width: 50%;" align="right">
       <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogHistory').hide();">
         <?php echo i18n("close");?>
       </button>
     </td>
   </tr>      
  </table>
<?php 
} ?>



