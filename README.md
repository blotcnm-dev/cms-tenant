<br><br>
## 🚀 개요
### 이 저장소는 blotcnm의 CMS 프로젝트를 위한 코드를 포함하고 있습니다.
<br><br><br>
## 📋 기술 스택
### - 프론트엔드: 미정
### - 백엔드 : Laravel
### - 데이터베이스 : MySQL
<br><br>
## ✨ 작업 히스토리
### - v1.0.0 (2025-04-03)
<br><br>
## 🌿 브랜치 전략
### - main : 개발 통합 브랜치
### - feature/* : 새로운 기능 개발 (ex : feature/user-auth )
### - bugfix/* : 버그 수정 (ex : bugfix/login-issue )
### - hotfix/* : 긴급 프로덕션 수정 (ex : hotfix/security-patch )
### - release/* : 릴리스 준비 (ex :release/v1.1.0 )
<br><br>


## ✨ 초기 배포 시 수행 코드!! 
        
    
        # 환경설정 파일 
        cp .env.example .env
        
        # DB 접속정보 변경 
        /.env                       DB_CONNECTION=mysql
        /config/database.php        DB접속정보 변경

        # 의존성 설치
        composer install --no-dev --optimize-autoloader --no-interaction

        # 키 생성 
        php artisan key:generate

        # 퍼미션 설정 
        sudo chown -R www-data:www-data /home/cms/html/bootstrap/cache
        sudo chmod -R 775 /home/cms/html/bootstrap/cache

        sudo chown -R www-data:www-data /home/cms/html/storage
        sudo chmod -R 775 /home/cms/html/storage

        sudo chown www-data:www-data /home/cms/html/database/
        sudo chmod 775 /home/cms/html/database/ 
 
    
        # 캐쉬 클리어
        php artisan config:clear
        php artisan cache:clear
        php artisan view:clear
        php artisan route:clear


        # DB 마이그레이션 (필요한 경우) 
        #php artisan migrate --force


        # 캐쉬 생성 
        php artisan config:cache
        php artisan cache:cache
        php artisan view:cache  
        php artisan route:cache
