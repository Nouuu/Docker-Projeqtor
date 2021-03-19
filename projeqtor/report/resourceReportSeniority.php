<?php
/** COPYRIGHT NOTICE ********************************************************
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
 ** DO NOT REMOVE THIS NOTICE ***********************************************/

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

if (array_key_exists ( 'nbOfMonths', $_REQUEST )) {
    $paramNbOfMonths = trim ( $_REQUEST ['nbOfMonths'] );
} else {
    $paramNbOfMonths = 12;
}
if ($paramNbOfMonths!="") {
    $headerParameters.= i18n("numberOfMonths") . ' : ' . htmlEncode($paramNbOfMonths) . '<br/>';
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
if (!$paramNbOfMonths || $paramNbOfMonths<=0) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('numberOfMonths')));
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

$seniority=array();
$tabRes = array();
$monthOfTheFor = $month;
$yearOfTheFor = $year;
$resource = new Resource();
$whereResource=$where." and startDate is not null and (endDate>='$yearOfTheFor-$monthOfTheFor-31' or endDate is null)";
$lstResource = $resource->getSqlElementsFromCriteria(null, false, $whereResource);
//We do this because we need the value of $month and $year whitout modification after for the creation the array
for ($i=1; $i<=$paramNbOfMonths; $i++) {
    $nbRes = 0;
    if ($monthOfTheFor and $monthOfTheFor<10) $monthOfTheFor='0'.intval($monthOfTheFor);
    if ($monthOfTheFor == '13'){
        $monthOfTheFor = '01';
        $yearOfTheFor += 1;
    }
    $nbMois = 0;
    foreach ($lstResource as $res){
        $start = $res->startDate;
        $end=$res->endDate;
        if (substr($start,0,7)>"$yearOfTheFor-$monthOfTheFor") continue;
        if ($end and substr($end,0,7)<="$yearOfTheFor-$monthOfTheFor") continue;
        $startDate = new DateTime($start);
        $lastDayOfTheFor=lastDayOfMonth(intval($monthOfTheFor),intval($yearOfTheFor));
        $dateOfTheFor=new DateTime("$yearOfTheFor-$monthOfTheFor-$lastDayOfTheFor");
        $interval=$startDate->diff($dateOfTheFor);
        $nbMoisRes=$interval->m+12*$interval->y;
        $nbMois+=$nbMoisRes;
//         $startFormat = $startDate->format("Y-m-d");
//         $today = new DateTime();
//         $todayFormat = $today->format("Y-m-d");
//         $interval = $today->diff($startDate);
//         $intervalDay = $interval->days;
//         $intervalYear = $intervalDay % 365.25;
//         $intervalMonth = $intervalYear % 12;
//         if ($intervalMonth >= 15){
//             $diff = $interval->y * 12 + $interval->m + 1;
//         } else {
//             $diff = $interval->y * 12 + $interval->m;
//         }
//         $nbMois += round($diff/12, 2);
        $nbRes ++;
    }
    $seniority[$i] = ($nbRes)?round($nbMois/$nbRes,1):0;
    $tabRes[$i] = $nbRes;
    $monthOfTheFor = intval($monthOfTheFor);
    $monthOfTheFor ++;
}
echo '<table width="95%" align="center">';
echo '<tr><td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';

if ($month=="13") $month = "01"; //we do this because in the for, $month is incremented once too much. We reset his value

if ($paramNbOfMonths <= 12){
    if ($month=="01") {
        echo '<td colspan="'.$paramNbOfMonths.'" class="reportTableHeader">' . $year . '</td>';
    } else {
        $colspan = 12 + 1 - $month;
        if ((13 - $month) >= $paramNbOfMonths){
            echo '<td colspan="' . $colspan . '" class="reportTableHeader">' . $year . '</td>';
        } else {
            echo '<td colspan="' . $colspan . '" class="reportTableHeader">' . $year . '</td>';
            echo '<td colspan="' . ($paramNbOfMonths - $colspan) . '" class="reportTableHeader">' . ($year + 1) . '</td>';            
        }
    }
} elseif ($paramNbOfMonths > 12){
    $reste = $paramNbOfMonths % 12;
    $nbModulo = ($paramNbOfMonths - $reste) / 12 - ($reste==0?1:0);
    $firstPart = 12 - $month + 1;
    //$firstPage does the first year even if it's not a full year
    echo '<td colspan="' . $firstPart . '" class="reportTableHeader">' . $year . '</td>';
    $lastPart = $reste - $firstPart;
    //the while fill the second year and eventually the other year until the end
    while ($nbModulo != 0){
        $year++;
        if ($nbModulo == 1 && $lastPart == 0){
            echo '<td colspan='. ($paramNbOfMonths - $firstPart) .' class="reportTableHeader">' . $year . '</td>';
        } elseif($nbModulo == 1 && $lastPart > 0){
            echo '<td colspan=12 class="reportTableHeader">' . $year . '</td>';
        } else {
            echo '<td colspan=12 class="reportTableHeader">' . $year . '</td>';
        }
        $nbModulo--;
    }
    //if the latest year is not full, $lastPart complete the array
    if($lastPart > 0){
        echo '<td colspan="' . $lastPart . '" class="reportTableHeader">' . ($year + 1) . '</td>';
    }
}
echo '</tr><tr>';
$arrMonth=getArrayMonth(4,true);
for ($i = 0; $i <= $month; $i++) {
    $val = array_shift($arrMonth);
    array_push($arrMonth, $val);
}

//====BEGIN OF THE GRAPH====
//The possibility to choose the number of months obligate to add the point in function of this number,
//so I begin the graph before the end of the creation of the array

if (! testGraphEnabled()) { return;}

$dataSet=new pData;
$dataSet->addPoints($seniority,"seniority");
$dataSet->setSerieDescription("seniority",i18n("seniority").' ('.i18n('month').')');
$dataSet->setSerieOnAxis("seniority",0);
$serieSettings = array("R"=>100,"G"=>100,"B"=>200,"Alpha"=>80);
$dataSet->setPalette("seniority",$serieSettings);
//====END OF THE BEGIN====

$nbParamMois = 0;//Number of case with month to create
$nbInf13 = 1;
$indice = 10;//Variable which permits browse $arrMonth
//= 10 because the month select in the spinner correspond to the index 10
while ($nbParamMois != $paramNbOfMonths){
    //This if allows you to browse one year and reset the value when we change year
    if($nbInf13 == 13){
        $nbInf13 = 1;
    }
    if($nbInf13 != 13){
        if($indice != 12){// 12 because it's the index of the max element of $arrMonth
            echo '<td class="reportTableColumnHeader">' . $arrMonth[$indice] . '</td>';
            $dataSet->addPoints($arrMonth[$indice],"month");
            $indice++;
            $nbParamMois++;
        } else {
            //Here it permits browse one year and reset the value when we come to the end of $arrMonth
            $indice=0;
        }
        $nbInf13++;
    }
}
echo '</tr>';

$tab=$seniority;
$caption=i18n('seniority');
echo '<tr><td class="reportTableLineHeader" style="width:18%">' . $caption .' ('.i18n('month').')'. '</td>';
$style = 77/$paramNbOfMonths;
foreach ($tab as $id=>$val) {
    echo '<td style="width:'.$style.'%;" class="reportTableData">';
    echo $val;
    echo '</td>';
}
echo '</table>';

//=====FOLLOWING THE GRAPH=====
$dataSet->setAbscissa("month");

// Initialise the graph
if ($paramNbOfMonths > 40){
    $widthGraph = $paramNbOfMonths/40;
    $width=1000*$widthGraph;
} else {
    $width=1000;
}
$legendWidth=0;
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
$graph->drawLegend($width/2-30,10,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
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

$dataSet->setSerieDrawable("seniority",true);
$graph->drawAreaChart(array("DisplayColor"=>DISPLAY_AUTO));
$graph->drawPlotChart();
$dataSet->setSerieDrawable("seniority",false);

$graph->drawBarChart();

$imgName=getGraphImgName("reportResourceSeniority");
$graph->render($imgName);
echo '<table width="95%" style="margin-top:20px;" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />';
echo '</td></tr></table>';

end:
