<?php
/*
 * @author : qCazelles
 */
require_once "../tool/projeqtor.php";

// Get the link info
if (! array_key_exists('businessFeatureObjectClass',$_REQUEST)) {
	throwError('businessFeatureObjectClass parameter not found in REQUEST');
}
$objectClass=$_REQUEST['businessFeatureObjectClass'];
Security::checkValidClass($objectClass);

if (! array_key_exists('businessFeatureObjectId',$_REQUEST)) {
	throwError('businessFeatureObjectId parameter not found in REQUEST');
}
$objectId=$_REQUEST['businessFeatureObjectId'];
Security::checkValidId($objectId);

if (! array_key_exists('businessFeatureName',$_REQUEST)) {
	throwError('businessFeatureName parameter not found in REQUEST');
}
$businessFeatureName=$_REQUEST['businessFeatureName'];

//ADD qCazelles - Business Feature (Correction) - Ticket #96
$businessFeatureId=null;
if ( array_key_exists('businessFeatureId',$_REQUEST)) {
  $businessFeatureId=$_REQUEST['businessFeatureId'];
}
//END ADD qCazelles - Business Feature (Correction) - Ticket #96

Sql::beginTransaction();
$result="";

$bf=new BusinessFeature($businessFeatureId);

$bf->name=$businessFeatureName;
$bf->idProduct=$objectId;
$bf->creationDate=date('Y-m-d');
$bf->idUser=$user->id;;

$result=$bf->save();

// Message of correct saving
displayLastOperationStatus($result);