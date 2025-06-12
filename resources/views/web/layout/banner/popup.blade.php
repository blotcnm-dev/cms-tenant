@if(isset($globalBanners['popup']) && count($globalBanners['popup']) > 0)
    {{-- 팝업 오버레이 --}}
    <div id="popupOverlay" class="popup-overlay" style="display: none;">
        {{-- 여러 팝업 처리 --}}
        @foreach($globalBanners['popup'] as $index => $popup)
            <div class="popup-modal"
                 id="popup{{ $popup['id'] }}"
                 data-popup-id="{{ $popup['id'] }}"
                 data-popup-index="{{ $index }}"
                 style="display: none;">

                {{-- 팝업 헤더 --}}
                <div class="popup-header">
                    <h3 class="popup-title">{{ $popup['title'] }}</h3>
                    <button class="popup-close" onclick="closePopup({{ $popup['id'] }})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- 팝업 내용 --}}
                <div class="popup-content">
                        @if($popup['link'])
                        <a href="{{ $popup['link'] }}"
                           target="{{ $popup['target'] }}"
                           onclick="trackBannerClick({{ $popup['id'] }}, 'popup')">
                            @endif
                            @if(isset($popup['image']) && !empty(trim($popup['image'])))
                            <div class="popup-image">
                                <img src="{{ $popup['image'] }}"
                                     alt="{{ $popup['title'] }}"
                                     loading="lazy">
                            </div>
                            @endif
                            @if($popup['description'])
                                <div class="popup-description">
                                    {!! nl2br(e($popup['description'])) !!}
                                </div>
                            @endif

                            @if($popup['link'])
                        </a>
                    @endif
                </div>

                {{-- 팝업 푸터 --}}
                <div class="popup-footer">
                    @if(isset($popup['is_today']) && $popup['is_today'] == 'Y')
                        <label class="popup-today-close">
                            <input type="checkbox" id="todayClose{{ $popup['id'] }}">
                            <span>오늘 하루 보지 않기</span>
                        </label>
                    @endif

                    {{-- 다중 팝업일 경우 네비게이션 표시 --}}
                    @if(count($globalBanners['popup']) > 1)
                        <div class="popup-navigation">
                            <span class="popup-counter">{{ $index + 1 }} / {{ count($globalBanners['popup']) }}</span>
                        </div>
                    @endif

                    <button class="popup-close-btn" onclick="closePopup({{ $popup['id'] }})">
                        닫기
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
    /* 팝업 오버레이 */
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(3px);
        animation: fadeIn 0.3s ease-out;
    }

    .popup-overlay.hidden {
        display: none !important;
    }

    /* 팝업 모달 */
    .popup-modal {
        background: white;
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        max-width: 500px;
        max-height: 80vh;
        width: 90%;
        overflow: hidden;
        animation: popupSlideIn 0.4s ease-out;
        position: relative;
    }

    /* 팝업 헤더 */
    .popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
    }

    .popup-title {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        color: #333;
    }

    .popup-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #666;
        transition: color 0.2s;
        padding: 5px;
        border-radius: 50%;
    }

    .popup-close:hover {
        color: #333;
        background: #e9ecef;
    }

    /* 팝업 내용 */
    .popup-content {
        padding: 0;
        text-align: center;
        max-height: 60vh;
        overflow-y: auto;
    }

    .popup-content a {
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .popup-image {
        width: 100%;
        line-height: 0;
    }

    .popup-image img {
        width: 100%;
        height: auto;
        display: block;
    }

    .popup-description {
        padding: 20px 25px;
        font-size: 14px;
        line-height: 1.6;
        color: #666;
    }

    /* 팝업 푸터 */
    .popup-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 25px;
        border-top: 1px solid #eee;
        background: #f8f9fa;
        flex-wrap: wrap;
        gap: 10px;
    }

    .popup-today-close {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-size: 14px;
        color: #666;
    }

    .popup-today-close input {
        margin-right: 8px;
    }

    .popup-navigation {
        display: flex;
        align-items: center;
        font-size: 12px;
        color: #999;
    }

    .popup-counter {
        background: #e9ecef;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    .popup-close-btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
    }

    .popup-close-btn:hover {
        background: #0056b3;
    }

    /* 애니메이션 */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes popupSlideIn {
        from {
            opacity: 0;
            transform: scale(0.8) translateY(-50px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes popupSlideOut {
        from {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        to {
            opacity: 0;
            transform: scale(0.8) translateY(-50px);
        }
    }

    .popup-modal.closing {
        animation: popupSlideOut 0.3s ease-in forwards;
    }

    /* 반응형 */
    @media (max-width: 768px) {
        .popup-modal {
            max-width: 95%;
            max-height: 85vh;
            margin: 20px;
        }

        .popup-header,
        .popup-footer {
            padding: 15px 20px;
        }

        .popup-description {
            padding: 15px 20px;
        }

        .popup-title {
            font-size: 16px;
        }

        .popup-footer {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }

        .popup-today-close {
            order: 2;
            justify-content: center;
        }

        .popup-navigation {
            order: 1;
            justify-content: center;
        }

        .popup-close-btn {
            order: 3;
            width: 100%;
        }
    }

    /* 스크롤바 커스텀 */
    .popup-content::-webkit-scrollbar {
        width: 6px;
    }

    .popup-content::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .popup-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .popup-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<script>
    // 팝업 관리 객체
    const PopupManager = {
        currentIndex: 0,
        popups: [],
        visiblePopups: [],
        overlay: null,

        // 초기화
        init() {
            console.log('PopupManager 초기화 시작');

            this.overlay = document.getElementById('popupOverlay');
            this.popups = Array.from(document.querySelectorAll('.popup-modal'));

            if (this.popups.length === 0) {
                console.log('팝업이 없습니다.');
                return;
            }

            console.log(`총 ${this.popups.length}개의 팝업 발견`);

            this.setupEventListeners();
            this.filterVisiblePopups();
            this.showFirstPopup();
        },

        // 이벤트 리스너 설정
        setupEventListeners() {
            // ESC 키로 팝업 닫기
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeCurrentPopup();
                }
            });

            // 오버레이 클릭으로 팝업 닫기
            if (this.overlay) {
                this.overlay.addEventListener('click', (e) => {
                    if (e.target === this.overlay) {
                        this.closeCurrentPopup();
                    }
                });
            }
        },

        // 표시 가능한 팝업 필터링 (오늘 하루 보지 않기 체크)
        filterVisiblePopups() {
            this.visiblePopups = this.popups.filter(popup => {
                const popupId = popup.dataset.popupId;
                const todayCloseKey = `popup_today_close_${popupId}`;
                const todayClose = localStorage.getItem(todayCloseKey);
                const today = new Date().toDateString();

                if (todayClose === today) {
                    console.log(`팝업 ${popupId}: 오늘 하루 보지 않기 설정됨`);
                    return false;
                }
                return true;
            });

            console.log(`표시 가능한 팝업: ${this.visiblePopups.length}개`);
        },

        // 첫 번째 팝업 표시
        showFirstPopup() {
            if (this.visiblePopups.length === 0) {
                console.log('표시할 팝업이 없습니다.');
                if (this.overlay) {
                    this.overlay.style.display = 'none';
                }
                return;
            }

            console.log('첫 번째 팝업 표시');
            this.currentIndex = 0;
            this.showPopup(this.currentIndex);
        },

        // 특정 인덱스의 팝업 표시
        showPopup(index) {
            if (index < 0 || index >= this.visiblePopups.length) {
                console.log('잘못된 팝업 인덱스:', index);
                return;
            }

            // 모든 팝업 숨김
            this.popups.forEach(popup => {
                popup.style.display = 'none';
            });

            // 오버레이 표시
            if (this.overlay) {
                this.overlay.style.display = 'flex';
                this.overlay.classList.remove('hidden');
            }

            // 현재 팝업 표시
            const currentPopup = this.visiblePopups[index];
            currentPopup.style.display = 'block';

            console.log(`팝업 ${currentPopup.dataset.popupId} 표시 (${index + 1}/${this.visiblePopups.length})`);
        },

        // 다음 팝업 표시
        showNextPopup() {
            const nextIndex = this.currentIndex + 1;

            if (nextIndex < this.visiblePopups.length) {
                console.log('다음 팝업으로 이동');
                this.currentIndex = nextIndex;
                this.showPopup(this.currentIndex);
            } else {
                console.log('모든 팝업 표시 완료');
                this.closeAllPopups();
            }
        },

        // 현재 팝업 닫기
        closeCurrentPopup() {
            if (this.visiblePopups.length === 0) return;

            const currentPopup = this.visiblePopups[this.currentIndex];
            if (currentPopup) {
                const popupId = currentPopup.dataset.popupId;
                this.closePopup(popupId);
            }
        },

        // 특정 팝업 닫기
        closePopup(popupId) {
            console.log(`팝업 ${popupId} 닫기 요청`);

            const popup = document.getElementById(`popup${popupId}`);
            const todayCloseCheckbox = document.getElementById(`todayClose${popupId}`);

            // 오늘 하루 보지 않기 처리
            if (todayCloseCheckbox && todayCloseCheckbox.checked) {
                const todayCloseKey = `popup_today_close_${popupId}`;
                const today = new Date().toDateString();
                localStorage.setItem(todayCloseKey, today);
                console.log(`팝업 ${popupId}: 오늘 하루 보지 않기 설정됨`);
            }

            // 현재 팝업에 닫기 애니메이션 적용
            if (popup) {
                popup.classList.add('closing');
                setTimeout(() => {
                    popup.style.display = 'none';
                    popup.classList.remove('closing');

                    // 다음 팝업 표시
                    this.showNextPopup();
                }, 300);
            }
        },

        // 모든 팝업 닫기
        closeAllPopups() {
            console.log('모든 팝업 닫기');

            if (this.overlay) {
                this.overlay.classList.add('hidden');
                setTimeout(() => {
                    this.overlay.style.display = 'none';
                }, 300);
            }

            // 모든 팝업 숨김
            this.popups.forEach(popup => {
                popup.style.display = 'none';
            });
        }
    };

    // 전역 함수로 팝업 닫기 (버튼에서 호출)
    function closePopup(popupId) {
        PopupManager.closePopup(popupId);
    }

    // 배너 클릭 추적
    function trackBannerClick(bannerId, position) {
        console.log(`배너 클릭: ${bannerId}, 위치: ${position}`);

        // CSRF 토큰 확인
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.warn('CSRF 토큰을 찾을 수 없습니다.');
            return;
        }

        fetch('/api/banner/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({
                banner_id: bannerId,
                position: position,
                action: 'click'
            })
        })
            .then(response => response.json())
            .then(data => {
                console.log('배너 클릭 추적 성공:', data);
            })
            .catch(err => {
                console.log('배너 클릭 추적 실패:', err);
            });
    }

    // 페이지 로드 후 팝업 초기화
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM 로드 완료, 팝업 초기화 대기 중...');

        // 2초 후 팝업 표시 (페이지 로딩 완료 후)
        setTimeout(() => {
            console.log('팝업 시스템 초기화');
            PopupManager.init();
        }, 2000);
    });

    // 디버깅용 함수들 (개발 중에만 사용)
    window.PopupDebug = {
        showAllPopups: () => {
            localStorage.clear();
            PopupManager.filterVisiblePopups();
            PopupManager.showFirstPopup();
        },
        hidePopup: (popupId) => {
            const key = `popup_today_close_${popupId}`;
            localStorage.setItem(key, new Date().toDateString());
        },
        clearStorage: () => {
            localStorage.clear();
            console.log('LocalStorage 초기화됨');
        },
        getCurrentPopup: () => {
            return PopupManager.visiblePopups[PopupManager.currentIndex];
        }
    };
</script>
