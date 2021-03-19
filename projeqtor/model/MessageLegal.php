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
class MessageLegal extends SqlElement {

  public $_sec_Description;
  public $id;
  public $name;
  public $idUser;
  public $description;
  public $_sec_Treatment;
  public $startDate;
  public $endDate;
  public $idle;
  public $_sec_MessageLegalFollowup;
  public $_spe_followupSynthesis;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="40%">${title}</th>
    <th field="startDate" width="10%" formatter="dateFormatter" >${startDate}</th>
    <th field="endDate" width="10%" formatter="dateFormatter" >${endDate}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th> '
    ;
  
  private static $_colCaptionTransposition = array('name'=> 'title', 'description'=>'message');
  
  private static $_fieldsAttributes=array("name"=>"required","description"=>"required","idUser"=>"hidden");  
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
// GET VALIDATION SCRIPT
// ============================================================================**********

/** ==========================================================================
 * Return the validation sript for some fields
 * @return the validation javascript (for dojo frameword)
 */
public function getValidationScript($colName) {
   $colScript = parent::getValidationScript($colName);
  if ($colName=="startDate" ) {
    $colScript .= '<script type="dojo/connect" event="onChange" >';
    $colScript .="var end=dijit.byId('endDate');";
    $colScript .="var start=dijit.byId('startDate').get('value');";
    $colScript .="end.set('dropDownDefaultValue',this.value);";
    $colScript .="end.constraints.min=start;"; 
    $colScript .= 'formChanged();';
    $colScript .= '</script>';
  }else if ($colName=="endDate"  ) {
    $colScript .= '<script type="dojo/connect" event="onChange" >';
    $colScript .=" if (!this.value || dijit.byId('idle').get('checked')==true) return;";
    $colScript .="var end=dijit.byId('endDate').get('value');";
    $colScript .="var start=dijit.byId('startDate');";
    $colScript .="start.set('dropDownDefaultValue',this.value);";
    $colScript .="start.constraints.max=end;";
    $colScript .= 'formChanged();';
    $colScript .= '</script>';
  }
  return $colScript;
}

public function drawSpecificItem($item){
  global $comboDetail, $print, $outMode, $largeWidth;
  $result="";
  if ($item=='followupSynthesis') {
    if($this->id){
      drawFollowupSynthesis($this);
    }
    return $result;
  }
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
  
  public function setAttributes() {
    if($this->id){
      $messageLegalFollowup = new MessageLegalFollowup();
      $lstFollowUp = $messageLegalFollowup->countSqlElementsFromCriteria(array('accepted'=>1,'idMessageLegal'=>$this->id));
      if($lstFollowUp>0){
        self::$_fieldsAttributes['description']='readonly';
      }
    }
  }
  
}
?>