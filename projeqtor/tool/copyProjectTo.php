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
 * Copy an object as a new one (of the same class) : call corresponding method in SqlElement Class
 */
require_once "../tool/projeqtor.php";
projeqtor_set_time_limit(300);

// Get the object from session(last status before change)
$proj=SqlElement::getCurrentObject(null,null,true,false);
if (! is_object($proj)) {
  throwError('last saved object is not a real object');
}
// Get the object class from request

if (! array_key_exists('copyProjectToName',$_REQUEST)) {
  throwError('copyProjectToName parameter not found in REQUEST');
}
$toName=$_REQUEST['copyProjectToName'];
if (! array_key_exists('copyProjectToType',$_REQUEST)) {
  throwError('copyProjectToName parameter not found in REQUEST');
}
$toType=$_REQUEST['copyProjectToType'];

if (! array_key_exists('copyProjectToSubProject',$_REQUEST)) {
  throwError('copyProjectToSubProject parameter not found in REQUEST');
}
$toSubProject=$_REQUEST['copyProjectToSubProject'];

$copyStructure=false;
if (array_key_exists('copyProjectStructure',$_REQUEST)) {
	$copyStructure=true;
}

$copyOtherStructure=false;
if (array_key_exists('copyOtherProjectStructure',$_REQUEST)) {
  $copyOtherStructure=true;
}

$copyProjectRequirement=false;
if (array_key_exists('copyProjectRequirement',$_REQUEST)) {
  $copyProjectRequirement=true;
}
  
$copySubProjects=false;
if (array_key_exists('copySubProjects',$_REQUEST)) {
  $copySubProjects=true;
}
$copyAffectations=false;
if (array_key_exists('copyProjectAffectations',$_REQUEST)) {
  $copyAffectations=true;
}
$copyAssignments=false;
if (array_key_exists('copyProjectAssignments',$_REQUEST)) {
	$copyAssignments=true;
}

$codeProject=null;
if (array_key_exists('copyProjectToProjectCode',$_REQUEST)) {
  $codeProject=$_REQUEST['copyProjectToProjectCode'];
}

//gautier #1769
$copyToWithLinks=false;
if (array_key_exists('copyToWithLinks',$_REQUEST)) {
  $copyToWithLinks=true;
}

//Krowry #2206
$copyToWithVersionProjects=false;
if (array_key_exists('copyToWithVersionProjects',$_REQUEST)) {
  $copyToWithVersionProjects=true;
}

// ADD BY Marc TABARY - 2017-03-17 - COPY ACTIVITY PRICE WHEN COPY PROJECT
$copyToWithActivityPrice=false;
if (array_key_exists('copyToWithActivityPrice',$_REQUEST)) {
  $copyToWithActivityPrice=true;
}

$copyToWithAttachments=RequestHandler::getBoolean('copyToWithAttachments');
// END ADD BY Marc TABARY - 2017-03-17 - COPY ACTIVITY PRICE WHEN COPY PROJECT

// copy from existing object
Sql::beginTransaction();
$error=false;
PlanningElement::$_noDispatch=true;
SqlElement::$_doNotSaveLastUpdateDateTime=true;
//$newProj=copyProject($proj, $toName, $toType , $copyStructure, $copySubProjects, $copyAffectations, $copyAssignments, null);

Security::checkValidId($toType);

// CHANGE BY Marc TABARY - 2017-03-17 - COPY ACTIVITY PRICE WHEN COPY PROJECT
$proj->projectCode=$codeProject;
$newProj=$proj->copyTo('Project',
                        $toType,
                        $toName,
                        null, 
                        false, false,
                        $copyToWithAttachments,
                        $copyToWithLinks,
                        $copyAssignments,
                        false, $toSubProject, null, false,
                        $copyToWithVersionProjects,
                        $copyToWithActivityPrice);
  // Old
//$newProj=$proj->copyTo('Project',$toType,$toName,false,false, false,$copyToWithLinks,$copyAssignments,false, $toSubProject, null, false, $copyToWithVersionProjects ); // toProject
// END CHANGE BY Marc TABARY - 2017-03-17 - COPY ACTIVITY PRICE WHEN COPY PROJECT

$result=$newProj->_copyResult;
if (! stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
  $error=true;
  $result.= '<input type="hidden" id="lastSaveId" value="" />';
  $result .= '<input type="hidden" id="lastOperation" value="copy" />';
  $result .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
}
unset($newProj->_copyResult);
if(!$error) {
  $newProj->sortOrder=$newProj->ProjectPlanningElement->wbsSortable; // update sortOrder
  $newProj->save();
}
if (!$error and $copyStructure) {
  $res=PlanningElement::copyStructure($proj, $newProj, false, false, $copyToWithAttachments,$copyToWithLinks,$copyAssignments, $copyAffectations,$newProj->id,$copySubProjects,$copyToWithVersionProjects);
  if ($res!='OK') {
    $result=$res;
    $error=true;
  }
}
if (!$error and $copyOtherStructure) {
  $res=PlanningElement::copyOtherStructure($proj, $newProj, false, false, $copyToWithAttachments,$copyToWithLinks,$copyAssignments, $copyAffectations,$newProj->id,$copySubProjects,$copyToWithVersionProjects,$copyStructure);
  if ($res!='OK') {
    $result=$res;
    $error=true;
  }
}

if(!$error and !$copyStructure and !$copyOtherStructure and $copySubProjects){
  $res=PlanningElement::copyStructureProject($proj, $newProj, false, false, $copyToWithAttachments,$copyToWithLinks,$copyAssignments, $copyAffectations,$newProj->id,$copySubProjects,$copyToWithVersionProjects);
  if ($res!='OK') {
    $result=$res;
    $error=true;
  }
}

if (!$error and ($copyStructure or $copyOtherStructure or $copySubProjects)) {
  PlanningElement::copyStructureFinalize();
}
// copy affectations
if (!$error and $copyAffectations) {
  $aff=new Affectation();
  $crit=array('idProject'=>$proj->id);
  $lstAff=$aff->getSqlElementsFromCriteria($crit);
  foreach ($lstAff as $aff) {
    $critExists=array('idProject'=>$newProj->id, 'idResource'=>$aff->idResource);
    $affExists=SqlElement::getSingleSqlElementFromCriteria('Affectation', $critExists);
    if (!$affExists or !$affExists->id) {
  		$aff->id=null;
  		$aff->idProject=$newProj->id;
  		$aff->save();
    }
  }
}
if(!$error and $copyProjectRequirement){
  $req=new Requirement();
  $crit=array('idProject'=>$proj->id);
  $lstReq=$req->getSqlElementsFromCriteria($crit);
  foreach ($lstReq as $req) {
    $critExists=array('idProject'=>$newProj->id, 'id'=>$req->id);
    $reqExists=SqlElement::getSingleSqlElementFromCriteria('Requirement', $critExists);
    if ($reqExists) {
    		$copyReq=$req->copyTo('Requirement', $req->idRequirementType, $req->name, $newProj->id, false, true, true, true);
    		$res=$copyReq->_copyResult;
    		if (! stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
    		  $error=true;
    		  $result.= '<input type="hidden" id="lastSaveId" value="" />';
    		  $result .= '<input type="hidden" id="lastOperation" value="copy" />';
    		  $result .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
    		}
    		unset($copyReq->_copyResult);
    }
  }
}
// Message of correct saving
$status = displayLastOperationStatus($result);
if ($status == "OK") {
  if (! array_key_exists ( 'comboDetail', $_REQUEST )) {
    SqlElement::setCurrentObject (new Project( $newProj->id ));
  }
  User::resetAllVisibleProjects(null,getSessionUser()->id); // Will reteive visibiity for new project and sub-projects
}

?>