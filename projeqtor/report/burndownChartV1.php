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
include_once '../tool/formatter.php';

$idProject="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $idProject=trim($_REQUEST['idProject']);
  $idProject = Security::checkValidId($idProject);
}
$scale="";
if (array_key_exists('format',$_REQUEST)) {
	$scale=$_REQUEST['format'];
};
$showCompleted=false;
if (array_key_exists('showBurndownActivities',$_REQUEST)) {
  $showCompleted=true;
}
$showToday=false;
if (array_key_exists('showBurndownToday',$_REQUEST)) {
  $showToday=true;
}

$headerParameters="";
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
}
if ( $scale) {
  $headerParameters.= i18n("colFormat") . ' : ' . i18n($scale) . '<br/>';
}
if ($showCompleted) {
  $headerParameters.= i18n("colShowBurndownActivities"). '<br/>';
}
if ($showToday) {
  $headerParameters.= i18n("colShowBurndownToday"). '<br/>';
}

include "header.php";

if (!$idProject) {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('messageNoData',array(i18n('Project'))); // TODO i18n message
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit; 
}
// Graph
if (! testGraphEnabled()) { return;}

$user=getSessionUser();
$proj=new Project($idProject);

// constitute query and execute for left work (history)
$ph=new ProjectHistory();
$phTable=$ph->getDatabaseTableName();
$querySelect= "select leftWork as leftwork, realWork as realwork, day "; 
$queryFrom=   " from $phTable ph";
$queryWhere=  " where ph.idProject=".Sql::fmtId($idProject);
$queryWhere.= " and ph.idProject in ".transformListIntoInClause($user->getVisibleProjects(false));
$queryOrder= "  order by day asc";
$query=$querySelect.$queryFrom.$queryWhere.$queryOrder;
$result=Sql::query($query);
$tabLeft=array();
$resLeft=array();
$start="";
$end="";
$hasReal=false;
$lastLeft=0;
while ($line = Sql::fetchLine($result)) {
  $day=substr($line['day'],0,4).'-'.substr($line['day'],4,2).'-'.substr($line['day'],6);
  $left=$line['leftwork'];
  $tabLeft[$day]=$left;
  $lastLeft=$left;
  if ($start=="" or $start>$day) {$start=$day;}
  if ($end=="" or $end<$day) { $end=$day;}
  if ($day>date('Y-m-d')) break;
}
$endReal=$end;

// constitute query and execute for planned post $end (last real work day)
$pw=new PlannedWork();
$pwTable=$pw->getDatabaseTableName();
$querySelect= "select sum(pw.work) as work, pw.workDate as day ";
$queryFrom=   " from $pwTable pw";
$queryWhere=  " where pw.workDate>'$end'";
$proj=new Project($idProject);
$queryWhere.= " and pw.idProject in " . transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(false, true));
$queryWhere.= " and pw.idProject in ".transformListIntoInClause($user->getVisibleProjects(false));
$queryOrder= "  group by pw.workDate";
$query=$querySelect.$queryFrom.$queryWhere.$queryOrder;
$resultPlanned=Sql::query($query);
$tabLeftPlanned=array();
$resLeftPlanned=array();
$resBest=array();
$tabLeftPlanned[$end]=$lastLeft;
$newLastLeft=$lastLeft;
while ($line = Sql::fetchLine($resultPlanned)) {
  $day=$line['day'];
  $planned=$line['work'];
  $newLastLeft-=$planned;
  if ($newLastLeft<0) $newLastLeft=0;
  $tabLeftPlanned[$day]=$newLastLeft;
  if ($start=="" or $start>$day) {$start=$day;}
  if ($end=="" or $end<$day) { $end=$day;}
  if ($newLastLeft==0) break;
}

// constitute query and execute for completed tasks
$pe=new PlanningElement();
$peTable=$pe->getDatabaseTableName();
$querySelect= "select plannedEndDate as plannedend, realEndDate as realend ";
$queryFrom=   " from $peTable pe";
$queryWhere=  " where pe.idProject in " . transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(false, true));
$queryWhere.= " and pe.idProject in ".transformListIntoInClause($user->getVisibleProjects(false));
$queryWhere.= "  and pe.elementary=1";
$queryOrder= "  order by COALESCE(pe.realEndDate, pe.plannedEndDate)";
$query=$querySelect.$queryFrom.$queryWhere.$queryOrder;
$tabCompletedTasks=array();
$tabCompletedTasksPlanned=array();
$resCompletedTasks=array();
$resCompletedTasksPlanned=array();
$resLeftTasks=array();
$resLeftTasksPlanned=array();
$nbTasks=0;
if ($showCompleted) {
  $resultTasks=Sql::query($query);
  while ($line = Sql::fetchLine($resultTasks)) {
    if ($line['realend']) {
      $day=$line['realend'];
      if (isset($tabCompletedTasks[$day])){ $tabCompletedTasks[$day]++;}
      else {$tabCompletedTasks[$day]=1;}
      $nbTasks++;
    } else if ($line['plannedend']){
      $day=$line['plannedend'];
      if (isset($tabCompletedTasksPlanned[$day])) {$tabCompletedTasksPlanned[$day]++;}
      else {$tabCompletedTasksPlanned[$day]=1;}
      $nbTasks++;
    } else {
      // No real, no planned => not taken into account
    }
  }
}

if (checkNoData(array_merge($tabLeft,$tabLeftPlanned))) if (!empty($cronnedScript)) goto end; else exit;

$pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',array('refType'=>'Project', 'refId'=>$idProject));
if (trim($pe->realStartDate)) $start=$pe->realStartDate;
if (trim($pe->realEndDate)) $end=$pe->realEndDate;
if (trim($pe->validatedEndDate) and $pe->validatedEndDate>$end) $end=$pe->validatedEndDate;
$arrDates=array();
$date=$start;
if (!$start or !$end) {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('reportNoData'); 
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit;
}
while ($date<=$end) {
  if ($scale=='week') { $arrDates[$date]=date('Y-W',strtotime($date)); } 
  else if ($scale=='month') { $arrDates[$date]=date('Y-m',strtotime($date));  } 
  else if ($scale=='quarter') { 
    $year=date('Y',strtotime($date));
    $month=date('m',strtotime($date));
    $quarter=1+intval(($month-1)/3);
    $arrDates[$date]=$year.'-Q'.$quarter;  }
  else { $arrDates[$date]=$date;}
  $date=addDaysToDate($date, 1);
}

$old=null;
$old=reset($tabLeft);
$oldPlanned=$lastLeft;
$nbSteps=0;
$leftTasks=$nbTasks;
$completedFound=0;
$plannedCompletedNotDone=0;
foreach ($arrDates as $date => $period) {
  if ($date>$endReal) {
    if (!isset($resLeft[$period])) $resLeft[$period]="";
  } else if (isset($tabLeft[$date])) {
    $resLeft[$period]=Work::displayWork($tabLeft[$date]);
    $old=$tabLeft[$date];
  } else {
    $resLeft[$period]=($old===null)?'':Work::displayWork($old);
  }
  if (isset($tabLeftPlanned[$date])) {
    $resLeftPlanned[$period]=Work::displayWork($tabLeftPlanned[$date]);
    $oldPlanned=$tabLeftPlanned[$date];
  } else {
    if ($date>=$endReal) {
      $resLeftPlanned[$period]=Work::displayWork($oldPlanned);
    } else  {
      $resLeftPlanned[$period]="";
    }
  }
  if ($showCompleted) {
    if (isset($tabCompletedTasks[$date])) {
      if (isset($resCompletedTasks[$period])) { $resCompletedTasks[$period]+=$tabCompletedTasks[$date];}
      else {$resCompletedTasks[$period]=$tabCompletedTasks[$date];}
      $leftTasks-=$tabCompletedTasks[$date];
    } else if (! isset($resCompletedTasks[$period]) ){
      $resCompletedTasks[$period]="";
    }  
    if (isset($tabCompletedTasksPlanned[$date])) {
      if (isset($resCompletedTasksPlanned[$period])) { $resCompletedTasksPlanned[$period]+=$tabCompletedTasksPlanned[$date];}
      else {$resCompletedTasksPlanned[$period]=$tabCompletedTasksPlanned[$date];}
      if (count($tabCompletedTasks)>0) {
        $plannedCompletedNotDone+=$tabCompletedTasksPlanned[$date];
      } else {
        $leftTasks-=$tabCompletedTasksPlanned[$date];
      }
    } else if (! isset($resCompletedTasksPlanned[$period]) ){
      $resCompletedTasksPlanned[$period]="";
    }  
    if (count($tabCompletedTasks)>0) { 
      $resLeftTasks[$period]=$leftTasks; 
      $resLeftTasksPlanned[$period]="";
    } else {
      if (! isset($resLeftTasks[$period])) {
        $resLeftTasks[$period]="";
      }
      $resLeftTasksPlanned[$period]=$leftTasks;
    }
    if (isset($tabCompletedTasks[$date])) {
      unset($tabCompletedTasks[$date]);
      if (count($tabCompletedTasks)==0) {
        $resLeftTasks[$period]=$leftTasks;
        $leftTasks-=$plannedCompletedNotDone;
        $resLeftTasksPlanned[$period]=$leftTasks;
      }
    }
    
  }
  if ($date<=$pe->validatedEndDate) $nbSteps++;
}

$startLabel=reset($arrDates);
$maxLeft=Work::displayWork($pe->validatedWork);
if (!$maxLeft and isset($resLeft[$startLabel])) $maxLeft=$resLeft[$startLabel];
if (!$maxLeft and isset($resLeftPlanned[$startLabel])) $maxLeft=$resLeftPlanned[$startLabel];
$minLeft=0;
//$nbSteps=count($arrDates)-1;
if (!$nbSteps) $nbSteps=count($arrDates);
$stepValue=($nbSteps)?(($maxLeft-$minLeft)/($nbSteps-1)):0;
$val=$maxLeft;

$graphWidth=1000;
$graphHeight=500;
$indexToday=0;
$today=null;
foreach ($arrDates as $date => $period) {
  if ($date==date('Y-m-d')) {$today=$period;}
  if ($val!=="" or ! isset($resBest[$period])) $resBest[$period]=$val;
  if ($val) {
    $val-=$stepValue;
    if ($val<0.01) $val=0;
  } else {
    $val="";
  }
}
$arrDates=array_flip($arrDates);
$cpt=0;
$modulo=intVal(50*count($arrDates)/$graphWidth);
if ($modulo<1) $modulo=1;
foreach ($arrDates as $date => $period) {
  if ($date<$today) $indexToday++;
  if ($cpt % $modulo !=0 ) {
    $arrDates[$date]="";
  } else {
    if ($scale=='day') {
      $arrDates[$date]=htmlFormatDate($date);
    } else if ($scale=='month') {
      $arrDates[$date]=getMonthName(substr($date,5)).' '.substr($date,0,4);
    } else {
      $arrDates[$date]=$date;
    }
  }
  $cpt++;
}
$arrLabel=array();
$arrVoidLabel=array();
foreach($arrDates as $date){
  $arrLabel[]=$date;
  $arrVoidLabel[]="";
}

$resLeftTasksScale=$resLeftTasks;
if ($nbTasks<150) {
  $maxVal=$nbTasks;
  if ($nbTasks<20) {
    if (intval($nbTasks/2)!=($nbTasks/2)) $maxVal+=1;
  } else {
    if (intval($nbTasks/20)!=($nbTasks/20)) $maxVal=intval(($nbTasks+20)/20)*20;
    else $maxVal=intval(($nbTasks)/20)*20;
  }
  array_splice($resLeftTasksScale, count($resLeftTasksScale)-1);
  $resLeftTasksScale[]=$maxVal;
}

$maxPlotted=30; // max number of point to get plotted lines. If over lines are not plotted/
include("../external/pChart/pData.class");  
include("../external/pChart/pChart.class");  
$dataSet=new pData;
$graph = new pChart($graphWidth,$graphHeight);
$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",9);
$graph->drawFilledRoundedRectangle(5,5,$graphWidth-5,$graphHeight-5,10,240,240,240);
$graph->drawRoundedRectangle(5,5,$graphWidth-5,$graphHeight-5,10,200,200,200);
$graph->setGraphArea(70,20,$graphWidth-50,$graphHeight-(($scale=='month')?90:70));
$graph->drawGraphArea(252,252,252);

// Definition of series
$dataSet->AddPoint($resBest,"best");
$dataSet->AddPoint($resLeft,"left");
$dataSet->AddPoint($resLeftPlanned,"leftPlanned");
if ($showCompleted){
  $dataSet->AddPoint($resLeftTasks,"leftTasks");
  $dataSet->AddPoint($resLeftTasksPlanned,"leftTasksPlanned");
  $dataSet->AddPoint($resCompletedTasks,"completedTasks");
  $dataSet->AddPoint($resCompletedTasksPlanned,"completedTasksPlanned");
}
$dataSet->AddPoint($arrLabel,"dates");
$dataSet->AddPoint($arrVoidLabel,"empty");
$dataSet->AddPoint($resLeftTasksScale,"taskScale");
$dataSet->SetSerieName(i18n("legendBestBurndown"),"best");
$dataSet->SetSerieName(i18n("legendRemainingEffort"),"left");
$dataSet->SetSerieName(i18n("legendRemainingEffort").' ('.i18n('planned').')',"leftPlanned");
if ($showCompleted){
  $dataSet->SetSerieName(i18n('legendRemainingTasks'),'leftTasks');
  $dataSet->SetSerieName(i18n('legendRemainingTasks').' ('.i18n('planned').')','leftTasksPlanned');
  $dataSet->SetSerieName(i18n('legendCompletedTasks'),'completedTasks');
  $dataSet->SetSerieName(i18n('legendCompletedTasks').' ('.i18n('planned').')','completedTasksPlanned');
}
$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",9);
$graph->setColorPalette(0,250,180,210);
$graph->setColorPalette(1,120,140,250);
$graph->setColorPalette(2,180,180,250);
if ($showCompleted){
  $graph->setColorPalette(3,50,150,50);
  $graph->setColorPalette(4,100,200,100);
  $graph->setColorPalette(5,200,200,100);
  $graph->setColorPalette(6,240,240,150);
}

