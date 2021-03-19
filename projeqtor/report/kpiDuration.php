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

$kpiColorFull=true; // decide how kpi color is displayed correspondng on threshold : false will display rounded badge, true will fill the cell 
$displayAsPct=false;

$idProject="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $idProject=trim($_REQUEST['idProject']);
  $idProject = Security::checkValidId($idProject);
}
$idOrganization="";
if (array_key_exists('idOrganization',$_REQUEST) and trim($_REQUEST['idOrganization'])!="") {
  $idOrganization=trim($_REQUEST['idOrganization']);
  $idOrganization = Security::checkValidId($idOrganization);
}
$idProjectType="";
if (array_key_exists('idProjectType',$_REQUEST) and trim($_REQUEST['idProjectType'])!="") {
  $idProjectType=trim($_REQUEST['idProjectType']);
  $idProjectType = Security::checkValidId($idProjectType);
}
$year="";
if (array_key_exists('yearSpinner',$_REQUEST)) {
  $year=trim($_REQUEST['yearSpinner']);
  $year = Security::checkValidYear($year);
}
$month="";
if (array_key_exists('monthSpinner',$_REQUEST)) {
  $month=trim($_REQUEST['monthSpinner']);
  $month = Security::checkValidMonth($month);
  if ($month and !$year) $year=date('Y');
}

$done=false;
if (array_key_exists('onlyFinished',$_REQUEST)) {
  $done=true;
}
$showThreshold=false;
if (array_key_exists('showThreshold',$_REQUEST)) {
  $showThreshold=true;
}
$scale='month';
if (array_key_exists('format',$_REQUEST)) {
  $scale=$_REQUEST['format'];
}

$headerParameters="";
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ($idProjectType!="") {
  $headerParameters.= i18n("colIdProjectType") . ' : ' . htmlEncode(SqlList::getNameFromId('ProjectType',$idProjectType)) . '<br/>';
}
if ($year!="") {
  $headerParameters.= i18n("year") . ' : ' . htmlFormatDate($year) . '<br/>';
}
if ($month!="") {
  $headerParameters.= i18n("month") . ' : ' . htmlFormatDate($month) . '<br/>';
}
if ($done) {
  $headerParameters.= i18n("colOnlyFinished").'<br/>';
}

include "header.php";

if ($month and $month<10) $month='0'.intval($month);

$scope=$_REQUEST['scope'];
if ($scope=='Project') {
  if (!$idProject) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('Project'))); 
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
} else if ($scope=='Organization') {
  $projectVisibility=securityGetAccessRight('menuProject','read');
  if ($projectVisibility=='ALL' and !$idProjectType and !$idOrganization) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('colIdOrganization').' / '.i18n('colIdProjectType'))); 
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
  if (!$year) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('year')));
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
} else {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('error : scope is not defined'); 
  echo '</div>';
}

$kpi=new KpiDefinition(1);

$user=getSessionUser();
$listProjects=array();
$visibleProjects=$user->getVisibleProjects(false);
if ($idProject) {
  $listProjects[$idProject]=new Project($idProject);
} else {
  $where='1=1';
  if ($idOrganization) {
    $where.=" and idOrganization='$idOrganization'";
  }
  if ($idProjectType) {
    $where.=" and idProjectType='$idProjectType'";
  }
  if (! $idOrganization and !$idProjectType) {
    $where.='and id in '.transformListIntoInClause($visibleProjects);
  } 
  if ($month) {
    $start=$year.'-'.$month.'-01';
    $end=$year.'-'.$month.'-'.date('t',strtotime($year.'-'.$month.'-01'));;
  } else if ($year) {
    $start=$year.'-01-01';
    $end=$year.'-12-31';
  }
  $prj=new Project();
  $pjTable=$prj->getDatabaseTableName();
  $kh=new KpiHistory();
  $khTable=$kh->getDatabaseTableName();
  $kv=new KpiValue();
  $kvTable=$kv->getDatabaseTableName();
  $pe=new PlanningElement();
  $peTable=$pe->getDatabaseTableName();
  if (isset($start) and isset($end)) {
    $where.=" and ( ";
    $where.="    exists (select 'x' from $khTable kh where kh.idKpiDefinition=$kpi->id and kh.refType='Project' and kh.refId=$pjTable.id and kh.kpiDate>='$start' and kh.kpiDate<='$end' ) ";
    $where.=" or exists (select 'x' from $kvTable kv where kv.idKpiDefinition=$kpi->id and kv.refType='Project' and kv.refId=$pjTable.id and kv.kpiDate>='$start' and kv.kpiDate<='$end' ) ";
    $where.=" or exists (select 'x' from $peTable pe where pe.refType='Project' and pe.refId=$pjTable.id and pe.realEndDate>='$start' and pe.realEndDate<='$end' ) ";
    $where.=" ) ";
  }
  $listProjects=$prj->getSqlElementsFromCriteria(null,false,$where);
}

//if (checkNoData($listProjects)) if (!empty($cronnedScript)) goto end; else exit;

$period=null;
$periodValue='';
if ($month) {
  $period='month';
  if ($month<10) $month='0'.intval($month);
  $periodValue=$year.$month;
} else if ($year) {
  $period='year';
  $periodValue=$year;
}

$KpiTreshold = new KpiThreshold();
$thresholds= $KpiTreshold->getSqlElementsFromCriteria(array('idKpiDefinition'=>$kpi->id),false,null,'thresholdValue desc');
echo '<table width="90%" align="center">';
echo '<tr>';
echo '<td class="reportTableHeader" style="width:20%">' . i18n('Project') . '</td>';
echo '<td class="reportTableHeader" style="width:20%">' . i18n('Client') . '</td>';
echo '<td class="reportTableHeader" style="width:10%">' . i18n('colValidatedStartDate') . '</td>';
echo '<td class="reportTableHeader" style="width:10%">' . i18n('colValidatedEndDate') . '</td>';
echo '<td class="reportTableHeader" style="width:10%">' . i18n('colPlannedEndDate') . '</td>';
echo '<td class="reportTableHeader" style="width:10%">' . i18n('colRealEndDate') . '</td>';
echo '<td class="reportTableHeader" style="width:20%">' . htmlEncode($kpi->name) . '</td>';
echo '</tr>';
$arrayProj=array();
$cptProjectsDisplayed=0;
$sumValues=0;
$sumWeight=0;
foreach($listProjects as $prj) {
  $arrayProj[$prj->id]=$prj->name;
  if (! array_key_exists($prj->id, $visibleProjects)) continue; // Will avoid to display projects not visible to user
  if ($done and ! $prj->ProjectPlanningElement->realEndDate) continue;
  if ($done and isset($start) and isset($end)) {
    if ($prj->ProjectPlanningElement->realEndDate<$start or $prj->ProjectPlanningElement->realEndDate>$end) {continue;}
  }
  $cptProjectsDisplayed++;
  echo '<tr>';
  echo '<td class="reportTableDataSpanned" style="width:20%;text-align:left">' . htmlEncode($prj->name) . '</td>';
  echo '<td class="reportTableDataSpanned" style="width:20%;text-align:left">' . htmlEncode(SqlList::getNameFromId('Client', $prj->idClient)) . '</td>';
  echo '<td class="reportTableDataSpanned" style="width:10%">' . htmlFormatDate($prj->ProjectPlanningElement->validatedStartDate) . '</td>';
  echo '<td class="reportTableDataSpanned" style="width:10%">' . htmlFormatDate($prj->ProjectPlanningElement->validatedEndDate) . '</td>';
  echo '<td class="reportTableDataSpanned" style="width:10%">' . htmlFormatDate($prj->ProjectPlanningElement->plannedEndDate) . '</td>';
  echo '<td class="reportTableDataSpanned" style="width:10%">' . htmlFormatDate($prj->ProjectPlanningElement->realEndDate) . '</td>';
  $critKpi=array('idKpiDefinition'=>$kpi->id,'refType'=>'Project','refId'=>$prj->id);
  if (! $period) { // Added if (1) as value displayed in table is always the actual value, 
    $kpiValue=SqlElement::getSingleSqlElementFromCriteria('KpiValue', $critKpi);
  } else {
    $critKpi[$period]=$periodValue;
    $newKpiHistory = new KpiHistory();
    $lstKpi= $newKpiHistory->getSqlElementsFromCriteria($critKpi,false,null,'kpiValue desc');
    if (count($lstKpi)==0) {
    	$where="idKpiDefinition=$kpi->id and refType='Project' and refId=$prj->id and $period<='$periodValue'";
    	$newKpiHist = new KpiHistory();
    	$lstKpi= $newKpiHist->getSqlElementsFromCriteria(null,false,$where,'kpiDate desc');
    }
    $kpiValue=reset($lstKpi);
  }
  if (!$kpiValue) $kpiValue=new KpiValue();
  $color='#ffffff';
  foreach ($thresholds as $th) {
    if ($kpiValue->kpiValue>$th->thresholdValue) {
      $color=$th->thresholdColor;
      break;
    }
  }
  $dispValue=$kpiValue->kpiValue;
  if ($dispValue and $displayAsPct) $dispValue=htmlDisplayPct($dispValue*100);
  if ($kpiColorFull) {
    echo '<td class="reportTableData" style="width:20%;background-color:'.$color.';text-align:center;">' . (($dispValue)?htmlDisplayColoredFull($dispValue, $color):'') . '</td>';
  } else {
    echo '<td class="reportTableDataSpanned" style="width:20%;text-align:left">' . (($dispValue)?htmlDisplayColored($dispValue, $color):'') . '</td>';
  }
  if ($kpiValue->kpiValue) {
    $sumValues+=$kpiValue->kpiValue*$kpiValue->weight;
    $sumWeight+=1;
  }
  echo '</tr>';
}
if ($cptProjectsDisplayed>0 and $scope=='Organization') {
  echo '<tr>';
  $name=$kpi->name;
  if ($idOrganization) {
    $org=new Organization($idOrganization,true);
    $name.=' - '.$org->name;
  }
  if ($idProjectType) {
    $typ=new ProjectType($idProjectType,true);
    $name.=' - '.$typ->name;
  }
  if ($done) {
    $name.=' ('.i18n("colOnlyFinished").')';
  }
  echo '<td class="reportTableHeader" style="width:80%;text-align:left" colspan="6">' . $name . '</td>';
  $consolidated=($sumWeight)?round($sumValues/$sumWeight,2):null;
  $color='#ffffff';
  foreach ($thresholds as $th) {
    if ($consolidated>$th->thresholdValue) {
      $color=$th->thresholdColor;
      break;
    }
  }
  if ($consolidated and $displayAsPct) $consolidated=htmlDisplayPct($consolidated*100);
  if ($kpiColorFull) {
    echo '<td class="reportTableData" style="width:20%;background-color:'.$color.';text-align:center;">' . (($consolidated)?htmlDisplayColoredFull($consolidated, $color):'') . '</td>';
  } else {
    echo '<td class="reportTableDataSpanned" style="width:20%;font-weight:bold;text-align:left">' . (($consolidated)?htmlDisplayColored($consolidated, $color):'') . '</td>';
  }
  echo '</tr>'; 
} 
echo '</table>';

// Graph
if (! testGraphEnabled()) { return;}

// constitute query and execute for planned post $end (last real work day)
$h=new KpiHistory();
$hTable=$h->getDatabaseTableName();
$query = "select AVG(prj.valueP) as value, prj.periodP as period";
$query.= " from (select MAX(h.kpiValue) as valueP, h.$scale as periodP, h.refId as idP";
$query.= " from $hTable h";
$query.= " where h.idKpiDefinition=$kpi->id and h.refType='Project' and h.refId in " . transformListIntoInClause($arrayProj);
if ($done) {$query.= " and h.refDone=1";}
//if (! $idProject) {
	if ($year) {
		if ($month) {
			$query.= " and h.month='$year$month'";
		} else if ($year==date('Y') and date('m')==1) {
	    $query.= " and (h.year='$year' or h.year='".($year-1)."')";
	  } else {
	    $query.= " and h.year='$year'";
	  }
	}
//}
$query.= " group by h.$scale, h.refId) prj ";
$query.= " group by periodP";
$result=Sql::query($query);


$end='';
$start='';
$valArray=array();
foreach ($result as $line) {
  if ($end=='' or $line['period']>$end) $end=$line['period'];
  if ($start=='' or $line['period']<$start) $start=$line['period'];
  $arrValues[$line['period']]=round($line['value'],2)*(($displayAsPct)?100:1);;
}

//if ($cptProjectsDisplayed==0 and (!$start or !$end)) {
if ($cptProjectsDisplayed==0 or (!$start or !$end)) {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n('reportNoData'); 
  echo '</div>';
  return;
}
$lastValue=VOID;
$arrDates=array();
$date=$start;
while ($date<=$end) {
  if (isset($arrValues[$date])) {
    $lastValue=$arrValues[$date];
  } else {
    if ($done) {
      $arrValues[$date]=VOID;
    } else {
      $arrValues[$date]=$lastValue;
    }
  }
  $arrDates[$date]=$date;
  if ($scale=='day') {
    $d=substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
    $d=addDaysToDate($d, 1);
    $date=str_replace('-','',$d);
  } else if ($scale=='week') { 
    $w=substr($date,4,2);
    $y=substr($date,0,4);
    $w++;
    $lastDayOfYear=$y.'-12-31';
    if ($w>weekNumber($lastDayOfYear)) {
      $y++;
      $w='1';
    }
    if ($w<10) $w='0'.intval($w);
    $date=$y.$w; 
  } else if ($scale=='month') { 
    $m=substr($date,4,2);
    $y=substr($date,0,4);
    $m++;
    if ($m>12) {
      $y++;
      $m=1;
    }
    if ($m<10) $m='0'.intval($m);
    $date=$y.$m;
  } else if ($scale=='year') { 
    $date++;  
  }
}
ksort($arrValues);

$graphWidth=700;
$graphHeight=400;
$indexToday=0;
$maxPlotted=50; // max number of point to get plotted lines. If over lines are not plotted/

$cpt=0;
$modulo=intVal(50*count($arrDates)/$graphWidth);
if ($modulo<0.5) $modulo=0;
foreach ($arrDates as $id=>$date) {
  if ($modulo!=0 and $cpt % $modulo !=0 ) {
    $arrDates[$id]=VOID;
  } else {
    if ($scale=='day') {
      $arrDates[$id]=htmlFormatDate(substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2)); 
    } else if ($scale=='week') {
      $arrDates[$id]=htmlFormatDate(substr($date,0,4).'-'.substr($date,4,2));
    } else if ($scale=='month') {
      $arrDates[$id]=getMonthName(substr($date,4),'auto').'-'.substr($date,2,2);
    } else if ($scale=='year') {
      $arrDates[$id]=$date;
    }
  }
  $cpt++;
}

$dataSet = new pData();
// Definition of series
$dataSet->addPoints($arrValues,"kpi");
$dataSet->addPoints($arrDates,"dates");
$dataSet->setSerieOnAxis("kpi",0);
$dataSet->setAbscissa("dates");

/* Create the pChart object */
$graph = new pImage($graphWidth,$graphHeight,$dataSet);

/* Draw the background */
$graph->Antialias = FALSE;
$Settings = array("R"=>255, "G"=>255, "B"=>255, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
$graph->drawFilledRectangle(0,0,$graphWidth,$graphHeight,$Settings);

/* Add a border to the picture */
$graph->drawRectangle(0,0,$graphWidth-1,$graphHeight-1,array("R"=>150,"G"=>150,"B"=>150));

/* Set the default font */
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>9,"R"=>50,"G"=>50,"B"=>50));

/* Draw the scale */
$dataSet->setAxisUnit(0,($displayAsPct)?"%  ":"  ");
$graph->setGraphArea(55,70,$graphWidth-20,$graphHeight-60);
$graph->drawFilledRectangle(55,70,$graphWidth-20,$graphHeight-60,array("R"=>230,"G"=>230,"B"=>230));

$graph->Antialias = TRUE;
if ($showThreshold) {
  $graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>9,"R"=>50,"G"=>50,"B"=>50));
  $cpt=0;
  foreach ($thresholds as $th) {
    if ($th->thresholdValue==0) continue;
    $cpt++;
    $arrTh=array();
    foreach ($arrDates as $dt) {$arrTh[]=$th->thresholdValue*(($displayAsPct)?100:1);}
    $dataSet->addPoints($arrTh,"th".$cpt);
    $format=array_merge(hex2rgb($th->thresholdColor),array("Alpha"=>255));
    $dataSet->setPalette("th".$cpt,$format);
    $dataSet->setSerieDrawable("th".$cpt,true);
    $dataSet->setSerieWeight("th".$cpt,1);
    //$dataSet->setSerieOnAxis("th"+$cpt,0);
  }
}
$formatGrid=array("LabelSkip"=>$modulo, "SkippedAxisAlpha"=>(($modulo>9)?0:20), "SkippedGridTicks"=>0,
    "Mode"=>SCALE_MODE_FLOATING , "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>false,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>150,"GridG"=>150,"GridB"=>150);
$graph->drawScale($formatGrid);
$dataSet->setSerieDrawable("kpi",false);
$graph->drawLineChart();

$dataSet->setSerieWeight("kpi",1);
$dataSet->setPalette("kpi",array("R"=>120,"G"=>140,"B"=>250,"Alpha"=>255));
$dataSet->setSerieDrawable("kpi",true);
$graph->drawLineChart();
$cpt=0;
foreach ($thresholds as $th) {
  $cpt++;
  $dataSet->setSerieDrawable("th".$cpt,false);
}
$graph->drawLineChart();
if (count($arrDates)<$maxPlotted) {
  $graph->drawPlotChart();
}
//if ($showToday) $graph->drawXThreshold(array($indexToday),array("Alpha"=>70,"Ticks"=>0));
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>12,"R"=>50,"G"=>50,"B"=>50));
$name=i18n('kpiProgress').' '.$kpi->name;
if ($idProject) {
  $prj=new Project($idProject,true);
  $name.=' - '.$prj->name;
} 
if ($idOrganization) {
  $org=new Organization($idOrganization,true);
  $name.=' - '.$org->name;
}
if ($idProjectType) {
  $typ=new ProjectType($idProjectType,true);
  $name.=' - '.$typ->name;
}
$name=wordwrap($name,50); 
$graph->drawText($graphWidth/2,35,$name,array("FontSize"=>18,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE));

/* Render the picture (choose the best way) */
$imgName=getGraphImgName("kpidurationchart");
$graph->Render($imgName);
echo '<br/><br/><br/>';
echo '<table width="95%" align="center"><tr><td align="center">';
echo '<img style="width:700px;height:400px" src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';

end:

?>