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
class NotificationDefinition extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
    public $id;    // redefine $id to specify its visible place 
    public $name;
    public $idNotifiable;
    public $idMenu;
    public $idNotificationType;
    public $idle;
  public $_sec_notificationTitle;  
    public $title;
    public $_spe_addDynamicFieldInTitle;
    public $_spe_listItemsTitle;
    public $_spe_listFieldsTitle;
    public $_spe_buttonAddInTitle;
  public $_sec_notificationContent;  
    public $content;
    public $_spe_addDynamicFieldInContent;
    public $_spe_listItemsContent;
    public $_spe_listFieldsContent;
    public $_spe_buttonAddInContent;
  public $_sec_notificationRule;  
    public $notificationRule;
    public $_spe_addDynamicFieldInRule;
    public $_spe_listItemsRule;
    public $_spe_listFieldsRule;
    public $_spe_buttonAddInRule;
    public $_spe_addOperatorOrFunctionInRule;
    public $_spe_listOperatorsAndFunctionsRule;
    public $_spe_buttonAddOperatorOrFunctionInRule;
  public $_sec_target;  
    public $targetDateNotifiableField;
    public $_spe_targetDateNotifiableField;
    public $notificationGenerateBefore=0;
    public $_lib_daysBefore;
    public $notificationGenerateBeforeInMin=0;
    public $_lib_minutesBefore;    
    public $_spe_repeatNotification;
    public $_tab_3_4_DNF=array('frequency','month','day','everyYear','everyMonth','everyWeek','everyDay');
        public $everyYear=0;
        public $fixedMonth=null;
        public $fixedMonthDay=null;    
        public $everyMonth=0;
        public $_void_3;
        public $fixedDay=null;
        public $everyWeek=0;
        public $_void_11;
        public $_void_12;
        public $everyDay=0;
        public $_void_1;
        public $_void_2;
    public $notificationNbRepeatsBefore=0;
  public $_sec_receivers;  
    public $notificationReceivers;
    public $_spe_addNotificationReceiver;
    public $_spe_listItemsReceiver;
    public $_spe_listFieldsReceiver;
    public $_spe_buttonAddInReceiver;
    public $sendEmail;
  public $_sec_helpAllowedWords;
    public $_spe_allowedWords;
  public $_sec_helpAllowedReceivers;
    public $_spe_allowedReceivers;

  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
    <th field="name" width="30%" >${name}</th>
    <th field="nameNotifiable" formatter="translateFormatter" width="30%" >${idNotifiable}</th>
    <th field="colorNameNotificationType" width="10%" formatter="colorTranslateNameFormatter">${type}</th>
    <th field="sendEmail" width="4%" formatter="booleanFormatter" >${sendEmail}</th>
    <th field="idle" width="4%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array(
                                            "id"                                            => "hidden",
                                            "name"                                          => "required",
                                            "idNotifiable"                                  => "required",
                                            "idMenu"                                        => "hidden",
                                            "idNotificationType"                            => "required",
                                            "title"                                         => "required, noList",
                                            "content"                                       => "required, noList",
                                            "_spe_allowedWords"                             => "hidden,readonly, noPrint, noList",
                                            "_spe_allowedReceivers"                         => "hidden,readonly, noPrint, noList",
                                            "_tab_3_4_DNF"                                  => "forceHeader",
                                            "notificationRule"                              => "noList",
                                            "notificationReceivers"                         => "required, noList",
                                            "targetDateNotifiableField"                     => "required, hidden",                                            
                                            "_spe_targetDateNotifiableField"                => "required",                                            
                                            "idle"                                          => "nobr",
                                            "_sec_helpAllowedWords"                         => "hidden,noPrint",
                                            "_sec_helpAllowedReceivers"                     => "hidden,noPrint",
                                            "notificationGenerateBefore"                    => "nobr",
                                            "_lib_daysBefore"                               => "nobr",
                                            "notificationGenerateBeforeInMin"               => "nobr",
                                        );  
  
  private static $_colCaptionTransposition = array(
      "idNotificationType"=>"type"
  );
  
  private static $_databaseColumnName = array('fixedMonthDay'=>'fixedDay');
  
// TOOLTIP - TABARY
  private static $_fieldsTooltip = array(
                                            "title"                                       => "tooltipNotificationTitleAndContent",
                                            "content"                                     => "tooltipNotificationTitleAndContent",
                                            "notificationRule"                            => "tooltipNotificationRule",
                                            "idMenu"                                      => "tooltipNotificationMenu",
                                            "idNotifiable"                                => "tooltipNotifiable",
                                            "notificationReceivers"                       => "tooltipNotificationReceivers",
                                            "_spe_targetDateNotifiableField"              => "tooltipNotificationTargetDateField",
                                            "everyDay"                                    => "tooltipNotificationEveryDay",
                                            "everyWeek"                                   => "tooltipNotificationEveryWeek",
                                            "everyMonth"                                  => "tooltipNotificationEveryMonth",
                                            "everyYear"                                   => "tooltipNotificationEveryYear",
                                            "fixedMonth"                                  => "tooltipNotificationFixedMonth",
                                            "fixedDay"                                    => "tooltipNotificationFixedDay",
                                            "fixedMonthDay"                               => "tooltipNotificationFixedDay",
                                            "notificationGenerateBefore"                  => "tooltipNotificationGenerateBefore",
                                            "notificationGenerateBeforeInMin"             => "tooltipNotificationGenerateBeforeInMin",
                                            "notificationNbRepeatsBefore"                 => "tooltipNotificationNbRepeatsBefore"
                                        );
// TOOLTIP - TABARY

// For each field that you want to draw as spinner
  private static $_spinnersAttributes = array(
      'fixedMonth'=>'min:1,max:12,step:1,showLabelInTab',
      'fixedDay'=>'min:1,max:31,step:1,showLabelInTab',
      'fixedMonthDay'=>'min:1,max:31,step:1,showLabelInTab',
      'notificationNbRepeatsBefore'=>'min:-1,max:99,step:1,showLabelInTab',
      'notificationGenerateBefore'=>'min:0,max:1000,step:1,showLabelInTab',
      'notificationGenerateBeforeInMin'=>'min:0,max:720,step:5,showLabelInTab'
      );  
  
//    private static $_databaseTableName = '';
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
        parent::__construct($id,$withoutDependentObjects);
        if (! isNotificationSystemActiv()) return;
        
        // Min of spinner $notificationGenerateBeforeInMin = param cronCheckNotifications
        $cronCheckNotifications = Parameter::getGlobalParameter ( 'cronCheckNotifications' ) / 60 ;
        self::$_spinnersAttributes['notificationGenerateBeforeInMin'] = 
                    str_replace("min:0", "min:$cronCheckNotifications", self::$_spinnersAttributes["notificationGenerateBeforeInMin"]);        

        if ($id == NULL) {
            $this->setFieldAttributeHidden('idle', true);
            $this->notificationGenerateBefore="";
            $this->notificationGenerateBeforeInMin="";
            $this->notificationNbRepeatsBefore="";
        } else {
            if ($this->notificationNbRepeatsBefore==0) { $this->notificationNbRepeatsBefore = "";}
            if ($this->notificationGenerateBefore==0) { $this->notificationGenerateBefore = "";}
            if ($this->notificationGenerateBeforeInMin==0) { $this->notificationGenerateBeforeInMin = "";}
        }
        
        $this->setHiddenFixedDayFixedMonthAttributes();

        if ($this->notificationGenerateBeforeInMin<$cronCheckNotifications and $this->notificationGenerateBeforeInMin>0) {
            $this->notificationGenerateBeforeInMin = $cronCheckNotifications;
        }
                
        if ($this->fixedDay==0) { $this->fixedDay="";}
        if ($this->fixedMonth==0) { $this->fixedMonth="";}
        if ($this->fixedMonthDay==0) { $this->fixedMonthDay="";}
                
        if (!$this->everyDay and !$this->everyWeek and !$this->everyMonth and !$this->everyYear) {
            $this->notificationNbRepeatsBefore = "";
            $this->setFieldAttributeReadonly('notificationNbRepeatsBefore', true);
        } else {
            $this->setFieldAttributeReadonly('notificationNbRepeatsBefore', false);
            $this->setFieldAttributeReadonly('notificationGenerateBeforeInMin', true);
            $this->notificationGenerateBeforeInMin="";
        }
        
        if ($this->everyYear) {
            //$this->fixedMonthDay = ($this->fixedDay==0?"":$this->fixedDay);
            $this->fixedDay="";
            if ($this->fixedMonthDay>0) {
                $this->setFieldAttributeReadonly('notificationGenerateBefore', true);
                $this->notificationGenerateBefore="";
            } else {
                $this->setFieldAttributeReadonly('notificationGenerateBefore', false);
            }
        } else if($this->everyMonth) {
            $this->fixedMonth="";
            $this->fixedMonthDay="";
            if ($this->fixedDay>0) {
                $this->setFieldAttributeReadonly('notificationGenerateBefore', true);
                $this->notificationGenerateBefore="";
            } else {
                $this->setFieldAttributeReadonly('notificationGenerateBefore', false);
        }
        } elseif($this->everyWeek) {
            $this->fixedDay="";            
            $this->fixedMonth="";
            $this->setFieldAttributeReadonly('notificationGenerateBefore', false);                        
        } elseif($this->everyDay) {
            $this->fixedDay="";            
            $this->fixedMonth="";
            $this->notificationGenerateBefore = "";
            $this->setFieldAttributeReadonly('notificationGenerateBefore', true);                        
        } else {
            $this->fixedMonth="";
            $this->setFieldAttributeReadonly('notificationGenerateBefore', false);                        
        }

        if ($this->notificationNbRepeatsBefore<0) {
            $this->setFieldAttributeReadonly('notificationGenerateBefore', true);            
            $this->notificationGenerateBefore= "";            
        }

        if ($this->notificationGenerateBefore>0 or 
            $this->notificationNbRepeatsBefore>0 or 
            substr($this->targetDateNotifiableField,-8)!=='DateTime') {
                $this->setFieldAttributeReadonly('notificationGenerateBeforeInMin', true);        
                $this->notificationGenerateBeforeInMin="";
        }
        
        if ($this->notificationGenerateBeforeInMin>0) {
            $this->setFieldAttributeReadonly('notificationGenerateBefore', true);            
            $this->notificationGenerateBefore= "";                        
        }
        
        $this->setTargetDateNotifiableField();
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
   * Return the generic spinnerAttributes
   * @return array[name,value] : the generic $_spinnerAttributes
   */
  protected function getStaticSpinnersAttributes() {
      if(!isset(self::$_spinnersAttributes)) {return array();}
      return self::$_spinnersAttributes;
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
    
  /** ========================================================================
   * Return the specific fieldsTooltip
   * @return array the fieldsTooltip
   */
  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  }
  
// ============================================================================**********
// OVERRIDED SQLELEMENT FUNCTIONS
// ============================================================================**********
  
 /**
   * =========================================================================
   * control data
   * 
   * @param void
   * @return "OK" if controls are good or an error message
   *         must be redefined in the inherited class
   */
  public function control() {
    $result = "";
    $spe_targetDateField = RequestHandler::getValue('_spe_targetDateNotifiableField');

    // The ObjectClass's fields
    $notificationItem = new Notifiable($this->idNotifiable);
    $className = $notificationItem->notifiableItem;
    
    // If title do reference to object's field ${xxx}
    if (strpos($this->title,'${')>=0) {
        // Verify if object's fields correct
        $result = $this->verifyObjectFields('title',$className);
    }
    
    // If content do reference to object's field ${xxx}
    if (strpos($this->content,'${')) {
        $result .= $this->verifyObjectFields('content',$className);
    }
    
    // If notificationRule do reference to object's field ${xxx}
    if (strpos($this->notificationRule,'${')) {
        $result .= $this->verifyObjectFields('notificationRule', $className);
    }
    
    // If notificationReceivers is'nt empty
    if (trim($this->notificationReceivers)!=="") {
        $result .= $this->verifyNotificationReceivers($className);
    }
        
    if ($result!="") {
        $result = i18n('Notifiable'). ' : '. i18n($className) . $result;
    }
    
    $defaultControl = parent::control ();
    if ($defaultControl != 'OK') {
      $result .= $defaultControl;
    }
    if ($result == "") {
      $result = 'OK';
    }
    
    return $result;
  }

  
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
      
    $spe_targetDateField = RequestHandler::getValue('_spe_targetDateNotifiableField');
    $this->targetDateNotifiableField = $spe_targetDateField;
    
    if (is_null($this->notificationNbRepeatsBefore) or $this->notificationNbRepeatsBefore=="") {
        $this->notificationNbRepeatsBefore= 0;
    }
    
    if (is_null($this->notificationGenerateBefore) or $this->notificationGenerateBefore=="") {
        $this->notificationGenerateBefore= 0;
    }

    if ($this->everyDay or $this->everyWeek) {
        $this->fixedDay=0;
        $this->fixedMonthDay=0;
        $this->fixedMonth=0;
    } else if ($this->everyMonth) {
      $this->fixedMonth=0;
      $this->fixedMonthDay=$this->fixedDay;
    } else if ($this->everyYear) {
        $this->fixedDay=$this->fixedMonthDay;
    }
    
    
    $result = parent::save();
    // If notification definition has changed
    if (strpos($result,'value="OK"')!==false) {
        // Delete the notifications issued of this notification definition
        // with notificationDate > currentDate
        $theCurrentDate = new DateTime();
        $theCurrentDateFmt=$theCurrentDate->format('Y-m-d');
        //$query  = "DELETE FROM notification ";
        //$query .= "WHERE idNotificationDefinition=".$this->id;
        //$query .= " AND (notificationDate>'".$theCurrentDate->format('Y-m-d')."'";
        //$query .= "      OR (IF(ISNULL(notificationTime) OR notificationDate<DATE(NOW()),(1<>1),notificationTime>TIME(NOW()))))";
        $clause = "  idNotificationDefinition=".$this->id;
        $clause .= " AND (      notificationDate>'$theCurrentDateFmt'";
        $clause .= "       OR ( notificationDate='$theCurrentDateFmt' AND notificationTime IS NULL )";
        $timeNow=(Sql::isPgsql())?'CURRENT_TIME':'TIME(NOW())';
        $clause .= "       OR ( notificationDate='$theCurrentDateFmt' AND notificationTime IS NOT NULL AND notificationTime>$timeNow )";
        $clause .= "     )";
        $notif = new Notification();
        $notif->purge($clause);
        
        //SqlDirectElement::execute($query);
        
        if ($this->idle===0) {
            // Generate Notifications with modified notification definition
            $resGenerate = $this->generateNotifications();
            if ($resGenerate != "OK") {
                $returnValue = i18n ( 'messageWrongRule' ) . ' ' . i18n ( get_class ( $this ) ) . ' #' . $this->id;
                $returnStatus = "INVALID";
                $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
                $returnValue .= '<input type="hidden" id="lastOperation" value="save" />';
                $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
                return $returnValue;
            }
        }
    }
    
    return $result;
    
  }

// =============================================================================================================
// DRAWING FUNCTION
// =============================================================================================================

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
    global $largeWidth, $print, $outMode;
    
    if ($item==='targetDateNotifiableField') {
        return $this->drawTargetDateNotifiableField($item, $readOnly, $refresh);
    } elseif ($item==='allowedWords') {
        return $this->drawAllowedWords($item, $readOnly, $refresh);
    } elseif ($item==='allowedReceivers') {
        return $this->drawAllowedReceivers($item, $readOnly, $refresh);
    } elseif ($item==='listItemsTitle' or 
              $item==='listItemsContent' or
              $item==='listItemsRule' or
              $item==='listItemsReceiver') {
        return $this->drawListItems($item, $readOnly, $refresh);        
    } elseif ($item==='listFieldsTitle' or 
              $item=='listFieldsContent' or
              $item=='listFieldsRule' or 
              $item=='listFieldsReceiver') {
        return $this->drawListFields($item, $readOnly, $refresh);        
    } elseif ($item==='addDynamicFieldInTitle' or 
              $item==='addDynamicFieldInContent' or
              $item==='addDynamicFieldInRule' or
              $item==='addNotificationReceiver' or 
              $item ==='addOperatorOrFunctionInRule') {
        if ($print or $outMode=='pdf' or $readOnly) {return "";}
        return '<label class="label" style="width:100%; text-align:left;font-weight:bold;">' . i18n('col' . ucfirst($item)) . '&nbsp;:&nbsp;</label>';
    } elseif ($item==='buttonAddInTitle' or 
              $item==='buttonAddInContent' or
              $item==='buttonAddInRule' or
              $item==='buttonAddInReceiver') {
        if ($print or $outMode=='pdf' or $readOnly) {return "";}
        return $this->drawButtonAddField($item);
    } elseif ($item ==='listOperatorsAndFunctionsRule') {
        if ($print or $outMode=='pdf' or $readOnly) {return "";}
        return $this->drawListOperatorsAndFunctions($item);
    } elseif ($item === 'buttonAddOperatorOrFunctionInRule') {
        if ($print or $outMode=='pdf' or $readOnly) {return "";}
        return $this->drawButtonAddOperatorOrFunction($item);
    } elseif ($item === 'repeatNotification') {
        return '<label class="label" style="width:100%; text-align:left;font-weight:bold;">' . i18n('colRepeatNotification') . '</label>';
    }
    
    return "";
    
  }

  /** =========================================================================
   * Draw the _spe_allowedReceivers field.
   * @param $item the item
   * @return an html string able to display a specific item
   */
  public function drawAllowedReceivers($item,$readOnly=false,$refresh=false) {
    global $largeWidth, $colWidth, $print, $outMode;
    
    if ($print || $outMode=="pdf" || $readOnly) {return "";}
    
    $notifiableItem=$this->getNotifiableItem();
    
    $fieldsList = getObjectClassAndForeignClassFieldsList($notifiableItem->notifiableItem,true);
    
    $fieldsListString = implode(' - ', $fieldsList);
    $fullItem = "_spe_$item";
    $style=$this->getDisplayStyling($item);
    $fieldStyle=$style["field"];
    $extName="";
    $name=' id="' . $fullItem . '" name="' . $fullItem . $extName . '" ';
    
    $result  = '<div style="text-align:left;font-weight:normal" class="tabLabel">'.htmlEncode($this->getColCaption($item),'stipAllTags').'&nbsp;:&nbsp;</div>';
//    $result .=  '<div '. $name .' style="border:1px dotted #AAAAAA;width:' . $colWidth . 'px;padding:5px;'.$fieldStyle.'">';
    $result .=  '<div '. $name .' style="border:1px dotted #AAAAAA;width:100%;padding:5px;'.$fieldStyle.'">';
    $result .=  $fieldsListString.'&nbsp;';
    $result .=  '</div>';
    
    return $result;
  }

  /** =========================================================================
   * Draw the _spe_allowedWords field.
   * @param $item the item
   * @return an html string able to display a specific item
   */
  public function drawAllowedWords($item,$readOnly=false,$refresh=false) {
    global $largeWidth, $colWidth, $print, $outMode;
    
    if ($print || $outMode=="pdf" || $readOnly) {return "";}
    
    $notifiableItem=$this->getNotifiableItem();
    
    $fieldsList = getObjectClassAndForeignClassFieldsList($notifiableItem->notifiableItem);
    $fieldsListString = implode(' - ', $fieldsList);
    $fullItem = "_spe_$item";
    $style=$this->getDisplayStyling($item);
    $fieldStyle=$style["field"];
    $extName="";
    $name=' id="' . $fullItem . '" name="' . $fullItem . $extName . '" ';
    
    $result  = '<div style="text-align:left;font-weight:normal" class="tabLabel">'.htmlEncode($this->getColCaption($item),'stipAllTags').'&nbsp;:&nbsp;</div>';
//    $result .=  '<div '. $name .' style="border:1px dotted #AAAAAA;width:' . $colWidth . 'px;padding:5px;'.$fieldStyle.'">';
    $result .=  '<div '. $name .' style="border:1px dotted #AAAAAA;width:100%;padding:5px;'.$fieldStyle.'">';
    $result .=  $fieldsListString.'&nbsp;';
    $result .=  '</div>';
    
    return $result;
  }

/** =========================================================================
   * Draw the _spe_listItemsTitle and _spe_listItemsContent fields.
   * @param $item the item
   * @return an html string able to display a specific item
   */
  public function drawListItems($item,$readOnly=false,$refresh=false) {
    global $largeWidth, $print, $toolTip, $outMode;
    if ($print or $outMode=="pdf" or $readOnly) {
        return("");
    }
    
    $itemLab = "listItemsTitle";
    
    $itemEnd = str_replace("listItems","", $item);
      
    $notifiableItem=$this->getNotifiableItem();
    
    $arrayClasses=getTranslatedClassAndFKeyClasses($notifiableItem->notifiableItem, ($itemEnd=='Receiver'?true:false));
        
    $fieldAttributes=$this->getFieldAttributes($item);
    if(strpos($fieldAttributes,'required')!==false) {
        $isRequired = true;
    } else {
        $isRequired = false;
    }

    $notReadonlyClass=($readOnly?"":" generalColClassNotReadonly ");
    $notRequiredClass=($isRequired?"":" generalColClassNotRequired ");
    $style=$this->getDisplayStyling($item);
    $labelStyle=$style["caption"];
    $fieldStyle=$style["field"];
    $fieldWidth=$largeWidth-10;
    $extName="";
    $fullItem = "_spe_$item";
    $name=' id="' . $fullItem . '" name="' . $fullItem . $extName . '" ';
    $attributes =' required="true" missingMessage="' . i18n('messageMandatory', array($this->getColCaption($itemLab))) . '" invalidMessage="' . i18n('messageMandatory', array($this->getColCaption($item))) . '"';
    $valStore='';
    
    $colScript  = '<script type="dojo/connect" event="onChange" >';
    $colScript .= '  refreshListFieldsInNotificationDefinition(this.value,"'.$itemEnd.'");';
    $colScript .= '</script>';
    
    $result  = '<tr class="detail generalRowClass">';
    $result .= '<td class="label" style="text-align:right;font-weight:normal;"><label>' . i18n("col".ucfirst($itemLab)).'';
    $result .= Tool::getDoublePoint().'</lable></td>';
    $result .= '<td>';
    $result .= '<select dojoType="dijit.form.Select" class="input '.(($isRequired)?'required':'').' generalColClass '.$notReadonlyClass.$notRequiredClass.$item.'Class" ';
    $result .= '  style="width: ' . ($fieldWidth) . 'px;' . $fieldStyle . '"';
    $result .= $name;
    $result .=$attributes;
    $result .=$valStore;
    $result .=">";
    foreach ($arrayClasses as $key => $value) {
        $result .= '<option value="' . $key . '"';
        if($notifiableItem->name === $key) {
            $result .= ' selected="selected" ';
        }
        $result .= '>'. htmlEncode(ucfirst($value)) . '</option>';
    }

    $result .=$colScript;
    $result .="</select></td>";
    $result .= '</tr>';
    return $result;
  }

/** =========================================================================
   * Draw the _spe_listFieldsTitle and _spe_listFieldsContent fields.
   * @param $item the item
   * @return an html string able to display a specific item
   */
  public function drawListFields($item,$readOnly=false,$refresh=false) {
    global $largeWidth, $print, $toolTip, $outMode;

    if ($print or $outMode=="pdf" or $readOnly) {
        return("");
    }
    
    $itemLab = "listFieldsTitle";
    $itemEnd = str_replace("listFields","", $item);
        
    $notifiableItem=$this->getNotifiableItem();

    $arrayFields = getObjectClassTranslatedFieldsList($notifiableItem->notifiableItem,
                                                      ($itemEnd==='Receiver'?true:false),
                                                      ($itemEnd==='Receiver'?false:true)
                                                     );
    
    $fieldAttributes=$this->getFieldAttributes($item);
    if(strpos($fieldAttributes,'required')!==false) {
        $isRequired = true;
    } else {
        $isRequired = false;
    }

    $notReadonlyClass=($readOnly?"":" generalColClassNotReadonly ");
    $notRequiredClass=($isRequired?"":" generalColClassNotRequired ");
    $style=$this->getDisplayStyling($item);
    $labelStyle=$style["caption"];
    $fieldStyle=$style["field"];
    $fieldWidth=$largeWidth;
    $extName="";
    $fullItem = "_spe_$item";
    $name=' id="' . $fullItem . '" name="' . $fullItem . $extName . '" ';
    $attributes =' required="true" missingMessage="' . i18n('messageMandatory', array($this->getColCaption($itemLab))) . '" invalidMessage="' . i18n('messageMandatory', array($this->getColCaption($item))) . '"';
    $valStore='';
    
    $colScript  = '';
    
    $result  = '<tr class="detail generalRowClass">';
    $result .= '<td class="label" style="font-weight:normal;"><label>' . i18n("col".ucfirst($itemLab)).'';
    $result .= Tool::getDoublePoint().'</lable></td>';
    $result .= '<td>';
    $result .= '<select dojoType="dijit.form.Select" class="input '.(($isRequired)?'required':'').' generalColClass '.$notReadonlyClass.$notRequiredClass.$item.'Class"  ';
    $result .= '  style="width: ' . ($fieldWidth-100) . 'px;' . $fieldStyle . '; "';
    $result .= $name;
    $result .=$attributes;
    $result .=$valStore;
    $result .=">";

    $first=true;
    foreach ($arrayFields as $key => $value) {
        $result .= '<option value="' . $key . '"';
        if($first) {
            $result .= ' selected="selected" ';
            $first=false;
        }
        $result .= '><span >'. htmlEncode(ucfirst($value)) . '</span></option>';
    }

    $result .=$colScript;
    $result .="</select></td>";
    $result .= '</tr>';
    return $result;
  }

/** =========================================================================
   * Draw the _spe_listFieldsTitle and _spe_listFieldsContent fields.
   * @param $item the item
   * @return an html string able to display a specific item
   */
  public function drawListOperatorsAndFunctions($item) {
    global $largeWidth, $print, $toolTip, $outMode, $readOnly;
    
    $itemLab = "listOperatorsAndFunctions";

    $arrayFields = [
                    "OR"            => (i18n('OR')),
                    "AND"           => (i18n('AND')),
                    "="             => (i18n('equal')),
                    "<>"            => (i18n('different')),
                    ">="            => (i18n('greaterOrEqual')),
                    ">"             => (i18n('greaterThan')),
                    "<="            => (i18n('lessOrEqual')),
                    "<"             => (i18n('lessThan')),
                    "now()"         => (i18n('nowDate')),
                    "year(date)"        => (i18n('yearOf')),
                    "month(date)"       => (i18n('monthOf')),
                    "day(date)"         => (i18n('dayOf')),
                    "isnull(field)"      => (i18n('isNull')),
                    "substr(field,start,length)" => (i18n('subString'))
                   ];    
    $fieldAttributes=$this->getFieldAttributes($item);
    if(strpos($fieldAttributes,'required')!==false) {
        $isRequired = true;
    } else {
        $isRequired = false;
    }

    $notReadonlyClass=($readOnly?"":" generalColClassNotReadonly ");
    $notRequiredClass=($isRequired?"":" generalColClassNotRequired ");
    $style=$this->getDisplayStyling($item);
    $labelStyle=$style["caption"];
    $fieldStyle=$style["field"];
    $fieldWidth=$largeWidth;
    $extName="";
    $fullItem = "_spe_$item";
    $name=' id="' . $fullItem . '" name="' . $fullItem . $extName . '" ';
    $attributes =' required="true" missingMessage="' . i18n('messageMandatory', array($this->getColCaption($itemLab))) . '" invalidMessage="' . i18n('messageMandatory', array($this->getColCaption($item))) . '"';
    $valStore='';
    
    $colScript  = '';
    
    $result  = '<tr class="detail generalRowClass">';
    $result .= '<td class="label" style="text-align:right;font-weight:normal;"><label>' . i18n("col".ucfirst($itemLab));
    $result .= Tool::getDoublePoint().'</label></td>';
    $result .= '<td>';
    $result .= '<select dojoType="dijit.form.Select" class="input '.(($isRequired)?'required':'').' generalColClass '.$notReadonlyClass.$notRequiredClass.$item.'Class"  ';
    $result .= '  style="width: ' . ($fieldWidth-100) . 'px;' . $fieldStyle . '; "';
    $result .= $name;
    $result .=$attributes;
    $result .=$valStore;
    $result .=">";

    $first=true;
    foreach ($arrayFields as $key => $value) {
        $result .= '<option value="' . $key . '"';
        if($first) {
            $result .= ' selected="selected" ';
            $first=false;
        }
        $result .= '><span >'. $value . '</span></option>';
    }

    $result .=$colScript;
    $result .="</select></td>";
    $result .= '</tr>';
    return $result;
  }
  
  public function drawButtonAddField($item) {
    global $largeWidth, $toolTip, $print, $outMode;

    $itemLab = "operationInsert";
    $fullItem= '_spe_'.$item;
    $itemEnd = str_replace("buttonAddIn","", $item);
    if ($itemEnd==='Receiver') {
        $textBox = 'notificationReceivers';
    } elseif ($itemEnd==='Rule') {
        $textBox = 'notificationRule';
    } else {
        $textBox = strtolower($itemEnd);                
    }
    
    $toolTipPosition = "'below'";
    $toolTipConnected = "'".$fullItem."'";
    $editor = getEditorType();

    $result  = '<div style="position:relative;width:'.($largeWidth+145).'px;">';
    $result .= '<button class="roundedVisibleButton" id="'.$fullItem.'" dojoType="dijit.form.Button" showlabel="true" style="position:absolute;'.((isNewGui())?'top:-34px;right:-47px;width:85px;':'top-24px;height:17px;right:0px;width:90px;').'">';
    $result .= i18n($itemLab);
    $result .= '<script type="dojo/connect" event="onClick" args="evt">';
    $result .= '  addFieldInTextBoxForNotificationItem("'.$itemEnd.'","'.$textBox.'","'.$editor.'");';
    $result .= '  formChanged();';
    $result .= '</script>';
    $result .= '</button>';
    $result .= '</div>';
    $result .= '<div data-dojo-type="dijit/Tooltip" data-dojo-props="connectId:'.$toolTipConnected.',position:['.$toolTipPosition.']">';
    if ($itemEnd!=='Receiver') {
        $result .= i18n('tooltipAddInTextBoxNotificationDefinition');
    }
    $result .= '</div>';
    
    return $result;
  }

  public function drawButtonAddOperatorOrFunction($item) {
    global $largeWidth, $toolTip, $print, $outMode;

    $itemLab = "operationInsert";
    $fullItem= '_spe_'.$item;
    $textBox = 'notificationRule';
    $itemEnd = "Rule";
    
    $toolTipPosition = "'below'";
    $toolTipConnected = "'".$fullItem."'";

    $result  = '<div style="position:relative;width:'.($largeWidth+145).'px;">';
    $result .= '<button class="roundedVisibleButton" id="'.$fullItem.'" dojoType="dijit.form.Button" showlabel="true" style="position:absolute;'.((isNewGui())?'top:-34px;right:-47px;width:85px;':'top-24px;height:17px;right:0px;width:90px;').'">';
    $result .= i18n($itemLab);
    $result .= '<script type="dojo/connect" event="onClick" args="evt">';
    $result .= '  addOperatorOrFunctionInTextBoxForNotificationItem("'.$textBox.'");';
    $result .= '  formChanged();';
    $result .= '</script>';
    $result .= '</button>';
    $result .= '</div>';
    $result .= '<div data-dojo-type="dijit/Tooltip" data-dojo-props="connectId:'.$toolTipConnected.',position:['.$toolTipPosition.']">';
    if ($itemEnd!=='Receiver') {
        $result .= i18n('tooltipAddInTextBoxNotificationDefinition');
    }
    $result .= '</div>';
    
    return $result;
  }
  
  /** =========================================================================
   * Draw the _spe_targetDateNotifiableField field.
   * @param $item the item
   * @return an html string able to display a specific item
   */
  public function drawTargetDateNotifiableField($item,$readOnly=false,$refresh=false) {
    global $largeWidth, $print, $toolTip, $outMode;
      
    $notifiableItem=$this->getNotifiableItem();

//    $arrayFieldsWithDateType=getObjectClassFieldsListWithDateType($notifiableItem->notifiableItem);
//    $arrayTargetDate[-1] = "without";
//    $arrayTargetDate = array_merge_preserve_keys($arrayTargetDate,$arrayFieldsWithDateType);
    $arrayTargetDate=getObjectClassFieldsListWithDateType($notifiableItem->notifiableItem);
        
    $fieldAttributes=$this->getFieldAttributes($item);
    if(strpos($fieldAttributes,'required')!==false) {
        $isRequired = true;
    } else {
        $isRequired = false;
    }

    $notReadonlyClass=($readOnly?"":" generalColClassNotReadonly ");
    $notRequiredClass=($isRequired?"":" generalColClassNotRequired ");
    $style=$this->getDisplayStyling($item);
    $labelStyle=$style["caption"];
    $fieldStyle=$style["field"];
    $fieldWidth=$largeWidth-10;
    $extName="";
    $fullItem = "_spe_$item";
    $name=' id="' . $fullItem . '" name="' . $fullItem . $extName . '" ';
    $attributes =' required="true" missingMessage="' . i18n('messageMandatory', array($this->getColCaption($item))) . '" invalidMessage="' . i18n('messageMandatory', array($this->getColCaption($item))) . '"';
    $valStore='';
    $colScript=$this->getValidationScript($fullItem);
    
    $result  = '<tr class="detail generalRowClass">';
    $result .= '<td class="label" style="text-align:right;font-weight:normal"><label>' . i18n("col".ucfirst($item));
    $result .= Tool::getDoublePoint().'</label></td>';    
    if (!$print) {
        $result .= '<td>';
        $result .= htmlDisplayTooltip($toolTip,$fullItem,$print,$outMode);
        $result .= '<select dojoType="dijit.form.Select" class="input '.(($isRequired)?'required':'').' generalColClass '.$notReadonlyClass.$notRequiredClass.$item.'Class" xlabelType="html" ';
        $result .= '  style="width: ' . ($fieldWidth) . 'px;' . $fieldStyle . '"';
        $result .= $name;
        $result .=$attributes;
        $result .=$valStore;
        $result .=">";
        if (!$isRequired) {
          $result .= '<option value=" " ></option>';
        }

        foreach ($arrayTargetDate as $key => $value) {
            $result .= '<option value="' . $key . '"';
            if($this->id and $key === $this->targetDateNotifiableField) {
                $result .= ' selected="selected" ';
            }
            $result .= '><span >'. htmlEncode($value) . '</span></option>';
        }

        $result .=$colScript;
        $result .="</select></td>";
    } else {
          $result .= '<td style="color:grey;'.$fieldStyle.'">' . i18n("col".ucfirst($this->targetDateNotifiableField)) . "&nbsp;&nbsp;&nbsp;</td>";        
    }
    $result .= '</tr>';
    return $result;
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

    if ($colName == "idNotifiable") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  refreshTargetDateFieldNotification(this.value);';
      $colScript .= '  refreshListItemsInNotificationDefinition(this.value,"NO");';
      $colScript .= '  refreshListItemsInNotificationDefinition(this.value,"YES");';
      $colScript .= '  refreshAllowedWordsForNotificationDefinition(this.value);';
      $colScript .= '  refreshAllowedReceiversForNotificationDefinition(this.value);';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } elseif ($colName == "everyMonth" || $colName =="everyYear" || $colName == "everyDay" || $colName == "everyWeek") {
      $colScript .= '<script type="dojo/connect" event="onClick" >';
      $colScript .= '  setFixedMonthDayAttributes(this.name);';
      $colScript .= '  readOnlyNotificationGenerateBeforeInMin();';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';        
    } elseif ($colName == "fixedMonth" || $colName == "fixedMonthDay") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      if ($colName == "fixedMonthDay") {
        $colScript .= '  setGenerateBeforeWhenFixedDayChange(this.value);';          
      }
      $colScript .= '  setDrawLikeFixedDayWhenFixedMonthChange(this.value, this.name);';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';              
    } elseif ($colName == "_spe_targetDateNotifiableField") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  readOnlyNotificationGenerateBeforeInMin(this.value);';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';                      
    } elseif ($colName == "fixedDay") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  setGenerateBeforeWhenFixedDayChange(this.value);';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';                              
    } elseif ($colName == "notificationNbRepeatsBefore") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  setGenerateBeforeWhenNotificationDayBeforeChange(this.value);';
      $colScript .= '  readOnlyNotificationGenerateBeforeInMin();';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } elseif ($colName == "notificationGenerateBefore") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  readOnlyNotificationGenerateBeforeInMin();';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';                                      
    } elseif ($colName == "notificationGenerateBeforeInMin") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.value>0) {';
      $colScript .= '       dijit.byId("notificationGenerateBefore").set("readOnly",true);';
      $colScript .= '       dijit.byId("notificationGenerateBefore").setValue("");';
      $colScript .= '  } else {';
      $colScript .= '       dijit.byId("notificationGenerateBefore").set("readOnly",false);';
      $colScript .= '       dijit.byId("notificationGenerateBefore").setValue("");';
      //$colScript .= '       dijit.byId("notificationGenerateBeforeInMin").set("readOnly",true);';
      //$colScript .= '       dijit.byId("notificationGenerateBeforeInMin").setValue("");';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';                                      
    }
    return $colScript;
  }

// =============================================================================================================
// MISCELANOUS FUNCTION
// =============================================================================================================

  /** ==========================================================================
   * Query the Db
   * @return stdClass[] : The lines return by the select
   */
  private function getLines($query) {
    $objects=array();
    $result = Sql::query($query);
    if (! $result) {
        return array("KO");
    }
    if (Sql::$lastQueryNbRows > 0) {
      $obj = new stdClass();
      $line = Sql::fetchLine($result);
      while ($line) {
        $objects[]=$line;
        $line = Sql::fetchLine($result);
      }
    } else {
        $objects=array();
    }
    return $objects;
  }
  

  /** ==========================================================================
   * Transform all words contented in $field in array[table_field]
   * @param string : the field's value to transform
   * @param string : the origin class of the field
   * @param array : An array contenting $table_$field
   * @return array : An array contenting table_field
   */
  private function transformWordsInArrayClassField($theField, $className, $tablesAndFields=array()) {
    $theField = str_replace('${', '#{', $theField);
    
      
    while (strpos($theField,'#{')!==false) {
        $table = "";
        $field = "";
        // While a word '#{xxxx} exists
        $deb =  strpos($theField,'#{')+2;
        $end = strpos($theField,'}');
        $word = substr($theField, $deb, $end-$deb);
        if (substr($word,0,2)==='id' && $word!="idle" && strpos($word,'.')!==false) {
            // The word makes reference to a foreign key's field
            
            $word = substr($word, 2);
            $posDot = strpos($word,'.');
            // The foreign Table
            $table = substr($word,0,$posDot);
            // The foreign field
            $field = substr($word,$posDot+1);
            
            $theField = preg_replace('/id/','',$theField,1);
        } else {
            $table = $className;
            $field = $word;
        }
        // Replace #{xxxx} by the table and field
        $theField = preg_replace('/#{'.$word.'}/',"",$theField,1);
        $tableAndField = $table."_".$field;
                        
        if (!in_array($tableAndField, $tablesAndFields)) {
            $tablesAndFields[] = $tableAndField;
        }
    }
    return $tablesAndFields;
  }
  
  private function transformReceiversInArrayClassField($className, $classesAndFields, &$receiversList) {
    $receiversList=array();
    
    $arrReceivers = explode(';',$this->notificationReceivers);
    foreach ($arrReceivers as $receiver) {
        if (strpos($receiver,'.')===false) {
            $class = $className;
            $field = $receiver;
        } else {
            $receiver = substr($receiver,2);
            $posDot = strpos($receiver,'.');
            $class = substr($receiver,0,$posDot);
            $field = substr($receiver,$posDot+1);
        }
        $classAndField = $class."_".$field;

        if (!in_array($classAndField, $classesAndFields)) {
            $classesAndFields[] = $classAndField;
            $receiversList[] = $classAndField;
        }
    }
    return $classesAndFields;
  }
  
  private function setValuesInField($theField, $className, $obj) {
      
      
    $theField = str_replace('${', '#{', $theField);
    while (strpos($theField,'#{')!==false) {
        $class = "";
        $field = "";
        // While a word '#{xxxx} exists
        $deb =  strpos($theField,'#{')+2;
        $end = strpos($theField,'}');
        $word = substr($theField, $deb, $end-$deb);
        if (substr($word,0,2)==='id' && $word!="idle" && strpos($word,'.')!==false) {
            // The word makes reference to a foreign key's field
            
            $word = substr($word, 2);
            $posDot = strpos($word,'.');
            // The foreign Table
            $class = substr($word,0,$posDot);
            // The foreign field
            $field = substr($word,$posDot+1);
            
            $theField = preg_replace('/id/','',$theField,1);
        } else {
            $class = $className;
            $field = $word;
        }
        // Replace #{xxxx} by the value
        $classAndField = $class."_".$field;
        $value = $obj[$classAndField];
        
        // Before place value in field
        // Replace #{ by |||||{  and } by ~~~~~~ in the value 
        $value = str_replace("#{", "|||||{", $value);
        $value = str_replace("}", "~~~~~~", $value);
        $theField = preg_replace('/#{'.$word.'}/',$value,$theField,1);
    }
    // Replace |||||{ by #{ in the field
    $theField = str_replace("|||||{", "#{", $theField);
    $theField = str_replace("~~~~~~", "}", $theField);
    
    return $theField;  
  }
  
  /** ==========================================================================
   * Generate the notifications for this notification definition
   * @return string : "OK" => Generation is done - "RULE" => Error in rule syntax - "KO" => No id for notificationDefinition
   */
  public function generateNotifications() {
    if (is_null($this->id)) {return "KO";}
    
    $paramDbPrefix = strtolower(Parameter::getGlobalParameter ( 'paramDbPrefix' ));
    $cronCheckNotifications = Parameter::getGlobalParameter ( 'cronCheckNotifications' ) / 60 ;
    
    // The Object Class
    $notificationItem = new Notifiable($this->idNotifiable);
    $className = $notificationItem->notifiableItem;
    
    // Contents the leftJoin
    $leftJoin = "";
    
    // Contents list of fields to put in the select
    $fieldsInSelect = "";
    
    // List of classes in the query
    $listClasses[]= $className;
    
    // id is in select
    $listFields[] = $className."_id";
    
    // The Target Date
    $targetDate = $this->targetDateNotifiableField;
    if ($targetDate!=='without') {
        $listFields[] = $className."_".$targetDate;
    }
    
    // Fields in the Title
    $listFields = $this->transformWordsInArrayClassField($this->title, $className, $listFields);

    // Fields in the Content
    $listFields = $this->transformWordsInArrayClassField($this->content, $className, $listFields);

    // Fields in the Rule
    $listFields = $this->transformWordsInArrayClassField($this->notificationRule, $className, $listFields);

    // Fields in the receivers
    $receiverList=array();
    $listFields = $this->transformReceiversInArrayClassField($className, $listFields, $receiverList);
    
    foreach($listFields as $classAndField) {
        $classe = substr($classAndField,0,strpos($classAndField,'_'));
        if (!in_array($classe, $listClasses)) { $listClasses[]=$classe;}
    }
    
    foreach($listClasses as $class) {
        $obj = new $class();
        $databaseTableName = $obj->getDatabaseTableName();
        $listTables[$class] = $databaseTableName;
    }
    
    foreach($listFields as $classAndField) {
        $class = substr($classAndField,0,strpos($classAndField,'_'));
        
        $field = substr($classAndField,strpos($classAndField,'_')+1);
        $fieldsInSelect .= ($fieldsInSelect===""?"":",")."$listTables[$class].$field AS $class"."_$field";

        if ($class!== $className and strpos($leftJoin, "LEFT JOIN $listTables[$class] ON")=== false) {
            $leftJoin .= " LEFT JOIN $listTables[$class] ON $listTables[$class].id=$listTables[$className].id$class";            
        }
    }
    
    
    // The Rule for clause WHERE
    $rule = $this->notificationRule;
    $rule = str_replace('${', '#{', $rule);
    // Find the tables and fields inside the rule to construct where clause
    while (strpos($rule,'#{')!==false) {
        $table = "";
        $field = "";
        // While a word '#{xxxx} exists
        $deb =  strpos($rule,'#{')+2;
        $end = strpos($rule,'}');
        $word = substr($rule, $deb, $end-$deb);
        if (substr($word,0,2)==='id' && $word!="idle" && strpos($word,'.')!==false) {
            // The word makes reference to a foreign key's field
            // The field to use for innerJoin
            $word = substr($word, 2);
            $posDot = strpos($word,'.');
            // The foreign Table
            $table = substr($word,0,$posDot);
            // The foreign field
            $field = substr($word,$posDot+1);
            
            $rule = preg_replace('/id/','',$rule,1);
        } else {
            $table = $className;
            $field = $word;
        }
        $table = strtolower($paramDbPrefix.$table);
        // Replace #{xxxx} by the table and field
        $tableAndField = "$table.$field";
        $rule = preg_replace('/'.$word.'/',"",$rule,1);
        $rule = preg_replace('/#{/',$tableAndField,$rule,1);
        $rule = preg_replace("/}/","",$rule,1);
    }
    // The clause Where
    if (trim($rule)=="") {
        $where = "";
    } else {
        $where=" WHERE $rule";    
    }

    // EveryDay
    $everyDay = $this->everyDay;
        
    // EveryWeek
    $everyWeek = $this->everyWeek;

    // EveryMonth
    $everyMonth = $this->everyMonth;
        
    // EveryYear
    $everyYear = $this->everyYear;
    
    // fixedMonth & fixedDay
    $fixedDay = $this->fixedDay;
    $fixedMonth = $this->fixedMonth;
    
    // nbRepeats Before
    $doAfter = false;
    $nbRepeatsBefore = $this->notificationNbRepeatsBefore;
    if ($nbRepeatsBefore<0 and $nbRepeatsBefore != "") {
        $doAfter = true;
        $nbRepeatsBefore=0;
    }
    
    // Generate Before
    if ($this->notificationGenerateBefore=="") {
        $generateBefore = 0;        
    } else {
        $generateBefore = $this->notificationGenerateBefore;
    }
    
    // Generate Before in minute
    if (substr($this->targetDateNotifiableField,-8)==="DateTime") {
        if ($this->notificationGenerateBeforeInMin=="" or is_null($this->notificationGenerateBeforeInMin)) {
            $generateBeforeInMin = 0;
        } else {
            $generateBeforeInMin = $this->notificationGenerateBeforeInMin;
        }
    } else {
        $generateBeforeInMin = 0;        
    }
    
    // The current date
    $theCurrentDate = new DateTime();

    // Initialize the notifications's informations
    $notif = new Notification();
    $notif->idMenu = $this->idMenu;
    $name = $this->name.' - To #';
    $notif->idNotificationDefinition = $this->id;
    $notif->idMenu = $this->idMenu;
    $notif->idNotifiable = $this->idNotifiable;
    $notif->idNotificationType = $this->idNotificationType;
    $notif->idle = 0;
    $notif->sendEmail = $this->sendEmail;
    $notif->emailSent=0;
    $notif->idResource = getSessionUser()->id;
    $notif->idStatusNotification = 1;
//    $notif->idUser = getSessionUser()->id;
    
    // Add className id in fieldsInSelect if is not
    if (strpos($fieldsInSelect, $className."_id")===false) {
        $fieldsInSelect .= ",".$className."_id";
    }
    
    // The Query
    if (trim($where)=="WHERE") {
        $where="";
    }
    $query = "SELECT $fieldsInSelect FROM $listTables[$className] $leftJoin $where";
    $listObjClasses = $this->getLines($query);
    // Error in query => Provide from $where and then from rule
    if (in_array("KO", $listObjClasses)) {
        $listObjClasses=array();
        return "RULE";
    }
            
    // For each instance of the Object Class
    foreach ($listObjClasses as $objClass) {
        // The id of notified instance object
        $notif->notifiedObjectId = $objClass[$className.'_id'];

        // The targetDate value
        if ($targetDate!="without") {
            $targetDateValue = $objClass[$className.'_'.$targetDate];
            // TargetDate is null => nothing to do
            if ($targetDateValue=="") {                
                continue;
            }
            $theTargetDate = new DateTime($targetDateValue);
        } else { // Without targetDate, targedDate is current date
            $theTargetDate = new DateTime();
        }

        $minusDate = new DateTime($theTargetDate->format('Y-m-d'));
        if ($this->everyDay) {
            $minusDate->modify("-{$nbRepeatsBefore} days");
        } elseif ($this->everyWeek) {
            $theNbDays = $nbRepeatsBefore*7;
            $minusDate->modify("-{$theNbDays} days");
        } elseif ($this->everyMonth) {
            $minusDate->modify("-{$nbRepeatsBefore} months");            
        } elseif ($this->everyYear) {
            $minusDate->modify("-{$nbRepeatsBefore} years");            
        }
        
        $time = null;
        // Calculate the notification date
        if (!$everyDay and !$everyWeek and !$everyMonth and !$everyYear) {
            // EveryDay and EveryWeek and EveryMonth and EveryYear not checked
            // Notification date is the targetDate
            if ($generateBeforeInMin>0) {
                $theTargetDate->modify("-$generateBeforeInMin minutes");
                $time = $theTargetDate->format('H:i:s');
            }
            $year = $theTargetDate->format('Y');
            $month = $theTargetDate->format('m');
            $day = $theTargetDate->format('d');
        } elseif ($everyDay or $everyWeek) {
            // EveryDay checked
            $year = $theTargetDate->format('Y');
            $month = $theTargetDate->format('m');
            $day = $theTargetDate->format('d');
        } elseif ($everyMonth) {
            // EveryMonth checked
            // ==> Replace the target date month by the current month
            $year = $theTargetDate->format('Y');
            $month = $theTargetDate->format('m');
            $day = $theTargetDate->format('d');
            if ($fixedDay>0) {
                // At a fixed day
                // ==> Replace the target date day by the fixed day
                $day = sprintf("%02d", $fixedDay);
            }
        } else {
            // EveryYear checked
            // ==> Replace the target date year by the current year
            $year = $theCurrentDate->format('Y');
            $month = $theTargetDate->format('m');
            $day = $theTargetDate->format('d');
            if ($fixedMonth>0) {
                // At a fixed month
                // ==> Replace the target month by the fixed month
                $month = sprintf("%02d", $fixedMonth);                
            }
            
            if ($fixedDay>0) {
                // At a fixed day
                // ===> Replace the target day by the fixed day
                $day = sprintf("%02d", $fixedDay);                
            }
        }
        
        // Take care of month's last day
        $dateString = $year.'-'.$month.'-01';
        $lastday = date('t',strtotime($dateString));
        if (intval($day)>$lastday) {
            $day = sprintf("%02d", $lastday);
        }
        $dateString = $year.'-'.$month.'-'.$day;
        
        if ($everyDay) {
            if ($doAfter) {
                if ($theTargetDate->format('Y-m-d') < $theCurrentDate->format('Y-m-d')) {
                    $theNotificationDate = new DateTime($theCurrentDate->format('Y-m-d'));                    
                } else {
                    continue;
                }
            } else {
                if ($theCurrentDate->format('Y-m-d') <= $theTargetDate->format('Y-m-d') and 
                    $theTargetDate->format('Y-m-d') >= $minusDate->format('Y-m-d')) {
                    $theNotificationDate = new DateTime($theCurrentDate->format('Y-m-d'));
                } else {
                    continue;
                }
            }
        } elseif ($everyWeek) {
            // EveryWeek checked
            $theNotificationDate = new DateTime($theTargetDate->format('Y-m-d'));
            $theDate = new DateTime($theNotificationDate->format('Y-m-d'));

            if ($doAfter) {
                $theDate->modify("+7 days");
                while($theDate->format('Y-m-d') <= $theCurrentDate->format('Y-m-d')) {
                    $theNotificationDate = new DateTime($theDate->format('Y-m-d'));
                    $theDate->modify("+7 days");
                }
            } else {
                $theDate->modify("-7 days");                
//                while($theDate->format('Y-m-d') >= $theCurrentDate->format('Y-m-d')) {
                while($theDate->format('Y-m-d') >= $theCurrentDate->format('Y-m-d') and $theDate->format('Y-m-d') >= $minusDate->format('Y-m-d')) {
                    $theNotificationDate = new DateTime($theDate->format('Y-m-d'));
                    $theDate->modify("-7 days");
                }
            }
            $theDate = new DateTime($theNotificationDate->format('Y-m-d'));
            $theDate->modify("-{$generateBefore} days");
            if ($theDate->format('Y-m-d') > $theCurrentDate->format('Y-m-d')) {
                continue;
                }
        } elseif ($everyMonth) {
            // EveryMonth checked
        $theNotificationDate = new DateTime($dateString);
            $theDate = new DateTime($theNotificationDate->format('Y-m-d'));
            if ($doAfter) {
               $theDate->modify("+1 months");
                while($theDate->format('Y-m-d') <= $theCurrentDate->format('Y-m-d')) {
                    $theNotificationDate = new DateTime($theDate->format('Y-m-d'));
                    $theDate->modify("+1 months");
                }                                
            } else {
               $theDate->modify("-1 months");
//                while($theDate->format('Y-m-d') >= $theCurrentDate->format('Y-m-d')) {
                while($theDate->format('Y-m-d') >= $theCurrentDate->format('Y-m-d') and $theDate->format('Y-m-d') >= $minusDate->format('Y-m-d')) {
                    $theNotificationDate = new DateTime($theDate->format('Y-m-d'));
                    $theDate->modify("-1 months");
                }                
            }
            $theDate = new DateTime($theNotificationDate->format('Y-m-d'));
            $theDate->modify("-{$generateBefore} days");
            if ($theDate->format('Y-m-d') > $theCurrentDate->format('Y-m-d')) {
                continue;
                }
        } elseif ($everyYear) {
            // EveryYear checked            
            $theNotificationDate = new DateTime($dateString);
            $theDate = new DateTime($theNotificationDate->format('Y-m-d'));
            if ($doAfter) {
                $theDate->modify("+1 years");
                while($theDate->format('Y-m-d') <= $theCurrentDate->format('Y-m-d')) {
                    $theNotificationDate = new DateTime($theDate->format('Y-m-d'));
                    $theDate->modify("+1 years");
                }                
            } else {
                $theDate->modify("-1 years");
//                while($theDate->format('Y-m-d') >= $theCurrentDate->format('Y-m-d')) {
                while($theDate->format('Y-m-d') >= $theCurrentDate->format('Y-m-d') and $theDate->format('Y-m-d') >= $minusDate->format('Y-m-d')) {
                    $theNotificationDate = new DateTime($theDate->format('Y-m-d'));
                    $theDate->modify("-1 years");
                }                
            }
            $theDate = new DateTime($theNotificationDate->format('Y-m-d'));
            $theDate->modify("-{$generateBefore} days");
            if ($theDate->format('Y-m-d') > $theCurrentDate->format('Y-m-d')) {
                continue;
                }
        } else {
            // Nothing is checked
            $theNotificationDate = new DateTime($dateString);
            // Minus the notification date with generateBefore
            $theNotificationDate->modify("-{$generateBefore} days");
        }
        
        // If the notification date < current date
        // ==> Nothing to do
        if ($theNotificationDate->format('Y-m-d') < $theCurrentDate->format('Y-m-d')) {
            continue;
        }
        
        // If dateTime
        if ($generateBeforeInMin>0) {
                // If the notification date = the current date
            if ($theNotificationDate->format('Y-m-d') == $theCurrentDate->format('Y-m-d')) { 
                // Notification Time > current time
                if ($time > $theCurrentDate->format('H:i:s') ) {
                    // Nothing to do
                    continue;
                }
            }
        }
        
        $theStringDate  = $theNotificationDate->format('Y') . '-';
        $theStringDate .= $theNotificationDate->format('m') . '-';
        $theStringDate .= $theNotificationDate->format('d');            

        if ($everyDay and !isOpenDay($theStringDate)) {
            // Nothing to do on day off and every day
            continue;
        }
        
        // If the notification date <> current date after application of generate before
        if ($theNotificationDate->format('Y-m-d') <> $theCurrentDate->format('Y-m-d')) {
            continue;
        }
        // Check if a Notification is already generated
        $crit = array( "idNotificationDefinition"  => $this->id,
                       "idNotifiable"              => $this->idNotifiable,
                       "notificationDate"          => $theNotificationDate->format('Y-m-d'),
                       "notificationTime"          => $time,
                       "notifiedObjectId"          => $notif->notifiedObjectId
                     );
        $listNotif = $notif->getSqlElementsFromCriteria($crit);
        // Already generated => Nothing to do
        if (!empty($listNotif)) { 
            continue;
        }
        
        // The Title values        
        $notif->title = $this->setValuesInField($this->title, $className, $objClass);
        // The Content values
        $notif->content = $this->setValuesInField($this->content, $className, $objClass);
        
        // If receiver is'nt an user, no chance to see notification => Don't take
        $critUser = array(
                          "isUser"    => '1',
                          "idle"      => '0',
                          "locked"    => '0',
                         );
        $theUser = new User();
        $usersList = $theUser->getSqlElementsFromCriteria($critUser);
        $userIds = array();
        foreach($usersList as $theUser) {
            $userIds[]=$theUser->id;
        }
        
        // Create a notification per receiver
        $receiversId = [];
        foreach( $receiverList as $receiver) {
            $idReceiver = $objClass[$receiver];            
            if ($idReceiver>0 and !in_array($idReceiver, $receiversId)) {
                if (!in_array($idReceiver, $userIds)) {
                    $receiversId[] = $idReceiver;
                    continue;
                }
                $notif->id=null;
                $notif->idUser = $objClass[$receiver];
                $notif->name = $name.$objClass[$receiver];
                $notif->notificationDate = $theNotificationDate->format('Y-m-d');            
                $notif->notificationTime = $time;
                $notif->save(); 
                $receiversId[] = $idReceiver;
            }
        }
    }
    return "OK";
  }
  
  /** ==========================================================================
   * Return the notifiable of this or the first notifiable is this is null
   * @return ObjectClass : The notifiable
   */
  private function getNotifiableItem() {
    if ($this->id) {
        $notifiableItem = new Notifiable($this->idNotifiable);
    } else {
        $crit = array();
        $notifiableItem=SqlElement::getFirstSqlElementFromCriteria('Notifiable', $crit);
    }
    return $notifiableItem;
  }
  
  /** =========================================================================
   * Verify that object's fields contented in the string passed in parameter are fields recognized
   * by this object Class or these linked by id (foreign key) object's classes.
   * @param string $objectClassFieldName : The objectClass's field name to verify
   * @param string $className : The objectClass' name to verify
   * @return "OK" if verifications are good or an error message
   */
    public function verifyObjectFields($objectClassFieldName=null, $className=null) {
        if (is_null($objectClassFieldName) or is_null($className)) {
            return "";
        }
        $result = "";
              
        $listFields = array();
        $ars = explode('${', $this->$objectClassFieldName);
        foreach($ars as $ar) {
            if (strpos($ar,'}')>0) {
                $listFields[] = substr($ar,0, strpos($ar,'}'));
            }
        }

        if (empty($listFields)) {return $result;}
        
        $fullFieldsList = getObjectClassAndForeignClassFieldsList($className);
        
        $fieldError = "";
        foreach($listFields as $field) {
            if (!in_array($field, $fullFieldsList)) {
                $fieldError.='<br/>' . $field;                                    
            }
        }        
        if ($fieldError!="") {
            $result  = '<br/>' . i18n('In'). ' ('. i18n("col".ucfirst($objectClassFieldName)).') - ';
            $result .= i18n("nonExistentFields").' :';
            $result .= $fieldError;
        }
        return $result;
    }

 /** ==============================================================================================
   * Verify that notificationReceivers contents only fields that are fields issued from ObjectClass
   * witch database table is 'resource' and that are recognized by this object Class or 
   * these linked by id (foreign key) object's classes.
   * @param string $className : The objectClass' name to verify
   * @return "OK" if verifications are good or an error message
   */
    public function verifyNotificationReceivers($className="") {
              
        $result = "";
        $listFields = explode(';', trim($this->notificationReceivers));

        if (empty($listFields) or $className==="") {return $result;}
               
        $fullFieldsList = getObjectClassAndForeignClassFieldsList($className, true);
        
        $fieldError = "";
        foreach($listFields as $field) {
            if (!in_array($field, $fullFieldsList)) {
                $fieldError.='<br/>' . $field;                                    
            }
        }        
        if ($fieldError!="") {
            $result  = '<br/>' . i18n('In'). ' ('. i18n("colNotificationReceivers").') - ';
            $result .= i18n("nonExistentFields").' :';
            $result .= $fieldError;
        }
        return $result;
    }

 /** ===================================================================================================
   * Set a field's attribut to hidden or not
   * @param string $field :  the field for witch set attribut
   * @param boolean $hidden :  true to set to hidden - False to set to 'visible'
   * @return nothing
   */
   public function setFieldAttributeHidden($field,$hidden) {
        if (!array_key_exists($field, self::$_fieldsAttributes)) {
            self::$_fieldsAttributes[$field]="";
        }
        if ($hidden) {
            if (strpos(self::$_fieldsAttributes[$field],'hidden')>0) {return;}
               self::$_fieldsAttributes[$field] .= (self::$_fieldsAttributes[$field]==""?"hidden":",hidden");
        } else {
            self::$_fieldsAttributes[$field] = str_replace('hidden','', self::$_fieldsAttributes[$field]);
            self::$_fieldsAttributes[$field] = str_replace(',,',',', self::$_fieldsAttributes[$field]);
        }
   } 
    
 /** ===================================================================================================
   * Set a field's attribut to readonly or not
   * @param string $field :  the field for witch set attribut
   * @param boolean $readOnly :  true to set to read only - False to set to 'modify'
   * @return nothing
   */
   public function setFieldAttributeReadonly($field,$readOnly) {
        if (!array_key_exists($field, self::$_fieldsAttributes)) {
            self::$_fieldsAttributes[$field]="";
        }
        if ($readOnly) {
            if (strpos(self::$_fieldsAttributes[$field],'readonly')>0) {return;}
            self::$_fieldsAttributes[$field] .= (self::$_fieldsAttributes[$field]==""?"readonly":",readonly");
        } else {
            self::$_fieldsAttributes[$field] = str_replace('readonly','', self::$_fieldsAttributes[$field]);
            self::$_fieldsAttributes[$field] = str_replace(',,',',', self::$_fieldsAttributes[$field]);
        }
   } 
   
 /** ===================================================================================================
   * Set attribut of TargeDateNotifiableField with required or not in function of
   * everyMonth, fixedMonth, fixedDay values
   * @return nothing
   */
   public function setTargetDateNotifiableField() {
//       if (intval($this->everyYear)===1 and intval($this->_drawLike_fixedDay)>0 and intval($this->fixedMonth)>0) {
//            if (isset(self::$_fieldsAttributes['targetDateNotifiableField'])) {
//                $fieldAttr = self::$_fieldsAttributes['targetDateNotifiableField'];
//                $fieldAttr = str_replace('required', '', $fieldAttr);
//                self::$_fieldsAttributes['targetDateNotifiableField'] = $fieldAttr;
//            }
//            if (isset(self::$_fieldsAttributes['_spe_targetDateNotifiableField'])) {
//                $fieldAttr = self::$_fieldsAttributes['_spe_targetDateNotifiableField'];
//                $fieldAttr = str_replace('required', '', $fieldAttr);
//                self::$_fieldsAttributes['_spe_targetDateNotifiableField'] = $fieldAttr;
//            }
//       } else {
//            if (isset(self::$_fieldsAttributes['targetDateNotifiableField'])) {
//                $fieldAttr = self::$_fieldsAttributes['targetDateNotifiableField'];
//                $fieldAttr .= ($fieldAttr===""?"required":",required");
//                self::$_fieldsAttributes['targetDateNotifiableField'] = $fieldAttr;
//            }
//            if (isset(self::$_fieldsAttributes['_spe_targetDateNotifiableField'])) {
//                $fieldAttr = self::$_fieldsAttributes['_spe_targetDateNotifiableField'];
//                $fieldAttr .= ($fieldAttr===""?"required":",required");
//                self::$_fieldsAttributes['_spe_targetDateNotifiableField'] = $fieldAttr;
//            }           
//       }
   }
   
 /** ===================================================================================================
   * Set fixedDay and fixedMonth attributes to hidden or not in function of everyMonth, everyYear values
   * @return nothing
   */
    public function setHiddenFixedDayFixedMonthAttributes() {
        if ($this->everyDay) {
            $this->setFieldAttributeHidden('fixedDay', true);
            $this->setFieldAttributeHidden('fixedMonth', true);
            $this->setFieldAttributeHidden('fixedMonthDay', true);
            $this->everyWeek=false;
            $this->everyYear=false;
            $this->everyMonth=false;            
        }

        if ($this->everyWeek) {
            $this->setFieldAttributeHidden('fixedDay', true);
            $this->setFieldAttributeHidden('fixedMonth', true);
            $this->setFieldAttributeHidden('fixedMonthDay', true);
            $this->everyDay=false;
            $this->everyYear=false;
            $this->everyMonth=false;            
        }

        if ($this->everyMonth) {
            $this->setFieldAttributeHidden('fixedDay', false);
            $this->setFieldAttributeHidden('fixedMonth', true);
            $this->setFieldAttributeHidden('fixedMonthDay', true);
            $this->everyWeek=false;
            $this->everyDay=false;
            $this->everyYear=false;
        } else {
            $this->setFieldAttributeHidden('fixedDay', true);            
        }
        
        if ($this->everyYear) {
            $this->setFieldAttributeHidden('fixedDay', true);
            $this->setFieldAttributeHidden('fixedMonthDay', false);
            $this->setFieldAttributeHidden('fixedMonth', false);
            $this->everyWeek=false;
            $this->everyDay=false;
            $this->everyMonth=false;
        } else {
            $this->setFieldAttributeHidden('fixedMonthDay', true);            $this->setFieldAttributeHidden('fixedMonth', true);
        }        
    }
    
}
?>