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
scriptLog("changePassword.php");  
  $password="";
  //florent 4088
  if(RequestHandler::getValue('passwordValidate')!='true'){
    passwordError();
  }
  if (array_key_exists('password',$_POST)) {
    $password=$_POST['password'];
  }
  //   
  $userSalt=$_POST['userSalt'];
  if ($password=="") {
    passwordError(i18n("isEmpty"));
  }
//   if ($password==hash('sha256',Parameter::getGlobalParameter('paramDefaultPassword').$userSalt)) {
//     passwordError(i18n("isDefault"));
//   }
  $user=getSessionUser();
  if ( ! $user ) {
   passwordError(i18n("colUser").' '.i18n("undefinedValue"),true);
  } 
  if ( ! $user->id) {
    passwordError(i18n("colUser").' '.i18n("unknown"),true);
  } 
  if ( $user->idle!=0) {
    passwordError(i18n("colUser").' '.i18n("colLocked"),true);
  } 
  $paramLdap_allow_login=Parameter::getGlobalParameter('paramLdap_allow_login');
  if ($user->isLdap<>0 and isset($paramLdap_allow_login) and strtolower($paramLdap_allow_login)=='true') {
    passwordError(i18n("colUser").' '.i18n("colIsLdap"));
  } 
  $passwordLength=$_POST['passwordLength'];
  if ($passwordLength<Parameter::getGlobalParameter('paramPasswordMinLength')) {
    passwordError(i18n("paramParamPasswordMinLength"));
  }
  changePassword($user, $password, $userSalt, 'sha256');
  
  /** ========================================================================
   * Display an error message because of invalid login
   * @return void
   */
  function passwordError($cause=null,$userIssue=false) {
    echo '<div class="messageERROR">';
    echo i18n('invalidPasswordChange');
    if (!$cause) {
      $reqStr=Parameter::getGlobalParameter('paramPasswordStrength');
      $cause='<div style="width:80%;text-align:left;color:white;padding-left:30px">';
      if ($reqStr>=0) $cause.='<br/>'.i18n('pwdRequiredStrength');
      if ($reqStr>=1) $cause.='<br/>&nbsp;-&nbsp;'.i18n("pwdErrorLength",array(Parameter::getGlobalParameter('paramPasswordMinLength')));
      if ($reqStr>=2) $cause.='<br/>&nbsp;-&nbsp;'.i18n("pwdErrorCase");
      if ($reqStr>=3) $cause.='<br/>&nbsp;-&nbsp;'.i18n("pwdErrorDijit");
      if ($reqStr>=4) $cause.='<br/>&nbsp;-&nbsp;'.i18n("pwdErrorChar");
      $cause.='</div>';
    } 
    echo '<br/><span style="color:#ffaaaa">'.$cause.'</span>';
    echo '</div>';
    if ($userIssue and SSO::isSamlEnabled()) {
    	SSO::addTry();
    }
    exit;
  }
  //
   /** ========================================================================
   * Valid login
   * @param $user the user object containing login information
   * @return void
   */
  function changePassword ($user, $newPassword, $salt, $crypto) {
  	Sql::beginTransaction();
    //$user->password=md5($newPassword); password is encryted in JS
    $user->password=$newPassword;
    $user->salt=$salt;
    $user->crypto=$crypto;
    $user->passwordChangeDate=date('Y-m-d');
    $result=$user->save();
    if (getLastOperationStatus($result)=='OK') {
      $result=i18n('passwordChanged');
	    $result.='<div id="validated" name="validated" type="hidden"  dojoType="dijit.form.TextBox">OK';
	    $result.='<input type="hidden" id="lastOperationStatus" value="OK" />';
    }
    displayLastOperationStatus($result);
  }
  
?>