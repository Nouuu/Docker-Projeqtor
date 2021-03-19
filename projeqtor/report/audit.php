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

AuditSummary::updateAuditSummary(date('Ymd'));
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
//$paramWeek='';
//if (array_key_exists('weekSpinner',$_REQUEST)) {
//  $paramWeek=$_REQUEST['weekSpinner'];
//};

$user=getSessionUser();

$periodType='month';
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
//if ( $periodType=='week') {
//  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
//}

include "header.php";

$crit="auditDay like '" . $periodValue . "%'";

$as=new AuditSummary();
$result=$as->getSqlElementsFromCriteria(null, false, $crit);

if (!$paramYear or !$paramMonth) {
  $result=array();
}
if (checkNoData($result)) if (!empty($cronnedScript)) goto end; else exit;

if ($paramMonth) $monthDays = date('t',mktime(0, 0, 0, $paramMonth, 1, $paramYear));
else  $monthDays=365;
$days=array();
$nb=array();
$min=array();
$max=array();
$mean=array();
for ($i=1;$i<=$monthDays;$i++) {
  $nb[$i]=0;
  $days[$i]=$i;
  $min[$i]=0;
  $max[$i]=0;
  $mean[$i]=0;
}
//$day=array();
foreach ($result as $as) {
	$d=intval(substr($as->auditDay,6));
	$nb[$d]=$as->numberSessions;
	$mean[$d]=formatDateRpt($as->meanDuration);
	$min[$d]=formatDateRpt($as->minDuration);
	$max[$d]=formatDateRpt($as->maxDuration);	  
}

// Graph
if (! testGraphEnabled()) { echo "pChart not enabled. See log file."; return;}
  include("../external/pChart2/class/pData.class.php");
  include("../external/pChart2/class/pDraw.class.php");
  include("../external/pChart2/class/pImage.class.php");  

// Graph 1 : connections per day
$dataSet = new pData;  
$dataSet->addPoints($nb,'Serie1');
$dataSet->setSerieDescription("Connexions","Serie1");
$dataSet->setSerieOnAxis('Serie1',0);
$dataSet->addPoints($days,'Serie2');
$dataSet->setAbscissa("Serie2");
 
$width=1000;
$legendWidth=100;
$height=400;
$legendHeight=100;
$graph = new pImage($width+$legendWidth, $height,$dataSet);

/* Draw the background */
$graph->Antialias = FALSE;

/* Add a border to the picture */
$settings = array("R"=>240, "G"=>240, "B"=>240, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
$graph->drawRoundedRectangle(5,5,$width+$legendWidth-8,$height-5,5,$settings);
$graph->drawRectangle(0,0,$width+$legendWidth-1,$height-1,array("R"=>150,"G"=>150,"B"=>150));

/* Set the default font */
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>10));

/* title */
$graph->drawText(500,22,i18n('connectionsNumberPerDay'),array("FontSize"=>10,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8,"R"=>100,"G"=>100,"B"=>100));

/* Draw the scale */
$graph->setGraphArea(60,50,$width-20,$height-$legendHeight);
$formatGrid=array("Mode"=>SCALE_MODE_ADDALL_START0, "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>false,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>200,"GridG"=>200,"GridB"=>200);
$graph->drawScale($formatGrid);
$graph->Antialias = TRUE;
$graph->drawLineChart();
$graph->drawPlotChart();

$imgName=getGraphImgName("auditNb");
$graph->render($imgName);
echo '<table width="95%" style="margin-top:35px" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />';
echo '</td></tr></table>';
echo '<br/>';

// Graph 2: connection duration per day
$dataSet2 = new pData;  
$dataSet2->addPoints($max,'max');
$dataSet2->setSerieDescription(i18n("max"),"max");
$dataSet2->setSerieOnAxis('max',0);
$dataSet2->addPoints($mean,'mean');
$dataSet2->setSerieDescription(i18n("mean"),"mean");
$dataSet2->setSerieOnAxis('mean',0);
$dataSet2->addPoints($min,'min');
$dataSet2->setSerieDescription(i18n("min"),"min");
$dataSet2->setSerieOnAxis('min',0);
$dataSet2->setAxisName(0, "");
$dataSet2->setAxisDisplay(0,AXIS_FORMAT_TIME);
$dataSet2->addPoints($days,'SerieX');
$dataSet2->setAbscissa("SerieX");

// Initialise the graph  
$width=1000;
$legendWidth=100;
$height=400;
$legendHeight=100;
$graph2 = new pImage($width+$legendWidth, $height,$dataSet2);
/* Draw the background */
$graph2->Antialias = FALSE;

/* Add a border to the picture */
$settings = array("R"=>240, "G"=>240, "B"=>240, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
$graph2->drawRoundedRectangle(5,5,$width+$legendWidth-8,$height-5,5,$settings);
$graph2->drawRectangle(0,0,$width+$legendWidth-1,$height-1,array("R"=>150,"G"=>150,"B"=>150));

/* Set the default font */
$graph2->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>10));

/* title */
// A FAIRE 
$graph2->drawText(500,22,i18n('connectionsDurationPerDay'),array("FontSize"=>10,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
//////////
$graph2->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8,"R"=>100,"G"=>100,"B"=>100));
$graph2->drawLegend($width+30,17,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
    "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
    "FontR"=>55,"FontG"=>55,"FontB"=>55,
    "Margin"=>5));

/* Draw the scale */
$graph2->setGraphArea(70,50,$width-20,$height-$legendHeight);
$formatGrid=array("Mode"=>SCALE_MODE_ADDALL_START0, "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>false,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>200,"GridG"=>200,"GridB"=>200);
$graph2->drawScale($formatGrid);
$graph2->Antialias = TRUE;
$graph2->drawLineChart();
$graph2->drawPlotChart();

$imgName=getGraphImgName("auditNb");
$graph2->render($imgName);

echo '<table width="95%" style="margin-top:20px;" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';

function formatDateRpt($dateRpt) {
	$baseDay=date('Y-m-d');
	if ($dateRpt>'24:00:00') {
		$split=explode(':',$dateRpt);
		$hours=$split[0];
		while ($hours>=24) {
			$hours-=24;
			$baseDay=addDaysToDate($baseDay, 1);
		}
		$dateRpt=$hours.':00:00';
	}
	
  return strtotime($baseDay.' '.$dateRpt)-strtotime(date('Y-m-d 00:00:00'));
}  

end:

?>