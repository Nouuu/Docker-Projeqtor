<?PHP
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

/** ===========================================================================
 * Get the list of objects, in Json format, to display the grid list
 */
    require_once "../tool/projeqtor.php";
    require_once "../tool/formatter.php";
    require_once "../tool/jsonFunctions.php";
    scriptLog('   ->/tool/jsonQuery.php'); 
    $objectClass=$_REQUEST['objectClass'];
	  Security::checkValidClass($objectClass);
	  Security::checkValidAccessForUser(null, 'read', $objectClass);
	  
    $showThumb=Parameter::getUserParameter('paramShowThumbList');
    if ($showThumb=='NO') {
      $showThumb=false;
    } else {
      $showThumb=true;
    }
    
    $hiddenFields=array();
    if (isset($_REQUEST['hiddenFields'])) {
    	$hiddens=explode(';',$_REQUEST['hiddenFields']);
    	foreach ($hiddens as $hidden) {
    		if (trim($hidden)) {
    			$hiddenFields[$hidden]=$hidden;
    		}
    	}
    }
    $print=false;
    if ( array_key_exists('print',$_REQUEST) ) {
      $print=true;
      include_once('../tool/formatter.php');
    }
    $comboDetail=false;
    if ( array_key_exists('comboDetail',$_REQUEST) ) {
      $comboDetail=true;
    }
    $showAllProjects=false;
    if (RequestHandler::isCodeSet('showAllProjects') and RequestHandler::getBoolean('showAllProjects')==true) {
      $showAllProjects=true;
    }
    
    $quickSearch=false;
    if ( array_key_exists('quickSearch',$_REQUEST) ) {
      $quickSearch=Sql::fmtStr($_REQUEST['quickSearch']);
    }
    if ( array_key_exists('quickSearchQuick',$_REQUEST) ) {
      $quickSearch=Sql::fmtStr($_REQUEST['quickSearchQuick']);
    }
    if (! isset($outMode)) { $outMode=""; } 
    if (! isset($csvExportAll)) $csvExportAll=false;
    
    if ($print && $outMode=='csv') {
      global $contextForAttributes;
      $contextForAttributes='global';
    }
    
    $obj=new $objectClass();
    $table=$obj->getDatabaseTableName();
    $accessRightRead=securityGetAccessRight($obj->getMenuClass(), 'read');  
    $querySelect = '';
    $queryFrom=($objectClass=='GlobalView')?GlobalView::getTableNameQuery().' as '.$table:$table;
    $queryWhere='';
    $queryOrderBy='';
    $idTab=0;
    
    $res=array();
    $layout=$obj->getLayout();
    $array=explode('</th>',$layout);
    
    

    // ====================== Build restriction clauses ================================================
    
    // --- Quick search criteria (textual search in any text field, including notes)
    if ($quickSearch) {
      $quickSearch=str_replace(array('*','.'),array('%','_'),$quickSearch);
    	$queryWhere.= ($queryWhere=='')?'':' and ';
    	$queryWhere.="( 1=2 ";
    	$note=new Note();
    	$noteTable=$note->getDatabaseTableName();
    	foreach($obj as $fld=>$val) {
    	  if ($fld=='id' or $fld=='objectId' or $fld=='objectClass' or $fld=='refType' or $fld=='refId') continue;
    	  if ($obj->getDataType($fld)=='varchar') {    				
            $queryWhere.=' or '.$table.".".$obj->getDatabaseColumnName($fld)." ".((Sql::isMysql())?'LIKE':'ILIKE')." '%".$quickSearch."%'";
    	  }
    	}
    	if (is_numeric($quickSearch)) {
    		$queryWhere.= ' or ' . $table . ".id=" . $quickSearch . "";
    	}
    	$queryWhere.=" or exists ( select 'x' from $noteTable ";
    	if ($objectClass=='GlobalView') {
    	  $queryWhere.=" where $noteTable.refId=$table.objectId ";
    	  $queryWhere.=" and $noteTable.refType=$table.objectClass ";
    	} else {
    	  $queryWhere.=" where $noteTable.refId=$table.id ";
    	  $queryWhere.=" and $noteTable.refType=".Sql::str($objectClass);
    	} 
      $queryWhere.=" and $noteTable.note ".((Sql::isMysql())?'LIKE':'ILIKE')." '%" . $quickSearch . "%' ) ";
    	$queryWhere.=" )";
    }
    
    // --- Should idle projects be shown ?
    $showIdleProjects=(! $comboDetail and sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1)?1:0;
    // --- "show idle checkbox is checked ?
    if (! isset($showIdle)) $showIdle=false;
    if($objectClass=='Work'){
      $showIdle = true;
    }
    if (getSessionValue("listShowIdle$objectClass",'off')=='on') $showIdle=true;
    if (!$showIdle and ! array_key_exists('idle',$_REQUEST) and ! $quickSearch) {
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.= $table . "." . $obj->getDatabaseColumnName('idle') . "=0";
    } else {
      $showIdle=true;
    }
    // For versions, hide versions in service
    $hideInService=Parameter::getUserParameter('hideInService');
    if (Parameter::getUserParameter('hideInService')=='true' and property_exists($obj, 'isEis') and ! $quickSearch) {
    	$queryWhere.= ($queryWhere=='')?'':' and ';
    	$queryWhere.= $table . "." . $obj->getDatabaseColumnName('isEis') . "=0";
    } else {
    	$showIdle=true;
    }
    
    // --- Direct filter on id (only used for printing, as direct filter is done on client side)
    if (array_key_exists('listIdFilter',$_REQUEST)  and ! $quickSearch) {
      $param=$_REQUEST['listIdFilter'];
      $param=strtr($param,"*?","%_");
      $param=Sql::fmtStr($param);
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.=$table.".".$obj->getDatabaseColumnName('id')." like '%".$param."%'";
    }
    // --- Direct filter on name (only used for printing, as direct filter is done on client side)
    if (array_key_exists('listNameFilter',$_REQUEST)  and ! $quickSearch) {
      $param=$_REQUEST['listNameFilter'];
      $param=strtr($param,"*?","%_");
      $param=Sql::fmtStr($param);
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.=$table.".".$obj->getDatabaseColumnName('name')." ".((Sql::isMysql())?'LIKE':'ILIKE')." '%".$param."%'";
    }
    // --- Direct filter on type 
    if ( array_key_exists('objectType',$_REQUEST)  and ! $quickSearch) {
      if (trim($_REQUEST['objectType'])!='') {
        $queryWhere.= ($queryWhere=='')?'':' and ';
// MTY - LEAVE SYSTEM        
        if ($objectClass=="EmployeeLeaveEarned") {
          $queryWhere.= $table . "." . $obj->getDatabaseColumnName('idLeaveType') . "=" . Sql::str($_REQUEST['objectType']);
        } else {        
          $queryWhere.= $table . "." . $obj->getDatabaseColumnName('id' . $objectClass . 'Type') . "=" . Sql::str($_REQUEST['objectType']);
        }
// MTY - LEAVE SYSTEM        
      }
      //ADD - Activity type restriction by project - F.KARA #459
      if(substr($objectClass, -4) == 'Type' and $comboDetail){
          $idProjectSelected= getSessionValue("idProjectSelectedForComboDetail");
          $restrictType = new RestrictType();
          $restrictTypeActivity = $restrictType->getSqlElementsFromCriteria(array('idProject' => $idProjectSelected));
          $tabRestrictedTypeId = array();
          foreach ($restrictTypeActivity as $rest) {
              array_push($tabRestrictedTypeId,$rest->idType);
          }
          if(count($tabRestrictedTypeId) != 0) {
              $queryWhere.= ' and type.id in ' . transformValueListIntoInClause($tabRestrictedTypeId);
          }
      }
      //END - Activity type restriction by project - F.KARA #459
    }
    // --- Direct filter on client
    if ( array_key_exists('objectClient',$_REQUEST)  and ! $quickSearch) {
      if (trim($_REQUEST['objectClient'])!='' and property_exists($obj, 'idClient')) {
        $queryWhere.= ($queryWhere=='')?'':' and ';
        $queryWhere.= "(" . $table . "." . $obj->getDatabaseColumnName('idClient') . "=" . Sql::str($_REQUEST['objectClient']);
        if (property_exists($obj, '_OtherClient')) {
          $otherclient=new OtherClient();
          $queryWhere.=" or exists (select 'x' from ".$otherclient->getDatabaseTableName()." other "
              ." where other.refType=".Sql::str($objectClass)." and other.refId=".$table.".id and other.idClient=".Sql::fmtId(RequestHandler::getId('objectClient'))
              .")";
        }
        $queryWhere.=")";
      }
    }
    // --- Direct filter on elementable
    if ( array_key_exists('objectElementable',$_REQUEST)  and ! $quickSearch) {
      if (trim($_REQUEST['objectElementable'])!='') {
        $elementable=null;
        if ( property_exists($obj,'idMailable') ) $elementable='idMailable';
        else if (property_exists($obj,'idIndicatorable')) $elementable='idIndicatorable';
        else if (property_exists($obj,'idTextable')) $elementable='idTextable';
        else if ( property_exists($obj,'idChecklistable')) $elementable='idChecklistable';
        else if ( property_exists($obj,'idSituationable')) $elementable='idSituationable';
        if ($elementable) {
          $queryWhere.= ($queryWhere=='')?'':' and ';
          $queryWhere.= $table . "." . $obj->getDatabaseColumnName($elementable) . "=" . Sql::str($_REQUEST['objectElementable']);
        }
      }
    }
    //ADD qCazelles - Filter by Status
    // --- Direct filter on status
    if ( array_key_exists('countStatus',$_REQUEST) and property_exists($obj, 'idStatus') and !$quickSearch) {
      $queryWhere .= ($queryWhere=='')?'':' and ';
    	$queryWhere .= $table.'.'.$obj->getDatabaseColumnName('idStatus').' in (0';
    	for ($i = 1; $i <= $_REQUEST['countStatus']; $i++) {
    		if ( array_key_exists('objectStatus'.$i,$_REQUEST) and trim($_REQUEST['objectStatus'.$i])!='') {
    			$queryWhere.= ', '.Sql::str($_REQUEST['objectStatus'.$i]);
    		}
    	}
    	$queryWhere.=')';
    }
    //END ADD qCazelles
// MTY - LEAVE SYSTEM
    // Don't take the Leave Project if it's not visible for the connected user
    if (isLeavesSystemActiv()) {
        if ($objectClass=='Project' and !Project::isProjectLeaveVisible()) {
            $queryWhere.= ($queryWhere=='')?'':' and ';
            $queryWhere.= $table . ".isLeaveMngProject = 0 ";
        }
    }
// MTY - LEAVE SYSTEM
    // --- Restrict to allowed projects : for Projects list
    if ($objectClass=='Project' and $accessRightRead!='ALL') {
        $accessRightRead='ALL';
        $queryWhere.= ($queryWhere=='')?'':' and ';
        $queryWhere.=  '(' . $table . ".id in " . transformListIntoInClause(getSessionUser()->getVisibleProjects(! $showIdle)) ;
        $queryWhere.= " or $table.codeType='TMP' "; // Templates projects are always visible in projects list
        $queryWhere.= ')';
    }  
    // --- Restrict to allowed project taking into account selected project : for all list that are project dependant
    if (property_exists($obj, 'idProject') and sessionValueExists('project')) {
// MTY - LEAVE SYSTEM
        // Don't take the Leave Project if it's not visible for the connected user
        if (isLeavesSystemActiv()) {
            if ($objectClass!='Project' && !Project::isProjectLeaveVisible() && Project::getLeaveProjectId() && trim(Project::getLeaveProjectId()) ) {
                $queryWhere.= ($queryWhere=='')?'':' and ';
                $queryWhere.= "($table.idProject <> " . Project::getLeaveProjectId() . " or $table.idProject is null)";
            }
        }
// MTY - LEAVE SYSTEM
        if (getSessionValue('project')!='*' and !$showAllProjects) {
          $queryWhere.= ($queryWhere=='')?'':' and ';
          if ($objectClass=='Project') {
            $queryWhere.=  $table . '.id in ' . getVisibleProjectsList(! $showIdleProjects) ;
          } else if ($objectClass=='Work') {
             $queryWhere.="1=1";
          } else if ($objectClass=='Document') {
            $app=new Approver();
            $appTable=$app->getDatabaseTableName();
            // Fix : do not systematically show documents where user is approver if project is selected
          	//$queryWhere.= "(" . $table . ".idProject in " . getVisibleProjectsList(! $showIdleProjects) . " or " . $table . ".idProject is null or exists (select 'x' from $appTable app where app.refType='Document' and app.refId=$table.id and app.idAffectable=$user->id ))";
            $queryWhere.= "(" . $table . ".idProject in " . getVisibleProjectsList(! $showIdleProjects) .")";
          } else if ($obj->isAttributeSetToField('idProject','required') ){
            $queryWhere.= $table . ".idProject in " . getVisibleProjectsList(! $showIdleProjects) ;
          } else {
            $queryWhere.= "($table.idProject in " . getVisibleProjectsList(! $showIdleProjects). " or $table.idProject is null)" ;
          }
        }
    }

    //Gautier #itemTypeRestriction
    if(Parameter::getGlobalParameter('hideItemTypeRestrictionOnProject')=='YES'){
      $lstGetClassList = Type::getClassList();
      $objType = $obj->getDatabaseColumnName($objectClass . 'Type');
      $lstGetClassList = array_flip($lstGetClassList);
      if(in_array($objType,$lstGetClassList)){
        $queryWhere.=($queryWhere)?' and ':'';
        $queryWhere.= $user->getItemTypeRestriction($obj,$objectClass,$user,$showIdle,$showIdleProjects);
      }
    }
    // --- Take into account restriction visibility clause depending on profile
    if ( ($objectClass=='Version' or $objectClass=='Resource') and $comboDetail) {
    	// No limit, although idProject exists
    } else {
      $clause=getAccesRestrictionClause($objectClass,$table, $showIdleProjects);
      //gautier #1700
      if (trim($clause) and $objectClass!="Work" and $objectClass!='GlobalView') {
        $queryWhere.= ($queryWhere=='')?'(':' and (';
        $queryWhere.= $clause;
        if ($objectClass=='Project') {
          $queryWhere.= " or $table.codeType='TMP' "; // Templates projects are always visible in projects list
        } else if ($objectClass=='Document' and getSessionValue('project')=='*' or $showAllProjects and strpos(getSessionValue('project'), ",") === null) {
          $app=new Approver();
          $appTable=$app->getDatabaseTableName();
          $queryWhere.= "or exists (select 'x' from $appTable app where app.refType='Document' and app.refId=$table.id and app.idAffectable=$user->id )";
        }
        $queryWhere.= ')';
      }
    }
    if ($objectClass=='Resource' or $objectClass=='ResourceTeam') {
      $scope=Affectable::getVisibilityScope('Screen');
      if ($scope!="all") {
        $queryWhere.= ($queryWhere=='')?'':' and ';
// ADD BY Marc TABARY - 2017-02-21 - RESOURCE VISIBILITY
          switch($scope) {
              case 'subOrga' :
                  $queryWhere.=" $table.idOrganization in (". Organization::getUserOrganizationList().")";
                  break;
              case 'orga' :
                  $queryWhere.=" $table.idOrganization = ". Organization::getUserOrganization();
                  break;
              case 'team' :
          $aff=new Affectable(getSessionUser()->id,true);
          $queryWhere.=" $table.idTeam='$aff->idTeam'";
                  break;
              default:
                  break;
        }
// END ADD BY Marc TABARY - 2017-02-21 - RESOURCE VISIBILITY
// COMMENT BY Marc TABARY - 2017-02-20 - RESOURCE VISIBILITY            
//        if ($scope=='orga') {
//          $queryWhere.=" $table.idOrganization in (". Organization::getUserOrganisationList().")";
//        } else if ($scope=='team') {
//          $aff=new Affectable(getSessionUser()->id,true);
//          $queryWhere.=" $table.idTeam='$aff->idTeam'";
//        }
// END COMMENT BY Marc TABARY - 2017-02-20 - RESOURCE VISIBILITY                    
      }
    }
    
// ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY            
    if ($objectClass=='Organization') {
      $scope=Affectable::getOrganizationVisibilityScope('Screen');
      if ($scope!="all") {
        $queryWhere.= ($queryWhere=='')?'':' and ';
        if ($scope=='subOrga') {
          // Can see organization and sub-organizations
          $queryWhere.=" $table.id in (". Organization::getUserOrganizationList().")";
        } else if ($scope=='orga') {
          // Can see only organization  
          $aff=new Affectable(getSessionUser()->id,true);
          $queryWhere.=" $table.id='$aff->idOrganization'";
        }
      }
    }
// END ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY            
    
    // --- Apply systematic restriction  criteria defined for the object class (for instance, for types, limit to corresponding type)
    $crit=$obj->getDatabaseCriteria();
    foreach ($crit as $col => $val) {
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.= $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . "=" . Sql::str($val) . " ";
    }

    // --- If isPrivate existe, take into account privacy 
    if (property_exists($obj,'isPrivate')) {
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.= SqlElement::getPrivacyClause($obj);
    }
    // --- When browsing Docments throught directory view, limit list of Documents to currently selected Directory
    if ($objectClass=='Document') {
    	if (sessionValueExists('Directory') and ! $quickSearch) {
    		$queryWhere.= ($queryWhere=='')?'':' and ';
        $queryWhere.= $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName('idDocumentDirectory') . "='" . getSessionValue('Directory') . "'";
    	}
    }
    
    // --- Apply sorting filers --------------------------------------------------------------
    // --- 1) retrieve corresponding filter clauses depending on context
    $arrayFilter=($quickSearch)?array():jsonGetFilterArray($objectClass, $comboDetail, $quickSearch);
    // --- 2) sort from index checked in List Header (only used for printing, as direct filter is done on client side)
    $sortIndex=null;   
    if ($print and $outMode!='csv') {
      if (array_key_exists('sortIndex', $_REQUEST)) {
        $sortIndex=$_REQUEST['sortIndex']+1;
        $sortWay=(array_key_exists('sortWay', $_REQUEST))?$_REQUEST['sortWay']:'asc';
        $nb=0;
        $numField=0;
        foreach ($array as $val) {
          $fld=htmlExtractArgument($val, 'field');      
          if ($fld and $fld!="photo") {            
            $numField+=1;
            if ($sortIndex and $sortIndex==$numField) {
              $queryOrderBy .= ($queryOrderBy=='')?'':', ';
              //if (Sql::isPgsql()) $fld='"'.$fld.'"';
              if (property_exists($obj, $fld)) {
                $queryOrderBy .= " " . $obj->getDatabaseTableName().".".$fld . " " . $sortWay;
              } else if (property_exists($obj,$objectClass.'PlanningElement') and property_exists($objectClass.'PlanningElement',$fld) ) {
                $queryOrderBy .= " ".strtolower($objectClass)."planningelement.".$fld . " " . $sortWay;
              } else if (property_exists($obj,'WorkElement') and property_exists('WorkElement',$fld)) {
                $queryOrderBy .= " workelement.".$fld . " " . $sortWay;
              } else {
                $queryOrderBy .= " " . $fld . " " . $sortWay;
              }
            }
          }
        }
      }
    }
    // 3) sort from Filter Criteria
    if (! $quickSearch) {
      jsonBuildSortCriteria($querySelect,$queryFrom,$queryWhere,$queryOrderBy,$idTab,$arrayFilter,$obj);
    }
    
    // --- Rest of filter selection will be done later, after building select clause
    
    // ====================== Build restriction clauses ================================================
    // --- Build select clause, and eventualy extended From clause and Where clause
    $numField=0;
    $formatter=array();
    $arrayWidth=array();
    if ($outMode=='csv') {
    	$obj=new $objectClass();
    	$arrayDependantObjects=array('Document'=>array('_DocumentVersion'=>new DocumentVersion()));
    	$arrayDep=array();
    	if (isset($arrayDependantObjects[$objectClass]) ) {
    	  $arrayDep=$arrayDependantObjects[$objectClass];
    	}
    	$clause=$obj->buildSelectClause(false,$hiddenFields,$arrayDep);
    	$querySelect .= ($querySelect=='')?'':', ';
    	$querySelect .= $clause['select'];
    	//$queryFrom .= ($queryFrom=='')?'':', ';
    	$queryFrom .= $clause['from'];
    	if (!isset($hiddenFields['hyperlink'])) {
    	  $querySelect .= ($querySelect=='')?'':', ';
    	  $querySelect .= $obj->getDatabaseTableName() . '.id as hyperlink';
    	}
    } else {
	    foreach ($array as $val) {
	      //$sp=preg_split('field=', $val);
	      //$sp=explode('field=', $val);
	      $fld=htmlExtractArgument($val, 'field');
	      if ($fld) {
	        $numField+=1;    
	        $formatter[$numField]=htmlExtractArgument($val, 'formatter');
	        $from=htmlExtractArgument($val, 'from');
	        $arrayWidth[$numField]=htmlExtractArgument($val, 'width');
	        $querySelect .= ($querySelect=='')?'':', ';
	        if (substr($formatter[$numField],0,5)=='thumb' and substr($formatter[$numField],0,9)!='thumbName') {
            $querySelect.=substr($formatter[$numField],5).' as ' . $fld;;
            continue;
          }
	        if (strlen($fld)>9 and substr($fld,0,9)=="colorName") {
	          $idTab+=1;
	          // requested field are colorXXX and nameXXX => must fetch the from external table, using idXXX
	          $externalClass = substr($fld,9);
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();
	          $externalTableAlias = 'T' . $idTab;
	          if (Sql::isPgsql()) {
	          	//$querySelect .= 'concat(';
		          if (property_exists($externalObj,'sortOrder')) {
	              $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('sortOrder');
	              $querySelect .=  " || '#split#' ||";
	            }
	            $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('name');
	            $querySelect .=  " || '#split#' ||";
	            $querySelect .= "COALESCE(".$externalTableAlias . '.' . $externalObj->getDatabaseColumnName('color').",'')";
	            //$querySelect .= ') as "' . $fld .'"';
	            $querySelect .= ' as "' . $fld .'"'; 
	          } else {
	            $querySelect .= 'convert(';
	            $querySelect .= 'concat(';
	            if (property_exists($externalObj,'sortOrder')) {
                $querySelect .= "COALESCE(".$externalTableAlias . '.' . $externalObj->getDatabaseColumnName('sortOrder').",'')";
                $querySelect .=  ",'#split#',";
	            }
	            $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('name');
	            $querySelect .=  ",'#split#',";
	            $querySelect .= "COALESCE(".$externalTableAlias . '.' . $externalObj->getDatabaseColumnName('color').",'')";
	            $querySelect .= ")"; // end of concat()
	            $querySelect .= ' using utf8)'; // end of convert
	            $querySelect .= ' as ' . $fld;
	          }	          
	          $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
	            ' on ' . $table . "." . $obj->getDatabaseColumnName('id' . $externalClass) . 
	            ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
	        } else if (strlen($fld)>4 and (substr($fld,0,4)=="name" or strpos($fld,'__id')>0) and !$from) {
	          $idTab+=1;
	          // requested field is nameXXX => must fetch it from external table, using idXXX
	          $posExt=strpos($fld, "__id");
	          if ($posExt>0) $externalClass=substr(foreignKeyWithoutAlias($fld), 2);
	          else $externalClass = substr($fld,4);
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();
	          $externalTableAlias = 'T' . $idTab;
	          if (property_exists($externalObj, '_calculateForColumn') and isset($externalObj->_calculateForColumn['name']) and $formatter[$numField]!='noCalculate' and $externalClass!='User')  {
	          	$fieldCalc=$externalObj->_calculateForColumn["name"];
	          	$fieldCalc=str_replace("(","($externalTableAlias.",$fieldCalc);
	          	//$calculated=true;
	          	$querySelect .= $fieldCalc . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
	          } else if ($externalClass=='DocumentDirectory') {
	          	  $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('location') . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
	          } else {
	          	$querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('name') . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
	          }
	          if (substr($formatter[$numField],0,9)=='thumbName' or substr($formatter[$numField],0,8)=='iconName') {
	            $numField+=1;
	            $formatter[$numField]='';
	            $arrayWidth[$numField]='';
	            $querySelect .= ', '.$table . "." . $obj->getDatabaseColumnName('id' . $externalClass) . ' as id' . $externalClass;
	          }
	          //if (! stripos($queryFrom,$externalTable)) {
	            $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
	              ' on ' . $table . "." . $obj->getDatabaseColumnName((substr($fld,0,4)=="name")?'id'.substr($fld,4):$fld) . 
	              ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
	          //}   
	        } else if (strlen($fld)>5 and substr($fld,0,5)=="color") {
	          $idTab+=1;
	          // requested field is colorXXX => must fetch it from external table, using idXXX
	          $externalClass = substr($fld,5);
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();
	          $externalTableAlias = 'T' . $idTab;
	          $querySelect .= $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('color') . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
	          //if (! stripos($queryFrom,$externalTable)) {
	            $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias . 
	              ' on ' . $table . "." . $obj->getDatabaseColumnName('id' . $externalClass) . 
	              ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
	          //}
	        } else if ($from) {
	          // Link to external table
	          $externalClass = $from;
	          $externalObj=new $externalClass();
	          $externalTable = $externalObj->getDatabaseTableName();          
	          $externalTableAlias = strtolower($externalClass);
	          if (! stripos($queryFrom,'left join ' . $externalTable . ' as ' . $externalTableAlias)) {
	            $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
	              ' on (' . $externalTableAlias . '.refId=' . $table . ".id" . 
	              ' and ' . $externalTableAlias . ".refType='" . $objectClass . "')";
	          }
	          if ($from=='OrganizationBudgetElementCurrent') {
	            $queryFrom.=' and '.$externalTableAlias . '.' . $externalObj->getDatabaseColumnName('year').'='.date('Y');
	          }
	          if (strlen($fld)>4 and substr($fld,0,4)=="name") {
              $idTab+=1;
              // requested field is nameXXX => must fetch it from external table, using idXXX
              $externalClassName = substr($fld,4);
              $externalObjName=new $externalClassName();
              $externalTableName = $externalObjName->getDatabaseTableName();
              $externalTableAliasName = 'T' . $idTab;
              $querySelect .= $externalTableAliasName . '.' . $externalObjName->getDatabaseColumnName('name') . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
              $queryFrom .= ' left join ' . $externalTableName . ' as ' . $externalTableAliasName .
                  ' on ' . $externalTableAlias . "." . $externalObj->getDatabaseColumnName('id' . $externalClassName) . 
                  ' = ' . $externalTableAliasName . '.' . $externalObjName->getDatabaseColumnName('id');   
            } else {
            	$querySelect .=  $externalTableAlias . '.' . $externalObj->getDatabaseColumnName($fld) . ' as ' . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
            } 	
            
	          if ( property_exists($externalObj,'wbsSortable') 
	            and strpos($queryOrderBy,$externalTableAlias . "." . $externalObj->getDatabaseColumnName('wbsSortable'))===false) {
	            $queryOrderBy .= ($queryOrderBy=='')?'':', ';
	            $queryOrderBy .= " " . $externalTableAlias . "." . $externalObj->getDatabaseColumnName('wbsSortable') . " ";
	          } 
	        } else {      
	          // Simple field to add to request 
	          $querySelect .= $table . '.' . $obj->getDatabaseColumnName($fld) . ' as ' . ((Sql::isPgsql())?'"'.strtr($fld,'.','_').'"':strtr($fld,'.','_'));
	        }
	      }
	    }
	    if (property_exists($obj,'idProject')) {
	      $querySelect.=','.$table.'.idProject as idproject';
	    }
	    if (get_class($obj)=='Affectation') {
	      $idTab+=1;
	      $externalClass = 'Affectable';
	      $externalObj=new Affectable();
	      $externalTable = $externalObj->getDatabaseTableName();
	      $externalTableAlias = 'T' . $idTab;
	      $fld='name';
	      $querySelect .= ($querySelect=='')?'':', ';
	      $querySelect .= "concat($externalTableAlias.name,'|',$externalTableAlias.fullName) as " . ((Sql::isPgsql())?'"'.$fld.'"':$fld);
	      $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
	      ' on ' . $table . "." . $obj->getDatabaseColumnName('idResource') .
	      ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
	      $numField+=1;
	      $formatter[$numField]='';
	    }
    }
    // --- build order by clause
    if ($objectClass=='DocumentDirectory') {
    	$queryOrderBy .= ($queryOrderBy=='')?'':', ';
    	$queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('location');
    } else if ( property_exists($objectClass,'wbsSortable')) {
      $queryOrderBy .= ($queryOrderBy=='')?'':', ';
      $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('wbsSortable');
    } else if ( property_exists($objectClass,'bbsSortable')) {
      $queryOrderBy .= ($queryOrderBy=='')?'':', ';
      $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('bbsSortable');
    } else if (property_exists($objectClass,'sortOrder')) {
      $queryOrderBy .= ($queryOrderBy=='')?'':', ';
      $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('sortOrder');
    } else {
      $queryOrderBy .= ($queryOrderBy=='')?'':', ';
      $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName('id') . " desc";
    }
    jsonBuildWhereCriteria($querySelect,$queryFrom,$queryWhere,$queryOrderBy,$idTab,$arrayFilter,$obj);
    
    $list=Plugin::getEventScripts('query',$objectClass);
    foreach ($list as $script) {
      require $script; // execute code
    }
    
    // ==================== Constitute query and execute ============================================================
    // --- Buimd where from "Select", "From", "Where" and "Order by" clauses built above
    //gautier #1700
    if($objectClass == 'Work'){
      $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
      $table = getListForSpecificRights('imputation');
      $getRessource = RequestHandler::getValue('exportRessourceAs');
      $date = RequestHandler::getValue('exportDateAs');
      $w=new Work();
      $wTable=$w->getDatabaseTableName();
      if (substr($getRessource,0,1) == 'C') {
        $getRessource = substr($getRessource,1);
        $queryWhere.=" and $wTable.idResource = $getRessource ";
      }else{
        $queryWhere.=" and $wTable.idResource in ".transformListIntoInClause($table);
      }
      if(substr($date,0,1) == 'W') {
        $dateWeekOrMonthOrYear = 'week';
      }elseif (substr($date,0,1) == 'M'){
        $dateWeekOrMonthOrYear = 'month';
      }elseif (substr($date,0,1) == 'Y'){
        $dateWeekOrMonthOrYear = 'year';
      }else {
        $date = 'All';
      }
      if($date != 'All'){
        $date = substr($date,1);
        $queryWhere.=" and $dateWeekOrMonthOrYear = ".Sql::str($date);
      }
    }
    //end gautier
    
    if($objectClass=='Budget'){
      $idSelectedBudget = RequestHandler::getValue('budgetParent');
      if($idSelectedBudget){
        $budg = new Budget($idSelectedBudget);
        $bbsSortable = $budg->bbsSortable;
        $queryWhere.= ' and bbsSortable like "'.$bbsSortable.'%"';
      }
    }
    
// MTY - LEAVE SYSTEM
    // For Class of Leave System
    if (isLeavesSystemActiv()) {
        if (array_key_exists($obj->getMenuClass(), leavesSystemHabilitationList())) {
            $userId = getSessionUser()->id;
            // If access right is OWN = In leave system, owner is 
            // ObjectClass = Employee
            //      - Self or manager of employee (id)
            // ObjectClass = Other
            //      - idUser or idEmployee
            //      - Manager of the Employee
            if ($accessRightRead=="OWN") {
                // objectClass = Employee
                if ($objectClass=="Employee") {
                    $empMng = new EmployeeManager(getSessionUser()->id);
                    $managedEmployees = $empMng->getManagedEmployees();
                    // Manager
                    if ($managedEmployees) {
                        $queryWhere .= ($queryWhere==""?"":" AND ");
                        $queryWhere .= "$table.id in (". getSessionUser()->id.",";
                        foreach($managedEmployees as $key => $name) {
                            if ($key != getSessionUser()->id) {$queryWhere .= "$key,";}
                        }            
                        $queryWhere .= ") ";
                        $queryWhere = str_replace(",)",")", $queryWhere);
                    }
                    // Self
                    else {
                        $queryWhere .= ($queryWhere==""?"":" AND ");
                        $queryWhere .= "$table.id=".getSessionUser()->id;
                    }           
                } 
                // Other ObjectClass
                else {
                    //      - idUser or idEmployee
                    $quote = false;
                    if (property_exists($objectClass, "idUser")) {
                        $queryWhere .= ($queryWhere==""?"":" AND ("). "$table.idUser = $userId ";
                        if (property_exists($objectClass, "idEmployee")) {
                            $queryWhere .= ($queryWhere==""?"":" OR ")."$table.idEmployee = $userId ";
                        }
                        $quote =true;
                    } elseif (property_exists($objectClass, "idEmployee")) {
                        $queryWhere .= ($queryWhere==""?"":" AND ")."$table.idEmployee = $userId ";
                    }
                    // Manager
                    if (property_exists($objectClass, "idEmployee")) {
                        $empMng = new EmployeeManager(getSessionUser()->id);
                        $managedEmployees = $empMng->getManagedEmployees();
                        if ($managedEmployees) {
                            if ($quote) {
                                $queryWhere .= ($queryWhere==""?"":" OR ");                                
                            } else {
                                $queryWhere .= ($queryWhere==""?"":" AND ");                                
                            }
                            $queryWhere .= "$table.idEmployee in (";
                            foreach($managedEmployees as $key => $name) {
                                $queryWhere .= "$key,";
                            }            
                            $queryWhere .= ") ";
                            $queryWhere = str_replace(",)",")", $queryWhere);
                        }                
                    }
                    if ($quote) {
                        $queryWhere .=") ";
                    }
                }
            }
        }    
    }
// MTY - LEAVE SYSTEM

    if (!$queryWhere) $queryWhere='1=1';
    $query='select ' . $querySelect 
         . ' from ' . $queryFrom
         . ' where ' . $queryWhere 
         . ' order by' . $queryOrderBy;
    // --- Execute query
    $result=Sql::query($query);
    if (isset($debugJsonQuery) and $debugJsonQuery) { // Trace in configured to
       debugTraceLog("jsonQuery: ".$query); // Trace query
       debugTraceLog("  => error (if any) = ".Sql::$lastQueryErrorCode.' - '.Sql::$lastQueryErrorMessage);
       debugTraceLog("  => number of lines returned = ".Sql::$lastQueryNbRows);
    }
    $nbRows=0;
    $dataType=array();
    
    // --- Format for "printing" 
    if ($print) {
    	if ($outMode=='csv') { // CSV mode
    		$exportReferencesAs='name';
    		if (isset($_REQUEST['exportReferencesAs'])) {
    		  $exportReferencesAs=$_REQUEST['exportReferencesAs'];
    		}
    		$exportHtml=false;
    		if (isset($_REQUEST['exportHtml']) and $_REQUEST['exportHtml']=='1') {
    		  $exportHtml=true;
    		}
            $csvSep="";
            if (isset($_REQUEST['separatorCSV'])) {
                $csvSep=$_REQUEST['separatorCSV'];
            } else {
                $csvSep=Parameter::getGlobalParameter('csvSeparator');
            }
    		$headers='caption';
    		$csvQuotedText=true;
    		if ($csvExportAll) {
    		  $exportReferencesAs='id';
    		  if (isset($csvSepExportAll)) $csvSep=$csvSepExportAll; // test should always be true
    		  $exportHtml=true;
    		  $headers='id';
    		  $csvQuotedText=false;
    		}
    		$obj=new $objectClass();
    		if (method_exists($obj, 'setAttributes')) $obj->setAttributes();
    		$first=true;
    		$arrayFields=array();
        $arrayFields=$obj->getLowercaseFieldsArray(true);
        $arrayFieldsWithCase=$obj->getFieldsArray(true);
        if ($objectClass!='Work') {
          $arrayFields['hyperlink'] = 'hyperlink';
          $arrayFieldsWithCase['hyperlink'] = 'Hyperlink';
        }
        foreach($arrayFieldsWithCase as $key => $val) {
          if (!SqlElement::isVisibleField($val)) {
            unset($arrayFields[strtolower($key)]);
            continue;
          }
          $arrayFieldsWithCase[$key]=$obj->getColCaption($val);
          if(isset($arrayFieldsWithCase[$key]) and substr($arrayFieldsWithCase[$key], 0, 1) == "["){
            unset($arrayFields[strtolower($key)]);
            continue;
          }
        }
    		while ($line = Sql::fetchLine($result)) {
    		  if ($first) {
	    			foreach ($line as $id => $val) {
	    			  if (!isset($arrayFields[strtolower($id)]) || ($objectClass=='GlobalView' and $id=='id')) {
	    			    continue;   
	    			  }
	    				$colId=$id;
	    				if (Sql::isPgsql() and isset($arrayFields[$id])) {
	    					$colId=$arrayFields[$id];
	    				}
	    				if (property_exists($obj, $colId)) {
	    				  $val=encodeCSV($obj->getColCaption($colId));
	    				} else if (property_exists($obj, 'WorkElement') and property_exists('WorkElement', $colId)) {
	    				  $we=new WorkElement();
	    				  $val=encodeCSV($we->getColCaption($colId));
	    				} else if (property_exists($obj, get_class($obj).'PlanningElement') and property_exists(get_class($obj).'PlanningElement', $colId)) {
	    				    $peClass=get_class($obj).'PlanningElement';
	    				    $pe=new $peClass();
	    				    $val=encodeCSV($pe->getColCaption($colId));
	    				} else {
	    				  $val=encodeCSV($obj->getColCaption($colId)); // well, in the end, get default.
	    				}
	    				if ($headers=='id') $val=$colId;
	    				if (strpos($colId,'_')!==null and isset($arrayDependantObjects[$objectClass])) {
	    				  $split=explode('_',$colId);
	    				  foreach ($arrayDependantObjects[$objectClass] as $incKey=>$incVal) {
	    				    $incKey=ltrim($incKey,'_');
	    				    if (strtolower($incKey)==$split[0] and SqlElement::class_exists($incKey)) {
	    				      $val=encodeCSV($incVal->getColCaption($split[1]).' ('.i18n($incKey).')');    				      
	    				      break;
	    				    }
	    				  }
	    				}
	    				if (substr($id,0,9)=='idContext' and strlen($id)==10) {
                $ctx=new ContextType(substr($id,-1));
                $val=encodeCSV($ctx->name);
              } 
	    				//$val=encodeCSV($id);
	    				$val=str_replace($csvSep,' ',$val);
	            //if ($id!='id') { echo $csvSep ;}
	    				echo $val.$csvSep;
	            $dataType[$id]=$obj->getDataType($id);
	            $dataLength[$id]=$obj->getDataLength($id);
	            if (! $dataLength[$id] and substr($id,0,2)=='id' and strlen($id)>2 and substr($id,2,1)==strtoupper(substr($id,2,1)) ) {
	              $dataType[$id]='int';
	              $dataLength[$id]='12';
	            }
	            if ($id=='refId' and ! property_exists($objectClass,'refName') and $exportReferencesAs=='name') {
	              echo encodeCSV(i18n('colName')).$csvSep;
	            }
	          }
	          echo "\r\n";
    			}
    			$refType=null;
    			foreach ($line as $id => $val) {
    			  if (!isset($arrayFields[strtolower($id)]) || ($objectClass=='GlobalView' and $id=='id')) continue;
    			  if ($id=='refType') $refType=$val;
    				$foreign=false;
    				$colId=$id;
    				if (Sql::isPgsql() and isset($arrayFields[$id])) {
    					$colId=$arrayFields[$id];
    				}
    				if (!isset($arrayFieldsWithCase[$colId])) continue;
    				//if (substr($id, 0,2)=='id' and strlen($id)>2) {
    				if (isForeignKey($arrayFields[strtolower($id)], $obj)) { // #3522 : Fix issue to export custom foreign items xxxx__idYyyyy 
    				  $class=substr(foreignKeyWithoutAlias($arrayFields[strtolower($id)]), 2);
    					//$class=substr($arrayFields[strtolower($id)], 2);
    					if (ucfirst($class)==$class) {
    						$foreign=true;
    						if ($class=="TargetVersion" or $class=="TargetProductVersion" or $class=="TargetComponentVersion"
    						 or $class=="OriginalVersion" or $class=="OriginalProductVersion" or $class=="OriginalComponentVersion") $class='Version';
    						if ($exportReferencesAs=='name') {
    						  if ($id=='idDocumentDirectory') {
    						    $val=SqlList::getFieldFromId($class, $val,'location');
    						  } else if (property_exists($class, 'name')){
    					      $val=SqlList::getNameFromId($class, $val);
    						  }
    						}
    					}
    				}
    				if ($dataLength[$id]>4000 and !$exportHtml) {
    					if (isTextFieldHtmlFormatted($val)) {
	    				  if (!$exportHtml) {
    							$text=new Html2Text($val);
	    				  	$val=$text->getText();
	    				  }
    					} else {
    				    $val=br2nl($val);
    					}
     				}
    				$val=encodeCSV($val);
    				if ($csvQuotedText) {
    				  $val=str_replace('"','""',$val);	
    				}
            //if ($id!='id') { echo $csvSep ;}
            if ( ($dataType[$id]=='varchar' or $foreign) and $csvQuotedText) { 
              echo '"' . $val . '"'.$csvSep;
            } else if ( ($dataType[$id]=='decimal')) {
            	echo formatNumericOutput($val).$csvSep;
            } else if ($id == 'hyperlink') {
              echo $obj->getReferenceUrl().$val.$csvSep;
            } else {
                $val=str_replace($csvSep,' ',$val);
                echo $val.$csvSep;
            }
            if ($id=='refId' and ! property_exists($objectClass,'refName') and $exportReferencesAs=='name' and $refType) {
              echo encodeCSV(SqlList::getNameFromId($refType, $val)).$csvSep;
            }
    			}
    			$first=false;
    			echo "\r\n";
    		}
    		if ($first) {
    			echo encodeCSV(i18n("reportNoData")); 
    		}
    	} else { // NON CSV mode : includes pure print and 'pdf' ($outMode=='pdf') mode
        echo '<br/>';
        echo '<div class="reportTableHeader" style="width:99%; font-size:150%;border: 0px solid #000000;">' . i18n('menu'.$objectClass) . '</div>';
        echo '<br/>';
	      echo '<table style="width:100%;'.((isset($outModeBack) and $outModeBack=='pdf' and isWkHtmlEnabled())?'font-size:10pt':'').'">';
	      echo '<tr>';
	      $layout=str_ireplace('width="','style="'.((isNewGui() and $outMode!='pdf')?'border:1px solid white;':'border:1px solid black;').'width:',$layout);
	      $layout=str_ireplace('<th ','<th class="reportHeader" ',$layout);
	      if ($objectClass=='GlobalView' ) $layout=str_replace('<th class="reportHeader" field="id" style="border:1px solid black;width:0%">id</th>','',$layout);
	      echo $layout;
	      echo '</tr>';
	      if (Sql::$lastQueryNbRows > 0) {
	        $hiddenField='<span style="color:#AAAAAA">(...)</span>';
	        while ($line = Sql::fetchLine($result)) {
	          echo '<tr>';
	          $numField=0;
	          $idProject=($objectClass=='Project')?$line['id']:((isset($line['idproject']))?$line['idproject']:null);
	          foreach ($line as $id => $val) {
	            $numField+=1;
	            $disp="";
	            if ($objectClass=='GlobalView' and $id=='id') continue;
	            if (!isset($arrayWidth[$numField]) or $arrayWidth[$numField]=='') continue;
	            if ($formatter[$numField]=="colorNameFormatter") {
	              $disp=colorNameFormatter($val); 
	            } else if ($formatter[$numField]=="classNameFormatter") {
	              $disp=classNameFormatter($val);
              } else if ($formatter[$numField]=="colorTranslateNameFormatter") {
	              $disp=colorTranslateNameFormatter($val);                        
	            } else if ($formatter[$numField]=="booleanFormatter") {
	              $disp=booleanFormatter($val);
	            } else if ($formatter[$numField]=="colorFormatter") {
	              $disp=colorFormatter($val);
	            } else if ($formatter[$numField]=="dateTimeFormatter") {
	              $disp=dateTimeFormatter($val);
	            } else if ($formatter[$numField]=="dateFormatter") {
	              $disp=dateFormatter($val);
	            } else if ($formatter[$numField]=="timeFormatter") {
                $disp=timeFormatter($val);
	            } else if ($formatter[$numField]=="translateFormatter") {
	              $disp=translateFormatter($val);
	            } else if ($formatter[$numField]=="percentFormatter") {
	              $disp=percentFormatter($val,($outMode=='pdf')?false:true);
	            } else if ($formatter[$numField]=="numericFormatter") {
	              $disp=numericFormatter($val);
	            } else if ($formatter[$numField]=="sortableFormatter") {
	              $disp=sortableFormatter($val);
	            } else if ($formatter[$numField]=="workFormatter") {
	              if ($idProject and ! $user->getWorkVisibility($idProject,$id)) {
	                $disp=$hiddenField;
	              } else {
                  $disp=workFormatter($val);
	              }
              } else if ($formatter[$numField]=="costFormatter") {
                if ($idProject and ! $user->getCostVisibility($idProject,$id)) {
                  $disp=$hiddenField;
                } else {
                  $disp=costFormatter($val);
                }
              } else if ($formatter[$numField]=="iconFormatter") {
                $disp=iconFormatter($val);
              } else if ($formatter[$numField]=="iconNameFormatter") {
                  $disp=iconFormatter($val);
              } else if (substr($formatter[$numField],0,9)=='thumbName') {
                //$disp=thumbFormatter($objectClass,$line['id'],substr($formatter[$numField],5));
                $nameClass=substr($id,4);
                if (Sql::isPgsql()) $nameClass=strtolower($nameClass);
                if ($val and $showThumb) {
                  $size=substr($formatter[$numField],9);
                  $radius=round($size/2,0);
                  $thumbUrl=Affectable::getThumbUrl('Affectable',$line['id'.$nameClass], substr($formatter[$numField],9),false, ($outMode=='pdf')?true:false);
                  if (substr($thumbUrl,0,6)=='letter') {
                    $disp.=formatLetterThumb($line['id'.$nameClass],$size,null,null,null).'&nbsp;'.$val;
                  } else {
	                  $disp='<div style="text-align:left;">';
	                  $disp.='<img style="border-radius:'.$radius.'px;height:'.$size.'px;float:left" src="'.$thumbUrl.'"';
	                  $disp.='/>';
	                  $disp.='<div style="margin-left:'.($size+2).'px;">'.$val.'</div>';
	                  $disp.='</div>';
                  }
                } else {
                  $disp="";
                }
              } else if (substr($formatter[$numField],0,5)=='thumb') {
                $thumClass=($objectClass=='ResourceTeam')?'Resource':$objectClass;
	            	$disp=thumbFormatter($thumClass,$line['id'],substr($formatter[$numField],5));
	            } else if ($formatter[$numField]=="privateFormatter") {
	              $disp=privateFormatter($val);
	            } else {
	              $disp=htmlEncode($val);
	            }
	            $colWidth=$arrayWidth[$numField];
	            echo '<td class="tdListPrint '.((substr($formatter[$numField],0,5)=='color')?'colorNameData':'').'" style="white-space:normal;width:' . $colWidth . ';">' . $disp . '</td>';
	          }
	          echo '</tr>';       
	        }
	      }
	      echo "</table>";
	      //echo "</div>";
    	}
    } else {
      // return result in json format
      echo '{"identifier":"id",' ;
      echo ' "items":[';
      if (Sql::$lastQueryNbRows > 0) {               
        while ($line = Sql::fetchLine($result)) {
          if ($objectClass=='Term') { // Attention, this part main reduce drastically performance
            $term=new Term($line['id']);
            $line['validatedAmount']=$term->validatedAmount;
            $line['validatedDate']=$term->validatedDate;
            $line['plannedAmount']=$term->plannedAmount;
            $line['plannedDate']=$term->plannedDate;
          }          
          echo (++$nbRows>1)?',':'';
          echo  '{';
          $nbFields=0;
          $idProject=($objectClass=='Project')?$line['id']:((isset($line['idproject']))?$line['idproject']:null);
          foreach ($line as $id => $val) {
            if ($id=='idproject') continue;
            echo (++$nbFields>1)?',':'';
            $numericLength=0;
            if (! isset($formatter[$nbFields])) $formatter[$nbFields]='';
            if ($id=='id') {
            	$numericLength=6;
            } else if ($formatter[$nbFields]=='classNameFormatter') {
              $val=i18n($val).'|'.$val;
            } else if ($formatter[$nbFields]=='percentFormatter') {
            	$numericLength=3;
            	if ($val<0) $numericLenght=0;
            } else if ($formatter[$nbFields]=='workFormatter') {
              $numericLength=9;
              if ($val<0) $numericLength=0;
              if ($idProject and ! $user->getWorkVisibility($idProject,$id)) {
                $val='-';
                $numericLength=0;
              }
            } else if ($formatter[$nbFields]=='costFormatter') {
            	$numericLength=9;
            	if ($val<0) $numericLength=0;
            	if ($idProject and ! $user->getCostVisibility($idProject,$id)) {
            	  $val='-';
            	  $numericLength=0;
            	}
            } else if ($formatter[$nbFields]=='numericFormatter') {
            	$numericLength=9;
            	if ($val<0) $numericLength=0;
            } 
            if ($id=='colorNameRunStatus') {
            	$split=explode('#',$val);
            	foreach ($split as $ix=>$sp) {
            	  if ($ix==0) {
            	  	$val=$sp;
            	  } else if ($ix==2) {
            		  $val.='#'.i18n($sp);	
            	  } else {
            	  	$val.='#'.$sp;
            	  }
            	} 
            }
            if (substr($formatter[$nbFields],0,8)=='iconName') {
              $nameClass=substr($id,4);
              if (Sql::isPgsql()) $nameClass=strtolower($nameClass);
              if ($val and property_exists($nameClass,'icon')) {
                $val=$val.'#!#'.SqlList::getFieldFromId($nameClass,$line['id'.$nameClass], 'icon');
              }
            }
            if (substr($formatter[$nbFields],0,5)=='thumb') {             
            	if (substr($formatter[$nbFields],0,9)=='thumbName') {
            	  $nameClass=substr($id,4);
            	  if (Sql::isPgsql()) $nameClass=strtolower($nameClass);
            	  if ($val and $showThumb) {
            	    $val=$val.'#!#'.Affectable::getThumbUrl('Affectable',$line['id'.$nameClass], substr($formatter[$nbFields],9)).'#'.$val;
            	  } else {
            	    $val=$val.'#!#'."####$val";
            	  }  	  
            	} else if (Affectable::isAffectable($objectClass)) {
            		$val=Affectable::getThumbUrl($objectClass,$line['id'], $val).'##'.strtoupper(mb_substr(SqlList::getNameFromId('Affectable', $line['id']),0,1,'UTF-8'));
            	} else {          	
	            	$image=SqlElement::getSingleSqlElementFromCriteria('Attachment', array('refType'=>$objectClass, 'refId'=>$line['id']));
	              if ($image->id and $image->isThumbable()) {
	            	  $val=getImageThumb($image->getFullPathFileName(),$val).'#'.htmlEncodeJson($image->id, 6).'#'.htmlEncodeJson($image->fileName); 
	              } else {
	              	$val="##";
	              }
            	}
            	
            }       
            if ($id=='name') {
              $val=htmlEncodeJson($val);
              if (property_exists($obj,'_isNameTranslatable') and $obj->_isNameTranslatable) {
                $val.='#!#!#!#!#!#'.mb_strtoupper(suppr_accents(i18n($val)));
              } else {
                $val.='#!#!#!#!#!#'.mb_strtoupper(suppr_accents($val));
              }
              echo '"' . htmlEncode($id) . '":"' . $val . '"';
            } else {
              echo '"' . htmlEncode($id) . '":"' . htmlEncodeJson($val, $numericLength) . '"';
            }
          }
          echo '}';
        }   
      }
       echo ']';
      //echo ', "numberOfRow":"' . $nbRows . '"' ;
      echo ' }';
    }
?>
