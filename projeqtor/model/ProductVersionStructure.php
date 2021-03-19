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
class ProductVersionStructure extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idProductVersion;
  public $idComponentVersion;
  public $comment;
  public $creationDate;
  public $idUser;
  public $idle;
  public static $_composition=array();
  
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
    if ($old->idProductVersion!=$this->idProductVersion or $old->idComponentVersion!=$this->idComponentVersion) {
      if ($this->idComponentVersion) {
        $vers=new ComponentVersion($this->idComponentVersion);
        $comp=new Component($vers->idComponent);
        $comp->updateAllVersionProject();
        $list=$comp->getComposition(false,true);
        foreach ($list as $cptId) {
          $comp=new Component($cptId);
          $comp->updateAllVersionProject();
        }
      }
      if ($old->idComponentVersion and $old->idComponentVersion!=$this->idComponentVersion) {
        $vers=new ComponentVersion($old->idComponentVersion);
        $comp=new Component($vers->idComponent);
        $comp->updateAllVersionProject();
        $list=$comp->getComposition(false,true);
        foreach ($list as $cptId) {
          $comp=new Component($cptId);
          $comp->updateAllVersionProject();
        }
      }
      if ($this->idProductVersion) {
        $vers=new ComponentVersion($this->idProductVersion);
        if ($vers->id) {
          $comp=new Component($vers->idComponent); // V5.3.0 : idProduct can refer to Component
          if ($comp->id) {
            $comp->updateAllVersionProject();
            $list=$comp->getComposition(false,true);
            foreach ($list as $cptId) {
              $comp=new Component($cptId);
              $comp->updateAllVersionProject();
            }
          }
        }
      }
      if ($old->idProductVersion and $old->idProductVersion!=$this->idProductVersion) {
        $vers=new ComponentVersion($old->idProductVersion);
        if ($vers->id) {
          $comp=new Component($vers->idComponent); // V5.3.0 : idProduct can refer to Component
          if ($comp->id) {
            $comp->updateAllVersionProject();
            $list=$comp->getComposition(false,true);
            foreach ($list as $cptId) {
              $comp=new Component($cptId);
              $comp->updateAllVersionProject();
            }
          }
        }
      }
    }
    return $result;
  }
  
  public function delete() {	
  	$result=parent::delete();    
  	if ($this->idComponentVersion) {
  	  $vers=new ComponentVersion($this->idComponentVersion);
  	  $comp=new Component($vers->idComponent);
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
    $checkCrit="idProductVersion=$this->idProductVersion and idComponentVersion=$this->idComponentVersion";
    if ($this->id) $checkCrit.=" and id!=$this->id";
    $comp=new ProductVersionStructure();
    $check=$comp->getSqlElementsFromCriteria(null, false,$checkCrit);
    if (count($check)>0) {
      $result.='<br/>' . i18n('errorDuplicateLink');
    } 
    
    // Infinite loops
    if ($this->idProductVersion==$this->idComponentVersion) {
      $result='<br/>' . i18n('errorHierarchicLoop');
    }
    $productVersionStructure=self::getStructure($this->idProductVersion);
    foreach ($productVersionStructure as $prd=>$prdId) {
      if ($prdId==$this->idComponentVersion) {
        $result='<br/>' . i18n('errorHierarchicLoop');
        break;
      }
    }
    $componentVersionComposition=self::getComposition($this->idComponentVersion);
    foreach ($componentVersionComposition as $comp=>$compId) {
      if ($compId==$this->idProductVersion) {
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
// PB - Optimization
//     $crit=array('idProductVersion'=>$id);
//     $ps=new ProductVersionStructure();
//     $psList=$ps->getSqlElementsFromCriteria($crit);
//     if (is_numeric($level)) $level--;
//     foreach ($psList as $ps) {
//       $result['#'.$ps->idComponentVersion]=$ps->idComponentVersion;
//       if ($level=='all' or $level>0) {
//         $result=array_merge($result,self::getComposition($ps->idComponentVersion));
//       }
//     }
// PB - Optimization - New code
    $key=$id.'|'.(($level=='all')?'1':'0');
    if (isset(self::$_composition[$key])) return self::$_composition[$key];
    $crit=array('idProductVersion'=>$id);
    $psList=SqlList::getListWithCrit('ProductVersionStructure',$crit,'idComponentVersion');
    if (is_numeric($level)) $level--;
    foreach ($psList as $idComp) {
      $result['#'.$idComp]=$idComp;
      if ($level=='all' or $level>0) {
        $result=array_merge($result,self::getComposition($idComp,$level));
      }
    }
    self::$_composition[$key]=$result;
// PB - Optimization - End
    return $result;
  }
  public static function getStructure($id, $level='all') {
    $result=array();
    $crit=array('idComponentVersion'=>$id);
    $ps=new ProductVersionStructure();
    $psList=$ps->getSqlElementsFromCriteria($crit);
    if (is_numeric($level)) $level--;
    foreach ($psList as $ps) {
      $result['#'.$ps->idProductVersion]=$ps->idProductVersion;
      if ($level=='all' or $level>0) {
        $result=array_merge($result,self::getStructure($ps->idProductVersion));
      }
    }
    return $result;
  }

  public static function sortCompositionComponentVersionListOnId($pvs1, $pvs2) {
    return strnatcmp($pvs2->idComponentVersion, $pvs1->idComponentVersion);
  }

  public static function sortStructureComponentVersionListOnId($pvs1, $pvs2) {
    return strnatcmp($pvs2->idProductVersion, $pvs1->idProductVersion);
  }
  
  public static function sortComponentVersionListOnType($pvs1, $pvs2) {
    $v1 = new ComponentVersion($pvs1->idComponentVersion, true);
    $v2 = new ComponentVersion($pvs2->idComponentVersion, true);
    if ($v1->idVersionType == $v2->idVersionType) {
      return strnatcmp($v2->name, $v1->name);
    }
    $t1 = new ComponentVersionType($v1->idVersionType, true);
    $t2 = new ComponentVersionType($v2->idVersionType, true);
    return strnatcmp($t1->name, $t2->name);
  }
  public static function sortVersionListOnType($pvs1, $pvs2) {
    $v1 = new Version($pvs1->idProductVersion, true);
    $v2 = new Version($pvs2->idProductVersion, true);
    if ($v1->scope != $v2->scope) {
      return strnatcmp($v2->scope, $v1->scope);
    }
    if ($v1->idVersionType == $v2->idVersionType) {
      return strnatcmp($v2->name, $v1->name);
    }
    $t1 = new Type($v1->idVersionType, true);
    $t2 = new Type($v2->idVersionType, true);
    return strnatcmp($t1->name, $t2->name);
  }
  public static function sortProductVersionList($vca, $vcb)  {
    global $idObj;
    $a=new ProductVersion((($idObj==$vca->idVersionA)?$vca->idVersionB:$vca->idVersionA));
    $b=new ProductVersion((($idObj==$vcb->idVersionA)?$vcb->idVersionB:$vcb->idVersionA));
    if (strcmp($a->name, $b->name)==0) {
      return strnatcmp($a->versionNumber, $b->versionNumber);
    }
    return strnatcmp($a->name, $b->name);
  }
  public static function sortVersionList($vp1, $vp2) {
    $version1 = new Version($vp1->idVersion, false);
    $version2 = new Version($vp2->idVersion, false);
    return strnatcmp($version2->name, $version1->name);
  }
  
}
?>