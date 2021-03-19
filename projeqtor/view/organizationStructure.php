<?php
use PhpOffice\PhpPresentation\Shape\Line;
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

/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
include_once '../tool/projeqtor.php';
include_once '../tool/formatter.php';
?>
<script type="text/javascript" src="js/projeqtor.js?version=<?php echo $version.'.'.$build;?>"></script> <?php 
$id = RequestHandler::getId('id');
$obj = new Organization($id);
$currentLine = $id;
$showClose=false;
if(sessionValueExists('showIdleOrganizationStructure')){
  $showClose = getSessionValue('showIdleOrganizationStructure');
  if($showClose=="true"){
    $showClose=true;
  }else{
    $showClose=false;
  }
}

echo '<Table id="organizationStructure" align="left" width="100%" style="min-width:400px">';
echo '<TR class="ganttHeight" style="height:32px">';
echo '  <TD class="reportTableHeader" style="width:60%;border-left:0px; text-align: center;">'.i18n('colName') .'</TD>';
echo '  <TD class="reportTableHeader amountTableHeaderTD" style="width:20%;"  ><div class="amountTableHeaderDiv">' . i18n('colIdOrganizationType') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" style="width:20%;" ><div class="amountTableHeaderDiv">' . i18n('colManager') . '</div></TD>' ;
echo '</TR>';
echo '</table>';

echo '<div id="organizationStructureListDiv" style="position:relative;height:600px;width:100%;min-width:400px;">';
echo '<table id="dndorganizationStructureList" id="dndorganizationStructure" align="left" width="100%" style="table-layout:fixed;">';
$parentOrganization=array();
$subOrganization=array();

$parentOrganization=$obj->getParentOrganizationStructure();
$subOrganization=$obj->getRecursiveSubOrganizationStructure($showClose);

$level=0;
foreach ($parentOrganization as $parentId=>$parentName) {
  $level++;
  showOrganization($parentId,$parentName,$level,'top', $showClose);
}
$level++;
showOrganization($obj->id,$obj->name,$level,'current', $showClose);
showSubItems($subOrganization,$level+1, $showClose);


echo "</table>";
echo '</div>';

function showSubItems($subItems,$level, $showClose){
  if (!$subItems) return;
  foreach ($subItems as $item) {
    showOrganization($item['id'],$item['name'],$level,'sub', $showClose);
    if (isset($item['subItems']) and is_array($item['subItems'])) {
      showSubItems($item['subItems'],$level+1, $showClose);
    }
  }
}


function showOrganization($id,$name,$level,$position, $showClose) {
  global $currentLine;
  $rowType  = "row";
  $display='';
  $compStyle="";
  
  $padding=16;
  if($level==1)$padding=5;
  $name="#$id - $name";
  $style="";
  $current=($position=='current');
  $item=new Organization($id);
  $isElementary = $item->isElementary($showClose);
  $limitedSubOrganization = array();
  if( !$isElementary) {
    $rowType = "group";
    $organization = new Organization();
    $subList = $organization->getSqlElementsFromCriteria(array('idOrganization'=>$id));
    foreach ($subList as $id=>$obj){
      $limitedSubOrganization[]=$obj->id;
    }
    $subOrganization=array();
    getSubOrganizationList($subList, $subOrganization);
      $class = 'ganttExpandOpened';
  }
  if($currentLine==$item->id){
    $style='background-color:#ffffaa;';
  }
  echo '<TR id="organizationStructureRow_'.$item->id.'" height="40px" '.$display.'>';
  echo '  <TD class="ganttName reportTableData" style="'.$style.'width:60%;max-width:60%;border-right:0px;' . $compStyle . '">';
  echo '    <span>';
  echo '      <table><tr>';
  echo '<TD>';
  if(!$isElementary){
    echo '     <div id="group_'.$item->id.'" class="'.$class.'"';
    echo '      style="'.$style.'word-wrap: break-word;margin-left:'.(($level-1)*$padding+5).'px; position: relative; z-index: 100000;   width:16px; height:13px;"';
    echo '      onclick="expandOrganizationGroup(\''.$item->id.'\',\''.implode(',', $limitedSubOrganization).'\',\''.implode(',', $subOrganization).'\');">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
  }else{
    echo '     <div id="group_'.$item->id.'" class="ganttOrganization"';
    echo '      style="'.$style.'word-wrap: break-word;margin-left:'.(($level-1)*$padding+5).'px;position: relative; z-index: 100000; width:16px; height:13px;"';
  }
  echo '</TD>';
  $goto = "";
  if (securityCheckDisplayMenu(null,'Organization') and securityGetAccessRightYesNo('menu'.get_class($item), 'read', '')=="YES") {
  	$goto=' onClick="top.gotoElement(\''.get_class($item).'\',\''.htmlEncode($item->id).'\');window.top.dijit.byId(\'dialogPrint\').hide();" style="cursor: pointer;" style="cursor: pointer;" ';
  }
  echo '       <TD '.$goto.' style="'.$style.'padding-bottom:5px;"><div class="amountTableDiv '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'">#'.htmlEncode($item->id).'  '.htmlEncode($item->name). '</div></TD>' ;
  echo '      </tr></table>';
  echo '    </span>';
  echo '  </TD>';
  echo '  <TD class="ganttName reportTableData amountTableTD" style="'.$style.'width:20%;overflow:auto;"><div class="amountTableDiv">' .SqlList::getNameFromId('Type', $item->idOrganizationType). '</div></TD>' ;
  echo '  <TD class="ganttName reportTableData amountTableTD" style="'.$style.'width:20%;overflow:auto;"><div class="amountTableDiv">' .SqlList::getNameFromId('Affectable', $item->idResource). '</div></TD>' ;
  echo '</TR>';
}


function getSubOrganizationList($subList, &$subOrganization){
  foreach ($subList as $id=>$obj){
    $subOrganization[]=$obj->id;
    $organization = new Organization();
    $resubList = $organization->getSqlElementsFromCriteria(array('idOrganization'=>$obj->id));
    getSubOrganizationList($resubList, $subOrganization);
  }
}