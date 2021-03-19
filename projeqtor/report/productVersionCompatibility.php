<?php
/*
 *	@author: qCazelles 
 */
//Print all compatible Product Versions for select Product Version in CSV format
include_once '../tool/projeqtor.php';
include_once '../tool/formatter.php';

$objectClass="";
if (array_key_exists('objectClass', $_REQUEST)){
	$objectClass=trim($_REQUEST['objectClass']);
}
Security::checkValidClass($objectClass);
$objectId="";
if (array_key_exists('objectId', $_REQUEST)){
	$objectId=trim($_REQUEST['objectId']);
}
Security::checkValidId($objectId);

$format="csv";
if (array_key_exists('format', $_REQUEST)){
	$format=trim($_REQUEST['format']);
}

$item=new $objectClass($objectId);
if ($item->scope=='Product') {
	$itemObj=SqlElement::getSingleSqlElementFromCriteria('Product', array('id'=>$item->idProduct));
}
else {
	$itemObj=SqlElement::getSingleSqlElementFromCriteria('Component', array('id'=>$item->idProduct));
}

$canRead=securityGetAccessRightYesNo('menu' . $objectClass, 'read', $item)=="YES";
if (!$canRead) if (!empty($cronnedScript)) goto end; else exit;

$result=array();

$vc=new VersionCompatibility();
$crit=array('idVersionA'=>$item->id);

$result=$vc->getSqlElementsFromCriteria($crit);

$crit=array('idVersionB'=>$item->id);

foreach ($vc->getSqlElementsFromCriteria($crit) as $vc) {
	$result[] = $vc;
}
$idItem=$item->id;

usort($result, function($vca, $vcb) use ($idItem) {
	$a=new ProductVersion((($idItem==$vca->idVersionA) ? $vca->idVersionB : $vca->idVersionA));
	$b=new ProductVersion((($idItem==$vcb->idVersionA) ? $vcb->idVersionB : $vcb->idVersionA));
	if (strcmp($a->name, $b->name) == 0) {
		return strnatcmp($a->versionNumber, $b->versionNumber);
	}
	return strcmp($a->name, $b->name);
});

if ($format=='csv') {
	echo "Product;Version;Compatible Product;Compatible Version\n";
	foreach($result as $vc) {
		$version=new Version(($item->id==$vc->idVersionA) ? $vc->idVersionB : $vc->idVersionA);
		if ($version->scope=='Product') {
			$version=new ProductVersion($version->id);
			$compatibleObj=SqlElement::getSingleSqlElementFromCriteria('Product', array('id'=>$version->idProduct));
		}
		else {
			$version=new ComponentVersion($version->id);
			$compatibleObj=SqlElement::getSingleSqlElementFromCriteria('Component', array('id'=>$version->idProduct));
		}
		echo $itemObj->name.';'.$item->versionNumber.';'.$compatibleObj->name.';'.$version->versionNumber."\n";
	}
}
else {
	errorLog("productCompatibility : incorrect format '$format'");
	if (!empty($cronnedScript)) goto end; else exit;
}

end:
