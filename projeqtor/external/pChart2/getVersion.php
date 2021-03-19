<?php
function pChartGetVersion() {
	$file=file_get_contents(__DIR__."/class/pImage.class.php");
	$deb=strpos($file,'Version');
	$fin=strpos($file,"\n",$deb+1);
	$msg=substr($file,$deb,$fin-$deb);
	$split=explode(':',$msg);
	$version=$split[1];
	$version=trim($version);
	return $version;
}