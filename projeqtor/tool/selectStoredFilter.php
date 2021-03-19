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
scriptLog('   ->/tool/selectStoredFilter.php');
$user=getSessionUser();
$comboDetail=false;
if (array_key_exists('comboDetail',$_REQUEST)) {
  $comboDetail=true;
}

if (! $comboDetail) {
  if (! $user->_arrayFilters) {
    $user->_arrayFilters=array();
  }
} else {
  if (! $user->_arrayFiltersDetail) {
    $user->_arrayFiltersDetail=array();
  }
}

// Get the filter info
if (! array_key_exists('idFilter',$_REQUEST)) {
  throwError('idFilter parameter not found in REQUEST');
}
$idFilter=$_REQUEST['idFilter'];
if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];

$filterArray=array();
$filter=new Filter($idFilter);
$arrayDisp=array();
$arraySql=array();

// Transform FilterCriteria Object as Array
if (is_array($filter->_FilterCriteriaArray)) {
  foreach ($filter->_FilterCriteriaArray as $filterCriteria) {
    $arrayDisp["attribute"]=$filterCriteria->dispAttribute;
    $arrayDisp["operator"]=$filterCriteria->dispOperator;
    $arrayDisp["value"]=$filterCriteria->dispValue;
    $arraySql["attribute"]=$filterCriteria->sqlAttribute;
    $arraySql["operator"]=$filterCriteria->sqlOperator;
    $arraySql["value"]=$filterCriteria->sqlValue;
    //CHANGE qCazelles - Dynamic filter - Ticket #78
    //Old
    //$filterArray[]=array("disp"=>$arrayDisp,"sql"=>$arraySql);
  	//New
  	$filterArray[]=array("disp"=>$arrayDisp,"sql"=>$arraySql,"isDynamic"=>$filterCriteria->isDynamic,"orOperator"=>$filterCriteria->orOperator);
  	//END CHANGE qCazelles - Dynamic filter - Ticket #78
  }
}
//ADD qCazelles - Ticket 165
if ($comboDetail and isset($user->_arrayFiltersDetail[$filterObjectClass])) {
  foreach ($user->_arrayFiltersDetail[$filterObjectClass] as $filterCriteria) {
    if (isset($filterCriteria['hidden']) and $filterCriteria['hidden']=='1') {
      $filterArray[]=$filterCriteria;
    }
  }
}
//END ADD qCazelles - Ticket 165
if (! $comboDetail) {
  $user->_arrayFilters[$filterObjectClass]=$filterArray;
  $user->_arrayFilters[$filterObjectClass . "FilterName"]=$filter->name;
} else {
	$user->_arrayFiltersDetail[$filterObjectClass]=$filterArray;
  $user->_arrayFiltersDetail[$filterObjectClass . "FilterName"]=$filter->name;
}

if (array_key_exists('context',$_REQUEST) and $_REQUEST['context']=='directFilterList') {
  if(isNewGui())include "../tool/displayQuickFilterList.php";
	include "../tool/displayFilterList.php";
} else {
  htmlDisplayFilterCriteria($filterArray,$filter->name);
}

?>