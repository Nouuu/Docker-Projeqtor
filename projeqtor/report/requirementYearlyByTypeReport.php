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
include_once('../tool/formatter.php');
include("../external/pChart2/class/pData.class.php");
include("../external/pChart2/class/pDraw.class.php");
include("../external/pChart2/class/pImage.class.php");

// set parameters
$paramProject=trim(RequestHandler::getId('idProject',false));
$paramProduct=trim(RequestHandler::getId('idProduct',false));
$paramVersion=trim(RequestHandler::getId('idVersion',false));
$paramYear=trim(RequestHandler::getYear('yearSpinner'));
$paramMonth=trim(RequestHandler::getMonth('monthSpinner'));
$paramResponsible=trim(RequestHandler::getId('responsible',false));
$paramRequestor=trim(RequestHandler::getId('requestor',false));
$paramIssuer=trim(RequestHandler::getId('issuer',false));

$headerParameters = "";

$periodType='year';
$periodValue=$paramYear;

// Header
$headerParameters="";
if ($paramProject!="") {
	$headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($periodType=='year' or $periodType=='month' or $periodType=='week') {
	$headerParameters.= i18n("year") . ' : ' . $paramYear . '<br/>';
}
if ($periodType=='month') {
	$headerParameters.= i18n("month") . ' : ' . $paramMonth . '<br/>';
}
if ( $periodType=='week') {
	$headerParameters.= i18n("week") . ' : ' . $paramWeek . '<br/>';
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

$paramPriorities=array();
if (array_key_exists('priorities',$_REQUEST)) {
	foreach ($_REQUEST['priorities'] as $idPriority => $boolean) {
		$paramPriorities[] = $idPriority;
	}
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

$user=getSessionUser();
$periodType = 'year';
$periodValue = $paramYear;


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
// Header END

// Set Filter by Type
$lst=SqlList::getList('RequirementType');

$includedReport = true;

// Set Table And graph view From requirementCumulatedAnualReport.php
$cptBoucle=0;
$cptBoucleMax=count($lst);

Foreach ($lst as $code=>$name) {
  echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
  echo "$name" . '<br/>';
  echo '</td></tr>';
  echo '<tr><td>&nbsp;</td></tr>';
  echo '<tr><td></td></tr>';
  echo '</table>';
  $paramRequirementType=$code;
  include "requirementCumulatedAnualReport.php";
  echo '<br/>';
  $cptBoucle++;
  if ($cptBoucle<$cptBoucleMax) {
    echo '</page><page pageset="old">';
  }
}
?>