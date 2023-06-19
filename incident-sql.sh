#!/bin/bash
set -ex

docker-compose stop mysql
docker-compose rm --force mysql
docker-compose up -d

while ! docker exec -i -e MYSQL_PWD=root pim-community-dev_mysql_1 mysql --user=root akeneo_pim_test --silent &> /dev/null <<<"show databases;"; do
    echo "Waiting for database connection..."
    sleep 2
done
#
docker exec -i -e MYSQL_PWD=root pim-community-dev_mysql_1 mysql --user=root akeneo_pim_test < ./mysql-slow.log
#
#docker exec -i -e MYSQL_PWD=root pim-community-dev_mysql_1 mysql --user=root akeneo_pim_test <<< "UPDATE performance_schema.setup_consumers SET ENABLED = 'YES' WHERE NAME = 'events_statements_history_long'"
#
