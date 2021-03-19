<?php
/*
 * 	@author: qCazelles
 */
require_once "../tool/projeqtor.php";

$productContextId=trim(RequestHandler::getId('productContextId',true));
$refType=RequestHandler::getClass('refType',true);

if ($refType=='Product' or $refType=='Component') {
  $scope=$refType;
  $scopeClass='ProductContext';
} else if ($refType=='ProductVersion' or $refType=='ComponentVersion') {
  $scope=str_replace('Version','',$refType);
  $scopeClass='VersionContext';
} else {
  errorLog("ERROR : removeProductContext to neither 'Product' nor 'Component' nor 'ProductVersion' nor 'ComponentVersion' but to  '$refType'");
  exit;
}
Sql::beginTransaction();
$result='';

$obj=new $scopeClass($productContextId);
$result=$obj->delete();

// Message of correct saving
displayLastOperationStatus($result);

?>