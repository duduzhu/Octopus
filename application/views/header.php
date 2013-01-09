<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Vampire - GSM Lab Remote Inventory Auto Collector</title>
<link href="css/screen.css" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="js/custom.js"></script>
</head>
<body>
<!--oncopy="alert('Do not copy!');return false;" oncut="alert('Do not copy!');return false;"-->

<div id=outer> 
<table background="img/icon_bg_grad2.gif" width="1000" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
     <td width="78%" class="systemtitle" > <span align="center" >Vampire System </span></td>
  </tr>
</table>

<div id=nav align="right" >
<a href="?category=misalign"><img src="img/alert_16.png" />Misalign</a>&nbsp;
<a href="?category=myri">Mine</a>&nbsp;
<a href="?category=allri">All</a>&nbsp;
<a href="?category=ip">IP</a>&nbsp;
<a href="?category=adm">ADM</a>&nbsp;
<a href="?category=upload">Upload</a>&nbsp;
<?php
    echo $vampireuser;
?>
&nbsp;
<?php
    if($vampireuser!='anonymous')
    {
        if(!isset($_SERVER['REMOTE_USER']))
            echo "<a href='?category=logout' >Logout </a>";
    }
    else
    {
        echo "<a href='?category=login' >Login </a>";
    }
?>
<?=$_SERVER['REMOTE_ADDR']?>
</div> <!-- nav -->
<div id="main" >
