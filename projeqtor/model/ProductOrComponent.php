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
 * ProductComponent is common class shared by Product and Component
 * Almost all other objects are linked to a given project.
 */ 
require_once('_securityCheck.php');
class ProductOrComponent extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $designation;
  public $idClient;
  public $idContact;
  public $idProduct;
  public $idProductType; //ADD PBE - Ticket #53
  public $idComponentType; //ADD PBE - Ticket #53
  public $creationDate;
  public $idStatus; //ADD qCazelles - Ticket #53
  public $idle;
  public $description;
  public $_Attachment=array();
  public $_Note=array();
  public $scope;
  //ADD qCazelles - LANG2
  public $_sec_language;
  public $_productLanguage;
  public $_sec_context;
  public $_productContext;
  //public $_sec_void;
  //END ADD qCazelles - LANG2

  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%" ># ${id}</th>
    <th field="name" width="25%" >${productName}</th>
  	<th field="designation" width="15%" >${designation}</th>
    <th field="nameProduct" width="25%" >${isSubProductOf}</th>
    <th field="nameClient" width="15%" >${clientName}</th>
    <th field="idle" width="10%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required",
      "scope"=>"hidden",
      "idStatus"=>"required" //ADD qCazelles - Ticket #53
  );   

  private static $_colCaptionTransposition = array('idContact'=>'contractor','idProduct'=>'isSubProductOf'
  );

  private static $_databaseTableName = 'product';
  
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
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */

  
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
    $subProducts=$obj->getSqlElementsFromCriteria($crit, false) ;
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
  static protected function drawStructureButton($class,$id) {
    global $print;
    $showVersionsForAll=(Parameter::getUserParameter('strucutreShowVersionsForAll')!='0')?true:false;
    $showProjectsLinked=(Parameter::getUserParameter('strucutreShowProjectsLinked')!='0')?true:false;
    $showClosedItems=(Parameter::getUserParameter('structureShowClosedItems')!='0')?true:false;
    if ($print) return "";
    $result='<br/><table>';
    $result.='<tr>';
    $result.='<td style="white-space:nowrap;padding-right:10px;"><label for="showVersionsForAll" style="width:300px">'.i18n('showVersionsForAll').'&nbsp;</label>';
    $result.='<div id="showVersionsForAll" dojoType="dijit.form.CheckBox"  style="'.((isNewGui())?'margin-top:5px':'').'" type="checkbox" '.(($showVersionsForAll)?'checked':'').' >';
    $result.='<script type="dojo/connect" event="onChange" args="evt">';
    $result.=' saveUserParameter("strucutreShowVersionsForAll",((this.checked)?"1":"0"));';
    $result.='</script>';
    $result.='</div></td>';
    $result.='<td rowspan="3" style="padding-left:10px">';
    $result.='<button id="showStructureButton" dojoType="dijit.form.Button" showlabel="true"';
    $result.=' title="'.i18n('showStructure').'" class="roundedVisibleButton" style="vertical-align: middle;">';
    $result.='<span>' . i18n('showStructure') . '</span>';
    $result.='<script type="dojo/connect" event="onClick" args="evt">';
    $page="../report/productStructure.php?objectClass=$class&objectId=$id";
    $result.="var url='$page';";
    $result.='url+="&showVersionsForAll="+((dijit.byId("showVersionsForAll").get("checked"))?"1":"0");';
    $result.='url+="&showProjectsLinked="+((dijit.byId("showProjectsLinked").get("checked"))?"1":"0");';
    $result.='url+="&showClosedItems="+((dijit.byId("showClosedItems").get("checked"))?"1":"0");';
    $result.='showPrint(url, null, null, "html", "P");';
    $result.='</script>';
    $result.='</button><br/>';
    //$result.='&nbsp;';
    $result.='<button id="showFlatStructureButton" dojoType="dijit.form.Button" showlabel="true"';
    $result.=' title="'.i18n('showFlatStructure').'" class="roundedVisibleButton" style="vertical-align: middle;">';
    $result.='<span>' . i18n('showFlatStructure') . '</span>';
    $result.='<script type="dojo/connect" event="onClick" args="evt">';
    $page="../report/productFlatStructure.php?objectClass=$class&objectId=$id";
    $result.="var url='$page';";
    $result.='url+="&showClosedItems="+((dijit.byId("showClosedItems").get("checked"))?"1":"0");';
    $result.='url+="&format=print";';
    $result.='showPrint(url, null, null, "html", "P");';
    $result.='</script>';
    $result.='</button><br/>';
    $result.='<button id="showFlatStructureButtonCsv" dojoType="dijit.form.Button" showlabel="false" ';
    $result.=' title="'.i18n('showFlatStructure').'" iconClass="imageColorNewGui dijitButtonIcon dijitButtonIconCsv" class="detailButton ">';
    $result.='<script type="dojo/connect" event="onClick" args="evt">';
    $page="../report/productFlatStructure.php?objectClass=$class&objectId=$id";
    $result.="var url='$page';";
    $result.='url+="&format=csv";';
    $result.='showPrint(url, null, null, "csv", "P");';
    $result.='</script>';
    $result.='</button>';
    $result.='</td></tr>';
    $result.='<tr><td style="white-space:nowrap;padding-right:10px;"><label for="showProjectsLinked" style="width:300px">'.i18n('showProjectsLinked').'&nbsp;</label>';
    $result.='<div id="showProjectsLinked" dojoType="dijit.form.CheckBox" style="'.((isNewGui())?'margin-top:5px':'').'" type="checkbox" '.(($showProjectsLinked)?'checked':'').' >';
    $result.='<script type="dojo/connect" event="onChange" args="evt">';
    $result.=' saveUserParameter("strucutreShowProjectsLinked",((this.checked)?"1":"0"));';
    $result.='</script>';
    $result.='</div></td>';
    //gautier #2442
    $result.='<tr>';
    $result.='<td style="white-space:nowrap;padding-right:10px;"><label for="showClosedItems" style="width:300px">'.i18n('labelShowIdle').'&nbsp;</label>';
    $result.='<div id="showClosedItems" dojoType="dijit.form.CheckBox"  style="'.((isNewGui())?'margin-top:5px':'').'" type="checkbox" '.(($showClosedItems)?'checked':'').' >';
    $result.='<script type="dojo/connect" event="onChange" args="evt">';
    $result.=' saveUserParameter("structureShowClosedItems",((this.checked)?"1":"0"));';
    $result.='</script>';
    $result.='</div></td>';
    $result.='</tr></table>';
    //end
    //$result.='</tr></table>';
    return $result;
  }

}
?>