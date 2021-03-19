<?php

require_once "../tool/projeqtor.php";

$businessFeatureId=null;
if (array_key_exists('businessFeatureId',$_REQUEST)) {
	$businessFeatureId=$_REQUEST['businessFeatureId'];
	Security::checkValidId($businessFeatureId);
}
$businessFeatureId=trim($businessFeatureId);
if ($businessFeatureId=='') {
	$businessFeatureId=null;
}
if ($businessFeatureId==null) {
	throwError('business feature id parameter not found in REQUEST');
}
Sql::beginTransaction();
$result='';

$obj=new BusinessFeature($businessFeatureId);
$result=$obj->delete();

// Message of correct saving
displayLastOperationStatus($result);

?>