<?php
/*
 * @author: atrancoso ticket #84
 */
include_once '../tool/projeqtor.php';

if (! isset ( $includedReport )) {
  //include ("../external/pChart/pData.class");
  //include ("../external/pChart/pChart.class");
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
  ;
  
  $paramVersion = '';
  if (array_key_exists ( 'idVersion', $_REQUEST )) {
    $paramVersion = trim ( $_REQUEST ['idVersion'] );
    $paramVersion = Security::checkValidId ( $paramVersion ); // only allow digits
  }
  ;
  if (array_key_exists ( 'nbOfDays', $_REQUEST )) {
    $paramNbOfDays = trim ( $_REQUEST ['nbOfDays'] );
  } else {
    $paramNbOfDays = 30;
  }
  if ($paramNbOfDays == ''){
   //TODO cast an error 
  }
  
  $paramPriorities = array();
  if (array_key_exists ( 'priorities', $_REQUEST )) {
    foreach ( $_REQUEST ['priorities'] as $idPriority => $boolean ) {
      $paramPriorities [] = $idPriority;
    }
  }
  
  // Header
  $headerParameters = "";
  $headerParameters = i18n ( "numberOfDays" ) . ' : ' . htmlEncode ( $paramNbOfDays ) . '<br/>';
  
  if ($paramVersion != "") {
    $headerParameters .= i18n ( "colVersion" ) . ' : ' . htmlEncode ( SqlList::getNameFromId ( 'Version', $paramVersion ) ) . '<br/>';
  }
  
  if ($paramProject != "") {
    $headerParameters .= i18n ( "colIdProject" ) . ' : ' . htmlEncode ( SqlList::getNameFromId ( 'Project', $paramProject ) ) . '<br/>';
  }
  
  if ($paramProduct != "") {
    $headerParameters .= i18n ( "colIdProduct" ) . ' : ' . htmlEncode ( SqlList::getNameFromId ( 'Product', $paramProduct ) ) . '<br/>';
  }
  
  if (! empty ( $paramPriorities )) {
    $priority = new Priority ();
    $priorities = $priority->getSqlElementsFromCriteria ( null, false, null, 'id asc' );
    
    $prioritiesDisplayed = array();
    for($i = 0; $i < count ( $priorities ); $i ++) {
      if (in_array ( $i + 1, $paramPriorities )) {
        $prioritiesDisplayed [] = $priorities [$i];
      }
    }
    
    $headerParameters .= i18n ( "colPriority" ) . ' : ';
    foreach ( $prioritiesDisplayed as $priority ) {
      $headerParameters .= $priority->name . ', ';
    }
    $headerParameters = substr ( $headerParameters, 0, - 2 );
    
    if (in_array ( 'undefined', $paramPriorities )) {
      $headerParameters .= ', ' . i18n ( 'undefinedPriority' );
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

$filterByPriority = false;
if (! empty ( $paramPriorities ) and $paramPriorities [0] != 'undefined') {
  $filterByPriority = true;
  $where .= " and idPriority in (";
  foreach ( $paramPriorities as $idDisplayedPriority ) {
    if ($idDisplayedPriority == 'undefined')
      continue;
    $where .= $idDisplayedPriority . ', ';
  }
  $where = substr ( $where, 0, - 2 ); // To remove the last comma and space
  $where .= ")";
}
if ($filterByPriority and in_array ( 'undefined', $paramPriorities )) {
  $where .= " or idPriority is null";
} else if (in_array ( 'undefined', $paramPriorities )) {
  $where .= " and idPriority is null";
} else if ($filterByPriority) {
  $where .= " and idPriority is not null";
}
// Date by number of days in the past
$prevDate = time () - ($paramNbOfDays * 24 * 60 * 60);

$whereClosed = $where . " and idStatus in (";

$lstStatusClosed = SqlList::getListWithCrit ( 'Status', array('setIdleStatus' => '1'), 'id' );
foreach ( $lstStatusClosed as $s ) {
  $whereClosed .= $s . ', ';
}
$whereClosed = substr ( $whereClosed, 0, - 2 ); // To remove the last comma and space
$whereClosed .= ') ';



$whereDone = $where;
$whereDone .= " and doneDate >= '" . date ( 'Y-m-d', $prevDate ) . "' ";


$where .= " and creationDateTime>='" . date ( 'Y-m-d', $prevDate ) . "' ";


$order = "";
// echo $where;
$req = new Requirement ();
$lstReqNew = $req->getSqlElementsFromCriteria ( null, false, $where, $order );
$lstReqclosed = $req->getSqlElementsFromCriteria ( null, false, $whereClosed, $order );
$lstReqDone = $req->getSqlElementsFromCriteria ( null, false, $whereDone, $order );

$month = getArrayMonth ( 4, true );

$created = array();
$closed = array();
$done = array();
$arrDays = array();
for($i = 1; $i <= $paramNbOfDays; $i ++) {
  $created [$i] = 0;
  $closed [$i] = 0;
  $done [$i] = 0;
  $arrDays [$i] = '';
  if ($paramNbOfDays <= 45){
    $arrDays [$i] = date ( 'd', $prevDate + ($i * 24 * 60 * 60)) . ' ' . $month [date ( 'n', $prevDate + ($i * 24 * 60 * 60) ) - 1] . ' ' . date ( 'Y', $prevDate + ($i * 24 * 60 * 60));
  }else {
  if ($i == 1) {
    $arrDays [1] = $month [date ( 'n', $prevDate ) - 1] . date ( 'Y', $prevDate );
  } else if (date ( 'd', $prevDate + ($i * 24 * 60 * 60) ) == '01' and date ( 'm', $prevDate + ($i * 24 * 60 * 60) ) == '01'){
      $arrDays [$i] = $month [date ( 'n', $prevDate + ($i * 24 * 60 * 60) ) - 1] . date ( 'Y', $prevDate + ($i * 24 * 60 * 60) );
  } else if (date ( 'd', $prevDate + ($i * 24 * 60 * 60) ) == '01') {
    $arrDays [$i] = $month [date ( 'n', $prevDate + ($i * 24 * 60 * 60) ) - 1];
  }
  }
}

foreach ( $lstReqNew as $t ) {
  if (strtotime ( $t->creationDateTime ) > $prevDate) {
    $i = ceil ( (strtotime ( $t->creationDateTime ) - $prevDate) / (24 * 60 * 60) );
    if (isset($created [$i])) {
      $created [$i] += 1;
      for($j = $i + 1; $j <= $paramNbOfDays; $j ++) {
        $created [$j] += 1;
      }
    }
  }
}

foreach ( $lstReqclosed as $t ) {
  if (strtotime ( $t->idleDate ) > $prevDate) {
    $i = ceil ( (strtotime ( $t->idleDate ) - $prevDate) / (24 * 60 * 60) );
    if (isset($created [$i])) {
      $closed [$i] += 1;
      for($j = $i + 1; $j <= $paramNbOfDays; $j ++) {
        $closed [$j] += 1;
      }
    }
  }
}
foreach ( $lstReqDone as $t ) {
  if (strtotime ( $t->doneDate ) > $prevDate) {
    $i = ceil ( (strtotime ( $t->doneDate ) - $prevDate) / (24 * 60 * 60) );
    if (isset($created [$i])) {
      $done [$i] += 1;
      for($j = $i + 1; $j <= $paramNbOfDays; $j ++) {
        $done [$j] += 1;
      }
    }
  }
}

for($i = 1; $i <= $paramNbOfDays; $i ++) {
  if ($created [$i] == 0) {
    $created [$i] = VOID;
  }
  if ($closed [$i] == 0) {
    $closed [$i] = VOID;
  }
  if ($done [$i] == 0) {
    $done [$i] = VOID;
  }
}

// Render graph
// pGrapg standard inclusions
if (! testGraphEnabled ()) {
  return;
}

$dataSet = new pData ();

$dataSet->addPoints ( $created, "created" );
$dataSet->setSerieDescription("created",i18n("created"));
$serieSettings = array("R"=>200,"G"=>100,"B"=>100,"Alpha"=>80);
$dataSet->setPalette("created",$serieSettings);
$dataSet->setSerieOnAxis("created",0);

$dataSet->addPoints ( $closed, "closed" );
$dataSet->setSerieDescription("closed",i18n("closed"));
$serieSettings = array("R"=>100,"G"=>100,"B"=>200,"Alpha"=>80);
$dataSet->setPalette("closed",$serieSettings);
$dataSet->setSerieOnAxis("closed",0);

$dataSet->addPoints ( $done, "done" );
$dataSet->setSerieDescription("done",i18n("done"));
$serieSettings = array("R"=>100,"G"=>200,"B"=>100,"Alpha"=>80);
$dataSet->setPalette("done",$serieSettings);
$dataSet->setSerieOnAxis("done",0);

$dataSet->addPoints ( $arrDays, "days" );
$dataSet->setAbscissa("days");

// Initialise the graph
$width = 1000;
$legendWidth=100;
$height=400;
$legendHeight=100;
$graph = new pImage($width+$legendWidth, $height,$dataSet);

$graph->Antialias = FALSE;
/* Add a border to the picture */
$settings = array("R"=>240, "G"=>240, "B"=>240, "Dash"=>0, "DashR"=>0, "DashG"=>0, "DashB"=>0);
$graph->drawRoundedRectangle(5,5,$width+$legendWidth-8,$height-5,5,$settings);
$graph->drawRectangle(0,0,$width+$legendWidth-1,$height-1,array("R"=>150,"G"=>150,"B"=>150));

$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8,"R"=>100,"G"=>100,"B"=>100));

$graph->drawLegend($width+18,17,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
    "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
    "FontR"=>55,"FontG"=>55,"FontB"=>55,
    "Margin"=>5));

$graph->setGraphArea(60,20,$width-20,$height-90);
$formatGrid=array("Mode"=>SCALE_MODE_START0, "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>true,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>200,"GridG"=>200,"GridB"=>200);

$dataSet->setSerieDrawable("created",true);
$dataSet->setSerieDrawable("done",true);
$dataSet->setSerieDrawable("closed",true);
$graph->drawScale($formatGrid);
// Draw the line graph
$graph->drawAreaChart(array("DisplayColor"=>DISPLAY_AUTO));
$graph->drawPlotChart();


// Draw the area between points


//$graph->drawRightScale ( $dataSet->GetData (), $dataSet->GetDataDescription (), SCALE_START0, 0, 0, 0, true, 60, 1, true );

$imgName = getGraphImgName ( "requirement_nb_of_days" );
$graph->render ( $imgName );
echo '<table width="95%" style="margin-top:20px;" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />';
echo '</td></tr></table>';

