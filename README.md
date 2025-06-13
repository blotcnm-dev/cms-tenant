 
        
    
        # 환경설정 파일 
        cp .env.example .env
        
        # DB 접속정보 변경 
        /.env                       DB접속정보 변경 

        # 의존성 설치
        composer install --no-dev --optimize-autoloader --no-interaction
      
        # 캐쉬 클리어
        php artisan optimize:clear 

        # 캐쉬 생성 
        php artisan optimize


        # 퍼미션 설정 
        WORK_DIR="/home/cms/html"            
        sudo chown -R www-data:cms $WORK_DIR 
        sudo find  $WORK_DIR/storage -type d -exec chmod 775 {} \;
        sudo find  $WORK_DIR/storage -type f -exec chmod 664 {} \;
        sudo find  $WORK_DIR/bootstrap/cache -type d -exec chmod 775 {} \;
        sudo find  $WORK_DIR/bootstrap/cache -type f -exec chmod 664 {} \;
  
