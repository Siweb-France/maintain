#!/usr/bin/env bash

# $1 : Host
# $2 : Partition
# $3 : Seuil
# $4 : email

usedpct=`df -h $2 | grep -v Taille | awk '{print $5}' | sed -e "s/%//"`;
available=`df -h $2 | grep -v Taille | awk '{print $4}'`;
avpct=`expr 100 - $usedpct`;
if [ $usedpct -ge $3 ];
        then
echo "
Notification Type: PROBLEM

Host: $1
Service: $2
State: WARNING
Date/Time: `date +%c`

Additional Info:

DISK WARNING - free space: $2 $available ($avpct%)
" | mail -s "** PROBLEM alert - $1 - $2 is WARNING **" $4;
fi