// Draw grid and scales
$dataSet->AddSerie("best");
$dataSet->AddSerie("left");
if ($showCompleted){
  $dataSet->AddSerie('leftTasks');
}
$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",9);
$dataSet->SetYAxisName(i18n("legendRemainingEffort"). ' ('.i18n(Work::getWorkUnit()).')');
$dataSet->SetYAxisUnit(' '.Work::displayShortWorkUnit());
$dataSet->SetAbsciseLabelSerie("empty");
$graph->drawScale($dataSet->GetData(),$dataSet->GetDataDescription(),SCALE_START0,150,150,150,true,0,0, true);
$graph->drawGrid(2,TRUE,230,230,230,200);
$graph->setLineStyle(1,0);
$dataSet->RemoveSerie('best');
$dataSet->RemoveSerie('left');
if ($showCompleted){
  $dataSet->RemoveSerie('leftTasks');
}
$graph->setLineStyle(1,5);
$graph->clearScale();
$dataSet->SetYAxisUnit("");
if ($showToday and $indexToday and count($arrLabel)) {
  $grW=$graphWidth-50-70;
  $intervals=count($arrLabel);
  $posLine=70+$grW/$intervals*($indexToday+0.5);
  $graph->setLineStyle(1,2);
  $graph->drawLine($posLine,20,$posLine,$graphHeight-(($scale=='month')?90:70),255,0,0);
}
// Draw complete tasks line
if ($showCompleted){
  $graph->setLineStyle(1,0);
  $dataSet->AddSerie('leftTasks');
  $dataSet->AddSerie('leftTasksPlanned');
  $dataSet->AddSerie('taskScale');
  $graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",9);
  $dataSet->SetYAxisName(i18n("legendNumberOfTasks"));
  $dataSet->SetAbsciseLabelSerie("dates");
  $graph->drawRightScale($dataSet->GetData(),$dataSet->GetDataDescription(), SCALE_START0 ,150,150,150,true,45,0, true);
  $dataSet->RemoveSerie('taskScale');
  $dataSet->RemoveSerie('leftTasksPlanned');
  $dataSet->RemoveSerie('leftTasks');
  $dataSet->AddSerie('completedTasks');
  $dataSet->AddSerie('completedTasksPlanned');
  //$graph->drawBarGraph($dataSet->GetData(),$dataSet->GetDataDescription(),false);
  $graph->drawStackedBarGraph($dataSet->GetData(),$dataSet->GetDataDescription(),70);
  $dataSet->RemoveSerie('completedTasks');
  $dataSet->RemoveSerie('completedTasksPlanned');
  $dataSet->AddSerie('leftTasks');
  $graph->drawLineGraph($dataSet->GetData(),$dataSet->GetDataDescription(),TRUE);
  if (count($resLeft)<$maxPlotted) {
    $graph->drawPlotGraph($dataSet->GetData(),$dataSet->GetDataDescription(),3,2,255,255,255);
  }
  $dataSet->RemoveSerie('leftTasks');
  $dataSet->AddSerie('leftTasksPlanned');
  $graph->setLineStyle(1,3);
  $graph->drawLineGraph($dataSet->GetData(),$dataSet->GetDataDescription(),TRUE);
  $dataSet->RemoveSerie('leftTasks');
  $dataSet->RemoveSerie('leftTasksPlanned');
}
$graph->setLineStyle(1,0);
$graph->clearScale();

// Draw "left" lines
$graph->setLineStyle(1,0);
$dataSet->SetAbsciseLabelSerie("dates");
$dataSet->AddSerie("best");
$dataSet->AddSerie("left");
$graph->drawScale($dataSet->GetData(),$dataSet->GetDataDescription(), SCALE_START0 ,150,150,150,false,0,0, true);
$dataSet->RemoveSerie('left');
$graph->drawLineGraph($dataSet->GetData(),$dataSet->GetDataDescription());
$dataSet->RemoveSerie('best');
$dataSet->AddSerie("left");
$graph->drawLineGraph($dataSet->GetData(),$dataSet->GetDataDescription());
if (count($resLeft)<$maxPlotted) {
  $graph->drawPlotGraph($dataSet->GetData(),$dataSet->GetDataDescription(),3,2,255,255,255);
}
$dataSet->RemoveSerie('left');
$graph->setLineStyle(1,3);
$dataSet->AddSerie("leftPlanned");
$graph->drawLineGraph($dataSet->GetData(),$dataSet->GetDataDescription());


$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",9);
$graph->drawLegend($graphWidth-255,30,$dataSet->GetDataDescription(),240,240,240,-1,-1,-1,100,100,100,true);
$graph->clearScale();

$imgName=getGraphImgName("burndownChart");
$graph->Render($imgName);
echo '<table width="95%" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';

end:

?>