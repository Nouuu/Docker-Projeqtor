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
 * 
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicListDependency.php');
$refType=$_REQUEST['dependencyRefType'];
Security::checkValidClass($refType);

$refId=$_REQUEST['dependencyRefId'];
//$refTypeDep=SqlList::getNameFromId('Dependable', $_REQUEST['dependencyRefTypeDep']);
$refTypeDepObj=new Dependable($_REQUEST['dependencyRefTypeDep']);
$refTypeDep=$refTypeDepObj->name;
//$id=$_REQUEST['id'];
$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
	$selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected);

$crit = array ( 'idle'=>'0');

if ($refType) {
  $obj=new $refType($refId);
  if ($refTypeDep<>"Project") {
    $crit['idProject']=$obj->idProject;
  }
}

if (SqlElement::class_exists ($refTypeDep) ) {
  $objList=new $refTypeDep();
  
  $list=$objList->getSqlElementsFromCriteria($crit,false,null);
} else {
  $list=array();
}
if ($refType=="Project") {
  $wbsList=SqlList::getList('Project','sortOrder',null,true);
  $sepChar=Parameter::getUserParameter('projectIndentChar');
  if (!$sepChar) $sepChar='__';
  $wbsLevelArray=array();
}
?>
<select id="dependencyRefIdDep" size="14" name="dependencyRefIdDep[]" multiple
onchange="enableWidget('dialogDependencySubmit');" ondblclick="saveDependency();" 
<?php if (isNewGui()) echo ' style="width:410px;" ';?>
class="selectList" >
 <?php
 $found=array();
 foreach ($list as $lstObj) {
 	 $sel="";
 	 if (in_array($lstObj->id,$selectedArray)) {
 	 	$sel=" selected='selected' ";
 	 	$found[$lstObj->id]=true;
 	 }
 	 $val=$lstObj->name;
   if ($refType=="Project" and $sepChar!='no') {
     $wbs=(isset($wbsList[$lstObj->id]))?$wbsList[$lstObj->id]:'';
     $wbsTest=$wbs;
     $level=1;
     while (strlen($wbsTest)>3) {
       $wbsTest=substr($wbsTest,0,strlen($wbsTest)-6);
       if (array_key_exists($wbsTest, $wbsLevelArray)) {
         $level=$wbsLevelArray[$wbsTest]+1;
         $wbsTest="";
       }
     }
     $wbsLevelArray[$wbs]=$level;
     $sep='';for ($i=1; $i<$level;$i++) {$sep.=$sepChar;}
     $val = $sep.$val;
   }
   echo "<option value='$lstObj->id'" . $sel . ">#".htmlEncode($lstObj->id)." - ".htmlEncode($val)."</option>";
 }
 foreach ($selectedArray as $selected) {
	 if ($selected and ! isset($found[$selected]) ) {   
	 	 $lstObj=new $refTypeDep($selected);
	 	 $val=$lstObj->name;
	 	 echo "<option value='$lstObj->id' selected='selected' >#".htmlEncode($lstObj->id)." - ".htmlEncode($val)."</option>";
	 }
 }
 ?>
</select>