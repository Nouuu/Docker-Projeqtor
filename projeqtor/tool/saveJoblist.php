<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Julien PAPASIAN
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
 * Save a joblistdefinition line : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
if (!array_key_exists('joblistDefinitionId', $_REQUEST)) {
    throwError('joblistDefinitionId parameter not found in REQUEST');
}
$joblistDefinitionId = trim($_REQUEST['joblistDefinitionId']);
/* if (!array_key_exists('joblistId', $_REQUEST)) {
  throwError('joblistId parameter not found in REQUEST');
  }
  $joblistId = trim($_REQUEST['joblistId']); */
if (!array_key_exists('joblistObjectClass', $_REQUEST)) {
    throwError('joblistObjectClass parameter not found in REQUEST');
}
$joblistObjectClass = $_REQUEST['joblistObjectClass'];
if (!array_key_exists('joblistObjectId', $_REQUEST)) {
    throwError('joblistObjectId parameter not found in REQUEST');
}
$joblistObjectId = trim($_REQUEST['joblistObjectId']);

$joblistDefinition = new JoblistDefinition($joblistDefinitionId);
$cl = new Job();
$linesTmp = $cl->getSqlElementsFromCriteria(array('refType' => $joblistObjectClass, 'refId' => $joblistObjectId, 'idJoblistDefinition' => $joblistDefinitionId));
$linesVal = array();
foreach ($linesTmp as $line) {
    $linesVal[$line->idJobDefinition] = $line;
}
Sql::beginTransaction();
$changed = false;
$status = 'NO_CHANGE';

foreach ($joblistDefinition->_JobDefinition as $line) {
    if (isset($linesVal[$line->id])) {
        $valLine = $linesVal[$line->id];
    } else {
        $valLine = new Job();
    }
    $valLine->refType = $joblistObjectClass;
    $valLine->refId = $joblistObjectId;
    $valLine->idJoblistDefinition = $joblistDefinitionId;
    $valLine->idJobDefinition = $line->id;

    //$valLine->checkTime=date('Y-m-d H:i:s');
    $checkedCpt = 0;
    $checkedCptChanged = 0;

    $checkName = "job_" . $line->id."_check";
    if (isset($_REQUEST[$checkName])) {
        $checkedCpt++;
        if (!$valLine->value) {
            $checkedCptChanged++;
            $valLine->checkTime = date('Y-m-d H:i:s');
        }
        $valLine->value = 1;
    } else {
        if ($valLine->value) {
            $checkedCptChanged++;
        }
        $valLine->value = 0;
    }

    if (isset($_REQUEST['job_' . $line->id.'_idUser'])) {
        $idUser = $_REQUEST['job_' . $line->id.'_idUser'];
        if ($valLine->idUser != $idUser) {
            $checkedCptChanged++;
        }
        $valLine->idUser = $idUser;
    } else {
        if (!$valLine->idUser) {
            $checkedCptChanged++;
            $valLine->idUser = getSessionUser()->id;
        }
    }

    if (isset($_REQUEST['job_' . $line->id.'_creationDate'])) {
        $creationDate = $_REQUEST['job_' . $line->id.'_creationDate'];
        if ($valLine->creationDate != $creationDate) {
            $checkedCptChanged++;
        }
        $valLine->creationDate = $creationDate;
    }

    $cmtName = 'job_' . $line->id . '_comment';
    if (isset($_REQUEST[$cmtName])) {
        $cmt = trim($_REQUEST[$cmtName]);
        if ($valLine->comment != $cmt) {
            $checkedCptChanged++;
        }
        $valLine->comment = $cmt;
        if ($cmt)
            $checkedCpt+=1;
    }
    $resultLine = $valLine->save();
    if ($resultLine) {
        $statusLine = getLastOperationStatus($resultLine);
        if ($statusLine == "NO_CHANGE") {
            // Nothing
        } else if ($statusLine == "ERROR") {
            $result = $resultLine;
            $status = $statusLine;
        } else if ($status == 'NO_CHANGE') { // Explicitly, $statusLine=="OK"
            $result = $resultLine;
            $status = $statusLine;
        }
    }
}
if ($status == "OK") {
    $result = i18n('Joblist') . ' ' . i18n('resultUpdated') . ' (' . i18n($joblistObjectClass) . ' #' . $joblistObjectId . ')';
    $result .= '<input type="hidden" id="lastSaveId" value="' . $joblistObjectId . '" />';
    $result .= '<input type="hidden" id="lastOperation" value="update" />';
    $result .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
    $result .= '<input type="hidden" id="joblistUpdated" value="true" />';
}
// Message of correct saving
if (!isset($included)) {
    displayLastOperationStatus($result);
} else {
    if ($status == "OK" or $status == "NO_CHANGE" or $status == "INCOMPLETE") {
        Sql::commitTransaction();
    } else {
        Sql::rollbackTransaction();
    }
}
?>
