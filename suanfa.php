<?php

$sql ="select ifnull( (select distinct disce from xx limit 1,1) , null) as secondearvae";
//删除重复的电子邮箱，保留最小的那个
$sql ='delete from persion where id not in (select id from (select min(id) as id from persion group by email) as t )';
?>

