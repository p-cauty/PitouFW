rm -rf pitoufw/.git pitoufw/.github/scripts/build-fw.sh pitoufw/.github/workflows/build-fw.yml
touch release.tar.gz
tar --exclude=release.tar.gz -zcvf release.tar.gz pitoufw
