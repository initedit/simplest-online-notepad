<?php
/*
 * Global Variable
 * 
 */
define("BASE_PATH", "note/"); // Path (Relative to index.php) where notes will be saved

/*
 * Start Function
 */

/**
 *  Encrypts the given message
 * 
 *  @param string $msg The message which will be encrypted.
 * 
 */

function encrypt($msg) {
    $masala = ""; //Add your masala(Salt) to hash
    return hash("sha256", $masala . $msg);
}

/**
 * Makes the file downloadable
 * 
 * @param string $file File to be downloaded
 * 
 */
function download_file($file) {
    //Removes the "note." from filename
    $filename = str_replace("note.", "", $file);
    //
    $filename = strlen($filename) == 4 ? "note.txt" : $filename;
    $_file = BASE_PATH . $file;
    if (file_exists($_file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_file));
        readfile($_file);
        exit();
    }else{
        //Display Debug Info
        //echo $_file."FILE NOT FOUND";
    }
}

/*
 * End Functions
 */

$url = str_replace("/", "note.", "$_SERVER[REQUEST_URI]");
//$lock=str_replace("/","note.","$_SERVER[REQUEST_URI]");
$lock = $url . ".lock";


session_start();
$authenticated = false;
$isPAsswordProtected = false;
$globalMessage=null;

if (file_exists(BASE_PATH . $lock)) {
    $isPAsswordProtected = true;
}
if (isset($_POST['data'])) {
    $theData = $_POST['data'];
    $myFile = "$url.txt";
    if ($_POST["session_destroy"] == "true") {
        unset($_SESSION["PWD"]);
        $globalMessage = "Removed password";
    } else if ($_POST["download"] == "true") {
        $download_auth = false;
        $download_auth = file_exists(BASE_PATH . $myFile);
        if ($download_auth) {
            download_file($myFile);
        }
    } else if ($_POST["delete"] == "true") {
        $isPAsswordProtected = file_exists(BASE_PATH . $lock);
        if ($isPAsswordProtected) {
            $token = $_POST["token"];
            $md_token = encrypt($token);
            $fh = fopen(BASE_PATH . $lock, 'r');
            $theTokenData = fread($fh, filesize(BASE_PATH . $lock));
            if ($theTokenData == $md_token) {
                unlink(BASE_PATH . $myFile);
                unlink(BASE_PATH . $lock);
                session_destroy();
                $isPAsswordProtected = false;
                $theData = "";
            }
        } else {
            unlink(BASE_PATH . $myFile);
            unlink(BASE_PATH . $lock);
            session_destroy();
            $isPAsswordProtected = false;
            $theData = "";
        }
    } else {

        if ($isPAsswordProtected) {
            $token = $_POST["token"];
            $md_token = encrypt($token);

            $fh = fopen(BASE_PATH . $lock, 'r');
            $theTokenData = fread($fh, filesize(BASE_PATH . $lock));
            if ($theTokenData == $md_token) {
                $authenticated = true;
                $_SESSION["PWD"] = $md_token;
            } else {
                if (isset($_SESSION["PWD"])) {
                    if ($_SESSION["PWD"] == $theTokenData) {
                        $authenticated = true;
                    }
                }
            }

            $fh = fopen(BASE_PATH . $myFile, 'r');
            $theData = fread($fh, filesize(BASE_PATH . $myFile));
        } else if (isset($_POST["token"]) && !empty($_POST["token"])) {
            $token = $_POST["token"];
            $md5_token = encrypt($token);
            $_SESSION["PWD"] = $md5_token;
            $fh = fopen(BASE_PATH . $lock, 'w');
            fwrite($fh, $md5_token);
            $authenticated = true;
            $isPAsswordProtected = true;
        } else {
            $authenticated = true;
        }

        if ($authenticated) {
            $theData = $_POST['data'];
            $fh = fopen(BASE_PATH . $myFile, 'w');
            fwrite($fh, $_POST['data']);
        }
    }
} else {
    $myFile = "$url.txt";
    $fh = fopen(BASE_PATH . $myFile, 'r');
    $theData = fread($fh, filesize(BASE_PATH . $myFile));

    if ($isPAsswordProtected) {
        $fh = fopen(BASE_PATH . $lock, 'r');
        $theTokenData = fread($fh, filesize(BASE_PATH . $lock));
        if ($theTokenData == $_SESSION["PWD"]) {
            $authenticated = true;
        }
    }
}
fclose($fh);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-110672294-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', 'UA-110672294-1');
        </script>
        <style>
            body,textarea,a{
                background-color: #111;
                color: #FFF;
            }
            textarea{
                background-color: #CCC;
                color: #111;
            }
            .page-view{
                display: block;
                padding: 10px;
                font-size: 16px;
                width: 100%;
                text-align: center;
                box-sizing: border-box;
                opacity: 0;
            }
            .page-view:hover{
                opacity: 1;
            }
            .button{
                background-color: #FFF;
                border: 1px solid #F60;
                color: #111;
                padding: 5px 15px;
                font-size:20px;
                cursor: pointer;
            }
            .red{
                color: red;
            }
            .newfile{
                border: 0px;
                padding: 0px;
            }
            .key-img{
                width: 25px;
                height: 25px;
                margin-left: 10px;
            }
            .globalMessage{
                padding: 10px;
                background-color: #6F0;
                
            }
        </style>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>Simplest online notepad for copy-paste - Initedit</title>
        <link rel="icon" type="image/x-icon" href="favicon.svg"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>

    <body onload="bodyLoaded()">
        <?php if(!empty($globalMessage)){ ?>
        <div class="globalMessage" id="globalMessage">
            <?php echo $globalMessage;?>
        </div>
        <?php }?>
        <center>
            <a href="https://note.initedit.com" style="text-decoration: none;"><font style="font-size:20px;"><b>Simplest online notepad for copy-paste</b><br/><br/></font></a>
            <font style="font-size:20px;"> 

                <?php
                $filename = str_replace("/", "", "$_SERVER[REQUEST_URI]");
                echo "$filename" . ".txt";
                ?>
                (<span style="cursor: pointer;font-size: 14px;color: <?php echo ($isPAsswordProtected) ? "#F00" : "#0F0" ?>;" onclick="lockedClick()" id="locked">
                    <?php
                    echo $isPAsswordProtected ? "Locked" . ($authenticated ? " - editable" : "" ) : "Unlocked";
                    ?>

                </span>)
            </font>
            <br/>
            <form name="noteform" method="post" action="">
                <textarea name="data" spellcheck="false" id="textData" rows="20" style="width:100%;height:60vh;font-size:18px;box-sizing: border-box;"><?php echo $theData; ?></textarea>
                <input type="hidden" name="token" id="token"/>             
                <input type="hidden" name="session_destroy" id="session_destroy" value="false"/>   

                <input type="hidden" name="delete" id="delete" value="false"/>     
                <input type="hidden" name="download" id="download" value="false"/> 

                <input type="hidden" name="pwdProtected" id="pwdProtected" value="<?php echo $isPAsswordProtected ? "true" : "false"; ?>"/> 

                <br/><br/>
                <div>
                    <input type="submit" value="Save File" class="button" onclick="formSave()" /> (CTRL + S)
                    <img align="right" onclick="showDeleteConfirm()" src="/img/delete2.png" title="Delete file" class='key-img'/>                 
                    <img align="right" onclick="downloadClick()" src="/img/download2.png" title="Download file" class='key-img'/>
                    <img align="right" onclick="showPasswordPromt()"  src="/img/lock2.png" title="Lock file" class='key-img'/>
                    <img align="right" onclick="copyClipboard()" src="/img/copy2.png" title="Copy To Clipboard" class='key-img'/>

                </div>
                <div>
                    <span id="key-msg" style="float: right;"></span>
                </div>

                <br/>

            </form>
        </center>
        <center style="word-break: break-all;">
            <br/><br/>
            <b>Create your own unique url : https://note.initedit.com/<span class="red" id="newFileTxtPlaceholder" onclick="showCreateNewFile()">Enter_your_unique_Text</span>
                <input type="text" value="" class="newfile" onkeydown="createNewFile(event)" onblur="hideCreateNewFile()" style="display: none;" id="newFileTxt"/>
            </b>
            <br/>
            <br/>
            <a href="https://github.com/initedit-project/simplest-online-notepad">Download source code</a>
        </center>
        <script>
            
            function bodyLoaded(){
                setTimeout(hideGlobalMessage,2000);
            }
            function hideGlobalMessage(){
                var el = document.getElementById("globalMessage");
                el.innerHTML = "";
                el.style.display = "none";
            }
            
            function formSave() {
                showKeyMsg("Saved");
            }
            function showKeyMsg(msg) {
                var el = document.getElementById("key-msg");
                el.innerHTML = msg;
                el.style.display = "inline";
                setTimeout(hideKeyMsg, 1500);
            }

            function hideKeyMsg(msg) {
                var el = document.getElementById("key-msg");
                el.innerHTML = "";
                el.style.display = "none";
            }

            function showCreateNewFile() {
                document.getElementById("newFileTxtPlaceholder").style.display = "none";
                document.getElementById("newFileTxt").style.display = "inline";
                document.getElementById("newFileTxt").focus();
            }

            function hideCreateNewFile() {
                document.getElementById("newFileTxtPlaceholder").style.display = "inline";
                document.getElementById("newFileTxt").style.display = "none";

            }
            function createNewFile(e) {
                console.log(e);
                /*
                 * 
                 * 13 -> Enter Key
                 * 27 -> Esc Key
                 * 
                 */

                if (e.keyCode == 13) {
                    var str = document.getElementById("newFileTxt").value;
                    if (str.length > 0) {
                        window.location.href = "" + document.getElementById("newFileTxt").value;
                    } else {
                        hideCreateNewFile();
                    }
                } else if (e.keyCode == 27) {
                    hideCreateNewFile();
                }
            }

            function showPasswordPromt() {
                var pass = prompt("Set Password");
                document.getElementById("token").value = pass;
                if (pass && pass.length > 0) {
                    document.forms.noteform.submit();
                }
            }
            function showDeleteConfirm() {

                var pwdProtected = document.getElementById("pwdProtected").value;
                if (pwdProtected == "true") {
                    var pass = prompt("Are you sure?(Password)");
                    if (pass && pass.length > 0) {
                        document.getElementById("token").value = pass;
                        document.getElementById("delete").value = "true";
                        document.forms.noteform.submit();
                    }
                } else {
                    document.getElementById("delete").value = "true";
                    document.forms.noteform.submit();
                }
            }
            function lockedClick() {
                var element = document.getElementById("locked");
                var str = element.innerHTML.trim();
                if (str == "Locked" || str=="Unlocked") {
                    showPasswordPromt();
                } else if (str == "Locked - editable") {
                    document.getElementById("session_destroy").value = "true";
                    document.forms.noteform.submit();
                }
            }

            /*
             * Keyboard Shortcut
             * 
             */
            function saveShortcut(zEvent) {

//                if (zEvent.ctrlKey && zEvent.shiftKey && zEvent.code === "KeyS") {
//                    document.forms.noteform.submit();
//                }
                if (zEvent.ctrlKey) {
                    if (zEvent.code === "KeyS") {
                        document.forms.noteform.submit();
                        showKeyMsg("Saved");
                        zEvent.preventDefault();
                        return false;
                    } else if (zEvent.code === "KeyD") {
                        downloadClick();

                        zEvent.preventDefault();
                        return false;
                    } else if (zEvent.code === "KeyL") {
                        lockedClick();
                        zEvent.preventDefault();
                        return false;
                    }
                } else if (zEvent.altKey) {
                    if (zEvent.code === "KeyN") {
                        showCreateNewFile();
                        zEvent.preventDefault();
                        return false;
                    } else if (zEvent.code === "KeyC") {
                        copyClipboard();

                        zEvent.preventDefault();
                        return false;
                    }
                }
            }

            function copyClipboard() {
                /* Get the text field */
                var copyText = document.getElementById("textData");

                /* Select the text field */
                copyText.select();

                /* Copy the text inside the text field */
                document.execCommand("Copy");
                showKeyMsg("Copied");
            }

            function downloadClick() {
                showKeyMsg("Downloding");
                document.getElementById("download").value = "true";
                document.forms.noteform.submit();
            }
            document.addEventListener("keydown", saveShortcut);

        </script>
        <?php
        //Page Counter

        $fp = fopen("counterlog.txt", "r");

        $count = fread($fp, 1024);

        fclose($fp);

        $count = $count + 1;

        echo "<span class='page-view'>Counter : " . $count . "</span>";

        $fp = fopen("counterlog.txt", "w");

        fwrite($fp, $count);
        fclose($fp);
        ?>

    </body>

</html>