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

include_once '../tool/projeqtor.php';
include_once '../tool/formatter.php';
//echo 'versionReport.php';

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
    $paramProject=trim($_REQUEST['idProject']);
    Security::checkValidId($paramProject);
}

$paramProduct='';
if (array_key_exists('idProduct',$_REQUEST)) {
    $paramProduct=trim($_REQUEST['idProduct']);
    $paramProduct = Security::checkValidId($paramProduct); // only allow digits
};

$paramVersion='';
if (array_key_exists('idVersion',$_REQUEST)) {
    $paramVersion=trim($_REQUEST['idVersion']);
    $paramVersion = Security::checkValidId($paramVersion); // only allow digits
};

$user=getSessionUser();

// Header
$headerParameters="";
if ($paramProject!="") {
    $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramProduct!="") {
    $headerParameters.= i18n("colIdProduct") . ' : ' . htmlEncode(SqlList::getNameFromId('Product', $paramProduct)) . '<br/>';
}
if ($paramVersion!="") {
    $headerParameters.= i18n("colVersion") . ' : ' . htmlEncode(SqlList::getNameFromId('Version', $paramVersion)) . '<br/>';
}
include "header.php";

$where=getAccesRestrictionClause('Requirement',false);

$order="";

if ($paramProject) {
    $lstProject=array($paramProject=>SqlList::getNameFromId('Project',$paramProject));
    $where.=" and idProject=".Sql::fmtId($paramProject);
} else {
    $lstProject=SqlList::getList('Project','name',null,true);
    $lstProject[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramProduct) {
    $lstProduct=array($paramProduct=>SqlList::getNameFromId('Product',$paramProduct));
    $where.=" and idProduct=".Sql::fmtId($paramProduct);
} else {
    $lstProduct=SqlList::getList('Product','name',null,true);
    $lstProduct[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramVersion) {
    $lstVersion=array($paramVersion=>SqlList::getNameFromId('Version',$paramVersion));
    $where.=" and idVersion=".Sql::fmtId($paramVersion);
} else {
    $lstVersion=SqlList::getList('Version','name',null,true);
    $lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';
}

$rq=new Requirement();
$lst=$rq->getSqlElementsFromCriteria(null, false, $where,'idProject, idProduct, idVersion, id');

if (checkNoData($lst)) if (!empty($cronnedScript)) goto end; else exit;

foreach ($lst as $rq) {
    $st=new Status($rq->idStatus);
    if(!$rq->done and !$rq->idle){
        $l=new Link();
        $containsOpenQuestion = false;
        $nbQuestion = 0;
        $crit=array('ref2Type'=>'Requirement','ref2Id'=>$rq->id);
        $lstl=$l->getSqlElementsFromCriteria($crit,true,null,'id');
        if(count($lstl)>0){
            foreach ($lstl as $l){
                if($l->ref1Type=='Question'){
                    $q=new Question($l->ref1Id);
                    $st=new Status($q->idStatus);
                    if($st->setIdleStatus==0){
                        $containsOpenQuestion = true;
                    }
                }
            }
            if($containsOpenQuestion){
                echo '<table style="width:' . ((isset($outMode) and $outMode=='pdf')?'80':'95') . '%" align="center">';
                echo '<tr>';
                echo '<td class="reportTableHeader" style="width:20%" >' . i18n('Project') . '</td>';
                if($rq->idTargetProductVersion==null){
                    echo '<td class="reportTableHeader" style="width:20%" >' . i18n('Product') . '</td>';
                }else{
                    echo '<td class="reportTableHeader" style="width:20%" " >' . i18n('Version') . '</td>';
                }
                echo '<td class="reportTableHeader" style="width:60%" colspan="3"  >'.i18n('Requirement').'</td>';
                echo '</tr><tr></tr>';
                echo '<tr>';
                echo '<td class="reportTableData" style="width:20%">' . (($rq->idProject)?$lstProject[$rq->idProject]:'') . '</td>';
                if($rq->idTargetProductVersion==null){
                    echo '<td class="reportTableData" style="width:20%">' . (($rq->idProduct)?$lstProduct[$rq->idProduct]:'') . '</td>';
                }else{
                    echo '<td class="reportTableData" style="width:20%">' . (($rq->idTargetProductVersion)?$lstVersion[$rq->idTargetProductVersion]:'') . '</td>';
                }
                echo '<td class="reportTableData" style="width:10%">#' . htmlEncode($rq->id) . '</td>';
                echo '<td class="reportTableData" style="text-align:left;width:38%;">' . htmlEncode($rq->name) . '</td>';
                $st = new Status($rq->idStatus);
                echo '<td class="reportTableData" style="text-align:left;width:12%">'. (($rq->id)?colorNameFormatter($st->name . '#split#' . $st->color):'') . '</td>';
                echo '</tr>';
                foreach ($lstl as $l){
                    $q=new Question($l->ref1Id);
                    $st=new Status($q->idStatus);
                    if(!$q->done and !$q->idle){
                        $nbQuestion ++;
                        echo '<tr><td></td><td colspan="4">';
                        echo '<table style="width:100%">';
                        if($nbQuestion == 1){
                            echo '<tr>';
                            echo '<td class="largeReportHeader" colspan="2" style="width:85%">'.i18n('Question').'</td>';
                            echo '<td class="largeReportHeader" colspan="2" style="width:15%">'.i18n('Status').'</td>';
                            echo '</tr>';
                        }
                        echo '<tr>';
                        echo '<td class="largeReportData" style="text-align: center;width:8%">' . (($q->id)?'#':'') . $q->id . '</td>';
                        echo '<td class="largeReportData" style="width:77%" >' . htmlEncode($q->name) . '</td>';
                        echo '<td class="largeReportData" style="text-align: left;width:15%" >' . (($q->id)?colorNameFormatter($st->name . '#split#' . $st->color):'') . '</td>';
                        echo '</tr>';
                        echo '</table>';
                        echo '</td></tr>';
                    }
                }
                echo '</table>';
                echo '<br/>';
            }
        }
    }
}

end:
