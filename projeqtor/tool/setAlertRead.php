<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

require_once "../tool/projeqtor.php";
if (! sessionUserExists()) {
	echo "noUser";
	return;
}
if (! array_key_exists('idAlert',$_REQUEST)) {
	return;
}
$remind=0;
if (array_key_exists('remind',$_REQUEST)) {
  $remind=Security::checkValidNumeric($_REQUEST['remind']);
}
Sql::beginTransaction();
$idAlert=trim($_REQUEST['idAlert']);
if ($idAlert=='*') {
  $crit=array('idUser'=>$user->id,'readFlag'=>'0', 'idle'=>'0');
  $alert=new Alert();
  $lst=$alert->getSqlElementsFromCriteria($crit, false, null, 'id asc');
  $date=date('Y-m-d H:i:s');
  foreach ($lst as $alert) {
    if ($alert->alertDateTime<=$date) {
      $alert->readFlag='1';
      $alert->alertReadDateTime=date('Y-m-d H:i:s');
      $result=$alert->save();
    }
  }
} else {
  $idAlert=Security::checkValidId($idAlert);
  $alert=new Alert($idAlert);
  if ($remind) {
  	$alert->alertDateTime= (addDelayToDatetime(date('Y-m-d H:i'), ($remind/60), 'HH'));
  	$alert->readFlag='0';
  } else {
    $alert->readFlag='1';
    $alert->alertReadDateTime=date('Y-m-d H:i:s');
  }
}
$result=$alert->save();
Sql::commitTransaction();