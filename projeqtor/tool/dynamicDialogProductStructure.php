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

if (! array_key_exists('objectClass',$_REQUEST)) {
  throwError('Parameter objectClass not found in REQUEST');
}
$objectClass=$_REQUEST['objectClass'];
Security::checkValidClass($objectClass);

if (! array_key_exists('objectId',$_REQUEST)) {
  throwError('Parameter objectId not found in REQUEST');
}
$objectId=$_REQUEST['objectId'];
Security::checkValidId($objectId);

$structureId=null;
if (array_key_exists('structureId',$_REQUEST)) {
	$structureId=$_REQUEST['structureId'];
	Security::checkValidId($structureId);
}

$way=null;
if (array_key_exists('way',$_REQUEST)) {
  $way=$_REQUEST['way'];
}
if ($way!='structure' and $way!='composition') {
  throwError("Incorrect value for parameter way='$way'");
}

$str=new ProductStructure($structureId);

if ($objectClass=='Product') {
  $listClass='Component';
  $listId=$str->idComponent;
} else if ($objectClass=='Component') {
  if ($way=='structure') {
    $listClass='ProductOrComponent';
    $listId=$str->idProduct;
  } else {
    $listClass='Component';
    $listId=$str->idComponent;
  }
} else {
  errorLog("Unexpected objectClass $objectClass");
  echo "Unexpected objectClass";
  exit;
}
$object=new $objectClass($objectId);

$directAccessToList='false';
if ($way=='composition') {
	$paramDirect=Parameter::getUserParameter('directAccessToComponentList');
	if ($paramDirect=='YES') {
		$directAccessToList='true';
	}
}

?>
<table>
  <tr>
    <td>
      <form id='productStructureForm' name='productStructureForm' onSubmit="return false;">
        <?php 
        $canCreateProduct=securityGetAccessRightYesNo('menuProduct', 'create') == "YES";
        $canCreateComponent=securityGetAccessRightYesNo('menuComponent', 'create') == "YES";
        ?>
        <input id="productStructureObjectClass" name="productStructureObjectClass" type="hidden" value="<?php echo $objectClass;?>" />
        <input id="productStructureObjectId" name="productStructureObjectId" type="hidden" value="<?php echo $objectId;?>" />
        <input id="productStructureListClass" name="productStructureListClass" type="hidden" value="<?php echo $listClass;?>" />
        <input id="productStructureId" name="productStructureId" type="hidden" value="<?php echo $structureId;?>" />
        <input id="productStructureWay" name="productStructureWay" type="hidden" value="<?php echo $way;?>" />
        <input id="directAccessToList" name="directAccessToList" type="hidden" value="<?php echo $directAccessToList;?>" />
        <input id="directAccessToListButton" name="directAccessToListButton" type="hidden" value="dialogProductStructureSubmit" />
        <input id="productStructureCanCreateProduct" type="hidden" value="<?php echo $canCreateProduct;?>" />
        <input id="productStructureCanCreateComponent" type="hidden" value="<?php echo $canCreateComponent;?>" />
        <table>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr><td colspan="2" class="section"><?php echo i18n('section'.ucfirst($way),array(i18n($objectClass),intval($objectId).' '.$object->name));?></td></tr>  
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
          <tr>
            <td class="dialogLabel"  >
              <label for="productStructureListId" ><?php echo i18n($listClass) ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <select size="14" id="productStructureListId" name="productStructureListId[]"
                <?php if (!$structureId) echo 'multiple';?> class="selectList" onchange="enableWidget('dialogProductStructureSubmit');"  ondblclick="saveProductStructure();" value="">
                  <?php htmlDrawOptionForReference('id'.$listClass, $listId, null, true);?>
              </select>
            </td>
            <td style="vertical-align: top">
              <?php if ($way=='structure') {?>
              <div style="position:relative">
              <button id="productStructureDetailButtonProduct" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('showDetail') . ' '. i18n('Product');?>"
                iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                <script type="dojo/connect" event="onClick" args="evt">
                <?php if (!$canCreateProduct) $canCreateProduct=0;?>
                showDetail('productStructureListId', <?php echo $canCreateProduct;?>, 'Product', true);
                </script>
              </button>
              <img style="position:absolute;right:-5px;top:0px;height:12px;" src="../view/css/images/iconProduct16.png" />
              </div>
              <?php }?>
              <div style="position:relative">
              <button id="productStructureDetailButtonComponent" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('showDetail'). ' '. i18n('Component')?>"
                iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                <script type="dojo/connect" event="onClick" args="evt">
                <?php if (!$canCreateComponent) $canCreateComponent=0;?>
                showDetail('productStructureListId', <?php echo $canCreateComponent;?>, 'Component', true);
                </script>
              </button>
              <img style="position:absolute;right:-5px;top:0px;height:12px;" src="../view/css/images/iconComponent16.png" />
              </div>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
        </table>
        <table>  
          <tr>
            <td class="dialogLabel" >
              <label for="productStructureComment" ><?php echo i18n("colComment") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <textarea dojoType="dijit.form.Textarea"
                id="productStructureComment" name="productStructureComment"
                style="width: 400px;"
                maxlength="4000"
                class="input"><?php echo $str->comment;?></textarea>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td align="center">
      <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogProductStructure').hide();">
        <?php echo i18n("buttonCancel");?>
      </button>
      <button class="mediumTextButton" <?php if (!$structureId) echo 'disabled';?> dojoType="dijit.form.Button" type="submit" id="dialogProductStructureSubmit" onclick="protectDblClick(this);saveProductStructure();return false;">
        <?php echo i18n("buttonOK");?>
      </button>
    </td>
  </tr>
</table>