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
$scale="";
if (array_key_exists('format',$_REQUEST)) {
	$scale=$_REQUEST['format'];
};
$type="";
if (array_key_exists('idMilestoneType',$_REQUEST)) {
  $type=trim($_REQUEST['idMilestoneType']);
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

//gautier ticket #2579
$showIdle=false;
if (array_key_exists('showIdle',$_REQUEST)) {
  $showIdle=true;
}

$headerParameters="";
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
}
if ($type!="") {
  $headerParameters.= i18n("colIdMilestoneType") . ' : ' . htmlEncode(SqlList::getNameFromId('Type',$type)) . '<br/>';
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
if($showIdle) {
  $headerParameters.= i18n("labelShowIdle"). '<br/>';
}
include "header.php";

if (!$idProject) {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('messageNoData',array(i18n('Project'))); 
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit; 
}

// Graph
if (! testGraphEnabled()) { return;}

$user=getSessionUser();
$proj=new Project($idProject,true);
$today=date('Y-m-d');
$refTime=' 00:00:00';

$start="";
$end="";
// constitute query and execute for planned post $end (last real work day)
// Milestones
$m=new Milestone();
$mpe=new MilestonePlanningElement();
$mTable=$m->getDatabaseTableName();
$mpeTable=$mpe->getDatabaseTableName();

$querySelect= "select mpe.id as idpe, m.name as name, m.id as id, m.idMilestoneType as type, mpe.realEndDate as realend, mpe.plannedEndDate as plannedend ";
$queryFrom=   " from $mTable m, $mpeTable as mpe";
$queryWhere=  " where mpe.refType='Milestone' and mpe.refId=m.id";
$queryWhere.= " and m.idProject in " . transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(false, true));
$queryWhere.= " and m.idProject in ".transformListIntoInClause($user->getVisibleProjects(false));
if ($type) {
  $queryWhere.= " and m.idMilestoneType = ".Sql::fmtId($type);
}
if ($showIdle) {
  $queryWhere.= " and mpe.idle in (0,1) ";
}else{
  $queryWhere.= " and mpe.idle = 0";
}
$query=$querySelect.$queryFrom.$queryWhere;
$resultMile=Sql::query($query);
$arrayMile=array();
$arrayMileID=array();
$existingDates=array();
$listMilePE='(0';
$listMile='(0';
while ($line = Sql::fetchLine($resultMile)) {
  $id=$line['id'];
  $idpe=$line['idpe'];
  $name=$line['name'];
  $tp=$line['type'];
  $listMilePE.=','.$idpe;
  $listMile.=','.$id;
  $arrayMileID[$id]=$idpe;
  $arrayMile[$idpe]=array('id'=>$id, 'idpe'=>$idpe, 'name'=>$name,'type'=>$tp,'dates'=>array(), 'periods'=>array(), 'current'=>VOID, 'lastDate'=>null);
  $mpeEndDate=null;
  if ($line['realend']) {
    $mpeEndDate=$line['realend'];
  } else {
    $mpeEndDate=$line['plannedend'];
  }
  $arrayMile[$idpe]['dates'][$mpeEndDate]=strtotime($mpeEndDate.$refTime);
  $arrayMile[$idpe]['lastDate']=$mpeEndDate;
  $arrayMile[$idpe]['real']=($line['realend'])?true:false;
  $existingDates[$mpeEndDate]=$mpeEndDate;
  if ($end=="" or $end<$mpeEndDate) { $end=$mpeEndDate;}
}
$listMilePE.=')';
$listMile.=')';
$h=new History();
$hTable=$h->getDatabaseTableName();
$ha=new HistoryArchive();
$haTable=$ha->getDatabaseTableName();
$querySelect= "select h.refId as idpe, h.oldValue as old, h.newValue as new, h.operationDate as date, h.refType as reftype";
$queryFrom=   " from $hTable h";
$queryWhere=  " where ( (h.refType='MilestonePlanningElement' and h.refId in $listMilePE) " ;
$queryWhere.= "      or (h.refType='Milestone' and h.refId in $listMile ) )" ;
$queryWhere.= " and colName='plannedEndDate' ";
$queryOrder= "  order by date";

if (!$showIdle) {
  $query=$querySelect.$queryFrom.$queryWhere.$queryOrder;
} else {
  $query=$querySelect.$queryFrom.$queryWhere;
  $query.=' UNION '.$querySelect.str_replace($hTable,$haTable,$queryFrom).$queryWhere.$queryOrder;
}

$resultPlanned=Sql::query($query);
$tablePlanned=array();
//$existingDates=array();
while ($line = Sql::fetchLine($resultPlanned)) {
  $day=substr($line['date'],0,10);
  if (!$day or $day=='' or $day<'2000-01-01' ) continue;
  $existingDates[$day]=$day;
  $type=$line['reftype'];
  $idpe=($type=='Milestone')?$arrayMileID[$line['idpe']]:$line['idpe'];
  $old=$line['old'];
  $new=$line['new'];
  if (!$arrayMile[$idpe]['real'] or $day<$arrayMile[$idpe]['lastDate']) { // #2590
    $arrayMile[$idpe]['dates'][$day]=strtotime($new.$refTime);
    if ($day>$arrayMile[$idpe]['lastDate']) { $arrayMile[$idpe]['lastDate']=$day;}
    if ($start=="" or $start>$day) {$start=$day;}
    if ($end=="" or $end<$day) { $end=$day;}
    if ($end<$new) {$end=$new;}
  }
}
if (checkNoData($arrayMile)) if (!empty($cronnedScript)) goto end; else exit;
if (!$start or !$end) {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('reportNoData'); 
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit;
}
//$start=substr($start,0,8).'01';
//$end=substr($end,0,8).date('t',strtotime($end));
$date=$start;
$arrDates=array();
ksort($existingDates);
$arrayShow=array();
while ($date<=$end) {
  $show=false;
  if (isset($existingDates[$date]) or $date==$today or $date==$end or $date==$start) {
    $show=true;
  }
  if ($scale=='day') { 
    $arrDates[$date]=strtotime($date.$refTime);
    if ($show) $arrayShow[$date.$refTime]=true;
  } else {
    $dt=new DateTime();
    $dt->setTimestamp(strtotime($date.$refTime));
    $last=lastDayOf($scale,$dt);
    $arrDates[$date]=$last->getTimestamp(); 
    if ($show) $arrayShow[$last->getTimestamp()]=true;
  }
  $date=addDaysToDate($date, 1);
}
$resBase=array();
$startDatePeriod=null;
$endDatePeriod=null;
if ($startDateReport and isset($arrDates[$startDateReport])) $startDatePeriod=$arrDates[$startDateReport];
if ($endDateReport and isset($arrDates[$endDateReport])) $endDatePeriod=$arrDates[$endDateReport];

foreach ($arrDates as $date => $period) {
  if ( ($startDateReport and $date<$startDateReport) or ($endDateReport and $date>$endDateReport) ) {
    unset ($arrDates[$date]);
    continue;
  } 
  $resBase[$period]=$period;
  foreach($arrayMile as $idx=>$arr) {
    if (isset($arrayMile[$idx]['dates'][$date])) {
      $arrayMile[$idx]['current']=$arrayMile[$idx]['dates'][$date];
    }
    if ($arrayMile[$idx]['current']!=VOID or ! isset($arrayMile[$idx]['periods'][$period])) {
      $arrayMile[$idx]['periods'][$period]=$arrayMile[$idx]['current'];
    }
    if ($arrayMile[$idx]['lastDate']==$date) {
      $arrayMile[$idx]['current']=VOID;
    }
  }
}

if (count($arrDates)<2) {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('reportNoData');
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit;
}

$graphWidth=1250;
$graphHeight=720;
$indexToday=0;

$arrDates=array_flip($arrDates);
$cpt=0;
$modulo=intVal(50*count($arrDates)/$graphWidth);
if ($modulo<0.5) $modulo=0;
foreach ($arrDates as $date => $period) {
  if ($period<$today) $indexToday++;
  if ($scale=='day') {
    //$arrDates[$date]=htmlFormatDate($date); 
  } else {
    $arrDates[$date]=$date;
  }
  $cpt++;
}

$arrLabel=array();
foreach($arrDates as $date){
  $arrLabel[]=$date;
}

$maxPlotted=30; // max number of point to get plotted lines. If over lines are not plotted/

$dataSet = new pData();

// KROWRY
foreach ($arrayMile as $idx=>$arr){
  $allVoid=true;
  foreach($arrayMile[$idx]['periods'] as $idp){
    if($idp!=VOID){
	    $allVoid=false;
	    break;
    }
  }
  if ($allVoid==true){
    unset($arrayMile[$idx]);
  }
}

// Definition of series
foreach($arrayMile as $idx=>$arr) {
    $dataSet->addPoints($arrayMile[$idx]['periods'],"mile$idx");
    $dataSet->setSerieOnAxis("mile$idx",0);
    $dataSet->setSerieWeight("mile$idx",1);
    $dataSet->setSerieDescription("mile$idx",wordwrap($arrayMile[$idx]['name']."\n",25,"\n"));
}
$dataSet->addPoints($resBase,"base");
$dataSet->setSerieOnAxis("base",0);
$dataSet->setSerieWeight("base",1);
$dataSet->setPalette("base",array("R"=>250,"G"=>180,"B"=>210,"Alpha"=>255));
$dataSet->setSerieDescription("base",i18n("legendBaseline")."  ");

foreach ($arrLabel as $idx=>$val) {
  if ($scale=='day') {
    $arrLabel[$idx]=strtotime($val.$refTime);
  }
  if ($scale=='month') {
    //$arrLabel[$idx]=strtotime($val.$refTime);
  }
}

