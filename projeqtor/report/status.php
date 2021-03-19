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
//echo "status.php";

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
  
  $showIdle=false;
  if (array_key_exists('showIdle',$_REQUEST)) {
    $showIdle=true;
  }
    
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

$includedReport=true;
$arrType=array('Ticket', 'Activity', 'Milestone');
$cptLoop=0;
$cptLoopMax=count($arrType);
foreach ($arrType as $refType) {
  echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
  echo i18n($refType);
  echo '</td></tr>';
  echo '<tr><td>&nbsp;</td></tr>';
  echo '<tr><td></td></tr>';
  echo '</table>';
  include "statusDetail.php";
  $cptLoop++;
  if ($cptLoop<$cptLoopMax) {
    echo '</page><page>';
  }
}
?>
