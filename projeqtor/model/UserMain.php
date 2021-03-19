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
 * User is a resource that can connect to the application.
 */
require_once('_securityCheck.php');
class UserMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $_spe_image;
  public $name;
  public $resourceName;
  public $initials;
  public $email;
  public $idProfile;
  //ADD qCazelles - LANG
  public $idLanguage;
  //END ADD qCazelles - LANG
  public $locked;
  public $loginTry;
  
  public $isContact;
// MTY - LEAVE SYSTEM
  public $isEmployee=0;
// MTY - LEAVE SYSTEM
  public $isResource=0;
  // ADD tLaguerie #Ticket 396
  public $startDate; // start date as a resource, is hidden on display
  public $_lib_colAsResource;
  public $idRole;
// END tLaguerie #Ticket 396
// MTY - MULTI CALENDAR
  public $idCalendarDefinition;
// MTY - MULTI CALENDAR
  
  public $idle;
  public $description;
  public $_sec_Affectations;
  public $_spe_affectations;
  public $_sec_Asset;
  public $_spe_asset;
  public $_sec_Miscellaneous;
  public $password;
  public $_spe_buttonSendMail;
  public $isLdap;
  public $dontReceiveTeamMails;
  public $apiKey;
  public $idTeam;
  public $idOrganization;
  public $_arrayFilters=array();
  //public $_arrayFiltersId=array();
  public $_arrayFiltersDetail=array();
  //public $_arrayFiltersDetailId=array();
  public $salt;
  public $crypto;
  public $cookieHash;
  public $passwordChangeDate;
  public $_nbColMax=3;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="20%">${userName}</th>
    <th field="photo" formatter="thumb32" width="5%">${photo}</th>
    <th field="nameProfile" width="15%" formatter="translateFormatter">${idProfile}</th>
    <th field="resourceName" width="25%">${realName}</th>
    <th field="initials" width="10%">${initials}</th> 
    <th field="isResource" width="5%" formatter="booleanFormatter">${isResource}</th>
    <th field="isContact" width="5%" formatter="booleanFormatter">${isContact}</th>
    <th field="isLdap" width="5%" formatter="booleanFormatter">${isLdap}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("id"=>"",
                                          "name"=>"required, truncatedWidth100",
                                          "resourceName"=>"truncatedWidth100",
  		                                    "email"=>"truncatedWidth100",
  										                    "isLdap"=>"",
                                          "idProfile"=>"required",
                                          "loginTry"=>"hidden",
                                          "salt"=>'hidden', 
                                          "crypto"=>'hidden',
  		                                    "cookieHash"=>'hidden',
  		                                    "passwordChangeDate"=>'hidden',
  		                                    "apiKey"=>"readonly",
                                          'idTeam'=>'hidden',
                                           'idRole'=>'hidden',
                                          'idOrganization'=>'hidden',
// MTY - LEAVE SYSTEM
                                          'isEmployee'=>'hidden',
// MTY - LEAVE SYSTEM
// ADD tLaguerie Ticket #396
                                          'startDate'=>"nobr",
// END tLaguerie Ticket #396 
// MTY - MULTI CALENDAR
                                          'idCalendarDefinition'=> "hidden"
// MTY - MULTI CALENDAR   
  );  
  
  public $_calculateForColumn=array("name"=>"coalesce(fullName,concat(name,' #'))");
  
  private static $_databaseCriteria = array('isUser'=>'1');
  
  private static $_databaseColumnName = array('resourceName'=>'fullName');
  
  private static $_colCaptionTransposition = array('resourceName'=>'realName',
   'name'=> 'userName');
  
  private static $_databaseTableName = 'resource';
  
  private $_accessControlRights;
  
  public $_accessControlVisibility; // ALL if user should have all projects listed

  private $_affectedProjects;  // Array listing all affected projects
  private $_affectedProjectsIncludingClosed;  // Array listing all affected projects
  private $_specificAffectedProfiles; // Array listing all projects affected with profile different from default
  private $_specificAffectedProfilesIncludingClosed; // Array listing all projects affected with profile different from default
  private $_allProfiles;
  private $_allAccessRights;
  
  public $_visibleProjects;   // Array listing all visible projects (affected and their subProjects)
  private $_visibleProjectsIncludingClosed;
  private $_hierarchicalViewOfVisibleProjects;
  private $_hierarchicalViewOfVisibleProjectsNotClosed;
  public $_visibleProducts;
  public $_visibleVersions;
  private $_visibleProductsIncludingClosed;
  private $_visibleVersionsIncludingClosed;
  private $_visibleVersionsOnlyDelivered;
  private $_visibleVersionsIncludingClosedOnlyDelivered;
  
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    global $objClass;
    //$paramDefaultPassword=Parameter::getGlobalParameter('paramDefaultPassword');
  	parent::__construct($id,$withoutDependentObjects);
    
  	if (! $this->id and Parameter::getGlobalParameter('initializePassword')=="YES") {
  		//$this->salt=hash('sha256',"projeqtor".date('YmdHis'));
  		$this->crypto=null;
  		$this->password=User::getRandomPassword();
  	}
    if (! $this->id) {
      $this->idProfile=Parameter::getGlobalParameter('defaultProfile');
    }
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
   
  public function setAttributes() {
    // Fetch data to set attributes only to display user. Other access to User (for History) don't need these attributes.
    $crit=array("name"=>"menuContact");
    $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
    if (! $menu) {
      return;
    }
    if (securityCheckDisplayMenu($menu->id)) {
      $canUpdateContact=(securityGetAccessRightYesNo('menuContact', 'update', $this) == "YES");
    } else {
      $canUpdateContact=false;
    }
    if (!$canUpdateContact) {
      self::$_fieldsAttributes["isContact"]="readonly";
    } else {
      self::$_fieldsAttributes["isContact"]="";
    }
    $crit=array("name"=>"menuResource");
    $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
    if (! $menu) {
      return;
    }
    if (securityCheckDisplayMenu($menu->id)) {
      $canUpdateResource=(securityGetAccessRightYesNo('menuResource', 'update', $this) == "YES");
    } else {
      $canUpdateResource=false;
    }
    if (!$canUpdateResource) {
      self::$_fieldsAttributes["isResource"]="readonly";
      self::$_fieldsAttributes["resourceName"]="readonly";
    } else {
      self::$_fieldsAttributes["isResource"]="";
      self::$_fieldsAttributes["resourceName"]="truncatedWidth100";
    }
    if ($this->isResource or $this->isContact) {
      self::$_fieldsAttributes["resourceName"]="required,truncatedWidth100";
      self::$_fieldsAttributes["idRole"]="required";
    }
    if (!$canUpdateContact and !$canUpdateResource) {
      self::$_fieldsAttributes["resourceName"]="readonly,truncatedWidth100";
    } else {
      self::$_fieldsAttributes["resourceName"]="truncatedWidth100";
    }

    $paramLdap_allow_login=Parameter::getGlobalParameter('paramLdap_allow_login');
    if ($this->isLdap!=0 and isset($paramLdap_allow_login) and strtolower($paramLdap_allow_login)=='true') {
      self::$_fieldsAttributes["name"]="readonly, truncatedWidth100";
      //self::$_fieldsAttributes["resourceName"]="readonly";
      self::$_fieldsAttributes["email"]="readonly, truncatedWidth100";
      self::$_fieldsAttributes["password"]="hidden";
    }
    
    //gautier #4040
    if ($this->id==getSessionUser()->id){
      self::$_fieldsAttributes["password"]="hidden";
    }
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }  
  
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
   /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
	
  	return self::$_fieldsAttributes;
  }
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="isResource") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked || dijit.byId("isContact").get("checked")) { ';
      $colScript .= '    dijit.byId("resourceName").set("required", "true");';
      $colScript .= '    dojo.addClass(dijit.byId("resourceName").domNode,"required");';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("resourceName").set("required", null);';
      $colScript .= '    dojo.removeClass(dijit.byId("resourceName").domNode,"required");';
      $colScript .= '  } '; 
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    dijit.byId("idRole").set("required", "true");';
      $colScript .= '    dojo.addClass(dijit.byId("idRole").domNode,"required");';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idRole").set("required", null);';
      $colScript .= '    dojo.removeClass(dijit.byId("idRole").domNode,"required");';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    if ($colName=="isContact") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked || dijit.byId("isResource").get("checked")) { ';
      $colScript .= '    dijit.byId("resourceName").set("required", "true");';
      $colScript .= '    dojo.addClass(dijit.byId("resourceName").domNode,"required");';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("resourceName").set("required", null);';
      $colScript .= '    dojo.removeClass(dijit.byId("resourceName").domNode,"required");';
      //$colScript .= '    dijit.byId("resourceName").set("value", "");';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;

  }
  
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
    global $print, $outMode, $largeWidth;
    $result="";
    if ($item=='buttonSendMail') {
      $canUpdate=(securityGetAccessRightYesNo('menuUser', 'update', $this) == "YES");
      if ($print or !$canUpdate or ! $this->id or !$this->password) {
        return "";
      } 
      $result .= '<tr><td valign="top" class="label"><label></label></td><td>';
      $result .= '<button id="sendInfoToUser" dojoType="dijit.form.Button" showlabel="true" class="roundedVisibleButton" '; 
      $result .= ' title="' . i18n('sendInfoToUser') . '" >';
      $result .= '<span>' . i18n('sendInfoToUser') . '</span>';
      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
      $result .= '   if (checkFormChangeInProgress()) {return false;}';
	    $result .=  '  var email="";';
	    $result .=  '  if (dojo.byId("email")) {email = dojo.byId("email").value;}';
      $result .=  '  if (email==null || email=="") { ';
      $result .=  '    showAlert("' . i18n('emailMandatory') . '");';
	    $result .=  '  } else {';
      $result .=  '    loadContent("../tool/sendMail.php","resultDivMain","objectForm",true);';
	    $result .=  '  }';	
      $result .= '</script>';
      $result .= '</button>';
      $result .= '</td></tr>';
      return $result;
    } 
    if ($item=='affectations') {
      $aff=new Affectation();
      $critArray=array('idUser'=>(($this->id)?$this->id:'0'));
      $affList=$aff->getSqlElementsFromCriteria($critArray, false);
      drawAffectationsFromObject($affList, $this, 'Project', false);   
      return $result;
    }
    if ($item=='image' and $this->id ){
      if ($print) return "";
      $result=Affectable::drawSpecificImage(get_class($this),$this->id, $print, $outMode, $largeWidth);
    	return $result;
    }
    if($item=='asset') {
      $asset = new Asset();
      $critArray=array('idAffectable'=>(($this->id)?$this->id:'0'));
      $order = " idAssetType asc ";
      $assetList=$asset->getSqlElementsFromCriteria($critArray, false,null);
      drawAssetFromUser($assetList, $this);
      return $result;
    }
  }

  /** =========================================================================
   * Get the access rights for all the screens
   * For more information, refer to AccessControl.ofp diagram 
   * @return an array containing rights for every screen
   *  must be redefined in the inherited class
   */
  public function getAccessControlRights($obj=null) {
    scriptLog("getAccessControlRights(obj=".debugDisplayObj($obj).")");
    // _accessControlRights fetched yet, just return it
    SqlElement::$_cachedQuery['AccessProfile']=array();
    
    $profile=$this->idProfile;
    if ($obj) {
      $profile=$this->getProfile($obj);
    }
    if ($this->_accessControlRights and isset($this->_accessControlRights[$profile])) {       
      return $this->_accessControlRights[$profile];
    }        
    $menuList=SqlList::getListNotTranslated('Menu');
    $noAccessArray=array( 'read' => 'NO', 'create' => 'NO', 'update' => 'NO', 'delete' => 'NO','report'=>'NO');
    $allAccessArray=array( 'read' => 'ALL', 'create' => 'ALL', 'update' => 'ALL', 'delete' => 'ALL', 'report'=>'ALL');
    $readAccessArray=array( 'read' => 'ALL', 'create' => 'NO', 'update' => 'NO', 'delete' => 'NO', 'report'=>'ALL');
    // first time function is called for object, so go and fetch data
    if (!$obj) $this->_accessControlVisibility='PRO';
    $accessControlRights=array();
    $accessScopeList=SqlList::getList('AccessScope', 'accessCode',null, true);
    $accessScopeRW=SqlList::getList('ListReadWrite', 'code',null, true);
    $accessRight=new AccessRight();
    $noAccessAllowed=array();
    $crit=array('idProfile'=>$profile);
    $accessRightList=$accessRight->getSqlElementsFromCriteria( $crit, false);
    $habilitation=new Habilitation();
    $crit=array('idProfile'=>$profile);
    $habilitationList=$habilitation->getSqlElementsFromCriteria( $crit, false);
    foreach ($habilitationList as $hab) { // if allowAcces = 1 in habilitation (access to screen), default access is all
    	if (array_key_exists($hab->idMenu,$menuList)) {
    	  $menuName=$menuList[$hab->idMenu];
    	  if ($hab->allowAccess==1 and Module::isMenuActive($menuName)) {
    	    $accessControlRights[$menuName]=$allAccessArray;
    	  } else {
    	    $accessControlRights[$menuName]=$noAccessArray;
    	    $accessControlRights[$menuName]['report']='ALL';
    	    $noAccessAllowed[$menuName]=true;
    	  }
    	}
    }
    foreach ($accessRightList as $arObj) {
      $menuName=(array_key_exists($arObj->idMenu,$menuList))?$menuList[$arObj->idMenu]:'';
      if (! $menuName or ! array_key_exists($menuName, $accessControlRights) or ! Module::isMenuActive($menuName)) {
        $accessControlRights[$menuName]=$noAccessArray;	
      } else {
        $scopeArray=$noAccessArray;
        $accessProfile=new AccessProfileAll($arObj->idAccessProfile);
//      if (1 or $arObj->idAccessProfile<1000000) { 
          if ($accessProfile->id) {
            $scopeArray=array( 'read' =>  $accessScopeList[$accessProfile->idAccessScopeRead],
                               'create' => $accessScopeList[$accessProfile->idAccessScopeCreate],
                               'update' => $accessScopeList[$accessProfile->idAccessScopeUpdate],
                               'delete' => $accessScopeList[$accessProfile->idAccessScopeDelete],
                               'report' =>  $accessScopeList[$accessProfile->idAccessScopeRead], );
            if ($accessScopeList[$accessProfile->idAccessScopeRead]=='ALL' and $accessProfile->isNonProject==0) {
              if (!$obj) $this->_accessControlVisibility='ALL';
            }
          }
//      } else {     
//        if (isset($noAccessAllowed[$menuName]) and $noAccessAllowed[$menuName]) {
//        // Nothing
//        } else {
//          $RW=$accessScopeRW[$arObj->idAccessProfile];
//          if ($RW=='WRITE') {
//            $scopeArray=$allAccessArray;
//          } else {
//            $scopeArray=$readAccessArray;
//          }
//        }
//      }
        $accessControlRights[$menuName]=$scopeArray;
      }
    }
    foreach ($menuList as $menuId=>$menuName) {
      if (! array_key_exists($menuName, $accessControlRights)) {
        $accessControlRights[$menuName]=$noAccessArray; 
      }     	
    }
    // override with habilitation 
    if (! $this->_accessControlRights) {
      $this->_accessControlRights=array();
    }
    $this->_accessControlRights[$profile]=$accessControlRights;
    if ($this->id==getSessionUser()->id and isset($this->_isRetreivedFromSession) and $this->_isRetreivedFromSession) {
      setSessionUser($this); // Store user to cache Data
      
    }
    return $this->_accessControlRights[$profile];
  }

  /** =========================================================================
   * Get the list of all projects the resource corresponding to the user is affected to
   * @return a list of projects (id=>name)
   */
  public function getAffectedProjects($limitToActiveProjects=true) {
    if ($this->_affectedProjects and $limitToActiveProjects) {
      return $this->_affectedProjects;
    } else if ($this->_affectedProjectsIncludingClosed and ! $limitToActiveProjects) {
      return $this->_affectedProjectsIncludingClosed;  	
    }
    $result=array();
    $aff=new Affectation();
    $crit = array("idResource"=>$this->id);
    if ($limitToActiveProjects) {
    	$crit["idle"]='0';
    }
    $affList=$aff->getSqlElementsFromCriteria($crit,false);
    $resultToSort=array();
    foreach ($affList as $aff) {
      $prj=new Project($aff->idProject,true); 
      if (!$prj->id) continue;
      // PBE : to avoid security issue in case of wrong wbs numbering, suffix order with id
    	if (! isset($resultToSort[$prj->sortOrder.'-'.$prj->id])) {
	      $resultToSort[$prj->sortOrder.'-'.$prj->id]=array('id'=>$prj->id,'name'=>$prj->name);
	      $lstSubPrj=$prj->getRecursiveSubProjectsFlatList($limitToActiveProjects);
	      foreach ($lstSubPrj as $idSubPrj=>$nameSubPrj) {
	        $prjSub=new Project($idSubPrj,true);
	      	$resultToSort[$prjSub->sortOrder.'-'.$idSubPrj]=array('id'=>$prjSub->id,'name'=>$prjSub->name);
	      }
    	}
    }
    ksort($resultToSort);
    foreach ($resultToSort as $toSort) {
      $result[$toSort['id']]=$toSort['name'];
    }
    if ($limitToActiveProjects) {
      $this->_affectedProjects=$result;
    } else {
      $this->_affectedProjectsIncludingClosed=$result;
    }
    return $result;
  }
  /** =========================================================================
   * Get the list of all teams the resource is manager of
   * @return a list of projects (id=>name)
   */
  public function getManagedTeams($limitToActiveTeams=true) {
    $team=new Team();
    $crit=array('idResource'=>$this->id);
    if ($limitToActiveTeams) $crit['idle']='0';
    $list=SqlList::getListWithCrit('Team', $crit);
    return $list;
  }
  public function getManagedTeamResources($limitToActiveTeams=true,$returnAs='object') {
    $crit='idTeam in ' . transformListIntoInClause($this->getManagedTeams(true));
    $res=new Resource();
    $lstRes=$res->getSqlElementsFromCriteria(null, false, $crit, null, true, true);
    if ($returnAs=='list') {
      $result=array();
      foreach ($lstRes as $res) {
        $result[$res->id]=$res->name;
      }
    } else {
      $result=$lstRes;    }
    return $result;
  }
  
  /** =========================================================================
   * Get the list of all projects where affected profile is different from main profile
   * @return a list of projects (idProject=>idProfile)
   */
  public function getSpecificAffectedProfiles($limitToActiveProjects=true) {
    if ($this->_specificAffectedProfiles and $limitToActiveProjects) {
      return $this->_specificAffectedProfiles;
    } else if ($this->_specificAffectedProfilesIncludingClosed and ! $limitToActiveProjects) {
      return $this->_specificAffectedProfilesIncludingClosed;
    } else {
      $this->getVisibleProjects($limitToActiveProjects); // Will update_specificAffectedProfiles or _specificAffectedProfilesIncludingClosed
      if ($limitToActiveProjects) {
        return $this->_specificAffectedProfiles;
      } else {
        return $this->_specificAffectedProfilesIncludingClosed;
      }
    }
  }
  
  public function getAllProfiles() {
    if ($this->_allProfiles) {
      return $this->_allProfiles;
    } else {
      $this->getVisibleProjects(); // Will update_specificAffectedProfiles or _specificAffectedProfilesIncludingClosed
      return $this->_allProfiles;
    }
  }  

