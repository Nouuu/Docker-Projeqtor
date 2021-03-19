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

/** ===========================================================================
 * Static class to retrieves data to build a list for reference needs
 * (to be able to build a select html list)  
 */
if (file_exists('../_securityCheck.php')) include_once('../_securityCheck.php');
class SqlList {

  static private $list=array();


  /** ==========================================================================
   * Constructor : private to lock instanciantion (static class)
   */
  private function __construct() {
  }

  /** ==========================================================================
   * Public method to get the list : either retrieve it from a static array
   * or fetch if from database (and store it in the static array)
   * @param $listType the name of the table containing the data
   * *@param $displayCol the name of the value column (defaut is name)
   * @return an array containing the list of references
   */
  public static function cleanAllLists() {
  	self::$list=array();
  }
  
  public static function getList($listType, $displayCol='name', $selectedValue=null, $showIdle=false, $applyRestrictionClause=false) {
    if (! class_exists($listType)) return array();
    if (! property_exists($listType, $displayCol)) $displayCol=self::getDefaultDisplayCol($listType);
    $listName=$listType . "_" . $displayCol;
    if ($showIdle) { $listName .= '_all'; }
    if ($applyRestrictionClause) {$listName.='_restrict';}
    if (array_key_exists($listName, self::$list)) {
      return self::$list[$listName];
    } else {
      return self::fetchList($listType, $displayCol, $selectedValue, $showIdle, true, $applyRestrictionClause);
    }
  }
  
  public static function getListNotTranslated($listType, $displayCol='name', $selectedValue=null, $showIdle=false) {
    if (! property_exists($listType, $displayCol)) $displayCol=self::getDefaultDisplayCol($listType);
    $listName='no_tr_' . $listType . "_" . $displayCol;
    if ($showIdle) { $listName .= '_all'; }
    if (array_key_exists($listName, self::$list)) {
      return self::$list[$listName];
    } else {
      return self::fetchList($listType, $displayCol, $selectedValue, $showIdle, false);
    }
  }

   public static function getListWithCrit($listType, $crit, $displayCol='name', $selectedValue=null, $showIdle=false) {
//scriptLog("       =>getListWithCrit($listType, implode('|',$crit), $displayCol, $selectedValue)");
     if (! property_exists($listType, $displayCol)) $displayCol=self::getDefaultDisplayCol($listType);
     return self::fetchListWithCrit($listType, $crit, $displayCol, $selectedValue,$showIdle);
   }
   
   private static function getDefaultDisplayCol($listType) {
     if ($listType=='Leave') return 'startDate';
     if (property_exists($listType, 'name')) return 'name';
     return 'id';
   }
  /** ==========================================================================
   * Private method to get fetch the list from database and store it in a static array
   * for further needs
   * @param $listType the name of the table containing the data
   * @return an array containing the list of references
   */
  private static function fetchList($listType,$displayCol, $selectedValue, $showIdle=false, $translate=true,$applyRestrictionClause=false) {
//scriptLog("fetchList($listType,$displayCol, $selectedValue, $showIdle, $translate)");
    $res=array();
    if (! SqlElement::class_exists($listType)) {
      debugTraceLog("WARNING : SqlList::fetchList() called for not valid class '$listType'");
      return array();
    }
    $obj=new $listType();
    $calculated=false;
    $field=$obj->getDatabaseColumnName($displayCol);
    if (property_exists($obj, '_calculateForColumn') and isset($obj->_calculateForColumn[$displayCol])) {
    	$field=$obj->_calculateForColumn[$displayCol];
    	$calculated=true;
    }
    $query="select " . $obj->getDatabaseColumnName('id') . " as id, " . $field . " as name from " . $obj->getDatabaseTableName() ;
    if ($showIdle or !property_exists($obj, 'idle')) {
      $query.= " where (1=1 ";
    } else {
      $query.= " where (idle=0 ";
    }
    $crit=$obj->getDatabaseCriteria();
    foreach ($crit as $col => $val) {
    	if ($obj->getDatabaseColumnName($col)=='idProject' and ($val=='*' or !$val)) {$val=0;}
    	if ($val===null) {
    	  $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . ' IS NULL';
    	} else {
        $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . '=' . Sql::str($val);
    	}
    }
    if ($applyRestrictionClause) {
    	$query.=' and '.getAccesRestrictionClause($listType,null,true);
    }
    $query .=')';
    if (trim($selectedValue)) {
    	if ($selectedValue!='*' and $selectedValue!='all' and intval($selectedValue) ) {
        $query .= " or " . $obj->getDatabaseColumnName('id') .'= ' . Sql::str($selectedValue) ;
    	}
    }
    if (property_exists($obj,'_sortCriteriaForList')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.'.$obj->_sortCriteriaForList;
    } else if (property_exists($obj,'sortOrder')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.sortOrder, ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol);
    } else if (property_exists($obj,'bbsSortable')) {
        $query .= ' order by ' . $obj->getDatabaseTableName() . '.bbsSortable, ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol); 
    } else if (property_exists($obj,'order')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.order, ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol);
    } else if (property_exists($obj,'baselineDate')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.baselineDate, ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol);
    } else {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol);
    }
    $result=Sql::query($query);
    if (Sql::$lastQueryNbRows > 0) {
      while ($line = Sql::fetchLine($result)) {
        $name=$line['name'];
        if ($obj->isFieldTranslatable($displayCol) and $translate){
        	if ($listType=='Linkable' and substr($name,0,7)=='Context') {
        		$name=SqlList::getNameFromId('ContextType', substr($name,7,1));
        	} else {
            $name=i18n($name);
        	}
        }
        if ($displayCol=='name' and property_exists($obj,'_constructForName') and !$calculated) {
        	$nameObj=new $listType($line['id'],true);
        	$name=$nameObj->name;
        }
        $res[($line['id'])]=$name;
      }
    }
    // Plugin - start - Management for event "list"
    global $pluginAvoidRecursiveCall;
    if (! $pluginAvoidRecursiveCall) {
      $pluginAvoidRecursiveCall=true;
      $pluginObjectClass=$listType;
      $table=$res;
      $lstPluginEvt=Plugin::getEventScripts('list',$pluginObjectClass);
      foreach ($lstPluginEvt as $script) {
        require $script; // execute code
      }
      $res=$table;
      $pluginAvoidRecursiveCall=false;
    }
    // Plugin - end
    if ($translate) {
      self::$list[$listType . "_" . $displayCol .(($showIdle)?'_all':'')]=$res;
    } else {
    	self::$list['no_tr_' . $listType . "_" . $displayCol .(($showIdle)?'_all':'')]=$res;
    } 
    return $res;
  }
 
  private static function fetchListWithCrit($listType,$criteria, $displayCol, $selectedValue, $showIdle) {
  //scriptLog("fetchListWithCrit(listType=$listType,criteria=".implode('|',$criteria).",displayCol=$displayCol, selectedValue=$selectedValue, showIdle=$showIdle)");
    $res=array();
    $obj=new $listType();
    $calculated=false;
    $field=$obj->getDatabaseColumnName($displayCol);
    if (property_exists($obj, '_calculateForColumn') and isset($obj->_calculateForColumn[$displayCol])) {
    	$field=$obj->_calculateForColumn[$displayCol];
    	$calculated=true;
    }
    $query="select " . $obj->getDatabaseColumnName('id') . " as id, " . $field . " as name from " . $obj->getDatabaseTableName() . " where (1=1 ";
    $query.=(! $showIdle and property_exists($obj, 'idle'))?' and idle=0 ':'';
    if (is_array($criteria)) {
      if (($listType=='Version' 
          or $listType=='ProductVersion' or $listType=='ComponentVersion'
          or $listType=='TargetVersion' or $listType=='TargetProductVersion' or $listType=='TargetComponentVersion'
          or $listType=='OriginalVersion' or $listType=='OriginalProductVersion' or $listType=='OriginalComponentVersion') and $criteria) {
        foreach($criteria as $key=>$val) {
          if ($key=='idComponent' or $key=='idProductOrComponent') {
            unset($criteria[$key]);
            $criteria['idProduct']=$val;
          }
        }
      }
      $crit=array_merge($obj->getDatabaseCriteria(),$criteria);
      foreach ($crit as $col => $val) {
        if ( (strtolower($listType)=='resource' or strtolower($listType)=='contact' or strtolower($listType)=='user' 
        or strtolower($listType)=='accountable' or strtolower($listType)=='affectable') and $col=='idProject') {
          $aff=new Affectation();
          $user=new Resource();
          if ($val=='*' or ! $val) {$val=0;}
          $query .= " and exists (select 'x' from " . $aff->getDatabaseTableName() . " a where a.idProject=" . Sql::fmtId($val) . " and a.idResource=" . $user->getDatabaseTableName() . ".id)";
        } else if ((strtolower($listType)=='version'
            or strtolower($listType)=='productversion' or strtolower($listType)=='componentversion' 
            or strtolower($listType)=='originalversion' or strtolower($listType)=='originalproductversion' or strtolower($listType)=='originalcomponentversion' 
            or strtolower($listType)=='targetversion' or strtolower($listType)=='targetproductversion' or strtolower($listType)=='targetcomponentversion') and $col=='idProject') {
        	$vp=new VersionProject();
          $ver=new Version();
          $proj=new Project($val);
          $lst=$proj->getTopProjectList(true);
          $inClause='(0';
          foreach ($lst as $prj) {
          	if ($prj) {
          	  $inClause.=',';
          	  $inClause.=$prj;
          	}
          }
          $inClause.=')';
          if ($val) $query .= " and exists (select 'x' from " . $vp->getDatabaseTableName() . " vp where vp.idProject in " . $inClause . " and vp.idVersion=" . $ver->getDatabaseTableName() . ".id)";
        } else if ( ( strtolower($listType)=='componentversion'  
              or strtolower($listType)=='originalcomponentversion'
              or strtolower($listType)=='targetcomponentversion') and $col=='idProductVersion') {
            $psv=new ProductVersionStructure();
            $ver=new Version();
            $lstVers=ProductVersionStructure::getComposition($val);
            $inClause='(0';
            foreach ($lstVers as $idsharp=>$idv) {
              $inClause.=','.$idv;
            }
            $inClause.=')';
            //$query .= " and exists (select 'x' from " . $psv->getDatabaseTableName() . " pvs where pvs.idProductVersion=".$val." and pvs.idComponentVersion=" . $ver->getDatabaseTableName() . ".id)";
            $query .= " and ".$ver->getDatabaseTableName().".id in ".$inClause;
          
        } else if (strtolower($listType)=='indicator' and $col=='idIndicatorable' ) {
        	$ii=new IndicatorableIndicator();
        	$i=new Indicator();
        	$query.=" and exists ( select 'x' from " . $ii->getDatabaseTableName() . " ii " 
        	      . " where ii.idIndicatorable='" . Sql::fmtId($val) . "' and ii.idIndicator=" . $i->getDatabaseTableName() . ".id)"; 
        } else if ( (strtolower($listType)=='warningdelayunit' or strtolower($listType)=='alertdelayunit') and $col=='idIndicator' ) {
          $ind=new Indicator($val);
          $query .= " and " . $obj->getDatabaseTableName() . '.type='. Sql::str($ind->type);
        } else if ( $col=='idProjectSub' ) {
          if ($val and $val!='*') {
            $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName('idProject') . ' in ' . getVisibleProjectsList(true,$val);
          }
        } else if (is_array($val)) {
          foreach ($val as $k => $v) {
            $val[$k] = Sql::str($v);
          }
          if (count($val)==0) $val[0]=0;
          $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . ' IN (' . implode(',', $val) . ')';
        } else {
          if ($val==null or $val=='' or $val=='null') {
            $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . " is null";
          } else {
            if ($col=='idProject' and ($val=='*' or ! $val)) {continue;}
            $dataType=$obj->getDataType($col);
            if ($dataType=='numeric' or $dataType=='decimal' or $dataType=='int' or $dataType=='boolean') {
              $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . '=' . Sql::fmtStr($val);
            } else { 
              $query .= ' and ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . '=' . Sql::str($val);
            }
          }
        }
      }
    } else {
      $query.=" and ( $criteria )";
    }
    $query .=')';
    if ($listType=='Report') {
      $hr=new HabilitationReport();
      $user=getSessionUser();
      $lstIn="";
      $lst=$hr->getSqlElementsFromCriteria(array('idProfile'=>$user->idProfile, 'allowAccess'=>'1'), false);
      foreach ($lst as $h) {
        $lstIn.=(($lstIn=='')?'':', ') . $h->idReport;
      }
      $query .= ' and id in (' . $lstIn . ')' ;
    } 
    if (trim($selectedValue)) {
    	if ($selectedValue!='*' and $selectedValue!='all' and intval($selectedValue) ) {
        $query .= " or " . $obj->getDatabaseColumnName('id') .'=' . Sql::fmtId($selectedValue) ;
    	}
    }
    if (property_exists($obj,'_sortCriteriaForList') and $obj->_sortCriteriaForList) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.'.$obj->_sortCriteriaForList;
    } else if (property_exists($obj,'sortOrder')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.sortOrder';
    } else if (property_exists($obj,'bbsSortable')) {
        $query .= ' order by ' . $obj->getDatabaseTableName() . '.bbsSortable';
    } else if (property_exists($obj,'order')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.order';
    } else if (property_exists($obj,'baselineDate')) {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.baselineDate, ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol);
    //ADD qCazelles - Sort versions in combobox - Ticket 89
    } else if (Parameter::getGlobalParameter('sortVersionComboboxNameDesc') == 'YES' and SqlElement::is_a($obj, 'Version') and $displayCol == 'name') {
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol) . ' desc'; 
    //END ADD qCazelles - Sort versions in combobox - Ticket 89
    } else{
      $query .= ' order by ' . $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($displayCol); 
    }
    $result=Sql::query($query);
    if (Sql::$lastQueryNbRows > 0) {
      while ($line = Sql::fetchLine($result)) {
        $name=$line['name'];
        if ($obj->isFieldTranslatable($displayCol)){
          $name=i18n($name);
        }
        if ($displayCol=='name' and property_exists($obj,'_constructForName') and !$calculated ) {
        	if ($listType=='TargetVersion') $listType='OriginalVersion';
        	if ($listType=='TargetProductVersion') $listType='OriginalProductVersion';
        	if ($listType=='TargetComponentVersion') $listType='OriginalComponentVersion';
          $nameObj=new $listType($line['id'],true);
          if ($nameObj->id) {
            $name=$nameObj->name;
          }
        }
        $res[($line['id'])]=$name;
      }
    }
    // Plugin - start - Management for event "list"
    global $pluginAvoidRecursiveCall;
    if (! $pluginAvoidRecursiveCall) {
      $pluginAvoidRecursiveCall=true;
      $pluginObjectClass=$listType;
      $table=$res;
      $lstPluginEvt=Plugin::getEventScripts('list',$pluginObjectClass);
      foreach ($lstPluginEvt as $script) {
        require $script; // execute code
      }
      $res=$table;
      $pluginAvoidRecursiveCall=false;
    }
    // Plugin - end
    // In fetchListWithCrit, never store the list : results may always depend on criteria => must fetch every time.
    //self::$list[$listType . "_" . $displayCol]=$res;
    return $res;
  }
  
  public static function getNameFromId($listType, $id, $translate=true) {
    return self::getFieldFromId($listType, $id, 'name', $translate);
  }
  
  public static function getFieldFromId($listType, $id, $field, $translate=true) {
    if ($id==null or $id=='') {
      return '';
    }
    $name=$id;
    $list=self::getListNotTranslated($listType,$field, null, true);
    if (array_key_exists($id,$list)) {
      $name=$list[$id];
      $obj=new $listType();
      if ($translate and $obj->isFieldTranslatable($field)) {
      	$trans=i18n(strtolower($listType) . ucfirst($name));
      	if ($trans=='['.strtolower($listType) . ucfirst($name).']') {
      		$trans=i18n($name);
      	}
        $name=$trans;
      }
    }
    return $name;
  }
 
  public static function getIdFromName($listType, $name, $noCache=false) {
    if ($name==null or $name=='') {
      return '';
    } 
    if ($noCache) {
      $crit=array("name"=>$name);
      $item=SqlElement::getSingleSqlElementFromCriteria($listType, $crit);
      if ($item and $item->id) {
        return $item->id;
      } else {
        return null;
      }
    } else {
      $list=self::getList($listType);      
      $id=array_search($name,$list);
      return $id;
    }
  }
  
  public static function getIdFromTranslatableName($listType, $name) {
    return self::getIdFromName($listType, i18n($name));
  }
  
  public static function getStatusList($class) {
    $result=SqlList::getList('Status');
    if (!property_exists($class,'idStatus')) return array();
    $wfArray=array();
    $type=$class.'Type';
    if (!SqlElement::class_exists($type) or !property_exists($type,'idWorkflow')) return $result;
    $typeObj=new $type();
    $lstType=$typeObj->getSqlElementsFromCriteria(null);
    foreach ($lstType as $tp) {
      if (!$tp->idWorkflow) continue;
      $wfArray[$tp->idWorkflow]=$tp->idWorkflow;
    }
    $allowedStatus=array();
    foreach ($wfArray as $wfId) {
      $stListFrom=SqlList::getListWithCrit('WorkflowStatus',array('idWorkflow'=>$wfId, 'allowed'=>'1'),'idStatusFrom');
      $stListTo=SqlList::getListWithCrit('WorkflowStatus',array('idWorkflow'=>$wfId, 'allowed'=>'1'),'idStatusTo');
      foreach ($stListFrom as $wfs) { $allowedStatus[$wfs]=$wfs;}
      foreach ($stListTo as $wfs) { $allowedStatus[$wfs]=$wfs;}
    }
    foreach ($result as $id=>$name) {
      if (!isset($allowedStatus[$id])) {
        unset($result[$id]);
      }
    }
    return $result;
  }
  
  public static function getFirstId($class) {
    $list=SqlList::getList($class);
    reset($list);
    return key($list);
  }
  public static function formatValWithId($id,$val) {
  	//return "#$id - $val";
  	return "$val [#$id]";
  }
}

?>