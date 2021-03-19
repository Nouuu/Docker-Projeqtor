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
include_once "../tool/jsonFunctions.php";

include("../external/pChart2/class/pData.class.php");
include("../external/pChart2/class/pDraw.class.php");
include("../external/pChart2/class/pImage.class.php");

$idOrganization='';
if (array_key_exists('idOrganization',$_REQUEST) and trim($_REQUEST['idOrganization'])!="") {
    $idOrganization=trim($_REQUEST['idOrganization']);
    $idOrganization = Security::checkValidId($idOrganization);
}
$year='';
if (array_key_exists('yearSpinner',$_REQUEST)) {
    $year=$_REQUEST['yearSpinner'];
    $year=Security::checkValidYear($year);
};
$month='';
if (array_key_exists('monthSpinner',$_REQUEST)) {
    $month=$_REQUEST['monthSpinner'];
    $month=Security::checkValidMonth($month);
} else {
    $month="01";
}
$where = "1=1";
$headerParameters = "";
if ($idOrganization!="") {
    $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
    $orgChoosed = new Organization($idOrganization);
    $listOrgChecked = array();
    $listOrgToCheck = array();
    $listOrgTemp = array();
    $listOrgChecked[] =  $orgChoosed->id;
    $listOrgToCheck = $orgChoosed->getSqlElementsFromCriteria(null, false, "idOrganization ='".$orgChoosed->id."'"); // This will return an array of the organizations having $orgChoosed as the parent.
    $isEmpty = false;
    while(!$isEmpty) {
        foreach ($listOrgToCheck as $key => $orga ) {
            if(isset($orga->id)) {
                $listOrgTemp = $orga->getSqlElementsFromCriteria(null, false, "idOrganization='".$orga->id."'");
                $listOrgChecked[] = $orga->id;
            } else {
                $newOrgToCheck = new Organization($orga);
                $listOrgTemp = $newOrgToCheck->getSqlElementsFromCriteria(null, false, "idOrganization='".$orga."'");
                $listOrgChecked[] = $orga;
            }
            array_splice($listOrgToCheck, 0, 1);  // We delete the element which was checked from the list of organization to check. 
            foreach($listOrgTemp as $subOrga) {
                $listOrgToCheck[] = $subOrga->id;
            }
            
        }
        if(empty($listOrgToCheck)) { // Check if $listOrgToCheck is empty. If not, it means we have to loop again and search if there is any organization having one of the organizations in the array as the parent
            $isEmpty = true;
        }
    }
    
    $in='(0';
    foreach ($listOrgChecked as $key => $orga){
        $in.=','.$orga;
    }
    $in.=')';
    $where.=' and idOrganization in '.$in;
}
if ($year!="") {
    $headerParameters.= i18n("year") . ' : ' . htmlFormatDate($year) . '<br/>';
}
if ($month!="") {
    $headerParameters.= i18n("month") . ' : ' . htmlFormatDate($month) . '<br/>';
}
$isEmployee = RequestHandler::getBoolean('isEmployee');
if($isEmployee == true){
    $where.=" and isEmployee = 1";
} else {
    $where.='';
}
$profile = RequestHandler::getValue('idProfile');
if ($profile) {
    $headerParameters.= i18n("colIdProfile") . ' : ';
    $cpt=0;
    $in='(0';
    foreach($profile as $prof) {
        if ($cpt>0) $headerParameters.=', ';
        $headerParameters.=htmlEncode(SqlList::getNameFromId('Profile', $prof));
        $in.=','.$prof;
        $cpt++;
    }
    $in.=')';
    $where.=' and idProfile in '.$in;
    $headerParameters.='<br/>';
}

// if (!$idOrganization) {
//     echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
//     echo i18n('messageNoData',array(i18n('colIdOrganization')));
//     echo '</div>';
//     if (!empty($cronnedScript)) goto end; else exit;
// }
if (!$year) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('year')));
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
}
if (!$month) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('month')));
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
}
include "header.php";