// ADD BY Marc TABARY - 2017-02-23 - NEW GETVISIBLE FUNCTIONS  
  /** =========================================================================
   * Get the list of all projects the user can have readable access to, 
   * this means the projects the resource corresponding to the user is affected to
   * and their sub projects
   * Difference with getVisibleProjects = Add a criteria for null or not 'foreign key'
   * @return a list of projects id
   */
  public function getVisibleProjectsNullForeignKey($limitToActiveProjects=true, $foreignKeyName='') {
    scriptLog("UserMain::getVisibleProjectsNullForeignKey(limitToActiveProjects=$limitToActiveProjects)");
    if ($foreignKeyName==null or trim($foreignKeyName)=='' or !property_exists('Project', $foreignKeyName)) {
        if ($limitToActiveProjects and $this->_visibleProjects) {
          return $this->_visibleProjects;
        }
        if (! $limitToActiveProjects and $this->_visibleProjectsIncludingClosed) {
          return $this->_visibleProjectsIncludingClosed;
        }
        $foreignKeyName='';
    }
        
    $result=array();
    // Retrieve current affectation profile for each project
    $resultAff=array();
    $resultProf=array();
    $resultProf[$this->idProfile]=$this->idProfile; // The default profile, even if used on no project
    if ($this->idProfile) {
      $resultProf[$this->idProfile]=$this->idProfile;
    }
    $affProfile=array();
    $aff=new Affectation();
    $crit = array("idResource"=>$this->id);
    if ($limitToActiveProjects) {
      $crit["idle"]='0';
    }
    $affList=$aff->getSqlElementsFromCriteria($crit,false, null,'idProject asc, startDate asc');
    $today=date('Y-m-d');
    foreach ($affList as $aff) {
      if ( (! $aff->startDate or $aff->startDate<=$today) and (! $aff->endDate or $aff->endDate>=$today)) {
        $affProfile[$aff->idProject]=$aff->idProfile;
        $resultProf[$aff->idProfile]=$aff->idProfile;
      }
    }
    $accessRightRead=securityGetAccessRight('menuProject', 'read');
    // For ALL, by default can have access to all projects
    if ($accessRightRead=="ALL") {
    	$listAllProjects=SqlList::getList('Project');
    	foreach($listAllProjects as $idPrj=>$namePrj) {
    		$result[$idPrj]=$namePrj;
    	}
    } 
    // Scpecific rights for projects affected to user : may change rights for ALL (admin)
    $affPrjList=$this->getAffectedProjects($limitToActiveProjects);
    $profile=$this->idProfile;
    foreach($affPrjList as $idPrj=>$namePrj) {
      // MTY - LEAVE SYSTEM
      // Don't take account the affectation if the project's is the LeaveProject and it's not visible for the connected user
      if ( isLeavesSystemActiv() && Project::isTheLeaveProject($idPrj) && !Project::isProjectLeaveVisible()) {continue;}
      // MTY - LEAVE SYSTEM  
        
      if (isset($affProfile[$idPrj])) {	        
        $profile=$affProfile[$idPrj];
        $resultAff[$idPrj]=$profile;
        $prj=new Project($idPrj,true);
        $lstSubPrj=$prj->getRecursiveSubProjectsFlatList($limitToActiveProjects);
        foreach ($lstSubPrj as $idSubPrj=>$nameSubPrj) {
          // MTY - LEAVE SYSTEM
          // Don't take account the sub-project if it's the LeaveProject and it's not visible for the connected user
          if (isLeavesSystemActiv() && Project::isTheLeaveProject($idSubPrj) && !Project::isProjectLeaveVisible()) {continue;}
          // MTY - LEAVE SYSTEM  
          $result[$idSubPrj]=$nameSubPrj;
          $resultAff[$idSubPrj]=$profile;
        }
      } 
    	$result[$idPrj]=$namePrj;
    }
    
    $this->_allProfiles=$resultProf;
    if ($foreignKeyName=='') {
        if ($limitToActiveProjects) {
          $this->_visibleProjects=$result;
          $this->_specificAffectedProfiles=$resultAff;
        } else {
          $this->_visibleProjectsIncludingClosed=$result;
          $this->_specificAffectedProfilesIncludingClosed=$resultAff;
        }
    } else {
        $whereClause = $foreignKeyName . ' is null';
        $prj = new Project();
        $listPrjForeignKeyNull = $prj->getSqlElementsFromCriteria(null,false,$whereClause);
        $listPrjForeignKeyIsNull=array();
        foreach ($listPrjForeignKeyNull as $prjList) {
          $listPrjForeignKeyIsNull[$prjList->id]=$prjList->name;
        }        
        $result = array_intersect_key($result, $listPrjForeignKeyIsNull);
    }
    if (getSessionUser()->id==$this->id) {
      setSessionUser($this); // Store user to cache Data
    }  
    return $result;
  }
// END ADD BY Marc TABARY - 2017-02-23 - NEW GETVISIBLE FUNCTIONS  
  
  /** =========================================================================
   * Get the list of all projects the user can have readable access to, 
   * this means the projects the resource corresponding to the user is affected to
   * and their sub projects
   * @return a list of projects id
   */  
  public function getVisibleProjects($limitToActiveProjects=true) {
    scriptLog("UserMain::getVisibleProjects(limitToActiveProjects=$limitToActiveProjects)");
    if ($limitToActiveProjects and $this->_visibleProjects) {
      return $this->_visibleProjects;
    }
    if (! $limitToActiveProjects and $this->_visibleProjectsIncludingClosed) {
      return $this->_visibleProjectsIncludingClosed;
    }
    $result=array();
    // Retrieve current affectation profile for each project
    $resultAff=array();
    $resultProf=array();
    $resultProf[$this->idProfile]=$this->idProfile; // The default profile, even if used on no project
    if ($this->idProfile) {
      $resultProf[$this->idProfile]=$this->idProfile;
    }
    $affProfile=array();
    $aff=new Affectation();
    $crit = array("idResource"=>$this->id);
    if ($limitToActiveProjects) {
      $crit["idle"]='0';
    }
    $affList=$aff->getSqlElementsFromCriteria($crit,false, null,'idProject asc, startDate asc');
    $today=date('Y-m-d');
    foreach ($affList as $aff) {
      // MTY - LEAVE SYSTEM
      if (isLeavesSystemActiv()) {
        // Don't take account the affectation if the project's is the LeaveProject and it's not visible for the connected user
        if (Project::isTheLeaveProject($aff->idProject) && !Project::isProjectLeaveVisible()) {continue;}
      }
      // MTY - LEAVE SYSTEM  
      if ( !$limitToActiveProjects or ((! $aff->startDate or $aff->startDate<=$today) and (! $aff->endDate or $aff->endDate>=$today))) {
        $affProfile[$aff->idProject]=$aff->idProfile;
        $resultProf[$aff->idProfile]=$aff->idProfile;
      }
    }
    $accessRightRead=securityGetAccessRight('menuProject', 'read');
    // For ALL, by default can have access to all projects
    if ($accessRightRead=="ALL") {
    	$listAllProjects=SqlList::getList('Project','name', null, true);
    	foreach($listAllProjects as $idPrj=>$namePrj) {
            // MTY - LEAVE SYSTEM
            // Don't take account the project if the project's is the LeaveProject and it's not visible for the connected user
            if (isLeavesSystemActiv()) {
                if (Project::isTheLeaveProject($idPrj) && !Project::isProjectLeaveVisible()) {continue;}
            }
            // MTY - LEAVE SYSTEM  
    		$result[$idPrj]=$namePrj;
    	}
    } 
    // Scpecific rights for projects affected to user : may change rights for ALL (admin)
    $affPrjList=$this->getAffectedProjects($limitToActiveProjects);
    $profile=$this->idProfile;
    foreach($affPrjList as $idPrj=>$namePrj) {
      if (isset($affProfile[$idPrj])) {
        $profile=$affProfile[$idPrj];
        $resultAff[$idPrj]=$profile;
        $prj=new Project($idPrj,true);
        $lstSubPrj=$prj->getRecursiveSubProjectsFlatList($limitToActiveProjects);
        foreach ($lstSubPrj as $idSubPrj=>$nameSubPrj) {
          if (!Profile::profileHasNoAccess($profile)) $result[$idSubPrj]=$nameSubPrj;
          $resultAff[$idSubPrj]=$profile;
        }
        if (!Profile::profileHasNoAccess($profile)) $result[$idPrj]=$namePrj;
      }
    }
    
    $this->_allProfiles=$resultProf;
    if ($limitToActiveProjects) {
      $this->_visibleProjects=$result;
      $this->_specificAffectedProfiles=$resultAff;
    } else {
      $this->_visibleProjectsIncludingClosed=$result;
      $this->_specificAffectedProfilesIncludingClosed=$resultAff;
    }
    if (getSessionUser()->id==$this->id) {
      setSessionUser($this); // Store user to cache Data
    }  
    return $result;
  }
  
  /** =========================================================================
   * Get the list of all products the user should have readable access to, 
   * this means the products linked (through version) to projects the resource corresponding to the user is affected to
   * and their sub projects
   * @return a list of projects id
   */
  public function getVisibleProducts($limitToActiveProjects=true) {
    if ($limitToActiveProjects and $this->_visibleProducts) {
      return $this->_visibleProducts;
    }
    if (! $limitToActiveProjects and $this->_visibleProductsIncludingClosed) {
      return $this->_visibleProductsIncludingClosed;
    }
    $result=array();
    $prjList=$this->getVisibleProjects($limitToActiveProjects);
    $v = new Version ();
    $vp = new VersionProject ();
    $clauseWhere="id in "
        ."(select idProduct from ".$v->getDatabaseTableName()." existV, ".$vp->getDatabaseTableName()." existVP "
            ."where existV.id=existVP.idVersion and existVP.idProject in ".transformListIntoInClause($prjList)
            .")";
    $prd=new Product();
    $prdList=$prd->getSqlElementsFromCriteria(null,false,$clauseWhere);
    foreach ($prdList as $prd) {
      $result[$prd->id]=$prd->name;
    }
    if ($limitToActiveProjects) {
      $this->_visibleProducts=$result;
    } else {
      $this->_visibleProductsIncludingClosed=$result;
    }
    return $result;
  }
  
  /** =========================================================================
   * Get the list of all product versions the user should have readable access to,
   * this means the product versions linked to projects the resource corresponding to the user is affected to
   * and their sub projects
   * @return a list of projects id
   */
  public function getVisibleVersions($limitToActiveProjects=true, $limitToNotDeliveredProducts=false) {
    if (!$limitToNotDeliveredProducts) {
      if ($limitToActiveProjects and $this->_visibleVersions) return $this->_visibleVersions;
      if (!$limitToActiveProjects and $this->_visibleVersionsIncludingClosed) return $this->_visibleVersionsIncludingClosed;
    } else {
    	if ($limitToActiveProjects and $this->_visibleVersionsOnlyDelivered) return $this->_visibleVersionsOnlyDelivered;
    	if (!$limitToActiveProjects and $this->_visibleVersionsIncludingClosedOnlyDelivered) return $this->_visibleVersionsIncludingClosedOnlyDelivered;
    }
    $result=array();
    $prjList=$this->getVisibleProjects($limitToActiveProjects);
    $v = new Version ();
    $vp = new VersionProject ();
    $clauseWhere="id in "
        ."(select existV.id from ".$v->getDatabaseTableName()." existV, ".$vp->getDatabaseTableName()." existVP "
            ."where existV.id=existVP.idVersion and existVP.idProject in ".transformListIntoInClause($prjList)
            .")";
    if (securityGetAccessRight('menuProject', 'read')=='ALL') $clauseWhere="1=1"; // Can see all projects, so can see all versions
    if ($limitToNotDeliveredProducts) $clauseWhere.=' and isDelivered = 0';
    $versList=$v->getSqlElementsFromCriteria(null,false,$clauseWhere);
    foreach ($versList as $vers) {
      $result[$vers->id]=$vers->name;
    }
    if (!$limitToNotDeliveredProducts) {
      if ($limitToActiveProjects) $this->_visibleVersions=$result;
      else $this->_visibleVersionsIncludingClosed=$result;
    } else {
    	if ($limitToActiveProjects) $this->_visibleVersionsOnlyDelivered=$result;
    	else $this->_visibleVersionsIncludingClosedOnlyDelivered=$result;
    }
    return $result;
  }
  
  public static function resetVisibleVersions() {
    $user=getSessionUser();
    $user->_visibleVersions=null;
    $user->_visibleVersionsIncludingClosed=null;
    $user->_visibleVersionsOnlyDelivered=null;
    $user->_visibleVersionsIncludingClosedOnlyDelivered=null;
    $user->_visibleProducts=null;
    $user->_visibleProductsIncludingClosed=null;
    setSessionUser($user);
  }
  public function getHierarchicalViewOfVisibleProjects($hideClosed=false) {
//scriptLog("getHierarchicalViewOfVisibleProjects()");
    if (!$hideClosed and is_array($this->_hierarchicalViewOfVisibleProjects)) {
      return $this->_hierarchicalViewOfVisibleProjects;
    } 
    if ($hideClosed and is_array($this->_hierarchicalViewOfVisibleProjectsNotClosed)) {
      return $this->_hierarchicalViewOfVisibleProjectsNotClosed;
    } 
    $result=array();
    $wbsArray=array();
    $currentTop='0';
    $visibleProjectsList=$this->getVisibleProjects($hideClosed);
    $critList="refType='Project' and refId in (0";
    foreach ($visibleProjectsList as $idPrj=>$namePrj) {
    	$critList.=','.$idPrj;
    }
    $critList.=')';  
    if ($hideClosed) {
    	$critList.=' and idle=0';  
    }
    $ppe=new ProjectPlanningElement();
    $projList=$ppe->getSqlElementsFromCriteria(null, false, $critList, 'wbsSortable', false);
    foreach ($projList as $projPe) {
    	$wbsTest=$projPe->wbsSortable;
    	$wbsParent='';
    	$wbsArray[$projPe->wbsSortable]=array();
    	$wbsArray[$projPe->wbsSortable]['cpt']=0;
    	while (strlen($wbsTest)>3) {
    		$wbsTest=substr($wbsTest,0,strlen($wbsTest)-6);
    		if (array_key_exists($wbsTest,$wbsArray)) {
    			$wbsParent=$wbsTest;
    			$wbsTest="";
    		}
    	}
    	if (! $wbsParent) {
    		$currentTop+=1;
    		$wbsArray[$projPe->wbsSortable]['wbs']=$currentTop;    		
    	} else {
    		$wbsArray[$wbsParent]['cpt']+=1;
    		$wbsArray[$projPe->wbsSortable]['wbs']=$wbsArray[$wbsParent]['wbs'].'.'.$wbsArray[$wbsParent]['cpt'];
    	}
    	$result['#'.$projPe->refId]=$wbsArray[$projPe->wbsSortable]['wbs'].'#'.str_replace('#','&sharp;',$projPe->refName);
    }
    if (! $hideClosed) {
      $this->_hierarchicalViewOfVisibleProjects=$result;
    } else {
    	$this->_hierarchicalViewOfVisibleProjectsNotClosed=$result;
    }
    return $result;
  }
  public function getHierarchicalViewOfVisibleProjectsWithTop() {
    if (is_array($this->_hierarchicalViewOfVisibleProjects)) {
      return $this->_hierarchicalViewOfVisibleProjects;
    } 
    $result=array();
    $visibleProjectsList=$this->getVisibleProjects();
    foreach ($visibleProjectsList as $idPrj=>$namePrj) {
      if (! array_key_exists("#".$idPrj, $result)) {
        $result["#".$idPrj]=$namePrj; 
        $prj=new Project($idPrj);
        while ($prj->idProject) {
          if (array_key_exists("#".$prj->idProject, $result)) {
            $prj->idProject=null;
          } else {
            $prj=new Project($prj->idProject);
            $result["#".$prj->id]=$prj->name;
          }
        }
      }
    }
    $this->_hierarchicalViewOfVisibleProjects=$result;
    return $result;
  }
  
  public function getProfile($objectOrIdProject=null) {
    scriptLog("getProfile(objectOrIdProject=".debugDisplayObj($objectOrIdProject).")");
    if (is_object($objectOrIdProject)) {
      if (get_class($objectOrIdProject)=='Project') {
        $idProject=$objectOrIdProject->id;
      } else if (property_exists($objectOrIdProject, 'idProject')) {
        $idProject=$objectOrIdProject->idProject;
      } else {
        return ($this->idProfile)?$this->idProfile:0;
      }
    } else {
      $idProject=$objectOrIdProject;
    }
    if (! $idProject) {
      return ($this->idProfile)?$this->idProfile:0;
    }
    $specificProfiles=$this->getSpecificAffectedProfiles();
    if (isset($specificProfiles[$idProject])) {
      return $specificProfiles[$idProject];
    } else {
      return ($this->idProfile)?$this->idProfile:0;
    }
  }
  
  // Return a list of project with specific access rights depending on profile (only read access taken into account) for a given class
  public function getAccessRights($class,$right=null,$showIdle=false) {
    if ($this->_allAccessRights and isset($this->_allAccessRights[$class])) {
      if ($right) { // Retrieve only for specific right (NO, OWN, RES, PRO, ALL)
        if (isset($this->_allAccessRights[$class][$right])) {
          return $this->_allAccessRights[$class][$right];
        } else {
          return array();
        }
      } else {      // Retrive all rights (one sub-table per right)
        return $this->_allAccessRights[$class];
      }
    }
    $result=array();
    $accessProfile=array();    
    $listAffectedProfiles=$this->getSpecificAffectedProfiles(!$showIdle);
    $obj=new $class();
    $menu=$obj->getMenuClass ();
    foreach($listAffectedProfiles as $prj=>$prf) {
      if (isset($accessProfile[$prf])) {
        $access=$accessProfile[$prf];
      } else {
        $accessList=$this->getAccessControlRights($prj);
        if (isset($accessList[$menu])) {
          $access=$accessList[$menu]['read'];
          $accessProfile[$prf]=$access;
        } else {
          $access="NO"; // Should not be reached because access list should always be set
        }
      }
      if (! isset($result[$access])) $result[$access]=array();
      $result[$access][$prj]=$prj;
    }
    if (! $this->_allAccessRights) $this->_allAccessRights=array();
    $this->_allAccessRights[$class]=$result;
    if ($this->id==getSessionUser()->id and isset($this->_isRetreivedFromSession) and $this->_isRetreivedFromSession) {
      setSessionUser($this); // Store user to cache Data
    }
    // Same code as beginning, but now _allAccessRights[$class] is set
    if ($right) { // Retrieve only for specific right (NO, OWN, RES, PRO, ALL)
      if (isset($this->_allAccessRights[$class][$right])) {
        return $this->_allAccessRights[$class][$right];
      } else {
        return array();
      }
    } else {      // Retrive all rights (one sub-table per right)
      return $this->_allAccessRights[$class];
    }
  }
  /** =========================================================================
   * Reinitalise Visible Projects list to force recalculate
   * @return void
   */  
  public function resetVisibleProjects() {
    $this->_visibleProjects=null;
    $this->_visibleProjectsIncludingClosed=null;
    $this->_affectedProjects=null;
    $this->_affectedProjectsIncludingClosed=null;
    $this->_hierarchicalViewOfVisibleProjects=null;
    $this->_hierarchicalViewOfVisibleProjectsNotClosed=null;
    $this->_specificAffectedProfiles=null;
    $this->_specificAffectedProfilesIncludingClosed=null;
    $this->_allProfiles=null;
    $this->_allAccessRights=null;
    $this->_allSpecificRightsForProfiles=null;
  }
  
  public static function resetAllVisibleProjects($idProject=null, $idUser=null) {
  	if (! getSessionUser()->id) return;
  	$user=getSessionUser();
    if ($idUser) {
      if ($idUser==$user->id) {
         self::resetAllVisibleProjects(null, null);
      } else {
    	  $audit=new Audit();
    	  $auditList=$audit->getSqlElementsFromCriteria(array("idUser"=>$idUser, 'idle'=>'0'));
    	  foreach ($auditList as $audit) {
    		  $audit->requestRefreshProject=1;
    		  $res=$audit->save();
    	  }
      }
    } else if ($idProject) {
      $aff=new Affectation();
      $affList=$aff->getSqlElementsFromCriteria(array('idProject'=>$idProject));
      foreach ($affList as $aff) {
        if ($aff->idUser==$user->id) {
          self::resetAllVisibleProjects(null, null);
        } else {
      	  $audit=new Audit();
	        $auditList=$audit->getSqlElementsFromCriteria(array("idUser"=>$aff->idUser, 'idle'=>'0'));
	        foreach ($auditList as $audit) {
	         $audit->requestRefreshProject=1;
	         $res=$audit->save();
	        }
        }
      }
    } else {
    	$user->resetVisibleProjects();
      setSessionUser($user);
      unsetSessionValue('visibleProjectsList');
    }
  }

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    if ($this->isResource and (! $this->resourceName or $this->resourceName=="")) {
      $result.='<br/>' . i18n('messageMandatory',array(i18n('colresourceName')));
    } 
    $crit=array("name"=>$this->name);
    $lst=$this->getSqlElementsFromCriteria($crit,false);
    if (count($lst)>0) {
    	if (! $this->id or count($lst)>1 or $lst[0]->id!=$this->id) {
    		$result.='<br/>' . i18n('errorDuplicateUser');
    	}
    }
    $old=$this->getOld();
    // if uncheck isResource must check resource for deletion
    if ($old->isResource and ! $this->isResource and $this->id) {
    		$obj=new Resource($this->id);
    		$resultDelete=$obj->deleteControl(true);
    		if ($resultDelete and $resultDelete!='OK') {
    			$result.=$resultDelete;
    		}
    }
    // if uncheck isContact must check contact for deletion
    if ($old->isContact and ! $this->isContact and $this->id) {
        $obj=new Contact($this->id);
        $resultDelete=$obj->deleteControl(true);
        if ($resultDelete and $resultDelete!='OK') {
          $result.=$resultDelete;
        }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
// MTY - LEAVE SYSTEM
  public function delete() {
    $old=$this->getOld();
    $result = parent::delete();
    if (strpos($result,'id="lastOperationStatus" value="OK"')) {
      if (isLeavesSystemActiv()) {
        $old->isEmployee=0;
        $resultI = initPurgeLeaveSystemElementsOfResource($old);
        if(getLastOperationStatus($resultI)!="OK"){
          return $resultI;
        }
      }
    }
    return $result;
  }
// MTY - LEAVE SYSTEM    
  
  public function deleteControl($nested=false)
  {
    $result="";
    
    if (! $nested) {
	    // if uncheck isResource must check resource for deletion
	    if ($this->isResource) {
	        $obj=new Resource($this->id);
	        $resultDelete=$obj->deleteControl(true);
	        if ($resultDelete and $resultDelete!='OK') {
	          $result.='<b><br/>'.i18n('Resource').' #'.htmlEncode($this->id).' :</b>'.$resultDelete;
	        }
	    }
	    // if uncheck isContact must check contact for deletion
	    if ($this->isContact) {
	        $obj=new Contact($this->id);
	        $resultDelete=$obj->deleteControl(true);
	        if ($resultDelete and $resultDelete!='OK') {
	          $result.='<b><br/>'.i18n('Contact').' #'.htmlEncode($this->id).' :</b>'.$resultDelete;
	        }
      }
    }
    if ($nested) {
      SqlElement::unsetRelationShip('User','Affectation');
    }
    $resultDelete=parent::deleteControl();
    if ($result and $resultDelete) {
      $resultDelete='<b><br/>'.i18n('User').' #'.htmlEncode($this->id).' :</b>'.$resultDelete.'<br/>';
    } 
    $result=$resultDelete.$result;
    return $result;
  }
  
  public function save() {
  	if (!$this->apiKey)  {
  		$this->apiKey=md5($this->id.date('Ymdhis'));
  	}
  	$old=$this->getOld();
  	if ($old->locked and ! $this->locked) {
  		$this->loginTry=0;
  	}
  	if (!$this->isResource) {
  	  $this->idOrganization=null;
  	  $this->idTeam=null;
  	}
  	//$paramDefaultPassword=Parameter::getGlobalParameter('paramDefaultPassword');
    if (! $this->id and !$this->password and Parameter::getGlobalParameter('initializePassword')=="YES") {
      //$this->salt=hash('sha256',"projeqtor".date('YmdHis'));
      //$this->password=hash('sha256',$paramDefaultPassword.$this->salt);
      //$this->crypto=null;
      $this->crypto='NULL';
      $this->password=User::getRandomPassword();
    }

    $result=parent::save();
    
    // MTY - LEAVE SYSTEM
    // If isResource become 0 => isEmployee become 0
    if (isLeavesSystemActiv()) {
      if ($this->isResource==0 and $old->isResource==1 and $this->isEmployee==1) {
        $this->isEmployee=0;
        $resultI = initPurgeLeaveSystemElementsOfResource($this);
        if(getLastOperationStatus($resultI)!="OK"){
          return $resultI;
        }
      }
    }
    // MTY - LEAVE SYSTEM
    
    if(getLastOperationStatus($result)!="OK" and getLastOperationStatus($result)!="NO_CHANGE"){
    //if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    Affectation::updateAffectations($this->id);
    if ($this->id==getSessionUser()->id) { //must refresh data
      $user=getSessionUser();
      $user->name=$this->name;
      $user->resourceName=$this->resourceName;
      $user->initials=$this->initials;
      $user->email=$this->email;
      $user->idProfile=$this->idProfile;
      $user->idLanguage=$this->idLanguage;
      $user->isContact=$this->isContact;
      $user->isResource=$this->isResource;
      setSessionUser($user);
    }
     if (! $old->id) { // Creation of user
       self::initializeNewUser($this->id);
     }
    if ($this->id==getSessionUser()->id) User::refreshUserInSession();
    return $result;
  }
  public static function initializeNewUser($id) {
    $newGui=Parameter::getGlobalParameter('newGui');
    if ($newGui==1) {
      Parameter::storeUserParameter('newGui',1,$id);
      self::storeDefaultMenus($id);
      $msg=SqlElement::getSingleSqlElementFromCriteria('MessageLegal', array('name'=>'newGui'));
      if ($msg and $msg->id) {
        $msgf=SqlElement::getSingleSqlElementFromCriteria('MessageLegalFollowup', array('idMessageLegal'=>$msg->id,'idUser'=>$id));
        if (! $msgf->id) {
          $msgf->name=$msg->name;
          $msgf->idMessageLegal=$msg->id;
          $msgf->idUser=$id;
          $msgf->accepted=1;
          $msgf->save();
        }
      }
      Parameter::storeUserParameter('paramScreen', 'left',$id);
      Parameter::storeUserParameter('paramRightDiv', 'bottom',$id);
      Parameter::storeUserParameter('paramLayoutObjectDetail', 'tab',$id);
      Parameter::storeUserParameter('menuLeftDisplayMode', 'ICONTXT',$id);
    }
  }
  public static function storeDefaultMenus($idRes) {
    $customRow[1]=array('Project', 'Activity', 'Milestone', 'Meeting', 'Planning', 'Resource', 'Reports');
    $customRow[2]=array('Ticket', 'TicketSimple', 'Kanban', 'Imputation', 'Absence');
    $sortOrder = 1;
    foreach ($customRow[1] as $menu){
      $customMenu = new MenuCustom();
      $customMenu->name = 'menu'.$menu;
      $customMenu->idUser = $idRes;
      $customMenu->idRow = 1;
      $customMenu->sortOrder = $sortOrder;
      $customMenu->save();
      $sortOrder++;
    }
    $sortOrder = 1;
    foreach ($customRow[2] as $menu){
      $customMenu = new MenuCustom();
      $customMenu->name = 'menu'.$menu;
      $customMenu->idUser = $idRes;
      $customMenu->idRow = 2;
      $customMenu->sortOrder = $sortOrder;
      $customMenu->save();
      $sortOrder++;
    }
  }
  
  public function reset() {
    $this->_accessControlRights=null;
    $this->_accessControlVisibility=null;
    $this->_visibleProjects=null;
    $this->_visibleProjectsIncludingClosed=null;
    $this->_hierarchicalViewOfVisibleProjects=null;
  }
  
  
  /** =========================================================================
   * fonction for authentificate user with user/password
   * @param $Username $Password
   * can create user directly from Ldap
   * @return -1 or Id of authentified user
   */
	public function authenticate( $paramlogin, $parampassword) {
	  global $loginSave;
debugTraceLog("User->authenticate('$paramlogin', '$parampassword')" );	
	  $paramLdap_allow_login=Parameter::getGlobalParameter('paramLdap_allow_login');
	  $paramLdap_base_dn=Parameter::getGlobalParameter('paramLdap_base_dn');
	  $paramLdap_host=Parameter::getGlobalParameter('paramLdap_host');
	  $paramLdap_port=Parameter::getGlobalParameter('paramLdap_port');
	  $paramLdap_version=Parameter::getGlobalParameter('paramLdap_version');
	  $paramLdap_search_user=Parameter::getGlobalParameter('paramLdap_search_user');
	  $paramLdap_search_pass=Parameter::getGlobalParameter('paramLdap_search_pass');
	  $paramLdap_user_filter=Parameter::getGlobalParameter('paramLdap_user_filter');
	  $paramLdap_defaultprofile=Parameter::getGlobalParameter('paramLdap_defaultprofile');
	  $rememberMe=false;
	  if (isset($_REQUEST['rememberMe']) and Parameter::getGlobalParameter('rememberMe')!='NO') {
	  	$rememberMe=true;
	  }
	 	if ( ! $this->id ) {
			if (isset($paramLdap_allow_login) and strtolower($paramLdap_allow_login)=='true') {
		  	$this->name=strtolower($paramlogin);
		  	$this->isLdap = 1;
        debugTraceLog("User->authenticate : access through LDAP");		  	
			} else {
        debugTraceLog("User->authenticate : no user id (exit)");			  
				return "login";
		  }	
	 	}	
 	
	 	$lstPluginEvt=Plugin::getEventScripts('connect','User');
	 	foreach ($lstPluginEvt as $script) {
	 	  require $script; // execute code
	 	  if (isset($plgErrorLogin)) {
	 	    break;
	 	  }
	 	}
	 	if (isset($plgErrorLogin)) {
	 	  debugTraceLog("User->authenticate : some plugin error (exit)");	 	  
	 	  return $plgErrorLogin;
	 	}
		if ($this->isLdap == 0 or !isset($paramLdap_allow_login) or strtolower($paramLdap_allow_login)!='true') {
			if ($this->crypto=='sha256') {
			  debugTraceLog("User->authenticate : sha256 encryption");
        $expected=$this->password.getSessionValue('sessionSalt');
        $expected=hash("sha256", $expected);
      } else if ($this->crypto=='md5') {
        debugTraceLog("User->authenticate : md5 encryption");
				$expected=$this->password.getSessionValue('sessionSalt');
				$expected=md5($expected);				
			} else if ($this->crypto=='old') {
			  debugTraceLog("User->authenticate : migration, no encryption");
        // Migrating to V4.0.0 : $parampassword is not MD5 unencrypted, but User->password is
        $expected=$this->password; // is MD5 encrypted
        $parampassword=md5(AesCtr::decrypt($parampassword, getSessionValue('sessionSalt'), Parameter::getGlobalParameter('aesKeyLength')));
      } else { // no crypto
        debugTraceLog("User->authenticate : no encryption");
				$expected=$this->password;
				$parampassword=AesCtr::decrypt($parampassword, getSessionValue('sessionSalt'), Parameter::getGlobalParameter('aesKeyLength'));
			}
			if ( $expected <> $parampassword) {
				$this->unsuccessfullLogin();
				debugTraceLog("User->authenticate : wrong password $expected!=$parampassword (exit)");
	      return "password";
			} else {
			  debugTraceLog("User->authenticate : Successfull login");
				$this->successfullLogin($rememberMe);
	  	  return "OK";
	  	}
	  } else {
	    debugTraceLog("User->authenticate : LDAP authenticate");
	  	disableCatchErrors();
	  	// Decode password
	  	$parampassword=AesCtr::decrypt($parampassword, getSessionValue('sessionSalt'), Parameter::getGlobalParameter('aesKeyLength'));
	  	// check password on LDAP
	    if (! function_exists('ldap_connect')) {
	    	errorLog('Ldap not installed on your PHP server. Check php-ldap extension or you should not set $paramLdap_allow_login to "true"');        
        return "ldap";
	    }
			try { 
	    	$ldapCnx=ldap_connect($paramLdap_host, $paramLdap_port);
			} catch (Exception $e) {
        traceLog("authenticate - LDAP connection error : " . $e->getMessage() );
        return "ldap";
	    }
	    if (! $ldapCnx) {
        traceLog("authenticate - LDAP connection error : not identified error");        
        return "ldap";
      }
			@ldap_set_option($ldapCnx, LDAP_OPT_PROTOCOL_VERSION, $paramLdap_version);
			@ldap_set_option($ldapCnx, LDAP_OPT_REFERRALS, 0);
	
			//$ldap_bind_dn = 'cn='.$this->ldap_search_user.','.$this->base_dn;
			$ldap_bind_dn = empty($paramLdap_search_user) ? null : $paramLdap_search_user;
			$ldap_bind_pw = empty($paramLdap_search_pass) ? null : $paramLdap_search_pass;
	
  		try {
		   $bind=ldap_bind($ldapCnx, $ldap_bind_dn, $ldap_bind_pw);
  		} catch (Exception $e) {
        traceLog("authenticate - LDAP Bind Error : " . $e->getMessage() );
        return "ldap";
      }  
			if (! $bind) {
        traceLog("authenticate - LDAP Bind Error : not identified error" );
			  return "ldap";
			}
			if (strpos($this->name,'*')!==false or strpos($this->name,'*')!==false 
			or strpos($this->name,'[')!==false or strpos($this->name,']')!==false
      or strpos($this->name,'\\')!==false) {
			  // Control : must not contain * or %
			  traceLog("authenticate - LDAP conection using for user '".$this->name."' : * or % or [ or ] or \ " );
			  return "login";
			}
			$filter_r = html_entity_decode(str_replace(array('%USERNAME%','%username%'), array($this->name,$this->name), $paramLdap_user_filter), ENT_COMPAT, 'UTF-8');
			$result = @ldap_search($ldapCnx, $paramLdap_base_dn, $filter_r);
			if (!$result) {
			  traceLog("authenticate - Filter error : ldap_search failed for filter $filter_r)" );			  
			  $this->unsuccessfullLogin();
				return "login";
			}
			$result_user = ldap_get_entries($ldapCnx, $result);
			if ($result_user['count'] == 0) {
			  traceLog("authenticate - Filter error : ldap_search returned no result for filter $filter_r)" );
			  $this->unsuccessfullLogin();
				return "login";
			}
		  if ($result_user['count'] > 1) {
		    traceLog("authenticate - Filter error : ldap_search returned more than one result for filter $filter_r)" );
		    $this->unsuccessfullLogin();
        return "login";
      }
			$first_user = $result_user[0];
			$ldap_user_dn = $first_user['dn'];
      if (strtolower($ldap_user_dn)==strtolower($paramLdap_search_user)) {
      	traceLog("authenticate - Filter error : filter retrieved admin user (LDAP user in global parameters)" );
      	$this->unsuccessfullLogin();
      	return "login";
      } 
			
			// Bind with the dn of the user that matched our filter (only one user should match filter ..)
      enableCatchErrors();
			try {
				$bind_user = @ldap_bind($ldapCnx, $ldap_user_dn, $parampassword);
			} catch (Exception $e) {
        traceLog("authenticate - LdapBind Error : " . $e->getMessage() );
        $this->unsuccessfullLogin();
        return "login";
      }
			if (! $bind_user or !$parampassword) {
			  $this->unsuccessfullLogin();
				return "login";
			}
			disableCatchErrors();
			if (! $this->id and $this->isLdap and isset($paramLdap_allow_login) and strtolower($paramLdap_allow_login)=='true') {
				if (!count($first_user) == 0) {
					Sql::beginTransaction();
					// Contact information based on the inetOrgPerson class schema
					if (isset( $first_user['mail'][0] )) {
				  		$this->email=$first_user['mail'][0];						
					}
					if (isset( $first_user['cn'][0] )) {
						$this->resourceName=$first_user['cn'][0];    
					} 
				  $this->isLdap=1;
				  $this->name=strtolower($paramlogin);
				  $this->idProfile=Parameter::getGlobalParameter('ldapDefaultProfile');
				  $createAction=Parameter::getGlobalParameter('ldapCreationAction');
				  
				  if ($createAction=='createResource' or $createAction=='createResourceAndContact') {
				    $this->isResource=1;
				  }
				  if ($createAction=='createContact' or $createAction=='createResourceAndContact') {
				    $this->isContact=1;
				  }
  				if (! $this->resourceName and ($this->isResource or $this->isContact)) {
  				  $this->resourceName=$this->name;
				  }
				  setSessionUser($this);
				  $resultSaveUser=$this->save();
				  //gautier #ldapProject
				  $idProject = Parameter::getGlobalParameter('ldapDefaultProject');
				  $aff = new Affectation();
				  $aff->idProject = $idProject;
				  $aff->idResource = $this->id;
				  $loginSave = true;
				  $result=$aff->save();
				  $loginSave = false;
				  
					$sendAlert=Parameter::getGlobalParameter('ldapMsgOnUserCreation');
					if ($sendAlert!='NO') {
						$title="ProjeQtOr - " . i18n('newUser');
						$message=i18n("newUserMessage",array($paramlogin));
						if ($sendAlert=='MAIL' or $sendAlert=='ALERT&MAIL') {
							$paramAdminMail=Parameter::getGlobalParameter('paramAdminMail');
						  sendMail($paramAdminMail, $title, $message);
						}
						if ($sendAlert=='ALERT' or $sendAlert=='ALERT&MAIL') {
							$prof=new Profile();
							$crit=array('profileCode'=>'ADM');
							$lstProf=$prof->getSqlElementsFromCriteria($crit,false);
							foreach ($lstProf as $prof) {
								$crit=array('idProfile'=>$prof->id);
								$lstUsr=$this->getSqlElementsFromCriteria($crit,false);
								foreach($lstUsr as $usr) {
									$alert=new Alert();
									$alert->idUser=$usr->id;
									$alert->alertType='INFO';
									$alert->alertInitialDateTime=date('Y-m-d H:i:s');
									$alert->message=$message;
									$alert->title=$title;
									$alert->alertDateTime=date('Y-m-d H:i:s');
									$alert->save();
								}
							}
						}
					}
					if (stripos($resultSaveUser,'id="lastOperationStatus" value="OK"')>0 ) {
            Sql::commitTransaction();
					} else {
						Sql::rollbackTransaction();
					}									
				}					
			}
	  }
	  $this->successfullLogin($rememberMe);
	  setSessionUser($this);
	  return "OK";     
  }

  private function unsuccessfullLogin() {
  	global $loginSave;
  	$maxTry=Parameter::getGlobalParameter('paramLockAfterWrongTries');
  	if ($maxTry and $this->id) {
  		$this->loginTry+=1;
  		if ($this->loginTry>=$maxTry) {
  			$this->locked=1;
  			traceLog("user '$this->name' locked - too many tries");
  		}
  		$loginSave=true;
  		$this->save();
  	}
  }
  
  private function successfullLogin($rememberMe) {
  	global $loginSave;
    $maxTry=Parameter::getGlobalParameter('paramLockAfterWrongTries');
  	if ($maxTry) {
      $this->loginTry=0;
      $loginSave=true;
      if ($rememberMe) {
      	$this->setCookieHash();
      }
      $this->save();
  	} else if ($rememberMe) {
  		$loginSave=true;
      $this->setCookieHash();
      $this->save();
  	}
  }
  
  /** ========================================================================
   * Valid login
   * @param $user the user object containing login information
   * @return void
   */
  public function finalizeSuccessfullConnection($rememberMe=false,$sso=false) {
    setSessionUser($this);
    setSessionValue('appRoot', getAppRoot());
    $crit=array();
    $crit['idUser']=$this->id;
    $crit['idProject']=null;
    $obj=new Parameter();
    $objList=$obj->getSqlElementsFromCriteria($crit,false);
    $multipleProject=false;
    //$this->_arrayFilters[$filterObjectClass . "FilterName"]=$filter->name;
    foreach($objList as $obj) {
      if ($obj->parameterCode=='lang' and $obj->parameterValue) {
        setSessionValue('currentLocale', $obj->parameterValue);
        $i18nMessages=null;
      } else if ($obj->parameterCode=='defaultProject') {
        if($obj->parameterValue=="**"){
          $obj->parameterValue=Parameter::getUserParameter('projectSelected');
          if(!is_numeric($obj->parameterValue)){
            $multipleProject = true;
            Project::setSelectedProject($obj->parameterValue);
          }
        }
        if(!$multipleProject){
          $prj=new Project ($obj->parameterValue);
          if ($prj->name!=null and $prj->name!='') {
            Project::setSelectedProject($obj->parameterValue);
          } else {
            Project::setSelectedProject('*');
          }
        }
      } else if (substr($obj->parameterCode,0,6)=='Filter') {
        if (! $this->_arrayFilters) {
          $this->_arrayFilters=array();
        }
        $idFilter=$obj->parameterValue;
        $filterObjectClass=substr($obj->parameterCode,6);
        $filterArray=array();
        $filter=new Filter($idFilter);
        $arrayDisp=array();
        $arraySql=array();
        if (is_array($filter->_FilterCriteriaArray)) {
          foreach ($filter->_FilterCriteriaArray as $filterCriteria) {
            $arrayDisp["attribute"]=$filterCriteria->dispAttribute;
            $arrayDisp["operator"]=$filterCriteria->dispOperator;
            $arrayDisp["value"]=$filterCriteria->dispValue;
            $arraySql["attribute"]=$filterCriteria->sqlAttribute;
            $arraySql["operator"]=$filterCriteria->sqlOperator;
            $arraySql["value"]=$filterCriteria->sqlValue;
            $orOperator=$filterCriteria->orOperator;
            $filterArray[]=array("disp"=>$arrayDisp,"sql"=>$arraySql,"orOperator"=>$orOperator);
          }
        }
        $this->_arrayFilters[$filterObjectClass]=$filterArray;
        $this->_arrayFilters[$filterObjectClass . "FilterName"]=$filter->name;
      } else {
        setSessionValue($obj->parameterCode, $obj->parameterValue);
      }
    }
    traceLog("NEW CONNECTED USER '" . $this->name . "'".(($rememberMe)?' (using remember me feature)':(($sso)?' (using sso authentication)':'')));
    Audit::updateAudit();
    MessageLegalFollowup::updateMessageLegalFollowup();
  }
  public static function refreshUserInSession() {
    $target=getSessionUser();
    $current=new User($target->id);
    foreach ($current as $fld=>$val) {
      if (substr($fld,0,1)=='_') continue; // Specific field
      if (is_object($val) or substr($fld,0,1)==strtoupper(substr($fld,0,1))) continue; // Object
      if (is_array($val)) continue; // array
      $target->$fld=$val; // Copy field
    } 
    setSessionUser($target);
  }
  
  public function disconnect() {
    if(Parameter::getGlobalParameter('paramReportTempDirectory') != ''){
      purgeFiles(Parameter::getGlobalParameter('paramReportTempDirectory'),"user" . $this->id . "_");
    }
    $this->stopAllWork();
    traceLog("DISCONNECTED USER '" . $this->name . "'");
    Parameter::clearGlobalParameters();
    setSessionUser(null);
  }

  public function stopAllWork() {
    $we=new WorkElement();
    $weList=$we->getSqlElementsFromCriteria(array('idUser'=>$this->id, 'ongoing'=>'1'));
    foreach ($weList as $we) {
      $we->stop();
    }
  }
  
  public static function setOldUserStyle() {
    self::$_databaseTableName = 'user';
  }  
  
  public function getPhotoThumb($size) {
    $result="";
    $image=SqlElement::getSingleSqlElementFromCriteria('Attachment', array('refType'=>'Resource', 'refId'=>$this->id));
    if ($image->id and $image->isThumbable()) {
      $result.='<img src="'. getImageThumb($image->getFullPathFileName(),$size).'" '
             . ' title="'.htmlEncode($image->fileName).'" style="cursor:pointer"'
             . ' onClick="showImage(\'Attachment\',\''.htmlEncode($image->id).'\',\''.htmlEncode($image->fileName,'protectQuotes').'\');" />';
    } else {
      $result='<div style="width:'.$size.';height:'.$size.';border:1px solide grey;">&nbsp;</span>';
    }
    return $result;
  }
  
  public function setCookieHash() {
  	$cookieHash = md5(sha1($this->name . microtime().rand(10000000,99999999))); // not secure - at least use an unknown value such as password...
	  /* to be checked later on : openssl_random_pseudo_bytes is compatible with PHP >= 5.3
       Compatibility with PHP 5.2 must be preserved
    $cookieHash = openssl_random_pseudo_bytes(32, $crypto_strong); // but this is better...
	  if (!$crypto_strong){
		  errorLog("DEBUG: openssl_random_pseudo_bytes() uses not cryptographiclly secure algorithm for login cookie");
	  }*/
  	$this->cookieHash=$cookieHash;
  	$domain=$_SERVER['SERVER_NAME'];
  	if ($domain=='localhost') {$domain="";}
  	$result=setcookie("projeqtor",$cookieHash,time()+3600*24*7,'/',$domain);
  }
  public function cleanCookieHash() {
  	$cookieHash=$this->cookieHash;
  	setcookie('projeqtor', $cookieHash, 1);
  	$this->cookieHash=null;
  	$this->save();
  }
  public static function getRememberMeCookie() {
  	$cookieHash=null;
  	if (isset($_COOKIE['projeqtor']) and Parameter::getGlobalParameter('rememberMe')!='NO') {
  		$cookieHash = $_COOKIE['projeqtor'];
  	}
  	return $cookieHash;
  }
  
  public function getWorkVisibility($idProject,$col) {
    return $this->getVisibility($idProject,$col,'work');
  }
  public function getCostVisibility($idProject,$col) {
    return $this->getVisibility($idProject,$col,'cost');
  }
  public function getVisibility($idProject,$col,$type) {
    $profile=$this->getProfile($idProject);
    if ($type=='cost') {
      $visibility=PlanningElement::getCostVisibility($profile);
    } else {
      $visibility=PlanningElement::getWorkVisibility($profile);
    }
    if ($visibility=='ALL') {
      return true;
    } else if ($visibility=='NO') {
      return false;
    } else if ($visibility=='VAL') {
      if (strpos(strtolower($col),'validated')!==false) {
        return true;
      } else {
        return false;
      }
    }
  }
  
  private $_allSpecificRightsForProfiles=array();
  
  public function getAllSpecificRightsForProfiles($specific) {
    SqlElement::$_cachedQuery['AccessScope']=array();
    SqlElement::$_cachedQuery['ListYesNo']=array();
    if (isset($this->_allSpecificRightsForProfiles[$specific])) {
      return $this->_allSpecificRightsForProfiles[$specific];
    }
    $result=array();
    foreach ($this->getAllProfiles() as $prof) {
      $crit=array('scope'=>$specific, 'idProfile'=>$prof);
      $habilitation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
      if ($specific=='planning' or $specific=='resourcePlanning' or $specific=='changeValidatedData') {
        $scope=new ListYesNo($habilitation->rightAccess);
        $code=$scope->code;
      } else {
        $scope=new AccessScopeSpecific($habilitation->rightAccess);
        $code=$scope->accessCode;
      }
      if (!isset($result[$code])) $result[$code]=array();
      $result[$code][$prof]=$prof;
    }
    $this->_allSpecificRightsForProfiles[$specific]=$result;
    if ($this->id==getSessionUser()->id) {
      setSessionUser($this); // Store user to cache Data
    }
    return $result;
  }
  
  public function allSpecificRightsForProfilesOneOnlyValue($specific,$value) {
    $list=$this->getAllSpecificRightsForProfiles($specific);
    foreach ($list as $val=>$lstProf) {
      if ($val!=$value) return false;
    }
    return true;
  }
  public function allSpecificRightsForProfilesContainsValue($specific,$value) {
    $list=$this->getAllSpecificRightsForProfiles($specific);
    foreach ($list as $val=>$lstProf) {
      if ($val==$value) return true;
    }
    return false;
  }
  
  public function getListOfPlannableProjects($scope="planning") {
    $rightsList=$this->getAllSpecificRightsForProfiles($scope); // Get planning rights for all user profiles
    $affProjects=$this->getSpecificAffectedProfiles();              // Affected projects, with profile
    $result=array();
    $defProfile=$this->idProfile;
    $access="NO";
    $accessList=$this->getAccessControlRights();                    // Get acces rights
    $canPlan=false;
    $right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$defProfile, 'scope'=>$scope));
    if ($right) {
      $list=new ListYesNo($right->rightAccess);
      if ($list->code=='YES') {
        $canPlan=true;
      }
    }
    if (isset($accessList['menuProject'])) {                        // Retrieve acces rights for projects
      $access=$accessList['menuProject']['update'];                 // Retrieve update acces right for projects
    }
    if ($access=='ALL' and $canPlan) {        // Update rights for project = "ALL" (admin type) and Can Plan for defaut profile
      // List of plannable project is list of all projects minus list of affected with no plan right
      $result=$this->getVisibleProjects();
      foreach ($affProjects as $prj=>$prf) {
        if (isset($rightsList['NO'][$prf])) {
          unset($result[$prj]);
        }
      }
    } else {
      // List of plannable project is list of projects with plannable rights
      if (! isset ($rightsList['YES'])) return $result; // Return empty array
      foreach ($affProjects as $prj=>$prf) {
        if (isset($rightsList['YES'][$prf])) {
          $result[$prj]=$prj;
        }
      }
    }
    return $result;
  } 

