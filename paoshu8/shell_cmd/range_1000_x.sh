#bin/bash
i=10
host="192.168.10.15"
port="3306"
user="root"
password="HM9GO3JH3XrLoouh"
database="book_center"
#echo "$query"
query="SELECT
store_id,
title,
pro_book_id,
author
FROM
ims_novel_info AS i
WHERE
is_async =0
and  source ='paoshu8'
ORDER BY
store_id asc 
LIMIT 100"
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
        echo "store_id: $column1 title：$column2 author：$column4"
        shell_cmd="cd /www/wwwroot/work_project/novelProject/paoshu8/ && nohup /www/server/php/72/bin/php gather_info_local.php $column1 >> run_range.out 2>&1 &"
        echo $shell_cmd | bash;
    done <<< "$result"
fi



