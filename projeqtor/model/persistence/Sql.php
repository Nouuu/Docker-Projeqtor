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
 * Static method defining all persistance methods
 */
if (file_exists('../_securityCheck.php')) include_once('../_securityCheck.php');
class Sql {

  private static $connexion = NULL;
   
  // Database informations
  private static $dbType;
  private static $dbHost;
  private static $dbPort;
  private static $dbUser;
  private static $dbPassword;
  private static $dbName;
  private static $dbVersion=NULL;

  private static $isEnsembleAllowed=false;
   
  // Visible Information
  public static $lastQuery=NULL;           // the string of the last executed query
  public static $lastQueryType=NULL;       // the type of the last executed query : SELECT or UPDATE
  public static $lastQueryResult=NULL;     // the result of the last executed query
  public static $lastQueryNbRows=NULL;     // the number of rows returns of affected by the last executed query
  public static $lastQueryNewid=NULL;      // the new id of the last executed query, if it was an INSERT query
  public static $lastQueryNewObjectId=NULL;
  public static $lastQueryErrorMessage=NULL;
  public static $lastQueryErrorCode=NULL;
  public static $lastConnectError=NULL;
  public static $lastCopyId=NULL;  
  public static $maintenanceMode=false;

  /** ========================================================================
   * Constructor (private, because static access only) 
   * => no destructor for this class
   * @return void
   */
  private function __construct() {
  }
	
  /** =========================================================================
   * Execute a query on database and return the result
   * @param $sqlRequest the resquest to be executed. Can be SELECT, UPDATE, INSERT, DELETE or else
   * @return resource of result if query is SELECT, false either
   */
  static function query($sqlRequest=NULL) {
    global $debugQuery;
    if ($sqlRequest==NULL) {
      echo "SQL WARNING : empty query";
      traceLog("SQL WARNING : empty query");
      return FALSE;
    }
    // Security check for Sql Injection
    // Reject ;
    // Reject UNION, INTERSECT
    
    // Execute query
    $cnx = self::getConnection();
    self::$lastQueryErrorMessage=NULL;
    self::$lastQueryErrorCode=NULL;
    enableCatchErrors();
    $result = new PDOStatement();
    $checkResult="OK";
    try { 
    	$startMicroTime=microtime(true);
      $result = $cnx->query($sqlRequest);
      if (isset($debugQuery) and $debugQuery 
      and ( $debugQuery===true or strtolower($debugQuery)==strtolower(substr($sqlRequest,0,strlen($debugQuery) ) ) )  ) {
        debugTraceLog(round((microtime(true) - $startMicroTime)*1000000)/1000000 . ";" . $sqlRequest);
      }
      if (! $result) {
        self::$lastQueryErrorMessage=i18n('sqlError'). ' : ' .$cnx->errorCode() . "<br/><br/>" . $sqlRequest;
        self::$lastQueryErrorCode=$cnx->errorInfo(); 
        errorLog('Error-[' . self::$lastQueryErrorCode . '] ' .self::$lastQueryErrorMessage);
        $checkResult="ERROR";       
      }
    } catch (PDOException $e) {
    	if (self::$dbVersion!='0.0.0') { // we get the version, if not set, may be normal : initial configuration. Must not log error
        $checkResult="EXCEPTION";
	      self::$lastQueryErrorMessage=$e->getMessage();
	      self::$lastQueryErrorCode=$e->getCode();
	      global $globalSilentErrors;
	      if ($globalSilentErrors) {
	        return false;
	      }
	      errorLog('Exception-[' . self::$lastQueryErrorCode . '] ' .self::$lastQueryErrorMessage);
	      errorLog('   For query : '.$sqlRequest);
	      errorLog('   Strack trace :');
	      $traces = debug_backtrace();
	      foreach ($traces as $idTrace=>$arrayTrace) {
	      	errorLog("   #$idTrace "
	      	  . ((isset($arrayTrace['class']))?$arrayTrace['class'].'->':'')
	      	  . ((isset($arrayTrace['function']))?$arrayTrace['function'].' called at ':'')
	      	  . ((isset($arrayTrace['file']))?'['.$arrayTrace['file']:'')
	      	  . ((isset($arrayTrace['line']))?':'.$arrayTrace['line']:'')
	      	  . ((isset($arrayTrace['file']))?']':'')
	      	  );
	      }
	      return false;
    	}
    }
    disableCatchErrors();
    // store informations about last query
    self::$lastQuery=$sqlRequest;
    self::$lastQueryResult=$result;
    self::$lastQueryType= (is_resource($result)) ? "SELECT" : "UPDATE";
    //if (strtoupper(substr($sqlRequest,0,6)=='SELECT')) self::$lastQueryType='SELECT';
    self::$lastQueryNbRows = (self::$lastQueryType=="SELECT") ? $result->rowCount() : $result->rowCount();
    self::$lastQueryNewid=null;
    if (self::$lastQueryType=="UPDATE") {
      if (self::isPgsql()) { // Specific update of sequence in pgsql mode.
      	if (strtolower(substr($sqlRequest,0,11))=='insert into' and !Sql::$maintenanceMode) {
      		$table=substr($sqlRequest,12,strpos($sqlRequest,'(')-13);
      		$seq=trim(strtolower($table)).'_id_seq';
      		if ($result->rowCount()) {
      		  $lastId=$cnx->lastInsertId($seq);
      		} else {
      		  $lastId=null;
      		}
      		self::$lastQueryNewid =($lastId)?$lastId:NULL;
      	}
      } else {   	
        self::$lastQueryNewid = ($cnx->lastInsertId()) ? $cnx->lastInsertId() : NULL ;
      }
    }
    if ($checkResult!='OK') {
    	return false;
    }
    return $result;
  }

  /** =========================================================================
   * Fetch the next line in a result set
   * @param $result
   * @return array of data, or false if no more line
   */
  static function fetchLine($result) {
    if ($result) {
      return $result->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  }
  
  /** =========================================================================
   * Begin a transaction
   * @return void
   */
  public static function beginTransaction() {
    $cnx=self::getConnection();
    if ( $cnx != NULL ) {
      error_reporting(E_ALL ^ E_WARNING);
      if (!$cnx->beginTransaction()) {      
        echo htmlGetErrorMessage("SQL ERROR : Error on Begin Transaction");
        errorLog("SQL ERROR : Error on Begin Transaction");
        exit; 
      }
      error_reporting(E_ALL ^ E_WARNING);
    }    
  }

  
  /** =========================================================================
   * Commit a transaction (validate the changes)
   * @return void
   */
  public static function commitTransaction() {
    $cnx=self::getConnection();
    if ( $cnx != NULL ) {
      error_reporting(E_ALL ^ E_WARNING);
      if (! $cnx->commit()) {      
        echo htmlGetErrorMessage("SQL ERROR : Error on Commit Transaction");
        errorLog("SQL ERROR : Error on Commit Transaction");
        exit; 
      }
      error_reporting(E_ALL ^ E_WARNING);
    }
  }

  
  /** =========================================================================
   * RoolBack a transaction (cancel the changes)
   * @return void
   */
  public static function rollbackTransaction() {
    $cnx=self::getConnection();
    if ( $cnx != NULL ) {
      error_reporting(E_ALL ^ E_WARNING);
      if (! $cnx->rollBack() ) {      
        echo htmlGetErrorMessage("SQL ERROR : Error on Rollback Transaction");
        errorLog("SQL ERROR : Error on Rollback Transaction");
        exit; 
      }
    }
  }
  
  
  /** =========================================================================
   * Replace in the string all the special caracters to ensure a valid query syntax
   * @param $string the string to be protected
   * @return the string, protected to ensure a correct sql query
   */
  public static function str($string, $objectClass=null) {
    // OK, validated, values are not escaped any more on check, but just while writing the query 
    /*if ($objectClass and $objectClass=="History") {
    	return $string; // for history saving, value have just been escaped yet, don't do it twice !
    }*/
  	$str=$string;
    // To be kept : if magic_quote_gpc is on, it would insert \' instead of ' and so on
//   	if (get_magic_quotes_gpc()) {
//       $str=str_replace('\"','"',$str);
//       $str=str_replace("\'","'",$str);
//       $str=str_replace('\\\\','\\',$str);
//     }   
    $str=str_replace(chr(8),'',$str);
    if ($str===null) $str='';
    $cnx=self::getConnection();
    return $cnx->quote($str);
  }
   
  
  /** =========================================================================
   * Return the connexion. Private. Only for internal use.
   * @return resource connexion to database
   */
  public static function getConnection($forceToReconnect=false) {
    global $enforceUTF8;
    if (self::$connexion != NULL and $forceToReconnect==false) {
    	//if (mysql_ping(self::$connexion)) {
        return self::$connexion;
    	//}
    }
    if (!self::$dbType or !self::$dbHost or !self::$dbName or ! self::$dbPort) {
      self::$dbType=Parameter::getGlobalParameter('paramDbType');
      self::$dbHost=Parameter::getGlobalParameter('paramDbHost');
      self::$dbPort=Parameter::getGlobalParameter('paramDbPort');
      self::$dbUser=Parameter::getGlobalParameter('paramDbUser');
      self::$dbPassword=Parameter::getGlobalParameter('paramDbPassword');
      self::$dbName=Parameter::getGlobalParameter('paramDbName');     
    }
    if (self::$dbType != "mysql" and self::$dbType != "pgsql") {
    	$logLevel=Parameter::getGlobalParameter('logLevel');
      if ($logLevel>=3) {
        echo htmlGetErrorMessage("SQL ERROR : Database type unknown '" . self::$dbType . "' \n");
      } else {
        echo htmlGetErrorMessage("SQL ERROR : Database type unknown");
      }
      errorLog("SQL ERROR : Database type unknown '" . self::$dbType . "'");
      self::$lastConnectError="TYPE";
      exit;
    }
    //restore_error_handler();
    //error_reporting(0);
    enableCatchErrors();
    if (self::$dbType == "mysql") {
      ini_set('mysql.connect_timeout', 10);
    }
    try {     
      //KEVIN
      $sslArray=array();
      $sslKey=Parameter::getGlobalParameter("SslKey");
      if($sslKey and !file_exists($sslKey)){
        traceLog("Error for SSL Key : file $sslKey do not exist");
        $sslKey=null;
      } 
           
      $sslCert=Parameter::getGlobalParameter("SslCert");
      if($sslCert and !file_exists($sslCert)){
        traceLog("Error for SSL Certification : file $sslCert do not exist");
        $sslCert=null;
      }
            
      $sslCa=Parameter::getGlobalParameter("SslCa");
      if($sslCa and !file_exists($sslCa)){
        traceLog("Error for SSL Certification Authority : file $sslCa do not exist");
        $sslCa=null;
      }
      
      if($sslKey and $sslCert and $sslCa){
        $sslArray=array(
          PDO::MYSQL_ATTR_SSL_KEY  => $sslKey,
          PDO::MYSQL_ATTR_SSL_CERT => $sslCert,
          PDO::MYSQL_ATTR_SSL_CA   => $sslCa
        );
	    }
	    $sslArray[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;
    	$dsn = self::$dbType.':host='.self::$dbHost.';port='.self::$dbPort.';dbname='.self::$dbName;
    	self::$connexion = new PDO($dsn, self::$dbUser, self::$dbPassword, $sslArray);
//     	self::$connexion = new PDO($dsn, self::$dbUser, self::$dbPassword,
    	self::$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	// Could solve some erroneous default storing in non utf8 format
    	if (self::$dbType == "mysql" and isset($enforceUTF8) and $enforceUTF8) {
    	  self::$connexion->query("SET NAMES utf8");
    	}
    }
    catch (PDOException $e) {
    	echo htmlGetErrorMessage($e->getMessage( )).'<br />';
    }
    if (self::$dbType == "mysql") {
      ini_set('mysql.connect_timeout', 60);
    }        
    disableCatchErrors();
    self::$lastConnectError=NULL;
    return self::$connexion;
  }
  public static function reconnect() {
    self::$connexion=null;
    self::getConnection(true);
  }

   /** =========================================================================
   * Return the version of the DataBase
   * @return the version of the DataBase, as String Vx.y.z or empty string if never initialized
   */
  public static function getDbVersion() {
    if (self::$dbVersion!=NULL) {
      return self::$dbVersion;
    }
    self::$dbVersion='0.0.0';
    $crit['idUser']=null;
    $crit['idProject']=null;
    $crit['parameterCode']='dbVersion';
    $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
    self::$dbVersion=NULL;
    if (! $obj or $obj->id==null) {
      return "";
    } else {
    	self::$dbVersion=$obj->parameterValue;
      return $obj->parameterValue;
    }
  }
  
   /** =========================================================================
   * Save the version of the DataBase
   * @return void
   */
  public static function saveDbVersion($vers) {
    $crit['idUser']=null;
    $crit['idProject']=null;
    $crit['parameterCode']='dbVersion';
    $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
    $obj->parameterValue=$vers;
    $obj->save();
  }
  
  // Retores the Sequence for PgSql
  public static function updatePgSeq($table) {
    if ($table=='tempupdate') return; // Exclude tables that don't have an ID
    $updateSeq=Sql::query("SELECT setval('".$table."_id_seq', (SELECT MAX(id) FROM $table));");
  }
  
  public static function isPgsql() {
  	if (! self::$dbType) {
  		self::$dbType=Parameter::getGlobalParameter('paramDbType');
  	}
  	if (self::$dbType=='pgsql') {
  		return true;
  	} else {
  		return false;
  	}
  } 

  public static function isMysql() {
    if (! self::$dbType) {
      self::$dbType=Parameter::getGlobalParameter('paramDbType');
    }
    if (self::$dbType=='mysql') {
      return true;
    } else {
      return false;
    }
  } 
  
  public static function fmtId($id) {
  	if ($id==null or $id=='*' or $id=='' or $id==' ') {
  		return '(-1)';
  	} else {
  	  return $id;
    }
  }
  
  public static function fmtStr($str) {
  	// Looks like Sql::str, but without surrounding quotes
  	$res=self::str($str);
  	$res=substr($res,1,strlen($res)-2);
  	return $res; 
  }
  
  public static function getYearFunction($str) {
    if (self::isPgsql()) {
      return "date_part('year', $str)";
    } else {
      return "year($str)";
    }
  }
  public static function resetConnection() {
    self::$connexion = NULL;
    self::$dbType = NULL;
    self::$dbHost = NULL;
    self::$dbPort = NULL;
    self::$dbUser = NULL;
    self::$dbPassword = NULL;
    self::$dbName = NULL;
  }
  
  public static function getDbCollation() {
    global $paramDbCollation;
    if (isset($paramDbCollation) and $paramDbCollation) return $paramDbCollation;
   	$value=getSessionTableValue('globalParametersArray', 'paramDbCollation');
   	if ($value) return $value;
  
   	// Not retreived, get from db
   	if (Sql::isMysql()) {
   		$value="utf8_general_ci"; // default
     	$dbName=Parameter::getGlobalParameter('paramDbName');
     	$query="SELECT DEFAULT_CHARACTER_SET_NAME as dbcharset, DEFAULT_COLLATION_NAME as dbcollation FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName';";
      $result=Sql::query($query);
     	if (Sql::$lastQueryNbRows > 0) {
     	  $line = Sql::fetchLine($result);
     	  $value=$line['dbcollation'];
     	}
     	setSessionTableValue('globalParametersArray', 'paramDbCollation', $value);
   	}
    return $value;
  }
  public static function getColumnCollation($table, $column) {
    global $paramDbCollation;
    if (isset($paramDbCollation) and $paramDbCollation) return $paramDbCollation; 
    $value=getSessionTableValue('globalParametersArray', 'paramDbCollation_'.$table.'_'.$column);
    if ($value) return $value;
  
    // Not retreived, get from db
    if (Sql::isMysql()) {
      $value="utf8_general_ci"; // default
      $dbName=Parameter::getGlobalParameter('paramDbName');
      $query="SELECT COLLATION_NAME as collationname FROM information_schema.columns "
           ."  WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'";
      $result=Sql::query($query);
      if (Sql::$lastQueryNbRows > 0) {
        $line = Sql::fetchLine($result);
        $value=$line['collationname'];
      }
      setSessionTableValue('globalParametersArray', 'paramDbCollation_'.$table.'_'.$column, $value);
    }
    return $value;
  }
  public static function getDbName() {
    if (!self::$dbName) {
      self::$dbName=Parameter::getGlobalParameter('paramDbName');
    }
    return self::$dbName;
  }
  public static function getDbPrefix() {
    return Parameter::getGlobalParameter('paramDbPrefix');
  }
  
}
?>