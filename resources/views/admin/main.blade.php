@extends('admin.layout.master')

@section('required-page-title', '대쉬 보드')
@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/termsList.css">
    <style>
        /* 기존 CSS에 Laravel 스타일 추가 */
        .page_title {
            margin-bottom: 30px;
            text-align: center;
        }

        .page_title .title {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 300;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .realtime-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .realtime-count {
            font-size: 3.5em;
            font-weight: bold;
            margin: 15px 0;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .metric-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
        }

        .metric-card h3 {
            color: #7f8c8d;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .metric-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #3498db;
        }

        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            height: 450px; /* 고정 높이 설정 */
            position: relative;
            overflow: hidden; /* 내용이 넘치지 않도록 */
        }

        .chart-container canvas {
            max-height: 350px !important; /* 캔버스 최대 높이 제한 */
            width: 100% !important;
        }

        .chart-container h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            height: 30px; /* 제목 높이 고정 */
        }

        .chart-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #95a5a6;
        }

        .chart-loading .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-error {
            text-align: center;
            color: #e74c3c;
            padding: 20px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
        }

        .controls {
            text-align: center;
            margin-top: 20px;
        }

        button {
            display: inline;
        }

        .btn-primary, .btn-secondary {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 24px;
            margin: 0 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-secondary {
            background: #95a5a6;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 400px; /* 모바일에서는 조금 더 작게 */
            }

            .chart-container canvas {
                max-height: 300px !important;
            }
        }

        /* 기간 검색 영역 스타일 추가 */
        .date-search-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }

        .date-search-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .date-search-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .date-search-content {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .date-search-label {
            font-weight: 500;
            color: #7f8c8d;
            white-space: nowrap;
        }

        .date-range-picker {
            flex: 1;
            min-width: 280px;
        }

        .search-button {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .search-button:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1c5985 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .search-button:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* 반응형 디자인 */
        @media (max-width: 768px) {
            .date-search-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .date-search-content {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .date-range-picker {
                min-width: auto;
            }
        }
    </style>
@stop

@section('required-page-header-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/src/js/dateRangePicker.js"></script>
@stop

@section('required-page-main-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <main> 
        <div id="wrap">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">대시보드</h2>
            </div>
            <!-- 페이지 타이틀 E  -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <!-- 실시간 사용자 -->
                <div class="realtime-card">
                    <h2>실시간 사용자</h2>
                    <div class="realtime-count" id="realtime-users">-</div>
                    <small>현재 웹사이트를 방문 중인 사용자</small>
                </div>

                <!-- 기간 검색 섹션 (새로 추가) -->
                <div class="date-search-section">
                    <div class="date-search-header">
                        <h3 class="date-search-title">📊 기간별 분석</h3>
                        <div class="period-info" id="current-period">최근 7일</div>
                    </div>
                    <div class="date-search-content">
                        <label class="date-search-label">조회 기간:</label>
                        <div class="date-range-picker" id="dateRangePicker"></div>
                        <button type="button" class="search-button" id="searchButton">
                            <span class="search-text">조회</span>
                            <span class="search-loading" style="display: none;">로딩중...</span>
                        </button>
                    </div>
                </div>

                <!-- 기본 메트릭 -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h3>총 사용자</h3>
                        <div class="metric-value" id="active-users">-</div>
                    </div>
                    <div class="metric-card">
                        <h3>총 세션</h3>
                        <div class="metric-value" id="sessions">-</div>
                    </div>
                    <div class="metric-card">
                        <h3>페이지뷰</h3>
                        <div class="metric-value" id="page-views">-</div>
                    </div>
                    <div class="metric-card">
                        <h3>이탈률</h3>
                        <div class="metric-value" id="bounce-rate">-</div>
                    </div>
                </div>

                <!-- 차트 그리드 -->
                <div class="charts-grid">
                    <!-- 상위 페이지 차트 -->
                    <div class="chart-container">
                        <h2>상위 페이지</h2>
                        <div class="chart-loading" id="pages-loading">
                            <div class="spinner"></div>
                            <div>데이터 로딩 중...</div>
                        </div>
                        <canvas id="pages-chart" style="display: none;"></canvas>
                        <div class="chart-error" id="pages-error" style="display: none;"></div>
                    </div>

                    <!-- 날짜별 트렌드 차트 -->
                    <div class="chart-container">
                        <h2>날짜별 트렌드</h2>
                        <div class="chart-loading" id="date-loading">
                            <div class="spinner"></div>
                            <div>데이터 로딩 중...</div>
                        </div>
                        <canvas id="date-chart" style="display: none;"></canvas>
                        <div class="chart-error" id="date-error" style="display: none;"></div>
                    </div>

                    <!-- 브라우저별 사용자 차트 (가로 막대로 변경) -->
                    <div class="chart-container">
                        <h2>브라우저별 사용자</h2>
                        <div class="chart-loading" id="browser-loading">
                            <div class="spinner"></div>
                            <div>데이터 로딩 중...</div>
                        </div>
                        <canvas id="browser-chart" style="display: none;"></canvas>
                        <div class="chart-error" id="browser-error" style="display: none;"></div>
                    </div>

                    <!-- 이벤트별 데이터 차트 -->
                    <div class="chart-container">
                        <h2>이벤트별 데이터</h2>
                        <div class="chart-loading" id="event-loading">
                            <div class="spinner"></div>
                            <div>데이터 로딩 중...</div>
                        </div>
                        <canvas id="event-chart" style="display: none;"></canvas>
                        <div class="chart-error" id="event-error" style="display: none;"></div>
                    </div>

                    <!-- 세션 소스/매체 차트 -->
                    <div class="chart-container">
                        <h2>세션 소스/매체</h2>
                        <div class="chart-loading" id="source-loading">
                            <div class="spinner"></div>
                            <div>데이터 로딩 중...</div>
                        </div>
                        <canvas id="source-chart" style="display: none;"></canvas>
                        <div class="chart-error" id="source-error" style="display: none;"></div>
                    </div>

                    <!-- 세션 캠페인 (일자별) 차트 -->
                    <div class="chart-container">
                        <h2>세션 캠페인 (일자별)</h2>
                        <div class="chart-loading" id="campaign-loading">
                            <div class="spinner"></div>
                            <div>데이터 로딩 중...</div>
                        </div>
                        <canvas id="campaign-chart" style="display: none;"></canvas>
                        <div class="chart-error" id="campaign-error" style="display: none;"></div>
                    </div>

                    <!-- 기기 카테고리별 사용자 차트 (새로 추가) -->
                    <div class="chart-container">
                        <h2>기기 카테고리별 사용자</h2>
                        <div class="chart-loading" id="device-loading">
                            <div class="spinner"></div>
                            <div>데이터 로딩 중...</div>
                        </div>
                        <canvas id="device-chart" style="display: none;"></canvas>
                        <div class="chart-error" id="device-error" style="display: none;"></div>
                    </div>

                    <!-- 화면 해상도별 사용자 차트 (새로 추가) -->
                    <div class="chart-container">
                        <h2>화면 해상도별 사용자</h2>
                        <div class="chart-loading" id="resolution-loading">
                            <div class="spinner"></div>
                            <div>데이터 로딩 중...</div>
                        </div>
                        <canvas id="resolution-chart" style="display: none;"></canvas>
                        <div class="chart-error" id="resolution-error" style="display: none;"></div>
                    </div>
                </div>

                <!-- 기간 선택 -->
                <div class="controls">
                    <button id="btn-7days" class="btn-primary">최근 7일</button>
                    <button id="btn-30days" class="btn-secondary">최근 30일</button>
                </div>
            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>
@stop

@section('required-page-add-content')
    <script type="module">
        class Dashboard {
            constructor() {
                this.charts = {
                    pages: null,
                    date: null,
                    browser: null,
                    event: null,
                    source: null,
                    campaign: null,
                    device: null,
                    resolution: null
                };

                // 기간 검색 관련 속성 추가
                this.dateRangePicker = null;
                this.currentDateRange = null;

                this.init();
                this.bindEvents();
            }

            // 이벤트 바인딩 메서드
            bindEvents() {
                // 7일 버튼
                document.getElementById('btn-7days').addEventListener('click', () => {
                    this.loadData(7);
                    this.updateActiveButton('btn-7days');
                    // 기간 선택기도 7일로 설정
                    this.updateDatePickerToPreset(7);
                });

                // 30일 버튼
                document.getElementById('btn-30days').addEventListener('click', () => {
                    this.loadData(30);
                    this.updateActiveButton('btn-30days');
                    // 기간 선택기도 30일로 설정
                    this.updateDatePickerToPreset(30);
                });
            }

            async init() {
                console.log('Dashboard 초기화 시작');
                // 기간 선택기 초기화 (새로 추가)
                this.initDateRangePicker();
                this.updateActiveButton('btn-7days');
                await this.loadData(7);
                await this.loadRealtimeData();

                // 30초마다 실시간 데이터 갱신
                // setInterval(() => this.loadRealtimeData(), 30000);
                console.log('Dashboard 초기화 완료');
            }

            // 기간 선택기 초기화 메서드 (새로 추가)
            initDateRangePicker() {
                // 기본값: 7일 전부터 오늘까지
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(startDate.getDate() - 6); // 7일 전 (오늘 포함해서 7일)

                this.dateRangePicker = new DateRangePicker({
                    container: '#dateRangePicker',
                    startDate: startDate,
                    endDate: endDate,
                    onDateChange: (dateRange) => {
                        console.log('날짜 범위 변경:', dateRange);
                        this.currentDateRange = dateRange;
                        this.updatePeriodInfo(dateRange);
                    },
                    // 새로 추가: 적용 버튼 클릭시 바로 검색 실행
                    onApply: (dateRange) => {
                        console.log('적용 버튼 클릭, 바로 검색 실행:', dateRange);
                        this.currentDateRange = dateRange;
                        this.updatePeriodInfo(dateRange);
                        this.loadDataByDateRange(dateRange);
                        // 기존 버튼 비활성화
                        this.updateActiveButton(null);
                    }
                });

                // 초기 기간 정보 설정
                this.currentDateRange = {
                    startDate: startDate,
                    endDate: endDate,
                    startDateString: startDate.toISOString().split('T')[0],
                    endDateString: endDate.toISOString().split('T')[0]
                };
                this.updatePeriodInfo(this.currentDateRange);

                // 조회 버튼 이벤트는 이제 필요없음 (적용 버튼에서 바로 검색하므로)
                // 하지만 UI에서 조회 버튼을 제거하지 않는다면 아래 코드 유지
                document.getElementById('searchButton').addEventListener('click', () => {
                    if (this.currentDateRange) {
                        this.loadDataByDateRange(this.currentDateRange);
                        // 기존 버튼 비활성화
                        this.updateActiveButton(null);
                    }
                });
            }

            // 기간 정보 업데이트 메서드 (새로 추가)
            updatePeriodInfo(dateRange) {
                const periodElement = document.getElementById('current-period');
                if (periodElement && dateRange) {
                    const start = new Date(dateRange.startDate).toLocaleDateString('ko-KR');
                    const end = new Date(dateRange.endDate).toLocaleDateString('ko-KR');
                    const dayDiff = Math.ceil((dateRange.endDate - dateRange.startDate) / (1000 * 60 * 60 * 24)) + 1;
                    periodElement.textContent = `${start} ~ ${end} (${dayDiff}일)`;
                }
            }

            // 날짜 범위로 데이터 로드 메서드 (새로 추가)
            async loadDataByDateRange(dateRange) {
                try {
                    console.log('기간별 데이터 로딩 시작:', dateRange);

                    // 로딩 상태 표시
                    this.showLoading(true);
                    this.showChartLoading('all', true);

                    // API 호출 (기간 파라미터 추가)
                    const response = await fetch(`/master/analytics/dashboard?start_date=${dateRange.startDateString}&end_date=${dateRange.endDateString}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('API 응답:', result);

                    if (result.success) {
                        // 메트릭 업데이트
                        this.updateMetrics(result.data.metrics);

                        // 각 차트를 비동기적으로 업데이트
                        await this.updateAllCharts(result.data);

                        this.showError(null);
                        console.log('기간별 데이터 로딩 완료');
                    } else {
                        console.error('데이터 로드 실패:', result.error);
                        this.showError('데이터를 불러오는데 실패했습니다: ' + result.error);
                        this.showChartError('all', result.error);
                    }
                } catch (error) {
                    console.error('API 호출 실패:', error);
                    this.showError('서버 연결에 실패했습니다: ' + error.message);
                    this.showChartError('all', error.message);
                } finally {
                    this.showLoading(false);
                }
            }

            updateDatePickerToPreset(days) {
                if (this.dateRangePicker) {
                    const endDate = new Date();
                    const startDate = new Date();
                    startDate.setDate(startDate.getDate() - (days - 1));

                    this.dateRangePicker.setDateRange(startDate, endDate);
                    this.currentDateRange = {
                        startDate: startDate,
                        endDate: endDate,
                        startDateString: startDate.toISOString().split('T')[0],
                        endDateString: endDate.toISOString().split('T')[0]
                    };
                    this.updatePeriodInfo(this.currentDateRange);
                }
            }

            updateActiveButton(activeId) {
                document.querySelectorAll('.controls button').forEach(btn => {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-secondary');
                });

                if (activeId) {
                    const activeBtn = document.getElementById(activeId);
                    if (activeBtn) {
                        activeBtn.classList.remove('btn-secondary');
                        activeBtn.classList.add('btn-primary');
                    }
                }
            }

            async loadData(days = 7) {
                try {
                    console.log(`${days}일 데이터 로딩 시작`);

                    // 로딩 상태 표시
                    this.showLoading(true);
                    this.showChartLoading('all', true);

                    // API 호출
                    const response = await fetch(`/master/analytics/dashboard?days=${days}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('API 응답:', result);

                    if (result.success) {
                        // 메트릭 업데이트
                        this.updateMetrics(result.data.metrics);

                        // 각 차트를 비동기적으로 업데이트
                        await this.updateAllCharts(result.data);

                        this.showError(null);
                        console.log('데이터 로딩 완료');
                    } else {
                        console.error('데이터 로드 실패:', result.error);
                        this.showError('데이터를 불러오는데 실패했습니다: ' + result.error);
                        this.showChartError('all', result.error);
                    }
                } catch (error) {
                    console.error('API 호출 실패:', error);
                    this.showError('서버 연결에 실패했습니다: ' + error.message);
                    this.showChartError('all', error.message);
                } finally {
                    this.showLoading(false);
                }
            }

            async updateAllCharts(data) {
                console.log('차트 업데이트 시작:', data);

                // 각 차트를 순차적으로 업데이트
                try {
                    await this.updatePagesChart(data.topPages);
                    await this.updateDateChart(data.datePages);
                    await this.updateBrowserChart(data.browserPages);
                    await this.updateEventChart(data.eventName);
                    await this.updateSourceChart(data.dailySourceMedium);
                    await this.updateCampaignChart(data.dailyCampaign);
                    await this.updateDeviceChart(data.deviceCategory);
                    await this.updateResolutionChart(data.screenResolution);
                    console.log('모든 차트 업데이트 완료');
                } catch (error) {
                    console.error('차트 업데이트 중 오류:', error);
                }
            }

            async updatePagesChart(pages) {
                try {
                    console.log('상위 페이지 차트 업데이트:', pages);
                    this.showChartLoading('pages', true);

                    if (!pages || pages.length === 0) {
                        this.showChartError('pages', '페이지 데이터가 없습니다.');
                        return;
                    }

                    const ctx = document.getElementById('pages-chart').getContext('2d');

                    if (this.charts.pages) {
                        this.charts.pages.destroy();
                    }

                    this.charts.pages = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: pages.map(p => {
                                const title = p.title || p.path || 'Unknown';
                                return title.length > 30 ? title.substring(0, 30) + '...' : title;
                            }),
                            datasets: [{
                                label: '페이지뷰',
                                data: pages.map(p => p.views || 0),
                                backgroundColor: 'rgba(52, 152, 219, 0.8)',
                                borderColor: 'rgba(52, 152, 219, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // 중요: 고정 비율 비활성화
                            layout: {
                                padding: {
                                    top: 10,
                                    bottom: 10
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            return pages[index].title || pages[index].path || 'Unknown';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return Number(value).toLocaleString();
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 45
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });

                    this.showChartLoading('pages', false);
                    document.getElementById('pages-chart').style.display = 'block';
                    console.log('상위 페이지 차트 완료');
                } catch (error) {
                    console.error('페이지 차트 오류:', error);
                    this.showChartError('pages', '페이지 차트 생성 실패: ' + error.message);
                }
            }

            async updateDateChart(dateData) {
                try {
                    console.log('날짜별 트렌드 차트 업데이트:', dateData);
                    this.showChartLoading('date', true);

                    if (!dateData || dateData.length === 0) {
                        this.showChartError('date', '날짜 데이터가 없습니다.');
                        return;
                    }

                    const ctx = document.getElementById('date-chart').getContext('2d');

                    if (this.charts.date) {
                        this.charts.date.destroy();
                    }

                    // YYYYMMDD 형식의 날짜를 파싱하는 함수
                    function parseYYYYMMDD(dateStr) {
                        if (!dateStr || typeof dateStr !== 'string') {
                            return null;
                        }

                        // YYYYMMDD 형식 확인 (8자리 숫자)
                        if (dateStr.length === 8 && /^\d{8}$/.test(dateStr)) {
                            const year = parseInt(dateStr.substring(0, 4));
                            const month = parseInt(dateStr.substring(4, 6)) - 1; // 월은 0부터 시작
                            const day = parseInt(dateStr.substring(6, 8));

                            const date = new Date(year, month, day);

                            // 유효한 날짜인지 확인
                            if (!isNaN(date.getTime()) &&
                                date.getFullYear() === year &&
                                date.getMonth() === month &&
                                date.getDate() === day) {
                                return date;
                            }
                        }

                        return null;
                    }

                    // 데이터 정제 및 검증
                    const validData = dateData.filter(d => {
                        const parsedDate = parseYYYYMMDD(d.date);
                        const hasValidMetrics = (
                            typeof d.activeUsers !== 'undefined' &&
                            typeof d.sessions !== 'undefined' &&
                            typeof d.pageViews !== 'undefined'
                        );

                        return parsedDate !== null && hasValidMetrics;
                    }).map(d => {
                        return {
                            date: d.date,
                            parsedDate: parseYYYYMMDD(d.date),
                            activeUsers: parseInt(d.activeUsers) || 0,
                            sessions: parseInt(d.sessions) || 0,
                            pageViews: parseInt(d.pageViews) || 0
                        };
                    });

                    console.log('유효한 데이터:', validData);

                    if (validData.length === 0) {
                        this.showChartError('date', '유효한 날짜 데이터가 없습니다.');
                        return;
                    }

                    // 날짜별로 중복 제거 (같은 날짜가 여러 개 있을 경우 합계 계산)
                    const dateMap = new Map();
                    validData.forEach(d => {
                        const dateKey = d.date;
                        if (dateMap.has(dateKey)) {
                            const existing = dateMap.get(dateKey);
                            dateMap.set(dateKey, {
                                date: dateKey,
                                parsedDate: d.parsedDate,
                                activeUsers: existing.activeUsers + d.activeUsers,
                                sessions: existing.sessions + d.sessions,
                                pageViews: existing.pageViews + d.pageViews
                            });
                        } else {
                            dateMap.set(dateKey, d);
                        }
                    });

                    // Map을 배열로 변환하고 날짜순 정렬
                    const uniqueData = Array.from(dateMap.values()).sort((a, b) => a.parsedDate - b.parsedDate);

                    console.log('정제된 날짜 데이터:', uniqueData);

                    // 날짜 라벨 포맷팅
                    const labels = uniqueData.map(d => {
                        return d.parsedDate.toLocaleDateString('ko-KR', {
                            month: 'short',
                            day: 'numeric',
                            weekday: 'short'
                        });
                    });

                    // 차트 생성
                    this.charts.date = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: '활성 사용자',
                                    data: uniqueData.map(d => Math.max(0, d.activeUsers)),
                                    borderColor: 'rgba(231, 76, 60, 1)',
                                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                                    tension: 0.4,
                                    fill: false,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    borderWidth: 2
                                },
                                {
                                    label: '세션',
                                    data: uniqueData.map(d => Math.max(0, d.sessions)),
                                    borderColor: 'rgba(46, 204, 113, 1)',
                                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                                    tension: 0.4,
                                    fill: false,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    borderWidth: 2
                                },
                                {
                                    label: '페이지뷰',
                                    data: uniqueData.map(d => Math.max(0, d.pageViews)),
                                    borderColor: 'rgba(52, 152, 219, 1)',
                                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                                    tension: 0.4,
                                    fill: false,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // 중요: 고정 비율 비활성화
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            const originalDate = uniqueData[index].date;
                                            const parsedDate = uniqueData[index].parsedDate;
                                            return parsedDate.toLocaleDateString('ko-KR', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                weekday: 'long'
                                            });
                                        },
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            const label = context.dataset.label;
                                            return `${label}: ${value.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return Number(value).toLocaleString();
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    },
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                }
                            }
                        }
                    });

                    this.showChartLoading('date', false);
                    document.getElementById('date-chart').style.display = 'block';
                    console.log('날짜별 트렌드 차트 완료');
                } catch (error) {
                    console.error('날짜 차트 오류:', error);
                    this.showChartError('date', '날짜 차트 생성 실패: ' + error.message);
                }
            }

// 브라우저 차트를 가로 막대로 변경
            async updateBrowserChart(browserData) {
                try {
                    console.log('브라우저별 사용자 차트 업데이트:', browserData);
                    this.showChartLoading('browser', true);

                    if (!browserData || browserData.length === 0) {
                        this.showChartError('browser', '브라우저 데이터가 없습니다.');
                        return;
                    }

                    const ctx = document.getElementById('browser-chart').getContext('2d');

                    if (this.charts.browser) {
                        this.charts.browser.destroy();
                    }

                    // 상위 브라우저만 표시 (최대 8개)
                    const topBrowsers = browserData.slice(0, 8);

                    this.charts.browser = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: topBrowsers.map(b => {
                                const browserName = b.browser || 'Unknown';
                                return browserName.length > 15 ? browserName.substring(0, 15) + '...' : browserName;
                            }),
                            datasets: [
                                {
                                    label: '활성 사용자',
                                    data: topBrowsers.map(b => b.activeUsers || 0),
                                    backgroundColor: 'rgba(52, 152, 219, 0.8)',
                                    borderColor: 'rgba(52, 152, 219, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: '세션',
                                    data: topBrowsers.map(b => b.sessions || 0),
                                    backgroundColor: 'rgba(46, 204, 113, 0.8)',
                                    borderColor: 'rgba(46, 204, 113, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: '페이지뷰',
                                    data: topBrowsers.map(b => b.pageViews || 0),
                                    backgroundColor: 'rgba(231, 76, 60, 0.8)',
                                    borderColor: 'rgba(231, 76, 60, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y', // 가로 막대
                            layout: {
                                padding: {
                                    top: 10,
                                    bottom: 10
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            return topBrowsers[index].browser || 'Unknown';
                                        },
                                        label: function(context) {
                                            const value = context.parsed.x;
                                            const label = context.dataset.label;
                                            return `${label}: ${value.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return Number(value).toLocaleString();
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });

                    this.showChartLoading('browser', false);
                    document.getElementById('browser-chart').style.display = 'block';
                    console.log('브라우저별 사용자 차트 완료');
                } catch (error) {
                    console.error('브라우저 차트 오류:', error);
                    this.showChartError('browser', '브라우저 차트 생성 실패: ' + error.message);
                }
            }

// 기기 카테고리 차트 (원형 그래프)
            async updateDeviceChart(deviceData) {
                try {
                    console.log('기기 카테고리별 사용자 차트 업데이트:', deviceData);
                    this.showChartLoading('device', true);

                    if (!deviceData || deviceData.length === 0) {
                        this.showChartError('device', '기기 카테고리 데이터가 없습니다.');
                        return;
                    }

                    const ctx = document.getElementById('device-chart').getContext('2d');

                    if (this.charts.device) {
                        this.charts.device.destroy();
                    }

                    const colors = [
                        'rgba(52, 152, 219, 0.8)',  // Desktop - 파랑
                        'rgba(46, 204, 113, 0.8)',  // Mobile - 초록
                        'rgba(231, 76, 60, 0.8)',   // Tablet - 빨강
                        'rgba(155, 89, 182, 0.8)',  // 기타 - 보라
                    ];

                    this.charts.device = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: deviceData.map(d => d.deviceCategory || 'Unknown'),
                            datasets: [{
                                data: deviceData.map(d => d.activeUsers || 0),
                                backgroundColor: colors,
                                borderColor: colors.map(color => color.replace('0.8', '1')),
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        font: {
                                            size: 14
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const index = context.dataIndex;
                                            const item = deviceData[index];
                                            const activeUsers = item.activeUsers || 0;
                                            const sessions = item.sessions || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((activeUsers / total) * 100).toFixed(1);

                                            return [
                                                `${item.deviceCategory}: ${activeUsers.toLocaleString()}명 (${percentage}%)`,
                                                `세션: ${sessions.toLocaleString()}`
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    });

                    this.showChartLoading('device', false);
                    document.getElementById('device-chart').style.display = 'block';
                    console.log('기기 카테고리별 사용자 차트 완료');
                } catch (error) {
                    console.error('기기 카테고리 차트 오류:', error);
                    this.showChartError('device', '기기 카테고리 차트 생성 실패: ' + error.message);
                }
            }

// 화면 해상도 차트 (가로 막대)
            async updateResolutionChart(resolutionData) {
                try {
                    console.log('화면 해상도별 사용자 차트 업데이트:', resolutionData);
                    this.showChartLoading('resolution', true);

                    if (!resolutionData || resolutionData.length === 0) {
                        this.showChartError('resolution', '화면 해상도 데이터가 없습니다.');
                        return;
                    }

                    const ctx = document.getElementById('resolution-chart').getContext('2d');

                    if (this.charts.resolution) {
                        this.charts.resolution.destroy();
                    }

                    // 상위 해상도만 표시 (최대 10개)
                    const topResolutions = resolutionData.slice(0, 10);

                    this.charts.resolution = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: topResolutions.map(r => {
                                const resolution = r.screenResolution || 'Unknown';
                                return resolution.length > 12 ? resolution.substring(0, 12) + '...' : resolution;
                            }),
                            datasets: [
                                {
                                    label: '활성 사용자',
                                    data: topResolutions.map(r => r.activeUsers || 0),
                                    backgroundColor: 'rgba(52, 152, 219, 0.8)',
                                    borderColor: 'rgba(52, 152, 219, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: '세션',
                                    data: topResolutions.map(r => r.sessions || 0),
                                    backgroundColor: 'rgba(46, 204, 113, 0.8)',
                                    borderColor: 'rgba(46, 204, 113, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: '페이지뷰',
                                    data: topResolutions.map(r => r.pageViews || 0),
                                    backgroundColor: 'rgba(231, 76, 60, 0.8)',
                                    borderColor: 'rgba(231, 76, 60, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y', // 가로 막대
                            layout: {
                                padding: {
                                    top: 10,
                                    bottom: 10
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            return topResolutions[index].screenResolution || 'Unknown';
                                        },
                                        label: function(context) {
                                            const value = context.parsed.x;
                                            const label = context.dataset.label;
                                            return `${label}: ${value.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return Number(value).toLocaleString();
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });

                    this.showChartLoading('resolution', false);
                    document.getElementById('resolution-chart').style.display = 'block';
                    console.log('화면 해상도별 사용자 차트 완료');
                } catch (error) {
                    console.error('화면 해상도 차트 오류:', error);
                    this.showChartError('resolution', '화면 해상도 차트 생성 실패: ' + error.message);
                }
            }

            async updateEventChart(eventData) {
                try {
                    console.log('이벤트별 데이터 차트 업데이트:', eventData);
                    this.showChartLoading('event', true);

                    if (!eventData || eventData.length === 0) {
                        this.showChartError('event', '이벤트 데이터가 없습니다.');
                        return;
                    }

                    const ctx = document.getElementById('event-chart').getContext('2d');

                    if (this.charts.event) {
                        this.charts.event.destroy();
                    }

                    // 상위 이벤트만 표시 (최대 10개)
                    const topEvents = eventData.slice(0, 10);

                    this.charts.event = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: topEvents.map(e => {
                                const eventName = e.eventName || 'Unknown'; // e.browser → e.eventName으로 변경
                                return eventName.length > 25 ? eventName.substring(0, 25) + '...' : eventName;
                            }),
                            datasets: [
                                {
                                    label: '활성 사용자',
                                    data: topEvents.map(e => e.activeUsers || 0),
                                    backgroundColor: 'rgba(52, 152, 219, 0.8)',
                                    borderColor: 'rgba(52, 152, 219, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: '세션',
                                    data: topEvents.map(e => e.sessions || 0),
                                    backgroundColor: 'rgba(46, 204, 113, 0.8)',
                                    borderColor: 'rgba(46, 204, 113, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: '페이지뷰',
                                    data: topEvents.map(e => e.pageViews || 0),
                                    backgroundColor: 'rgba(231, 76, 60, 0.8)',
                                    borderColor: 'rgba(231, 76, 60, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            layout: {
                                padding: {
                                    top: 10,
                                    bottom: 10
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            return topEvents[index].eventName || 'Unknown'; // browser → eventName으로 변경
                                        },
                                        label: function(context) {
                                            const value = context.parsed.x;
                                            const label = context.dataset.label;
                                            return `${label}: ${value.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return Number(value).toLocaleString();
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });

                    this.showChartLoading('event', false);
                    document.getElementById('event-chart').style.display = 'block';
                    console.log('이벤트별 데이터 차트 완료');
                } catch (error) {
                    console.error('이벤트 차트 오류:', error);
                    this.showChartError('event', '이벤트 차트 생성 실패: ' + error.message);
                }
            }

            // 새로운 차트 메서드 추가
            async updateSourceChart(sourceData) {
                try {
                    console.log('일자별 세션 소스/매체 차트 업데이트:', sourceData);
                    this.showChartLoading('source', true);

                    if (!sourceData || !sourceData.data || sourceData.data.length === 0) {
                        this.showChartError('source', '소스/매체 데이터가 없습니다.');
                        return;
                    }

                    const ctx = document.getElementById('source-chart').getContext('2d');

                    if (this.charts.source) {
                        this.charts.source.destroy();
                    }

                    const { data: dailyData, topSources } = sourceData;

                    // YYYYMMDD 형식의 날짜를 파싱하는 함수
                    function parseYYYYMMDD(dateStr) {
                        if (!dateStr || typeof dateStr !== 'string') {
                            return null;
                        }

                        if (dateStr.length === 8 && /^\d{8}$/.test(dateStr)) {
                            const year = parseInt(dateStr.substring(0, 4));
                            const month = parseInt(dateStr.substring(4, 6)) - 1;
                            const day = parseInt(dateStr.substring(6, 8));
                            return new Date(year, month, day);
                        }
                        return null;
                    }

                    // 날짜 라벨 생성
                    const labels = dailyData.map(d => {
                        const parsedDate = parseYYYYMMDD(d.date);
                        if (parsedDate) {
                            return parsedDate.toLocaleDateString('ko-KR', {
                                month: 'short',
                                day: 'numeric',
                                weekday: 'short'
                            });
                        }
                        return d.date;
                    });

                    // 색상 팔레트
                    const colors = [
                        'rgba(52, 152, 219, 1)',   // 파랑
                        'rgba(231, 76, 60, 1)',    // 빨강
                        'rgba(46, 204, 113, 1)',   // 초록
                        'rgba(155, 89, 182, 1)',   // 보라
                        'rgba(241, 196, 15, 1)',   // 노랑
                        'rgba(230, 126, 34, 1)',   // 주황
                        'rgba(149, 165, 166, 1)',  // 회색
                        'rgba(26, 188, 156, 1)'    // 청록
                    ];

                    // 데이터셋 생성
                    const datasets = topSources.map((sourceMedium, index) => {
                        const color = colors[index % colors.length];
                        return {
                            label: sourceMedium.length > 20 ? sourceMedium.substring(0, 20) + '...' : sourceMedium,
                            data: dailyData.map(d => d[sourceMedium] || 0),
                            borderColor: color,
                            backgroundColor: color.replace('1)', '0.1)'),
                            tension: 0.4,
                            fill: false,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            borderWidth: 2
                        };
                    });

                    this.charts.source = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            const originalDate = dailyData[index].date;
                                            const parsedDate = parseYYYYMMDD(originalDate);
                                            if (parsedDate) {
                                                return parsedDate.toLocaleDateString('ko-KR', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                    weekday: 'long'
                                                });
                                            }
                                            return originalDate;
                                        },
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            const label = context.dataset.label;
                                            return `${label}: ${value.toLocaleString()} 세션`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return Number(value).toLocaleString();
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    },
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                }
                            }
                        }
                    });

                    this.showChartLoading('source', false);
                    document.getElementById('source-chart').style.display = 'block';
                    console.log('일자별 세션 소스/매체 차트 완료');
                } catch (error) {
                    console.error('소스/매체 차트 오류:', error);
                    this.showChartError('source', '소스/매체 차트 생성 실패: ' + error.message);
                }
            }

            async updateCampaignChart(campaignData) {
                try {
                    console.log('일자별 세션 캠페인 차트 업데이트:', campaignData);
                    this.showChartLoading('campaign', true);

                    if (!campaignData || !campaignData.data || campaignData.data.length === 0) {
                        this.showChartError('campaign', '캠페인 데이터가 없습니다.');
                        return;
                    }

                    const ctx = document.getElementById('campaign-chart').getContext('2d');

                    if (this.charts.campaign) {
                        this.charts.campaign.destroy();
                    }

                    const { data: dailyData, topCampaigns } = campaignData;

                    // YYYYMMDD 형식의 날짜를 파싱하는 함수
                    function parseYYYYMMDD(dateStr) {
                        if (!dateStr || typeof dateStr !== 'string') {
                            return null;
                        }

                        if (dateStr.length === 8 && /^\d{8}$/.test(dateStr)) {
                            const year = parseInt(dateStr.substring(0, 4));
                            const month = parseInt(dateStr.substring(4, 6)) - 1;
                            const day = parseInt(dateStr.substring(6, 8));
                            return new Date(year, month, day);
                        }
                        return null;
                    }

                    // 날짜 라벨 생성
                    const labels = dailyData.map(d => {
                        const parsedDate = parseYYYYMMDD(d.date);
                        if (parsedDate) {
                            return parsedDate.toLocaleDateString('ko-KR', {
                                month: 'short',
                                day: 'numeric',
                                weekday: 'short'
                            });
                        }
                        return d.date;
                    });

                    // 색상 팔레트 (캠페인용)
                    const colors = [
                        'rgba(255, 99, 132, 1)',   // 핑크
                        'rgba(54, 162, 235, 1)',   // 파랑
                        'rgba(255, 205, 86, 1)',   // 노랑
                        'rgba(75, 192, 192, 1)',   // 청록
                        'rgba(153, 102, 255, 1)',  // 보라
                        'rgba(255, 159, 64, 1)',   // 주황
                        'rgba(199, 199, 199, 1)',  // 회색
                        'rgba(83, 102, 255, 1)'    // 인디고
                    ];

                    // 데이터셋 생성
                    const datasets = topCampaigns.map((campaign, index) => {
                        const color = colors[index % colors.length];
                        return {
                            label: campaign.length > 25 ? campaign.substring(0, 25) + '...' : campaign,
                            data: dailyData.map(d => d[campaign] || 0),
                            borderColor: color,
                            backgroundColor: color.replace('1)', '0.1)'),
                            tension: 0.4,
                            fill: false,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 3
                        };
                    });

                    this.charts.campaign = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        title: function(context) {
                                            const index = context[0].dataIndex;
                                            const originalDate = dailyData[index].date;
                                            const parsedDate = parseYYYYMMDD(originalDate);
                                            if (parsedDate) {
                                                return parsedDate.toLocaleDateString('ko-KR', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                    weekday: 'long'
                                                });
                                            }
                                            return originalDate;
                                        },
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            const label = context.dataset.label;
                                            return `${label}: ${value.toLocaleString()} 세션`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return Number(value).toLocaleString();
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    },
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                }
                            }
                        }
                    });

                    this.showChartLoading('campaign', false);
                    document.getElementById('campaign-chart').style.display = 'block';
                    console.log('일자별 세션 캠페인 차트 완료');
                } catch (error) {
                    console.error('캠페인 차트 오류:', error);
                    this.showChartError('campaign', '캠페인 차트 생성 실패: ' + error.message);
                }
            }

            async loadRealtimeData() {
                try {
                    const response = await fetch('/master/analytics/realtime', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.success) {
                        document.getElementById('realtime-users').textContent =
                            result.data.activeUsers.toLocaleString();
                    }
                } catch (error) {
                    console.error('실시간 데이터 로드 실패:', error);
                    document.getElementById('realtime-users').textContent = '-';
                }
            }

            updateMetrics(metrics) {
                if (!metrics) {
                    this.showError('메트릭 데이터가 없습니다.');
                    return;
                }

                const elements = {
                    'active-users': metrics.activeUsers,
                    'sessions': metrics.sessions,
                    'page-views': metrics.pageViews,
                    'bounce-rate': metrics.bounceRate
                };

                Object.entries(elements).forEach(([id, value]) => {
                    const element = document.getElementById(id);
                    if (element) {
                        if (id === 'bounce-rate') {
                            element.textContent = value ? `${value}%` : '-';
                        } else {
                            element.textContent = value ? value.toLocaleString() : '-';
                        }
                    }
                });

                console.log('메트릭 업데이트 완료:', metrics);
            }

            showChartLoading(chartType, show) {
                const charts = chartType === 'all' ? ['pages', 'date', 'browser', 'event', 'source', 'campaign', 'device', 'resolution'] : [chartType];

                charts.forEach(type => {
                    const loadingElement = document.getElementById(`${type}-loading`);
                    const chartElement = document.getElementById(`${type}-chart`);
                    const errorElement = document.getElementById(`${type}-error`);

                    if (loadingElement) {
                        loadingElement.style.display = show ? 'block' : 'none';
                    }
                    if (chartElement && !show) {
                        chartElement.style.display = 'none';
                    }
                    if (errorElement) {
                        errorElement.style.display = 'none';
                    }
                });
            }

            showChartError(chartType, message) {
                const charts = chartType === 'all' ? ['pages', 'date', 'browser', 'event', 'source', 'campaign', 'device', 'resolution'] : [chartType];

                charts.forEach(type => {
                    const loadingElement = document.getElementById(`${type}-loading`);
                    const chartElement = document.getElementById(`${type}-chart`);
                    const errorElement = document.getElementById(`${type}-error`);

                    if (loadingElement) {
                        loadingElement.style.display = 'none';
                    }
                    if (chartElement) {
                        chartElement.style.display = 'none';
                    }
                    if (errorElement) {
                        errorElement.textContent = message;
                        errorElement.style.display = 'block';
                    }
                });
            }

            showLoading(show) {
                const buttons = document.querySelectorAll('.controls button');
                const searchButton = document.getElementById('searchButton');
                const searchText = searchButton.querySelector('.search-text');
                const searchLoading = searchButton.querySelector('.search-loading');

                buttons.forEach(btn => {
                    btn.disabled = show;
                    if (show) {
                        btn.textContent = '로딩 중...';
                    } else {
                        btn.textContent = btn.id === 'btn-7days' ? '최근 7일' : '최근 30일';
                    }
                });

                // 검색 버튼 로딩 상태
                searchButton.disabled = show;
                if (show) {
                    searchText.style.display = 'none';
                    searchLoading.style.display = 'inline';
                } else {
                    searchText.style.display = 'inline';
                    searchLoading.style.display = 'none';
                }
            }

            showError(message) {
                // 기존 에러 메시지 제거
                const existingError = document.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }

                if (message) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.style.cssText = `
                        background: #f8d7da;
                        color: #721c24;
                        padding: 15px;
                        border: 1px solid #f5c6cb;
                        border-radius: 5px;
                        margin: 20px 0;
                        text-align: center;
                        font-weight: 500;
                    `;
                    errorDiv.textContent = message;

                    const container = document.querySelector('.container');
                    const realtimeCard = document.querySelector('.realtime-card');
                    if (container && realtimeCard) {
                        container.insertBefore(errorDiv, realtimeCard);
                    }
                }
            }
        }

        // 전역 스코프에 함수 등록
        window.loadData = function(days) {
            if (window.dashboardInstance) {
                window.dashboardInstance.loadData(days);
            }
        };

        // 페이지 로드 완료 후 대시보드 초기화
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM 로드 완료, Dashboard 인스턴스 생성');
            window.dashboardInstance = new Dashboard();
        });

        // 페이지 언로드 시 차트 정리
        window.addEventListener('beforeunload', () => {
            if (window.dashboardInstance && window.dashboardInstance.charts) {
                Object.values(window.dashboardInstance.charts).forEach(chart => {
                    if (chart) {
                        chart.destroy();
                    }
                });
            }
        });
    </script>
@stop
