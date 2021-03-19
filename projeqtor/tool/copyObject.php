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
 * Copy an object as a new one (of the same class) : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

// Get the object from session(last status before change)
$obj=SqlElement::getCurrentObject(null,null,true,false);

/* @var SqlElement $obj */
if (! is_object($obj)) {
  throwError('last saved object is not a real object');
}

// Get the object class from request
if (! array_key_exists('objectClassName',$_REQUEST)) {
  throwError('className parameter not found in REQUEST');
}
$className=$_REQUEST['objectClassName'];

// compare expected class with object class
if ($className!=get_class($obj)) {
  throwError('last saved object (' . get_class($obj) . ') is not of the expected class (' . $className . ').'); 
}
Sql::beginTransaction();
// copy from existing object
$newObj=$obj->copy();
// save the new object to session (modified status)
$result=$newObj->_copyResult;
unset($newObj->_copyResult);

// Message of correct saving
$status=displayLastOperationStatus($result);
if ($status == "OK") {
  if (! array_key_exists('comboDetail', $_REQUEST)) {
    SqlElement::setCurrentObject($newObj);
  }
}

?>