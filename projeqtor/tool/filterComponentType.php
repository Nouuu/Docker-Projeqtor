<?php
/*
 *  mOlives Ticket 178
 */

require_once "../tool/projeqtor.php";
$objectClass = RequestHandler::getClass('objectClass', true);

$user = getSessionUser();
if (!isset($user->_arrayFiltersDetail) or !is_array($user->_arrayFiltersDetail)) {
  $user->_arrayFiltersDetail = array();
}
$user->_arrayFiltersDetail['Component'] = array();
$cvs = new Component();
$type = new Type();

$componentTypeDisplay = $type->getSqlElementsFromCriteria(array('lockUseOnlyForCC'=>'0','scope'=>'Component'));
$cvListId='(';
$cvListName='';
foreach ($componentTypeDisplay as $type){
  foreach ($cvs->getSqlElementsFromCriteria(array('idComponentType' => $type->id )) as $cv) {
      $cvListId.=$cv->id.', ';
      $cvListName.="'".$cv->name."', ";
    }
}

$cvListId=substr($cvListId, 0, -2).')';
$cvListName=substr($cvListName, 0, -2);

if (!isset($user->_arrayFiltersDetail['Component'])) {
  $user->_arrayFiltersDetail['Component']=array();
  $index=0;
} else if (count($user->_arrayFiltersDetail['Component'])==0) {
  $index=0;
} else {
  $index=max(array_keys($user->_arrayFiltersDetail['Component']))+1;
}

if ($objectClass == 'Product' ){
$user->_arrayFiltersDetail['Component'][$index]['disp']['attribute']=i18n('colIdComponent');
$user->_arrayFiltersDetail['Component'][$index]['disp']['operator']=i18n('amongst');
$user->_arrayFiltersDetail['Component'][$index]['disp']['value']=$cvListName;
$user->_arrayFiltersDetail['Component'][$index]['sql']['attribute']='id';
$user->_arrayFiltersDetail['Component'][$index]['sql']['operator']='IN';
$user->_arrayFiltersDetail['Component'][$index]['sql']['value']=$cvListId;
$user->_arrayFiltersDetail['Component'][$index]['isDynamic']="0";
$user->_arrayFiltersDetail['Component'][$index]['orOperator']="0";
$user->_arrayFiltersDetail['Component'][$index]['hidden']="1";

}
setSessionUser($user);

