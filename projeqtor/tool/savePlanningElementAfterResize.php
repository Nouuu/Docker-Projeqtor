<?php
use PhpOffice\PhpSpreadsheet\Shared\Date;
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

/** ============================================================================
 * Save some information about planning columns status.
 */
require_once "../tool/projeqtor.php";

$id=trim((RequestHandler::isCodeSet('id'))?RequestHandler::getValue('id'):'');
$obj=trim((RequestHandler::isCodeSet('object'))?RequestHandler::getValue('object'):'');
$idObj=trim((RequestHandler::isCodeSet('idObj'))?RequestHandler::getValue('idObj'):'');
$startDate=trim((RequestHandler::isCodeSet('startDate'))?strtotime(RequestHandler::getValue('startDate')):'');
$endDate=trim((RequestHandler::isCodeSet('endDate'))?strtotime(RequestHandler::getValue('endDate')):'');
$user=getSessionUser();

$newStartDate = date('Y-m-d',$startDate);
$newEndDate = date('Y-m-d',$endDate);



$object=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array("id"=>$id ,"refType"=>$obj, "refId"=>$idObj));
Sql::beginTransaction();

$pm=SqlList::getFieldFromId('PlanningMode', $object->idPlanningMode, "code");
if ($pm=="START") {
  $object->validatedStartDate=$newStartDate;
  if ($object->validatedEndDate) $object->validatedEndDate=$newEndDate;
}
if ($pm=="ALAP") {
  $object->validatedEndDate=$newEndDate;
  if ($object->validatedStartDate) $object->validatedStartDate=$newStartDate;
}
if ($pm=="FDUR") {
  $object->validatedDuration=workDayDiffDates($newStartDate, $newEndDate);
  if ($object->validatedStartDate) $object->validatedStartDate=$newStartDate;
  if ($object->validatedEndDate) $object->validatedEndDate=$newEndDate;
}
if ($pm=="REGUL" or $pm=="QUART" or $pm=="HALF" or $pm=="FULL") {
  $object->validatedStartDate=$newStartDate;
  $object->validatedEndDate=$newEndDate;
}
$object->plannedStartDate=$newStartDate;
$object->plannedEndDate=$newEndDate;
$object->plannedDuration=workDayDiffDates($newStartDate, $newEndDate);

$result=$object->save();

Sql::commitTransaction();
?>