<?php
/*
 * @author: qCazelles 
 */
require_once "../tool/projeqtor.php";
require_once "../tool/jsonFunctions.php";
scriptLog('   ->/tool/jsonContractGantt.php');
SqlElement::$_cachedQuery['SupllierContract']=array();

$objectClass=(RequestHandler::isCodeSet('objectClass'))?RequestHandler::getValue('objectClass'):'';
$displayResource=Parameter::getUserParameter('displayResourcePlan');
$lstContract= array();
$nbRows=0;
$obj=new $objectClass();
$test= new SupplierContract();
$showClosedContract = Parameter::getUserParameter('contractShowClosed');
$where="idle=0";
if($showClosedContract=='1'){
  $where="id=id";
}
$lstContract=$obj->getSqlElementsFromCriteria(null,null,$where);
echo '{"identifier":"id",' ;
echo ' "items":[';
    drawElementContractGantt($objectClass,$lstContract,$nbRows,$displayResource);
echo ' ] }';






function drawElementContractGantt($objectClass,$lstContract,$nbRows,$displayResource){
    $nbContract=count($lstContract);
    foreach ($lstContract as $contract) {
      $namePC='-';
      $display='';
      $mile= array();
      $redLine=false;
      $unity='';
      echo (++$nbRows>1)?',':'';
      $idContract=$contract->id.'.'.$nbRows;
      $class=get_class($contract);
      $nameType='id'.$objectClass.'Type';
      $type=new Type($contract->$nameType);
      if($class=='SupplierContract'){
        if($contract->idProvider){
          $provider=new Provider($contract->idProvider);
          $namePC=$provider->name;
          $val=i18n('colIdProviderContract');
        }
      }else{
        if($contract->idClient){
          $client=new Client($contract->idClient);
          $namePC=$client->name;
          $val=i18n('colIdClient');
        }
      }
      if(strtotime($contract->deadlineDate) > strtotime($contract->endDate) or strtotime($contract->endDate) < time() ){
        $redLine=true;       
      }
      if($contract->idResource){
        $resource=new Resource($contract->idResource);
       $display=($displayResource!='NO')?$resource->$displayResource:null;
        if ($displayResource=='initials' and (!$display or $display=='')) {
          $words=mb_split(' ',str_replace(array('"',"'"), ' ',$resource->name));
          foreach ($words as $word) {
            $display.=(mb_substr($word,0,1,'UTF-8'));
          }
          
        }
      }
      if(isset($contract->initialContractTerm) and isset($contract->idUnitContract)){
        $unit= new UnitContract($contract->idUnitContract);
        $w=mb_split(' ',str_replace(array('"',"'"), ' ',$unit->name));
        foreach ($w as $letter) {
          $unity.=(mb_substr($letter,0,1,'UTF-8'));
        }
      }
      if($contract->deadlineDate or $contract->noticeDate)$class=$class.'hasChild';
      
      echo  '{';
        echo '"id":"'.$contract->id.'"';
        echo ',"refid":"'.$contract->id.'"';
        echo ',"refname":"'.htmlEncode(htmlEncodeJson($contract->name)).'"';
        echo ',"reftype":"'.$class.'"';
        echo ',"objecttype":"'.htmlEncode(htmlEncodeJson($type->name)).'"';
        echo ',"resource":"'.htmlEncode(htmlEncodeJson($display)).'"';
        echo ',"externalressource":"'.htmlEncode(htmlEncodeJson($namePC)).'"';
        echo ',"realstartdate":"'.($contract->startDate).'"';
        echo ',"realenddate":"'.($contract->endDate).'"';
        echo ',"duration":"'.($contract->initialContractTerm).' '.($unity).'"';
        echo ',"status":"'.htmlEncodeJson(SqlList::getNameFromId('Status', $contract->idStatus)).'"';
        echo ',"collapsed":"0"';
        if ($contract->handled and $redLine == false ) {
          echo ',"redElement":"0"';
        }else if ($redLine==true and !$contract->idle and !$contract->done) {
          echo ',"redElement":"1"';
        }
        else {
          echo ',"redElement":"0"';
        }
      echo '  }';
      
      if($contract->deadlineDate or $contract->noticeDate){
        if($contract->deadlineDate !='' )$mile[]='DeadlineDate';
        if($contract->noticeDate!='')$mile[]='NoticeDate';
        $compt=1;
        foreach($mile as $id=>$name){
          $compt++;
          echo ',';
          echo '{';
          echo '"id":"'.$idContract.'.'.$compt.'"';
          echo ',"refid":"'.$contract->id.'"';
          echo ',"reftype":"Milestone"';
          echo ',"reftypeparent":"'.$class.'"';
          echo ',"topid":"'.$contract->id.'"';
          if($name=='NoticeDate'){
            echo ',"realstartdate":"'.($contract->noticeDate).'"';
            echo ',"refname":"'.i18n('col'.$name).'_'.htmlEncode(htmlEncodeJson($contract->name)).'"';
          }else if ($name=='DeadlineDate'){
            echo ',"realstartdate":"'.($contract->deadlineDate).'"';
            echo ',"refname":"'.i18n('col'.$name).'_'.htmlEncode(htmlEncodeJson($contract->name)).'"';
          }
          echo '  }';
        }
      }
    }
}
