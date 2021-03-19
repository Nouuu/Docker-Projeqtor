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
 * Action is establised during meeting, to define an action to be followed.
 */ 
require_once('_securityCheck.php'); 
class Notifiable extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
    public $id;    // redefine $id to specify its visible place 
    public $notifiableItem;
    public $_spe_notifiableItem;
    public $name;
    public $idle;

    public $_isNameTranslatable = true;
    
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
    <th field="name" width="30" >${name}</th>
    <th field="idle" width="4%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array(
                                            "notifiableItem"             => "required, hidden",
                                            "name"                       => "hidden",
                                            "idle"                       => "nobr"
                                        );  
  
  private static $_colCaptionTransposition = array();
  
  private static $_databaseColumnName = array();
  
//    private static $_databaseTableName = '';
    
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
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    return $colScript;
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {

    //$spe_notifiableItem = RequestHandler::getValue('_spe_notifiableItem');    
    //$array_class = getUserVisibleObjectClassWithFieldDateType();
    //$this->notifiableItem = (isset($array_class[$spe_notifiableItem]))?$_spe_notifiableItem:'';
    $this->name = i18n($this->notifiableItem);
    
    $result = parent::save();
    return $result;
    
  }

// =============================================================================================================
// DRAWING FUNCTION
// =============================================================================================================

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
    global $largeWidth, $print;

    if ($item!='notifiableItem') {return "";}

    $arrayClassWithDateTypeFields=getUserVisibleObjectClassWithFieldDateType();
        
    $fieldAttributes=$this->getFieldAttributes($item);
    if(strpos($fieldAttributes,'required')!==false) {
        $isRequired = true;
    } else {
        $isRequired = false;
    }

    $notReadonlyClass=($readOnly?"":" generalColClassNotReadonly ");
    $notRequiredClass=($isRequired?"":" generalColClassNotRequired ");
    $style=$this->getDisplayStyling($item);
    $labelStyle=$style["caption"];
    $fieldStyle=$style["field"];
    $fieldWidth=$largeWidth;
    $extName="";
    $name=' id="_spe_' . $item . '" name="_spe_' . $item . $extName . '" ';
    $attributes =' required="true" missingMessage="' . i18n('messageMandatory', array($this->getColCaption($item))) . '" invalidMessage="' . i18n('messageMandatory', array($this->getColCaption($item))) . '"';
    $valStore='';
    $colScript="";
    
    $result  = '<tr class="detail generalRowClass">';
    $result .= '<td class="tabLabel" style="text-align:right;font-weight:normal">' . i18n("col".ucfirst($item));
    $result .= '&nbsp;:&nbsp;</td>';    
    if (!$print) {
        $result .= '<td>';
        $result .= '<select dojoType="dijit.form.FilteringSelect" class="input '.(($isRequired)?'required':'').' generalColClass '.$notReadonlyClass.$notRequiredClass.$item.'Class" xlabelType="html" ';
        $result .= '  style="width: ' . ($fieldWidth) . 'px;' . $fieldStyle . '"';
        $result .= $name;
        $result .=$attributes;
        $result .=$valStore;
        $result .=autoOpenFilteringSelect();
        $result .=">";
        if (!$isRequired) {
          $result .= '<option value=" " ></option>';
        }

        foreach ($arrayClassWithDateTypeFields as $key => $value) {
            $result .= '<option value="' . $key . '"';
            if($this->id and $value === $this->notifiableItem) {
                $result .= ' SELECTED ';
            }
            $result .= '><span >'. htmlEncode(i18n($value)) . '</span></option>';
        }

        $result .=$colScript;
        $result .="</select></td>";
    } else {
          $result .= '<td style="color:grey;'.$fieldStyle.'">' . i18n($this->notifiableItem) . "&nbsp;&nbsp;&nbsp;</td>";        
    }
    $result .= '</tr>';
    return $result;
  }
  
// =============================================================================================================
// MISCELANOUS FUNCTION
// =============================================================================================================

}
?>