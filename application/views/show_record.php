<?php

function addButton($text,$note,$parameters)
{
	$urlparts = explode('?', $_SERVER['REQUEST_URI'],2);
	?>
	<a title="<?php echo $note; ?>"href="http://<?php
		echo $_SERVER['HTTP_HOST'].$urlparts[0];
		echo $parameters;
	?>" ><button><?php echo $text; ?></button></a>
	<?php
}
if(isset($heading))
{
    ?>
        <h2><?=$heading?></h2>
    <?php
}
if(isset($note))
{?>
Note:<br />
    <form action="<?php if(isset($action))echo $action; ?>">
	<textarea rows="3" cols="64" name="note" align="center"><?php echo $note; ?></textarea>
        <br /><input type=submit value="Update Note" />
        <input type=hidden name="databasetable" value="<?php echo $databasetable;?>" />
        <input type=hidden name="category" value="updatenote" />
        <input type=hidden name="id" value="<?php echo $id;?>" />
    </form>
<?php
}

?>
<style type="text/css">
#note{border:1px solid #cccccc; background:#9900CC;color:#fff; padding:5px; display:none; position:absolute;}
</style>

<script language="javascript">
    function askid()
    {
        return prompt("Target USER CSL");
    }
    function transferparent($parent_id)
    {
        $id=askid();
        if($id == null)
            return;

        window.location.href="?category=transferparent&targetuser="+$id+"&parent_id="+$parent_id;
    }
    function transfermeta($meta_id)
    {
        $id=askid();
        if($id == null)
            return;
        window.location.href="?category=transfermeta&targetuser="+$id+"&meta_id="+$meta_id;
    }
</script>

<table align="center" border="0" cellpadding="0" cellspacing="0" id="ri" class="display">
	<thead>
<tr><th width="1" ><img src="img/alert_16.png" /></th><th width="1" title="PlatformType">P</th><th title="PlatformName">N</th><th width="1" title="USER">U</th><th width="1" title="Equipment">E</th><th title="SerialNumber">S</th><th width="31" title="User">U</th><th title="Source">S</th><th title="LastUpdate">L</th></tr>
</thead>
<tbody>

<?php 
foreach($table as $row)
{
    if($row['meta_type']!="" && ($row['parent_user']!="" || $row['meta_user']!="") && $row['meta_user']!=$row['parent_user'])
        $alert=true;
    else
        $alert=false;
    echo "<tr ";
    if($alert)
    { ?> style="color:red" <?php }
    echo " >";
    echo "<td>";
    if($alert)
    { ?> <img src="img/alert_16.png" /> <?php }
    echo "</td>";

    echo "<td>";
    ?> <a href="?category=showparenttype&parent_type=<?=$row['parent_type']?>"><?=$row['parent_type']?></a><?php
    echo "</td>";

    echo "<td>";
    ?> <a title="<?=$row['parent_note']?>" href="?category=showparent&parent_id=<?=$row['parent_id']?>"><?=$row['parent_sn']?></a><?php
    if(($row['parent_user']==""&&$row['parent_sn']!=""&&$vampireuser!='anonymous')|| 'naniw' == $vampireuser)
    {
        addButton('D','Declare','?category=ownparent&parent_id='.$row['parent_id']);
    }
    if($row['parent_user']==$vampireuser || 'naniw' == $vampireuser)
    {
        addButton('R','Release','?category=releaseparent&parent_id='.$row['parent_id']);
        addButton('T','Transfer','javascript:transferparent('.$row['parent_id'].')');
    }
    echo "</td>";

    echo "<td>";
    ?> <a href="mailto:<?=$row['parent_user']?>@sh.ad4.ad.alcatel.com"><?=$row['parent_user']?></a> <?php
    echo "</td>";

    echo "<td>";
    ?> <a href="?category=showmetatype&meta_type=<?=$row['meta_type']?>"><?=$row['meta_type']?></a><?php
    echo "</td>";

    echo "<td>";
    ?> <a title="<?=$row['meta_note']?>" href="?category=showmeta&meta_id=<?=$row['meta_id']?>"><?=$row['meta_sn']?></a><?php
    if(($row['meta_user']==""&&$row['meta_sn']!=""&&$vampireuser!='anonymous')|| 'naniw' == $vampireuser)
    {
	addButton('D','Declare','?category=ownmeta&meta_id='.$row['meta_id']);
    }
    if($row['meta_user']==$vampireuser  || 'naniw' == $vampireuser)
    {
	addButton('R','Release','?category=releasemeta&meta_id='.$row['meta_id']);
	addButton('T','Transfer','javascript:transfermeta('.$row['meta_id'].')');
    }
    echo "</td>";

    echo "<td>";
    ?> <a href="mailto:<?=$row['meta_user']?>@sh.ad4.ad.alcatel.com"><?=$row['meta_user']?></a> <?php
    echo "</td>";

    echo "<td>";
    echo $row['source'];
    echo "</td>";

    echo "<td>";
    $times=explode(' ',$row['timestamp'],2);
    echo $times[0];
    echo "</td>";

    echo "</tr>";
}
?> 

</tbody>
</table>

