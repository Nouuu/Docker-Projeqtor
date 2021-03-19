<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott LEGRAND (from Salto Consulting - 2018) 
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

// ELIOTT - LEAVE SYSTEM

// RULES :
// x. If isDefault = 1 => idle is readonly and idle = 0
//      Done in construct
//      Done in getValidationScript
// x. If isDefault = 0 => idle not readonly
//      Done in getValidationScript     
// x. Only one EmploymentContractType with isDefault = 1.
//      Done in control
// x. Don't delete if isDefault = 1
//          Done in delete
// x. On delete :
//      Set session value to null if isDefault was 1
//          Done in delete

/* ============================================================================
 * 
 */  
require_once('_securityCheck.php');

class EmploymentContractTypeMain extends SqlElement {
    public $_sec_Description;
    public $id;
    public $name;
//    public $idRecipient; // UNDER REFLEXION : idRecipient or idOrganization ?
    
    public $_sec_treatment;
    public $idManagementType;
    public $idWorkflow;
    public $isDefault=0;
    public $idle;
    
    public $_sec_RightsOfLeaveTypes;
    public $_spe_RightsOfLeaveTypes;
    
    public $_sec_RightsOfDatasOfContract;
    public $_spe_RightsOfDatasOfContract;

  public $_nbColMax=1;
    
    // Define the layout that will be used for lists
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="35%">${name}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
    
    private static $_fieldsAttributes=array(
        "name"=>"required", 
//      "idWorkflow"=>"hidden",
//        "idRecipient"=>"hidden",
        "idManagementType" => "hidden" // TO MODIFY when full MANAGEMENT SYSTEM will be ready : "required"
        );
    
    //private static $_databaseTableName = ''; 
    /** ==========================================================================
    * Constructor
    * @param $id the id of the object in the database (null if not stored yet)
    * @return void
    */ 
    function __construct($id = NULL, $withoutDependentObjects=false) {
     parent::__construct($id,$withoutDependentObjects);
    // If isDefault = 1 => idle is readonly and idle = 0     
     if ($this->isDefault) {
         self::$_fieldsAttributes['idle'] = "readonly";
         $this->idle=0;
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

  
    /** ==========================================================================
     * Get the contractType's id that is the default contractType (ie : isDefault==1)
     * @return integer The project's id
    */
    public static function getDefaultEmploymentContractTypeId() {
        $crit=['isDefault' => '1'];
        $contractType = SqlElement::getFirstSqlElementFromCriteria('EmploymentContractType', $crit);
        if (!isset($contractType->id)) {
            return null;
        }
        return $contractType->id;
    }
  
      /** ==========================================================================
       * Return true if the EmploymentContractType that have the id passed in parameter is the default
       * @param $id integer The EmploymentContractType's id to test if it's the the default
       * @return boolean
    */
    static function isTheDefaultEmploymentContractType($id=null) {
        if ($id==null) {
            return false;        
        }
        $ret = ($id==self::getDefaultEmploymentContractTypeId()?true:false);
        return $ret;
    }
  
// ============================================================================**********
// CONTROL AND SAVE - DELETE FUNCTIONS
// ============================================================================**********
  
   /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
        $result .= $defaultControl;
    }
        
    // Only one EmploymentContractType with isDefault = 1.
    $defaultId = self::getDefaultEmploymentContractTypeId();    
    if ($defaultId!=null && 
        $this->isDefault==1 &&
        $this->id != $defaultId) {
        $result.='<br/>' . i18n('OnlyOneDefaultEmploymentContractType');        
    }
           
    return ($result==""?'OK':$result);	
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $result = parent::save();
        
    if(strpos($result,"OK")===false){
        return $result;
    }
        
    return $result;
  }
  
  public function delete($byPassControl=false) {
    $isDefault = $this->isDefault;
    // Don't delete if isDefault = 1
    if ($isDefault && !$byPassControl) {
        $returnValue = '<b>' . i18n('messageInvalidControls').'</b><br/>'. i18n ( 'CantDeleteTheDefaultEmploymentContractType' ) . '<br/>';
        $returnValue .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
        $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
        $returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
        return $returnValue;
        
    }  
    $result = parent::delete();

    if ($isDefault) {
        // Set session value to null if isDefault was 1
        setSessionValue('idDefaultEmploymentContractType', null);        
    }
    return $result;
  }

  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    
    $colScript = parent::getValidationScript($colName);
        
    if($colName=="isDefault"){
    // if isDefault=1, idle = 0 and idle is readonly
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        $colScript .= '  if (this.checked) { ';
                      // isDefault = 1 
                      //    => idle = 0 and idle is readonly
        $colScript .= '    dijit.byId("idle").set("readOnly", "true");';
        $colScript .= '    dijit.byId("idle").setValue(0);';
        $colScript .= '  } else {';
                      // isDefault = 0
                      //    => Not readonly
        $colScript .= '    dijit.byId("idle").set("readOnly", null);';        
        $colScript .= '  }';
        $colScript .= '  formChanged();';
        $colScript .= '</script>';
    
    }
    return $colScript;
  }

// ============================================================================**********
// DRAW FUNCTIONS
// ============================================================================**********
    
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
    $result="";
    
    switch($item) {
        case 'RightsOfLeaveTypes' :
            $result .= $this->drawSpeRightsOfLeaveTypes();
        break;
        case 'RightsOfDatasOfContract':
            $result.= $this->drawSpeLvTypeRulesOfContract();
            break;
    }    
     return $result;
  }
  
  
  
  /**
   * to draw the attribute $_spe_RightsOfLeaveTypes
   */
  public function drawSpeRightsOfLeaveTypes(){
      global $print;
      
      $months = array (
                        0 => "",
                        1 => i18n("January"),
                        2 => i18n("February"),
                        3 => i18n("March"),
                        4 => i18n("April"),
                        5 => i18n("May"),
                        6 => i18n("June"),
                        7 => i18n("July"),
                        8 => i18n("August"),
                        9 => i18n("September"),
                        10 => i18n("October"),
                        11 => i18n("November"),
                        12 => i18n("December"),
                    );
      
      //global $cr, $print, $user, $browserLocale, $comboDetail;
      //the object LeaveTypeOfEmploymentContractType must contains the id of an EmploymentContractType
      if($this->id==NULL){
          return;
      }
      $obj = $this;
      $result = '';

      $canRead=securityGetAccessRight('menuLeaveTypeOfEmploymentContractType', 'read')!="NO";

      if (!$canRead) {
          return '<i>'.i18n('messageNoAccess',array(i18n('LeaveTypeOfEmploymentContractType'))).'</i>';
      }

      $canUpdate=securityGetAccessRightYesNo('menu'.get_class($obj), 'update', $obj)=="YES";
      
      $canCreateR=securityGetAccessRightYesNo('menuLeaveTypeOfEmploymentContractType', 'create')=="YES";
      
      if (!$canUpdate or $obj->idle==1) {
        $canCreateR=false;
        $canDeleteR=false;
        $canUpdateR=false;
      } else {
        $canDeleteR=true;
        $canUpdateR=true;          
      }

      $idleClass=$obj->idle;
      
      $result .="<table style=\"width:100%\">";
      $result .='<tr><td colspan=4 style="width:100%;"><table style="width:100%;">';
      $result.='<tr>';
      $result.='<td class="assignHeader" style="width:5%">';//replace the class with lvTypeOf...Header
      
      if ($this->id && $idleClass==0) {
        $result .= '<a onClick="addLvTypeOfEmpContractType('.$this->id.')"> '.formatSmallButton('Add').'</a>';
      }
      $result.='</td>';
      
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colIdLeaveType').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colStartMonthPeriod').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colStartDayPeriod').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colPeriodDuration').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colQuantity').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colEarnedPeriod').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colIsIntegerQuotity').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colValidityDuration').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colIsJustifiable').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colIsAnticipated').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colNbDaysAfterNowLeaveDemandIsAllowed').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colNbDaysBeforeNowLeaveDemandIsAllowed').'</td>';
      $result.='</tr>';
      
      $rightsList = $obj->getLeaveTypeOfEmploymentContractType();
      
      foreach($rightsList as $right){
          if ($canUpdateR) {
            $canUpdateR=securityGetAccessRightYesNo('menuLeaveTypeOfEmploymentContractType', 'update', $right)=="YES";
          }
          if ($canDeleteR) {
            $canDeleteR= securityGetAccessRightYesNo('menuLeaveTypeOfEmploymentContractType', 'delete', $right)=="YES";
          }
          $canReadR= securityGetAccessRightYesNo('menuLeaveTypeOfEmploymentContractType', 'read', $right)=="YES";
          $result.="<tr>";
          
          if (!$canReadR) {
              $result .= '<td colspan="9">';
              $result .= '<i>'.i18n('noAccessToThisElement').'</i>';
              $result .= "</td></tr>";
              continue;
          }
          
          $result.="<tr>";
          $idleClass=$right->idle;
          
          if(!$print){
            $result.='<td class="linkData" style="text-align:center;white-space: nowrap;">';
          }
          if ($canUpdateR and !$print) {
            $result .= '<a onClick="editLvTypeOfEmpContractType'
                    . '(\''.htmlEncode($right->id).'\',\''.htmlEncode($right->idEmploymentContractType).'\',\''. htmlEncode($right->idLeaveType).'\','
                    . '\''.htmlEncode($right->startMonthPeriod).'\',\''.htmlEncode($right->startDayPeriod).'\',\''.htmlEncode($right->periodDuration).'\','
                    . '\''.htmlEncode($right->nbDaysAfterNowLeaveDemandIsAllowed).'\',\''.htmlEncode($right->nbDaysBeforeNowLeaveDemandIsAllowed).'\','
                    . '\''.htmlEncode($right->quantity).'\',\''.htmlEncode($right->earnedPeriod).'\',\''.htmlEncode($right->isIntegerQuotity).'\',\''.htmlEncode($right->validityDuration).'\',\''.htmlEncode($right->isJustifiable).'\',\''.htmlEncode($right->isAnticipated).'\');"'
                    . ' '.'title="'.i18n('editLvTypeOfEmpContractType').'" > '.formatSmallButton('Edit').'</a>';
          }
          if ($canDeleteR and !$print) {
            $result .= '  <a onClick="removeLvTypeOfEmpContractType'
                    . '(\''.htmlEncode($right->id).'\');"'
                    . ' '.'title="'.i18n('removeLvTypeOfEmpContractType').'" > '.formatSmallButton('Remove').'</a>';
          }
          $result .= "</td>";
          
          $lvType=new LeaveType($right->idLeaveType);
          $goto="";
          if (!$print and securityGetAccessRightYesNo('menuLeaveType', 'read', '')=="YES") {
            $goto=' onClick="gotoElement('."'".get_class($lvType)."','".htmlEncode($lvType->id)."'".');" style="cursor: pointer;" ';
          }
          
          $result .= '<td class="linkData '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" align="center" '.$goto.'>'.htmlEncode($lvType->name).'</td>';
          if ($right->startMonthPeriod==null) {
              $theMonth = $months[0];
          } else {
              $theMonth=$months[$right->startMonthPeriod];
          }
          $result .= '<td class="linkData" align="center">'.htmlEncode($theMonth).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($right->startDayPeriod).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($right->periodDuration).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($right->quantity).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($right->earnedPeriod).'</td>';
          $result .= '<td class="linkData" align="center"><div dojoType="dijit.form.CheckBox" type="checkbox" readonly '.(($right->isIntegerQuotity==1)?'checked':'').' ></div></td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($right->validityDuration).'</td>';
          $result .= '<td class="linkData" align="center"><div dojoType="dijit.form.CheckBox" type="checkbox" readonly '.(($right->isJustifiable==1)?'checked':'').' ></div></td>';
          $result .= '<td class="linkData" align="center"><div dojoType="dijit.form.CheckBox" type="checkbox" readonly '.(($right->isAnticipated==1)?'checked':'').' ></div></td>';          
          $result .= '<td class="linkData" align="center">'.htmlEncode($right->nbDaysAfterNowLeaveDemandIsAllowed).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($right->nbDaysBeforeNowLeaveDemandIsAllowed).'</td>';
          $result.="</tr>";
      }
      
      $result.='</table>';
      
      $result.='</td></tr></table>';
      
      return $result;
  }
  
  
  /**
   * to draw the attribute $_spe_RightsOfDatasOfContract
   */
  public function drawSpeLvTypeRulesOfContract(){
      global $print;
      //global $cr, $print, $user, $browserLocale, $comboDetail;
      if($this->id==NULL){
          return;
      }
      $obj = $this;
      $result = '';
      
      $canRead=securityGetAccessRight('menuCustomEarnedRulesOfEmploymentContractType', 'read')!="NO";

      if (!$canRead) {
          return '<i>'.i18n('messageNoAccess',array(i18n('CustomEarnedRulesOfEmploymentContractType'))).'</i>';
      }

      $canUpdate=securityGetAccessRightYesNo('menu'.get_class($obj), 'update', $obj)=="YES";
      
      $canCreateR=securityGetAccessRightYesNo('menuCustomEarnedRulesOfEmploymentContractType', 'create')=="YES";
      
      if (!$canUpdate or $obj->idle==1) {
        $canCreateR=false;
        $canDeleteR=false;
        $canUpdateR=false;
      } else {
        $canDeleteR=true;
        $canUpdateR=true;          
      }
      
      $idleClass=$obj->idle;
      
      $result .="<table style=\"width:100%\">";
      $result .='<tr><td colspan=4 style="width:100%;"><table style="width:100%;">';
      $result.='<tr>';
      $result.='<td class="assignHeader" style="width:5%">';//replace the class with customRules...Header
      
      if ($this->id && $idleClass==0) {
        $result .= '<a onClick="addCustomEarnedRulesOfEmpContractType('.$this->id.')"> '.formatSmallButton('Add').'</a>';
      }
      $result.='</td>';
      
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colName').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colTriggeringRule').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colWhereClause').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colQuantity').'</td>';
      $result.= '<td class="assignHeader" style="width:5%">'.i18n('colIdLeaveType').'</td>';
      $result.='</tr>';
      
      $rulesList = $obj->getCustomEarnedRulesOfEmpCOntractType();
      
      foreach($rulesList as $rule){
          if ($canUpdateR) {
            $canUpdateR=securityGetAccessRightYesNo('menuCustomEarnedRulesOfEmploymentContractType', 'update', $rule)=="YES";
          }
          if ($canDeleteR) {
            $canDeleteR= securityGetAccessRightYesNo('menuCustomEarnedRulesOfEmploymentContractType', 'delete', $rule)=="YES";
          }
          $canReadR= securityGetAccessRightYesNo('menuCustomEarnedRulesOfEmploymentContractType', 'read', $rule)=="YES";
          $result.="<tr>";
          if (!$canReadR) {
              $result .= '<td colspan="5">';
              $result .= '<i>'.i18n('noAccessToThisElement').'</i>';
              $result .= "</td></tr>";
              continue;
          }
          $idleClass=$rule->idle;
          
          if(!$print){
            $result.='<td class="linkData" style="text-align:center;white-space: nowrap;">';
          }
          
          if ($canUpdateR and !$print) {
            $result .= '<a onClick="editCustomEarnedRulesOfEmpContractType'
                    . '(\''.htmlEncode($rule->id).'\',\''.htmlEncode($rule->idEmploymentContractType).'\',\''. htmlEncode($rule->idLeaveType).'\','
                    . '\''.htmlEncode($rule->quantity).'\',\''.htmlEncode($rule->name).'\',\''.htmlEncode($rule->rule).'\',\''.htmlEncode($rule->whereClause).'\');"'
                    . ' '.'title="'.i18n('editCustomEarnedRulesOfEmpContractType').'" > '.formatSmallButton('Edit').'</a>';
          }
          if ($canDeleteR and !$print) {
            $result .= '  <a onClick="removeCustomEarnedRulesOfEmpContractType'
                    . '(\''.htmlEncode($rule->id).'\');"'
                    . ' '.'title="'.i18n('removeCustomEarnedRulesOfEmpContractType').'" > '.formatSmallButton('Remove').'</a>';
          }
          $result .= "</td>";
          
          $lvType=new LeaveType($rule->idLeaveType);
          $goto="";
          if (!$print and securityGetAccessRightYesNo('menuLeaveType', 'read', '')=="YES") {
            $goto=' onClick="gotoElement('."'".get_class($lvType)."','".htmlEncode($lvType->id)."'".');" style="cursor: pointer;" ';
          }
          
          $result .= '<td class="linkData" align="center">'.htmlEncode($rule->name).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($rule->rule).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($rule->whereClause).'</td>';
          $result .= '<td class="linkData" align="center">'.htmlEncode($rule->quantity).'</td>';
          $result .= '<td class="linkData '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" align="center" '.$goto.'>'.htmlEncode($lvType->name).'</td>';
          $result.="</tr>";
      }
      
      $result.='</table>';
      
      $result.='</td></tr></table>';
      
      return $result;
  }

