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
$idOrganization = trim(RequestHandler::getId('idOrganization'));
$paramTeam='';
if (array_key_exists('idTeam',$_REQUEST)) {
  $paramTeam=trim($_REQUEST['idTeam']);
  Security::checkValidId($paramTeam);
}
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

$user=getSessionUser();

$paramResource='';
if (array_key_exists('idResource',$_REQUEST)) {
  $paramResource=Security::checkValidId(trim($_REQUEST['idResource']));
  $canChangeResource=false;
  $crit=array('idProfile'=>$user->idProfile, 'scope'=>'reportResourceAll');
  $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
  if ($habil and $habil->id and $habil->rightAccess=='1') {
    $canChangeResource=true;
  }
  if (!$canChangeResource and $paramResource!=$user->id) {
    echo i18n('messageNoAccess',array(i18n('colReport')));
    if (!empty($cronnedScript)) goto end; else exit;
  }
}

$periodType=$_REQUEST['periodType']; // not filtering as data as data is only compared against fixed strings
$periodValue='';
if (array_key_exists('periodValue',$_REQUEST))
{
	$periodValue=$_REQUEST['periodValue'];
	$periodValue=Security::checkValidPeriod($periodValue);
}

// Header
$headerParameters="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
	$idProject = trim($_REQUEST['idProject']);
	$idProject = Security::checkValidId($idProject);
	$headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $idProject)) . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ($paramTeam!="") {
  $headerParameters.= i18n("colIdTeam") . ' : ' . htmlEncode(SqlList::getNameFromId('Team', $paramTeam)) . '<br/>';
}
if ( trim($paramResource)) {
  $headerParameters.= i18n("colIdResource") . ' : ' . htmlEncode(SqlList::getNameFromId('Affectable',$paramResource)) . '<br/>';
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

$where=getAccesRestrictionClause('Activity',false,false,true,true);
$where='('.$where.' or idProject in '.Project::getAdminitrativeProjectList().')';

if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
	$idProject = trim($_REQUEST['idProject']);
	$idProject = Security::checkValidId($idProject);
	$where.= ($where=='')?'':' and ';
	$where.=  " idProject in " . getVisibleProjectsList(true, $idProject) ;
}
  
$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';
$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
$today=date('Y-m-d');
/*if ($paramResource!='') {
  $ressList=Sql::fmtId($paramResource);
  $rta=new ResourceTeamAffectation();
  $rtaList=$rta->getSqlElementsFromCriteria(array('idResource'=>$paramResource));
  foreach ($rtaList as $rta) {
    if ($rta->idle) continue;
    if ($rta->endDate==null or $rta->endDate>=$today) {
      $ressList.=','.Sql::fmtId($rta->idResourceTeam);
    }
  }
  $where.=" and idResource in ($ressList)";
}*/
$where.=($paramResource!='')?" and idResource='" . $paramResource . "'":'';
$order="";
//echo $where;
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
$projects=array();
$resources=array();
$activities=array();
$realDays=array();
foreach ($lstWork as $work) {
	$ref=$work->refType . "#" . $work->refId;
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Affectable', $work->idResource);
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
    $result[$work->idProject]=array();
    $realDays[$work->idProject]=array();
  }
  if (! array_key_exists($ref,$result[$work->idProject])) {
    $result[$work->idProject][$ref]=array();
    $realDays[$work->idProject][$ref]=array();
  }
  if (! array_key_exists($ref,$activities)) {
    $activities[$ref]=SqlList::getNameFromId($work->refType,  $work->refId);
  }
  if (! array_key_exists($work->idResource,$result[$work->idProject][$ref])) {
    $result[$work->idProject][$ref][$work->idResource]=array();
    $realDays[$work->idProject][$ref][$work->idResource]=array();
  }  
  if (! array_key_exists($work->day,$result[$work->idProject][$ref][$work->idResource])) {
    $result[$work->idProject][$ref][$work->idResource][$work->day]=0;
    $realDays[$work->idProject][$ref][$work->idResource][$work->day]='real';
  } 
  $result[$work->idProject][$ref][$work->idResource][$work->day]+=$work->work;
}

$planWork=new PlannedWork();
$lstPlanWork=$planWork->getSqlElementsFromCriteria(null,false, $where, $order);
foreach ($lstPlanWork as $work) {
	$ref=$work->refType . "#" . $work->refId;
  if (! array_key_exists($work->idResource,$resources)) {
    $resources[$work->idResource]=SqlList::getNameFromId('Affectable', $work->idResource);
  }
  if (! array_key_exists($work->idProject,$projects)) {
    $projects[$work->idProject]=SqlList::getNameFromId('Project', $work->idProject);
    $result[$work->idProject]=array();
    $realDays[$work->idProject]=array();
  }
  if (! array_key_exists($ref,$activities)) {
    $activities[$ref]=SqlList::getNameFromId($work->refType,  $work->refId);
  }
  if (! array_key_exists($ref,$result[$work->idProject])) {
    $result[$work->idProject][$ref]=array();
    $realDays[$work->idProject][$ref]=array();
  }
  if (! array_key_exists($work->idResource,$result[$work->idProject][$ref])) {
    $result[$work->idProject][$ref][$work->idResource]=array();
    $realDays[$work->idProject][$ref][$work->idResource]=array();
  }
  if (! array_key_exists($work->day,$result[$work->idProject][$ref][$work->idResource])) {
    $result[$work->idProject][$ref][$work->idResource][$work->day]=0;
  }
  if (! array_key_exists($work->day,$realDays[$work->idProject][$ref][$work->idResource])) { // Do not add planned if real exists 
  	 $result[$work->idProject][$ref][$work->idResource][$work->day]+=$work->work;
  } else if ($work->day>date('Ymd')) {
    $result[$work->idProject][$ref][$work->idResource][$work->day]+=$work->work;
    if (isset($realDays[$work->idProject][$ref][$work->idResource][$work->day])) {
      unset($realDays[$work->idProject][$ref][$work->idResource][$work->day]);
    }
  }
}

if ($periodType=='month') {
  $startDate=$periodValue. "01";
  if (!$paramYear ) {
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
$plannedBGColor='#FFFFDD';
$plannedFrontColor='#777777';
$plannedStyle=' style="text-align:center;background-color:' . $plannedBGColor . '; color: ' . $plannedFrontColor . ';" ';

$month=$paramYear.'-'.$paramMonth;
if (checkNoData($result,$month)) continue;

echo "<table width='95%' align='center'>";
echo "<tr><td><table  width='100%' align='left'><tr>";
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
//echo "<br/>";

// title
echo '<table width="100%" align="left">';
echo '<tr>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Project') . '</td>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Activity') . '</td>';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
echo '<td colspan="' . ($nbDays+1) . '" class="reportTableHeader">' . $header . '</td>';
echo '<td class="reportTableHeader" rowspan="2" width=50px;>' . i18n('colNotPlannedWork'). '</td>';
echo '</tr>';
echo '<tr>';
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
echo '<td class="reportTableHeader" >' . i18n('sum'). '</td>';
echo '</tr>';

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

asort($resources);
if($idOrganization){
  $orga = new Organization($idOrganization);
  $listResOrg=$orga->getResourcesOfAllSubOrganizationsListAsArray();
  foreach ($resources as $idR=>$nameR){
    if(! in_array($idR, $listResOrg))unset($resources[$idR]);
  }
}
if ($paramTeam) {
  foreach ($resources as $idR=>$ress) {
    $res=new Resource($idR);
    if ($res->idTeam!=$paramTeam) {
      unset($resources[$idR]);
    }
  }
}
  foreach ($projects as $idP=>$nameP) {
  	foreach($result[$idP] as $idA=>$acti) {
	    foreach ($result[$idP][$idA] as $idR=>$ress) {
	      if (! isset($resources[$idR]) ) {
	        unset  ($result[$idP][$idA][$idR]);
	        if (count($result[$idP][$idA])==0 ) {
	          unset ($result[$idP][$idA]);
	          if (count($result[$idP])==0 ) {
	          	 unset ($result[$idP]);
	          	 unset($projects[$idP]);
	          }          
	        }
	      }
	    }
  	}
  }

$globalSum=array();
for ($i=1; $i<=$nbDays;$i++) {
  $globalSum[$startDate+$i-1]=0;
}
foreach ($projects as $idP=>$nameP) {
  $sum=array();
  for ($i=1; $i<=$nbDays;$i++) {
    $sum[$startDate+$i-1]=0;
  }
  echo '<tr height="20px">';
  $cpt=0;
  foreach ($result[$idP] as $res) { 
    $cpt+=count($res);
  }
  $cpt+=1;
  echo '<td class="reportTableLineHeader" style="width:100px;" rowspan="'. ($cpt) . '">' . htmlEncode($nameP) . '</td>';
  foreach ($result[$idP] as $idA=>$acti) {
    foreach ($result[$idP][$idA] as $idR=>$ress) {
      $sumNpw=0;
	    if (array_key_exists($idA, $activities)) {
	      echo '<td class="reportTableData" style="width:100px;text-align: left;">' . htmlEncode($activities[$idA]) . '</td>';
	      echo '<td class="reportTableData" style="width:100px;text-align: left;">' . htmlEncode($resources[$idR]) . '</td>';
        
	      $lineSum=0;
	      for ($i=1; $i<=$nbDays;$i++) {
	        $day=$startDate+$i-1;
	        $style="";
	        $ital=false;
	        if ($days[$day]=="off") {
	          $style=$weekendStyle;
	        } else {
	          if (  ! array_key_exists($day, $realDays[$idP][$idA][$idR])  
	          and array_key_exists($day,$result[$idP][$idA][$idR])) {
	            $style=$plannedStyle;
	            $ital=true;
	          }
	        }
	        echo '<td class="reportTableData" ' . $style . ' valign="top">';
	        if (array_key_exists($day,$result[$idP][$idA][$idR])) {
	          echo ($ital)?'<i>':'';
	          echo Work::displayWork($result[$idP][$idA][$idR][$day]);
	          echo ($ital)?'</i>':'';
	          $sum[$day]+=$result[$idP][$idA][$idR][$day];
	          $globalSum[$day]+=$result[$idP][$idA][$idR][$day];
	          $lineSum+=$result[$idP][$idA][$idR][$day];
	        }
	        echo '</td>';
	      }
	      echo '<td class="reportTableColumnHeader">' . Work::displayWork($lineSum) . '</td>';
	      //Krowry #2129
	      $ass= new Assignment();
	      $split=explode('#', $idA);
	      $crit=array('idResource'=>$idR, 'idProject'=>$idP, 'refId'=>$split[1] , 'refType'=>$split[0]);
	      $npw=$ass->sumSqlElementsFromCriteria('notPlannedWork',$crit);
	      $sumNpw+=$npw;
	      echo '<td class="reportTableData">'.Work::displayWork($npw).'</td>';
	       
	      echo '</tr><tr>';
	    }
    }
  }
  echo '<td class="reportTableLineHeader" colspan="2">' . i18n('sum') . '</td>';
  $lineSum=0;
  for ($i=1; $i<=$nbDays;$i++) {
    $style='';
    $day=$startDate+$i-1;
    if ($days[$day]=="off") {
          $style=$weekendStyle;
    }
    echo '<td class="reportTableColumnHeader" ' . $style . ' >' . Work::displayWork($sum[$startDate+$i-1]) . '</td>';
    $lineSum+=$sum[$startDate+$i-1];
  }
  echo '<td class="reportTableHeader" >' . Work::displayWork($lineSum) . '</td>';
  echo '<td class="reportTableHeader">' . Work::displayWork($sumNpw) . '</td>';
  echo '</tr>';
  
}

echo '<tr><td colspan="' . ($nbDays+3) . '">&nbsp;</td></tr>';
echo '<tr><td class="reportTableHeader" colspan="3">' . i18n('sum') . '</td>';
$lineSum=0;
for ($i=1; $i<=$nbDays;$i++) {
  $style='';
  $day=$startDate+$i-1;
  if ($days[$day]=="off") {
    $style=$weekendStyle;
  }
  $sumDay=$globalSum[$startDate+$i-1];
  if ($paramResource) {
    $res=new Resource($paramResource);
    $day=$startDate+$i-1;
    $day=substr($day,0,4).'-'.substr($day,4,2).'-'.substr($day,6,2);
    if ($res->getCapacityPeriod($day)<$sumDay) {
      $style=' style="color:#ffc0c0 !important;font-weight:bold"';
    }
  }
  echo '<td class="reportTableHeader" ' . $style . '>' . Work::displayWork($sumDay) . '</td>';
  $lineSum+=$globalSum[$startDate+$i-1];
}
echo '<td class="reportTableHeader">' . Work::displayWork($lineSum) . '</td>';
echo '</tr>';
echo '</table>';
echo '</td></tr></table>';

echo '<br/><br/>';
// END OF LOOP ON MONTH
}

end:
