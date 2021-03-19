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
$obj = new Asset($id);
$currentLine = $id;

echo '<table id="assetStructure" width="100%"" style="min-width:400px">';
echo '<TR>';
echo '  <TD class="reportTableHeaderAsset" style="width:48%;text-align: center;">' . i18n('colName') . '</TD>';
echo '  <TD class="reportTableHeaderAsset" style="width:10%;"  >' . i18n('colAssetType') . '</TD>' ;
echo '  <TD  class="reportTableHeaderAsset" style="width:10%;" >';
echo '    <table style="height:100%;" width="100%"> 
            <tr style="height:50%;border-bottom:solid 0.5px #AAAAAA;"> <td class="reportTableHeaderAsset"> '.i18n('colBrand').'</td> <tr> 
            <tr style="height:50%;"> <td class="reportTableHeaderAsset">'.i18n('colModel').'</td> <tr>
          </table>';
echo '  </TD>';
echo '  <TD class="reportTableHeaderAsset" style="width:10%;" >' . i18n('colUser') . '</TD>' ;
echo '  <TD  class="reportTableHeaderAsset" style="width:12%;" >';
echo '    <table width="100%">
            <tr style="height:50%;border-bottom:solid 0.5px #AAAAAA;"> <td class="reportTableHeaderAsset"> '.i18n('colSerialNumber').'</td> <tr>
            <tr style="height:50%;"> <td class="reportTableHeaderAsset">'.i18n('colInventoryNumber').'</td> <tr>
          </table>';
echo '  </TD>';
echo '  <TD class="reportTableHeaderAsset" style="width:10%;" >' . i18n('colIdStatus') . '</TD>' ;
echo '</TR>';
echo '</table>';

echo '<div id="assetStructureListDiv" style="position:relative;height:600px;width:100%;min-width:400px;">';
echo '<table id="dndassetStructureList" id="dndassetStructure" align="left" width="100%" style="table-layout:fixed;">';
$parentAsset=array();
$subAsset=array();

$parentAsset=$obj->getParentAsset();
$subAsset=$obj->getRecursiveSubAsset();

$level=0;
foreach ($parentAsset as $parentId=>$parentName) {
  $level++;
  showAsset($parentId,$parentName,$level,'top');
}
$level++;
showAsset($obj->id,$obj->name,$level,'current');
showSubItems($subAsset,$level+1);


echo "</table>";
echo '</div>';

function showSubItems($subItems,$level){
  if (!$subItems) return;
  foreach ($subItems as $item) {
    showAsset($item['id'],$item['name'],$level,'sub');
    if (isset($item['subItems']) and is_array($item['subItems'])) {
      showSubItems($item['subItems'],$level+1);
    }
  }
}


function showAsset($id,$name,$level,$position) {
  global $showVersionsForAll, $showProjectsLinked, $showClosedItems, $currentLine;
  $rowType  = "row";
  $display='';
  $compStyle="";
  $padding=16;
  if($level==1)$padding=5;
  $name="#$id - $name";
  $style="";
  $current=($position=='current');
  $item=new Asset($id);
  $isElementary = $item->isElementary();
  $limitedSubAsset = array();
  if( !$isElementary) {
    $rowType = "group";
    $asset = new Asset();
    $subList = $asset->getSqlElementsFromCriteria(array('idAsset'=>$id));
    foreach ($subList as $id=>$obj){
      $limitedSubAsset[]=$obj->id;
    }
    $subBudget=array();
    getSubAssetList($subList, $subBudget);
      $class = 'ganttExpandOpened';
  }
  if($currentLine==$item->id){
    $style='background-color:#ffffaa;';
  }
  echo '<TR id="assetStructureRow_'.$item->id.'" height="40px" '.$display.'>';
  echo '  <TD class="ganttName reportTableData" style="'.$style.'width:48%;' . $compStyle . '">';
  echo '    <span>';
  echo '      <table><tr>';
  echo '<TD>';
  if(!$isElementary){
    echo '     <div id="group_'.$item->id.'" class="'.$class.'"';
    echo '      style="word-wrap: break-word; margin-left:'.(($level-1)*$padding+5).'px; position: relative; z-index: 100000;   width:16px; height:13px;"';
    echo '      onclick="expandAssetGroup(\''.$item->id.'\',\''.implode(',', $limitedSubAsset).'\',\''.implode(',', $subBudget).'\');">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
  }else{
     echo '     <div id="group_'.$item->id.'" class="ganttAsset"';
     echo '      style="'.$style.'word-wrap: break-word; margin-left:'.(($level-1)*$padding+5).'px; position: relative; z-index: 100000;   width:16px; height:13px;"</div>';
  }
  echo '</TD>';
  $goto = "";
  if (securityCheckDisplayMenu(null,'Asset') and securityGetAccessRightYesNo('menu'.get_class($item), 'read', '')=="YES") {
    $goto=' onClick="top.gotoElement(\''.get_class($item).'\',\''.htmlEncode($item->id).'\');window.top.dijit.byId(\'dialogPrint\').hide();" style="cursor: pointer;" ';
  }
  echo '       <TD '.$goto.' style="'.$style.'padding-bottom:5px;" class="'.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'"><div class="amountTableDiv">#'.htmlEncode($item->id).'  '.htmlEncode($item->name). '</div></TD>' ;
  echo '      </tr></table>';
  echo '    </span>';
  echo '  </TD>';
  
  
  echo '  <TD class="ganttName reportTableData amountTableTD" style="'.$style.'width:10%;overflow:auto;"><div style="word-wrap: break-word;" class="amountTableDiv">' .SqlList::getNameFromId('Type', $item->idAssetType). '</div></TD>' ;
  echo '  <TD class="ganttName reportTableData amountTableTD" style="'.$style.'width:10%;overflow:auto;">';
  echo ' <table width="100%" height="100%"> <tr style="height:50%;border-bottom:solid 0.5px #AAAAAA;"> <td> '.SqlList::getNameFromId('Brand', $item->idBrand).'</td> <tr> <tr style="height:50%;"> <td>'.SqlList::getNameFromId('Model', $item->idModel).'</td> <tr> </table>';
  echo '  </TD>';
  echo '  <TD class="ganttName reportTableData amountTableTD" style="'.$style.'width:10%;overflow:auto;"><div style="word-wrap: break-word;" class="amountTableDiv">' .SqlList::getNameFromId('Affectable', $item->idAffectable). '</div></TD>' ;
  
  echo '  <TD class="ganttName reportTableData amountTableTD" style="'.$style.'width:12%;overflow:auto;">';
  echo ' <table width="100%" height="100%"> <tr style="height:50%;border-bottom:solid 0.5px #AAAAAA;"> <td> '.htmlEncode($item->serialNumber).'</td> <tr> <tr style="height:50%;"> <td>'.htmlEncode($item->inventoryNumber).'</td> <tr> </table>';
  echo '  </TD>';
  $objStatus=new Status($item->idStatus);
  echo '  <TD class="ganttName  reportTableData amountTableTD" style="width:10%;"><div style="word-wrap: break-word; height:100%; overflow:auto;" class="amountTableDiv">'.colorNameFormatter($objStatus->name."#split#".$objStatus->color).'</div></TD>' ;
}


function getSubAssetList($subList, &$subBudget){
  foreach ($subList as $id=>$obj){
    $subBudget[]=$obj->id;
    $asset = new Asset();
    $resubList = $asset->getSqlElementsFromCriteria(array('idAsset'=>$obj->id));
    getSubAssetList($resubList, $subBudget);
  }
}