// ============================================================================**********
// MISCELANIOUS FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Return the LeaveTypeOfEmploymentContractType of this contractType
   * @return LeaveTypeOfEmploymentContractType[]
   */
  public function getLeaveTypeOfEmploymentContractType(){
      if($this->id==NULL){
          return array();
      }
      $rights= new LeaveTypeOfEmploymentContractType();
      $crit = array(
          "idEmploymentContractType"=>$this->id
      );
      $rightsRq=$rights->getSqlElementsFromCriteria($crit);
      return $rightsRq;
  }
  
    /** ==========================================================================
    * Return the rightsOfLeaveType of this contractType
    * @return array
    */
   public function getCustomEarnedRulesOfEmpCOntractType(){
        if($this->id==NULL){
            return array();
        }
        $rights= new customEarnedRulesOfEmploymentContractType();
        $crit = array(
            "idEmploymentContractType"=>$this->id
        );
        $rightsRq=$rights->getSqlElementsFromCriteria($crit);
        return $rightsRq;
    }
  
    public function getEmploymentContract($withClosed=false) {
        $crit = array("idEmploymentContractType" => $this->id);
        if (!$withClosed) {
            $crit["idle"] = '0';
        }
        return $this->getSqlElementsFromCriteria($crit);
    }
}
?>
