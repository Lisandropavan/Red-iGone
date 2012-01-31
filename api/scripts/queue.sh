#!/bin/bash
for (( ; ; ))
do
	/usr/bin/php /var/www/api.redigone.com/application/controllers/queuecontroller.php
	sleep 1
done
