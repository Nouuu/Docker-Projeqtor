<?php
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
* Save a note : call corresponding method in SqlElement Class
* The new values are fetched in $_REQUEST
*/
require_once "../tool/projeqtor.php";
// Get the link info
$dependencyDelay=RequestHandler::getNumeric('delayDependency',true);
$dependencyComment=RequestHandler::getValue('commentDependency',true);
$dependencyType=RequestHandler::getValue('dependencyType',false);
if (! $dependencyType) $dependencyType='E-S';
$id=RequestHandler::getId('dependencyRightClickId',true);

Sql::beginTransaction();

  $dep=new Dependency($id);
  $dep->dependencyDelay=$dependencyDelay;
  $dep->comment=$dependencyComment;
  $dep->dependencyType=$dependencyType;
  $result=$dep->save();
  displayLastOperationStatus($result);

?>