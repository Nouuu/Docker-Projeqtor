<?php 
require_once "../tool/projeqtor.php";
$categ=null;
if (isset($_REQUEST['idCategory'])) {
  $categ=$_REQUEST['idCategory'];
}
$hr=new HabilitationReport();
$user=getSessionUser();
$allowedReport=array();
$allowedCategory=array();
$lst=$hr->getSqlElementsFromCriteria(array('idProfile'=>$user->idProfile, 'allowAccess'=>'1'), false);
foreach ($lst as $h) {
  $report=$h->idReport;
  $nameReport=SqlList::getNameFromId('Report', $report, false);
  if (! Module::isReportActive($nameReport)) continue;
  $allowedReport[$report]=$report;
  $category=SqlList::getFieldFromId('Report', $report, 'idReportCategory',false);
  $allowedCategory[$category]=$category;
}

if (!$categ) {
  echo "<div class='messageData headerReport' style= ''>";
  echo ucfirst(i18n('colCategory'));
  echo "</div>";
  $listCateg=SqlList::getList('ReportCategory');
  echo "<ul class='bmenu'>";
  foreach ($listCateg as $id=>$name) {
    if (isset($allowedCategory[$id])) {
      echo "<li class='section' onClick='loadDiv(\"../view/reportListMenu.php?idCategory=$id\",\"reportMenuList\");'><div class='bmenuCategText'>$name</div></li>";
    }
  }
  echo "</ul>";
} else {
  $catObj=new ReportCategory($categ);
  echo "<div class='messageData headerReport' style= ''>";
  echo i18n($catObj->name);
  echo "</div>";
  echo "<div class='arrowBack' style='position:absolute;top:5px;left:25px;'>";
  echo "<span class='dijitInline dijitButtonNode backButton noRotate'  onClick='loadDiv(\"../view/reportListMenu.php\",\"reportMenuList\")' style='border:unset;'>";
  if(isNewGui()){
    echo formatNewGuiButton('Back', 22);
  }else{
    echo formatBigButton('Back');
  }
  echo "</div>";
  echo '</span>';
  
  $report=new Report();
  $crit=array('idReportCategory'=>$categ);
  $listReport=$report->getSqlElementsFromCriteria($crit, false, null, 'sortOrder asc');
  echo "<ul class='bmenu report' style=''>";
  foreach ($listReport as $rpt) {
    if($rpt->id=="108" and Parameter::getGlobalParameter("technicalProgress")!="YES")continue;
    if (isset($allowedReport[$rpt->id])) {
      echo "<li class='section' id='report$rpt->id' onClick='reportSelectReport($rpt->id);'><div class='bmenuText'>".ucfirst(i18n($rpt->name))."</div></li>";   
    }
  }
  echo "</ul>";
}  
  
?>