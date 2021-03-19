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
scriptLog('   ->/tool/saveResourceCapacity.php');

$refType= RequestHandler::getValue('refType');
$refId= RequestHandler::getId('refId');
$month= trim(RequestHandler::getValue('month'));
if(strlen($month)==1){
  $month = '0'.$month;
}
$value= RequestHandler::getValue('value');
if($value=='noVal')$value = null;
$interventionCap = SqlElement::getSingleSqlElementFromCriteria('InterventionCapacity', array('refType'=>$refType,'refId'=>$refId,'month'=>$month)); 
if(! $interventionCap->id) {
  $interventionCap = new InterventionCapacity();
  $interventionCap->fte = $value;
  $interventionCap->refId = $refId;
  $interventionCap->refType = $refType;
  $interventionCap->month = $month;
}else{
  $interventionCap->fte = $value;
}
$interventionCap->save();
?>