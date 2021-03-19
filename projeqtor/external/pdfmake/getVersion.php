<?php
function pdfmakeGetVersion() {
	$file=file_get_contents(__DIR__."/pdfmake.js");
	$deb=strpos($file,'pdfmake');
	$fin=strpos($file,",",$deb+1);
	$msg=substr($file,$deb,$fin-$deb);
	$split=explode(' ',$msg);
	$version=$split[1];
	$version=trim($version);
	$version=trim($version,'v');
	return $version;
}