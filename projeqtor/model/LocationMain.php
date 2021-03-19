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
class LocationMain extends SqlElement {

  public $_sec_Description;
  public $id;
  public $name;
  public $idLocation;
  public $sortOrder=0;
  public $idle;
  public $description;
  public $_sec_Adress;
  public $designation;
  public $street;
  public $complement;
  public $zipCode;
  public $city;
  public $state;
  public $country;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="30%">${name}</th>
    <th field="sortOrder" width="5%">${sortOrderShort}</th> 
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_colCaptionTransposition = array('idLocation' => 'parentLocation');
  
  private static $_fieldsAttributes=array(
      'name'=>'required'
  );
  
  private static $_databaseColumnName = array();
  
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

  
  public function control() {
    $result = "";
    if ($this->id and $this->id == $this->idLocation) {
      $result .= '<br/>' . i18n ( 'errorHierarchicLoop' );
    }
    if($this->idLocation){
      $result .= $this->controlLocationHierarchicLoop($this->idLocation);
    }
    $defaultControl = parent::control ();
    if ($defaultControl != 'OK') {
      $result .= $defaultControl;
    }
    if ($result == "") {
      $result = 'OK';
    }
    return $result;
  }
  
  
  public function controlLocationHierarchicLoop($parentId) {
    $result="";
    $parent= new Location($parentId);
    $parentListObj=$parent->getLocationParentItemsArray();
    if (array_key_exists('#' . $this->id,$parentListObj)) {
      $result='<br/>' . i18n('errorHierarchicLoop');
      return $result;
    }
    return $result;
  }
  
  public function getLocationParentItemsArray() {
    $result=array();
    if ($this->idLocation) {
      $parent=new Location($this->idLocation);
      $result=$parent->getLocationParentItemsArray();
      $result['#' . $parent->id]=$parent;
    }
    return $result;
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
    $result="";
    return $result;
  }
  
  private static $locationFullNameArray=array();
  public function getLocationFullName(){
    if (isset(self::$locationFullNameArray[$this->name.'#'])) {
      return self::$locationFullNameArray[$this->name.'#'];
    }
    if(!$this->idLocation){
      return array();
    }else{
      $topLocation=new Location($this->idLocation);
      $topList=$topLocation->getLocationFullName();
      $result=array_merge(array($topLocation->name),$topList);
      self::$locationFullNameArray[$this->name]=$result;
    }
    return $result;
  }
}
?>