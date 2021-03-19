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

// LEAVE SYSTEM
// RULES :
// x. At Creation :
//      - Can't create in same time, Resource that is Employee => Pb in creation of it's EmploymentContract
//              Done in construct
// x. If update AND isEmployee changed => Init or purge elements of leave system for the resource
//      Done in save
// x. On delete resource => purge elements of leave system for this resource
//      Done in delete

require_once('_securityCheck.php');
class ResourceMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;
  public $_spe_image;
  public $name;
  public $userName;
  public $initials;
  public $email;
  public $capacity=1;
  public $idCalendarDefinition;
  public $idProfile;
  public $idOrganization;
  public $idTeam;
  public $phone;
  public $mobile;
  public $fax;
  // ADD tLaguerie Ticket #396
  public $startDate;
  // END tLaguerie Ticket #396
  public $isContact;
  public $isUser;
// MTY - LEAVE SYSTEM  
  public $isEmployee;
  public $student;
  public $subcontractor;
  public $isLeaveManager;
  // MTY - LEAVE SYSTEM
  public $idle;
  // ADD tLaguerie Ticket #396
  public $endDate;
  // END tLaguerie Ticket #396
  public $description;
  public $_sec_ResourceCost;
  public $idRole;
  public $_ResourceCost=array();
  public $_sec_Affectations;
  public $_spe_affectations;
  public $_spe_affectationGraph;
  public $_sec_affectationResourceTeamResource;
  public $_spe_affectationResourceTeamResource;
  public $_sec_resourceCapacity;
  public $_spe_resourceCapacity;
  public $_sec_resourceSurbooking;
  public $_spe_resourceSurbooking;
  public $_sec_resourceIncompatible;
  public $_spe_resourceIncompatible;
  public $_sec_resourceSupport;
  public $_spe_resourceSupport;
  public $_sec_Asset;
  public $_spe_asset;
  public $_sec_Miscellaneous;
  public $isLdap;
  public $dontReceiveTeamMails;
  public $password;
  public $crypto;
  public $isResourceTeam;

  
  public $_nbColMax=3;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="20%">${realName}</th>
    <th field="photo" formatter="thumb32" width="5%">${photo}</th>
    <th field="initials" width="10%">${initials}</th>  
    <th field="nameTeam" width="15%">${team}</th>
    <th field="capacity" formatter="decimalFormatter" width="10%" >${capacity}</th>
    <th field="userName" width="20%">${userName}</th> 
    <th field="isUser" width="5%" formatter="booleanFormatter">${isUser}</th>
    <th field="isContact" width="5%" formatter="booleanFormatter">${isContact}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required, truncatedWidth100",
                                          "email"=>"truncatedWidth100",
                                          "idProfile"=>"",
                                          "isUser"=>"",
                                          "isContact"=>"",
                                          "password"=>"hidden" ,
                                          "isResourceTeam"=>"hidden" ,
                                          "userName"=>"truncatedWidth100",
                                          "idRole"=>"required",
                                          "idCalendarDefinition"=>"required",
                                          "isLdap"=>"hidden",
                                          "crypto"=>"hidden",
                                          "idle"=>"nobr",
                                          "endDate"=>"",
                                          "startDate"=>""
  );    
  
  private static $_databaseTableName = 'resource';

  private static $_databaseColumnName = array('name'=>'fullName',
                                              'userName'=>'name');

  private static $_databaseCriteria = array('isResource'=>'1','isResourceTeam'=>'0');
  
  private static $_colCaptionTransposition = array('idRole'=>'mainRole', 'name'=>'realName', 'startDate'=>'entryDate', 'endDate'=>'exitDate'
  );
  
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
// MTY - LEAVE SYSTEM        
        if (!isLeavesSystemActiv()) {
            self::$_fieldsAttributes['isEmployee'] = "hidden";
            self::$_fieldsAttributes['isLeaveManager'] = "hidden";
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
  
    /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  
  public function setAttributes() {
    $crit=array("name"=>"menuUser");
    $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
    if (! $menu) {
      return;
    }
    if (securityCheckDisplayMenu($menu->id)) {
      $canUpdateUser=(securityGetAccessRightYesNo('menuUser', 'update', $this) == "YES");
    } else {
      $canUpdateUser=false;
    }
    if (! $canUpdateUser) {
      self::$_fieldsAttributes["idProfile"]="readonly";
      self::$_fieldsAttributes["isUser"]="readonly";
      self::$_fieldsAttributes["userName"]="readonly,truncatedWidth100";
      if ($this->isUser) {
        self::$_fieldsAttributes["isLdap"]="readonly";
      }
    } else {
      self::$_fieldsAttributes["isUser"]="";
      self::$_fieldsAttributes["idProfile"]="";
      self::$_fieldsAttributes["userName"]="truncatedWidth100";
      if ($this->isUser) {
        self::$_fieldsAttributes["idProfile"]="required";
        self::$_fieldsAttributes["userName"]="required,truncatedWidth100";
        self::$_fieldsAttributes["isLdap"]="";
      }
    }
    
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
  }
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName, $date=null) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="isUser") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    dijit.byId("userName").set("required", "true");';
      $colScript .= '    dojo.addClass(dijit.byId("userName").domNode,"required");';
      $colScript .= '    dijit.byId("idProfile").set("required", "true");';
      $colScript .= '    dojo.addClass(dijit.byId("idProfile").domNode,"required");';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("userName").set("required", null);';
      $colScript .= '    dijit.byId("userName").set("value", "");';
      $colScript .= '    dojo.removeClass(dijit.byId("userName").domNode,"required");';
      $colScript .= '    dijit.byId("idProfile").set("required", null);';
      $colScript .= '    dojo.removeClass(dijit.byId("idProfile").domNode,"required");';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
