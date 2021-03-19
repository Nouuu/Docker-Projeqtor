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
 * Line defines right to the application for a menu and a profile.
 */  
require_once('_securityCheck.php'); 
class BillLine extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $refType;
  public $refId;
  public $line;
  public $quantity;
  public $description;
  public $detail;
  public $price;
  public $idMeasureUnit;
  public $amount;
  public $idTerm;
  public $idResource;
  public $idActivityPrice;
  public $startDate;
  public $endDate;
  public $extra;
  public $billingType;
  public $idCatalog;
  public $numberDays;
  public $idBillLine;
  public $rate;
  
  public $_noHistory=true; // Will never save history for this object
  
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
 
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";    
    
  	$bill = new $this->refType($this->refId);
  	$billingType='M';
  	if ($this->billingType) {
  	  $billingType=$this->billingType;
  	} else if (property_exists($bill, 'billingType')) {
      $billingType=$bill->billingType;
  	}
	  if (property_exists($bill, 'billId') and is_numeric($bill->billId) and $bill->done) {
		  $result.='<br/>' . i18n('errorLockedBill');
	  }
	  if ($billingType=='E') {
	    if ( ! trim($this->idTerm) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdTerm')));
      }
	  }
	  if ($billingType=='R' or $billingType=='P') {
      if ( ! trim($this->idResource) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdResource')));
      }
	    if ( ! trim($this->idActivityPrice) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdActivityPrice')));
      }
	    if ( ! $this->startDate){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colStartDate')));
      }
	    if ( ! $this->endDate){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colEndDate')));
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
  
/** =========================================================================
   * Overrides SqlElement::deleteControl() function to add specific treatments
   * @see persistence/SqlElement#deleteControl()
   * @return the return message of persistence/SqlElement#deleteControl() method
   */  
  
  public function deleteControl() {
  	$result="";    
    $bill = new $this->refType($this->refId);
    if (property_exists($bill, 'billId') and is_numeric($bill->billId) and $bill->done) {
      $result.='<br/>' . i18n('errorLockedBill');
    }    
  	if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
  }
  
  
  /** =========================================================================
   * Overrides SqlElement::delete() function to add specific treatments
   * @see persistence/SqlElement#delete()
   * @return the return message of persistence/SqlElement#delete() method
   */  
  public function delete()
  {  	
  	$paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
  		
	  $bill=new $this->refType($this->refId);
    $billingType='M';
  	if ($this->billingType) {
  	  $billingType=$this->billingType;
  	} else if (property_exists($bill, 'billingType')) {
      $billingType=$bill->billingType;
  	}
	  if ($billingType=='E') {
      $term=new Term($this->idTerm);
      $term->idBill=null;
      $term->save();
      $crit=array('successorRefType'=>'Term','successorRefId'=>$term->id);
      $dep=new Dependency();
      $depList=$dep->getSqlElementsFromCriteria($crit, null);
      foreach($depList as $dep) {
        $class=$dep->predecessorRefType;
        $obj=new $class($dep->predecessorRefId);
        $pe=new PlanningElement($dep->predecessorId);
        $pe->idBill=null;
        $pe->save();          
      }
    }
    if ($billingType=='R' or$billingType=='P' ) {
      $price=New ActivityPrice($this->idActivityPrice);
      $act=New Activity();
      $critAct=array("idActivityType"=>$price->idActivityType, "idProject"=>$price->idProject);
      $actList=$act->getSqlElementsFromCriteria($critAct, false);
      foreach ($actList as $act) {
        
        $lstIdWithIdPool=array();
        $lstIdWithIdPool[]=$this->idResource;
        $resourceTemaAff=new ResourceTeamAffectation();
        $lstPoolAff=$resourceTemaAff->getSqlElementsFromCriteria(array("idResource"=>$this->idResource));
        if(!empty($lstPoolAff)){
          foreach ($lstPoolAff as $poolAff){
            $lstIdWithIdPool[]=$poolAff->idResourceTeam;
          }
        }
        $lstIdWithIdPool=implode(',', $lstIdWithIdPool);
        $ass=new Assignment();
        $whereAss="refType='Activity'and refId=$act->id and idProject=$act->idProject and idResource in ($lstIdWithIdPool)";
        $assList=$ass->getSqlElementsFromCriteria(null,null,$whereAss);
        
        foreach ($assList as $ass) {
          $selectedAss=false;
          $work = new Work();
          $crit = "idProject='".Sql::fmtId($bill->idProject) . "'";
          $crit.=" and idResource='".Sql::fmtId($this->idResource). "'";    
          if ($this->startDate) $crit.=" and workDate>='" . $this->startDate . "'";
          if ($this->endDate) $crit.=" and workDate<='" . $this->endDate . "'";
          $crit.=" and idAssignment='".Sql::fmtId($ass->id)."'";
          $crit.=" and idBill='" . Sql::fmtId($bill->id) . "'";   
          $workList = $work->getSqlElementsFromCriteria(null,false,$crit, "idAssignment asc");
          foreach ($workList as $work) {
            $work->idBill=null;
            $selectedAss=true;
            $ass->billedWork-=$work->work;
            $work->save();
          }
          if ($selectedAss) {
            $ass->save();
          }
        }
      }       
    }
//Debut Code Marc
    // Update Bill to get total of amount
    $billToSave=false;
    if (property_exists($bill, 'untaxedAmount') and property_exists($bill, 'fullAmount') and property_exists($bill, 'taxPct') ) {
      $bill->untaxedAmount=$bill->untaxedAmount-$this->amount;
      $bill->fullAmount=$bill->untaxedAmount*(1+$bill->taxPct*0.01);
      $billToSave=true;
    } 
    if ( property_exists($bill, 'plannedWork')) {
      $bill->plannedWork=$bill->plannedWork-$this->numberDays;
      if($bill->plannedWork<0) $bill->plannedWork = 0;
      $billToSave=true;
    }
    // Only save without calculate the amount
    
// Fin Code Marc
    //gautier #devisTender
    if (property_exists($bill, 'totalUntaxedAmount') and property_exists($bill, 'totalTaxAmount') and property_exists($bill, 'totalFullAmount') ) {
      if($bill->untaxedAmount == 0){
        $bill->taxAmount=0;
        $bill->totalUntaxedAmount=0;
        $bill->totalFullAmount=0;
        $bill->totalTaxAmount=0;
        $bill->fullAmount=0;
      }else{
        $bill->taxAmount = $bill->fullAmount-$bill->untaxedAmount;
        $bill->totalUntaxedAmount=$bill->totalUntaxedAmount-$this->amount;
        $bill->totalFullAmount=$bill->totalUntaxedAmount*(1+$bill->taxPct*0.01);
        $bill->totalTaxAmount=$bill->totalFullAmount-$bill->totalUntaxedAmount;
      }
      $billToSave=true;
    }
    
    if ($billToSave) {
      $bill->simpleSave();
    }
    
    return parent::delete();
  }
  
  /** =========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */  
  public function save() {
  	
    $bill=new $this->refType($this->refId);
    
    $billingType='M';
  	if ($this->billingType) {
  	  $billingType=$this->billingType;
  	} else if (property_exists($bill, 'billingType')) {
      $billingType=$bill->billingType;
  	}
  	
  	if ($billingType=='E') {
  		if (! $this->id) {
  		  $term=new Term($this->idTerm);
  		  $this->description=$term->name;
  		  $this->price=$term->amount;
  		  $term->idBill=$bill->id;
  		  $term->save();
  		  $crit=array('successorRefType'=>'Term','successorRefId'=>$term->id);
  		  $dep=new Dependency();
  		  $depList=$dep->getSqlElementsFromCriteria($crit, null);
  		  $this->detail="";
  		  foreach($depList as $dep) {
  		  	$class=$dep->predecessorRefType;
  		  	$obj=new $class($dep->predecessorRefId);
  		  	$this->detail.=($this->detail)?"\n":'';
  		  	$this->detail.=$obj->name;
  		  	$pe=new PlanningElement($dep->predecessorId);
  		  	$pe->idBill=$bill->id;
  		  	$pe->save();  		  	
  		  }
  		}
  	}
  	if ($billingType=='R' or $billingType=='P' ) {
      if (! $this->id) {
      	$this->detail="";
      	$totalWork=0;
      	$billableWork=0;
      	$listDates=array();
      	$price=New ActivityPrice($this->idActivityPrice);
      	$act=New Activity();
      	$critAct=array("idActivityType"=>$price->idActivityType, "idProject"=>$price->idProject);
      	$actList=$act->getSqlElementsFromCriteria($critAct, false);
      	if(!($this->startDate or $this->endDate))return;
      	foreach ($actList as $act) {
      		$actWork=0;
      		$actBilled=0;
      		$actAssigned=0;
      		$actPlanned=0;
      		$selectedAct=false;
// D�but Code Marc
      		// Activity closed => idle=1
      		$actClose = $act->idle;
      		$lstIdWithIdPool=array();
      		$lstIdWithIdPool[]=$this->idResource;
      		$resourceTemaAff=new ResourceTeamAffectation();
      		$lstPoolAff=$resourceTemaAff->getSqlElementsFromCriteria(array("idResource"=>$this->idResource));
      		if(!empty($lstPoolAff)){
      		  foreach ($lstPoolAff as $poolAff){
      		    $lstIdWithIdPool[]=$poolAff->idResourceTeam;
      		  }
      		}
      		$lstIdWithIdPool=implode(',', $lstIdWithIdPool);
      		$ass=new Assignment();
      		$whereAss="refType='Activity'and refId=$act->id and idProject=$act->idProject and idResource in ($lstIdWithIdPool)";
      		$assList=$ass->getSqlElementsFromCriteria(null,null,$whereAss);
      		
// Fin Code Marc

//       		$critAss=array("refType"=>"Activity", "refId"=>$act->id, "idProject"=>$act->idProject, "idResource"=>$this->idResource);
//       		$assList=$ass->getSqlElementsFromCriteria($critAss, false);
      		if(!empty($assList)){
          		foreach ($assList as $ass) {
        			$selectedAss=false;
        			$actBilled+=$ass->billedWork;
        			$actAssigned+=$ass->assignedWork;
        			$actPlanned+=$ass->plannedWork;
        			$work = new Work();
                    $crit = "idProject='".$bill->idProject . "'";
                    $crit.=" and idResource='".Sql::fmtId($this->idResource). "'";    
                    $crit.=" and workDate>='".$this->startDate."'";
                    $crit.=" and workDate<='".$this->endDate."'";
                    $crit.=" and idAssignment='".Sql::fmtId($ass->id)."'";
                    $crit.=" and idBill is null";   
                    $workList = $work->getSqlElementsFromCriteria(null,false,$crit, "idAssignment asc");
                    foreach ($workList as $work) {
                    	$work->idBill=$bill->id;
                    	$totalWork+=$work->work;
                    	$actWork+=$work->work;
                    	$selectedAct=true;
                    	$selectedAss=true;
        //            	$ass->billedWork+=$work->work;
        // D�but Code Marc
        				if ($billingType=='P') {
        					// Add until not > assignment
        					$ass->billedWork=min($ass->billedWork+$work->work,$actAssigned);
        				} else {           	
        	            	$ass->billedWork+=$work->work;
        				}
        // Fin Code Marc
                    	            	// Sum of work for dates : to be displayed if needed
                    	if (array_key_exists($work->workDate, $listDates)) {
                    	  $listDates[$work->workDate]+=$work->work;
                      } else {
                        $listDates[$work->workDate]=$work->work;
                      }
                    	$work->save();
                    }
    /*            if ($selectedAss) {
                	$ass->save();
                }
    */
    // D�but Code Marc
                // If some work to bill [$selectedAss==true] or the activity is close [$actClose==1]
                if ($selectedAss OR $actClose==1) {
                	// If the activity is close AND the $billingType=='P'
                	if ($actClose==1 AND $billingType=='P') {
                		// The billedWork is the assigned work
                		$ass->billedWork=$actAssigned;
                	}
                	// Fin mon code
                	$ass->save();
                }
    // Fin Code Marc            
                }
      		}else{
      		  continue;
      		}
/*
      		if ($selectedAct) {
      			$doneWork=($actWork+$actBilled);
      			$progressWork=round( ($doneWork/$actPlanned),3);
      			$actBillable=round( ( ($actAssigned*$progressWork)-$actBilled),1);
      			$actBillable=($actBillable>0)?$actBillable:0;
      			$billableWork+=$actBillable;
      			$this->detail.=(($this->detail)?"\n":"").$act->name;
      			if ($billingType=='P') {
      				$this->detail.=" : ".$actBillable." ".i18n('days');
      				$this->detail.="\n...[" . i18n('colBillable') . "] = [" . i18n('colValidated') . "]"
      				                        . " x [" . i18n('progress')  . "] - [" . i18n('colIsBilled') . "]";
      				$this->detail.="\n...[" . $actBillable . " " . i18n('days') . "] = [" . $actAssigned . " " . i18n('days') . "]"
      				                        . " x [" . ($progressWork*100) . "%] - [" . $actBilled . " " . i18n('days') . "]";
      			} else {
      			  $this->detail.=" : ".$actWork." ".i18n('days');
      			}
      		}
*/
//D�but Code Marc
      		// If some work to bill [$selectedAct==true] or the activity is close [$actClose==1]
      		if ($selectedAct OR $actClose==1) {
      			$doneWork=($actWork+$actBilled);
    	  		if ($actClose==0) {
					// Activity NOT CLOSE
					// Work Billable = MIN(work of period, assigned Work - work billed)
    	  			$actBillable = min($actWork,$actAssigned-$actBilled);
    	  		} else {
    			  	// Activity CLOSE
    			  	// Work Billable = MAX(0, assigned Work - work billed) <== The sold
    			  	$actBillable = max(0,$actAssigned-$actBilled);  			
    	  		}
      			$actBillable=($actBillable>0)?$actBillable:0;
      			$billableWork+=$actBillable;      			
      			if ($billingType=='P') {
      				if ($actBillable>0) {
	      				$this->detail.=(($this->detail)?"\n":"").$act->name;
      					$this->detail.=" : ".$actBillable." ".i18n('days');
      					if($actClose==0) {
	    	  				$this->detail.="\n...[" . i18n('colBillable') . "] = MIN([" . i18n('colWork') . "]"
    	  				                        . " , [" . i18n('colValidated')  . "] - [" . i18n('colIsBilled') . "])";
							$this->detail.="\n...[" . $actBillable . " " . i18n('days') . "] = MIN([" . $actWork . " " . i18n('days') . "]"
      				    	                    . " , [" . $actAssigned . " " . i18n('days') . "] - [" . $actBilled . " " . i18n('days') . "])";
						} else {
      						$this->detail.="\n...[" . i18n('colBillable') . "] = MAX(0 ," 
      					                        . " [" . i18n('colValidated')  . "] - [" . i18n('colIsBilled') . "])";
							$this->detail.="\n...[" . $actBillable . " " . i18n('days') . "] = MAX(0,[" . $actAssigned . " " . i18n('days') . "]"
    	  				                        . "] - [" . $actBilled . " " . i18n('days') . "])";
						
						}
      				}
      			} else {
      				if ($actWork>0) {
	      				$this->detail.=(($this->detail)?"\n":"").$act->name;
      					$this->detail.=" : ".$actWork." ".i18n('days');
      				}
      			}
      		}
//Fin Code Marc      		
        }
      	if ($billingType=='P') {
      		$this->quantity=$billableWork;
      	} else {     	
      	  $this->quantity=$totalWork;
      	}
      	$this->price=$price->priceCost;
      	$ress=new Resource($this->idResource);
        $this->description=$ress->name 
                 . "\n" . $price->name 
                 . "\n" . htmlFormatDate($this->startDate) . " - " . htmlFormatDate($this->endDate);
      }
  	}
  	
  	$this->amount=floatval($this->quantity)*floatval($this->price);
  	$result=parent::save();
  	
  	// Update Bill to get total of amount
  	$bill->save(); 
  	return $result;
  }
}
?>
