<?php
/*
 *	@author: qCazelles 
 */
require_once "../tool/projeqtor.php";

if (! array_key_exists('versionCompatibilityObjectClass',$_REQUEST)) {
	throwError('versionCompatibilityObjectClass parameter not found in REQUEST');
}
$objectClass=$_REQUEST['versionCompatibilityObjectClass'];
Security::checkValidClass($objectClass);

if (! array_key_exists('versionCompatibilityObjectId',$_REQUEST)) {
	throwError('versionCompatibilityObjectId parameter not found in REQUEST');
}
$objectId=$_REQUEST['versionCompatibilityObjectId'];
Security::checkValidId($objectId);

if (! array_key_exists('versionCompatibilityListClass',$_REQUEST)) {
	throwError('versionCompatibilityListClass parameter not found in REQUEST');
}
$listClass=$_REQUEST['versionCompatibilityListClass'];
Security::checkValidClass($listClass);

if (! array_key_exists('versionCompatibilityListId',$_REQUEST)) {
	throwError('versionCompatibilityListId parameter not found in REQUEST');
}
$listId=$_REQUEST['versionCompatibilityListId'];

$comment="";
if (array_key_exists('versionCompatibilityComment',$_REQUEST)) {
	$comment=$_REQUEST['versionCompatibilityComment'];
}

$arrayId=array();
if (is_array($listId)) {
	$arrayId=$listId;
} else {
	$arrayId[]=$listId;
}
Sql::beginTransaction();

$result="";
// get the modifications (from request)
foreach ($arrayId as $id) {
	$str=new VersionCompatibility();
	$str->idVersionA=$objectId;
	$str->idVersionB=$id;
	$str->comment=$comment;
	$str->idUser=$user->id;
	$str->creationDate=date("Y-m-d");
	$res=$str->save();
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
// Message of correct saving
displayLastOperationStatus($result);
?>