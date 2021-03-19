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
 * MailToSend stores emails to be sent grouped.
 * When parameter to group email is off, this table is always empty
 * Items are deleted when emails are sent (trace is then is email table)
 */ 
require_once('_securityCheck.php');
class MailToSend extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $idUser;
  public $refType;
  public $refId;
  public $idEmailTemplate;
  public $template;
  public $title;
  public $dest;
  public $recordDateTime;
  public $idle;
  
  public $_noHistory=true;
  public $_readOnly = true;
  
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameUser" formatter="thumbName22" width="10%" >${sender}</th>
    <th field="refType" width="7%" >${refType}</th>
    <th field="refId" width="3%" >${id}</th>
    <th field="title" width="35%" >${mailTitle}</th>
    <th field="dest" width="20%" >${recipient}</th>
    <th field="nameEmailTemplate" width="10%" >${template}</th>
    <th field="recordDateTime" width="10%" >${recordDate}</th>
    ';
    
    private static $_fieldsAttributes=array('idUser'=>'readonly',
        'mailTitle'=>'readonly',
        'refType'=>'readonly,size1/3, nobr',
        'refId'=>'readonly, size1/3',
        'idEmailTemplate'=>'readonly',
        'template'=>'hidden',
        'title'=>'readonly',
        'dest'=>'readonly',
        'recordDateTime'=>'readonly',
        'idle'=>'hidden'
    );
    
    private static $_colCaptionTransposition = array(
        'refType'=>'notifiableItem',
        'refId'=> 'id', 
        'recordDateTime'=>'recordDate',
        'dest'=>'mailTo'
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
  
  public function save() {
    if (!$this->recordDateTime) $this->recordDateTime=date('Y-m-d H:i:s');
    if (!$this->idUser) $this->idUser=getCurrentUserId();
  	return parent::save();
  }

}
?>