<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott LEGRAND (from Salto Consulting - 2018) 
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
/**
 * the dialog for the table of CustomEarnedRulesOfEmploymentContractType objects in EmploymentContractTypeMain.php
 */

// ELIOTT - LEAVE SYSTEM
include_once ("../tool/projeqtor.php");

//two modes are defined for the creation of the form in the popup, add and edit, each one permitting to differenciate a call from the action of creation or the action of editing
if(! array_key_exists ( 'addMode', $_REQUEST ) || ! array_key_exists ( 'editMode', $_REQUEST )){
    throwError ( 'Parameters addMode/updateMode not found in REQUEST' );
}
$addMode= $_REQUEST['addMode'];
$editMode= $_REQUEST['editMode'];
Security::checkValidBoolean($addMode);
Security::checkValidBoolean($editMode);

//addMode and editMode cannot be true (or false) at the same time
if( !($addMode==='true' && $editMode==='false') && !($addMode==='false' && $editMode==='true') ){  
    throwError ( 'A wrong mode was given' );
}

if(! array_key_exists ( 'idEmploymentContractType', $_REQUEST )){
    throwError ( 'Parameter idEmploymentContractType not found in REQUEST' );
}
$idEmploymentContractType = $_REQUEST ['idEmploymentContractType'];
Security::checkValidId ( $idEmploymentContractType );

//if $addMode, set all the attributes to null, except idEmploymentContractType which stay the same for the two modes
if($addMode==="true"){
    $idCustomEarnedRuleOfEmpContractType=null;
    $name=null;
    $customEarnedRule=null;
    $customEarnedWhereClause=null;
    $quantity=null;
    $idLeaveType=null;
}

//if $editMode, test and set all the variables with the informations passed in the request
if($editMode==="true"){
    if(! array_key_exists ( 'idCustomEarnedRules', $_REQUEST )){
        throwError ( 'Parameter idCustomEarnedRules not found in REQUEST' );
    }
    $idCustomEarnedRuleOfEmpContractType = $_REQUEST ['idCustomEarnedRules'];
    Security::checkValidId ( $idCustomEarnedRuleOfEmpContractType );
    
    if(! array_key_exists ( 'name', $_REQUEST )){
        throwError ( 'Parameter name not found in REQUEST' );
    }
    $name = $_REQUEST ['name'];
    
    if(! array_key_exists ( 'rule', $_REQUEST )){
        throwError ( 'Parameter rule not found in REQUEST' );
    }
    $customEarnedRule = $_REQUEST ['rule'];
    
    if(! array_key_exists ( 'whereClause', $_REQUEST )){
        throwError ( 'Parameter whereClause not found in REQUEST' );
    }
    $customEarnedWhereClause = $_REQUEST ['whereClause'];

    if(! array_key_exists ( 'quantity', $_REQUEST )){
        throwError ( 'Parameter quantity not found in REQUEST' );
    }
    $quantity = $_REQUEST ['quantity'];
    if($quantity!=null){    
        Security::checkValidInteger ( $quantity );
        if($quantity<0){
            throwError ( 'Parameter quantity not valid in REQUEST' );
        }
    }
    
    if(! array_key_exists ( 'idLeaveType', $_REQUEST )){
        throwError ( 'Parameter idLeaveType not found in REQUEST' );
    }
    $idLeaveType = $_REQUEST ['idLeaveType'];
    Security::checkValidId ( $idLeaveType );
}

?>

<table>
        <tr>
            <td>
                <form dojoType="dijit.form.Form" id='customEarnedRulesOfEmpContractTypeForm'
                    name='customEarnedRulesOfEmpContractTypeForm' onSubmit="return false;">
                       
                    <input id="idEmploymentContractType" name="idEmploymentContractType" type="hidden"
			value="<?php echo $idEmploymentContractType;?>" />
                    
                    <input id="idCustomEarnedRuleOfEmpContractType" name="idCustomEarnedRuleOfEmpContractType" type="hidden" 
			value="<?php echo $idCustomEarnedRuleOfEmpContractType;?>" />
                    
                    <table>                        
                        <tr>
                            <td class="dialogLabel" >
                                <label class="dialogLabel longLabel" for="ruleName"><?php echo i18n('name');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit.form.ValidationTextBox" type="text" id="ruleName" name="ruleName" class="input required"
                                      value="<?php echo $name;?>" data-dojo-props="maxLength:100,invalidMessage:'Name is required.'" style="width: 40em;" required="true"/>
                            </td>
                        </tr>
			
                        <tr>
                            <td class="dialogLabel" >
                             <label class="dialogLabel longLabel" for="ruleCustomEarnedRule" ><?php echo i18n("customEarnedRule");?>&nbsp;:&nbsp;</label>
                            </td>
                            <td> 
                                <!--dijit.form.Textarea doesn't support the attribute required, so the hidden validationTextBox ijs here to force the required when clicking on the submit button -->
                                <textarea id="ruleCustomEarnedRuleTextArea" name="ruleCustomEarnedRuleTextArea" maxlength="4000" class="input required" 
                                          data-dojo-type="dijit.form.Textarea" style="width:40em;" 
                                          onchange="dijit.byId('ruleCustomEarnedRule').set('value', this.value);"><?php echo $customEarnedRule;?></textarea>
                                
                                <input dojoType="dijit.form.ValidationTextBox" type="hidden"
                                   id="ruleCustomEarnedRule" name="ruleCustomEarnedRule"
                                   style="display: none"
                                   maxlength="4000"
                                   value="<?php echo $customEarnedRule;?>" required="true"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="dialogLabel" >
                             <label class="dialogLabel longLabel" for="whereClauseCustomEarnedRule" ><?php echo i18n("whereClause");?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <textarea id="whereClauseCustomEarnedRuleTextArea" name="whereClauseCustomEarnedRuleTextArea" maxlength="4000" class="input" 
                                          data-dojo-type="dijit.form.Textarea" style="width:40em;" 
                                          onchange="dijit.byId('whereClauseCustomEarnedRule').set('value', this.value);"><?php echo $customEarnedWhereClause;?></textarea>
                                <input dojoType="dijit.form.ValidationTextBox" type="hidden"
                                   id="whereClauseCustomEarnedRule" name="whereClauseCustomEarnedRule"
                                   style="display: none"
                                   maxlength="4000"
                                   value="<?php echo $customEarnedWhereClause;?>" />
                            </td>
                        </tr>
                        

                        <tr>
                            <!--<td class="dialogLabel" >
                             <label class="label" style="width:100%; text-align:left;"><?php echo i18n("customEarnedRuleHelpLabel");?>&nbsp;:&nbsp;</label>
                            </td>-->
                            <td colspan="2">
                                <div id="customEarnedRuleHelpTitle" data-dojo-type="dijit/TitlePane" data-dojo-props="open:false, title: '<?php echo i18n("customEarnedRuleHelpTitle");?>'">
                                    <table id="ruleCustomEarnedRuleHelpTable" name="ruleCustomEarnedRuleHelpTable">
                                        <tr class="detail generalRowClass">
                                            <td><label class="label longLabel"><?php echo i18n("customEarnedRuleHelpListItems");?>&nbsp;:&nbsp;</label></td> 
                                            <td>
                                                <select dojoType="dijit.form.Select" id="ruleCustomEarnedRuleHelpListItems" name="ruleCustomEarnedRuleHelpListItems" class="input" 
                                                    style="width:40em;" required onchange="refreshListFieldsInDialogCustomEarnedRules(this.value);">
                                                    <?php 
                                                        $arrayItems=getRulableItems();
                                                        $first=true;
                                                        foreach($arrayItems as $key => $value){
                                                            $item = '<option value="' . $key . '"';
                                                            if($first) {
                                                                $item .= ' selected="selected" ';
                                                                $first=false;
                                                            }
                                                            $item .= '><span >'. htmlEncode(ucfirst($value)) . '</span></option>';
                                                            echo $item;
                                                        }
                                                    ?>
                                                </select>

                                            </td>
                                        </tr>
                                        <tr class="detail generalRowClass">
                                            <td class="label longLabel" style="font-weight:normal;"><?php echo i18n("customEarnedRuleHelpListFields");?>&nbsp;:&nbsp;</td>
                                            <td>
                                                <select dojoType="dijit.form.Select" class="input  generalColClass" id="ruleCustomEarnedRuleHelpListFields" name="ruleCustomEarnedRuleHelpListFields" style="width: 40em;" required>
                                                    <?php 
                                                        $arrayFieldsOfFirstItem=getFieldsOfFirstRulableItem();
                                                        $first=true;
                                                        foreach($arrayFieldsOfFirstItem as $key => $value){
                                                            $field = '<option value="' . $key . '"';
                                                            if($first) {
                                                                $field .= ' selected="selected" ';
                                                                $first=false;
                                                            }
                                                            $field .= '><span >'. htmlEncode(ucfirst($value)) . '</span></option>';
                                                            echo $field;
                                                        }
                                                    ?>

                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label longLabel" style="font-weight:normal;"><?php echo i18n("buttonInsertFieldIntoCustomEarnedRule");?>&nbsp;:&nbsp;</td>
                                            <td>
                                                <div>
                                                    <button id="ruleButtonInsertFieldIntoCustomEarnedRule" name="ruleButtonInsertFieldIntoCustomEarnedRule" dojoType="dijit.form.Button" showlabel="true">
                                                        <?php echo i18n("insertIntoRule"); ?>
                                                        <script type="dojo/connect" event="onClick" args="evt">  
                                                                addFieldInTextBoxForCustomEarnedRules();
                                                        </script>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="label longLabel" style="font-weight:normal;"><?php echo i18n("buttonInsertFieldIntoCustomEarnedWhereClause");?>&nbsp;:&nbsp;</td>
                                            <td>
                                                <div>
                                                    <button id="whereClauseButtonInsertFieldIntoCustomEarnedRule" name="whereClauseButtonInsertFieldIntoCustomEarnedRule" dojoType="dijit.form.Button" showlabel="true">
                                                        <?php echo i18n("insertIntoWhereClause"); ?>
                                                        <script type="dojo/connect" event="onClick" args="evt">  
                                                                addFieldInTextBoxWhereClauseForCustomEarnedRules();
                                                        </script>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <tr class="detail generalRowClass">
                                            <td class="label longLabel" style="font-weight:normal;"><?php echo i18n("customEarnedRuleHelpOperators");?>&nbsp;:&nbsp;</td>
                                            <td>
                                                <select dojoType="dijit.form.Select" class="input  generalColClass" id="ruleCustomEarnedRuleHelpOperators" name="ruleCustomEarnedRuleHelpOperators" style="width: 40em;" required>
                                                    <?php 
                                                        $arrayFields = [
                                                                        "IF(condition,valueTrue,valueFalse)" => (i18n('IF')),
                                                                        "OR"            => (i18n('OR')),
                                                                        "AND"           => (i18n('AND')),
                                                                        "="             => (i18n('equal')),
                                                                        "<>"            => (i18n('different')),
                                                                        ">="            => (i18n('greaterOrEqual')),
                                                                        ">"             => (i18n('greaterThan')),
                                                                        "<="            => (i18n('lessOrEqual')),
                                                                        "<"             => (i18n('lessThan')),
                                                                        "now()"         => (i18n('nowDate')),
                                                                        "year(date)"        => (i18n('yearOf')),
                                                                        "month(date)"       => (i18n('monthOf')),
                                                                        "day(date)"         => (i18n('dayOf')),
                                                                        "isnull(field)"      => (i18n('isNull')),
                                                                        "substr(field,start,length)" => (i18n('subString'))
                                                                       ];
                                                        $first=true;
                                                        foreach ($arrayFields as $key => $value) {
                                                            $op = '<option value="' . $key . '"';
                                                            if($first) {
                                                                $op .= ' selected="selected" ';
                                                                $first=false;
                                                            }
                                                            $op .= '><span >'. $value . '</span></option>';
                                                            echo $op;
                                                        }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label longLabel" style="font-weight:normal;"><?php echo i18n("buttonInsertOpIntoCustomEarnedRule");?>&nbsp;:&nbsp;</td>
                                            <td>
                                                <div>
                                                    <button id="ruleButtonInsertOpIntoCustomEarnedRule" name="ruleButtonInsertOpIntoCustomEarnedRule" dojoType="dijit.form.Button" showlabel="true">
                                                        <?php echo i18n("insertIntoRule"); ?>
                                                        <script type="dojo/connect" event="onClick" args="evt">  
                                                                addOpInTextBoxForCustomEarnedRules(); 
                                                        </script>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label longLabel" style="font-weight:normal;"><?php echo i18n("buttonInsertOpIntoCustomEarnedWhereClause");?>&nbsp;:&nbsp;</td>
                                            <td>
                                                <div>
                                                    <button id="whereClauseButtonInsertOpIntoCustomEarnedRule" name="whereClauseButtonInsertOpIntoCustomEarnedRule" dojoType="dijit.form.Button" showlabel="true">
                                                        <?php echo i18n("insertIntoWhereClause"); ?>
                                                        <script type="dojo/connect" event="onClick" args="evt">  
                                                                addOpInTextBoxWhereClauseForCustomEarnedRules(); 
                                                        </script>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="dialogLabel" >
                                <label class="dialogLabel longLabel" for="ruleQuantity"><?php echo i18n('colQuantity');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="ruleQuantity" name="ruleQuantity" value="<?php echo $quantity;?>" class="input required"
                                    data-dojo-props="smallDelta:0.5, largeDelta:1.0, constraints:{min:0.5,max:999.5,places:1}" name="quantity" style="width:100px" required/>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="dialogLabel" >
                                <label class="dialogLabel longLabel" for="ruleIdLeaveType"><?php echo i18n('colIdLeaveType');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <select id="ruleIdLeaveType"  dojoType="dijit.form.Select"
                                    <?php if ($idLeaveType) echo ' readonly ';?> 
                                    name="ruleIdLeaveType" class="input required" value="<?php echo $idLeaveType;?>" required>
                                        <?php htmlDrawOptionForReference('idLeaveType', $idLeaveType, null, true);?>
                                </select>    
                            </td>
                        </tr>
                    </table>
                    
                </form>
            </td>
        </tr>
        <tr>
            <td align="center"><input type="hidden" id="customEarnedRulesOfEmpContractTypeAction">
		<button class="mediumTextButton" dojoType="dijit.form.Button"
                    type="button" onclick="dijit.byId('dialogCustomEarnedRulesOfEmpContractType').hide();">
                    <?php echo i18n("buttonCancel");?>
                </button>
		<button class="mediumTextButton" dojoType="dijit.form.Button"
                    type="submit" id="dialogCustomEarnedRulesOfEmpContractTypeSubmit"
                    onclick="protectDblClick(this);saveCustomEarnedRulesOfEmpContractType();return false;">
                    <?php echo i18n("buttonOK");?>
                </button>
            </td>
        </tr>
</table>