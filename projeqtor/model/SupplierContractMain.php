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

require_once('_securityCheck.php');
class SupplierContractMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    
  public $name;
  public $number;
  public $idSupplierContractType;
  public $idProject;
  public $idUser;
  public $idProvider;
  public $tenderReference;
  public $Origin;
  public $description;
  public $_sec_Progress;
  public $_tab_4_1=array('startDate', 'endDate','','', 'contractDate');
  public $startDate;
  public $endDate;
  public $_void_1;
  public $_void_2;
  public $initialContractTerm;
  public $idUnitContract;
  public $noticePeriod;
  public $idUnitNotice;
  public $noticeDate;
  public $deadlineDate;
  public $periodicityContract;
  public $_lib_helpPeriodicityContract;
  public $periodicityBill;
  public $_lib_helpPeriodicityBill;
  public $_sec_Treatment_right;
  public $idResource;
  public $idStatus;
  public $idRenewal;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $_sec_supplierContact;
  public $idContactContract;
  public $phoneNumber;
  public $sla;
  public $_lib_help_sla;
  public $_tab_2_3_allowWrap=array('StartTime', 'EndTime' , 'weekPeriod','saturdayPeriod','sundayAndOffDayPeriod');
  public $weekPeriod;
  public $weekPeriodEnd;
  public $saturdayPeriod;
  public $saturdayPeriodEnd;
  public $sundayAndOffDayPeriod;
  public $sundayAndOffDayPeriodEnd;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  
  private static $_layout='
          <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
          <th field="nameProject" width="15%" >${idProject}</th>
          <th field="name" width="30%" >${name}</th>
          <th field="number" width="15%" >${number}</th>
          <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
          <th field="startDate" width="10%" formatter="dateFromatter">${startDate}</th>
          <th field="endDate" width="10%" formatter="dateFromatter">${endDate}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required",  
                                  "idProject"=>"required",
                                  "idSupplierContractType"=>"required",
                                  "done"=>"nobr",
                                  "handled"=>"nobr",
                                  "idle"=>"nobr",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "idStatus"=>"required",
                                  "startDate"=>"nobr",
                                  "periodicityContract"=>"nobr",
                                  "periodicityBill"=>"nobr",
                                  "sla"=>"nobr",
                                  "weekPeriod"=>"nobr",
                                  "sundayAndOffDayPeriod"=>"nobr",
                                  "startDate"=>"nobr",          
                                  "initialContractTerm"=>"nobr",
                                  "idUnitContract"=>"size1/3",
                                  "noticePeriod"=>"nobr",
                                  "idUnitNotice"=>"size1/3"
  );   
 
  private static $_colCaptionTransposition = array(
   'idUser'=>'issuer',
   'idContactContract'=>'idContact',
   'idProvider'=>'idProviderContract',
   'idResource'=>'responsible',
   'idUnitContract'=>'idUnitDurationContract',
   'idUnitNotice'=>'idUnitDurationNotice',
  );
  
  private static $_databaseColumnName = array( 
      'idUnitContract'=>'idUnitDurationContract', 
      'idUnitNotice' => 'idUnitDurationNotice');
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
  	parent::__construct($id,$withoutDependentObjects);
  	if (!$this->id) {
  	  $this->idUnitNotice=2;
  	  $this->idUnitContract=2;
  	}
  	if ($withoutDependentObjects) return;
  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

  public function setAttributes() {
    if (!$this->id) {
      self::$_fieldsAttributes['approved']='readonly,nobr';
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
   * Return the specific databaseColumnName
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
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    if ($colName=='startDate') {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .="    var end=dijit.byId('endDate');";
      $colScript .="    end.set('dropDownDefaultValue',startDate);";
      $colScript .="    end.constraints.min=startDate; ";
      $colScript .="    setDatesContract('startDate');";
      $colScript .= '</script>';
    }else if($colName=='idUnitContract'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .="     setDatesContract('idUnitContract');";
      $colScript .= '</script>';
    }else if($colName=='initialContractTerm'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .=" if(isNaN(this.value))dijit.byId('initialContractTerm').set('value',0);";
      $colScript .="     setDatesContract('initialContractTerm');";
      $colScript .= '</script>';
    }else if($colName=='endDate'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .="    var start=dijit.byId('startDate');";
      $colScript .="    start.set('dropDownDefaultValue',this.value);";
      $colScript .="    start.constraints.max=this.value; ";
      $colScript .="    setDatesContract('endDate');";
      $colScript .= '</script>';
    }else if($colName=='noticeDate') {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .="     setDatesContract('noticeDate');";
      $colScript .= '</script>';
    }else if($colName=='idUnitNotice'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .="     setDatesContract('idUnitNotice');";
      $colScript .= '</script>';
    }else if($colName=='noticePeriod'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .=" if(isNaN(this.value))dijit.byId('noticePeriod').set('value',0);";
      $colScript .="     setDatesContract('noticePeriod');";
      $colScript .= '</script>';
    }else if($colName=='periodicityContract'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .=" if(isNaN(this.value))dijit.byId('periodicityContract').set('value',0);";
      $colScript .= '</script>';
    }else if($colName=='periodicityBill'){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .=" if(isNaN(this.value))dijit.byId('periodicityBill').set('value',0);";
      $colScript .= '</script>';
    }else if ($colName=="idProvider") {
			$colScript .= '<script type="dojo/connect" event="onChange" >';
			$colScript .= '  refreshList("idContactContract", "idProvider", this.value, null, null, false);';
			$colScript .= '  formChanged();';
			$colScript .= '</script>';
	  } else if ($colName=="idContactContract") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.value) {';
      $colScript .= '    dojo.xhrGet({';
      $colScript .= '      url: "../tool/getSingleData.php?dataType=contactPhone&idContact=" + this.value,';
      $colScript .= '      handleAs: "text",';
      $colScript .= '      load: function (data) {dijit.byId("phoneNumber").set("value",data);}';
      $colScript .= '    });';
      $colScript .= '  };';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;
  }
  
  
  public function control(){
    $result="";
    if(!$this->initialContractTerm){
      $this->initialContractTerm=0;
    }
    if(!$this->noticePeriod){
      $this->noticePeriod=0;
    }
    if(!$this->periodicityBill){
      $this->periodicityBill=0;
    }
    if(!$this->periodicityContract){
      $this->periodicityContract=0;
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save() {
    
    $result=parent::save();
    return $result;
  }
  
  }
?>