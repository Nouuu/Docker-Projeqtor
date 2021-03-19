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
if ($objectClass!='ProductVersion' and $objectClass!='ComponentVersion') return;

$format="print";
if (array_key_exists('format', $_REQUEST)){
  $format=trim($_REQUEST['format']);
}
$item=new $objectClass($objectId);
$canRead=securityGetAccessRightYesNo('menu' . $objectClass, 'read', $item)=="YES";
if (!$canRead) if (!empty($cronnedScript)) goto end; else exit;

if ($objectClass=='VersionProduct') {
  //$parentVersionProducts=$item->getParentProducts();
}
$result=array();
$result=getSubItems($item,$result);


if ($format=='print') {
  echo '<table style="width:100%;table-layout: fixed;"><tr>';
  echo '<td class="linkHeader" style="width:8%">'.i18n('colId').'</td>';
  echo '<td class="linkHeader" style="width:40%">'.i18n('colName').'</td>';
  echo '<td class="linkHeader" style="width:14%">'.i18n('colResponsible').'</td>';
  echo '<td class="linkHeader" style="width:14%">'.i18n('colType').'</td>';
  echo '<td class="linkHeader" style="width:8%">'.i18n('colPeriodicityStartDate').'</td>';
  echo '<td class="linkHeader" style="width:8%">'.i18n('colDeliveryDate').'</td>';
  echo '<td class="linkHeader" style="width:8%">'.i18n('colDoneDate').'</td>';
  echo '</tr>';
  foreach ($result as $item) {
    echo "<tr>";
    showProduct($item);
    echo "</tr>";
  }
  echo '</table>';
} else if ($format=='csv' and Parameter::getGlobalParameter("displayMilestonesStartDelivery") != 'YES') {
  echo "Class;Id;Name\n";
  foreach ($result as $item) {
    echo $item['class'].';'.$item['id'].';'.$item['name']."\n";
  }
} else if ($format=='csv') {
  echo "Class;Id;Type;Name;Responsible;Real delivery date;Planned delivery date;Initial delivery date\n";
  foreach ($result as $item) {
    echo $item['class'].';'.$item['id'].';'.$item['type'].';'.$item['name'].';'.$item['idResource'].';'.$item['startDate'].';'.$item['deliveryDate'].';'.$item['endDate']."\n";
  }
  //END CHANGE qCazelles - DeliveryDateXLS - Ticket #126
} else {
  errorLog("productStructure : incorrect format '$format'");
  if (!empty($cronnedScript)) goto end; else exit;
}

function getSubItems($item,$result){
  $ps=new ProductVersionStructure();
  $psList=$ps->getSqlElementsFromCriteria(array('idProductVersion'=>$item->id));
  foreach ($psList as $ps) {
    $comp=new ComponentVersion($ps->idComponentVersion);
    $deliveryDate= ($comp->realDeliveryDate?$comp->realDeliveryDate:($comp->plannedDeliveryDate?$comp->plannedDeliveryDate:($comp->initialDeliveryDate?$comp->initialDeliveryDate:"")));
    $startDate= ($comp->realStartDate?$comp->realStartDate:($comp->plannedStartDate?$comp->plannedStartDate:($comp->initialStartDate?$comp->initialStartDate:"")));
    $endDate= ($comp->realEndDate?$comp->realEndDate:($comp->plannedEndDate?$comp->plannedEndDate:($comp->initialEndDate?$comp->initialEndDate:"")));
    $typeObj = new type();
    $typeObj=$typeObj->getSingleSqlElementFromCriteria('type', array('id'=>$comp->idVersionType));
    $resourceObj = new resource();
    $resourceObj=$resourceObj->getSingleSqlElementFromCriteria('resource', array('id'=>$comp->idResource));
    $result[$ps->idComponentVersion]=array('class'=>get_class($comp),'id'=>$comp->id,'name'=>$comp->name,'deliveryDate'=>$deliveryDate,'startDate'=>$startDate,'endDate'=>$endDate,'idResource'=>$resourceObj->name, 'type'=>$typeObj->name);
    $result=getSubItems($comp,$result);
  }
  return $result;
}

//CHANGE by qCazelles - dateComposition
//Old
//function showProduct($class,$id,$name) {
//New
function showProduct($item) {
  $class=$item['class'];
  $id=$item['id'];
  $name=$item['name'];
  $resource=$item['idResource'];
  $deliveryDate=$item['deliveryDate'];
  $startDate=$item['startDate'];
  $endDate=$item['endDate'];
  $type=$item['type'];
  
  echo '<tr><td class="linkData" style="white-space:nowrap;width:15%"><table><tr>';
  echo '<td>'.formatIcon($class, 16).'</td><td style="vertical-align:top">&nbsp;'.'#'.$id.'</td></tr></table>';
  echo '</td>';
  echo '<td class="linkData">'.$name.'</td>';
  echo '<td class="linkData">'.$resource.'</td>';
  echo '<td class="linkData">'.$type.'</td>';
  echo '<td class="linkData">'.$startDate.'</td>';
  echo '<td class="linkData">'.$deliveryDate.'</td>';
  echo '<td class="linkData">'.$endDate.'</td>';
}

end:
