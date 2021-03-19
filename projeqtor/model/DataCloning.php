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
require_once ('_securityCheck.php');
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";

class DataCloning extends SqlElement {

  public $id;

  public $name;

  public $nameDir;

  public $idResource;

  public $idRequestor;

  public $versionCode;

  public $idOrigin;

  public $requestedDate;

  public $plannedDate;

  public $deletedDate;

  public $requestedDeletedDate;

  public $codeError;

  public $isRequestedDelete;

  public $isActive;

  public $idle;

  private static $_databaseTableName='dataCloning';

  /**
   * ==========================================================================
   * Constructor
   * 
   * @param $id the
   *          id of the object in the database (null if not stored yet)
   * @return void
   */
  function __construct($id=NULL, $withoutDependentObjects=false) {
    parent::__construct($id, $withoutDependentObjects);
  }

  /**
   * ==========================================================================
   * Destructor
   * 
   * @return void
   */
  function __destruct() {
    parent::__destruct();
  }

  function save() {
    return parent::save();
  }

  function getVersionCodeList() {
    $List=SqlList::getList('DataCloning', 'versionCode');
    if ($List) {
      foreach ($List as $version) {
        $versionList[$version]=$version;
      }
      return $versionList;
    } else {
      return false;
    }
  }

  public static function drawDataCloningList($idUser, $versionCode) {
    $noData=true;
    $dataCloning=new DataCloning();
    $user=getSessionUser();
    $showClosed=Parameter::getUserParameter('dataCloningShowClosed');
    if ($showClosed=='') {
      $showClosed=0;
    }
    $critWhere="";
    if ($versionCode!='') {
      $critWhere.=" and versionCode='".$versionCode."'";
    }
    if ($showClosed==0) {
      $critWhere.=" and idle=".$showClosed;
    }
    $listUser=array();
    if (trim($idUser)) {
      $aff=new Affectable($idUser);
      $listUser[$idUser]=($aff->name)?$aff->name:$aff->userName;
    } else {
      $listUser=getListForSpecificRights('dataCloningRight');
    }
    $res=new Affectable($idUser);
    $date=date('Y-m-d');
    $addDate=addDaysToDate(date('Y-m-d'), 1);
    $wherePerDay="requestedDate > '$date' and requestedDate < '$addDate' ";
    $dataCloningCountPerDay=$dataCloning->countSqlElementsFromCriteria(null, $wherePerDay);
    $dataCloningCountTotal=$dataCloning->countSqlElementsFromCriteria(array("idle"=>"0", "idResource"=>$idUser));
    $dataCloningPerDay=Parameter::getGlobalParameter('dataCloningPerDay');
    $dataCloningTotalObj=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array(
        "scope"=>"dataCloningTotal", 
        "idProfile"=>$res->idProfile));
    $dataCloningTotal=intval($dataCloningTotalObj->rightAccess);
    $hide='none';
    if ($idUser!='') {
      if ($dataCloningTotal-$dataCloningCountTotal>0 and $dataCloningPerDay-$dataCloningCountPerDay>0) {
        $hide='block';
      }
      $dataCloningCount=i18n('colDataCloningCountTotal', array($dataCloningTotal-$dataCloningCountTotal, $dataCloningTotal));
    } else {
      if ($dataCloningPerDay-$dataCloningCountPerDay>0) {
        $hide='block';
      }
      $dataCloningCount=i18n('colDataCloningCount', array($dataCloningPerDay-$dataCloningCountPerDay, $dataCloningPerDay));
    }
    $result="";
    $result.='<div id="dataCloningDiv" align="center" style="margin-top:20px;margin-bottom:20px; overflow-y:auto; width:100%;">';
    $result.='  <table width="98%" style="margin-left:20px;margin-right:20px;border: 1px solid grey;">';
    $result.='   <tr class="reportHeader">';
    $result.='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('colIdUser').'</td>';
    $result.='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:15%;text-align:center;vertical-align:center;">'.i18n('colName').'</td>';
    $result.='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('colVersion').'</td>';
    $result.='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:15%;text-align:center;vertical-align:center;">'.i18n('colOrigin').'</td>';
    $result.='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('colRequestDate').'</td>';
    $result.='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('dataCloningPlannedDate').'</td>';
    $result.='     <td style="border: 1px solid grey;border-right: 1px solid white;height:60px;width:10%;text-align:center;vertical-align:center;">'.i18n('colRequestedDeletedDate').'</td>';
    $result.='     <td style="border: 1px solid grey;height:60px;width:20%;text-align:center;vertical-align:center;">';
    $result.='       <table width="100%"><tr>';
    $result.='         <td width="80%">'.$dataCloningCount.'</td>';
    $result.='         <td width="20%"><a onClick="addDataCloning();" class="imageColorWhite" title="'.i18n('dialogAddDataCloning').'" style="display:'.$hide.'">'.formatBigButton('Add').'</a></td>';
    $result.='       </tr></table>';
    $result.='   </tr>';
    foreach ($listUser as $id=>$name) {
      $where="idResource=".$id." ".$critWhere;
      $listDataCloning=$dataCloning->getSqlElementsFromCriteria(null, null, $where);
      $countLine=0;
      foreach ($listDataCloning as $data) {
        $noData=false;
        $resource=new Resource($data->idResource, true);
        $result.='<tr>';
        if ($countLine==0) {
          $result.='<td style="border-top: 1px solid grey;border-left: 1px solid grey;border-right: 1px solid grey;height:40px;width:10%;text-align:left;vertical-align:center;">';
          $result.='<table align="center"><tr>'.'<td style="text-align:right">'.formatUserThumb($resource->id, $resource->name, null, 22, 'right').'</td>'.'<td style="white-space:nowrap;text-align:left">&nbsp'.$resource->name.'</td></tr>';
          $result.=' </table></td>';
        } else {
          $result.='     <td style="border-left: 1px solid grey;border-right: 1px solid grey;height:40px;width:10%;"></td>';
        }
        $idleColor='';
        if ($data->idle) {
          $idleColor='background-color:#d9d9d9;';
        }
        $result.='<td style="border: 1px solid grey;height:40px;width:15%;text-align:center;vertical-align:center;'.$idleColor.'">';
        $result.='<table width="100%"><tr>';
        if (!$data->idle) {
          $result.='<td width=10%" style="padding-left:10px">';
          if ($data->isActive) {
            $result.='<a onClick="copyDataCloning('.$data->id.');" title="'.i18n('copyDataCloningButton').'" > '.formatMediumButton('Copy').'</a>';
          }
          $result.='</td>';
          $result.='<td width=90%" style="padding-right:42px">'.$data->name.'</td></tr></table></td>';
        } else {
          $result.='<td width=100%">'.$data->name.'</td></tr></table></td>';
        }
        $result.='<td style="border: 1px solid grey;height:40px;width:10%;text-align:center;vertical-align:center;'.$idleColor.'">'.$data->versionCode.'</td>';
        $result.='<td style="border: 1px solid grey;height:40px;width:15%;text-align:center;vertical-align:center;'.$idleColor.'">';
        $result.='<table width="100%"><tr>';
        if ($data->idOrigin and !$data->idle) {
          $origin=new DataCloning($data->idOrigin, true);
          $result.='<td width=10%" style="padding-left:10px">';
          if ($origin->isActive) {
            $dataCloningUrl=Parameter::getGlobalParameter('dataCloningUrl');
            if ($dataCloningUrl) {
              $pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
              if (substr($dataCloningUrl, -1)!=$pathSeparator) $dataCloningUrl.=$pathSeparator;
              $href=$dataCloningUrl.$origin->nameDir.'/view/main.php?directAccess=true';
            } else {
              $href="../simulation/$origin->nameDir/view/main.php?directAccess=true";
            }
            $result.='<a href='.$href.' target="_blank" title="'.i18n('gotoDataCloningButton').'" > '.formatMediumButton('Goto', true).'</a>';
          }
          $result.='</td>';
          $result.='<td width=90%" style="padding-right:42px">'.$origin->name.'</td></tr></table></td>';
        } else {
          $result.='</tr></table></td>';
        }
        $result.='<td style="border: 1px solid grey;height:40px;width:10%;text-align:center;vertical-align:center;'.$idleColor.'">'.htmlFormatDateTime($data->requestedDate).'</td>';
        $plannedDate=$data->plannedDate;
        if (!$data->isActive) {
          $cronExecution=SqlElement::getSingleSqlElementFromCriteria('CronExecution', array(
              'fonctionName'=>'dataCloningCheckRequest'));
          $plannedDate=$cronExecution->nextTime;
        }
        $font='';
        if (!$data->isActive) {
          $font='font-style:italic';
        }
        $result.='<td style="border: 1px solid grey;height:40px;width:10%;text-align:center;vertical-align:center;'.$font.';'.$idleColor.'">'.htmlFormatDateTime(date('Y-m-d H:i:s', $plannedDate)).'</td>';
        if (!$data->idle) {
          $font='font-style:italic';
        }
        $result.='<td style="border: 1px solid grey;height:40px;width:10%;text-align:center;vertical-align:center;'.$font.';'.$idleColor.'">'.htmlFormatDateTime($data->requestedDeletedDate).'</td>';
        $result.='<td style="border: 1px solid grey;height:40px;width:20%;text-align:center;vertical-align:center;">';
        $background='#a3d179';
        $result.='<table width="100%"><tr>';
        if ($data->idle) {
          $background='#d9d9d9';
          $result.='<td width="100%" style="background-color:'.$background.';border-right:1px solid grey;height:40px;">'.i18n('deleteCloningStatus', array(
              htmlFormatDateTime($data->deletedDate))).'</td>';
        } else if ($data->isRequestedDelete) {
          $background='#ffb366';
          $result.='<td width="80%" style="background-color:'.$background.';border-right:1px solid grey;height:40px;">'.i18n('cancelCloningStatus').'</td>';
          $result.='<td width="20%"><a onClick="cancelDataCloningStatus('.$data->id.');" title="'.i18n('cancelDataCloningButton').'" > '.formatMediumButton('Cancel', true).'</a></td>';
        } else if ($data->codeError) {
          $background='#ff7777';
          $result.='<td width="80%" style="background-color:'.$background.';border-right:1px solid grey;height:40px;">'.i18n($data->codeError).'</td>';
          $result.='<td width="20%"><a onClick="refreshDataCloningError('.$data->id.', \''.$data->codeError.'\');" title="'.i18n('refreshDataCloningErrorButton').'" > '.formatMediumButton('Refresh', true).'</a></td>';
        } else {
          if ($data->isActive) {
            $activeText=i18n('activeCloningStatus');
          } else {
            $background='#99ccff';
            $activeText=i18n('requestedCloningStatus');
          }
          $result.='<td width=80%" style="background-color:'.$background.';border-right:1px solid grey;height:40px;">';
          $result.='<table width="100%"><tr>';
          $result.='<td width=10%" style="padding-left:10px">';
          if ($data->isActive) {
            $dataCloningUrl=Parameter::getGlobalParameter('dataCloningUrl');
            if ($dataCloningUrl) {
              $pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
              if (substr($dataCloningUrl, -1)!=$pathSeparator) $dataCloningUrl.=$pathSeparator;
              $href=$dataCloningUrl.$data->nameDir.'/view/main.php?directAccess=true';
            } else {
              $href="../simulation/$data->nameDir/view/main.php?directAccess=true";
            }
            $result.='<a href='.$href.' target="_blank" title="'.i18n('gotoDataCloningButton').'" > '.formatMediumButton('Goto', true).'</a>';
          }
          $result.='</td>';
          $result.='<td width=90%">'.$activeText.'</td></tr></table>';
          $result.='<td width="20%"><a onClick="removeDataCloningStatus('.$data->id.');" title="'.i18n('removeDataCloningButton').'" > '.formatMediumButton('Remove').'</a></td>';
        }
        $result.='</tr></table></td>';
        $countLine++;
      }
    }
    if ($noData==true) {
      $result.='<tr><td colspan="8">';
      $result.='<div style="background:#FFDDDD;font-size:150%;color:#808080;text-align:center;padding:15px 0px;width:100%;">'.i18n('noDataFound').'</div>';
      $result.='</td></tr>';
    }
    $result.='  </table>';
    $result.='</div>';
    echo $result;
  }

  public static function drawDataCloningParameter() {
//     $paramDbType=Parameter::getGlobalParameter('paramDbType');
    $columnList=SqlList::getList('profile');
    echo '<div style="width:100%;">';
    echo '<div id="CrossTable_DataCloning_Right" dojoType="dijit.TitlePane"';
    echo ' title="'.i18n('dataCloningProfileRight').'"';
    echo ' style="width:100%; overflow-x:auto;  overflow-y:hidden;"';
    echo '><br/>';
    echo '<table class="crossTable" >';
    // Draw Header
    echo '<tr><td>&nbsp;</td>';
    foreach ($columnList as $col) {
      echo '<td class="tabLabel">'.$col.'</td>';
    }
    echo '</tr>';
    echo '<tr><td class="crossTableLine"><label class="label largeLabel" style="'.((isNewGui())?'margin-top:-2px':'').'">'.i18n('dataCloningAccess').Tool::getDoublePoint().'</label></td>';
    foreach ($columnList as $colId=>$colName) {
      echo '<td class="crossTablePivot">';
      $crit=array("idProfile"=>$colId, "idMenu"=>"222");
      $checked=SqlElement::getSingleSqlElementFromCriteria('Habilitation', $crit);
      $checked=($checked->allowAccess)?'checked':'';
      echo '<input dojoType="dijit.form.CheckBox" type="checkbox" '.$checked.' id="dataCloningAccess'.$colId.'" name="dataCloningAccess'.$colId.'"/>';
      echo '</td>';
    }
    echo '</tr>';
    echo '<tr><td class="crossTableLine"><label class="label largeLabel" style="'.((isNewGui())?'margin-top:-5px':'').'">'.i18n('dataCloningRight').Tool::getDoublePoint().'</label></td>';
    foreach ($columnList as $colId=>$colName) {
      echo '<td class="crossTablePivot">';
      echo '<select dojoType="dijit.form.FilteringSelect" class="input" ';
      echo autoOpenFilteringSelect();
      echo ' style="width: 100px; font-size: 80%;"';
      echo ' id="dataCloningRight'.$colId.'" name="dataCloningRight'.$colId.'" ';
      echo ' >';
      $crit=array("scope"=>"dataCloningRight", "idProfile"=>$colId);
      $right=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
      echo htmlDrawOptionForReference('idaccessScopeSpecific', $right->rightAccess, null, true);
      echo '</select>';
      echo '</td>';
    }
    echo '<tr><td class="crossTableLine"><label class="label largeLabel" style="'.((isNewGui())?'margin-top:-3px':'').'">'.i18n('dataCloningTotal').Tool::getDoublePoint().'</label></td>';
    foreach ($columnList as $colId=>$colName) {
      echo '<td class="crossTablePivot">';
      $crit=array("scope"=>"dataCloningTotal", "idProfile"=>$colId);
      $paramCreaTotal=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
      $creaTotal=$paramCreaTotal->rightAccess;
      echo '<input dojoType="dijit.form.TextBox" id="dataCloningTotal'.$colId.'" name="dataCloningTotal'.$colId.'" type="number" class="input" style="width: 100px;" value="'.$creaTotal.'" />';
      echo '</td>';
    }
    echo '</tr>';
    
    echo '</tr>';
    echo '</table></div><br/>';
    echo '<div id="CrossTable_DataCloning_GlobalParmeter" dojoType="dijit.TitlePane"';
    echo ' title="'.i18n('menuGlobalParameter').'"';
    echo ' style="width:100%; overflow-x:auto;  overflow-y:hidden;"';
    echo '>';
//     if ($paramDbType=='pgsql') {
//       $endPm=Parameter::getGlobalParameter('endPM');
//       $date=new DateTime();
//       $date->setTimestamp(strtotime($endPm));
//       $date->modify('+60 minute');
//       $endPm=htmlFormatTime(date('H:i', $date->getTimestamp()));
//       $startAm=Parameter::getGlobalParameter('startAM');
//       $date=new DateTime();
//       $date->setTimestamp(strtotime($startAm));
//       $date->modify('-60 minute');
//       $startAm=htmlFormatTime(date('H:i', $date->getTimestamp()));
//       echo '<div class="messageWARNING" style="width:89%; margin-left:5%;margin-right:5%;text-align:center;margin-bottom:0.5%;margin-top:0.2%">'.i18n('pgsqlDataCloningMessage', array($endPm, $startAm)).'</div>';
//     }
    echo '<table class="crossTable" >';
    echo '<tr><td class="crossTableLine"><label class="label largeLabel" style="'.((isNewGui())?'margin-top:-5px':'').'">'.i18n('dataCloningCreationRequest').Tool::getDoublePoint().'</label></td>';
    echo '<td class="crossTablePivot">';
    $disabled='';
//     if ($paramDbType=='pgsql') {
//       $disabled='disabled';
//     }
    echo '<select dojoType="dijit.form.FilteringSelect" class="input" ';
    echo autoOpenFilteringSelect();
    echo ' style="width: 120px; font-size: 80%;"';
    echo ' id="dataCloningCreationRequest" name="dataCloningCreationRequest"';
    echo ' onChange="showSpecificCreationRequest();" '.$disabled.'>';
    $request=SqlElement::getSingleSqlElementFromCriteria('Parameter', array("parameterCode"=>"dataCloningCreationRequest"));
    $request=$request->parameterValue;
//     if ($paramDbType=='pgsql') {
//       $request='specificHours';
//     }
    $selectImmediate=($request!='specificHours')?'selected':'';
    $selectSpecificHours=($request=='specificHours')?'selected':'';
    echo '<option value="immediate" '.$selectImmediate.'>'.i18n('dataCloningImmediate').'</option>';
    echo '<option value="specificHours" '.$selectSpecificHours.'>'.i18n('dataCloningSpecificHours').'</option>';
    echo '</select></td>';
    echo '<td>';
    $display=($request=='specificHours')?'block':'none';
    echo '<div dojoType="dijit.form.TimeTextBox" name="dataCloningSpecificHours" id="dataCloningSpecificHours"
          invalidMessage="'.i18n('messageInvalidTime').'"';
    $cronExecution=SqlElement::getSingleSqlElementFromCriteria('CronExecution', array('fonctionName'=>'dataCloningCheckRequest'));
    if ($request=='specificHours' and strpos($cronExecution->cron, '/')==null) {
      $minutes=substr($cronExecution->cron, 0, 2);
      $hours=substr($cronExecution->cron, 3, -6);
      $min=$hours.':'.$minutes.':00';
    } else {
      $min=Parameter::getGlobalParameter('endPM');
      $date=new DateTime();
      $date->setTimestamp(strtotime($min));
      $date->modify('+30 minute');
      $min=date('H:i:s', $date->getTimestamp());
    }
    echo 'type="text" maxlength="5" style="margin-left:20px;width:40px; text-align: center;display:'.$display.';" class="input rounded"
          value="T'.$min.'" hasDownArrow="false">';
    echo '</div>';
    $display=($request!='specificHours')?'block':'none';
    $specificFrequency=$request;
    echo '<select dojoType="dijit.form.FilteringSelect" class="input"';
    echo autoOpenFilteringSelect();
    echo 'style="width:110px;margin-left:20px;display:'.$display.';" name="dataCloningSpecificFrequency" id="dataCloningSpecificFrequency">';
    $selected=($specificFrequency=='5')?'selected':'';
    echo '<option value="5" '.$selected.'>'.i18n('dataCloningEveryTime').' 5'.i18n('shortMinute').'</option>';
    $selected=($specificFrequency=='10')?'selected':'';
    echo '<option value="10" '.$selected.'>10'.i18n('shortMinute').'</option>';
    $selected=($specificFrequency=='15')?'selected':'';
    echo '<option value="15" '.$selected.'>15'.i18n('shortMinute').'</option>';
    $selected=($specificFrequency=='30')?'selected':'';
    echo '<option value="30" '.$selected.'>30'.i18n('shortMinute').'</option>';
    $selected=($specificFrequency=='60')?'selected':'';
    echo '<option value="60" '.$selected.'>1'.i18n('shortHour').'</option>';
    $selected=($specificFrequency=='120')?'selected':'';
    echo '<option value="120" '.$selected.'>2'.i18n('shortHour').'</option>';
    $selected=($specificFrequency=='240')?'selected':'';
    echo '<option value="240" '.$selected.'>4'.i18n('shortHour').'</option>';
    $selected=($specificFrequency=='360')?'selected':'';
    echo '<option value="360" '.$selected.'>6'.i18n('shortHour').'</option>';
    $selected=($specificFrequency=='720')?'selected':'';
    echo '<option value="720" '.$selected.'>12'.i18n('shortHour').'</option>';
    echo '</select>';
    echo '</td></tr>';
    echo '<tr><td class="crossTableLine"><label class="label largeLabel" style="'.((isNewGui())?'margin-top:-3px':'').'">'.i18n('dataCloningPerDay').Tool::getDoublePoint().'</label></td>';
    echo '<td class="crossTablePivot">';
    $paramPerDay=SqlElement::getSingleSqlElementFromCriteria('Parameter', array("parameterCode"=>"dataCloningPerDay"));
    $creaPerDay=$paramPerDay->parameterValue;
    echo '<input dojoType="dijit.form.TextBox" id="dataCloningPerDay" name="dataCloningPerDay" type="number" class="input" style="width: 100px;" value="'.$creaPerDay.'" />';
    echo '</td>';
    echo '</tr></table>';
    echo '</div></div>';
  }

  public static function createDataCloning($id) {
    global $parametersLocation;
    global $paramDbName;
    global $dbType;
    
    $dataCloning=new DataCloning($id);
    $chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $newPwd=substr(str_shuffle($chars), 0, 6);
    $newPwdBd=strtolower($newPwd);
    
    // COPY FOLDER and CODE
    $dataCloningDirectory=Parameter::getGlobalParameter('dataCloningDirectory');
    if ($dataCloningDirectory and !file_exists($dataCloningDirectory)) {
      errorLog(i18n("dataCloningErrorPathDontExist"));
      $dataCloning->codeError="dataCloningErrorPathDontExist";
      $dataCloning->save();
      return;
    }
    if ($dataCloningDirectory) {
      $pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
      if (substr($dataCloningDirectory, -1)!=$pathSeparator) $dataCloningDirectory.=$pathSeparator;
    }
    if ($dataCloning->idOrigin) {
      $OriginData=new DataCloning($dataCloning->idOrigin);
      if ($dataCloningDirectory) {
        $dir_source=$dataCloningDirectory.$OriginData->nameDir;
        $dir_dest=$dataCloningDirectory.$newPwd;
      } else {
        $dir_source=dirname(__DIR__)."/simulation/".$OriginData->nameDir;
        $dir_dest=dirname(__DIR__).'/simulation/'.$newPwd;
      }
      $parameterP="parameters_".$OriginData->nameDir.".php";
    } else {
      $dir_source=dirname(__DIR__);
      $parameterP="parameters.php";
      if ($dataCloningDirectory) {
        $dir_dest=$dataCloningDirectory.$newPwd;
      } else {
        $dir_dest='../simulation/'.$newPwd;
      }
    }
    
    $nameDir=$newPwd;
    $startMicroTime=microtime(true);
    traceLog($dataCloning->name.' - '.i18n('dataCloningStart'));
    
    // create folder
    enableCatchErrors();
    if (!mkdir($dir_dest, 0777, true)) {
      errorLog(i18n("dataCloningErrorCanNotCreateFolder"));
      $dataCloning->codeError="dataCloningErrorCanNotCreateFolder";
      $dataCloning->save();
      disableCatchErrors();
      return;
    }
    disableCatchErrors();
    // Database
    try {
      $newPwd='simu_'.$newPwdBd;
      $newPwd=strtolower($newPwd);
      $exceptionTable=array(
          "alert",
          "attachment",
          "audit",
          "auditsummary",
          "cronautosendreport",
          "cronexecution",
          "datacloning",
          "history",
          "kpihistory",
          "kpivalue",
          "language",
          "mail",
          "mailtosend",
          "message",
          "messagelegal",
          "messagelegalfollowup",
          "notification",
          "notificationdefinition",
          "projecthistory",
          "statusmail",
          "subscription",
          "translationaccessright",
          "translationcode",
          "translationlanguage",
          "translationvalue");
      
      if ($dataCloning->idOrigin) {
        $PDO=$dataCloning->connexionDbSimu('simu_'.$OriginData->nameDir);
      } else {
        $PDO=Sql::getConnection();
      }
      traceLog($dataCloning->name.' - '.i18n('dataCloningStartDbCopy').' '.$newPwd);
      // pgsql
      if (Parameter::getGlobalParameter('paramDbType')=="pgsql") {
        
//         if (!$dataCloning->idOrigin) {
//           $originDb=$paramDbName;
//         } else {
//           $originDb='simu_'.$OriginData->nameDir;
//         }
//         $sql="SELECT pg_terminate_backend(pg_stat_activity.pid)
//           	    FROM pg_stat_activity
//           	    WHERE pg_stat_activity.datname = '".$originDb."' AND pid <> pg_backend_pid();";
//         $sql2="CREATE DATABASE ".$newPwd." WITH TEMPLATE ".$originDb.";";
//         $PDO->prepare($sql)->execute();
//         $PDO->prepare($sql2)->execute();
//         $exceptionTable="('alert','attachment','audit','auditsummary','cronautosendreport','cronexecution','datacloning','history','kpihistory'
//                   	        ,'kpivalue','language','mail','mailtosend','message','messagelegal','messagelegalfollowup','notification','notificationdefinition'
//                   	        ,'projecthistory','statusmail','subscription','translationaccessright','translationcode','translationlanguage','translationvalue')";
        
//         $sqlDropTable="SELECT table_name
//                           FROM information_schema.tables
//                           WHERE table_schema = 'public'
//                           AND table_name in ".$exceptionTable.";";
//         $PDO3=$dataCloning->connexionDbSimu($newPwd);
//         $sth=$PDO3->prepare($sqlDropTable);
//         $sth->execute();
//         $listTable=$sth->fetchAll(PDO::FETCH_COLUMN, 0);
//         foreach ($listTable as $table) {
//           $sqlTruncateTable="TRUNCATE TABLE ".$table.";";
//           $PDO3->prepare($sqlTruncateTable)->execute();
//         }
//         $connexion=$PDO3;
        
        $sqlCreate="CREATE DATABASE ".$newPwd.";";
        $PDO->prepare($sqlCreate)->execute();
        $sqlCreate = "select relname as tablename
                      from pg_class where relkind in ('r')
                      and relname not like 'pg_%' and relname not like 'sql_%' order by tablename";
        $result_tables = $PDO->query($sqlCreate);
        
        $connexion=$dataCloning->connexionDbSimu($newPwd);
        $sql = '';
        
        foreach($result_tables as $row) {
            $hasPk=false;
            $tableName=$row['tablename'];
      
          $query=" SELECT column_name , data_type , column_default, is_nullable, character_maximum_length, numeric_precision, numeric_scale
                   FROM information_schema.columns
                   WHERE table_schema = 'public' AND table_name = '$tableName'
                   ORDER BY ordinal_position";
          
          $result_create = $PDO->query($query);
          $sql .= 'CREATE TABLE '.$tableName.' ( ';
          foreach ($result_create as $r){
            $field=$r['column_name'];
            $format='';
            $nullable='';
            $default='';
            if ($r['data_type']=="integer" and substr($r['column_default'],0,7)=='nextval'){
                $format = "serial";
            }else if ($r['data_type']=="character varying"){
              $format = "varchar(".$r['character_maximum_length'].")";
   		      }else if ($r['data_type']=="numeric") {
           		      if ($r['numeric_scale']>0) $format.="numeric(".$r['numeric_precision'].",".$r['numeric_scale'].")";
           		          else $format.="numeric(".$r['numeric_precision'].")";
   		      }else {
              $format=$r['data_type'];
            }
   		      if ($format!='serial' and $r['is_nullable']=='NO') {
           		      $nullable=' NOT NULL';
            }
            if ($format!='serial' and $r['column_default']!==null ) {
              $default=' DEFAULT '.str_replace(array('(0)','(1)'),array('0','1'),$r['column_default']);
            }else if ($format=='text') {
              $default=' DEFAULT NULL';
            }
            $sql .= "\n  $field $format";
                if ($nullable) $sql.=$nullable;
                if ($default) $sql.=$default;
                $sql.=",";
          }
          $sql=rtrim($sql, ",");
          $sql .= "\n); ";
          $sql .= "\n \n";
          //INDEX
          $result_index = $PDO->query("SELECT pg_index.indisprimary as ispk, pg_catalog.pg_get_indexdef(pg_index.indexrelid) as indexdef
                                       FROM pg_catalog.pg_class c, pg_catalog.pg_class c2,pg_catalog.pg_index AS pg_index
                                       WHERE c.relname = '$tableName'
                                       AND c.oid = pg_index.indrelid
                                       AND pg_index.indexrelid = c2.oid");
          while($r = $result_index->fetch()) {
            if ($r['ispk']) {
              $t = str_replace("CREATE UNIQUE INDEX", "", $r['indexdef']);
              $t = str_replace("USING btree", "|", $t);
              $t = str_replace(" ON ", "|", $t);
              $Temparray = explode("|", $t);
              $sql .= "ALTER TABLE ONLY ". $Temparray[1] . " ADD CONSTRAINT " .
              $Temparray[0] . " PRIMARY KEY " . $Temparray[2] .";\n";
              $hasPk=true;
            }else{
              $sql .= $r['indexdef'].";\n";
            }
          }
          $sql .= "\n ";
          
          if (in_array($row[0],$exceptionTable)) {
            continue;
          }
          // INSERT ...
          $result_insert = $PDO->query('SELECT * FROM '. $row[0]);
          $sql .= "\n";
          $cpt=0;
          $cptMax=100; // generate an INSERT query containing max $cptMax line
          foreach ($result_insert as $rowInsert){
            $obj_insert = $rowInsert;
            $virgule = false;
            $id=null; $refType=null; $refId=null;
            if ($cpt==0 or $cpt%$cptMax==0) {
              if ($cpt!=0) $sql .= ";\n";
              $sql .="INSERT INTO ". $row[0];
              $cptFld=0;
              foreach($obj_insert as $fld=>$val) {
                if (is_numeric($fld)) continue;
                if ($cptFld==0) $sql.=' (';
                else $sql.=',';
                $cptFld++;
                $sql.=$fld;
              }
              $sql .=") VALUES\n  (";
            }
            else $sql.= ",\n  (";
            $cpt++;
            foreach($obj_insert as $fld=>$val) {
              if (is_numeric($fld)) continue;
              $fld=strtolower($fld);
              $sql .= ($virgule ? ',' : '');
              if(is_null($val)) {
                $sql .= 'NULL';
              } else {
                $sql .= "'". $dataCloning->insert_clean($val,$dbType) . "'";
              }
              $virgule = true;
            } // for
            $sql .= ')';
          }
          if ($cpt>0) $sql .= ";\n";
          //}
          if ($hasPk) {
            $sql.="SELECT setval('".$tableName."_id_seq', (SELECT MAX(id) FROM $tableName));\n";
          }
        }
          $connexion->exec($sql);
      }else{
        $requete="CREATE DATABASE IF NOT EXISTS `".$newPwd."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
        $PDO->query($requete);
        $sql='SHOW TABLE STATUS';
        $result_tables=$PDO->query($sql);
        $sql="";
        $connexion=$dataCloning->connexionDbSimu($newPwd);
        
        foreach ($result_tables as $row) {
          // CREATE ..
          $result_create=$PDO->query('SHOW CREATE TABLE `'.$row['Name'].'`');
          foreach ($result_create as $row) {
            $obj_create=$row;
            $sql.=$obj_create['Create Table'].";\n";
          }
          if ($sql) {
            $connexion->exec($sql);
            $sql="";
          }
//        if (str_replace($exceptionTable, '', $row[0])!=$row[0]) {
//          continue;
//        }
          if (in_array($row[0],$exceptionTable)) {
            continue;
          }
          // INSERT ...
          $sqlInsert="";
          $query='SELECT * FROM `'.$row[0].'`';
          if ($row[0]=='columnselector') {
            $query='SELECT * FROM `'.$row[0].'` WHERE idUser='.$dataCloning->idResource;
          }
          $result_insert=$PDO->query($query);
          $cpt=0;
          $connexion->beginTransaction();
          foreach ($result_insert as $rowInsert) {
            $cpt++;
            $virgule=false;
            $sqlInsert.='INSERT INTO `'.$row[0].'` VALUES (';
            foreach ($rowInsert as $fld=>$val) {
              if (is_numeric($fld)) continue;
              $sqlInsert.=($virgule?',':'');
              if (is_null($val)) {
                $sqlInsert.='NULL';
              } else {
                $sqlInsert.='\''.$dataCloning->insert_clean($val).'\'';
              }
              $virgule=true;
            } // for
            $sqlInsert.=')'.";\n";
            if ($cpt%10==0) {
              $connexion->exec($sqlInsert);
              $sqlInsert="";
            }
            if ($cpt%100==0) {
              $connexion->commit();
              $connexion->beginTransaction();
            }
          }
          if ($sqlInsert) {
            $connexion->exec($sqlInsert);
          }
          $connexion->commit();
        }
      }
      $dbParam=new Parameter();
      $nameDbParam=$dbParam->getDatabaseTableName();
      $requeteDbName="UPDATE ".$nameDbParam." SET parameterValue = ".Sql::str($dataCloning->name)." WHERE parameterCode = 'paramDbDisplayName';";
      $connexion->exec($requeteDbName);
      $dbModule=new Module();
      $moduleDBName=$dbModule->getDatabaseTableName();
      $requestModule="UPDATE ".$moduleDBName." SET active = 0, idle = 1 WHERE name = 'moduleDataCloning';";
      $connexion->exec($requestModule);
      $dbHabilitation=new Habilitation();
      $habilitationDBName=$dbHabilitation->getDatabaseTableName();
      $requestHabilitation="UPDATE ".$habilitationDBName." SET allowAccess = 0 WHERE idMenu = 222 or idMenu = 224;";
      $connexion->exec($requestHabilitation);
      traceLog($dataCloning->name.' - '.i18n('dataCloningFinish').' - '.(round((microtime(true)-$startMicroTime), 1)).' '.i18n('shortSecond'));
    } catch (Exception $e) {
      errorLog(i18n("dataCloningErrorCantCreateDb"));
      $dataCloning->codeError="dataCloningErrorCantCreateDb";
      $dataCloning->save();
      return;
    }
    
    // Copy CODE
    try {
      $dir_iterator=new RecursiveDirectoryIterator($dir_source, RecursiveDirectoryIterator::SKIP_DOTS);
      $iterator=new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
      $exceptionPath=array(
            "/.settings","\\.settings", 
            "/.svn", "\\.svn",
            "/deploy", "\\deploy",
            "/html2pdf/test", "\\html2pdf\\test",
            "/test", "\\test", 
            "/rst/test","\\rst\\test",
            "/.externalToolBuilders", "\\.externalToolBuilders", 
            "/api", "\\api",
            "/db", "\\db",
            "/manual", "\\manual", 
            "/attach", "\\attach",
            "/cron", "\\cron",
            "/documents", "\\documents",
            "/import", "\\import", 
            "/logs", "\\logs",
            "/files/report","\\files\\report");
      $exceptionFile=array("deploy", "test", "api", "db", "manual");
      if (! $dataCloning->idOrigin) {
        $exceptionPath[]="/simulation";
        $exceptionPath[]="\\simulation";
        $exceptionFile[]="simulation";
      }
      $paramIsRelative=false;
      foreach ($iterator as $element) {
        if ($iterator->getSubPathName()=='tool\parameters.php' or $iterator->getSubPathName()=='tool/parameters.php') continue; // Not expected file existing on very old instances
                                                                                                                                // parameter php
        if ($dataCloning->idOrigin) {
          if ($element->getBasename()==$parameterP) {
            $paramIsRelative=true;
            $parameterPhp=$dir_dest.DIRECTORY_SEPARATOR.str_replace($parameterP, "parameters_".$nameDir.".php", $iterator->getSubPathName());
            enableCatchErrors();
            $resCopy=copy($element, $parameterPhp);
            if (!$resCopy) {
              errorLog(i18n("dataCloningErrorCantCreateParameter")." (1.1)");
              $dataCloning->codeError="dataCloningErrorCantCreateParameter";
            }
            $parameterPhp2="../".str_replace($parameterP, "parameters_".$nameDir.".php", $iterator->getSubPathName());
            $paramContext=file_get_contents($parameterPhp);
            $paramDbNameOrigin='simu_'.$OriginData->nameDir;
            $paramDbNameOrigin=strtolower($paramDbNameOrigin);
            $paramDbNameParam="\$paramDbName='$paramDbNameOrigin';";
            $paramDbNameNew='simu_'.$nameDir;
            $paramDbNameNew=strtolower($paramDbNameNew);
            $paramDbNameParamSimu="\$paramDbName='$paramDbNameNew';";
            $paramContext=str_replace($paramDbNameParam, $paramDbNameParamSimu, $paramContext);
            $paramSimuIndexOrigin="\$simuIndex='$OriginData->nameDir';";
            $paramSimuIndexNew="\$simuIndex='$nameDir';";
            $paramContext=str_replace($paramSimuIndexOrigin, $paramSimuIndexNew, $paramContext);
            $resUpdate=file_put_contents($parameterPhp, $paramContext);
            if (!$resUpdate) {
              errorLog(i18n("dataCloningErrorCantCreateParameter")." (1.2)");
              $dataCloning->codeError="dataCloningErrorCantCreateParameter";
            }
            disableCatchErrors();
            continue;
          }
        } else {
          if ($element->getBasename()==$parameterP and (str_replace("plugin", '', $element->getPath())==$element->getPath()) and (str_replace("simulation", '', $element->getPath())==$element->getPath())) {
            $paramIsRelative=true;
            $parameterPhp=$dir_dest.DIRECTORY_SEPARATOR.str_replace("parameters.php", "parameters_".$nameDir.".php", $iterator->getSubPathName());
            enableCatchErrors();
            $resCopy=copy($element, $parameterPhp);
            if (!$resCopy) {
              errorLog(i18n("dataCloningErrorCantCreateParameter")." (2.1)");
              $dataCloning->codeError="dataCloningErrorCantCreateParameter";
            }
            $parameterPhp2="../".str_replace("parameters.php", "parameters_".$nameDir.".php", $iterator->getSubPathName());
            $paramContext=file_get_contents($parameterPhp);
            $paramDbNameParam="\$paramDbName='$paramDbName';";
            $newPwdBd2="simu_".$newPwdBd;
            $paramDbNameParamSimu="\$paramDbName='$newPwdBd2';";
            $paramContext=str_replace($paramDbNameParam, $paramDbNameParamSimu, $paramContext);
            $paramContext.="\n";
            $paramContext.="\$simuIndex='$nameDir';";
            $resUpdate=file_put_contents($parameterPhp, $paramContext);
            if (!$resUpdate) {
              errorLog(i18n("dataCloningErrorCantCreateParameter")." (2.2)");
              $dataCloning->codeError="dataCloningErrorCantCreateParameter";
            }
            disableCatchErrors();
            continue;
          }
        }
        // exception
        $relativePath=substr($element->getPath(),strlen($dir_source));
        if ((str_replace($exceptionPath, '', $relativePath)!=$relativePath) or (substr($element->getBasename(), 0, 1)==".") or (in_array($element->getBasename(), $exceptionFile))) {
          continue;
        }
        // end exception
        
        if ($element->isDir()) {
          enableCatchErrors();
          if (!mkdir($dir_dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName())) {
            errorLog(i18n('dataCloningErrorCantCreateUnderFolder').' => '.$dir_dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
            $dataCloning->codeError="dataCloningErrorCantCreateUnderFolder";
            $dataCloning->save();
            disableCatchErrors();
            return;
          }
        } else {
          if (($element->getBasename()=="parametersLocation.php")) {
            $paramLocation=$dir_dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
            $parametersLocationNewPwd=str_replace("parameters.php", "parameters_".$nameDir.".php", $parametersLocation);
          }
          copy($element, $dir_dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
        }
      }
      enableCatchErrors();
      if (!$paramIsRelative) {
        try {
          $resCopy=copy($parametersLocation, $parametersLocationNewPwd);
          if (!$resCopy) {
            errorLog(i18n("dataCloningErrorCantCreateParameter"));
            $dataCloning->codeError="dataCloningErrorCantCreateParameter";
            $dataCloning->save();
            return;
          }
          $parameterPhp2=$parametersLocationNewPwd;
          $paramContext=file_get_contents($parametersLocationNewPwd);
          $paramDbNameParam="\$paramDbName='$paramDbName';";
          $newPwdBd2="simu_".$newPwdBd;
          $paramDbNameParamSimu="\$paramDbName='$newPwdBd2';";
          $paramContext=str_replace($paramDbNameParam, $paramDbNameParamSimu, $paramContext);
          $paramContext.="\n";
          $paramContext.="\$simuIndex='$dataCloning->name';";
          $resCopy=file_put_contents($parametersLocationNewPwd, $paramContext);
          if (!$resCopy) {
            errorLog(i18n("dataCloningErrorCantCreateParameter"));
            $dataCloning->codeError="dataCloningErrorCantCreateParameter";
            $dataCloning->save();
            return;
          }
        } catch (Exception $e) {
          errorLog(i18n("dataCloningErrorCantCreateParameter"));
          $dataCloning->codeError="dataCloningErrorCantCreateParameter";
          $dataCloning->save();
          return;
        }
      }
      
      if (isset($paramLocation)) {
        kill($paramLocation);
        if (!writeFile(' ', $paramLocation)) {
          showError("impossible to write \'$paramLocation\' file, cannot write to such a file : check access rights");
        }
        kill($paramLocation);
        writeFile('<?php '."\n", $paramLocation);
        if (isset($parameterPhp2)) {
          writeFile('$parametersLocation = \''.$parameterPhp2.'\';', $paramLocation);
        }
      }
    } catch (Exception $e) {
      errorLog(i18n("dataCloningErrorCanNotCopy"));
      $dataCloning->codeError="dataCloningErrorCanNotCopy";
      $dataCloning->save();
      $bdName='simu_'.strtolower($nameDir);
      $sqlRemove="DROP DATABASE $bdName ;";
      $connexion->exec($sqlRemove);
      return;
    }
    
    $dataCloning->isActive=1;
    $dataCloning->nameDir=$nameDir;
    $dataCloning->plannedDate=strtotime(date('Y-m-d H:i:s'));
    $dataCloning->save();
  }

  public static function deleteDataCloning($id) {
    $dataCloning=new DataCloning($id);
    $dataCloningDirectory=Parameter::getGlobalParameter('dataCloningDirectory');
    $codeError=$dataCloning->codeError;
    $startMicroTime=microtime(true);
    $firstDelete=true;
    if ($codeError) $firstDelete=false;
    traceLog(i18n('dataCloningDeleteStart').' - '.$dataCloning->name);
    if ($codeError!='dataCloningErrorDeleteDb') {
      if ($dataCloningDirectory) {
        $pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
        if (substr($dataCloningDirectory, -1)!=$pathSeparator) $dataCloningDirectory.=$pathSeparator;
        $dir=$dataCloningDirectory.$dataCloning->nameDir;
      } else {
        $dir=dirname(__DIR__).'/simulation/'.$dataCloning->nameDir;
      }
      try {
        $dataCloning->remove_dir($dir, $dataCloning);
        if ($dataCloning->codeError) {
          $dataCloning->idle=1;
          $dataCloning->deletedDate=date('Y-m-d H:i');
          $dataCloning->codeError=null;
          $dataCloning->isRequestedDelete=0;
          $dataCloning->isActive=0;
          $dataCloning->save();
        }
      } catch (Exception $e) {
        $dataCloning->codeError="dataCloningErrorDeleteFolder";
        errorLog(i18n('dataCloningErrorDeleteFolder').' - '.$dataCloning->nameDir);
        $dataCloning->isRequestedDelete=0;
        $dataCloning->isActive=0;
        $dataCloning->save();
      }
    }
    
    if ($codeError!='dataCloningErrorDeleteFolder' or $firstDelete) {
      $bdName='simu_'.strtolower($dataCloning->nameDir);
      if (Parameter::getGlobalParameter('paramDbType')=="pgsql") {
        $PDO=$dataCloning->connexionDbSimu(Parameter::getGlobalParameter('paramDbName'));
        $sqlRemove="SELECT pg_terminate_backend(pg_stat_activity.pid)
                	    FROM pg_stat_activity
                	    WHERE pg_stat_activity.datname = '$bdName';";
        $sqlDrop="DROP DATABASE $bdName ;";
        $PDO->exec($sqlRemove);
      } else {
        $PDO=$dataCloning->connexionDbSimu($bdName);
        $sqlDrop="DROP DATABASE $bdName;";
      }
      try {
        $PDO->exec($sqlDrop);
        traceLog($dataCloning->name.' - '.i18n('dataCloningDeleteFinish').' - '.(round((microtime(true)-$startMicroTime), 1)).' '.i18n('shortSecond'));
        $dataCloning->idle=1;
        $dataCloning->deletedDate=date('Y-m-d H:i');
        if ($dataCloning->codeError) $dataCloning->codeError=null;
      } catch (Exception $e) {
        errorLog(i18n('dataCloningErrorDeleteDb'));
        if ($dataCloning->codeError=="dataCloningErrorDeleteFolder") {
          $dataCloning->codeError.="dataCloningErrorDeleteDb";
        } else {
          $dataCloning->codeError="dataCloningErrorDeleteDb";
        }
        $dataCloning->isActive=0;
      }
      $dataCloning->isRequestedDelete=0;
      $dataCloning->save();
    }
  }

  public static function remove_dir($directory, $dataCloning, $empty=false) {
    if (substr($directory, -1)=="/") {
      $directory=substr($directory, 0, -1);
    }
    
    if (!file_exists($directory)||!is_dir($directory)) {
      return false;
    } elseif (!is_readable($directory)) {
      return false;
    } else {
      $directoryHandle=opendir($directory);
      
      while ($contents=readdir($directoryHandle)) {
        if ($contents!='.'&&$contents!='..') {
          $path=$directory."/".$contents;
          
          if (is_dir($path)) {
            $dataCloning->remove_dir($path, $dataCloning);
          } else {
            unlink($path);
          }
        }
      }
      closedir($directoryHandle);
      
      if ($empty==false) {
        if (!rmdir($directory)) {
          return false;
        }
      }
      
      return true;
    }
  }

  public static function connexionDbSimu($dbName) {
    $dbType=Parameter::getGlobalParameter('paramDbType');
    $dbHost=Parameter::getGlobalParameter('paramDbHost');
    $dbPort=Parameter::getGlobalParameter('paramDbPort');
    $dbUser=Parameter::getGlobalParameter('paramDbUser');
    $dbPassword=Parameter::getGlobalParameter('paramDbPassword');
    $dbName=strtolower($dbName);
    if ($dbType!="mysql" and $dbType!="pgsql") {
      $logLevel=Parameter::getGlobalParameter('logLevel');
      if ($logLevel>=3) {
        echo htmlGetErrorMessage("SQL ERROR : Database type unknown '".$dbType."' \n");
      } else {
        echo htmlGetErrorMessage("SQL ERROR : Database type unknown");
      }
      errorLog("SQL ERROR : Database type unknown '".$dbType."'");
      $lastConnectError="TYPE";
      exit();
    }
    enableCatchErrors();
    if ($dbType=="mysql") {
      ini_set('mysql.connect_timeout', 10);
    }
    try {
      $sslArray=array();
      $sslKey=Parameter::getGlobalParameter("SslKey");
      if ($sslKey and !file_exists($sslKey)) {
        errorLog("Error for SSL Key : file $sslKey do not exist");
        $sslKey=null;
      }
      
      $sslCert=Parameter::getGlobalParameter("SslCert");
      if ($sslCert and !file_exists($sslCert)) {
        errorLog("Error for SSL Certification : file $sslCert do not exist");
        $sslCert=null;
      }
      
      $sslCa=Parameter::getGlobalParameter("SslCa");
      if ($sslCa and !file_exists($sslCa)) {
        errorLog("Error for SSL Certification Authority : file $sslCa do not exist");
        $sslCa=null;
      }
      
      if ($sslKey and $sslCert and $sslCa) {
        $sslArray=array(PDO::MYSQL_ATTR_SSL_KEY=>$sslKey, PDO::MYSQL_ATTR_SSL_CERT=>$sslCert, PDO::MYSQL_ATTR_SSL_CA=>$sslCa);
      }
      $sslArray[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;
      $dsn=$dbType.':host='.$dbHost.';port='.$dbPort.';dbname='.$dbName;
      $connexion=new PDO($dsn, $dbUser, $dbPassword, $sslArray);
      $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      // $connexion->setAttribute(PDO::ATTR_TIMEOUT, 500);
      // $connexion->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
      if ($dbType=="mysql" and isset($enforceUTF8) and $enforceUTF8) {
        $connexion->query("SET NAMES utf8");
      }
    } catch (PDOException $e) {
      echo htmlGetErrorMessage($e->getMessage()).'<br />';
    }
    if ($dbType=="mysql") {
      ini_set('mysql.connect_timeout', 60);
    }
    disableCatchErrors();
    $lastConnectError=NULL;
    return $connexion;
  }

  public static function insert_clean($string) {
    $s1=array("\\", "'", "\r", "\n");
    $s2=array("\\\\", "''", '\r', '\n');
    return str_replace($s1, $s2, $string);
  }

}
?>