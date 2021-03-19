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
  include_once("../tool/file.php");
  include_once("../tool/configCheckPrerequisites.php");
  restore_error_handler();
  error_reporting(0);
  $param=$_REQUEST["param"];
  $pname=$_REQUEST["pname"];
  $label=$_REQUEST["label"];
  $value=$_REQUEST["value"];
  $ctrl=$_REQUEST["ctrls"];
  
  // Force DbName and DbPrefix to lowercase
  $param['DbName']=strtolower($param['DbName']);
  $param['DbPrefix']=strtolower($param['DbPrefix']);
  
  if (file_exists('../tool/parametersLocation.php')) {
  	traceHack("direct access to configCheck.php where parametersLocation.php exists");
  	exit;
  }
  // Controls
  $error=false;
  foreach ($param as $id=>$val) {
    $ct=$ctrl[$id];
    if (substr($ct,0,1)=="=") {
      if ( strpos($ct, '=' . $val . '=')===false) {
        showError("incorrect value for '" . $label[$id] . "', valid values are : " . str_replace("="," ",$ct));
        $error=true;
      }
    } else if ($ct=="mandatory") {
      if ( ! $val) {
        showError("incorrect value for '" . $label[$id] . "', field is mandatory");
        $error=true;
      }
    } else if ($ct=="email") {
      if ($val and !filter_var($val, FILTER_VALIDATE_EMAIL)) {
        showError("incorrect value for '" . $label[$id] . "', invalid email address");
        $error=true;
      }
    } else if ($ct=="integer") {
      if (! is_numeric($val) or !is_int($val*1)) {
        showError("incorrect value for '" . $label[$id] . "', field must be an integer");
        $error=true;
      }
    }
  }
  if ($error) exit;
  
  // Check that PDO is enabled
  if (checkPrerequisites(true,$param['DbType'])!="OK") {
    echo "<br/>";
    exit;
  }
  
  // check database connexion
  //error_reporting();
  $dbType=$param['DbType'];
  if ($dbType=='mysql') {
    ini_set('mysql.connect_timeout', 10);
  }
  // dsn without database
  $dsn = $param['DbType'].':host='.$param['DbHost'].';port='.$param['DbPort'];
  try {
   $arraySsl=null;
   if (isset($param['SslKey']) and $param['SslKey'] and isset($param['SslCert']) and $param['SslCert'] and isset($param['SslCa']) and $param['SslCa']) {
     $arraySsl=array(
            PDO::MYSQL_ATTR_SSL_KEY   => $param['SslKey'],
            PDO::MYSQL_ATTR_SSL_CERT  => $param['SslCert'],
            PDO::MYSQL_ATTR_SSL_CA    => $param['SslCa']
        );
   }
    $connexion = new PDO($dsn, $param['DbUser'], $param['DbPassword'],$arraySsl);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
  	if ($dbType=='pgsql' and strpos($e->getMessage(), $param['DbUser'])>0 
  	    and (strpos($e->getMessage(), "does not exist")>0 or strpos($e->getMessage(), "n'existe pas")>0)) {
  	   //FATAL: database "pj_integ" does not exist
  	   //FATAL: la base de données « pj_integ » n'existe pas
  	   // => not an error, pgsql expect an existing database with user name
  	   $pgError="User  '" . $param['DbUser'] . "' is valid but no database named '". $param['DbUser'] ."' exists."
  	     . "<br/>You have to create database '".$param['DbName']."' on your own "
  	     . "<br/>or create default database '".$param['DbUser']."' in order to allow connection of user '".$param['DbUser']."'";
  	} else {
      showError(utf8_encode($e->getMessage()).'<br/>dsn = '.$dsn);
      if ($dbType=='mysql') {
        exit;
      }
  	}
  }
  $baseExists=false;
  $dsn = $param['DbType'].':host='.$param['DbHost'].';port='.$param['DbPort'].';dbname='.$param['DbName'];
  try {
    //KEVIN
    $connexion = new PDO($dsn, $param['DbUser'], $param['DbPassword'],$arraySsl);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $baseExists=true;
  } catch (PDOException $e) {
    $baseExists=false;
  }
  if ( ! $baseExists and $dbType=='pgsql' and isset($pgError)) {
    showError($pgError);
    exit;
  }
  if ( ! $baseExists ) {
  	try {
  	  $dbName=$param['DbName'];
  	  $dbName=str_replace(chr(8),'',$dbName);
  	  $dbName=$connexion->quote($dbName);
  	  $dbName=trim($dbName,"'");
      $query='CREATE DATABASE ' . $dbName;
      if ($dbType=='mysql') {
        $query.=' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;';
      } else if ($dbType=='pgsql') {
      	$query.=' ENCODING \'UNICODE\';';
      }
      $result=$connexion->exec($query);
  	} catch (PDOException $e) {
      showError($e->getMessage().'<br/>dsn = '.$dsn);
      exit;
    }  
    showMessage("Database '" . $param['DbName'] . "' created : OK");
  } else {
    showMessage("Database '" . $param['DbName'] . "' already exists : OK");
  }
  
  // Check attachment directory (may be empty)
  if ($param['AttachmentDirectory']) {
    if (! file_exists ($param['AttachmentDirectory'])) {
      if (! mkdir($param['AttachmentDirectory'],0777,true)) {
        showError("incorrect value for '" . $label['AttachmentDirectory'] . "', this is not a valid directory name");
      }  
    }
  }
  // Check log file location : write possible
  if ($param['logFile']) {
    $rep=dirname($param['logFile']);
    if (! file_exists ($rep)) {
      if (! mkdir($rep,0777,true)) {
        showError("incorrect value for '" . $label['logFile'] . "', does not include a valid directory name");
      } 
    }
    if (! $error) {
      $logFile=str_replace('${date}',date('Ymd'),$param['logFile']);
      if (! writeFile ( 'CONFIGURATION CONTROLS ARE OK', $logFile )) {
        showError("incorrect value for '" . $label['logFile'] . "', cannot write to such a file : check access rights");
      } else {
        //echo "Write in $logFile OK<br/>";
        kill($logFile);
      }
    }
  }  
  
  // Check parameter file location : write possible
  $paramFile=$_REQUEST['location'];
  if ($paramFile) {
    $rep=dirname($paramFile);
    if (! $rep or $rep=='.') {
      $paramFile='../tool/' . $paramFile;
      $rep=dirname($paramFile);
    }
    if (! file_exists ($rep)) {
      if (! mkdir($rep,0777,true)) {
        showError("incorrect value for 'Parameter file name', does not include a valid directory name ($rep)");
      } 
    }
    if (! $error) {
      if (! writeFile ( 'TEST' , $paramFile)) {
        showError("incorrect value for 'Parameter file name', cannot write to such a file : check access rights");
      } else {
        kill($paramFile);
      }
    }
  } else {
    showError("incorrect value for 'Parameter file name', field is mandatory");
  } 
  
  if ($error) {exit;}

  kill($paramFile);
  writeFile('<?php ' . "\n", $paramFile);
  writeFile('// =======================================================================================' . "\n", $paramFile);
  writeFile('// Automatically generated parameter file' . "\n", $paramFile);
  writeFile('// =======================================================================================' . "\n", $paramFile);
  foreach ($param as $id=>$val) {
    if ($pname[$id]=='paramDbPrefix') $val=strtolower($val);
    writeFile('$' . $pname[$id] . ' = \'' . addslashes($val) . '\';', $paramFile);
    writeFile("\n", $paramFile);
  }
  if ($error) {exit;}
  
  $paramLocation="../tool/parametersLocation.php";
  kill($paramLocation);
  if (! writeFile(' ',$paramLocation)) {
    showError("impossible to write \'$paramLocation\' file, cannot write to such a file : check access rights");
  }
  kill($paramLocation);
  writeFile('<?php ' . "\n", $paramLocation);
  writeFile('$parametersLocation = \'' . $paramFile . '\';', $paramLocation);
  
  //rename ('../tool/config.php','../tool/config.php.old');
  showMessage("Parameters are saved.");
  
  showMessage('<span style="font-size:120%;color:#e97b2c">On next page, log in as user "admin" with password "admin"</span>');
  
  echo '<br/><button id="continueButton" class="roundedVisibleButton" dojoType="dijit.form.Button" showlabel="true">Continue';
  echo '<script type="dojo/connect" event="onClick" args="evt">';
  echo '  window.location = ".";';
  echo '</script>';
  echo '</button>';

?>