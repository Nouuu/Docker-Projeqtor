<?php
// ADD BY Marc TABARY - 2017-02-23 - REMOVE OBJECTS LINKED BY ID TO MAIN OBJET

/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$idLinkObject=null;
if (array_key_exists('idLinkObject',$_REQUEST)) {
  $idLinkObject=$_REQUEST['idLinkObject'];
}
$idLinkObject=trim($idLinkObject);
if ($idLinkObject=='') {
  $idLinkObject=null;
} 
if ($idLinkObject==null) {
  throwError('idLinkObject parameter not found in REQUEST');
}

if (! array_key_exists('mainObjectClassName',$_REQUEST)) {
  throwError('mainObjectClassName parameter not found in REQUEST');
}
$idMainObjectClassName = 'id'.$_REQUEST['mainObjectClassName'];

if (! array_key_exists('linkObjectClassName',$_REQUEST)) {
  throwError('linkObjectClassName parameter not found in REQUEST');
}
$linkObjectClassName = $_REQUEST['linkObjectClassName'];

if ($linkObjectClassName=='Resource') $linkObjectClassName='ResourceAll';
Sql::beginTransaction();
$obj=new $linkObjectClassName($idLinkObject);
$obj->$idMainObjectClassName=null;
$result=$obj->save();

// Message of correct saving
displayLastOperationStatus($result);
// END ADD BY Marc TABARY - 2017-02-23 - REMOVE OBJECTS LINKED BY ID TO MAIN OBJET

?>