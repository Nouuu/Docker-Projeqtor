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
};

$paramClosedItems=false;
if (array_key_exists('showClosedItems',$_REQUEST)) {
  $paramClosedItems=true;
};
  // Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramClosedItems!="") {
  $headerParameters.= i18n("colShowClosedItems") . ' : ' . i18n('displayYes') . '<br/>';
}  

include "header.php";

$queryWhereAction=getAccesRestrictionClause('Action',false);
$queryWhereRisk=getAccesRestrictionClause('Risk',false);
$queryWhereIssue=getAccesRestrictionClause('Issue',false);
$queryWhereOpportunity=getAccesRestrictionClause('Opportunity',false);

$queryWherePlus="";
if ($paramProject!="") {
  $queryWherePlus.=" and idProject in " . getVisibleProjectsList(true, $paramProject);
}
if(!$paramClosedItems){
  $queryWherePlus.=" and idle=0";
}

$clauseOrderBy=" actualEndDate asc";
$tabAction = array();

echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('Risk');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';

$obj=new Risk();
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereRisk . $queryWherePlus, $clauseOrderBy);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colType') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('Risk') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colCause') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colImpact') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colMitigationPlan') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colSeverityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colLikelihoodShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colCriticalityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colPriorityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colDueDate') . '<br/><span style="font-size:75%">' . i18n('commentDueDates') . '</span></td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colResult') . '</td>';
echo '</tr>';
foreach ($lst as $risk) {
  echo '<tr>';
  $done=($risk->done)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . 'R' . htmlEncode($risk->id) . '</td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:7%">' . SqlList::getNameFromId('RiskType', $risk->idRiskType) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . htmlEncode($risk->name); 
  if ($risk->description and $risk->name!=$risk->description) { echo ':<br/>' . ($risk->description); }
  echo '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . ($risk->cause) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . ($risk->impact) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . ($risk->mitigationPlan) . '</td>';
  
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Severity', $risk->idSeverity) . '</div></td>';
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Likelihood', $risk->idLikelihood) . '</div></td>';
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Criticality', $risk->idCriticality) . '</div></td>';
    echo '<td align="" class="largeReportData' . $done . '" style="width:5;max-width:50px"><div>' . formatColor('Priority', $risk->idPriority) . '</div></td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:6%;max-width:50px">' . SqlList::getNameFromId('Resource', $risk->idResource) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;max-width:50px"><table width="100%">';
  if ($risk->initialEndDate!=$risk->actualEndDate) {
    echo '<tr ><td align="center" style="text-decoration: line-through;">' . htmlFormatDate($risk->initialEndDate) . '</td></tr>';
    echo '<tr><td align="center">' . htmlFormatDate($risk->actualEndDate) . '</td></tr>';
  } else {
    echo '<tr><td align="center">'. htmlFormatDate($risk->initialEndDate) . '</td></tr>';
    echo '<tr><td align="center">&nbsp;</td></tr>'; 
  }
  echo   '<tr><td align="center" style="font-weight: bold">' . htmlFormatDate($risk->doneDate) . '</td></tr>';
  
  echo '</table></td>';
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Status', $risk->idStatus) . '</div></td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . listLinks($risk) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . ($risk->result) . '</td>';
  echo '</tr>';
}
unset($risk);
echo '</table><br/><br/>';
echo '</page><page>';

echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('Opportunity');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';

$obj=new Opportunity();
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereOpportunity . $queryWherePlus, $clauseOrderBy);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colType') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('Opportunity') . '</td>';
echo '<td class="largeReportHeader" style="width:13%">' . i18n('colOpportunitySourceShort') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('colImpact') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;max-width:50px">' . i18n('colSeverityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;max-width:50px">' . i18n('colOpportunityImprovementShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;max-width:50px">' . i18n('colCriticalityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;max-width:50px">' . i18n('colPriorityShort') . '</td>';
echo '<td class="largeReportHeader" style="width:6%;max-width:50px">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:6%;max-width:50px">' . i18n('colDueDate') . '<br/><span style="font-size:75%">' . i18n('commentDueDates') . '</span></td>';
echo '<td class="largeReportHeader" style="width:5%;max-width:50px">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colResult') . '</td>';
echo '</tr>';
foreach ($lst as $opportunity) {
  echo '<tr>';
  $done=($opportunity->done)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . 'O' . htmlEncode($opportunity->id) . '</td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:7%">' . SqlList::getNameFromId('OpportunityType', $opportunity->idOpportunityType) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . ($opportunity->name); 
  if ($opportunity->description and $opportunity->name!=$opportunity->description) { echo ':<br/>' . ($opportunity->description); }
  echo '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:13%">' . ($opportunity->cause) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%">' . ($opportunity->impact) . '</td>';
  
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Severity', $opportunity->idSeverity) . '</div></td>';
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Likelihood', $opportunity->idLikelihood) . '</div></td>';
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Criticality', $opportunity->idCriticality) . '</div></td>';
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Priority', $opportunity->idPriority) . '</div></td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:6%">' . SqlList::getNameFromId('Resource', $opportunity->idResource) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%"><table width="100%">';
  if ($opportunity->initialEndDate!=$opportunity->actualEndDate) {
    echo '<tr ><td align="center" style="text-decoration: line-through;">' . htmlFormatDate($opportunity->initialEndDate) . '</td></tr>';
    echo '<tr><td align="center">' . htmlFormatDate($opportunity->actualEndDate) . '</td></tr>';
  } else {
    echo '<tr><td align="center">'. htmlFormatDate($opportunity->initialEndDate) . '</td></tr>';
    echo '<tr><td align="center">&nbsp;</td></tr>'; 
  }
  echo   '<tr><td align="center" style="font-weight: bold">' . htmlFormatDate($opportunity->doneDate) . '</td></tr>';
  
  echo '</table></td>';
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Status', $opportunity->idStatus) . '</div></td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . listLinks($opportunity) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . ($opportunity->result) . '</td>';
  echo '</tr>';
}
unset($opportunity);
echo '</table><br/><br/>';
echo '</page><page>';
echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('Issue');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';

$obj=new Issue();
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereIssue . $queryWherePlus, $clauseOrderBy);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colType') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('Action') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('colCause') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('colImpact') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colPriority') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colDueDate') . '<br/><span style="font-size:75%">' . i18n('commentDueDates') . '</span></td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('colResult') . '</td>';
echo '</tr>';
foreach ($lst as $issue) {
  echo '<tr>';
  $done=($issue->done)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . 'I' . htmlEncode($issue->id) . '</td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:8%">' . SqlList::getNameFromId('IssueType', $issue->idIssueType) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%">' . htmlEncode($issue->name); 
  if ($issue->description and $issue->name!=$issue->description) { echo ':<br/>' . ($issue->description); }
  echo '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%">' . ($issue->cause) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%">' . ($issue->impact) . '</td>';
  
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Priority', $issue->idPriority) . '</div></td>';
  echo '<td align="center" class="largeReportData' . $done . '" style="width:10%">' . SqlList::getNameFromId('Resource', $issue->idResource) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%"><table width="100%">';
  if ($issue->initialEndDate!=$issue->actualEndDate) {
    echo '<tr ><td align="center" style="text-decoration: line-through;">' . htmlFormatDate($issue->initialEndDate) . '</td></tr>';
    echo '<tr><td align="center">' . htmlFormatDate($issue->actualEndDate) . '</td></tr>';
  } else {
    echo '<tr><td align="center">'. htmlFormatDate($issue->initialEndDate) . '</td></tr>';
    echo '<tr><td align="center">&nbsp;</td></tr>'; 
  }
  echo   '<tr><td align="center" style="font-weight: bold">' . htmlFormatDate($issue->doneDate) . '</td></tr>';
  
  echo '</table></td>';
  echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Status', $issue->idStatus) . '</div></td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . listLinks($issue) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%">' . ($issue->result) . '</td>';
  echo '</tr>';
}
echo '</table><br/><br/>';
unset ($issue);
echo '</page><page>';

echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('Action');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';
$obj=new Action();
$clauseOrderBy=" actualDueDate asc";
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereAction . $queryWherePlus, $clauseOrderBy);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colType') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('Action') . '</td>';
echo '<td class="largeReportHeader" style="width:31%">' . i18n('colDescription') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colPriority') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colDueDate') . '<br/><span style="font-size:75%">' . i18n('commentDueDates') . '</span></td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('colResult') . '</td>';
echo '</tr>';


foreach ($lst as $action) {
  //gautier #2576
   $bool = false;
   listLinks($action);
   foreach ($tabAction as $actiones){
     if($actiones == 'A' . htmlEncode($action->id) ){
       $bool = true;
     }
   }
  if($action->isPrivate == false){
    if ($bool == true){
      echo '<tr>';
      $done=($action->done)?'Done':'';
      echo '<td class="largeReportData' . $done . '" style="width:3%">' . 'A' . htmlEncode($action->id) . '</td>';
      echo '<td align="center" class="largeReportData' . $done . '" style="width:10%">' . SqlList::getNameFromId('ActionType', $action->idActionType) . '</td>';
      echo '<td class="largeReportData' . $done . '" style="width:15%">' . htmlEncode($action->name) . '</td>';
      echo '<td class="largeReportData' . $done . '" style="width:31%">' . ($action->description) . '</td>';
      echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Priority', $action->idPriority) . '</div></td>';
      echo '<td align="center" class="largeReportData' . $done . '" style="width:7%">' . SqlList::getNameFromId('Resource', $action->idResource) . '</td>';
      echo '<td class="largeReportData' . $done . '" style="width:6%"><table width="100%">';
      if ($action->initialDueDate!=$action->actualDueDate) {
        echo '<tr ><td align="center" style="text-decoration: line-through;">' . htmlFormatDate($action->initialDueDate) . '</td></tr>';
        echo '<tr><td align="center">' . htmlFormatDate($action->actualDueDate) . '</td></tr>';
      } else {
        echo '<tr><td align="center">'. htmlFormatDate($action->initialDueDate) . '</td></tr>';
        echo '<tr><td align="center">&nbsp;</td></tr>'; 
      }
      echo   '<tr><td align="center" style="font-weight: bold">' . htmlFormatDate($action->doneDate) . '</td></tr>';
      
      echo '</table></td>';
      echo '<td align="" class="largeReportData' . $done . '" style="width:5%;max-width:50px"><div>' . formatColor('Status', $action->idStatus) . '</div></td>';
      echo '<td class="largeReportData' . $done . '" style="width:3%">' . listLinks($action) . '</td>';
      echo '<td class="largeReportData' . $done . '" style="width:15%">' . ($action->result) . '</td>';
      echo '</tr>';  
    }       
  }
}
echo '</table><br/>';

function listLinks($objIn) {
  global $tabAction;
  $lst=Link::getLinksAsListForObject($objIn);
  $res='<table style="width:100%; margin:0 ; spacing:0 ; padding: 0">';
  foreach ($lst as $link) {
    $obj=new $link['type']($link['id']);
    $style=(isset($obj->done) and $obj->done)?'style="text-decoration: line-through;"':'';
    if ($link['type']=='Action' or $link['type']=='Issue' or $link['type']=='Risk' or $link['type']=='Opportunity') {
      $type=substr($link['type'],0,1);
    } else {
      //$type=substr(i18n($link['type']),0,10);
      $type=substr($link['type'],0,10);
    }
    //gautier #2576
    if($link['type']=='Action'){
     $act = new Action($link['id']);
     if($act->isPrivate == false){
       $res.='<tr><td '. $style . '>' . $type . $link['id'] . '</td></tr>'; 
       $tabAction[$type . $link['id']] =  $type . $link['id'];
     } 
    }else{
      $res.='<tr><td '. $style . '>' . $type . $link['id'] . '</td></tr>';
    }  
  }
  $res.='</table>';
  return $res;
}

?>
