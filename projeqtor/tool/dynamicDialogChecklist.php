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
require_once "../tool/projeqtor.php";
include_once '../tool/formatter.php';

if (! isset ($print) or !$print) {
	$print=false;
	$printWidthDialog="100%";
	$internalWidth='100%';
	$nameWidth="15%";
} else {
  $printWidthDialog=$printWidth.'px';
  $nameWidthValue=120;
  $nameWidth=$nameWidthValue.'px;';
  $internalWidth=($printWidth-$nameWidthValue+8).'px';
}
$context="popup";
if (isset($obj)) {
  $objectClass=get_class($obj);
  $objectId=$obj->id;
  $context="detail";
} else {
  if (! array_key_exists('objectClass',$_REQUEST)) {
  	throwError('Parameter objectClass not found in REQUEST');
  }
  $objectClass=$_REQUEST['objectClass'];
  Security::checkValidClass($objectClass);
  
  if (! array_key_exists('objectId',$_REQUEST)) {
  	throwError('Parameter objectId not found in REQUEST');
  }
  $objectId=$_REQUEST['objectId'];
}
$checklistDefinition=null;
$obj=new $objectClass($objectId); // Note: $objectId is checked in base SqlElement constructor to be numeric value
$type='id'.$objectClass.'Type';
$checklist=new Checklist();
$checklistList=$checklist->getSqlElementsFromCriteria(array('refType'=>$objectClass, 'refId'=>$objectId));
if (count($checklistList)>0) {
  $checklist=array_shift($checklistList);
	$checklistDefinition=new ChecklistDefinition($checklist->idChecklistDefinition);
	if ($checklistDefinition->id and 
      ( ( $checklistDefinition->nameChecklistable!=$objectClass) 
      or( $checklistDefinition->idType and $checklistDefinition->idType!=$obj->$type)
      ) ) {
		$checklist->delete();
		unset($checklist);
	}
	// Clear dupplicate 
	if (count($checklistList)>0) {
	  foreach ($checklistList as $del) {
	    $del->delete();
	  }
	}
}
if (!isset($checklist) or !$checklist or !$checklist->id) {
	$checklist=new Checklist();
}
if (!$checklistDefinition or ! $checklistDefinition->id) {
	if (property_exists($obj,$type)) {
		$crit=array('nameChecklistable'=>$objectClass, 'idType'=>$obj->$type, 'idle'=>'0');
  	$checklistDefinition=SqlElement::getSingleSqlElementFromCriteria('ChecklistDefinition', $crit);
	}
	if (!$checklistDefinition or !$checklistDefinition->id) {
		$crit="nameChecklistable='$objectClass' and idle=0";
		if (property_exists($obj,$type)) {$crit.=" and idType is null ";}
		$cd=new ChecklistDefinition();
		$cdList=$cd->getSqlElementsFromCriteria(null,false,$crit);
		$checklistDefinition=reset($cdList);
	}
}
if (!$checklistDefinition or !$checklistDefinition->id) {
	echo '<div class="ERROR" >'.i18n('noChecklistDefined').'</div>';
	exit;
}
$cdl=new ChecklistDefinitionLine();
$defLines=$cdl->getSqlElementsFromCriteria(array('idChecklistDefinition'=>$checklistDefinition->id),false, null, 'sortOrder asc');
//usort($defLines,"ChecklistDefinitionLine::sort");
$cl=new ChecklistLine();
$linesTmp=$cl->getSqlElementsFromCriteria(array('idChecklist'=>$checklist->id));

$linesVal=array();
foreach ($linesTmp as $line) {
	$linesVal[$line->idChecklistDefinitionLine]=$line;
}

$canUpdate=(securityGetAccessRightYesNo('menu' . $objectClass, 'update', $obj)=='YES');
if ($obj->idle) $canUpdate=false;
if ($print) $canUpdate=false;
?>
<?php if (! $print) {?>
<?php if ($context=='popup') {?>
<form id="dialogChecklistForm" name="dialogChecklistForm" action="">
<?php }?>
<input type="hidden" name="checklistDefinitionId" value="<?php echo $checklistDefinition->id;?>" />
<input type="hidden" name="checklistId" value="<?php echo $checklist->id;?>" />
<input type="hidden" name="checklistObjectClass" value="<?php echo htmlEncode($objectClass);?>" />
<input type="hidden" name="checklistObjectId" value="<?php echo htmlEncode($objectId);?>" />
<?php } else {?>
<table style="width:<?php echo $printWidthDialog;?>;">
  <tr><td>&nbsp;</td></tr>
  <tr><td class="section"><?php echo i18n("Checklist");?></td></tr>
  <tr style="height:0.5em;font-size:80%"><td>&nbsp;</td></tr>
</table>	
<?php }?> 
<table style="width:<?php echo $printWidthDialog;?>;">
  <tr>
    <td style="width:<?php echo $printWidthDialog;?>;">
	    <table style="width:<?php echo $printWidthDialog;?>;" >
