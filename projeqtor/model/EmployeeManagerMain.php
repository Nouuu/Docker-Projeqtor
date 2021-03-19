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
 * Manager are resource that are employee (isEmployee=1) and manager (isManager=1).
 */ 

// LEAVE SYSTEM

require_once('_securityCheck.php');
class EmployeeManagerMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
    public $id;
    public $_spe_image;
    public $name;
    public $initials;
    public $email;
    public $idOrganization;
    public $idCalendarDefinition;
    public $idTeam;
    public $phone;
    public $mobile;
    public $description;
    public $isResource;
    public $isEmployee;
    public $isLeaveManager;
    public $idProfile;
    public $idle;
  public $_sec_BulkEmployeesManaged;
    public $_spe_AllOfOrganizationAndSubOrganization;
    public $_spe_AllOfOrganization;
    public $_spe_AllOfTeam;
    public $_spe_itSelf;
    public $_spe_startDate;
    public $_spe_endDate;
    public $_spe_buttonBulk;
  public $_sec_EmployeesManaged;
    public $_spe_EmployeesManaged;

  public $_nbColMax=3;
  
  private $___dFieldsAttributes=array();
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="20%">${realName}</th>
    <th field="photo" formatter="thumb32" width="5%">${photo}</th>
    <th field="initials" width="10%">${initials}</th>  
    ';

  private static $_fieldsAttributes=array("name"=>"required, truncatedWidth100",
                                          "idProfile" => "hidden",
                                          "isEmployee" => "hidden",
                                          "isResource" => "",
                                          "isLeaveManager" => "hidden",
                                          "idTeam" => "readonly",
                                          "idle" => "readonly"
  );    
  
  private static $_databaseTableName = 'resource';

  private static $_databaseColumnName = array('name'=>'fullName',
                                              'userName'=>'name');

  private static $_databaseCriteria = array('isEmployee'=>'1', 'isLeaveManager'=>'1');
  
  private static $_colCaptionTransposition = array('name'=>'realName');
  
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
    function __construct($id = NULL, $withoutDependentObjects=false) {
        parent::__construct($id,$withoutDependentObjects);
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

  public function setAttributes() {
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
    } else {
      self::$_fieldsAttributes["isResource"]="";
    }
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
    return $colScript;

  } 

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $result = parent::control();
   if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save() {      
    $result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    return $result;
  }
  
  public function delete() {
    $result = parent::delete();
    if (strpos($result,'id="lastOperationStatus" value="OK"')) {
        // On delete resource => purge elements of leave system for this resource
        $theResource->isEmployee=0;
        $result = initPurgeLeaveSystemElementsOfResource($theResource);
    }
    return $result;
  }
  
  public function drawEmployeesManaged() {
      global $print;
      if($this->id==NULL){
          return;
      }
      $obj = $this;
      $result = '';
      
      $canRead=securityGetAccessRight('menuEmployeeManager', 'read')!="NO";

      if (!$canRead) {
          return '<i>'.i18n('messageNoAccess',array(i18n('menuEmployeeManager'))).'</i>';
      }

      $canUpdate=securityGetAccessRightYesNo('menu'.get_class($obj), 'update', $obj)=="YES";
      
      $canCreateR=true;
      
      if (!$canUpdate or $this->idle==1) {
        $canCreateR=false;
        $canDeleteR=false;
        $canUpdateR=false;
      } else {
        $canDeleteR=true;
        $canUpdateR=true;          
      }
      
      $idleClass=$this->idle;
      
      $result .='<div id="EmployeesManaged" name="EmployeesManaged">';
      $result .="<table style=\"width:100%\">";
      $result .='<tr><td colspan=4 style="width:100%;"><table style="width:100%;">';
      $result.='<tr>';
      $result.='<td class="assignHeader" style="width:5%">';
      
      if ($this->id && $idleClass==0) {
        $result .= '<a onClick="addEmployeesManaged('.$this->id.')"> '.formatSmallButton('Add').'</a>';
      }
      $result.='</td>';
      
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colName').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colStartDate').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colEndDate').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colIdle').'</td>';
      $result.='</tr>';
      
      $employeesManagedList = $this->getEmployeesManaged(false,true);
      
      foreach($employeesManagedList as $employee){
          if ($canUpdateR) {
            $canUpdateR=securityGetAccessRightYesNo('menuEmployeesManaged', 'update', $employee)=="YES";
          }
          $canDeleteR = $canUpdateR;
          $canReadR= securityGetAccessRightYesNo('menuEmployeesManaged', 'read', $employee)=="YES";
          $result.="<tr>";
          if (!$canReadR) {
              $result .= '<td colspan="5">';
              $result .= '<i>'.i18n('noAccessToThisElement').'</i>';
              $result .= "</td></tr>";
              continue;
          }
          $idleClass=$employee->idle;
          
          if(!$print){
            $result.='<td class="linkData" style="text-align:center;white-space: nowrap;">';
          }
          
          if ($canUpdateR and !$print) {
            $result .= '<a onClick="editEmployeesManaged'
                    . '(\''.htmlEncode($employee->id).'\',\''.htmlEncode($employee->idEmployeeManager).'\',\''. htmlEncode($employee->idEmployee).'\','
                    . '\''.htmlEncode($employee->startDate).'\',\''.htmlEncode($employee->endDate).'\',\''.htmlEncode($employee->idle).'\');"'
                    . ' '.'title="'.i18n('editEmployeesManaged').'" > '.formatSmallButton('Edit').'</a>';
          }
          if ($canDeleteR and !$print) {
            $result .= '  <a onClick="removeEmployeesManaged'
                    . '(\''.htmlEncode($employee->id).'\');"'
                    . ' '.'title="'.i18n('removeEmployeeManaged').'" > '.formatSmallButton('Remove').'</a>';
          }
          $result .= "</td>";
          
          $res=new Resource($employee->idEmployee);
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
      $result.='</div>';
      return $result;
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
    $canUpdate=securityGetAccessRightYesNo('menu'.get_class($this), 'update', $this)=="YES";
    switch ($item) {
        case 'image' :
            if ($this->id) {
                $result=Affectable::drawSpecificImage(get_class($this),$this->id, $print, $outMode, $largeWidth);                
            }
            break;
        case 'EmployeesManaged' :
            return $this->drawEmployeesManaged();
            break;
        case 'AllOfOrganizationAndSubOrganization' :
            if ($this->idle==1 or $this->id<1 or !$canUpdate) { return "";}
            $result  = '<tr class="detail">';
            $result .= '    <td class="label" >';
            $result .= '        <label for="allOfOrganizationAndSubOrganization" class="label longLabel" >'.i18n("allOfOrganizationAndSubOrganization").'&nbsp;:&nbsp;</label>';
            $result .= '    </td>';
            $result .= '    <td >';
            $result .= '        <div class="greyCheck generalColClass" ';
            $result .= '             dojoType="dijit.form.CheckBox" type="checkbox" ';
            $result .= '             style="display:inline-block;" ';
            $result .= '             id="allOfOrganizationAndSubOrganization" name="allOfOrganizationAndSubOrganization"';
            $result .= '        >';
            $result .= '            <script type="dojo/connect" event="onChange" >';
            $result .= '                if (dijit.byId("allOfOrganizationAndSubOrganization").checked) { ';
            $result .= '                    dijit.byId("allOfOrganization").set("checked", false);';
            $result .= '                    dijit.byId("allOfTeam").set("checked", false);';
            $result .= '                } ';             
            $result .= '            </script>';
            $result .= '        </div>';
            $result .= '    </td>';
            $result .='</tr>';            
            break;
        case 'AllOfOrganization':
            if ($this->idle==1 or $this->id<1 or !$canUpdate) { return "";}
            $result  = '<tr class="detail">';
            $result .= '    <td class="label" style="width:10%">';
            $result .= '        <label for="allOfOrganization" class="label longLabel" >'.i18n("allOfOrganization").'&nbsp;:&nbsp;</label>';
            $result .= '    </td>';
            $result .= '    <td style="width:90%">';
            $result .= '        <div class="greyCheck generalColClass" ';
            $result .= '             dojoType="dijit.form.CheckBox" type="checkbox" ';
            $result .= '             style="display:inline-block;" ';
            $result .= '             id="allOfOrganization" name="allOfOrganization"';
            $result .= '        >';
            $result .= '            <script type="dojo/connect" event="onChange" >';
            $result .= '                if (dijit.byId("allOfOrganization").checked) { ';
            $result .= '                    dijit.byId("allOfOrganizationAndSubOrganization").set("checked", false);';
            $result .= '                    dijit.byId("allOfTeam").set("checked", false);';
            $result .= '                } ';             
            $result .= '            </script>';
            $result .= '        </div>';
            $result .= '    </td>';
            $result .='</tr>';            
            break;
        case 'AllOfTeam':
            if ($this->idle==1 or $this->id<1 or !$canUpdate) { return "";}
            $result  = '<tr class="detail">';
            $result .= '    <td class="label" >';
            $result .= '        <label for="allOfTeam" class="label longLabel" >'.i18n("allOfTeam").'&nbsp;:&nbsp;</label>';
            $result .= '    </td>';
            $result .= '    <td >';
            $result .= '        <div class="greyCheck generalColClass" ';
            $result .= '             dojoType="dijit.form.CheckBox" type="checkbox" ';
            $result .= '             style="display:inline-block;" ';
            $result .= '             id="allOfTeam" name="allOfTeam"';
            $result .= '        >';
            $result .= '            <script type="dojo/connect" event="onChange" >';
            $result .= '                if (dijit.byId("allOfTeam").checked) { ';
            $result .= '                    dijit.byId("allOfOrganizationAndSubOrganization").set("checked", false);';
            $result .= '                    dijit.byId("allOfOrganization").set("checked", false);';
            $result .= '                } ';             
            $result .= '            </script>';
            $result .= '        </div>';
            $result .= '    </td>';
            $result .='</tr>';            
            break;
        case 'itSelf':
            $result  = '<tr class="detail">';
            $result .= '    <td class="label" >';
            $result .= '        <label for="itSelf" class="label longLabel">'.i18n("itSelfIncluded").'&nbsp;:&nbsp;</label>';
            $result .= '    </td>';
            $result .= '    <td >';
            $result .= '        <div class="greyCheck generalColClass" ';
            $result .= '             dojoType="dijit.form.CheckBox" type="checkbox" ';
            $result .= '             style="display:inline-block;" ';
            $result .= '             id="itSelf" name="itSelf"';
            $result .= '        >';
            $result .= '        </div>';
            $result .= '    </td>';
            $result .='</tr>';            
            break;
        case 'startDate':
            if ($this->idle==1 or $this->id<1 or !$canUpdate) { return "";}
            $result  = '<tr class="detail">';
            $result .= '    <td class="label" >';
            $result .= '        <label for="startDateBulk" class="label longLabel">'.i18n("colStartDate").'&nbsp;:&nbsp;</label>';
            $result .= '    </td>';
            $result .= '    <td >';
            $result .= '        <input type="text" id="startDateBulk" data-dojo-type="dijit/form/DateTextBox" style="width:100px;"';
            $result .= '        />';
            $result .= '    </td>';
            $result .='</tr>';            
            break;
        case 'endDate' :
            if ($this->idle==1 or $this->id<1 or !$canUpdate) { return "";}
            $result  = '<tr class="detail">';
            $result .= '    <td class="label" >';
            $result .= '        <label for="endDateBulk" class="label longLabel">'.i18n("colEndDate").'&nbsp;:&nbsp;</label>';
            $result .= '    </td>';
            $result .= '    <td >';
            $result .= '        <input type="text" id="endDateBulk" data-dojo-type="dijit/form/DateTextBox" style="width:100px;"';
            $result .= '        />';
            $result .= '    </td>';
            $result .='</tr>';            
            break;
        case 'buttonBulk':
            if ($this->idle==1 or $this->id<1 or !$canUpdate) { return "";}
            $result  = '<div id="div_buttonBulk" style="display:inline-block;">';
            $result .= '    <button id="bt_buttonBulk" dojoType="dijit.form.Button" showlabel="true"';
            $result .= '            title="' . i18n ( 'titleAddEmployeesToManager' ) . '" style="vertical-align: middle;">';
            $result .= '        <span>' . i18n ( 'addEmployeesToManager' ) . '</span>';
            $result .= '        <script type="dojo/connect" event="onClick" args="evt">';
            $result .= '            protectDblClick(this);';
            $result .= '            if(!dijit.byId("itSelf").checked && !dijit.byId("allOfTeam").checked && !dijit.byId("allOfOrganization").checked && !dijit.byId("allOfOrganizationAndSubOrganization").checked) {';
            $result .= '                showAlert(i18n("checkSomething"));';
            $result .= '                return;';
            $result .= '            }';
            $result .= '            var itSelf="NO";';
            $result .= '            if (dijit.byId("itSelf").checked) {itSelf="YES";}';
            $result .= '            var startDate = dijit.byId("startDateBulk").value;';
            $result .= '            var endDate = dijit.byId("endDateBulk").value;';
            $result .= '            var mode="OS";';
            $result .= '            if (dijit.byId("allOfTeam").checked) { mode="T";}';
            $result .= '            if (dijit.byId("allOfOrganization").checked) { mode="O";}';
            $result .= '            loadContent("../tool/addEmployeesManagedBulk.php?idEmployeeManager='.htmlEncode($this->id).'&idOrganization='.htmlEncode($this->idOrganization).'&idTeam='.htmlEncode($this->idTeam).'&itSelf="+itSelf+"&startDate="+startDate+"&endDate="+endDate+"&mode="+mode,"EmployeeManager_EmployeesManaged");'; 	
            $result .= '        </script>';
            $result .= '    </button>';
            $result .= '</div>';
            break;
    }
    return $result;
  }
      
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
  
    /**
     * Return the most recent manager for this manager
     * @param boolean $actual : True if actual manager (currentDate between startDate and endDate and idle=0)
     * @param boolean $includeLeaveAdm : If true and no actual manager found, return the leaves administrator
     * @return Resource : The object Resource corresponding to the manager.
     */
    public function getManager($actual=true, $includeLeaveAdm=false) {
        $employeesManaged = new EmployeeManager();
        $critArray = array(
            "idEmployee" => $this->id,
            "idle" => '0'
        );
        $orderBy = "startDate DESC";
        $managers = $employeesManaged->getSqlElementsFromCriteria($critArray,false,null,$orderBy);
        if ($actual) {
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
            if ($includeLeaveAdm) {
                  return getLeavesAdmin();                
            } else {
                return array();
            }
        } else {
          if (count($managers)>0) {
              $res = new Resource($managers[0]->idEmployeeManager);
              return $res;              
          } 
          else { 
              if ($includeLeaveAdm) {
                  return getLeavesAdmin();
              }
              return array();
          }
        }
    }
  
    /**
     * 
     * @param boolean $actual
     * @param type $includeLeaveAdmin
     * @return type
     */
    public function hasManager($actual=true, $includeLeaveAdmin=null) {
      $ret = $this->getManager($actual,$includeLeaveAdmin);
      return(empty($ret)?false:true);
      // return (empty($this->getManager($actual,$includeLeaveAdmin))?false:true);
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
        $crit = array("idEmployeeManager" => $this->id);
        if (!$withClosed) {
            $crit["idle"] = "0";
        }
        $orderBy="idEmployee ASC, startDate ASC";
        $currentDate = new DateTime();
        $currentDateString = $currentDate->format("Y-m-d");
        $managedEmployees = $managedEmployee->getSqlElementsFromCriteria($crit,false,null,$orderBy);
        foreach($managedEmployees as $key => $empl) {
            if ($actual and ($empl->startDate!=null or $empl->endDate!=null)) {
                if ($empl->startDate==null) {$startDate = "1900-01-01";} else {$startDate = $empl->startDate;}
                if ($empl->endDate==null) {$endDate = "2200-12-31";} else {$endDate = $empl->endDate;}
                if ($currentDateString < $startDate or $currentDateString > $endDate) {
                    unset($managedEmployees[$key]);
                }
            }    
        }
        return $managedEmployees;
    }
    
    /**
     * Get the list (Array[key -> name] of managed employees by the manager passed in parameter
     * @param boolean $actual : If true, return only Managed Employees at this time
     * @param EmployeeManager $manager : The manager for which retrieve managed employees. If null, the connected user
     * @param boolean $withClosed : If true, include in result idle=1
     * @param bolean $limitToUser : If true, limit list to the managed employees that are users too
     * @return Array[$key => name] : An array of managed employees
     */
    public function getManagedEmployees($actual=true, $manager=null, $withClosed=false, $limitToUser=false) {
        if ($manager==null) {
            $manager = getSessionUser();
            if (isLeavesAdmin()) {
                return getUserVisibleResourcesList(true, 'List', '', false, true, false,false,$limitToUser);                
            }
        }
        $managedEmployee = new EmployeesManaged();
        $crit = array("idEmployeeManager" => $manager->id);
        if (!$withClosed) {
            $crit["idle"] = "0";
        }
        $orderBy = "idEmployee ASC, startDate ASC";
        $managedEmployees = $managedEmployee->getSqlElementsFromCriteria($crit,false,null,$orderBy);
        $resourcesList=array();
        $whereClause = ($limitToUser?"isUser=1 ":"");
        $currentDateTime = new DateTime();
        $currentDate = $currentDateTime->format("Y-m-d");
        if (count($managedEmployees)>0) {
            $find=false;
            $whereClause .= ($whereClause==""?" resource.id IN (":" AND resource.id IN(");
            foreach($managedEmployees as $employee) {
                if ($actual) {                    
                    if ($employee->startDate==null) {$startDate="1900-01-01";} else {$startDate=$employee->startDate;}
                    if ($employee->endDate==null) {$endDate="2200-12-31";} else {$endDate=$employee->endDate;}
                    if ($currentDate>=$startDate and $currentDate<=$endDate) {
                        $whereClause .= "$employee->idEmployee".",";
                        $find=true;                        
                    }
                } else {
                    $find=true;
                    $whereClause .= "$employee->idEmployee".",";
                }
            }
            if (!$find) { return $resourcesList; }
            $whereClause = substr($whereClause, 0,-1).")";
            $res = new Employee();
            $employees = $res->getSqlElementsFromCriteria(null, false, $whereClause);
            foreach($employees as $empl) {
              $resourcesList[$empl->id]=$empl->name;                
            }
        }
        
        return $resourcesList;                
    }
    
    public function hasManagedEmployees() {
        return ($this->getManagedEmployees()==null?false:true);
    }
    
    /**
     * Determine if this manager is a manager
     * @return boolean : True if he is a manager
     */
    public function isManager() {
        if (isLeavesAdmin()) {return true;}
        if ($this->isLeaveManager==1) { return true;} else {return false;}
    }
    
    /**
     * Determine if this manager is actual manager of an employee
     * @param integer $idEmployee : Employee for which know if is actual manager
     * @return boolean : True if is actual manager
     */
    public function isManagerOfEmployee($idEmployee=null) {
        if ($idEmployee==null) {return false; }
        if (isLeavesAdmin()) {return true;}
        $employeesManaged = $this->getManagedEmployees(true, $this, false, false);
        if (count($employeesManaged)>0) { 
            foreach($employeesManaged as $key => $empl) {
                if ($key == $idEmployee) {
                    return true;
                }
            }
        }
        return false;
    }    
}
?>
