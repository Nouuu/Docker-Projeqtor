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
 * Line defines right to the application for a menu and a profile.
 */  
require_once('_securityCheck.php'); 
class ProjectSituationMain extends SqlElement {
  
  public $_sec_Description;
  public $id;
  public $idProject;
  public $name;
  public $idle;
  public $situationNameExpense;
  public $refTypeExpense;
  public $refIdExpense;
  public $situationDateExpense;
  public $idResourceExpense;
  public $situationNameIncome;
  public $refTypeIncome;
  public $refIdIncome;
  public $situationDateIncome;
  public $idResourceIncome;
  
  public $_sec_SituationExpense;
  public $_spe_SituationExpense;
  
  public $_sec_SituationIncome;
  public $_spe_SituationIncome;
  
  public $_nbColMax=2;
  public $_readOnly = true;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="refTypeExpense" width="10%" formatter="translateFormatter">${refTypeExpense}</th>
    <th field="refIdExpense" width="4%">${refIdExpense}</th>
    <th field="situationNameExpense" width="11%">${situationNameExpense}</th>
    <th field="situationDateExpense" width="6%" formatter="dateTimeFormatter">${situationDateExpense}</th>
    <th field="nameResourceExpense" formatter="thumbName22" width="10%">${idResourceExpense}</th>
    <th field="refTypeIncome" width="10%" formatter="translateFormatter">${refTypeIncome}</th>
    <th field="refIdIncome" width="4%">${refIdIncome}</th>
    <th field="situationNameIncome" width="11%">${situationNameIncome}</th>
    <th field="situationDateIncome" width="6%" formatter="dateTimeFormatter">${situationDateIncome}</th>
    <th field="nameResourceIncome" formatter="thumbName22" width="10%">${idResourceIncome}</th>
    <th field="idle" width="4%" formatter="booleanFormatter" >${idle}</th>';

  
  private static $_fieldsAttributes=array(
      '_sec_Description'=>'hidden',
      'name'=>'hidden'
  );
  
  private static $_colCaptionTransposition = array();
  
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
  
  function save() {
    if(!$this->refIdExpense and !$this->refIdIncome){
      $this->delete();
    }
  	return parent::save();
  }
  
  protected function getStaticColCaptionTransposition($fld=null) {
  	return self::$_colCaptionTransposition;
  }
  
  protected function getStaticLayout() {
  	return self::$_layout;
  }
  
  protected function getStaticFieldsAttributes() {
  	return self::$_fieldsAttributes;
  }
  
  public function drawSpecificItem($item){
  	global $print;
  	$result="";
  	if($item == 'SituationExpense'){
  		drawProjectSituation('Expense', $this);
  	}else if($item == 'SituationIncome'){
  		drawProjectSituation('Income', $this);
  	}
  	return $result;
  }
  
  public static function updateLastSituation($old, $obj, $situation=null){
    if($situation==null and $obj->idSituation){
      $situation = new Situation($obj->idSituation);
    }
    $actualProjectSituation = new Situation();
    $actualProjectSituationList = $actualProjectSituation->getSqlElementsFromCriteria(array('idProject'=>$obj->idProject, 'situationType'=>$situation->situationType),null,null, 'date desc');
    if(count($actualProjectSituationList) > 0){
    	$actualProjectSituation = $actualProjectSituationList[0];
    }
    if($actualProjectSituation->id){
    	$projectSituation = SqlElement::getSingleSqlElementFromCriteria('ProjectSituation', array('idProject'=>$actualProjectSituation->idProject));
    	$situationType = $actualProjectSituation->situationType;
    	$projectName = SqlList::getNameFromId('Project', $actualProjectSituation->idProject);
    	$refId = $actualProjectSituation->refId;
    	$refType = $actualProjectSituation->refType;
    	$idResource = $actualProjectSituation->idResource;
    	$situationName = $actualProjectSituation->name;
    	$situationDate = $actualProjectSituation->date;
    }else{
    	$projectSituation = SqlElement::getSingleSqlElementFromCriteria('ProjectSituation', array('idProject'=>$obj->idProject));
    	$situationType = $situation->situationType;
    	$projectName = SqlList::getNameFromId('Project', $situation->idProject);
    	$refId = null;
    	$refType = null;
    	$idResource = null;
    	$situationName = null;
    	$situationDate = null;
    }
  	if(!$projectSituation->name)$projectSituation->name = i18n('ProjectSituation').' - '.$projectName;
  	if($situationType == 'expense'){
  		$projectSituation->refIdExpense = $refId;
  		$projectSituation->refTypeExpense = $refType;
  		$projectSituation->idResourceExpense = $idResource;
  		$projectSituation->situationNameExpense = $situationName;
  		$projectSituation->situationDateExpense = $situationDate;
  	}else if($situationType == 'income') {
  		$projectSituation->refIdIncome = $refId;
  		$projectSituation->refTypeIncome = $refType;
  		$projectSituation->idResourceIncome = $idResource;
  		$projectSituation->situationNameIncome = $situationName;
  		$projectSituation->situationDateIncome = $situationDate;
  	}
  	$projectSituation->save();
  	ProjectSituation::updateProjectSituation($old, $obj);
  }
  
  public static function updateProjectSituation($old, $obj){
    $inProject = '(0';
    if ($old->idProject) $inProject.=','.$old->idProject;
    if ($obj->idProject) $inProject.=','.$obj->idProject;
    $inProject.=')';
    $where = 'idProject in '.$inProject;
    $projectSituation = new ProjectSituation();
    $projectSituationList = $projectSituation->getSqlElementsFromCriteria(null, null, $where);
    foreach ($projectSituationList as $id=>$object){
      $projectSituation = new ProjectSituation($object->id);
      $actualExpenseSituation = new Situation();
      $actualExpenseSituationList = $actualExpenseSituation->getSqlElementsFromCriteria(array('idProject'=>$projectSituation->idProject, 'situationType'=>'expense'),null,null, 'date desc');
      if(count($actualExpenseSituationList) > 0){
      	$actualExpenseSituation = $actualExpenseSituationList[0];
      	$projectSituation->refIdExpense = $actualExpenseSituation->refId;
      	$projectSituation->refTypeExpense = $actualExpenseSituation->refType;
      	$projectSituation->idResourceExpense = $actualExpenseSituation->idResource;
      	$projectSituation->situationNameExpense = $actualExpenseSituation->name;
      	$projectSituation->situationDateExpense = $actualExpenseSituation->date;
      }else{
        $projectSituation->refIdExpense = null;
        $projectSituation->refTypeExpense = null;
        $projectSituation->idResourceExpense = null;
        $projectSituation->situationNameExpense = null;
        $projectSituation->situationDateExpense = null;
      }
      
      $actualIncomeSituation = new Situation();
      $actualIncomeSituationList = $actualIncomeSituation->getSqlElementsFromCriteria(array('idProject'=>$projectSituation->idProject, 'situationType'=>'income'),null,null, 'date desc');
      if(count($actualIncomeSituationList) > 0){
       $actualIncomeSituation = $actualIncomeSituationList[0];
        $projectSituation->refIdIncome = $actualIncomeSituation->refId;
        $projectSituation->refTypeIncome = $actualIncomeSituation->refType;
        $projectSituation->idResourceIncome = $actualIncomeSituation->idResource;
        $projectSituation->situationNameIncome = $actualIncomeSituation->name;
        $projectSituation->situationDateIncome = $actualIncomeSituation->date;
      }else{
        $projectSituation->refIdIncome = null;
        $projectSituation->refTypeIncome = null;
        $projectSituation->idResourceIncome = null;
        $projectSituation->situationNameIncome = null;
        $projectSituation->situationDateIncome = null;
      }
  	  $projectSituation->save();
    }
  }
  }
?>