// MTY - LEAVE SYSTEM      
    } elseif($colName=="isLeaveManager") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked && dijit.byId("isEmployee").checked==false) { ';
      $colScript .= '    dijit.byId("isEmployee").set("checked", true);';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';        
    } elseif ($colName=="isEmployee") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked==false && dijit.byId("isLeaveManager").checked) { ';
      $colScript .= '    dijit.byId("isLeaveManager").set("checked", false);';
      $colScript .= '  } '; 
      $colScript .= '  if (this.checked==true) {';  
      $colScript .= '    dijit.byId("student").set("checked", false);';
      $colScript .= '    dijit.byId("subcontractor").set("checked", false);';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';                
// MTY - LEAVE SYSTEM      
     }  else if($colName=="student") {
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        $colScript .= '  if (this.checked==true) {';  
        $colScript .= '    dijit.byId("isEmployee").set("checked", false);';
        $colScript .= '    dijit.byId("subcontractor").set("checked", false);';
        $colScript .= '  } '; 
        $colScript .= '  formChanged();';
        $colScript .= '</script>';
     }  else if($colName=="subcontractor") {
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        $colScript .= '  if (this.checked==true) {';  
        $colScript .= '    dijit.byId("isEmployee").set("checked", false);';
        $colScript .= '    dijit.byId("student").set("checked", false);';
        $colScript .= '  } '; 
        $colScript .= '  formChanged();';
        $colScript .= '</script>';
     }
    return $colScript;

  } 
  public function getWork($startDate, $withProjectRepartition=false) {
    $result=array();
    $real=array();
    $startDay=str_replace('-','',$startDate);
    $where="day >= '" . $startDay . "'";
    $where.=" and idResource=" . Sql::fmtId($this->id); 
    $pw=new PlannedWork();
    $pwList=$pw->getSqlElementsFromCriteria(null,false,$where);
    $listTopProjectsArray=array();
    foreach ($pwList as $work) {
      $date=$work->workDate;
      if (array_key_exists($date,$result)) {
        $val=$result[$date];
      } else {
        $val=0;
      }
      $val+=$work->work;
      $result[$date]=$val;
      if ($withProjectRepartition) {
        $projectKey='Project#'. $work->idProject;
        if (array_key_exists($projectKey,$listTopProjectsArray)) {
          $listTopProjects=$listTopProjectsArray[$projectKey];
        } else {
          $proj = new Project($work->idProject);
          $listTopProjects=$proj->getTopProjectList(true);
          $listTopProjectsArray[$projectKey]=$listTopProjects;
        }
      // store Data on a project level view
        foreach ($listTopProjects as $idProject) {
          $projectKey='Project#'. $idProject;
          $week=getWeekNumberFromDate($date);
          if (array_key_exists($projectKey,$result)) {
            if (array_key_exists($week,$result[$projectKey])) {
              $valProj=$result[$projectKey][$week];
            } else {
              $valProj=0;
            }
          } else {
            $result[$projectKey]=array();
            $result[$projectKey]['rate']=$this->getAffectationRate($idProject, $listTopProjects); // Ticket #4549
            $valProj=0;
          }
          $valProj+=$work->work; 
          $result[$projectKey][$week]=$valProj;
        }
      }
    }
    $w=new Work();
    $wList=$w->getSqlElementsFromCriteria(null,false,$where);
    foreach ($wList as $work) {
      $date=$work->workDate;
      if (array_key_exists($date,$result)) {
        $val=$result[$date];
      } else {
        $val=0;
      }
      $val+=$work->work;
      $result[$date]=$val;
// ProjectRepartition - start
      if ($withProjectRepartition) {
        $projectKey='Project#'. $work->idProject;
        if (array_key_exists($projectKey,$listTopProjectsArray)) {
          $listTopProjects=$listTopProjectsArray[$projectKey];
        } else {
          $proj = new Project($work->idProject);
          $listTopProjects=$proj->getTopProjectList(true);
          $listTopProjectsArray[$projectKey]=$listTopProjects;
        }
        // store Data on a project level view
        foreach ($listTopProjects as $idProject) {
          $projectKey='Project#' . $idProject;
          $week=getWeekNumberFromDate($date);
          if (array_key_exists($projectKey,$result)) {
            if (array_key_exists($week,$result[$projectKey])) {
              $valProj=$result[$projectKey][$week];
            } else {
              $valProj=0;
            }
          } else {
            $result[$projectKey]=array();
            $result[$projectKey]['rate']=$this->getAffectationRate($idProject, $listTopProjects); // Ticket #4549
            $valProj=0;
          }
          $valProj+=$work->work; 
          $result[$projectKey][$week]=$valProj;
        }
      } // ProjectRepartition - end
      $key=$work->refType.'#'.$work->refId;
      if (! isset($real[$key])) {
        $real[$key]=array();
      }
      $real[$key][$date]=$work->work;
    }
    // will add structure for ResourceTeam management
    // ['team'] => 0 or 1
    // ['members'] => array (only if ['team']=1)
    //     [idResource] => array (result of getWork)
    // ['periods'] => array (only if ['team']=1)
    //     [startDate] => array
    //         ['start'] => startDate
    //         ['end'] => endDate
    //         ['rate'] => global rate (summ for all members on the period)
    //         ['idResource'] => array
    //             [idResource] => capacity allocated for the resource
    $result['real']=$real;
    $result['team']=$this->isResourceTeam;
    if ($result['team']) {
      $result['members']=array();
      $rta=new ResourceTeamAffectation();
      $rtaList=$rta->getSqlElementsFromCriteria(array('idResourceTeam'=>$this->id, 'idle'=>'0'));
      $result['periods']=ResourceTeamAffectation::buildResourcePeriods($this->id);
      foreach ($rtaList as $rta) {
        $rr=new Resource($rta->idResource);
        $result['members'][$rr->id]=$rr->getWork($startDate, $withProjectRepartition);
        //foreach($result['members'][$rr->id] as $keyM=>$valM) { // Add work on unit resource to Team to subtract availability
          //if ($keyM=='real' or substr($keyM,0,7)=='Project' or $keyM=='isMemberOf') continue;
          //if (!isset($result[$keyM])) $result[$keyM]=0;
          //$result[$keyM]+=$valM;
        //}
      }
    } else {
      $result['isMemberOf']=array();
      $rta=new ResourceTeamAffectation();
      $rtaList=$rta->getSqlElementsFromCriteria(array('idResource'=>$this->id, 'idle'=>'0'));
      foreach ($rtaList as $rta) {
        //$result['isMemberOf'][$rta->startDate]=array('team'=>$rta->idResourceTeam,'start'=>$rta->startDate,'end'=>$rta->endDate,'rate'=>$rta->rate);
        $result['isMemberOf'][$rta->idResourceTeam]=$rta->idResourceTeam;
      }
    }
    $result['variableCapacity']=($this->hasVariableCapacity() or $this->hasSurbookedCapacity());
    $result['weekTotalCapacity']=array();
    $result['normalCapacity']=$this->capacity;
    $result['calendar']=$this->idCalendarDefinition;
    // Incompatible resources
    $result['incompatible']=array();
    $resourceIncompatible = new ResourceIncompatible();
    $critArray=array('idResource'=>$this->id);
    $incompatibleResourceList=$resourceIncompatible->getSqlElementsFromCriteria($critArray, false);
    foreach ($incompatibleResourceList as $inc) {
      $result['incompatible'][$inc->idIncompatible]=$inc->idIncompatible;
    }
    // Support resources
    $result['support']=array();
    $resourceSupport = new ResourceSupport();
    $critArray=array('idResource'=>$this->id);
    $supportResourceList=$resourceSupport->getSqlElementsFromCriteria($critArray, false);
    foreach ($supportResourceList as $sup) {
      $result['support'][$sup->idSupport]=$sup->rate;
    }   
    return $result; 
  }
  
  private static $affectationRates=array();
  public function getAffectationRate($idProject, $listTopProjects=null) {
    $showIdle=false; // removed from parameters;
  	if (isset(self::$affectationRates[$this->id.'#'.$idProject])) {
  		return self::$affectationRates[$this->id.'#'.$idProject];
  	}
    $result="";
    /*$crit=array('idResource'=>$this->id, 'idProject'=>$idProject);
    $aff=SqlElement::getSingleSqlElementFromCriteria('Affectation',$crit);
    if ($aff->rate) {
      $result=$aff->rate;
    } else {
      $prj=new Project($idProject);
      if ($prj->idProject) {
        $result=$this->getAffectationRate($prj->idProject,$listTopProjects);
      } else {
        $result='100';
      }
    }*/
    $periods=Affectation::buildResourcePeriodsPerProject($this->id,$showIdle);
    if (isset($periods[$idProject])) {
    	$result=$periods[$idProject]['periods'];
    } else {
      $result=array(array('start'=>Affectation::$minAffectationDate, 'end'=>Affectation::$maxAffectationDate, 'rate'=>100,'fake'=>true));
    }
    self::$affectationRates[$this->id.'#'.$idProject]=$result;
    return $result;
  }
  // Find a rate amongst list of project affectation periods
  public static function findAffectationRate($arrayPeriods,$date) {
  	foreach ($arrayPeriods as $period) {
  		if ($period['start']<=$date and $date<=$period['end']) {
  			return $period['rate']; 
  		} else if ($date<$period['start']) {
  			return 0;
  		}
  	}
  	return -1; // not found => -1 as it means no more allocation exists;
  }

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    
    if ($this->isUser and (! $this->userName or $this->userName=="")) {
      $result.='<br/>' . i18n('messageMandatory',array(i18n('colUserName')));
    } 
    // Control that user is not duplicate
    if ($this->userName) {
      $crit=array("name"=>$this->userName);
      $usr=new User();
      $lst=$usr->getSqlElementsFromCriteria($crit,false);
      if (count($lst)>0) {
        if (! $this->id or count($lst)>1 or $lst[0]->id!=$this->id) {
          $result.='<br/>' . i18n('errorDuplicateUser');
        }
      }
    }
    $old=$this->getOld();
    // if uncheck isUser must check user for deletion
    if ($old->isUser and ! $this->isUser and $this->id) {
        $obj=new User($this->id);
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
    self::$_fieldsAttributes["idProfile"]="";
    self::$_fieldsAttributes["userName"]="";
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save() {
    if ($this->isUser and !$this->password and Parameter::getGlobalParameter('initializePassword')=="YES") {
      $this->crypto=null;
  		$this->password=User::getRandomPassword();
    }

// MTY - LEAVE SYSTEM
    // A leave manager, must be an employee
    if ($this->isLeaveManager==1 and $this->isEmployee==0) {
        $this->isEmployee=1;
    }    
    if ($this->isEmployee==0 and $this->isLeaveManager==1) {
        $this->isLeaveManager=0;
    }
    $oldResource=$this->getOld();

  	$result=parent::save();
  	// MTY - LEAVE SYSTEM
  	if (isLeavesSystemActiv()) {
  	  // isEmployee changes
  	  if ($this->isEmployee != $oldResource->isEmployee and ($oldResource->id or $this->isEmployee==1)) {
  	    // => Init or purge elements of leave system for the resource
  	    $resultI = initPurgeLeaveSystemElementsOfResource($this);
  	    if (getLastOperationStatus($resultI)!="OK") {
  	      return $resultI;
  	    }
  	  }
  	  // isLeaveManager changes and become 0
  	  if ($this->isLeaveManager == 0 and $oldResource->isLeaveManager==1) {
  	    // Delete corresponding EmployeesManaged
  	    $crit = "idEmployeeManager = $this->id";
  	    $emplManaged = new EmployeesManaged();
  	    $resultI = $emplManaged->purge($crit);
  	    if (getLastOperationStatus($resultI)!="OK" and getLastOperationStatus($resultI)!="NO_CHANGE") {
  	      return $resultI;
  	    }
  	  }
  	}
  	// MTY - LEAVE SYSTEM
  	
    //if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
    if(getLastOperationStatus($result)!="OK" and getLastOperationStatus($result)!="NO_CHANGE"){
      return $result;     
    }
    
    // #397 : if idTeam or idOrganization are modified also modify them in employmentContract
    if ($this->isEmployee == 1 and ($this->idTeam!=$oldResource->idTeam or $this->idOrganization!=$oldResource->idOrganization)) {
      $ec=new EmploymentContract();
      $ecList=$ec->getSqlElementsFromCriteria(array("idle"=>'0',"idEmployee"=>$this->id));
      foreach ($ecList as $ec) {
        $ec->idTeam=$this->idTeam;
        $ec->idOrganization=$this->idOrganization;
        $resEc=$ec->save();
      }
    }
    // MTY - LEAVE SYSTEM
    
  	Affectation::updateAffectations($this->id);
  	if ($this->id==getSessionUser()->id) { //must refresh data
  	  $user=getSessionUser();
  	  $user->name=$this->userName;
  	  $user->resourceName=$this->name;
  	  $user->initials=$this->initials;
  	  $user->email=$this->email;
  	  $user->idProfile=$this->idProfile;
  	  $user->isContact=$this->isContact;
  	  setSessionUser($user);
  	}
  	if ($this->id==getSessionUser()->id) User::refreshUserInSession();

// MTY - MULTI CALENDAR
    // If user is the changed resource AND idCalendarDefinition changed
    // ==> Update the cookies values of uWorkDayList et uOffDayList (user cookies)
    if ($this->id == getSessionUser()->id and $oldResource->idCalendarDefinition != $this->idCalendarDefinition) {
        $calDef = new CalendarDefinition($this->idCalendarDefinition);
        $calDef->updateCookiesForCalendar(true, $this->idCalendarDefinition);
    }
// MTY - MULTI CALENDAR    

// MTY - LEAVE SYSTEM
   if (isLeavesSystemActiv()) {
        // Reload the menu if 
        //          - changed resource is the user
        //          - leave System is activ
        if ($this->id == getSessionUser()->id and
            $this->isEmployee != $oldResource->isEmployee and isLeavesSystemActiv()) {
                $forceRefreshMenu="Resource";
                $user=getSessionUser();
                $user->reset();
                setSessionUser($user);
                Parameter::clearGlobalParameters();// force refresh 
                echo '<input type="hidden" id="forceRefreshMenu" value="'.$forceRefreshMenu.'_'.$this->id.'" />';
                //echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
                //echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . 'OK' .'">';
        }
    }
// MTY - LEAVE SYSTEM
    if (!$oldResource->isUser and $this->isUser) {
      UserMain::initializeNewUser($this->id);
    }
  	return $result;
  }
  
// ELIOTT - LEAVE SYSTEM
  public function delete() {
    $result = parent::delete();
    if (isLeavesSystemActiv()) {
      $theResource = clone $this;
      // On delete resource => purge elements of leave system for this resource
      $theResource->isEmployee=0;
      $resultI = initPurgeLeaveSystemElementsOfResource($theResource);
      if(getLastOperationStatus($resultI)!="OK"){
        return $resultI;
      }
    }
    return $result;
  }
// ELIOTT - LEAVE SYSTEM
  
  public function getResourceCost() {
    $result=array();
    $rc=new ResourceCost();
    $crit=array('idResource'=>$this->id);
    $rcList=$rc->getSqlElementsFromCriteria($crit, false, null, 'idRole, startDate');
    return $rcList;
  }
  public function getActualResourceCost($idRole=null) {
    if (! $this->id) return null;
    if (! $idRole or $idRole<=0) $idRole=$this->idRole;
    $where="idResource=" . Sql::fmtId($this->id) ;
    if ($idRole) {
      $where.= " and idRole=" . Sql::fmtId($idRole);
    }
    $where.= " and (startDate is null or startDate<='".date('Y-m-d')."')";
    $rc=new ResourceCost();
    $rcL = $rc->getSqlElementsFromCriteria(null, false, $where, "startDate desc".((Sql::isPgsql())?' NULLS LAST':''));
    if (count($rcL)>=1) {
      $rc=reset($rcL);
      return $rc->cost;
    }
    return null;
  }  
  public function getLastResourceCost($idRole=null) {
    if (! $this->id) return null;
    if (! $idRole) $idRole=$this->idRole;
    $where="idResource='" . Sql::fmtId($this->id) . "'";
    if ($idRole) {
      $where.= " and idRole='" . Sql::fmtId($idRole) . "'";
    }
    $where.= " and endDate is null";
    $rc=new ResourceCost();
    $rcL = $rc->getSqlElementsFromCriteria(null, false, $where, "startDate desc");
    if (count($rcL)>=1) {
      $rc=reset($rcL);
      return $rc->cost;
    }
    return null;
  }

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
  	global $comboDetail, $print, $outMode, $largeWidth;
    $result="";
    if ($item=='affectations') {
      $aff=new Affectation();
      $critArray=array('idResource'=>(($this->id)?$this->id:'0'));
      $affList=$aff->getSqlElementsFromCriteria($critArray, false);
      drawAffectationsFromObject($affList, $this, 'Project', false);   
      return $result;
    } else if ($item=='affectationGraph') {
    	//$result.='<tr style="height:100%">';
    	//$result.='<td colspan="2" style="width:100%">';
    	$result.=Affectation::drawResourceAffectation($this->id);
    	//$result.='</td></tr>';
    	echo $result;
    } else if ($item=='image' and $this->id){
    	$result=Affectable::drawSpecificImage(get_class($this),$this->id, $print, $outMode, $largeWidth);
    	echo $result;
    }
    //gautier #resourceTeam
    else if ($item=='affectationsResourceTeam') {
         $resourceTeamAff = new ResourceTeamAffectation();
         $critArray=array('idResourceTeam'=>(($this->id)?$this->id:'0'));
         $affList=$resourceTeamAff->getSqlElementsFromCriteria($critArray, false);
         drawAffectationsResourceTeamFromObject($affList, $this, 'ResourceTeam', false);
      return $result;
    }else if ($item=='affectationResourceTeamGraph') {
      $result.=ResourceTeamAffectation::drawResourceTeamAffectation($this->id);
      echo $result;
    }else if ($item=='affectationResourceTeamResource') {
      $resourceTeamAff = new ResourceTeamAffectation();
      $critArray=array('idResource'=>$this->id);
      $affList=$resourceTeamAff->getSqlElementsFromCriteria($critArray, false);
      drawAffectationsResourceTeamResourceFromObject($affList, $this, 'ResourceTeam', false);
      return $result;
    }else if ($item=='resourceCapacity'){
      $resourceCapacity = new ResourceCapacity();
      $critArray=array('idResource'=>(($this->id)?$this->id:'0'));
      $capacityList=$resourceCapacity->getSqlElementsFromCriteria($critArray, false);
      drawResourceCapacity($capacityList,$this,'ResourceCapacity',false);
      return $result;
    }else if ($item=='resourceSurbooking'){
      $resourceSurbooking = new ResourceSurbooking();
      $critArray=array('idResource'=>(($this->id)?$this->id:'0'));
      $capacityList=$resourceSurbooking->getSqlElementsFromCriteria($critArray, false);
      drawResourceSurbooking($capacityList,$this,'ResourceSurbooking',false);
      return $result;
    }else if ($item=='resourceIncompatible'){
      $resourceIncompatible = new ResourceIncompatible();
      $critArray=array('idResource'=>$this->id);
      $incompatibleResourceList=$resourceIncompatible->getSqlElementsFromCriteria($critArray, false);
      drawIncompatibleResource($incompatibleResourceList, $this, 'ResourceIncompatible', false);
      return $result;
    }else if ($item=='resourceSupport'){
      $resourceSupport = new ResourceSupport();
      $critArray=array('idResource'=>$this->id);
      $supportResourceList=$resourceSupport->getSqlElementsFromCriteria($critArray, false);
      drawResourceSupport($supportResourceList, $this, 'ResourceSupport', false);
      return $result;
    }else if ($item=='asset'){
      $asset = new Asset();
      $critArray=array('idAffectable'=>(($this->id)?$this->id:'0'));
      $order = " idAssetType asc ";
      $assetList=$asset->getSqlElementsFromCriteria($critArray, false,null);
      drawAssetFromUser($assetList, $this);
      return $result;
    }
  }
  
  public function deleteControl($nested=false) {
  	$result="";
    if ($this->isUser) {   	
	    $crit=array("name"=>"menuUser");
	    $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
	    if (! $menu) {
	      return "KO";
	    }     
	    if (! securityCheckDisplayMenu($menu->id)) {
	      $result="<br/>" . i18n("msgCannotDeleteResource");
	      return $result;
	    }     	    	
    }
    if (! $nested) {
	    // if uncheck isContact must check contact for deletion
	    if ($this->isContact) {
	        $obj=new Contact($this->id);
	        $resultDelete=$obj->deleteControl(true);
	        if ($resultDelete and $resultDelete!='OK') {
	          $result.='<b><br/>'.i18n('Contact').' #'.htmlEncode($this->id).' :</b>'.$resultDelete;
	        }
	    }
	  // if uncheck isUser must check user for deletion
	    if ($this->isUser) {
	        $obj=new User($this->id);
	        $resultDelete=$obj->deleteControl(true);
	        if ($resultDelete and $resultDelete!='OK') {
	          $result.='<b><br/>'.i18n('User').' #'.htmlEncode($this->id).' :</b>'.$resultDelete;;
	        }
	    }
    }
    if ($nested) {
    	SqlElement::unsetRelationShip('Resource','Affectation');
    }
    $resultDelete=parent::deleteControl();
    if ($result and $resultDelete) {
      $resultDelete='<b><br/>'.i18n('Resource').' #'.htmlEncode($this->id).' :</b>'.$resultDelete.'<br/>';
    } 
    $result=$resultDelete.$result;
    return $result;
  }
  //florent ticket 4632
  public function drawMemberList($team, $showClosedItems) {
    global $print;
    if ($showClosedItems == FALSE) $crit=array('idTeam'=>$team, 'idle'=>"0");
    if ($showClosedItems == TRUE) $crit=array('idTeam'=>$team);
    $clauseOrderBy="id DESC";
    $resAll= new ResourceAll();
    $resList=$resAll->getSqlElementsFromCriteria($crit, false,false,$clauseOrderBy);
    $listVisibleLinkedObj = getUserVisibleObjectsList(get_class($this));
    
    $result = '<table width="99.9%">';
    $result .= '<tr>';
    if (!$print) {
      $result .= '<td class="assignHeader smallButtonsGroup" style="width:5%">';
      if (!$print ) {
        $result .='<a onClick="addLinkObjectToObject(\'Team\',\'' . htmlEncode($team) . '\',\'' . get_class($this) .'\');" title="' . i18n('addLinkObject') . '" >'.formatSmallButton('Add').'</a>';
      }
      $result .= '</td>';
    }
    $result .= '<td class="assignHeader" style="width:5%">' . i18n('colId') . '</td>';
    $result .= '<td class="assignHeader" style="width:' . (($print)?'45':'40') . '%">' . i18n('colName') . '</td>';
    $result .= '<td class="assignHeader" style="width:' . (($print)?'5':'10') . '%">' . i18n('colIdle') . '</td>';
    $result .= '</tr>';
    $result .= '</tr>';
    foreach ($resList as $res){
      $subClass=get_class($res);
      if ($subClass=='ResourceAll') $subClass=($res->isResourceTeam)?'ResourceTeam':'Resource';
      $result .= '<tr>';
      if (!$print) {
        $result .= '<td class="noteData smallButtonsGroup">';
        if (!$print) {
          if($res->idle==0) {
                      $result .= ' <a onClick="removeLinkObjectFromObject(\'Team\',\'' . get_class($this) .'\',\'' . htmlEncode($res->id) . 
                                                                    '\',\'' . htmlEncode(str_replace("'"," ",$res->name)) .
                                                                  '\');" title="' . i18n('removeLinkObject') . '" > '.formatSmallButton('Remove').'</a>';
          }
        }
        $result .= '</td>';
      }
      if (array_key_exists($res->id, $listVisibleLinkedObj)) {
        if (!$print and  securityCheckDisplayMenu(null, $subClass) and securityGetAccessRightYesNo('menu'.$subClass, 'read', '') == "YES"){
          $goto=' onClick="gotoElement(\''.$subClass.'\',\'' . htmlEncode($res->id) . '\');" style="cursor: pointer;" ';
        }else {
          $goto="";
        }
        $result .= '<td class="assignData" style="width:5%">#' . htmlEncode($res->id) . '</td>';
        $result .= '<td  class="assignData hyperlink" style="width:' . (($print)?'45':'40') . '%">';
        $result .= '      <div style="float:left !important"> ' . $res->getPhotoThumb(22).'</div>';
        $result .='      <div style="padding-top:5px;" '.$goto.' class="'.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'">&nbsp;&nbsp;' . htmlEncode($res->name) . '</div>';
        $result .='</td>';
      } else {
          $result .= '<td class="assignData" style="width:5%"></td>';
          $result .= '<td class="assignData" style="width:' . (($print)?'45':'40') . '%">' . i18n('isNotVisible') . '</td>';        
      }
      $result .= '<td class="noteData" style="text-align:center">' . htmlDisplayCheckbox($res->idle) . '</td>';
      $result .= '</tr>';
    }
    $result .= '</table>';
    return $result;
  }
  //
  public function getPhotoThumb($size) {
  	$result="";
  	$radius=round($size/2,0);
  	$image=SqlElement::getSingleSqlElementFromCriteria('Attachment', array('refType'=>'Resource', 'refId'=>$this->id));
    if ($image->id and $image->isThumbable()) {
  	  $result.='<img src="'. getImageThumb($image->getFullPathFileName(),$size).'" '
             . ' style="cursor:pointer;border-radius:'.$radius.'px;height:'.$size.'px;width:'.$size.'px"'
             . ' onClick="showImage(\'Attachment\',\''.htmlEncode($image->id).'\',\''.htmlEncode($image->fileName,'protectQuotes').'\');" />';
    } else {
      $result.= formatLetterThumb($this->id, $size,$this->name,"right",null);
    }
    return $result;
  }
  
// MTY - LEAVE SYSTEM
    public function getEmployees($withClosed=false, $limitToUser=false) {
        $whereClause = "isEmployee=1";
        if (!$withClosed) {
            $whereClause .= " AND idle=0";
        }
        if ($limitToUser) {
            $whereClause .= " AND isUser=1";            
        }
        return $this->getSqlElementsFromCriteria(null, false, $whereClause);
    }
    
    public function getEmployeesList($withClosed=false, $limitToUser=false) {
        $employees = $this->getEmployees($withClosed, $limitToUser);
        $emplList = array();
        foreach($employees as $empl) {
            $emplList[$empl->id] = $empl->name;
        }
        return $emplList;
    }
    public function getLeaveManagers($withClosed=false, $limitToUser=false) {
        $whereClause = "isLeaveManager=1";
        if (!$withClosed) {
            $whereClause .= " AND idle=0";
        }
        if ($limitToUser) {
            $whereClause .= " AND isUser=1";            
        }
        return $this->getSqlElementsFromCriteria(null, false, $whereClause);
    }
    
    public function getLeaveManagersList($withClosed=false, $limitToUser=false) {
        $managers = $this->getLeaveManagers($withClosed, $limitToUser);
        $managersList = array();
        foreach($managers as $man) {
            $managersList[$man->id] = $name->name;
        }
        return $managersList;
    }
// MTY - LEAVE SYSTEM

    public function buildCapacityPeriod(){
    	$resCap = new ResourceCapacity();
    	$listResCap = $resCap->getSqlElementsFromCriteria(array('idResource'=>$this->id),null,null,'startDate desc');
    	foreach ($listResCap as $cap){
    		if($cap->idle == 0 ){
    			$capacityPeriod[$this->id][$cap->startDate]['capacity']=$cap->capacity;
    			$capacityPeriod[$this->id][$cap->startDate]['startDate']=$cap->startDate;
    			$capacityPeriod[$this->id][$cap->startDate]['endDate']=$cap->endDate;
    		}
    	}
    	if(!isset($capacityPeriod)){
    	  $capacityPeriod = array();
    	}
    	return $capacityPeriod;
    }
    
    public function getCapacityPeriod($date) {
      //gautier #3880
      //ressource closed
      if($this->endDate){
        if($date > $this->endDate)return 0;
      }
      if($this->startDate){
        if($date < $this->startDate)return 0;
      }
    	if(!sessionValueExists('capacityPeriod')){
    		setSessionValue('capacityPeriod', array());
    	}
    	if(!sessionTableValueExist('capacityPeriod', $this->id)){
    		setSessionTableValue('capacityPeriod',$this->id, $this->buildCapacityPeriod());
    	}
    	$capacityPeriod = getSessionTableValue('capacityPeriod', $this->id);
    	foreach ($capacityPeriod as $val) {
    		foreach ($val as $value){
    			if ($date>=$value['startDate'] and $date<=$value['endDate']){
    				return $value['capacity'];
    			}
    		}
    	}
    	return $this->capacity;
    }
    
    public function hasVariableCapacity() {
      if(!sessionValueExists('capacityPeriod')){
        setSessionValue('capacityPeriod', array());
      }
      if(!sessionTableValueExist('capacityPeriod', $this->id)){
        setSessionTableValue('capacityPeriod',$this->id, $this->buildCapacityPeriod());
      }
      $capacityPeriod = getSessionTableValue('capacityPeriod', $this->id);
      if (count($capacityPeriod)>0) return true;
      else return false;
    }
    
    public function getWeekCapacity($week, $capacityRate=1) {
      $weekDay=date('Y-m-d',firstDayofWeek(substr($week,-2),substr($week,0,4)));
      $capaWeek=0;
      for ($i=0;$i<7;$i++) {
        if (isOpenDay($weekDay,$this->idCalendarDefinition)) {
          $capaWeek+=$this->getSurbookingCapacity($weekDay);
        }
        $weekDay=addDaysToDate($weekDay,1);
      }
      return $capaWeek;
    }
    
    //Surbooking
    public function getSurbookingCapacity($date,$onlySurbooking=false) {
      if(!sessionValueExists('surbookingPeriod')){
        setSessionValue('surbookingPeriod', array());
      }
      if(!sessionTableValueExist('surbookingPeriod', $this->id)){
        setSessionTableValue('surbookingPeriod',$this->id, $this->buildSurbookedPeriod());
      }
      $surbookingPeriod = getSessionTableValue('surbookingPeriod', $this->id);
      foreach ($surbookingPeriod as $val) {
        foreach ($val as $value){
          if ($date>=$value['startDate'] and $date<=$value['endDate']){
            if ($onlySurbooking) return $value['capacity'];
            else return $value['capacity']+$this->getCapacityPeriod($date);
          }
        }
      }
      if ($onlySurbooking) return 0;
      return $this->getCapacityPeriod($date);
    }
    public function hasSurbookedCapacity() {
    if(!sessionValueExists('surbookingPeriod')){
        setSessionValue('surbookingPeriod', array());
      }
      if(!sessionTableValueExist('surbookingPeriod', $this->id)){
        setSessionTableValue('surbookingPeriod',$this->id, $this->buildSurbookedPeriod());
      }
      $surbookingPeriod = getSessionTableValue('surbookingPeriod', $this->id);
      if (count($surbookingPeriod)>0) return true;
      else return false;
    }
    public function buildSurbookedPeriod(){
      $resSur = new ResourceSurbooking();
      $listResSur = $resSur->getSqlElementsFromCriteria(array('idResource'=>$this->id),null,null,'startDate desc');
      foreach ($listResSur as $sur){
        if($sur->idle == 0){
          $surbookedPeriod[$this->id][$sur->startDate]['capacity']=$sur->capacity;
          $surbookedPeriod[$this->id][$sur->startDate]['startDate']=$sur->startDate;
          $surbookedPeriod[$this->id][$sur->startDate]['endDate']=$sur->endDate;
        }
      }
      if(!isset($surbookedPeriod)){
        $surbookedPeriod = array();
      }
      return $surbookedPeriod;
    }
    
    
}
?>