<?php
use PHPMailer\PHPMailer\PHPMailer;
/**
 * * COPYRIGHT NOTICE *********************************************************
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
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org
 *
 * ** DO NOT REMOVE THIS NOTICE ***********************************************
 */
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

$projeqtor = 'loaded';
spl_autoload_register ( 'projeqtorAutoload', true );
// MTY - LEAVE SYSTEM
//if (isLeavesSystemActiv()) {
 require_once('../tool/projeqtor-hr.php');
//}
// MTY - LEAVE SYSTEM
//include_once ('../model/User.php');
global $targetDirImageUpload;
$targetDirImageUpload='../files/images/';
// Example
if (is_session_started()===FALSE) {
  session_start();
} else {
  echo "ProjeQtOr is not compatible with session auto start.<br/>";
  echo "session.auto_start must be disabled (set to Off or 0). <br/>";
  echo "Update your php.ini file : session.auto_start = 0<br/>";
  echo "or create .htaccess at projeqtor root with : php_flag session.auto_start Off";
  exit();
}
// Setup session. Must be first command.
// === Application data : version, dependencies, about message, ...
$applicationName = "ProjeQtOr"; // Name of the application
$copyright = $applicationName; // Copyright to be displayed
$version = "V9.0.6"; // Version of application : Major / Minor / Release
$build = "0281"; // Build number. To be increased on each release
$website = "http://www.projeqtor.org"; // ProjeQtOr site url
if (!isset($aesKeyLength)) { // one can define key lenth to 256 in parameters.php with $aesKeyLength=256; // valid values are 128, 192 and 256
  $aesKeyLength=128;
}
/**
 * ============================================================================
 * Global tool script for the application.
 * Must be included (include once) on each script remotely called.
 * $Revision$
 * $Date$
 */
// some servers provide empty PHP_SELF, fill it
if (!isset($_SERVER['PHP_SELF']) or !$_SERVER['PHP_SELF']) { // PHP_SELF do not exist or is empty
  $_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];
}
date_default_timezone_set('Europe/Paris');
$globalCatchErrors=false;
$globalSilentErrors=false;
set_exception_handler('exceptionHandler');
set_error_handler('errorHandler');
$browserLocale="";
$reportCount=0;
include_once ("../tool/file.php");
include_once "../tool/html.php"; // include html functions
if (!defined('PHP_VERSION_ID')) {
  $version=explode('.', PHP_VERSION);
  define('PHP_VERSION_ID', ($version[0]*10000+$version[1]*100+$version[2]));
}
/*
 * ============================================================================ global variables ============================================================================
 */
if (is_file("../tool/parametersLocation.php")) {
  // Location of the parameters file should be changed.
  // For security reasons, you should move it to a non web accessed directory.
  // Just create parametersLocation.php file including just one line :
  // <?php $parametersLocation='location of your parameters file';
  include_once "../tool/parametersLocation.php";
  if (!is_file($parametersLocation)) {
    echo "*** ERROR ****<br/>";
    echo " parameter file not found at '".$parametersLocation."'<br/>";
    echo " Check file '/tool/parametersLocation.php' or remove it to use '/tool/parameters.php'.<br/>";
    echo " <br/>";
    echo " If problem persists, you may get some help at the forum at <a href='http://www.projeqtor.org/'>ProjeQtOr web site </a>.";
    exit();
  }
  include_once $parametersLocation;
} else {
  setSessionValue('setup', true, true);
  if (is_file("../tool/config.php") and !(isset($indexPhp) and $indexPhp)) {
    include_once "../tool/config.php";
    exit();
  }
  include_once "../tool/parameters.php"; // New in 0.6.0 : No more need to change this line if you move this file. See above.
}

$tz=Parameter::getGlobalParameter('paramDefaultTimezone');
if ($tz) date_default_timezone_set($tz);
if (!isset($noScriptLog)) {
  if (isset($debugTraceUpdates) and $debugTraceUpdates==true) {
    debugTraceLog("===== ".$_SERVER["SCRIPT_NAME"]." =======================================================================");
  } else {
    scriptLog("=====".$_SERVER["SCRIPT_NAME"]);
  }
}

if (RequestHandler::isCodeSet('nosso')) {
  SSO::setAvoidSSO();
}
$testMode=false; // Setup a variable for testing purpose test.php changes this value to true
$i18nMessages=null; // Array containing messages depending on local (initialized at first need)

setupLocale(); // Set up the locale : must be called before any call to i18n()
securityCheckRequest();

// About message (click on Logo)
$aboutMessage=''; // About message to be displayed when clicking on application logo
$aboutMessage.='<div>'.$applicationName.' '.$version.' ('.($build+0).')</div><br/>';
$aboutMessage.='<div>'.i18n("aboutMessageWebsite").' : <a target=\'#\' href=\''.$website.'\'>'.$website.'</a></div><br/>';
if (isset($paramSupportEmail)) {
  $aboutMessage.='<div>'.i18n("colEmail").' : <a target=\'#\' href=\'mailto:'.$paramSupportEmail.'\'>'.$paramSupportEmail.'</a></div><br/>';
}

// $paramIconSize=setupIconSize(); //Not used any more this way - user Parameter::getUserParameter("paramIconSize");
$cr="\n"; // Line feed (just for html dynamic building, to ease debugging

$isAttachmentEnabled=true; // allow attachment
if (!Parameter::getGlobalParameter('paramAttachmentDirectory') or !Parameter::getGlobalParameter('paramAttachmentMaxSize')) {
  $isAttachmentEnabled=false;
}

if (isset($debugReport) and $debugReport) {
  $pos=strpos($_SERVER["SCRIPT_NAME"], '/report/');
  if (RequestHandler::getValue('fromToday',false,'false')=='true') $pos=false;
  if ($pos!==false) {
    echo '<div style="color:var(--color-medium);position:absolute;right:17px;top:-3px;font-size:90%">'.substr($_SERVER["SCRIPT_NAME"], $pos).'</div>';
  }
}
if (false===function_exists('lcfirst')) {

  function lcfirst($str) {
    $str[0]=strtolower($str[0]);
    return (string)$str;
  }
}
/*
 * ============================================================================ main controls ============================================================================
 */

// Check 'magic_quotes' : must be disabled ====================================
// if (get_magic_quotes_runtime()) {
//   @set_magic_quotes_runtime(0);
// }
$page=$_SERVER['PHP_SELF'];
if (!(isset($maintenance) and $maintenance) and !(isset($batchMode) and $batchMode) and !(isset($indexPhp) and $indexPhp)) {
  // Get the user from session. If not exists, request connection ===============
  if (getSessionUser() and getSessionUser()->id) {
    $user=getSessionUser();
    // user must be a User object. Otherwise, it may be hacking attempt.
    if (get_class($user)!="User") {
      // Hacking detected
      traceLog("'user' is not an instance of User class. May be a hacking attempt from IP ".$_SERVER['REMOTE_ADDR']);
      envLog();
      $user=null;
      throw new Exception(i18n("invalidAccessAttempt"));
    }
    if (property_exists($user, '_API') and !isset($apiMode)) {
      // Hacking detected
      traceLog("'user' was connected through API and should not reach Application for same session. May be a hacking attempt from IP ".$_SERVER['REMOTE_ADDR']);
      envLog();
      $user=null;
      throw new Exception(i18n("invalidAccessAttempt"));
    }
    $oldRoot="";
    if (sessionValueExists('appRoot')) {
      $oldRoot=getSessionValue('appRoot');
    }
    if ($oldRoot!="" and $oldRoot!=getAppRoot() and $oldRoot.'mobile'!=getAppRoot()) {
      $appRoot=getAppRoot();
      traceLog("Application root changed (from $oldRoot to $appRoot). New Login requested for user '".$user->name."' from IP ".$_SERVER['REMOTE_ADDR']);
      // session_destroy();
      Audit::finishSession();
      $user=null;
    }
  } else {
    $user=null;
  }
  $pos=strrpos($page, "/");
  if ($pos) {
    $page=substr($page, $pos+1);
  }
  scriptLog("Page=".$page);
  if ($page=='passwordChange.php' and isset($lockPassword) and $lockPassword==true) {
    header('Status: 301 Moved Permanently', false, 301);
    header('Location: main.php');
    exit;
  }
  // SSO Athentication using SAML2
  //damian #3980
  Parameter::refreshParameters();
  $ssoUserCreated=false;
  if (!$user and SSO::isSamlEnabled() and $page!='loginCheck.php' and $page!='getHash.php' and $page!='saveDataToSession.php' ) {
    SSO::addTry();
    require_once dirname(__DIR__).'/sso/_toolkit_loader.php';
    require_once dirname(__DIR__).'/sso/projeqtor/settings.php'; // defines $settingsInfo
    if (isset($_SESSION['samlUserdata'])) {
      $auth = new OneLogin_Saml2_Auth($settingsInfo);
      SSO::resetTry();
      $authAttr = $_SESSION['samlUserdata'];
      if (isset($authAttr[SSO::getAttributeName('uid')]) and isset($authAttr[SSO::getAttributeName('uid')][0])) {
        $login = $authAttr[SSO::getAttributeName('uid')][0];
      } else {
        traceLog("Cannot retreive field ".SSO::getAttributeName('uid')." in samlUserData");
        traceLog($authAttr);
        $login=null;
      }
      $user=new User();
      $user=SqlElement::getSingleSqlElementFromCriteria('User', array('name'=>strtolower($login)));
      if (!$user->id) {
        $user=SSO::createNewUser($authAttr);
        if (!$user->id) $user=null;
        else $ssoUserCreated=true;
      }
      if ($user and $user->id) {
        User::resetAllVisibleProjects();
        //damian #3724
        if(Parameter::getGlobalParameter('applicationStatus')=='Closed'){
          $prf=new Profile($user->idProfile);
          if ($prf->profileCode!='ADM') {
            $user=null;
          }else{
            if (substr($_SERVER['PHP_SELF'],-9)=='/main.php'){
              $user->finalizeSuccessfullConnection(false,true);
            }
          }
        }else{
          if (substr($_SERVER['PHP_SELF'],-9)=='/main.php'){
            $user->finalizeSuccessfullConnection(false,true);
          }
        }
        $currentLocale=null;
        $i18nMessages=null;
        setupLocale();
      }
    } else if (SSO::isFirstTry()) { // Only 1 try to connect, then return to standard connection    
      $auth = new OneLogin_Saml2_Auth($settingsInfo);
      $auth->login();
    } else { // Too many tries, get to ProjeQtOr Login screen
      $user=null;
      $errorSSO=i18n("ssoConnectionFailed",array(SSO::getCommonName()));
      SSO::resetTry();
    }
  	SSO::unsetAvoidSSO();
  }
  
  if ((!$user or !$user->id) and $page!='loginCheck.php' and $page!='getHash.php' and $page!='saveDataToSession.php') {
    $cookieHash=User::getRememberMeCookie();
    if (!empty($cookieHash)) {
      $cookieUser=SqlElement::getSingleSqlElementFromCriteria('User', array('cookieHash'=>$cookieHash));
      if ($cookieUser and $cookieUser->id and ! SSO::isEnabled()) {
        $user=$cookieUser;
        $loginSave=true;
        $user->setCookieHash();
        $user->save();
        User::resetAllVisibleProjects();
        //damian
        if(Parameter::getGlobalParameter('applicationStatus')=='Closed'){
        	$prf=new Profile($user->idProfile);
        	if ($prf->profileCode!='ADM') {
        	  $user=false;
        	}else{
        	  $user->finalizeSuccessfullConnection(true);
        	  setSessionUser($user);
        	  $currentLocale=null;
        	  $i18nMessages=null;
        	  setupLocale();
        	}
        }else{
          $user->finalizeSuccessfullConnection(true);
          setSessionUser($user);
          $currentLocale=null;
          $i18nMessages=null;
          setupLocale();
        }
      }
    }
    if (!$user or !$user->id) {
      if (is_file("login.php")) {
        include "login.php";
      } else {
        echo '<input type="hidden" id="lastOperation" name="lastOperation" value="testConnection">';
        echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="ERROR">';
        echo '<div class="messageERROR" >'.i18n('errorConnection').'</div>';
      }
      exit();
    }
  }
  $paramLdap_allow_login=Parameter::getGlobalParameter('paramLdap_allow_login');
  if (isset($user) and $user and $user->id) {
    if ( ($user->isLdap==0 or !isset($paramLdap_allow_login) or strtolower($paramLdap_allow_login)!='true')  ) {
      if ($user and $page!='loginCheck.php' and $page!="changePassword.php") {
        $changePassword=false;
        if (array_key_exists('changePassword', $_REQUEST)) {
          $changePassword=true;
        }
        if (!$user->crypto) {
          $changePassword=true;
        } else {
          $defaultPwd=$user->getRandomPassword();
          if ($user->crypto=="md5") {
            $defaultPwd=md5($defaultPwd.$user->salt);
          } else if ($user->crypto=="sha256") {
            $defaultPwd=hash("sha256", $defaultPwd.$user->salt);
          }
          if ($user->password==$defaultPwd) {
            $changePassword=true;
          }
          $passwordValidityDays=Parameter::getGlobalParameter('passwordValidityDays');
          if ($passwordValidityDays and isset($user->passwordChangeDate)) {
            if (addDaysToDate($user->passwordChangeDate, $passwordValidityDays)<date('Y-m-d')) {
              $changePassword=true;
              traceLog("password expired for user '$user->name'");
            }
          }
        }
        if ($changePassword) {
          if (is_file("../view/passwordChange.php")) {
            include "../view/passwordChange.php";
          } else {
            echo '<input type="hidden" id="lastOperation" name="lastOperation" value="testPassword">';
            echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="ERROR">';
            echo '<span class="messageERROR" >'.i18n('invalidPasswordChange').'</span>';
          }
          exit();
        }
      }
    }
  }
  if (isset($user)) {
    Audit::updateAudit();
  } 
}

/*
 * ============================================================================ functions ============================================================================
 */

/**
 * 
 * @param array $array : The array containing the list of Model objects to sort
 * @param string $field : The field with sort
 * @param boolean $preserveKey : true to preserve keys of array
 * @return array : The array sorted
 */
function sortArrayOfModelObjectsByAField($array=array(), $field="", $preserveKey=false) {
    $intType = array("bigint", "double", "float", "int", "integer", "mediumint", "numeric", "real", "smallint", "tinyint","dec","decimal","serial","bigserial","double precision");
    
    if ($array==null or $field==="") { return $array; }

    reset($array);
    $firtKey = key($array);
    $object = $array[$firtKey];
    if (!is_subclass_of($object,'SqlElement')) { return $array; }
    if ($object->getDatabaseColumnName($field, true)=="") { return $array; }
    $dataType = $object->getDataType($field);
    if (in_array($dataType, $intType)) {
        if ($preserveKey) {
            uasort($array, function($a, $b){global $field; return ($a->$field > $b->$field);});
        } else {
            usort($array, function($a, $b){global $field; return ($a->$field > $b->$field);});            
        }
    } else {
        if ($preserveKey) {
            uasort($array, function($a, $b){global $field; return strcmp($a->$field, $b->$field);});                
        } else {
            usort($array, function($a, $b){global $field; return strcmp($a->$field, $b->$field);});                
        }
    }
    return $array;
}

// ADD BY TABARY - CLASS FIELD WITHOUT ALIAS FOREIGN KEY
/**
 * ===========================================================
 * 
 * @param $field :
 *          The class field to test
 * @return string : the real foreign Key
 */
function foreignKeyWithoutAlias($field) {
  $realFkPos=strpos($field, "__id");
  $realFk=($realFkPos==false?$field:substr($field, $realFkPos+2));
  return $realFk;
}
// END : ADD BY TABARY - CLASS WITHOUT ALIAS FOREIGN KEY

// ADD BY TABARY - CLASS FIELD WITH ONLY ALIAS FOREIGN KEY
/**
 * ===========================================================
 * 
 * @param $field :
 *          The class field to test
 * @return string : the real foreign Key
 */
function foreignKeyOnlyAlias($field) {
  $realFkPos=strpos($field, "__id");
  $realFk=($realFkPos==false?$field:substr($field, 0, $realFkPos));
  return $realFk;
}
// END : ADD BY TABARY - CLASS WITHOUT ALIAS FOREIGN KEY

// ADD BY TABARY - IS CLASS'S FIELD A FOREIGN KEY
/**
 * ===========================================================
 * 
 * @param $field :
 *          The class's field to test
 * @param $class :
 *          The class
 * @return boolean : true if field passed in parameter is a foreign Key
 */
function isForeignKey($field, $class) {
  $field=foreignKeyWithoutAlias($field); // Will allow to have xxxx__idYyyy, not only idXxxx_idYyyy
  if (substr($field, 0, 2)=='id' and strlen($field)>2 and $field!='idle' and substr($field, 2, 1)==strtoupper(substr($field, 2, 1))) {
//     $dataLength=$class->getDataLength($field);
//     $dataType=$class->getDataType($field);
//     return ($dataLength==12 and $dataType=='int');
    return true;
  } else {
    return false;
  }
}
// END : ADD BY TABARY - IS CLASS'S FIELD A FOREIGN KEY

// ADD BY Marc TABARY - 2017-03-15 - GENERIC FUNCTION TO GET VISIBILITY ON WORK & COST FOR CONNECTED USER
/**
 * ============================================================================================
 * Return the work & cost visibility of the connected user
 * 
 * @return array key-value : Key 1 : workVisibility
 *         Key 2 : costVisibility
 */
function getUserConnectedWorkCostVisibility() {
  if (!sessionUserExists()) {
    return array('workVisibility'=>'NO', 'costVisibility'=>'NO');
  }
  
  $user=getSessionUser();
  $profile=$user->getProfile();
  
  $list=SqlList::getList('VisibilityScope', 'accessCode', null, false);
  $hCost=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile, 'scope'=>'cost'));
  $hWork=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile, 'scope'=>'work'));
  
  $costVisibility='NO';
  $workVisibility='NO';
  
  if ($hCost->id) {
    $costVisibility=$list[$hCost->rightAccess];
  } else {
    $costVisibility='ALL';
  }
  if ($hWork->id) {
    $workVisibility=$list[$hWork->rightAccess];
  } else {
    $workVisibility='ALL';
  }
  
  return array('workVisibility'=>$workVisibility, 'costVisibility'=>$costVisibility);
}
// END ADD BY Marc TABARY - 2017-03-15 - GENERIC FUNCTION TO GET VISIBILITY ON WORK & COST FOR CONNECTED USER

// ADD BY Marc TABARY - 2017-02-23 - GENERIC FUNCTION TO GET VISIBLE OBJECT's LIST IN FUNCTION OF USER HABILITATION
/**
 * =============================================================================================================
 * Return an array (key - name) of objects that are visible by the connected user in function of its habilitation
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $objName
 *          : The name of the object to whith return visible list (ex : 'Project'
 * @param boolean $limitToActiveObjects
 *          : false = list with idle's object = 0 or 1 - true = idle = 0
 * @param string $listScreen
 *          : Must be "List" (combobox list or list) - "Screen" (Screen lists on objects)
 * @param boolean $idLinkObjectName
 *          : if not '', limit the list of object with this idLinkObject = null
 *          --------------------------------------------------------------------------------------------------------------
 * @return array (key - name)
 *         --------------------------------------------------------------------------------------------------------------
 *         Ex : getUserVisibleObjectsList('Project', true, 'List', 'idOrganization')
 *         return the project's list with active projects, for combobox list or selection list and idOrganization null
 */
function getUserVisibleObjectsList($objName, $limitToActiveObjects=false, $listScreen="List", $idLinkObjectName='') {
  if ($objName==NULL or trim($objName)=='') {
    return array();
  }
  if ($listScreen!='List' and $listScreen!='Screen') {
    return array();
  }
  
  switch($objName) {
// MTY - LEAVE SYSTEM
    case 'EmploymentContract' :
      $user=getSessionUser();
      return $user->getVisibleEmploymentContract($limitToActiveObjects);            
      break;
// MTY - LEAVE SYSTEM
    case 'Resource' :
      return getUserVisibleResourcesList($limitToActiveObjects, $listScreen, $idLinkObjectName);
      break;
    case 'Project' :
      $user=getSessionUser();
      return $user->getVisibleProjectsNullForeignKey($limitToActiveObjects, $idLinkObjectName);
      break;
    default :
      return array();
      break;
  }
}
// END ADD BY Marc TABARY - 2017-02-23 - GENERIC FUNCTION TO GET VISIBLE OBJECT's LIST IN FUNCTION OF USER HABILITATION

// BEGIN - ADD BY TABARY - GENERIC FUNCTION TO GET VISIBLE CLASS's LIST IN FUNCTION OF USER's PROFILE HABILITATION
/**
 * =============================================================================================================
 * Return an array () of classes that are visible by the connected user in function of its habilitation
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $listScreen
 *          : Must be "List" (combobox list or list) - "Screen" (Screen lists on objects)
 * @param string $user
 *          : The user to get visible classe - If null, the current user
 *          --------------------------------------------------------------------------------------------------------------
 * @return array (key - className)
 *         --------------------------------------------------------------------------------------------------------------
 *         Ex : getUserVisibleObjectClassesList('List')
 *         return the classes list for combobox list or selection list and current user
 */
function getUserVisibleObjectClassesList($listScreen="List", $user=NULL) {
  if ($listScreen!='List' and $listScreen!='Screen') {
    return array();
  }
  
  if (is_null($user)) {
    $user=getSessionUser();
  }
  
  $menu=new Menu();
  $menuTable=$menu->getDatabaseTableName();
  $habi=new Habilitation();
  $habiTable=$habi->getDatabaseTableName();
  $query="SELECT substr(menu.name,5) AS class ";
  $query.="FROM $menuTable menu ";
  $query.="INNER JOIN $habiTable habilitation ON habilitation.idMenu = menu.id ";
  $query.="WHERE habilitation.allowAccess=1 AND habilitation.idProfile=";
  $query.=$user->idProfile;
  
  $result=Sql::query($query);
  $array_result=array();
  $i=1;
  if (Sql::$lastQueryNbRows>0) {
    $line=Sql::fetchLine($result);
    while ($line) {
      $theClass=$line['class'];
      if (SqlElement::class_exists($theClass)&&strpos($theClass, 'Type')==false) {
        $array_result[]=$theClass;
        $i++;
      }
      $line=Sql::fetchLine($result);
    }
  }
  
  return $array_result;
}
// END - ADD BY TABARY - GENERIC FUNCTION TO GET VISIBLE CLASS's LIST IN FUNCTION OF USER's PROFILE HABILITATION

// BEGIN - ADD BY TABARY - GENERIC FUNCTION TO GET CLASSES LIST ASSOCIATED TO RESOURCE DATABASE TABLE
/**
 * =============================================================================================================
 * Return an array () of classes that are associated to database table 'resource'
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @return array (fieldName)
 *         --------------------------------------------------------------------------------------------------------------
 */
function getObjectClassesAssociatedToResourceDatabaseTable() {
  $classesList=getUserVisibleObjectClassesList();
  $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
  
  $classesResourceList=array();
  foreach ($classesList as $key=>$class) {
    $refClass=new ReflectionClass($class);
    try {
      $refProp=$refClass->getProperty('_databaseTableName');
    } catch (Exception $ex) {
      $pClass=get_parent_class($class);
      if (substr($pClass, -4)==="Main") {
        $refClass=new ReflectionClass($pClass);
        try {
          $refProp=$refClass->getProperty('_databaseTableName');
        } catch (Exception $ex) {
          $refProp="";
        }
      } else {
        $refProp="";
      }
    }
    $databaseTableName="";
    if ($refProp!="") {
      if ($refProp->isPrivate()) {
        $refProp->setAccessible(true);
      }
      $refValue=$refProp->getValue();
      if ($refProp->isPrivate()) {
        $refProp->setAccessible(false);
      }
      $databaseTableName=strtolower(str_replace($paramDbPrefix, "", $refValue));
    }
    if (strtolower($class)==='resource') {
      $classesResourceList[]=$class;
    } elseif ($databaseTableName!="") {
      if ($databaseTableName==='resource') {
        $classesResourceList[]=$class;
      }
    }
  }
  return $classesResourceList;
}
// END - ADD BY TABARY - GENERIC FUNCTION TO GET CLASSES LIST ASSOCIATED TO RESOURCE DATABASE TABLE

// BEGIN - ADD BY TABARY - GENERIC FUNCTION TO GET CLASS's AND FOREIGN CLASSES's FIELDS LIST
/**
 * =============================================================================================================
 * Return an array () of class's fields
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $objectClassName
 *          : The class name to get the field's list and the foreign class field's list
 * @param boolean $onlyResource
 *          : Get only idField that reference a resource
 * @param boolean $withoutCreationDate
 *          : If true, the creationDate field is'nt take
 *          --------------------------------------------------------------------------------------------------------------
 * @return array (fieldName)
 *         --------------------------------------------------------------------------------------------------------------
 *         Ex : getObjectClassFieldsList('Project')
 *         return the fields list for 'Project'
 */
