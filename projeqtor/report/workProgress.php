<?php
include_once '../tool/projeqtor.php';
$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
  $paramProject = Security::checkValidId($paramProject); // only allow digits
}
$headerParameters="";
if ($paramProject) {
  $headerParameters.= i18n("Project") . ' : ' . SqlList::getNameFromId('Project',$paramProject) . '<br/>';
}
include "header.php";

$user=new User();
$w=new Work();
$table=$w->getDatabaseTableName();
$inProj=getVisibleProjectsList(true, $paramProject);
echo $inProj;
$query="select day, sum(work) as wrk from $table where idProject in $inProj group by day";
$result=Sql::query($query);
$totalWork=0;
$result=array();
while ($line = Sql::fetchLine($result)) {
  $work=$line['wrk'];
  $day=$line['day'];
  $totalWork+=$work;
  //echo "$day => $work =>$totalWork <br/> ";
  $result[$day]=$totalWork;
}

if (! testGraphEnabled()) { return;}

/*include("../external/pChart2/class/pData.class.php");
include("../external/pChart2/class/pDraw.class.php");
include("../external/pChart2/class/pImage.class.php");
$myData = new pData();
$myData->addPoints(1,5, 6,7 );
$myPicture = new pImage(700,230,$myData);
$myPicture->setGraphArea(60,40,670,190);
$myPicture->setFontProperties(array("FontName"=>"fonts/Forgotte.ttf","FontSize"=>11));
$myPicture->drawSplineChart();
$myPicture->Stroke();
*/

/* Include all the classes */
include("../external/pChart2/class/pDraw.class.php");
include("../external/pChart2/class/pImage.class.php");
include("../external/pChart2/class/pData.class.php");

/* Create your dataset object */
$myData = new pData();

/* Add data in your dataset */
$myData->addPoints(array(VOID,3,4,3,5));

/* Create a pChart object and associate your dataset */
$myPicture = new pImage(700,230,$myData);

/* Choose a nice font */
$myPicture->setFontProperties(array("FontName"=>"../external/pChart2/fonts/Forgotte.ttf","FontSize"=>11));

/* Define the boundaries of the graph area */
$myPicture->setGraphArea(60,40,670,190);

/* Draw the scale, keep everything automatic */
$myPicture->drawScale();

/* Draw the scale, keep everything automatic */
$myPicture->drawSplineChart();

/* Build the PNG file and send it to the web browser */
$myPicture->Render("basic.png");
