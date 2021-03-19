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

/* ============================================================================
 * RiskType defines the type of a risk.
 */ 
require_once('_securityCheck.php');
class Type extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $code;
  public $idWorkflow;
  public $sortOrder=0;
  public $idle;
  public $description;
  public $_sec_Behavior;
  public $mandatoryDescription;
  public $_lib_mandatoryField;
  public $mandatoryResourceOnHandled;
  public $_lib_mandatoryOnHandledStatus;
  public $mandatoryResultOnDone;
  public $_lib_mandatoryOnDoneStatus;
  public $mandatoryResolutionOnDone;
  public $_lib_mandatoryResolutionOnDoneStatus;
  public $lockHandled;
  public $_lib_statusMustChangeHandled;
  public $lockDone;
  public $_lib_statusMustChangeDone;
  public $lockIdle;
  public $_lib_statusMustChangeIdle;
  public $lockCancelled;
  public $_lib_statusMustChangeCancelled;
  public $lockNoLeftOnDone;
  public $_lib_statusMustChangeLeftDone;
  public $showInFlash;
  public $internalData;
  public $scope;
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
  public $color;
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
  public static $_cacheClassList=array();
  public static $_cacheRestrictedTypesClass;
  public static $_cacheListRestritedTypesForClass;
  
// MTY - LEAVE SYSTEM
  private $___dFieldsAttributes=array();
// MTY - LEAVE SYSTEM
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="50%">${name}</th>
    <th field="code" width="10%">${code}</th>
    <th field="sortOrder" width="5%">${sortOrderShort}</th>
    <th field="nameWorkflow" width="20%" >${idWorkflow}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required", 
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
                                          "color"=>"hidden",
// END - ADD BY TABARY - NOTIFICATION SYSTEM                                          
                                          "idWorkflow"=>"required",
                                          "mandatoryDescription"=>"nobr",
                                          "mandatoryResourceOnHandled"=>"nobr",
                                          "mandatoryResultOnDone"=>"nobr",
                                          "mandatoryResolutionOnDone"=>"hidden",
                                          "_lib_mandatoryResolutionOnDoneStatus"=>"hidden",             
                                          "lockHandled"=>"nobr",
                                          "lockDone"=>"nobr",
                                          "lockIdle"=>"nobr",
                                          "lockCancelled"=>"nobr",
  										                    "internalData"=>"hidden",
                                          "showInFlash"=>"hidden",
                                          "scope"=>"hidden",
                                          "lockNoLeftOnDone"=>"hidden",
                                          "_lib_statusMustChangeLeftDone"=>"hidden",
                                          );
  
  private static $_databaseTableName = 'type';
  private static $_databaseCriteria = array();
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
// MTY - LEAVE SYSTEM
    // Can't modify code = "LEAVESYST" for scope 'Activity'
    if ($this->scope=="Activity" and $this->code=="LEAVESYST") {
        $this->___dFieldsAttributes['code'] = "readonly";
        $this->___dFieldsAttributes['idle'] = "readonly";
        $this->___dFieldsAttributes['idWorkflow'] = "readonly";
  }
// MTY - LEAVE SYSTEM
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }

    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
// MTY - LEAVE SYSTEM    
    /**
   * Get the dynamic attributes (or static if dynamic not found) of the field that name is passed in parameter
   * @param String $fieldName : The fieldName for witch get attributes
   * @return String Attributes of the field
   */
  public function getFieldAttributes($fieldName) {
    if (array_key_exists ( $fieldName, $this->___dFieldsAttributes )) {
      return $this->___dFieldsAttributes[$fieldName];
    } else {
        return parent::getFieldAttributes($fieldName);
    }      
  }
// MTY - LEAVE SYSTEM  
  
    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  /** =========================================================================
   * 
   * @return 
   */
  public static function getClassList($includeProjectType=false) {
    global $hideAutoloadError;
    if (self::$_cacheClassList and isset(self::$_cacheClassList[$includeProjectType])) {
      return self::$_cacheClassList[$includeProjectType];
    } else if (getSessionValue('typeClassList_'.$includeProjectType)) {
      self::$_cacheClassList[$includeProjectType]=getSessionValue('typeClassList_'.$includeProjectType);
      return self::$_cacheClassList[$includeProjectType];
    }
    $hideAutoloadError=true;
    $dir='../model/';
    $handle = opendir($dir);
    $result=array();
    if ($includeProjectType) $result['ProjectType']=i18n('ProjectType');
    while ( ($file = readdir($handle)) !== false) {
      if ($file == '.' || $file == '..' || $file=='index.php' // exclude ., .. and index.php
      || substr($file,-4)!='.php'                             // exclude non php files
      || substr($file,-8)!='Type.php' || strlen($file)<=8) {  // exclude non *Type.php
        continue;
      }
      $class=pathinfo($file,PATHINFO_FILENAME);
      $ext=pathinfo($file,PATHINFO_EXTENSION);
      $classObj=substr($class,0,strlen($class)-4);
      if (SqlElement::is_subclass_of ( $class, 'Type') and SqlElement::class_exists($classObj)) {
        $result[$class]=i18n($class);
      }
    }
    closedir($handle);
    asort($result);
    setSessionValue('typeClassList_'.$includeProjectType,$result);
    self::$_cacheClassList[$includeProjectType]=$result;
    return $result;
  }

  public static function getRestrictedTypes($idProject,$idProjectType,$idProfile) {
    if ($idProject) {
      $crit['idProject']=$idProject;
    } else if ($idProjectType) {
      $crit['idProjectType']=$idProjectType;
    } else if ($idProfile) {
      $crit['idProfile']=$idProfile;
    }
    $rtList=SqlList::getListWithCrit('RestrictType', $crit, 'idType');
    return $rtList;
  }
  public static function getRestrictedTypesClass($idProject,$idProjectType,$idProfile) {
    $key="$idProject#$idProjectType#$idProfile";
    if (self::$_cacheRestrictedTypesClass and isset(self::$_cacheRestrictedTypesClass[$key])) {
      return self::$_cacheRestrictedTypesClass[$key];
    } else {
      $sessionValue=getSessionValue('restrictedTypesClass',array());
      if ($sessionValue and isset($sessionValue[$key])) {
        if (!self::$_cacheRestrictedTypesClass) self::$_cacheRestrictedTypesClass=array();
        self::$_cacheRestrictedTypesClass[$key]=$sessionValue[$key];
        return self::$_cacheRestrictedTypesClass[$key];
      }
    }
    if (!$sessionValue) $sessionValue=array();
    if (!self::$_cacheRestrictedTypesClass) self::$_cacheRestrictedTypesClass=array();
    $listClass=SqlList::getList('Type','scope');    
    $result=array();
    $list=self::getRestrictedTypes($idProject,$idProjectType,$idProfile);
    foreach ($list as $id=>$val) {
      if (isset($listClass[$val]) and ! isset($result[$listClass[$val]])) {
        $result[$listClass[$val]]=i18n($listClass[$val]);
      }
    }
    asort($result);
    self::$_cacheRestrictedTypesClass[$key]=$result;
    $sessionValue[$key]=$result;
    setSessionValue('restrictedTypesClass', $sessionValue);
    return $result;
  }
  public static function listRestritedTypesForClass($class,$idProject,$idProjectType,$idProfile,$exclusive=false) {
    //$key="$class#".implode(',',$idProject)."#$idProjectType#$idProfile";
    /*if (self::$_cacheListRestritedTypesForClass and isset(self::$_cacheListRestritedTypesForClass[$key])) {
      return self::$_cacheListRestritedTypesForClass[$key];
    } else {
      $sessionValue=getSessionValue('listRestritedTypesForClass',array());
      if ($sessionValue and isset($sessionValue[$key])) {
        if (!self::$_cacheListRestritedTypesForClass) self::$_cacheListRestritedTypesForClass=array();
        self::$_cacheListRestritedTypesForClass[$key]=$sessionValue[$key];
        return self::$_cacheListRestritedTypesForClass[$key];
      }
    }*/
    if (!isset($sessionValue) or !$sessionValue) $sessionValue=array();
    if (!self::$_cacheListRestritedTypesForClass) self::$_cacheRestrictedTypesClass=array();
    $result=array();
    if ($idProject ) {
      $result=SqlList::getListWithCrit('RestrictType', array('idProject'=>$idProject, 'className'=>$class),'idType');
      global $doNotRestrictLeave;
      $doNotRestrictLeave=true; // It is the only place where this is used... will have impact on ProjectMain::__construct()
                               // This way is done to avoid changing the signature of constructor
      if (is_array($idProject)) {
        $idProjectType=array();
        foreach($idProject as $idProj) {
          $proj=new Project($idProj,true);
          $idProjectType[$proj->idProjectType]=$proj->idProjectType;
        }
        $idProject=null;
      } else {
        $proj=new Project($idProject,true);
        $idProjectType=$proj->idProjectType;
      }
    } // else will retreive from project type
    if (!count($result) and $idProjectType) { // If no restrictions exist for the project, get restriction for type
      $result=SqlList::getListWithCrit('RestrictType', array('idProjectType'=>$idProjectType, 'className'=>$class),'idType');
    }
    if ($class=='ProjectType') {
      $idProfile=getSessionUser()->getProfile($idProject);
    }
    if (!$idProfile and $idProject and !$exclusive) { // If $exclusive is set, we are in definition (dialogRestrictType) so do not look for profile
      $idProfile=getSessionUser()->getProfile($idProject);
    }
    if ($idProfile) { // Apply restriction for Profile
      $lst=SqlList::getListWithCrit('RestrictType', array('idProfile'=>$idProfile, 'className'=>$class),'idType');
      if (!count($result)) { // Not restriction, for project or for project type
        $result=$lst;
      } else {
        if (count($lst)>0) {
          foreach ($result as $id=>$val) {
            if (!in_array($val,$lst)) {
              unset ($result[$id]);
            }
          }
          if (count($result)==0) {
            $result[0]=0;
          }
        }
      }
    }
    //self::$_cacheListRestritedTypesForClass[$key]=$result;
    //$sessionValue[$key]=$result;
    //setSessionValue('listRestritedTypesForClass', $sessionValue);
    return $result;
  }
  
  public static function getSpecificRestrictTypeValue($idType,$idProject,$idProjectType,$idProfile) {
    $crit=array('idType'=>$idType);
    if ($idProject) {
      $crit['idProject']=$idProject;
    } else if ($idProjectType) {
      $crit['idProjectType']=$idProjectType;
    } else if ($idProfile) {
      $crit['idProfile']=$idProfile;
    } else {
      errorLog(" invalid call parameter for getSpecificRestrictTypeValue($idType,$idProject,$idProjectType,$idProfile)");
      $crit['id']='0'; // Error : Will return no value
    }
    $rt=SqlElement::getSingleSqlElementFromCriteria('RestrictType', $crit);
    if ($rt->id) return true;
    else return false;
  }
  
  public static function clearRestrictTypeCache() {
    self::$_cacheRestrictedTypesClass=null;
    unsetSessionValue('restrictedTypesClass');
    self::$_cacheListRestritedTypesForClass=null;
    unsetSessionValue('listRestritedTypesForClass');
  }
// MTY - LEAVE SYSTEM
    public function control() {
        $old = $this->getOld();
        $result = parent::control();
        $class = get_class($this);
        // Can't create or update type with code='LEAVESYST' and scope='Activity' if code change
        if ($class=="ActivityType" and $this->code=="LEAVESYST" and $this->code != $old->code) {
            if ($result!="OK") {$result .= '<br/>';} else {$result="";}
            $result .= i18n('CantGiveThisCode');
}
        return $result;
    }

    public function deleteControl() {
        $result = parent::deleteControl();
        // Can't Delete the following type : code='LEAVESYST' and scope='Activity' 
        if ($this->scope=="Activity" and $this->code=="LEAVESYST") {
            if ($result!="OK") {$result .= '<br/>';} else {$result="";}
            $result .= i18n('CantDeleteTypeForThisScopeAndCode');
        }
        return $result;
    }
    
    public function copy() {
        if ($this->scope=='Activity' and $this->code=='LEAVESYST') {
            $copyType = clone $this;
            $result = i18n ( get_class ( $this ) ) . ' #' . htmlEncode ( $this->id ) . ' ' . i18n ( 'cantCopyWithThisCode' );
            $result .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $copyType->id ) . '" />';
            $result .= '<input type="hidden" id="lastOperation" value="copy" />';
            $result .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
            
            $copyType->_copyResult= $result;
            return  $copyType;
        }
        return parent::copy();
        
    }
// MTY - LEAVE SYSTEM
}?>