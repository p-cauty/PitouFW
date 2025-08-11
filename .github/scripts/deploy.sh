#echo '------'
#which jq
#jq --version
#echo '------'

echo $DEPLOY_SERVER
echo $SSH_USER
echo $REPO_PATH
echo $REPO_NAME
echo $TAG

#echo '------'
#curl -H "Authorization: token $GH_TOKEN" "https://api.github.com/repos/$REPO_PATH/releases/tags/$TAG"
#curl -H "Authorization: token $GH_TOKEN" "https://api.github.com/repos/$REPO_PATH/releases/tags/$TAG"
#echo '------'

asset_id=$(curl -H "Authorization: token $GH_TOKEN" "https://api.github.com/repos/$REPO_PATH/releases/tags/$TAG" | jq -r '.assets[0].id')
asset_name=$(curl -H "Authorization: token $GH_TOKEN" "https://api.github.com/repos/$REPO_PATH/releases/tags/$TAG" | jq -r '.assets[0].name')

#echo $asset_id
#echo $asset_name

curl -H "Authorization: token $GH_TOKEN" -H "Accept: application/octet-stream" -LJO "https://api.github.com/repos/$REPO_PATH/releases/assets/$asset_id"

#ls -la

echo "$SSH_PRIVATE" > ~/id_ed25519
chmod 600 ~/id_ed25519

scp -oStrictHostKeyChecking=accept-new -i '~/id_ed25519' "$asset_name" "$SSH_USER@$DEPLOY_SERVER:$ROOT_PATH/"
ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" "tar xzf $ROOT_PATH/$asset_name -C $ROOT_PATH"

if ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" [[ -d "$ROOT_PATH/$REPO_NAME" ]]; then
  ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" "rm -rf $ROOT_PATH/$REPO_NAME.old && mv $ROOT_PATH/$REPO_NAME $ROOT_PATH/$REPO_NAME.old"
fi

ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" "mv $ROOT_PATH/$REPO_NAME.new $ROOT_PATH/$REPO_NAME"

if ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" [[ -d "$ROOT_PATH/$REPO_NAME.old/storage" ]]; then
  ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" "rm -rf $ROOT_PATH/$REPO_NAME/storage && mv $ROOT_PATH/$REPO_NAME.old/storage $ROOT_PATH/$REPO_NAME/storage"
fi

if ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" [[ -f "$ROOT_PATH/$REPO_NAME.old/.env" ]]; then
  ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" "mv -f $ROOT_PATH/$REPO_NAME.old/.env $ROOT_PATH/$REPO_NAME/.env"
fi

#ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" "chmod -R a+w $ROOT_PATH/$REPO_NAME/storage"
ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" "php $ROOT_PATH/$REPO_NAME/vendor/bin/phinx migrate -c $ROOT_PATH/$REPO_NAME/phinx.php"
ssh -i '~/id_ed25519' "$SSH_USER@$DEPLOY_SERVER" "rm -f $ROOT_PATH/$asset_name"