function getObjectClassAndForeignClassFieldsList($objectClassName="", $onlyResource=false, $withoutCreationDate=true) {
  if ($objectClassName==="") {
    return array();
  }
  
  $allFields=getObjectClassFieldsList($objectClassName);
  
  if (!$onlyResource) {
    $fieldsWithCalculated=$allFields;
  } else {
    $resourceClasses=getObjectClassesAssociatedToResourceDatabaseTable();
    $fieldsWithCalculated=[];
    foreach ($allFields as $field) {
      if ($field==="id") {
        if (in_array($objectClassName, $resourceClasses)) {
          $fieldsWithCalculated[]=$field;
        }
      } else {
        if (substr($field, 0, 2)=="id" and strpos($field, "idle")===false and substr($field, -4)!='Type') {
          $fieldsWithCalculated[]=$field;
        }
      }
    }
  }
  
  // Don't take calculated fields
  $fields=array();
  $obj=new $objectClassName();
  foreach ($fieldsWithCalculated as $field) {
    if (!$obj->isAttributeSetToField($field, "calculated")) {
      $fields[]=$field;
    }
  }
  
  $fFields=array();
  foreach ($fields as $field) {
    if (substr($field, 0, 2)=="id" and strpos($field, "idle")===false and substr($field, -4)!='Type' and $field!="id") {
      // Foreign Class
      $fClass=substr($field, 2);
      if (class_exists($fClass)) {
        $fFieldsFullListWithCalculated=getObjectClassFieldsList($fClass, $withoutCreationDate);
        
        // Don't take calculated fields
        $fFieldsFullList=[];
        $obj=new $fClass();
        foreach ($fFieldsFullListWithCalculated as $theField) {
          if (!$obj->isAttributeSetToField($field, "calculated")) {
            $fFieldsFullList[]=$theField;
          }
        }
        if ($onlyResource) {
          $fFieldsList=[];
          foreach ($fFieldsFullList as $fld) {
            if (substr($fld, 0, 2)=="id" and strpos($fld, "idle")===false and substr($fld, -4)!='Type' and $fld!="id") {
              $class=substr($fld, 2);
              if (in_array($class, $resourceClasses)) {
                $fFieldsList[]=$fld;
              }
            }
          }
        } else {
          $fFieldsList=$fFieldsFullList;
        }
        
        for ($i=0; $i<count($fFieldsList); $i++) {
          $fFieldsList[$i]=$field.'.'.$fFieldsList[$i];
        }
        $fFields=array_merge($fFields, $fFieldsList);
      }
    }
  }
  
  if ($onlyResource) {
    $tFields=$fields;
    $fields=[];
    foreach ($tFields as $field) {
      $class=substr($field, 2);
      if (in_array($class, $resourceClasses)) {
        $fields[]=$field;
      }
    }
  }
  
  $fullFields=array_merge($fields, $fFields);
  return $fullFields;
}

/**
 * =============================================================================================================
 * Return an array () of class's fields
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $objectClassName
 *          : The class name to get the field's list and the foreign class field's list
 * @param boolean $onlyResource
 *          : Get only idField that reference a resource
 * @param boolean $withoutCreationDate
 *          : If true, the creationDate field is'nt take
 *          --------------------------------------------------------------------------------------------------------------
 * @return array (fieldName)
 *         --------------------------------------------------------------------------------------------------------------
 *         Ex : getObjectClassFieldsList('Project')
 *         return the fields list for 'Project'
 */
function getObjectClassAndForeignClassFieldsList_($objectClassName="", $onlyResource=false, $withoutCreationDate=true) {
  if ($objectClassName==="") {
    return array();
  }
  
  $allFields=getObjectClassFieldsList($objectClassName);
  
  if (!$onlyResource) {
    $fieldsWithCalculated=$allFields;
  } else {
    $resourceClasses=getObjectClassesAssociatedToResourceDatabaseTable();
    $fieldsWithCalculated=[];
    foreach ($allFields as $field) {
      if ($field==="id") {
        if (in_array($objectClassName, $resourceClasses)) {
          $fieldsWithCalculated[]=$field;
        }
      } else {
        if (substr($field, 0, 2)=="id" and strpos($field, "idle")===false and substr($field, -4)!='Type') {
          $fieldsWithCalculated[]=$field;
        }
      }
    }
  }
  
  $fields=array();
  $obj=new $objectClassName();
  foreach ($fieldsWithCalculated as $field) {
    // Don't take calculated fields AND hidden fields
    if (!$obj->isAttributeSetToField($field, "calculated") and !$obj->isAttributeSetToField($field, "hidden")) {
      $fields[]=$field;
    }
  }
  
  $fFields=array();
  foreach ($fields as $field) {
    if (substr($field, 0, 2)=="id" and strpos($field, "idle")===false and substr($field, -4)!='Type' and $field!="id") {
      // Foreign Class
      $fClass=substr($field, 2);
      if (class_exists($fClass)) {
        $fFieldsFullListWithCalculated=getObjectClassFieldsList($fClass, $withoutCreationDate);
        
        $fFieldsFullList=[];
        $fObj=new $fClass();
        foreach ($fFieldsFullListWithCalculated as $theField) {
          // Don't take calculated fields and hidden fields
          if (!$fObj->isAttributeSetToField($field, "calculated") and !$fObj->isAttributeSetToField($field, "hidden")) {
            $fFieldsFullList[]=$theField;
          }
        }
        if ($onlyResource) {
          $fFieldsList=[];
          foreach ($fFieldsFullList as $fld) {
            if (substr($fld, 0, 2)=="id" and strpos($fld, "idle")===false and substr($fld, -4)!='Type' and $fld!="id") {
              $class=substr($fld, 2);
              if (in_array($class, $resourceClasses)) {
                $fFieldsList[]=$fld;
              }
            }
          }
        } else {
          $fFieldsList=$fFieldsFullList;
        }
        
        for ($i=0; $i<count($fFieldsList); $i++) {
          $fFieldsList[$i]=$field.'.'.$fFieldsList[$i];
        }
        $fFields=array_merge($fFields, $fFieldsList);
      }
    }
  }
  
  if ($onlyResource) {
    $tFields=$fields;
    $fields=[];
    foreach ($tFields as $field) {
      $class=substr($field, 2);
      if (in_array($class, $resourceClasses)) {
        $fields[]=$field;
      }
    }
  }
  
  $fullFields=array_merge($fields, $fFields);
  
  $fieldsTranslated=[];
  $objectClassNameTranslated=str_replace(' ', '_', i18n($objectClassName));
  for ($i=0; $i<count($fullFields); $i++) {
    if (strpos($fullFields[$i], '.')===false) {
      $fieldTranslated=$obj->getColCaption($fullFields[$i]);
      if (substr($fieldTranslated, 0, 1)!=='[') {
        $fieldsTranslated[]=$objectClassNameTranslated.".".str_replace(' ', '_', $fieldTranslated);
      }
    } else {
      $posDot=strpos($fullFields[$i], '.');
      // The foreign Table
      $table=substr($fullFields[$i], 2, $posDot-2);
      // The foreign field
      $field=substr($fullFields[$i], $posDot+1);
      
      $obj=new $table();
      $fieldTranslated=$obj->getColCaption($field);
      if (substr($fieldTranslated, 0, 1)!=='[') {
        $fieldTranslated=str_replace(' ', '_', $fieldTranslated);
        $tableTranslated=str_replace(' ', '_', i18n($table));
        $fieldsTranslated[]=$tableTranslated.'.'.$fieldTranslated;
      }
    }
  }
  return $fieldsTranslated;
}

/**
 * =============================================================================================================
 * Return an array () of classes corresponding to the class passed in parameters and its foreign keys classes
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $objectClassName
 *          : The class name to get the fereign keys classes list
 * @param boolean $onlyResource
 *          : Get only classes that have at less one field which references a resource
 *          --------------------------------------------------------------------------------------------------------------
 * @return array (key => value)
 *         - key = the class name
 *         - value = the translate class name
 *         --------------------------------------------------------------------------------------------------------------
 */
function getTranslatedClassAndFKeyClasses($objectClassName="", $onlyResource=false) {
  if (trim($objectClassName)=="") {
    return array();
  }
  
  $resourceClasses=getObjectClassesAssociatedToResourceDatabaseTable();
  
  if (!$onlyResource) {
    $classesList[$objectClassName]=i18n($objectClassName);
  }
  
  $allFields=getObjectClassFieldsList($objectClassName);
  
  if ($onlyResource) {
    $hasFieldThatReferenceAResource=false;
    foreach ($allFields as $field) {
      $theClass=substr($field, 2);
      if (in_array($theClass, $resourceClasses) and substr($field, 0, 2)=='id') {
        $hasFieldThatReferenceAResource=true;
        break;
      }
    }
    if ($hasFieldThatReferenceAResource) {
      $classesList[$objectClassName]=i18n($objectClassName);
    } else {
      $classesList=array();
    }
  }
  
  $fClasses=array();
  $objClass=new $objectClassName();
  foreach ($allFields as $field) {
    if (substr($field, 0, 2)=="id" and strpos($field, "idle")===false and substr($field, -4)!='Type' and $field!="id") {
      // Foreign Class
      $fClass=substr($field, 2);
      
      if ($onlyResource) {
        $allFields_=getObjectClassFieldsList($fClass);
        $hasFieldThatReferenceAResource=false;
        foreach ($allFields_ as $field) {
          $theClass=substr($field, 2);
          if (in_array($theClass, $resourceClasses) and substr($field, 0, 2)=='id') {
            $hasFieldThatReferenceAResource=true;
            break;
          }
        }
        if ($hasFieldThatReferenceAResource) {
          $fClasses[$fClass]=i18n($fClass);
        }
      } else {
        if ($objectClassName==$fClass) {
          $nameClass=i18n($fClass);
        } else {
          $nameClass=$objClass->getColCaption($field);
        }
        $fClasses[$fClass]=$nameClass;
        //$fClasses[$fClass]=i18n($fClass);
      }
    }
  }
  return array_merge_preserve_keys($classesList, $fClasses);
}

/**
 * =============================================================================================================
 * Return an array () of class's fields
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $objectClassName
 *          : The class name to get the field's list
 *          --------------------------------------------------------------------------------------------------------------
 * @return array (fieldName)
 *         --------------------------------------------------------------------------------------------------------------
 *         Ex : getObjectClassFieldsList('Project')
 *         return the fields list for 'Project'
 */
function getObjectClassFieldsList($objectClassName="", $withoutCreationDate=true, $allFields=false) {
  $reflect=new ReflectionClass($objectClassName);
  $props=$reflect->getProperties(ReflectionProperty::IS_PUBLIC);
  
  $nItem=array();
  foreach ($props as $prop) {
    if (substr($prop->getName(), 0, 1)!="_") {
      if (($prop->getName()==='creationDate' or $prop->getName()==='creationDateTime') and $withoutCreationDate) {} else {
        $nItem[]=$prop->getName();
      }
    } elseif ($allFields) {
      $nItem[]=$prop->getName();
    }
  }
  return $nItem;
}

/**
 * =============================================================================================================
 * Return an array () of class's fields (key = name - value = translated name)
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $objectClassName
 *          : The class name to get the field's list
 * @param boolean $onlyResource
 *          : Only field that reference a resource
 * @param boolean $withoutIdFkFields
 *          : Don't take the idXXX fields
 * @param boolean $withoutCreationDate
 *          : Don't take the creationDate and creationDateTime field
 * @param boolean $allFields
 *          : Take fields like _spe, _label, etc.
 *          --------------------------------------------------------------------------------------------------------------
 * @return array (key : fieldName => value : i18n(fieldName))
 *         --------------------------------------------------------------------------------------------------------------
 */
function getObjectClassTranslatedFieldsList($objectClassName="", $onlyResource=false, $withoutIdFkFields=false, $withoutCreationDate=true, $allFields=false) {
  if (trim($objectClassName)=="") {
    return [];
  }
  
  $fieldsList=getObjectClassFieldsList($objectClassName, $withoutCreationDate, $allFields);
  if (count($fieldsList)==0) {
    return $fieldsList;
  }
  
  $resourceClasses=getObjectClassesAssociatedToResourceDatabaseTable();
  
  $obj=new $objectClassName();
  $arrayFields=array();
  
  foreach ($fieldsList as $field) {
    // Don't take calculated fields
    // AND
    // hidden fields
    if (!$obj->isAttributeSetToField($field, "calculated") and !$obj->isAttributeSetToField($field, "hidden")) {
      if (substr($field, 0, 2)=="id" and strlen($field)>2 and $withoutIdFkFields) {
        continue;
      }
      $arrayFields[]=$field;
    }
  }
  
  $translatedArrayFields=array();
  foreach ($arrayFields as $field) {
    // Don't take not translated fields
    if (substr($obj->getColCaption($field), 0, 4)=='[col') {
      continue;
    }
    if ($onlyResource) {
      $class=substr($field, 2);
      if (in_array($class, $resourceClasses)) {
        $translatedArrayFields[$field]=$obj->getColCaption($field);
      }
    } else {
      $translatedArrayFields[$field]=$obj->getColCaption($field);
    }
  }
  
  return $translatedArrayFields;
}

// END - ADD BY TABARY - GENERIC FUNCTION TO GET CLASS's FIELDS LIST

// BEGIN - ADD BY TABARY - GENERIC FUNCTION TO GET CLASS's FIELDS LIST THAT HAVE 'Date' IN THEIR NAME
/**
 * =============================================================================================================
 * Return an array () of fields that have the dateType - Based on the field name
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $objectClassName
 *          : The class name to get the field's list
 * @param boolean $withCreationDate
 *          : If true = creationDate field is'nt take
 *          --------------------------------------------------------------------------------------------------------------
 * @return array (key - fieldName)
 *         --------------------------------------------------------------------------------------------------------------
 *         Ex : getUserObjectClassFieldsListWithDateType('Project')
 *         return the fields list for 'Project' that have the data type 'Date'
 */
function getObjectClassFieldsListWithDateType($objectClassName='', $withoutCreationDate=true) {
  if (trim($objectClassName)=='') {
    return array();
  }
  
  $obj=new $objectClassName();
  $array_fieldsDateType=array();
  // $i=0;
  foreach ($obj as $colName=>$val) {
    if ($colName!='date' and (strpos($colName, 'Date')===false or substr($colName, 0, 1)=='_')) continue;
    if ($colName=='handledDate' or $colName=='doneDate' or $colName=='idleDate') continue;
    if ($colName=='handledDateTime' or $colName=='doneDateTime' or $colName=='idleDateTime') continue;
    if ($obj->isAttributeSetToField($colName, 'hidden')) continue;
    if (($colName=='creationDate' or $colName=='creationDateTime' or $colName=='lastUpdateDateTime') and $withoutCreationDate===true) continue;
    $array_fieldsDateType[$colName]=$obj->getColCaption($colName);
  }
  return $array_fieldsDateType;
}
// END - ADD BY TABARY - GENERIC FUNCTION TO GET CLASS's FIELDS LIST THAT HAVE 'Date' IN THEIR NAME

// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM

/**
 * =============================================================================================================
 * Determine if the class passed in parameter is a notifiable class
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @param string $className
 *          : The class to determine if it is notifiable
 *          --------------------------------------------------------------------------------------------------------------
 * @return boolean True if the className is notificable
 *         --------------------------------------------------------------------------------------------------------------
 */
function isNotifiable($className) {
  $crit=array("idle"=>'0', "notifiableItem"=>$className);
  $obj=SqlElement::getSingleSqlElementFromCriteria('Notifiable', $crit);
  if ($obj->id) {
    $nd=new NotificationDefinition();
    $cpt=$nd->countSqlElementsFromCriteria(array('idNotifiable'=>$obj->id));
    if ($cpt>0) return true;
    else return false;
  } else {
    return false;
  }
  return false;
}

/**
 * =============================================================================================================
 * Return an array () of class that have one or more field with the dateType - Based on the field name
 * --------------------------------------------------------------------------------------------------------------
 * --------------------------------------------------------------------------------------------------------------
 * 
 * @return array (key - ClassName)
 *         --------------------------------------------------------------------------------------------------------------
 */
function getUserVisibleObjectClassWithFieldDateType() {
  $arrayClass=getUserVisibleObjectClassesList();
  $arrayClassWithDateTypeFields=array();
  $i=0;
  foreach ($arrayClass as $key=>$className) {
    if ($className!="Plugin" and $className!="NotificationDefinition" and count(getObjectClassFieldsListWithDateType($className))>0) {
      $arrayClassWithDateTypeFields[$i]=$className;
      $i++;
    }
  }
  sort($arrayClassWithDateTypeFields);
  return $arrayClassWithDateTypeFields;
}

/**
 * ================================================================
 * Return true if notification system is activ
 * 
 * @return boolean
 */
function isNotificationSystemActiv() {
  //if (Parameter::getGlobalParameter('notificationSystemActiv')==="NO") {
  if (! Module::isModuleActive('moduleNotification')) {
    return false;
  } else {
    $notifCheckDelay=Parameter::getGlobalParameter('cronCheckNotifications');
    if (intval($notifCheckDelay)>0) {
      return true;
    } else {
      return false;
    }
  }
}

// END - ADD BY TABARY - NOTIFICATION SYSTEM

// ADD BY Marc TABARY - 2017-02-21 - RESOURCE VISIBILITY FUNCTION OF 'teamOrga'
// Adding because, used in html.php, jsonList.php, objectDetail.php and other
/**
 * ================================================================
 * Return the visible resources list for the user in function of 'teamOrga' parameters
 * 
 * @param boolean $limitToActiveResources          
 * @param string $listScreen
 *          ('List' for visibility on Resources combobox - 'Screen' for visibility on Resources Screen list
 * @param boolean $idLinkObjectName
 *          = '' if not restrict on idLinkObjectName - true if restrict to resource with idLinkObject null
 * @return list of visible resources
 */
function getUserVisibleResourcesList($limitToActiveResources=false,
                                     $listScreen="List", 
                                     $idLinkObjectName='', 
                                     $includePool=false, 
                                     $limitToEmployee=false,
                                     $limitToManagedEmployee=false,
                                     $selfIncluded=false,
                                     $limitToUser=false,
                                     $idProject=null) {
  $crit="";
  if ($limitToActiveResources) {
    $crit="idle=0 and ";
  }
    
// MTY - LEAVE SYSTEM
    if ($limitToManagedEmployee) {
        // Leave Admin => Can see all employees
        if(isLeavesAdmin()) {
            $res = new Resource();
            $emplList = $res->getEmployeesList($limitToActiveResources,$limitToUser);
            if ($selfIncluded and getSessionUser()->isEmployee) {
                if (!array_key_exists(getSessionUser()->id, $emplList)) {
                    $emplList[getSessionUser()->id] = getSessionUser()->name;
                }                
            }
            return $emplList;
        } 
        // Leave Manager - Can see it's managedEmployee
        elseif (isLeavesManager()) {
            $manager = new EmployeeManager(getSessionUser()->id);
            $emplList = $manager->getManagedEmployees(true,null,false,$limitToUser);
            if ($selfIncluded) {
                if ($emplList==null) {
                    $emplList[getSessionUser()->id] = getSessionUser()->name;                    
                } else {
                    if (!array_key_exists(getSessionUser()->id, $emplList) and getSessionUser()->isEmployee) {
                        $emplList[getSessionUser()->id] = getSessionUser()->name;
                    }
                }
            }
            return $emplList;
        }
    }

    if ($limitToEmployee) {
        if($selfIncluded and getSessionUser()->isEmployee) {
            $crit .= "(isEmployee=1 or id=". getSessionUser()->id.") and ";
        } else {
            $crit .= "isEmployee=1 and ";
        }    
    }
    if ($limitToUser) {
        $crit .= " isUser=1 and ";
    }
// MTY - LEAVE SYSTEM

  if ($idLinkObjectName!='' and property_exists('Resource', $idLinkObjectName)) {
    $crit.=$idLinkObjectName." is null and ";
  }
  $resourcesList=array();
  $res=new ResourceAll();
  if ($includePool) $res=new ResourceAll();
  $scope=Affectable::getVisibilityScope($listScreen,$idProject);
  switch ($scope) {
    case 'all' :
      $crit.='(1=1)';
      break;
    case 'orga' :
      if (Organization::getUserOrganization()) {
        $crit.="idOrganization = ".Organization::getUserOrganization();
      } else {
        $crit.="idOrganization is null";
      }
      break;
    case 'subOrga' :
      if (Organization::getUserOrganization()) {
        $crit.="idOrganization in (".Organization::getUserOrganizationList().")";
      } else {
        $crit.="idOrganization is null";
      }
      break;
    case 'team' :
      $aff=new Affectable(getSessionUser()->id, true);
      if ($aff->idTeam) {
        $crit.="idTeam=$aff->idTeam";
      } else {
        $crit.="idTeam is null";
      }
      break;
    default :
      traceLog("Error on getUserVisibleResourcesList() : Resource::getVisibilityScope returned something different from 'all', 'team', 'orga', 'subOrga'");
      $crit=array('id'=>'0');
      break;
  }
  
  $list=$res->getSqlElementsFromCriteria(null, false, $crit);
  foreach ($list as $res) {
    $resourcesList[$res->id]=$res->name;
  }
  
  return $resourcesList;
}
// END ADD BY Marc TABARY - 2017-02-21 - RESOURCE VISIBILITY FUNCTION OF 'teamOrga'

/**
 * ============================================================================
 * Set up the locale
 * May be found in request : transmitted from dojo (javascript)
 *
 * @return void
 */
function setupLocale() {
  global $currentLocale, $browserLocale, $browserLocaleDateFormat;
  $paramDefaultLocale=Parameter::getGlobalParameter('paramDefaultLocale');
  $paramUserLocale=Parameter::getGlobalParameter('currentLocale');
  $paramUserLang=Parameter::getGlobalParameter('lang');
  if (sessionValueExists('currentLocale')) {
    // First fetch in Session (filled in at login depending on user parameter)
    $currentLocale=getSessionValue('currentLocale');
  } else if (isset($_REQUEST['currentLocale'])) {
    // Second fetch from request (for screens before user id identified)
    $currentLocale=trim($_REQUEST['currentLocale']);
    Security::checkValidLocale($currentLocale);
    setSessionValue('currentLocale', $currentLocale);
    $i18nMessages=null; // Should be null at this moment, just to be sure
  } else if ($paramUserLocale) {
    $currentLocale=$paramUserLocale;
  } else if ($paramUserLang) {  
    $currentLocale=$paramUserLang;
  } else {
    // none of the above methods worked : get the default one form parameter file
    $currentLocale=$paramDefaultLocale;
  }
  if (sessionValueExists('browserLocale')) {
    $browserLocale=getSessionValue('browserLocale');
  } else {
    $browserLocale=$currentLocale;
  }
  setSessionValue('lang', $currentLocale); // Must be kept for user parameter screen initialization
  if (sessionValueExists('browserLocaleDateFormat')) {
    $browserLocaleDateFormat=getSessionValue('browserLocaleDateFormat');
  } else if (! $browserLocaleDateFormat) {
    if ($currentLocale=='fr' or $currentLocale=='de') {
      $browserLocaleDateFormat='DD/MM/YYYY';
    } else if ($currentLocale=='en') {
      $browserLocaleDateFormat='MM/DD/YYYY';
    } else {
      $browserLocaleDateFormat='YYYY-MM-DD';
    } 
  }
}

/**
 * ============================================================================
 * Set up the icon size, converting session text value (small, medium, big)
 * to int corresponding value (16, 22, 32)
 *
 * @return void
 */

/**
 * ============================================================================
 * Internationalization / same function exists in js exploiting same resources
 *
 * @param $str the
 *          code of the message to search and translate
 * @return the translated message (or the input message if not found)
 */
function i18n($str, $vars=null) {
  // **********************************************************
  // IMPORTANT
  // ==========================================================
  // This procedure is called before any parameter is set
  // So don't use any database access (objects use db)
  // and don't use any log function (such as traceLog or other debug tracing function)
  global $i18nMessages, $currentLocale;
  $i18nSessionValue='i18nMessages'.((isset($currentLocale))?$currentLocale:'');
  // on first use, initialize $i18nMessages
  if (!$i18nMessages) { // Try and retrieve from session : not activated as not performance increased
    $i18nMessages=getSessionValue($i18nSessionValue, null, false);
  }
  if (!$i18nMessages) {
    $filename="../tool/i18n/nls/lang.js";
    $i18nMessages=array();
    if (isset($currentLocale)) {
      $testFile="../tool/i18n/nls/".$currentLocale."/lang.js";
      if (file_exists($testFile)) {
        $filename=$testFile;
      }
    }
    $file=fopen($filename, "r");
    while ($line=fgets($file)) {
      $split=explode(":", $line);
      if (isset($split[1])) {
        $var=trim($split[0], ' ');
        $valTab=explode(",", $split[1]);
        $val=trim($valTab[0], ' ');
        $val=trim($val, '"');
        $i18nMessages[$var]=$val;
      }
    }
    fclose($file);
    
    // Retrieve Plugin Translation files ==============================
    $langFileList=array();
    $pluginList=Plugin::getInstalledPluginNames();
    $locale=(isset($currentLocale))?$currentLocale:'';
    foreach ($pluginList as $plugin) {
      $testLocale=Plugin::getDir().'/'.$plugin.'/nls/'.$locale."/lang.js";
      $testDefault=Plugin::getDir().'/'.$plugin."/nls/lang.js";
      if ($locale and file_exists($testLocale)) {
        $langFileList[$plugin]=$testLocale;
      } else if (file_exists($testDefault)) {
        $langFileList[$plugin]=$testDefault;
      }
    }
    
    // extra for personalizedTranslations plugin : old format (for plugin version < 1.0)
    $testLocale="../plugin/personalizedTranslations/".$currentLocale."/lang.js";
    if (file_exists($testLocale)) {
      $langFileList['personalizedTranslationsLangOld']=$testLocale;
    }
    // extra for personalizedTranslations plugin : new format (for plugin version >= 1.0)
    $testLocale="../plugin/nls/".$currentLocale."/lang.js";
    $testDefault="../plugin/nls/lang.js";
    if (file_exists($testLocale)) {
      $langFileList['personalizedTranslationsLang']=$testLocale;
    } else if (file_exists($testDefault)) {
      $langFileList['personalizedTranslationsLang']=$testDefault;
    }
    foreach ($langFileList as $testFile) {
      if (file_exists($testFile)) {
        $filename=$testFile;
        $file=fopen($filename, "r");
        while ($line=fgets($file)) {
          $split=explode(":", $line);
          if (isset($split[1])) {
            $var=trim($split[0], ' ');
            $valTab=explode(",", $split[1]);
            $val=trim($valTab[0], ' ');
            $val=trim($val, '"');
            $i18nMessages[$var]=$val;
          }
        }
        fclose($file);
      }
    }
    if (!isset($i18nNocache) or $i18nNocache==false) { // To help dev, do not cache captions
                                                          // setSessionValue($i18nSessionValue,$i18nMessages,false); // does not improve unitary perfs, but may on high loaded server
    }
  }
  // fetch the message in the array
  if (array_key_exists($str, $i18nMessages)) {
    $ret=$i18nMessages[$str];
    if ($vars) {
      foreach ($vars as $ind=>$var) {
        $rep='${'.($ind+1).'}';
        $ret=str_replace($rep, $var, $ret);
      }
    }
    return $ret;
  } else {
    return "[".$str."]"; // return a defaut value if message code not found
  }
}

/**
 * ============================================================================
 * Return the layout for a grid with the columns header translated (i18n)
 *
 * @param $layout the
 *          layout string
 * @return the translated layout
 */
/*
 * function layoutTranslation($layout) { $deb=strpos($layout,'${'); while ($deb) { $fin=strpos($layout,'}',$deb); if (! $fin) {exit;} $rep=substr($layout,$deb,$fin-$deb+1); $col=substr($rep,2, strlen($rep) - 3); $col=i18n('col' . ucfirst($col)); $layout=str_replace( $rep, $col, $layout); $deb=strpos($layout,'${'); } return $layout; }
 */

/**
 * ============================================================================
 * Exception management
 *
 * @param $exeption the
 *          exception
 * @return void
 */
function exceptionHandler($exception) {
  global $globalSilentErrors,$cronnedScript, $printInfo;
  if ($globalSilentErrors) {
    return true;
  }
  $logLevel=Parameter::getGlobalParameter('logLevel');
  errorLog("EXCEPTION *****");
  errorLog("on file '".$exception->getFile()."' at line (".$exception->getLine().")");
  errorLog("cause = ".$exception->getMessage());
  $trace=$exception->getTrace();
  foreach ($trace as $indTrc=>$trc) {
    if (isset($trc['file']) and isset($trc['line']) and isset($trc['function'])) {
      errorLog("   => #".$indTrc." ".$trc['file']." (".$trc['line'].")"." -> ".$trc['function']."()");
    }
  }
  if (isset($printInfo) and is_array($printInfo) and count($printInfo)>0) {  
    errorLog("   => Error on print for parameters:");
    if (isset($printInfo['page'])) errorLog("      -> page : ".$printInfo['page']);
    if (isset($printInfo['objectClass'])) errorLog("      -> object class : ".$printInfo['objectClass']);
    if (isset($printInfo['objectId'])) errorLog("      -> object id : ".$printInfo['objectId']);
    if (isset($printInfo['reportName'])) errorLog("      -> report name : ".$printInfo['reportName']);
  }  
  if ($cronnedScript==true) {
    // PBER : 2020-09-11 : retart fails, so CRON is stopped, not restarted
    // Exception in Cron Process => try and restart CRON
    //traceLog("Exception while executing script : try and restart");
    //Cron::setRestartFlag();
    //exit;
    errorLog($exception->getMessage());
    errorLog("Exception while executing CRON script : fix the source issue and manually restart the CRON Process");
  } else if ($logLevel>=3) {
    throwError($exception->getMessage());
  } else {
    throwError(i18n('exceptionMessage', array(date('Y-m-d'), date('H:i:s'))));
  }
}

/**
 * ============================================================================
 * Error management
 *
 * @param $exeption the
 *          exception
 * @return void
 */
function errorHandler($errorType, $errorMessage, $errorFile, $errorLine) {
  global $globalCatchErrors, $globalSilentErrors,$cronnedScript;
  $logLevel=Parameter::getGlobalParameter('logLevel');
  if ($globalSilentErrors) {
    return true;
  }
  if (!strpos($errorMessage, "getVersion.php") and !strpos($errorMessage, "file-get-contents") and !strpos($errorMessage, "function.session-destroy")) {
    errorLog("ERROR *****");
    errorLog("on file '".$errorFile."' at line (".$errorLine.")");
    errorLog("cause = ".$errorMessage);
  }
  if ($globalCatchErrors) {
    return true;
  }
  if ($cronnedScript==true) {
    // PBER : 2020-09-11 : retart fails, so CRON is stopped, not restarted
    // Error in Cron Process => try and restart CRON
    //traceLog("Error while executing script : try and restart");
    //Cron::setRestartFlag();
    //exit;
    errorLog($errorMessage." in ".basename($errorFile)." at line ".$errorLine);
    errorLog("Error while executing CRON script : fix the source issue and manually restart the CRON Process");
  } else if ($logLevel>=3) {
    throwError($errorMessage."<br/>&nbsp;&nbsp;&nbsp;in ".basename($errorFile)."<br/>&nbsp;&nbsp;&nbsp;at line ".$errorLine, true);
  } else {
    throwError(i18n('errorMessage', array(date('Y-m-d'), date('H:i:s'))));
  }
}

function enableCatchErrors() {
  global $globalCatchErrors;
  $globalCatchErrors=true;
}

function disableCatchErrors() {
  global $globalCatchErrors;
  $globalCatchErrors=false;
}

function enableSilentErrors() {
  global $globalSilentErrors;
  $globalSilentErrors=true;
}

function disableSilentErrors() {
  global $globalSilentErrors;
  $globalSilentErrors=false;
}

function traceHack($msg="Unidentified source code") {
  errorLog("HACK ================================================================");
  errorLog("Try to hack detected");
  errorLog(" Source Code = ".$msg);
  errorLog(" QUERY_STRING = ".$_SERVER['QUERY_STRING']);
  errorLog(" REMOTE_ADDR = ".$_SERVER['REMOTE_ADDR']);
  errorLog(" SCRIPT_FILENAME = ".$_SERVER['SCRIPT_FILENAME']);
  if (intval(Parameter::getGlobalParameter('logLevel'))>=2) {
  	debugPrintTraceStack();
  }
  // FIX FOR IIS
  if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI']=substr($_SERVER['PHP_SELF'], 1);
    if (isset($_SERVER['QUERY_STRING'])) {
      $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
    }
  }
  errorLog(" REQUEST_URI = ".$_SERVER['REQUEST_URI']);
  require "../tool/hackMessage.php"; // Will call exit
                                       // exit; / exit is called in hackMessage
}

function securityCheckPage($page) {
  if (!trim($page)) return; // Not control empty value
  $path=$page;
  $pos=strpos($path, '?');
  if ($pos!==FALSE) { // there are parameters
    $path=substr($path, 0, $pos); // path up to parameters
  }
  if ((substr($path, -4)!=='.php')|| // verify that path ends with '.php'
(strpos($path, ":")!==FALSE)|| // verify $path does not use a URL wrapper
(file_exists($path)===FALSE)) { // verify $path is an actual file
    traceHack("securityCheckPage($page) - not .php or URL wrapper or not actual file");
    exit(); // Not required : traceHack already exits script
  }
  $allowed_folders=array(
      realpath("../tool/"), 
      realpath("../view/"), 
      realpath("../report/"), 
      realpath("../report/object/"), 
      realpath("../plugin/templateReport"),
      realpath("../sso/projeqtor/")
  );
  if (!in_array(dirname(realpath($path)), $allowed_folders)) {
    traceHack("securityCheckPage($page) - '".dirname(realpath($path))."' is not in allowed folders list");
    exit(); // Not required : traceHack already exits script
  }
  if (dirname(realpath($path))==realpath("../tool/") and substr($page,0,12)!='../tool/json' and substr($page,0,32)!='../tool/adminFunctionalities.php') {
    traceHack("securityCheckPage($page) - '".dirname(realpath($path))."' is not in allowed except for json queries");
    exit(); // Not required : traceHack already exits script
  }
  if (dirname(realpath($path))==realpath("../sso/projeqtor/") and substr($page,0,25)!='../sso/projeqtor/metadata') {
    traceHack("securityCheckPage($page) - '".dirname(realpath($path))."' is not in allowed except metadata");
    exit(); // Not required : traceHack already exits script
  }
}

/**
 * ============================================================================
 * Format error message, display it and exit script
 * NB : error messages are not using i18n (because it may be the origin of the error)
 * Error messages are always displayed in english (hard coded)
 *
 * @param $message string
 *          the message of the error to be returned
 * @param $code not
 *          used
 * @return void
 */
function throwError($message, $noEncode=false) {
  global $globalCatchErrors, $globalCronMode;
  if (isset($globalCronMode)) {
    traceLog("Cron error : ".$message);
    if ($globalCronMode==false) {
      traceLog("CRON IS STOPPED TO AVOID MULTIPLE-TREATMENT OF SAME FILES");
      exit();
    }
  } else {
    $msg=($noEncode)?$message:htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); // $noEncode used only on errorHandler : message is PHP error
    echo '<div class="messageERROR" >ERROR : '.$msg.'</div>';
    echo '<input type="hidden" id="lastSaveId" value="" />';
    echo '<input type="hidden" id="lastOperation" value="ERROR" />';
    echo '<input type="hidden" id="lastOperationStatus" value="ERROR" />';
    if (!$globalCatchErrors) {
      if (Sql::$maintenanceMode==true and file_exists("../files/cron/MIGRATION")) unlink("../files/cron/MIGRATION"); // If error during migration that stops the script, remove migration Flag
      exit();
    }
  }
}

/**
 * ============================================================================
 * Autoload fonction, to automatically load classes
 * Class file is searched in :
 * 1 => current directory (same as current script) [DISABLED]
 * 2 => model directory => all object model classes should be here
 * 3 => model/persistence => all Sql classes, to interact with database
 * 4 => tool directory [DISABLED]
 *
 * @param $className string
 *          the name of the class
 * @return void
 */
$hideAutoloadError=false;
function projeqtorAutoload($className) {
  global $hideAutoloadError;
  if (preg_match('/\.\./', trim($className))==true) {
    traceHack("Directory traversal in className = $className");
    exit();
  }
  if(strpos(strtolower($className), 'saml')){
    $hideAutoloadError=true;
  }
  $localfile=ucfirst($className).'.php'; // locally
  $customfile='../model/custom/'.$localfile; // Custom directory
  $modelfile='../model/'.$localfile; // in the model directory
  $persistfile='../model/persistence/'.$localfile; // in the model/persistence directory
  if (is_file($customfile)) {
    require_once $customfile;
  } elseif (is_file($modelfile)) {
    require_once $modelfile;
  } elseif (is_file($persistfile)) {
    require_once $persistfile;
  } else {
    if (!$hideAutoloadError) {
      errorLog("Impossible to load class $className<br/>"."  => Not found in $customfile <br/>"."  => Not found in $modelfile <br/>"."  => Not found in $persistfile <br/>");
      debugPrintTraceStack();
    }
    return false;
  }
}

/**
 * ============================================================================
 * Return the id of the current connected user (user stored in session)
 * If an weird data is detected (user not existing, user not of User class) an error is raised
 *
 * @return the current user id or raises an error
 */
function getCurrentUserId() {
  if (!sessionUserExists()) {
    throw new Exception("ERROR user does not exist");
    exit();
  }
  $user=getSessionUser();
  if (get_class($user)!='User') {
    throw new Exception("ERROR user is not a User object");
    exit();
  }
  return $user->id;
}

/**
 * Return an array of the objects difference between the two arrays passed in parameter
 * @param array $array1 : First Array of objects
 * @param type $array2 : Second array of objects
 * @return array
 */
function twoArraysObjects_diff($array1=array(), $array2=array()) {
    $result=array();
    
    foreach($array2 as $key=>$value) {
        if (!in_array($value,$array1)) {
            $result[$key] = $value;                
        }        
    }

    foreach($array1 as $key=>$value) {
        if (!in_array($value,$array2)) {
            $result[$key] = $value;                
        }        
    }
    
  return $result;
}

/**
 * ===========================================================================
 * New function that merges array, but preseves numeric keys (unlike array_merge)
 *
 * @param
 *          any number of arrays
 *          @retrun the arrays merged into one, preserving keys (even numeric ones)
 */
function array_merge_preserve_keys() {
  $params=func_get_args();
  $result=array();
  foreach ($params as &$array) {
    foreach ($array as $key=>&$value) {
      $result[$key]=$value;
    }
  }
  return $result;
}

function array_sum_preserve_keys() {
  $params=func_get_args();
  $result=array();
  $hiddenAff=false;
  foreach ($params as &$array) {
    if (! is_array($array)) {
      $hiddenAff=$array;
      continue;
    }
    foreach ($array as $key=>&$value) {
      if (isset($result[$key])) {
        if (! $hiddenAff) $result[$key]+=$value;
      } else {
        $result[$key]=$value;
      }
    }
  }
  return $result;
}
function array_max_preserve_keys() {
  $params=func_get_args();
  $result=array();
  $hiddenAff=false;
  foreach ($params as &$array) {
    if (! is_array($array)) {
      $hiddenAff=$array;
      continue;
    }
    foreach ($array as $key=>&$value) {
      if (isset($result[$key])) {
        if (! $hiddenAff) $result[$key]=max($result[$key],$value);
      } else {
        $result[$key]=$value;
      }
    }
  }
  return $result;
}
/**
 * ===========================================================================
 * Check if menu can be displayed, depending of user profile
 *
 * @param $menu the
 *          name of the menu to check
 * @return boolean, true if displayable, false either
 */
function securityCheckDisplayMenu($idMenu, $class=null, $user=null) {
  $menu=$idMenu;
  if (!$idMenu and $class) {
    $menu=SqlList::getIdFromName('MenuList', 'menu'.$class);
  }
  if (!$user and sessionUserExists()) {
    $user=getSessionUser();
  }
  if (!$user) {
    return false;
  }
  if (! Module::isMenuActive(SqlList::getNameFromId('Menu',$menu,false))) return false;
  $result=false;
// MTY - LEAVE SYSTEM
  if (isLeavesSystemMenuByMenuName("menu".$class)) {
    //return showLeavesSystemMenu("menu".$class);
    $showLeaveMenu=showLeavesSystemMenu("menu".$class);
    if ($class=="HumanResourceParameters") {
      $menuObj=new Menu();
      $subMenus=$menuObj->getSqlElementsFromCriteria(array('idMenu'=>$menu));
      foreach ($subMenus as $subMenu) {
        $showSubMenu=showLeavesSystemMenu($subMenu->name);
        if ($showSubMenu) {
          return 1;
        }
      }
      return 0;
    }
    if ($class=="Employee") {
      if (! $showLeaveMenu) return 0;
    } else {
      return $showLeaveMenu;
    }
  }
// MTY - LEAVE SYSTEM
  $typeAdmin=SqlList::getFieldFromId('Menu', $idMenu, 'isAdminMenu',false);
  if ($typeAdmin==0) {
    $allProfiles=$user->getAllProfiles();
    foreach ($allProfiles as $profile) {
      $crit=array();
      $crit['idProfile']=$profile;
      $crit['idMenu']=$menu;
      $obj=SqlElement::getSingleSqlElementFromCriteria('Habilitation', $crit);
      if ($obj->id!=null and $obj->allowAccess==1) {
        $result=true;
        break;
      }
    }
  } else {
    $crit['idProfile']=$user->idProfile;
    $crit['idMenu']=$menu;
    $obj=SqlElement::getSingleSqlElementFromCriteria('Habilitation', $crit);
    if ($obj->id!=null and $obj->allowAccess==1) {
      $result=true;
    }
  }
  return $result;
}

/**
 * ===========================================================================
 * Get the list of Project Id that are visible : the selected project and its
 * sub-projects
 * At the difference of User->getVisibleProjects(),
 * selected Project is taken into account
 *
 * @return the list of projects as a string of id
 */
function getVisibleProjectsList($limitToActiveProjects=true, $idProject=null) {
  if (sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1) {
    $limitToActiveProjects=false;
  }
  if (!sessionValueExists('project')) {
    return '( 0 )';
  }
  $arrayProj = array();
  if ($idProject) {
    $project=$idProject;
  } else {
    $project=getSessionValue('project');
    if(strpos($project, ",") != null){
    	$arrayProj = explode(",", $project);
    }
  }
  $keyVPL=(($limitToActiveProjects)?'TRUE':'FALSE').'_'.(($project)?$project:'*');
  if (!sessionValueExists('visibleProjectsList')) {
    setSessionValue('visibleProjectsList', array());
  }
  if (sessionTableValueExist('visibleProjectsList', $keyVPL)) {
    return getSessionTableValue('visibleProjectsList', $keyVPL);
  }
  if ($project=="*" or $project=='') {
    $user=getSessionUser();
    setSessionTableValue('visibleProjectsList', $keyVPL, transformListIntoInClause($user->getVisibleProjects($limitToActiveProjects)));
    return getSessionTableValue('visibleProjectsList', $keyVPL);
  }
  //damian
  $result='(0';
  if($arrayProj){
  	foreach($arrayProj as $idProj){
  	  $prj=new Project($idProj);
  	  $lstSubProj=$prj->getRecursiveSubProjectsFlatList($limitToActiveProjects, true);
  	  foreach($lstSubProj as $id=>$name){
  	    $subProjectsList[$id]=[$name];
  	  }
  	}
  }else{
    $prj=new Project($project);
    $subProjectsList=$prj->getRecursiveSubProjectsFlatList($limitToActiveProjects);
    if ($project!='*' and $project) {
    	$result.=', '.$project;
    }
  }
  foreach ($subProjectsList as $id=>$name) {
    if (!$id) continue;
    $result.=', '.$id;
  }
  $result.=')';
  setSessionTableValue('visibleProjectsList', $keyVPL, $result);
  return $result;
}

function getAccesRestrictionClause($objectClass, $alias=null, $showIdle=false, $excludeUserClause=false, $excludeResourceClause=false) {
  global $reportContext;
  // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
  if (!property_exists($objectClass, 'idProject') and $objectClass!='Notification') return '(1=1)'; // If not project depedant, no extra clause
                                                                                                    
  // if (! property_exists($objectClass,'idProject')) return '(1=1)'; // If not project depedant, no extra clause
                                                                                                    // END - ADD BY TABARY - NOTIFICATION SYSTEM
  
  $obj=new $objectClass();
  $user=getSessionUser();
  if (sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1) {
    $showIdle=true;
  }
  if ($alias) {
    $tableAlias=$alias.'.';
  } else if ($alias===false) {
    $tableAlias=''; // No alias for table
  } else {
    $tableAlias=$obj->getDatabaseTableName().'.';
  }
  // Retrieve acces right for default profile
  if (isset($reportContext) and $reportContext==true) {
    $accessRightRead=securityGetAccessRight($obj->getMenuClass(), 'report');
  } else {
    $accessRightRead=securityGetAccessRight($obj->getMenuClass(), 'read');
  }
  $listNO=transformListIntoInClause($user->getAccessRights($objectClass, 'NO', $showIdle));
  $listOWN=(property_exists($obj, "idUser"))?transformListIntoInClause($user->getAccessRights($objectClass, 'OWN', $showIdle)):null;
  $listRES=(property_exists($obj, "idResource"))?transformListIntoInClause($user->getAccessRights($objectClass, 'RES', $showIdle)):null;
  $listPRO=transformListIntoInClause($user->getAccessRights($objectClass, 'PRO', $showIdle));
  $listALL=transformListIntoInClause($user->getAccessRights($objectClass, 'ALL', $showIdle));
  $listALLPRO=transformListIntoInClause($user->getAccessRights($objectClass, 'ALL', $showIdle)+$user->getAccessRights($objectClass, 'PRO', $showIdle));
  
  $clauseNO='(1=2)'; // Will dintinct the NO
  
  $clauseOWN='';
  if (!$excludeUserClause and property_exists($obj, "idUser") and substr($alias, -15)!='planningelement') {
    $clauseOWN="(".$tableAlias."idUser='".Sql::fmtId(getSessionUser()->id)."')";
  } else {
    $clauseOWN="(1=3)"; // Will distinct the OWN
  }
  
  $clauseRES='';
  if (!$excludeResourceClause and property_exists($obj, "idResource") and substr($alias, -15)!='planningelement') {
    $clauseRES="(".$tableAlias."idResource='".Sql::fmtId(getSessionUser()->id)."')";
  } else {
    $clauseRES="(1=4)"; // Will distinct the RES
  }
  
  // $clausePRO='';
  $clauseAffPRO='';
  $fieldProj='idProject';
  $extraFieldCriteria='';
  $extraFieldCriteriaReverse='';
  if ($objectClass=='Project') {
    if ($alias=='planningelement') {
      $fieldProj='refId';
      $extraFieldCriteria=" and refType='Project'";
      $extraFieldCriteriaReverse=" or refType!='Project'";
    } else {
      $fieldProj='id';
    }
  }
  // if ($objectClass == 'Document' or $objectClass=='TestCase' or $objectClass=='Requirement' or $objectClass=='TestSession') {
  if (property_exists($objectClass, 'idProject') and !$obj->isAttributeSetToField('idProject', 'required') and property_exists($objectClass, 'idProduct')) {
    $v=new Version();
    $vp=new VersionProject();
    $clauseALLPRO="(".$tableAlias."idProject in ".$listALLPRO." or (".$tableAlias."idProject is null and ".$tableAlias."idProduct in "."(select idProduct from ".$v->getDatabaseTableName()." existV, ".$vp->getDatabaseTableName()." existVP "."where existV.id=existVP.idVersion and existVP.idProject in ".$listALLPRO.")))";
  } else {
    // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
    if ($objectClass=='Notification') {
      $clauseALLPRO="(1=10)";
    } else {
      // END - ADD BY TABARY - NOTIFICATION SYSTEM
      // $clausePRO= "(".$tableAlias.$fieldProj." in ".transformListIntoInClause($user->getAffectedProjects(!$showIdle)).")";
      $clauseALLPRO="(".$tableAlias.$fieldProj." in ".$listALLPRO.")";
    }
  }
  
  $clauseALL='(1=1)'; // Will distinct the ALL
                      // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
  if ($extraFieldCriteriaReverse=="" and $objectClass=="Notification") {
    $extraFieldCriteriaReverse="(1=1)";
  }
  // END - ADD BY TABARY - NOTIFICATION SYSTEM
  
  // Build where clause depending
  if ($accessRightRead=='NO') { // Default profile is No Access
    $queryWhere=$clauseNO;
    // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
    if ($objectClass=='Notification') {
      if ($listOWN) $queryWhere.=" or ($clauseOWN $extraFieldCriteria)";
      if ($listRES) $queryWhere.=" or ($clauseRES $extraFieldCriteria)";
    } else {
      // END - ADD BY TABARY - NOTIFICATION SYSTEM
      if ($listOWN) $queryWhere.=" or ($clauseOWN and $tableAlias$fieldProj in $listOWN $extraFieldCriteria)";
      if ($listRES) $queryWhere.=" or ($clauseRES and $tableAlias$fieldProj in $listRES $extraFieldCriteria)";
    }
    $queryWhere.=" or ($clauseALLPRO)";
  } else if ($accessRightRead=='OWN') {
    $queryWhere="($clauseOWN";
    // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
    if ($objectClass=='Notification') {
      if ($listRES) $queryWhere.=" or ($clauseRES $extraFieldCriteria)";
      $queryWhere.=" or ($clauseALLPRO)";
      $queryWhere.=") and ($extraFieldCriteriaReverse)";
    } else {
      // END - ADD BY TABARY - NOTIFICATION SYSTEM
      if ($listRES) $queryWhere.=" or ($clauseRES and $tableAlias$fieldProj in $listRES $extraFieldCriteria)";
      $queryWhere.=" or ($clauseALLPRO)";
      $queryWhere.=") and ($tableAlias$fieldProj not in $listNO or $tableAlias$fieldProj is null $extraFieldCriteriaReverse)";
    }
  } else if ($accessRightRead=='RES') {
    $queryWhere="($clauseRES";
    // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
    if ($objectClass=='Notification') {
      if ($listOWN) $queryWhere.=" or ($clauseOWN $extraFieldCriteria)";
      $queryWhere.=" or ($clauseALLPRO)";
      $queryWhere.=") and ($extraFieldCriteriaReverse)";
    } else {
      // END - ADD BY TABARY - NOTIFICATION SYSTEM
      if ($listOWN) $queryWhere.=" or ($clauseOWN and $tableAlias$fieldProj in $listOWN $extraFieldCriteria)";
      $queryWhere.=" or ($clauseALLPRO)";
      $queryWhere.=") and ($tableAlias$fieldProj not in $listNO or $tableAlias$fieldProj is null $extraFieldCriteriaReverse)";
    }
  } else if ($accessRightRead=='PRO') {
    $queryWhere="($clauseALLPRO";
    // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
    if ($objectClass=='Notification') {
      if ($listRES) $queryWhere.=" or ($clauseRES $extraFieldCriteria)";
      if ($listOWN) $queryWhere.=" or ($clauseOWN $extraFieldCriteria)";
      $queryWhere.=") and ($extraFieldCriteriaReverse)";
    } else {
      // END - ADD BY TABARY - NOTIFICATION SYSTEM
      if ($listRES) $queryWhere.=" or ($clauseRES and $tableAlias$fieldProj in $listRES $extraFieldCriteria)";
      if ($listOWN) $queryWhere.=" or ($clauseOWN and $tableAlias$fieldProj in $listOWN $extraFieldCriteria)";
      // $queryWhere.=" or (".$clauseALLPRO.")";
      $queryWhere.=") and ($tableAlias$fieldProj not in $listNO or $tableAlias$fieldProj is null $extraFieldCriteriaReverse)";
    }
  } else if ($accessRightRead=='ALL') {
    // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
    if ($objectClass=='Notification') {
      $queryWhere="($extraFieldCriteriaReverse)";
      if ($listRES) $queryWhere.=" and ($clauseRES $extraFieldCriteria)";
      if ($listOWN) $queryWhere.=" and ($clauseOWN $extraFieldCriteria)";
    } else {
      // END - ADD BY TABARY - NOTIFICATION SYSTEM
      $queryWhere="($tableAlias$fieldProj not in $listNO or $tableAlias$fieldProj is null $extraFieldCriteriaReverse)";
      if ($listRES) $queryWhere.=" and ($tableAlias$fieldProj not in $listRES or $tableAlias$fieldProj is null or $clauseRES $extraFieldCriteriaReverse)";
      if ($listOWN) $queryWhere.=" and ($tableAlias$fieldProj not in $listOWN or $tableAlias$fieldProj is null or $clauseOWN $extraFieldCriteriaReverse)";
    }
  }
  return " (".$queryWhere.") ";
}

/**
 * ============================================================================
 * Return the name of the theme : defaut of selected by user
 */
function getTheme() {
  global $indexPhp;
  if (isNewGui()) return "ProjeQtOrFlatGrey ProjeQtOrNewGui";
  if (isset($indexPhp) and $indexPhp and getSessionValue('setup', null, true)) return "ProjeQtOr"; // On first configuration, use default
  $defaultTheme=Parameter::getGlobalParameter('defaultTheme');
  if (!$defaultTheme) $defaultTheme='ProjeQtOrFlatBlue';
  if (substr($defaultTheme, 0, 12)=="ProjectOrRia") {
    $defaultTheme="ProjeQtOr".substr($defaultTheme, 12);
  }
  $theme='ProjeQtOr'; // default if not always set
  if (isset($defaultTheme)) {
    $theme=$defaultTheme;
  }
  if (sessionValueExists('theme') and trim(getSessionValue('theme'))) {
    $theme=getSessionValue('theme');
  }
  if ($theme=="random") {
    $themes=array_keys(Parameter::getList('theme'));
    $rnd=rand(0, count($themes)-2);
    $theme=$themes[$rnd];
    setSessionValue('theme', $theme); // keep value in session to have same theme during all session...
  }
  if ($theme=='ProjeQtOrFlatGrey ProjeQtOrNewGui' and !isNewGui()) $theme='ProjeQtOrFlatBlue';
  return $theme;
}

function getThemesList() {
  return array('ProjeQtOrFlatBlue'=>i18n('themeProjeQtOrFlatBlue'),
      'ProjeQtOrFlatRed'=>i18n('themeProjeQtOrFlatRed'),
      'ProjeQtOrFlatGreen'=>i18n('themeProjeQtOrFlatGreen'),
      'ProjeQtOrFlatGrey'=>i18n('themeProjeQtOrFlatGrey'),
      'ProjeQtOrFlatMinimal'=>i18n('themeProjeQtOrFlatMinimal'),
      'ProjeQtOr'=>i18n('themeProjeQtOr'),
      'ProjeQtOrFire'=>i18n('themeProjeQtOrFire'),
      'ProjeQtOrForest'=>i18n('themeProjeQtOrForest'),
      'ProjeQtOrEarth'=>i18n('themeProjeQtOrEarth'),
      'ProjeQtOrWater'=>i18n('themeProjeQtOrWater'),
      'ProjeQtOrWine'=>i18n('themeProjeQtOrWine'),
      'ProjeQtOrDark'=>i18n('themeProjeQtOrDark'),
      'ProjeQtOrLight'=>i18n('themeProjeQtOrLight'),
      'Projectom'=>i18n('themeProjectom'),
      'ProjectomLight'=>i18n('themeProjectomLight'),
      'blueLight'=>i18n('themeBlueLight'),
      'blue'=>i18n('themeBlue'),
      'blueContrast'=>i18n('themeBlueContrast'),
      'redLight'=>i18n('themeRedLight'),
      'red'=>i18n('themeRed'),
      'redContrast'=>i18n('themeRedContrast'),
      'greenLight'=>i18n('themeGreenLight'),
      'green'=>i18n('themeGreen'),
      'greenContrast'=>i18n('themeGreenContrast'),
      'orangeLight'=>i18n('themeOrangeLight'),
      'orange'=>i18n('themeOrange'),
      'orangeContrast'=>i18n('themeOrangeContrast'),
      'greyLight'=>i18n('themeGreyLight'),
      'grey'=>i18n('themeGrey'),
      'greyContrast'=>i18n('themeGreyContrast'),
      'white'=>i18n('themeWhite'),
      'ProjectOrRia'=>i18n('themeProjectOrRIA'),
      'ProjectOrRiaContrasted'=>i18n('themeProjectOrRIAContrasted'),
      'ProjectOrRiaLight'=>i18n('themeProjectOrRIALight'),
      'random'=>i18n('themeRandom'));
}
/**
 * ===========================================================================
 * Send a mail
 *
 * @param $to the
 *          receiver of message
 * @param $title title
 *          of the message
 * @param $message the
 *          main body of the message
 * @return unknown_type
 */
function sendMail($to, $subject, $messageBody, $object=null, $headers=null, $sender=null, $attachmentsArray=null, $boundary=null, $references=null,$canSend=false,$autoSendReport=false,$attachments=false,$erroSize=false,$tempAttach=false) {
  // Code that caals sendMail :
  // + SqlElement::sendMailIfMailable() : sendMail($dest, $title, $message, $this)
  // + Cron::checkImport() : sendMail($to, $title, $message, null, null, null, $attachmentsArray, $boundary); !!! with attachments
  // + IndicatorValue::send() : sendMail($dest, $title, $messageMail, $obj)
  // + Meeting::sendMail() : sendMail($destList, $this->name, $vcal, $this, $headers,$sender) !!! VCAL Meeting Invite
  // + User::authenticate : sendMail($paramAdminMail, $title, $message)
  // + /tool/sendMail.php : sendMail($dest,$title,$msg)
  global $targetDirImageUpload, $cronnedScript, $cronnedMailSender;
  $messageBody=str_replace($targetDirImageUpload, SqlElement::getBaseUrl().substr(str_replace("..", "", $targetDirImageUpload), 0, strlen(str_replace("..", "", $targetDirImageUpload))-1), $messageBody);
  $paramMailSendmailPath=Parameter::getGlobalParameter('paramMailSendmailPath');
  $paramMailSmtpUsername=Parameter::getGlobalParameter('paramMailSmtpUsername');
  $paramMailSmtpPassword=Parameter::getGlobalParameter('paramMailSmtpPassword');
  $paramMailerType=strtolower(Parameter::getGlobalParameter('paramMailerType'));
  $paramMailSender=Parameter::getGlobalParameter('paramMailSender');
  if ($cronnedScript) $sender=$paramMailSender;
  // florent
  if(Parameter::getUserParameter('notReceiveHisOwnEmails')=='YES' and $canSend==false and $autoSendReport!=true and ! $cronnedScript ){
    $curUser=new Affectable(getSessionUser()->id);
    if($curUser->email and strpos($to,$curUser->email)!==false){
      $to=trim(str_replace($curUser->email,"",$to));
      $to=str_replace(';;',';',$to);
      if($to!="" ){
        //$mail->mailTo=$to;
      }else{
        return false;
      }
    }
  }
  if (!isset($paramMailerType) or $paramMailerType=='' or $paramMailerType=='phpmailer') {
    // Cute method using PHPMailer : should work on all situations / First implementation on V4.0
    return sendMail_phpmailer($to, $subject, $messageBody, $object, $headers, $sender, $attachmentsArray, $references,false,$autoSendReport,$attachments,$erroSize,$tempAttach);
  } else {
    $messageBody=wordwrap($messageBody, 70);
    if ((isset($paramMailerType) and $paramMailerType=='mail') or !$paramMailSmtpUsername or !$paramMailSmtpPassword) {
      // Standard method using php mail function : do not take authentication into account
      return sendMail_mail($to, $subject, $messageBody, $object, $headers, $sender, $boundary, $references);
    } else {
      // Authentified method using sockets : cannot send vCalendar or mails with attachments
      return sendMail_socket($to, $subject, $messageBody, $object, $headers, $sender, $boundary);
    }
  }
}

function sendMail_phpmailer($to, $title, $message, $object=null, $headers=null, $sender=null, $attachmentsArray=null, $references=null,$canSend=false,$autoSendReport=null,$attachments=false,$erroSize=false,$tempAttach=false) {
  scriptLog('sendMail_phpmailer');
  global $logLevel,$cronnedMailSender;
  $paramMailSender=Parameter::getGlobalParameter('paramMailSender');
  // The user is stored in session , if you try to changed email of the admin , you need to disconnect/reconnect for have the new email in sender
  $user=getSessionUser();
  $senderMailAdmin=($user->email)?$user->email:$paramMailSender;
  // $paramMailSender = $senderMailAdmin;
  $paramMailReplyTo=Parameter::getGlobalParameter('paramMailReplyTo');
  $paramMailSmtpServer=Parameter::getGlobalParameter('paramMailSmtpServer');
  $paramMailSmtpPort=Parameter::getGlobalParameter('paramMailSmtpPort');
  $paramMailSendmailPath=Parameter::getGlobalParameter('paramMailSendmailPath');
  $paramMailSmtpUsername=Parameter::getGlobalParameter('paramMailSmtpUsername');
  $paramMailSmtpPassword=Parameter::getGlobalParameter('paramMailSmtpPassword');
  $paramMailSenderName=Parameter::getGlobalParameter('paramMailReplyToName');
  $paramMailerHelo=Parameter::getGlobalParameter('paramMailerHelo');
  $eol=Parameter::getGlobalParameter('mailEol');
  if ($paramMailSmtpServer==null or strtolower($paramMailSmtpServer)=='null' or !$paramMailSmtpServer) {
    return "";
  }
  // Save data of the mail ===========================================================
  $addAttachToMessage="";
  if($tempAttach=='Yes'){
    if($erroSize!=''){
      $addAttachToMessage="<div style='color:red;'>".$erroSize."</div>";
    }
  }
  $message.=$addAttachToMessage;
  $mail=new Mail();
  if ($cronnedMailSender and $cronnedMailSender!=null) {
    $mail->idUser=$cronnedMailSender;
  } else if (sessionUserExists()) {
    $mail->idUser=getSessionUser()->id;
  }
  if ($object) {
    $mail->idProject=(property_exists($object, 'idProject'))?$object->idProject:null;
    $mail->idMailable=SqlList::getIdFromTranslatableName('Mailable', get_class($object));
    $mail->refId=$object->id;
    $mail->idStatus=(property_exists($object, 'idStatus'))?$object->idStatus:null;
  }
  $mail->mailDateTime=date('Y-m-d H:i');
  $mail->mailTo=$to;
  $mail->mailTitle=$title;
  $mail->mailBody=$message;
  $mail->mailStatus='WAIT';
  $mail->idle='0';
  $resMail=$mail->save();
  if (stripos($resMail, 'id="lastOperationStatus" value="ERROR"')>0) {
    errorLog("Error storing email in table : ".$resMail);
  }
  enableCatchErrors();
  $resultMail="NO";
  
  // require_once '../external/PHPMailer/class.phpmailer.php';
  // require_once '../external/PHPMailer/class.smtp.php';
  require_once '../external/PHPMailer/src/Exception.php';
  require_once '../external/PHPMailer/src/PHPMailer.php';
  require_once '../external/PHPMailer/src/SMTP.php';
  
  $phpmailer=new PHPMailer();
  $phpmailer->CharSet="UTF-8";
  ob_start();
  if ($logLevel>='3') $phpmailer->SMTPDebug=4;
  // if #3077
  if ($paramMailerHelo=='YES') {
    $phpmailer->Helo='['.$_SERVER['SERVER_ADDR'].']';
    // $phpmailer->Helo = '82.243.121.187';
  }
  $phpmailer->isSMTP(); // Set mailer to use SMTP
  $phpmailer->Host=$paramMailSmtpServer; // Specify main smtp server
  $phpmailer->Port=$paramMailSmtpPort;
  $phpmailer->SMTPOptions=array('ssl'=>['verify_peer'=>false, 'verify_peer_name'=>false, 'allow_self_signed'=>true]); // To be applied even for not authenticated connections as phpMailer may force TLS/SSL
  if ($paramMailSmtpUsername and $paramMailSmtpPassword) {
    $phpmailer->SMTPAuth=true; // Enable SMTP authentication
    $phpmailer->Username=$paramMailSmtpUsername; // SMTP username
    $phpmailer->Password=$paramMailSmtpPassword; // SMTP password
    $phpmailer->SMTPSecure='tls'; // default (for ports 25 and 587
                                    // gautier phpMailer version 6.0.3
    $phpmailer->SMTPOptions=array('ssl'=>['verify_peer'=>false, 'verify_peer_name'=>false, 'allow_self_signed'=>true]);
    if ($paramMailSmtpPort=='465') $phpmailer->SMTPSecure='ssl'; // 465 is default for ssl
    if (strpos($phpmailer->Host, '://')!==false) {
      $phpmailer->SMTPSecure=substr($phpmailer->Host, 0, strpos($phpmailer->Host, '://'));
      if ($phpmailer->SMTPSecure=="smtp") $phpmailer->SMTPSecure="";
      $phpmailer->Host=substr($phpmailer->Host, strpos($phpmailer->Host, '://')+3);
    }
  }
  $allowSendFromCurrentUser=Parameter::getGlobalParameter('paramMailerSendAsCurrentUser');
  $phpmailer->From=($allowSendFromCurrentUser=='YES' and $sender)?$sender:$paramMailSender; // Sender of email
  $phpmailer->FromName=$paramMailSenderName; // Name of sender
  $toList=explode(';', str_replace(',', ';', $to));
  foreach ($toList as $addrMail) {
    $addrName=null;
    if (strpos($addrMail, '<')) {
      $addrName=substr($addrMail, 0, strpos($addrMail, '<'));
      $addrName=str_replace('"', '', $addrName);
      $addrMail=substr($addrMail, strpos($addrMail, '<'));
      $addrMail=str_replace(array('<', '>'), array('', ''), $addrName);
    }
    $phpmailer->addAddress($addrMail, $addrName); // Add a recipient with optional name
  }
  $phpmailer->addReplyTo($paramMailReplyTo, $paramMailSenderName); //
  $phpmailer->WordWrap=70; // Set word wrap to 70 characters
  $phpmailer->isHTML(true); // Set email format to HTML
  $phpmailer->Subject=$title; //
  // $phpmailer->AltBody = 'Your email client does not support HTML format. The message body cannot be displayed';
                                
  // TODO : FOR OUTLOOK // TEST PBE FOR V7.0
  if ($headers) {
    /*
    // Test V1 - Up to 7.2.7 => not opened on Outlook 2010
    //$phpmailer->ContentType = 'multipart/alternative';
    $phpmailer->addStringAttachment($message, "meeting.ics", "7bit", 'text/calendar; charset="utf-8"; method="REQUEST"');
    $heads=explode("\r\n", $headers);
    // PHPMailer
    $phpmailer->Body=" ";
    //$phpmailer->Body = $message;
    */
    
     // Test VOK - Tested on Outlook, Thunderbird, Gmail, Zimbra, Sogo
     $phpmailer->ContentType = 'text/calendar; charset="utf-8"; method="REQUEST"';
     $heads = explode ( "\r\n", $headers );
     $phpmailer->Body = $message;

    /*
     // Test V3
     $phpmailer->ContentType = 'multipart/alternative';
     $phpmailer->addStringAttachment($message, "invite.ics", "7bit", "text/calendar; charset=utf-8; method=REQUEST");
     $heads=explode("\r\n", $headers);
     $phpmailer->Body = $message;
     */
    
    /*
     // Test V4
     $phpmailer->ContentType = 'multipart/alternative';
     $phpmailer->addStringAttachment($message, "invite.ics", "base64", "text/calendar;charset=utf-8; method=REQUEST");
     $heads=explode("\r\n", $headers);
     // PHPMailer
     $phpmailer->Body=" ";
     // $phpmailer->Body = $message;
     */
  } else {
    //florent ticket 4442 
    if($attachments!=''){
      foreach ($attachments as $id=>$fileAttach){
        if($fileAttach!="" and file_exists($fileAttach)){
          $phpmailer->addAttachment($fileAttach,$id);
        }else{
          traceLog("ERROR attachment : ".$id . ' not found');
        }
      }
    }
    $phpmailer->Body=$message; //
    $text=new Html2Text($message);
    $phpmailer->AltBody=$text->getText();
  }
  if ($references) {
    $phpmailer->addCustomHeader('References', '<'.$references.'.'.$paramMailSender.'>');
  }
  
  $phpmailer->CharSet="UTF-8";
  if ($attachmentsArray) { // attachments
    if (!is_array($attachmentsArray)) {
      $attachmentsArray=array($attachmentsArray);
    }
    foreach ($attachmentsArray as $attachment) {
      $phpmailer->AddAttachment($attachment);
    }
  }
  if (trim($paramMailSendmailPath)) {
    ini_set('sendmail_path', $paramMailSendmailPath);
    $phpmailer->IsSendmail();
  }
  $resultMail=$phpmailer->send();
  disableCatchErrors();
  $debugMessages=ob_get_contents();
  ob_end_clean();
  if (!$resultMail) {
    errorLog("Error sending mail");
    errorLog("   SMTP Server : ".$paramMailSmtpServer);
    errorLog("   SMTP Port : ".$paramMailSmtpPort);
    errorLog("   Mail stored in Database : #".$mail->id);
    errorLog("   PHPMail error : ".$phpmailer->ErrorInfo);
    errorLog("   PHPMail debug : ".$debugMessages);
    Mail::setLastErrorMessage($phpmailer->ErrorInfo.'<br/>'.$debugMessages);
  }
  if ($resultMail==="NO") {
    $resultMail="";
  }
  $mail->mailStatus=($resultMail)?'OK':'ERROR';
  $mail->save();
  return $resultMail;
}
function projeqtor_mb_str_split($str, $split_length) {
  if (function_exists('mb_str_split')) return mb_str_split($str, $split_length);
  $chars = array();
  $len = mb_strlen($str, 'UTF-8');
  for ($i = 0; $i < $len; $i+=$split_length ) {
    $chars[] = mb_substr($str, $i, $split_length, 'UTF-8');
  }
  return $chars;
}
function sendMail_socket($to, $subject, $messageBody, $object=null, $headers=null, $sender=null, $boundary=null) {
  scriptLog('sendMail_socket');
  global $cronnedMailSender;
  $paramMailSender=Parameter::getGlobalParameter('paramMailSender');
  // The user is stored in session , if you try to changed email of the admin , you need to disconnect/reconnect for have the new email in sender
  $user=getSessionUser();
  $senderMailAdmin=($user->email)?$user->email:$paramMailSender;
  // $paramMailSender = $senderMailAdmin;
  $paramMailReplyTo=Parameter::getGlobalParameter('paramMailReplyTo');
  error_reporting(E_ERROR);
  $debug=false; // set to FALSE in production code
  $newLine=Parameter::getGlobalParameter('mailEol'); // "\r\n";
  $timeout=30;
  // find location of script
  $path_info=pathinfo(__FILE__);
  $dir=$path_info['dirname'];
  chdir($dir);
  $replyToEmailAddress=Parameter::getGlobalParameter('paramMailReplyTo');
  $replyToEmailName=Parameter::getGlobalParameter('paramMailReplyToName');
  if (!$replyToEmailName) {
    $replyToEmailName=$replyToEmailAddress;
  }
  $key='default';
  $smtpHost=Parameter::getGlobalParameter('paramMailSmtpServer');
  if (!$smtpHost) {
    $resultMail='NO';
    $to="";
  }
  if (!strpos($smtpHost, '://')) {
    $smtpHost='ssl://'.$smtpHost;
  }
  $smtpServers['default']['server']=$smtpHost;
  $smtpServers['default']['userName']=Parameter::getGlobalParameter('paramMailSmtpUsername');
  $smtpServers['default']['passWord']=Parameter::getGlobalParameter('paramMailSmtpPassword');
  $smtpServers['default']['smtpPort']=Parameter::getGlobalParameter('paramMailSmtpPort');
  // Save data of the mail
  $mail=new Mail();
  if ($cronnedMailSender and $cronnedMailSender!=null) {
    $mail->idUser=$cronnedMailSender;
  } else if (sessionUserExists()) {
    $mail->idUser=getSessionUser()->id;
  }
  if ($object) {
    $mail->idProject=(property_exists($object, 'idProject'))?$object->idProject:null;
    $mail->idMailable=SqlList::getIdFromTranslatableName('Mailable', get_class($object));
    $mail->refId=$object->id;
    $mail->idStatus=(property_exists($object, 'idStatus'))?$object->idStatus:null;
  }
  $mail->mailDateTime=date('Y-m-d H:i');
  $mail->mailTo=$to;
  $mail->mailTitle=$subject;
  $mail->mailBody=$messageBody;
  $mail->mailStatus='WAIT';
  $mail->idle='0';
  $mail->save();
  //
  // start smtp
  enableCatchErrors();
  // Fix $To Formatting for SMTP clients
  $toArray=explode(",", $to);
  forEach ($toArray as &$to) {
    $to=trim($to);
    $resultMail=false;
    $sock=fsockopen($smtpServers[$key]['server'], $smtpServers[$key]['smtpPort'], $errno, $errstr, $timeout);
    if (!$sock) Mail::setLastErrorMessage('cannot connect server (socket mode)');
    break; // or loop over more smtp servers
    $res=fgets($sock, 515);
    if ($debug) errorLog($res."\n");
    if (!empty($res)) {
      // send "HELO"
      $cmd="HELO YOURSUBDOMAIN.YOURDOMAIN.com".$newLine; // you can change this into more relevant uri
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
      if (!isValidReturn($res, "250")) {
        Mail::setLastErrorMessage("invalid return for HELO in socket mode : '$res'");
        quit($sock);
        break;
      }
      // send "AUTH LOGIN"
      $cmd="AUTH LOGIN".$newLine;
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
      if (!isValidReturn($res, "334 VXNlcm5hbWU6")) {
        Mail::setLastErrorMessage("invalid return for AUTH in socket mode : '$res'");
        quit($sock);
        break;
      }
      // SEND USERNAME base64 encoded
      $cmd=base64_encode($smtpServers[$key]['userName']).$newLine;
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
      if (!isValidReturn($res, "334 UGFzc3dvcmQ6")) {
        Mail::setLastErrorMessage("invalid return for username input in socket mode : '$res'");
        quit($sock);
        break;
      }
      // SEND PASSWORD base64 encoded
      $cmd=base64_encode($smtpServers[$key]['passWord']).$newLine;
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
      if (!isValidReturn($res, "235")) {
        Mail::setLastErrorMessage("invalid return for password input in socket mode : '$res'");
        quit($sock);
        break;
      }
      // send SMTP command "MAIL FROM"
      $cmd="MAIL FROM: <".$paramMailSender.">".$newLine;
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
      if (!isValidReturn($res, "250")) {
        Mail::setLastErrorMessage("invalid return for MAIL FROM in socket mode : '$res'");
        quit($sock);
        break;
      }
      // tell the SMTP server who are the recipients
      $cmd="RCPT TO: "." <".$to.">".$newLine;
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
      if (!isValidReturn($res, "250")) {
        Mail::setLastErrorMessage("invalid return for RCPT TO in socket mode : '$res'");
        quit($sock);
        break;
      }
      // if more recipients add a line for each recipient
      // send SMTP command "DATA"
      $cmd="DATA".$newLine;
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
      if (!isValidReturn($res, "354")) {
        Mail::setLastErrorMessage("invalid return for DATA in socket mode : '$res'");
        quit($sock);
        break;
      }
      // send SMTP command containing whole message
      // comment out if not relevant
      $headers="TO: ".$to." <".$to.">".$newLine;
      $headers.="From: ".$replyToEmailName." <".$paramMailSender.">".$newLine;
      $headers.="Reply-To: ".$replyToEmailName." <".$replyToEmailAddress.">".$newLine;
      $headers.="Subject: ".$subject.$newLine;
      // Generate a mime boundary string
      $rnd_str=md5(time());
      $mime_boundary="==Multipart_Boundary_x{$rnd_str}x";
      $mime_alternative="==Multipart_Boundary_x{$rnd_str}altx";
      $altcontent="MIME-Version: 1.0".$newLine."Content-Type: multipart/alternative;"." boundary=\"{$mime_alternative}\" ".$newLine.$newLine;
      $altcontent.="This is a multi-part message in MIME format".$newLine.$newLine."--{$mime_alternative}".$newLine;
      $altcontent.="Content-Type: text/plain; charset=\"iso-8859-1\"".$newLine."Content-Disposition: inline".$newLine."Content-Transfer-Encoding: 7bit".$newLine.$newLine.strip_tags(preg_replace('#<[Bb][Rr]/?>#', PHP_EOL, $messageBody)).$newLine.$newLine."--{$mime_alternative}".$newLine;
      $altcontent.="Content-Type: text/html; charset=\"iso-8859-1\"".$newLine."Content-Disposition: inline".$newLine."Content-Transfer-Encoding: 7bit".$newLine.$newLine.'<html><body style="font-family: Verdana, Arial, Helvetica, sans-serif;">'.$messageBody.'</body></html>'.$newLine.$newLine."--{$mime_alternative}--".$newLine;
      // Add headers for file attachment
      $headers.=$altcontent;
      $headers.=$newLine.".".$newLine;
      $cmd=$headers;
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
      if (!isValidReturn($res, "250")) {
        Mail::setLastErrorMessage("invalid return for headers in socket mode : '$res'");
        quit($sock);
        break;
      }
      $resultMail=true; // ASSUME correct return for now
                          // tell SMTP we are done
      $cmd="QUIT".$newLine;
      fputs($sock, $cmd);
      $res=fgets($sock, 515);
      if ($debug) errorLog("+ $cmd- $res\n");
    }
  } // ENDING FOR EACH STATEMENT
  disableCatchErrors();
  error_reporting(E_ALL);
  if (!$resultMail) {
    errorLog("Error sending mail");
    $smtp=$smtpServers['default']['server'];
    errorLog("   SMTP Server : ".$smtp);
    $port=$smtpServers['default']['smtpPort'];
    errorLog("   SMTP Port : ".$port);
    $path=$smtpServers['default']['userName'];
    errorLog("   SMTP User : ".$path);
    errorLog("   Mail stored in Database : #".$mail->id);
  }
  if ($resultMail==="NO") {
    $resultMail="";
  }
  // save the status of the sending
  $mail->mailStatus=($resultMail)?'OK':'ERROR';
  $mail->save();
  return $resultMail;
}

//
// isValidReturn ()
//
// checks expected return over socket
//
function isValidReturn($ret, $expected) {
  $retLocal=trim($ret);
  $pos=strpos($retLocal, $expected);
  if ($pos===FALSE) return FALSE;
  if ($pos==0) return TRUE;
  return FALSE;
}

//
// quit()
//
// quit if fails, probably overkill
//
function quit($sock) {
  if ($sock) {
    $cmd="QUIT"."\r\n";
    fputs($sock, $cmd);
  }
}

function sendMail_mail($to, $title, $message, $object=null, $headers=null, $sender=null, $boundary=null, $references=null) {
  scriptLog('sendMail_mail');
  global $cronnedMailSender;
  $paramMailSender=Parameter::getGlobalParameter('paramMailSender');
  // The user is stored in session , if you try to changed email of the admin , you need to disconnect/reconnect for have the new email in sender
  $user=getSessionUser();
  $senderMailAdmin=($user->email)?$user->email:$paramMailSender;
  // $paramMailSender = $senderMailAdmin;
  $paramMailReplyTo=Parameter::getGlobalParameter('paramMailReplyTo');
  $paramMailSmtpServer=Parameter::getGlobalParameter('paramMailSmtpServer');
  $paramMailSmtpPort=Parameter::getGlobalParameter('paramMailSmtpPort');
  $paramMailSendmailPath=Parameter::getGlobalParameter('paramMailSendmailPath');
  $eol=Parameter::getGlobalParameter('mailEol');
  if ($paramMailSmtpServer==null or strtolower($paramMailSmtpServer)=='null') {
    return "";
  }
  // Save data of the mail
  $mail=new Mail();
  if ($cronnedMailSender and $cronnedMailSender!=null) {
    $mail->idUser=$cronnedMailSender;
  } else if (sessionUserExists()) {
    $mail->idUser=getSessionUser()->id;
  }
  if ($object) {
    $mail->idProject=(property_exists($object, 'idProject'))?$object->idProject:null;
    $mail->idMailable=SqlList::getIdFromTranslatableName('Mailable', get_class($object));
    $mail->refId=$object->id;
    $mail->idStatus=(property_exists($object, 'idStatus'))?$object->idStatus:null;
  }
  $mail->mailDateTime=date('Y-m-d H:i');
  $mail->mailTo=$to;
  $mail->mailTitle=$title;
  $mail->mailBody=$message;
  $mail->mailStatus='WAIT';
  $mail->idle='0';
  $resMail=$mail->save();
  if (stripos($resMail, 'id="lastOperationStatus" value="ERROR"')>0) {
    errorLog("Error storing email in table : ".$resMail);
  }
  // Send then mail
  if (!$headers) {
    $headers='MIME-Version: 1.0'.$eol;
    if ($boundary) {
      $headers.='Content-Type: multipart/mixed;boundary='.$boundary.$eol;
      $headers.=$eol;
      $message='Your email client does not support MIME type.'.$eol.'Your may have difficulties to read this mail or have access to linked files.'.$eol.'--'.$boundary.$eol.'Content-Type: text/html; charset=utf-8'.$eol.$message;
    } else {
      $headers.='Content-Type: text/html; charset=utf-8'.$eol;
    }
    $headers.='From: '.(($sender)?$sender:$paramMailSender).$eol;
    $headers.='Reply-To: '.(($sender)?$sender:$paramMailReplyTo).$eol;
    $headers.='Content-Transfer-Encoding: 8bit'.$eol;
    $headers.='X-Mailer: PHP/'.phpversion().$eol;
  } else {
	  $headers.=$eol.'Content-Type: text/calendar; charset="utf-8"; method="REQUEST"';
  }
  if ($references) {
    $headers.='References: <'.$references.'.'.$paramMailSender.'>'.$eol;
  }
  if (isset($paramMailSmtpServer) and $paramMailSmtpServer) {
    ini_set('SMTP', $paramMailSmtpServer);
  }
  if (isset($paramMailSmtpPort) and $paramMailSmtpPort) {
    ini_set('smtp_port', $paramMailSmtpPort);
  }
  if (isset($paramMailSendmailPath) and $paramMailSendmailPath) {
    ini_set('sendmail_path', $paramMailSendmailPath);
  }
  // error_reporting(E_ERROR);
  // restore_error_handler();
  enableCatchErrors();
  $resultMail="NO";
  if ($paramMailSmtpServer!==null) {
    $resultMail=mail($to, $title, $message, $headers);
  } else {
    Mail::setLastErrorMessage("SMTP Server not set. Not able to send mail.");
    errorLog("   SMTP Server not set. Not able to send mail.");
  }
  disableCatchErrors();
  // error_reporting(E_ALL);
  // set_error_handler('errorHandler');
  if (!$resultMail) {
    errorLog("Error sending mail");
    $smtp=ini_get('SMTP');
    errorLog("   SMTP Server : ".$smtp);
    $port=ini_get('smtp_port');
    errorLog("   SMTP Port : ".$port);
    $path=ini_get('sendmail_path');
    errorLog("   Sendmail path : ".$path);
    errorLog("   Mail stored in Database : #".$mail->id);
    Mail::setLastErrorMessage("Error sending mail. <br/>Cannot retreive more information.<br/>Maybe you'll get more info in mailing system log file.");
  }
  if ($resultMail==="NO") {
    $resultMail="";
  }
  // save the status of the sending
  $mail->mailStatus=($resultMail)?'OK':'ERROR';
  $mail->save();
  return $resultMail;
}

/**
 * ===========================================================================
 * Log tracing.
 * Not to be called directly. Use following functions instead.
 *
 * @param $message message
 *          to store on log
 * @param $level level
 *          of trace : 1=error, 2=trace, 3=debug, 4=script
 * @return void
 */
$previousTraceTimestamp=0;

function logTracing($message, $level=9, $increment=0) {
  global $debugPerf, $previousTraceTimestamp;
  $execTime="";
  if (isset($debugPerf) and $debugPerf==true) {
    if ($previousTraceTimestamp) {
      $execTime=(round(microtime(true)-$previousTraceTimestamp, 3));
      $pos=strpos($execTime, '.');
      if ($pos==0) $execTime=$execTime.'.000';
      else $execTime=substr($execTime.'000', 0, ($pos+4));
      $execTime=" => ".$execTime;
    } else {
      $execTime=' => 0.000';
    }
    $previousTraceTimestamp=microtime(true);
  }
  $logLevel=Parameter::getGlobalParameter('logLevel');
  $tabcar='                        ';
  if ($logLevel==5) {
    if ($level<=3) echo $message;
    return;
  }
  $logFile=Parameter::getGlobalParameter('logFile');
  if (!$logFile or $logFile=='' or $level==9) {
    exit();
  }
  if ($level<=$logLevel) {
    $file=str_replace('${date}', date('Ymd'), $logFile);
    if (is_array($message) or is_object($message)) {
      $tab=($increment==0)?'':substr($tabcar, 0, ($increment*3-1));
      $txt=$tab.(is_array($message)?'Array['.count($message).']':'Object['.get_class($message).']');
      logTracing($txt, $level, $increment);
      foreach ($message as $ind=>$val) {
        $tab=substr($tabcar, 0, (($increment+1)*3-1));
        if (is_array($val) or is_object($val)) {
          $txt=$tab.$ind.' => ';
          $txt.=is_array($val)?'Array ':'Object ';
          logTracing($txt, $level, $increment+1);
          logTracing($val, $level, $increment+1);
        } else {
          $txt=$tab.$ind.' => '.$val;
          logTracing($txt, $level, $increment+1);
        }
      }
      $level=999;
      $msg='';
    } else {
      $msg=$message."\n";
    }
    switch ($level) {
      case 1 :
        $version=Parameter::getGlobalParameter('dbVersion');
        $msg=date('Y-m-d H:i:s').substr(microtime(), 1, 4).$execTime." ***** ERROR ***** [$version] ".$msg;
        break;
      case 2 :
        $msg=date('Y-m-d H:i:s').substr(microtime(), 1, 4).$execTime." ===== TRACE ===== ".$msg;
        break;
      case 3 :
        $msg=date('Y-m-d H:i:s').substr(microtime(), 1, 4).$execTime." ----- DEBUG ----- ".$msg;
        break;
      case 4 :
        $msg=date('Y-m-d H:i:s').substr(microtime(), 1, 4).$execTime." ..... SCRIPT .... ".$msg;
        break;
      default :
        break;
    }
    $dir=dirname($file);
    if (!file_exists($dir)) {
      echo '<br/><span class="messageERROR">'.i18n("invalidLogDir", array($dir)).'</span>';
    } else if (!is_writable($dir)) {
      echo '<br/><span class="messageERROR">'.i18n("lockedLogDir", array($dir)).'</span>';
    } else {
      writeFile($msg, $file);
    }
  }
}

/**
 * ===========================================================================
 * Log tracing for debug
 *
 * @param $message message
 *          to store on log
 * @return void
 */
// debugLog to keep
function debugLog($message) {
  logTracing($message, 3);
}

/**
 * ===========================================================================
 * Log tracing for debug to keep in the code
 * Will be used for debugQuery mode of for performance tracing
 * so can be considered as Trace log, but will generate a Debug message in log
 * Will be activated, depending on location, with :
 * $debugTrace=true
 * $debugQuery=true
 * or directly calling traceExecutionTime() function
 * 
 * @param $message message
 *          to store on log
 * @return void
 */
function debugTraceLog($message) {
  logTracing($message, 3);
}

/**
 * ===========================================================================
 * Log tracing for general trace
 *
 * @param $message message
 *          to store on log
 * @return void
 */
function traceLog($message) {
  logTracing($message, 2);
}

/**
 * ===========================================================================
 * Log tracing for error
 *
 * @param $message message
 *          to store on log
 * @return void
 */
function errorLog($message) {
  if (getSessionValue('setup', null, true)) return;
  logTracing($message, 1);
}

/**
 * ===========================================================================
 * Log tracing for entry into script
 *
 * @param $message message
 *          to store on log
 * @return void
 */
function scriptLog($script) {
  logTracing(getIP()." ".$script, 4);
}

/**
 * ===========================================================================
 * Log a maximum of environment data (to trace hacking)
 *
 * @return void
 */
function envLog() {
  traceLog('IP CLient='.getIP());
  if (isset($_REQUEST)) {
    foreach ($_REQUEST as $ind=>$val) {
      traceLog('$_REQUEST['.$ind.']='.$val);
    }
  }
}

function debugDisplayObj($obj) {
  if (is_object($obj)) {
    return get_class($obj).' #'.$obj->id;
  } else if (is_array($obj)) {
    return ("array(".count($obj).")");
  } else {
    return $obj;
  }
}

/**
 * ===========================================================================
 * Get the IP of the Client
 *
 * @return the IP as a string
 */
function getIP() {
  if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
  } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    $ip=$_SERVER['HTTP_CLIENT_IP'];
  } else if (isset($_SERVER['REMOTE_ADDR'])) {
    $ip=$_SERVER['REMOTE_ADDR'];
  } else {
    $ip='batch';
  }
  return $ip;
}

/**
 * ===========================================================================
 * Get the access right for a menu and an access type
 *
 * @param $menuName The
 *          name of the menu; should be 'menuXXX'
 * @param $accessType requested
 *          access type : 'read', 'create', 'update', 'delete'
 * @return the access right
 *         'NO' => non access
 *         'PRO' => all elements of affected projects
 *         'OWN' => only own elements
 *         'ALL' => any element
 */
function securityGetAccessRight($menuName, $accessType, $obj=null, $user=null) {
  scriptLog("securityGetAccessRight(menuName=$menuName, accessType=$accessType, obj=".debugDisplayObj($obj).", user=".debugDisplayObj($user).")");
  if (!$user) {
    $user=getSessionUser();
  }
// MTY - LEAVE SYSTEM
  // If a leave system obj => return result for specific leave system habilitation
  if (isLeavesSystemActiv()) {
    $secLeaveSystemResult = securityGetLeaveSystemAccessRight($menuName, $accessType, $obj, $user);
    if ($secLeaveSystemResult!= 'NOTALEAVEELEMENT' and substr($secLeaveSystemResult,0,1)!= '_') {
      return $secLeaveSystemResult;
    }
    // If a leave system obj => return result for specific leave system habilitation
    if (substr($secLeaveSystemResult,0,1)=='_') {
      $theClass = substr($secLeaveSystemResult,1,-1);
      $menuName = "menu".$theClass;
      if ($obj!=null) {
        $obj = new $theClass($obj->id);
      }
    }
  }
// MTY - LEAVE SYSTEM  
  $accessRightList = $user->getAccessControlRights ($obj);
  $accessRight = 'ALL';
  if ($accessType == 'update' and $obj and $obj->id == null) {
    return securityGetAccessRight ( $menuName, 'create' );
  }
  if (array_key_exists ( $menuName, $accessRightList )) {
    $accessRightObj = $accessRightList [$menuName];
    if (array_key_exists ( $accessType, $accessRightObj )) {
      $accessRight = $accessRightObj [$accessType];
    }
  }
  return $accessRight;
}

/**
 * ===========================================================================
 * Get the access right for a menu and an access type, just returning 'YES' or 'NO'
 *
 * @param $menuName name
 *          : name of the menu as 'menuXXX'
 * @param $accessType requested
 *          access type : 'read', 'create', 'update', 'delete'
 * @return the right as Yes or No (depending on object properties)
 */
function securityGetAccessRightYesNo($menuName, $accessType, $obj=null, $user=null) {
  scriptLog("securityGetAccessRightYesNo ( menuName=$menuName, accessType=$accessType, obj=".debugDisplayObj($obj).", user=".debugDisplayObj($user).")");
// MTY - LEAVE SYSTEM
  if (isLeavesSystemActiv()) {
    $secLeaveSystemResult = securityGetLeaveSystemAccessRight($menuName, $accessType, $obj, $user, true);
    // If a leave system obj => return result for specific leave system habilitation
    if ($secLeaveSystemResult!= 'NOTALEAVEELEMENT' and substr($secLeaveSystemResult,0,1)!= '_') {
      return $secLeaveSystemResult;
    }
    // If the obj is a leave system class but is in fact a standard projeqtor object
    // => Substitutes with the real class et real obj
    if (substr($secLeaveSystemResult,0,1)=='_') {
      $theClass = substr($secLeaveSystemResult,1,-1);
      $menuName = "menu".$theClass;
      if ($obj!=null) {
        $obj = new $theClass($obj->id);
      }
    }
  }
// MTY - LEAVE SYSTEM  
  $class=substr ( $menuName, 4 ); 
  global $remoteDb;
  if (isset($remoteDb) and $remoteDb==true) {
    if ($class=='Parameter' or $class=='History' or $class=='TranslationCode' or $class=='TranslationValue') {
      return "YES";
    }
  }
  if (!SqlElement::class_exists($class) and $obj) {
    $class=get_class($obj);
  }
  if (!$class) return 'NO';
  if ($class=='Admin') return 'YES';
  if (!SqlElement::class_exists($class)) {
    errorLog("securityGetAccessRightYesNo : '$class' is not an existing object class");
    errorLog("securityGetAccessRightYesNo($menuName, $accessType, ".debugDisplayObj($obj).", ".debugDisplayObj($user).")");
    debugPrintTraceStack();
  }
  
  if ($obj and property_exists($obj, 'isPrivate') and $obj->isPrivate==1 and $obj->idUser!=getSessionUser()->id) {
    return 'NO';
  }
  if (property_exists($class, '_no'.ucfirst($accessType))) {
    return 'NO';
  }
  if (property_exists($class, '_readOnly') and $accessType!='read') {
    return 'NO';
  }
  if ($obj and $obj->id==0) {
    $obj->id=null;
  }
  if (!$user) {
    if (!sessionUserExists()) {
      global $maintenance;
      if ($maintenance) {
        return 'YES';
      } else {
        // traceLog("securityGetAccessRightYesNo : This is a case that should not exist unless hacking attempt. Exit.");
        exit(); // return 'NO'; // This is a case that should not exist unless hacking attempt or use of F5
      }
    } else {
      $user=getSessionUser();
    }
  }
  $accessRight=securityGetAccessRight($menuName, $accessType, $obj, $user);
  if ($accessType=='create') {
    if ((!$obj or (property_exists(substr($menuName, 4), 'name') and !$obj->name)) and property_exists(substr($menuName, 4), 'idProject')) { // Case of project dependent screen, will allow if user has some create rights on one of his profiles
      foreach ($user->getAllProfiles() as $prf) {
        $tmpUser=new User();
        $tmpUser->idProfile=$prf;
        $accessRight=securityGetAccessRight($menuName, $accessType, $obj, $tmpUser);
        $accessRight=($accessRight=='NO' or $accessRight=='OWN' or $accessRight=='RES' or $accessRight=='READ')?'NO':'YES';
        if ($accessRight=='YES') break;
      }
    } else if ($accessRight=='NO') {
      $accessRight="NO"; // will return no
    } else if ($accessRight=='READ') {
      $accessRight="NO";
    } else if ($accessRight=='ALL' or $accessRight=='WRITE') {
      $accessRight='YES';
    } else if ($accessRight=='PRO') {
      $accessRight='NO';
      if ($obj!=null) {
        if (!$obj->id and (!property_exists(substr($menuName, 4), 'name') or !$obj->name)) {
          $accessRight='YES';
        } else if (get_class($obj)=='Project') {
          if (array_key_exists($obj->id, $user->getAffectedProjects(false))) {
            $accessRight='YES';
          }
        } else if (property_exists($obj, 'idProject')) {
          $limitToActiveProjects=(get_class($obj)=='Affectation')?false:true;
          if (sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1) $limitToActiveProjects=false;
          if (array_key_exists($obj->idProject, $user->getAffectedProjects($limitToActiveProjects))) {
            $accessRight='YES';
          }
        }
      } else {
        $accessRight='YES';
      }
    } else if ($accessRight=='OWN') {
      $accessRight='NO';
    } else if ($accessRight=='RES') {
      $accessRight='NO';
    }
  } else if ($accessType=='update' or $accessType=='delete' or $accessType=='read') {
    if ($accessRight=='NO') {
      $accessRight="NO"; // will return no
    } else if ($accessRight=='READ') {
      if ($accessType=='read') {
        $accessRight="NO"; // TODO : why is it no here ?
      } else {
        $accessRight="NO";
      }
    } else if ($accessRight=='ALL' or $accessRight=='WRITE') {
      $accessRight='YES';
    } else if ($accessRight=='PRO') {
      $accessRight='NO';
      if ($obj!=null) {
        if (get_class($obj)=='Project') {
          if (array_key_exists($obj->id, $user->getAffectedProjects(false)) or !$obj->id) {
            $accessRight='YES';
          }
        } else if (property_exists($obj, 'idProject')) {
          $limitToActiveProjects=(get_class($obj)=='Affectation')?false:true;
          if (sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1) $limitToActiveProjects=false;
          if (array_key_exists($obj->idProject, $user->getAffectedProjects($limitToActiveProjects)) or $obj->id==null) {
            $accessRight='YES';
          }
          // BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
        } else if (get_class($obj)=='Notification') {
          if ($obj->idUser==$user->id) {
            $accessRight='YES';
          }
          // END - ADD BY TABARY - NOTIFICATION SYSTEM
        }
      } else {
        // TODO : IF NO OBJ and AccesRight = PRO : should return YES ???
      }
    } else if ($accessRight=='OWN') {
      $accessRight='NO';
      if ($obj!=null) {
        if (property_exists($obj, 'idUser')) {
          $old=$obj->getOld();
          if ($old->id and $user->id==$old->idUser) {
            $accessRight='YES';
          }
        }
      }
    } else if ($accessRight=='RES') {
      $accessRight='NO';
      if ($obj!=null) {
        if (property_exists($obj, 'idResource')) {
          $old=$obj->getOld();
          if ($old->id and $user->id==$old->idResource) {
            $accessRight='YES';
          }
        }
      }
    }
  }
  // Give read access to approvers
  if ($accessRight=='NO' and $accessType=='read' and $class=='Document' and $obj and $obj->id) {
    $app=new Approver();
    $cpt=$app->countSqlElementsFromCriteria(array('refType'=>'Document', 'refId'=>$obj->id, 'idAffectable'=>$user->id));
    if ($cpt>0) $accessRight='YES';
  }
  return $accessRight;
}

/**
 * ============================================================================
 * Transfor a list, as an array, into an 'IN' clause
 *
 * @param $list an
 *          array, with the id to select as index
 * @return the IN clause, as ('xx', 'yy', ... )
 */
function transformListIntoInClause($list) {
  if (count($list)==0) return '(0)';
  $result='(0';
  foreach ($list as $id=>$name) {
    if (trim($id)) {
      $result.=($result=='(')?'':', ';
      $result.=$id;
    }
  }
  $result.=')';
  return $result;
}

function transformValueListIntoInClause($list) {
  if (count($list)==0) return '(0)';
  $result='(';
  foreach ($list as $id=>$name) {
    if (trim($name)) {
      $result.=($result=='(')?'':', ';
      if (is_numeric($name)) {
        $result.=$name;
      } else {
        $result.="'".$name."'";
      }
    }
  }
  $result.=')';
  if ($result=='()') {
    $result='(0)';
  }
  return $result;
}

/**
 * ============================================================================
 * Calculate difference between 2 dates
 *
 * @param $start start
 *          date - format yyyy-mm-dd
 * @param $end end
 *          date - format yyyy-mm-dd
 * @return int number of work days (remove week-ends)
 */
function workDayDiffDates($start, $end) {
  if (!$start or !$end) {
    return "";
  }
  $currentDate=$start;
  $endDate=$end;
  if ($end<$start) {
    return 0;
  }
  $duration=0;
  if (isOffDay($currentDate) and $currentDate!=$endDate) $duration++;
  while ($currentDate<=$endDate) {
    if (!isOffDay($currentDate) or $currentDate==$endDate) {
      $duration++;
    }
    $currentDate=addDaysToDate($currentDate, 1);
  }
  return $duration;
}

function countDayDiffDates($start, $end, $idCalendarDefinition) {
	if (!$start or !$end) {
		return "";
	}
	$currentDate=$start;
	$endDate=$end;
	if ($end<$start) {
		return 0;
	}
	$duration=0;
	while ($currentDate<=$endDate) {
		if (!isOffDay($currentDate, $idCalendarDefinition)) {
			$duration++;
		}
		$currentDate=addDaysToDate($currentDate, 1);
	}
	return $duration;
}

/**
 * ============================================================================
 * Calculate difference between 2 dates
 *
 * @param $start start
 *          date - format yyyy-mm-dd
 * @param $end end
 *          date - format yyyy-mm-dd
 * @return int number of days
 */
function dayDiffDates($start, $end) {
  if (strlen($start)>10) $start=substr($start,0,10);
  if (strlen($end)>10) $end=substr($end,0,10);
  if (!trim($start) or !trim($end)) return 0;
  $tStart=explode("-", $start);
  $tEnd=explode("-", $end);
  $dStart=mktime(0, 0, 0, $tStart[1], $tStart[2], $tStart[0]);
  $dEnd=mktime(0, 0, 0, $tEnd[1], $tEnd[2], $tEnd[0]);
  $diff=$dEnd-$dStart;
  $diffDay=($diff/86400);
  return round($diffDay, 0);
}

/**
 * ============================================================================
 * Calculate new date after adding some days
 *
 * @param $date start
 *          date - format yyyy-mm-dd
 * @param $days numbers
 *          of days to add (can be < 0 to subtract days)
 * @return new calculated date - format yyyy-mm-dd
 */
function addWorkDaysToDate_old($date, $days) {
  if ($days==0) {
    return $date;
  }
  if ($days<0) {
    return removeWorkDaysToDate($date, (-1)*$days);
  }
  if (!$date) {
    return;
  }
  $days-=1;
  $tDate=explode("-", $date);
  $dStart=mktime(0, 0, 0, $tDate[1], $tDate[2], $tDate[0]);
  if (date("N", $dStart)>=6) {
    $tDate[2]=$tDate[2]+8-date("N", $dStart);
    $dStart=mktime(0, 0, 0, $tDate[1], $tDate[2], $tDate[0]);
  }
  $weekEnds=floor($days/5);
  $additionalDays=$days-(5*$weekEnds);
  if (date("N", $dStart)+$additionalDays>=6) {
    $weekEnds+=1;
  }
  $days+=2*$weekEnds;
  $dEnd=mktime(0, 0, 0, $tDate[1], $tDate[2]+$days, $tDate[0]);
  return date("Y-m-d", $dEnd);
}

function addWorkDaysToDate($date, $days) {
  if (!$date) {
    return;
  }
  if ($days==0) {
    return $date;
  }
  if ($days<0) {
    return removeWorkDaysToDate($date, (-1)*$days);
  }
  $endDate=$date;
  $left=$days;
  $left--;
  while ($left>0) {
    $endDate=addDaysToDate($endDate, 1);
    if (!isOffDay($endDate)) {
      $left--;
    }
  }
  return $endDate;
}

function removeWorkDaysToDate($date, $days) {
  if ($days==0) {
    return $date;
  }
  if ($days<=0) {
    return addWorkDaysToDate($date, (-1)*$days);
  }
  if (!$date) {
    return;
  }
  $endDate=$date;
  $left=$days;
  while ($left>0) {
    $endDate=addDaysToDate($endDate, -1);
    if (!isOffDay($endDate)) {
      $left--;
    }
  }
  return $endDate;
}

/**
 * ============================================================================
 * Calculate new date after adding some months
 *
 * @param $date start
 *          date - format yyyy-mm-dd
 * @param $months numbers
 *          of months to add (can be < 0 to subtract months)
 * @return new calculated date - format yyyy-mm-dd
 */
function addDaysToDate($date, $days) {
  // if (strlen($date)>10) $date=substr($date,0,10);
  if (!trim($date)) return null;
  if (strlen($date>10)) $date=substr($date,0,10);
  $tDate=explode("-", $date);
  if (count($tDate)<3) return null;
  return date("Y-m-d", mktime(0, 0, 0, $tDate[1], $tDate[2]+$days, $tDate[0]));
}

/**
 * ============================================================================
 * Calculate new date after adding some months
 *
 * @param $date start
 *          date - format yyyy-mm-dd
 * @param $months numbers
 *          of months to add (can be < 0 to subtract months)
 * @return new calculated date - format yyyy-mm-dd
 */
function addMonthsToDate($date, $months) {
  if (!$date) return addMonthsToDate(date('Y-m-d'), $months);
  $tDate=explode("-", $date);
  return date("Y-m-d", mktime(0, 0, 0, $tDate[1]+$months, $tDate[2], $tDate[0]));
}

function sameDayOfNextMonths($date, $day) {
  if (!$date) return addMonthsToDate(date('Y-m-d'), $months);
  $tDate=explode("-", $date);
  $newYear=$tDate[0];
  $newMonth=$tDate[1]+1;
  if ($newMonth>12) {
    $newMonth='1';
    $newYear+=1;
  }
  if (strlen($newMonth)<2) $newMonth='0'.$newMonth;
  $lastDayOfMonth=date('t', strtotime("$newYear-$newMonth-01"));
  if ($day=='last' or $day>$lastDayOfMonth) {
    $newDay=$lastDayOfMonth;
  } else {
    $newDay=$day;
  }
  return "$newYear-$newMonth-$newDay";
}

/**
 * ============================================================================
 * Calculate new date after adding some weeks
 *
 * @param $date start
 *          date - format yyyy-mm-dd
 * @param $weeks numbers
 *          of weeks to add (can be < 0 to subtract weeks)
 * @return new calculated date - format yyyy-mm-dd
 */
function padto2($val) {
  return str_pad($val, 2, "0", STR_PAD_LEFT);
}

function addWeeksToDate($date, $weeks) {
  $tDate=explode("-", $date);
  return date("Y-m-d", mktime(0, 0, 0, $tDate[1], $tDate[2]+(7*$weeks), $tDate[0]));
}

function workTimeDiffDateTime($start, $end) {
  $hoursPerDay=Parameter::getGlobalParameter('dayTime');
  if (!$hoursPerDay) $hoursPerDay=8;
  $startDay=substr($start, 0, 10);
  $endDay=substr($end, 0, 10);
  $time=substr($start, 11, 5);
  if (! $time) $time='00:00:00';
  $hh=substr($time, 0, 2);
  $mn=substr($time, 3, 2);
  $mnStart=intval($hh)*60+intval($mn);
  $time=substr($end, 11, 5);
  if (! $time) $time='00:00:00';
  $hh=substr($time, 0, 2);
  $mn=substr($time, 3, 2);
  $mnStop=intval($hh)*60+intval($mn);
  $mnFullDay=60*24;
  if ($startDay==$endDay) {
    $days=0;
    $delay=($mnStop-$mnStart)/(60*$hoursPerDay);
  } else {
    $days=dayDiffDates($startDay, $endDay)-1;
    $delay=0;
    if ($days>0) {
      $delay=($days*$mnFullDay)/(60*$hoursPerDay);
    }
    $delay+=($mnFullDay-$mnStart)/(60*$hoursPerDay);
    $delay+=($mnStop)/(60*$hoursPerDay);
  }
  return $delay;
}

function addDelayToDatetime($dateTime, $delay, $unit) {
  $date=substr($dateTime, 0, 10);
  $time=substr($dateTime, 11, 5);
  if ($unit=='DD') {
    $newDate=addDaysToDate($date, $delay);
    return $newDate." ".$time;
  } else if ($unit=='OD') {
    if ($delay<0) {
      $newDate=removeWorkDaysToDate($date, (-1)*$delay);
    } else {
      $newDate=addWorkDaysToDate($date, $delay+1);
    }
    return $newDate." ".$time;
  } else if ($unit=='HH') {
    $hh=intval(substr($time, 0, 2));
    $mn=intval(substr($time, 3, 2));
    if (!$hh and !$mn) {
      $hh=00;
      $mn=00;
    }
    $res=minutesToTime($hh*60+$mn+$delay*60);
    $newDate=addDaysToDate($date, $res['d']);
    return $newDate." ".padto2($res['h']).":".padto2($res['m']).':00';
  } else if ($unit=='OH') {
    $startAM=Parameter::getGlobalParameter('startAM');
    $endAM=Parameter::getGlobalParameter('endAM');
    $startPM=Parameter::getGlobalParameter('startPM');
    $endPM=Parameter::getGlobalParameter('endPM');
    if (!$startAM or !$endAM or !$startPM or !$endPM) {
      return $dateTime;
    }
    $mnEndAM=intval((substr($endAM, 0, 2))*60+intval(substr($endAM, 3)));
    $mnStartAM=intval(substr($startAM, 0, 2)*60+intval(substr($startAM, 3)));
    $mnEndPM=intval(substr($endPM, 0, 2)*60+intval(substr($endPM, 3)));
    $mnStartPM=intval(substr($startPM, 0, 2)*60+intval(substr($startPM, 3)));
    $mnDelay=$delay*60;
    $hh=substr($time, 0, 2);
    $mn=substr($time, 3, 2);
    $mnTime=intval($hh)*60+intval($mn);
    $AMPM='AM';
    if ($mnDelay>=0) {
      if (isOffDay($date)) {
        $date=addWorkDaysToDate($date, 2);
        $mnTime=$mnStartAM;
        $AMPM='AM';
      } else if ($mnTime>=$mnEndPM) {
        $date=addWorkDaysToDate($date, 2);
        $mnTime=$mnStartAM;
        $AMPM='AM';
      } else if ($mnTime>=$mnStartPM) {
        $AMPM='PM';
      } else if ($mnTime>=$mnEndAM) {
        $mnTime=$mnStartPM;
        $AMPM='PM';
      } else if ($mnTime>=$mnStartAM) {
        $AMPM='AM';
      } else {
        $mnTime=$mnStartAM;
        $AMPM='AM';
      }
      while ($mnDelay>0) {
        if ($AMPM=='AM') {
          $left=$mnEndAM-$mnTime;
          if ($left>$mnDelay) {
            $mnTime+=$mnDelay;
            $mnDelay=0;
          } else {
            $mnTime=$mnStartPM;
            $mnDelay-=$left;
            $AMPM='PM';
          }
        } else {
          $left=$mnEndPM-$mnTime;
          if ($left>$mnDelay) {
            $mnTime+=$mnDelay;
            $mnDelay=0;
          } else {
            $mnTime=$mnStartAM;
            $mnDelay-=$left;
            $date=addWorkDaysToDate($date, 2);
            $AMPM='AM';
          }
        }
      }
    } else { // $mnDelay<0
      if (isOffDay($date)) {
        $date=removeWorkDaysToDate($date, 1);
        $mnTime=$mnEndPM;
        $AMPM='PM';
      } else if ($mnTime>=$mnEndPM) {
        $mnTime=$mnEndPM;
        $AMPM='AP';
      } else if ($mnTime>=$mnStartPM) {
        $AMPM='PM';
      } else if ($mnTime>=$mnEndAM) {
        $mnTime=$mnEndAM;
        $AMPM='AM';
      } else if ($mnTime>=$mnStartAM) {
        $AMPM='AM';
      } else {
        $date=removeWorkDaysToDate($date, 1);
        $mnTime=$mnEndPM;
        $AMPM='PM';
      }
      while ($mnDelay<0) {
        if ($AMPM=='AM') {
          $left=$mnTime-$mnStartAM;
          if ($left>abs($mnDelay)) {
            $mnTime+=$mnDelay;
            $mnDelay=0;
          } else {
            $date=removeWorkDaysToDate($date, 1);
            $mnTime=$mnEndPM;
            $mnDelay+=$left;
            $AMPM='PM';
          }
        } else {
          $left=$mnTime-$mnStartPM;
          if ($left>abs($mnDelay)) {
            $mnTime+=$mnDelay;
            $mnDelay=0;
          } else {
            $mnTime=$mnEndAM;
            $mnDelay+=$left;
            $AMPM='AM';
          }
        }
      }
    }
    $res=minutesToTime($mnTime);
    return $date." ".padto2($res['h']).":".padto2($res['m']).':00';
  } else {
    // return $dateTime;
  }
}

function minutesToTime($time) {
  if (is_numeric($time)) {
    $value=array("d"=>0, "h"=>0, "m"=>0);
    while ($time<0) {
      $value["d"]-=1;
      $time+=1440;
    }
    if ($time>=1440) {
      $value["d"]=floor($time/1440);
      $time=($time%1440);
    }
    if ($time>=60) {
      $value["h"]=floor($time/60);
      $time=($time%60);
    }
    $value["m"]=floor($time);
    return (array)$value;
  } else {
    return (bool)FALSE;
  }
}

/**
 * Return wbs code as a sortable value string (pad number with zeros)
 *
 * @param $wbs wbs
 *          code
 * @return string the formated sortable wbs
 */
function formatSortableWbs($wbs) {
  if ($wbs===null) return null;
  $exp=explode('.', $wbs);
  $result="";
  foreach ($exp as $node) {
    $result.=($result!='')?'.':'';
    if ($node=='_#') $result.='00001.99999.00500';
    else $result.=substr('00000', 0, 5-strlen($node)).$node;
  }
  return $result;
}

/**
 * Calculate forecolor for a given background color
 * Return black for light backgroud color
 * Return white for dark backgroud color
 *
 * @param
 *          $color
 * @return string The fore color to fit the back ground color
 */
function getForeColor($color) {
  $foreColor='#000000';
  if ($color=='transparent') {
    $foreColor='#FFFFFF';
  } else if (strlen($color)==7) {
    $red=substr($color, 1, 2);
    $green=substr($color, 3, 2);
    $blue=substr($color, 5, 2);
    $light=(0.3)*hexdec($red)+(0.6)*hexdec($green)+(0.1)*hexdec($blue);
    if ($light<128) {
      $foreColor='#FFFFFF';
    }
  }
  return $foreColor;
}

/*
 * calculate the first day of a given week. Returns a timestamp
 */
function firstDayofWeek($week=null, $year=null) {
  if (!$week or !$year) {
    $now=date('Y-m-d');
    return firstDayofWeek(weekNumber($now), substr($now, 0, 4));
  }
  $Jan1=mktime(1, 1, 1, 1, 1, $year);
  $MondayOffset=(11-date('w', $Jan1))%7-3;
  $desiredMonday=strtotime((intval($week)-1).' weeks '.$MondayOffset.' days', $Jan1);
  return $desiredMonday;
}

function lastDayofWeek($week=null, $year=null) {
	if (!$week or !$year) {
		$now=date('Y-m-d');
		return firstDayofWeek(weekNumber($now), substr($now, 0, 4));
	}
	$date = new DateTime();
	$date->setISODate($year, $week, 7);
	$lastDay = substr($date->format('Y-m-d'), 0, 10);
	return $lastDay;
}
/*
 * Calculate number of days between 2 dates
 */
/*
 * Not user anymore. See dayDiffDates()
 * function numberOfDays($startDate, $endDate) {
 * $tabStart = explode("-", $startDate);
 * $tabEnd = explode("-", $endDate);
 * $diff = mktime(0, 0, 0, $tabEnd[1], $tabEnd[2], $tabEnd[0]) -
 * mktime(0, 0, 0, $tabStart[1], $tabStart[2], $tabStart[0]);
 * return(($diff / 86400)+1);
 * }
 */

/*
 * calculate the week number for a given date
 *
 */
function weekNumber($dateValue) {
  return date('W', strtotime($dateValue));
}

function weekFormat($dateValue) {
  // return date('Y-W', strtotime ($dateValue) );
  $w=(date('W', strtotime($dateValue)));
  $m=(date('m', strtotime($dateValue)));
  $y=(date('Y', strtotime($dateValue)));
  if ($w==1&&$m==12) {
    return ($y+1).'-'.$w;
  } else if ($w>=52&&$m==1) {
    return ($y-1).'-'.$w;
  } else {
    return date('Y-W', strtotime($dateValue));
  }
}

/*
 * Checks if a date is a "off day" (weekend or else)
 */
function isOffDay($dateValue, $idCalendarDefinition=null) {
  if (isOpenDay($dateValue, $idCalendarDefinition)) {
    return false;
  } else {
    return true;
  }
}
/*
 * Checks if a date is a "off day" (weekend or else)
 */
$bankHolidays=array();
$bankWorkdays=array();
$bankOffDays=array();

function isOpenDay($dateValue, $idCalendarDefinition='1') {
  global $bankHolidays, $bankWorkdays, $bankOffDays;
  $paramDefaultLocale=Parameter::getGlobalParameter('paramDefaultLocale');
  $iDate=strtotime($dateValue);
  $year=date('Y', $iDate);
  if (!$idCalendarDefinition) $idCalendarDefinition=1;
  if (array_key_exists($year.'#'.$idCalendarDefinition, $bankWorkdays)) {
    $aBankWorkdays=$bankWorkdays[$year.'#'.$idCalendarDefinition];
  } else {
    $cal=new Calendar();
    $crit=array('year'=>$year, 'isOffDay'=>'0', 'idCalendarDefinition'=>$idCalendarDefinition);
    $aBankWorkdays=array();
    $lstCal=$cal->getSqlElementsFromCriteria($crit);
    foreach ($lstCal as $obj) {
      $aBankWorkdays[]=$obj->day;
    }
    $bankWorkdays[$year.'#'.$idCalendarDefinition]=$aBankWorkdays;
  }
  if (array_key_exists($year.'#'.$idCalendarDefinition, $bankHolidays)) {
    $aBankHolidays=$bankHolidays[$year.'#'.$idCalendarDefinition];
  } else {
    $cal=new Calendar();
    $crit=array('year'=>$year, 'isOffDay'=>'1', 'idCalendarDefinition'=>$idCalendarDefinition);
    $aBankHolidays=array();
    $lstCal=$cal->getSqlElementsFromCriteria($crit);
    foreach ($lstCal as $obj) {
      $aBankHolidays[]=$obj->day;
    }
    $bankHolidays[$year.'#'.$idCalendarDefinition]=$aBankHolidays;
  }
  
// MTY - GENERIC DAY OFF
  if (isset($bankOffDays[$idCalendarDefinition])) {
    $arrayDefaultOffDays=$bankOffDays[$idCalendarDefinition];
  } else {
    $calDef = new CalendarDefinition($idCalendarDefinition);
    $arrayDefaultOffDays=array();
    if (Parameter::getGlobalParameter('OpenDayMonday')=='offDays') {
      $arrayDefaultOffDays[]=1;  
    } elseif ($calDef->dayOfWeek1==1) {
      $arrayDefaultOffDays[]=1;        
    }
    if (Parameter::getGlobalParameter('OpenDayTuesday')=='offDays') {
        $arrayDefaultOffDays[]=2;
    } elseif ($calDef->dayOfWeek2==1) {
      $arrayDefaultOffDays[]=2;        
    }
    if (Parameter::getGlobalParameter('OpenDayWednesday')=='offDays') {
        $arrayDefaultOffDays[]=3;
    } elseif ($calDef->dayOfWeek3==1) {
      $arrayDefaultOffDays[]=3;        
    }
    if (Parameter::getGlobalParameter('OpenDayThursday')=='offDays') {
        $arrayDefaultOffDays[]=4;
    } elseif ($calDef->dayOfWeek4==1) {
      $arrayDefaultOffDays[]=4;        
    }
    if (Parameter::getGlobalParameter('OpenDayFriday')=='offDays') {
        $arrayDefaultOffDays[]=5;
    } elseif ($calDef->dayOfWeek5==1) {
      $arrayDefaultOffDays[]=5;        
    }
    if (Parameter::getGlobalParameter('OpenDaySaturday')=='offDays') {
        $arrayDefaultOffDays[]=6;
    } elseif ($calDef->dayOfWeek6==1) {
      $arrayDefaultOffDays[]=6;        
    }
    if (Parameter::getGlobalParameter('OpenDaySunday')=='offDays') {
        $arrayDefaultOffDays[]=0;
    } elseif ($calDef->dayOfWeek0==1) {
      $arrayDefaultOffDays[]=0;        
    }
    $bankOffDays[$idCalendarDefinition]=$arrayDefaultOffDays;
  }
// MTY - GENERIC DAY OFF  
  if (in_array (date('w', $iDate), $arrayDefaultOffDays)) {
    if (in_array(date( 'Ymd', $iDate), $aBankWorkdays)) {
      return true;
    } else {
      return false;
    }
  } else {
    if (in_array(date('Ymd', $iDate), $aBankHolidays)) {
      return false;
    } else {
      return true;
    }
  }
}

function getEaster($iYear=null) {
  if (is_null($iYear)) {
    $iYear=(int)date('Y');
  }
  $iN=$iYear-1900;
  $iA=$iN%19;
  $iB=floor(((7*$iA)+1)/19);
  $iC=((11*$iA)-$iB+4)%29;
  $iD=floor($iN/4);
  $iE=($iN-$iC+$iD+31)%7;
  $iResult=25-$iC-$iE;
  if ($iResult>0) {
    $iEaster=strtotime($iYear.'/04/'.$iResult);
  } else {
    $iEaster=strtotime($iYear.'/03/'.(31+$iResult));
  }
  return $iEaster;
}

function numberOfDaysOfMonth($dateValue) {
  return date('t', strtotime($dateValue));
}

function getBooleanValue($val) {
  if ($val===true) {
    return true;
  }
  if ($val===false) {
    return false;
  }
  if ($val=='true') {
    return true;
  }
  if ($val=='false') {
    return false;
  }
  return false;
}

function getBooleanValueAsString($val) {
  if (getBooleanValue($val)) {
    return 'true';
  } else {
    return 'false';
  }
}

// ADD By atrancoso #ticket 84
function getMonth($max, $SpeMonth, $addPoint=true) {
  $monthArr=array(
      i18n('January'), 
      i18n('February'), 
      i18n('March'), 
      i18n('April'), 
      i18n('May'), 
      i18n('June'), 
      i18n('July'), 
      i18n('August'), 
      i18n('September'), 
      i18n('October'), 
      i18n('November'), 
      i18n('December'));
  if ($max) {
    foreach ($monthArr as $num=>$month) {
      if (mb_strlen($month, 'UTF-8')>$max) {
        if ($addPoint) {
          $monthArr[$num]=mb_substr($month, 0, $max-1, 'UTF-8').'.';
        } else {
          $monthArr[$num]=mb_substr($month, 0, $max, 'UTF-8');
        }
      }
    }
  }
  return ($monthArr[$SpeMonth]);
}

// end ADD atrancoso #ticket 84
// ADD By atrancoso #ticket 84
function getNbMonth($max, $addPoint=true) {
  $monthArr=array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
  if ($max) {
    foreach ($monthArr as $num=>$month) {
      if (mb_strlen($month, 'UTF-8')>$max) {
        if ($addPoint) {
          $monthArr[$num]=mb_substr($month, 0, $max-1, 'UTF-8').'.';
        } else {
          $monthArr[$num]=mb_substr($month, 0, $max, 'UTF-8');
        }
      }
    }
  }
  return $monthArr;
}

// end ADD atrancoso #ticket 84
function getArrayMonth($max, $addPoint=true) {
  $monthArr=array(
      i18n("January"), 
      i18n("February"), 
      i18n("March"), 
      i18n("April"), 
      i18n("May"), 
      i18n("June"), 
      i18n("July"), 
      i18n("August"), 
      i18n("September"), 
      i18n("October"), 
      i18n("November"), 
      i18n("December"));
  if ($max) {
    foreach ($monthArr as $num=>$month) {
      if (mb_strlen($month, 'UTF-8')>$max) {
        if ($addPoint) {
          $monthArr[$num]=mb_substr($month, 0, $max-1, 'UTF-8').'.';
        } else {
          $monthArr[$num]=mb_substr($month, 0, $max, 'UTF-8');
        }
      }
    }
  }
  return $monthArr;
}

function getAppRoot() {
  $appRoot="";
  $page=$_SERVER['PHP_SELF'];
  if (strpos($page, '/', 1)) {
    $appRoot=substr($page, 0, strpos($page, '/', 1));
  }
  if ($appRoot=='/view' or $appRoot=='/tool' or $appRoot=='/report' or $appRoot=='/plugin') {
    $appRoot='/';
  }
  return $appRoot;
}

function getPrintInNewWindow($mode='print') {
  $printInNewWindow=($mode=='pdf')?true:false;
  if (sessionValueExists($mode.'InNewWindow')) {
    if (getSessionValue($mode.'InNewWindow')=='YES') {
      $printInNewWindow=true;
    } else if (getSessionValue($mode.'InNewWindow')=='NO') {
      $printInNewWindow=false;
    }
  }
  return $printInNewWindow;
}

