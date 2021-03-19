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

/** ===========================================================================
 * Save a filter : call corresponding method in SqlElement Class
 * The new values are fetched in REQUEST
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/displayFiletrList.php');
$user=getSessionUser();
$context="";
$comboDetail=false;
if (RequestHandler::isCodeSet('comboDetail')) {
  $comboDetail=true;
}

// Get the filter info
if (! RequestHandler::isCodeSet('filterObjectClass')) {
	if (isset($objectClass)) {
		$filterObjectClass=$objectClass;
		$context="directFilterList";
	} else {
    throwError('filterObjectClass parameter not found in REQUEST');
	}
} else {
  $filterObjectClass=RequestHandler::getValue('filterObjectClass');
}
if (!isset($objectClass) or !$objectClass) $objectClass=$filterObjectClass;
if ($objectClass=='Planning' or $objectClass=='GlobalPlanning' or $objectClass=='VersionsPlanning' or $objectClass=='ResourcePlanning'){
  $objectClass='Activity';
  $dontDisplay=true;
}
else if (substr($objectClass,0,7)=='Report_') $objectClass=substr($objectClass,7);
Security::checkValidClass($objectClass);
if (RequestHandler::isCodeSet('context')) $context=RequestHandler::getValue('context');

// Get existing filter info
if (! $comboDetail and array_key_exists($filterObjectClass,$user->_arrayFilters)) {
  $filterArray=$user->_arrayFilters[$filterObjectClass];
} else if ( $comboDetail and array_key_exists($filterObjectClass,$user->_arrayFiltersDetail)) {
  $filterArray=$user->_arrayFiltersDetail[$filterObjectClass];
} else {
  $filterArray=array();
}

$currentFilter="";
if (! $comboDetail and ! $user->_arrayFilters) {
  $user->_arrayFilters=array();
} else if ($comboDetail and ! $user->_arrayFiltersDetail) {
  $user->_arrayFiltersDetail=array();
}
if (! $comboDetail and array_key_exists($filterObjectClass . "FilterName",$user->_arrayFilters)) {
  $currentFilter=$user->_arrayFilters[$filterObjectClass . "FilterName"];
} else if ($comboDetail and array_key_exists($filterObjectClass . "FilterName",$user->_arrayFiltersDetail)) {
  $currentFilter=$user->_arrayFiltersDetail[$filterObjectClass . "FilterName"];
}

$flt=new Filter();
$crit=array('idUser'=> $user->id, 'refType'=>$objectClass );
$orderByFilter = "sortOrder ASC";
$filterList=$flt->getSqlElementsFromCriteria($crit,false,null,$orderByFilter);
$displayQuickFilter = RequestHandler::getValue('displayQuickFilter');
if($displayQuickFilter){
  if(isNewGui())include "../tool/displayQuickFilterList.php";
  $context = "directFilterList";
}

htmlDisplayStoredFilter($filterList,$filterObjectClass,$currentFilter, $context);

?>