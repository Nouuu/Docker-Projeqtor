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
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once('_securityCheck.php');
class ProductStructure extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idProduct;
  public $idComponent;
  public $comment;
  public $creationDate;
  public $idUser;
  public $idle;
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
 
  /**
   * Save object (permuts objects ref if needed)
   * @see persistence/SqlElement#save()
   */
  public function save() {
    $old=$this->getOld();
    $result=parent::save();
    if ($old->idProduct!=$this->idProduct or $old->idComponent!=$this->idComponent) {
      if ($this->idComponent) {
        $comp=new Component($this->idComponent);
        $comp->updateAllVersionProject();
      }
      if ($old->idComponent and $old->idComponent!=$this->idComponent) {
        $comp=new Component($old->idComponent);
        $comp->updateAllVersionProject();
      }
      if ($this->idProduct) {
        $comp=new Component($this->idProduct); // V5.3.0 : idProduct can refer to Component
        if ($comp->id) {
          $comp->updateAllVersionProject();
        }
      }
      if ($old->idProduct and $old->idProduct!=$this->idProduct) {
        $comp=new Component($old->idProduct); // V5.3.0 : idProduct can refer to Component
        if ($comp->id) {
          $comp->updateAllVersionProject();
        }
      }
    }
    return $result;
  }
  
  public function delete() {	
  	$result=parent::delete();    
  	if ($this->idComponent) {
  	  $comp=new Component($this->idComponent);
  	  $comp->updateAllVersionProject();
  	  $list=$comp->getComposition(false,true);
  	  foreach ($list as $cptId) {
  	    $comp=new Component($cptId);
  	    $comp->updateAllVersionProject();
  	  }
  	}
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
    
    // Duplicate
    $checkCrit="idProduct=$this->idProduct and idComponent=$this->idComponent";
    if ($this->id) $checkCrit.=" and id!=$this->id";
    $comp=new ProductStructure();
    $check=$comp->getSqlElementsFromCriteria(null, false,$checkCrit);
    if (count($check)>0) {
      $result.='<br/>' . i18n('errorDuplicateLink');
    } 
      
    // Infinite loops
    if ($this->idProduct==$this->idComponent) {
      $result='<br/>' . i18n('errorHierarchicLoop');
    }   
    $productStructure=self::getStructure($this->idProduct);
    foreach ($productStructure as $prd=>$prdId) {
      if ($prdId==$this->idComponent) {
        $result='<br/>' . i18n('errorHierarchicLoop');
        break;
      }
    }    
    $componentComposition=self::getComposition($this->idComponent);
    foreach ($componentComposition as $comp=>$compId) {
      if ($compId==$this->idProduct) {
        $result='<br/>' . i18n('errorHierarchicLoop');
        break;
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
  
  public static function getComposition($id,$level='all') {
    $result=array();
    $crit=array('idProduct'=>$id);
    $ps=new ProductStructure();
    $psList=$ps->getSqlElementsFromCriteria($crit);
    if (is_numeric($level)) $level--;
    foreach ($psList as $ps) {
      $result['#'.$ps->idComponent]=$ps->idComponent;
      if ($level=='all' or $level>0) {
        $result=array_merge($result,self::getComposition($ps->idComponent));
      }
    }
    return $result;
  }
  public static function getStructure($id, $level='all') {
    $result=array();
    $crit=array('idComponent'=>$id);
    $ps=new ProductStructure();
    $psList=$ps->getSqlElementsFromCriteria($crit);
    if (is_numeric($level)) $level--;
    foreach ($psList as $ps) {
      $result['#'.$ps->idProduct]=$ps->idProduct;
      if ($level=='all' or $level>0) {
        $result=array_merge($result,self::getStructure($ps->idProduct));
      }
    }
    return $result;
  }
}
?>