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
 * Light view of ticket, for simple definition.
 */ 
require_once('_securityCheck.php');
class TicketSimpleMain extends TicketMain {

	public $_noDisplayHistory=true;
	
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%">${idProject}</th>
    <th field="nameTicketType" width="15%" >${idTicketType}</th>
    <th field="name" width="25%">${name}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="actualDueDateTime" width="10%" formatter="dateTimeFormatter">${actualDueDateTime}</th>
    <th field="nameResource" formatter="thumbName22" width="15%">${responsible}</th>
    ';
  

  private static $_fieldsAttributes=array(
    "actualDueDateTime"=>"readonly",
    "creationDateTime"=>"readonly",
    "done"=>"readonly,nobr",
    "doneDateTime"=>"readonly,",
    "externalReference"=>"hidden",
    "handled"=>"readonly,nobr",
    "handledDateTime"=>"readonly",
    "id"=>"nobr", 
    "idle"=>"nobr, readonly",
    "idleDateTime"=>"nobr, readonly",
    "cancelled"=>"nobr, readonly",                              
    "idActivity"=>"hidden",
    "idContact"=>"hidden",
    "idContext1"=>"nobr,size1/3,title",
    "idContext2"=>"nobr,title", 
    "idContext3"=>"title",
    "idCriticality"=>"hidden",
    "idPriority"=>"readonly",
    "idProject"=>"required",
    "idResource"=>"readonly",
    "idStatus"=>"required",
    "idTicket"=>"hidden",
    "idTicketType"=>"readonly",
    "idUser"=>"hidden",
	  "idProduct"=>"hidden",
    "initialDueDateTime"=>"hidden",
    "name"=>"required",                               
    "Origin"=>"hidden",
    "reference"=>"readonly",
    "result"=>"readonly",
    "idTargetVersion"=>"readonly",
    "idTargetProductVersion"=>"readonly",
    "idTargetComponentVersion"=>"readonly",
	  "idOriginalVersion"=>"",
    "idComponent"=>"hidden",
    "idResolution"=>"readonly",
    "WorkElement"=>"hidden", "_Link"=>"hidden",
    "doneDateTime"=>"nobr,readonly",
    "solved"=>"nobr,readonly",
    "delayReadOnly"=>"hidden",
    "idAccountable"=>"hidden",
    "idMilestone"=>"hidden",
    "isRegression"=>"hidden"
  );  
    
  private static $_colCaptionTransposition = array('name'=>'ticketName',
                                                   'idUser'=>'issuer', 
                                                   'idResource'=> 'responsible',
                                                   'idActivity' => 'planningActivity',
                                                   'idContact' => 'requestor',
                                                   'idTargetVersion'=>'targetVersion',
                                                   'idOriginalVersion'=>'version',
                                                   'idTicket'=>'duplicateTicket',
                                                   'idContext1'=>'idContext',
                                                   'actualDueDateTime'=>'dueDate');
  
  private static $_databaseColumnName = array('idTargetVersion'=>'idVersion');

  private static $_databaseTableName = 'ticket';
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    unset($this->_Link);
    unset($this->WorkElement);
    unset($this->_sec_Link);
    unset($this->_tab_2_1);
    if (!$this->id and getSessionUser()->isContact) {
      $this->idContact=getSessionUser()->id;
    }
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
  
  /** ========================================================================
   * Return the specific databaseColumnName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
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
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }

  public function save() {
  	//$old=new Ticket($this->id);
  	$user=getSessionUser();
  	if (! $this->id) {
  	  if (! trim($this->idContact) and $user->isContact) {
  		  $this->idContact=$user->id;
  	  }
  	  $this->idUser=$user->id;
  	  $lst=SqlList::getList('TicketType');
  	  foreach ($lst as $id=>$val) {
  	    $this->idTicketType=$id;
  	    break;
  	  }
  	}
  	$result=parent::save();
  	return $result;
  }

  public function deleteControl() { 
    $result='';
    $canDeleteRealWork = false;
    $crit = array('idProfile' => getSessionUser()->getProfile ( $this ), 'scope' => 'canDeleteRealWork');
    $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', $crit );
    if ($habil and $habil->id and $habil->rightAccess == '1') {
    	$canDeleteRealWork = true;
    }
    $crit=array('refType'=>'Ticket', 'refId'=>$this->id);
    $this->WorkElement=SqlElement::getSingleSqlElementFromCriteria('WorkElement', $crit);
    if ($this->WorkElement and $this->WorkElement->realWork>0 and !$canDeleteRealWork) {
      $result.='<br/>' . i18n('msgUnableToDeleteRealWork');
    }
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }
  
  public function getTitle($col) {
  	if (substr($col,0,9)=='idContext') {
  	  return SqlList::getNameFromId('ContextType', substr($col, 9));
  	} else {
  		return parent::getTitle($col);
  	} 
  	
  }
  
}
?>