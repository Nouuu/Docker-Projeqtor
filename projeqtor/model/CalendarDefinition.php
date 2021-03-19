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

// GENERIC DAY OFF
// Introduice days of week that are'nt worked and generic day off (for exemple public holidays)
// GENERIC DAY OFF

/* ============================================================================
 * Manage the CalendarDefinition Sql Element.
 */  
require_once('_securityCheck.php'); 
class CalendarDefinition extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  //public $sortOrder=0;
  public $idle;
// MTY - GENERIC DAY OFF
  public $_sec_weekDayOff;
    public $dayOfWeek0;
    public $dayOfWeek1;
    public $dayOfWeek2;
    public $dayOfWeek3;
    public $dayOfWeek4;
    public $dayOfWeek5;
    public $dayOfWeek6;
    public $_spe_dayOfWeek;
// MTY - GENERIC DAY OFF
  public $_sec_Year;
  public $_spe_year;
  public $_spe_copyFromDefault;
  public $_sec_BankOffDays;
    public $_spe_bankOffDays;
    public $_spe_buttonAddToCalendar;
  public $_sec_Calendar;
  public $_spe_calendar;
  public $_calendar_colSpan="2";
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="60%">${name}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  private static $_fieldsAttributes=array(
// MTY - GENERIC DAY OFF      
      "dayOfWeek0" => "hidden",
      "dayOfWeek1" => "hidden",
      "dayOfWeek2" => "hidden",
      "dayOfWeek3" => "hidden",
      "dayOfWeek4" => "hidden",
      "dayOfWeek5" => "hidden",
      "dayOfWeek6" => "hidden",
// MTY - GENERIC DAY OFF      
      "sortOrder"=>"hidden"      
      );
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
// MTY - GENERIC DAY OFF
    if (Parameter::getGlobalParameter('OpenDaySunday')=='offDays') {
        $this->dayOfWeek0 = 1;
        self::$_fieldsAttributes["dayOfWeek0"] = "readonly,hidden";
  }
    if (Parameter::getGlobalParameter('OpenDayMonday')=='offDays') {
        $this->dayOfWeek1 = 1;
        self::$_fieldsAttributes["dayOfWeek1"] = "readonly,hidden";        
    }
    if (Parameter::getGlobalParameter('OpenDayTuesday')=='offDays') {
        $this->dayOfWeek2 = 1;
        self::$_fieldsAttributes["dayOfWeek2"] = "readonly,hidden";                
    }
    if (Parameter::getGlobalParameter('OpenDayWednesday')=='offDays') {
        $this->dayOfWeek3 = 1;
        self::$_fieldsAttributes["dayOfWeek3"] = "readonly,hidden";                
    }
    if (Parameter::getGlobalParameter('OpenDayThursday')=='offDays') {
        $this->dayOfWeek4 = 1;
        self::$_fieldsAttributes["dayOfWeek4"] = "readonly,hidden";        
    }
    if (Parameter::getGlobalParameter('OpenDayFriday')=='offDays') {
        $this->dayOfWeek5 = 1;
        self::$_fieldsAttributes["dayOfWeek5"] = "readonly,hidden";        
    }
    if (Parameter::getGlobalParameter('OpenDaySaturday')=='offDays') {
        $this->dayOfWeek6 = 1;
        self::$_fieldsAttributes["dayOfWeek6"] = "readonly,hidden";        
    }
// MTY - GENERIC DAY OFF    
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
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
  	return self::$_fieldsAttributes;
  }
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
// MTY - GENERIC DAY OFF
  private function drawButtonAddToCalendar() {
      global $print;
      $result="";
      $readOnly=(securityGetAccessRightYesNo('menuCalendarDefinition', 'update', $this)=="YES"?"":"readonly ");
      if ($readOnly!="readonly " and !$print and $this->id>0) {
            $calBankOffDays = SqlElement::getFirstSqlElementFromCriteria("CalendarBankOffDays", array("idCalendarDefinition" => $this->id));
            if (isset($calBankOffDays->id)) {
                // Add only
                $result .= '<div id="div_btAddToCalendar" style="display:inline-block;">';
                $result .= '<button id="bt_AddToCalendar" dojoType="dijit.form.Button" showlabel="true"';
                $result .= ' class="roundedVisibleButton" title="' . i18n ( 'titleAddBankOffDaysToCalendar' ) . '" style="vertical-align: middle;">';
                $result .= '<span>' . i18n ( 'addBankOffDaysToCalendar' ) . '</span>';
                $result .= '<script type="dojo/connect" event="onClick" args="evt">';
                $result .= '   protectDblClick(this);';
                $result .= '   var year = dijit.byId("calendartYearSpinner").value;';
  		$result .= '   loadContent("../tool/addGenericBankOffDaysToCalendar.php?idCalendarDefinition='.htmlEncode($this->id).'&year="+year+"&clear=false","CalendarDefinition_Calendar");'; 	
                $result .= '</script>';
                $result .= '</button>';
                $result .= '</div>';
                $result .= '&nbsp;';
                // Clear Previous and Add
                $result .= '<div id="div_btClearAddToCalendar" style="display:inline-block;">';
                $result .= '<button id="bt_ClearAddToCalendar" dojoType="dijit.form.Button" showlabel="true"';
                $result .= ' class="roundedVisibleButton" title="' . i18n ( 'titleClearOldAndAddBankOffDaysToCalendar' ) . '" style="vertical-align: middle;">';
                $result .= '<span>' . i18n ( 'clearOldAndAddBankOffDaysToCalendar' ) . '</span>';
                $result .= '<script type="dojo/connect" event="onClick" args="evt">';
                $result .= '   protectDblClick(this);';
                $result .= '   var year = dijit.byId("calendartYearSpinner").value;';
  		$result .= '   loadContent("../tool/addGenericBankOffDaysToCalendar.php?idCalendarDefinition='.htmlEncode($this->id).'&year="+year+"&clear=true","CalendarDefinition_Calendar");'; 	
                $result .= '</script>';
                $result .= '</button>';
                $result .= '</div>';
            }
      }
      return $result;
  }
  private function drawBankOffDays() {
    global $print;
    $easterDay = array(
                        0 => i18n("easter"),
                        1 => i18n("ascension"),
                        2 => i18n("pentecost"),
                        3 => i18n("holyfriday"),
                        4 => ""
                       );
    $months = array (
                        0 => "",
                        1 => i18n("January"),
                        2 => i18n("February"),
                        3 => i18n("March"),
                        4 => i18n("April"),
                        5 => i18n("May"),
                        6 => i18n("June"),
                        7 => i18n("July"),
                        8 => i18n("August"),
                        9 => i18n("September"),
                        10 => i18n("October"),
                        11 => i18n("November"),
                        12 => i18n("December"),
                    );

    $result = "";
    $readOnly=(securityGetAccessRightYesNo('menuCalendarDefinition', 'update', $this)=="YES"?"":"readonly ");
    
    if ($readOnly!="readonly " and !$print and $this->id>1) {
    $result .= '<div type="button" dojoType="dijit.form.Button" showlabel="true">'
             . i18n('copyBankOffDaysFromCalendar')	
             . ' <script type="dojo/method" event="onClick" >'
             . ' 	   loadContent("../tool/copyBankOffDaysOfCalendarDefinition.php?copyFrom="+dijit.byId("bankOffDaysCopyFrom").get("value")+"&idCalendarDefinition='.htmlEncode($this->id).'","CalendarDefinition_BankOffDays");'
             . ' </script>'
             . '</div>&nbsp;&nbsp;';
    
    $result.='<select dojoType="dijit.form.FilteringSelect" class="input" xlabelType="html" '
                  . '  style="width:150px;" name="bankOffDaysCopyFrom" id="bankOffDaysCopyFrom" '.autoOpenFilteringSelect().'>';
    ob_start();
                  htmlDrawOptionForReference('idCalendarDefinition', 1, null, true);
                  $result.=ob_get_clean();
                  $result.= '</select>';
    }

    $result .= '<table style="width:100%">';
    // HEADER
    $result .= '<tr>';    
    // Action Add
    $result .= '<td class="linkData" style="text-align:center;white-space: nowrap; width:10%">';
    if ($readOnly!="readonly " and !$print and $this->id>0) {
        $result .= '<a onClick="addGenericBankOffDays('.htmlEncode($this->id).')"> '.formatSmallButton('Add').'</a>';
    }
    $result .= '</td>';
    $result .= '<td class="assignHeader" style="width:30%"><b>'.i18n("colName").'</b></td>';
    $result .= '<td class="assignHeader" style="width:30%"><b>'.i18n("month").'</b></td>';
    $result .= '<td class="assignHeader" style="width:10%"><b>'.i18n("day").'</b></td>';
    $result .= '<td class="assignHeader" style="width:20%"><b>'.i18n("easterDay").'</b></td>';
    $result .= '</tr>';
    
    // LINES = CALENDAR BANK OFF DAYS
    $calBankOffDays = new CalendarBankOffDays();
    $calBankOffDaysList = $calBankOffDays->getSqlElementsFromCriteria(
                                array("idCalendarDefinition" => $this->id),
                                false, null, "month asc, day asc, easterDay asc"
                          );
    foreach ($calBankOffDaysList as $obj) {
        $result .= '<tr>';
        // Actions Button : Edit and Remove
        $result .= '<td class="linkData" style="text-align:center;white-space: nowrap;">';
        if ($readOnly!="readonly " and !$print) {
            // Edit
            $theName = str_replace("'", "\'", $obj->name);
            $editRef = '<a title="'.i18n('editGenericBankOffDays').'" ';
            $editRef .= 'onClick="editGenericBankOffDays(';
            $editRef .= '\''.htmlEncode($obj->id).'\',\''.htmlEncode($obj->idCalendarDefinition).'\',\''. htmlEncode($theName).'\',';
            $editRef .= '\''.htmlEncode($obj->month).'\',\''.htmlEncode($obj->day).'\',\''.htmlEncode($obj->easterDay).'\'';
            $editRef .= ');"> '.formatSmallButton('Edit').'</a>';
            $result .= $editRef;
            $result .= '&nbsp;';
            // Remove
            $result .= '<a title="'.i18n('removeGenericBankOffDays').'" ';
            $result .= 'onClick="removeGenericBankOffDays(';
            $result .= '\''.htmlEncode($obj->id).'\',\''.htmlEncode($theName).'\'';
            $result .= ');"> '.formatSmallButton('Remove').'</a>';
            
        }        
        $result .= '</td>';
        // DATAS
        $result .= '<td class="linkData">'.htmlEncode($obj->name).'</td>';
        if ($obj->month==null) {
            $monthName = $months[0];
        } else {
            $monthName = $months[$obj->month];
        }
        $result .= '<td class="linkData" align="center">'.htmlEncode($monthName).'</td>';        
        $result .= '<td class="linkData" align="center">'.htmlEncode($obj->day).'</td>';
        if ($obj->easterDay==null) {
            $easterDayName = $easterDay[4];
        } else {
            $easterDayName = $easterDay[$obj->easterDay];
        }
        $result .= '<td class="linkData" align="center">'.htmlEncode($easterDayName).'</td>';        
        $result .= '</tr>';
    }
    
    $result .= "</table>";
    $result .= $this->drawButtonAddToCalendar();
    return $result;
  }

  private function drawDayOffWeek() {
    $readOnlyObj=(securityGetAccessRightYesNo('menuCalendarDefinition', 'update', $this)=="YES"?"":"readonly ");
    $result = '<table style="width:100%">';
    // Header
    $result .= '<tr>';
    $result .= '<td style="width:15%"></td>';
    $result .= '<td class="assignHeader" style="width:12%"><b>'.i18n("Sunday").'</b></td>';
    $result .= '<td class="assignHeader" style="width:12%"><b>'.i18n("Monday").'</b></td>';
    $result .= '<td class="assignHeader" style="width:12%"><b>'.i18n("Tuesday").'</b></td>';
    $result .= '<td class="assignHeader" style="width:12%"><b>'.i18n("Wednesday").'</b></td>';
    $result .= '<td class="assignHeader" style="width:12%"><b>'.i18n("Thursday").'</b></td>';
    $result .= '<td class="assignHeader" style="width:12%"><b>'.i18n("Friday").'</b></td>';
    $result .= '<td class="assignHeader" style="width:12%"><b>'.i18n("Saturday").'</b></td>';
    $result .= '</tr>';

    // DayOf line
    $result .= '<tr>';
    $result .= '<td class="tabLabel">'.i18n('offDays').'&nbsp;:&nbsp;</td>';
    for ($i=0;$i<=6;$i++) {
        $itemName = "dayOfWeek".$i;
        if ($readOnlyObj=="") {
            if (strpos($this->getFieldAttributes($itemName),"readonly")!==false) {
                $readOnly = "readonly ";
            } else {
                $readOnly = "";
            }
        } else {
                $readOnly = "readonly ";
        }
        $itemId = "dayOfWeek_".$i;
        $result .= '<td class="linkData" style="text-align:center;white-space: nowrap;">';
        $result .= '<input data-dojo-type="dijit/form/CheckBox" id="'.$itemId.'" name="'.$itemId.'" '.$readOnly;
        $result .= ($this->$itemName==1?'checked ':'');
        $result .= 'onChange="';
        $result .= "    formChanged();";
        $result .= '"';
        $result .= '>';
        $result .= "</td>";                
    }
    $result .= "</tr>";

    $result .= "</table>";
    return $result;
  }
// MTY - GENERIC DAY OFF  
  
  public function drawSpecificItem($item){
    global $print;
  	//scriptLog("Project($this->id)->drawSpecificItem($item)");
  	$result="";
  	$cal=new Calendar;
  	$currentYear=date('Y');
  	if ($item=='calendar') {
  		//$result.='<div id="viewCalendarDiv" dojoType="dijit.layout.ContentPane" region="top">';  		
      $cal->setDates($currentYear.'-01-01');
      $cal->idCalendarDefinition=$this->id;
      $result= $cal->drawSpecificItem('calendarView');
      //$result.='</div>';
  		return $result;
  	} else if ($item=='year' and !$print) {
  		$result.='<div style="width:70px; text-align: center; color: #000000;" dojoType="dijit.form.NumberSpinner"'
  		 . ' constraints="{min:2000,max:2100,places:0,pattern:\'###0\'}" intermediateChanges="true" maxlength="4" '
       . ' value="'. $currentYear.'" smallDelta="1" id="calendartYearSpinner" name="calendarYearSpinner" >'
  		 . ' <script type="dojo/method" event="onChange" >'
  		 . '  saveDataToSession("calendarYear",this.value);'
  		 . '  saveDataToSession("calendarYearId",'.$this->id.');'
  		 . ' 	loadContent("../tool/saveCalendar.php?idCalendarDefinition='.htmlEncode($this->id).'&year="+this.value,"CalendarDefinition_Calendar");'
  		 . ' </script>'
  		 . '</div>';
  		 return $result;
  	} else if ($item=='copyFromDefault' and !$print) {
  		if ($this->id!=1) {
  		  $result.='<div type="button" dojoType="dijit.form.Button" showlabel="true">'
  			. i18n('copyFromCalendar')	
  		  . ' <script type="dojo/method" event="onClick" >'
  			. ' 	loadContent("../tool/saveCalendar.php?copyYearFrom="+dijit.byId("calendarCopyFrom").get("value")+"&idCalendarDefinition='.htmlEncode($this->id).'&year="+dijit.byId("calendartYearSpinner").get("value"),"CalendarDefinition_Calendar");'
  			. ' </script>'
  			. '</div>&nbsp;&nbsp;';
  		  $result.='<select dojoType="dijit.form.FilteringSelect" class="input" xlabelType="html" '
				. '  style="width:150px;" name="calendarCopyFrom" id="calendarCopyFrom" '.autoOpenFilteringSelect().'>';
  		  ob_start();
				htmlDrawOptionForReference('idCalendarDefinition', 1, null, true);
				$result.=ob_get_clean();
				$result.= '</select>';
				
				//Modif Damian
				$result.='</br>';
				$result.='<div type="button" dojoType="dijit.form.Button" showlabel="true">'
				       . i18n('MarkEvery')
				       . ' <script type="dojo/method" event="onClick" >'
				       . ' 	loadContent("../tool/saveCalendar.php?calendarWorkFrom="+dijit.byId("calendarWorkFrom").get("value")+"&calendarDayFrom="+dijit.byId("calendarDayFrom").get("value")+"&idCalendarDefinition='.htmlEncode($this->id).'&year="+dijit.byId("calendartYearSpinner").get("value"),"CalendarDefinition_Calendar");'
				       . ' </script>'
							 . '</div>&nbsp;&nbsp;';
				$result.= '<select dojoType="dijit.form.FilteringSelect" class="input" xlabelType="html" '
				          . 'style="width:150px;" name="calendarDayFrom" id="calendarDayFrom" '.autoOpenFilteringSelect().'>
        				    <option value="Monday" selected="selected">'.i18n('Monday').'</option>
        				    <option value="Tuesday">'.i18n('Tuesday').'</option>
        				    <option value="Wednesday">'.i18n('Wednesday').'</option>
        				    <option value="Thursday">'.i18n('Thursday').'</option>
        				    <option value="Friday">'.i18n('Friday').'</option>
        				    <option value="Saturday">'.i18n('Saturday').'</option>
        				    <option value="Sunday">'.i18n('Sunday').'</option>';
				$result.= '</select>';
			  $result.= '&nbsp;&nbsp;'.i18n('Like').'&nbsp;&nbsp;';
			  $result.= '<select dojoType="dijit.form.FilteringSelect" class="input" xlabelType="html" '
      			  		. 'style="width:150px;" name="calendarWorkFrom" id="calendarWorkFrom" '.autoOpenFilteringSelect().'>
          				    <option value="off" selected="selected">'.i18n('offDays').'</option>
          				    <option value="open">'.i18n('openDays').'</option>';
			  $result.= '</select>';
				//Fin Modif
  		}		
// MTY - GENERIC DAY OFF                
  	} else if ($item=="dayOfWeek" and !$print) {
            $result = $this->drawDayOffWeek();
        } else if ($item=="bankOffDays") {
            $result = $this->drawBankOffDays();
        } else if ($item=="buttonAddToCalendar__") {
            $result = $this->drawButtonAddToCalendar();
  	}
// MTY - GENERIC DAY OFF
  	return $result;
  }
  	
// MTY - GENERIC DAY OFF
    /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save($fromParameter=false) {
    $old = $this->getOld();
    if (!$fromParameter) {
        for ($i=0;$i<=6;$i++) {
            $dayOfWeek = "dayOfWeek".$i;
            $this->$dayOfWeek = (RequestHandler::getValue('dayOfWeek_'.$i)=='on'?1:0);
        }
    }
    $result = parent::save();
        
    if(strpos($result,"OK")===false){
  	return $result;
  }
// MTY - MULTI CALENDAR    
    $change = false;
    $dayOfWeekChanged = array();
    for ($i=0;$i<=6;$i++) {
        $dayOfWeek = "dayOfWeek".$i;
        if ($this->$dayOfWeek != $old->$dayOfWeek) {
            $dayOfWeekChanged[$i]=$this->$dayOfWeek;
            $change = true;
        }
    }
    if ($change) {
        $this->updateCookiesForCalendar();
        if (getSessionUser()->idCalendarDefinition == $this->id) {
            $this->updateCookiesForCalendar(true, $this->id);
// MTY - LEAVE SYSTEM
            if (isLeavesSystemActiv()) {
                $result = $this->updateLeaveLeaveEarnedPlannedWork($result, $dayOfWeekChanged);
            }
// MTY - LEAVE SYSTEM            
        }
    }
// MTY - MULTI CALENDAR    
  
    return $result;
  }
// MTY - GENERIC DAY OFF

  
  public function deleteControl() {
  	$result="";
  	if ($this->id==1)	{
  		$result .= "<br/>" . i18n("errorDeleteDefaultCalendar");
  	}
  	if (! $result) {
  		$result=parent::deleteControl();
  	}
  	return $result;
  }

// MTY - MULTI CALENDAR  
    /**
     * Update offDayList,workDayList (default calendar definition) and uOffDayList, uWorkDayList (user) cookies
     * @param Boolean onlyForUser = true if only change cookies uOffDayList, uWorkDayList (user cookies). 
     * 
     */
    public function updateCookiesForCalendar($onlyForUser=false,$idCalDef=null) {
        if ($this->id==1 and !$onlyForUser) {
            // The default Calendar
            $offDayList = Calendar::getOffDayList();
            if (!$offDayList) {$offDayList="#00000000#";}
            setcookie("offDayList", $offDayList,0,'/');
            $workDayList = Calendar::getWorkDayList();
            if (!$workDayList) {$workDayList="#00000000#";}
            setcookie("workDayList", $workDayList,0,'/');
}
        if($this->id == $idCalDef and $idCalDef!=null) {
            // The user calendar            
            $offDayList = Calendar::getOffDayList($this->id);
            if (!$offDayList) {$offDayList="#00000000#";}
            setcookie("uOffDayList", $offDayList,0,'/');
            $workDayList = Calendar::getWorkDayList($this->id);
            if (!$workDayList) {$workDayList="#00000000#";}
            setcookie("uWorkDayList", $workDayList,0,'/');                       
        }
    }
// MTY - MULTI CALENDAR  

// MTY - LEAVE SYSTEM
    private function updateLeaveLeaveEarnedPlannedWork($result,$dayOfWeekChanged) {
        // Employee with idCalendarDefinition of this
        $critArray = array("idle" => "0",
                           "idCalendarDefinition" => $this->id);
        $employee = new Employee();
        $employees = $employee->getSqlElementsFromCriteria($critArray);
        if ($employees) {
            $employeeIdIn = "idEmployee in (";
            foreach ($employees as $employee) {
                $employeeIdIn .= $employee->id . ",";
            }
            $employeeIdIn = substr($employeeIdIn, 0,-1).")";                
        } else {
            $employeeIdIn = "";
        }
        // update recorded leaves which have an Employee associated to this calendar :
        if ($employeeIdIn!="") {
            $lv = new Leave();
            $critWhere="idStatus=1 AND $employeeIdIn";
            $lvRq = $lv->getSqlElementsFromCriteria(null,false,$critWhere);
            if(!$lvRq) {
                return $result;
            }
            $offDayCalendar = Calendar::getOffDayList($this->id);
            foreach($lvRq as $leave){
                $dateChanged=array();
                $theDate = $leave->startDate;
                while($theDate <= $leave->endDate) {                    
                    $dateTime = new DateTime($theDate);
                    $dayOfWeek = $dateTime->format('N');
                    if ($dayOfWeek==7) {$dayOfWeek=0;}
                    
                    if (array_key_exists($dayOfWeek, $dayOfWeekChanged)) {
                        $dateChanged[$theDate]=$dayOfWeekChanged[$dayOfWeek];
                    }
                    
                    $dateTime->add(new DateInterval('P1D'));
                    $theDate = $dateTime->format("Y-m-d");
                } // while($tehDate <= $leave->startDate
                if (count($dateChanged)>0) {
                    // Update NbDays
                    $oldLeave = $leave->getOld();
                    $leave->calculateNbDays(true);
                    $deleteLeave = false;
                    if ($leave->nbDays==0) {
                        $deleteLeave=true;
                        $critPurge="id=$leave->id";
                        $resultL = $leave->purge($critPurge);
                    } else {
                        $resultL = $leave->simpleSave();
                    }
                    $lastStatus = getLastOperationStatus($resultL);
                    if($lastStatus!="OK" and $lastStatus!="NO_CHANGE"){
                        $result = htmlSetResultMessage(null, 
                                                      i18n("errorOnUpdatingLeaves"), 
                                                      false,
                                                      "", 
                                                      "Updating nbDays of Leaves when calendar changes",
                                                      $lastStatus);                    
                        return $result;
                    }
                    if ($lastStatus!="NO_CHANGE") {
                        // Update Leftquantity of corresponding Leave Earned
                        if ($deleteLeave) {
                            $resultL = $leave->updateLeftQOfLeaveEarned($leave->idLeaveType,$leave->idEmployee,$leave->nbDays,(float)$oldLeave->nbDays,false, false,true);                            
                        } else {
                            $resultL = $leave->updateLeftQOfLeaveEarned($leave->idLeaveType,$leave->idEmployee,$leave->nbDays,(float)$oldLeave->nbDays,false, true);
                        }    
                        $lastStatus = getLastOperationStatus($resultL);
                        if($lastStatus!="OK" and $lastStatus!="NO_CHANGE"){
                            $result = htmlSetResultMessage(null, 
                                                          i18n("errorOnUpdatingLeaves"), 
                                                          false,
                                                          "", 
                                                          "Updating Leave Earned when calendar changes",
                                                          $lastStatus);                    
                            return $result;
                        }
                    }
                    $plWorkInDate = "";
                    foreach($dateChanged as $date => $offDay) {
                        if ($offDay==1) {
                            if ($plWorkInDate=="") { $plWorkInDate = "workDate IN ("; }
                            $plWorkInDate .= "'$date',";
                        }
                    }
                    $plWork = new PlannedWork();
                    $getIdPrjIdActIdAss = $leave->getIdProjectIdActivityIdAssignmentOfThis();

                    if ($plWorkInDate!="") { $plWorkInDate = substr($plWorkInDate, 0,-1).")"; }
                    if ($plWorkInDate!="") {
                        // Delete planned Work of corresponding date, idEmployee (idResource)
                        $critPurge  = $plWorkInDate;
                        $critPurge .= " AND idResource=$leave->idEmployee";
                        $critPurge .= " AND idProject=".$getIdPrjIdActIdAss["idProject"];
                        $critPurge .= " AND refType='Activity' AND refId=".$getIdPrjIdActIdAss["idActivity"];
                        $critPurge .= " AND idAssignment = ".$getIdPrjIdActIdAss["idAssignment"];
                        $critPurge .= " AND idLeave=$leave->id";
                        $resultL = $plWork->purge($critPurge);
                        $lastStatus = getLastOperationStatus($resultL);
                        if($lastStatus!="OK" and $lastStatus!="NO_CHANGE"){
                            $result = htmlSetResultMessage(null, 
                                                          i18n("errorOnUpdatingLeaves"), 
                                                          false,
                                                          "", 
                                                          "Purge Planned Work when calendar changes",
                                                          $lastStatus);                    
                            return $result;
                        }

                    }
                    foreach($dateChanged as $date => $offDay) {
                        if ($offDay==0) {
                            $theDay=substr($date,0,4).substr($date,5,2).substr($date,8,2);
                            if (strpos($offDayCalendar,$theDay)!==false) {continue;} 
                            // Create,Update related planned work
                            if($date==$leave->startDate and $leave->startAMPM=="PM"){
                                $plWork->work=0.5;
                            }else if($date==$leave->endDate and $leave->endAMPM=="AM"){
                                $plWork->work=0.5;
                            }else{
                                $plWork->work=1.0;
                            }
                            $plWork->idResource=$leave->idEmployee;
                            $plWork->idProject=$getIdPrjIdActIdAss["idProject"];
                            $plWork->refType="Activity";
                            $plWork->refId=$getIdPrjIdActIdAss["idActivity"];
                            $plWork->idAssignment=$getIdPrjIdActIdAss["idAssignment"];
                            $plWork->setDates($date);
                            $plWork->idLeave = $leave->id;                        
                            $critPlWork = array('idResource'=>$plWork->idResource);
                            $critPlWork['idProject']= $plWork->idProject;
                            $critPlWork['idAssignment']= $plWork->idAssignment;
                            $critPlWork['refType']= $plWork->refType;
                            $critPlWork['refId']= $plWork->refId;
                            $critPlWork['idLeave']= $leave->id;
                            $critPlWork['workDate']= $plWork->workDate;                    
                            $existingPlWork = $plWork->getSqlElementsFromCriteria($critPlWork);
                            if ($existingPlWork) {
                                $plWork->id = $existingPlWork[0]->id;
                                unset($plWork->idLeave);
                            }
                            $resultL = $plWork->simpleSave();
                            $lastStatus = getLastOperationStatus($resultL);
                            if($lastStatus!="OK" and $lastStatus!="NO_CHANGE"){
                                $result = htmlSetResultMessage(null, 
                                                              i18n("errorOnUpdatingLeaves"), 
                                                              false,
                                                              "", 
                                                              "Create or Update Planned Work when calendar changes",
                                                              $lastStatus);                    
                                return $result;
                            }                    
                        }
                    }
                }
            }
        }
        
        return $result;
    }
// MTY - LEAVE SYSTEM
    
}?>