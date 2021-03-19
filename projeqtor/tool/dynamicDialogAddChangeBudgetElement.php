<?php
// ADD BY Marc TABARY - 2017-03-13 - PERIODIC YEAR BUDGET ELEMENT
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Julien PAPASIAN
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

require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/tool/dynamicDialogAddChangeBudgetElement.php');

$error = array();
if(!array_key_exists('action', $_REQUEST)) {
    $error = array('action');
    $action = '????';
} else {
    $action = $_REQUEST['action'];
}

if(!array_key_exists('objectClass', $_REQUEST)) {
    $error = array('objectClass');
} else {
    $objectClass = $_REQUEST['objectClass'];
}

if(!array_key_exists('refId', $_REQUEST)) {
    $error = array('refId');
} else {
       $refId = $_REQUEST['refId'];
 }

if(!array_key_exists('id', $_REQUEST)) {
    $error = array('id');
} else {
       $id = $_REQUEST['id'];
 }
 
if ($action=='ADD') {
    $budgetWork=0;
    $budgetCost=0;
    $budgetExpenseAmount=0;
    if(!array_key_exists('scope', $_REQUEST)) {
        $error = array('scope');
    } else {    
        $scope = $_REQUEST['scope'];        
    }
} else {
    $scope='';
    if(!array_key_exists('budgetWork', $_REQUEST)) {
        $error = array('budgetWork');
    } else {
        $budgetWork=$_REQUEST['budgetWork'];;        
    }
    if(!array_key_exists('budgetCost', $_REQUEST)) {
        $error = array('budgetCost');
    } else {
        $budgetCost=$_REQUEST['budgetCost'];;        
    }    
    if(!array_key_exists('budgetExpenseAmount', $_REQUEST)) {
        $error = array('budgetExpenseAmount');
    } else {
        $budgetExpenseAmount=$_REQUEST['budgetExpenseAmount'];;        
    }    
}

if(!array_key_exists('year', $_REQUEST)) {
    $error = array('year');
} else {
    $year = $_REQUEST['year'];
}
if (count($error)) {
    $msgError = 'There are errors on parameters in $_REQUEST :';
    foreach($error as $err) {
        $msgError.= ' - '.$err;
    }
    throwError($msgError);
    return;
}
$keyDownEventScript=NumberFormatter52::getKeyDownEvent();

// Visibility
$workAndCostVisibility = getUserConnectedWorkCostVisibility();
$workVisibility = ($workAndCostVisibility['workVisibility']=='NO'?'hidden':'');
$costVisibility = ($workAndCostVisibility['costVisibility']=='NO'?'hidden':'');
?>

<!--
<div dojoType="projeqtorDialogClass" type="hidden" id="dialogAddChangeBudgetElement" name="dialogAddChangeBudgetElement">
-->
<table>
    <tr><td>
        <form dojoType="dijit.form.Form" jsid='addChangeBudgetElementForm' 
              id="addChangeBudgetElementForm" name="addChangeBudgetElementForm" onSubmit="return false;">
            <input hidden id="AddChangeBudgetElementScope" name="AddChangeBudgetElementScope"value="<?php echo $scope;?>" />
            <input hidden id="AddChangeBudgetElementAction" name="AddChangeBudgetElementAction"value="<?php echo $action;?>" />
            <input hidden id="AddChangeBudgetElementId" name="AddChangeBudgetElementId"value="<?php echo $id;?>" />
            <input hidden id="AddChangeBudgetElementRefId" name="AddChangeBudgetElementRefId"value="<?php echo $refId;?>" />
            <?php 
                if($workVisibility=='hidden') {
                    echo '<input hidden id="AddChangeBudgetElementBudgetWork" name="AddChangeBudgetElementBudgetWork"value="'.$budgetWork.'" />';
                } 
                if($costVisibility=='hidden') {
                    echo '<input hidden id="AddChangeBudgetElementBudgetCost" name="AddChangeBudgetElementBudgetCost"value="'.$budgetCost.'" />';
                    echo '<input hidden id="AddChangeBudgetElementBudgetExpenseAmount" name="AddChangeBudgetElementBudgetExpenseAmount"value="'.$budgetExpenseAmount.'" />';
                } 
            ?>            
            <table>
                <tr>
                    <td class="dialogLabel">
                        <label for="AddChangeBudgetElementYear"><?php echo i18n('colPeriod');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                    </td>                    
                    <td>
                        <input id="AddChangeBudgetElementYear" name="AddChangeBudgetElementYear"
                             dojoType="dijit.form.TextBox" style="text-align:center; width:100px;"
                             value="<?php echo $year;?>"  
                             class="display" readonly /> 
                    </td>
                </tr>
                <?php if($workVisibility!='hidden') { ?>
                <tr class="detail">
                    <td class="dialogLabel">
                        <label for="AddChangeBudgetElementBudgetWork"><?php echo i18n('colWork');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                    </td>
                    <td>                        
                        <div id="AddChangeBudgetElementBudgetWork" name="AddChangeBudgetElementBudgetWork"
                             dojoType="dijit.form.NumberTextBox" 
                             constraints="{min:0,max:9999999.99}" 
                             value="<?php echo Work::displayWork($budgetWork);?>"  
                             class="input dijitNumberTextBox generalColClass"
                             style="text-align:right; width: 100px" >
                        </div>
                        <input id="AddChangeBudgetElementWorkUnit" name="AddChangeBudgetElementWorkUnit"
                               value="<?php echo Work::displayShortWorkUnit();?>" readonly tabindex="-1"
                               xdojoType="dijit.form.TextBox" 
                               class="display" style="width:15px; background-color:white; color:#000000; border:0px;"/>
                        <input type="hidden" id="AddChangeBudgetElementWorkUnit" name="AddChangeBudgetElementWorkUnit" value="" 
                               style="width:97px"/>  
                    </td>
                </tr>
                <?php }?>
                <?php if($costVisibility!='hidden') { ?>
                <tr class="detail">
                    <td class="dialogLabel">
                        <label for="AddChangeBudgetElementBudgetCost"><?php echo i18n('colCost');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                    </td>
                    <td>
                        <?php 
                            $currency=Parameter::getGlobalParameter('currency');
                            $currencyPosition=Parameter::getGlobalParameter('currencyPosition');
                            echo ($currencyPosition=='before'?$currency:''); 
                        ?>
                        <div id="AddChangeBudgetElementBudgetCost" name="AddChangeBudgetElementBudgetCost"
                             dojoType="dijit.form.NumberTextBox" 
                             constraints="{min:0,max:9999999.99}" 
                             value="<?php echo $budgetCost;?>"  
                             class="input dijitNumberTextBox generalColClass"
                             role="presentation" style="text-align:right; width: 100px">
                        </div>    
                        <?php echo ($currencyPosition=='before'?'':$currency); ?>
                    </td>
                </tr>
                <tr class="detail">
                    <td class="dialogLabel">
                        <label for="AddChangeBudgetElementBudgetExpenseAmount"><?php echo i18n('colExpense');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                    </td>
                    <td>
                        <?php echo ($currencyPosition=='before'?$currency:''); ?>
                        <div id="AddChangeBudgetElementBudgetExpenseAmount" name="AddChangeBudgetElementBudgetExpenseAmount"
                             dojoType="dijit.form.NumberTextBox" 
                             constraints="{min:0,max:9999999.99}" 
                             value="<?php echo $budgetExpenseAmount;?>"  
                             class="input dijitNumberTextBox generalColClass"
                             role="presentation" style="text-align:right; width: 100px" >
                        </div>    
                        <?php echo ($currencyPosition=='before'?'':$currency); ?>
                    </td>
                </tr>
                <?php }?>
            </table>
        </form>
    </td></tr>
    <tr>
        <td align="center">
            <input type="hidden" id="dialogAddChangeBudgetElementAction" />
            <button class="mediumTextButton" dojoType="dijit.form.Button" type="button"
                    onclick="dijit.byId('dialogAddChangeBudgetElement').hide();">
                <?php echo i18n("buttonCancel"); ?>
            </button>
            <button class="mediumTextButton" id="dialogAddChangeBudgetElementSubmit" dojoType="dijit.form.Button" type="submit"
                    onclick="protectDblClick(this);saveOrganizationBudgetElement();return false;" >
                <?php echo i18n("buttonOK"); ?>
            </button>
        </td>
    </tr>
</table>
<!--    
</div>
-->
<!-- END ADD BY Marc TABARY - 2017-03-13 - PERIODIC YEAR BUDGET ELEMENT -->