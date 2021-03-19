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
class ProductMain extends ProductOrComponent {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $scope;
  public $name;
  public $idProductType;
  public $designation;
  public $idClient;
  public $idContact;
  public $idResource;
  public $idProduct;
  public $creationDate;
  public $idUser;
  public $idStatus; //ADD qCazelles - Ticket #53
  public $idle;
  public $description;
  public $_sec_ProductprojectProjects;
  public $_ProductProject=array();
  public $_sec_ProductVersions;
  public $_spe_versions;
  public $_sec_SubProducts;
  public $_spe_subproducts;
  public $_sec_ProductComposition;
  public $_productComposition;
  public $_spe_structure;
  public $_sec_ProductBusinessFeatures; // ADD qCazelles
  public $_productBusinessFeatures; // ADD qCazelles
  public $_sec_language;
  public $_productLanguage;
  public $_sec_context;
  public $_productContext;
  public $_sec_Tenders;
  public $_spe_tenders;
  public $_sec_Link;
  public $_Link = array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  private static $_composition=array();


  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="25" >${productName}</th>
    <th field="designation" width="20%" >${designation}</th>
    <th field="nameProductType" width="10%" >${type}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameProduct" width="15%" >${isSubProductOf}</th>
    <th field="nameClient" width="10%" >${clientName}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

   private static $_fieldsAttributes=array(
       "name"=>"required", 
       "scope"=>"hidden",
       "idStatus"=>"required", //ADD qCazelles - Ticket #53
       "idProductType"=>"required", // ADD PBE - Ticket #53
       "idComponentType"=>"hidden"
  );   

  private static $_colCaptionTransposition = array('idContact'=>'contractor','idProduct'=>'isSubProductOf',
      'idResource'=>'responsible'
  );
  
  private static $_databaseTableName = 'product';
  private static $_databaseCriteria = array('scope'=>'Product');
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
      $page="objectDetail";
      if (isset($_REQUEST['page'])) $page=substr( basename($_REQUEST['page']) , 0, strpos(basename($_REQUEST['page']),'.php'));
      $showClosedVersions=(Parameter::getUserParameter('showClosedVersions')!='0')?true:false;
      if ($page!='objectDetail') $showClosedVersions=(Parameter::getUserParameter('structureShowClosedItems')!='0')?true:false;
      $result='<table style="width:100%;"><tr>';
      $result.= '<td class="linkHeader" style="width:15%">'.i18n('colId').'</td>';
      $result.= '<td class="linkHeader" style="width:60%">'.i18n('colName').'</td>';
      $result.= '<td class="linkHeader" style="width:20%">'.i18n('Status').'</td>';
      $result.= '</tr>';
      if ($this->id) {
        $vers=new ProductVersion();
        if(!$showClosedVersions){
          $crit=array('idProduct'=>$this->id,'idle'=>'0');
        }else{
          $crit=array('idProduct'=>$this->id);
        }
        $result .= $vers->drawVersionsList($crit,($item=='versionsWithProjects')?true:false);
      }
      $result.='</table>';
      // $resultSC is here to store the "Show Closed" part, it allows to move the checkbox more easily
      $resultSC='';
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
        $resultSC.='</div></div>';
      } else {
        $resultSC='';
      }
      return $result.$resultSC;
    } elseif ($item=='subproducts') {
      $result .="<table><tr>";
      //$result .="<td class='label' valign='top'><label>" . i18n('versions') . "&nbsp;:&nbsp;</label></td>";
      $result .="<td>";
      if ($this->id) {
        $result .= $this->drawSubProductsList();
        
      }
      $result .="</td></tr></table>";
      return $result;
    } else if ($item=='structure' and !$print and $this->id) {
      $result=parent::drawStructureButton('Product',$this->id);
      return $result;
    } else if ($item=='tenders') {
      Tender::drawListFromCriteria('id'.get_class($this),$this->id);
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
    if ($this->id and $this->id==$this->idProduct) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    } else if ($this->idProduct){
    	$parent=new Product($this->idProduct,true);
    	while ($parent->id) {
    	  if ($parent->id==$this->id) {
          $result.='<br/>' . i18n('errorHierarchicLoop');
          break;
        }
        $parent=new Product($parent->idProduct,true);
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
  
  public function getSubProducts($limitToActiveProducts=false) {
    if ($this->id==null or $this->id=='') {
      return array();
    }
    $crit=array();
  	$crit['idProduct']=$this->id;
    if ($limitToActiveProducts) {$crit['idle']='0';}
    $sorted=SqlList::getListWithCrit('Product',$crit,'name');
  	$subProducts=array();
    foreach($sorted as $prodId=>$prodName) {
      $subProducts[$prodId]=new Product($prodId);
    }
    return $subProducts;
  }
  public function getSubProductsList($limitToActiveProducts=false) {
    if ($this->id==null or $this->id=='') {
      return array();
    }
    $crit=array();
    $crit['idProduct']=$this->id;
    if ($limitToActiveProducts) {$crit['idle']='0';}
    $sorted=SqlList::getListWithCrit('Product',$crit,'name');
    return $sorted;
  }
  
  /** ==========================================================================
   * Recusively retrieves all the hierarchic sub-products of the current product
   * @return an array containing id, name, subproducts (recursive array)
   */
  public function getRecursiveSubProducts($limitToActiveProducts=false) {
    $crit=array('idProduct'=>$this->id);
    if ($limitToActiveProducts) {
      $crit['idle']='0';
    }
    $obj=new Product();
    $subProducts=$obj->getSqlElementsFromCriteria($crit, false,null,null,null,true) ;
    $subProductList=null;
    foreach ($subProducts as $subProd) {
      $recursiveList=null;
      $recursiveList=$subProd->getRecursiveSubProducts($limitToActiveProducts);
      $arrayProd=array('id'=>$subProd->id, 'name'=>$subProd->name, 'subItems'=>$recursiveList);
      $subProductList[]=$arrayProd;
    }
    return $subProductList;
  }
  
  /** ==========================================================================
   * Recusively retrieves all the sub-Products of the current Product
   * and presents it as a flat array list of id=>name
   * @return an array containing the list of subProducts as id=>name 
   */
  public function getRecursiveSubProductsFlatList($limitToActiveProducts=false, $includeSelf=false) {
  	$tab=$this->getSubProductsList($limitToActiveProducts);
    $list=array();
    if ($includeSelf) {
      $list[$this->id]=$this->name;
    }
    if ($tab) {
      foreach($tab as $id=>$name) {
        $list[$id]=$name;
        $subobj=new Product();
        $subobj->id=$id;
        $sublist=$subobj->getRecursiveSubProductsFlatList($limitToActiveProducts);
        if ($sublist) {
          $list=array_merge_preserve_keys($list,$sublist);
        }
      }
    }
    return $list;
  }

  public function getParentProducts() {
    $result=array();
    if ($this->idProduct) {
      $parent=new Product($this->idProduct);
      $result=array_merge_preserve_keys($parent->getParentProducts(),array($parent->id=>$parent->name));
    } 
    return $result;
  } 
  
  // Retrive composition in terms of components (will not retreive products in the composition of the product)
  public function getComposition($withName=true,$reculsively=false) {
// PB : Performance optimization   
    return self::getStaticComposition($this->id,$withName,$reculsively);
//     $result=array();
//     $ps=new ProductStructure();
//     $psList=$ps->getSqlElementsFromCriteria(array('idProduct'=>$this->id),null,null,null,null,true);
//     foreach ($psList as $ps) {
//       $result[$ps->idComponent]=($withName)?SqlList::getNameFromId('Component', $ps->idComponent):$ps->idComponent;
//       if ($reculsively) {
//         $comp=new Component($ps->idComponent,true);
//         $result=array_merge_preserve_keys($comp->getComposition($withName,true),$result);
//       }
//     }
//     return $result;
  }
  public function getStaticComposition($id,$withName=true,$reculsively=false) {
    $key=$id.'|'.(($withName)?'1':'0').'|'.(($reculsively)?'1':'0');
    if (isset(self::$_composition[$key])) return self::$_composition[$key];
    $result=array();
    $psList=SqlList::getListWithCrit('ProductStructure', array('idProduct'=>$id),'idComponent');
    foreach ($psList as $id=>$idComp) {
      $result[$idComp]=($withName)?SqlList::getNameFromId('Component',$idComp):$idComp;
      if ($reculsively) {
        $result=array_merge_preserve_keys(self::getStaticComposition($idComp,$withName,true),$result);
      }
    }
    self::$_composition[$key]=$result;
    return $result;
  }
  
  public function drawSubProductsList() {
    $result="<table>";
    $list=$this->getSubProducts();
    foreach ($list as $prod) {
      $result.= '<tr>';
      $result.= '<td valign="top" width="20px" style="padding-left:15px;"><img src="css/images/iconProduct16.png" height="16px" /></td>';
      $style="";
      if ($prod->idle) {$style='color#5555;text-decoration: line-through;';}
      $result.= '<td style="vertical-align:top;'.$style.'">';
      $result.="#$prod->id - ".htmlDrawLink($prod);
      $result.= '</td></tr>';
    }
    $result .="</table>";
    return $result;
  }
  
  public function delete() {
    $result=parent::delete();
    $ps=new ProductStructure();
    $crit=array('idProduct'=>$this->id);
    $list=$ps->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $ps) {
      $ps->delete();
    }
    return $result;
  }
  // Ticket 2325 Kevin
  public function copy() {
    $result=parent::copy();
  
    $pp=new ProductProject();
    $crit=array('idProduct'=>$this->id);
    $list=$pp->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $pp) {
      $pp->idProduct=$result->id;
      $pp->id=null;
      $pp->save();
    }
    $ps=new ProductStructure();
    $crit=array('idProduct'=>$this->id);
    $list=$ps->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $ps) {
      $ps->idProduct=$result->id;
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