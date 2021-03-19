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
 * 
 */ 
require_once('_securityCheck.php');
class CallForTenderMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place
  public $reference; 
  public $name;
  public $idCallForTenderType;
  public $idProject;
  public $idUser;
  public $creationDate;
  public $maxAmount;
  public $deliveryDate;
  public $description;
  public $businessRequirements;
  public $technicalRequirements;
  public $otherRequirements;  
  public $_sec_treatment;
  public $idStatus;
  public $idResource;
  public $sendDateTime;
  public $expectedTenderDateTime;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  public $_sec_productComponent_right;
  public $idProduct;
  public $idComponent;
  public $idProductVersion;
  public $idComponentVersion;
  public $_sec_submissions;
  public $_spe_submissions;
  public $_sec_evaluationCriteria;
  public $_spe_evaluationCriteria;
  public $evaluationMaxValue;
  public $fixValue;
  public $_lib_colFixValue;
  public $_sec_situation;
  public $idSituation;
  public $_spe_situation;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
    
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameCallForTenderType" width="15%" >${type}</th>
    <th field="name" width="50%" >${name}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "idProject"=>"required",
                                  "name"=>"required",
                                  "idCallForTenderType"=>"required",
                                  "idStatus"=>"required",
  								                "idUser"=>"",      
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "evaluationMaxValue"=>"nobr",
                                  "fixValue"=>"nobr",
                                  "idSituation"=>"readonly"
  );  
  
  private static $_colCaptionTransposition = array('idUser'=>'issuer',
      'idCallForTenderType'=>'type', 
      'idResource'=>'responsible',
      'sendDateTime'=>'sendDate',
      'expectedTenderDateTime'=>'expectedTenderDate',
      'idSituation'=>'actualSituation'
  );
  
  private static $_fieldsTooltip = array(
      "fixValue"                                       => "tooltipLevelNote",
      "_lib_colFixValue"                                       => "tooltipLevelNote",
  );    
  
  private static $_databaseColumnName = array();
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($this->fixValue) {
      self::$_fieldsAttributes['evaluationMaxValue']='nobr';
    } else {
      self::$_fieldsAttributes['evaluationMaxValue']='nobr,readonly';
    }
    if(trim(Module::isModuleActive('moduleSituation')) != 1){
      self::$_fieldsAttributes['_sec_situation']='hidden';
      self::$_fieldsAttributes['idSituation']='hidden';
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
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $old=$this->getOld();
    $this->updateEvaluationMaxValue();
    $result = parent::save();
    if ($this->sendDateTime!=$old->sendDateTime 
     or $this->expectedTenderDateTime!=$old->expectedTenderDateTime
     or $this->idProject!=$old->idProject
     or $this->idCallForTenderType!=$old->idCallForTenderType
     or $this->deliveryDate!=$old->deliveryDate) {
      $tender=new Tender();
      $listTender=$tender->getSqlElementsFromCriteria(array('idCallForTender'=>$this->id));
      foreach ($listTender as $tender) {
        if ($this->sendDateTime!=$old->sendDateTime and $tender->requestDateTime==$old->sendDateTime) $tender->requestDateTime=$this->sendDateTime;
        if ($this->expectedTenderDateTime!=$old->expectedTenderDateTime and $tender->expectedTenderDateTime<=$this->expectedTenderDateTime) $tender->expectedTenderDateTime=$this->expectedTenderDateTime;
        if ($this->deliveryDate>$old->deliveryDate and $tender->deliveryDate<$this->deliveryDate)  $tender->deliveryDate=$this->deliveryDate;
        // idProject and idTenderType will be updated in Tender::save()
        $tender->save();
      }
    }
    if($this->idSituation){
    	$situation = new Situation($this->idSituation);
    	if($this->idProject != $situation->idProject){
    		$critWhere = array('refType'=>get_class($this),'refId'=>$this->id);
    		$situationList = $situation->getSqlElementsFromCriteria($critWhere,null,null);
    		foreach ($situationList as $sit){
    		  $sit->idProject = $this->idProject;
    		  $sit->save();
    		}
    		ProjectSituation::updateLastSituation($old, $this, $situation);
    	}
    }
    return $result;
  }
  
  public function copy() {
    $result=parent::copy();
    $newId=Sql::$lastCopyId;
    $crit=new TenderEvaluationCriteria();
    $list=$crit->getSqlElementsFromCriteria(array('idCallForTender'=>$this->id));
    foreach ($list as $crit) {
      $crit->idCallForTender=$newId;
      $crit->id=null;
      $crit->save();
    }
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
    if ($colName=='fixValue') {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    dijit.byId("evaluationMaxValue").set("readOnly",false); ';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("evaluationMaxValue").set("readOnly",true); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;
  }
  public function drawSpecificItem($item, $included=false) {
    global $print, $comboDetail, $nbColMax;
    $result = "";
    if ($item == 'evaluationCriteria' and ! $comboDetail) {
      $this->drawTenderEvaluationCriteriaFromObject();
    }
    if ($item == 'submissions' and ! $comboDetail) {
      $this->drawTenderSubmissionsFromObject();
    }
    if ($item == 'situation') {
      $situation = new Situation();
      $situation->drawSituationHistory($this);
    }
    return $result;
  }
  
  function drawTenderEvaluationCriteriaFromObject() {
    global $cr, $print, $outMode, $user, $comboDetail, $displayWidth, $printWidth;
    if ($comboDetail) {
      return;
    }
    $canUpdate=securityGetAccessRightYesNo('menu' . get_class($this), 'update', $this) == "YES";
    if ($this->idle == 1) {
      $canUpdate=false;
    }
    $eval=new TenderEvaluationCriteria();
    $evalList=$eval->getSqlElementsFromCriteria(array('idCallForTender'=>$this->id));
    echo '<table width="99.9%">';
    echo '<tr>';
    if (!$print) {
      echo '<td class="noteHeader smallButtonsGroup" style="width:10%">';
      if ($this->id != null and !$print and $canUpdate) {
        ////KEVIN TICKET #2278
        echo '<a '; echo 'onClick="addTenderEvaluationCriteria('.htmlEncode($this->id). ');"title="' . i18n('addTenderEvaluationCriteria') .'"'; echo '>';
        echo formatSmallButton('Add');
        echo '</a>';
      }
      echo '</td>';
    }
    echo '<td class="noteHeader" style="width:' . (($print)?'60':'50') . '%">' . i18n('colName') . '</td>';
    echo '<td class="noteHeader" style="width:20%">' . i18n('colEvaluationMaxValue') . '</td>';
    echo '<td class="noteHeader" style="width:20%">' . i18n('colCoefficient') . '</td>';
    echo '</tr>';
    $sum=0;
    foreach ( $evalList as $eval ) {     
      echo '<tr>';
      if (!$print) {
        echo '<td class="noteData smallButtonsGroup">';
        if (!$print and $canUpdate) {
        echo '  <a onClick="editTenderEvaluationCriteria(' . htmlEncode($eval->id) . ');" title="' . i18n('editTenderEvaluationCriteria'). '" >'
                .formatSmallButton('Edit') 
                .'</a> ';
        echo '  <a onClick="removeTenderEvaluationCriteria(' . htmlEncode($eval->id) . ');" title="' . i18n('removeTenderEvaluationCriteria'). '" >'
            .formatSmallButton('Remove')
            .'</a> ';
        }
        echo '</td>';
      }
      echo '<td class="noteData">' . htmlEncode($eval->criteriaName) . '</td>';
      echo '<td class="noteData" style="text-align:center">' . htmlEncode($eval->criteriaMaxValue) . '</td>';
      echo '<td class="noteData" style="text-align:center">' . htmlEncode($eval->criteriaCoef) . '</td>';
      $sum+=$eval->criteriaMaxValue*$eval->criteriaCoef;
      echo '</tr>';
    }
    echo '<tr>';
    echo '<td class="noteData" style="text-align:right" colspan="'.(($print)?'2':'3').'">' .i18n('colCountTotal') . '&nbsp;:&nbsp;</td>';
    echo '<td class="noteData" style="text-align:center">' . htmlEncode($sum) . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="'.(($print)?'3':'4').'" class="noteDataClosetable">&nbsp;</td>';
    echo '</tr>';
    echo '</table>';
  }
  
  function drawTenderSubmissionsFromObject($readOnly=false) {
    global $cr, $print, $outMode, $user, $comboDetail, $displayWidth, $printWidth;
    if ($comboDetail) {
      return;
    }
    $canUpdate=securityGetAccessRightYesNo('menu' . get_class($this), 'update', $this) == "YES";
    if ($this->idle == 1 or $readOnly ) {
      $canUpdate=false;
    }
    $tender=new Tender();
    $tenderList=$tender->getSqlElementsFromCriteria(array('idCallForTender'=>(($this->id)?$this->id:-1)),false,null, 'evaluationValue desc, id asc');
    if (count($tenderList)==0 and $readOnly) return;
    echo '<table width="99.9%">';
    echo '<tr>';
    if (!$print and !$readOnly) {
      echo '<td class="noteHeader smallButtonsGroup" style="width:10%">';
      if ($this->id != null and !$print and $canUpdate) {
        ////KEVIN TICKET #2278
        echo '<a '; echo 'onClick="addTenderSubmission('.htmlEncode($this->id). ');"title="' . i18n('addTenderSubmission') .'"'; echo '>';
        echo formatSmallButton('Add');
        echo '</a>';        
      }
      echo '</td>';
    }
    echo '<td class="noteHeader" style="width:' . (($print or $readOnly)?'40':'30') . '%">' . i18n('colIdProvider') . '</td>';
    echo '<td class="noteHeader" style="width:15%">' . i18n('colRequested') . '</td>';
    echo '<td class="noteHeader" style="width:15%">' . i18n('colExpected') . '</td>';
    echo '<td class="noteHeader" style="width:15%">' . i18n('colReceived') . '</td>';
    echo '<td class="noteHeader" style="width:15%">' . i18n('evaluationValueAndAmount') . '</td>';
    echo '</tr>';
    $sum=0;
    foreach ( $tenderList as $tender ) {
      echo '<tr>';
      if (!$print and !$readOnly) {
        echo '<td class="noteData smallButtonsGroup">';
        if (!$print and $canUpdate) {
          echo '<a '; echo 'onClick="editTenderSubmission(' . htmlEncode($tender->id) . ');" class="roundedButtonSmall" title="' . i18n('editTenderSubmission') . '" /> ';
          echo formatSmallButton('Edit');
          echo '</a>';
          echo '<a '; echo 'onClick="removeTenderSubmission(' . htmlEncode($tender->id) . ');" class="roundedButtonSmall" title="' . i18n('removeTenderSubmission') . '" /> ';
          echo formatSmallButton('Remove');
          echo '</a>';
        }
        echo '</td>';
      }
      $tenderStatus=new TenderStatus($tender->idTenderStatus);
      echo '<td class="noteData" style="cursor:pointer" onClick="gotoElement(\'Tender\','.htmlEncode($tender->id).');">' . htmlEncode(SqlList::getNameFromId('Provider',$tender->idProvider)) 
        . formatColorThumb('idTenderStatus',$tenderStatus->color, 20, 'right',htmlEncode($tenderStatus->name,'protectQuotes'))
        . '</td>';
      echo '<td class="noteData" style="text-align:center">' . htmlFormatDate($tender->requestDateTime) . '</td>';
      echo '<td class="noteData" style="text-align:center">' . htmlFormatDate($tender->expectedTenderDateTime) . '</td>';
      echo '<td class="noteData" style="text-align:center">' . htmlFormatDate($tender->receptionDateTime) . '</td>';
      echo '<td class="noteData" style="text-align:center">' . (($tender->evaluationValue===null)?'':htmlDisplayNumericWithoutTrailingZeros($tender->evaluationValue));
      if (Parameter::getGlobalParameter('ImputOfAmountProvider')=='TTC') { 
        $tenderAmount=($tender->totalFullAmount)?$tender->totalFullAmount:$tender->fullAmount;
      } else {
        $tenderAmount=($tender->totalUntaxedAmount)?$tender->totalUntaxedAmount:$tender->untaxedAmount;
      } 
      echo '<br/><span style="white-space:nowrap;font-size:90%;color:#555555"><i>'.htmlDisplayCurrency($tenderAmount,false).'</i></span>';
      echo '</td>';
      echo '</tr>';
    }
    echo '<tr>';
    echo '<td colspan="'.(($print)?'5':'6').'" class="noteDataClosetable">&nbsp;</td>';
    echo '</tr>';
    echo '</table>';
  }
  public function updateEvaluationMaxValue($withSave=false) {
    if (!$this->fixValue) {
      $crit=new TenderEvaluationCriteria();
      $list=$crit->getSqlElementsFromCriteria(array('idCallForTender'=>$this->id));
      $sum=0;
      foreach ($list as $crit) {
        $sum+=($crit->criteriaMaxValue*$crit->criteriaCoef);
      }
      $this->evaluationMaxValue=$sum;
      if ($withSave) $this->save();
    }
  }
  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  }
}
?>