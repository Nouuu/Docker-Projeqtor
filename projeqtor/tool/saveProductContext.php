<?php
/*
 * @author : qCazelles
 */
require_once "../tool/projeqtor.php";

// Get the link info
$objectClass=RequestHandler::getClass('productContextObjectClass',true);
$objectId=RequestHandler::getId('productContextObjectId',true);
$listId=RequestHandler::getValue('productContextListId',true);
$scopeClass=RequestHandler::getClass('productContextScopeClass',true);
$scope=RequestHandler::getClass('productContextScope',true);

$arrayId=array();
if (is_array($listId)) {
	$arrayId=$listId;
} else {
	$arrayId[]=$listId;
}

Sql::beginTransaction();
$result="";

foreach ($arrayId as $id) {
	$str=new $scopeClass();
	if ($scopeClass=='ProductContext') {
	  $str->idProduct=$objectId;
	}	else if ($scopeClass=='VersionContext') {
	  $str->idVersion=$objectId;
	}	else {
	  errorLog("ERROR : saveProductContext to neither 'ProductContext' nor 'VersionContext' but to  '$scopeClass'");
	  exit;
	}
	$str->scope=$scope;
	$str->idContext=$id;
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