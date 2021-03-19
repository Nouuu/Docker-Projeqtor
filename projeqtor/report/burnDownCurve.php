<?php
/*
 * @author: atrancoso #ticket 84
 */
include_once '../tool/projeqtor.php';

if (! isset ( $includedReport )) {
  include("../external/pChart2/class/pData.class.php");
  include("../external/pChart2/class/pDraw.class.php");
  include("../external/pChart2/class/pImage.class.php");
  
  $paramProject = '';
  if (array_key_exists ( 'idProject', $_REQUEST )) {
    $paramProject = trim ( $_REQUEST ['idProject'] );
    Security::checkValidId ( $paramProject );
  }
  
  $paramProduct = '';
  if (array_key_exists ( 'idProduct', $_REQUEST )) {
    $paramProduct = trim ( $_REQUEST ['idProduct'] );
    $paramProduct = Security::checkValidId ( $paramProduct ); // only allow digits
  }
  ;
  
  $paramVersion = '';
  if (array_key_exists ( 'idVersion', $_REQUEST )) {
    $paramVersion = trim ( $_REQUEST ['idVersion'] );
    $paramVersion = Security::checkValidId ( $paramVersion ); // only allow digits
  }
  $paramUrgency = array();
  if (array_key_exists ( 'urgency', $_REQUEST )) {
    foreach ( $_REQUEST ['urgency'] as $idUrgency => $boolean ) {
      $paramUrgency [] = $idUrgency;
    }
  }
  ;

  $paramCriticality = array();
  if (array_key_exists ( 'criticality', $_REQUEST )) {
    foreach ( $_REQUEST ['criticality'] as $idCriticality => $boolean ) {
      $paramCriticality[] = $idCriticality;
    }
  }

  // Header
  $headerParameters = "";
  
  if ($paramVersion != "") {
    $headerParameters .= i18n ( "colVersion" ) . ' : ' . htmlEncode ( SqlList::getNameFromId ( 'Version', $paramVersion ) ) . '<br/>';
  }
  
  if ($paramProject != "") {
    $headerParameters .= i18n ( "colIdProject" ) . ' : ' . htmlEncode ( SqlList::getNameFromId ( 'Project', $paramProject ) ) . '<br/>';
  }
  
  if ($paramProduct != "") {
    $headerParameters .= i18n ( "colIdProduct" ) . ' : ' . htmlEncode ( SqlList::getNameFromId ( 'Product', $paramProduct ) ) . '<br/>';
  }
  
  if (! empty ( $paramUrgency )) {
    $urg = new Urgency ();
    $urgency = $urg->getSqlElementsFromCriteria ( null, false, null, 'id asc' );
    
    $urgDisplayed = array();
    for($i = 0; $i < count ( $urgency ); $i ++) {
      if (in_array ( $i + 1, $paramUrgency )) {
        $urgDisplayed [] = $urgency [$i];
      }
    }
    
    $headerParameters .= i18n ( "colUrgency" ) . ' : ';
    foreach ( $urgDisplayed as $urg ) {
      $headerParameters .= $urg->name . ', ';
    }
    $headerParameters = substr ( $headerParameters, 0, - 2 );
    
    if (in_array ( 'undefined', $paramUrgency )) {
      $headerParameters .= ', ' . i18n ( 'undefinedUrgency' );
    }
  }
  
  if (! empty ( $paramCriticality )) {
    $cri = new Criticality ();
    $criticality = $cri->getSqlElementsFromCriteria ( null, false, null, 'id asc' );
    
    $criDisplayed = array();
    for($i = 0; $i < count ( $criticality ); $i ++) {
      if (in_array ( $i + 1, $paramCriticality )) {
        $criDisplayed [] = $criticality [$i];
      }
    }
    
    $headerParameters .= i18n ( "colCriticality" ) . ' : ';
    foreach ( $criDisplayed as $cri ) {
      $headerParameters .= $cri->name . ', ';
    }
    $headerParameters = substr ( $headerParameters, 0, - 2 );
    
    if (in_array ( 'undefined', $paramCriticality )) {
      $headerParameters .= ', ' . i18n ( 'undefinedCriticality' );
    }
  }

  include "header.php";
}

$where = getAccesRestrictionClause ( 'Requirement', false );

if ($paramProject != "") {
  $where .= " and idProject in " . getVisibleProjectsList ( false, $paramProject );
}
if ($paramProduct != "") {
  $where .= " and idProduct=" . Sql::fmtId ( $paramProduct );
}
if ($paramVersion != "") {
  $where .= " and idVersion=" . Sql::fmtId ( $paramVersion );
}
$filterByUrgency = false;
if (! empty ( $paramUrgency ) and $paramUrgency [0] != 'undefined') {
  $filterByUrgency = true;
  $where .= " and idUrgency in (";
  foreach ( $paramUrgency as $idDisplayedUrgency ) {
    if ($idDisplayedUrgency == 'undefined')
      continue;
      $where .= $idDisplayedUrgency . ', ';
  }
  $where = substr ( $where, 0, - 2 ); // To remove the last comma and space
  $where .= ")";
}
if ($filterByUrgency and in_array ( 'undefined', $paramUrgency )) {
  $where .= " or idUrgency is null";
} else if (in_array ( 'undefined', $paramUrgency )) {
  $where .= " and idUrgency is null";
} else if ($filterByUrgency) {
  $where .= " and idUrgency is not null";
}
$filterByCriticality = false;
if (! empty ( $paramCriticality) and $paramCriticality[0] != 'undefined') {
  $filterByCriticality= true;
  $where .= " and idCriticality in (";
  foreach ( $paramCriticality as $idDisplayedCriticality) {
    if ($idDisplayedCriticality== 'undefined')
      continue;
      $where .= $idDisplayedCriticality. ', ';
  }
  $where = substr ( $where, 0, - 2 ); // To remove the last comma and space
  $where .= ")";
}
if ($filterByCriticality and in_array ( 'undefined', $paramCriticality)) {
  $where .= " or idCriticality is null";
} else if (in_array ( 'undefined', $paramCriticality)) {
  $where .= " and idCriticality is null";
} else if ($filterByCriticality) {
  $where .= " and idCriticality is not null";
}

$startDate = '';
$endDate = '';
if ($paramVersion != '') {
  $pe = new Version ();
  $pe = SqlElement::getSingleSqlElementFromCriteria ( 'Version', array('id' => $paramVersion) );
  if ((($pe->initialStartDate != '') or ($pe->plannedStartDate != '')) and (($pe->initialEndDate != '') or ($pe->plannedEndDate != ''))) {
    if ($pe->initialStartDate != '') {
      $startDate = $pe->initialStartDate;
    } else {
      $startDate = $pe->plannedStartDate;
    }
    if ($pe->initialEndDate != '') {
      $endDate = $pe->initialEndDate;
    } else {
      $endDate = $pe->plannedEndDate;
    }
  } else {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n ( 'wrongDate' );
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
} else if ($paramProject != '') {
  $pe = new PlanningElement ();
  $pe = SqlElement::getSingleSqlElementFromCriteria ( 'PlanningElement', array('refType' => 'Project', 'refId' => $paramProject) );
  if ((($pe->validatedStartDate != '') or ($pe->plannedStartDate != '')) and (($pe->validatedEndDate != '') or ($pe->plannedEndDate != ''))) {
    if ($pe->validatedStartDate != '') {
      $startDate = $pe->validatedStartDate;
    } else {
      $startDate = $pe->plannedStartDate;
    }
    if ($pe->validatedEndDate != '') {
      $endDate = $pe->validatedEndDate;
    } else {
      $endDate = $pe->plannedEndDate;
    }
  } else {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n ( 'wrongDate' );
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
  }
} else {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo i18n ( 'messageNoData', array(i18n ( 'Project' )) );
  echo i18n ( 'messageNoData', array(i18n ( 'Version' )) );
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit;
}
$order = "";
// echo $where;
$req = new Requirement ();
$lstReqNew = $req->getSqlElementsFromCriteria ( null, false, $where, $order );
$nbReq = 0;
foreach ( $lstReqNew as $t ) {
  if ($t->creationDateTime != '') {
    $nbReq = $nbReq + 1;
  }
}
$start = date_create ( $startDate );
$end = date_create ( $endDate );
$nbDay = $start->diff ( $end )->days + 1;

$perfect = array();
for($i = 1; $i <= $nbDay; $i ++) {
  $perfect [$i] = ((- $nbReq) / ($nbDay)) * $i + $nbReq;
}

$created = array();
if ($nbDay != 0) {
  for($i = 1; $i <= $nbDay; $i ++) {
    foreach ( $lstReqNew as $t ) {
      if ($t->doneDate != '') {
        $startReq = strtotime ( $t->doneDate );
        if ($startReq < (strtotime ( $startDate ) + ($i * 24 * 60 * 60)) and $t->doneDate != '') {
          $nbReq = $nbReq - 1;
          $t->doneDate = '';
        }
      }
    }
    $created [$i] = $nbReq;
  }
} else {
  echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
  echo ('invalidNbOfDay');
  echo '</div>';
  if (!empty($cronnedScript)) goto end; else exit;
}
$month = getNbMonth ( 4, true );
$arrDays = array();
if (($month [date ( 'n', strtotime ( $startDate ) ) - 1] . '/' . date ( 'Y', strtotime ( $startDate ))) == $month [date ( 'n', strtotime ( $endDate ) ) - 1] . '/' . date ( 'Y', strtotime ( $endDate ) ))
{
  for($i = 1; $i <= $nbDay; $i ++) {
    $arrDays [$i] = '';
    if ($i == 1) {
  $arrDays [1] =date ( 'd', strtotime($startDate)). '/' . $month [date ( 'n', strtotime ( $startDate ) ) - 1] . '/' . date ( 'Y', strtotime ( $startDate ) );
    } else {
      $arrDays [$i] = date ( 'd', strtotime($startDate)+ (($i-1) * 24 * 60 * 60)) . '/' . $month [date ( 'n', strtotime ( $startDate ) + (($i-1)* 24 * 60 * 60) ) - 1] . '/' . date ( 'Y', strtotime ( $startDate ) + (($i)* 24 * 60 * 60) );
    }if ($i == $nbDay){
      $arrDays [$i] = date ( 'd', strtotime($endDate) ). '/' . $month [date ( 'n', strtotime ( $endDate ) ) - 1] . '/' . date ( 'Y', strtotime ( $endDate ) );
    }
  }
}else {
for($i = 1; $i <= $nbDay; $i ++) {
  $arrDays [$i] = '';
  if ($i == 1) {
    $arrDays [1] = date ( 'd', strtotime($startDate)). '/' .$month [date ( 'n', strtotime ( $startDate ) ) - 1] . '/' . date ( 'Y', strtotime ( $startDate ) );
  } else if (date ( 'm', strtotime ( $startDate ) + ($i * 24 * 60 * 60) ) == '01' and (date ( 'd', strtotime ( $startDate ) + ($i * 24 * 60 * 60) ) == '01')) {
    $arrDays [$i] = date ( 'd', strtotime($startDate)+ (($i) * 24 * 60 * 60)) . '/' . $month [date ( 'n', strtotime ( $startDate ) + (($i)* 24 * 60 * 60) ) - 1] . '/' . date ( 'Y', strtotime ( $startDate ) + (($i)* 24 * 60 * 60) );
  }
  else if (date ( 'd', strtotime ( $startDate ) + ($i * 24 * 60 * 60) ) == '01') {
    $arrDays [$i] = date ( 'd', strtotime($startDate)+ (($i) * 24 * 60 * 60)) . '/' . $month [date ( 'n', strtotime ( $startDate ) + (($i)* 24 * 60 * 60) ) - 1] . '/' . date ( 'Y', strtotime ( $startDate ) + (($i)* 24 * 60 * 60) );
  }
  if ($i == $nbDay){
    $arrDays [$i] = date ( 'd', strtotime($endDate) ). '/' . $month [date ( 'n', strtotime ( $endDate ) ) - 1] . '/' . date ( 'Y', strtotime ( $endDate ) );
  }
}
}
// Render graph
// pGrapg standard inclusions
if (! testGraphEnabled ()) {
  return;
}

$dataSet = new pData ();
$dataSet->addPoints($created,"created");
$dataSet->addPoints($perfect,"perfect");
$dataSet->addPoints($arrDays,"days");
$dataSet->setSerieDescription("created",i18n ( "requirementLeft" )."  ");
$dataSet->setSerieDescription("perfect",i18n ( "idealNbOfRequirement" )."  ");
$dataSet->setSerieOnAxis("created",0);
$dataSet->setSerieOnAxis("perfect",0);
$serieSettings = array("R"=>200,"G"=>100,"B"=>100,"Alpha"=>80);
$dataSet->setPalette("created",$serieSettings);
$serieSettings = array("R"=>000,"G"=>200,"B"=>100,"Alpha"=>80);
$dataSet->setPalette("perfect",$serieSettings);
$dataSet->setAbscissa("days");

// Initialise the graph
//$dataSet->setPalette("days",array("R"=>100,"G"=>100,"B"=>200));
$width=1000;
$legendWidth=100;
$height=400;
$legendHeight=100;
$graph = new pImage($width+$legendWidth, $height,$dataSet);
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8));

