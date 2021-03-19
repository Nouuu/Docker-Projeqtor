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

require_once('../model/_securityCheck.php');
require_once('maintenanceFunctions.php');
require_once('../tool/configCheckPrerequisites.php');
$maintenance=true;
Sql::$maintenanceMode=true;
setSessionValue('setup', false, true);
// Version History : starts at 0.3.0 with clean database (before scripts are empty)
$versionHistory = array(
  "V0.3.0", "V0.4.0",  "V0.5.0",  "V0.6.0",  "V0.7.0",  "V0.8.0",  "V0.9.0",  
	"V1.0.0", "V1.1.0",  "V1.2.0",  "V1.3.0",  "V1.4.0",  "V1.5.0",  "V1.6.0",  "V1.7.0",  "V1.8.0",  "V1.9.0",
  "V2.0.0", "V2.0.1",  "V2.1.0",  "V2.1.1",  "V2.2.0",  "V2.3.0",  "V2.4.0",  "V2.4.1",  "V2.4.2",  "V2.5.0",  "V2.6.0",
  "V3.0.0", "V3.0.1",  "V3.1.0",  "V3.2.0",  "V3.3.0",  "V3.3.1",  "V3.4.0",  "V3.4.1",
  "V4.0.0", "V4.0.1",  "V4.1.-",  "V4.1.0",  "V4.2.0",  "V4.2.1",  "V4.3.0.a","V4.3.0",  "V4.3.2",  "V4.4.0",  "V4.5.0", "V4.5.3", "V4.5.6",
  "V5.0.0", "V5.1.0.a","V5.1.0",  "V5.1.1",  "V5.1.4",  "V5.1.5",  "V5.2.0",  "V5.2.2.a","V5.2.2",  "V5.2.3",  "V5.2.4", "V5.2.5", "V5.3.0.a", "V5.3.0", "V5.3.2", "V5.3.3", 
  "V5.4.0", "V5.4.2", "V5.4.3", "V5.4.4", "V5.4.5", "V5.5.0", "V5.5.2", "V5.5.3", 
  "V6.0.0", "V6.0.2", "V6.0.3", "V6.0.6", "V6.1.0", "V6.1.1", "V6.1.3", "V6.2.0", "V6.3.0", "V6.3.2", "V6.3.3", "V6.4.0", "V6.4.1", "V6.4.2", "V6.4.3", "V6.5.0", "V6.5.1", "V6.5.5", "V6.6.0",
	"V7.0.0", "V7.0.2", "V7.1.0", "V7.1.2", "V7.1.3", "V7.2.0", "V7.2.3", "V7.2.6", "V7.3.0", "V7.3.2", "V7.3.3", "V7.4.0", "V7.4.1", "V7.4.3",
	"V8.0.0", "V8.0.2", "V8.1.0", "V8.2.0", "V8.2.1", "V8.2.2", "V8.2.3", "V8.3.0", "V8.3.1", "V8.3.2","V8.3.4", "V8.3.5", "V8.4.0", "V8.4.1", "V8.5.0", "V8.5.1", "V8.6.0", "V8.6.1", "V8.6.2", "V8.6.5", 
  "V9.0.0", "V9.0.2", "V9.0.3", "V9.0.5"
);
$versionParameters =array(
  'V1.2.0'=>array('paramMailSmtpServer'=>'localhost',
                 'paramMailSmtpPort'=>'25',
                 'paramMailSendmailPath'=>null,
                 'paramMailTitle'=>'[Project\'Or RIA] ${item} #${id} moved to status ${status}',
                 'paramMailMessage'=>'The status of ${item} #${id} [${name}] has changed to ${status}',
                 'paramMailShowDetail'=>'true' ),
  'V1.3.0'=>array('defaultTheme'=>'blue'),
  'V1.4.0'=>array('paramReportTempDirectory'=>'../files/report/'),
  'V1.5.0'=>array('currency'=>'€', 
                  'currencyPosition'=>'after'),
  'V1.8.0'=>array('paramLdap_allow_login'=>'false',
					'paramLdap_base_dn'=>'dc=mydomain,dc=com',
					'paramLdap_host'=>'localhost',
					'paramLdap_port'=>'389',
					'paramLdap_version'=>'3',
					'paramLdap_search_user'=>'cn=Manager,dc=mydomain,dc=com',
					'paramLdap_search_pass'=>'secret',
					'paramLdap_user_filter'=>'uid=%USERNAME%')
);
$SqlEndOfCommand=";";
$SqlComment="--";
   
require_once (dirname(__FILE__) . '/../tool/projeqtor.php');
// New in V5.1 => check again prerequisites (may have been changed on new version, but only displays errors
if (checkPrerequisites()!="OK") {
  exit;
} 

$nbErrors=0;
if(file_exists("../files/cron/MIGRATION")){
  echo '<div class="messageERROR">'.i18n("messageUpgradeMigration").'</div>';
  exit;
} else {
  $filename = "../tool/i18n/nls/lang.js";
  fopen("../files/cron/MIGRATION","w");
}
$currVersion=Sql::getDbVersion();
traceLog("");
traceLog("=====================================");
traceLog("");
traceLog("DataBase actual Version = " . $currVersion );
traceLog("ProjeQtOr actual Version = " . $version );
traceLog("");
if ($currVersion=="") {
  $currVersion='V0.0.0';
  // if no current version, parameters are set through config.php
  //$versionParameters=array(); // Clear $versionParameter to avoid dupplication of parameters
  $versionParameters=array("V4.4.0"=>array('enforceUTF8'=>true)); // V4.4.0 set enforceUTF8 only for new fresh install
}
/*$arrVers=explode('.',substr($currVersion,1));
$currVer=$arrVers[0];
$currMaj=$arrVers[1];
$currRel=$arrVers[2];*/

if ($currVersion!='V0.0.0' and beforeVersion($currVersion,'V3.0.0') ) {
	$nbErrors+=runScript('V3.0.-');
}

foreach ($versionHistory as $vers) {
  /*$arrVers=explode('.',substr($vers,1));
  $histVer=$arrVers[0];
  $histMaj=$arrVers[1];
  $histRel=$arrVers[2];*/
  if ( beforeVersion($currVersion, $vers) ) {
    $nbErrors+=runScript($vers);
  }
}

// Set Session User with Admin Rights
$admin=new User();
$admin->idProfile=1;
setSessionUser($admin);

if ($currVersion=='V0.0.0') {
  traceLog ("create default project");
  $type=new ProjectType();
  $lst=$type->getSqlElementsFromCriteria(array('name'=>'Fixed Price'));
  $type=(count($lst)>0)?$lst[0]:null;
  $proj=new Project();
  $proj->color='#0000FF';
  $proj->description='Default project' . "\n" .
                     'For example use only.' . "\n" .
                     'Remove or rename this project when initializing your own data.';
  $proj->name='Default project';
  if ($type) {
    $proj->idProjectType=$type->id;
  }
  $result=$proj->save();
  $split=explode("<", $result);
  traceLog($split[0]);
  // For V4.4.0 initialize consolidateValidated for new installations (for others, keep previous behavior as defaut)
  $prm=new Parameter();
  $prm->parameterCode='consolidateValidated';
  $prm->parameterValue='IFSET';
  $prm->save();
  // New in V5 : Start Guide Page
  Parameter::storeUserParameter('startPage', 'startGuide.php',1);
  Parameter::storeGlobalParameter('newGui', '1');
  Parameter::storeGlobalParameter('newGuiThemeColor', '545381');
  Parameter::storeGlobalParameter('newGuiThemeColorBis', 'e97b2c');
  Parameter::storeGlobalParameter('paramScreen', 'left');
  Parameter::storeGlobalParameter('paramRightDiv', 'bottom');
  if (! isIE())Parameter::storeGlobalParameter('paramLayoutObjectDetail', 'tab');
  Parameter::storeUserParameter('menuLeftDisplayMode', 'ICONTXT');
  for($idRes=1; $idRes <= 2; $idRes++){
    Parameter::storeUserParameter('newGui', '1', $idRes);
	  UserMain::storeDefaultMenus($idRes);
  }
  
  enableCatchErrors();
  rename("../api/.htaccess.example","../api/.htaccess"); // Use exemple to "lock" API access (will use not existing password file)
  disableCatchErrors();
}

//echo "for V1.6.1<br/>";
// For V1.6.1
$tst=new ExpenseDetailType('1');
if (! $tst->id and beforeVersion($currVersion,"V1.6.1")) {
	$nbErrors+=runScript('V1.6.1');
}

$memoryLimitForPDF=Parameter::getGlobalParameter('paramMemoryLimitForPDF');
// For V1.7.0
if (! isset($memoryLimitForPDF) and beforeVersion($currVersion,"V3.0.0")) {
	writeFile('$paramMemoryLimitForPDF = \'512\';',$parametersLocation);
  writeFile("\n",$parametersLocation);
  traceLog('Parameter $paramMemoryLimitForPDF added');
}

// For V1.9.0
if (beforeVersion($currVersion,"V1.9.0") and $currVersion!='V0.0.0') {
  traceLog("update Reference [V1.9.0]");
	$adminFunctionality='updateReference';
	include('../tool/adminFunctionalities.php');
	echo "<br/>";
}

// For V1.9.1
if (beforeVersion($currVersion,"V1.9.1")) {
  traceLog("update affectations [V1.9.1]");
  // update affectations
  $aff=new Affectation();
  $affList=$aff->getSqlElementsFromCriteria(null, false);
  foreach ($affList as $aff) {
    $aff->save();
  }
}

// For V2.1.0
if (beforeVersion($currVersion,"V2.1.0")) {
  traceLog("update planning elements [2.1.0]");
  // update PlanningElements (progress)
  $pe=new PlanningElement();
  $peList=$pe->getSqlElementsFromCriteria(null, false);
  foreach ($peList as $pe) {
    $pe->save();
  }
}
// For V2.1.1
if (beforeVersion($currVersion,"V2.1.1")) {
  traceLog("update assignments [V2.1.1]");
  // update PlanningElements (progress)
  $ass=new Assignment();
  $assList=$ass->getSqlElementsFromCriteria(null, false);
  foreach ($assList as $ass) {
    $ass->saveWithRefresh();
  }
}

// For V2.4.1 & V2.4.2
if (beforeVersion($currVersion,"V2.4.2")) {
  traceLog("update dependencies for requirements [V2.4.2]");
  $req=new Requirement();
  $reqList=$req->getSqlElementsFromCriteria(null, false);
  foreach ($reqList as $req) {
  	$rq=new Requirement($req->id);
    $rq->updateDependencies();
  }
  $ses=new TestSession();
  $sesList=$ses->getSqlElementsFromCriteria(null, false);
  foreach ($sesList as $ses) {
  	$ss=new TestSession($ses->id);
    $ss->updateDependencies();
  }
  $tst=new TestCase();
  $tstList=$tst->getSqlElementsFromCriteria(null, false);
  foreach ($tstList as $tst) {
    $tc=new TestCase($tst->id);
    $tc->updateDependencies();
  }
}

// For V2.6.0 : migration of parameters to database
if (beforeVersion($currVersion,"V2.6.0")) {
  $arrayParamsToMigrate=array('paramDbDisplayName',
                              'paramMailTitle','paramMailMessage','paramMailSender','paramMailReplyTo','paramAdminMail',
                              'paramMailSmtpServer','paramMailSmtpPort','paramMailSendmailPath','paramMailShowDetail');
  migrateParameters($arrayParamsToMigrate); 
}
if (beforeVersion($currVersion,"V3.0.0")) {
  $arrayParamsToMigrate=array('paramLdap_allow_login', 'paramLdap_base_dn', 'paramLdap_host', 'paramLdap_port',
    'paramLdap_version', 'paramLdap_search_user', 'paramLdap_search_pass', 'paramLdap_user_filter',
    'paramDefaultPassword','paramPasswordMinLength', 'lockPassword',
    'paramDefaultLocale', 'paramDefaultTimezone', 'currency', 'currencyPosition',
    'paramFadeLoadingMode', 'paramRowPerPage', 'paramIconSize',
    'defaultTheme', 'paramPathSeparator', 'paramAttachmentDirectory', 'paramAttachmentMaxSize',
    'paramReportTempDirectory', 'paramMemoryLimitForPDF',
    'defaultBillCode','paramMailEol' 
    //'logFile', 'logLevel', 'paramDebugMode',
    );
  migrateParameters($arrayParamsToMigrate); 
}
if (afterVersion($currVersion,"V3.0.0") and beforeVersion($version,"V3.1.3") 
and ! strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' and $paramDbType=='mysql') { 
  traceLog("rename table workPeriod to workperiod [V3.1.3]");
	$paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
	$query="RENAME TABLE `".$paramDbPrefix."workPeriod` TO `".$paramDbPrefix."workperiod`;";
	$query=trim(formatForDbType($query));
  //Sql::beginTransaction();
  $result=Sql::query($query);
  //Sql::commitTransaction();
}

if (beforeVersion($currVersion,"V3.3.0") and $currVersion!='V0.0.0') {
  traceLog("update test sessions dates [V3.3.0]");
  $ses=new TestSession();
  $sesList=$ses->getSqlElementsFromCriteria(null, false);
  foreach ($sesList as $ses) {
    $ss=new TestSession($ses->id);
    $ss->TestSessionPlanningElement->validatedStartDate=$ss->startDate;
    $ss->TestSessionPlanningElement->validatedEndDate=$ss->endDate;
    $ss->save();
  }
}

if (beforeVersion($currVersion,"V3.4.0")) {
  traceLog("set default profile [V3.4.0]");
	$defProf=Parameter::getGlobalParameter('defaultProfile');
	if (! $defProf) {
		$prf=new Profile('5');
		if ($prf->profileCode=='G') {
			$param=New Parameter();
			$param->parameterCode='defaultProfile';
			$param->parameterValue=5;
			$param->idUser=null;
			$param->idProject=null;
			$param->save();
		}
	}
}

if (beforeVersion($currVersion,"V4.0.0")) {
  traceLog("delete old references to projectorria [V4.0.0]");
	// Deleting old files referencing projector or projectorria : these files have been renamed
  $root=$_SERVER['SCRIPT_FILENAME'];
	$root=substr($root,0,strpos($root, '/tool/'));
  if (! $root) { // On IIS, previous method does not return correct method 
	  $root=__FILE__;
	  $root=substr($root,0,strpos($root, '/db/'));
	}
	if (! $root) { // On Windows, previous method should fail
	  $root=__FILE__;
	  $root=substr($root,0,strpos($root, '\\db\\'));
	}	
	$files = glob($root.'/db/Projector_*.sql'); // get all file names
  error_reporting(0);
  enableCatchErrors();
  if ($files) {
	  foreach($files as $file){ // iterate files
	    if(is_file($file))
	      $perms = fileperms($file);
	      if ($perms & 0x0080) {
	        $do=@unlink($file); // delete file
	      } else {
	      	errorLog("Cannot delete file : ".$file);
	      } 
	  }
  }  
  $arrayFiles=array('/tool/projector.php',
    '/view/js/projector.js',
    '/view/js/projectorDialog.js',
    '/view/js/projectorFormatter.js',
    '/view/js/projectorWork.js',
    '/view/css/projector.css',
    '/view/css/projectorIcons.css',
    '/view/css/projectorPrint.css');
  foreach ($arrayFiles as $file) {
  	if (file_exists($root.$file)) {
  		$perms = fileperms($root.$file);
  		if ($perms & 0x0080) {
  		  $do=@unlink($root.$file);
  		} else {
        errorLog("Cannot delete file : ".$root.$file);
      } 
  	}
  }
  error_reporting(E_ALL);
  disableCatchErrors();
}

if (beforeVersion($currVersion,"V4.1.-")) {
	if (isset($flashReport) and ($flashReport==true or $flashReport=='true')) {
		$nbErrors+=runScript('V4.1.-.flash');
	}
}

if (beforeVersion($currVersion,"V4.2.0")) {
  traceLog("update user password changed date [4.2.0]");
	$user=new User();
	$userList=$user->getSqlElementsFromCriteria(null);
	foreach ($userList as $user) {
		if (! $user->passwordChangeDate) {
	    $user->passwordChangeDate=date('Y-m-d');
	    $user->save();
		}
	}
	
}
if (beforeVersion($currVersion,"V5.0.1") and $currVersion!='V0.0.0') {
  traceLog("update attachment on drive [5.0.1]");
  // Attachments : directory name changed from attachement_x to attachment_x
  $error=false;
  $attDir=Parameter::getGlobalParameter('paramAttachmentDirectory');
  if (file_exists($attDir)) {
    $handle = opendir($attDir);
    if (! $handle) $error=true;
    enableCatchErrors();
    while (!$error and ($file = readdir($handle)) !== false) {
      if ($file == '.' || $file == '..' || $file=='index.php') {
        continue;
      }
      $filepath = ($attDir == '.') ? $file : $attDir . '/' . $file;
      if (is_link($filepath)) {
        continue;
      }
      
      if (is_dir($filepath) and substr($file,0,12)=='attachement_') { 
        $newfilepath=str_replace('attachement_', 'attachment_', $filepath);
        $res=rename($filepath,$newfilepath);
        if (!$res) {
          traceLog("Error rename $filepath into $newfilepath");
          //$error=true;
        }
      }
    }
  } else {
    traceLog("WARNING : attachment directory '$attDir' not found");
  }
  traceLog("update attachment in table [5.0.1]");
  disableCatchErrors();
  $att=new Attachment();
  $lstAtt=$att->getSqlElementsFromCriteria(array()); // All attachments stored in DB
  $cpt=0;
  $cptCommit=1000;
  Sql::beginTransaction();
  traceLog("   => ".count($lstAtt)." attachments to read (may not all be updated)");
  foreach ($lstAtt as $att) {
    if ($att->subDirectory) {
      $arrayFrom=array('${attachementDirectory}','attachement_');
      $arrayTo=array('${attachmentDirectory}','attachment_');
      $att->subDirectory=str_replace($arrayFrom, $arrayTo, $att->subDirectory);
      $att->save();
      $cpt++;
      if ( ($cpt % $cptCommit) == 0) {
        Sql::commitTransaction();
        traceLog("   => $cpt attachments done...");
        projeqtor_set_time_limit(1500);
        Sql::beginTransaction();
      }
    }
  } 
  Sql::commitTransaction();
  traceLog("   => $cpt attachments updated");
}
if (beforeVersion($currVersion,"V5.0.2") and $currVersion!='V0.0.0') {
  traceLog("generate thumbs for resources [5.0.2]");
  Affectable::generateAllThumbs();
}
if (beforeVersion($currVersion,"V5.1.0.a")) {
  traceLog("update bill reference [5.1.0.a]");
  include_once("../tool/formatter.php");
  // Take into account of BillId and prefix/suffix to define new Reference format
  $prefix=Parameter::getGlobalParameter('billPrefix');
  $suffix=Parameter::getGlobalParameter('billSuffix');
  $length=Parameter::getGlobalParameter('billNumSize');
  $ref="$prefix{NUME}$suffix";
  Parameter::storeGlobalParameter('billReferenceFormat', $ref);
  $bill=new Bill();
  $bills=$bill->getSqlElementsFromCriteria(null,null, 'billId is not null', 'billId asc');
  foreach($bills as $bill) {
    $bill->reference=str_replace('{NUME}', numericFixLengthFormatter( $bill->billId,$length), $ref);
    $bill->save();
  }
}  

if (beforeVersion($currVersion,"V5.1.0.a") and $currVersion!='V0.0.0' and Sql::isMysql()) {
  // Must remove default enforceUTF8
  $maintenanceDisableEnforceUTF8=true;
  Parameter::regenerateParamFile();
}
if (beforeVersion($currVersion,"V5.1.5") and afterVersion($currVersion, "V5.1.0")) {
  // Fresh installs from 5.1.0 to 5.1.4 left many parameters in file, that were moved to database
  // must clean parameter file to enforce db value
  Parameter::regenerateParamFile();
}
if (beforeVersion($currVersion,"V5.2.0") and $currVersion!='V0.0.0') {
  traceLog("update work elements [5.2.0]");
  //setSessionUser(new User());
  $we=new WorkElement();
  $weList=$we->getSqlElementsFromCriteria(null,false, "realWork>0");
  $cpt=0;
  $cptCommit=100;
  Sql::beginTransaction();
  traceLog("   => ".count($weList)." to update");
  if (count($weList)<1000) {
    projeqtor_set_time_limit(1500);
  } else {
    traceLog("   => setting unlimited execution time for script (more than 1000 work elements to update)");
    projeqtor_set_time_limit(0);
  }
  foreach($weList as $we) {
    $res=$we->save();
    $cpt++; 
    if ( ($cpt % $cptCommit) == 0) {
      Sql::commitTransaction();
      traceLog("   => $cpt work elements done...");      
      Sql::beginTransaction();
    } 
  }
  Sql::commitTransaction();
  traceLog("   => $cpt work elements updated");
}

if (beforeVersion($currVersion,"V5.3.0") and $currVersion!='V0.0.0') {
  traceLog("update version project for versions of all components [5.3.0]");
  $comp=new Component();
  $compList=$comp->getSqlElementsFromCriteria(null,false,null,null,false,true); // List all components
  $cpt=0;
  $cptCommit=100;
  Sql::beginTransaction();
  traceLog("   => ".count($compList)." components to update");
  if (count($compList)<1000) {
    projeqtor_set_time_limit(1500);
  } else {
    traceLog("   => setting unlimited execution time for script (more than 1000 work elements to update)");
    projeqtor_set_time_limit(0);
  }
  foreach($compList as $comp) {
    $comp->updateAllVersionProject();
    $cpt++;
    if ( ($cpt % $cptCommit) == 0) {
      Sql::commitTransaction();
      traceLog("   => $cpt components done...");
      Sql::beginTransaction();
    }
  }
  Sql::commitTransaction();
  traceLog("   => $cpt components updated");
}

if ($currVersion=='V5.5.0' and Sql::isPgsql()) {
  traceLog("   => Fix issues on tenderstatus for PostgreSql database");
  traceLog("   => If issue has already been fixed, don't care about errors");
  $nbErrorsPg=runScript('V5.5.1.pg');
}
if (beforeVersion($currVersion,"V5.5.4") and $currVersion!='V0.0.0' and file_exists('../api/.htpasswd')) {
  traceLog("   => Removing default .htpassword file in API to avoid security leak");
  enableCatchErrors();
  $pwd=file_get_contents('../api/.htpasswd');
  if (strpos($pwd,'admin:$apr1$31cb5jwm$Ae3XumMQ1ckxUerDZoi290')!==null) {
    if (! rename('../api/.htpasswd','../api/.htpasswd.sav') ) {
      traceLog("   => Could not rename ../api/.htpasswd - this can be a security leak");
      echo "Could not rename file '../api/.htpasswd' - this can be a security leak<br/>";
      echo "Try and rename or remove this file to secure your data<br/><br/>";
      $nbErrors++;
    }
  }
  disableCatchErrors();
}
if ($currVersion=="V6.0.0" or $currVersion=="V6.0.1" ) {
  enableCatchErrors();
  if (file_exists('../model/OrganizationPlanningElement.php')) {
    if ( ! kill('../model/OrganizationPlanningElement.php') ) {
      
    }
  }
  if (file_exists('../model/OrganizationPlanningElementMain.php')) {
    if ( ! kill('../model/OrganizationPlanningElementMain.php') ) {
      
    }
  }
  disableCatchErrors();
}

if (beforeVersion($currVersion,"V6.1.2") and $currVersion!='V0.0.0') {
	traceLog("update assignment were cost is null [6.1.0]");
	//setSessionUser(new User());
	$ass=new Assignment();
	$assList=$ass->getSqlElementsFromCriteria(null,false, "realCost is null and realWork is not null and newDailyCost is not null");
	$cpt=0;
	$cptCommit=100;
	Sql::beginTransaction();
	traceLog("   => ".count($assList)." to update");
	if (count($assList)<100) {
		projeqtor_set_time_limit(1500);
	} else {
		traceLog("   => setting unlimited execution time for script (more than 100 assignments to update)");
		projeqtor_set_time_limit(0);
	}
	foreach($assList as $ass) {
		$res=$ass->saveWithRefresh();
		$cpt++;
		if ( ($cpt % $cptCommit) == 0) {
			Sql::commitTransaction();
			traceLog("   => $cpt assignments done...");
			Sql::beginTransaction();
		}
	}
	Sql::commitTransaction();
	traceLog("   => $cpt assignments updated");
}

if (beforeVersion($currVersion,"V6.3.0") and $currVersion!='V0.0.0') {
  SqlElement::$_doNotSaveLastUpdateDateTime=true;
	traceLog("update idProject and idle on notes");
	//setSessionUser(new User());
	$note=new Note();
	$noteList=$note->getSqlElementsFromCriteria(null,false);
	$cpt=0;
	$cptCommit=100;
	Sql::beginTransaction();
	traceLog("   => ".count($noteList)." to update");
	if (count($noteList)<100) {
		projeqtor_set_time_limit(1500);
	} else {
		traceLog("   => setting unlimited execution time for script (more than 100 notes to update)");
		projeqtor_set_time_limit(0);
	}
	foreach($noteList as $note) {
		$res=$note->save();
		$cpt++;
		if ( ($cpt % $cptCommit) == 0) {
			Sql::commitTransaction();
			traceLog("   => $cpt notes done...");
			Sql::beginTransaction();
		}
	}
	Sql::commitTransaction();
	traceLog("   => $cpt notes updated");
	SqlElement::$_doNotSaveLastUpdateDateTime=false;
}
if ($currVersion=='V6.3.0' and Sql::isPgsql()) {
  $nbErrorsPg=runScript('V6.3.1.pg');
}

//ADD qCazelles
if (beforeVersion($currVersion,'V6.5.0')) {
	traceLog("Create delivery types from deliverable types");
  $deliverableType=new DeliverableType();
  $list=$deliverableType->getSqlElementsFromCriteria(null);
  $workflow=new Workflow();
  $listWorkflow=$workflow->getSqlElementsFromCriteria(null, false, null, 'id asc');
  $workflow=$listWorkflow[0];
  foreach ($list as $deliverableType) {
    $deliveryType = new DeliveryType();
    foreach ($deliverableType as $attribute => $val) {
      $deliveryType->$attribute = $val;
    }
    $deliveryType->id = null;
    $deliveryType->scope = 'Delivery';
    $deliveryType->idWorkflow = $workflow->id;
    $res=$deliveryType->save();
    $delivery = new Delivery();
    $deliveries=$delivery->getSqlElementsFromCriteria(array('idDeliveryType' => $deliverableType->id));
    foreach ($deliveries as $delivery) {
      $delivery->idDeliveryType = $deliveryType->id;
      $res=$delivery->save();
    }
  }
  //END ADD qCazelles
  
  $pl=new ProductLanguage();
  $critWhere='scope is null';
  $list=$pl->getSqlElementsFromCriteria(null,null,$critWhere);
  if (count($list)>0) {
    traceLog("Purge of ProductLanguage");
    $cpt=0;
    $cptCommit=100;
    Sql::beginTransaction();
    traceLog("   => ".count($list)." to remove");
    foreach($list as $pl) {
      errorLog("***** ProductLanguage cannot be identified as Product or Version for language '".Sqllist::getNameFromId('Language', $pl->idLanguage)."', as it could be :");
      errorLog("            Product #".$pl->idProduct." - ".Sqllist::getNameFromId('Product', $pl->idProduct));
      errorLog("            Version #".$pl->idProduct." - ".Sqllist::getNameFromId('Version', $pl->idProduct));
      errorLog("----- ProductLanguage deleted");
  		$res=$pl->delete();
  		$cpt++;
  		$nbErrors++;
  		if ( ($cpt % $cptCommit) == 0) {
  			Sql::commitTransaction();
  			traceLog("   => $cpt ProductLanguage done...");
  			Sql::beginTransaction();
  		}
  	}
  	Sql::commitTransaction();
  	traceLog("   => $cpt ProductLanguage deleted");
  }
  $pc=new ProductContext();
  $critWhere='scope is null';
  $list=$pc->getSqlElementsFromCriteria(null,null,$critWhere);
  if (count($list)>0) {
    traceLog("Purge of ProductContext");
    $cpt=0;
    $cptCommit=100;
    Sql::beginTransaction();
    traceLog("   => ".count($list)." to remove");
    foreach($list as $pc) {
      errorLog("***** ProductContext cannot be identified as Product or Version for context '".Sqllist::getNameFromId('Context', $pc->idContext)."', as it could be :");
      errorLog("            Product #".$pc->idProduct." - ".Sqllist::getNameFromId('Product', $pc->idProduct));
      errorLog("            Version #".$pc->idProduct." - ".Sqllist::getNameFromId('Version', $pc->idProduct));
      errorLog("----- ProductContext deleted");
    		$res=$pc->delete();
    		$cpt++;
    		$nbErrors++;
    		if ( ($cpt % $cptCommit) == 0) {
    		  Sql::commitTransaction();
    		  traceLog("   => $cpt ProductContext done...");
    		  Sql::beginTransaction();
    		}
    }
    Sql::commitTransaction();
    traceLog("   => $cpt ProductContext deleted");
  }
}

if ($currVersion=='V7.1.0') {
  error_reporting(0);
  enableCatchErrors();
  enableSilentErrors();
  $rta=new ResourceTeamAffectation(1);
  disableSilentErrors();
  if (Sql::$lastQueryErrorCode) {
    traceLog("Rename table resourceTeamAffectation into lowercase");
    $nbErrors+=runScript('V7.1.1.linux');
  }
  error_reporting(E_ALL);
  disableCatchErrors();
}

if (beforeVersion($currVersion,'V7.2.0')) {
    // Retreive Timesheet alerts parameters from old system to new format
    $alertGenerationDay=Parameter::getGlobalParameter('imputationAlertGenerationDay');
    $alertGenerationHour=Parameter::getGlobalParameter('imputationAlertGenerationHour');
    $alertControlDay=Parameter::getGlobalParameter('imputationAlertControlDay');
    $alertControlNumberOfDays=Parameter::getGlobalParameter('imputationAlertControlNumberOfDays');
    $alertSendToTeamManager=Parameter::getGlobalParameter('imputationAlertSendToTeamManager');
    $arrayDest = array('Resource', 'ProjectLeader', 'TeamManager', 'OrganismManager');
    Parameter::storeGlobalParameter('imputationAlertSendToOrganismManager', $alertSendToTeamManager);
    foreach ($arrayDest as $dest){
        Parameter::storeGlobalParameter('imputationAlertControlDay'.$dest, $alertControlDay);
        Parameter::storeGlobalParameter('imputationAlertControlNumberOfDays'.$dest, $alertControlNumberOfDays);
        $cronExec=SqlElement::getSingleSqlElementFromCriteria('CronExecution',array('fonctionName'=>'cronImputationAlertCron'.$dest));
        $splitHour = explode(':',$alertGenerationHour);
        $hour = (isset($splitHour[0]))?$splitHour[0]:null;
        $minute = (isset($splitHour[1]))?$splitHour[1]:null;
        if($hour<10)$hour=substr($hour,1);
        if(isset($minute) && $minute!='' && isset($hour) && $hour!='' && isset($alertGenerationDay) && $alertGenerationDay!=''){
            $cronExec->cron=$minute.' '.$hour.' * * '.$alertGenerationDay;
        }else{
            $cronExec->cron='0 0 1 * *';
        }
        $cronExec->idle=(Parameter::getGlobalParameter('imputationAlertSendTo'.$dest)=="NO" || $alertControlDay=="NEVER" || $dest=="OrganismManager") ? 1 : 0;
        $cronExec->save();
    }
}
if (beforeVersion($currVersion,'V7.4.0')) {
  PlanningElement::$_noDispatch=true;
  $pe=new PlanningElement();
  $list=$pe->getSqlElementsFromCriteria(null,null,null,'wbsSortable asc');
  if (count($list)>0) {
    traceLog("Reformat WBS for PlanningElement");
    $cpt=0;
    $cptCommit=100;
    Sql::beginTransaction();
    traceLog("   => ".count($list)." to save");
    projeqtor_set_time_limit(1500);
    foreach($list as $pe) {
    		$pe->wbsSave(false); // without sub-items
    		$cpt++;
    		if ( ($cpt % $cptCommit) == 0) {
    		  Sql::commitTransaction();
    		  traceLog("   => $cpt saved...");
    		  projeqtor_set_time_limit(1500);
    		  Sql::beginTransaction();
    		}
    }
    Sql::commitTransaction();
    traceLog("   => $cpt saved");
  }
  $peb=new PlanningElementBaseline();
  $list=$peb->getSqlElementsFromCriteria(null,null,null,'wbsSortable asc');
  if (count($list)>0) {
    traceLog("Reformat WBS for PlanningElementBaseline");
    $cpt=0;
    $cptCommit=100;
    Sql::beginTransaction();
    traceLog("   => ".count($list)." to save");
    projeqtor_set_time_limit(1500);
    foreach($list as $peb) {
      $peb->_noHistory=true;
    	$peb->wbsSortable=formatSortableWbs($peb->wbs);
    	$resTmp=$peb->saveForced();
      $cpt++;
      if ( ($cpt % $cptCommit) == 0) {
        Sql::commitTransaction();
        traceLog("   => $cpt saved...");
        projeqtor_set_time_limit(1500);
        Sql::beginTransaction();
      }
    }
    Sql::commitTransaction();
    traceLog("   => $cpt saved");
  }
  $pex=new PlanningElementExtension();
  $list=$pex->getSqlElementsFromCriteria(null,null,null);
  if (count($list)>0) {
    traceLog("Reformat WBS for PlanningElementExtension");
    $cpt=0;
    $cptCommit=100;
    Sql::beginTransaction();
    traceLog("   => ".count($list)." to save");
    projeqtor_set_time_limit(1500);
    foreach($list as $pex) {
      $pex->_noHistory=true;
      $pex->wbsSortable=formatSortableWbs($pex->wbs);
      $resTmp=$pex->saveForced();
      $cpt++;
      if ( ($cpt % $cptCommit) == 0) {
        Sql::commitTransaction();
        traceLog("   => $cpt saved...");
        projeqtor_set_time_limit(1500);
        Sql::beginTransaction();
      }
    }
    Sql::commitTransaction();
    traceLog("   => $cpt saved");
  }
}
if (beforeVersion($currVersion,"V8.0.0")) {
  setSessionValue('showModule', true);
  if (Parameter::getGlobalParameter('notificationSystemActiv')!='YES') {
    $mod=new Module(13);
    $mod->active=false;
    $mod->save();
  }
}
if (beforeVersion($currVersion,"V8.0.4")) {
  $crit="id in ".Project::getAdminitrativeProjectList(false);
  $prj=new Project();
  $prjList=$prj->getSqlElementsFromCriteria(null,null,$crit);
  foreach ($prjList as $prj) {
    Project::unsetNeedReplan($prj->id);
  }
}
if (beforeVersion($currVersion,"V8.0.5")) {
  $crit="refType='Milestone' and validatedCost is not null and validatedCost>0";
  $m=new MilestonePlanningElement();
  $mList=$m->getSqlElementsFromCriteria(null,null,$crit);
  if (count($mList)>0) {
    traceLog("Set validatedCost to zero for Milestones");
    $cpt=0;
    $cptCommit=100;
    Sql::beginTransaction();
    traceLog("   => ".count($mList)." to save");
    projeqtor_set_time_limit(1500);
    foreach ($mList as $m) {
      $m->validatedCost=0;
      $m->save();
      $cpt++;
      if ( ($cpt % $cptCommit) == 0) {
        Sql::commitTransaction();
        traceLog("   => $cpt saved...");
        projeqtor_set_time_limit(1500);
        Sql::beginTransaction();
      }
    }
    Sql::commitTransaction();
    traceLog("   => $cpt saved");
  }
}

if (afterVersion($currVersion,"V8.0.4") and beforeVersion($currVersion,"V8.1.3") and $currVersion!="V8.0.7") {
  $mpe=new MilestonePlanningElement();
  $mpeTable=$mpe->getDatabaseTableName();
  $h=new History();
  $hTable=$h->getDatabaseTableName();
  $crit="refType='MilestonePlanningElement' "
      ." and (colName='validatedCost' or colName='validatedWork') and oldValue is not null and newValue is null"
          ." and operationDate > '2019-06-24 00:00:00'"
              ." and (select $mpeTable.refType from $mpeTable where $mpeTable.id=$hTable.refId)!='Milestone'"
              ." and not exists (select 'x' from $hTable as xx$hTable where xx$hTable.refType like '%PlanningElement' and xx$hTable.refId=$hTable.refId and xx$hTable.colName=$hTable.colName and xx$hTable.operationDate>$hTable.operationDate)";
  $hList=$h->getSqlElementsFromCriteria(null,null,$crit, 'operationDate asc');
  if (count($hList)>0) {
    traceLog("Fix incorrectly reset validatedCost and validatedWork");
    $cpt=0;
    $cptCommit=100;
    Sql::beginTransaction();
    traceLog("   => ".count($hList)." to save");
    projeqtor_set_time_limit(1500);
    foreach ($hList as $h) {
      $pe=new PlanningElement($h->refId);
      if ($h->colName=='validatedCost') $pe->validatedCost=$h->oldValue;
      if ($h->colName=='validatedWork') $pe->validatedWork=$h->oldValue;
      $pe->save();
      $cpt++;
      if ( ($cpt % $cptCommit) == 0) {
        Sql::commitTransaction();
        traceLog("   => $cpt saved...");
        projeqtor_set_time_limit(1500);
        Sql::beginTransaction();
      }
    }
    Sql::commitTransaction();
    traceLog("   => $cpt saved");
  }
}

if (afterVersion($currVersion,"V8.1.0") and beforeVersion($currVersion,"V8.1.5") ) {
	// Issue existing for version 8.1.0 to 8.1.4
	$ap=new AccessProfile(10);
	if (!$ap->id) {
		$ap->id=10;
		$ap->name='accessReadOwnOnly';
		$ap->description=null;
		$ap->idAccessScopeRead=2;
		$ap->idAccessScopeCreate=1;
		$ap->idAccessScopeUpdate=1;
		$ap->idAccessScopeDelete=1;
		$ap->sortOrder=900;
		$ap->idle=0;
		$ap->save();
	}
}
				
if (beforeVersion($currVersion,"V8.2.0")) {  
  $timeZone = Parameter::getGlobalParameter('paramDefaultTimezone');
  if(substr($timeZone,0,6)== 'Europe'){
    Sql::beginTransaction();
    $MessageLegal = new MessageLegal();
    if(substr(Parameter::getGlobalParameter('paramDefaultLocale'),0,6)=='fr'){
      $MessageLegal->name = "Message RGPD";
      $MessageLegal->description = "Conformément aux exigences de la RGPD, nous vous informons que les données personnelles que nous collectons sur vous sont votre nom, votre adresse email professionnelle et les informations que vous enregistrez dans ProjeQtOr dans le cadre de votre travail.
          <br/>Nous stockons et utilisons ces données uniquement à titre professionnel dans le cadre de la gestion des projets auxquels vous participez.
          <br/>Ces données peuvent être mises à jour par l'administrateur de l'application. 
          Veuillez le contacter en cas de besoin. 
          Vous trouverez ses coordonnées dans la fenêtre \"A propos de ProjeQtOr\".";
    }else{
      $MessageLegal->name = "GPRD Message";
      $MessageLegal->description = "In accordance with the requirements of the GDPR, we inform you that the personal data we collect about you is your name, your professional e-mail address and the information you save in ProjeQtOr as part of your work.
          <br/> We only store and use this data for professional purposes as part of the management of the projects in which you participate.
          <br/> This data can be updated by the application administrator.
          Please contact him if needed.
          You will find its coordinates in the \"About ProjeQtOr\" window.";
    }
    $MessageLegal->endDate='2010-01-01 00:00:00';
    $MessageLegal->save();
    Sql::commitTransaction();
  }
}

if (beforeVersion($currVersion,"V8.2.1") and Sql::isPgsql()) {
  traceLog("   => Fix issues on tender for PostgreSql database");
  $nbErrorsPg=runScript('V8.2.1.pg');
}
if (beforeVersion($currVersion,"V8.2.3")) {
  $rp=SqlElement::getSingleSqlElementFromCriteria('ReportParameter', array('idReport'=>26, 'name'=>'showIdle'));
  if (! $rp->id) {
    $rp->idReport=26;
    $rp->name='showIdle';
    $rp->paramType='boolean';
    $rp->sortOrder=20;
    $rp->save();
  }
}

// Integration of plugin Live Meet
if (beforeVersion($currVersion,"V8.3.0")) {
  if (Plugin::isPluginEnabled("liveMeeting")) {
    // remove old plugin
    enableCatchErrors();
    kill("../model/custom/Meeting.php");
    kill("../model/custom/LiveMeeting.php");
    purgeFiles("../plugin/liveMeeting", null);
    disableCatchErrors();
  } else {
    $nbErrorsPg=runScript('V8.3.0.lm');
  }
}
if (beforeVersion($currVersion,"V8.3.0")) {
  if (Plugin::isPluginInstalled("kanban")) {
    // remove old plugin
    enableCatchErrors();
    purgeFiles("../plugin/kanban", null);
    disableCatchErrors();
  } else {
    $nbErrorsPg=runScript('V8.3.0.kb');
    kanbanPostInstall();
  }
}
if (beforeVersion($currVersion,"V8.3.3")) {
  $plg=new Plugin();
  $plg->purge("name='kanban'");
}
if (beforeVersion($currVersion,"V8.3.7") and $currVersion!='V0.0.0') {
  $mail=new Mail();
  $mailList=$mail->getSqlElementsFromCriteria(null,null,"mailTitle like '%###PROJEQTOR###%'");
  foreach ($mailList as $mail) {
    $pos=max(strrpos($mail->mailTitle,"\n")+1,strrpos($mail->mailTitle,">")+1);
    if ($pos>0) {
      $mail->mailTitle=substr($mail->mailTitle,$pos);
      $mail->save();
    }
  }
}
if (beforeVersion($currVersion,"V8.6.0") and Sql::getDbPrefix()) {
  $nbErrors+=runScript('V8.6.0.lm');
}
if (beforeVersion($currVersion,"V8.6.0") and Sql::isMysql()) {
  $nbErrors+=runScript('V8.6.0.mysql');
}
if (beforeVersion($currVersion,"V9.0.0") and $currVersion!='V0.0.0') {
    Sql::beginTransaction();
    $MessageLegal = new MessageLegal();
    $MessageLegal->name = 'newGui';
    $MessageLegal->description ='<div>'.i18n('newGuiMessageLegalTop').'&nbsp;</div>
    
        <div>&nbsp;</div>
    
        <div style="margin-left:100px"><img onClick="showImage(\'Note\',\'../view/img/newGui.png\',\' \');" src="../view/img/newGui.png" style="width:500px;cursor:pointer;" title="'.i18n("clickToView").'"/></div>
    
        <div>'.i18n('newGuiMessageLegalBottom').'</div>';
    $MessageLegal->endDate='3721-07-21 21:21:21';
    $MessageLegal->save();
    $res = new ResourceAll();
    $resList = $res->getSqlElementsFromCriteria(array('isUser'=>'1'));
    $customRow[1]=array('Project', 'Activity', 'Milestone', 'Meeting', 'Planning', 'Resource', 'Reports');
    $customRow[2]=array('Ticket', 'Kanban', 'Imputation', 'Absence');
    foreach ($resList as $resource){
      $menuCustom = new MenuCustom();
      $countCustomMenuList = $menuCustom->countSqlElementsFromCriteria(array('idUser'=>$resource->id));
      if($countCustomMenuList > 0){
        $customMenuList = $menuCustom->getSqlElementsFromCriteria(array('idUser'=>$resource->id));
        $sortOrder = 1;
        foreach ($customMenuList as $menu){
          $menu->idRow = 1;
          $menu->sortOrder = $sortOrder;
          $menu->save();
          $sortOrder++;
        }
      }else{
        $sortOrder = 1;
        foreach ($customRow[1] as $menu){
        	$customMenu = new MenuCustom();
        	$customMenu->name = 'menu'.$menu;
        	$customMenu->idUser = $resource->id;
        	$customMenu->idRow = 1;
        	$customMenu->sortOrder = $sortOrder;
        	$customMenu->save();
        	$sortOrder++;
        }
        $sortOrder = 1;
        foreach ($customRow[2] as $menu){
        	$customMenu = new MenuCustom();
        	$customMenu->name = 'menu'.$menu;
        	$customMenu->idUser = $resource->id;
        	$customMenu->idRow = 2;
        	$customMenu->sortOrder = $sortOrder;
        	$customMenu->save();
        	$sortOrder++;
        }
      }
    }
    Sql::commitTransaction();
}
if (beforeVersion($currVersion,"V9.0.3") and $currVersion!='V0.0.0') {
  traceLog("update assignment were cost is null [9.0.3]");
  //setSessionUser(new User());
  $ass=new Assignment();
  $assList=$ass->getSqlElementsFromCriteria(null,false, "realCost is null and realWork is not null and dailyCost is not null");
  $cpt=0;
  $cptCommit=100;
  Sql::beginTransaction();
  KpiValue::$_noKpiHistory=true;
  traceLog("   => ".count($assList)." to update");
  if (count($assList)<100) {
    projeqtor_set_time_limit(1500);
  } else {
    traceLog("   => setting unlimited execution time for script (more than 100 assignments to update)");
    projeqtor_set_time_limit(0);
  }
  foreach($assList as $ass) {
    $res=$ass->saveWithRefresh();
    $cpt++;
    if ( ($cpt % $cptCommit) == 0) {
      Sql::commitTransaction();
      traceLog("   => $cpt assignments done...");
      Sql::beginTransaction();
    }
  }
  Sql::commitTransaction();
  traceLog("   => $cpt assignments updated");
}
if (beforeVersion($currVersion,"V9.0.5") and $currVersion!='V0.0.0') {
  traceLog("update assignment with unique ressource without assignmentSelection [9.0.5]");
  //setSessionUser(new User());
  $ass=new Assignment(); $assTable=$ass->getDatabaseTableName();
  $assSel=new AssignmentSelection(); $assSelTable=$assSel->getDatabaseTableName();
  $assList=$ass->getSqlElementsFromCriteria(null,false, "uniqueResource=1 and not exists (select 'x' from $assSelTable where $assSelTable.idAssignment=$assTable.id)");
  $cpt=0;
  $cptCommit=100;
  Sql::beginTransaction();
  KpiValue::$_noKpiHistory=true;
  traceLog("   => ".count($assList)." to update");
  if (count($assList)<100) {
    projeqtor_set_time_limit(1500);
  } else {
    traceLog("   => setting unlimited execution time for script (more than 100 assignments to update)");
    projeqtor_set_time_limit(0);
  }
  foreach($assList as $ass) {
    $res=AssignmentSelection::addResourcesFromPool($ass->id,$ass->idResource,null);
    $cpt++;
    if ( ($cpt % $cptCommit) == 0) {
      Sql::commitTransaction();
      traceLog("   => $cpt assignments done...");
      Sql::beginTransaction();
    }
  }
  Sql::commitTransaction();
  traceLog("   => $cpt assignments updated");
}

if (beforeVersion($currVersion,"V9.0.6")) {
  $nbErrors+=runScript('V9.0.6.lm');
}

// To be sure, after habilitations updates ...
Habilitation::correctUpdates();
Habilitation::correctUpdates();
Habilitation::correctUpdates();
deleteDuplicate();
Sql::saveDbVersion($version);
Parameter::clearGlobalParameters();
unsetSessionValue('_tablesFormatList');
if (file_exists(Parameter::getGlobalParameter('cronDirectory')) and Cron::check()=='running') Cron::restart();

traceLog('=====================================');
traceLog("");
echo '<div class="message'.(($nbErrors==0)?'OK':'WARNING').'">';
echo "__________________________________";
echo "<br/><br/>";
if ($nbErrors==0) {
  traceLog("DATABASE UPDATE COMPLETED TO VERSION " . $version);
  echo "DATABASE UPDATE COMPLETED <br/>TO VERSION " . $version;
} else {
  traceLog($nbErrors . " ERRORS DURING UPGRADE TO VERSION " . $version );
  echo $nbErrors . " ERRORS DURING UPGRADE <BR/>TO VERSION " . $version . "<br/>";
  echo "(details of errors in log file)";
}

traceLog("");
traceLog("=====================================");
traceLog("");
echo "<br/>__________________________________<br/><br/>";
echo '</div>';

unlink("../files/cron/MIGRATION");

// Check if installed plugins are compatible with new ProjeQtOr plugin
Plugin::checkPluginCompatibility($version);
if (file_exists('../plugin/screenCustomization/screenCustomizationFixDefinition.php')) {
  include_once('../plugin/screenCustomization/screenCustomizationFixDefinition.php');
}
Plugin::checkCustomDefinition();