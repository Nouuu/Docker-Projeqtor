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
 * Save filter from User to Session to be able to restore it
 * Retores it if cancel is set
 * Cleans it if clean is set
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


// Get the filter info
$cancel=false;
if (array_key_exists('cancel',$_REQUEST)) {
  $cancel=true;
}
$clean=false;
if (array_key_exists('clean',$_REQUEST)) {
  $clean=true;
}
$valid=false;
if (array_key_exists('valid',$_REQUEST)) {
  $valid=true;
}
$default=false;
if (array_key_exists('default',$_REQUEST)) {
  $default=true;
}

if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];
if ($filterObjectClass!='Planning' and $filterObjectClass!='GlobalPlanning' and $filterObjectClass!='VersionsPlanning' and $filterObjectClass!='ResourcePlanning' and substr($filterObjectClass,0,7)!='Report_') Security::checkValidClass($filterObjectClass);
else if (substr($filterObjectClass,0,7)=='Report_') Security::checkValidClass(substr($filterObjectClass,7));

$name="";
if (array_key_exists('filterName',$_REQUEST)) {
  $name=$_REQUEST['filterName'];
  $name=htmlspecialchars($name,ENT_QUOTES,'UTF-8');
}

$filterName='stockFilter' . $filterObjectClass;
if ($cancel) {
  if (! $comboDetail) {
    if (sessionValueExists($filterName)){
      $user->_arrayFilters[$filterObjectClass]= getSessionValue($filterName);
	    setSessionUser($user);
	  } else {
	    if (array_key_exists($filterObjectClass, $user->_arrayFilters)) {
	      unset($user->_arrayFilters[$filterObjectClass]);
	      setSessionUser($user);
	    }
	  }
  } else {
    if (sessionValueExists($filterName.'_Detail')){
      $user->_arrayFiltersDetail[$filterObjectClass]= getSessionValue($filterName.'_Detail');
      setSessionUser($user);
    } else {
      if (array_key_exists($filterObjectClass, $user->_arrayFiltersDetail)) {
        unset($user->_arrayFiltersDetail[$filterObjectClass]);
        setSessionUser($user);
      }
    }
  }
} 
if ($clean or $cancel or $valid) {
	if ($comboDetail) {
    if (sessionValueExists($filterName)) {
      unsetSessionValue($filterName);
    }
	} else {
	  if (sessionValueExists($filterName.'_Detail')){
      unsetSessionValue($filterName.'_Detail');
    }
	}
}
if ( ! $clean and ! $cancel and !$valid) {
	if (! $comboDetail) {
	  if (array_key_exists($filterObjectClass,$user->_arrayFilters)) {
	   setSessionValue($filterName,$user->_arrayFilters[$filterObjectClass]);
	  } else {
	    setSessionValue($filterName, array());
	  }
	} else {
    if (array_key_exists($filterObjectClass,$user->_arrayFiltersDetail)) {
      setSessionValue($filterName.'_Detail',$user->_arrayFiltersDetail[$filterObjectClass]);
    } else {
      setSessionValue($filterName.'_Detail', array());
    }
  }
	
}

if ($valid or $cancel) {
  $user->_arrayFilters[$filterObjectClass . "FilterName"]=$name;
  setSessionUser($user);
}

?>