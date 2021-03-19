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

$format="print";
if (array_key_exists('format', $_REQUEST)){
  $format=trim($_REQUEST['format']);
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
if ($objectClass=='Product') {
  $parentProducts=$item->getParentProducts();
}
$result=array();
$result=getSubItems($item,$result);

if ($format=='print') {
  echo "<table style='width:100%'>";
  echo "<tr><td style='width:50%;vertical-align:top;padding:5px;'>";
  // Items
  echo "<table style='width:100%;'>";
  echo "<tr><th style='padding:5px;text-align:center;'>".i18n('sectionComposition',array(i18n($objectClass),intval($objectId).' - '.$item->name)).'</th></tr>';
  foreach ($result as $item) {
    echo "<tr><td>";
    showProduct($item['class'], $item['id'], $item['name']);
    echo "</td></tr>";
  }
  echo "</table>";
  echo "</td><td style='width:50%;vertical-align:top;padding:5px;'>";
  // Parents  
  /*echo "<table style='width:100%;'>";
  echo "<tr><th style='padding:5px;text-align:center;'>".i18n('parentProductList').'</th></tr>';
  foreach ($parentProducts as $prdId=>$prdName) {
    echo "<tr><td>";
    showProduct('Product', $prdId, $prdName);
    echo "</td></tr>";
  }
  echo "</table>";*/
  echo "</td></tr>";
  echo "</table>";
} else if ($format=='csv') {
  echo "Class;Id;Name\n";
  foreach ($result as $item) {
    echo $item['class'].';'.$item['id'].';'.$item['name']."\n";
  }
} else {
  errorLog("productFlatStructure : incorrect format '$format'");
  if (!empty($cronnedScript)) goto end; else exit;
}

function getSubItems($item,$result){
  global $showClosedItems;
  if (get_class($item)=='Product' and Parameter::getGlobalParameter('includeProductInProductStructure')!='NO') {
    $crit=array('idProduct'=>$item->id);
    $lst=$item->getSqlElementsFromCriteria($crit);
    foreach ($lst as $prd) {
      $result[$prd->id]=array('class'=>'Product','id'=>$prd->id,'name'=>$prd->name);
      $result=getSubItems($prd,$result);
    }
  }
  $ps=new ProductStructure();
  $psList=$ps->getSqlElementsFromCriteria(array('idProduct'=>$item->id));
  foreach ($psList as $ps) {
    $comp=new Component($ps->idComponent);
      if(!$showClosedItems){
        if($comp->idle == 0){
          $result[$ps->idComponent]=array('class'=>get_class($comp),'id'=>$comp->id,'name'=>$comp->name);
          $result=getSubItems($comp,$result);
        }
      }else{
        $result[$ps->idComponent]=array('class'=>get_class($comp),'id'=>$comp->id,'name'=>$comp->name);
        $result=getSubItems($comp,$result);
      }
    }
  return $result;
}


function showProduct($class,$id,$name) {
  $name="#$id - $name";
  $style="width:100%";
  $item=new $class($id);
  echo '<table style="'.$style.'"><tr><td style="padding-left:5px;padding-top:2px;width:20px;" class="icon'.$class.'16" />&nbsp;</td>'
      .'<td style="padding:0px 5px;vertical-align:middle;">'.$name.'</td></tr></table>';
}

end:
