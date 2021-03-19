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

// ============================================================================
// All specific ProjeQtOr functions for work management
// This file is included in the main.php page, to be reachable in every context
// ============================================================================

/**
 * Open / Close Group : hide sub-lines
 */
function workOpenCloseLine(line, scope) {
  var nbLines=dojo.byId('nbLines').value;
  var wbsLine=dojo.byId('wbs_'+line).value;
  var wbsLineTop=wbsLine.substr(0,wbsLine.lastIndexOf("."));
  var action=(dojo.byId('status_'+line).value=='opened')?"close":"open";
  if (action=="close") {
    dojo.byId('group_' + line).className="ganttExpandClosed";
    dojo.byId('status_'+line).value="closed";
    saveCollapsed(scope);
  } else {
    dojo.byId('group_' + line).className="ganttExpandOpened";
    dojo.byId('status_'+line).value="opened";
    saveExpanded(scope);
  }
  for (i=line+1; i<=nbLines; i++) {
    var wbs=dojo.byId('wbs_'+i).value;
    var wbsTop=wbs.substr(0,wbs.lastIndexOf("."));
    if (wbs.length <= wbsLine.length ) {
      break;
    } 
    if (wbsTop.substr(0,wbsLine.length)!=wbsLine) {
      break;
    }
    if (action=="close") {
      dojo.byId('line_' + i).style.display = "none";
    } else {
      dojo.byId('line_' + i).style.display = "";
      var status=dojo.byId('status_'+i).value;
      if (status=='closed') {
        var wbsClosed=dojo.byId('wbs_'+i).value;
        for (j=i+1; j<=nbLines; j++) {
          var wbsSub=dojo.byId('wbs_'+j).value;
          if (wbsSub.indexOf(wbsClosed)==-1) {
            break;
          }
        }
        i=j-1;
      }
    }
  } 
}

/**
 * Refresh the imputation list
 * @return
 */
function refreshImputationList() {
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    return false;
  }
  formInitialize();
  dojo.byId('userId').value=dijit.byId('userName').get("value");
  dojo.byId('idle').checked=dojo.byId('listShowIdle').checked;
  dojo.byId('showPlannedWork').checked=dojo.byId('listShowPlannedWork').checked;
  dojo.byId('showIdT').checked=dojo.byId('showId').checked;
  dojo.byId('yearSpinnerT').value=dojo.byId('yearSpinner').value;
  dojo.byId('weekSpinnerT').value=dijit.byId('weekSpinner').get('value');
  dojo.byId('hideDone').checked=dojo.byId('listHideDone').checked;

  enableWidget("userName");
  enableWidget("yearSpinner");
  enableWidget("weekSpinner");
  enableWidget("dateSelector");
  enableWidget("listDisplayOnlyCurrentWeekMeetings");
  enableWidget("listHideDone");
  enableWidget("listHideNotHandled");
  enableWidget("listShowIdle");
  enableWidget("listShowPlannedWork");
  enableWidget("showId");
  
  if (dojo.byId('hideNotHandled') && dojo.byId('listHideNotHandled') ) {
    dojo.byId('hideNotHandled').checked=dojo.byId('listHideNotHandled').checked;
  }
  dojo.byId('displayOnlyCurrentWeekMeetings').checked=dojo.byId('listDisplayOnlyCurrentWeekMeetings').checked;
  loadContent('../view/refreshImputationList.php', 'workDiv', 'listForm', false);
  return true;
}

/**
 * Refresh the imputation list after period update (check format first)
 * @return
 */
var refreshTimoutInProgress=null;
var refreshInProgress=false;
function refreshImputationPeriod(directDate) {
  if (refreshInProgress && directDate) {
    return true; 
  }
  if (refreshTimoutInProgress!==null) {
   clearTimeout(refreshTimoutInProgress);
  }
  if (checkFormChangeInProgress()) {
    showAlert(i18n('alertOngoingChange'));
    var period=dojo.byId('rangeValue').value;
    var year=period.substr(0,4);
        dijit.byId('yearSpinner').set('value',year);
        var week=period.substr(4,2);
        dijit.byId('weekSpinner').set('value',week);
    //var week=dijit.byId('weekSpinner').get('value') + '';
    var day=getFirstDayOfWeek(week,year);
    dijit.byId('dateSelector').set('value',day);
    return false;
  }
  refreshInProgress=true;
  if (directDate) {
    var year=directDate.getFullYear();
    var week=getWeek(directDate.getDate(),directDate.getMonth()+1,directDate.getFullYear())+'';
    if (week==1 && directDate.getMonth()>10) {
      year+=1;
    }
    dijit.byId('yearSpinner').set('value',year);
    dijit.byId('weekSpinner').set('value',week);
  } else {
    var year=dijit.byId('yearSpinner').get('value');
    var week=dijit.byId('weekSpinner').get('value') + '';
  }
  if (week.length==1 || parseInt(week,10)<10) {
    week='0' + week;
  }
  if (week=='00') {
    week=getWeek(31,12,year-1);
    if (week==1) {
      var day=getFirstDayOfWeek(1,year);
      //day=day-1;
      week=getWeek(day.getDate()-1,day.getMonth()+1,day.getFullYear());
    }
    year=year-1;
    dijit.byId('yearSpinner').set('value',year);
    dijit.byId('weekSpinner').set('value', week);
  } else if (parseInt(week,10)>53) {
      week='01';
      year+=1;
    dijit.byId('yearSpinner').set('value', year);
    dijit.byId('weekSpinner').set('value', week);
  } else if (parseInt(week,10)>52) {
    lastWeek=getWeek(31,12,year);
    if (lastWeek==1) {
      var day=getFirstDayOfWeek(1,year+1);
      //day=day-1;
      lastWeek=getWeek(day.getDate()-1,day.getMonth()+1,day.getFullYear());
    }
    if (parseInt(week,10)>parseInt(lastWeek,10)) {
      week='01';
        year+=1;
      dijit.byId('yearSpinner').set('value', year);
      dijit.byId('weekSpinner').set('value', week);
    }
  }
  var day=getFirstDayOfWeek(week,year);
  dijit.byId('dateSelector').set('value',day);
  dojo.byId('rangeValue').value='' + year + week;
  if ((year+'').length==4) { 
     refreshTimoutInProgress=setTimeout("refreshImputationList();refreshInProgress=false;",500);
  }
  return true;
}

function recursiveAddWorkProject(idProject, day, diff){
  if (dojo.byId('sumProject_'+idProject+'_'+day))
    dojo.byId('sumProject_'+idProject+'_'+day).innerHTML=parseFloat(dojo.byId('sumProject_'+idProject+'_'+day).innerHTML)+parseFloat(diff);
  if (dojo.byId('sumProjectDisplay_'+idProject+'_'+day))
    dojo.byId('sumProjectDisplay_'+idProject+'_'+day).value=formatDecimalToDisplay(dojo.byId('sumProject_'+idProject+'_'+day).innerHTML);
  if (dojo.byId('sumWeekProject_'+idProject))
    dojo.byId('sumWeekProject_'+idProject).innerHTML=parseFloat(dojo.byId('sumWeekProject_'+idProject).innerHTML)+parseFloat(diff);
  if (dojo.byId('sumWeekProjectDisplay_'+idProject))
    dojo.byId('sumWeekProjectDisplay_'+idProject).value=formatDecimalToDisplay(dojo.byId('sumWeekProject_'+idProject).innerHTML);
  if(dojo.byId('projectParent_'+idProject+'_'+day) && dojo.byId('projectParent_'+idProject+'_'+day)!=null)
    recursiveAddWorkProject(dojo.byId('projectParent_'+idProject+'_'+day).value, day, diff);
}

/**
 * Dispatch updates for a work value : to column sum, real work, left work and planned work
 * @param rowId
 * @param colId
 * @return
 */
//var oldImputationWorkValue=0;
function dispatchWorkValueChange(rowId, colId, date) { 
  var oldWorkValue=dojo.byId('workOldValue_' + rowId + '_' + colId).value;
  //var oldWorkValue=oldImputationWorkValue;
  if (oldWorkValue==null || oldWorkValue=='') {oldWorkValue=0;}   
  var newWorkValue=dijit.byId('workValue_' + rowId + '_' + colId).get('value');
  if (isNaN(newWorkValue)) {
    newWorkValue=0;
  }
  if(parseInt(dojo.byId('isAdministrative_' + rowId + '_' + colId).value)==0){
    //daysWorkFuture daysWorkFutureBlocking
    isFuture=dojo.byId('colIsFuture_' + colId).value==1;
    isFutureBlocking=dojo.byId('colIsFutureBlocking_' + colId).value==1;
    daysWorkFutureV=dojo.byId('daysWorkFuture').value;
    daysWorkFutureBlockingV=dojo.byId('daysWorkFutureBlocking').value;
    toAdd=rowId+"|"+colId;
    if(newWorkValue!=0){
      if(isFuture) {
        if(daysWorkFutureV==0){
          daysWorkFutureV=[];
          daysWorkFutureV.push(rowId+"|"+colId);
        }else{
          daysWorkFutureV=daysWorkFutureV.split(',');
          find=false;
          for(var ite in daysWorkFutureV){
            if(daysWorkFutureV[ite]==toAdd)find=true;
          }
          if(!find){
            daysWorkFutureV.push(toAdd);
          }
        }
      }
      if(isFutureBlocking) {
        if(daysWorkFutureBlockingV==0){
          daysWorkFutureBlockingV=[];
          daysWorkFutureBlockingV.push(rowId+"|"+colId);
        }else{
          daysWorkFutureBlockingV=daysWorkFutureBlockingV.split(',');
          find=false;
          for(var ite in daysWorkFutureBlockingV){
            if(daysWorkFutureBlockingV[ite]==toAdd)find=true;
          }
          if(!find){
            daysWorkFutureBlockingV.push(toAdd);
          }
        }
      }
    } else {
      if(isFuture) {
        if(daysWorkFutureV!=0){
          daysWorkFutureV=daysWorkFutureV.split(',');
          find=false;
          for(var ite in daysWorkFutureV){
            if(daysWorkFutureV[ite]==toAdd)daysWorkFutureV.splice(ite, 1);
          }
        }
      }
      if(isFutureBlocking) {
        if(daysWorkFutureBlockingV!=0){
          daysWorkFutureBlockingV=daysWorkFutureBlockingV.split(',');
          for(var ite in daysWorkFutureBlockingV){
            if(daysWorkFutureBlockingV[ite]==toAdd)daysWorkFutureBlockingV.splice(ite, 1);
          }
        }
      }
    }
    toAddFuture='';
    if (Array.isArray(daysWorkFutureV)) {
      for(var ite in daysWorkFutureV){
        toAddFuture+=(ite==0 ? '' : ',')+daysWorkFutureV[ite];
      }
    }
    if(toAddFuture=='')toAddFuture='0';
    dojo.byId('daysWorkFuture').value=toAddFuture;
    toAddFutureBlocking='';
    if (Array.isArray(daysWorkFutureBlockingV)) {
      for(var ite in daysWorkFutureBlockingV){
        toAddFutureBlocking+=(ite==0 ? '' : ',')+daysWorkFutureBlockingV[ite];
      }
    }
    if(toAddFutureBlocking=='')toAddFutureBlocking='0';
    dojo.byId('daysWorkFutureBlocking').value=toAddFutureBlocking;
  }
  var diff=newWorkValue-oldWorkValue;
  recursiveAddWorkProject(dojo.byId('idProject_'+rowId+'_'+colId).value, colId, diff);
  // Update sum for column
  var oldSum=dijit.byId('colSumWork_' + colId).get("value");
  var newSum=oldSum + diff;
  newSum=Math.round(newSum*100)/100;
  dijit.byId('colSumWork_' + colId).set("value",newSum);
  //Update real work
  var oldReal=formatDisplayToDecimal(dojo.byId('realWork_' + rowId).value);
  var newReal=oldReal + diff;
  dojo.byId('realWork_' + rowId).value=formatDecimalToDisplay(newReal);
  //Update left work
  var assigned=formatDisplayToDecimal(dojo.byId('assignedWork_' + rowId).value);
  var oldLeft=dijit.byId('leftWork_' + rowId).get("value");
  if (assigned>0 || diff>0 || oldLeft>0) {
    var newLeft=oldLeft - diff;
    newLeft=(newLeft<0)?0:newLeft;
      dijit.byId('leftWork_' + rowId).set("value",newLeft);
  } else {
      var newLeft=oldLeft;  
  }
  //Update planned work
  var newPlanned=newReal+newLeft;
  dojo.byId('plannedWork_' + rowId).value=formatDecimalToDisplay(newPlanned);
  // store new value for next calculation...
  dojo.byId('workOldValue_' + rowId + '_' + colId).value=newWorkValue;
  //oldImputationWorkValue=newWorkValue;
  formChanged();
  disableWidget("userName");
  disableWidget("yearSpinner");
  disableWidget("weekSpinner");
  disableWidget("dateSelector");
  disableWidget("listDisplayOnlyCurrentWeekMeetings");
  disableWidget("listHideDone");
  disableWidget("listHideNotHandled");
  disableWidget("listShowIdle");
  disableWidget("listShowPlannedWork");
  disableWidget("showId");
  checkCapacity(date);
  dijit.byId('totalWork').set("value",parseFloat(dijit.byId('totalWork').get("value"))+diff);
  totalWork=Math.round(parseFloat(dijit.byId('totalWork').get("value"))*100)/100;
  businessDay=Math.round(parseFloat(dojo.byId('businessDay').value)*100)/100;
  classTotalWork="imputationValidCapacity imputation";
  if (totalWork > businessDay) {
    classTotalWork='imputationInvalidCapacity imputation';
  } else if (totalWork<businessDay) {
    classTotalWork='displayTransparent imputation';
  }
  dijit.byId('totalWork').set("class",classTotalWork);
  var classCurrentInput="input imputation"+((dijit.byId('workValue_'+rowId+'_'+colId).get("value")>0)?' imputationHasValue':'');
  dijit.byId('workValue_'+rowId+'_'+colId).set("class",classCurrentInput)
  if ((oldReal==0 && newReal>0) || (oldLeft>0 && newLeft==0) || (newReal<oldReal) ) {
    var url= '../tool/checkStatusChange.php';
    url+='?newReal='+newReal;
    url+='&newLeft='+newLeft;
    url+='&idAssignment='+dojo.byId('idAssignment_' + rowId).value;
    dojo.xhrGet({
      url:url,
      handleAs: "text",
      load: function (data) {
        dojo.byId('extra_'+rowId).innerHTML=data;
      }
    });
  }
}

function isOffDay(vDate) {
  
  if ( defaultOffDays.indexOf(vDate.getDay()) != -1) {
    var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
    if (workDayList.lastIndexOf('#'+day+'#')>=0) {
    return false; 
    } else {
      return true;
    }
  } else {
    var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
    if (offDayList.lastIndexOf('#'+day+'#')>=0) {
      return true; 
    } else {
      return false;
    }
  }
}
// V6.0.0 : not used any more
/*function isOffDayNotWeekEnd(vDate) {
    var day=(vDate.getFullYear()*10000)+((vDate.getMonth()+1)*100)+vDate.getDate();
    if (offDayList.lastIndexOf('#'+day+'#')>=0) {
    return true; 
    } else {
      return false;
    }
}*/

/**
 * Dispatch updates for left work : re-calculate planned work 
 */
function dispatchLeftWorkValueChange(rowId) {

  var newLeft=dijit.byId('leftWork_' + rowId).get("value");
  if (newLeft==null || isNaN(newLeft) || newLeft=='') {
    dijit.byId('leftWork_' + rowId).set("value",'0');
    newLeft=0;
  }
  var newReal=formatDisplayToDecimal(dojo.byId('realWork_' + rowId).value);
  var newPlanned=newReal+newLeft;
  dojo.byId('plannedWork_' + rowId).value=formatDecimalToDisplay(newPlanned);
  formChanged();
  
  disableWidget("userName");
  disableWidget("yearSpinner");
  disableWidget("weekSpinner");
  disableWidget("dateSelector");
  disableWidget("listDisplayOnlyCurrentWeekMeetings");
  disableWidget("listHideDone");
  disableWidget("listHideNotHandled");
  disableWidget("listShowIdle");
  disableWidget("listShowPlannedWork");
  disableWidget("showId");
  
  if (newLeft || newLeft==0) {
    var url= '../tool/checkStatusChange.php';
    url+='?newReal='+newReal;
    url+='&newLeft='+newLeft;
    url+='&idAssignment='+dojo.byId('idAssignment_' + rowId).value;
    dojo.xhrGet({
      url:url,
      handleAs: "text",
      load: function (data) {
        dojo.byId('extra_'+rowId).innerHTML=data;
      }
    });
  }
}


function startMove(id) {
  document.body.style.cursor='help';
}

function endMove(id) {
  document.body.style.cursor='normal';
}

//==========================================================
//Work Period Locking
//==========================================================

function submitWorkPeriod(action) {
  if (checkFormChangeInProgress()) {
  return false;
  }
  var rangeValue=dojo.byId('rangeValue').value;
  var rangeType='week';
  var resource=dijit.byId('userName').get('value');
  dojo.xhrGet({
  url: '../tool/submitWorkPeriod.php?action='+action+'&rangeType='+rangeType+'&rangeValue='+rangeValue+'&resource='+resource,
    handleAs: "text",
    load: function(data,args) { refreshImputationList();sendAlertOnSubmitWork(action, rangeType, rangeValue, resource);},
    error: function() { }
  });
}

function sendAlertOnSubmitWork(action, rangeType, rangeValue, resource){
	dojo.xhrGet({
		  url: '../tool/sendMail.php?className=Imputation&action='+action+'&rangeType='+rangeType+'&rangeValue='+rangeValue+'&resource='+resource,
		    handleAs: "text",
		    load: function(data,args) {},
		    error: function() { }
		  });
}

function enterRealAsPlanned(nbDays){   
  if (! dojo.byId('listShowPlannedWork').checked) {
    showAlert(i18n('enterRealAsPlannedNeedsPlannedWork'));
    return;
  }
  var cptUpdates=0;
  var nblines = dojo.byId("nbLines").value;
  for (line=1;line<=nblines;line++) {
    if (dojo.byId('locked_'+line) && dojo.byId('locked_'+line).value=='1') continue;
    for (day=1; day<=nbDays; day++) { 
      var workValue = dijit.byId('workValue_' + line + '_' + day);
      var plannedValue = dojo.byId('plannedValue_' + line + '_' + day);
      var isResourceTeam = '';
      if(dojo.byId('isResourceTeam_' + line + '_' + day)){
    	  isResourceTeam = dojo.byId('isResourceTeam_' + line + '_' + day).value;
      }
      if(plannedValue && isResourceTeam != '1'){
        workValue.set('value',plannedValue.getAttribute("data-value"));
        cptUpdates++;
      }
    }
  }
  if (cptUpdates==0) {
    showAlert(i18n('messageNoImputationChange'));
  }
}

function checkCapacity(date) {
  var capacity=parseFloat(dojo.byId('resourceCapacity_'+date).value);
  capacity=Math.round(capacity*100)/100;
  //for (colId=1; colId<=7; colId++) {
  colId = dojo.byId('colId_'+date).value;
  valSum=Math.round(parseFloat(dijit.byId('colSumWork_' + colId).get("value"))*100)/100;
  if (valSum > capacity) {
    //dojo.style('colSumWork_' + colId, "backgroung","red");
    dijit.byId('colSumWork_' + colId).set("class","imputationInvalidCapacity imputation");
  } else if (valSum < capacity) {
    dijit.byId('colSumWork_' + colId).set("class","displayTransparent imputation");
    //domClass.remove('colSumWork_' + colId, "imputationInvalidCapacity");
  } else {
    //dojo.style('colSumWork_' + colId, "backgroung","red");
    dijit.byId('colSumWork_' + colId).set("class","imputationValidCapacity imputation");
  }   
  //}
}

function validFutureWorkDate(){
  valid=function() {
    formChangeInProgress=false; 
    submitForm("../tool/saveImputation.php","resultDivMain", "listForm", true);
  };
  nbDays=dojo.byId('nbFutureDays').value;
  var msg=i18n('msgRealWorkInTheFuture',new Array(nbDays));
  showConfirm(msg,valid);
}

function saveImputation() {
  var futureInput=dojo.byId('daysWorkFuture').value != '0' ? true : false;
  var futureInputBlocking=dojo.byId('daysWorkFutureBlocking').value != '0' ? true : false;;  
  if(futureInputBlocking){
    nbDays=dojo.byId('nbFutureDaysBlocking').value;
    hideWait();
    showAlert(i18n('msgRealWorkInTheFutureBlocking',new Array(nbDays)));
  } else if (futureInput) {
    valid=function() {
      formChangeInProgress=false; 
      submitForm("../tool/saveImputation.php","resultDivMain", "listForm", true);
    };
    nbDays=dojo.byId('nbFutureDays').value;
    var msg=i18n('msgRealWorkInTheFuture',new Array(nbDays));
    hideWait();
    showConfirm(msg,valid);
  } else {
    formChangeInProgress=false; 
    submitForm("../tool/saveImputation.php","resultDivMain", "listForm", true);
  }
  
  enableWidget("userName");
  enableWidget("yearSpinner");
  enableWidget("weekSpinner");
  enableWidget("dateSelector");
  enableWidget("listDisplayOnlyCurrentWeekMeetings");
  enableWidget("listHideDone");
  enableWidget("listHideNotHandled");
  enableWidget("listShowIdle");
  enableWidget("listShowPlannedWork");
  enableWidget("showId");
  
}

function dispatchWork(refType, refId) {
  var params="&refType=" + refType+"&refId="+refId;
  if (dijit.byId('WorkElement_realWork')) {
    params+="&work="+dijit.byId('WorkElement_realWork').get('value');
  }
  loadDialog('dialogDispatchWork', null, true, params, true);
}

function addDispatchWorkLine(unit,nbLines) {
  index=updateDispatchWorkTotal();
  var tr = dojo.create("tr", {}, "dialogDispatchTable");
  var td1= dojo.create("td", {}, tr);
  var td2= dojo.create("td", {}, tr);
  var td3= dojo.create("td", {}, tr);
  var td4= dojo.create("td", {}, tr);
  var td5= dojo.create("td", {}, tr);
  var td6= dojo.create("td", {}, tr);
  
  workDateId="dispatchWorkDate_"+index;
  var dt = new dijit.form.DateTextBox({
    'class': "input",
    name: "dispatchWorkDate[]",
    id: workDateId,
    maxlength: "10",
    style: "width:100px; text-align: center;",
    hasDownArrow: "true"
  },td1);
  
  workResourceId="dispatchWorkResource_"+index;
  var lst = new dijit.form.FilteringSelect({
    'class': "input",
    id: workResourceId,
    name: "dispatchWorkResource[]",
    style: "width:150px;"
  },td3);
  
  workValueId="dispatchWorkValue_"+index;
  var val= new dijit.form.NumberTextBox({
    'class': "input",
    id: workValueId,
    name: "dispatchWorkValue[]",
    style: "width:50px;",
    value: 0,
    onChange:  function(){
      updateDispatchWorkTotal();
    },
    onKeyDown: function() {
      if (event.keyCode==110) {
        return intercepPointKey(this,event);
      }
    }
  },td5);
  td6.innerHTML+='&nbsp;'+unit;
  
  refreshList('idResource', 'idProject', dijit.byId('idProject').get('value'), null, workResourceId, false); 
}

function updateDispatchWorkTotal() {
  var cpt=1;
  var sum=0;
  while (dijit.byId('dispatchWorkValue_'+cpt)) {
    val=dijit.byId('dispatchWorkValue_'+cpt).get('value');
    if(isNaN(val)){
      val = 0;
    }
    if (val==0 && dojo.byId('dispatchWorkValue_'+cpt).value) {
      val=dojo.byId('dispatchWorkValue_'+cpt).value;
    }
    sum+=parseFloat(val);
    cpt++;
  }
  dijit.byId('dispatchWorkTotal').set('value',sum);
  return cpt; // return next available value
}

function dispatchWorkSave() {
  var listArray=new Array();
  var cpt=1;
  var futureInput=false;  
  var futureInputBlocking=false;  
  var nbDays=dojo.byId('nbFutureDays').value;
  var nbDaysTime=dojo.byId('nbFutureDaysTime').value;
  var nbDaysBlocking=dojo.byId('nbFutureDaysBlocking').value;
  var nbDaysBlockingTime=dojo.byId('nbFutureDaysBlockingTime').value;
  var isAdministrative=dojo.byId('isAdministrative').value==1;
  while (dijit.byId('dispatchWorkValue_'+cpt)) {
    res=dijit.byId('dispatchWorkResource_'+cpt).get('value');
    dat=dijit.byId('dispatchWorkDate_'+cpt).get('value');
    val=dijit.byId('dispatchWorkValue_'+cpt).get('value');
    if ( res && dat) {
      var key=res+"#"+dat;
      if (key in listArray) {
        showAlert(i18n('duplicateEntry'));
        return;
      }
      listArray[res+"#"+dat]='OK';
    }
    if(dat!=null && !isAdministrative){
      if(dat.getTime()/1000>nbDaysTime && val!=0)futureInput=true;
      if(dat.getTime()/1000>nbDaysBlockingTime && val!=0)futureInputBlocking=true;
    }
    cpt++;
  }
  
  if(futureInputBlocking && parseInt(nbDaysBlocking)!=-1){
    showAlert(i18n('msgRealWorkInTheFutureBlocking',new Array(nbDaysBlocking)));
  }else if(futureInput && parseInt(nbDays)!=-1){
    valid=function() {
      finishDispatchWorkSave();
    };
    var msg=i18n('msgRealWorkInTheFuture',new Array(nbDays));
    showConfirm(msg,valid);
  }else{
    finishDispatchWorkSave();
  }
  
}

function finishDispatchWorkSave(){
  updateDispatchWorkTotal();
  var callBack=function() { dijit.byId('dialogDispatchWork').hide();};
  loadContent("../tool/saveDispatchWork.php","resultDivMain", "dialogDispatchWorkForm", true, 'dispatchWork',false,false,callBack);
  
}

function formatDisplayToDecimal(val) {
  val=val+"";
  val=val.replace(browserLocaleDecimalSeparator,".");
  if (val==null || isNaN(val) || val=='') {
    val=0;
  }
  return parseFloat(val);
}
function formatDecimalToDisplay(val) {
  val=Math.round(val*100)/100;
  val=val+"";
  val=val.replace(".", browserLocaleDecimalSeparator);
  return val;
}

// PlannedWorkManual functions

var selectInterventionDateInProgress=0;
function selectInterventionDate(date,resource,period,allowDouble,event) {
  if(allowDouble && event.ctrlKey) period=period+'X';
  var idMode=(dojo.byId('idInterventionMode'))?dojo.byId('idInterventionMode').value:'';
  var letterMode=(dojo.byId('letterInterventionMode'))?dojo.byId('letterInterventionMode').value:'';
  var refType=(dojo.byId('interventionActivityType'))?dojo.byId('interventionActivityType').value:'';
  var refId=(dojo.byId('interventionActivityId'))?dojo.byId('interventionActivityId').value:'';
  var url="../tool/selectInterventionDate.php?date="+date+"&resource="+resource+"&period="+period;
  url+='&idMode='+idMode+"&letterMode="+letterMode;
  url+='&refType='+refType+"&refId="+refId;
  if (!idMode && !refType && !refId) {
    var msg=i18n('errorInterventionInput');
    displayMessageInResultDiv(msg,'WARNING',true,false);
    return;
  }
  selectInterventionDateInProgress+=1;
  showWait();
  dojo.xhrGet({
    url:url,
    handleAs: "text",
    load: function (data) {
      hideWait();
      var result={};
      if (data) try { result=JSON.parse(data); }
      catch (error) {
        return;
      }
      if (result.error) {
        displayMessageInResultDiv(result.error,'WARNING',true,false);
        return;
      }
      if (dijit.byId('assignmentAssignedWork') && dijit.byId('assignmentRealWork') && dijit.byId('assignmentLeftWork')) {
        //var result=JSON.parse(data);
        if (result.assigned!==null) dijit.byId('assignmentAssignedWork').set('value',result.assigned);
        if (result.real!==null) dijit.byId('assignmentRealWork').set('value',result.real);
        if (result.left!==null) dijit.byId('assignmentLeftWork').set('value',result.left);
        if (result.real!==null && result.left!==null) dijit.byId('assignmentPlannedWork').set('value',result.real+result.left);
      }
      selectInterventionDateInProgress-=1;
      if (selectInterventionDateInProgress==0) hideWait();
      if (dojo.byId('selectInterventionDataResult')) dojo.byId('selectInterventionDataResult').value=data;
      var refreshUrl='../tool/refreshInterventionTable.php';
      if (dojo.byId('plannedWorkManualInterventionDiv') 
       && dojo.byId('plannedWorkManualInterventionResourceList')
       && dojo.byId('plannedWorkManualInterventionMonthList') ) {
        refreshUrl="../tool/refreshInterventionTable.php?scope=intervention&resources="
          +dojo.byId('plannedWorkManualInterventionResourceList').value+'&months='
          +dojo.byId('plannedWorkManualInterventionMonthList').value;
        if (dojo.byId('plannedWorkManualInterventionSize')) refreshUrl+='&size='+dojo.byId('plannedWorkManualInterventionSize').value;
        loadDiv(refreshUrl,'plannedWorkManualInterventionDiv');}
      if (dojo.byId('plannedWorkManualAssignmentDiv')
       && dojo.byId('plannedWorkManualAssignmentResourceList')
       && dojo.byId('plannedWorkManualAssignmentMonthList') ) {
        refreshUrl="../tool/refreshInterventionTable.php?scope=assignment&resources="
          +dojo.byId('plannedWorkManualAssignmentResourceList').value+'&months='
          +dojo.byId('plannedWorkManualAssignmentMonthList').value;
        if (dojo.byId('plannedWorkManualAssignmentSize')) refreshUrl+='&size='+dojo.byId('plannedWorkManualAssignmentSize').value;
        if (dojo.byId('assignmentRefType') && dojo.byId('assignmentRefId')) {
          refreshUrl+='&refType='+dojo.byId('assignmentRefType').value+'&refId='+dojo.byId('assignmentRefId').value
        }
        loadDiv(refreshUrl,'plannedWorkManualAssignmentDiv');
      }
      refreshInterventionCapacity(date,refId,period);
    }
  });
}
function selectInterventionNoCapacity() {
  showInfo(i18n("selectInterventionNoCapacity"));
}
function selectInterventionMode(id,letter) {
  var mode='select';
  if (dojo.byId('idInterventionMode')) {
    if (dojo.byId('idInterventionMode').value==id) {
      dojo.byId('idInterventionMode').value='';
      if (dojo.byId('letterInterventionMode')) dojo.byId('letterInterventionMode').value='';
      mode='unselect';
    } else {
      dojo.byId('idInterventionMode').value=id;
      if (dojo.byId('letterInterventionMode')) dojo.byId('letterInterventionMode').value=letter;
    }
  }
  dojo.query(".interventionModeSelector").forEach(function(node, index, nodelist) {
    dojo.removeClass(node,'dojoxGridRowSelected');
  });
  if (mode=='select') { 
    dojo.query(".interventionModeSelector"+id).forEach(function(node, index, nodelist) {
      dojo.addClass(node,'dojoxGridRowSelected');
    });
  }
  saveDataToSession('selectInterventionPlannedWorkManual', id);
}

function selectInterventionActivity(refType,refId,peId) {
  var mode='select';
  if (dojo.byId('interventionActivityType') && dojo.byId('interventionActivityId')) {
    if (dojo.byId('interventionActivityType').value==refType && dojo.byId('interventionActivityId').value==refId) {
      dojo.byId('interventionActivityType').value='';
      dojo.byId('interventionActivityId').value='';
      mode='unselect';
    } else {
      dojo.byId('interventionActivityType').value=refType;
      dojo.byId('interventionActivityId').value=refId;
    }
  }
  dojo.query(".interventionActivitySelector").forEach(function(node, index, nodelist) {
    dojo.removeClass(node,'dojoxGridRowSelected');
  });
  dojo.query(".iconGoto").forEach(function(node, index, nodelist) {
  	dojo.removeClass(node,'iconGotoWhite16');
    });
  if (mode=='select') {
    dojo.query(".interventionActivitySelector"+peId).forEach(function(node, index, nodelist) {
      dojo.addClass(node,'dojoxGridRowSelected');
    });
    dojo.query(".iconGoto").forEach(function(node, index, nodelist) {
    	dojo.addClass(node,'iconGotoWhite16');
      });
  }
  if(mode=='unselect'){
    saveDataToSession('selectActivityPlannedWorkManual', '');
  }else{
    saveDataToSession('selectActivityPlannedWorkManual', refId);
  }
  
}

function saveInterventionCapacity(refType,refId,month,id) {
  var value=dijit.byId("interventionActivitySelector"+refId).get("value");
  if(isNaN(value) || value==null){
    value = 'noVal';
    dijit.byId("interventionActivitySelector"+refId).set("value"," ");
  }
  var url = '../tool/saveInterventionCapacity.php?refType='+refType +'&refId='+refId +'&month='+month+'&value='+value;
  dojo.xhrPut({
    url : url,
    form : 'listFormPlannedWorkManual',
    handleAs : "text",
    load : function(data) {
      document.getElementById('idImageInterventionActivitySelector'+refId).style.display="block";
      setTimeout("dojo.byId('idImageInterventionActivitySelector"+refId+"').style.display='none';", 1000);
      refreshPlannedWorkManualList();
    }
  });
}

function refreshInterventionCapacity(date,idActivity,period) {
  var url = '../tool/refreshInterventionCapacity.php?date='+date+'&refId='+idActivity +'&period='+period;
  dojo.xhrPut({
    url : url,
    form : 'listFormPlannedWorkManual',
    handleAs : "text",
    load : function(data) {
      if(data){
        var obj = JSON.parse(data);
        date = date.replace("-", "");
        date = date.replace("-", "");
        if(obj.length == 7 && dojo.byId(date+period+idActivity)){
          dojo.byId(date+period+idActivity).style.background=obj;
        }else if (dojo.byId(date+'AM'+idActivity) && dojo.byId(date+'PM'+idActivity)){
          dojo.byId(date+'AM'+idActivity).style.background=obj.substr(0,7);
          dojo.byId(date+'PM'+idActivity).style.background=obj.substr(7,13);
        }
      }
    }
  });
}