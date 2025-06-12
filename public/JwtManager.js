class JwtManager {
    constructor() {
        this.logToPage("constructor :::::: TokenManager.js");
        this.initializeLogContainer();
        this.checkInterval = null;
        this.logAllCookies(); // 모든 쿠키 확인
        this.initializeTokenChecker();
    }

    logAllCookies() {
        this.logToPage("모든 쿠키 목록:");
        const cookies = document.cookie.split(';');
        if (cookies.length === 1 && cookies[0] === "") {
            this.logToPage("쿠키가 없습니다.");
        } else {
            cookies.forEach(cookie => {
                this.logToPage(cookie.trim());
            });
        }
    }

    // 로그 컨테이너 초기화 메서드
    initializeLogContainer() {
        // 기존 로그 컨테이너가 있는지 확인
        this.logContainer = document.getElementById('token-log-container');

        // 없으면 새로 생성
        if (!this.logContainer) {
            this.logContainer = document.createElement('div');
            this.logContainer.id = 'token-log-container';
            this.logContainer.style.cssText = 'max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin: 10px 0; font-family: monospace; background-color: #f8f8f8;';

            // 제목 추가 (선택사항)
            const title = document.createElement('h3');
            title.textContent = 'Token Manager Log';
            title.style.margin = '0 0 10px 0';
            this.logContainer.appendChild(title);

            // body나 특정 컨테이너에 추가
            document.body.appendChild(this.logContainer);

            // 초기 로그 메시지
            this.logToPage('Token Manager initialized');
        }
    }




    // JWT 디코딩 (만료 시간 확인용)
    decodeToken(token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            return JSON.parse(atob(base64));
        } catch (error) {
            return null;
        }
    }

    // 토큰 만료 시간 확인
    getTokenExpiry() {
        const token = this.getCookie('jwt_token');
        console.log(token);
        if (!token) return null;

        const decoded = this.decodeToken(token);
        this.logToPage("토큰 만료시간 ===>["+ decoded + "]");
        return decoded ? decoded.exp * 1000 : null; // 밀리초로 변환
    }

    // 주기적으로 토큰 상태 체크
    initializeTokenChecker() {
        // 30초마다 체크
        this.checkInterval = setInterval(() => {
            this.checkAndRefreshToken();
        }, 30000); //30000

        // 초기 체크
        this.checkAndRefreshToken();
    }

    async checkAndRefreshToken() {
        const expiry = this.getTokenExpiry();
        if (!expiry) return;

        const now = Date.now();

        const timeUntilExpiry = expiry - now;
        // 로그에 남은 시간 표시 (분 단위로 변환)
        const minutesLeft = Math.floor(timeUntilExpiry / (60 * 1000));
        this.logToPage(`토큰 만료까지 ${minutesLeft}분 남음`);

        // 만료 5분 전에 갱신
        if (timeUntilExpiry < 5 * 60 * 1000) {
            this.logToPage('Token expiring soon, refreshing...');
            await this.refreshToken();
        }
    }

    async refreshToken() {
        try {
            const response = await fetch('https://cms.blot-i.co.kr/api/refresh', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                this.logToPage('Token refreshed successfully');
            } else {
                // 갱신 실패 - 로그인 페이지로
                window.location.href = '/login';
            }
        } catch (error) {
            console.error('Token refresh failed:', error);
        }
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }



    logToPage(message) {
        // 로그 컨테이너가 없으면 메시지만 콘솔에 출력
        if (!this.logContainer) {
            console.log("컨테이너 없슴==>["+message+"]");
            return;
        }
        console.log("컨테이너있슴.");
        const logEntry = document.createElement('div');
        const timestamp = new Date().toLocaleTimeString();
        logEntry.innerHTML = `<span style="color: #888;">[${timestamp}]</span> ${message}`;
        this.logContainer.appendChild(logEntry);

        // 자동 스크롤 맨 아래로
        this.logContainer.scrollTop = this.logContainer.scrollHeight;
    }


}
