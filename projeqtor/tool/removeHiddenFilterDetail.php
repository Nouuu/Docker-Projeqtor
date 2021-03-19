<?php
/*
 *  @author: qCazelles - Ticket 83 
 */
//Delete the filters used to sort comboDetail (on Version Composition for instance)

require_once "../tool/projeqtor.php";

$objectClass=RequestHandler::getClass('objectClass', true);

$user=getSessionUser();

$indexes=array();
$nbCriteria = 0;
if(isset($user->_arrayFiltersDetail[$objectClass])){
  foreach ($user->_arrayFiltersDetail[$objectClass] as $key => $filter) {
  	if (isset($filter['hidden']) and $filter['hidden']=='1') {
  		$indexes[]=$key;
  	} else {
  		$nbCriteria+=1;
  	}
  }
  
  if ($nbCriteria==0) {
  	unset($user->_arrayFiltersDetail[$objectClass]);
  } else if (count($indexes)>0) {
  	foreach ($indexes as $index) {
  		unset($user->_arrayFiltersDetail[$objectClass][$index]);
  	}
  }
}