/* Draw the background */
$graph->Antialias = FALSE;

/* Add a border to the picture */
$settings = array("R"=>240, "G"=>240, "B"=>240, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
$graph->drawRoundedRectangle(5,5,$width+$legendWidth-8,$height-5,5,$settings);
$graph->drawRectangle(0,0,$width+$legendWidth-1,$height-1,array("R"=>150,"G"=>150,"B"=>150));

$graph->setGraphArea(60,20,$width-100,$height-80);
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>10));
$formatGrid=array("SkippedGridTicks"=>0,
    "Mode"=>SCALE_MODE_START0, "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>true,
    "LabelRotation"=>60, "GridR"=>230,"GridG"=>230,"GridB"=>230);
$graph->drawScale($formatGrid);

//$graph->drawGrid ();
$dataSet->setSerieDrawable("created",true);
$dataSet->setSerieDrawable("perfect",true);
//$dataSet->setSerieDrawable("days",true);

// Draw the line graph
//$graph->drawLineGraph ();
if ($nbDay < 30){
	$graph->drawPlotGraph ();
}
// Draw the area between points
//$graph->drawArea ( $dataSet->GetData (), "created", "perfect", 127, 127, 127 );
$graph->drawAreaChart();

// Finish the graph
$graph->drawLegend($width - 90,35,array("R"=>240,"G"=>240,"B"=>240));


$imgName = getGraphImgName ( "Curve_of_Requirements" );
$graph->render ( $imgName );
echo '<table width="95%" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />';
echo '</td></tr></table>';

end:
