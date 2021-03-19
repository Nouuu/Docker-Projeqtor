<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott LEGRAND (from Salto Consulting - 2018) 
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
 * Used in EmploymentContractTypeMain
 */ 
require_once('_securityCheck.php'); 
class CustomEarnedRulesOfEmploymentContractType extends SqlElement {    
    // List of fields that will be exposed in general user interface
    public $id;    // redefine $id to specify its visible place 
    public $idle;
    public $name;
    public $rule;
    public $whereClause;
    public $idEmploymentContractType;
    public $idLeaveType;
    public $quantity;
    
    // Define the layout that will be used for lists
  private static $_layout='';

  private static $_fieldsAttributes=array();  
  
  private static $_colCaptionTransposition = array();
  
  private static $_databaseColumnName = array();
  
//    private static $_databaseTableName = '';
  
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

    return $colScript;
  }
  
    /**=========================================================================
     * Overrides SqlElement::save() function to add specific treatments
     * @see persistence/SqlElement#save()
     * @return the return message of persistence/SqlElement#save() method
     */
    public function save() {
        $old = $this->getOld();
    
        $result = parent::save();
        $lastStatus = getLastOperationStatus($result);
        if ($lastStatus!="OK" and $lastStatus!="NO_CHANGE") { return $result; }

        // On change of : quantity, rule, whereClause, idLeaveType, idle
        // => Must calculate new leave earned for concerned EmployeeLeaveEarned
        if ($this->quantity != $old->quantity or 
            $this->rule != $old->rule or
            $this->whereClause != $old->whereClause or
            $this->idLeaveType != $old->idLeaveType or
            $this->idle != $old->idle or
            $this->idEmploymentContractType != $old->idEmploymentContractType
           ) {
            $resultE = setLeaveEarnedForContractType($this);
            if (getLastOperationStatus($resultE)!="OK" and getLastOperationStatus($resultE)!="NO_CHANGE") {
                $resultE = htmlSetResultMessage( null, 
                                                getResultMessage($resultE)." "."InUpdateOfLeaveEarnedForCalculation", 
                                                false,
                                                "", 
                                                "CalculationOfNewLeaveEarned",
                                                getLastOperationStatus($resultE)
                                               );
                return $resultE;                      
            }
        }
        return $result;

    }

  /**=========================================================================
   * Overrides SqlElement::delete() function to add specific treatments
   * @see persistence/SqlElement#delete()
   * @return the return message of persistence/SqlElement#delete() method
   */
    public function delete() {
        $old = $this->getOld();
        $result = parent::delete();
        $lastStatus = getLastOperationStatus($result);
        if ($lastStatus!="OK" and $lastStatus!="NO_CHANGE") { return $result; }
        $resultE = setLeaveEarnedForContractType($old);
        if (getLastOperationStatus($resultE)!="OK" and getLastOperationStatus($resultE)!="NO_CHANGE") {
            $resultE = htmlSetResultMessage( null, 
                                            getResultMessage($resultE)." "."InUpdateOfLeaveEarnedForCalculation", 
                                            false,
                                            "", 
                                            "CalculationOfNewLeaveEarned",
                                            getLastOperationStatus($resultE)
                                           );
            return $resultE;                      
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
    
    // quantity can't be null or less or egal to 0
    if($this->quantity!=null && $this->quantity<=0){
        $result.='<br/>' . i18n('invalidQuantity');
    }
    
    if(!($this->quantity!=null && $this->name!=null && $this->rule!=null && $this->idLeaveType!=null && $this->idEmploymentContractType!=null)){
        $result.='<br/>' . i18n('invalidAttributesForCustomEarnedRule');
    }
    
    // quantity must be a modulo of 0.5
    if($this->quantity!==null && trim($this->quantity)!==""){
        if(! (fmod($this->quantity, 0.5) == 0)){
            $result.='<br/>' . i18n('errorQuantityNotModuloOfZeroPointFive');
        }
    }
    
    $objects=$this->transformWordsInArrayClassField($this->rule);
    $ruleOK=true;
    // Error on rule
    if (array_key_exists("error", $objects)) {
        foreach($objects as $class => $field) {
            if ($class=="error") {
                $result.='<br/>' . i18n("RuleFieldWithoutTableOrTableWithoutField")." ".i18n("IN")." ".$field;                
                $ruleOK=false;
            }
        }
        unset($objects["error"]);
    }
        
    // At least, one object in rule and only one
    if (count($objects)!=1) {
        $result.='<br/>' . i18n('errorLeastOneObjectAndOnlyOneInRule');        
        $ruleOK=false;
    }
    
    // Fields must exist in class
    foreach($objects as $class => $field) {
        if (!property_exists($class, $field)) {
            $result.='<br/>' . $field. " ". i18n("IN"). " object ". $class. " " . i18n('doesntExists')." ".i18n("IN")." ".i18n("rule");
            $ruleOK=false;
        }
    }

    $whereObjects=$this->transformWordsInArrayClassField($this->whereClause);
    $whereOK = true;
    if ($this->whereClause!="") {
        // Error on whereClause
        if (array_key_exists("error", $whereObjects)) {
            foreach($whereObjects as $class => $field) {
                if ($class=="error") {
                    $result.='<br/>' . i18n("WhereFieldWithoutTableOrTableWithoutField")." ".i18n("IN")." ".$field;
                    $whereOK=false;
                }
            }
            unset($whereObjects["error"]);
        }

        // At least, one class in whereClause and only one
        if (count($whereObjects)!=1) {
            $result.='<br/>' . i18n('errorLeastOneObjectAndOnlyOneInWhere');
            $whereOK=false;
        }

        // Fields must exist in class
        foreach($whereObjects as $class => $field) {
            if (!property_exists($class, $field)) {
                $result.='<br/>' . $field. " ". i18n("IN"). " ". i18n("object")." ". $class. " " . i18n('doesntExists')." ".i18n("IN")." ".i18n("whereClause");
                $whereOK=false;
            }
        }
        
        // Same class for rule and whereClause
        if ($ruleOK and $whereOK) {
            $sameClass = true;
            foreach($objects as $class => $fld) {
                foreach($whereObjects as $wClass => $wFld) {
                    if ($class != $wClass) {
                        $sameClass=false;
                    }
                }
            }
            if (!$sameClass) {
                $result.='<br/>' . i18n("CLASS"). " ". i18n("IN"). i18n("rule"). " " . i18n('isNotTheSameThat')." ".i18n("IN")." ".i18n("whereClause");
            }
        }
    }
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
        $result.=$defaultControl;
    }
    
    if ($result == "") {$result='OK';}
    
    return $result;
  }
  
// =============================================================================================================
// MISCELANOUS FUNCTION
// =============================================================================================================
  /** ==========================================================================
   * Transform all words contented in $field in array[table => field]
   * @param string : the field's value to transform
   * @return array : An array contenting table_field
   */
  public function transformWordsInArrayClassField($theField) {
    $theField = str_replace('${', '#{', $theField);
    
    $tablesAndFields=[];
    while (strpos($theField,'#{')!==false) {
        $table = "";
        $field = "";
        // While a word '#{xxxx} exists
        $deb =  strpos($theField,'#{')+2;
        $end = strpos($theField,'}');
        $word = substr($theField, $deb, $end-$deb);
        if (strpos($word,'.')===false) {
            $tablesAndFields['error']= $word; 
        } else {            
            $posDot = strpos($word,'.');
            // The Table
            $table = substr($word,0,$posDot);
            // The field
            $field = substr($word,$posDot+1);
            
            $tablesAndFields[$table]=$field;
        }
        // Replace #{xxxx} by the table and field
        $theField = preg_replace('/#{'.$word.'}/',"",$theField,1);
    }
    return $tablesAndFields;
  }
  
}
?>
