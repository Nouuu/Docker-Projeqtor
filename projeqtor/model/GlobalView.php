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
class GlobalView extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;
  public $objectClass;
  public $objectId;    // redefine $id to specify its visible place 
  public $reference;
  public $name;
  public $idType;
  public $idProject;
  public $idUser;
  public $description;
  public $creationDate;
  public $_sec_treatment;
  public $idStatus;
  public $idResource;
  public $result;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $validatedEndDate;
  public $plannedEndDate;
  public $realEndDate;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" width="-1%" >${id}</th>
    <th field="objectClass" formatter="classNameFormatter" width="15%" >${refType}</th>
    <th field="objectId" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="20%" >${idProject}</th>
    <th field="name" width="45%" >${name}</th>
     <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idProject"=>"required",
                                  "idType"=>"required",
                                  "idUser"=>"hidden",
                                  "idStatus"=>"required",
                                  "idle"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('idResource'=>'responsible', 'idType'=>'type', 'objectClass'=>'refType'
  );
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array();
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($withoutDependentObjects) return;
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

    if ($colName=="idStatus") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= htmlGetJsTable('Status', 'setIdleStatus', 'tabStatusIdle');
      $colScript .= htmlGetJsTable('Status', 'setDoneStatus', 'tabStatusDone');
      $colScript .= '  var setIdle=0;';
      $colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
      $colScript .= '  if (setIdle==1) {';
      $colScript .= '    dijit.byId("idle").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idle").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  var setDone=0;';
      $colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
      $colScript .= '  if (setDone==1) {';
      $colScript .= '    dijit.byId("done").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("done").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="initialDueDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("actualDueDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("actualDueDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="actualDueDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("initialDueDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("initialDueDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';           
    } else     if ($colName=="idle") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("idleDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("idleDate").set("value", curDate); ';
      $colScript .= '    }';
//       $colScript .= '    if (! dijit.byId("done").get("checked")) {';
//       $colScript .= '      dijit.byId("done").set("checked", true);';
//       $colScript .= '    }';  
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idleDate").set("value", null); ';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="done") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("doneDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("doneDate").set("value", curDate); ';
      $colScript .= '    }';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("doneDate").set("value", null); ';
//       $colScript .= '    if (dijit.byId("idle").get("checked")) {';
//       $colScript .= '      dijit.byId("idle").set("checked", false);';
//       $colScript .= '    }'; 
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;
  }
    
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  public static function getTableNameQuery() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    $typeDb=Parameter::getGlobalParameter('paramDbType');
    $obj=new GlobalView();
    $na=Parameter::getUserParameter('notApplicableValue');
    if (!$na or $typeDb=='pgsql' ) $na='null';
    $pe=new PlanningElement();
    $peTable=$pe->getDatabaseTableName();
    $itemsToDisplay=Parameter::getUserParameter('globalViewSelectedItems');
    $itemsToDisplayArray=explode(',', $itemsToDisplay);
    if (count($itemsToDisplayArray)==0 or (count($itemsToDisplayArray)==1 and $itemsToDisplayArray[0]=='none')) return $obj->getDatabaseTableName();
    $query='(';
    foreach (self::getGlobalizables() as $class=>$className) {
      if ($itemsToDisplay and $itemsToDisplay!=' ' and !in_array($class,$itemsToDisplayArray)) {
        continue;
      }
      $clsObj=new $class();
      $table=$clsObj->getDatabaseTableName();
      $convert=self::$_globalizables[$class];
      if ($query!='(') $query.=' UNION ';
      $query.="SELECT concat('$class',$table.id) as id";
      foreach ($obj as $fld=>$val) {
        if (substr($fld,0,1)=='_' or $fld=='id') continue;        
        $query.=", ";
        if ($fld=='objectClass') $query.="'$class'";
        else if ($fld=='objectId') $query.="$table.id";
        //florent
        else if (isset($convert[$fld]) )$query.=(($convert[$fld]=='null')?$na:"$table.".$convert[$fld]);
        else if ($fld=='idType') $query.="$table.id".$class."Type";
        else if (($fld=='validatedEndDate' or $fld=='plannedEndDate' or $fld=='realEndDate') and property_exists($class, $class.'PlanningElement')) $query.="$peTable.$fld";
        else $query.="$table.$fld";
        $query.=" as $fld";
      }
      $query.=" FROM ".$table;
      if (property_exists($class, $class.'PlanningElement')) {
        $query.=" LEFT JOIN $peTable ON $peTable.refType='$class' and $peTable.refId=$table.id ";
      }
      // Add control rights
      $clause=getAccesRestrictionClause($class,$table, false);
      if ($class=='Project') {
         $query.=" WHERE (".$clause." or ".$table.".codeType='TMP' )"; // Templates projects are always visible in projects list
      } else {
        $query.=" WHERE ".$clause;
      }
      $crit=$clsObj->getDatabaseCriteria();
      foreach ($crit as $col => $val) {
        $query.= " and $table.".$clsObj->getDatabaseColumnName($col)."=".Sql::str($val);
      }
    }
    $query.=')';
    return $query;
  }
  
  public static function getGlobalizables() {
    $result=array();
    foreach (self::$_globalizables as $key=>$val) {
      if (securityCheckDisplayMenu(null,$key)) {
        $result[i18n($key)]=$key;
      }
    }
    ksort($result);
    $result=array_flip($result);
    return $result;
  }
  
  static protected $_globalizables=array(
      'Project'=>array('idProject'=>'id','result'=>'null','reference'=>'null'),
      'Ticket'=>array('handledDate'=>'handledDateTime','doneDate'=>'doneDateTime','idleDate'=>'idleDateTime','validatedEndDate'=>'initialDueDateTime','plannedEndDate'=>'actualDueDateTime','realEndDate'=>'doneDateTime', 'creationDate'=>'creationDateTime'),
      'Activity'=>array(),
      'Milestone'=>array(),
      'Action'=>array('validatedEndDate'=>'initialDueDate','plannedEndDate'=>'actualDueDate','realEndDate'=>'doneDate'),
      'Requirement'=>array('validatedEndDate'=>'initialDueDate','plannedEndDate'=>'actualDueDate','realEndDate'=>'doneDate','creationDate'=>'creationDateTime'),
      'TestCase'=>array('validatedEndDate'=>'null','plannedEndDate'=>'null','realEndDate'=>'doneDate','creationDate'=>'creationDateTime'),
      'TestSession'=>array('creationDate'=>'creationDateTime'),
      'Risk'=>array('validatedEndDate'=>'initialEndDate','plannedEndDate'=>'actualEndDate','realEndDate'=>'doneDate'),
      'Opportunity'=>array('validatedEndDate'=>'initialEndDate','plannedEndDate'=>'actualEndDate','realEndDate'=>'doneDate'),
      'Issue'=>array('validatedEndDate'=>'initialEndDate','plannedEndDate'=>'actualEndDate','realEndDate'=>'doneDate'),
      'Meeting'=>array('creationDate'=>'meetingDate'),
      'Decision'=>array('result'=>'null','handled'=>'null','handledDate'=>'null','doneDate'=>'null','idleDate'=>'null','validatedEndDate'=>'null','plannedEndDate'=>'null','realEndDate'=>'decisionDate'),
      'Question'=>array('validatedEndDate'=>'initialDueDate','plannedEndDate'=>'actualDueDate','realEndDate'=>'doneDate'),
      'Incoming'=>array('idType'=>'null','idStatus'=>'null','handled'=>'null','done'=>'null','cancelled'=>'null','handledDate'=>'null','doneDate'=>'null','idleDate'=>'null','validatedEndDate'=>'initialDate','plannedEndDate'=>'plannedDate','realEndDate'=>'realDate','creationDate'=>'creationDateTime'),
      'Deliverable'=>array('idType'=>'null','idStatus'=>'null','handled'=>'null','done'=>'null','cancelled'=>'null','handledDate'=>'null','doneDate'=>'null','idleDate'=>'null','validatedEndDate'=>'initialDate','plannedEndDate'=>'plannedDate','realEndDate'=>'realDate','creationDate'=>'creationDateTime'),
      'Delivery'=>array('handledDate'=>'handledDateTime','doneDate'=>'doneDateTime','idleDate'=>'idleDateTime','validatedEndDate'=>'initialDate','plannedEndDate'=>'plannedDate','realEndDate'=>'realDate','creationDate'=>'creationDateTime'),
  );

  public static function drawGlobalizableList() {
    $itemsToDisplay=Parameter::getUserParameter('globalViewSelectedItems');
    $itemsToDisplayArray=explode(',', $itemsToDisplay);
    echo '<select dojoType="dojox.form.CheckedMultiSelect"  multiple="true" style="border:1px solid #A0A0A0;width:initial;height:218px;max-height:218px;"';
    echo '  id="globalViewSelectItems" name="globalViewSelectItems[]" onChange="globalViewSelectItems(this.value);" value="'.$itemsToDisplay.'" >';
    echo '  <option value=" ">'.i18n("activityStreamAllItems").'</option>';
    $items=self::getGlobalizables();
    foreach ($items as $class=>$className) {
      echo "  <option value='$class'>$className</option>";
    }
    echo '</select>';
  }
  
}
?>