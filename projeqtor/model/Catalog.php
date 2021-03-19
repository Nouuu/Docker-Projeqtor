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
 * ProductType defines the type of a Product.
*/
require_once('_securityCheck.php');
class Catalog extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place
  public $name;
  public $idCatalogType;
  public $description;
  public $idle;
  public $detail;
  public $nomenclature;
  public $specification;
  public $_sec_treatment;
  public $unitCost;
  public $idMeasureUnit;
  public $quantity;
  public $idProduct;
  public $idProductVersion;
  
  // Define the layout
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="20%">${name}</th>
    <th field="description" width="40%" >${description}</th>
    <th field="unitCost" width="10%" formatter="costFormatter">${unitPrice}</th>
    <th field="nameMeasureUnit" width="10%" formatter="nameFormatter">${idMeasureUnit}</th>
  	<th field="quantity" width="10%" formatter="decimalFormatter">${quantity}</th>
    ';
  
  //required and hidden attribute
  private static $_fieldsAttributes=array("name"=>"required",
      "idle"=>"hidden",
      "description"=>"required",
      "idCatalogType"=>"required",
      "unitCost"=>"nobr",
  		"idMeasureUnit"=>""
  );
  
  private static $_colCaptionTransposition = array('unitCost'=>'unitPrice'
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
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
}
?>