#bin/bash

i=10

MYSQL_HOST='localhost'
MYSQL_USER='root'
MYSQL_PASS='root'
MYSQL_DATABASE='book_center'
MYSQL_PORT='3306'

#定义查询语句
SQL_QUERY1='select * from ims_novel_info limit 3;'

# use $MYSQL_DATABASE;
mysql -h localhost -uroot -proot -D book_center
echo 3
#mysql -h $MYSQL_HOST -P $MYSQL_PORT -u$MYSQL_USER -p$MYSQL_PASS assurance_acct -A --default-character-set=utf8 -N -e "
#SELECT * FROM ims_novel_info limit 3;
#"

