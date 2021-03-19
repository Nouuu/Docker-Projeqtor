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
 * Profile defines right to the application or to a project.
 */ 
require_once('_securityCheck.php');
class ProfileMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $profileCode;
  public $sortOrder=0;
  public $idle;
  public $description;
  public $_sec_restrictTypes;
  public $_spe_restrictTypes;
  public static $_profileHasAccess;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%" ># ${id}</th>
    <th field="name" width="75%" formatter="translateFormatter">${name}</th>
    <th field="sortOrder" width="10%" formatter="numberFormatter">${sortOrder}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
  
  public $_isNameTranslatable = true;
  
  private static $_fieldsAttributes=array(
      'name'=>'required', 
      'sortOrder'=>'required'
  );
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($this->profileCode=="ADM" or $this->profileCode=="PL") {
      self::$_fieldsAttributes["profileCode"]="readonly";
    }
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

  public function deleteControl() {
    $result="";
    if ($this->profileCode=='ADM' or $this->profileCode=='PL') {    
      $cpt=$this->countSqlElementsFromCriteria(array('profileCode'=>$this->profileCode));
      if ($cpt<2) {
        $result="<br/>" . i18n("msgCannotDeleteProfile");
      }
    }
    if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
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
  
  public function copy() {
    if ($this->profileCode=='ADM' or $this->profileCode=='PL') {
      $this->profileCode='';
    }
    $result=parent::copy();
    $new=$result->id;
    $toCopy=array('AccessRight', 'Habilitation', 'HabilitationOther', 'HabilitationReport', 'WorkflowStatus');
    foreach ($toCopy as $objectClass) {
      $obj=new $objectClass();
      $crit=array('idProfile'=>$this->id);
      $lst=$obj->getSqlElementsFromCriteria($crit);
      foreach ($lst as $obj) {
        $obj->idProfile=$new;
        $obj->id=null;
        $obj->save();
      }
    }
    Sql::$lastQueryNewid=$new;
    return $result;
  }
  
  public function drawSpecificItem($item){
    global $print;
    $result="";
    if ($item=='restrictTypes') {
      if (!$this->id) return '';
      if (! $print) {
        $result.= '<button id="buttonRestrictTypes" dojoType="dijit.form.Button" showlabel="true"'
            . ' title="'.i18n('helpRestrictTypesProfile').'" iconClass="iconType16" class="roundedVisibleButton">'
                . '<span>'.i18n('restrictTypes').'</span>'
                    . ' <script type="dojo/connect" event="onClick" args="evt">'
                        . '  var params="&idProfile='.$this->id.'";'
                            . '  loadDialog("dialogRestrictTypes", null, true, params);'
                                . ' </script>'
                                    . '</button>';
        $result.= '<span style="font-size:80%">&nbsp;&nbsp;&nbsp;('.i18n('helpRestrictTypesProfileInline').')</span>';
      }
      $result.='<table style="witdh:100%"><tr style=""><td class="label" style="width:220px">'.i18n('existingRestrictions').Tool::getDoublePoint().'</td><td>';
      $result.='<div id="resctrictedTypeClassList" style="position:relative;left:5px;top:2px">';
      $list=Type::getRestrictedTypesClass(null,null,$this->id);
      $cpt=0;
      foreach ($list as $cl) {
        $cpt++;
        $result.=(($cpt>1)?', ':'').$cl;
      }
      $result.='</div>';
      $result.='</td></tr><tr><td colspan="2">&nbsp;</td></tr></table>';
      if (! $print) {
        $result.= '<button id="buttonRestrictProductList" dojoType="dijit.form.Button" showlabel="true"'
                . ' title="'.i18n('helpRestrictProductListProfile').'" iconClass="iconType16" class="roundedVisibleButton">'
                . '<span>'.i18n('restrictProductList').'</span>'
                . ' <script type="dojo/connect" event="onClick" args="evt">'
                . '  var params="&idProfile='.$this->id.'";'
                . '  loadDialog("dialogRestrictProductList", null, true, params);'
                . ' </script>'
                . '</button>';
      }
      return $result;
    }
  }
  
  public static function profileHasNoAccess($prof) {
    if (!self::$_profileHasAccess) {
      self::$_profileHasAccess=array();
       $hab=new Habilitation();
       $res=$hab->countGroupedSqlElementsFromCriteria(null, array('idProfile'), "allowAccess=1");
       foreach($res as $idP=>$cpt) {     
         if ($cpt>0) {
           self::$_profileHasAccess[$idP]=true;
         }
       }
    }
    if (isset(self::$_profileHasAccess[$prof]) and self::$_profileHasAccess[$prof]==true) return false;
    else return true;
  }
}
?>