// MTY - LEAVE SYSTEM  
  /** =========================================================================
   * Get the list of employment Contract visible by the connected user, 
   * @return a list of employmentContract id
   */
  public function getVisibleEmploymentContract($limitToActiveContracts=true) {
    scriptLog("ResourceMain::getVisibleEmploymentContract(limitToActiveContracts=$limitToActiveContracts)");
    if ($this->isEmployee==0) {
        return array();
}
        
    if ($limitToActiveContracts) { $where = "idle=0";} else {$where=null;}
    $clauseOrderBy = "startDate ASC";
    $emplC = new EmploymentContract();
    $list = $emplC->getSqlElementsFromCriteria(null, false, $where, $clauseOrderBy);
    $result = array();
    foreach ($list as $emplC) {
        $canRead = securityGetLeaveSystemAccessRight("menuEmploymentContract", "read", $emplC, $this, true);
        if ($canRead == 'YES') {    
            $result[$emplC->id]=$emplC->name;
        }    
    }
    return $result;
  }
// MTY - LEAVE SYSTEM

  //gautier #itemTypeRestriction
  public function getItemTypeRestriction($obj,$objectClass,$user,$showIdle,$showIdleProjects) {
  	$table=$obj->getDatabaseTableName();
  	$resultOr = '';
  	$resultAnd = '';
  	$objType = $obj->getDatabaseColumnName($objectClass . 'Type');
  	$restrictType = new RestrictType();
  	$listRestrictType = $restrictType->getSqlElementsFromCriteria(array('className'=>$objType,'idProfile'=>$user->idProfile));
  	foreach ($listRestrictType as $typeRestrict){
  		$tabListIdRestrictType[] = $typeRestrict->idType;
  	}
  	if(isset($tabListIdRestrictType)){
  		$inTabListIdRestrictType = transformValueListIntoInClause($tabListIdRestrictType);
  		$resultAnd.= "  $table.id$objType in $inTabListIdRestrictType " ;
  	}else{
  		$resultAnd.= "  1=1" ;
  	}
  
  	if (property_exists($obj,'idProject')) {
  		if (Project::getSelectedProject(false,false)=='*'){
  			$resultAnd.= " and ($table.idProject in " . getVisibleProjectsList(! $showIdleProjects). " or $table.idProject is null)";
  		}
  		$monTab = getVisibleProjectsList(! $showIdleProjects);
  		$monTab = substr($monTab, 1);
  		$monTab = substr($monTab,0,-1);
  		$tabVisibleProjet = explode(', ', $monTab);
  
  		$listProjOtherProfile = $user->getSpecificAffectedProfiles($showIdle);
  		foreach ($listProjOtherProfile as $id=>$idProfile){
  			if($idProfile != $user->idProfile){
  				$tabProfileByProj[$id]=$idProfile;
  			}
  		}
  		if(isset($tabProfileByProj)){
  			foreach ($tabProfileByProj as $idProj=>$idProfile){
  				if(in_array($idProj,$tabVisibleProjet)){
  					$listRestrictType = $restrictType->getSqlElementsFromCriteria(array('className'=>$objType,'idProfile'=>$idProfile));
  					$tabListIdRestrictTypeByProject = array();
  					foreach ($listRestrictType as $typeRestrict){
  						$tabListIdRestrictTypeByProject[] = $typeRestrict->idType;
  					}
  					if($tabListIdRestrictTypeByProject){
  						$tabListIdRestrictTypeByProject = transformValueListIntoInClause($tabListIdRestrictTypeByProject);
  						$resultOr.= " or ($table.idProject=$idProj and ( $table.id$objType in $tabListIdRestrictTypeByProject)) " ;
  					}else{
  						$resultOr.= " or ($table.idProject=$idProj ) " ;
  					}
  				}
  			}
  			$listIdProj = transformListIntoInClause($tabProfileByProj);
  			$resultAnd .= " and  $table.idProject not in $listIdProj ";
  		}
  	}
  	$result = ($resultOr)?" ( ($resultAnd) $resultOr ) ":" ( $resultAnd ) ";
  	return $result;
  }
  
  public static function getRandomPassword($length=12) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $chars.=$chars;
    $newPwd = substr( str_shuffle( $chars ), 0, $length);
    return $newPwd;
  }
}
?>