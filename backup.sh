#!/bin/sh
# バックアップ世代数
period=3

# 保存ディレクトリ
dirpath='/home/*****'

# ファイル名を定義
filename=`date +%y%m%d`

# mysqldump実行(mysql5.6以降は直接パスワードが書き込めないのでこんな感じで。)
MYSQL_PWD="*****" mysqldump --opt --all-databases --events --default-character-set=binary -u **** -h **** | gzip > $dirpath/$filename.sql.gz

# 古いバックアップファイルを削除
oldfile=`date --date "$period days ago" +%y%m%d`
rm -f $dirpath/$oldfile.sql.gz
