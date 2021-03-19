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

//echo "tichetSyntesis.php";
include_once '../tool/projeqtor.php';
include_once "../tool/jsonFunctions.php";

//Isset For other Requirement Synthesis File
if (! isset($includedReport)) {
  include("../external/pChart2/class/pData.class.php");
  include("../external/pChart2/class/pDraw.class.php");
  include("../external/pChart2/class/pImage.class.php");
  include("../external/pChart2/class/pPie.class.php");
  
  // set Parameters
  $paramProject=trim(RequestHandler::getId('idProject',false));
  $paramYear=trim(RequestHandler::getYear('yearSpinner'));
  $paramMonth=trim(RequestHandler::getMonth('monthSpinner'));
//   $paramWeek=trim(RequestHandler::getDatetime('weekSpinner'));
  $paramResponsible=trim(RequestHandler::getId('responsible',false));
  $paramRequirementType=trim(RequestHandler::getId('idRequirementType',false));
  $paramRequestor=trim(RequestHandler::getId('requestor',false));
  $paramIssuer=trim(RequestHandler::getId('issuer',false));
  
  $paramWeek='';
  if (array_key_exists('weekSpinner',$_REQUEST)) {
  	$paramWeek=$_REQUEST['weekSpinner'];
  	$paramWeek=Security::checkValidWeek($paramWeek);
  };
  
  $paramPriorities=array();
  if (array_key_exists('priorities',$_REQUEST)) {
  	foreach ($_REQUEST['priorities'] as $idPriority => $boolean) {
  		$paramPriorities[] = $idPriority;
  	}
  }
  
  $user=getSessionUser();
  
  $periodType="";
  $periodValue="";
  if (array_key_exists('periodType',$_REQUEST)) {
	$periodType=$_REQUEST['periodType']; // not filtering as data as data is only compared against fixed strings
	if (array_key_exists('periodValue',$_REQUEST))
	{
		$periodValue=$_REQUEST['periodValue'];
		$periodValue=Security::checkValidPeriod($periodValue);
	}
  }
  
  // Header
  $headerParameters="";
  if ($paramProject!="") {
    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
  }
  if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
    $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';  
  }
  if ($periodType=='year' and $paramMonth!="01") {
    if(!$paramMonth){
      $paramMonth="01";
    }
    $headerParameters.= i18n("startMonth") . ' : ' . i18n(date('F', mktime(0,0,0,$paramMonth,10))) . '<br/>';
  }
  if ($periodType=='month') {
    $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
  }
  if ( $periodType=='week') {
    $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
  }
  if ($paramRequirementType!="") {
    $headerParameters.= i18n("colIdRequirementType") . ' : ' . SqlList::getNameFromId('RequirementType', $paramRequirementType) . '<br/>';
  }
  if ($paramRequestor!="") {
    $headerParameters.= i18n("colRequestor") . ' : ' . SqlList::getNameFromId('Contact', $paramRequestor) . '<br/>';
  }
  if ($paramIssuer!="") {
    $headerParameters.= i18n("colIssuer") . ' : ' . SqlList::getNameFromId('User', $paramIssuer) . '<br/>';
  }
  if ($paramResponsible!="") {
    $headerParameters.= i18n("colResponsible") . ' : ' . SqlList::getNameFromId('Resource', $paramResponsible) . '<br/>';
  }
  if (!empty($paramPriorities)) {
  	$priority = new Priority();
  	$priorities = $priority->getSqlElementsFromCriteria(null, false, null, 'id asc');
  	
  	$prioritiesDisplayed = array();
  	for ($i = 0; $i < count($priorities); $i++) {
  		if ( in_array($i+1, $paramPriorities)) {
  			$prioritiesDisplayed[] = $priorities[$i];
  		}
  	}
  	
  	$headerParameters.= i18n("colPriority") .' : ';
  	foreach ($prioritiesDisplayed as $priority) {
  		$headerParameters.=$priority->name . ', ';
  	}
  	$headerParameters=substr($headerParameters, 0, -2);
  	
  	if ( in_array('undefined', $paramPriorities)) {
  		$headerParameters.=', '.i18n('undefinedPriority');
  	}
  }
  //Header END
  include "header.php";
}

$where=getAccesRestrictionClause('Requirement',false);

// Adapt clause on filter
$arrayFilter=jsonGetFilterArray('Report_Requirement', false);
if (count($arrayFilter)>0) {
  $obj=new Requirement();
  $querySelect="";
  $queryFrom="";
  $queryOrderBy="";
  $idTab=0;
  jsonBuildWhereCriteria($querySelect,$queryFrom,$where,$queryOrderBy,$idTab,$arrayFilter,$obj);
}

if ($periodType) {
  $start=date('Y-m-d');
  $end=date('Y-m-d');
  if ($periodType=='year') {
    $start=$paramYear . '-' . $paramMonth . '-01';
    $endMonth = ($paramMonth=="01") ? "12" : (($paramMonth<11?'0':'') . ($paramMonth - 1));
    $endYear = ($paramMonth=="01") ? $paramYear : $paramYear + 1;
    $end=$endYear . '-' . $endMonth . '-31';
  } else if ($periodType=='month') {
    if ((!$paramYear and !$paramMonth) or (!$paramYear) or (!$paramMonth)) {
      echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
      if(!$paramYear and !$paramMonth){
        echo i18n('messageNoData',array(i18n('yearAndmonth')));
      } else if(!$paramYear){
        echo i18n('messageNoData',array(i18n('year')));
      } else if(!$paramMonth){
        echo i18n('messageNoData',array(i18n('month')));
      }
      echo '</div>';
      if (!empty($cronnedScript)) goto end; else exit;
    }
    $start=$paramYear . '-' . (($paramMonth<10)?'0':'') . $paramMonth . '-01';
    $end=$paramYear . '-' . (($paramMonth<10)?'0':'') . $paramMonth . '-' . date('t',mktime(0,0,0,$paramMonth,1,$paramYear));  
  } if ($periodType=='week') {
      if ((!$paramYear and !$paramWeek) or (!$paramYear) or (!$paramWeek)) {
        echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
        if(!$paramYear and !$paramWeek){
          echo i18n('messageNoData',array(i18n('yearAndweek')));
        } else if(!$paramYear){
          echo i18n('messageNoData',array(i18n('year')));
        } else if(!$paramWeek){
          echo i18n('messageNoData',array(i18n('week')));
        }
        echo '</div>';
        if (!empty($cronnedScript)) goto end; else exit;
      }
    $start=date('Y-m-d', firstDayofWeek($paramWeek, $paramYear));
    $end=addDaysToDate($start,6);
  }
  $start.=' 00:00:00';
  $end.=' 23:59:59';
  $where.=" and (  creationDateTime>= '" . $start . "'";
  $where.="        and creationDateTime<='" . $end . "' )";
}//End of Isset

if ($paramProject!="") {
   $where.=" and idProject in " .  getVisibleProjectsList(false, $paramProject);
}
if ($paramRequirementType!="") {
  $where.=" and idRequirementType='" . Sql::fmtId($paramRequirementType) . "'";
}
if ($paramRequestor!="") {
  $where.=" and requestor='" . Sql::fmtId($paramRequestor) . "'";
}
if ($paramIssuer!="") {
  $where.=" and idUser='" . Sql::fmtId($paramIssuer) . "'";
}
if ($paramResponsible!="") {
  $where.=" and idResource='" . Sql::fmtId($paramResponsible) . "'";
}

$filterByPriority = false;
if (!empty($paramPriorities) and $paramPriorities[0] != 'undefined') {
	$filterByPriority = true;
	$where.=" and idPriority in (";
	foreach ($paramPriorities as $idDisplayedPriority) {
		if ($idDisplayedPriority== 'undefined') continue;
		$where.=$idDisplayedPriority.', ';
	}
	$where = substr($where, 0, -2);
	$where.=")";
	
}
if ($filterByPriority and in_array('undefined', $paramPriorities)) {
	$where.=" or idPriority is null";
}
else if (in_array('undefined', $paramPriorities)) {
	$where.=" and idPriority is null";
}
else if ($filterByPriority) {
	$where.=" and idPriority is not null";
}

$order="";
$requirement=new Requirement();
$lstRequirement=$requirement->getSqlElementsFromCriteria(null,false, $where, $order);

$lstUrgency=array();
$lstCriticality=array();
$lstPriority=array();
$lstType=array();
$lstIssuer=array();
$lstResponsible=array();

foreach ($lstRequirement as $r) {
  $urgency=($r->idUrgency==null or trim($r->idUrgency)=='')?'0':$r->idUrgency;
  $criticality=($r->idCriticality==null or trim($r->idCriticality)=='')?'0':$r->idCriticality;
  $priority=($r->idPriority==null or trim($r->idPriority)=='')?'0':$r->idPriority;
  $type=$r->idRequirementType;
  $issuer=$r->idUser;
  $responsible=($r->idResource==null or trim($r->idResource)=='')?'0':$r->idResource;
  //urgency
  if (! array_key_exists($urgency, $lstUrgency)) {
    $lstUrgency[$urgency]=0;
  }
  $lstUrgency[$urgency]+=1;
  //criticality
  if (! array_key_exists($criticality, $lstCriticality)) {
    $lstCriticality[$criticality]=0;
  }
  $lstCriticality[$criticality]+=1;
  //priority
  if (! array_key_exists($priority, $lstPriority)) {
    $lstPriority[$priority]=0;
  }
  $lstPriority[$priority]+=1;
  //type
  if (! array_key_exists($type, $lstType)) {
    $lstType[$type]=0;
  }
  $lstType[$type]+=1;
  //issuer
  if (! array_key_exists($issuer, $lstIssuer)) {
    $lstIssuer[$issuer]=0;
  }
  $lstIssuer[$issuer]+=1;
  //responsible
  if (! array_key_exists($responsible, $lstResponsible)) {
    $lstResponsible[$responsible]=0;
  }
  $lstResponsible[$responsible]+=1;
}

if (checkNoData($lstRequirement)) if (!empty($cronnedScript)) goto end; else exit;

echo '<table style="width:95%;" align="center">';
echo '<tr>';
echo '<td class="section" style="width:49%;">' . i18n('RequirementType') . '</td>';
echo '<td style="width:2%;">&nbsp;</td>';
echo '<td class="section" style="width:49%;">' . i18n('Urgency') . '</td>';
echo '</tr><tr><td valign="top">';
drawSynthesisTable('RequirementType', $lstType); 
echo '</td><td></td><td valign="top">';
drawSynthesisTable('Urgency', $lstUrgency);  
echo '</td>';
echo '</tr>';

echo '<tr><td colspan="3">&nbsp;</td></tr>';
echo '<tr>';
echo '<td class="section" style="width:49%;">' . i18n('Priority') . '</td>';
echo '<td style="width:2%;">&nbsp;</td>';
echo '<td class="section" style="width:49%;">' . i18n('Criticality') . '</td>';
echo '</tr><tr><td style="width:49%;" valign="top">';
drawSynthesisTable('Priority',$lstPriority); 
echo '</td><td style="width:2%;"></td><td style="width:49%;" valign="top">';
drawSynthesisTable('Criticality', $lstCriticality);  
echo '</td>';
echo '</tr>';
echo '<tr><td colspan="3">&nbsp;</td></tr>';
echo '<tr>';
echo '<td class="section" style="width:49%;">' . i18n('colIssuer') . '</td>';
echo '<td style="width:2%;">&nbsp;</td>';
echo '<td class="section" style="width:49%;">' . i18n('colResponsible') . '</td>';
echo '</tr>';
echo '<tr><td valign="top">';
drawSynthesisTable('User',$lstIssuer); 
echo '</td><td></td><td valign="top">';
drawSynthesisTable('Resource', $lstResponsible);  
echo '</td>';
echo '</tr>';
echo '</table>';

function drawSynthesisTable($scope, $lst) {
  echo '<table valign="top" style="width:100%">';
  echo '<tr>';
  echo '<td style="width:50%" valign="top">';
  echo '<table style="width:230px" valign="top">';
  $lstRef=SqlList::getList($scope,'name',false,true);
  if (array_key_exists('0', $lst)) {
    echo '<tr><td class="reportTableHeader" style="width:150px">';
    echo '<i>'.i18n('undefinedValue').'</i>';
    echo '</td><td class="reportTableData" style="width:80px">' . $lst['0'] . '</td></tr>'; 
  }
  foreach ($lstRef as $code=>$val) {
    if (array_key_exists($code, $lst)) {
      echo '<tr><td class="reportTableHeader" style="width:150px">';
      echo $val;
      echo '</td><td class="reportTableData" style="width:80px">' . $lst[$code] . '</td></tr>'; 
    }
  }
  echo '</table>';
  echo '</td>';
  echo '<td style="width:250px">';
  drawsynthesisGraph($scope, $lst);
  echo '</td>';
  echo "</tr></table>";
}

function drawsynthesisGraph($scope, $lst) {
	global $rgbPalette;
	global $arrayColors;
	$tabColor = array();
  if (! testGraphEnabled()) { return;}
  if (count($lst)==0) { return;}  
  $valArr=array();
  $legArr=array();
  $lstRef=SqlList::getList($scope,'name',null,true,false);
  $lstColorRef=array();
  if (property_exists($scope, 'color')) {
    $lstColorRef=SqlList::getList($scope,'color',null,true,false);
  }
  $nbItem=0;
  
  $hgt=count($lst)*20;
  $hgt=($hgt<110)?110:$hgt;
  $dataSet=new pData;
  if (array_key_exists('0', $lst)) {
    $legArr[]=i18n('undefinedValue');
    $valArr[]=$lst['0'];
    $nbItem++;
  }
  foreach ($lstRef as $code=>$val) {
    if (array_key_exists($code, $lst)) {
      $valArr[]=$lst[$code];
      $legArr[]=$val;
      if (isset($lstColorRef[$code])) {
        $color=$lstColorRef[$code];
      } else {
        $color=$arrayColors[$code%count($arrayColors)];
      }
      $colorRequirement = hex2rgb($color);
      $serieSettings = array("R"=>$colorRequirement['R'],"G"=>$colorRequirement['G'],"B"=>$colorRequirement['B']);
      $tabColor[$nbItem]=$serieSettings;
      $nbItem++;
    }
  }
  $dataSet->addPoints($valArr,$scope);
  $dataSet->setSerieDescription(i18n($scope),$scope);
  $dataSet->setSerieOnAxis($scope,0);
  $dataSet->addPoints($legArr,"legend");
  
  $dataSet->setAbscissa("legend");
  // Initialise the graph 
  $graph = new pImage(300,$hgt,$dataSet);
  
  /* Draw the background */
  $graph->Antialias = FALSE;
  
  $pieChart = new pPie($graph,$dataSet);
  
  if (array_key_exists('0', $lst)) {
  	$pieChart->setSliceColor(0,array("R"=>204,"G"=>204,"B"=>204));
  }else {
  	$pieChart->setSliceColor(0,$tabColor[0]);
  }
  
  for ($i=1; $i<$nbItem; $i++){
  	$pieChart->setSliceColor($i,$tabColor[$i]);
  }
  
  /* Set the default font */
  $graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8));
  $formSettings = array("R"=>255,"G"=>255,"B"=>255,"Alpha"=>0,"Surrounding"=>0);
  $graph->setShadow(TRUE,$formSettings);
  $pieChart->draw3DPie(90,($hgt/2)+7,array("Border"=>FALSE));
  $pieChart->drawPieLegend(180,20,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_VERTICAL));
  $imgName=getGraphImgName("RequirementYearlySynthesis");
  
  $graph->Render($imgName);
  echo '<table width="95%" align="center"><tr><td align="center">';
  echo '<img src="' . $imgName . '" />'; 
  echo '</td></tr></table>';
}

end:

?>