rm -rf $REPO.new/.github $REPO.new/.git $REPO.new/.gitignore $REPO.new/composer.json $REPO.new/composer.lock $REPO.new/.docker $REPO.new/docker-compose.yml
echo "$TAG" > $REPO.new/version.txt
touch release.tar.gz
tar --exclude=release.tar.gz -zcvf release.tar.gz $REPO.new