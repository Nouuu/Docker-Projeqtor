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

if (! isset($includedReport)) {
  include("../external/pChart2/class/pData.class.php");
  include("../external/pChart2/class/pDraw.class.php");
  include("../external/pChart2/class/pImage.class.php");
  
	$paramProject='';
	if (array_key_exists('idProject',$_REQUEST)) {
	  $paramProject=trim($_REQUEST['idProject']);
	  $paramProject=Security::checkValidId($paramProject); // only allow digits
	};

  
  $paramIssuer='';
  if (array_key_exists('issuer',$_REQUEST)) {
    $paramIssuer=trim($_REQUEST['issuer']);
	  $paramIssuer=Security::checkValidId($paramIssuer); // only allow digits
  }

  // Note: removed redundant duplicate
  $paramResponsible='';
  if (array_key_exists('responsible',$_REQUEST)) {
    $paramResponsible=trim($_REQUEST['responsible']);
	  $paramResponsible=Security::checkValidId($paramResponsible); // only allow digits
  }
  
  $paramRefType=''; // Note: not used anywhere. No point in filtering. Filtering anyway
  if (array_key_exists('refType',$_REQUEST)) {
    $paramRefType=trim($_REQUEST['refType']);
	  $paramRefType=Security::checkValidClass($paramRefType); // only allow a-z, A-Z, 0-9
  }
  
  $showIdle=false;
  if (array_key_exists('showIdle',$_REQUEST)) {
    $showIdle=true;
  }
  
  $user=getSessionUser();
    
  // Header
  $headerParameters="";
  if ($paramProject!="") {
    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
  }
  if ($paramIssuer!="") {
    $headerParameters.= i18n("colIssuer") . ' : ' . htmlEncode(SqlList::getNameFromId('User', $paramIssuer)) . '<br/>';
  }
  if ($paramResponsible!="") {
    $headerParameters.= i18n("colResponsible") . ' : ' . htmlEncode(SqlList::getNameFromId('Resource', $paramResponsible)) . '<br/>';
  }
  include "header.php";
}

$obj=new $refType();
$user=getSessionUser();

$query = "select count(id) as nb, id" . $refType . "Type as idType, idStatus ";
$query .= " from " . $obj->getDatabaseTableName();
$query.=" where " . getAccesRestrictionClause($refType,false,false,true,true);
if ($paramProject!='') {
  $query.=  "and idProject in " . getVisibleProjectsList(true, $paramProject) ;
}
if (! $showIdle) {
 $query .= " and idle=0 ";
}
if ($paramIssuer!="") {
 $query .= " and idUser=" . Sql::fmtId($paramIssuer);
}
if ($paramResponsible!="") {
 $query .= " and idResource=" . $paramResponsible; 
}
$query .= " group by id" . $refType . "Type, idStatus";

$result=Sql::query($query);
$arr=array();
$arrStatus=array();
while ($line = Sql::fetchLine($result)) {
	$line=array_change_key_case($line,CASE_LOWER);
  $type=$line['idtype'];
  $status=$line['idstatus'];
  $val=$line['nb'];
  if (! array_key_exists($type, $arr)) {
    $arr[$type]=array();
  }
  if (! array_key_exists($status, $arrStatus)) {
    $arrStatus[$status]=0;
  }
  $arrStatus[$status]+=$val;
  $arr[$type][$status]=$val;
}
$lstStatus=SqlList::getList('Status');
foreach ($lstStatus as $id=>$st) {
  if (! array_key_exists($id, $arrStatus)) {
    unset($lstStatus[$id]);
  }
}
$lstType=SqlList::getList($refType . 'Type');
foreach ($lstType as $id=>$st) {
  if (! array_key_exists($id, $arr)) {
    unset($lstType[$id]);
  }
  $tabTypeColor[$st] = SqlList::getFieldFromId('Type', $id, 'color');
}
if (count($lstStatus)>0) {

	echo '<table width="95%" align="center">';
	echo '<tr><td class="reportTableHeader" rowspan="2">' . i18n($refType . 'Type') . '</td>';
	echo '<td colspan="' . (count($lstStatus  )) . '" class="reportTableHeader">' .  i18n('colIdStatus') . '</td>';
	echo '<td class="reportTableHeader" rowspan="2">' . i18n('sum') . '</td>';
	echo '</tr>';
	echo '<tr>';
	foreach ($lstStatus as $id=>$status) {
	  echo '<td class="reportTableColumnHeader">' . $status . '</td>';
	}
	echo '</tr>';
	
	foreach ($lstType as $idType=>$name) {
	  $sum=0;
	  echo '<tr><td class="reportTableLineHeader" style="width:20%">' . $name . '</td>';
	  if (count($lstStatus)) {
	    $detWidth=floor(70/count($lstStatus));
	  } else {
	    $detWidth='70';
	  }
	  foreach ($lstStatus as $idStatus=>$status) {
	    echo '<td class="reportTableData" style="width:' . $detWidth . '%">';
	    if (isset($arr[$idType][$idStatus])) {
	      echo $arr[$idType][$idStatus];
	      $sum+=$arr[$idType][$idStatus];
	    }
	    echo '</td>';
	  }
	  echo '<td class="reportTableLineHeader" style="width:10%;text-align:center;">' . $sum . '</td>';
	  echo '</tr>';
	}
	
	echo '<tr><td class="reportTableHeader" >' . i18n('sum') . '</td>';
	$sum=0;
	foreach ($lstStatus as $id=>$val) {
	  echo '<td class="reportTableLineHeader" style="text-align:center;">' . $arrStatus[$id] . '</td>';
	  $sum+=$arrStatus[$id];
	}
	echo '<td class="reportTableHeader" >' . $sum . '</td>';
	echo '</tr>';
	echo '</table>';
	
	// Render graph
	// pGrapg standard inclusions     
	if (! testGraphEnabled()) { return;}
	
	$dataSet=new pData;
	$nbItem=0;
	foreach($arr as $id=>$arrType) {
	  $temp=array();
	  foreach ($lstStatus as $is=>$status) {
	    if (array_key_exists($is,$arrType)) {
	      $temp[$is]=$arrType[$is];
	    } else {
	      $temp[$is]=0;
	    }
	  } 
	 
	  $idName = SqlList::getNameFromId('Type', $id);
	  $dataSet->addPoints($temp,$idName);
	  if (isset($lstType[$id])) {
	    $dataSet->setSerieDescription($lstType[$id],$idName);
	    $dataSet->setSerieOnAxis($idName,0);
	    if(isset($tabTypeColor[$idName])){
	      $color=hex2rgb($tabTypeColor[$idName]);
	      $serieSettings=array("R"=>$color['R'],"G"=>$color['G'],"B"=>$color['B']);
	    }else{
	      $serieSettings = array("R"=>$rgbPalette[($nbItem % 12)]['R'],"G"=>$rgbPalette[($nbItem % 12)]['G'],"B"=>$rgbPalette[($nbItem % 12)]['B']);
	    }
	    $dataSet->setPalette($idName,$serieSettings);
  	  $nbItem++;
	  }
	}
	$dataSet->addPoints($lstStatus,"status");
	$dataSet->setAbscissa("status");
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
	$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>10));
	
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
	
	/* Render the picture (choose the best way) */
	$imgName=getGraphImgName("statusDetail");
	$graph->render($imgName);
	echo '<table width="95%" style="margin-top:20px;" align="center"><tr><td align="center">';
	echo '<img src="' . $imgName . '" />'; 
	echo '</td></tr></table>';
	echo '<br/>';
}
?>
