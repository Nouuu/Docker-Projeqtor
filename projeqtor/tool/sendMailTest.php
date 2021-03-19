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

/** =========================================================================== 
 * Chek login/password entered in connection screen
 */
  require_once "../tool/projeqtor.php";
  require_once "../tool/formatter.php";
  scriptLog('   ->/tool/sendMailTest.php');  
  $title=Parameter::getGlobalParameter('mailerTestTitle');
  $msg=Parameter::getGlobalParameter('mailerTestMessage');
  $dest=Parameter::getGlobalParameter('mailerTestDest');
  $send=Parameter::getGlobalParameter('mailerTestSender');
  $dbName=Parameter::getGlobalParameter('paramDbDisplayName');
  $arrayFrom=array('${dbName}','${date}');
  $arrayTo=array($dbName,htmlFormatDateTime(date('Y-m-d H:i:s')));
  $title=str_replace($arrayFrom, $arrayTo, $title);
  $msg=str_replace($arrayFrom, $arrayTo, $msg);
  $result="";
  $sender=null;
  if ($send=='sender') {
    $sender=Parameter::getGlobalParameter('paramMailSender');
  } else {
    $sender=getSessionUser()->email;
  }
  $smtp=Parameter::getGlobalParameter('paramMailSmtpServer');
  if (!$smtp) {
    $error=i18n("messageMandatory",array(i18n("paramParamMailSmtpServer")));
  } else {
    $result=sendMail($dest,$title,$msg,null,null,$sender);
  }
  echo i18n('paramMailerTestSender')."&nbsp;:&nbsp;".$sender.'<br/>';
  if ($result and !isset($error)) {
    echo "<span style='color:green; font-weight:bold'>".i18n("mailSentTo",array($dest))."</span>";
  } else {
    if (!isset($error)) $error=nl2br(Mail::getLastErrorMessage());
    echo "<div style='color:red; font-weight:bold;'>$error</div>";
  }
?>