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

/** ============================================================================
 * 
 */
require_once "../tool/projeqtor.php";
require_once "../external/phpAES/aes.class.php";
require_once "../external/phpAES/aesctr.class.php";
$username="";
if (isset($_REQUEST['username'])) {
	$username=$_REQUEST['username'];
	$username=AesCtr::decrypt($username, md5(session_id()), Parameter::getGlobalParameter('aesKeyLength'));	
}
if (! function_exists('mb_check_encoding')) {
  $msg="mbstring module not enabled (mb_check_encoding not existing) : install module and unable module in php.ini";
  errorLog($msg);
  echo "ERROR".$msg;
  exit;
} else if (! mb_check_encoding($username,'UTF-8')) {
  echo 'SESSION'.md5(session_id());
  exit;
}
$crit=array('name'=>$username);
$user=SqlElement::getSingleSqlElementFromCriteria('User', $crit);
$sessionSalt=md5("projeqtor".date('YmdHis'));
setSessionValue('sessionSalt', $sessionSalt);
$paramLdap_allow_login=Parameter::getGlobalParameter('paramLdap_allow_login');
if (isset($user->crypto) and ! ($user->isLdap and isset($paramLdap_allow_login) and strtolower($paramLdap_allow_login)=='true')) {
  echo $user->crypto.";".$user->salt.";".$sessionSalt;
} else {
	echo ";;".$sessionSalt;
}