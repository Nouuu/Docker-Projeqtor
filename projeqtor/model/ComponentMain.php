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
 * Component splits pruduct into elementary objects. A component car participate to several Components
 * Almost all other objects are linked to a given project.
 */ 
require_once('_securityCheck.php');
class ComponentMain extends ProductOrComponent {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $scope;
  public $name;
  public $idComponentType;
  public $designation;
  public $idResource;
  public $idComponent;
  public $creationDate;
  public $idUser;
  public $idStatus; //ADD qCazelles - Ticket #53
  public $idle;
  public $description;
  public $_sec_ComponentVersions;
  public $_spe_versions;
  public $_sec_ComponentStructure;
  public $_componentStructure=array();
  public $_sec_ComponentComposition;
  public $_componentComposition=array(); 
  public $_sec_Tenders;
  public $_spe_tenders;
  public $_sec_language;
  public $_productLanguage;
  public $_sec_context;
  public $_productContext;
  public $_sec_Link;
  public $_Link = array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="35%" >${componentName}</th>
    <th field="designation" width="25%" >${identifier}</th>
    <th field="nameComponentType" width="15%" >${type}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

   private static $_fieldsAttributes=array("name"=>"required",
      "scope"=>"hidden", 
       "idClient"=>"hidden", 
       "idContact"=>"hidden", 
       "idProduct"=>"hidden", 
       "idComponent"=>"hidden",
       "idStatus"=>"required", //ADD qCazelles - Ticket #53
       "idComponentType"=>"required", // ADD PBE - Ticket #53
       "idProductType"=>"hidden"
  );   

  private static $_colCaptionTransposition = array('idContact'=>'contractor',
      'idComponent'=>'isSubComponentOf',
      "designation"=>"identifier",
      'idResource'=>'responsible'
  );
  private static $_databaseColumnName = array('idComponent'=>'idProduct');
  
  private static $_databaseTableName = 'product';
  private static $_databaseCriteria = array('scope'=>'Component');
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
    global $print, $showClosedItems;
    $result="";
    if ($item=='versions' or $item=='versionsWithProjects') {
      $showClosedVersions=(Parameter::getUserParameter('showClosedVersions')!='0')?true:false;
      $page="objectDetail";
      $result='<table style="width:100%;"><tr>';
      $result.= '<td class="linkHeader" style="width:15%">'.i18n('colId').'</td>';
      $result.= '<td class="linkHeader" style="width:60%">'.i18n('colName').'</td>';
      $result.= '<td class="linkHeader" style="width:20%">'.i18n('Status').'</td>';
      $result.= '</tr>';
      if (isset($_REQUEST['page'])) $page=substr( basename($_REQUEST['page']) , 0, strpos(basename($_REQUEST['page']),'.php'));
      if ($page!='objectDetail') $showClosedVersions=(Parameter::getUserParameter('structureShowClosedItems')!='0')?true:false;
      if ($this->id) {
        $vers=new ComponentVersion();
        $crit=array('idComponent'=>$this->id);
        if (! $showClosedVersions) $crit['idle']='0';
        $result .= $vers->drawVersionsList($crit,($item=='versionsWithProjects')?true:false);
      }
      $result.="</table>";
      // $resultSC is here to store the "Show Closed" part, it allows to move the checkbox more easily
      if (!$print) {
        $resultSC='<div style="position:absolute;right:5px;top:3px;">';
        $resultSC.='<label for="showClosedVersions"  class="dijitTitlePaneTitle" style="border:0;font-weight:normal !important;height:'.((isNewGui())?'20':'10').'px;width:'.((isNewGui())?'50':'150').'px">'.i18n('labelShowIdle'.((isNewGui())?'Short':'')).'&nbsp;</label>';
        $resultSC.='<div class="whiteCheck" id="showClosedVersions" style="'.((isNewGui())?'margin-top:14px':'').'" dojoType="dijit.form.CheckBox" type="checkbox" '.(($showClosedVersions)?'checked':'');
        $resultSC.=' title="'.i18n('labelShowIdle').'" >';
        $resultSC.='<script type="dojo/connect" event="onChange" args="evt">';
        $resultSC.=' saveUserParameter("showClosedVersions",((this.checked)?"1":"0"));';
        $resultSC.=' if (checkFormChangeInProgress()) {return false;}';
        $resultSC.=' loadContent("objectDetail.php", "detailDiv", "listForm");';
        $resultSC.='</script>';
        $resultSC.='</div>';
      } else {
        $resultSC='';
      }
      return $result.$resultSC;
    } else {
      if ($item=='tenders') {
        Tender::drawListFromCriteria('id'.get_class($this),$this->id);
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
    if ($this->id and $this->id==$this->idComponent) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    } else if ($this->idComponent){
    	$parent=new Component($this->idComponent,true);
    	while ($parent->id) {
    	  if ($parent->id==$this->id) {
          $result.='<br/>' . i18n('errorHierarchicLoop');
          break;
        }
        $parent=new Component($parent->idComponent,true);
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
  
  public function getSubComponents($limitToActiveComponents=false) {
    if ($this->id==null or $this->id=='') {
      return array();
    }
    $crit=array();
  	$crit['idComponent']=$this->id;
    if ($limitToActiveComponents) {$crit['idle']='0';}
    $sorted=SqlList::getListWithCrit('Component',$crit,'name');
  	$subComponents=array();
    foreach($sorted as $prodId=>$prodName) {
      $subComponents[$prodId]=new Component($prodId,true);
    }
    return $subComponents;
  }
  public function getSubComponentsList($limitToActiveComponents=false) {
    if ($this->id==null or $this->id=='') {
      return array();
    }
    $crit=array();
    $crit['idComponent']=$this->id;
    if ($limitToActiveComponents) {$crit['idle']='0';}
    $sorted=SqlList::getListWithCrit('Component',$crit,'name');
    return $sorted;
  }
  
  /** ==========================================================================
   * Recusively retrieves all the hierarchic sub-Components of the current Component
   * @return an array containing id, name, subComponents (recursive array)
   */
  public function getRecursiveSubComponents($limitToActiveComponents=false) {
    $crit=array('idComponent'=>$this->id);
    if ($limitToActiveComponents) {
      $crit['idle']='0';
    }
    $obj=new Component();
    $subComponents=$obj->getSqlElementsFromCriteria($crit, false,null,null,null,true) ;
    $subComponentList=null;
    foreach ($subComponents as $subProd) {
      $recursiveList=null;
      $recursiveList=$subProd->getRecursiveSubComponents($limitToActiveComponents);
      $arrayProd=array('id'=>$subProd->id, 'name'=>$subProd->name, 'subItems'=>$recursiveList);
      $subComponentList[]=$arrayProd;
    }
    return $subComponentList;
  }
  
  /** ==========================================================================
   * Recusively retrieves all the sub-Components of the current Component
   * and presents it as a flat array list of id=>name
   * @return an array containing the list of subComponents as id=>name 
   */
  public function getRecursiveSubComponentsFlatList($limitToActiveComponents=false, $includeSelf=false) {
  	$tab=$this->getSubComponentsList($limitToActiveComponents);
    $list=array();
    if ($includeSelf) {
      $list[$this->id]=$this->name;
    }
    if ($tab) {
      foreach($tab as $id=>$name) {
        $list[$id]=$name;
        $subobj=new Component();
        $subobj->id=$id;
        $sublist=$subobj->getRecursiveSubComponentsFlatList($limitToActiveComponents);
        if ($sublist) {
          $list=array_merge_preserve_keys($list,$sublist);
        }
      }
    }
    return $list;
  }
  
  public function updateAllVersionProject() {
    global $doNotUpdateAllVersionProject;
    if ($doNotUpdateAllVersionProject) return; // This will avoid unexpected recursive call in some cases
    $vers=new ComponentVersion();
    $versList=$vers->getSqlElementsFromCriteria(array('idComponent'=>$this->id),null,null,null,null,true); // List all versions of the component
    foreach ($versList as $vers) {
      $existing=$vers->getLinkedProjects(false); // List of projects linked to the version of the component
      $target=array(); // Will list of project that should be linked to the version of the component
      $productVersions=$vers->getLinkedProductVersions(false); // List all product versions using this component version
      foreach ($productVersions as $pvId) {
        $pv=new ProductVersion($pvId,true);
        $arr=$pv->getLinkedProjects(false);
        $target=array_merge_preserve_keys($target,$arr); // If product version is linked to project, component version should also be linked
      }
      foreach ($existing as $projId) {
        if (! in_array($projId,$target)) { // Existing not in target => delete VersionProject for all versions
          $vp=SqlElement::getSingleSqlElementFromCriteria('VersionProject', array('idProject'=>$projId,'idVersion'=>$vers->id),true);
          if ($vp->id) {
            $res=$vp->delete();
          }
        }
      }
      foreach ($target as $projId) { 
        $vp=SqlElement::getSingleSqlElementFromCriteria('VersionProject', array('idProject'=>$projId,'idVersion'=>$vers->id),true);
        if (! $vp->id) { // targt not existing yet : create it
          $res=$vp->save();
        }
      }
    }
  }
  
  public function getComposition($withName=true,$reculsively=false) {
    $ps=new ProductStructure();
    $psList=$ps->getSqlElementsFromCriteria(array('idProduct'=>$this->id),null,null,null,null,true);
    $result=array();
    foreach ($psList as $ps) {
      $result[$ps->idComponent]=($withName)?SqlList::getNameFromId('Component', $ps->idComponent):$ps->idComponent;
      if ($reculsively) {
        $comp=new Component($ps->idComponent,true);
        $result=array_merge_preserve_keys($comp->getComposition($withName,true),$result);
      }
    }
    return $result;
  }
  
  public static function canViewComponentList($obj=null) {
    //return securityGetAccessRightYesNo('menuComponent', 'read', null, null);
    $user=getSessionUser();
    $habil=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile' => $user->getProfile($obj),'scope' => 'viewComponents'));
    if ($habil) {
      $list=new ListYesNo($habil->rightAccess);
      return $list->code;
    }
    return 'NO';
  }
  public function delete() {
    $result=parent::delete();
    $ps=new ProductStructure();
    $crit=array('idProduct'=>$this->id);
    $list=$ps->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $ps) {
      $ps->delete();
    }
    $crit=array('idComponent'=>$this->id);
    $list=$ps->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $ps) {
      $ps->delete();
    }
    return $result;
  }
  public function copy() {
    $result=parent::copy();
  
    $ps=new ProductStructure();
    $crit=array('idProduct'=>$this->id);
    $list=$ps->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $ps) {
      $ps->idProduct=$result->id;
      $ps->id=null;
      $ps->creationDate=date('Y-m-d');
      $ps->save();
    }
    $crit=array('idComponent'=>$this->id);
    $list=$ps->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $ps) {
      $ps->idComponent=$result->id;
      $ps->id=null;
      $ps->creationDate=date('Y-m-d');
      $ps->save();
    }
    // Copy language
    $lang = new ProductLanguage();
    $listLang=$lang->getSqlElementsFromCriteria(array('idProduct'=>$this->id),null,null,null,null,true);
    foreach($listLang as $lang){
      $lang->id = NULL;
      $lang->idProduct = $result->id;
      $lang->scope = $result->scope; //Add mOlives - bugLanguage - 19/04/2018
      $lang->save();
    }
    return $result;
  }
}
?>