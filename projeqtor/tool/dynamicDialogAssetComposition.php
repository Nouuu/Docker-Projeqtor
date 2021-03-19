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

include_once("../tool/projeqtor.php");
$objectClass = 'Asset';
$structureId = RequestHandler::getId('idParent');
$object=new Asset($structureId);
?>
<table>
  <tr>
    <td>
      <form id='assetCompositionForm' name='assetCompositionForm' onSubmit="return false;">
        <?php 
        $canCreate=securityGetAccessRightYesNo('menuAsset', 'create') == "YES";
        ?>
        <input id="idParent" name="idParent" type="hidden" value="<?php echo $structureId;?>" />
        <input id="directAccessToListButton" name="directAccessToListButton" type="hidden" value="dialogAssetCompositionSubmit" />
        <input id="assetCanCreateProduct" type="hidden" value="<?php echo $canCreate;?>" />
        <table>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr><td colspan="2" class="section"><?php echo i18n('sectionAssetComposition',array(i18n($objectClass),intval($object->id).' '.$object->name));?></td></tr>  
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
          <tr>
            <td class="dialogLabel"  >
              <label for="assetStuctureListId" ><?php echo i18n('Asset') ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <select size="14" id="assetStuctureListId" name="assetStuctureListId[]"
                <?php if (!$structureId) echo 'multiple';?> class="selectList" onchange="enableWidget('dialogProductStructureSubmit');"  ondblclick="saveAssetComposition();" value="">
                <?php htmlDrawOptionForReference('idAsset', null, $object, true,'idAsset','');?>
              </select>
            </td>
            <td style="vertical-align: top">
              <div style="position:relative">
              <button id="assetStructureDetailButton" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('showDetail'). ' '. i18n('Asset')?>"
                iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                <script type="dojo/connect" event="onClick" args="evt">
                <?php if (!$canCreate) $canCreate=0;?>
                showDetail('assetStuctureListId', <?php echo $canCreate;?>, 'Asset', true);
                </script>
              </button>
              </div>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
        </table>
        <table>  
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td align="center">
      <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogAssetComposition').hide();">
        <?php echo i18n("buttonCancel");?>
      </button>
      <button class="mediumTextButton" <?php if (!$structureId) echo 'disabled';?> dojoType="dijit.form.Button" type="submit" id="dialogAssetCompositionSubmit" onclick="protectDblClick(this);saveAssetComposition();return false;">
        <?php echo i18n("buttonOK");?>
      </button>
    </td>
  </tr>
</table>