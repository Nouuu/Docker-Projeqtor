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
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
require_once('_securityCheck.php');
class VersionProject extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $idVersion;
  public $idProject;
  public $startDate;
  public $endDate;
  public $idle;

  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="idle" width="10%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required"
  );   

  private static $_colCaptionTransposition = array('idContact'=>'contractor', 'idResource'=>'responsible'
  );
  
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
    $result="";
    if ($item=='projects') {
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('projects') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if ($this->id) {
        //$result .= $this->drawSubProjects();
      }
      $result .="</td></tr></table>";
      return $result;
    } 
  }
  
  public function save() {
    global $doNotUpdateAllVersionProject;
    $new=($this->id)?false:true;
    $old=$this->getOld();
    if (!$this->idle) {
      $vers=new Version($this->idVersion,true);
      if ($vers->idle) $this->idle=1;
    }
    if (!$this->idle) {  
      $proj=new Version($this->idProject,true);
      if ($proj->idle) $this->idle=1;
      
    }
    $result=parent::save();
    if (!$doNotUpdateAllVersionProject) {
      if ($new) { // On new link Version<->Project, must create VersionProject for components of Product
        $v=new Version($this->idVersion,true);
        if ($v->scope=='Product') {
          $p=new Product($v->idProduct,true);
          $compList=$p->getComposition(false,true);
          foreach ($compList as $compId=>$compName) {
            $comp=new Component($compId,true);
            $comp->updateAllVersionProject();
          } 
        }
      } else if ($this->idProject!=$old->idProject or $this->idVersion!=$old->idVersion) {
        $v=new Version($this->idVersion,true);
        if ($v->scope=='Product') {
          $p=new Product($v->idProduct,true);
          $compList=$p->getComposition(false,true);
          if ($this->idVersion!=$old->idVersion) {
            $v=new Version($old->idVersion,true);
            $p=new Product($v->idProduct,true);
            $compList=array_merge_preserve_keys($p->getComposition(false,true),$compList);
          }
          foreach($compList as $compId=>$compName) {
            $comp=new Component($compId,true);
            $comp->updateAllVersionProject();
          } 
        }
      }
    }
    if (! $old->id) { // On creation of link Product-Version : refresh rights
      User::resetVisibleVersions();
    }
    return $result;
  }
  public function delete() {
    global $doNotUpdateAllVersionProject;
    $result=parent::delete();
    if (!$doNotUpdateAllVersionProject) {
      $v=new Version($this->idVersion,true);
      $p=new Product($v->idProduct,true);
      $compList=$p->getComposition(false,true);
      foreach ($compList as $compId=>$compName) {
        $comp=new Component($compId,true);
        $comp->updateAllVersionProject();
      }
    }
    User::resetVisibleVersions();
    return $result;
  }

  public function control() {
  	$result="";
  	if (! $this->id) {
  	  $crit=array('idProject'=>$this->idProject, 'idVersion'=>$this->idVersion);
  	  $list=$this->getSqlElementsFromCriteria($crit, false,null,null,null,true);
  	  if (count($list)>0) {
        $result.='<br/>' . i18n('errorDuplicateVersionProject');
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
  
  public static function updateIdle($type,$id) {
    $vp=new VersionProject();
    $vps=$vp->getSqlElementsFromCriteria(array("id".$type=>$id, "idle"=>'0'), false,null,null,null,true);
    foreach ($vps as $vp) {
      $vp->idle=1;
      $vp->save();
    }
  }
  
  public function propagateCreationToComponentVersions() {
    $pv=new ProductVersion($this->idVersion,true);
    $listCV=$pv->getComposition(false,true);
    $listIn="(0";
    foreach ($listCV as $cv=>$cvId) {
      $listIn.=",".$cvId;
    }
    $listIn.=')';
    $vp=new VersionProject();
    $listVP=$vp->getSqlElementsFromCriteria(null,null,"idProject=$this->idProject and idVersion in $listIn");
    $listVPkey=array();
    foreach ($listVP as $vp) {
      $listVPkey[$vp->idVersion]=$vp->idVersion;
    }
    foreach ($listCV as $cv) {
      if (! isset($listVPkey[$cv])) {
        $vp=new VersionProject();
        $vp->idProject=$this->idProject;
        $vp->idVersion=$cv;
        $vp->idle=$this->idle;
        $vp->startDate=$this->startDate;
        $vp->endDate=$this->endDate;
        $vp->save();
      }
    }  
  }
  public function propagateDeletionToComponentVersions() {
    $pv=new ProductVersion($this->idVersion,true);
    $listCV=$pv->getComposition(false,true);
    $listIn="(0";
    foreach ($listCV as $cv=>$cvId) {  
        $listIn.=",".$cvId;
    }
    $listIn.=')';
    $vp=new VersionProject();
    $listVP=$vp->getSqlElementsFromCriteria(null,null,"idProject=$this->idProject and idVersion in $listIn");
    foreach ($listVP as $vp) {
      $vp->delete();
    }
  }
}
?>