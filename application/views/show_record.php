<?php
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
        <input type="text" name="note" value="<?php echo $note; ?>" />
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
    function transferparent($parent_id)
    {
        window.location.href="?category=transferparent&targetuser="+prompt("Target USER CSL")+"&parent_id="+$parent_id;
    }
    function transfermeta($meta_id)
    {
        window.location.href="?category=transfermeta&targetuser="+prompt("Target USER CSL")+"&meta_id="+$meta_id;
    }
</script>

<table align="center" border="0" cellpadding="0" cellspacing="0" id="ri" class="display">
	<thead>
<tr><th></th><th>Equip</th><th>Label</th><th>USER</th><th>NAME</th><th>SN</th><th>USER</th><th>LastUpdate</th></tr>
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
        ?>&nbsp;<a href="?category=ownparent&parent_id=<?=$row['parent_id']?>">[Declare]</a><?php
    }
    if($row['parent_user']==$vampireuser || 'naniw' == $vampireuser)
    {
        ?>
            &nbsp;
            <a href="?category=releaseparent&parent_id=<?=$row['parent_id']?>">[Release]</a>
            &nbsp;
            <a href="javascript:transferparent(<?php echo $row['parent_id']; ?>)">[Transfer]</a>
        <?php
    }
    echo "</td>";

    echo "<td>";
    ?> <a href="mailto:<?=$row['parent_user']?>@sh.ad4.ad.alcatel.com"><?=$row['parent_user']?></a> <?php
    echo "</td>";

    echo "<td>";
    ?> <a href="?category=showmetatype&meta_type=<?=$row['meta_type']?>"><?=$row['meta_type']?></a><?php
    echo "</td>";

    echo "<td>";
    ?> <a title="<?=$row['meta_note']?>" href="?category=showmeta&meta_id=<?=$row['meta_id']?>"><?=$row['meta_sn']?></a> <?php
    if(($row['meta_user']==""&&$row['meta_sn']!=""&&$vampireuser!='anonymous')|| 'naniw' == $vampireuser)
    {
        ?>&nbsp;<a href="?category=ownmeta&meta_id=<?=$row['meta_id']?>">[Declare]</a><?php
    }
    if($row['meta_user']==$vampireuser  || 'naniw' == $vampireuser)
    {
        ?>&nbsp;<a href="?category=releasemeta&meta_id=<?=$row['meta_id']?>">[Release]</a>
         &nbsp;
            <a href="javascript:transfermeta(<? echo $row['meta_id']; ?>)">[Transfer]</a>
         <?php
    }
    echo "</td>";

    echo "<td>";
    ?> <a href="mailto:<?=$row['meta_user']?>@sh.ad4.ad.alcatel.com"><?=$row['meta_user']?></a> <?php
    echo "</td>";

    echo "<td>";
    echo $row['timestamp'];
    echo "</td>";

    echo "</tr>";
}
?> 

</tbody>
</table>

