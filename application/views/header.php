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
<h1 align="center" >Vampire System</h1>
<div id=nav align="right" >
<a href="?category=myri">My Equipment</a>&nbsp;
<a href="?category=misalign">Misalign Equipment</a>&nbsp;
<a href="?category=allri">All Equipment</a>&nbsp;
<a href="?category=ip">Lab IP</a>&nbsp;
<?=$_SERVER['REMOTE_USER']?>&nbsp;
<?=$_SERVER['REMOTE_ADDR']?>
</div> <!-- nav -->
<div id="main" >
