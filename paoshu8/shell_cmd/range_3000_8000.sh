#bin/bash
i=10
host="127.0.01"
port="3306"
user="root"
password="HM9GO3JH3XrLoouh"
database="book_center"

query="SELECT  i.store_id,mc.book_name as title,mc.id as pro_book_id,mc.author,mc.source_url,is_few,chapter_num,chapter_few_num  from novel.mc_book as mc left join book_center.ims_novel_info as i
on mc.id = i.pro_book_id
WHERE  mc.is_few=1 and  source='paoshu8' and mc.id is not null  and mc.chapter_num>3000 and   mc.chapter_num<8000 order by mc.uptime desc LIMIT 50"
#echo "$query"
result=$(mysql -h $host -u $user -p$password $database -s -e "$query")


if [ $? -ne 0 ]; then
    echo "数据库连接失败或查询错误：$result"
    exit 1
fi

#echo "$result"
if [ ! -n "$result" ]; then
    echo "DATA IS NULL"
else
    NAME=VALUEBANK
    while IFS=$'\t' read -r column1 column2 column3 column4; do
        # 在这里处理每一行数据，可以使用变量column1、column2、column3
        #   # 例如打印每一行的数据
        echo "store_id: $column1"
        shell_cmd="cd /www/wwwroot/work_project/novelProject/paoshu8/ && nohup /www/server/php/72/bin/php gather_info_local.php $column1 >> run_range.out 2>&1 &"
        echo $shell_cmd | bash;
    done <<< "$result"
fi



