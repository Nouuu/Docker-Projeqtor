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
scriptLog('   ->/tool/saveTestCaseRun.php');
// Get the info
if (! array_key_exists('testCaseRunMode',$_REQUEST)) {
  throwError('testCaseRunMode parameter not found in REQUEST');
}
$mode=($_REQUEST['testCaseRunMode']);

if (! array_key_exists('testCaseRunId',$_REQUEST)) {
  throwError('testCaseRunId parameter not found in REQUEST');
}
$id=($_REQUEST['testCaseRunId']);

if (! array_key_exists('testCaseRunTestSession',$_REQUEST)) {
  throwError('testCaseRunTestSession parameter not found in REQUEST');
}
$session=($_REQUEST['testCaseRunTestSession']);

//Krowry
$testCaseList = null;
if ($mode=='add') {
	if (! array_key_exists('testCaseRunTestCaseList',$_REQUEST)) {
	  throwError('testCaseRunTestCaseList parameter not found in REQUEST');
	}
	$testCaseList=($_REQUEST['testCaseRunTestCaseList']);
}

$testCase = null;
if ($mode=='edit'){
  if (! array_key_exists('testCaseRunTestCase',$_REQUEST)) {
    throwError('testCaseRunTestCase parameter not found in REQUEST');
  }
  $testCase=($_REQUEST['testCaseRunTestCase']);
}
//gautier #1716
if(RequestHandler::isCodeSet('testCaseRunResult')){
  $resultTcr = RequestHandler::getValue('testCaseRunResult');
}

if (! array_key_exists('testCaseRunComment',$_REQUEST)) {
  throwError('testCaseRunComment parameter not found in REQUEST');
}
$comment=($_REQUEST['testCaseRunComment']);

$status=1;
if ( array_key_exists('testCaseRunStatus',$_REQUEST)) {
  $status=($_REQUEST['testCaseRunStatus']);
}

$ticket=null;
if (array_key_exists('testCaseRunTicket',$_REQUEST)) {
  $ticket=($_REQUEST['testCaseRunTicket']);
}


$allowDuplicate=false;
if ($mode=='add'){
  if (array_key_exists('testCaseRunAllowDuplicate',$_REQUEST)) {
    $allowDuplicate=true;
  }
}

$sortOrder=0;
if (array_key_exists('dialogTestCaseRunSortOrder',$_REQUEST)) {
  $sortOrder=($_REQUEST['dialogTestCaseRunSortOrder']);
  Security::checkValidNumeric($sortOrder);
}

$arrayTestCase=array();
if ($mode=='add') {
	$id='';
	if (is_array($testCaseList)) {
	  $arrayTestCase=$testCaseList;
	} else {
	  $arrayTestCase[]=$testCaseList;
	}
} else {
	$arrayTestCase[]=$testCase;
}
if (count($arrayTestCase)>10) {
	projeqtor_set_time_limit(300);
}
Sql::beginTransaction();
$result="";
$sortOrder-=10;
foreach($arrayTestCase as $testCaseId) {
  $testCaseRun=new TestCaseRun($id);
  $testCaseRun->idTestCase=$testCaseId;
  $testCaseRun->idTestSession=$session;
  $testCaseRun->idTicket=$ticket;
  $sortOrder+=10;
  $testCaseRun->sortOrder=$sortOrder;
  $testCaseRun->result=$resultTcr;
  $testCaseRun->comment=$comment;
  if ($testCaseRun->idRunStatus!=$status) {
    $testCaseRun->idRunStatus=$status;
    if ($id) {
      $testCaseRun->statusDateTime=date('Y-m-d H:i:s');
    }
  }
  $save=true;
  if ($mode=='add' and !$allowDuplicate) {
  	$crit=array('idTestCase'=>$testCaseId,'idTestSession'=>$session);
  	$lst=$testCaseRun->getSqlElementsFromCriteria($crit);
  	if (count($lst)>0) {
      $save=false;
  		if (! $result) {
        $result='<b>' . i18n('messageInvalidControls') . '</b><br/><br/>' . i18n('errorDuplicateTestCase');
        $result .= '<input type="hidden" id="lastSaveId" value="" />';
        $result .= '<input type="hidden" id="lastOperation" value="control" />';
        $result .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
  		}
  	}
  } 
  if ($save) {
    $res=$testCaseRun->save($allowDuplicate);
	  if (!$result) {
	    $result=$res;
	  } else if (stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
	    if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	      $deb=stripos($res,'#');
	      $fin=stripos($res,' ',$deb);
	      $resId=substr($res,$deb, $fin-$deb);
	      $deb=stripos($result,'#');
	      $fin=stripos($result,' ',$deb);
	      $result=substr($result, 0, $fin).','.$resId.substr($result,$fin);
	    } else {
	      $result=$res;
	    } 
	  }
  }
}
displayLastOperationStatus($result);

?>