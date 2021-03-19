/*******************************************************************************
 * COPYRIGHT NOTICE *
 * 
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
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
// All specific ProjeQtOr functions and variables
// This file is included in the main.php page, to be reachable in every context
// ============================================================================
// =============================================================================
// = Variables (global)
// =============================================================================
var i18nMessages = null; // array containing i18n messages
var i18nMessagesCustom = null; // array containing i18n messages
var currentLocale = null; // the locale, from browser or user set
var browserLocale = null; // the locale, from browser
var cancelRecursiveChange_OnGoingChange = false; // boolean to avoid
// recursive change trigger
var formChangeInProgress = false; // boolean to avoid exit from form when
// changes are not saved
var currentRow = null; // the row num of the current selected
// element in the main grid
var currentFieldId = ''; // Id of the ciurrent form field (got
// via onFocus)
var currentFieldValue = ''; // Value of the current form field (got
// via onFocus)
var g; // Gant chart for JsGantt : must be
// named "g"
var quitConfirmed = false;
var noDisconnect = false;
var forceRefreshMenu = false;
var directAccessIndex = null;

var debugPerf = new Array();

var pluginMenuPage = new Array();

var previousSelectedProject=null;
var previousSelectedProjectName=null;

var mustApplyFilter=false;

var arraySelectedProject = new Array();

var displayFilterVersionPlanning='0';
var displayFilterComponentVersionPlanning='0';

var contentPaneResizingInProgress={};

function saveContentPaneResizing(pane, size, saveAsUserParameter) {
  if (donotSaveResize) return;
  if (contentPaneResizingInProgress[pane]) clearTimeout(contentPaneResizingInProgress[pane]);
  contentPaneResizingInProgress[pane]=setTimeout('saveDataToSession("'+pane+'","'+size+'",'+((saveAsUserParameter)?'true':'false')+');contentPaneResizingInProgress["'+pane+'"]=null;',100);
  //saveDataToSession(pane,size,saveAsUserParameter);
}
// =============================================================================
// = Functions
// =============================================================================

/**
 * ============================================================================
 * Refresh the ItemFileReadStore storing Data for the main grid
 * 
 * @param className
 *          the class of objects in the list
 * @param idle
 *          the idle filter parameter
 * @return void
 */

// MTY - FACILITY FUNCTIONS
/**
 * Transforms an object Date to a string compatible with sql Date
 * @param {Date} date
 * @returns {String}
 */
function transformDateToSqlDate(date) {
    var sqlDate="";
    if (isDate(date)) {
        month = date.getMonth()+1;
        year = date.getFullYear();
        day = date.getDate();
        sqlDate = year+'-'+(month<10?'0':'')+month+'-'+(day<10?'0':'')+day;
    }
    return sqlDate;
}

/** ============================================================================
 * Get the SqlElement operation result status
 * @param {String} theResult
 *              The result of an SqlElement operation
 * @return {String}
 */
function getSqlElementOperationStatus(theResult) {
    if (typeof(theResult)!=="string" && typeof(theResult)!=="String") {
        return "TYPEOF RESULT = "+typeof(theResult);        
    }
    // Retrieve type of message
    var indexResult = theResult.indexOf('id="lastOperationStatus" value="');
    if (indexResult===-1) {
        return "NOT RESULT OF SQLELEMENT OPERATION";
    }
    var result = theResult.substr(indexResult+32);
    indexResult = result.indexOf('"');
    var status = new String();
    status = result.substr(0,indexResult);
    return status;    
}

function isSqlElementOperationStatus(result) {
    res = getSqlElementOperationStatus(result);
    if (res.indexOf('TYPEOF RESULT = ')>=0 || res.indexOf('NOT RESULT OF SQLELEMENT OPERATION')>=0) {
        return false;        
    }
    return true;
}

/** ============================================================================
 * Return a message formated as a resultDiv result
 * @param {string} messageType : ERROR or WARNING - If passed other value then = null and no header message
 * @param {string} message : The message content. Default = 'An unknown error occurs' 
 * @param {boolean} toTranslate : True, if the content must be translated. In this case, $message must have a translation in tool/i18n
 * @param {integer} idValue : The id's value of the object on which the result occurs
 * @param {string} lastOperationValue : The last operation introduising this result
 * @param {string} lastOperationStatus : The status of the last operation introduising this result
 * @return {string} formated html message, with corresponding html input
 */
function setLikeResultDivMessage(messageType, 
                                 message,
                                 toTranslate,
                                 idValue, 
                                 lastOperationValue, 
                                 lastOperationStatus) {
  if (!message)  message="AnUnknownErrorOccurs";
  if (!lastOperationValue) lastOperationValue="ERROR";
  if (!lastOperationStatus) lastOperationStatus="ERROR";
  returnValue="";
    if (messageType!="ERROR" && messageType!="WARNING") {messageType = null;}
    if (message=="AnUnknownErrorOccurs") { 
        message = i18n(message);
    } else { 
        message = (toTranslate?i18n(message):message);
    }
    if (messageType!=null) {
        returnValue = '<div class="message'+messageType+'" >'+message+'</div>';
    } else {
        returnValue = message;
    }
    returnValue += '<input type="hidden" id="lastSaveId" value="'+idValue+'" />';
    returnValue += '<input type="hidden" id="lastOperation" value="'+lastOperationValue+'" />';
    returnValue += '<input type="hidden" id="lastOperationStatus" value="'+lastOperationStatus+'" />';
  return returnValue;
}
// MTY - FACILITY FUNCTION

// Function to call console log without messing with debug
function consoleTraceLog(message) {
  // console.log to keep
  console.log(message);
}
function refreshJsonList(className, keepUrl) {
  var grid = dijit.byId("objectGrid");
  if (grid) {
    showWait();
    var sortIndex = grid.getSortIndex();
    var sortAsc = grid.getSortAsc();
    var scrollTop = grid.scrollTop;
    // store = grid.store;
    // store.close();
    unselectAllRows("objectGrid");
    url = "../tool/jsonQuery.php?objectClass=" + className;
    if (dojo.byId('comboDetail')) {
      url = url + "&comboDetail=true";
      if (dojo.byId('comboDetailId')) {
        dojo.byId('comboDetailId').value = '';
      }
    }
    if (dijit.byId('showAllProjects')) {
      if (dijit.byId('showAllProjects').get("value") != '') {
        url = url + "&showAllProjects=true";
      }
    }
    if (dijit.byId('listShowIdle')) {
      saveDataToSession('listShowIdle'+className, dijit.byId('listShowIdle').get("value"), false);
      if (dijit.byId('listShowIdle').get("value") != '') {
        url = url + "&idle=true";
      }
    }
    if (dijit.byId('listTypeFilter')) {
      saveDataToSession('listTypeFilter'+className, dijit.byId('listTypeFilter').get("value"), false);
      if (dijit.byId('listTypeFilter').get("value") != '') {
        url = url + "&objectType=" + dijit.byId('listTypeFilter').get("value");
      }
    }
    
    if (dijit.byId('listClientFilter')) {
      saveDataToSession('listClientFilter'+className, dijit.byId('listClientFilter').get("value"), false);
      if (dijit.byId('listClientFilter').get("value") != '') {
        url = url + "&objectClient="
            + dijit.byId('listClientFilter').get("value");
      }
    }
    if (dijit.byId('listBudgetParentFilter')) {
      saveDataToSession('listBudgetParentFilter', dijit.byId('listBudgetParentFilter').get("value"), false);
      if (dijit.byId('listBudgetParentFilter').get("value") != '') {
        url = url + "&budgetParent="
            + dijit.byId('listBudgetParentFilter').get("value");
      }
    }
    if (dijit.byId('listElementableFilter')) {
      saveDataToSession('listElementableFilter'+className, dijit.byId('listElementableFilter').get("value"), false);
      if (dijit.byId('listElementableFilter').get("value") != '') {
        url = url + "&objectElementable="
            + dijit.byId('listElementableFilter').get("value");
      }
    }
    //ADD qCazelles - Filter by status
    if (dojo.byId('countStatus')) {
      var filteringByStatus = false;
      for (var i = 1; i <= dojo.byId('countStatus').value; i++) {
        saveDataToSession('showStatus'+dijit.byId('showStatus' + i).value+className, dijit.byId('showStatus'+i).checked, false);
        if (dijit.byId('showStatus' + i).checked) {
          url = url + "&objectStatus" + i + "=" + dijit.byId('showStatus' + i).value;
          filteringByStatus = true;
        }
      }
      if (filteringByStatus) {
        url = url + "&countStatus=" + dojo.byId('countStatus').value;
      }
    }
    
    //END ADD qCazelles - Filter by status
    if (dijit.byId('quickSearchValue')) {
      if (dijit.byId('quickSearchValue').get("value") != '') {
        // url = url + "&quickSearch=" +
        // dijit.byId('quickSearchValue').get("value");
        url = url + "&quickSearch="
            + encodeURIComponent(dijit.byId('quickSearchValue').get("value"));
      }
    }
    if (dijit.byId('quickSearchValueQuick')) {
      if (dijit.byId('quickSearchValueQuick').get("value") != '') {
        url = url + "&quickSearchQuick="
            + encodeURIComponent(dijit.byId('quickSearchValueQuick').get("value"));
      }
    }
    // store.fetch();
    if (!keepUrl) {
      grid.setStore(new dojo.data.ItemFileReadStore({
        url : url,
        clearOnClose : 'true'
      }));
    }
    store = grid.store;
    store.close();
    store.fetch({
          onComplete : function() {
            grid._refresh();
            hideBigImage(); // Will avoid resident pop-up always displayed
            var objectId = dojo.byId('objectId');
            setTimeout('dijit.byId("objectGrid").setSortIndex(' + sortIndex
                + ',' + sortAsc + ');', 10);
            setTimeout('dijit.byId("objectGrid").scrollTo(' + scrollTop + ');',
                20);
            setTimeout('selectRowById("objectGrid", '
                + parseInt(objectId.value) + ');', 30);
            setTimeout('hideWait();', 40);
            filterJsonList(className);
          }
        });
  }
}

/**
 * ============================================================================
 * Refresh the ItemFileReadStore storing Data for the planning (gantt)
 * 
 * @return void
 */
function refreshJsonPlanning(versionsPlanning) {
  param = false;
  
  if (dojo.byId("resourcePlanning")|| versionsPlanning=='resource' ) {
    url = "../tool/jsonResourcePlanning.php";
  } else if (dojo.byId("versionsPlanning")|| versionsPlanning=='version') {
    url = "../tool/jsonVersionsPlanning.php";
  } else if (dojo.byId("globalPlanning")) {
    url = "../tool/jsonPlanning.php?global=true";
    param=true;
  } else if (dojo.byId("contractGantt")) {
    url = "../tool/jsonContractGantt.php";
  } else {
    url = "../tool/jsonPlanning.php";
  }
  
  //ADD qCazelles - GANTT
  if (dojo.byId('nbPvs')) {
    url += (param) ? "&" : "?";
    for (var i = 0; i < dojo.byId('nbPvs').value; i++) {
      if (i != 0) {
        url += "&";
      }
      url += "pvNo" + i + "=" + dojo.byId('pvNo' + i).value;
    }
    if (dojo.byId('nbPvs').value != 0) {
      param = true;
    }
  }
  //END ADD qCazelles - GANTT
  
  if (dojo.byId('listShowIdle')) {
    if (dojo.byId('listShowIdle').checked) {
      url += (param) ? "&" : "?";
      url += "idle=true";
      param = true;
    }
  }
  if (dojo.byId('showWBS')) {
    if (dojo.byId('showWBS').checked) {
      url += (param) ? "&" : "?";
      url += "showWBS=true";
      param = true;
    }
  }
  if (dojo.byId('listShowResource')) {
    if (dojo.byId('listShowResource').checked ) {
      url += (param) ? "&" : "?";
      url += "showResource=true";
      param = true;
    }
  }
  if (dojo.byId('listShowLeftWork')) {
    if (dojo.byId('listShowLeftWork').checked) {
      url += (param) ? "&" : "?";
      url += "showWork=true";
      param = true;
    }
  }
  if (dojo.byId('listShowProject')) {
    if (dojo.byId('listShowProject').checked) {
      url += (param) ? "&" : "?";
      url += "showProject=true";
      param = true;
    }
  }
  if (dijit.byId('listShowMilestone')) {
    url += (param) ? "&" : "?";
    url += "showMilestone=" + dijit.byId('listShowMilestone').get("value");
    param = true;
  }
  if (dijit.byId('listShowNullAssignment')) {
    if (dojo.byId('listShowNullAssignment').checked) {
      url += (param) ? "&" : "?";
      url += "listShowNullAssignment=true";
      param = true;
    }
  } 
  if(dijit.byId('projectDate') && dijit.byId('projectDate').get('checked')){
	  dijit.byId('listSaveDates').set('checked', false);
	  dojo.setAttr('startDatePlanView', 'value', null);
	  dojo.setAttr('endDatePlanView', 'value', null);
  }
  loadContent(url, "planningJsonData", 'listForm', false);
}

/**
 * ============================================================================
 * Filter the Data of the main grid on Id and/or Name
 * 
 * @return void
 */
//gautier
String.prototype.toUpperCaseWithoutAccent = function(){
  var accent = [
      /[\300-\306]/g, /[\340-\346]/g, // A, a
      /[\310-\313]/g, /[\350-\353]/g, // E, e
      /[\314-\317]/g, /[\354-\357]/g, // I, i
      /[\322-\330]/g, /[\362-\370]/g, // O, o
      /[\331-\334]/g, /[\371-\374]/g, // U, u
      /[\321]/g, /[\361]/g, // N, n
      /[\307]/g, /[\347]/g, // C, c
  ];
  var noaccent = ['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];
   
  var str = this;
  for(var i = 0; i < accent.length; i++){
      str = str.replace(accent[i], noaccent[i]);
  }
   
  return str.toUpperCase();
}

     /*
        Ticket #3988  - Object list : boutton reset parameters  
         florent
     */
function resetFilter(lstStat){
 var grid = dijit.byId("objectGrid");
 var notDef;
 var i=0;
 for(i=1;i<=lstStat;i++){
   if(dijit.byId('showStatus'+i)){
     dijit.byId('showStatus'+i).set('checked',false);
   }
 }
 
 if (dijit.byId("listFilterFilter").iconClass == "dijitButtonIcon iconActiveFilter"){
   selectStoredFilter('0','directFilterList',notDef,notDef);
  }    
    if(grid){
    if(dijit.byId('listTypeFilter')){
      dijit.byId('listTypeFilter').set('value','');
    }
    if(dijit.byId('listClientFilter')){
      dijit.byId('listClientFilter').set('value','');
    }
    if(dijit.byId('listItemSelector')){
      dijit.byId('listItemSelector').set('value','');
    }
    if(dijit.byId('showAllProjects')){
      dijit.byId('showAllProjects').set('value','');
    }
    if(dijit.byId('ListPredefinedActions')){
      dijit.byId('ListPredefinedActions').set('value','');
    }
    if(dijit.byId('ListBudgetParentFilter')){
      dijit.byId('ListBudgetParentFilter').set('value','');
    }
    if(dijit.byId('ListBudgetParentFilter')){
      dijit.byId('ListBudgetParentFilter').set('value','');
    }
    if(dijit.byId('ListShowIdle')){
      dijit.byId('ListShowIdle').set('value','');
    }
    if(dijit.byId('hideInService')){
      dijit.byId('hideInService').set('value','');
    }
    if(dijit.byId('listIdFilter') || dijit.byId('listNameFilter') || dijit.byId('listNameFilter') && dijit.byId('listIdFilter') ) {
      dijit.byId('listIdFilter').set('value','');
      dijit.byId('listNameFilter').set('value','');
      filter={};
      grid.query=filter;
      grid._refresh();
    }
  }
 
}

function resetFilterQuick(lstStat){
  var grid = dijit.byId("objectGrid");
  var notDef;
  var i=0;
  for(i=1;i<=lstStat;i++){
    if(dijit.byId('showStatus'+i)){
      dijit.byId('showStatus'+i).set('checked',false);
    }
  }
  
  if (dijit.byId("listFilterFilter").iconClass == "dijitButtonIcon iconActiveFilter"){
    selectStoredFilter('0','directFilterList',notDef,notDef);
   }  
     if(grid){
     if(dijit.byId('listTypeFilter')){
       dijit.byId('listTypeFilter').set('value','');
     }
     if(dijit.byId('listClientFilter')){
       dijit.byId('listClientFilter').set('value','');
     }
     if(dijit.byId('listItemSelector')){
       dijit.byId('listItemSelector').set('value','');
     }
     if(dijit.byId('showAllProjects')){
       dijit.byId('showAllProjects').set('value','');
     }
     if(dijit.byId('ListPredefinedActions')){
       dijit.byId('ListPredefinedActions').set('value','');
     }
     if(dijit.byId('ListBudgetParentFilter')){
       dijit.byId('ListBudgetParentFilter').set('value','');
     }
     if(dijit.byId('ListBudgetParentFilter')){
       dijit.byId('ListBudgetParentFilter').set('value','');
     }
     if(dijit.byId('ListShowIdle')){
       dijit.byId('ListShowIdle').set('value','');
     }
     if(dijit.byId('hideInService')){
       dijit.byId('hideInService').set('value','');
     }
     if (dijit.byId('listIdFilter')) {
       dijit.byId('listIdFilter').set('value','');
     }
     if (dijit.byId('listNameFilter')) {
       dijit.byId('listNameFilter').set('value','');
     }
     if(dijit.byId('listIdFilter') || dijit.byId('listNameFilter') ) { 
       filter={};
       grid.query=filter;
       grid._refresh();
     }
   }
     
     if(dijit.byId('listIdFilterQuick')){
       dijit.byId('listIdFilterQuick').set('value','');
       if(dijit.byId('listIdFilterQuickSw').get('value')=='off'){
         dojo.byId('filterDivsSpan').style.display="none";
         dijit.byId('listIdFilter').domNode.style.display = 'none';
       }
     }
     if(dijit.byId('listNameFilterQuick')){
       dijit.byId('listNameFilterQuick').set('value','');
       if(dijit.byId('listNameFilterQuickSw').get('value')=='off'){
         dojo.byId('listNameFilterSpan').style.display="none";
         dijit.byId('listNameFilter').domNode.style.display = 'none';
       }
     }
     if(dijit.byId('listTypeFilterQuick')){
       dijit.byId('listTypeFilterQuick').set('value','');
     }
     if(dijit.byId('listClientFilterQuick')){
       dijit.byId('listClientFilterQuick').set('value','');
     }
     if(dijit.byId('listBudgetParentFilterQuick')){
       dijit.byId('listBudgetParentFilterQuick').set('value','');
     }
     
     if(dijit.byId('quickSearchValueQuick')){
       dijit.byId('quickSearchValueQuick').set('value','');
     }
}



function filterJsonList(myObjectClass) {
  var filterId = dojo.byId('listIdFilter');
  var filterName = dojo.byId('listNameFilter');
  var grid = dijit.byId("objectGrid");
  if (grid && (filterId || filterName)) {
    filter = {};
    unselectAllRows("objectGrid");
    filter.id = '*'; // delfault
    if (filterId) {
      saveDataToSession('listIdFilter'+myObjectClass, dojo.byId('listIdFilter').value, false);
      if (filterId.value && filterId.value != '') {
        filter.id = '*' + filterId.value + '*';
      }
    }
    if (filterName) {
      saveDataToSession('listNameFilter'+myObjectClass, dojo.byId('listNameFilter').value, false);
      if (filterName.value && filterName.value != '') {
        filter.name = '*' + filterName.value.toUpperCaseWithoutAccent() + '*';
      }
    }
    grid.query = filter;
    grid._refresh();
  }
  refreshGridCount();
  selectGridRow();
}

function refreshGrid(noReplan) {
  if (dijit.byId("objectGrid")) { // Grid exists : refresh it
    showWait();
    if (dojo.byId('objectClassList')) refreshJsonList(dojo.byId('objectClassList').value, true);
    else refreshJsonList(dojo.byId('objectClass').value, true);
  } else { // If Grid does not exist, we are displaying Planning : refresh it
    showWait();
    if (dojo.byId('automaticRunPlan') && dojo.byId('automaticRunPlan').checked && ! noReplan ) {
      plan();
    } else {
      refreshJsonPlanning();
    }
  }
}
/**
 * Refresh de display of number of items in the grid
 * 
 * @param repeat
 *          internal use only
 */
avoidRecursiveRefresh = false;
function refreshGridCount(repeat) {
  var grid = dijit.byId("objectGrid");
  if (grid.rowCount == 0 && !repeat) {
    // dojo.byId('gridRowCount').innerHTML="?";
    setTimeout("refreshGridCount(1);", 100);
  } else {
    if(dojo.byId('gridRowCount')){
      dojo.byId('gridRowCount').innerHTML = grid.rowCount;
    }
    if(dojo.byId('gridRowCountShadow1')){
      dojo.byId('gridRowCountShadow1').innerHTML = grid.rowCount;
    }
    if(dojo.byId('gridRowCountShadow2')){
      dojo.byId('gridRowCountShadow2').innerHTML = grid.rowCount;
    }
  }
  if (isNewGui && dojo.byId("classNameSpan") && dojo.byId("objectClass")) {
    var classText=i18n("menu"+(dojo.byId("objectClass").value));
    if (parseInt(grid.rowCount)<=1) {
      classText=i18n(dojo.byId("objectClass").value);
    }
    dojo.byId("classNameSpan").innerHTML=classText;
    if (dojo.byId("classNameSpanQuickSearch")) dojo.byId("classNameSpanQuickSearch").innerHTML=classText;
  }
}

/**
 * ============================================================================
 * Return the current time, correctly formated as HH:MM
 * 
 * @return the current time correctly formated
 */
function getTime() {
  var currentTime = new Date();
  var hours = currentTime.getHours();
  var minutes = currentTime.getMinutes();
  if (minutes < 10) {
    minutes = "0" + minutes;
  }
  return hours + ":" + minutes;
}

/**
 * ============================================================================
 * Add a new message in the message Div, on top of messages (last being on top)
 * 
 * @param msg
 *          the message to add
 * @return void
 */
function addMessage(msg) {
  msg = msg.replace(" class='messageERROR' ", "");
  msg = msg.replace(" class='messageOK' ", "");
  msg = msg.replace(" class='messageWARNING' ", "");
  msg = msg.replace(" class='messageNO_CHANGE' ", "");
  msg = msg.replace("</div><div>", ", ");
  msg = msg.replace("</div><div>", ", ");
  msg = msg.replace("<div>", "");
  msg = msg.replace("<div>", "");
  msg = msg.replace("</div>", "");
  msg = msg.replace("</div>", "");
  var msgDiv =(isNewGui)? dojo.byId("messageDivNewGui"):dojo.byId("messageDiv");
  if (isNewGui) {msg=msg.replace('- Email','<br/>Email');}
  if (msgDiv) {
    if (isNewGui)  msgDiv.innerHTML = "<table><tr><td style='white-space:nowrap;vertical-align:top;'>[" + getTime() + "]&nbsp;</td><td>" + msg + "</td></tr></table>" + msgDiv.innerHTML;
    else msgDiv.innerHTML = "[" + getTime() + "] " + msg + "<br/>"+ msgDiv.innerHTML;
  }
}

/**
 * ============================================================================
 * Change display theme to a new one. Themes must be defined is projeqtor.css.
 * The change is also stored in Session.
 * 
 * @param newTheme
 *          the new theme
 * @return void
 */
function changeTheme(newTheme) {
  if (newTheme != "") {
    if (isNewGui) {
      if (dojo.byId('body')) dojo.byId('body').className = 'nonMobile tundra ProjeQtOrFlatGrey ProjeQtOrNewGui';
    } else {
      dojo.byId('body').className = 'nonMobile tundra ' + newTheme;
    }
    // Mehdi #2887
    var callBack = function() { 
     if(!isNewGui) addMessage("Theme=" + newTheme); 
      if(dojo.byId("mainDivContainer"))resizeContainer("mainDivContainer", null);
    };
    saveDataToSession('theme',newTheme, true, callBack);
  }
}

function saveUserParameter(parameter, value) {
  dojo.xhrPost({
    url : "../tool/saveUserParameter.php?parameter=" + parameter + "&value="
        + value,
    handleAs : "text",
    load : function(data, args) {
    }
  });
}
/**
 * ============================================================================
 * Save the browser locale to session. Needed for number formating under PHP 5.2
 * compatibility
 * 
 * @param none
 * @return void
 */
function saveBrowserLocaleToSession() {
  browserLocale = dojo.locale;
  //#2887
  saveDataToSession('browserLocale', browserLocale, null);
  var date = new Date(2000, 11, 31, 0, 0, 0, 0);
  if (browserLocaleDateFormat) {
    format = browserLocaleDateFormat;
  } else {
    var formatted = dojo.date.locale.format(date, {
      formatLength : "short",
      selector : "date"
    });
    var reg = new RegExp("(2000)", "g");
    format = formatted.replace(reg, 'YYYY');
    reg = new RegExp("(00)", "g");
    format = format.replace(reg, 'YYYY');
    reg = new RegExp("(12)", "g");
    format = format.replace(reg, 'MM');
    reg = new RegExp("(31)", "g");
    format = format.replace(reg, 'DD');
    browserLocaleDateFormat = format;
    browserLocaleDateFormatJs = browserLocaleDateFormat.replace(/D/g, 'd')
        .replace(/Y/g, 'y');
  }
  saveDataToSession('browserLocaleDateFormat', encodeURI(format));
  var fmt = "" + dojo.number.format(1.1) + " ";
  var decPoint = fmt.substr(1, 1);
  saveDataToSession('browserLocaleDecimalPoint', decPoint);
  var fmt = dojo.number.format(100000) + ' ';
  var thousandSep = fmt.substr(3, 1);
  if (thousandSep == '0') {
    thousandSep = '';
  }
  saveDataToSession('browserLocaleThousandSeparator', thousandSep);
}

/**
 * ============================================================================
 * Change the current locale. Has an impact on i18n function. The change is also
 * stored in Session.
 * 
 * @param locale
 *          the new locale (en, fr, ...)
 * @return void
 */
function saveDataToSessionAndReload(param, value, saveUserParameter) {
  var callBack = function() { 
    showWait();
    noDisconnect = true;
    quitConfirmed = true;
    //gautier 3287
    if(param == 'currentLocale'){
      var currentItem=historyTable[historyPosition];
      if(currentItem != undefined && currentItem[2] != undefined){
        if(currentItem[2]=="object"){
          var directAccessPage = "objectMain.php";
          dojo.byId("changeCurrentLocale").value = "changeCurrentLocale";
          dojo.byId("p1name").value = currentItem[0];
          dojo.byId("p1value").value = currentItem[1];
        }else{
          var directAccessPage = getTargetFromCurrentScreenChangeLang(currentItem[2]);
          if(directAccessPage == "parameter.php"){
            dojo.byId("p1name").value = "type";
            dojo.byId("p1value").value = "userParameter";
          }
        }
        dojo.byId("directAccessPage").value = directAccessPage;
      }else{
        dojo.byId("directAccessPage").value = "parameter.php";
        dojo.byId("p1name").value = "type";
        dojo.byId("p1value").value = "userParameter";
      }
    }else{
      dojo.byId("directAccessPage").value = "parameter.php";
      dojo.byId("p1name").value = "type";
      dojo.byId("p1value").value = "userParameter";
    }
    dojo.byId("menuActualStatus").value = menuActualStatus;
    dojo.byId("directAccessForm").submit();
  };
  saveDataToSession(param, value, saveUserParameter, callBack);
}

function changeLocale(locale, saveAsUserParam) {
  if (checkFormChangeInProgress()) {
    dijit.byId("langMenuUserTop").set("value",currentLocale);
    return;
  }
  if (locale != "") {
    currentLocale = locale;
    if (saveAsUserParam) saveDataToSession('lang', locale, true);
    saveDataToSessionAndReload('currentLocale', locale,true);
  }
}

function changeBrowserLocaleForDates(newFormat) {
  saveUserParameter('browserLocaleDateFormat', newFormat);
  // #2887
  var callBack = function() { 
  showWait();
    noDisconnect = true;
    quitConfirmed = true;
    dojo.byId("directAccessPage").value = "parameter.php";
    dojo.byId("menuActualStatus").value = menuActualStatus;
    dojo.byId("p1name").value = "type";
    dojo.byId("p1value").value = "userParameter";
    dojo.byId("directAccessForm").submit();
  };
  saveDataToSession('browserLocaleDateFormat', newFormat,true, callBack);
}
//gautier
function changeBrowserLocaleTimeFormat(newFormat) {
  saveUserParameter('browserLocaleTimeFormat', newFormat);
  //#2887
  var callBack = function() { 
  showWait();
  noDisconnect = true;
  quitConfirmed = true;
  dojo.byId("directAccessPage").value = "parameter.php";
  dojo.byId("menuActualStatus").value = menuActualStatus;
  dojo.byId("p1name").value = "type";
  dojo.byId("p1value").value = "userParameter";
  dojo.byId("directAccessForm").submit();
  };
  saveDataToSession('browserLocaleTimeFormat', newFormat,true, callBack);
}

function requestPasswordChange() {
  showWait();
  noDisconnect = true;
  quitConfirmed = true;
  window.location = "passwordChange.php";
  dojo.byId("directAccessPage").value = "passwordChange.php";
}
/**
 * ============================================================================
 * Change display theme to a new one. Themes must be defined is projeqtor.css.
 * The change is also stored in Session.
 * 
 * @param newTheme
 *          the new theme
 * @return void
 */
function saveResolutionToSession() {
  //var height = screen.height;
  //var width = screen.width;
  var height=document.documentElement.getBoundingClientRect().height;
  var width=document.documentElement.getBoundingClientRect().width;
  //#2887
  saveDataToSession("screenWidth", width);
  saveDataToSession("screenHeight", height);
}

/**
 * ============================================================================
 * Check if the recived key is able to change content of field or not
 * 
 * @param keyCode
 *          the code of the key
 * @return boolean : true if able to change field, else false
 */
/*
 * function isUpdatableKey(keyCode) { if (keyCode==9 // tab || (keyCode>=16 &&
 * keyCode<=20) // shift, ctrl, alt, pause, caps lock || (keyCode>=33 &&
 * keyCode<=40) // Home, end, page up, page down, arrows // (left, right, up,
 * down) || (keyCode==67) // ctrl+C || keyCode==91 // Windows || (keyCode>=112 &&
 * keyCode<=123) // Function keys || keyCode==144 // numlock || keyCode==145 //
 * stop || keyCode>=166 // Media keys ) { return false; } return true; // others }
 */

/**
 * ============================================================================
 * Clean the content of a Div. To be sure all widgets are cleaned before setting
 * new data in the Div. If fadeLoading is true, the Div fades away before been
 * cleaned. (fadeLoadsing is a global var definied in main.php)
 * 
 * @param destination
 *          the name of the Div to clean
 * @return void
 */
function cleanContent(destination) {
  var contentNode = dojo.byId(destination);
  var contentWidget = dijit.byId(destination);
  if (!(contentNode && contentWidget)) {
    return;
  }
  if (contentWidget) {
    contentWidget.set('content', null);
  }
  return;

}

/**
 * ============================================================================
 * Load the content of a Div with a new page. If fadeLoading is true, the Div
 * fades away before, and fades back in after. (fadeLoadsing is a global var
 * definied in main.php)
 * 
 * @param page
 *          the url of the page to fetch
 * @param destination
 *          the name of the Div to load into
 * @param formName
 *          the name of the form containing data to send to the page
 * @param isResultMessage
 *          boolean to specify that the destination must show the result of some
 *          treatment, calling finalizeMessageDisplay
 * @return void
 */
var formDivPosition = null; // to replace scrolling of detail after save.
var editorArray = new Array();
//var loadContentRetryArray=new Array();
var loadContentStack=new Array();
var loadContentCallSequential=false; // Should be ok to false, if errors, place to true
function truncUrlFromParameter(page,param) {
  if (page.indexOf("?"+param+"=") > 0) {
    page=page.substring(0,page.indexOf("?"+param+"="));
  } else if (page.indexOf("&"+param+"=") > 0) {
    page=page.substring(0,page.indexOf("&"+param+"="));
  }
  return page;
}
function getLoadContentStackKey(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading) {
  page=truncUrlFromParameter(page,'destinationWidth');
  page=truncUrlFromParameter(page,'directAccessIndex');
  page=truncUrlFromParameter(page,'isIE');
  page=truncUrlFromParameter(page,'xhrPostDestination');
  page=truncUrlFromParameter(page,'xhrPostTimestamp');
  var callKey=page
         +"|"+destination
         +"|"+((formName==undefined || formName==null || formName==false)?'':formName)
         +"|"+((isResultMessage==undefined || isResultMessage==null || isResultMessage==false)?'false':isResultMessage)
         +"|"+((validationType==undefined || validationType==null || validationType==false)?'':validationType);
  return callKey;
}
function storeLoadContentStack(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading) {
  var arrayStack=new Array();
  var callKey=getLoadContentStackKey(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
  arrayStack['page']=page;
  arrayStack['destination']=destination;
  arrayStack['formName']=formName;
  arrayStack['isResultMessage']=isResultMessage;
  arrayStack['validationType']=validationType;
  arrayStack['directAccess']=directAccess;
  arrayStack['silent']=silent;
  arrayStack['callBackFunction']=callBackFunction;
  arrayStack['noFading']=noFading;
  loadContentStack[callKey]=arrayStack;
}
function cleanLoadContentStack(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading) {
  var callKey=getLoadContentStackKey(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
  if (loadContentStack[callKey]!==undefined) {
    //loadContentStack.splice(callKey,1);
    delete loadContentStack[callKey];
  }
  if (loadContentCallSequential==true) {
    // Call next
    for (var arrKey in loadContentStack) {
      firstItemKey=arrKey;
      break;
    }
    var firstItem=loadContentStack[firstItemKey];
    if (firstItem===undefined) return;
    delete loadContentStack[firstItemKey];
    loadContent(firstItem['page'], firstItem['destination'], firstItem['formName'], firstItem['isResultMessage'], firstItem['validationType'], firstItem['directAccess'], firstItem['silent'], firstItem['callBackFunction'], firstItem['noFading']);
  }
}
function warnLoadContentError(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading) {
  console.warn("Error while calling xhrPost for loadContent()");
  console.warn("  => page='"+page+"'");
  console.warn("  => destination='"+destination+"'");
  console.warn("  => formName'"+formName+"'");
  console.warn("  => isResultMessage='"+isResultMessage+"'");
  console.warn("  => validationType='"+validationType+"'");
  console.warn("  => directAccess='"+directAccess+"'");
  console.warn("  => silent='"+silent+"'");
  console.warn("  => callBackFunction='"+"?"+"'");
  console.warn("  => noFading='"+noFading+"'");
}
function loadContentStream() {
  if(dojo.byId('detailRightDiv') && dojo.byId('detailRightDiv').offsetWidth>0 && dojo.byId('detailRightDiv').offsetHeight>0){
    loadContent("objectStream.php", "detailRightDiv", "listForm");  
  }
}
function loadContent(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading) {
  if (formName && formName!=undefined && formName.id) formName=formName.id;
  if (!dojo.byId(formName)) formName=null;
  var debugStart = (new Date()).getTime();
  // Test validity of destination : must be a node and a widget
  var contentNode = dojo.byId(destination);
  var contentWidget = dijit.byId(destination);
  var fadingMode = top.fadeLoading;
  var callKey=getLoadContentStackKey(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
  //if (loadContentRetryArray[callKey]===undefined) {
  //  loadContentRetryArray[callKey]=1;
  //} else {
  //  loadContentRetryArray[callKey]+=1;
  //}
  if (loadContentStack[callKey]===undefined) {
    storeLoadContentStack(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
    // If only call sequential, wait don't process : will be triggered when current has ended
    if (loadContentCallSequential==true && Object.keys(loadContentStack).length>1) {
      return; 
    }
  } else {
    // already calling same request for same target with same parameters.
    // avoid double call
    return;
  }
  
  if (dojo.byId('formDiv')) {
    formDivPosition = dojo.byId('formDiv').scrollTop;
  }
  if (page.substr(0, 16) == 'objectDetail.php') {
    // if item = current => refresh without fading
    if (dojo.byId('objectClassName') && dojo.byId('objectId') && dojo.byId('objectClass') && dojo.byId('id')) {
      if (dojo.byId('objectClass').value == dojo.byId('objectClassName').value
          && dojo.byId('objectId').value == dojo.byId('id').value) {
        fadingMode = false;
      }
    }
  }
  if (noFading) fadingMode = false;
  if (page.substr(0, 16) == 'objectStream.php') {
    fadingMode = false;
    silent=true;
  }
  
  if (!(contentNode && contentWidget)) {
    consoleTraceLog(i18n("errorLoadContent", new Array(page, destination,
        formName, isResultMessage, destination)));
    hideWait();
    cleanLoadContentStack(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
    return;
  }
  filterStatus = document.getElementById('barFilterByStatus');
  if (contentNode && page.indexOf("destinationWidth=")<0) {
    destinationWidth = dojo.style(contentNode, "width");
    destinationHeight = dojo.style(contentNode, "height");
    if (destination == 'detailFormDiv' && !editorInFullScreen()) {
      widthNode = dojo.byId('detailDiv');
      if (widthNode) {
        destinationWidth = dojo.style(widthNode, "width");
        destinationHeight = dojo.style(widthNode, "height");
      }
    }
    if (page.indexOf('diary.php') != -1) {
		  detailTop = dojo.byId('listDiv').offsetHeight;
		  detail = dojo.byId('detailDiv');
      destinationHeight = dojo.byId('centerDiv').offsetHeight - detailTop;
      detail.style.height = destinationHeight + "px";
    	dojo.byId('detailDiv').style.top = detailTop + "px";
    }
    if (page.indexOf("?") > 0) {
      page += "&destinationWidth=" + destinationWidth + "&destinationHeight="
          + destinationHeight;
    } else {
      page += "?destinationWidth=" + destinationWidth + "&destinationHeight="
          + destinationHeight;
    }
  }
  if (directAccessIndex && page.indexOf("directAccessIndex=")<0) {
    if (page.indexOf("?") > 0) {
      page += "&directAccessIndex=" + directAccessIndex;
    } else {
      page += "?directAccessIndex=" + directAccessIndex;
    }
  }
  if (page.indexOf("isIE=")<0) {
    page += ((page.indexOf("?") > 0) ? "&" : "?") + "isIE=" + ((dojo.isIE) ? dojo.isIE : '');
  }
  if (page.indexOf('diary.php') != -1) {
	  page+="&diarySelectItems="+dijit.byId('diarySelectItems').value;
	  if (dojo.byId('countStatus')) {
	    	var filteringByStatus = false;
	    	for (var i = 1; i <= dojo.byId('countStatus').value; i++) {
	    		if (dijit.byId('showStatus' + i).checked) {
	    			page += "&objectStatus" + i + "=" + dijit.byId('showStatus' + i).value;
	    			filteringByStatus = true;
	    		}
	    	}
	    	if (filteringByStatus) {
	    		page += "&countStatus=" + dojo.byId('countStatus').value;
	    	}
	    }
  }
  if (!silent) showWait();
  // NB : IE Issue (<IE8) must not fade load
  // send Ajax request
    // add to url main parameters of call to loadContent 
    page += ((page.indexOf("?") > 0) ? "&" : "?") + "xhrPostDestination="+((destination)?destination:'')
                                                  + "&xhrPostIsResultMessage="+((isResultMessage)?'true':'false')
                                                  + "&xhrPostValidationType="+((validationType)?validationType:'');
    // add a Timestamp to url
    page += '&xhrPostTimestamp='+Date.now();
    if (page.substr(0, 16) == 'objectStream.php' && page.indexOf("objectClassList=")<0) {
      var currentScreenUrl='undefined';
      if (dojo.byId('objectClassManual')) currentScreenUrl=dojo.byId('objectClassManual').value;
      else if (dojo.byId('objectClass')) currentScreenUrl=dojo.byId('objectClass').value;
      page+='&objectClassList='+currentScreenUrl;
    }
    dojo.xhrPost({
        url : page,
        form : formName,
        handleAs : "text",
        load : function(data, args) {     
          var sourceUrl=args['url'];
          if (sourceUrl && sourceUrl!='undefined' && sourceUrl.indexOf('xhrPostDestination=')>0) {
            var xhrPostArgsString=sourceUrl.substr(sourceUrl.indexOf('xhrPostDestination='));
            var xhrPostParams=xhrPostArgsString.split('&');
            for (var i=0; i<xhrPostParams.length;i++) {
              var str=xhrPostParams[i];
              var callParam=str.split('=');
              if (callParam[0]=='xhrPostDestination' ) {
                destination=(callParam[1] && callParam[1]!='undefined')?callParam[1]:'';
              } else if (callParam[0]=='xhrPostIsResultMessage') {
                isResultMessage=(callParam[1] && callParam[1]!='undefined' && callParam[1]=='true')?true:false;
              } else if (callParam[0]=='xhrPostValidationType') {
                validationType=(callParam[1] && callParam[1]!='undefined')?callParam[1]:'';
              }
            }
          }
          // retreive parameters of loadContent from url
          var debugTemp = (new Date()).getTime();
          var contentNode = dojo.byId(destination);
          var contentWidget = dijit.byId(destination);
          if (fadingMode) {
            dojo.fadeIn({
              node : contentNode,
              duration : 500,
              onEnd : function() {
              }
            }).play();
          }
          // update the destination when ajax request is received
          if (!contentNode || !contentWidget) {
            //if (loadContentRetryArray[callKey]!==undefined) {
            //  loadContentRetryArray.splice(callKey, 1);
            //}
            warnLoadContentError(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading)
            console.warn("return from xhrPost for a loadContent : '"+destination+"' is not a node or not a widget");
            console.warn(contentNode);
            console.warn(contentWidget);
            cleanLoadContentStack(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
            hideWait();
            return;
          }
          // Must destroy existing instances of CKEDITOR before refreshing the page
          // page.
          if (page.substr(0, 16) == 'objectDetail.php'
              && (destination == 'detailDiv' || destination == 'detailFormDiv' || destination == "formDiv") && !editorInFullScreen()) {
            editorArray = new Array();
            for (name in CKEDITOR.instances) {
              CKEDITOR.instances[name].removeAllListeners();
              CKEDITOR.instances[name].destroy(false);
            }
            if (dijit.byId('attachmentFileDirect')) { // Try to remove dropTarget, but does not exist in API
              //dijit.byId('attachmentFileDirect').removeDropTarget(dojo.byId('attachmentFileDirectDiv'));
              //dijit.byId('attachmentFileDirect').removeDropTarget(dojo.byId('formDiv'),true);
              dijit.byId('attachmentFileDirect').reset(); // Test
            }
          }
          hideBigImage(); // Will avoid resident pop-up always displayed
          
          if (destination=='menuBarListDiv' || destination=='anotherBarContainer') {
            // Specific treatment for refreshMenuBarList.php and refreshMenuAnotherBarList.php so that they are cleared on the same time, to avoid blinking
            if (destination=='menuBarListDiv') { menuBarListDivData=data; menuBarListDivCallback=callBackFunction; } 
            if (destination=='anotherBarContainer') { anotherBarContainerData=data; anotherBarContainerCallback=callBackFunction; }
            if (menuBarListDivData!=null && anotherBarContainerData!=null) {
              cleanContent('menuBarListDiv');
              cleanContent('anotherBarContainer');
              dijit.byId('menuBarListDiv').set('content', menuBarListDivData);
              dijit.byId('anotherBarContainer').set('content', anotherBarContainerData);
              if (menuBarListDivCallback!=null) setTimeout(menuBarListDivCallback, 100);
              if (anotherBarContainerCallback!=null) setTimeout(anotherBarContainerCallback, 100);
              menuBarListDivData=null;
              anotherBarContainerData=null;
              menuNewGuiFilterInProgress=false;
              hideWait();
            }
            cleanLoadContentStack(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
            return;
          } else {
            cleanContent(destination);
            if(!editorInFullScreen()) contentWidget.set('content', data);
          }
          checkDestination(destination);
          // Create instances of CKEDITOR
          if (page.substr(0, 16) == 'objectDetail.php'
              && (destination == 'detailDiv' || destination == 'detailFormDiv' || destination == "formDiv") && !editorInFullScreen()) {
            ckEditorReplaceAll();
          }
          if ( (page.substr(0, 16) == 'objectDetail.php' && destination == 'detailDiv')
            || (page.substr(0, 17) == 'objectButtons.php' && destination == 'buttonDiv')) {
            if (dojo.byId('attachmentFileDirectDiv') && dijit.byId('attachmentFileDirect')) {
              dijit.byId('attachmentFileDirect').reset();
              dijit.byId('attachmentFileDirect').addDropTarget(dojo.byId('attachmentFileDirectDiv'));
              dijit.byId('attachmentFileDirect').addDropTarget(dojo.byId('formDiv'),true);
            }
          }
          if (dojo.byId('objectClass') && destination.indexOf(dojo.byId('objectClass').value) == 0) { // If refresh a section
            var section = destination.substr(dojo.byId('objectClass').value.length + 1);
            refreshSectionCount(section);
          }
          if (destination == "detailDiv" || destination == "centerDiv") {
            finaliseButtonDisplay();
          }
          if (destination == "detailDiv" && dojo.byId('objectClass')&& dojo.byId('objectClass').value && dojo.byId('objectId') && dojo.byId('objectId').value) {
            stockHistory(dojo.byId('objectClass').value,
                dojo.byId('objectId').value);
          } 
          if (dojo.byId('formDiv') && formDivPosition >= 0) {
            dojo.byId('formDiv').scrollTop = formDivPosition;
          }
          if (destination == "centerDiv" && switchedMode && !directAccess) {
            if(loadingContentDiv==true){
              hideList();
              loadingContentDiv=false;
            }else{
              showList();
            }           
          }
          if (destination == "centerDiv" && dijit.byId('objectGrid')) {
            mustApplyFilter=true;
          }
          if (destination == "dialogLinkList") {
            selectLinkItem();
          }
          if (destination == "directFilterList") {
            if (!validationType || validationType=='returnFromFilter' ) {
              if (window.top.dojo.byId('noFilterSelected')&& window.top.dojo.byId('noFilterSelected').value == 'true') {
                dijit.byId("listFilterFilter").set("iconClass", "dijitButtonIcon iconFilter");
              } else {
                dijit.byId("listFilterFilter").set("iconClass","dijitButtonIcon iconActiveFilter");
              }
              if (globalSelectFilterContenLoad && globalSelectFilterContainer) {
                loadContent(globalSelectFilterContenLoad, globalSelectFilterContainer);
                globalSelectFilterContenLoad=null;
                globalSelectFilterContainer=null;
              } else if (dojo.byId('objectClassManual') && (dojo.byId('objectClassManual').value=='Planning' || dojo.byId('objectClassManual').value=='VersionsPlanning' || dojo.byId('objectClassManual').value=='ResourcePlanning' ||dojo.byId('objectClassManual').value=='ContractGantt')) {
                refreshJsonPlanning();
              } else if (dojo.byId('objectClassList')) {
                refreshJsonList(dojo.byId('objectClassList').value);
              } else {
                refreshJsonList(dojo.byId('objectClass').value);
              }
            }
          }
          if (destination == "expenseDetailDiv") {
            expenseDetailRecalculate();
          }
          if (directAccess) {
            if (dojo.byId('objectClass') && dojo.byId('objectId') && dijit.byId('listForm')) {
              if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value=='GlobalView') {
                var expl= directAccess.split('|');
                dojo.byId('objectClass').value = expl[0];
                dojo.byId('objectId').value = expl[1];
              } else {
                dojo.byId('objectId').value = directAccess;
                directAccess=parseInt(directAccess);
              }
              showWait();
              var callBackFinal=function() {setTimeout('selectRowById("objectGrid", '+directAccess+');', 10);};
              loadContent("objectDetail.php", "detailDiv", 'listForm',null,null,null,null,callBackFinal);
              loadContentStream();
              showWait();
              hideList();              
            }
          }
          if (isResultMessage) {    
            var contentNode = dojo.byId(destination);
            var lastOperationStatus = dojo.byId('lastOperationStatus');
            var lastOperation = dojo.byId('lastOperation');
            if (! (lastOperationStatus && lastOperation) ) {
              consoleTraceLog("***** Error **** isResultMessage without lastOperation or lastOperationStatus");
              consoleTraceLog(data);
            }
            dojo.fadeIn({
              node : contentNode,
              duration : 100,
              onEnd : function() {
                if(!editorInFullScreen()) {
                  finalizeMessageDisplay(destination, validationType);
                } else {
                  var elemDiv = document.createElement('div');
                  elemDiv.id='testFade';
                  var leftMsg=(window.innerWidth - 400)/2;
                  elemDiv.style.cssText = 'position:absolute;text-align:center;width:400px;height:auto;z-index:10000;top:50px;left:'+leftMsg+'px';
                  //elemDiv.className='messageOK';
                  elemDiv.innerHTML=data;
                  document.body.appendChild(elemDiv);
                  resultDivFadingOut = dojo.fadeOut({
                    node : elemDiv,
                    duration : 3000,
                    onEnd : function() {
                      elemDiv.remove();
                    }
                  }).play();
                  hideWait();
                  formInitialize();
                  if (whichFullScreen==996) {
                    // save with editor in full screen for new CK TextFull Screen
                    // Do not change focus
                  } else if (whichFullScreen>=0 && editorArray[whichFullScreen]) {
                    editorArray[whichFullScreen].focus();
                  }
                }
              }
            }).play();
          } else if (destination == "loginResultDiv") {
            checkLogin();
          } else if (destination == "passwordResultDiv") {
            checkLogin();
          } else if (page.indexOf("planningMain.php") >= 0
              || page.indexOf("planningList.php") >= 0
              || (page.indexOf("jsonPlanning.php") >= 0 && dijit.byId("startDatePlanView"))
              || page.indexOf("resourcePlanningMain.php") >= 0
              || page.indexOf("resourcePlanningList.php") >= 0
              || (page.indexOf("jsonResourcePlanning.php") >= 0 && dijit.byId("startDatePlanView"))
              || page.indexOf("globalPlanningMain.php") >= 0
              || page.indexOf("globalPlanningList.php") >= 0
              || (page.indexOf("jsonGlobalPlanning.php") >= 0 && dijit.byId("startDatePlanView"))
              || page.indexOf("portfolioPlanningMain.php") >= 0
              || page.indexOf("portfolioPlanningList.php") >= 0
              || (page.indexOf("jsonPortfolioPlanning.php") >= 0 && dijit.byId("startDatePlanView"))
              //ADD qCazelles - GANTT
              || page.indexOf("versionsPlanningMain.php") >= 0 
              || page.indexOf("versionsPlanningList.php") >= 0
              || (page.indexOf("jsonVersionsPlanning.php") >= 0 && dijit.byId("startDatePlanView"))
              || page.indexOf("contractGanttMain.php") >= 0 
              || page.indexOf("contractGanttList.php") >= 0
              || (page.indexOf("jsonContractGantt.php") >= 0 && dijit.byId("startDatePlanView"))) {
            //END ADD qCazelles - GANTT
            drawGantt();
            selectPlanningRow();
            if (!silent)
              hideWait();
            var bt = dijit.byId('planButton');
            if (bt) {
              bt.set('iconClass', "dijitIcon iconPlanStopped");
            }
          } else if (destination == "resultDivMultiple") {
            finalizeMultipleSave();
          } else {
            if (!silent)
              hideWait();
          }
          // For debugging purpose : will display call page with execution time
          var debugEnd = (new Date()).getTime();
          var debugDuration = debugEnd - debugStart;
          var msg = "=> " + debugDuration + "ms";
          msg += " | page='"
              + ((page.indexOf('?')) ? page.substring(0, page.indexOf('?'))
                  : page) + "'";
          msg += " | destination='" + destination + "'";
          if (formName)
            msg += " | formName=" + formName + "'";
          if (isResultMessage)
            msg += " | isResultMessage='" + isResultMessage + "'";
          if (validationType)
            msg += " | validationType='" + validationType + "'";
          if (directAccess)
            msg += " | directAccess='" + directAccess + "'";
          if (callBackFunction != null)
            setTimeout(callBackFunction, 100);
          var debugDurationServer = debugTemp - debugStart;
          var debugDurationClient = debugEnd - debugTemp;
          msg += " (server:" + debugDurationServer + "ms, client:"
              + debugDurationClient + "ms)";
          consoleTraceLog(msg);
          cleanLoadContentStack(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
          //if (loadContentRetryArray[callKey]!==undefined) {
          //  loadContentRetryArray.splice(callKey, 1);
          //}
        },
        error : function(error, args) {
          //var retries=-1;          
          //if (loadContentRetryArray[callKey]!==undefined) {
          //  retries=loadContentRetryArray[callKey];
          //}
          cleanLoadContentStack(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
          warnLoadContentError(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading);
          console.warn(error);
          if (!silent) hideWait();
          finaliseButtonDisplay();
          //formChanged();
          //if (retries>0 && retries <3) { // On error, will retry ou to 3 times before raising an error
          //  console.warn('['+retries+'] '+i18n("errorXhrPost", new Array(page, destination,formName, isResultMessage, error)));
          //  loadContent(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction);
          //} else {
            enableWidget('saveButton');
            enableWidget('undoButton');
            //console.warn(i18n("errorXhrPost", new Array(page, destination,formName, isResultMessage, error))); // No use with warnLoadContentError
            hideWait();
            //showError(i18n('errorXhrPostMessage'));
          //}
        }
      });
  if (fadingMode) {
    dojo.fadeOut({
      node : contentNode,
      duration : 200,
      onEnd : function() {
      }
    }).play();
  }
}

/**
 * ============================================================================
 * Load some non dojo content div (like loadContent, but for simple div) Content
 * will not be parsed by dojo
 * 
 * @param page
 *          php page to load
 * @param destinationDiv
 *          name of distination div
 * @param formName
 *          nale of form to post (optional)
 */

function loadDiv(page, destinationDiv, formName, callback) {
  if (formName && formName!=undefined && formName.id) formName=formName.id;
  if (!dojo.byId(formName)) formName=null;
  var contentNode = dojo.byId(destinationDiv);
  if (page.indexOf('getObjectCreationInfo')>=0 && dijit.byId('detailDiv') && page.indexOf('destinationWidth')<0) {
    var destinationWidth = dojo.style(dojo.byId('detailDiv'), "width");
    // var destinationHeight = dojo.style(dojo.byId('detailDiv'), "height");
    page+=((page.indexOf('?')>=0)?'&':'?')+'destinationWidth='+destinationWidth;
  }
  dojo.xhrPost({
    url : page,
    form : formName,
    handleAs : "text",
    load : function(data) {
      contentNode.innerHTML = data;
      if (callback)
        setTimeout(callback, 10);
    }
  });
}
/**
 * ============================================================================
 * Check if destnation is correct If not in main page and detect we have login
 * page => wrong destination
 */
function checkDestination(destination) {
  if (dojo.byId("isLoginPage") && destination != "loginResultDiv") {
    // if (dojo.isFF) {
    consoleTraceLog("errorConnection: isLoginPage but destination is not loginResultDiv");
    quitConfirmed = true;
    noDisconnect = true;
    window.location = "main.php?lostConnection=true";
    // } else {
    // hideWait();
    // showAlert(i18n("errorConnection"));
    // }
  }
  if (!dijit.byId('objectGrid') && dojo.byId('multiUpdateButtonDiv')) {
    dojo.byId('multiUpdateButtonDiv').style.display = 'none';
  }
  if (dojo.byId('indentButtonDiv')) {
    if (dijit.byId('objectGrid')) {
      dojo.byId('indentButtonDiv').style.display = 'none';
    } else if (dojo.byId('objectClassManual') && (dojo.byId('objectClassManual').value != 'Planning' && dojo.byId('objectClassManual').value != 'GlobalPlanning')) {
      dojo.byId('indentButtonDiv').style.display = 'none';
    }
  }
  dojo.query('.titlePaneFromDetail').forEach(function(node, index, nodelist) { // Apply specific style for title panes
    dijit.byId(node.id).titlePaneHandler();
  });
}
/**
 * ============================================================================
 * Chek the return code from login check, if valid, refresh page to continue
 * 
 * @return void
 */
function checkLogin() {
  resultNode = dojo.byId('validated');
  resultWidget = dojo.byId('validated');
  if (resultNode && resultWidget) {
    saveResolutionToSession();
    // showWait();
    if (changePassword) {
      quitConfirmed = true;
      noDisconnect = true;
      var tempo=300;
      if (dojo.byId('notificationOnLogin')) {
        tempo=1500;
      } 
      setTimeout('window.location = "main.php?changePassword=true";',tempo);
    } else {
      quitConfirmed = true;
      noDisconnect = true;
      url = "main.php";
      if (dojo.byId('objectClass') && dojo.byId("objectId")) {
        url += "?directAccess=true&objectClass="
            + dojo.byId('objectClass').value + "&objectId="
            + dojo.byId("objectId").value;
      }
      var tempo=400;
      if (dojo.byId('notificationOnLogin')) {
        tempo=1500;
      } 
      setTimeout('window.location ="'+url+'";',tempo);
    }
  } else {
    hideWait();
  }
}

/**
 * ============================================================================
 * Submit a form, after validating the data
 * 
 * @param page
 *          the url of the page to fetch
 * @param destination
 *          the name of the Div to load into
 * @param formName
 *          the name of the form containing data to send to the page
 * @return void
 */
function submitForm(page, destination, formName) {
  var formVar = dijit.byId(formName);
  if (!formVar) {
    showError(i18n("errorSubmitForm", new Array(page, destination, formName)));
    return;
  }
  // validate form Data
  if (formVar.validate()) {
    formLock();
    // form is valid, continue and submit it
    var isResultDiv = true;
    if (formName == 'passwordForm') {
      isResultDiv = false;
    }
    loadContent(page, destination, formName, isResultDiv);
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
/**
 * ============================================================================
 * Refresh the Notification tree on the 'Unread Notifications' Accordion
 * @param bCheckFormChangeInProgress : True if FormChangeInProgress is to check
 * @return void
 */
function refreshNotificationTree(bCheckFormChangeInProgress) {
    if (paramNotificationSystemActiv == false) {
        return;
    }
    if (bCheckFormChangeInProgress && waitingForReply) {
        return;
    }
    dijit.byId("notificationTree").model.store.clearOnClose = true;
    dijit.byId("notificationTree").model.store.close();
    // Completely delete every node from the dijit.Tree
    dijit.byId("notificationTree")._itemNodesMap = {};
    dijit.byId("notificationTree").rootNode.state = "UNCHECKED";
    dijit.byId("notificationTree").model.root.children = null;
    // Destroy the widget
    dijit.byId("notificationTree").rootNode.destroyRecursive();
    // Recreate the model, (with the model again)
    dijit.byId("notificationTree").model.constructor(dijit.byId("notificationTree").model);
    // Rebuild the tree
    dijit.byId("notificationTree").postMixInProperties();
    dijit.byId("notificationTree")._load();
}
// END - ADD BY TABARY - NOTIFICATION SYSTEM

/**
 * ============================================================================
 * Finalize some operations after receiving validation message of treatment
 * 
 * @param destination
 *          the name of the Div receiving the validation message
 * @return void
 */
var resultDivFadingOut = null;
var forceRefreshCreationInfo = false;
var avoidInfiniteLoop=false;
function finalizeMessageDisplay(destination, validationType) {
  var contentNode = dojo.byId(destination);
  var contentWidget = dijit.byId(destination);
  var lastOperationStatus = dojo.byId('lastOperationStatus');
  var lastOperation = dojo.byId('lastOperation');
  var needProjectListRefresh = false;
  // scpecific Plan return
  if ((! validationType || validationType=='dependency') && dojo.byId('lastPlanStatus')) {
    lastOperationStatus = dojo.byId('lastPlanStatus');
    lastOperation = "plan";
    validationType = null;
  }
  if (destination == 'resultDivMain' || destination == 'resultDiv') {
    contentNode.style.display = "block";
    if (destination == 'resultDiv') {
      contentNode.style.padding='0';
      contentNode.style.position='absolute';
    }
  }
  var noHideWait = false;
  if (!(contentWidget && contentNode && lastOperationStatus && lastOperation)) {
    returnMessage = "";
    if (contentWidget) {
      returnMessage = contentWidget.get('content');
    }
    consoleTraceLog("***** ERROR ***** on finalizeMessageDisplay("
        + destination + ", " + validationType + ")");
    if (!contentNode) {
      consoleTraceLog("contentNode unknown");
    } else {
      consoleTraceLog("contentNode='" + contentNode.innerHTML+"'");
    }
    if (!contentWidget) {
      consoleTraceLog("contentWidget unknown");
    } else {
      consoleTraceLog("contentWidget='" + contentWidget.get("content")+"'");
    }
    if (!lastOperationStatus) {
      consoleTraceLog("lastOperationStatus unknown");
    } else {
      consoleTraceLog("lastOperationStatus='" + lastOperationStatus.value+"'");
    }
    if (!lastOperation) {
      consoleTraceLog("lastOperation unknown");
    } else {
      consoleTraceLog("lastOperation='" + lastOperation.value+"'");
    }
    hideWait();
    //showError(i18n("errorFinalizeMessage", new Array(destination, returnMessage)));
    formInitialize();
    return;
  }
  if (!contentWidget) {
    return;
  }
  // fetch last message type
  var message = contentWidget.get('content');
  posdeb = message.indexOf('class="message') + 7;
  posfin = message.indexOf('>', posdeb) - 1;
  typeMsg = message.substr(posdeb, posfin - posdeb);
  // if operation is OK
  if (lastOperationStatus.value == "OK"
      || lastOperationStatus.value == "INCOMPLETE") {
    posdeb = posfin + 2;
    posfin = message.indexOf('<', posdeb);
    msg = message.substr(posdeb, posfin - posdeb);
    // add the message in the message Div (left part) and prepares form to new
    // changes
    addMessage(msg);
    // alert('validationType='+validationType);
    if (validationType) {
      if (validationType == 'note') {
        loadContentStream();
        if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual')=='Kanban') loadContent("../tool/dynamicDialogKanbanGetObjectStream.php","dialogKanbanGetObjectStream","noteFormStreamKanban");
        else loadContent("objectDetail.php?refreshNotes=true", dojo.byId('objectClass').value+ '_Note', 'listForm');
        if (dojo.byId('buttonDivCreationInfo')) {
          var url = '../tool/getObjectCreationInfo.php?objectClass='+ dojo.byId('objectClass').value +'&objectId='+dojo.byId('objectId').value;
          loadDiv(url, 'buttonDivCreationInfo', null);
        }
      } else if (validationType == 'attachment') {
        if (dojo.byId('buttonDivCreationInfo')) {
          var url = '../tool/getObjectCreationInfo.php?objectClass='+ dojo.byId('objectClass').value 
          + '&objectId='+dojo.byId('objectId').value;
          loadDiv(url, 'buttonDivCreationInfo', null);
        }
        if (dojo.byId('parameter') && dojo.byId('parameter').value == 'true') {
          formChangeInProgress = false;
          waitingForReply = false;
          loadMenuBarItem('UserParameter', 'UserParameter', 'bar');
          
        } else if (dojo.byId('objectClass')
            && (dojo.byId('objectClass').value == 'Resource'
                || dojo.byId('objectClass').value == 'ResourceTeam'  
                || dojo.byId('objectClass').value == 'User' 
                || dojo.byId('objectClass').value == 'Contact')) {
          loadContent("objectDetail.php?refresh=true", "detailFormDiv",
              'listForm');
          refreshGrid();
        } else {
          loadContent("objectDetail.php?refreshAttachments=true", dojo.byId('objectClass').value+ '_Attachment', 'listForm');
        }
        dojo.style(dojo.byId('downloadProgress'), {
          display : 'none'
        });
      } else if (validationType == 'billLine') {
        loadContent("objectDetail.php?refreshBillLines=true", dojo
            .byId('objectClass').value
            + '_BillLine', 'listForm');
        loadContent("objectDetail.php?refresh=true", "detailFormDiv",
            'listForm');
        refreshGrid();
        // } else if (validationType=='documentVersion') {
        // loadContent("objectDetail.php?refresh=true", "detailFormDiv",
        // 'listForm');
      } else if (validationType == 'checklistDefinitionLine') {
        loadContent("objectDetail.php?refreshChecklistDefinitionLines=true",
            dojo.byId('objectClass').value + '_ChecklistDefinitionLine',
            'listForm');
      } else if (validationType == 'jobDefinition') {
        loadContent("objectDetail.php?refreshJobDefinition=true",
            dojo.byId('objectClass').value + '_JobDefinition',
            'listForm');
      } else if (validationType == 'testCaseRun') {
        loadContent("objectDetail.php?refresh=true", "detailFormDiv",
            'listForm');
        if (dojo.byId(dojo.byId('objectClass').value + '_history')) {
          loadContent("objectDetail.php?refreshHistory=true", dojo
              .byId('objectClass').value
              + '_history', 'listForm');
        }
      } else if (validationType == 'copyTo' || validationType == 'copyProject') {
        if (validationType == 'copyProject') {
          needProjectListRefresh = true;
          dojo.byId('objectClass').value = "Project";
        } else {
          if (dijit.byId('copyToClass') ) {
            dojo.byId('objectClass').value = copyableArray[dijit.byId('copyToClass').get('value')];
          }
        }
        var lastSaveId = dojo.byId('lastSaveId');
        var lastSaveClass = dojo.byId('objectClass');
        if (lastSaveClass && lastSaveId) {
          waitingForReply = false;
          gotoElement(lastSaveClass.value, lastSaveId.value, null, true, true);
          waitingForReply = true;
        }
      } else if (validationType == 'admin') {
        hideWait();
      } else if (validationType != 'link'  && validationType.substr(0,4)=='link' && (dojo.byId('objectClass').value == 'Requirement' || dojo.byId('objectClass').value == 'TestSession')) {
        loadContent("objectDetail.php?refresh=true", "detailFormDiv",'listForm');
        if (dojo.byId('buttonDivCreationInfo')) {
          var url = '../tool/getObjectCreationInfo.php?objectClass='+ dojo.byId('objectClass').value +'&objectId='+dojo.byId('objectId').value;
          loadDiv(url, 'buttonDivCreationInfo', null);  
        }
        refreshGrid();
      } else if ( validationType=='linkObject') {
        loadContent("objectDetail.php?refresh=true", "detailFormDiv",'listForm');
      }else if( (validationType =='link' || validationType.substr(0,4)=='link') && validationType !='linkObject'){
        var refTypeName=validationType.substr(4);     
        if (dojo.byId('buttonDivCreationInfo')) {
          var url = '../tool/getObjectCreationInfo.php?objectClass='+ dojo.byId('objectClass').value +'&objectId='+dojo.byId('objectId').value;
          loadDiv(url, 'buttonDivCreationInfo', null);  
        }
        if(refTypeName && dijit.byId(dojo.byId('objectClass').value+'_Link_'+refTypeName)){
          var url = "objectDetail.php?refreshLinks="+refTypeName;
          loadContent("objectDetail.php?refreshLinks="+refTypeName,dojo.byId('objectClass').value+ '_Link_'+refTypeName,'listForm');  
          //gautier #2947
          loadContent("objectDetail.php?refresh=true", "detailFormDiv",'listForm');
        }else{
          loadContent("objectDetail.php?refreshLinks=true",dojo.byId('objectClass').value+ '_Link','listForm');
        }
      } else if (validationType == 'report') {
        hideWait();
      } else if (validationType == 'checklist' || validationType == 'joblist') {
        hideWait();
      } else if (validationType == 'dispatchWork') {
        if (lastOperationStatus.value == "OK") {
          sum=dijit.byId('dispatchWorkTotal').get('value');
          
          if (dijit.byId('WorkElement_realWork')) {
            var stock=formChangeInProgress;
            dijit.byId('WorkElement_realWork').set('value',sum);
            if (!stock) {
              setTimeout("formInitialize();",10);
            }
          }
        }
        dijit.byId('dialogDispatchWork').hide();
        hideWait();
      } else if (lastOperation != 'plan') {
        if (dijit.byId('detailFormDiv')) { // only refresh is detail is show
                                            // (possible when DndLing on
                                            // planning
          loadContent("objectDetail.php?refresh=true", "detailFormDiv",
              'listForm');
        }
        if (validationType == 'assignment'
            || validationType == 'documentVersion') {
          refreshGrid();
        } else if ( (validationType == 'dependency' || validationType == 'affectation')
            && (dojo.byId("GanttChartDIV"))) {
          noHideWait = true;
          refreshGrid(); // Will call refreshJsonPlanning() if needed and
                          // plan() if required
        }
        // hideWait();
      }
    } else { // ! validationType
      buttonRightRefresh();
      formInitialize();
      // refresh the grid to reflect changes
      var lastSaveId = dojo.byId('lastSaveId');
      var objectId = dojo.byId('objectId');
      // Refresh the Grid list (if visible)
      var grid = dijit.byId("objectGrid");
      if (objectId && lastSaveId && lastOperation!="plan") {
        objectId.value = lastSaveId.value;
      }
      if (grid) {
        var sortIndex = grid.getSortIndex();
        var sortAsc = grid.getSortAsc();
        var scrollTop = grid.scrollTop;
        store = grid.store;
        store.close();
        store.fetch({
          onComplete : function() {
            grid._refresh();
            setTimeout('dijit.byId("objectGrid").setSortIndex(' + sortIndex
                + ',' + sortAsc + ');', 10);
            setTimeout('dijit.byId("objectGrid").scrollTo(' + scrollTop + ');',
                20);
            setTimeout('selectRowById("objectGrid", '
                + parseInt(objectId.value) + ');', 30);
          }
        });
      }
      // Refresh the planning Gantt (if visible)
      if (dojo.byId("GanttChartDIV")) {
        noHideWait = true;
        if (dojo.byId("saveDependencySuccess")
            && dojo.byId("saveDependencySuccess").value == 'true') {
          refreshGrid(); // It is a dependency add throught D&D => must
                          // replan is needed
        } else if (dojo.byId('lastOperation')
            && dojo.byId('lastOperation').value == 'move') {
          refreshGrid();
        } else if (! avoidInfiniteLoop) {
          avoidInfiniteLoop=true;
          setTimeout("avoidInfiniteLoop=false;",1000);
          if (dojo.byId("lastPlanStatus")) {
            refreshGrid(true);
          } else {
            refreshGrid(false);
          }
        } else {
          avoidInfiniteLoop=false;
          refreshJsonPlanning(); // Must not call refreshGrid() to avoid never ending loop
        }
      }
   // Refresh Hierarchical Budget list
      if (dojo.byId("HierarchicalBudget")) {
          refreshHierarchicalBudgetList();
          loadContent('objectDetail.php', 'detailDiv','listForm');
		}
      if (dojo.byId('id') && lastOperation && (lastOperation.value == "insert" || forceRefreshCreationInfo)) {
     // last operations depending on the executed operatoin (insert, delete, ...)
        if (lastSaveId) dojo.byId('id').value=lastSaveId.value;
        if (dojo.byId('objectClass')
            && dojo.byId('objectClass').value == "Project") {
          needProjectListRefresh = true;
        }
        if (dojo.byId("buttonDivObjectId")
            && (lastOperation.value=='insert' || forceRefreshCreationInfo)
            && lastSaveId && lastSaveId.value) {
          if (lastOperation.value=='insert' && dojo.byId('directLinkUrlDivDetail')) {
            //dojo.byId("buttonDivObjectId").innerHTML = "&nbsp;#"
            //  + lastSaveId.value;
            var ref=dojo.byId('directLinkUrlDivDetail').value;
            var objId=dojo.byId('id').value;
            var valueDiv='<span class="roundedButton">';
            valueDiv+= '&nbsp;<a href="'+ref+objId+'" onClick="copyDirectLinkUrl(\'Button\');return false;" title="'+i18n("rightClickToCopy")+'" ';
            valueDiv+= ' style="cursor: pointer; ';
            if (!isNewGui) valueDiv+= 'color: white;" onmouseover=this.style.color="black" onmouseout=this.style.color="white';
            valueDiv+= '">';
            valueDiv+= (objId)?'&nbsp;#'+objId:'';
            valueDiv+= '&nbsp;</a>';
            valueDiv+= '</span>';
            valueDiv+= '<input readOnly type="text" onClick="this.select();" id="directLinkUrlDivButton" style="display:none;font-size:9px; '+((isNewGui)?'':'color: #000000')+';position :absolute; top: 47px; left: 157px; border: 0;background: transparent;width:300px;" value="'+ref+objId+'" />';
            dojo.byId("buttonDivObjectId").innerHTML=valueDiv;
          }
          //gautier
          if(dojo.byId("buttonDivObjectName") && dijit.byId('name')){
              if(dijit.byId('name').get("value")){
                dojo.byId("buttonDivObjectName").innerHTML="-&nbsp;"+dijit.byId('name').get("value");
            }
          }
          if (dojo.byId('buttonDivCreationInfo')) {
// MTY - LEAVE SYSTEM    
            if (dojo.byId("forceRefreshMenu")) {
                if (dojo.byId("forceRefreshMenu").value.substr(0,8)=='Resource') {
                    var url="";
                }
            } else {
              
            var url = '../tool/getObjectCreationInfo.php' + '?objectClass='
                + dojo.byId('objectClass').value + '&objectId='
                + lastSaveId.value;
            }  
// MTY - LEAVE SYSTEM    
            var callback=null;
            if (dojo.byId('objectClass').value=='ProductVersion' || dojo.byId('objectClass').value=='ComponentVersion' ) {
              callback=function() {
                if (! dojo.byId('isCurrentUserSubscription')) return;
                var subs=dojo.byId('isCurrentUserSubscription').value;
                if(subs == '1'){
                  dijit.byId('subscribeButton').set('iconClass','dijitButtonIcon dijitButtonIconSubscribeValid');
                  enableWidget('subscribeButtonUnsubscribe');
                  disableWidget('subscribeButtonSubscribe');
                }else{
                  dijit.byId('subscribeButton').set('iconClass','dijitButtonIcon dijitButtonIconSubscribe');
                  disableWidget('subscribeButtonUnsubscribe');
                  enableWidget('subscribeButtonSubscribe');
                }
              };
            }
// MTY - LEAVE SYSTEM    
            if (url!="") {
// MTY - LEAVE SYSTEM    
            loadDiv(url, 'buttonDivCreationInfo',null, callback);
          }
        }
        }
        forceRefreshCreationInfo = false;
        if (dojo.byId('attachmentFileDirectDiv')) {
          if(dojo.byId('attachmentFileDirectDiv').style.visibility == 'hidden') {
            dijit.byId('attachmentFileDirect').reset();
            //dijit.byId('attachmentFileDirect').addDropTarget(dojo.byId('formDiv'),true);
          }
          dojo.byId('attachmentFileDirectDiv').style.visibility = 'visible';
        }
        if (dojo.byId('objectClass') && dojo.byId('objectId')) {
          stockHistory(dojo.byId('objectClass').value,dojo.byId('objectId').value);
        }
      }
      if (lastOperation.value == "delete") {
        var zone = dijit.byId("formDiv");
        var zoneRight = dijit.byId("detailRightDiv");
        var msg = dojo.byId("noDataMessage");
        if (zone && msg) {
          zone.set('content', msg.value);
        }
        if (zoneRight && msg) {
          zoneRight.set('content', msg.value);
        }
        if (dojo.byId('objectClass')
            && dojo.byId('objectClass').value == "Project") {
          needProjectListRefresh = true;
        }
        if (dojo.byId("buttonDivObjectId")) {
          dojo.byId("buttonDivObjectId").innerHTML = "";
        }
        
        if (dojo.byId('buttonDivCreationInfo')) {
          dojo.byId("buttonDivCreationInfo").innerHTML = "";
        }
        if (dojo.byId('attachmentFileDirectDiv')) {
          dojo.byId('attachmentFileDirectDiv').style.visibility = 'hidden';
          dijit.byId('attachmentFileDirect').reset();
        }
        // unselectAllRows("objectGrid");
        finaliseButtonDisplay();
      }
      if ((grid || dojo.byId("GanttChartDIV")) && dojo.byId("detailFormDiv")
          && refreshUpdates == "YES" && lastOperation.value != "delete") {
        // loadContent("objectDetail.php?refresh=true", "formDiv",
        // 'listForm');
        if (lastOperation.value == "copy") {
          loadContent("objectDetail.php?", "detailDiv", 'listForm');
        } else {
          loadContent("objectDetail.php?refresh=true", "detailFormDiv", 'listForm');
          if(dojo.byId('detailRightDiv')){
            loadContentStream();  
          } else if (validationType == 'noteKanban' ){
            loadContent("../tool/dynamicDialogKanbanGetObjectStream.php","dialogKanbanGetObjectStream","noteFormStreamKanban");     
          } 
          // Need also to refresh History
          if (dojo.byId(dojo.byId('objectClass').value + '_history') && dojo.byId(dojo.byId('objectClass').value + '_history').style.display!='none') {
            loadContent("objectDetail.php?refreshHistory=true", dojo
                .byId('objectClass').value
                + '_history', 'listForm');
          }
          if (dojo.byId(dojo.byId('objectClass').value + '_BillLine')) {
            loadContent("objectDetail.php?refreshBillLines=true", dojo
                .byId('objectClass').value
                + '_BillLine', 'listForm');
          }
          var refreshDetailElse = false;
          if (lastOperation.value == "insert") {
            refreshDetailElse = true;
          } else {
            if (dijit.byId('idle') && dojo.byId('attachmentIdle')) {
              if (dijit.byId('idle').get("value") != dojo
                  .byId('attachmentIdle').value) {
                refreshDetailElse = true;
              }
            }
            if (dijit.byId('idle') && dojo.byId('noteIdle')) {
              if (dijit.byId('idle').get("value") != dojo.byId('noteIdle').value) {
                refreshDetailElse = true;
              }
            }
            if (dijit.byId('idle') && dojo.byId('billLineIdle')) {
              if (dijit.byId('idle').get("value") != dojo.byId('billLineIdle').value) {
                refreshDetailElse = true;
              }
            }
          }
          if (refreshDetailElse && !validationType) {
            if (dojo.byId(dojo.byId('objectClass').value + '_Attachment')) {
              loadContent("objectDetail.php?refreshAttachments=true", dojo
                  .byId('objectClass').value
                  + '_Attachment', 'listForm');
            }
            if (dojo.byId(dojo.byId('objectClass').value + '_Note')) {
              loadContent("objectDetail.php?refreshNotes=true", dojo
                  .byId('objectClass').value
                  + '_Note', 'listForm');
            }
            if (dojo.byId(dojo.byId('objectClass').value + '_BillLine')) {
              loadContent("objectDetail.php?refreshBillLines=true", dojo
                  .byId('objectClass').value
                  + '_BillLine', 'listForm');
            }
            if (dojo.byId(dojo.byId('objectClass').value + '_checklistDefinitionLine')) {
              loadContent(
                  "objectDetail.php?refreshChecklistDefinitionLines=true", dojo
                      .byId('objectClass').value
                      + '_checklistDefinitionLine', 'listForm');
            }
            if (dojo.byId(dojo.byId('objectClass').value + '_jobDefinition')) {
              loadContent(
                  "objectDetail.php?refreshJobDefinition=true", dojo
                      .byId('objectClass').value
                      + '_jobDefinition', 'listForm');
            }
          }
        }
      } else {
        if (!noHideWait) {
          hideWait();
        }
      }
      // Manage checkList button
      if (dojo.byId('buttonCheckListVisible')
          && dojo.byId('buttonCheckListVisibleObject')) {
        var visible = dojo.byId('buttonCheckListVisible').value;
        var visibleObj = dojo.byId('buttonCheckListVisibleObject').value;
        // loadContent('objectButtons.php', 'buttonDivContainer','listForm');
        if (visible != 'never' && visible != visibleObj) {
          // loadContent('objectButtons.php', 'buttonDivContainer','listForm');
          if (visibleObj == 'visible') {
            dojo.byId("checkListButtonDiv").style.display = "inline";
          } else {
            dojo.byId("checkListButtonDiv").style.display = "none";
          }
          dojo.byId('buttonCheckListVisible').value = visibleObj;
        }
      }
      if (lastOperation.value == "insert" && dojo.byId("buttonHistoryVisible")
          && dojo.byId("buttonHistoryVisible").value == 'REQ') {
        dojo.byId("historyButtonDiv").style.display = "inline";
      }
      if (lastOperation.value == "delete" && dojo.byId("buttonHistoryVisible")) {
        dojo.byId("historyButtonDiv").style.display = "none";
      }
    }
    var classObj = null;
    if (dojo.byId('objectClass'))
      classObj = dojo.byId('objectClass');
    if (classObj && classObj.value == 'DocumentDirectory') {
      dijit.byId("documentDirectoryTree").model.store.clearOnClose = true;
      dijit.byId("documentDirectoryTree").model.store.close();
      // Completely delete every node from the dijit.Tree
      dijit.byId("documentDirectoryTree")._itemNodesMap = {};
      dijit.byId("documentDirectoryTree").rootNode.state = "UNCHECKED";
      dijit.byId("documentDirectoryTree").model.root.children = null;
      // Destroy the widget
      dijit.byId("documentDirectoryTree").rootNode.destroyRecursive();
      // Recreate the model, (with the model again)
      dijit.byId("documentDirectoryTree").model.constructor(dijit
          .byId("documentDirectoryTree").model);
      // Rebuild the tree
      dijit.byId("documentDirectoryTree").postMixInProperties();
      dijit.byId("documentDirectoryTree")._load();
    }
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
    if (classObj && (classObj.value == 'Notification' || classObj.value == 'NotificationDefinition')) {
      refreshNotificationTree(false);
    }
// END - ADD BY TABARY - NOTIFICATION SYSTEM
    if (dojo.byId("forceRefreshMenu")
        && dojo.byId("forceRefreshMenu").value != "") {
      forceRefreshMenu = dojo.byId("forceRefreshMenu").value;
    }
    if (forceRefreshMenu) {
      // loadContent("../view/menuTree.php", "mapDiv",null,false);
      // loadContent("../view/menuBar.php", "toolBarDiv",null,false);
      showWait();
      noDisconnect = true;
      quitConfirmed = true;
// MTY - LEAVE SYSTEM
      // forceRefreshMenu = 'Resource_xxx' when xxx = id of resource 
      // When in Ressource Screen and isEmployee is changed and leavesSystemActiv = YES and user is the modified ressource
      // ===> Force relead menu and return to the Resource Screen
      if (forceRefreshMenu.substr(0,8) == "Resource") {
            dojo.byId("directAccessPage").value = "objectMain.php";
            dojo.byId("menuActualStatus").value = menuActualStatus;
            dojo.byId("p1name").value = "Resource";
            dojo.byId("p1value").value = forceRefreshMenu.substr(9);
      } else {
            // When Leaves System Habilitations change 
            if (forceRefreshMenu=="leavesSystemHabilitation") {
                dojo.byId("directAccessPage").value = "leavesSystemHabilitation.php";
                dojo.byId("menuActualStatus").value = menuActualStatus;
                dojo.byId("p1name").value = "";
                dojo.byId("p1value").value = "";              
            } else {
// MTY - LEAVE SYSTEM        
      // window.location="../view/main.php?directAccessPage=parameter.php&menuActualStatus="
      // + menuActualStatus + "&p1name=type&p1value="+forceRefreshMenu;
      dojo.byId("directAccessPage").value = "parameter.php";
      dojo.byId("menuActualStatus").value = menuActualStatus;
      dojo.byId("p1name").value = "type";
      dojo.byId("p1value").value = forceRefreshMenu;
            }
      }
      forceRefreshMenu = "";
      dojo.byId("directAccessForm").submit();
    }
  } else if (lastOperationStatus.value == "INVALID"
      || lastOperationStatus.value == "CONFIRM") {
    if (formChangeInProgress) {
      formInitialize();
      formChanged();
    } else {
      formInitialize();
    }
  } else {
    if (dojo.byId('objectClass') && dojo.byId('objectId')) {
      var url = '../tool/getObjectCreationInfo.php?objectClass='+ dojo.byId('objectClass').value +'&objectId='+dojo.byId('objectId').value;
      loadDiv(url, 'buttonDivCreationInfo', null);
      var objClass=dojo.byId('objectClass').value;
      if (lastOperationStatus.value=='NO_CHANGE' && !validationType && dojo.byId(objClass+'PlanningElement_assignedCost') 
          && (dojo.byId(objClass+'PlanningElement_assignedCost').style.textDecoration=="line-through" || dojo.byId(objClass+'PlanningElement_leftCost').style.textDecoration=="line-through")) {
        // No change but assignment changed so that refresh is required
        loadContent("objectDetail.php?", "detailDiv", 'listForm');
        refreshGrid();
      }
    }
    if (validationType != 'note' && validationType != 'attachment') {
      formInitialize();
    }
    hideWait(); 
  }
  // If operation is correct (not an error) slowly fade the result message
  if (resultDivFadingOut) resultDivFadingOut.stop();
  if ((lastOperationStatus.value != "ERROR"
      && lastOperationStatus.value != "INVALID"
      && lastOperationStatus.value != "CONFIRM" && lastOperationStatus.value != "INCOMPLETE")) {
    contentNode.style.pointerEvents='none';
    resultDivFadingOut = dojo.fadeOut({
      node : contentNode,
      duration : 3000,
      onEnd : function() {
        contentNode.style.display = "none";
        contentWidget.set("content",null);
      }
    }).play();  
  } else {
    contentNode.style.pointerEvents='auto';
    if (lastOperationStatus.value == "ERROR") {
      showError(message);
      addCloseBoxToMessage(destination);
    } else {
      if (lastOperationStatus.value == "CONFIRM") {
        if (message.indexOf('id="confirmControl" value="delete"') > 0 || message.indexOf('id="confirmControl" type="hidden" value="delete"') > 0) {
          confirm = function() {
            dojo.byId("deleteButton").blur();
            loadContent("../tool/deleteObject.php?confirmed=true", "resultDivMain",
                'objectForm', true);
          };
        } else {
          confirm = function() {
            dojo.byId("saveButton").blur();
            loadContent("../tool/saveObject.php?confirmed=true", "resultDivMain",
                'objectForm', true);
          };
        }
        showConfirm(message, confirm);
        contentWidget = dijit.byId(destination);
        contentNode = dojo.byId(destination);
        contentNode.style.display = "none";
        contentWidget.set('content',null);
      } else {
        // showAlert(message);
        addCloseBoxToMessage(destination);
      }
    }
    hideWait();
  }
  if (dojo.byId('needProjectListRefresh')
      && dojo.byId('needProjectListRefresh').value == 'true') {
    needProjectListRefresh = true;
  }
  if (needProjectListRefresh) {
    refreshProjectSelectorList();
  }
  forceRefreshCreationInfo = false;
}
function displayMessageInResultDiv(message,type,fade,showCloseBox) {
  if (!type) type='WARNING';
  contentNode = dojo.byId('resultDivMain');
  contentNode.innerHTML = '<div class="message'+type+'" >'+message+'</div>';
  contentNode.style.display='block';
  //addMessage(message);
  dojo.fadeIn({
    node : contentNode,
    duration : 10,
    onEnd : function() {
      if (showCloseBox) {
        addCloseBoxToMessage('resultDivMain');
      }
      if (fade) {
        if (resultDivFadingOut) resultDivFadingOut.stop();
        resultDivFadingOut=dojo.fadeOut({
          node : contentNode,
          duration : 5000,
          onEnd : function() {
            dojo.byId('resultDivMain').style.display='none';
          }
        }).play();
      }
    }
  }).play();
}
function addCloseBoxToMessage(destination) {
  contentWidget = dijit.byId(destination);
  var closeBox = '<div class="closeBoxIcon" onClick="clickCloseBoxOnMessage('
      + "'" + destination + "'" + ');">&nbsp;</div>';
  contentWidget.set("content", closeBox + contentWidget.get("content"));
}
var clickCloseBoxOnMessageAction = null;
function clickCloseBoxOnMessage(destination) {
  contentWidget = dijit.byId(destination);
  contentNode = dojo.byId(destination);
  if (!contentNode) return;
  if (contentNode.style.display=="none") return;
  dojo.fadeOut({
    node : contentNode,
    duration : 500,
    onEnd : function() {
      // contentWidget.set("content","");
      contentNode.style.display = "none";
      if (clickCloseBoxOnMessageAction != null) {
        clickCloseBoxOnMessageAction();
      }
      clickCloseBoxOnMessageAction = null;
    }
  }).play();
}
/**
 * ============================================================================
 * Operates locking, hide and show correct buttons after loadContent, when
 * destination is detailDiv
 * @param specificWidgetArray : array or null
 *                              List of specific widget to enable
 * @return void
 */
// CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
function finaliseButtonDisplay(specificWidgetArray) {
// Old    
//function finaliseButtonDisplay() {
// END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET

// ADD BY Marc TABARY - 2017-03-06 -  - ALLOW DISABLED SPECIFIC WIDGET
  if (specificWidgetArray!==undefined) {
        // This parameter must be an array
        if (specificWidgetArray instanceof Array) {
            for (i = 0; i < specificWidgetArray.length; i++) {
               enableWidget(specificWidgetArray[i]);
            }
        }
  }
// END ADD BY Marc TABARY - 2017-03-06 -  - ALLOW DISABLED SPECIFIC WIDGET

  id = dojo.byId("id");
  if (id) {
    if (id.value == "") {
      // id exists but is not set => new item, all buttons locked until first
      // change
      formLock();
      enableWidget('newButton');
      enableWidget('newButtonList');
      enableWidget('saveButton');
      disableWidget('undoButton');
      disableWidget('mailButton');
      disableWidget('changeStatusButton');
      disableWidget('subscribeButton');
      if (dijit.byId("objectGrid")) {
        enableWidget('multiUpdateButton');
      } else {
        disableWidget('multiUpdateButton');
        disableWidget('indentDecreaseButton');
        disableWidget('indentIncreaseButton');
      }
      dojo.query(".pluginButton").forEach(function(node, index, nodelist) {
        disableWidget(node.getAttribute('widgetid'));
      });
    }
  } else {
    // id does not exist => not selected, only new button possible
    formLock();
    enableWidget('newButton');
    enableWidget('newButtonList');
    disableWidget('changeStatusButton');
    disableWidget('subscribeButton');
    if (dijit.byId("objectGrid")) {
      enableWidget('multiUpdateButton');
    } else {
      disableWidget('multiUpdateButton');
    }
    // but show print buttons if not in objectDetail (buttonDiv exists)
    if (!dojo.byId("buttonDiv")) {
      enableWidget('printButton');
      enableWidget('printButtonPdf');
    }
    if (dojo.byId('objectClass') && dojo.byId('objectClass').value=='Work') {
      enableWidget('refreshButton');
    }
    dojo.query(".pluginButton").forEach(function(node, index, nodelist) {
      disableWidget(node.getAttribute('widgetid'));
    });
  }
  buttonRightLock();
}

function finalizeMultipleSave() {
  // refreshGrid();
  var grid = dijit.byId("objectGrid");
  if (grid) {
    // unselectAllRows("objectGrid");
    var sortIndex = grid.getSortIndex();
    var sortAsc = grid.getSortAsc();
    var scrollTop = grid.scrollTop;
    store = grid.store;
    store.close();
    store
        .fetch({
          onComplete : function(items) {
            grid._refresh();
            setTimeout('dijit.byId("objectGrid").setSortIndex(' + sortIndex
                + ',' + sortAsc + ');', 10);
            setTimeout('dijit.byId("objectGrid").scrollTo(' + scrollTop + ');',
                20);
            selection = ';' + dojo.byId('selection').value;
            dojo.forEach(items, function(item, index) {
              if (selection.indexOf(";" + parseInt(item.id) + ";") >= 0) {
                grid.selection.setSelected(index, true);
              } else {
                grid.selection.setSelected(index, false);
              }
            })
          }
        });
  }
  if (dojo.byId('summaryResult')) {
    contentNode = dojo.byId('resultDivMain');
    contentNode.innerHTML = dojo.byId('summaryResult').value;
    contentNode.style.display='block';
    msg = dojo.byId('summaryResult').value;
    msg = msg.replace(" class='messageERROR' ", "");
    msg = msg.replace(" class='messageOK' ", "");
    msg = msg.replace(" class='messageWARNING' ", "");
    msg = msg.replace(" class='messageNO_CHANGE' ", "");
    msg = msg.replace("</div><div>", ", ");
    msg = msg.replace("</div><div>", ", ");
    msg = msg.replace("<div>", "");
    msg = msg.replace("<div>", "");
    msg = msg.replace("</div>", "");
    msg = msg.replace("</div>", "");
    addMessage(msg);
    dojo.fadeIn({
      node : contentNode,
      duration : 10,
      onEnd : function() {
        if (resultDivFadingOut) resultDivFadingOut.stop();
        resultDivFadingOut=dojo.fadeOut({
          node : contentNode,
          duration : 5000,
          onEnd : function() {
            dojo.byId('resultDivMain').style.display='none';
          }
        }).play();
      }
    }).play();
  }
  hideWait();
}
/**
 * ============================================================================
 * Operates locking, hide and show correct buttons when a change is done on form
 * to be able to validate changes, and avoid actions that may lead to loose
 * change
// ADD BY Marc TABARY - 2017-03-06 -  - ALLOW DISABLED SPECIFIC WIDGET
 * @param specificWidgetArray : Array of specific widget to disabled  
// END ADD BY Marc TABARY - 2017-03-06 -  - ALLOW DISABLED SPECIFIC WIDGET
 * @return void
 */
// CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
function formChanged(specificWidgetArray) {
// Old    
//function formChanged() {
// END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
  var updateRight = dojo.byId('updateRight');
  if (updateRight && updateRight.value == 'NO') {
    return;
  }
  disableWidget('newButton');
  disableWidget('newButtonList');
  enableWidget('saveButton');
  disableWidget('printButton');
  disableWidget('printButtonPdf');
  disableWidget('copyButton');
  enableWidget('undoButton');
  showWidget('undoButton');
  disableWidget('deleteButton');
  disableWidget('refreshButton');
  hideWidget('refreshButton');
  disableWidget('mailButton');
  disableWidget('multiUpdateButton');
  disableWidget('indentDecreaseButton');
  disableWidget('indentIncreaseButton');
  formChangeInProgress = true;
  grid = dijit.byId("objectGrid");
  if (grid) {
    // saveSelection=grid.selection;
    grid.selectionMode = "none";

  }
  buttonRightLock();

// ADD BY Marc TABARY - 2017-03-06 -  - ALLOW DISABLED SPECIFIC WIDGET
    if (specificWidgetArray!==undefined) {
        // This parameter must be an array
        if (specificWidgetArray instanceof Array) {
            for (i = 0; i < specificWidgetArray.length; i++) {
               if (dijit.byId(specificWidgetArray[i])) {               // Widget
                   disableWidget(specificWidgetArray[i]);
               } else if(specificWidgetArray[i].indexOf('_spe_')!=-1) { // Specific attributes '_spe_'
                   // Search the id DOM
                   var theIdName = 'id_'+specificWidgetArray[i].replace('_spe_','');
                   var theId = document.getElementById(theIdName);
                   if (theId!==null) {
                       theIdName = theIdName.toLowerCase();
                       if(theIdName.indexOf('button')!=-1) {  // Button => Hide
                           theId.style.visibility = "hidden";
                       } else {                                         // Else, readonly
                           theId.readOnly=true;
                           theId.class +=' "readOnly"';
}
                   }
               }
            }
        }
    }
// END ADD BY Marc TABARY - 2017-03-06 -  - ALLOW DISABLED SPECIFIC WIDGET
    dojo.query(".pluginButton").forEach(function(node, index, nodelist) {
      disableWidget(node.getAttribute('widgetid'));
    });
}

/**
 * ============================================================================
 * Operates unlocking, hide and show correct buttons when a form is refreshed to
 * be able to operate actions only available on forms with no change ongoing,
 * and avoid actions that may lead to unconsistancy
 * @param specificWidgetArray : Array of specific widget to disabled  
 * @return void
 */
// CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
function formInitialize(specificWidgetArray) {
// Old    
//function formInitialize() {
// END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET

// ADD BY Marc TABARY - 2017-03-06 -  - ALLOW DISABLED SPECIFIC WIDGET
    if (specificWidgetArray!==undefined) {
        // This parameter must be an array
        if (specificWidgetArray instanceof Array) {
            for (i = 0; i < specificWidgetArray.length; i++) {
               enableWidget(specificWidgetArray[i]);
            }
        }
    }
// END ADD BY Marc TABARY - 2017-03-06 -  - ALLOW DISABLED SPECIFIC WIDGET
  enableWidget('newButton');
  enableWidget('newButtonList');
  enableWidget('saveButton');
  enableWidget('printButton');
  enableWidget('printButtonPdf');
  disableWidget('undoButton');
  hideWidget('undoButton');
// MTY - LEAVE SYSTEM
  // Can't delete or copy certains elements of leave system
  if ( isLeaveMngConditionsKO()) {
    disableWidget('copyButton');
    disableWidget('deleteButton');      
  } else {
    enableWidget('copyButton');
  enableWidget('deleteButton');
  }
// MTY - LEAVE SYSTEM  
  enableWidget('refreshButton');
  showWidget('refreshButton');
  enableWidget('mailButton');
  if ( (dojo.byId("id") && dojo.byId("id").value != "") || (dojo.byId("lastSaveId") && dojo.byId("lastSaveId")!= "") ) {
    enableWidget('changeStatusButton');
    enableWidget('subscribeButton');
  } else {
    disableWidget('changeStatusButton');
    disableWidget('subscribeButton');
  }
  if (dijit.byId("objectGrid")) {
    enableWidget('multiUpdateButton');
  } else {
    disableWidget('multiUpdateButton');
    enableWidget('indentDecreaseButton');
    enableWidget('indentIncreaseButton');
  }
  dojo.query(".pluginButton").forEach(function(node, index, nodelist) {
    enableWidget(node.getAttribute('widgetid'));
  });
  formChangeInProgress = false;
  buttonRightLock();
}

/**
 * ============================================================================
 * Operates locking, to disable all actions during form submition
 * 
 * @return void
 */
function formLock() {
  disableWidget('newButton');
  disableWidget('newButtonList');
  disableWidget('saveButton');
  disableWidget('printButton');
  disableWidget('printButtonPdf');
  disableWidget('copyButton');
  disableWidget('undoButton');
  hideWidget('undoButton');
  disableWidget('deleteButton');
  disableWidget('refreshButton');
  showWidget('refreshButton');
  disableWidget('mailButton');
  disableWidget('multiUpdateButton');
  disableWidget('indentDecreaseButton');
  disableWidget('changeStatusButton');
  disableWidget('subscribeButton');
  dojo.query(".pluginButton").forEach(function(node, index, nodelist) {
    disableWidget(node.getAttribute('widgetid'));
  });
}

/**
 * ============================================================================
 * Lock some buttons depending on access rights
 */
function buttonRightLock() {
  var createRight = dojo.byId('createRight');
  var updateRight = dojo.byId('updateRight');
  var deleteRight = dojo.byId('deleteRight');
  if (createRight) {
    if (createRight.value != 'YES') {
      disableWidget('newButton');
      disableWidget('newButtonList');
      disableWidget('copyButton');
    }
  }
  if (updateRight) {
    if (updateRight.value != 'YES') {
      disableWidget('saveButton');
      disableWidget('undoButton');
      disableWidget('multiUpdateButton');
      disableWidget('indentDecreaseButton');
      disableWidget('indentIncreaseButton');
      disableWidget('changeStatusButton');
      disableWidget('subscribeButton');
      dojo.query(".pluginButton").forEach(function(node, index, nodelist) {
        disableWidget(node.getAttribute('widgetid'));
      });
    }
  }
  if (deleteRight) {
    if (deleteRight.value != 'YES') {
      disableWidget('deleteButton');
    }
  }
}
function buttonRightRefresh() {
  var createRight = dojo.byId('createRight');
  var updateRight = dojo.byId('updateRight');
  var deleteRight = dojo.byId('deleteRight');
  var newCreateRight = dojo.byId('createRightAfterSave');
  var newUpdateRight = dojo.byId('updateRightAfterSave');
  var newDeleteRight = dojo.byId('deleteRightAfterSave');
  if (createRight && newCreateRight && newCreateRight.value!=createRight.value) createRight.value=newCreateRight.value;
  if (updateRight && newUpdateRight && newUpdateRight.value!=updateRight.value) updateRight.value=newUpdateRight.value;
  if (deleteRight && newDeleteRight && newDeleteRight.value!=deleteRight.value) deleteRight.value=newDeleteRight.value;
}
/**
 * ============================================================================
 * Disable a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function disableWidget(widgetName) {
  if (dijit.byId(widgetName)) {
    dijit.byId(widgetName).set('disabled', true);
  }
}

/**
 * ============================================================================
 * Enable a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function enableWidget(widgetName) {
  if (dijit.byId(widgetName)) {
    dijit.byId(widgetName).set('disabled', false);
  }
}

/**
 * ============================================================================
 * Hide a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function hideWidget(widgetName) {
  if (dojo.byId(widgetName)) {
    dojo.style(dijit.byId(widgetName).domNode, {
      display : 'none'
    });
  }
}
/**
 * ============================================================================
 * Show a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function showWidget(widgetName) {
  if (dojo.byId(widgetName)) {
    dojo.style(dijit.byId(widgetName).domNode, {
      display : 'inline-block'
    });
  }
}
/**
 * ============================================================================
 * Loack a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function lockWidget(widgetName) {
  if (dijit.byId(widgetName)) {
    dijit.byId(widgetName).set('readOnly', true);
  }
}

/**
 * ============================================================================
 * Unlock a widget, testing it exists before to avoid error
 * 
 * @return void
 */
function unlockWidget(widgetName) {
  if (dijit.byId(widgetName)) {
    dijit.byId(widgetName).set('readOnly', false);
  }
}

/**
 * ============================================================================
 * Check if change is possible : to avoid recursive change when computing data
 * from other changes
 * 
 * @return boolean indicating if change is allowed or not
 */
function testAllowedChange(val) {
  if (cancelRecursiveChange_OnGoingChange == true) {
    return false;
  } else {
    if (val == null) {
      return false;
    } else {
      cancelRecursiveChange_OnGoingChange = true;
      return true;
    }
  }
}

/**
 * ============================================================================
 * Checks that ongoing change is finished, so another change cxan be taken into
 * account so that testAllowedChange() can return true
 * 
 * @return void
 */
function terminateChange() {
  window.setTimeout("cancelRecursiveChange_OnGoingChange=false;", 100);
}

/**
 * ============================================================================
 * Check if a change is waiting for form submission to be able to avoid unwanted
 * actions leading to loose of data change
 * 
 * @return boolean indicating if change is in progress for the form
 */
function checkFormChangeInProgress(actionYes, actionNo) {
  if (waitingForReply) {
    showInfo(i18n("alertOngoingQuery"));
    return true;
  } else if (formChangeInProgress) {
    if (multiSelection) {
      endMultipleUpdateMode();
      return false;
    }
    if (actionYes) {
      if (!actionNo) {
        actionNo = function() {
        };
      }
      showQuestion(i18n("confirmChangeLoosing"), actionYes, actionNo);
    } else {
      showAlert(i18n("alertOngoingChange"));
    }
    return true;
  } else {
    if (actionYes) {
    	actionYes();
    }
    return false;
  }
}

/**
 * ============================================================================
 * Unselect all the lines of the grid
 * 
 * @param gridName
 *          the name of the grid
 * @return void
 */
function unselectAllRows(gridName) {
  grid = dijit.byId(gridName); // if the element is not a widget, exit.
  if (!grid) {
    return;
  }
  grid.store.fetch({
    onComplete : function(items) {
      dojo.forEach(items, function(item, index) {
        grid.selection.setSelected(index, false);
      });
    }
  });
}

function selectAllRows(gridName) {
  grid = dijit.byId(gridName); // if the element is not a widget, exit.
  if (!grid) {
    return;
  }
  grid.store.fetch({
    onComplete : function(items) {
      dojo.forEach(items, function(item, index) {
        grid.selection.setSelected(index, true);
      });
    }
  });
}

function countSelectedItem(gridName,selectedName) {
  grid = dijit.byId(gridName); // if the element is not a widget, exit.
  if (!grid || ! dojo.byId(selectedName)) {
    return;
  }
  dojo.byId(selectedName).value=0;
  var lstStore=new Array();
  grid.store.fetch({
    onComplete : function(items) {
      dojo.forEach(items, function(item, index) {
        lstStore[item.id]=item.id;
      });
      var items=grid.selection.getSelected();
      if (items.length) {
        dojo.forEach(items, function(selectedItem) {
          if (selectedItem !== null) {
            if (lstStore.indexOf(selectedItem.id)=== -1) {
              grid.selection.setSelected(selectedItem.id, false);
            }
          }
        });
      }
      dojo.byId(selectedName).value=grid.selection.getSelectedCount();
    }
  });
}
/**
 * ============================================================================
 * Select a given line of the grid, corresponding to the given id
 * 
 * @param gridName
 *          the name of the grid
 * @param id
 *          the searched id
 * @return void
 */
var gridReposition = false;
function selectRowById(gridName, id, tryCount) {
  if (!tryCount)
    tryCount = 0;
  var grid = dijit.byId(gridName); // if the element is not a widget, exit.
  if (!grid || !id) {
    return;
  }
  unselectAllRows(gridName); // first unselect, to be sure to select only 1
                              // line
  // De-activate this function for IE8 : grid.getItem does not work
  if (dojo.isIE && parseInt(dojo.isIE, 10) <= '8') {
    return;
  }
  if (dojo.byId('objectClassList') && dojo.byId('objectClassList').value=='GlobalView' && dojo.byId('objectClass')) {
    id=dojo.byId('objectClass').value+id;
  }
  var nbRow = grid.rowCount;
  gridReposition = true;
  var j = -1;
  dojo.forEach(grid.store._getItemsArray(), function(item, i) {
    if (item && item.id == id) {
      var j = grid.getItemIndex(item); // if item is in the page, will find
                                        // quickly
      if (j == -1) { // not found : must search
        if (grid.getSortIndex() == -1) { // No sort so order in grid is same as
                                          // order in store
          grid.selection.setSelected(i, true);
        } else {
          tryCount++;
          if (tryCount <= 3) {
            setTimeout("selectRowById('" + gridName + "', " + id + ","
                + tryCount + ");", 100);
          } else {
            var indexLength = grid._by_idx.length;
            var element = null;
            for (var x = 0; x < indexLength; x++) {
              element = grid._by_idx[x];
              if (!element) continue;
              if (parseInt(element.item.id) == id) {
                grid.selection.setSelected(x, true);
                break;
              }
            }
          }
          /*
           * if (1 || j==-1) { for (var i=0;i<nbRow;i++) { var
           * item=grid.getItem(i); if (parseInt(item.id)==id) {
           * grid.selection.setSelected(i,true); } } }
           */
        }
      } else {
        grid.selection.setSelected(j, true);
      }
      // first=grid.scroller.firstVisibleRow; // Remove the scroll : will be a
      // mess when dealing with many items and order of item changes
      // last=grid.scroller.lastVisibleRow;
      // if (j<first || j>last) //grid.scrollToRow(j);
      gridReposition = false;
      return;
    }
  });
  gridReposition = false;
}
function selectPlanningRow() {
  setTimeout("selectPlanningLine(dojo.byId('objectClass').value,dojo.byId('objectId').value);",1);
}
function selectGridRow() {
  setTimeout("selectRowById('objectGrid',dojo.byId('objectId').value);", 100);
}

/**
 * ============================================================================
 * i18n (internationalization) function to return all messages and caption in
 * the language corresponding to the locale File lang.js must exist in directory
 * tool/i18n/nls/xx (xx as locale) otherwise default is uses (english) (similar
 * function exists in php, using same resource)
 * 
 * @param str
 *          the code of the string message
 * @param vars
 *          an array of parameters to replace in the message. They appear as
 *          ${n}.
 * @return the formated message, in the correct language
 */
function i18n(str, vars) {
  if (!i18nMessages) {
    try {
      // dojo.registerModulePath('i18n', '/tool/i18n');
      dojo.requireLocalization("i18n", "lang", currentLocale);
      i18nMessages = dojo.i18n.getLocalization("i18n", "lang", currentLocale);
    } catch (err) {
      i18nMessages = new Array();
    }
    if (customMessageExists) {
      try {
        // dojo.registerModulePath('i18n', '/tool/i18n');
        dojo.requireLocalization("i18nCustom", "lang", currentLocale);
        i18nMessagesCustom = dojo.i18n.getLocalization("i18nCustom", "lang",
            currentLocale);
      } catch (err) {
        i18nMessagesCustom = new Array();
      }
    } else {
      i18nMessagesCustom = new Array();
    }
  }
  var ret = null;
  if (window.top.i18nMessagesCustom[str]) {
    ret = window.top.i18nMessagesCustom[str];
  } else if (window.top.i18nMessages[str]) {
    ret = window.top.i18nMessages[str];
  } else if (window.top.i18nPluginArray && window.top.i18nPluginArray[str]) {
    ret = window.top.i18nPluginArray[str];
  }
  if (ret) {
    if (vars) {
      for (i = 0; i < vars.length; i++) {
        rep = '${' + (parseInt(i, 10) + 1) + '}';
        pos = ret.indexOf(rep);
        if (pos >= 0) {
          ret = ret.substring(0, pos) + vars[i]
              + ret.substring(pos + rep.length);
          pos = ret.indexOf(rep);
        }
      }
    }
    return ret;
  } else {
    return "[" + str + "]";
  }
}

/**
 * ============================================================================
 * set the selected project (transmit it to session)
 * 
 * @param idProject
 *          the id of the selected project
 * @param nameProject
 *          the name of the selected project
 * @param selectionField
 *          the name of the field where selection is executed
 * @return void
 */
function setSelectedProject(idProject, nameProject, selectionField,resetPrevious) {
	var isChecked = dijit.byId('onlyCheckedProject').get('checked');
	if(isChecked == true){
		showSelectedProject(false);
		changedIdProjectPlan(idProject);
		showSelectedProject(true);
	}
	if(idProject != '*'){
		var pos = idProject.indexOf('_');
		if(pos != -1){
			idProject = idProject.split('_');
			idProject = idProject.flat();
		}
		if(Array.isArray(idProject)){
			arraySelectedProject.forEach(function(element){
				if(dijit.byId('checkBoxProj'+element)){
					dijit.byId('checkBoxProj'+element).set('checked', false);
				}
			});
			arraySelectedProject.splice(0);
			idProject.forEach(function(element){
				if(dijit.byId('checkBoxProj'+element)){
					dijit.byId('checkBoxProj'+element).set('checked', true);
					arraySelectedProject.push(element);
				}
			});
		}else{
			dojo.query(".projectSelectorCheckbox").forEach(function(node, index, nodelist) {
			    if(dijit.byId(node.getAttribute('widgetid')).get('checked')){
			    	dijit.byId(node.getAttribute('widgetid')).set('checked', false);
			    }
			  });
			if (dijit.byId('checkBoxProj'+idProject)) dijit.byId('checkBoxProj'+idProject).set('checked', true);
			arraySelectedProject.splice(0);
		}
	} else {
		dojo.query(".projectSelectorCheckbox").forEach(function(node, index, nodelist) {
		    if(dijit.byId(node.getAttribute('widgetid')).get('checked')){
		    	dijit.byId(node.getAttribute('widgetid')).set('checked', false);
		    }
		  });
		arraySelectedProject.splice(0);
	}
  if (selectionField) {
    dijit.byId(selectionField).set(
        "label",
        '<div style="width:220px; overflow: hidden;text-align: left;" >'
            + nameProject + '</div>');
  }
  if (resetPrevious) {
    previousSelectedProject=null;
    previousSelectedProjectName=null;  
  }
  currentSelectedProject = idProject;
  if (idProject != "") {
    var callBack = function(){
        addMessage(i18n("Project") + "=" + nameProject);
        if (dojo.byId("GanttChartDIV")) {
          if (dojo.byId("resourcePlanning")) {
            loadContent("resourcePlanningList.php", "listDiv", 'listForm');
          } else if (dojo.byId("portfolioPlanning")) {
            loadContent("portfolioPlanningList.php", "listDiv", 'listForm');
          } else if (dojo.byId("globalPlanning")) {
            loadContent("globalPlanningList.php", "listDiv", 'listForm');
          } else {
            loadContent("planningList.php", "listDiv", 'listForm');
          }
        } else if (dijit.byId("listForm") && dojo.byId('objectClassList') && dojo.byId('listShowIdle')) {
          refreshJsonList(dojo.byId('objectClassList').value);
        } else if (dijit.byId("listForm") && dojo.byId('objectClass') && dojo.byId('listShowIdle')) {
          refreshJsonList(dojo.byId('objectClass').value);
        } else if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value == 'Today') {
          loadContent("../view/today.php", "centerDiv");
        } else if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value == 'Kanban') {
          loadContent("../view/kanbanViewMain.php", "centerDiv");        
        } else if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value == 'ActivityStream') {
          loadContent("../view/activityStreamList.php", "activityStreamListDiv", "activityStreamForm");      
        } else if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value == 'DashboardTicket') {
          loadContent("../view/dashboardTicketMain.php", "centerDiv");
        } else if (dojo.byId('currentPhpPage') && dojo.byId('currentPhpPage').value) {
          loadContent("../view/dashboardTicketMain.php", "centerDiv");
        } else if (currentPluginPage) {
          loadContent(currentPluginPage, "centerDiv");
        }
        if (dijit.byId('imputationButtonDiv') && dijit.byId('limitResByProj') && dijit.byId('limitResByProj').get('value')=="on"){
          refreshList('imputationResource', null, null, dijit.byId('userName').get('value'), 'userName', true); 
        }
        saveDataToSession('projectSelected',idProject,true);
    };
    saveDataToSession('project', idProject, null, callBack);
  }
  if (idProject != "" && dijit.byId("idProjectPlan")) {
    if (idProject == "*" ) dijit.byId("idProjectPlan").set("value", 0);
    else dijit.byId("idProjectPlan").set("value", idProject);
  }
  if (selectionField) {
    dijit.byId(selectionField).closeDropDown();
  }
  loadContent('../view/shortcut.php', "projectLinkDiv", null, null, null, null,
      true);
}

/**
 * Ends current user session
 * 
 * @return
 */
function disconnect(cleanCookieHash) {
  disconnectFunction = function() {
    quitConfirmed = true;
//    if(switchedMode==true){
//      saveDataToSession("paramScreen",'switch');
//    }
    //extUrl="";
    extUrl="origin==disconnect";
    if (cleanCookieHash) {
      extUrl += "&cleanCookieHash=true";
    }
    //#2887
    var callBack = function(){
      showWait();
      saveDataToSession("avoidSSOAuth",true);
      setTimeout('window.location = "../index.php"',100);
    }
    saveDataToSession("disconnect", extUrl, null, callBack);
  };
  if (!checkFormChangeInProgress()) {
    if (paramConfirmQuit != "NO") {
      showConfirm(i18n('confirmDisconnection'), disconnectFunction);
    } else {
      disconnectFunction();
    }
  }
}

//Disconnect when SSO is enabled
// targets are : 
// login : standard, get back to projeqtor login screen
// SSO : disconnect from SSO
// welcome : just quit projeqtor
function disconnectSSO(target,ssoCommonName) {
  if (!ssoCommonName) ssoCommonName='SSO';
  disconnectFunction = function() {
    quitConfirmed = true;
    extUrl="origin==disconnect&cleanCookieHash=true";
    //#2887
    if (target=='SSO') {
      setTimeout('window.location = "../sso/projeqtor/index.php?slo"',100);
    } else {
      var callBack = function(){
        showWait();
        if (target=='welcome') {
          setTimeout('window.location = "../view/welcome.php"',100);
        } else {
          saveDataToSession("avoidSSOAuth",true);
          setTimeout('window.location = "../index.php"',100);
        }
      }
      saveDataToSession("disconnect", extUrl, null, callBack);
    }
  };
  if (!checkFormChangeInProgress()) {
    if ( (paramConfirmQuit != "NO"  || target=='SSO') && target!='welcome') {
      var msg=i18n('confirmDisconnection');
      if (target=='SSO') msg=i18n('confirmDisconnectionSSO',new Array(ssoCommonName));
      showConfirm(msg, disconnectFunction);
    } else {
      disconnectFunction();
    }
  }
}
//Gautier #dataCloning
function disconnectDataCloning(target,dataCloningName) {
  if (!dataCloningName) dataCloningName='simu';
  disconnectFunction = function() {
    quitConfirmed = true;
    extUrl="origin==disconnect&cleanCookieHash=true";
      var callBack = function(){
        showWait();
        setTimeout('window.location = "../view/welcome.php"',100);
      }
      saveDataToSession("disconnect", extUrl, null, callBack);
  };
  if (!checkFormChangeInProgress()) {
    if ( paramConfirmQuit != "NO"  && target!='welcome') {
      var msg=i18n('confirmDisconnection');
      showConfirm(msg, disconnectFunction);
    } else {
      disconnectFunction();
    }
  }
}
/**
 * Disconnect (kill current session)
 * 
 * @return
 */

function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  } 
}
function quit() {
  if (!noDisconnect) {
    showWait();
    saveDataToSession('disconnect', '&origin==quit');
    if(dojo.isFF || dojo.isSafari){
      sleep(1000);
    }
    setTimeout("window.location='../index.php'", 100);
  }
}

/**
 * Before quitting, check for updates
 * 
 * @return
 */
function beforequit() {
  if (!quitConfirmed) {
    if (checkFormChangeInProgress()) {
      return (i18n("alertQuitOngoingChange"));
    } else {
      if (paramConfirmQuit != "NO") {
        return (i18n('confirmDisconnection'));
      }
    }
  }
  // return false;
}

/**
 * Draw a gantt chart using jsGantt
 * 
 * @return
 */
function drawGantt() {
  // first, if detail is displayed, reload class
  if (dojo.byId('objectClass') && !dojo.byId('objectClass').value
      && dojo.byId("objectClassName") && dojo.byId("objectClassName").value) {
    dojo.byId('objectClass').value = dojo.byId("objectClassName").value;
  }
  if (dojo.byId("objectId") && !dojo.byId("objectId").value && dijit.byId("id")
      && dijit.byId("id").get("value")) {
    dojo.byId("objectId").value = dijit.byId("id").get("value");
  }
  var startDateView = new Date();
  if (dijit.byId('startDatePlanView')) {
    startDateView = dijit.byId('startDatePlanView').get('value');
  }
  var endDateView = null;
  if (dijit.byId('endDatePlanView')) {
    endDateView = dijit.byId('endDatePlanView').get('value');
  }
  var showWBS = null;
  if (dijit.byId('showWBS')) {
    showWBS = dijit.byId('showWBS').get('checked');
  }
  // showWBS=true;
  var gFormat = "day";
  if (g) {
    gFormat = g.getFormat();
  }
  g = new JSGantt.GanttChart('g', dojo.byId('GanttChartDIV'), gFormat);
  setGanttVisibility(g);
  g.setCaptionType('Caption'); // Set to Show Caption
  // (None,Caption,Resource,Duration,Complete)
  // g.setShowStartDate(1); // Show/Hide Start Date(0/1)
  // g.setShowEndDate(1); // Show/Hide End Date(0/1)
  g.setDateInputFormat('yyyy-mm-dd'); // Set format of input dates
  // ('mm/dd/yyyy', 'dd/mm/yyyy',
  // 'yyyy-mm-dd')
  g.setDateDisplayFormat('default'); // Set format to display dates
  // ('mm/dd/yyyy', 'dd/mm/yyyy',
  // 'yyyy-mm-dd')
  g.setFormatArr("day", "week", "month", "quarter"); // Set format options (up
  if (dijit.byId('selectBaselineBottom')) {
    g.setBaseBottomName(dijit.byId('selectBaselineBottom').get('displayedValue'));
  }
  if (dijit.byId('selectBaselineTop')) {
    g.setBaseTopName(dijit.byId('selectBaselineTop').get('displayedValue'));
  }
  // to 4 :
  // "minute","hour","day","week","month","quarter")
  if (ganttPlanningScale) {
    g.setFormat(ganttPlanningScale,true);
  }
  g.setStartDateView(startDateView);
  g.setEndDateView(endDateView);
  if (dijit.byId('criticalPathPlanning')) g.setShowCriticalPath(dijit.byId('criticalPathPlanning').get('checked'));
  var contentNode = dojo.byId('gridContainerDiv');
  if (contentNode) {
    g.setWidth(dojo.style(contentNode, "width"));
  }
  jsonData = dojo.byId('planningJsonData');
  if (jsonData.innerHTML.indexOf('{"identifier"') < 0 || jsonData.innerHTML.indexOf('{"identifier":"id", "items":[ ] }')>=0) {
    if (dijit.byId('leftGanttChartDIV')) dijit.byId('leftGanttChartDIV').set('content',null);
    if (dijit.byId('rightGanttChartDIV')) dijit.byId('rightGanttChartDIV').set('content',null);
    if (dijit.byId('topGanttChartDIV')) dijit.byId('topGanttChartDIV').set('content',null);  
    if (jsonData.innerHTML.length > 10 && jsonData.innerHTML.indexOf('{"identifier":"id", "items":[ ] }')<0) {
      showAlert(jsonData.innerHTML);
    } else {
      dojo.byId("leftGanttChartDIV").innerHTML='<div class="labelMessageEmptyArea" style="top:42px;">'
        + i18n('ganttMsgLeftPart') + '</div>';
      dojo.byId("rightGanttChartDIV").innerHTML='<div class="labelMessageEmptyArea" style="top:0px;">'
        + i18n('ganttMsgRightPart') + '</div>';
    }
    hideWait();
    return;
  }
  var now = formatDate(new Date());
  // g.AddTaskItem(new JSGantt.TaskItem( 0, 'project', '', '', 'ff0000', '',
  // 0, '', '10', 1, '', 1, '' , 'test'));
  if (g && jsonData) {
    try {
      var store = eval('(' + jsonData.innerHTML + ')');
    } catch(e) {
      consoleTraceLog("ERROR Parsing jsonData in drawGantt()");
      consoleTraceLog(jsonData.innerHTML);
      return;
    }
    var items = store.items;
    // var arrayKeys=new Array();
    var keys = "";
    var currentResource=null;
    if(dojo.byId('portfolioPlanning')){
      for(var j=0;j <items.lenght; i++){
        var item = items[j];
        if(item.reftype == 'Milestone'){
          items[j-1]+=item;
        }
      }
    }
    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      // var topId=(i==0)?'':item.topid;
      var topId = item.topid;
      // pStart : start date of task
      var pStart = now;
      var pStartFraction = 0;
      pStart = (trim(item.initialstartdate) != "") ? item.initialstartdate
          : pStart;
      pStart = (trim(item.validatedstartdate) != "") ? item.validatedstartdate
          : pStart;
      pStart = (trim(item.plannedstartdate) != "") ? item.plannedstartdate
          : pStart;
      pStart = (trim(item.realstartdate) != "") ? item.realstartdate : pStart;
      pStart = (trim(item.plannedstartdate)!="" && trim(item.realstartdate) && item.plannedstartdate<item.realstartdate)?item.plannedstartdate:pStart;
      if (trim(item.plannedstartdate) != "" && trim(item.realenddate) == "") {
        pStartFraction = item.plannedstartfraction;
      }
      // If real work in the future, don't take it in account
      if (trim(item.plannedstartdate) && trim(item.realstartdate)
          && item.plannedstartdate < item.realstartdate
          && item.realstartdate > now) {
        pStart = item.plannedstartdate;
      }
      // pEnd : end date of task
      var pEnd = now;
      //var pEndFraction = 1;
      pEnd = (trim(item.initialenddate) != "") ? item.initialenddate : pEnd;
      pEnd = (trim(item.validatedenddate) != "") ? item.validatedenddate : pEnd;
      pEnd = (trim(item.plannedenddate) != "") ? item.plannedenddate : pEnd;
      
      pRealEnd = "";
      pPlannedStart = "";
      pWork = "";
      if (dojo.byId('resourcePlanning')) {
        pRealEnd = item.realenddate;
        pPlannedStart = item.plannedstartdate;
        pWork = item.leftworkdisplay;
        g.setSplitted(true);
      } else if(dojo.byId('contractGantt') && item.reftype == 'Milestone'){
        pEnd=item.realstartdate;
      }else {
        pEnd = (trim(item.realenddate) != "") ? item.realenddate : pEnd;
      }
      if (pEnd < pStart)
        pEnd = pStart;
      //
      var realWork = parseFloat(item.realwork);
      var plannedWork = parseFloat(item.plannedwork);
      var validatedWork = parseFloat(item.validatedwork);
      var progress = 0;
      if (item.isglobal && item.isglobal==1 && item.progress) { 
        progress=item.progress;
      } else {
        if (plannedWork > 0) {
          progress = Math.round(100 * realWork / plannedWork);
        } else {
          if (item.done == 1) {
            progress = 100;
          }
        }
      }
      // pGroup : is the task a group one ?
      var pGroup = (item.elementary == '0') ? 1 : 0;
      //MODIF qCazelles - GANTT
      if (item.reftype=='Project' || item.reftype=='Fixed' || item.reftype=='Replan' || item.reftype=='Construction' || item.reftype=='ProductVersionhasChild' || item.reftype=='ComponentVersionhasChild' || item.reftype=='SupplierContracthasChild' || item.reftype=='ClientContracthasChild' || item.reftype=='ActivityhasChild') pGroup=1;
     //END MODIF qCazelles - GANTT
      var pobjecttype='';
      var pHealthStatus='';
      var pQualityLevel='';
      var pTrend='';
      var pExtRessource='';
      var pDurationContract='';
      var pOverallProgress='';
      if(dojo.byId('contractGantt') &&  item.reftype!='Milestone'){
        pExtRessource=item.externalressource;
        pDurationContract=item.duration;
        pobjecttype=item.objecttype;
      }
      if(dojo.byId('portfolio')){
        pHealthStatus=item.health;
        pQualityLevel=item.quality;
        pTrend=item.trend;
        pOverallProgress=item.overallprogress
      }

      if(dojo.byId('versionsPlanning')){
        pobjecttype=item.objecttype;
      }
     
      // runScript : JavaScript to run when click on task (to display the
      // detail of the task)
      var runScript="";
      if(!(dojo.byId('contractGantt') && item.reftype=='Milestone')){
         runScript = "runScript('" + item.reftype + "','" + item.refid + "','"+ item.id + "');";
      }
      elementIdRef=" \' "+ item.reftype +" \',\' " + item.refid +"\',\'"+ item.id +" \' " ;
      if(!(dojo.byId('contractGantt'))){
        var contextMenu = "runScriptContextMenu('" + item.reftype + "','" + item.refid + "','"+ item.id + "');";
      }
      
      // display Name of the task
      var pName = ((showWBS) ? item.wbs : '') + " " + item.refname; // for
                                                                    // testeing
      // purpose, add
      // wbs code
      // var pName=item.refname;
      // display color of the task bar
      var pColor = (pGroup)?'003000':'50BB50'; // Default green
      if (! pGroup && item.notplannedwork > 0) { // Some left work not planned : purple
        pColor = '9933CC';
      } else if (trim(item.validatedenddate) != "" && item.validatedenddate < pEnd) { // Not respected constraints (end date) : red
        if (item.reftype!='Milestone' && ( ! item.assignedwork || item.assignedwork==0 ) && ( ! item.leftwork || item.leftwork==0 ) && ( ! item.realwork || item.realwork==0 )) {
          pColor = (pGroup)?'650000':'BB9099';
        } else {
          pColor = (pGroup)?'650000':'BB5050';
        }
      } else if (! pGroup && item.reftype!='Milestone' && ( ! item.assignedwork || item.assignedwork==0 ) && ( ! item.leftwork || item.leftwork==0 ) && ( ! item.realwork || item.realwork==0 ) ) { // No workassigned : greyed green
        pColor = 'AEC5AE';
      }
      if (item.surbooked==1) pColor='f4bf42';
      
      if (item.redElement == '1') {
        pColor = 'BB5050';
      }
      else if(item.redElement == '0') {
        pColor = '50BB50';
      }
      //gautier #3925
      if(trim(item.plannedenddate) != "" && item.done == 0){
        var today = (new Date()).toISOString().substr(0,10);
        var endDate = item.plannedenddate.substr(0,10);
        if( endDate < today){
          if(item.reftype=="Project"){
            pColor = '650000';
          }else{
            pColor = 'BB5050';
          }
        }
      }
      var pItemColor=item.color;
      // pMile : is it a milestone ?      
      var pMile = (item.reftype == 'Milestone') ? 1 : 0;
      if (pMile) {
        pStart = pEnd;
      }
      pClass = item.reftype;
      pId = item.refid;
      pScope = "Planning_" + pClass + "_" + pId;
      pOpen = (item.collapsed == '1') ? '0' : '1';
      var pResource = item.resource;
      var pCaption = "";
      if (dojo.byId('listShowResource')) {
        if (dojo.byId('listShowResource').checked) {
          pCaption = pResource;
        }
      }
      if (dojo.byId('showRessourceComponentVersion')) {
        if (dojo.byId('showRessourceComponentVersion').checked) {
          pCaption = pResource;
        }
      }
      
      if (dojo.byId('listShowLeftWork')
          && dojo.byId('listShowLeftWork').checked) {
        if (item.leftwork > 0) {
          pCaption = item.leftworkdisplay;
        } else {
          pCaption = "";
        }
      }
      var pDepend = item.depend;
      topKey = "#" + topId + "#";
      curKey = "#" + item.id + "#";
      if (keys.indexOf(topKey) == -1) {
        topId = '';
      }
      keys += "#" + curKey + "#";
      g.AddTaskItem(new JSGantt.TaskItem(item.id, pName, pStart, pEnd, pColor, pItemColor,
          runScript, contextMenu, pMile, pResource, progress, pGroup, 
          topId, pOpen, pDepend,
          pCaption, pClass, pScope, pRealEnd, pPlannedStart,
          item.validatedworkdisplay, item.assignedworkdisplay, item.realworkdisplay, item.leftworkdisplay, item.plannedworkdisplay,
          item.priority,item.idplanningmode, item.planningmode, 
          item.status,pHealthStatus,pQualityLevel,pTrend,pOverallProgress, item.type, 
          item.validatedcostdisplay, item.assignedcostdisplay, item.realcostdisplay, item.leftcostdisplay, item.plannedcostdisplay,
          item.baseTopStart, item.baseTopEnd, item.baseBottomStart, item.baseBottomEnd, 
          item.isoncriticalpath,pobjecttype,pExtRessource,pDurationContract,elementIdRef,item.fixplanning));
    }
    dojo.query(".inputDateGantBarResize").forEach(function(node, index, nodelist) {
      node.value='';
    });
    g.Draw();
    g.DrawDependencies();
  } else {
    // showAlert("Gantt chart not defined");
    return;
  }
  if (dojo.byId('leftGanttChartDIV').offsetWidth>dojo.byId('listHeaderDiv').offsetWidth-15) {
    var resizeWidth=dojo.byId('listHeaderDiv').offsetWidth-15;
    dijit.byId('leftGanttChartDIV').resize({w:resizeWidth});
    dijit.byId("centerDiv").resize(); 
  }
  highlightPlanningLine();
}

function runScript(refType, refId, id) {
  if (g) {  
    var vList=g.getList();
    if (vList) {
      var vTask=null;
      for(var i = 0; i < vList.length; i++) {
        if (vList[i].getID()==id) {
          vTask=vList[i];
          break;
        }
      }
      if (vTask && dojo.byId('resourcePlanningSelectedResource')) {
        dojo.byId('resourcePlanningSelectedResource').value=vTask.getResource();
      }
    }
  }
  if (refType == 'Fixed' || refType=='Construction' || refType=='Replan') {
    refType = 'Project';
  }
  //ADD by qCazelles - GANTT
  if (refType == 'ActivityhasChild') {
    refType = 'Activity';
  }
  if (refType == 'ProductVersionhasChild') {
    refType = 'ProductVersion';
  }
  if (refType == 'ComponentVersionhasChild') {
    refType = 'ComponentVersion';
  }
  if(refType=='SupplierContracthasChild'){
    refType = 'SupplierContract';
  }
  if(refType=='ClientContracthasChild'){
    refType = 'ClientContract';
  }
  //END ADD qCazelles - GANTT
  if (waitingForReply) {
    showInfo(i18n("alertOngoingQuery"));
    return;
  }
  if (checkFormChangeInProgress()) {
    return false;
  }
  dojo.byId('objectClass').value = refType;
  dojo.byId('objectId').value = refId;
  var ctrlPressed=(window.event && (window.event.ctrlKey || window.event.shiftKey))?true:false;
  if (ctrlPressed && refType && refId) {
    openInNewWindow(refType, refId);
    return;
  }
  hideList();
  loadContent('objectDetail.php?planning=true&planningType='
      + dojo.byId('objectClassManual').value, 'detailDiv', 'listForm');
  loadContentStream();
  highlightPlanningLine(id);
}
var ongoingRunScriptContextMenu=false;
function runScriptContextMenu(refType, refId, id) {
  if (ongoingRunScriptContextMenu) return;
  ongoingRunScriptContextMenu=true;
  var objectClassManual = dojo.byId('objectClassManual').value;
  showWait();
  setTimeout("document.body.style.cursor='default';",100);
  dojo.xhrGet({
    url : "../view/planningBarDetail.php?class="+refType+"&id="+refId+"&scale="+ganttPlanningScale+"&objectClassManual="+objectClassManual+"&idAssignment="+id,
    load : function(data, args) {
      //ongoingRunScriptContextMenu=true;
      setTimeout("document.body.style.cursor='default';",100);
      var bar = dojo.byId('bardiv_'+id);
      var line = dojo.byId('childgrid_'+id);
      var detail = dojo.byId('rightTableBarDetail');
      detail.style.display="block";
      detail.innerHTML=data;
      detail.style.width=(parseInt(bar.style.width)+202)+'px';
      detail.style.left=(bar.offsetLeft-1)+"px";
      var tableHeight=44;
      if (dojo.byId('planningBarDetailTable')) tableHeight=dojo.byId('planningBarDetailTable').offsetHeight
      if ( dojo.byId('rightTableContainer').offsetHeight + tableHeight > (dojo.byId('rightGanttChartDIV').offsetHeight) && (line.offsetTop+25)> dojo.byId('rightTableContainer').offsetHeight ) {
        detail.style.top=(line.offsetTop-tableHeight+1)+"px";  
      } else {
        detail.style.top=(line.offsetTop+22)+"px";
      }
      hideWait();
      setTimeout("ongoingRunScriptContextMenu=false;",20);
    },
    error : function () {
      console.warn ("error on return from planningBarDetail.php");
      hideWait();
      setTimeout("ongoingRunScriptContextMenu=false;",20);
    }
  });
  return false;
}
function highlightPlanningLine(id) {
  if (id == null)
    id = vGanttCurrentLine;
  if (id < 0)
    return;
  vGanttCurrentLine = id;
  vTaskList = g.getList();
  for (var i = 0; i < vTaskList.length; i++) {
    JSGantt.ganttMouseOut(i);
  }
  var vRowObj1 = JSGantt.findObj('child_' + id);
  if (vRowObj1) {
    // vRowObj1.className = "dojoxGridRowSelected dojoDndItem";// ganttTask" +
    // pType;
    dojo.addClass(vRowObj1, "dojoxGridRowSelected");
  }
  var vRowObj2 = JSGantt.findObj('childrow_' + id);
  if (vRowObj2) {
    // vRowObj2.className = "dojoxGridRowSelected";
    dojo.addClass(vRowObj2, "dojoxGridRowSelected");
  }
}
function selectPlanningLine(selClass, selId) {
  vGanttCurrentLine = id;
  vTaskList = g.getList();
  var tId = null;
  for (var i = 0; i < vTaskList.length; i++) {
    scope = vTaskList[i].getScope();
    spl = scope.split("_");
    if (spl.length > 2 && spl[1] == selClass && spl[2] == selId) {
      tId = vTaskList[i].getID();
    }
  }
  if (tId != null) {
    unselectPlanningLines();
    highlightPlanningLine(tId);
  }
}
function unselectPlanningLines() {
  dojo.query(".dojoxGridRowSelected").forEach(function(node, index, nodelist) {
    dojo.removeClass(node, "dojoxGridRowSelected");
  });
}
/**
 * calculate diffence (in work days) between dates
 */

function workDayDiffDates(paramStartDate, paramEndDate) {
  var currentDate = new Date();
  if (!isDate(paramStartDate))
    return '';
  if (!isDate(paramEndDate))
    return '';
  currentDate.setFullYear(paramStartDate.getFullYear(), paramStartDate.getMonth(), paramStartDate.getDate());
  currentDate.setHours(0,0,0,0);
  var endDate = new Date();
  endDate.setFullYear(paramEndDate.getFullYear(), paramEndDate.getMonth(), paramEndDate.getDate());
  endDate.setHours(0,0,0,0);
  if (endDate < currentDate) {
    return 0;
  }
  var duration = 0;
  if (isOffDay(currentDate) && currentDate.valueOf()!=endDate.valueOf()) duration++;
  while (currentDate <= endDate) {
    if (!isOffDay(currentDate) || currentDate.valueOf()==endDate.valueOf()) {
      duration++;
    }
    currentDate = addDaysToDate(currentDate, 1);
  }
  return duration;
}
/**
 * calculate diffence (in days) between dates
 */
function dayDiffDates(paramStartDate, paramEndDate) {
  var startDate = paramStartDate;
  var endDate = paramEndDate;
  var valDay = (24 * 60 * 60 * 1000);
  var duration = (endDate - startDate) / valDay;
  duration = Math.round(duration);
  return duration;
}

/**
 * Return the day of the week like php function : date("N",$valDate) Monday=1,
 * Tuesday=2, Wednesday=3, Thursday=4, Friday=5, Saturday=6, Sunday=7 (not 0 !)
 */
function getDay(valDate) {
  var day = valDate.getDay();
  day = (day == 0) ? 7 : day;
  return day;
}

/**
 * ============================================================================
 * Calculate new date after adding some days
 * 
 * @param paramDate
 *          start date
 * @param days
 *          numbers of days to add (can be < 0 to subtract days)
 * @return new calculated date
 */
function addDaysToDate(paramDate, paramDays) {
  var date = paramDate;
  var days = paramDays;
  var endDate = date;
  endDate.setDate(date.getDate() + days);
  return endDate;
}

/**
 * ============================================================================
 * Calculate new date after adding some work days, subtracting week-ends
 * 
 * @param $ate
 *          start date
 * @param days
 *          numbers of days to add (can be < 0 to subtract days)
 * @return new calculated date
 */
function addWorkDaysToDate_old(paramDate, paramDays) {
  var startDate = paramDate;
  var days = paramDays;
  if (days <= 0) {
    return startDate;
  }
  days -= 1;
  if (getDay(startDate) >= 6) {
    // startDate.setDate(startDate.getDate()+8-getDay(startDate));
  }
  var weekEnds = Math.floor(days / 5);
  var additionalDays = days - (5 * weekEnds);
  if (getDay(startDate) + additionalDays >= 6) {
    weekEnds += 1;
  }
  days += (2 * weekEnds);
  var endDate = startDate;
  endDate.setDate(startDate.getDate() + days);
  return endDate;
}

function addWorkDaysToDate(paramDate, paramDays) {
  endDate = paramDate;
  left = paramDays;
  left--;
  while (left > 0) {
    endDate = addDaysToDate(endDate, 1);
    if (!isOffDay(endDate)) {
      left--;
    }
  }
  return endDate;
}
/**
 * Check "all" checkboxes on workflow definition
 * 
 * @return
 */
function workflowSelectAll(line, column, profileList) {
  workflowChange(null, null, null);
  var reg = new RegExp("[ ]+", "g");
  var profileArray = profileList.split(reg);
  var check = dijit.byId('val_' + line + "_" + column);
  if (check) {
    var newValue = (check.get("checked")) ? 'checked' : '';
    for (i = 0; i < profileArray.length; i++) {
      var checkBox = dijit.byId('val_' + line + "_" + column + "_"
          + profileArray[i]);
      if (checkBox) {
        checkBox.set("checked", newValue);
      }
    }
  } else {
    var newValue = dojo.byId('val_' + line + "_" + column).checked;
    for (i = 0; i < profileArray.length; i++) {
      var checkBox = dojo.byId('val_' + line + "_" + column + "_"
          + profileArray[i]);
      if (checkBox) {
        checkBox.checked = newValue;
      }
    }
  }
}

/**
 * Flag a change on workflow definition
 * 
 * @return
 */
function workflowChange(line, column, profileList) {
  var change = dojo.byId('workflowUpdate');
  change.value = new Date();
  formChanged();
  if (line == null) {
    return;
  }
  var allChecked = true;
  var reg = new RegExp("[ ]+", "g");
  var profileArray = profileList.split(reg);
  var check = dijit.byId('val_' + line + "_" + column);
  if (check) {
    // var newValue=(check.get("checked"))? 'checked': '';
    for (i = 0; i < profileArray.length; i++) {
      var checkBox = dijit.byId('val_' + line + "_" + column + "_"
          + profileArray[i]);
      if (checkBox) {
        if (checkBox.get("checked") == 'false') {
          allChecked = false;
        }
      }
    }
    check.set('checked', (allChecked ? 'true' : 'false'));
  } else {
    // var newValue=dojo.byId('val_' + line + "_" + column).checked;
    for (i = 0; i < profileArray.length; i++) {
      var checkBox = dojo.byId('val_' + line + "_" + column + "_"
          + profileArray[i]);
      if (checkBox) {
        if (!checkBox.checked) {
          allChecked = false;
        }
      }
    }
    dojo.byId('val_' + line + "_" + column).checked = allChecked;
  }

}

function isDate(date) {
  if (!date)
    return false;
  if (date instanceof Date && !isNaN(date.valueOf()))
    return true;
  return false;
}
/**
 * refresh Projects List on Today screen
 */
function refreshTodayProjectsList(value) {
  if(value==null || value==undefined){
    value=dojo.byId('showAllProjectToday').value; 
  }
  if(value!=dojo.byId('showAllProjectToday').value){
    saveDataToSession('showAllProjectTodayVal',value,false);
  }
  loadContent("../view/today.php?refreshProjects=true+&showAllProjectToday="+value, "Today_project",
      "todayProjectsForm", false);
}

/**
 * refresh Projects List on Today screen
 */
function refreshTodayList(list,value) {
  if(value==null || value==undefined){
    value=dojo.byId('showAll'+list+'Today').value; 
  }
  if(value!=dojo.byId('showAll'+list+'Today').value){
    saveDataToSession('showAll'+list+'TodayVal',value,false);
  }
  loadContent("../view/today.php?refresh"+list+"=true+&showAll"+list+"Today="+value, "Today_"+(list=='Message'?list.toLowerCase():list),"today"+list+"Form", false);
}

//var newWin=null;
function openInNewWindow(eltClass, eltId) {
  var url="main.php?directAccess=true&objectClass="+eltClass+"&objectId="+eltId;
  var key=(window.event.ctrlKey)?'ctrl':((window.event.shiftKey)?'shift':'');
  var params=(key=='shift' && ! dojo.isChrome)?"scrollbars=yes":null;
  window.open(url,'_blank',params).focus;
}
function gotoElement(eltClass, eltId, noHistory, forceListRefresh, target, mustReplan) {
if (noHistory==undefined) noHistory=false;
if (forceListRefresh==undefined) forceListRefresh=false;
if (target==undefined) target='object';
  if (eltClass=='BudgetItem') eltClass='Budget'; 
  var ctrlPressed=(window.event && (window.event.ctrlKey || window.event.shiftKey))?true:false;
  if (ctrlPressed && eltClass && eltId) {
    openInNewWindow(eltClass, eltId);
    return;
  }
  if (checkFormChangeInProgress()) {
    return false;
  }
  if (eltClass == 'Project' || eltClass == 'Activity'
      || eltClass == 'Milestone' || eltClass == 'Meeting'
      || eltClass == 'TestSession') {
    if (dojo.byId("GanttChartDIV")) {
      target = 'planning';
      forceListRefresh = true;
    }
  }
  if (eltClass=='BudgetItem') eltClass='Budget'; 
  if(!isNewGui)selectTreeNodeById(dijit.byId('menuTree'), eltClass);
  formChangeInProgress = false;
  // if ( dojo.byId("GanttChartDIV")
  // && (eltClass=='Project' || eltClass=='Activity' || eltClass=='Milestone'
  // || eltClass=='TestSession' || eltClass=='Meeting' ||
  // eltClass=='PeriodicMeeting') ) {
  if (target == 'planning') {
    if (!dojo.byId("GanttChartDIV")) {
      vGanttCurrentLine = -1;
      cleanContent("centerDiv");
      var callback = function() {
        gotoElement(eltClass, eltId, noHistory, forceListRefresh, target);
      }
      loadContent("planningMain.php", "centerDiv", null, null, null, null,
          null, callback);
      return;
    }
    if (forceListRefresh) {
      if (mustReplan==null || mustReplan=='undefined') mustReplan=false;
      refreshGrid(mustReplan);
    }
    dojo.byId('objectClass').value = eltClass;
    dojo.byId('objectId').value = eltId;
    loadContent('objectDetail.php', 'detailDiv', 'listForm');
    loadContentStream();
  } else {
    if (dojo.byId("detailDiv")) {
      cleanContent("detailDiv");
    }
    if ( ( (!dojo.byId('objectClass') || dojo.byId('objectClass').value != eltClass) && (!dojo.byId('objectClassList') || dojo.byId('objectClassList').value != eltClass))
        || forceListRefresh || dojo.byId('titleKanban')) {
      var callBack=null;
      loadContent("objectMain.php?objectClass=" + eltClass, "centerDiv", false,
          false, false, eltId,false,callBack);
    } else {
      if (eltClass=='GlobalView') {
        var explode=eltId.split('|');
        dojo.byId('objectClass').value = explode[0];
        dojo.byId('objectId').value =  explode[1];
      } else {
        dojo.byId('objectClass').value = eltClass;
        dojo.byId('objectId').value = eltId;
      }
      loadContent('objectDetail.php', 'detailDiv', 'listForm');
      loadContentStream();
      hideList();
      var key=(eltClass=='GlobalView')?eltId:parseInt(eltId);
      setTimeout('selectRowById("objectGrid", ' + key + ');', 100);
    }
  }
  if (!noHistory) {
    stockHistory(eltClass, eltId);
  }
  selectIconMenuBar(eltClass);
  if(isNewGui){
    refreshSelectedMenuLeft('menu'+eltClass);
    refreshSelectedItem(eltClass, defaultMenu);
  }
}

function runReport() {
  var fileName = dojo.byId('reportFile').value;
  dijit.byId('listReportDiv').resize({h:250});
  dijit.byId('mainReportContainer').resize();
  loadContent("../report/" + fileName, "detailReportDiv", "reportForm", false);
}
function saveReportInToday() {
  var fileName = dojo.byId('reportFile').value;
  var form="reportForm";
  if(fileName=="showIntervention" && dojo.byId("consultationPlannedWorkManualParamDiv")){
    form="listFormConsPlannedWorkManual";
  }
    loadContent("../tool/saveReportInToday.php", "resultDivMain", form, true,
    'report');
}
function saveReportParametersForDialog() {
  var callback=function(){
	hideWait();
	showDialogAutoSendReport();
  };
  loadDiv("../tool/saveReportParametersForDialog.php", "resultDivMain", "reportForm", callback);
}	  
/**
 * Global save function through [CTRL)+s
 */
function globalSave() {
  if (dijit.byId('dialogDetail') && dijit.byId('dialogDetail').open) {
    var button = dijit.byId('comboSaveButton');
  } else if (dijit.byId('dialogNote') && dijit.byId('dialogNote').open) {
    var button = dijit.byId('dialogNoteSubmit');
  } else if (dijit.byId('dialogLine') && dijit.byId('dialogLine').open) {
    var button = dijit.byId('dialogLineSubmit');
  } else if (dijit.byId('dialogLink') && dijit.byId('dialogLink').open) {
    var button = dijit.byId('dialogLinkSubmit');
  } else if (dijit.byId('dialogOrigin') && dijit.byId('dialogOrigin').open) {
    var button = dijit.byId('dialogOriginSubmit');
  } else if (dijit.byId('dialogCopy') && dijit.byId('dialogCopy').open) {
    var button = dijit.byId('dialogCopySubmit');
    //gautier #2522
  } else if (dijit.byId('dialogCopyDocument') && dijit.byId('dialogCopyDocument').open) {
    var button = dijit.byId('dialogCopyDocumentSubmit');
  } else if (dijit.byId('dialogCopyProject')
      && dijit.byId('dialogCopyProject').open) {
    var button = dijit.byId('dialogProjectCopySubmit');
  } else if (dijit.byId('dialogAttachment')
      && dijit.byId('dialogAttachment').open) {
    var button = dijit.byId('dialogAttachmentSubmit');
  } else if (dijit.byId('dialogDocumentVersion')
      && dijit.byId('dialogDocumentVersion').open) {
    var button = dijit.byId('submitDocumentVersionUpload');
  } else if (dijit.byId('dialogAssignment')
      && dijit.byId('dialogAssignment').open) {
    var button = dijit.byId('dialogAssignmentSubmit');
  } else if (dijit.byId('dialogExpenseDetail')
      && dijit.byId('dialogExpenseDetail').open) {
    var button = dijit.byId('dialogExpenseDetailSubmit');
  } else if (dijit.byId('dialogPlan') && dijit.byId('dialogPlan').open) {
    var button = dijit.byId('dialogPlanSubmit');
  } else if (dijit.byId('dialogDependency')
      && dijit.byId('dialogDependency').open) {
    var button = dijit.byId('dialogDependencySubmit');
  } else if (dijit.byId('dialogResourceCost')
      && dijit.byId('dialogResourceCost').open) {
    var button = dijit.byId('dialogResourceCostSubmit');
  } else if (dijit.byId('dialogVersionProject')
      && dijit.byId('dialogVersionProject').open) {
    var button = dijit.byId('dialogVersionProjectSubmit');
  } else if (dijit.byId('dialogProductProject')
      && dijit.byId('dialogProductProject').open) {
    var button = dijit.byId('dialogProductProjectSubmit');
  } else if (dijit.byId('dialogAffectation')
      && dijit.byId('dialogAffectation').open) {
    var button = dijit.byId('dialogAffectationSubmit');
  } else if (dijit.byId('dialogFilter') && dijit.byId('dialogFilter').open) {
    var button = dijit.byId('dialogFilterSubmit');
  } else if (dijit.byId('dialogBillLine') && dijit.byId('dialogBillLine').open) {
    var button = dijit.byId('dialogBillLineSubmit');
  } else if (dijit.byId('dialogMail') && dijit.byId('dialogMail').open) {
    var button = dijit.byId('dialogMailSubmit');
  } else if (dijit.byId('dialogChecklistDefinitionLine')
      && dijit.byId('dialogChecklistDefinitionLine').open) {
    var button = dijit.byId('dialogChecklistDefinitionLineSubmit');
  } else if (dijit.byId('dialogChecklist')
      && dijit.byId('dialogChecklist').open) {
    var button = dijit.byId('dialogChecklistSubmit');
  } else if (dijit.byId('dialogJobDefinition')
      && dijit.byId('dialogJobDefinition').open) {
    var button = dijit.byId('dialogJobDefinitionSubmit');
  } else if (dijit.byId('dialogJob')
      && dijit.byId('dialogJob').open) {
    var button = dijit.byId('dialogJobSubmit');
  } else if (dijit.byId('dialogCreationInfo')
      && dijit.byId('dialogCreationInfo').open) {
    var button = dijit.byId('dialogCreationInfoSubmit');
  } else if (dijit.byId('dialogJobInfo')
      && dijit.byId('dialogJobInfo').open) {
    var button = dijit.byId('dialogJobInfoSubmit');
  } else if (dijit.byId('dialogDispatchWork')
      && dijit.byId('dialogDispatchWork').open) {
    var button = dijit.byId('dialogDispatchWorkSubmit');
  } else if (dijit.byId('dialogExport') && dijit.byId('dialogExport').open) {
    var button = dijit.byId('dialogPrintSubmit');
  } else if (dijit.byId('dialogRestrictTypes')
      && dijit.byId('dialogRestrictTypes').open) {
    var button = dijit.byId('dialogRestrictTypesSubmit');
  } else if (dijit.byId('dialogRestrictProductList')
	  && dijit.byId('dialogRestrictProductList').open) {
	var button = dijit.byId('dialogRestrictProductListSubmit');
  } else if (dojo.byId("editDependencyDiv") && dojo.byId("editDependencyDiv").style.display=="block") {
    dojo.byId("dependencyRightClickSave").click();
    return;
  } else {
    dojo.query(".projeqtorDialogClass").forEach(
        function(node, index, nodelist) {
          var widgetName = node.id;
          if (node.widgetid)
            widgetName = node.widgetid;
          widget = dijit.byId(widgetName);
          if (widget && widget.open) {
            btName1 = "dialog" + widgetName.charAt(0).toUpperCase()
                + widgetName.substr(1) + "Submit";
            btName2 = widgetName + "Submit";
            if (dijit.byId(btName1)) {
              button = dijit.byId(btName1);
            } else if (dijit.byId(btName2)) {
              button = dijit.byId(btName2);
            }
          }
        });
  }
  if (!button) {
    var button = dijit.byId('saveButton');
  }
  if (!button) {
    button = dijit.byId('saveParameterButton');
  }
  if (!button) {
    button = dijit.byId('saveButtonMultiple');
  }
  // for(name in CKEDITOR.instances) { // Moved to saveObject() function
  // CKEDITOR.instances[name].updateElement();
  // }
  if (button && button.isFocusable()) {
    if (dojo.byId('formDiv'))
      formDivPosition = dojo.byId('formDiv').scrollPosition;
    button.focus(); // V5.1 : attention, may loose scroll position on formDiv
                    // (see above and below lines)
    if (dojo.byId('formDiv'))
      dojo.byId('formDiv').scrollPosition = formDivPosition;
    var id = button.get('id');
    setTimeout("dijit.byId('" + id + "').onClick();", 20);
  }
}

function getFirstDayOfWeek(week, year) {
  if (week >= 53) {
    var testDate = new Date(year, 11, 31);
  } else {
    var testDate = new Date(year, 0, 5 + (week - 1) * 7);
  }
  var day = testDate.getDate();
  var month = testDate.getMonth() + 1;
  var year = testDate.getFullYear();
  var testWeek = getWeek(day, month, year);

  while (testWeek >= week) {
    testDate.setDate(testDate.getDate() - 1);
    day = testDate.getDate();
    month = testDate.getMonth() + 1;
    year = testDate.getFullYear();
    testWeek = getWeek(day, month, year);
    if (testWeek > 10 && week == 1) {
      testWeek = 0;
    }
  }
  testDate.setDate(testDate.getDate() + 1);
  return testDate;
}
function getFirstDayOfWeekFromDate(directDate) {
  var year=directDate.getFullYear();
  var week=getWeek(directDate.getDate(),directDate.getMonth()+1,directDate.getFullYear())+'';
  if (week==1 && directDate.getMonth()>10) {
    year+=1;
  } else if (week==0) {
    week=getWeek(31,12,year-1);
    if (week==1) {
      var day=getFirstDayOfWeek(1,year);
      week=getWeek(day.getDate()-1,day.getMonth()+1,day.getFullYear());
    }
    year=year-1;
  } else if (parseInt(week,10)>53) {
    week='01';
    year+=1;
  } else if (parseInt(week,10)>52) {
    lastWeek=getWeek(31,12,year);
    if (lastWeek==1) {
      var day=getFirstDayOfWeek(1,year+1);
      lastWeek=getWeek(day.getDate()-1,day.getMonth()+1,day.getFullYear());
    }
    if (parseInt(week,10)>parseInt(lastWeek,10)) {
      week='01';
      year+=1;
    }
  }
  var day=getFirstDayOfWeek(week,year);
  return day;
  
}


dateGetWeek = function(paramDate, dowOffset) {
  /*
   * getWeek() was developed by Nick Baicoianu at MeanFreePath:
   * http://www.meanfreepath.com
   */
  dowOffset = (dowOffset == null) ? 1 : dowOffset; // default dowOffset to 1
  // (ISO 8601)
  var newYear = new Date(paramDate.getFullYear(), 0, 1);
  var day = newYear.getDay() - dowOffset; // the day of week the year begins
  // on
  day = (day >= 0 ? day : day + 7);
  var daynum = Math.floor((paramDate.getTime() - newYear.getTime() - (paramDate
      .getTimezoneOffset() - newYear.getTimezoneOffset()) * 60000) / 86400000) + 1;
  var weeknum;
  // if the year starts before the middle of a week
  if (day < 4) {
    weeknum = Math.floor((daynum + day - 1) / 7) + 1;
    if (weeknum > 52) {
      nYear = new Date(paramDate.getFullYear() + 1, 0, 1);
      nday = nYear.getDay() - dowOffset;
      nday = nday >= 0 ? nday : nday + 7;
      /*
       * if the next year starts before the middle of the week, it is week #1 of
       * that year
       */
      weeknum = nday < 4 ? 1 : 53;
    }
  } else {
    weeknum = Math.floor((daynum + day - 1) / 7);
    if (weeknum > 52) {
      nYear = new Date(paramDate.getFullYear() + 1, 0, 1);
      nday = nYear.getDay() - dowOffset;
      nday = nday >= 0 ? nday : nday + 7;
      /*
       * if the next year starts before the middle of the week, it is week #1 of
       * that year
       */
      weeknum = nday < 4 ? 1 : 55;
    }
  }
  return weeknum;
};

function getWeek(day, month, year) {
  var paramDate = new Date(year, month - 1, day);
  return dateGetWeek(paramDate, 1);
}

function moveTask(source, destination) {
  var mode = 'before';
  dndSourceTable.sync();
  var nodeList = dndSourceTable.getAllNodes();
  for (i = 0; i < nodeList.length; i++) {
    if  (nodeList[i].id == source[0]) {
      mode = 'before';
      break;
    } else if (nodeList[i].id == destination) {
      mode = 'after';
      break;
    }
  }
  var url = '../tool/moveTask.php?from=' + source.join() + '&to=' + destination
      + '&mode=' + mode;
  loadContent(url, "resultDivMain", null, true, null);
  
}

function indentTask(way) {
  if (!dojo.byId("resultDivMain") || !dojo.byId('objectClass')
      || !dojo.byId('objectId')) {
    return;
  }
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return;
  }
  objectClass = dojo.byId('objectClass').value;
  objectId = dojo.byId('objectId').value;
  var url = '../tool/indentTask.php?objectClass=' + objectClass + '&objectId='
      + objectId + '&way=' + way;
  loadContent(url, "resultDivMain", null, true, null);
}

var arrayCollapsed=[];
function saveCollapsed(scope,callBack) {
  if (waitingForReply == true)  return;
  if (!scope) {
    if (dijit.byId(scope)) {
      scope = dijit.byId(scope);
    } else {
      return;
    }
  }
  if (arrayCollapsed[scope] && arrayCollapsed[scope]=='true') { return; }
  saveCollapsed[scope]='true';
  dojo.xhrPost({
    url : "../tool/saveCollapsed.php?scope=" + scope + "&value=true",
    handleAs : "text",
    load : function(data, args) {
      if (callBack) setTimeout(callBack, 10);
    }
  });
}

function saveExpanded(scope,callBack) {
  if (waitingForReply == true)
    return;
  if (!scope) {
    if (dijit.byId(scope)) {
      scope = dijit.byId(scope);
    } else {
      return;
    }
  }
  if (arrayCollapsed[scope] && arrayCollapsed[scope]=='false') { return; }
  saveCollapsed[scope]='false';
  dojo.xhrPost({
    url : "../tool/saveCollapsed.php?scope=" + scope + "&value=false",
    handleAs : "text",
    load : function(data, args) {
      if (callBack) setTimeout(callBack, 10);
    }
  });
}

function togglePane(pane) {
  if (waitingForReply == true)
    return;
  titlepane = dijit.byId(pane);
  if (titlepane) {
    if (titlepane.get('open')) {
      saveExpanded(pane);
    } else {
      saveCollapsed(pane);
    }
  }

}
// *********************************************************************************
// IBAN KEY CALCULATOR
// *********************************************************************************
function calculateIbanKey() {
  var country = ibanFormater(dijit.byId('ibanCountry').get('value'));
  var bban = ibanFormater(dijit.byId('ibanBban').get('value'));
  var number = ibanConvertLetters(bban.toString() + country.toString()) + "00";
  var calculateKey = 0;
  var pos = 0;
  while (pos < number.length) {
    calculateKey = parseInt(calculateKey.toString() + number.substr(pos, 9), 10) % 97;
    pos += 9;
  }
  calculateKey = 98 - (calculateKey % 97);
  var key = (calculateKey < 10 ? "0" : "") + calculateKey.toString();
  dijit.byId('ibanKey').set('value', key);
}

function ibanFormater(text) {
  var text = (text == null ? "" : text.toString().toUpperCase());
  return text;
}

function ibanConvertLetters(text) {
  convertedText = "";
  for (i = 0; i < text.length; i++) {
    car = text.charAt(i);
    if (car > "9") {
      if (car >= "A" && car <= "Z") {
        convertedText += (car.charCodeAt(0) - 55).toString();
      }
    } else if (car >= "0") {
      convertedText += car;
    }
  }
  return convertedText;
}

function trim(myString, car) {
  if (!myString) {
    return myString;
  }
  ;
  myStringAsTring = myString + "";
  return myStringAsTring.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function trimTag(myString, car) {
  if (!myString) {
    return myString;
  }
  ;
  myStringAsTring = myString + "";
  return myStringAsTring.replace(/^</g, '').replace(/>$/g, '');
}

function moveMenuBar(way, duration) {
  if (!duration)
    duration = 150;
  if (!menuBarMove)
    return;
  var bar = dojo.byId('menubarContainer');
  left = parseInt(bar.style.left.substr(0, bar.style.left.length - 2), 10);
  width = parseInt(bar.style.width.substr(0, bar.style.width.length - 2), 10);
  var step = 56 * 1;
  if (way == 'left') {
    pos = left + step;
  }
  if (way == 'right') {
    pos = left - step;
  }
  if (pos > 0)
    pos = 0;
  if (way == 'right') {
    var visibleWidthRight = dojo.byId('menuBarRight').getBoundingClientRect().left;
    var visibleWidthLeft = dojo.byId('menuBarLeft').getBoundingClientRect().right;
    var visibleWidth = visibleWidthRight - visibleWidthLeft;
    if (visibleWidth - left > width) {
      moveMenuBarStop();
      return;
    }
  }
  dojo.fx.slideTo({
    duration : duration,
    node : bar,
    left : pos,
    easing : function(n) {
      return n;
    },
    onEnd : function() {
      duration -= 10;
      if (duration < 50)
        duration = 50;
      if (menuBarMove) {
        moveMenuBar(way, duration);
      }
      showHideMoveButtons();
    }
  }).play();
}
menuBarMove = false;
function moveMenuBarStop() {
  showHideMoveButtons();
  menuBarMove = false;
}

function isHtml5() {
  if (dojo.isIE && dojo.isIE <= 9) {
    return false;
  } else if (dojo.isFF && dojo.isFF < 4) {
    return false;
  } else {
    return true;
  }
}

function updateCommandTotal() {
  if (cancelRecursiveChange_OnGoingChange)
    return;
  cancelRecursiveChange_OnGoingChange = true;
  // Retrieve values used for calculation
  var untaxedAmount = dijit.byId("untaxedAmount").get("value");
  if (!untaxedAmount)
    untaxedAmount = 0;
  var taxPct = dijit.byId("taxPct").get("value");
  if (!taxPct)
    taxPct = 0;
  var addUntaxedAmount = dijit.byId("addUntaxedAmount").get("value");
  if (!addUntaxedAmount)
    addUntaxedAmount = 0;
  var initialWork = dijit.byId("initialWork").get("value");
  var addWork = dijit.byId("addWork").get("value");
  // Calculated values
  var taxAmount = Math.round(untaxedAmount * taxPct) / 100;
  var fullAmount = taxAmount + untaxedAmount;
  var addTaxAmount = Math.round(addUntaxedAmount * taxPct) / 100;
  var addFullAmount = addTaxAmount + addUntaxedAmount;
  var totalUntaxedAmount = untaxedAmount + addUntaxedAmount;
  var totalTaxAmount = taxAmount + addTaxAmount;
  var totalFullAmount = fullAmount + addFullAmount;
  var validatedWork = initialWork + addWork;
  // Set values to fields
  dijit.byId("taxAmount").set('value', taxAmount);
  dijit.byId("fullAmount").set('value', fullAmount);
  dijit.byId("addTaxAmount").set('value', addTaxAmount);
  dijit.byId("addFullAmount").set('value', addFullAmount);
  dijit.byId("totalUntaxedAmount").set('value', totalUntaxedAmount);
  dijit.byId("totalTaxAmount").set('value', totalTaxAmount);
  dijit.byId("totalFullAmount").set('value', totalFullAmount);
  dijit.byId("validatedWork").set('value', validatedWork);

  cancelRecursiveChange_OnGoingChange = false;
}

function updateCommandTotalTTC() {
  if (cancelRecursiveChange_OnGoingChange)
    return;
  cancelRecursiveChange_OnGoingChange = true;
  // Retrieve values used for calculation
  var fullAmount = dijit.byId("fullAmount").get("value");
  if (!fullAmount)
    fullAmount = 0;
  var taxPct = dijit.byId("taxPct").get("value");
  if (!taxPct)
    taxPct = 0;
  var addFullAmount = dijit.byId("addFullAmount").get("value");
  if (!addFullAmount)
    addFullAmount = 0;
  var initialWork = dijit.byId("initialWork").get("value");
  var addWork = dijit.byId("addWork").get("value");
  // Calculated values
  var untaxedAmount = Math.round( fullAmount / ( 1 + ( taxPct / 100 )));
  var taxAmount = fullAmount - untaxedAmount;
  var addUntaxedAmount = Math.round( addFullAmount / ( 1 + ( taxPct / 100 )));
  var addTaxAmount =  addFullAmount - addUntaxedAmount;
  var totalUntaxedAmount = untaxedAmount + addUntaxedAmount;
  var totalTaxAmount = taxAmount + addTaxAmount;
  var totalFullAmount = fullAmount + addFullAmount;
  var validatedWork = initialWork + addWork;
  // Set values to fields
  dijit.byId("taxAmount").set('value', taxAmount);
  dijit.byId("untaxedAmount").set('value', untaxedAmount);
  dijit.byId("addTaxAmount").set('value', addTaxAmount);
  dijit.byId("addUntaxedAmount").set('value', addUntaxedAmount);
  dijit.byId("totalUntaxedAmount").set('value', totalUntaxedAmount);
  dijit.byId("totalTaxAmount").set('value', totalTaxAmount);
  dijit.byId("totalFullAmount").set('value', totalFullAmount);
  dijit.byId("validatedWork").set('value', validatedWork);

  cancelRecursiveChange_OnGoingChange = false;
}

//gautier
function providerPaymentIdProviderBill() {
  var idBill=dijit.byId("idProviderBill").get("value");
  url='../tool/getSingleData.php?dataType=providerPayment&idBill='
    + idBill;
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data) {
      if(data){
        dijit.byId("paymentAmount").set("value",dojo.number.format(data));
      }
    }
  });
}
function providerPaymentIdProviderTerm() {
  var idTerm=dijit.byId("idProviderTerm").get("value");
  url='../tool/getSingleData.php?dataType=providerPayment&idTerm='
    + idTerm;
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data) {
      if(data){
        dijit.byId("paymentAmount").set("value",dojo.number.format(data));
      }
    }
  });
}
function updateComplexities(number,idCatalog,parameterNumber){
  url = "../tool/removeWorkUnit.php?number="+number+"&idCatalog="+idCatalog;
  var notRefresh = false;
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data) {
      if(data){
        showAlert(i18n("cantDeleteUsingUOComplexity"));
        notRefresh = true;
        dijit.byId("numberComplexities").set("value",dojo.number.format(data));
      }
      var numberComplexities = dijit.byId("numberComplexities").get("value");
      if(numberComplexities > 0 && numberComplexities < parameterNumber+1 && notRefresh==false){
        loadContent("objectDetail.php?refreshComplexities=true&nb="+numberComplexities, "drawComplexity", 'listForm');
        loadContent("objectDetail.php?refreshComplexitiesValues=true", "CatalogUO_unitOfWork", 'listForm');
      }
      if(numberComplexities > parameterNumber && notRefresh==false){
        showAlert(i18n("complexityCantBeSuperiorThan", new Array(''+parameterNumber)));
        dijit.byId("numberComplexities").set("value",dojo.number.format(parameterNumber));
      }
    }
  });
}

function updateFinancialTotal(mode, col) {
  if (cancelRecursiveChange_OnGoingChange){
    return;
  }
  cancelRecursiveChange_OnGoingChange = true;
  if(mode == 'HT'){
    // Retrieve values used for calculation
    var untaxedAmount = dijit.byId("untaxedAmount").get("value");
    var fullAmount = dijit.byId("fullAmount").get("value");
    if (!untaxedAmount) untaxedAmount = 0;
    var taxPct = dijit.byId("taxPct").get("value");
    if (!taxPct) taxPct = 0;
    var discount=dijit.byId("discountAmount").get("value");
    var discountRate=dijit.byId("discountRate").get("value");
    if (!isNaN(discount) && (!dijit.byId('discountFrom') || dijit.byId('discountFrom').get('value')=='amount') ) {
      if (col!='discountRate') {
        discountRate=Math.round(10000*discount/untaxedAmount)/100;
        dijit.byId("discountRate").set("value",discountRate);
      }
    } else if (!isNaN(discountRate) ) {
      if (col!='discountAmount') {
        discount=Math.round(discountRate*untaxedAmount)/100;
        dijit.byId("discountAmount").set("value",discount);
      }
    }
    if (!discount) discount=0;
    // Calculated values
    var taxAmount = Math.round(untaxedAmount * taxPct) / 100;
    var fullAmount = taxAmount + untaxedAmount;
    var totalUntaxedAmount = untaxedAmount - discount;
    var totalTaxAmount = Math.round(totalUntaxedAmount * taxPct) / 100;
    var totalFullAmount = totalUntaxedAmount + totalTaxAmount;
    var discountFull= fullAmount-totalFullAmount;
    // Set values to fields
    dijit.byId("taxAmount").set('value', taxAmount);
    dijit.byId("fullAmount").set('value', fullAmount);
    dijit.byId("totalUntaxedAmount").set('value', totalUntaxedAmount);
    dijit.byId("totalTaxAmount").set('value', totalTaxAmount);
    dijit.byId("totalFullAmount").set('value', totalFullAmount);
    dijit.byId("discountFullAmount").set("value",discountFull);
  }else{ // TTC
    var fullAmount = dijit.byId("fullAmount").get("value");
    var untaxedAmount = dijit.byId("untaxedAmount").get("value");
    if (!fullAmount) fullAmount = 0;
    var taxPct = dijit.byId("taxPct").get("value");
    if (!taxPct) taxPct = 0;
    var discountFull=dijit.byId("discountFullAmount").get("value");
    var discountRate=dijit.byId("discountRate").get("value");
    if (!isNaN(discountFull) && (!dijit.byId('discountFrom') || dijit.byId('discountFrom').get('value')=='amount') ) {  
      if (col!='discountRate') {
        discountRate=Math.round(10000*discountFull/fullAmount)/100;
        dijit.byId("discountRate").set("value",discountRate);
      }
    } else if (!isNaN(discountRate) ) {
      if (col!='discountFullAmount') {
        discountFull=Math.round(discountRate*fullAmount)/100;
        dijit.byId("discountFullAmount").set("value",discountFull);
      }
    }
    if (!discountFull) discountFull=0;
    // Calculated values
    var untaxedAmount =  Math.round(fullAmount / ( 1 + ( taxPct / 100 ))*100)/100;
    var taxAmount = fullAmount - untaxedAmount;
    var totalFullAmount = fullAmount - discountFull;
    var totalUntaxedAmount = Math.round(totalFullAmount / ( 1 + ( taxPct / 100 ))*100)/100;
    var totalTaxAmount = totalFullAmount - totalUntaxedAmount;
    var discount= untaxedAmount-totalUntaxedAmount;
    // Set values to fields
    dijit.byId("taxAmount").set('value', taxAmount);
    dijit.byId("untaxedAmount").set('value', untaxedAmount);
    dijit.byId("totalUntaxedAmount").set('value', totalUntaxedAmount);
    dijit.byId("totalTaxAmount").set('value', totalTaxAmount);
    dijit.byId("totalFullAmount").set('value', totalFullAmount);
    dijit.byId("discountAmount").set("value",discount);
  }
  setTimeout("cancelRecursiveChange_OnGoingChange = false;",5);
}
//end
function updateBillTotal() { // Also used for Quotation !!!
  if (cancelRecursiveChange_OnGoingChange)
    return;
  cancelRecursiveChange_OnGoingChange = true;
  // Retrieve values used for calculation
  var untaxedAmount = dijit.byId("untaxedAmount").get("value");
  if (!untaxedAmount)
    untaxedAmount = 0;
  var taxPct = dijit.byId("taxPct").get("value");
  if (!taxPct)
    taxPct = 0;
  // Calculated values
  var taxAmount = Math.round(untaxedAmount * taxPct) / 100;
  var fullAmount = taxAmount + untaxedAmount;
  // Set values to fields
  dijit.byId("taxAmount").set('value', taxAmount);
  dijit.byId("fullAmount").set('value', fullAmount);
  cancelRecursiveChange_OnGoingChange = false;
}

function updateBillTotalTTC() { // Also used for Quotation !!!
  if (cancelRecursiveChange_OnGoingChange)
    return;
  cancelRecursiveChange_OnGoingChange = true;
  // Retrieve values used for calculation
  var fullAmount = dijit.byId("fullAmount").get("value");
  if (!fullAmount)
    fullAmount = 0;
  var taxPct = dijit.byId("taxPct").get("value");
  if (!taxPct)
    taxPct = 0;
  // Calculated values
  var untaxedAmount = Math.round( fullAmount / ( 1 + ( taxPct / 100 )));
  var taxAmount = fullAmount - untaxedAmount;
  
  // Set values to fields
  dijit.byId("taxAmount").set('value', taxAmount);
  dijit.byId("untaxedAmount").set('value', untaxedAmount);
  
  cancelRecursiveChange_OnGoingChange = false;
}

function copyDirectLinkUrl(scope) {
  dojo.byId('directLinkUrlDiv'+scope).style.display = 'block';
  dojo.byId('directLinkUrlDiv'+scope).select();
  setTimeout("dojo.byId('directLinkUrlDiv"+scope+"').style.display='none';", 5000);
  return false;
}

/*
 * function copyToClipboard(inElement) { if (inElement.createTextRange) { var
 * range = inElement.createTextRange(); if (range && BodyLoaded==1) {
 * range.execCommand('Copy'); } } else { var flashcopier = 'flashcopier';
 * if(!document.getElementById(flashcopier)) { var divholder =
 * document.createElement('div'); divholder.id = flashcopier;
 * document.body.appendChild(divholder); }
 * document.getElementById(flashcopier).innerHTML = ''; var divinfo = '<embed
 * src="_clipboard.swf" FlashVars="clipboard='+escape(inElement.value)+'"
 * width="0" height="0" type="application/x-shockwave-flash"></embed>';
 * document.getElementById(flashcopier).innerHTML = divinfo; } }
 */

function runWelcomeAnimation() {
  titleNode = dojo.byId("welcomeTitle");
  if (titleNode) {
    dojo.fadeOut({
      node : titleNode,
      duration : 500,
      onEnd : function() {
        var newleft = Math.floor((Math.random() * 60) - 30);
        var newtop = Math.floor((Math.random() * 80) + 10);
        dojo.byId("welcomeTitle").style.top = newtop + "%";
        dojo.byId("welcomeTitle").style.left = newleft + "%";
        dojo.fadeIn({
          node : titleNode,
          duration : 500,
          onEnd : function() {
            setTimeout("runWelcomeAnimation();", 100);
          }
        }).play();
      }
    }).play();

  }
}

function cryptData(data) {
  var arr = data.split(';');
  var crypto = arr[0];
  var userSalt = arr[1];
  var sessionSalt = arr[2];
  var pwd = dijit.byId('password').get('value');
  var login = dijit.byId('login').get('value');
  dojo.byId('hashStringLogin').value = Aes.Ctr.encrypt(login, sessionSalt, aesKeyLength);
  if (crypto == 'md5') {
    crypted = CryptoJS.MD5(pwd + userSalt);
    crypted = CryptoJS.MD5(crypted + sessionSalt);
    dojo.byId('hashStringPassword').value = crypted;
  } else if (crypto == 'sha256') {
    crypted = CryptoJS.SHA256(pwd + userSalt);
    crypted = CryptoJS.SHA256(crypted + sessionSalt);
    dojo.byId('hashStringPassword').value = crypted;
  } else {
    var crypted = Aes.Ctr.encrypt(pwd, sessionSalt, aesKeyLength);
    dojo.byId('hashStringPassword').value = crypted;
  }
}
var getHashTry = 0;
function connect(resetPassword) {
  showWait();
  dojo.byId('login').focus();
  dojo.byId('password').focus();
  changePassword = resetPassword;
  var urlCompl = "";
  if (resetPassword) {
    urlCompl = '?resetPassword=true';
  }
  if (!dojo.byId('isLoginPage')) {
    urlCompl += ((urlCompl == "") ? '?' : '&') + 'isLoginPage=true'; // Patch
                                                                    // (try) for
                                                                    // looping
                                                                    // connections
  }
  quitConfirmed = true;
  noDisconnect = true;
  var login = dijit.byId('login').get('value');
  // in cas login is included in main page, to be more fluent to move next
  var crypted = Aes.Ctr.encrypt(login, aesLoginHash, aesKeyLength);
  dojo.byId('login').focus();
  dojo.xhrGet({
    url : '../tool/getHash.php?username=' + encodeURIComponent(crypted),
    handleAs : "text",
    load : function(data) {
      if (data.substr(0, 5) == "ERROR") {
        showError(data.substr(5));
      } else if (data.substr(0, 7) == "SESSION") {
        getHashTry++;
        if (getHashTry > 1) {
          showError(i18n('errorSessionHash'));
          getHashTry = 0;
        } else {
          aesLoginHash = data.substring(7);
          connect(resetPassword);
        }
      } else {
        getHashTry = 0;
        cryptData(data);
        loadContent("../tool/loginCheck.php" + urlCompl, "loginResultDiv", "loginForm");
      }
    }
  });
}

function addNewItem(item) {
  var objectClass=dojo.byId('objectClass').value;
  var currentItem=historyTable[historyPosition];
  var currentScreen=(currentItem!==undefined)?currentItem[2]:'';
  if((currentScreen=="VersionsPlanning" || currentScreen=="ResourcePlanning") && objectClass!="Activity"){
    showAlert(i18n('alertActivityVersion'));
    return;
  }
  dojo.byId('objectClass').value = item;
  dojo.byId('objectId').value = null;
  if (switchedMode) {
    setTimeout("hideList(null,true);", 1);
  }
    if (currentScreen=="Planning" || currentScreen=="GlobalPlanning" || ((currentScreen=="VersionsPlanning" ||  currentScreen=="ResourcePlanning") && objectClass=="Activity")){
      var currentItemParent = currentItem[1];
      var originClass = currentItem[0];
      var url = 'objectDetail.php?insertItem=true&currentItemParent='+currentItemParent+'&originClass='+originClass;
      if(currentScreen=="VersionsPlanning" || currentScreen=="ResourcePlanning" ){
        url+="&currentPlanning="+currentScreen;
      }
      if(currentItemParent){
        loadContent(url, "detailDiv", 'listForm');
      }else{
        loadContent("objectDetail.php", "detailDiv", 'listForm');
      }
    }else{
      loadContent("objectDetail.php", "detailDiv", 'listForm');
    }
    if(dijit.byId('planningNewItem'))dijit.byId('planningNewItem').closeDropDown();
}

function startStopWork(action, type, id, start) {
  loadContent("../tool/startStopWork.php?action=" + action, "resultDivMain",
      "objectForm", true);
  var now = new Date();
  var vars = new Array();
  if (start) {
    vars[0] = start;
  } else {
    vars[0] = now.getHours() + ':' + now.getMinutes();
  }
  var msg = '<div style="cursor:pointer" onClick="gotoElement(' + "'" + type
      + "'," + id + ');">' + type + ' #' + id + ' '
      + i18n("workStartedAt", vars) + '</div>';
  if (action == 'start') {
    dojo.byId("currentWorkDiv").innerHTML = msg;
    dojo.byId("currentWorkDiv").style.display = 'block';
    dojo.byId("statusBarInfoDiv").style.display = 'none';
  } else {
    dojo.byId("currentWorkDiv").innerHTML = "";
    dojo.byId("currentWorkDiv").style.display = 'none';
    dojo.byId("statusBarInfoDiv").style.display = 'block';
  }
}

function getBrowserLocaleDateFormatJs() {
  return browserLocaleDateFormatJs;
}

// For FF issue on CTRL+S and F1
// Fix proposed by CACCIA
function stopDef(e) {
  var inputs, index;

  inputs = document.getElementsByTagName('input');
  for (index = 0; index < inputs.length; ++index) {
    inputs[index].blur();
  }
  inputs = document.getElementsByClassName('dijitInlineEditBoxDisplayMode');
  for (index = 0; index < inputs.length; ++index) {
    inputs[index].blur();
  }
  if (e && e.preventDefault)
    e.preventDefault();
  else if (window.event && window.event.returnValue)
    window.eventReturnValue = false;
};
// End Fix

// Button Functions to simplify onClick
function newObject() {
  dojo.byId("newButton").blur();
  id = dojo.byId('objectId');
  if (id) {
    id.value = "";
    unselectAllRows("objectGrid");
    loadContent("objectDetail.php", "detailDiv", dojo.byId('listForm'));
  } else {
    showError(i18n("errorObjectId"));
  }
}

function saveObject() {
  var param=false;
  if(dojo.byId('resourcePlanningAssignment') && dojo.byId('resourcePlanningAssignment').value!='false'){
    param=dojo.byId('resourcePlanningAssignment').value;
  }
  if(dojo.byId('buttonDivCreationInfo')!=null){
    forceRefreshCreationInfo=true;
  }
  if (waitingForReply) {
    showInfo(i18n("alertOngoingQuery"));
    return true;
  }
  for (name in CKEDITOR.instances) { // Necessary to update CKEditor field
                                      // whith focus, otherwise changes are not
                                      // detected
    CKEDITOR.instances[name].updateElement();
  }
  if (dojo.byId("saveButton")) dojo.byId("saveButton").blur();
  else if (dojo.byId("comboSaveButton")) dojo.byId("comboSaveButton").blur();
  if(param && dojo.byId('resourcePlanning')){
    submitForm("../tool/saveObject.php?selectedResource="+param, "resultDivMain", "objectForm", true);
  }else{
    submitForm("../tool/saveObject.php", "resultDivMain", "objectForm", true);
  }
  
}

function onKeyDownFunction(event, field, editorFld) {
  var editorWidth = editorFld.domNode.offsetWidth;
  var screenWidth = document.body.getBoundingClientRect().width;
  var fullScreenEditor = (editorWidth > screenWidth * 0.9) ? true : false; // if editor is > 90% screen width : editor is in full mode
  if (event.keyCode == 83
      && (navigator.platform.match("Mac") ? event.metaKey : event.ctrlKey)
      && !event.altKey) { // CTRL + S
    event.preventDefault();
    if (fullScreenEditor)
      return;
    if (window.top.dojo.isFF) {
      window.top.stopDef();
    }
    window.top.setTimeout("window.top.onKeyDownFunctionEditorSave();", 10);
  } else if (event.keyCode == 112) { // On F1
    event.preventDefault();
    if (fullScreenEditor)
      return;
    if (window.top.dojo.isFF) {
      window.top.stopDef();
    }
    window.top.showHelp();
  } else if (event.keyCode == 9 || event.keyCode == 27) { // Tab : prevent
    if (fullScreenEditor) {
      event.preventDefault();
      editorFld.toggle(); // Not existing function : block some unexpected
                          // resizing // KEEP THIS even if it logs an error in
                          // the console
    }
  } else {
    if (field == 'noteNoteEditor') {
      // nothing
    } else if (isEditingKey(event)) {
      formChanged();
    }
  }
}
function onKeyDownCkEditorFunction(event, editor) {
  var editorWidth = editor.document.$.body.offsetWidth;
  var screenWidth = window.top.document.body.getBoundingClientRect().width;
  var fullScreenEditor = (editorWidth > screenWidth * 0.9) ? true : false; // if editor is > 90% screen width : editor is in full mode
  if (event.data.keyCode == CKEDITOR.CTRL + 83) { // CTRL + S
    event.cancel();
    /*if (fullScreenEditor)
      return;*/
    if (window.top.dojo.isFF) {
      window.top.stopDef();
    }
    window.top.setTimeout("window.top.onKeyDownFunctionEditorSave();", 10);
  } else if (event.data.keyCode == 112) { // On F1
    event.cancel();
    if (fullScreenEditor)
      return;
    if (window.top.dojo.isFF) {
      window.top.stopDef();
    }
    window.top.showHelp();
  }else if(event.data.keyCode==27){
    if(window.top.editorInFullScreen() && top.whichFullScreen!=-1){
      window.top.editorArray[whichFullScreen].execCommand('maximize');
    }
  } 
}

function cancelBothFullScreen(){
  if(window.top.editorInFullScreen() && top.whichFullScreen!=-1){
    window.top.editorArray[whichFullScreen].execCommand('maximize');
    dijit.byId("globalContainer").resize();
  }
  
}

function isEditingKey(evt) {
  if (evt.ctrlKey && (evt.keyCode == 65 || evt.keyCode == 67))
    return false; // Copy or Select All
  if (evt.keyCode == 8 || evt.keyCode == 13 || evt.keyCode == 32)
    return true;
  if (evt.keyCode <= 40 || evt.keyCode == 93 || evt.keyCode == 144)
    return false;
  if (evt.keyCode >= 112 && evt.keyCode <= 123)
    return false;
  return true;
}
function onKeyDownFunctionEditorSave() {
  if (dojo.byId('formDiv')) {
    formDivPosition = dojo.byId('formDiv').scrollTop;
    if (dijit.byId('id')) dijit.byId('id').focus();
    dojo.byId('formDiv').scrollTop = formDivPosition;
  }
  window.top.setTimeout("top.globalSave();", 20);
}

function editorBlur(fieldId, editorFld) {
  var editorWidth = editorFld.domNode.offsetWidth;
  var screenWidth = document.body.getBoundingClientRect().width;
  var fullScreenEditor = (editorWidth > screenWidth * 0.9) ? true : false; // if editor is > 90% screen width : editor is in full mode
  window.top.dojo.byId(fieldId).value = editorFld.document.body.firstChild.innerHTML;
  if (fullScreenEditor) {
    editorFld.toggle(); // Not existing function : block some unexpected
                        // resizing // KEEP THIS even if it logs an error in the
                        // console
  }
  return 'OK';
}

var fullScreenTest = false;
var whichFullScreen=-1;
var isCk=false;
function editorInFullScreen() {
  if (whichFullScreen==996) return true;
  fullScreenTest = false;
  whichFullScreen=-1;
  dojo.query(".dijitEditor").forEach(function(node, index, arr) {
    var editorWidth = node.offsetWidth;
    var screenWidth = document.body.getBoundingClientRect().width;
    var fullScreenEditor = (editorWidth > screenWidth * (0.8)) ? true : false;
    if (fullScreenEditor) {
      fullScreenTest = true;
    }
  });
  if(!fullScreenTest){
    var numEditor = 1;
    while (dojo.byId('ckeditor' + numEditor)) {
      if(typeof editorArray[numEditor] != 'undefined'){
//        if(editorArray[numEditor].toolbar && editorArray[numEditor].toolbar[3] 
//        && editorArray[numEditor].toolbar[3].items[1] && editorArray[numEditor].toolbar[3].items[1]._
//        && editorArray[numEditor].toolbar[3].items[1]._.state==1){
//          fullScreenTest=true;
//          whichFullScreen=numEditor;
//        }
        if(editorArray[numEditor].commands.maximize && editorArray[numEditor].commands.maximize.state==1){
          fullScreenTest=true;
          whichFullScreen=numEditor;
        }
      }
      numEditor++;
    }
  }
  return fullScreenTest;
}

function menuFilter(filter) {
  /*
   * dojo.query(".menuBarItem").forEach(function(node, index, arr){
   * console.debug(node.innerHTML); });
   */
  menuListAutoshow = false; // the combo will be closed
  var allCollection = dojo.query(".menuBarItem");
  var newCollection = dojo.query("." + filter);
  allCollection
      .fadeOut(
          {
            duration : 200,
            onEnd : function() {
              allCollection.style("display", "none");
              bar = dojo.byId('menubarContainer');
              bar.style.left = 0;
              dojo.byId("menubarContainer").style.width = (newCollection.length * 56)
                  + "px";
              dojo.byId("menuBarVisibleDiv").style.width = (newCollection.length * 56)
                  + "px";
              newCollection.style("display", "block");
              if (newCollection.length < 20) {
                newCollection.fadeIn({
                  duration : 200
                }).play();
              } else {
                newCollection.style("height", "35px");
                newCollection.style("opacity", "1");
              }
              showHideMoveButtons();
            }
          }).play();
  saveUserParameter('defaultMenu', filter);
}

function showHideMoveButtons() {
  var bar = dojo.byId('menubarContainer');
  left = parseInt(bar.style.left.substr(0, bar.style.left.length - 2), 10);
  width = parseInt(bar.style.width.substr(0, bar.style.width.length - 2), 10);
  dojo.byId('menuBarMoveLeft').style.display = (left == 0) ? 'none' : 'block';
  if (dojo.byId('menuBarRight') && dojo.byId('menuBarLeft')) {
    var visibleWidthRight = dojo.byId('menuBarRight').getBoundingClientRect().left;
    var visibleWidthLeft = dojo.byId('menuBarLeft').getBoundingClientRect().right;
    var visibleWidth = visibleWidthRight - visibleWidthLeft;
    dojo.byId('menuBarMoveRight').style.display = (visibleWidth - left > width) ? 'none'
        : 'block';
  } else if (dojo.byId('menuBarMoveRight')){
    dojo.byId('menuBarMoveRight').style.display='none';
  }
}

function getExtraRequiredFields() {
  var objectClass=(dojo.byId('objectClass'))?dojo.byId('objectClass').value:((dojo.byId('objectClassName'))?dojo.byId('objectClassName').value:null);
  dojo.xhrPost({
    url : "../tool/getExtraRequiredFields.php",
    form : 'objectForm',
    handleAs : "text",
    load : function(data) {
      dojo.query(".generalColClassNotRequired").forEach(function(domNode){
        var key=domNode.id.replace("widget_","");
        var widget=dijit.byId(key);
        if (dijit.byId(key)) {
          dojo.removeClass(dijit.byId(key).domNode, 'required');
          dijit.byId(key).set('required',false);
        } else if (dojo.byId(key + 'Editor')) {
          keyEditor = key + 'Editor';
          dojo.removeClass(dijit.byId(keyEditor).domNode, 'required');
        } else if (dojo.byId('cke_' + key)) {
          var ckeKey = 'cke_' + key;
          dojo.removeClass(ckeKey, 'input required');
        }
      });
      var obj = JSON.parse(data);
      for ( var key in obj) {
        if (dijit.byId(key)) {
          if (obj[key] == 'required') {
            // dijit.byId(key).set('class','input required');
            dojo.addClass(dijit.byId(key).domNode, 'required');
            dijit.byId(key).set('required',true);
          } else if (obj[key] == 'optional') {
            // dijit.byId(key).set('class','input');
            dojo.removeClass(dijit.byId(key).domNode, 'required');
            dijit.byId(key).set('required',false);
          }
        } else if (dojo.byId(key + 'Editor')) {
          keyEditor = key + 'Editor';
          if (obj[key] == 'required') {
            // dijit.byId(keyEditor).set('class','dijitInlineEditBoxDisplayMode
            // input required');
            dojo.addClass(dijit.byId(keyEditor).domNode, 'required');
          } else if (obj[key] == 'optional') {
            // dijit.byId(keyEditor).set('class','dijitInlineEditBoxDisplayMode
            // input');
            dojo.removeClass(dijit.byId(keyEditor).domNode, 'required');
          }
        } else if (dojo.byId('cke_' + key)) {
          var ckeKey = 'cke_editor_' + key;
          if (obj[key] == 'required') {
            dojo.query('.'+ckeKey).addClass('input required','');
          } else if (obj[key] == 'optional') {
            dojo.query('.'+ckeKey).removeClass('input required','');
          }
        }
      }
    }
  });
}
function getExtraHiddenFields(idType,idStatus,idProfile) {
  var objectClass=(dojo.byId('objectClass'))?dojo.byId('objectClass').value:((dojo.byId('objectClassName'))?dojo.byId('objectClassName').value:null);
  if (!idStatus) {
    if (dijit.byId('idStatus')) {
      idStatus=dijit.byId('idStatus').get('value');
    }
  }
  if (!idType) {
    if (objectClass) {
      var typeName='id'+objectClass+'Type';
      if (dijit.byId(typeName)) {
        idType=dijit.byId(typeName).get('value');
      }
    }
  }
  dojo.xhrGet({
    url : "../tool/getExtraHiddenFields.php" + "?type=" + idType+"&status="+idStatus+"&profile="+idProfile
        + "&objectClass=" + objectClass,
    handleAs : "text",
    load : function(data) {
      var obj = JSON.parse(data);
      dojo.query(".generalRowClass:not(.dijitTooltipData)").style("display", "table-row");
      dojo.query(".generalColClass:not(.dijitTooltipData)").style("display", "inline-block");
      for (key in obj) {
        dojo.query("." + obj[key] + "Class:not(.dijitTooltipData)").style("display", "none");
      }
      hideEmptyTabs();
    }
  });
}
function hideEmptyTabs() {
  dojo.query(".detailTabClass").forEach(function(domNode){
    var name=domNode.id.replace("widget_","");
    var widget=dijit.byId(name);
    if (widget) {
      var displayTab="none";
      var children=widget.getChildren();
      for (var i=0;i<children.length;i++) {
        if (children[i].class.indexOf("titlePaneFromDetail")>=0) {
          item=dojo.byId(children[i].id);
          if (dojo.style(item,"display")!="none") {
            displayTab="inline-block";
            break;
          }
        }
      }
      dojo.query("[widgetid$=tablist_"+domNode.id+"]").forEach(function(tabNode){
        dojo.style(tabNode,"display",displayTab);
      });
    }
  });
}
function getExtraReadonlyFields(idType,idStatus,idProfile) {
  var objectClass=(dojo.byId('objectClass'))?dojo.byId('objectClass').value:((dojo.byId('objectClassName'))?dojo.byId('objectClassName').value:null);
  if (!idStatus) {
    if (dijit.byId('idStatus')) {
      idStatus=dijit.byId('idStatus').get('value');
    }
  }
  if (!idType) {
    if (objectClass) {
      var typeName='id'+objectClass+'Type';
      if (dijit.byId(typeName)) {
        idType=dijit.byId(typeName).get('value');
      }
    }
  }
  dojo.xhrGet({
    url : "../tool/getExtraReadonlyFields.php" + "?type=" + idType+"&status="+idStatus+"&profile="+idProfile
        + "&objectClass=" + objectClass,
    handleAs : "text",
    load : function(data) {
      var obj = JSON.parse(data);
      dojo.query(".generalColClassNotReadonly").forEach(function(domNode){
        var name=domNode.id.replace("widget_","");
        var widget=dijit.byId(name);
        if (widget) {
          widget.set('readOnly',false);
        }
      });
      for (key in obj) {
        dojo.query("." + obj[key] + "Class").forEach(function(domNode){
          var name=domNode.id.replace("widget_","");
          var widget=dijit.byId(name);
          if (widget) {
            widget.set('readOnly',true);
          }
        });
        //if (dijit.byId(obj[key])) dijit.byId(obj[key]).set('readOnly',true); // ("readonly", "true"); ?
      }
    }
  });
}
function intercepPointKey(obj, event) {
  var attr = dijit.byId(obj.id).get('readOnly');
	if(attr == false){
		event.preventDefault();
		setTimeout('replaceDecimalPoint("' + obj.id + '");', 1);
	}
  return false;
}
function replaceDecimalPoint(field) {
  var dom = dojo.byId(field);
  var cursorPos = dom.selectionStart;
  dom.value = dom.value.slice(0,cursorPos)+browserLocaleDecimalSeparator+dom.value.slice(cursorPos);
  dom.selectionStart=cursorPos+1;
  dom.selectionEnd=cursorPos+1;
}
function ckEditorReplaceAll() {
  var numEditor = 1;
  while (dojo.byId('ckeditor' + numEditor)) {
    var editorName = dojo.byId('ckeditor' + numEditor).value;
    ckEditorReplaceEditor(editorName, numEditor);
    numEditor++;
  }
}
var maxEditorHeight = Math.round(screen.height * 0.6);
var tempResizeCK = null;
var currentEditorIsNote=false;
var doNotTriggerResize=false;
function ckEditorReplaceEditor(editorName, numEditor) {
  var height = 200;
  doNotTriggerResize=true;
  if (dojo.byId("ckeditorHeight"+numEditor)) {
    height=dojo.byId("ckeditorHeight"+numEditor).value;
  }
  currentEditorIsNote=false;
  if (editorName == 'noteNote') {
    height = maxEditorHeight - 150;
    currentEditorIsNote=true;
  }
  if (editorName == 'kanbanResult') {
    height = maxEditorHeight - 150;
    currentEditorIsNote=true;
  }
  forceCkInline = false;
  if (editorName == 'WUDescriptions' || editorName == 'WUIncomings' || editorName == 'WULivrables') {
    height = 100;
    forceCkInline = true;
    autofocus = true;
  }
  if (editorName == 'situationComment') {
    height = 200;
    currentEditorIsNote=true;
  }
  if (editorName == 'textFullScreenCK') {
    currentEditorIsNote=true;
  }
  var readOnly = false;
  if (dojo.byId('ckeditor' + numEditor + 'ReadOnly')
      && dojo.byId('ckeditor' + numEditor + 'ReadOnly').value == 'true') {
    readOnly = true;
  }
  autofocus = (editorName == 'noteNote') ? true : false;
  
  editorArray[numEditor] = CKEDITOR.replace(editorName, {
    customConfig : 'projeqtorConfig.js',
    filebrowserUploadUrl : '../tool/uploadImage.php',
    height : height,
    readOnly : readOnly,
    language : currentLocale,
    startupFocus : autofocus
  });
  if (editorName != 'noteNote' && editorName != 'WUDescriptions'&& editorName != 'WUIncomings'&& editorName != 'WULivrables' && editorName!="situationComment") { // No formChanged for notes
    editorArray[numEditor].on('change', function(evt) {
      formChanged();
    });
  }
  if (editorName == 'textFullScreenCK') { // Control CKEditor
    editorArray[numEditor].on( 'instanceReady', function(event){ 
      if(event.editor.getCommand( 'maximize' ).state == CKEDITOR.TRISTATE_OFF) event.editor.execCommand( 'maximize'); //ckeck if maximize is off
      displayFullScreenCKopening=false;
    });
    editorArray[numEditor].on( 'resize', function(event){
      var editorName='textFullScreenCK';
      var status=CKEDITOR.instances['textFullScreenCK'].commands.maximize.state; // 1=minimized, 2=maximized
      if (status==1) displayFullScreenCK_close();
    });
    editorArray[numEditor].addCommand('CKfullScreenSave', {
      exec : function(editor, data) {
        if (CKEDITOR.instances[displayFullScreenCKfield]) {
          CKEDITOR.instances[displayFullScreenCKfield].setData(CKEDITOR.instances['textFullScreenCK'].getData());
          saveObject();
        }
      }
    });
    editorArray[numEditor].keystrokeHandler.keystrokes[CKEDITOR.CTRL + 83]='CKfullScreenSave';
    editorArray[numEditor].keystrokeHandler.keystrokes[27]='maximize';
  }
  editorArray[numEditor].on('blur', function(evt) { // Trigger after paster image : notificationShow, afterCommandExec, dialogShow
    evt.editor.updateElement();
    // formChanged();
  });
  //gautier
  if (editorName != 'textFullScreenCK') { 
    editorArray[numEditor].on('resize', function(evt) {
      if(tempResizeCK){
        clearTimeout(tempResizeCK);
      }
      var CkHeight = this.ui.editor.container.$.clientHeight - 102;
      tempResizeCK = setTimeout("CKeEnd("+CkHeight+","+numEditor+");",500);
    }); 
    
    editorArray[numEditor].on('key', function(evt) {
      onKeyDownCkEditorFunction(evt, this);
    });
    editorArray[numEditor].on('instanceReady', function(evt) {
      if (dojo.hasClass(evt.editor.name, 'input required')) {
        dojo.query('.cke_editor_'+evt.editor.name).addClass('input required');
      }
    });
    editorArray[numEditor].on('dragover', function(evt) {
      if (dojo.byId('dropFilesInfoDiv')) {
        dojo.byId('dropFilesInfoDiv').style.opacity='0%';
        dojo.byId('dropFilesInfoDiv').style.display='none';
      }
    });
    editorArray[numEditor].on('dragleave', function(evt) {
      if (dojo.byId('dropFilesInfoDiv')) {
        dojo.byId('dropFilesInfoDiv').style.opacity='50%';
        dojo.byId('dropFilesInfoDiv').style.display='block';
      }
    });
  }
  doNotTriggerResize=false;
}
function CKeEnd(CkHeight,numEditor) {
  if (doNotTriggerResize) return;
  if (! dojo.byId('ckeditorObj'+numEditor)) return;
  var ckeObj = dojo.byId('ckeditorObj'+numEditor).value;
  ckeObj = 'ckeditorHeight'+ckeObj;
  saveDataToSession(ckeObj,CkHeight,true);
  tempResizeCK=null;
}

// Default Planning Mode
function setDefaultPlanningMode(typeValue) {
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=defaultPlanningMode&idType='
        + typeValue + "&objectClass=" + dojo.byId('objectClass').value,
    handleAs : "text",
    load : function(data) {
      var objClass = dojo.byId('objectClass').value;
      var planningMode = objClass + "PlanningElement_id" + objClass
          + "PlanningMode";
      dijit.byId(planningMode).set('value', data);
    }
  });
}

// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
function readOnlyNotificationGenerateBeforeInMin(theTargetDate) {
    if (typeof(theTargetDate)=='undefined') {
        theTargetDate = dijit.byId('_spe_targetDateNotifiableField').getValue();
    }
    if (theTargetDate.substr(theTargetDate.length-8)!=="DateTime") {
        dijit.byId("notificationGenerateBeforeInMin").set("readOnly",true);
        dijit.byId("notificationGenerateBeforeInMin").setValue("");
    } else {
        everyChecked = dijit.byId("everyDay").checked + dijit.byId("everyWeek").checked + dijit.byId("everyMonth").checked + dijit.byId("everyYear").checked;
        genBefore = dijit.byId("notificationGenerateBefore").getValue();
        nbRepeatsBefore = dijit.byId("notificationNbRepeatsBefore").getValue();
        if ( everyChecked==0 && !(genBefore>0) && !(nbRepeatsBefore>0) ) {
            dijit.byId("notificationGenerateBeforeInMin").set("readOnly",false);
            dijit.byId("notificationGenerateBeforeInMin").setValue("");
            dijit.byId("notificationGenerateBefore").set("readOnly",true);
            dijit.byId("notificationGenerateBefore").setValue("");
            dijit.byId("notificationNbRepeatsBefore").set("readOnly",true);
            dijit.byId("notificationNbRepeatsBefore").setValue("");
        } else {
            dijit.byId("notificationGenerateBeforeInMin").set("readOnly",true);
            dijit.byId("notificationGenerateBeforeInMin").setValue("");            
            if (dijit.byId("everyDay").checked) {
                dijit.byId("notificationGenerateBefore").set("readOnly",true);
                dijit.byId("notificationGenerateBefore").setValue("");
        }
    }
}
}

function refreshTargetDateFieldNotification(notificationItemValue) {
    url='../tool/getDateFieldsNotifiable.php?idNotifiable='+ notificationItemValue;

    var selectTarget = "_spe_targetDateNotifiableField";
    var idSelectTarget = dijit.byId(selectTarget);
    idSelectTarget.removeOption(idSelectTarget.getOptions());
    dijit.byId(selectTarget).set('value','');
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        load : function(data) {
            var obj = JSON.parse(data);
            if(data){
                first=true;
                for ( var key in obj) {
                    if (first===true) {
                        first=false;
                        readOnlyNotificationGenerateBeforeInMin(key);
                    }
                    var o = dojo.create("option", {label: obj[key], value: key});     
                    dijit.byId(selectTarget).addOption(o); 
                }
            }
            if (first==true) {
              var o = dojo.create("option", {label: i18n('noDataFound'), value: ' '});     
              dijit.byId(selectTarget).addOption(o); 
              //readOnlyNotificationGenerateBeforeInMin(' ');
            }
        }
    });
}

function refreshAllowedWordsForNotificationDefinition(notificationItemValue) {
        url='../tool/getAllowedWordsForNotificationDefinition.php?idNotifiable='+ notificationItemValue;
    var allowedWords = "_spe_allowedWords";
    var element = document.getElementById(allowedWords);
    if (typeof(element)==='undefined' || element==null) {return;}
    element.innerHTML = "";
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        load : function(data) {
            if(data){
                var dataP = JSON.parse(data);
                element.innerHTML = dataP; 
            }
        }
    });
                }

function refreshAllowedReceiversForNotificationDefinition(notificationItemValue) {
        url='../tool/getAllowedReceiversForNotificationDefinition.php?idNotifiable='+ notificationItemValue;        
    var allowedReceivers = "_spe_allowedReceivers";
    var element = document.getElementById(allowedReceivers);
    if (typeof(element)==='undefined' || element==null) {return;}
    element.innerHTML="";
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        load : function(data) {
            if(data){
                var dataP = JSON.parse(data);
                element.innerHTML = dataP; 
            }
        }
    });

}

function refreshListItemsInNotificationDefinition(idNotifiable, forReceivers) {
    url='../tool/getAFieldForAClassById.php?Class=Notifiable&field=notifiableItem&id=' + idNotifiable;
    dojo.xhrGet({
        url : url,
        handleAs : "text",
        load : function(notifItem) {
            if(notifItem){
                var notifiableItem = JSON.parse(notifItem);
                url='../tool/getListItemsForNotificationDefinition.php?notifiableItem='+notifiableItem+'&forReceivers='+forReceivers;
                dojo.xhrGet({
                    url : url,
                    handleAs : "text",
                    load : function(data) {
                        if(data){
                            var obj = JSON.parse(data);
                            if(forReceivers==="NO") {
                                var selectTarget = '_spe_listItemsTitle';
                                dijit.byId(selectTarget).removeOption(dijit.byId(selectTarget).getOptions());
                                dijit.byId(selectTarget).set('value','');
                                first = true;
                                for ( var key in obj) {
                                    var o = dojo.create("option", {label: obj[key], value: key});
                                    dijit.byId(selectTarget).addOption(o);
                                    if (first===true) {
                                        refreshListFieldsInNotificationDefinition(key, "Title");
                                        first=false;
                                    }
                                }
                                var selectTarget = '_spe_listItemsContent';
                                dijit.byId(selectTarget).removeOption(dijit.byId(selectTarget).getOptions());
                                dijit.byId(selectTarget).set('value','');
                                first = true;
                                for ( var key in obj) {
                                    var o = dojo.create("option", {label: obj[key], value: key});
                                    dijit.byId(selectTarget).addOption(o);
                                    if (first===true) {
                                        refreshListFieldsInNotificationDefinition(key, "Content");
                                        first=false;
                                    }
                                }
                                var selectTarget = '_spe_listItemsRule';
                                dijit.byId(selectTarget).removeOption(dijit.byId(selectTarget).getOptions());
                                dijit.byId(selectTarget).set('value','');
                                first = true;
                                for ( var key in obj) {
                                    var o = dojo.create("option", {label: obj[key], value: key});
                                    dijit.byId(selectTarget).addOption(o);
                                    if (first===true) {
                                        refreshListFieldsInNotificationDefinition(key, "Rule");
                                        first=false;
                                    }
                                }
                            } else {
                                var selectTarget = '_spe_listItemsReceiver';
                                dijit.byId(selectTarget).removeOption(dijit.byId(selectTarget).getOptions());
                                dijit.byId(selectTarget).set('value','');
                                first = true;
                                for ( var key in obj) {
                                    var o = dojo.create("option", {label: obj[key], value: key});     
                                    dijit.byId(selectTarget).addOption(o);
                                    if (first===true) {
                                        refreshListFieldsInNotificationDefinition(key, "Receiver");
                                        first=false;
                                    }
                                }                                
                            }
                        }
                    }
                });                
            }
        }
    });   
}

//Damian
function refreshListFieldsInTemplate(idItemMailable) {
    url='../tool/getListFieldsForTemplate.php?idItemMailable='+ idItemMailable;
    var selectTarget = '_spe_listItemTemplate';
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

function refreshListFieldsInNotificationDefinition(table, context) {
    url='../tool/getListFieldsForNotificationDefinition.php?table='+ table + '&context=' + context;

    var selectTarget = '_spe_listFields' + context;
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

function addFieldInTextBoxForNotificationItem(context, textBox, editor) {

	var selectItems = '_spe_listItems' + context;
    var selectedItemLabel = dijit.byId(selectItems).attr('displayedValue');
    var selectedItem = dijit.byId(selectItems).getValue();
    var selectFields = '_spe_listFields' + context;
    var selectedField = dijit.byId(selectFields).getValue();
    var selectedNotifiable = document.getElementById('idNotifiable').value;

    var idTextBox = dijit.byId(textBox);
    element = document.getElementById(textBox);
    
    if (editor==='text' || textBox!=='content') {
      var val = element.value;
      cursPos = val.slice(0, element.selectionStart).length;
    } else if (editor==='CK' || editor==='CKInline') {
      var val = CKEDITOR.instances[textBox].getData();
      cursPos = val.length;
    } else if (editor==='Dojo' || editor==='DojoInline') {
      var val = dijit.byId(textBox+'Editor').getValue();
      cursPos = val.length;
    }

    if (editor=='text' && textBox!=='content') {
        oldText = idTextBox.getValue();
    } else {
        oldText = val;
    }
    
    if (context === 'Receiver') {
      if (oldText.length>0) {
        textToAdd=';';            
      } else {
        textToAdd='';
      }    
    } else {
        textToAdd = '${';
    }    
    if (selectedItemLabel!==selectedNotifiable) {
        textToAdd=textToAdd + 'id' + selectedItem + '.';
    }
    textToAdd=textToAdd + selectedField;
    if (context !== 'Receiver') {
        textToAdd=textToAdd + "}";            
    }
    if (context==="Receiver") {
        newText = oldText + textToAdd;
    } else {
        newText = oldText.substr(0, cursPos) + textToAdd + oldText.substr(cursPos);        
    }
    
    
    if (editor==='text' || textBox!=='content') {
        idTextBox.setValue(newText);
    } else if (editor==='CK' || editor==='CKInline') {
       CKEDITOR.instances[textBox].setData(newText);
    } else if (editor==='Dojo' || editor==='DojoInline') {    
       dijit.byId(textBox+'Editor').setValue(newText)
    }
}

//Damian
function addFieldInTextBoxForEmailTemplateItem(editor) {
  var selectedItem = dijit.byId('_spe_listItemTemplate').get("value");
  var idTextBox = dojo.byId('template').value;
  var element = document.getElementById('template');
  var context = '_spe_listItemTemplate';
  var textBox = 'template';
  
  if (editor==='text' || textBox!=='template') {
    var val = element.value;
    cursPos = val.slice(0, element.selectionStart).length;
  } else if (editor==='CK' || editor==='CKInline') {
    var val = CKEDITOR.instances[textBox].getData();
    cursPos = val.length;
  } else if (editor==='Dojo' || editor==='DojoInline') {
    var val = dijit.byId(textBox+'Editor').getValue();
    cursPos = val.length;
  }

  if (editor=='text' && textBox!=='template') {
      oldText = idTextBox.getValue();
  } else {
      oldText = val;
  }
  
  textToAdd = '${';
    
  if(selectedItem.search('_') == 0){
	  textToAdd=textToAdd + selectedItem.substring(1);
  }else{
	  textToAdd=textToAdd + selectedItem;
  }
  textToAdd=textToAdd + "}";
  newText = oldText.substr(0, cursPos) + textToAdd + oldText.substr(cursPos);        
  
  if (editor==='text' || textBox!=='template') {
      idTextBox.setValue(newText);
  } else if (editor==='CK' || editor==='CKInline') {
    CKEDITOR.instances[textBox].insertText(textToAdd);
    //CKEDITOR.instances[textBox].setData(newText);
  } else if (editor==='Dojo' || editor==='DojoInline') {    
     dijit.byId(textBox+'Editor').setValue(newText);
  }
}

function addOperatorOrFunctionInTextBoxForNotificationItem(textBox) {                
    var selectItems = '_spe_listOperatorsAndFunctionsRule';
    var selectedItem = dijit.byId(selectItems).getValue();

    oldText = dijit.byId(textBox).getValue();
    element = document.getElementById(textBox);
    var val = element.value;
    cursPos = val.slice(0, element.selectionStart).length;

    textToAdd=selectedItem;
    newText = oldText.substr(0, cursPos) + textToAdd + oldText.substr(cursPos);        
    dijit.byId(textBox).setValue(newText);
}

function setGenerateBeforeWhenNotificationDayBeforeChange(colValue) {
    isFixedDay = false;
    if ((dijit.byId('everyMonth').checked && dijit.byId('fixedDay').getValue()>0) ||
        (dijit.byId('everyYear').checked && dijit.byId('fixedMonthDay').getValue()>0)) {
        isFixedDay = true;   
    }
    if (colValue<0 || isFixedDay || dijit.byId('everyDay').checked ) {
        dijit.byId('notificationGenerateBefore').set('readOnly', true);
        dijit.byId('notificationGenerateBefore').setValue(null);
        dojo.addClass('notificationGenerateBefore', 'readonly');        
    } else {
        dijit.byId('notificationGenerateBefore').set('readOnly', false);
        dojo.removeClass('notificationGenerateBefore', 'readonly');                
    }
    
}

function setGenerateBeforeWhenFixedDayChange(colValue) {
    if (colValue>0 || colValue=="" || dijit.byId('notificationNbRepeatsBefore').getValue()<0) {
        dijit.byId('notificationGenerateBefore').set('readOnly', true);
        dijit.byId('notificationGenerateBefore').setValue(null);
        dojo.addClass('notificationGenerateBefore', 'readonly');        
    } else {
        dijit.byId('notificationGenerateBefore').set('readOnly', false);
        dojo.removeClass('notificationGenerateBefore', 'readonly');                
    }
}

function setFixedMonthDayAttributes(colName) {
    if (colName==='everyDay') {
        if(dijit.byId('everyDay').checked) {
            dijit.byId('everyWeek').set('checked', false);
            dijit.byId('everyMonth').set('checked', false);
            dijit.byId('everyYear').set('checked', false);
            dojo.byId('widget_fixedDay').style.display = 'none';
            dojo.byId('widget_fixedMonth').style.display = 'none';
            dojo.byId('widget_fixedMonthDay').style.display = 'none';
            dojo.addClass('_spe_targetDateNotifiableField', 'required');
            dijit.byId('fixedMonth').setValue(null);
            dijit.byId('fixedMonthDay').setValue(null);
            dijit.byId('fixedDay').setValue(null);
            dijit.byId('notificationGenerateBefore').set('readOnly', true);
            dijit.byId('notificationGenerateBefore').setValue(null);
            dojo.addClass('notificationGenerateBefore', 'readonly');
        }
    }

    if (colName==='everyWeek') {
        if(dijit.byId('everyWeek').checked) {
            dijit.byId('everyDay').set('checked', false);
            dijit.byId('everyMonth').set('checked', false);
            dijit.byId('everyYear').set('checked', false);
            dojo.byId('widget_fixedDay').style.display = 'none';
            dojo.byId('widget_fixedMonth').style.display = 'none';
            dojo.byId('widget_fixedMonthDay').style.display = 'none';
            dojo.addClass('_spe_targetDateNotifiableField', 'required');
            dijit.byId('fixedMonth').setValue(null);
            dijit.byId('fixedMonthDay').setValue(null);
            dijit.byId('fixedDay').setValue(null);
            dijit.byId('notificationGenerateBefore').set('readOnly', false);
            dojo.removeClass('notificationGenerateBefore', 'readonly');
        }
    }

    if (colName==='everyMonth') {
        if(dijit.byId('everyMonth').checked) {
            dijit.byId('everyDay').set('checked', false);
            dijit.byId('everyWeek').set('checked', false);
            dijit.byId('everyYear').set('checked', false);
            dojo.byId('widget_fixedDay').style.display = 'block';
            dojo.byId('widget_fixedMonth').style.display = 'none';
            dojo.byId('widget_fixedMonthDay').style.display = 'none';
            dojo.addClass('_spe_targetDateNotifiableField', 'required');
            dijit.byId('fixedMonth').setValue(null);
            dijit.byId('fixedMonthDay').setValue(null);
            if (dijit.byId('fixedDay').getValue()>0 || dijit.byId('fixedDay').getValue() == "" || dijit.byId('notificationNbRepeatsBefore').getValue()<0) {
                dijit.byId('notificationGenerateBefore').set('readOnly', true);
                dijit.byId('notificationGenerateBefore').setValue(null);
                dojo.addClass('notificationGenerateBefore', 'readonly');                                
            } else {
                dijit.byId('notificationGenerateBefore').set('readOnly', false);
                dojo.removeClass('notificationGenerateBefore', 'readonly');                
            }
        } else{
            dojo.byId('widget_fixedDay').style.display = 'none';
            dijit.byId('fixedDay').setValue(null);
        }
    }
    
    if (colName==='everyYear') {
        if(dijit.byId('everyYear').checked) {
            dijit.byId('everyDay').set('checked', false);
            dijit.byId('everyWeek').set('checked', false);
            dijit.byId('everyMonth').set('checked', false);            
            dojo.byId('widget_fixedDay').style.display = 'none';
            dojo.byId('widget_fixedMonth').style.display = 'block';
            dojo.byId('widget_fixedMonthDay').style.display = 'block';
            dijit.byId('fixedDay').setValue('');
            if (dijit.byId('fixedMonthDay').getValue()>0 || dijit.byId('fixedMonthDay').getValue()=="" || dijit.byId('notificationNbRepeatsBefore').getValue()<0) {
                dijit.byId('notificationGenerateBefore').set('readOnly', true);
                dijit.byId('notificationGenerateBefore').setValue(null);
                dojo.addClass('notificationGenerateBefore', 'readonly');                                
            } else {
                dijit.byId('notificationGenerateBefore').set('readOnly', false);
                dojo.removeClass('notificationGenerateBefore', 'readonly');                
            }
        } else{
            dojo.byId('widget_fixedMonth').style.display = 'none';
            dojo.byId('widget_fixedMonthDay').style.display = 'none';            
            dijit.byId('fixedMonth').setValue(null);
            dijit.byId('fixedMonthDay').setValue(null);
        }
    }
        
    if (!dijit.byId('everyDay').checked && !dijit.byId('everyWeek').checked && !dijit.byId('everyMonth').checked && !dijit.byId('everyYear').checked) {
       dijit.byId('notificationNbRepeatsBefore').set('readOnly', true); 
       dijit.byId('notificationNbRepeatsBefore').setValue(""); 
       dojo.addClass('notificationNbRepeatsBefore', 'readonly');
       dijit.byId('notificationGenerateBefore').set('readOnly', false);
       dojo.removeClass('notificationGenerateBefore', 'readonly');
    } else {
       dijit.byId('notificationNbRepeatsBefore').set('readOnly', false);         
       dojo.removeClass('notificationNbRepeatsBefore', 'readonly');
    }
    if (!dijit.byId('everyDay').checked && !dijit.byId('everyWeek').checked && !dijit.byId('everyMonth').checked && !dijit.byId('everyYear').checked) {
       dijit.byId('notificationNbRepeatsBefore').set('readOnly', true); 
       dijit.byId('notificationNbRepeatsBefore').setValue(""); 
       dojo.addClass('notificationNbRepeatsBefore', 'readonly');
       dijit.byId('notificationGenerateBefore').set('readOnly', false);
       dojo.removeClass('notificationGenerateBefore', 'readonly');
    } else {
       dijit.byId('notificationNbRepeatsBefore').set('readOnly', false);         
       dojo.removeClass('notificationNbRepeatsBefore', 'readonly');
}
}

function setDrawLikeFixedDayWhenFixedMonthChange(value, name) {
    var arrayMonth30 = new Array(4,6,9,11);
    var dLFixedDay='';
    if (name==='fixedMonth') {
        if (value===null || value<1 || value>12) {return;}
        var dLFixedDay = 'fixedMonthDay';
        var dayValue = dijit.byId(dLFixedDay).getValue();
        var monthValue = value;
    }
    if (name==='fixedMonthDay') {
        var dLFixedDay = name;
        var dayValue = value;
        var monthValue = dijit.byId('fixedMonth').getValue();        
    }
    
    if (dLFixedDay==='' || dayValue < 29) { return;}
    
    if (monthValue=== 2 && dayValue>28) {
        dijit.byId(dLFixedDay).setValue(28);        
    }

    if (arrayMonth30.includes(monthValue) && dayValue===31) {
        dijit.byId(dLFixedDay).setValue(30);
        return;
    } 
}

// END - ADD BY TABARY - NOTIFICATION SYSTEM
function setDefaultPriority(typeValue) {
  url='../tool/getSingleData.php?dataType=defaultPriority&idType='
    + typeValue + "&objectClass=" + dojo.byId('objectClass').value;
  dojo.xhrGet({
    url : url,
    handleAs : "text",
    load : function(data) {
      var objClass = dojo.byId('objectClass').value;
      var planningPriority = objClass + "PlanningElement_priority" ;
      if(data){
        dijit.byId(planningPriority).set('value', data);
      }
    }
  });
}

function setDefaultCategory(typeValue) {
  dojo.xhrGet({
    url : '../tool/getSingleData.php?dataType=defaultCategory&idType='
        + typeValue + "&objectClass=" + dojo.byId('objectClass').value ,
    handleAs : "text",
    load : function(data) {
      dijit.byId("idCategory").set('value', data);
    }
  });
}

function updateVersionName(sep) {
  var prd = '';
  if (dijit.byId("idComponent")) {
    prd = dijit.byId("idComponent").get("displayedValue");
  } else if (dijit.byId("idProduct")) {
    prd = dijit.byId("idProduct").get("displayedValue");
  }
  var num = dijit.byId("versionNumber").get("value");
  var result = prd + sep + num;
  dijit.byId("name").set("value", result);
}
// GALLERY
function runGallery() {
  loadContent("galleryShow.php", "detailGalleryDiv", "galleryForm", false);
}
function changeGalleryEntity() {
  loadContent("galleryParameters.php", "listGalleryDiv", "galleryForm", false);
}

function saveDataToSession(param, value, saveUserParameter, callBack) {
  var url="../tool/saveDataToSession.php";
  url+="?idData="+param;
  url+="&value="+value;
  if (saveUserParameter && (saveUserParameter==true || saveUserParameter=='true' || saveUserParameter==1)) { 
    url+="&saveUserParam=true";
  }
  dojo.xhrPost({
    url : url,
    load : function(data, args) {
      if(callBack){
      setTimeout(callBack, 10);
      }
    },
    error : function () {
      consoleTraceLog("error saving data to session param="+param+", value="+value+", saveUserParameter="+saveUserParameter);
    }
 });
}

function showExtraButtons(location) {
  var btnNode=dojo.byId(location);
  var divNode=dojo.byId(location+'Div');
  if (! divNode) return;
  
  if (divNode.style.display=='block') {
    divNode.style.display='none';
  } else {
    divNode.style.display='block';
    divNode.style.left=(btnNode.offsetLeft-((isNewGui)?10:5))+"px";
    var container=dojo.byId('buttonDiv');
    var positionner=dojo.byId('buttonDivContainerDiv');
    if (container) {
      var containerWidth=parseInt(container.style.width);
      var nodeWidth=parseInt(divNode.style.width);
      var nodeLeft=parseInt(divNode.style.left);
      var position=positionner.offsetLeft;
      if (nodeLeft+nodeWidth>containerWidth-position-5) {
        divNode.style.left=(containerWidth-position-nodeWidth-5)+"px";
      }
      if(nodeLeft < 220 && location=="subscribeButton"){
        if (isNewGui) {divNode.style.left = '-250px';divNode.style.top='65px';}
        else divNode.style.left = -186+'px';
      }
    }
  }
}
function hideExtraButtons(location) {
  var btnNode=dojo.byId(location);
  var divNode=dojo.byId(location+'Div');
  if (! divNode) return;
  if (divNode.style.display=='block') {
    divNode.style.display='none';
  }
}

//ADD qCazelles - Predefined Action
function loadPredefinedAction(editorType) {
  
  dojo.xhrPost({
    url : "../tool/getPredefinedAction.php?idPA=" + dijit.byId('listPredefinedActions').get("value"),
    handleAs : "text",
    load: function(data,args) {
      if (data) {
        var pa = JSON.parse(data);

        if (dijit.byId('name')) {
          dijit.byId('name').set('value', pa.name);
          dijit.byId('idActionType').set('value', pa.idActionType);
          dijit.byId('idProject').set('value', pa.idProject);
          dijit.byId('idPriority').set('value', pa.idPriority);
          dijit.byId('idContact').set('value', pa.idContact);
          dijit.byId('idResource').set('value', pa.idResource);
          dijit.byId('idEfficiency').set('value', pa.idEfficiency);
          
          if (pa.isPrivate == 1) {
            dijit.byId('isPrivate').set('checked', true);
          }

          dijit.byId('initialDueDate').set('value', null);
          if (Number(pa.initialDueDateDelay) != 0) {
            var myDate = new Date();
            myDate.setDate(myDate.getDate() + Number(pa.initialDueDateDelay));
            dijit.byId('initialDueDate').set('value', myDate);
          }

          dijit.byId('actualDueDate').set('value', null);
          if (Number(pa.actualDueDateDelay) != 0) {
            var myDateBis = myDate;
            myDateBis.setDate(myDateBis.getDate() + Number(pa.actualDueDateDelay));
            dijit.byId('actualDueDate').set('value', myDateBis);
          }

            if (editorType=="CK" || editorType=="CKInline") { // CKeditor type
              CKEDITOR.instances['description'].setData(pa.description);
            CKEDITOR.instances['result'].setData(pa.result);
          }
          else if (editorType=="text") {
                dijit.byId('description').set('value', pa.description);
            dijit.byId('result').set('value', pa.result);
            }
          else if (editorType=="Dojo") {   //NOT FUNCTIONNAL
            //dojo.byId('descriptionEditor').value = pa.description;
            //dojo.byId('dijitE').value = pa.description;
          }

        }
      }
    }
  });
}
//END ADD qCazelles - Predefined Action

function showDirectChangeStatus() {
  var divNode=dojo.byId('directChangeStatusDiv');
  if (! divNode) return;
  if (divNode.style.display=='block') {
    divNode.style.display='none';
  } else {
    divNode.style.display='block';
  }
}
function hideDirectChangeStatus() {
  var divNode=dojo.byId('directChangeStatusDiv');
  if (! divNode) return;
  if (divNode.style.display=='block') {
    divNode.style.display='none';
  }
}

function drawGraphStatus() {
  if (! dijit.byId("idStatus") || ! dojo.byId('objectClass') ) return;
  var callBack = function(){
    dojo.byId('graphStatusContentDiv');
  };
  graphIdStatus=dijit.byId("idStatus").get('value');
  graphIdProject=(dijit.byId("idProject"))?dijit.byId("idProject").get('value'):'';
  objectClass=dojo.byId('objectClass').value;
  graphIdType=dijit.byId("id"+objectClass+"Type").get('value');
  var url = '../tool/dynamicDialogGraphStatus.php?idStatus='+graphIdStatus + '&idProject='+graphIdProject + '&idType='+graphIdType;
  loadContent(url,"graphStatusDiv",null,null,null,null,null,callBack);
}

function hideGraphStatus(){
  var divNode=dojo.byId("graphStatusContentDiv");
  if (divNode){
    divNode.style.display="none";
  }
}


function scrollInto(){
  var scrollElmnt = dojo.byId("scrollToBottom");
  if(scrollElmnt){
    dojo.window.scrollIntoView('scrollToBottom');
  }
}

//*************************************************************************
//Access Imputation
//*************************************************************************
function accessImputationCallBack(){
  var callback=function(){
    if(dojo.byId('focusToday')){
    var valTest = dojo.byId('focusToday').value;
    dojo.window.scrollIntoView(valTest);
    dijit.byId(valTest).focus(); }  };
    showWait();
    return callback;
}


// *************************************************************************
// Activity Stream 
// *************************************************************************

function saveNoteStream(event){
  var key = event.keyCode;
  if (key == 13 && !event.shiftKey || (key == 83 && (navigator.platform.match("Mac") ? event.metaKey : event.ctrlKey) && ! event.altKey)) {
    var noteEditor = dijit.byId("noteNoteStream");
    var noteEditorContent=noteEditor.get("value");
    if (noteEditorContent.trim()=="") {
      noteEditor.focus();
      return;
    }
    loadContent("../tool/saveNoteStream.php", "resultDivMain", "noteFormStream", true, 'note',null,null);
    noteEditor.set("value",null);
    event.preventDefault();
  } 
}

var menuRightDivLastWidth=null;
var menuRightDivLastHeight=null;
function hideStreamMode(show,position,dimension,modeGlobal){
  if(dojo.byId('objetMultipleUpdate') && modeGlobal==true){
         return;
  }
  if(modeGlobal){
    loadDiv("menuUserScreenOrganization.php?paramActiveGlobal="+show,"mainDivMenu");
  }
  dijit.byId('iconMenuUserScreen').closeDropDown();
  if (! dijit.byId('detailRightDiv')) return;
  if(position=='bottom'){
    if (show=='true') {
      if (dijit.byId("detailRightDiv").h != '0') return;
    } else {
      if (dijit.byId("detailRightDiv").h == '0') return;
      menuRightDivLastHeight=dijit.byId("detailRightDiv").h;
      dimension=0;
    } 
    if (dimension && menuRightDivLastHeight) dimension=menuRightDivLastHeight;
    dijit.byId("detailRightDiv").resize({h : dimension});
    dijit.byId("centerDiv").resize();    
    //var detailHidden=false;
    //if (dojo.byId('detailBarShow') && dojo.byId('detailBarShow').style.display=='block') detailHidden=true;
    
  } else { // position='trailing'
    if (show=='true') {
      if (dijit.byId("detailRightDiv").w != '0') return;
    } else {
      if (dijit.byId("detailRightDiv").w == '0') return;
      menuRightDivLastWidth=dijit.byId("detailRightDiv").w;
      dimension=0;
    } 
    if (dimension && menuRightDivLastWidth) dimension=menuRightDivLastWidth;
    dijit.byId("detailRightDiv").resize({ w : dimension });
    dijit.byId("centerDiv").resize();
    //var detailHidden=false;
    //if (dojo.byId('detailBarShow') && dojo.byId('detailBarShow').style.display=='block') detailHidden=true;
  }
  loadContentStream(); 
  if (dimension==0) setTimeout("refreshObjectDivAfterResize();",100);
  else setTimeout('if (dojo.byId("buttonDiv")) loadContent("objectButtons.php?refreshButtons=true","buttonDiv", "listForm");',100);
}

function focusStream() {
  if(dijit.byId("noteNoteStream") && dijit.byId("noteNoteStream").get('value')==trim(i18n("textareaEnterText"))){
    dijit.byId("noteNoteStream").set('value',"");
  }
  if(dijit.byId("noteStreamKanban") && dijit.byId("noteStreamKanban").get('value')==trim(i18n("textareaEnterText"))){
  dijit.byId("noteStreamKanban").set('value',"");
  }
}

function refreshActivityStreamList(){
  loadContent('activityStreamList.php', 'activityStreamListDiv','activityStreamForm');
}

function resetActivityStreamListParameters() {
  dojo.byId('activityStreamShowClosed').value=1;
  switchActivityStreamListShowClosed();
  dijit.byId("activityStreamAuthorFilter").set('value',null);
  dijit.byId("activityStreamTypeNote").set('value',null);
  dijit.byId("activityStreamIdNote").set('value',null);
  dijit.byId("activityStreamNumberDays").set('value','7'); 
  dijit.byId("activityStreamTeamFilter").set('value',null);
}

function resetActivityStreamListParametersNewGui(){
 // dojo.byId('activityStreamShowClosed').value=1;
  //switchActivityStreamListShowClosed();
  dijit.byId("activityStreamAuthorFilter").set('value',null);
  dijit.byId("activityStreamTypeNote").set('value',null);
  dijit.byId("activityStreamIdNote").set('value',null);
  dijit.byId("activityStreamNumberDays").set('value','7'); 
  dijit.byId("activityStreamTeamFilter").set('value',null);
  if(dijit.byId('addRecentlySwitch').get('value')=='on'){
    dijit.byId("addRecentlySwitch").set('value','off');
  }
  if(dijit.byId('updatedRecentlySwitch').get('value')=='on'){
    dijit.byId("updatedRecentlySwitch").set('value','off');
  }
  if(dijit.byId('showIdleSwitchAS').get('value')=='on'){
    dijit.byId("showIdleSwitchAS").set('value','off');
  }
  if(dijit.byId('showOnlyNoteSwitch').get('value')=='on'){
    dijit.byId("showOnlyNoteSwitch").set('value','off');
  }
}

function switchActivityStreamListShowClosed() {
  var oldValue=dojo.byId('activityStreamShowClosed').value;
  if (oldValue==1) {
    dojo.byId('activityStreamShowClosed').value=0;
    if(dojo.byId('activityStreamShowClosedCheck')){
      dojo.byId('activityStreamShowClosedCheck').style.display='none';
    }
  } else {
    dojo.byId('activityStreamShowClosed').value=1;
    if(dojo.byId('activityStreamShowClosedCheck')){
      dojo.byId('activityStreamShowClosedCheck').style.display='inline-block';
    }
  }
  setTimeout("refreshActivityStreamList();", 100);
}

function switchActivityStreamListAddedRecently() {
  var oldValue=dojo.byId('activityStreamAddedRecently').value;
  if (oldValue=="added") {
    dojo.byId('activityStreamAddedRecently').value="";
    if(dojo.byId('activityStreamAddedRecentlyCheck')){
      dojo.byId('activityStreamAddedRecentlyCheck').style.display='none';
    }
  } else {
    dojo.byId('activityStreamAddedRecently').value="added";
    if(dojo.byId('activityStreamAddedRecentlyCheck')){
      dojo.byId('activityStreamAddedRecentlyCheck').style.display='inline-block';
    }
  }
  setTimeout("refreshActivityStreamList();", 100);
}


function switchActivityStreamListUpdatedRecently() {
  var oldValue=dojo.byId('activityStreamUpdatedRecently').value;
  if (oldValue=="updated") {
    dojo.byId('activityStreamUpdatedRecently').value="";
    if(dojo.byId('activityStreamUpdatedRecentlyCheck')){
      dojo.byId('activityStreamUpdatedRecentlyCheck').style.display='none';
    }
  } else {
    dojo.byId('activityStreamUpdatedRecently').value="updated";
    if(dojo.byId('activityStreamUpdatedRecentlyCheck')){
      dojo.byId('activityStreamUpdatedRecentlyCheck').style.display='inline-block';
    }
  }
}

function showOnlyNoteStream(){
  val=dojo.byId('showOnlyNotesValue').value;
  if(val=='NO'){
    val="YES";
    if(dojo.byId('showOnlyNotes')){
      dojo.byId('showOnlyNotes').style.display='inline-block';
    }
  }else{
    val="NO";
    if(dojo.byId('showOnlyNotes')){
      dojo.byId('showOnlyNotes').style.display='none';
    }
  }
  dojo.byId('showOnlyNotesValue').value=val;
  saveUserParameter('showOnlyNotes',val);
  setTimeout("refreshActivityStreamList();", 100);
}

function activityStreamTypeRead(){
  var typeNote = dijit.byId("activityStreamTypeNote").get('value');
  if(trim(typeNote) == ""){
    dijit.byId("activityStreamIdNote").set('value',null);
    dijit.byId("activityStreamIdNote").set('readOnly', true);
  } else {
    dijit.byId("activityStreamIdNote").set('readOnly', false);
  }
}

var notesHeight=[];
function switchNoteStatus(idNote) {
  var noteDiv=dojo.byId("activityStreamNoteContent_"+idNote);
  var status="closed";
  var img=dojo.byId('imgCollapse_'+idNote);
  if (!noteDiv.style.transition) {
    noteDiv.style.transition="all 0.5s ease";
    if (noteDiv.offsetHeight==0) {
      noteDiv.style.height="100px";
      noteDiv.style.maxHeight="100px";
      noteDiv.style.maxHeight="0px";
      noteDiv.style.height="0px";
      if (isNewGui) noteDiv.style.padding="0";
      setTimeout("switchNoteStatus("+idNote+")",10);
      return;
    } else {
      noteDiv.style.maxHeight=(noteDiv.offsetHeight)+"px";
      if (isNewGui) noteDiv.style.padding="5px 8px";
    }
  }
  if (noteDiv.style.height=='0px') {
    var newHeight=(idNote in notesHeight)?notesHeight[idNote]:"1000";
    noteDiv.style.maxHeight=newHeight+"px";
    noteDiv.style.height="100%";
    if (isNewGui) noteDiv.style.padding="5px 8px";
    noteDiv.style.marginBottom="10px";
    status="open";
    dojo.query('#imgCollapse_'+idNote+' div').forEach(function(node, index, arr){
      node.className="iconButtonCollapseHide16 iconButtonCollapseHide iconSize16";
    });
  } else {
    if (noteDiv.offsetHeight) notesHeight[idNote]=noteDiv.offsetHeight;
    noteDiv.style.maxHeight="0px";
    noteDiv.style.height="0px";
    if (isNewGui) noteDiv.style.padding="0";
    noteDiv.style.marginBottom="0px";
    status="closed";
    dojo.query('#imgCollapse_'+idNote+' div').forEach(function(node, index, arr){
      node.className="iconButtonCollapseOpen16 iconButtonCollapseOpen iconSize16";
    });
  }
  url="../tool/saveClosedNote.php?idNote="+idNote+"&statusNote="+status;
  dojo.xhrPost({
    url : url,
    load : function(data, args) {
    },
    error : function () {
      consoleTraceLog("error saving note status : "+url);
    }
 });
  
}
function switchNotesPrivacyStream() {
  if (! dojo.byId("notePrivacyStream") || !dojo.byId("notePrivacyStreamUserTeam") || !dojo.byId("notePrivacyStreamDiv") ) {
    return;
  }
  var privacy=dojo.byId("notePrivacyStream").value;
  var team=dojo.byId("notePrivacyStreamUserTeam").value;
  if (privacy=="2") {
    dojo.byId("notePrivacyStream").value="3";
    dojo.byId("notePrivacyStreamDiv").className="imageColorBlack iconFixed16 iconFixed iconSize16";
    dojo.byId("notePrivacyStreamDiv").title=i18n("colIdPrivacy")+" : "+i18n("private");
  } else if (privacy=="3") {
    dojo.byId("notePrivacyStream").value="1";
    dojo.byId("notePrivacyStreamDiv").className="";
    dojo.byId("notePrivacyStreamDiv").title=i18n("colIdPrivacy")+" : "+i18n("public");
  } else {
    if (team) {
      dojo.byId("notePrivacyStream").value="2";
      dojo.byId("notePrivacyStreamDiv").className="imageColorBlack iconTeam16 iconTeam iconSize16";
      dojo.byId("notePrivacyStreamDiv").title=i18n("colIdPrivacy")+" : "+i18n("team");
    } else {
      dojo.byId("notePrivacyStream").value="3";
      dojo.byId("notePrivacyStreamDiv").className="imageColorBlack iconFixed16 iconFixed iconSize16";
      dojo.byId("notePrivacyStreamDiv").title=i18n("colIdPrivacy")+" : "+i18n("private");
    }
  }
  var currentClass=(dojo.byId('objectClass'))?dojo.byId('objectClass').value:'';
  saveDataToSession("privacyNotes"+currentClass,dojo.byId("notePrivacyStream").value,true);
}

function setAttributeOnTitlepane(pane,attr,height) {
  if (height) attr+='height:'+height+'px';
  dojo.byId(pane+'_titleBarNode').style=attr;
}

function redirectMobile(){
  redirectMobileFunction = function() {
    var url = "../mobile/";  
    window.location = url;
    quitConfirmed = true;
  };
  showConfirm(i18n('confirmRedirectionMobile'), redirectMobileFunction);
}

function displayImageEditMessageMail(code){
  //var codeParam = code.name;
  var iconMessageMail = dojo.byId(code+'_iconMessageMail');
  iconMessageMail.style.display = "inline-block";
}

function hideImageEditMessageMail(code){
  //var codeParam = code.name;
  var iconMessageMail = dojo.byId(code+'_iconMessageMail');
  iconMessageMail.style.display = "none";
}

var timeoutDirectSelectProject=null;
function directSelectProject() {
  var selected=null;
  var selectedName=null; 
  if (dojo.byId("objectClass") && dijit.byId("idProject") && dijit.byId("id")) {
    if (dojo.byId("objectClass").value=='Project' && dijit.byId("id").value) {
      selected=dijit.byId("id").get("value");
      selectedName=dijit.byId("name").get("value");
    } else {
      selected=dijit.byId("idProject").get("value");
      selectedName=dijit.byId("idProject").get("displayedValue");
    }
  }
  if (selected) {
    if (dojo.byId('projectSelectorMode') && dojo.byId('projectSelectorMode').value=='Standard') {
      setSelectedProject(selected,selectedName,'selectedProject');
    } else {
      dijit.byId("projectSelectorFiletering").set("value",selected);
    }
  } else {
    showAlert(i18n("noCurrentProject"));
  }
  timeoutDirectSelectProject=null;
  hideWait();
}
function directUnselectProject() {
  clearTimeout(timeoutDirectSelectProject);
  if (dojo.byId('projectSelectorMode') && dojo.byId('projectSelectorMode').value=='Standard') { 
    setSelectedProject('*',i18n('allProjects'),'selectedProject'); 
  } else { 
    dijit.byId('projectSelectorFiletering').set('value','*'); 
  }
  timeoutDirectSelectProject=null;
  hideWait();
}

function refreshPlannedWorkManualList() {
    formInitialize();
    loadContent('../view/refreshPlannedWorkManualList.php', 'fullPlannedWorkManualList', 'listFormPlannedWorkManual', false);
}

function refreshConsultationPlannedWorkManualList() {
  formInitialize();
  loadContent('../view/refreshConsultationPlannedWorkManualList.php', 'fullConsPlannedWorkManualList', 'listFormConsPlannedWorkManual', false);
  return true;
}
//Absence list refresh function
function refreshAbsenceList() {
	if (checkFormChangeInProgress()) {
	    showAlert(i18n('alertOngoingChange'));
	    return false;
	  }
	  formInitialize();
	  loadContent('../view/refreshAbsenceList.php', 'fullWorkDiv', 'listForm', false);
	  return true;
}

//Absence calendar refresh function
function refreshAbsenceCalendar(tabColor) {
	if (checkFormChangeInProgress()) {
	    showAlert(i18n('alertOngoingChange'));
	    return false;
	  }
	  formInitialize();
	  showWait();
	  var callback=function() {
	    hideWait();
	  };
	  loadDiv('../view/refreshAbsenceCalendar.php', 'calendarDiv', 'listForm', callback);
	  return true;
}

// Absence activity selection function
function selectActivity(actRowId, actId, idProject, assId){
	var row = dojo.byId(actRowId);
	if (dojo.hasClass(row,'absActivityRow')){
		dojo.query('.absActivityRow').removeClass('dojoxGridRowSelected', row);
		dojo.addClass(row,'dojoxGridRowSelected');
		dojo.setAttr('inputActId', 'value', actId);
		dojo.setAttr('inputIdProject', 'value', idProject);
		dojo.setAttr('inputAssId', 'value', assId);
	}
	dojo.byId('warningNoActivity').style.display = 'none';
	saveDataToSession('selectAbsenceActivity', actId);
	saveDataToSession('inputIdProject', idProject);
	saveDataToSession('inputAssId', assId);
}

// Absence day selection fonction
function selectAbsenceDay(dateId, day, workDay, month, year, week, userId, isValidated){
	var workVal = dijit.byId('absenceInput').get('value');
	var actId = dojo.byId('inputActId').value;
	var idProject = dojo.byId('inputIdProject').value;
	var assId = dojo.byId('inputAssId').value;
	if(!isValidated){
		dojo.byId('warningisValiadtedDay').style.display = 'none';
		if(actId == ""){
			dojo.byId('warningNoActivity').style.display = 'block';
		}else {
		  showWait();
			dojo.byId('warningNoActivity').style.display = 'none';
			var url='../tool/saveAbsence.php?day='+day+'&workDay='+workDay+'&month='+month+'&year='+year+'&week='+week+'&userId='+userId+'&workVal='+workVal+'&actId='+actId+'&idProject='+idProject+'&assId='+assId;
			  dojo.xhrGet({
			    url : url,
			    handleAs : "text",
			    load : function(data){
			      hideWait();
			    	refreshAbsenceCalendar();
			    	if(data == 'warning'){
			    		dojo.byId('warningExceedWork').style.display = 'block';
			    		setTimeout("dojo.byId('warningExceedWork').style.display = 'none'", 2000);
			    	}else if( data == 'warningPlanned'){
			    	  dojo.byId('warningExceedWorkWithPlanned').style.display = 'block';
              setTimeout("dojo.byId('warningExceedWorkWithPlanned').style.display = 'none'", 2000);
			    	}
			    }
			  });
		}
	}else{
		dojo.byId('warningisValiadtedDay').style.display = 'block';
		setTimeout("dojo.byId('warningisValiadtedDay').style.display = 'none'", 2000);
	}
}

//Imputation Validation refresh function
function refreshImputationValidation(startDate, endDate) {
  if (startDate) {
//    var year=directDate.getFullYear();
//    var week=getWeek(directDate.getDate(),directDate.getMonth()+1,directDate.getFullYear())+'';
//    if (week==1 && directDate.getMonth()>10) {
//      year+=1;
//    }
//    if (week.length==1 || parseInt(week,10)<10) {
//      week='0' + week;
//    }
//    if (week=='00') {
//      week=getWeek(31,12,year-1);
//      if (week==1) {
//        var day=getFirstDayOfWeek(1,year);
//        week=getWeek(day.getDate()-1,day.getMonth()+1,day.getFullYear());
//      }
//      year=year-1;
//      //dijit.byId('yearSpinner').set('value',year);
//      //dijit.byId('weekSpinner').set('value', week);
//    } else if (parseInt(week,10)>53) {
//      week='01';
//      year+=1;
//      //dijit.byId('yearSpinner').set('value', year);
//      //dijit.byId('weekSpinner').set('value', week);
//    } else if (parseInt(week,10)>52) {
//      lastWeek=getWeek(31,12,year);
//      if (lastWeek==1) {
//        var day=getFirstDayOfWeek(1,year+1);
//        //day=day-1;
//        lastWeek=getWeek(day.getDate()-1,day.getMonth()+1,day.getFullYear());
//      }
//      if (parseInt(week,10)>parseInt(lastWeek,10)) {
//        week='01';
//        year+=1;
//        //dijit.byId('yearSpinner').set('value', year);
//        //dijit.byId('weekSpinner').set('value', week);
//      }
//    }
    
    var day=getFirstDayOfWeekFromDate(startDate);
    dijit.byId('startWeekImputationValidation').set('value',day);
  }
  if (endDate) {
    if (startDate && startDate>endDate) endDate=startDate;
    var day=getFirstDayOfWeekFromDate(endDate);
    day=addDaysToDate(day,6),
    dijit.byId('endWeekImputationValidation').set('value',day);
  }
  formInitialize();
  showWait();
  var callback=function() {
    hideWait();
  };
  loadContent('../view/refreshImputationValidation.php', 'imputationValidationWorkDiv', 'imputValidationForm', false,false,false,false,callback,false);
  return true;
}

function refreshSubmitValidateDiv(idWorkPeriod, buttonAction) {
	formInitialize();
	showWait();
	var callback=function() {
		hideWait();
	};
	if(buttonAction == 'validateWork' || buttonAction == 'cancelValidation'){
		loadContent('../view/refreshSubmitValidateDiv.php','validatedDiv'+idWorkPeriod,false,false,false,false,false,callback,false);
	}else{
		loadContent('../view/refreshSubmitValidateDiv.php','submittedDiv'+idWorkPeriod,false,false,false,false,false,callback,false);
	}
}

//Imputation Validation Save function
function saveImputationValidation(idWorkPeriod, buttonAction){
	saveDataToSession('idWorkPeriod', idWorkPeriod, false);
	saveDataToSession('buttonAction', buttonAction, false);
	showWait();
	var url='../tool/saveImputationValidation.php?idWorkPeriod='+idWorkPeriod+'&buttonAction='+buttonAction;
	  dojo.xhrGet({
	    url : url,
	    handleAs : "text",
	    load : function(data){
	      hideWait();
	      if(buttonAction != 'validateSelection'){
	    	  refreshSubmitValidateDiv(idWorkPeriod, buttonAction);
	      }
	      if(buttonAction == 'validateSelection'){
	    	  refreshImputationValidation(null);
	      }
	      if(buttonAction == 'cancelSubmit'){
	  		cancelSubmitbyOther(idWorkPeriod);
	  	  }
	    }
	  });
}

function cancelSubmitbyOther(idWorkPeriod) {
  var url='../tool/sendMail.php?className=Imputation&action=cancelSubmitByOther&idWorkPeriod='+idWorkPeriod;
	  dojo.xhrGet({
	    url : url,
	    handleAs : "text",
	    load : function(){
	    	
	    }
	  });
}

function imputationValidationSelection(){
	var countLine = dojo.byId('countLine').value;
	for(var i=1; i<=countLine; i++){
		if(dojo.byId('validCheckBox'+i) && dojo.byId('validatedLine'+i).value == '0'){
			dijit.byId('validCheckBox'+i).set("checked", dijit.byId('selectAll').get('checked'));
		}
	}
}

function validateAllSelection(){
	var countLine = dojo.byId('countLine').value;
	var listId = '';
	if(countLine > 0){
		for(var i=1; i<=countLine; i++){
			if(dijit.byId('validCheckBox'+i) && dojo.byId('validatedLine'+i).value == '0' && dijit.byId('validCheckBox'+i).get('checked') == true){
				listId += dojo.byId('validatedLine'+i).name+',';
			}
		}
		if(listId != ''){
			listId = listId.substr(0, listId.length-1);
			saveImputationValidation(listId, 'validateSelection');
		}
	}
}

function refreshAutoSendReportList(idUser) {
	formInitialize();
	showWait();
	var callback=function() {
		hideWait();
	};
	loadContent('../view/refreshAutoSendReportList.php', 'autoSendReportWorkDiv', 'autoSendReportListForm', false,false,false,false,callback,false);
}

function activeAutoSendReport(idSendReport){
	dojo.byId("idSendReport").value = idSendReport;
	var idle = dijit.byId('activeCheckBox'+idSendReport).get('checked');
	showWait();
	var url='../tool/saveAutoSendReport.php?action=changeStatus&idle='+idle+'&idSendReport='+idSendReport;
	  dojo.xhrGet({
	    url : url,
	    handleAs : "text",
	    load : function(){
	    	hideWait();
	    	refreshAutoSendReportList(null);
	    }
	  });
}

function removeAutoSendReport(idSendReport){
	dojo.byId("idSendReport").value = idSendReport;
	action=function(){
		showWait();
		var url='../tool/saveAutoSendReport.php?action=delete&idSendReport='+idSendReport;
		  dojo.xhrGet({
		    url : url,
		    handleAs : "text",
		    load : function(){
		    	hideWait();
		    	refreshAutoSendReportList(null);
		    }
		  });
	}
  showConfirm(i18n('removeAutoSendReport') ,action);
}

function selectedMultiProject(){
	var nameProject = null;
	arraySelectedProject.splice(0);
	dojo.query(".projectSelectorCheckbox").forEach(function(node, index, nodelist) {
	    if(dijit.byId(node.getAttribute('widgetid')).get('checked')){
	    	arraySelectedProject.push(dijit.byId(node.getAttribute('widgetid')).get('value'));
	    }
	  });
	if(arraySelectedProject.length == 0){
		arraySelectedProject.push('*');
		nameProject = '<i>'+i18n('allProjects')+'</i>';
	}
	if(arraySelectedProject != null){
		if(arraySelectedProject.length ==  1){
			if(dojo.byId('projectSelectorName'+arraySelectedProject[0])){
				nameProject = dojo.byId('projectSelectorName'+arraySelectedProject[0]).value;
			}else{
				nameProject = '<i>'+i18n('allProjects')+'</i>';
			}
			setSelectedProject(arraySelectedProject[0], nameProject, 'selectedProject');
		}else{
			nameProject = '<i>'+i18n('selectedProject')+'</i>';
			setSelectedProject(arraySelectedProject.flat(), nameProject, 'selectedProject');
		}
	}
}

function refreshDataCloningList() {
	formInitialize();
	var callback=function() {
		hideWait();
	};
	loadDiv('../view/refreshDataCloningCount.php', 'dataCloningRequestorCount', 'dataCloningListForm');
	loadContent('../view/refreshDataCloningList.php', 'dataCloningWorkDiv', 'dataCloningListForm', false,false,false,false,callback,false);
}

function saveDataCloning(){
	var formVar=dijit.byId('addDataCloningForm');
	  if (dijit.byId('dataCloningUser').get('value') == '' || dijit.byId('dataCloningName').get('value') == '') {
		  showAlert(i18n("alertInvalidForm"));
	      return;
	  }
      callback=function() {
    	  hideWait();
    	  refreshDataCloningList();
	  };
	  if(formVar.validate()) {
		  showWait();
		  loadContent("../tool/saveDataCloning.php", "resultDivMain", "addDataCloningForm", true, false, false, false, callback);
		  dijit.byId('dialogAddDataCloning').hide();
	  } else {
	    showAlert(i18n("alertInvalidForm"));
	  }
}

function removeDataCloningStatus(idDataCloning){
	action=function(){
		showWait();
		var url='../tool/saveDataCloning.php?status=remove&idDataCloning='+idDataCloning;
		  dojo.xhrGet({
		    url : url,
		    handleAs : "text",
		    load : function(){
		    	hideWait();
		    	refreshDataCloningList();
		    }
		  });
	}
  showConfirm(i18n('removeDataCloning') ,action);
}

function cancelDataCloningStatus(idDataCloning){
	action=function(){
		showWait();
		var url='../tool/saveDataCloning.php?status=cancel&idDataCloning='+idDataCloning;
		  dojo.xhrGet({
		    url : url,
		    handleAs : "text",
		    load : function(){
		    	hideWait();
		    	refreshDataCloningList();
		    }
		  });
	}
  showConfirm(i18n('cancelDataCloning') ,action);
}

function refreshDataCloningError(idDataCloning, codeError){
	//action=function(){
		showWait();
		var url='../tool/saveDataCloning.php?status=reset&codeError='+codeError+'&idDataCloning='+idDataCloning;
		  dojo.xhrGet({
		    url : url,
		    handleAs : "text",
		    load : function(){
		    	hideWait();
		    	refreshDataCloningList();
		    }
		  });
	//}
  //showConfirm(i18n('refreshDataCloning') ,action);
}

function showSpecificCreationRequest(){
	var value = dijit.byId('dataCloningCreationRequest').get('value');
	if(value == 'specificHours'){
		dijit.byId("dataCloningSpecificHours").domNode.style.display = 'block';
	}else{
		dijit.byId("dataCloningSpecificHours").domNode.style.display = 'none';
	}
	if(value == 'immediate'){
		dijit.byId("dataCloningSpecificFrequency").domNode.style.display = 'block';
	}else{
		dijit.byId("dataCloningSpecificFrequency").domNode.style.display = 'none';
	}
}

function resizeListDiv() {
  var width = dojo.byId("listDiv").offsetWidth;
  dojo.query(".allSearchTD").forEach(function(node, index, nodelist) { node.style.display="table-cell";});
  
  var arrayFields={
      "name":{"set":false,"visible":true,"fixWidth":0,"size":2},
      "id":{"set":false,"visible":true,"fixWidth":0,"size":1},
      "type":{"set":false,"visible":true,"fixWidth":0,"size":3},
      "idle":{"set":false,"visible":true,"fixWidth":0,"size":0},
      "reset":{"set":false,"visible":true,"fixWidth":0,"size":0},
      "client":{"set":false,"visible":true,"fixWidth":0,"size":3},
      "parentBudget":{"set":false,"visible":true,"fixWidth":0,"size":3},
      "element":{"set":false,"visible":true,"fixWidth":0,"size":3}
  };
  var arrayFieldsOrder=["reset","element","client","parentBudget","type","idle","id","name"];
  // Reset all fields length to 10px (minimum)
  for (var i=0;i<arrayFieldsOrder.length;i++) {
    var fld=arrayFieldsOrder[i];
    if (arrayFields[fld]["size"]==0) continue;
    var widgetId="#widget_list"+fld[0].toUpperCase()+fld.substring(1)+"Filter";
    dojo.query(widgetId).forEach(function(node, index, nodelist) { node.style.width="10px";});  
  }
  
  // Count size for Fixed items (labels, reset, idle) and sum 'size' for each displayed item
  var fixedLenghtPart=0;
  var variableSize=0;
  dojo.query(".allSearchFixLength").forEach(function(node, index, nodelist) { 
      var nodeWidth=(node.offsetWidth)?node.offsetWidth+5:0;
      if (isNewGui && node.hasChildNodes() && node.childNodes[1] && node.childNodes[1] && node.childNodes[1].style.display=='none') {
        // Do not count hidden
      } else {
        fixedLenghtPart+=nodeWidth;
        for (var fld in arrayFields) {
          if (isNewGui && fld=='reset') continue; // Do not count reset : on pop-up
          var cls=fld+"SearchTD";
          if (dojo.hasClass(node,cls)) {
            arrayFields[fld]["set"]=true;
            arrayFields[fld]["fixWidth"]=nodeWidth;
            variableSize+=arrayFields[fld]["size"];
          }
        }      
      }
  });
  
  fixedLenghtPart+=dojo.byId("classNameSpan").offsetWidth; // Add length of Class Name
  fixedLenghtPart+=75; // Add size of icon (42) + small margin
  //if (isNewGui) fixedLenghtPart+=50;
  var leftWidth=width-fixedLenghtPart;
  var minSize=25;
  var cptLoop=0;
  if (isNewGui) {
    // Nothing, hidden fileds already taken into account
  } else {
    while (minSize*variableSize>leftWidth && cptLoop<20) {
      for (var i=0;i<arrayFieldsOrder.length;i++) {
        var fld=arrayFieldsOrder[i];
        if (arrayFields[fld]["set"]==true && arrayFields[fld]["visible"]==true) {
          arrayFields[fld]["visible"]=false;
          variableSize-=arrayFields[fld]["size"];
          leftWidth+=arrayFields[fld]["fixWidth"];
          break; // Check if display is possible
        }  
      }
      cptLoop++;
    }
  }
  var finalSize=Math.floor(leftWidth/variableSize);
  if (isNewGui) finalSize-=10;
  if (isNewGui && finalSize<minSize) finalSize=minSize;
  if (finalSize>100) finalSize=100;
  if (isNewGui && finalSize>90) finalSize=90;
  for (var i=0;i<arrayFieldsOrder.length;i++) {
    var fld=arrayFieldsOrder[i];
    if (arrayFields[fld]["visible"]==false && ! isNewGui) {
      dojo.query("."+fld+"SearchTD").forEach(function(node, index, nodelist) { node.style.display="none";});
    } else {
      var widgetId="#widget_list"+fld[0].toUpperCase()+fld.substring(1)+"Filter";
      var fldWidth=finalSize*arrayFields[fld]["size"];
      dojo.query(widgetId).forEach(function(node, index, nodelist) { 
        node.style.width=(fldWidth)+"px";
        node.style.maxWidth=(fldWidth)+"px";
      });  
    }
    
  }
}

var donotSaveResize=false;
var hideEmptyDetail=false;
function checkValidatedSize(paramDiv,paramRightDiv, paramMode){
  var minRight=400;
  if (donotSaveResize) return;
  if (isNewGui) minRight=500;
  if (paramMode != 'switch'){
    if (!dojo.byId('detailRightDiv')) return;
    if (paramDiv== 'left'){
      if(hideEmptyDetail && dojo.byId("contentDetailDiv") && dojo.byId("noDataInObjectDetail")) {
        donotSaveResize=true;
        var listWidth=(dojo.byId("centerDiv").offsetWidth);
        dijit.byId("listDiv").resize({w: listWidth});
        resizeContainer("mainDivContainer", null);
        setTimeout("donotSaveResize=false",1000);
        return true;
      }  
	    if (! dojo.byId('detailRightDiv') || dojo.byId('detailRightDiv').offsetWidth==0 || paramRightDiv=='bottom'){
	      if (dojo.byId("contentDetailDiv").offsetWidth<minRight) {
	        var listWidth=(dojo.byId("centerDiv").offsetWidth)-(minRight+10);
	        dijit.byId("listDiv").resize({w: listWidth});
	        resizeContainer("mainDivContainer", null);
	        return true;
	      }
	    } else {
  	    if((dojo.byId("contentDetailDiv").offsetWidth - dojo.byId('detailRightDiv').offsetWidth) < minRight){
  	      var detailRightWidth=(dojo.byId('contentDetailDiv').offsetWidth)-(minRight+10);
  	      var listWidth=dojo.byId('centerDiv').offsetWidth-dojo.byId('contentDetailDiv').offsetWidth;
  	      if(150 > detailRightWidth){
  	        detailRightWidth=150;
  	        listWidth=(dojo.byId("centerDiv").offsetWidth)-(minRight+detailRightWidth+10);
  	      }
  	      dijit.byId('listDiv').resize({w:listWidth});
  	      dijit.byId('detailRightDiv').resize({w:detailRightWidth});
  	      resizeContainer("mainDivContainer", null);
  	      return true;
  	    }
  	  }
	  } else {
	    if(dojo.byId('detailRightDiv').offsetHeight==0 || paramRightDiv=='trailing'){
	      if (dojo.byId("contentDetailDiv").offsetHeight<250) {
	        var listWidth=(dojo.byId("centerDiv").offsetHeight)-260;
	        dijit.byId("listDiv").resize({h: listWidth});
	        resizeContainer("mainDivContainer", null);
	        return true;
	      }
	    }else{
	      if(dojo.byId("contentDetailDiv") && dojo.byId('detailRightDiv') && (dojo.byId("contentDetailDiv").offsetHeight - dojo.byId('detailRightDiv').offsetHeight) < 250){
	        var detailRightHeight=(dojo.byId('contentDetailDiv').offsetHeight)-260;
	        var listHeight=dojo.byId('centerDiv').offsetHeight-dojo.byId('contentDetailDiv').offsetHeight;
	        if(130 > detailRightHeight){
	          detailRightHeight=130;
	          listHeight=(dojo.byId("centerDiv").offsetHeight)-390;
	        }
	        dijit.byId('listDiv').resize({h:listHeight});
	        dijit.byId('detailRightDiv').resize({h:detailRightHeight});
	        resizeContainer("mainDivContainer", null);
		        return true;
		      }
		    }
		  }
	  }else{
		  return;
	  }
}

function checkValidatedSizeRightDiv(paramDiv,paramRightDiv, paramMode){
  if (donotSaveResize) return;
	if(paramMode !='switch'){
	  if (!dojo.byId('detailRightDiv')) return;
		if(paramDiv== 'left'){
		    if(((dojo.byId("contentDetailDiv").offsetWidth - dojo.byId('detailRightDiv').offsetWidth) < 400) && paramRightDiv=='trailing' && dojo.byId('detailRightDiv').offsetWidth>150){
		      var detailRightWidth=(dojo.byId("contentDetailDiv").offsetWidth)-410;
		      if(150 > detailRightWidth){
		        detailRightWidth=150;
		      }
		      dijit.byId('detailRightDiv').resize({w:detailRightWidth});
		      resizeContainer("mainDivContainer", null);
		      return true;
		    }
		 }else {
		   if(((dojo.byId("contentDetailDiv").offsetHeight - dojo.byId('detailRightDiv').offsetHeight) < 250 )&& paramRightDiv=='bottom' && dojo.byId('detailRightDiv').offsetHeight >130){
		     var detailRightHeight=(dojo.byId("contentDetailDiv").offsetHeight)-260;
		     if(130 > detailRightHeight){
		       detailRightHeight=130;
		     }
		     dijit.byId('detailRightDiv').resize({h:detailRightHeight});
		     resizeContainer("mainDivContainer", null);
		     return true;
		   }
		 }
	}else{
		return;
	}	
}


function hideSplitterStream (paramDiv){
  if(paramDiv=='trailing'){
    if(dojo.byId("detailRightDiv").offsetWidth == 0){
      dojo.query('#detailRightDiv_splitter').forEach(function(node, index, nodelist) {
       node.style.display = 'none';
      });
    }else{
      dojo.query('#detailRightDiv_splitter').forEach(function(node, index, nodelist) {
       node.style.display = 'block';
      });
    }
  }else{
    if(dojo.byId("detailRightDiv").offsetHeight == 0){
      dojo.query('#detailRightDiv_splitter').forEach(function(node, index, nodelist) {
       node.style.display = 'none';
      });
    }else{
      dojo.query('#detailRightDiv_splitter').forEach(function(node, index, nodelist) {
       node.style.display = 'block';
      });
    }
  }
}
function refreshObjectDivAfterResize() {
  if( multiSelection==false){
    if (!formChangeInProgress && dijit.byId('id')) { 
      setTimeout('loadContent("objectDetail.php", "detailDiv", "listForm",null,null,null,null,null,true);', 50); 
    } else {
      setTimeout('if (dojo.byId("buttonDiv")) loadContent("objectButtons.php?refreshButtons=true","buttonDiv", "listForm",false,false,false,false,'
                  +((formChangeInProgress)?'function() {formChanged();}':'null')
                  +',true);', 50);
    }
  } else if(multiSelection==true && formChangeInProgress==false){
    loadContent('objectMultipleUpdate.php?objectClass=' + dojo.byId('objectClass').value,'detailDiv',null,null,null,null,null,null,true);
  }
}
//florent 4299
function showListFilter(checkBoxName,value){
  if(checkBoxName=='planningVersionDisplayProductVersionActivity' ){
    if((value=='1' && displayFilterVersionPlanning!='1')){
      displayFilterVersionPlanning='1';
    }else{
      displayFilterVersionPlanning='0';
    }
  }
  if( displayFilterVersionPlanning=='0' && dojo.byId('listDisplayProductVersionActivity').checked==true){
    displayFilterVersionPlanning='1';
  }
  if((checkBoxName=='planningVersionDisplayComponentVersionActivity') ){
    if( (value=='1' && displayFilterComponentVersionPlanning!='1')  ){
      displayFilterComponentVersionPlanning='1';
    }else{
      displayFilterComponentVersionPlanning='0';
    }
  }
  if( displayFilterComponentVersionPlanning=='0' && dojo.byId('listDisplayComponentVersionActivity').checked==true){
    displayFilterComponentVersionPlanning='1';
  }
  if((displayFilterVersionPlanning=='0' && displayFilterComponentVersionPlanning=='0')){
    selectStoredFilter('0','directFilterList');
    dojo.byId('listFilterAdvanced').style.visibility="hidden";
    dojo.byId('displayRessource').style.visibility="hidden";
    dojo.byId('displayRessourceCheck').style.visibility="hidden";
    dojo.byId('versionsWithoutActivity').style.visibility="hidden";
    dojo.byId('hideVersionsWithoutActivityCheck').style.visibility="hidden";
    dojo.byId('addNewActivity').style.visibility="hidden";
    dojo.byId('versionsWithoutActivity').style.visibility="hidden";
    dojo.byId('hideOneTimeActivitiesLabel').style.visibility="hidden";
    dojo.byId('hideOneTimeActivitiesCheck').style.visibility="hidden";
    dojo.byId('hideProjectLevelLabel').style.visibility="hidden";
    dojo.byId('hideProjectLevelCheck').style.visibility="hidden";
    dojo.byId('hideActivityHierarchyLabel').style.visibility="hidden";
    dojo.byId('hideActivityHierarchyCheck').style.visibility="hidden";
  }else{
    dojo.byId('listFilterAdvanced').style.visibility="visible";
    dojo.byId('displayRessource').style.visibility="visible";
    dojo.byId('displayRessourceCheck').style.visibility="visible";
    dojo.byId('versionsWithoutActivity').style.visibility="visible";
    dojo.byId('hideVersionsWithoutActivityCheck').style.visibility="visible";
    dojo.byId('addNewActivity').style.visibility="visible";
    dojo.byId('hideOneTimeActivitiesLabel').style.visibility="visible";
    dojo.byId('hideOneTimeActivitiesCheck').style.visibility="visible";
    dojo.byId('hideProjectLevelLabel').style.visibility="visible";
    dojo.byId('hideProjectLevelCheck').style.visibility="visible";
    dojo.byId('hideActivityHierarchyLabel').style.visibility="visible";
    dojo.byId('hideActivityHierarchyCheck').style.visibility="visible";
  }
}

var dropFilesFormInProgress=null;
function dropFilesFormOnDragOver() {
  event.preventDefault();
  if (dropFilesFormInProgress) clearTimeout(dropFilesFormInProgress);
  if (dojo.byId('updateRight') && dojo.byId('updateRight').value=='NO') return;
  if (! dojo.byId('id')) return;
  if (!dojo.byId('dropFilesInfoDiv')) return;
  if (!dojo.byId('attachmentFileDirectDiv')) return; 
  if ( dijit.byId('idle') && dijit.byId('idle').get('checked')==true) return;
  dojo.byId('dropFilesInfoDiv').style.height=(dojo.byId('formDiv').offsetHeight-10)+"px";
  var hasScrollBar=(dojo.byId('formDiv').scrollHeight>dojo.byId('formDiv').clientHeight)?true:false;
  var removeWidth=(hasScrollBar)?25:10;
  dojo.byId('dropFilesInfoDiv').style.width=(dojo.byId('formDiv').offsetWidth-removeWidth)+"px";
  dojo.byId('dropFilesInfoDiv').style.top=(dojo.byId('formDiv').scrollTop)+"px";
  dojo.byId('dropFilesInfoDiv').style.display='block';
  dojo.byId('dropFilesInfoDiv').style.opacity='50%';
}
function dropFilesFormOnDragLeave() {
  event.preventDefault();
  if (dropFilesFormInProgress) clearTimeout(dropFilesFormInProgress);
  dropFilesFormInProgress=setTimeout("dojo.byId('dropFilesInfoDiv').style.display='none';",100);
}
function dropFilesFormOnDrop() {
  event.preventDefault();
  if (dropFilesFormInProgress) clearTimeout(dropFilesFormInProgress);
  dojo.byId('dropFilesInfoDiv').style.opacity='0%';
  dojo.byId('dropFilesInfoDiv').style.display='none';
}

function refreshHierarchicalBudgetList(){
	showWait();
	callback=function(){
		hideWait();
		if (dijit.byId('listDiv')) dijit.byId('listDiv').resize();
	}
	loadContent("../view/refreshHierarchicalBudgetList.php", "hierarchicalListDiv", null, false, null, null, null, callback, null);
}

function expandHierarchicalBudgetGroup(idBudget, subBudget, recSubBudget, visibleRow){
	var recSubBudgetList = recSubBudget.split(',');
	var visibleRowList = visibleRow.split(',');
	var subBudgetList = subBudget.split(',');
	var budgetClass = dojo.attr('group_'+idBudget, 'class');
	if(visibleRowList == ''){
		visibleRowList = subBudgetList;
	}
	if(budgetClass == 'ganttExpandClosed'){
		visibleRowList.forEach(function(item){
			saveExpanded('hierarchicalBudgetRow_'+idBudget);
			if (dojo.byId('hierarchicalBudgetRow_'+item)) dojo.byId('hierarchicalBudgetRow_'+item).style.display = 'table-row';
			if (dojo.byId('group_'+idBudget)) dojo.setAttr('group_'+idBudget, 'class', 'ganttExpandOpened');
		});
		//refreshHierarchicalBudgetList();
	}else{
		recSubBudgetList.forEach(function(item){
			saveCollapsed('hierarchicalBudgetRow_'+idBudget);
			if (dojo.byId('hierarchicalBudgetRow_'+item)) dojo.byId('hierarchicalBudgetRow_'+item).style.display = 'none';
			if (dojo.byId('group_'+idBudget)) dojo.setAttr('group_'+idBudget, 'class', 'ganttExpandClosed');
		});
	}
}

function expandAssetGroup(idAsset, subAsset,recSubAsset){
  var recSubAsset = recSubAsset.split(',');
  var subBudgetList = subAsset.split(',');
  var budgetClass = dojo.attr('group_'+idAsset, 'class');
  if(budgetClass == 'ganttExpandClosed'){
    if (dojo.byId('group_'+idAsset)) dojo.setAttr('group_'+idAsset, 'class', 'ganttExpandOpened');
    subBudgetList.forEach(function(item){
      if (dojo.byId('assetStructureRow_'+item)) dojo.byId('assetStructureRow_'+item).style.display = 'table-row';
    });
  }else{
    if (dojo.byId('group_'+idAsset)){
      dojo.setAttr('group_'+idAsset, 'class', 'ganttExpandClosed');
    }
    recSubAsset.forEach(function(item){
      if (dojo.byId('assetStructureRow_'+item)){
        dojo.byId('assetStructureRow_'+item).style.display = 'none';
        if (dojo.attr('group_'+item, 'class') == 'ganttExpandOpened'){
          dojo.setAttr('group_'+item, 'class', 'ganttExpandClosed');
        }
      }
    });
  }
}
function switchAddRemoveDaytoDate(unit,date,val,operator){
  var newDate
  switch (unit) { 
  case '1': 
            
            if(operator=='+'){
              newDate=addDaysToDate(date,val);
              dijit.byId('endDate').set('value',newDate);
            }else{
              newDate=addDaysToDate(date,-val);
              dijit.byId('noticeDate').set('value',newDate);
            }
            
            break;
  case '2':
            newDate= new Date(date);
            var addJ=-1;
            if(operator=='+'){
              if(val==0)addJ=0;
              newDate.setMonth(date.getMonth()+val);
              dijit.byId('endDate').set('value',addDaysToDate(newDate,addJ));
            }else{
              newDate.setMonth(date.getMonth()-val);
              if(val==0)addDaysToDate(newDate,-1);
              dijit.byId('noticeDate').set('value',newDate);
            }
            break;
  case '3':
            newDate= new Date(date);
            if(operator=='+'){
              var addJ=-1;
              if(val==0)addJ=0;
              newDate.setFullYear(date.getFullYear()+val);
              dijit.byId('endDate').set('value',addDaysToDate(newDate,addJ));
            }else{
              newDate.setFullYear(date.getFullYear()-val);
              if(val==0)addDaysToDate(newDate,-1);
              dijit.byId('noticeDate').set('value',newDate);
            }
            break;
  } 
}


function setDatesContract(val){
  var endDate= new Date (dijit.byId('endDate').getValue());
  var startDate=new Date (dijit.byId('startDate').getValue());
  var noticeDate=new Date (dijit.byId('noticeDate').getValue());
  var reelEndDate=addDaysToDate(endDate,1);
  var initialContractTermVal=dijit.byId('initialContractTerm').getValue();
  var unitDuration=dijit.byId('idUnitContract').getValue();
  var noticePeriod=dijit.byId('noticePeriod').getValue();
  var idUnitNotice=dijit.byId('idUnitNotice').getValue();
  var dayEndDate=0;
  var MonthEnd=0;
  var dayStartDate=0;
  var MonthStart=0;
  var dayNoticeDate=0;
  var MonthNotice=0;
  if(reelEndDate!=''){
    var dayEndDate=reelEndDate.getDate();
    var MonthEnd=reelEndDate.getMonth();
  }
  if(startDate!=''){
    var dayStartDate=startDate.getDate();
    var MonthStart=startDate.getMonth();
  }
  if(noticeDate!=''){
    var dayNoticeDate=noticeDate.getDate();
    var MonthNotice=noticeDate.getMonth();
  }
  var monthYear=0;
  if (val=='startDate') {
      if( initialContractTermVal && initialContractTermVal != 0 ){ 
        switchAddRemoveDaytoDate(unitDuration,startDate,initialContractTermVal,'+');
      } 
  }else if(val=='idUnitContract'){
      if( (initialContractTermVal  && initialContractTermVal!= 0) && (startDate != undefined)  ){ 
        switchAddRemoveDaytoDate(unitDuration,startDate,initialContractTermVal,'+');
      } 
  }else if(val=='initialContractTerm'){
      if( startDate != undefined ){ 
        switchAddRemoveDaytoDate(unitDuration,startDate,initialContractTermVal,'+');
      } 
  }else if(val=='endDate'){
      if( startDate != undefined){
        if( dayStartDate == dayEndDate && MonthStart==MonthEnd && startDate.getYear()!= reelEndDate.getYear()){
          var nbY =0;
          var newDY=0;
          if(dijit.byId('idUnitContract').getValue==3 && dijit.byId('initialContractTerm').getValue!=''){
            nbY=dijit.byId('initialContractTerm').getValue();
          }else{
            var yearStartDate=startDate.getYear();
            days=dayDiffDates(startDate,reelEndDate);
            for(var i=0;i<days;i++){
              newDate=addDaysToDate(startDate,+1);
              newDY=newDate.getYear();
              if(yearStartDate!=newDY){
                nbY++;
                yearStartDate=newDY;
              }
            }
          }
         setTimeout(dijit.byId('idUnitContract').set('value',3),500);
         setTimeout(dijit.byId('initialContractTerm').set('value',nbY),500);
        }else if( dayStartDate == dayEndDate && MonthStart!=MonthEnd ) {
          var nbM =0;
          if(dijit.byId('idUnitContract').getValue==2 && dijit.byId('initialContractTerm').getValue!=''){
            nbM =dijit.byId('initialContractTerm').getValue();
          }else{
            var newDM=0;
            var monthStartDate=startDate.getMonth();
            days=dayDiffDates(startDate,reelEndDate);
            for(var i=0;i<days;i++){
              newDate=addDaysToDate(startDate,+1);
              newDM=newDate.getMonth();
              if(monthStartDate!=newDM){
                nbM++;
                monthStartDate=newDM;
              }
            }
          }
          setTimeout(dijit.byId('idUnitContract').set('value',2),500);
          setTimeout(dijit.byId('initialContractTerm').set('value',nbM),500);
        }else { 
          var nbJ=(dayDiffDates(startDate,endDate))-1;
          dijit.byId('idUnitContract').set('value',1);
          dijit.byId('initialContractTerm').set('value',nbJ);
        }
      }
      if( noticePeriod != 0 && idUnitNotice!= undefined){
        switchAddRemoveDaytoDate(idUnitNotice,endDate,noticePeriod,'-');
      }
  }else if(val=='noticeDate') {
      if( endDate != undefined ){
        if( dayNoticeDate == dayEndDate &&  MonthNotice==MonthEnd && noticeDate.getYear()!=reelEndDate.getYear()){
          var nbY =0;
          var newDY=0;
          if(dijit.byId('idUnitNotice').getValue==3 && dijit.byId('noticePeriod').getValue!=''){
            nbY=dijit.byId('noticePeriod').getValue();
          }else{
            var yearNoticeDate=noticeDate.getYear();
            days=dayDiffDates(noticeDate,reelEndDate);
            for(var i=0;i<days;i++){
              newDate=addDaysToDate(noticeDate,+1);
              newDY=newDate.getYear();
              if(yearNoticeDate!=newDY){
                nbY++;
                yearNoticeDate=newDY;
              }
            }
          }
          dijit.byId('idUnitNotice').set('value',3);
          dijit.byId('noticePeriod').set('value',nbY);
        }else if( dayNoticeDate == dayEndDate &&  MonthNotice!=MonthEnd ){
          var nbM =0;
          if(dijit.byId('idUnitNotice').getValue==2 && dijit.byId('noticePeriod').getValue!=''){
            nbM =dijit.byId('noticePeriod').getValue();
          }else{
            var newDM=0;
            var monthNoticeDAte=noticeDate.getMonth();
            days=dayDiffDates(noticeDate,reelEndDate);
            for(var i=0;i<days;i++){
              newDate=addDaysToDate(noticeDate,+1);
              newDM=newDate.getMonth();
              if(monthNoticeDAte!=newDM){
                nbM++;
                monthNoticeDAte=newDM;
              }
            }
          }
          dijit.byId('idUnitNotice').set('value',2);
          dijit.byId('noticePeriod').set('value',nbM);
        }else{
          var nbJ=dayDiffDates(noticeDate,endDate);
          dijit.byId('idUnitNotice').set('value',1);
          dijit.byId('noticePeriod').set('value',nbJ);
        }
      }
  }else if(val=='idUnitNotice'){
     if( (noticePeriod  && noticePeriod!= 0) && (endDate != undefined)  ){
       switchAddRemoveDaytoDate(idUnitNotice,endDate,noticePeriod,'-');
     }
  }else if(val=='noticePeriod'){
    if( endDate != undefined ){
      switchAddRemoveDaytoDate(idUnitNotice,endDate,noticePeriod,'-');
    }
  }
}

function expandOrganizationGroup(idOrganization, subOrganization,recSubOrganization){
  var recSubOrganizationList = recSubOrganization.split(',');
  var subOrganizationList = subOrganization.split(',');
  var budgetClass = dojo.attr('group_'+idOrganization, 'class');
  if(budgetClass == 'ganttExpandClosed'){
    if (dojo.byId('group_'+idOrganization)) dojo.setAttr('group_'+idOrganization, 'class', 'ganttExpandOpened');
    subOrganizationList.forEach(function(item){
      if (dojo.byId('organizationStructureRow_'+item)) dojo.byId('organizationStructureRow_'+item).style.display = 'table-row';
    });
  }else{
    if (dojo.byId('group_'+idOrganization)){
      dojo.setAttr('group_'+idOrganization, 'class', 'ganttExpandClosed');
    }
    recSubOrganizationList.forEach(function(item){
      if (dojo.byId('organizationStructureRow_'+item)){
        dojo.byId('organizationStructureRow_'+item).style.display = 'none';
        if (dojo.attr('group_'+item, 'class') == 'ganttExpandOpened'){
            dojo.setAttr('group_'+item, 'class', 'ganttExpandClosed');
        }
      }
    });
  }
}

function setUnitProgress(){
  if (!dijit.byId('ActivityPlanningElement_unitToRealise') || !dijit.byId('ActivityPlanningElement_unitRealised')) return null;
  var todo=dijit.byId('ActivityPlanningElement_unitToRealise').get("value");
  var real=dijit.byId('ActivityPlanningElement_unitRealised').get("value");
  var result=0;
  if( todo!=0 ){
    var adv=parseFloat((real/todo)).toFixed(4);
    result=((adv)*100);
  }
  return result;
}

function showProjectToDay(val,projList){
  var projList = projList.split(',');
  var callBack=function() {refreshTodayProjectsList();};
  if(val==1){
    projList.forEach(function(item){
      saveCollapsed('todayProjectRow_'+item,callBack);
    });
  }else{
    projList.forEach(function(item){
      saveExpanded('todayProjectRow_'+item,callBack);
    });
  }
  
  //loadContent("../view/today.php?", "centerDiv");
}

function expandProjectInToDay(id,subProj,visibleRow){
  var visibleRowList = visibleRow.split(',');
  var subProjList = subProj.split(',');
  var projClass = dojo.attr('group_'+id, 'class');
  if(visibleRowList == ''){
    visibleRowList = subProjList;
  }
  var callBack=function() {refreshTodayProjectsList();};
  if(projClass == 'ganttExpandOpened'){
    visibleRowList.forEach(function(item){
      if(dojo.byId('group_asSub_'+item)){
        var newItem=dojo.byId('group_asSub_'+item).value;
        visibleRowList.push(newItem.split(','));
      }
      saveExpanded('todayProjectRow_'+item,callBack);
    });
  }else{
    subProjList.forEach(function(item){
      saveCollapsed('todayProjectRow_'+item,callBack);
    });
  }
//loadContent("../view/today.php", "centerDiv");
}

// ====================================================================
// TAGS MANAGEMENT
// ====================================================================

function addDocumentTag(value) {
  if (!value) return;
  value=replaceAccentuatedCharacters(value);
  cleaned=value.replace(new RegExp("[^(a-z0-9)]", "g"), '');
  if (cleaned!=value) {
    showInfo(i18n('tagFormatError'));
    setTimeout("dijit.byId('tagInput').focus();",100);
    return false;
  }
  tags=dojo.byId('tags');
  if (tags.value.indexOf('#'+value+'#')>-1) {
    duplicateTag(value);
    return;
  }
  divTag=value+'&nbsp;<div class="docLineTagRemove" onClick="removeDocumentTag(\''+value+'\');">x</div>';
  var widget=dijit.byId('tagInput');
  dojo.create('span', {'innerHTML':divTag, class: 'docLineTagNew', id:value+'TagDiv'}, dojo.byId('tagList'),'last');
  dijit.byId('tagInput').reset();
  dijit.byId('tagInput').focus();
  if (tags.value=='') tags.value='#';
  tags.value+=value+'#';
}
function duplicateTag(value) {
  dojo.addClass(value+"TagDiv","docLineTagDouble");
  setTimeout('dojo.removeClass("'+value+'TagDiv","docLineTagDouble");',1000);
}
function removeDocumentTag(value) {
  tags=dojo.byId('tags');
  tags.value=tags.value.replace("#"+value+"#","#");
  if (tags.value=='#') tags.value='';
  dojo.destroy(value+"TagDiv");
}
var accentuatedCharactersTranscoding = {"à":"a","á":"a","â":"a","ã":"a","ä":"a","å":"a","ò":"o","ó":"o","ô":"o","õ":"o","ö":"o","ø":"o","è":"e","é":"e","ê":"e","ë":"e","ç":"c","ì":"i","í":"i","î":"i","ï":"i","ù":"u","ú":"u","û":"u","ü":"u","ÿ":"y","ñ":"n","-":" ","_":" "}; 
function replaceAccentuatedCharacters(text){
  var reg=/[àáäâèéêëçìíîïòóôõöøùúûüÿñ_-]/gi; 
  return text.replace(reg,function(){ return accentuatedCharactersTranscoding[arguments[0].toLowerCase()];}).toLowerCase();
}

function decrementProjectListConsolidation(listProj,length,nameDiv,month){
  var i=0;
  while(i<length){
    if(dojo.byId(nameDiv+''+month+listProj[i])){
      listProj.splice(i,1);
      length=length-1;
      continue;
    }
      i++;
  }
  return listProj;
}

function getHabilitationConsolidation(lst,lenght,mode,month){
  var i=0;
  while(i<length){
    if(mode=='locked'){
      if(dojo.byId('projHabilitationLocked_'+lst[i]).value=='2'){
        lst.splice(i,1);
        length=length-1;
        continue;
      }
    }else{
       if(dojo.byId('projHabilitationValidation_'+lst[i]).value=='2'){
          lst.splice(i,1);
          length=length-1;
          continue;
       }else if (dojo.byId('projHabilitationValidation_'+lst[i]).value=='1' && dojo.byId('projHabilitationLocked_'+lst[i]).value=='2' && dojo.byId('lockedImputation_'+month+lst[i])){
         lst.splice(i,1);
         length=length-1;
         continue;
       }
    }
      i++;
  }
  return lst;
}


function refreshConcolidationValidationList(){
  formInitialize();
  showWait();
  var callback=function() {
    hideWait();
  };
  loadContent("../view/refreshConsolidationValidation.php", "imputListDiv","consolidationValidationForm");
}

function refreshConsolidationDiv (proj,month,mode){
  var div=((mode=='Locked' || mode=='UnLocked')?'lockedDiv_':'validatedDiv_')+proj;
  formInitialize();
  showWait();
  var callback=function() {
    hideWait();
//    if(mode=='validaTionCons' || mode=='cancelCons'){
//      if(dojo.byId('lockedImputation_'+proj) && mode=='validaTionCons'){
//        mode='UnLocked';
//        refreshConsolidationDiv(proj,month,mode);
//      }else if(dojo.byId('lockedImputation_'+proj)){
//        mode='Locked';
//        refreshConsolidationDiv(proj,month,mode);
//      }else if(dojo.byId('UnlockedImputation_'+proj)){
//        mode='UnLocked';
//        refreshConsolidationDiv(proj,month,mode);
//      }
//    }
  };
  loadContent('../view/refreshConsolidationDiv.php?proj='+proj+'&month='+month+'&mode='+mode,div,false,false,false,false,false,callback);
}

function lockedImputation(mode,listProj,all,month,asSub){
  if(all!='All')all=false;
  else all=true;
  if(all){
    listProj=listProj.split(',');
    length=listProj.length;
    listProj=getHabilitationConsolidation(listProj,length,'locked');
    if(mode=='Locked'){
        nameDiv='lockedImputation_';
        listProj=decrementProjectListConsolidation(listProj,length,nameDiv,month);
    }else{
        nameDiv='UnlockedImputation_';
       listProj=decrementProjectListConsolidation(listProj,length,nameDiv,month);
    }
    if(listProj.length==0)return;
    listProj=listProj.join(",");
  }
  saveConsolidationValidation(listProj,mode,month,all,asSub);
}

function validateOrCancelAllConsolidation(listId,mode,month){
  listIdP=listId.split(',');
  length=listIdP.length;
  listId=getHabilitationConsolidation(listIdP,length,'validation');
  if(mode=="validaTionCons"){
    nameDiv='buttonCancel_';
    listId=decrementProjectListConsolidation(listId,length,nameDiv,month);
  }else{
    nameDiv='buttonValidation_';
    listId=decrementProjectListConsolidation(listId,length,nameDiv,month);
  }
  if(listId.length==0)return;
  listId=listId.join(",");
  saveConsolidationValidation(listId,mode,month,true);
}

function saveOrCancelConsolidationValidation(proj,month,asSub){
  all=false;
  if(dojo.byId('buttonValidation_'+proj)){
    mode='validaTionCons';
  }else{
    mode='cancelCons';
  }
//  if (dojo.byId('projHabilitationValidation_'+proj.substr(6)).value=='1' && dojo.byId('projHabilitationLocked_'+proj.substr(6)).value=='2' && dojo.byId('lockedImputation_'+proj)){
//    showAlert(i18n('cantHaveHabilitaionLocked'));
//    return;
//  }
  saveConsolidationValidation(proj,mode,month,all,asSub);
}

function saveConsolidationValidation(listProj,mode,month,all,asSub){  
  listproj=((mode=='Locked' || mode=='UnLocked') && !all )?listProj.substr(6):''+listProj+'';
  var url='../tool/saveConsolidationValidation.php?lstProj='+listproj+'&mode='+mode+'&month='+month+'&all='+all;
  var form= dojo.byId("consolidationForm");
  if(mode=='validaTionCons' || mode=='cancelCons'){
    dojo.xhrPost({
      url : url,
      form : form,
      handleAs : "text",
      load : function(){
          if(all || asSub){
            refreshConcolidationValidationList();
          }else{
            refreshConsolidationDiv(listProj,month,mode);
          }
      }
    });
  }else{
    dojo.xhrPost({
      url : url,
      handleAs : "text",
      load : function(){
          if(all || asSub){
            refreshConcolidationValidationList();
          }else{
            refreshConsolidationDiv(listProj,month,mode);
          }
      }
    });
  }
}


//====================================================================
// NEW GUI FEATURES
//====================================================================

function refreshSectionCount(section) {
  if (dojo.byId(section + "SectionCount")
      && dojo.byId(section + "Badge")) {
    dojo.byId(section + "Badge").innerHTML = dojo.byId(section+ "SectionCount").value;
    if (dojo.byId(section + "BadgeTab")) {
      dojo.byId(section + "BadgeTab").innerHTML = dojo.byId(section+"SectionCount").value;
      if (dojo.byId(section+"SectionCount").value>0) {
        dojo.byId(section + "BadgeTab").style.opacity=1;
      } else {
        dojo.byId(section + "BadgeTab").style.opacity=0.5;
      }
    }
  }
}

var actionSelectTimeout=null;
var actionSectionField=null;
function showActionSelect(selectClass, selectId, selectField, canCreate, canUpdate) {
  if (actionSelectTimeout && actionSectionField==selectField) clearTimeout(actionSelectTimeout);
  else if (actionSelectTimeout && actionSectionField!=selectField) {
    if (dojo.byId("toolbar_"+actionSectionField)) {
      clearTimeout(actionSelectTimeout);
      dojo.byId("toolbar_"+actionSectionField).style.opacity='0';
      dojo.byId("toolbar_"+actionSectionField).style.display='none';
    }
  }
  var selectClassTitle=selectClass;
  if (selectClassTitle.substr(0,8)=='Original') selectClassTitle=selectClassTitle.substr(8);
  if (selectClassTitle.substr(0,6)=='Target') selectClassTitle=selectClassTitle.substr(6);  
  var toolId="toolbar_"+selectField;
  var width=0;
  var maxWidth=((dojo.byId("widget_"+selectField).offsetWidth)/2)-25;
  if (! dojo.byId(toolId)) return;
  if (dojo.byId(toolId).innerHTML=="...") {
    var buttons='';
    if (canUpdate && width<maxWidth) {
      width+=25;
      buttons+='<div title="'+i18n('comboSearchButton')+'" style="float:right;margin-right:3px;" class="roundedButton roundedIconButton generalColClass '+selectField+'Class">';
      buttons+='  <div class="imageColorNewGui iconToolbarSearch" onclick="actionSelectSearch(\''+selectClass+'\', \''+selectId+'\', \''+selectField+'\');"></div>';
      buttons+='</div>';
    }
    if (canCreate && width<maxWidth) {
      width+=25;
      buttons+='<div title="'+i18n('buttonNew',new Array(i18n(selectClassTitle)))+'" style="float:right;margin-right:3px;" class="roundedButton roundedIconButton generalColClass '+selectField+'Class">';
      buttons+='  <div class="imageColorNewGui iconToolbarAdd" onclick="actionSelectAdd(\''+selectClass+'\', \''+selectId+'\', \''+selectField+'\');"></div>';
      buttons+='</div>';
    }
    if (selectId && width<maxWidth) {
      width+=25;
      buttons+='<div title="'+i18n('showItem')+'" style="float:right;margin-right:3px;" class="roundedButton roundedIconButton generalColClass '+selectField+'Class">';
      buttons+='  <div class="imageColorNewGui iconToolbarView" onclick="actionSelectView(\''+selectClass+'\', \''+selectId+'\', \''+selectField+'\');"></div>';
      buttons+='</div>';
    }
    if (selectId && width<maxWidth) {
      width+=25;
      buttons+='<div title="'+i18n('showDirectAccess')+'" style="float:right;margin-right:3px;" class="roundedButton roundedIconButton generalColClass '+selectField+'Class">';
      buttons+='  <div class="imageColorNewGui iconToolbarGoto" onclick="actionSelectGoto(\''+selectClass+'\', \''+selectId+'\', \''+selectField+'\');"></div>';
      buttons+='</div>';
    }
    dojo.byId(toolId).style.width=width+"px";
    dojo.byId(toolId).innerHTML=buttons;
  } 
  dojo.byId(toolId).style.display='block';
  dojo.byId(toolId).style.opacity='1';
}

var actionProjectSelectorTimeout=null;
var actionProjectSelectorField=null;
function showActionProjectSelector() {
	var toolId="toolbar_projectSelector";
	if (actionProjectSelectorTimeout){
		clearTimeout(actionProjectSelectorTimeout);
	}else {
	    if (dojo.byId(toolId)) {
	      clearTimeout(actionProjectSelectorTimeout);
	      dojo.byId(toolId).style.opacity='0';
	      dojo.byId(toolId).style.display='none';
	    }
	}
  if (! dojo.byId(toolId)) return;
  dojo.byId(toolId).style.display='block';
  dojo.byId(toolId).style.opacity='1';
}

function actionSelectGoto(selectClass, selectId, selectField) {
  var sel=dijit.byId(selectField);
  if (sel && trim(sel.get('value'))) { 
    gotoElement(selectClass,sel.get('value'));
  } else { 
    showAlert(i18n('cannotGoto'));
  } 
}
function actionSelectView(selectClass, selectId, selectField, canCreate) {
  var sel=dijit.byId(selectField);
  if (sel && trim(sel.get('value'))) { 
    showDetail(selectField,((canCreate)?1:0),selectClass,false,null,false);
  } else { 
    showAlert(i18n('cannotView'));
  } 
}
function actionSelectSearch(selectClass, selectId, selectField, canCreate) {
  showDetail(selectField,((canCreate)?1:0),selectClass,false,null,true); 
}
function actionSelectAdd(selectClass, selectId, selectField) {
  showDetail(selectField,1,selectClass,false,null,true);
  newDetailItem();
}
function hideActionSelect(selectClass, selectId, selectField) {
  actionSectionField=selectField;
  actionSelectTimeout=setTimeout("dojo.byId('toolbar_"+selectField+"').style.display='none';",100);
  
}

function hideActionProjectSelector() {
  actionProjectSelectorTimeout=setTimeout("dojo.byId('toolbar_projectSelector').style.display='none';",100);
}

function displayCheckBoxDefinitionLine(){
  var requiredVisibility=dojo.byId('tr_dialogChecklistDefinitionLineRequired').style.visibility;
  var exclusiveVisibility=dojo.byId('tr_dialogChecklistDefinitionLineExclusive').style.visibility;
  if(requiredVisibility=='hidden' && exclusiveVisibility=='hidden' && this.value!=''){
    dojo.byId('tr_dialogChecklistDefinitionLineRequired').style.visibility='visible';
    dojo.byId('tr_dialogChecklistDefinitionLineExclusive').style.visibility='visible';
  }else {
    dojo.byId('tr_dialogChecklistDefinitionLineRequired').style.visibility='hidden';
    dojo.byId('tr_dialogChecklistDefinitionLineExclusive').style.visibility='hidden'
  }
}


//=================================================================
var isResizingGanttBar=false;
var resizerEventIsInit=false;
function handleResizeGantBAr (element,refId,id,minDate,dayWidth,dateFormat){
  if(isResizingGanttBar)return;
  id=id.trim();
  var barDiv=dojo.byId('bardiv_'+id),
   el = dojo.byId('taskbar_'+id),
     width=0,
       left=0,
         startDate=0,
           endDate=0,
             duration=0,
               label=dojo.byId('labelBarDiv_'+id),
                 resizerStart =dojo.byId('taskbar_'+id+'ResizerStart'),
                   resizerEnd =dojo.byId('taskbar_'+id+'ResizerEnd'),
                     startX,  
                       startWidth,
                         divVisibleStartDateChange=dojo.byId('divStartDateResize_'+id),
                           divVisibleEndDateChange=dojo.byId('divEndDateResize_'+id),
                             inputDateGantBarResizeleft=dojo.byId('inputDateGantBarResizeleft_'+id),
                               inputDateGantBarResizeRight=dojo.byId('inputDateGantBarResizeRight_'+id),
                                 isCalulated=false,
                                   directionMovement='';
 
  if((!resizerStart && !resizerEnd ))return;
  if(resizerStart){
    resizerStart.style.display="block";
    divVisibleStartDateChange.style.display="block";
    resizerStart.addEventListener('mousedown', initDragStart, false);
  }
  if(resizerEnd){
    resizerEnd.style.display="block";
    divVisibleEndDateChange.style.display="block";
    resizerEnd.addEventListener('mousedown', initDragEnd, false);
  }
  
  function initDragStart(e) {
     if(resizerEventIsInit)return;
     resizerEventIsInit=true;
   //set current pos
     startX = e.clientX;
     startLeft = barDiv.offsetLeft;
     startWidth = parseInt(document.defaultView.getComputedStyle(el).width,10);
     
     document.documentElement.addEventListener('mousemove', doDragStart, false);
     document.documentElement.addEventListener('mouseup', stopDrag, false);
  }
  
  function initDragEnd(e) {
    if(resizerEventIsInit)return;
    resizerEventIsInit=true;
  //set current pos
     startX = e.clientX;
     labelLeft=label.offsetLeft;
     startLeft = barDiv.offsetLeft;
     startWidth = parseInt(document.defaultView.getComputedStyle(el).width,10);
     
     document.documentElement.addEventListener('mousemove', doDragEnd, false);
     document.documentElement.addEventListener('mouseup', stopDrag, false);
  }
  
  
  function doDragStart(e) {
    isResizingGanttBar=true;
    if(resizerEnd){
      resizerEnd.style.display="none";
      divVisibleEndDateChange.style.display="none";
    }
    // defined if it's positive movement or negative with respect to the initial position 
    directionMovement=(Math.sign( startX - e.clientX)==-1)?'neg':'pos';
    //
    // move all ellement 
    left=startLeft - (Math.ceil((startX - e.clientX)/dayWidth)*dayWidth);
    resizerStart.style.left=(left-22)+'px';
    divVisibleStartDateChange.style.left=(left-43)+'px';
    barDiv.style.left = left+'px';
    width=(startWidth+(Math.ceil((startX - e.clientX)/dayWidth)*dayWidth) > dayWidth)? startWidth+(Math.ceil((startX - e.clientX)/dayWidth)*dayWidth) : dayWidth;
    barDiv.style.width = width+ 'px';
    el.style.width =width+ 'px';
    
    //
    calculatedDate();
    divVisibleStartDateChange.innerHTML=JSGantt.formatDateStr(dateStart,dateFormat);
  }

  
  function doDragEnd(e) {
    isResizingGanttBar=true;
    if(resizerStart){
      divVisibleStartDateChange.style.display="none";
      resizerStart.style.display="none";
    }
    // defined if it's positive movement or negative with respect to the initial position 
    directionMovement=(Math.sign(e.clientX - startX)==-1)?'neg':'pos';
    //
    // move all ellement 
    left=(Math.ceil((startLeft+startWidth+(e.clientX - startX))/dayWidth)*dayWidth < startLeft)? startLeft+dayWidth : Math.ceil((startLeft+startWidth+(e.clientX - startX))/dayWidth)*dayWidth;
    resizerEnd.style.left=(left-11)+'px';
    divVisibleEndDateChange.style.left=(left-11)+'px';
    label.style.left=Math.ceil((labelLeft +(e.clientX - startX))/dayWidth)*dayWidth+'px';
    width=(Math.ceil((startWidth + e.clientX - startX)/dayWidth)*dayWidth > dayWidth)? Math.ceil((startWidth + e.clientX - startX)/dayWidth)*dayWidth : dayWidth ;
    el.style.width = width+ 'px';
    barDiv.style.width = width+ 'px';
    //
    calculatedDate();
    divVisibleEndDateChange.innerHTML=JSGantt.formatDateStr(dateEnd,dateFormat);
  }
  
  
  function calculatedDate(){ //calcul new date 
    left=barDiv.offsetLeft;
    duration=Math.ceil(width/dayWidth);
    startDate=minDate+(((left/dayWidth)*(24 * 60 * 60 * 1000)));
    endDate=minDate+((((left/dayWidth)+duration-1)*(24 * 60 * 60 * 1000)));
    dateStart = new Date(startDate);
    dateEnd = new Date(endDate);
    isCalulated=true;
  }
  
  
  function stopDrag(e) {
    var startResize=0,
    endResize=0;
    document.documentElement.removeEventListener('mouseup', stopDrag,false);
    //stop event and hide handle
    if(resizerEnd){
      resizerEnd.removeEventListener('mousedown', initDragEnd, false);
      document.documentElement.removeEventListener('mousemove', doDragEnd, false);   
      resizerEnd.style.display="none";
      divVisibleEndDateChange.style.display="none";
    }
    if(resizerStart){
      resizerStart.removeEventListener('mousedown', initDragStart, false);
      document.documentElement.removeEventListener('mousemove', doDragStart, false);   
      divVisibleStartDateChange.style.display="none";
      resizerStart.style.display="none";
    }
    if(isCalulated){
      // loop to define a non-off Day date
        if(isOffDay(dateStart)){
          while(isOffDay(dateStart)==true){
            if(directionMovement=='pos'){
              startDate=startDate+(24 * 60 * 60 * 1000);
            }else{
              startDate= startDate-(24 * 60 * 60 * 1000);
            }
            dateStart = new Date(startDate);
            startResize++;
          }
        }else if(isOffDay(dateEnd)){
          while(isOffDay(dateEnd)==true) {
            if(directionMovement=='neg'){
              endDate=endDate+(24 * 60 * 60 * 1000);
            }else{
              endDate= endDate-(24 * 60 * 60 * 1000)
            }
            dateEnd = new Date(endDate);
            endResize++;
          }
        }
       // 
       //redefines the size if it was a day off
        if(startResize!=0){
          if(directionMovement=='pos'){
            left=barDiv.offsetLeft+(dayWidth*startResize);
            barDiv.style.left =left+'px';
            width=width-(dayWidth*startResize);
          }else{
            left=barDiv.offsetLeft-(dayWidth*startResize);
            barDiv.style.left =left+'px';
            width=width+(dayWidth*startResize);
          }
          if (resizerStart) resizerStart.style.left=(left-22)+'px';
          if (divVisibleStartDateChange) divVisibleStartDateChange.style.left=(left-43)+'px';
          if (el) el.style.width = width+ 'px';
          if (barDiv) barDiv.style.width = width+ 'px';
          startDateFormatForDisplay=JSGantt.formatDateStr(dateStart,dateFormat);
          if (divVisibleStartDateChange) divVisibleStartDateChange.innerHTML=startDateFormatForDisplay;
        }else if (endResize!=0){
          if(directionMovement=='pos'){
            width=width-(dayWidth*endResize);
            left=barDiv.offsetLeft+width;
            label.style.left=label.offsetLeft-(dayWidth*endResize)+'px';
          }else{
            width=width+(dayWidth*endResize);
            left=barDiv.offsetLeft+width;
            label.style.left=label.offsetLeft+(dayWidth*endResize)+'px';
          }
          barDiv.style.width = width+ 'px';
          el.style.width = width+ 'px';
          resizerEnd.style.left=(left-11)+'px';
          divVisibleEndDateChange.style.left=(left-11)+'px';
          endDateFormatForDisplay=JSGantt.formatDateStr(dateEnd,dateFormat);
          divVisibleEndDateChange.innerHTML=endDateFormatForDisplay;
        }
        duration=duration-endResize-startResize;
        if(resizerStart)inputDateGantBarResizeleft.setAttribute('value',dateStart);
        if(resizerEnd)inputDateGantBarResizeRight.setAttribute('value',dateEnd);
        dateStart=JSGantt.formatDateStr(dateStart,'yyyy-mm-dd');
        dateEnd=JSGantt.formatDateStr(dateEnd,'yyyy-mm-dd');
        saveGanttElementResize( element,refId,id,dateStart,dateEnd,duration);
    }
    setTimeout('isResizingGanttBar=false;',150);
    setTimeout('resizerEventIsInit=false;',150);
  }
}

isOnResizer=false;
function hideResizerGanttBar (vID) {
  if(isResizingGanttBar==false && dojo.byId('taskbar_'+vID+'ResizerEnd')){
    dojo.byId('taskbar_'+vID+'ResizerEnd').style.display='none';
    dojo.byId('divEndDateResize_'+vID).style.display='none';
  }
  if(isResizingGanttBar==false && dojo.byId('taskbar_'+vID+'ResizerStart')){
    dojo.byId('taskbar_'+vID+'ResizerStart').style.display='none';
    dojo.byId('divStartDateResize_'+vID).style.display='none';
  }
  
}

function showResizerGanttBar (vID,val) {
  if(val=='start'){
    dojo.byId('taskbar_'+vID+'ResizerStart').style.display='block';
    dojo.byId('divStartDateResize_'+vID).style.display='block';
  }else{
    dojo.byId('taskbar_'+vID+'ResizerEnd').style.display='block';
    dojo.byId('divEndDateResize_'+vID).style.display='block';
  }

}


function saveGanttElementResize(element,refId,id,dateStart,dateEnd,duration){
  var param="?id="+id+"&object="+element+"&idObj="+refId+"&startDate="+dateStart+"&endDate="+dateEnd+"&duration="+duration;
  var url= "../tool/savePlanningElementAfterResize.php"+param;
  dojo.xhrGet({
    url : url,
    load : function(data, args) {
      if (dojo.getAttr('automaticRunPlan','aria-checked')=='true')plan();
      else refreshJsonPlanning();
      if((dojo.byId('objectClass').value.trim() !='' && dojo.byId('objectId').value.trim() !='' && dojo.getAttr('automaticRunPlan','aria-checked')!='true') && dojo.byId('objectId').value.trim()==refId.trim() ){
        loadContent("objectDetail.php", "detailDiv", 'listForm');
        loadContentStream();
      }
    },
  });
}

function switchNewGui(){
	if(isNewGui){
		val = 0;
	}else{
		val = 1;
	}
	saveDataToSessionAndReload("newGui", val, true);
}