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
 * Budget is the main financial income for expenses
 * Almost all other objects are linked to a given Budget.
 */ 
require_once('_securityCheck.php');
class BudgetMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idBudgetType;
  public $idBudgetOrientation;
  public $idBudgetCategory;
  public $idUser;
  public $creationDate;
  public $lastUpdateDateTime;
  public $articleNumber;
  public $idOrganization;
  public $idClient;
  public $clientCode;
  public $idBudget;
  public $idSponsor;
  public $idResource;
  public $color;
  public $description;
  public $_sec_Treatment_right;
  public $idStatus;
  public $elementary;
  public $isUnderConstruction=1;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $_sec_subBudgets;
  public $_spe_subBudgets;
  public $_sec_Progress_center;
  public $_tab_2_1=array('startDate','endDate','budgetDates');
  public $budgetStartDate;
  public $budgetEndDate;
  public $bbs;
  public $bbsSortable;
  public $_tab_2_13=array('untaxedAmount','fullAmount','targetAmount','estimateAmount','initialAmount','update1Amount','update2Amount','update3Amount','update4Amount','updatedAmount','engagedAmount','availableAmount','billedAmount','leftAmount','availableTransferedAmount');
  public $targetAmount;
  public $targetFullAmount;
  public $plannedAmount;
  public $plannedFullAmount;
  public $initialAmount;
  public $initialFullAmount;
  public $update1Amount;
  public $update1FullAmount;
  public $update2Amount;
  public $update2FullAmount;
  public $update3Amount;
  public $update3FullAmount;
  public $update4Amount;
  public $update4FullAmount;
  public $actualAmount;
  public $actualFullAmount;
  //public $actualSubAmount;
  //public $actualSubFullAmount;
  public $usedAmount;
  public $usedFullAmount;
  public $availableAmount;
  public $availableFullAmount;
  public $billedAmount;
  public $billedFullAmount;
  public $leftAmount;
  public $leftFullAmount;
  public $availableTransferedAmount;
  public $availableTransferedFullAmount;
  public $_sec_ExpenseBudgetDetail;
  public $_spe_ExpenseBudgetDetail;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();

  public static $_consolidate=false;
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="bbsSortable" formatter="sortableFormatter" width="5%" >${bbs}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="nameBudgetOrientation" width="10%" >${idBudgetOrientation}</th>
    <th field="nameBudgetType" width="10%" >${idBudgetType}</th>
    <th field="articleNumber" width="10%" >${articleNumber}</th>
    <th field="actualAmount" width="8%" formatter="costFormatter">${updatedAmount}</th>
    <th field="usedAmount" width="8%" formatter="costFormatter">${engagedAmount}</th>
    <th field="availableAmount" width="8%" formatter="costFormatter">${availableAmount}</th>
    <th field="billedAmount" width="8%" formatter="costFormatter">${billedAmount}</th>
    <th field="leftAmount" width="8%" formatter="costFormatter">${leftAmount}</th>
    ';
  
  private static $_fieldsTooltip = array(
  );  

  private static $_fieldsAttributes=array("name"=>"required",      
                                  "done"=>"nobr",
                                  "handled"=>"hidden",
                                  "handledDate"=>"hidden",
                                  "idle"=>"nobr",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "idBudgetType"=>"required",
                                  "idStatus"=>"required",
                                  "lastUpdateDateTime"=>"hidden",
                                  "bbs"=>"display,noImport", 
                                  "bbsSortable"=>"hidden,noImport",
                                  "elementary"=>"readonly",
                                  "actualAmount"=>"readonly,noimport",
                                  "actualFullAmount"=>"readonly,noimport",
                                  "actualSubAmount"=>"hidden",
                                  "actualSubFullAmount"=>"hidden",
                                  "billedAmount"=>"readonly,noimport",
                                  "billedFullAmount"=>"readonly,noimport",
                                  "availableAmount"=>"readonly,noimport",
                                  "availableFullAmount"=>"readonly,noimport",
                                  "usedAmount"=>"readonly,noimport",
                                  "usedFullAmount"=>"readonly,noimport",
                                  "leftAmount"=>"readonly,noimport",
                                  "leftFullAmount"=>"readonly,noimport",
                                  "targetFullAmount"=>"hidden",
                                  "targetAmount"=>"hidden",
                                  "availableTransferedAmount"=>"calculated,readonly,noimport",
                                  "availableTransferedFullAmount"=>"calculated,readonly,noimport"
  );   
 
  private static $_colCaptionTransposition = array('idResource'=>'manager',
   'idBudget'=> 'isSubBudget',
   'done'=>'approved',
   'doneDate'=>'dateApproved',
   'idUser'=>'issuer',
   'plannedAmount'=>'estimateAmount',
   'plannedFullAmount'=>'estimateFullAmount',
   'actualAmount'=>'updatedAmount',
   'actualFullAmount'=>'updatedFullAmount',
   'usedAmount'=>'engagedAmount',
   'usedFullAmount'=>'engagedFullAmount',
   'elementary'=>'isBudgetItem'
  );
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
  	parent::__construct($id,$withoutDependentObjects);
  	if (!$this->id) {
  	  $year=(date('md')<'0701')?date('Y'):date('Y')+1;
  	  $this->budgetStartDate=$year.'-01-01';
  	  $this->budgetEndDate=$year.'-12-31';
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
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
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    if (substr($colName,-6)=='Amount') {
    	$colScript .= '<script type="dojo/connect" event="onChange" >';
    	$colScript.="    var actual=0;";  
    	if ($colName=='plannedAmount') {
    	  $colScript.="  if (this.value && ! dijit.byId('initialAmount').get('value')) dijit.byId('initialAmount').set('value',this.value);";
    	}
    	if ($colName=='plannedFullAmount') {
    	  $colScript.="  if (this.value && ! dijit.byId('initialFullAmount').get('value')) dijit.byId('initialFullAmount').set('value',this.value);";
    	}
    	 
    	$colScript.="    if (dijit.byId('initialAmount').get('value')) actual+=dijit.byId('initialAmount').get('value');";
    	$colScript.="    if (dijit.byId('update1Amount').get('value')) actual+=dijit.byId('update1Amount').get('value');";
    	$colScript.="    if (dijit.byId('update2Amount').get('value')) actual+=dijit.byId('update2Amount').get('value');";
    	$colScript.="    if (dijit.byId('update3Amount').get('value')) actual+=dijit.byId('update3Amount').get('value');";
    	$colScript.="    if (dijit.byId('update4Amount').get('value')) actual+=dijit.byId('update4Amount').get('value');";
    	$colScript.="    dijit.byId('actualAmount').set('value',actual);";
    	$colScript.="    var available=actual;";
    	$colScript.="    if (dijit.byId('usedAmount').get('value')) available-=dijit.byId('usedAmount').get('value');";
    	$colScript.="    dijit.byId('availableAmount').set('value',available);";
    	$colScript.="    var left=actual;";
    	$colScript.="    if (dijit.byId('billedAmount').get('value')) left+=dijit.byId('billedAmount').get('value');";
    	$colScript.="    dijit.byId('leftAmount').set('value',left);";
    	$colScript.="    var actualFull=0;";
    	$colScript.="    if (dijit.byId('initialFullAmount').get('value')) actualFull+=dijit.byId('initialFullAmount').get('value');";
    	$colScript.="    if (dijit.byId('update1FullAmount').get('value')) actualFull+=dijit.byId('update1FullAmount').get('value');";
    	$colScript.="    if (dijit.byId('update2FullAmount').get('value')) actualFull+=dijit.byId('update2FullAmount').get('value');";
    	$colScript.="    if (dijit.byId('update3FullAmount').get('value')) actualFull+=dijit.byId('update3FullAmount').get('value');";
    	$colScript.="    if (dijit.byId('update4FullAmount').get('value')) actualFull+=dijit.byId('update4FullAmount').get('value');";
    	$colScript.="    dijit.byId('actualFullAmount').set('value',actualFull);";
    	$colScript.="    var availableFull=actualFull;";
    	$colScript.="    if (dijit.byId('usedFullAmount').get('value')) availableFull-=dijit.byId('usedFullAmount').get('value');";
    	$colScript.="    dijit.byId('availableFullAmount').set('value',availableFull);";
    	$colScript.="    var leftFull=actualFull;";
    	$colScript.="    if (dijit.byId('billedFullAmount').get('value')) left+=dijit.byId('billedFullAmount').get('value');";
    	$colScript.="    dijit.byId('leftFullAmount').set('value',leftFull);";
    	$colScript .= '  formChanged();';
    	$colScript .= '</script>';
    } else if ($colName=='done') {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
    	$colScript .="    if (this.checked) dijit.byId('isUnderConstruction').set('checked',false);";
      $colScript .= '</script>';
    }
    return $colScript;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subBudgets => presents sub-Budgets as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){ 	
    $result="";
    if ($item=='subBudgets') {
      $label = "<td class='label' valign='top'><label>" . i18n('subBudgets') . "&nbsp;:&nbsp;</label></td>";
      if(isNewGui()){
        $label = "<td width='30px'></td>";
      }
      $result .="<table><tr>$label";
      $result .="<td style='padding-top:5px;'>";
      if ($this->elementary) {
        $result .=i18n('isBudgetItemMsg');
      }
      if ($this->id) {
        $result .= $this->drawSubBudgets();
      }
      $result .="</td></tr></table>";
      return $result;
    }
    if($item == 'ExpenseBudgetDetail'){
      drawExpenseBudgetDetail($this);
    }
  }
  

  /** =========================================================================
   * Specific function to draw a recursive tree for subBudgets
   * @return string the html table for the given level of subBudgets
   *  must be redefined in the inherited class
   */  
  public function drawSubBudgets($recursiveCall=false) {
    global $outMode;
    $result="";
    
 	  $subList=SqlList::getListWithCrit('Budget',array('idBudget'=>$this->id));
    $result .='<table style="width: 100%;" >';
    if (count($subList)>0) {
      foreach ($subList as $idBdg=>$nameBdg) {
      	$bdg=new Budget($idBdg,true);
      	$iconBudget  = '<img src="css/images/iconList16.png" height="16px" />';
      	$padding="";
      	if(isNewGui()){
      	  $iconBudget = '<div class="iconProject16 iconBudget iconSize16 imageColorNewGuiNoSelection"></div>';
      	  $padding="padding-bottom:5px;"; 
      	}
        $result .='<tr><td valign="top" width="20px">'.$iconBudget.'</td>';
        //$result .= '<td style="#AAAAAA;" NOWRAP><div class="'.(($outMode=='html' or $outMode=='pdf')?'':'display').'" style="width: 100%;">' . htmlEncode($bdg->name) . '</div>';
        $clickEvent=' onClick=\'gotoElement("Budget","' . htmlEncode($bdg->id) . '");\' ';
        if ($outMode=='html' or $outMode=='pdf') $clickEvent='';
        
        $result .= '<td><div ' . $clickEvent . ' class="  '.(isNewGui()?' link ':'').(($outMode=='html' or $outMode=='pdf')?'':'menuTree').'" style="'.$padding.' width:100%;color:black">';
        $result .= htmlEncode($bdg->name);
        $ttc=(Parameter::getGlobalParameter('ImputOfAmountProvider')=='TTC')?true:false;
        $amount=($ttc)?$bdg->actualFullAmount:$bdg->actualAmount;       
        if ( ($ttc and $bdg->actualFullAmount) or ( !$ttc and $bdg->actualAmount) ) $result.='<div style="float:right">&nbsp;&nbsp;&nbsp;<i>('.htmlDisplayCurrency($amount).')</i></div>';
        $result .= '</div>';
        $result .= $bdg->drawSubBudgets(true);
        $result .= '</td></tr>';
      }
    }
    $result .='</table>';
   return $result;
  }
  
  public function getSubBudgetFlatList($showIdle=false) {
    if (!$this->id) return array();
    $sub=SqlList::getListWithCrit('Budget',array('idBudget'=>$this->id),'name',null,$showIdle);
    foreach ($sub as $budId=>$budName) {
      $bud=new Budget($budId,true);
      $subList=$bud->getSubBudgetFlatList();
      $sub=array_merge_preserve_keys($sub,$subList);
    }
    return $sub;
  }
  public function getParentsFlatList() {
    $res=array();
    if ($this->idBudget) {
      $parent=new Budget($this->idBudget);
      $res[$parent->id]=$parent->name;
      if ($parent->idBudget) {
        $res=array_merge_preserve_keys($res,$parent->getParentsFlatList());
      }
    }
    return $res;
  }
   /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {	
    $old=$this->getOld();
    if(SqlList::getFieldFromId("Status", $this->idStatus, "setHandledStatus")!=0) {
      $this->isUnderConstruction=0;
    } 
    $bud=new Budget();
    $budList=($this->id)?$bud->getSqlElementsFromCriteria(array('idBudget'=>$this->id)):array();
    $this->elementary=(count($budList)==0)?1:0;
    if (! $this->elementary) {
      $this->usedAmount=0;
      $this->usedFullAmount=0;
      $this->billedAmount=0;
      $this->billedFullAmount=0;
      //$this->actualSubAmount=0;
      //$this->actualSubFullAmount=0;
      $this->plannedAmount=0;
      $this->plannedFullAmount=0;
      $this->initialAmount=0;
      $this->initialFullAmount=0;
      $this->update1Amount=0;
      $this->update1FullAmount=0;
      $this->update2Amount=0;
      $this->update2FullAmount=0;
      $this->update3Amount=0;
      $this->update3FullAmount=0;
      $this->update4Amount=0;
      $this->update4FullAmount=0;
      foreach ($budList as $bud) {
        if ($bud->cancelled==1) continue;
        //$this->actualSubAmount+=$bud->actualAmount;
        //$this->actualSubFullAmount+=$bud->actualFullAmount;
        if ($bud->actualAmount or $bud->initialAmount or $bud->plannedAmount) {
          $this->usedAmount+=$bud->usedAmount;
          $this->billedAmount+=$bud->billedAmount;
          $this->plannedAmount+=$bud->plannedAmount;
          $this->initialAmount+=$bud->initialAmount;
          $this->update1Amount+=$bud->update1Amount;
          $this->update2Amount+=$bud->update2Amount;
          $this->update3Amount+=$bud->update3Amount;
          $this->update4Amount+=$bud->update4Amount;
        }
        if ($bud->actualFullAmount or $bud->initialFullAmount or $bud->plannedFullAmount) {
          $this->usedFullAmount+=$bud->usedFullAmount;
          $this->billedFullAmount+=$bud->billedFullAmount;
          $this->plannedFullAmount+=$bud->plannedFullAmount;
          $this->initialFullAmount+=$bud->initialFullAmount;
          $this->update1FullAmount+=$bud->update1FullAmount;
          $this->update2FullAmount+=$bud->update2FullAmount;
          $this->update3FullAmount+=$bud->update3FullAmount;
          $this->update4FullAmount+=$bud->update4FullAmount;
        }
      }
      if (!$this->update1Amount) $this->update1Amount=null;
      if (!$this->update1FullAmount) $this->update1FullAmount=null;
      if (!$this->update2Amount) $this->update2Amount=null;
      if (!$this->update2FullAmount) $this->update2FullAmount=null;
      if (!$this->update3Amount) $this->update3Amount=null;
      if (!$this->update3FullAmount) $this->update3FullAmount=null;
      if (!$this->update4Amount) $this->update4Amount=null;
      if (!$this->update4FullAmount) $this->update4FullAmount=null;
    }
    $this->actualAmount=$this->initialAmount
      +$this->update1Amount
      +$this->update2Amount
      +$this->update3Amount
      +$this->update4Amount;
    $this->actualFullAmount=$this->initialFullAmount
      +$this->update1FullAmount
      +$this->update2FullAmount
      +$this->update3FullAmount
      +$this->update4FullAmount;
    if ($this->elementary) {
      $exp=new Expense();
      $expList=($this->id)?$exp->getSqlElementsFromCriteria(array('idBudgetItem'=>$this->id,'cancelled'=>'0')):array();
      $this->usedAmount=0;
      $this->usedFullAmount=0;
      $this->billedAmount=0;
      $this->billedFullAmount=0;
      foreach ($expList as $exp) {
        if ($this->actualAmount or !$this->actualFullAmount) {
          $this->usedAmount+=$exp->plannedAmount;
          $this->billedAmount+=$exp->realAmount;
        }
        if ($this->actualFullAmount or !$this->actualAmount) {
          $this->usedFullAmount+=$exp->plannedFullAmount;
          $this->billedFullAmount+=$exp->realFullAmount;
        }
      }
    }
    $this->availableAmount=$this->actualAmount-$this->usedAmount;
    $this->availableFullAmount=$this->actualFullAmount-$this->usedFullAmount;
    $this->leftAmount=$this->actualAmount-$this->billedAmount;
    $this->leftFullAmount=$this->actualFullAmount-$this->billedFullAmount;
    
    if (!$this->initialAmount) $this->initialAmount=null;
    if (!$this->initialFullAmount) $this->initialFullAmount=null;
    if (!$this->plannedAmount) $this->plannedAmount=null;
    if (!$this->plannedFullAmount) $this->plannedFullAmount=null;
    if (!$this->usedAmount) $this->usedAmount=null;
    if (!$this->usedFullAmount) $this->usedFullAmount=null;
    if (!$this->billedAmount) $this->billedAmount=null;
    if (!$this->billedFullAmount) $this->billedFullAmount=null;
    if (!$this->actualAmount) $this->actualAmount=null;
    if (!$this->actualFullAmount) $this->actualFullAmount=null;
    if (!$this->availableAmount) $this->availableAmount=null;
    if (!$this->availableFullAmount) $this->availableFullAmount=null;
    if (!$this->leftAmount) $this->leftAmount=null;
    if (!$this->leftFullAmount) $this->leftFullAmount=null;
    // CALCULATE WBS
    if (Budget::$_consolidate==true) {
      $result = parent::saveForced();
    } else {
      $result = parent::save();
    }
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }   
    // UPDATE PARENTS (recursively)
    if ($this->idBudget) {
      $parent=new Budget($this->idBudget);
      $parent->save();
    }
    if ($old->idBudget and $old->idBudget!=$this->idBudget) {
      $parent=new Budget($old->idBudget);
      $parent->save();
    }
    if (!$this->bbs or !$old->id or $old->idBudget!=$this->idBudget) {
      $parent=new Budget($this->idBudget);
      $parent->regenerateBbsLevel();
      if ($old->id and $this->idBudget!=$old->idBudget) {
        $parent=new Budget($old->idBudget);
        $parent->regenerateBbsLevel();
      }
    }
    //gautier #4400
    if ($old->isUnderConstruction!=$this->isUnderConstruction
     or $old->done!=$this->done
     or $old->idle!=$this->idle
     or $old->cancelled!=$this->cancelled) {
      $listSubProject = $this->getSubBudgetFlatList(true);
      foreach ($listSubProject as $idSub=>$sub){
        $budg=new Budget($idSub);
        $budg->isUnderConstruction = $this->isUnderConstruction;
        $budg->done = $this->done;
        $budg->doneDate = $this->doneDate;
        $budg->idle = $this->idle;
        $budg->idleDate = $this->idleDate;
        $budg->cancelled = $this->cancelled;
        $budg->save();
      }
    }
    return $result; 
  }
  public function simpleSave() {
    return parent::save();
  }
  public function delete() {
  	$result = parent::delete();
  	if ($this->idBudget) {
    	$parent=new Budget($this->idBudget);
    	$parent->save();
    	$parent->regenerateBbsLevel();
  	}
    return $result;
  }

  public function regenerateBbsLevel() {
    $bbs=$this->bbs;
    if ($bbs) $bbs.='.';
    else $bbs='';
    $items=$this->getSqlElementsFromCriteria(array('idBudget'=>$this->id),null,null,"coalesce(bbsSortable,'99999') asc");
    $cpt=0;
    foreach($items as $item) {
      $cpt++;
      if ($bbs.$cpt!=$item->bbs) {
        $item->bbs=$bbs.$cpt;
        $item->bbsSortable=formatSortableWbs($item->bbs);
        $item->simpleSave();
        $item->regenerateBbsLevel();
      }
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
    $old=$this->getOld();
    if ($this->id and $this->id==$this->idBudget) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    }
    if ($this->idBudget) {
      // Parent must not have expenses linked
      $exp=new Expense();
      $cpt=$exp->countSqlElementsFromCriteria(array('idBudgetItem'=>$this->idBudget));
      if ($cpt>0) {
        $result.='<br/>' . i18n('errorBudgetWithExpense');
      }
      $parents=$this->getParentsFlatList();
      $sons=$this->getSubBudgetFlatList();
      foreach ($parents as $idParent=>$nameParent) {
        if (isset($sons[$idParent])) {
          $result.='<br/>' . i18n('errorHierarchicLoop');
          break;
        }
      }
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

  
  public function getColor() {
    $color="#777777";
    if ($this->color) {
      $color=$this->color;
    } else if ($this->idBudget) {
      $top=new Budget($this->idBudget);
      $color=$top->getColor();
    }
    return $color;
  }

  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  }
  public function setAttributes() {
    if (! $this->elementary and $this->id) {
      self::$_fieldsAttributes["plannedAmount"]="readonly";
      self::$_fieldsAttributes["plannedFullAmount"]="readonly";
      self::$_fieldsAttributes["initialAmount"]="readonly";
      self::$_fieldsAttributes["initialFullAmount"]="readonly";
      self::$_fieldsAttributes["update1Amount"]="readonly";
      self::$_fieldsAttributes["update1FullAmount"]="readonly";
      self::$_fieldsAttributes["update2Amount"]="readonly";
      self::$_fieldsAttributes["update2FullAmount"]="readonly";
      self::$_fieldsAttributes["update3Amount"]="readonly";
      self::$_fieldsAttributes["update3FullAmount"]="readonly";
      self::$_fieldsAttributes["update4Amount"]="readonly";
      self::$_fieldsAttributes["update4FullAmount"]="readonly";
    }
    if (! $this->elementary) {
    	self::$_fieldsAttributes["_sec_ExpenseBudgetDetail"]='hidden';
    	unset($this->_spe_ExpenseBudgetDetail);
    }
    if ($this->id and !$this->idBudget and $this->isUnderConstruction) {
      self::$_fieldsAttributes["targetFullAmount"]="";
      self::$_fieldsAttributes["targetAmount"]="";
    }
    if (! $this->isUnderConstruction) {
      self::$_fieldsAttributes["plannedAmount"]="readonly";
      self::$_fieldsAttributes["plannedFullAmount"]="readonly";
    }
    if ($this->done) {
      self::$_fieldsAttributes["initialAmount"]="readonly";
      self::$_fieldsAttributes["initialFullAmount"]="readonly";
    }
    // Retreive value for $availableTransferedAmount and $availableTransferedFullAmount 
    //$crit=array("budgetStartDate"=>$this->budgetStartDate, "budgetEndDate"=>$this->budgetEndDate, "elementary"=>"1");

  }
  public function calculateFieldsForDisplay() {
    $critClause="budgetStartDate='$this->budgetStartDate' and budgetEndDate='$this->budgetEndDate' and elementary=1";
    $critClause.=" and bbsSortable like '".substr($this->bbsSortable,0,5)."%'";
    $this->availableTransferedAmount=(-1)*$this->sumSqlElementsFromCriteria('update4Amount', null,$critClause);
    $this->availableTransferedFullAmount=(-1)*$this->sumSqlElementsFromCriteria('update4FullAmount', null,$critClause);
  }
}
?>