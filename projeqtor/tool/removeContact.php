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
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$objectClass=((RequestHandler::isCodeSet('objectClass'))?RequestHandler::getValue('objectClass'):'');
$selectId=((RequestHandler::isCodeSet('objectId'))?RequestHandler::getValue('objectId'):'');
$class=((RequestHandler::isCodeSet('class'))?RequestHandler::getValue('class'):'');
$newVal=((RequestHandler::isCodeSet('addVal'))?RequestHandler::getValue('addVal'):'');
$operation=((RequestHandler::isCodeSet('operation'))?RequestHandler::getValue('operation'):'');
if($objectClass=='' or $selectId=='' or $operation==''){
  return;
}
$obj= new $objectClass($selectId);
Sql::beginTransaction();
    if($class!='' ){
      if($class=='Provider'){
        $obj->idProvider='';
      }elseif ($class=='Client'){
        $obj->idClient='';
      }
      else{
        return;
      }
    }
$result=$obj->save();
displayLastOperationStatus($result);
?>