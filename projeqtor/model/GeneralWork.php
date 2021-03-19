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
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
require_once('_securityCheck.php');
class GeneralWork extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $id;    // redefine $id to specify its visible place 
  public $idResource;
  public $idProject;
  public $refType;
  public $refId;
  public $idAssignment;
  public $work;
  public $workDate;
  public $day;
  public $week;
  public $month;
  public $year;
  public $dailyCost;
  public $cost;
  public $_noHistory;
  private static $hoursPerDay;
  private static $imputationUnit;
  private static $shortImputationUnit;
  private static $imputationCoef;
  private static $workUnit;
  private static $workCoef;
  private static $shortWorkUnit;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%" ># ${id}</th>
    <th field="nameResource" formatter="thumbName22" width="35%" >${resourceName}</th>
    <th field="nameProject" width="35%" >${projectName}</th>
    ';

  
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


// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idle") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("PlanningElement_realEndDate").get("value")==null) {';
      $colScript .= '      dijit.byId("PlanningElement_realEndDate").set("value", new Date); ';
      $colScript .= '    }';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("PlanningElement_realEndDate").set("value", null); ';
      //$colScript .= '    dijit.byId("PlanningElement_realDuration").set("value", null); ';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Set all date values : workDate, 
   * @param $workDate
   * @return void
   */
  public function setDates($workDate) {
    $year=substr($workDate,0,4);
    $month=substr($workDate,5,2);
    $day=substr($workDate,8,2);
    $this->workDate=$workDate;
    $this->day=$year . $month . $day;
    $this->month=$year . $month; 
    $this->year=$year;
    if (weekNumber($workDate)=='01' and $month=='12') {$year+=1;}
    else if (weekNumber($workDate)>50 and $month=='01') {$year-=1;};
    $this->week=$year.weekNumber($workDate);
  }
  
  public function save() {
    if (! $this->idProject) {
      if ($this->refType=='Project') {
        $this->idProject=$this->refId;
      } else if ($this->refType) {
        $refObj=new $this->refType($this->refId);
        $this->idProject=$refObj->idProject;
      }
    }
    if (!$this->dailyCost) {
      $ass=new Assignment($this->idAssignment);
      $idRole=$ass->idRole;
      if (!$idRole) {
        $r=new Resource($this->idResource);
        $idRole=$r->idRole;
      }
      $where="idResource=" . Sql::fmtId($this->idResource) 
       . " and ". (($idRole)?"idRole=".Sql::fmtId($idRole):"1=1")
       . " and (startDate is null or startDate<='" . $this->workDate . "')"
       . " and (endDate is null or endDate>='" . $this->workDate . "')";
      $order="startDate asc, id asc"; // Take oldest in date, or oldest inserted in db (id)
      $rc=new ResourceCost();
      $rcList=$rc->getSqlElementsFromCriteria(null, false, $where, $order);
      $this->dailyCost=((count($rcList)>0)?$rcList[0]->cost:$ass->dailyCost);
    }
    $this->cost=$this->dailyCost*$this->work;
    return parent::save();
  }
  
  public static function displayImputation($val) {
  	self::setImputationUnit();
    $coef=self::$imputationCoef;
  	return (round($val*$coef,2));
  }
  public static function displayImputationWithUnit($val) {
    self::setImputationUnit();
    $coef=self::$imputationCoef;
    return (round($val*$coef,2)) . ' '. self::displayShortImputationUnit();
  }
  
  public static function convertImputation($val) {
    self::setImputationUnit();
    $coef=self::$imputationCoef;
    if (!$coef) return $val;
    if (!$val) return 0;
    if(!is_numeric($val)){
      return 0;
    }
    return (round($val/$coef,5));
  }
  
  private static function setImputationUnit() {
    if (self::$imputationUnit) return;
  	$unit=Parameter::getGlobalParameter('imputationUnit');
    $unit=($unit)?$unit:'days';
    self::$imputationUnit=$unit;
    self::$shortImputationUnit=($unit=='days')?i18n('shortDay'):i18n('shortHour');
    if (self::$hoursPerDay) {
      $hoursPerDay=self::$hoursPerDay;
    } else {
      $hoursPerDay=Parameter::getGlobalParameter('dayTime');
      $hoursPerDay=($hoursPerDay)?$hoursPerDay:'8';
      self::$hoursPerDay=$hoursPerDay;
    }
    $coef=($unit=='days')?'1':$hoursPerDay;
    self::$imputationCoef=$coef;
  }
  
  public static function displayImputationUnit() {
  	self::setImputationUnit();
  	$res='<b>' . i18n('paramImputationUnit') . " = " . i18n(self::$imputationUnit) . '</b>';
    if (self::$imputationUnit=="hours") {
      $res.= ' - ' . i18n('paramDayTime') . " = " . self::$hoursPerDay ;
    } 
    return $res;
  }
  public static function displayShortImputationUnit() {
    self::setImputationUnit();
    //$res=mb_substr(i18n(self::$imputationUnit),0,1);
    return self::$shortImputationUnit;
  }
  
  public static function getConvertedCapacity($capacity) {
    self::setImputationUnit();
    if (self::$imputationUnit=="hours" and self::$hoursPerDay) {
      return $capacity * self::$hoursPerDay ;
    } else {
      return $capacity;
    }
  }
  
  private static function setWorkUnit() {
    if (self::$workUnit) return;
    $unit=Parameter::getGlobalParameter('workUnit');
    $unit=($unit)?$unit:'days';
    self::$workUnit=$unit;
    self::$shortWorkUnit=($unit=='days')?i18n('shortDay'):i18n('shortHour');
    if (self::$hoursPerDay) {
    	$hoursPerDay=self::$hoursPerDay;
    } else {
      $hoursPerDay=Parameter::getGlobalParameter('dayTime');
      $hoursPerDay=($hoursPerDay)?$hoursPerDay:'8';
      self::$hoursPerDay=$hoursPerDay;
    }
    $coef=($unit=='days')?'1':$hoursPerDay;
    self::$workCoef=$coef;
  }
  
  public static function getWorkCoef() {
    self::setWorkUnit();
    return self::$workCoef;
  }
  public static function displayWork($val,$rounding=3) {
    self::setWorkUnit();
    $coef=self::$workCoef;
    if (!$val) return 0;
    return round(floatval($val)*$coef,$rounding); // Rounding to 3 leads to rounding errors in Reports
  }
  
  public static function displayWorkWithUnit($val) {
    global $outMode;
    if (isset($outMode) and $outMode=='excel') return self::displayWork($val);
    $ret=rtrim(htmlDisplayNumeric(self::displayWork($val)),'0');
    if (substr($ret,-1)==',' or substr($ret,-1)=='.') $ret=substr($ret,0,strlen($ret)-1);
    return $ret . '&nbsp;' . self::displayShortWorkUnit();
  }
  
  public static function convertWork($val) {
    self::setWorkUnit();
    $coef=self::$workCoef;
    if (!$val) return 0;
    return (round(floatval($val)/$coef,5));
  }
  
  public static function displayShortWorkUnit() {
    self::setWorkUnit();
    //$res=mb_substr(i18n(self::$workUnit),0,1);
    return self::$shortWorkUnit;
  }
  public static function getWorkUnit() {
  	self::setWorkUnit();
  	return self::$workUnit;
  }  
  public static function getHoursPerDay() {
  	self::setWorkUnit();
    return self::$hoursPerDay;
  }
  public static function displayWorkUnit() {
    self::setWorkUnit();
    $res='<b>' . i18n('paramWorkUnit') . " = " . i18n(self::$workUnit) . '</b>';
    if (self::$workUnit=="hours") {
      $res.= ' - ' . i18n('paramDayTime') . " = " . self::$hoursPerDay ;
    } 
    return $res;
  }
}
?>