<?php foreach($defLines as $line) {
	      if (isset($linesVal[$line->id])) {
          $lineVal=$linesVal[$line->id];
        } else {
          $lineVal=new ChecklistLine();
        }?>	 
		    <tr style="height:25px;min-height:25px;<?php echo ($line->required==1)?'border-left:3px solid red ;':''; ?>">
<?php   if ($line->check01) {?>
			    <td class="noteData" style="<?php echo ($print)?'width:'.$nameWidth:'';?>border-right:0; text-align:right" title="<?php echo ($print)?'':$line->title;?>"> 
				  <?php echo htmlEncode($line->name);?>
				  <input type="hidden" name="isRequired_<?php echo $line->id;?>" value="<?php echo $line->required;?>" />  
		      </td>
			    <td class="noteData" style="border-left:0;">
			      <table style="width:<?php echo $internalWidth;?>;">
			        <tr>
				<?php 
				      $nb=0;
				      for ($i=1;$i<=5;$i++) {
				        $check='check0'.$i;
				        if ($line->$check) $nb++;
				      }
				      if ($nb<3) $nb=3;
				      $width=($nb)?15*5/$nb:15*5;
				      for ($i=1;$i<=5;$i++) {
								$check='check0'.$i;
								$title='title0'.$i;
								$value='value0'.$i;?>
								<td style="min-width:100px;width:<?php echo $width;?>%;vertical-align:top;<?php if (!$line->$check) echo 'display:none';?>" title="<?php echo ($print)?'':$line->$title;?>" >
					<?php if ($line->$check) {
								  $checkName="check_".htmlEncode($line->id)."_".$i;
								  if ($print) {
		                $checkImg="checkedKO.png";
		                if ($lineVal->$value) {
			               $checkImg= 'checkedOK.png';
		                }
		                echo '<img src="img/' . $checkImg . '" />&nbsp;'.htmlEncode($line->$check).'&nbsp;&nbsp;';
							    } else {?>
								  <div dojoType="dijit.form.CheckBox" type="checkbox"
						        <?php if ($line->exclusive and ! $print) {?>onClick="checkClick(<?php echo $line->id;?>, <?php echo $i;?>)" <?php }?>
						        name="<?php echo $checkName;?>" id="<?php echo $checkName;?>"
						        <?php if (! $canUpdate) echo 'readonly';?>
				            <?php if ($lineVal->$value) { echo 'checked'; }?> ></div>
								  <span style="cursor:pointer;" onClick="dojo.byId('<?php echo $checkName;?>').click();"><?php echo ($line->$check);?>&nbsp;&nbsp;</span>
					  <?php } 
		            }?>
		            </td>
				<?php }?>
				<?php if (! $print) {?>
				<td >&nbsp;</td>
				<?php }?>	
				<td style="white-space:nowrap;text-align:right; width:<?php echo ($print)?'0px':'50px;min-width:50px';?>; color: #A0A0A0;white-space:nowrap" valign="top">				  
				<?php 
				  if ($lineVal->checkTime and !$print) {
            $userId=$lineVal->idUser;
            $userName=SqlList::getNameFromId('User', $userId);
            echo formatUserThumb($userId, $userName, 'Creator');
            echo formatDateThumb($lineVal->checkTime,null);
         }?></td>
				<td style="width:3px;">&nbsp;</td>
				<td valign="top" style="width:<?php echo ($print)?'115px;font-size:90%;':'150px;'; echo (isNewGui())?'padding:0;':'';?>"> 
				  <?php if (! $print) {?>
				  <textarea dojoType="dijit.form.Textarea"
            id="checklistLineComment_<?php echo $line->id;?>" name="checklistLineComment_<?php echo $line->id;?>"
            style="width: 150px;min-height:20px; top:-2px;font-size:90%;<?php echo (isNewGui())?'position:relative;top:-5px':'';?>"
            maxlength="4000"
            class="input"><?php echo $lineVal->comment;?></textarea>
          <?php } else {
            echo htmlEncode($lineVal->comment); 
                }?>  
				</td>
				  </tr></table></td>
				
<?php } else { ?>
				<td class="noteHeader" colspan="2" style="text-align:center" title="<?php echo $line->title;?>">
				  <?php echo $line->name;?>
				  <div style="width: 150px; float:right; font-weight: normal"><?php echo i18n('colComment')?></div>
				</td>
<?php }?>		
	    </tr>
<?php } // end foreach($defLine?>
      <tr>
        <td class="noteDataClosetable">&nbsp;</td>
	      <td class="noteDataClosetable">&nbsp;</td>
	    </tr>
	    <?php if (! $print or $checklist->comment) {?>
	    <tr>
	      <td style="text-align: right;"><?php echo i18n('colComment')?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</td>
	      <td>
	      <?php if (! $print) {?>
				  <textarea dojoType="dijit.form.Textarea" 
            id="checklistComment" name="checklistComment"
            style="width: 100%;font-size: 90%"
            maxlength="4000"
            class="input"><?php echo $checklist->comment;?></textarea>
          <?php } else {
            echo htmlEncode($checklist->comment); 
                }?>  
	      </td>
	    </tr>
	    <?php }?>
	  </table>
  </td></tr>
 <tr><td style="width:<?php echo $printWidthDialog;?>;">&nbsp;</td></tr>
<?php if (! $print and $context=='popup') {?>
 <tr>
   <td style="width: 100%;" align="center">
     <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogChecklist').hide();">
       <?php echo i18n("buttonCancel");?>
     </button>
     <button id="dialogChecklistSubmit" dojoType="dijit.form.Button" type="submit" 
       onclick="protectDblClick(this);saveChecklist();return false;" >
       <?php echo i18n("buttonOK");?>
     </button>
   </td>
 </tr>      
<?php }?> 
</table>
<?php if (! $print and $context=='popup') {?></form><?php }?>