$where.=" and isResource = 1";

if ($month and $month<10) $month='0'.intval($month);
if ($month=="01") {
    $endMonth = "12";
} else {
    $endMonth = intval($month) - 1;
    if ($endMonth<10) $endMonth='0'.intval($endMonth);
}

$workload = array();
//we put the result of the query in workload for each month
for ($i=1; $i<13; $i++) {
    if ($month and $month<10) $month='0'.intval($month);
    $lastDay=lastDayOfMonth(intval($month),intval($year));
    $whereCompleted = $where." and startDate<='" . $year . "-" . $month . "-".$lastDay."'";
    $whereCompleted .= " and (endDate>= '" . $year . "-" . $month . "-".$lastDay."' or endDate is null)";
    if ($month == '13'){
        $month = '01';
        $year += 1;
    }
    $resource = new Resource();
    $workload[$i] = $resource->countSqlElementsFromCriteria(null, $whereCompleted);
    $month = intval($month);
    $month ++;
}
echo '<table width="95%" align="center">';
echo '<tr><td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
if ($month=="13") $month = "01"; //we do this because in the for, $month is incremented once too much. We reset his value
if ($month=="01") {
    echo '<td colspan="12" class="reportTableHeader">' . $year . '</td>';
} else {
    echo '<td colspan="' . (13 - $month) . '" class="reportTableHeader">' . ($year - 1)  . '</td>';
    echo '<td colspan="' . ($month - 1) . '" class="reportTableHeader">' . $year . '</td>';
}

echo '</tr><tr>';
$arrMonth=getArrayMonth(4,true);

for ($i = 0; $i < $month -1 ; $i++) {
    $val = array_shift($arrMonth);
    array_push($arrMonth, $val);
}

for ($i=1; $i<=12; $i++) {
    echo '<td class="reportTableColumnHeader">' . $arrMonth[$i-1] . '</td>';
}
echo '</tr>';

$tab=$workload;
$caption=i18n('workforce');
$serie="workload";
echo '<tr><td class="reportTableLineHeader" style="width:18%">' . $caption . '</td>';
foreach ($tab as $id=>$val) {
    echo '<td style="width:6%;" class="reportTableData">';
    echo $val;
    echo '</td>';
}
echo '</tr>';
echo '</table>';

//====BEGIN OF THE GRAPH====
$workload[13]=VOID;

if (! testGraphEnabled()) { return;}

$dataSet=new pData;
$dataSet->addPoints($workload,"workload");
$dataSet->setSerieDescription("workload",i18n("workforce"));
$dataSet->setSerieOnAxis("workload",0);
$serieSettings = array("R"=>100,"G"=>100,"B"=>200,"Alpha"=>80);
$dataSet->setPalette("workload",$serieSettings);

$dataSet->addPoints($arrMonth,"month");
$dataSet->setAbscissa("month");

// Initialise the graph
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
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8));

/* title */
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8,"R"=>100,"G"=>100,"B"=>100));
$graph->drawLegend($width+18,17,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
    "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
    "FontR"=>55,"FontG"=>55,"FontB"=>55,
    "Margin"=>5));

/* Draw the scale */
$graph->setGraphArea(60,20,$width-20,$height-40);
$formatGrid=array("Mode"=>SCALE_MODE_START0, "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>true,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>200,"GridG"=>200,"GridB"=>200);
//$graph->drawScale($formatGrid);
$graph->Antialias = TRUE;

$graph->drawScale($formatGrid);

$dataSet->setSerieDrawable("workload",true);
$graph->drawAreaChart(array("DisplayColor"=>DISPLAY_AUTO));
$graph->drawPlotChart();
$dataSet->setSerieDrawable("workload",false);

$graph->drawBarChart();

$imgName=getGraphImgName("reportResourceWorkload");
$graph->render($imgName);
echo '<table width="95%" style="margin-top:20px;" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />';
echo '</td></tr></table>';

end:
