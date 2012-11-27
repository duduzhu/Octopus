<?php
define("AD4",$_SERVER['REMOTE_USER']);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title; ?></title>
<script type="text/javascript" src="jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="ddtf.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
jQuery(document).ready(function () {
    jQuery('#ri').ddTableFilter();
});
//--><!]]>
</script>
<style type="text/css">
<!--
.like_filter_data{ background:#CCC}.like_filter_data tr td,.like_filter_data tr th{ text-align: center; height:25px; line-height:25px; font-size:12px; font-weight:normal; background:#FFF;}.like_filter_data tr th{ background:#EEEEEE; font-weight:bold; font-size:12px; height:30px; line-height:30px; text-align:center;}
-->
</style>
</head>
<body>
<h1 align="center" ><?php echo $heading; ?></h1>
<table align="center" border="0" cellpadding="0" cellspacing="1" id="ri" class="like_filter_data">
<tbody>
<tr><th>Equipment Type</th><th>Equipment Label</th><th>MNEMONIC </th><th>SERIAL NUMBER </th><th>LastUpdate</th></tr>
<?php 
foreach($table as $row)
{
echo "<tr>";
echo "<td>";
echo $row['parent_type'];
echo "</td>";
echo "<td>";
echo $row['parent_sn'];
echo "</td>";
echo "<td>";
echo $row['meta_type'];
echo "</td>";
echo "<td>";
echo $row['meta_type'];
echo "</td>";
echo "<td>";
echo $row['timestamp'];
echo "</td>";
echo "</tr>";
}
?> 

</tbody>
</table>
<hr>
<p align="center" >Copyright (C) Alcatel-Lucent 2012 <a href="https://acos.alcatel-lucent.com/projects/lric-g/" >Acos Homepage</a></p>
</body>
</html>
