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


$listResource = array();
$size=30;

PlannedWorkManual::setSize($size);
$headerParameters="";
$resourceId =trim(RequestHandler::getId('idResource'));
$idProject = trim(RequestHandler::getId('idProject'));
if ($idProject =="*"){
  $idProject="*";
}else if (strpos($idProject, ",") != null) {
  $idProject=explode(",", $idProject);
}
$yearSpinner= RequestHandler::getYear('yearSpinner');
$monthSpinner= RequestHandler::getMonth('monthSpinner');
$inIdTeam = trim(RequestHandler::getId('idTeam'));
$inIdOrga = trim(RequestHandler::getId('idOrganization'));
$onlyRes = false;

if (!$resourceId and !$inIdTeam and !$inIdOrga) {
  $resourceId=getCurrentUserId();
}

if ($yearSpinner!="") {
  $headerParameters.= i18n("year") . ' : ' . $yearSpinner . '<br/>';
};
if ($monthSpinner!="") {
  $headerParameters.= i18n("month") . ' : ' . $monthSpinner . '<br/>';
};
// Header
if ($resourceId!="") {
  $headerParameters.= i18n("colIdResource") . ' : ' . htmlEncode(SqlList::getNameFromId('Affectable',$resourceId)) . '<br/>';
}
if ($idProject!=" "  and $idProject!="*" and !is_array($idProject)) {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $idProject)) . '<br/>';
}else if ($idProject=="*"){
  $headerParameters.= i18n("colIdProject") . ' : ' .i18n("allProjects");
}else{
  $headerParameters.= i18n("colIdProject") . ' : ';
  $lenght= count($idProject);
  $c=0;
  foreach ($idProject as $proj){
    $c++;
    $headerParameters.= htmlEncode(SqlList::getNameFromId('Project', $proj)).(($c==$lenght)?'':'/ ');
  }
  $headerParameters.= '<br/>';
}

if ($inIdOrga!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$inIdOrga)) . '<br/>';
}
if ($inIdTeam!="") {
  $headerParameters.= i18n("colIdTeam") . ' : ' . SqlList::getNameFromId('Team', $inIdTeam) . '<br/>';
}


include "header.php";

if ($resourceId and !$inIdTeam and !$inIdOrga) {
  $listResource[0] = $resourceId;
  $onlyRes = true;
}else{
  $res = new Resource();
  if(!$resourceId and $inIdTeam and !$inIdOrga){
    $listResourceObj = $res->getSqlElementsFromCriteria(array('idTeam'=>$inIdTeam,'idle'=>'0'),null,null,null,true);
  }elseif(!$resourceId and !$inIdTeam and $inIdOrga){
    $listResourceObj = $res->getSqlElementsFromCriteria(array('idOrganization'=>$inIdOrga,'idle'=>'0'),null,null,null,true);
  }elseif($resourceId and $inIdTeam and $inIdOrga){
    $listResourceObj = $res->getSqlElementsFromCriteria(array('id'=>$resourceId,'idTeam'=>$inIdTeam,'idOrganization'=>$inIdOrga,'idle'=>'0'),null,null,null,true);
  }elseif($resourceId and $inIdTeam and !$inIdOrga){
    $listResourceObj = $res->getSqlElementsFromCriteria(array('id'=>$resourceId,'idTeam'=>$inIdTeam,'idle'=>'0'),null,null,null,true);
  }elseif($resourceId and !$inIdTeam and $inIdOrga){
    $listResourceObj = $res->getSqlElementsFromCriteria(array('id'=>$resourceId,'idOrganization'=>$inIdOrga,'idle'=>'0'),null,null,null,true);
  }elseif(!$resourceId and $inIdTeam and $inIdOrga){
    $listResourceObj = $res->getSqlElementsFromCriteria(array('idTeam'=>$inIdTeam,'idOrganization'=>$inIdOrga,'idle'=>'0'),null,null,null,true);
  }
  if (isset($listResourceObj) and is_array($listResourceObj)) {
    foreach ($listResourceObj as $obj) {
      $listResource[]=$obj->id;
    }
  }
}


  echo' <table id="bodyPlanMan" name="bodyPlanMan"  style="margin-left:15px;">';
  echo'  <tr>';
  echo'    <td colspan="2">';       
                if(isset($idProject)){
                 if((!is_array($idProject) and trim($idProject)=='' ) or $idProject=="*"){
                    PlannedWorkManual::drawActivityTable(null,$yearSpinner.$monthSpinner,true);
                  }else{
                    PlannedWorkManual::drawActivityTable($idProject,$yearSpinner.$monthSpinner,true);
                  }
                }else{
                    PlannedWorkManual::drawActivityTable(null,$yearSpinner.$monthSpinner,true);
                }
  
  echo'    </td>';
  echo'  </tr>';
  echo'  <tr><td>';
  echo'     <div style="height:15px;">&nbsp;</div>';
  echo'  </td></tr>';
  echo'  <tr>';
  echo'    <td>';
  echo'       <div style="width:240px;">';
                  InterventionMode::drawList(true);
  echo'       </div>';
  echo'    </td>';
  echo'    <td >';
  echo'       <div style="min-width:1123px;margin-left:201px;top:20px;">';
                  $listMonth=array($yearSpinner.$monthSpinner);
                  if(!$onlyRes){
                    foreach ($listResource as $id=>$val){
                      $listResource[$id]=$val;
                    } 
                  }
                    PlannedWorkManual::drawTable('intervention',$listResource, $listMonth, null, true);
  echo'       </div>';
  echo'     </td>';
  echo'  </tr>';
  echo' </table>';
  
 ?>