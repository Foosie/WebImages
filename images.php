<?php

/*
<<<<<<< HEAD
 * images43.php
=======
 * images.php
>>>>>>> be8251339a63aa74442ab3fce4c67c397242a9b2
 *
 * Copyright (c) 2021 Don Mankin (Foose, Fooser, Foosie)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Visit https://opensource.org/licenses/MIT
*/
 
// start session before we do anything else
session_start();

///////////  functions ///////////////

function isMobile() {
    // stubbing this function - display port works better
    return FALSE;
    switch (getDeviceType()) {
        case "iphone":
        case "android":
        case "blackberry":
            return TRUE;
        break;
        default:
            return FALSE;
    }
}

function getDeviceType() {
	$device = "unknown";
	if (stristr($_SERVER['HTTP_USER_AGENT'],'ipad'))
		$device = "ipad";
	else if (stristr($_SERVER['HTTP_USER_AGENT'],'iphone') || strstr($_SERVER['HTTP_USER_AGENT'],'iphone')) 
		$device = "iphone";
	else if (stristr($_SERVER['HTTP_USER_AGENT'],'blackberry'))
		$device = "blackberry";
	else if (stristr($_SERVER['HTTP_USER_AGENT'],'android'))
		$device = "android";
    else if (stristr($_SERVER['HTTP_USER_AGENT'],'webOS'))
		$device = "webOS";
    return $device;
}

function getFileList($dir, $recurse = FALSE)
{
    // clear php file cache
    clearstatcache();
    
    // set up our supported image types
    $imagetypes = ['image/jpeg','image/x-ms-bmp','image/png','image/gif','video/mp4','video/quicktime']; // tif's unsupported by browsers
    
    $retval = [];
    // add trailing slash if missing
    if(substr($dir, -1) != "/") {
        $dir .= "/";
    }
    
    // open pointer to directory and read list of files
    $d = @dir($dir) or die("getFileList: Failed opening directory {$dir} for reading");
    while(FALSE !== ($entry = $d->read())) {
        
        // skip hidden files
        if($entry[0] == ".") continue;
        if(is_dir("{$dir}{$entry}")) {
            if (trim(basename(strtolower("{$dir}{$entry}")) != "thm")) {
                $retval[] = [
                    'file' => "",
                    'folder' => "{$dir}{$entry}",
                    'size' => 0
                ];
                if($recurse && is_readable("{$dir}{$entry}/")) {
                    $retval = array_merge($retval, getFileList("{$dir}{$entry}/", TRUE));
                }
            };
 
        } elseif(is_readable("{$dir}{$entry}")) {          
            // check for image files
            $f = "{$dir}{$entry}";
            $mimetype = mime_content_type($f);
            foreach($imagetypes as $valid_type) {
                if(preg_match("@^{$valid_type}@", $mimetype)) {
                    $retval[] = [
                        'file' => "{$dir}{$entry}",
                        'folder' => "",
                        'size' => getimagesize("{$dir}{$entry}")
                    ];
                    break;
                }
            }
        }
    }
    $d->close();
    return $retval;
}

