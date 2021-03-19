<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 * 
 * Most of properties are extracted from Dojo Framework.
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

require_once '../tool/projeqtor.php';
include_once('../tool/formatter.php');
$paramProject = trim(RequestHandler::getId('idProject'));
$paramProjectType = trim(RequestHandler::getId('idProjectType'));
$idOrganization = trim(RequestHandler::getId('idOrganization'));
$paramYear = RequestHandler::getYear('yearSpinner');
$paramMonth = RequestHandler::getMonth('monthSpinner');
$user=getSessionUser();

// Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramProjectType!="") {
  $headerParameters.= i18n("colIdProjectType") . ' : ' . htmlEncode(SqlList::getNameFromId('ProjectType', $paramProjectType)) . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ($paramMonth!="") {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ($paramYear!="") {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
}
if (isset($outMode) and $outMode=='excel') {
  $headerParameters.=str_replace('- ','<br/>',Work::displayWorkUnit()).'<br/>';
}

include "header.php";
/*__________________________________________________*/

$compStyle='font-size:10px;';
$lstVisibleProj=ConsolidationValidation::getVisibleProjectToConsolidated($paramProject, $paramProjectType, $idOrganization,true);
$lstProj=$lstVisibleProj[0];
$month=(strlen($paramMonth)==1)?'0'.$paramMonth:$paramMonth;
$concMonth=$paramYear.$month;

$reelTotal=0;
$leftWorkTotal=0;
$plannedWorkTotal=0;
$validatedWorkTotal=0;
$revenueTotal=0;
$marginTotal=0;
$reelConsTotal=0;


// top board
echo '<table  style="width:90%;margin-left:5%;margin-right:5%;" '.excelName().'>';
echo ' <tr>';
echo '   <td style="width:20%,border-bottom:2px solid black;" class="reportTableHeader" '.excelFormatCell('header',60).' rowspan="2" colspan="2">'.i18n('menuProject').'</td>';
echo '   <td style="width:10%" class="reportTableHeader" '.excelFormatCell('header',20).' rowspan="2">'.ucfirst(i18n('validatedConsolidation')).'</td>';
echo '   <td style="width:70%" class="reportTableHeader" '.excelFormatCell('header',20).' colspan="7">'.ucfirst(i18n('technicalWork')).'</td>';
echo ' </tr>';
echo ' <tr>';
echo '  <td  style="width:10%;" class="reportTableHeader" '.excelFormatCell('header',20).'>'.i18n('colRevenue').'</td>';
echo '  <td  style="width:10%;" class="reportTableHeader" '.excelFormatCell('header',20).'>'.i18n('colWorkApproved').'</td>';
echo '  <td  style="width:10%;" class="reportTableHeader" '.excelFormatCell('header',20).'>'.i18n('totalReal').'</td>';
echo '  <td  style="width:10%;" class="reportTableHeader" '.excelFormatCell('header',20).'>'.ucfirst(i18n('colRealCons')).'</td>';
echo '  <td  style="width:10%;" class="reportTableHeader" '.excelFormatCell('header',20).'>'.i18n('colRemainToDo').'</td>';
echo '  <td  style="width:10%;" class="reportTableHeader" '.excelFormatCell('header',20).'>'.ucfirst(i18n('colWorkReassessed')).'</td>';
echo '  <td  style="width:10%;" class="reportTableHeader" '.excelFormatCell('header',20).'>'.i18n('colMargin').'</td>';
echo ' </tr>';

if(isset($lstProj) and is_array($lstProj)){
  foreach ($lstProj as $proj){    // draw line for each project 
    if(empty($proj))continue;
    $consValPproj=SqlElement::getSingleSqlElementFromCriteria("ConsolidationValidation",array("idProject"=>$proj->id,"month"=>$concMonth));
    $consolidation=i18n('displayNo');
    if($consValPproj->id!=''){
      $consolidation=i18n('displayYes');
      $reel=$consValPproj->realWork;
      $leftWork=$consValPproj->leftWork;
      $plannedWork=$consValPproj->plannedWork;
      $validatedWork=$consValPproj->validatedWork;
      $revenue=$consValPproj->revenue;
      $margin=$consValPproj->margin;
      $reelCons=$consValPproj->realWorkConsumed;
    }else{
      $lstPeProject=$proj->ProjectPlanningElement;
      $reel=$lstPeProject->realWork;
      $leftWork=$lstPeProject->leftWork;
      $plannedWork=$lstPeProject->plannedWork;
      $validatedWork=$lstPeProject->validatedWork;
      $revenue=($lstPeProject->revenue!='')?$lstPeProject->revenue:0;
      $margin=$validatedWork-$plannedWork;
      $reelCons=ConsolidationValidation::getReelWorkConsumed($proj,$concMonth);
    }
    $colorCons=($consolidation==i18n('displayYes'))?"color:green;":"color:red;";
    $projectCode=($proj->projectCode!='')?$proj->projectCode:'-';
    
    $wbs=$proj->ProjectPlanningElement->wbsSortable;
    $split=explode('.', $wbs);
    $level=0;
    $testWbs='';
    foreach($split as $sp) {
      $testWbs.=(($testWbs)?'.':'').$sp;
      if (isset($levels[$testWbs])) $level=$levels[$testWbs]+1;
    }
    $levels[$wbs]=$level;
    $tab="";
    for ($j=1; $j<=$level; $j++) {
      $tab.='&nbsp;&nbsp;&nbsp;';
    }
    if($level==0){
      $revenueTotal+=$revenue;
      $validatedWorkTotal+=$validatedWork;
      $reelTotal+=$reel;
      $reelConsTotal+=$reelCons;
      $leftWorkTotal+=$leftWork;
      $plannedWorkTotal+=$plannedWork;
      $marginTotal+=$margin;
    }
    
    echo '  <tr>';
      echo '   <td class="reportTableData" style="border-right:1px solid grey;text-align:left;'.$compStyle.'" '.excelFormatCell('data',40,null,null,null,'left').' >&nbsp;'.$tab.$proj->name.'</td>';
      echo '   <td class="reportTableData" style="'.$compStyle.'" '.excelFormatCell('data',20).' >'.$projectCode.'</td>';
      echo '   <td class="reportTableData" style="'.$compStyle.''.$colorCons.'" '.excelFormatCell('data',null,null,null,null,null,null,null,'work').'>'.$consolidation.'</td>';
      echo '   <td class="reportTableData" style="'.$compStyle.'" '.excelFormatCell('data',null,null,null,null,null,null,null,'work').'>'.(($outMode=='excel')?$revenue:costFormatter($revenue)).'</td>';    
      echo '   <td class="reportTableData" style="'.$compStyle.'" '.excelFormatCell('data',null,null,null,null,null,null,null,'work').'>'.(($outMode=='excel')?$validatedWork:Work::displayWorkWithUnit($validatedWork)).'</td>';
      echo '   <td class="reportTableData" style="'.$compStyle.'" '.excelFormatCell('data',null,null,null,null,null,null,null,'work').'>'.(($outMode=='excel')?$reel:Work::displayWorkWithUnit($reel)).'</td>';
      echo '   <td class="reportTableData" style="'.$compStyle.'" '.excelFormatCell('data',null,null,null,null,null,null,null,'work').'>'.(($outMode=='excel')?$reelCons:Work::displayWorkWithUnit($reelCons)).'</td>';
      echo '   <td class="reportTableData" style="'.$compStyle.'" '.excelFormatCell('data',null,null,null,null,null,null,null,'work').'>'.(($outMode=='excel')?$leftWork:Work::displayWorkWithUnit($leftWork)).'</td>';
      echo '   <td class="reportTableData" style="'.$compStyle.'" '.excelFormatCell('data',null,null,null,null,null,null,null,'work').'>'.(($outMode=='excel')?$plannedWork:Work::displayWorkWithUnit($plannedWork)).'</td>';
      echo '   <td class="reportTableData" style="'.$compStyle.''.(($margin<0)?"color:red;":"").'" '.excelFormatCell('data',null,(($margin<0)?"#F50000":""),null,null,null,null,null,'work').'>'.(($outMode=='excel')?$margin:Work::displayWorkWithUnit($margin)).'</td>';
    echo '  </tr>';
  }
}
//Total line 
if (isset($outMode) and $outMode=='excel') {
  str_replace('- ','<br/>',Work::displayWorkUnit()).'<br/>';
}
echo '  <tr>';
echo '   <td class="reportTableHeader" colspan="3" '.excelFormatCell('header').' >'.i18n('sum').'</td>';
echo '   <td class="assignHeader" style="'.$compStyle.'" '.excelFormatCell('subheader',null,null,null,null,null,null,null,'work').'>'.costFormatter($revenueTotal).'</td>';
echo '   <td class="assignHeader" style="'.$compStyle.'" '.excelFormatCell('subheader',null,null,null,null,null,null,null,'work').'>'.Work::displayWorkWithUnit($validatedWorkTotal).'</td>';
echo '   <td class="assignHeader" style="'.$compStyle.'" '.excelFormatCell('subheader',null,null,null,null,null,null,null,'work').'>'.Work::displayWorkWithUnit($reelTotal).'</td>';
echo '   <td class="assignHeader" style="'.$compStyle.'" '.excelFormatCell('subheader',null,null,null,null,null,null,null,'work').'>'.Work::displayWorkWithUnit($reelConsTotal).'</td>';
echo '   <td class="assignHeader" style="'.$compStyle.'" '.excelFormatCell('subheader',null,null,null,null,null,null,null,'work').'>'.Work::displayWorkWithUnit($leftWorkTotal).'</td>';
echo '   <td class="assignHeader" style="'.$compStyle.'" '.excelFormatCell('subheader',null,null,null,null,null,null,null,'work').'>'.Work::displayWorkWithUnit($plannedWorkTotal).'</td>';
echo '   <td class="assignHeader" style="'.$compStyle.''.(($marginTotal<0)?"color:red;":"").'" '.excelFormatCell('subheaderred',null,null,null,null,null,null,null,'work').'>'.Work::displayWorkWithUnit($marginTotal).'</td>';
echo '  </tr>';
echo '</table>';