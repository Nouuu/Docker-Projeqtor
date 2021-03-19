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
/* Extra JavaScript for custom screen management                           */
/* =============================================================================== */

var lastIdKanban=-1;
var anchorTmp;
var itemDisabled=[];
var targetLast;
var kanbanScrollTop=0;

function sendChangeKanBan(id,type,newStatut,target,oldStatut){
  targetLast=target;
  dojo.style(dojo.byId('itemRow'+id+'-'+type),'display',"block");
  if(dojo.byId('plannedWorkC'+newStatut)!=null){
    plannedTicket=parseFloat(dojo.byId('plannedWork'+id).getAttribute("valueWork"));
    
    realTicket=parseFloat(dojo.byId('realWork'+id).getAttribute("valueWork"));
    leftTicket=parseFloat(dojo.byId('leftWork'+id).getAttribute("valueWork"));
  
    dojo.byId('plannedWorkC'+newStatut).setAttribute("valueWork",(parseFloat(dojo.byId('plannedWorkC'+newStatut).getAttribute("valueWork"))+plannedTicket));
    dojo.byId('plannedWorkC'+newStatut).innerHTML=workFormatter(dojo.byId('plannedWorkC'+newStatut).getAttribute("valueWork"));
    
    dojo.byId('realWorkC'+newStatut).setAttribute("valueWork",(parseFloat(dojo.byId('realWorkC'+newStatut).getAttribute("valueWork"))+realTicket));
    dojo.byId('realWorkC'+newStatut).innerHTML=workFormatter(dojo.byId('realWorkC'+newStatut).getAttribute("valueWork"));
    
    dojo.byId('leftWorkC'+newStatut).setAttribute("valueWork",(parseFloat(dojo.byId('leftWorkC'+newStatut).getAttribute("valueWork"))+leftTicket));
    dojo.byId('leftWorkC'+newStatut).innerHTML=workFormatter(dojo.byId('leftWorkC'+newStatut).getAttribute("valueWork"));

    dojo.byId('plannedWorkC'+oldStatut).setAttribute("valueWork",(parseFloat(dojo.byId('plannedWorkC'+oldStatut).getAttribute("valueWork"))-plannedTicket));
    dojo.byId('plannedWorkC'+oldStatut).innerHTML=workFormatter(dojo.byId('plannedWorkC'+oldStatut).getAttribute("valueWork"));
    
    
    dojo.byId('realWorkC'+oldStatut).setAttribute("valueWork",(parseFloat(dojo.byId('realWorkC'+oldStatut).getAttribute("valueWork"))-realTicket));
    dojo.byId('realWorkC'+oldStatut).innerHTML=workFormatter(dojo.byId('realWorkC'+oldStatut).getAttribute("valueWork"));
    
    dojo.byId('leftWorkC'+oldStatut).setAttribute("valueWork",(parseFloat(dojo.byId('leftWorkC'+oldStatut).getAttribute("valueWork"))-leftTicket));
    dojo.byId('leftWorkC'+oldStatut).innerHTML=workFormatter(dojo.byId('leftWorkC'+oldStatut).getAttribute("valueWork"));
  }
  
  dojo.byId('numberTickets'+newStatut).innerHTML=parseFloat(dojo.byId('numberTickets'+newStatut).innerHTML)+1;
  dojo.byId('numberTickets'+oldStatut).innerHTML=parseFloat(dojo.byId('numberTickets'+oldStatut).innerHTML)-1;
  
  var nodeTicket=dojo.byId('itemRow'+id+'-'+type);
  dojo.removeClass(dojo.byId('itemRow'+id+'-'+type),'dojoDndHandle');
  var oldColor=dojo.style(nodeTicket, "background-color");
  dojo.style(nodeTicket, "background-color", '#999');
  showWait();
  dojo.xhrGet({
    url : "../tool/kanbanUpdate.php?idTicket="+id+"&type="+type+"&newStatut="+newStatut+"&idKanban="+dojo.byId('idKanban').value,
    load : function(data) {
      if(data.indexOf('messageError/split/')!=-1){
		    //hideWait();
        loadContent("../view/kanbanView.php?idKanban="+dojo.byId('idKanban').value, "divKanbanContainer");
        showAlert(data.split('messageError/split/')[1], null);
      }else if (data.indexOf('&idTicket=')!=-1) {
        functionCallback=function(){
          kanbanFindTitle('update');
		      hideWait();
        };
        if(data.indexOf('needResult')!=-1 && typeof dojo.byId("kanbanResultEditorType") != 'undefined')functionCallback=function(){
          var editorType=dojo.byId("kanbanResultEditorType").value;
          if (editorType=="CK") { // CKeditor type
            ckEditorReplaceEditor("kanbanResult",999);
          } else if (dijit.byId("liveMeetingResult")) { // Dojo type editor
            dijit.byId("kanbanResult").set("class", "input");
          }
          kanbanFindTitle('update');
        };
        loadDialog('dialogKanbanUpdate', functionCallback, true, data+"&typeDynamic=update", true, false);
        dojo.style(nodeTicket, "background-color", oldColor);
        dojo.addClass(nodeTicket,'dojoDndHandle');
      }else{
        dataUserThumb=data.split('[splitcustom2]')[1]; 
    	  idKanban = dojo.byId("idKanban").value;
        splitData=data.split('[splitcustom]');
        id=splitData[0].split('-')[0];
        type=splitData[0].split('-')[1];
        newStatut=splitData[0].split('-')[2];
        var oldAt=dojo.byId('itemRow'+id+'-'+type).getAttribute("dndType");
        var addTo='';
        if(oldAt.indexOf('-')!=-1)addTo+=oldAt.split(oldAt.split('-')[0])[1];
        if(type=='Status')target.getItem('itemRow'+id+'-'+type).type[0]="typeRow"+newStatut+addTo;
        dojo.byId('itemRow'+id+'-'+type).setAttribute('fromC',newStatut);
        dojo.byId('divPrincItem'+id).innerHTML=splitData[1].split('[splitcustom2]')[0];
      	var callback=function() {
      	  nodeTicket=dojo.byId('itemRow'+id+'-'+type);
      	  dojo.addClass(nodeTicket,'dojoDndItemAnchor');
      	  dojo.style(nodeTicket, "background-color", oldColor);
          dojo.addClass(nodeTicket,'dojoDndHandle');
          dijit.byId("descr_"+id).value = "truncated";
          dojo.byId('userThumbTicket'+id).innerHTML=dataUserThumb;
      	  hideWait();
      	};
        loadDiv('../tool/kanbanRefreshTicket.php?id='+id+'&type='+type+'&idKanban='+idKanban+'&from='+newStatut,'itemRow'+id+'-'+type,null,callback);
      }
    },
    error : function(data) {
      showError(data);
      hideWait();
    }
  });
}

function saveKanbanResult(id,type,newStatut){
  showWait();
  tmpCkEditor='';
  if(typeof CKEDITOR.instances.kanbanResult != 'undefined'){
    CKEDITOR.instances.kanbanResult.updateElement();
    tmpCkEditor=CKEDITOR.instances.kanbanResult.document.getBody().getText();
  }
  if(((typeof dijit.byId('kanbanResourceList') != 'undefined' && dijit.byId('kanbanResourceList').get('value').trim()!='') || typeof dijit.byId('kanbanResourceList') == 'undefined')
  &&( (typeof CKEDITOR.instances.kanbanResult == 'undefined' || (typeof CKEDITOR.instances.kanbanResult != 'undefined' && tmpCkEditor.trim()!='')) && ((typeof dijit.byId('kanbanResult') != 'undefined' && dijit.byId('kanbanResult').get('value').trim()!='') || typeof dijit.byId('kanbanResult') == 'undefined'))
  &&((typeof dijit.byId('kanbanResolutionList') != 'undefined' && dijit.byId('kanbanResolutionList').get('value').trim()!='') || typeof dijit.byId('kanbanResolutionList') == 'undefined')){
    dojo.xhrPost({
      url : "../tool/kanbanUpdate.php?idTicket="+id+"&type="+type+"&newStatut="+newStatut+"&needIdKanban=kanbanResult&idKanban="+dojo.byId('idKanban').value,
      form : "kanbanResultForm",
      handleAs : "text",
      load : function(data, args) {
        formChangeInProgress=false;
        dijit.byId('dialogKanbanUpdate').hide();
        if(data.indexOf('messageError/split/')!=-1){
		      //hideWait();
          loadContent("../view/kanbanView.php?idKanban="+dojo.byId('idKanban').value, "divKanbanContainer");
          showAlert(data.split('messageError/split/')[1], null);
        }else{
          dataUserThumb=data.split('[splitcustom2]')[1]; 
          splitData=data.split('[splitcustom]');
          newStatut=splitData[0].split('-')[2];
          var oldAt=dojo.byId('itemRow'+id+'-'+type).getAttribute("dndType");
          var addTo='';
          if(oldAt.indexOf('-')!=-1)addTo+=oldAt.split(oldAt.split('-')[0])[1];
          if(type=='Status')targetLast.getItem('itemRow'+id+'-'+type).type[0]="typeRow"+newStatut+addTo;
          dojo.byId('itemRow'+id+'-'+type).setAttribute('fromC',newStatut);
          dojo.byId('divPrincItem'+id).innerHTML=splitData[1].split('[splitcustom2]')[0];
          dojo.byId('userThumbTicket'+id).innerHTML=dataUserThumb;
		      hideWait();
        }
      },
      error : function() {
        hideWait();
      }
    });
  }else{
    var finalMessage='';
    
    if((typeof dijit.byId('kanbanResourceList') != 'undefined' && dijit.byId('kanbanResourceList').get('value').trim()==''))
    {
      finalMessage+=i18n('messageMandatory',[i18n('colMandatoryResourceOnHandled')]);
    }
    valCk='';
    if(typeof CKEDITOR.instances.kanbanResult !='undefined')valCk=CKEDITOR.instances.kanbanResult.getData();
    if(!( (typeof CKEDITOR.instances.kanbanResult == 'undefined' || (typeof CKEDITOR.instances.kanbanResult != 'undefined' && tmpCkEditor.trim()!='')) && ((typeof dijit.byId('kanbanResult') != 'undefined' && dijit.byId('kanbanResult').get('value').trim()!='') || typeof dijit.byId('kanbanResult') == 'undefined')))
    {
      if(finalMessage!='')finalMessage+='<br/>';
      finalMessage+=i18n('messageMandatory',[i18n('colMandatoryResultOnDone')]);
    }
    
    if((typeof dijit.byId('kanbanResolutionList') != 'undefined' && dijit.byId('kanbanResolutionList').get('value').trim()==''))
    {
      if(finalMessage!='')finalMessage+='<br/>';
      finalMessage+=i18n('messageMandatory',[i18n('colIdResolution')]);
    }

    if(finalMessage!='')showAlert(finalMessage);
    hideWait();	
  }
}

function plgAddKanban(){
  var name=dijit.byId("kanbanName").get("value");
  var type=dijit.byId("kanbanTypeList").get("value");
  var shared=dijit.byId("kanbanShared").get("value");

  if(name.trim()!='' && type!=''){
    showWait();
  dojo.xhrPost({
    url : "../tool/kanbanAdd.php?name="+name+"&type="+type+"&shared="+shared,
    form : "kanbanResultForm",
    handleAs : "text",
    load : function(data, args) {
      formChangeInProgress=false;
      loadContent("../view/kanbanView.php?idKanban="+data, "divKanbanContainer");
      dijit.byId('dialogKanbanUpdate').hide();
      //hideWait();
    },
    error : function() {
      hideWait();
    }
  });
  }else{
    if(type=='' && name.trim()==''){
      showAlert(i18n('messageMandatory',[i18n('Type')])+'</br>'+i18n('messageMandatory',[i18n('colName')]));
    }else if(type==''){
      showAlert(i18n('messageMandatory',[i18n('Type')]));
    }else if(name.trim()==''){
      showAlert(i18n('messageMandatory',[i18n('colName')]));
    }
  }
}

function delKanban(idKanban, i18nF,idFrom){
  if(typeof idFrom=='undefined')idFrom='';
  showConfirm(i18nF, function(){
    showWait();
    addUrl='';
    if(idFrom!=''){
      addUrl='&idFrom='+idFrom;
    }
    dojo.xhrGet({
      url : "../tool/kanbanDel.php?idKanban="+idKanban+addUrl,
      handleAs : "text",
      load : function(data, args) {
        formChangeInProgress=false;
        loadContent("../view/kanbanView.php?idKanban="+data, "divKanbanContainer");
        //hideWait();
      },
      error : function() {
        hideWait();
      }
    });
  });
}

function plgAddColumnKanban(idKanban,idFrom,isStatut,typeD){
  var name="";
  if(typeof(dijit.byId("kanbanName"))!='undefined')name=dijit.byId("kanbanName").get("value");
  var type='';
  if(typeof(dijit.byId("kanbanTypeList"))!='undefined')type=dijit.byId("kanbanTypeList").get("value");
  if((idFrom==-1 && ((name.trim()!='' && isStatut) || (!isStatut && type.trim()!=''))) || (idFrom!=-1 && isStatut && name.trim()!='') || (idFrom!=-1 && !isStatut)){
	showWait();
	dojo.xhrPost({
    url : "../tool/kanbanColumnAdd.php?name="+name+"&type="+type+"&idKanban="+idKanban+'&idFrom='+idFrom,
    form : "kanbanResultForm",
    handleAs : "text",
    load : function(data, args) {
      formChangeInProgress=false;
      loadContent("../view/kanbanView.php?idKanban="+idKanban, "divKanbanContainer");
      dijit.byId('dialogKanbanUpdate').hide();
      //hideWait();
    },
    error : function() {
      hideWait();
    }
  });
  }else{
    var finalMessage='';
    if(name.trim()=='' && isStatut && idFrom==-1){
      finalMessage+=i18n('messageMandatory',[i18n('colName')]);
    }
    
    if(!isStatut && type.trim()=='' && idFrom==-1)
    {
      var trad="colIdTargetProductVersion";
      if(typeD=="Activity")trad="colPlanningActivity";
      if(typeD=="Status")trad="colIdStatus";
      if(finalMessage!='')finalMessage+='<br/>';
      finalMessage+=i18n('messageMandatory',[i18n(trad)]);
    }
    
    if(idFrom!=-1 && isStatut && name.trim()==''){
      if(finalMessage!='')finalMessage+='<br/>';
      finalMessage+=i18n('messageMandatory',[i18n('colName')]);
    }
    if(finalMessage!='')showAlert(finalMessage); 
  }
}

function plgShareKanban(idKanban){
  showWait();
  dojo.xhrGet({
    url : "../tool/kanbanShare.php?idKanban="+idKanban,
    handleAs : "text",
    load : function(data, args) {
      loadContent("../view/kanbanView.php?idKanban="+data, "divKanbanContainer");
      //hideWait();
    },
    error : function() {
      hideWait();
    }
  });
}

function kanbanGoToKan(id){
  lastIdKanban=id;
  showWait();
  loadContent("../view/kanbanView.php?idKanban="+lastIdKanban, "divKanbanContainer");
}

function kanbanSeeWork(){
  showWait();
  dojo.xhrGet({
    url : "../tool/kanbanSeeWork.php",
    handleAs : "text",
    load : function(data, args) {
      loadContent("../view/kanbanView.php?idKanban="+data, "divKanbanContainer");
      //hideWait();
    },
    error : function() {
      hideWait();
    }
  });
}

function kanbanFindTitle(type){
  title=i18n('dialogKanbanUpdate');
  if(type=="addKanban"){
    title= i18n('kanbanAdd');
  }else if(type=="addColumnKanban"){
    title= i18n('kanbanAddColumn');
  }else if (type=="editColumnKanban"){
    title= i18n('kanbanColumnEdit');
  }else if(type=="update"){
    title= i18n('kanbanTicketEdit');
  }
  dijit.byId('dialogKanbanUpdate').set('title',title);
}

function copyKanban(idKanban){
  showWait();
  dojo.xhrGet({
    url : "../tool/kanbanCopy.php?idKanban="+idKanban,
    handleAs : "text",
    load : function(data, args) {
      loadContent("../view/kanbanView.php?idKanban="+idKanban, "divKanbanContainer");
      //hideWait();
    },
    error : function() {
      hideWait();
    }
  });
}

function editKanban(idKanban){
  loadDialog('dialogKanbanUpdate', function(){kanbanFindTitle('editKanban');}, true, "&idKanban="+idKanban+"&typeDynamic=editKanban", true, false);
}

function saveEditKanban(idKanban){
  var name="";
  if(typeof(dijit.byId("kanbanName"))!='undefined')name=dijit.byId("kanbanName").get("value");
  if(name.trim()!='') {
	showWait();
	dojo.xhrPost({
	  url : "../tool/kanbanEditName.php?idKanban="+idKanban,
	  form : "kanbanResultForm",
	  handleAs : "text",
	  load : function(data, args) {
	    formChangeInProgress=false;
	    loadContent("../view/kanbanView.php?idKanban="+idKanban, "divKanbanContainer");
	    dijit.byId('dialogKanbanUpdate').hide();
	    //hideWait();
	  },
	  error : function() {
	    hideWait();
	  }
	});
  }
}

function kanbanShowIdle(idKanban){
  showWait();
  dojo.xhrGet({
    url : "../tool/kanbanShowIdle.php?",
    handleAs : "text",
    load : function(data, args) {
      loadContent("../view/kanbanView.php?idKanban="+idKanban, "divKanbanContainer");
      //hideWait();
    },
    error : function() {
      hideWait();
    }
  });
}

//kanbanFullWidthElement
function kanbanFullWidthElement(){
  showWait();
  dojo.xhrGet({
	url : "../tool/kanbanFullWidthElement.php",
	handleAs : "text",
	load : function(data, args) {
	  loadContent("../view/kanbanView.php", "divKanbanContainer");
	},
	error : function() {
	  hideWait();
	}
  });
}

function changeWorkNbTicket(idColumn, idTicket, factor){
  if(dojo.byId('plannedWorkC'+idColumn)!=null){
    plannedTicket=parseFloat(dojo.byId('plannedWork'+idTicket).getAttribute("valueWork"))*factor;
    realTicket=parseFloat(dojo.byId('realWork'+idTicket).getAttribute("valueWork"))*factor;
    leftTicket=parseFloat(dojo.byId('leftWork'+idTicket).getAttribute("valueWork"))*factor;
    
    dojo.byId('plannedWorkC'+idColumn).setAttribute("valueWork",(parseFloat(dojo.byId('plannedWorkC'+idColumn).getAttribute("valueWork"))+plannedTicket));
    dojo.byId('plannedWorkC'+idColumn).innerHTML=workFormatter(dojo.byId('plannedWorkC'+idColumn).getAttribute("valueWork"));
    
    dojo.byId('realWorkC'+idColumn).setAttribute("valueWork",(parseFloat(dojo.byId('realWorkC'+idColumn).getAttribute("valueWork"))+realTicket));
    dojo.byId('realWorkC'+idColumn).innerHTML=workFormatter(dojo.byId('realWorkC'+idColumn).getAttribute("valueWork"));
    
    dojo.byId('leftWorkC'+idColumn).setAttribute("valueWork",(parseFloat(dojo.byId('leftWorkC'+idColumn).getAttribute("valueWork"))+leftTicket));
    dojo.byId('leftWorkC'+idColumn).innerHTML=workFormatter(dojo.byId('leftWorkC'+idColumn).getAttribute("valueWork"));
  }
  
  if(dojo.byId('plannedWorkC'+idColumn)!=null){
	
	realTicket=parseFloat(dojo.byId('realWork'+idTicket).getAttribute("valueWork"))*factor;
    leftTicket=parseFloat(dojo.byId('leftWork'+idTicket).getAttribute("valueWork"))*factor;
    
  }
  if(dojo.byId('numberTickets'+idColumn)!=null)dojo.byId('numberTickets'+idColumn).innerHTML=parseFloat(dojo.byId('numberTickets'+idColumn).innerHTML)+factor;
}

function kanbanStart(){
  if(dijit.byId('searchByName')==null){
    setTimeout(function(){kanbanStart();},20);
  }else{
    dojo.byId('kanbanContainer').scrollTop=kanbanScrollTop;
    kanbanSearchBy();
  }
}

function kanbanSaveDataSession(type,value,idSearch){
  // #2887
	saveDataToSession("kanban"+type, (idSearch==-1 ? value : idSearch));
  /*dojo.xhrPost({
    url : "../tool/saveDataToSession.php?idData=kanban"+type+"&value="
        + (idSearch==-1 ? value : idSearch),
    handleAs : "text",
    load : function(data, args) {
    }
  });*/
}

function kanbanSearchBy(){
  arrayVerify=[];
    if(dojo.byId('searchByName') != null){
      searchValue=dijit.byId('searchByName').get('value').replace(/[*]/g,".*");
      regex=new RegExp(kanbanEscapeRegExp(dijit.byId('searchByName').get('value')), 'i');
      arrayVerify.push({"regex":regex,"id":"name","val":dijit.byId('searchByName').get('value'),"idSearch":"-1"});
    }
    
    if(dojo.byId('searchByResponsible') != null){
      searchValue=dijit.byId('searchByResponsible').get('displayedValue').replace(/[*]/g,".*");
      regex=new RegExp(kanbanEscapeRegExp(dijit.byId('searchByResponsible').get('displayedValue')), 'i');
      arrayVerify.push({"regex":regex,"id":"responsible","val":dijit.byId('searchByResponsible').get('displayedValue'),"idSearch":dijit.byId('searchByResponsible').get('value')});
    }
    
    if(dojo.byId('listStatus') != null){
      searchValue=dijit.byId('listStatus').get('displayedValue').replace(/[*]/g,".*");
      regex=new RegExp(kanbanEscapeRegExp(dijit.byId('listStatus').get('displayedValue')), 'i');
      arrayVerify.push({"regex":regex,"id":"status","val":dijit.byId('listStatus').get('displayedValue'),"idSearch":dijit.byId('listStatus').get('value')});
    }
    
    if(dojo.byId('listTargetProductVersion') != null){
      searchValue=dijit.byId('listTargetProductVersion').get('displayedValue').replace(/[*]/g,".*");
      regex=new RegExp(kanbanEscapeRegExp(dijit.byId('listTargetProductVersion').get('displayedValue')), 'i');
      arrayVerify.push({"regex":regex,"id":"targetProductVersion","val":dijit.byId('listTargetProductVersion').get('displayedValue'),"idSearch":dijit.byId('listTargetProductVersion').get('value')});
    }
  
  for(var ite in arrayVerify)kanbanSaveDataSession(arrayVerify[ite]['id'],arrayVerify[ite]['val'],arrayVerify[ite]['idSearch']);
  listItem=dojo.query('.ticketKanBanColor');
  for(ite in listItem){
    if(listItem[ite]!=null && dojo.byId(listItem[ite].id)!=null){
      idTicket=listItem[ite].id.split('itemRow')[1].split('-')[0];
      idColumn=listItem[ite].getAttribute('fromC');
      controlePass=true;
      for(var ite2 in arrayVerify){
        if(arrayVerify[ite2]['val']!=''){
          var textToControl=null;
          type=arrayVerify[ite2]['id'];
          if(type!='responsible' && dojo.byId(type+idTicket)!=null){
            textToControl=dojo.byId(type+idTicket).innerHTML;
          }else if(dojo.byId(type+idTicket)!=null){
            textToControl=dojo.byId(type+idTicket).getAttribute('valueuser');
          }
          if(textToControl==null || textToControl.match(arrayVerify[ite2]['regex'])==null)controlePass=false;
        }
      }
      if(controlePass){
        changeWorkNbTicket(idColumn, idTicket, changeTicketVisible(listItem[ite].id,1));
      }else{
        changeWorkNbTicket(idColumn, idTicket, changeTicketVisible(listItem[ite].id,-1));
      }
    }
  }
}

function changeTicketVisible(idTicket,factorBase){
  oldVisible=dojo.style(dojo.byId(idTicket),'display');
  if(factorBase==1 && oldVisible =="none"){
    dojo.byId(idTicket).style.setProperty('display',"inline-table","important");
    return 1;
  }
  if(factorBase==-1 && oldVisible!="none"){
    dojo.byId(idTicket).style.setProperty('display',"none","important");
    return -1;
  }
  return 0;
}

function kanbanChangeOrderBy(val, idKanban){
  loadContent("../view/kanbanView.php?idKanban="+idKanban+"&kanbanOrderBy="+val, "divKanbanContainer");
}

function kanbanEscapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function divWidthKanban(idLine, typeKanban,numberColumn){
  var itemRow = dojo.byId("itemRow"+idLine+"-"+typeKanban);
  if(numberColumn >'2' && numberColumn <='4'){
	  itemRow.className = "dojoDndItem dojoDndHandle ticketKanBanStyleFull ticketKanBanColor ticketKanbanCustomThree";
  } else if(numberColumn=='2'){
	  itemRow.className = "dojoDndItem dojoDndHandle ticketKanBanStyleFull ticketKanBanColor ticketKanbanCustomTwo";
  } else if(numberColumn=='1'){
	  itemRow.className = "dojoDndItem dojoDndHandle ticketKanBanStyleFull ticketKanBanColor ticketKanbanCustom";
  }	else if (numberColumn >'4'){
	  itemRow.className = "dojoDndItem dojoDndHandle ticketKanBanStyleFull ticketKanBanColor ticketKanbanCustomMin";
  }
}

function kanbanShowDescr(field,type,id){
  if (dojo.byId("descr_"+id).value == "full"){
    return;
  }
  dojo.byId('descr_'+id).value = "full";
  url= '../tool/kanbanGetDescription.php?dataType=defaultPriority&Type=' +type + "&id=" +id + "&field=" + field+"&width="+dojo.byId('objectDescr'+id).offsetWidth ;
    dojo.xhrGet({
	url: url,
	handleAs : "text",
	//mehdi #2516
	  load : function(data) {
		dojo.byId('objectDescr'+id).innerHTML=data;	  
	  }
	});
}

function setDefaultPriority(typeValue) {
	  url='../tool/getSingleData.php?dataType=defaultPriority&idType='+typeValue+"&objectClass="+dojo.byId('objectClass').value;
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

function activityStreamKanban(objectId, objectClass,type){
	var param = "&objectId="+objectId+"&objectClass="+objectClass+"&type="+type;
	//loadDialog(dialogDiv, callBack, autoShow, params, clearOnHide, closable, dialogTitle)
	loadDialog('dialogKanbanGetObjectStream', null, true, param, true, true, 'titleStream');
}

var saveNoteStreamKanbanTimeout=null;
function saveNoteStreamKanban(event,line){
  var key = event.keyCode;
  var type=dojo.byId('kanbanRefType').value;
  var idKanban=dojo.byId('idKanban').value;
  var id= dojo.byId('noteRefId').value;
  var newStatut= '';
  if (key == 13 && !event.shiftKey) {
    var noteEditor = dijit.byId("noteStreamKanban");
    var noteEditorContent=noteEditor.get("value");
    if (noteEditorContent.trim()=="") {
      noteEditor.focus();
      return;
    }
    var callBack = function(){
      dojo.byId("resultKanbanStreamDiv").style.display="block";
      if (saveNoteStreamKanbanTimeout) clearTimeout(saveNoteStreamKanbanTimeout);
	    saveNoteStreamKanbanTimeout=setTimeout('dojo.byId("resultKanbanStreamDiv").style.display="none";',3000); 
	  };
	//loadContent(page, destination, formName, isResultMessage, validationType, directAccess, silent, callBackFunction, noFading)
	  loadContent("../tool/saveNoteStreamKanban.php", "activityStreamCenterKanban", "noteFormStreamKanban", false, null,null,null,callBack);
	  var nodeTicket=dojo.byId('itemRow'+id+'-'+type);
	  dojo.removeClass(dojo.byId('itemRow'+id+'-'+type),'dojoDndHandle');
	  var oldColor=dojo.style(nodeTicket, "background-color");
	  dojo.style(nodeTicket, "background-color", '#999');
	  dojo.xhrGet({
	    url : "../tool/kanbanUpdate.php?idTicket="+id+"&type="+type+"&newStatut="+newStatut+"&idKanban="+idKanban,
	    load : function(data) {
	        var callback=function() {
	          nodeTicket=dojo.byId('itemRow'+id+'-'+type);
	          dojo.addClass(nodeTicket,'dojoDndItemAnchor');
	          dojo.style(nodeTicket, "background-color", oldColor);
	          dojo.addClass(nodeTicket,'dojoDndHandle');
	          dojo.byId('itemRow'+id+'-'+type).setAttribute('fromC',newStatut);
	          hideWait();
	        };
	        loadDiv('../tool/kanbanRefreshTicket.php?id='+id+'&type='+type+'&idKanban='+idKanban+'&from='+newStatut,'itemRow'+id+'-'+type,null,callback);
	    },
	  });
	  noteEditor.set("value",null);
	  event.preventDefault();
  } 
}

function kanbanRefreshListType(listType, destination, param ) { // , paramVal, selected, required
  var urlList='../tool/kanbanJsonList.php?listType=' + listType;
  urlList+='&critField=' + param;
  var datastore=new dojo.data.ItemFileReadStore({
    url : urlList
  });
  var store=new dojo.store.DataStore({
    store : datastore
  });

  var mySelect=dijit.byId('kanbanTypeList');

  mySelect.set({labelAttr: 'name', store: store, sortByLabel: false});
  store.query({
    id : "*"
  });
  
}