function displayFileList($images,$server_root,$http_base,$pic_formats,$vid_formats,$prev_page, $CheckPW) {
    
    // convert the array to a string
    $images_string = serialize($images);
        
    // change double quotes to single for string passing 
    $images_string = str_replace("\"", "'", $images_string); ?>
    
    <center>
    <table>
        <tr>        
            <td>           
                <form method="post" id="previous_form" action="">
                <input type="hidden" name="image_array" value="<?php echo $images_string;?>"> 
                <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
                <input type="hidden" name="image_previous" value="<?php echo $image_current;?>">
                <input type="submit" value="[Previous]">
                </form>
            </td>
            <td>
                <form method="post" id="next_form" action="">
                <input type="hidden" name="image_array" value="<?php echo $images_string;?>">
                <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
                <input type="hidden" name="image_next" value="<?php echo $image_current;?>">
                <input type="submit" value="[Next]">
                </form>
            </td>
            <td>
                <form>
                <a href="<?php echo $prev_page;?>">&nbsp;<b>[Go Back]</b></a>
                </form>
            </td> <?php
            if (isset($_SESSION['foose_menu_root'])) { ?>
                <td>
                    <form>
                    <a href="<?php echo $_SESSION['foose_menu_root']; ?>">&nbsp;<b>[Menu]</b></a>
                    </form>
                </td> <?php
            }
            if ($CheckPW == TRUE) { ?>  
                <td>
                    <form method="post" id="logoff_form" action="">
                    <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
                    <input type="submit" name="page_logout" value="[Logoff]">
                    </form>
                </td> <?php
            } ?>
        </tr>
    </table>  
    </center> <?php   
    
    echo "<div id='images'>";
    echo "<ul>";
    $idx = 0;
    foreach($images as $img) {
        if (!empty($img['file'])) {           
            $path_parts = pathinfo($img['file']);
            $extension = strtolower($path_parts['extension']);
            $url = $http_base . str_replace($server_root,"",str_replace("\\","/",$path_parts['dirname'])) . "/" . basename($img['file']);
            $pathspec = $path_parts['dirname'] . "/" .basename($img['file']);
            $thm_pathspec = $path_parts['dirname'] . "/thm/THM_" .basename($img['file']);
            $thm_url = $http_base . str_replace($server_root,"",str_replace("\\","/",$path_parts['dirname'])) . "/thm/THM_" . basename($img['file']);
            if (in_array(strtolower($extension), $pic_formats)){
                echo "<li class=\"projbox\">";
                if (file_exists($thm_pathspec)) {
                    list($width, $height, $type, $attr) = getimagesize($thm_pathspec);
                    if ($height > $width){ echo "<img src='".$thm_url."' height='120'>";}
                    else { echo "<img src='".$thm_url."' width='160'>";}
                } else {
                    list($width, $height, $type, $attr) = getimagesize($pathspec);
                    if ($height > $width){ echo "<img src='".$url."' height='120'>";}
                    else { echo "<img src='".$url."' width='160'>";}
                }
                ?>
                <center>
                <form method="post" id="start_form" action="">
                <input type="hidden" name="image_array" value="<?php echo $images_string;?>">
                <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
                <input type="hidden" name="image_next" value="<?php echo $image_current;?>">
                <input type="hidden" name="image_previous" value="<?php echo $image_current;?>">
                <input type="hidden" name="image_start" value="<?php echo $idx;?>">
                <input type="submit" value="[View]">
                </form>
                </center> <?php               
                
                echo "</li>";
                echo "&nbsp;&nbsp;";
            }
            else if (in_array(strtolower($extension), $vid_formats)){
                echo "<li class=\"video\">";
                $fn = pathinfo($url, PATHINFO_FILENAME);
                switch (getDeviceType()) {
                    case "iphone":
                    case "ipad":
                        $fs="18px"; ?>
                        <div style="font-size:<?php echo $fs;?>"> <?php echo $fn;?></div>
                        <video autoplay muted width="160" height="120" src="<?php echo $url;?>"></video> <?php
                    break;
                    case "android":
                        $fs="18px"; ?>
                        <div style="font-size:<?php echo $fs;?>"> <?php echo $fn;?></div>
                        <video controls width="160" height="120" src="<?php echo $url;?>"></video> <?php
                    break;
                    default:
                        $fs="9px"; ?>
                        <div style="font-size:<?php echo $fs;?>"> <?php echo $fn;?></div>
                        <video controls width="160" height="120" src="<?php echo $url;?>"></video> <?php
                } ?>
    
                <form method="post" id="start_form" action="">
                <input type="hidden" name="image_array" value="<?php echo $images_string;?>">
                <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
                <input type="hidden" name="image_next" value="<?php echo $image_current;?>">
                <input type="hidden" name="image_previous" value="<?php echo $image_current;?>">
                <input type="hidden" name="image_start" value="<?php echo $idx;?>">
                <input type="submit" value="[Play]">
                </form> <?php
                echo "</li>";
                echo "&nbsp;&nbsp;";
            }
        }
        ++$idx;
    }
    echo "</ul>";
    echo "</div>";
    echo "<br><br>";
}

function displayFile($images,$image_current,$server_root,$http_base,$pic_formats,$vid_formats,$prev_page, $CheckPW) {
    //  check bounds
    if (($image_current < 0) || $image_current > count($images))
        return;
    
    // convert the array to a string
    $images_string = serialize($images);
        
    // change double quotes to single for string passing 
    $images_string = str_replace("\"", "'", $images_string);
    
    ?>
    
    <center>

    <table>
        <tr>
            <td>           
                <form method="post" id="previous_form" action="">
                <input type="hidden" name="image_array" value="<?php echo $images_string;?>">
                <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
                <input type="hidden" name="image_previous" value="<?php echo $image_current;?>">
                <input type="submit" value="[Previous]">
                </form>
            </td>
             <td>
                <form method="post" id="next_form" action="">
                <input type="hidden" name="image_array" value="<?php echo $images_string;?>">
                <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
                <input type="hidden" name="image_next" value="<?php echo $image_current;?>">
                <input type="submit" value="[Next]">
                </form>
            </td>
            <td>
                <form>
                <a href="<?php echo $prev_page;?>">&nbsp;<b>[Go Back]</b></a>
                </form>
            </td> <?php
            if (isset($_SESSION['foose_menu_root'])) { ?>
                <td>
                    <form>
                    <a href="<?php echo $_SESSION['foose_menu_root']; ?>">&nbsp;<b>[Menu]</b></a>
                    </form>
                </td> <?php
            }        
            if ($CheckPW == TRUE) { ?>  
                <td>
                    <form method="post" id="logoff_form" action="">
                    <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
                    <input type="submit" name="page_logout" value="[Logoff]">
                    </form>
                </td> <?php
            } ?>
        </tr>
    </table>

    <div class="fsdiv"> <?php
    
    // display on page
    $img = $images[$image_current];
    
    if (!empty($img['file'])) {           
        $path_parts = pathinfo($img['file']);
        $extension = strtolower($path_parts['extension']);
        $url = $http_base . str_replace($server_root,"",str_replace("\\","/",$path_parts['dirname'])) . "/" . basename($img['file']);
        $pathspec = $path_parts['dirname'] . "/" .basename($img['file']);
        if (in_array(strtolower($extension), $pic_formats))
            echo "<a href='".$url."' target='_blank'><img class='fsimg' src='".$url."'></a>";
        else if (in_array(strtolower($extension), $vid_formats)){       
            $fn = pathinfo($url, PATHINFO_FILENAME); 
            switch (getDeviceType()) {
                case "iphone":
                case "ipad":
                    $fs="22px"; ?>
                    <div style="font-size:<?php echo $fs;?>"> <?php echo $fn;?></div>
                    <video controls autoplay muted width="90%" height="90%" src="<?php echo $url;?>"></video><?php
                break;
                case "android":
                    $fs="22px"; ?>
                    <div style="font-size:<?php echo $fs;?>"> <?php echo $fn;?></div>
                    <video controls width="90%" height="90%" src="<?php echo $url;?>"></video><?php
                break;
                default:
                    $fs="12px"; ?>
                    <div style="font-size:<?php echo $fs;?>"> <?php echo $fn;?></div>
                    <video controls width="90%" height="90%" src="<?php echo $url;?>"></video><?php
            } 
        }
    } ?>

    </div> <!-- class="fsdiv"-->
    </center> <?php
}

function createThumbnailsFromFileList($images,$server_root,$http_base,$pic_formats) {
    foreach($images as $img) {
        if (!empty($img['file'])) {           
            $path_parts = pathinfo($img['file']);
            $extension = strtolower($path_parts['extension']);
            if (in_array(strtolower($extension), $pic_formats)){
                $oldfolder = str_replace("\\","/",$path_parts['dirname']) . "/";
                $newfolder = $oldfolder . "thm/" ;
                $oldfilespec = $oldfolder . basename($img['file']);
                $newfilespec = $newfolder . "THM_" . basename($img['file']);
                if (!file_exists($newfolder)) {
                    mkdir($newfolder);
                }
                if (!file_exists($newfilespec)) {
                    createThumbnails($oldfilespec, $newfilespec, 120);
                }
            }
        }
    }
}

function CreateThumbnails($sourceImagePath, $destImagePath, $thumbWidth=120) {  

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $sourceImagePath);
    if ($mime == 'image/jpeg') {                   // fix EXIF orentation if it exists
        $exif = @exif_read_data($sourceImagePath); // @suppress ilegal IFD size warnings
        if(isset($exif['Orientation'])) {
            $orientation = $exif['Orientation'];
        }
        if(isset($orientation) && $orientation) {
            $sourceImage = @imagecreatefromjpeg($sourceImagePath);
            if (!$sourceImage)
                $sourceImage= imagecreatefromstring(file_get_contents($sourceImagePath));
            switch($orientation) {
                case 3:
                    $newimage = imagerotate($sourceImage, 180, 0);
                    break;
                case 6:
                    $newimage = imagerotate($sourceImage, -90, 0);
                    break;
                case 8:
                    $newimage = imagerotate($sourceImage, 90, 0);
                    break;
                default:
                    $newimage = $sourceImage;
            }
            $sourceImage = $newimage;
        }
        else {
            $sourceImage = imagecreatefromjpeg($sourceImagePath);
            if (!$sourceImage)
                $sourceImage= imagecreatefromstring(file_get_contents($sourceImagePath));
        }
        
        // now resize and save image
        $orgWidth = imagesx($sourceImage);
        $orgHeight = imagesy($sourceImage);
        $thumbHeight = floor($orgHeight * ($thumbWidth / $orgWidth));
        $destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $orgWidth, $orgHeight);
        imagejpeg($destImage, $destImagePath);
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
    }
}

// some arrays contain directories rather than images, need to skip them
function getValidImageIndex($index,$images,$direction) {
    if ($index < 0) // check bounds
        $index = count($images)-1;
    else if ($index > (count($images)-1))
        $index = 0;     
    $start = $index;
    $img = $images[$index];
    while (empty($img['file'])) {
        $index += $direction;
        if ($index < 0) // wrap if out of bounds
            $index = count($images)-1;
        else if ($index > (count($images)-1))
            $index = 0;       
        if ($index == $start)
            break;
        $img = $images[$index];
    }
    return $index;
}

function debug_console($textstr) { ?>
    <script>
        console.log("<?php echo $textstr;?>");
	</script> <?php
} ?>

<script>
    function GoBackToReferer(page) { 
        window.location.replace(page);
    }
</script> <?php

///////// end of functions //////////

//////// processing starts /////////

// should we leave?
if (isset($_POST['page_logout'])||(isset($_POST['goto_prev_page']))) {
    if (isset($_POST['page_logout']))
        unset($_SESSION['picture_password']);
    $page = $_POST['prev_page'];
    ?><script>window.location.replace('<?php echo $page;?>');</script><?php
}

// determine which page first called our script
if (!isset($_POST['prev_page'])) {
    $prev_page = getenv('HTTP_REFERER');
}
else {
    $prev_page = $_POST['prev_page'];
}

// check password status
if (isset($_POST['submit_pass']) && $_POST['pass'])
{
    $pass=$_POST['pass'];
    if ($pass=="password")
        $_SESSION['picture_password']=$pass;
    else
        $error="Incorrect Password";
}
else {
    $error = "";
}

// do we have an image array?
$image_current = 0;
if (isset($_POST["image_array"])) {
    $images_string = $_POST["image_array"];
    $images_string = str_replace("'","\"",$images_string); // revert single quotes back to double quotes
    $images = unserialize($images_string);                 // now recreate our array
    if (isset($_POST["image_start"]))
        $image_current = getValidImageIndex((int)$_POST["image_start"],$images,0);
    else if (isset($_POST["image_next"]))
        $image_current = getValidImageIndex((int)$_POST["image_next"]+1,$images,1);
    else if (isset($_POST["image_previous"]))
        $image_current = getValidImageIndex((int)$_POST["image_previous"]-1,$images,-1);
}

// get base host server url
if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" )
    $protocol = "https://";
else
    $protocol = "http://";
$http_base = $protocol . $_SERVER['HTTP_HOST'];
$server_root = $_SERVER['DOCUMENT_ROOT']; ?>

<html>

<head>
    <link rel="icon" href="<?php echo $http_base.'/favicon.ico';?>" type="mage/x-icon"/>
    <link rel="shortcut icon" href="<?php echo $http_base.'/favicon.ico';?>" type="image/x-icon"/>
    <meta charset="UTF-8">
    <meta name="author" content="Don Mankin">
    <meta name="description" content="Display all video & images in a folder">
    <meta name="keywords" content="PHP, HTML, CSS, JavaScript">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="my_text">

<style>
ul  {
    list-style: none;
    color: #fff;
    font-weight: bold;
    margin: 0;
    padding: 0;
}
.projbox {
    padding: 0px 0px;
    margin: 10px;
    display: inline-block;
    box-sizing: border-box;
    max-height: 180px;
    width: 180px;
}
.video {
    padding: 0px 0px;
    margin: 10px;
    display: inline-block;
    box-sizing: border-box;
    max-height: 180px;
    width: 180px;
}
body
{
    margin:0 auto;
    padding:0px;
    text-align:center;
    width:100%;
    font-family: "Myriad Pro","Helvetica Neue",Helvetica,Arial,Sans-Serif;
    color:#ffffff;
    background-color:#8A4B08;
}
.my_text
{
    font-family:    "Myriad Pro","Helvetica Neue",Helvetica,Arial,Sans-Serif;
    font-size:      9px;
    font-weight:    bold;
}
.fsdiv {
    width:80vw;
    height:80vh;
    display:table-cell;
    vertical-align:middle;
    text-align:center;
}
.fsimg {
    max-width:100%;
    height:auto;
    max-height:90%;
} <?php
if (isMobile()) { ?>
    #login_form
    {   
        display:table-cell;    
        margin-top:100px;
        background-color:white;
        width:650px;
        padding:20px;
        box-sizing:border-box;
        box-shadow:0px 0px 10px 0px #3B240B;
    } 
    #login_form h1
    {
        margin:0px;
        font-size:50px;
        color:#8A4B08;
    }
    #login_form input[type="password"]
    {
        width:400px;
        margin-top:10px;
        height:40px;
        padding-left:10px;
        font-size:32px;
    }
    #login_form input[type="submit"]
    {
        width:400px;
        margin-top:10px;
        height:40px;
        font-size:32px;
        background-color:#8A4B08;
        border:none;
        box-shadow:0px 4px 0px 0px #61380B;
        color:white;
        border-radius:3px;
    }
    #login_form p
    {
        margin:0px;
        margin-top:15px;
        color:#8A4B08;
        font-size:32px;
        font-weight:bold;
    } <?php  
}
else { ?>
    #login_form
    {   
        display:table-cell;    
        margin-top:100px;
        background-color:white;
        width:350px;
        padding:20px;
        box-sizing:border-box;
        box-shadow:0px 0px 10px 0px #3B240B;
    } 
    #login_form h1
    {
        margin:0px;
        font-size:25px;
        color:#8A4B08;
    }
    #login_form input[type="password"]
    {
        width:250px;
        margin-top:10px;
        height:40px;
        padding-left:10px;
        font-size:16px;
    }
    #login_form input[type="submit"]
    {
        width:250px;
        margin-top:10px;
        height:40px;
        font-size:16px;
        background-color:#8A4B08;
        border:none;
        box-shadow:0px 4px 0px 0px #61380B;
        color:white;
        border-radius:3px;
    }
    #login_form p
    {
        margin:0px;
        margin-top:15px;
        color:#8A4B08;
        font-size:17px;
        font-weight:bold;
    } <?php   
}
if (isMobile()) { ?>
    #start_form input[type="submit"],#next_form input[type="submit"],#previous_form input[type="submit"]
    {
        font-size:22px;
        background:none;
        border:none;
        color:white;
        font-weight: bold;
    }
    #logoff_form input[type="submit"]
    {
        font-size:22px;
        background:none;
        border:none;
        color:white;
    }
    a:link, a:visited {
    font-size: 22px;
    background-color: #8A4B08;
    color: white;
    text-decoration: none;
    }
    a:hover, a:active {
    font-size: 22px;
    background-color: #8A4B08;
    text-decoration: none;
    } <?php
}
else { ?>
    #start_form input[type="submit"],#next_form input[type="submit"],#previous_form input[type="submit"]
    {
        font-size:12px;
        background:none;
        border:none;
        color:white;
        font-weight: bold;
    }
    #logoff_form input[type="submit"]
    {
        font-size:12px;
        background:none;
        border:none;
        color:white;
    }
    a:link, a:visited {
    font-size: 12px;
    background-color: #8A4B08;
    color: white;
    text-decoration: none;
    }
    a:hover, a:active {
    font-size: 12px;
    background-color: #8A4B08;
    text-decoration: none;
    } <?php
} ?>
</style>

<?php

// globals
$createThumbs = TRUE;
$recursive = TRUE;
<<<<<<< HEAD
$showHeader = TRUE;
$CheckPW = FALSE;  // set to FALSE to disable

if (($CheckPW == FALSE)||(isset($_SESSION['picture_password'])&&(($_SESSION['picture_password']=="password"))))
=======
$showHeader = FALSE;
$CheckPW = FALSE;

if (($CheckPW == FALSE)||(isset($_SESSION['picture_password'])&&(($_SESSION['picture_password']=="password1")||($_SESSION['picture_password']=="password2"))))
>>>>>>> be8251339a63aa74442ab3fce4c67c397242a9b2
{
    // lets hog the memory
    ini_set('memory_limit', '-1');

    // set for 15 minutes
    ini_set('max_execution_time', 450);

    // resets the time limit value
    set_time_limit(0);
    
   // get current directory
    $current_dir = getcwd();
    $url_root = $http_base . str_replace($server_root,"",str_replace("\\","/",$current_dir));

    // shall we recurse folders?
    if (isset($_GET['recurse'])) {
        $shallrecurse = strtolower($_GET['recurse']);
        if ($shallrecurse == "true" || $shallrecurse == "1")
            $recursive = TRUE;
        else if ($shallrecurse == "false" || $shallrecurse == "0")
            $recursive = FALSE;
    }
    
    if (isMobile()) { ?>
        <div style="font-size: 22px; font-weight: bold;"> <?php
    }
    else { ?>
        <div style="font-size: 12px; font-weight: bold;"> <?php
    }

    if ($showHeader == TRUE) { ?>
        &nbsp;<br>Directory "<b><?php echo basename($current_dir);?></b>" <?php
        if ($recursive == TRUE) echo " recursed";
    }
    else { ?>
        &nbsp; <?php
    } ?>
    
    </div>
    
    <?php
    
    // get variables
    $hard_dir = $current_dir . "/";
    
    $vid_formats = array("mp4", "mov");
    $pic_formats = array("jpg", "jpeg", "png", "gif", "bmp");  // tif is not supported in modern browsers
    
    // have we done this before?
    if (!isset($_POST["image_array"])) {
    
        // make sure we know what we are talking about
        if (!file_exists($hard_dir))
            die("");   
            
        // fetch image details - hard folder path
        $images = getFileList($hard_dir, $recursive);
        
        // sort the images by newest first
        // usort($images, function($a, $b){ return(filemtime($a['file']) < filemtime($b['file'])); });
        
        // sort the images alphabetically
        usort($images, function($a, $b){ return(basename($a['file']) > basename($b['file'])); });
        
        // create thumbnails
        if ($createThumbs)
            createThumbnailsFromFileList($images,$server_root,$http_base,$pic_formats);
        
        // display images
        displayFileList($images,$server_root,$http_base,$pic_formats,$vid_formats,$prev_page, $CheckPW);
    }
    else {     
        // display images
        displayFile($images,$image_current,$server_root,$http_base,$pic_formats,$vid_formats,$prev_page, $CheckPW);
    } ?>

   
    
    <?php
}
else
{
    ?>
    <center><br><br><br><br><br><br><br><br>
    <form method="post" action="" id="login_form">
    <input type="hidden" name="prev_page" value="<?php echo $prev_page;?>"> 
    <h1>Photos &amp; Videos</h1>
    <input type="password" name="pass" placeholder="*******">
    <br>
    <input type="submit" name="submit_pass" value="SUBMIT PASSWORD">
    <p><font style="color:red;"><?php echo $error;?></font></p>
    </form>
    </center>
    <?php	
}
?>

</div> <!--- class="my_text" -->
</body>
</html>
