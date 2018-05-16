// Referenced & Modified Team code for playback of music library on web

$FORMAT_M3U = "m3u";

$scriptUrl =  strtolower(strtok($_SERVER['SERVER_PROTOCOL'], '/')).'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$musicRootUrl = strtolower(strtok($_SERVER['SERVER_PROTOCOL'], '/')).'://'.$_SERVER['HTTP_HOST'].$musicRoot;


// ************************* 

$path = isset($_GET['p']) ? $_GET["p"] : "";
$format = isset($_GET['f']) ? $FORMAT_M3U : "";
if ($FORMAT_M3U !== $format) {
  global $scriptUrl, $title;
  $m3uUrl = $scriptUrl.'?f=m3u&amp;p='.rawurlencode($path);
  require_once("./header.php");
}
writePlaylist($path, $format);
if ($FORMAT_M3U !== $format) {
  require_once("./footer.php");
}

// *************************


function writePlaylist($path, $format) {
  global $FORMAT_M3U;
  global $extensions, $musicRoot, $musicRootUrl, $scriptUrl;
  $dirName = substr($path, strrpos("/".$path, "/"));
  $realPath = realpath($path).'/';
  
  $pathEnc = str_replace("%2F", "/", rawurlencode($path));
  
  if ($format === $FORMAT_M3U) {
    // header('Content-Type: text/plain'); // For testing
    header('Content-Type: audio/x-mpegurl');
    header('Content-Disposition: inline; filename="'.$dirName.'.m3u"');
  }
 
  $files = array();
  $dirHandle = @opendir($realPath);

  if ($path !== "") {
    $dirs["[Up]"] = $scriptUrl.'?p='. substr($pathEnc, 0, strrpos($pathEnc, "/"));
  }
  while (false !== ($file = @readdir($dirHandle))) {
    if (substr($file, 0, 1) != ".") { // Ignore ".", "..", ".any_hidden_files"
      if (is_dir($realPath.$file)) {
        if ($FORMAT_M3U === $format) {
          addFilesRecursively(&$files, $path.'/'.$file);
        } else {
          $dirs[$file] = $scriptUrl.'?p='.rawurlencode(
            (($path === "") ? $path : $path.'/')  // Insert a "/" if $path is not blank
            .$file);
        }
      } else if (isExtensionOK($file)) {
        $files[$file] = $musicRootUrl
          .(($pathEnc === "") ? "" : "/") // Insert a "/" if $pathEnc is not blank
          .$pathEnc
          .'/'.rawurlencode($file);
      }
    }
  }
  @closedir($dirHandle);
  if ($FORMAT_M3U === $format && isset($files)) {
    
    foreach ($files as $display => $url) {
      echo $url."\n";
    }
  } else {
  
    if (isset($dirs)) {
      natcasesort($dirs);
      foreach ($dirs as $display => $url) {
        writeDirectory($display, $url);
      }
    }
    if (isset($files)) {
      natcasesort($files);
      foreach ($files as $display => $url) {
        writeFile($display, $url);
      }
    }
  }
}

function addFilesRecursively(&$arr, $subDir) {
  global $extensions, $musicRoot, $musicRootUrl, $scriptRoot;
  $realSubDirPath = realpath($musicRoot.'/'.$subDir);
  $subDirPathEnc = str_replace("%2F", "/", rawurlencode($subDir));
  $subDirHandle = @opendir($realSubDirPath);
  while (false !== ($file = @readdir($subDirHandle))) {
    if (substr($file, 0, 1) != ".") { // Ignore ".", "..", ".any_hidden_files"
      if (is_dir($realSubDirPath.'/'.$file)) {
        addFilesRecursively($arr, $subDir.'/'.$file);
      } else if (isExtensionOK($file)) {
        $arr[$file] = $musicRootUrl
        .(($subDirPathEnc === "") ? "" : "/") // Insert a "/" if $subDirPathEnc is not blank
        .$subDirPathEnc
        .'/'.rawurlencode($file);
      }
    }
  }
  @closedir($subDirHandle);
}

function isExtensionOK($file) {
  global $extensions;
  $ext = strrchr($file, ".");
  if ($ext !== '' && false !== strpos($extensions, $ext)) {
    return true;
  }
}
?>

