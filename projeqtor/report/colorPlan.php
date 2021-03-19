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

//echo "colorPlan.php";
include_once '../tool/projeqtor.php';

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
  Security::checkValidId($paramProject);
}
$idOrganization = trim(RequestHandler::getId('idOrganization'));
$paramTeam='';
if (array_key_exists('idTeam',$_REQUEST)) {
  $paramTeam=trim($_REQUEST['idTeam']);
  Security::checkValidId($paramTeam);
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
$paramShowAdminProj=RequestHandler::getBoolean('showAdminProj');

$user=getSessionUser();

$periodType=$_REQUEST['periodType']; // not filtering as data as data is only compared against fixed strings
$periodValue='';
if (array_key_exists('periodValue',$_REQUEST))
{
	$periodValue=$_REQUEST['periodValue'];
	$periodValue=Security::checkValidPeriod($periodValue);
}

// Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ($paramTeam!="") {
  $headerParameters.= i18n("colIdTeam") . ' : ' . htmlEncode(SqlList::getNameFromId('Team', $paramTeam)) . '<br/>';
}
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
  
}
if ($periodType=='month') {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}
$nbMonths=1;
if ($periodType=='month' and isset($_REQUEST['includeNextMonth'])) {
  $nbMonths=2;
  $headerParameters.= i18n("colIncludeNextMonth").'<br/>';
}
if ($paramShowAdminProj) {
  $headerParameters.= i18n("colShowAdminProj").'<br/>';
}

include "header.php";

$initParamMonth=$paramMonth;
// LOOP FOR SEVERAL MONTHS
for ($cptMonth=0;$cptMonth<$nbMonths;$cptMonth++) {
  if ($periodType=='month') {
    $paramMonth=intval($initParamMonth)+$cptMonth;
  if ($paramMonth>12) {$paramYear+=1;$paramMonth=1;}
    if ($paramMonth<10) $paramMonth='0'.$paramMonth;
    $periodValue=$paramYear.$paramMonth;
  }
  
$where=getAccesRestrictionClause('Resource',false,false,true,true);
//$where="1=1";
$where='('.$where.' or idProject in '.Project::getAdminitrativeProjectList().')';

$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';
$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
if ($paramProject!='') {
//   $where.=  "and ( idProject in " . getVisibleProjectsList(true, $paramProject) ;
//   if ($paramShowAdminProj) {
//     $where.= "or idProject in ".Project::getAdminitrativeProjectList();
//   }
//   $where.= ")";
}
$order="";
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
$projects=array();
$projectsColor=array();
$resources=array();
$resourcesTeam=array();
$resourceCapacity=array();
//gautier #2441
$idRessource=getSessionUser()->id;
$resss=new ResourceAll($idRessource);
$resourcesFull = array();
$resourcesAffect= array();
$resourcesToShow = array();
$specific='imputation';
$commonElement = getListForSpecificRights($specific,true);
$resourcesToShow=$commonElement;
//$commonElement = $table;
//no parameters
//project

if($paramProject){
  $proj=new Project($paramProject);
  $listProj=$proj->getRecursiveSubProjectsFlatList(false,true);
  if ($paramShowAdminProj) {
    foreach (Project::getAdminitrativeProjectList(true) as $idP=>$nameP) {
      $listProj[$idP]=$nameP;
    }
  }
  $resourcesAffect = SqlList::getListWithCrit('Affectation', array('idProject'=>array_keys($listProj)),'idResource');
  $resourcesProject = SqlList::getListWithCrit('ResourceAll', array('id'=>$resourcesAffect));
  $resourcesToShow = array_intersect($resourcesToShow,$resourcesProject);
} else {
  $resourcesNotClose = SqlList::getListWithCrit('ResourceAll', array('idle'=>'0'));
  $resourcesToShow = array_intersect($resourcesToShow,$resourcesNotClose);
}
//team
if($paramTeam){
  $resourcesTeam =SqlList::getListWithCrit('ResourceAll', array('idTeam'=>$paramTeam));
  $resourcesToShow = array_intersect($resourcesToShow,$resourcesTeam);
}
// organization
if($idOrganization){
  $orga = new Organization($idOrganization);
  $listResOrg=$orga->getResourcesOfAllSubOrganizationsListAsArray();
  foreach ($resourcesToShow as $idR=>$nameR){
    if(! in_array($idR, $listResOrg)) unset($resourcesToShow[$idR]);
  }
}
foreach ($lstWork as $work) {
  //if (! isset($resourcesToShow[$work->idResource])) continue;
  if ($paramProject and isset($listProj) and !isset($listProj[$work->idProject]) ) continue;
  if (! array_key_exists($work->idResource,$resources)) {
    if ($paramTeam) {
      $team=SqlList::getFieldFromId('Resource', $work->idResource,'idTeam');
      if ($team!=$paramTeam) continue;
    }
    if ($idOrganization) {
      $orga=SqlList::getFieldFromId('Resource', $work->idResource,'idOrganization');
      if ($orga!=$idOrganization) continue;
    }
  	$resources[$work->idResource]=SqlList::getNameFromId('ResourceAll', $work->idResource);
  	$resourceCapacity[$work->idResource]=SqlList::getFieldFromId('ResourceAll', $work->idResource, 'capacity');
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
    $proj=new Project($work->idProject);
    $projectsColor[$work->idProject]=$proj->getColor();
  }
  if (! array_key_exists($work->day,$result[$work->idResource])) {
    $result[$work->idResource][$work->day]=array();
  }
  if (! array_key_exists($work->idProject,$result[$work->idResource][$work->day])) {
    $result[$work->idResource][$work->day][$work->idProject]=0;
    $result[$work->idResource][$work->day]['real']=true;
  } 
  $result[$work->idResource][$work->day][$work->idProject]+=$work->work;
  //echo "work : " . htmlEncode($work->day) . " / " . htmlEncode($work->idProject) . " / " . htmlEncode($work->idResource) . " / " . htmlEncode($work->work) . "<br/>";
}
$planWork=new PlannedWork();
$lstPlanWork=$planWork->getSqlElementsFromCriteria(null,false, $where, $order);
foreach ($lstPlanWork as $work) {
  //if (! isset($resourcesToShow[$work->idResource])) continue;
  if ($paramProject and isset($listProj) and !isset($listProj[$work->idProject]) ) continue;
  if (! array_key_exists($work->idResource,$resources)) {
    if ($paramTeam) {
      $team=SqlList::getFieldFromId('Resource', $work->idResource,'idTeam');
      if ($team!=$paramTeam) continue;
    }
    if ($idOrganization) {
      $orga=SqlList::getFieldFromId('Resource', $work->idResource,'idOrganization');
      if ($orga!=$idOrganization) continue;
    }
    $resources[$work->idResource]=SqlList::getNameFromId('ResourceAll', $work->idResource);
    $resourceCapacity[$work->idResource]=SqlList::getFieldFromId('ResourceAll', $work->idResource, 'capacity');
    $result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
    $proj=new Project($work->idProject);
    $projectsColor[$work->idProject]=$proj->getColor();
  }
  if (! array_key_exists($work->day,$result[$work->idResource])) {
    $result[$work->idResource][$work->day]=array();
  }
  if (! array_key_exists($work->idProject,$result[$work->idResource][$work->day])) {
    $result[$work->idResource][$work->day][$work->idProject]=0;
  }
  if (! array_key_exists('real',$result[$work->idResource][$work->day])) { // Do not add planned if real exists 
    $result[$work->idResource][$work->day][$work->idProject]+=$work->work;
  }
}

if ($periodType=='month') {
  $startDate=$periodValue. "01";
  if (!$paramYear) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('year'))); // TODO i18n message
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  } 
  $time=mktime(0, 0, 0, $paramMonth, 1, $paramYear);
  $header=i18n(strftime("%B", $time)).strftime(" %Y", $time);
  $nbDays=date("t", $time);
}
$weekendBGColor='#cfcfcf';
$weekendFrontColor='#555555';
$weekendStyle=' style="background-color:' . $weekendBGColor . '; color:' . $weekendFrontColor . '" ';

$resourcesToShow=array_merge_preserve_keys($resourcesToShow,$resources);
$month=$paramYear.'-'.$paramMonth;
if (count($resourcesToShow)==0 and checkNoData($result,$month)) continue;

echo '<table width="95%" align="center">';
echo '<tr><td>';
echo '<table width="100%" align="left">';
echo '<tr>';
echo '<td class="reportTableDataFull">';
echo '<div style="height:20px;width:20px;position:relative;background-color:#DDDDDD;">&nbsp;';
echo '<div style="width:20px;position:absolute;top:3px;left:5px;color:#000000;">R</div>';
echo '<div style="width:20px;position:absolute;top:2px;left:6px;color:#FFFFFF;">R</div>';
echo '</div>';
echo '</td><td style="width:100px; padding-left:5px;" class="legend">' . i18n('colRealWork') . '</td>';
echo '<td style="width:5px";>&nbsp;&nbsp;&nbsp;</td>';
echo '<td class="reportTableDataFull">';
echo '<div style="height:20px;width:20px;position:relative;background-color:#DDDDDD;">&nbsp;';
echo '</div>';
echo '</td><td style="width;100px; padding-left:5px;" class="legend">' . i18n('colPlanned') . '</td>';
echo '<td>&nbsp;</td>';
echo "</tr></table>";
//echo "<br/>";
echo '<table width="100%" align="left"><tr>';
$sortProject=array();
foreach ($projects as $id=>$name) {
  $sortProject[SqlList::getFieldFromId('Project', $id, 'sortOrder').'#'.$id]=$name;
}
ksort($sortProject);
$projects=array();
foreach ($sortProject as $sortId=>$name) {
  $split=explode('#', $sortId);
  $projects[$split[1]]=$name;
}
$cptProj=0;
foreach($projects as $idP=>$nameP) {
	if ((($cptProj) % 8)==0) { echo '</tr><tr>';}
	$cptProj++;
  echo '<td width="20px">';
  echo '<div style="border:1px solid #AAAAAA ;height:20px;width:20px;position:relative;background-color:' . (($projectsColor[$idP])?$projectsColor[$idP]:"#FFFFFF") . ';">&nbsp;';
  echo '</div>';
  echo '</td><td style="width:100px; padding-left:5px;" class="legend">' . htmlEncode($nameP) . '</td>';
  echo '<td width="5px">&nbsp;&nbsp;&nbsp;</td>';
}

