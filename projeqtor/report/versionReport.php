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
//echo 'versionReport.php';

if (! isset($includedReport)) {
  include("../external/pChart/pData.class");  
  include("../external/pChart/pChart.class");  
  
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
  
  $paramResponsible='';
  if (array_key_exists('responsible',$_REQUEST)) {
    $paramResponsible=trim($_REQUEST['responsible']);
	$paramResponsible = Security::checkValidId($paramResponsible); // only allow digits
  };
  
  $paramDoneVersion=false;
  if (array_key_exists('showDoneVersions',$_REQUEST)) {
    $paramDoneVersion=true;
  };
  
  $paramOtherVersion=false;
  if (array_key_exists('otherVersions',$_REQUEST)) {
    $paramOtherVersion=true;
  };
  
  $user=getSessionUser();
  
  // Header
  $headerParameters="";
  if ($paramProject!="") {
    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
  }
  if ($paramTicketType!="") {
    $headerParameters.= i18n("colIdTicketType") . ' : ' . htmlEncode(SqlList::getNameFromId('TicketType', $paramTicketType)) . '<br/>';
  }
  if ($paramResponsible!="") {
    $headerParameters.= i18n("colResponsible") . ' : ' . htmlEncode(SqlList::getNameFromId('Resource', $paramResponsible)) . '<br/>';
  }
  if ($paramDoneVersion!="") {
    $headerParameters.= i18n("colShowDoneVersions") . ' : ' . i18n('displayYes') . '<br/>';
  }
  if ($paramOtherVersion!="") {
    $headerParameters.= i18n("colOtherVersions") . ' : ' . i18n('displayYes') . '<br/>';
  }
  include "header.php";
}

$where=getAccesRestrictionClause('Ticket',false);
if ($paramProject!="") {
  $where.=" and idProject='" . Sql::fmtId($paramProject) . "'";
}
if ($paramTicketType!="") {
  $where.=" and idTicketType='" . Sql::fmtId($paramTicketType) . "'";
}
if ($paramResponsible!="") {
  $where.=" and idResource='" . Sql::fmtId($paramResponsible) . "'";
}

$order="";

$ticket=new Ticket();
$lstTicket=$ticket->getSqlElementsFromCriteria(null,false, $where, $order);

if ($paramProject) {
	$lstVersion=array();
	$vp=new VersionProject();
	$tmpList=$vp->getSqlElementsFromCriteria(array('idProject'=>$paramProject));
	foreach ($tmpList as $vp) {
		$vers=new Version($vp->idVersion);
		if (! $vers->idle) {
			$lstVersion[$vp->idVersion]=$vers->name;
		}
	}
	if (! $paramDoneVersion) {
	  $lstVersionNotDone=SqlList::getListWithCrit('Version',array('isEis'=>'0'),'name',null,true);
	  $lstVersion=array_intersect($lstVersion,$lstVersionNotDone);
	}
} else {
  if ($paramDoneVersion) {
	  $lstVersion=SqlList::getList('Version','name',null,true);
  } else {
    $lstVersion=SqlList::getListWithCrit('Version',array('isEis'=>'0'),'name',null,true);
  }
  
}

asort($lstVersion);
$lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';


if ($paramTicketType!="") {
	$lstType=array($paramTicketType=>SqlList::getNameFromId('TicketType', $paramTicketType));
} else {
  $lstType=SqlList::getList('TicketType');
}

$arrType=array();
foreach($lstType as $code=>$name) {
  $arrType[$code]=array('name'=>$name,'count'=>0,'estimated'=>0,'real'=>0,'left'=>0);
}
$version=array();
foreach($lstVersion as $code=>$name) {
  $version[$code]=$arrType;
}
$version['0']=$arrType;

if (count($lstType)) {
  $medWidth=floor(65/count($lstType));
} else {
  $medWidth="65";
}

foreach ($lstTicket as $t) {
	$ticket=new Ticket($t->id);
	$vers=($t->idTargetProductVersion)?$t->idTargetProductVersion:'0';
  if (isset($version[$vers][$t->idTicketType])) {
	  $version[$vers][$t->idTicketType]['count']+=1;
    $version[$vers][$t->idTicketType]['estimated']+=$ticket->WorkElement->plannedWork;
    $version[$vers][$t->idTicketType]['real']+=$ticket->WorkElement->realWork;
    $version[$vers][$t->idTicketType]['left']+=$ticket->WorkElement->leftWork;
  }
  if ($paramOtherVersion) {
  	//$ot=new OtherVersion();
  	//$crit=array('refType'=>'Ticket', 'refId'=>$t->id, 'scope'=>'TargetVersion');
  	//$otList=$ot->getSqlElementsFromCriteria($crit);
  	foreach ($ticket->_OtherTargetProductVersion as $ot) {
  		$vers=($ot->idVersion)?$ot->idVersion:'0';
  	  if (isset($version[$vers][$t->idTicketType])) {
		    $version[$vers][$t->idTicketType]['count']+=1;
		    $version[$vers][$t->idTicketType]['estimated']+=$ticket->WorkElement->plannedWork;
		    $version[$vers][$t->idTicketType]['real']+=$ticket->WorkElement->realWork;
		    $version[$vers][$t->idTicketType]['left']+=$ticket->WorkElement->leftWork;
		  }  		
  	}
  }
  
}

if (checkNoData($lstTicket)) if (!empty($cronnedScript)) goto end; else exit;
  
// title
echo '<table width="95%" align="center">';
echo '<tr><td class="reportTableHeader" rowspan="2" colspan="2">' . i18n('Version') . '</td>';
echo '<td colspan="' . (count($lstType)+1) . '" class="reportTableHeader">' . i18n('TicketType') . '</td>';
echo '</tr><tr>';
foreach ($lstType as $type) {
  echo '<td class="reportTableColumnHeader">' . $type . '</td>';
}
echo '<td class="reportTableHeader" >' . i18n('sum') . '</td>';
echo '</tr>';

$arrSum=$arrType;
foreach ($version as $idVersion=>$arrayVers) {
  echo '<tr><td style="font-size:25%;">&nbsp;</td></tr>';
  echo '<tr>';
  echo '<td class="reportTableLineHeader" style="width:10%;" rowspan="4">' . $lstVersion[$idVersion] . '</td>'; 
  // count
  $arrLines=array('count','estimated','real','left');
  foreach ($arrLines as $val) {
  	$sum=0;
  	if ($val!='count') {echo '<tr>';}
    echo '<td class="reportTableLineHeader" style="width:10%;" >' . i18n($val) . '</td>';
    foreach ($arrayVers as $idType=>$arrayType) {
  	  echo '<td class="reportTableData" style="width:' . $medWidth . '%;">' . $arrayType[$val] . '</td>';
  	  $sum+=$arrayType[$val];
    }
    echo '<td class="reportTableData" style="width:' . $medWidth . '%;">' . $sum . '</td></tr>';
  }  
}  
  
echo '</table>';
echo '<br/>';

end:
