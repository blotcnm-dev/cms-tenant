#!/bin/bash

TENANT_NAME="tenant1"
TENANT_DOMAIN="tenant1.example.com"
TENANT_DB="tenant1_db"
TENANT_ROOT="/custom/path/to/tenants"   # 원하는 경로로 지정

TENANT_GIT="https://github.com/your-org/tenant-template.git"
TENANT_PATH="$TENANT_ROOT/$TENANT_NAME"

# 1. 소스 코드 복제
git clone  $TENANT_GIT $TENANT_PATH

# 2. .env 파일 생성
cp $TENANT_PATH/.env.example $TENANT_PATH/.env
echo "APP_URL=https://$TENANT_DOMAIN" >> $TENANT_PATH/.env
echo "DB_DATABASE=$TENANT_DB" >> $TENANT_PATH/.env
# ... 기타 환경변수 추가

# 3. Nginx 설정 자동 추가
cat <<EOL > /etc/nginx/sites-available/$TENANT_NAME
server {
    listen 80;
    server_name $TENANT_DOMAIN;
    root $TENANT_PATH/public;
    # 기타 설정
}
EOL
ln -s /etc/nginx/sites-available/$TENANT_NAME /etc/nginx/sites-enabled/
nginx -s reload
