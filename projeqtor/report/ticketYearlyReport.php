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

//echo "ticketYearlyReport.php";
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
	
  $paramProject='';
  if (array_key_exists('idProject',$_REQUEST)) {
    $paramProject=trim($_REQUEST['idProject']);
	Security::checkValidId($paramProject);
  }
  
  //ADD qCazelles - graphTickets
  $paramProduct='';
  if (array_key_exists('idProduct',$_REQUEST)) {
  	$paramProduct=trim($_REQUEST['idProduct']);
  	$paramProduct = Security::checkValidId($paramProduct); // only allow digits
  };
  
  $paramVersion='';
  if (array_key_exists('idVersion',$_REQUEST)) {
  	$paramVersion=trim($_REQUEST['idVersion']);
  	$paramVersion = Security::checkValidId($paramVersion); // only allow digits
  };
  //END ADD qCazelles - graphTickets
    
  $paramTicketType='';
  if (array_key_exists('idTicketType',$_REQUEST)) {
    $paramTicketType=trim($_REQUEST['idTicketType']);
	  $paramTicketType = Security::checkValidId($paramTicketType); // only allow digits
  };
  
  $paramRequestor='';
  if (array_key_exists('requestor',$_REQUEST)) {
    $paramRequestor=trim($_REQUEST['requestor']);
	  $paramRequestor = Security::checkValidId($paramRequestor); // only allow digits
  }
    
  $paramIssuer='';
  if (array_key_exists('issuer',$_REQUEST)) {
    $paramIssuer=trim($_REQUEST['issuer']);
	  $paramIssuer = Security::checkValidId($paramIssuer); // only allow digits
  };
  
  $paramResponsible='';
  if (array_key_exists('responsible',$_REQUEST)) {
    $paramResponsible=trim($_REQUEST['responsible']);
	  $paramResponsible = Security::checkValidId($paramResponsible); // only allow digits
  };
  
  //ADD qCazelles - graphTickets
  $paramPriorities=array();
  if (array_key_exists('priorities',$_REQUEST)) {
  	foreach ($_REQUEST['priorities'] as $idPriority => $boolean) {
  		$paramPriorities[] = $idPriority;
  	}
  }
  //END ADD qCazelles - graphTickets
  
  $user=getSessionUser();
  
  $periodType='year';
  //$periodValue=$_REQUEST['periodValue'];
  $periodValue=$paramYear;
  
  // Header
  $headerParameters="";
  if ($paramProject!="") {
    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
  }
  //ADD qCazelles - graphTickets
  if ($paramProduct!="") {
  	$headerParameters.= i18n("colIdProduct") . ' : ' . htmlEncode(SqlList::getNameFromId('Product', $paramProduct)) . '<br/>';
  }
  if ($paramVersion!="") {
  	$headerParameters.= i18n("colVersion") . ' : ' . htmlEncode(SqlList::getNameFromId('Version', $paramVersion)) . '<br/>';
  }
  //END ADD qCazelles
  if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
    $headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';  
  }
  //ADD qCazelles - Report fiscal year - Ticket #128
  if ($periodType=='year' and $paramMonth!="01") {
    if(!$paramMonth){
      $paramMonth="01";
    }
    $headerParameters.= i18n("startMonth") . ' : ' . i18n(date('F', mktime(0,0,0,$paramMonth,10))) . '<br/>';
  }
  //END ADD qCazelles - Report fiscal year - Ticket #128
  if ($periodType=='month') {
    $headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
  }
  if ( $periodType=='week') {
    $headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
  }
  if ($paramTicketType!="") {
    $headerParameters.= i18n("colIdTicketType") . ' : ' . SqlList::getNameFromId('TicketType', $paramTicketType) . '<br/>';
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
$reportContext=false;
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

//CHANGE qCazelles - Report fiscal year - Ticket #128
//ADD
if(!$paramMonth){
  $paramMonth="01";
}
if ($paramMonth<10) $paramMonth='0'.intval($paramMonth);
if ($paramMonth=="01") {
  $endMonth = "12";
} else {
  $endMonth = intval($paramMonth) - 1;
  if ($endMonth<10) $endMonth='0'.intval($endMonth);
}
$endYear = ($paramMonth=="01") ? $paramYear : $paramYear + 1;
//END ADD
//Old
// $where.=" and ( (    creationDateTime>= '" . $paramYear . "-01-01'";
// $where.="        and creationDateTime<='" . $paramYear . "-12-31' )";
// $where.="    or (    doneDateTime>= '" . $paramYear . "-01-01'";
// $where.="        and doneDateTime<='" . $paramYear . "-12-31' )";
// $where.="    or (    idleDateTime>= '" . $paramYear . "-01-01'";
// $where.="        and idleDateTime<='" . $paramYear . "-12-31' ) )";

//New
$where.=" and ( (    creationDateTime>= '" . $paramYear . "-" .$paramMonth . "-01 00:00:00'";
$where.="        and creationDateTime<='" . $endYear. "-" . $endMonth . "-31 23:59:59' )";
$where.="    or (    doneDateTime>= '" . $paramYear . "-" .$paramMonth . "-01 00:00:00'";
$where.="        and doneDateTime<='" . $endYear. "-" . $endMonth . "-31 23:59:59' )";
$where.="    or (    idleDateTime>= '" . $paramYear . "-" .$paramMonth . "-01 00:00:00'";
$where.="        and idleDateTime<='" . $endYear. "-" . $endMonth . "-31 23:59:59' ) )";
//END CHANGE qCazelles - Report fiscal year - Ticket #128

if ($paramProject!="") {
  $where.=" and idProject in " .  getVisibleProjectsList(false, $paramProject);
}
//ADD qCazelles - graphTickets
if (isset($paramProduct) and $paramProduct!="") {
	$where.=" and idProduct='" . Sql::fmtId($paramProduct) . "'";
}
if (isset($paramVersion) and $paramVersion!="") {
	$where.=" and idOriginalProductVersion='" . Sql::fmtId($paramVersion) . "'";
}
//END ADD qCazelles - graphTickets
if ($paramTicketType!="") {
  $where.=" and idTicketType='" . Sql::fmtId($paramTicketType) . "'";
}
if ($paramRequestor!="") {
  $where.=" and idContact='" . Sql::fmtId($paramRequestor) . "'";
}
if ($paramIssuer!="") {
  $where.=" and idUser='" . Sql::fmtId($paramIssuer) . "'";
}
if ($paramResponsible!="") {
  $where.=" and idResource='" . Sql::fmtId($paramResponsible) . "'";
}

//ADD qCazelles - graphTickets
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
//END ADD qCazelles - graphTickets

$order="";
//echo $where;
$ticket=new Ticket();
$lstTicket=$ticket->getSqlElementsFromCriteria(null,false, $where, $order);
$created=array();
$done=array();
$closed=array();
for ($i=1; $i<=13; $i++) {
  $created[$i]=0;
  $done[$i]=0;
  $closed[$i]=0;
}

$sumProj=array();
foreach ($lstTicket as $t) {
  //CHANGE qCazelles - Report fiscal year - Ticket #128
  if (substr($t->creationDateTime,0,4)==$paramYear) {
    $month=intval(substr($t->creationDateTime,5,2));
    if ($month>=$paramMonth) {
      $created[$month - ($paramMonth - 1)]+=1;
      $created[13]+=1;
    }
  }
  //ADD qCazelles
  else if (substr($t->creationDateTime,0,4)==$endYear) {
    $month=intval(substr($t->creationDateTime,5,2));
    if ($month<=$paramMonth) {
      $created[12 - $paramMonth + $month + 1]+=1;
      $created[13]+=1;
    }
  }
  //END ADD qCazelles
  if (substr($t->doneDateTime,0,4)==$paramYear) {
    $month=intval(substr($t->doneDateTime,5,2));
    if ($month>=$paramMonth) {
      $done[$month - ($paramMonth - 1)]+=1;
      $done[13]+=1;
    }
  }
  //ADD qCazelles
  //CHANGE qCazelles - Error on report - Ticket 153
  //Old
  //else if (substr($t->doneDateTime,0,4)==$paramYear) {
  //New
  else if (substr($t->doneDateTime,0,4)==$endYear) {
  //END CHANGE qCazelles - Error on report - Ticket 153
    $month=intval(substr($t->doneDateTime,5,2));
    $done[12 - $paramMonth + $month + 1]+=1;
    $done[13]+=1;
  }
  //END ADD qCazelles
  if (substr($t->idleDateTime,0,4)==$paramYear) {
    $month=intval(substr($t->idleDateTime,5,2));
    if ($month>=$paramMonth) {
      $closed[$month - ($paramMonth - 1)]+=1;
      $closed[13]+=1;
    }
  }
  //ADD qCazellles
  else if (substr($t->idleDateTime,0,4)==$endYear) {
    $month=intval(substr($t->idleDateTime,5,2));
    $closed[12 - $paramMonth + $month + 1]+=1;
    $closed[13]+=1;
  }
  //END ADD qCazelles
  //END CHANGE qCazelles - Report fiscal year - Ticket #128
}

if (checkNoData($lstTicket)) return;

// title
echo '<table width="95%" align="center">';
echo '<tr><td class="reportTableHeader" rowspan="2">' . i18n('Ticket') . '</td>';
//CHANGE atrancoso - Ticket #84
//Old
//echo '<td colspan="13" class="reportTableHeader">' . $periodValue . '</td>';
//New
if ($paramMonth=="01") {
  echo '<td colspan="13" class="reportTableHeader">' . $periodValue . '</td>';
} else {
  echo '<td colspan="' . (13 - $paramMonth) . '" class="reportTableHeader">' . $periodValue . '</td>';
  echo '<td colspan="' . ($paramMonth - 1) . '" class="reportTableHeader">' . ($periodValue + 1) . '</td>';
  echo '<td colspan="1" class="reportTableHeader"></td>';
}
//END CHANGE atrancoso - Ticket #84
echo '</tr><tr>';
$arrMonth=getArrayMonth(4,true);

//ADD qCazelles - Report fiscal year - Ticket #128
for ($i = 0; $i < $paramMonth - 1; $i++) {
  $val = array_shift($arrMonth);
  array_push($arrMonth, $val);
}
//END ADD qCazelles - Report fiscal year - Ticket #128

$arrMonth[13]=i18n('sum');

for ($i=1; $i<=12; $i++) {
  echo '<td class="reportTableColumnHeader">' . $arrMonth[$i-1] . '</td>';
}
echo '<td class="reportTableHeader" >' . i18n('sum') . '</td>';
echo '</tr>';

$sum=0;
for ($line=1; $line<=3; $line++) {
  if ($line==1) {
    $tab=$created;
    $caption=i18n('created');
    $serie="created";
  } else if ($line==2) {
    $tab=$done;
    $caption=i18n('done');
    $serie="done";
  } else if ($line==3) {
    $tab=$closed;
    $caption=i18n('closed');
    $serie="closed";
  }
  echo '<tr><td class="reportTableLineHeader" style="width:18%">' . $caption . '</td>';
  foreach ($tab as $id=>$val) {
    if ($id=='13') {
      echo '<td style="width:10%;" class="reportTableColumnHeader">';
    } else {
      echo '<td style="width:6%;" class="reportTableData">';
    }
    echo $val;
    echo '</td>';
  }
  
  echo '</tr>';
}
echo '</table>';
  
$createdSum=array(VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,$created[13]);
$doneSum=array(VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,$done[13]);
$closedSum=array(VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,$closed[13]);
$closed[13]=VOID;
$created[13]=VOID;
$done[13]=VOID;

// Render graph
// pGrapg standard inclusions     
if (! testGraphEnabled()) { return;}

$dataSet=new pData;
$dataSet->addPoints($created,"created");
$dataSet->setSerieDescription("created",i18n("created"));
$dataSet->setSerieOnAxis("created",0);
$serieSettings = array("R"=>200,"G"=>100,"B"=>100,"Alpha"=>80);
$dataSet->setPalette("created",$serieSettings);
$dataSet->addPoints($done,"done");
$dataSet->setSerieDescription("done",i18n("done"));
$dataSet->setSerieOnAxis("done",0);
$serieSettings = array("R"=>100,"G"=>200,"B"=>100,"Alpha"=>80);
$dataSet->setPalette("done",$serieSettings);
$dataSet->addPoints($closed,"closed");
$dataSet->setSerieDescription("closed",i18n("closed"));
$dataSet->setSerieOnAxis("closed",0);
$serieSettings = array("R"=>100,"G"=>100,"B"=>200,"Alpha"=>80);
$dataSet->setPalette("closed",$serieSettings);


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

$dataSet->addPoints($createdSum,"createdSum");
$dataSet->setSerieOnAxis("createdSum",1);
$dataSet->addPoints($doneSum,"doneSum");
$dataSet->setSerieOnAxis("doneSum",1);
$dataSet->addPoints($closedSum,"closedSum");
$dataSet->setSerieOnAxis("closedSum",1);
$dataSet->setAxisName(0,i18n("sum"));
$dataSet->setAxisPosition(1,AXIS_POSITION_RIGHT);
$serieSettings = array("R"=>200,"G"=>100,"B"=>100,"Alpha"=>80);
$dataSet->setPalette("createdSum",$serieSettings);
$serieSettings = array("R"=>100,"G"=>200,"B"=>100,"Alpha"=>80);
$dataSet->setPalette("doneSum",$serieSettings);
$serieSettings = array("R"=>100,"G"=>100,"B"=>200,"Alpha"=>80);
$dataSet->setPalette("closedSum",$serieSettings);

//$formatGrid=array("LabelRotation"=>90,"GridTicks"=>0 ,"AutoAxisLabels"=>FALSE,"Mode"=>SCALE_MODE_START0);
$graph->drawScale($formatGrid);

$dataSet->setSerieDrawable("created",true);
$dataSet->setSerieDrawable("done",true);
$dataSet->setSerieDrawable("closed",true);
$dataSet->setSerieDrawable("createdSum",false);
$dataSet->setSerieDrawable("doneSum",false);
$dataSet->setSerieDrawable("closedSum",false);
//$graph->drawZoneChart("created", "done");
$graph->drawAreaChart(array("DisplayColor"=>DISPLAY_AUTO));
$graph->drawPlotChart();
$dataSet->setSerieDrawable("created",false);
$dataSet->setSerieDrawable("done",false);
$dataSet->setSerieDrawable("closed",false);
$dataSet->setSerieDrawable("createdSum",true);
$dataSet->setSerieDrawable("doneSum",true);
$dataSet->setSerieDrawable("closedSum",true);

$graph->drawBarChart();

$imgName=getGraphImgName("ticketYearlyReport");
$graph->render($imgName);
echo '<table width="95%" style="margin-top:20px;" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />'; 
echo '</td></tr></table>';