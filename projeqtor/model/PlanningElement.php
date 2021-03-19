<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Matthias Nowak : fix to avoid infinite loop in getRecursivePredecessor()
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
 * Planning element is an object included in all objects that can be planned.
 */ 
require_once('_securityCheck.php');
class PlanningElement extends SqlElement {

  public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $refName;
  public $initialStartDate;
  public $validatedStartDate;
  public $validatedStartFraction;
  public $plannedStartDate;
  public $plannedStartFraction;
  public $realStartDate;
  public $initialEndDate;
  public $validatedEndDate;
  public $validatedEndFraction;
  public $plannedEndDate;
  public $plannedEndFraction;
  public $realEndDate;
  public $initialDuration;
  public $validatedDuration;
  public $plannedDuration;
  public $realDuration;
  public $initialWork;
  public $validatedWork;
  public $assignedWork;
  public $plannedWork;
  public $leftWork;
  public $realWork;
  public $progress;
  public $validatedCost;
  public $assignedCost;
  public $plannedCost;
  public $leftCost;
  public $realCost;
  public $expectedProgress;
  public $wbs;
  public $wbsSortable;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $priority;
  public $elementary;
  public $idle;
  public $done;
  public $cancelled;
  public $idPlanningMode;
  public $minimumThreshold;
  public $indivisibility;
  public $_workVisibility;
  public $_costVisibility;
  public $idBill;
  public $validatedCalculated;
  public $validatedExpenseCalculated;
  public $latestStartDate;
  public $latestEndDate;
  public $isOnCriticalPath;
  public $notPlannedWork;
  public $isManualProgress;
  public $surbooked;
  public $fixPlanning;
  public $unitToDeliver;
  public $unitToRealise;
  public $unitRealised;
  public $unitLeft;
  public $unitProgress;
  public $idProgressMode;
  public $unitWeight;
  public $idWeightMode;
  public $color;
  public $revenue;
  public $commandSum;
  public $billSum;
  public $idRevenueMode;
  
  private static $_fieldsAttributes=array(
                                  "id"=>"hidden",
                                  "refType"=>"hidden",
                                  "refId"=>"hidden",
                                  "refName"=>"hidden",
                                  "wbs"=>"display,noImport", 
                                  "wbsSortable"=>"hidden,noImport",
                                  "progress"=>"display,noImport",
                                  "expectedProgress"=>"display,noImport",
                                  "marginWorkPct"=>"display,noImport",
                                  "marginCostPct"=>"display,noImport",
                                  "marginWork"=>"readonly,noImport",
                                  "marginCost"=>"readonly,noImport",
                                  "topType"=>"hidden",
                                  "topId"=>"hidden",
                                  "topRefType"=>"hidden",
                                  "topRefId"=>"hidden",
                                  "idProject"=>"hidden",
                                  "idle"=>"hidden",
                                  "done"=>"hidden",
                                  "cancelled"=>"hidden",
                                  "plannedStartDate"=>"readonly,noImport",
                                  "plannedEndDate"=>"readonly,noImport",
                                  "plannedDuration"=>"readonly,noImport",
                                  "plannedWork"=>"readonly,noImport",
  								                "notPlannedWork"=>"hidden",
                                  "realStartDate"=>"readonly,noImport",
                                  "realEndDate"=>"readonly,noImport",
                                  "realDuration"=>"readonly,noImport",
                                  "realWork"=>"readonly,noImport",
                                  "assignedCost"=>"readonly,noImport",
                                  "realCost"=>"readonly,noImport",
                                  "leftCost"=>"readonly,noImport",
                                  "validatedCost"=>"",
                                  "plannedCost"=>"readonly,noImport",
                                  "elementary"=>"hidden",
                                  "idPlanningMode"=>"hidden",
  								                "idBill"=>"hidden",
  		                            "validatedCalculated"=>"hidden",
                                  "validatedExpenseCalculated"=>"hidden",
                                  "plannedStartFraction"=>"hidden",
                                  "plannedEndFraction"=>"hidden",
                                  "validatedStartFraction"=>"hidden",
                                  "validatedEndFraction"=>"hidden",
                                  "latestStartDate"=>"hidden",
                                  "latestEndDate"=>"hidden",
                                  "isOnCriticalPath"=>"hidden",
                                  "isManualProgress"=>"hidden",
                                  "surbooked"=>"hidden",
                                  "indivisibility"=>"hidden",
                                  "minimumThreshold"=>"hidden",
                                  "fixPlanning"=>"hidden",
                                  "unitToDeliver"=>"hidden,noImport",
                                  "unitToRealise"=>"hidden,noImport",
                                  "unitRealised"=>"hidden,noImport",
                                  "unitLeft"=>"hidden,noImport",
                                  "unitProgress"=>"hidden",
                                  "idProgressMode"=>"hidden",
                                  "unitWeight"=>"hidden",
                                  "_label_weight"=>"hidden",
                                  "idWeightMode"=>"hidden",
                                  "revenue"=>"hidden,noImport",
                                  "commandSum"=>"hidden,noImport",
                                  "billSum"=>"hidden,noImport",
                                  "idRevenueMode"=>"hidden,noImport"
  );   
  
  private static $_predecessorItemsArray = array();
  private static $_successorItemsArray = array();
  
  protected static $staticCostVisibility=null;
  protected static $staticWorkVisibility=null;
  public static $_noDispatch=false;
  public static $_noDispatchArray=array();
  public static $_copiedItems=array();
  public static $_revenueCalculated=array();
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
// GET VALIDATION SCRIPT
// ============================================================================**********
 
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    $rubr=""; $name="";
    $test = 'initial';
    $pos = stripos( $colName, $test);
    if ($pos!==false) { 
      $rubr=$test; $name=substr($colName,$pos+strlen($test));
    } else {
      $test = 'validated';
      $pos = stripos( $colName, $test);
      if ($pos!==false) { 
        $rubr=$test; $name=substr($colName,$pos+strlen($test));
      } else {
        $test = 'planned';
        $pos = stripos( $colName, $test);
        if ($pos!==false) { 
          $rubr=$test; $name=substr($colName,$pos+strlen($test));      
        } else {
          $test = 'real';
          $pos = stripos( $colName, $test);
          if ($pos!==false) { 
            $rubr=$test; $name=substr($colName,$pos+strlen($test));
          }
        }
      }
    }
   
    if ($name=="StartDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (testAllowedChange(this.value)) {';
      $colScript .= '    var startDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'StartDate").get("value");';
      //$colScript .= '    var endDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'EndDate").value;';
      //$colScript .= '    var duration=workDayDiffDates(startDate, endDate);';
      //$colScript .= '    if (duration) dijit.byId("' . get_class($this) . '_' . $rubr . 'Duration").set("value",duration);';
      $colScript .= '    var duration=dijit.byId("' . get_class($this) . '_' . $rubr . 'Duration").get("value");';
      $colScript .= '    var endDate=addWorkDaysToDate(startDate,duration);';
      $colScript .= '    if ((duration || duration===0) && endDate) dijit.byId("' . get_class($this) . '_' . $rubr . 'EndDate").set("value",endDate);';      //$colScript .= '    if (!duration) dijit.byId("' . get_class($this) . '_' . $rubr . 'Duration").set("value",1);';
      $colScript .= '    terminateChange();';
      $colScript .= '    formChanged();';
      $colScript .= '  }';
      $colScript .= '</script>';
    } else if ($name=="EndDate") { // Not to do any more for end date (not managed this way) ???? Reactivted !
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (testAllowedChange(this.value)) {';    
      $colScript .= '    var endDate=this.value;';
      $colScript .= '    var startDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'StartDate").value;';
      $colScript .= '    var duration=workDayDiffDates(startDate, endDate);';
      $colScript .= '    if (endDate && startDate) dijit.byId("' . get_class($this) . '_' . $rubr . 'Duration").set("value",duration);';
//       if ($rubr=="real") {
//         $colScript .= '   if (dijit.byId("idle")) { ';
//         $colScript .= '     if ( endDate!=null && endDate!="") {';
//         $colScript .= '       dijit.byId("idle").set("checked", true);';
//         $colScript .= '     } else {';
//         $colScript .= '       dijit.byId("idle").set("checked", false);';
//         $colScript .= '     }';
//         $colScript .= '   }';
//       }
      $colScript .= '    terminateChange();';
      $colScript .= '    formChanged();';
            $colScript .= '  }';   
      $colScript .= '</script>';
    } else if ($name=="Duration") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var value=dijit.byId("' . get_class($this) . '_' . $rubr . 'Duration");';
      $colScript .= '  if (testAllowedChange(value)) {';
      $colScript .= '    var duration=(value==null || value=="")?"":parseInt(value.get("value"));';
      $colScript .= '    var startDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'StartDate").get("value");';
      $colScript .= '    var endDate=dijit.byId("' . get_class($this) . '_' . $rubr . 'EndDate").get("value");';
      $colScript .= '    if (duration!=null && duration!="") {';
      $colScript .= '      if (startDate!=null && startDate!="") {';
      $colScript .= '        endDate = addWorkDaysToDate(startDate,duration);';
      $colScript .= '        dijit.byId("' . get_class($this) . '_' . $rubr . 'EndDate").set("value",endDate);';
      //$colScript .= '      } else if (endDate!=null){';
      //$colScript .= '        startDate= addworkDaysToDate(endDate,"day", duration * (-1));';
      //$colScript .= '        dijit.byId("' . get_class($this) . '_' . $rubr . 'StartDate").set("value",startDate);';
      $colScript .= '      }';
      $colScript .= '    }';
      $colScript .= '    terminateChange();';
      $colScript .= '    formChanged();';
      $colScript .= '  }';
      $colScript .= '</script>';
    } else if($colName=='indivisibility'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if(this.checked){';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_minimumThreshold").set("required", true);';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_minimumThreshold").set("class", "input required");';
      $colScript .= '  }else{';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_minimumThreshold").set("required", false);';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_minimumThreshold").set("class", "input");';
      $colScript .= '  }';
      $colScript .= '</script>';
    }else if($colName=='idProgressMode'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var widget=dijit.byId("'.$this->refType.'PlanningElement_unitProgress");';
      $colScript .= '  if(this.value==1){';
      $colScript .= '   if (widget) {';
      $colScript .= '     widget.set("readOnly",true);';
      $colScript .= '   }';
      $colScript .= '   if(dijit.byId("'.$this->refType.'PlanningElement_unitToRealise").get("value")!="0"){';
      $colScript .= '     var progress=setUnitProgress();';
      $colScript .= '     dijit.byId("'.$this->refType.'PlanningElement_unitProgress").set("value", progress);';
      $colScript .= '   }';
      $colScript .= '  }else{';
      $colScript .= '   if (widget) {';
      $colScript .= '     widget.set("readOnly",false);';
      $colScript .= '   }';
      $colScript .= '  }';
      $colScript .= '</script>';
    }else if($colName=='idWeightMode'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var widget=dijit.byId("'.$this->refType.'PlanningElement_unitWeight");';
      $colScript .= '  if(this.value==1){';
      $colScript .= '   if (widget) {';
      $colScript .= '     widget.set("readOnly",false);';
      $colScript .= '   }';
      $colScript .= '  }else{';
      $colScript .= '   if (widget) {';
      $colScript .= '     widget.set("readOnly",true);';
      $colScript .= '   }';
      $colScript .= '     if (this.value==3){';
      $colScript .= '       if(dojo.byId("'.$this->refType.'PlanningElement_unitToRealise").value==""){';
      $colScript .= '         dijit.byId("'.$this->refType.'PlanningElement_unitWeight").set("value","0");';
      $colScript .= '       }else{';
      $colScript .= '         dijit.byId("'.$this->refType.'PlanningElement_unitWeight").set("value",dojo.byId("'.$this->refType.'PlanningElement_unitToRealise").value);';
      $colScript .= '       }';
      $colScript .= '     }';
      $colScript .= '  }';
      $colScript .= '</script>';
    }else if($colName=='unitToDeliver'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if(this.value!="0" && dojo.byId("'.$this->refType.'PlanningElement_unitToRealise").value=="0" || dojo.byId("'.$this->refType.'PlanningElement_unitToRealise").value==""){';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_unitToRealise").set("value", this.value);';
      $colScript .= '  }';
      $colScript .= '</script>';
    }else if($colName=='unitToRealise'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if(this.value!="" && dojo.byId("'.$this->refType.'PlanningElement_unitRealised").value!=""){';
      $colScript .= '   var left=this.value-dojo.byId("'.$this->refType.'PlanningElement_unitRealised").value;';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_unitLeft").set("value", left);';
      $colScript .= '  }';
      $colScript .= '   if(dijit.byId("'.$this->refType.'PlanningElement_idProgressMode").get("value")=="1"){';
      $colScript .= '     var progress=setUnitProgress();';
      $colScript .= '     dijit.byId("'.$this->refType.'PlanningElement_unitProgress").set("value", progress);';
      $colScript .= '   }';
      $colScript .= '   if(dijit.byId("'.$this->refType.'PlanningElement_idWeightMode").get("value")=="3"){';
      $colScript .= '     dijit.byId("'.$this->refType.'PlanningElement_unitWeight").set("value", this.value);';
      $colScript .= '   }';      
      $colScript .= '</script>';
    }else if($colName=='unitRealised'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if(this.value!="" && dojo.byId("'.$this->refType.'PlanningElement_unitToRealise").value!=""){';
      $colScript .= '   var left=dojo.byId("'.$this->refType.'PlanningElement_unitToRealise").value-this.value;';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_unitLeft").set("value", left);';
      $colScript .= '  }';
      $colScript .= '   if(dijit.byId("'.$this->refType.'PlanningElement_idProgressMode").get("value")=="1"){';
      $colScript .= '     var progress=setUnitProgress();';
      $colScript .= '     dijit.byId("'.$this->refType.'PlanningElement_unitProgress").set("value", progress);';
      $colScript .= '   }';
      $colScript .= '</script>';
    }else if($colName=='idRevenueMode'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if(this.value ==  2){';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_revenue").set("readOnly",true);';
      $colScript .= '  }else{';
      $colScript .= '   dijit.byId("'.$this->refType.'PlanningElement_revenue").set("readOnly",false);';
      $colScript .= '  }';
      $colScript .= '</script>';
    }
    return $colScript;
  }
  
  /** ==========================================================================
   * Extends save functionality to implement wbs calculation
   * Triggers parent::save() to run defaut functionality in the end.
   * @return the result of parent::save() function
   */
  public function save() {
    $pmName='id'.$this->refType.'PlanningMode';
    if( property_exists($this,$pmName)){
     $this->idPlanningMode = $this->$pmName;
    }
    global $canForceClose;
  	// Get old element (stored in database) : must be fetched before saving
    $old=new PlanningElement($this->id);
    if (! $this->idProject) {
      if ($this->refType=='Project') {
        $this->idProject=$this->refId;
      } else if ($this->refType) {
        $refObj=new $this->refType($this->refId);
        $this->idProject=$refObj->idProject;
      }
    }
    if (! $this->idProject and $this->refType=='Project') {
    	$this->idProject=$this->refId;
    }
    // If done and no work, set up end date
    if (($this->leftWork==0 and $this->realWork==0) or $this->isManualProgress) {
      $refType=$this->refType;
      if ($refType) {
        $refObj=new $refType($this->refId);
        if ($this->done and property_exists($refObj, 'doneDate')) {
          $this->realEndDate=$refObj->doneDate;
          $this->progress=100;
          $this->expectedProgress=100;
        } else {
          $this->realEndDate=null;
          if(!$this->isManualProgress){
            $this->progress=0;
          }
          $this->expectedProgress=0;
        }
        if (property_exists($refObj, 'handled') and property_exists($refObj, 'handledDate') and $this->refType!='Milestone') {
        	if ($refObj->handled) {
        		$this->realStartDate=$refObj->handledDate;
        	} else {
        		$this->realStartDate=null;
        	}
        }
      }
    } else {
      if ($this->realWork==0 and $this->leftWork>0) $this->realStartDate=null;
      if ($this->leftWork==0 and !$this->realEndDate) {
        $ass=new Assignment();
        $critArray=array('refType'=>$this->refType,'refId'=>$this->refId);
        $this->realEndDate=$ass->getMaxValueFromCriteria('realEndDate', $critArray);
      } else if ($this->leftWork>0 and $this->realEndDate and !$canForceClose) {
        $this->realEndDate=null;
      }
      if(!$this->isManualProgress){
    	  $this->progress = ($this->realWork)?round($this->realWork / ($this->realWork + $this->leftWork) * 100):0;
      }
    }
    if ($this->validatedWork!=0) {
      $this->expectedProgress=round($this->realWork / ($this->validatedWork) *100);
      if ($this->expectedProgress>999999) { $this->expectedProgress=999999; }
    } else {
    	if (!$this->expectedProgress) {
    	  $this->expectedProgress=0;
    	}  
    }

    // update topId if needed
    $topElt=null;
    if (! $this->wbs or trim($this->wbs)=='') { //
      $this->topId=null; // Will force redefine $topElt, to be sure to get correct wbs
    }
    if ( (! $this->topId or trim($this->topId)=='') and ( $this->topRefId and trim($this->topRefId)!='') ) {
      $crit=array("refType"=>$this->topRefType, "refId"=>$this->topRefId);
      $topElt=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$crit);
      if ($topElt) {
        $this->topId=$topElt->id;
        //$topElt->elementary=0; // No need, will be done in updateSynthesis        
      }
    }
    
    // calculate wbs
    $dispatchNeeded=false;
    $crit='';
    if (! $this->wbs or trim($this->wbs)=='') {
      $wbs="";
      if ($topElt) {
        $wbs=$topElt->wbs . ".";
        $crit=" topId=" . Sql::fmtId($this->topId);
      } else {
        $crit=" (topId is null) ";
      }
      if ($this->id) {
        $crit.=" and id<>" . Sql::fmtId($this->id);
      }
      $lst=$this->getSqlElementsFromCriteria(null, null, $crit, 'wbsSortable desc');
      if (count($lst)==0) {
        $localSort=1;
      } else {
        if ( !$lst[0]->wbsSortable or $lst[0]->wbsSortable=='') {
          $localSort=1;
        } else {
          $localSort=substr($lst[0]->wbsSortable,-5,5)+1;
        }
      }
      $wbs.=$localSort;
      $this->wbs=$wbs;
      $dispatchNeeded=true;
    }
    $wbsSortable=formatSortableWbs($this->wbs);
    if ($wbsSortable != $this->wbsSortable) {
      $dispatchNeeded=true;
    }
    $this->wbsSortable=$wbsSortable;
    // search for dependant elements
    $crit=" topId=" . Sql::fmtId($this->id);
    $this->elementary=1;
    $cpt=($this->id)?$this->countSqlElementsFromCriteria(null, $crit):0;
    if ($cpt>0) {
      $this->elementary=0;
      if($this->isManualProgress==1){
        $this->isManualProgress = 0;
        $this->realWork = 0;
        $this->leftWork = 0;
        $this->progress = 0;
        $this->expectedProgress = 0;
      }
    } else {
      $this->elementary=1;
      $this->validatedCalculated=0;
      $this->validatedExpenseCalculated=0;
    }

    if (! $this->priority or $this->priority==0) {
      $this->priority=500; // default value for priority
    }
    
    $this->realDuration=workDayDiffDates($this->realStartDate, $this->realEndDate);
    $this->plannedDuration=workDayDiffDates($this->plannedStartDate, $this->plannedEndDate);
    //if (!$this->plannedDuration and $this->validatedDuration) { // Initialize planned duration to validated
      //$this->plannedDuration=$this->validatedDuration;
      //if ($this->plannedStartDate) {
      //  $this->plannedEndDate=addWorkDaysToDate($this->plannedStartDate, $this->plannedDuration);
      //}
    //}
    if ($this->validatedStartDate and $this->validatedEndDate) {
      $this->validatedDuration=workDayDiffDates($this->validatedStartDate, $this->validatedEndDate);
    }
    if ($this->initialStartDate and $this->initialEndDate) {
      $this->initialDuration=workDayDiffDates($this->initialStartDate, $this->initialEndDate);
    }
    if( get_class($this)=='ActivityPlanningElement'){
      if($this->idWorkUnit and $this->idComplexity and $this->quantity){
        $complexityVal = SqlElement::getSingleSqlElementFromCriteria('ComplexityValues', array('idWorkUnit'=>$this->idWorkUnit,'idComplexity'=>$this->idComplexity));
        if($complexityVal->duration){
          $this->validatedDuration = $complexityVal->duration*$this->quantity;
          if($this->validatedStartDate)$this->validatedEndDate = addWorkDaysToDate($this->validatedStartDate, ($this->validatedDuration));
        }
      }
    }
    //
    $consolidateValidated=Parameter::getGlobalParameter('consolidateValidated');
    if ($consolidateValidated=='NO' or ! $consolidateValidated) {
    	$this->validatedCalculated=0;
    	$this->validatedExpenseCalculated=0;
    } else if ($consolidateValidated=='ALWAYS' and ! $this->elementary) {
    	$this->validatedCalculated=1;
    } 
    
    if ($this->realEndDate){
      $this->plannedEndDate=$this->realEndDate;
      $this->plannedDuration=workDayDiffDates($this->plannedStartDate, $this->plannedEndDate);
    }
    if ($this->realStartDate){
    	if ($this->plannedStartDate>$this->realStartDate) $this->plannedStartDate=$this->realStartDate;
    	if ($this->plannedStartDate>$this->plannedEndDate) $this->plannedEndDate=$this->plannedStartDate;
    	$this->plannedDuration=workDayDiffDates($this->plannedStartDate, $this->plannedEndDate);
    }
    
    //gautier
    $ass = new Assignment();
    $crit = array("refType"=>$this->refType,"refId"=>$this->refId);
    $cptAss = $ass->countSqlElementsFromCriteria($crit);
    
    if($this->refType == "Activity"){
      $paramManualProgress=Parameter::getGlobalParameter('isManualProgress');
      if($paramManualProgress=='YES'){
        if($this->refId){
          $lstPlMode = SqlList::getListWithCrit('PlanningMode', array("code" => "FDUR"));
          if( array_key_exists($this->idPlanningMode,$lstPlMode) and $this->elementary == 1 and $cptAss==0){
            $this->isManualProgress = 1;
          }
        }
      }
    }
    if($this->isManualProgress){
      $this->realWork = $this->progress * $this->validatedWork / 100;
      $this->leftWork = $this->validatedWork - $this->realWork;
      $this->plannedWork = $this->realWork+$this->leftWork;
      $this->expectedProgress=$this->progress;
    }
    if( $old->isManualProgress and ($old->idPlanningMode!=$this->idPlanningMode or $cptAss!=0) ){
      $this->isManualProgress = 0;
      $this->progress = 0;
      $this->expectedProgress = 0;
      $this->updateSynthesisObj(true);
    }
    ///florent
    if(Parameter::getGlobalParameter('technicalProgress')=='YES' and ($this->refType=='Project' or $this->refType=='Activity')){
      if($this->refType=='Project' and ($this->idProgressMode=='' or $this->idWeightMode=='')){
        $this->idProgressMode=1;
        $this->idWeightMode=2;
        $this->unitProgress=0;
        $this->unitWeight=0;
      }else if($this->refType=='Activity' and ($this->idProgressMode=='' or $this->idWeightMode=='')){
        $this->idProgressMode=2;
        $this->idWeightMode=1;
        $this->unitProgress=0;
        $this->unitWeight=0;
        $this->unitToDeliver=0;
        $this->unitToRealise=0;
        $this->unitRealised=0;
        $this->unitLeft=0;
      }else{
        if($this->refType=='Activity'){
          $sons=$this->countSonItems();
          if(!$sons and $this->idWeightMode==2){
            $this->idWeightMode=1;
          }
          $this->unitProgress=$this->setUnitProgress();
          $this->unitWeight=$this->setUnitWeight();
        }
        if($this->unitToRealise!='' and $this->unitRealised!=''){
          $this->unitLeft=($this->unitToRealise-$this->unitRealised );
        }
      }
    }
    ///
    
    //end
    $result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    // Update dependant objects
    if ($dispatchNeeded) { // and ! self::$_noDispatch // Criteria removed : must dispatch for move task 
      $crit=" topId=" . Sql::fmtId($this->id);
      $lstElt=$this->getSqlElementsFromCriteria(null, null, $crit ,'wbsSortable asc');
      projeqtor_set_time_limit(600);
      $cpt=0;
      foreach ($lstElt as $elt) {
        $cpt++;
        $elt->wbs=$this->wbs . '.' . $cpt;
        if ($elt->refType) { // just security for unit testing 
          $elt->wbsSave();
        }
      }
    }
    // If project changed, update assignment
    if($old->idProject!=$this->idProject and $cptAss>0) {
      // Project change : update assignment
      Assignment::updateProjectFromPlanningElement($this->refType, $this->refId, $this->idProject);
    }
    // update topObject
    if ($topElt) {
      if ($topElt->refId and $topElt->refType) {
        if (! self::$_noDispatch) {
          // $topElt->save(); // No need to save, will call Update Synthesis   
      	} else {
      	  if ($this->elementary) { // noDispatch (for copy) and elementary : store top in array for Update Synthesis
      	    self::updateSynthesisNoDispatch($topElt->refType, $topElt->refId);
      	  }
      	}
      }
    }
    if ($this->wbsSortable!=$old->wbsSortable) {
      $refType=$this->refType;
      if ($refType=='Project') {
        $refObj=new $refType($this->refId);
        if ($refObj and $refObj->id) {
          $refObj->sortOrder=$this->wbsSortable;
          $subRes=$refObj->saveForced(true);
        }
      }
    }
    // save old parent (for synthesis update) if parent has changed
    if ($old->topId!=$this->topId) {
      if ($old->topId!='') {
        if (! self::$_noDispatch) {
          self::updateSynthesis($old->topRefType, $old->topRefId);
        } else {
          self::updateSynthesisNoDispatch($old->topRefType, $old->topRefId);
        }
      }
      if ($old->id) {
        // Must also renumber children for old parent
        $oldTopElt=new PlanningElement($old->topId);
        projeqtor_set_time_limit(600);
        $critOldTop=($oldTopElt->id)?" topId=" . Sql::fmtId($oldTopElt->id):" topId is null";
        $lstEltOldTop=$this->getSqlElementsFromCriteria(null, null, $critOldTop ,'wbsSortable asc');
        $cpt=0;
        foreach ($lstEltOldTop as $elt) {
          $cpt++;
          $elt->wbs=($oldTopElt->wbs)?$oldTopElt->wbs . '.' . $cpt:$cpt;
          if ($elt->refType) { // just security for unit testing
            $elt->wbsSave();
          }
        }
      }
    }
    if(Module::isModuleActive('moduleGestionCA')){
    	$project = new Project($this->idProject);
    	$projectList = $project->getRecursiveSubProjectsFlatList(true, true);
    	$projectList = array_flip($projectList);
    	$projectList = '(0,'.implode(',',$projectList).')';
    	$where = 'idProject in '.$projectList.' and idle = 0';
    	$paramAmount = Parameter::getGlobalParameter('ImputOfAmountClient');
    	$cmdAmount = ($paramAmount == 'HT')?'totalUntaxedAmount':'totalFullAmount';
    	$command = new Command();
    	$this->commandSum = $command->sumSqlElementsFromCriteria($cmdAmount, null, $where);
    	if(!$this->commandSum)$this->commandSum=0;
    	$billAmount = ($paramAmount == 'HT')?'untaxedAmount':'fullAmount';
    	$bill = new Bill();
    	$this->billSum = $bill->sumSqlElementsFromCriteria($billAmount, null, $where);
    	if(!$this->billSum)$this->billSum=0;
    	$paramCA = Parameter::getGlobalParameter('CaReplaceValidCost');
    	if($paramCA == 'YES' and $this->revenue > 0){
    		$this->validatedCost = $this->revenue;
    	}
    	if($old->idRevenueMode != $this->idRevenueMode and $this->idRevenueMode == 2){
    	  $this->updateRevenue();
    	}
    }
    // save new parent (for synthesis update) if parent has changed
    // #2995 : a previous version changed the following condition so that updateSynthesis is always called for parent
    //         so now calling updateSynthesis for parent in ProjectPlanningElement::updateSynthesisProject is obsolete
    //         and would lead to re-update synthesis several times (as many as project WBS level)
    //         Call in ProjectPlanningElement::updateSynthesisProject has been removed. 
    //         DO NOT CHANGE CONDITION TO PREVIOUS VERSION UNLESS YOU REACTIVATE CALL IN ProjectPlanningElement::updateSynthesisProject
    if ($this->topId!='') { // and ($old->topId!=$this->topId or $old->cancelled!=$this->cancelled)) {
      if (! self::$_noDispatch) {
        if (!isset(self::$_noDispatchArray[$this->topRefType.'#'.$this->topRefId])) {
          self::updateSynthesis($this->topRefType, $this->topRefId);
        }
      } else {
        self::updateSynthesisNoDispatch($this->topRefType, $this->topRefId);
      }
    }
    // remove existing planned work (if any)
    if ($this->idle) {
       $pw=new PlannedWork();
       $crit="refType=".Sql::str($this->refType)." and refId=".$this->refId;
       $pw->purge($crit);
    }
    // set to first handled status on first work input
    //if ($old->realWork==0 and $this->realWork!=0 and $this->refType) {
    if ($old->realWork==0 and $this->realWork!=0 and $this->refType) {
      if (!PlannedWork::$_planningInProgress) $this->setHandledOnRealWork('save');
    }
    // set to first done status on lastt work input (left work = 0)
    if ($old->leftWork!=0 and $this->leftWork==0 and $this->realWork>0 and $this->refType) {
      if (!PlannedWork::$_planningInProgress) $this->setDoneOnNoLeftWork('save');
    }
    if ($old->topId and $old->topId!=$this->topId) { // and ! self::$_noDispatch removed constraitn for move // This renumbering is to avoid holes in numbering // 
    	$pe=new PlanningElement($old->topId);
    	$pe->renumberWbs();
    }
    $pm=new PlanningMode($this->idPlanningMode);
    if ($this->idPlanningMode!=$old->idPlanningMode      // Change Planning Mode
     or $this->topId!=$old->topId                        // Change Top
     or $this->priority!=$old->priority                  // Change priority
     or ( ($pm->code=='REGUL' or $pm->code=='FULL' or $pm->code=='HALF' or $pm->code=='QUART') and ($this->validatedStartDate!=$old->validatedStartDate or $this->validatedEndDate!=$old->validatedEndDate) )
     or ( $pm->code=='ALAP' and $this->validatedEndDate!=$old->validatedEndDate)
     or ( $pm->code=='FIXED' and $this->validatedEndDate!=$old->validatedEndDate)    
     or ( $pm->code=='START' and $this->validatedStartDate!=$old->validatedStartDate)        
        ) {
      if ($this->idProject) Project::setNeedReplan($this->idProject);
    }
    
    //gautier
    if(isset($this->_moveToAfterCreate)){
      $idPlanningElementOrigin= $this->_moveToAfterCreate;
      $peOrigin = new PlanningElement($idPlanningElementOrigin);
        if($this->idProject == $peOrigin->idProject){
          if(property_exists($this->refType, 'idActivity') and property_exists($peOrigin->refType, 'idActivity')){
            $objOrigin= new $peOrigin->refType($peOrigin->refId,true);
            $currentObj = new $this->refType($this->refId,true);
            if($objOrigin->idActivity == $currentObj->idActivity){
              $this->moveTo($idPlanningElementOrigin, 'after');
            }
          }else{
            if($peOrigin->refType != 'Project'){
              $this->moveTo($idPlanningElementOrigin, 'after');
            }
          }
        }
    }
    return $result;
  }
  
  public function setHandledOnRealWork ($action='check') {
    $refType=$this->refType;
    $refObj=new $refType($this->refId);
    $newStatus=null;
    if (property_exists($refObj, 'idStatus') and Parameter::getGlobalParameter('setHandledOnRealWork')=='YES') {
      $st=new Status($refObj->idStatus);
      if (!$st->setHandledStatus) { // if current stauts is not handled, move to first allowed handled status (fitting workflow)
        $typeClass=$refType.'Type';
        $typeField='id'.$typeClass;
        $type=new $typeClass($refObj->$typeField);
        $user=getSessionUser();
        // Is change possible ?
        if (property_exists($type, 'mandatoryResourceOnHandled') and $type->mandatoryResourceOnHandled) { // Resource Mandatroy
          if (property_exists($refObj, 'idResource') and ! $refObj->idResource) { // Resource not set
					  if (! $user->isResource or Parameter::getGlobalParameter('setResponsibleIfNeeded')=='NO') { // Resource will not be set
              // So, cannot change status to handled (responsible needed)
              return '[noResource]';
            } 
          }
        } 
        $crit=array('idWorkflow'=>$type->idWorkflow, 'idStatusFrom'=>$refObj->idStatus, 'idProfile'=>$user->getProfile($this->idProject), 'allowed'=>'1');
        $ws=new WorkflowStatus();
        $possibleStatus=$ws->getSqlElementsFromCriteria($crit);
        $in="(0";
        foreach ($possibleStatus as $ws) {
          $in.=",".$ws->idStatusTo;
        }
        $in.=")";
        $st=new Status();
        $stList=$st->getSqlElementsFromCriteria(null, null, " setHandledStatus=1 and id in ".$in, 'sortOrder asc');
        if (count($stList)>0) {
          if ($action=='save') {
            $refObj->idStatus=$stList[0]->id;
            $resSetStatus=$refObj->save();
          }
          return $stList[0]->name; // Return new status name
        }
      }
    }
    return null; // OK nothing to do
  } 
  public function setDoneOnNoLeftWork($action='check', $simulatedStartStatus=null) {
    $refType=$this->refType;
    $refObj=new $refType($this->refId);
    if (property_exists($refObj, 'idStatus') and Parameter::getGlobalParameter('setDoneOnNoLeftWork')=='YES') {
      $st=null;
      if ($simulatedStartStatus) {
        $st=new Status(SqlList::getIdFromName('Status', $simulatedStartStatus));
      }
      if (! $st or !$st->id) {
        $st=new Status($refObj->idStatus);
      }
      if (!$st->setDoneStatus) { // if current status is not handled, move to first allowed handled status (fitting workflow)
        $typeClass=$refType.'Type';
        $typeField='id'.$typeClass;
        $type=new $typeClass($refObj->$typeField);
        $user=getSessionUser();
        // Is change possible ?
        if (property_exists($type, 'mandatoryResultOnDone') and $type->mandatoryResultOnDone) { // Result Mandatroy
          if (property_exists($refObj, 'result') and !$refObj->result) { // Result not set
            // So, cannot change status to done (result needed)
            return '[noResult]';
          }
        }
        $crit=array('idWorkflow'=>$type->idWorkflow, 'idStatusFrom'=>$refObj->idStatus, 'idProfile'=>$user->getProfile($this->idProject), 'allowed'=>'1');
        $ws=new WorkflowStatus();
        $possibleStatus=$ws->getSqlElementsFromCriteria($crit);
        $in="(0";
        foreach ($possibleStatus as $ws) {
          $in.=",".$ws->idStatusTo;
        }
        $in.=")";
        $st=new Status();
        $stList=$st->getSqlElementsFromCriteria(null, null, " setDoneStatus=1 and id in ".$in, 'sortOrder asc');
        if (count($stList)>0) {
          if ($action=='save') {
            $refObj->idStatus=$stList[0]->id;
            $resSetStatus=$refObj->save();
          }
          return $stList[0]->name;
        }
      }
    }
    return null; // OK nothing to do
  }
  /// florent
  public function setUnitProgress(){
    if($this->idProgressMode=='1'){
      $ref=$this->refType;
      $sons=$this->getSonItemsArray(true);
      if($ref=='Project' and $sons){
        foreach ($sons as $id=>$pe) {
          if ($pe->refType=='Project' and $pe->topRefId==$this->refId) {
            $idprojPlEl[$id]=$pe;
          } else if ($pe->refType=='Activity' and $pe->topRefId==$this->refId) {
            $idActPlEl[$id]=$pe;
          } else {
            continue;
          }
        }
        if (isset($idprojPlEl)) {
          $sons=$idprojPlEl;
        } else if (isset($idActPlEl)) {
          $sons=$idActPlEl;
        } else {
          return 0;
        }
      }else if ($ref=='Project' and !$sons){
        return 0; 
      }else if ($ref=='Activity' and $sons){
        foreach ($sons as $id=>$pe){
          if($pe->topRefId!=$this->refId or $pe->refType!='Activity'){
            unset($sons[$id]);
          }
          continue;
        }
      }
      
      if($ref=='Activity' and !$sons ){
        if($this->unitToRealise!=0){
          $result=(floatval(($this->unitRealised/$this->unitToRealise)*100));
        }else{
          $result=0;
        }
      }else{
        $sumWeight=0;
        $sumProgress=0;
        foreach ($sons as $son ){
          if($son->unitWeight!=0){
            $sumProgress=(floatval($sumProgress+($son->unitProgress*$son->unitWeight)));
            $sumWeight+=$son->unitWeight;
            continue;
          }else{
            continue;
          }
        }
        if($sumWeight==0){
          return 0;
        }
          $result=(floatval((($sumProgress)/($sumWeight))));
      }
    }else{
      $result=$this->unitProgress;
    }
    return $result;
  }
  
  public function setUnitWeight(){
    if($this->idWeightMode==2){
      $refType=$this->refType;
      $summWeight=0;
      $sons=$this->getSonItemsArray(true);
      if($refType=='Project' and $sons){
        foreach ($sons as $id=>$pe){
          if($pe->refType=='Project' and $pe->topRefId==$this->refId){
            $idprojPlEl[$id]=$pe;
          }else if ($pe->refType=='Activity' and $pe->topRefId==$this->refId){
            $idActPlEl[$id]=$pe;
          }else{
            continue;
          }
        }
        if(isset($idprojPlEl)){
          $sons=$idprojPlEl;
        }else if (isset($idActPlEl)){
          $sons=$idActPlEl;
        }else{
          return 0;
        } 
      }else if($refType=='Activity' and $sons){
        foreach ($sons as $id=>$pe){
            if($pe->topRefId!=$this->refId or $pe->refType!='Activity'){
              unset($sons[$id]);
            }
            continue;
        }
      }else if($refType=='Project' and !$sons){
        return 0;
      }
      foreach ($sons as $son){
        if($son->unitWeight!=0){
          $summWeight=$summWeight+$son->unitWeight;
          continue;
        }else{
          continue;
        }
      }
      $result=$summWeight;
    }else if($this->idWeightMode==3){
      $result=$this->unitToRealise;
    }else{
      $result=$this->unitWeight;
    }
    return $result;
  }
  ///
  
  // Save without extra save() feature and without controls
  public function simpleSave() {
    if ($this->plannedStartDate>$this->plannedEndDate) $this->plannedEndDate=$this->plannedStartDate;
    $this->plannedDuration=workDayDiffDates($this->plannedStartDate, $this->plannedEndDate);
    if ($this->validatedStartDate and $this->validatedEndDate) {
    	$this->validatedDuration=workDayDiffDates($this->validatedStartDate, $this->validatedEndDate);
    }
    if ($this->initialStartDate and $this->initialEndDate) {
      $this->initialDuration=workDayDiffDates($this->initialStartDate, $this->initialEndDate);
    }
    if (PlannedWork::$_planningInProgress and $this->id) {
      // Attention, we'll execute direct query to avoid concurrency issues for long duration planning
      // Otherwise, saving planned data may overwrite real work entered on Timesheet for corresponding items.
      $old=$this->getOld();
      $change=false;
      $fields=array('plannedStartDate','plannedStartFraction','plannedEndDate','plannedEndFraction','plannedDuration','latestStartDate','latestEndDate','isOnCriticalPath','notPlannedWork','surbooked');
      if (property_exists($this,'_profile') and $this->_profile=='RECW' and $this->assignedWork!=$old->assignedWork) {
        $extraFields=array('assignedWork','assignedCost','leftWork','leftCost','plannedWork','plannedCost','progress');
        $fields=array_merge($fields,$extraFields);
        $this->plannedWork=$this->leftWork+$old->realWork;
        $this->plannedCost=$this->leftCost+$old->realCost;
        $this->progress=(($this->plannedWork)?round($old->realWork/($this->plannedWork)*100):0);
      }
      $this->plannedDuration=workDayDiffDates($this->plannedStartDate, $this->plannedEndDate);
      $query="UPDATE ".$this->getDatabaseTableName(). " SET ";
      foreach($fields as $field) {
        if (substr($field,-4)!='Date') {
          $newVal=floatval($this->$field);
          $oldVal=floatval($old->$field);
        } else {
          $newVal=$this->$field;
          $oldVal=$old->$field;
        }
        if ( strval($newVal) != strval($oldVal) ) {
          if ($change) $query.=',';
          if ($newVal===null or $newVal==='') {
            $query.=" $field=null ";
          } else if (substr($field,-4)=='Date') {
            $query.=" $field='".$newVal."' ";
          } else {
            $query.=" $field=".$newVal;
          }
          $change=true;
          History::store($this, $this->refType, $this->refId, 'update', $field, $oldVal, $newVal);
        }
      }
      $query.=" WHERE id=$this->id";
      if ($change) {
        Sql::query($query);
      }
      $result="OK";
    } else {
      $result = parent::saveForced();
    }
    if ($this->refType=='Project') {
      KpiValue::calculateKpi($this);
    }
    return $result;
  }

  public function wbsSave($withSubItems=true) {
  	$this->_noHistory=true;
  	$this->wbsSortable=formatSortableWbs($this->wbs);
  	$resTmp=$this->saveForced();
  	if ($this->refType=='Project') {
  		$proj=new Project($this->refId); 
  		$proj->sortOrder=$this->wbsSortable;
  		$resSaveProj=$proj->saveForced();
  	} 
  	if (!$withSubItems) return;
  	//if (self::$_noDispatch) return;
  	$crit=" topId=" . Sql::fmtId($this->id);
  	$lstElt=$this->getSqlElementsFromCriteria(null, null, $crit ,'wbsSortable asc');
  	$cpt=0;
  	foreach ($lstElt as $elt) {
  		$cpt++;
  		$elt->wbs=$this->wbs . '.' . $cpt;
  		if ($elt->refType) { // just security for unit testing
  			$elt->wbsSave();
  		}
  	}
  }
  
    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
   /** =========================================================================
   * Update the synthesis Data (work).
   * Called by sub-element (assignment, ...) 
   * @param $col the nale of the property
   * @return a boolean 
   */
  protected function updateSynthesisObj ($doNotSave=false) {
    $consolidateValidated=Parameter::getGlobalParameter('consolidateValidated');	
    $technicalProgress=Parameter::getGlobalParameter('technicalProgress');  	
    $this->validatedCalculated=0;
  	$this->validatedExpenseCalculated=0;
    $assignedWork=0;
    $leftWork=0;
    $plannedWork=0;
    $notPlannedWork=0;
    $realWork=0;
    $validatedWork=0;
    $assignedCost=0;
    $leftCost=0;
    $plannedCost=0;
    $realCost=0;
    $validatedCost=0;
    $validatedExpense=0;
    //$this->_noHistory=true; // Should keep history of changes
    $this->_workHistory=true; // History will be tagged in order to select visibility
    // Add data from assignments directly linked to this item
    $critAss=array("refType"=>$this->refType, "refId"=>$this->refId);
    $assignment=new Assignment();
    $assList=$assignment->getSqlElementsFromCriteria($critAss, false);
    if ($this->refType=='PeriodicMeeting') {
    	$assList=array();
    }
    $realStartDate=null;
    $realEndDate=null;
    $plannedStartDate=null;
    $plannedEndDate=null;
    foreach ($assList as $ass) {
    	$assignedWork+=$ass->assignedWork;
      $leftWork+=$ass->leftWork;
      $plannedWork+=$ass->plannedWork;
      $notPlannedWork+=$ass->notPlannedWork;
      $realWork+=$ass->realWork;
      if ($ass->assignedCost) $assignedCost+=$ass->assignedCost;
      if ($ass->leftCost) $leftCost+=$ass->leftCost;
      if ($ass->plannedCost) $plannedCost+=$ass->plannedCost;
      if ($ass->realCost) $realCost+=$ass->realCost;
      if ( $ass->realStartDate and (! $realStartDate or $ass->realStartDate<$realStartDate )) {
        $realStartDate=$ass->realStartDate;
      }
      if ( $ass->realEndDate and (! $realEndDate or $ass->realEndDate>$realEndDate )) {
        $realEndDate=$ass->realEndDate;
      }
      if ( $ass->plannedStartDate and (! $plannedStartDate or $ass->plannedStartDate<$plannedStartDate )) {
        $plannedStartDate=$ass->plannedStartDate;
      }
      if ( $ass->plannedEndDate and (! $plannedEndDate or $ass->plannedEndDate>$plannedEndDate )) {
        $plannedEndDate=$ass->plannedEndDate;
      }      
    }
    /// florent
    if($technicalProgress=='YES' and ($this->refType=='Project' or $this->refType=='Activity')){
      if($this->refType=='Project' and ($this->idProgressMode=='' or $this->idWeightMode=='')){
        $this->idProgressMode=1;
        $this->idWeightMode=2;
      }else if($this->refType=='Activity' and ($this->idProgressMode=='' or $this->idWeightMode=='')) {
        $this->idProgressMode=2;
        $this->idWeightMode=1;
        if($this->countSonItems()){
          $this->idProgressMode=1;
          $this->idWeightMode=2;
        }
      }
      if($this->idWeightMode==3){
        $this->unitWeight=0;
        $this->idWeightMode=2;
      }else if ($this->idWeightMode==1 and $this->unitWeight==0){
        $this->idWeightMode=2;
      }
      $this->idProgressMode=1;
      $this->unitProgress=$this->setUnitProgress();
      $this->unitWeight=$this->setUnitWeight();
    }
    if(Module::isModuleActive('moduleGestionCA')){
      if(($this->refType=='Project' or $this->refType=='Activity') and $this->idRevenueMode==''){
        $this->idRevenueMode = 2;
      }
      if($this->refType=='Project' and $this->id and $this->idRevenueMode!= 2) {
        $countSub=$this->countSqlElementsFromCriteria(array('topId'=>$this->id,'refType'=>'Project'));
        if ($countSub>0) $this->idRevenueMode = 2;
      }
      $this->updateRevenue();
    }
    ///
    // Add data from other planningElements dependant from this one
    $critPla=array("topId"=>$this->id);
    $planningElement=new PlanningElement();
    $plaList=$planningElement->getSqlElementsFromCriteria($critPla, false);
    // Add data from other planningElements dependant from this one
    $this->elementary=(count($plaList)==0)?1:0;
    foreach ($plaList as $pla) {
      $assignedWork+=$pla->assignedWork;
      $leftWork+=$pla->leftWork;
      $plannedWork+=$pla->plannedWork;
      $notPlannedWork+=$pla->notPlannedWork;
      $realWork+=$pla->realWork;
      if (!$pla->cancelled and $pla->assignedCost) $assignedCost+=$pla->assignedCost;
      if (!$pla->cancelled and $pla->leftCost) $leftCost+=$pla->leftCost;
      if ($pla->plannedCost) $plannedCost+=$pla->plannedCost;
      if ($pla->realCost) $realCost+=$pla->realCost;
      if ( !$pla->cancelled and $pla->realStartDate and (! $realStartDate or $pla->realStartDate<$realStartDate )) {
        $realStartDate=$pla->realStartDate;
      }
      if ( !$pla->cancelled and $pla->realEndDate and (! $realEndDate or $pla->realEndDate>$realEndDate )) {
        $realEndDate=$pla->realEndDate;
      }  
      if ( !$pla->cancelled and $pla->plannedStartDate and (! $plannedStartDate or $pla->plannedStartDate<$plannedStartDate )) {
        $plannedStartDate=$pla->plannedStartDate;
      }
      if ( !$pla->cancelled and $pla->plannedEndDate and (! $plannedEndDate or $pla->plannedEndDate>$plannedEndDate )) {
        $plannedEndDate=$pla->plannedEndDate;
      }  
      // If realEnd calculated, but left task with no work, keep real not set
      if ($realEndDate and !$pla->realEndDate and $pla->assignedWork==0 and $pla->leftWork==0 and $pla->plannedEndDate>$realEndDate) {
        $realEndDate="";
      }
      if($this->refType=='Project'){
        $proj=new Project($this->idProject);
        if($proj->commandOnValidWork != 1){
          if (!$pla->cancelled and $pla->validatedWork) $validatedWork+=$pla->validatedWork;
          if (!$pla->cancelled and $pla->validatedCost) $validatedCost+=$pla->validatedCost;
        }
      }else{
        if (!$pla->cancelled and $pla->validatedWork) $validatedWork+=$pla->validatedWork;
        if (!$pla->cancelled and $pla->validatedCost) $validatedCost+=$pla->validatedCost;
      }
    }
    $this->realStartDate=$realStartDate;
    if ($realWork>0 or $leftWork>0) {
      if ($leftWork==0) {
        $this->realEndDate=$realEndDate;
      } else {
        $this->realEndDate=null;
      }
    }
    if ($plannedStartDate) {$this->plannedStartDate=$plannedStartDate;}
    if ($this->elementary and $plannedStartDate and $realStartDate and $realStartDate<$plannedStartDate) {
      $this->plannedStartDate=$realStartDate;
    }
    if ($plannedEndDate) {$this->plannedEndDate=$plannedEndDate;}
    // save cumulated data
    $this->assignedWork=$assignedWork;
    $this->leftWork=$leftWork;
    $this->plannedWork=$plannedWork;
    $this->notPlannedWork=$notPlannedWork;
    $this->realWork=$realWork;
    $this->assignedCost=$assignedCost;
    $this->leftCost=$leftCost;
    $this->plannedCost=$plannedCost;
    $this->realCost=$realCost;
    if (! $this->elementary) {
      if ($consolidateValidated=="ALWAYS") {
      	$this->validatedWork=$validatedWork;
      	$this->validatedCost=$validatedCost;
      	$this->validatedCalculated=1;
      } else if ($consolidateValidated=="IFSET") {
      	if ($validatedWork) {
      		$this->validatedWork=$validatedWork;
      		$this->validatedCalculated=1;
      	}
      	if ($validatedCost) {
      		$this->validatedCost=$validatedCost;
      		$this->validatedCalculated=1;
      	}
      } 
    }
    if (! $doNotSave) {
	    return $this->save();
	    // Dispath to top element
	    // #2995 : a previous version changed the condition in save() in PlanningElement so that updateSynthesis is always called for parent
	    //         so now calling updateSynthesis for parent in ProjectPlanningElement::updateSynthesisProject is obsolete
	    //         and would lead to re-update synthesis several times (as many as project WBS level)
	    //         Call in ProjectPlanningElement::updateSynthesisProject has been removed.
	    //         DO NOT CHANGE CONDITION IN PLANNINGELEMENT::SAVE() UNLESS YOU REACTIVATE CALL HERE
	    //if ($this->topId) {
	    //    self::updateSynthesis($this->topRefType, $this->topRefId);
	    //}
    }
  }
  
   /** =========================================================================
   * Update the synthesis Data (work).
   * Called by sub-element (assignment, ...) 
   * @param $col the nale of the property
   * @return a boolean 
   */
  public static function updateSynthesis ($refType, $refId) { 
  	if (!$refType or !$refId) return;
    $crit=array("refType"=>$refType, "refId"=>$refId);
    $obj=SqlElement::getSingleSqlElementFromCriteria($refType.'PlanningElement', $crit);
    if (! $obj or ! $obj->id) {
      $obj=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
    }
    if ($obj and $obj->id) {
    	$method='updateSynthesis'.$refType;
    	if (method_exists($obj,$method )) {
    		return $obj->$method();
    	} else {
        return $obj->updateSynthesisObj();
    	}
    }
  } 
  
    /**
   * Delete object 
   * @see persistence/SqlElement#save()
   */
  public function delete() { 
    $refType=$this->topRefType;
    $refId=$this->topRefId;
    $result = parent::delete();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    $topElt=null;
    if ( $refId and trim($refId)!='') {
      $crit=array("refType"=>$refType, "refId"=>$refId);
      $topElt=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$crit);
      if ($topElt  and $topElt->id) {
      	if ($topElt->refId) {
          $topElt->save();
      	}
        if (!PlanningElement::$_noDispatch) self::updateSynthesis($refType, $refId);          
      }
    }
    if ($this->topId) { // This renumbering is to avoid holes in numbering
      $pe=new PlanningElement($this->topId);
      $pe->renumberWbs();
    }
    //krowry
    if (! PlanningElement::$_noDispatch) { // if noDispatch, we are deleting project, so do not try and create dependency that will be removed
      $dep=new Dependency();
      $critPredecessor=array('successorRefId'=>$this->refId,'successorRefType'=>$this->refType);
      //$critPredecessor=array('successorId'=>$this->id); // Alternative
      $lp=$dep->getSqlElementsFromCriteria($critPredecessor);
      $critSuccessor=array('predecessorRefId'=>$this->refId,'predecessorRefType'=>$this->refType);
      //$critSuccessor=array('predecessorId'=>$this->id); // Alternative
      $ls=$dep->getSqlElementsFromCriteria($critSuccessor);
      if(count($ls)>0 || count($lp)>0 ){
        foreach ($lp as $depP){
          foreach ($ls as $depS){
            $critElt=array('successorRefType'=>$depS->successorRefType,'successorRefId'=>$depS->successorRefId,'predecessorRefType'=>$depP->predecessorRefType,'predecessorRefId'=>$depP->predecessorRefId);
            $eltLsLp=$dep->getSqlElementsFromCriteria($critElt);
            //$eltLsLp=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $eltLsLp); // Alternative
            if(count($eltLsLp)==0){
            //if (! $eltLsLp->id) { // Alternative
              if($depS->dependencyType=="E-S" && $depP->dependencyType=="E-S"){
            		$dp=new Dependency();
  			        $dp->predecessorId=$depP->predecessorId;
  		          $dp->predecessorRefId=$depP->predecessorRefId;
  		          $dp->predecessorRefType=$depP->predecessorRefType;
  		          $dp->successorId=$depS->successorId;
  		          $dp->successorRefId=$depS->successorRefId;
  		          $dp->successorRefType=$depS->successorRefType;
  	            $dp->dependencyType="E-S";
  	            $dp->save();
              }
            }
          }
        }
      }
    }
    // Dispatch value
    return $result;
   
  }
  
 /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    global $canForceClose;
    $result="";
    if ($this->idle and $this->leftWork>0 and !SqlElement::isSaveConfirmed() and !$canForceClose) {
      $result.='<br/>' . i18n('errorIdleWithLeftWork');
    }
    $stat=array('initial','validated','planned','real');
    foreach ($stat as $st) {
      $start=$st.'StartDate';
      $end=$st.'EndDate';
      $startAttr=$this->getFieldAttributes($start);
      $endAttr=$this->getFieldAttributes($end);
      if (strpos($startAttr,'hidden')===false and strpos($startAttr,'readonly')===false 
      and strpos($endAttr,'hidden')===false and strpos($endAttr,'readonly')===false ) {
        if ($this->$start and $this->$end and $this->$start>$this->$end) {
          $result.='<br/>' . i18n('errorStartEndDates',array($this->getColCaption($start),$this->getColCaption($end)));
        }
      }
    }
    
//     if($this->id){
//   	  if($this->refType="Project"){
//   	  	$proj = new Project($this->refId);
//   	  	if($proj->fixPerimeter == 1){
//   	  		$result .= "<br/>" . i18n("msgUnableToUpdateOnFixPerimeter");
//   	  	}
//   	  }
//   	}
    
  	//Damian
    $old = $this->getOld();
    if($old->idProject!=$this->idProject or ($this->refType=='Project' and $old->topRefId!=$this->topRefId)){
      if($this->refType=='Project') {
        $projOld = new Project($old->topRefId,true);
        $projNew = new Project($this->topRefId,true);
      } else {
        $projOld = new Project($old->idProject,true);
        $projNew = new Project($this->idProject,true);
      }
      if ($projOld->fixPerimeter) {
        $result .= "<br/>" .i18n('msgUnableToMoveOutToFixPerimeter');
      }
      if ($this->realWork>0 and $projNew->isUnderConstruction==1) {
        $result .= "<br/>" .i18n('msgUnableToMoveRealWorkToUnderConstruction');
      }
      if ($projNew->fixPerimeter) {
        if(!$this->id){
          $result .= "<br/>".i18n('msgUnableToAddToFixPerimeter');
        } else {
          $result .= "<br/>".i18n('msgUnableToMoveOnFixPerimeter');
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
  
  public function deleteControl()
  {
  	$result="";
  	$canDeleteRealWork = false;
  	$crit = array('idProfile' => getSessionUser()->getProfile ( $this ), 'scope' => 'canDeleteRealWork');
  	$habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', $crit );
  	if ($habil and $habil->id and $habil->rightAccess == '1') {
  		$canDeleteRealWork = true;
  	} 
  	// Cannot delete item with real work
  	if ($this->id and $this->realWork and $this->realWork>0 and !$canDeleteRealWork)	{
 	    $result .= "<br/>" . i18n("msgUnableToDeleteRealWork");
  	}

  	//damian
  	if($this->refType=='Project') {
  	  $proj = new Project($this->topRefId,true);
  	} else {
  	  $proj = new Project($this->idProject,true);
  	}
  	if($proj->fixPerimeter){
  	  $result .= "<br/>" . i18n("msgUnableToDeleteOfFixPerimeter");
  	}
  	
  	if (! $result) {
  		$result=parent::deleteControl();
  	}
  	return $result;
  }
  
  public function controlHierarchicLoop($parentType, $parentId) {
    $result=""; 
    $parent=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',array('refType'=>$parentType,'refId'=>$parentId));
    $parentListObj=$parent->getParentItemsArray();
    if (array_key_exists('#' . $this->id,$parentListObj)) {
      $result='<br/>' . i18n('errorHierarchicLoop');
      return $result;
    }
    
    if($parentType == 'Activity'){
      $activity = new Activity($parentId);
      $activityType = new ActivityType($activity->idActivityType);
      if(!$activityType->canHaveSubActivity){
        $result='<br/>' . i18n('cantHaveSubActivity');
        return $result;
      }
    }
      
    $precListObj=$this->getPredecessorItemsArray();
    $succListObj=$this->getSuccessorItemsArray();
    //$parentListObj=$parent->getParentItemsArray(); // Commented => already done above
    $parentListObj['#'.$parent->id]=$parent;
    foreach ($parentListObj as $parentId=>$parentObj) {
      if (array_key_exists($parentId, $precListObj)) {
        $result='<br/>' . i18n('errorHierarchicLoop');
        return $result;
      }
      if (array_key_exists($parentId, $succListObj)) {
        $result='<br/>' . i18n('errorHierarchicLoop');
        return $result;
      }
    }
    return $result;    
  }
  
  public function getParentItemsArray() {
    // V2.1 refactoring of function
    $result=array();
    if ($this->topId) {
      $parent=new PlanningElement($this->topId);
      $result=$parent->getParentItemsArray();
      $result['#' . $parent->id]=$parent;
    }
    return $result;
  }
  
  public function getSonItemsArray($onlyFirstLevel=false) {
    // V2.1 refactoring of function
    // V8.6 : add option to get only
    $result=array();
    $crit=array('topId'=>$this->id);
    $listSons=$this->getSqlElementsFromCriteria($crit);
    if ($onlyFirstLevel) return $listSons;
    foreach ($listSons as $son) {
      $result['#'.$son->id]=$son;
      $result=array_merge($result,$son->getSonItemsArray());
    }
    return $result;
  }
  public function countSonItems() {
    // V2.1 refactoring of function
    // V8.6 : add option to get only
    $result=0;
    $crit=array('topId'=>$this->id);
    $result=$this->countSqlElementsFromCriteria($crit);
    return $result;
  }
  
  /** ==============================================================
   * Retrieve the list of all Predecessors, recursively
   */
  public function getPredecessorItemsArray() {
  	// Improvement : get static stored value if already fetched 
  	if (isset(self::$_predecessorItemsArray['#'.$this->id])) {
  		return self::$_predecessorItemsArray['#'.$this->id]; 
  	}
    $result=array();
    $crit=array("successorId"=>$this->id);
    $dep=new Dependency();
    $depList=$dep->getSqlElementsFromCriteria($crit, false);
    foreach ($depList as $dep) {
      $elt=new GlobalPlanningElement($dep->predecessorId);
      if ($elt->id and ! array_key_exists('#' . $elt->id, $result) and !isset(self::$_predecessorItemsArray['#'.$elt->id])) {
        $result['#' . $elt->id]=$elt;
        $resultPredecessor=$elt->getPredecessorItemsArray();
        $result=array_merge_preserve_keys($result,$resultPredecessor);
      }
    }
    // Imporvement : static store result to avoid multiple fetch
    self::$_predecessorItemsArray['#' . $this->id]=$result;
    return $result;
  }
  
    /** ==============================================================
   * Retrieve the list of direct Predecessors, and may include direct parents predecessors
   */
  public static function getPredecessorList($idCurrent, $includeParents=false) {
    $dep=new Dependency();
    if (! $includeParents) {
      return $dep->getSqlElementsFromCriteria(array("successorId"=>$idCurrent),false);
    }
    // Include parents successsors
    $testParent=new PlanningElement($idCurrent);
    $resultList=$dep->getSqlElementsFromCriteria(array("successorId"=>$idCurrent),false,null, null, true);
    while ($testParent->topId) {
      $testParent=new PlanningElement($testParent->topId);
      $list=$dep->getSqlElementsFromCriteria(array("successorId"=>$testParent->id),false,null, null, true);
      $resultList=array_merge($resultList,$list);
    }
    return $resultList;
  }
  public function getPredecessorItemsArrayIncludingParents() {
  	$result=$this->getPredecessorItemsArray();
  	$parents=$this->getParentItemsArray();
  	foreach ($parents as $parent) {
  		$resParent=$parent->getPredecessorItemsArray();
  		array_merge($result,$resParent);
  	}
    return $result;
  }
  public function getSuccessorItemsArrayIncludingParents() {
    $result=$this->getSuccessorItemsArray();
    $parents=$this->getParentItemsArray();
    foreach ($parents as $parent) {
    		$resParent=$parent->getSuccessorItemsArray();
    		array_merge($result,$resParent);
    }
    return $result;
  }
   /** ==============================================================
   * Retrieve the list of all Successors, recursively
   */
  public function getSuccessorItemsArray() {
    if (isset(self::$_successorItemsArray['#'.$this->id])) {
      return self::$_successorItemsArray['#'.$this->id];
    }
    $result=array();
    $crit=array("predecessorId"=>$this->id);
    $dep=new Dependency();
    $depList=$dep->getSqlElementsFromCriteria($crit, false);
    foreach ($depList as $dep) {
      $elt=new GlobalPlanningElement($dep->successorId);
      if ($elt->id and ! array_key_exists('#' . $elt->id, $result) and !isset(self::$_successorItemsArray['#'.$elt->id])) {
        $result['#' . $elt->id]=$elt;
        $resultSuccessor=$elt->getSuccessorItemsArray();
        $result=array_merge($result,$resultSuccessor);
      } 
//       else {
//         $elt=new PlanningElementExtended($dep->successorId);
//         if ($elt->id and ! array_key_exists('#' . $elt->id, $result)) {
//           $result['#' . $elt->id]=$elt;
//           $resultSuccessor=$elt->getSuccessorItemsArray();
//           $result=array_merge($result,$resultSuccessor);
//         }
//       }
    }
    self::$_successorItemsArray['#' . $this->id]=$result;
    return $result;
  }

  public function moveTo($destId,$mode,$recursive=false) {
    $status="WARNING";
    $result="";
    $returnValue="";
    $task=null;
    $changeParent=false;
    
    $checkClass=get_class($this);
    if (SqlElement::is_a($this, 'PlanningElement')) {
      $checkClass=$this->refType;
    }
    $right=securityGetAccessRightYesNo('menu' . $checkClass, 'update', $this);
    if ($right!='YES') {
      $returnValue=i18n('errorUpdateRights');
      $returnValue .= '<input type="hidden" id="lastOperation" value="move" />';
      $returnValue .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
      $returnValue .= '<input type="hidden" id="lastPlanStatus" value="OK" />';
      return $returnValue;
    }
    
    $dest=new PlanningElement($destId);
    if ($dest->topRefType!=$this->topRefType
    or $dest->topRefId!=$this->topRefId) {    // Change parent
      $objectClass=$this->refType;
      $objectId=$this->refId;
      $task=new $objectClass($objectId);     // Object to move
      if ($dest->topRefType=="Project") {    // Move directly under project
      	$task->idProject=$dest->topRefId;
      	if (property_exists($task, 'idActivity')) {
      		$task->idActivity=null;
      	}
      	$changeParent="project";
      	$status="OK";
      } else if ($dest->topRefType=="Activity" and property_exists($task, 'idActivity')) {  // Move under (new) activity
      	$task->idProject=$dest->idProject;   // Move to same project
      	$task->idActivity=$dest->topRefId;   // Move under same activity
      	$changeParent='activity';
      	$status="OK";
      } else if (! $dest->topRefType and $objectClass=='Project') { // Moving a project to root
      	$task->idProject=null;
      	$changeParent='root';
      	$status="OK";
      }
  		if ($status!="OK") {
  			$returnValue=i18n('moveCancelled');
  		}
  		if (!$this->idle and $dest->idle) { // Move non idle after/before idle : check if new parent is idle
  		  $destParent=new PlanningElement($dest->topId);
  		  if ($destParent->idle) { // Move non closed item under closed item : forbidden
  		    $returnValue=i18n('moveCancelledIdle');
  		    $status="WARNING";
  		  }
  		}
    } else { // Don't change parent => just reorder at same level
      $status="OK"; 
      $changeParent=false;
    }
    $parent=new PlanningElement($dest->topId);
    if ($status=="OK" and $task and !$recursive) { // Change parent, then will recursively call moveTo to reorder correctly
      $peName=get_class($task).'PlanningElement';
      if ($peName=='PeriodicMeetingPlanningElement') $peName='MeetingPlanningElement';
      $oldParentId=$task->$peName->topId;
      $task->$peName->topRefType=$dest->topRefType;
      $task->$peName->topRefId=$dest->topRefId;
      $task->$peName->topId=$dest->topId;
    	$resultTask=$task->save();
    	if (stripos($resultTask,'id="lastOperationStatus" value="OK"')>0 ) {
    		$pe=new PlanningElement($this->id);
    		$pe->moveTo($destId,$mode,true);
    		$returnValue=i18n('moveDone');
    		// Must renumber old parent...
     		if ($changeParent=="project" and !$oldParentId) {
     		  $oldParent=new PlanningElement();
     		  $oldParent->renumberWbs(true);
     		}
      } else {
      	$returnValue=$resultTask;//i18n('moveCancelled');
      	//$status="ERROR";
      	$status=getLastOperationStatus($resultTask);
      }
    } else if ($status=="OK") { // Just reorder on same level
      if ($this->topRefType) {
        $where="topRefType='" . $this->topRefType . "' and topRefId=" . Sql::fmtId($this->topRefId) ;
      } else {
        $where="topRefType is null and topRefId is null";
      }
      $order="wbsSortable asc";
      $list=$this->getSqlElementsFromCriteria(null,false,$where,$order);
      $idx=0;
      $currentIdx=0;
      foreach ($list as $pe) {
        if ($pe->id==$this->id) {
          // met the one we are moving => skip
        } else {
          if ($pe->id==$destId and $mode=="before") {
            $idx++;
            $currentIdx=$idx;
          }
          $idx++;
          $root=substr($pe->wbs,0,strrpos($pe->wbs,'.'));
          //$root=$parent->wbs;
          $oldWbs=$pe->wbs;
          $pe->wbs=($root=='')?$idx:$root.'.'.$idx;
          if ($pe->refType and $oldWbs!=$pe->wbs) {
            $pe->wbsSave();
          }
          if ($pe->id==$destId and $mode=="after") {
            $idx++;
            $currentIdx=$idx;
          }
        }
      }
      $root=substr($this->wbs,0,strrpos($this->wbs,'.'));
      $this->wbs=($root=='')?$currentIdx:$root.'.'.$currentIdx;
      $this->save();
      $returnValue=i18n('moveDone');
      $status="OK";
    } 
      
    $returnValue .= '<input type="hidden" id="lastOperation" value="move" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $status . '" />';
    $returnValue .= '<input type="hidden" id="lastPlanStatus" value="OK" />'; // Must send OK to refresh planning (and revert move) 
    return $returnValue;
  }

  public function indent($way) {
  	$result=i18n('moveCancelled');
  	$status="WARNING";
  	$objectClass=$this->refType;
  	$objectId=$this->refId;
  	$task=new $objectClass($objectId);
  	if ($way=="decrease") {
  		$top=null;
  		if (property_exists($task, 'idActivity') and $task->idActivity) {
  			$top=new Activity($task->idActivity);
  		} else if (property_exists($task, 'idProject') and $task->idProject) {
  			$top=new Project($task->idProject);
  		}
  		if ($top and property_exists($top, 'idActivity') and $top->idActivity) {
  			$task->idActivity=$top->idActivity;
  			$resTmp=$task->save();
  			if (getLastOperationStatus($resTmp)=="OK") {
  			  $result=i18n('moveDone');
  			  $status="OK";
  			} else {
  			  $status="ERROR";
  			  $result=$resTmp;
  			}	
  		} else if ($top and property_exists($top, 'idProject') and ($top->idProject or $objectClass=='Project') ) {
  			if (property_exists($task, 'idActivity') and $task->idActivity) {
  				$task->idActivity=null;
  			}
  			$task->idProject=$top->idProject;
  			$resTmp=$task->save();
  		  if (getLastOperationStatus($resTmp)=="OK") {
  			  $result=i18n('moveDone');
  			  $status="OK";
  			} else {
  			  $status="ERROR";
  			  $result=$resTmp;
  			}	
  		}
  		if ($top and $status=="OK") {
  			$pe=new PlanningElement($this->id);
  			$crit=array('refType'=>get_class($top),'refId'=>$top->id);
  			$peTop=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
  			$resTmp=$pe->moveTo($peTop->id,"after");
  			if (getLastOperationStatus($resTmp)=="OK") {
  			  echo $resTmp;
  			} else {
  			  $status="ERROR";
  			  $result=$resTmp;
  			}
  		}
  	} else { // $way=="increase"
  	  $prec=null;
  		$precs=$this->getSqlElementsFromCriteria(null,false,
  		    "wbsSortable<'".$this->wbsSortable."' and idProject in " . getVisibleProjectsList(true),"wbsSortable desc");
  		if (count($precs)>0) {
  			foreach ($precs as $pp) {
  				if (strlen($pp->wbsSortable)<=strlen($this->wbsSortable)) {
  				  $proj=new Project($pp->idProject);
  				  $type=new Type($proj->idProjectType);
  				  if ($type->code=='TMP' or $type->code=='ADM') {
  				    continue;
  				  } else {
  					  $prec=$pp;
  					  break;
  				  } 
  				}
  			}
  			if ($prec and $prec->idle and !$this->idle) {
  			  $result=i18n('moveCancelledIdle');
  			  $status="WARNING";
  			} else if ($prec and $prec->refType=='Project' and $prec->refId!=$task->idProject) {
  				$task->idProject=$prec->refId;
    			$resTmp=$task->save();
    			if (getLastOperationStatus($resTmp)=="OK") {
    			  $result=i18n('moveDone');
    			  $status="OK";
    			} else {
    			  $status="ERROR";
    			  $result=$resTmp;
    			}	
  			} else if ($prec and $prec->refType=='Activity' and property_exists($task, 'idActivity') and $task->idActivity!=$prec->refId) {
  				$task->idActivity=$prec->refId;
    			$resTmp=$task->save();
    			if (getLastOperationStatus($resTmp)=="OK") {
    			  $result=i18n('moveDone');
    			  $status="OK";
    			} else {
    			  $status="ERROR";
    			  $result=$resTmp;
    			}	
  			} else {
  				// Cannot move
  			}
  		}
  	}
  	$result .= '<input type="hidden" id="lastOperation" value="move" />';
  	$result .= '<input type="hidden" id="lastOperationStatus" value="' . $status . '" />';
  	$result .= '<input type="hidden" id="lastPlanStatus" value="OK" />';
  	return $result;
  }
  
  public function renumberWbs($force=false) {
    if (PlanningElement::$_noDispatch and !$force) return;
  	if ($this->id) {
  		$where="topRefType='" . $this->refType . "' and topRefId=" . Sql::fmtId($this->refId) ;
  	} else {
  		$where="topRefType is null and topRefId is null";
  	}
  	$order="wbsSortable asc";
  	$list=$this->getSqlElementsFromCriteria(null,false,$where,$order);
  	$idx=0;
  	$currentIdx=0;
  	foreach ($list as $pe) {
  			$idx++;
  			$root=substr($pe->wbs,0,strrpos($pe->wbs,'.'));
  			$pe->wbs=($root=='')?$idx:$root.'.'.$idx;
  			if ($pe->refType) {
  				$pe->wbsSave();
  			}
  	}
  }
  
  // Preserved old syntax for compatibility with plugins
  public static function getWorkVisibiliy($profile) {
    return self::getWorkVisibility($profile);
  }
  // Preserved old syntax for compatibility with plugins
  public static function getCostVisibiliy($profile) {
    return self::getCostVisibility($profile);
  }
  
  public static function getWorkVisibility($profile) {
    if (! self::$staticWorkVisibility or ! isset(self::$staticWorkVisibility[$profile]) ) {
      $pe=new PlanningElement();
      $pe->setVisibility($profile);
    }
    return self::$staticWorkVisibility[$profile];
  }
  public static function getCostVisibility($profile) {
    if (! self::$staticCostVisibility or ! isset(self::$staticCostVisibility[$profile]) ) {
      $pe=new PlanningElement();
      $pe->setVisibility($profile);
    }
    return self::$staticCostVisibility[$profile];
  }
  
  public function setVisibility($profile=null) {
    if (! sessionUserExists()) {
      return;
    }
    if (! $profile) {
      $user=getSessionUser();
      $profile=$user->getProfile($this->idProject);
    }
    if (self::$staticCostVisibility and isset(self::$staticCostVisibility[$profile])
    and self::$staticWorkVisibility and isset(self::$staticWorkVisibility[$profile]) ) {
      $this->_costVisibility=self::$staticCostVisibility[$profile];
      $this->_workVisibility=self::$staticWorkVisibility[$profile];
      return;
    }
    $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$profile,'scope'=>'changeValidatedData'));
      if ($habil and ($habil->rightAccess == 2 or ! $habil->id ) ) { // If selected NO or not set (default is NO)
      self::$_fieldsAttributes['validatedStartDate']='readonly';
      self::$_fieldsAttributes['validatedEndDate']='readonly';    
      self::$_fieldsAttributes['validatedWork']='readonly';
      self::$_fieldsAttributes['validatedDuration']='readonly';
      self::$_fieldsAttributes['validatedCost']='readonly';
      self::$_fieldsAttributes['expenseValidatedAmount']='readonly';
    } else {
      self::$_fieldsAttributes['validatedStartDate']='';
      self::$_fieldsAttributes['validatedEndDate']='';
      self::$_fieldsAttributes['validatedWork']='';
      self::$_fieldsAttributes['validatedDuration']='';
      self::$_fieldsAttributes['validatedCost']='';
      self::$_fieldsAttributes['expenseValidatedAmount']='';
    }
    
    $paramPriorit='changePriorityOther';
    if($this->refType=='Project'){
      $paramPriorit='changePriorityProj';
    }
    //damian
    $priority=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$profile,'scope'=>$paramPriorit));
    if ($priority and ($priority->rightAccess == 2 or ! $priority->id ) ) { // If selected NO or not set (default is NO)
    	self::$_fieldsAttributes['priority']='readonly';
    } else {
    	self::$_fieldsAttributes['priority']='';
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
  
  public function getFieldAttributes($fieldName) {
    if (! $this->_costVisibility or ! $this->_workVisibility) {
      $this->setVisibility();
    }
    if ($this->_costVisibility =='NO') {
      if (substr($fieldName,-4)=='Cost'
       or substr($fieldName,0,7)=='expense'
       or substr($fieldName,0,5)=='total'
       or substr($fieldName, 0,13) == 'reserveAmount'
       or $fieldName=='revenue' 
       or $fieldName=='_label_commandSum' or $fieldName=='commandSum'
       or $fieldName=='_label_billSum' or $fieldName=='billSum') {
         return 'hidden';
      }
    } else if ($this->_costVisibility =='VAL') {
      if ( (substr($fieldName,-4)=='Cost' and $fieldName!='validatedCost')
       or (substr($fieldName,0,7)=='expense' and $fieldName!='expenseValidatedAmount')
       or (substr($fieldName,0,5)=='total' and $fieldName!='totalValidatedCost')
       or substr($fieldName, 0,13) == 'reserveAmount') {
         return 'hidden';
      }
    }
    if ($this->_workVisibility=='NO') {
      if (substr($fieldName,-4)=='Work' or $fieldName=='idWorkUnit' or $fieldName=='idComplexity' or $fieldName=='quantity'
          or $fieldName=='_label_complexity' or $fieldName=='_label_quantity') {
         return 'hidden';
      }
    } else if ($this->_workVisibility=='VAL') {
      if ( substr($fieldName,-4)=='Work' and $fieldName!='validatedWork') {
         return 'hidden';
      }
    }
    if ($this->_workVisibility=='NO' and $this->_costVisibility =='NO') {
      if ( $fieldName=='_separator_sectionRevenue_marginTop') {
        return 'hidden';
      }
    }
    //gautier #4344
    if ($this->id and $this->validatedCalculated) {
    	if ($fieldName=='validatedWork' and $this->validatedWork > 0) {
    	  return "readonly";
    	}
    	if ($fieldName=='validatedCost' and $this->validatedCost > 0) {
    	  return "readonly";
    	}
    }
    if ($this->id and $this->validatedExpenseCalculated) {
      if ($fieldName=='expenseValidatedAmount' and $this->$fieldName>0) {
        return "readonly";
      }
    }
    return parent::getFieldAttributes($fieldName);
  }  
  
  /**
   * Fulfill a planningElementList with :
   *  - parents for each item
   *  - predecessor for each item
   * @param List of PlanningElements
   */
  public static function initializeFullList($list) {
    if (count($list)==0) return $list;
    $idList=array();
    // $list must be sorted on WBS !
    $result=$list;
    $listProjectsPriority=array();
    // Parents
    foreach ($list as $id=>$pe) {
    	if ($pe->refType=='Project') {		
    		$listProjectsPriority[$pe->refId]=$pe->priority;
    	}
      $idList[$pe->id]=$pe->id;
      $pe->_parentList=array();
      $pe->_childList=array();
      if ($pe->topId) { 
        if (array_key_exists('#'.$pe->topId, $result)) {
          $parent=$result['#'.$pe->topId];
        } else {
          $parent=new GlobalPlanningElement($pe->topId,true);
          $parent->_parentList=array();
          $parent->_predecessorList=array();
          $parent->_predecessorListWithParent=array();
          $parent->_noPlan=true;
          $parent->_childList=array();
          $result['#'.$pe->topId]=$parent;
        }
        if (isset($parent->_parentList)) {
          $pe->_parentList=$parent->_parentList;
        }
        $pe->_parentList['#'.$pe->topId]=$pe->topId;
      }
      if (! $pe->idPlanningMode) {
        $profile="ASAP";
      } else {
        $pm=new PlanningMode($pe->idPlanningMode,true);
        $profile=$pm->code;
      }
      $pe->_profile=$profile;
      $result[$id]=$pe;
    }
    // In the end and GlobalItems that have dependencies on Project
    $where="topRefType='Project' and topRefId IN ".transformListIntoInClause($listProjectsPriority);
    $pex=new PlanningElementExtension();
    $pexList=$pex->getSqlElementsFromCriteria(null,null,$where);
    foreach ($pexList as $pex) {
      $id='#'.$pex->getFakeId();
      if (!isset($result[$id])) {
        $gpe=new GlobalPlanningElement($pex->getFakeId());
        $gpe->_parentList=array();
        $gpe->_predecessorList=array();
        $gpe->_predecessorListWithParent=array();
        $gpe->_noPlan=false;
        $gpe->_childList=array();
        $gpe->_profile='FDUR';
        $gpe->id=$pex->getFakeId();
        $result[$id]=$gpe;
        $idList[$gpe->id]=$gpe->id;
      } else {
        $gpe=$result[$id];
        $gpe->_noPlan=false;
        $gpe->_profile='FDUR';
        $result[$id]=$gpe;
      }
    }
    
    $reverse=array_reverse($result, true);
    foreach ($reverse as $id=>$pe) {
      if ($pe->topId) {
        if (array_key_exists('#'.$pe->topId, $result)) {
          $parent=$result['#'.$pe->topId];
        } else {
          $parent=new GlobalPlanningElement($pe->topId,true);
          $parent->_parentList=array();
          $parent->_predecessorList=array();
          $parent->_predecessorListWithParent=array();
          $parent->_noPlan=true;
          $parent->_childList=array();
          $parent->_profile='';
          $result['#'.$pe->topId]=$parent;
        } 
        $parent=$result['#'.$pe->topId];
        $parent->_childList=array_merge_preserve_keys($pe->_childList,$parent->_childList);
        $parent->_childList['#'.$pe->id]=$pe->id;
        $parent->_profile='';
        $result['#'.$pe->topId]=$parent;
      }
    }
    // Predecessors
    $crit='successorId in (0,' . implode(',',$idList) . ')';
    $dep=new Dependency();
    
    $depList=$dep->getSqlElementsFromCriteria(null, false, $crit);
    $directPredecessors=array();
    foreach ($depList as $dep) {
      if (! array_key_exists("#".$dep->successorId, $directPredecessors)) {
        $directPredecessors["#".$dep->successorId]=array();
      }
      $lstPrec=$directPredecessors["#".$dep->successorId];
      //$lstPrec["#".$dep->predecessorId]=$dep->predecessorId;
      $lstPrec["#".$dep->predecessorId]=array("delay"=>$dep->dependencyDelay, "type"=>$dep->dependencyType);  // #77 : store delay of dependency
      if (! array_key_exists("#".$dep->predecessorId, $result)) {
      	$parent=new GlobalPlanningElement($dep->predecessorId,true);
        $parent->_parentList=array();
        $parent->_predecessorList=array();
        $parent->_predecessorListWithParent=array();
        $parent->_noPlan=true;
        $parent->_childList=array();
        $result["#".$dep->predecessorId]=$parent;
      }
      $parentChilds=$result["#".$dep->predecessorId]->_childList;
      foreach ($parentChilds as $tmpIdChild=>$tempValChild) {
      	$parentChilds[$tmpIdChild]=array("delay"=>$dep->dependencyDelay, "type"=>$dep->dependencyType);
      }
      if (isset($parentChilds["#".$dep->successorId])) { unset($parentChilds["#".$dep->successorId]); } // Self cannot be it own predecessor
      $directPredecessors["#".$dep->successorId]=array_merge_preserve_keys($lstPrec,$parentChilds);
    }
    foreach ($result as $id=>$pe) {
      $pe=$result[$id];
      if (array_key_exists($id, $directPredecessors)) {
        $pe->_directPredecessorList=$directPredecessors[$id];
      } else {
        $pe->_directPredecessorList=array();
      } 
      $visited=array();
      //$pe->_predecessorList=self::getRecursivePredecessor($directPredecessors,$id,$result,'main', $visited, 1); // #3212
      $pe->_predecessorList=$pe->_directPredecessorList;  // #3212 : do not get recursive predecessors, only direct
      $pe->_predecessorListWithParent=$pe->_predecessorList;
      foreach ($pe->_parentList as $idParent=>$parent) {
      	$visited=array();
      	$parentPrecListTmp=self::getRecursivePredecessor($directPredecessors,$idParent,$result,'parent', $visited, 1);
      	foreach ($parentPrecListTmp as $idPrec=>$valPrec) {
      	  // If relation does not exist yet, 
      	  if (!isset($pe->_predecessorListWithParent[$idPrec]) 
      	      or ($valPrec['type']=='E-S' and $pe->_predecessorListWithParent[$idPrec]['type']=='E-S' and $valPrec['delay']>$pe->_predecessorListWithParent[$idPrec]['delay']) 
      	      or ($valPrec['type']=='E-S' and $pe->_predecessorListWithParent[$idPrec]['type']!='E-S')) {
      	    $pe->_predecessorListWithParent[$idPrec]=$valPrec;
      	  }
      	}
      }
      if (! $pe->realStartDate and ! (isset($pe->_noPlan) and $pe->_noPlan) and ! (isset($pe->isGlobal) and $pe->isGlobal) ) {
        $pe->plannedStartDate=null;
      }
      if (! $pe->realEndDate and ! (isset($pe->_noPlan) and $pe->_noPlan) and ! (isset($pe->isGlobal) and $pe->isGlobal)) {
        $pe->plannedEndDate=null;
      }
      if (! property_exists($pe, 'indivisibility')) {
        $pe->indivisibility=0;
      }
      $result[$id]=$pe;
    }
    $result['_listProjectsPriority']=$listProjectsPriority;
    return $result;
  }
  
  
  private static function getRecursivePredecessor($directFullList, $id, $result,$scope,$visited,$level) {
  	if (isset($result[$id]->_predecessorList)) {
  		return $result[$id]->_predecessorList;
  	}
  	if (array_key_exists($id, $directFullList)) {
  	  if ($level==1) {
        $result=$directFullList[$id];
  	  } else {
  	    $dfl=array(); // For level > 1, only include E-S
  	    foreach ($directFullList[$id] as $dflId=>$dflItem) {
  	      if ($dflItem['type']=='E-S') {
  	        $dfl[$dflId]=$dflItem;
  	      }
  	    }
  	    $result=$dfl;
  	  }
  	  foreach ($directFullList[$id] as $idPrec=>$prec) {
  	    if ($prec['type']=='E-E' or $prec['type']=='S-S') continue; // If current is not E-S, do not retreive recursive predecessors
  	  	if(array_key_exists($idPrec,$visited)) continue;
  	  	$visited[$idPrec]=1;
        $result=array_merge(self::getRecursivePredecessor($directFullList,$idPrec,$result,$scope,$visited,$level+1),$result);
      }
    } else {
      $result=array();
    }
  	return $result;
  }
  
  static function comparePlanningElementSimple($a, $b) {
    if ($a->_sortCriteria<$b->_sortCriteria) {
      return -1;
    }
    if ($a->_sortCriteria>$b->_sortCriteria) {
      return +1;
    }
    return 0;       
  }
  
  static function copyStructure($obj, $newObj, $copyToOrigin=false, 
      $copyToWithNotes=false, $copyToWithAttachments=false, $copyToWithLinks=false, 
      $copyAssignments=false, $copyAffectations=false, $toProject=null, $copySubProjects=false) {
    //self::$_noDispatch=true; // avoid recursive updates on each item, will be done only at elementary level    
    $pe=new PlanningElement();
    $list=$pe->getSqlElementsFromCriteria(array('topRefType'=>get_class($obj), 'topRefId'=>$obj->id),null,null,'wbsSortable asc');
    foreach ($list as $pe) { // each planning element corresponding to item to copy
      if ($pe->refType!='Activity' and $pe->refType!='Project' and $pe->refType!='Milestone') continue;
      if ($pe->refType=='Project' and ! $copySubProjects) continue;
      $item=new $pe->refType($pe->refId);
      $type='id'.get_class($item).'Type';
      $newItem=$item->copyTo(get_class($item),$item->$type, $item->name, ($toProject)?$toProject:$pe->idProject, $copyToOrigin, 
                             $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks, 
                             $copyAssignments, $copyAffectations, $toProject, (get_class($newObj)=='Activity')?$newObj->id:null );
      $resultItem=$newItem->_copyResult;
      unset($newItem->_copyResult);
      if (! stripos($resultItem,'id="lastOperationStatus" value="OK"')>0 ) {
        return $resultItem;
      }
      self::$_copiedItems[get_class($item).'#'.$item->id]=array('from'=>$item,'to'=>$newItem);
      if ($pe->refType=='Project' and $copyAffectations) {
        $aff=new Affectation();
        $crit=array('idProject'=>$item->id);
        $lstAff=$aff->getSqlElementsFromCriteria($crit);
        foreach ($lstAff as $aff) {
          $critExists=array('idProject'=>$aff->idProject, 'idResource'=>$aff->idResource);
          $affExists=SqlElement::getSingleSqlElementFromCriteria('Affectation', $critExists);
          if (!$affExists or !$affExists->id) {
        		$aff->id=null;
        		$aff->idProject=$newItem->id;
        		$aff->save();
          }
        }
      }
      // recursively call copy structure
      $res=self::copyStructure($item, $newItem, $copyToOrigin,
                          $copyToWithNotes, $copyToWithAttachments, $copyToWithLinks,
                          $copyAssignments, $copyAffectations, ($pe->refType=='Project')?$newItem->id:$toProject,$copySubProjects);
      if ($res!='OK') {
        return $res;
      }
    }
    return "OK"; // No error ;)
  }
  
  static function copyStructureProject($obj, $newObj, $copyToOrigin=false,
      $copyToWithNotes=false, $copyToWithAttachments=false, $copyToWithLinks=false,
      $copyAssignments=false, $copyAffectations=false, $toProject=null, $copySubProjects=false) {
    $pe=new PlanningElement();
    $list=$pe->getSqlElementsFromCriteria(array('refType'=>'Project','topRefType'=>'Project', 'topRefId'=>$obj->id),null,null,'wbsSortable asc');
    foreach ($list as $pe) { // each planning element corresponding to item to copy
      $item=new $pe->refType($pe->refId);
      $type='id'.get_class($item).'Type';
      $newItem=$item->copyTo(get_class($item),$item->$type, $item->name, ($toProject)?$toProject:$pe->idProject, $copyToOrigin,
          $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks,
          $copyAssignments, $copyAffectations, $toProject, (get_class($newObj)=='Activity')?$newObj->id:null );
      $resultItem=$newItem->_copyResult;
      unset($newItem->_copyResult);
      if (! stripos($resultItem,'id="lastOperationStatus" value="OK"')>0 ) {
        return $resultItem;
      }
      self::$_copiedItems[get_class($item).'#'.$item->id]=array('from'=>$item,'to'=>$newItem);
      if ($pe->refType=='Project' and $copyAffectations) {
        $aff=new Affectation();
        $crit=array('idProject'=>$item->id);
        $lstAff=$aff->getSqlElementsFromCriteria($crit);
        foreach ($lstAff as $aff) {
          $critExists=array('idProject'=>$aff->idProject, 'idResource'=>$aff->idResource);
          $affExists=SqlElement::getSingleSqlElementFromCriteria('Affectation', $critExists);
          if (!$affExists or !$affExists->id) {
          		$aff->id=null;
          		$aff->idProject=$newItem->id;
          		$aff->save();
          }
        }
      }
      // recursively call copy structure
      $res=self::copyStructureProject($item, $newItem, $copyToOrigin,
          $copyToWithNotes, $copyToWithAttachments, $copyToWithLinks,
          $copyAssignments, $copyAffectations, ($pe->refType=='Project')?$newItem->id:$toProject,$copySubProjects);
      if ($res!='OK') {
        return $res;
      }
    }
    return "OK"; // No error ;)
  }
  
  
  
  static function copyOtherStructure($obj, $newObj, $copyToOrigin=false,
    $copyToWithNotes=false, $copyToWithAttachments=false, $copyToWithLinks=false,
    $copyAssignments=false, $copyAffectations=false, $toProject=null, $copySubProjects=false, $copyToWithVersionProjects=false, $copyStructure=false) {
    //self::$_noDispatch=true; // avoid recursive updates on each item, will be done only al elementary level
    $pe=new PlanningElement();
    $list=$pe->getSqlElementsFromCriteria(array('topRefType'=>get_class($obj), 'topRefId'=>$obj->id),null,null,'wbsSortable asc');
    foreach ($list as $pe) { // each planning element corresponding to item to copy
      if ($pe->refType!='Meeting' and $pe->refType!='TestSession' and $pe->refType!='PeriodicMeeting' and $pe->refType!='Project') continue;
      if ($pe->refType=='Project' and  !$copySubProjects) continue;
      if ($pe->refType=='Project' and  $copyStructure) continue;
      $item=new $pe->refType($pe->refId);
      $type=null;
      if(get_class($item)=='PeriodicMeeting'){
        $type='idMeetingType';
      } else {
        $type='id'.get_class($item).'Type';
      }
      $newItem=$item->copyTo(get_class($item),$item->$type, $item->name ,$pe->idProject, $copyToOrigin,
          $copyToWithNotes, $copyToWithAttachments,$copyToWithLinks,
          $copyAssignments, $copyAffectations, $toProject,null );
      $resultItem=$newItem->_copyResult;
      unset($newItem->_copyResult);
      if (! stripos($resultItem,'id="lastOperationStatus" value="OK"')>0 ) {
        return $resultItem;
      }
      self::$_copiedItems[get_class($item).'#'.$item->id]=array('from'=>$item,'to'=>$newItem);
      if ($pe->refType=='Project' and $copyAffectations) {
        $aff=new Affectation();
        $crit=array('idProject'=>$item->id);
        $lstAff=$aff->getSqlElementsFromCriteria($crit);
        foreach ($lstAff as $aff) {
          $critExists=array('idProject'=>$aff->idProject, 'idResource'=>$aff->idResource);
          $affExists=SqlElement::getSingleSqlElementFromCriteria('Affectation', $critExists);
          if (!$affExists or !$affExists->id) {
          		$aff->id=null;
          		$aff->idProject=$newItem->id;
          		$aff->save();
          }
        }
      }
      // recursively call copy structure
      $res=self::copyOtherStructure($item, $newItem, $copyToOrigin,
          $copyToWithNotes, $copyToWithAttachments, $copyToWithLinks,
          $copyAssignments, $copyAffectations, ($pe->refType=='Project')?$newItem->id:$toProject,$copySubProjects,$copyToWithVersionProjects,$copyStructure);
      if ($res!='OK') {
        return $res;
      }
    }
    return "OK"; // No error ;)
  }
  
  static function copyStructureFinalize() {
    self::$_noDispatch=true;
    // Update synthesys for non elementary item (will just be done once ;)
    foreach (array_reverse(PlanningElement::$_noDispatchArray) as $pe) {
      $res=PlanningElement::updateSynthesis($pe['refType'], $pe['refId']);
    }
    self::$_noDispatch=false;
    // copy dependencies
    $critWhere="";
    foreach (self::$_copiedItems as $id=>$fromTo) {
      $from=$fromTo['from'];
      $critWhere.=(($critWhere)?',':'')."('".get_class($from)."','" . Sql::fmtId($from->id) . "')";
    }
    if ($critWhere) {
      $clauseWhere="(predecessorRefType,predecessorRefId) in (" . $critWhere . ")"
          . " or (successorRefType,successorRefId) in (" . $critWhere . ")";
    } else {
      $clauseWhere=" 1=0 ";
    }
    $dep=New dependency();
    $deps=$dep->getSqlElementsFromCriteria(null, false, $clauseWhere);
    foreach ($deps as $dep) {      
      // Do not copy link with globalizable items as they are not copied
      if (GlobalPlanningElement::isGlobalizable($dep->predecessorRefType) or GlobalPlanningElement::isGlobalizable($dep->successorRefType)) continue; 
      if (array_key_exists($dep->predecessorRefType . "#" . $dep->predecessorRefId, self::$_copiedItems) ) {
        $to=self::$_copiedItems[$dep->predecessorRefType . "#" . $dep->predecessorRefId]['to'];
        $dep->predecessorRefType=get_class($to);
        $dep->predecessorRefId=$to->id;
        $crit=array('refType'=>get_class($to), 'refId'=>$to->id);
        $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
        $dep->predecessorId=$pe->id;
      }
      if (array_key_exists($dep->successorRefType . "#" . $dep->successorRefId, self::$_copiedItems) ) {
        $to=self::$_copiedItems[$dep->successorRefType . "#" . $dep->successorRefId]['to'];
        $dep->successorRefType=get_class($to);
        $dep->successorRefId=$to->id;
        $crit=array('refType'=>get_class($to), 'refId'=>$to->id);
        $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
        $dep->successorId=$pe->id;
      }
      $dep->id=null;
      $tmpRes=$dep->save();
      if (! stripos($tmpRes,'id="lastOperationStatus" value="OK"')>0 ) {
        debugTraceLog($tmpRes); // Will not raise an error but will trace it in log
        debugTraceLog("for predecessor $dep->predecessorRefType #$dep->predecessorRefId, successor $dep->successorRefType #$dep->successorRefId");
      }
    }
    BudgetElement::dispatchFinalize();
    $result="OK";
  }
  
  public static function moveTaskFinalize() {
    self::$_noDispatch=true;
    // Update synthesys for non elementary item (will just be done once ;)
    $list=PlanningElement::$_noDispatchArray;
    PlanningElement::$_noDispatchArray=array();
    foreach ($list as $pe) {
      $res=PlanningElement::updateSynthesis($pe['refType'], $pe['refId']);
    }
    if (count(PlanningElement::$_noDispatchArray)>0) {
      self::moveTaskFinalize();
    }
    BudgetElement::dispatchFinalize();
  }
    
  public static function getMilestonableList() {
    $dir='../model/';
    $handle = opendir($dir);
    $result=array();
    while ( ($file = readdir($handle)) !== false) {
      if ($file == '.' || $file == '..' || $file=='index.php' // exclude ., .. and index.php
          || substr($file,-4)!='.php'                           // exclude non php files
          || substr($file,-8)=='Main.php') {                    // exclude the *Main.php
        continue;
      }
      $class=pathinfo($file,PATHINFO_FILENAME);
      $ext=pathinfo($file,PATHINFO_EXTENSION);
      if (file_exists($dir.$class.'Main.php')) {
        if (property_exists($class, 'idMilestone') and property_exists($class, 'idle')) $result[]=$class;
      }
    }
    closedir($handle);
    asort($result);
    return $result;
  }
  
  public static function updateSynthesisNoDispatch($refType, $refId) {
    self::$_noDispatchArray[$refType.'#'.$refId]=array('refType'=>$refType,'refId'=>$refId);
  }
  /**
   * Will update all items referencing the milstone to set planned date to new Milstone planned date
   * @param string $restrictType => if set will restrict to items of this class
   * @param string $restrictId => if setand $restricType also set) will restrict to single item 
   */
  public function updateMilestonableItems($restrictType=null,$restrictId=null) {
    if ($restrictType) {
      $list=array($restrictType);
    } else {
      $list=self::getMilestonableList();
    }
    $critMilestone=array('idMilestone'=>$this->refId,'idle'=>'0');
    if ($restrictType && $restrictId) {
      $critMilestone=array('id'=>$restrictId);
    }
    
    foreach ($list as $class) {
      $dt="";
      $arrayDate=array('actualDueDate', 'actualDueDateTime','actualEndDate','plannedDate','plannedDeliveryDate','plannedEisDate','expectedTenderDateTime','expensePlannedDate');
      foreach($arrayDate as $date) {
        if (property_exists($class, $date)) {
          if ($date=='plannedDeliveryDate' and substr($class,-7)=='Version' and Parameter::getGlobalParameter('displayMilestonesStartDelivery')!='YES') continue;
          $dt=$date;
          break;
        }
      }
      if ($dt) {
        $ref=new $class();
        $refList=$ref->getSqlElementsFromCriteria($critMilestone);
        foreach ($refList as $ref) {
          $ref->$dt=$this->plannedStartDate;
          $ref->save();   
        }
      }
      if ($restrictType and $restrictId and Parameter::getGlobalParameter('autoLinkMilestone')=='YES') { // Add a link to milestone
        if ($class=='Activity') {
          $crit=array('ref1Type'=>$restrictType, 'ref1Id'=>$restrictId,'ref2Type'=>'Milestone','ref2Id'=>$this->refId);
        } else {
          $crit=array('ref2Type'=>$restrictType, 'ref2Id'=>$restrictId,'ref1Type'=>'Milestone','ref1Id'=>$this->refId);
        }
        $link=SqlElement::getSingleSqlElementFromCriteria('Link', $crit);
        if (!$link->id) {
          $link->creationDate=date('Y-m-d H:i:s');
          $resLn=$link->save();
        }
      }
    }
  }
  
  public function updateCA($updateParent=false){
      if(! Module::isModuleActive('moduleGestionCA')){ return;}
      //if ($this->refType)
      //$old = $this->getOld();
    	$project = new Project($this->idProject);
    	$projectList = $project->getRecursiveSubProjectsFlatList(true, ($this->idProject)?true:false);
    	$projectList = array_flip($projectList);
    	$projectList = '(0,'.implode(',',$projectList).')';
    	$where = 'idProject in '.$projectList.' and cancelled = 0';
    	$paramAmount = Parameter::getGlobalParameter('ImputOfAmountClient');
    	$cmdAmount = ($paramAmount == 'HT')?'totalUntaxedAmount':'totalFullAmount';
    	$command = new Command();
    	$this->commandSum = $command->sumSqlElementsFromCriteria($cmdAmount, null, $where);
    	if(!$this->commandSum)$this->commandSum=0;
    	$billAmount = ($paramAmount == 'HT')?'untaxedAmount':'fullAmount';
    	$bill = new Bill();
    	$this->billSum = $bill->sumSqlElementsFromCriteria($billAmount, null, $where);
    	if(!$this->billSum)$this->billSum=0;
    	$paramCA = Parameter::getGlobalParameter('CaReplaceValidCost');
    	if($paramCA == 'YES' and $this->revenue > 0){
    		$this->validatedCost = $this->revenue;
    	}
    	$this->simpleSave();
    	if ($updateParent and $this->topId and $this->topRefType='Project') {
    	  $top=new PlanningElement($this->topId);
    	  $top->updateCA(true);
    	}
  }
  
  function updateRevenue(){
    if(! Module::isModuleActive('moduleGestionCA')){ return;}
  	$project = new Project($this->idProject);
  	//$projectList = $project->getRecursiveSubProjectsFlatList(true,true);
  	//$projectList = array_flip($projectList);
  	//$projectList = '(0,'.implode(',',$projectList).')';
  	if (! isset(self::$_revenueCalculated[$this->refType.'#'.$this->refId])) {
  	  self::$_revenueCalculated[$this->refType.'#'.$this->refId]=$this->id;
    	if(($this->idRevenueMode == 2 and $this->refType == 'Project') 
    	or ($this->refType == 'Activity' and property_exists($project, 'ProjectPlanningElement') and is_object($project->ProjectPlanningElement) and $project->ProjectPlanningElement->idRevenueMode == 2)){
    		$sons=$this->getSonItemsArray(true);
    		$sumActPlEl=0;
    		$sumProjlEl=0;
    		$asSubProj=false;
    		$asSubAct=false;
    		foreach ($sons as $id=>$pe){
    			if ($pe->refType=='Activity' and $pe->idProject==$this->idProject and $pe->topId==$this->id and !$pe->cancelled){
    				$sumActPlEl+=$pe->revenue;
    				$asSubAct=true;
    			}else if ($pe->refType=='Project' and $pe->topRefId==$this->idProject and !$pe->cancelled){
    				$asSubProj=true;
    				$sumProjlEl+=$pe->revenue;
    			}else{
    				continue;
    			}
    		}
    		if($sumActPlEl>0 and !$asSubProj){
    			$this->revenue = $sumActPlEl;
    		}else if($sumProjlEl>0 and ($asSubProj or $asSubAct)){
    			$this->revenue =($asSubAct)?$sumProjlEl+$sumActPlEl:$sumProjlEl;
    		}else if ($this->refType == 'Project'){
    		  $this->revenue = 0;
    		}
    	}
    	if ($this->refType=='Project') $this->updateCA();
  	}
  }
}
?>