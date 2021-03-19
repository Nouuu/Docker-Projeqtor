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
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
require_once('_securityCheck.php');
class ResourceCost extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $idResource;
  public $idRole;
  public $cost=0;
  public $startDate;
  public $endDate; 
  public $idle;
  //public $_sec_void;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  
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
  
  public function save() {
    $new=($this->id)?false:true;
    $newCost=true;
    if (! $new) {
      $old=$this->getOld();
      $newCost=($old->cost==$this->cost)?false:true;
    }
    $result=parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    $id=($this->id)?$this->id:SQL::$lastQueryNewid;
    if ($this->startDate and $new) {
      $where="idResource=" . Sql::fmtId($this->idResource) . " and idRole=" . Sql::fmtId($this->idRole) . " ";
      $where.=" and endDate is null";
      $where.=" and id<>" . Sql::fmtId($id);
      $rc=new ResourceCost();
      $precs=$rc->getSqlElementsFromCriteria(null, false, $where);
      if (count($precs)==1) {
        $prec=$precs[0];
        $prec->endDate=addDaysToDate($this->startDate,-1);
        $prec->save();
      }
    }
    if ($newCost) { // Cost has changed : must dispatch to stored costs
      // Update Cost for Real Work : for the start date of new cost only (if set)
      $wk=new Work();
      $where="idResource=" . Sql::fmtId($this->idResource) ;
      if ($this->startDate) {
        $where.= " and workDate>='" . $this->startDate . "'";
      }
      $wkList=$wk->getSqlElementsFromCriteria(null, false, $where);
      $arrayAss=array();
      $arrayWE=array();
      foreach ($wkList as $wk) {
        if ($wk->idAssignment) {
          if (array_key_exists($wk->idAssignment,$arrayAss)) {
            $ass=$arrayAss[$wk->idAssignment];
          } else {
            $ass=new Assignment($wk->idAssignment);
          }
          
          if ($ass->idRole==$this->idRole) {
            $wk->dailyCost=$this->cost;
            $wk->cost=$wk->dailyCost*$wk->work;
            $res=$wk->saveForced();
            $arrayAss[$ass->id]=$ass;
          }
        } else {
          $wk->dailyCost=$this->cost;
          $wk->cost=$wk->dailyCost*$wk->work;
          $res=$wk->saveForced();
          if ($wk->idWorkElement) {
            if (!isset($arrayWE[$wk->idWorkElement])) {
              $arrayWE[$wk->idWorkElement]=new WorkElement($wk->idWorkElement);
            }
          }
        }
      }           
      $where="idResource=" . Sql::fmtId($this->idResource) . " and idRole=" .Sql::fmtId($this->idRole) . " and leftWork>0";
      $ass=new Assignment();
      $assList=$ass->getSqlElementsFromCriteria(null, false, $where);
      foreach ($assList as $ass) {
        if ($ass->realWork==0 and trim($this->startDate)=='') {
          // If single cost only and real work not defined : update defaut cost so that assigned cost is updated
          $ass->dailyCost=$this->cost;
        }
        $ass->saveWithRefresh();
        if (isset($arrayAss[$ass->id])) unset($arrayAss[$ass->id]);
      }
      foreach ($arrayAss as $ass) {
        $ass->saveWithRefresh();
      }
      foreach ($arrayWE as $wk) {
        $wk->save();
      } 
    }
    return $result; 
  }
  
  public function delete() { 
    $result = parent::delete();  
    if (strpos($result,'lastOperationStatus" value="OK"')==0) {
      return $result;
    }
    
    $precStartDate=null;
    $precCost=0;
    if ($this->startDate) {
      $where="idResource=" . Sql::fmtId($this->idResource) . " and idRole=" . Sql::fmtId($this->idRole)
        . " and (endDate is not null ".((Sql::isMysql())?"and endDate<>'0000-00-00'":""). ")";
      if ($this->id) {
        $where.=" and id<>" . Sql::fmtId($this->id);
      }
      $order="endDate desc";
      $rc=new ResourceCost();
      $precs=$rc->getSqlElementsFromCriteria(null, false, $where, $order);
      if (count($precs)>=1) {
        $prec=$precs[0];
        $prec->endDate=null;
        $prec->save();
        $precStartDate==$prec->startDate;
        $precCost=$prec->cost;
      }
    }

    $wk=new Work();
    $where="idResource='" . Sql::fmtId($this->idResource) . "'";
    if ($this->startDate) {
      $where.= " and workDate>='" . $this->startDate . "'";
    }
    $wkList=$wk->getSqlElementsFromCriteria(null, false, $where);
    foreach ($wkList as $wk) {
      $ass=new Assignment($wk->idAssignment);
      if ($ass->idRole==$this->idRole) {
        $wk->dailyCost=$precCost;
        $wk->cost=$wk->dailyCost*$wk->work;
        $res=$wk->saveForced();
      }
    }     
  
    $where="idResource='" . Sql::fmtId($this->idResource) . "' and idRole='" . Sql::fmtId($this->idRole) . "' and leftWork>0";
    $ass=new Assignment();
    $assList=$ass->getSqlElementsFromCriteria(null, false, $where);
    foreach ($assList as $ass) {
      $ass->saveWithRefresh();
    }
    
    return $result;
  }
  
  public function deleteControl() { 
    $result='';
    if (! $this->startDate) {
      // Control : if assignment exists for this ressource and role => cancel deletion
      $crit=array("idResource"=>$this->idResource,"idRole"=>$this->idRole);
      $asg=new Assignment();
      $lstAsg=$asg->getSqlElementsFromCriteria($crit, false);
      if (count($lstAsg)>0) {
        // ERROR CONTROL
        $result.="<br/>".i18n("errorControlDelete");
        $result.="<br/>&nbsp;-&nbsp;" . i18n('Assignment') . " (" . count($lstAsg) . ")";
        return $result;
      } 
    }
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }
  
  public function control() {
    $result="";
    if ($this->startDate and !$this->id) {
      $where="idResource='" . Sql::fmtId($this->idResource) . "' ";
      $where.=" and idRole='" . Sql::fmtId($this->idRole) . "' ";
      $where.=" and (endDate is null " .((Sql::isMysql())?"or endDate='0000-00-00'":"") .")";
      if ($this->id) {
        $where.=" and id<>" . $this->id;
      }
      $rc=new ResourceCost();       
      $precs=$rc->getSqlElementsFromCriteria(null, false, $where);
      if (count($precs)==1) {
        $prec=$precs[0];
        if ($prec->startDate and $prec->startDate>=$this->startDate) {
          $result.='<br/>' . i18n('errorStartEndDates', array(i18n('colPreviousStartDate'),i18n('colStartDate')));
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
}
?>