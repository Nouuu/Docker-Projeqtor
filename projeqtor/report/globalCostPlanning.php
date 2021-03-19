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

//echo "globalCostPlanning.php";
include_once '../tool/projeqtor.php';
$idProject="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $idProject=trim($_REQUEST['idProject']);
  $idProject = Security::checkValidId($idProject);
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
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project',$idProject)) . '<br/>';
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

$accessRightRead=securityGetAccessRight('menuProject', 'read');
  
$user=getSessionUser();
$queryWhere=getAccesRestrictionClause('Activity','w',false,true,true);

if ($idProject!='') {
  $queryWhere.=  " and w.idProject in " . getVisibleProjectsList(true, $idProject) ;
} else {
  //
}
// Remove Admin Projects : should not appear in Work Plan
$queryWhere.= " and w.idProject not in " . Project::getAdminitrativeProjectList() ;

if ($paramYear) {
  $queryWhere.=  " and year=".Sql::str($paramYear);
}
if ($paramMonth) {
  $queryWhere.=  " and month=".Sql::str($periodValue);
}
if ( $paramWeek) {
  $queryWhere.=  " and week=".Sql::str($periodValue);
}
if ($paramTeam) {
  $res=new Resource();
  $lstRes=$res->getSqlElementsFromCriteria(array('idTeam'=>$paramTeam));
  $inClause='(0';
  foreach ($lstRes as $res) {
    $inClause.=','.$res->id;
  }
  $inClause.=')';
  $queryWhere.= " and w.idResource in ".$inClause;
}

if ($idOrganization ) {
  $orga = new Organization($idOrganization);
  $listResOrg=$orga->getResourcesOfAllSubOrganizationsListAsArray();
  $inClause='(0';
  foreach ($listResOrg as $res) {
    $inClause.=','.$res;
  }
  $inClause.=')';
  $queryWhere.= " and w.idResource in ".$inClause;
}
$querySelect1= 'select sum(w.cost) as sumCost, w.' . $scale . ' as scale , w.idProject'; 
$queryGroupBy1 = 'w.'.$scale . ', w.idProject, t2.sortOrder';
$queryWhere1 = $queryWhere;

$querySelect2= 'select sum(w.work * a.dailyCost) as sumCost, w.' . $scale . ' as scale , w.idProject'; 
$queryGroupBy2 = $scale . ', w.idProject, t2.sortOrder';
$queryWhere2 = $queryWhere . ' and w.idAssignment=a.id ';
// constitute query and execute

$tab=array();
$start="";
$end="";
for ($i=1;$i<=2;$i++) {
  $obj=($i==1)?new Work():new PlannedWork();
  $proj= new Project();
  $ass=new Assignment();
  $var=($i==1)?'real':'plan';
  $querySelect=($i==1)?$querySelect1:$querySelect2;
  $queryGroupBy=($i==1)?$queryGroupBy1:$queryGroupBy2;
  $queryWhere=($i==1)?$queryWhere1:$queryWhere2;
  //$queryFrom=($i==1)?$queryFrom1:$queryFrom2;
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query=$querySelect 
     . ' from ' . $obj->getDatabaseTableName().' w '.(($i==2)?', '.$ass->getDatabaseTableName() . ' a':'') .', '.$proj->getDatabaseTableName().' t2 ' 
     . ' where ' . $queryWhere.' AND t2.id=w.idProject ' 
     . ' group by ' . $queryGroupBy
     . ' order by t2.sortOrder asc ';
  $result=Sql::query($query);
  while ($line = Sql::fetchLine($result)) {
  	$line=array_change_key_case($line,CASE_LOWER);
    $date=$line['scale'];
    $proj=$line['idproject'];
    $cost=round($line['sumcost'],2);
    if (! array_key_exists($proj, $tab) ) {
      $tab[$proj]=array("name"=>SqlList::getNameFromId('Project', $proj), "real"=>array(),"plan"=>array());
    }
    $tab[$proj][$var][$date]=$cost;
    if ($start=="" or ($start>$date and $date)) {
      $start=$date;
    }
    if ($end=="" or $end<$date) {
      $end=$date;
    }
  }
}
if (checkNoData($tab)) if (!empty($cronnedScript)) goto end; else exit;

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
$plannedStyle=' style="width:20px;text-align:center;background-color:' . $plannedBGColor . '; color: ' . $plannedFrontColor . ';" ';

echo "<table width='95%' align='center'><tr>";
echo '<td><table width="100%" align="left"><tr>';
echo "<td class='reportTableDataFull' style='width:20px; text-align:center;'>";
echo "1";
echo "</td><td width='100px' class='legend'>" . i18n('colRealCost') . "</td>";
echo "<td width='5px'>&nbsp;&nbsp;&nbsp;</td>";
echo '<td class="reportTableDataFull" ' . $plannedStyle . '>';
echo "<i>1</i>";
echo "</td><td width='100px' class='legend'>" . i18n('colPlanned') . "</td>";
echo "<td>&nbsp;</td>";
echo "</tr></table>";
echo "<br/>";
echo '<table width="100%" align="center">';
echo '<tr rowspan="2">';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Project') . '</td>';
foreach ($arrYear as $year=>$nb) {
  echo '<td class="reportTableHeader" colspan="' . $nb . '">' . $year . '</td>';
}
echo '<td class="reportTableHeader" rowspan="2">' . i18n('sum') . '</td>';
echo '</tr>';
echo '<tr>';
$arrSum=array();
foreach ($arrDates as $date) {
  echo '<td class="reportTableColumnHeader" >';
  echo substr($date,4,2); 
  echo '</td>';
  $arrSum[$date]=0;
} 
echo '</tr>';
$sumProj=array();
foreach($tab as $proj=>$lists) {
  $sumProj[$proj]=array();
  for ($i=1; $i<=2; $i++) {
    if ($i==1) {
      echo '<tr><td class="reportTableLineHeader" rowspan="2">' . htmlEncode($lists['name']) . '</td>';
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
        $sumProj[$proj][$date]=0;
      }
      $val=null;
      if (array_key_exists($mode, $lists) and array_key_exists($date,$lists[$mode])) {
        $val=$lists[$mode][$date];
      }
      echo '<td class="reportTableData" ' . $style . '>';
      echo ($ital)?'<i>':'';
      echo htmlDisplayCurrency($val);
      echo ($ital)?'</i>':'';
      $sum+=$val;
      $arrSum[$date]+=$val;
      echo '</td>';
      $sumProj[$proj][$date]+=$val;
    }
    echo '<td class="reportTableColumnHeader" style="text-align:right;">';
    echo ($ital)?'<i>':'';
    echo htmlDisplayCurrency($sum);
    echo ($ital)?'</i>':'';
    echo '</td>';
    echo '</tr>';
    
  }
}
echo "<tr><td>&nbsp;</td></tr>";
echo '<tr><td class="reportTableHeader" >' . i18n('sum') . '</td>';
$sum=0;
$cumul=array();
foreach ($arrSum as $date=>$val) {
  echo '<td class="reportTableHeader" >' . htmlDisplayCurrency($val) . '</td>';
  $sum+=$val;
  $cumul[$date]=$sum;
}
echo '<td class="reportTableHeader" >' . htmlDisplayCurrency($sum) . '</td>';
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
foreach($sumProj as $id=>$vals) {
  $proj = SqlList::getNameFromId('Project', $id);
  $dataSet->addPoints($vals,$proj);
  $dataSet->setSerieDescription($tab[$id]['name'],$proj);
  $dataSet->setSerieOnAxis($proj,0);
  // take color of project   
  $proje=new Project($id);
  $projCol = $proje->color;
  $projectColor=$proje->getColor();
  $colorProj=hex2rgb($projectColor);
  if($projCol){
    $serieSettings = array("R"=>$colorProj['R'],"G"=>$colorProj['G'],"B"=>$colorProj['B']);
    $dataSet->setPalette($proj,$serieSettings);
  } else {
    $serieSettings = array("R"=>$rgbPalette[($nbItem % 12)]['R'],"G"=>$rgbPalette[($nbItem % 12)]['G'],"B"=>$rgbPalette[($nbItem % 12)]['B']);
    $dataSet->setPalette($proj,$serieSettings);
  }
  //
  $nbItem++;
}
$arrLabel=array();
foreach($arrDates as $date){
  $arrLabel[]=substr($date,0,4) . '-' . substr($date,4,2);
}

$dataSet->addPoints($arrLabel,"dates");
$dataSet->setAbscissa("dates");
$dataSet->setSerieOnAxis("dates",0);  

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
    "DrawYLines"=>array(0), "DrawXLines"=>false,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>200,"GridG"=>200,"GridB"=>200);
$graph->drawScale($formatGrid);
$graph->Antialias = TRUE;
$graph->drawStackedBarChart();

$serie = 0 ;
foreach($sumProj as $id=>$vals) {
  $serie+=1;
  $dataSet->RemoveSerie($tab[$id]['name']);
}

$dataSet->setAxisPosition(0,AXIS_POSITION_RIGHT);
$dataSet->addPoints($cumul,"sum");
$dataSet->setSerieDescription(i18n("cumulated"),"sum");
$dataSet->setSerieOnAxis("sum",0);
$dataSet->setAxisName(0,i18n("cumulated"));

$formatGrid=array("LabelRotation"=>90,"GridTicks"=>0 );
$graph->drawScale($formatGrid);

$dataSet->setPalette("sum",array("R"=>0,"G"=>0,"B"=>0));
$graph->drawLineChart();
$graph->drawPlotChart();
/* Render the picture (choose the best way) */
$imgName=getGraphImgName("globalCostPlanning");
$graph->render($imgName);

echo '<table width="95%" style="margin-top:20px;" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />';
echo '</td></tr></table>';
echo '<br/>';

end:

?>