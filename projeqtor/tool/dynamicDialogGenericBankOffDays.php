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

// MTY - GENERIC DAY OFF
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

if(! array_key_exists ( 'idGenericBankOffDays', $_REQUEST )){
    throwError ( 'Parameter idGenericBankOffDays not found in REQUEST' );
}
$idGenericBankOffDays = $_REQUEST ['idGenericBankOffDays'];
Security::checkValidId ( $idGenericBankOffDays );

if(! array_key_exists ( 'idCalendarDefinition', $_REQUEST )){
    throwError ( 'Parameter idCalendarDefinition not found in REQUEST' );
}
$idCalendarDefinition = $_REQUEST ['idCalendarDefinition'];
Security::checkValidId ( $idCalendarDefinition );

//if $addMode, set all the attributes to null, except idCalendarDefintion which stay the same for the two modes
if($addMode==="true"){
    $idGenericBankOffDays=null;
    $name=null;
    $month=null;
    $day=null;
    $easterDay=null;
}

//if $editMode, test and set all the variables with the informations passed in the request
if($editMode==="true"){
    if(! array_key_exists ( 'name', $_REQUEST )){
        throwError ( 'Parameter name not found in REQUEST' );
    }
    $name = $_REQUEST ['name'];
    
    if(! array_key_exists ( 'month', $_REQUEST )){
        throwError ( 'Parameter month not found in REQUEST' );
    }
    $month = $_REQUEST ['month'];
    if($month!=null){    
        Security::checkValidInteger ( $month );
        if($month<0 or $month>12){
            throwError ( 'Parameter month not valid in REQUEST' );
        }
    }
    
    if(! array_key_exists ( 'day', $_REQUEST )){
        throwError ( 'Parameter day not found in REQUEST' );
    }
    $day = $_REQUEST ['day'];
    if($day!=null){    
        Security::checkValidInteger ( $day );
        if($day<0 or $day>31){
            throwError ( 'Parameter day not valid in REQUEST' );
        }
    }
    
    if(! array_key_exists ( 'easterDay', $_REQUEST )){
        throwError ( 'Parameter easterDay not found in REQUEST' );
    }
    $easterDay = $_REQUEST ['easterDay'];
    if($easterDay!=null){    
        Security::checkValidInteger ( $easterDay );
        if($easterDay<0 or $easterDay>3){
            throwError ( 'Parameter easterDay not valid in REQUEST' );
        }
    }    
}

$easterDayList = array(
                    4 => "",
                    0 => i18n("easter"),
                    1 => i18n("ascension"),
                    2 => i18n("pentecost"),
                    3 => i18n("holyfriday"),
    
                   );
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

<table>
        <tr>
            <td>
                <form dojoType="dijit.form.Form" id='genericBankOffDaysForm'
                    name='genericBankOffDaysForm' onSubmit="return false;">
                       
                    <input id="idGenericBankOffDays" name="idGenericBankOffDays" type="hidden"
			value="<?php echo $idGenericBankOffDays;?>" />
                    
                    <input id="idGenCalendarDefinition" name="idGenCalendarDefinition" type="hidden" 
			value="<?php echo $idCalendarDefinition;?>" />
                    
                    <table>                        
                        <tr>
                            <td class="dialogLabel" >
                                <label class="longLabel" for="genericBankOffDayName" style="white-space:nowrap;width:250px"><?php echo i18n('colName');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit.form.ValidationTextBox" type="text" id="genericBankOffDayName" name="genericBankOffDayName" class="input required"
                                      value="<?php echo $name;?>" data-dojo-props="maxLength:100,invalidMessage:'Name is required.'" style="width: 40em;" required="true"/>
                            </td>
                        </tr>
			                      
                        <tr>
                            <td class="dialogLabel">
                                <label class="longLabel" for="genericBankOffDayMonth" style="white-space:nowrap;width:250px"><?php echo i18n('month');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                            </td>
                            <td>
                                <select id="genericBankOffDayMonth"  dojoType="dijit.form.FilteringSelect"
                                        name="genericBankOffDayMonth" 
                                        class="input required" 
                                        value="<?php 
                                                    $theMonth = ($month==null?0:$month);
                                                    echo $month;
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
                            <td class="dialogLabel" >
                                <label class="longLabel" for="genericBankOffDayDay" style="white-space:nowrap;width:250px"><?php echo i18n('day');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                            </td>
                            <td>
                                <input data-dojo-type="dijit/form/NumberSpinner" id="genericBankOffDayDay" name="genericBankOffDayDay" value="<?php echo $day;?>" class="input required"
                                    data-dojo-props="smallDelta:1, largeDelta:1, constraints:{min:1,max:31}" name="day" style="width:100px"/>
                            </td>
                        </tr>
              
                        <tr>
                            <td class="dialogLabel" >
                                <label class="longLabel" for="genericBankOffDayEasterDay" style="white-space:nowrap;width:250px"><?php echo i18n('easterDay');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                            </td>
                            <td>
                                <select id="genericBankOffDayEasterDay"  dojoType="dijit.form.FilteringSelect"
                                        name="genericBankOffDayEasterDay" 
                                        class="input" 
                                        value="<?php 
                                            $theEasterDay = ($easterDay==null?4:$easterDay);
                                            echo $theEasterDay;
                                               ?>">
                                    <script type="dojo/method" event="onChange" >
                                      if (this.value == 4) {
                                        enableWidget("genericBankOffDayMonth");
                                        enableWidget("genericBankOffDayDay");
                                        dojo.addClass(dijit.byId("genericBankOffDayMonth").domNode, 'required');
                                        dojo.addClass(dijit.byId("genericBankOffDayDay").domNode, 'required');
                                      } else {
                                        disableWidget("genericBankOffDayMonth");
                                        disableWidget("genericBankOffDayDay");
                                        dijit.byId("genericBankOffDayName").set("value",dijit.byId("genericBankOffDayEasterDay").get("displayedValue"));
                                        dojo.removeClass(dijit.byId("genericBankOffDayMonth").domNode, 'required');
                                        dojo.removeClass(dijit.byId("genericBankOffDayDay").domNode, 'required');
                                      }
                                    </script> 
                                    <?php
                                    foreach( $easterDayList as $key => $val) {
                                        echo '<option value="' . $key . '"';
                                        if ( $theEasterDay == $key) { 
                                          echo ' SELECTED ';
                                        }
                                        echo '><span >'. htmlEncode($val) . '</span></option>';
                                    }    
                                    ?>
                                </select>    
                            </td>
                        </tr>
                    </table>
                    
                </form>
            </td>
        </tr>
        <tr>
            <td align="center"><input type="hidden" id="genericBankOffDaysAction">
		<button class="mediumTextButton" dojoType="dijit.form.Button"
                    type="button" id="dialogGenericBankOffDaysCancel"
                    onclick="dijit.byId('dialogGenericBankOffDays').hide();">
                    <?php echo i18n("buttonCancel");?>
                </button>
		<button class="mediumTextButton" dojoType="dijit.form.Button"
                    type="submit" id="dialogGenericBankOffDaysSubmit"
                    onclick="protectDblClick(this);saveGenericBankOffDays();return false;">
                    <?php echo i18n("buttonOK");?>
                </button>
            </td>
        </tr>
</table>