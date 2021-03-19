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

// Header
include_once '../tool/projeqtor.php';
include_once('../tool/formatter.php');

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
  $paramProject=Security::checkValidId($paramProject); // only allow digits
}
  // Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}  
include "header.php";

if ($paramProject!="") {
  
}
$queryWhereOpportunity=getAccesRestrictionClause('Opportunity',false);

$queryWherePlus="";
if ($paramProject!="") {
  $queryWherePlus.=" and idProject in " . getVisibleProjectsList(true, $paramProject);
}
//$queryWherePlus.=" and idle=0";
$clauseOrderBy=" id asc";

echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('Opportunity');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';

$obj=new Opportunity();
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereOpportunity . $queryWherePlus, $clauseOrderBy);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:2%;">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:15%;">' . i18n('colDescription') . '</td>';
echo '<td class="largeReportHeader" style="width:10%;">' . i18n('colOrigin') . '</td>';
echo '<td class="largeReportHeader" style="width:15%;">' . i18n('colImpact') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;">' . i18n('colSeverityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;">' . i18n('colOpportunityImprovementShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;">' . i18n('colCriticalityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;">' . i18n('colPriorityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:6%;">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:6%;">' . i18n('colCreationDate') . '</td>';
echo '<td class="largeReportHeader" style="width:6%;">' . i18n('colDueDate') . '<br/><span style="font-size:75%">' . i18n('commentDueDates') . '</span></td>';
echo '<td class="largeReportHeader" style="width:5%;">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:15%;">' . i18n('colResult') . '</td>';
echo '</tr>';
foreach ($lst as $opportunity) {
  echo '<tr>';
  $done=($opportunity->idle)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:2%;">' . '#' . htmlEncode($opportunity->id) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%;">' . htmlEncode($opportunity->name); 
  if ($opportunity->description and $opportunity->name!=$opportunity->description) { echo ':<br/><i>' . ($opportunity->description).'</i>'; }
  echo '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%;">' . ($opportunity->cause) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%;">' . ($opportunity->impact) . '</td>';  
  echo '<td align="center" class="largeReportData' . $done . '" style="width:5%;">' . formatColor('Severity', $opportunity->idSeverity) . '</td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:5%;">' . formatColor('Likelihood', $opportunity->idLikelihood) . '</td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:5%;">' . formatColor('Criticality', $opportunity->idCriticality) . '</td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:5%;">' . formatColor('Priority', $opportunity->idPriority) . '</td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:6%;">' . SqlList::getNameFromId('Resource', $opportunity->idResource) . '</td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:6%;">' . htmlFormatDate($opportunity->creationDate) . '</td>';
  
  echo '<td class="largeReportData' . $done . '" style="width:6%;"><table width="100%">';
  if ($opportunity->initialEndDate!=$opportunity->actualEndDate) {
    echo '<tr ><td align="center" style="text-decoration: line-through;">' . htmlFormatDate($opportunity->initialEndDate) . '</td></tr>';
    echo '<tr><td align="center">' . htmlFormatDate($opportunity->actualEndDate) . '</td></tr>';
  } else {
    echo '<tr><td align="center">'. htmlFormatDate($opportunity->initialEndDate) . '</td></tr>';
    echo '<tr><td align="center">&nbsp;</td></tr>'; 
  }
  echo   '<tr><td align="center" style="font-weight: bold">' . htmlFormatDate($opportunity->doneDate) . '</td></tr>';  
  echo '</table></td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:5%;">' . formatColor('Status', $opportunity->idStatus) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%;">' . ($opportunity->result) . '</td>';
  echo '</tr>';
}
echo "</table>";
unset($opportunity);

function listLinks($obj) {
  $lst=Link::getLinksAsListForObject($obj);
  $res='<table style="width:100%; margin:0 ; spacing:0 ; padding: 0">';
  foreach ($lst as $link) {
  $obj=new $link['type']($link['id']);
  $style=(isset($obj->done) and $obj->done)?'style="text-decoration: line-through;"':'';
    $res.='<tr><td '. $style . '>' . substr($link['type'],0,1) . $link['id'] . '</td></tr>';
  }
  $res.='</table>';
  return $res;
}

function listFiles($obj) {
  $lst=Link::getLinksAsListForObject($obj);
  $res='<table style="width:100%; margin:0 ; spacing:0 ; padding: 0">';
  foreach ($lst as $link) {
  $obj=new $link['type']($link['id']);
  $style=(isset($obj->done) and $obj->done)?'style="text-decoration: line-through;"':'';
    $res.='<tr><td '. $style . '>' . substr($link['type'],0,1) . $link['id'] . '</td></tr>';
  }
  $res.='</table>';
  return $res;
}

?>
