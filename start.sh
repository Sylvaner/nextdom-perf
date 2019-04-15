#!/bin/bash

BRANCH_TO_TEST=develop

function installation_jeedom() {
	echo -e "\033[31mInstallation Jeedom\033[0m"
	wget https://raw.githubusercontent.com/jeedom/core/master/install/install.sh > /dev/null 2>&1
	docker run --name jeedom-perf -p 81:80 -d sylvaner1664/nextdom-dev /bin/sh -c "while true; do sleep 10; done"
	docker cp install.sh jeedom-perf:/ > /dev/null 2>&1
	docker exec jeedom-perf bash /install.sh > /dev/null 2>&1
	docker cp backup-Jeedom-3.3.20-2019-04-14-12h22.tar.gz jeedom-perf:/var/www/html/backup/ 
	docker exec jeedom-perf php /var/www/html/install/restore.php --force > /dev/null 2>&1
}

function installation_nextdom() {
	echo -e "\033[31mInstallation NextDom\033[0m"
	wget https://raw.githubusercontent.com/NextDom/NextDom-DebInstaller/master/deb-install.sh > /dev/null 2>&1
	docker run --name nextdom-perf -p 82:80 -d sylvaner1664/nextdom-dev /bin/sh -c "while true; do sleep 10; done"
	docker cp deb-install.sh nextdom-perf:/
	docker exec nextdom-perf rm -fr /var/www/html
	docker exec nextdom-perf git clone https://github.com/NextDom/nextdom-core /var/www/html
	docker exec nextdom-perf bash -c "cd /var/www/html && git checkout $BRANCH_TO_TEST && git rev-parse HEAD"
	docker exec nextdom-perf service mysql start > /dev/null 2>&1
	docker exec nextdom-perf ./var/www/html/install/postinst > /dev/null 2>&1
	docker cp backup-Jeedom-3.3.20-2019-04-14-12h22.tar.gz nextdom-perf:/var/www/html/backup/ 
	docker exec nextdom-perf php /var/www/html/install/restore.php > /dev/null 2>&1
}

function start_jeedom_benchmark() {
	echo -e "\033[31mBenchmark Jeedom\033[0m"
	docker exec jeedom-perf service mysql start > /dev/null
	docker exec jeedom-perf service apache2 stop > /dev/null
	docker exec jeedom-perf service cron stop > /dev/null
	docker cp benchmark.php jeedom-perf:/var/www/html/
	docker exec -i jeedom-perf php /var/www/html/benchmark.php
	docker cp jeedom-perf:/var/www/html/result.json result_jeedom.json
}

function start_nextdom_benchmark() {
	echo -e "\033[31mBenchmark NextDom\033[0m"
	docker exec nextdom-perf service mysql start > /dev/null
	docker exec nextdom-perf service apache2 stop > /dev/null
	docker exec nextdom-perf service cron stop > /dev/null
	docker cp benchmark.php nextdom-perf:/var/www/html/
	docker exec -i nextdom-perf php /var/www/html/benchmark.php
	docker cp nextdom-perf:/var/www/html/result.json result_nextdom.json
}

function start() {
	installation_jeedom
	installation_nextdom
	start_jeedom_benchmark
	start_nextdom_benchmark
}

if [ -f branch_to_test ]; then
	BRANCH_TO_TEST=$(<branch_to_test)
fi
start
