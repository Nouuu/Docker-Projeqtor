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

if (! array_key_exists('dialogCallForTenderSubmissionCallForTenderId',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionCallForTenderId parameter not found in REQUEST');
}
$callForTenderId=$_REQUEST['dialogCallForTenderSubmissionCallForTenderId'];
Security::checkValidId($callForTenderId);

if (! array_key_exists('dialogCallForTenderSubmissionTenderId',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionTenderId parameter not found in REQUEST');
}
$tenderId=$_REQUEST['dialogCallForTenderSubmissionTenderId'];
Security::checkValidId($tenderId);

if (! array_key_exists('dialogCallForTenderSubmissionProvider',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionProvider parameter not found in REQUEST');
}
$providerId=$_REQUEST['dialogCallForTenderSubmissionProvider'];
Security::checkValidId($providerId);

if (! array_key_exists('dialogCallForTenderSubmissionContact',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionContact parameter not found in REQUEST');
}
$contactId=$_REQUEST['dialogCallForTenderSubmissionContact'];
Security::checkValidId($contactId);

if (! array_key_exists('dialogCallForTenderSubmissionRequestDate',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionRequestDate parameter not found in REQUEST');
}
$requestDate=$_REQUEST['dialogCallForTenderSubmissionRequestDate'];
if (! array_key_exists('dialogCallForTenderSubmissionRequestTime',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionRequestTime parameter not found in REQUEST');
}
$requestTime=$_REQUEST['dialogCallForTenderSubmissionRequestTime'];
$requestDateTime=$requestDate.' '.substr($requestTime,1);
Security::checkValidDateTime($requestDateTime);

if (! array_key_exists('dialogCallForTenderSubmissionExpectedTenderDate',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionExpectedTenderDate parameter not found in REQUEST');
}
$expectedTenderDate=$_REQUEST['dialogCallForTenderSubmissionExpectedTenderDate'];
if (! array_key_exists('dialogCallForTenderSubmissionExpectedTenderTime',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionExpectedTenderTime parameter not found in REQUEST');
}
$expectedTenderTime=$_REQUEST['dialogCallForTenderSubmissionExpectedTenderTime'];
$expectedTenderDateTime=$expectedTenderDate.' '.substr($expectedTenderTime,1);
Security::checkValidDateTime($expectedTenderDateTime);

if (! array_key_exists('dialogCallForTenderSubmissionStatus',$_REQUEST)) {
  throwError('dialogCallForTenderSubmissionStatus parameter not found in REQUEST');
}
$tenderStatusId=$_REQUEST['dialogCallForTenderSubmissionStatus'];
Security::checkValidId($tenderStatusId);

Sql::beginTransaction();
// get the modifications (from request)
$tender=new Tender($tenderId);
//$callForTender=new CallForTender($callForTenderId);
$tender->idCallForTender=$callForTenderId;
$tender->idProvider=$providerId;
$tender->idContact=$contactId;
$tender->requestDateTime=$requestDateTime;
$tender->expectedTenderDateTime=$expectedTenderDateTime;
$tender->idTenderStatus=$tenderStatusId;
$tender->creationDate=date('Y-m-d');
$result=$tender->save();

// Message of correct saving
displayLastOperationStatus($result);
?>