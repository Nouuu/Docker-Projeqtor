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

$idResource="";
if (array_key_exists('idResource',$_REQUEST) and trim($_REQUEST['idResource'])!="") {
  $idResource=trim($_REQUEST['idResource']);
  $idResource = Security::checkValidId($idResource);
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
};
$paramWeek='';
if (array_key_exists('weekSpinner',$_REQUEST)) {
	$paramWeek=$_REQUEST['weekSpinner'];
	$paramWeek=Security::checkValidWeek($paramWeek);
};
$idOrganization = trim(RequestHandler::getId('idOrganization'));
$showAdminProj=trim(RequestHandler::getValue('showAdminProj'));
$paramTeam='';
if (array_key_exists('idTeam',$_REQUEST)) {
  $paramTeam=trim($_REQUEST['idTeam']);
  Security::checkValidId($paramTeam);
}
$scale='month';
if (array_key_exists('scale',$_REQUEST)) {
  $scale=$_REQUEST['scale'];
  $scale=Security::checkValidPeriodScale($scale);
}
$periodValue='';
if (array_key_exists('periodValue',$_REQUEST))
{
	$periodValue=$_REQUEST['periodValue'];
	$periodValue=Security::checkValidPeriod($periodValue);
}
$headerParameters="";
if ($idResource!="") {
  $headerParameters.= i18n("colIdResource") . ' : ' . htmlEncode(SqlList::getNameFromId('Affectable',$idResource)) . '<br/>';
}
if ($idOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization',$idOrganization)) . '<br/>';
}
if ( $paramTeam) {
  $headerParameters.= i18n("team") . ' : ' . SqlList::getNameFromId('Team', $paramTeam) . '<br/>';
}
if ($paramYear) {
  $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
}
if ($paramMonth) {
  $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $paramWeek) {
  $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
}

include "header.php";
  
$user=getSessionUser();
$queryWhere=getAccesRestrictionClause('Activity','t1',false,true,true);

if ($idResource!='') {
  //$queryWhere.=  " and t1.idProject in " . getVisibleProjectsList(true, $idResource) ;
  $queryWhere.=  " and t1.idResource = " . Sql::fmtId($idResource) ;
} 
//   //
// }
// Remove Admin Projects : should not appear in Work Plan
if($showAdminProj !='on'){
  $queryWhere.= " and t1.idProject not in " . Project::getAdminitrativeProjectList() ;
}


if ($paramYear) {
	$queryWhere.=  " and year=".Sql::str($paramYear);
}
if ($paramMonth) {
  $queryWhere.=  " and month=".Sql::str($periodValue);
}
if ( $paramWeek) {
	$queryWhere.=  " and week=".Sql::str($periodValue);
}
if ( $idResource) {
  $queryWhere.=  " and t1.idResource=".Sql::fmtId($idResource);
}
if ($idOrganization ) {
  $orga = new Organization($idOrganization);
  $listResOrg=$orga->getResourcesOfAllSubOrganizationsListAsArray();
  $inClause='(0';
  foreach ($listResOrg as $res) {
    $inClause.=','.$res;
  }
  $inClause.=')';
  $queryWhere.= " and t1.idResource in ".$inClause;
}
if ($paramTeam) {
	$res=new Resource();
	$lstRes=$res->getSqlElementsFromCriteria(array('idTeam'=>$paramTeam));
	$inClause='(0';
	foreach ($lstRes as $res) {
		$inClause.=','.$res->id;
	}
	$inClause.=')';
	$queryWhere.= " and t1.idResource in ".$inClause;
}

$querySelect= 'select sum(work) as sumWork, ' . $scale . ' as scale , t1.idResource as idresource '; 
$queryGroupBy = $scale . ', t1.idResource';
// constitute query and execute

$tab=array();
$start="";
$end="";
$res=new Resource();
$resTable=$res->getDatabaseTableName();
for ($i=1;$i<=2;$i++) {
  $obj=($i==1)?new Work():new PlannedWork();
  $var=($i==1)?'real':'plan';
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query=$querySelect 
     . ' from ' . $obj->getDatabaseTableName().' t1, '.$resTable.' t2 '
     . ' where ' . $queryWhere." and t1.idResource=t2.id "
     . ' group by ' . $queryGroupBy;
    // . ' order by t2.sortOrder asc '; 
  $result=Sql::query($query);
  while ($line = Sql::fetchLine($result)) {
  	$line=array_change_key_case($line,CASE_LOWER);
    $date=$line['scale'];
    $res=$line['idresource'];
    //$work=round($line['sumwork'],2);
    $work=$line['sumwork'];
    if (! array_key_exists($res, $tab) ) {
      $tab[$res]=array("name"=>SqlList::getNameFromId('Affectable', $res), "real"=>array(),"plan"=>array());
    }
    $tab[$res][$var][$date]=$work;
    if ($start=="" or $start>$date) {
      $start=$date;
    }
    if ($end=="" or $end<$date) {
      $end=$date;
    }
  }
}
if (checkNoData($tab)) if (!empty($cronnedScript)) goto end; else exit;

// Sort resources on name
uasort ( $tab , function ($a, $b) {  return strnatcmp($a["name"],$b["name"]); } );

$arrDates=array();
$arrYear=array();
$date=$start;
while ($date<=$end) {
  $arrDates[]=$date;
  $year=substr($date,0,4);
  if (! array_key_exists($year,$arrYear)) {
    $arrYear[$year]=0;
  }
  $arrYear[$year]+=1;
  if ($scale=='week') {
    $day=date('Y-m-d',firstDayofWeek(substr($date,4,2),substr($date,0,4)));
    $next=addWeeksToDate($day,1);
    $date=str_replace('-','', weekFormat($next));
  } else {
    $day=substr($date,0,4) . '-' . substr($date,4,2) . '-01';
    $next=addMonthsToDate($day,1);
    $date=substr($next,0,4) . substr($next,5,2);
  }
}
// Header
$plannedBGColor='#FFFFDD';
$plannedFrontColor='#777777';
$styleRed=' style="color:#d05050;font-weight:bold;" ';
$plannedStyle=' style="width:20px;text-align:center;background-color:' . $plannedBGColor . '; color: ' . $plannedFrontColor . ';" ';
$plannedStyleRed=' style="width:20px;text-align:center;background-color:' . $plannedBGColor . ';'.$styleRed.'" ';
 
echo "<table width='95%' align='center'><tr>";
echo '<td>';
echo '<table width="100%" align="left"><tr>';
echo "<td class='reportTableDataFull' style='width:20px; text-align:center;'>1</td>";
echo "<td width='100px' class='legend'>" . i18n('colRealWork') . "</td>";
echo "<td width='5px'>&nbsp;&nbsp;&nbsp;</td>";
echo '<td class="reportTableDataFull" ' . $plannedStyle . '><i>1</i></td>';
echo "<td width='100px' class='legend'>" . i18n('colPlanned') . "</td>";
echo "<td>&nbsp;</td>";
echo "<td class='legend'>" . Work::displayWorkUnit() . "</td>";
echo "<td>&nbsp;</td>";
echo "</tr></table>";
echo "<br/>";
echo '<table width="100%" align="left">';
echo '<tr rowspan="2">';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Resource') . '</td>';
foreach ($arrYear as $year=>$nb) {
  echo '<td class="reportTableHeader" colspan="' . $nb . '">' . $year . '</td>';
}
echo '<td class="reportTableHeader" rowspan="2" style="width:40px;">' . i18n('sum') . '</td>';
echo '</tr>';
echo '<tr>';
$arrSum=array();
foreach ($arrDates as $date) {
  echo '<td class="reportTableColumnHeader" style="width:40px" >';
  echo substr($date,4,2); 
  echo '</td>';
  $arrSum[$date]=0;
} 
echo '</tr>';
$sumProj=array();
$sumProjUnit=array();
$capaResDate=array();
foreach($tab as $res=>$lists) {
  $currentRes=new Resource($res);
  $capaResDate[$res]=array();
  foreach($arrDates as $date) {
    if ($scale=='week') {
      $firstDay=date('Y-m-d',firstDayofWeek(substr($date,4,2),substr($date,0,4)));
      $lastDay=addDaysToDate($firstDay, 7);
    } else {
      $firstDay=substr($date,0,4) . '-' . substr($date,4,2) . '-01';
      $lastDay=substr($date,0,4) . '-' . substr($date,4,2).'-'.lastDayOfMonth(intval(substr($date,4,2)),substr($date,0,4));
    }
    $capa=0;
    for ($dt=$firstDay;$dt<=$lastDay;$dt=addDaysToDate($dt,1)) {
      if (isOffDay($dt,$currentRes->idCalendarDefinition)) continue;
      $capa+=$currentRes->getCapacityPeriod($dt);
    }
    $capaResDate[$res][$date]=$capa;
  }
}
foreach($tab as $res=>$lists) {
  $sumProj[$res]=array();
  $sumProjUnit[$res]=array();
  for ($i=1; $i<=2; $i++) {
    if ($i==1) {
      echo '<tr><td class="reportTableLineHeader" style="width:200px;" rowspan="2">' . htmlEncode($lists['name']) . '</td>';
      $style='';
      $mode='real';
      $ital=false;
    } else {
      echo '<tr>';
      $style=$plannedStyle;
      $mode='plan';
      $ital=true;
    }
    $sum=0;
    foreach($arrDates as $date) {
      if ($i==1) {
        $sumProj[$res][$date]=0;
        $sumProjUnit[$res][$date]=0;
      }
      $val=null;
      if (array_key_exists($mode, $lists) and array_key_exists($date,$lists[$mode])) {
        $val=round($lists[$mode][$date],((Work::getWorkUnit()=='hours')?5:2));
      }
      $styleDate=$style;
      if ($capaResDate[$res][$date]<$val) {   
        if ($style==$plannedStyle) $styleDate=$plannedStyleRed;
        else $styleDate=$styleRed;
      }
      echo '<td class="reportTableData" ' . $styleDate . '>';
      echo ($ital)?'<i>':'';
      echo Work::displayWork($val);
      echo ($ital)?'</i>':'';
      $sum+=$val;
      $arrSum[$date]+=$val;
      echo '</td>';
      $sumProj[$res][$date]+=$val;
      $sumProjUnit[$res][$date]+=Work::displayWork($val);
    }
    echo '<td class="reportTableColumnHeader" style="width:50px">';
    echo ($ital)?'<i>':'';
    echo Work::displayWork($sum);
    echo ($ital)?'</i>':'';
    echo '</td>';
    echo '</tr>';
    
  }
}
echo "<tr><td>&nbsp;</td></tr>";
echo '<tr><td class="reportTableHeader" style="width:40px;">' . i18n('sum') . '</td>';
$sum=0;
$cumul=array();
$cumulUnit=array();
foreach ($arrSum as $date=>$val) {
  echo '<td class="reportTableHeader" >' . Work::displayWork($val) . '</td>';
  $sum+=$val;
  $cumul[$date]=$sum;
  $cumulUnit[$date]=Work::displayWork($sum);
}
echo '<td class="reportTableHeader">' . Work::displayWork($sum) . '</td>';
echo '</tr>';
echo '</table>';
echo '</td></tr></table>';

// Graph
if (! testGraphEnabled()) { return;}
  include("../external/pChart2/class/pData.class.php");
  include("../external/pChart2/class/pDraw.class.php");
  include("../external/pChart2/class/pImage.class.php");
$dataSet=new pData;
$nbItem=0;
foreach($sumProjUnit as $id=>$vals) {
  $dataSet->setAxisPosition(0,AXIS_POSITION_LEFT);
  $dataSet->addPoints($vals,$tab[$id]['name']);
  $dataSet->setSerieDescription($tab[$id]['name'],$tab[$id]['name']);
  $dataSet->setSerieOnAxis($tab[$id]['name'],0);
  
  //$rsc=new Resource($id);
  // A FAIRE
  //$graph->setColorPalette($nbItem,$rgbPalette[($nbItem % 12)]['R'],$rgbPalette[($nbItem % 12)]['G'],$rgbPalette[($nbItem % 12)]['B']);
  //$dataSet->setAxisName(0,i18n("cumulated"));
  $nbItem++;
}
$arrLabel=array();
foreach($arrDates as $date){
  $arrLabel[]=substr($date,0,4) . '-' . substr($date,4,2);
}

$dataSet->addPoints($arrLabel,"dates");
$dataSet->setAbscissa("dates");

$width=1000;
$legendWidth=300;
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
$graph->drawLegend($width+30,17,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
    "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
    "FontR"=>55,"FontG"=>55,"FontB"=>55,
    "Margin"=>5));

/* Draw the scale */
$graph->setGraphArea(60,50,$width-20,$height-$legendHeight);
$formatGrid=array("Mode"=>SCALE_MODE_ADDALL_START0, "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>true,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>200,"GridG"=>200,"GridB"=>200);
$graph->drawScale($formatGrid);
$graph->Antialias = TRUE;
$graph->drawStackedBarChart(array("DisplayColor"=>DISPLAY_AUTO,"DisplaySize"=>6,"BorderR"=>255, "BorderG"=>255,"BorderB"=>255));

foreach($sumProjUnit as $id=>$vals) {
  $dataSet->removeSerie($tab[$id]['name']);
}

$dataSet->setAxisPosition(0,AXIS_POSITION_RIGHT);
$dataSet->addPoints($cumulUnit,"sum");
$dataSet->setSerieDescription(i18n("cumulated"),"sum");
$dataSet->setSerieOnAxis("sum",0);
$dataSet->setAxisName(0,i18n("cumulated"));

$formatGrid=array("LabelRotation"=>90,"DrawXLines"=>FALSE,"DrawYLines"=>NONE);
$graph->drawScale($formatGrid);

$graph->drawLineChart();
$graph->drawPlotChart();


$imgName=getGraphImgName("globalWorkPlanning");
$graph->Render($imgName);
echo '<table width="95%" style="margin-top:20px" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';

end:

?>