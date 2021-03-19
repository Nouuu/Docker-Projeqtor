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
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
  require_once "../tool/projeqtor.php";
  require_once "../tool/formatter.php";
  scriptLog('   ->/view/startGuide.php');  
  $user=getSessionUser();
  
 echo '<div style="height:100%;padding:10px;overflow:auto;position:relative;">';
 echo '<div class="siteH1">'.i18n('startGuideTitle').'</div>';
 echo '<br/>';
 
 echo i18n('startGuideIntro');
 echo '<br/>';
 
 $arrayItem=array('menuEnvironmentalParameter', 'Client','Contact', 'Resource', 'User',
   'menuWork', 'Project', 'Affectation', 'Activity', 'Milestone', 'Planning', 'Imputation', 'Ticket');
 
 $progress=0;
 $total=0;
 echo '<table style="width:100%">';
 foreach ($arrayItem as $item) {
   if (substr($item,0,4)=='menu') {
     echo '<tr class="siteH2">';
     echo '<td colspan="5">';
     echo '<b>'.i18n("startGuideActionMenu").' "'.i18n($item).'"</b>';
     echo '</td></tr>';
     echo '<tr><td colspan="5">';
     echo '<br/>';
     echo '</td></tr>';
   } else {
     $total++;
     $hideAutoloadError=true; // Avoid error message is autoload
     $is_object=SqlElement::class_exists($item,true);
     $hideAutoloadError=false;
     $canRead=(securityGetAccessRightYesNo('menuUser', 'read') == "YES");
     echo '<tr VALIGN="top" style="padding:0;margin:0;white-space:nowrap">';
     echo '<td class="siteH2" style="text-align:right;">&nbsp;&nbsp;&nbsp;'.i18n("startGuideActionCreate")." ".i18n('menu'.$item).'</td>';
     echo '<td style="position: relative; padding-left:10px;top:-15px;width:50px">&nbsp;&nbsp;&nbsp;';
     if ($canRead) {
       echo '<span style="cursor:pointer; position: relative;top:-8px; margin-left:10px;" onClick="loadMenuBar'.(($is_object)?'Object':'Item').'(\'' . $item .  '\',null,\'bar\');" >';
     }
     echo formatIcon($item, 32);
     echo '</span>';
     echo '&nbsp;&nbsp;&nbsp;';
     if ($item=='Planning') {
       $obj=new PlannedWork();
       $nbItem=0;
       $nb=$obj->countGroupedSqlElementsFromCriteria(array(), array('idProject'), "1=1");
       if ($nb) {
         $nbItem=count($nb);
       }
     } else if ($item=='Imputation') {
       $obj=new PlannedWork();
       $nbItem=0;
       $nb=$obj->countGroupedSqlElementsFromCriteria(array(), array('week','idResource'), "1=1");
       if ($nb) {
         $nbItem=count($nb);
       }
     } else {
        $obj=new $item();
        $crit=array();
        if ($item=='Contact' or $item=='Resource' or $item=='User') {
          $crit['is'.$item]='1';
        }
        $nbItem=$obj->countSqlElementsFromCriteria($crit);
     }
     echo '</td><td style="position: relative; padding-left:10px;top:-5px;width:50px">';
     if ($nbItem==0 or ($item=='User' and $nbItem<=2) ) {
       echo '<img src="css/images/iconStartGuideTodo.png" />&nbsp;&nbsp;&nbsp;</td>';
       echo '<td VALIGN="middle" colspan="2" style="white-space:normal">';
     } else {
       echo '<img src="css/images/iconStartGuideDone.png"/>&nbsp;&nbsp;&nbsp;</td>';
       echo '<td class="siteH2" style="white-space:nowrap;">'.$nbItem." ";
       if ($item=='Planning') {
         echo i18n('plannedProjects');
       } else {
         echo i18n('menu'.$item);
       }
       echo '&nbsp;&nbsp;</td><td VALIGN="middle" style="white-space:normal; color:#a6a0bc;vertical-align:top;padding-top:2px;">';
       $progress++;
     }
     $help=i18n("startGuide".$item);
     if (substr($help,0,1)!='[') {
       echo i18n("startGuide".$item);
     }
     echo '</td></tr>';
     echo '<tr><td colspan="4">&nbsp;</td></tr>';
   }
 }
 echo "</table>";
 
 echo '<br/>';
 
 echo i18n('startGuideFooter');
 echo '<br/>';
 echo '<br/>';
 echo '<br/>';
 echo '<div style="position:absolute; right:10px; top:0px; ">';
 $progressVal=round($progress/$total*100,0);
 echo '<div class="siteH2" style="white-space:nowrap">'.progressFormatter($progressVal,i18n('progress')."&nbsp;:&nbsp; ").'</div>';
 echo '<br/>'.i18n('showThisPageOnStart').'&nbsp;';
 echo '<div dojoType="dijit.form.CheckBox" checked type="checkbox" id="showOnStart">';
 echo '<script type="dojo/method" event="onChange" >';
 echo ' if (this.checked) {';
 echo '   saveUserParameter("startPage", "startGuide.php");';
 echo ' } else {';
 echo '   saveUserParameter("startPage", "today.php");';
 echo '   showInfo(i18n("showThisPageUnckeck"));';
 echo ' }';
 echo '</script>';
 echo '</div>';
 echo '</div>';
 echo '</div>'
 /*
  "*/
?>