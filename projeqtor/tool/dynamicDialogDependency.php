<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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
include_once ("../tool/projeqtor.php");
$id=RequestHandler::getId('id',true);
$dep=new Dependency($id);
$delayDep=$dep->dependencyDelay;
$commentDep=$dep->comment;


?>
<div class="contextMenuDiv" id="contextMenuDiv" style="height:<?php echo (isNewGui())?"140px":"135px";?>;z-index:99999999999;">

  <div style="width:215px;border-radius:1px 1px 0px 0px;">
    <div class="section" style="display: inline-block;width:100%; border-radius:0px;<?php if (isNewGui()) echo "background:var(--color-darker) !important;"?>" >
      <p  style="text-align:center;color:white;height:20px;font-size:15px;display:inline-block;<?php if (isNewGui()) echo "position:relative;top:3px;"?>"><?php echo i18n("operationUpdate");?></p>
      <div style="float:right;">
        <?php if (isNewGui()) {?>
        <div onclick="hideDependencyRightClick();" class="dijitDialogCloseIcon"></div>
        <?php } else  {?>
        <a onclick="hideDependencyRightClick();" <?php echo formatSmallButton('Mark') ;?></a>
         <?php } ?>
      </div>
    </div>
  </div>
  <form dojoType="dijit.form.Form" id='dynamicRightClickDependencyForm' name='dynamicRightClickDependencyForm' onSubmit="return false;" style="padding:5px;">
	  <table style="width:100%">
	    <tr style="height:28px">
	      <td style="text-align:right; width:100px;">
	        <input id="dependencyRightClickId" name="dependencyRightClickId" type="hidden" value="<?php echo $id;?>" />
          <label for="dependencyDelay" style="width:100px"><?php echo i18n("colDependencyDelay");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
        </td>
        <td style="text-align:left; white-space:nowrap">
	        <input id="delayDependency" name="delayDependency" dojoType="dijit.form.NumberTextBox" constraints="{min:-999, max:999}" 
            style="width:25px; text-align: center;" value="<?php echo $delayDep;?>" />
		      <?php echo i18n("days");?>
		    </td>
		    <td style="padding-left:5px;width:24px;">
		      <a id="dependencyRightClickSave" onclick="saveDependencyRightClick();"><?php echo formatMediumButton('Save') ;?></a> 
        </td>
      </tr>
      <tr style="height:28px;">
	      <td style="text-align:right;">
	        <label for="modeDependency" style="width:100px"><?php echo i18n("colType");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label> 
	      </td>
	      <td colspan="2">
	        <select dojoType="dijit.form.FilteringSelect" class="input" name="dependencyType" id="dependencyType"
            <?php echo autoOpenFilteringSelect();?> style="width:115px;height:20px">
            <?php $depType=array('E-S','E-E','S-S');
            foreach ($depType as $type) {
              $select=($dep->dependencyType==$type)?' selected ':'';
              $lib=( (substr($type,0,1)=='E')?i18n('colEnd'):i18n('colStart') ).' - '.( (substr($type,-1)=='E' )?i18n('colEnd'):i18n('colStart') );
              echo "<option value='$type' $select >$lib</option>";
            }?>
          </select>
	      </td>
	    </tr>
	    <tr style="height:28px;">
	      <td colspan="2">
	        <label for="commentDependency" style="text-align: left;"><?php echo i18n("colComment");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
					<input id="commentDependency" name="commentDependency"  dojoType="dijit.form.Textarea" value="<?php echo $commentDep;?>" />                        
	      </td>
	      <td style="text-align:right;vertical-align:bottom;">
	        <a onclick="removeDependencyRightClick();" <?php echo formatMediumButton('Remove') ;?></a> 
	      </td>
	    </tr>
    </table>  
  </form>		  
</div>