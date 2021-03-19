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
class PeriodicMeetingMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idMeetingType;
  public $idProject;
  public $location;
  public $idUser;
  public $description;
  public $_sec_treatment;
  public $idActivity;
  public $idResource;
  public $idle;
  public $_sec_periodicity;
  public $periodicityStartDate;
  public $_lib_periodicUntil;
  public $periodicityEndDate;
  public $_lib_periodicFor;
  public $periodicityTimes;
  public $_lib_periodicTimes;
  public $meetingStartTime;
  public $_lib_to;
  public $meetingEndTime;
  public $idPeriodicity;
  public $_spe_periodicity;
  public $periodicityOpenDays;
  public $periodicityDailyFrequency;
  public $periodicityWeeklyFrequency;
  public $periodicityWeeklyDay;
  public $periodicityMonthlyDayFrequency;
  public $periodicityMonthlyDayDay;
  public $periodicityMonthlyWeekFrequency;
  public $periodicityMonthlyWeekNumber;
  public $periodicityMonthlyWeekDay;
  public $periodicityYearlyDay;
  public $periodicityYearlyMonth;
  public $_sec_Attendees;
  public $_Assignment=array();
  public $attendees;
  public $_spe_buttonAssignTeam;
  public $_sec_progress;
  public $MeetingPlanningElement;
//   public $_sec_predecessor;
//   public $_Dependency_Predecessor=array();
//   public $_sec_successor;
//   public $_Dependency_Successor=array();
  public $_Note=array();
  public $idPeriodicMeeting;

  public $_nbColMax=3;
  // Define the layout that will be used for lists

  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameMeetingType" width="15%" >${idMeetingType}</th>
    <th field="periodicityStartDate" formatter="dateFormatter" width="10%" >${startDate}</th>
    <th field="periodicityEndDate" formatter="dateFormatter" width="10%" >${endDate}</th>
    <th field="namePeriodicity" formatter="translateFormatter" width="10%" >${idPeriodicity}</th>
    <th field="meetingStartTime" formatter="dateFormatter" width="10%" >${meetingStartTime}</th>
    <th field="name" width="25%" >${name}</th>
    ';
  
  
  private static $_fieldsAttributes=array("idProject"=>"required",
                                  "idMeetingType"=>"required",
                                  "periodicityStartDate"=>"required, nobr",
                                  "_lib_periodicUntil"=>'nobr',
                                  "periodicityEndDate"=>"nobr",
                                  "_lib_periodicFor"=>'nobr',      
                                  "periodicityTimes"=>'nobr,smallWidth',                            
                                  "meetingStartTime"=>'nobr',
                                  "_lib_to"=>'nobr',
                                  "meetingEndTime"=>'',
                                  "idUser"=>"hidden",
                                  "idResource"=>"",
                                  "idStatus"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
  "idPeriodicity"=>"required",
  'periodicityDailyFrequency'=>'hidden',
  'periodicityWeeklyFrequency'=>'hidden',
  'periodicityWeeklyDay'=>'hidden',
  'periodicityMonthlyDayFrequency'=>'hidden',
  'periodicityMonthlyDayDay'=>'hidden',
  'periodicityMonthlyWeekFrequency'=>'hidden',
  'periodicityMonthlyWeekNumber'=>'hidden',
  'periodicityMonthlyWeekDay'=>'hidden',
  'periodicityYearlyDay'=>'hidden',
  'periodicityYearlyMonth'=>'hidden',
  'idPeriodicMeeting'=>'hidden,calculated'
  );  
  
  private static $_colCaptionTransposition = array(
    'idResource'=>'responsible',
    'description'=>'meetingAgenda',
    'idActivity'=>'parentActivity',
    'periodicityStartDate'=>'period',
    'meetingStartTime'=>'time',
    'attendees'=>'otherAttendees' );
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
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

    if ($colName=="periodicityEndDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (this.value) {';
      $colScript .= '  dijit.byId("periodicityTimes").set("value", null); ';
      $colScript .= '  formChanged();';
      $colScript .= '}';
      $colScript .= '</script>';     
    } else if ($colName=="periodicityTimes") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (this.value) {';
      $colScript .= '  dijit.byId("periodicityEndDate").set("value", null); ';
      $colScript .= '  formChanged();';
      $colScript .= '}';
      $colScript .= '</script>';           
    } else if ($colName=="idPeriodicity") {
    	$colScript .= '<script type="dojo/connect" event="onChange" >';
    	$colScript .= 'var arrPer=new Array();';
    	$colScript .= 'arrPer[1]="DAY";';
    	$colScript .= 'arrPer[2]="WEEK";';
    	$colScript .= 'arrPer[3]="MONTHDAY";';
    	$colScript .= 'arrPer[4]="MONTHWEEK";';
    	$colScript .= 'arrPer[5]="YEAR";';
    	$colScript .= 'for (i=1;i<=5; i++) {';
    	$colScript .= '  if (i==this.value) {';
    	$colScript .= '    dojo.byId(arrPer[i]).style.display="block";';
    	$colScript .= '  } else {';
    	$colScript .= '    dojo.byId(arrPer[i]).style.display="none";';
    	$colScript .= '  }';
    	$colScript .= '}';
    	$colScript .= 'formChanged();';
    	$colScript .= '</script>';      
    } else if ($colName=="periodicityStartDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= ' weekday=new Date(this.value).getDay();';
      $colScript .= ' if (weekday==0) weekday=7;';
      $colScript .= '  dijit.byId("periodicityWeeklyDayId").set("value",weekday);';
      $colScript .= '  day=new Date(this.value).getDate();';
      $colScript .= '  month=new Date(this.value).getMonth()+1;';
      $colScript .= '  dijit.byId("periodicityMonthlyDayDayId").set("value",day);';
      $colScript .= '  dijit.byId("periodicityMonthlyWeekDayId").set("value",weekday);';
      $colScript .= '  dijit.byId("periodicityYearlyDayId").set("value",day);';
      $colScript .= '  dijit.byId("periodicityYearlyMonthId").set("value",month);';
      $colScript .= ' formChanged();';
      $colScript .= '</script>';   
    }
    return $colScript;
  }

  public function drawSpecificItem($item){
    global $print;
    $result="";
    if ($item=='periodicity') {
    	
    	$result.='<div style="display:'.(($this->idPeriodicity==1)?'block':'none').'" id="DAY">';
    	$result.='<table><tr><td class="label"></td><td>';
    	$result.=i18n('periodicEvery').'&nbsp;';
    	$result.='<div dojoType="dijit.form.NumberTextBox" style="width: 20px;"  ';
    	$result.='   constraints="{min:0,max:99}" name="periodicityDailyFrequency" ';
    	$result.='   value="'.(($this->periodicityDailyFrequency)?$this->periodicityDailyFrequency:1).'" class="input"></div>';
    	$result.='&nbsp;'.i18n('days');
    	$result.='</td></tr></table>';
    	$result.='</div>';
    	
    	$result.='<div style="display:'.(($this->idPeriodicity==2)?'block':'none').'" id="WEEK">';
      $result.='<table><tr><td class="label"></td><td>';
      $result.=i18n('periodicOn');
      $result.='&nbsp;<select dojoType="dijit.form.FilteringSelect" style="width: 120px;"  ';
      $result.=autoOpenFilteringSelect();
      $result.='   name="periodicityWeeklyDay" id="periodicityWeeklyDayId" class="input" labelType="html">';
      $result.=htmlReturnOptionForWeekdays($this->periodicityWeeklyDay, true);
      $result.='</select>';
      $result.='&nbsp;'.i18n('periodicEvery').'&nbsp;';
      $result.='<div dojoType="dijit.form.NumberTextBox" style="width: 20px;"  ';
      $result.='   constraints="{min:0,max:99}" name="periodicityWeeklyFrequency" ';
      $result.='   value="'.(($this->periodicityWeeklyFrequency)?$this->periodicityWeeklyFrequency:1).'" class="input"></div>';
      $result.='&nbsp;'.i18n('periodicWeeks');   
      $result.='</td></tr></table>';     
    	$result.='</div>';
      
    	$result.='<div style="display:'.(($this->idPeriodicity==3)?'block':'none').'" id="MONTHDAY">';
      $result.='<table><tr><td class="label"></td><td>';
      $result.=i18n('day').'&nbsp;';
      $result.='<div dojoType="dijit.form.NumberTextBox" style="width: 20px;"  ';
      $result.='   constraints="{min:0,max:31}" name="periodicityMonthlyDayDay" id="periodicityMonthlyDayDayId"';
      $result.='   value="'.(($this->periodicityMonthlyDayDay)?$this->periodicityMonthlyDayDay:1).'" class="input"></div>';
      $result.='&nbsp;'.i18n('periodicEvery');
      $result.='&nbsp;<div dojoType="dijit.form.NumberTextBox" style="width: 20px;"  ';
      $result.='   constraints="{min:0,max:12}" name="periodicityMonthlyDayFrequency" ';
      $result.='   value="'.(($this->periodicityMonthlyDayFrequency)?$this->periodicityMonthlyDayFrequency:1).'" class="input"></div>';
      $result.='&nbsp;'.i18n('periodicMonths');
      $result.='</td></tr></table>';
      $result.='</div>';
      
    	$result.='<div style="display:'.(($this->idPeriodicity==4)?'block':'none').'" id="MONTHWEEK">';
      $result.='<table><tr><td class="label"></td><td>';
      $result.=i18n('periodicOn').'&nbsp;';
      $result.='<div dojoType="dijit.form.NumberTextBox" style="width: 20px;"  ';
      $result.='   constraints="{min:0,max:5}" name="periodicityMonthlyWeekNumber" ';
      $result.='   value="'.(($this->periodicityMonthlyWeekNumber)?$this->periodicityMonthlyWeekNumber:1).'" class="input"></div>';
      $result.=i18n('periodicTh');
      $result.='&nbsp;<select dojoType="dijit.form.FilteringSelect" style="width: 120px;"  ';
      $result.=autoOpenFilteringSelect();
      $result.='   name="periodicityMonthlyWeekDay" id="periodicityMonthlyWeekDayId" class="input" labelType="html">';
      $result.=htmlReturnOptionForWeekdays($this->periodicityMonthlyWeekDay, true);
      $result.='</select>';
      $result.='&nbsp;'.i18n('periodicEvery');
      $result.='&nbsp;<div dojoType="dijit.form.NumberTextBox" style="width: 20px;"  ';
      $result.='   constraints="{min:0,max:12}" name="periodicityMonthlyWeekFrequency" ';
      $result.='   value="'.(($this->periodicityMonthlyWeekFrequency)?$this->periodicityMonthlyWeekFrequency:1).'" class="input"></div>';
      $result.='&nbsp;'.i18n('periodicMonths');
      $result.='</td></tr></table>';
    	$result.='</div>';
    	
    	$result.='<div style="display:'.(($this->idPeriodicity==5)?'block':'none').'" id="YEAR">';
    	$result.='<table><tr><td class="label"></td><td>';
    	$result.=i18n('periodicOn').'&nbsp;';
    	$result.='<div dojoType="dijit.form.NumberTextBox" style="width: 20px;"  ';
      $result.='   constraints="{min:0,max:31}" name="periodicityYearlyDay" id="periodicityYearlyDayId"';
      $result.='   value="'.(($this->periodicityYearlyDay)?$this->periodicityYearlyDay:1).'" class="input"></div>';
      //$result.=i18n('periodicTh');
      $result.='&nbsp;<select dojoType="dijit.form.FilteringSelect" style="width: 120px;"  ';
      $result.=autoOpenFilteringSelect();
      $result.='   name="periodicityYearlyMonth" id="periodicityYearlyMonthId" class="input" labelType="html">';
      $result.=htmlReturnOptionForMonths($this->periodicityYearlyMonth, true);
      $result.='</select>';
      $result.='</td></tr></table>';
      $result.='</div>';
    }
    // Gautier ticket #2838
    if ($item=='buttonAssignTeam') {
      $result .= '<tr><td valign="top" class="label"><label></label></td><td>';
      $result .= '<button style="height:71% !important;" class="dynamicTextButton" id="attendeesAllTeam2" dojoType="dijit.form.Button" showlabel="true" onClick ="assignTeamForMeeting()"';
      $result .= ' title="' . i18n('buttonAssignWholeTeam') . '" >';
      $result .= '<span>' . i18n('buttonAssignWholeTeam') . '</span>';
      $result .= '</button>';
      $result .= '</td></tr>';
    }
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
    if ($this->MeetingPlanningElement and $this->MeetingPlanningElement->realWork>0 and !$canDeleteRealWork) {
      $result.='<br/>' . i18n('msgUnableToDeleteRealWork');
    }
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }
  public function delete() {
  	
  	// The delete cascades delete of meetings, so PlanningElement will not correctly be destroyed.
  	projeqtor_set_time_limit(600); // Set time limit to 10mn
  	$meet=new Meeting();
  	$lstMeet=$meet->getSqlElementsFromCriteria(array('idPeriodicMeeting'=>$this->id));
  	foreach ($lstMeet as $meet) {
  		projeqtor_set_time_limit(300);
  		$meeting=new Meeting($meet->id);
  		$resDel=$meeting->delete();
  		if (stripos($resDel,'id="lastOperationStatus" value="OK"')==0 ) {
        return $resDel;
      }    
  	}	
  	$result=parent::delete();
  	
  	// If delete is successfull, check if some meeting could not be deleted (because of real work existing)
  	// then remove reference to periodic for this meeting
  	if (stripos($result,'id="lastOperationStatus" value="OK"')>=0 ) {
  		$meet=new Meeting();
	    $lstMeet=$meet->getSqlElementsFromCriteria(array('idPeriodicMeeting'=>$this->id));
	    foreach ($lstMeet as $meet) {
	      $meeting=new Meeting($meet->id);
	      $meeting->idPeriodicMeeting=null;
	      $meeting->isPeriodic=false;
	      $resMaj=$meeting->save();
	      if (stripos($resMaj,'id="lastOperationStatus" value="OK"')==0 ) {
	        return $resMaj;
	      }    
	    } 
  	}
  	return $result;
  }
  
  public function control(){
    $result="";
    if (trim($this->idActivity)) {
      $parentActivity=new Activity($this->idActivity);
      if ($parentActivity->idProject!=$this->idProject) {
        $result.='<br/>' . i18n('msgParentActivityInSameProject');
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
  

  public function save() {
    $old=$this->getOld(false);
  	if (! $this->name) {
      $this->name=SqlList::getNameFromId('MeetingType',$this->idMeetingType);
  	}
    $listTeam=array_map('strtolower',SqlList::getList('Team','name'));
    $listName=array_map('strtolower',SqlList::getList('Affectable'));
    $listUserName=array_map('strtolower',SqlList::getList('Affectable','userName'));
    $listInitials=array_map('strtolower',SqlList::getList('Affectable','initials'));
    if ($this->attendees) {
      $listAttendees=explode(',',str_replace(';',',',$this->attendees));
      $this->attendees="";
      foreach ($listAttendees as $attendee) {
      	$stockAttendee=$attendee;
        $attendee=strtolower(trim($attendee));
        if (in_array($attendee,$listName)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listName));
          $this->attendees.='"' . $aff->name . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listUserName)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listUserName));
          $this->attendees.='"' . (($aff->name)?$aff->name:$stockAttendee) . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listInitials)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listInitials));         
          $this->attendees.='"' . ( ($aff->name)?$aff->name:(($aff->userName)?$aff->userName:$stockAttendee)) . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listTeam)) {
          $this->attendees.=($this->attendees)?', ':'';
          $id=array_search($attendee,$listTeam);
          $aff=new Affectable();
          $lst=$aff->getSqlElementsFromCriteria(array('idTeam'=>$id));
          foreach ($lst as $aff) {
            $this->attendees.=($this->attendees)?', ':'';
            $this->attendees.='"' . ( ($aff->name)?$aff->name:(($aff->userName)?$aff->userName:$stockAttendee)) . '"';
            if ($aff->email) {
              $this->attendees.=' <' . $aff->email . '>';
            }
          }
        } else {
          $this->attendees.=($this->attendees)?', ':'';
          $this->attendees.=$stockAttendee;
        }
      }
      $this->attendees=str_ireplace(',  ', ', ', $this->attendees);
      $this->attendees=str_ireplace(',  ', ', ', $this->attendees);
    }
    if (trim($this->idProject)!=trim($old->idProject) or trim($this->idActivity)!=trim($old->idActivity)) {
      $this->MeetingPlanningElement->wbs=null;
      $this->MeetingPlanningElement->wbsSortable=null;
    }
    $result=parent::save();
    if (isset($this->_moveToAfterCreate)) unset($this->_moveToAfterCreate);
    if (stripos($result,'id="lastOperationStatus" value="OK"')==0 ) {
    	return $result;
    }    
    // Create / Update meetings
    $nb=0;
    $currentDate=$this->periodicityStartDate;
    $maxDate=addMonthsToDate($currentDate, 36);
    $nbWeekDay=0;
    if ($this->idPeriodicity==4) {
    	$currentDate=substr($currentDate,0,8).'01';
    } else if ($this->idPeriodicity==5) {
      $currentDate=substr($currentDate,0,4).'-'.htmlFixLengthNumeric($this->periodicityYearlyMonth,2)
                                           .'-'.htmlFixLengthNumeric($this->periodicityYearlyDay,2);
    }    
    $currentMonth=substr($currentDate,5,2);
    $lastDate=$currentDate;
    if ($this->periodicityEndDate) {$this->periodicityTimes=null;}
    if (! $this->periodicityDailyFrequency) $this->periodicityDailyFrequency=1;
    if (! $this->periodicityWeeklyFrequency) $this->periodicityWeeklyFrequency=1;
    if (! $this->periodicityWeeklyDay) $this->periodicityWeeklyDay=1;
    if ($this->periodicityOpenDays and $this->periodicityWeeklyDay>=6) $this->periodicityOpenDays=0;  
    if (! $this->periodicityMonthlyDayDay) $this->periodicityMonthlyDayDay=1;
    if (! $this->periodicityMonthlyDayFrequency) $this->periodicityMonthlyDayFrequency=1;
    while ( (    ($this->periodicityEndDate and $currentDate<=$this->periodicityEndDate) 
              or ($this->periodicityTimes and $nb<$this->periodicityTimes) )
           and $currentDate<$maxDate) {
    	if ($this->idPeriodicity==1) { // DAILY
    		if (! $this->periodicityOpenDays or isOpenDay($currentDate,'1')) {
    			$nb++;
    			$this->saveMeeting($currentDate, $nb, $old);
    			$lastDate=$currentDate;
    		}
    		$currentDate=addDaysToDate($currentDate, $this->periodicityDailyFrequency);
    	}
    	
      if ($this->idPeriodicity==2) { // WEEKLY
        if ($this->periodicityWeeklyDay==date('N', strtotime($currentDate)) ) {
          if (! $this->periodicityOpenDays or isOpenDay($currentDate,'1')) {       	
	          $nb++;
	          $this->saveMeeting($currentDate, $nb, $old);
	          $lastDate=$currentDate;
          }
          $currentDate=addDaysToDate($currentDate, 7*$this->periodicityWeeklyFrequency);
        } else {
        	$currentDate=addDaysToDate($currentDate, 1);
        }
      }
      
      if ($this->idPeriodicity==3) { // MONTHLY DAY
        if ($this->periodicityMonthlyDayDay==substr($currentDate,8,2)) {
          if (! $this->periodicityOpenDays or isOpenDay($currentDate,'1')) {        
            $nb++;
            $this->saveMeeting($currentDate, $nb, $old);
            $lastDate=$currentDate;
          }
          $currentDate=addMonthsToDate($currentDate, $this->periodicityMonthlyDayFrequency);
        } else {
        	if ($this->periodicityMonthlyDayDay<substr($currentDate,8,2)) {
        		$currentDate=substr($currentDate,0,8).'01';
        		$currentDate=addMonthsToDate($currentDate, 1);
        	}          
          $currentDate=substr($currentDate,0,8).htmlFixLengthNumeric($this->periodicityMonthlyDayDay,2);
          if (! $this->periodicityOpenDays or isOpenDay($currentDate,'1')) {        
            $nb++;
            $this->saveMeeting($currentDate, $nb, $old);
            $lastDate=$currentDate;
          }
          $currentDate=addMonthsToDate($currentDate, $this->periodicityMonthlyDayFrequency);
        }
      }
      
      if ($this->idPeriodicity==4) { // MONTHLY WEEK
      	if ($this->periodicityMonthlyWeekDay==date('N', strtotime($currentDate)) ) {
      		$nbWeekDay+=1;
      		if ($nbWeekDay==$this->periodicityMonthlyWeekNumber) {
      			if ( (! $this->periodicityOpenDays or isOpenDay($currentDate,'1') )
      			  and $currentDate>=$this->periodicityStartDate 
      			  and substr($currentDate,5,2)==$currentMonth ) {  
      				$nb++;
              $this->saveMeeting($currentDate, $nb, $old);
              $lastDate=$currentDate;
      			}
      			$nbWeekDay=0;
      			$currentDate=substr($currentDate,0,8).'01';
      			if ($currentMonth==substr($currentDate,5,2)) {
      			  $currentDate=addMonthsToDate($currentDate, $this->periodicityMonthlyWeekFrequency);
      			}
      			$currentMonth=substr($currentDate,5,2);
      		} else {
      		  $currentDate=addDaysToDate($currentDate, 7);
      		}
        } else {          
          $currentDate=addDaysToDate($currentDate, 1);
        }
      }
      
      if ($this->idPeriodicity==5) { // YEARLY
        if ( (! $this->periodicityOpenDays or isOpenDay($currentDate,'1') )
        and $currentDate>=$this->periodicityStartDate ) {  
          $nb++;
          $this->saveMeeting($currentDate, $nb, $old);
          $lastDate=$currentDate;
        }
        $currentDate=addMonthsToDate($currentDate, 12);
      }

    }
    // Purge old meeting (if number of meeting is less that previous one
    $meet=new Meeting;
    $crit="idPeriodicMeeting=".$this->id." and isPeriodic=1 and periodicOccurence>".$nb;
    $lstMeet=$meet->getSqlElementsFromCriteria(null, false,$crit);
    foreach ($lstMeet as $mt) {
    	$meet=new Meeting($mt->id);
    	if ($meet->MeetingPlanningElement->realWork==0) {
    	  $res=$meet->delete();
	    	if (stripos($res,'id="lastOperationStatus" value="OK"')==0) {
	    		$nb++;
	    	}
    	} else {
    		$nb++;
    	}
    }
    if (!$this->periodicityTimes) {
      $this->periodicityTimes=$nb;
    } 
    if (! $this->periodicityEndDate) {
    	$this->periodicityEndDate=$lastDate;
    }
    $this->MeetingPlanningElement->assignedCost=0;
    $this->MeetingPlanningElement->realCost=0;
    $this->MeetingPlanningElement->leftCost=0;
    parent::save();
    return $result;
  }
  private function saveMeeting($currentDate, $nb, $old) {
  	projeqtor_set_time_limit(300);
  	$critArray=array("idPeriodicMeeting"=>$this->id, "isPeriodic"=>'1',"periodicOccurence"=>$nb);
  	$meeting=SqlElement::getSingleSqlElementFromCriteria('Meeting', $critArray,false);
  	$isNew=($meeting->id)?false:true;
  	$meeting->idProject=$this->idProject;
    $meeting->idMeetingType=$this->idMeetingType;
    $meeting->idPeriodicMeeting=$this->id;
    $meeting->isPeriodic=1;
    $meeting->periodicOccurence=$nb;
    $meeting->meetingDate=$currentDate;
    if ($old->meetingStartTime!=$this->meetingStartTime) $meeting->meetingStartTime=$this->meetingStartTime;
    if ($old->meetingEndTime!=$this->meetingEndTime) $meeting->meetingEndTime=$this->meetingEndTime;
    $meeting->name=$this->name . " #".$nb;
    if ($old->location!=$this->location) $meeting->location=$this->location;
    if ($old->attendees!=$this->attendees) $meeting->attendees=$this->attendees;
    $meeting->idUser=$this->idUser;
    if ($old->description!=$this->description) $meeting->description=$this->description;
    $meeting->idActivity=null;
    if (! $meeting->idStatus) {
      $table=SqlList::getList('Status');
      reset($table);
      $meeting->idStatus=key($table);
    }
    if ($old->location!=$this->idResource) $meeting->idResource=$this->idResource;
    if ($old->MeetingPlanningElement->priority!=$this->MeetingPlanningElement->priority) 
        $meeting->MeetingPlanningElement->priority=$this->MeetingPlanningElement->priority;
    $meeting->MeetingPlanningElement->color=$this->MeetingPlanningElement->color;
    if (isset($meeting->_moveToAfterCreate)) unset($meeting->_moveToAfterCreate);
    $resultMeetingSave=$meeting->save();
    $resultMeetingSaveStatus=getLastOperationStatus($resultMeetingSave);
    if ($resultMeetingSaveStatus!='OK' and $resultMeetingSaveStatus!='NO_CHANGE') {
      traceLog("saveMeeting() for Periodic Meeting #$this->id => unexpected result for save of Meeting : $resultMeetingSaveStatus");
    }
    if ($isNew) {
      $ass=new Assignment();
      $assList=$ass->getSqlElementsFromCriteria(array('refType'=>'PeriodicMeeting','refId'=>$this->id));
      foreach ($assList as $assPeriodic) {
        $ass=new Assignment();
        $ass->refType='Meeting';
        $ass->refId=$meeting->id;
        $ass->idResource=$assPeriodic->idResource;
        $ass->idRole=$assPeriodic->idRole;
        $ass->idProject=$assPeriodic->idProject;
        $ass->comment=$assPeriodic->comment;
        $ass->assignedWork=$assPeriodic->assignedWork;
        $ass->leftWork=$assPeriodic->assignedWork;
        $ass->plannedWork=$assPeriodic->assignedWork;
        $ass->realWork=0;
        $ass->rate=$assPeriodic->rate;
        $ass->dailyCost=$assPeriodic->dailyCost;
        $ass->assignedCost=$assPeriodic->assignedCost;
        $ass->leftCost=$assPeriodic->assignedCost;
        $ass->realCost=0;
        $ass->plannedCost=$ass->assignedCost;
        $ass->idle=0;
        $resAss=$ass->save();
      }
    }
  }
  
  public function setAttributes() { 
    /*if (! $this->id) {
      unset($this->_sec_progress);
    } else {
      $pe=new PlanningElement();
      $pe->setVisibility();
      if ($pe->_workVisibility!='ALL') {
        unset($this->_sec_progress);
      } else {
        if (count($this->_Assignment)==0) {
          unset($this->_sec_progress);
        }
      }
    }*/
  }
  
  public function copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments,$withLinks, $withAssignments=false, $withAffectations=false, $toProject=null, $toActivity=null, $copyToWithResult=false, $copyToWithVersionProjects=false){
    $result = parent::copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, false, false,false,false,$toProject);
    $ass=new Assignment();
    $crit=array('refId'=>$this->id,'refType'=>'PeriodicMeeting');
    $list=$ass->getSqlElementsFromCriteria($crit);
    foreach ($list as $ass) {
      $newAss = new Assignment();
      $newAss->idResource= $ass->idResource;
      $newAss->refId = $result->id;
      $newAss->refType = 'PeriodicMeeting';
      $newAss->assignedWork = $ass->assignedWork;
      $newAss->leftWork = $ass->leftWork;
      $newAss->idProject = $ass->idProject;
      $newAss->save();
    }
    return $result;
  }
}
?>