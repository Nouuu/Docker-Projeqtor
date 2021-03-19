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
include_once '../tool/formatter.php';
//echo 'versionReport.php';

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
  $paramProject=Security::checkValidId($paramProject); // only allow digits
};
  
$paramProduct='';
if (array_key_exists('idProduct',$_REQUEST)) {
  $paramProduct=trim($_REQUEST['idProduct']);
  $paramProduct=Security::checkValidId($paramProduct); // only allow digits
};
$paramVersion='';
if (array_key_exists('idVersion',$_REQUEST)) {
  $paramVersion=trim($_REQUEST['idVersion']);
  $paramVersion=Security::checkValidId($paramVersion); // only allow digits
};
$paramDetail=false;
if (array_key_exists('showDetail',$_REQUEST)) {
  $paramDetail=trim($_REQUEST['showDetail']); // no need to filter as value is only used in boolean comparison.
}

$user=getSessionUser();
  
  // Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramProduct!="") {
  $headerParameters.= i18n("colIdProduct") . ' : ' . htmlEncode(SqlList::getNameFromId('Product', $paramProduct)) . '<br/>';
}
if ($paramVersion!="") {
  $headerParameters.= i18n("colVersion") . ' : ' . htmlEncode(SqlList::getNameFromId('Version', $paramVersion)) . '<br/>';
}
include "header.php";

$where=getAccesRestrictionClause('TestCase',false);

$order="";

if ($paramProject) {
  $lstProject=array($paramProject=>SqlList::getNameFromId('Project',$paramProject));
  $where.=" and idProject=".Sql::fmtId($paramProject);
} else {
  $lstProject=SqlList::getList('Project','name',null,true);
  $lstProject[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramProduct) {
  $lstProduct=array($paramProduct=>SqlList::getNameFromId('Product',$paramProduct));
  $where.=" and idProduct=".Sql::fmtId($paramProduct);
} else {
  $lstProduct=SqlList::getList('Product','name',null,true);
  $lstProduct[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramVersion) {
  $lstVersion=array($paramVersion=>SqlList::getNameFromId('Version',$paramVersion));
  $where.=" and idVersion=".Sql::fmtId($paramVersion);
} else {
  $lstVersion=SqlList::getList('Version','name',null,true);
  $lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';
}

$lstType=SqlList::getList('TestCaseType','name',null,true);

$tc=new TestCase();
$lst=$tc->getSqlElementsFromCriteria(null, false, $where,'idProject, idProduct, idVersion, id');

if (checkNoData($lst)) if (!empty($cronnedScript)) goto end; else exit;

echo '<table style="width:' . ((isset($outMode) and $outMode=='pdf')?'90':'95') . '%" align="center">';
echo '<tr>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colIdProject') . '</td>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colIdProduct') . '</td>';
echo '<td class="reportTableHeader" style="width:10%" rowspan="2" >' . i18n('colIdVersion') . '</td>';
echo '<td class="reportTableHeader" style="width:9%" rowspan="2" >' . i18n('colType') . '</td>';
echo '<td class="reportTableHeader" style="width:40%" colspan="2" rowspan="2" >' . i18n('TestCase') . '</td>';
echo '<td class="reportTableHeader" style="width:25%" colspan="5" >' .  i18n('TestSession') . " / " . i18n('sectionProgress') . '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountTotal') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountPlanned') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountPassed') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountBlocked') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colCountFailed') . '</td>';
echo '</tr>';
$sumTotal=0;
$sumPlanned=0;
$sumPassed=0;
$sumBlocked=0;
$sumFailed=0;
$cpt=0;
$sumReal='';
  
if ($paramDetail) {
  echo '<tr><td colspan="10" style="font-size:3px;">&nbsp;</td></tr>';
}
foreach ($lst as $tc) {
	$countTotal=0;
	$countPlanned=0;
	$countPassed=0;
	$countBlocked=0;
	$countFailed=0;
	$tcr=new TestCaseRun();
  $crit=array('idTestCase'=>$tc->id);
  $lstTcr=$tcr->getSqlElementsFromCriteria($crit,true, false, 'idTestSession');
  $inClause='(0';
  foreach($lstTcr as $tcr) {
  	$countTotal+=1;
  	if ($tcr->idRunStatus==1) $countPlanned+=1;
  	if ($tcr->idRunStatus==2) $countPassed+=1;
  	if ($tcr->idRunStatus==3) $countFailed+=1;
  	if ($tcr->idRunStatus==4) $countBlocked+=1;
  }
  if ($tc->idTestCaseType and ! isset($lstType[$tc->idTestCaseType])) {
  	$tctype=new TestCaseType($tc->idTestCaseType);
  	$lstType[$tc->idTestCaseType]=$tctype->name;
  }
  echo '<tr>';
  echo '<td class="reportTableData" style="width:8%">' . (($tc->idProject)?$lstProject[$tc->idProject]:'') . '</td>';
  echo '<td class="reportTableData" style="width:8%">' . (($tc->idProduct)?$lstProduct[$tc->idProduct]:'') . '</td>';
  echo '<td class="reportTableData" style="width:10%">' . (($tc->idVersion)?$lstVersion[$tc->idVersion]:'') . '</td>';
  echo '<td class="reportTableData" style="width:9%">' . (($tc->idTestCaseType)?$lstType[$tc->idTestCaseType]:'') . '</td>';
  echo '<td class="reportTableData" style="width:5%">#' . htmlEncode($tc->id) . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:35%">' . htmlEncode($tc->name) . '</td>';
  echo '<td class="reportTableData" style="width:5%">' . $countTotal . '</td>';
  echo '<td class="reportTableData" style="width:5%">' . $countPlanned . '</td>';
  echo '<td class="reportTableData" style="width:5%;' . (($countPassed and $countPassed==$countTotal)?'color:green;':'') . '">' . $countPassed . '</td>';
  echo '<td class="reportTableData" style="width:5%;' . (($countBlocked)?'color:orange;':'') . '">' . $countBlocked . '</td>';
  echo '<td class="reportTableData" style="width:5%;' . (($countFailed)?'color:red;':'') . '">' . $countFailed . '</td>';
  echo '</tr>';
  $sumTotal+=$countTotal;
  $sumPlanned+=$countPlanned;
  $sumPassed+=$countPassed;
  $sumBlocked+=$countBlocked;
  $sumFailed+=$countFailed;
  $cpt+=1;
  if ($paramDetail) {
  	if (count($lstTcr)>0) {
	  	echo '<tr><td></td><td colspan="10">';
	  	echo '<table style="width:100%">';
	  	echo '<tr>';
	  	echo '<td colspan="2" style="width:45%"></td>';
	  	echo '<td class="largeReportHeader" colspan="2" style="width:40%">' . i18n('TestSession') . '</td>';
	  	echo '<td class="largeReportHeader" colspan="2" style="width:15%">' . i18n('colResult') . '</td>';
	  	echo '</tr>';
        
      foreach ($lstTcr as $tcr) {
        echo '<tr>';
        echo '<td style="width:5%" style="text-align: center;"></td>';
        echo '<td style="width:40%""></td>';
        echo '<td class="largeReportData" style="width:5%" style="text-align: center;">' . (($tcr->idTestSession)?'#':'') . $tcr->idTestSession . '</td>';
        echo '<td class="largeReportData" style="width:35%" >' . (($tcr->idTestSession)?SqlList::getNameFromId('TestSession', $tcr->idTestSession):'') . '</td>';
          $st=new RunStatus($tcr->idRunStatus);
        echo '<td class="largeReportData" style="text-align: left;width:7%" >' . (($tcr->id)?colorNameFormatter(i18n($st->name) . '#split#' . $st->color):'') . '</td>';
        echo '<td class="largeReportData" style="text-align: center;font-size:75%;width:8%" >' . htmlFormatDate($tcr->statusDateTime, true) . '</td>';
        echo '</tr>';
      }
    }
    echo '<tr><td colspan="6" style="font-size:3px;">&nbsp;</td></tr>';
    echo '</table>';
    echo '</td></tr>';
  }
}
echo '<tr>';
echo '<td colspan="6"></td>';
echo '<td class="largeReportHeader" >' . $sumTotal . '</td>';
echo '<td class="largeReportHeader" >' . $sumPlanned . '</td>';
echo '<td class="largeReportHeader" >' . $sumPassed . '</td>';
echo '<td class="largeReportHeader" >' . $sumBlocked . '</td>';
echo '<td class="largeReportHeader" >' . $sumFailed . '</td>';
echo '</tr>';
echo '</table>';
echo '<br/>';

end:
