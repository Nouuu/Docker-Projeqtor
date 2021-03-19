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

/** ============================================================================
 * Get the status list , dynamically, corresponding of :
 *   - a Type
 *   - an origin Status
 *   - the workflow 'allowed' status
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicListChangeStatus.php');
if (! array_key_exists('objectClass',$_REQUEST)) {
  throwError('objectClass parameter not found in REQUEST');
}
$objClass=$_REQUEST['objectClass'];
Security::checkValidClass($objClass);

if (! array_key_exists('idType',$_REQUEST)) {
  throwError('idType parameter not found in REQUEST');
}
$objIdType=$_REQUEST['idType'];
$typeClass= $objClass . 'Type';
Security::checkValidClass($typeClass);

if (! array_key_exists('idStatus',$_REQUEST)) {
  throwError('idStatus parameter not found in REQUEST');
}
$objIdStatus = $_REQUEST['idStatus'];
$table=SqlList::getList('Status','name',$objIdStatus, false );
unset($table[$objIdStatus]);

if (! array_key_exists('objectId',$_REQUEST)) {
  throwError('objectId parameter not found in REQUEST');
}
$obj = new $objClass($_REQUEST['objectId']);

$idClassType = "id".$objClass."Type";

reset($table);

$firstKey=key($table);
$firstName=current($table);

// look for workflow
if ($objIdType and $objIdStatus) {
    $profile="";
    if (sessionUserExists()) {
            $profile=getSessionUser()->getProfile($obj);
    }
    $type=new $typeClass($obj->$idClassType,true);
    if (property_exists($type,'idWorkflow') ) {
        $ws=new WorkflowStatus();
        $crit=array('idWorkflow'=>$type->idWorkflow, 'allowed'=>1, 'idProfile'=>$profile, 'idStatusFrom'=>$obj->idStatus);
        $wsList=$ws->getSqlElementsFromCriteria($crit, false);
        $compTable=array($obj->idStatus=>'ok');
        foreach ($wsList as $ws) {
                $compTable[$ws->idStatusTo]="ok";
        }
        $table=array_intersect_key($table,$compTable);
    }
    $current=new Status($obj->idStatus,true);
    if ($current->isCopyStatus and isset($firstKey)) {
            $table[$firstKey]=$firstName;
    }
} else {
    $table=array($firstKey=>$firstName);
}
?>

<select id="changeStatusId" size="14" name="changeStatusId" multiple="false"
onchange="enableWidget('dialogChangeStatusSubmit');"  ondblclick="saveChangedStatusObject();"
class="selectList" >
<?php
    foreach ($table as $stId=>$stName) {
       echo "<option value='$stId'>#".htmlEncode($stId)." - ".htmlEncode($stName)."</option>";
    }
?>
</select>
