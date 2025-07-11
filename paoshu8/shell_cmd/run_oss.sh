#bin/bash
i=10
host="192.168.10.15"
port="3306"
user="root"
password="HM9GO3JH3XrLoouh"
database="novel"
#echo "$query"
query="SELECT
id,
book_name as title,
author
FROM mc_book
WHERE
( instr(source_url,'http')>0 or instr(source_url,'qijitushu')>0 ) and is_aws_store = 0
ORDER BY
id asc 
LIMIT 50"
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
    while IFS=$'\t' read -r column1 column2 column3; do
        # 在这里处理每一行数据，可以使用变量column1、column2、column3
        #   # 例如打印每一行的数据
        echo "id: $column1 title：$column2 author：$column3"
        shell_cmd="cd /www/wwwroot/work_project/novelProject/paoshu8/ && nohup /www/server/php/72/bin/php synR3Client.php $column1 >> run_oss.out 2>&1 &"
        echo $shell_cmd | bash;
    done <<< "$result"
fi

date "+%Y-%m-%d %H:%M:%S"
echo $date

