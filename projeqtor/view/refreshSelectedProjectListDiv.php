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
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/refreshSelectedProjectListDiv.php'); 

$isChecked = RequestHandler::getValue('isChecked');
$selectedProjectPlan = RequestHandler::getValue('selectedProjectPlan');
if(!$selectedProjectPlan){
  $isChecked='false';
}

?>
<select dojoType="dojox.form.CheckedMultiSelect"  class="selectPlan" multiple="true" style="border:1px solid #A0A0A0;width:initial;height:218px;max-height:218px;"
                  id="idProjectPlan" name="idProjectPlan[]" onChange="changedIdProjectPlan(this.value);"
                  value=" " >
                  <?php if(($selectedProjectPlan == ' ' or $selectedProjectPlan == '*') or $isChecked == 'false'){ ?>
                   <option value=" "><strong><?php echo i18n("allProjects");?></strong></option>
                   <?php }
                      $proj=null; 
                      if (sessionValueExists('project')) {
                          $proj=getSessionValue('project');
                          if(strpos($proj, ",")){
                          	$proj="*";
                          }
                      }
                      if ($proj=="*" or ! $proj) $proj=null;
                      $user=getSessionUser();
                      $wbsList=SqlList::getList('Project','sortOrder',$proj, true );
                      $sepChar=Parameter::getUserParameter('projectIndentChar');
                      if (!$sepChar) $sepChar='__';
                      $wbsLevelArray=array();
                      $projectClause = transformListIntoInClause(getSessionUser()->getListOfPlannableProjects());
                      if($isChecked == 'true'){
                        if(!trim($selectedProjectPlan) or $selectedProjectPlan == '*')$selectedProjectPlan=0;
                      	$projectClause = "(".$selectedProjectPlan.")";
                      }
                      $inClause=" idProject in ". $projectClause;
                      $inClause.=" and idProject not in " . Project::getAdminitrativeProjectList();
                      $inClause.=" and refType= 'Project'";
                      $inClause.=" and idle=0";
                      $order="wbsSortable asc";
                      $pe=new PlanningElement();
                      $list=$pe->getSqlElementsFromCriteria(null,false,$inClause,$order,null,true);
                      foreach ($list as $projOb){
                        if (isset($wbsList[$projOb->idProject])) {
                          $wbs=$wbsList[$projOb->idProject];
                        } else {
                          $wbs='';
                        }
                        $wbsTest=$wbs;
                        $level=1;
                        while (strlen($wbsTest)>3) {
                          $wbsTest=substr($wbsTest,0,strlen($wbsTest)-6);
                          if (array_key_exists($wbsTest, $wbsLevelArray)) {
                            $level=$wbsLevelArray[$wbsTest]+1;
                            $wbsTest="";
                          }
                        }
                        $wbsLevelArray[$wbs]=$level;
                        $sep='';
                        for ($i=1; $i<$level;$i++) {$sep.=$sepChar;}
                        $val = $sep.$projOb->refName;
                        ?>
                        <option value="<?php echo $projOb->idProject; ?>" ><?php if($isChecked == 'true'){ echo '<strong>';}?><?php echo $val; ?><?php if($isChecked == 'true'){ echo '</strong>';}?></option>      
                       <?php
                     }
                   ?>
                 </select>
