<?PHP
/**
 * * COPYRIGHT NOTICE *********************************************************
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
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org
 *
 * ** DO NOT REMOVE THIS NOTICE ***********************************************
 */

/**
 * ===========================================================================
 * Get the list of objects, in Json format, to display the grid list
 */
require_once "../tool/projeqtor.php";
// scriptLog(' ->/tool/jsonList.php');
$type = $_REQUEST ['listType']; // Note: checked against constant values.
if (isset ( $_REQUEST ['critField'] )) {
  $field = $_REQUEST ['critField'];
  Security::checkValidAlphanumeric ( $field );
  if (! isset ( $_REQUEST ['critValue'] )) {
    errorLog ( "incorrect query jonList : critValue is not set but critField set" );
    return;
  }
  if (substr ( $field, 0, 2 ) == 'id' and substr ( $field, 2, 1 ) == strtoupper ( substr ( $field, 2, 1 ) )) {
    if (isset ( $_REQUEST ['critArray'] )) {
      $_REQUEST ['critValue'] = explode ( ',', $_REQUEST ['critValue'] );
      foreach ( $_REQUEST ['critValue'] as $v ) {
        Security::checkValidId ( $v );
      }
    } else {
      if ($field=='idProject') {
        $idList=explode(',',$_REQUEST ['critValue']);
        foreach($idList as $idP) {
          Security::checkValidId ( $idP);
        }
      } else {
        Security::checkValidId ( $_REQUEST ['critValue'] );
      }
    }
  }
} else if (isset ( $_REQUEST ['critValue'] )) {
  errorLog ( "incorrect query jonList : critValue is set but critField is not set" );
  return;
}

echo '{"identifier":"id",';
echo 'label: "name",';
echo ' "items":[';
// If type = 'list' and $dataType = idResource : execute the listResourceProject type
$required = true; // when directly requesting 'listResourceProject', required is by default
if ($type == 'list' and array_key_exists ( 'dataType', $_REQUEST ) and ($_REQUEST ['dataType'] == 'idResource' or $_REQUEST ['dataType'] == 'idResourceAll'   or $_REQUEST ['dataType'] == 'idAccountable' or $_REQUEST ['dataType'] == 'idResponsible' or substr($_REQUEST ['dataType'],-12)=='__idResource') and array_key_exists ( 'critField', $_REQUEST ) and array_key_exists ( 'critValue', $_REQUEST ) and $_REQUEST ['critField'] == 'idProject') {
  $type = 'listResourceProject';
  $_REQUEST ['idProject'] = $_REQUEST ['critValue']; // This is valid : force idProject to critValue as criFiled=idProject (value has been tested as an id)
  $required = array_key_exists ( 'required', $_REQUEST );
}
if ($type == 'ExpenseDetailType') {
  $type = 'list';
}

if ($type == 'empty') {
  // Emty ;)
} else if ($type == 'object') { // ================================================= OBJECT =====================================================================================
  $objectClass = $_REQUEST ['objectClass'];
  Security::checkValidClass ( $objectClass, 'objectClass' );
  $obj = new $objectClass ();
  ob_start();
  $nbRows = listFieldsForFilter ( $obj, 0 );
  $json=ob_get_clean();
  $jsonValid='{"identifier":"id", "items":['.$json.']}';
  $arr=json_decode($jsonValid);
  function build_sorter($key) {
    return function ($a, $b) use ($key) {
      return strnatcmp($a->$key, $b->$key);
    };
  }
  usort($arr->items, function ($a, $b) { return (strcasecmp(replace_accents($a->name),replace_accents($b->name))); });
  $result=str_replace(array('[',']'),'',json_encode($arr->items));
  echo $result;
  //damian
}else if($type == "emailTemplate"){
  $objIdClass = RequestHandler::getValue('objectIdClass');
  $split=explode('_',$objIdClass);
  $objectId = $split[0];
  $objectClass = $split[1];
  if($objectClass == 'TicketSimple'){
    $objectClass = 'Ticket';
  }
  $obj=new $objectClass($objectId);
  $emTp = new EmailTemplate();
  $idObjectType = 'id'.$objectClass.'Type';
  $idMailable = SqlList::getIdFromTranslatableName('Mailable', $objectClass);
  $where = "(idMailable = ".Sql::fmtId($idMailable)." or idMailable IS NULL) and (idType = ".Sql::fmtId($obj->$idObjectType)." or idType IS NULL)";
  $listEmailTemplate = $emTp->getSqlElementsFromCriteria(null,false,$where);
  $tabEmailTemplate = array();
  foreach ($listEmailTemplate as $val){
  	$tabEmailTemplate[$val->id]=$val->name;
  }
  foreach ($tabEmailTemplate as $id=>$name){
    echo '{id:"'.$id.'", name:"'.$name.'"},';
  }
} else if ($type == 'operator') { // =============================================== OPERATOR ===================================================================================
  $dataType = $_REQUEST ['dataType']; // Note: checked against constant values.
  if ($dataType == 'int' or $dataType == 'date' or $dataType == 'datetime' or $dataType == 'decimal') {
    echo ' {id:"=", name:"="}';
    echo ',{id:">=", name:">="}';
    echo ',{id:"<=", name:"<="}';
    echo ',{id:"<>", name:"<>"}';
    if ($dataType != 'int' and $dataType != 'decimal') {
      // echo ',{id:"xx", name:"xx"}';
      echo ',{id:"<=now+", name:"<= ' . i18n ( 'today' ) . ' + "}';
      echo ',{id:">=now+", name:">= ' . i18n ( 'today' ) . ' + "}';
      echo ',{id:"isEmpty", name:"' . i18n ( 'isEmpty' ) . '"}';
      echo ',{id:"isNotEmpty", name:"' . i18n ( 'isNotEmpty' ) . '"}';
    }
    echo ',{id:"SORT", name:"' . i18n ( 'sortFilter' ) . '"}';
  } else if ($dataType == 'varchar') {
    echo ' {id:"LIKE", name:"' . i18n ( "contains" ) . '"}';
    echo ',{id:"NOT LIKE", name:"' . i18n ( "notContains" ) . '"}';
    // ADD qCazelles - Dynamic filter - Ticket #78
    echo ',{id:"startBy", name:"' . i18n ( 'startBy' ) . '"}';
    echo ',{id:"isEmpty", name:"' . i18n ( 'isEmpty' ) . '"}';
    echo ',{id:"isNotEmpty", name:"' . i18n ( 'isNotEmpty' ) . '"}';
    echo ',{id:"SORT", name:"' . i18n ( 'sortFilter' ) . '"}';
  } else if ($dataType == 'bool') {
    echo ' {id:"=", name:"="}';
    echo ',{id:"SORT", name:"' . i18n ( 'sortFilter' ) . '"}';
  } else if ($dataType == 'list') {
    echo ' {id:"IN", name:"' . i18n ( "amongst" ) . '"}';
    echo ',{id:"NOT IN", name:"' . i18n ( "notAmongst" ) . '"}';
    echo ',{id:"isEmpty", name:"' . i18n ( 'isEmpty' ) . '"}';
    echo ',{id:"isNotEmpty", name:"' . i18n ( 'isNotEmpty' ) . '"}';
    echo ',{id:"SORT", name:"' . i18n ( 'sortFilter' ) . '"}';
  } else if ($dataType == 'refObject') {
    echo ' {id:"LIKE", name:"' . i18n ( "contains" ) . '"},';
    echo ' {id:"hasSome", name:"' . i18n ( "isNotEmpty" ) . '"}';
    // echo ',{id:"NOT LIKE", name:"' . i18n("notContains") . '"}';
  } else {
    echo ' {id:"UNK", name:"?"}';
    echo ',{id:"SORT", name:"' . i18n ( 'sortFilter' ) . '"}';
  }
} else if ($type == 'list') { // ================================================= LIST =======================================================================================
  $dataType = RequestHandler::getValue ( 'dataType' );
  $critField = RequestHandler::getValue ( 'critField' );
  $critValue = RequestHandler::getValue ( 'critValue' );
  $dataType=str_replace(array('ActivityPlanningElement_','ProjectPlanningElement_','MilestonePlanningElement_','MeetingPlanningElement_','TestSessionPlanningElement_'),'',$dataType);
  if(strpos($critValue, '_') != null){
  	$critValue = explode('_', $critValue);
  }
  $selected = "";
  if (strpos($dataType, "__id")>0) {
    $dataType=foreignKeyWithoutAlias($dataType);
  }
  if (array_key_exists ( 'selected', $_REQUEST )) {
    $selected = $_REQUEST ['selected'];
  }
// MTY - LEAVE SYSTEM
  $leaveProjectId=null;
  if (isLeavesSystemActiv()) {
    if (array_key_exists("withoutLeaveProject", $_REQUEST)) {
          if ($_REQUEST['withoutLeaveProject']==1) {
              $leaveProjectId = Project::getLeaveProjectId();
          }
    }
  }
// MTY - LEAVE SYSTEM    
  if ($dataType == 'planning') {
    $class = 'Project';
  } else {
    $class = substr ( $dataType, 2 );
  }
  if (($dataType == 'idContact' or $dataType == 'idAffectable') and $critField == 'idProject') {
    //$list = SqlList::getListWithCrit ( 'Contact', array($critField => $critValue) );
    if(is_array($critValue)){
      foreach ($critValue as $idProj){
        $prj=new Project($idProj, true);
        $lstTopSelectedPrj=$prj->getTopProjectList(true);
        foreach ($lstTopSelectedPrj as $idProject){
          $lstTopPrj[$idProject]=$idProject;
        }
      }
    }else{
      $prj=new Project($critValue, true);
      $lstTopPrj=$prj->getTopProjectList(true);
    }
    $in=transformValueListIntoInClause($lstTopPrj);
    $today=date('Y-m-d');
    $where="idProject in " . $in;
    $where.=" and idle=0";
    $where.=" and (endDate is null or endDate>='$today')";
    $aff=new Affectation();
    $listAff=$aff->getSqlElementsFromCriteria(null,null, $where);
    $nbRows=0;
    $list=array();
    if ($selected) {
      $selectList=explode('_',$selected);
      foreach($selectList as $sel)
        $list[$sel]=SqlList::getNameFromId('Affectable', $sel);
    }
    foreach ($listAff as $aff) {
      if (! array_key_exists($aff->idResource, $list)) {
        $id=$aff->idResource;
        $name=SqlList::getNameFromId(substr($dataType, 2), $id);
        if ($name!=$id) {
          $list[$id]=$name;
        }
      }
    }
    asort($list);
  } else if ($dataType == 'idProject' and securityGetAccessRight ( 'menuProject', 'read' ) != 'ALL') {
    $user = getSessionUser ();
    $list = $user->getVisibleProjects ();
  }else if($dataType=='idBudgetParent'){
    $listBudgetElementary = SqlList::getList('BudgetItem','id',null,true);
    $showIdle = false;
    if($critValue =='on')$showIdle = true; 
    $budgetList=SqlList::getList('Budget','bbsSortable',$selected,$showIdle);
    $sepChar=Parameter::getUserParameter('projectIndentChar');
    if (!$sepChar) $sepChar='__';
    $bbsLevelArray=array();
    $list = array();
    foreach($budgetList as $key => $val){
      if( in_array($key, $listBudgetElementary))continue;
      $budgetOrder = $budgetList[$key];
      $budgetTest=$budgetOrder;
      $level=1;
      while (strlen($budgetTest)>4) {
        $budgetTest=substr($budgetTest,0,strlen($budgetTest)-6);
        if (array_key_exists($budgetTest, $bbsLevelArray)) {
          $level=$bbsLevelArray[$budgetTest]+1;
          $budgetTest="";
        }
      }
      $bbsLevelArray[$budgetOrder]=$level;
      $sep='';
      for ($i=1; $i<$level;$i++) {
        if (strpos($sepChar,'|')!==FALSE and $i<$level-1 and strlen($sepChar)>1) {
          $sepCharW = str_repeat('..', 2);
        } else {$sepCharW = $sepChar;}
        $sep.=$sepCharW;
      }
      $val =$sep.SqlList::getNameFromId('Budget', $key);
      $list[$key]=$val;
      $selected=null;
   }
  }else if ($dataType == 'imputationResource') {
    $class = 'Affectable';
    $specific = 'imputation';
    $list = getListForSpecificRights ( $specific );
    $selectedProject = getSessionValue ( 'project' );
    if(strpos($selectedProject, '_') != null){
      $selectedProject = explode('_', $selectedProject);
    }
    $limitResourceByProj = Parameter::getUserParameter ( "limitResourceByProject" );
    if ($selectedProject and $selectedProject != '*' and $limitResourceByProj == 'on') {
      $restrictTableProjectSelected = array();
      $lstTopPrj=array();
      $sub=array();   
      if(! is_array($selectedProject)) $selectedProject=explode(',',$selectedProject);
      foreach ($selectedProject as $idProj){
        $prj = new Project ( $idProj, true );
        $lstTopSelectedPrj = $prj->getTopProjectList ( true );
        foreach ($lstTopSelectedPrj as $idProject){
          $lstTopPrj[$idProject]=$idProject;
        }
        $subProj = $prj->getRecursiveSubProjectsFlatList ();
        foreach ($subProj as $id=>$name){
          $sub[$id]=$name;
        }
      }
      $in = transformValueListIntoInClause ( array_merge ( $lstTopPrj, array_keys ( $sub ) ) );
      $crit = 'idProject in ' . $in;
      $aff = new Affectation ();
      $lstAff = $aff->getSqlElementsFromCriteria ( null, false, $crit, null, true );
      foreach ( $lstAff as $id => $aff ) {
        $restrictTableProjectSelected [$aff->idResource] = $aff->idResource;
      }
    }
    $restrictArrayVisibility = getUserVisibleResourcesList ( true );
    foreach ( $list as $idR => $nameR ) {
      if (isset ( $restrictTableProjectSelected ) and ! isset ( $restrictTableProjectSelected [$idR] )) {
        unset ( $list [$idR] );
        continue;
      }
      if (! isset ( $restrictArrayVisibility [$idR] )) {
        unset ( $list [$idR] );
        continue;
      }
    }
    $user = getSessionUser ();
    if (! isset ( $list [$user->id] )) {
      $list [$user->id] = $user->name;
    }
  } else if ($dataType == 'planning') {
    $user = getSessionUser ();
    $list = $user->getVisibleProjects ();
    $restrictArray = $user->getListOfPlannableProjects ();
    //Flo #4311
    $administrativ=Project::getAdminitrativeProjectList(true);
    foreach ( $list as $prj => $prjname ) {
      if ($prj != '*' and ! isset ( $restrictArray [$prj] ) or trim ( $prj ) == '' or isset($administrativ[$prj])) {
        unset ( $list [$prj] );
      }
// MTY - LEAVE SYSTEM
      if (isLeavesSystemActiv()) {
        if ($leaveProjectId!=null and $prj == $leaveProjectId) {
          unset ( $list [$prj] );              
    }
      }
// MTY - LEAVE SYSTEM
    }
  } else if ($dataType == 'idProfile' and array_key_exists ( 'critField', $_REQUEST ) and array_key_exists ( 'critValue', $_REQUEST ) and $_REQUEST ['critField'] == 'idProject') {
    $idProj = $_REQUEST ['critValue'];
    $user = new User ();
    $prf = new Profile ( getSessionUser ()->getProfile ( $idProj ) );
    $lstPrf = $prf->getSqlElementsFromCriteria ( null, false, "idle=0 and " . (($prf->sortOrder) ? 'sortOrder>=' . $prf->sortOrder : '1=1'), "sortOrder asc" );
    $list = array();
    foreach ( $lstPrf as $profile ) {
      $list [$profile->id] = i18n ( $profile->name );
    }
    if ($selected) {
      $aff = new Affectation ( $selected );
      $list [$aff->idProfile] = SqlList::getNameFromId ( 'Profile', $aff->idProfile );
      $selected = null;
    }
  } else if (($dataType == 'idProduct' or $dataType == 'idComponent' or $dataType == 'idProductOrComponent') and array_key_exists ( 'critField', $_REQUEST ) and array_key_exists ( 'critValue', $_REQUEST )) {
    if (trim ( $_REQUEST ['critValue'] ) and $_REQUEST ['critField'] == 'idProject') {
      $list = array();
      $listProd = SqlList::getList ( $class );
      $versProj = new VersionProject ();
      $proj = new Project ( $_REQUEST ['critValue'] );
      $lst = $proj->getTopProjectList ( true );
      $inClause = '(0';
      foreach ( $lst as $prj ) {
        if ($prj) {
          $inClause .= ',';
          $inClause .= $prj;
        }
      }
      $inClause .= ')';
      $versProjList = $versProj->getSqlElementsFromCriteria ( null, false, 'idProject in ' . $inClause );
      foreach ( $versProjList as $versProj ) {
        $vers = new Version ( $versProj->idVersion );
        if (isset ( $listProd [$vers->idProduct] )) {
          $list [$vers->idProduct] = $listProd [$vers->idProduct];
        }
      }
      // Add list of products directly linked to project (not only through version)
      $pp = new ProductProject ();
      $ppList = $pp->getSqlElementsFromCriteria ( null, false, 'idProject in ' . $inClause );
      foreach ( $ppList as $pp ) {
        if (isset ( $listProd [$pp->idProduct] ))
          $list [$pp->idProduct] = $listProd [$pp->idProduct];
      }
    } else if (trim ( $_REQUEST ['critValue'] ) and $_REQUEST ['critField'] == 'idProduct') {
      $prod = new Product ( $_REQUEST ['critValue'] );
      $list = $prod->getComposition ( true, true );
      if ($selected) {
        $list [$selected] = SqlList::getNameFromId ( 'Component', $selected );
      }
    } else {
      $list = SqlList::getList ( $class );
    }
  } else if (substr ( $dataType, 0, 2 ) == 'id' and substr ( $dataType, - 4 ) == 'Type' and $dataType != 'idType' and $dataType != "idExpenseDetailType") {
    $list = SqlList::getList ( $class );
    if (array_key_exists ( 'critField', $_REQUEST ) and array_key_exists ( 'critValue', $_REQUEST )) {
      $critField = $_REQUEST ['critField'];
      $critVal = $_REQUEST ['critValue'];
      //if (strpos($critVal,',')>0) $critVal=null; // Is it really usefull to have selection of multile projects...
      if ($critField == 'idProject') {
        if (strpos($critVal, ',')) $critVal=explode(',', $critVal);
        $rtListProjectType = Type::listRestritedTypesForClass ( $class, $critVal, null, null );
        if (count ( $rtListProjectType )) {
          $first=null;
          foreach ( $list as $id => $val ) {
            //if ($id != $selected and ! in_array ( $id, $rtListProjectType )) {
            if ( ! in_array ( $id, $rtListProjectType )) {
              unset ( $list [$id] );
            } else if (!$first) {
              $first=$id;
            }
          }
          if ($selected and  ! in_array ( $selected, $rtListProjectType )) {
            $selected=$first;
          }
        }
      }
      // $_REQUEST['required']='true';
    }
  } else if ($dataType=='idBaselineSelect') {
    $list=array();
    $critProj=(RequestHandler::getValue ('critField')=='idProject')?intval(RequestHandler::getValue('critValue')):null;
    if ($critProj) {$critWhere='idProject = '.$critProj;}
    else {$critWhere='idProject in '.getVisibleProjectsList(); }
    $bl=new Baseline;
    $lstBaseline=$bl->getSqlElementsFromCriteria(null,null,$critWhere);
    foreach ($lstBaseline as $bl) {
      $list[$bl->id]=$bl->name." (".htmlFormatDate($bl->baselineDate).") - ".SqlList::getNameFromId('Project', $bl->idProject);
    }
  }else if($dataType=="refTypeIncome" or $dataType=="refTypeExpense"){
    $list=array();
    $critArray=($dataType=="refTypeIncome")?array('type'=>'Income'):array('type'=>'Expense');
    $situationable= new Situationable();
    $listSituationable=$situationable->getSqlElementsFromCriteria($critArray);
    foreach ($listSituationable as $st){
      $list[$st->id]=i18n($st->name);
    }
  }else if (array_key_exists ( 'critField', $_REQUEST ) and array_key_exists ( 'critValue', $_REQUEST )) {
    $critField = $_REQUEST ['critField'];
    $critValue = $_REQUEST ['critValue'];
    if (($dataType == 'idVersion' or $dataType == 'idProductVersion' or $dataType == 'idComponentVersion' or $dataType == 'idOriginalVersion' or $dataType == 'idOriginalProductVersion' or $dataType == 'idOriginalComponentVersion' or $dataType == 'idTargetVersion' or $dataType == 'idTargetProductVersion' or $dataType == 'idTargetComponentVersion') and ($critField == 'idProductOrComponent' or $critField == 'idComponent')) {
      $critField = 'idProduct';
    }
    $showIdle = false;
    if ($critField == 'idle' and $_REQUEST ['critValue'] == 'all') {
      $showIdle = true;
      $crit = array();
    } else if (property_exists ( $class, $critField ) or ($critField == 'idProjectSub' and property_exists ( $class, 'idProject' )) or ($class == 'Indicator' and $critField == 'idIndicatorable') or (($class == 'WarningDelayUnit' or $class == 'AlertDelayUnit') and $critField == 'idIndicator')) {
      $crit = array($critField => $_REQUEST ['critValue']);
    } else {
      $crit = array();
    }
    if (isset ( $_REQUEST ['critField1'] ) and isset ( $_REQUEST ['critValue1'] )) {
      if ($_REQUEST ['critField1'] == 'idle' and $_REQUEST ['critValue1'] == 'all') {
        $showIdle = true;
      } else {
        $crit [$_REQUEST ['critField1']] = $_REQUEST ['critValue1'];
      }
    }
    $limitPlanning = Parameter::getGlobalParameter ( 'limitPlanningActivity' );
    if ($class == "Activity" and $limitPlanning == "YES") {
      $object = SqlElement::getCurrentObject ( null, null, true, false );
      if ($object and get_class ( $object ) == "Ticket") {
        $crit ['isPlanningActivity'] = 1;
      }
    }
    // ADD qCazelles
    if (($dataType=='idProductVersion' or $dataType=='idTargetProductVersion' or $dataType=='idOriginalProductVersion') and $critField=='idProject') {
    //if ($dataType == 'idProductVersion' and $critField == 'idProject') {
      // if (Parameter::getGlobalParameter('sortingLists')=='YES') {
      // $versionProject=new VersionProject();
      // $list=$versionProject->getVersionsFromProject($critValue);
      // } else {
      $proj = new Project ( $critValue );
      $listProjs = $proj->getRecursiveSubProjectsFlatList ( false, true );
      $clauseWhere = 'idProject in (0';
      foreach ( $listProjs as $idProj => $nameProj ) {
        if ($idProj) $clauseWhere .= ", $idProj";
      }
      $clauseWhere .= ')';
      $versionProject = new VersionProject ();
      $listVersionProjects = $versionProject->getSqlElementsFromCriteria ( null, false, $clauseWhere );
      $crit = array('id' => array());
      if (count ($listVersionProjects)==0 ) $crit ['id'] []=0;
      foreach ( $listVersionProjects as $versionProject ) {
        $crit ['id'] [] = $versionProject->idVersion;
      }
      $list = SqlList::getListWithCrit ( $class, $crit, 'name', null, $showIdle );
      // }
    } else {
      // END ADD qCazelles
      $list = SqlList::getListWithCrit ( $class, $crit, 'name', null, $showIdle );
      if ($dataType=='idDocumentVersion') {
        uasort($list, 'version_compare');
      }
      // Begin add gmartin /handle emailTemplates Ticket #157 - FIXED PBE
      if ($dataType=='idEmailTemplate' and isset ( $_REQUEST ['critField'] ) and isset ( $_REQUEST ['critValue'] ) ) {
        $list = array_merge_preserve_keys ( $list, SqlList::getListWithCrit ( $class, array($_REQUEST ['critField']=>null), 'name', null, false ) );
        if (isset ( $_REQUEST ['critField1'] ) and isset ( $_REQUEST ['critValue1'] ) ) {
          $list = array_merge_preserve_keys ( $list, SqlList::getListWithCrit ( $class, array($_REQUEST ['critField']=>$_REQUEST ['critValue'],$_REQUEST ['critField1']=>null), 'name', null, false ) );
        }
      }
      if ($dataType=='idBrand' && $critField=='idAssetType') {
        $brandsOfModels=SqlList::getListWithCrit('Model', array($critField=>$critValue),'idBrand');
        foreach($list as $id=>$val) {
          if (! in_array($id, $brandsOfModels) ) {
            unset($list[$id]);
          }
        }
      }
      if($class == 'Activity'){
        $object = SqlElement::getCurrentObject ( null, null, true, false );
        if($object and (get_class($object)=='Activity' or get_class($object)=='TestSession' or get_class($object)=='Milestone')) {
          $activityTypeList = "(".implode(',' ,SqlList::getListWithCrit('ActivityType', array('canHaveSubActivity'=>'1', 'idle'=>'0'),'id')).")";
          if ($activityTypeList=='()') $activityTypeList='(0)';
          $activity = new Activity();
          $critWhere = "idActivityType in $activityTypeList";
          if ($critField=='idProject' and $critValue=='*') { 
            // nothing 
          } else if ($critField and $critValue) {
            $critWhere .= " and $critField = $critValue";
          }
          $activityList = $activity->getSqlElementsFromCriteria(null,null,$critWhere,null,null, true);
          //if(count($activityList)>0)unset($table);
          $tableForType=array();
          foreach ($activityList as $id=>$act){
            $tableForType[$act->id]=$act->name;
          }
          $list=array_intersect_key($list, $tableForType);
        }
      }
      if ($class=='Activity' or $class=='Ticket') {
      	foreach ($list as $idL=>$valL) {
      		$list[$idL]=SqlList::formatValWithId($idL,$valL);
      	}
      }
      // end add gmartin - FIXED PBE
    }
  } else {
    $list = SqlList::getList ( $class );
    // ADD BY Marc TABARY - 2017-02-22 - RESOURCE VISIBILITY (list teamOrga)
    // Special case for idResource, idLocker, idResponsive
    // Don't see or access to the resource if is not visible for the user connected (respect of HabilitationOther - teamOrga)
    if ($class == 'Resource') {
      $listVisibleRes = getUserVisibleResourcesList ( true );
      $list = array_intersect_key ( $list, $listVisibleRes );
    }
    // ADD BY Marc TABARY - 2017-02-22 - RESOURCE VISIBILITY (list teamOrga)
// MTY - LEAVE SYSTEM
    if ($class == 'Project' and isLeavesSystemActiv()) {
        if (!Project::isProjectLeaveVisible()) {
            $idLeavePrj = Project::getLeaveProjectId();
            unset($list[$idLeavePrj]);
        }
    }
// MTY - LEAVE SYSTEM
  }
  if ($critField == 'scope' and $dataType = 'idEventForMail') {
    if (SqlElement::class_exists ( $critValue )) {
      // $objVal=new $critVal();
      if (! property_exists ( $critValue, 'idResource' )) {
        unset ( $list [1] ); // 1 responsibleChange
      }
      if (! property_exists ( $critValue, '_Note' )) {
        unset ( $list [2] ); // 2 noteAdd
        unset ( $list [4] ); // 4 noteChange
      }
      if (! property_exists ( $critValue, '_Link' )) {
        unset ( $list [12] ); // 12 linkAdd
        unset ( $list [13] ); // 13 linkDelete
      }
      if (! property_exists ( $critValue, '_Attachment' )) {
        unset ( $list [3] ); // 3 attachmentAdd
      }
      if ($critValue != 'Project') {
        unset ( $list [10] ); // 10 affectationAdd
        unset ( $list [11] ); // 11 affectationChange
      }
      if (! property_exists ( $critValue, '_Assignment' )) {
        unset ( $list [7] ); // 7 assignmentAdd
        unset ( $list [8] ); // 8 assignmentChange
      }
      if (! property_exists ( $critValue, 'description' )) {
        unset ( $list [5] ); // 5 descriptionChange
      }
      if (! property_exists ( $critValue, 'result' )) {
        unset ( $list [6] ); // 6 resultChange
      }
      /* 9 anyChange */
    }
  }
  /*
   * if ($dataType=='idResource') {
   * $scope=Affectable::getVisibilityScope();
   * if ($scope!="all") {
   * $list=array();
   * $res=new Resource();
   * if ($scope=='orga') {
   * $crit="idOrganization in (". Organization::getUserOrganisationList().")";
   * } else if ($scope=='orga') {
   * $crit="idOrganization in (". Organization::getUserOrganisationList().")";
   * } else if ($scope=='team') {
   * $aff=new Affectable(getSessionUser()->id,true);
   * $crit="idTeam='$aff->idTeam'";
   * } else {
   * traceLog("Error on htmlDrawOptionForReference() : Resource::getVisibilityScope returned something different from 'all', 'team', 'orga'");
   * $crit=array('id'=>'0');
   * }
   * $listRestrict=$res->getSqlElementsFromCriteria(null,false,$crit);
   * foreach ($listRestrict as $res) {
   * $list[$res->id]=$res->name;
   * }
   * asort($list);
   * }
   * $list = getUserVisibleResourcesList(true); // Try ?
   * }
   */
  if ($dataType=='idTargetProductVersion' or $dataType=='idProductVersion' or $dataType=='idOriginProductVersion') {
    $objectClass=RequestHandler::getValue('objectClass',false,null);
    if ($objectClass!='ProjectVersion') {
      // Must restrict to versions visible to user
      $restrictArray=getSessionUser()->getVisibleVersions();
      $list=array_intersect_key($list, $restrictArray);
    }
  }
  if ($dataType=='idProduct') {
    // Must restrict to products visible to user
    $restrictArray=getSessionUser()->getVisibleProducts();
    $list=array_intersect_key($list, $restrictArray);
  }
  if ($selected) {
    $selectList=explode('_',$selected);
    foreach($selectList as $sel) {
      $name = SqlList::getNameFromId ( $class, $sel );
      if ($name == $sel and ($class == 'Resource' or $class == 'User' or $class == 'Contact')) {
        $name = SqlList::getNameFromId ( 'Affectable', $sel );
      }
      if ($name == $sel and substr ( $class, - 7 ) == 'Version' and SqlElement::is_a ( $class, 'Version' )) {
        $name = SqlList::getNameFromId ( 'Version', $sel );
      }
      if ($class=='Activity' or $class=='Ticket') {
        $list [$sel] = SqlList::formatValWithId($sel,$name);
      } else {
        $list [$sel] = $name;
      }
    }
  }
  if ($dataType == "idProject" or $dataType == 'planning') {
    $wbsList = SqlList::getList ( 'Project', 'sortOrder', $selected, true );
  }
  $nbRows = 0;
  // return result in json format
  if (! array_key_exists ( 'required', $_REQUEST ) and ! isset ( $_REQUEST ['critArray'] )) {
    if ($dataType == 'planning')
      echo '{id:" ", name:"' . i18n ( "allProjects" ) . '"}';
    else
      echo '{id:" ", name:""}';
    $nbRows += 1;
  }
  if ($dataType == "idProject" or $dataType == 'planning') {
    $sepChar = Parameter::getUserParameter ( 'projectIndentChar' );
    if (! $sepChar) $sepChar = '__';
    $wbsLevelArray = array();
  }
  if ($class=='Mailable' or $class=='Indicatorable' or $class=='Textable' or $class=='Checklistable' 
      or $class=='Linkable' or $class=='Copyable' or $class=='Dependable' or $class=='Originable'
      or $class=='Importable' or $class=='Notifiable' or $class=='Dependable' or $class=='Originable' or $class=='Situationable'){
    $temp=SqlList::getListNotTranslated($class,'name',$selected);
    foreach($temp as $key => $val){
      $checkMenu='menu'.$val;
      if ($val=='Assignment') $checkMenu='menuActivity';
      else if ($val=='TestCaseRun') $checkMenu='menuTestCase';
      else if ($val=='ProductStructure') $checkMenu='menuProduct';
      else if ($val=='Work') $checkMenu='menuImputation';
      else if ($val=='DocumentVersion') $checkMenu='menuDocument';
      if (! Module::isMenuActive($checkMenu)) unset($list[$key]);
      if ($class=='Linkable' or $class=='Copyable' or $class=='Dependable' or $class=='Originable') {
        $typeRight='read';
        if($class=='Copyable') $typeRight='create';
        $objTmp=new $val();
        if(securityGetAccessRightYesNo('menu'.$val, $typeRight, $objTmp)=="NO" or !securityCheckDisplayMenu(null,$val)) unset($list[$key]);
      }
    }
  }  
  if ($dataType == 'idLinkable' or $dataType == 'idCopyable' or $dataType == 'idImportable' or $dataType == 'idMailable' or $dataType == 'idIndicatorable' or $dataType == 'idChecklistable' or $dataType == 'idDependable' or $dataType == 'idOriginable' or $dataType == 'idReferencable' or $dataType == 'idNotifiable'  ) {
    asort ( $list );
  }
  $pluginObjectClass = substr ( $dataType, 2 );
  $table = $list;
  $lstPluginEvt = Plugin::getEventScripts ( 'list', $pluginObjectClass );
  foreach ( $lstPluginEvt as $script ) {
    require $script; // execute code
  }
  foreach ( $table as $id => $name ) {
    if (($dataType == "idProject" or $dataType == 'planning') and $sepChar != 'no') {
      if (isset ( $wbsList [$id] )) {
        $wbs = $wbsList [$id];
      } else {
        $wbsProj = new Project ( $id );
        $wbs = $wbsProj->sortOrder;
      }
      $wbsTest = $wbs;
      $level = 1;
      while ( strlen ( $wbsTest ) > 3 ) {
        $wbsTest = substr ( $wbsTest, 0, strlen ( $wbsTest ) - 6 );
        if (array_key_exists ( $wbsTest, $wbsLevelArray )) {
          $level = $wbsLevelArray [$wbsTest] + 1;
          $wbsTest = "";
        }
      }
      $wbsLevelArray [$wbs] = $level;
      $sep = '';
      for($i = 1; $i < $level; $i ++) {
        $sep .= $sepChar;
      }
      // $levelWidth = ($level-1) * 2;
      // $sep=($levelWidth==0)?'':substr('_____________________________________________________',(-1)*($levelWidth));
      $name = $sep . $name;
    }
    if ($nbRows > 0)
      echo ', ';
    echo '{id:"' . htmlEncodeJson ( $id ) . '", name:"' . htmlEncodeJson ( $name ) . '"}';
    $nbRows += 1;
  }
} else if ($type == 'listResourceProject') { // ====================================================== LISTRESOURCEPROJECT ===================================================
  $idPrj = $_REQUEST ['idProject'];
  $arrayPrj=explode(',',$idPrj);
  $lstTopPrj=array();
  foreach($arrayPrj as $idPrj) {
    $prj = new Project ( $idPrj );
    $lstTopPrj = array_merge($lstTopPrj,$prj->getTopProjectList ( true ));
  }
  $today = date ( 'Y-m-d' );
  $in = transformValueListIntoInClause ( $lstTopPrj );
  $where = "idle=0 and idProject in " . $in;
  // .$where.=" and (startDate is null or startDate<='$today')";
  $where .= " and (endDate is null or endDate>='$today')";
  if (isset ( $_REQUEST ['objectClass'] ) and $_REQUEST ['objectClass'] == 'IndividualExpense') {
    if (securityGetAccessRight ( 'menuIndividualExpense', 'read', null, getSessionUser () ) == 'OWN') {
      $where .= " and idResource=" . Sql::fmtId ( getSessionUser ()->id );
    }
  }
  $aff = new Affectation ();
  $list = $aff->getSqlElementsFromCriteria ( null, null, $where );
  $nbRows = 0;
  $lstRes = array();
  if (array_key_exists ( 'selected', $_REQUEST )) {
    $lstRes [$_REQUEST ['selected']] = SqlList::getNameFromId ( 'Affectable', $_REQUEST ['selected'] );
  }
  // CHANGE BY Marc TABARY - 2017-02-21 - GENERIC FUNCTION IN PROJEQTOR.PHP
  $restrictArray = getUserVisibleResourcesList (true);
  // Old
  // $restrictArray=array();
  // END CHANGE BY Marc TABARY - 2017-02-21 - GENERIC FUNCTION IN PROJEQTOR.PHP
  // COMMENT BY Marc TABARY - 2017-02-21 - GENEREIC FUNCTION IN PROJEQTOR.PHP
  // $scope=Affectable::getVisibilityScope();
  // if ($scope!="all") {
  // $res=new Resource();
  // if ($scope=='orga') {
  // $crit="idOrganization in (". Organization::getUserOrganisationList().")";
  // } else if ($scope=='team') {
  // $aff=new Affectable(getSessionUser()->id,true);
  // $crit="idTeam='$aff->idTeam'";
  // } else {
  // traceLog("Error on htmlDrawOptionForReference() : Resource::getVisibilityScope returned something different from 'all', 'team', 'orga'");
  // $crit=array('id'=>'0');
  // }
  // $listRestrict=$res->getSqlElementsFromCriteria(null,false,$crit);
  // foreach ($listRestrict as $res) {
  // $restrictArray[$res->id]=$res->name;
  // }
  // }
  // END COMMENT BY Marc TABARY - 2017-02-21 - GENEREIC FUNCTION IN PROJEQTOR.PHP
  foreach ( $list as $aff ) {
    if (! array_key_exists ( $aff->idResource, $lstRes )) {
      $id = $aff->idResource;
      $name = SqlList::getNameFromId ( 'Resource', $id );
      if ($name != $id) {
        // COMMENT BY Marc TABARY - 2017-03-07 - BUG Undefined variable: scope
        // Not needed because now
        // - $scope is retrieve in getUserVisibleResourceList
        // - $restrictArray is set by getUserVisibleResourceList
        // if ($scope=="all" or isset($restrictArray[$id])) {
        // $lstRes[$id]=$name;
        // }
        // END COMMENT BY Marc TABARY - 2017-03-07 - BUG Undefined variable: scope
        // ADD BY Marc TABARY - 2017-03-07 - BUG Undefined variable: scope
        // Apply realy the restriction
        if (array_key_exists ( $aff->idResource, $restrictArray )) {
          $lstRes [$id] = $name;
        }
        // END ADD BY Marc TABARY - 2017-03-07 - BUG Undefined variable: scope
      }
    }
  }
  $pluginObjectClass = 'Affectable';
  $table = $lstRes;
  $lstPluginEvt = Plugin::getEventScripts ( 'list', $pluginObjectClass );
  foreach ( $lstPluginEvt as $script ) {
    require $script; // execute code
  }
  asort ( $table );
  // return result in json format
  if (! $required) {
    echo '{id:" ", name:""}';
    $nbRows += 1;
  }
  foreach ( $table as $id => $name ) {
    if ($nbRows > 0)
      echo ', ';
    echo '{id:"' . htmlEncodeJson ( $id ) . '", name:"' . htmlEncodeJson ( $name ) . '"}';
    $nbRows += 1;
  }
} else if ($type == 'listTermProject') {
  if (! isset ( $_REQUEST ['selected'] )) {
    $obj = SqlElement::getCurrentObject ( null, null, false, false ); // V5.2
    $idPrj = $_REQUEST ['idProject'];
    $prj = new Project ( $obj->idProject );
    $lstTopPrj = $prj->getTopProjectList ( true );
    $in = transformValueListIntoInClause ( $lstTopPrj );
    $where = "idProject in " . $in . " AND idBill is null";
    $term = new Term ();
    $list = $term->getSqlElementsFromCriteria ( null, null, $where );
    $listFinal = array();
    foreach ( $list as $term ) {
      // we get triggers
      $dep = new Dependency ();
      $crit = array("successorRefType" => "Term", "successorRefId" => $term->id);
      $depList = $dep->getSqlElementsFromCriteria ( $crit, false );
      $idle = 1;
      foreach ( $depList as $dep ) {
        switch ($dep->predecessorRefType) {
          case "Activity" :
            // $act = new Activity($dep->predecessorRefId);
            // if ($act->idle == 0) $idle = 0;
            break;
          case "Milestone" :
            $mil = new Milestone ( $dep->predecessorRefId );
            if ($mil->idle == 0)
              $idle = 0;
            break;
          case "Project" :
            // $project = new Project($dep->predecessorRefId);
            // if ($project->idle == 0) $idle = 0;
            break;
        }
      }
      // if all triggers are closed, so add term to list
      if ($idle == 1) {
        if ($term->date != null) {
          $now = date ( 'Y-m-d' );
          $now = new DateTime ( $now );
          $now = $now->format ( 'Y-m-d' );
          if ($now >= $term->date) {
            $listFinal [$term->id] = $term;
          }
        } else {
          $listFinal [$term->id] = $term;
        }
      }
    }
    foreach ( $listFinal as $term ) {
      if (! array_key_exists ( $term->id, $listFinal )) {
        $listFinal [$term->id] = SqlList::getNameFromId ( 'Term', $term->id );
      }
    }
    
    asort ( $listFinal );
    // return result in json format
    echo '{id:null, name:""}';
    // $i=0;
    foreach ( $listFinal as $term ) {
      // if($i!=0)
      echo ', ';
      echo '{id:"' . $term->id . '", name:"' . $term->name . '"}';
      // $i++;
    }
  } else {
    echo '{id:"' . htmlEncodeJson ( $_REQUEST ['selected'] ) . '", name:"' . htmlEncodeJson ( SqlList::getNameFromId ( 'Term', $_REQUEST ['selected'] ) ) . '"}';
  }
} else if ($type == 'listRoleResource') {
  $ctrl = "";
  $idR = $_REQUEST ['idResource'];
  $resource = new ResourceAll( $idR );
  $nbRows = 0;
  if($resource->isResourceTeam){
    $role = new Role();
    $lstRoles = $role->getSqlElementsFromCriteria(array('idle'=>'0'));
    foreach ($lstRoles as $rol){
      if ($nbRows > 0)
        echo ', ';
      echo '{id:"' . $rol->id . '", name:"' . $rol->name . '"}';
      $nbRows += 1;
      $ctrl .= '#' . $rol->id . '#';
    }
  }else{
    if ($resource->idRole) {
      echo '{id:"' . $resource->idRole . '", name:"' . SqlList::getNameFromId ( 'Role', $resource->idRole ) . '"}';
      $nbRows += 1;
      $ctrl .= '#' . $resource->idRole . '#';
    }
    
    $where = "idResource='" . Sql::fmtId ( $idR ) . "' and endDate is null";
    $where .= " and idRole <>'" . Sql::fmtId ( $resource->idRole ) . "'";
    $rc = new ResourceCost ();
    $lstRoles = $rc->getSqlElementsFromCriteria ( null, false, $where );
    // return result in json format
    foreach ( $lstRoles as $resourceCost ) {
      $key = '#' . $resource->idRole . '#';
      //if (strpos ( $ctrl, $key ) === false) {
        if ($nbRows > 0)
          echo ', ';
        echo '{id:"' . $resourceCost->idRole . '", name:"' . SqlList::getNameFromId ( 'Role', $resourceCost->idRole ) . '"}';
        $nbRows += 1;
        $ctrl .= $key;
      //}
    }
  }
}else if($type=="idWorkUnit"){
  $idWorkUnit = RequestHandler::getId('idWorkUnit');
  $complexityVal = new ComplexityValues();
  $where = " (idWorkUnit = ".$idWorkUnit.") and  (price IS NOT NULL or charge IS NOT NULL) ";
  $listComplexityValues = $complexityVal->getSqlElementsFromCriteria(null,false,$where);
  $tabComplexityValues = array();
  foreach ($listComplexityValues as $val){
    $complexity = new Complexity($val->idComplexity);
    $tabComplexityValues[$complexity->id]=$complexity->name;
  }
   ksort($tabComplexityValues);
   echo '{id:"", name:""},';
  foreach ($tabComplexityValues as $id=>$name){
    echo '{id:"'.$id.'", name:"'.$name.'"},';
  }
} else if ($type == 'listStatusDocumentVersion') {
  $doc = SqlElement::getCurrentObject ( null, null, false, false ); // V5.2
  $idDocumentVersion = $_REQUEST ['idDocumentVersion'];
  $docVers = new documentVersion ( $idDocumentVersion );
  $table = SqlList::getList ( 'Status', 'name', $docVers->idStatus );
  if ($doc and $docVers->idStatus) {
    $profile = getSessionUser ()->getProfile ( $doc );
    $type = new DocumentType ( $doc->idDocumentType );
    $ws = new WorkflowStatus ();
    $crit = array('idWorkflow' => $type->idWorkflow, 'allowed' => 1, 'idProfile' => $profile, 'idStatusFrom' => $docVers->idStatus);
    $wsList = $ws->getSqlElementsFromCriteria ( $crit, false );
    $compTable = array($docVers->idStatus => 'ok');
    foreach ( $wsList as $ws ) {
      $compTable [$ws->idStatusTo] = "ok";
    }
    // Ticket #3417Ticket #3417
    $currentStatus=new Status($docVers->idStatus);
    if ($currentStatus->isCopyStatus) {
      $listAll=SqlList::getList('Status','id');
      $compTable[reset($listAll)]='ok';
    }
    $table = array_intersect_key ( $table, $compTable );
  } else {
    reset ( $table );
    $table = array(key ( $table ) => current ( $table ));
  }
  $nbRows = 0;
  foreach ( $table as $id => $name ) {
    if ($nbRows > 0)
      echo ', ';
    echo '{id:"' . $id . '", name:"' . $name . '"}';
    $nbRows += 1;
  }
} 
echo ' ] }';

function listFieldsForFilter($obj, $nbRows, $included = false) {
  // return result in json format
  global $contextForAttributes;
  $contextForAttributes='global';
  if (method_exists($obj,'setAttributes')) $obj->setAttributes();
  foreach ( $obj as $col => $val ) {
    if (get_class($obj)=='GlobalView' and $col=='id') continue;
    if ($col=='_Assignment') {
      if ($nbRows > 0) echo ', ';
      echo '{"id":"' . ($included ? get_class ( $obj ) . '_' : '') . 'assignedResource__idResourceAll' . '", "name":"' . i18n("assignedResource") . '", "dataType":"list"}';
      continue;
    }
    if (substr ( $col, 0, 1 ) != "_" and substr ( $col, 0, 1 ) != ucfirst ( substr ( $col, 0, 1 ) ) and ! $obj->isAttributeSetToField ( $col, 'hidden' ) and ! $obj->isAttributeSetToField ( $col, 'calculated' ) and 
    // ADD BY Marc TABARY - 2017-03-20 - FIELD NOT PRESENT FOR FILTER
    ! $obj->isAttributeSetToField ( $col, 'notInFilter' ) and 
    // END ADD BY Marc TABARY - 2017-03-20 - FIELD NOT PRESENT FOR FILTER
    (! $included or ($col != 'id' and $col != 'refType' and $col != 'refId' and $col != 'idle'))) {
      if ($nbRows > 0)
        echo ', ';
      $dataType = $obj->getDataType ( $col );
      $dataLength = $obj->getDataLength ( $col );
      if ($dataType == 'int' and $dataLength == 1) {
        $dataType = 'bool';
      } else if ($dataType == 'datetime') {
        $dataType = 'date';
      //} else if ((substr ( $col, 0, 2 ) == 'id' and $dataType == 'int' and strlen ( $col ) > 2 and substr ( $col, 2, 1 ) == strtoupper ( substr ( $col, 2, 1 ) ))) {
      } else if (isForeignKey($col, $obj)) {
        $dataType = 'list';
      }
      $colName = $obj->getColCaption ( $col );
      if (substr ( $col, 0, 9 ) == 'idContext') {
        $colName = SqlList::getNameFromId ( 'ContextType', substr ( $col, 9 ) );
      }
      echo '{"id":"' . ($included ? get_class ( $obj ) . '_' : '') . $col . '", "name":"' . $colName . '", "dataType":"' . $dataType . '"}';
      $nbRows ++;
    } else if (substr ( $col, 0, 1 ) != "_" and substr ( $col, 0, 1 ) == ucfirst ( substr ( $col, 0, 1 ) )) {
      $sub = new $col ();
      $nbRows = listFieldsForFilter ( $sub, $nbRows, true );
    }
  }
  if (isset ( $obj->_Note )) {
    if ($nbRows > 0)
      echo ', ';
    echo '{"id":"Note", "name":"' . i18n ( 'colNote' ) . '", "dataType":"refObject"}';
    $nbRows ++;
  }
  return $nbRows;
}
?>