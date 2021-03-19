<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

// Get the note info
if (! array_key_exists('reportId',$_REQUEST)) {
  throwError('reportId parameter not found in REQUEST');
}
$reportId=$_REQUEST['reportId'];

Sql::beginTransaction();
$item=new Favorite();
$user=getSessionUser();
$item->idUser=$user->id;
$item->scope='report';
$item->idReport=$reportId;
$item->idle=0;
$lst=$item->getSqlElementsFromCriteria(array('idUser'=>$user->id));
$item->sortOrder=count($lst)+1;
$result=$item->save();
$rpt=new Report($reportId); // validated to be numeric value in SqlElement base constructor.
$params=FavoriteParameter::returnReportParameters($rpt,true);
if (isset($params['period']) and isset($_REQUEST['periodScale']) and isset($_REQUEST['periodValue'])) {
  unset($params['period']);
  $params['periodScale']='';
  $params['periodValue']='';
}
foreach ($params as $pName=>$pValue) {
  $reqValue='';
	if (isset($_REQUEST[$pName])) {
		$reqValue=$_REQUEST[$pName];
	}
	if (trim($reqValue)!=trim($pValue)) {
		$tp=new FavoriteParameter();
		$tp->idUser=$item->idUser;
		$tp->idReport=$item->idReport;
		$tp->idFavorite=$item->id;
		$tp->parameterName=$pName;
		$tp->parameterValue=$reqValue;
		$res=$tp->save();		
	}
}

// Message of correct saving
displayLastOperationStatus($result);
?>