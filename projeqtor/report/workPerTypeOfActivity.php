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
//echo "workPerTypeOfActivity.php";

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
  Security::checkValidId($paramProject);
}
//#407
$paramActivityType='';
if (array_key_exists('idActivityType', $_REQUEST)) {
  $paramActivityType=trim($_REQUEST['idActivityType']);
  Security::checkValidId($paramActivityType);
}
//End #407
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
} else {
  $paramMonth="01";
}
$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
	$paramWeek=$_REQUEST['weekSpinner'];
	$paramWeek=Security::checkValidWeek($paramWeek);
};
$showDetail=false;
if (array_key_exists('showDetail',$_REQUEST)) {
  $showDetail=true;
}

$user=getSessionUser();

$paramResource='';
if (array_key_exists('idResource',$_REQUEST)) {
  $paramResource=trim($_REQUEST['idResource']);
  $paramResource = preg_replace('/[^0-9]/', '', $paramResource); // only allow digits
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
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramTeam!="") {
  $headerParameters.= i18n("colIdTeam") . ' : ' . htmlEncode(SqlList::getNameFromId('Team', $paramTeam)) . '<br/>';
}
//#407
if ($paramActivityType!="") {
  $headerParameters.= i18n("colIdActivityType") . ' : ' . htmlEncode(SqlList::getNameFromId('ActivityType', $paramActivityType)) . '<br/>';
}
//End #407
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
}
//ADD qCazelles - Report fiscal year - Ticket #128
if ($periodType=='year' and $paramMonth!="01") {
  if (!$paramMonth ) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('month'))); // TODO i18n message
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  } else {
    $headerParameters.= i18n("startMonth") . ' : ' . i18n(date('F', mktime(0,0,0,$paramMonth,10))) . '<br/>';
  }
}
//END ADD qCazelles - Report fiscal year - Ticket #128
if ($periodType=='month') {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}
if ( $paramResource=='') {
  $headerParameters.= i18n("colIdResource") . ' : ' . htmlEncode(SqlList::getNameFromId('Resource',$paramResource)) . '<br/>';
}
include "header.php";


#florent ticket #4049
#$where="(".getAccesRestrictionClause('Activity',false,false,true,true) ." or idResource=". getSessionUser()->id . " or idProject in ".Project::getAdminitrativeProjectList().")";
$where="(".getAccesRestrictionClause('Activity',false,true,true,true) ." or idResource=". getSessionUser()->id . " or idProject in ".Project::getAdminitrativeProjectList().")"; 
//$where="1=1 ";
$where.=($periodType=='week')?" and week='" . $periodValue . "'":'';
$where.=($periodType=='month')?" and month='" . $periodValue . "'":'';

//CHANGE qCazelles - Report start month - Ticket #128
//Old
//$where.=($periodType=='year')?" and year='" . $periodValue . "'":'';
//New
if ($periodType=='year') {
  if (!$periodValue ) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('year'))); // TODO i18n message
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
  if ($paramMonth<10) $paramMonth='0'.intval($paramMonth);
  $where.=" and ((year='" . $periodValue . "' and month>='" . $periodValue.$paramMonth . "')".
          " or (year='" . ($periodValue + 1) . "' and month<'" . ($periodValue + 1) . $paramMonth . "'))";
}
//END CHANGE qCazelles - Report start month - Ticket #128

if ($paramProject!='') {
  #florent ticket #4049
  #$where.=  " and idProject in " . getVisibleProjectsList(true, $paramProject); 
  $where.=  " and idProject in " . getVisibleProjectsList(false, $paramProject); 
}
$where.=($paramResource!='')?" and idResource='" . $paramResource . "'":'';
$order="";
//echo $where;
$work=new Work();
$lstWork=$work->getSqlElementsFromCriteria(null,false, $where, $order);
$result=array();
$projects=array();
$resources=array();
$sumProj=array();
$sumWorkAct=array();
$actNames=array();
$workActivity=array();
$resActTypes=array();
foreach ($lstWork as $work) {
  $act = new Activity($work->refId);
  $actType = new activityType($act->idActivityType);
  if ($paramActivityType == "" or $paramActivityType == $act->idActivityType) {
    if (! array_key_exists($work->idResource,$resources)) {
      $resources[$work->idResource]=SqlList::getNameFromId('Resource', $work->idResource);
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
    // #407
    // #ActNames;
    if (!array_key_exists($work->idResource, $actNames)) {
      $actNames[$work->idResource] = array();
      $actNames[$work->idResource][$actType->name] = array();
      $actNames[$work->idResource][$actType->name][$act->name] = $work->work + 0;
    }
    else if (!array_key_exists($actType->name, $actNames[$work->idResource])) {
      $actNames[$work->idResource][$actType->name] = array();
      $actNames[$work->idResource][$actType->name][$act->name] = $work->work + 0;
    }
    else if (!array_key_exists($act->name, $actNames[$work->idResource][$actType->name])) {
      $actNames[$work->idResource][$actType->name][$act->name] = $work->work + 0;
    }
    else
      $actNames[$work->idResource][$actType->name][$act->name] += $work->work;
    // #workActivity
    if (!array_key_exists($work->idResource, $workActivity)) {
      $workActivity[$work->idResource] = array();
      $workActivity[$work->idResource][$work->idProject] = array();
      $workActivity[$work->idResource][$work->idProject][$actType->name] = array();
      $workActivity[$work->idResource][$work->idProject][$actType->name][$act->name] = $work->work + 0;
    }
    else if (!array_key_exists($work->idProject, $workActivity[$work->idResource])) {
      $workActivity[$work->idResource][$work->idProject] = array();
      $workActivity[$work->idResource][$work->idProject][$actType->name] = array();
      $workActivity[$work->idResource][$work->idProject][$actType->name][$act->name] = $work->work + 0;
    }
    else if (!array_key_exists($actType->name, $workActivity[$work->idResource][$work->idProject])) {
      $workActivity[$work->idResource][$work->idProject][$actType->name] = array();
      $workActivity[$work->idResource][$work->idProject][$actType->name][$act->name] = $work->work + 0;
    }
    else if (!array_key_exists($act->name, $workActivity[$work->idResource][$work->idProject][$actType->name]))
      $workActivity[$work->idResource][$work->idProject][$actType->name][$act->name] = $work->work + 0;
    else
      $workActivity[$work->idResource][$work->idProject][$actType->name][$act->name] += $work->work;
    // #sumWorkAct
    if (!array_key_exists($work->idResource, $sumWorkAct)) {
      $sumWorkAct[$work->idResource] = array();
      $sumWorkAct[$work->idResource][$actType->name] = $work->work + 0;
    }
    else if (!array_key_exists($actType->name, $sumWorkAct[$work->idResource])) {
      $sumWorkAct[$work->idResource][$actType->name] = $work->work + 0;
    }
    else
      $sumWorkAct[$work->idResource][$actType->name] += $work->work;
    // #ActTypes
    if (!array_key_exists($work->idResource, $resActTypes)) {
      $resActTypes[$work->idResource] = array();
      array_push($resActTypes[$work->idResource], $actType->name);
    }
    else if (!in_array($actType->name, $resActTypes[$work->idResource])) {
      array_push($resActTypes[$work->idResource], $actType->name);
    }
    // End #407
    $result[$work->idResource][$work->idProject]+=$work->work;
  }
}

if (checkNoData($result)) if (!empty($cronnedScript)) goto end; else exit;
// title
$newProject=array();
foreach ($projects as $id=>$name) {
  $newProject[SqlList::getFieldFromId('Project', $id, 'sortOrder').'-'.$id]=$name;
}
$projects=$newProject;
ksort($projects);
foreach ($projects as $id=>$name) {
  $idExplo=explode('-',$id);
  $id=$idExplo[1];
  $sumProj[$id]=0;  
}

asort($resources);
foreach ($resources as $idR=>$nameR) {
  if ($paramTeam) {
    $res=new Resource($idR);
  }
  if (!$paramTeam or $res->idTeam==$paramTeam) {
    foreach ($projects as $idP=>$nameP) {
      $idExplo=explode('-',$idP);
      $idP=$idExplo[1];
      if (array_key_exists($idR, $result)) {
        if (array_key_exists($idP, $result[$idR])) {
          $val=$result[$idR][$idP];
          $sumProj[$idP]+=$val;
        }
      }
    }
  }
}
$nbProj=0;
foreach ($projects as $id=>$name) {
  $idExplo=explode('-',$id);
  $id=$idExplo[1];
  if($sumProj[$id] != 0)
    $nbProj+=1;
}
if($nbProj != 0)
$colWidth=round(80/$nbProj);
else
$colWidth=round(80/1);

echo '<table style="width:95%;" align="center">';
echo '<tr>';
echo '<td style="width:10%" class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
if (array_key_exists('idActivityType', $_REQUEST)) {
  echo '<td style="width:10%" class="reportTableHeader" rowspan="2">' . i18n('ActivityType') . '</td>';
}
if (array_key_exists('showDetail', $_REQUEST)) {
  echo '<td style="width:10%" class="reportTableHeader" rowspan="2">' . i18n('Activity') . '</td>';
}
echo '<td style="width:60%" colspan="' . $nbProj . '" class="reportTableHeader">' . i18n('Project') . '</td>';
if ($showDetail)
  echo '<td style="width:5%" class="reportTableHeader" rowspan="2">' . i18n('ActivityTypeSum') . '</td>';
echo '<td style="width:15%" class="reportTableHeader" rowspan="2">' . i18n('sum') . '</td>';
echo '</tr><tr>';

foreach ($projects as $id=>$name) {
  $idExplo=explode('-',$id);
  $id=$idExplo[1];
  if($sumProj[$id] != 0)
  echo '<td style="width:'. (array_key_exists('idActivityType', $_REQUEST) ? ($colWidth * 3 / 4) : $colWidth) .'%" class="reportTableColumnHeader">' . htmlEncode($name) . '</td>';
}
echo '</tr>';
$sum=0;
$sumAct = 0;
$count=0;
$rowspanRes = 0;
$rowspanAct = 0;
foreach ($resources as $idR=>$nameR) {
  if ($paramTeam) {
		$res=new Resource($idR);
  }
  if (!$paramTeam or $res->idTeam==$paramTeam) {
    $sumRes=0;
    $sumAct=0;
    $rowspanRes=0;
    $firstAct=true;
    $showSum=true;
    foreach ($result[$idR] as $id=>$work) {
      $sumRes+=$work;
    }
    if ($showDetail) {
      foreach ($actNames[$idR] as $acts) {
        $rowspanRes += count($acts);
      }
    } else if (array_key_exists('idActivityType', $_REQUEST))
      $rowspanRes = count($resActTypes[$idR]);
    else
      $rowspanRes = 1;
    echo '<tr><td style="width:10%" class="reportTableLineHeader" rowspan =' . $rowspanRes . '>' . htmlEncode($nameR) . '</td>';
    for ($nbActTypes = 0; $nbActTypes < count($resActTypes[$idR]); $nbActTypes++) {
      $rowspanAct = 0;
      if (!$showDetail) {
        $rowspanAct = 1;
        echo '<td style="width:' . ($colWidth / 8) . '%" class="reportTableData" rowspan = ' . $rowspanAct . '>' . $resActTypes[$idR][$nbActTypes] . '</td>';
        foreach ($projects as $idP=>$nameP) {
          $val=0;
          $idExplo=explode('-',$idP);
          $idP=$idExplo[1];
          if($sumProj[$idP] != 0){
            echo '<td style="width:' . (array_key_exists('idActivityType', $_REQUEST) ? ($colWidth / 3) : $colWidth) . '%" class="reportTableData">';
            if (array_key_exists($idR, $workActivity)) {
              if (array_key_exists($idP, $workActivity[$idR])) {
                if (array_key_exists($resActTypes[$idR][$nbActTypes], $workActivity[$idR][$idP])) {
                  foreach($workActivity[$idR][$idP][$resActTypes[$idR][$nbActTypes]] as $act=>$work) {
                    $val+=$work;
                  }
                  echo Work::displayWorkWithUnit($val);
                  $sum+=$val;
                }
              } 
            }
            echo '</td>';
          }
        }
        if ($firstAct) {
          echo '<td style="width:10%" class="reportTableColumnHeader" rowspan =' . $rowspanRes . '>' . Work::displayWorkWithUnit($sumRes) . '</td>';
          $firstAct=false;
        }
        echo '</tr>';
      } else {
        $firstAct=true;
        $rowspanAct = count($actNames[$idR][$resActTypes[$idR][$nbActTypes]]);
        echo '<td style="width:' . ($colWidth / 8) . '%" class="reportTableData" rowspan = ' . $rowspanAct . '>' . $resActTypes[$idR][$nbActTypes] . '</td>';
        foreach ($actNames[$idR][$resActTypes[$idR][$nbActTypes]] as $acts=>$work) {
          echo '<td style="width:' . ($colWidth / 8) . '%" class="reportTableData">' . $acts . '</td>';
          foreach ($projects as $idP=>$nameP) {
            $idExplo=explode('-',$idP);
            $idP=$idExplo[1];
            if($sumProj[$idP] != 0){
              echo '<td style="width:' . (array_key_exists('idActivityType', $_REQUEST) ? ($colWidth / 3) : $colwidth) . '%" class="reportTableData">';
              if (array_key_exists($idR, $workActivity)) {
                if (array_key_exists($idP, $workActivity[$idR])) {
                  if (array_key_exists($resActTypes[$idR][$nbActTypes], $workActivity[$idR][$idP])) {
                    if (array_key_exists($acts, $workActivity[$idR][$idP][$resActTypes[$idR][$nbActTypes]])) {
                      $val=$workActivity[$idR][$idP][$resActTypes[$idR][$nbActTypes]][$acts];
                      echo Work::displayWorkWithUnit($val);
                      $sum+=$val;
                    }
                  }
                } 
              }
              echo '</td>';
            }
          }
          if ($firstAct) {
            echo '<td style="width:5%" class="reportTableData" rowspan = ' . $rowspanAct . '>' .  Work::displayWorkWithUnit($sumWorkAct[$idR][$resActTypes[$idR][$nbActTypes]]) . '</td>';
            $firstAct=false;
          }
          if ($showSum) {
            echo '<td style="width:10%" class="reportTableColumnHeader" rowspan =' . $rowspanRes . '>' . Work::displayWorkWithUnit($sumRes) . '</td>';
            $showSum=false;
          }
          echo '</tr>';
        }
      }
    }
  }
}
echo '<tr><td class="reportTableHeader" colspan="' . ($showDetail ? 3 : 2) . '">' . i18n('sum') . '</td>';
if ($nbProj == 0)
   echo '<td class="reportTableHeader">' . "" . '</td>';

foreach ($projects as $id=>$name) {
  $idExplo=explode('-',$id);
  $id=$idExplo[1];
  if($sumProj[$id] != 0)
  echo '<td class="reportTableColumnHeader">' . Work::displayWorkWithUnit($sumProj[$id]);
  echo '</td>';
}
if ($showDetail)
  echo '<td class="reportTableColumnHeader">' . Work::displayWorkWithUnit($sum);
echo '<td class="reportTableHeader">' . Work::displayWorkWithUnit($sum);
echo'</td></tr>';
echo '</table>';

end:
