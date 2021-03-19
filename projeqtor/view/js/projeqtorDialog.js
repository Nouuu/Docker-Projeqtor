/*******************************************************************************
 * COPYRIGHT NOTICE *
 * 
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org Contributors : -
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

// ============================================================================
// All specific ProjeQtOr functions and variables for Dialog Purpose
// This file is included in the main.php page, to be reachable in every context
// ============================================================================
// =============================================================================
// = Variables (global)
// =============================================================================
var filterType="";
var closeFilterListTimeout;
var openFilterListTimeout;
var closeFavoriteReportsTimeout;
var openFavoriteReportsTimeout=null;
var closeFavoriteTimeout;
var openFavoriteTimeout=null;
var popupOpenDelay=200;
var closeMenuListTimeout=null;
var openMenuListTimeout=null;
var menuListAutoshow=false;
var hideUnderMenuTimeout;
var hideUnderMenuId;
var previewhideUnderMenuId;
var stockEmailHistory = new Array();
var lastKeys= new Array();
var cplastKey;
var tabLastKeys= new Array();
var addVal= [0,0,0,0];
var paramLength;
var cpMaj=0;
var cpNum=0;
var cpChar=0;
var cpParamLength=0;
// =============================================================================
// = Wait spinner
// =============================================================================

var waitingForReply=false;
/**
 * ============================================================================
 * Shows a wait spinner
 * 
 * @return void
 */
function showWait() {
  if (dojo.byId("wait")) {
    showField("wait");
    waitingForReply=true;
  } else {
    showField("waitLogin");
  }
}

/**
 * ============================================================================
 * Hides a wait spinner
 * 
 * @return void
 */
function hideWait() {
  waitingForReply=false;
  hideField("wait");
  hideField("waitLogin");
  if (window.top.dijit.byId("dialogInfo")) {
    window.top.dijit.byId("dialogInfo").hide();
  }
}

// =============================================================================
// = Generic field visibility properties
// =============================================================================

/**
 * ============================================================================
 * Setup the style properties of a field to set it visible (show it)
 * 
 * @param field
 *          the name of the field to be set
 * @return void
 */
function showField(field) {
  var dest=dojo.byId(field);
  if (dijit.byId(field)) {
    dest=dijit.byId(field).domNode;
  }
  if (dest) {
    dojo.style(dest, {
      visibility : 'visible'
    });
    dojo.style(dest, {
      display : 'inline'
    });
    // dest.style.visibility = 'visible';
    // dest.style.display = 'inline';
  }
}

/**
 * ============================================================================
 * Setup the style properties of a field to set it invisible (hide it)
 * 
 * @param field
 *          the name of the field to be set
 * @return void
 */
function hideField(field) {
  var dest=dojo.byId(field);
  if (dijit.byId(field)) {
    dest=dijit.byId(field).domNode;
  }
  if (dest) {
    dojo.style(dest, {
      visibility : 'hidden'
    });
    dojo.style(dest, {
      display : 'none'
    });
    // dest.style.visibility = 'hidden';
    // dest.style.display = 'none';
  }
}

function protectDblClick(widget){
  if (!widget.id) return;
  disableWidget(widget.id);
  setTimeout("enableWidget('"+widget.id+"');",300);
}
// =============================================================================
// = Message boxes
// =============================================================================

/**
 * ============================================================================
 * Display a Dialog Error Message Box
 * 
 * @param msg
 *          the message to display in the box
 * @return void
 */
function showError(msg) {
  window.top.hideWait();
  if (window.top.dojo.byId("dialogErrorMessage")) {
    window.top.dojo.byId("dialogErrorMessage").innerHTML=msg;
    window.top.dijit.byId("dialogError").show();
  } else if (dojo.byId('loginResultDiv')) {
    dojo.byId('loginResultDiv').innerHTML=
      '<input type="hidden" id="isLoginPage" name="isLoginPage" value="true" />'
      +'<div class="messageERROR" style="width:100%">'+msg+'</div>';
  } else {
    alert(msg);
  }
}

/**
 * ============================================================================
 * Display a Dialog Information Message Box
 * 
 * @param msg
 *          the message to display in the box
 * @return void
 */
function showInfo(msg,callback) {
  var callbackFunc=function() {};
  if (callback) { 
    callbackFunc=callback;
  }
  window.top.dojo.byId("dialogInfoMessage").innerHTML=msg;
  window.top.dijit.byId("dialogInfo").acceptCallback=callbackFunc;
  window.top.dijit.byId("dialogInfo").show();
}

/**
 * ============================================================================
 * Display a Dialog Alert Message Box
 * 
 * @param msg
 *          the message to display in the box
 * @return void
 */
function showAlert(msg,callback) {
  window.top.hideWait();
  var callbackFunc=function() {};
  if (callback) { 
    callbackFunc=callback;
  }
  window.top.dojo.byId("dialogAlertMessage").innerHTML=msg;
  window.top.dijit.byId("dialogAlert").acceptCallback=callbackFunc;
  window.top.dijit.byId("dialogAlert").show();
}

/**
 * ============================================================================
 * Display a Dialog Question Message Box, with Yes/No buttons
 * 
 * @param msg
 *          the message to display in the box
 * @param actionYes
 *          the function to be executed if click on Yes button
 * @param actionNo
 *          the function to be executed if click on No button
 * @return void
 */
function showQuestion(msg, actionYes, actionNo) {
  dojo.byId("dialogQuestionMessage").innerHTML=msg;
  dijit.byId("dialogQuestion").acceptCallbackYes=actionYes;
  dijit.byId("dialogQuestion").acceptCallbackNo=actionNo;
  dijit.byId("dialogQuestion").show();
}

/**
 * ============================================================================
 * Display a Dialog Confirmation Message Box, with OK/Cancel buttons NB : no
 * action on Cancel click
 * 
 * @param msg
 *          the message to display in the box
 * @param actionOK
 *          the function to be executed if click on OK button
 * @return void
 */
function showConfirm(msg, actionOK) {
  dojo.byId("dialogConfirmMessage").innerHTML=msg;
  dijit.byId("dialogConfirm").acceptCallback=actionOK;
  dijit.byId("dialogConfirm").show();
}

/**
 * ============================================================================
 * Display a About Box
 * 
 * @param msg
 *          the message of the about box (must be passed here because built in
 *          php)
 * @return void
 */
function showAbout(msg) {
  showInfo(msg);
}

function showMsg(id,value){
  if(dojo.byId("divMsgFull"+id).style.display=="none"){
    dojo.byId("divSubTitle"+id).style.display="none";
    if(value==0.25 || value==1.25 || value==2.25){
      if(dojo.byId("divMsgTitle"+(id+1))){
        dojo.byId("divMsgTitle"+(id+1)).style.display="none";
      }
      if(dojo.byId("divMsgTitle"+(id+2))){
        dojo.byId("divMsgTitle"+(id+2)).style.display="none";
      }
      if(dojo.byId("divMsgTitle"+(id+3))){
        dojo.byId("divMsgTitle"+(id+3)).style.display="none";
      }
    }
    if(value==0.5 || value==1.5 || value==2.5){
      if(dojo.byId("divMsgTitle"+(id+1))){
        dojo.byId("divMsgTitle"+(id+1)).style.display="none";
      }
      if(dojo.byId("divMsgTitle"+(id+2))){
        dojo.byId("divMsgTitle"+(id+2)).style.display="none";
      }
      if(dojo.byId("divMsgTitle"+(id-1))){
        dojo.byId("divMsgTitle"+(id-1)).style.display="none";
      }
    }
    if(value==0.75 || value==1.75 || value==2.75){
      if(dojo.byId("divMsgTitle"+(id+1))){
        dojo.byId("divMsgTitle"+(id+1)).style.display="none";
      }
      if(dojo.byId("divMsgTitle"+(id-1))){
        dojo.byId("divMsgTitle"+(id-1)).style.display="none";
      }
      if(dojo.byId("divMsgTitle"+(id-2))){
        dojo.byId("divMsgTitle"+(id-2)).style.display="none";
      }
    }
    if(value==1 || value==2 || value==3){
      if(dojo.byId("divMsgTitle"+(id-1))){
        dojo.byId("divMsgTitle"+(id-1)).style.display="none";
      }
      if(dojo.byId("divMsgTitle"+(id-2))){
        dojo.byId("divMsgTitle"+(id-2)).style.display="none";
      }
      if(dojo.byId("divMsgTitle"+(id-3))){
        dojo.byId("divMsgTitle"+(id-3)).style.display="none";
      }
    }
    
    dojo.byId("divMsgTitle"+id).style.height=50+'px';
    dojo.byId("divMsgTitle"+id).style.margin = 0+'px';
    dojo.byId("divMsgTitle"+id).style.width=340+'px';
    dojo.byId("divMsgTitle"+id).style.borderRadius = 5+'px '+5+'px '+0+'px '+0+'px';
    dojo.addClass(dojo.byId("divMsgTitle"+id),"colorMediumDiv");
    dojo.byId("divMsgFull"+id).style.display="block";
    dojo.byId("divMsgFull"+id).style.height=270+'px';
    dojo.byId("divMsgFull"+id).style.width=340+'px';
    dojo.byId("divMsgTitle"+id).style.fontSize=13+'px';
    
  }else{
    dojo.byId("divMsgFull"+id).style.display="none";
    dojo.byId("divMsgTitle"+id).style.height=155+'px';
    dojo.byId("divMsgTitle"+id).style.width=165+'px';
    dojo.byId("divMsgTitle"+id).style.borderRadius = 5+'px '+5+'px '+5+'px '+5+'px';
    dojo.byId("divMsgTitle"+id).style.flexDirection="column";
    dojo.byId("divMsgTitle"+id).style.justifyContent="center";
    dojo.byId("divMsgTitle"+id).style.fontSize=13+'px';
    dojo.byId("divMsgtextTitle"+id).style.padding = 15+'px';
    dojo.byId("arrowNewsDown"+id).style.display="block";
    if(id==1 || id==3 || id==5 || id==7 || id==9 || id==11){
      dojo.byId("divMsgTitle"+id).style.marginRight = 10+'px';
    }
    dojo.byId("divMsgTitle"+id).style.marginBottom = 10+'px';
    
    dojo.removeClass(dojo.byId("divMsgTitle"+id),"colorMediumDiv");
    if(value==0.25 || value==1.25 || value==2.25){
      if(dojo.byId("divMsgTitle"+(id+1))){
        dojo.byId("divMsgTitle"+(id+1)).style.display="flex";
      }
      if(dojo.byId("divMsgTitle"+(id+2))){
        dojo.byId("divMsgTitle"+(id+2)).style.display="flex";
      }
      if(dojo.byId("divMsgTitle"+(id+3))){
        dojo.byId("divMsgTitle"+(id+3)).style.display="flex";
      }
    }
    if(value==0.5 || value==1.5 || value==2.5){
      if(dojo.byId("divMsgTitle"+(id+1))){
        dojo.byId("divMsgTitle"+(id+1)).style.display="flex";
      }
      if(dojo.byId("divMsgTitle"+(id+2))){
        dojo.byId("divMsgTitle"+(id+2)).style.display="flex";
      }
      if(dojo.byId("divMsgTitle"+(id-1))){
        dojo.byId("divMsgTitle"+(id-1)).style.display="flex";
      }
    }
    if(value==0.75 || value==1.75 || value==2.75){
      if(dojo.byId("divMsgTitle"+(id+1))){
        dojo.byId("divMsgTitle"+(id+1)).style.display="flex";
      }
      if(dojo.byId("divMsgTitle"+(id-1))){
        dojo.byId("divMsgTitle"+(id-1)).style.display="flex";
      }
      if(dojo.byId("divMsgTitle"+(id-2))){
        dojo.byId("divMsgTitle"+(id-2)).style.display="flex";
      }
    }
    if(value==1 || value==2 || value==3){
      if(dojo.byId("divMsgTitle"+(id-1))){
        dojo.byId("divMsgTitle"+(id-1)).style.display="flex";
      }
      if(dojo.byId("divMsgTitle"+(id-2))){
        dojo.byId("divMsgTitle"+(id-2)).style.display="flex";
      }
      if(dojo.byId("divMsgTitle"+(id-3))){
        dojo.byId("divMsgTitle"+(id-3)).style.display="flex";
      }
    }
  }
}
function showIntrotext(id){
  if(dojo.byId("divMsgFull"+id).style.display=="none"){
    dojo.byId("divMsgTitle"+id).style.height=39+'px';
    dojo.byId("divMsgTitle"+id).style.width=165+'px';
    dojo.byId("divMsgTitle"+id).style.margin = 0+'px';
    dojo.byId("divMsgTitle"+id).style.borderRadius = 5+'px '+5+'px '+0+'px '+0+'px';
    dojo.addClass(dojo.byId("divMsgTitle"+id),"colorMediumDiv");
    dojo.byId("divSubTitle"+id).style.display="block";
    dojo.byId("divSubTitle"+id).style.height=116+'px';
    dojo.byId("divSubTitle"+id).style.fontSize=10+'px';
    dojo.byId("divMsgTitle"+id).style.fontSize=10+'px';
    dojo.byId("divMsgTitle"+id).style.textOverflow='ellipsis';
    dojo.byId("arrowNewsDown"+id).style.display="none";
    dojo.byId("divMsgtextTitle"+id).style.padding = 0+'px';
  }
}

function hideIntrotext(id){
  if(dojo.byId("divMsgFull"+id).style.display=="none"){
    dojo.byId("divSubTitle"+id).style.display="none";
    dojo.byId("divMsgTitle"+id).style.height=155+'px';
    dojo.byId("divMsgTitle"+id).style.width=165+'px';
    if(id==1 || id==3 || id==5 || id==7 || id==9 || id==11){
      dojo.byId("divMsgTitle"+id).style.marginRight = 10+'px';
    }
    dojo.byId("divMsgTitle"+id).style.marginBottom = 10+'px';
    dojo.byId("divMsgTitle"+id).style.borderRadius = 5+'px '+5+'px '+5+'px '+5+'px';
    dojo.byId("divMsgTitle"+id).style.flexDirection="column";
    dojo.byId("divMsgTitle"+id).style.justifyContent="center";
    dojo.byId("divMsgTitle"+id).style.display="flex";
    dojo.byId("divMsgTitle"+id).style.fontSize=13+'px';
    dojo.removeClass(dojo.byId("divMsgTitle"+id),"colorMediumDiv");
    dojo.byId("arrowNewsDown"+id).style.display="block";
    dojo.byId("divMsgtextTitle"+id).style.padding = 15+'px';
  }
}

function hideMsg(id,value){
  dojo.byId("divMsgFull"+id).style.display="none";
  dojo.byId("divMsgTitle"+id).style.height=155+'px';
  dojo.byId("divMsgTitle"+id).style.width=165+'px';
  if(id==1 || id==3 || id==5 || id==7 || id==9 || id==11){
    dojo.byId("divMsgTitle"+id).style.marginRight = 10+'px';
  }
  dojo.byId("arrowNewsDown"+id).style.display="block";
  dojo.removeClass(dojo.byId("divMsgTitle"+id),"colorMediumDiv");
  if(value==0.25 || value==1.25 || value==2.25){
    if(dojo.byId("divMsgTitle"+(id+1))){
      dojo.byId("divMsgTitle"+(id+1)).style.display="flex";
    }
    if(dojo.byId("divMsgTitle"+(id+2))){
      dojo.byId("divMsgTitle"+(id+2)).style.display="flex";
    }
    if(dojo.byId("divMsgTitle"+(id+3))){
      dojo.byId("divMsgTitle"+(id+3)).style.display="flex";
    }
  }
  if(value==0.5 || value==1.5 || value==2.5){
    if(dojo.byId("divMsgTitle"+(id+1))){
      dojo.byId("divMsgTitle"+(id+1)).style.display="flex";
    }
    if(dojo.byId("divMsgTitle"+(id+2))){
      dojo.byId("divMsgTitle"+(id+2)).style.display="flex";
    }
    if(dojo.byId("divMsgTitle"+(id-1))){
      dojo.byId("divMsgTitle"+(id-1)).style.display="flex";
    }
  }
  if(value==0.75 || value==1.75 || value==2.75){
    if(dojo.byId("divMsgTitle"+(id+1))){
      dojo.byId("divMsgTitle"+(id+1)).style.display="flex";
    }
    if(dojo.byId("divMsgTitle"+(id-2))){
      dojo.byId("divMsgTitle"+(id-2)).style.display="flex";
    }
    if(dojo.byId("divMsgTitle"+(id-1))){
      dojo.byId("divMsgTitle"+(id-1)).style.display="flex";
    }
  }
  if(value==1 || value==2 || value==3){
    if(dojo.byId("divMsgTitle"+(id-1))){
      dojo.byId("divMsgTitle"+(id-1)).style.display="block";
    }
    if(dojo.byId("divMsgTitle"+(id-2))){
      dojo.byId("divMsgTitle"+(id-2)).style.display="block";
    }
    if(dojo.byId("divMsgTitle"+(id-3))){
      dojo.byId("divMsgTitle"+(id-3)).style.display="block";
    }
  }
}
// =============================================================================
// = Print
// =============================================================================

/**
 * ============================================================================
 * Display a Dialog Print Preview Box
 * 
 * @param page
 *          the page to display
 * @param forms
 *          the form containing the data to send to the page
 * @return void
 */
function showPrint(page, context, comboName, outMode, orientation) {
  // dojo.byId('printFrame').style.width= 1000 + 'px';
  showWait();
  quitConfirmed=true;
  noDisconnect=true;
  if (!orientation)
    orientation='L';
  if (!outMode)
    outMode='html';
  var printInNewWin=printInNewWindow;
  if (outMode == "pdf") {
    printInNewWin=pdfInNewWindow;
  }
  if (outMode == "csv") {
    printInNewWin=true;
  }
  if (outMode == "mpp") {
    printInNewWin=true;
  }
  if (context=='favorite' || context=='admin' || context=='organization' || context=='asset') {
    printInNewWin=false;
  }
  if (outMode == "csv" || outMode == "word" || outMode == "excel" || outMode == "download" || context=="download" || context=="downloadList") {
    printInNewWin=true; // Will not show print frame
  }
  if (!printInNewWin) {
    dijit.byId("dialogPrint").show();
  }
  
  cl='';
  if ( (context == 'list' || context == 'downloadList') && dojo.byId('objectClassList')) {
    cl=dojo.byId('objectClassList').value;
  } else if (dojo.byId('objectClass')) {
    cl=dojo.byId('objectClass').value;
  }
  
  id='';
  if (dojo.byId('objectId')) {
    id=dojo.byId('objectId').value;
  }
  var params="&orientation=" + orientation;
  dojo.byId("sentToPrinterDiv").style.display='block';
  if (outMode) {
    params+="&outMode=" + outMode;
    if (outMode == 'pdf') {
      dojo.byId("sentToPrinterDiv").style.display='none';
    }
  }

// ADD BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
  // For Organization, add the period year as parameter
  if (cl=='Organization' && dijit.byId('OrganizationBudgetElementCurrent__byMet_periodYear')) {
    params+='&OrganizationBudgetPeriod='+dijit.byId('OrganizationBudgetElementCurrent__byMet_periodYear').get("value");
  }
// END ADD BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
  if (context == 'list' || context == 'downloadList' || context == 'download') {
    if (dijit.byId("listShowIdle")) {
      if (dijit.byId("listShowIdle").get('checked')) {
        params+="&idle=true";
      }
    }
    if (dijit.byId("listIdFilter")) {
      if (dijit.byId("listIdFilter").get('value')) {
        params+="&listIdFilter="
            + encodeURIComponent(dijit.byId("listIdFilter").get('value'));
      }
    }
    if (dijit.byId("listNameFilter")) {
      if (dijit.byId("listNameFilter").get('value')) {
        params+="&listNameFilter="
            + encodeURIComponent(dijit.byId("listNameFilter").get('value'));
      }
    }
    if (dijit.byId("listTypeFilter")) {
      if (trim(dijit.byId("listTypeFilter").get('value'))) {
        params+="&objectType="
            + encodeURIComponent(dijit.byId("listTypeFilter").get('value'));
      }
    }
    if (dijit.byId("listBudgetParentFilter")) {
      if (trim(dijit.byId("listBudgetParentFilter").get('value'))) {
        params+="&budgetParent="
            + encodeURIComponent(dijit.byId("listBudgetParentFilter").get('value'));
      }
    }
    if (dijit.byId("listClientFilter")) {
      if (trim(dijit.byId("listClientFilter").get('value'))) {
        params+="&objectClient="
            + encodeURIComponent(dijit.byId("listClientFilter").get('value'));
      }
    }
    if (dijit.byId("listElementableFilter")) {
      if (trim(dijit.byId("listElementableFilter").get('value'))) {
        params+="&objectElementable="
            + encodeURIComponent(dijit.byId("listElementableFilter").get('value'));
      }
    }
  } else if (context == 'planning') {
    if (dijit.byId("startDatePlanView").get('value')) {
      params+="&startDate="
          + encodeURIComponent(formatDate(dijit.byId("startDatePlanView").get(
              "value")));
      params+="&endDate="
          + encodeURIComponent(formatDate(dijit.byId("endDatePlanView").get(
              "value")));
      params+="&format=" + g.getFormat();
      if(dijit.byId('listShowIdle')!=null){
        if (dijit.byId('listShowIdle').get('checked')) {
          params+="&idle=true";
        }
      }
      if(dijit.byId('showWBS')!=null){
        if (dijit.byId('showWBS').checked) {
          params+="&showWBS=true";
        }
      }
      if (dijit.byId('listShowResource')) {
        if (dijit.byId('listShowResource').checked) {
          params+="&showResource=true";
        }
      }
      if (dijit.byId('listShowLeftWork')) {
        if (dijit.byId('listShowLeftWork').checked) {
          params+="&showWork=true";
        }
      }
      if (dijit.byId('listShowProject')) {
        if (dijit.byId('listShowProject').checked) {
          params+="&showProject=true";
        }
      }
    }
  } else if (context == 'report' || context=='favorite') {
    if (context == 'report' ) { 
      var frm=dojo.byId('reportForm'); 
    } else {
      var frm=dojo.byId('favoriteForm'); 
    }
    frm.action="../view/print.php";
    if (outMode) {
      frm.page.value=page;
      dojo.byId('outMode').value=outMode;
    } else {
      dojo.byId('outMode').value='';
    }
    if (printInNewWin) {
      frm.target='#';
    } else {
      frm.target='printFrame';
    }
    frm.submit();
    hideWait();
    quitConfirmed=false;
    noDisconnect=false;
    return;
  } else if (context == 'imputation' || context == 'hierarchicalBudget') {
    var frm=dojo.byId('listForm');
    frm.action="../view/print.php?orientation=" + orientation;
    if (printInNewWin) {
      frm.target='#';
    } else {
      frm.target='printFrame';
    }
    if (outMode) {
      dojo.byId('outMode').value=outMode;
    } else {
      dojo.byId('outMode').value='';
    }
    frm.submit();
    hideWait();
    quitConfirmed=false;
    noDisconnect=false;
    return;
  }
  var grid=dijit.byId('objectGrid');
  if (grid) {
    var sortWay=(grid.getSortAsc()) ? 'asc' : 'desc';
    var sortIndex=grid.getSortIndex();
    if (sortIndex >= 0) {
      params+="&sortIndex=" + sortIndex;
      params+="&sortWay=" + sortWay;
    }
  }
  if (outMode=="download" && context=='template') {
    dojo.byId("printFrame").src="print.php?print=true&page=" + page;
    hideWait();
  } else if (outMode == "csv" || outMode == "word" || outMode == "excel" || outMode == "download" || context=="download" || context == 'downloadList') {
    dojo.byId("printFrame").src="print.php?print=true&page=" + page
        + "&context="+context
        + "&objectClass=" + cl + "&objectId=" + id + params;
    hideWait();
  } else if (printInNewWin) {
    var newWin=window.open("print.php?print=true&page=" + page
        + "&context="+context
        + "&objectClass=" + cl + "&objectId=" + id + params);
    hideWait();
  } else {
    dojo.byId("printFrame").src="print.php?print=true&page=" + page
        + "&context="+context
        + "&objectClass=" + cl + "&objectId=" + id + params;
    if (outMode == 'pdf') {
      //hideWait();
    } 
  }
  quitConfirmed=false;
  noDisconnect=false;
}

function sendFrameToPrinter() {
  dojo.byId("sendToPrinter").blur();
  window.frames['printFrame'].focus();
  window.frames['printFrame'].print();
  dijit.byId('dialogPrint').hide();
  return true;
}
// =============================================================================
// = Detail (from combo)
// =============================================================================

function showDetailDependency() {
  var depType=dijit.byId('dependencyRefTypeDep').get("value");
  if (depType) {
    var dependable=dependableArray[depType];
    var canCreate=0;
    if (canCreateArray[dependable] == "YES") {
      canCreate=1;
    }
    showDetail('dependencyRefIdDep', canCreate, dependable, true);

  } else {
    showInfo(i18n('messageMandatory', new Array(i18n('linkType'))));
  }
}

function showDetailLink() {
  var linkType=dijit.byId('linkRef2Type').get("value");
  if (linkType) {
    var linkable=linkableArray[linkType];
    var canCreate=0;
    if (canCreateArray[linkable] == "YES") {
      canCreate=1;
    }
    showDetail('linkRef2Id', canCreate, linkable, true);

  } else {
    showInfo(i18n('messageMandatory', new Array(i18n('linkType'))));
  }
}

function showDetailApprover() {
  var canCreate=0;
  if (canCreateArray['Resource'] == "YES") {
    canCreate=1;
  }
  showDetail('approverId', canCreate, 'Resource', true);
}

function showDetailOrigin() {
  var originType=dijit.byId('originOriginType').get("value");
  if (originType) {
    var originable=originableArray[originType];
    var canCreate=0;
    if (canCreateArray[originable] == "YES") {
      canCreate=1;
    }
    showDetail('originOriginId', canCreate, originable);

  } else {
    showInfo(i18n('messageMandatory', new Array(i18n('originType'))));
  }
}

function showDetail(comboName, canCreate, objectClass, multiSelect, objectId, forceSearch) {
  var contentWidget=dijit.byId("comboDetailResult");
  
  dojo.byId("canCreateDetail").value=canCreate;
  if (contentWidget) {
    contentWidget.set('content', '');
  }
  if (!objectClass) {
    objectClass=comboName.substring(2);
  }
  dojo.byId('comboName').value=comboName;
  dojo.byId('comboClass').value=objectClass;
  dojo.byId('comboMultipleSelect').value=(multiSelect) ? 'true' : 'false';
  dijit.byId('comboDetailResult').set('content',null);
  var val=null;
  if (dijit.byId(comboName)) {
    val=dijit.byId(comboName).get('value');
  } else if(dojo.byId(comboName)) {
    val=dojo.byId(comboName).value;
  }
  if (forceSearch) val=null; // will force search
  if (objectId) {
    if (objectId=='new') {
      cl=objectClass;
      id=null;
      window.frames['comboDetailFrame'].document.body.innerHTML='<i>'
          + i18n("messagePreview") + '</i>';
      dijit.byId("dialogDetail").show();
      //frames['comboDetailFrame'].location.href="print.php?print=true&page=preparePreview.php";
      newDetailItem(objectClass);
    } else {
      cl=objectClass;
      id=objectId;
      window.frames['comboDetailFrame'].document.body.innerHTML='<i>'
          + i18n("messagePreview") + '</i>';
      dijit.byId("dialogDetail").show();
      //frames['comboDetailFrame'].location.href="print.php?print=true&page=preparePreview.php";
      gotoDetailItem(objectClass,objectId);
    }
    
  } else if (!val || val == "" || val == " " || val == "*") {
    cl=objectClass;
    window.frames['comboDetailFrame'].document.body.innerHTML='<i>'
        + i18n("messagePreview") + '</i>';
    dijit.byId("dialogDetail").show();
    displaySearch(cl);
  } else {
    cl=objectClass;
    id=val;
    window.frames['comboDetailFrame'].document.body.innerHTML='<i>'
        + i18n("messagePreview") + '</i>';
    dijit.byId("dialogDetail").show();
    displayDetail(cl, id);
  }
  dojo.connect(dijit.byId("dialogDetail"),"onhide", 
    function(){
      // nothing to do;
    });
}

function displayDetail(objClass, objId) {
  showWait();
  showField('comboSearchButton');
  hideField('comboSelectButton');
  hideField('comboNewButton');
  hideField('comboSaveButton');
  showField('comboCloseButton');
  dijit.byId('comboDetailResult').set('content',null);
  frames['comboDetailFrame'].location.href="print.php?print=true&page=objectDetail.php&objectClass="
      + objClass + "&objectId=" + objId + "&detail=true";
}

function directDisplayDetail(objClass, objId) {
  showWait();
  hideField('comboSearchButton');
  hideField('comboSelectButton');
  hideField('comboNewButton');
  hideField('comboSaveButton');
  showField('comboCloseButton');
  dijit.byId('comboDetailResult').set('content',null);
  window.frames['comboDetailFrame'].document.body.innerHTML='<i>'
    + i18n("messagePreview") + '</i>';
  dijit.byId("dialogDetail").show();
  frames['comboDetailFrame'].location.href="print.php?print=true&page=objectDetail.php&objectClass="
    + objClass + "&objectId=" + objId + "&detail=true";
}

function selectDetailItem(selectedValue, lastSavedName) {
  var idFldVal="";
  if (selectedValue) {
    idFldVal=selectedValue;
  } else {
    var idFld=frames['comboDetailFrame'].dojo.byId('comboDetailId');
    var comboGrid=frames['comboDetailFrame'].dijit.byId('objectGrid');
    if (comboGrid) {
      idFldVal="";
      var items=comboGrid.selection.getSelected();
      dojo.forEach(items, function(selectedItem) {
        if (selectedItem !== null) {
          idFldVal+=(idFldVal != "") ? '_' : '';
          idFldVal+=parseInt(selectedItem.id, 10) + '';
        }
      });
    } else {
      if (!idFld) {
        showError('error : comboDetailId not defined');
        return;
      }
      idFldVal=idFld.value;
    }
    if (!idFldVal) {
      showAlert(i18n('noItemSelected'));
      return;
    }
  }
  var comboName=dojo.byId('comboName').value;
  var combo=dijit.byId(comboName);
  var comboClass=dojo.byId('comboClass').value;
  crit=null;
  critVal=null;
  if (comboClass == 'Activity' || comboClass == 'Resource'
      || comboClass == 'Ticket') {
    if (comboName.substr(0,15)=='filterValueList') {
      // Do not set current project (would be project of selected item), will apply restriction to selected project
    } else {
      prj=dijit.byId('idProject');
      if (prj) {
        crit='idProject';
        critVal=prj.get("value");
      }
    }  
  }
  if (comboName != 'idStatus'  && comboName != 'versionsPlanningDetail' && comboName != 'projectSelectorFiletering') { 
    if (combo) {
      refreshList('id' + comboClass, crit, critVal, idFldVal, comboName);
    } else {
      if (comboName == 'dependencyRefIdDep') {
        refreshDependencyList(idFldVal);
        setTimeout("dojo.byId('dependencyRefIdDep').focus()", 1000);
        enableWidget('dialogDependencySubmit');
      } else if (comboName == 'linkRef2Id') {
        refreshLinkList(idFldVal);
        setTimeout("dojo.byId('linkRef2Id').focus()", 1000);
        enableWidget('dialogLinkSubmit');
      } else if (comboName == 'productStructureListId') {
        refreshProductStructureList(idFldVal,lastSavedName);
        setTimeout("dojo.byId('productStructureListId').focus()",500);
        enableWidget('dialogProductStructureSubmit');
      //ADD aGaye - Ticket 179
      } else if (comboName == 'versionCompatibilityListId'){
    	  refreshVersionCompatibilityList(idFldVal,lastSavedName);
    	  setTimeout("dojo.byId('versionCompatibilityListId').focus()",500);
          enableWidget('dialogVersionCompatibilitySubmit');
      //END aGaye - Ticket 179
      } else if (comboName == 'productVersionStructureListId') {
    	  refreshProductVersionStructureList(idFldVal,lastSavedName);
        setTimeout("dojo.byId('productVersionStructureListId').focus()",500);
        enableWidget('dialogProductVersionStructureSubmit');
      } else if (comboName == 'otherVersionIdVersion') {
        refreshOtherVersionList(idFldVal);
        setTimeout("dojo.byId('otherVersionIdVersion').focus()", 1000);
        enableWidget('dialogOtherVersionSubmit');
      } else if (comboName == 'otherClientIdClient') {
        refreshOtherClientList(idFldVal);
        setTimeout("dojo.byId('otherClientIdClient').focus()", 1000);
        enableWidget('dialogOtherClientSubmit');
      } else if (comboName == 'approverId') {
        refreshApproverList(idFldVal);
        setTimeout("dojo.byId('approverId').focus()", 1000);
        enableWidget('dialogApproverSubmit');
      } else if (comboName == 'originOriginId') {
        refreshOriginList(idFldVal);
        setTimeout("dojo.byId('originOriginId').focus()", 1000);
        enableWidget('dialogOriginSubmit');
      } else if (comboName == 'testCaseRunTestCaseList') {
        refreshTestCaseRunList(idFldVal);
        setTimeout("dojo.byId('testCaseRunTestCaseList').focus()", 1000);
        enableWidget('dialogTestCaseRunSubmit');
// ADD BY Marc TABARY - 2017-02-23 - ADD OBJECTS LINKED BY ID TO MAIN OBJECT
      } else if (comboName == 'linkedObjectId') {
        refreshLinkObjectList(idFldVal);
        setTimeout("dojo.byId('linkedObjectId').focus()", 1000);
        enableWidget('dialogObjectSubmit');
// END ADD BY Marc TABARY - 2017-02-23 - ADD OBJECTS LINKED BY ID TO MAIN OBJECT
      } else if (comboName == 'linkProviderTerm') {
        refreshLinkProviderTerm(idFldVal);
      }    
    }
  }
  
  //ADD qCazelles - Correction GANTT - Ticket #100
  if (comboName == 'versionsPlanningDetail') {
	  displayVersionsPlanning(idFldVal,'ProductVersion');
	  hideDetail();
	  return;
  }else if(comboName == 'versionsComponentPlanningDetail'){
    displayVersionsPlanning(idFldVal,'ComponentVersion');
    hideDetail();
    return;                                                    
  }  
  //END ADD qCazelles - Correction GANTT - Ticket #100
  if(comboClass=='Contact' && (dojo.byId('objectClass').value=='Client' || dojo.byId('objectClass').value=='Provider') ){
    saveContact(idFldVal,comboClass,comboName);
    hideDetail();
    return;
  }
  
  if (combo) {
  	if(comboName == 'projectSelectorFiletering'){
  		var pos = idFldVal.indexOf('_');
  		if(pos != -1){
  			dijit.byId('multiProjectSelector').set("value", idFldVal);
  		}else{
  			combo.set("value", idFldVal);
  		}
  	}else if(comboName.substr(0,15) == 'filterValueList'){
  		//var pos = idFldVal.indexOf('_');
  		//if(pos != -1){
  		//	dijit.byId('filterValueList').set("value", idFldVal);
  		//}else{
  		idFldVal=idFldVal.split('_');
  		combo.set("value", idFldVal);
  		//}
    }else{
  	  combo.set("value", idFldVal);
  	}
  }
  hideDetail();
  if (dojo.byId('directAccessToList') && dojo.byId('directAccessToList').value=='true' && dojo.byId('directAccessToListButton')) {
    var idButton = dojo.byId('directAccessToListButton').value;
    setTimeout("dijit.byId('" + idButton + "').onClick();", 20);
  }
}

function displaySearch(objClass) {
  if (!objClass) {
    // comboName=dojo.byId('comboName').value;
    objClass=dojo.byId('comboClass').value;
  }
  showWait();
  hideField('comboSearchButton');
  showField('comboSelectButton');
  if (dojo.byId("canCreateDetail").value=="1" && objClass!='Project' && objClass!='Status' ) {
    showField('comboNewButton');
  } else {
    hideField('comboNewButton');
  }
  hideField('comboSaveButton');
  showField('comboCloseButton');
  var multipleSelect=(dojo.byId('comboMultipleSelect').value == 'true') ? '&multipleSelect=true'
      : '';
  var currentProject=(top.dijit.byId('idProject'))?'&currentSelectedProject='+top.dijit.byId('idProject').get("value"):'';
  if (top.dojo.byId('objectClass') && top.dojo.byId('objectClass')=='Project' && top.dojo.byId('id')) currentProject='&currentSelectedProject='+top.dojo.byId('id').value;
  window.top.frames['comboDetailFrame'].location.href="comboSearch.php?objectClass="
      + objClass + "&mode=search" + multipleSelect+currentProject;
  setTimeout('dijit.byId("dialogDetail").show()', 10);
}

function newDetailItem(objectClass) {
  gotoDetailItem(objectClass);
}
function gotoDetailItem(objectClass,objectId) {
  // comboName=dojo.byId('comboName').value;
  dijit.byId("dialogDetail").show();
  hideField('comboSearchButton');
  var objClass=objectClass;
  if (!objectClass) {
    objClass=dojo.byId('comboClass').value;
    showField('comboSearchButton');
  }
  showWait();
  hideField('comboSelectButton');
  hideField('comboNewButton');
  if (dojo.byId("canCreateDetail").value == "1") {
    showField('comboSaveButton');
  } else {
    hideField('comboSaveButton');
  }
  showField('comboCloseButton');
  //contentNode=frames['comboDetailFrame'].dojo.byId('body');
  //destinationWidth=dojo.style(contentNode, "width");
  destinationWidth=frames['comboDetailFrame'].document.body.offsetWidth
  page="comboSearch.php";
  page+="?objectClass=" + objClass;
  if (objectId) {
    page+="&objectId="+objectId;
    page+="&mode=new";    
  } else {
    page+="&objectId=0";
    page+="&mode=new";
    if(dijit.byId('idClient')){
    	if(trim(dijit.byId('idClient').get('value')) != ''){
    		page+="&idClient="+dijit.byId('idClient').get('value');
    	}
    }
  }
  page+="&destinationWidth=" + destinationWidth;
  window.top.frames['comboDetailFrame'].location.href=page;
  setTimeout('dijit.byId("dialogDetail").show()', 10);   
}

function saveDetailItem() {
  var comboName=dojo.byId('comboName').value;
  var formVar=frames['comboDetailFrame'].dijit.byId("objectForm");
  if (!formVar) {
    showError(i18n("errorSubmitForm", new Array(page, destination, formName)));
    return;
  }
  for(name in frames['comboDetailFrame'].CKEDITOR.instances) {
    frames['comboDetailFrame'].CKEDITOR.instances[name].updateElement();
  }
  // validate form Data
  if (formVar.validate()) {
    showWait();
    frames['comboDetailFrame'].dojo
        .xhrPost({
          url : "../tool/saveObject.php?comboDetail=true",
          form : "objectForm",
          handleAs : "text",
          load : function(data, args) {
            var contentWidget=dijit.byId("comboDetailResult");
            if (!contentWidget) {
              return;
            }
            contentWidget.set('content', data);
            checkDestination("comboDetailResult");
            var lastOperationStatus=window.top.dojo
                .byId('lastOperationStatusComboDetail');
            var lastOperation=window.top.dojo.byId('lastOperationComboDetail');
            var lastSaveId=window.top.dojo.byId('lastSaveIdComboDetail');
            if (lastOperationStatus.value == "OK") {
              var currentItemName="";
              if (frames['comboDetailFrame'].dijit.byId("name")) {
                currentItemName=frames['comboDetailFrame'].dijit.byId("name").get("value");
              }
              selectDetailItem(lastSaveId.value,currentItemName);
            }
            hideWait();
          },
          error : function() {
            hideWait();
          }
        });

  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function hideDetail() {
  hideField('comboSearchButton');
  hideField('comboSelectButton');
  hideField('comboNewButton');
  hideField('comboSaveButton');
  hideField('comboCloseButton');
  frames['comboDetailFrame'].location.href="preparePreview.php";
  dijit.byId("dialogDetail").hide();
  if (dijit.byId(dojo.byId('comboName').value)) {
    dijit.byId(dojo.byId('comboName').value).focus();
  }
}

//=============================================================================
//= Copy Object
//=============================================================================

/**
 * Display a copy object Box
 * 
 */
function copyObjectBox(copyType) {
  var callBack=function() {

  };
  //gautier #2522
  if (copyType=="copyDocument") {
    callBack=function() {
    };
    var params="&objectClass="+dojo.byId('objectClass').value;
    params+="&objectId="+dojo.byId("objectId").value;   
    params+="&copyType="+copyType;  
    loadDialog('dialogCopyDocument', callBack, true, params, false);
  }else{
  if (copyType=="copyVersion") {
    callBack=function() {
    };
  } else if(copyType=="copyObjectTo"){
    callBack=function() {
      dojo.byId('copyClass').value=dojo.byId('objectClass').value;
      dojo.byId('copyId').value=dojo.byId("objectId").value;
      copyObjectToShowStructure();
    };
  }else if(copyType=="copyProject"){
    callBack=function() {
      dojo.byId('copyProjectId').value=dojo.byId("objectId").value;
      dijit.byId('copyProjectToName').set('value', dijit.byId('name').get('value'));
      dijit.byId('copyProjectToType').reset();
      if (dijit.byId('idProjectType') && dojo.byId('codeType')
          && dojo.byId('codeType').value != 'TMP') {
        var runModif="dijit.byId('copyProjectToType').set('value',dijit.byId('idProjectType').get('value'))";
        setTimeout(runModif, 1);
      }
    };
  }
  var params="&objectClass="+dojo.byId('objectClass').value;
  params+="&objectId="+dojo.byId("objectId").value;   
  params+="&copyType="+copyType;   
  loadDialog('dialogCopy', callBack, true, params, false);
  }
}

//=============================================================================
//= Planning PDF
//=============================================================================

/**
* Display a planning PDF Box
* 
*/
function planningPDFBox(copyType) { 
  loadDialog('dialogPlanningPdf', null, true, "", false);
}

// =============================================================================
// = Notes
// =============================================================================

/**
 * Display a add note Box
 * 
 */
// DOJO HACK
// Hack to be able to interact with ck_editor popups in Notes
// NEEDS TO CHANGE dijit/Dialog.js, in focus.watch
// Replace
//   if(node == topDialog.domNode || domClass.contains(node, "dijitPopup")){ return; }
// With
//   if(node == topDialog.domNode || domClass.contains(node, "dijitPopup") || domClass.contains(node, "cke_dialog_body")){ return; }
// And then rebuild dojo
// 7.3.0 Not usefull anymore with dojo 1.14 and 2 lines below
function pauseBodyFocus() { dojo.query(".cke_dialog_body").addClass("dijitPopup");}      
function resumeBodyFocus() { dojo.query(".cke_dialog_body").removeClass("dijitPopup");}   
function addNote(reply, idParentNote) {
  if (dijit.byId("noteToolTip")) {
    dijit.byId("noteToolTip").destroy();
    dijit.byId("noteNote").set("class", "");
  }
  pauseBodyFocus();
  var callBack=function() {
    var editorType=dojo.byId("noteEditorType").value;
    if (editorType=="CK" || editorType=="CKInline") { // CKeditor type
      ckEditorReplaceEditor("noteNote",999);
    } else if (editorType=="text") {
      dijit.byId("noteNote").focus();
      dojo.byId("noteNote").style.height=(screen.height*0.6)+'px';
      dojo.byId("noteNote").style.width=(screen.width*0.6)+'px';
    } else if (dijit.byId("noteNoteEditor")) { // Dojo type editor
      dijit.byId("noteNoteEditor").set("class", "input");
      dijit.byId("noteNoteEditor").focus();
      dijit.byId("noteNoteEditor").set("height", (screen.height*0.6)+'px'); // Works on first time
      dojo.byId("noteNoteEditor_iframe").style.height=(screen.height*0.6)+'px'; // Works after first time
    }
  };
  var params="&objectClass="+dojo.byId('objectClass').value;
  params+="&objectId="+dojo.byId("objectId").value;
  params+="&noteId="; // Null
  params+="&reply="+reply;
  if(reply){
	  params+="&idParentNote="+idParentNote;
  }
  loadDialog('dialogNote', callBack, true, params, true);
}

function noteSelectPredefinedText(idPrefefinedText) {
  dojo.xhrGet({
    url : '../tool/getPredefinedText.php?id=' + idPrefefinedText,
    handleAs : "text",
    load : function(data) {
      var editorType=dojo.byId("noteEditorType").value;
      if (editorType=="CK" || editorType=="CKInline") { // CKeditor type
        CKEDITOR.instances['noteNote'].setData(data);
      } else if (editorType=="text") { 
        dijit.byId('noteNote').set('value', data);
        dijit.byId('noteNote').focus();
      } else if (dijit.byId('noteNoteEditor')) {
        dijit.byId('noteNote').set('value', data);
        dijit.byId('noteNoteEditor').set('value', data);
        dijit.byId("noteNoteEditor").focus();
      } 
    }
  });
}
/**
 * Display a edit note Box
 * 
 */
function editNote(noteId, privacy) {
  if (dijit.byId("noteToolTip")) {
    dijit.byId("noteToolTip").destroy();
    dijit.byId("noteNote").set("class", "");
  }
  pauseBodyFocus();
  var callBack=function() {
    //dijit.byId('notePrivacyPublic').set('checked', 'true');
    var editorType=dojo.byId("noteEditorType").value;
    if (editorType=="CK" || editorType=="CKInline") { // CKeditor type
      ckEditorReplaceEditor("noteNote",999);
    } else if (editorType=="text") { 
      dijit.byId("noteNote").focus();
      dojo.byId("noteNote").style.height=(screen.height*0.6)+'px';
      dojo.byId("noteNote").style.width=(screen.width*0.6)+'px';
    } else if (dijit.byId("noteNoteEditor")) { // Dojo type editor
      dijit.byId("noteNoteEditor").set("class", "input");
      dijit.byId("noteNoteEditor").focus();
      dijit.byId("noteNoteEditor").set("height", (screen.height*0.6)+'px'); // Works on first time
      dojo.byId("noteNoteEditor_iframe").style.height=(screen.height*0.6)+'px'; // Works after first time
    } 
  };
  var params="&objectClass="+dojo.byId('objectClass').value;
  params+="&objectId="+dojo.byId("objectId").value;
  params+="&noteId="+noteId;
  loadDialog('dialogNote', callBack, true, params, true);
}

/**
 * save a note (after addNote or editNote)
 * 
 */
function saveNote() {
  var editorType=dojo.byId("noteEditorType").value;
  if (editorType=="CK" || editorType=="CKInline") {
    noteEditor=CKEDITOR.instances['noteNote'];
    noteEditor.updateElement();
    var tmpCkEditor=noteEditor.document.getBody().getText();
    var tmpCkEditorData=noteEditor.getData();
    if (tmpCkEditor.trim()=="" && tmpCkEditorData.indexOf('<img')<=0) {
      var msg=i18n('messageMandatory', new Array(i18n('Note')));
      noteEditor.focus();
      showAlert(msg);
      return;
    }
  } else if (dijit.byId("noteNoteEditor")) {
    if (dijit.byId("noteNote").getValue() == '') {
      dijit.byId("noteNoteEditor").set("class", "input required");
      var msg=i18n('messageMandatory', new Array(i18n('Note')));
      dijit.byId("noteNoteEditor").focus();
      dojo.byId("noteNoteEditor").focus();
      showAlert(msg);
      return;
    }
  } 
  loadContent("../tool/saveNote.php", "resultDivMain", "noteForm", true, 'note');
  loadContentStream();
  dijit.byId('dialogNote').hide();
}


/**
 * Display a delete note Box
 * 
 */
function removeNote(noteId) {
  var param="?noteId="+noteId;
  var dest="resultDivMain";
  if (dojo.byId('objectClass') && dojo.byId("objectId")) {
    param+="&noteRefType="+dojo.byId('objectClass').value;
    param+="&noteRefId="+dojo.byId("objectId").value;
  } else if (dojo.byId('noteRefType') && dojo.byId('noteRefId')) {
    param+="&noteRefType="+dojo.byId('noteRefType').value;
    param+="&noteRefId="+dojo.byId('noteRefId').value;
    //dest="resultKanbanStreamDiv";
  }
  actionOK=function() {
    loadContent("../tool/removeNote.php"+param, dest, "noteForm", true, 'note');
  };
  msg=i18n('confirmDelete', new Array(i18n('Note'), noteId));
  showConfirm(msg, actionOK);
}

//=============================================================================
//= Situation
//=============================================================================

function addSituation() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
   }
  if (dijit.byId("situationToolTip")) {
    dijit.byId("situationToolTip").destroy();
    dijit.byId("situationComment").set("class", "");
  }
  pauseBodyFocus();
  var callBack=function() {
    var editorType=dojo.byId("situationEditorType").value;
    if (editorType=="CK" || editorType=="CKInline") { // CKeditor type
      ckEditorReplaceEditor("situationComment",995);
    } else if (editorType=="text") {
      dijit.byId("situationComment").focus();
      dojo.byId("situationComment").style.height=(screen.height*0.6)+'px';
      dojo.byId("situationComment").style.width=(screen.width*0.6)+'px';
    } else if (dijit.byId("situationEditor")) { // Dojo type editor
      dijit.byId("situationEditor").set("class", "input");
      dijit.byId("situationEditor").focus();
      dijit.byId("situationEditor").set("height", (screen.height*0.6)+'px'); // Works on first time
      dojo.byId("situationEditor_iframe").style.height=(screen.height*0.6)+'px'; // Works after first time
    }
  };
  var params="&objectClass="+dojo.byId('objectClass').value;
  params+="&objectId="+dojo.byId("objectId").value;
  loadDialog('dialogSituation', callBack, true, params, true);
}

function editSituation(situationId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  if (dijit.byId("situationToolTip")) {
    dijit.byId("situationToolTip").destroy();
    dijit.byId("situationComment").set("class", "");
  }
  pauseBodyFocus();
  var callBack=function() {
	    var editorType=dojo.byId("situationEditorType").value;
	    if (editorType=="CK" || editorType=="CKInline") { // CKeditor type
	      ckEditorReplaceEditor("situationComment",995);
	    } else if (editorType=="text") {
	      dijit.byId("situationComment").focus();
	      dojo.byId("situationComment").style.height=(screen.height*0.6)+'px';
	      dojo.byId("situationComment").style.width=(screen.width*0.6)+'px';
	    } else if (dijit.byId("situationEditor")) { // Dojo type editor
	      dijit.byId("situationEditor").set("class", "input");
	      dijit.byId("situationEditor").focus();
	      dijit.byId("situationEditor").set("height", (screen.height*0.6)+'px'); // Works on first time
	      dojo.byId("situationEditor_iframe").style.height=(screen.height*0.6)+'px'; // Works after first time
	    }
  };
  var params="&objectClass="+dojo.byId('objectClass').value;
  params+="&objectId="+dojo.byId("objectId").value;
  params+="&situationId="+situationId;
  loadDialog('dialogSituation', callBack, true, params, true);
}

function saveSituation() {
  var formVar=dijit.byId('situationForm');
  if (formVar.validate()) {
	  var editorType=dojo.byId("situationEditorType").value;
	  if (editorType=="CK" || editorType=="CKInline") {
		situationEditor=CKEDITOR.instances['situationComment'];
		situationEditor.updateElement();
	    var tmpCkEditor=situationEditor.document.getBody().getText();
	    var tmpCkEditorData=situationEditor.getData();
	  }
	  loadContent("../tool/saveSituation.php", "resultDivMain", "situationForm", true, 'situation');
	  dijit.byId('dialogSituation').hide();
  }else{
	  showAlert(i18n("alertInvalidForm"));
	  return;
  }
}
/**
 * Display a delete situation Box
 * 
 */
function removeSituation(situationId) {
  var param="?situationId="+situationId;
  param+="&situationRefType="+dojo.byId('objectClass').value;
  param+="&situationRefId="+dojo.byId("objectId").value;
  param+="&action=remove";
  actionOK=function() {
    loadContent("../tool/saveSituation.php"+param, "resultDivMain", "situationForm", true, 'situation');
  };
  msg=i18n('confirmDelete', new Array(i18n('Situation'), situationId));
  showConfirm(msg, actionOK);
}

function situationSelectPredefinedText(idPrefefinedText) {
	dojo.xhrPost({
	    url : '../tool/getPredefinedSituation.php?id=' + idPrefefinedText,
		handleAs : "text",
		load : function(data,args) {
			if (data) {
		        var ps = JSON.parse(data);
		        dijit.byId('situationSituation').set('value', ps.situation);
		        var editorType=dojo.byId("situationEditorType").value;
		        if (editorType=="CK" || editorType=="CKInline") { // CKeditor type
		          CKEDITOR.instances['situationComment'].setData(ps.comment);
		        } else if (editorType=="text") { 
		          dijit.byId('situationComment').set('value', ps.comment);
		          dijit.byId('situationComment').focus();
		        } else if (dijit.byId('situationCommentEditor')) {
		          dijit.byId('situationComment').set('value', ps.comment);
		          dijit.byId('situationCommentEditor').set('value', ps.comment);
		          dijit.byId("situationCommentEditor").focus();
		        }
			}
	    }
	  });
}

// =============================================================================
// = Attachments
// =============================================================================

/**
 * Display an add attachment Box
 * 
 */
function addAttachment(attachmentType,refType,refId) {
  var content="";
  if (dijit.byId('dialogAttachment')) content=dijit.byId('dialogAttachment').get('content');
  if (content == "") {
    callBack=function() {
      dojo.connect(dijit.byId("attachmentFile"), "onComplete", function(dataArray) {
        saveAttachmentAck(dataArray);
      });
      dojo.connect(dijit.byId("attachmentFile"), "onProgress", function(data) {
        saveAttachmentProgress(data);
      });
      dojo.connect(dijit.byId("attachmentFile"), "onError", function(evt) {
        hideWait();
        showError(i18n("uploadUncomplete"));
      });
      addAttachment(attachmentType,refType,refId);
      if (isHtml5() && dijit.byId('attachmentFileDirect')) {
        dijit.byId('attachmentFileDirect').reset();
        dijit.byId('attachmentFileDirect').addDropTarget(dojo.byId('attachmentFileDropArea'));
      }
    };
    loadDialog('dialogAttachment', callBack);
    return;
  }
  dojo.byId("attachmentId").value="";
  dojo.byId("attachmentRefType").value=(refType)?refType:((dojo.byId('objectClass'))?dojo.byId('objectClass').value:'User');
  dojo.byId("attachmentRefId").value=(refId)?refId:((dojo.byId('objectId'))?dojo.byId("objectId").value:dojo.byId("userMenuIdUser").value);
  dojo.byId("attachmentType").value=attachmentType;
  dojo.byId('attachmentFileName').innerHTML="";
  dojo.style(dojo.byId('downloadProgress'), {
    display : 'none'
  });
  if (attachmentType == 'file') {
    if (dijit.byId("attachmentFile")) {
      dijit.byId("attachmentFile").reset();
      if (!isHtml5()) {
        enableWidget('dialogAttachmentSubmit');
      } else {
        disableWidget('dialogAttachmentSubmit');
      }
    }
    dojo.style(dojo.byId('dialogAttachmentFileDiv'), {
      display : 'block'
    });
    dojo.style(dojo.byId('dialogAttachmentLinkDiv'), {
      display : 'none'
    });
  } else {
    dijit.byId("attachmentLink").set('value', null);
    dojo.style(dojo.byId('dialogAttachmentFileDiv'), {
      display : 'none'
    });
    dojo.style(dojo.byId('dialogAttachmentLinkDiv'), {
      display : 'block'
    });
    enableWidget('dialogAttachmentSubmit');
  }
  dijit.byId("attachmentDescription").set('value', null);
  dijit.byId("dialogAttachment").set('title', i18n("dialogAttachment"));
  dijit.byId('attachmentPrivacyPublic').set('checked', 'true');
  dijit.byId("dialogAttachment").show();
}

function changeAttachment(list) {
  if (list.length > 0) {
    htmlList="";
    for (var i=0; i < list.length; i++) {
      htmlList+=list[i]['name'] + '<br/>';
    }
    dojo.byId('attachmentFileName').innerHTML=htmlList;
    enableWidget('dialogAttachmentSubmit');
    dojo.byId('attachmentFile').height="200px";
  } else {
    dojo.byId('attachmentFileName').innerHTML="";
    disableWidget('dialogAttachmentSubmit');
    dojo.byId('attachmentFile').height="20px";
  }
}

/**
 * save an Attachment
 * 
 */
var cancelDupplicate=false;
function saveAttachment(direct) {
  // disableWidget('dialogAttachmentSubmit');
  if (!isHtml5()) {
    if (dojo.isIE && dojo.isIE<=8) {
      dojo.byId('attachmentForm').submit();
    }
    showWait();
    dijit.byId('dialogAttachment').hide();
    return true;
  }
  if (dojo.byId("attachmentType")
      && dojo.byId("attachmentType").value == 'file'
      && dojo.byId('attachmentFileName')
      && dojo.byId('attachmentFileName').innerHTML == "") {
    return false;
  }
  if (direct) {
    if (dijit.byId("attachmentFileDirect")) {
      if (dijit.byId("attachmentFileDirect").getFileList().length > 20) {
        showAlert(i18n('uploadLimitNumberFiles'));
        return false;
      }
    }
  } else {
    if (dijit.byId("attachmentFile")) {
      if (dijit.byId("attachmentFile").getFileList().length > 20) {
        showAlert(i18n('uploadLimitNumberFiles'));
        return false;
      }
    }
  }
  dojo.style(dojo.byId('downloadProgress'), {
    display : 'block'
  });
  showWait();
  dijit.byId('dialogAttachment').hide();
  return true;
}

/**
 * Acknowledge the attachment save
 * 
 * @return void
 */
function saveAttachmentAck(dataArray) {
  if (dataArray==undefined) {
    dojo.style(dojo.byId('downloadProgress'), {display : 'none'});
    dojo.byId('resultAck').value=i18n("uploadUncomplete");  
    hideWait();
    return;
  }
  if (!isHtml5()) {
    resultFrame=document.getElementById("resultPost");
    resultText=resultPost.document.body.innerHTML;
    dojo.byId('resultAck').value=resultText;
    loadContent("../tool/ack.php", "resultDivMain", "attachmentAckForm", true,
        'attachment');
    return;
  }
  dijit.byId('dialogAttachment').hide();
  if (dojo.isArray(dataArray)) {
    result=dataArray[0];
  } else {
    result=dataArray;
  }
  dojo.style(dojo.byId('downloadProgress'), {display : 'none'});
  dojo.byId('resultAck').value=result.message;
  loadContent("../tool/ack.php", "resultDivMain", "attachmentAckForm", true,
      'attachment');
//gautier #menuUserTop
  loadContent("../view/menuUserTop.php", "drawMenuUser");
}

function saveAttachmentProgress(data) {
  done=data.bytesLoaded;
  total=data.bytesTotal;
  if (total) {
    progress=done / total;
  }
  // dojo.style(dojo.byId('downloadProgress'), {display:'block'});
  dijit.byId('downloadProgress').set('value', progress);
}
/**
 * Display a delete Attachment Box
 * 
 */
function removeAttachment(attachmentId) {
  var content="";
  if (dijit.byId('dialogAttachment')) content=dijit.byId('dialogAttachment').get('content');
  if (content == "") {
    callBack=function() {
      dojo.connect(dijit.byId("attachmentFile"), "onComplete", function(
          dataArray) {
        saveAttachmentAck(dataArray);
      });
      dojo.connect(dijit.byId("attachmentFile"), "onProgress", function(data) {
        saveAttachmentProgress(data);
      });
      dijit.byId('dialogAttachment').hide();
      removeAttachment(attachmentId);
    };
    loadDialog('dialogAttachment', callBack);
    return;
  }
  dojo.byId("attachmentId").value=attachmentId;
  dojo.byId("attachmentRefType").value=dojo.byId('objectClass').value;
  dojo.byId("attachmentRefId").value=dojo.byId("objectId").value;
  actionOK=function() {
    loadContent("../tool/removeAttachment.php", "resultDivMain", "attachmentForm",
        true, 'attachment');
    loadContent("../view/menuUserTop.php", "drawMenuUser");
    //loadContent("../view/menuBar.php", "iconMenuUserPhoto");
  };
  msg=i18n('confirmDelete', new Array(i18n('Attachment'), attachmentId));
  showConfirm(msg, actionOK);
}

// =============================================================================
// = Links
// =============================================================================

/**
 * Display a add link Box
 * 
 */
var noRefreshLink=false;
function addLink(classLink, defaultLink) {
  if (dojo.byId('objectClass') && dojo.byId('objectClass').value=='Requirement' && classLink=='TestCase' &&checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  if (!classLink) {
    var params="&objectClass="+dojo.byId('objectClass').value+"&objectId="+dojo.byId("objectId").value;
  }
  loadDialog('dialogLink',function(){
    noRefreshLink=true;
    var objectClass=dojo.byId('objectClass').value;
    var objectId=dojo.byId("objectId").value;
    var message=i18n("dialogLink");
    dojo.byId("linkId").value="";
    dojo.byId("linkRef1Type").value=objectClass;
    dojo.byId("linkRef1Id").value=objectId;
    dojo.style(dojo.byId('linkDocumentVersionDiv'), {
      display : 'none'
    });
    dijit.byId("linkDocumentVersion").reset();
    if (classLink) {
      dojo.byId("linkFixedClass").value=classLink;
      message=i18n("dialogLinkRestricted", new Array(i18n(objectClass), objectId,
          i18n(classLink)));
      dijit.byId("linkRef2Type").setDisplayedValue(i18n(classLink));
      lockWidget("linkRef2Type");
      // var url="../tool/dynamicListLink.php"
      // + "?linkRef2Type="+dojo.byId("linkRef2Type").value
      // + "&linkRef1Type="+objectClass
      // + "&linkRef1Id="+objectId;
      // loadContent(url, "dialogLinkList", null, false);
      noRefreshLink=false;
      refreshLinkList();
    } else {
      dojo.byId("linkFixedClass").value="";
      if (defaultLink) {
        dijit.byId("linkRef2Type").set('value', defaultLink);
      } else {
        dijit.byId("linkRef2Type").reset();
      }
      message=i18n("dialogLinkExtended", new Array(i18n(objectClass), objectId));
      unlockWidget("linkRef2Type");
      noRefreshLink=false;
      refreshLinkList();
    }
  
    // dojo.byId("linkRef2Id").value='';
    dijit.byId("dialogLink").set('title', message);
    dijit.byId("linkComment").set('value', '');
    dijit.byId("dialogLink").show();
    disableWidget('dialogLinkSubmit');
  }, true, params, true);
}

function selectLinkItem() {
  var nbSelected=0;
  list=dojo.byId('linkRef2Id');
  if (dojo.byId("linkRef2Type").value == "Document") {
    if (list.options) {
      selected=new Array();
      for (var i=0; i < list.options.length; i++) {
        if (list.options[i].selected) {
          selected.push(list.options[i].value);
          nbSelected++;
        }
      }
      if (selected.length == 1) {
        dijit.byId("linkDocumentVersion").reset();
        refreshList('idDocumentVersion', 'idDocument', selected[0], null,'linkDocumentVersion', false);
        dojo.style(dojo.byId('linkDocumentVersionDiv'), {
          display : 'block'
        });
      } else {
        dojo.style(dojo.byId('linkDocumentVersionDiv'), {
          display : 'none'
        });
        dijit.byId("linkDocumentVersion").reset();
      }
    }
  } else {
    if (list.options) {
      for (var i=0; i < list.options.length; i++) {
        if (list.options[i].selected) {
          nbSelected++;
        }
      }
    }
    dojo.style(dojo.byId('linkDocumentVersionDiv'), {
      display : 'none'
    });
    dijit.byId("linkDocumentVersion").reset();
  }
  if (nbSelected > 0) {
    enableWidget('dialogLinkSubmit');
  } else {
    disableWidget('dialogLinkSubmit');
  }
}

/**
 * Refresh the link list (after update)
 */
function refreshLinkList(selected) {
  if (noRefreshLink)
    return;
  disableWidget('dialogLinkSubmit');
  var url='../tool/dynamicListLink.php';
  if (selected) {
    url+='?selected=' + selected;
  }
  if (!selected) {
    selectLinkItem();
  }
  loadContent(url, 'dialogLinkList', 'linkForm', false);
}

/**
 * save a link (after addLink)
 * 
 */
function saveLink() {
  if (dojo.byId("linkRef2Id").value == "")
    return;
  var fixedClass = (dojo.byId('linkFixedClass'))?dojo.byId('linkFixedClass').value:'';
  loadContent("../tool/saveLink.php", "resultDivMain", "linkForm", true, 'link'+fixedClass);
  dijit.byId('dialogLink').hide();
}

/**
 * Display a delete Link Box
 * 
 */
function removeLink(linkId, refType, refId, refTypeName, fixedClass) {
  if (dojo.byId('objectClass') && dojo.byId('objectClass').value=='Requirement' && fixedClass=='TestCase' && checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    if(fixedClass && fixedClass==refType){
      loadContent("../tool/removeLink.php?linkId="+linkId+"&linkRef1Type="+dojo.byId('objectClass').value
          +"&linkRef1Id="+dojo.byId("objectId").value+"&linkRef2Type="+refType
          +"&linkRef2Id="+refId, "resultDivMain", null, true, 'link'+fixedClass);
    } else {
      loadContent("../tool/removeLink.php?linkId="+linkId+"&linkRef1Type="+dojo.byId('objectClass').value
          +"&linkRef1Id="+dojo.byId("objectId").value+"&linkRef2Type="+refType
          +"&linkRef2Id="+refId, "resultDivMain", null, true, 'link');
    }
  };
  if (!refTypeName) {
    refTypeName=i18n(refType);
  }
  msg=i18n('confirmDeleteLink', new Array(refTypeName, refId));
  showConfirm(msg, actionOK);
}

//=============================================================================
//= Product Composition
//=============================================================================

/**
* Display a add link Box
* 
*/
function addProductStructure(way) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  var param="&objectClass="+objectClass+"&objectId="+objectId+"&way="+way;
  var callBackFunc=function() {
    if (dojo.byId('directAccessToList') && dojo.byId('directAccessToList').value=='true') {
      var canCreate=0;
      if (dojo.byId('productStructureCanCreateComponent')) {
        canCreate=dojo.byId('productStructureCanCreateComponent').value;
      }
      showDetail('productStructureListId', canCreate, 'Component', true); 
    } else {
      dijit.byId('dialogProductStructure').show();
    }
  }
  dojo.xhrGet({
   	url : "../tool/filterComponentType.php?" + param,
	});
  loadDialog('dialogProductStructure',callBackFunc, false, param, true);
}

function editProductStructure(way,productStructureId) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  
  var param="&objectClass="+objectClass+"&objectId="+objectId+"&way="+way+"&structureId="+productStructureId;
  loadDialog('dialogProductStructure',null, true, param, true);
}

function refreshProductStructureList(selected,newName) {
  var selectList=dojo.byId('productStructureListId');
  if (selected && selectList) {
    if (newName) {
      var option = document.createElement("option");
      option.text = newName;
      option.value=selected;
      selectList.add(option);
    }
    var ids=selected.split('_');
    for (j=0;j<selectList.options.length;j++) {
      var sel=selectList.options[j].value;
      if (ids.indexOf(sel)>=0) { // Found in selected items
        selectList.options[j].selected='selected';
      }
    }
    selectList.focus()
    enableWidget('dialogProductStructureSubmit');
  }
}
/**
* save a link (after addLink)
* 
*/
function saveProductStructure() {
  if (dojo.byId("productStructureListId").value == "") return;
  loadContent("../tool/saveProductStructure.php", "resultDivMain", "productStructureForm", true, 'ProductStructure');
  dijit.byId('dialogProductStructure').hide();
}

/**
* Display a delete Link Box
* 
*/
function removeProductStructure(ProductStructureId, refType, refId, refTypeName) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  actionOK=function() {
   loadContent("../tool/removeProductStructure.php?id="+ProductStructureId, "resultDivMain", null, true, 'ProductStructure');
  };
  if (!refTypeName) {
   refTypeName=i18n(refType);
  }
  msg=i18n('confirmDeleteLink', new Array(refTypeName, refId));
  showConfirm(msg, actionOK);
}

//ADD by qCazelles - Business features
//=============================================================================
//= BusinessFeatures
//=============================================================================
function addBusinessFeature() {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId('objectClass').value;
	var objectId=dojo.byId("objectId").value;
	var param="&objectClass="+objectClass+"&objectId="+objectId;
	loadDialog('dialogBusinessFeature', null, true, param, false);
}

//ADD qCazelles - Business Feature (Correction)
function editBusinessFeature(businessFeatureId) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId('objectClass').value;
	var objectId=dojo.byId("objectId").value;
	var param="&objectClass="+objectClass+"&objectId="+objectId+"&businessFeatureId="+businessFeatureId;
	loadDialog('dialogBusinessFeature', null, true, param, false);
}
//END ADD qCazelles - Business Feature (Correction)

function saveBusinessFeature() {
	if (dojo.byId("businessFeatureName").value == "") return;
	loadContent("../tool/saveBusinessFeature.php", "resultDivMain", "businessFeatureForm", true, 'BusinessFeature');
	dijit.byId('dialogBusinessFeature').hide();	
}

function removeBusinessFeature(businessFeatureId, refType) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	actionOK=function() {
		loadContent("../tool/removeBusinessFeature.php?businessFeatureId="+businessFeatureId, "resultDivMain", null, true, 'BusinessFeature');
	};
	msg=i18n('confirmDeleteBusinessFeature', new Array(refType, businessFeatureId));
	showConfirm(msg, actionOK);
}
//END ADD qCazelles

//ADD qCazelles - Lang-Context
//=============================================================================
//= Product/Component Language/Context 
//=============================================================================

function addProductLanguage() {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId('objectClass').value;
	var objectId=dojo.byId("objectId").value;
	var param="&objectClass="+objectClass+"&objectId="+objectId;
	loadDialog('dialogProductLanguage', null, true, param, false);
}

function saveProductLanguage() {
	loadContent("../tool/saveProductLanguage.php", "resultDivMain", "productLanguageForm", true, 'ProductLanguage');
	dijit.byId('dialogProductLanguage').hide();	
}

function editProductLanguage(productLanguageId) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId('objectClass').value;
	var objectId=dojo.byId("objectId").value;
	
	var param="&objectClass="+objectClass+"&objectId="+objectId+"&languageId="+productLanguageId;
	loadDialog('dialogProductLanguage',null,true,param,true);
}

function removeProductLanguage(productLanguageId, refType) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	actionOK=function() {
		loadContent("../tool/removeProductLanguage.php?refType="+refType+"&productLanguageId="+productLanguageId, "resultDivMain", null, true, 'ProductLanguage');
	};
	msg=i18n('confirmDeleteProductLanguage');
	showConfirm(msg, actionOK);
}

function addProductContext() {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId('objectClass').value;
	var objectId=dojo.byId("objectId").value;
	var param="&objectClass="+objectClass+"&objectId="+objectId;
	loadDialog('dialogProductContext', null, true, param, false);
}

function saveProductContext() {
	loadContent("../tool/saveProductContext.php", "resultDivMain", "productContextForm", true, 'ProductContext');
	dijit.byId('dialogProductContext').hide();	
}

function editProductContext(productContextId) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId('objectClass').value;
	var objectId=dojo.byId("objectId").value;
	
	var param="&objectClass="+objectClass+"&objectId="+objectId+"&contextId="+productContextId;
	loadDialog('dialogProductContext',null,true,param,true);
}

function removeProductContext(productContextId, refType) {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	actionOK=function() {
		loadContent("../tool/removeProductContext.php?refType="+refType+"&productContextId="+productContextId, "resultDivMain", null, true, 'ProductContext');
	};
	msg=i18n('confirmDeleteProductContext');
	showConfirm(msg, actionOK);
}
//END ADD qCazelles - Lang-Context
//ADD qCazelles - Version compatibility
//=============================================================================
//= Product Version Compatibility
//=============================================================================
function addVersionCompatibility() {
	if (checkFormChangeInProgress()) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId('objectClass').value;
	var objectId=dojo.byId("objectId").value;
	var param="&objectClass="+objectClass+"&objectId="+objectId;
	var callBackFunc=function() {
		 if (dojo.byId('directAccessToList') && dojo.byId('directAccessToList').value=='true') {
			  showDetail('versionCompatibilityListId', 0, 'ProductVersion', true);
			  dijit.byId('dialogDetail').on('hide', function(evt) {
				  dojo.xhrGet({
					  url : "../tool/removeHiddenFilterDetail.php?objectClass=ProductVersion"
				  });
				  dijit.byId('dialogDetail').on('hide', null);
				  dijit.byId('dialogVersionCompatibility').hide();
			  });
		  }else {
			 	dijit.byId('dialogVersionCompatibility').show();
		 }
	};
	loadDialog('dialogVersionCompatibility', callBackFunc, false, param, true);
}

//ADD aGaye - Ticket 179
function refreshVersionCompatibilityList(selected,newName) {
	  var selectList=dojo.byId('versionCompatibilityListId');
	  if (selected && selectList) {
	    if (newName) {
	      var option = document.createElement("option");
	      option.text = newName;
	      option.value=selected;
	      selectList.add(option);
	    }
	    var ids=selected.split('_');
	    for (j=0;j<selectList.options.length;j++) {
	      var sel=selectList.options[j].value;
	      if (ids.indexOf(sel)>=0) { // Found in selected items
	        selectList.options[j].selected='selected';
	      }
	    }
	    selectList.focus();
	    enableWidget('dialogVersionCompatibilitySubmit');
	  }
}
//END aGaye - Ticket 179

function saveVersionCompatibility() {
if (dojo.byId('versionCompatibilityListId').value=='') return;
loadContent("../tool/saveVersionCompatibility.php", "resultDivMain", "versionCompatibilityForm", true, 'VersionCompatibility');
dijit.byId('dialogVersionCompatibility').hide();
}

function removeVersionCompatibility(versionCompatibilityId, refType, refId, refTypeName) {
if (checkFormChangeInProgress()) {
	showAlert(i18n('alertOngoingChange'));
	return;
}
actionOK=function() {
	loadContent("../tool/removeVersionCompatibility.php?versionCompatibilityId="+versionCompatibilityId, "resultDivMain", null, true, 'VersionCompatibility');
};
if (!refTypeName) {
	refTypeName=i18n(refType);
}
msg=i18n('confirmDeleteVersionCompatibility', new Array(refType, versionCompatibilityId));
showConfirm(msg, actionOK);
}
//END ADD qCazelles - Version compatibility

//Gautier #4404
function addAssetComposition(id) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    dijit.byId("dialogAssetComposition").show();
  };
  var params="&idParent="+id;
  params+="&mode=add";  
  loadDialog('dialogAssetComposition',callBack,false,params);
}

function saveAssetComposition() {
  if (dojo.byId("idParent").value == "") return;
  loadContent("../tool/saveAssetComposition.php", "resultDivMain", "assetCompositionForm", true, 'AssetComposition');
  dijit.byId('dialogAssetComposition').hide();
}

function removeAssetComposition(assetId) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  actionOK=function() {
   loadContent("../tool/removeAssetComposition.php?id="+assetId, "resultDivMain", null, true, 'AssetComposition');
  };
  refTypeName=i18n('Asset');
  msg=i18n('confirmDeleteLink', new Array(refTypeName, assetId));
  showConfirm(msg, actionOK);
}
//=============================================================================
//= Product Version Composition
//=============================================================================

/**
* Display a add link Box
* 
*/
function addProductVersionStructure(way) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  var param="&objectClass="+objectClass+"&objectId="+objectId+"&way="+way;
  //CHANGE qCazelles - Ticket 165
  //Old
  //loadDialog('dialogProductVersionStructure', null, false, param, true);
  //New
  var callBackFunc=function() {
	  if (dojo.byId('directAccessToList') && dojo.byId('directAccessToList').value=='true') {
		  showDetail('productVersionStructureListId', 0, 'ComponentVersion', true);
		  handle=dijit.byId('dialogDetail').on('hide', function(evt) {
			  dojo.xhrGet({
				  url : "../tool/removeHiddenFilterDetail.php?objectClass=ComponentVersion"
			  });
			  //dijit.byId('dialogDetail').on('hide', null);
			  handle.remove();
		  });
	  } else {
		  dijit.byId('dialogProductVersionStructure').show();
	  }
  }
  loadDialog('dialogProductVersionStructure', callBackFunc, false, param, true);
  //END CHANGE qCazelles - Ticket 165
}

function editProductVersionStructureAsset(productVersionStructureId) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  var param="&objectClass="+objectClass+"&objectId="+objectId+"&structureId="+productVersionStructureId;
  loadDialog('dialogProductVersionStructure',null, true, param, true);
}

function editProductVersionStructure(way, productVersionStructureId) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  var param="&objectClass="+objectClass+"&objectId="+objectId+"&way="+way+"&structureId="+productVersionStructureId;
  loadDialog('dialogProductVersionStructure',null, true, param, true);
}

var upgradeProductVersionStructureId=null;
function upgradeProductVersionStructure(structureId,withoutConfirm) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  upgradeProductVersionStructureId=structureId;
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  var params="&objectClass="+objectClass+"&objectId="+objectId;
  if (structureId) params+="&structureId="+structureId;
  if (withoutConfirm) {
    loadContent("../tool/upgradeProductVersionStructure.php?confirm=true"+params, "resultDivMain", null, true, 'ProductVersionStructure');
  } else {
    dojo.xhrGet({
      url : "../tool/upgradeProductVersionStructure.php?confirm=false"+params,
      handleAs : "text",
      load : function(data) {
        actionOK=function() {
          var objectClass=dojo.byId('objectClass').value;
          var objectId=dojo.byId("objectId").value;
          var params="&objectClass="+objectClass+"&objectId="+objectId;
          if (upgradeProductVersionStructureId) params+="&structureId="+upgradeProductVersionStructureId;
          loadContent("../tool/upgradeProductVersionStructure.php?confirm=true"+params, "resultDivMain", null, true, 'ProductVersionStructure');
        };
        showConfirm(data, actionOK);
      }
    });
  }
}

function refreshProductVersionStructureList(selected,newName) {
  var selectList=dojo.byId('productVersionStructureListId');
  if (selected && selectList) {
    if (newName) {
      var option = document.createElement("option");
      option.text = newName;
      option.value=selected;
      selectList.add(option);
    }
    var ids=selected.split('_');
    for (var j=0;j<selectList.options.length;j++) {
      var sel=selectList.options[j].value;
      if (ids.indexOf(sel)>=0) { // Found in selected items
        selectList.options[j].selected='selected';
      }
    }
    selectList.focus();
    enableWidget('dialogProductVersionStructureSubmit');
  }
}
/**
* save a link (after addLink)
* 
*/
function saveProductVersionStructure() {
  if (dojo.byId("productVersionStructureListId").value == "") return;
  loadContent("../tool/saveProductVersionStructure.php", "resultDivMain", "productVersionStructureForm", true, 'ProductVersionStructure');
  dijit.byId('dialogProductVersionStructure').hide();
}

function saveProductVersionStructureAsset(){
  if (dojo.byId("productVersionStructureListId").value == "") return;
  loadContent("../tool/saveProductAsset.php", "resultDivMain", "productVersionStructureForm", true, 'ProductVersionStructure');
  dijit.byId('dialogProductVersionStructure').hide();
}

/**
* Display a delete Link Box
* 
*/
function removeProductVersionStructure(ProductVersionStructureId, refType, refId, refTypeName) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  actionOK=function() {
   loadContent("../tool/removeProductVersionStructure.php?id="+ProductVersionStructureId, "resultDivMain", null, true, 'ProductVersionStructure');
  };
  if (!refTypeName) {
   refTypeName=i18n(refType);
  }
  msg=i18n('confirmDeleteLink', new Array(refTypeName, refId));
  showConfirm(msg, actionOK);
}

function removeProductVersionStructureAsset(id,refType, refId,refTypeName) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  actionOK=function() {
   loadContent("../tool/removeProductVersionStructureAsset.php?id="+id, "resultDivMain", null, true, 'ProductVersionStructure');
  };
  if (!refTypeName) {
    refTypeName=i18n(refType);
   }
  msg=i18n('confirmDeleteLink', new Array(refTypeName, refId));
  showConfirm(msg, actionOK);
}

function changeValueSecurityConstraint(value) {
  dojo.byId("securityConstraint").value=value;
}

// =============================================================================
// = OtherVersions
// =============================================================================
function addOtherVersion(versionType) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  dojo.byId("otherVersionRefType").value=objectClass;
  dojo.byId("otherVersionRefId").value=objectId;
  dojo.byId("otherVersionType").value=versionType;
  refreshOtherVersionList(null);
  dijit.byId("dialogOtherVersion").show();
  disableWidget('dialogOtherVersionSubmit');
}

/**
 * Refresh the link list (after update)
 */
function refreshOtherVersionList(selected) {
  disableWidget('dialogOtherVersionSubmit');
  var url='../tool/dynamicListOtherVersion.php';
  if (selected) {
    url+='?selected=' + selected;
  }
  if (!selected) {
    selectOtherVersionItem();
  }
  loadContent(url, 'dialogOtherVersionList', 'otherVersionForm', false);
}

function selectOtherVersionItem() {
  var nbSelected=0;
  list=dojo.byId('otherVersionIdVersion');
  if (list.options) {
    for (var i=0; i < list.options.length; i++) {
      if (list.options[i].selected) {
        nbSelected++;
      }
    }
  }
  if (nbSelected > 0) {
    enableWidget('dialogOtherVersionSubmit');
  } else {
    disableWidget('dialogOtherVersionSubmit');
  }
}

function saveOtherVersion() {
  if (dojo.byId("otherVersionIdVersion").value == "")
    return;
  loadContent("../tool/saveOtherVersion.php", "resultDivMain", "otherVersionForm",
      true, 'otherVersion');
  dijit.byId('dialogOtherVersion').hide();
}

function removeOtherVersion(id, name, type) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dojo.byId("otherVersionId").value=id;
  actionOK=function() {
    loadContent("../tool/removeOtherVersion.php", "resultDivMain",
        "otherVersionForm", true, 'otherVersion');
  };
  msg=i18n('confirmDeleteOtherVersion', new Array(name, i18n('colId' + type)));
  showConfirm(msg, actionOK);
}

function swicthOtherVersionToMain(id, name, type) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dojo.byId("otherVersionId").value=id;
  // actionOK=function() {loadContent("../tool/switchOtherVersion.php",
  // "resultDivMain", "otherVersionForm", true,'otherVersion');};
  // msg=i18n('confirmSwitchOtherVersion',new Array(name, i18n('col'+type)));
  // showConfirm (msg, actionOK);
  loadContent("../tool/switchOtherVersion.php", "resultDivMain",
      "otherVersionForm", true, 'otherVersion');
}

function showDetailOtherVersion() {
  var canCreate=0;
  if (canCreateArray['Version'] == "YES") {
    canCreate=1;
  }
  var versionType='Version';
  if (dojo.byId("otherVersionType")) {
    var typeValue=dojo.byId("otherVersionType").value;
    if (typeValue.substr(-16)=='ComponentVersion') versionType='ComponentVersion';
    else if (typeValue.substr(-14)=='ProductVersion') versionType='ProductVersion';
  }
  showDetail('otherVersionIdVersion', canCreate, versionType, true);
}

//=============================================================================
//= OtherClients
//=============================================================================
var handle=null;
function addOtherClient() {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  dojo.byId("otherClientRefType").value=objectClass;
  dojo.byId("otherClientRefId").value=objectId;
  if (1) { // direct Access To List : always on
    dojo.byId('otherClientIdClient').value=null;
    showDetail('otherClientIdClient', 0, 'Client', true);
    handle=dijit.byId('dialogDetail').on('hide', function(evt) {
      saveOtherClient();
      handle.remove();
    });
  } else { // No real use, kept in case direct access to list will become parametered
    refreshOtherClientList(null);
    dijit.byId("dialogOtherClient").show();
    disableWidget('dialogOtherClientSubmit');
  }
}

/**
* Refresh the link list (after update)
*/
function refreshOtherClientList(selected) {
  disableWidget('dialogOtherClientSubmit');
  var url='../tool/dynamicListOtherClient.php';
  if (selected) {
   url+='?selected=' + selected;
  }
  if (!selected) {
   selectOtherClientItem();
  }
  loadContent(url, 'dialogOtherClientList', 'otherClientForm', false);
}

function selectOtherClientItem() {
  var nbSelected=0;
  list=dojo.byId('otherClientIdClient');
  if (list.options) {
   for (var i=0; i < list.options.length; i++) {
     if (list.options[i].selected) {
       nbSelected++;
     }
   }
  }
  if (nbSelected > 0) {
   enableWidget('dialogOtherClientSubmit');
  } else {
   disableWidget('dialogOtherClientSubmit');
  }
}

function saveOtherClient() {
  if (dojo.byId("otherClientIdClient").value == "")
   return;
  loadContent("../tool/saveOtherClient.php", "resultDivMain", "otherClientForm",
     true, 'otherClient');
  dijit.byId('dialogOtherClient').hide();
}

function removeOtherClient(id, name, type) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  dojo.byId("otherClientId").value=id;
  actionOK=function() {
   loadContent("../tool/removeOtherClient.php", "resultDivMain",
       "otherClientForm", true, 'otherClient');
  };
  msg=i18n('confirmDeleteOtherClient', new Array(name, i18n('colId' + type)));
  showConfirm(msg, actionOK);
}

function swicthOtherClientToMain(id, name, type) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  dojo.byId("otherClientId").value=id;
  loadContent("../tool/switchOtherClient.php", "resultDivMain",
     "otherClientForm", true, 'otherClient');
}

function showDetailOtherClient() {
  var canCreate=0;
  if (canCreateArray['Client'] == "YES") {
   canCreate=1;
  }
  showDetail('otherVersionIdVersion', canCreate, 'Client', true);
}
// =============================================================================
// = Approvers
// =============================================================================

/**
 * Display a add link Box
 * 
 */
function addApprover() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  dojo.byId("approverRefType").value=objectClass;
  dojo.byId("approverRefId").value=objectId;
  refreshApproverList();
  dijit.byId("dialogApprover").show();
  disableWidget('dialogApproverSubmit');
}

function selectApproverItem() {
  var nbSelected=0;
  list=dojo.byId('approverId');
  if (list.options) {
    for (var i=0; i < list.options.length; i++) {
      if (list.options[i].selected) {
        nbSelected++;
      }
    }
  }
  if (nbSelected > 0) {
    enableWidget('dialogApproverSubmit');
  } else {
    disableWidget('dialogApproverSubmit');
  }
}

/**
 * Refresh the Approver list (after update)
 */
function refreshApproverList(selected) {
  disableWidget('dialogApproverSubmit');
  var url='../tool/dynamicListApprover.php';
  if (selected) {
    url+='?selected=' + selected;
  }
  selectApproverItem();
  loadContent(url, 'dialogApproverList', 'approverForm', false);
}

/**
 * save a link (after addLink)
 * 
 */
function saveApprover() {
  if (dojo.byId("approverId").value == "")
    return;
  loadContent("../tool/saveApprover.php", "resultDivMain", "approverForm", true,
      'approver');
  dijit.byId('dialogApprover').hide();
}

/**
 * Display a delete Link Box
 * 
 */
function removeFollowup(followupId,all){
  var param="?messageFollowup="+followupId;
  param+="&deleteAll="+all;
  
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeMessageFollowup.php"+param, "resultDivMain", "objectForm", true, 'MessageLegalFollowup');
  };

  msg=i18n('confirmRemoveMessageFollowup');
  showConfirm(msg, actionOK);
}

function removeApprover(approverId, approverName) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dojo.byId("approverItemId").value=approverId;
  dojo.byId("approverRefType").value=dojo.byId('objectClass').value;
  dojo.byId("approverRefId").value=dojo.byId("objectId").value;
  actionOK=function() {
    loadContent("../tool/removeApprover.php", "resultDivMain", "approverForm",
        true, 'approver');
  };
  msg=i18n('confirmDeleteApprover', new Array(approverName));
  showConfirm(msg, actionOK);
}

function approveItem(approverId, action) {
  var form = null;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  if(action=='disapproved'){
	  form='confirmDisapproveForm';
  }
  loadContent("../tool/approveItem.php?approverId="+approverId+"&action="+action, "resultDivMain",form, true, 'approver');
}

function disapproveItem(approverId){
	var params = "&approverId="+approverId;
	loadDialog('dialogConfirmDisapprove',null, true, params, false);
}

function enableConfirmDisapproveSubmit(value){
	var value = dijit.byId('disapproveDescription').get('value');
	if(value != ""){
		enableWidget('dialogConfirmDisapproveSubmit');
	}else{
		disableWidget('dialogConfirmDisapproveSubmit');
	}
}

// =============================================================================
// = Origin
// =============================================================================

/**
 * Display a add origin Box
 * 
 */
function addOrigin() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  dijit.byId("originOriginType").reset();
  refreshOriginList();
  dojo.byId("originId").value="";
  dojo.byId("originRefType").value=objectClass;
  dojo.byId("originRefId").value=objectId;
  dijit.byId("dialogOrigin").show();
  disableWidget('dialogOriginSubmit');
}

/**
 * Refresh the origin list (after update)
 */
function refreshOriginList(selected) {
  disableWidget('dialogOriginSubmit');
  var url='../tool/dynamicListOrigin.php';
  if (selected) {
    url+='?selected=' + selected;
  }
  loadContent(url, 'dialogOriginList', 'originForm', false);
}

/**
 * save a link (after addLink)
 * 
 */
function saveOrigin() {
  if (dojo.byId("originOriginId").value == "")
    return;
  loadContent("../tool/saveOrigin.php", "resultDivMain", "originForm", true,
      'origin');
  dijit.byId('dialogOrigin').hide();
}

/**
 * Display a delete Link Box
 * 
 */
function removeOrigin(id, origType, origId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dojo.byId("originId").value=id;
  dojo.byId("originRefType").value=dojo.byId('objectClass').value;
  dojo.byId("originRefId").value=dojo.byId("objectId").value;
  dijit.byId("originOriginType").set('value', origType);
  dojo.byId("originOriginId").value=origId;
  actionOK=function() {
    loadContent("../tool/removeOrigin.php", "resultDivMain", "originForm", true,
        'origin');
  };
  msg=i18n('confirmDeleteOrigin', new Array(i18n(origType), origId));
  showConfirm(msg, actionOK);
}

// =============================================================================
// = Assignments
// =============================================================================

/**
 * Display a add Assignment Box
 * 
 */
function addAssignment(unit, rawUnit, hoursPerDay, isTeam, isOrganization) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var objClass = dojo.byId('objectClass').value;
  var callBack = function () {
    dijit.byId("dialogAssignment").show();
  };
  var params="&refType="+dojo.byId('objectClass').value;
  params+="&refId="+dojo.byId("objectId").value;
  params+="&idProject="+dijit.byId('idProject').get('value');
  params+="&unit="+unit;
  if (dojo.byId('objectClass').value == 'Meeting' || dojo.byId('objectClass').value == 'PeriodicMeeting') {
    params+="&meetingEndTime="+dijit.byId('meetingEndTime');
    params+="&meetingEndTimeValue="+dijit.byId('meetingEndTime').get('value');
    params+="&meetingStartTime="+dijit.byId('meetingStartTime');
    params+="&meetingStartTimeValue="+dijit.byId('meetingStartTime').get('value');
    params+="&rawUnit="+rawUnit;
    params+="&hoursPerDay="+hoursPerDay;
  }
  if (dojo.byId('objectClass').value != 'PeriodicMeeting') {
    params+="&validatedWorkPe="+dijit.byId(objClass +"PlanningElement_validatedWork").get('value');
    params+="&assignedWorkPe="+dijit.byId(objClass +"PlanningElement_assignedWork").get('value');
  }
  params+="&isTeam="+isTeam+"&isOrganization="+isOrganization; 
  params+="&mode=add";  
  loadDialog('dialogAssignment',callBack,false,params);
}

/**
 * Display a edit Assignment Box
 * 
 */

var editAssignmentLoading=false;
function editAssignment(assignmentId, idResource, idRole, cost, rate,
    assignedWork, realWork, leftWork, unit, optional) { 
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    editAssignmentLoading=false;
    assignmentUpdatePlannedWork('assignment');
    dijit.byId("dialogAssignment").show();
};
var params="&idAssignment="+assignmentId;
params+="&refType="+dojo.byId('objectClass').value;
params+="&idProject="+dijit.byId('idProject').get('value');
params+="&refId="+dojo.byId("objectId").value;
params+="&idResource="+idResource;
params+="&idRole="+idRole;
params+="&mode=edit";
params+="&unit="+unit;
params+="&realWork="+realWork;
editAssignmentLoading=true;
loadDialog('dialogAssignment',callBack,false,params);
}

/**
 * Display a divide assignment box 
 * 
 * @param prefix
 * @return
 */

function divideAssignment(assignedIdOrigin,unit) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    dijit.byId("dialogAssignment").show();
  };
  var params="&refType="+dojo.byId('objectClass').value;
  params+="&refId="+dojo.byId("objectId").value;
  params+="&idProject="+dijit.byId('idProject').get('value');
  params+="&assignedIdOrigin="+assignedIdOrigin;
  params+="&unit="+unit;
  params+="&mode=divide";
  loadDialog('dialogAssignment',callBack,false,params);
}

/**
 * Update the left work on assignment update
 * 
 * @param prefix
 * @return
 */
function assignmentUpdateLeftWork(prefix) {
  var initAssigned=dojo.byId(prefix + "AssignedWorkInit");
  var initLeft=dojo.byId(prefix + "LeftWorkInit");
  var assigned=dojo.byId(prefix + "AssignedWork");
  var newAssigned=dojo.number.parse(assigned.value);
  if (newAssigned == null || isNaN(newAssigned)) {
    newAssigned=0;
    assigned.value=dojo.number.format(newAssigned);
  }
  var left=dojo.byId(prefix + "LeftWork");
  //// Krowry #2338 ////
  var real = dojo.byId(prefix + "RealWork");
  // var planned = dojo.byId(prefix + "PlannedWork");
  diff=dojo.number.parse(assigned.value) - initAssigned.value;
  newLeft=parseFloat(initLeft.value) + diff;
  if (newLeft < 0 || isNaN(newLeft)) {
    newLeft=0;
  }
  if(assigned.value != initAssigned.value){
    diffe=dojo.number.parse(assigned.value) - real.value ;
    if (initAssigned.value==0 || isNaN(initAssigned.value)){
      newLeft= 0 + diffe;
    }
  }
  left.value=dojo.number.format(newLeft);
  assignmentUpdatePlannedWork(prefix);
}

/**
 * Update the planned work on assignment update
 * 
 * @param prefix
 * @return
 */
function assignmentUpdatePlannedWork(prefix) {
  var left=dojo.byId(prefix + "LeftWork");
  var newLeft=dojo.number.parse(left.value);
  if (newLeft == null || isNaN(newLeft)) {
    newLeft=0;
    left.value=dojo.number.format(newLeft);
  }
  var real=dojo.byId(prefix + "RealWork");
  var planned=dojo.byId(prefix + "PlannedWork");
  newPlanned=dojo.number.parse(real.value) + dojo.number.parse(left.value);
  planned.value=dojo.number.format(newPlanned);
  
}

/**
 * save an Assignment (after addAssignment or editAssignment)
 * 
 */
function saveAssignment(definitive) {
  /*
   * if (! dijit.byId('assignmentIdResource').get('value')) {
   * showAlert(i18n('messageMandatory',new Array(i18n('colIdResource'))));
   * return; } if (! dijit.byId('assignmentIdResource').get('value')) {
   * showAlert(i18n('messageMandatory',new Array(i18n('colIdResource'))));
   * return; }
   */
  var formVar=dijit.byId('assignmentForm');
  var planningMode = dojo.byId('planningMode').value;
  var mode = dojo.byId('mode').value;
  var isTeam = dojo.byId('isTeam').value;
  var isOrga = dojo.byId('isOrganization').value;
  
  if (formVar.validate()) {
    dijit.byId("assignmentPlannedWork").focus();
    dijit.byId("assignmentLeftWork").focus();
    url="../tool/saveAssignment.php";
    if (definitive) url+="?definitive="+definitive;
    if(planningMode == 'MAN' && mode != 'edit' && !isTeam && !isOrga){
    	var callback=function(){
    	    	var lastOperationStatus = dojo.byId('lastOperationStatus').value;
    	    	if(lastOperationStatus != 'INVALID'){
    	    		var params="&idAssignment="+dojo.byId('idAssignment').value;
        	    	params+="&refType="+dojo.byId('objectClass').value;
        	    	params+="&idProject="+dijit.byId('idProject').get('value');
        	    	params+="&refId="+dojo.byId("objectId").value;
        	    	params+="&idResource="+dijit.byId('assignmentIdResource').get('value');
        	    	params+="&idRole="+dijit.byId('assignmentIdRole').get('value');
        	    	params+="&unit="+dojo.byId('assignmentAssignedUnit').value;
        	    	params+="&realWork="+dijit.byId('assignmentRealWork').get('value');
        	    	params+=dijit.byId('assignmentDailyCost').get('value');
        	    	params+="&mode=edit";
    	    		loadDialog('dialogAssignment',null,false,params);
    	    	}else{
    	    		dijit.byId('dialogAssignment').hide();
    	    	}
        };
        loadContent(url, "resultDivMain", "assignmentForm",
            true, 'assignment', null,null,callback);
    }else{
    	loadContent(url, "resultDivMain", "assignmentForm",
                true, 'assignment');
    	dijit.byId('dialogAssignment').hide();
    }
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

/**
 * Display a delete Assignment Box
 * 
 */
function removeAssignment(assignmentId, realWork, resource) {
  var planningMode = dojo.byId('planningMode').value;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  if (parseFloat(realWork)) {
    msg=i18n('msgUnableToDeleteRealWork');
    showAlert(msg);
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeAssignment.php?assignmentId="+assignmentId+"&assignmentRefType="+dojo.byId('objectClass').value+"&assignmentRefId="+dojo.byId("objectId").value+"&planningMode="+planningMode, "resultDivMain", null,
        true, "assignment");
  };
  msg=i18n('confirmDeleteAssignment', new Array(resource));
  if(planningMode == 'MAN'){
	  msg += '<br/><br/>'+i18n("confirmControlDeletePlannedWork");
  }
  showConfirm(msg, actionOK);
}
//gautier #resourceTeam
function assignmentChangeResourceTeamForCapacity() {
  if (editAssignmentLoading)
    return;
  var idResource=dijit.byId("assignmentIdResource").get("value");
  if (idResource.trim()) {
	  enableWidget('dialogAssignmentSubmit');
  }else{
	  disableWidget('dialogAssignmentSubmit');
  }
  if (!idResource.trim()) {return;}
  dojo.xhrGet({
    url : '../tool/getIfResourceTeamOrResource.php?idResource='+idResource,
    handleAs : "text",
    load : function(data) {
      if(data == 'isResourceTeam'){
        dojo.byId('assignmentRateRow').style.display="none";
        dojo.byId('assignmentCapacityResourceTeam').style.display="table-row";
        dojo.byId('assignmentUniqueSelection').style.display="table-row";
      }else{
        dojo.byId('assignmentRateRow').style.display="table-row";
        dojo.byId('assignmentCapacityResourceTeam').style.display="none";
        dojo.byId('assignmentUniqueSelection').style.display="none";
        dijit.byId('assignmentUnique').set('checked',false);
      }
      var planningMode = dojo.byId('planningMode').value;
      if(planningMode=='MAN'){
    	  dojo.byId('assignmentRateRow').style.display="none";
      }
    }
  });
}
function assignmentChangeUniqueResource(newValue) {
  if(newValue==false){
    dojo.byId('assignmentRateRow').style.display="none";
    dojo.byId('assignmentCapacityResourceTeam').style.display="table-row";
  }else{
    dojo.byId('assignmentRateRow').style.display="table-row";
    dojo.byId('assignmentCapacityResourceTeam').style.display="none";
  }
}
assignmentUserSelectUniqueResourceCurrent=null;
function assignmentUserSelectUniqueResource(newValue,idRes) {
  if (assignmentUserSelectUniqueResourceCurrent!=null) return;
  assignmentUserSelectUniqueResourceCurrent=idRes;
  dojo.query(".dialogAssignmentManualSelectCheck").forEach(function(node, index, nodelist) {
    var id=node.getAttribute('widgetid');
    if (dijit.byId(id) && parseInt(id.substr(34))!=parseInt(idRes)) {
      dijit.byId(id).set('checked',false);
    }
  });
  dojo.byId("dialogAssignmentManualSelect").value=(newValue)?idRes:null;
  setTimeout("assignmentUserSelectUniqueResourceCurrent=null;",100);
}

function assignmentChangeResource() {
  if (editAssignmentLoading)
    return;
  var idResource=dijit.byId("assignmentIdResource").get("value");
  var isTeam=dojo.byId("isTeam").value;
  var isOrganization=dojo.byId("isOrganization").value;
  if (!idResource) {return;}
  if (dijit.byId('assignmentDailyCost')) {dijit.byId('assignmentDailyCost').reset();}
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=resourceRole&idResource='+idResource+'&isTeam='+isTeam+'&isOrganization='+isOrganization,
    handleAs : "text",
    load : function(data) {
      //if (data) dijit.byId('assignmentCapacity').set('value', parseInt(data)); // Error fixed by PBER : we retreive an idRole (and must)
      if (data) dijit.byId('assignmentIdRole').set('value', parseInt(data));
    }
  });
}

function assignmentChangeResourceSelectFonction(){
  if (editAssignmentLoading)
    return;
  var idResource=dijit.byId("assignmentIdResource").get("value");
  if (!idResource) {return;}
  if (dijit.byId('assignmentDailyCost')) {dijit.byId('assignmentDailyCost').reset();}
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=resourceRole&idResource='+idResource,
    handleAs : "text",
    load : function(data) {
      refreshListSpecific('listRoleResource', 'assignmentIdRole', 'idResource',idResource);
      if (data) {
        dijit.byId('assignmentIdRole').set('value', parseInt(data));
      }else{
        dijit.byId('assignmentIdRole').set('value', null);
      }
    }
  });
}

function refreshReccurentAssignmentDiv(){
	showWait();
	callBack=function() {
	     hideWait();
    };
	loadContent('../tool/refreshReccurentAssignmentDiv.php', 'recurringAssignmentDiv', 'assignmentForm', null, null, null, null, callBack);
}

function assignmentChangeRole() {
  if (editAssignmentLoading)
    return;
  var idResource=dijit.byId("assignmentIdResource").get("value");
  var idRole=dijit.byId("assignmentIdRole").get("value");
  if(!idRole.trim())disableWidget('dialogAssignmentSubmit');
  else if ( dijit.byId('dialogAssignmentSubmit').get('disabled')==true)enableWidget('dialogAssignmentSubmit');
  if (!idResource || !idRole)
    return;
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=resourceCost&idResource='
        + idResource + '&idRole=' + idRole,
    handleAs : "text",
    load : function(data) {
      // #303
      // dijit.byId('assignmentDailyCost').set('value',data);
      dijit.byId('assignmentDailyCost').set('value', dojo.number.format(data));
    }
  });
}
//gautier #2516
function billLineChangeCatalog(){
  var idCatalog=dijit.byId("billLineIdCatalog").get("value");
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=catalogBillLine&idCatalog='+idCatalog,
    handleAs : "text",
    load : function(data) {
      arrayData=data.split('#!#!#!#!#!#');
      dijit.byId('billLineDescription').set('value',arrayData[0]);
      dijit.byId('billLineDetail').set('value',arrayData[1]);
      dijit.byId('billLinePrice').set('value',parseFloat(arrayData[3]));
      dijit.byId('billLineUnit').set('value',arrayData[4]);
      if(arrayData[6]){
        dijit.byId('billLineQuantity').set('value',parseFloat(arrayData[6])); 
      }
    }
  });
}
//end

// =============================================================================
// = ExpenseDetail
// =============================================================================

/**
 * Display a add Assignment Box
 * 
 */
function addExpenseDetail(expenseType) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dojo.byId("expenseDetailId").value="";
  dojo.byId("idExpense").value=dojo.byId("objectId").value;
  dijit.byId("expenseDetailName").reset();
  dijit.byId("expenseDetailReference").reset();
  dijit.byId("expenseDetailDate").set('value', null);
  dijit.byId("expenseDetailType").reset();
  dojo.byId("expenseDetailDiv").innerHTML="";
  dijit.byId("expenseDetailAmount").reset();
  refreshList('idExpenseDetailType', expenseType, '1', null,'expenseDetailType', false);
  // dijit.byId("dialogExpenseDetail").set('title',i18n("dialogExpenseDetail"));
  dijit.byId("dialogExpenseDetail").show();
}

/**
 * Display a edit Assignment Box
 * 
 */
var expenseDetailLoad=false;
function editExpenseDetail(expenseType, id, idExpense, type, expenseDate,
    amount) {
  expenseDetailLoad=true;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  refreshList('idExpenseDetailType', expenseType, '1', null,'expenseDetailType', false);
  dojo.byId("expenseDetailId").value=id;
  dojo.byId("idExpense").value=idExpense;
  dijit.byId("expenseDetailName").set("value",
      dojo.byId('expenseDetail_' + id).value);
  dijit.byId("expenseDetailReference").set("value",
      dojo.byId('expenseDetailRef_' + id).value);
  dijit.byId("expenseDetailDate").set("value", getDate(expenseDate));
  dijit.byId("expenseDetailAmount").set("value", dojo.number.parse(amount));
  dijit.byId("dialogExpenseDetail").set('title',
      i18n("dialogExpenseDetail") + " #" + id);
  dijit.byId("expenseDetailType").set("value", type);
  expenseDetailLoad=false;
  expenseDetailTypeChange(id);
  expenseDetailLoad=true;
  setTimeout('expenseDetailLoad=false;', 500);
  dijit.byId("dialogExpenseDetail").show();
}

/**
 * save an Assignment (after addAssignment or editAssignment)
 * 
 */
function saveExpenseDetail() {
  expenseDetailRecalculate();
  if (!dijit.byId('expenseDetailName').get('value')) {
    showAlert(i18n('messageMandatory', new Array(i18n('colName'))));
    return;
  }
  /*if (!dijit.byId('expenseDetailDate').get('value')) {
    showAlert(i18n('messageMandatory', new Array(i18n('colDate'))));
    return;
  }*/
  /*if (!dijit.byId('expenseDetailType').get('value')) {
    showAlert(i18n('messageMandatory', new Array(i18n('colType'))));
    return;
  }*/
  if (!dijit.byId('expenseDetailAmount').get('value')) {
    showAlert(i18n('messageMandatory', new Array(i18n('colAmount'))));
    return;
  }
  var formVar=dijit.byId('expenseDetailForm');
  if (formVar.validate()) {
    dijit.byId("expenseDetailName").focus();
    dijit.byId("expenseDetailAmount").focus();
    loadContent("../tool/saveExpenseDetail.php", "resultDivMain",
        "expenseDetailForm", true, 'expenseDetail');
    dijit.byId('dialogExpenseDetail').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

/**
 * Display a delete Assignment Box
 * 
 */
function removeExpenseDetail(expenseDetailId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dojo.byId("expenseDetailId").value=expenseDetailId;
  actionOK=function() {
    loadContent("../tool/removeExpenseDetail.php", "resultDivMain",
        "expenseDetailForm", true, 'expenseDetail');
  };
  msg=i18n('confirmDeleteExpenseDetail', new Array(dojo.byId('expenseDetail_'
      + expenseDetailId).value));
  showConfirm(msg, actionOK);
}

function expenseDetailTypeChange(expenseDetailId) {
  if (expenseDetailLoad)
    return;
  var idType=dijit.byId("expenseDetailType").get("value");
  var url='../tool/expenseDetailDiv.php?idType=' + idType;
  if (expenseDetailId) {
    url+='&expenseDetailId=' + expenseDetailId;
  }
  loadContent(url, 'expenseDetailDiv', null, false);
}

function expenseDetailRecalculate() {
  val=false;
  if (! dojo.byId('expenseDetailValue01')) return;
  if (dijit.byId('expenseDetailValue01')) {
    val01=dijit.byId('expenseDetailValue01').get("value");
  } else {
    val01=dojo.byId('expenseDetailValue01').value;
  }
  if (dijit.byId('expenseDetailValue02')) {
    val02=dijit.byId('expenseDetailValue02').get("value");
  } else {
    val02=dojo.byId('expenseDetailValue02').value;
  }
  if (dijit.byId('expenseDetailValue03')) {
    val03=dijit.byId('expenseDetailValue03').get("value");
  } else {
    val03=dojo.byId('expenseDetailValue03').value;
  }
  total=1;
  if (dojo.byId('expenseDetailUnit01').value) {
    total=total * val01;
    val=true;
  }
  if (dojo.byId('expenseDetailUnit02').value) {
    total=total * val02;
    val=true;
  }
  if (dojo.byId('expenseDetailUnit03').value) {
    total=total * val03;
    val=true;
  }
  if (val) {
    dijit.byId("expenseDetailAmount").set('value', total);
    lockWidget("expenseDetailAmount");
  } else {
    unlockWidget("expenseDetailAmount");
  }
}

// =============================================================================
// = DocumentVersion
// =============================================================================

/**
 * Display a add Document Version Box
 * 
 */
function addDocumentVersion(defaultStatus, typeEvo, numVers, dateVers, nameVers, lockStatus) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  content=dijit.byId('dialogDocumentVersion').get('content');
  if (content == "") {
    callBack=function() {
      dojo.connect(dijit.byId("documentVersionFile"), "onComplete", function(
          dataArray) {
        saveDocumentVersionAck(dataArray);
      });
      dojo.connect(dijit.byId("documentVersionFile"), "onProgress", function(
          data) {
        saveDocumentVersionProgress(data);
      });
      addDocumentVersion(defaultStatus, typeEvo, numVers, dateVers, nameVers, lockStatus);
    };
    loadDialog('dialogDocumentVersion', callBack);
    return;
  }
  dojo.style(dojo.byId('downloadProgress'), {
    display : 'none'
  });
  if (dijit.byId("documentVersionFile")) {
    dijit.byId("documentVersionFile").reset();
    if (!isHtml5()) {
      enableWidget('dialogDocumentVersionSubmit');
    } else {
      disableWidget('dialogDocumentVersionSubmit');
    }
  }
  dojo.byId("documentVersionId").value="";
  dojo.byId('documentVersionFileName').innerHTML="";
  refreshListSpecific('listStatusDocumentVersion', 'documentVersionIdStatus','idDocumentVersion', '');
  dijit.byId('documentVersionIdStatus').set('value', defaultStatus);
  dojo.style(dojo.byId('inputFileDocumentVersion'), {
    display : 'block'
  });
  dojo.byId("documentId").value=dojo.byId("objectId").value;
  dojo.byId("documentVersionVersion").value=dojo.byId('version').value;
  dojo.byId("documentVersionRevision").value=dojo.byId('revision').value;
  dojo.byId("documentVersionDraft").value=dojo.byId('draft').value;
  dojo.byId("typeEvo").value=typeEvo;
  dijit.byId("documentVersionLink").set('value', '');
  dijit.byId("documentVersionFile").reset();
  dijit.byId("documentVersionDescription").set('value', '');
  dijit.byId("documentVersionUpdateMajor").set('checked', 'true');
  dijit.byId("documentVersionUpdateDraft").set('checked', false);
  dijit.byId("documentVersionDate").set('value', new Date());
  dijit.byId("documentVersionUpdateMajor").set('readOnly', false);
  dijit.byId("documentVersionUpdateMinor").set('readOnly', false);
  dijit.byId("documentVersionUpdateNo").set('readonly', false);
  dijit.byId("documentVersionUpdateDraft").set('readonly', false);
  dijit.byId("documentVersionIsRef").set('checked', false);
  dijit.byId('documentVersionVersionDisplay')
      .set(
          'value',
          getDisplayVersion(typeEvo, dojo.byId('documentVersionVersion').value,
              dojo.byId('documentVersionRevision').value, dojo
                  .byId('documentVersionDraft').value), numVers, dateVers,
          nameVers);
  dojo.byId('documentVersionMode').value="add";
  calculateNewVersion();
  setDisplayIsRefDocumentVersion();
  if(lockStatus==1){
	dojo.byId("lockedMsg").style.display = 'block';
  }else{
    dojo.byId("lockedMsg").style.display = 'none';  
  }
  dijit.byId("dialogDocumentVersion").show();
  dojo.setStyle('widget_documentVersionNewVersionDisplay',"border-color","#b3b3b3");
}

/**
 * Display a edit Document Version Box
 * 
 */
// var documentVersionLoad=false;
function editDocumentVersion(id, version, revision, draft, versionDate, status,
    isRef, typeEvo, numVers, dateVers, nameVers) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  content=dijit.byId('dialogDocumentVersion').get('content');
  if (content == "") {
    callBack=function() {
      dojo.connect(dijit.byId("documentVersionFile"), "onComplete", function(
          dataArray) {
        saveDocumentVersionAck(dataArray);
      });
      dojo.connect(dijit.byId("documentVersionFile"), "onProgress", function(
          data) {
        saveDocumentVersionProgress(data);
      });
      editDocumentVersion(id, version, revision, draft, versionDate, status,
          isRef, typeEvo, numVers, dateVers, nameVers);
    };
    loadDialog('dialogDocumentVersion', callBack);
    return;
  }
  dijit.byId('documentVersionIdStatus').store;
  refreshListSpecific('listStatusDocumentVersion', 'documentVersionIdStatus','idDocumentVersion', id);
  dijit.byId('documentVersionIdStatus').set('value', status);
  dojo.style(dojo.byId('inputFileDocumentVersion'), {
    display : 'none'
  });
  dojo.byId("documentVersionId").value=id;
  dojo.byId("documentId").value=dojo.byId("objectId").value;
  dojo.byId("documentVersionVersion").value=version;
  dojo.byId("documentVersionRevision").value=revision;
  dojo.byId("documentVersionDraft").value=draft;
  dojo.byId("typeEvo").value=typeEvo;
  if (draft) {
    dijit.byId('documentVersionUpdateDraft').set('checked', true);
  } else {
    dijit.byId('documentVersionUpdateDraft').set('checked', false);
  }
  if (isRef == '1') {
    dijit.byId('documentVersionIsRef').set('checked', true);
  } else {
    dijit.byId('documentVersionIsRef').set('checked', false);
  }
  dijit.byId("documentVersionLink").set('value', '');
  dijit.byId("documentVersionFile").reset();
  dijit.byId("documentVersionDescription").set("value",
      dojo.byId("documentVersion_" + id).value);
  dijit.byId("documentVersionUpdateMajor").set('readOnly', 'readOnly');
  dijit.byId("documentVersionUpdateMinor").set('readOnly', 'readOnly');
  dijit.byId("documentVersionUpdateNo").set('readonly', 'readonly');
  dijit.byId("documentVersionUpdateNo").set('checked', true);
  dijit.byId("documentVersionUpdateDraft").set('readonly', 'readonly');
  dijit.byId("documentVersionDate").set('value', versionDate);
  dojo.byId('documentVersionMode').value="edit";
  dijit.byId('documentVersionVersionDisplay').set('value', nameVers);
  calculateNewVersion(false);
  setDisplayIsRefDocumentVersion();
  dijit.byId("dialogDocumentVersion").show();
}

function changeDocumentVersion(list) {
  if (list.length > 0) {
    dojo.byId('documentVersionFileName').innerHTML=list[0]['name'];
    enableWidget('dialogDocumentVersionSubmit');
  } else {
    dojo.byId('documentVersionFileName').innerHTML="";
    disableWidget('dialogDocumentVersionSubmit');
  }
}

/**
 * save an Assignment (after addAssignment or editAssignment)
 * 
 */
function saveDocumentVersion() {
  // dojo.byId('documentVersionForm').submit();
  if (!isHtml5()) {
    // dojo.byId('documentVersionForm').submit();
    showWait();
    dijit.byId('dialogDocumentVersion').hide();
    return true;
  }
  if (dojo.byId('documentVersionFileName').innerHTML == "") {
    return false;
  }
  dojo.style(dojo.byId('downloadProgress'), {
    display : 'block'
  });
  showWait();
  dijit.byId('dialogDocumentVersion').hide();
  return true;
}

/**
 * Acknoledge the attachment save
 * 
 * @return void
 */
function saveDocumentVersionAck(dataArray) {
  if (!isHtml5()) {
    resultFrame=document.getElementById("documentVersionPost");
    resultText=documentVersionPost.document.body.innerHTML;
    dojo.byId('resultAckDocumentVersion').value=resultText;
    loadContent("../tool/ack.php", "resultDivMain", "documentVersionAckForm", true,
        'documentVersion');
    return;
  }
  dijit.byId('dialogDocumentVersion').hide();
  if (dojo.isArray(dataArray)) {
    result=dataArray[0];
  } else {
    result=dataArray;
  }
  dojo.style(dojo.byId('downloadProgress'), {
    display : 'none'
  });
  dojo.byId('resultAckDocumentVersion').value=result.message;
  loadContent("../tool/ack.php", "resultDivMain", "documentVersionAckForm", true,
      'documentVersion');
}

function saveDocumentVersionProgress(data) {
  done=data.bytesLoaded;
  total=data.bytesTotal;
  if (total) {
    progress=done / total;
  }
  dijit.byId('downloadProgress').set('value', progress);
}
/**
 * Display a delete Assignment Box
 * 
 */
function removeDocumentVersion(documentVersionId, documentVersionName) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  content=dijit.byId('dialogDocumentVersion').get('content');
  if (content == "") {
    callBack=function() {
      dojo.connect(dijit.byId("documentVersionFile"), "onComplete", function(
          dataArray) {
        saveDocumentVersionAck(dataArray);
      });
      dojo.connect(dijit.byId("documentVersionFile"), "onProgress", function(
          data) {
        saveDocumentVersionProgress(data);
      });
      removeDocumentVersion(documentVersionId, documentVersionName);
    };
    loadDialog('dialogDocumentVersion', callBack);
    return;
  }
  dojo.byId("documentVersionId").value=documentVersionId;
  actionOK=function() {
    loadContent("../tool/removeDocumentVersion.php", "resultDivMain",
        "documentVersionForm", true, 'documentVersion');
  };
  msg=i18n('confirmDeleteDocumentVersion', new Array(documentVersionName));
  showConfirm(msg, actionOK);
}

function getDisplayVersion(typeEvo, version, revision, draft, numVers,
    dateVers, nameVers) {
  var res="";
  if (typeEvo == "EVO") {
    if (version != "" && revision != "") {
      res="V" + version + "." + revision;
    }
  } else if (typeEvo == "EVT") {
    res=dateVers;
  } else if (typeEvo == "SEQ") {
    res=numVers;
  } else if (typeEvo == "EXT") {
    res=nameVers;
  }
  if (typeEvo == "EVO" || typeEvo == "EVT" || typeEvo == "SEQ") {
    if (draft) {
      res+=draftSeparator + draft;
    }
  }
  return res;
}

function calculateNewVersion(update) {
  var typeEvo=dojo.byId("typeEvo").value;
  var numVers="";
  var dateVers="";
  var nameVers="";
  if (dijit.byId('documentVersionUpdateMajor').get('checked')) {
    type="major";
  } else if (dijit.byId('documentVersionUpdateMinor').get('checked')) {
    type="minor";
  } else if (dijit.byId('documentVersionUpdateNo').get('checked')) {
    type="none";
  }
  version=dojo.byId('documentVersionVersion').value;
  revision=dojo.byId('documentVersionRevision').value;
  draft=dojo.byId('documentVersionDraft').value;
  isDraft=dijit.byId('documentVersionUpdateDraft').get('checked');
  version=(version == '') ? 0 : parseInt(version, 10);
  revision=(revision == '') ? 0 : parseInt(revision, 10);
  draft=(draft == '') ? 0 : parseInt(draft, 10);
  if (type == "major") {
    dojo.byId('documentVersionNewVersion').value=version + 1;
    dojo.byId('documentVersionNewRevision').value=0;
    dojo.byId('documentVersionNewDraft').value=(isDraft) ? '1' : '';
  } else if (type == "minor") {
    dojo.byId('documentVersionNewVersion').value=version;
    dojo.byId('documentVersionNewRevision').value=revision + 1;
    dojo.byId('documentVersionNewDraft').value=(isDraft) ? '1' : '';
  } else { // 'none'
    dojo.byId('documentVersionNewVersion').value=version;
    dojo.byId('documentVersionNewRevision').value=revision;
    if (dojo.byId('documentVersionId').value) {
      dojo.byId('documentVersionNewDraft').value=(isDraft) ? ((draft) ? draft
          : 1) : '';
    } else {
      dojo.byId('documentVersionNewDraft').value=(isDraft) ? draft + 1 : '';
    }
  }
  dateVers=dojo.date.locale.format(dijit.byId("documentVersionDate").get(
      'value'), {
    datePattern : "yyyyMMdd",
    selector : "date"
  });
  nameVers=dijit.byId("documentVersionVersionDisplay").get('value');
  numVers=nameVers;
  if (typeEvo == "SEQ" && dojo.byId('documentVersionMode').value == "add") {
    if (!nameVers) {
      nameVers=0;
    }
    numVers=parseInt(nameVers, 10) + 1;
  }
  dijit.byId("documentVersionNewVersionDisplay").set('readOnly', 'readOnly');
  if (typeEvo == "EXT") {
    dijit.byId("documentVersionNewVersionDisplay").set('readOnly', false);
  }
  var newVers=getDisplayVersion(typeEvo,
      dojo.byId('documentVersionNewVersion').value, dojo
          .byId('documentVersionNewRevision').value, dojo
          .byId('documentVersionNewDraft').value, numVers, dateVers, nameVers);
  dijit.byId('documentVersionNewVersionDisplay').set('value', newVers);
  if (typeEvo == "EXT") {
    dojo.byId('oldDocumentVersionNewVersionDisplay').value= newVers;
  }
  if (isDraft) {
    dijit.byId('documentVersionIsRef').set('checked', false);
    setDisplayIsRefDocumentVersion();
  }
}

function setDisplayIsRefDocumentVersion() {
  if (dijit.byId('documentVersionIsRef').get('checked')) {
    dojo.style(dojo.byId('documentVersionIsRefDisplay'), {
      display : 'block'
    });
    dijit.byId('documentVersionUpdateDraft').set('checked', false);
    calculateNewVersion();
  } else {
    dojo.style(dojo.byId('documentVersionIsRefDisplay'), {
      display : 'none'
    });
  }
}

// =============================================================================
// = Dependency
// =============================================================================

/**
 * Display a add Dependency Box
 * 
 */
function addDependency(depType) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  noRefreshDependencyList=false;
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  var message=i18n("dialogDependency");
  if (depType) {
    dojo.byId("dependencyType").value=depType;
    message=i18n("dialogDependencyRestricted", new Array(i18n(objectClass),
        objectId, i18n(depType)));
  } else {
    dojo.byId("dependencyType").value=null;
    message=i18n("dialogDependencyExtended", new Array(i18n(objectClass),
        objectId.value));
  }
  if (objectClass == 'Requirement') {
    refreshList('idDependable', 'scope', 'R', '4', 'dependencyRefTypeDep',true);
    dijit.byId("dependencyRefTypeDep").set('value', '4');
    dijit.byId("dependencyDelay").set('value', '0');
    dojo.byId("dependencyDelayDiv").style.display="none";
    dojo.byId("dependencyTypeDiv").style.display="none";
  } else if (objectClass == 'TestCase') {
    refreshList('idDependable', 'scope', 'TC', '5', 'dependencyRefTypeDep',true);
    dijit.byId("dependencyRefTypeDep").set('value', '5');
    dijit.byId("dependencyDelay").set('value', '0');
    dojo.byId("dependencyDelayDiv").style.display="none";
    dojo.byId("dependencyTypeDiv").style.display="none";
  } else {
    if (objectClass == 'Project') {
      dijit.byId("dependencyRefTypeDep").set('value', '3');
      refreshList('idDependable', 'scope', 'PE', '3', 'dependencyRefTypeDep',true);
    } else {
      dijit.byId("dependencyRefTypeDep").set('value', '1');
      refreshList('idDependable', 'scope', 'PE', '1', 'dependencyRefTypeDep',true);
    }
    if (objectClass == 'Term') {
      dojo.byId("dependencyDelayDiv").style.display="none";
      dojo.byId("dependencyTypeDiv").style.display="none";
      dijit.byId("typeOfDependency").set("value","E-S");
    } else {
      dojo.byId("dependencyDelayDiv").style.display="block";
      dojo.byId("dependencyTypeDiv").style.display="block";
    }
  }
  dojo.byId("dependencyRefType").value=objectClass;
  dojo.byId("dependencyRefId").value=objectId;
  refreshList('idActivity', 'idProject', '0', null, 'dependencyRefIdDepEdit',false);
  dijit.byId('dependencyRefIdDepEdit').reset();
  dojo.byId("dependencyId").value="";
  dijit.byId("dialogDependency").set('title', message);
  dijit.byId("dialogDependency").show();
  dojo.byId('dependencyAddDiv').style.display='block';
  dojo.byId('dependencyEditDiv').style.display='none';
  dijit.byId("dependencyRefTypeDep").set('readOnly', false);
  dijit.byId("dependencyComment").set('value',null);
  disableWidget('dialogDependencySubmit');
  refreshDependencyList();
}

function editDependency(depType, id, refType, refTypeName, refId, delay, typeOfDependency) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  noRefreshDependencyList=true;
  var objectClass=dojo.byId('objectClass').value;
  var objectId=dojo.byId("objectId").value;
  var message=i18n("dialogDependencyEdit");
  if (objectClass == 'Requirement') {
    refreshList('idDependable', 'scope', 'R', refType, 'dependencyRefTypeDep',true);
    dijit.byId("dependencyRefTypeDep").set('value', refType);
    dijit.byId("dependencyDelay").set('value', '0');
    dojo.byId("dependencyDelayDiv").style.display="none";
    dojo.byId("dependencyTypeDiv").style.display="none";
  } else if (objectClass == 'TestCase') {
    refreshList('idDependable', 'scope', 'TC', refType, 'dependencyRefTypeDep',true);
    dijit.byId("dependencyRefTypeDep").set('value', refType);
    dijit.byId("dependencyDelay").set('value', '0');
    dojo.byId("dependencyDelayDiv").style.display="none";
    dojo.byId("dependencyTypeDiv").style.display="none";
  } else {
    refreshList('idDependable', 'scope', 'PE', refType, 'dependencyRefTypeDep',true);
    dijit.byId("dependencyRefTypeDep").set('value', refType);
    dijit.byId("dependencyDelay").set('value', delay);
    dojo.byId("dependencyDelayDiv").style.display="block";
    dojo.byId("dependencyTypeDiv").style.display="block";
  }
  // refreshDependencyList();
  refreshList('id' + refTypeName, 'idProject', '0', refId,'dependencyRefIdDepEdit', true);
  dijit.byId('dependencyRefIdDepEdit').set('value', refId);
  dojo.byId("dependencyId").value=id;
  dojo.byId("dependencyRefType").value=objectClass;
  dojo.byId("dependencyRefId").value=objectId;
  dojo.byId("dependencyType").value=depType;
  dijit.byId("typeOfDependency").set('value', typeOfDependency);
  dijit.byId("dialogDependency").set('title', message);
  dijit.byId("dialogDependency").show();
  dojo.byId('dependencyAddDiv').style.display='none';
  dojo.byId('dependencyEditDiv').style.display='block';
  dijit.byId("dependencyRefTypeDep").set('readOnly', true);
  dijit.byId("dependencyRefIdDepEdit").set('readOnly', true);
  disableWidget('dialogDependencySubmit');
  //KEVIN TICKET #2038 
  disableWidget('dependencyComment');
  dijit.byId('dependencyComment').set('value',"");
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=dependencyComment&idDependency='+ id,
    handleAs : "text",
    load : function(data) {
      dijit.byId('dependencyComment').set('value', data);
      enableWidget('dialogDependencySubmit');
      enableWidget('dependencyComment');
    }
  });
}

/**
 * Refresh the Dependency list (after update)
 */
var noRefreshDependencyList=false;
function refreshDependencyList(selected) {
  if (noRefreshDependencyList)
    return;
  disableWidget('dialogDependencySubmit');
  var url='../tool/dynamicListDependency.php';
  if (selected) {
    url+='?selected=' + selected;
  }
  loadContent(url, 'dialogDependencyList', 'dependencyForm', false);
}
/**
 * save a Dependency (after addLink)
 * 
 */
function saveDependency() {
  var formVar=dijit.byId('dependencyForm');
  if (!formVar.validate()) {
    showAlert(i18n("alertInvalidForm"));
    return;
  }
  if (dojo.byId("dependencyRefIdDep").value == ""
      && !dojo.byId('dependencyId').value)
    return;
  loadContent("../tool/saveDependency.php", "resultDivMain", "dependencyForm",
      true, 'dependency');
  dijit.byId('dialogDependency').hide();
}

function saveDependencyFromDndLink(ref1Type, ref1Id, ref2Type, ref2Id) {
  // alert("saveDependencyFromDndLink("+ref1Type+","+ref1Id+","+ref2Type+","+ref2Id+")");
  if (ref1Type == ref2Type && ref1Id == ref2Id)
    return;
  param="ref1Type=" + ref1Type;
  param+="&ref1Id=" + ref1Id;
  param+="&ref2Type=" + ref2Type;
  param+="&ref2Id=" + ref2Id;
  loadContent("../tool/saveDependencyDnd.php?" + param, "resultDivMain", null,
      true, 'dependency');
}
/**
 * Display a delete Dependency Box
 * 
 */
function removeDependency(dependencyId, refType, refId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dojo.byId("dependencyId").value=dependencyId;
  actionOK=function() {
    loadContent("../tool/removeDependency.php", "resultDivMain", "dependencyForm",
        true, 'dependency');
  };
  msg=i18n('confirmDeleteLink', new Array(i18n(refType), refId));
  showConfirm(msg, actionOK);
}

// =============================================================================
// = BillLines
// =============================================================================

/**
 * Display a add line Box
 * 
 */
function addBillLine(billingType) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var postLoad=function() {  
    var prj=dijit.byId('idProject').get('value');
    refreshListSpecific('listTermProject', 'billLineIdTerm', 'idProject', prj);
    refreshListSpecific('listResourceProject', 'billLineIdResource', 'idProject',prj);
    refreshList('idActivityPrice', 'idProject', prj, null,'billLineIdActivityPrice');
    dijit.byId("dialogBillLine").set('title', i18n("dialogBillLine"));
  };
  var params="&id=";
  params+="&refType="+dojo.byId('objectClass').value;
  params+="&refId="+dojo.byId("objectId").value;
  if (billingType) params+="&billingType="+billingType;
  loadDialog('dialogBillLine', postLoad, true, params, true);
}

/**
 * Display a edit line Box
 * 
 */
function editBillLine(id,billingType) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&id="+id;
  params+="&refType="+dojo.byId('objectClass').value;
  params+="&refId="+dojo.byId("objectId").value;
  if (billingType) params+="&billingType="+billingType;
  loadDialog('dialogBillLine', null, true, params, true);
}


/**
 * save a line (after addDetail or editDetail)
 * 
 */
function saveBillLine() {
  if (isNaN(dijit.byId("billLineLine").getValue())) {
    dijit.byId("billLineLine").set("class", "dijitError");
    // dijit.byId("noteNote").blur();
    var msg=i18n('messageMandatory', new Array(i18n('BillLine')));
    new dijit.Tooltip({
      id : "billLineToolTip",
      connectId : [ "billLineLine" ],
      label : msg,
      showDelay : 0
    });
    dijit.byId("billLineLine").focus();
  } else {
    loadContent("../tool/saveBillLine.php", "resultDivMain", "billLineForm", true,
        'billLine');
    dijit.byId('dialogBillLine').hide();
  }
}

/**
 * Display a delete line Box
 * 
 */
function removeBillLine(lineId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  //dojo.byId("billLineId").value=lineId;
  actionOK=function() {
    loadContent("../tool/removeBillLine.php?billLineId="+lineId, "resultDivMain", null,
        true, 'billLine');
  };
  msg=i18n('confirmDelete', new Array(i18n('BillLine'), lineId));
  showConfirm(msg, actionOK);
}

function billLineUpdateAmount() {
  var price=dijit.byId('billLinePrice').get('value');
  var quantity=dijit.byId('billLineQuantity').get('value');
  var amount=price*quantity;
  dijit.byId('billLineAmount').set('value',amount);
}
function billLineUpdateNumberDays() {
  if (dijit.byId('billLineUnit') && dijit.byId('billLineUnit').get("value")=='3' ) { // If unit = day
    if (dijit.byId('billLineNumberDays') && dijit.byId('billLineQuantity') && dijit.byId('billLineQuantity').get("value")>0) {
      dijit.byId('billLineNumberDays').set("value",dijit.byId('billLineQuantity').get("value"));
    }
  }
}

// =============================================================================
// = ChecklistDefinitionLine
// =============================================================================

/**
 * Display a add line Box
 * 
 */
function addChecklistDefinitionLine(checkId) {
  var params="&checkId=" + checkId;
  loadDialog('dialogChecklistDefinitionLine', null, true, params);
}

/**
 * Display a edit line Box
 * 
 */
function editChecklistDefinitionLine(checkId, lineId) {
  var params="&checkId=" + checkId + "&lineId=" + lineId;
  loadDialog('dialogChecklistDefinitionLine', null, true, params);
}

/**
 * save a line (after addDetail or editDetail)
 * 
 */
function saveChecklistDefinitionLine() {
  if (!dijit.byId("dialogChecklistDefinitionLineName").get('value')) {
    showAlert(i18n('messageMandatory', new Array(i18n('colName'))));
    return false;
  }
  loadContent("../tool/saveChecklistDefinitionLine.php", "resultDivMain",
      "dialogChecklistDefinitionLineForm", true, 'checklistDefinitionLine');
  dijit.byId('dialogChecklistDefinitionLine').hide();

}

/**
 * Display a delete line Box
 * 
 */
function removeChecklistDefinitionLine(lineId) {
  var params="?lineId=" + lineId;
  // loadDialog('dialogChecklistDefinitionLine',null, true, params)
  // dojo.byId("checklistDefinitionLineId").value=lineId;
  actionOK=function() {
    loadContent("../tool/removeChecklistDefinitionLine.php" + params,
        "resultDivMain", null, true, 'checklistDefinitionLine');
  };
  msg=i18n('confirmDelete', new Array(i18n('ChecklistDefinitionLine'), lineId));
  showConfirm(msg, actionOK);
}

// =============================================================================
// = Checklist
// =============================================================================

function showChecklist(objectClass) {
  if (!objectClass) {
    return;
  }
  if (dijit.byId('id')) {
    var objectId=dijit.byId('id').get('value');
  } else {
    return;
  }
  var params="&objectClass=" + objectClass + "&objectId=" + objectId;
  loadDialog('dialogChecklist', null, true, params, true);
}

function saveChecklist() {
  // var params="&objectClass="+objectClass+"&objectId="+objectId;
  // loadDialog('dialogChecklist',null, true, params);
  loadContent('../tool/saveChecklist.php', 'resultDivMain', 'dialogChecklistForm',
      true, 'checklist');
  dijit.byId('dialogChecklist').hide();
  return false;
}

function checkClick(line, item) {
  checkName="check_" + line + "_" + item;
  if (dijit.byId(checkName).get('checked')) {
    for (var i=1; i <= 5; i++) {
      if (i != item && dijit.byId("check_" + line + "_" + i)) {
        dijit.byId("check_" + line + "_" + i).set('checked', false);
      }
    }
  }
}

// =============================================================================
// = History
// =============================================================================

function showHistory(objectClass) {
  if (!objectClass) {
    return;
  }
  if (dijit.byId('id')) {
    var objectId=dijit.byId('id').get('value');
  } else {
    return;
  }
  var params="&objectClass=" + objectClass + "&objectId=" + objectId;
  loadDialog('dialogHistory', null, true, params);
}
// =============================================================================
// = Import
// =============================================================================

/**
 * Display an import Data Box (Not used, for an eventual improvement)
 * 
 */
function importData() {
  var controls=controlImportData();
  if (controls) {
    showWait();
  }
  return controls;
}

function showHelpImportData() {
  var controls=controlImportData();
  if (controls) {
    showWait();
    var url='../tool/importHelp.php?elementType='
        + dijit.byId('elementType').get('value');
    url+='&fileType=' + dijit.byId('fileType').get('value');
    frames['resultImportData'].location.href=url;
  }
}

function controlImportData() {
  var elementType=dijit.byId('elementType').get('value');
  if (!elementType) {
    showAlert(i18n('messageMandatory', new Array(i18n('colImportElementType'))));
    return false;
  }
  var fileType=dijit.byId('fileType').get('value');
  if (!fileType) {
    showAlert(i18n('messageMandatory', new Array(i18n('colImportFileType'))));
    return false;
  }
  return true;
}
function importFinished() {
  if (dijit.byId('elementType') && dijit.byId('elementType').get('displayedValue')==i18n('Project') ) {
    refreshProjectSelectorList();
  }
}
// =============================================================================
// = Plan
// =============================================================================

/**
 * Display a planning Box
 * 
 */
var oldSelectedProjectsToPlan=null;
function showPlanParam(selectedProject) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  dijit.byId("dialogPlan").show();
  oldSelectedProjectsToPlan=dijit.byId("idProjectPlan").get("value");
}

function changedIdProjectPlan(value) {
	var selectField = dijit.byId("idProjectPlan").get("value");
	if(selectField.length <= 0){
		dijit.byId('dialogPlanSubmit').set('disabled', true);
	}else{
		dijit.byId('dialogPlanSubmit').set('disabled', false);
	}
  if (!oldSelectedProjectsToPlan || oldSelectedProjectsToPlan==value) return;
  if (oldSelectedProjectsToPlan.indexOf(" ")>=0 && value.length>1 ) {
    if(value.indexOf(" ")>=0){
    	value.splice(0,1);
    }
    oldSelectedProjectsToPlan=value;
    dijit.byId("idProjectPlan").set("value",value);
  } else if (value.indexOf(" ")>=0 && oldSelectedProjectsToPlan.indexOf(" ")===-1) {
    value=[" "];
    oldSelectedProjectsToPlan=value;
    dijit.byId("idProjectPlan").set("value",value);
  }
  oldSelectedProjectsToPlan=value;
}

function showSelectedProject(value){
	var selectedProj = oldSelectedProjectsToPlan;
	var callback=function(){
		 dijit.byId("idProjectPlan").set("value",selectedProj);
		 var selectField = dijit.byId("idProjectPlan").get("value");
			if(selectField.length <= 0){
				dijit.byId('dialogPlanSubmit').set('disabled', true);
			}else{
				dijit.byId('dialogPlanSubmit').set('disabled', false);
			}
	  };
	loadContent("../view/refreshSelectedProjectListDiv.php?isChecked="+value+"&selectedProjectPlan="+selectedProj, "selectProjectList", "dialogPlanForm", false, null,null,null,callback);
}

/**
 * Run planning
 * 
 */
function plan() {
  var bt=dijit.byId('planButton');
  if (bt) {
    bt.set('iconClass', "dijitIcon iconPlan");
  }
  if (!dijit.byId('idProjectPlan').get('value')) {
    dijit.byId('idProjectPlan').set('value', ' ');
  }
  if (!dijit.byId('startDatePlan').get('value')) {
    showAlert(i18n('messageInvalidDate'));
    return;
  }
  loadContent("../tool/plan.php", "resultDivMain", "dialogPlanForm", true, null);
  dijit.byId("dialogPlan").hide();
}

function cancelPlan() {
  if (!dijit.byId('idProjectPlan').get('value')) {
    dijit.byId('idProjectPlan').set('value', ' ');
  }
  dijit.byId('dialogPlan').hide();
}

function showPlanSaveDates() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  callBack=function() {
    var proj=dijit.byId('idProjectPlan');
    if (proj && proj.get('value') && proj.get('value')!='*') {
      dijit.byId('idProjectPlanSaveDates').set('value', proj.get('value'));
    }
  };
  /*if (dijit.byId("dialogPlanSaveDates")) {
    callBack();
    dijit.byId("dialogPlanSaveDates").show();
    return;
  }*/
  loadDialog('dialogPlanSaveDates', callBack, true,null,true);
}
function planSaveDates() {
  var formVar=dijit.byId('dialogPlanSaveDatesForm');
  if (!formVar.validate()) {
    showAlert(i18n("alertInvalidForm"));
    return;
  }
  if (!dijit.byId('idProjectPlanSaveDates').get('value')) {
    dijit.byId('idProjectPlanSaveDates').set('value', ' ');
  }
  loadContent("../tool/planSaveDates.php", "resultDivMain",
      "dialogPlanSaveDatesForm", true, null);
  dijit.byId("dialogPlanSaveDates").hide();
}

//=============================================================================
//= Baseline
//=============================================================================

function showPlanningBaseline() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  callBack=function() {
    var proj=dijit.byId('idProjectPlan');
    if (proj) {
      dijit.byId('idProjectPlanBaseline').set('value', proj.get('value'));
    }
  };
  loadDialog('dialogPlanBaseline', callBack, true);
}
function savePlanningBaseline() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callback=function(){
    dijit.byId('selectBaselineTop').reset();
    dijit.byId('selectBaselineBottom').reset();
    refreshList('idBaselineSelect',null,null,null,'selectBaselineTop');
    refreshList('idBaselineSelect',null,null,null,'selectBaselineBottom');
  };
  if (dojo.byId('isGlobalPlanning')) {
    if (dojo.byId('globalPlanning') && dojo.byId('globalPlanning').value=='true') {
      dojo.byId('isGlobalPlanning').value='true';
    }
  }
  var formVar=dijit.byId('dialogPlanBaselineForm');
  if (formVar.validate()) {
    loadContent("../tool/savePlanningBaseline.php", "resultDivMain", "dialogPlanBaselineForm", true, null,null,null,callback);
    dijit.byId("dialogPlanBaseline").hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}
function editBaseline(baselineId) {
  var params="&editMode=true&baselineId="+baselineId;
  loadDialog('dialogPlanBaseline', null, true, params, true);
}

function removeBaseline(baselineId) {
  var param="?baselineId="+baselineId;
  actionOK=function() {
    loadContent("../tool/removePlanningBaseline.php"+param, "dialogPlanBaseline", null);
  };
  msg=i18n('confirmDelete', new Array(i18n('Baseline'), baselineId));
  showConfirm(msg, actionOK);
}
// =============================================================================
// = Filter
// =============================================================================

/**
 * Display a Filter Box
 * 
 */
var filterStartInput=false;
var filterFromDetail=false;
function showFilterDialog() {
  function callBack(){
    filterStartInput=false;
    window.top.filterFromDetail=false;
    if (window.top.dijit.byId('dialogDetail').open) {
      window.top.filterFromDetail=true;
      dojo.byId('filterDefaultButtonDiv').style.display='none';
    } else {
      dojo.byId('filterDefaultButtonDiv').style.display='block';
    }
    dojo.style(dijit.byId('idFilterOperator').domNode, {
      visibility : 'hidden'
    });
    dojo.style(dijit.byId('filterValue').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode, {
      display : 'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode, {
      display : 'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) { 
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
        display : 'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterSortValueList').domNode, {
      display : 'none'
    });
    dojo.byId('filterDynamicParameterPane').style.display='none';
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dijit.byId('idFilterAttribute').reset();
    if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) dojo.byId('filterObjectClass').value=dojo.byId('objectClassList').value;
    else if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value) dojo.byId('filterObjectClass').value=dojo.byId('objectClassManual').value;
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) dojo.byId('filterObjectClass').value=dojo.byId('objectClass').value;
    else dojo.byId('filterObjectClass').value=null;
    filterType="";
    var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
    dojo.xhrPost({
      url : "../tool/backupFilter.php?filterObjectClass="
          + dojo.byId('filterObjectClass').value + compUrl,
      handleAs : "text",
      load : function(data, args) {
      }
    });
    compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
    loadContent("../tool/displayFilterClause.php" + compUrl,
    		"listFilterClauses", "dialogFilterForm", false, null, null, null, displayOrOperator);
    loadContent("../tool/displayFilterList.php" + compUrl,
        "listStoredFilters", "dialogFilterForm", false);
    loadContent("../tool/displayFilterSharedList.php" + compUrl,
        "listSharedFilters", "dialogFilterForm", false);
    var objectClass='';
    if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
    else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value && (dojo.byId("objectClassManual").value=='Planning' || dojo.byId("objectClassManual").value=='VersionsPlanning'|| dojo.byId("objectClassManual").value=='ResourcePlanning')) objectClass='Activity';
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
    if (objectClass.substr(0,7)=='Report_') objectClass=objectClass.substr(7);
    refreshListSpecific('object', 'idFilterAttribute', 'objectClass', objectClass);
    dijit.byId("dialogFilter").show();
  }
  loadDialog('dialogFilter', callBack, true, "", true);
}

//ADD qCazelles - Dynamic filter - Ticket #78
function displayOrOperator() {
	if (dojo.byId('nbFilterCriteria').value != "0") {
		dojo.byId('filterLogicalOperator').style.display='block';
    }
}
//END ADD qCazelles - Dynamic filter - Ticket #78

/**
 * Select attribute : refresh dependant lists box
 * 
 */
function filterSelectAtribute(value) {
  if (value) {
    filterStartInput=true;
    if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value', 'off');
    dijit.byId('idFilterAttribute').store.store.fetchItemByIdentity({
      identity : value,
      onItem : function(item) {
        var dataType=dijit.byId('idFilterAttribute').store.store.getValue(
            item, "dataType", "inconnu");
        if(value=="refTypeIncome" || value=="refTypeExpense"){
          dataType="list";
        }
        var datastoreOperator=new dojo.data.ItemFileReadStore({
          url : '../tool/jsonList.php?listType=operator&dataType=' + dataType
        });
        var storeOperator=new dojo.store.DataStore({
          store : datastoreOperator
        });
        storeOperator.query({
          id : "*"
        });
        dijit.byId('idFilterOperator').set('store', storeOperator);
        datastoreOperator.fetch({
          query : {
            id : "*"
          },
          count : 1,
          onItem : function(item) {
            dijit.byId('idFilterOperator').set("value", item.id);
          },
          onError : function(err) {
            console.info(err.message);
          }
        });
        dojo.style(dijit.byId('idFilterOperator').domNode, {
          visibility : 'visible'
        });
        //ADD qCazelles - Dynamic filter - Ticket #78
        dojo.byId('filterDynamicParameterPane').style.display='block';
        if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
        //END ADD qCazelles - Dynamic filter - Ticket #78
        dojo.byId('filterDataType').value=dataType;
        if (dataType == "bool") {
          filterType="bool";
          dojo.style(dijit.byId('filterValue').domNode, {
            display : 'none'
          });
          dojo.style(dijit.byId('filterValueList').domNode, {
            display : 'none'
          });
          if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
          if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
          if (dijit.byId('filterValueCheckboxSwitch')) { 
            dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
              display : 'block'
            });
            dijit.byId('filterValueCheckbox').set('value', 'off');
          } else {
            dojo.style(dijit.byId('filterValueCheckbox').domNode, {
              display : 'block'
            });
            dijit.byId('filterValueCheckbox').set('checked', '');
          }
          dojo.style(dijit.byId('filterValueDate').domNode, {
            display : 'none'
          });
        } else if (dataType == "list") {
          filterType="list";
          var extraUrl="";
          if (value == 'idTargetVersion' || value == 'idTargetProductVersion' || value == 'idOriginalProductVersion') {
            value='idProductVersion';
            extraUrl='&critField=idle&critValue=all';
          } else if (value == 'idTargetComponentVersion' || value == 'idOriginalComponentVersion') {
            value='idComponentVersion';
            extraUrl='&critField=idle&critValue=all';
          }
          var urlListFilter='../tool/jsonList.php?required=true&listType=list&dataType='+value;
          
          //CHANGE qCazelles - Ticket 165 //Empty lists on filter in comboDetail
          //Old
          //if (currentSelectedProject && currentSelectedProject!='' && currentSelectedProject!='*') {
          //New
          if (typeof currentSelectedProject!='undefined' && currentSelectedProject!='' && currentSelectedProject!='*') {
          //END CHANGE qCazelles - Ticket 165
        	if (value=='idActivity') {
              urlListFilter+='&critField=idProjectSub&critValue='+currentSelectedProject;
            } if (value=='idComponent') {
              // noting
            } else {
              urlListFilter+='&critField=idProject&critValue='+currentSelectedProject;
            }
            if (extraUrl=='&critField=idle&critValue=all') {
              extraUrl=='&critField1=idle&critValue1=all';
            }
          }
          if (extraUrl!="") {
            urlListFilter+=extraUrl;
          }  
          var tmpStore=new dojo.data.ItemFileReadStore({
            url : urlListFilter
          });
          var mySelect=dojo.byId("filterValueList");
          mySelect.options.length=0;
          var nbVal=0;
        //ADD aGaye - Ticket 196
          if(dijit.byId('idFilterAttribute').getValue()=="idBusinessFeature"){
	          var listId = "";
	          tmpStore.fetch({
	              query : {
	                id : "*"
	              },
	              onItem : function(item) {
	            	  listId += (listId != "") ? '_' : '';
	                  listId += parseInt(tmpStore.getValue(item, "id", ""), 10) + '';
	                  nbVal++;
	              },
	              onError : function(err) {
	                console.info(err.message);
	              },
	              onComplete : function() { 
	            	  dojo.xhrGet({
	    	        	url : '../tool/getProductNameFromBusinessFeature.php?listId=' + listId,
	    			    handleAs : "text",
	    			    load: function(data){
	    			    	var listName = JSON.parse(data);
	    			    	tmpStore.fetch({
	    			              query : {
	    			                id : "*"
	    			              },
	    			              onItem : function(item) {
	    			                mySelect.options[mySelect.length]=new Option(tmpStore.getValue(item, "name", "") + " (" + listName[tmpStore.getValue(item, "id", "")] + ")", tmpStore.getValue(item, "id", ""));
	    			              },
	    			              onError : function(err) {
	    			                console.info(err.message);
	    			              }
	    			            });
	    			    }
	    	          });
	               }
	            });
          }else{
        	  tmpStore.fetch({
                  query : {
                    id : "*"
                  },
                  onItem : function(item) {
                    mySelect.options[mySelect.length]=new Option(tmpStore.getValue(
                        item, "name", ""), tmpStore.getValue(item, "id", ""));
                    nbVal++;
                  },
                  onError : function(err) {
                    console.info(err.message);
                  }
                });
          }
          //END aGaye - Ticket 196
          mySelect.size=(nbVal > 10) ? 10 : nbVal;
          dojo.style(dijit.byId('filterValue').domNode, {
            display : 'none'
          });
          dojo.style(dijit.byId('filterValueList').domNode, {
            display : 'block'
          });
          if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="8px";
          if (isNewGui) dojo.byId("filterValueListHideTop").style.display="block";
          dojo.style(dijit.byId('showDetailInFilter').domNode, {
            display : 'block'
          });
          dijit.byId('showDetailInFilter').set('value', item.id);
          dijit.byId('filterValueList').reset();
          dojo.style(dijit.byId('filterValueCheckbox').domNode, {
            display : 'none'
          });
          if (dijit.byId('filterValueCheckboxSwitch')) { 
            dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
              display : 'none'
            });
          }
          dojo.style(dijit.byId('filterValueDate').domNode, {
            display : 'none'
          });
        } else if (dataType == "date") {
          filterType="date";
          dojo.style(dijit.byId('filterValue').domNode, {
            display : 'none'
          });
          dojo.style(dijit.byId('filterValueList').domNode, {
            display : 'none'
          });
          if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
          if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
          dojo.style(dijit.byId('showDetailInFilter').domNode, {
            display : 'none'
          });
          dojo.style(dijit.byId('filterValueCheckbox').domNode, {
            display : 'none'
          });
          if (dijit.byId('filterValueCheckboxSwitch')) { 
            dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
              display : 'none'
            });
          }
          dojo.style(dijit.byId('filterValueDate').domNode, {
            display : 'block'
          });
          dijit.byId('filterValueDate').reset();
        } else {
          filterType="text";
          dojo.style(dijit.byId('filterValue').domNode, {
            display : 'block'
          });
          dijit.byId('filterValue').reset();
          dojo.style(dijit.byId('filterValueList').domNode, {
            display : 'none'
          });
          if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
          if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
          dojo.style(dijit.byId('showDetailInFilter').domNode, {
            display : 'none'
          });
          dojo.style(dijit.byId('filterValueCheckbox').domNode, {
            display : 'none'
          });
          if (dijit.byId('filterValueCheckboxSwitch')) { 
            dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
              display : 'none'
            });
          }
          dojo.style(dijit.byId('filterValueDate').domNode, {
            display : 'none'
          });
        }
      },
      onError : function(err) {
        dojo.style(dijit.byId('idFilterOperator').domNode, {
          visibility : 'hidden'
        });
        dojo.style(dijit.byId('filterValue').domNode, {
          display : 'none'
        });
        dojo.style(dijit.byId('filterValueList').domNode, {
          display : 'none'
        });
        if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
        if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
        dojo.style(dijit.byId('showDetailInFilter').domNode, {
          display : 'none'
        });
        dojo.style(dijit.byId('filterValueCheckbox').domNode, {
          display : 'none'
        });
        if (dijit.byId('filterValueCheckboxSwitch')) { 
          dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
            display : 'none'
          });
        }
        dojo.style(dijit.byId('filterValueDate').domNode, {
          display : 'none'
        });
        // hideWait();
      }
    });
    dijit.byId('filterValue').reset();
    dijit.byId('filterValueList').reset();
    dijit.byId('filterValueCheckbox').reset();
    if (dijit.byId('filterValueCheckboxSwitch')) { dijit.byId('filterValueCheckboxSwitch').reset();}
    dijit.byId('filterValueDate').reset();
    
  } else {
    dojo.style(dijit.byId('idFilterOperator').domNode, {
      visibility : 'hidden'
    });
    dojo.style(dijit.byId('filterValue').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode, {
      display : 'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode, {
      display : 'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) { 
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
        display : 'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode, {
      display : 'none'
    });
  }
}

function filterSelectOperator(operator) {
  filterStartInput=true;
  if (operator == "SORT") {
    filterType="SORT";
    dojo.style(dijit.byId('filterValue').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode, {
      display : 'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode, {
      display : 'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) { 
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
        display : 'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterSortValueList').domNode, {
      display : 'block'
    });
  //ADD qCazelles - Dynamic filter - Ticket #78
    dijit.byId('filterDynamicParameter').set('checked', '');
    if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value', 'off');
    dojo.byId('filterDynamicParameterPane').style.display='none';
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    //END ADD qCazelles - Dynamic filter - Ticket #78
  } else if (operator == "<=now+" || operator == ">=now+") {
    filterType="text";
    dojo.style(dijit.byId('filterValue').domNode, {
      display : 'block'
    });
    dojo.style(dijit.byId('filterValueList').domNode, {
      display : 'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode, {
      display : 'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) { 
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
        display : 'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterSortValueList').domNode, {
      display : 'none'
    });
  } else if (operator == "isEmpty" || operator == "isNotEmpty"
      || operator == "hasSome") {
    filterType="null";
    dojo.style(dijit.byId('filterValue').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueList').domNode, {
      display : 'none'
    });
    if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="200px";
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    dojo.style(dijit.byId('showDetailInFilter').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterValueCheckbox').domNode, {
      display : 'none'
    });
    if (dijit.byId('filterValueCheckboxSwitch')) { 
      dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
        display : 'none'
      });
    }
    dojo.style(dijit.byId('filterValueDate').domNode, {
      display : 'none'
    });
    dojo.style(dijit.byId('filterSortValueList').domNode, {
      display : 'none'
    });
    //ADD qCazelles - Dynamic filter - Ticket #78
    dijit.byId('filterDynamicParameter').set('checked', '');
    if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value', 'off');
    dojo.byId('filterDynamicParameterPane').style.display='none';
    if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
    //END ADD qCazelles - Dynamic filter - Ticket #78
  } else {
    dojo.style(dijit.byId('filterValue').domNode, {
      display : 'none'
    });
    dataType=dojo.byId('filterDataType').value;
    dojo.style(dijit.byId('filterSortValueList').domNode, {
      display : 'none'
    });
    if (dataType == "bool") {
      filterType="bool";
      if (dijit.byId('filterValueCheckboxSwitch')) { 
        dojo.style(dijit.byId('filterValueCheckboxSwitch').domNode, {
          display : 'block'
        });
      } else {
        dojo.style(dijit.byId('filterValueCheckbox').domNode, {
          display : 'block'
        });
      }
    } else if (dataType == "list") {
      filterType="list";
      dojo.style(dijit.byId('filterValueList').domNode, {
        display : 'block'
      });
      if (isNewGui) dojo.byId("filterDynamicParameterPane").style.left="8px";
      if (isNewGui) dojo.byId("filterValueListHideTop").style.display="block";
      dojo.style(dijit.byId('showDetailInFilter').domNode, {
        display : 'block'
      });
      //ADD qCazelles - Dynamic filter - Ticket #78
      dijit.byId('filterDynamicParameter').set('checked', '');
      if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value', 'off');
      dojo.byId('filterDynamicParameterPane').style.display='block';
      //END ADD qCazelles - Dynamic filter - Ticket #78
    } else if (dataType == "date") {
      filterType="date";
      dojo.style(dijit.byId('filterValueDate').domNode, {
        display : 'block'
      });
      //ADD qCazelles - Dynamic filter - Ticket #78
      dijit.byId('filterDynamicParameter').set('checked', '');
      if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value', 'off');
      dojo.byId('filterDynamicParameterPane').style.display='block';
      if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
      //END ADD qCazelles - Dynamic filter - Ticket #78
    } else {
      filterType="text";
      dojo.style(dijit.byId('filterValue').domNode, {
        display : 'block'
      });
      //ADD qCazelles - Dynamic filter - Ticket #78
      dijit.byId('filterDynamicParameter').set('checked', '');
      if (dijit.byId('filterDynamicParameterSwitch')) dijit.byId('filterDynamicParameterSwitch').set('value', 'off');
      dojo.byId('filterDynamicParameterPane').style.display='block';
      if (isNewGui) dojo.byId("filterValueListHideTop").style.display="none";
      //END ADD qCazelles - Dynamic filter - Ticket #78
    }
  }
}

/**
 * Save filter clause
 * 
 */
function addfilterClause(silent) {
  filterStartInput=false;
  if (dijit.byId('filterNameDisplay')) {
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
  }
  if (filterType == "") {
    if (!silent)
      showAlert(i18n('attributeNotSelected'));
    return;
  }
  if (trim(dijit.byId('idFilterOperator').get('value')) == '') {
    if (!silent)
      showAlert(i18n('operatorNotSelected'));
    return;
  }
  //ADD qCazelles - Dynamic filter - Ticket #78
  if (!dijit.byId('filterDynamicParameter').get('checked')) {
  //END ADD qCazelles - Dynamic filter - Ticket #78
	  if (filterType == "list"
	      && trim(dijit.byId('filterValueList').get('value')) == '') {
	    if (!silent)
	      showAlert(i18n('valueNotSelected'));
	    return;
	  }
	  if (filterType == "date" && !dijit.byId('filterValueDate').get('value')) {
	    if (!silent)
	      showAlert(i18n('valueNotSelected'));
	    return;
	  }
	  if (filterType == "text" && !dijit.byId('filterValue').get('value')) {
	    if (!silent)
	      showAlert(i18n('valueNotSelected'));
	    return;
	  }
	  if (dijit.byId('idFilterAttribute').get('value')=='idle' 
	    && dijit.byId('idFilterOperator').get('value')=='='
	    && dijit.byId('filterValueCheckbox').get('checked')) {
	    dijit.byId('listShowIdle').set('checked',true);
	  }
  //ADD qCazelles - Dynamic filter - Ticket #78
  }
  //END ADD qCazelles - Dynamic filter - Ticket #78
  // Add controls on operator and value
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  loadContent("../tool/addFilterClause.php" + compUrl, "listFilterClauses",
      "dialogFilterForm", false,null,null,null,function(){clearDivDelayed('saveFilterResult');});
  // dijit.byId('filterNameDisplay').set('value',null);
  // dojo.byId('filterName').value=null;
  
  //ADD qCazelles - Dynamic filter - Ticket #78
  if (dojo.byId('filterLogicalOperator') && dojo.byId('filterLogicalOperator').style.display=='none') {
	  dojo.byId('filterLogicalOperator').style.display='block';
  }
  //END ADD qCazelles - Dynamic filter - Ticket #78
	  
}

/**
 * Remove a filter clause
 * 
 */
function removefilterClause(id) {
  if (dijit.byId('filterNameDisplay')) {
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get(
        'value');
  }
  // Add controls on operator and value
  dojo.byId("filterClauseId").value=id;
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  loadContent("../tool/removeFilterClause.php" + compUrl,
      "listFilterClauses", "dialogFilterForm", false);
  // dijit.byId('filterNameDisplay').set('value',null);
  // dojo.byId('filterName').value=null;
  
  //ADD qCazelles - Dynamic filter - Ticket #78
  if (id=='all' || dojo.byId('nbFilterCriteria').value == "1") { //Value is not set to 0 already but is going to
	dojo.byId('filterLogicalOperator').style.display='none';
  }
  else if (dojo.byId('nbFilterCriteria').value == "2") { //Value is going to be set at 1
	  loadContent("../tool/displayFilterClause.php" + compUrl,
		"listFilterClauses", "dialogFilterForm", false,null,null,null,function(){clearDivDelayed('saveFilterResult');});
  }
  //END ADD qCazelles - Dynamic filter - Ticket #78
}

/**
 * Action on OK for filter
 * 
 */

function selectFilter() {
  if (filterStartInput) {
    addfilterClause(true);
    setTimeout("selectFilterContinue();", 1000);
  } else {
    selectFilterContinue();
  }
}

function selectFilterContinue() {
  if (window.top.dijit.byId('dialogDetail').open) {
    var doc=window.top.frames['comboDetailFrame'];
  } else {
    var doc=window.top;
  }
  if (dijit.byId('filterNameDisplay')) {
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
  }
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  dojo.xhrPost({
    url : "../tool/backupFilter.php?valid=true" + compUrl,
    form : 'dialogFilterForm',
    handleAs : "text",
    load : function(data, args) {}
  });
  if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value){
    objectClass=dojo.byId('objectClassList').value;
  }else if (! window.top.dijit.byId('dialogDetail').open && dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value){ 
    objectClass=dojo.byId("objectClassManual").value;
  }else if (dojo.byId('objectClass') && dojo.byId('objectClass').value){
    objectClass=dojo.byId('objectClass').value;
  } 
  if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value=='Kanban') {
    compUrl+='&context=directFilterList';
    compUrl+='&contentLoad=../view/kanbanView.php';
    compUrl+='&container=divKanbanContainer';
  }
  doc.loadContent(
     "../tool/displayFilterList.php?displayQuickFilter=true&context=directFilterList&filterObjectClass="
         + objectClass + compUrl, "directFilterList", null,
    false, 'returnFromFilter', false);
  /*
   * florent 
   *  Ticket #4010  
   * When adding filter (not stored), icon has not the "on" flag
   */
   if(dojo.byId("nbFilterCriteria").value > 0 && !dijit.byId('filterDynamicParameter').get("checked") && dojo.byId('nbDynamicFilterCriteria').value==0) {
     setTimeout("dijit.byId('listFilterFilter').set('iconClass', 'dijitButtonIcon iconActiveFilter')",500);
   }else{
     setTimeout("dijit.byId('listFilterFilter').set('iconClass', 'dijitButtonIcon iconFilter')",500);
   }
  if(! window.top.dijit.byId('dialogDetail').open && dojo.byId('objectClassManual') && (dojo.byId('objectClassManual').value=='Kanban' || dojo.byId('objectClassManual').value=='LiveMeeting')){
    loadContent("../view/kanbanView.php?idKanban="+dojo.byId('idKanban').value, "divKanbanContainer");
  }else if (!dijit.byId('filterDynamicParameter').get("checked")) {
    if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value=='Planning' && ! window.top.dijit.byId('dialogDetail').open) {
        refreshJsonPlanning();
    }else if(dojo.byId("objectClassManual") &&  (dojo.byId("objectClassManual").value=='VersionsPlanning' || dojo.byId("objectClassManual").value=='ResourcePlanning') && ! window.top.dijit.byId('dialogDetail').open){
      if(dojo.byId("objectClassManual").value=='VersionsPlanning'){
        refreshJsonPlanning('version');
      }else{
        refreshJsonPlanning('resource');
      }
    }else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value=='Report') {
      dojo.byId('outMode').value='';runReport();
    } else {
      doc.refreshJsonList(objectClass);
    }
  }
  dijit.byId("dialogFilter").hide();
  filterStartInput=false;
}

/**
 * Action on Cancel for filter
 * 
 */
function cancelFilter() {
  filterStartInput=true;
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  dojo.xhrPost({
    url : "../tool/backupFilter.php?cancel=true" + compUrl,
    form : 'dialogFilterForm',
    handleAs : "text",
    load : function(data, args) {
    }
  });
  dijit.byId('dialogFilter').hide();
}

/**
 * Action on Clear for filter
 * 
 */
function clearFilter() {
  if (dijit.byId('filterNameDisplay')) {
    dijit.byId('filterNameDisplay').reset();
  }
  dojo.byId('filterName').value="";
  removefilterClause('all');
  // setTimeout("selectFilter();dijit.byId('listFilterFilter').set('iconClass','dijitButtonIcon iconFilter');",100);
  dijit.byId('listFilterFilter').set('iconClass', 'dijitButtonIcon iconFilter');
  dijit.byId('filterNameDisplay').set('value', null);
  dojo.byId('filterName').value=null;
}

/**
 * Action on Default for filter
 * 
 */
function defaultFilter() {
  if (dijit.byId('filterNameDisplay')) {
    // if (dijit.byId('filterNameDisplay').get('value')=="") {
    // showAlert(i18n("messageMandatory", new Array(i18n("filterName")) ));
    // return;
    // }
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get(
        'value');
  }
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  loadContent("../tool/defaultFilter.php" + compUrl, "listStoredFilters",
      "dialogFilterForm", false,null,null,null,function(){clearDivDelayed('saveFilterResult');});
}

/**
 * Save a filter as a stored filter
 * 
 */
function saveFilter() {
  if (dijit.byId('filterNameDisplay')) {
    if (dijit.byId('filterNameDisplay').get('value') == "") {
      showAlert(i18n("messageMandatory", new Array(i18n("filterName"))));
      return;
    }
    dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get(
        'value');
  }
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
  loadContent("../tool/saveFilter.php" + compUrl, "listStoredFilters",
      "dialogFilterForm", false,null,null,null,function(){clearDivDelayed('saveFilterResult');});
}
clearDivDelayedTimeout=[];
function clearDivDelayed(divName,delay) {
  if (clearDivDelayedTimeout[divName]) clearTimeout(clearDivDelayedTimeout[divName]);
  if (!divName) return;
  if (!delay) delay=2000;
  clearDivDelayedTimeout[divName]=setTimeout("if (dojo.byId('"+divName+"')) dojo.byId('"+divName+"').innerHTML='';",delay);
}
/**
 * Select a stored filter in the list and fetch criteria
 * 
 */
var globalSelectFilterContenLoad=null;
var globalSelectFilterContainer=null;
function selectStoredFilter(idFilter, context, contentLoad, container) {  
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  globalSelectFilterContenLoad=null;
  globalSelectFilterContainer=null;
  if (context == 'directFilterList') {
    if (dojo.byId('noFilterSelected')) {
      if (idFilter == '0') {
        dojo.byId('noFilterSelected').value='true';
      } else {
        dojo.byId('noFilterSelected').value='false';
      }
    } else if (window.top.dojo.byId('noFilterSelected')) {
    	if (idFilter == '0') {
    	  window.top.dojo.byId('noFilterSelected').value='true';
      } else {
        window.top.dojo.byId('noFilterSelected').value='false';
      }
    }
    if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
    else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value) objectClass=dojo.byId("objectClassManual").value;
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
    var validationType=null;
    if (dojo.byId('dynamicFilterId'+idFilter)) {  		
  		var param="&idFilter="+idFilter+"&filterObjectClass="+objectClass;
  		loadDialog('dialogDynamicFilter', null, true, param, true);
  		globalSelectFilterContenLoad=contentLoad;
  		globalSelectFilterContainer=container;
  		validationType='selectFilter'; // will avoid immediate refresh
  	}
    if(typeof contentLoad != 'undefined' && typeof container != 'undefined'){
      loadContent("../tool/selectStoredFilter.php?idFilter=" + idFilter
          + "&context=" + context + "&contentLoad="+contentLoad+"&container="+container+"&filterObjectClass="
          + objectClass + compUrl, "directFilterList", null,false,validationType);
      if (!dojo.byId('dynamicFilterId'+idFilter)) loadContent(contentLoad, container);
    }else{
      loadContent("../tool/selectStoredFilter.php?idFilter=" + idFilter
          + "&context=" + context + "&filterObjectClass="
          + objectClass + compUrl, "directFilterList", null,false,validationType);
      if (dojo.byId("objectClassList") && dojo.byId("objectClassList").value.substr(0,7)=='Report_') {
        dojo.byId('outMode').value='';
        runReport();
      }
    }
    if(isNewGui){
      dijit.byId('listFilterFilter').closeDropDown();
    }
  } else {
	  if (dojo.byId('filterLogicalOperator') && dojo.byId('filterLogicalOperator').style.display=='none') {
		  	dojo.byId('filterLogicalOperator').style.display='block';
	  }
    loadContent(
        "../tool/selectStoredFilter.php?idFilter=" + idFilter + compUrl,
        "listFilterClauses", "dialogFilterForm", false);
  }

}

/**
 * Removes a stored filter from the list
 * 
 */
function removeStoredFilter(idFilter, nameFilter) {
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  var action=function() {
    loadContent("../tool/removeFilter.php?idFilter=" + idFilter + compUrl,
        "listStoredFilters", "dialogFilterForm", false,null,null,null,function(){clearDivDelayed('saveFilterResult');});
  };
  window.top.showConfirm(i18n("confirmRemoveFilter", new Array(nameFilter)), action);
}

/**
 * Share a stored filter from the list
 * 
 */
function shareStoredFilter(idFilter, nameFilter) {
  var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '&comboDetail=true' : '';
  loadContent("../tool/shareFilter.php?idFilter=" + idFilter + compUrl,
        "listStoredFilters", "dialogFilterForm", false);
}

//ADD qCazelles - Dynamic filter - Ticket #78
function selectDynamicFilter() {
	
	for (var i = 0; i < dojo.byId('nbDynamicFilterClauses').value; i++) {
		if (dijit.byId('filterValueList' + i)) {
			if (dijit.byId('filterValueList' + i).get("value")=="") {
				showAlert(i18n('valueNotSelected'));
				return;
			}
		}
		else if (dijit.byId('filterValue' + i)) {
			if (dijit.byId('filterValue' + i).get("value")=="") {
				showAlert(i18n('valueNotSelected'));
				return;
			}
		}
		else if (dijit.byId('filterValueDate' + i)) {
			if (dijit.byId('filterValueDate' + i).get("value")=="") {
				showAlert(i18n('valueNotSelected'));
				return;
			}
		}
	}
	
	var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
	var callBack=function() {selectDynamicFilterContinue();}
	loadContent("../tool/addDynamicFilterClause.php" + compUrl, "listDynamicFilterClauses",
		      "dialogDynamicFilterForm", false,null,null,null,callBack);
}

function selectDynamicFilterContinue() {
	  if (window.top.dijit.byId('dialogDetail').open) {
	    var doc=window.top.frames['comboDetailFrame'];
	  } else {  
	    var doc=top;
	  }
	  if (dijit.byId('filterNameDisplay')) {
		  dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
	  }
	  doc.dijit.byId("listFilterFilter").set("iconClass", "dijitButtonIcon iconActiveFilter");
	  if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value) objectClass=dojo.byId('objectClassList').value;
	  else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value) objectClass=dojo.byId("objectClassManual").value;
    else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) objectClass=dojo.byId('objectClass').value;
	  var compUrl='';
	  if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value=='Kanban') {
	    compUrl+='&context=directFilterList';
	    compUrl+='&contentLoad=../view/kanbanView.php';
	    compUrl+='&container=divKanbanContainer';
	  }
    doc.loadContent(
        "../tool/displayFilterList.php?context=directFilterList&displayQuickFilter=true&displayQuickFilter=true&filterObjectClass="
            + objectClass+compUrl, "directFilterList", null,
        false, 'returnFromFilter', false);
    
	  if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value=='Planning' && ! window.top.dijit.byId('dialogDetail').open) {
      refreshJsonPlanning();
	  } else if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value=='Report') {
	    dojo.byId('outMode').value='';runReport();
	  } else if (doc.dojo.byId('objectClassList')) {
      doc.refreshJsonList(doc.dojo.byId('objectClassList').value);
	  } else {
	    doc.refreshJsonList(doc.dojo.byId('objectClass').value);
    }
	  dijit.byId("dialogDynamicFilter").hide();
}
//END ADD qCazelles - Dynamic filter - Ticket #78

// =============================================================================
// = Reports
// =============================================================================

function reportSelectCategory(idCateg) {
  if (isNaN(idCateg)) return;
  loadContent("../view/reportsParameters.php?idReport=", "reportParametersDiv",
      null, false);
  var tmpStore=new dojo.data.ItemFileReadStore(
      {
        url : '../tool/jsonList.php?required=true&listType=list&dataType=idReport&critField=idReportCategory&critValue='
            + idCateg
      });
  var mySelectWidget=dijit.byId("reportsList");
  mySelectWidget.reset();
  var mySelect=dojo.byId("reportsList");
  mySelect.options.length=0;
  var nbVal=0;
  tmpStore.fetch({
    query : {
      id : "*"
    },
    onItem : function(item) {
      mySelect.options[mySelect.length]=new Option(tmpStore.getValue(item,
          "name", ""), tmpStore.getValue(item, "id", ""));
      nbVal++;
    },
    onError : function(err) {
      console.info(err.message);
    }
  });
}

function reportSelectReport(idReport) {
  if (isNaN(idReport)) return;
  dojo.query(".section").removeClass("reportSelected");
  dojo.addClass(dojo.byId('report'+idReport),"reportSelected");
  var height=dojo.byId('mainReportContainer').offsetHeight;
  dijit.byId('listReportDiv').resize({h:height});
  dijit.byId('mainReportContainer').resize();
  loadContent("../view/reportsParameters.php?idReport=" + idReport,
  "reportParametersDiv", null, false);
  //mehdi Ticket #3092
  detailReportDiv.innerHTML = ""; 
  }

// =============================================================================
// = Resource Cost
// =============================================================================

function addResourceCost(idResource, idRole, funcList) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    affectationLoad=true;
  dojo.byId("resourceCostId").value="";
  dojo.byId("resourceCostIdResource").value=idResource;
  dojo.byId("resourceCostFunctionList").value=funcList;
  dijit.byId("resourceCostIdRole").set('readOnly', false);
  if (idRole) {
    dijit.byId("resourceCostIdRole").set('value', idRole);
  } else {
    dijit.byId("resourceCostIdRole").reset();
  }
  dijit.byId("resourceCostValue").reset('value');
  dijit.byId("resourceCostStartDate").set('value', null);
  resourceCostUpdateRole();
    dijit.byId("dialogResourceCost").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&idResource="+idResource;
  params+="&funcList="+funcList;
  params+="&idRole="+idRole;
  params+="&mode=add";
  loadDialog('dialogResourceCost',callBack,true,params);
}

function removeResourceCost(id, idRole, nameRole, startDate) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&idResource="+dijit.byId('id').get("value");
  params+="&funcList=";
  params+="&idRole="+idRole;
  params+="&mode=delete";
  var callBack=function(){dojo.byId("resourceCostId").value=id;}
  loadDialog('dialogResourceCost',callBack,false,params,false); // Ticket #3584 : be sure dialog has been loaded at least once
  actionOK=function() {
    
    loadContent("../tool/removeResourceCost.php", "resultDivMain",
        "resourceCostForm", true, 'resourceCost');
  };
  msg=i18n('confirmDeleteResourceCost', new Array(nameRole, startDate));
  showConfirm(msg, actionOK);
}

reourceCostLoad=false;
function editResourceCost(id, idResource, idRole, cost, startDate, endDate) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack=function() {
    dojo.byId("resourceCostId").value=id;
    dojo.byId("resourceCostIdResource").value=idResource;
    dijit.byId("resourceCostIdRole").set('readOnly', true);
    dijit.byId("resourceCostValue").set('value', dojo.number.format(cost / 100));
    var dateStartDate=getDate(startDate);
    dijit.byId("resourceCostStartDate").set('value', dateStartDate);
    dijit.byId("resourceCostStartDate").set('disabled', true);
    dijit.byId("resourceCostStartDate").set('required', 'false');
    reourceCostLoad=true;
    dijit.byId("resourceCostIdRole").set('value', idRole);
    setTimeout('reourceCostLoad=false;', 300);
    dijit.byId("dialogResourceCost").show();
  };
  loadDialog('dialogResourceCost',callBack,true,null);
}

function saveResourceCost() {
  var formVar=dijit.byId('resourceCostForm');
  if (formVar.validate()) {
    loadContent("../tool/saveResourceCost.php", "resultDivMain",
        "resourceCostForm", true, 'resourceCost');
    dijit.byId('dialogResourceCost').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function resourceCostUpdateRole() {
  if (reourceCostLoad) {
    return;
  }
  if (dijit.byId("resourceCostIdRole").get('value') ) {
    dojo.xhrGet({
      url : '../tool/getSingleData.php?dataType=resourceCostDefault&idRole=' + dijit.byId("resourceCostIdRole").get('value'),
      handleAs : "text",
      load : function(data) {
        dijit.byId('resourceCostValue').set('value', dojo.number.format(data));
      }
    });
  }
  var funcList=dojo.byId('resourceCostFunctionList').value;
  $key='#' + dijit.byId("resourceCostIdRole").get('value') + '#';
  if (funcList.indexOf($key) >= 0) {
    dijit.byId("resourceCostStartDate").set('disabled', false);
    dijit.byId("resourceCostStartDate").set('required', 'true');
  } else {
    dijit.byId("resourceCostStartDate").set('disabled', true);
    dijit.byId("resourceCostStartDate").set('value', null);
    dijit.byId("resourceCostStartDate").set('required', 'false');
  }
}

// =============================================================================
// = Version Project
// =============================================================================

function addVersionProject(idVersion, idProject) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  params="&idVersionProject=&idVersion="+idVersion+"&idProject="+idProject;
  loadDialog('dialogVersionProject', null, true, params, true);
  }

function removeVersionProject(id) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeVersionProject.php?idVersionProject="+id, "resultDivMain", null, true, 'versionProject');
  };
  msg=i18n('confirmDeleteVersionProject');
  showConfirm(msg, actionOK);
}
;
function editVersionProject(id, idVersion, idProject) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  params="&idVersionProject="+id+"&idVersion="+idVersion+"&idProject="+idProject;
  loadDialog('dialogVersionProject', null, true, params, true);
}

function saveVersionProject() {
  var formVar=dijit.byId('versionProjectForm');
  if (formVar.validate()) {
    loadContent("../tool/saveVersionProject.php", "resultDivMain",
        "versionProjectForm", true, 'versionProject');
    dijit.byId('dialogVersionProject').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

// =============================================================================
// = Product Project
// =============================================================================

function addProductProject(idProduct, idProject) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  params="&idProductProject=&idProduct="+idProduct+"&idProject="+idProject;
  loadDialog('dialogProductProject', null, true, params, true);
}

function removeProductProject(id) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeProductProject.php?idProductProject="+id, "resultDivMain", null, true, 'productProject');
  };
  msg=i18n('confirmDeleteProductProject');
  showConfirm(msg, actionOK);
}

function editProductProject(id, idProduct, idProject) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  params="&idProductProject="+id+"&idProduct="+idProduct+"&idProject="+idProject;
  loadDialog('dialogProductProject', null, true, params, true);
}

function saveProductProject() {
  var formVar=dijit.byId('productProjectForm');
  if (formVar.validate()) {
    loadContent("../tool/saveProductProject.php", "resultDivMain",
        "productProjectForm", true, 'productProject');
    dijit.byId('dialogProductProject').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

//=============================================================================
//= Test Case Run
//=============================================================================

function addTestCaseRun() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
   //disableWidget('dialogTestCaseRunSubmit');  
  var params="&testSessionId="+dijit.byId('id').get('value');
  loadDialog('dialogTestCaseRun', null, true, params);
}

function refreshTestCaseRunList(selected) {
  disableWidget('dialogTestCaseRunSubmit');
  var url='../tool/dynamicListTestCase.php';
  url+='?idProject='+dijit.byId('idProject').get('value');
  if (dijit.byId('idProduct')) url+='&idProduct='+dijit.byId('idProduct').get('value');
  else if (dijit.byId('idProductOrComponent')) url+='&idProduct='+dijit.byId('idProductOrComponent').get('value');
  else if (dijit.byId('idComponent')) url+='&idComponent='+dijit.byId('idComponent').get('value');
  if (selected) {
    url+='&selected=' + selected;
  }
  loadContent(url, 'testCaseRunListDiv', 'testCaseRunForm', false);
}

function editTestCaseRun(testCaseRunId, idRunStatus, callback) {
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
    return;
  }
  var testSessionId = dijit.byId('id').get('value');
  var params="&testCaseRunId=" + testCaseRunId + "&testSessionId=" + testSessionId;
  if (idRunStatus) params+="&runStatusId="+idRunStatus;
  loadDialog('dialogTestCaseRun', callback, ((callback)?false:true), params);
}

function passedTestCaseRun(idTestCaseRun) {
  var callback=function() { 
    if (saveTestCaseRun()) dijit.byId('dialogTestCaseRun').hide();
  };
  editTestCaseRun(idTestCaseRun, '2', callback);
}

function failedTestCaseRun(idTestCaseRun) {
  editTestCaseRun(idTestCaseRun, '3', null);
}

function blockedTestCaseRun(idTestCaseRun) {
  var callback=function() { 
    if (saveTestCaseRun()) dijit.byId('dialogTestCaseRun').hide();
  };
  editTestCaseRun(idTestCaseRun, '4', callback);
}

function testCaseRunChangeStatus() {
  var status=dijit.byId('testCaseRunStatus').get('value');
  if (status == '3') {
   dojo.byId('testCaseRunTicketDiv').style.display="block";
  } else {
   if (!trim(dijit.byId('testCaseRunTicket').get('value'))) {
     dojo.byId('testCaseRunTicketDiv').style.display="none";
   } else {
     dojo.byId('testCaseRunTicketDiv').style.display="block";
   }
  }
}

function removeTestCaseRun(id, idTestCase) {
  formInitialize();
  if (! dojo.byId("testCaseRunId")) {
    var callBack=function() {
      if (dijit.byId('dialogAlert')) {
        dijit.byId('dialogAlert').hide();
      }
      removeTestCaseRun(id, idTestCase);  
    }
    loadDialog('dialogTestCaseRun', callBack, false);
  }
  if (checkFormChangeInProgress()) {
   showAlert(i18n('alertOngoingChange'));
   return;
  }
  dojo.byId("testCaseRunId").value=id;
  actionOK=function() {
   loadContent("../tool/removeTestCaseRun.php", "resultDivMain",
       "testCaseRunForm", true, 'testCaseRun');
  };
  msg=i18n('confirmDeleteTestCaseRun', new Array(idTestCase));
  showConfirm(msg, actionOK);
}

function saveTestCaseRun() {
  var formVar=dijit.byId('testCaseRunForm');
  var mode=dojo.byId("testCaseRunMode").value;
  if ( (mode == 'add'  && dojo.byId("testCaseRunTestCaseList").value == "") 
    || (mode == 'edit' && dojo.byId("testCaseRunTestCase").value == "" ) )
   return ;
  if (mode == 'edit') {
   var status=dijit.byId('testCaseRunStatus').get('value');
   if (status == '3') {
     if (trim(dijit.byId('testCaseRunTicket').get('value')) == '') {
       dijit.byId("dialogTestCaseRun").show();
       showAlert(i18n('messageMandatory', new Array(i18n('colTicket'))));
       return;
     }
   }
  }
  if (formVar.validate()) {
   loadContent("../tool/saveTestCaseRun.php", "resultDivMain", "testCaseRunForm", true, 'testCaseRun');
   dijit.byId('dialogTestCaseRun').hide();
   return true;
  } else {
   dijit.byId("dialogTestCaseRun").show();
   showAlert(i18n("alertInvalidForm"));
   return false;
  }
}
//gautier complexity
function saveComplexity(id,idZone) {
  var value=dijit.byId("complexity"+idZone).get("value");
  var url = '../tool/saveComplexity.php?idCatalog='+id +'&name='+value+'&idZone='+idZone;
  dojo.xhrPut({
    url : url,
    form : 'objectForm',
    handleAs : "text",
    load : function(data) {
      if(data){
        dijit.byId("complexity"+idZone).set("value",data);
        showAlert(i18n("cantDeleteUsingUOComplexity"));
      }else{
        loadContent("objectDetail.php?refreshComplexitiesValues=true", "CatalogUO_unitOfWork", 'listForm');
      }
    }
  });
  
}

//gautier #1716
function saveTcrData(id,textZone) {
  var value=dijit.byId("tcr"+textZone+"_"+id).get("value");
  var url = '../tool/saveTcrData.php?idTcr='+id +'&zone='+textZone +'&valueZone='+value;
  dojo.xhrPut({
    url : url,
    form : 'objectForm',
    handleAs : "text",
    load : function(data) {
     //Display saved message
      addMessage(i18n("col"+textZone)+" "+i18n("resultSave"));
      document.getElementById('idImage'+textZone+id).style.display="block";
      setTimeout("dojo.byId('idImage"+textZone+id+"').style.display='none';", 1000);
      }
  });
}

//Mehdi 
function assUpdateLeftWork(id) {
  var initAss =dojo.byId('initAss_'+id).value;
  var assign=dijit.byId("assAssignedWork_"+id).get('value');
  var newAss = assign;
  if (newAss == null || isNaN(newAss)) {
	  newAss=0;
	  dijit.byId("assAssignedWork_"+id).set('value',0);
  }
  var leftWork = dijit.byId('assLeftWork_'+id).get("value");
  var diff = (newAss)-(initAss);
  var newLeft=leftWork + diff;
  if (newLeft < 0 || isNaN(newLeft)) {
    newLeft=0;
  }
  // update assigned for PlanningElement
  var objClass=dojo.byId('objectClass').value;
  var assPeAss=dijit.byId(objClass+'PlanningElement_assignedWork');
  if(assPeAss){
    assPeAss.set("value", assPeAss.get("value") + diff);
  }
  //
  dijit.byId('assLeftWork_'+id).set("value",newLeft); // Will trigger the saveLeftWork() function
  dojo.byId('initAss_'+id).value = newAss;
  diff = 0;
  dojo.byId(objClass+'PlanningElement_assignedCost').style.textDecoration="line-through";
}
function assUpdateLeftWorkDirect(id) {
  var initLeft=dojo.byId('initLeft_'+id).value;
  var left=dijit.byId("assLeftWork_"+id).get('value');
  if (left == null || isNaN(left)) {
    left=0;
  }
  var diff = (left)-(initLeft);
  // update left for PlanningElement
  var objClass=dojo.byId('objectClass').value;
  var assPeLeft=dijit.byId(objClass+'PlanningElement_leftWork');
  if(assPeLeft){
    assPeLeft.set("value", assPeLeft.get("value") + diff);
  }
  var assPePlanned=dijit.byId(objClass+'PlanningElement_plannedWork');
  if(assPePlanned){
    assPePlanned.set("value", assPePlanned.get("value") + diff);
  }
  //
  dojo.byId('initLeft_'+id).value=left;
  diff = 0;
  dojo.byId(objClass+'PlanningElement_leftCost').style.textDecoration="line-through";
}
  
function saveAssignedWork(id, zone) {
  var value=dijit.byId("ass"+zone+"_"+id).get("value");
  var objClass=dojo.byId('objectClass').value;
  var url = '../tool/saveLeftWork.php?idAssign='+id +'&zone='+zone +'&valueTextZone='+value;
  dojo.xhrPut({
	url : url,
	form : 'objectForm',
	handleAs : "text",
	load : function(data) {
	  addMessage(i18n("col"+zone)+" "+i18n("resultSave"));
	  document.getElementById('idImage'+zone+id).style.display="none";
	  setTimeout("dojo.byId('idImage"+zone+id+"').style.display='block';", 1000);
	}
  });
}

function saveLeftWork(id, zone) {
  var value=dijit.byId("ass"+zone+"_"+id).get("value");
  if(isNaN(value) || value==null){
    value=0;
    dijit.byId("ass"+zone+"_"+id).set("value",0);
  }
  // update left and planned for PlanningElement
  var initLeft =dojo.byId('initLeft_'+id).value;
  var objClass=dojo.byId('objectClass').value;
  var assPeLeft=dijit.byId(objClass+'PlanningElement_leftWork');
  var assPePlan=dijit.byId(objClass+'PlanningElement_plannedWork');
  var diff=value-initLeft;
  if(assPeLeft){
    assPeLeft.set("value", assPeLeft.get("value") + diff);
  }
  if(assPePlan){
    assPePlan.set("value", assPePlan.get("value") + diff);
  }
	//
	var url = '../tool/saveLeftWork.php?idAssign='+id +'&zone='+zone +'&valueTextZone='+value;
	dojo.xhrPut({
	  url : url,
	  form : 'objectForm',
	  handleAs : "text",
	  load : function(data) {
		  addMessage(i18n("col"+zone)+" "+i18n("resultSave"));
	    document.getElementById('idImage'+zone+id).style.display="block";
	    setTimeout("dojo.byId('idImage"+zone+id+"').style.display='none';", 1000);
	    var objClass=dojo.byId('objectClass').value;
	    if (data) {
	      dijit.byId(objClass+'PlanningElement_realEndDate').set('value',data);
	    } else {
	      dijit.byId(objClass+'PlanningElement_realEndDate').set('value',null);
	    }
	  }
	});
	dojo.byId('initLeft_'+id).value=value;
	dojo.byId(objClass+'PlanningElement_leftCost').style.textDecoration="line-through";
	dojo.byId(objClass+'PlanningElement_plannedCost').style.textDecoration="line-through";
}

// ADD BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT - ADD-EDIT-REMOVE
// =============================================================================
// = Add-Edit-Remove an organization's Budget Element
// =============================================================================
/**
 * Add a budgetElement
 * @param objectClassName     : The class name on witch the edit is done
 * @param refId               : The RefId of the MainClass
 * @param id                  : The id of the budgetElement = 0
 * @param year                : The year of the budgetElement to add
 * @param scope               : The scope (For organization : 'organization'
 */
function addBudgetElement(objectClassName, refId, id, year, scope) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  params='&objectClass='+objectClassName;
  params+='&action=ADD';
  params+='&refId='+refId;
  params+='&id='+id;
  params+='&year='+year;
  params+='&scope='+scope;
  loadDialog('dialogAddChangeBudgetElement', null, true, params, true, true, 'addBudgetElement');
  }

/**
 * Change values of a budgetElement
 * @param objectClassName     : The class name on witch the edit is done
 * @param refId               : The id of the MainClass
 * @param id                  : The id on witch the edit is done
 * @param year                : The year of the budgetElement (for showing)
 * @param budgetWork          : The budget Work to edit
 * @param budgetCost          : The budget Cost to edit
 * @param budgetExpenseAmount : The budget element's expense Amount to edit
 */
function changeBudgetElement(objectClassName, refId, id, year, budgetWork, budgetCost, budgetExpenseAmount) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  params='&objectClass='+objectClassName;
  params+='&action=CHANGE';
  params+='&refId='+refId;
  params+='&id='+id;
  params+='&year='+year;
  params+='&budgetWork='+budgetWork;
  params+='&budgetCost='+budgetCost;
  params+='&budgetExpenseAmount='+budgetExpenseAmount;
    
  loadDialog('dialogAddChangeBudgetElement', null, true, params, false, true, 'changeBudgetElement');        
}

/**
 * Add or save a budgetElement
 * After calling the dialog dialogAddChangeBudgetElement
 */
//gautier #4360
function saveOrganizationBudgetElement() {
//loadContent("../tool/saveOrganizationBudgetElement.php", "detailDiv", "addChangeBudgetElementForm");
  loadContent("../tool/saveOrganizationBudgetElement.php", "resultDivMain", "addChangeBudgetElementForm", true);
  dijit.byId('dialogAddChangeBudgetElement').hide();
showWait();
}

/**
 * Close or unclose a budgetElement
 * @param objectClassName     : The class name on witch the edit is done
 * @param refId               : The id of main Class
 * @param id                  : The id of a budget element
 * @param idle                : The value of idle  - If 0, setting to 1
 * @param year                : The period year
 */
function closeUncloseBudgetElement(objectClassName, refId, id, idle, year) {
  var param="?objectClassName="+objectClassName;
  param+="&refId="+refId;
  param+="&budgetElementId="+id;
  param+="&idle="+idle;
  param+="&year="+year;

if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }

  if(idle==0) {
      msg=i18n('confirmCloseBudgetElement');
  } else {
      msg=i18n('confirmUncloseBudgetElement');  
  }
      

  actionOK=function() {
    loadContent("../tool/closeUncloseOrganizationBudgetElement.php"+param, "detailDiv","");
  };

  showConfirm(msg, actionOK);
}

/**
 * Delete a budgetElement
 * @param objectClassName     : The class name on witch the edit is done
 * @param refId               : The id of main Class
 * @param id                  : The id of the budgetElement to delete
 * @param year                : The period year
 */
function removeBudgetElement(objectClassName, refId, id, year) {
  var param="?objectClassName="+objectClassName;
  param+="&refId="+refId;
  param+="&budgetElementId="+id;
  param+="&year="+year;

if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }

  actionOK=function() {
    loadContent("../tool/removeOrganizationBudgetElement.php"+param, "detailDiv","");
  };

  msg=i18n('confirmRemoveBudgetElement');
  showConfirm(msg, actionOK);

}

// END ADD BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT - ADD-EDIT-REMOVE


// ADD BY Marc TABARY - 2017-02-23 - LIST - ADD - REMOVE OBJECTS LINKED BY ID TO MAIN OBJECT
// =============================================================================
// = Linked Object by id to main object
// =============================================================================
function addLinkObjectToObject(mainObjectClassName, idOfInstanceOfMainClass, linkObjectClassName) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  // empty select of previous options
  if (typeof dojo.byId('linkedObjectId') !== 'undefined') {
    var node = document.getElementById("linkedObjectId");
    while (node.firstChild) node.removeChild(node.firstChild);
  }
//  dijit.byId("dialogObject").hide();
  refreshLinkObjectList(0,mainObjectClassName, linkObjectClassName);
  dijit.byId("dialogObject").show();

  dojo.byId("mainObjectClass").value = mainObjectClassName;
  dojo.byId("idInstanceOfMainClass").value = idOfInstanceOfMainClass;
  dojo.byId("linkObjectClassName").value = linkObjectClassName;

  disableWidget('dialogObjectSubmit');
}

/**
 * save a idXXX of the selected linked object
 * 
 */
function saveLinkObject() {  
  var param="";  
  var nbSelected=0;
  if (dojo.byId("linkedObjectId").value == "") {
      return;
  }

// ADD BY Marc TABARY - 2017-03-31 - ADD MULTIPLE OBJECTS LINKED BY ID
  list=dojo.byId("linkedObjectId");
  if (list.options) {
    selected=new Array();
    for (var i=0; i < list.options.length; i++) {
      if (list.options[i].selected) {
        selected.push(list.options[i].value);
        nbSelected++;
      }
    }
  }  
// END ADD BY Marc TABARY - 2017-03-31 - ADD MULTIPLE OBJECTS LINKED BY ID

// CHANGE BY Marc TABARY - 2017-03-31 - ADD MULTIPLE OBJECTS LINKED BY ID
  param="?linkedObjectId="+selected;
// END CHANGE BY Marc TABARY - 2017-03-31 - ADD MULTIPLE OBJECTS LINKED BY ID
  param+="&mainObjectClass="+dojo.byId('mainObjectClass').value;
  param+="&idInstanceOfMainClass="+dojo.byId('idInstanceOfMainClass').value;
  param+="&linkObjectClassName="+dojo.byId('linkObjectClassName').value;

  loadContent("../tool/saveObjectLinkedByIdToMainObject.php"+param, "resultDivMain", "objectFormDialog", true, 'linkObject');
  dijit.byId('dialogObject').hide();
}

/**
 * Set idXXX of the linked object to null
 * 
 */
function removeLinkObjectFromObject(mainObjectClassName, linkObjectClassName, idLinkObject, nameLinkObject) {
  var param="?mainObjectClassName="+mainObjectClassName;
  param+="&linkObjectClassName="+linkObjectClassName;
  param+="&idLinkObject="+idLinkObject;

if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }

  actionOK=function() {
    loadContent("../tool/removeObjectLinkedByIdToMainObject.php"+param, "resultDivMain", "objectForm", true, 'linkObject');
  };

  msg=i18n('confirmRemoveLinkObjFromObj') + '<br/>' + nameLinkObject;
  showConfirm(msg, actionOK);
}


/**
 * Refresh the link objects list (after update)
 */
function refreshLinkObjectList(selected, mainObjectClassName, linkObjectClassName) {
  var param='';
  
  selected = typeof selected !== 'undefined' ? selected : 0;
  mainObjectClassName = typeof mainObjectClassName !== 'undefined' ? mainObjectClassName : '';
  linkObjectClassName = typeof linkObjectClassName !== 'undefined' ? linkObjectClassName : '';
  
  disableWidget('dialogObjectSubmit');
  var url='../tool/dynamicListObjectLinkedByIdToMainObject.php';

  param='?selected=' + selected;
    
  if (mainObjectClassName!='') {
    param+='&mainObjectClass=' + mainObjectClassName;      
  }

  if (linkObjectClassName!='') {
    param+='&linkObjectClassName=' + linkObjectClassName;      
  }

  url+=param;
  loadContent(url, 'dialogObjectList', 'objectForm', false);
  selectLinkObjectItem();
}

function selectLinkObjectItem() {
  var nbSelected=0;
  list=dojo.byId('linkedObjectId');
  if (list.options) {
    for (var i=0; i < list.options.length; i++) {
      if (list.options[i].selected) {
        nbSelected++;
      }
    }
  }
  if (nbSelected > 0) {
    enableWidget('dialogObjectSubmit');
  } else {
    disableWidget('dialogObjectSubmit');
  }
}
// END ADD BY Marc TABARY - 2017-02-23 - LIST - ADD - REMOVE OBJECTS LINKED BY ID TO MAIN OBJECT

// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM

/**
 * Save the new status of the object
 */
function saveChangedStatusObject() {
    // The selected status
    list=dojo.byId("changeStatusId");
    if (list.options) {
        selected=0;
        for (var i=0; i < list.options.length; i++) {
          if (list.options[i].selected) {
            selected=list.options[i].value;
            i = list.options.length+10;
          }
        }
    
    // The class object
    var objectClass = dojo.byId("objectClassChangeStatus").value;
    
    // The Object id
    var objectId = dojo.byId("idInstanceOfClassChangeStatus").value;
    
    url = "";
    
    param="?newStatusId="+selected;
    param+="&objectClass="+objectClass;
    param+="&idInstanceOfClass="+objectId;

    loadContent("../tool/changeObjectStatus.php"+param, "resultDivMain", "objectForm", true, objectClass);
    dijit.byId('dialogChangeStatus').hide();
    }  
}

function selectChangeStatusItem() {
  var nbSelected=0;
  list=dojo.byId('changeStatusId');
  if (list.options) {
    for (var i=0; i < list.options.length; i++) {
      if (list.options[i].selected) {
        nbSelected=1;
        break;
      }
    }
  }
  if (nbSelected > 0) {
    enableWidget('dialogChangeStatusSubmit');
  } else {
    disableWidget('dialogChangeStatusSubmit');
  }
}

/**
 * Refresh the allowed status for the object
 * @param objClass      : The object's class for which change the status
 * @param objId         : The object's id
 * @param obTypeId      : The object's type
 * @param objStatusId   : The object's status
 * 
 */
function changeObjectStatus(objClass, objId, objTypeId, objStatusId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  
  // empty select of previous options
  if (typeof dojo.byId('changeStatusId') !== 'undefined') {
    var node = document.getElementById("changeStatusId");
    while (node.firstChild) node.removeChild(node.firstChild);
  }
  
  var param='';
    
  disableWidget('dialogChangeStatusSubmit');
  var url='../tool/dynamicListChangeStatus.php';

  param = '?objectId=' + objId;
  param += '&objectClass=' + objClass;
  param += '&idType=' + objTypeId;
  param += '&idStatus=' + objStatusId;

  url+=param;
  loadContent(url, 'dialogChangeStatusList', 'changeStatusForm', false);
  selectChangeStatusItem();

  dijit.byId("dialogChangeStatus").show();

  dojo.byId("objectClassChangeStatus").value = objClass;
  dojo.byId("idInstanceOfClassChangeStatus").value = objId;
  dojo.byId("idStatusOfInstanceOfClassChangeStatus").value = objStatusId;
  dojo.byId("idTypeOfInstanceOfClassChangeStatus").value = objTypeId;
}


/**
 * Refresh the allowed status for the object
 * @param objId         : The object's id
 * @param objStatusId   : The object's status
 * 
 */
function changeStatusNotification(objId, objStatusId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  
  if (objStatusId == 1) { newStatusId = 2; } else { newStatusId = 1;}
  param="?newStatusId="+newStatusId;
  param+="&idInstanceOfClass="+objId;

  loadContent("../tool/changeStatusNotification.php"+param, "resultDivMain", "objectForm", true, "Notification");
  refreshNotificationTree(false);
}

// END - ADD BY TABARY - NOTIFICATION SYSTEM


//=============================================================================
//= Financial
//=============================================================================

//gautier #providerTerm
function editProviderTerm(objectClass,idProviderOrder,isLine,id,name,date,tax,discount,untaxed,taxAmount,fullAmount,totalUntaxed) {
  affectationLoad=true;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  //var percent = Math.round(untaxed*100000/totalUntaxed)/1000;
  var percent = untaxed*100/totalUntaxed;
  var callBack = function () {
    if(name){
      dijit.byId("providerTermName").set('value', name);
    }
    if (date) {
      dijit.byId("providerTermDate").set('value', date);
    }
    if (tax) {
      dijit.byId("providerTermTax").set('value', tax);
    }
    if (discount) {
      dijit.byId("providerTermDiscount").set('value', discount);
    }
    if( isLine == 'false'){
      dijit.byId("providerTermPercent").set('value', percent);
      
      if (untaxed) {
        dijit.byId("providerTermUntaxedAmount").set('value', untaxed);
      }
      if (taxAmount) {
        dijit.byId("providerTermTaxAmount").set('value', taxAmount);
      } 
      if (fullAmount) {
        dijit.byId("providerTermFullAmount").set('value', fullAmount);
      } 
    }
    dijit.byId("dialogProviderTerm").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&objectClass="+objectClass;
  params+="&id="+id;
  params+="&idProviderOrderEdit="+idProviderOrder;
  params+="&isLineMulti="+isLine;
  params+="&mode=edit";
  loadDialog('dialogProviderTerm',callBack,false,params);
}

function removeProviderTerm(id, fromBill) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    var url="../tool/removeProviderTerm.php?providerTermId="+id;
    if (fromBill) url+="&fromBill=true";
    loadContent(url, "resultDivMain",
        null, true, 'providerTerm');
  };
    msg=i18n('confirmDeleteProviderTerm', new Array(id));
    showConfirm(msg, actionOK);
}

function removeProviderTermFromBill(id,idProviderBill) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeProviderTerm.php?providerTermId="+id+"&isProviderBill=true", "resultDivMain",
        null, true, 'providerTerm');
  };
    msg=i18n('confirmRemoveProviderTermFromBill', new Array(id));
    showConfirm(msg, actionOK);
}

function addProviderTerm(objectClass, type, idProviderOrder, isLine) {
  var callBack = function () {
    affectationLoad=true;
    dijit.byId("dialogProviderTerm").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&idProviderOrder="+idProviderOrder;
  params+="&type="+type;
  params+="&isLine="+isLine;
  params+="&mode=add";
  params+="&objectClass="+objectClass;
  loadDialog('dialogProviderTerm',callBack,false,params);
}

function saveProviderTerm() {
  var formVar=dijit.byId('providerTermForm');
  if (formVar.validate()) {
    loadContent("../tool/saveProviderTerm.php", "resultDivMain", "providerTermForm", true, 'providerTerm');
    dijit.byId('dialogProviderTerm').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

var cancelRecursiveChange_OnGoingChange = false;
function providerTermLine(totalUntaxedAmount){
  if (cancelRecursiveChange_OnGoingChange) return;
  cancelRecursiveChange_OnGoingChange = true;
  var totalUntaxedAmountValue = totalUntaxedAmount;
  var untaxedAmount = dijit.byId("providerTermUntaxedAmount").get("value");
  if (!untaxedAmount)
    untaxedAmount = 0;
  var taxPct = dijit.byId("providerTermTax").get("value");
  if (!taxPct)
    taxPct = 0;
  // Calculated values
  var taxAmount = Math.round(untaxedAmount * taxPct) / 100;
  var fullAmount = taxAmount + untaxedAmount;
  //var percent = Math.round(untaxedAmount*100000/totalUntaxedAmountValue)/1000;
  var percent = untaxedAmount*100/totalUntaxedAmountValue;
  // Set values to fields
  dijit.byId("providerTermPercent").set('value', percent);
  dijit.byId("providerTermTaxAmount").set('value', taxAmount);
  dijit.byId("providerTermFullAmount").set('value', fullAmount);
  setTimeout("cancelRecursiveChange_OnGoingChange = false;",50);
}
function providerTermLinePercent(totalUntaxedAmount){
  if (cancelRecursiveChange_OnGoingChange) return;
  cancelRecursiveChange_OnGoingChange = true;
  var totalUntaxedAmountValue = totalUntaxedAmount;
  var percent = dijit.byId("providerTermPercent").get("value");
  var taxPct = dijit.byId("providerTermTax").get("value");
  if (!taxPct)
    taxPct = 0;
  // Calculated values
  var untaxedAmount = percent*totalUntaxedAmountValue/100;
  var taxAmount = Math.round(untaxedAmount * taxPct) / 100;
  var fullAmount = taxAmount + untaxedAmount;
  // Set values to fields
  dijit.byId("providerTermUntaxedAmount").set('value', untaxedAmount);
  dijit.byId("providerTermTaxAmount").set('value', taxAmount);
  dijit.byId("providerTermFullAmount").set('value', fullAmount);
  setTimeout("cancelRecursiveChange_OnGoingChange = false;",50);
}

function providerTermLineBillLine(id){
  if (cancelRecursiveChange_OnGoingChange) return;
  cancelRecursiveChange_OnGoingChange = true;
  var totalUntaxedAmountValue = dijit.byId("providerTermBillLineUntaxed"+id).get("value");
  var untaxedAmount = dijit.byId("providerTermUntaxedAmount"+id).get("value");
  var discount = dijit.byId("providerTermDiscount").get("value");
  if (!untaxedAmount)
    untaxedAmount = 0;
  var taxPct = dijit.byId("providerTermTax").get("value");
  if (!taxPct)
    taxPct = 0;
  // Calculated values
  var discountBill = (untaxedAmount * discount / 100);
  var taxAmount = Math.round((untaxedAmount-discountBill)* taxPct) / 100;
  var fullAmount = untaxedAmount - discountBill + taxAmount;
  //var percent = Math.round(untaxedAmount*100000/totalUntaxedAmountValue)/1000;
  var percent = untaxedAmount*100/totalUntaxedAmountValue;
  // Set values to fields
  dijit.byId("providerTermDiscountAmount"+id).set('value', discountBill);
  dijit.byId("providerTermPercent"+id).set('value', percent);
  dijit.byId("providerTermTaxAmount"+id).set('value', taxAmount);
  dijit.byId("providerTermFullAmount"+id).set('value', fullAmount);
  setTimeout("cancelRecursiveChange_OnGoingChange = false;",50);
}

function providerTermLinePercentBilleLine(id){
  if (cancelRecursiveChange_OnGoingChange) return;
  cancelRecursiveChange_OnGoingChange = true;
  var totalUntaxedAmountValue = dijit.byId("providerTermBillLineUntaxed"+id).get("value");
  var percent = dijit.byId("providerTermPercent"+id).get("value");
  var taxPct = dijit.byId("providerTermTax").get("value");
  var discount = dijit.byId("providerTermDiscount").get("value");
  if (!taxPct)
    taxPct = 0;
  // Calculated values
  var untaxedAmount = percent*totalUntaxedAmountValue/100;
  var discountBill = (untaxedAmount * discount / 100);
  var taxAmount = Math.round((untaxedAmount-discountBill)* taxPct) / 100;
  var fullAmount = untaxedAmount - discountBill + taxAmount;
  // Set values to fields
  dijit.byId("providerTermUntaxedAmount"+id).set('value', untaxedAmount);
  dijit.byId("providerTermDiscountAmount"+id).set('value', discountBill);
  dijit.byId("providerTermTaxAmount"+id).set('value', taxAmount);
  dijit.byId("providerTermFullAmount"+id).set('value', fullAmount);
  setTimeout("cancelRecursiveChange_OnGoingChange = false;",50);
}

function addProviderTermFromProviderBill() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&providerBillId="+dijit.byId('id').get('value');
  loadDialog('dialogProviderTermFromProviderBill', null, true, params);
}


function saveProviderTermFromProviderBill() {
  var formVar=dijit.byId('providerTermFromProviderBillForm');
  if (formVar.validate() && dojo.byId('linkProviderTerm') && dojo.byId('linkProviderTerm').value ) {
    loadContent("../tool/saveProviderTermFromProviderBill.php", "resultDivMain", "providerTermFromProviderBillForm", true,'ProviderTerm');
    dijit.byId('dialogProviderTermFromProviderBill').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function providerTermLineChangeNumber(){
  if (!dijit.byId('providerTermNumberOfTerms')) return;
  var number=dijit.byId('providerTermNumberOfTerms').get("value");
  if (! number || number<=0) return;
  //dijit.byId('providerTermPercent').set('value',100/number);
  
  if (number>1) {
    cancelRecursiveChange_OnGoingChange=true;
    dijit.byId('providerTermUntaxedAmount').set('value',dijit.byId('providerTermOrderUntaxedAmount').get('value')/number);
    dijit.byId('providerTermPercent').set('value',100/number);
    dijit.byId('providerTermFullAmount').set('value',dijit.byId('providerTermOrderFullAmount').get('value')/number);
    dijit.byId('providerTermTaxAmount').set('value',dijit.byId('providerTermFullAmount').get('value')-dijit.byId('providerTermUntaxedAmount').get('value'));
    lockWidget('providerTermPercent');
    lockWidget('providerTermUntaxedAmount');
    var termDate=dijit.byId('providerTermDate').get('value');
    if (! termDate) {
      dojo.byId('labelRegularTerms').innerHTML='<br/>'+'<span style="color:red">'+i18n('messageMandatory',new Array(i18n('colDate')))+'</span>';
    } else {
      var termDay=termDate.getDate();
      var lastDayOfMonth = (new Date(termDate.getFullYear(), termDate.getMonth()+1, 0)).getDate();
      if (termDay==lastDayOfMonth ) {
        termDay=i18n('colLastDay');
      }
      var startDate=dateFormatter(formatDate(termDate));
      dojo.byId('labelRegularTerms').innerHTML='<br/>'+i18n('labelRegularTerms',new Array(number,termDay,startDate));
    }
    setTimeout("cancelRecursiveChange_OnGoingChange=false;",50);
  } else {
    //dijit.byId('providerTermPercent').set('value',100);
    unlockWidget('providerTermPercent');
    unlockWidget('providerTermUntaxedAmount');
    dojo.byId('labelRegularTerms').innerHTML="";
  }
}
function refreshLinkProviderTerm(selected) {
  var url='../tool/dynamicListLinkProviderTerm.php';
  if (selected) {
    url+='?selected=' + selected;
    if (dojo.byId("ProviderBillId")) {
      url+="&providerBillId="+dojo.byId("ProviderBillId").value;
    }
    var callback=function() {
      dojo.byId('linkProviderTerm').focus();
    };
    loadDiv(url, 'linkProviderTermDiv', null, callback);
  }
}
// =============================================================================
// = Affectation
// =============================================================================

function addAffectation(objectClass, type, idResource, idProject) {
//  if (checkFormChangeInProgress()) {
//    showAlert(i18n('alertOngoingChange'));
//    return;
//  }
  var callBack = function () {
    affectationLoad=true;
    dijit.byId("dialogAffectation").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&idProject="+idProject;
  params+="&objectClass="+objectClass;
  params+="&idResource="+idResource;
  params+="&type="+type;
  params+="&mode=add";
  loadDialog('dialogAffectation',callBack,false,params);
}
//gautier #workUnits
function addWorkUnit(idCatalogUO) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  pauseBodyFocus();
  var callBack = function () {
    ckEditorReplaceEditor("WUDescriptions",992);
    ckEditorReplaceEditor("WUIncomings",993);
    ckEditorReplaceEditor("WULivrables",994);
    dijit.byId("dialogWorkUnit").show();
  };
  var params="&idCatalog="+idCatalogUO;
  params+="&mode=add";
  loadDialog('dialogWorkUnit',callBack,false,params);
}
function removeWorkUnit(idWorkUnit) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeWorkUnit.php?idWorkUnit="+idWorkUnit, "resultDivMain",null, true, 'affectation');
  };
  msg=i18n('confirmDeleteWorkUnit', new Array(id,i18n('WorkUnit'),idWorkUnit));
  showConfirm(msg, actionOK);
}
//end workUnits
//gautier #resourceCapacity
function addResourceCapacity(objectClass, type, idResource) {
  var callBack = function () {
    affectationLoad=true;
    dijit.byId("dialogResourceCapacity").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&idResource="+idResource;
  params+="&type="+type;
  params+="&mode=add";
  loadDialog('dialogResourceCapacity',callBack,false,params);
}

function saveResourceCapacity(capacity){
  var formVar=dijit.byId('resourceCapacityForm');
  if (dijit.byId('resourceCapacityStartDate') && dijit.byId('resourceCapacityEndDate')) {
    var start=dijit.byId('resourceCapacityStartDate').value;
    var end=dijit.byId('resourceCapacityEndDate').value;
    if (start && end && dayDiffDates(start, end) < 0) {
      showAlert(i18n("errorStartEndDates", new Array(i18n("colStartDate"),
          i18n("colEndDate"))));
      return;
    }
  }
  if (dijit.byId('resourceCapacity')){
    var newCapacity = dijit.byId('resourceCapacity').value;
    if(capacity === newCapacity){
      showAlert(i18n("changeCapacity"));
      return;
    }
  }
  
  if (formVar.validate()) {
    loadContent("../tool/saveResourceCapacity.php", "resultDivMain", "resourceCapacityForm",true,'affectation');
    dijit.byId('dialogResourceCapacity').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function removeResourceCapacity(id,idResource) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeResourceCapacity.php?idResourceCapacity="+id+"&idResource="+idResource, "resultDivMain",null, true, 'affectation');
  };
  msg=i18n('confirmDeleteResourceCapacity', new Array(id,i18n('Resource'),idResource));
  showConfirm(msg, actionOK);
}

function editResourceCapacity(id,idResource,capacity, idle, startDate, endDate) {
  affectationLoad=true;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    dojo.xhrGet({
      url : '../tool/getSingleData.php?dataType=resourceCapacityDescription&idResourceCapacity='+id,
      handleAs : "text",
      load : function(data) {
        dijit.byId('resourceCapacityDescription').set('value', data);
        enableWidget("resourceCapacityDescription");
      }
      });
    if (capacity) {
      dijit.byId("resourceCapacity").set('value', parseFloat(capacity));
    }
    if (startDate) {
      dijit.byId("resourceCapacityStartDate").set('value', startDate);
    } else {
      dijit.byId("resourceCapacityStartDate").reset();
    }
    if (endDate) {
      dijit.byId("resourceCapacityEndDate").set('value', endDate);
    } else {
      dijit.byId("resourceCapacityEndDate").reset();
    }
    if (idle == 1) {
      dijit.byId("resourceCapacityIdle").set('value', idle);
    } else {
      dijit.byId("resourceCapacityIdle").reset();
    }
    dijit.byId("dialogResourceCapacity").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&id="+id;
  params+="&idResource="+idResource;
  params+="&mode=edit";
  loadDialog('dialogResourceCapacity',callBack,false,params);
}
//gautier workUnit
function saveWorkUnit(){
  editorDescriptions=CKEDITOR.instances['WUDescriptions'];
  editorDescriptions.updateElement();
  editorWUIncomings=CKEDITOR.instances['WUIncomings'];
  editorWUIncomings.updateElement();
  editorWULivrables=CKEDITOR.instances['WULivrables'];
  editorWULivrables.updateElement();
  if (trim(dijit.byId('WUReferences').get("value"))=="") {
    showAlert(i18n("referenceIsMissing"));
    return;
  }
  var formVar=dijit.byId('workUnitForm');
  if (formVar.validate()) {
    loadContent("../tool/saveWorkUnit.php", "resultDivMain", "workUnitForm",true,'WorkUnit');
    dijit.byId('dialogWorkUnit').hide();
    loadContent("objectDetail.php?refreshComplexitiesValues=true", "CatalogUO_unitOfWork", 'listForm');
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}
function editWorkUnit(id,idCatalogUO,validityDate) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  pauseBodyFocus();
  var callBack = function () {
    ckEditorReplaceEditor("WUDescriptions",992);
    ckEditorReplaceEditor("WUIncomings",993);
    ckEditorReplaceEditor("WULivrables",994);
    if (validityDate) {
      dijit.byId("ValidityDateWU").set('value', validityDate);
    } else {
      dijit.byId("ValidityDateWU").reset();
    }
    dijit.byId("dialogWorkUnit").show();
  };
  var params="&id="+id;
  params+="&idCatalog="+idCatalogUO;
  params+="&mode=edit";
  loadDialog('dialogWorkUnit',callBack,false,params);
}
// end workUnit

//gautier resourceSurbooking
function addResourceSurbooking(objectClass, type, idResource) {
  var callBack = function () {
    affectationLoad=true;
    dijit.byId("dialogResourceSurbooking").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&idResource="+idResource;
  params+="&type="+type;
  params+="&mode=add";
  loadDialog('dialogResourceSurbooking',callBack,false,params);
}

function saveResourceSurbooking(capacity){
  var formVar=dijit.byId('resourceSurbookingForm');
  if (dijit.byId('resourceSurbookingStartDate') && dijit.byId('resourceSurbookingEndDate')) {
    var start=dijit.byId('resourceSurbookingStartDate').value;
    var end=dijit.byId('resourceSurbookingEndDate').value;
    if (start && end && dayDiffDates(start, end) < 0) {
      showAlert(i18n("errorStartEndDates", new Array(i18n("colStartDate"),
          i18n("colEndDate"))));
      return;
    }
  }
  if (dijit.byId('resourceSurbooking')){
    var newCapacity = dijit.byId('resourceSurbooking').value;
    if(newCapacity === 0){
      showAlert(i18n("changeSurbooking"));
      return;
    }
  }
  if (formVar.validate()) {
    loadContent("../tool/saveResourceSurbooking.php", "resultDivMain", "resourceSurbookingForm",true,'affectation');
    dijit.byId('dialogResourceSurbooking').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function removeResourceSurbooking(id,idResource) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeResourceSurbooking.php?idResourceSurbooking="+id+"&idResource="+idResource, "resultDivMain",null, true, 'affectation');
  };
  msg=i18n('confirmDeleteResourceSurbooking', new Array(id,i18n('Resource'),idResource));
  showConfirm(msg, actionOK);
}

function editResourceSurbooking(id,idResource,capacity, idle, startDate, endDate) {
  affectationLoad=true;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    dojo.xhrGet({
      url : '../tool/getSingleData.php?dataType=resourceSurbookingDescription&idResourceSurbooking='+id,
      handleAs : "text",
      load : function(data) {
        dijit.byId('resourceSurbookingDescription').set('value', data);
        enableWidget("resourceSurbookingDescription");
      }
      });
    if (capacity) {
      dijit.byId("resourceSurbooking").set('value', parseFloat(capacity));
    }
    if (startDate) {
      dijit.byId("resourceSurbookingStartDate").set('value', startDate);
    } else {
      dijit.byId("resourceSurbookingStartDate").reset();
    }
    if (endDate) {
      dijit.byId("resourceSurbookingEndDate").set('value', endDate);
    } else {
      dijit.byId("resourceSurbookingEndDate").reset();
    }
    if (idle == 1) {
      dijit.byId("resourceSurbookingIdle").set('value', idle);
    } else {
      dijit.byId("resourceSurbookingIdle").reset();
    }
    dijit.byId("dialogResourceSurbooking").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&id="+id;
  params+="&idResource="+idResource;
  params+="&mode=edit";
  loadDialog('dialogResourceSurbooking',callBack,false,params);
}

//gautier #resourceTeam
function addAffectationResourceTeam(objectClass, type, idResource) {
var callBack = function () {
  affectationLoad=true;
  dijit.byId("dialogAffectationResourceTeam").show();
  setTimeout("affectationLoad=false", 500);
};
var params="&idResource="+idResource;
params+="&type="+type;
params+="&mode=add";
loadDialog('dialogAffectationResourceTeam',callBack,false,params);
}

function removeAffectation(id,own,affectedClass,affectedId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeAffectation.php?affectationId="+id+"&affectationIdTeam=''", "resultDivMain",
        null, true, 'affectation');
  };
  if (own) {
    msg='<span style="color:red;font-weight:bold;">'+i18n('confirmDeleteOwnAffectation', new Array(id))+'</span>';
  } else {
    msg=i18n('confirmDeleteAffectation', new Array(id,i18n(affectedClass),affectedId));
  }
  showConfirm(msg, actionOK);
}

// gautier #resourceTeam
function removeAffectationResourceTeam(id,idResource) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeAffectationResourceTeam.php?affectaionId="+id, "resultDivMain",
        null, true, 'affectation');
  };
  msg=i18n('confirmDeleteAffectation', new Array(id,i18n('Resource'),idResource));
  showConfirm(msg, actionOK);
}

affectationLoad=false;
//gautier #resourceTeam
function editAffectationResourceTeam(id, objectClass, type, idResource, rate, idle, startDate, endDate) {
  affectationLoad=true;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    dojo.xhrGet({
      url : '../tool/getSingleData.php?dataType=affectationDescriptionResourceTeam&idAffectation='+id,
      handleAs : "text",
      load : function(data) {
        dijit.byId('affectationDescriptionResourceTeam').set('value', data);
        enableWidget("affectationDescriptionResourceTeam");
      }
      });
    if(idResource){
      dijit.byId("affectationResourceTeam").set('value', idResource);
    }
    if (rate) {
      dijit.byId("affectationRateResourceTeam").set('value', rate);
    }
    if (startDate) {
      dijit.byId("affectationStartDateResourceTeam").set('value', startDate);
    } else {
      dijit.byId("affectationStartDateResourceTeam").reset();
    }
    if (endDate) {
      dijit.byId("affectationEndDateResourceTeam").set('value', endDate);
    } else {
      dijit.byId("affectationEndDateResourceTeam").reset();
    }
    if (idle == 1) {
      dijit.byId("affectationIdleResourceTeam").set('value', idle);
    } else {
      dijit.byId("affectationIdleResourceTeam").reset();
    }
    dijit.byId("dialogAffectationResourceTeam").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&id="+id;
  params+="&refType="+dojo.byId('objectClass').value;
  params+="&idResource="+idResource;
  params+="&mode=edit";
  params+="&type="+type;
  params+="&objectClass="+objectClass;
  loadDialog('dialogAffectationResourceTeam',callBack,false,params);
}

function editAffectation(id, objectClass, type, idResource, idProject, rate, idle, startDate, endDate, idProfile) {
  affectationLoad=true;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=affectationDescription&idAffectation='+id,
    handleAs : "text",
    load : function(data) {
      dijit.byId('affectationDescription').set('value', data);
      enableWidget("affectationDescription");
      }
    });
    if (startDate) {
      dijit.byId("affectationStartDate").set('value', startDate);
    } else {
      dijit.byId("affectationStartDate").reset();
    }
    if (endDate) {
      dijit.byId("affectationEndDate").set('value', endDate);
    } else {
      dijit.byId("affectationEndDate").reset();
    }
    if (idle == 1) {
      dijit.byId("affectationIdle").set('value', idle);
    } else {
      dijit.byId("affectationIdle").reset();
    }
    dijit.byId("dialogAffectation").show();
    setTimeout("affectationLoad=false", 500);
  };
  var params="&id="+id;
  params+="&refType="+dojo.byId('objectClass').value;
  params+="&idProject="+idProject;
  params+="&idResource="+idResource;
  params+="&mode=edit";
  params+="&type="+type;
  params+="&objectClass="+objectClass;
  loadDialog('dialogAffectation',callBack,false,params);
}

function saveAffectation() {
  var formVar=dijit.byId('affectationForm');
  if (dijit.byId('affectationStartDate') && dijit.byId('affectationEndDate')) {
    var start=dijit.byId('affectationStartDate').value;
    var end=dijit.byId('affectationEndDate').value;
    if (start && end && dayDiffDates(start, end) < 0) {
      showAlert(i18n("errorStartEndDates", new Array(i18n("colStartDate"),
          i18n("colEndDate"))));
      return;
    }
  }
  if (formVar.validate()) {
    loadContent("../tool/saveAffectation.php", "resultDivMain", "affectationForm",
        true, 'affectation');
    dijit.byId('dialogAffectation').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}
//gautier #resourceTeam
function saveAffectationResourceTeam() {
  var formVar=dijit.byId('affectationResourceTeamForm');
  if (dijit.byId('affectationStartDate') && dijit.byId('affectationEndDate')) {
    var start=dijit.byId('affectationStartDate').value;
    var end=dijit.byId('affectationEndDate').value;
    if (start && end && dayDiffDates(start, end) < 0) {
      showAlert(i18n("errorStartEndDates", new Array(i18n("colStartDate"),
          i18n("colEndDate"))));
      return;
    }
  }
  if (trim(dijit.byId('affectationResourceTeam'))=='') {
    showAlert(i18n("messageMandatory", new Array(i18n("colIdResource"))));
    return;
  }
  if (formVar.validate()) {
    loadContent("../tool/saveAffectationResourceTeam.php", "resultDivMain", "affectationResourceTeamForm",
        true, 'affectation');
    dijit.byId('dialogAffectationResourceTeam').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function affectTeamMembers(idTeam) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    dijit.byId("dialogAffectation").show();
  };
  var params="&affectationIdTeam="+idTeam;
  loadDialog('dialogAffectation',callBack,false,params);
}

function affectOrganizationMembers(idOrganization) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var callBack = function () {
    dijit.byId("dialogAffectation").show();
  };
  var params="&affectationIdOrganization="+idOrganization;
  loadDialog('dialogAffectation',callBack,false,params);
}

function affectationChangeResource() {
  var idResource=dijit.byId("affectationResource").get("value");
  if (!idResource)
    return;
  if (affectationLoad)
    return;
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=resourceProfile&idResource='
        + idResource,
    handleAs : "text",
    load : function(data) {
      dijit.byId('affectationProfile').set('value', data);
    }
  });
}

function replaceAffectation (id, objectClass, type, idResource, idProject, rate,
    idle, startDate, endDate, idProfile) {
  var callback=function() {
    refreshList('idProfile', 'idProject', idProject, null, 'replaceAffectationProfile', false  );
  };
  var param="&idAffectation="+id;
  loadDialog("dialogReplaceAffectation", callback, true, param);
}
function replaceAffectationSave() {
  var formVar=dijit.byId('replaceAffectationForm');
  if (dijit.byId('replaceAffectationStartDate') && dijit.byId('replaceAffectationEndDate')) {
    var start=dijit.byId('replaceAffectationStartDate').value;
    var end=dijit.byId('replaceAffectationEndDate').value;
    if (start && end && dayDiffDates(start, end) <= 0) {
      showAlert(i18n("errorStartEndDates", new Array(i18n("colStartDate"),i18n("colEndDate"))));
      return;
    }
  }
  if (dijit.byId('replaceAffectationResource').get("value")==dojo.byId("replaceAffectationExistingResource").value) {
    showAlert(i18n("errorReplaceResourceNotChanged"));
    return;
  }
  if (formVar.validate()) {
    loadContent("../tool/saveAffectationReplacement.php", "resultDivMain", "replaceAffectationForm",
        true, 'affectation');
    dijit.byId('dialogReplaceAffectation').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}
function replaceAffectationChangeResource() {
  var idResource=dijit.byId("replaceAffectationResource").get("value");
  if (!idResource)
    return;
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=resourceProfile&idResource='
        + idResource,
    handleAs : "text",
    load : function(data) {
      dijit.byId('replaceAffectationProfile').set('value', data);
    }
  });
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=resourceCapacity&idResource='
        + idResource,
    handleAs : "text",
    load : function(data) {
      dijit.byId('replaceAffectationCapacity').set('value', parseFloat(data));
    }
  });
}

function addResourceIncompatible(idResource) {
  var callBack = function () {
    dijit.byId("dialogResourceIncompatible").show();
  };
  var params="&idResource="+idResource;
  loadDialog('dialogResourceIncompatible',callBack,false,params);
}

function saveResourceIncompatible(){
  var formVar=dijit.byId('resourceIncompatibleForm');
  if (formVar.validate()) {
    loadContent("../tool/saveResourceIncompatible.php", "resultDivMain", "resourceIncompatibleForm", true, 'affectation');
    dijit.byId('dialogResourceIncompatible').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function removeResourceIncompatible(id) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/saveResourceIncompatible.php?idIncompatible="+id, "resultDivMain", null, true, 'affectation');
  };
  msg=i18n('confirmDeleteResourceIncompatible', new Array(id,i18n('Resource')));
  showConfirm(msg, actionOK);
}

function addResourceSupport(idResource) {
  var callBack = function () {
    dijit.byId("dialogResourceSupport").show();
  };
  var params="&idResource="+idResource;
  loadDialog('dialogResourceSupport',callBack,false,params);
}

function saveResourceSupport(mode){
  var formVar=dijit.byId('resourceSupportForm');
  if (formVar.validate()) {
    loadContent("../tool/saveResourceSupport.php?mode="+mode, "resultDivMain", "resourceSupportForm",true, 'affectation');
    dijit.byId('dialogResourceSupport').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function editResourceSupport(id) {
	var callBack = function () {
	    dijit.byId("dialogResourceSupport").show();
	  };
	  var params="&idSupport="+id;
	  loadDialog('dialogResourceSupport',callBack,false,params);
}

function removeResourceSupport(id) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/saveResourceSupport.php?idSupport="+id, "resultDivMain", null, true, 'affectation');
  };
  msg=i18n('confirmDeleteResourceSupport', new Array(id,i18n('Resource')));
  showConfirm(msg, actionOK);
}

// =============================================================================
// = Misceallanous
// =============================================================================

var manualWindow=null;
var helpTimer=false;
function showHelp(link) {
  if (helpTimer) return; // avoid double open
  helpTimer=true;
  if (manualWindow) manualWindow.close();
  var objectClass=(dojo.byId('objectClassList'))?dojo.byId('objectClassList'):dojo.byId('objectClass');
  var objectClassManual=dojo.byId('objectClassManual');
  var section='';
  var selectedTab=(dijit.byId('parameterTabContainer'))?dijit.byId('parameterTabContainer').selectedChildWidget.get("id"):null;
  if (objectClassManual) {
    section=objectClassManual.value;
  } else if (objectClass) {
    section=objectClass.value;
  }
  if(link == 'ShortCut'){
    section = link;
  }
  dojo.xhrGet({
    url : "../tool/getManualUrl.php?section=" + section+"&tab="+selectedTab,
    handleAs : "text",
    load : function(data, args) {
      var url=data;
      var name="Manual";
      var attributes='toolbar=yes, titlebar=no, menubar=no, status=no, scrollbars=yes, directories=no, location=no, resizable=yes,'
          + 'height=650, width=1024, top=0, left=0';
      manualWindow=window.open(url, name, attributes);
      manualWindow.focus();
    },
    error : function() {
      consoleTraceLog("Error retrieving Manual URL for section '"+section+"'");
    }
  });
  setTimeout("helpTimer=false;",1000);
  return false;
}
/**
 * Refresh a list (after update)
 */
function refreshList(field, param, paramVal, selected, destination, required, param1, paramVal1,objectClass) {
  var urlList='../tool/jsonList.php?listType=list&dataType=' + field;
  if (param) {
    urlList+='&critField=' + param;
    urlList+='&critValue=' + paramVal;
    if(Array.isArray(paramVal)) {
      urlList += '&critArray=1';
    }
  }
  if (param1) {
    urlList+='&critField1=' + param1;
    urlList+='&critValue1=' + paramVal1;
  }
  if (selected && field!='planning') {
    urlList+='&selected=' + selected;
  }
  if (required || Array.isArray(paramVal)) {
    urlList+='&required=true';
  }
  if (objectClass) urlList+='&objectClass='+objectClass;
  // MTY - LEAVE SYSTEM
    if (destination=='idProjectPlan') {
        urlList+='&withoutLeaveProject=1';
    }
// MTY - LEAVE SYSTEM
  var datastore=new dojo.data.ItemFileReadStore({
    url : urlList
  });
  var store=new dojo.store.DataStore({
    store : datastore
  });
  if (destination) {
    var mySelect=dijit.byId(destination);
  } else {
    var mySelect=dijit.byId(field);
  }
  //mySelect.set('store', store);
  mySelect.set({labelAttr: 'name', store: store, sortByLabel: false});
  store.query({
    id : "*"
  }).then(function(items) {
    if (destination) {
      var mySelect=dijit.byId(destination);
    } else {
      var mySelect=dijit.byId(field);
    }
    if (required && ! selected && ! trim(mySelect.get('value')) ) { // required but no value set : select first
      mySelect.set("value", items[0].id);
    }
    if (selected) { // Check that selected is in the list
      var found=false;
      items.forEach(function(item) {
        if (item.id==selected) found=true;
      });
      if (! found) mySelect.set("value", items[0].id);
    }
    if (field=='planning') {
      mySelect.set("value",selected); 
    }
    if(destination){
      if(destination.substr(0,15)=='filterValueList') {
        var list=dojo.byId(destination);
        selectionList=selected.split('_');
        //while (list.options.length) {list.remove(0);} // Clean combo
        items.forEach(function(item) {
          if (!item.name || item.id==' ' || item.name==selected) {
          } else {
            if (selectionList.indexOf(item.id)>=0 && 1) {
              var found=false;
              for (var i=0;i<list.options.length;i++) { if (list.options[i].value==item.id) found=true; }
              if (!found) {
                var option = document.createElement("option");
                option.text = item.name;
                option.value = item.id;
                option.selected=true;
                list.add(option);
              }
            }
          }
        });
      }
    }
  });
}

function refreshListSpecific(listType, destination, param, paramVal, selected, required) {
  var urlList='../tool/jsonList.php?listType=' + listType;
  if (param) {
    urlList+='&' + param + '=' + paramVal;
  }
  if (selected) {
    urlList+='&selected=' + selected;
  }
  if (required) {
    urlList+='&required=true';
  }
  var datastore=new dojo.data.ItemFileReadStore({
    url : urlList
  });
  var store=new dojo.store.DataStore({
    store : datastore
  });
  store.query({
    id : "*"
  });
  var mySelect=dijit.byId(destination);
  mySelect.set('store', store);
}
function setProductValueFromVersion(field, versionId) {
  // alert("Call : "+field+"/"+versionId);
  dojo.xhrGet({
    url : "../tool/getProductValueFromVersion.php?idVersion=" + versionId,
    handleAs : "text",
    load : function(data, args) {
      prd=dijit.byId(field);
      if (prd) {
        prd.set("value", trim(data));
      }
    },
    error : function() {
    }
  });
}
function setClientValueFromProject(field, projectId) {
  dojo.xhrGet({
    url : "../tool/getClientValueFromProject.php?idProject=" + projectId,
    handleAs : "text",
    load : function(data, args) {
      client=dijit.byId(field);
      if (client && data) {
        client.set("value", data);
      }
    },
    error : function() {
    }
  });
}

var menuHidden=false;
var menuActualStatus='visible';
var menuDivSize=0;
var menuShowMode='CLICK';
var hideShowMenuInProgress=false;
var hideShowTries=0;
/**
 * Hide or show the Menu (left part of the screen
 */
function hideShowMenu(noRefresh,noStore) {
  if(isNewGui)return;
  var disableSlide=true;
  if (!dijit.byId("leftDiv")) {
    return;
  }
  if (!dijit.byId("leftDiv") || !dijit.byId("centerDiv") || !dijit.byId("leftDiv_splitter")) {
    hideShowTries++;
    if (hideShowTries<10) setTimeout("hideShowMenu();",100);
    return;
  }
  hideShowTries=0;
  hideShowMenuInProgress=true;
  duration=1;
  if (menuActualStatus == 'visible' || !menuHidden) {
    saveDataToSession("hideMenu","YES",true);
    if (!noStore) menuDivSize=dojo.byId("leftDiv").offsetWidth;
    fullWidth=dojo.byId("mainDiv").offsetWidth;
    if (menuDivSize < 2) {
      menuDivSize=dojo.byId("mainDiv").offsetWidth * .2;
    }
    if (disableSlide || !isHtml5()) {
      duration=0;
      dojo.byId('menuBarShow').style.display='block';
      dojo.byId('leftDiv_splitter').style.display='none';
      dijit.byId("leftDiv").resize({
        w : dojo.byId("menuBarShow").offsetWidth
      });
    } else {
      dojox.fx.combine([ dojox.fx.animateProperty({
        node : "leftDiv",
        properties : {
          width : 34
        },
        duration : duration
      }), dojox.fx.animateProperty({
        node : "centerDiv",
        properties : {
          left : 45,
          width : fullWidth
        },
        duration : duration
      }), dojox.fx.animateProperty({
        node : "leftDiv_splitter",
        properties : {
          left : 31
        },
        duration : duration
      }) ]).play();
      setTimeout("dojo.byId('menuBarShow').style.display='block'", duration);
      setTimeout("dojo.byId('leftDiv_splitter').style.display='none';",duration);
    }
    //dojo.byId("buttonHideMenuLabel").innerHTML=i18n('buttonShowMenu');
    menuHidden=true;
    menuActualStatus='hidden'; 
    dojo.byId('hideMenuBarShowButton2').style.display='none';
  } else {
    saveDataToSession("hideMenu","NO",true);  
    if (menuDivSize < 20) {
      menuDivSize=dojo.byId("mainDiv").offsetWidth * .2;
    }
    if (disableSlide || !isHtml5()) {
      duration=0;
      dijit.byId("leftDiv").resize({
        w : menuDivSize
      });
      dojo.byId('menuBarShow').style.display='none';
      dojo.byId('leftDiv_splitter').style.left='20px';
      dojo.byId('leftDiv_splitter').style.display='block';
    } else {
      dojox.fx.combine([ dojox.fx.animateProperty({
        node : "leftDiv",
        properties : {
          width : menuDivSize
        },
        duration : duration
      }), dojox.fx.animateProperty({
        node : "centerDiv",
        properties : {
          left : menuDivSize + 5
        },
        duration : duration
      }), dojox.fx.animateProperty({
        node : "leftDiv_splitter",
        properties : {
          left : menuDivSize
        },
        duration : duration
      }) ]).play();
      dojo.byId('menuBarShow').style.display='none';
      dojo.byId('leftDiv_splitter').style.left='20px';
      dojo.byId('leftDiv_splitter').style.display='block';
    }
    //dojo.byId("buttonHideMenuLabel").innerHTML=i18n('buttonHideMenu');
    menuHidden=false;
    menuActualStatus='visible';
    dojo.byId('hideMenuBarShowButton2').style.display='block';
  }
  setTimeout('dijit.byId("globalContainer").resize();', duration + 10);
  var detailHidden=false;
  if (dojo.byId('detailBarShow') && dojo.byId('detailBarShow').style.display=='block') detailHidden=true;
  if (!noRefresh && !formChangeInProgress && dojo.byId('id') && dojo.byId('id').value && !detailHidden) {
    setTimeout('loadContent("objectDetail.php", "detailDiv", "listForm");',
        duration + 50);
  }
  setTimeout("hideShowMenuInProgress=false;",duration+50);
  // dojo.byId('menuBarShow').style.top='50px';
  dojo.byId("hideMenuBarShowButton2").style.left=dojo.byId("leftDiv").offsetWidth+3+"px";
}

// gautier #2672
function hideMenuBarShowMode() {
  hideShowMenu(false);
  dijit.byId("iconMenuUserScreen").closeDropDown();
}

//gautier menu top
function hideMenuBarShowModeTop(){ 
  if(dojo.byId('statusBarDiv').style.height == '0px'){
    saveDataToSession("hideMenuTop","NO",true);
    //dojo.byId('statusBarDiv').style.display='block';
    dojo.byId('statusBarDiv').style.height="48px";
    dojo.byId('statusBarDiv').style.padding="1px";
    dojo.byId('leftDiv').style.top='82px';
    dojo.byId('centerDiv').style.top='82px';
    dojo.byId('menuBarShow').style.top='82px';
    var height=parseInt(dojo.byId('mainDiv').offsetHeight)-82;
    dijit.byId('centerDiv').resize({h:height});
    dijit.byId('leftDiv').resize({h:height});
    if(dojo.byId('menuBarShow').style.display=='none' || dojo.byId('menuBarShow').style.display == ''){
      dojo.byId('leftDiv_splitter').style.top='82px';
      var height = dojo.byId("leftDiv").offsetHeight+50;
      dojo.byId('leftDiv_splitter').style.height=height+'px';
    }
  }else{
    saveDataToSession("hideMenuTop","YES",true);
    //dojo.byId('statusBarDiv').style.display='none';
    dojo.byId('statusBarDiv').style.height="0px";
    dojo.byId('statusBarDiv').style.padding="0px";
    dojo.byId('leftDiv').style.top='30px';
    dojo.byId('centerDiv').style.top='30px';
    dojo.byId('menuBarShow').style.top='30px';
    var height=parseInt(dojo.byId('mainDiv').offsetHeight)-30;
    dijit.byId('centerDiv').resize({h:height});
    dijit.byId('leftDiv').resize({h:height});
    if(dojo.byId('menuBarShow').style.display=='none' || dojo.byId('menuBarShow').style.display == '' ){
      dojo.byId('leftDiv_splitter').style.top='32px';
      var height = dojo.byId("leftDiv").offsetHeight;
      dojo.byId('leftDiv_splitter').style.height=height+'px';
    }
    if(switchedMode==true){
      switchModeOn();
    }
  }
  dijit.byId("iconMenuUserScreen").closeDropDown();
}

function menuClick() {
  if (menuHidden) {
    menuHidden=false;
    hideShowMenu(true);
    menuHidden=true;
  }
}

var switchedMode=false;
var loadingContentDiv=false;
var listDivSize=0;
var switchedVisible='';
var switchListMode='CLICK';

function switchModeOn(objectIdScreen){
  switchedMode=true;
  //dojo.byId("buttonSwitchModeLabel").innerHTML=i18n('buttonStandardMode');
  if (!dojo.byId("listDiv")) {
    if (listDivSize == 0) {
      listDivSize=dojo.byId("centerDiv").offsetHeight * .4;
    }
    return;
  } else {
    listDivSize=dojo.byId("listDiv").offsetHeight;
  }
  if (dojo.byId('listDiv_splitter')) {
    dojo.byId('listDiv_splitter').style.display='none';
  }
  if (dijit.byId('id')) {
    hideList();
  } else {
    loadingContentDiv=false;
    showList();
  }
}

function switchModeOff(){
  switchedMode=false;
  //dojo.byId("buttonSwitchModeLabel").innerHTML=i18n('buttonSwitchedMode');
  if (!dojo.byId("listDiv")) {
    return;
  }
  if (dojo.byId('listBarShow')) {
    dojo.byId('listBarShow').style.display='none';
  }
  if (dojo.byId('detailBarShow')) {
    dojo.byId('detailBarShow').style.display='none';
  }
  if (dojo.byId('listDiv_splitter')) {
    dojo.byId('listDiv_splitter').style.display='block';
  }
  if (listDivSize == 0) {
    listDivSize=dojo.byId("centerDiv").offsetHeight * .4;
  }
  dijit.byId("listDiv").resize({
    h : listDivSize
  });
  dijit.byId("mainDivContainer").resize();
}

function switchModeLayout(paramToSend){
  if(dojo.byId('objectClass')){
    var currentObject=dojo.byId('objectClass').value;
    var currentScreen=(dojo.byId('objectClassManual'))?dojo.byId('objectClassManual').value:'Object';
  }else{
    return;
  }
  if(checkFormChangeInProgress()){
    return;
  }
  var objectIdScreen=dojo.byId('objectId').value;
  var currentItem=historyTable[historyPosition];
  if (currentItem && currentItem!='undefined' && currentItem.length>2) currentScreen=currentItem[2];
  if(currentScreen=='Reports'){
    return false;
  }
  if (paramToSend=='top' || paramToSend=='left'){
      var paramDiv='paramScreen';
      if(switchedMode==true){
       switchModeOff();
      }
      switchModeLoad(currentScreen,currentObject,paramDiv,paramToSend,objectIdScreen);
  }else if(paramToSend=='bottom' || paramToSend=='trailing'){
    var paramDiv='paramRightDiv';
    switchModeLoad(currentScreen,currentObject,paramDiv,paramToSend,objectIdScreen);
  }else if(paramToSend=='col' || paramToSend=='tab'){
    var paramDiv='paramLayoutObjectDetail';
    switchModeLoad(currentScreen,currentObject,paramDiv,paramToSend,objectIdScreen);
  }else if (paramToSend=='switch'){
    var paramDiv='paramScreen';
    if(objectIdScreen!=null){
      loadingContentDiv=true;
    }
    switchModeLoad(currentScreen,currentObject,paramDiv,paramToSend,objectIdScreen);
    switchModeOn(objectIdScreen);
  }
  dijit.byId('iconMenuUserScreen').closeDropDown();
}

function switchModeLoad(currentScreen,currentObject,paramDiv,paramToSend,objectIdScreen){
  //var urlParams="?objectClass="+ currentObject+"&"+paramDiv+"="+paramToSend+"&objectId="+objectIdScreen;
  var urlParams="?"+paramDiv+"="+paramToSend;
  if (currentObject) urlParams+="&objectClass="+ currentObject;
  if (objectIdScreen) urlParams+="&objectId="+objectIdScreen;
  var urlPage="objectMain.php";
  if(currentScreen=='Planning'){
    urlPage="planningMain.php";
  }else if(currentScreen=='GlobalPlanning'){
    urlPage="globalPlanningMain.php";
  }else if(currentScreen=='PortfolioPlanning' ){
    urlPage="portfolioPlanningMain.php";
  }else if(currentScreen=='ResourcePlanning') {
    urlPage="resourcePlanningMain.php";
  }else if(currentScreen=='VersionsPlanning') {
    var productVersionsListId=dojo.byId('productVersionsListId').value;
    urlPage="versionsPlanningMain.php";
    urlParams+="&productVersionsListId="+productVersionsListId;
  }else if(currentScreen=='ContractGantt') {
    urlPage="contractGanttMain.php";
  }else if(currentScreen=='HierarchicalBudget') {
    urlPage="hierarchicalBudgetMain.php";
  }
  var callBack=null;
  if(objectIdScreen !=''){
    callBack=function(){loadContent("objectDetail.php", "detailDiv", 'listForm');};
  }
  if (dojo.byId('objectClass') && (dojo.byId('objectClass').value || urlPage!="objectMain.php")) {loadContent(urlPage+urlParams, "centerDiv",null,null,null,null,null,callBack);}
  loadDiv("menuUserScreenOrganization.php?currentScreen="+currentScreen+"&"+paramDiv+"="+paramToSend,"mainDivMenu");  
}
var switchModeSkipAnimation=true;
function showList(mode, skipAnimation) {
  duration=300;
  if (switchModeSkipAnimation) {
    skipAnimation=true;
    duration=0;
  }
  if (mode == 'mouse' && switchListMode == 'CLICK')
    return;
  if (!switchedMode) {
    return;
  }
  if (!dijit.byId("listDiv") || !dijit.byId("mainDivContainer")) {
    return;
  }
  if (dojo.byId('listDiv_splitter')) {
    setTimeout("dojo.byId('listDiv_splitter').style.display='none';",duration+50);
  }
  if (dojo.byId('listBarShow')) {
    setTimeout("dojo.byId('listBarShow').style.display='none';",duration+50);
  }
  correction=0;
  if (dojo.byId("listDiv").offsetHeight > 100)
    correction=5;
  fullSize=dojo.byId("listDiv").offsetHeight
      + dojo.byId("contentDetailDiv").offsetHeight -20+correction;
  if (skipAnimation || !isHtml5()) {
    dijit.byId("listDiv").resize({
      h : fullSize
    });
    duration=0;
  } else {
    dojox.fx.animateProperty({
      node : "listDiv",
      properties : {
        height : fullSize
      },
      duration : duration
    }).play();
  }
  if (dojo.byId('detailBarShow')) {
    setTimeout("dojo.byId('detailBarShow').style.display='block';",
        duration + 50);
  }
  resizeContainer("mainDivContainer", duration);
  switchedVisible='list';
}

function hideList(mode, skipAnimation) {
  duration=300; 
  if (mode == 'mouse' && switchListMode == 'CLICK'){
    return;
  }
  if (!switchedMode) {
    return;
  }
  if (!dijit.byId("listDiv") || !dijit.byId("mainDivContainer")) {
    return;
  }
  if (skipAnimation && dijit.byId("detailDiv")) {
    dijit.byId("detailDiv").set('content', '');
  }
  if (switchModeSkipAnimation) {
    skipAnimation=true;
    duration=0;
  }
  if (dojo.byId('listDiv_splitter')) {
    dojo.byId('listDiv_splitter').style.display='none';
  }
  if (dojo.byId('listBarShow')) {
    setTimeout("dojo.byId('listBarShow').style.display='block';",duration+50);
  }
  if (dojo.byId('detailBarShow')) {
    setTimeout("dojo.byId('detailBarShow').style.display='none';",duration+50);
  }
  if (!isHtml5() || skipAnimation) {
    dijit.byId("listDiv").resize({
      h : 20
    });
    duration=0;
  } else {
    dojox.fx.combine([ dojox.fx.animateProperty({
      node : "listDiv",
      properties : {
        height : 20
      },
      duration : duration
    }) ]).play();
  }
  resizeContainer("mainDivContainer", duration);
  switchedVisible='detail';
}

function resizeContainer(container, duration) {
  sequ=10;
  if (!dijit.byId(container)) return;
  if (duration) {
    for (var i=0; i < sequ; i++) {
      setTimeout('dijit.byId("' + container + '").resize();', i * duration / sequ);
    }
  }
  setTimeout('dijit.byId("' + container + '").resize();', duration + 10);
}

function listClick() {
  stockHistory(dojo.byId('objectClass').value, dojo.byId('objectId').value);
  if (!switchedMode ) {
    return;
  }
  setTimeout("hideList(null,true);", 1);
}

function consoleLogHistory(msg) {
  //KROWRY
  consoleTraceLog('====='+msg+'==== ('+historyTable.length+')');
  if (historyTable.length==0) {
    consoleTraceLog(msg+' => Empty');
  }
  for (var i=0;i<historyTable.length;i++) {
    current=historyTable[i];
    consoleTraceLog(msg+' => '+current[0]+ ' | '+current[1]+' | '+current[2]);
  }
}

function stockHistory(curClass, curId, currentScreen) {
  if (!currentScreen) {
    currentScreen="object";
    if (dojo.byId("objectClassManual")){
      currentScreen=dojo.byId("objectClassManual").value;
    }
  }
  if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value=='GlobalView' && curId) {
    curId=curClass+'|'+parseInt(curId);
    curClass=dojo.byId('objectClassList').value;
  }
  if (historyPosition>=0) {
    current=historyTable[historyPosition];
    if (current[0]==curClass && current[1]==curId && current[2]==currentScreen) return; // do not re-stock current item
    if (current[0]==curClass && current[1]==null && current[2]==currentScreen) historyPosition-=1; // previous is same class but with no selection, will overwrite
  }
  historyPosition+=1;
  historyTable[historyPosition]=new Array(curClass, curId,currentScreen);
  // Purge next history (not valid any more)
  for (var i=historyPosition+1;i<historyTable.length;i++) {
    historyTable.splice(i,1); 
  }
  if (historyPosition > 0) {
    enableWidget('menuBarUndoButton');
  }
  if (historyPosition == historyTable.length - 1) {
    disableWidget('menuBarRedoButton');
  }
}

function undoItemButton(curClass,curId) {
  var len=historyTable.length;
  if (len == 0) {
    return;
  }
  if (historyPosition == 0) {
    return;
  }
  historyPosition-=1;
  var currentItem=historyTable[historyPosition];
  var currentScreen=currentItem[2];
  var target="";
  if (currentScreen=="object" && currentItem[1]!=null){
    gotoElement(currentItem[0], currentItem[1], true, false, currentScreen);
  }else if (currentScreen=="object") {
    loadContent("objectMain.php?objectClass=" + currentItem[0],"centerDiv");
    //gautier #3413
  } else if (currentScreen=="Planning" && currentItem[1]!=null){ 
    gotoElement(currentItem[0], currentItem[1], false, false, "planning");
  }else if(currentScreen=='GanttSupplierContract' || currentScreen=='GanttClientContract'){
    if(currentScreen=='GanttClientContract'){
      loadMenuBarItem('GanttClientContract','menuGanttClientContract','bar');
    }else{
      loadMenuBarItem('GanttSupplierContract','menuGanttSupplierContract','bar');
    }
  }else if (currentScreen=='ContractGantt') {
    dojo.byId('objectClass').value=currentItem[0];
    loadContent('contractGanttMain.php?objectClass='+currentItem[0]+'&objectId='+currentItem[1], 'centerDiv');
    dojo.byId('objectClass').value = currentItem[0];
    dojo.byId('objectId').value = currentItem[1];
    loadContent('objectDetail.php', 'detailDiv', 'listForm');
    loadContentStream();
  }else if(currentScreen=='ConsultationValidation'){
    loadMenuBarItem('ConsultationValidation','menuConsultationValidation','bar');
  }else {
    target=getTargetFromCurrentScreen(currentScreen);
    loadContent(target,"centerDiv"); 
  }
  enableWidget('menuBarRedoButton');
  if (historyPosition == 0) {
    disableWidget('menuBarUndoButton');
  }
  selectIconMenuBar(currentItem[0]);
  if(isNewGui){
    refreshSelectedMenuLeft('menu'+currentItem[0]);
    refreshSelectedItem(currentItem[0], defaultMenu);
  }
}

function getTargetFromCurrentScreen(currentScreen){
  if (currentScreen=="Administration" || currentScreen=="Admin"){ 
    target="admin.php";
  } else if (currentScreen=="Import" || currentScreen=="ImportData"){ 
    target="importData.php";
  } else if (currentScreen=="DashboardTicket") {
    target="dashboardTicketMain.php";
  } else if (currentScreen=="DashboardRequirement") { //ADD qCazelles - Requirements dashboard - Ticket 90
	target="dashboardRequirementMain.php";
  } else if (currentScreen=="ActivityStream") {
    target="activityStreamMain.php";
  } else if (currentScreen=="Plugin"){ 
    target="pluginManagement.php";
  }else if (currentScreen=="Today"){ 
    target="today.php";
  } else if (currentScreen=="Kanban"){ 
    target="KanbanViewMain.php";
  } else if (currentScreen=="UserParameter") {
    target="parameter.php?type=userParameter";
  } else if (currentScreen=="ProjectParameter") {
    target="parameter.php?type=projectParameter";
  } else if (currentScreen=="GlobalParameter") {
    target="parameter.php?type=globalParameter";
  } else if (currentScreen=="Habilitation") {
    target="parameter.php?type=habilitation";
  } else if (currentScreen=="HabilitationReport") {
    target="parameter.php?type=habilitationReport";
  } else if (currentScreen=="HabilitationOther") {
    target="parameter.php?type=habilitationOther";
  } else if (currentScreen=="AccessRight") {
    target="parameter.php?type=accessRight";
  } else if (currentScreen=="AccessRightNoProject") {
    target="parameter.php?type=accessRightNoProject";
  } else if (pluginMenuPage['menu'+currentScreen]) {
    target=pluginMenuPage['menu'+currentScreen];
  } else {
    target=currentScreen.charAt(0).toLowerCase()+currentScreen.substr(1)+"Main.php";
  }
  return target;
}

function getTargetFromCurrentScreenChangeLang(currentScreen){
  if (currentScreen=="Administration" || currentScreen=="Admin"){ 
    target="admin.php";
  } else if (currentScreen=="Import" || currentScreen=="ImportData"){ 
    target="importData.php";
  } else if (currentScreen=="DashboardTicket") {
    target="dashboardTicketMain.php";
  } else if (currentScreen=="DashboardRequirement") { //ADD qCazelles - Requirements dashboard - Ticket 90
    target="dashboardRequirementMain.php";
  } else if (currentScreen=="ActivityStream") {
    target="activityStreamMain.php";
  } else if (currentScreen=="PlannedWorkManual") {
    target="plannedWorkManualMain.php";
  } else if (currentScreen=="Today"){ 
    target="today.php";
  } else {
    target="parameter.php";
  }
  return target;
}

function redoItemButton() {
  var len=historyTable.length;
  if (len == 0) {
    return;
  }  
  if (historyPosition == len - 1) {
    return;
  }
  historyPosition+=1;
  
  var currentItem=historyTable[historyPosition];
  var currentScreen=currentItem[2];
  var target="";
  if (currentScreen=="object" && currentItem[1]!=null){
    gotoElement(currentItem[0], currentItem[1], true, false, currentScreen);
  } else if (currentScreen=="object") {
    loadContent("objectMain.php?objectClass=" + currentItem[0],"centerDiv");
  //gautier
  } else if (currentScreen=="Planning" && currentItem[1]!=null){ 
    gotoElement(currentItem[0], currentItem[1], false, false, "planning");
  }else if(currentScreen=='GanttSupplierContract' || currentScreen=='GanttClientContract'){
    if(currentScreen=='GanttClientContract'){
      loadMenuBarItem('GanttClientContract','menuGanttClientContract','bar');
    }else{
      loadMenuBarItem('GanttSupplierContract','menuGanttSupplierContract','bar');
    }
  }else if (currentScreen=='ContractGantt') {
    dojo.byId('objectClass').value=currentItem[0];
    loadContent('contractGanttMain.php?objectClass='+currentItem[0]+'&objectId='+currentItem[1], 'centerDiv');
    dojo.byId('objectClass').value = currentItem[0];
    dojo.byId('objectId').value = currentItem[1];
    loadContent('objectDetail.php', 'detailDiv', 'listForm');
    loadContentStream();
  }else {
    target=getTargetFromCurrentScreen(currentScreen);
    loadContent(target,"centerDiv"); 
  }
  enableWidget('menuBarUndoButton');
  if(isNewGui){
    refreshSelectedMenuLeft('menu'+currentItem[0]);
  }
  if (historyPosition == (len - 1)) {
    disableWidget('menuBarRedoButton');
  }
  selectIconMenuBar(currentItem[0]);
  getTargetFromCurrentScreen(currentScreen);
}

// Stock id and name, to
// => avoid filterJsonList to reduce visibility => clear this data on open
// => retrieve data before close to retrieve the previous visibility
var quickSearchStockId=null;
var quickSearchStockName=null;
var quickSearchIsOpen=false;

function quickSearchOpen() {
  dojo.style("quickSearchDiv", "display", "block");
  if (dijit.byId("listTypeFilter")) {
    dojo.style("listTypeFilter", "display", "none");
  }
  if (dijit.byId("listClientFilter")) {
    dojo.style("listClientFilter", "display", "none");
  }
  if (dijit.byId("listElementableFilter")) {
    dojo.style("listElementableFilter", "display", "none");
  }
  quickSearchStockId=dijit.byId('listIdFilter').get("value");
  if (dijit.byId('listNameFilter')) {
    quickSearchStockName=dijit.byId('listNameFilter').get("value");
    dojo.style("listNameFilter", "display", "none");
    dijit.byId('listNameFilter').reset();
  }
  dijit.byId('listIdFilter').reset();
  dojo.style("listIdFilter", "display", "none");
  dijit.byId("quickSearchValue").reset();
  dijit.byId("quickSearchValue").focus();
  quickSearchIsOpen=true;
}

function quickSearchClose() {
  quickSearchIsOpen=false;
  dojo.style("quickSearchDiv", "display", "none");
  if (dijit.byId("listTypeFilter")) {
    dojo.style("listTypeFilter", "display", "block");
  }
  if (dijit.byId("listClientFilter")) {
    dojo.style("listClientFilter", "display", "block");
  }
  if (dijit.byId("listElementableFilter")) {
    dojo.style("listElementableFilter", "display", "block");
  }
  dojo.style("listIdFilter", "display", "block");
  if (dijit.byId('listNameFilter')) {
    dojo.style("listNameFilter", "display", "block");
    dijit.byId('listNameFilter').set("value", quickSearchStockName);
  }
  dijit.byId("quickSearchValue").reset();
  dijit.byId('listIdFilter').set("value", quickSearchStockId);
  var objClass=(dojo.byId('objectClassList'))?dojo.byId('objectClassList').value:dojo.byId('objectClass').value;
  refreshJsonList(objClass);
}

function quickSearchCloseQuick() {
  dijit.byId("quickSearchValueQuick").reset();
  var objClass=(dojo.byId('objectClassList'))?dojo.byId('objectClassList').value:dojo.byId('objectClass').value;
  refreshJsonList(objClass);
}
function quickSearchExecute() {
  if (!quickSearchIsOpen) {
    return;
  }
  if (!dijit.byId("quickSearchValue").get("value")) {
    showInfo(i18n('messageMandatory', new Array(i18n('quickSearch'))));
    return;
  }
  var objClass=(dojo.byId('objectClassList'))?dojo.byId('objectClassList').value:dojo.byId('objectClass').value;
  refreshJsonList(objClass);
}

function quickSearchExecuteQuick() {
  if (!dijit.byId("quickSearchValueQuick").get("value")) {
    showInfo(i18n('messageMandatory', new Array(i18n('quickSearch'))));
    return;
  }
  var objClass=(dojo.byId('objectClassList'))?dojo.byId('objectClassList').value:dojo.byId('objectClass').value;
  refreshJsonList(objClass);
}

/*
 * ========================================== Copy functions
 */
function copyObject(objectClass) {
  dojo.byId("copyButton").blur();
  action=function() {
    unselectAllRows('objectGrid');
    loadContent("../tool/copyObject.php", "resultDivMain", 'objectForm', true);
  };
  showConfirm(i18n("confirmCopy", new Array(i18n(objectClass),
      dojo.byId('id').value)), action);
}

function copyLinkTo(objectClass) {
  dojo.byId("copyButton").blur();
  action=function() {
    unselectAllRows('objectGrid');
    loadContent("../tool/copyObject.php", "linkRef2Id", 'objectForm', true);
  };
  showConfirm(i18n("confirmCopy", new Array(i18n(objectClass),
      dojo.byId('id').value)), action);
}

function copyObjectToShowStructure() {
  if (dojo.byId('copyClass').value == 'Activity'
      && copyableArray[dijit.byId('copyToClass').get('value')] == 'Activity') {
    dojo.byId('copyWithStructureDiv').style.display='block';
  } else {
    dojo.byId('copyWithStructureDiv').style.display='none';
  }
}

function copyObjectToSubmit(objectClass) {
  var formVar=dijit.byId('copyForm');
  if (!formVar.validate()) {
    showAlert(i18n("alertInvalidForm"));
    return;
  }
  unselectAllRows('objectGrid');
  loadContent("../tool/copyObjectTo.php", "resultDivMain", 'copyForm', true,
      'copyTo');
  dijit.byId('dialogCopy').hide();
}
//gautier #2522
function copyDocumentToSubmit(objectClass) {
  loadContent("../tool/copyDocumentTo.php", "resultDivMain", 'copyDocumentForm', true );
  dijit.byId('dialogCopyDocument').hide();
}

function copyProjectToSubmit(objectClass) {
  var formVar=dijit.byId('copyProjectForm');
  if (!formVar.validate()) {
    showAlert(i18n("alertInvalidForm"));
    return;
  }
  unselectAllRows('objectGrid');
  loadContent("../tool/copyProjectTo.php", "resultDivMain", 'copyProjectForm',
      true, 'copyProject');
  dijit.byId('dialogCopy').hide();
}

function copyProjectStructureChange() {
  var cpStr=dijit.byId('copyProjectStructure');
  if (cpStr) {
    if (!cpStr.get('checked')) {
      dijit.byId('copyProjectAssignments').set('checked', false);
      dijit.byId('copyProjectAssignments').set('readOnly', 'readOnly');
    } else {
      dijit.byId('copyProjectAssignments').set('readOnly', false);
    }
  }
}

function selectIconMenuBar(menuClass){
	var icon = dojo.byId('iconMenuBar'+menuClass);
	dojo.query('.menuBarItem').removeClass('menuBarItemSelected', icon);
	if (icon && dojo.hasClass(icon,'menuBarItem')){
		dojo.addClass(icon,'menuBarItemSelected');
	}
}

function loadMenuBarObject(menuClass, itemName, from) {
  if (checkFormChangeInProgress()) {
    return false;
  }
  currentPluginPage=null;
  if (from == 'bar' && !isNewGui) {
    selectTreeNodeById(dijit.byId('menuTree'), menuClass);
  }
  hideResultDivs();
  cleanContent("detailDiv");
  formChangeInProgress=false;
  var objectExist='true';
  var currentScreen=menuClass; 
  loadContent("objectMain.php?objectClass=" + currentScreen, "centerDiv"); 
  loadDiv("menuUserScreenOrganization.php?currentScreen="+currentScreen+'&objectExist='+objectExist,"mainDivMenu");
  stockHistory(currentScreen,null,"object");
  selectIconMenuBar(menuClass);
  if(isNewGui){
	  refreshSelectedItem(menuClass, defaultMenu);
	  if(defaultMenu == 'menuBarRecent'){
		  menuNewGuiFilter(defaultMenu, menuClass);
	  }
	  editFavoriteRow(true);
  }
  return true;
}
function hideResultDivs() {
  if (dojo.byId('resultDivMain')) {
      dojo.byId('resultDivMain').style.display='none';
  }
}

function loadMenuBarItem(item, itemName, from) {
  if (checkFormChangeInProgress()) {
    return false;
  }
  currentPluginPage=null;
  if (from == 'bar' && !isNewGui) {
    selectTreeNodeById(dijit.byId('menuTree'), item);
  }
  cleanContent("detailDiv");
  hideResultDivs();
  formChangeInProgress=false;
  var currentScreen=item;
  var objectExist='false';
  if (item == 'Today') {
    loadContent("today.php", "centerDiv");
  } else if (item == 'Planning') {
    objectExist='true';
    vGanttCurrentLine=-1;
    cleanContent("centerDiv");
    loadContent("planningMain.php", "centerDiv");
  } else if (item == 'PortfolioPlanning') {
    objectExist='true';
    vGanttCurrentLine=-1;
    cleanContent("centerDiv");
    loadContent("portfolioPlanningMain.php", "centerDiv");
  } else if (item == 'ResourcePlanning') {
    objectExist='true';
    vGanttCurrentLine=-1;
    cleanContent("centerDiv");
    loadContent("resourcePlanningMain.php", "centerDiv");
  } else if (item == 'GlobalPlanning') {
    objectExist='true';
    vGanttCurrentLine=-1;
    cleanContent("centerDiv");
    loadContent("globalPlanningMain.php", "centerDiv");
  } else if (item == 'HierarchicalBudget') {
	objectExist='true';
    vGanttCurrentLine=-1;
    cleanContent("centerDiv");
    loadContent("hierarchicalBudgetMain.php", "centerDiv");
  } else if (item == 'GanttClientContract' || item == 'GanttSupplierContract') {
    var object="SupplierContract";
    if(item == 'GanttClientContract'){
      object="ClientContract";
    }
    objectExist='true';
    vGanttCurrentLine=-1;
    cleanContent("centerDiv");
    loadContent("contractGanttMain.php?objectClass="+object, "centerDiv");
  } else if (item == 'Imputation') {
    loadContent("imputationMain.php", "centerDiv");
  } else if (item == 'Diary') {
    loadContent("diaryMain.php", "centerDiv");
  } else if (item == 'ActivityStream') {
    loadContent("activityStreamMain.php", "centerDiv");
  } else if (item == 'ImportData') {
    loadContent("importData.php", "centerDiv");
  } else if (item == 'Reports') {
    loadContent("reportsMain.php", "centerDiv");
  } else if (item == 'Absence') {
	    loadContent("absenceMain.php", "centerDiv");
  } else if (item == 'PlannedWorkManual' || item=='ConsultationPlannedWorkManual') {
    var param='false';
    if (item=='ConsultationPlannedWorkManual')param='true';
    loadContent("plannedWorkManualMain.php?readonly="+param, "centerDiv");
  }else if (item == 'ImputationValidation') {
	    loadContent("imputationValidationMain.php", "centerDiv");  
  }else if(item == 'ConsultationValidation'){
    loadContent("consolidationValidationMain.php", "centerDiv");  
  }else if (item == 'AutoSendReport') {
    loadContent("autoSendReportMain.php", "centerDiv"); 
  } else if (item == 'DataCloning') {
		loadContent("dataCloningMain.php", "centerDiv");
  } else if (item == 'DataCloningParameter') {
		loadContent("dataCloningParameterMain.php", "centerDiv");
    //ADD qCazelles - GANTT
  } else if (item == 'VersionsPlanning'  ) {
    objectExist='true';
	//CHANGE qCazelles - Correction GANTT - Ticket #100
	//Old
	//loadDialog("dialogVersionsPlanning", null, true, null, false);
	//New
	showDetail('versionsPlanningDetail', false, 'ProductVersion', true);
	//END CHANGE qCazelles - Correction GANTT - Ticket #100
	//END ADD qCazelles - GANTT
  }else if(item=='VersionsComponentPlanning'){
    showDetail('versionsComponentPlanningDetail', false, 'ComponentVersion', true);
  }else if (item == 'UserParameter') {
    loadContent("parameter.php?type=userParameter", "centerDiv");
  } else if (item == 'ProjectParameter') {
    loadContent("parameter.php?type=projectParameter", "centerDiv");
  } else if (item == 'GlobalParameter') {
    loadContent("parameter.php?type=globalParameter", "centerDiv");
  } else if (item == 'Habilitation') {
    loadContent("parameter.php?type=habilitation", "centerDiv");
  } else if (item == 'HabilitationReport') {
    loadContent("parameter.php?type=habilitationReport", "centerDiv");
  } else if (item == 'HabilitationOther') {
    loadContent("parameter.php?type=habilitationOther", "centerDiv");
  } else if (item == 'AccessRight') {
    loadContent("parameter.php?type=accessRight", "centerDiv");
  } else if (item == 'AccessRightNoProject') {
    loadContent("parameter.php?type=accessRightNoProject", "centerDiv");
  } else if (item == 'Admin') {
    loadContent("admin.php", "centerDiv");
  } else if (item == 'Plugin' || item == 'PluginManagement') {
    loadContent("pluginManagement.php", "centerDiv");
  } else if (item == 'Calendar') {
    // loadContent("calendar.php","centerDiv");
    loadContent("objectMain.php?objectClass=CalendarDefinition", "centerDiv");
  } else if (item == 'Gallery') {
    loadContent("galleryMain.php", "centerDiv");
  } else if (item == 'DashboardTicket') {
    loadContent("dashboardTicketMain.php", "centerDiv");
  } else if (item == 'DashboardRequirement') {  //ADD qCazelles - Requirements dashboard - Ticket 90
	loadContent("dashboardRequirementMain.php", "centerDiv");
  } else if (pluginMenuPage && pluginMenuPage['menu'+item]) {
    loadMenuBarPlugin(item, itemName, from);
// ELIOTT - LEAVE SYSTEM    
  } else if(item == "LeaveCalendar"){
      loadContent("leaveCalendar.php", "centerDiv");
  } else if(item == "LeavesSystemHabilitation"){
      loadContent("leavesSystemHabilitation.php", "centerDiv");
  } else if(item == "DashboardEmployeeManager"){
      loadContent("dashboardEmployeeManager.php", "centerDiv");
// ELIOTT - LEAVE SYSTEM    
  } else if(item == "Module"){
    loadContent("moduleView.php", "centerDiv");
  } else if(item == "Kanban"){
    loadContent("kanbanViewMain.php", "centerDiv");
  }else {  
    showInfo(i18n("messageSelectedNotAvailable", new Array(itemName)));
  }
  loadDiv("menuUserScreenOrganization.php?currentScreen="+currentScreen+'&objectExist='+objectExist,"mainDivMenu");
  stockHistory(item,null,currentScreen);
  selectIconMenuBar(item);
  if(isNewGui){
	  refreshSelectedItem(item, defaultMenu);
	  if(defaultMenu == 'menuBarRecent'){
		  menuNewGuiFilter(defaultMenu, item);
	  }
	  editFavoriteRow(true);
  }
  return true;
}

var currentPluginPage=null;
function loadMenuBarPlugin(item, itemName, from) {
  if (checkFormChangeInProgress()) {
    return false;
  }
  if (! pluginMenuPage || ! pluginMenuPage['menu'+item]) {
    showInfo(i18n("messageSelectedNotAvailable", new Array(item.name)));
    return;
  }
  hideResultDivs();
  currentPluginPage=pluginMenuPage['menu'+item];
  loadContent(pluginMenuPage['menu'+item], "centerDiv");
  if(isNewGui){
	  refreshSelectedItem(item, itemName);
	  if(defaultMenu == 'menuBarRecent'){
		  menuNewGuiFilter(defaultMenu, item);
	  }
	  editFavoriteRow(true);
  }
  return currentPluginPage;
}

var customMenuAddRemoveTimeout=null;
var customMenuAddRemoveTimeoutDelay=3000;
var customMenuAddRemoveClass=null;
function customMenuManagement(menuClass) {
  var button=dojo.byId('iconMenuBar'+menuClass);
  offsetbutton=button.offsetLeft+dojo.byId('menuBarVisibleDiv').offsetLeft+dojo.byId('menubarContainer').offsetLeft;
  if ( dojo.hasClass(button,'menuBarCustom') ) {
    clearTimeout(customMenuAddRemoveTimeout);
    dojo.byId('customMenuAdd').style.display='none';
    customMenuAddRemoveClass=menuClass;
    dojo.byId('customMenuRemove').style.left=offsetbutton+'px';
    dojo.byId('customMenuRemove').style.display='block';
    customMenuAddRemoveTimeout=setTimeout("dojo.byId('customMenuRemove').style.display='none';",customMenuAddRemoveTimeoutDelay);
  } else {
    clearTimeout(customMenuAddRemoveTimeout);
    dojo.byId('customMenuRemove').style.display='none';
    customMenuAddRemoveClass=menuClass;
    dojo.byId('customMenuAdd').style.left=offsetbutton+'px';
    dojo.byId('customMenuAdd').style.display='block';
    customMenuAddRemoveTimeout=setTimeout("dojo.byId('customMenuAdd').style.display='none';",customMenuAddRemoveTimeoutDelay);
  }
}

function customMenuAddItem() {
  var param="?operation=add&class="+customMenuAddRemoveClass;
  dojo.xhrGet({
    url : "../tool/saveCustomMenu.php"+param,
    handleAs : "text",
    load : function(data, args) {
    },
  });
  dojo.addClass('iconMenuBar'+customMenuAddRemoveClass,'menuBarCustom');
  if(!isNewGui){
	  dojo.byId('customMenuAdd').style.display='none';
  }else{
	  hideFavoriteTooltip(0, customMenuAddRemoveClass);
  }
}

function customMenuRemoveItem() {
  var param="?operation=remove&class="+customMenuAddRemoveClass;
  dojo.xhrGet({
    url : "../tool/saveCustomMenu.php"+param,
    handleAs : "text",
    load : function(data, args) {
      if (data=='menuBarCustom') {
        dojo.byId('iconMenuBar'+customMenuAddRemoveClass).style.display="none";
      }
    },
  });
  dojo.removeClass('iconMenuBar'+customMenuAddRemoveClass,'menuBarCustom');
  if(!isNewGui){
	  dojo.byId('customMenuRemove').style.display='none';
  }else{
	  hideFavoriteTooltip(0, customMenuAddRemoveClass);
  }
}

function showIconViewSubMenu(comboName){
	var name = comboName+'IconViewSubMenu';
	var offsetLeft=dojo.byId(comboName+'ButtonDetail').offsetLeft;
	if(dojo.byId(name).style.display == 'none'){
		dojo.byId(name).style.left=offsetLeft+'px';
		dojo.byId(name).style.display='block';
	}else{
		dojo.byId(name).style.display='none';
	}
	var val=null;
	if (dijit.byId(comboName)) {
		val=dijit.byId(comboName).get('value');
	}
	if(!val || val == "" || val == " " || val == "*"){
		dojo.byId(comboName+'SubViewItem').style.display='none';
	}
}

var hideIconViewSubMenuTimeOut;
function hideIconViewSubMenu(col){
	var name = col+'IconViewSubMenu';
	if(hideIconViewSubMenuTimeOut){
		clearTimeout(hideIconViewSubMenuTimeOut);
	}
	hideIconViewSubMenuTimeOut=setTimeout("dojo.byId("+name+").style.display='none';",300);
}
// ====================================================================================
// ALERTS
// ====================================================================================
//
// var alertDisplayed=false;
var checkAlertDisplayQuick=false;
function checkAlert() {
  // if (alertDisplayed) return;
  dojo.xhrGet({
    url : "../tool/checkAlertToDisplay.php",
    handleAs : "text",
    load : function(data, args) {
      checkAlertRetour(data);
    },
    error : function() {
      if (alertCheckTime>0) setTimeout('checkAlert();', alertCheckTime * 1000);
    }
  });
}
function checkAlertRetour(data) {
  if (data) {
    if (data.indexOf('name="lastOperation" value="testConnection"')>0 && data.indexOf('name="lastOperationStatus" value="ERROR"')>0) {
      showDisconnectedMessage(data);
    }
    var reminderDiv=dojo.byId('reminderDiv');
    var dialogReminder=dojo.byId('dialogReminder');
    reminderDiv.innerHTML=data;
    if (dojo.byId("cronStatusRefresh") && dojo.byId("cronStatusRefresh").value != "") {
      refreshCronIconStatus(dojo.byId("cronStatusRefresh").value);
    }
    if (dojo.byId("requestRefreshProject")&& dojo.byId("requestRefreshProject").value == "true") {
      refreshProjectSelectorList();
      if (alertCheckTime>0) setTimeout('checkAlert();', alertCheckTime * 1000);
    } else if (dojo.byId("alertNeedStreamRefresh") && dojo.byId("alertNeedStreamRefresh").value>0) {
      loadContent("objectStream.php?onlyCenter=true", "activityStreamCenter", "listForm");
      if (alertCheckTime>0) setTimeout('checkAlert();', alertCheckTime * 1000);
    } else if (dojo.byId('alertType')) {
      if (dojo.byId('alertCount') && dojo.byId('alertCount').value>1) {
        dijit.byId('markAllAsReadButton').set('label',i18n('markAllAsRead',new Array(dojo.byId('alertCount').value)));
        dojo.byId("markAllAsReadButtonDiv").style.display="inline";
      } else {
        dojo.byId("markAllAsReadButtonDiv").style.display="none";
      }
      dojo.style(dialogReminder, {
        visibility : 'visible',
        display : 'inline',
        bottom : '-200px'
      });
      var toColor='#FFCCCC';
      if (dojo.byId('alertType') && dojo.byId('alertType').value == 'WARNING') {
        toColor='#FFFFCC';
      }
      if (dojo.byId('alertType') && dojo.byId('alertType').value == 'INFO') {
        toColor='#CCCCFF';
      }
      var duration=2000;
      if (checkAlertDisplayQuick) duration=200;
      dojo.animateProperty({
        node : dialogReminder,
        properties : {
          bottom : {
            start : -200,
            end : 0
          },
          right : 0,
          backgroundColor : {
            start : '#FFFFFF',
            end : toColor
          }
        },
        duration : duration
      }).play();
    } else {
      if (alertCheckTime>0) setTimeout('checkAlert();', alertCheckTime * 1000);
    }
  } else {
    if (alertCheckTime>0) setTimeout('checkAlert();', alertCheckTime * 1000);
  }
  checkAlertDisplayQuick=false;
  if (dojo.byId("alertCount")) {
    if (dojo.byId("alertCount").value>1) {
      checkAlertDisplayQuick=true;
    }
  }
}
function showDisconnectedMessage(data) {
  dojo.byId('disconnectionMessageText').innerHTML=data;
  dojo.byId('disconnectionMessage').style.display='block';
}
function setAlertReadMessage() {
  // alertDisplayed=false;
  closeAlertBox();
  if (dojo.byId('idAlert') && dojo.byId('idAlert').value) {
    setAlertRead(dojo.byId('idAlert').value);
  }
}
function setAllAlertReadMessage() {
  // alertDisplayed=false;
  checkAlertDisplayQuick=false;
  closeAlertBox();
  setAlertRead('*');
}
function setAlertReadMessageInForm() {
  dijit.byId('readFlag').set('checked', 'checked');
  submitForm("../tool/saveObject.php", "resultDivMain", "objectForm", true);
}
function setAlertRemindMessage() {
  closeAlertBox();
  if (dojo.byId('idAlert') && dojo.byId('idAlert').value) {
    setAlertRead(dojo.byId('idAlert').value, dijit.byId('remindAlertTime').get(
        'value'));
  }
}

function setAlertRead(id, remind) {
  var url="../tool/setAlertRead.php?idAlert=" + id;
  if (remind) {
    url+='&remind=' + remind;
  }
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data, args) {
      setTimeout('checkAlert();', 100);
    },
    error : function() {
      setTimeout('checkAlert();', 100);
    }
  });
}

function closeAlertBox() {
  var dialogReminder=dojo.byId('dialogReminder');
  var duration=900;
  if (checkAlertDisplayQuick && dialogReminder) duration=90;
  dojo.animateProperty({
    node : dialogReminder,
    properties : {
      bottom : {
        start : 0,
        end : -200
      }
    },
    duration : duration,
    onEnd : function() {
      if (dojo.byId('dialogReminder')) {
        dialogReminder=dojo.byId('dialogReminder');
        dojo.style(dialogReminder, {
          visibility : 'hidden',
          display : 'none',
          bottom : '-200px'
        });
      }
    }
  }).play();
}

function setReadMessageLegalFollowup(idMessageLegal){
  var param="?idMessageLegal="+idMessageLegal;
  dojo.xhrGet({
    url : "../tool/saveMessageLegalFollowup.php"+param,
    handleAs : "text",
    load : function(data, args) {
    },
  });
}

function setNewGui(idMessageLegal, newGuiActivated){
	var param="?idMessageLegal="+idMessageLegal+"&newGuiActivated="+newGuiActivated;
	  dojo.xhrGet({
	    url : "../tool/saveMessageLegalFollowup.php"+param,
	    handleAs : "text",
	    load : function(data, args) {
	    	if(newGuiActivated){
	    	  showWait();
	          noDisconnect=true;
	          quitConfirmed=true;        
	          dojo.byId("directAccessPage").value="today.php";
	          dojo.byId("directAccessForm").submit();
	    	}
	    },
	  });
}
// ===========================================================================================
// ADMIN functionalities
// ===========================================================================================
//
var cronCheckIteration=50; // Number of cronCheckTimeout to wait max
function adminLaunchScript(scriptName,needRefresh) {
  if(typeof needRefresh == 'undefined')needRefresh=true;
  var url="../tool/" + scriptName + ".php";
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data, args) {
      
    },
    error : function() {
    }
  });
  if (scriptName == 'cronRun') {
    if(needRefresh)setTimeout('loadContent("admin.php","centerDiv");', 3000);
  } else if (scriptName == 'cronStop' && needRefresh) {
    i=120;
    cronCheckIteration=5 * cronSleepTime;
    setTimeout('adminCronCheckStop();', 1000);
  }
}

function adminCronCheckStop() {
  dojo.xhrGet({
    url : "../tool/cronCheck.php",
    handleAs : "text",
    load : function(data, args) {
      if (data != 'running') {
        loadContent("admin.php", "centerDiv");
      } else {
        cronCheckIteration--;
        if (cronCheckIteration > 0) {
          setTimeout('adminCronCheckStop();', 1000);
        } else {
          loadContent("admin.php", "centerDiv");
        }
      }
    },
    error : function() {
      loadContent("admin.php", "centerDiv");
    }
  });
}

function adminCronRelaunch() {
  var url="../tool/cronRelaunch.php";
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data, args) {
    },
    error : function() {
    }
  });
}

function adminCronRestart() {
  if (cronCheckCount>0) return;
  cronCheckCount=1;
  dojo.xhrGet({
    url : "../tool/cronStop.php",
    handleAs : "text",
    load : function(data, args) {
      setTimeout("adminCronRestartCheck();",1000);
    }
  });
}
var cronCheckCount=0;
function adminCronRestartCheck() {
  dojo.xhrGet({
    url : "../tool/cronCheck.php",
    handleAs : "text",
    load : function(data, args) {
      if (data == 'running') {
        cronCheckCount++;
        if (cronCheckCount<60) setTimeout("adminCronRestartCheck();",1000);
      } else {
        adminCronRestartRun();
      }
    }
  });
}
function adminCronRestartRun() {
  dojo.xhrGet({
    url : "../tool/cronRun.php",
    handleAs : "text",
    load : function(data, args) {
    }
  });
  cronCheckCount=0;
}
function adminSendAlert() {
  formVar=dijit.byId("adminForm");
  if (formVar.validate()) {
    loadContent(
        "../tool/adminFunctionalities.php?adminFunctionality=sendAlert",
        "resultDivMain", "adminForm", true, 'admin');
  }
}

// MTY - LEAVE SYSTEM
// Add param 'toConfirm' set to true to adminDisconnectAll 
// MTY - LEAVE SYSTEM
//function adminDisconnectAll() {
function adminDisconnectAll(toConfirm) {
  actionOK=function() {
    loadContent(
        "../tool/adminFunctionalities.php?adminFunctionality=disconnectAll&element=Audit",
        "resultDivMain", "adminForm", true, 'admin');
  };
  if (toConfirm) {
  msg=i18n('confirmDisconnectAll');
  showConfirm(msg, actionOK);
}
}

function maintenance(operation, item) {
  if (operation == "updateReference") {
    loadContent("../tool/adminFunctionalities.php?adminFunctionality="
        + operation + "&element=" + item, "resultDivMain", "adminForm", true,
        'admin');
  } else {
    var nb=0;
    if (operation!='read') {
      nb=dijit.byId(operation + item + "Days").get('value');
    }
    loadContent(
        "../tool/adminFunctionalities.php?adminFunctionality=maintenance&operation="
            + operation + "&item=" + item + "&nbDays=" + nb, "resultDivMain",
        "adminForm", true, 'admin');
  }
}
function adminSetApplicationTo(newStatus) {
  var url="../tool/adminFunctionalities.php?adminFunctionality=setApplicationStatusTo&newStatus="
      + newStatus;
  showWait();
  dojo.xhrPost({
    url : url,
    form : "adminForm",
    handleAs : "text",
    load : function(data, args) {
      loadContent("../view/admin.php", "centerDiv");
    },
    error : function() {
    }
  });
}

function lockDocument() {
  if (checkFormChangeInProgress()) {
    return false;
  }
  dijit.byId('locked').set('checked', true);
  dijit.byId('idLocker').set('value', dojo.byId('idCurrentUser').value);
  var curDate=new Date();
  dijit.byId('lockedDate').set('value', curDate);
  dijit.byId('lockedDateBis').set('value', curDate);
  formChanged();
  submitForm("../tool/saveObject.php", "resultDivMain", "objectForm", true);
  return true;
}

function unlockDocument() {
  if (checkFormChangeInProgress()) {
    return false;
  }
  dijit.byId('locked').set('checked', false);
  dijit.byId('idLocker').set('value', null);
  dijit.byId('lockedDate').set('value', null);
  dijit.byId('lockedDateBis').set('value', null);
  formChanged();
  submitForm("../tool/saveObject.php", "resultDivMain", "objectForm", true);
  return true;
}

/*
 * ========================================================================
 * Planning columns management
 * ========================================================================
 */
function openPlanningColumnMgt() {
  // alert("openPlanningColumnMgt");
}

function changePlanningColumn(col, status, order) {
  if (status) {
    // order=planningColumnOrder.indexOf('Hidden'+col);
    order=dojo.indexOf(planningColumnOrder, 'Hidden' + col);
    planningColumnOrder[order]=col;
    movePlanningColumn(col, col);
  } else {
    // order=planningColumnOrder.indexOf(col);
    order=dojo.indexOf(planningColumnOrder, col);
    planningColumnOrder[order]='Hidden' + col;
  } 
  //moveListColumn(); // Removed as sets error 
  if (col=='IdStatus' || col=='Type') {
    validatePlanningColumnNeedRefresh=true;
  }
  setPlanningFieldShow(col,status);
  dojo.xhrGet({
    url : '../tool/savePlanningColumn.php?action=status&status='
        + ((status) ? 'visible' : 'hidden') + '&item=' + col,
    handleAs : "text",
    load : function(data, args) {
    },
    error : function() {
    }
  });
}
function changePlanningColumnWidth(col, width) {
  setPlanningFieldWidth(col,width);
  showWait();
  JSGantt.changeFormat(g.getFormat(), g);
  dojo.xhrGet({
    url : '../tool/savePlanningColumn.php?action=width&width='+width+'&item=' + col,
    handleAs : "text",
    load : function(data, args) {
    },
    error : function() {
    }
  });
  hideWait();
}
var validatePlanningColumnNeedRefresh=false;
function validatePlanningColumn() {
  dijit.byId('planningColumnSelector').closeDropDown();
  showWait();
  setGanttVisibility(g);
  if (validatePlanningColumnNeedRefresh) { 
    refreshJsonPlanning();
  } else {
    JSGantt.changeFormat(g.getFormat(), g);
    hideWait();
  }
  validatePlanningColumnNeedRefresh=false;
}

function movePlanningColumn(source, destination) {
  var mode='';
  var list='';
  var nodeList=dndPlanningColumnSelector.getAllNodes();
  planningColumnOrder=new Array();
  for (var i=0; i < nodeList.length; i++) {
    var itemSelected=nodeList[i].id.substr(14);
    check=(dijit.byId('checkColumnSelector' + itemSelected).get('checked')) ? ''
        : 'hidden';
    list+=itemSelected + "|";
    planningColumnOrder[i]=check + itemSelected;
  }
  //alert(planningColumnOrder);
  
  var url='../tool/movePlanningColumn.php?orderedList=' + list;
  dojo.xhrPost({
    url : url,
    handleAs : "text",
    load : function(data, args) {
    }
  });
  // loadContent(url, "resultDivMain");
}

function moveBudgetFromHierarchicalView(idFrom, idTo){
  var mode = 'before';
  dndSourceTableBudget.sync();
  var nodeList = dndSourceTableBudget.getAllNodes();
  for (i = 0; i < nodeList.length; i++) {
    if  (nodeList[i].id == idFrom) {
      mode = 'before';
      break;
    } else if (nodeList[i].id == idTo) {
      mode = 'after';
      break;
    }
  }
	 var url='../tool/moveBudgetFromHierarchicalView.php?idFrom=' + idFrom +'&idTo='+idTo+'&mode='+mode;
	  dojo.xhrPost({
	    url : url,
	    handleAs : "text",
	    load : function() {
	    	refreshHierarchicalBudgetList();
	    }
	  });
}

/*
 * ======================================================================== List
 * columns management
 * ========================================================================
 */

function changeListColumn(tableId, fieldId, status, order) {
  var spinner=dijit.byId('checkListColumnSelectorWidthId' + fieldId);
  spinner.set('disabled', !status);
  dojo.xhrGet({
    url : '../tool/saveSelectedColumn.php?action=status&status='
        + ((status) ? 'visible' : 'hidden') + '&item=' + tableId,
    handleAs : "text",
    load : function(data, args) {
    },
    error : function() {
    }
  });
  recalculateColumnSelectorName();
}

function changeListColumnWidth(tableId, fieldId, width) {
  if (width < 1) {
    width=1;
    dijit.byId('checkListColumnSelectorWidthId' + fieldId).set('value', width);
  } else if (width > 50) {
    width=50;
    dijit.byId('checkListColumnSelectorWidthId' + fieldId).set('value', width);
  }
  dojo.xhrGet({
    url : '../tool/saveSelectedColumn.php?action=width&item=' + tableId
        + '&width=' + width,
    handleAs : "text",
    load : function(data, args) {
    },
    error : function() {
    }
  });
  recalculateColumnSelectorName();
}

function validateListColumn() {
  showWait();
  dijit.byId('listColumnSelector').closeDropDown();
  var callBack=function(){resizeListDiv();};
  loadContent("objectList.php?objectClass=" + dojo.byId('objectClassList').value+ "&objectId="+dojo.byId('objectId').value, 
              "listDiv",null,null,null,null,null,callBack);
}

function resetListColumn() {
  var actionOK=function() {
    showWait();
    dijit.byId('listColumnSelector').closeDropDown();
    dojo.xhrGet({
      url : '../tool/saveSelectedColumn.php?action=reset&objectClass='
          + dojo.byId('objectClassList').value,
      handleAs : "text",
      load : function(data, args) {
        var callBack=function(){resizeListDiv();};
        loadContent("objectList.php?objectClass="+dojo.byId('objectClassList').value+"&objectId="+dojo.byId('objectId').value,
                    "listDiv",null,null,null,null,null,callBack);
      },
      error : function() {
      }
    });
  };
  showConfirm(i18n('confirmResetList'), actionOK);
}

function moveListColumn(source, destination) {
  var mode='';
  var list='';
  var nodeList=dndListColumnSelector.getAllNodes();
  listColumnOrder=new Array();
  for (var i=0; i < nodeList.length; i++) {
    var itemSelected=nodeList[i].id.substr(20);
    // check=(dijit.byId('checkListColumnSelector'+itemSelected).get('checked'))?'':'hidden';
    list+=itemSelected + "|";
    // listColumnOrder[i]=check+itemSelected;
  }  
  // dijit.byId('listColumnSelector').closeDropDown();
  var url='../tool/moveListColumn.php?orderedList=' + list;
  dojo.xhrPost({
    url : url,
    handleAs : "text",
    load : function(data, args) {
    }
  });
  // loadContent(url, "resultDivMain");
  // setGanttVisibility(g);
  // JSGantt.changeFormat(g.getFormat(),g);
  // hideWait();
}

function moveFilterListColumn() {
    var mode='';
    var list='';
    var nodeList=dndListFilterSelector.getAllNodes();
    listColumnOrder=new Array();
    for (i=0; i < nodeList.length; i++) {
      var itemSelected=nodeList[i].id.substr(6);
      list+=itemSelected + "|";
    }  
    
    var callback=function() {
        if (window.top.dijit.byId('dialogDetail').open) {
          var doc=window.top.frames['comboDetailFrame'];
        } else {
          var doc=top;
        }
        if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value){
          var objectClass=dojo.byId('objectClassList').value;
        }else if (! window.top.dijit.byId('dialogDetail').open && dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value){ 
          var objectClass=dojo.byId("objectClassManual").value;
        }else if (dojo.byId('objectClass') && dojo.byId('objectClass').value){
          var objectClass=dojo.byId('objectClass').value;
        } 
        var compUrl=(window.top.dijit.byId("dialogDetail").open) ? '?comboDetail=true' : '';
        if (dojo.byId("objectClassManual") && dojo.byId("objectClassManual").value=='Kanban') {
          compUrl+='&context=directFilterList';
          compUrl+='&contentLoad=../view/kanbanView.php';
          compUrl+='&container=divKanbanContainer';
        }
        doc.loadContent(
            "../tool/displayFilterList.php?context=directFilterList&filterObjectClass="
                + objectClass + compUrl, "directFilterList", null,
           false, 'returnFromFilter', false);
      };
    
    var url='../tool/moveFilterColumn.php?orderedList=' + list;
    dojo.xhrPost({
      url : url,
      handleAs : "text",
      load : function(data, args) {
        if (callback)
          setTimeout(callback, 10);
      }
    });
}

function moveFilterListColumn2() {
  var mode='';
  var list='';
  var nodeList=dndListFilterSelector2.getAllNodes();
  listColumnOrder=new Array();
  for (i=0; i < nodeList.length; i++) {
    var itemSelected=nodeList[i].id.substr(6);
    list+=itemSelected + "|";
  }  
  
  var url='../tool/moveFilterColumn.php?orderedList=' + list;
  dojo.xhrPost({
    url : url,
    handleAs : "text",
    load : function(data, args) {
    }
  });
}

function recalculateColumnSelectorName() {
  cpt=0;
  tot=0;
  while (cpt < 999) {
    var itemSelected=dijit.byId('checkListColumnSelectorWidthId' + cpt);
    if (itemSelected) {
      if (!itemSelected.get('disabled')) {
        tot+=itemSelected.get('value');
      }
    } else {
      cpt=999;
    }
    cpt++;
  }
  if (!dojo.byId('columnSelectorNameFieldId')) return;
  name="checkListColumnSelectorWidthId"+dojo.byId('columnSelectorNameFieldId').value;
  nameWidth=100 - tot;
  color="";
  if (nameWidth < 10) {
    nameWidth=10;
    color="#FFAAAA";
  }
  if (dijit.byId(name)) dijit.byId(name).set('value', nameWidth);
  totWidth=tot + nameWidth;
  totWidthDisplay="";
  if (color) {
    totWidthDisplay='<div style="background-color:' + color + '">' + totWidth
        + '&nbsp;%</div>';
  }
  dojo.byId('columnSelectorTotWidthTop').innerHTML=totWidthDisplay;
  dojo.byId('columnSelectorTotWidthBottom').innerHTML=totWidthDisplay;
  dojo.xhrGet({
    url : '../tool/saveSelectedColumn.php?action=width&item='
        + dojo.byId('columnSelectorNameTableId').value + '&width=' + nameWidth,
    handleAs : "text",
    load : function(data, args) {
    },
    error : function() {
    }
  });
}

// =========================================================
// Items selector
// =========================================================
var oldSelectedItems=null;

function diarySelectItems(value) {
	  if (!oldSelectedItems || oldSelectedItems==value) return;
	  if (oldSelectedItems.indexOf("All")>=0 && value.length>1 ) {
	    value[0]=null;
	    oldSelectedItems=value;
	    dijit.byId("diarySelectItems").set("value",value);
	  } else if (value.indexOf("All")>=0 && oldSelectedItems.indexOf("All")===-1) {
	    value=["All"];
	    oldSelectedItems=value;
	    dijit.byId("diarySelectItems").set("value",value);
	  }
	  var finish=function() {
          loadContent("../view/diary.php","detailDiv","diaryForm");
	  };
	  if (value.length==0) value='none';
	  saveDataToSession('diarySelectedItems', value, true, finish);
	  oldSelectedItems=value;
}

function globalViewSelectItems(value) {
  if (!oldSelectedItems || oldSelectedItems==value) return;
  if (oldSelectedItems.indexOf(" ")>=0 && value.length>1 ) {
    value[0]=null;
    oldSelectedItems=value;
    dijit.byId("globalViewSelectItems").set("value",value);
  } else if (value.indexOf(" ")>=0 && oldSelectedItems.indexOf(" ")===-1) {
    value=[" "];
    oldSelectedItems=value;
    dijit.byId("globalViewSelectItems").set("value",value);
  }
  var finish=function() {
    refreshJsonList("GlobalView");
  };
  if (value.length==0) value='none';
  saveDataToSession('globalViewSelectedItems', value, true, finish);
  oldSelectedItems=value;
}
function globalPlanningSelectItems(value) {
  if (!oldSelectedItems || oldSelectedItems==value) return;
  if (oldSelectedItems.indexOf(" ")>=0 && value.length>1 ) {
    value[0]=null;
    oldSelectedItems=value;
    dijit.byId("globalPlanningSelectItems").set("value",value);
  } else if (value.indexOf(" ")>=0 && oldSelectedItems.indexOf(" ")===-1) {
    value=[" "];
    oldSelectedItems=value;
    dijit.byId("globalPlanningSelectItems").set("value",value);
  }
  var finish=function() {
    refreshJsonPlanning();
  };
  if (value.length==0) value='none';
  saveDataToSession('globalPlanningSelectedItems', value, true, finish);
  oldSelectedItems=value;
}

// =========================================================
// Other
// =========================================================


//    var objectClass=dojo.byId('objectClass').value;
//    var objectId=dojo.byId('objectId').value;
//    var param='';
//    if(objectClass!=null && objectId!=null  &&  dojo.byId('mailRefType') &&  dojo.byId('mailRefId').value){
//      dojo.byId('mailRefType').value=dojo.byId('objectClass').value;
//      dojo.byId('mailRefId').value=dojo.byId('objectId').value;
//       param="&objectClass=" +objectClass+"&objectId=" +objectId;
//    }


function showMailOptions() {
  var callback=function() {
    title=i18n('buttonMail', new Array(i18n(dojo.byId('objectClass').value)));
    if (dijit.byId('attendees')) {
      dijit.byId('dialogMailToOther').set('checked', 'checked');
      dijit.byId('dialogOtherMail').set('value',
          extractEmails(dijit.byId('attendees').get('value')));
      dialogMailToOtherChange();
    }
    dijit.byId("dialogMail").set('title', title);
    refreshListSpecific('emailTemplate', 'selectEmailTemplate','objectIdClass',dojo.byId('objectId').value+'_'+dojo.byId('objectClass').value);
    loadDialog("dialogMail", null, true, '&objectClass='+ dojo.byId('objectClass').value+'&objectId='+dojo.byId('objectId').value);
  }
  if (dijit.byId("dialogMail")
      && dojo.byId('dialogMailObjectClass')
      && dojo.byId('dialogMailObjectClass').value == dojo.byId('objectClass').value) {
    refreshListSpecific('emailTemplate', 'selectEmailTemplate','objectIdClass',dojo.byId('objectId').value+'_'+dojo.byId('objectClass').value);
    loadDialog("dialogMail", null, true, '&objectClass='+ dojo.byId('objectClass').value+'&objectId='+dojo.byId('objectId').value);
  } else {
    var param="&objectClass=" + dojo.byId('objectClass').value+"&objectId=" + dojo.byId('objectId').value;
    loadDialog("dialogMail", callback, false, param);
  }
}

//florent ticket 4442
function showAttachedSize(size,name,id,type){
  var totalSize=dojo.byId('totalSizeNoConvert').value;
  var maxSize=Number(dojo.byId('maxSizeNoconvert').value);
  var attachments=dojo.byId('attachments').value;
  var addAttachments='';
  if(isNaN(size)){
    size=0;
  }
  if(dijit.byId('dialogMail'+name).get('checked')==true){
    totalSize=Number(totalSize)+Number(size);
    if(attachments!=''){
      addAttachments=attachments+'/'+id+'_'+type;
    }else{
      addAttachments=id+'_'+type;
    }
    dojo.byId('attachments').value=addAttachments;
  }else{
    var regex='/'+id+'_'+type;
    if(attachments.indexOf('/'+id+'_'+type)!=-1){
      addAttachments=attachments.replace(regex,'');
    }else{
      regex=id+'_'+type;
      addAttachments=attachments.replace(regex,'');
    }
    dojo.byId('attachments').value=addAttachments;
    totalSize=Number(totalSize)-Number(size);
  }
  var noConvert=totalSize;
  if(totalSize!=0){
    totalSize=octetConvertSize(totalSize);
  }
  if( maxSize < noConvert ){
    dojo.byId('infoSize').style.color="red";
    dojo.byId('totalSize').style.color="red";
  }else if ((maxSize >= noConvert) || noConvert==0) {
    dojo.byId('infoSize').style.color="green";
    dojo.byId('totalSize').style.color="green";
  }
  dojo.byId('totalSizeNoConvert').value=noConvert;
  dojo.byId('totalSize').value=totalSize;
}

function octetConvertSize(octet){
  if(octet!=0 && octet!='-'){
    octet = Math.abs(parseInt(octet, 10));
    var def = [[1, ' octets'], [1024, ' ko'], [1024*1024, ' Mo'], [1024*1024*1024, ' Go'], [1024*1024*1024*1024, ' To']];
    for(var i=0; i<def.length; i++){
      if(octet<def[i][0]) return (octet/def[i-1][0]).toFixed(2)+' '+def[i-1][1];
    }
  }else{
    return i18n('errorNotFoundAttachment');
  }

}

function changeFileSizeMail(name){
  var attachments=dojo.byId('attachments').value;
  var addAttachments='';
  var totalSize=dojo.byId('totalSizeNoConvert').value;
  var maxSize=Number(dojo.byId('maxSizeNoconvert').value);
  var val1=dojo.byId('v1_'+name).value;
  var val2=dojo.byId('v2_'+name).value;
  var id=dojo.byId('addVersion'+name).value;
  var type='DocumentVersion';
  var docVersRef=dojo.byId('idDocRef'+name).value;
  var docVers=dojo.byId('idDoc'+name).value;
  if(dijit.byId('dialogMail'+name).get('checked')==true  ){
    
    if(totalSize!=0){
      size=Number(totalSize)-Number(dojo.byId('filesizeNoConvert'+name).value);
    }
    var regex='/'+id+'_'+type;
    if(attachments.indexOf(regex)==-1){
      regex=id+'_'+type;
    }
    suprAttachments=attachments.replace(regex,'');
    if(dijit.byId('versionRef'+name).get('checked')==true){
      if(suprAttachments!=''){
        addAttachments=suprAttachments+'/'+docVersRef+'_'+type;
      }else{
        addAttachments=docVersRef+'_'+type;
      }
      totalSize=size+Number(val1);
      dojo.byId('filesize'+name).value=octetConvertSize(val1);
      dojo.byId('filesizeNoConvert'+name).value=val1;
      dojo.byId('addVersion'+name).value=docVersRef;
      dojo.byId('attachments').value=addAttachments;
    }else{
      if(suprAttachments!=''){
        addAttachments=suprAttachments+'/'+docVers+'_'+type;
      }else{
        addAttachments=docVers+'_'+type;
      }
      dojo.byId('filesize'+name).value=octetConvertSize(val2);
      dojo.byId('filesizeNoConvert'+name).value=val2;
      dojo.byId('addVersion'+name).value=docVers;
      dojo.byId('attachments').value=addAttachments;
    }
    var noConvert=totalSize;
    if(totalSize!=0){
      totalSize=octetConvertSize(totalSize);
    }
    if( maxSize < noConvert ){
      dojo.byId('infoSize').style.color="red";
      dojo.byId('totalSize').style.color="red";
    }else if ((maxSize >= noConvert) || noConvert==0) {
      dojo.byId('infoSize').style.color="green";
      dojo.byId('totalSize').style.color="green";
    }
    dojo.byId('totalSizeNoConvert').value=noConvert;
    dojo.byId('totalSize').value=totalSize;
  }else{
    if(dijit.byId('versionRef'+name).get('checked')==true){
      dojo.byId('filesize'+name).value=octetConvertSize(val1);
      dojo.byId('filesizeNoConvert'+name).value=val1;
      dojo.byId('addVersion'+name).value=docVersRef;
    }else{
      dojo.byId('filesize'+name).value=octetConvertSize(val2);
      dojo.byId('filesizeNoConvert'+name).value=val2;
      dojo.byId('addVersion'+name).value=docVers;
    }
  }
}
//
function dialogMailToOtherChange() {
  var show=dijit.byId('dialogMailToOther').get('checked');
  if (show) {
    showField('dialogOtherMail');
    showField('otherMailDetailButton');
  } else {
    hideField('dialogOtherMail');
    hideField('otherMailDetailButton');
  }
}

//mehdi #3019
function mailerTextEditor(code){
  var callBack= function() {
  var codeParam = dojo.byId("codeParam");
  codeParam.value = code;
	var editorType=dojo.byId("mailEditorType").value;
	if (editorType=="CK" || editorType=="CKInline") { // CKeditor type
      ckEditorReplaceEditor("mailEditor",999);
	} else if (editorType=="text") {
      dijit.byId("mailEditor").focus();
      dojo.byId("mailEditor").style.height=(screen.height*0.6)+'px';
      dojo.byId("mailEditor").style.width=(screen.width*0.6)+'px';
    } else if (dijit.byId("mailMessageEditor")) { // Dojo type editor
      dijit.byId("mailMessageEditor").set("class", "input");
      dijit.byId("mailMessageEditor").focus();
      dijit.byId("mailMessageEditor").set("height", (screen.height*0.6)+'px'); // Works on first time
      dojo.byId("mailMessageEditor_iframe").style.height=(screen.height*0.6)+'px'; // Works after first time
    }
	  dojo.byId("mailEditor").innerHTML=dojo.byId(code).value;
  };
  loadDialog('dialogMailEditor', callBack, true, null, true, true);
}

function saveMailMessage() {
  var codeParam = dojo.byId("codeParam").value;
  var editorType=dojo.byId("mailEditorType").value;
  if (editorType=="CK" || editorType=="CKInline") {
    noteEditor=CKEDITOR.instances['mailEditor'];
    noteEditor.updateElement();
    var tmpCkEditor=noteEditor.document.getBody().getText();
    var tmpCkEditorData=noteEditor.getData();
    if (tmpCkEditor.trim()=="" && tmpCkEditorData.indexOf('<img')<=0) {
      var msg=i18n('messageMandatory', new Array(i18n('Message')));
      noteEditor.focus();
      showAlert(msg);
      return;
    }
  } else if (dijit.byId("messageMailEditor")) {
    if (dijit.byId("mailEditor").getValue() == '') {
      dijit.byId("messageMailEditor").set("class", "input required");
      var msg=i18n('messageMandatory', new Array(i18n('Message')));
      dijit.byId("messageMailEditor").focus();
      dojo.byId("messageMailEditor").focus();
      showAlert(msg);
      return;
    }
  } 
  var callBack = function(){
    dojo.byId(codeParam).value = tmpCkEditorData; 
    dojo.byId(codeParam+"_display").innerHTML = tmpCkEditorData;
  };
  loadDiv("../tool/saveParameter.php", "resultDivMain", "parameterForm", callBack);
  dijit.byId('dialogMailEditor').hide();
}
//end

//gautier #2935
var doNotTriggerEmailChange=false;
function findAutoEmail(){
  if (doNotTriggerEmailChange==true) return;
  var adress=dijit.byId('dialogOtherMail').get('value');
  var regex = /,[ ]*|;[ ]*/gi;
  adress=adress.replace(regex,",");
  dojo.xhrGet({
    url: '../tool/saveFindEmail.php?&isId=false&adress='+adress ,
    load: function(data,args) { 
      var email = data;
      doNotTriggerEmailChange=true;
      dijit.byId('dialogOtherMail').set('value', email);
      doNotTriggerEmailChange=false;
    }
  });
}

function dialogMailIdEmailChange(){
  if (doNotTriggerEmailChange==true) return;
  doNotTriggerEmailChange=true;
  var value = dijit.byId('dialogOtherMail').get('value');
  var id=dijit.byId('dialogMailObjectIdEmail').get('value');
  id = id+','+value;
  dojo.xhrGet({
    url: '../tool/saveFindEmail.php?&isId=true&id='+id,
    load: function(data,args) { 
      var email = data;
      dijit.byId('dialogOtherMail').set('value', email);
      doNotTriggerEmailChange=false;
    }
  });
}
//end

//damian #2936
function stockEmailCurrent(){
	var adress=dijit.byId('dialogOtherMail').get('value');
	var adressSplit = adress.split(',');
	adressSplit.forEach(function(emailSplit) {
		if(stockEmailHistory.indexOf(emailSplit) == -1){
			stockEmailHistory.push(emailSplit);
		}
	});
}

function compareEmailCurrent(){
	if(stockEmailHistory.length > 0){
		var inputEmail=dijit.byId('dialogOtherMail').get('value');
		var split = inputEmail.split(',');
		inputEmail = split[split.length - 1];
		var count = 0;
		var email = "";
		var divCount = 0;
		//var display = '';
		stockEmailHistory.forEach(function(element){
			count++;
			if(split.indexOf(element) <= -1){
				divCount++;
				if(divCount < 0){
					dojo.byId('dialogOtherMailHistorical').style.display = 'none';
				}
				if(element.search(inputEmail) > -1){
					dojo.byId('dialogOtherMailHistorical').style.display = 'block';
					email += '<div class="emailHistorical" id="email'+count+'" style="cursor:pointer;"'
							+'onclick="selectEmailHistorical(\''+element+'\')">'
							+element+'</div>';
					dojo.byId('dialogOtherMailHistorical').innerHTML = email;
				}
			}else{
				divCount--;
			}
			if(divCount > 7){
				dojo.byId('dialogOtherMailHistorical').style.height = '100px';
			}else{
				dojo.byId('dialogOtherMailHistorical').style.height = 'auto';
			}
		});
	}else{
		dojo.byId('dialogOtherMailHistorical').style.display = 'none';
	}
}

function hideEmailHistorical(){
	setTimeout(function(){dojo.byId('dialogOtherMailHistorical').style.display = 'none';},200); 
}

function selectEmailHistorical(email){
	var currentValue = dijit.byId('dialogOtherMail').get("value");
	var tab = currentValue.split(',');
	var tabLength = tab.length;
	var newValue = "";
	if(currentValue != ""){
		if(tabLength>1){
			for(var i = 0; i<tabLength-1; i++){
				if(tab[i].search('@') > -1){
					newValue += tab[i]+',';
				}
			}
		}
		newValue += email+',';
		dijit.byId('dialogOtherMail').set("value", newValue);
	}else{
		dijit.byId('dialogOtherMail').set("value", email+',');
	}
	dojo.byId('dialogOtherMailHistorical').style.display = 'none';
}
//end #2936

function extractEmails(str) {
  var current='';
  var result='';
  var name=false;
  for (var i=0; i < str.length; i++) {
    car=str.charAt(i);
    if (car == '"') {
      if (name == true) {
        name=false;
        current="";
      } else {
        if (current != '') {
          if (result != '') {
            result+=', ';
          }
          result+=trimTag(current);
          current='';
        }
        name=true;
      }
    } else if (name == false) {
      if (car == ',' || car == ';' || car == ' ') {
        if (current != '') {
          if (result != '') {
            result+=', ';
          }
          result+=trimTag(current);
          current='';
        }
      } else {
        current+=car;
      }
    }
  }
  if (current != "") {
    if (result != '') {
      result+=', ';
    }
    result+=trimTag(current);
  }
  return result;
}

function sendMail() {
	var idEmailTemplate = dijit.byId('selectEmailTemplate').get("value");
	if(dojo.byId('maxSizeNoconvert') && dojo.byId('totalSizeNoConvert').value > Number(dojo.byId('maxSizeNoconvert').value)){
	  showAlert(i18n('errorAttachmentSize'));
	  return;
	}else{
	  loadContent("../tool/sendMail.php?className=Mailable&idEmailTemplate="+idEmailTemplate, "resultDivMain",
	      "mailForm", true, 'mail');
	  dijit.byId("dialogMail").hide();
	}
}
//gautier ticket #2096
function assignTeamForMeeting() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/assignTeamForMeeting.php?assignmentId=&assignmentRefType="+dojo.byId('objectClass').value+"&assignmentRefId="+dojo.byId("objectId").value,"resultDivMain", null,
        true, 'assignment');
  };
  msg=i18n('confirmAssignWholeTeam');
  showConfirm(msg, actionOK);
  
}
function lockRequirement() {
  if (checkFormChangeInProgress()) {
    return false;
  }
  dijit.byId('locked').set('checked', true);
  dijit.byId('idLocker').set('value', dojo.byId('idCurrentUser').value);
  var curDate=new Date();
  dijit.byId('lockedDate').set('value', curDate);
  dijit.byId('lockedDateBis').set('value', curDate);
  formChanged();
  submitForm("../tool/saveObject.php", "resultDivMain", "objectForm", true);
  return true;
}

function unlockRequirement() {
  if (checkFormChangeInProgress()) {
    return false;
  }
  dijit.byId('locked').set('checked', false);
  dijit.byId('idLocker').set('value', null);
  dijit.byId('lockedDate').set('value', null);
  dijit.byId('lockedDateBis').set('value', null);
  formChanged();
  submitForm("../tool/saveObject.php", "resultDivMain", "objectForm", true);
  return true;
}

// CHANGE BY Marc TABARY - 2017-03-13 - CHANGE TITLE DYNAMIC DIALOG
function loadDialog(dialogDiv, callBack, autoShow, params, clearOnHide, closable, dialogTitle, dialogTitleKeepAsIs) {
  // Before loading, be sure to clear dialogs containing "directAccessToListButton" 
  // This is mandatory as these dialogs may not be cleared on direct access, as they are not showed so .hide() and no effect and clearOnHide is not triggered  
  if (dojo.byId('directAccessToListButton')) {
    var parentName=dojo.byId('directAccessToListButton').parentNode.id;
    var dialogName="dialog"+parentName.substr(0,1).toUpperCase()+parentName.substr(1,parentName.length-5);
    if (dijit.byId(dialogName)){
      dijit.byId(dialogName).set("content",null);
    }
  }
// Old    
//function loadDialog(dialogDiv, callBack, autoShow, params, clearOnHide, closable) {
// END CHANGE BY Marc TABARY - 2017-03-13 - PERIODIC YEAR BUDGET ELEMENT
  if(typeof closable =='undefined')closable=true;
  var hideCallback=function() {
    if (dialogDiv=='dialogNote') resumeBodyFocus();
  };
  if (clearOnHide) {
    hideCallback=function() {
      dijit.byId(dialogDiv).set('content', null);
      if (dialogDiv=='dialogNote') resumeBodyFocus();
    };
  }
  
  // ADD BY Marc TABARY - 2017-03-13 - CHANGE TITLE DYNAMIC DIALOG
  var setTitle=false;
  if(typeof dialogTitle == 'undefined') {
      theDialogTitle = dialogDiv;
  } else if (dialogTitle=='') {
      theDialogTitle = dialogDiv;    
  } else {
      theDialogTitle = dialogTitle;
      setTitle=true;
  }
  if (! dialogTitleKeepAsIs) theDialogTitle=i18n(theDialogTitle);
  // END ADD BY Marc TABARY - 2017-03-13 - CHANGE TITLE DYNAMIC DIALOG
  
  extraClass="projeqtorDialogClass";
  if (dialogDiv=="dialogLogfile") {
    extraClass="logFile";
  }
  if (!dijit.byId(dialogDiv)) {
    dialog=new dijit.Dialog({
      id : dialogDiv,
// CHANGE BY Marc TABARY - 2017-03-13 - CHANGE TITLE DYNAMIC DIALOG
      title : theDialogTitle,
      // Old     
//      title : i18n(dialogDiv),
// END CHANGE BY Marc TABARY - 2017-03-13 - CHANGE TITLE DYNAMIC DIALOG
      width : '500px',
      onHide : hideCallback,
      content : i18n("loading"),
      'class' : extraClass,
      closable : closable
    });
  } else {
    dialog=dijit.byId(dialogDiv);
// ADD BY Marc TABARY - 2017-03-13 - CHANGE TITLE DYNAMIC DIALOG
    if (setTitle) {
        dialog.set('title',theDialogTitle);
  }
// END ADD BY Marc TABARY - 2017-03-13 - CHANGE TITLE DYNAMIC DIALOG
  }
  if (!params) {
    params="";
  }
  showWait();
  dojo.xhrGet({
    url : '../tool/dynamicDialog.php?dialog=' + dialogDiv + '&isIE='
        + ((dojo.isIE) ? dojo.isIE : '') + params,
    handleAs : "text",
    load : function(data) {
      var contentWidget=dijit.byId(dialogDiv);
      contentWidget.set('content', data);
      if (autoShow) {
        setTimeout("dijit.byId('" + dialogDiv + "').show();", 100);
      }
      hideWait();
      if (callBack) {
        setTimeout(callBack, 10);
      }
    },
    error : function() {
      consoleTraceLog("error loading dialog " + dialogDiv);
      hideWait();
    }
  });
}
/*
 * ========================================================================
 * Today management
 * ========================================================================
 */
function saveTodayParameters() {
  loadContent('../tool/saveTodayParameters.php', 'centerDiv',
      'todayParametersForm');
  dijit.byId('dialogTodayParameters').hide();
}

function setTodayParameterDeleted(id) {
  dojo.byId('dialogTodayParametersDelete' + id).value=1;
  dojo.byId('dialogTodayParametersRow' + id).style.display='none';
}

function loadReport(url, dialogDiv) {
  var contentWidget=dijit.byId(dialogDiv);
  contentWidget.set('content',
      '<img src="../view/css/images/treeExpand_loading.gif" />');
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data) {
      var contentWidget=dijit.byId(dialogDiv);
      if (!contentWidget) {
        return;
      }
      contentWidget.set('content', data);
    },
    error : function() {
      consoleTraceLog("error loading report " + url + " into " + dialogDiv);
    }
  });
}

function reorderTodayItems() {
  var nodeList=dndTodayParameters.getAllNodes();
  for (i=0; i < nodeList.length; i++) {
    var item=nodeList[i].id.substr(24);
    var order=dojo.byId("dialogTodayParametersOrder" + item);
    if (order) {
      order.value=i + 1;
    }
  }
}
var multiSelection=false;
var switchedModeBeforeMultiSelection=false;
function startMultipleUpdateMode(objectClass) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  grid=dijit.byId("objectGrid"); // if the element is not a widget, exit.
  if (!grid) {
    return;
  }
  multiSelection=true;
  formChangeInProgress=true;
  switchedModeBeforeMultiSelection=switchedMode;
  if (switchedModeBeforeMultiSelection) {
    switchModeOn();
  }
  unselectAllRows("objectGrid");
  dijit.byId('objectGrid').selection.setMode('extended');
  loadContent('../view/objectMultipleUpdate.php?objectClass=' + objectClass,
      'detailDiv');
}

function saveMultipleUpdateMode(objectClass) {
  // submitForm("../tool/saveObject.php","resultDivMain", "objectForm", true);
  grid=dijit.byId("objectGrid"); // if the element is not a widget, exit.
  if (!grid) {
    return;
  }
  dojo.byId("selection").value="";
  var items=grid.selection.getSelected();
  if (items.length) {
    dojo.forEach(items, function(selectedItem) {
      if (selectedItem !== null) {
        dojo.byId("selection").value+=parseInt(selectedItem.id) + ";";
      }
    });
  }
  var callBack = function(){
    setTimeout("updateSelectedCountMultiple();",100);
  };
  loadContent('../tool/saveObjectMultiple.php?objectClass=' + objectClass,
      'resultDivMultiple', 'objectFormMultiple',null,null,null,null,callBack);
}

function endMultipleUpdateMode(objectClass) {
  if (dijit.byId('objectGrid')) {
    dijit.byId('objectGrid').selection.setMode('single');
    unselectAllRows("objectGrid");
  }
  multiSelection=false;
  formChangeInProgress=false;
  var sm='';
  if (switchedModeBeforeMultiSelection) {
    if (!switchedMode) {
      switchModeOn();
      sm='&switchedMode=on';
    }
  } else {
    if (switchedMode) {
      switchModeOn();
    }
  }
  if (objectClass) {
    loadContent('../view/objectDetail.php?noselect=true'+sm+'&objectClass='
        + objectClass, 'detailDiv');
  }
}

function deleteMultipleUpdateMode(objectClass) {
  grid=dijit.byId("objectGrid"); // if the element is not a widget, exit.
  if (!grid) {
    return;
  }
  dojo.byId("selection").value=""
  var items=grid.selection.getSelected();
  if (items.length) {
    dojo.forEach(items, function(selectedItem) {
      if (selectedItem !== null) {
        dojo.byId("selection").value+=parseInt(selectedItem.id) + ";";
      }
    });
  }
  actionOK=function() {
    actionOK2=function() {
      if (dijit.byId('deleteMultipleResultDiv').get('content')!='') {
        showConfirm(dijit.byId('deleteMultipleResultDiv').get('content'), function(){loadContent('../tool/deleteObjectMultiple.php?objectClass=' + objectClass,
          'resultDivMultiple', 'objectFormMultiple');});
      } else {
        loadContent('../tool/deleteObjectMultiple.php?objectClass=' + objectClass,
            'resultDivMultiple', 'objectFormMultiple');
      } 
    };
    setTimeout(function(){
      loadContent('../tool/deleteObjectMultipleControl.php?objectClass=' + objectClass,
          'deleteMultipleResultDiv', 'objectFormMultiple',null,null,null,null,actionOK2);
    },200);
  };
  msg=i18n('confirmDeleteMultiple', new Array(i18n('menu' + objectClass),
      items.length));
  showConfirm(msg, actionOK);
}
function updateSelectedCountMultiple() {
  if (dojo.byId('selectedCount')) {
    countSelectedItem('objectGrid','selectedCount');
  }
}
//gautier #533
function multipleUpdateResetPwd(objectClass) {
  grid=dijit.byId("objectGrid"); // if the element is not a widget, exit.
  if (!grid) {
    return;
  }
  dojo.byId("selection").value="";
  var items=grid.selection.getSelected();
  if (items.length) {
    dojo.forEach(items, function(selectedItem) {
      if (selectedItem !== null) {
        dojo.byId("selection").value+=parseInt(selectedItem.id) + ";";
      }
    });
  }
  var callBack = function(){
    
  };
  loadContent('../tool/saveObjectMultiplePwd.php?objectClass=' + objectClass,
      'resultDivMultiple', 'objectFormMultiple',null,null,null,null,callBack);
}

function showImage(objectClass, objectId, imageName) {
  if (objectClass == 'Affectable' || objectClass == 'Resource'
      || objectClass == 'User' || objectClass == 'Contact') {
    imageUrl="../files/thumbs/Affectable_" + objectId + "/thumb80.png";
  }else if(objectClass == 'Note'){
	  imageUrl=objectId;
  }else {
    imageUrl="../tool/download.php?class=" + objectClass + "&id=" + objectId;
  }
  var dialogShowImage=dijit.byId("dialogShowImage");
  if (!dialogShowImage) {
    dialogShowImage=new dojox.image.LightboxDialog({});
    dialogShowImage.startup();
  }
  if (dialogShowImage && dialogShowImage.show) {
    if (dojo.isFF) {
      dojo.xhrGet({
        url : imageUrl,
        handleAs : "text",
        load : function(data) {
          dialogShowImage.show({
            title : imageName,
            href : imageUrl
          });
          dijit.byId('formDiv').resize();
        }
      });
    } else {
      dialogShowImage.show({
        title : imageName,
        href : imageUrl
      });
      dijit.byId('formDiv').resize();
    }
    // dialogShowImage.show({ title:imageName, href:imageUrl });
  } else {
    showError("Error loading image " + imageName);
  }
  // dijit.byId('formDiv').resize();
}
function showBigImage(objectClass, objectId, node, title, hideImage, nocache) {
  var top=node.getBoundingClientRect().top;
  var left=node.getBoundingClientRect().left;
  var height=node.getBoundingClientRect().height;
  var width=node.getBoundingClientRect().width;
  if (!objectClass && !objectId) top+=15;
  if (!height) height=40;
  if (objectClass == 'Affectable' || objectClass == 'Resource'
      || objectClass == 'User' || objectClass == 'Contact') {
    imageUrl="../files/thumbs/Affectable_" + objectId + "/thumb80.png";
    if (nocache) {
      imageUrl+=nocache;
    }
  } else {
    imageUrl="../tool/download.php?class=" + objectClass + "&id=" + objectId;
  }
  var centerThumb80=dojo.byId("centerThumb80");
  if (centerThumb80) {
    var htmlPhoto='';
    var alone='';
    if (objectClass && objectId && !hideImage) {
      htmlPhoto='<img style="border-radius:40px;" src="' + imageUrl + '" />';
    } else {
      alone='Alone';
    }
    if (title) {
      htmlPhoto+='<div id="centerThumb80TitleContainer" class="thumbBigImageTitle' + alone + '">' + title
          + '</div>';
    }
    var topPx=(top - 40 + (height / 2)) + "px";
    var leftPx=(left - 125) + "px";
    if(dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value=='ActivityStream'){
      leftPx=(left + 125) + "px";
    }
    if (parseInt(leftPx)<3) {
      leftPx=(left+width+5)+"px";
    }
    
    centerThumb80.innerHTML=htmlPhoto;
    centerThumb80.style.top=topPx;
    centerThumb80.style.left=leftPx;
    centerThumb80.style.display="block";
    var titleDivRect=(dojo.byId('centerThumb80TitleContainer'))?dojo.byId('centerThumb80TitleContainer').getBoundingClientRect():null;
    var globalDivRect=document.documentElement.getBoundingClientRect();
    if (titleDivRect && titleDivRect.top+titleDivRect.height+50>globalDivRect.height) {
      var newTop=globalDivRect.height-titleDivRect.height-50;
      if (newTop<0) newTop=0;
      centerThumb80.style.top=newTop+'px';
    }
  }
  
}
function hideBigImage(objectClass, objectId) {
  var centerThumb80=dojo.byId("centerThumb80");
  if (centerThumb80) {
    centerThumb80.innerHTML="";
    centerThumb80.style.display="none";
  }
}

showHtmlContent=null;
function showLink(link) {
  if (dojo.isIE) {
    if (showHtmlContent==null) {
      showHtmlContent=dijit.byId("dialogShowHtml").get('content');
    } else {
      dijit.byId("dialogShowHtml").set('content',showHtmlContent);
    }
  }
  // window.frames['showHtmlFrame'].location.href='../view/preparePreview.php';
  dijit.byId("dialogShowHtml").title=link;
  window.frames['showHtmlFrame'].location.href=link;
  dijit.byId("dialogShowHtml").show();
  window.frames['showHtmlFrame'].focus();
}
function showHtml(id, file, className) {
  if (dojo.isIE) {
    if (showHtmlContent==null) {
      showHtmlContent=dijit.byId("dialogShowHtml").get('content');
    } else {
      dijit.byId("dialogShowHtml").set('content',showHtmlContent);
    }
  }
  dijit.byId("dialogShowHtml").title=file;
  window.frames['showHtmlFrame'].location.href='../tool/download.php?class='+className+'&id='
      + id + '&showHtml=true';
  dijit.byId("dialogShowHtml").clearOnHide=false;
  dijit.byId("dialogShowHtml").show();
  window.frames['showHtmlFrame'].focus();
} 
/*function displayTestPreview(){
	var toto = dojo.byId('testPreview');
}*/

// *******************************************************
// Dojo code to position into a tree
// *******************************************************
function recursiveHunt(lookfor, model, buildme, item) {
  var id=model.getIdentity(item);
  buildme.push(id);
  if (id == lookfor) {
    return buildme;
  }
  for ( var idx in item.children) {
    var buildmebranch=buildme.slice(0);
    var r=recursiveHunt(lookfor, model, buildmebranch, item.children[idx]);
    if (r) {
      return r;
    }
  }
  return undefined;
}

function selectTreeNodeById(tree, lookfor) {
  var buildme=[];
  var result=recursiveHunt(lookfor, tree.model, buildme, tree.model.root);
  if (result && result.length > 0) {
    tree.set('path', result);
  }
}

// ************************************************************
// Code to select columns to be exported
// ************************************************************
var ExportType='';
// open the dialog with checkboxes
function openExportDialog(Type) {
  ExportType=Type;
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var objectClass=(dojo.byId('objectClassList'))?dojo.byId('objectClassList').value:dojo.byId('objectClass').value;
  var params = "&objectClass=" + objectClass;
  if(objectClass == 'Work'){
    params += "&dateWeek="+ dojo.byId("dateWeek").value;
    params += "&dateMonth="+ dojo.byId("dateMonth").value;
    params += "&userId="+ dojo.byId("userId").value;
  }
   loadDialog("dialogExport", null, true,params );
}

// close the dialog with checkboxes
function closeExportDialog() {
  dijit.byId("dialogExport").hide();
}

// save current state of checkboxes
function saveCheckboxExport(obj, idUser) {
  var val=dojo.byId('column0').value;
  var toStore="";
  val=eval(val);
  for (i=1; i <= val; i++) {
    var checkbox=dijit.byId('column' + i);
    if (checkbox) {
      if (!checkbox.get('checked')) {
        var field=checkbox.value;
        toStore+=field + ";";
      }
    }
  }
  dojo.xhrPost({
    url : "../tool/saveCheckboxes.php?&objectClass=" + obj + "&toStore="
        + toStore,
    handleAs : "text",
    load : function() {
    }
  });
}

// Executes the report (shows the print/pdf/csv)
function executeExport(obj, idUser) {
  var verif=0;
  var val=dojo.byId('column0').value;
  var exportReferencesAs=dijit.byId('exportReferencesAs').get('value');
  var exportHtml=(dijit.byId('exportHtml').get('checked'))?'1':'0';
  var separatorCSV=dijit.byId('separatorCSV').get('value');
  if(obj == 'Work'){
    var exportDateAs = dijit.byId('exportDateAs').get('value');
    var exportRessourceAs = dijit.byId('exportRessourceAs').get('value');
  }
  val=eval(val);
  var toExport="";
  for (var i=1; i <= val; i++) {
    var checkbox=dijit.byId('column' + i);
    if (checkbox) {
      if (checkbox.get('checked')) {
        verif=1;
      } else {
        var field=checkbox.value;
        toExport+=field + ";";
      }
    }
  }
  if (dijit.byId('documentVersionLastOnly') && dijit.byId('documentVersionLastOnly').get('checked')) {
    toExport+='documentVersionAll';
  }
  if (verif == 1) {
    if (ExportType == 'csv') {
      if(obj != 'Work'){
      showPrint("../tool/jsonQuery.php?exportHtml="+exportHtml
          +"&exportReferencesAs="+ exportReferencesAs + "&hiddenFields=" + toExport + "&separatorCSV=" + separatorCSV
          , 'list', null,
          'csv');
      }else{
        showPrint("../tool/jsonQuery.php?exportHtml="+exportHtml
            +"&exportReferencesAs="+ exportReferencesAs + "&hiddenFields=" + toExport +"&exportDateAs="+ exportDateAs
            +"&exportRessourceAs="+ exportRessourceAs + "&separatorCSV=" + separatorCSV
            , 'list', null,
            'csv');
      }
    }
    saveCheckboxExport(obj, idUser);
    closeExportDialog(obj, idUser);
  } else {
    showAlert(i18n('alertChooseOneAtLeast'));
  }
}

// Check or uncheck all boxes
function checkExportColumns(scope) {
  if (scope == 'aslist') {
    showWait();
    dojo.xhrGet({
      url : "../tool/getColumnsList.php?objectClass="+((dojo.byId('objectClassList'))?dojo.byId('objectClassList').value:dojo.byId('objectClass').value),
      load : function(data) {
        var list=";" + data;
        var val=dojo.byId('column0').value;
        val=eval(val);
        var allChecked=true;
        for (i=1; i <= val; i++) {
          var checkbox=dijit.byId('column' + i);
          if (checkbox) {
            var search=";" + checkbox.value + ";";
            if (list.indexOf(search) >= 0) {
              checkbox.set('checked', true);
            } else {
              checkbox.set('checked', false);
              allChecked=false;
            }
          }
        }
        dijit.byId('checkUncheck').set('checked', allChecked);
        hideWait();
      },
      error : function() {
        hideWait();
      }
    });
  } else {
    var check=dijit.byId('checkUncheck').get('checked');
    var val=dojo.byId('column0').value;
    val=eval(val);
    for (i=1; i <= val; i++) {
      var checkbox=dijit.byId('column' + i);
      if (checkbox) {
        checkbox.set('checked', check);
      }
    }
  }
}

// ==================================================================
// Project Selector Functions
// ==================================================================
function changeProjectSelectorType(displayMode) {
	//#2887
	
	
	var callBack = function(){
	  loadContent("../view/menuProjectSelector.php", 'projectSelectorDiv');
	};
	
	saveDataToSession('projectSelectorDisplayMode', displayMode, true, callBack);
  if (dijit.byId('dialogProjectSelectorParameters')) {
    dijit.byId('dialogProjectSelectorParameters').hide();
  }
}

function refreshProjectSelectorList() {
  dojo.xhrPost({
    url : "../tool/refreshVisibleProjectsList.php",
    load : function() {
      loadContent('../view/menuProjectSelector.php', 'projectSelectorDiv');
      if (dijit.byId('idProjectPlan')) {
        refreshList('planning', null, null, dijit.byId('idProjectPlan').get('value'), 'idProjectPlan', false);
      }
    }
  });
  if (dijit.byId('dialogProjectSelectorParameters')) {
    dijit.byId('dialogProjectSelectorParameters').hide();
  }
}

// ********************************************************************************************
// Diary
// ********************************************************************************************
function diaryPrevious() {
  diaryPreviousNext(-1);
}
function diaryNext() {
  diaryPreviousNext(1);
}

var noRefreshDiaryPeriod=false;
function diarySelectDate(directDate) {
  if (!directDate)
    return;
  if (noRefreshDiaryPeriod) {
    return;
  }
  noRefreshDiaryPeriod=true;
  var period=dojo.byId("diaryPeriod").value;
  var year=directDate.getFullYear();
  var month=directDate.getMonth() + 1;
  if (period == "month") {
    dojo.byId("diaryYear").value=year;
    dojo.byId("diaryMonth").value=(month >= 10) ? month : "0" + month;
    diaryDisplayMonth(month, year);
  } else if (period == "week") {
    var week=getWeek(directDate.getDate(), month, year) + '';
    if (week == 1 && month > 10) {
      year+=1;
      month=1;
    }
    if (week > 50 && month == 1) {
      year-=1;
      month=12;
    }
    dojo.byId("diaryWeek").value=week;
    dojo.byId("diaryYear").value=year;
    dojo.byId("diaryMonth").value=month;
    diaryDisplayWeek(week, year);
  } else if (period == "day") {
    day=formatDate(directDate);
    dojo.byId("diaryDay").value=day;
    dojo.byId("diaryYear").value=year;
    diaryDisplayDay(day);
  }
  setTimeout("noRefreshDiaryPeriod=false;", 10);
  setTimeout('loadContent("../view/diary.php", "detailDiv", "diaryForm");',200);
  return true;
}

function diaryPreviousNext(way) {
  if (waitingForReply)  {
    showInfo(i18n("alertOngoingQuery"));
    return;
  }
  period=dojo.byId("diaryPeriod").value;
  year=dojo.byId("diaryYear").value;
  month=dojo.byId("diaryMonth").value;
  week=dojo.byId("diaryWeek").value;
  day=dojo.byId("diaryDay").value;
  if (period == "month") {
    month=parseInt(month) + parseInt(way);
    if (month <= 0) {
      month=12;
      year=parseInt(year) - 1;
    } else if (month >= 13) {
      month=1;
      year=parseInt(year) + 1;
    }
    dojo.byId("diaryYear").value=year;
    dojo.byId("diaryMonth").value=(month >= 10) ? month : "0" + month;
    diaryDisplayMonth(month, year);
  } else if (period == "week") {
    week=parseInt(week) + parseInt(way);
    if (parseInt(week) == 0) {
      week=getWeek(31, 12, year - 1);
      if (week == 1) {
        var day=getFirstDayOfWeek(1, year);
        week=getWeek(day.getDate() - 1, day.getMonth() + 1, day.getFullYear());
      }
      year=parseInt(year) - 1;
    } else if (parseInt(week, 10) > 53) {
      week=1;
      year=parseInt(year) + 1;
    } else if (parseInt(week, 10) > 52) {
      lastWeek=getWeek(31, 12, year);
      if (lastWeek == 1) {
        var day=getFirstDayOfWeek(1, year + 1);
        lastWeek=getWeek(day.getDate() - 1, day.getMonth() + 1, day
            .getFullYear());
      }
      if (parseInt(week, 10) > parseInt(lastWeek, 10)) {
        week=01;
        year=parseInt(year) + 1;
      }
    }
    dojo.byId("diaryWeek").value=week;
    dojo.byId("diaryYear").value=year;
    diaryDisplayWeek(week, year);
  } else if (period == "day") {
    day=formatDate(addDaysToDate(getDate(day), way));
    year=day.substring(0, 4);
    dojo.byId("diaryDay").value=day;
    dojo.byId("diaryYear").value=year;
    diaryDisplayDay(day);
  }
  //loadContent("../view/diary.php", "detailDiv", "diaryForm");
}

function diaryWeek(week, year) {
  dojo.byId("diaryPeriod").value="week";
  dojo.byId("diaryYear").value=year;
  dojo.byId("diaryWeek").value=week;
  diaryDisplayWeek(week, year);
  loadContent("../view/diary.php", "detailDiv", "diaryForm");
}

function diaryMonth(month, year) {
  dojo.byId("diaryPeriod").value="month";
  dojo.byId("diaryYear").value=year;
  dojo.byId("diaryMonth").value=month;
  diaryDisplayMonth(month, year);
  loadContent("../view/diary.php", "detailDiv", "diaryForm");
}
function diaryDay(day) {
  dojo.byId("diaryPeriod").value="day";
  dojo.byId("diaryYear").value=day.substring(day, 0, 4);
  dojo.byId("diaryMonth").value=day.substring(day, 5, 2);
  dojo.byId("diaryDay").value=day;
  diaryDisplayDay(day);
  loadContent("../view/diary.php", "detailDiv", "diaryForm");
}

function diaryDisplayMonth(month, year) {
  var vMonthArr=new Array(i18n("January"), i18n("February"), i18n("March"),
      i18n("April"), i18n("May"), i18n("June"), i18n("July"), i18n("August"),
      i18n("September"), i18n("October"), i18n("November"), i18n("December"));
  caption=vMonthArr[month - 1] + " " + year;
  dojo.byId("diaryCaption").innerHTML=caption;
  var firstday=new Date(year, month - 1, 1);
  dijit.byId('dateSelector').set('value', firstday);
}

function diaryDisplayWeek(week, year) {
  var firstday=getFirstDayOfWeek(week, year);
  var lastday=new Date(firstday);
  lastday.setDate(firstday.getDate() + 6);
  if (week<10) week='0'+parseInt(week);
  caption=year + ' #' + week + "<span style='font-size:70%'> (" + dateFormatter(formatDate(firstday))
      + " - " + dateFormatter(formatDate(lastday)) + ") </span>";
  dojo.byId("diaryCaption").innerHTML=caption;
  dijit.byId('dateSelector').set('value', firstday);
}

function diaryDisplayDay(day) {
  var vDayArr=new Array(i18n("Sunday"), i18n("Monday"), i18n("Tuesday"),
      i18n("Wednesday"), i18n("Thursday"), i18n("Friday"), i18n("Saturday"));
  var d=getDate(day);
  caption=vDayArr[d.getDay()] + " " + dateFormatter(day);
  dojo.byId("diaryCaption").innerHTML=caption;
  dijit.byId('dateSelector').set('value', day);
}

// ********************************************************************************************
// WORKFLOW PARAMETERS (selection of status)
// ********************************************************************************************
var workflowParameterAllChecked=true;
function showWorkflowParameter(id) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  callBack=function() {
  };
  workflowParameterAllChecked=true;
  var params='&idWorkflow=' + id;
  loadDialog('dialogWorkflowParameter', callBack, true, params);
}

function saveWorkflowParameter() {
  loadContent("../tool/saveWorkflowParameter.php", "resultDivMain",
      "dialogWorkflowParameterForm", true);
  dijit.byId('dialogWorkflowParameter').hide();
}

function dialogWorkflowParameterUncheckAll() {
  dojo.query(".workflowParameterCheckbox").forEach(function(node, index, nodelist) {
    var id=node.getAttribute('widgetid');
    if (dijit.byId(id) ) {
      dijit.byId(id).set('checked',!workflowParameterAllChecked);
    }
  });
  workflowParameterAllChecked=!workflowParameterAllChecked;
}

//********************************************************************************************
//WORKFLOW AUTHORIZATION PARAMETERS (selection of profile)
//********************************************************************************************
var workflowProfileParameterAllChecked=true;
function showWorkflowProfileParameter(id) {
if (checkFormChangeInProgress()) {
 showAlert(i18n('alertOngoingChange'));
 return;
}
callBack=function() {
};
workflowProfileParameterAllChecked=true;
var params='&idWorkflow=' + id;
loadDialog('dialogWorkflowProfileParameter', callBack, true, params);
}

function saveWorkflowProfileParameter() {
loadContent("../tool/saveWorkflowProfileParameter.php", "resultDivMain",
   "dialogWorkflowProfileParameterForm", true);
dijit.byId('dialogWorkflowProfileParameter').hide();
}

function dialogWorkflowProfileParameterUncheckAll() {
dojo.query(".workflowProfileParameterCheckbox").forEach(function(node, index, nodelist) {
 var id=node.getAttribute('widgetid');
 if (dijit.byId(id) ) {
   dijit.byId(id).set('checked',!workflowProfileParameterAllChecked);
 }
});
workflowProfileParameterAllChecked=!workflowProfileParameterAllChecked;
}
//********************************************************************************************
//END - WORKFLOW AUTHORIZATION PARAMETERS (selection of profile)
//********************************************************************************************


function changeCreationInfo() {
  toShow=false;
  if (dijit.byId('idUser')) {
    dijit.byId('dialogCreationInfoCreator').set('value',
        dijit.byId('idUser').get('value'));
    dojo.byId('dialogCreationInfoCreatorLine').style.display='inline';
    toShow=true;
  } else if (dojo.byId('idUser')) {
    dijit.byId('dialogCreationInfoCreator').set('value',
        dojo.byId('idUser').value);
    dojo.byId('dialogCreationInfoCreatorLine').style.display='inline';
    toShow=true;
  } else {
    dojo.byId('dialogCreationInfoCreatorLine').style.display='none';
  }

  if (dijit.byId('creationDate')) {
    dijit.byId('dialogCreationInfoDate').set('value',
        dijit.byId('creationDate').get('value'));
    dojo.byId('dialogCreationInfoDateLine').style.display='inline';
    dojo.byId('dialogCreationInfoTimeLine').style.display='none';
    toShow=true;
  } else if (dojo.byId('creationDate')) {
    dijit.byId('dialogCreationInfoDate').set('value',
        dojo.byId('creationDate').value);
    dojo.byId('dialogCreationInfoDateLine').style.display='inline';
    dojo.byId('dialogCreationInfoTimeLine').style.display='none';
    toShow=true;
  } else if (dijit.byId('creationDateTime')) {
    val=dijit.byId('creationDateTime').get('value');
    valDate=val.substr(0, 10);
    valTime='T' + val.substr(11, 8);
    dijit.byId('dialogCreationInfoDate').set('value', valDate);
    dijit.byId('dialogCreationInfoTime').set('value', valTime);
    dojo.byId('dialogCreationInfoDateLine').style.display='inline';
    dojo.byId('dialogCreationInfoTimeLine').style.display='inline';
    toShow=true;
  } else if (dojo.byId('creationDateTime')) {
    val=dojo.byId('creationDateTime').value;
    valDate=val.substr(0, 10);
    valTime=val.substr(11, 8);
    dijit.byId('dialogCreationInfoDate').set('value', valDate);
    dijit.byId('dialogCreationInfoTime').set('value', valTime);
    dojo.byId('dialogCreationInfoDateLine').style.display='inline';
    dojo.byId('dialogCreationInfoTimeLine').style.display='inline';
    toShow=true;
  } else {
    dojo.byId('dialogCreationInfoDateLine').style.display='none';
    dojo.byId('dialogCreationInfoTimeLine').style.display='none';
  }
  if (toShow) {
    dijit.byId('dialogCreationInfo').show();
  }

  if (toShow) {
    dijit.byId('dialogCreationInfo').show();
  }
}

function saveCreationInfo() {
  if (dijit.byId('idUser')) {
    dijit.byId('idUser').set('value',
        dijit.byId('dialogCreationInfoCreator').get('value'));
  } else if (dojo.byId('idUser')) {
    dojo.byId('idUser').value=dijit.byId('dialogCreationInfoCreator').get(
        'value');
  }

  if (dijit.byId('creationDate')) {
    dijit.byId('creationDate').set('value',
        formatDate(dijit.byId('dialogCreationInfoDate').get('value')));
  } else if (dojo.byId('creationDate')) {
    dojo.byId('creationDate').value=formatDate(dijit.byId(
        'dialogCreationInfoDate').get('value'));
  } else {
    if (dijit.byId('creationDateTime')) {
      valDate=formatDate(dijit.byId('dialogCreationInfoDate').get('value'));
      valTime=formatTime(dijit.byId('dialogCreationInfoTime').get('value'));
      val=valDate + ' ' + valTime;
      dijit.byId('creationDateTime').set('value', val);
    } else if (dojo.byId('creationDateTime')) {
      valDate=format(Datedijit.byId('dialogCreationInfoDate').get('value'));
      valTime=format(Datedijit.byId('dialogCreationInfoTime').get('value'));
      val=valDate + ' ' + valTime;
      dojo.byId('dialogCreationInfoDate').value=val;
    }
  }
  formChanged();
  //dojo.byId('buttonDivCreationInfo').innerHTML="";
  //forceRefreshCreationInfo=true;
  saveObject();
  dijit.byId('dialogCreationInfo').hide();
}

function logLevel(value){
  var url='../tool/storeLogLevel.php?value=' + value;
  dojo.xhrPost({
    url : url,
    handleAs : "text",
    load : function(data, args) {
    }
  });
}

function showLogfile(name) {
  var atEnd=null;
  if (name=='last') {
    atEnd=function(name){
      var scroll=function() {
        dojo.query(".logFile .dijitDialogPaneContent").forEach(function(node, index, arr){
          node.scrollTop=parseInt(dojo.byId('logTableContainer').offsetHeight);
        });
      };
      setTimeout(scroll,500);
    };
  }
  
  loadDialog('dialogLogfile', atEnd, true, '&logname='+name, true);
}

function installPlugin(fileName,confirmed) {
  if (! confirmed) {
    actionOK=function() {
      installPlugin(fileName, true);
    };
    msg=i18n('confirmInstallPlugin', new Array(fileName));
    showConfirm(msg,actionOK);
  } else {
    showWait();
    dojo.xhrGet({
      url : "../plugin/loadPlugin.php?pluginFile="
          + encodeURIComponent(fileName),
      load : function(data) {
        if (data=="OK") {
          loadContent("pluginManagement.php", "centerDiv");
        } else if (data=="RELOAD") {
          showWait();
          noDisconnect=true;
          quitConfirmed=true;        
          dojo.byId("directAccessPage").value="pluginManagement.php";
          dojo.byId("menuActualStatus").value=menuActualStatus;
          dojo.byId("p1name").value="type";
          dojo.byId("p1value").value=forceRefreshMenu;
          forceRefreshMenu="";
          dojo.byId("directAccessForm").submit();     
        } else if (data.substr(0,8)=="CALLBACK") {
          var url=data.substring(9,data.indexOf('#'));
          window.open(url);
          var msg=data.substring(data.indexOf('#')+1,data.indexOf('##'));
          hideWait();
          callback=function() {loadContent("pluginManagement.php", "centerDiv");};
          showInfo(msg,callback);
          //setTimeout(callback,5000);
        } else {
          hideWait();
          showError(data+'<br/>');
        }
      },
      error : function(data) {
        hideWait();
        showError(data);
      }
    });
  }
}
function deletePlugin(fileName,confirmed) {
  if (! confirmed) {
    actionOK=function() {
      deletePlugin(fileName, true);
    };
    msg=i18n('confirmDeletePluginFile', new Array(fileName));
    showConfirm(msg,actionOK);
  } else {
    showWait();
    dojo.xhrGet({
      url : "../plugin/deletePlugin.php?pluginFile="
          + encodeURIComponent(fileName),
      load : function(data) {
        if (data=="OK") {
          loadContent("pluginManagement.php", "centerDiv");
        } else {
          hideWait();
          showError(data+'<br/>');
        }
      },
      error : function(data) {
        hideWait();
        showError(data);
      }
    });
  }
}
var historyShowHideWorkStatus=0;
function historyShowHideWork() {
  if (! dojo.byId('objectClass')) {return;}
  historyShowHideWorkStatus=((historyShowHideWorkStatus)?0:1);
  if (dijit.byId('dialogHistory')) {
    dijit.byId('dialogHistory').hide();
  } 
  var callBack = function(){
	showHistory(dojo.byId('objectClass').value);
  };
  saveDataToSession("showWorkHistory", historyShowHideWorkStatus, null, callBack);
}

// ====================================================
// * UPLOAD PLUGIN * //
// ====================================================

function uploadPlugin() {
  if (!isHtml5()) {
    return true;
  }
  if (dojo.byId('pluginFileName').innerHTML == "") {
    return false;
  }
  dojo.style(dojo.byId('downloadProgress'), {
    display : 'block'
  });
  showWait();
  return true;
}

function changePluginFile(list) {
  if (list.length > 0) {
    dojo.byId("pluginFileName").innerHTML=list[0]['name'];
    return true;
  }
}

function savePluginAck(dataArray) {
  if (!isHtml5()) {
    resultFrame=document.getElementById("resultPost");
    resultText=resultPost.document.body.innerHTML;
    dijit.byId('resultDivMain').set('content',resultText);
    savePluginFinalize();
    return;
  }
  if (dojo.isArray(dataArray)) {
    result=dataArray[0];
  } else {
    result=dataArray;
  }
  dojo.style(dojo.byId('downloadProgress'), {
    display : 'none'
  });
  if (dojo.isArray(dataArray)) {
    result=dataArray[0];
  } else {
    result=dataArray;
  }
  dojo.style(dojo.byId('downloadProgress'), {
    display : 'none'
  });
  contentNode = dojo.byId('resultDivMain');
  contentNode.innerHTML=result.message;
  contentNode.style.display="block"; 
  contentNode.style.opacity=1; 
  setTimeout("dojo.byId('resultDivMain').style.display='none';",2000);
  savePluginFinalize();
}
function savePluginFinalize() {
  contentNode = dojo.byId('resultDivMain');
  if (contentNode.innerHTML.indexOf('resultOK')>0) {
    setTimeout('loadContent("pluginManagement.php", "centerDiv");',1000);
  } else {
    hideWait();
  }
}

// ===================================================
// favorite reports management
// ===================================================

function refreshFavoriteReportList() {
  if (!dijit.byId('favoriteReports')) return;
  dijit.byId('favoriteReports').refresh();
  //var listContent=trim(dijit.byId('favoriteReports').get('content'));
}
function saveReportAsFavorite() {
  var fileName=dojo.byId('reportFile').value;
  var callback=function(){
    refreshFavoriteReportList();
    dijit.byId('listFavoriteReports').openDropDown();
    var delay=2000;
    var listContent=trim(dijit.byId('favoriteReports').get('content'));
    if (listContent=="") {delay=1;}
    hideReportFavoriteTooltip(delay);
  };
  loadContent("../tool/saveReportAsFavorite.php" , "resultDivMain", "reportForm", true, 'report',false,false, callback);
}

function showReportFavoriteTooltip() {
  var listContent=trim(dijit.byId('favoriteReports').get('content'));
  if (listContent=="") {
    return;
  }
  clearTimeout(closeFavoriteReportsTimeout);
  clearTimeout(openFavoriteReportsTimeout);
  openFavoriteReportsTimeout=setTimeout("dijit.byId('listFavoriteReports').openDropDown();",popupOpenDelay);
}

function hideReportFavoriteTooltip(delay) {
  if (!dijit.byId("listFavoriteReports")) return;
  clearTimeout(closeFavoriteReportsTimeout);
  clearTimeout(openFavoriteReportsTimeout);
  closeFavoriteReportsTimeout=setTimeout('dijit.byId("listFavoriteReports").closeDropDown();',delay);
}

function removeFavoriteReport(id) {
  dojo.xhrGet({
    url: '../tool/removeFavoriteReport.php?idFavorite='+id,
    load: function(data,args) { 
      refreshFavoriteReportList(); 
    }
  });
}
function reorderFavoriteReportItems() {
  var nodeList=dndFavoriteReports.getAllNodes();
  var param="";
  for (var i=0; i < nodeList.length; i++) {
    var domNode=nodeList[i];
    var item=nodeList[i].id.substr(11);
    var order=dojo.byId("favoriteReportOrder" + item);
    if (dojo.hasClass(domNode,'dojoDndItemAnchor')) {
      order.value=null;
      dojo.removeClass(domNode,'dojoDndItemAnchor');
      dojo.query('dojoDndItemAnchor').removeClass('dojoDndItemAnchor');
      //continue;
    }
    if (order) {
      order.value=i + 1;
      param+=((param)?'&':'?')+"favoriteReportOrder"+item+"="+(i+1);
    }
  }
  dojo.xhrPost({
    url: '../tool/saveReportFavoriteOrder.php'+param,
    handleAs: "text",
    load: function(data,args) {
      refreshFavoriteReportList(); 
    }
  });
}

function checkEmptyReportFavoriteTooltip() {
  var listContent=trim(dijit.byId('favoriteReports').get('content'));
  if (listContent=="") {
    dijit.byId("listFavoriteReports").closeDropDown();
  }
}

function showTickets(refType, refId) {
  loadDialog('dialogShowTickets', null, true, '&refType='+refType+'&refId='+refId, true);
}

function showMenuList() {
  clearTimeout(closeMenuListTimeout);
  menuListAutoshow=true;
  clearTimeout(openMenuListTimeout);
  openMenuListTimeout=setTimeout("dijit.byId('menuSelector').loadAndOpenDropDown();",popupOpenDelay);
  
}
function hideMenuList(delay, item) {
  if (! menuListAutoshow) return;
  clearTimeout(closeMenuListTimeout);
  clearTimeout(openMenuListTimeout);
  closeMenuListTimeout=setTimeout("dijit.byId('menuSelector').closeDropDown();",delay);
}

function saveRestrictTypes() {
  $callback=function() {
    dojo.xhrGet({
      url : '../tool/getSingleData.php?dataType=restrictedTypeClass'
        +'&idProject='+dojo.byId('idProjectParam').value
        +'&idProjectType='+dojo.byId('idProjectTypeParam').value
        +'&idProfile='+dojo.byId('idProfile').value,
      handleAs : "text",
      load : function(data) {
        dojo.byId('resctrictedTypeClassList').innerHTML=data;
      }
    });
  }
  loadContent("../tool/saveRestrictTypes.php" , "resultDivMain", "restrictTypesForm", true, 'report',false,false, $callback);
  dijit.byId('dialogRestrictTypes').hide();
}

/*************************************************************************************
 * 				START FUNCTION RESTRICTION LIST
 ************************************************************************************/

function saveRestrictProductList() {
	loadContent("../tool/saveRestrictProductList.php" , "resultDivMain", "dialogRestrictProductListForm", true);
	dijit.byId('dialogRestrictProductList').hide();
}

/*************************************************************************************
 * 				END FUNCTION RESTRICTION LIST
 ************************************************************************************/

function getMaxWidth(document){
  return Math.max( document.scrollWidth, document.offsetWidth, 
      document.clientWidth);
}

function getMaxHeight(document){
  return Math.max( document.scrollHeight, document.offsetHeight, 
      document.clientHeight);
}

function planningToCanvasToPDF(){

  var iframe = document.createElement('iframe');
  
  //this onload is for firefox but also work on others browsers
  iframe.onload = function() {
  var orientation="landscape";  // "portrait" ou "landscape"
  if(!document.getElementById("printLandscape").checked)orientation="portrait";
  var ratio=parseInt(document.getElementById("printZoom").value)/100;
  var repeatIconTask=document.getElementById("printRepeat").checked; // If true this will repeat on each page the icon
  loadContent("../tool/submitPlanningPdf.php", "resultDivMain", 'planningPdfForm', false,null,null,null,function(){showWait();});
  var sizeElements=[];
  var marge=30;
  var widthIconTask=0; // the width that icon+task represent
  //var heightColumn=parseInt(document.getElementById('leftsideTop').offsetHeight)*ratio;
  //damian #exportPDF
  var deviceRatio = window.devicePixelRatio;
  if(!deviceRatio){
	  deviceRatio = 1;
  }
  var heightColumn=parseInt(document.getElementById('leftsideTop').offsetHeight)*deviceRatio;
  //var heightRow=21*ratio;
  var heightRow=21*deviceRatio;
  //var widthRow=(parseInt(dojo.query('.ganttRightTitle')[0].offsetWidth)-1)*ratio;
  var widthRow=(parseInt(dojo.query('.ganttRightTitle')[0].offsetWidth)-1);
  var nbRowTotal=0;
  var nbColTotal=0;
  // init max width/height by orientation
  var pageFormat='A4';
  if(document.getElementById("printFormatA3").checked)pageFormat="A3";
  var imageZoomIn=1.3/ratio;
  var imageZoomOut=1/imageZoomIn;
  ratio=1;
  var maxWidth=(596-(2*marge))*imageZoomIn;
  var maxHeight=(842-(2*marge))*imageZoomIn;
  if (pageFormat=='A3') {
    var maxTemp=maxWidth;
    maxWidth=maxHeight;
    maxHeight=2*maxTemp;
  }
  if(orientation=="landscape"){
    var maxTemp=maxWidth;
    maxWidth=maxHeight;
    maxHeight=maxTemp;
  }
  
  //We create an iframe will which contain the planning to transform it in image
  var frameContent=document.getElementById("iframeTmpPlanning");
  
  var cssLink2 = document.createElement("link");
  cssLink2.href = "css/projeqtor.css"; 
  cssLink2 .rel = "stylesheet"; 
  cssLink2 .type = "text/css"; 
  frameContent.contentWindow.document.head.appendChild(cssLink2);
  
  var cssLink = document.createElement("link");
  cssLink.href = "css/jsgantt.css"; 
  cssLink .rel = "stylesheet"; 
  cssLink .type = "text/css";
  frameContent.contentWindow.document.head.appendChild(cssLink);
  
  /*var css = document.createElement("style");
  css .type = "text/css";
  frameContent.contentWindow.document.head.appendChild(css);
  styles = '.rightTableLine{ height:22px; }';
  
  if (css.styleSheet) css.styleSheet.cssText = styles;
  else css.appendChild(document.createTextNode(styles));*/
  var heightV=(heightColumn+getMaxHeight(document.getElementById('leftside'))+(getMaxHeight(document.getElementById('leftside'))/21))+'px';
  
  frameContent.style.position='absolute';
  frameContent.style.width=(4+parseInt(document.getElementById('leftGanttChartDIV').style.width)+getMaxWidth(document.getElementById('rightTableContainer')))+'px';
  frameContent.style.height=heightV;
  frameContent.style.border='0';
  //frameContent.style.top='0';
  //frameContent.style.left='0';
  frameContent.contentWindow.document.body.innerHTML='<div style="float:left;width:'+document.getElementById('leftGanttChartDIV').style.width+';overflow:hidden;height:'+heightV+';">'+document.getElementById('leftGanttChartDIV').innerHTML+'</div><div style="float:left;width:'+getMaxWidth(document.getElementById('rightTableContainer'))+'px;height:'+heightV+';">'+document.getElementById('GanttChartDIV').innerHTML+"</div>";

  frameContent.contentWindow.document.getElementById('ganttScale').style.display='none';
  frameContent.contentWindow.document.getElementById('topGanttChartDIV').style.width=getMaxWidth(document.getElementById('rightTableContainer'))+'px';
  frameContent.contentWindow.document.getElementById('topGanttChartDIV').style.overflow='visible';
  frameContent.contentWindow.document.getElementById('mainRightPlanningDivContainer').style.overflow='visible';
  frameContent.contentWindow.document.getElementById('rightGanttChartDIV').style.overflow='visible';
  frameContent.contentWindow.document.getElementById('mainRightPlanningDivContainer').style.height=(getMaxHeight(document.getElementById('leftside')))+'px';
  frameContent.contentWindow.document.getElementById('rightGanttChartDIV').style.height=(getMaxHeight(document.getElementById('leftside')))+'px';
  frameContent.contentWindow.document.getElementById('rightGanttChartDIV').style.height=(getMaxHeight(document.getElementById('leftside')))+'px';
  frameContent.contentWindow.document.getElementById('dndSourceTable').style.height=(getMaxHeight(document.getElementById('leftside')))+'px';
  frameContent.contentWindow.document.getElementById('vScpecificDay_1').style.height=(getMaxHeight(document.getElementById('leftside')))+'px';
  frameContent.contentWindow.document.getElementById('leftside').style.top="0";
  frameContent.contentWindow.document.getElementById('leftsideTop').style.width=document.getElementById('leftGanttChartDIV').style.width;
  frameContent.contentWindow.document.getElementById('leftside').style.width=document.getElementById('leftGanttChartDIV').style.width;
  frameContent.contentWindow.document.getElementById('rightGanttChartDIV').style.overflowX="visible";
  frameContent.contentWindow.document.getElementById('rightGanttChartDIV').style.overflowY="visible";
  //Calculate each width column in left top side
  for(var i=0; i<dojo.query("[id^='topSourceTable'] tr")[1].childNodes.length;i++){
    sizeElements.push((dojo.query("[id^='topSourceTable'] tr")[1].childNodes[i].offsetWidth)*ratio);
  }
  for(var i=0; i<dojo.query("[class^='rightTableLine']").length;i++){
    dojo.query("[class^='rightTableLine']")[i].style.width=(parseInt(dojo.query("[class^='rightTableLine']")[i].style.width)-1)+"px";
  }
  for(var i=0; i<dojo.query("[class^='ganttDetail weekBackground']").length;i++){
    dojo.query("[class^='ganttDetail weekBackground']")[i].style.width=(parseInt(dojo.query("[class^='ganttDetail weekBackground']")[i].style.width)-1)+"px";
  }
  
  widthIconTask=(sizeElements[0]+sizeElements[1])*deviceRatio;
  if (widthIconTask>parseInt(document.getElementById('leftGanttChartDIV').style.width)*deviceRatio) widthIconTask=parseInt(document.getElementById('leftGanttChartDIV').style.width)*deviceRatio;
  
  sizeColumn=parseInt(dojo.query(".ganttRightTitle")[0].style.width)*ratio;
  
  frameContent.contentWindow.document.getElementById('rightGanttChartDIV').style.width=getMaxWidth(frameContent.contentWindow.document.getElementById('rightGanttChartDIV'))+'px';
  frameContent.contentWindow.document.getElementById('topGanttChartDIV').style.width=getMaxWidth(frameContent.contentWindow.document.getElementById('rightGanttChartDIV'))+'px';
  frameContent.contentWindow.document.getElementById('mainRightPlanningDivContainer').style.width=getMaxWidth(frameContent.contentWindow.document.getElementById('rightGanttChartDIV'))+'px';
  //add border into final print
  frameContent.contentWindow.document.getElementById('leftsideTop').innerHTML ='<div id="separatorLeftGanttChartDIV2" style="position:absolute;height:100%;z-index:10000;width:4px;background-color:#C0C0C0;"></div>'+frameContent.contentWindow.document.getElementById('leftsideTop').innerHTML;
  frameContent.contentWindow.document.getElementById('leftside').innerHTML ='<div id="separatorLeftGanttChartDIV" style="position:absolute;height:100%;z-index:10000;width:4px;background-color:#C0C0C0;"></div>'+frameContent.contentWindow.document.getElementById('leftside').innerHTML;
  frameContent.contentWindow.document.getElementById('leftside').style.width=(parseInt(frameContent.contentWindow.document.getElementById('leftside').style.width)+parseInt(frameContent.contentWindow.document.getElementById('separatorLeftGanttChartDIV').style.width))+'px';
  frameContent.contentWindow.document.getElementById('leftsideTop').style.width=frameContent.contentWindow.document.getElementById('leftside').style.width;
  frameContent.contentWindow.document.getElementById('separatorLeftGanttChartDIV').style.left=(parseInt(frameContent.contentWindow.document.getElementById('leftside').style.width)-4)+'px';
  frameContent.contentWindow.document.getElementById('separatorLeftGanttChartDIV2').style.left=(parseInt(frameContent.contentWindow.document.getElementById('leftsideTop').style.width)-4)+'px';
  frameContent.contentWindow.document.getElementById('rightGanttChartDIV').style.width=frameContent.contentWindow.document.getElementById('rightTableContainer').style.width;
  frameContent.contentWindow.document.getElementById('rightGanttChartDIV').style.height=frameContent.contentWindow.document.getElementById('rightTableContainer').style.height;

  var tabImage=[]; //Contain pictures 
  var mapImage={}; //Contain pictures like key->value, cle=namePicture, value=base64(picture)
  
  //Start the 4 prints function
  //Print image activities and projects
  html2canvas(frameContent.contentWindow.document.getElementById('leftside')).then(function(leftElement) {
    //Print image column left side
    html2canvas(frameContent.contentWindow.document.getElementById('leftsideTop')).then(function(leftColumn) { 
      //Print right Line
      html2canvas(frameContent.contentWindow.document.getElementById('rightGanttChartDIV')).then(function(rightElement) {
        //Print right column
        html2canvas(frameContent.contentWindow.document.getElementById('rightside')).then(function(rightColumn) {
          if(ratio!=1){
            leftElement=cropCanvas(leftElement,0,0,leftElement.width,leftElement.height,ratio);
            leftColumn=cropCanvas(leftColumn,0,0,leftColumn.width,leftColumn.height,ratio);
            rightElement=cropCanvas(rightElement,0,0,rightElement.width,rightElement.height,ratio);
            rightColumn=cropCanvas(rightColumn,0,0,rightColumn.width,rightColumn.height,ratio);
          }
          //Init number of total rows
          nbRowTotal=Math.round(leftElement.height/heightRow); 
          //frameContent.parentNode.removeChild(frameContent);
          //Start pictures's calcul
          firstEnterHeight=true;
          var EHeightValue=0; //Height pointer cursor
          var EHeight=leftElement.height; //total height
          while((Math.ceil(EHeight/maxHeight)>=1 || firstEnterHeight) && EHeight>heightRow){
            var calculHeight=maxHeight;
            var ELeftWidth=leftElement.width; //total width
            var ERightWidth=rightElement.width; //total width
            var addHeighColumn=0;
            if(firstEnterHeight || (!firstEnterHeight && repeatIconTask)){
              addHeighColumn=heightColumn;
            }
            var heightElement=0;
            while(calculHeight-addHeighColumn>=heightRow && nbRowTotal!=0){
              calculHeight-=heightRow;
              heightElement+=heightRow;
              nbRowTotal--;
            }
            var iterateurColumnLeft=0;
            firstEnterWidth=true;
            var widthElement=0;
            var imageRepeat=null;
            if(repeatIconTask){
              imageRepeat=combineCanvasIntoOne(
                              cropCanvas(leftColumn,0,0,widthIconTask,heightColumn),
                              cropCanvas(leftElement,0,EHeightValue,widthIconTask,heightElement),
                              true);
            }
            var canvasList=[];
            while(ELeftWidth/maxWidth>=1 || (!firstEnterWidth && ELeftWidth>0)){
              firstEnterWidth2=true;
              oldWidthElement=widthElement;
              while(iterateurColumnLeft<sizeElements.length && ELeftWidth>=sizeElements[iterateurColumnLeft]){
                ELeftWidth-=sizeElements[iterateurColumnLeft];
                widthElement+=sizeElements[iterateurColumnLeft]*deviceRatio;
                if(repeatIconTask && !firstEnterWidth && firstEnterWidth2)ELeftWidth+=widthIconTask;
                iterateurColumnLeft++;
                firstEnterWidth2=false;
              }
              if(oldWidthElement==widthElement){
                widthElement+=ELeftWidth;
                ELeftWidth=0;
              }
              if(!firstEnterWidth){
                if(repeatIconTask){
                  canvasList.push(combineCanvasIntoOne(imageRepeat,
                                  combineCanvasIntoOne(
                                      cropCanvas(leftColumn,oldWidthElement,0,widthElement-oldWidthElement,heightColumn),
                                      cropCanvas(leftElement,oldWidthElement,EHeightValue,widthElement-oldWidthElement,heightElement),
                                      true),
                                      false));
                }else{
                  if(firstEnterHeight){
                    canvasList.push(combineCanvasIntoOne(
                                        cropCanvas(leftColumn,oldWidthElement,0,widthElement-oldWidthElement,heightColumn),
                                        cropCanvas(leftElement,oldWidthElement,EHeightValue,widthElement-oldWidthElement,heightElement),
                                        true));
                  }else{
                    canvasList.push(cropCanvas(leftElement,oldWidthElement,EHeightValue,widthElement-oldWidthElement,heightElement));
                  } 
                }
              }else{
                if(firstEnterHeight || repeatIconTask){
                  canvasList.push(combineCanvasIntoOne(
                                        cropCanvas(leftColumn,oldWidthElement,0,widthElement-oldWidthElement,heightColumn),
                                        cropCanvas(leftElement,oldWidthElement,EHeightValue,widthElement-oldWidthElement,heightElement),
                                        true));
                }else{
                  canvasList.push(cropCanvas(leftElement,oldWidthElement,EHeightValue,widthElement-oldWidthElement,heightElement));                  
                }
              }
              firstEnterWidth=false;
            }
            if(canvasList.length==0){
              if(firstEnterHeight || repeatIconTask){
                canvasList.push(combineCanvasIntoOne(
                                        cropCanvas(leftColumn,0,0,leftColumn.width,heightColumn),
                                        cropCanvas(leftElement,0,EHeightValue,leftElement.width,heightElement),
                                        true));
              }else{
                canvasList.push(cropCanvas(leftElement,0,EHeightValue,leftElement.width,heightElement));
              }
            }
            firstEnterWidth=true;
            if(repeatIconTask && leftColumn.width>widthIconTask){
              imageRepeat=combineCanvasIntoOne(combineCanvasIntoOne(
                                                    cropCanvas(leftColumn,0,0,widthIconTask,heightColumn),
                                                    cropCanvas(leftElement,0,EHeightValue,widthIconTask,heightElement),
                                                    true),
                                               combineCanvasIntoOne(
                                                    cropCanvas(leftColumn,leftColumn.width-4,0,4,heightColumn),
                                                    cropCanvas(leftElement,leftElement.width-4,EHeightValue,4,heightElement),
                                                    true),
                                               false);
            }
            widthElement=0;
            firstEnterWidth=true;
            var canvasList2=[];
            //Init number of total cols
            nbColTotal=Math.round(rightElement.width/widthRow); 
            var countIteration=0;
            while((Math.ceil(ERightWidth/maxWidth)>=1 || (!firstEnterWidth && ERightWidth>0)) && nbColTotal>0){
              countIteration++;
              firstEnterWidth2=true;
              oldWidthElement=widthElement;
              limit=0;
              if(firstEnterWidth)limit=canvasList[canvasList.length-1].width;
              if(!firstEnterWidth && repeatIconTask)limit=widthIconTask;
              var currentWidthElm=0;
              while(ERightWidth>widthRow && currentWidthElm+widthRow<maxWidth-limit && nbColTotal>0){
                ERightWidth-=widthRow;
                widthElement+=widthRow;
                currentWidthElm+=widthRow;
                firstEnterWidth2=false;
                nbColTotal--;
              }
              if(!firstEnterWidth){
                if(currentWidthElm!=0 && widthElement!=oldWidthElement)
                  if(repeatIconTask){
                    canvasList2.push(combineCanvasIntoOne(imageRepeat,
                                       combineCanvasIntoOne(
                                           cropCanvas(rightColumn,oldWidthElement+1,0,currentWidthElm,heightColumn),
                                           cropCanvas(rightElement,oldWidthElement,EHeightValue,currentWidthElm,heightElement),
                                           true),
                                       false));
                }else{
                  if(firstEnterHeight){
                    canvasList2.push(combineCanvasIntoOne(
                                          cropCanvas(rightColumn,oldWidthElement+1,0,currentWidthElm,heightColumn),
                                          cropCanvas(rightElement,oldWidthElement,EHeightValue,currentWidthElm,heightElement),
                                          true));
                  }else{
                    canvasList2.push(cropCanvas(rightElement,oldWidthElement,EHeightValue,currentWidthElm,heightElement));
                  }
                }
              }else{
                if(widthElement==0){
                  canvasList2.push(canvasList[canvasList.length-1]);
                }else if(firstEnterHeight || repeatIconTask){
                  canvasList2.push(combineCanvasIntoOne(canvasList[canvasList.length-1],
                                        combineCanvasIntoOne(
                                            cropCanvas(rightColumn,oldWidthElement+1,0,currentWidthElm,heightColumn),
                                            cropCanvas(rightElement,oldWidthElement,EHeightValue,currentWidthElm,heightElement),
                                            true),
                                        false));
                }else{
                  canvasList2.push(combineCanvasIntoOne(canvasList[canvasList.length-1],
                                        cropCanvas(rightElement,oldWidthElement,EHeightValue,currentWidthElm,heightElement),
                                        false));
                }
              }
              if(nbColTotal==0 || countIteration>100){
                ERightWidth=0;
              }
              firstEnterWidth=false;
            }
            var baseIterateur=tabImage.length;
            for(var i=0;i<canvasList.length-1;i++){
              
              //Add image to mapImage in base64 format
              mapImage["image"+(i+baseIterateur)]=canvasList[i].toDataURL();
              
              //Add to tabImage an array wich contain parameters to put an image into a pdf page with a pagebreak if necessary
              ArrayToPut={image: "image"+(i+baseIterateur),width: canvasList[i].width*imageZoomOut,height:canvasList[i].height*imageZoomOut};
              if(!(canvasList2.length==0 && i==canvasList.length-1)){
                ArrayToPut['pageBreak']='after';
              }
              tabImage.push(ArrayToPut);
            }
            for(var i=0;i<canvasList2.length;i++){
              if(canvasList2[i].width-widthIconTask>4){
                //Add image to mapImage in base64 format
                mapImage["image"+(i+canvasList.length+baseIterateur)]=canvasList2[i].toDataURL();
                
                //Add to tabImage an array wich contain parameters to put an image into a pdf page with a pagebreak if necessary
                ArrayToPut={image: "image"+(i+canvasList.length+baseIterateur),width: canvasList2[i].width*imageZoomOut,height:canvasList2[i].height*imageZoomOut};
                if(i!=canvasList2.length-1){
                  ArrayToPut['pageBreak']='after';
                }
                tabImage.push(ArrayToPut);
              }
            }
            EHeight-=maxHeight-calculHeight;
            EHeightValue+=maxHeight-calculHeight;
            firstEnterHeight=false;
          }
          var dd = {
             pageMargins: [ marge, marge, marge, marge ],
             pageOrientation: orientation,
             content: tabImage,
             images: mapImage,
             footer: function(currentPage, pageCount) {  return { fontSize : 8, text: currentPage.toString() + ' / ' + pageCount , alignment: 'center' };},
             pageSize: pageFormat
          };
          if( !dojo.isIE ) {
            var userAgent = navigator.userAgent.toLowerCase(); 
            var IEReg = /(msie\s|trident.*rv:)([\w.]+)/; 
            var match = IEReg.exec(userAgent); 
            if( match )
              dojo.isIE = match[2] - 0;
            else
              dojo.isIE = undefined;
          }
          var pdfFileName='ProjeQtOr_Planning';
          var now = new Date();
          pdfFileName+='_'+formatDate(now).replace(/-/g,'')+'_'+formatTime(now).replace(/:/g,'');
          pdfFileName+='.pdf';
          if((dojo.isIE && dojo.isIE>0) || window.navigator.userAgent.indexOf("Edge") > -1) {
            pdfMake.createPdf(dd).download(pdfFileName);
          }else{
            pdfMake.createPdf(dd).download(pdfFileName);
          }
          // open the PDF in a new window
          //pdfMake.createPdf(dd).open();
          // print the PDF (temporarily Chrome-only)
         // pdfMake.createPdf(dd).print();
          // download the PDF (temporarily Chrome-only)
          dijit.byId('dialogPlanningPdf').hide();
          iframe.parentNode.removeChild(iframe);
          setTimeout('hideWait();',100);
        });
      });
    });
  });
  };
  iframe.id="iframeTmpPlanning";
  document.body.appendChild(iframe);
}
function cropCanvas(canvasToCrop,x,y,w,h,r){
  if(typeof r=='undefined')r=1;
    var tempCanvas = document.createElement("canvas"),
    tCtx = tempCanvas.getContext("2d");
    tempCanvas.width = w*r;
    tempCanvas.height = h*r;
    if(w!=0 && h!=0)tCtx.drawImage(canvasToCrop,x,y,w,h,0,0,w*r,h*r);
    return tempCanvas;
}

//addBottom=true : we add the canvas2 at the bottom of canvas1, addBottom=false : we add the canvas2 at the right of canvas1
function combineCanvasIntoOne(canvas1,canvas2,addBottom){
  var tempCanvas = document.createElement("canvas");
  var tCtx = tempCanvas.getContext("2d");
  var ajoutWidth=0;
  var ajoutHeight=0;
  var x=0;
  var y=0;
  if(addBottom){
    ajoutHeight=canvas2.height;
    y=canvas1.height;
  }else{
    ajoutWidth=canvas2.width;
    x=canvas1.width;
  }
  tempCanvas.width = canvas1.width+ajoutWidth;
  tempCanvas.height = canvas1.height+ajoutHeight;
  if(canvas1.width!=0 && canvas1.height!=0)tCtx.drawImage(canvas1,0,0,canvas1.width,canvas1.height);
  if(canvas1.width!=0 && canvas1.height!=0)if(canvas2.width!=0 && canvas2.height!=0)tCtx.drawImage(canvas2,0,0,canvas2.width,canvas2.height,x,y,canvas2.width,canvas2.height);
  return tempCanvas;
}

function changeParamDashboardTicket(paramToSend){
  loadContent('dashboardTicketMain.php?'+paramToSend, 'centerDiv', 'dashboardTicketMainForm');
}

function changeDashboardTicketMainTabPos(){
  var listChild=dojo.byId('dndDashboardLeftParameters').childNodes[1].childNodes;
  addLeft="";
  iddleList=',"iddleList":[';
  if(listChild.length>1){
    addLeft="[";
    for(var i=1;i<listChild.length;i++){
      getId="";
      if(listChild[i].id.includes('dialogDashboardLeftParametersRow')){
        getId=listChild[i].id.split('dialogDashboardLeftParametersRow')[1];
      }
      if(listChild[i].id.includes('dialogDashboardRightParametersRow')){
        getId=listChild[i].id.split('dialogDashboardRightParametersRow')[1];
      }
      //iddleList+='"'+dijit.byId('dialogTodayParametersIdle'+listChild[i].id.split('dialogDashboardLeftParametersRow')[1]).get('checked')+'"';
      if(getId!=""){
        addLeft+='"'+getId+'"';
        iddleList+='{"name":"'+getId+'","idle":'+dijit.byId('tableauBordTabIdle'+getId).get('checked')+'}';
        if(i+1!=listChild.length){
          addLeft+=',';
          iddleList+=',';
        } 
      }
    }
    addLeft+="]";
    if(dojo.byId('dndDashboardRightParameters').childNodes[0].childNodes.length>1){
      iddleList+=',';
    }
  }
  
  var listChild=dojo.byId('dndDashboardRightParameters').childNodes[0].childNodes;
  addRight="";
  if(listChild.length>1){
    addRight="[";
    for(var i=1;i<listChild.length;i++){
      getId="";
        if(listChild[i].id.includes('dialogDashboardLeftParametersRow')){
          getId=listChild[i].id.split('dialogDashboardLeftParametersRow')[1];
        }
        if(listChild[i].id.includes('dialogDashboardRightParametersRow')){
          getId=listChild[i].id.split('dialogDashboardRightParametersRow')[1];
        }
        //iddleList+='"'+dijit.byId('dialogTodayParametersIdle'+listChild[i].id.split('dialogDashboardLeftParametersRow')[1]).get('checked')+'"';
        if(getId!=""){
          addRight+='"'+getId+'"';
          iddleList+='{"name":"'+getId+'","idle":'+dijit.byId('tableauBordTabIdle'+getId).get('checked')+'}';
          if(i+1!=listChild.length){
            addRight+=',';
            iddleList+=',';
          }
        }
      }
    addRight+="]";
  }
  toSend='{"addLeft":';
  if(addLeft==""){
    addLeft="[]";
  }
  toSend+=addLeft;
  
  toSend+=',"addRight":';
  if(addRight==""){
    addRight="[]";
  }
  iddleList+="]";
  toSend+=addRight+iddleList+"}";
//CHANGE qCazelles - Requirement dashboard - Ticket 90
//Old
//	loadContent('dashboardTicketMain.php?updatePosTab='+toSend, 'centerDiv', 'dashboardTicketMainForm');
//}
//New
  if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value=='DashboardRequirement') {
	  loadContent('dashboardRequirementMain.php?updatePosTab='+toSend, 'centerDiv', 'dashboardRequirementMainForm');
  } else {
	  loadContent('dashboardTicketMain.php?updatePosTab='+toSend, 'centerDiv', 'dashboardTicketMainForm');
  }
}

function changeParamDashboardRequirement(paramToSend){
  loadContent('dashboardRequirementMain.php?'+paramToSend, 'centerDiv', 'dashboardRequirementMainForm');
}
//END CHANGE qCazelles - Requirement dashboard - Ticket 90

function getLocalLocation(){
  var availableScaytLocales=["en_US", "en_GB", "pt_BR", "da_DK", "nl_NL", "en_CA", "fi_FI", "fr_FR", "fr_CA", "de_DE", "el_GR", "it_IT", "nb_NO", "pt_PT", "es_ES", "sv_SE"];
  var correspondingLocales= ["en",    "",      "pt-br", "",      "nl",    "",      "",      "fr",    "fr-ca", "de",    "el",    "it",    "",      "pt",    "es",    ""];
  var locale=dojo.locale;
  if (currentLocale) {
    var pos=correspondingLocales.indexOf(currentLocale);
    if (pos>=0) {
      locale=availableScaytLocales[pos];
    }
  }
  return locale;
}
function getLocalScaytAutoStartup() {
  if (typeof scaytAutoStartup == "undefined" || scaytAutoStartup===null || scaytAutoStartup==='' || scaytAutoStartup=='YES' || scaytAutoStartup===true) {
    return true;
  } else {
    return scaytAutoStartup;
  }
}

function commentImputationSubmit(year,week,idAssignment,refType,refId){
  var text=dijit.byId('commentImputation').get('value');
  if(text.trim()==''){
    showAlert(i18n('messageMandatory',[i18n('colComment')]));
    return;
  }
  showWait();
  dojo.xhrPost({
    url : "../tool/dynamicDialogCommentImputation.php?year="+year+"&week="+week+"&idAssignment="+idAssignment+"&refTypeComment="+refType+"&refIdComment="+refId,
    handleAs : "text",
    form : 'commentImputationForm',
    load : function(data, args) {
      formChangeInProgress=false;
      document.getElementById("showBig"+idAssignment).style.display='block'; 
      dojo.byId("showBig"+idAssignment).childNodes[0].onmouseover=function(){
        showBigImage(null,null,this,data);
      };
      dijit.byId('dialogCommentImputation').hide();
      hideWait();
    },
    error : function() {
      hideWait();
    }
  });
}

function commentImputationTitlePopup(type){
  title='';
  if(type=='add'){
    title= i18n('commentImputationAdd');
  }else if(type=='view'){
    title= i18n('commentImputationView');
  }
  dijit.byId('dialogCommentImputation').set('title',title);
}

// Evaluation criteria
function addTenderEvaluationCriteria(callForTenderId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&mode=add&callForTenderId="+callForTenderId;
  loadDialog('dialogCallForTenderCriteria', null, true, params, false);
}
function editTenderEvaluationCriteria(criteriaId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&mode=edit&criteriaId="+criteriaId;
  loadDialog('dialogCallForTenderCriteria', null, true, params, false);
}
function saveTenderEvaluationCriteria() {
  var formVar=dijit.byId("dialogTenderCriteriaForm");
  if (!formVar) {
    showError(i18n("errorSubmitForm", new Array("n/a", "n/a", "dialogTenderCriteriaForm")));
    return;
  }
  if (formVar.validate()) {
    loadContent("../tool/saveTenderEvaluationCriteria.php", "resultDivMain", "dialogTenderCriteriaForm", true,'tenderEvaluationCriteria');
    dijit.byId('dialogCallForTenderCriteria').hide();
  }  
}
function removeTenderEvaluationCriteria(criteriaId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeTenderEvaluationCriteria.php?criteriaId="+criteriaId, "resultDivMain", null,true,'tenderEvaluationCriteria');
  };
  msg=i18n('confirmDelete', new Array(i18n('TenderEvaluationCriteria'), criteriaId));
  showConfirm(msg, actionOK);
}

//Tender submission
function addTenderSubmission(callForTenderId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&mode=add&callForTenderId="+callForTenderId;
  loadDialog('dialogCallForTenderSubmission', null, true, params, false);
}
function editTenderSubmission(tenderId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  var params="&mode=edit&tenderId="+tenderId;
  loadDialog('dialogCallForTenderSubmission', null, true, params, false);
}
function saveTenderSubmission() {
  var formVar=dijit.byId("dialogTenderSubmissionForm");
  if (dijit.byId('dialogCallForTenderSubmissionProvider') && ! trim(dijit.byId('dialogCallForTenderSubmissionProvider').get("value"))) {
    showAlert(i18n('messageMandatory', new Array(i18n('colIdProvider'))));
    return;
  }
  if (!formVar) {
    showAlert(i18n("errorSubmitForm", new Array("n/a", "n/a", "dialogTenderSubmissionForm")));
    return;
  }
  if (formVar.validate()) {
    loadContent("../tool/saveTenderSubmission.php", "resultDivMain", "dialogTenderSubmissionForm", true,'tenderSubmission');
    dijit.byId('dialogCallForTenderSubmission').hide();
  }  
}
function removeTenderSubmission(tenderId) {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  actionOK=function() {
    loadContent("../tool/removeTenderSubmission.php?tenderId="+tenderId, "resultDivMain", null,true,'tenderSubmission');
  };
  msg=i18n('confirmDelete', new Array(i18n('Tender'), tenderId))+'<br/><b>'+i18n('messageAlerteDeleteTender')+'</b>';
  showConfirm(msg, actionOK);
}


function changeTenderEvaluationValue(index) {
  var value=dijit.byId("tenderEvaluation_"+index).get("value");
  var coef=dojo.byId("tenderCoef_"+index).value;
  var total=value*coef;
  dijit.byId("tenderTotal_"+index).set("value",total);
  var list=dojo.byId('idTenderCriteriaList').value.split(';');
  var sum=0;
  for (var i=0;i<list.length;i++) {
    sum+=dijit.byId('tenderTotal_'+list[i]).get('value');
  }
  dijit.byId("tenderTotal").set("value",sum);
  var newValue=Math.round(sum*dojo.byId('evaluationMaxCriteriaValue').value/dojo.byId('evaluationSumCriteriaValue').value*100)/100;
  dijit.byId("evaluationValue").set("value",newValue);
}
// =============================================================================
// = JobDefinition
// =============================================================================

/**
 * Display a add line Box
 *
 */
function addJobDefinition(checkId) {
  var params="&checkId=" + checkId;
  loadDialog('dialogJobDefinition', null, true, params);
}

/**
 * Display a edit line Box
 *
 */
function editJobDefinition(checkId, lineId) {
  var params="&checkId=" + checkId + "&lineId=" + lineId;
  loadDialog('dialogJobDefinition', null, true, params);
}

/**
 * save a line (after addDetail or editDetail)
 *
 */
function saveJobDefinition() {
  if (!dijit.byId("dialogJobDefinitionName").get('value')) {
    showAlert(i18n('messageMandatory', new Array(i18n('colName'))));
    return false;
  }
  loadContent("../tool/saveJobDefinition.php", "resultDivMain",
      "dialogJobDefinitionForm", true, 'jobDefinition');
  dijit.byId('dialogJobDefinition').hide();

}

/**
 * Display a delete line Box
 *
 */
function removeJobDefinition(lineId) {
  var params="?lineId=" + lineId;
  // loadDialog('dialogJobDefinition',null, true, params)
  // dojo.byId("jobDefinitionId").value=lineId;
  actionOK=function() {
    loadContent("../tool/removeJobDefinition.php" + params,
        "resultDivMain", null, true, 'jobDefinition');
  };
  msg=i18n('confirmDelete', new Array(i18n('JobDefinition'), lineId));
  showConfirm(msg, actionOK);
}

// =============================================================================
// = Joblist
// =============================================================================

function showJoblist(objectClass) {
  if (!objectClass) {
    return;
  }
  if (dijit.byId('id')) {
    var objectId=dijit.byId('id').get('value');
  } else {
    return;
  }
  var params="&objectClass=" + objectClass + "&objectId=" + objectId;
  loadDialog('dialogJoblist', null, true, params, true);
}

function saveJoblist() {
  // var params="&objectClass="+objectClass+"&objectId="+objectId;
  // loadDialog('dialogJoblist',null, true, params);
  loadContent('../tool/saveJoblist.php', 'resultDivMain', 'dialogJoblistForm',
      true, 'joblist');
  dijit.byId('dialogJoblist').hide();
  return false;
}

function jobClick(line) {
  jobName="check_" + line;
  if (dijit.byId(jobName).get('checked') && dijit.byId("check_" + line)) {
    dijit.byId("check_" + line).set('checked', false);
  }
}

// ===================================================
// custom
// ===================================================
function changeJobInfo(jobId) {
  toShow=false;
  if(dijit.byId('dialogJobInfoJobId')) {
    dijit.byId('dialogJobInfoJobId').set('value', jobId);
  } else if (dijit.byId('dialogJobInfoJobId')) {
    dojo.byId('dialogJobInfoJobId').value = jobId;
  }

  if (dijit.byId('job_'+jobId+'_idUser')) {
    dijit.byId('dialogJobInfoCreator').set('value',
        dijit.byId('job_'+jobId+'_idUser').get('value'));
    dojo.byId('dialogJobInfoCreatorLine').style.display='inline';
    toShow=true;
  } else if (dojo.byId('job_'+jobId+'_idUser')) {
    dijit.byId('dialogJobInfoCreator').set('value',
        dojo.byId('job_'+jobId+'_idUser').value);
    dojo.byId('dialogJobInfoCreatorLine').style.display='inline';
    toShow=true;
  } else {
    dojo.byId('dialogJobInfoCreatorLine').style.display='none';
  }

  if (dijit.byId('job_'+jobId+'_creationDate')) {
    if(dijit.byId('job_'+jobId+'_creationDate').get('value') != '') {
      dijit.byId('dialogJobInfoDate').set('value', dijit.byId('job_'+jobId+'_creationDate').get('value'));
    }
    dojo.byId('dialogJobInfoDateLine').style.display='inline';
    toShow=true;
  } else if (dojo.byId('job_'+jobId+'_creationDate')) {
    if(dojo.byId('job_'+jobId+'_creationDate').value != '') {
      dojo.byId('dialogJobInfoDate').set('value', dojo.byId('job_'+jobId+'_creationDate').value);
    }
    dojo.byId('dialogJobInfoDateLine').style.display='inline';
    toShow=true;
  } else {
    dojo.byId('dialogJobInfoDateLine').style.display='none';
  }

  if (toShow) {
    dijit.byId('dialogJobInfo').show();
  }
}

function saveJobInfo() {
  if(dijit.byId('dialogJobInfoJobId')) {
    jobId = dijit.byId('dialogJobInfoJobId').get('value');
  } else if (dijit.byId('dialogJobInfoJobId')) {
    jobId = dijit.byId('dialogJobInfoJobId').get('value');
  }
  if(jobId) {
    if (dijit.byId('job_'+jobId+'_idUser')) {
      dijit.byId('job_'+jobId+'_idUser').set('value',
        dijit.byId('dialogJobInfoCreator').get('value'));
    } else if (dojo.byId('job_'+jobId+'_idUser')) {
      dojo.byId('job_'+jobId+'_idUser').value = dijit.byId('dialogJobInfoCreator').get(
          'value');
    }

    if(dijit.byId('dialogJobInfoDate').get('value') != '') {
        if (dijit.byId('job_'+jobId+'_creationDate')) {
            dijit.byId('job_'+jobId+'_creationDate').set('value',
              formatDate(dijit.byId('dialogJobInfoDate').get('value')));
        } else if (dojo.byId('job_'+jobId+'_creationDate')) {
            dojo.byId('job_'+jobId+'_creationDate').value = formatDate(dijit.byId(
              'dialogJobInfoDate').get('value'));
        }
    }
    formChanged();
    // To implement if we want to hide before reload after save
    /*dojo.byId('buttonDivCreationInfo').innerHTML="";*/
    forceRefreshJobInfo=true;
    saveObject();
    dijit.byId('dialogJobInfo').hide();
  }
}

//=============================================================================
//= KpiThreshold
//=============================================================================

/**
* Display a add Kpi Threshold Box
*
*/
function addKpiThreshold(idKpiDefinition) {
  var params="&mode=add&idKpiDefinition=" + idKpiDefinition;
  loadDialog('dialogKpiThreshold', null, true, params);
}

/**
* Display a edit Kpi Threshold Box
*
*/
function editKpiThreshold(idKpiThreshold) {
  var params="&mode=edit&idKpiThreshold=" + idKpiThreshold;
  loadDialog('dialogKpiThreshold', null, true, params);
}

/**
* save a Kpi Threshold (after add or edit)
*
*/
function saveKpiThreshold() {
  if (!dijit.byId("kpiThresholdName").get('value')) {
    showAlert(i18n('messageMandatory', new Array(i18n('colName'))));
    return false;
  }
  if (! dijit.byId("kpiThresholdValue").get('value') && dijit.byId("kpiThresholdValue").get('value')!='0') {
    showAlert(i18n('messageMandatory', new Array(i18n('colValue'))));
    return false;
  }
  loadContent("../tool/saveKpiThreshold.php", "resultDivMain","dialogKpiThresholdForm", true,'kpiThreshold');
  dijit.byId('dialogKpiThreshold').hide();
}

/**
* Display a delete line Box
*
*/
function removeKpiThreshold(idKpiThreshold) {
  var params="?kpiThresholdId=" + idKpiThreshold;
  actionOK=function() {
    loadContent("../tool/removeKpiThreshold.php" + params, "resultDivMain", null, true,'kpiThreshold');
  };
  msg=i18n('confirmDelete', new Array(i18n('KpiThreshold'), idKpiThreshold));
  showConfirm(msg, actionOK);
}

function toggleFullScreen() {
  if ((document.fullScreenElement && document.fullScreenElement !== null) ||    
   (!document.mozFullScreen && !document.webkitIsFullScreen)) {
    enterFullScreen();
  } else { 
    exitFullScreen();
  }
  dijit.byId("iconMenuUserScreen").closeDropDown();
}
function enterFullScreen() {
  if (document.documentElement.requestFullScreen) {
    document.documentElement.requestFullScreen();  
  } else if (document.documentElement.mozRequestFullScreen) {  
    document.documentElement.mozRequestFullScreen();  
  } else if (document.documentElement.webkitRequestFullScreen) {  
    document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);  
  }
}
function exitFullScreen() {
  if (document.cancelFullScreen ) {
    document.cancelFullScreen(); 
  } else if (document.mozCancelFullScreen) {  
    document.mozCancelFullScreen(); 
  } else if (document.webkitCancelFullScreen) {  
    document.webkitCancelFullScreen(); 
  }
}
/**
* Subscription
*
*/
function subscribeToItem(objectClass, objectId, userId) {
    if (! objectId && dojo.byId('id')) objectId=dojo.byId('id').value;
    var url="../tool/saveSubscription.php?mode=on";
    url+="&objectClass="+objectClass;
    url+="&objectId="+objectId;
    url+="&userId="+userId;
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data) {
      var result="KO";
      var itemLabel="";
      var response=JSON.parse(data);
      if (response.hasOwnProperty('result')) result=response.result;
      if (response.hasOwnProperty('itemLabel')) itemLabel=response.itemLabel;
      if (result=='OK') {
        addMessage(i18n('subscriptionSuccess',new Array(itemLabel)));
        dijit.byId('subscribeButton').set('iconClass','dijitButtonIcon dijitButtonIconSubscribeValid');
        enableWidget('subscribeButtonUnsubscribe');
        disableWidget('subscribeButtonSubscribe');
      } else {
        showError(i18n('subscriptionFailed'));
      }
    },
    error : function() {
      showError(i18n('subscriptionFailed'));
    }
  });
}

function unsubscribeFromItem(objectClass, objectId, userId) {
  if (! objectId && dojo.byId('id')) objectId=dojo.byId('id').value;
  var url="../tool/saveSubscription.php?mode=off";
  url+="&objectClass="+objectClass;
  url+="&objectId="+objectId;
  url+="&userId="+userId;
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data) {
      var result="KO";
      var itemLabel="";
      var message="";
      var response=JSON.parse(data);
      if (response.hasOwnProperty('result')) result=response.result;
      if (response.hasOwnProperty('itemLabel')) itemLabel=response.itemLabel;
      if (response.hasOwnProperty('message')) message=response.message;
      if (result=='OK') {
        addMessage(i18n('unsubscriptionSuccess',new Array(itemLabel)));
        dijit.byId('subscribeButton').set('iconClass','dijitButtonIcon dijitButtonIconSubscribe');
        enableWidget('subscribeButtonSubscribe');
        disableWidget('subscribeButtonUnsubscribe');
      } else {
        showError(i18n('subscriptionFailed')+'<br/>'+message);
      }
    },
    error : function() {
      showError(i18n('subscriptionFailed'));
    }
  });
}

function subscribeForOthers(objectClass, objectId) {
  if (! objectId && dojo.byId('id')) objectId=dojo.byId('id').value;
  loadDialog('dialogSubscriptionForOthers',null,true,'&objectClass='+objectClass+'&objectId='+objectId,true);
}
function showSubscribersList(objectClass, objectId) {
  if (! objectId && dojo.byId('id')) objectId=dojo.byId('id').value;
  loadDialog('dialogSubscribersList',null,true,'&objectClass='+objectClass+'&objectId='+objectId,true);
}

function showSubscriptionList(userId) {
  loadDialog('dialogSubscriptionList',null,true,'&userId='+userId,true);
}

function changeSubscriptionFromDialog(mode,dialog,objectClass,objectId,userId,key,currentUserId) {
  if (! objectId && dojo.byId('id')) objectId=dojo.byId('id').value;
  var url="../tool/saveSubscription.php?mode="+mode;
  url+="&objectClass="+objectClass;
  url+="&objectId="+objectId;
  url+="&userId="+userId;
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data) {
      var result="KO";
      var itemLabel="";
      var message="";
      var userName="";
      var userId="";
      var currentUserId="";
      var objectClass="";
      var objectId="";
      var response=JSON.parse(data);
      if (response.hasOwnProperty('result')) result=response.result;
      if (response.hasOwnProperty('itemLabel')) itemLabel=response.itemLabel;
      if (response.hasOwnProperty('userName')) userName=response.userName;
      if (response.hasOwnProperty('userId')) userId=response.userId;
      if (response.hasOwnProperty('currentUserId')) currentUserId=response.currentUserId;
      if (response.hasOwnProperty('objectClass')) objectClass=response.objectClass;
      if (response.hasOwnProperty('objectId')) objectId=response.objectId;
      if (response.hasOwnProperty('message'))  message=response.message;
      if (result=='OK') {
        if (dialog=='list') {
          addMessage(i18n('unsubscriptionSuccess',new Array(itemLabel)));
        } else if (dialog=='other') {
          if (mode=='on') {
            addMessage(i18n('subscriptionSuccess',new Array(userName)));
          } else {
            addMessage(i18n('unsubscriptionSuccess',new Array(userName)));
          }
        }
        if (key) {
          if (mode=='on') {
            dojo.byId('subscribtionButton'+key).style.display="none";
            dojo.byId('unsubscribtionButton'+key).style.display="inline-block";
          } else {
            dojo.byId('unsubscribtionButton'+key).style.display="none";
            dojo.byId('subscribtionButton'+key).style.display="inline-block";
          }
        }
        if (userId && currentUserId && userId==currentUserId && objectClass && objectId) {
          if (dojo.byId('objectClass') && objectClass==dojo.byId('objectClass').value && dojo.byId('objectId') && parseInt(objectId)==parseInt(dojo.byId('objectId').value)) {
            if (mode=='on') {
              if (dijit.byId('subscribeButton')) dijit.byId('subscribeButton').set('iconClass','dijitButtonIcon dijitButtonIconSubscribeValid');
              enableWidget('subscribeButtonUnsubscribe');
              disableWidget('subscribeButtonSubscribe');
            } else {
              if (dijit.byId('subscribeButton')) dijit.byId('subscribeButton').set('iconClass','dijitButtonIcon dijitButtonIconSubscribe');
              enableWidget('subscribeButtonSubscribe');
              disableWidget('subscribeButtonUnsubscribe');
            }
          }
        }
      } else {
        showError(i18n('subscriptionFailed')+'<br/>'+message);
      }
    },
    error : function() {
      showError(i18n('subscriptionFailed'));
    }
  });
}

//ADD qCazelles - GANTT
function displayVersionsPlanning(idProductVersions,objectVersion) {
	vGanttCurrentLine=-1;
	cleanContent("centerDiv");
	loadContent("versionsPlanningMain.php?productVersionsListId=" + idProductVersions+"&objectVersion="+objectVersion, "centerDiv");
	//REMOVE qCazelles - Correction GANTT - Ticket #100
	//dijit.byId('dialogVersionsPlanning').hide();
	//END REMOVE qCazelles - Correction GANTT - Ticket #100
}
//END ADD qCazelles - GANTT


function filterDnDList(search,list) {
  var searchVal=dojo.byId(search).value;
  searchVal=searchVal.replace(/\*/gi,'.*');
  var pattern = new RegExp(searchVal, 'i');
  dojo.map(dojo.byId(list).children, function(child){
    if (searchVal!='' && ! pattern.test(child.getAttribute('value')) ) {
      child.style.display="none";
    } else {
      child.style.display="block";
    }
  });
}

var arrayPaneSize=[];
function storePaneSize(paneName,sizeValue) {
  if (arrayPaneSize[paneName] && arrayPaneSize[paneName]==sizeValue) {
    return;
  }
  arrayPaneSize[paneName]=sizeValue;
  saveDataToSession(paneName, sizeValue, true);
}

//gautier #showHideMenu
function displayMenu(id){
  if(hideUnderMenuId){
    if (hideUnderMenuId == id ){
      clearTimeout(hideUnderMenuTimeout);
      hideUnderMenuId=null;
    }else{
      hideUnderMenu(hideUnderMenuId);
    }
  }
  dojo.byId('UnderMenu'+id).style.zIndex="999999";
  dojo.byId('UnderMenu'+id).style.display="block";
  setTimeout("repositionMenuDiv("+id+","+id+");",10);
}
//Florent 
function displayUnderMenu(id,idParent){
  if (hideUnderMenuId==null && previewhideUnderMenuId!=null){
    hideUnderMenu(previewhideUnderMenuId,0);
  }
  else 
    if(hideUnderMenuId){
      if (hideUnderMenuId == id ){
        dojo.byId('UnderMenu'+id).style.display="none";
        clearTimeout(hideUnderMenuTimeout);
      }else{
        hideUnderMenu(hideUnderMenuId);
      }
    }
    dojo.byId('UnderMenu'+id).style.display="block";
    setTimeout("repositionMenuDiv("+id+","+idParent+");",10);
  //Florent 
    previewhideUnderMenuId=id;
}

function repositionMenuDiv(id,idParent) {
  var parentDiv=dojo.byId('Menu'+idParent);
  var currentDiv=dojo.byId('UnderMenu'+id);
  var top = parentDiv.offsetTop;
  var totalHeight = dojo.byId('centerDiv').offsetHeight;
  currentDiv.style.maxHeight=(totalHeight-50)+'px';
  var height = currentDiv.offsetHeight;
  if(id==152 && top + height > totalHeight - 45){
    newTop = totalHeight - (top + height) - 10 ; 
    currentDiv.style.top = newTop+'px';
  }
  if (top + height > totalHeight - 30){
    newTop = totalHeight - (top + height) - 10 ; 
    currentDiv.style.top = newTop+'px';
  };
}
//end
function hideMenu(id,delay){
  if(! delay){ 
    delay=300;
  }
  if(hideUnderMenuTimeout){
    clearTimeout(hideUnderMenuTimeout);
  }  
  hideUnderMenuId = id;
  hideUnderMenuTimeout=setTimeout("hideUnderMenu("+id+")",delay);
}

function hideUnderMenu(id){
  dojo.query(".hideUndermenu"+id+".dijitAccordionTitle2.reportTableColumnHeader2.largeReportHeader2").forEach(function(node, index, nodelist) {
    node.style.display="none";
   });
  dojo.byId('UnderMenu'+id).style.display="none";
  hideUnderMenuId = null;
}
//Florent 
function hidePreviewUnderMenu(id){
  if(previewhideUnderMenuId!=id && previewhideUnderMenuId!=null){
    hideUnderMenu(previewhideUnderMenuId);
  }
}
//end
function displayListOfApprover(id){
  var params="&objectId=" + id;
  loadDialog('dialogListApprover', null, true,params,null,true);
}


//gautier
function readNotification (id){
  var url='../view/menuNotificationRead.php?id=' + id;
  dojo.xhrPost({
    url : url,
    handleAs : "text",
    load : function(data, args) {
      var objClass = 'objectClass';
      try {
         objClass = dojo.byId('objectClass').value;
      } catch(e) {
        objClass = 'Other';
      }
      if (objClass == 'Notification'){
        loadContent("objectMain.php?objectClass="+objClass,"centerDiv");
      }
      refreshNotificationTree(false);
      loadContent("../view/menuNotificationRead.php", "drawNotificationUnread");
    }
  });
}
//end

// ====================================
// CRON FEATURES
// ====================================

function cronActivation(scope){
  showWait();
  dojo.xhrGet({
    url : "../tool/cronExecutionStandard.php?operation=activate&cronExecutionScope="+scope,
    load : function(data) {
      loadContent("../view/parameter.php?type=globalParameter", "centerDiv");
      adminCronRestart();
    },
    error : function(data) {
      hideWait();
    }
  });
}

function cronExecutionDefinitionSave(){
  var finish=false;
  showWait();
  dojo.xhrPost({
    url : "../tool/cronExecutionStandard.php?operation=saveDefinition",
    form: "cronDefiniton",
    handleAs : "text",
    load : function(data, args) {
      dijit.byId('dialogCronDefinition').hide();
      loadContent("../view/parameter.php?type=globalParameter", "centerDiv");
      hideWait();
    },
    error : function() {
      hideWait();
    }
  });
}

// MTY - GENERIC DAY OFF
function addGenericBankOffDays(idCalendarDefinition) {
    if (checkFormChangeInProgress()) {
      showAlert(i18n('alertOngoingChange'));
      return;
    }

    var params="&idGenericBankOffDays=0";
    params += "&idCalendarDefinition="+idCalendarDefinition;
    params += "&addMode=true&editMode=false";
    
    loadDialog('dialogGenericBankOffDays',null,true,params,true);        
}

function editGenericBankOffDays(idGenericBankOffDays,
                                idCalendarDefinition,
                                name,
                                month,
                                day,
                                easterDay
                                ) {
    if (checkFormChangeInProgress()) {
      showAlert(i18n('alertOngoingChange'));
      return;
    }
    var params = "&idGenericBankOffDays="+idGenericBankOffDays;
    params+="&idCalendarDefinition="+idCalendarDefinition;
    params+="&name="+name;
    params+="&month="+month;
    params+="&day="+day;
    params+="&easterDay="+easterDay;
    params +="&addMode=false&editMode=true";
    loadDialog('dialogGenericBankOffDays',null,true,params,true);    
}

function saveGenericBankOffDays() {
  var formVar=dijit.byId('genericBankOffDaysForm');
  if (formVar.validate()) {
    loadContent("../tool/saveGenericBankOffDays.php", "resultDivMain", "genericBankOffDaysForm", true, 'calendarBankOffDays');
    dijit.byId('dialogGenericBankOffDays').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function removeGenericBankOffDays(id, name) {
    if (checkFormChangeInProgress()) {
        showAlert(i18n('alertOngoingChange'));
        return;
    }
    actionOK=function() {
        loadContent("../tool/removeGenericBankOffDay.php?idBankOffDay="+id, "resultDivMain", null, true, 'calendarBankOffDays');
    };
    msg=i18n('confirmDeleteGenericBankOffDay', new Array(name));
    showConfirm(msg, actionOK);
}
// MTY - GENERIC DAY OFF

function showDialogAutoSendReport(){
	setTimeout(loadDialog('dialogAutoSendReport',null,true,null,true), 200);
}
function saveAutoSendReport(){
	var formVar=dijit.byId('autoSendReportForm');
	  if (dijit.byId('destinationInput').get('value') == '' && dijit.byId('otherDestinationInput').get('value') == '') {
	      showAlert(i18n("errorNoReceivers"));
	      return;
	  }
	  if (formVar.validate()) {
		  loadContent("../tool/saveAutoSendReport.php", "resultDivMain", "autoSendReportForm", true, "report");
		  dijit.byId('dialogAutoSendReport').hide();
	  } else {
	    showAlert(i18n("alertInvalidForm"));
	  }
}

function refreshRadioButtonDiv(){
	loadContent("../tool/refreshButtonAutoSendReport.php", "radioButtonDiv", "autoSendReportForm");
}
		  
function saveModuleStatus(id,status) {
  if (id==12 && (status==false || status=='false')) {
      actionOK = function () {
        adminDisconnectAll(false);
        saveModuleStatusContinue(id,status);
      };
      actionKO = function () {
          dijit.byId("module_12").set("checked",true);
      };
      msg=i18n("thisActionWillDeleteAllsLeavesSystemElements")+"<br/><br/>"+i18n("AreYouSure")+" ?";
      showQuestion(msg, actionOK, actionKO);
  } else {
    saveModuleStatusContinue(id,status);
  }
}   
function saveModuleStatusContinue(id,status) {
  var url='../tool/saveModuleStatus.php?idModule='+id+'&status='+status;
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(){
    }
  });
  dojo.query(".moduleClass.parentModule"+id).forEach(function(domNode){
    var name=domNode.getAttribute('widgetid');
    var widget=dijit.byId(name);
    if (widget) {
      widget.set('checked',(status==true)?true:false);
      var idSub=name.replace('module_','');
      var url='../tool/saveModuleStatus.php?idModule='+idSub+'&status='+status;
      dojo.xhrGet({
        url : url,
        handleAs : "text",
        load : function(){
        }
      });
    }
  });
  saveModuleStatusCheckParent(id);  
}
function saveModuleStatusCheckParent(id) {
  var wdgt=dijit.byId('module_'+id);
  var parent=wdgt.get('parent');
  if (parent && dojo.byId('module_'+parent)) {
    var oneOn=false;
    var allOff=true;
    dojo.query(".moduleClass.parentModule"+parent).forEach(function(domNode){
      var name=domNode.getAttribute('widgetid');
      var widget=dijit.byId(name);
      if (widget) {
        if (widget.get('checked')==true) {
          allOff=false;
          oneOn=true;
        }
      }
    });
    var status=(oneOn==true)?true:false;
    var widget=dijit.byId('module_'+parent);
    if (widget.get('checked')!=status) {
      widget.set('checked',status);
      var url='../tool/saveModuleStatus.php?idModule='+parent+'&status='+status;
      dojo.xhrGet({
        url : url,
        handleAs : "text",
        load : function(){         
        }
      });
    }
  }
}

function addDataCloning(){
	loadDialog('dialogAddDataCloning',null,true,null,true);
}

function copyDataCloning(idDataCloning){
	var param = '&idDataCloningParent='+idDataCloning;
	loadDialog('dialogAddDataCloning',null,true,param,true);
}

function controlChar (){
  var requiredLength=dojo.byId('paramPwdLth').value;
  var gen= new RegExp(["^(?=.*[a-zA-Z0-9!@#$&()-`.+,/\"])"]);
  var min =new RegExp([ "^(?=.*[a-z])"]);
  var maj =new RegExp([ "^(?=.*[A-Z])"]);
  var num=new RegExp(["^(?=.*[0-9])"]);
  var char=new RegExp("(?=.*[!@#\$%\^&\*\\/\~])");
  var progress=dojo.byId('progress');
  var value=0;
  var curpwd=dojo.byId('dojox_form__NewPWBox_0').value;
  addVal=[0,0,0,0];
  if (curpwd.length>=requiredLength) {
    addVal[0]=1;
    value+=1; 
  }
  if( min.test(curpwd) && maj.test(curpwd) ){
    addVal[1]=1;
    value+=1;    
  }
  if(num.test(curpwd)){
    addVal[2]=1;
    if (min.test(curpwd) || maj.test(curpwd) ) value+=1;
  } 
  if(char.test(curpwd)){
    addVal[3]=1;
    if (min.test(curpwd) || maj.test(curpwd) ) value+=1;
  }
  progress.value=value;
  var strength=dojo.byId('parmPwdSth').value;
  var enough=false;
  var msg=i18n('pwdRequiredStrength');
  if(strength==1){
    if (addVal[0]==1) enough=true;
  }else if(strength==2){
    if (addVal[0]==1 && addVal[1]==1) enough=true;
  }else if(strength==3){
    if (addVal[0]==1 && addVal[1]==1 && addVal[2]==1) enough=true; 
  }else if(strength==4){
    if (addVal[0]==1 && addVal[1]==1 && addVal[2]==1 && addVal[3]==1) enough=true;
  }
  if (addVal[0]==0 && strength>=1) msg+='<br/>&nbsp;-&nbsp;'+i18n("pwdErrorLength",[requiredLength]);
  if (addVal[1]==0 && strength>=2) msg+='<br/>&nbsp;-&nbsp;'+i18n("pwdErrorCase");
  if (addVal[2]==0 && strength>=3) msg+='<br/>&nbsp;-&nbsp;'+i18n("pwdErrorDijit");
  if (addVal[3]==0 && strength>=4) msg+='<br/>&nbsp;-&nbsp;'+i18n("pwdErrorChar");
  var strengthMsg=document.getElementById('strength');
  dojo.byId('passwordValidate').value=(enough)?'true':'false';
  dojo.byId('criteria').value=msg;
  require(["dijit/Tooltip", "dojo/domReady!"], function(Tooltip) {
    var node = dojo.byId('dojox_form__NewPWBox_0');
    if (enough) {
      Tooltip.hide(node);
      strengthMsg.innerHTML=i18n('pwdValidStrength');
      strengthMsg.style="color:green;";
    } else {      
      Tooltip.show(msg, node);
      strengthMsg.innerHTML=i18n('pwdInvalidStrength');
      strengthMsg.style="color:red;";
    }
  });
}

function refreshDataCloningCountDiv(userSelected){
	loadContent("../tool/refreshDataCloningCountDiv.php?userSelected="+userSelected, "labelDataCloningCountDiv", "addDataCloningForm");
}

function selectAllCheckBox(val){
  dojo.query(val).forEach(function(node, index, nodelist) {
      if(dijit.byId('dialogMailAll').get('checked')!=true){
        dijit.byId(node.getAttribute('widgetid')).set('checked', false);
      }else{
        dijit.byId(node.getAttribute('widgetid')).set('checked', true);
      }
    });
}

function refreshCronIconStatus(status){
  if (dojo.byId('actualCronStatusInDiv') && dojo.byId('actualCronStatusInDiv').value.toLowerCase()==status.toLowerCase()) return;
	var url='../view/refreshCronIconStatus.php';
	    url+='?cronStatus=' + status;
    loadDiv(url, 'menuBarCronStatus', null, null);
}

function checkCronStatus(status){
	dojo.byId('cronStatus').style.filter = 'grayscale(100%)';
	if (status=='Stopped') {
  	adminLaunchScript("cronRun", false);
  	refreshCronIconStatus("running");
	} else {
  	adminLaunchScript("cronStop", false);
  	dojo.byId('cronStatusButton').title = i18n('cronStopping');
  	//refreshCronIconStatus("stopped");
	}
}

function saveContact(idFldVal,comboClass){
  var addVal=dojo.byId('objectId').value;
  var obj=dojo.byId('objectClass').value;
  var parm="operation=add&objectClass="+comboClass+"&listId="+idFldVal+"&class="+obj+"&addVal="+addVal;
  loadContent("../tool/saveContact.php?"+parm, "resultDivMain",null,true,"contact"+dojo.byId('objectClass'));
}

function removeContact(idFldVal){
  var obj=dojo.byId('objectClass').value;
  var parm="operation=remove&objectClass=Contact&objectId="+idFldVal+"&class="+obj;
  actionOK=function() {
    loadContent("../tool/removeContact.php?"+parm, "resultDivMain",null,true,"contact"+dojo.byId('objectClass'));
  };
  msg=i18n('confirmDissociate', new Array(i18n('Contact'),idFldVal));
  showConfirm(msg, actionOK);
}
//End

function showDisplayModule(id,total){
  if(dojo.byId("displayModule"+id).style.display=="block"){
      dojo.byId("moduleTitle_"+id).style.width=260+'px';
      dojo.byId("displayModule"+id).style.display="none";
  }else{
    for (var i=1; i <= total; i++) {
      if(dojo.byId("moduleTitle_"+i)){
        if(dojo.byId("moduleTitle_"+i).style.width==290+'px'){
          dojo.byId("moduleTitle_"+i).style.width=260+'px';
        }
      }
      if(dojo.byId("displayModule"+i)){
        if(dojo.byId("displayModule"+i).style.display=="block"){
          dojo.byId("displayModule"+i).style.display="none";
          dojo.byId("displayModule"+i).style.visibility = 'hidden';
          dojo.byId("displayModule"+i).style.opacity = 0;
        }
      }
    }
    if(dojo.byId("displayModule"+id)){
      dojo.byId("displayModule"+id).style.display="block";
      dojo.byId("displayModule"+id).style.visibility="visible";
      dojo.byId("displayModule"+id).style.opacity=1;
    }
    dojo.byId("moduleTitle_"+id).style.width=290+'px';
  }
}

function filterMenuModule(id,nbTotal){
  var reset = 0;
  for (var i=1; i <= 7; i++) {
    if(dojo.hasClass(dojo.byId("menuFilterModuleTop"+i),'menuBarItemSelectedModule')){
      if(id==i){
        reset = 1;
      }
      dojo.removeClass(dojo.byId("menuFilterModuleTop"+i),"menuBarItemSelectedModule");
      dojo.removeClass(dojo.byId("menuFilterModuleTopIcon"+i),"menuFilterModuleTopIcon");
    }
  }
  if(reset==0){
    dojo.addClass(dojo.byId("menuFilterModuleTop"+id),"menuBarItemSelectedModule");
    dojo.addClass(dojo.byId("menuFilterModuleTopIcon"+id),"menuFilterModuleTopIcon");
  }else{
    id=1;
    dojo.addClass(dojo.byId("menuFilterModuleTop"+id),"menuBarItemSelectedModule");
    dojo.addClass(dojo.byId("menuFilterModuleTopIcon"+id),"menuFilterModuleTopIcon");
  }
  
  if(id==2){
    var tab = [1,9,16];
  }else if(id==3){
    var tab = [2,16];
  }else if(id==4){
    var tab = [4,8,9,10];
  }else if(id==5){
    var tab = [5,6,7,19,20];
  }else if(id==6){
    var tab = [17,13,15];
  }
  for (var i=1; i <= nbTotal; i++) {
    if(id==1){
      if(dojo.byId("moduleMenuDiv_"+i)){
        dojo.byId("moduleMenuDiv_"+i).style.display="block";
      }
    }else{
      if(dojo.byId("moduleMenuDiv_"+i)){
        if(tab.indexOf(i) !== -1){
          dojo.byId("moduleMenuDiv_"+i).style.display="block";
        }else{
          dojo.byId("moduleMenuDiv_"+i).style.display="none";
          if(dojo.byId("displayModule"+i).style.display=="block"){
            dojo.byId("displayModule"+i).style.display="none";
            dojo.byId("moduleTitle_"+i).style.width=260+'px';
          }
        }
      }
    }
  }
}

function filterMenuModuleDisable(nbTotal){
  for (var i=1; i <= 6; i++) {
    if(dojo.hasClass(dojo.byId("menuFilterModuleTop"+i),'menuBarItemSelectedModule')){
      dojo.removeClass(dojo.byId("menuFilterModuleTop"+i),"menuBarItemSelectedModule");
      dojo.removeClass(dojo.byId("menuFilterModuleTopIcon"+i),"menuFilterModuleTopIcon");
    }
  }
  var reset = 0;
  if(dojo.hasClass(dojo.byId("menuFilterModuleTop7"),'menuBarItemSelectedModule')){
    reset = 1;
  }
  if(reset==0){
    dojo.addClass(dojo.byId("menuFilterModuleTop7"),"menuBarItemSelectedModule");
    dojo.addClass(dojo.byId("menuFilterModuleTopIcon7"),"menuFilterModuleTopIcon");
    for (var i=1; i <= nbTotal; i++) {
      if(dojo.byId("moduleTitle_"+i)){
        if(dojo.hasClass(dojo.byId("moduleMenuDiv_"+i),'activeModuleMenu')){
          dojo.byId("moduleMenuDiv_"+i).style.display="none";
          if(dojo.byId("displayModule"+i).style.display=="block"){
            dojo.byId("displayModule"+i).style.display="none";
            dojo.byId("moduleTitle_"+i).style.width=260+'px';
          }
        }else{
          dojo.byId("moduleMenuDiv_"+i).style.display="block";
        }
      }
    }
  }else{
    filterMenuModule(1,nbTotal);
  }

}
