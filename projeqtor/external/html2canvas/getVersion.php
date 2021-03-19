<?php
function html2canvasGetVersion() {
	$file=file_get_contents(__DIR__."/html2canvas.js");
	$deb=strpos($file,'html2canvas');
	$fin=strpos($file,"<http",$deb+1);
	$msg=substr($file,$deb,$fin-$deb);
	$split=explode(' ',$msg);
	$version=$split[1];
	$version=trim($version);
	return $version;
}