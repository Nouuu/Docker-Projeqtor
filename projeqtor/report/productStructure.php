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

//
// THIS IS THE PRODUCT STRUCTURE REPORT
//
include_once '../tool/projeqtor.php';
include_once '../tool/formatter.php';
$print=true;
$objectClass="";
if (array_key_exists('objectClass', $_REQUEST)){
  $objectClass=trim($_REQUEST['objectClass']);
}
Security::checkValidClass($objectClass);
$objectId="";
if (array_key_exists('objectId', $_REQUEST)){
  $objectId=trim($_REQUEST['objectId']);
}
Security::checkValidId($objectId);
if (!$objectClass or !$objectId) return;
if ($objectClass!='Product' and $objectClass!='Component') return;
$showVersionsForAll=false;
if (array_key_exists('showVersionsForAll', $_REQUEST)){
  if ($_REQUEST['showVersionsForAll']!='0') {
    $showVersionsForAll=true;
  }
}

$showProjectsLinked=true;
if (array_key_exists('showProjectsLinked', $_REQUEST)){
  if ($_REQUEST['showProjectsLinked']=='0') {
    $showProjectsLinked=false;  
  }
}
//gautier #2442
$showClosedItems=false;
if (array_key_exists('showClosedItems', $_REQUEST)){
  if ($_REQUEST['showClosedItems']!='0') {
    $showClosedItems=true;
  }
}
//end 
$item=new $objectClass($objectId);
$canRead=securityGetAccessRightYesNo('menu' . $objectClass, 'read', $item)=="YES";
if (!$canRead) if (!empty($cronnedScript)) goto end; else exit;

$subProducts=array();
$parentProducts=array();
if ($objectClass=='Product' and Parameter::getGlobalParameter('includeProductInProductStructure')!='NO') {
  $subProducts=$item->getRecursiveSubProducts();
  $parentProducts=$item->getParentProducts();
}
$level=0;
foreach ($parentProducts as $parentId=>$parentName) {
  $level++;
  showProduct('Product',$parentId,$parentName,$level,'top');
}
$level++;
showProduct($objectClass,$item->id,$item->name,$level,'current');
showSubItems('Product',$subProducts,$level+1);
function showSubItems($class,$subItems,$level){
  if (!$subItems) return;
  foreach ($subItems as $item) {
    showProduct($class,$item['id'],$item['name'],$level,'sub');
    if (isset($item['subItems']) and is_array($item['subItems'])) {
      showSubItems('Product',$item['subItems'],$level+1);
    }
  }
}

function showProduct($class,$id,$name,$level,$position) {
  global $showVersionsForAll, $showProjectsLinked, $showClosedItems;
  $padding=30;
  $name="#$id - $name";
  $style="";
  $current=($position=='current');
  $item=new $class($id);
  if ($current) $style.='border:2px solid #000;border-radius:5px;';
  echo '<div style="padding-bottom:5px;padding-left:'.($level*$padding).'px;">'
      .'<table style="border:1px dotted #ddd;width:100%"><tr><td style="vertical-align:top;width:10px;white-space:nowrap">'
      .'<table style="'.$style.'"><tr><td style="padding-left:5px;padding-top:2px;width:25px;" class="icon'.$class.'16" />&nbsp;&nbsp;&nbsp;</td>'
      .'<td style="padding:0px 5px;vertical-align:middle;">'.$name.'</td></tr></table>'
      .'</td>';
  
  if ($showVersionsForAll or $current) {
    echo '<td style="padding-top:5px;">';
    echo $item->drawSpecificItem('versions'.(($showProjectsLinked)?'WithProjects':''));
    echo "</td>";
  }

  echo'</tr>';
  echo'</table></div>';
  if ($position!='top') {
    $compList=$item->getComposition(true,false);
    foreach ($compList as $compId=>$compName) {
      //echo '<tr><td></td><td>';
      $compo = new Component($compId);
      if(!$showClosedItems){
        if($compo->idle == 0){
          showProduct('Component',$compId,$compName,$level+1,'sub');
        }
      }else{
        showProduct('Component',$compId,$compName,$level+1,'sub');
      }
      //echo '</td></tr>';
    }
  }
}

end:
