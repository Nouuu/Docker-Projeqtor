<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
* Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

function checkPrerequisites($showOK=false,$dbType=null) {
  global $projeqtor;
  
  $checkErrors=0;
  $checkWarnings=0;
  $checkOK=0;
  
  // Check PHP Version
  if ( version_compare(phpversion(), '5.2.0', '>=') ) {
    if ($showOK) showMessage("PHP version is ".phpversion()." : OK");
    $checkOK++;
  } else {
    showError("PHP version is ".phpversion().". PHP V5.2.0 or over is required");
    $checkErrors++;
  }
    
  // db modules : PDO and MYSQL_PDO or PGSQL_PDO
  if (!$dbType and is_file ( "../tool/parametersLocation.php" )) {
    $dbType=Parameter::getGlobalParameter('paramDbType');
  }
  if (! extension_loaded('pdo')) {
    showError(" PDO is not available - check your php configuration (php.ini)");
    $checkErrors++;
  } else {
    if ($showOK) showMessage("Module PDO is available : OK");
    $checkOK++;
  }
  if ( ! extension_loaded('pdo_'.$dbType) ) {
    showError("Module PDO for '" . strtoupper($dbType)."' is not available - check your php configuration (php.ini)");
    $checkErrors++;
  } else {
    if ($showOK) showMessage("Module PDO for '" . strtoupper($dbType)."' is available : OK");
    $checkOK++;
  }
  /*if ($dbType=='mysql') { // Not mandatory as ProjeQtOr uses PDO
    if ( ! extension_loaded('mysql') ) { // Obsolete extension since PHP7
      showError("Module MYSQL is not available - check your php configuration (php.ini)");
      $checkErrors++;
    } else {
      if ($showOK) showMessage("Module MYSQL is available : OK");
      $checkOK++;
    } 
    if ( ! extension_loaded('mysqli') ) {
      showWarning("Module MYSQLi is not available - check your php configuration (php.ini)");
      $checkWarnings++;
    } else {
      if ($showOK) showMessage("Module MYSQLi is available : OK");
      $checkOK++;
    }    
  }*/
  /*if ($dbType=='pgsql') { // Not mandatory as ProjeQtOr uses PDO
    if ( ! extension_loaded('pgsql') ) {
      showError("Module PGSQL is not available - check your php configuration (php.ini)");
      $checkError++;
    } else {
      if ($showOK) showMessage("Module PGSQL is available : OK");
      $checkOK++;
    }
  }*/
  
  // MBSTRING is required (for utf-8 compatibility)
  if (! function_exists('mb_check_encoding')) {
    showError("Module MBSTRING is not available - check your php configuration (php.ini)");
    $checkErrors++;
  } else {
    if ($showOK) showMessage("Module MBSTRING is available : OK");
    $checkOK++;
  }
  
  if (! class_exists('ZipArchive')) {
  	showWarning("Class ZipArchive is not available - You won't be able to manage plugins - check your php configuration (php.ini)");
  	$checkErrors++;
  } else {
    if ($showOK) showMessage("Class  ZipArchive is available : OK");
    $checkOK++;
  }
  
  // DOMDocument is required (XML)
  if(! class_exists('DOMDocument')){
    showError("Module DOMDocument is not available - check your php configuration (php.ini)");
    $checkErrors++;
  } else {
    if ($showOK) showMessage("Module DOMDocument is available : OK");
    $checkOK++;
  }
  
  // safe_mode should be disabled
  if (ini_get ( 'safe_mode' )) {
    showWarning("PHP safe_mode is enabled, it should be disabled as it may lead to unexpected behaviors.<br/>Notice that safe_mode is deprecated in PHP 5.3 and removed in PHP 5.4.");
    $checkWarnings++;
  }
  
  // Optional configuration
  if (! function_exists('ImagePng')) {
    showWarning("GD Library not enabled - impossible to draw charts on reports");
    $checkWarnings++;
  } else {
    if (! function_exists('imageftbbox')) {
      showWarning("GD Library or FreeType Librairy incorrect or not correctly installed - impossible to draw charts on reports");
      $checkWarnings++;
    } else {
      if ($showOK) showMessage("GD Library is available and correctly installed : OK");
      $checkOK++;
    }
  }
  if ( ! extension_loaded('imap') ) {
    showWarning("Module IMAP is not available - impossible to configure ProjeQtOr to retreive notes from received emails");
    $checkWarnings++;
  } else {
    if ($showOK) showMessage("Module IMAP is available : OK");
    $checkOK++;
  }
  if ( ! extension_loaded('openssl') ) {
    showWarning("Module OPENSSL is not available - impossible to configure secured access to email server");
    $checkWarnings++;
  } else {
    if ($showOK) showMessage("Module OPENSSL is available : OK");
    $checkOK++;
  }
  if ( ! extension_loaded('xml') ) {
    showWarning("Module XML is not available - this module is mandatory to install plugins");
    $checkWarnings++;
  } else {
    if ($showOK) showMessage("Module XML is available : OK");
    $checkOK++;
  } 
  $ini_val=ini_get('max_input_vars');
  if (!$ini_val or $ini_val<2000) {
    if (!$ini_val) {
      showWarning("max_input_vars is not defined. Default value (1000) is too small - value should be at least 2000, 4000 recommended - check your php configuration (php.ini)");
    } else {
      showWarning("max_input_vars=$ini_val is too small - value should be at least 2000, 4000 recommended - check your php configuration (php.ini)");
    }
    $checkWarnings++;
  } else {
    if ($showOK) showMessage("max_input_vars=$ini_val : OK");
    $checkOK++;
  }
  $ini_val=ini_get('max_execution_time');
  if (!$ini_val) $ini_val=30; // default
  if ($ini_val<30) {
    showWarning("max_execution_time=$ini_val is too small - value should be at least 30 - check your php configuration (php.ini)");
    $checkWarnings++;
  } else {
    if ($showOK) showMessage("max_execution_time=$ini_val : OK");
    $checkOK++;
  }
  $ini_val=ini_get('memory_limit');
  if (!$ini_val) $ini_val='128M'; // default
  $val=intval($ini_val);
  $unit=substr($ini_val,-1);
  if ($unit=='G') $val=$val*1024;
  else if ($unit=='M') $val=$val*1;
  else if ($unit=='K') $val=$val/1024;
  else $val=$val/(1024*1024);
  if ($val>0 and $val<128) {
    showWarning("memory_limit=$ini_val ($val.$unit) is too small - value should be at least 128M, 512M is advised - check your php configuration (php.ini)");
    $checkWarnings++;
  } else {
    if ($showOK) showMessage("memory_limit=$ini_val : OK");
    $checkOK++;
  }
  $ini_val=strtolower(ini_get('file_uploads'));
  if ($ini_val!='on' and $ini_val!='1') {
    showWarning("file_uploads=$ini_val is incorrect - value should be On to allow file uploads - check your php configuration (php.ini)");
    $checkWarnings++;
  } else {
    if ($showOK) showMessage("file_uploads=$ini_val : OK");
    $checkOK++;
  }
  
  // session.auto_start must be disabled 
  if (! isset($projeqtor) or $projeqtor != 'loaded') {
    if ( is_session_started() !== FALSE ) {
      $msg = "ProjeQtOr is not compatible with session auto start.<br/>"
       . "session.auto_start must be disabled (set to Off or 0). <br/>"
       . "Update your php.ini file : session.auto_start = 0<br/>"
       . "or create .htaccess at projeqtor root with : php_flag session.auto_start Off";
      showError($msg);
      $checkErrors++;
    } else {
      if ($showOK) showMessage("session.auto_start is disabled : OK");
      $checkOK++;
    }
  }
  
  // Check 'magic_quotes' : must be disabled ====================================
//   if (get_magic_quotes_gpc ()) {
//     //showWarning ( i18n ( "errorMagicQuotesGpc" ) );
//     showWarning ( "magic_quotes_gpc must be disabled (set to false). Update your Php.ini file.)");
//     $checkWarnings++;
//   } else {
//     if ($showOK) showMessage("magic_quotes_gpc is disabled : OK");
//     $checkOK++;
//   }
  
  // Check Register Globals
  if (ini_get ( 'register_globals' )) {
    //showWarning ( i18n ( "errorRegisterGlobals" ) );
    showWarning ("register_globals must be disabled (set to false). <br/>Update your Php.ini file.");
    $checkWarnings++;
  } else {
    if ($showOK) showMessage("register_globals is disabled : OK");
    $checkOK++;
  }
  
  // All checks done
  if ($checkErrors==0) {
    return "OK";
  } else {
    return $checkErrors;
  }
}

function showError($msg) {
  global $error;
  $error=true;
  echo "<div style='padding:10px 20px' class='messageERROR'><i>" . $msg . "</i></div><br/>";
}

function showMessage($msg) {
  echo "<div style='padding:10px 20px' class='messageOK'><i>" . $msg . "</i></div><br/>";
}

function showWarning($msg) {
  echo "<div style='padding:10px 20px' class='messageWARNING'><i>" . $msg . "</i></div><br/>";
}

if (false === function_exists('is_session_started')) {
  function is_session_started() {
    if ( version_compare(phpversion(), '5.4.0', '>=') ) {
      return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
    } else {
      return session_id() === '' ? FALSE : TRUE;
    }
    return FALSE;
  }
}