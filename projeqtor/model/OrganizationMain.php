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

/** ============================================================================
 * Organization is a structure that provides consolidation over new axis.
 */ 
require_once('_securityCheck.php');
class OrganizationMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place
  public $name;
  public $idOrganizationType;
  public $idResource;
  public $_byMet_hierarchicName;
  public $idOrganization;
  public $_spe_arboOrganization;
  public $idUser;
  public $creationDate;
  public $lastUpdateDateTime;
  public $_tab_2_1 = array('idle','idleDate','idStatus');
  public $idle;
  public $idleDateTime;
  public $description;
  public $_sec_ValueAlertOverWarningOverOkUnder;
  public $_tab_3_1_smallLabel = array('alertOver', 'warningOver', 'okUnder','thresholds');
  public $alertOverPct;
  public $warningOverPct;
  public $okUnderPct;
  public $sortOrder;
  public $OrganizationBudgetElementCurrent; // is an object because first Letter is Upper
 // ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT 
  public $_sec_synthesis;
  public $_tab_5_4_smallLabel = array('validated','assigned','real','left','reassessed',
      'work','cost','expense','totalCost');
  // Work row
  public $_byMet_validatedWork;
  public $_byMet_assignedWork;
  public $_byMet_realWork;
  public $_byMet_leftWork;
  public $_byMet_plannedWork;
  // Cost row
  public $_byMet_validatedCost;
  public $_byMet_assignedCost;
  public $_byMet_realCost;
  public $_byMet_leftCost;
  public $_byMet_plannedCost;
  // Expense row  
  public $_byMet_expenseValidatedAmount;
  public $_byMet_expenseAssignedAmount;
  public $_byMet_expenseRealAmount;
  public $_byMet_expenseLeftAmount;
  public $_byMet_expensePlannedAmount;
  // total row
  public $_byMet_totalValidatedCost;
  public $_byMet_totalAssignedCost;
  public $_byMet_totalRealCost;
  public $_byMet_totalLeftCost;
  public $_byMet_totalPlannedCost;
  // END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT 
  // Section that presents the projects that are linked to this organization and its sub-organizations
  // Want a item's count on section header => ='itemsCount=method to call to get objects to count'
  public $_sec_HierarchicOrganizationProjects='itemsCount=getProjectsOfOrganizationAndSubOrganizations';
  public $_spe_Project=array();
  public $_sec_ResourcesOfObject;
  public $_Resource=array();
  public $_spe_affectMembers;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  // hidden
  public $_nbColMax=3;
  
  // ADD BY Marc TABARY - 2017-06-06 - WORK AND COST VISIBILITY
  public $_workVisibility;
  public $_costVisibility;
  
  public static $_projectsList=array();
  public static $_projectsListOut=array();
  public static $_projectsListForWork=array();
  
  private static $staticCostVisibility=null;
  private static $staticWorkVisibility=null;
// END ADD BY Marc TABARY - 2017-06-06 - WORK AND COST VISIBILITY

  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameOrganizationType" width="15%" >${idOrganizationType}</th>
    <th field="name" width="30%" >${name}</th>
    <th field="nameResource" width="10%" >${manager}</th>
    <th field="idle" formatter="booleanFormatter" width="5%" >${idle}</th>  
    ';
  
  private static $_fieldsAttributes=array(
      "name"=>"required",                                   
      "idOrganizationType"=>"required",
      "sortOrder"=>"hidden,noImport",
      "_byMet_hierarchicName"=>"readonly,noImport",
      "idleDateTime"=>"readonly,noImport",
      "alertOverPct"=>"noList,notInFilter",
      "warningOverPct"=>"noList,notInFilter",
      "okUnderPct"=>"noList,notInFilter",
      "_spe_Project"=>"noExport",
      "_byMet_validatedWork"=>"readonly",
      "_byMet_assignedWork"=>"readonly",
      "_byMet_realWork"=>"readonly",
      "_byMet_leftWork"=>"readonly",
      "_byMet_plannedWork"=>"readonly",
      "_byMet_validatedCost"=>"readonly",
      "_byMet_assignedCost"=>"readonly",
      "_byMet_realCost"=>"readonly",
      "_byMet_leftCost"=>"readonly",
      "_byMet_plannedCost"=>"readonly",
      "_byMet_expenseValidatedAmount"=>"readonly",
      "_byMet_expenseAssignedAmount"=>"readonly",
      "_byMet_expenseRealAmount"=>"readonly",
      "_byMet_expenseLeftAmount"=>"readonly",
      "_byMet_expensePlannedAmount"=>"readonly",
      "_byMet_totalValidatedCost"=>"readonly",
      "_byMet_totalAssignedCost"=>"readonly",
      "_byMet_totalRealCost"=>"readonly",
      "_byMet_totalLeftCost"=>"readonly",
      "_byMet_totalPlannedCost"=>"readonly"
  );   
 
  private static $_colCaptionTransposition = array(
      'idResource'=>'manager',
      'idUser'=>'issuer',
      'idOrganization'=>'parentOrganization',
      '_byMet_hierarchicName'=>'hierarchicString',      
      'idleDateTime'=>'idleDate',
  );

  // Spinner for drawing et inputing the alertOverPct, warningOverPct, okUnderPct
  private static $_spinnersAttributes = array(
      'alertOverPct'=>'min:0,max:100,step:5,bkColor:#FFAAAA !important',
      'warningOverPct'=>'min:0,max:100,step:5,bkColor:#FFBE00 !important;',
      'okUnderPct'=>'min:0,max:100,step:5,bkColor:#B5DE8E !important;',      
  );  
  
  private static $_subOrganizationList=array();
  private static $_subOrganizationFlatList=array();

   /** ==========================================================================
   * Constructor
   * @param int             $id the id of the object in the database (null if not stored yet)
   * @param boolean         $withoutDependentObjects
   * @param budgetElement   $budgetElement : the budgetElement for which update synthesis
    *                                         - not null = Do nothing else
    *                                         - null = Normal construct 
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {      
  	parent::__construct($id,$withoutDependentObjects);
  }
  
  public function setAttributesForBudget() {
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
    $this->_attributesSet=true;
    $id=$this->id;
    if(Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {
      self::$_fieldsAttributes["_sec_ValueAlertOverWarningOverOkUnder"] = "hidden,noList,notInFilter,noPrint";
      self::$_fieldsAttributes["alertOverPct"] = "hidden,noList,notInFilter,noPrint";
      self::$_fieldsAttributes["warningOverPct"] = "hidden,noList,notInFilter,noPrint";
      self::$_fieldsAttributes["okUnderPct"] = "hidden,noList,notInFilter,noPrint";
      unset($this->OrganizationBudgetElementCurrent);
      if ($id!==NULL  and trim($id)!='') {            
          $this->calculatePlanningElement();
          $this->setHierarchicString();            
      }
      return;
    } else {
      unset($this->_sec_synthesis);
      unset($this->_tab_5_4_smallLabel);        
      unset($this->_byMet_validatedWork);
      unset($this->_byMet_assignedWork);
      unset($this->_byMet_realWork);
      unset($this->_byMet_leftWork);
      unset($this->_byMet_plannedWork);
      unset($this->_byMet_validatedCost);
      unset($this->_byMet_assignedCost);
      unset($this->_byMet_realCost);
      unset($this->_byMet_leftCost);
      unset($this->_byMet_plannedCost);
      unset($this->_byMet_expenseValidatedAmount);
      unset($this->_byMet_expenseAssignedAmount);
      unset($this->_byMet_expenseRealAmount);
      unset($this->_byMet_expenseLeftAmount);
      unset($this->_byMet_expensePlannedAmount);
      unset($this->_byMet_totalValidatedCost);
      unset($this->_byMet_totalAssignedCost);
      unset($this->_byMet_totalRealCost);
      unset($this->_byMet_totalLeftCost);
      unset($this->_byMet_totalPlannedCost);
    }
// END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
    if (trim($id)) {
      if (is_object($this->OrganizationBudgetElementCurrent)) {
        $this->setHierarchicString();
        if ($this->OrganizationBudgetElementCurrent->id) {
          $this->OrganizationBudgetElementCurrent->setDaughtersBudgetElementAndPlanningElement();
          $this->OrganizationBudgetElementCurrent->setValueOfAlertOverWarningOverOkUnder($this->alertOverPct, $this->warningOverPct, $this->okUnderPct);
          $this->OrganizationBudgetElementCurrent->hideOrganizationBudgetElementMsg(true);
          $this->OrganizationBudgetElementCurrent->setWorkCostExpenseTotalCostBudgetElement();
          $this->OrganizationBudgetElementCurrent->hideSynthesisBudgetAndProjectElement(false);
        } else {
          $this->OrganizationBudgetElementCurrent->hideSynthesisBudgetAndProjectElement(true);
        }
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
   * Return the generic spinnerAttributes
   * @return array[name,value] : the generic $_spinnerAttributes
   */
  protected function getStaticSpinnersAttributes() {
      if(!isset(self::$_spinnersAttributes)) {return array();}
      return self::$_spinnersAttributes;
  }

  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    if(!isset(self::$_layout)) {return array();}
    return self::$_layout;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    if(!isset(self::$_colCaptionTransposition)) {return array();}
    return self::$_colCaptionTransposition;
  }  

    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    if(!isset(self::$_fieldsAttributes)) {return array();}
    return self::$_fieldsAttributes;
  }
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    return $colScript;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  

/** =====================================================================================
 * Draw section of an object linked by an id with the object to which we draw the detail
 * Sample : drawObjectLinkedByIdToObject($obj, 'Project', true)
 *          Draw a section for projects with idxxxx (where xxxx the name of the $obj's classe)
 * --------------------------------------------------------------------------------------
 * @global type $cr
 * @global type $print
 * @global type $outMode
 * @global type $comboDetail
 * @global type $displayWidth
 * @global type $printWidth
 * @param boolean $refresh
 * @return nothing
   */
  function drawProjectsOfOrganizationAndSubOrganizations($item, $refresh=false) {
    global $cr, $print, $outMode, $comboDetail, $displayWidth, $printWidth;

    if ($comboDetail) {
        return;
    }

    $goto='';  
    $obj = $this;
    $objects=array();

    $objLinkedByIdObject = 'Project';

    // Get the visible list of linked Object
    $listVisibleLinkedObj = getUserVisibleObjectsList($objLinkedByIdObject);

    $canUpdate=securityGetAccessRightYesNo('menu' . get_class($obj), 'update', $obj) == "YES";
    if ($canUpdate) {$canUpdate = securityGetAccessRightYesNo('menu' . $objLinkedByIdObject, 'update', $obj) == "YES";}

    if($obj->id!=null and trim($obj->id)!='') {
        if ($obj->idle == 1) {
          $canUpdate=false;
        }
        // Retrieve the projects
        $objects = $obj->getProjectsOfOrganizationAndSubOrganizations();
        
        // Retrieve organization et suborganization in an array ('id'=>array('name','idle')
        $listOrgaAndSubOrga = $obj->getRecursiveSubOrganizationsIdNameIdleList(false,true);
    } // if($obj->id!=null and trim($obj->id)!='')
    
    if (!$refresh and !$print) echo '<tr><td colspan="2">';
    echo '<input type="hidden" id="objectIdle" value="' . htmlEncode($obj->idle) . '" />';

    if (! $print) {
      echo '<table width="99.9%">';
    }  
    echo '<tr>';
    if (!$print) {
      echo '<td class="assignHeader smallButtonsGroup" style="width:5%">';
      if ($obj->id != null and !$print and $canUpdate) {
        // Parameters passed at addLinkObjectToObject
        // 1 - The main object's class name
        // 2 - The id of main object
        // 3 - The linked object's class name
        echo '<a onClick="addLinkObjectToObject(\'' . get_class($obj) . '\',\'' . htmlEncode($obj->id) . '\',\'' . $objLinkedByIdObject .'\');" title="' . i18n('addLinkObject') . '" >'.formatSmallButton('Add').'</a>';

      }
      echo '</td>';
    }
    echo '<td class="assignHeader" style="width:5%">' . i18n('colId') . '</td>';
    echo '<td class="assignHeader" style="width:' . (($print)?'45':'40') . '%">' . i18n('Project') . '</td>';
    echo '<td class="assignHeader" style="width:' . (($print)?'5':'10') . '%">' . i18n('colIdle') . '</td>';
    echo '<td class="assignHeader" style="width:' . (($print)?'45':'40') . '%">' . i18n('Organization') . '</td>';
    echo '</tr>';
    $nbObjects=0;
    foreach ( $objects as $theObj ) {
      $nbObjects++;
      // Name of it organization
      if (array_key_exists($theObj->idOrganization, $listOrgaAndSubOrga)) {
        $orgaNameIdle = $listOrgaAndSubOrga[$theObj->idOrganization];
        $orgaName = $orgaNameIdle['name'];
      } else {
          $orgaName='';
      }
      echo '<tr>';
      if (!$print) {
        echo '<td class="assignData smallButtonsGroup">';
        if (!$print  and 
                $canUpdate 
                and array_key_exists($theObj->id, $listVisibleLinkedObj)
           ) {
           // Implement to following rule :
           // Can't remove link (idOrganization) for suborganizations
           if (get_class($obj)=='Organization' and get_class($theObj)=='Project' and $obj->id != $theObj->idOrganization) {
              echo ' <a title="' . i18n('ownToSubOrganization') . '" >'.formatSmallButton('SubOrganization',false,false).'</a>';
           } else {
                  if($theObj->idle==0) {
                      // Parameters passed at removeLinkObjectFromObject
                      // 1 - The main object's class name
                      // 2 - The linked object's class name
                      // 3 - The id of the selected linked object
                      // 4 - The name of the selected linked object  
                      echo ' <a onClick="removeLinkObjectFromObject(\'' . 
                                                                    get_class($obj) . 
                                                                    '\',\'' . $objLinkedByIdObject . 
                                                                    '\',\'' . htmlEncode($theObj->id) . 
                                                                    '\',\'' . htmlEncode(str_replace("'"," ",$theObj->name)) .
                                                                    '\');" title="' . i18n('removeLinkObject') . '" > '.formatSmallButton('Remove').'</a>';
                  }
           }
        }
        echo '</td>';
      }
      if (array_key_exists($theObj->id, $listVisibleLinkedObj)) {
          echo '<td class="assignData" style="width:5%">#' . htmlEncode($theObj->id) . '</td>';
          if (!$print and 
              securityCheckDisplayMenu(null, get_class($theObj)) and 
              securityGetAccessRightYesNo('menu'.get_class($theObj), 'read', '') == "YES")
          {
            $goto=' onClick="gotoElement(\''.get_class($theObj).'\',\'' . htmlEncode($theObj->id) . '\');" style="cursor: pointer;" ';
          }
          echo '<td '. $goto .' class="assignData hyperlink '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" style="width:' . (($print)?'45':'40') . '%" >' . htmlEncode($theObj->name) . '</td>';
      } else {
          echo '<td class="assignData" style="width:5%"></td>';
          echo '<td class="assignData" style="width:' . (($print)?'45':'40') . '%">' . i18n('isNotVisible') . '</td>';        
      }
          echo '<td class="assignData dijitButtonText" style="width:' . (($print)?'5':'10') . '%">' . htmlDisplayCheckbox($theObj->idle) . '</td>';                
          echo '<td class="assignData" style="width:' . (($print)?'45':'40') . '%">' . htmlEncode($orgaName) . '</td>';

      echo '</tr>';
    }
    if (!$print) {
      echo '</table>';
    }
    if (!$refresh and !$print) echo '</td></tr>';
  }
  
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
    global $print;
    $result="";
        switch($item) {
            // Draw the message that say if BudgetElement exits or not
            case 'Project' :
                $this->drawProjectsOfOrganizationAndSubOrganizations('_spe_'.$item, $refresh);
                break;
        }    
        if($item == 'arboOrganization'){
        	if ($print or !$this->id) return "";
        	$result='<table>';
        	$result.='<tr class="detail generalRowClass">';
        	$result.='<td class="label"></td>';
        	$result.='<td>';
        	$result.='<button id="showStructureButton" dojoType="dijit.form.Button" showlabel="true"';
        	$result.=' class="roundedVisibleButton" title="'.i18n('showStructure').'" style="vertical-align: middle;">';
        	$result.='<span>' . i18n('showStructure') . '</span>';
        	$result.='<script type="dojo/connect" event="onClick" args="evt">';
        	$page="../view/organizationStructure.php?id=$this->id";
        	$result.="var url='$page';";
        	$result.='showPrint(url, "organization", null, "html", "P");';
        	$result.='</script>';
        	$result.='</button>';
        	$result.='</div></td>';
        	$result.='<td style="padding-left:10px;">';
        	$showClose='';
        	if(sessionValueExists('showIdleOrganizationStructure')){
        		$showClose = getSessionValue('showIdleOrganizationStructure');
        		if($showClose=="true")$showClose='checked="checked"';
        	}
        	$result.='  <div id="showIdleOrg" name="showIdleOrg" dojoType="dijit.form.CheckBox" '.$showClose.' class="greyCheck generalColClass" type="checkbox" title="'.i18n('colShowIdleOrganizationStructure').'">';
        	$result.='   <script type="dojo/method" event="onChange" args="evt">';
        	$result.='     saveDataToSession("showIdleOrganizationStructure",this.checked,true);';
        	$result.='   </script>';
        	$result.='  </div>';
        	$result.='</td>';
        	$result.='<td style="text-align:left;white-space: nowrap;">';
        	$result.='  <label class="label" for="showIdleOrg">'.i18n('colShowIdleOrganizationStructure').'</label>';
        	$result.='</td>';
        	$result.='</tr></table>';
        } else if ($item=='affectMembers') {
        	if ($this->id and !$print) {
    	    	$result .= '<button id="affectOrganizationMembers" dojoType="dijit.form.Button" showlabel="true"'; 
    	      $result .= ' class="roundedVisibleButton" title="' . i18n('affectOrganizationMembers') . '" >';
    	      $result .= '<span>' . i18n('affectOrganizationMembers') . '</span>';
    	      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
    	      $result .=  '  affectOrganizationMembers(' . htmlEncode($this->id) . ');';
    	      $result .= '</script>';
    	      $result .= '</button>';
    	      return $result;
        	}
        }    
     return $result;
  }
  
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawCalculatedItem($item){
    $result="";
     return $result;
  }

  // Save without extra save() feature and without controls
  public function simpleSave() {
    return parent::saveForced();
  }

  public function setHierarchicString() {
    if ($this->id==NULL or trim($this->id)=="") {
          $this->_byMet_hierarchicName = '';
    } else { 
        $orga = $this;
        $hierarchicName="";
        while ($orga->idOrganization and trim($orga->idOrganization)!='') {
// COMMENT BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
//            $orga = new Organization($orga->idOrganization);
// END COMMENT BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
            $critArray = array("id" => $orga->idOrganization);
            $orga = SqlElement::getSingleSqlElementFromCriteria("Organization", $critArray);
// END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
            $hierarchicName=$orga->name.' - '.$hierarchicName;
        }
        if ($hierarchicName==='') {$this->_byMet_hierarchicName='';} else {$this->_byMet_hierarchicName = substr($hierarchicName, 0, -3);}
    }
  }
              
   /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {	
    $old=$this->getOld();
    
    // The idleDate
    if ($old->idle != $this->idle) {
        $this->idleDateTime = ($this->idle?date('Y-m-d H:i:s'):null);        
    }
    
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
    if(Parameter::getGlobalParameter('useOrganizationBudgetElement')==="YES") {
    
    if ($this->OrganizationBudgetElementCurrent->id or   # The Budget Element exist for this organization et selected period
        $this->id==null or trim($this->id)==''           # The organization is to create
       ) {
        if ($this->name !== $old->name) {$this->OrganizationBudgetElementCurrent->refName=$this->name;}
        // 'unclose' organization ==> BudgetElement is 'unclose' to.
        if ($this->idle !== $old->idle and $this->idle) {
            $this->OrganizationBudgetElementCurrent->idle=$this->idle;            
            $this->OrganizationBudgetElementCurrent->idleDateTime=$this->idleDateTime;            
        }
    }
    }
    if (!$this->alertOverPct) $this->alertOverPct=0;
    if (!$this->warningOverPct) $this->warningOverPct=0;
    if (!$this->okUnderPct) $this->okUnderPct=0;
    $result = parent::save();
    $lastStatus = getLastOperationStatus($result);
    if ($lastStatus!='OK') {
    return $result; 
    }

// Init sortOrder if new organization
if($old->id==null or trim($old->id)=='') {
    $this->sortOrder = sprintf("%04d", $this->id);    
    $this->simpleSave();
  }
        
   // If manager change and new manager is'nt empty
   if ($old->idResource !=$this->idResource and $this->idResource!=null) {
       // Check if the manager has an organization.
       $resOrgaList = $this->getResourcesOfOrganizationsListAsArray();
       if (!array_key_exists($this->idResource,$resOrgaList)) {
           $manager = new Resource($this->idResource);
           $manager->idOrganization = $this->id;
           $manager->save();
       }
   }  
    
    // Use database colum sortOrder to have the organization level
    if ($old->idOrganization != $this->idOrganization) {
        self::$_subOrganizationList=array();
        self::$_subOrganizationFlatList=array();
        $this->sortOrder = $this->getOrganizationSortOrder();
        // Only save sortOrder of the Organization
        $this->simpleSave();

        // New level of the subOrganizations
        $subOrga = $this->getRecursiveSubOrganizationsFlatList();
        foreach($subOrga as $key=>$name) {
            $orga = new Organization($key);
            $orga->sortOrder = $orga->getOrganizationSortOrder();
            // Only save sortOrder of the subOrganization
            $orga->simpleSave();
        }
    }
    
    if ($this->idOrganization != $old->idOrganization) {
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
        if(Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {
            $this->calculatePlanningElement();
        } else {
        // Change the current BudgetElement
        if ($this->idOrganization and trim($this->idOrganization)!='') {
          $this->OrganizationBudgetElementCurrent->topRefType='Organization';
          $this->OrganizationBudgetElementCurrent->topRefId=$this->idOrganization;
          $this->OrganizationBudgetElementCurrent->topId=null;
        } else {
          $this->OrganizationBudgetElementCurrent->topId=null;
          $this->OrganizationBudgetElementCurrent->topRefType=null;
          $this->OrganizationBudgetElementCurrent->topRefId=null;
        } 
                
        // UpdateSynthesis of the old parent organization
        if ($old->idOrganization and trim($old->idOrganization)!="") {
            $oldParentOrganization = new Organization($old->idOrganization);
            $oldParentOrganization->updateSynthesis();
        }
        //$this->updateSynthesis(); // No use : changing parent does not change current values
        }    
    } else {
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
      if(Parameter::getGlobalParameter('useOrganizationBudgetElement')==="YES") {
// END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
        if($this->idle!=$old->idle or $this->name!=$old->name) {            
            $this->saveOrganizationBudgetElement( # Close BudgetElement only on change idle 0 => 1
                                                   (($this->idle!=$old->idle and $this->idle==1)?$this->idle:null),
                                                   $this->idleDateTime,
                                                   ($this->name!=$old->name?$this->name:null)
                                                  );
        }
      }
    }
    
    return $result; 

  }
  
  
/** ===================================================================
 * Save idle, idleDateTime et name of all not closed BudgetElement of this organization
 * @param integer $idle
 * @param datetime $idleDateTime
 * @param string $name
 * @return Result : The Result Class
 */  
public function saveOrganizationBudgetElement($idle=null,$idleDateTime=null,$name=null) {
    if (($idle==null and $name==null) or $this->id==null or trim($this->id)=='') {return null;}

    $crit = array('refType'=>'Organization',
                  'refId'=>$this->id,
                  'idle'=>'0'
                  );
      $result=null;
      $budgetElement = new BudgetElement();
      $budgetElementList = $budgetElement->getSqlElementsFromCriteria($crit,false,null,null,true,true,null);
      foreach($budgetElementList as $budgetElement) {
          if($idle==1) {
              $budgetElement->idle=1;
              $budgetElement->idleDateTime = $idleDateTime;
          }
          if($name!=null) {
              $budgetElement->refName = $name;
          }
          $result=$budgetElement->save();
          if(getLastOperationStatus($result)!='OK') {return $result;}
      }
      return $result;

}  


  public function delete() {
  	$result = parent::delete();
    return $result;
  }
  

  
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    // Can't be it's own parent organization
    if ($this->id and $this->id==$this->idOrganization) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    }  else if (trim($this->idOrganization)){
      // Can't be a sub Organizaton of one of its sub Organizations  
      $parentList=array();
    	$parent=new Organization($this->idOrganization);
    	while ($parent->idOrganization) {
    		$parentList[$parent->idOrganization]=$parent->idOrganization;
    		$parent=new Organization($parent->idOrganization);
    	}
      if (array_key_exists($this->id,$parentList)) {
        $result.='<br/>' . i18n('errorHierarchicLoop');
      }
    }

    // An organization's manager must be attached to the organization
    $res = new Resource($this->idResource);
    if ($res->idOrganization!=null and $this->id!=$res->idOrganization) {
        $result.='<br/>' . i18n('organizationManagerDifferentOfThisOrganization');        
    }
        
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  /** ====================================================================
   * Update the BudgetElement
   * @param sqlElement budgetElement
   */
  public function updateBudgetElementSynthesis($bE=null) {
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
    if(Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {return;}
// END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
    // PBE - Improve performance and reliability of the synthesis
    if($bE==null) {return;}
    $this->updateSynthesis($bE);
  
  }

  
  /** ====================================================================
   * Update BudgetElement of :
   *    - the organization
   *    - its parent organizations
   * for each Budget period
   * @param boolean $updateIdle : If true, Updade the BudgetElement's idle
   * @param boolean $updateName : If true, Update the BudgetElement's name
   */
  public function updateSynthesis($budget=null) {
    if(Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {return;}
    if (isset(BudgetElement::$_noDispatchArrayBudget[$this->id])) {return;}
    BudgetElement::$_noDispatchArrayBudget[$this->id]=$this->id; // Will avoid double update for same change
    // Retrieve organization's projects
    // Retrieve each BudgetElement of this organization
    if ($budget) {
      $budgetElementList=array('#'.$budget->id=>$budget);
    } else {
      $budgetElement = new BudgetElement();
      $scritBe = array('idle'=>'0', 'refType'=>'Organization', 'refId'=>$this->id);     // No update for closed BudgetElement
      $budgetElementList=$budgetElement->getSqlElementsFromCriteria($scritBe,false,null,'year asc',true,true,null);
    }
    foreach($budgetElementList as $bE) {
      // Update the idle et idleDateTime of BudgetElement
      if ($this->id) {
        if ($this->idle and ! $bE->idle) $bE->idleDateTime = $this->idleDateTime;
        else if (! $this->idle) $bE->idleDateTime = null;
        $bE->idle = $this->idle;
        // Update the BudgetElement's name
        $bE->refName = $this->name;
      }
      $periodValue = $bE->year;
      $this->setProjectsOnOrga($periodValue);
      $pe=new ProjectPlanningElement();
      $arrayFields=array('validatedWork','assignedWork','realWork','leftWork','plannedWork',
          'validatedCost','assignedCost','realCost','leftCost','plannedCost',
          'expenseValidatedAmount','expenseAssignedAmount','expenseRealAmount','expenseLeftAmount','expensePlannedAmount',
          'reserveAmount',
          'totalValidatedCost','totalAssignedCost','totalRealCost','totalLeftCost','totalPlannedCost'
      );
      $whereClause="(refType='Project' and refId in ".transformListIntoInClause(self::$_projectsList).")";
      //$whereClause .= "and ( ".Sql::getYearFunction('coalesce(validatedStartDate,realStartDate,plannedStartDate,initialStartDate)')."=$periodValue )";
      $peSum = $pe->sumSqlElementsFromCriteria($arrayFields, null,$whereClause);
      $whereClause="(refType='Project' and refId in ".transformListIntoInClause(self::$_projectsListOut).")";
      //$whereClause .= "and ( ".Sql::getYearFunction('coalesce(validatedStartDate,realStartDate,plannedStartDate,initialStartDate)')."=$periodValue )";
      $peSub = $pe->sumSqlElementsFromCriteria($arrayFields, null,$whereClause);
      foreach ($arrayFields as $fld) {
        $fldsum='sum'.strtolower($fld);
        $bE->$fld=$peSum[$fldsum]-$peSub[$fldsum];
      }
      // For Real => based on Work
      $work = new Work();
      $whereClause = "year='$periodValue' and idProject in ".transformListIntoInClause(self::$_projectsListForWork);
      $workSum = $work->sumSqlElementsFromCriteria(array('work','cost'),null,$whereClause);      
      $bE->realWork=$workSum['sumwork'];
      $bE->realCost=$workSum['sumcost'];
      $bE->totalRealCost=$workSum['sumcost'];
      // For Expense => based on Expense (real - planned - left=if(planned-real>0 THEN planned-real ELSE 0) - Assigned=planned
      $expense = new Expense();
      $whereClause = 'year=\''.$periodValue.'\' and idProject in '.transformListIntoInClause(self::$_projectsListForWork);
      $expenseSum=$expense->sumSqlElementsFromCriteria(array('plannedAmount','realAmount'), null, $whereClause);
      //$bE->expenseAssignedAmount=$expenseSum['sumplannedamount']; // Keep assigne as sum for project
      $bE->expenseRealAmount=$expenseSum['sumrealamount'];
      $bE->expensePlannedAmount=$expenseSum['sumplannedamount'];
      $expenseLeftSum=$expense->sumSqlElementsFromCriteria(array('plannedAmount'), null, $whereClause.' and realAmount is null');
      $bE->expenseLeftAmount=$expenseLeftSum['sumplannedamount'];
      $bE->save();
    }
    // Repeat for parent organization
    if ($this->idOrganization and trim($this->idOrganization)!='') {
      $orga = new Organization($this->idOrganization);
      $orga->updateSynthesis();
    }
  }
  
//   public function updateSynthesisOld($updateIdle=true) {
// // ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
//     if(Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {return;}
// // END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
      
//     // Retrieve organization's projects (idle=0 and 1)
//     $prjOrgaList = $this->getRecursiveOrganizationProjects(true,false);

//     // Retrieve each BudgetElement of this organization  
//     $budgetElement = new BudgetElement();
//     // No update for closed BudgetElement
//     $scritBe = array('idle'=>'0', 'refType'=>'Organization', 'refId'=>$this->id);
//     $budgetElementList=$budgetElement->getSqlElementsFromCriteria($scritBe,FALSE,NULL,NULL,TRUE,TRUE,NULL);
//     foreach($budgetElementList as $bE) {
//         // Update the idle et idleDateTime of BudgetElement
//         if ($updateIdle) {
//             $bE->idle = $this->idle;
//             $bE->idleDateTime = $this->idleDateTime;
//         }
//         // Update the BudgetElement's name
//         $bE->refName = $this->name;
//         $periodValue = $bE->year;
//         $bE->validatedWork=0;
//         $bE->assignedWork=0;
//         $bE->realWork=0;
//         $bE->leftWork=0;
//         $bE->plannedWork=0;
//         $bE->validatedCost=0;
//         $bE->assignedCost=0;
//         $bE->realCost=0;
//         $bE->leftCost=0;
//         $bE->plannedCost=0;
//         $bE->expenseValidatedAmount=0;
//         $bE->expenseAssignedAmount=0;
//         $bE->expenseRealAmount=0;
//         $bE->expenseLeftAmount=0;
//         $bE->expensePlannedAmount=0;
//         $bE->reserveAmount=0;
//         $bE->totalValidatedCost=0;
//         $bE->totalAssignedCost=0;
//         $bE->totalRealCost=0;
//         $bE->totalLeftCost=0;
//         $bE->totalPlannedCost=0;
        
//         foreach($prjOrgaList as $keyPrjOrga => $name) {
//             // Calculate BudgetElement
//             // For Validated, Assigned AND Left => Based on PlanningElement
//             $pe=new ProjectPlanningElement();
//             $whereClause='(refId='.$keyPrjOrga.' and refType=\'Project\') and ';
//             // BudgetElement period based on 
//             //      - realStartDate and realEndDate if PlanningElement.idle=1
//             //      - validatedStartDate and validatedEndDate if PlanningElement.idle = 0 
//             // xxxStartDate = null : No Filter >
//             // xxxEndDate = null : No filter <
//             // Else filter > and < on selected period
//             /*$whereClause .= '(
//                                 (idle=1 and
//                                     (
//                                         (isnull(realStartDate) and isnull(realEndDate)) or
//                                         (isnull(realStartDate) and year(realEndDate)=YYYY) OR
//                                         (isnull(realEndDate)) OR
//                                         (year(realStartDate)=YYYY or year(realEndDate)=YYYY)
//                                     )
//                                 ) or
//                                 (idle=0 and
//                                     (
//                                         (isnull(validatedStartDate) and isnull(validatedEndDate)) or
//                                         (isnull(validatedStartDate) and year(validatedEndDate)=YYYY) OR
//                                         (isnull(validatedEndDate)) OR
//                                         (year(validatedStartDate)=YYYY or year(validatedEndDate)=YYYY)
//                                     )
//                                 )
//                              )';
//             $whereClause = str_replace('YYYY', $periodValue, $whereClause);*/
//             $whereClause .= "( ".Sql::getYearFunction('coalesce(validatedStartDate,realStartDate,plannedStartDate,initialStartDate)')."=$periodValue )";
//             $arrayFields=array('validatedWork',
//                                'assignedWork',
//                                'realWork',
//                                'leftWork',
//                                'plannedWork',
//                                'validatedCost',
//                                'assignedCost',
//                                'realCost',
//                                'leftCost',
//                                'plannedCost',
//                                'expenseValidatedAmount',
//                                'expenseAssignedAmount',
//                                'expenseRealAmount',
//                                'expenseLeftAmount',
//                                'expensePlannedAmount',
//                                'reserveAmount',
//                                'totalValidatedCost',
//                                'totalAssignedCost',
//                                'totalRealCost',
//                                'totalLeftCost',
//                                'totalPlannedCost'
//                               );
//             $peSum = $pe->sumSqlElementsFromCriteria($arrayFields, null,$whereClause);

//             $bE->validatedWork+=$peSum['sumvalidatedwork'];
//             $bE->assignedWork+=$peSum['sumassignedwork'];
//             $bE->realWork+=$peSum['sumrealwork'];
//             $bE->leftWork+=$peSum['sumleftwork'];
//             $bE->plannedWork+=$peSum['sumplannedwork'];
//             $bE->validatedCost+=$peSum['sumvalidatedcost'];
//             $bE->assignedCost+=$peSum['sumassignedcost'];
//             $bE->realCost+=$peSum['sumrealcost'];
//             $bE->leftCost+=$peSum['sumleftcost'];
//             $bE->plannedCost+=$peSum['sumplannedcost'];
//             $bE->expenseValidatedAmount+=$peSum['sumexpensevalidatedamount'];
//             $bE->expenseAssignedAmount+=$peSum['sumexpenseassignedamount'];
//             $bE->expenseRealAmount+=$peSum['sumexpenserealamount'];
//             $bE->expenseLeftAmount+=$peSum['sumexpenseleftamount'];
//             $bE->expensePlannedAmount+=$peSum['sumexpenseplannedamount'];
//             $bE->reserveAmount+=$peSum['sumreserveamount'];
//             $bE->totalValidatedCost+=$peSum['sumtotalvalidatedcost'];
//             $bE->totalAssignedCost+=$peSum['sumtotalassignedcost'];
//             $bE->totalRealCost+=$peSum['sumtotalrealcost'];
//             $bE->totalLeftCost+=$peSum['sumtotalleftcost'];
//             $bE->totalPlannedCost+=$peSum['sumtotalplannedcost'];
            
//             // If periodValue < current year
//             // Real based on work & expense
//             if($periodValue<date('Y')) {
//                 $bE->realWork=0;
//                 $bE->realCost=0;
//                 $bE->totalRealCost=0;

//                 $bE->expenseAssignedAmount=0;
//                 $bE->expenseRealAmount=0;
//                 $bE->expensePlannedAmount=0;                
//                 $bE->expenseLeftAmount=0;
                
//                 // For Real => based on Work 
//                 $work = new Work();
//                 $whereClause = 'year<=\''.$periodValue.'\' and idProject='.$keyPrjOrga;
//                 $workSum = $work->sumSqlElementsFromCriteria(array('work','cost'),null,$whereClause);
//                 $bE->realWork+=$workSum['sumwork'];
//                 $bE->realCost+=$workSum['sumcost'];
//                 $bE->totalRealCost+=$workSum['sumcost'];

//                 // For Expense => based on Expense (real - planned - left=if(planned-real>0 THEN planned-real ELSE 0) - Assigned=planned
//                 $expense = new Expense();
//                 $whereClause = 'year<=\''.$periodValue.'\' and idProject='.$keyPrjOrga;                
//                 $expenseSum = $expense->sumSqlElementsFromCriteria(array('plannedAmount','realAmount'), null, $whereClause);
//                 $bE->expenseAssignedAmount+=$expenseSum['sumplannedamount'];
//                 $bE->expenseRealAmount+=$expenseSum['sumrealamount'];
//                 $bE->expensePlannedAmount+=$expenseSum['sumplannedamount'];
//                 $bE->expenseLeftAmount+=($expenseSum['sumplannedamount']-$expenseSum['sumrealamount']>0?$expenseSum['sumplannedamount']-$expenseSum['sumrealamount']:0);

//               // Do again work, plannedWork, expense for each sub-project of project
//               $prj = new Project($keyPrjOrga,true);
//               $prjList = $prj->getRecursiveSubProjectsFlatList();
//               foreach($prjList as $keyPrj=>$prjName) {
//                   // For Real => based on Work 
//                   $work = new Work();
//                   $whereClause = 'year<=\''.$periodValue.'\' and idProject='.$keyPrj;
//                   $workSum = $work->sumSqlElementsFromCriteria(array('work','cost'),null,$whereClause);
//                   $bE->realWork+=$workSum['sumwork'];
//                   $bE->realCost+=$workSum['sumcost'];
//                   $bE->totalRealCost+=$workSum['sumcost'];

//                   // For Expense => based on Expense (real - planned - left=if(planned-real>0 THEN planned-real ELSE 0) - Assigned=planned
//                   $expense = new Expense();
//                   $whereClause = 'year<=\''.$periodValue.'\' and idProject='.$keyPrj;                
//                   $expenseSum = $expense->sumSqlElementsFromCriteria(array('plannedAmount','realAmount'), null, $whereClause);
//                   $bE->expenseAssignedAmount+=$expenseSum['sumplannedamount'];
//                   $bE->expenseRealAmount+=$expenseSum['sumrealamount'];
//                   $bE->expensePlannedAmount+=$expenseSum['sumplannedamount'];
//                   $bE->expenseLeftAmount+=($expenseSum['sumplannedamount']-$expenseSum['sumrealamount']>0?$expenseSum['sumplannedamount']-$expenseSum['sumrealamount']:0);
//               } // SubProject
//             }
//         } // Organization's projects        
        
//         // periodValue < current year
//         //     - Left = assigned - real
//         if($periodValue<date('Y')) {
//             $bE->leftWork = ($bE->assignedWork-$bE->realWork<0?0:$bE->assignedWork-$bE->realWork);
//             $bE->leftCost = ($bE->assignedCost-$bE->realCost<0?0:$bE->assignedCost-$bE->realCost);
//             $bE->totalLeftCost = $bE->leftCost + ($bE->expenseAssignedAmount-$bE->expenseRealAmount<0?0:$bE->expenseAssignedAmount-$bE->expenseRealAmount);
//             }
//         //Planned (in fact reevaluate) = real + left then assigned
//         $bE->plannedCost = $bE->realCost+$bE->leftCost;
//         $bE->plannedWork = $bE->realWork+$bE->leftWork;
//         $bE->expensePlannedAmount = $bE->expenseRealAmount+$bE->expenseLeftAmount;
//         $bE->totalPlannedCost = $bE->totalRealCost+$bE->totalLeftCost;
//         $bE->save();
//     }
  
//     // Repeat for parent organization
//     if ($this->idOrganization and trim($this->idOrganization)!='') {
//        $orga = new Organization($this->idOrganization);
//        // Don't update idle or name for the parent organizations
//        $orga->updateSynthesis();
//     }
//   }

  /** ====================================================================
   * Update BudgetElement of :
   *    - the organization
   *    - its parent organizations
   * only for the current BudgetElement
   */
  public function updateSynthesisWithoutPeriod() {      
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
    if(Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {return;}
// END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
    // Update current budgetElement
    $bec=$this->OrganizationBudgetElementCurrent;
    $bec->year=0;
    $this->updateSynthesis($bec);    
  }

//   public function updateSynthesisWithOutPeriodAndWithOutHierarchic() {
// // ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
//     if(Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {return;}
// // END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
      
//     // Update current budgetElement
//     $bec=$this->OrganizationBudgetElementCurrent;
    
//     $bec->validatedWork=0;
//     $bec->assignedWork=0;
//     $bec->realWork=0;
//     $bec->leftWork=0;
//     $bec->plannedWork=0;
//     $bec->validatedCost=0;
//     $bec->assignedCost=0;
//     $bec->realCost=0;
//     $bec->leftCost=0;
//     $bec->plannedCost=0;
//     $bec->expenseValidatedAmount=0;
//     $bec->expenseAssignedAmount=0;
//     $bec->expenseRealAmount=0;
//     $bec->expenseLeftAmount=0;
//     $bec->expensePlannedAmount=0;
//     $bec->reserveAmount=0;
//     $bec->totalValidatedCost=0;
//     $bec->totalAssignedCost=0;
//     $bec->totalRealCost=0;
//     $bec->totalLeftCost=0;
//     $bec->totalPlannedCost=0;
    
//     // Add all Projects
//     $pe=new ProjectPlanningElement();
//     $crit=array('idOrganization'=>$this->id, 'idle'=>'0');
//     $peList=$pe->getSqlElementsFromCriteria($crit);
//     foreach ($peList as $pe) {
//       $bec->validatedWork+=$pe->validatedWork;
//       $bec->assignedWork+=$pe->assignedWork;
//       $bec->realWork+=$pe->realWork;
//       $bec->leftWork+=$pe->leftWork;
//       $bec->plannedWork+=$pe->plannedWork;
//       $bec->validatedCost+=$pe->validatedCost;
//       $bec->assignedCost+=$pe->assignedCost;
//       $bec->realCost+=$pe->realCost;
//       $bec->leftCost+=$pe->leftCost;
//       $bec->plannedCost+=$pe->plannedCost;
//       $bec->expenseValidatedAmount+=$pe->expenseValidatedAmount;
//       $bec->expenseAssignedAmount+=$pe->expenseAssignedAmount;
//       $bec->expenseRealAmount+=$pe->expenseRealAmount;
//       $bec->expenseLeftAmount+=$pe->expenseLeftAmount;
//       $bec->expensePlannedAmount+=$pe->expensePlannedAmount;
//       $bec->reserveAmount+=$pe->reserveAmount;
//       $bec->totalValidatedCost+=$pe->totalValidatedCost;
//       $bec->totalAssignedCost+=$pe->totalAssignedCost;
//       $bec->totalRealCost+=$pe->totalRealCost;
//       $bec->totalLeftCost+=$pe->totalLeftCost;
//       $bec->totalPlannedCost+=$pe->totalPlannedCost;
//       $crit=array('topId'=>$pe->id,'refType'=>'Project');
//       // Remove sub-projects : will remove sub-projects of same Organization (already included) and of different Organization (must not be included)
//       // This way, for projects with sub-projects we count only work on main project, sub-projects are added separately
//       // It is importatn to di this way to remove sub-projects of different Organization 
//       $subList=$pe->getSqlElementsFromCriteria($crit);
//       foreach ($subList as $sub) {
//         $bec->validatedWork-=$sub->validatedWork;
//         $bec->assignedWork-=$sub->assignedWork;
//         $bec->realWork-=$sub->realWork;
//         $bec->leftWork-=$sub->leftWork;
//         $bec->plannedWork-=$sub->plannedWork;
//         $bec->validatedCost-=$sub->validatedCost;
//         $bec->assignedCost-=$sub->assignedCost;
//         $bec->realCost-=$sub->realCost;
//         $bec->leftCost-=$sub->leftCost;
//         $bec->plannedCost-=$sub->plannedCost;
//         $bec->expenseValidatedAmount-=$sub->expenseValidatedAmount;
//         $bec->expenseAssignedAmount-=$sub->expenseAssignedAmount;
//         $bec->expenseRealAmount-=$sub->expenseRealAmount;
//         $bec->expenseLeftAmount-=$sub->expenseLeftAmount;
//         $bec->expensePlannedAmount-=$sub->expensePlannedAmount;
//         $bec->reserveAmount-=$sub->reserveAmount;
//         $bec->totalValidatedCost-=$sub->totalValidatedCost;
//         $bec->totalAssignedCost-=$sub->totalAssignedCost;
//         $bec->totalRealCost-=$sub->totalRealCost;
//         $bec->totalLeftCost-=$sub->totalLeftCost;
//         $bec->totalPlannedCost-=$sub->totalPlannedCost;
//       }
      
//     }
//     if ($bec->expenseValidatedAmount<0) $bec->expenseValidatedAmount=0;
//     if ($bec->expenseAssignedAmount<0) $bec->expenseAssignedAmount=0;
//     if ($bec->expenseRealAmount<0) $bec->expenseRealAmount=0;
//     if ($bec->expenseLeftAmount<0) $bec->expenseLeftAmount=0;
//     if ($bec->expensePlannedAmount<0) $bec->expensePlannedAmount=0;
//     $bec->save();
//   }
  
  
  /** ===========================================
   * Get the idOrganization of the user connected
   * @return idOrganization
   */
  public static function getUserOrganization() {
    $res=new Affectable(getSessionUser()->id);
    return $res->idOrganization;
  }
  
  /** ===================================================================
   * Get the list of resources linked by id with the organization
   * @return array of resources key-name
   */
  public function getResourcesOfOrganizationsListAsArray($limitToActiveResource=false) {
      if ($limitToActiveResource) {$crit['idle'] = '0';}
      $crit['idOrganization'] = $this->id;
      $resource = new Resource();
      $listRes = SqlElement::transformObjSqlElementInArrayKeyName($resource->getSqlElementsFromCriteria($crit));
      return $listRes;
  }
  
  //gautier #4342
  public function getResourcesOfAllSubOrganizationsListAsArray() {
    $listOrga = $this->getRecursiveSubOrganizationsFlatList(false,true);
    $listResOrg = array();
    foreach ($listOrga as $id=>$org){
      $org = new Organization($id);
      $listResOrg += $org->getResourcesOfOrganizationsListAsArray();
    }
    $listResOrg = array_flip($listResOrg);
    return $listResOrg;
  }
  
  /** ===========================================================================
   * Get the list of organizations (with sub_organizations) of the connected user
   * @return array of organization's key-name
   */
  public static function getUserOrganizationsListAsArray() {
    $userConnected = new Affectable(getSessionUser()->id);
    if($userConnected->idOrganization and trim($userConnected->idOrganization)!='') {
        $userOrga = new Organization($userConnected->idOrganization);
        return $userOrga->getRecursiveSubOrganizationsFlatList(true,true);
    } else {return array();}        
}

  /** ===========================================================================
   * Get the list of organizations (with sub_organizations) of the connected user
   * @return string of organization's id separated by commas ('0' if no organization)
   */
  public static function getUserOrganizationList() {
    $userConnected = new Affectable(getSessionUser()->id);
    if($userConnected->idOrganization and trim($userConnected->idOrganization)!='') {
        $userOrga = new Organization($userConnected->idOrganization);
        $orgaList = $userOrga->getRecursiveSubOrganizationsFlatList(true,true);
        if (count($orgaList) === 0 ) {return '0';} 
        $orgaListId='';
        foreach($orgaList as $key => $name) {
            $orgaListId.= $key . ',';
        }        
        return substr($orgaListId, 0, -1);;
    } else {return '0';}
    
  }


  
  /** ==========================================================================
   * Retrieve sortOrder of a organization that represents it hierarchie
   * Top Level = xxxx - Level 1 = xxxx.xxxx - Etc.
   * with xxxx the organization id of the level
   * Rem : This format is used to be coherent with sortOrder of project
   * @return the sortOrder
   */
  public function getOrganizationSortOrder() {
    $orga = $this;
    $sortOrder=sprintf("%04d", $orga->id).'.';
    while ($orga->idOrganization and trim($orga->idOrganization)!='') {
        $orga = new Organization($orga->idOrganization);
        $sortOrder=sprintf("%04d", $orga->id).'.'.$sortOrder;
    }
    return substr($sortOrder, 0, -1);
  }
  
  /** ==========================================================================
   * Recusively retrieves all the hierarchic sub-organization of the current organization
   * @return an array containing id, name, suborganization (recursive array)
   */
  public function getRecursiveSubOrganizations($limitToActiveOrganizations=false) {
    if (array_key_exists($this->id, self::$_subOrganizationList)) {
        return self::$_subOrganizationList[$this->id];
    }
    
    $crit=array('idOrganization'=>$this->id);
    if ($limitToActiveOrganizations) {
      $crit['idle']='0';
    }

    $subOrganizations=$this->getSqlElementsFromCriteria($crit, false,null,null,null,true) ;
    $subOrganizationsList=null;
    foreach ($subOrganizations as $subOrga) {
      $recursiveList=null;
      $recursiveList=$subOrga->getRecursiveSubOrganizations($limitToActiveOrganizations);
      $arrayOrga=array('id'=>$subOrga->id, 'name'=>$subOrga->name, 'idle'=>$subOrga->idle, 'subItems'=>$recursiveList);
//      $arrayOrga=array('id'=>$subOrga->id, 'name'=>$subOrga->name, 'subItems'=>$recursiveList);
      $subOrganizationsList[]=$arrayOrga;
    }
    self::$_subOrganizationList[$this->id]=$subOrganizationsList;
    return $subOrganizationsList;
  }
  
  /** ==========================================================================
   * Get string (x,y,z) containing recursive sub-Organization
   * Used for idOrganization in
   * @return string
   */
  public function getRecursiveSubOrganizationInString($limitToActiveOrganizations=false, $includeSelf=false) {
      $orgaList = $this->getRecursiveSubOrganizationsFlatList($limitToActiveOrganizations, $includeSelf);
        if (count($orgaList) === 0 ) {return '(0)';} 
        $orgaListId='(';
        foreach($orgaList as $key => $name) {
            $orgaListId.= $key . ',';
        }        
        return substr($orgaListId, 0, -1).')';      
  }
  
    /** ==========================================================================
   * Recusively retrieves all the sub-organization of the current organization
   * and presents it as an array list (id,name,idle)
   * @return an array containing the list of suborganizations (id=>array('name'=>name,'idle'=>idle))
   * 
   */
  public function getRecursiveSubOrganizationsIdNameIdleList($limitToActiveOrganizations=false, $includeSelf=false) {

    $tab=$this->getRecursiveSubOrganizations($limitToActiveOrganizations);
    $list=array();
    if ($includeSelf) {
      $list[$this->id]=array('name'=>$this->name,'idle'=>$this->idle);      
    }
    if ($tab) {
      foreach($tab as $subTab) {
        $id=$subTab['id'];
        $name=$subTab['name'];
        $idle=$subTab['idle'];
        $list[$id]=array('name'=>$name,'idle'=>$idle);
//        $subobj=new Organization($id, false);
        $subobj=new Organization();
        $subobj->id = $id;
        $sublist=$subobj->getRecursiveSubOrganizationsIdNameIdleList($limitToActiveOrganizations);
        if ($sublist) {
          $list=array_merge_preserve_keys($list,$sublist);
        }
      }
    }
    return $list;
  }

  
  /** ==========================================================================
   * Recusively retrieves all the sub-organization of the current organization
   * and presents it as a flat array list of id=>name
   * @return an array containing the list of suborganizations as id=>name
   * 
   */
  public function getRecursiveSubOrganizationsFlatList($limitToActiveOrganizations=false, $includeSelf=false) {
// BEGIN - ticket #2862
//    if (array_key_exists($this->id, self::$_subOrganizationFlatList)) {
//        return self::$_subOrganizationFlatList[$this->id];
//    }
// END - ticket #2862
    $tab=$this->getRecursiveSubOrganizations($limitToActiveOrganizations);
    $list=array();
    if ($includeSelf) {
      $list[$this->id]=$this->name;
    }
    if ($tab) {
      foreach($tab as $subTab) {
        $id=$subTab['id'];
        $name=$subTab['name'];
        $list[$id]=$name;
//        $subobj=new Organization($id, false);
        $subobj=new Organization();
        $subobj->id = $id;
        $sublist=$subobj->getRecursiveSubOrganizationsFlatList($limitToActiveOrganizations);
        if ($sublist) {
          $list=array_merge_preserve_keys($list,$sublist);
        }
      }
    }
    self::$_subOrganizationFlatList[$this->id]=$list;
    return $list;
  }

  /** ==========================================================================
   * Retrieve projects of an organization
   * @return an array containing the list of projects as id=>name
   * 
   */
  public function getOrganizationProjects($limitToActiveProjects=false) {
    $crit=array('idOrganization'=>$this->id);
    if ($limitToActiveProjects) {
      $crit['idle']='0';
    }
    $prj = new Project();
    $prjOrgaList=$prj->getSqlElementsFromCriteria($crit);
    $prjList=array();
    foreach($prjOrgaList as $prjOrga) {
        $id = $prjOrga->id;
        $name = $prjOrga->name;
        $prjList[$id] = $name;
    }
    return $prjList;
  }

  public function getProjectsOfOrganizationAndSubOrganizations($obj=null) {
    if($obj==null) {$obj = $this;}  
    $objects=array();
    // Retrieve organization et suborganization in an array ('id'=>array('name','idle')
    $listOrgaAndSubOrga = $obj->getRecursiveSubOrganizationsIdNameIdleList(false,($this->id)?true:false);
    // construct in() string for getSqlElementFromCriteria
    $inString = '';
    foreach($listOrgaAndSubOrga as $orgaAndSubOrga=>$nameIdle) {
        if ($orgaAndSubOrga) $inString.= ','.$orgaAndSubOrga;
    }
    if ($inString!='' and $inString!=',') {
        $inString = '(0'.$inString.')';
        // Retrieve the projects of this organization and its suborganizations
        $whereClose = 'idOrganization in '.$inString;
        $prj = new Project();
        $objects = $prj->getSqlElementsFromCriteria(null,false,$whereClose,null,false,true);        
    }
    
    return $objects;  
  }
  
  /** ==========================================================================
   * Retrieve projects of organization
   * Retrieve projects of its suborganization recursively
   * Output recursively subprojets of projects of organization
   * and presents it as a flat array list of id=>name
   * @return an array containing the list of projects as id=>name
   * 
   */
  public function getRecursiveOrganizationProjects($limitToActiveOrganizations=false,$limitToActiveProjects=true) {  

    $limitToActiveOrganizations=($limitToActiveOrganizations)?1:0;
    $limitToActiveProjects=($limitToActiveProjects)?1:0;
    
    if ($limitToActiveOrganizations and $this->idle === 0) {
        return array();
    }
    
    // Projects of Organization
    $prjList=$this->getOrganizationProjects($limitToActiveProjects);
    
    // SubOrganizations of Organization
    $subOrgaList = $this->getRecursiveSubOrganizationsFlatList($limitToActiveOrganizations);
    foreach($subOrgaList as $keySubOrga=>$val) {
        // Projects of SubOrganization
        $Orga = new Organization($keySubOrga, false);
        $prjSubOrgaList=$Orga->getOrganizationProjects($limitToActiveProjects);
        foreach($prjSubOrgaList as $keyPrjSubOrga=>$name) {
            $id = $keyPrjSubOrga;
            $prjList[$id] = $name;            
        }
    }
    $prjList = array_unique($prjList);
    // Output SubProjects of projects from projects list
    $prjWithOutSubPrjList = $prjList;
    foreach($prjList as $keyPrj => $name) {
        // List of SubProjects
        $project = new Project();
        $project->id = $keyPrj;
        $subPrjList = $project->getRecursiveSubProjectsFlatList($limitToActiveProjects);
      
        foreach($subPrjList as $keySubPrj => $name) {
          if (array_key_exists($keySubPrj, $prjWithOutSubPrjList)) {
              unset($prjWithOutSubPrjList[$keySubPrj]);
          }  
        }
    }
    return $prjWithOutSubPrjList;
  }
  
  private static function getYearFromPlanningElement($pe) {
    if ($pe->validatedStartDate) return substr($pe->validatedStartDate,0,4);
    if ($pe->realStartDate) return substr($pe->realStartDate,0,4);
    if ($pe->plannedStartDate) return substr($pe->plannedStartDate,0,4);
    if ($pe->initialStartDate) return substr($pe->initialStartDate,0,4);
    return date('Y');
  }
  public function setProjectsOnOrga($periodValue=null) {
    self::$_projectsList=array();
    self::$_projectsListOut=array();
    self::$_projectsListForWork=array();
    $pe=new ProjectPlanningElement();
    
    $orgaList=$this->getSubOrgaFlatList();
    $critOrga="refType='Project' and idOrganization in ".transformListIntoInClause($orgaList);
    $list=$pe->getSqlElementsFromCriteria(null,false,$critOrga,'wbsSortable asc',false,true); // List of all projects linked to orga
    $critOnWBS="refType='Project' and (1=0 ";
    $wbsList=array();
    foreach ($list as $pe) {
      self::$_projectsListForWork[$pe->refId]=$pe->refName;
      //if ($periodValue and self::getYearFromPlanningElement($pe)!=$periodValue) continue; 
      if (strlen($pe->wbsSortable)>5 and in_array(substr($pe->wbsSortable,-6),$wbsList)) continue; // Parent already in the list
      $critOnWBS.=" or wbsSortable like '$pe->wbsSortable%'"; // Will fetch all sub-projects
      $wbsList[$pe->refId]=$pe->wbsSortable;
    }
    $critOnWBS.=')';
    $list=$pe->getSqlElementsFromCriteria(null,false,$critOnWBS,'wbsSortable asc',false,true); // List all project where parent is in the orga
    $wbsIn=array();
    $wbsOut=array();
    foreach ($list as $pe) {
      $yearStartProj=self::getYearFromPlanningElement($pe);
      if (isset($orgaList[$pe->idOrganization]) and ! $pe->cancelled and (!$periodValue or $yearStartProj==$periodValue)) { // Project in Orga, except cancelled
        $wbsIn[]=$pe->wbsSortable;
        if (strlen($pe->wbsSortable)<=5 or ! in_array(substr($pe->wbsSortable,0,-6),$wbsIn)) {
          self::$_projectsList[$pe->refId]=$pe->refName; // Parent not in the list, add ! 
        }
      } else { // Not in = Orga
        if (! in_array(substr($pe->wbsSortable,0,-6),$wbsIn)) continue;
        $wbsOut[]=$pe->wbsSortable;
        if (strlen($pe->wbsSortable)<=5 or ! in_array(substr($pe->wbsSortable,0,-6),$wbsOut)) {
          self::$_projectsListOut[$pe->refId]=$pe->refName; // Parent not in the list, add !
        }
      }
    }
  }
  
  public function getSubOrgaFlatList() {
    $res=array($this->id=>$this->name);
    $subList=$this->getSqlElementsFromCriteria(array('idOrganization'=>$this->id));
    foreach ($subList as $sub) {
      $res=array_merge_preserve_keys($res,$sub->getSubOrgaFlatList());
    }
    return $res;
  }
  
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT  
  /** ==========================================================================
   * Calculate the work, cost, expense of projets that belong to the organization
   * @return Nothing
   */
  
  function calculatePlanningElement() {
    $this->setProjectsOnOrga();
    // Update the idle et idleDateTime of BudgetElement      $periodValue = $bE->year;
    $pe=new ProjectPlanningElement();
    $arrayFields=array('validatedWork','assignedWork','realWork','leftWork','plannedWork',
        'validatedCost','assignedCost','realCost','leftCost','plannedCost',
        'expenseValidatedAmount','expenseAssignedAmount','expenseRealAmount','expenseLeftAmount','expensePlannedAmount',
        'totalValidatedCost','totalAssignedCost','totalRealCost','totalLeftCost','totalPlannedCost'
    );
    $whereClause="(refType='Project' and refId in ".transformListIntoInClause(self::$_projectsList).")";
    $peSum = $pe->sumSqlElementsFromCriteria($arrayFields, null,$whereClause);
    $whereClause="(refType='Project' and refId in ".transformListIntoInClause(self::$_projectsListOut).")";
    $peSub = $pe->sumSqlElementsFromCriteria($arrayFields, null,$whereClause);
    foreach ($arrayFields as $fld) {
      $fldsum='sum'.strtolower($fld);
      $fldorg='_byMet_'.$fld;
      $this->$fldorg=$peSum[$fldsum]-$peSub[$fldsum];
    }
  }
// function calculatePlanningElement() {
//     $this->_byMet_validatedWork=0;
//     $this->_byMet_assignedWork=0;
//     $this->_byMet_realWork=0;
//     $this->_byMet_leftWork=0;
//     $this->_byMet_plannedWork=0;
//     $this->_byMet_validatedCost=0;
//     $this->_byMet_assignedCost=0;
//     $this->_byMet_realCost=0;
//     $this->_byMet_leftCost=0;
//     $this->_byMet_plannedCost=0;
//     $this->_byMet_expenseValidatedAmount=0;
//     $this->_byMet_expenseAssignedAmount=0;
//     $this->_byMet_expenseRealAmount=0;
//     $this->_byMet_expenseLeftAmount=0;
//     $this->_byMet_expensePlannedAmount=0;
//     $this->_byMet_totalValidatedCost=0;
//     $this->_byMet_totalAssignedCost=0;
//     $this->_byMet_totalRealCost=0;
//     $this->_byMet_totalLeftCost=0;
//     $this->_byMet_totalPlannedCost=0;
  
//     // Get list of projets of the organization and sub-organizations
//     $lstProjects = $this->getRecursiveOrganizationProjects(true,false);
//     //foreach($lstProjects as $keyPrjOrga=>$name) {
//     $pe=new ProjectPlanningElement();
//     $whereClause='(refId in '.transformListIntoInClause($lstProjects).' and refType=\'Project\')';
//     $arrayFields=array('validatedWork',
//                        'assignedWork',
//                        'realWork',
//                        'leftWork',
//                        'plannedWork',
//                        'validatedCost',
//                        'assignedCost',
//                        'realCost',
//                        'leftCost',
//                        'plannedCost',
//                        'expenseValidatedAmount',
//                        'expenseAssignedAmount',
//                        'expenseRealAmount',
//                        'expenseLeftAmount',
//                        'expensePlannedAmount',
//                        'totalValidatedCost',
//                        'totalAssignedCost',
//                        'totalRealCost',
//                        'totalLeftCost',
//                        'totalPlannedCost'
//                       );
//     $peSum = $pe->sumSqlElementsFromCriteria($arrayFields, null,$whereClause);
//     $this->_byMet_validatedWork+=$peSum['sumvalidatedwork'];
//     $this->_byMet_assignedWork+=$peSum['sumassignedwork'];
//     $this->_byMet_realWork+=$peSum['sumrealwork'];
//     $this->_byMet_leftWork+=$peSum['sumleftwork'];
//     $this->_byMet_plannedWork+=$peSum['sumplannedwork'];
//     $this->_byMet_validatedCost+=$peSum['sumvalidatedcost'];
//     $this->_byMet_assignedCost+=$peSum['sumassignedcost'];
//     $this->_byMet_realCost+=$peSum['sumrealcost'];
//     $this->_byMet_leftCost+=$peSum['sumleftcost'];
//     $this->_byMet_plannedCost+=$peSum['sumplannedcost'];
//     $this->_byMet_expenseValidatedAmount+=$peSum['sumexpensevalidatedamount'];
//     $this->_byMet_expenseAssignedAmount+=$peSum['sumexpenseassignedamount'];
//     $this->_byMet_expenseRealAmount+=$peSum['sumexpenserealamount'];
//     $this->_byMet_expenseLeftAmount+=$peSum['sumexpenseleftamount'];
//     $this->_byMet_expensePlannedAmount+=$peSum['sumexpenseplannedamount'];
//     $this->_byMet_totalValidatedCost+=$peSum['sumtotalvalidatedcost'];
//     $this->_byMet_totalAssignedCost+=$peSum['sumtotalassignedcost'];
//     $this->_byMet_totalRealCost+=$peSum['sumtotalrealcost'];
//     $this->_byMet_totalLeftCost+=$peSum['sumtotalleftcost'];
//     $this->_byMet_totalPlannedCost+=$peSum['sumtotalplannedcost'];   
//     //}
    
// }

    /** ==========================================================================
   * Set the visibility of work and cost in function of user's right
   * @return Nothing
   */
    public function setVisibility($profile=null) {
        if (! sessionUserExists()) {
          return;
        }
        if (! $profile) {
          $user=getSessionUser();
          $profile=$user->getProfile();
        }

        if (self::$staticCostVisibility and isset(self::$staticCostVisibility[$profile]) 
        and self::$staticWorkVisibility and isset(self::$staticWorkVisibility[$profile]) ) {
          $this->_costVisibility=self::$staticCostVisibility[$profile];
          $this->_workVisibility=self::$staticWorkVisibility[$profile];
          return;
        }

        $user=getSessionUser();
        $list=SqlList::getList('VisibilityScope', 'accessCode', null, false);
        $hCost=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile,'scope'=>'cost'));
        $hWork=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile,'scope'=>'work'));
        if ($hCost->id) {
          $this->_costVisibility=$list[$hCost->rightAccess];
        } else {
          $this->_costVisibility='ALL';
        }
        if ($hWork->id) {
          $this->_workVisibility=$list[$hWork->rightAccess];
        } else {
          $this->_workVisibility='ALL';
        }
        if (!self::$staticCostVisibility) self::$staticCostVisibility=array();
        if (!self::$staticWorkVisibility) self::$staticWorkVisibility=array();
        self::$staticCostVisibility[$profile]=$this->_costVisibility;
        self::$staticWorkVisibility[$profile]=$this->_workVisibility;
    }

  /** ==========================================================================
   * Set the attribute of work, cost, expense in function of visibility
   * @return Nothing
   */
    public function setAttributes() {
      //$currentDetailObj=SqlElement::getCurrentObject(null,null,true,false);
      //if (($currentDetailObj and get_class($currentDetailObj)==get_class($this) and $currentDetailObj->id==$this->id)
      //or !$this->id) { 
      //}
      if(Parameter::getGlobalParameter('useOrganizationBudgetElement')==="YES") {return;}
      $this->setVisibility();
      $wcVisibility = $this->_workVisibility.$this->_costVisibility;
      switch ($wcVisibility) {
          case "NONO" :
              $this->hideWorkCost();
              break;
          case "NOALL" :
              $this->showOnlyCost();
              break;
          case "NOVAL" :
              $this->hideWorkAndShowValidatedCost();
              break;
          case "ALLALL" :
              $this->showWorkCost();
              break;
          case "ALLNO" :
              $this->showOnlyWork();
              break;
          case "ALLVAL" :
              $this->showAllWorkAndValidatedCost();
              break;
          case "VALVAL" :
              $this->showValidated();
              break;
          case "VALALL" :
              $this->showOnlyValidatedWorkAndAllCost();
              break;
          case "VALNO" :
              $this->showOnlyValidatedWorkAndHideCost();
              break;
          default:
              $this->hideWorkCost();
              break;
      }
    }
    
  /** =========================================================
   * Hide all fields those have :
   *  - $_fieldsAttributes defined
   *  - Cost, Work at the name's end
   *  - Amount (case insensitive) in the name
   * @return nothing
   */
  private function hideWorkCost() {
    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false ) {
                continue;
            }
            
            if (substr($name,-4,4)==='Cost' or
                substr($name,-4,4)==='Work' or
                strtolower(substr($name,-6,6))==='amount') {
                    if (strpos($value,'readonly')!==false) {
                        self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                    } else {
                        if (strpos($value,'hidden')===false) {
                            self::$_fieldsAttributes[$name] = $value.',hidden';
                        }
                    }
            }
        }
    }        
  }

  /** =========================================================
   * For fields those have $_fieldsAttributes defined :
   *  - Show :
   *    - Cost, Work at the name's end
   *    - Amount (case insensitive) in the name
   *  - Allows enter value :
   *    - budget (case insensitive) and not total (case insensitive) in the name
   * @return nothing
   */
  private function showWorkCost() {
    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false) {
                continue;
            }
            // Budgets : Allows input its except for total
            if (strpos(strtolower($name),'budget')!==false and strpos(strtolower($name),'total')===false) {
                self::$_fieldsAttributes[$name] = str_replace('readonly', '', $value);
                self::$_fieldsAttributes[$name] = str_replace('hidden', '', $value);
            } else {
                // Cost - Amount - Work are readonly
                if (substr($name,-4,4)==='Cost' or
                    substr($name,-4,4)==='Work' or
                    strtolower(substr($name,-6,6))==='amount') {
                        if (strpos($value,'hidden')!==false) {
                            self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                        } else {
                            if (strpos($value,'readonly')===false) {
                                self::$_fieldsAttributes[$name] = $value.',readonly';
                            }
                        }
                }
            }
        }
    }        
    return;
  }
  
  /** ===============================================
   * For fields those have $_fieldsAttributes defined :
   *  - Allows enter value :
   *    - budget (case insensitive) and not total (case insensitive) in the name
   *  - Show :
   *    - validated (case insensitive) in the name
   *  - Hide :
   *    - Cost, Work in the name's end
   *    - amount (cas insensitive) in the name
   * @return nothing
   */
  private function showValidated() {    
    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false) {
                continue;
            }
            // total budget : show
            if (strpos(strtolower($name),'total')!== false and strpos(strtolower($name),'budget')!==false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Budgets : Allows input its except for total
            if (strpos(strtolower($name),'budget')!==false and strpos(strtolower($name),'total')===false) {
                self::$_fieldsAttributes[$name] = str_replace('readonly', '', $value);
                self::$_fieldsAttributes[$name] = str_replace('hidden', '', $value);
                continue;            
            } 

            // Validated : Show
            if (strpos(strtolower($name),'validated')!== false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Cost, Work, amount : Hide
            if (strpos(strtolower($name),'amount')!== false or
                substr($name,-4,4)==='Cost' or
                substr($name,-4,4)==='Work') {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }            
            }
        }    
    }        
  }

  
  /** ===============================================
   * For fields those have $_fieldsAttributes defined :
   *  - Allows enter value :
   *    - budget (case insensitive) in the name and Work at the name's end
   *  - Show :
   *    - Work in the name's end
   *  - Hide :
   *    - Cost in the name's end
   *    - amount (case insensitive) in the name
   * @return nothing
   */
  private function showOnlyWork() {
    
    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false) {
                continue;
            }
            // Budgets : Allows input only for work
            if (strpos(strtolower($name),'budget')!==false and substr($name,-4,4)==='Work') {
                self::$_fieldsAttributes[$name] = str_replace('readonly', '', $value);
                self::$_fieldsAttributes[$name] = str_replace('hidden', '', $value);
                continue;            
            } 

            // Work : Show
            if (substr($name,-4,4)==='Work') {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Cost, amount : Hide
            if (strpos(strtolower($name),'amount')!== false or
                substr($name,-4,4)==='Cost') {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }            
            }
        }    
    }        
  }
  
  /** ===============================================
   * For fields those have $_fieldsAttributes defined :
   *  - Allows enter value, except total (case insensitive) in the name:
   *    - budget (case insensitive) in the name and ( Cost at the name's end or amount (case insensitive) in the name)
   *  - Show :
   *    - Cost in the name's end and amount (case insensitive) in the name
   *  - Hide :
   *    - Work in the name's end
   * @return nothing
   */
  private function showOnlyCost() {

    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false) {
                continue;
            }
            // total budget : show
            if (strpos(strtolower($name),'total')!== false and strpos(strtolower($name),'budget')!==false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Budgets : Allows input only for cost and amount (except total)
            if (strpos(strtolower($name),'budget')!==false and
                strpos(strtolower($name),'total')===false and  
                (substr($name,-4,4)==='Cost' or strpos(strtolower($name),'amount')!== false)
               ) {
                self::$_fieldsAttributes[$name] = str_replace('readonly', '', $value);
                self::$_fieldsAttributes[$name] = str_replace('hidden', '', $value);
                continue;            
            } 

            // Cost and amount : Show
            if (substr($name,-4,4)==='Cost' or strpos(strtolower($name),'amount')!== false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Work : Hide
            if (substr($name,-4,4)==='Work') {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }            
            }
        }    
    }        
  }

  /** ===============================================
   * For fields those have $_fieldsAttributes defined :
   *  - Allows enter value, except total (case insensitive) in the name :
   *    - budget (case insensitive) in the name
   *  - Show :
   *    - Cost in the name's end and amount (case insensitive) in the name
   *    - Work in the name's end and validated (case insensitive) in the name
   *  - Hide :
   *    - Work in the name's end and not validated (case insensitive) in the name
   * @return nothing
   */
  private function showOnlyValidatedWorkAndAllCost() {
    
    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false) {
                continue;
            }
            // total budget : show
            if (strpos(strtolower($name),'total')!== false and strpos(strtolower($name),'budget')!==false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Budgets : Allows input except total
            if (strpos(strtolower($name),'budget')!==false and
                strpos(strtolower($name),'total')===false) {
                self::$_fieldsAttributes[$name] = str_replace('readonly', '', $value);
                self::$_fieldsAttributes[$name] = str_replace('hidden', '', $value);
                continue;            
            } 

            // Cost and amount : Show
            if (substr($name,-4,4)==='Cost' or strpos(strtolower($name),'amount')!== false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // validated Work : Show
            if (substr($name,-4,4)==='Work' and strpos(strtolower($name),'validated')!== false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // not validated Work : Hide
            if (substr($name,-4,4)==='Work' and strpos(strtolower($name),'validated')=== false) {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }            
            }
        }    
    }        
  }

  /** ===============================================
   * For fields those have $_fieldsAttributes defined :
   *  - Allows enter value, except total (case insensitive) in the name :
   *    - budget (case insensitive) in the name and not Work at the name's end
   *  - Show :
   *    - Cost in the name's end and amount (case insensitive) in the name and validated (case insensitive) in the name
   *  - Hide :
   *    - Work in the name's end
   *    - Cost in the name's end and amount (case insensitive) in the name and not validated (case insensitive) in the name
   * @return nothing
   */
  private function hideWorkAndShowValidatedCost() {
    
    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false) {
                continue;
            }
            // total budget : show
            if (strpos(strtolower($name),'total')!== false and strpos(strtolower($name),'budget')!==false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Budgets : Allows input except total and work
            if (strpos(strtolower($name),'budget')!==false and
                strpos(strtolower($name),'total')===false and
                substr($name,-4,4)!=='Work') {
                self::$_fieldsAttributes[$name] = str_replace('readonly', '', $value);
                self::$_fieldsAttributes[$name] = str_replace('hidden', '', $value);
                continue;            
            } 

            // Cost and amount validated : Show
            if ((substr($name,-4,4)==='Cost' or strpos(strtolower($name),'amount')!== false) and
                 strpos(strtolower($name),'validated')!==false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Work : Hide
            if (substr($name,-4,4)==='Work') {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }
                continue;
            }

            // Cost and amount not validated : Hide
            if ((substr($name,-4,4)==='Cost' or strpos(strtolower($name),'amount')!== false) and
                 strpos(strtolower($name),'validated')===false) {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }            
            }
        }    
    }        
  }

  /** ===============================================
   * For fields those have $_fieldsAttributes defined :
   *  - Allows enter value, except total (case insensitive) in the name :
   *    - budget (case insensitive) in the name
   *  - Show :
   *    - Cost in the name's end and amount (case insensitive) in the name and validated (case insensitive) in the name
   *    - Work in the name's end
   *  - Hide :
   *    - Cost in the name's end and amount (case insensitive) in the name and not validated (case insensitive) in the name
   * @return nothing
   */
  private function showAllWorkAndValidatedCost() {

    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false) {
                continue;
            }
            // total budget : show
            if (strpos(strtolower($name),'total')!== false and strpos(strtolower($name),'budget')!==false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Budgets : Allows input except total
            if (strpos(strtolower($name),'budget')!==false and
                strpos(strtolower($name),'total')===false) {
                self::$_fieldsAttributes[$name] = str_replace('readonly', '', $value);
                self::$_fieldsAttributes[$name] = str_replace('hidden', '', $value);
                continue;            
            } 

            // Cost and amount validated : Show
            if ((substr($name,-4,4)==='Cost' or strpos(strtolower($name),'amount')!== false) and
                 strpos(strtolower($name),'validated')!==false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Work : Show
            if (substr($name,-4,4)==='Work') {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Cost and amount not validated : Hide
            if ((substr($name,-4,4)==='Cost' or strpos(strtolower($name),'amount')!== false) and
                 strpos(strtolower($name),'validated')===false) {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }            
            }
        }    
    }        
  }
  
  /** ===============================================
   * For fields those have $_fieldsAttributes defined :
   *  - Allows enter value, except total (case insensitive) in the name :
   *    - budget (case insensitive) in the name and Work at the name's end
   *  - Show :
   *    - Work in the name's end and validated (case insensitive) in the name
   *  - Hide :
   *    - Cost in the name's end and amount (case insensitive)
   *    - Work in the name's end and not validated (case insensitive) in the name
   * @return nothing
   */
  private function showOnlyValidatedWorkAndHideCost() {
    
    if (isset(self::$_fieldsAttributes)) {
        foreach(self::$_fieldsAttributes as $name => $value) {
            // Do nothing if 'hiddenforce'
            if (strpos(strtolower($value),'hiddenforce')!== false) {
                continue;
            }
            // total budget : hide
            if (strpos(strtolower($name),'total')!== false and strpos(strtolower($name),'budget')!==false) {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }            
                continue;
            }

            // Budgets work : Allows input except total
            if (strpos(strtolower($name),'budget')!==false and
                substr($name,-4,4)==='Work' and    
                strpos(strtolower($name),'total')===false) {
                self::$_fieldsAttributes[$name] = str_replace('readonly', '', $value);
                self::$_fieldsAttributes[$name] = str_replace('hidden', '', $value);
                continue;            
            } 

            // Work validated : Show
            if (substr($name,-4,4)==='Work' and strpos(strtolower($name),'validated')!==false) {
                if (strpos($value,'hidden')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('hidden', 'readonly', $value);
                } else {
                    if (strpos($value,'readonly')===false) {
                        self::$_fieldsAttributes[$name] = $value.',readonly';
                    }
                }
                continue;
            }

            // Cost and amount : Hide
            if (substr($name,-4,4)==='Cost' or strpos(strtolower($name),'amount')!== false) {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }
                continue;
            }

            // Work not validated : Hide
            if (substr($name,-4,4)==='Work' and strpos(strtolower($name),'validated')===false) {
                if (strpos($value,'readonly')!==false) {
                    self::$_fieldsAttributes[$name] = str_replace('readonly', 'hidden', $value);
                } else {
                    if (strpos($value,'hidden')===false) {
                        self::$_fieldsAttributes[$name] = $value.',hidden';
                    }
                }
                continue;
            }
        }    
    }        
  }
  public function getMembers() {
    $result=array();
    $crit=array('idOrganization'=>$this->id);
    $res=new Resource();
    $resList=$res->getSqlElementsFromCriteria($crit, false);
    foreach ($resList as $res) {
      if (!$res->idle) $result[$res->id]=$res->name;
    }
    return $result;
  }
    
  public function getRecursiveSubOrganizationStructure($showClose){
  	if($showClose==true){
  	  $crit = array('idOrganization'=>$this->id);
  	}else{
  	  $crit=array('idOrganization'=>$this->id, 'idle'=>'0');
  	}
  	$obj=new Organization();
  	$subOrganization=$obj->getSqlElementsFromCriteria($crit, false,null,null,null,true) ;
  	$subOrganizationList=null;
  	foreach ($subOrganization as $subOrg) {
  		$recursiveList=null;
  		$recursiveList=$subOrg->getRecursiveSubOrganizationStructure($showClose);
  		$arrayOrg=array('id'=>$subOrg->id, 'name'=>$subOrg->name, 'subItems'=>$recursiveList);
  		$subOrganizationList[]=$arrayOrg;
  	}
  	return $subOrganizationList;
  }
  
  public function getParentOrganizationStructure() {
  	$result=array();
  	if ($this->idOrganization) {
  		$parent=new Organization($this->idOrganization);
  		$result=array_merge_preserve_keys($parent->getParentOrganizationStructure(),array($parent->id=>$parent->name));
  	}
  	return $result;
  }
  
  public function isElementary($showClose){
  	$result = true;
  	if($showClose==true){
  	  $crit = array('idOrganization'=>$this->id);
  	}else{
  	  $crit = array('idOrganization'=>$this->id, 'idle'=>'0');
  	}
  	$cpt = $this->countSqlElementsFromCriteria($crit);
  	if($cpt > 0)$result = false;
  	return $result;
  }
    
// END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
  }?>