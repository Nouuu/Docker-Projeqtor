/*******************************************************************************
 * COPYRIGHT NOTICE *
 * 
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Salto Consulting
 * 
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org
 * 
 * DO NOT REMOVE THIS NOTICE **
 ******************************************************************************/

/* ============================================================================
 * This js file contents all implemented functions for LEAVE SYSTEM
   ============================================================================ */

// **********************
// MISCELANIOUS FUNCTIONS
// **********************

/**
 * Show Xhr error in error popup
 * php file must include '../view/leaveCalendarPopupErrorAndResult.php'
 * @param {XhrError} error
 * @param {string} msg
 * @return {void}
 */
function showXhrErrorInErrorPopup(error,msg) {
    hideWait();
    theError = new String();
    theError = i18n("unknown");
    errorTitle= i18n("network")+" "+i18n("ERROR");
    errorTitle = errorTitle.toUpperCase();
    
    if (typeof(error.xhr)==="undefined") {
        theError = i18n("notXhrError");
    } else if(typeof(error.status)!=="undefined") {
        theError = error.status;
    } else if(typeof(error.message)!=="undefined") {
        theError = error.message;        
    }
    if (dojo.byId('resultPopup')) {
        // Set content Node of result
        contentNode = dojo.byId('resultPopup');
        dojo.removeClass(contentNode);
        dojo.addClass(contentNode, "messageERROR");
        dojo.addClass(contentNode, "closeBoxIconLeave");
        contentNode.innerHTML = "<h3>"+errorTitle+"</h3><br/>"+msg+"<br/>"+theError+"<br/><i>"+i18n("clickIntoToClose")+"</i>";
        // Show result
        contentNode.style.display = "block";
        contentNode.style.pointerEvents='auto';
    } 
}

/**
 * Show the result of a SqlElement operation in a popup
 * php file must include '../view/leaveCalendarPopupErrorAndResult.php'
 * @param {String} msg : The result of a SqlElement operation
 * @return {void}
 */
function showSqlElementResultInPopup(msg) {
    hideWait();
    // Retrieve type of message
    var type = getSqlElementOperationStatus(msg);
    
    // Retrieve useful message
    indexResult = msg.indexOf("<input");
    var theMsg = msg.substr(0,indexResult);
    
    if (dojo.byId('resultPopup')) {
        // Set content Node of result
        contentNode = dojo.byId('resultPopup');
        dojo.removeClass(contentNode);
        dojo.addClass(contentNode, "message"+type);
        contentNode.innerHTML = "<br/>"+theMsg;

        // Show result
        contentNode.style.display = "block";
        contentNode.style.pointerEvents='auto';
        
        // If type = "OK" or "NO_CHANGE" => fade short time
        if (type=="OK" || type=="NO_CHANGE") {
            dojo.fadeIn({
              node : contentNode,
              duration : 100,
              onEnd : function() {
                dojo.fadeOut({
                  node : contentNode,
                  duration : 3000
                }).play();
              }
            }).play();
        } else { // fade long time
          dojo.addClass(contentNode, "closeBoxIconLeave");
            dojo.fadeIn({
              node : contentNode,
              duration : 100,
              onEnd : function() {
//                dojo.fadeOut({
//                  node : contentNode,
//                  duration : 10000
//                }).play();
              }
            }).play();        
        }
    }    
}

// *********************
// FUNCTIONS FOR COOKIES
// *********************
/**
 * 
 * @param {String} cname : The cookie name to get the value
 * @returns {void} The cookie value
 */
function getCookieValue(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "unknown";
}
// *********************
// END FUNCTIONS FOR COOKIES
// *********************


// *********************
// FUNCTIONS FOR CSS
// *********************
/**
 * Return the opposite color of the color passed in parameter
 * @param {String} hex : The color in Hexa to opposite
 * @param {Boolean} bw : If true, color opposite is Black or White
 * @returns {String} : The oppocite color
 */
function oppositeColor(hex, bw) {
    if (! hex || typeof(hex)=="undefined") {return '#000000';}
    if (hex.indexOf('#') === 0) {
        hex = hex.slice(1);
    }
    // convert 3-digit hex to 6-digits.
    if (hex.length === 3) {
        hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }
    if (hex.length !== 6) {
        throw new Error('Invalid HEX color "'+hex+'"');
    }
    var r = parseInt(hex.slice(0, 2), 16),
        g = parseInt(hex.slice(2, 4), 16),
        b = parseInt(hex.slice(4, 6), 16);
    if (bw) {
        return (r * 0.299 + g * 0.587 + b * 0.114) > 186
            ? '#000000'
            : '#FFFFFF';
    }
    // invert color components
    r = (255 - r).toString(16);
    g = (255 - g).toString(16);
    b = (255 - b).toString(16);
    // pad each with zeros and return
    return "#" + padZero(r) + padZero(g) + padZero(b);
}

/**
 * Test if selector passed in parameter exists in document
 * @param {String} selector to test existance in document
 * @returns {Boolean} : true if selector exists in document
 */
function selectorExists(selector) { 
    var theElt = document.querySelector(selector);
    if (theElt==null) { return false;} else {return true;}
}

/**
 * Create a CSS class named classStatus with background color colorStatus
 * @param {String} classStatus : The status class to create
 * @param {String} colorStatus : The background color of status class
 * @returns {Boolean} : True if status class is created
 */
function createLeaveStatusClass(classStatus, colorStatus) {
    if (typeof(colorStatus)=='undefined') { return false;}
    if (!selectorExists(classStatus)) {
        var color = oppositeColor(colorStatus, true);
        var style = document.createElement('style');
        style.type = 'text/css';
        style.innerHTML = classStatus + ' { ';
        style.innerHTML += 'background-color: '+ colorStatus + ' !important; ';
        style.innerHTML += 'color: ' + color + ' !important; ';
        style.innerHTML += '}';        
        document.getElementsByTagName('head')[0].appendChild(style);
    }
}
// *********************
// END FUNCTIONS FOR CSS
// *********************

// ****************************
// FUNCTIONS FOR LEAVE CALENDAR
// ****************************
// Variables Off and Work day list to resource that is not user connected
var emplWorkDayList = new Array();
var emplOffDayList = new Array();
var emplDefaultOffDays = new Array();
var theSelectedLeave = null;

/**
 * Get the employee's id for which manage leave 
 * that is store in view/leaveCalendar.php (idEmployeeCalendar) or in model/LeaveMain.php (idEmployee)
 * @param {boolean} fromLeaveCalendar : true if from Leave Calendar - false if from LeaveMain
 * @returns {Element.value} : The id employee
 */
function getIdEmployeeInLeaveCalMain(fromLeaveCalendar) {
    if (!fromLeaveCalendar) {
        if (dijit.byId("idEmployee")) {
            return dijit.byId("idEmployee").value;
        } else {
            return null;
        }
    } else {
        if (typeof(document.getElementById('idEmployeeCalendar'))=="undefined") {
            return null;
        } else {
            return document.getElementById('idEmployeeCalendar').value;
        }        
    }
}

/**
 * Get the connected user's id that is store in idUserCalendar (view/leaveCalendar.php) 
 * @returns {Element.value} : The user's id
 */
function getIdUserInLeaveCalendar() {
    if (typeof(document.getElementById('idUserCalendar'))=="undefined") {
        return null;
    } else {
        return document.getElementById('idUserCalendar').value;
    }        
}

/**
 * Initialize the workDayList and OffDayList for a resource
 * @param {integer} idRes : resource's id for which initialize
 * @param {string} startDate : start date for which initialize
 * @param {string} endDate : end date for which initialize
 * @returns {void}
 */
function initOffDayOfRes(idRes,startDate,endDate) {
    var url = "../tool/getOffDaysOfResource.php?idRes="+idRes;
    if (startDate!=="") {
        url += "&startDate="+startDate;
    }
    if (endDate!=="") {
        url += "&endDate="+endDate;        
    }
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        sync: true,
        load : function(data, args) {
            var datas = JSON.parse(data);
            var opStatus = getSqlElementOperationStatus(datas);
            if (opStatus.indexOf("TYPEOF")===-1) {
                if (opStatus==='NOT RESULT OF SQLELEMENT OPERATION') {
                    datas = setLikeResultDivMessage(null, 
                                  opStatus+'<br/>'+datas, 
                                  false,
                                  "", 
                                  "InitOffDayOfRes",
                                  "INVALID"
                            );
                }
                showSqlElementResultInPopup(datas);
            } else {
                emplOffDayList[idRes] = datas['offDays'];
                emplWorkDayList[idRes] = datas['workDays'];
                emplDefaultOffDays = datas['defaultOffDays'];
            }
        },
        error: function(error){
            showXhrErrorInErrorPopup(error,'getOffDaysOfResource.php');
        }
    });        
}

/**
 * Return if the date passed in parameter is a day off for the resource passed in parameter
 * @param {Date} vDate : The date to test
 * @param {Integer} idRes : The resource's id to test - If null, use of default calendar (ie calendar definition with id=1)
 * @param {Integer} idUser : The connected user
 * @returns {Boolean} : true if day off
 */
function isOffDayOfResource(vDate, idRes, idUser) {
    var cWorkDayList;
    var cOffDayList;
    
    if (idRes==null) {
        // Use datas of the default calendar
        cWorkDayList = getCookieValue("workDayList");
        cOffDayList = getCookieValue("offDayList");
        if (cWorkDayList!="unknown") {
            workDayList = cWorkDayList;
        } else {
            cWorkDayList = workDayList;
        }
        if (cOffDayList!="unknown") {
            offDayList = cOffDayList;
        } else {
            cOffDayList = offDayList;
        }
    } else {
        if (idUser == idRes) {
            // Use datas of the user calendar
            cWorkDayList = getCookieValue("uWorkDayList");
            cOffDayList = getCookieValue("uOffDayList");        
            if (cWorkDayList!="unknown") {
                uWorkDayList = cWorkDayList;
            } else {
                cWorkDayList = uWorkDayList;
            }
            if (cOffDayList!="unknown") {
                uOffDayList = cOffDayList;
            } else {
                cOffDayList = uOffDayList;
            }
        } else {
            // Take a leave for an another ressource (not for him self)
            if (typeof(emplWorkDayList[idRes])=="undefined") {
                cWorkDayList="";
                cOffDayList="";
            } else {
                cWorkDayList = emplWorkDayList[idRes];
                cOffDayList = emplOffDayList[idRes];
            }
        }
    }
    trueFalse=false;
    if (idUser == idRes) {
        trueFalse = ( defaultOffDays.indexOf(vDate.getDay()) != -1 );
    } else {
        for (var key in emplDefaultOffDays) {
            if (key == vDate.getDay() && trueFalse==false) {
                trueFalse=true;
                break;
            }
        }
    }
    
    if ( trueFalse ) {
        var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
        if (cWorkDayList.lastIndexOf('#'+day+'#')>=0) {
            return false; 
        } else {
            return true;
        }
    } else {
        var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
        if (cOffDayList.lastIndexOf('#'+day+'#')>=0) {
            return true; 
        } else {
            return false;
        }
    }
}

/**
 * Calculate the nomber of open days between to dates
 * @param {Date} paramStartDate
 * @param {Date} paramEndDate
 * @param {Integer} idRes The employee's id for which search open days
 * @param {Integer} idUser The connected user's id
 * @returns {Number} : Number of open days between to dates
 */
function openDayDiffDates(paramStartDate, paramEndDate, idRes, idUser) {
    var currentDate = new Date();
    if (!isDate(paramStartDate))
      return '';
    if (!isDate(paramEndDate))
      return '';
    currentDate.setFullYear(paramStartDate.getFullYear(), paramStartDate.getMonth(), paramStartDate.getDate());
    currentDate.setHours(0,0,0,0);
    var endDate = paramEndDate;
    if (paramEndDate < paramStartDate) {
      return 0;
    }
    var duration = 0;

    while (currentDate <= endDate) {
      if (!isOffDayOfResource(currentDate, idRes, idUser)) {
        duration++;
      }
      currentDate = addDaysToDate(currentDate, 1);
    }
    return duration;
}


/**
 * to calculate the nbDays between two leaves
 * @param {string} idStartDate
 * @param {string} idEndDate
 * @param {string} idStartAM
 * @param {string} idStartPM
 * @param {string} idEndAM
 * @param {string} idEndPM
 * @param {string} idNbDays
 * @param {integer} idRes
 * @param {integer} idUser
 * @returns void
 */
function calculateHalfDaysForLeave(idStartDate, idEndDate, idStartAM, idStartPM, idEndAM, idEndPM, idNbDays, idRes, idUser){
    var startDate = dijit.byId(idStartDate).get("value");
    var endDate = dijit.byId(idEndDate).get("value");
    
    if (idRes != idUser) {
        initOffDayOfRes(idRes, transformDateToSqlDate(startDate), transformDateToSqlDate(endDate));
    }
    
    var startDate = dijit.byId(idStartDate).get("value");
    var endDate = dijit.byId(idEndDate).get("value");
    var nbOpenDays=openDayDiffDates(startDate,endDate, idRes, idUser);
    //if the two dates are set and the dates are the same and the number of days is superior to 0
    if(startDate && endDate && startDate.getTime() === endDate.getTime() && nbOpenDays > 0){
        //if two cases are ticked
        if((dijit.byId(idStartAM).checked && dijit.byId(idEndPM).checked) || 
           (dijit.byId(idStartAM).checked && dijit.byId(idEndAM).checked) || 
           (dijit.byId(idStartPM).checked && dijit.byId(idEndPM).checked) ||
           (dijit.byId(idStartPM).checked && dijit.byId(idEndAM).checked)) {
           
            if(dijit.byId(idStartAM).checked && dijit.byId(idEndPM).checked){
                dijit.byId(idNbDays).set("value",nbOpenDays);
            }else if(dijit.byId(idStartAM).checked && dijit.byId(idEndAM).checked){
                dijit.byId(idNbDays).set("value",(nbOpenDays-0.5));
            }else if(dijit.byId(idStartPM).checked && dijit.byId(idEndPM).checked){
                dijit.byId(idNbDays).set("value",(nbOpenDays-0.5));
            }else if(dijit.byId(idStartPM).checked && dijit.byId(idEndAM).checked){
                dijit.byId(idNbDays).set("value",nbOpenDays-1);
            }
        }else{
            dijit.byId(idNbDays).set("value",nbOpenDays);
        }
    }
    //else if the two dates are different
    else if(startDate && endDate && startDate.getTime() !== endDate.getTime() && nbOpenDays > 0){
        if((dijit.byId(idStartAM).checked && dijit.byId(idEndPM).checked) || 
           (dijit.byId(idStartAM).checked && dijit.byId(idEndAM).checked) || 
           (dijit.byId(idStartPM).checked && dijit.byId(idEndPM).checked) || 
           (dijit.byId(idStartPM).checked && dijit.byId(idEndAM).checked)) {
            
            if(dijit.byId(idStartAM).checked && dijit.byId(idEndPM).checked){
                dijit.byId(idNbDays).set("value",nbOpenDays);
            }else if(dijit.byId(idStartAM).checked && dijit.byId(idEndAM).checked){
                if(!isOffDayOfResource(endDate, idRes, idUser)){
                    dijit.byId(idNbDays).set("value",(nbOpenDays-0.5));
                }else{
                    dijit.byId(idNbDays).set("value",(nbOpenDays));
                }
            }else if(dijit.byId(idStartPM).checked && dijit.byId(idEndPM).checked){
                if(!isOffDayOfResource(startDate, idRes, idUser)){
                    dijit.byId(idNbDays).set("value",(nbOpenDays-0.5));
                }else{
                    dijit.byId(idNbDays).set("value",(nbOpenDays));
                }
            }else if(dijit.byId(idStartPM).checked && dijit.byId(idEndAM).checked){
                if((!isOffDayOfResource(startDate, idRes, idUser)) && (!isOffDayOfResource(endDate, idRes, idUser))){
                    dijit.byId(idNbDays).set("value",(nbOpenDays-1));
                }else if((!isOffDayOfResource(startDate, idRes, idUser)) && isOffDayOfResource(endDate, idRes, idUser)){
                    dijit.byId(idNbDays).set("value",(nbOpenDays-0.5));
                }else if(isOffDayOfResource(startDate, idRes, idUser) && (!isOffDayOfResource(endDate, idRes, idUser))){
                    dijit.byId(idNbDays).set("value",(nbOpenDays-0.5));
                }else{
                    dijit.byId(idNbDays).set("value",(nbOpenDays));
                }
            }
        }else{
            dijit.byId(idNbDays).set("value",nbOpenDays);
        }
    }else{
        dijit.byId(idNbDays).set("value",0);
    }
    
}

function calculateNbRemainingDays(from, idRes, idUser, nbDays, idStatus, idLeaveType) {
    if (from === "" || idRes === null || idUser === null) {return "";}
    var nbRemainingDays="";
    var theLeft = null;
    var theNbDays = null;
    var theLeaveNbDays = null;
    var idLeave=null;
    var idStatusNew=null;
    var idStatusOld=null;
    var idLeaveTypeNew=null;
    var idLeaveTypeOld=null;
    
    if (from === "fromLeaveMain") {
        nbRemainingDays="idNbDaysRemaining";
        if (idStatus === null || idLeaveType === null) { 
            dijit.byId(nbRemainingDays).set("value","");
            dojo.byId(nbRemainingDays).style.display="none";
            return;            
        }
        if (employeeLeft!=idRes) {
            getLeftByLeaveType(idRes);
        }
        left = leftArray[dijit.byId('idLeaveType').value];
        theNbDays = Number(dijit.byId('nbDays').value);
        theLeaveNbDays = nbDays;
        idLeave=dijit.byId('id').value;
        idStatusNew=dijit.byId('idStatus').value;
        idStatusOld=idStatus;
        idLeaveTypeNew=dijit.byId('idLeaveType').value;
        idLeaveTypeOld=idLeaveType;
    } else if (from === "fromLeaveCalendar") {
        nbRemainingDays = 'popupNbRemainingDays';
        left = leftArray[dijit.byId('popupLeaveType').value];
        theNbDays = Number(dijit.byId('popupNbDays').value);
        theLeaveNbDays = Number(theSelectedLeave.nbDays);
        idLeave = dijit.byId('popupLeaveId').value
        idStatusNew = dijit.byId('popupStatus').value;
        idStatusOld = theSelectedLeave.idStatus;
        idLeaveTypeNew = dijit.byId('popupLeaveType').value;
        idLeaveTypeOld = theSelectedLeave.idLeaveType;        
    } else {
        return;
    }

    var isCancelledNew="0";
    var isCancelledOld="0";
    var l=wfStatusArray.length;
    for (var i=0; i<l; i++) {
        if (wfStatusArray[i].id==idStatusOld) {
            isCancelledOld = wfStatusArray[i].setRejectedLeave;
        }
        if (wfStatusArray[i].id==idStatusNew) {
            isCancelledNew = wfStatusArray[i].setRejectedLeave;
        }        
    }
    
    // left is null => No limit
    if (left==null) {
        dijit.byId(nbRemainingDays).set("value","");
        hideWidget(nbRemainingDays);
        if (dojo.byId("labelPopupNbRemainingDays")) {
            dojo.byId("labelPopupNbRemainingDays").style.display="none";
        }
        dojo.byId(nbRemainingDays).style.display="none";
        return;
    }

    if (left!=null) {theLeft = Number(left);}
    showWidget(nbRemainingDays);
    if (dojo.byId("labelPopupNbRemainingDays")) {
        dojo.byId("labelPopupNbRemainingDays").style.display="block";
    }
    dojo.byId(nbRemainingDays).style.display="block";

    // New Leave
    // ---------
    if (idLeave<0 || idLeave=="") {
        // status is has SetCancelledLeave = 1
        if (isCancelledNew == 1) {
            dijit.byId(nbRemainingDays).set("value",theLeft);
        } else {
            dijit.byId(nbRemainingDays).set("value",theLeft-theNbDays);
        }
        return;
    }

    // Existing Leave
    // --------------
    // status was not cancelled and become cancelled
    if (isCancelledOld != 1 && isCancelledNew == 1) {
        // Same LeaveType
        if (idLeaveTypeOld == idLeaveTypeNew) {                    
            dijit.byId(nbRemainingDays).set("value",theLeft+theLeaveNbDays);
        } else {
            dijit.byId(nbRemainingDays).set("value",theLeft);                    
        }
        return;
    }
    // status is cancelled but become not cancelled
    if (isCancelledOld == 1 && isCancelledNew != 1) {
        dijit.byId(nbRemainingDays).set("value",theLeft-theNbDays);
        return;            
    }

    // status was not cancelled and don't become cancelled
    if (idLeaveTypeOld == idLeaveTypeNew) {
        // Type don't change
        dijit.byId(nbRemainingDays).set("value",theLeft-theNbDays+theLeaveNbDays);            
    } else {
        // Type change
        dijit.byId(nbRemainingDays).set("value",theLeft-theNbDays);            
    }    
}

/* ****************************************************************
 * for the changes of the spe StartAM in model/LeaveMain.php 
 * @param {inter} idUser : The connected user's id
 * @returns {undefined}
 */
function changesStartAM(idUser,nbDays,idStatus,idLeaveType) {
    var idRes = getIdEmployeeInLeaveCalMain();
    var input = dijit.byId("startAM");
    if(input.checked){
        dijit.byId("startPM").set("checked",false);
    }else{
        dijit.byId("startPM").set("checked",true);
    }
    calculateHalfDaysForLeave('startDate', 'endDate', 'startAM', 'startPM', 'endAM', 'endPM', 'nbDays', idRes, idUser);
    calculateNbRemainingDays("fromLeaveMain", idRes, idUser,nbDays,idStatus,idLeaveType);
    formChanged();
}

/* ****************************************************************
 * for the changes of the spe StartPM in model/LeaveMain.php 
 * @param {inter} idUser : The connected user's id
 * @returns {undefined}
 */
function changesStartPM(idUser,nbDays,idStatus,idLeaveType) {
    var idRes = getIdEmployeeInLeaveCalMain();
    var input = dijit.byId("startPM");
    if(input.checked){
        dijit.byId("startAM").set("checked",false);
    }else{
        dijit.byId("startAM").set("checked",true);
    }
    calculateHalfDaysForLeave('startDate', 'endDate', 'startAM', 'startPM', 'endAM', 'endPM', 'nbDays', idRes, idUser);
    calculateNbRemainingDays("fromLeaveMain", idRes, idUser,nbDays,idStatus,idLeaveType);
    formChanged();    
    
}

/* ****************************************************************
 * for the changes of the spe EndAM in model/LeaveMain.php 
 * @param {inter} idUser : The connected user's id
 * @returns {undefined}
 */
function changesEndAM(idUser,nbDays,idStatus,idLeaveType) {
    var idRes = getIdEmployeeInLeaveCalMain();
    var input = dijit.byId("endAM");
    if(input.checked){
        dijit.byId("endPM").set("checked",false);
    }else{
        dijit.byId("endPM").set("checked",true);
    }
    calculateHalfDaysForLeave('startDate', 'endDate', 'startAM', 'startPM', 'endAM', 'endPM', 'nbDays', idRes, idUser);
    calculateNbRemainingDays("fromLeaveMain", idRes, idUser,nbDays,idStatus,idLeaveType);
    formChanged();    
    
}

/* ****************************************************************
 * for the changes of the spe EndPM in model/LeaveMain.php 
 * @param {inter} idUser : The connected user's id
 * @returns {undefined}
 */
function changesEndPM(idUser,nbDays,idStatus,idLeaveType) {
    var idRes = getIdEmployeeInLeaveCalMain();
    var input = dijit.byId("endPM");
    if(input.checked){
        dijit.byId("endAM").set("checked",false);
    }else{
        dijit.byId("endAM").set("checked",true);
    }
    calculateHalfDaysForLeave('startDate', 'endDate', 'startAM', 'startPM', 'endAM', 'endPM', 'nbDays', idRes, idUser);
    calculateNbRemainingDays("fromLeaveMain", idRes, idUser,nbDays=null,idStatus=null,idLeaveType=null);
    formChanged();      
}
//end of the functions for model/leaveMain.php


// ========================================================
// VARIABLES FOR THE CALENDAR
//a boolean to differenciate the actions of the buttons of the popup (between the creation or the modification)
var creatBool =false;

var leavesCalendarData = [];
var defaultLvTypeArray = [];
var leavesTypesArray = [];
var statusArray = [];
var statusFromTo = [];
var leftArray = [];
var employeeLeft = null;
var idEvent=0;
var daysOffCalendarData = [];
var wfStatusArray = [];
var popupTypeSelectOptions = [];

/**
 * Get the statuses of the workflow of the leave type
 * @param {string} from : Called from LeaveMain (fromLeaveMain) or from leaveCalendar (fromLeaveCalendar)
 * @param {integer} idLeaveType : The leave type
 * @param {integer} idRes : The resource for which is getting the workflow statuses
 * @param {integer} idUser : The connected user
 * @param {float} nbDays : nb days of leave
 * @param {integer} idStatus : the status
 * @returns {Status[]} An array of statuses
 */
function getWorkflowStatusesOfLeaveType(from,idLeaveType,idRes,idUser,nbDays,idStatus) {
    if (from=="fromLeaveCalendar") {
        idRes=document.getElementById('idEmployeeCalendar').value;
    }
    var url = '../tool/getWorkflowStatusesOfLeaveType.php?idType='+idLeaveType+'&idEmployee='+idRes+'&from='+from;
    var res = [];
    var resStatus = [];
    var resFromTo = [];
    
    if (from===null && wfStatusArray.length>0) {
        return;
    } 
    
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        sync: true,
        load : function(data) {
            if(data){
                res = JSON.parse(data);
                resStatus = Object.values(res["status"]);
                if (!(idLeaveType in statusFromTo)) {
                    statusFromTo[idLeaveType] = res["FromTo"];
                }
                var statusesAreChanged = false;
                if (JSON.stringify(resStatus)!==JSON.stringify(wfStatusArray)) {
                    statusesAreChanged=true;
                }
                wfStatusArray = resStatus;
                if (statusesAreChanged===true && from !== null) {
                    refreshStatusesSelect(wfStatusArray,(from==='fromLeaveCalendar'?'popupStatus':'idStatus'),idLeaveType);
                }
                if (from==="fromLeaveCalendar") {
                    calculateNbRemainingDays(from,idRes,idRes);
                }
            } else {
                wfStatusArray = resStatus;
                return wfStatusArray;
            }
        },
        error: function(error){
            showXhrErrorInErrorPopup(error, 'getWorkflowStatusesOfLeaveType');
            wfStatusArray = resStatus;
            statusFromTo = resFromTo;
            if (from==="fromLeaveCalendar" || from==="fromLeaveMain") {
                refreshStatusesSelect(wfStatusArray,(from==='fromLeaveCalendar'?'popupStatus':'idStatus'),idLeaveType);
            }
            return wfStatusArray;
        }
    });
}

/**
 * Enabled or Disabled popup option in function of status and StatusTo of workflow
 * @param {integer} theStatus : The status for with enabled or disabled popupStatusOption
 * @param {object} thePopupStatusSelectOptions : The popup status select option to enable or disable
 * @param {integer} theLeaveType : The leave type for with enabled or disabled popupStatusOption.  
 * @returns {object} The popup status select option with enabled or disabled
 */
function disabledEnabledPopupStatusOption(theStatus, thePopupStatusSelectOptions, theLeaveType) {
    var statusTo = [];
    var lS = thePopupStatusSelectOptions.length;
    var populatePopupStatus=false;
    
    if (thePopupStatusSelectOptions===null) {
        thePopupStatusSelectOptions = dijit.byId("popupStatus").get("options");
        populatePopupStatus=true;
    }
    
    if (theLeaveType!=null) {    
        for (var keyFt in statusFromTo[theLeaveType]) {
            if (keyFt==theStatus) {
                statusTo.push(statusFromTo[theLeaveType][keyFt]);
            }
        }
    }
    for (var i=0; i<lS; i++) {
        var bFound=false;
        var lT = statusTo.length;
        for (var j=0;j<lT;j++) {
            if (statusTo[j][thePopupStatusSelectOptions[i].value]!==undefined) {
                bFound=true;
                break;
            }
        }
        if (!bFound && thePopupStatusSelectOptions[i].value !== theStatus) {            
            thePopupStatusSelectOptions[i].disabled=true;
        } else {
            thePopupStatusSelectOptions[i].disabled=false;            
        }
    }
    
    if (document.getElementById('isManagerCalendar').value === "0") {
        var lAS = wfStatusArray.length;
        for (var i=0; i<lAS; i++) {
            if (wfStatusArray[i].setRejectedLeave=="1" || wfStatusArray[i].setAcceptedLeave=="1") {
                var lPS = thePopupStatusSelectOptions.length;
                for (var j=0; j<lPS; j++) {
                    if (thePopupStatusSelectOptions[j].value==wfStatusArray[i].id) {
                        thePopupStatusSelectOptions[j].disabled=true;
                    }
                }
            }
        }
    }
    
    // Order by enable
    var popupStatusSelectOptionsOrder = [];
    var lPS = thePopupStatusSelectOptions.length;
    for (var i=0; i<lPS; i++) {
        if (thePopupStatusSelectOptions[i].disabled===false) {
            popupStatusSelectOptionsOrder.push(thePopupStatusSelectOptions[i]);
        }
    }
    for (var i=0; i<lPS; i++) {
        if (thePopupStatusSelectOptions[i].disabled===true) {
            popupStatusSelectOptionsOrder.push(thePopupStatusSelectOptions[i]);
        }
    }
    
    if (populatePopupStatus) {
        dijit.byId("popupStatus").set("options", popupStatusSelectOptionsOrder);   
    }
    return popupStatusSelectOptionsOrder;
}

function refreshStatusesSelect(theStatusArray, theSelectString, idLeaveType) {        
    var lengthStatusArray = 0;
    var theStatusArrayOfLeaveType= [];
    
    if (typeof(theStatusArray)!='undefined') {
        lengthStatusArray = theStatusArray.length;
        if ((idLeaveType in statusFromTo)) {
            var theStatusFromToOfLeaveType = statusFromTo[idLeaveType];
            for (var i=0; i<lengthStatusArray;i++) {
                if ((theStatusArray[i].id in theStatusFromToOfLeaveType)) {
                    theStatusArrayOfLeaveType.push(theStatusArray[i]);
                }
            }
            theStatusArray = theStatusArrayOfLeaveType;
            lengthStatusArray = theStatusArray.length;
        }
    }
    
    // A new Leave
    if (theSelectedLeave.idLeave==null) {
        status = theStatusArray[0].id;
        theSelectedLeave.status = status;
        theSelectedLeave.idLeaveType=idLeaveType;
    }

    var popupStatusSelectOptions = [];
    var selectedStatusExist = false;

    if (theSelectString==='popupStatus') {
        var linkIdNameStatus = [];
        var bDisabled = false;
        for(var i = 0; i< lengthStatusArray; i++){
            var bSelected = false;
            // If user is not a leaveAdmin or the leave manager => can't see status with setRejectedLeave = 1 and setAcceptedLeave = 1
            if (document.getElementById('isManagerCalendar').value === "0" &&
                (theStatusArray[i].setRejectedLeave=="1" || theStatusArray[i].setAcceptedLeave=="1")
               ) {
                    bDisabled=true;
            }
            // The theStatusArray's id is the leave's status id
            if (status==theStatusArray[i].id) {
                bSelected = true;
                selectedStatusExist = true;
            }
            // Add popup option to popup status
            popupStatusSelectOptions.push({value:theStatusArray[i].id, label:theStatusArray[i].name, selected:bSelected, disabled:bDisabled}); 
            linkIdNameStatus[theStatusArray[i].id]= theStatusArray[i].name;                            
        }
        // If the leave's status don't exist
        if (!selectedStatusExist) {
            // Add popup option to popup status = Unknow
            popupStatusSelectOptions.push({value:0, label:i18n('unknown'), selected:true, disabled:true});
            linkIdNameStatus[0]= i18n('unknown');            
            // Disabled popup status
            dijit.byId('popupStatus').set('disabled', true);
            // Disabled validateButtonCalendarPopup et deleteButtonCalendarPopup
            validateButtonCalendarPopup.set('disabled', true);
            deleteButtonCalendarPopup.set('disabled', true);
        } else {
            // Disabled or enabled popup option in function of status to of leave's status
            popupStatusSelectOptions = disabledEnabledPopupStatusOption(status,popupStatusSelectOptions,idLeaveType);
        }
        dijit.byId('popupStatus').set("options", popupStatusSelectOptions);
        dijit.byId('popupStatus').set("value", status);
    } else {
        var idStatus = dijit.byId('idStatus').value;
        var theStore = dijit.byId('idStatus').store;
        var lengthData = theStore.data.length;
        var theSelectedStatusName = "";
        while(lengthData>0) {            
            var theDatas = theStore.query();
            theStore.remove(theDatas[0].id);
            theStore = dijit.byId('idStatus').store;
            lengthData = theStore.data.length;
        }        
        for(var i = 0; i< lengthStatusArray; i++){
            theStore = dijit.byId('idStatus').store;
            if (idStatus==theStatusArray[i].id) {
                selectedStatusExist = true;
                theSelectedStatusName = theStatusArray[i].name;
            }
            theStore.add({id:theStatusArray[i].id,value:theStatusArray[i].id,name:theStatusArray[i].name});
        }
        if (selectedStatusExist===true) {
            document.getElementById('idStatus').value = theSelectedStatusName;
        }        
    }
}

/**
 * fill the array daysOffCalendarData with the days off of the current user's calendar
 * @param {String} startDate
 * @param {String} endDate
 * @returns void
 */
function fillDaysOffCalendarDataArray(startDate,endDate){
    var idRes = null;
    var idUser = null;
    var idDayOff = 0;
    var theDate = new Date(startDate);
    theDate.setHours(0,0,0,0);
    var theEndDate = new Date(endDate);
    theEndDate.setHours(23,59,59,0);
    daysOffCalendarData = [];
    
    // The resource for which search off day
    idRes = getIdEmployeeInLeaveCalMain(true);
    // The user
    idUser = getIdUserInLeaveCalendar();
    
    if (idRes != idUser) {        
        initOffDayOfRes(idRes,transformDateToSqlDate(startDate),transformDateToSqlDate(endDate));
    }
    while (theDate<=theEndDate) {
        if (isOffDayOfResource(theDate, idRes, idUser)) {
            var theStartTime = new Date(theDate);
            theStartTime.setHours(0,0,0,0);
            var theEndTime = new Date(theDate);
            theEndTime.setHours(23,59,59,0);
            var dayOff={
                id: 'do'+idDayOff,
                calendar: "OFF",
                startTime: theStartTime,
                endTime: theEndTime,
                cssStyle: 'calendarDayOff'
            };
            daysOffCalendarData.push(dayOff);
            idDayOff++;
        }
        theDate = addDaysToDate(theDate, 1);
    }
}

/**
 * fill the array leavesCalendarData with the leaves already registered for the current user
 * @param {String} startDate
 * @param {String} endDate
 * @returns void
 */
function fillLeavesCalendarDataArray(startDate,endDate){
    //getLeaves returns an array containing three sub-arrays, one containing all of the existing leaves of the user between startDate and endDate, the second containing all the leaveTypes, the third all the existing status
    var idRes = getIdEmployeeInLeaveCalMain(true);
    leavesCalendarData = [];
    idEvent=0;
    dojo.xhrGet({
        url : "../tool/getLeaves.php?startDate="+startDate+"&endDate="+endDate+"&idRes="+idRes,
        handleAs : "text",
        sync: true,
        load : function(data, args) {
            var leaves=JSON.parse(data);
            if (isSqlElementOperationStatus(leaves)) {
               showSqlElementResultInPopup(leaves);
               return;
            }
            
            leavesTypesArray = leaves["leaveTypes"];
            defaultLvTypeArray = leaves["default"];
            statusArray = leaves["status"];
            leftArray = leaves["left"];
            statusFromTo = leaves["statusFromTo"];
            employeeLeft = idRes;
            
            if(leaves['leaves']!=="empty"){                                
                for(var it in leaves['leaves']){
                    //to get the name of the leaveType of the current leave
                    var lvT="";
                    var lvtColor="";
                    for(var i in leaves["leaveTypes"]){
                        if( leaves["leaveTypes"][i]["id"] == leaves['leaves'][it]["idLeaveType"]){
                            lvT=leaves["leaveTypes"][i]["name"];
                            lvtColor=leaves["leaveTypes"][i]["color"];
                            break;
                        }
                    }
                    var statusSetLeaveChange = "";
                    if (leaves['leaves'][it]["statusSetLeaveChange"]=="1") {
                        statusSetLeaveChange = '&nbsp;<span style=background-color:red;color:white;">&nbsp;S&nbsp;</span>';
                    }
                    
                    var statusColor="#000000";
                    if (leaves['leaves'][it]["statusOutOfWorkflow"]!="1") {
                        for(var i in leaves["status"]){
                            if( leaves["status"][i]["id"] == leaves['leaves'][it]["idStatus"]){
                                statusColor=leaves["status"][i]["color"];
                                break;
                            }
                        }
                    }
                    var nbDays = parseFloat(leaves['leaves'][it]["nbDays"]);
                    if(lvtColor==null){
                        lvtColor='#FFFFFF';
                    }
                    var textColor = oppositeColor(lvtColor, true);
                    var leaveType = '<span class="leaveType" style="background-color:'+lvtColor+'; color:'+textColor+';">&nbsp;'+lvT+'&nbsp;</span>';
                    var summary = leaveType + "<br/>" + nbDays + ' ' + (nbDays<=1?i18n('day'):i18n('days'))+statusSetLeaveChange;

                    var startDate = new Date(leaves['leaves'][it]["startDate"]);
                    var endDate = new Date(leaves['leaves'][it]["endDate"]);
                   
                    //to set the hours of the leave
                    if(leaves['leaves'][it]["startAMPM"] == "AM"){ 
                        startDate.setHours(9,0,0,0);
                    }else{
                        startDate.setHours(14,0,0,0);
                    }
                    if(leaves['leaves'][it]["endAMPM"] == "AM"){ 
                        endDate.setHours(12,0,0,0);
                    }else{
                        endDate.setHours(18,0,0,0);
                    }
                    
                    var statusUnknow = leaves['leaves'][it]["statusOutOfWorkflow"];
                    var theEventStatus = (statusUnknow!="1"?leaves['leaves'][it]["idStatus"]:"0");
                    var theTransition = "";
                    if (leaves['leaves'][it]["submitted"]=="0" && leaves['leaves'][it]["accepted"]=="0" && leaves['leaves'][it]["rejected"]=="0") {
                        theTransition = i18n("neutral");
                    } else if (leaves['leaves'][it]["submitted"]=="1") {
                        theTransition = i18n("colSubmitted");
                    } else if (leaves['leaves'][it]["accepted"]=="1") {
                        theTransition = i18n("colAccepted");
                    } else if (leaves['leaves'][it]["rejected"]=="1") {
                        theTransition = i18n("colRejected");
                    } else {
                        theTransition = i18n("unknown");
                    }
                    var event={
                        id: parseInt(it),
                        summary: summary,
                        startTime: startDate,
                        endTime: endDate,
                        startAMPM: leaves['leaves'][it]["startAMPM"],
                        endAMPM: leaves['leaves'][it]["endAMPM"],
                        idLeave: leaves['leaves'][it]["id"],
                        idCreator: leaves['leaves'][it]["idUser"],
                        idValidator: leaves['leaves'][it]["idResource"],
                        idRequester: leaves['leaves'][it]["idEmployee"],
                        idLeaveType: leaves['leaves'][it]["idLeaveType"],
                        transition: theTransition,
                        status: theEventStatus,
                        statusUnknow: statusUnknow,
                        statusOK: leaves['leaves'][it]["statusSetLeaveChange"],
                        color: statusColor,
                        reason: leaves['leaves'][it]["comment"],
                        nbDays: leaves['leaves'][it]["nbDays"],
                        cssStyle: "leaveStatus leaveStatus_" + theEventStatus
                    };
                    leavesCalendarData.push(event);
                }
                idEvent=leavesCalendarData.length;
            }
        },
        error: function(error){
            showXhrErrorInErrorPopup(error, 'getLeaves');
        }
    });
}


/**
 * return the date to the format month/year in the form of a string 
 * @param {type} date an object date
 * @returns {String}
 */
function formatMonthYearDate(date) {
    var monthNames = [i18n("January"), 
                      i18n("February"),
                      i18n("March"),
                      i18n("April"),
                      i18n("May"),
                      i18n("June"),
                      i18n("July"),
                      i18n("August"),
                      i18n("September"),
                      i18n("October"),
                      i18n("November"),
                      i18n("December")];
    var monthIndex = date.getMonth(), year = date.getFullYear();
    return monthNames[monthIndex] + ' ' + year;
}


/**
 * Refresh the content of the summary after the save/delete of a leaveType or the change of employee (refreh all the content of the tab summary)
 * @returns {undefined}
 */
function refreshEmployeeCustomLeavesEarned(){
    var idEmployee=document.getElementById('idEmployeeCalendar').value;
    //xhrGet, return an array with every lines to be put in the div summaryLeaveEarnedOfEmployee
    //args=idEmployee
    //return lvType:(idLeaveType/color),lvTypeOfEmpContractType:(periodDuration),lvEarned:(startDate/endDate/quantity/leftQuantity), 
    dojo.xhrGet({
        url : "../tool/getEmployeeCustomLeavesEarned.php?idEmployee="+idEmployee,
        handleAs : "text",
        sync: true,
        load : function(data, args) {
            var lines = JSON.parse(data);
            if (isSqlElementOperationStatus(lines)) {
                showSqlElementResultInPopup(lines);
                return;
            }
            var customContent = '<b>'+i18n("extraActualLeaveEarned")+'</b>';
            customContent += '<table style="width:96%; margin-left:2%; text-align:center; border: solid 1pt;">\n\
                                  <tr style="border: solid 1pt; height: 20px;">\n\
                                  <th style="text-align:center;"><b>'+ i18n('leaveType')+'</b></th>\n\
                                  <th style="text-align:center;"><b>'+ i18n('colName')+'</b></th>\n\
                                  <th style="text-align:center;"><b>'+ i18n('quantity')+'</b></th></tr>';
            var len =lines.length;
            var find = false;
            for(var i=0;i<len;i++){
               var lenCustom = lines[i].custom.length;
               if (lenCustom!==0) {
                    find=true;
                    customContent += '<tr style="border: solid 1pt; height: 20px;">';
                    customContent += '<td style="background-color:'+lines[i].lvTColor+';color:'+lines[i].lvTOppositeColor+';">'+lines[i].lvTName+'</td>';                                   
                    var first = true;
                    for(var j=0;j<lenCustom;j++) {
                        if (first===false) {
                            customContent += '<tr style="border: solid 1pt; height: 20px;">';
                            customContent += '<td style="text-align:right;" colspan="2">'+lines[i].custom[j].name+'</td>';
                        } else {
                            customContent += '<td>'+lines[i].custom[j].name+'</td>';
                        }
                        customContent += '<td>'+lines[i].custom[j].quantity+'</td>';
                        if (first===false) {
                            customContent += '</tr>';
                        }
                        first=false;
                    }
                    customContent += '</tr>';
               } 
            }
            customContent += '</table>';
            if (find) {
                document.getElementById("customLeaveEarnedOfEmployee").innerHTML=customContent;
            } else {
                document.getElementById("customLeaveEarnedOfEmployee").innerHTML="";
            }    
        },
        error: function(error){
            showXhrErrorInErrorPopup(error, 'getEmployeeCustomLeavesEarned');
        }
    });
}

 
/**
 * Refresh the content of the summary after the save/delete of a leaveType or the change of employee (refreh all the content of the tab summary) 
 * @returns {undefined}
 */
function refreshEmployeeLeavesSummary(){
    var idEmployee=document.getElementById('idEmployeeCalendar').value;
    //xhrGet, return an array with every lines to be put in the div summaryLeaveEarnedOfEmployee
    //args=idEmployee
    //return lvType:(idLeaveType/color),lvTypeOfEmpContractType:(periodDuration),lvEarned:(startDate/endDate/quantity/leftQuantity), 
    dojo.xhrGet({
        url : "../tool/getEmployeeLeaveEarnedSummary.php?idEmployee="+idEmployee,
        handleAs : "text",
        sync: true,
        load : function(data, args) {
            var lines = JSON.parse(data);
            if (isSqlElementOperationStatus(lines)) {
                showSqlElementResultInPopup(lines);
                return;
            }
            var summaryContent = '<b>'+i18n("synthesisOfLeaveEarned")+'</b>';
            summaryContent += '<table style="width:96%; margin-left:2%; text-align:center; border: solid 1pt;">\n\
                                  <tr style="border: solid 1pt; height: 20px;">\n\
                                  <th style="text-align:center;"><b>'+ i18n('leaveType')+'</b></th>\n\
                                  <th style="text-align:center;"><b>'+ i18n('colPeriodDuration')+'</b></th>\n\
                                  <th style="text-align:center;"><b>'+ i18n('leavePeriod')+'</b></th>\n\
                                  <th style="text-align:center;"><b>'+ i18n('quantity')+'</b></th>\n\
                                  <th style="text-align:center;"><b>'+ i18n('taken')+'</b></th>\n\
                                  <th style="text-align:center;"><b>'+ i18n('colLeft')+'</b></th>\n\
                                  <th style="text-align:center;"><b>'+ i18n('earnedPeriodPlusOne')+'</b></th></tr>';
            var len =lines.length;
            for(var i=0;i<len;i++){
               summaryContent += '<tr style="border: solid 1pt; height: 20px;">';
               summaryContent += '<td style="background-color:'+lines[i].lvTColor+';color:'+lines[i].lvTOppositeColor+';">'+lines[i].lvTName+'</td>';
               summaryContent += '<td>'+lines[i].periodDuration+'</td>';
               summaryContent += '<td>'+lines[i].startDateEndDate+'</td>';
               summaryContent += '<td>'+lines[i].quantity+'</td>';
               summaryContent += '<td>'+lines[i].taken+'</td>';
               summaryContent += '<td>'+lines[i].left+'</td>';
               summaryContent += '<td>'+lines[i].earnedPeriodPlusOne+'</td>';
               summaryContent += '</tr>';

            }
            summaryContent += '</table>';
            document.getElementById("summaryLeaveEarnedOfEmployee").innerHTML=summaryContent;
        },
        error: function(error){
            showXhrErrorInErrorPopup(error, 'getEmployeeLeaveEarnedSummary');
        }
    });
}

/**
 * Return an object containing the full status informations
 * @param {string} from : 'fromLeaveCalendar' if function call from leave calendar
 *                        'fromLeaveMain' if function called from LeaveMain
 * @param {integer} idStatus : The status'id for which retrieve informations
 * @param {integer} idLeaveType : The leave type's id for which retrieve statuses assiociated to this
 * @returns {object} : Null if informations not found. Else, the Status object. 
 */
function getFullStatusInfo(from,idStatus, idLeaveType) {
    var theFullStatusInfo=null;

    if (idStatus===null || idLeaveType===null || from==="") {return null;}

    if (wfStatusArray.length===0) {
        getWorkflowStatusesOfLeaveType(from,idLeaveType);
    }                    
        
    var lengthStatusArray = wfStatusArray.length;
    for(var i = 0; i< lengthStatusArray; i++){
        if (idStatus==wfStatusArray[i].id) {
            theFullStatusInfo = wfStatusArray[i];
            break;
        }
    }
    return theFullStatusInfo;
}

/**
 * Determine if the status passed in parameter is a neutral status
 * @param {object} theFullStatusInfo : The object containing the status
 * @returns {Boolean} : True if the status as setSubmittedLeave = 0, setAcceptedLeave = 0, setCancelledLeave = 0
 */
function isANeutralStatus(theFullStatusInfo) {
    if (theFullStatusInfo===null) { return false;}
    return ((theFullStatusInfo.setSubmittedLeave==1 || theFullStatusInfo.setAcceptedLeave==1 || theFullStatusInfo.setRejectedLeave==1)?false:true);
}

// =========================================================
// POPUP FUNCTIONS
// =========================================================

/**
 * Show the popup after clicking on empty grid calendar (creating an item) or dblClicking/resizing/moving it to modify it
 * @param {integer} idLeave : The id of leave to show
 * @param {integer} leaveType : The id leaveType of the leave
 * @param {integer} status : The id status of the leave
 * @param {date} startDate : The start date of the leave
 * @param {date} endDate : The end date of the leave
 * @param {string} startAMPM : The start AM, PM of the leave = AM = After Midnight - PM = Post Midnight
 * @param {string} endAMPM : The end AM, PM of the leave = AM = After Midnight - PM = Post Midnight
 * @param {float} nbDays : The number of opened days of the leave
 * @param {string} reason : The reason of the leave
 * @param {integer} statusOK : 1 if leave is unsynchronized vs its status
 * @param {integer} statusUnknow : 1 if leave has a status out of the leave type workflow
 * @returns {undefined}
 */
function showModifPopup(idLeave, leaveType, status, startDate, endDate, startAMPM, endAMPM, nbDays, reason, statusOK, statusUnknow){
    if (leaveType=="0") {return;}
    
    var popupStatusSelectOptions = [];

    var theFullStatus = getFullStatusInfo("fromLeaveCalendar",status, leaveType);
    var neutralStatus = isANeutralStatus(theFullStatus);    

    if (theFullStatus===null || statusUnknow=="1") {
    // Unknow status
        // Add popup option to popup status = Unknow
        popupStatusSelectOptions.push({value:0, label:i18n('unknown'), selected:true, disabled:true});
        dijit.byId('popupStatus').set('value', 0);
        // Disabled popup status
        dijit.byId('popupStatus').set('disabled', true);
        // Disabled validateButtonCalendarPopup et deleteButtonCalendarPopup
        validateButtonCalendarPopup.set('disabled', true);
        deleteButtonCalendarPopup.set('disabled', true);
    }

    // Populate popupStatus Options
    lengthStatusArray = wfStatusArray.length;
    for(var i = 0; i< lengthStatusArray; i++){
        popupStatusSelectOptions.push({value:wfStatusArray[i].id, label:wfStatusArray[i].name, selected:false, disabled:false});                            
    }

    // Enable or not popupStatus Options
    popupStatusSelectOptions = disabledEnabledPopupStatusOption(status,popupStatusSelectOptions,leaveType);

    cancelButtonCalendarPopup.set("disabled", false);
    dojo.setStyle(dijit.byId('leavePopup').closeButtonNode,{"display": "", "visibility": ""});

    // On new leave or status of leave not allowed => status can't be changed
    if (idLeave==null || theFullStatus==null || statusOK=="1" || statusUnknow=="1") {
        dijit.byId('popupStatus').set("disabled", true);
    } else {
        dijit.byId('popupStatus').set("disabled", false);
    }
    if(neutralStatus && theFullStatus!=null && statusOK!="1" && statusUnknow!="1"){
        dijit.byId('popupLeaveType').set("disabled", false);
        dijit.byId('popupStartDate').set("disabled", false);
        dijit.byId('popupEndDate').set("disabled", false);
        dijit.byId("popupStartAM").set("disabled", false);
        dijit.byId("popupStartPM").set("disabled", false);
        dijit.byId("popupEndAM").set("disabled", false);
        dijit.byId("popupEndPM").set("disabled", false);
        dijit.byId('popupReason').set("disabled", false);                        
        validateButtonCalendarPopup.set("disabled", false);
        deleteButtonCalendarPopup.set("disabled",false);
    }else{
        dijit.byId('popupLeaveType').set("disabled", true);
        dijit.byId('popupStartDate').set("disabled", true);
        dijit.byId('popupEndDate').set("disabled", true);
        dijit.byId("popupStartAM").set("disabled", true);
        dijit.byId("popupStartPM").set("disabled", true);
        dijit.byId("popupEndAM").set("disabled", true);
        dijit.byId("popupEndPM").set("disabled", true);
        dijit.byId('popupReason').set("disabled", true);
        if (theFullStatus===null || statusOK=="1" || statusUnknow=="1") {
            validateButtonCalendarPopup.set("disabled", true);                            
        } else if(theFullStatus.setRejectedLeave=="0" || theFullStatus.setAcceptedLeave=="0") {
            validateButtonCalendarPopup.set("disabled", false);
        } else {
            validateButtonCalendarPopup.set("disabled", true);
        }
        deleteButtonCalendarPopup.set("disabled", true);
    }
    if(creatBool){
        deleteButtonCalendarPopup.set("disabled", true);
    }

//to set the values of the fields of the popup before opening it
    // The leave id
    dijit.byId('popupLeaveId').set("value", idLeave);
    
    // The popup of types
    // Disable type option if employee has not left for a type
    var selectLeaveTypeFirst = [];
    for(var i=0;i<popupTypeSelectOptions.length;i++) {
        if (!(popupTypeSelectOptions[i].value in leftArray)) {
            popupTypeSelectOptions[i].disabled=true;
        } else {
            popupTypeSelectOptions[i].disabled=false;
            selectLeaveTypeFirst.push(popupTypeSelectOptions[i].value);
        }
    }
    dijit.byId('popupLeaveType').set("options", popupTypeSelectOptions);
    //Put the first value in the select menu of leave type
    dijit.byId('popupLeaveType').attr('value', selectLeaveTypeFirst[0]);

    // The popup of status
    dijit.byId('popupStatus').set("options", popupStatusSelectOptions);
    dijit.byId('popupStatus').attr('value', status);

    // popup startDate et EndDate
    dijit.byId('popupStartDate').set("value", startDate);
    dijit.byId('popupEndDate').set("value", endDate);

    // popup AM & PM
    if(startAMPM==="AM"){
        dijit.byId("popupStartAM").set("checked",true);
        dijit.byId("popupStartPM").set("checked",false);
    }else{
        dijit.byId("popupStartAM").set("checked",false);
        dijit.byId("popupStartPM").set("checked",true);
    }
    if(endAMPM==="AM"){                     
        dijit.byId("popupEndAM").set("checked",true);
        dijit.byId("popupEndPM").set("checked",false);
    }else{
        dijit.byId("popupEndAM").set("checked",false);
        dijit.byId("popupEndPM").set("checked",true);
    }                    

    // popup nb days
    dijit.byId('popupNbDays').set("value", nbDays);
    
    //popup NbRemainingDays
    dijit.byId('popupReason').set("value", reason);            
    dijit.byId('leavePopup').show();
}

/**
 * Function called when the validate button of popup is activate
 * @returns {undefined}
 */    
function validPopupAction(){
    showWait();
    dijit.byId('leavePopup').hide();
    var idEmpl = getIdEmployeeInLeaveCalMain(true);

    //the url for the xhrGet request to save the leave
    var url = "../tool/saveLeaveOfCalendar.php?\n\
            idEmployee="+idEmpl+"&\n\
            idLeaveType="+dijit.byId("popupLeaveType").value+"&\n\
            idLeaveStatus="+dijit.byId("popupStatus").value+"&\n\
            &startDate="+dijit.byId("popupStartDate").value+"&\n\
            endDate="+dijit.byId("popupEndDate").value+"&nbDays="+dijit.byId("popupNbDays").value+"&comment="+dijit.byId("popupReason").value;
            if(dijit.byId("popupStartAM").checked){
                url +="&startAMPM=AM";  
            }else{
                url +="&startAMPM=PM";  
            }
            if(dijit.byId("popupEndAM").checked){
                url +="&endAMPM=AM";  
            }else{
                url +="&endAMPM=PM";  
            }
    if(creatBool === true){
        url = url + "&create=true";
    }else{//if it's a modification, give the id of the leave to update it
       url = url + "&create=false&idLeave="+dijit.byId("popupLeaveId").value;
    }
    //the request to save the leave, is synchronous so the new leave is displayed after the call to fillCalendarStore()
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        sync: true,
        load : function(data, args) {
            var res=JSON.parse(data);
            showSqlElementResultInPopup(res);
            refreshEmployeeLeavesSummary();
        },
        error: function(error){
            showXhrErrorInErrorPopup(error, 'saveLeaveOfCalendar');
        }
    });
}

/**
 * Function called when the delete button of popup is activate
 * @returns {undefined}
 */
function deletePopupAction(){
    showWait();
    dijit.byId('leavePopup').hide();
    //if the object is already saved on the database delete it and recorded
    if(creatBool === false){
       dojo.xhrGet({
            url : "../tool/deleteLeaveOfCalendar.php?idLeave="+dijit.byId("popupLeaveId").value,
            handleAs : "text",
            sync: true,
            load : function(data, args) {
                var res=JSON.parse(data);
                showSqlElementResultInPopup(res);
                refreshEmployeeLeavesSummary();
            },
            error: function(error){
                showXhrErrorInErrorPopup(error, 'deleteLeaveOfCalendar');
            }
        });                       

    } else {
        hideWait();
    }
}

/**
  * Function called when the cancel button of popup is activate 
 * @returns {undefined}
 */
function cancelPopupAction(){
        dijit.byId('leavePopup').hide();
}

/**
 * Function to disable the buttons of the popup
 * @returns {undefined}
 */
function disablePopupButtons(){
    validateButtonCalendarPopup.set("disabled", true);
    deleteButtonCalendarPopup.set("disabled", true);
    cancelButtonCalendarPopup.set("disabled", true);
    //the closeButtonNode is a <span>, it can't be disabled
    dojo.setStyle(dijit.byId('leavePopup').closeButtonNode,{"display": "none", "visibility": "hidden"});
}

/**
 * Function to treate changes on status in the popup
 * @returns {undefined}
 */
function changesPopupStatus() {
return;    
}

//for the changes of the checkboxes of the popup in view/LeaveCalendar.php
/**
 * define the action of the checkbox StartAM of the popup, if it has been ticked, untick the checkbox StartPM and inversely
 * @returns void
 */
function changesPopupStartAM() {
    var idRes = getIdEmployeeInLeaveCalMain(true);
    var idUser = getIdUserInLeaveCalendar();
    var input = dijit.byId("popupStartAM");
    if(input.checked){
        dijit.byId("popupStartPM").set("checked",false);
    }else{
        dijit.byId("popupStartPM").set("checked",true);
    }
    calculateHalfDaysForLeave('popupStartDate', 'popupEndDate', 'popupStartAM', 'popupStartPM', 'popupEndAM', 'popupEndPM', 'popupNbDays', idRes, idUser);
    calculateNbRemainingDays("fromLeaveCalendar", idRes, idUser);
}

function changesPopupStartPM() {
    var idRes = getIdEmployeeInLeaveCalMain(true);
    var idUser = getIdUserInLeaveCalendar();
    var input = dijit.byId("popupStartPM");
    if(input.checked){
        dijit.byId("popupStartAM").set("checked",false);
    }else{
        dijit.byId("popupStartAM").set("checked",true);
    }    
    calculateHalfDaysForLeave('popupStartDate', 'popupEndDate', 'popupStartAM', 'popupStartPM', 'popupEndAM', 'popupEndPM', 'popupNbDays', idRes, idUser);
    calculateNbRemainingDays("fromLeaveCalendar", idRes, idUser);
}

function changesPopupEndAM() {
    var idRes = getIdEmployeeInLeaveCalMain(true);
    var idUser = getIdUserInLeaveCalendar();
    var input = dijit.byId("popupEndAM");
    if(input.checked){
        dijit.byId("popupEndPM").set("checked",false);
    }else{
        dijit.byId("popupEndPM").set("checked",true);
    }
    calculateHalfDaysForLeave('popupStartDate', 'popupEndDate', 'popupStartAM', 'popupStartPM', 'popupEndAM', 'popupEndPM', 'popupNbDays', idRes, idUser);
    calculateNbRemainingDays("fromLeaveCalendar", idRes, idUser);
}

function changesPopupEndPM() {
    var idRes = getIdEmployeeInLeaveCalMain(true);
    var idUser = getIdUserInLeaveCalendar();
   var input = dijit.byId("popupEndPM");
    if(input.checked){
        dijit.byId("popupEndAM").set("checked",false);
    }else{
        dijit.byId("popupEndAM").set("checked",true);
    }
    calculateHalfDaysForLeave('popupStartDate', 'popupEndDate', 'popupStartAM', 'popupStartPM', 'popupEndAM', 'popupEndPM', 'popupNbDays', idRes, idUser);
    calculateNbRemainingDays("fromLeaveCalendar", idRes, idUser);
}

// ==================================================================
// FUNCTIONS FOR DISPLAYING CALENDAR AND MANAGING CALENDAR EVENTS
// ===================================================================
var calendar = null;
/** 
 * contains the function to create the calendar in view/leaveCalendar.php at the div #calendarNode
 * @returns void
 */
function leaveCalendarDisplay(){ 
    require(["dojo/ready", 
             "dojox/calendar/Calendar", 
             "dojo/dom-construct", 
             "dojo/store/Observable", 
             "dojo/store/Memory", 
             "dojo/dom-style" 
            ],
        function(ready, projeqtorCalendar, domConstruct, Observable, Memory, domStyle){
            ready(function(){
            
            //creation of the new calendar    
                calendar = new projeqtorCalendar({
                    date: new Date(),
                    dateInterval: "month",
                    // Item css style in function of it's status
                    cssClassFunc: function(item){
                        if (typeof(item.color)!=="undefined" && typeof(item.status)!=="undefined") {
                            createLeaveStatusClass('.leaveStatus_'+item.status, item.color);
                        }
                        return item.cssStyle;
                    },
                    style: "position:relative;width:100%;height:100%"
                }, "calendarNode");                                
                
                // Hidden unused buttons
                dojo.byId(calendar.dayButton.id).style.display="none";
                dojo.byId(calendar.todayButton.id).style.display="none";
                dojo.byId(calendar.monthButton.id).style.display="none";
                dojo.byId(calendar.weekButton.id).style.display="none";
                dojo.byId(calendar.fourDaysButton.id).style.display="none";
                dojo.byId(calendar._startupWidgets[3].id).style.display="none";
                dojo.byId(calendar._startupWidgets[5].id).style.display="none";

            //function to fill the store of the calendar
                var fillCalendarStore = function(){
                    var cY = calendar.date.getFullYear();
                    var cM = calendar.date.getMonth();
                    //startDate and endDate are string to respect the format required for the request
                    var startDate = cM===0 ? ((cY-1)+"1223") : (cY + "" + ((cM) <10 ? "0"+(cM) : (cM) ) + "21");
                    var endDate = ( (cM+2) <13 ? (cY + "" + ((cM + 2) <10 ? "0"+(cM+2) : (cM+2) ) + "07"): ((cY+1) + "0107") );
                    fillLeavesCalendarDataArray(startDate,endDate);
                    calendar.set("store", new Observable(new Memory({data: leavesCalendarData})));                    
                };//end fillCalendarStore
                
            //to fill the store with the leaves of the current month at the creation of the calendar
                fillCalendarStore();
            
            // function to fill the decorationStore of the calendar (contents days off)
                var fillCalendarDecorationStore = function() {
                    var cY = calendar.date.getFullYear();
                    var cM = calendar.date.getMonth()+1;
                    var theCalendarDate = new Date(cY+'-'+(cM<10?'0':'')+cM+'-01');
                    var startDate = new Date(theCalendarDate);
                    var days = -24;
                    startDate.setDate(days);                    
                    var endDate = new Date(theCalendarDate);
                    days = 70;
                    endDate.setDate(days);
                    fillDaysOffCalendarDataArray(startDate,endDate);
                    calendar.set("decorationStore", new Observable(new Memory({data: daysOffCalendarData})));                                        
                }
                fillCalendarDecorationStore();
                
            //if there is no leaveTypes, there is no calendar
                if(leavesTypesArray.length===0){
                    document.getElementById("calendarNode").innerHTML = "<p>"+i18n("noLeaveTypeNoLeaveCalendar")+"</p>";
                    return;
                }
            
            
            //to remove the row header column of the calendar
                domStyle.set(calendar.matrixView.columnHeader,'left','0px');
                domStyle.set(calendar.matrixView.columnHeader,'border-left','1px solid #B5BCC7');
                domStyle.set(calendar.matrixView.grid,'left','0px');
                domStyle.set(calendar.matrixView.grid,'border-left','1px solid #B5BCC7');
                domStyle.set(calendar.matrixView.itemContainer,'left','0px');
                domStyle.set(calendar.matrixView.buttonContainer,'position','relative');
                domConstruct.destroy(calendar.matrixView.rowHeader);
                domConstruct.destroy(calendar.matrixView.yearColumnHeader);
            
            //to display the current month and year of the calendar in the header
                var calendarMonthYear = "<div id=\"calendarMonthYear\" style=\"position:absolute;left:24%;top:5px;\"><table><tbody><tr><td><span>"+formatMonthYearDate(calendar.date)+"</span></td></tr></tbody></table></div>";
                domConstruct.place(calendarMonthYear, calendar.matrixView.buttonContainer, "first");
            
            
            //a function to refresh the date displayed in the header
                var refreshMonthYearInHeader = function(){
                    var calendarMonthYearContent = "<table><tbody><tr><td><span>"+formatMonthYearDate(calendar.date)+"</span></td></tr></tbody></table>";
                    document.getElementById("calendarMonthYear").innerHTML = calendarMonthYearContent;
                };
            
            //to display the next month and the current year in the header when clicking the nextButton of the calendar
                require(["dojo/on"], function(on){
                    on(calendar.nextButton, "click", function(e){
                        refreshMonthYearInHeader();
                        widgetSelectDate.set("value", calendar.date);
                    });
                });
                
            //to display the previous month and the current year in the header
                require(["dojo/on"], function(on){
                    on(calendar.previousButton, "click", function(e){
                        refreshMonthYearInHeader();
                        widgetSelectDate.set("value", calendar.date);
                    });
                });
                  
            //Arrays to make the link between the names and ids of the leaveType
                var lengthLvTArray = defaultLvTypeArray.length;
                popupTypeSelectOptions = [];
                for(var i = 0; i< lengthLvTArray; i++){
                    popupTypeSelectOptions.push({value:defaultLvTypeArray[i].id, label:defaultLvTypeArray[i].name, selected:false, disabled:false});
                }
                
                var lengthStatusArray = statusArray.length;
                var linkIdNameStatus = [];
                for(var i = 0; i< lengthStatusArray; i++){
                    linkIdNameStatus[statusArray[i].id]= statusArray[i].name;
                }
                                
            //create new item on grid dblClick
                calendar.on("gridDoubleClick", function(e){
                    creatBool = true;
                    var start, end;               
                    var cal = calendar.dateModule;                                
                    start = calendar.floorToDay(e.date);
                    end = calendar.floorToDay(e.date);
                    
                    //can't create a leave if it's a dayOff
                    for (var dayOff in daysOffCalendarData){
                        if(dayOff && dayOff.startTime && dayOff.startTime.getTime()===start.getTime()){
                            return;
                        }
                    }
                    start.setHours(9,0,0,0);
                    end.setHours(18,0,0,0);                    
                    
                    // Select leave Type with left for employee
                    var theIdLeaveType=0;
                    var theIdStatus=0;
                    if (leftArray.length!==0) {
                        for (var key in leftArray) {
                            theIdLeaveType = key;       
                            break;
                        }
                        // Select the first status of workflow associated to the leave type
                        for (var key in statusFromTo[theIdLeaveType]) {
                            theIdStatus = key;
                            break;
                        }
                    }
                    
                    var item = {
                        id: idEvent,
                        summary: "",
                        startTime: start,
                        endTime: end,
                        status: theIdStatus,
                        statusOk: 0,
                        statusUnknow: 0,
                        startAMPM: "AM",
                        endAMPM: "PM",
                        idLeave: null,
                        idLeaveType: theIdLeaveType,
                        reason: "",
                        nbDays: 1.0
                    };
                    calendar.store.add(item);
                    
                    theSelectedLeave = item;
                    status = item.status;                    
                    showModifPopup(item.idLeave, item.idLeaveType, item.status, start, end, item.startAMPM, item.endAMPM, item.nbDays, item.reason, item.statusOk, item.statusUnknow);
                    
                    idEvent++;
                });
            //end of the creation of a new item
                
            //when double clicking an item, a popup appear to modify it
                calendar.on('itemDoubleClick',function(e){
                    creatBool = false;
                    theSelectedLeave = e.item;
                    status = e.item.status;
                    showModifPopup(e.item.idLeave, e.item.idLeaveType, e.item.status, e.item.startTime, e.item.endTime, e.item.startAMPM, e.item.endAMPM, e.item.nbDays, e.item.reason, e.item.statusOK, e.item.statusUnknow);
                });
            
            //a listener to trigger the popup when an item was edited
                calendar.on("itemEditEnd", function(e){
                    creatBool = false;
                    var theFullStatus = getFullStatusInfo("fromLeaveCalendar",e.item._item.status, e.item._item.idLeaveType);
                    var neutralStatus = isANeutralStatus(theFullStatus);                        
                    if(!neutralStatus){//if the leave was validated, cancel the resize/deplacement
                        e.preventDefault();
                        e.item.startTime=e.item._item.startTime;
                        e.item.endTime=e.item._item.endTime;                        
                    }else{//calculate the new number of days of the leave
                        var idRes = getIdEmployeeInLeaveCalMain(true);
                        var idUser = getIdUserInLeaveCalendar();
                        var nbOpenDays = openDayDiffDates(e.item.startTime,e.item.endTime, idRes, idUser);
                        if(e.item._item.startAMPM==="AM" && e.item._item.endAMPM==="PM"){
                            var newNbDays = nbOpenDays;
                        }else if(e.item._item.startAMPM==="AM" && e.item._item.endAMPM==="AM"){
                            var newNbDays = nbOpenDays - 0.5;
                        }else if(e.item._item.startAMPM==="PM" && e.item._item.endAMPM==="PM"){
                            var newNbDays = nbOpenDays - 0.5;
                        }else{
                            var newNbDays = nbOpenDays - 1;
                        }
                        if(newNbDays<0){
                            newNbDays=0;
                        }
                        if(newNbDays<=0){
                            e.preventDefault();
                            e.item.startTime=e.item._item.startTime;
                            e.item.endTime=e.item._item.endTime;
                            return;
                        }                        
                        calendar.items[e.item.id]._item.nbDays = newNbDays;
                    }
                    //note: e.item.startTime is given to showModifPopup and not e.item._item.startTime, as the e.item._item contains the previous state of the object
                    theSelectedLeave = e.item._item;
                    status=e.item._item.status;
                    showModifPopup(e.item._item.idLeave, e.item._item.idLeaveType, e.item._item.status, e.item.startTime, e.item.endTime, e.item._item.startAMPM, e.item._item.endAMPM, newNbDays, e.item._item.reason, e.item._item.statusOK, e.item._item.statusUnknow);
                });
                                
            //to remove the possibility to close the popup by pressing the escape key
                dijit.byId("leavePopup")._onKey = function(){};
                                           
            //a listener for the validate button of the popup, WIP
                validateButtonCalendarPopup.on("click", function(e){
                    disablePopupButtons();
                    validPopupAction();                   
                    fillCalendarStore();
                });

            //a listener for the delete button of the popup, WIP
                deleteButtonCalendarPopup.on("click", function(e){
                    disablePopupButtons();
                    deletePopupAction();
                    fillCalendarStore();
                });

            //a listener for the cancel button of the popup, WIP
                cancelButtonCalendarPopup.on("click", function(e){
                    disablePopupButtons();
                    cancelPopupAction();                    
                    fillCalendarStore();
                });
                
            //a listener to replace the action of the button close of the popup by the action of cancel
                require(["dojo/on"], function(on){
                    on(dijit.byId('leavePopup').closeButtonNode, "click", function(e){
                        disablePopupButtons();
                        cancelPopupAction();
                    });
                });
                
            //a listener for the button to refresh the calendar
                refreshCalendarButton.on("click", function(e){
                    calendar.set('date', (new Date()));
                    widgetSelectDate.set("value", new Date());
                    refreshMonthYearInHeader();
                    fillCalendarStore();
                    fillCalendarDecorationStore();

                });
            
            // a listener for the select leaveEmployeeSelect to refresh the calendar this leave of selected employee
                if (dojo.byId('leaveEmployeeSelect')) {
                    leaveEmployeeSelect.on("change", function(e){
                        calendar.set('date', (new Date()));
                        widgetSelectDate.set("value", new Date());
                        document.getElementById('idEmployeeCalendar').value = e;
                        refreshMonthYearInHeader();
                        fillCalendarStore();
                        fillCalendarDecorationStore();
                        refreshEmployeeCustomLeavesEarned();
                        refreshEmployeeLeavesSummary();
                    });
                }
            
            //a DateTextBox to change the month and/or year of the calendar
                widgetSelectDate.on("change", function(e){
                    calendar.set("date", e);
                    refreshMonthYearInHeader();
                    fillCalendarStore();
                    fillCalendarDecorationStore();
                });
                
                widgetSelectDate.set("value", new Date());
            
            //a tooltip to show more infos about a leave when passing the mouse over it
                calendar.on("itemRollOver", function (e) {
                    var theStatusName = "";
                    var theExplainStatusNone = "";
                    
                    // Transition
                    var theTransition = i18n("transition")+"&nbsp;:&nbsp;"+e.item.transition+"<br/>";

                    // Reason
                    var reason=i18n("reason")+"&nbsp;:&nbsp;";
                    if(e.item.reason){
                        reason = reason + e.item.reason;
                    } else{
                        reason = reason + i18n("paramNone");
                    }
                    reason = reason + "<br/>";

                    // Duration
                    var duration = i18n("colDuration")+"&nbsp;:&nbsp;"+e.item.nbDays+"&nbsp;"+(Number(e.item.nbDays)>1?i18n("days"):i18n("day"))+"<br/>";
                    
                    // Status and explaination
                    if (typeof(linkIdNameStatus[e.item.status])=="undefined" || e.item.statusUnknow=="1") {
                        theStatusName = i18n('unknown');
                        theExplainStatusNone = i18n("cantBeUpdatedUntilStatusIsUnknown");
                    } else if (e.item.statusOK=="1") {
                        theExplainStatusNone = i18n("cantBeUpdatedUntilStatusIsUnsynchronized");                        
                        theStatusName = linkIdNameStatus[e.item.status] ;
                    } else {
                        theStatusName = linkIdNameStatus[e.item.status] ;
                    }
                    var theStatus = i18n("colIdStatus")+"&nbsp:&nbsp;"+theStatusName;

                    var toolTipContent = theTransition+duration+reason+theStatus;
                    if (theExplainStatusNone!=="") {
                        toolTipContent = toolTipContent + "<br/><br/><p>"+theExplainStatusNone+"</p>";
                    }
                    tooltipNode = e.renderer.domNode;     
                    dijit.showTooltip(toolTipContent,  tooltipNode, ["below"]); 

                 }); 

                calendar.on("itemRollOut", function (e) { 
                    dijit.hideTooltip(tooltipNode); 
                }); 

                calendar.on("itemEditBegin", function (e) { 
                    dijit.hideTooltip(tooltipNode); 
                });            
            });
        }
    );
}//end displayCalendar

// ********************************
// END FUNCTIONS FOR LEAVE CALENDAR
// ********************************

// ***********************************
//FUNCTIONS FOR EmploymentContractType
// ***********************************

/**
 * to add a new lvTypeOfEmpContractType, Called in model/EmploymentContractTypeMain.php
 * 
 * @param {int} idEmploymentContractType
 * @returns {undefined}
 */
function addLvTypeOfEmpContractType(idEmploymentContractType){
    var params="&idEmploymentContractType="+idEmploymentContractType;
    params +="&addMode=true&editMode=false";
    
    loadDialog('dialogLvTypeOfEmpContractType',null,true,params,true);
}

/**
 * to save a lvTypeOfEmploymentContractType, called in tool/dynamicDialogLvTypeOfEmpContractType.php
 * 
 * @returns void
 */
function saveLvTypeOfEmpContractType() {
  var formVar=dijit.byId('lvTypeOfContractTypeForm');
  if (formVar.validate()) {
    loadContent("../tool/saveLvTypeOfEmpContractType.php", "resultDivMain",
        "lvTypeOfContractTypeForm", true, 'leaveTypeOfEmploymentContractType');
    dijit.byId('dialogLvTypeOfEmpContractType').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}


/**
 * defines the action of the button to edit a lvTypeOfEmploymentContractType, Called in model/EmploymentContractTypeMain.php
 * 
 * @param {int} idLvTypeOfContractType
 * @param {int} idEmploymentContractType
 * @param {int} idLeaveType
 * @param {int} startMonthPeriod between 1 and 12
 * @param {int} startDayPeriod (not used as this attribute is set to 1 in tool/dynamicDialogLvTypeOfEmpContractType.php)
 * @param {int} periodDuration 
 * @param {float} quantity
 * @param {int} earnedPeriod
 * @param {int} validityDuration
 * @param {int("bool")} isJustifiable
 * @returns void
 */
function editLvTypeOfEmpContractType (idLvTypeOfContractType,
                                      idEmploymentContractType,
                                      idLeaveType,
                                      startMonthPeriod,
                                      startDayPeriod,
                                      periodDuration,
                                      nbDaysAfterNowLeaveDemandIsAllowed,
                                      nbDaysBeforeNowLeaveDemandIsAllowed,
                                      quantity,
                                      earnedPeriod,
                                      isIntegerQuotity,
                                      validityDuration,
                                      isJustifiable,
                                      isAnticipated){
    var params = "&idLvTypeOfContractType="+idLvTypeOfContractType;
    params+="&idEmploymentContractType="+idEmploymentContractType;
    params+="&idLeaveType="+idLeaveType;
    params+="&startMonthPeriod="+startMonthPeriod;
    params+="&startDayPeriod="+startDayPeriod;
    params+="&periodDuration="+periodDuration;
    params+="&nbDaysBeforeNowLeaveDemandIsAllowed="+nbDaysBeforeNowLeaveDemandIsAllowed;
    params+="&nbDaysAfterNowLeaveDemandIsAllowed="+nbDaysAfterNowLeaveDemandIsAllowed;    
    params+="&quantity="+quantity;
    params+="&earnedPeriod="+earnedPeriod;
    params+="&isIntegerQuotity="+isIntegerQuotity;
    params+="&validityDuration="+validityDuration;
    params+="&isJustifiable="+isJustifiable;
    params+="&isAnticipated="+isAnticipated;
    params +="&addMode=false&editMode=true";
    loadDialog('dialogLvTypeOfEmpContractType',null,true,params,true);
}

/**
 * Used in employmentContractTypeMain, defines the actions of deletion of a lvTypeOfEmpContractType
 * 
 * @param {int} id of the LvTypeOfEmpContractType to delete
 * @returns void 
 */
function removeLvTypeOfEmpContractType(id){
    if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeLvTypeOfEmpContractType.php?idLvTypeOfContractType="+id, "resultDivMain",
        null, true, 'leaveTypeOfEmploymentContractType');
  };
  msg=i18n('confirmDeleteLvTypeOfContractType', new Array(id));
  showConfirm(msg, actionOK);
}

/**
 * to add a new CustomEarnedRulesOfEmpContractType, Called in model/EmploymentContractTypeMain.php
 * 
 * @param {int} idEmploymentContractType
 * @returns {undefined}
 */
function addCustomEarnedRulesOfEmpContractType(idEmploymentContractType){
    var params="&idEmploymentContractType="+idEmploymentContractType;
    params +="&addMode=true&editMode=false";
    
    loadDialog('dialogCustomEarnedRulesOfEmpContractType',null,true,params,true);
}

/**
 * defines the action of the button to edit a CustomEarnedRulesOfEmpContractType, Called in model/EmploymentContractTypeMain.php
 * 
 * @param {int} idCustomEarnedRules
 * @param {int} idEmploymentContractType
 * @param {int} idLeaveType
 * @param {float} quantity
 * @param {string} name
 * @param {string} rule
 * @param {string} whereClause
 * @returns void
 */
function editCustomEarnedRulesOfEmpContractType (idCustomEarnedRules,
                                                 idEmploymentContractType,
                                                 idLeaveType,
                                                 quantity,
                                                 name,
                                                 rule,
                                                 whereClause){
    var params = "&idCustomEarnedRules="+idCustomEarnedRules;
    params+="&idEmploymentContractType="+idEmploymentContractType;
    params+="&idLeaveType="+idLeaveType;
    params+="&quantity="+quantity;
    params+="&name="+name;
    params+="&rule="+rule;
    params+="&whereClause="+whereClause;
    params +="&addMode=false&editMode=true";
    loadDialog('dialogCustomEarnedRulesOfEmpContractType',null,true,params,true);
}

/**
 * Used in employmentContractTypeMain, defines the actions of deletion of a CustomEarnedRulesOfEmpContractType
 * 
 * @param {int} idCustomEarnedRulesOfEmpContractType of the CustomEarnedRulesOfEmpContractType to delete
 * @returns void 
 */
function removeCustomEarnedRulesOfEmpContractType(idCustomEarnedRulesOfEmpContractType){
    if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeCustomEarnedRulesOfEmpContractType.php?idCustomEarnedRulesOfEmpContractType="+idCustomEarnedRulesOfEmpContractType, "resultDivMain",
        null, true, 'customEarnedRulesOfEmploymentContractType');
  };
  msg=i18n('confirmDeleteCustomEarnedRulesOfEmpContractType', new Array(id));
  showConfirm(msg, actionOK);
}

/**
 * to save a CustomEarnedRulesOfEmpContractType, called in tool/dialogCustomEarnedRulesOfEmpContractType.php
 * 
 * @returns void
 */
function saveCustomEarnedRulesOfEmpContractType() {
  var formVar=dijit.byId("customEarnedRulesOfEmpContractTypeForm");
  if (formVar.validate()) {
    loadContent("../tool/saveCustomEarnedRulesOfEmpContractType.php", "resultDivMain",
        "customEarnedRulesOfEmpContractTypeForm", true, "customEarnedRulesOfEmploymentContractType");
    dijit.byId('dialogCustomEarnedRulesOfEmpContractType').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}
// ***************************************
//END FUNCTIONS FOR EmploymentContractType
// ***************************************

// ***************************************
// BEGIN FUNCTIONS FOR EmployeesManaged
// ***************************************
/**
 * to add a new EmployeesManaged, Called in model/EmployesManagerMain.php
 * 
 * @param {int} idEmployeeManager
 * @returns {undefined}
 */
function addEmployeesManaged(idEmployeeManager){
    var params="&idEmployeeManager="+idEmployeeManager;
    params +="&addMode=true&editMode=false";
    
    loadDialog('dialogEmployeesManaged',null,true,params,true);
}

/**
 * defines the action of the button to edit a EmployeesManaged, Called in model/EmployesManagerMain.php
 * 
 * @param {int} idEmployeesManaged
 * @param {int} idEmployeeManager
 * @param {int} idEmployee
 * @param {float} startDate
 * @param {string} endDate
 * @param {string} idle
 * @returns void
 */
function editEmployeesManaged (idEmployeesManaged,
                               idEmployeeManager,
                               idEmployee,
                               startDate,
                               endDate,
                               idle){
    var params = "&id="+idEmployeesManaged;
    params+="&idEmployeeManager="+idEmployeeManager;
    params+="&idEmployee="+idEmployee;
    params+="&startDate="+startDate;
    params+="&endDate="+endDate;
    params+="&idle="+idle;
    params +="&addMode=false&editMode=true";
    loadDialog('dialogEmployeesManaged',null,true,params,true);
}

/**
 * Used in model/EmployesManagerMain.php, defines the actions of deletion of a EmployeesManaged
 * 
 * @param {int} id of the EmployeesManaged to delete
 * @returns void 
 */
function removeEmployeesManaged(id){
    if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeEmployeesManaged.php?id="+id, "resultDivMain",
        null, true, 'employeesManaged');
  };
  msg=i18n('confirmDeleteEmployeesManaged', new Array(id));
  showConfirm(msg, actionOK);
}

/**
 * to save a EmployeesManaged, called in tool/dialogEmployeesManaged.php
 * 
 * @returns void
 */
function saveEmployeesManaged() {
  var formVar=dijit.byId("employeesManagedForm");
  if (formVar.validate()) {
    loadContent("../tool/saveEmployeesManaged.php", "resultDivMain",
        "employeesManagedForm", true, "employeesManaged");
    dijit.byId('dialogEmployeesManaged').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}
// ***************************************
// END FUNCTIONS FOR EmployeesManaged
// ***************************************


// ***************************************
// FUNCTIONS FOR Leave
// ***************************************
function changeStatusInMaintenanceOfLeave(idUser) {
    var idStatus = dijit.byId("workflowStatus").item.id;
    var submitted = dijit.byId("_spe_submitted").checked;
    var accepted = dijit.byId("_spe_accepted").checked;
    var rejected = dijit.byId("_spe_rejected").checked;
        
    if (wfStatusArray.length===0) {
        getWorkflowStatusesOfLeaveType(null,dijit.byId("idLeaveType").value,dijit.byId("idEmployee").value,idUser);        
    }
    
    var theNewStatus = null;
    for (var i=0;i<wfStatusArray.length;i++) {
        if (wfStatusArray[i].id == idStatus) {
            theNewStatus = wfStatusArray[i];
            break;
        }
    }
    var styleRed = style="text-align:center; background-color:red !important;";
    var styleNormal = "text-align:center;";

    if (theNewStatus === null) {
        document.getElementById('td_submittedS').style = styleRed;
        document.getElementById('td_acceptedS').style = styleRed;
        document.getElementById('td_rejectedS').style = styleRed;        
        dijit.byId("newIdStatus").set("value", 0);
        return;
    }
    dijit.byId("newIdStatus").set("value", idStatus);
    
    var submittedS = (theNewStatus.setSubmittedLeave=="0"?false:true);
    var acceptedS = (theNewStatus.setAcceptedLeave=="0"?false:true);
    var rejectedS = (theNewStatus.setRejectedLeave=="0"?false:true);
    
    dijit.byId("_spe_submittedS").set("checked",submittedS);
    dijit.byId("_spe_acceptedS").set("checked",acceptedS);
    dijit.byId("_spe_rejectedS").set("checked",rejectedS);
        
    document.getElementById('td_submittedS').style = (submitted !== submittedS?styleRed:styleNormal);
    document.getElementById('td_acceptedS').style = (accepted !== acceptedS?styleRed:styleNormal);
    document.getElementById('td_rejectedS').style = (rejected !== rejectedS?styleRed:styleNormal);

    formChanged();
}
// ***************************************
// END FUNCTIONS FOR Leave
// ***************************************


// ***************************************
// OTHERS FUNCTIONS
// ***************************************
/**
 * Return true if the class and id passed in parameters are leave System object (class and id) dedicated to the leave system
 * ie : is the project dedicated to the leave system.
  * @return boolean
 */
function isLeaveMngConditionsKO() {
    var theLeaveClasses = new Array(
        'Project', 
        'Activity', 
        'EmploymentContract',
        'EmploymentContractType'
    );
    // If no id in screen => No leave conditions to manage
    if (!dojo.byId("id")) {return false;}    
    var id = dojo.byId("id").value;
    // If no objectClassName in screen => No leave conditions to manage
    if(!dojo.byId("objectClassName")) {return false;}
    var objClass = dojo.byId("objectClassName").value;
    
    // It's not a class that has conditions for leave
    if (theLeaveClasses.indexOf(objClass)<0) {return false;}
    
    // Can't delete or copy an EmploymentContract. Delete is done with deleted resource
    if (objClass == 'EmploymentContract') {return true;}
    
    // Can't delete or copy an EmploymentContractType that is the default type (isDefault=1)
    if (objClass == 'EmploymentContractType' && 
        dojo.byId('isDefault') && 
        dojo.byId('isDefault').checked  == true) 
    {return true;}
        
    // Project and isLeaveMngProject = 1 => Depending on Leave System
    if (objClass == 'Project' && 
        dojo.byId('isLeaveMngProject') && 
        dojo.byId('isLeaveMngProject').checked==true)
    {return true;}
    
    // Activity and its Project is the leave management project => Depending on Leave System
    if (objClass == 'Activity' && typeof(document.getElementById('isLeaveMngActivity'))!='undefined') {
        if (document.getElementById('isLeaveMngActivity') && document.getElementById('isLeaveMngActivity').value == 1) { return true;}
    } 
    
    // Else return false
    return false;
}


/**
 * used in dynamicDialogCustomEarnedRulesOfEmpContractType.php
 * @returns void
 */
function addOpInTextBoxForCustomEarnedRules() {
    var textBox = 'ruleCustomEarnedRuleTextArea';
    var selectOp = 'ruleCustomEarnedRuleHelpOperators';
    var selectedOp = dijit.byId(selectOp).getValue();

    oldText = dijit.byId(textBox).getValue();
    element = document.getElementById(textBox);
    var val = element.value;
    cursPos = val.slice(0, element.selectionStart).length;

    textToAdd=selectedOp+' ';
    newText = oldText.substr(0, cursPos) + textToAdd + oldText.substr(cursPos);        
    dijit.byId(textBox).setValue(newText);
}
function addOpInTextBoxWhereClauseForCustomEarnedRules() {
    var textBox = 'whereClauseCustomEarnedRuleTextArea';
    var selectOp = 'ruleCustomEarnedRuleHelpOperators';
    var selectedOp = dijit.byId(selectOp).getValue();

    oldText = dijit.byId(textBox).getValue();
    element = document.getElementById(textBox);
    var val = element.value;
    cursPos = val.slice(0, element.selectionStart).length;

    textToAdd=selectedOp+' ';
    newText = oldText.substr(0, cursPos) + textToAdd + oldText.substr(cursPos);        
    dijit.byId(textBox).setValue(newText);
}

/**
 * used in dynamicDialogCustomEarnedRulesOfEmpContractType.php
 * @returns void
 */
function addFieldInTextBoxForCustomEarnedRules() {
    var textBox = 'ruleCustomEarnedRuleTextArea';
    var selectItems = 'ruleCustomEarnedRuleHelpListItems';
    var selectedItem = dijit.byId(selectItems).getValue();
    var selectFields = 'ruleCustomEarnedRuleHelpListFields';
    var selectedField = dijit.byId(selectFields).getValue();

    oldText = dijit.byId(textBox).getValue();
    element = document.getElementById(textBox);
    var val = element.value;
    cursPos = val.slice(0, element.selectionStart).length;

    textToAdd='${'+selectedItem+'.'+selectedField+'} ';
    newText = oldText.substr(0, cursPos) + textToAdd + oldText.substr(cursPos);        
    dijit.byId(textBox).setValue(newText);
}

function addFieldInTextBoxWhereClauseForCustomEarnedRules() {
    var textBox = 'whereClauseCustomEarnedRuleTextArea';
    var selectItems = 'ruleCustomEarnedRuleHelpListItems';
    var selectedItem = dijit.byId(selectItems).getValue();
    var selectFields = 'ruleCustomEarnedRuleHelpListFields';
    var selectedField = dijit.byId(selectFields).getValue();

    oldText = dijit.byId(textBox).getValue();
    element = document.getElementById(textBox);
    var val = element.value;
    cursPos = val.slice(0, element.selectionStart).length;

    textToAdd='${'+selectedItem+'.'+selectedField+'} ';
    newText = oldText.substr(0, cursPos) + textToAdd + oldText.substr(cursPos);        
    dijit.byId(textBox).setValue(newText);
}

/**
 * used in dynamicDialogCustomEarnedRulesOfEmpContractType.php
 * @param {string} table
 * @returns void
 */
function refreshListFieldsInDialogCustomEarnedRules(table) {
    url='../tool/getListFieldsForRulableForEmpContractType.php?table='+ table;

    var selectTarget = 'ruleCustomEarnedRuleHelpListFields';
    dijit.byId(selectTarget).removeOption(dijit.byId(selectTarget).getOptions());
    dijit.byId(selectTarget).set('value','');
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        load : function(data) {
            if(data){
                var obj = JSON.parse(data);
                for ( var key in obj) {
                    var o = dojo.create("option", {label: obj[key], value: key});     
                    dijit.byId(selectTarget).addOption(o); 
                }
            }
        }
    });
}

function checkLeavesEarned(userId) {
    if (typeof(userId)=='undefined' || userId<0) {
      consoleTraceLog("No check for Leaves Earned - Unknow UserId - " + userId);
    }

    var currentDate = new Date();
    
    var url='../tool/checkLeavesEarned.php?userId='+ userId;
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        load : function(data) {
            if(data && data.indexOf('name="lastOperationStatus" value="ERROR">')<0){            	
                var result = JSON.parse(data);
                if (result!='OK') {
                  consoleTraceLog('Error on checking Leaves Earned at ' + currentDate +' - result = '+result);
                }
            }
        },
        error: function(error){
          consoleTraceLog("checkLeavesEarned Error at " + currentDate);
          consoleTraceLog(error);
        }
    });
    
    setTimeout('checkLeavesEarned('+userId+');',3600*24*1000);
}

function loadContentDashboardEmployeeManager() {
    var yearParam = "?year="+dijit.byId("yearSelect").value;
    var monthParam = "&month="+dijit.byId("monthSelect").value;
    var employeeParam = (dijit.byId("employeeSelect").value>0?"&idEmployee="+dijit.byId("employeeSelect").value:"");
    var typeParam = (dijit.byId("leaveTypeSelect").value>0?"&idLeaveType="+dijit.byId("leaveTypeSelect").value:"");
    var statusParam = (dijit.byId("leaveStatusSelect").value>0?"&idStatus="+dijit.byId("leaveStatusSelect").value:"");
    loadContent('dashboardEmployeeManager.php'+yearParam+monthParam+employeeParam+typeParam+statusParam, 'centerDiv');        
}

function nextPrevYearDashboardEmployeeManager(plusMinus, startYear, endYear) {
    var year = parseInt(dijit.byId("yearSelect").value)+plusMinus;
    if (year<startYear || year>endYear) { return; }
    dijit.byId("yearSelect").attr('value',year);
}

function nextPrevMonthDashboardEmployeeManager(plusMinus) {
    var month = parseInt(dijit.byId("monthSelect").value)+plusMinus;
    var year = parseInt(dijit.byId("yearSelect").value);
    if (month<1 || month>12) {
      if(month>12){
        month=1;
        year+=1;
      }else{
        month=12;
        year-=1;
      }
      dijit.byId("yearSelect").attr('value',year);
    }
    dijit.byId("monthSelect").attr('value',month);    
}

function validOrCancelLeave(idLeave,motif,context) {
    if (!context) context="CAL";
    if (idLeave<1) {return;}
    dojo.setStyle(dijit.byId('leaveValidCancelPopup').closeButtonNode,{"display": "", "visibility": ""});
    dijit.byId('popupLeaveId').set("value", idLeave);
    dijit.byId('validateButtonDashboardPopup').set('disabled', false);
    dijit.byId('popupReason').set("value", motif);
    
    if (context!="CAL") {
        var theStatus = 0;
        dijit.byId('popupLeaveStatus').set("disabled", true);
        if (context=="VAL") {
            dijit.byId("leaveValidCancelPopup").attr('title',i18n('validLeave'));
            for (var i=0; i<wfStatusArray.length;i++) {
                if (wfStatusArray[i].setAcceptedLeave==1) {
                    theStatus = wfStatusArray[i].id;
                    break;
                }
            }
        } else {
            dijit.byId("leaveValidCancelPopup").attr('title',i18n('cancelLeave'));
            for (var i=0; i<wfStatusArray.length;i++) {
                if (wfStatusArray[i].setRejectedLeave==1) {
                    theStatus = wfStatusArray[i].id;
                    break;
                }
            }
        }
        if (theStatus!=0) {
            dijit.byId("popupLeaveStatus").attr('value', theStatus);
        } else {
            dijit.byId('validateButtonDashboardPopup').set('disabled', true);
        }

    } else {
        dijit.byId("leaveValidCancelPopup").attr('title',i18n('validOrCancelLeave'));            
        dijit.byId('popupLeaveStatus').set("disabled", false);
    }

    dijit.byId('leaveValidCancelPopup').show();
}

function saveValidOrCancelLeaveStatus() {
    showWait();
    dijit.byId('leaveValidCancelPopup').hide();

    //the url for the xhrGet request to save ValidOrCancel leave's status
    var url  = "../tool/saveValidOrCancelStatusLeave.php";
    url += "?idLeave="+dijit.byId("popupLeaveId").value;
    url += "&idLeaveStatus="+dijit.byId("popupLeaveStatus").value;
    url += "&comment="+dijit.byId("popupReason").value;

    dojo.xhrGet({
        url : url,
        handleAs : "text",
        sync: true,
        load : function(data, args) {
            var res=JSON.parse(data);
            if (getSqlElementOperationStatus(res)=="OK") {
                loadContentDashboardEmployeeManager();
            } else {
                showSqlElementResultInPopup(res);
            }    
        },
        error: function(error){
            showXhrErrorInErrorPopup(error, 'saveValidOrCancelStatusLeave');
        }
    });
    
}

function getLeftByLeaveType(idEmployee) {
    if (idEmployee === null) {return;}

    dojo.xhrGet({
        url : "../tool/getLeftQuantityByLeaveTypeForAnEmployee?idEmployee="+idEmployee,
        handleAs : "text",
        sync: true,
        load : function(data, args) {
            datas=JSON.parse(data);
            if (isSqlElementOperationStatus(datas)) {
               showSqlElementResultInPopup(datas);
               return;
            }            
            leftArray = datas['left'];
            employeeLeft = datas['idEmployee'];
        },
        error: function(error){
            showXhrErrorInErrorPopup(error, 'getLeftQuantityByLeaveTypeForAnEmployee');
        }
    });
    
}

function exportLeaveCalendarOfDashboardEmployeeManager() {
    var year = dijit.byId("yearSelect").value;
    var month = dijit.byId("monthSelect").value;
    var idStatus = dijit.byId("leaveStatusSelect").value;
    var idLeaveType = dijit.byId("leaveTypeSelect").value;
    var idEmployee = dijit.byId("employeeSelect").value;

    var params = "year="+year;
    params = params + "&month="+month;
    params = params + "&idStatus="+idStatus;
    params = params + "&idLeaveType="+idLeaveType;
    params = params + "&idEmployee="+idEmployee;
    
    window.onbeforeunload = function() {
        return;
    };
    window.location.replace("../tool/exportLeaveCalendarOfDashboardEmployeeManager.php?"+params);
    
}

function statusSetLeaveFalse(the,id1,id2) {
    if (the.checked) {
        dijit.byId("set"+id1+"Leave").set("value",false);
        dijit.byId("set"+id2+"Leave").set("value",false);
        formChanged();
    }    
}
