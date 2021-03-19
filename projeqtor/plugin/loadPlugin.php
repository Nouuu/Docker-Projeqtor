<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2014 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

$maintenance=true;
require_once '../tool/projeqtor.php';
require_once "../db/maintenanceFunctions.php";
if (securityGetAccessRightYesNo('menuPlugin','read')!='YES') {
  traceHack ( "plugin management tried without access right" );
  exit ();
}

$oneFile=null;
if (isset($_REQUEST['pluginFile']) ) {
  $oneFile=urldecode($_REQUEST['pluginFile']);
  $oneFile=Security::checkValidFileName($oneFile);
}
$user=getSessionUser();
$profile=new Profile($user->idProfile);
if ($profile->profileCode!='ADM') {
  echo 'Call to loadPlugin.php for non Admin user.<br/>This action and your IP has been traced.';
  traceHack('Call to loadPlugin.php for non Admin user');
	exit;
}

Sql::$maintenanceMode=true;
$files=Plugin::getZipList($oneFile);
$result="";
foreach ($files as $file) {
  $plugin=new Plugin();
  $result=$plugin->load($file);
  if ($oneFile) {
    echo $result;
  } else {
    echo $result.' ('.htmlEncode($plugin->name).')<br/>';
  }
}
$i18nSessionValue='i18nMessages'.((isset($currentLocale))?$currentLocale:'');
unsetSessionValue($i18nSessionValue,false);

if (! $oneFile) {
  echo "loadPlugin.php executed at ".date('Y-m-d H:i:s');
}
