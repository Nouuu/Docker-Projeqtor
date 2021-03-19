<?php
/*
 *	@author: qCazelles 
 */

include_once '../tool/projeqtor.php';
include_once "../tool/jsonFunctions.php";

if (! isset($includedReport)) {
  include("../external/pChart2/class/pData.class.php");
  include("../external/pChart2/class/pDraw.class.php");
  include("../external/pChart2/class/pImage.class.php");
	
	$paramYear='';
	if (array_key_exists('yearSpinner',$_REQUEST)) {
		$paramYear=$_REQUEST['yearSpinner'];
		$paramYear=Security::checkValidYear($paramYear);
	};
	
	$paramProject='';
	if (array_key_exists('idProject',$_REQUEST)) {
		$paramProject=trim($_REQUEST['idProject']);
		Security::checkValidId($paramProject);
	}
	
	$paramTicketType='';
	if (array_key_exists('idTicketType',$_REQUEST)) {
		$paramTicketType=trim($_REQUEST['idTicketType']);
		$paramTicketType = Security::checkValidId($paramTicketType); // only allow digits
	};
	
	$paramProduct='';
	if (array_key_exists('idProduct',$_REQUEST)) {
		$paramProduct=trim($_REQUEST['idProduct']);
		$paramProduct = Security::checkValidId($paramProduct); // only allow digits
	};
	
	if (array_key_exists('nbOfDays',$_REQUEST)) {
		$paramNbOfDays=trim($_REQUEST['nbOfDays']);
	}
	else {
		$paramNbOfDays=30;
	}
	
	$paramPriorities=array();
	if (array_key_exists('priorities',$_REQUEST)) {
		foreach ($_REQUEST['priorities'] as $idPriority => $boolean) {
			$paramPriorities[] = $idPriority;
		}
	}

	// Header
	$headerParameters="";
	$headerParameters= i18n("numberOfDays") . ' : ' . htmlEncode($paramNbOfDays) . '<br/>';
	
	if ($paramProject!="") {
		$headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
	}
	
	if ($paramTicketType!="") {
		$headerParameters.= i18n("colIdTicketType") . ' : ' . SqlList::getNameFromId('TicketType', $paramTicketType) . '<br/>';
	}
	
	if ($paramProduct!="") {
		$headerParameters.= i18n("colIdProduct") . ' : ' . htmlEncode(SqlList::getNameFromId('Product', $paramProduct)) . '<br/>';
	}

	
	//qCazelles : GRAPH TICKETS - COPY THAT IN EACH REPORT FILE
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
	//END OF THAT
	include "header.php";
}

$where=getAccesRestrictionClause('Ticket',false);
// Adapt clause on filter
$arrayFilter=jsonGetFilterArray('Report_Ticket', false);
if (count($arrayFilter)>0) {
  $obj=new Ticket();
  $querySelect="";
  $queryFrom="";
  $queryOrderBy="";
  $idTab=0;
  jsonBuildWhereCriteria($querySelect,$queryFrom,$where,$queryOrderBy,$idTab,$arrayFilter,$obj);
}

if ($paramProject!="") {
	$where.=" and idProject in " .  getVisibleProjectsList(false, $paramProject);
}
if ($paramTicketType!="") {
	$where.=" and idTicketType='" . Sql::fmtId($paramTicketType) . "'";
}
if ($paramProduct!="") {
	$where.=" and idProduct=".Sql::fmtId($paramProduct);
}

