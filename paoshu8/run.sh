#bin/bash
i=10
host="127.0.01"
port="3306"
user="root"
password="HM9GO3JH3XrLoouh"
database="book_center"

query="SELECT store_id,story_id,pro_book_id,title FROM ims_novel_info where is_async = 0  limit 1"
#echo "$query"
result=$(mysql -h $host -u $user -p$password $database -s -e "$query")


if [ $? -ne 0 ]; then
    echo "数据库连接失败或查询错误：$result"
    exit 1
fi
#echo "$result"

#echo "$shell_cmd"

NAME=VALUEBANK
#eval $shell_cmd
while IFS=$'\t' read -r column1 column2 column3 column4; do
    # 在这里处理每一行数据，可以使用变量column1、column2、column3
    #   # 例如打印每一行的数据
    echo "store_id: $column1"
    shell_cmd="cd /www/wwwroot/work_project/novelProject/paoshu8/ && nohup /www/server/php/72/bin/php gather_info_local.php $column1 >> run_store.out 2>&1 &"
    echo $shell_cmd
    #echo "nohup php aa.php  $column1  >>tst.out  2>&1" | bash;
done <<< "$result"


