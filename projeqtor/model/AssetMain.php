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
 * Client is the owner of a project.
 */  
require_once('_securityCheck.php'); 
class AssetMain extends SqlElement {

  public $_sec_Description;
  public $id;
  public $name;
  public $idAssetType;
  public $idBrand;
  public $idModel;
  public $idProvider;
  public $idAssetCategory;
  public $idAsset;
  public $serialNumber;
  public $inventoryNumber;
  public $description;
  public $_sec_Attribution;
  public $idStatus;
  public $_tab_2_1 = array('installationDate','decommissioningDate','date');
  public $installationDate;
  public $decommissioningDate;
  public $idLocation;
  public $complement;
  public $_spe_fullNameLocation;
  public $idAffectable;
  public $idUser;
  //public $idResource;
  public $creationDateTime;
  public $lastUpdateDateTime;
  public $idle;
  public $_sec_Cost;
  public $_tab_2_1_3 = array('untaxedAmount','fullAmount','purchaseValue');
  public $purchaseValueHTAmount;
  public $purchaseValueTTCAmount;
  public $warantyDurationM;
  public $warantyEndDate;
  public $depreciationDurationY;
  public $needInsurance;
  public $_sec_AssetComposition;
  public $_assetComposition=array();
  public $_spe_arboAsset;
  public $_sec_ComponentVersionStructureAsset;
  public $_componentVersionStructureAsset=array();
  public $_sec_Link;
  public $_Link = array();
  public $_Attachment = array();
  public $_Note = array();
  public $_nbColMax = 3;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="nameAssetType" formatter="iconName22" width="10%">${type}</th>
    <th field="name" width="15%">${name}</th>
    <th field="nameBrand" width="10%">${idBrand}</th>
    <th field="nameModel" width="10%">${idModel}</th>
    <th field="serialNumber" width="10%">${serialNumber}</th>
    <th field="nameAsset" width="10%">${idAsset}</th>
    <th field="nameLocation" width="10%">${idLocation}</th>
    <th field="nameAffectable" formatter="thumbName22" width="10%">${idUser}</th>
    <th field="colorNameStatus" width="6%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="idle" width="4%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_colCaptionTransposition = array('idAffectable' => 'user','idAsset' => 'parentAsset');
  
  private static $_fieldsAttributes=array(
      'name'=>'required',
      'idUser'=>'hidden',
      //'idResource'=>'hidden',
      'idAssetType'=>'required',
      'idStatus'=>'required',
      "installationDate"=>"nobr",
      "idLocation"=>"nobr"
  );
  
  private static $_databaseColumnName = array(
      //'idResource'=>'idAffectable'
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

  public function control(){
    $result="";
    if ($this->id == $this->idAsset and $this->id)$result .= '<br/>' . i18n ( 'assetParentCanNotBeHimself' );
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  

  public function delete() {
    $result=parent::delete();
    if (getLastOperationStatus($result)=='OK') {
      $asset = new Asset();
      $listSubAsset = $asset->getSqlElementsFromCriteria(array('idAsset'=>$this->id));
      foreach ($listSubAsset as $ass){
        $ass->idAsset = null;
        $ass->save();
      }
    }
    return $result;
  }
  
  public function copyTo($newClass, $newType, $newName, $newProject, $structure, $withNotes, $withAttachments, $withLinks, $withAssignments = false, $withAffectations = false, $toProject = NULL, $toActivity = NULL, $copyToWithResult = false,$copyToWithVersionProjects=false) {
    $result=parent::copyTo($newClass, $newType, $newName, $newProject, $structure, $withNotes, $withAttachments, $withLinks);
    if ($newClass=='Asset' and $structure) {
      $productAsset = new ProductAsset();
      $assList=$productAsset->getSqlElementsFromCriteria(array('idAsset'=>$this->id));
      foreach ($assList as $list){
        $prAss = new ProductAsset();
        $prAss->idAsset = $result->id;
        $prAss->idProductVersion = $list->idProductVersion;
        $prAss->save();
      }
      $ass=new Asset();
      $assList=$ass->getSqlElementsFromCriteria(array('idAsset'=>$this->id));
      foreach($assList as $val) {
        $val->idAsset = $result->id;
        $val->copyTo($newClass, $newType, $val->name, $newProject, $structure, $withNotes, $withAttachments, $withLinks);
      }
    }
    return $result;
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
    if ($colName=="idBrand") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var idBrand=dijit.byId("idBrand").get("value");';
      $colScript .= '  var idModel=dijit.byId("idModel").get("value");';
      $colScript .= '  if(idBrand == " "){';
      $colScript .= '   refreshList("idModel","idAssetType", dijit.byId("idAssetType").get("value"), idModel, null, false);';
      $colScript .= '  }else{';
      $colScript .= '   if(dijit.byId("idAssetType").get("value")!= " "){';
      $colScript .= '     refreshList("idModel","idBrand", this.value, idModel, null, false,"idAssetType",dijit.byId("idAssetType").get("value"));';
      $colScript .= '   }else{';
      $colScript .= '     refreshList("idModel","idBrand", this.value, idModel, null, false);';
      $colScript .= '   }';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if($colName=="idAssetType"){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= ' var idBrand=dijit.byId("idBrand").get("value");';
      $colScript .= ' var idModel=dijit.byId("idModel").get("value");';
      $colScript .= ' refreshList("idBrand","idAssetType", this.value, idBrand, null, false);';
      $colScript .= ' if(dijit.byId("idBrand").get("value")!= " "){';
      $colScript .= '  refreshList("idModel","idAssetType", this.value, idModel, null, false, "idBrand",dijit.byId("idBrand").get("value"));';
      $colScript .= ' }else{';
      $colScript .= '  refreshList("idModel","idAssetType", this.value, idModel, null, false);';
      $colScript .= ' }';
      $colScript .= ' formChanged();';
      $colScript .= '</script>';
    } else if($colName=="idModel"){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= ' if(dijit.byId("idBrand").get("value")== " " && this.value){';
      $colScript .= '    dojo.xhrGet({';
      $colScript .= '      url: "../tool/getSingleData.php?dataType=brandOfModel&idModel=" + this.value,';
      $colScript .= '      handleAs: "text",';
      $colScript .= '      load: function (data) {dijit.byId("idBrand").set("value",data);}';
      $colScript .= '    });';
      $colScript .= '  };';
      $colScript .= ' formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;
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
  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
    global $print;
    $result = "";
    if($item == 'arboAsset'){
      if ($print or !$this->id) return "";
      $result='<br/><table>';
      $result.='<tr>';
      $result.='<td rowspan="2" style="padding-left:10px">';
      $result.='<button id="showStructureButton" dojoType="dijit.form.Button" showlabel="true"';
      $result.=' class="roundedVisibleButton" title="'.i18n('showStructure').'" style="vertical-align: middle;">';
      $result.='<span>' . i18n('showStructure') . '</span>';
      $result.='<script type="dojo/connect" event="onClick" args="evt">';
      $page="../view/assetStructure.php?id=$this->id";
      $result.="var url='$page';";
      $result.='showPrint(url, "asset", null, "html", "P");';
      $result.='</script>';
      $result.='</button>';
      $result.='</div></td>';
      $result.='</tr></table>';
    }elseif($item=="fullNameLocation"){
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('colLocation') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if($this->idLocation){ 
        $location= new Location($this->idLocation); 
        $locationArray = array_reverse($location->getLocationFullName());
        foreach ( $locationArray as $name){
          $result .= $name.'  -  ';
        }
        $result .= $location->name;
      }
      $result .= '</td></tr></table>';
    }
    return $result;
  }
  
  
  public function getRecursiveSubAsset(){
    $crit=array('idAsset'=>$this->id);
    $obj=new Asset();
    $subProducts=$obj->getSqlElementsFromCriteria($crit, false,null,null,null,true) ;
    $subProductList=null;
    foreach ($subProducts as $subProd) {
      $recursiveList=null;
      $recursiveList=$subProd->getRecursiveSubAsset();
      $arrayProd=array('id'=>$subProd->id, 'name'=>$subProd->name, 'subItems'=>$recursiveList);
      $subProductList[]=$arrayProd;
    }
    return $subProductList;
  }
  
  public function getParentAsset() {
    $result=array();
    if ($this->idAsset) {
      $parent=new Asset($this->idAsset);
      $result=array_merge_preserve_keys($parent->getParentAsset(),array($parent->id=>$parent->name));
    }
    return $result;
  }
  
  public function isElementary(){
    $result = true;
    $cpt = $this->countSqlElementsFromCriteria(array('idAsset'=>$this->id));
    if($cpt > 0)$result = false;
    return $result;
  }
}
?>