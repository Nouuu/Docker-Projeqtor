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
echo '<td class="reportTableHeader" style="width:8%"  >' . i18n('colType') . '</td>';
echo '<td class="reportTableHeader" style="width:2%"  >' . i18n('colId') . '</td>';
echo '<td class="reportTableHeader" style="width:20%" >' . i18n('TestCase') . '</td>';
echo '<td class="reportTableHeader" style="width:25%" >' .  i18n('colDescription') . '</td>';
echo '<td class="reportTableHeader" style="width:20%" >' .  i18n('colPrerequisite') . '</td>';
echo '<td class="reportTableHeader" style="width:25%" >' .  i18n('colExpectedResult') . '</td>';
echo '</tr>';
  
$product="";
$project="";
$version="";
foreach ($lst as $tc) {
	if ($tc->idProject!=$project or $tc->idProduct!=$product or $tc->idVersion!=$version) {
		$product=$tc->idProduct;
		$project=$tc->idProject;
		$version=$tc->idVersion;
		echo '<tr>';
		echo '<td class="reportTableHeader" colspan="6" style="width:100%"  >' ;
		echo '<table width="100%"><tr>';
		if ($tc->idProject) echo '<td width="34%">'.i18n('Project').' : '.$lstProject[$tc->idProject].'</td>'; 
		else echo '<td width="34%">&nbsp;</td>';
		if ($tc->idProduct) echo '<td width="33%">'.i18n('Product').' : '.$lstProduct[$tc->idProduct].'</td>';
		else echo '<td width="33%">&nbsp;</td>';
		if ($tc->idVersion) echo '<td width="33%">'.i18n('Version').' : '.$lstVersion[$tc->idVersion].'</td>';
		else echo '<td width="33%">&nbsp;</td>';
		echo '</tr></table>';
		echo '</td>';
		echo '</tr>';
	}
  echo '<tr>';
  echo '<td class="reportTableData" style="width:8%">' . (($tc->idTestCaseType)?$lstType[$tc->idTestCaseType]:'') . '</td>';
  echo '<td class="reportTableData" style="width:2%">#' . htmlEncode($tc->id) . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:20%">' . htmlEncode($tc->name) . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:25%">' . htmlEncode($tc->description,"formatted") . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:25%">' . htmlEncode($tc->prerequisite,"formatted") . '</td>';
  echo '<td class="reportTableData" style="text-align:left;width:25%">' . htmlEncode($tc->result,"formatted") . '</td>';
  echo '</tr>';
}
echo '</table>';
echo '<br/>';

end:
