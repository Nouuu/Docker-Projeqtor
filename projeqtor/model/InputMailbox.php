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
class InputMailbox extends SqlElement {

  public $_sec_Description;
  public $id;
  public $name;
  public $idProject;
  public $serverImap;
  public $imapUserAccount;
  public $pwdImap;
  public $securityConstraint;
  public $_tab_4_1_4 = array('','','','','allowAttach');
  public $allowAttach;
  public $_label_sizeAttachment1;
  public $sizeAttachment;
  public $_label_sizeAttachment2;
  public $idle;
  public $_sec_treatment;
  public $idTicketType;
  public $idAffectable;
  public $idActivity;
  public $limitOfInputPerHour;
  public $lastInputDate;
  public $idTicket;
  public $totalInputTicket;
  public $failedRead;
  public $failedMessage;
  public $_sec_TicketHistory;
  public $limitOfHistory;
  public $_spe_ticketHistory;
  public $_nbColMax = 3;
  
  public $_noCopy;
  public $_dynamicHiddenFields=array('sizeAttachment','_label_sizeAttachment1','_label_sizeAttachment2');
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="8%"># ${id}</th>
    <th field="name" width="23%">${name}</th>
    <th field="nameProject" width="15%">${idProject}</th>
    <th field="serverImap" width="20%">${serverImap}</th>
    <th field="imapUserAccount" width="20%">${imapUserAccount}</th>
    <th field="limitOfInputPerHour" width="10%">${limitOfInputPerHour}</th>
    <th field="idle" width="4%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_fieldsAttributes=array(
      'name'=>'required',
      'idProject'=>'required',
      'serverImap'=>'required',
      'imapUserAccount'=>'required',
      'pwdImap'=>'required',
      'securityConstraint'=>'required',
      'idTicketType'=>'required',
      'lastInputDate'=>'readonly',
      'idTicket'=>'readonly',
      'totalInputTicket'=>'readonly',
      'failedRead'=>'hidden',
      'failedMessage'=>'hidden',
      'limitOfInputPerHour'=>'required',
      '_label_sizeAttachment2'=>'leftAlign',
      '_label_sizeAttachment1'=>'longLabel'
  );
  
  private static $_colCaptionTransposition = array(
      'idAffectable' => 'responsible',
      'idActivity' => 'PlanningActivity',
      'idTicket' => 'lastInputTicket',
      'sizeAttachment'=>'sizeAttachment1'
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

  
  
  public function control(){
    $result="";
    $defaultControl=parent::control();
    $old = $this->getOld();
    //unicity project/ticket type control
    $unicity = $this->countSqlElementsFromCriteria(array('idProject'=>$this->idProject,'idTicketType'=>$this->idTicketType));
    if($unicity > 0){
      if($this->id){
        if($old->idProject != $this->idProject)$result .= '<br/>' . i18n ( 'projectIsAlreadyUsed' );
      }else{
        $result .= '<br/>' . i18n ( 'projectIsAlreadyUsed' );
      }
    }
    //unicity global parameters
    $emailHost=Parameter::getGlobalParameter('cronCheckEmailsHost'); // {imap.gmail.com:993/imap/ssl}INBOX';
    $emailEmail=Parameter::getGlobalParameter('cronCheckEmailsUser');
    if($emailHost and $emailEmail){
      if($this->serverImap == $emailHost and $this->imapUserAccount == $emailEmail){
        $result .= '<br/>' . i18n ( 'imapIsAlreadyUsed' );
      }
    }
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function setAttributes() {
    self::$_fieldsAttributes['_spe_ticketHistory']='hidden';
    if($this->limitOfHistory > 0){
      self::$_fieldsAttributes['_spe_ticketHistory']='readonly';
    }
    if(!$this->id or $this->allowAttach == '0'){
      self::$_fieldsAttributes['sizeAttachment']='hidden';
      self::$_fieldsAttributes['_label_sizeAttachment1']='hidden,longLabel';
      self::$_fieldsAttributes['_label_sizeAttachment2']='hidden,leftAlign';
    }
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
  * @see persistence/SqlElement#save()
  * @return the return message of persistence/SqlElement#save() method
  */
  public function save() {
    $old = $this->getOld();
    if(!$this->id and !$this->limitOfHistory){
     $this->limitOfHistory = 10; 
    }
    if ($old->idle and !$this->idle) {
      $this->failedRead=0; // Reactivate closed mailbox
    }
    $result = parent::save();
    if(!$old->id and $this->id == 1){
      $checkEmails=Parameter::getGlobalParameter('cronCheckEmails');
      if (!$checkEmails or intval($checkEmails)<=0) {
        Parameter::storeGlobalParameter('cronCheckEmails', 10);
      }
    }
    return $result;
  }
  public function delete() {
    $result=parent::delete();
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
    if ($colName=="allowAttach") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if(dojo.byId("allowAttach").checked){ ';
      $colScript .= '    dojo.query(".generalColClass.sizeAttachmentClass").style("display", "inline-block");';
      $colScript .= '    dojo.query("._label_sizeAttachment1Class").style("display", "inline-block");';
      $colScript .= '    dojo.query("._label_sizeAttachment2Class").style("display", "inline-block");';
      $colScript .= '  }else{';
      $colScript .= '    dojo.query(".generalColClass.sizeAttachmentClass").style("display", "none");';
      $colScript .= '    dojo.query("._label_sizeAttachment1Class").style("display", "none");';
      $colScript .= '    dojo.query("._label_sizeAttachment2Class").style("display", "none");';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
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
    if($item=='ticketHistory'){
        $history = new InputMailboxHistory();
        $critArray=array('idInputMailbox'=>$this->id);
        $order = " date desc ";
        $historyList=$history->getSqlElementsFromCriteria($critArray, false,null,$order,false,false,$this->limitOfHistory);
        drawInputMailboxHistory($historyList,$this);
    }
    return $result;
  }
  
}
?>