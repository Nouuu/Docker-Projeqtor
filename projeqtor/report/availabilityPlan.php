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
$idOrganization = trim(RequestHandler::getId('idOrganization'));
$paramTeam='';
if (array_key_exists('idTeam',$_REQUEST)) {
  $paramTeam=trim($_REQUEST['idTeam']);
  Security::checkValidId($paramTeam);
}
$user=getSessionUser();

$periodType=$_REQUEST['periodType']; // not filtering as data is only compared against fixed strings
$periodValue='';
if (array_key_exists('periodValue',$_REQUEST))
{
	$periodValue=$_REQUEST['periodValue'];
	$periodValue=Security::checkValidPeriod($periodValue);
}

// Header
$headerParameters="";
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
  
}
if ($periodType=='month') {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ($paramTeam!="") {
  $headerParameters.= i18n("colIdTeam") . ' : ' . SqlList::getNameFromId('Team', $paramTeam) . '<br/>';
}
include "header.php";

$where=getAccesRestrictionClause('Affectation',false,false,true,true);
//$where='('.$where.' or idProject in '.Project::getAdminitrativeProjectList().')';

$resources=array();
$resourceCalendar=array();
$aff=new Affectation();
$affLst=$aff->getSqlElementsFromCriteria(null,false, $where);
foreach($affLst as $aff){
	$ress=new ResourceAll($aff->idResource);
	if ($ress->id and !$ress->idle) {
    $resources[$ress->id]=htmlEncode($ress->name);
    $resourceCalendar[$ress->id]=$ress->idCalendarDefinition;
	}
	asort($resources);
}
if($idOrganization){
  $orga = new Organization($idOrganization);
  $listResOrg=$orga->getResourcesOfAllSubOrganizationsListAsArray();
  foreach ($resources as $idR=>$nameR){
    if(! in_array($idR, $listResOrg))unset($resources[$idR]);
  }
}

$where="1=1"; // Ticket #2532 : must show availability whatever the project 
$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';
$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
$order="";
//echo $where;
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
//$resources=array();

$capacity=array();
foreach ($resources as $id=>$name) {
	$capacity[$id]=SqlList::getFieldFromId('ResourceAll', $id, 'capacity');
  $result[$id]=array();
}
$real=array();
foreach ($lstWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    continue;
    //$resources[$work->idResource]=SqlList::getNameFromId('Affectable', $work->idResource);
    //$resourceCalendar[$work->idResource]=SqlList::getFieldFromId('Resource', $work->idResource, 'idCalendarDefinition');
    //$capacity[$work->idResource]=SqlList::getFieldFromId('Affectable', $work->idResource, 'capacity');
    //$result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idResource,$real)) {
  	$real[$work->idResource]=array();
  }
  if (! array_key_exists($work->day,$result[$work->idResource])) {
    $result[$work->idResource][$work->day]=0;
    $real[$work->idResource][$work->day]=true;
  }
  $result[$work->idResource][$work->day]+=$work->work;
}
$planWork=new PlannedWork();
$lstPlanWork=$planWork->getSqlElementsFromCriteria(null,false, $where, $order);
foreach ($lstPlanWork as $work) {
  if (! array_key_exists($work->idResource,$resources)) {
    continue;
    //$resources[$work->idResource]=SqlList::getNameFromId('Affectable', $work->idResource);
    //$resourceCalendar[$work->idResource]=SqlList::getFieldFromId('Resource', $work->idResource, 'idCalendarDefinition');
    //$capacity[$work->idResource]=SqlList::getFieldFromId('Affectable', $work->idResource, 'capacity');
    //$result[$work->idResource]=array();
  }
  if (! array_key_exists($work->idResource,$real)) {
    $real[$work->idResource]=array();
  }
  if (! array_key_exists($work->day,$result[$work->idResource])) {
    $result[$work->idResource][$work->day]=0;
  }
  //if (! array_key_exists($work->day,$real)) { // Do not add planned if real exists 
    $result[$work->idResource][$work->day]+=$work->work;
  //}
}

if ($periodType=='month') {
  $startDate=$periodValue. "01";
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
  $time=mktime(0, 0, 0, $paramMonth, 1, $paramYear);
  $header=i18n(strftime("%B", $time)).strftime(" %Y", $time);
  $nbDays=date("t", $time);
}
$weekendBGColor='#cfcfcf';
$weekendFrontColor='#555555';
$weekendStyle=' style="text-align: center;background-color:' . $weekendBGColor . '; color:' . $weekendFrontColor . '" ';
$plannedBGColor='#FFFFDD';
$plannedFrontColor='#777777';
$plannedStyle=' style="text-align:center;background-color:' . $plannedBGColor . '; color: ' . $plannedFrontColor . ';" ';

//if (checkNoData($result)) if (!empty($cronnedScript)) goto end; else exit;


echo '<table width="95%" align="center">';
echo '<tr><td>';
echo '<table width="100%" align="left">';
echo '<tr>';
echo "<td class='reportTableDataFull' style='width:20px;text-align:center;'>1</td>";
echo "<td width='100px' class='legend'>" . i18n('colRealWork') . "</td>";
echo "<td width='5px'>&nbsp;&nbsp;&nbsp;</td>";
echo '<td class="reportTableDataFull" ' . $plannedStyle . '><i>1</i></td>';
echo "<td width='100px' class='legend'>" . i18n('colPlanned') . "</td>";
echo "<td>&nbsp;</td>";
echo "<td class='legend'>" . Work::displayWorkUnit() . "</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
echo "</table>";
echo '</td></tr>';
echo '<tr><td>';
//echo '<br/>';
// title

echo '<table width="100%" align="left"><tr>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('colCapacity') . '</td>';
echo '<td colspan="' . ($nbDays+1) . '" class="reportTableHeader">' . $header . '</td>';
echo '<td class="reportTableHeader" rowspan="2" width=50px;>' . i18n('colNotPlannedWork'). '</td>';
echo '</tr><tr>';
$days=array();
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
    
    echo '<td class="reportTableColumnHeader" ' . $style . '>' . $day . '</td>';
  }  
}
echo '<td class="reportTableHeader" style="width:5%">' . i18n('sum') . '</td>';
echo '</tr>';

foreach ($resources as $idR=>$nameR) {
	//if ($paramTeam) {
  $res=new ResourceAll($idR);
  //}
  if ($res->isResourceTeam) {
    $maxCapa=$capacity[$idR];
  } else {
//    $maxCapa=$capacity[$idR];
    $maxCapa = 0;
    for ($i=1; $i<=$nbDays;$i++) {
      $day=$startDate+$i-1;
      $dayDate=substr($day,0,4) . "-" . substr($day,4,2) . "-" . substr($day,6,2);
      if($res->getCapacityPeriod($dayDate) > $maxCapa){
        $maxCapa = round($res->getCapacityPeriod($dayDate), 2);
      }
    }
  }
  if (!$paramTeam or $res->idTeam==$paramTeam) {
		$sum=0;
	  echo '<tr height="20px">';
	  echo '<td class="reportTableLineHeader" style="width:20%">' . $nameR . '</td>';
	  echo '<td class="reportTableLineHeader" style="width:5%;text-align:center;">';
	  if($capacity[$idR]*1 != $maxCapa){
  	  echo '<table width="100%"><tr><td style="width:50%;text-align:right;padding-right:10px;">'.($capacity[$idR]*1).'</td>';
  	  echo '<td style="width:50%;text-align:left;font-style:italic;">max('.$maxCapa.')</td></tr></table>';
	  }else{
	    echo ($capacity[$idR]*1);
	  }
	  echo '</td>';
	  for ($i=1; $i<=$nbDays;$i++) {
	    $day=$startDate+$i-1;
	    $style="";
	    $italic=false;
	    //if ($days[$day]=="off") {
	    if (isOffDay(substr($day,0,4) . "-" . substr($day,4,2) . "-" . substr($day,6,2), $resourceCalendar[$idR])) {	
	      $style=$weekendStyle;
	    } else {
	      $weekDate = substr($day, 0,4).'-'.substr($day, 4, -2).'-'.substr($day, 6);
	      if (array_key_exists($day,$result[$idR])) {
	        $val=$res->getCapacityPeriod($weekDate)-$result[$idR][$day];
	      } else {
	        $val=$res->getCapacityPeriod($weekDate);
	      }
	      $style=' style="text-align:center;';
	      //if (! array_key_exists($day,$real) and array_key_exists($day,$result[$idR])) {
	      if (array_key_exists($idR,$real) and ! array_key_exists($day,$real[$idR]) and array_key_exists($day,$result[$idR])) {
	        $style.='background-color:' . $plannedBGColor . ';';
	        $italic=true;
	      }
	      if ($val>0) {
	        $style.='color: #00AA00;';      	
	      } else if ($val < 0) {
	      	$style.='color: #FF0000;';
	      } else {
	      	$style.='color: ' . $plannedFrontColor . ';';
	      }
	      $style.='"';  
	    }
	    if ($style==$weekendStyle) {$val="";}
	    echo '<td class="reportTableDataFull" ' . $style . ' valign="middle">';    
	     if ($italic) {
	     	 echo '<i>' . Work::displayWork($val,2) . '</i>';
	     } else { 
	     	 echo Work::displayWork($val,2);
	     }
	  	echo '</td>';
	  	if ($val>0) {
	  		$sum+=$val;
	  	}
	  }
	  echo '<td class="reportTableColumnHeader" style="width:5%">' . Work::displayWork($sum) . '</td>';
	  //Krowry #2129
	  $ass= new Assignment();
	  $crit=array('idResource'=>$idR);
	  $npw=$ass->sumSqlElementsFromCriteria('notPlannedWork',$crit);
	  echo '<td class="reportTableData">'.Work::displayWork($npw).'</td>';
	  echo '</tr>';
  }
}

echo '</table>';

echo '</td></tr></table>';

end:
