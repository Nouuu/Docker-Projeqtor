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
 * the dialog for the table of leaveTypeOfEmploymentContractType objects in EmploymentContractTypeMain.php
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
    $idLvTypeOfContractType = null;
    $idLeaveType=null;
    $startMonthPeriod=null;
    $periodDuration=null;
    $quantity=null;
    $earnedPeriod=null;
    $validityDuration=null;
    $isJustifiable=0;
    $isAnticipated=0;
    $isIntegerQuotity=0;
    $nbDaysAfterNowLeaveDemandIsAllowed=0;
    $nbDaysBeforeNowLeaveDemandIsAllowed=0;
}

//if $editMode, test and set all the variables with the informations passed in the request
if($editMode==="true"){
    if(! array_key_exists ( 'idLvTypeOfContractType', $_REQUEST )){
        throwError ( 'Parameter idLvTypeOfContractType not found in REQUEST' );
    }
    $idLvTypeOfContractType = $_REQUEST ['idLvTypeOfContractType'];
    Security::checkValidId ( $idLvTypeOfContractType );
    
    if(! array_key_exists ( 'idLeaveType', $_REQUEST )){
        throwError ( 'Parameter idLeaveType not found in REQUEST' );
    }
    $idLeaveType = $_REQUEST ['idLeaveType'];
    Security::checkValidId ( $idLeaveType );
    
    if(! array_key_exists ( 'startMonthPeriod', $_REQUEST )){
        throwError ( 'Parameter startMonthPeriod not found in REQUEST' );
    }
    $startMonthPeriod = $_REQUEST ['startMonthPeriod'];
    if(! in_array((int)$startMonthPeriod, [null,1,2,3,4,5,6,7,8,9,10,11,12])){
        throwError ( 'Invalid Parameter startMonthPeriod found in REQUEST' );
        //Security::checkValidMonth($startMonthPeriod);
        //Security::checkValidMonth() doesn't take into account that $startMonthPeriod can be null;
    }
    
    /*if(! array_key_exists ( 'startDayPeriod', $_REQUEST )){
        throwError ( 'Parameter startDayPeriod not found in REQUEST' );
    }
    $startDayPeriod = $_REQUEST ['startDayPeriod'];
    if($startMonthPeriod !=null and $startDayPeriod!=null){
        //warning: doesn't take into account Leap years (2016/02/29 for example is valid, 2018/02/29 is not, and this test doesn't take it into account)
        Security::checkValidDateTime( '2018-' . ($startMonthPeriod<10?'0'.$startMonthPeriod:$startDayPeriod) .'-'. ($startDayPeriod<10?'0'.$startDayPeriod:$startDayPeriod) );
    }else if(false){
        //todo test $startDayPeriod validity
    }*/
    //for now, it has been decided that the day of the start of the period of acquisition is set to 1
    $startDayPeriod=1;
    
    if(! array_key_exists ( 'periodDuration', $_REQUEST )){
        throwError ( 'Parameter periodDuration not found in REQUEST' );
    }
    $periodDuration = $_REQUEST ['periodDuration'];
    if($periodDuration!=null){    
        Security::checkValidInteger ( $periodDuration );
        if($periodDuration<0){
            throwError ( 'Parameter periodDuration not valid in REQUEST' );
        }
    }
    
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
    
    if(! array_key_exists ( 'earnedPeriod', $_REQUEST )){
        throwError ( 'Parameter earnedPeriod not found in REQUEST' );
    }
    $earnedPeriod = $_REQUEST ['earnedPeriod'];
    if($earnedPeriod!=null){    
        Security::checkValidInteger ( $earnedPeriod );
        if($earnedPeriod<0){
            throwError ( 'Parameter earnedPeriod not valid in REQUEST' );
        }
    }

    if(! array_key_exists ( 'isIntegerQuotity', $_REQUEST )){
        throwError ( 'Parameter isIntegerQuotity not found in REQUEST' );
    }
    $isIntegerQuotity = $_REQUEST ['isIntegerQuotity'];
    if($isIntegerQuotity!=0 && $isIntegerQuotity!=1){
        throwError ( 'Parameter isIntegerQuotity not valid in REQUEST' );
    }
    
    if(! array_key_exists ( 'validityDuration', $_REQUEST )){
        throwError ( 'Parameter validityDuration not found in REQUEST' );
    }
    $validityDuration = $_REQUEST ['validityDuration'];
    if($validityDuration!=null){    
        Security::checkValidInteger ( $validityDuration );
        if($validityDuration<0){
            throwError ( 'Parameter validityDuration not valid in REQUEST' );
        }
    }

    if(! array_key_exists ( 'nbDaysBeforeNowLeaveDemandIsAllowed', $_REQUEST )){
        throwError ( 'Parameter nbDaysBeforeNowLeaveDemandIsAllowed not found in REQUEST' );
    }
    $nbDaysBeforeNowLeaveDemandIsAllowed = $_REQUEST ['nbDaysBeforeNowLeaveDemandIsAllowed'];
    if($nbDaysBeforeNowLeaveDemandIsAllowed!=null){    
        Security::checkValidInteger ( $nbDaysBeforeNowLeaveDemandIsAllowed );
        if($nbDaysBeforeNowLeaveDemandIsAllowed<0){
            throwError ( 'Parameter nbDaysBeforeNowLeaveDemandIsAllowed not valid in REQUEST' );
        }
    }
    
    if(! array_key_exists ( 'nbDaysAfterNowLeaveDemandIsAllowed', $_REQUEST )){
        throwError ( 'Parameter nbDaysAfterNowLeaveDemandIsAllowed not found in REQUEST' );
    }
    $nbDaysAfterNowLeaveDemandIsAllowed = $_REQUEST ['nbDaysAfterNowLeaveDemandIsAllowed'];
    if($nbDaysAfterNowLeaveDemandIsAllowed!=null){    
        Security::checkValidInteger ( $nbDaysAfterNowLeaveDemandIsAllowed );
        if($nbDaysAfterNowLeaveDemandIsAllowed<0){
            throwError ( 'Parameter nbDaysAfterNowLeaveDemandIsAllowed not valid in REQUEST' );
        }
    }

    if(! array_key_exists ( 'isJustifiable', $_REQUEST )){
        throwError ( 'Parameter isJustifiable not found in REQUEST' );
    }
    $isJustifiable = $_REQUEST ['isJustifiable'];
    if($isJustifiable!=0 && $isJustifiable!=1){
        throwError ( 'Parameter isJustifiable not valid in REQUEST' );
    }
    
    if(! array_key_exists ( 'isAnticipated', $_REQUEST )){
        throwError ( 'Parameter isAnticipated not found in REQUEST' );
    }
    $isAnticipated = $_REQUEST ['isAnticipated'];
    if($isAnticipated!=0 && $isAnticipated!=1){
        throwError ( 'Parameter isAnticipated not valid in REQUEST' );
    }    
}

$months = array (
                    0 => "",
                    1 => i18n("January"),
                    2 => i18n("February"),
                    3 => i18n("March"),
                    4 => i18n("April"),
                    5 => i18n("May"),
                    6 => i18n("June"),
                    7 => i18n("July"),
                    8 => i18n("August"),
                    9 => i18n("September"),
                    10 => i18n("October"),
                    11 => i18n("November"),
                    12 => i18n("December"),
                );

?>
<table id="tableDialogLvTypeOfEmpContractType">
        <tr>
            <td>
                <form dojoType="dijit.form.Form" id='lvTypeOfContractTypeForm'
                    name='lvTypeOfContractTypeForm' onSubmit="return false;">
                    
                    <input id="idLvTypeOfContractType" name="idLvTypeOfContractType" type="hidden"
			value="<?php echo $idLvTypeOfContractType;?>" />
                    <input id="idEmploymentContractType" name="idEmploymentContractType" type="hidden"
			value="<?php echo $idEmploymentContractType;?>" />
                    
                    <table>
			<tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightIdLeaveType"><?php echo i18n('LeaveType');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <select id="rightIdLeaveType"  dojoType="dijit.form.FilteringSelect"
                                    <?php if ($idLeaveType) echo ' readonly ';?>
                                    name="rightIdLeaveType" class="input <?php if (!$idLeaveType) echo ' required ';?>" value="<?php echo $idLeaveType;?>" required>
                                        <?php htmlDrawOptionForReference('idLeaveType', $idLeaveType, null, true);?>
                                </select>    
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightStartMonthPeriod"><?php echo i18n('colStartMonthPeriod');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
<!--
                                <input data-dojo-type="dijit/form/NumberSpinner" id="rightStartMonthPeriod" value="<?php echo $startMonthPeriod;?>"
                                    data-dojo-props="smallDelta:1, constraints:{min:1,max:12,places:0}" name="rightStartMonthPeriod" style="width:100px"/>
-->
                                <select id="rightStartMonthPeriod"  dojoType="dijit.form.FilteringSelect"
                                        name="rightStartMonthPeriod" 
                                        class="input required" 
                                        value="<?php 
                                                    $theMonth = ($startMonthPeriod==null?0:$startMonthPeriod);
                                                    echo $startMonthPeriod;
                                                ?>">
                                    <?php
                                    foreach( $months as $key => $val) {
                                        echo '<option value="' . $key . '"';
                                        if ( $theMonth == $key) { 
                                          echo ' SELECTED ';
                                        }
                                        echo '><span >'. htmlEncode($val) . '</span></option>';
                                    }    
                                    ?>
                                </select>    
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightStartDayPeriod"><?php echo i18n('colStartDayPeriod');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="rightStartDayPeriod" value="1" readonly
                                    data-dojo-props="smallDelta:1, constraints:{min:1,max:31,places:0}" name="rightStartDayPeriod" style="width:100px"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightPeriodDuration"><?php echo i18n('colPeriodDuration');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="rightPeriodDuration" value="<?php echo $periodDuration;?>"
                                    data-dojo-props="smallDelta:1, constraints:{min:1,max:99999,places:0}" name="rightPeriodDuration" style="width:100px"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightQuantity"><?php echo i18n('colQuantity');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="rightQuantity" value="<?php echo $quantity;?>"
                                    data-dojo-props="smallDelta:0.5, largeDelta:1.0, constraints:{min:0,max:999.5,places:1}" name="rightQuantity" style="width:100px"/>
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightEarnedPeriod"><?php echo i18n('colEarnedPeriod');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="rightEarnedPeriod" value="<?php echo $earnedPeriod;?>"
                                    data-dojo-props="smallDelta:1, constraints:{min:1,max:99999,places:0}" name="rightEarnedPeriod" style="width:100px"/>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightIsIntegerQuotity"><?php echo i18n('isIntegerQuotity');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/CheckBox" type="checkbox" id="rightIsIntegerQuotity" value="1" <?php if($isIntegerQuotity==1) {echo 'checked="checked"';} ?>
                                    name="rightIsIntegerQuotity"  />
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightValidityDuration"><?php echo i18n('colValidityDuration');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="rightValidityDuration" value="<?php echo $validityDuration;?>"
                                    data-dojo-props="smallDelta:1, constraints:{min:1,max:99999,places:0}" name="rightValidityDuration" style="width:100px"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightNbDaysAfterNowLeaveDemandIsAllowed"><?php echo i18n('colNbDaysAfterNowLeaveDemandIsAllowed');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="rightNbDaysAfterNowLeaveDemandIsAllowed" value="<?php echo $nbDaysAfterNowLeaveDemandIsAllowed;?>"
                                    data-dojo-props="smallDelta:1, constraints:{min:0,max:999,places:0}" name="rightNbDaysAfterNowLeaveDemandIsAllowed" style="width:100px"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightNbDaysBeforeNowLeaveDemandIsAllowed"><?php echo i18n('colNbDaysBeforeNowLeaveDemandIsAllowed');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="rightNbDaysBeforeNowLeaveDemandIsAllowed" value="<?php echo $nbDaysBeforeNowLeaveDemandIsAllowed;?>"
                                    data-dojo-props="smallDelta:1, constraints:{min:0,max:999,places:0}" name="rightNbDaysBeforeNowLeaveDemandIsAllowed" style="width:100px"/>
                            </td>
                        </tr>                        
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightIsJustifiable"><?php echo i18n('isJustifiable');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/CheckBox" type="checkbox" id="rightIsJustifiable" value="1" <?php if($isJustifiable==1) {echo 'checked="checked"';} ?>
                                    name="rightIsJustifiable"  />
                            </td>
                        </tr>
                               
                        <tr>
                            <td>
                                <label class="dialogLabel longLabel" for="rightIsAnticipated"><?php echo i18n('colIsAnticipated');?>&nbsp;:&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/CheckBox" type="checkbox" id="rightIsAnticipated" value="1" <?php if($isAnticipated==1) {echo 'checked="checked"';} ?>
                                    name="rightIsAnticipated"  />
                            </td>
                        </tr>
                        <!--$isAnticipated-->
                    </table>
                </form>
            </td>
        </tr>
        <tr>
            <td align="center"><input type="hidden" id="lvTypeOfContractTypeAction">
		<button class="mediumTextButton" dojoType="dijit.form.Button"
                    type="button" onclick="dijit.byId('dialogLvTypeOfEmpContractType').hide();">
                    <?php echo i18n("buttonCancel");?>
                </button>
		<button class="mediumTextButton" dojoType="dijit.form.Button"
                    type="submit" id="dialogLvTypeOfEmpContractTypeSubmit"
                    onclick="protectDblClick(this);saveLvTypeOfEmpContractType();return false;">
                    <?php echo i18n("buttonOK");?>
                </button>
            </td>
        </tr>
</table>    
