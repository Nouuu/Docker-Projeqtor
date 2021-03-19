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

include_once '../tool/projeqtor.php';

$paramProject = trim(RequestHandler::getId('idProject'));
$paramProjectType = trim(RequestHandler::getId('idProjectType'));
$idOrganization = trim(RequestHandler::getId('idOrganization'));

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

if (isset($outMode) and $outMode=='excel') {
  $headerParameters.=str_replace('- ','<br/>',Work::displayWorkUnit()).'<br/>';
}

include "header.php";

$where="(".getAccesRestrictionClause('Activity',false,true,true,true) ." or idResource=". getSessionUser()->id . " or idProject in ".Project::getAdminitrativeProjectList().")";
if ($paramProject!='') {
  $where.=  " and idProject in " . getVisibleProjectsList(false, $paramProject);
}
if($paramProjectType!=''){
  $crit = array('idProjectType'=>$paramProjectType);
  $listProject = SqlList::getListWithCrit('Project', $crit);
  $where.= " and idProject in ". transformListIntoInClause($listProject);
}
$order="";
$work=new Assignment();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
$projects=array();
$resources=array();
$sumProj=array();

foreach ($lstWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('ResourceAll', $work->idResource);
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
  }
  if (! array_key_exists($work->idResource,$result)) {
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idProject,$result[$work->idResource])) {
    $result[$work->idResource][$work->idProject]=0;
  }
  $result[$work->idResource][$work->idProject]=round($result[$work->idResource][$work->idProject]+$work->leftWork,2);
}

if (checkNoData($result)) if (!empty($cronnedScript)) goto end; else exit;
// title
$newProject=array();
foreach ($projects as $id=>$name) {
  $newProject[SqlList::getFieldFromId('Project', $id, 'sortOrder').'-'.$id]=$name;
}

$projects=$newProject;
ksort($projects);
$listProjectsClause='(0';
foreach ($projects as $id=>$name) {
  $idExplo=explode('-',$id);
  $id=$idExplo[1];
  $sumProj[$id]=0;
  $listProjectsClause.=','.$id;
}
$listProjectsClause.=')';

asort($resources);
if($idOrganization){
  $orga = new Organization($idOrganization);
  $listOrga = $orga->getRecursiveSubOrganizationsFlatList(false,true);
  $listResOrg = array();
  foreach ($listOrga as $id=>$org){
    $org = new Organization($id);
    $listResOrg += $org->getResourcesOfOrganizationsListAsArray();
  }
  $listResOrg = array_flip($listResOrg);
  foreach ($resources as $idR=>$nameR){
    if(! in_array($idR, $listResOrg))unset($resources[$idR]);
  }
}
// Add left work on Activity isManualPlanning = left not liked to assignment
$pe=new PlanningElement();
$we=new WorkElement();
foreach ($sumProj as $id=>$val) {
  $sumPe=$pe->sumSqlElementsFromCriteria('leftWork', null, "idProject=$id and isManualProgress=1");
  $sumWe=$we->sumSqlElementsFromCriteria('leftWork', null, "idProject=$id and idActivity is null");
  $sum=$sumPe+$sumWe;
  if ($sum) {
    $key='xxx';
    if (! isset($resources[$key])) $resources[$key]=i18n('notAssignedWork');
    if (! isset($result[$key])) $result[$key]=array();
    $result[$key][$id]=$sum;
  }
}

foreach ($resources as $idR=>$nameR) {
    foreach ($projects as $idP=>$nameP) {
      $idExplo=explode('-',$idP);
      $idP=$idExplo[1];
      if (array_key_exists($idR, $result)) {
        if (array_key_exists($idP, $result[$idR])) {
          $val=$result[$idR][$idP];
          $sumProj[$idP]=round($sumProj[$idP]+$val,2);
        }
      }
    }
}

$nbProj=0;
$hasCode=false;
$arrayCodes=array();
foreach ($projects as $id=>$name) {
  $idExplo=explode('-',$id);
  $idS=$idExplo[1];
  if($sumProj[$idS] != 0){
    $cdProj=SqlList::getFieldFromId('Project',$idS,'projectCode');
  $arrayCodes[$id]=($cdProj)?$cdProj:'&nbsp;';
  if (trim($cdProj)!='') $hasCode=true;
  $nbProj+=1;
}
}
if($nbProj != 0)
  $colWidth=round(80/$nbProj);
else
  $colWidth=round(80/1);
$rowspan=($hasCode)?'3':'2';
echo '<table style="width:95%;" align="center" '.excelName().'>';
echo '<tr>';
echo '<td style="width:10%" class="reportTableHeader" rowspan="'.$rowspan.'" '.excelFormatCell('header',20).'>' . i18n('Resource') . '</td>';
echo '<td style="width:80%" colspan="' . $nbProj . '" class="reportTableHeader" '.excelFormatCell('header').'>' . i18n('Project') . '</td>';
echo '<td style="width:10%" class="reportTableHeader" rowspan="'.$rowspan.'" '.excelFormatCell('header',10).'>' . i18n('sum') . '</td>';
echo '</tr><tr>';
foreach ($projects as $id=>$name) {
  $idExplo=explode('-',$id);
  $id=$idExplo[1];
  if($sumProj[$id] != 0) {
    echo '<td style="width:'.$colWidth.'%" class="reportTableColumnHeader" '.excelFormatCell('subheader',20).'>' . htmlEncode($name) . '</td>';
  }
}
echo '</tr>';
if ($hasCode) {
  echo '<tr>';
  foreach ($projects as $id=>$name) {
    if (isset($arrayCodes[$id])) {
      echo '<td style="width:'.$colWidth.'%" class="reportTableColumnHeader" '.excelFormatCell('subheader',20).'>' . $arrayCodes[$id] . '</td>';
    }
  }
  echo '</tr>';
}

$sum=0;
foreach ($resources as $idR=>$nameR) {
    $sumRes=0;
    echo '<tr><td style="width:10%" class="reportTableLineHeader" '.excelFormatCell('rowheader').'>' . htmlEncode($nameR) . '</td>';
    foreach ($projects as $idP=>$nameP) {
       
      $idExplo=explode('-',$idP);
      $idP=$idExplo[1];
      if($sumProj[$idP] != 0){
        echo '<td style="width:' . $colWidth . '%" class="reportTableData" '.excelFormatCell('data',null,null,null,null,null,null,null,(($val)?'work':null)).'>';
        if (array_key_exists($idR, $result)) {
          if (array_key_exists($idP, $result[$idR])) {
            $val=$result[$idR][$idP];
            echo Work::displayWorkWithUnit($val);
            $sumRes=round($sumRes+$val,2);
            $sum=round($sum+$val,2);
            
          }
        }
        echo '</td>';
      }
    }
    echo '<td style="width:20%" class="reportTableColumnHeader" '.excelFormatCell('subheader').'>' . Work::displayWorkWithUnit($sumRes) . '</td>';
    echo '</tr>';
}
echo '<tr><td class="reportTableHeader" '.excelFormatCell('header').'>' . i18n('sum') . '</td>';
if ($nbProj == 0)
  echo '<td class="reportTableHeader" '.excelFormatCell('subheader').'>' . "" . '</td>';

foreach ($projects as $id=>$name) {
  $idExplo=explode('-',$id);
  $id=$idExplo[1];
  if($sumProj[$id] != 0)
    echo '<td class="reportTableColumnHeader" '.excelFormatCell('subheader').'>' . Work::displayWorkWithUnit($sumProj[$id]) . '</td>';
}
echo '<td style="white-space:nowrap;" class="reportTableHeader" '.excelFormatCell('header').'>' . Work::displayWorkWithUnit($sum) . '</td></tr>';
echo '</table>';

end:
