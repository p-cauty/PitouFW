rm -rf pitoufw/.git
touch release.tar.gz
tar --exclude=release.tar.gz -zcvf release.tar.gz pitoufw
