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
include("../external/pChart2/class/pData.class.php");
include("../external/pChart2/class/pDraw.class.php");
include("../external/pChart2/class/pImage.class.php");

$idProject="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $idProject=trim($_REQUEST['idProject']);
  $idProject = Security::checkValidId($idProject);
}
$idBaseline="";
if (array_key_exists('idBaselineSelect',$_REQUEST) and trim($_REQUEST['idBaselineSelect'])!="") {
  $idBaseline=trim($_REQUEST['idBaselineSelect']);
  $idBaseline = Security::checkValidId($idBaseline);
}
$scale="";
if (array_key_exists('format',$_REQUEST)) {
	$scale=$_REQUEST['format'];
};
$startDateReport="";
if (array_key_exists('startDate',$_REQUEST)) {
  $startDateReport=$_REQUEST['startDate'];
};
$endDateReport="";
if (array_key_exists('endDate',$_REQUEST)) {
  $endDateReport=$_REQUEST['endDate'];
};
$showToday=false;
if (array_key_exists('showBurndownToday',$_REQUEST)) {
  $showToday=true;
}
$legend='included';
if (array_key_exists('showBurndownLegendOnTop',$_REQUEST)) {
  $legend="top";
}

$headerParameters="";
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
}
if ($idBaseline!="") {
  $headerParameters.= i18n("colIdBaselineSelect") . ' : ' . htmlEncode(SqlList::getNameFromId('BaselineSelect',$idBaseline)) . '<br/>';
}
if ( $scale) {
  $headerParameters.= i18n("colFormat") . ' : ' . i18n($scale) . '<br/>';
}
if ($startDateReport!="") {
  $headerParameters.= i18n("colStartDate") . ' : ' . htmlFormatDate($startDateReport) . '<br/>';
}
if ($endDateReport!="") {
  $headerParameters.= i18n("colEndDate") . ' : ' . htmlFormatDate($endDateReport) . '<br/>';
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
if (!$idBaseline) {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('messageNoData',array(i18n('colIdBaselineSelect'))); // TODO i18n message
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit;
}

$baseline=new Baseline($idBaseline);

// Graph
if (! testGraphEnabled()) { return;}

$user=getSessionUser();
$proj=new Project($idProject);
$baseline=new Baseline($idBaseline);
if ($baseline->idProject!=$idProject) {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('messageNoData',array(i18n('colIdBaselineSelect'))); // TODO i18n message
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit;
}

$start="";
$end="";
// constitute query and execute for planned post $end (last real work day)
$w=new Work();
$wTable=$w->getDatabaseTableName();
$querySelect= "select sum(w.work) as work, w.workDate as day ";
$queryFrom=   " from $wTable w";
$proj=new Project($idProject);
$queryWhere= " where w.idProject in " . transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(false, true));
$queryWhere.= " and w.idProject in ".transformListIntoInClause($user->getVisibleProjects(false));
$queryOrder= "  group by w.workDate";
$query=$querySelect.$queryFrom.$queryWhere.$queryOrder;
$resultReal=Sql::query($query);
$tableReal=array();
//$tableRealSum=array();
//$sumReal=0;
$endACWP="";
$today=date('Y-m-d');
while ($line = Sql::fetchLine($resultReal)) {
  $day=$line['day'];
  $real=Work::displayWork($line['work']);
  $tableReal[$day]=$real;
  //$sumReal+=$real;
  //$tableRealSum[$day]=$sumReal;
  if ( ($start=="" or $start>$day) and $day<=$today) {$start=$day;}
  if ( ($end=="" or $end<$day) and $day<=$today) { $end=$day;}
  if ( $endACWP=="" or $endACWP<$day) {$endACWP=$day;}
}
if (!$end) $end=$today;
if (!$start) $start=$today;
$endReal=$end;

// constitute query and execute for planned post $end (last real work day)
$pw=new PlannedWork();
$pwTable=$pw->getDatabaseTableName();
$querySelect= "select sum(pw.work) as work, pw.workDate as day ";
$queryFrom=   " from $pwTable pw";
$queryWhere=  " where pw.workDate>'$endReal'";
$proj=new Project($idProject);
$queryWhere.= " and pw.idProject in " . transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(false, true));
$queryWhere.= " and pw.idProject in ".transformListIntoInClause($user->getVisibleProjects(false));
$queryOrder= "  group by pw.workDate";
$query=$querySelect.$queryFrom.$queryWhere.$queryOrder;
$resultPlanned=Sql::query($query);
$tablePlanned=array();
//$tablePlannedSum=array();
//$sumPlanned=$sumReal; // We start left sum at real
while ($line = Sql::fetchLine($resultPlanned)) {
  $day=$line['day'];
  $planned=Work::displayWork($line['work']);
  //$sumPlanned+=$planned;
  $tablePlanned[$day]=$planned;
  //$tablePlannedSum[$day]=$sumPlanned;
  if ($start>$day) {$start=$day;}
  if ($end<$day) { $end=$day;}
  if ( $endACWP=="" or $endACWP<$day) {$endACWP=$day;}
}

// constitute query and execute for baseline
$pwb=new PlannedWorkBaseline();
$pwbTable=$pwb->getDatabaseTableName();
$querySelect= "select sum(pwb.work) as work, pwb.workDate as day ";
$queryFrom=   " from $pwbTable pwb";
$queryWhere=  " where pwb.idBaseline=".Sql::fmtId($idBaseline);
$proj=new Project($idProject);
$queryWhere.= " and pwb.idProject in " . transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(false, true));
$queryWhere.= " and pwb.idProject in ".transformListIntoInClause($user->getVisibleProjects(false));
$queryOrder= "  group by pwb.workDate";
$query=$querySelect.$queryFrom.$queryWhere.$queryOrder;
$resultBaseline=Sql::query($query);
$tableBaseline=array();
$endBCWS="";
while ($line = Sql::fetchLine($resultBaseline)) {
  $day=$line['day'];
  $planned=Work::displayWork($line['work']);
  $tableBaseline[$day]=$planned;
  if ($start>$day) {$start=$day;}
  if ($end<$day) { $end=$day;}
  if ( $endBCWS=="" or $endBCWS<$day) {$endBCWS=$day;}
}
ksort($tableBaseline);
if (checkNoData(array_merge($tablePlanned,$tableReal,$tableBaseline))) if (!empty($cronnedScript)) goto end; else exit;

$pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',array('refType'=>'Project', 'refId'=>$idProject));
if (trim($pe->realStartDate) and $pe->realStartDate<$start) $start=$pe->realStartDate;
if (trim($pe->realEndDate) and $pe->realEndDate>$end) $end=$pe->realEndDate;
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
  if ($scale=='week') { 
    $arrDates[$date]=weekFormat($date); 
  } else if ($scale=='month') { 
    $arrDates[$date]=date('Y-m',strtotime($date));  
  } else if ($scale=='quarter') { 
    $year=date('Y',strtotime($date));
    $month=date('m',strtotime($date));
    $quarter=1+intval(($month-1)/3);
    $arrDates[$date]=$year.'-Q'.$quarter;  }
  else { 
    $arrDates[$date]=$date;
  }
  $date=addDaysToDate($date, 1);
}
$resReal=array();
$sumReal=0;
$resPlanned=array();
$sumPlanned=0;
$resBaseline=array();
$sumBaseline=0;
foreach ($arrDates as $date => $period) {
  if (isset($tableReal[$date])) {
    $sumReal+=$tableReal[$date];
  }
  if ($date==$endReal) {
    $sumPlanned=$sumReal;
  }
  if (isset($tablePlanned[$date])) {
    $sumPlanned+=$tablePlanned[$date];
  }
  if (isset($tableReal[$date]) and $date>$today) {
  	$sumPlanned+=$tableReal[$date];
  }
  if (isset($tableBaseline[$date])) {
    $sumBaseline+=$tableBaseline[$date];
  }
  if ($date<$endReal) {
    $resReal[$period]=$sumReal;
    $resPlanned[$period]=VOID;
  } else if ($date==$endReal) {
    $resReal[$period]=$sumReal;
    $resPlanned[$period]=$sumPlanned;
  } else if ($date>$endReal) {
    if (!isset($resReal[$period])) $resReal[$period]=VOID;
    if ($date>$endACWP) {
      if (!isset($resPlanned[$period])) {$resPlanned[$period]=VOID;}
    } else {
      $resPlanned[$period]=$sumPlanned;
    }
  }
  if (($date>$endBCWS)) {
    if (!isset($resBaseline[$period])) {$resBaseline[$period]=VOID;}
  } else {
    $resBaseline[$period]=$sumBaseline;
  }
}
$startDatePeriod=null;
$endDatePeriod=null;
if ($startDateReport and isset($arrDates[$startDateReport])) $startDatePeriod=$arrDates[$startDateReport];
if ($endDateReport and isset($arrDates[$endDateReport])) $endDatePeriod=$arrDates[$endDateReport];

if ($startDatePeriod or $endDatePeriod) {
  foreach ($arrDates as $date => $period) {
    if ( ($startDatePeriod and $period<$startDatePeriod) or ($endDatePeriod and $period>$endDatePeriod) ) {
      unset($arrDates[$date]);
      unset($resReal[$period]);
      unset($resPlanned[$period]);
      unset($resBaseline[$period]);
    }
  }
}

$graphWidth=1200;
$graphHeight=720;
$indexToday=0;

$arrDates=array_flip($arrDates);
$cpt=0;
$modulo=intVal(50*count($arrDates)/$graphWidth);
if ($modulo<0.5) $modulo=0;
foreach ($arrDates as $date => $period) {
  if ($period<$today) $indexToday++;
  if (0 and $cpt % $modulo !=0 ) {
    $arrDates[$date]=VOID;
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
foreach($arrDates as $date){
  $arrLabel[]=$date;
}

$maxPlotted=30; // max number of point to get plotted lines. If over lines are not plotted/

$dataSet = new pData();
// Definition of series
$dataSet->addPoints($resReal,"real");
$dataSet->addPoints($resPlanned,"planned");
$dataSet->addPoints($resBaseline,"baseline");
$dataSet->addPoints($arrLabel,"dates");

$dataSet->setSerieOnAxis("real",0);
$dataSet->setSerieOnAxis("planned",0);
$dataSet->setSerieOnAxis("baseline",0);

$dataSet->setAxisName(0,i18n("colWork"). ' ('.i18n(Work::getWorkUnit()).')');
$dataSet->setAxisUnit(0,' '.Work::displayShortWorkUnit().' ');

$dataSet->setSerieDescription("real",i18n("legendACWP")."  ");
$dataSet->setSerieDescription("planned",i18n("legendACWP").' ('.i18n('planned').')  ');
$dataSet->setSerieDescription("baseline",i18n("legendBCWS")."  ");

$dataSet->setAbscissa("dates");

/* Create the pChart object */
$graph = new pImage($graphWidth,$graphHeight,$dataSet);

/* Draw the background */
$graph->Antialias = FALSE;
$Settings = array("R"=>240, "G"=>240, "B"=>240, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
$graph->drawFilledRectangle(0,0,$graphWidth,$graphHeight,$Settings);

/* Add a border to the picture */
$graph->drawRectangle(0,0,$graphWidth-1,$graphHeight-1,array("R"=>150,"G"=>150,"B"=>150));

/* Set the default font */
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>9,"R"=>100,"G"=>100,"B"=>100));

/* Draw the scale */
$graph->setGraphArea(60,30,$graphWidth-20,$graphHeight-(($scale=='month')?100:75));
$graph->drawFilledRectangle(60,30,$graphWidth-20,$graphHeight-(($scale=='month')?100:75),array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>230));
$formatGrid=array("LabelSkip"=>$modulo, "SkippedAxisAlpha"=>(($modulo>9)?0:20), "SkippedGridTicks"=>0,
    "Mode"=>SCALE_MODE_START0, "GridTicks"=>0, 
    "DrawYLines"=>array(0), "DrawXLines"=>true,"Pos"=>SCALE_POS_LEFTRIGHT, 
    "LabelRotation"=>60, "GridR"=>200,"GridG"=>200,"GridB"=>200);
$graph->drawScale($formatGrid);

$graph->Antialias = TRUE;
$dataSet->setSerieWeight("real",1);
$dataSet->setSerieWeight("planned",1);
$dataSet->setSerieWeight("baseline",1);
$dataSet->setPalette("real",array("R"=>120,"G"=>140,"B"=>250,"Alpha"=>255));
$dataSet->setPalette("planned",array("R"=>180,"G"=>180,"B"=>250,"Alpha"=>50));
$dataSet->setPalette("baseline",array("R"=>250,"G"=>180,"B"=>210,"Alpha"=>255));
$dataSet->setSerieTicks("planned",3);

$dataSet->setSerieDrawable("real",true);
$dataSet->setSerieDrawable("planned",true);
$dataSet->setSerieDrawable("baseline",true);
$graph->drawLineChart();
if (count($arrLabel)<$maxPlotted) {
  $graph->drawPlotChart();
}
if ($showToday) $graph->drawXThreshold(array($indexToday),array("Alpha"=>70,"Ticks"=>0));

if ($legend=="top") {
  $graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>10.8,"R"=>100,"G"=>100,"B"=>100));
  $graph->drawLegend(10,10,array("Mode"=>LEGEND_HORIZONTAL, "Family"=>LEGEND_FAMILY_BOX ,
      "R"=>255,"G"=>255,"B"=>255,"Alpha"=>0,
      "FontR"=>55,"FontG"=>55,"FontB"=>55,
      "Margin"=>0));
  $graph->drawText($graphWidth/2,50,i18n("reportSCurveChart"),array("FontSize"=>14,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
} else {
  $graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>11,"R"=>100,"G"=>100,"B"=>100));
  $graph->drawLegend(100,50,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
      "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
      "FontR"=>55,"FontG"=>55,"FontB"=>55,
      "Margin"=>5));
  $graph->drawText($graphWidth/2,20,i18n("reportSCurveChart"),array("FontSize"=>14,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
}

$workGap=($sumBaseline-$sumPlanned)/Work::getWorkCoef();
if ($workGap>0) {
  $format=array("R"=>50, "G"=>100, "B"=>50, "Align"=>TEXT_ALIGN_BOTTOMRIGHT, "FontSize"=>'12');
  $title=i18n('legendWorkPositive');
} else {
  $format=array("R"=>100, "G"=>50, "B"=>50, "Align"=>TEXT_ALIGN_BOTTOMRIGHT, "FontSize"=>'12');
  $title=i18n('legendWorkNegative');
}
$title.=" : ".str_replace('&nbsp;',' ',Work::displayWorkWithUnit(abs($workGap)));
$graph->drawText($graphWidth-40,$graphHeight-120,$title,$format);

if ($endACWP>$endBCWS) {
  $delayGap=dayDiffDates($endBCWS, $endACWP);
  $delayGapOpen=intval(workDayDiffDates($endBCWS, $endACWP))-1;
  $format=array("R"=>100, "G"=>50, "B"=>50, "Align"=>TEXT_ALIGN_BOTTOMRIGHT, "FontSize"=>'12');
  $title=i18n('legendDelayNegative');
} else {
  $delayGap=dayDiffDates($endACWP, $endBCWS);
  $delayGapOpen=workDayDiffDates($endACWP, $endBCWS)-1;
  $format=array("R"=>50, "G"=>100, "B"=>50, "Align"=>TEXT_ALIGN_BOTTOMRIGHT, "FontSize"=>'12');
  $title=i18n('legendDelayPositive');
}
$title.=" : ".$delayGap.' '.i18n('days');
$graph->drawText($graphWidth-40,$graphHeight-100,$title,$format);
$title='('.$delayGapOpen.' '.i18n('openDays').')';
$graph->drawText($graphWidth-40,$graphHeight-80,$title,$format);

/* Render the picture (choose the best way) */
$imgName=getGraphImgName("scurvechart");
$graph->Render($imgName);

echo '<table width="95%" align="center"><tr><td align="center">';
echo '<img style="width:1000px;height:600px" src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';

end:

?>