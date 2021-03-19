<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : BRW
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

/** ===========================================================================
 *
 */
function writeFile($msg,$file) {
	if (function_exists('error_log')) {
		return error_log( $msg,3, $file);
	} else {
		$handle=fopen($file,"a");
		if (! $handle) return false;
		if (! fwrite($handle,$msg)) return false;
		if (! fclose($handle))  return false;
		return true;
	}
}

/**
 * Delete a file
 * @param $file
 * @throws Exception
 */
function kill($file) {
	if (file_exists($file)) {
		return unlink($file);
	}
	return false;
}

/**
 * Purge some files
 * @param string $dir directory
 * @param string $pattern pattern for file selection
 */
function purgeFiles($dir, $pattern, $removeDirs=false) {
	if (! is_dir($dir)) {
		traceLog ("purgeFiles('$dir', '$pattern') - directory '$dir' does not exist");
		return;
	}
	$handle = opendir($dir);
	if (! is_resource($handle)) {
		traceLog ("purgeFiles('$dir', '$pattern') - Unable to open directory '$dir' ");
		return;
	}
	while (($file = readdir($handle)) !== false) {
		if ($file == '.' || $file == '..' || $file=='.svn') {
			continue;
		}
		$filepath = $dir == '.' ? $file : $dir . '/' . $file;
		if (is_link($filepath)) {
			continue;
		}
		if (is_file($filepath)) {
			if (!$pattern or substr($file,0,strlen($pattern))==$pattern) {
				unlink($filepath);
			}
		} else if (is_dir($filepath)) {
			purgeFiles($filepath, $pattern);
		}
	}
	if ($removeDirs) {
	  rmdir($dir);
	}
	closedir($handle);
}

/**
 * Create of thumb image of given size, with same name suffixed with "_thumb$size" or with name definied in $thumb
 * @param string $image source image to generate thumb from
 * @param integer $size size of thumb
 * @param string $thumb name of target thumb (to avoid defaut naming)
 * $param boolean $square if thumb target to be rendered as a square (crop borders)
 */
function createThumb($imageFile,$size,$thumb=null, $square=false) {
  $paramFilenameCharset=Parameter::getGlobalParameter('filenameCharset');
  if ($paramFilenameCharset) {
    $thumb = iconv("UTF-8", $paramFilenameCharset.'//TRANSLIT//IGNORE',$thumb);
  }
  if (!$size) {
    copy($imageFile, $thumb);
    return;
  }
  $paramFilenameCharset=Parameter::getGlobalParameter('filenameCharset');
  if ($paramFilenameCharset) {
    $imageFile = iconv("UTF-8", $paramFilenameCharset.'//TRANSLIT//IGNORE',$imageFile);
  }
  if (!$imageFile or ! is_file($imageFile)) {
    return false;
  }
  $ext=strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
  $imgFmt="";
  // EDIT START BRW 2013-07-31
  $bSupported = true;
  if(1){
    $arr = getimagesize($imageFile);
    switch ($arr['mime']) {
    case 'image/tiff':
      $bSupported = false; break;    
    case 'image/jpeg':    case 'image/jpg':
      $imgFmt='jpeg'; break;   
    case 'image/gif': 
      $imgFmt='gif'; $blending = true; break;
    case 'image/png':
      $imgFmt='png'; $blending = false; break;
    default:
      $bSupported = false; break;  
    }
  }
  else{
    switch ($ext) {
    case 'jpg': case 'jpeg':
      $imgFmt='jpeg'; break;   
    case 'gif': 
      $imgFmt='gif'; $blending = true; break;
    case 'png':
      $imgFmt='png'; $blending = false; break;    
    }
  }
  if($bSupported){
    $imagecreate = "imagecreatefrom$imgFmt"; 
    $imagesave = "image$imgFmt";
 
    $img=$imagecreate($imageFile);
    $x = imagesx($img);
    $y = imagesy($img);
    $px=0; $py=0;
    if ($square) {
    	$nx = $size;
    	$ny = $size;
    	if($x>$y) {
    		$px=round(($x-$y)/2,0);
    		$x=$y;
    	} else if ($x<$y) {
    		$py=round(($y-$y)/2,0);
    		$y=$x;
    	}
    } else if($x>$size or $y>$size) {
      if($x>$y) {
        $nx = $size;
        $ny = floor($y/($x/$size));
      } else {
        $nx = floor($x/($y/$size));
        $ny = $size;
      }
    } else {
      $nx=$x;
      $ny=$y;
    }
    if ($nx<1) $nx=1;
    if ($ny<1) $ny=1;
    $nimg = imagecreatetruecolor($nx,$ny);
    // preserve transparency for PNG and GIF images 
    if ($imgFmt == 'png' or $imgFmt == 'gif'){ 
      $background = imagecolorallocate($nimg, 0, 0, 0); 
      imagecolortransparent($nimg, $background); 
      imagealphablending($nimg, $blending); 
      imagesavealpha($nimg, true); 
    } 
    imagecopyresampled($nimg,$img,0,0,$px,$py,$nx,$ny,$x,$y);
    if (! $thumb) {
      $thumb=getThumbFileName($imageFile,$size);
    }
    enableCatchErrors();
    $dir=pathinfo($thumb, PATHINFO_DIRNAME);
    if (! file_exists($dir)) {
      mkdir($dir,0777,true);
    }
    try {
			$res=@$imagesave($nimg,$thumb);
		} catch (Exception $e) {
      errorLog("create thumb error : " . $e->getMessage() );
      $res=false;
    }
    if (! $res) {
      errorLog("Cannot write thumb file : '$thumb' (check write access to folder)" );
    }
    disableCatchErrors();
    return true;
  }
  return false;
  // EDIT END BRW 2013-07-31
}

function getThumbFileName($imageFile,$size) {
	$thumbLocation='../files/thumbs';
	$attLoc=Parameter::getGlobalParameter('paramAttachmentDirectory');
	$docLoc=Parameter::getGlobalParameter('documentRoot');
	$root='../files';
	if (substr($imageFile,0,strlen($attLoc))==$attLoc) {
		$root=$attLoc;
	} else if (substr($imageFile,0,strlen($attLoc))==$docLoc)  {
		$root=$docLoc;
	}
	$imageFile=str_replace($root, '../files/thumbs', $imageFile);
	$imageFile=str_replace('\\', '/', $imageFile); 
	if (!$size) return $imageFile;
	$ext=strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
	return substr($imageFile,0,strlen($imageFile)-(strlen($ext)+1)).'_thumb'.$size.'.'.$ext;
}

function getImageThumb($imageFile,$size) {
	$thumb=getThumbFileName($imageFile,$size);
	if (! file_exists($thumb)) {
		if (! createThumb($imageFile,$size,$thumb)) {
			errorLog("Cannot create image thumb of size $size for $imageFile");
			return "";
		}
	}
	return $thumb;
}

function isThumbable($fileName) {
  $ext=strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
  if ($ext=='jpg' or $ext=='jpeg' or $ext=='gif' or $ext=='png') {
    return true;	    
  } else {
  	return false;
  }
}