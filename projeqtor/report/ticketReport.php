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
//echo 'ticketReport.php';

if (! isset($includedReport)) {
  
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

if ($periodType) {
  $start=date('Y-m-d');
  $end=date('Y-m-d');
  if ($periodType=='year') {
    
    //CHANGE qCazelles - Report fiscal year - Ticket #128    
    //Old
    //$start=$paramYear . '-01-01';
    //$end=$paramYear . '-12-31';
    //New
    $startMonth=$paramMonth;
    if ($startMonth<10) $startMonth='0'.$startMonth;
    $start=$paramYear.'-'.$startMonth.'-01';
    $endMonth=$paramMonth-1;
    if ($endMonth<1) $endMonth=12;
    if ($endMonth<10) $endMonth='0'.$endMonth;
    $endYear=$paramYear;
    if ($paramMonth!=1) $endYear++;
    $end=$endYear.'-'.$endMonth.'-'.lastDayOfMonth(intval($endMonth),$endYear);
    //END CHANGE qCazelles - Report fiscal year - Ticket #128
  } else if ($periodType=='month') {
      if ((!$paramYear and !$paramMonth) or (!$paramYear) or (!$paramMonth)) {
        echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
        if(!$paramYear and !$paramMonth){
          echo i18n('messageNoData',array(i18n('yearAndmonth'))); // TODO i18n message
        } else if(!$paramYear){
          echo i18n('messageNoData',array(i18n('year'))); // TODO i18n message
        } else if(!$paramMonth){
          echo i18n('messageNoData',array(i18n('month'))); // TODO i18n message
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
        echo i18n('messageNoData',array(i18n('yearAndweek'))); // TODO i18n message
      } else if(!$paramYear){
        echo i18n('messageNoData',array(i18n('year'))); // TODO i18n message
      } else if(!$paramWeek){
        echo i18n('messageNoData',array(i18n('week'))); // TODO i18n message
      }
      echo '</div>';
      if (!empty($cronnedScript)) goto end; else exit;
    }
    $start=date('Y-m-d', firstDayofWeek($paramWeek, $paramYear));
    $end=addDaysToDate($start,6);
  }
  //echo $start . ' - ' . $end . '<br/>';
  $start.=' 00:00:00';
  $end.=' 23:59:59';
  $where.=" and ( (    creationDateTime>= '" . $start . "'";
  $where.="        and creationDateTime<='" . $end . "' )";
  $where.="    or (    doneDateTime>= '" . $start . "'";
  $where.="        and doneDateTime<='" . $end . "' )";
  $where.="    or (    idleDateTime>= '" . $start . "'";
  $where.="        and idleDateTime<='" . $end . "') )";
}
if ($paramProject!="") {
   $where.=" and idProject in " .  getVisibleProjectsList(false, $paramProject);
}
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

$lstUrgency=SqlList::getList('Urgency');
$lstCriticality=SqlList::getList('Criticality');
$lstPriority=SqlList::getList('Priority');
$lstType=SqlList::getList('TicketType');
//$arrType=array('0'=>'');
foreach($lstType as $code=>$name) {
  $arrType[$code]=0;
}
if (count($lstType)) {
  $medWidth=floor(65/count($lstType));
} else {
  $medWidth="65";
}
$arrUrgency=array('0'=>$arrType);
foreach($lstUrgency as $code=>$name) {
  $arrUrgency[$code]=$arrType;
}
$arrCriticality=array('0'=>$arrType);
foreach($lstCriticality as $code=>$name) {
  $arrCriticality[$code]=$arrType;
}
$arrPriority=array('0'=>$arrType);
foreach($lstPriority as $code=>$name) {
  $arrPriority[$code]=$arrType;
}

// Init multi-dimension array
$created['Urgency']=$arrUrgency;
$created['Criticality']=$arrCriticality;
$created['Priority']=$arrPriority;
$done=$created;
$closed=$created;

foreach ($lstTicket as $t) {
  $urgency=($t->idUrgency==null or trim($t->idUrgency)=='')?'0':$t->idUrgency;
  $criticality=($t->idCriticality==null or trim($t->idCriticality)=='')?'0':$t->idCriticality;
  $priority=($t->idPriority==null or trim($t->idPriority)=='')?'0':$t->idPriority;
  $type=($t->idTicketType==null or trim($t->idTicketType)=='')?'0':$t->idTicketType;
  if ( (! $periodType and $t->creationDateTime) 
  or ($periodType and $t->creationDateTime>=$start and $t->creationDateTime<=$end) ) {
    if (isset($created['Urgency'][$urgency][$type])) $created['Urgency'][$urgency][$type]+=1;
    if (isset($created['Criticality'][$criticality][$type])) $created['Criticality'][$criticality][$type]+=1;
    if (isset($created['Priority'][$priority][$type])) $created['Priority'][$priority][$type]+=1;
  }
  if ( (! $periodType and $t->doneDateTime) 
  or ($periodType and $t->doneDateTime>=$start and $t->doneDateTime<=$end) ) {
    if (isset($done['Urgency'][$urgency][$type])) $done['Urgency'][$urgency][$type]+=1;
    if (isset($done['Criticality'][$criticality][$type])) $done['Criticality'][$criticality][$type]+=1;
    if (isset($done['Priority'][$priority][$type])) $done['Priority'][$priority][$type]+=1;
  }
  if ( (! $periodType and $t->idleDateTime) 
  or ($periodType and $t->idleDateTime>=$start and $t->idleDateTime<=$end) ) {  
    if (isset($closed['Urgency'][$urgency][$type])) $closed['Urgency'][$urgency][$type]+=1;
    if (isset($closed['Criticality'][$criticality][$type])) $closed['Criticality'][$criticality][$type]+=1;
    if (isset($closed['Priority'][$priority][$type])) $closed['Priority'][$priority][$type]+=1;
  }
}

if (checkNoData($lstTicket)) if (!empty($cronnedScript)) goto end; else exit;

for ($i=1; $i<=3; $i++) {
  if ($i==1) {
    $tab=$created;
    $caption=i18n('created');
  } else if ($i==2) {
    $tab=$done;
    $caption=i18n('done');
    echo"</page><page>";
  } else if ($i==3) {
    $tab=$closed;
    $caption=i18n('closed');
    echo"</page><page>";
  }
  
  // title
  echo '<table width="95%" align="center">';
  echo '<tr><td class="reportTableHeader" rowspan="2" colspan="2">' . $caption . '</td>';
  echo '<td colspan="' . (count($lstType)+1) . '" class="reportTableHeader">' . i18n('TicketType') . '</td>';
  echo '</tr><tr>';
  $arrMonth=getArrayMonth(4,true);
  foreach ($lstType as $type) {
    echo '<td class="reportTableColumnHeader">' . $type . '</td>';
  }
  echo '<td class="reportTableHeader" >' . i18n('sum') . '</td>';
  echo '</tr>';
  
  $sum=0;
  $arrTypeSum=array();
  foreach ($arrType as $cd=>$val) {
    $arrTypeSum[$cd]=0;
  }
  foreach ($tab as $codeArr=>$modeArr) {
    echo '<tr><td style="font-size:25%;">&nbsp;</td></tr>';
    foreach ($modeArr as $codeMode=>$arrType) {
      $sum=0;
      echo '<tr>';
      if ($codeMode==0) {
        echo '<td class="reportTableLineHeader" style="width:10%;" rowspan="' . count($modeArr) . '">' . i18n($codeArr) . '</td>';
        echo '<td class="reportTableLineHeader" style="width:15%" color:#808080;"><i>' . i18n('undefinedValue') .  '</i></td>';
      } else {
        echo '<td class="reportTableLineHeader">' . SqlList::getNameFromId($codeArr, $codeMode) .  '</td>';
      }
      foreach ($arrType as $codeType=>$val) {
        echo '<td class="reportTableData" style="width:' . $medWidth . '%;">' . $val . '</td>';
        $sum+=$val;
        //echo "x";
        if ($codeArr=='Urgency') {
          $arrTypeSum[$codeType]+=$val;
        }
      }
      echo '<td class="reportTableLineHeader" style="text-align:center;width:10%">' . $sum . '</td>';
      echo '</tr>';
    }
  }
  echo '<tr><td style="font-size:25%;">&nbsp;</td></tr>';
  echo '<tr><td colspan="2"></td>';
  $sum=0;
  foreach ($arrTypeSum as $codeType=>$val) {
    echo '<td class="reportTableLineHeader" style="text-align:center;">' . $val . '</td>';
    $sum+=$val;
  }
  echo '<td class="reportTableHeader">' . $sum . '</td>';
  echo '</tr>';
  echo '</table>';
  echo '<br/>';
}    

end:

return;
