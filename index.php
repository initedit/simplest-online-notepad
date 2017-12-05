<?php
$url=str_replace("/","note","$_SERVER[REQUEST_URI]");
if(isset($_POST['data'])) {
$myFile = "$url.txt";
$theData = $_POST['data'];
$fh = fopen($myFile, 'w');
fwrite($fh, $_POST['data']);
} else {
$myFile = "$url.txt";
$fh = fopen($myFile, 'r');
$theData = fread($fh, filesize($myFile));
}
fclose($fh);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Simplest online notepad for copy-paste - Initedit</title>
</head>

<body>
<center>
    <a href="https://note.initedit.com" style="color:black;text-decoration: none;"><font style="font-size:20px;"><b>Simplest online notepad for copy-paste</b><br/><br/></font></a>
    <font style="font-size:20px;"> 
    <br/>
    <?php
    $filename=str_replace("/","","$_SERVER[REQUEST_URI]");
    echo "$filename".".txt";
    ?>
     </font>
    <br/>
<form name="test" method="post" action="">
    <textarea name="data" rows="20" cols="50" style="width:100%;height:60vh;font-size:18px;"><?php echo $theData; ?></textarea>
<br/><br/>
<input type="submit" name="submit" value="Save File" style="font-size:20px;" />
</form>
<br/><br/><br/>
Create your own unique url : https://note.initedit.com/<b>Enter_your_unique_Text</b>
</center>
</body>
</html>
