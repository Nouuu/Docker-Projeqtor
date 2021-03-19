<?php
/*
 * 	@author: qCazelles
 */
require_once "../tool/projeqtor.php";


$productLanguageId=trim(RequestHandler::getId('productLanguageId',true));
$refType=RequestHandler::getClass('refType',true);

if ($refType=='Product' or $refType=='Component') {
  $scope=$refType;
  $scopeClass='ProductLanguage';
} else if ($refType=='ProductVersion' or $refType=='ComponentVersion') {
  $scope=str_replace('Version','',$refType);
  $scopeClass='VersionLanguage';
} else {
  errorLog("ERROR : removeProductLanguage to neither 'Product' nor 'Component' nor 'ProductVersion' nor 'ComponentVersion' but to  '$refType'");
  exit;
}

Sql::beginTransaction();
$result='';

$obj=new $scopeClass($productLanguageId);
$result=$obj->delete();

// Message of correct saving
displayLastOperationStatus($result);

?>