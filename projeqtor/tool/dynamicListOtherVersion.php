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

/** ============================================================================
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicLisOtherVersion.php');
$refType=$_REQUEST['otherVersionRefType'];
Security::checkValidClass($refType);

$refId=$_REQUEST['otherVersionRefId'];
$versionType=$_REQUEST['otherVersionType'];
Security::checkValidClass($versionType);


//otherVersionId
$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
  $selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected);

$obj=new $refType($refId);

$list=array();
$proj=null;
$prod=null;
if (property_exists($refType, "idProject")) {
	$proj=$obj->idProject;
}
if (property_exists($refType, "idProject")) {
  $proj=$obj->idProject;
}

$versionObjet='Version';
if (substr($versionType,-16)=='ComponentVersion') $versionObjet='ComponentVersion';
else if (substr($versionType,-14)=='ProductVersion') $versionObjet='ProductVersion';

$varProd='idProduct';
if (property_exists($refType, "idProduct") and $versionObjet=='ProductVersion') {
  $prod=$obj->idProduct;
  $varProd='idProduct';
} else if (property_exists($refType, "idComponent") and $versionObjet=='ComponentVersion') {
  $prod=$obj->idComponent;
  $varProd='idComponent';
} else if (property_exists($refType, "idProductOrComponent")) {
  $prod=$obj->idProductOrComponent;
  $varProd='idProductOrComponent';
}
$crit=array();
if ($prod) {
  $crit=array( $varProd=>$prod);
} else if ($proj) { 
	$crit=array( 'idProject'=>$proj);
}

$list=SqlList::getListWithCrit($versionObjet, $crit);
?>
<select id="otherVersionIdVersion" size="14"" name="otherVersionIdVersion[]" multiple
onchange="selectOtherVersionItem();"  ondblclick="saveOtherVersion();"
class="selectList" >
 <?php
 $found=array();
 foreach ($list as $id=>$lst) {
   $sel="";
   if (in_array($id,$selectedArray)) {
    $sel=" selected='selected' ";
    $found[$id]=true;
   }
   echo "<option value='$id'" . $sel . ">#$id - ".htmlEncode($lst)."</option>";
 }
 foreach ($selectedArray as $selected) {
	 if ($selected and ! isset($found[$selected]) ) {
	   $lstObj=new $versionType($selected);
	   echo "<option value='$lstObj->id' selected='selected' >#".htmlEncode($lstObj->id)." - ".htmlEncode($lstObj->name)."</option>";
	 }
 }
 ?>
</select>