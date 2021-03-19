<?php
/*
 * @author : qCazelles
 */
require_once "../tool/projeqtor.php";

// Get the link info

$objectClass=RequestHandler::getClass('productLanguageObjectClass',true);
$objectId=RequestHandler::getId('productLanguageObjectId',true);
$listId=RequestHandler::getValue('productLanguageListId',true);
$scopeClass=RequestHandler::getClass('productLanguageScopeClass',true);
$scope=RequestHandler::getClass('productLanguageScope',true);

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
	if ($scopeClass=='ProductLanguage') {
	  $str->idProduct=$objectId;
	}	else if ($scopeClass=='VersionLanguage') {
	  $str->idVersion=$objectId;
	}	else {
	  errorLog("ERROR : saveProductLanguage to neither 'ProductLanguage' nor 'VersionLanguage' but to  '$scopeClass'");
	  exit;
	} 
	$str->scope=$scope;
	$str->idLanguage=$id;
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