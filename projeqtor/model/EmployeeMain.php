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
 * Employee is a resource that can be managed in HR module
 */ 
require_once('_securityCheck.php');
class EmployeeMain extends SqlElement {
  public $_sec_Description;
    public $id;
    public $name;
    public $userName;
    public $initials;
    public $email;
    public $idCalendarDefinition;
    public $idProfile;
    public $idOrganization;
    public $idTeam;
    public $phone;
    public $mobile;
    public $fax;
    public $startDate;
    public $_lib_colAsResource;
    public $isContact;
    public $isUser;
    public $isResource;
    public $isEmployee;
    public $isLeaveManager;
    public $idle;
    public $endDate;
    public $description;
    public $idRole;
    public $_sec_EmploymentContracts;
    public $_spe_EmploymentContract;
    public $_sec_Managers;
    public $_spe_EmployeesManaged;

  // Define the layout that will be used for lists

  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="20%">${realName}</th>
    <th field="photo" formatter="thumb32" width="5%">${photo}</th>
    <th field="initials" width="10%">${initials}</th>  
    <th field="userName" width="20%">${userName}</th> 
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  
    private static $_fieldsAttributes=array("name"=>"required, truncatedWidth100",
                                          "email"=>"truncatedWidth100",
                                          "isUser"=>"readonly",
                                          "isContact"=>"readonly",
                                          "isResource"=>"readonly",
                                          "userName"=>"truncatedWidth100",
                                          "idCalendarDefinition"=>"required,readonly",
                                          "idProfile"=>"readonly",
                                          "idOrganization"=>"readonly",
                                          "idTeam"=>"readonly",
                                          "idRole"=>"readonly",
                                          // UPDATE tLaguerie ticket #396
                                          "idle"=>"nobr, readonly",
                                          // ADD tLaguerie ticket #396
                                          "startDate"=>"nobr, readonly",
                                          "endDate"=>"readonly",
                                          // END tLaguerie ticket #396
                                          "isEmployee"=>"hidden",
                                          "isLeaveManager"=>""
  );    

  private static $_databaseTableName = 'resource';
  private static $_databaseCriteria = array('isResource'=>'1','isEmployee'=>'1');

  private static $_databaseColumnName = array('name'=>'fullName',
                                              'userName'=>'name');
  private static $_colCaptionTransposition = array('name'=>'realName', 'startDate'=>'entryDate', 'endDate'=>'exitDate');
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($this->id==null) {
        $this->isResource=1;
    } else {
        // Manager of the Employee (note Leaves Admin is Manager of all Employee), can modify
        if ($this->isManagerOfThis(getSessionUser())) {
            // Calendar
            self::$_fieldsAttributes["idCalendarDefinition"] = "required";
        }
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
 
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
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
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }

// =============================================================================
// DRAWING FUNCTIONS  
// =============================================================================
    
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
    $result="";
    
    switch($item) {
        case 'EmploymentContract' :
            $result = $this->drawEmploymentContract();
        break;
        case 'EmployeesManaged' :
            $result = $this->drawEmployeesManaged();
            break;
    }    
     return $result;
  }
  
  private function drawEmployeesManaged() {
      global $print;
      //global $cr, $print, $user, $browserLocale, $comboDetail;
      if($this->id==NULL){
          return;
      }
      $result = '';
      
      $canRead=securityGetAccessRight('menuEmployeeManager', 'read')!="NO";

      if (!$canRead) {
          return '<i>'.i18n('messageNoAccess',array(i18n('menuEmployeeManager'))).'</i>';
      }
                  
      $result .="<table style=\"width:100%\">";
      $result .='<tr><td colspan=4 style="width:100%;"><table style="width:100%;">';
      $result.='<tr>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colName').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colStartDate').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colEndDate').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colIdle').'</td>';
      $result.='</tr>';
      
      $employeesManagedList = $this->getEmployeesManaged(false,true);
      
      foreach($employeesManagedList as $employee){
          $canReadR= securityGetAccessRightYesNo('menuEmployeesManaged', 'read', $employee)=="YES";
          $result.="<tr>";
          if (!$canReadR) {
              $result .= '<td colspan="4">';
              $result .= '<i>'.i18n('noAccessToThisElement').'</i>';
              $result .= "</td></tr>";
              continue;
          }
                    
          $res=new Resource($employee->idEmployeeManager);
          $goto="";
          if (!$print and securityGetAccessRightYesNo('menuResource', 'read', '')=="YES") {
            $goto=' onClick="gotoElement('."'".get_class($res)."','".htmlEncode($res->id)."'".');" style="cursor: pointer;" ';
          }
          
          $result .= '<td class="linkData '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" align="center"'.$goto.'>'.htmlEncode($res->name).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($employee->startDate).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($employee->endDate).'</td>';
          $result .= '<td class="linkData" align="center"><div dojoType="dijit.form.CheckBox" type="checkbox" readonly '.(($employee->idle==1)?'checked':'').' ></div></td>';
          $result.="</tr>";
      }
      
      $result.='</table>';
      
      $result.='</td></tr></table>';
      
      return $result;
  }
  
  /**
   * to draw the attribute $_spe_EmploymentContract
   */
  public function drawEmploymentContract(){
      global $print;
      //global $cr, $print, $user, $browserLocale, $comboDetail;
      if($this->id==NULL){
          return;
      }
      $obj = $this;
      $result = '';
      
      $canCreate=securityGetAccessRightYesNo('menu'.get_class($obj), 'create',$obj)=="YES";
      $canUpdate=securityGetAccessRightYesNo('menu'.get_class($obj), 'update', $obj)=="YES";
      $canDelete= securityGetAccessRightYesNo('menu'.get_class($obj), 'delete', $obj)=="YES";
      
      if (!$canUpdate) {
        $canCreate=false;
        $canDelete=false;
      }
      if ($obj->idle==1) {
        $canUpdate=false;
        $canCreate=false;
        $canDelete=false;
      }
      $idleClass=$obj->idle;
      
      $result .="<table style=\"width:100%\">";
      $result .='<tr><td colspan=4 style="width:100%;"><table style="width:100%;">';
      $result.='<tr>';
      
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colName').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colStartDate').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colEndDate').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colIdle').'</td>';
      $result.='</tr>';
      
      $crit = array("idEmployee" =>$this->id);
      $contract = new EmploymentContractMain();      
      $contractsList = $contract->getSqlElementsFromCriteria($crit, false, null, null, false, true);
      
      foreach($contractsList as $ctr){
          $result.="<tr>";
          $idleClass=$ctr->idle;
                    
          $goto="";
          if (!$print and securityGetAccessRightYesNo('menuEmploymentContract', 'read', '')=="YES") {
            $goto=' onClick="gotoElement('."'EmploymentContract"."','".htmlEncode($ctr->id)."'".');" style="cursor: pointer;" ';
          }
          
          $result .= '<td class="linkData classLinkName '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" align="center" '.$goto.'>'.htmlEncode($ctr->name).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($ctr->startDate).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($ctr->endDate).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlDisplayCheckbox($ctr->idle).'</td>';
          $result.="</tr>";
      }      
      $result.='</table>';
      
      $result.='</td></tr></table>';
      
      return $result;
  }

// =============================================================================
// MISCELANIUS FUNCTIONS  
// =============================================================================
  
  /**
   * Return true if this employee has only closed contracts
   * @return boolean True if this employee has contracts that are all closed
   */
  public function hasAllClosedContracts() {
      $crit = array("idle"=> '0',
                    "idEmployee"=> $this->id);
      $contract = new EmploymentContract();
      $contractList = $contract->getSqlElementsFromCriteria($crit);
      if (count($contractList)==0) {return true;}
      return false;
  }
  
  /**
   * Return true if manager is the manager of this employee
   * @param User $manager = user for which known if he's manager of this employee
   * @return boolean
   */
    public function isManagerOfThis($manager=null) {
        if ($manager==null) {return false;}
        // Leaves Admin is Manager of all employees
        if (isLeavesAdmin($manager->id)) {return true;}
        
        return isManagerOfEmployee($manager->id, $this->id);
    }
    
    /**
     * Get the most recent activ manager of the employee
     * @return Resource : The resource object of the activ manager of this employee
     */
    public function getActivManager($includeLeaveAdmin=true) {
        $employeesManaged = new EmployeesManaged();
        $critArray = array(
            "idEmployee" => $this->id,
            "idle" => '0'
        );
        $orderBy = "startDate DESC";
        $managers = $employeesManaged->getSqlElementsFromCriteria($critArray,false,null,$orderBy);
        $currentDateTime = new DateTime();
        $currentDate = $currentDateTime->format("Y-m-d");
        foreach($managers as $manager) {
            if ($manager->startDate==null) { $startDate = "1900-01-01";} else { $startDate = $manager->startDate; }
            if ($manager->endDate==null) { $endDate = "2200-12-31";} else { $endDate = $manager->endDate; }
            if ($currentDate>=$startDate and $currentDate<= $endDate) {
                $res = new Resource($manager->idEmployeeManager);
                return $res;
            }
        }
        if ($includeLeaveAdmin) {
                return getLeavesAdmin();
        } else {
            return array();
        }        
    }

    /**
     * Get the activ employment contract of this employee
     * @return EmploymentContract : Null is not found
     */
    public function getActivEmploymentContract() {
        $crit = array("idle" => '0', "idEmployee" => $this->id);
        $emplCnt = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract", $crit);
        if (isset($emplCnt->id)) { return $emplCnt; } else {return null;}
    }

    /**
     * Get the employees managed by this.
     * @param boolean $actual : If true, return only Managed Employees at this time
     * @param Employee $manager : The manager for which retrieve managed employees. If null, the connected user
     * @param boolean $withClosed : If true, include in result idle=1
     * @return EmployeesManaged[] : An array of EmployeesManaged
     */
    public function getEmployeesManaged($actual=true,$withClosed=false) {
        $managedEmployee = new EmployeesManaged();
        $crit = array("idEmployee" => $this->id);
        if (!$withClosed) {
            $crit["idle"] = "0";
        }
        $orderBy="idEmployee ASC, startDate ASC";
        $currentDate = new DateTime();
        $currentDateString = $currentDate->format("Y-m-d");
        $managedEmployees = $managedEmployee->getSqlElementsFromCriteria($crit,false,null,$orderBy);
        foreach($managedEmployees as $key => $empl) {
            if ($actual) {
                if ($empl->startDate==null) {$startDate = "1900-01-01";} else {$startDate = $empl->startDate;}
                if ($empl->endDate==null) {$endDate = "2200-12-31";} else {$endDate = $empl->endDate;}
                if ($currentDateString <= $startDate or $currentDateString >= $endDate) {
                    unset($managedEmployees[$key]);
                }
            }    
        }
        return $managedEmployees;
    }
    
    /**
     * Get the sum of left quantity of leave for this employee by leave type
     * @param integer $idLeaveType : id's leave type. If 0 or null, all leave types
     * @param boolean $actualOnly : Actual leave Earned only if true (ie : startDate >= currentDate and endDate <= currentDate)
     * @return Array[idLeaveType] : The sum of leave Earned left quantity by idLeaveType
     */
    public function getLeftLeavesByLeaveType($idLeaveType=0, $actualOnly=false) {
        $arrayRes = array();
        $clauseWhere = "idEmployee = ".$this->id." AND idle=0";
        if ($idLeaveType>0) {
            $clauseWhere .= " AND idLeaveType=$idLeaveType";
        }
        $date = (new DateTime())->format("Y-m-d");
        $clauseWhere .= " AND (startDate<='$date' OR startDate IS NULL)";
        if ($actualOnly) {
            $clauseWhere .= " AND (endDate>='$date' OR endDate IS NULL)";
        }
        $orderBy = "idLeaveType ASC";
        $lvE = new EmployeeLeaveEarned();
        $lvEList = $lvE->getSqlElementsFromCriteria(null,false,$clauseWhere,$orderBy);
        
        // Not found for the date
        // Search if exists one or more
        if (count($lvEList)==0) {
            $clauseWhere = "idEmployee = ".$this->id." AND idle=0";
            if ($idLeaveType>0) {
                $clauseWhere .= " AND idLeaveType=$idLeaveType";
            }
            $orderBy = "idLeaveType ASC";
            $lvE = new EmployeeLeaveEarned();
            $lvEList = $lvE->getSqlElementsFromCriteria(null,false,$clauseWhere,$orderBy);
            // No leaveEarned => Null
            if (count($lvEList)==0) {
                return $arrayRes;
            }
            foreach($lvEList as $lvE) {
                if (!array_key_exists($lvE->idLeaveType, $arrayRes)) {
                    $arrayRes[$lvE->idLeaveType]=0;
                }
            }
            return $arrayRes;
        }
        
        foreach($lvEList as $lvE) {
            if (array_key_exists($lvE->idLeaveType, $arrayRes)) {
                if ($lvE->leftQuantity!=null) {
                    $arrayRes[$lvE->idLeaveType] += $lvE->leftQuantity;
                }
            } else {
                $contract = null;
                if ($lvE->startDate!=null) {
                    $actualContractualValue = getActualLeaveContractualValues($this->id,$lvE->idLeaveType,$contract);
                    if ($actualContractualValue==null) {
                        $arrayRes[$lvE->idLeaveType]=0;
                    } else {
                        $validityDuration = $actualContractualValue->validityDuration;
                        $theValidityDate = (new DateTime($lvE->startDate))->add(new DateInterval("P".$validityDuration."M"))->format("Y-m-d");
                        if ($theValidityDate>=$date) {
                            $arrayRes[$lvE->idLeaveType] = $lvE->leftQuantity;
                        }
                    }
                } else {
                    $arrayRes[$lvE->idLeaveType] = $lvE->leftQuantity;                    
                }
            }
        }
        return $arrayRes;
    }
   
   /**
    * Return the leaves for this employee between two dates that are to processed
    * @param String $startDate  Start date of leave to retrieve - Format "YYYY-MM-DD"
    * @param String $endDate    End date of leave to retrieve - Format "YYYY-MM-DD"
    * @param String $orderBy    Sort leave
    * @param integer $submitted -1 for all
    * @param integer $accepted  -1 for all
    * @param integer $rejected  -1 for all
    * @return Array[Leave]     Array of leaves
    */ 
   public function getEmployeeLeavesBetweenDateToProcess($startDate=null, $endDate=null, $idLeaveType=null, $orderBy=null,$submitted=-1,$accepted=-1,$rejected=-1) {
       if ($startDate == null or $endDate == null) {return null;}
       $clauseSubmitted = "";
       if ($submitted!=-1) {
           $clauseSubmitted = "AND submitted=".$submitted;
       }
       $clauseAccepted = "";
       if ($accepted!=-1) {
           $clauseAccepted = "AND accepted=".$accepted;
       }
       $clauseRejected = "";
       if ($rejected!=-1) {
           $clauseRejected = "AND rejected=".$rejected;
       }
              
       $clauseWhere = "idle=0 AND startDate <= '$endDate' AND endDate >= '$startDate' AND idEmployee = $this->id $clauseSubmitted $clauseAccepted $clauseRejected";
       if ($idLeaveType>0) {
           $clauseWhere .= " AND idLeaveType = $idLeaveType"; 
       }       
       $leave = new Leave();
       $leavesList = $leave->getSqlElementsFromCriteria(null, false, $clauseWhere, $orderBy);
       return $leavesList;
   }
   
   public function setAttributes() {
     foreach (array('Resource','User','Contact','EmployeeManager') as $obj) {
       $crit=array("name"=>"menu".$obj);
       $menu=SqlElement::getSingleSqlElementFromCriteria('Menu', $crit);
       if (! $menu) {
         return;
       }
       if (securityCheckDisplayMenu($menu->id)) {
         $canUpdateObj=(securityGetAccessRightYesNo('menu'.$obj, 'update', $this) == "YES");
       } else {
         $canUpdateObj=false;
       }
       $fld='is'.(($obj=='EmployeeManager')?'LeaveManager':$obj);
       if (!$canUpdateObj) {
         self::$_fieldsAttributes[$fld]="readonly";
       } else {
         self::$_fieldsAttributes[$fld]="";
       }
     }
   }
}
?>
