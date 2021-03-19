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

if (! array_key_exists('dialogTenderCriteriaCallForTenderId',$_REQUEST)) {
  throwError('dialogTenderCriteriaCallForTenderId parameter not found in REQUEST');
}
$tenderId=$_REQUEST['dialogTenderCriteriaCallForTenderId'];
Security::checkValidId($tenderId);

if (! array_key_exists('dialogTenderCriteriaId',$_REQUEST)) {
  throwError('dialogTenderCriteriaId parameter not found in REQUEST');
}
$criteriaId=$_REQUEST['dialogTenderCriteriaId'];
Security::checkValidId($criteriaId);

if (! array_key_exists('dialogTenderCriteriaName',$_REQUEST)) {
  throwError('dialogTenderCriteriaName parameter not found in REQUEST');
}
$criteriaName=$_REQUEST['dialogTenderCriteriaName'];

if (! array_key_exists('dialogTenderCriteriaMaxValue',$_REQUEST)) {
  throwError('dialogTenderCriteriaMaxValue parameter not found in REQUEST');
}
$criteriaMaxValue=$_REQUEST['dialogTenderCriteriaMaxValue'];

if (! array_key_exists('dialogTenderCriteriaCoef',$_REQUEST)) {
  throwError('dialogTenderCriteriaCoef parameter not found in REQUEST');
}
$criteriaCoef=$_REQUEST['dialogTenderCriteriaCoef'];

Sql::beginTransaction();
// get the modifications (from request)
$criteria=new TenderEvaluationCriteria($criteriaId);
$criteria->idCallForTender=$tenderId;
$criteria->criteriaName=$criteriaName;
$criteria->criteriaMaxValue=$criteriaMaxValue;
$criteria->criteriaCoef=$criteriaCoef;

$result=$criteria->save();

// Message of correct saving
displayLastOperationStatus($result);
?>