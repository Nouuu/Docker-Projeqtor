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

include_once '../tool/projeqtor.php';

//Si l'idProject est défini dans les paramètres du rapport
$idProject = "";
if (array_key_exists('idProject', $_REQUEST)){
    $idProject=trim($_REQUEST['idProject']);
	  $idProject = Security::checkValidId($idProject);
}

$paramYear='';
if (array_key_exists('yearSpinner',$_REQUEST)) {
	$paramYear=$_REQUEST['yearSpinner'];
	$paramYear=Security::checkValidYear($paramYear);
};
  
$paramMonth='';
if (array_key_exists('monthSpinner',$_REQUEST)) {
	$paramMonth=$_REQUEST['monthSpinner'];
  $paramMonth=Security::checkValidMonth($paramMonth);
};

$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
	$paramWeek=$_REQUEST['weekSpinner'];
	$paramWeek=Security::checkValidWeek($paramWeek);
};

if (array_key_exists('periodType',$_REQUEST)) {
	$periodType=$_REQUEST['periodType']; // not filtering as data as data is only compared against fixed strings
    //$periodValue=$_REQUEST['periodValue'];
}
// We build the Where clause
$term = new Term();
$termAlias = $term->getDatabaseTableName();
$where = '1=1';
if ($idProject)
$where .= " AND ".$termAlias . ".idProject = " . $idProject;
if ($periodType) {
	$start = date('Y-m-d');
	$end = date('Y-m-d');
	if ($periodType == 'year') {
		$start = $paramYear . '-01-01';
		$end = $paramYear . '-12-31';
	} else if ($periodType == 'month') {
		$start = $paramYear . '-' . (($paramMonth < 10) ? '0' : '') . $paramMonth . '-01';
    if ((!$paramYear and !$paramMonth) or (!$paramYear) or (!$paramMonth)) {
      echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    if(!$paramYear and !$paramMonth){
      echo i18n('messageNoData',array(i18n('yearAndmonth'))); // TODO i18n message
    } else if(!$paramYear){
      echo i18n('messageNoData',array(i18n('year'))); // TODO i18n message
    } else if(!$paramMonth){
      echo i18n('messageNoData',array(i18n('month'))); // TODO i18n message
    }
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
    }
		$end = $paramYear . '-' . (($paramMonth < 10) ? '0' : '') . $paramMonth . '-' . date('t', mktime(0, 0, 0, $paramMonth, 1, $paramYear));
	} if ($periodType == 'week') {
  	  if ((!$paramYear and !$paramWeek) or (!$paramYear) or (!$paramWeek)) {
        echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
      if(!$paramYear and !$paramWeek){
        echo i18n('messageNoData',array(i18n('yearAndweek'))); // TODO i18n message
      } else if(!$paramYear){
        echo i18n('messageNoData',array(i18n('year'))); // TODO i18n message
      } else if(!$paramWeek){
        echo i18n('messageNoData',array(i18n('week'))); // TODO i18n message
      }
      echo '</div>';
      if (!empty($cronnedScript)) goto end; else exit;
      }
		$start=date('Y-m-d', firstDayofWeek($paramWeek, $paramYear));
  	$end=addDaysToDate($start,6);
	}
	$where.=" AND ( ".$termAlias . ".date >= '" . $start . "'";
	$where.=" and ". $termAlias . ".date <='" . $end . "' )";
}

$termList = $term->getSqlElementsFromCriteria(null,false, $where);

//En-tete du tableau
        echo '<div style="page-break-before:always;"></div>';
        echo '<h3>'.i18n("reportTermTitle").' '.$start.' -> '.$end.'</h3>';
        echo '
        <table style="width: 100%;">
            <tr>
                <th class="reportTableHeader">'.i18n("colDate").'</th>
                <th class="reportTableHeader">'.i18n("Term").'</th>
                <th class="reportTableHeader">'.i18n("colUntaxedAmount").'</th>
                <th class="reportTableHeader">'.i18n("colProjectCode").'</th>
                <th class="reportTableHeader">'.i18n("colProjectName").'</th>
                <th class="reportTableHeader">'.i18n("Bill").'</th>
                <th class="reportTableHeader">'.i18n("colIsBilled").'</th>
            </tr>';
        
//liste de toutes les échances correspondants aux paramètres
foreach ($termList as $term)
{
    $project=new Project($term->idProject);
    $bill=new Bill($term->idBill);
 
    echo '
            <tr>
                <td class="reportTableData">'.htmlEncode($term->date).'</td>
                <td class="reportTableData">'.htmlEncode($term->name).'</td>
                <td class="reportTableData">'.htmlEncode($term->amount).'</td>
                <td class="reportTableData">'.htmlEncode($project->projectCode).'</td>
                <td class="reportTableData">'.htmlEncode($project->name).'</td>
                <td class="reportTableData">'.htmlEncode($bill->name).'</td>';
                if($bill->id){
                    echo'<td class="reportTableData"><img src="./img/checkedOK.png" width="12" height="12" /></td>';
                }else{
                    echo'<td class="reportTableData"><img src="./img/checkedKO.png" width="12" height="12" /></td>';
                }
                
            echo'</tr>';
            
}
 echo '</table>';

end:
 
?>