$filterByPriority = false;
if (!empty($paramPriorities) and $paramPriorities[0] != 'undefined') {
	$filterByPriority = true;
	$where.=" and idPriority in (";
	foreach ($paramPriorities as $idDisplayedPriority) {
		if ($idDisplayedPriority== 'undefined') continue;
		$where.=$idDisplayedPriority.', ';
	}
	$where = substr($where, 0, -2); //To remove the last comma and space
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

$whereClosed=$where." and idStatus in (";

$lstStatusClosed = SqlList::getListWithCrit('Status', array('setDoneStatus' => '1'), 'id');
foreach ($lstStatusClosed as $s) {
	$whereClosed .= $s.', ';
}
$whereClosed=substr($whereClosed,0,-2); //To remove the last comma and space
$whereClosed.=') ';


//Date by number of days in the past
$prevDate = time() - ($paramNbOfDays * 24 * 60 * 60);

$where.=" and creationDateTime>='".date('Y-m-d', $prevDate)."' ";

$order="";
//echo $where;
$ticket=new Ticket();
$lstTicketNew=$ticket->getSqlElementsFromCriteria(null,false, $where, $order);
$lstTicketclosed=$ticket->getSqlElementsFromCriteria(null,false, $whereClosed, $order);

$month=getArrayMonth(4, true);

$created = array();
$closed = array();
$arrDays = array();
$arrDays = array();
for($i = 1; $i <= $paramNbOfDays; $i ++) {
  $created [$i] = 0;
  $closed [$i] = 0;
  $arrDays [$i] = '';
  if ($paramNbOfDays <= 45){
    $arrDays [$i] = date ( 'd', $prevDate + ($i * 24 * 60 * 60)) . ' ' . $month [date ( 'n', $prevDate + ($i * 24 * 60 * 60) ) - 1] . ' ' . date ( 'Y', $prevDate + ($i * 24 * 60 * 60));
  }else{
  if ($i == 1) {
    $arrDays [1] = $month [date ( 'n', $prevDate ) - 1] . date ( 'Y', $prevDate );
  } else if (date ( 'd', $prevDate + ($i * 24 * 60 * 60) ) == '01' and date ( 'm', $prevDate + ($i * 24 * 60 * 60) ) == '01'){
    $arrDays [$i] = $month [date ( 'n', $prevDate + ($i * 24 * 60 * 60) ) - 1] . date ( 'Y', $prevDate + ($i * 24 * 60 * 60) );
  } else if (date ( 'd', $prevDate + ($i * 24 * 60 * 60) ) == '01') {
    $arrDays [$i] = $month [date ( 'n', $prevDate + ($i * 24 * 60 * 60) ) - 1];
  }
  }
}

foreach ($lstTicketNew as $t) {
	if (strtotime($t->creationDateTime) > $prevDate) {		
		$i = ceil((strtotime($t->creationDateTime) - $prevDate) / (24 * 60 * 60));
		if (isset($created[$i])) {
  		$created[$i]+=1;
  		for ($j = $i+1; $j <= $paramNbOfDays; $j++) {
  			$created[$j]+=1;
  		}
		}
	}
}

foreach ($lstTicketclosed as $t) {
	if (strtotime($t->doneDateTime) > $prevDate) {
		$i = ceil((strtotime($t->doneDateTime) - $prevDate) / (24 * 60 * 60));
		if (isset($closed[$i])) {
  		$closed[$i]+=1;
  		for ($j = $i+1; $j <= $paramNbOfDays; $j++) {
  			$closed[$j]+=1;
  		}
	  }
	}
}

for ($i = 1; $i <= $paramNbOfDays; $i++) {
	if ($created[$i]==0) {
		$created[$i]=0;
	}
	if ($closed[$i]==0) {
		$closed[$i]=0;
	}
}

// Render graph
// pGrapg standard inclusions
if (! testGraphEnabled()) { return;}

if (checkNoData(array_merge($created,$closed)))  if (!empty($cronnedScript)) goto end; else exit;

$dataSet=new pData;

$dataSet->addPoints($created,"created");
$dataSet->setSerieDescription("created",i18n("created"));
$dataSet->setSerieOnAxis("created",0);
$dataSet->addPoints($closed,"closed");
$dataSet->setSerieDescription("closed",i18n("done"));
$dataSet->setSerieOnAxis("closed",0);
$dataSet->addPoints($arrDays,"days");
$dataSet->setAbscissa("days");

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
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>10));

/* title */
$graph->setFontProperties(array("FontName"=>getFontLocation("verdana"),"FontSize"=>8,"R"=>100,"G"=>100,"B"=>100));
$graph->drawLegend($width-10,17,array("Mode"=>LEGEND_VERTICAL, "Family"=>LEGEND_FAMILY_BOX ,
    "R"=>255,"G"=>255,"B"=>255,"Alpha"=>100,
    "FontR"=>55,"FontG"=>55,"FontB"=>55,
    "Margin"=>5));

/* Draw the scale */
$graph->setGraphArea(60,50,$width-20,$height-$legendHeight);
$formatGrid=array("Mode"=>SCALE_MODE_START0, "GridTicks"=>0,
    "DrawYLines"=>array(0), "DrawXLines"=>true,"Pos"=>SCALE_POS_LEFTRIGHT,
    "LabelRotation"=>90, "GridR"=>200,"GridG"=>200,"GridB"=>200);
$graph->drawScale($formatGrid);
$graph->drawAreaChart();
$graph->Antialias = TRUE;
$graph->drawLineChart();
$graph->drawPlotChart();

// Draw the line graph
$imgName=getGraphImgName("ticketOpenClosedReport");
$graph->render($imgName);
echo '<table width="95%" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />';
echo '</td></tr></table>';

end:
