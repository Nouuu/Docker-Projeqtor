<?php
/*
 * @author: qCazelles 
 */
//Adds dynamic filter clause in user session
//Called by selectDynamicFilter() (projeqtorDialog.js) in tool/dynamicDialogDynamicFilter.php
//Adds the dynamic values for the filter now active

require_once "../tool/projeqtor.php";

$user=getSessionUser();

$comboDetail=false;
if (array_key_exists('comboDetail',$_REQUEST)) {
	$comboDetail=true;
}

if (! $comboDetail and ! $user->_arrayFilters) {
	$user->_arrayFilters=array();
} else if ($comboDetail and ! $user->_arrayFiltersDetail) {
	$user->_arrayFiltersDetail=array();
}

$idFilter=RequestHandler::getId('idFilter',true);
$filterObjectClass=RequestHandler::getValue('filterObjectClass',true);
if (!isset($objectClass) or !$objectClass) $objectClass=$filterObjectClass;
if ($objectClass=='Planning' or $objectClass=='GlobalPlanning' or $objectClass=='VersionsPlanning' or $objectClass=='ResourcePlanning') $objectClass='Activity';
else if (substr($objectClass,0,7)=='Report_') $objectClass=substr($objectClass,7);
Security::checkValidClass($objectClass);

$nbDynamicFilterClauses=RequestHandler::getNumeric('nbDynamicFilterClauses',true);

$filter=new Filter($idFilter);
$obj=new $objectClass();

// Get existing filter info
if (!$comboDetail and array_key_exists($filterObjectClass,$user->_arrayFilters)) {
	$filterArray=$user->_arrayFilters[$filterObjectClass];
} else if ($comboDetail and array_key_exists($filterObjectClass,$user->_arrayFiltersDetail)) {
	$filterArray=$user->_arrayFiltersDetail[$filterObjectClass];
} else {
	$filterArray=array();
}

//Define which clauses (dynamic) must be rewrite with values
$dynamicClauses=array();
if (!empty($filterArray)) {
	foreach ($filterArray as $key => $crit) {
		if (isset($crit['isDynamic']) and $crit['isDynamic']=='1') {
			$dynamicClauses[]=$key;
		}
	}
}

for ($i=0;$i<$nbDynamicFilterClauses;$i++) {
	
	$idFilterAttribute=$_REQUEST['idFilterAttribute'.$i];
	$idFilterOperator=$_REQUEST['idFilterOperator'.$i];
	$filterDataType=RequestHandler::getValue('filterDataType'.$i);
	
	$orOperator=RequestHandler::getValue('orOperator'.$i);
	
	$filterValue="";
	if ( array_key_exists('filterValue'.$i,$_REQUEST)) {
		$filterValue=$_REQUEST['filterValue'.$i];
	}
	
	$filterValueDate="";
	if ( array_key_exists('filterValueDate'.$i,$_REQUEST)) {
		$filterValueDate=$_REQUEST['filterValueDate'.$i];
	}
	
	$filterValueList="";
	//$_REQUEST['filterValueList'.$i] is an array
	if (array_key_exists('filterValueList'.$i,$_REQUEST)) {
		$filterValueList=$_REQUEST['filterValueList'.$i];
	}
	
	$filterValueCheckbox=false;
	if (array_key_exists('filterValueCheckbox'.$i,$_REQUEST)) {
		$filterValueCheckbox=true;
	}
	
	// Add new filter
	$arrayDisp=array();
	$arraySql=array();
	$dataType=$obj->getDataType($idFilterAttribute);
	$dataLength=$obj->getDataLength($idFilterAttribute);
	$pos=strpos($idFilterAttribute, "__id");
	if ($pos>0) {
	  $dataType='int';
	  $dataLength='12';
	  $foreignKey=foreignKeyWithoutAlias($idFilterAttribute);
	  $foreignKeyName=i18n(substr($idFilterAttribute,0,$pos));
	  //$idFilterAttribute=$foreignKey;
	}
	$split=explode('_',$idFilterAttribute);
	if (count($split)>1 and strpos($idFilterAttribute, "__id")<0) {
		$externalClass=$split[0];
		$externalObj=new $externalClass();
		$arrayDisp["attribute"]=$externalObj->getColCaption($split[1]);
	} else {
		if (substr($idFilterAttribute,0,9)=='idContext') {
			$arrayDisp["attribute"]=SqlList::getNameFromId('ContextType',substr($idFilterAttribute,9));
		} else {
			$arrayDisp["attribute"]=$obj->getColCaption($idFilterAttribute);
		}
	}
	
	$arraySql["attribute"]=$obj->getDatabaseColumnName($idFilterAttribute);
	if (($idFilterOperator=="=" or $idFilterOperator==">=" or $idFilterOperator=="<="  or $idFilterOperator=="<>") and $filterDataType!="intDate") {
		$arrayDisp["operator"]=$idFilterOperator;
		$arraySql["operator"]=$idFilterOperator;
		if ($filterDataType=='date') {
			$arrayDisp["value"]="'" . htmlFormatDate($filterValueDate) . "'";
			$arraySql["value"]="'" . $filterValueDate . "'";
		} else if ($filterDataType=='bool') {
			$arrayDisp["value"]=($filterValueCheckbox)?i18n("displayYes"):i18n("displayNo");
			$arraySql["value"]=($filterValueCheckbox)?1:0;
		} else {
			$arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
			$arraySql["value"]="'" . trim(Sql::str(htmlEncode($filterValue)),"'") . "'";
		}
	} else if (($idFilterOperator=="LIKE" or $idFilterOperator=="ILIKE") and $filterDataType=="varcharStartBy") {
	  $arrayDisp["operator"]=i18n('startBy'); //TRANSLATION qCazelles
	  $arraySql["operator"]=(Sql::isMysql())?"LIKE":"ILIKE";
	  $arrayDisp["value"]="'".htmlEncode($filterValue)."'";
	  $arraySql["value"]="'".htmlEncode($filterValue)."%'";	
	} else if (($idFilterOperator=="LIKE" or $idFilterOperator=="ILIKE" or $idFilterOperator=="hasSome" or $idFilterOperator==" exists ") and $filterDataType!="varcharStartBy") {
		if ($filterDataType=='refObject' or $idFilterOperator=="hasSome" or $idFilterOperator==" exists ") {
			$arraySql["operator"]=' exists ';
			if ($idFilterOperator=="hasSome") {
				$filterValue="";
				$arrayDisp["value"]="";
				$arrayDisp["operator"]=i18n("isNotEmpty");
			} else {
				$arrayDisp["operator"]=i18n("contains");
				$arrayDisp["value"]="'" . trim(Sql::str(htmlEncode($filterValue)),"'") . "'";
			}
			Security::checkValidClass($idFilterAttribute);
			$refObj=new $idFilterAttribute();
			$refObjTable=$refObj->getDatabaseTableName();
			$table=$obj->getDatabaseTableName();
			$arraySql["value"]=" ( select 'x' from $refObjTable "
			. " where $refObjTable.refType=".Sql::str($filterObjectClass)." "
					. " and $refObjTable.refId=$table.id "
					. " and $refObjTable.note ".((Sql::isMysql())?'LIKE':'ILIKE')." '%" . trim(Sql::str(htmlEncode($filterValue)),"'") . "%' ) ";
		} else {
			$arrayDisp["operator"]=i18n("contains");
			$arraySql["operator"]=(Sql::isMysql())?'LIKE':'ILIKE';
			$arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
			$arraySql["value"]="'%" . trim(Sql::str(htmlEncode($filterValue)),"'") . "%'";
		}
	} else if ($idFilterOperator=="NOT LIKE" or $idFilterOperator=="NOT ILIKE") {
		$arrayDisp["operator"]=i18n("notContains");
		$arraySql["operator"]=(Sql::isMysql())?'NOT LIKE':'NOT ILIKE';
		$arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
		$arraySql["value"]="'%" . trim(Sql::str(htmlEncode($filterValue)),"'") . "%'";
	} else if ($idFilterOperator=="IN" or $idFilterOperator=="NOT IN") {
		$arrayDisp["operator"]=($idFilterOperator=="IN")?i18n("amongst"):i18n("notAmongst");
		$arraySql["operator"]=$idFilterOperator;
		$arrayDisp["value"]="";
		$arraySql["value"]="(";
		foreach ($filterValueList as $key=>$val) {
			$arrayDisp["value"].=($key==0)?"":", ";
			$arraySql["value"].=($key==0)?"":", ";
			$arrayDisp["value"].="'" . Sql::fmtStr(SqlList::getNameFromId(Sql::fmtStr(substr($idFilterAttribute,2)),$val)) . "'";
			$arraySql["value"].=Security::checkValidId($val);
		}
		$arraySql["value"].=")";
		if ($idFilterAttribute=="assignedResource__idResourceAll") {
		  $arraySql["operator"]=' exists ';
		  $ass=new Assignment();
		  $assTable=$ass->getDatabaseTableName();
		  $obj=new $objectClass();
		  $objTable=$obj->getDatabaseTableName();
		  $arraySql["value"]="(select 'x' from $assTable where $assTable.refType='$objectClass' and $assTable.refId=$objTable.id and $assTable.idResource $idFilterOperator ".$arraySql["value"].")";
		}
	} else if ($idFilterOperator=="<=" and $filterDataType=="intDate") {
		$arrayDisp["operator"]="<= " . i18n('today') . (($filterValue>0)?' +':' ');
		$arraySql["operator"]="<=";
		$arrayDisp["value"]=htmlEncode(intval($filterValue)) . ' ' . i18n('days');
		if (preg_match('/[^\-0-9]/', $filterValue) == true) {
			$filterValue="";
		}
		if (Sql::isPgsql()) {
			$arraySql["value"]= "CURRENT_DATE + INTERVAL '" . intval($filterValue) . " day 23 hours 59 minutes'";
		} else {
			$arraySql["value"]= "ADDDATE(addtime(DATE(NOW()), '23:59:59'), INTERVAL (" . intval($filterValue) . ") DAY)";
		}
	} else if ($idFilterOperator==">=" and $filterDataType=="intDate") {
		$arrayDisp["operator"]=">= " . i18n('today') . (($filterValue>0)?' +':' ');
		$arraySql["operator"]=">=";
		$arrayDisp["value"]=htmlEncode(intval($filterValue)) . ' ' . i18n('days');
		if (preg_match('/[^\-0-9]/', $filterValue) == true) {
			$filterValue="";
		}
		if (Sql::isPgsql()) {
			$arraySql["value"]= "NOW() + INTERVAL '" . intval($filterValue) . " day'";
		} else {
			$arraySql["value"]= "ADDDATE(DATE(NOW()), INTERVAL (" . intval($filterValue) . ") DAY)";
		}	
	} else {
		echo htmlGetErrorMessage(i18n('incorrectOperator'));
		exit;
	}
	$filterArray[$dynamicClauses[$i]]=array("disp"=>$arrayDisp,"sql"=>$arraySql,"isDynamic"=>"0","orOperator"=>$orOperator);

}

if (! $comboDetail) {
	$user->_arrayFilters[$filterObjectClass]=$filterArray;
	$user->_arrayFilters[$filterObjectClass . "FilterName"]=$filter->name;
} else {
	$user->_arrayFiltersDetail[$filterObjectClass]=$filterArray;
	$user->_arrayFiltersDetail[$filterObjectClass . "FilterName"]=$filter->name;
}

// save user (for filter saving)
setSessionUser($user);

?>