function checkVersion() {
  global $version, $website;
  $user=getSessionUser();
  $profile=new Profile($user->idProfile);
  if ($profile->profileCode!='ADM') {
    return;
  }
  $getYesNo=Parameter::getGlobalParameter('getVersion');
  if ($getYesNo=='NO') {
    return;
  }
  $checkUrl='http://projeqtor.org/admin/getVersion.php';
  $currentVersion=null;
  if (ini_get('allow_url_fopen')) {
    enableCatchErrors();
    enableSilentErrors();
    $currentVersion=file_get_contents($checkUrl);
    disableCatchErrors();
    disableSilentErrors();
  }
  if (!$currentVersion) {
    traceLog('Cannot check Version at '.$checkUrl);
    traceLog('Maybe allow_url_fopen is Off in php.ini...');
  }
  if (!$currentVersion) {
    return;
  }
  $crit=array('title'=>$currentVersion, 'idUser'=>$user->id);
  $alert=new Alert();
  $lst=$alert->getSqlElementsFromCriteria($crit, false);
  if (count($lst)>0) {
    return;
  }
  $current=explode(".", substr($currentVersion, 1));
  $check=explode(".", substr($version, 1));
  $newVersion="";
  for ($i=0; $i<3; $i++) {
    // echo "'$check[$i]' - '$current[$i]'\n";
    if ($check[$i]<$current[$i]) {
      $newVersion=$currentVersion;
      break;
    }
    if ($check[$i]>$current[$i]) {
      traceLog("current version $version is higher than latest released $currentVersion");
      break;
    }
  }
  if ($newVersion) {
    $alert=new Alert();
    $alert->title=$currentVersion;
    $alert->message=i18n('newVersion', array($newVersion)).'<br/><a href="'.$website.'" target="#">'.$website.'</a>';
    $alert->alertDateTime=date("Y-m-d H:i:s");
    $alert->alertInitialDateTime=$alert->alertDateTime;
    $alert->idUser=$user->id;
    $alert->alertType='INFO';
    $alert->save();
  }
}

function wbsProjectSort($p1, $p2) {
  if ($p1->ProjectPlanningElement->wbsSortable<$p1->ProjectPlanningElement->wbsSortable) {
    return -1;
  } else {
    return 1;
  }
}

function formatColor($type, $val) {
  $obj=new $type($val);
  $color=$obj->color;
  $foreColor='#000000';
  if (strlen($color)==7) {
    $red=substr($color, 1, 2);
    $green=substr($color, 3, 2);
    $blue=substr($color, 5, 2);
    $light=(0.3)*base_convert($red, 16, 10)+(0.6)*base_convert($green, 16, 10)+(0.1)*base_convert($blue, 16, 10);
    if ($light<128) {
      $foreColor='#FFFFFF';
    }
  }
  $result='<div align="center" style="text-align:center; height:100%; background:'.$color.';color:'.$foreColor.';">'.SqlList::getNameFromId($type, $val).'</div>';
  return $result;
}

function formatColorFromNameAndColor($name, $color) {
  $foreColor='#000000';
  if (strlen($color)==7) {
    $red=substr($color, 1, 2);
    $green=substr($color, 3, 2);
    $blue=substr($color, 5, 2);
    $light=(0.3)*base_convert($red, 16, 10)+(0.6)*base_convert($green, 16, 10)+(0.1)*base_convert($blue, 16, 10);
    if ($light<128) {
      $foreColor='#FFFFFF';
    }
  }
  $result='<div align="center" style="text-align:center;  background:'.$color.';color:'.$foreColor.';">'.$name.'</div>';
  return $result;
}

function getPrintTitle() {
  //$result=i18n("applicationTitle");
  $result=(Parameter::getGlobalParameter('paramDbDisplayName'))?Parameter::getGlobalParameter('paramDbDisplayName'):i18n("applicationTitle");
  if (isset($_REQUEST['objectClass']) and isset($_REQUEST['page'])) {
    $objectClass=$_REQUEST['objectClass'];
    Security::checkValidClass($objectClass, 'objectClass');
    
    if ($_REQUEST['page']=='objectDetail.php') {
      $result.=' - '.i18n($objectClass).' #'.($_REQUEST['objectId']+0);
    } else if ($_REQUEST['page']=='../tool/jsonQuery.php') {
      $result.=' - '.i18n('menu'.$objectClass);
    }
  }
  return $result;
}

$startMicroTime=null;

function traceExecutionTime($step='', $reset=false) {
  global $startMicroTime;
  if ($reset) {
    $startMicroTime=microtime(true);
    return;
  }
  debugTraceLog((round((microtime(true)-$startMicroTime)*1000)/1000).(($step)?" s for step ".$step:''));
  $startMicroTime=microtime(true);
}

function isHtml5() {
  if (isset($_REQUEST['isIE'])) {
    $isIE=$_REQUEST['isIE'];
    if ($isIE and $isIE<=9) {
      return false;
    } else {
      return true;
    }
  }
  $browser=Audit::getBrowser();
  if ($browser['browser']=='Internet Explorer') {
    if ($browser['version']<'10') {
      return false;
    }
  }
  return true;
}

function isIE() {
  $browser=Audit::getBrowser();
  if ($browser['browser']=='Internet Explorer') {
    if ($browser['version']) {
      return $browser['version'];
    } else {
      return true;
    }
  }
  return false;
}
function isFF() {
  $browser=Audit::getBrowser();
  if ($browser['browser']=='Mozilla Firefox') {
    if ($browser['version']) {
      return $browser['version'];
    } else {
      return true;
    }
  }
  return false;
}

function formatBrowserDateToDate($dateTime) {
  global $browserLocaleDateFormat;
  $AMPM='';
  if (substr($dateTime, 4, 1)=='-' and substr($dateTime, 7, 1)=='-') {
    return $dateTime;
  }
  if (substr_count($dateTime, ':')>0 and substr_count($dateTime, ' ')>0) {
    if (substr_count($dateTime, ' ')==1) list($date, $time)=explode(' ', $dateTime);
    else list($date, $time,$AMPM)=explode(' ', $dateTime);
  } else {
    $date=$dateTime;
    $time="";
  }
  if ($browserLocaleDateFormat=='DD/MM/YYYY' and substr_count($date, '/')==2) {
    list($day, $month, $year)=explode('/', $date);
  } else if ($browserLocaleDateFormat=='MM/DD/YYYY' and substr_count($date, '/')==2) {
    list($month, $day, $year)=explode('/', $date);
  } else {
    return $dateTime;
  }
  $month=intval($month);
  $day=intval($day);
  $year=intval($year);
  if ($year<100) $year+=2000;
  if (trim($time)) {
    if ($AMPM=='') $AMPM=(strpos($time,'AM'))?'AM':((strpos($time,'PM'))?'PM':'');
    if ($AMPM!='') $time=str_replace(array('AM','PM'),'',$time);
    if (substr_count($time, ':')==2) {
      list($hour, $minute, $second)=explode(':', $time);
    } else {
      list($hour, $minute)=explode(':', $time);
      $second=0;
    }
    $hour=intval($hour);
    $minute=intval($minute);
    $second=intval($second);
    if ($AMPM=='PM') $hout+=12;
    return date('Y-m-d H:i:s', mktime($hour, $minute, $second, $month, $day, $year));
  } else {
    return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
  }
}

function securityCheckRequest() {
  // parameters to check for non html
  $parameters=array('objectClass', 'objectId', 'directAccess', 'page', 'directAccessPage');
  $pages=array('page', 'directAccessPage');
  foreach ($parameters as $param) {
    if (isset($_REQUEST[$param])) {
      $paramVal=$_REQUEST[$param];
      if (in_array($param, $pages)) {
        $paramVal=str_replace('../report/../tool/','../tool/',$paramVal);
        securityCheckPage($paramVal);
        $pos=strpos($paramVal, '?');
        if ($pos) {
          $paramVal=substr($paramVal, 0, $pos);
        }
      }
      if (trim($paramVal) and htmlEntities($paramVal)!=$paramVal) {
        traceHack("projeqtor->securityCheckRequest, _REQUEST['$param']=$paramVal");
        exit();
      }
    }
  }
}

function projeqtor_set_time_limit($timeout) {
  if (ini_get('safe_mode')) {
    traceLog("WARNING : try to extend time limit to $timeout seconds forbidden by safe_mode. This may lead to unsuccessfull operation.");
  } else if (!function_exists('set_time_limit')) {
    traceLog("WARNING : try to extend time limit to $timeout seconds but set_time_limit has been disabled. This may lead to unsuccessfull operation.");
  } else {
    $max=ini_get('max_execution_time');
    if ($max!=0&&($timeout>$max or $timeout==0)) { // Don't bother if unlimited or request max
      @set_time_limit($timeout);
    } else {
      @set_time_limit($max); // Set time limit to max to reset current execution time to zero
    }
  }
}

function projeqtor_set_memory_limit($memory) {
  @ini_set('memory_limit', $memory);
}

// Functions to set and retrieve data from SESSION : do not use direct $_SESSION
function setSessionValue($code, $value, $global=false) {
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (!isset($_SESSION[$projeqtorSession])) {
    $_SESSION[$projeqtorSession]=array();
  }
  $_SESSION[$projeqtorSession][$code]=$value;
}

function unsetSessionValue($code, $global=false) {
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (!isset($_SESSION[$projeqtorSession])) {
    return null;
  }
  if (isset($_SESSION[$projeqtorSession][$code])) {
    unset($_SESSION[$projeqtorSession][$code]);
  }
}

function getSessionValue($code, $default=null, $global=false) {
  // Global parameter is forced when "whatever the databse" is required
  // it is mostly used to cases "also when database is not set yet" ;)
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (!isset($_SESSION[$projeqtorSession])) {
    return $default;
  }
  if (!isset($_SESSION[$projeqtorSession][$code])) {
    return $default;
  }
  return $_SESSION[$projeqtorSession][$code];
}
function getAllSessionValues($global=false) {
  // Global parameter is forced when "whatever the databse" is required
  // it is mostly used to cases "also when database is not set yet" ;)
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (!isset($_SESSION[$projeqtorSession])) {
    return array();
  }
  return $_SESSION[$projeqtorSession];
}
//gautier filter
function sessionDisplayFilter($code,$objectClass){
  $display = "none";
  if((sessionValueExists($code.$objectClass) and getSessionValue($code.$objectClass)!='' )or (sessionValueExists($code.'QuickSw'.$objectClass) and getSessionValue($code.'QuickSw'.$objectClass)=='on')){
    $display = "block";
  }
  return $display;
}

// Gautier #2512
function sessionValueExists($code, $global=false) {
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (!isset($_SESSION[$projeqtorSession])) {
    return false;
  }
  if (isset($_SESSION[$projeqtorSession][$code])) {
    return true;
  } else {
    return false;
  }
}

function setSessionTableValue($table, $code, $val, $global=false) {
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (!isset($_SESSION[$projeqtorSession][$table])) {
    $_SESSION[$projeqtorSession][$table]=array();
  }
  $_SESSION[$projeqtorSession][$table][$code]=$val;
}

function resetSession($global=false) {
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  $_SESSION[$projeqtorSession]=array();
}

function getSessionTableValue($table, $code, $default=null, $global=false) {
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (!isset($_SESSION[$projeqtorSession])) {
    return $default;
  }
  if (!isset($_SESSION[$projeqtorSession][$table][$code])) {
    return $default;
  }
  return $_SESSION[$projeqtorSession][$table][$code];
}

function sessionTableValueExist($table, $code, $global=false) {
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (!isset($_SESSION[$projeqtorSession])) {
    return false;
  }
  if (isset($_SESSION[$projeqtorSession][$table][$code])) {
    return true;
  } else {
    return false;
  }
}

function unsetSessionTable($table, $code, $global=false) {
  global $paramDbName, $paramDbPrefix;
  if ($global) {
    $projeqtorSession='ProjeQtOr';
  } else {
    $projeqtorSession='ProjeQtOr_'.$paramDbName.(($paramDbPrefix)?'_'.$paramDbPrefix:'');
  }
  if (isset($_SESSION[$projeqtorSession][$table][$code])) {
    unset($_SESSION[$projeqtorSession][$table][$code]);
  }
}
// end #2512

// Functions to get and set current user value from session
function getSessionUser() {
  $user=getSessionValue('user');
  if ($user===null) {
    return new User();
  } else {
    $user->_isRetreivedFromSession=true;
    return $user;
  }
}

function setSessionUser($user) {
  if ($user and is_object($user)) {
    setSessionValue('user', $user);
  } else {
    unsetSessionValue('user');
  }
}

function sessionUserExists() {
  $user=getSessionValue('user');
  if ($user===null) {
    return false;
  } else {
    return true;
  }
}

/**
 * Get list of resources depending on access rights visibility : restricted on team or or organization or without rtestriction depending on specific access rights
 * 
 * @param string $specific
 *          : type or scope of the list. Possible values are : 'imputation', 'diary', 'planning'
 * @param boolean $includePool          
 * @return simple list of resources of type id=>name
 */
function getListForSpecificRights($specific, $includePool=false) {
  global $user;
  if (!isset($user)) {
    $user=getSessionUser();
  }
  if ($user->allSpecificRightsForProfilesOneOnlyValue($specific, 'NO')) {
    // $table[$user->id]=' ';
    $table=array($user->id=>SqlList::getNameFromId('Affectable', $user->id));
  } else if ($user->allSpecificRightsForProfilesOneOnlyValue($specific, 'ALL')) {
    $table=SqlList::getList(($includePool)?'ResourceAll':'Resource','name',null,true);
  } else if (($user->allSpecificRightsForProfilesOneOnlyValue($specific, 'OWN') or $user->allSpecificRightsForProfilesOneOnlyValue($specific, 'RES')) and $user->isResource) {
    $table=array($user->id=>SqlList::getNameFromId('Affectable', $user->id));
  } else if ($user->allSpecificRightsForProfilesOneOnlyValue($specific, 'TEAM')) {
    $table=$user->getManagedTeamResources(true, 'list');
  } else {
    $table=array();
    $fullTable=SqlList::getList(($includePool)?'ResourceAll':'Resource','name',null,true);
    foreach ($user->getAllSpecificRightsForProfiles($specific) as $right=>$profList) {
      if (($right=='OWN' or $right=='RES') and $user->isResource) {
        $table[$user->id]=SqlList::getNameFromId('Affectable', $user->id);
      } else if ($right=='ALL' and in_array($user->idProfile, $profList)) {
        $table=$fullTable;
        break;
      } else if ($right=='TEAM' and in_array($user->idProfile, $profList)) {
        $table=array_merge_preserve_keys($table, $user->getManagedTeamResources(true, 'list'));
      } else if ($right=='ALL' or $right=='PRO' or $right=='YES') {
        $inClause='(0';
        foreach ($user->getSpecificAffectedProfiles() as $prj=>$prf) {
          if (in_array($prf, $profList)) {
            $inClause.=','.$prj;
          }
        }
        $inClause.=')';
        $crit='idProject in '.$inClause;
        $aff=new Affectation();
        $lstAff=$aff->getSqlElementsFromCriteria(null, false, $crit, null, true);
        foreach ($lstAff as $id=>$aff) {
          if (array_key_exists($aff->idResource, $fullTable)) {
            $table[$aff->idResource]=$fullTable[$aff->idResource];
          }
        }
      }
    }
  }
  if (count($table)==0) {
    if (!$user->isResource) {
      $table[0]=' ';
    } else {
      $table[$user->id]=' ';
    }
  }
  asort($table);
  return $table;
}

function formatNumericOutput($val) {
  global $browserLocale;
  $fmt=new NumberFormatter52($browserLocale, NumberFormatter52::DECIMAL);
  return $fmt->formatDecimalPoint($val);
}

function formatNumericInput($val) {
  global $browserLocale;
  $fmt=new NumberFormatter52($browserLocale, NumberFormatter52::DECIMAL);
  if ($fmt->thouthandSeparator=='.' and substr_count($val, $fmt->decimalSeparator)!=1) {
    // Thouthand separator is "." but locale decimal is not present :
    // as we are dealing with decimals we expect it is generic format,
    // if not it will raise an error (expected behavior)
    $from=array(' ');
    $to=array('');
  } else {
    $from=array($fmt->thouthandSeparator, $fmt->decimalSeparator, ' '); // Take care to replace thouthand first
    $to=array('', '.', '');
  }
  return str_replace($from, $to, $val);
}

function getLastOperationStatus($result) {
  if (!$result) return 'OK';
  $search='id="lastOperationStatus" value="';
  if (!stripos($result, $search)) {
    $search='id="lastPlanStatus" value="';
  }
  $start=stripos($result, $search)+strlen($search);
  if (strlen($result)<=$start) {
    errorLog("invalid search for result in string '$result'");
    debugPrintTraceStack();
  }
  $end=stripos($result, '"', $start);
  $status=substr($result, $start, $end-$start);
  switch ($status) {
    case "OK" :
    case "INVALID" :
    case "CONTROL" :
    case "ERROR" :
    case "NO_CHANGE" :
    case "INCOMPLETE" :
    case "WARNING" :
    case "CONFIRM" :
      break; // OK, valid status
    default :
      errorLog("'$status' is not an expected status in result \n$result");
  }
  return $status;
}

function getLastOperationMessage($result) {
  return substr($result, 0, strpos($result, '<input type="hidden" id="lastSaveId" value="'));
}

function displayLastOperationStatus($result) {
  $status=getLastOperationStatus($result);
  if ($status=="OK" or $status=="NO_CHANGE" or $status=="INCOMPLETE") {
    Sql::commitTransaction();
  } else {
    Sql::rollbackTransaction();
  }
  echo '<div class="message'.$status.'" >'.$result.'</div>';
  return $status;
}

function calculateFractionFromTime($time, $subtractMidDay=true) {
  $paramHoursPerDay=Parameter::getGlobalParameter('dayTime');
  $paramStartAm=Parameter::getGlobalParameter('startAM');
  $paramEndAm=Parameter::getGlobalParameter('endAM');
  $paramStartPm=Parameter::getGlobalParameter('startPM');
  $paramEndPm=Parameter::getGlobalParameter('endPM');
  $minutesPerDay=60*floatval($paramHoursPerDay);
  if (!$minutesPerDay) return 0;
  $minutesTime=round(strtotime("1970-01-01 $time UTC")/60, 0);
  $minutesStartAM=round(strtotime("1970-01-01 $paramStartAm UTC")/60, 0);
  $minutesEndAM=round(strtotime("1970-01-01 $paramEndAm UTC")/60, 0);
  $minutesStartPM=round(strtotime("1970-01-01 $paramStartPm UTC")/60, 0);
  $minutes=$minutesTime-$minutesStartAM;
  if ($subtractMidDay and $minutesTime>$minutesStartPM) {
    $minutes-=$minutesStartPM-$minutesEndAM;
  }
  return round($minutes/$minutesPerDay, 2);
}

function calculateFractionBeetweenTimes($startTime, $endTime) {
  $start=calculateFractionFromTime($startTime, false);
  $end=calculateFractionFromTime($endTime, false);
  return ($end-$start);
}

function is_session_started() {
  if (version_compare(phpversion(), '5.4.0', '>=')) {
    return session_status()===PHP_SESSION_ACTIVE?TRUE:FALSE;
  } else {
    return session_id()===''?FALSE:TRUE;
  }
  return FALSE;
}

function getEditorType() {
  if (isNewGui()) return "CKInline";
  $editor=Parameter::getUserParameter('editor');
  if ($editor) {
    return $editor;
  } else {
    return "CK";
  }
}

function encodeCSV($val) {
  $csvExportUTF8=Parameter::getGlobalParameter('csvExportUTF8');
  // ini_set('mbstring.substitute_character', "none");
  // $val= mb_convert_encoding($val, 'UTF-8', 'UTF-8'); // This removes invalid UTF8 characters.
  if ($csvExportUTF8=='YES') {
    return $val;
  } else {
    return iconv("UTF-8", 'CP1252//TRANSLIT//IGNORE', $val);
  }
  // Was previous format, encoding to ISO-8859-1 : not including some characters (Euro)
  return utf8_decode($val);
}

function decodeCSV($val) {
  $csvExportUTF8=Parameter::getGlobalParameter('csvExportUTF8');
  // ini_set('mbstring.substitute_character', "none");
  // $val= mb_convert_encoding($val, 'UTF-8', 'UTF-8'); // This removes invalid UTF8 characters.
  if ($csvExportUTF8=='YES') {
    return $val;
  } else {
    return iconv('CP1252//TRANSLIT//IGNORE', "UTF-8", $val);
  }
  // Was previous format, encoding to ISO-8859-1 : not including some characters (Euro)
  return utf8_encode($val);
}
//
function autoOpenFilteringSelect($comboDetail=false) {
  if ($comboDetail) return ' onMouseDown="window.top.frames[\'comboDetailFrame\'].dijit.byId(this.name.replace(\'_detail\',\'\')).toggleDropDown();"  selectOnClick="true"';
  else return ' onMouseDown="dijit.byId(this.name).toggleDropDown();"  selectOnClick="true"';
}

function debugPrintTraceStack() {
  $stack=debug_backtrace();
  foreach ($stack as $stackLine) {
    $file=isset($stackLine['file'])?$stackLine['file']:'';
    $line=isset($stackLine['line'])?$stackLine['line']:'';
    $func=isset($stackLine['function'])?$stackLine['function']:'';
    $clas=isset($stackLine['class'])?$stackLine['class']:'';
    debugTraceLog(" =>".(($file)?" $file":"").(($line)?" at line $line":"").( ($func)?" calling ".(($clas)?"$clas:":"")."$func()":""));
  }
}

function formatIcon ($class, $size, $title=null, $withHighlight=false, $withImageColorNewGui=false) {
  $title=htmlEncode($title,"quote");
// MTY - LEAVE SYSTEM    
    $hr="";
    $menuName = "menu".$class;
    if (isLeavesSystemActiv() and isLeavesSystemMenuByMenuName($menuName)) { $hr="HR"; }
// MTY - LEAVE SYSTEM    
    
  //if ($size=="22") $size==24;
  global $print, $outMode;
  $result='';
  if ($withHighlight) {
    if ($size==32) {
      $result.='<div style="position:absolute;left:0px;width:43px;top:0px;height:32px;" class="iconHighlight">&nbsp;</div>';
    } else if ($size==16) { // Tested only for $size=16
      $result.='<div style="position:absolute;left:3px;width:18px;top:3px;height:17px;z-index:20;opacity:0.7;alpha(opacity=70)" class="iconHighlight">&nbsp;</div>';
    }
  }
  $position=($withHighlight)?'position:absolute;'.(($size=='32')?'top:0;left:5px;':''):'';
  if (isset($outMode) and $outMode=='pdf') {
    $result.="<span style='z-index:500;width:".$size."px;height:".$size."px;$position;'>"."<img style='width:".$size."px;height:".$size."px;' src='css/customIcons/grey/icon$class.png' /></span>";
  } else {
    if($withImageColorNewGui){
      $result.="<div class=".((isNewGui() and $withHighlight)?'NoSelection':'')." icon$class$size icon$class iconSize$size' style='z-index:500;width:".$size."px;height:".$size."px;$position;' title='$title'>&nbsp;</div>";
    }else{
      $result.="<div class='imageColorNewGui".((isNewGui() and $withHighlight)?'NoSelection':'')." icon$class$size icon$class iconSize$size' style='z-index:500;width:".$size."px;height:".$size."px;$position;' title='$title'>&nbsp;</div>";
    }
  }
  return $result;
}

function formatIconNewGui ($class, $size, $title=null, $withHighlight=false) {
	$title=htmlEncode($title,"quote");
	// MTY - LEAVE SYSTEM
	$hr="";
	$menuName = "menu".$class;
	if (isLeavesSystemActiv() and isLeavesSystemMenuByMenuName($menuName)) { $hr="HR"; }
	// MTY - LEAVE SYSTEM

	//if ($size=="22") $size==24;
	global $print, $outMode;
	$result='';
	if ($withHighlight) {
		if ($size==32) {
			$result.='<div style="position:absolute;left:0px;width:43px;top:0px;height:32px;" class="iconHighlight">&nbsp;</div>';
		} else if ($size==16) { // Tested only for $size=16
			$result.='<div style="position:absolute;left:3px;width:18px;top:3px;height:17px;z-index:20;opacity:0.7;alpha(opacity=70)" class="iconHighlight">&nbsp;</div>';
		}
	}
	$position=($withHighlight)?'position:absolute;'.(($size=='32')?'top:0;left:5px;':''):'';
	if (isset($outMode) and $outMode=='pdf') {
		$result.="<span style='z-index:500;width:".$size."px;height:".$size."px;$position;' class='imageColorNewGui' >"."<img style='width:".$size."px;height:".$size."px;' src='css/customIcons/new/icon$class.svg' /></span>";
	} else {
		$result.="<div class='icon$class iconSize$size imageColorNewGui' style='z-index:500;width:".$size."px;height:".$size."px;$position;' title='$title'>&nbsp;</div>";
	}
	return $result;
}

function formatSmallButton($class, $isClass=false, $activeButton=true, $dontApplyRoundedButtonSmall=false) {
  global $print, $outMode;
  $size="16";
  $result='';
  $roundedButton=" roundedButtonSmall ";
  if (isset($outMode) and $outMode=='pdf') {
    $result.="<span class='".$roundedButton.$newGui." ' style='top:0px;display:inline-block;width:".$size."px;height:".$size."px;'><img style='width:".$size."px;height:".$size."px;' src='css/customIcons/grey/icon$class.png' /></span>";
  } else {
    $button=($isClass)?'':'Button';
    $buttonClass=($activeButton)?'roundedButtonSmall':'';
    $cursorMouse = "";
    if($dontApplyRoundedButtonSmall){
      $buttonClass="";
      $cursorMouse= " cursor:pointer; ";
    }
    $result.="<span class='$buttonClass' style='".$cursorMouse."top:0px;display:inline-block;width:".$size."px;height:".$size."px;'><div class='icon$button$class$size icon$button$class iconSize$size' style='' >&nbsp;</div></span>";
  }
  return $result;
}

function formatBigButton($class, $isClass=false, $activeButton=true) {
  global $print, $outMode;
  $size="32";
  $result='';
  if (isset($outMode) and $outMode=='pdf') {
    $result.="<span class='roundedButtonSmall' style='top:0px;display:inline-block;width:".$size."px;height:".$size."px;'><img style='width:".$size."px;height:".$size."px;' src='css/customIcons/grey/icon$class.png' /></span>";
  } else {
    $button=($isClass)?'':'Button';
    $buttonClass=($activeButton)?'roundedButtonSmall':'';
    $result.="<span class='$buttonClass' style='top:0px;display:inline-block;width:".$size."px;height:".$size."px;'><div class='icon$button$class$size icon$button$class iconSize$size' style='' >&nbsp;</div></span>";
  }
  return $result;
}

function formatMediumButton($class, $isClass=false, $activeButton=true) {
  global $print, $outMode;
  $size="22";
  $result='';
  if (isset($outMode) and $outMode=='pdf') {
    $result.="<span class='roundedButtonSmall' style='top:0px;display:inline-block;width:".$size."px;height:".$size."px;'><img style='width:".$size."px;height:".$size."px;' src='css/customIcons/grey/icon$class.png' /></span>";
  } else {
    $button=($isClass)?'':'Button';
    $buttonClass=($activeButton)?'roundedButtonSmall roundedButtonNoBorder':'';
    $result.="<span class='$buttonClass' style='top:0px;display:inline-block;width:".$size."px;height:".$size."px;'><div class='icon$button$class$size icon$button$class iconSize$size' style='' >&nbsp;</div></span>";
  }
  return $result;
}

function formatNewGuiButton($class, $size, $isClass=false, $activeButton=true) {
	global $print, $outMode;
	$result='';
	if (isset($outMode) and $outMode=='pdf') {
		$result.="<span class='roundedButtonSmall' style='top:0px;display:inline-block;width:".$size."px;height:".$size."px;'><img style='width:".$size."px;height:".$size."px;' src='css/customIcons/new/icon$class.svg' /></span>";
	} else {
		$button=($isClass)?'':'Button';
		$buttonClass=($activeButton)?'roundedButtonSmall':'';
		$noRotate=($size=='16')?'':'noRotate';
		$result.="<span class='$buttonClass $noRotate' style='top:0px;display:inline-block;width:".$size."px;height:".$size."px;'><div class='icon$button$class iconSize$size $noRotate' style=''>&nbsp;</div></span>";
	}
	return $result;
}
// ===============================================================================================================================
// Text formating for long fields, to preserve or not html tags depending on text type (html formatted or plain text)
// This is needed to preserve compatibility between texts entered in Plan Text Editor and Rich Html Editor (CK or Dojo)
// ===============================================================================================================================
function isTextFieldHtmlFormatted($val) {
  $test=strtolower(substr(ltrim($val), 0, 10));
  if (substr(ltrim($val), 0, 1)=='<') {
    return true;
  } else {
    return false;
  }
}
// Replace nl to <br/> : will remove nl, what nl2br does not do
function nl2brForPlainText($val) {
  if (isTextFieldHtmlFormatted($val)) return $val;
  return str_replace(array("\r\n", "\r", "\n"), '', nl2br($val));
}

function formatPlainTextForHtmlEditing($val, $mode="full") {
  if ($mode=='full') return nl2br(htmlspecialchars(htmlEncode(str_replace(array('<br>', '<br/>', '<br />'), "\n", $val))));
  else if ($mode=='single') return nl2br(htmlEncode(str_replace(array('<br>', '<br/>', '<br />'), "\n", $val)));
  else return $val;
}

function formatAnyTextToPlainText($val, $removeNl=true) {
  if (isTextFieldHtmlFormatted($val)) {
    $text=new Html2Text($val);
    $val=$text->getText();
    echo htmlEncode($val);
  } else if ($removeNl) {
    echo str_replace(array("\n", '<br>', '<br/>', '<br />'), array("", "\n", "\n", "\n"), $val);
  } else {
    echo str_replace(array('<br>', '<br/>', '<br />'), array("\n", "\n", "\n"), $val);
  }
}

function br2nl($val) {
  return str_replace(array('<br>', '<br/>', '<br />'), array("\n", "\n", "\n"), $val);
}

// gautier #subscription
function adAutoSub($obj) {
  if (Parameter::getGlobalParameter('subscriptionAuto')!='YES') {
    return;
  }
  $list=array();
  $crit=array('idProduct'=>$obj->refId, 'idle'=>'0', 'isEis'=>'0');
  $productVersion=new Version();
  $list=$productVersion->getSqlElementsFromCriteria($crit);
  foreach ($list as $vers) {
    $sub=new Subscription();
    if ($obj->refType=='Product') {
      $refType='ProductVersion';
    } else {
      $refType='ComponentVersion';
    }
    $crit2=array('idAffectable'=>$obj->idAffectable, 'refType'=>$refType, 'refId'=>$vers->id);
    $list2=$sub->getSqlElementsFromCriteria($crit2);
    if (empty($list2)) {
      $sub->idAffectable=$obj->idAffectable;
      if ($obj->refType=='Product') {
        $sub->refType='ProductVersion';
      } else {
        $sub->refType='ComponentVersion';
      }
      $sub->refId=$vers->id;
      $sub->idUser=getSessionUser()->id;
      $sub->creationDateTime=date('Y-m-d H:i:s');
      $sub->isAutoSub=1;
      $sub->save();
    }
  }
}

function deleteAutoSub($obj) {
  if (Parameter::getGlobalParameter('subscriptionAuto')!='YES') {
    return;
  }
  $list=array();
  $crit=array('idProduct'=>$obj->refId, 'idle'=>'0', 'isEis'=>'0');
  $productVersion=new Version();
  $list=$productVersion->getSqlElementsFromCriteria($crit);
  foreach ($list as $vers) {
    $sub=new Subscription();
    $crit2=array('refId'=>$vers->id, 'idAffectable'=>$obj->idAffectable, 'isAutoSub'=>'1');
    $list2=$sub->getSqlElementsFromCriteria($crit2);
    foreach ($list2 as $lst) {
      $lst->delete();
    }
  }
}

function isSubscribeVersion($obj, $idUser) {
  $subscribed=false;
  $sub=new Subscription();
  $crit=array('refId'=>$obj->id, 'refType'=>$obj->scope.'Version', 'idAffectable'=>$idUser);
  $list=$sub->getSqlElementsFromCriteria($crit);
  if (!empty($list)) {
    $subscribed=true;
  }
  return $subscribed;
}
// end
function splitCssAttributes($attr) {
  $spl=explode(';', $attr);
  $res=array();
  foreach ($spl as $at) {
    $sep=explode(':', $at);
    if (count($sep)<2) continue;
    $sep[1]=str_replace(' !important', '', $sep[1]);
    $res[$sep[0]]=$sep[1];
  }
  return $res;
}

function getWeekNumberFromDate($date) {
  if (!$date) $date=date('Y-m-d');
  $currentWeek=weekNumber($date) ;
  $currentYear=substr($date,0,4);
  $currentMonth=substr($date,5,2);
  if ($currentWeek==1 and $currentMonth>10 ) {
    $currentYear+=1;
  }
  if ($currentWeek>50 and $currentMonth==1 ) {
    $currentYear-=1;
  }
  if (strlen($currentWeek)==1) $currentWeek='0'.$currentWeek;
  return ($currentYear.$currentWeek);
}
// MTY - GENERIC DAY OFF
/** Return the last day of the month - year
 * @param integer $month = The month to retrieve last day
 * @param integer $year = The year to retrieve last day month
 * @return integer The last day of year month
  */
function lastDayOfMonth($month=null,$year=null) {
    if ($month==null or $year==null) {
        return 0;
    }    
    if ($year<1970) {
        return 0;        
    }    
    $monthString = ($month>9?"":"0").$month;    
    $date = new DateTime($year."-".$monthString."-01");
    $lastDayDate = $date->format("Y-m-t");
    return substr($lastDayDate,-2);
}
// MTY - GENERIC DAY OFF


/**
 * Send a notification. 
 * If Notification System is'nt activ or receiver is not allowed to read notification :
 *     - send an alert. If receiver is not allowed to read alert :
 *          - send an email
 * @param array $receivers : The array of receivers
 * @param object $obj
 * @param string $typeNotif
 * @param string $title
 * @param string $content
 * @param string $name
 * @param boolean $alertIfNotificationNotAllowed
 * @param boolean $emailIfAlertNotAllowed
 * @return void
 */
function sendNotification($receivers=null,$obj=null,$typeNotif="INFO",$title="",$content="",$name="",$alertIfNotificationNotAllowed=true,$emailIfAlertNotAllowed=true) {
    if ($receivers==null or $obj==null or $title=="") { return;}    
    if (!isNotificationSystemActiv() and !$alertIfNotificationNotAllowed and !$emailIfAlertNotAllowed) {return;}
    
    $class = get_class($obj);
    if ($class=="") {return;}
    if (strpos("Main",$class)!==false) {
        $class = substr($class,0,-4);
    }
    
    $menu = SqlElement::getFirstSqlElementFromCriteria("Menu", array("name" => "menu$class"));
    if (!isset($menu->id)) {
        $idMenu = null;
    } else {
        $idMenu = $menu->id;
    }
    if (isNotificationSystemActiv()) {
        $notifType = SqlElement::getFirstSqlElementFromCriteria("Type", array("name" => $typeNotif, "scope" => "Notification"));
        if (!isset($notifType->id)) {
            $notifType = SqlElement::getFirstSqlElementFromCriteria("Type", array("scope" => "Notification"));
        }
        if (isset($notifType->id)) {
            $idNotifType = $notifType->id;
        } else {
            $idNotifType = null;
        }
        $notifiable = SqlElement::getFirstSqlElementFromCriteria("Notifiable", array("notifiableItem" => $class));
        if (isset($notifiable->id)) {
            $idNotifiable = $notifiable->id;
        } else {
            $idNotifiable = null;
        }
        // Prepare notification values
        $notif = new Notification();
        $notif->idResource = getSessionUser()->id;
        $notif->idNotifiable = $idNotifiable;
        $notif->notifiedObjectId = $obj->id;
        $notif->idNotificationDefinition=null;
        $notif->idMenu=$idMenu;
        $notif->name = $name;
        $notifDate = new DateTime();
        $notif->notificationDate = $notifDate->format("Y-m-d");
        $notif->notificationTime = $notifDate->format("H:i:s");
        $notif->idNotificationType = $idNotifType;
        $notif->title = $title;
        $notif->content = $content;
        $notif->sendEmail = 0;
        $notif->idle=0;
    }
    
    // For each receivers
    foreach($receivers as $receiver) {
        $user = new User($receiver->id);
        $readAllowed = false;
        if (isNotificationSystemActiv()) {
            // Must be set before access right
            $notif->idUser = $receiver->id;
            // Get access to menu Notification for receiver
            $readAllowed = (securityGetAccessRightYesNo("menuNotification", "read", $notif, $user)=="YES");
            // Notification allowed
            if ($readAllowed) {
                // Send Notification
                $notif->id=null;
                $notif->simpleSave();
            }
        }
        if ($alertIfNotificationNotAllowed and $readAllowed==false) {
            // ALERT
            // Must be set before access right
            $alert=new Alert();
            $alert->idUser=$receiver->id;
            // Get access to menu Alert
            $readAllowed = (securityGetAccessRightYesNo("menuAlert", "read", $alert, $user)=="YES");
            // Alert allowed
            if ($readAllowed) {
                // Emit alert
                $title = $title;
                $message = $content;
                $theDate = new DateTime();
                
                $alert=new Alert();
                $alert->idUser=$receiver->id;
                $alert->alertType=htmlspecialchars("INFO",ENT_QUOTES,'UTF-8');
                $alert->alertInitialDateTime=$theDate->format("Y-m-d H:i:s");
                $alert->alertDateTime=$theDate->format("Y-m-d H:i:s");
                $alert->title=htmlspecialchars($title,ENT_QUOTES,'UTF-8');
                $alert->message=htmlspecialchars($message,ENT_QUOTES,'UTF-8');
                $alert->simpleSave();
            } elseif ($emailIfAlertNotAllowed) {
                // EMAIL
                if ($receiver->email!=null) {
                    $subject = $title;
                    $messageBody = $content;
                    sendMail($receiver->email, $subject, $messageBody);
                }
            }            
        }
    }    
}

/**
 * Determine if the leave system is activ
 * It's the case when :
 *  parameter leavesSystemActiv = YES
 * @return Boolean = True if leave system is activ
 */
function isLeavesSystemActiv() {
	//return ((Parameter::getGlobalParameter ( 'leavesSystemActiv' )=="NO"?false:true));
	return Module::isModuleActive('moduleHumanResource');
}

// florent ticket 4102
function changeLayoutObjectDetail($paramScreen,$paramLayoutObjectDetail){
  if(empty($paramScreen)){
    $currentScreen=Parameter::getUserParameter("paramScreen");
  }else{
    Parameter::storeUserParameter("paramScreen", $paramScreen);
    $currentScreen=$paramScreen;
  }
  if ($currentScreen=='top') $positionListDiv='top';
  else if($currentScreen=='switch') $positionListDiv='top';
  else $positionListDiv='left';
   
  if($paramLayoutObjectDetail){
    $currentLayout=Parameter::getUserParameter("paramLayoutObjectDetail");
    if(empty($currentLayout) or $currentLayout!=$paramLayoutObjectDetail) {
      Parameter::storeUserParameter("paramLayoutObjectDetail", $paramLayoutObjectDetail);
    }
  }
  return $positionListDiv;
}

function changeLayoutActivityStream($paramRightDiv){
  $currentRightDiv=Parameter::getUserParameter("paramRightDiv");
  if (empty($paramRightDiv)) {
    if(empty($currentRightDiv)) {
      $currentRightDiv='trailing';
      Parameter::storeUserParameter("paramRightDiv", $currentRightDiv);
    }
    $positonRightDiv=$currentRightDiv;
  } else {
    $positonRightDiv=$paramRightDiv;
    Parameter::storeUserParameter("paramRightDiv", $paramRightDiv);
  }
  return $positonRightDiv;
}

function getHeightLaoutActivityStream($objectClass){
  $rightHeight='0%';
  if(Parameter::getUserParameter("paramRightDiv") == 'bottom' ){
    $paramScreen=Parameter::getUserParameter("paramScreen");
    $modeActiveStreamGlobal=Parameter::getUserParameter('modeActiveStreamGlobal');
    $detailRightHeight=Parameter::getUserParameter('contentPaneRightDetailDivHeight'.$objectClass);
    $modeActiveStream=($detailRightHeight==='' or $detailRightHeight===null)?$modeActiveStreamGlobal:(($detailRightHeight==0)?'false':'true');
    if ($modeActiveStream!='true') return 0;
    if (!$detailRightHeight) {
      $detailRightHeight=getDefaultLayoutSize('contentPaneRightDetailDivHeight');
      Parameter::storeUserParameter('contentPaneRightDetailDivHeight'.$objectClass,$detailRightHeight);
    }
    $detailDivHeight=Parameter::getUserParameter('contentPaneDetailDivHeight'.$objectClass);
    //if (!$detailRightHeight) $detailRightHeight=0;
    if($detailRightHeight or $detailRightHeight==="0"){
      if ($detailRightHeight < 180){
        $detailRightHeight=180;
      }
      if((($detailRightHeight>$detailDivHeight ) and $paramScreen=='top' )and (!empty($detailDivHeight))){       
        $detailRightHeight=($detailDivHeight/2);
      }
      $rightHeight=$detailRightHeight.'px';
    } else {
      $rightHeight="0%";
    }
  }
  return $rightHeight;
}

function getWidthLayoutActivityStream($objectClass){
  $paramDetailDiv=Parameter::getUserParameter('paramScreen');
  $modeActiveStreamGlobal=Parameter::getUserParameter('modeActiveStreamGlobal');
  $detailDivWidth=Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass);
  $modeActiveStream=($detailDivWidth==='' or $detailDivWidth===null)?$modeActiveStreamGlobal:(($detailDivWidth==0)?'false':'true');
  if ($modeActiveStream!='true') return 0;
  
  if (!$detailDivWidth) {
    $detailDivWidth=getDefaultLayoutSize('contentPaneRightDetailDivWidth');
    Parameter::storeUserParameter('contentPaneRightDetailDivWidth'.$objectClass,$detailDivWidth);
  }  
  $topDivWidth=Parameter::getUserParameter('contentPaneDetailDivWidth'.$objectClass);
  //if (!$detailDivWidth) $detailDivWidth=0;
  if($detailDivWidth or $detailDivWidth==="0"){
    $rightWidth=$detailDivWidth.'px';
    if((!empty($topDivWidth)) and ($detailDivWidth > ($topDivWidth/2)) and $paramDetailDiv=='left'){
      $rightWidth=($topDivWidth/2).'px';
    }else if(empty($topDivWidth) and $paramDetailDiv=='left'){
      $rightWidth='20%';
    }
  } else {
    $rightWidth="0%";
  }
  return $rightWidth;
}

function WidthDivContentDetail($positionListDiv,$objectClass){
  if($positionListDiv=='left'){
    $rightDivWidth=intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass));
    $widthListDiv=intval(Parameter::getUserParameter("contentPaneTopDetailDivWidth".$objectClass));
    $widthDetailDiv=intval(Parameter::getUserParameter('contentPaneDetailDivWidth'.$objectClass));
    if(!empty($widthListDiv) or !empty($widthDetailDiv)){
      if($widthDetailDiv > 1400){
        $widthDetailDiv=1400;
        $widthListDiv=463;
      }else if($widthDetailDiv==0){
        $widthDetailDiv=($widthListDiv*.5);
        $widthListDiv=$widthListDiv-$widthDetailDiv;
      }else if($widthListDiv >= 1800){
        $widthListDiv=$widthListDiv-$widthDetailDiv;
      }else if($rightDivWidth >= $widthDetailDiv){
        $widthDetailDiv=$rightDivWidth+($rightDivWidth*0.5);
      }
      if($widthDetailDiv <= 400){
        $centerDivSize=$widthListDiv + $widthDetailDiv;
        $widthDetailDiv=400;
        $widthListDiv=$centerDivSize-$widthDetailDiv;
      }
      $widthListDiv= $widthListDiv.'px' ;
      $widthDetailDiv=$widthDetailDiv.'px';
    }else{
        $widthListDiv= '60%';
        $widthDetailDiv='40%';
    }
  }else{
    $widthListDiv='100%';
    $widthDetailDiv='100%';
  }
  return $tableDiv=array($widthListDiv,$widthDetailDiv);
}

function HeightLayoutListDiv($objectClass){
  $topDetailDivHeight=Parameter::getUserParameter('contentPaneTopDetailDivHeight'.$objectClass);
  $detailDivHeight=Parameter::getUserParameter('contentPaneDetailDivHeight'.$objectClass);
  $screenHeight=getSessionValue('screenHeight');
  if ($screenHeight and $topDetailDivHeight>$screenHeight-300) {
   $topDetailDivHeight=$screenHeight-300;
  }
  if(!empty($detailDivHeight) and !empty($topDetailDivHeight) and $detailDivHeight <= 250 ){
    $centerDivSize=$topDetailDivHeight+$detailDivHeight;
    $topDetailDivHeight=$centerDivSize-250;
  }
  if(empty($topDetailDivHeight)){
    $listHeight='50%';
  }else{
  $listHeight=$topDetailDivHeight."px";
  }
  return $listHeight;
}

function getDefaultLayoutSize($layout) {
  $rightDiv=Parameter::getUserParameter('paramRightDiv'); // bottom or trailing
  $screen=Parameter::getUserParameter("paramScreen"); // top, left or switch
  if ($layout=='contentPaneRightDetailDivHeight') {
    if ($screen=='top') return 130;
    else return 250;
  }
  if ($layout=='contentPaneRightDetailDivWidth') {
    if ($screen=='left') return 130;
    else return 250;
  }
  
}

function array_insert_after($array, $item, $position) {
  //function insert_i($tab,$valeurInsert,$indice)
  $result=array();
  foreach($array as $key=>$value) {
    array_push($result,$value);
    if ($key==$position) array_push($result,$item);
  }
  return $result;
}
function array_insert_before($array, $item, $position) {
  //function insert_i($tab,$valeurInsert,$indice)
  $result=array();
  foreach($array as $key=>$value) {
    if ($key==$position) array_push($result,$item);
    array_push($result,$value);
  }
  return $result;
}

//florent #4442
function octectConvertSize($octet){
  if($octet!=0 and $octet!='' and $octet!='-'){
    $def = [[1, ' octets'], [1024, ' ko'], [1024*1024, ' Mo'], [1024*1024*1024, ' Go'], [1024*1024*1024*1024, ' To']];
    for($i=0; $i<sizeof($def); $i++){
      if($octet<$def[$i][0]){
        $res=number_format(floatval($octet/$def[$i-1][0]),2,'.','').''.$def[$i-1][1];
        return $res;
      }
    }
  }else{
      return $res=i18n('errorNotFoundAttachment');
  }
}

function searchAllAttachmentMailable($objectClass,$idObj){
  if (!$objectClass or !$idObj) return array(array(),array());
  $forbidDownload = Parameter::getGlobalParameter('lockDocumentDownload');
  $attach= new Attachment();
  $link= new Link();
  $orderBy="creationDate DESC,id DESC ";
  $where="refType='".$objectClass."' and refId=".$idObj." and type='file'";
  $lstAttach=$attach->getSqlElementsFromCriteria(null,null,$where,$orderBy);
  $where="(ref2Type='".$objectClass."' and ref2Id=".$idObj." and ref1Type in ('DocumentVersion','Document') ) ";
  $where.="or (ref1Type='".$objectClass."' and ref1Id=".$idObj." and ref2Type in ('DocumentVersion','Document') ) ";
  $lstDoc=$link->getSqlElementsFromCriteria(null,null,$where,$orderBy);
  $currentUser=new User(getCurrentUserId());
  $c=0;
  if($lstDoc!=''){
    foreach ($lstDoc as $key=>$linkdoc){
      if ($linkdoc->ref1Type==$objectClass and $linkdoc->ref1Id==$idObj) { // Reverse ref1 / ref2 to have doc always in ref1
        $linkdoc->ref1Type=$linkdoc->ref2Type;
        $linkdoc->ref1Id=$linkdoc->ref2Id;
        $linkdoc->ref2Type=$objectClass;
        $linkdoc->ref2Id=$idObj;
      }
      if ($linkdoc->ref1Type=='Document') {
        $obj=new Document($linkdoc->ref1Id,true);
      } else {
        $vers=new DocumentVersion($linkdoc->ref1Id,true);
        $obj=new Document($vers->idDocument);
      }
      $canDownload=(securityCheckDisplayMenu(null, get_class($obj)) and securityGetAccessRightYesNo('menu'.get_class($obj), 'read', $obj)=="YES")?true:false;
      if(! $canDownload){
        unset($lstDoc[$key]);
      }
      if( $forbidDownload=="YES" and $obj->locked and $obj->idLocker!=$currentUser->id ){
        unset($lstDoc[$key]);
      }
    }
  }
  return array($lstAttach, $lstDoc);
}

function getGui() {
  $paramNewGui = Parameter::getUserParameter('newGui');
  if ($paramNewGui==='') {
    $dbVersion=Parameter::getGlobalParameter('dbVersion');
    if ($dbVersion==='') $paramNewGui='1';
  }
  if ($paramNewGui==='1') return "new";
  else return "std";
}
function isNewGui() {
  $auditBrowser=Audit::getBrowser();
  if (isset($auditBrowser['browser']) && $auditBrowser['browser']=='Internet Explorer') return false;
  return (getGui()=='new');
}

function replace_accents($string) {
  $accents = array('À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý','à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ð','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ');
  $woaccts = array('A','p','A','A','A','A','C','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','Y','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','o','u','u','u','u','y','y');
  return str_replace($accents, $woaccts, $string);
}

//
