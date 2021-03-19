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

// Order 
// show a table with Order /order information
//echo "<page_subProjectDashboard.php>";
projeqtor_set_time_limit(300);
projeqtor_set_memory_limit('512M');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Projet section dans le rapport

	$bPrjSheet=($tabPrj['prj']['id']==$idProject && $bMultiPrj);

	$indicator="neutral";
	$diff=round($tabPrj['prj']['real']+$tabPrj['prj']['left'] - $tabPrj['prj']['validated'], 2);
	if($diff>0) {
		$indicator="unhappy";
	} else if($diff<0) {
		$indicator="happy";
	} else if($diff==0) {
		$indicator="neutral";
	}
	
	$color1='#32FF32';
	$color2='#32FF32';
	if (($tabPrj['prj']['left']+$tabPrj['prj']['real'])>$tabPrj['prj']['validated']) $color1='#FF3232';
	if (($tabPrj['prj']['left']+$tabPrj['prj']['real'])>$tabPrj['prj']['assigned']) $color2='#FF3232';
	
	echo '<table align="center" style="page-break-inside: avoid;" >';
	if ($bPrjSheet) {
		echo '<tr rowspan=4>';
		echo '<td class="reportTableHeader" rowspan=4 style="width:150px;">' . i18n('mainProject') . '</td>';
	} else {
		echo '<tr rowspan=2>';
		echo '<td class="reportTableHeader" rowspan=2 style="width:150px;">' . i18n('Project') . '</td>';
	}
	
	echo '<td class="reportTableHeader" style="xwidth:620px;" colspan=2>' . i18n('colManager').' : '.htmlEncode($tabPrj['prj']['manager']) . '</td>';
	echo '<td class="reportTableHeader" style="xwidth:15%;">' . i18n('colValidated') . '</td>';
	echo '<td class="reportTableHeader" style="xwidth:15%;">' . i18n('colAssigned') . '</td>';
	echo '<td class="reportTableHeader" style="xwidth:15%;">' . i18n('colReal') . '</td>';
	echo '<td class="reportTableHeader" style="xwidth:15%;">' . i18n('colLeft') . '</td>';
	echo '<td class="reportTableHeader" style="xwidth:15%;">' . i18n('colPlanned') . '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td class="reportTableLineHeader" style="width:45%;background-color:' . $tabPrj['prj']['color'] . '">' . htmlEncode($tabPrj['prj']['name']) . '</td>';
	echo '<td class="reportTableLineHeader" style="width:20px;" title='. Work::displayWork($diff) .'>
					<img style="width:16px" src="../view/css/images/indicator_' . $indicator . '.png" /></td>';
	echo '<td class="reportTableData" style="width:10%;color:' . $color1 . ';"><b>' .  Work::displayWork($tabPrj['prj']['validated']) . '</b></td>';
	echo '<td class="reportTableData" style="width:10%;color:' . $color2 . ';"><b>' .  Work::displayWork($tabPrj['prj']['assigned']) . '</b></td>';
	echo '<td class="reportTableData" style="width:10%;">' .  Work::displayWork($tabPrj['prj']['real']) . '</td>';
	echo '<td class="reportTableData" style="width:10%;">' .  Work::displayWork($tabPrj['prj']['left']) . '</td>';
	echo '<td class="reportTableData" style="width:10%;">' .  Work::displayWork($tabPrj['prj']['planned']) . '</td>';
	echo '</tr>';
//	echo '<tr><td colspan=8><br/></td></tr>';
	
	
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Order section dans le rapport

	if (!$bPrjSheet) {
	
		$nb=count($tabPrj['bc'])+1;
		$item=null;
		
		echo '<tr rowspan=' . $nb . '>';
		echo '<td class="reportTableHeader" rowspan=' . $nb . ' style="width:150px;">' . i18n('Command') . '</td>';
		echo '<td class="reportTableHeader" colspan=2>' . i18n('colName') . '</td>';
		echo '<td class="reportTableHeader" >' . i18n('colValidatedWork2') . '</td>';
		echo '<td class="reportTableHeader" >' . i18n('colValidatedPricePerDayAmount2') . '</td>';
		echo '<td class="reportTableHeader" >' . i18n('colValidatedAmount2') . '</td>';
		echo '<td colspan="2" class="reportTableHeader" >' . i18n('colIdStatus') . '</td>';
		echo '</tr>';
		
		foreach ($tabPrj['bc'] as $item) {
			if (strlen($item['name'])>42) {
				$name=substr($item['name'], 0, 40) . '...' ;
			} else {
				$name=$item['name'];
			}
				
			echo '<tr>';
			echo '<td class="reportTableLineHeader"  colspan=2 title="'. htmlEncode($item['name'])  .'" style="width:600px;">' . htmlEncode($name) . '</td>';
			echo '<td class="reportTableData" >' . $item['work'] . '</td>';
			echo '<td class="reportTableData" >' . $item['tjm'] . '</td>';
			echo '<td class="reportTableData" >' . $item['total'] . '</td>';
			echo '<td class="reportTableData" style="background-color:' . $item['color'] . ';" colspan=2>' . $item['status'] . '</td>';
			echo '</tr>';
		}
	}
	
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Phase section dans le rapport
	
	if ($bPrjSheet) {
		echo '<tr rowspan=2>';
		echo '<td class="reportTableHeader" rowspan=2 colspan=2>' . i18n('colWork') . '</td>';
		echo '<td class="reportTableHeader" ></td>';
	} else {
		
		$nb=count($tabPrj['charge'])+2;
		echo '<tr rowspan=' . $nb . '>';
		echo '<td class="reportTableHeader" rowspan=' . $nb . ' style="width:150px;">' . i18n('colIdActivityType') . '</td>';
		echo '<td class="reportTableHeader" colspan=2>' . i18n('colName') . '</td>';
		//echo '<td class="reportTableHeader" >' . i18n('colValidated') . '</td>';
		echo '<td class="reportTableHeader" ></td>';
		
	}
	
	echo '<td class="reportTableHeader" >' . i18n('colAssigned') . '</td>';
	echo '<td class="reportTableHeader" >' . i18n('colReal') . '</td>';
	echo '<td class="reportTableHeader" >' . i18n('colLeft') . '</td>';
	echo '<td class="reportTableHeader" >' . i18n('colPlanned') . '</td>';
	echo '</tr>';

	if (!$bPrjSheet) {
		$type=new Type();
		$lstActType=$type->getSqlElementsFromCriteria(array("scope"=>"Activity","idle"=>"0"),false,null,"sortOrder");
		foreach ($lstActType as $actType) {
			$idActType=$actType->id;
		
			if (array_key_exists($idActType,$tabPrj['charge'])) {
				$wValidated=0;
				$wAssign=$tabPrj['charge'][$idActType]['assigned'];
				$wLeft=$tabPrj['charge'][$idActType]['left'];
				$wReal=$tabPrj['charge'][$idActType]['real'];
		
				$indicator="neutral";
				$diff=round($wReal+$wLeft- $wAssign, 2);
				if($diff>0) {
					$indicator="unhappy";
				} else if($diff<0) {
					$indicator="happy";
				} else if($diff==0) {
					$indicator="neutral";
				}
		
				echo '<tr>';
				echo '<td class="reportTableLineHeader">' . htmlEncode($tabPrj['charge'][$idActType]['name']) . '</td>';
				echo '<td class="reportTableLineHeader" style="width:20px;" title='. Work::displayWork($diff) .'>
						<img style="width:16px" src="../view/css/images/indicator_' . $indicator . '.png" /></td>';
				//echo '<td class="reportTableData">' . Work::displayWork($wValidated) . '</td>';
				echo '<td class="reportTableLineHeader"></td>';
				echo '<td class="reportTableData">' . Work::displayWork($wAssign) . '</td>';
				echo '<td class="reportTableData">' . Work::displayWork($wReal) . '</td>';
				echo '<td class="reportTableData">' . Work::displayWork($wLeft) . '</td>';
				echo '<td class="reportTableData">' . Work::displayWork($wReal+$wLeft) . '</td>';
				echo '</tr>';
			}
		
		}
		echo '<tr>';
		echo '<td class="reportTableHeader" colspan=2>' . i18n('sum') . '</td>';
	} else {
		echo '<tr>';
	}
	echo '<td class="reportTableHeader"></td>';
	echo '<td class="reportTableHeader">' . Work::displayWork($tabPrj['prj']['assigned']) . '</td>';
	echo '<td class="reportTableHeader">' . Work::displayWork($tabPrj['prj']['real']) . '</td>';
	echo '<td class="reportTableHeader">' . Work::displayWork($tabPrj['prj']['left']) . '</td>';
	echo '<td class="reportTableHeader">' . Work::displayWork($tabPrj['prj']['left']+$tabPrj['prj']['real']) . '</td>';
	echo '</tr>';
	
	echo '</table>';	
?>
