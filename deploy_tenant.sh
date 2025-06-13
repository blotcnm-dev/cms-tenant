#!/bin/bash

# ===============================================
# 테넌트 생성 + SSL 자동화 스크립트
# ===============================================

set -e  # 오류 발생 시 스크립트 중단

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 로그 함수들
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 설정 변수
TENANT_NAME="cmstest"
TENANT_DOMAIN="cmstest.blot-i.co.kr"
TENANT_DB="cmstest"
TENANT_ROOT="/home/tenants"   # 원하는 경로로 지정
TENANT_GIT="https://github.com/blotcnm-dev/cms-tenant.git"
TENANT_PATH="$TENANT_ROOT/$TENANT_NAME"

# 관리자 이메일 (Let's Encrypt용)
ADMIN_EMAIL="admin@blot-i.co.kr"

# PHP 버전 (자동 감지)
PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)

log_info "=== 테넌트 생성 + SSL 자동화 시작 ==="
log_info "테넌트: $TENANT_NAME"
log_info "도메인: $TENANT_DOMAIN"
log_info "경로: $TENANT_PATH"
log_info "PHP 버전: $PHP_VERSION"

# ===============================================
# 1. 사전 조건 확인
# ===============================================
log_info "사전 조건 확인 중..."

# root 권한 확인
if [ "$EUID" -ne 0 ]; then
    log_error "이 스크립트는 root 권한으로 실행해야 합니다."
    log_info "sudo $0 를 사용하세요."
    exit 1
fi

# 필수 패키지 확인
check_package() {
    if ! command -v $1 &> /dev/null; then
        log_error "$1이 설치되지 않았습니다."
        return 1
    fi
}

check_package "nginx" || exit 1
check_package "php" || exit 1
check_package "git" || exit 1
check_package "certbot" || {
    log_warning "Certbot이 설치되지 않았습니다. 설치를 진행합니다..."
    snap install --classic certbot
    ln -sf /snap/bin/certbot /usr/bin/certbot
}

# 도메인 DNS 확인
log_info "도메인 DNS 확인 중..."
if ! nslookup $TENANT_DOMAIN &> /dev/null; then
    log_warning "도메인 $TENANT_DOMAIN의 DNS 설정을 확인해주세요."
    read -p "계속 진행하시겠습니까? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# ===============================================
# 2. 디렉토리 생성 및 권한 설정
# ===============================================
log_info "디렉토리 구조 생성 중..."

# 테넌트 루트 디렉토리 생성
mkdir -p $TENANT_ROOT
mkdir -p /var/log/nginx

# 기존 테넌트 확인
if [ -d "$TENANT_PATH" ]; then
    log_warning "테넌트 디렉토리가 이미 존재합니다: $TENANT_PATH"
    read -p "기존 디렉토리를 삭제하고 새로 생성하시겠습니까? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        rm -rf $TENANT_PATH
        log_info "기존 디렉토리 삭제 완료"
    else
        log_error "작업을 중단합니다."
        exit 1
    fi
fi

# ===============================================
# 3. 소스 코드 복제
# ===============================================
log_info "소스 코드 복제 중..."

git clone $TENANT_GIT $TENANT_PATH

if [ ! -d "$TENANT_PATH" ]; then
    log_error "소스 코드 복제에 실패했습니다."
    exit 1
fi

cd $TENANT_PATH

# ===============================================
# 4. 환경 설정 파일 생성
# ===============================================
log_info "환경 설정 파일 생성 중..."

if [ -f ".env.example" ]; then
    cp .env.example .env
    log_success ".env 파일 생성 완료"

    # 환경변수 설정
    sed -i "s|APP_URL=.*|APP_URL=https://$TENANT_DOMAIN|g" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$TENANT_DB|g" .env
    sed -i "s|APP_NAME=.*|APP_NAME=\"$TENANT_NAME\"|g" .env

    log_success "환경변수 설정 완료"
else
    log_warning ".env.example 파일이 없습니다. 수동으로 .env 파일을 생성해주세요."
fi

# ===============================================
# 5. 권한 설정
# ===============================================
log_info "파일 권한 설정 중..."

chown -R www-data:www-data $TENANT_PATH
chmod -R 755 $TENANT_PATH

# Laravel/PHP 프로젝트인 경우 추가 권한 설정
if [ -d "$TENANT_PATH/storage" ]; then
    chmod -R 775 $TENANT_PATH/storage
    chmod -R 775 $TENANT_PATH/bootstrap/cache
    log_success "Laravel 권한 설정 완료"
fi

# ===============================================
# 6. Composer 의존성 설치 (필요한 경우)
# ===============================================
if [ -f "$TENANT_PATH/composer.json" ]; then
    log_info "Composer 의존성 설치 중..."
    cd $TENANT_PATH
    if command -v composer &> /dev/null; then
        sudo -u www-data composer install --no-dev --optimize-autoloader
        log_success "Composer 설치 완료"
    else
        log_warning "Composer가 설치되지 않았습니다. 수동으로 설치해주세요."
    fi
fi

# ===============================================
# 7. 기본 Nginx 설정 생성 (HTTP만)
# ===============================================
log_info "기본 Nginx 설정 생성 중..."

cat <<EOL > /etc/nginx/sites-available/$TENANT_NAME
# HTTP 서버 (SSL 인증서 발급용)
server {
    listen 80;
    server_name $TENANT_DOMAIN;

    root $TENANT_PATH/public;
    index index.html index.php;

    # 로그 파일
    access_log /var/log/nginx/$TENANT_DOMAIN.access.log;
    error_log /var/log/nginx/$TENANT_DOMAIN.error.log;

    # 기본 location
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP 파일 처리
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php$PHP_VERSION-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # 정적 파일 최적화
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)\$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # 보안 설정
    location ~ /\.ht {
        deny all;
    }

    location ~ /\.(git|env) {
        deny all;
    }
}
EOL

# sites-enabled에 심볼릭 링크 생성
ln -sf /etc/nginx/sites-available/$TENANT_NAME /etc/nginx/sites-enabled/

# Nginx 설정 테스트
log_info "Nginx 설정 테스트 중..."
if nginx -t; then
    log_success "Nginx 설정 테스트 통과"
else
    log_error "Nginx 설정에 오류가 있습니다."
    exit 1
fi

# Nginx 재시작
systemctl reload nginx
log_success "Nginx 재시작 완료"

# ===============================================
# 8. Let's Encrypt SSL 인증서 발급
# ===============================================
log_info "SSL 인증서 발급 중..."

# 방화벽 확인 및 포트 열기
if command -v ufw &> /dev/null; then
    ufw allow 80/tcp
    ufw allow 443/tcp
    log_info "방화벽 포트 80, 443 열기 완료"
fi

# Certbot으로 SSL 인증서 발급 및 자동 설정
log_info "Certbot 실행 중... (자동 모드)"

certbot --nginx \
    -d $TENANT_DOMAIN \
    --non-interactive \
    --agree-tos \
    --email $ADMIN_EMAIL \
    --redirect

if [ $? -eq 0 ]; then
    log_success "SSL 인증서 발급 및 설정 완료!"
else
    log_error "SSL 인증서 발급에 실패했습니다."
    log_info "수동으로 다음 명령어를 실행해보세요:"
    log_info "sudo certbot --nginx -d $TENANT_DOMAIN"
    exit 1
fi

# ===============================================
# 9. 보안 헤더 추가
# ===============================================
log_info "보안 헤더 추가 중..."

# 기존 HTTPS 설정에 보안 헤더 추가
cat <<EOL >> /etc/nginx/sites-available/$TENANT_NAME

# 추가 보안 설정
server {
    listen 443 ssl http2;
    server_name $TENANT_DOMAIN;

    # 보안 헤더 추가
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline' 'unsafe-eval'" always;

    # OCSP Stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    resolver 8.8.8.8 8.8.4.4 valid=300s;
    resolver_timeout 5s;
}
EOL

# Nginx 재시작
nginx -t && systemctl reload nginx

# ===============================================
# 10. 자동 갱신 설정
# ===============================================
log_info "SSL 인증서 자동 갱신 설정 중..."

# Cron job 확인 및 추가
if ! crontab -l 2>/dev/null | grep -q "certbot renew"; then
    (crontab -l 2>/dev/null; echo "0 12,0 * * * /usr/bin/certbot renew --quiet && /usr/sbin/nginx -s reload") | crontab -
    log_success "자동 갱신 설정 완료"
else
    log_info "자동 갱신이 이미 설정되어 있습니다."
fi

# ===============================================
# 11. 완료 및 테스트
# ===============================================
log_success "=== 테넌트 생성 및 SSL 설정 완료! ==="

echo
log_info "📋 설정 정보:"
echo "  • 테넌트 이름: $TENANT_NAME"
echo "  • 도메인: $TENANT_DOMAIN"
echo "  • 문서 루트: $TENANT_PATH/public"
echo "  • SSL 인증서: /etc/letsencrypt/live/$TENANT_DOMAIN/"
echo "  • Nginx 설정: /etc/nginx/sites-available/$TENANT_NAME"

echo
log_info "🔗 접속 URL:"
echo "  • HTTP: http://$TENANT_DOMAIN (자동으로 HTTPS로 리다이렉트)"
echo "  • HTTPS: https://$TENANT_DOMAIN"

echo
log_info "🧪 테스트 명령어:"
echo "  • SSL 테스트: curl -I https://$TENANT_DOMAIN"
echo "  • 설정 확인: sudo nginx -t"
echo "  • 인증서 확인: sudo certbot certificates"

echo
log_info "🔄 관리 명령어:"
echo "  • Nginx 재시작: sudo systemctl reload nginx"
echo "  • SSL 갱신: sudo certbot renew"
echo "  • 로그 확인: sudo tail -f /var/log/nginx/$TENANT_DOMAIN.access.log"

# 최종 연결 테스트
log_info "연결 테스트 중..."
if curl -s -o /dev/null -w "%{http_code}" https://$TENANT_DOMAIN | grep -q "200\|301\|302"; then
    log_success "✅ HTTPS 연결 성공!"
else
    log_warning "⚠️  HTTPS 연결 테스트 실패. 수동으로 확인해주세요."
fi

log_success "🎉 모든 작업이 완료되었습니다!"
