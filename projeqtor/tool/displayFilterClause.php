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
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

$user=getSessionUser();

$comboDetail=false;
if (array_key_exists('comboDetail',$_REQUEST)) {
  $comboDetail=true;
}

if (! $comboDetail and ! $user->_arrayFilters) {
  $user->_arrayFilters=array();
} else if ($comboDetail and ! $user->_arrayFiltersDetail) {
  $user->_arrayFiltersDetail=array();
}


if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];

// Get existing filter info
if (!$comboDetail and array_key_exists($filterObjectClass,$user->_arrayFilters)) {
  $filterArray=$user->_arrayFilters[$filterObjectClass];
} else if ($comboDetail and array_key_exists($filterObjectClass,$user->_arrayFiltersDetail)) {
  $filterArray=$user->_arrayFiltersDetail[$filterObjectClass];
} else {
  $filterArray=array();
}

$name="";
if (! $comboDetail and array_key_exists($filterObjectClass . "FilterName", $user->_arrayFilters)) {
  $name=$user->_arrayFilters[$filterObjectClass . "FilterName"];
} else if ($comboDetail and array_key_exists($filterObjectClass . "FilterName", $user->_arrayFiltersDetail)) {
  $name=$user->_arrayFiltersDetail[$filterObjectClass . "FilterName"];
}

//ADD qCazelles - Dynamic filter - Ticket #78
//When removing filter criterias, the first one can not be defined by an OR operator
if (!empty($filterArray)) {
	$filterArray=array_values($filterArray);
	//CHANGE qCazelles - Ticket 165
	//Old
// 	if (isset($filterArray[0]['orOperator']) and $filterArray[0]['orOperator']=='1') {
// 		$filterArray[0]['orOperator']='0';
		
// 		if (! $comboDetail) {
// 			$user->_arrayFilters[$filterObjectClass]=$filterArray;
// 			$user->_arrayFilters[$filterObjectClass . "FilterName"]=$name;
// 		} else {
// 			$user->_arrayFiltersDetail[$filterObjectClass]=$filterArray;
// 			$user->_arrayFiltersDetail[$filterObjectClass . "FilterName"]=$name;
// 		}
// 		setSessionUser($user);
// 	}
	//New
	$index=0;
	foreach ($filterArray as $key => $filter) {
	  if (!isset($filter['hidden']) or $filter['hidden']=='0') {
	    $index=$key;
	    break;
	  }
	}
	if (isset($filterArray[$index]['orOperator']) and $filterArray[$index]['orOperator']=='1') {
	  $filterArray[$index]['orOperator']='0';
	  
	  if (! $comboDetail) {
	    $user->_arrayFilters[$filterObjectClass]=$filterArray;
	    $user->_arrayFilters[$filterObjectClass . "FilterName"]=$name;
	  } else {
	    $user->_arrayFiltersDetail[$filterObjectClass]=$filterArray;
	    $user->_arrayFiltersDetail[$filterObjectClass . "FilterName"]=$name;
	  }
	  setSessionUser($user);
	}
	//END CHANGE qCazelles - Ticket 165
}
//END ADD qCazelles - Dynamic filter - Ticket #78

htmlDisplayFilterCriteria($filterArray,$name); 

?>