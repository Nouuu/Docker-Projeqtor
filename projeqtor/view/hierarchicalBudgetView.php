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

/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
include_once('../tool/formatter.php');
scriptLog('   ->/view/hierarchicalBudgetView.php');


$objectClass='Budget';
$obj=new $objectClass();
$table=$obj->getDatabaseTableName();
$hiddenRow = array();

if ( array_key_exists('print',$_REQUEST) ) {
	$print=true;
}

global $print;

$showFullAmount = false;
if(getSessionValue('showFullAmountHierarchicalBudget')){
  $showFullAmount=(getSessionValue('showFullAmountHierarchicalBudget')=='true')?true:false;
}else{
  $showFullAmount=(Parameter::getGlobalParameter('ImputOfAmountProvider')== 'HT')?false:true;
}
$showClosed=(getSessionValue('listShowIdleBudget')=='on')?true:false;

$budgetParent=trim(getSessionValue('listBudgetParentFilter'));

$idTab=0;

$querySelect = " * ";
$queryFrom = $table;
$queryWhere=($showClosed)?'1=1':'idle=0';
if ($budgetParent) {
  $budg = new Budget($budgetParent);
  $bbsSortable = $budg->bbsSortable;
  $queryWhere.= ' and bbsSortable like "'.$bbsSortable.'%"';
}
$queryOrderBy =" bbssortable ";

// constitute query and execute
$query='select ' . $querySelect
. ' from ' . $queryFrom
.' where ' . $queryWhere
. ' order by ' . $queryOrderBy;
$result=Sql::query($query);
$maxwidht = '';
if($print)$maxwidht='max-width:1350px;';
// Header
echo '<div id="hierarchicalBudgetListHeaderDiv"" style="'.$maxwidht.'">';
echo '<table id="hierarchicalBudgetListHeader" align="left" width="100%" style="min-width:'.((isNewGui())?1363:1350).'px;">';
echo '<TR class="ganttHeight" style="height:32px">';
$min=(isNewGui())?'33':'20';
echo '  <TD class="reportTableHeader" style="width:'.$min.'px;min-width:'.$min.'px;max-width:'.$min.'px; border-right: 0px;"></TD>';
echo '  <TD class="reportTableHeader" style="border-left:0px; text-align: left;width:100%;">' . i18n('colBudget') . '</TD>';
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colEstimateAmount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colInitialAmount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colUpdate1Amount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colUpdate2Amount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colUpdate3Amount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colUpdate4Amount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colUpdatedAmount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colEngagedAmount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colAvailableAmount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colBilledAmount') . '</div></TD>' ;
echo '  <TD class="reportTableHeader amountTableHeaderTD" ><div class="amountTableHeaderDiv" style="min-width:80px;">' . i18n('colLeftAmount') . '</div></TD>' ;
if(!$print)echo '  <TD class=""  ><div style="width:12px;" id="hierarchicBudgetScrollSpace">&nbsp;</div></TD>';
echo '</TR>';
echo '</table>';
echo "</div>";
$destHeight=RequestHandler::getValue('destinationHeight');
$height=($destHeight)?(intval($destHeight)-40).'px':'100%';
echo '<div id="hierarchicalBudgetListDiv" style="position:relative;height:100%;width:100%;min-width:'.((isNewGui())?1363:1350).'px;'.$maxwidht.'overflow-x:hidden;">';
echo '<table id="dndHierarchicalBudgetList" dojoType="dojo.dnd.Source" jsId="dndSourceTableBudget" id="dndSourceTableBudget" align="left" width="100%" style="min-width:1350px;">';
function getSubBudgetList($subList, &$subBudget){
	foreach ($subList as $id=>$obj){
      $subBudget[]=$obj->id;
      $budget = new Budget();
      $resubList = $budget->getSqlElementsFromCriteria(array('idBudget'=>$obj->id));
      getSubBudgetList($resubList, $subBudget);
	}
}

function getVisibleRowList($idBudget, $subList, &$visibleRow){
	foreach ($subList as $id=>$obj){
		$col = SqlElement::getSingleSqlElementFromCriteria('Collapsed', array('scope'=>'hierarchicalBudgetRow_'.$obj->id, 'idUser'=>getCurrentUserId()));
		$colParent = SqlElement::getSingleSqlElementFromCriteria('Collapsed', array('scope'=>'hierarchicalBudgetRow_'.$obj->idBudget, 'idUser'=>getCurrentUserId()));
		if(!$colParent->id and $idBudget == $obj->idBudget){
      	   $visibleRow[$obj->id]=$obj->id;
      	   $resubList = $obj->getSqlElementsFromCriteria(array('idBudget'=>$obj->id));
      	   getVisibleRowList($idBudget, $resubList, $visibleRow);
		}else if(!$colParent->id and $idBudget != $obj->idBudget){
	  	   $visibleRow[$obj->id]=$obj->id;
		}else{
		  continue;
		}
	}
}
// Treat each line
if (Sql::$lastQueryNbRows > 0) {
	while ($line = Sql::fetchLine($result)) {
		$line=array_change_key_case($line,CASE_LOWER);
		if(!$showFullAmount){
		  $plannedAmount=$line['plannedamount'];
		  $initialAmount=$line['initialamount'];
		  $update1Amount=$line['update1amount'];
		  $update2Amount=$line['update2amount'];
		  $update3Amount=$line['update3amount'];
		  $update4Amount=$line['update4amount'];
		  $actualAmount=$line['actualamount'];
		  $actualSubAmount=$line['actualsubamount'];
		  $usedAmount=$line['usedamount'];
		  $availableAmount=$line['availableamount'];
		  $billedAmount=$line['billedamount'];
		  $leftAmount=$line['leftamount'];
		}else {
		  $plannedAmount=$line['plannedfullamount'];
		  $initialAmount=$line['initialfullamount'];
		  $update1Amount=$line['update1fullamount'];
		  $update2Amount=$line['update2fullamount'];
		  $update3Amount=$line['update3fullamount'];
		  $update4Amount=$line['update4fullamount'];
		  $actualAmount=$line['actualfullamount'];
		  $actualSubAmount=$line['actualsubfullamount'];
		  $usedAmount=$line['usedfullamount'];
		  $availableAmount=$line['availablefullamount'];
		  $billedAmount=$line['billedfullamount'];
		  $leftAmount=$line['leftfullamount'];
		}

		// pGroup : is the tack a group one ?
		$pGroup=($line['elementary']=='1')?0:1;
		$compStyle="";
		$visibleRow = array();
		$limitedSubBudget = array();
		if( $pGroup) {
			$rowType = "group";
			$compStyle="font-weight: normal; background: #E8E8E8";
			$budget = new Budget();
			$subList = $budget->getSqlElementsFromCriteria(array('idBudget'=>$line['id']));
			foreach ($subList as $id=>$obj){
				$limitedSubBudget[]=$obj->id;
			}
			$subBudget=array();
			getSubBudgetList($subList, $subBudget);
			getVisibleRowList($line['id'], $subList, $visibleRow);
			$crit=array('scope'=>'hierarchicalBudgetRow_'.$line['id'], 'idUser'=>getCurrentUserId());
			$col=SqlElement::getSingleSqlElementFromCriteria('Collapsed', $crit);
			if($col->id){
				$class = 'ganttExpandClosed';
    			if(!$line['idbudget']){
    			  $hiddenRow[$line['id']]=$line['id'];
    			}
			}else{
				$class = 'ganttExpandOpened';
			}
		} else {
			$rowType  = "row";
			if(!$line['idbudget']){
			  $hiddenRow[$line['id']]=$line['id'];
			}
		}
		$hiddenRow = array_merge($hiddenRow, $visibleRow);
		$wbs=$line['bbssortable'];
		$level=(strlen($wbs)+1)/6;
		$tab="";
		$id=$line['id'];
		for ($i=1;$i<$level;$i++) {
			$tab.='<span class="ganttSep">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
		}
		$display='';
		if($line['idbudget'] and !in_array($line['id'], $hiddenRow) and $line['id']!=$budgetParent){
		  $display='visibility:collapse';
		}
		echo '<TR id="hierarchicalBudgetRow_'.$id.'" dndType="budgetHierachical" class="dojoDndItem ganttTask'.$rowType.' hierarchicalBudgetRow" height="30px" style="cursor:default;'.$display.'">';
  		echo '  <TD class="ganttName reportTableData" style="width:30px;min-width:30px;max-width:30px;border-right:0px;' . $compStyle . '">';
//   		if(!$print){
    		echo '    <span class="dojoDndHandle handleCursor">';
    		echo '      <table><tr>';
    		echo '        <td class="ganttIconBackground">';
    		echo           formatIcon('Budget', '16');
    		echo '        </td>';
    		echo '        <td><img style="margin-right:2px;width:8px" src="css/images/iconDrag.gif" /></td>';
    		echo '      </tr></table>';
    		echo '    </span>';
//   		}
    	echo '  </TD>';
		$nameWidth='';
		if($print)$nameWidth='width:21%;';
		echo '  <TD class="ganttName reportTableData" style="overflow:hidden;border-left:0px; text-align: left;' .$nameWidth. $compStyle . '" nowrap>';
		echo '    <div style="position:relative;height:100%;width:100%;"><div class="" style="position:absolute;overflow:hidden;width:100%;height:100%;top:0px;" >';
		echo '    <table style="width:100%;height:100%;vertical-align:middle;"><tr style="height:100%">';
		echo '     <td style="width:1px">'.$tab.'</td>';
		echo '     <td style="position:relative;width:10px">';
		if($pGroup and !$print){
			echo '     <div id="group_'.$line['id'].'" class="'.$class.'"';
			echo '      style="position: relative; z-index: 100000; width:16px; height:13px;"';
			echo '      onclick="expandHierarchicalBudgetGroup(\''.$line['id'].'\',\''.implode(',', $limitedSubBudget).'\',\''.implode(',', $subBudget).'\',\''.implode(',', $visibleRow).'\');">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
		} else {
		  echo '     <div class="ganttNoExpand" style="position: relative; z-index: 100000; width:16px; height:13px;" >&nbsp;&nbsp;&nbsp;&nbsp;</div>';
		}
		echo '     </td>';
		echo '     <td style="position:relative" onClick="dojo.byId('."'objectId'".').value=\''.$id.'\';listClick();loadContent('."'objectDetail.php'".', '."'detailDiv'".','."'listForm'".');">' . htmlEncode($line['name']).'</td>';
		echo '    </tr></table>';
		echo '    </div></div>';
		echo '  </TD>';
		$style='style="min-width:80px;"';
		if($print)$style = 'style="min-width:80px;width:88px;"';
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($plannedAmount). '</div></TD>' ;
		if($print)$style = 'style="min-width:80px;width:85px;"';
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($initialAmount). '</div></TD>' ;
		if($print)$style = 'style="min-width:80px;width:90px;"';
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($update1Amount). '</div></TD>' ;
		if($print)$style = 'style="min-width:80px;width:90px;"';
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($update2Amount). '</div></TD>' ;
		if($print)$style = 'style="min-width:80px;width:103px;"';
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($update3Amount). '</div></TD>' ;
		if($print)$style = 'style="min-width:80px;width:85px;"';
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($update4Amount). '</div></TD>' ;
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($actualAmount). '</div></TD>' ;
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($usedAmount). '</div></TD>' ;
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($availableAmount). '</div></TD>' ;
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($billedAmount). '</div></TD>' ;
		echo '  <TD class="ganttName reportTableData amountTableTD" style="' . $compStyle . ';"><div class="amountTableDiv" '.$style.'>' .htmlDisplayCurrency($leftAmount). '</div></TD>' ;
		echo '</TR>';
	}
}
echo "</table>";
echo '<div id="hierarchicalBudgetListDivEnd" style="min-height:20px; display:none;">&nbsp;</div>';
echo '</div>';
?>