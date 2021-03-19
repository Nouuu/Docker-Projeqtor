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
if($objectClass=='Asset'){
  $way='structure';
}
  if (array_key_exists('way',$_REQUEST)) {
    $way=$_REQUEST['way'];
  }
  if ($way!='structure' and $way!='composition') {
    throwError("Incorrect value for parameter way='$way'");
  }
$str=new ProductVersionStructure($structureId);

if ($objectClass=='ProductVersion') {
  $listClass='ComponentVersion';
  $listId=$str->idComponentVersion;
} else if ($objectClass=='ComponentVersion')  {
  if ($way=='structure') {
    $listClass='ProductVersion';
    $listId=$str->idProductVersion;
  } else {
    $listClass='ComponentVersion';
    $listId=$str->idComponentVersion;
  }
//gautier #4404
}else if($objectClass=='Asset'){
  if ($way=='structure') {
    $listClass='ProductVersion';
    $listId=$str->idProductVersion;
  } else {
    $listClass='ComponentVersion';
    $listId=$str->idComponentVersion;
  }
} else {
  errorLog("Unexpected objectClass $objectClass");
  echo "Unexpected objectClass";
  exit;
}
$object=new $objectClass($objectId);
if ($way=='structure') {
  $critClass='ComponentVersion';
} else {
  $critClass='ProductVersion';
}
//ADD qCazelles - Ticket 165
$directAccessToList='false';
if ($way=='composition') {
  $paramDirect=Parameter::getUserParameter('directAccessToComponentList');
  if ($paramDirect=='YES') {
    $directAccessToList='true';
  }
}
if ($way=='composition' and $directAccessToList=='true') {
  $user=getSessionUser();
  if ($objectClass=='ProductVersion') {
    $productOrComponent=new Product($object->idProduct);
  } else {
    $productOrComponent=new Component($object->idComponent);
  }
  $cvListId='(';
  $cvListName='';
  foreach ($productOrComponent->getComposition(false) as $idComponent) {
    $cvs=new ComponentVersion();
    foreach ($cvs->getSqlElementsFromCriteria(array('idComponent' => $idComponent)) as $cv) {
      $cvListId.=$cv->id.', ';
      $cvListName.="'".$cv->name."', ";
    }
  }
  $cvListId=substr($cvListId, 0, -2).')';
  $cvListName=substr($cvListName, 0, -2);
  if (!isset($user->_arrayFiltersDetail['ComponentVersion'])) {
    $user->_arrayFiltersDetail['ComponentVersion']=array();
    $index=0;
  } else if (count($user->_arrayFiltersDetail['ComponentVersion'])==0) {
    $index=0;
  } else {
    $index=max(array_keys($user->_arrayFiltersDetail['ComponentVersion']))+1;
  }
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['disp']['attribute']=i18n('colIdComponentVersion');
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['disp']['operator']=i18n('amongst');
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['disp']['value']=$cvListName;
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['sql']['attribute']='id';
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['sql']['operator']='IN';
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['sql']['value']=$cvListId;
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['isDynamic']="0";
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['orOperator']="0";
  $user->_arrayFiltersDetail['ComponentVersion'][$index]['hidden']="1";
  setSessionUser($user);
} 
//END ADD qCazelles - Ticket 165
?>
<table>
  <tr>
    <td>
      <form id='productVersionStructureForm' name='productVersionStructureForm' onSubmit="return false;">
        <input id="productVersionStructureObjectClass" name="productVersionStructureObjectClass" type="hidden" value="<?php echo $objectClass;?>" />
        <input id="productVersionStructureObjectId" name="productVersionStructureObjectId" type="hidden" value="<?php echo $objectId;?>" />
        <input id="productVersionStructureListClass" name="productVersionStructureListClass" type="hidden" value="<?php echo $listClass;?>" />
        <input id="productVersionStructureId" name="productVersionStructureId" type="hidden" value="<?php echo $structureId;?>" />
        <input id="productVersionStructureWay" name="productVersionStructureWay" type="hidden" value="<?php echo $way;?>" />
        <?php //ADD qCazelles - Ticket 165 ?>
        <input id="directAccessToList" name="directAccessToList" type="hidden" value="<?php echo $directAccessToList;?>" />
        <input id="directAccessToListButton" name="directAccessToListButton" type="hidden" value="dialogProductVersionStructureSubmit" />
        <?php //END ADD qCazelles - Ticket 165 ?>
        <table>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr><td colspan="2" class="section"><?php echo i18n('sectionVersion'.ucfirst($way),array(i18n($objectClass),intval($objectId).' '.$object->name));?></td></tr>  
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        <?php if (0) {?>
          <tr>
            <td class="dialogLabel">
              <label for="productVersionStructureHideInService" class="nobr">&nbsp;&nbsp;<?php echo i18n("hideInService");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td><?php $hideInService=Parameter::getUserParameter('hideInService');?>
              <div title="<?php echo i18n('hideInService')?>" dojoType="dijit.form.CheckBox" 
                class="" <?php if ($hideInService=='true') echo " checked ";?>
                type="checkbox" id="productVersionStructureHideInService" name="productVersionStructureHideInService">
                <script type="dojo/method" event="onChange" >
                  saveDataToSession('hideInService',((this.checked)?true:false),true);
                  refreshList('id<?php echo $listClass;?>',null, null, null,'productVersionStructureListId', true);
                </script>
              </div>&nbsp;
            </td>
          </tr>
       <?php }?>     
          <tr>
            <td class="dialogLabel"  >
              <label for="productVersionStructureListId" ><?php echo i18n($listClass) ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <?php $listWidth=getSessionValue('screenWidth',800)/2;?>             
              <select size="14" id="productVersionStructureListId" name="productVersionStructureListId[]" style="width:<?php echo $listWidth;?>px"
                <?php if (!$structureId) echo 'multiple';?> class="selectList" onchange="enableWidget('dialogProductVersionStructureSubmit');"  ondblclick="<?php if($objectClass=='Asset'){?>saveProductVersionStructureAsset();<?php }else{?>saveProductVersionStructure();<?php }?>" value="">
                  <?php 
                    if (!$structureId) { 
                      if($objectClass =='Asset'){
                        htmlDrawOptionForReference('idversion', $listId, null, true);
                      }else{
                        htmlDrawOptionForReference('id'.$listClass, $listId, null, true, 'id'.$critClass, $objectId);
                      }
                    } else {
                      $compVers=new ComponentVersion($str->idComponentVersion);
                      htmlDrawOptionForReference('id'.$listClass, $listId, null, true, 'idComponent', $compVers->idComponent);
                    }?>
              </select>
            </td>
            <td style="vertical-align: top">
              <?php if ($way=='structure') {?>
              <div style="position:relative">
              <button id="productVersionStructureDetailButtonProduct" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('showDetail') . ' '. i18n('ProductVersion');?>"
                iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                <script type="dojo/connect" event="onClick" args="evt">
                <?php $canCreate=securityGetAccessRightYesNo('menuProductVersion', 'create') == "YES"; ?>
                showDetail('productVersionStructureListId', <?php echo $canCreate;?>, 'ProductVersion', true);
                </script>
              </button>
              <img style="position:absolute;right:-5px;top:0px;height:12px;" src="../view/css/images/iconProductVersion16.png" />
              </div>
              <?php }?>
              <div style="position:relative">
              <button id="productVersionStructureDetailButtonComponent" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('showDetail'). ' '. i18n('Component')?>"
                iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                <script type="dojo/connect" event="onClick" args="evt">
                <?php $canCreate=securityGetAccessRightYesNo('menuComponentVersion', 'create') == "YES"; ?>
                showDetail('productVersionStructureListId', <?php echo $canCreate;?>, 'ComponentVersion', true);
                </script>
              </button>
              <img style="position:absolute;right:-5px;top:0px;height:12px;" src="../view/css/images/iconComponentVersion16.png" />
              </div>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
        </table>
        <table>  
          <tr>
            <td class="dialogLabel" >
              <label for="productVersionStructureComment" ><?php echo i18n("colComment") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <textarea dojoType="dijit.form.Textarea"
                id="productVersionStructureComment" name="productVersionStructureComment"
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
      <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogProductVersionStructure').hide();">
        <?php echo i18n("buttonCancel");?>
      </button>
      <button class="mediumTextButton" <?php if (!$structureId) echo 'disabled';?> dojoType="dijit.form.Button" type="submit" id="dialogProductVersionStructureSubmit" onclick="protectDblClick(this);<?php if($objectClass=='Asset'){?>saveProductVersionStructureAsset();<?php }else{?>saveProductVersionStructure();<?php }?>return false;">
        <?php echo i18n("buttonOK");?>
      </button>
    </td>
  </tr>
</table>