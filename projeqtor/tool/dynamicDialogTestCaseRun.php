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
$mode="";
$testCaseRunId=null;
if ( array_key_exists('testCaseRunId',$_REQUEST) ) {
  $testCaseRunId=$_REQUEST['testCaseRunId'];
  Security::checkValidId($testCaseRunId);
  $mode='edit';
} else {
  $mode='add';
}
$testSessionId=0;
if ( array_key_exists('testSessionId',$_REQUEST)) {
	$testSessionId=$_REQUEST['testSessionId'];
}

Security::checkValidId($testSessionId);

$session=new TestSession($testSessionId);
$idProject=$session->idProject;
$idProduct=$session->idProduct;
$testCaseRun=new TestCaseRun($testCaseRunId);
if(!$testCaseRun->idRunStatus){
  $testCaseRun->idRunStatus=1;
}
if (isset($_REQUEST['runStatusId'])) {
  $testCaseRun->idRunStatus=intval($_REQUEST['runStatusId']);
}

$selected="";
if (array_key_exists('selected', $_REQUEST)) {
  $selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected); // Note: elements are validated to be numeric in SqlElement base constructor
$obj=new TestCase();

$crit = array ( 'idle'=>'0');
if (trim($idProject)) {
  $crit['idProject']=$idProject;
}
if (trim($idProduct)) {
  if (property_exists($obj,'idProduct')) $crit['idProduct']=$idProduct;
  else if (property_exists($obj,'idProductOrComponent')) $crit['idProductOrComponent']=$idProduct;
  else if (property_exists($obj,'idComponent')) $crit['idComponent']=$idProduct;
}

$list=$obj->getSqlElementsFromCriteria($crit,false,null,null,true);
foreach ($selectedArray as $selected) {
  if ($selected and ! array_key_exists("#" . $selected, $list)) {
    $list["#".$selected]=new TestCase($selected);
  }
}
if ($mode=='add'){
$tcr=new TestCaseRun();
$listTcr=$tcr->getSqlElementsFromCriteria(array('idTestSession'=>$testSessionId),false,null,'sortOrder desc');
if (count($listTcr)) {
  $testCaseRun->sortOrder=(reset($listTcr)->sortOrder)+10;
} else {
  $testCaseRun->sortOrder=10;
}
}

$selectedProject=getSessionValue('project');
if(strpos($selectedProject, ",")){
	$selectedProject="*";
}
?>

  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='testCaseRunForm' name='testCaseRunForm' onSubmit="return false;">
         <input id="testCaseRunId" name="testCaseRunId" type="hidden" value="<?php echo $testCaseRunId;?>" />
         <input id="testCaseRunTestSession" name="testCaseRunTestSession" type="hidden" value="<?php echo $testSessionId;?>" />
         <input id="testCaseRunMode" name="testCaseRunMode" type="hidden" value="<?php echo $mode;?>"  />
         <?php if ($mode=='add') {?>
         <input type="hidden" id="testCaseRunStatus" name="testCaseRunStatus" value="1" />
         <div id="testCaseRunAddDiv">
	         <table>
	           <tr>
	             <td class="dialogLabel" >
	               <label for="testCaseRunTestCaseList" ><?php echo i18n("colTestCases") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
	             </td>
	             <td>
	               <div id="testCaseRunListDiv" dojoType="dijit.layout.ContentPane" region="center">
                    <select xdojoType="dijit.form.MultiSelect" multiple
                      id="testCaseRunTestCaseList" name="testCaseRunTestCaseList[]" 
                      class="selectList" required="required" size="10"
                      onchange="enableWidget('dialogTestCaseRunSubmit');"  
                      ondblclick="saveTestCaseRun();" >                    
                     <?php
                     //foreach ($list as $lstObj) {
                       //echo "<option value='$lstObj->id'" . ((in_array($lstObj->id,$selectedArray))?' selected ':'') . ">#".htmlEncode($lstObj->id)." - ".htmlEncode($lstObj->name)."</option>";
                     //}?>
                     <?php htmlDrawOptionForReference('idTestCase',null, null, true); ?>
                    </select>
	               </div>
	             </td>
	             <td style="vertical-align: top">
	               <button id="testCaseRunTestCaseDetailButton" dojoType="dijit.form.Button" showlabel="false"
	                 title="<?php echo i18n('showDetail');?>"
	                 iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                   <?php $createRight=(securityGetAccessRightYesNo('menuTestCase', 'create')=='YES')?'1':'0';?>
	                 <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("testCaseRunTestCaseList", "<?php echo $createRight;?>","TestCase",true); 
                   </script>
	               </button>
	             </td>
	           </tr>
             <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
             <tr>
               <td class="dialogLabel" >
                 <label for="testCaseRunAllowDuplicate" ><?php echo i18n("colAllowDuplicate") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
               </td>
               <td>
                 <div id="testCaseRunAllowDuplicate" name="testCaseRunAllowDuplicate"
                   dojoType="dijit.form.CheckBox" type="checkbox" >
                 </div>
                 <?php echo i18n("colAllowDuplicateTestInSession");?>
               </td>    
             </tr>
	         </table>
         </div>         
         <?php } else if ($mode=='edit') { ?> 
           <div id="testCaseRunEditDiv">  
	         <table>
	           <tr>
	             <td class="dialogLabel"  >
	               <label for="testCaseRunTestCase" ><?php echo i18n("colTestCase") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
	             </td>
	             <td>
	               <select dojoType="dijit.form.FilteringSelect" 
	               <?php echo autoOpenFilteringSelect();?>
	                id="testCaseRunTestCase" name="testCaseRunTestCase" 
	                class="input" size="10">
	                <?php htmlDrawOptionForReference('idTestCase',$testCaseRun->idTestCase, null, true); ?>
	               </select>
	             </td>
	           </tr>
	           <tr>
	             <td class="dialogLabel"  >
	               <label for="testCaseRunStatus" ><?php echo i18n("colIdStatus") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
	             </td>
	             <td>
	               <select dojoType="dijit.form.FilteringSelect" 
	               <?php echo autoOpenFilteringSelect();?>
	                id="testCaseRunStatus" name="testCaseRunStatus" 
                  onchange="testCaseRunChangeStatus();"
	                class="input" required="required">	                
	                <?php htmlDrawOptionForReference('idRunStatus',$testCaseRun->idRunStatus, null, true); ?>
	               </select>
	             </td>
	           </tr>
	         </table>  
	         <div id='testCaseRunTicketDiv' >
		         <table>
		          <tr>
		             <td class="dialogLabel"  >
		               <label for="testCaseRunTicket" ><?php echo i18n("colTicket") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
		             </td>
		             <td>
		               <select dojoType="dijit.form.FilteringSelect" 
		               <?php echo autoOpenFilteringSelect();?>
		                id="testCaseRunTicket" name="testCaseRunTicket"
		                class="input"><?php ($idProject)?htmlDrawOptionForReference('idTicket',$testCaseRun->idTicket, null, false,'idProject',$idProject):htmlDrawOptionForReference('idTicket',$testCaseRun->idTicket, null, false,'idProject',$selectedProject); ?>
		               </select>
		             </td>
                 <td style="vertical-align: top">
	                 <?php
	                 $readRight=(securityGetAccessRightYesNo('menuTicket', 'create')=='YES')?'1':'0'; 
	                 if ($readRight) {
	                   $createRight=(securityGetAccessRightYesNo('menuTicket', 'create')=='YES')?'1':'0';?>
                   <button id="testCaseRunTicketDetailButton" dojoType="dijit.form.Button" showlabel="false"
	                   title="<?php echo i18n('showDetail');?>"
	                   iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">	                   
	                   <script type="dojo/connect" event="onClick" args="evt">
                      showDetail("testCaseRunTicket", "<?php echo $createRight;?>","Ticket"); 
                   </script>
	                 </button>
                   <?php }?>
                </td>
		           </tr>
		         </table>
		       </div>
	         
	         
         </div>
         <?php }?>
         <table>
             <tr>
	             <td class="dialogLabel" >
	               <label for="testCaseRunResult" ><?php echo i18n("colResult");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
	             </td>
	             <td>
	                <textarea dojoType="dijit.form.Textarea"
	                          id="testCaseRunResult" name="testCaseRunResult"
	                          style="width: 400px;"
	                          maxlength="4000"
	                          class="input"><?php echo $testCaseRun->result;?></textarea>                                       
	             </td>    
	           </tr>
	           <tr>
	             <td class="dialogLabel" >
	               <label for="testCaseRunComment" ><?php echo i18n("colComment");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
	             </td>
	             <td>
	                <textarea dojoType="dijit.form.Textarea"
	                          id="testCaseRunComment" name="testCaseRunComment"
	                          style="width: 400px;"
	                          maxlength="4000"
	                          class="input"><?php echo $testCaseRun->comment;?></textarea>                                       
	             </td>    
	           </tr>
	           <tr>
                <td class="dialogLabel" >
                  <label for="dialogTestCaseRunSortOrder" ><?php echo i18n('colSortOrder');?> : </label>
                </td>
                <td>
                  <input type="text" dojoType="dijit.form.NumberTextBox" 
                  id="dialogTestCaseRunSortOrder" 
                  name="dialogTestCaseRunSortOrder"                 
                  value="<?php echo $testCaseRun->sortOrder;?>"
                  style="width: 30px;" 
                  maxlength="3" 
                  class="input"></input>
                </td>
             </tr>
	         </table>
        </form>
      </td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr>
      <td align="center">
        <input type="hidden" id="testCaseRunAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogTestCaseRun').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogTestCaseRunSubmit" onclick="protectDblClick(this);saveTestCaseRun();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
