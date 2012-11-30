<?php
if(isset($heading))
{
    ?>
        <h2><?=$heading?></h2>
        <br />
    <?php
}
?>
<table align="center" border="0" cellpadding="0" cellspacing="1" id="ri" class="showlist">
<tbody>
<tr><th>Equipment</th><th>Equipment</th><th>USER</th><th>RESOURCE</th><th>SN</th><th>USER</th><th>LastUpdate</th></tr>
<?php 
foreach($table as $row)
{
    echo "<tr ";
    if($row['parent_user']!="" && $row['meta_user']!="" && $row['meta_user']!=$row['parent_user'])
    { ?> style="color:red" <?php }
    echo ">";

    echo "<td>";
    echo $row['parent_type'];
    echo "</td>";

    echo "<td>";
    ?> <a href="?category=showparent&parent_sn=<?=$row['parent_sn']?>"><?=$row['parent_sn']?></a><?php
    if($row['parent_user']=="")
    {
        ?>&nbsp;<a href="?category=ownparent&parent_id=<?=$row['parent_id']?>"> Declare</a><?php
    }
    if($row['parent_user']==$_SERVER['REMOTE_USER'])
    {
        ?>&nbsp;<a href="?category=releaseparent&parent_id=<?=$row['parent_id']?>"> Release</a><?php
    }
    echo "</td>";

    echo "<td>";
    ?> <a href="?category=ri_by_user&user=<?=$row['parent_user']?>"><?=$row['parent_user']?></a> <?php
    echo "</td>";

    echo "<td>";
    ?> <a href="?category=showmetatype&meta_type=<?=$row['meta_type']?>"><?=$row['meta_type']?></a><?php
    echo "</td>";

    echo "<td>";
    ?> <a href="?category=showmetahistory&meta_id=<?=$row['meta_id']?>"><?=$row['meta_sn']?></a> <?php
    if($row['meta_user']=="")
    {
        ?>&nbsp;<a href="?category=ownmeta&meta_id=<?=$row['meta_id']?>"> Declare</a><?php
    }
    if($row['meta_user']==$_SERVER['REMOTE_USER'])
    {
        ?>&nbsp;<a href="?category=releasemeta&meta_id=<?=$row['meta_id']?>"> Release</a><?php
    }
    echo "</td>";

    echo "<td>";
    ?> <a href="?category=ri_by_user&user=<?=$row['meta_user']?>"><?=$row['meta_user']?></a> <?php
    echo "</td>";

    echo "<td>";
    echo $row['timestamp'];
    echo "</td>";

    echo "</tr>";
}
?> 

</tbody>
</table>