$dataSet->addPoints($arrLabel,"dates");
$dataSet->setAxisDisplay(0,AXIS_FORMAT_DATE);
$dataSet->setAbscissa("dates");
if ($scale=='day') {
  $dataSet->setXAxisDisplay(AXIS_FORMAT_DATE);
} else if ($scale=='week') {
  $dataSet->setXAxisDisplay(AXIS_FORMAT_DATE,'Y-W');
} else if ($scale=='month' or $scale=='quarter') {
    $dataSet->setXAxisDisplay(AXIS_FORMAT_DATE,'F Y');  
}

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
$graph->setGraphArea(90,30,$graphWidth-220,$graphHeight-(($scale=='month' or $scale=='quarter')?100:75));
$graph->drawFilledRectangle(90,30,$graphWidth-220,$graphHeight-(($scale=='month' or $scale=='quarter')?100:75),array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>230));
$formatGrid=array("LabelSkip"=>$modulo, "SkippedAxisAlpha"=>(($modulo>9)?0:20), "SkippedGridTicks"=>0,
    "Mode"=>SCALE_MODE_FLOATING, "GridTicks"=>0, 
    "DrawYLines"=>array(0), "DrawXLines"=>true,"Pos"=>SCALE_POS_LEFTRIGHT, 
    "LabelRotation"=>60, "GridR"=>200,"GridG"=>200,"GridB"=>200,
    "ScaleModeAuto"=>TRUE
    );
$graph->drawScale($formatGrid);

$graph->Antialias = TRUE;

$dataSet->setSerieDrawable("base",true);

$drawFormat=array("ScaleModeAuto"=>TRUE);
$graph->drawLineChart($drawFormat);
$dataSet->setSerieDrawable("base",false);
if (count($arrLabel)<$maxPlotted) {
  $graph->drawPlotChart($drawFormat);
}
if ($showToday) { 
  $min=reset($arrLabel);
  $max=$arrLabel[count($arrLabel)-1];
  $td=strtotime($today.$refTime);
  if ($min!=$max) {
    $pos=($td-$min)/($max-$min)*(count($arrLabel)-1);
    $graph->drawXThreshold(array($pos),array("Alpha"=>70,"Ticks"=>0));
  }
}
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>10,"R"=>100,"G"=>100,"B"=>100));
$graph->drawLegend($graphWidth-210,17,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
    "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
    "FontR"=>55,"FontG"=>55,"FontB"=>55,
    "Margin"=>5));
$graph->drawText($graphWidth/2,20,i18n("report45DegreeChart"),array("FontSize"=>14,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

/* Render the picture (choose the best way) */
$imgName=getGraphImgName("fortyfivedegreechart");
$graph->Render($imgName);

echo '<table width="95%" align="center"><tr><td align="center">';
echo '<img style="width:1000px;height:600px" src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';

// ===============================================================================================================
/*
function firstDayOf($period, DateTime $date = null)
{
  $period = strtolower($period);
  $validPeriods = array('year', 'quarter', 'month', 'week');

  if ( ! in_array($period, $validPeriods))
    throw new InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));

  $newDate = ($date === null) ? new DateTime() : clone $date;

  switch ($period) {
  	case 'year':
  	  $newDate->modify('first day of january ' . $newDate->format('Y'));
  	  break;
  	case 'quarter':
  	  $month = $newDate->format('n') ;

  	  if ($month < 4) {
  	    $newDate->modify('first day of january ' . $newDate->format('Y'));
  	  } elseif ($month > 3 && $month < 7) {
  	    $newDate->modify('first day of april ' . $newDate->format('Y'));
  	  } elseif ($month > 6 && $month < 10) {
  	    $newDate->modify('first day of july ' . $newDate->format('Y'));
  	  } elseif ($month > 9) {
  	    $newDate->modify('first day of october ' . $newDate->format('Y'));
  	  }
  	  break;
  	case 'month':
  	  $newDate->modify('first day of this month');
  	  break;
  	case 'week':
  	  $newDate->modify(($newDate->format('w') === '0') ? 'monday last week' : 'monday this week');
  	  break;
  }

  return $newDate;
}*/
function lastDayOf($period, DateTime $date = null)
{
  $period = strtolower($period);
  $validPeriods = array('year', 'quarter', 'month', 'week');

  if ( ! in_array($period, $validPeriods))
    throw new InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));

  $newDate = ($date === null) ? new DateTime() : clone $date;

  switch ($period)
  {
  	case 'year':
  	  $newDate->modify('last day of december ' . $newDate->format('Y'));
  	  break;
  	case 'quarter':
  	  $month = $newDate->format('n') ;

  	  if ($month < 4) {
  	    $newDate->modify('last day of march ' . $newDate->format('Y'));
  	  } elseif ($month > 3 && $month < 7) {
  	    $newDate->modify('last day of june ' . $newDate->format('Y'));
  	  } elseif ($month > 6 && $month < 10) {
  	    $newDate->modify('last day of september ' . $newDate->format('Y'));
  	  } elseif ($month > 9) {
  	    $newDate->modify('last day of december ' . $newDate->format('Y'));
  	  }
  	  break;
  	case 'month':
  	  $newDate->modify('last day of this month');
  	  break;
  	case 'week':
  	  $newDate->modify(($newDate->format('w') === '0') ? 'now' : 'sunday this week');
  	  break;
  }

  return $newDate;
}

end:

?>