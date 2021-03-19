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
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once('_securityCheck.php');
class Link extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $ref1Type;
  public $ref1Id;
  public $ref2Type;
  public $ref2Id;
  public $comment;
  public $creationDate;
  public $idUser;
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /**
   * Save object (permuts objects ref if needed)
   * @see persistence/SqlElement#save()
   */
  public function save() {

    if ($this->ref2Type < $this->ref1Type) {
      $type=$this->ref2Type;
      $id=$this->ref2Id;
      $this->ref2Type=$this->ref1Type;
      $this->ref2Id=$this->ref1Id;
      $this->ref1Type=$type;
      $this->ref1Id=$id;
    } 
    $result=parent::save();
    
    if ($this->ref1Type=='Requirement' and ($this->ref2Type=='TestCase' or $this->ref2Type=='Ticket')) {
    	$req=new Requirement($this->ref1Id);
      $req->updateDependencies();
    }
    if ($this->ref1Type=='TestSession' and $this->ref2Type=='Ticket') {
      $ts=new TestSession($this->ref1Id);
      $ts->updateDependencies();
    }
    if(SqlElement::class_exists($this->ref1Type)){
      // gautier add
      $class1 = $this->ref1Type;
      $id1 = $this->ref1Id;
      $obj = new $class1( $id1 );
      if (property_exists ( $class1, 'lastUpdateDateTime' ) and !SqlElement::$_doNotSaveLastUpdateDateTime) { 
        $resObj=$obj->saveForced();
      }
    }
    
    if(SqlElement::class_exists($this->ref2Type)){
      $class2 = $this->ref2Type;
      $id2 = $this->ref2Id;
      $obj = new $class2( $id2 );
      if (property_exists ( $class2, 'lastUpdateDateTime' )and !SqlElement::$_doNotSaveLastUpdateDateTime) {
        $obj->lastUpdateDateTime = date ( "Y-m-d H:i:s" );
        $resObj=$obj->saveForced();
      }
      // end gautier 
    }
    return $result;
  }
  
  public function delete() {
  	
  	$result=parent::delete();
  	
    if ($this->ref1Type=='Requirement' and ($this->ref2Type=='TestCase' or $this->ref2Type=='Ticket')) {
      $req=new Requirement($this->ref1Id);
      $req->updateDependencies();
    }
    if ($this->ref1Type=='TestSession' and $this->ref2Type=='Ticket') {
      $ts=new TestSession($this->ref1Id);
      $ts->updateDependencies();
    }
    
    // gautier add
    if(SqlElement::class_exists($this->ref1Type)){
      $class1 = $this->ref1Type;
      $id1 = $this->ref1Id;
      $obj = new $class1( $id1 );
      if (property_exists ( $class1, 'lastUpdateDateTime' ) and !SqlElement::$_doNotSaveLastUpdateDateTime) {
        $obj->lastUpdateDateTime = date ( "Y-m-d H:i:s" );
        $resObj=$obj->saveForced();
      }
    }
    
    if(SqlElement::class_exists($this->ref1Type)){
      $class2 = $this->ref2Type;
      $id2 = $this->ref2Id;
      $obj = new $class2( $id2 );
      if (property_exists ( $class2, 'lastUpdateDateTime' ) and !SqlElement::$_doNotSaveLastUpdateDateTime) {
        $obj->lastUpdateDateTime = date ( "Y-m-d H:i:s" );
        $resObj=$obj->saveForced();
      }
    }
    // end gautier add
    return $result;
  }
  /** ==========================================================================
   * Return a list of Link objects involving one given object
   * @param $obj the object we are looking links for
   * @param $classLink optional reference to a class to restrict links of this class
   * @return array of Link objects
   */
  static function getLinksForObject($obj, $classLink=null) {
    $where=null;
    $orderBy=null;
    $link=new Link();
    $class=get_class($obj);
    if ($classLink) {
      if ($class<$classLink) {
        $where=" ref1Type='" . $class . "' and ref1Id=" . Sql::fmtId($obj->id) . " and ref2Type='" . $classLink . "' ";
      } else {
        $where=" ref2Type='" . $class . "' and ref2Id=" . Sql::fmtId($obj->id) . " and ref1Type='" . $classLink . "' ";
      }
    } else {
      $where=" ( ref1Type='" . $class . "' and ref1Id=" . Sql::fmtId($obj->id) . ") ";
      $where.=" or ( ref2Type='" . $class . "' and ref2Id=" . Sql::fmtId($obj->id) . " ) ";
    }
    //echo $where . "\n";
    $list=$link->getSqlElementsFromCriteria(null,false,$where,$orderBy);
    return $list;
  }
  /** ==========================================================================
   * Return a list of links as "type" and "id" array involving one given object
   * @param $obj the object we are looking links for
   * @param $classLink optional reference to a class to restrict links of this class
   * @return array of "type" and "id" sur array
   */
  static function getLinksAsListForObject($obj, $classLink=null) {
    $list = self::getLinksForObject($obj, $classLink);
    $class=get_class($obj);
    $result=array();
    foreach($list as $listObj) {
      $type="";
      $id="";
      if ($listObj->ref1Type==$class and $listObj->ref1Id==$obj->id ) {
         $type=$listObj->ref2Type;
         $id=$listObj->ref2Id;
      } else {
         $type=$listObj->ref1Type;
         $id=$listObj->ref1Id;
      }
      $res=array("type"=>$type, "id"=>$id);
      $result[$listObj->id]=$res;
    }
    return $result;
  }
  
  static function getLinksAsObjectsForObject($obj, $classLink=null) {
    $list = getLinksForObject($obj, $classLink);
    $class=get_class($obj);
    foreach($list as $lstObj) {
      $type="";
      $id="";
      if ($lstObj->ref1Type=$class and $lstObj->ref1Id=$obj->id ) {
         $type=$lstObj->ref2Type;
         $id=$lstObj->ref2Id;
      } else {
         $type=$lstObj->ref1Type;
         $id=$lstObj->ref1Id;
      }
      $resObj=new $type($id);
      $result[$lstObj->id]=$resObj;
    }
    return $result;
  }

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $checkCrit=array('ref1Type'=>$this->ref1Type,
                     'ref1Id'=>$this->ref1Id,
                     'ref2Type'=>$this->ref2Type,
                     'ref2Id'=>$this->ref2Id);
    $lnk=new Link();
    $check=$lnk->getSqlElementsFromCriteria($checkCrit);
    if (count($check)>0) {
      $result.='<br/>' . i18n('errorDuplicateLink');
    }
    if( Module::isModuleActive('moduleGestionCA') and $this->ref1Type == 'Bill' and $this->ref2Type == 'Command'){
      $checkUniqueBill=$lnk->getSqlElementsFromCriteria(array('ref1Type'=>$this->ref1Type, 'ref1Id'=>$this->ref1Id));
      if (count($checkUniqueBill)>0) {
        $result.='<br/>' . i18n('errorUniqueBillLink');
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
}
?>