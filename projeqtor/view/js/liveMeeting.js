/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 *
 ******************************************************************************
 *** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
 ******************************************************************************
 *
 * This file is an add-on to ProjeQtOr, packaged as a plug-in module.
 * It is NOT distributed under an open source license. 
 * It is distributed in a proprietary mode, only to the customer who bought
 * corresponding licence. 
 * The company ProjeQtOr remains owner of all add-ons it delivers.
 * Any change to an add-ons without the explicit agreement of the company 
 * ProjeQtOr is prohibited.
 * The diffusion (or any kind if distribution) of an add-on is prohibited.
 * Violators will be prosecuted.
 *    
 *** DO NOT REMOVE THIS NOTICE ************************************************/
 
/* =============================================================================== */
/* Extra JavaScript for Live Meeting                                               */
/* =============================================================================== */
var whoSpeak=-1;
var whoSpeakLast=-1;
var allTime=[];
var oldResult="";
var timeToSend=30;
var timeToSendI=0;
var typeLoadBottom='normal';
var timerBeforeSave=60; //save toutes les 60 secondes
var firstTitleBase=false;

function liveMeetingGoStop(idMeeting){
  whoSpeak=-1;
  liveMeetingSave(false);
  gotoElement('Meeting',idMeeting);
}

function liveMeetingStart(){
  whoSpeak=-1;
  allTime=[];
  oldResult="";
  var editorType=dojo.byId("liveMeetingResultEditorType").value;
  if (editorType=="CK") { // CKeditor type
    ckEditorReplaceEditor("liveMeetingResult",999);
  } else if (editorType=="text") {

  } else if (dijit.byId("liveMeetingResult")) { // Dojo type editor
    dijit.byId("liveMeetingResult").set("class", "input");
    dijit.byId("liveMeetingResult").focus();
  }
  firstTitleBase=false;
  seekWhoSpeak();
}

function seekWhoSpeak(){
  if(!firstTitleBase)liveMeetingTitleNextIterator();
  exist=false;
  if(whoSpeak!=-1 && dojo.byId("timeFor"+whoSpeak)!=null){
    valTime=dojo.byId("timeFor"+whoSpeak).innerHTML;
    if(typeof allTime[whoSpeak]=='undefined'){
      allTime[whoSpeak]=timeToTimeStamp(valTime);
    }
    if((dojo.style( dojo.byId("timeFor"+whoSpeak),'color')=='RED' || dojo.style( dojo.byId("timeFor"+whoSpeak),'color')=='rgb(255, 0, 0)') && allTime[whoSpeak]>0)allTime[whoSpeak]=-allTime[whoSpeak];
    allTime[whoSpeak]-=1;
    require(["dojo/date/locale","dojo/domReady!"], function(dateLocale) {
      var time_leftD = new Date(1970,0,1);
      time_leftD.setSeconds(Math.abs(allTime[whoSpeak]));
      var nDate=dateLocale.format(time_leftD,{locale:"fr", selector:"time", timePattern:"H:m:s" });
      splitDate=nDate.split(':');
      splitDateF='';
      addZero='';
      if(parseInt(splitDate[0])<10)addZero='0';
      splitDateF=addZero+splitDate[0]+':';
      addZero='';
      if(parseInt(splitDate[1])<10)addZero='0';
      splitDateF+=addZero+splitDate[1]+':';
      addZero='';
      if(parseInt(splitDate[2])<10)addZero='0';
      splitDateF+=addZero+splitDate[2];
      dojo.byId("timeFor"+whoSpeak).innerHTML=splitDateF;
      dojo.style( dojo.byId("timeFor"+whoSpeak),'color',allTime[whoSpeak]<0 ? 'RED' : 'GREEN');
    });
    formChangeInProgress=true;
    setTimeout(function(){seekWhoSpeak();},1000);
    exist=true;
  }else if(dojo.byId("liveMeetingId")==null){
    allTime=[];
    whoSpeak=-1;
  }else{
    setTimeout(function(){seekWhoSpeak();},1000);
    exist=true;
  }
  if(exist){
    liveMeetingButtonSaveChange();
    timerBeforeSave=timerBeforeSave-1;
    if(timerBeforeSave<=0){
      liveMeetingSave();
      timerBeforeSave=60;
    }
  }
}

function liveMeetingButtonSaveChange(){
  if(typeof dijit.byId('saveButton')!='undefined')dijit.byId('saveButton').set('disabled', !formChangeInProgress);
}

function liveMeetingSave(noHistory){
  if(typeof noHistory =='undefined')noHistory=true;
  var nDate='';
  var allColor='';
  var allwhoSpeak='';
  var allOrganizator='';
  var allCanSpeak='';
  var showCounters='false';
  require(["dojo/date/locale","dojo/domReady!"], function(dateLocale) {
    for(var i in allTime){
      if(allwhoSpeak!='')allwhoSpeak+='|'+i;
      if(allwhoSpeak=='')allwhoSpeak=i;
      if(allOrganizator!='')allOrganizator+='|'+document.getElementById('blockFor'+i).getAttribute('isOrganizator');
      if(allOrganizator=='')allOrganizator=document.getElementById('blockFor'+i).getAttribute('isOrganizator');
      if(allCanSpeak!='')allCanSpeak+='|'+document.getElementById('blockFor'+i).getAttribute('canSpeak');
      if(allCanSpeak=='')allCanSpeak=document.getElementById('blockFor'+i).getAttribute('canSpeak');
      if(allColor!='')allColor+='|'+dojo.style( dojo.byId("timeFor"+i),'color');
      if(allColor=='')allColor=dojo.style( dojo.byId("timeFor"+i),'color');
      var time_leftD = new Date(1970,0,1);
      time_leftD.setSeconds(Math.abs(allTime[i]));
      var nDateT=dateLocale.format(time_leftD,{locale:"fr", selector:"time", timePattern:"H:m:s" });
      splitDate=nDateT.split(':');
      splitDateF='';
      addZero='';
      if(parseInt(splitDate[0])<10)addZero='0';
      splitDateF=addZero+splitDate[0]+':';
      addZero='';
      if(parseInt(splitDate[1])<10)addZero='0';
      splitDateF+=addZero+splitDate[1]+':';
      addZero='';
      if(parseInt(splitDate[2])<10)addZero='0';
      splitDateF+=addZero+splitDate[2];
      if(nDate!='')nDate+='|'+splitDateF;
      if(nDate=='')nDate=splitDateF;
    }
  });
  if(dojo.byId("hideCounters").checked==true){
    showCounters='true';
  }
  if(typeof CKEDITOR.instances.liveMeetingResult != 'undefined')CKEDITOR.instances.liveMeetingResult.updateElement();
  dojo.xhrPost({
    url : "../tool/liveMeetingUpdate.php?timeForSpeaker="+nDate+"&color="+allColor
    +"&idSpeaker="+allwhoSpeak+"&allOrganizator="+allOrganizator+"&allCanSpeak="+allCanSpeak+"&idLiveMeeting="+dojo.byId("liveMeetingId").value+"&noHistory="+(noHistory ? 1 : 0),
    form : "liveMeetingForm"
  });
  saveUserParameter("hideCounters",showCounters);
  formChangeInProgress=false;
  liveMeetingButtonSaveChange();
}

function liveMeetingStartTimerSpeak(id,name) {
  if(whoSpeak==id.split('blockFor')[1]){
    whoSpeak=-1;
    resetPlayPause();
  }else{
    if(whoSpeak!=-1)dojo.style(dojo.byId('blockFor'+whoSpeak),'background-color','#EEE');
    whoSpeak=id.split('blockFor')[1];
    //if(whoSpeakLast!=whoSpeak || whoSpeakLast==-1) liveMeetingAddToEditor(name);
    whoSpeakLast=whoSpeak;
    resetPlayPause();
  }
}

function resetPlayPause(){
  if(dojo.byId('containerRessource').children[0].childNodes[0].childNodes[0].length!=0){
    var listItem=dojo.byId('containerRessource').children[0].childNodes[0].childNodes[0].childNodes;
    var itePair=0;
    var enterPause=false;
    for(var item in listItem){
      if(itePair==0 && typeof listItem[item].id!='undefined'){
        var itemName=listItem[item].childNodes[1].childNodes[0].innerHTML;
        var itemId=listItem[item].id.split('blockFor')[1];
        if(whoSpeak==itemId){
          dijit.byId('playPauseButtonItem'+itemId).set('class','iconLiveMeetingPauseS iconSize16');
          liveMeetingAddToEditor(itemName);
          enterPause=true;
        }else if(whoSpeak==-1 && whoSpeakLast==itemId){
          dijit.byId('playPauseButtonItem'+itemId).set('class','iconLiveMeetingPlayS iconSize16');
        }else{
          dijit.byId('playPauseButtonItem'+itemId).set('class','iconLiveMeetingPlay iconSize16');
        }
        itePair+=1;
      }else{
        itePair=0;
      }
    }
    if(enterPause){
      dijit.byId('playPauseButton').set('iconClass','iconLiveMeetingPause22');
    }else{
      dijit.byId('playPauseButton').set('iconClass','iconLiveMeetingPlay22');
      liveMeetingAddToEditor('#pause#');
    }
    if(dijit.byId('playPauseButton').get('title')==i18n('liveMeetingTitlePlay'))dijit.byId('playPauseButton').set('title',i18n('liveMeetingTitlePause'));
    else
    dijit.byId('playPauseButton').set('title',i18n('liveMeetingTitlePlay'));
  }
}

function liveMeetingAddToEditor(toPass){
  var time = new Date();
  var now="";
  var nowFmt="";
  now=("0"+time.getHours()).slice(-2)+":"+("0"+time.getMinutes()).slice(-2)+":"+("0"+time.getSeconds()).slice(-2);
  nowFmt='&nbsp;<span style="font-size:75%;color:grey;">['+now+']</span>';
  var editorType=dojo.byId("liveMeetingResultEditorType").value;
  if (editorType=="CK") { // CKeditor type
    valTmp=CKEDITOR.instances['liveMeetingResult'].getData();
    valTmpT=CKEDITOR.instances['liveMeetingResult'].getData();
    if(valTmpT.trim()!='<div></div>' && valTmpT.trim()!='')valTmp+='<div></div>';
    if (toPass=='#pause#') {
      valTmp+='<div><b><i>'+i18n('liveMeetingPause')+'</i></b>'+nowFmt+'&nbsp;&nbsp;</div>';
    } else {
      valTmp+='<div><b>'+toPass+'</b>'+nowFmt+'&nbsp;:&nbsp;</div>';
    }
    CKEDITOR.instances['liveMeetingResult'].setData(valTmp);
    var moveToBottom=function() {
      // set focus to editor
      var editor = CKEDITOR.instances['liveMeetingResult'];
      editor.focus();
      // scroll editor to bottom
      var editorBody=window.top.dojo.query('.cke_wysiwyg_frame')[0].contentWindow.document.body;
      editorBody.scrollTop=editorBody.scrollHeight;
      // move cursor to end of editor text
      var s = editor.getSelection(); // getting selection
      var selected_ranges = s.getRanges(); // getting ranges
      var node = selected_ranges[0].startContainer; // selecting the starting node
      var parents = node.getParents(true);
      node = parents[parents.length - 2].getFirst();
      while (true) {
          var x = node.getNext();
          if (x == null) { break; }
          node = x;
      }
      s.selectElement(node);
      selected_ranges = s.getRanges();
      selected_ranges[0].collapse(false);  
      s.selectRanges(selected_ranges);
    };
    setTimeout(moveToBottom,10); // Must desyunchronise to potitionning of editor from main stream to have it work ... connot explain why but it works
  } else if (editorType=="text") {
    valTmp=dojo.byId("liveMeetingResult").value;
    if(valTmp.trim()!=""){
      dojo.byId("liveMeetingResult").value+="\n\n";
    }
    if (toPass=='#pause#') {
      dojo.byId("liveMeetingResult").value+=i18n('liveMeetingPause')+" ["+now+"] : \n";
    } else {
      dojo.byId("liveMeetingResult").value+=toPass+" ["+now+"] : \n";
    }
    finalValue=dojo.byId("liveMeetingResult").value;
    dojo.byId("liveMeetingResult").value="";
    dojo.byId("liveMeetingResult").focus();
    dojo.byId("liveMeetingResult").value=finalValue;
    dojo.byId("liveMeetingResult").scrollTop=dojo.byId("liveMeetingResult").scrollHeight;
  } else if (dijit.byId("liveMeetingResult")) { // Dojo type editor
    dijit.byId("liveMeetingResultEditor").focus();
    valTmp=dijit.byId("liveMeetingResult").get('value');
    valTmpT=dijit.byId("liveMeetingResult").get('value');
    if(valTmpT.trim()!='')valTmp=valTmp+'<br /><br />';
    if (toPass=='#pause#') {
      valTmp=valTmp+'<b><i>'+i18n('liveMeetingPause')+'</i></b>'+nowFmt+'&nbsp;&nbsp;<br /><br />';
    } else {
      valTmp=valTmp+'<b>'+toPass+'</b>'+nowFmt+'&nbsp;:&nbsp;<br /><br />';
    }
    dijit.byId("liveMeetingResult").set('value',valTmp);
    dijit.byId("liveMeetingResultEditor").set('value',valTmp);
    dijit.byId("liveMeetingResultEditor").placeCursorAtEnd();
    dijit.byId("liveMeetingResultEditor").document.activeElement.scrollTop=dijit.byId("liveMeetingResultEditor").document.activeElement.scrollHeight;
  }
}

function liveMeetingGoPlay(){
  var idFirst=whoSpeak;
  if(dijit.byId('playPauseButton').get('iconClass') == 'iconLiveMeetingPlay22'){
    dijit.byId('playPauseButton').set('iconClass','iconLiveMeetingPause22');
    if(idFirst==-1 && whoSpeakLast==-1)idFirst=dojo.byId('containerRessource').children[0].childNodes[0].childNodes[0].childNodes[0].id.split('blockFor')[1];
    if(idFirst==-1 && whoSpeakLast!=-1)idFirst=whoSpeakLast;
    liveMeetingStartTimerSpeak('blockFor'+idFirst,dojo.byId('nameFor'+idFirst).innerHTML);
  }else{
    if(idFirst==-1 && whoSpeakLast!=-1)idFirst=whoSpeakLast;
    dijit.byId('playPauseButton').set('iconClass','iconLiveMeetingPlay22');
    liveMeetingStartTimerSpeak('blockFor'+whoSpeak,dojo.byId('nameFor'+whoSpeak).innerHTML);
  }
}

function liveMeetingResizeEditor(loopCount) {
  var offsetHeight=dojo.byId("liveMeetingDivCenter").offsetHeight;
  var valueHeight=parseInt(offsetHeight);
  var editorType=dojo.byId("liveMeetingResultEditorType").value;
  if (editorType=="CK") { // CKeditor type
    var editor = CKEDITOR.instances['liveMeetingResult'];
    if (editor.status=='ready') {
      if(dijit.byId('hideCounters').get('checked')==true){
        valueHeight-=29;
      }else{
        valueHeight-=130;
      }
      editor.resize('100%',valueHeight,false,false);
    } else {
      if (!loopCount) loopCount=0;
      loopCount++;      
      if (loopCount>200) return;
      setTimeout('liveMeetingResizeEditor('+loopCount+')',10);
    }
  } else if (editorType=="text") {
    valueHeight-=130;
    dojo.byId('liveMeetingResult').style.height=valueHeight+'px';
    dojo.byId('liveMeetingResult').style.maxHeight=valueHeight+'px';
    dojo.byId('liveMeetingResult').style.minHeight=valueHeight+'px';
  } else if (dijit.byId("liveMeetingResult")) { // Dojo type editor  
    valueHeight-=130;
    dijit.byId('liveMeetingResultEditor').resize({h:valueHeight}); ;
  }
  
}

function liveMeetingIsOrganizator(idElm){
  if(document.getElementById('blockFor'+idElm).getAttribute('canSpeak')==0)liveMeetingDontSpeak(idElm, false);
  isOrganizator=document.getElementById('blockFor'+idElm).getAttribute('isOrganizator');
  time=document.getElementById('liveMeetingTime').getAttribute('value');
  timeOrganizator=document.getElementById('liveMeetingTimeOrganizator').getAttribute('value');
  thereIsOrganizator=-1;
  var pair=0;
  var listId=[];
  if(dojo.byId('containerRessourceTab') != null && typeof dojo.byId('containerRessourceTab').rows != 'undefined')for (var ite=0;ite<dojo.byId('containerRessourceTab').rows[0].cells.length; ite++){
    if(pair==0 && typeof dojo.byId('containerRessourceTab').rows[0].cells[ite].id != 'undefined' && dojo.byId('containerRessourceTab').rows[0].cells[ite].id.indexOf('blockFor')!=-1){
      id=dojo.byId('containerRessourceTab').rows[0].cells[ite].id;
      id=id.split('blockFor')[1];
      if(document.getElementById('blockFor'+id).getAttribute('canSpeak')==1){
        listId.push(id);
        if(document.getElementById('blockFor'+id).getAttribute('isOrganizator')==1){
          thereIsOrganizator=id;
        }
      }else{
        document.getElementById('blockFor'+id).setAttribute('isOrganizator',0);
      }
      pair=1;
    }else{
      pair=0;
    }
  }
  if(isOrganizator==0 && thereIsOrganizator!=-1 && thereIsOrganizator!=idElm){
    for(var idL in listId){
      idL=listId[idL];
      if(idL==idElm){
        timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))+parseInt(timeOrganizator)-parseInt(time),idL);
        changeOrganizator(idL);
      }else if(idL==thereIsOrganizator){
        timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))-parseInt(timeOrganizator)+parseInt(time),idL);
        changeOrganizator(idL);
      }
    }
  }else if(isOrganizator==1 && thereIsOrganizator!=-1 && thereIsOrganizator==idElm){
    for(var idL in listId){
      idL=listId[idL];
      if(idL==idElm){
        timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))-parseInt(time)+parseInt(time/(listId.length)),idL);
        document.getElementById('liveMeetingTimeOrganizator').setAttribute('value',0);
        changeOrganizator(idL);
      }else{
        timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))+parseInt(time/(listId.length)),idL);
        document.getElementById('liveMeetingTime').setAttribute('value',parseInt(time)+parseInt(time/(listId.length)));
      }
    }
  }else{
    for(var idL in listId){
      idL=listId[idL];
      if(idL==idElm){
        timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))-parseInt(time)+2*parseInt(time*listId.length/(listId.length+1)),idL);
        document.getElementById('liveMeetingTimeOrganizator').setAttribute('value',2*parseInt(time*listId.length/(listId.length+1)));
        changeOrganizator(idL);
      }else{
        timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))-parseInt(time)+parseInt(time*listId.length/(listId.length+1)),idL);
        document.getElementById('liveMeetingTime').setAttribute('value',parseInt(time*listId.length/(listId.length+1)));
      }
    }
  }
  liveMeetingSave();
  liveMeetingVerifButton();
}

function changeOrganizator(id){
  if(document.getElementById('blockFor'+id).getAttribute('isOrganizator')==1){
    dijit.byId('buttonChangeTimer'+id).set('iconClass','iconLiveMeetingNormal iconSize22');
    document.getElementById('blockFor'+id).setAttribute('isOrganizator',0);
  }else{
    dijit.byId('buttonChangeTimer'+id).set('iconClass','iconLiveMeetingOrganizator iconSize22');
    document.getElementById('blockFor'+id).setAttribute('isOrganizator',1);
  }
  liveMeetingTitleNextIterator();
}

function changeCanSpeak(id){
  if(document.getElementById('blockFor'+id).getAttribute('canSpeak')==1){
    dijit.byId('buttonChangeTimer'+id).set('iconClass','iconLiveMeetingCanSpeak iconSize22');
    document.getElementById('blockFor'+id).setAttribute('canSpeak',0);
  }else{
    dijit.byId('buttonChangeTimer'+id).set('iconClass','iconLiveMeetingNormal iconSize22');
    document.getElementById('blockFor'+id).setAttribute('canSpeak',1);
  }
  liveMeetingTitleNextIterator();
}

function liveMeetingVerifButton(){
  var pair=0;
  var listId=[];
  if(dojo.byId('containerRessourceTab') != null && typeof dojo.byId('containerRessourceTab').rows != 'undefined')for (var ite=0;ite<dojo.byId('containerRessourceTab').rows[0].cells.length; ite++){
    if(pair==0 && typeof dojo.byId('containerRessourceTab').rows[0].cells[ite].id != 'undefined' && dojo.byId('containerRessourceTab').rows[0].cells[ite].id.indexOf('blockFor')!=-1){
      id=dojo.byId('containerRessourceTab').rows[0].cells[ite].id;
      id=id.split('blockFor')[1];
      if(document.getElementById('blockFor'+id).getAttribute('canSpeak')==1){
        listId.push(id);
      }
      pair=1;
    }else{
      pair=0;
    }
  }
  if(listId.length==1){
    dijit.byId('buttonChangeTimer'+listId[0]).set('disabled',true);
  }else{
    for(var ide in listId){
      dijit.byId('buttonChangeTimer'+listId[ide]).set('disabled',false);
    }
  }
}

function liveMeetingDontSpeak(idElm, needSave){
  if(typeof needSave == 'undefined')needSave=true;
  isOrganizator=document.getElementById('blockFor'+idElm).getAttribute('isOrganizator');
  canSpeak=document.getElementById('blockFor'+idElm).getAttribute('canSpeak');
  time=document.getElementById('liveMeetingTime').getAttribute('value');
  timeOrganizator=document.getElementById('liveMeetingTimeOrganizator').getAttribute('value');
  var thereIsOrganizator=-1;
  var pair=0;
  var listId=[];
  if(dojo.byId('containerRessourceTab') != null && typeof dojo.byId('containerRessourceTab').rows != 'undefined')for (var ite=0;ite<dojo.byId('containerRessourceTab').rows[0].cells.length; ite++){
    if(pair==0 && typeof dojo.byId('containerRessourceTab').rows[0].cells[ite].id != 'undefined' && dojo.byId('containerRessourceTab').rows[0].cells[ite].id.indexOf('blockFor')!=-1){
      id=dojo.byId('containerRessourceTab').rows[0].cells[ite].id;
      id=id.split('blockFor')[1];
      if(document.getElementById('blockFor'+id).getAttribute('canSpeak')==1){
        listId.push(id);
        if(document.getElementById('blockFor'+id).getAttribute('isOrganizator')==1){
          thereIsOrganizator=id;
        }
      }
      pair=1;
    }else{
      pair=0;
    }
  }
  baseTime=time;
  if(isOrganizator==1)baseTime=timeOrganizator;
  if(canSpeak==1){
    timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idElm).innerHTML, idElm))-baseTime,idElm);
    changeCanSpeak(idElm);
    document.getElementById('liveMeetingTime').setAttribute('value',0);
    document.getElementById('liveMeetingTimeOrganizator').setAttribute('value',0);
    
    for(var idL in listId){
      idL=listId[idL];
      if(idL!=idElm){
        if(isOrganizator==1){
          timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))+parseInt(timeOrganizator/(listId.length-1)),idL);
          document.getElementById('liveMeetingTime').setAttribute('value',parseInt(parseInt(time)+parseInt(timeOrganizator/(listId.length-1))));
          document.getElementById('liveMeetingTimeOrganizator').setAttribute('value',0);
        }else{
          if(thereIsOrganizator!=-1){
            isOrganizatorTmp=document.getElementById('blockFor'+idL).getAttribute('isOrganizator');
            if(isOrganizatorTmp==1){
              timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))+2*parseInt(time/(listId.length)),idL);
              document.getElementById('liveMeetingTimeOrganizator').setAttribute('value',parseInt(timeOrganizator)+2*parseInt(time/(listId.length)));
              
            }else{
              timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))+parseInt(time/(listId.length)),idL);
              document.getElementById('liveMeetingTime').setAttribute('value',parseInt(time)+parseInt(time/(listId.length)));
            }
          }else{
            timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))+parseInt(time/(listId.length-1)),idL);
            document.getElementById('liveMeetingTime').setAttribute('value',parseInt(time)+parseInt(time/(listId.length-1)));
          }
        }
      }
    }
  }else{
    if(isOrganizator==1 || thereIsOrganizator!=-1){
      if(listId.length!=1 && timeOrganizator!=0){
        time=(parseInt(time)*(listId.length-1)+parseInt(timeOrganizator))/(listId.length+2);
      }else if(listId.length!=1 && timeOrganizator==0){
        time=(parseInt(time)*(listId.length))/(listId.length+2);
      }else if(time!=0){
        time=parseInt(time)/(listId.length+2);
      }else{
        time=parseInt(timeOrganizator)/3;
      }
      timeOrganizator=2*parseInt(time);
      document.getElementById('liveMeetingTimeOrganizator').setAttribute('value',timeOrganizator);
    }else{
      time=parseInt(time)*listId.length/(listId.length+1);
      document.getElementById('liveMeetingTimeOrganizator').setAttribute('value',0);
    }
    baseTime=time;
    document.getElementById('liveMeetingTime').setAttribute('value',time);
    if(isOrganizator==1)baseTime=timeOrganizator;
    timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idElm).innerHTML, idElm))+baseTime,idElm);
    changeCanSpeak(idElm);
    for(var idL in listId){
      idL=listId[idL];
      if(isOrganizator==1){
        timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))-parseInt(timeOrganizator/(listId.length)),idL);
      }else{
        if(thereIsOrganizator!=-1){
          isOrganizatorTmp=document.getElementById('blockFor'+idL).getAttribute('isOrganizator');
          if(isOrganizatorTmp==1){
            timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))-2*parseInt(time/(listId.length+1)),idL);
            
          }else{
            timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))-parseInt(time/(listId.length+1)),idL);
          }
        }else{
          timeStampToTime(parseInt(timeToTimeStamp(document.getElementById('timeFor'+idL).innerHTML, idL))-parseInt(time/(listId.length)),idL);
        }
      }
      
    }
  }
  if(needSave)liveMeetingSave(); 
  liveMeetingVerifButton();
}

function timeToTimeStamp(time, idLL){
  if(typeof idLL =='undefined'){
    splitVal=time.split(':');
    return parseInt(splitVal[0])*3600+parseInt(splitVal[1])*60+parseInt(splitVal[2]);
  }else{
    mult=1;
    if((dojo.style( dojo.byId("timeFor"+idLL),'color')=='RED' || dojo.style( dojo.byId("timeFor"+idLL),'color')=='rgb(255, 0, 0)'))mult=-1;
    splitVal=time.split(':');
    return mult*(parseInt(splitVal[0])*3600+parseInt(splitVal[1])*60+parseInt(splitVal[2]));
  }
}

function timeStampToTime(time, idEl){
  require(["dojo/date/locale","dojo/domReady!"], function(dateLocale) {
    allTime[idEl]=time;
    var time_leftD = new Date(1970,0,1);
    time_leftD.setSeconds(Math.abs(allTime[idEl]));
    var nDate=dateLocale.format(time_leftD,{locale:"fr", selector:"time", timePattern:"H:m:s" });
    splitDate=nDate.split(':');
    splitDateF='';
    addZero='';
    if(parseInt(splitDate[0])<10)addZero='0';
    splitDateF=addZero+splitDate[0]+':';
    addZero='';
    if(parseInt(splitDate[1])<10)addZero='0';
    splitDateF+=addZero+splitDate[1]+':';
    addZero='';
    if(parseInt(splitDate[2])<10)addZero='0';
    splitDateF+=addZero+splitDate[2];
    dojo.byId("timeFor"+idEl).innerHTML=splitDateF;
    dojo.style( dojo.byId("timeFor"+idEl),'color',allTime[idEl]<0 ? 'RED' : 'GREEN');
  });
}

function thereIsOragnizator(){
  var thereIsOrganizator=-1;
  var pair=0;
  if(dojo.byId('containerRessourceTab') != null && typeof dojo.byId('containerRessourceTab').rows != 'undefined')for (var ite=0;ite<dojo.byId('containerRessourceTab').rows[0].cells.length; ite++){
    if(pair==0 && typeof dojo.byId('containerRessourceTab').rows[0].cells[ite].id != 'undefined' && dojo.byId('containerRessourceTab').rows[0].cells[ite].id.indexOf('blockFor')!=-1){
      id=dojo.byId('containerRessourceTab').rows[0].cells[ite].id;
      id=id.split('blockFor')[1];
      if(document.getElementById('blockFor'+id).getAttribute('canSpeak')==1){
        if(document.getElementById('blockFor'+id).getAttribute('isOrganizator')==1){
          thereIsOrganizator=id;
        }
      }
      pair=1;
    }else{
      pair=0;
    }
  }
  return thereIsOrganizator;
}

function liveMeetingTitleNextIterator(){
  var pair=0;
  if(dojo.byId('containerRessourceTab') != null && typeof dojo.byId('containerRessourceTab').rows != 'undefined'){
    for (var ite=0;ite<dojo.byId('containerRessourceTab').rows[0].cells.length; ite++){
      if(pair==0 && typeof dojo.byId('containerRessourceTab').rows[0].cells[ite].id != 'undefined' && dojo.byId('containerRessourceTab').rows[0].cells[ite].id.indexOf('blockFor')!=-1){
        id=dojo.byId('containerRessourceTab').rows[0].cells[ite].id;
        id=id.split('blockFor')[1];
        liveMeetingTitleNext(id);
        pair=1;
      }else{
        pair=0;
      }
    }
  }else{
    if (dojo.byId('playPauseButton')) dojo.byId('playPauseButton').setAttribute('disabled','disabled');
  }
}

function liveMeetingTitleNext(idN){
  thereIs=thereIsOragnizator();
  isOrganizator=document.getElementById('blockFor'+idN).getAttribute('isOrganizator');
  canSpeak=document.getElementById('blockFor'+idN).getAttribute('canSpeak');
  tabArray=[];
  if(canSpeak==1 && isOrganizator==0)tabArray.push(i18n('liveMeetingNormal'));
  else if(isOrganizator==1)tabArray.push(i18n('liveMeetingOrganizator'));
  else if(canSpeak==0)tabArray.push(i18n('liveMeetingCantSpeak'));
  if(thereIs!=-1){
    if(thereIs==idN){
      tabArray.push(i18n('liveMeetingNormal'));
    }else{
      if(canSpeak==0)tabArray.push(i18n('liveMeetingNormal'));
      if(canSpeak==1)tabArray.push(i18n('liveMeetingCantSpeak'));
    }
  }else{
    if(canSpeak==1 && isOrganizator==0)tabArray.push(i18n('liveMeetingCantSpeak'));
    else if(canSpeak==0)tabArray.push(i18n('liveMeetingOrganizator'));
    else if(isOrganizator==0)tabArray.push(i18n('liveMeetingNormal'));
  }
  
  if(dijit.byId('buttonChangeTimer'+idN)!=null)dijit.byId('buttonChangeTimer'+idN).set('title', i18n('liveMeetingTitleBase',tabArray));
}

function hideCounters(){
  if(dijit.byId('hideCounters').get('checked')==true){
    dojo.byId('tabeTimeEditor').style.display='none';
    if(dijit.byId('playPauseButton').get('iconClass') != 'iconLiveMeetingPlay22'){
      liveMeetingGoPlay();
    }
  }else{
    dojo.byId('tabeTimeEditor').style.display='block';
  }
  liveMeetingResizeEditor();
}

function addNewLinkMeeting (item,comboName,idProj){
  var objName=dijit.byId(item).get('value');
  if(!objName)return;
  var objClass=item.substring(3);
  if(objName && objClass){
    dojo.byId('comboName').value=comboName;
    dojo.byId('comboClass').value=objClass;
    showWait();
    dojo.xhrPost({
          url : "../tool/saveLiveMeetingAttachment.php?objectClass="+objClass+'&objName='+objName+'&idProj='+idProj,
          form :null,
          handleAs : "text",
          load : function(data, args) {
            var contentWidget=dijit.byId("resultDivMain");
            if (!contentWidget) {
              return;
            }
            contentWidget.set('content', data);
            var lastOperationStatus=window.top.dojo.byId('lastOperationStatus');
            var lastSaveId=window.top.dojo.byId('lastSaveId');
            if (lastOperationStatus.value == "OK") {
              selectDetailItem(lastSaveId.value,objName);
            } else {
              consoleTraceLog("Error on addNewLinkMeeting() : return from saveLiveMeetingAttachment.php is not in status OK");
              showAlert(data);
              dojo.byId("resultDivMain").style.display='block';
            }
            hideWait();
          },
          error : function() {
            hideWait();
          }
        });

    }
  
}