echo '<td>&nbsp;</td></tr></table>';
//echo '<br/>';
// title
echo '<table align="center"><tr><td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
echo '<td colspan="' . $nbDays . '" class="reportTableHeader">' . $header . '</td>';
echo '</tr><tr>';
$days=array();
$daysCal=array();
$lstCal=SqlList::getList('CalendarDefinition');
foreach($lstCal as $idCal=>$nameCal) {$daysCal[$idCal]=array();}
for($i=1; $i<=$nbDays;$i++) {
  if ($periodType=='month') {
    $day=(($i<10)?'0':'') . $i;
    if (isOffDay(substr($periodValue,0,4) . "-" . substr($periodValue,4,2) . "-" . $day)) {
      $days[$periodValue . $day]="off";
      $style=$weekendStyle;
    } else {
      $days[$periodValue . $day]="open";
      $style='';
    }
    foreach($lstCal as $idCal=>$nameCal) {
      if (isOffDay(substr($periodValue,0,4) . "-" . substr($periodValue,4,2) . "-" . $day,$idCal)) {
        $daysCal[$idCal][$periodValue . $day]="off";
      } else {
        $daysCal[$idCal][$periodValue . $day]="open";
      }
    }
    echo '<td class="reportTableColumnHeader" ' . $style . '>' . $day . '</td>';
  }  
}

echo '</tr>';

asort($resourcesToShow);
//asort($resources);
foreach ($resourcesToShow as $idR=>$nameR) {
  $idCal=SqlList::getFieldFromId('Affectable', $idR, 'idCalendarDefinition');
	//if ($paramTeam) {
    $res=new Resource($idR);
  //}
  if (!$paramTeam or $res->idTeam==$paramTeam) {
    //gautier
    if(array_key_exists($idR,$resourceCapacity)){
  	  $capacity=$resourceCapacity[$idR];
    }else{
      $capacity=SqlList::getFieldFromId('ResourceAll', $idR, 'capacity');
    }
    $maxCapa = 0;
    for ($i=1; $i<=$nbDays;$i++) {
    	$day=$startDate+$i-1;
    	$weekDate = substr($day, 0,4).'-'.substr($day, 4, -2).'-'.substr($day, 6);
    	if($res->getCapacityPeriod($weekDate) > $maxCapa){
    		$maxCapa = round($res->getCapacityPeriod($weekDate), 2);
    	}
    }
	  echo '<tr height="20px"><td class="reportTableLineHeader" style="width:200px">' . $nameR;
	  echo '<div style="float:right;font-size:80%;color:#A0A0A0;">';
	  if($capacity != $maxCapa){
	  	echo '<table width="100%"><tr><td style="width:50%;text-align:right;padding-right:10px;">'.htmlDisplayNumericWithoutTrailingZeros($capacity).'</td>';
	  	echo '<td style="width:50%;text-align:left;font-style:italic;">max('.$maxCapa.')</td></tr></table>';
	  }else{
	  	echo htmlDisplayNumericWithoutTrailingZeros($capacity);
	  }
	  echo '</div>';
	  echo '</td>';
	  for ($i=1; $i<=$nbDays;$i++) {
	    $day=$startDate+$i-1;
	    $style="";
	    //if ($days[$day]=="off") {
	    if ($idCal and $daysCal[$idCal][$day]=="off") {
	      $style=$weekendStyle;
	    }
	    echo '<td class="reportTableDataFull" ' . $style . ' valign="top">';
	    // test day and result
	  //if (array_key_exists($resources[$idR],$result) and array_key_exists($resources[$idR],$days )){
	  if(isset($result[$idR])){
	    if (array_key_exists($day,$result[$idR])) {
	      echo "<div style='position:relative;'>";
	      $real=false;
	      foreach ($result[$idR][$day] as $idP=>$val) {
	        if ($idP=='real') {
	          $real=true;
	        } else {
	          if($capacity != 0){
	            $height=floor(20*$val/$capacity);
	            echo "<div style='position:relative;height:" . $height . "px; background-color:" . $projectsColor[$idP] . ";' ></div>";
	          }	          
	        }
	      }
	      if ($real) {
	        echo "<div style='position:absolute;top:3px;left:5px;color:#000000;'>R</div>";
	        echo "<div style='position:absolute;top:2px;left:6px;color:#FFFFFF;'>R</div>";
	      }
	      echo "</div>";
	    
	    }
	  }
	    echo '</td>';
	  }
	  echo '</tr>';
  }
}
echo '</table>';
echo '</td></tr></table>';

echo '<br/><br/>';

// END OF LOOP ON MONTH
}

end:
