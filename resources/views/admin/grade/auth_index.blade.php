@extends('admin.layout.master')

@section('required-page-title', '관리자 권한 설정')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/userManagement/gradeNameSetting.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title">
                <h2 class="title">관리자 권한 설정</h2>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 메뉴 데이터를 hidden input에 저장 -->
            <input type="hidden" id="menus-data" data-menus='@json($origin_menus)'>
            <!-- 컨텐츠 S -->
            <div class="container">
                <form id="permissions-form" class="max_width">
                    <div class="content_title_box no_mg">
                        <h3 class="title">권한 설정</h3>
                    </div>
                    <div class="input_box">
                        <div class="input_item half">
                            <label class="input_title">권한</label>
                            <div class="inner_box">
                                <div class="custom_select_1 js_custom_select select_type">
                                    <input type="text" id='select_text' class="common_input select_value" placeholder="선택" data-value="" readonly>
                                    <ul role="list">
                                        @foreach($adminGrades as $grade)
                                            <li role="listitem" data-value="{{ $grade->code }}">{{ $grade->code_name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 권한 테이블 영역 -->
                    <div id="permission_table_body">
                        <!-- 여기에 권한 테이블이 동적으로 생성됩니다 -->
                    </div>

                    <!-- 하단 버튼 S -->
                    <div class="common_bottom_btn fixed">
                        <button type="button" class="border_btn cancel btn-cancel">
                            <span>취소</span>
                        </button>
                        <button type="button" class="border_btn save btn-submit" id="mainForm_submit">
                            <span>저장</span>
                        </button>
                    </div>
                    <!-- 하단 버튼 E -->
                </form>
            </div>
        </div>
    </main>
@endsection

@section('required-page-add-content')
    <!-- 순수 자바스크립트 코드 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 메뉴 데이터
            const menusData = document.getElementById('menus-data');
            const menus = menusData ? JSON.parse(menusData.dataset.menus) : @json($origin_menus);

            let currentGradeId = null;
            let permissions = {};
            let permissionByMenuId = {}; // 메뉴 ID를 키로 하는 권한 맵

            // 요소 참조
            const permissionTableBody = document.getElementById('permission_table_body');
            const submitButton = document.querySelector('.btn-submit');
            const cancelButton = document.querySelector('.btn-cancel');

            // URL에서 grade_id 파라미터 확인
            function getGradeIdFromURL() {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get('grade_id');
            }

            const $selectBox = document.querySelector('.js_custom_select.select_type');
            const $selectList = $selectBox.querySelectorAll('li');
            $selectList.forEach(item => {
                item.addEventListener('click', (e)=> {
                    const gradeId = e.target.dataset.value;
                    updateURLParameter('grade_id', gradeId);
                    getGradeSelect(gradeId);
                });
            });

            // URL 파라미터 업데이트 함수
            function updateURLParameter(key, value) {
                const url = new URL(window.location.href);
                url.searchParams.set(key, value);
                window.history.replaceState({}, '', url.toString());
            }

            // 취소 버튼 클릭 이벤트
            cancelButton.addEventListener('click', function(e) {
                e.preventDefault();
                location.reload();
            });

            // 저장 버튼 클릭 이벤트
            submitButton.addEventListener('click', function(e) {
                e.preventDefault();
                savePermissions();
            });

            /**
             * 등급에 따른 권한 정보 가져오기
             * @param {string|number} gradeId - 등급 ID
             */
            function getGradeSelect(gradeId) {
                currentGradeId = gradeId;

                // Fetch API를 사용한 AJAX 요청
                fetch('{{ route("auth.getPermissions", "") }}/' + currentGradeId, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('서버 응답이 정상적이지 않습니다.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('서버 응답 데이터:', data);

                        if (data.success) {
                            permissions = data.data || {};

                            // 메뉴 ID 기반 권한 맵 생성
                            permissionByMenuId = {};

                            // 서버 응답 분석 및 메뉴 ID 기반 권한 맵 생성
                            for (const key in permissions) {
                                const permItem = permissions[key];
                                // 숫자 키를 가진 항목만 처리 (메뉴 ID)
                                if (!isNaN(parseInt(key))) {
                                    permissionByMenuId[key] = permItem;
                                }
                            }

                            console.log('메뉴 ID 기반 권한 맵:', permissionByMenuId);

                            renderPermissionTable();

                            // permission_container가 있다면 표시
                            const permissionContainer = document.getElementById('permission_container');
                            if (permissionContainer) {
                                permissionContainer.style.display = 'block';
                            }
                        } else {
                            alert(data.message || '권한을 불러오는 중 오류가 발생했습니다.');
                        }
                    })
                    .catch(error => {
                        console.error('권한 로드 오류:', error);
                        alert('서버 통신 중 오류가 발생했습니다: ' + error.message);
                    });
            }

            /**
             * 권한 테이블 렌더링
             */
            function renderPermissionTable() {
                // 테이블 내용 초기화
                permissionTableBody.innerHTML = '';

                // 각 메뉴에 대해 처리
                menus.forEach((menu, menuIndex) => {
                    // 자식 메뉴가 있는지 확인
                    const childCount = menu.children ? menu.children.length : 0;

                    if (childCount > 0) {
                        // 각 상위 메뉴별 테이블 컨테이너 생성
                        const tableContainer = document.createElement('div');
                        tableContainer.classList.add('common_table', 'grade_bottom', 'setting');

                        // 테이블 생성
                        const table = document.createElement('table');
                        table.classList.add('grade_table');

                        // 테이블 헤더 생성
                        const thead = document.createElement('thead');
                        const headerRow = document.createElement('tr');

                        // 메뉴명 열
                        const menuHeader = document.createElement('th');
                        menuHeader.setAttribute('colspan', '2');
                        menuHeader.textContent = '메뉴명';
                        headerRow.appendChild(menuHeader);

                        // 읽기 권한 열
                        const readHeader = document.createElement('th');
                        readHeader.innerHTML = `
                    <label class="chk_input">
                        <input type="checkbox" name="read_${menuIndex}" id="all_read_${menuIndex}">
                        <span>Read<br>(읽기)</span>
                    </label>
                `;
                        headerRow.appendChild(readHeader);

                        // 수정 권한 열
                        const updateHeader = document.createElement('th');
                        updateHeader.innerHTML = `
                    <label class="chk_input">
                        <input type="checkbox" name="update_${menuIndex}" id="all_update_${menuIndex}">
                        <span>Update<br>(수정, 등록)</span>
                    </label>
                `;
                        headerRow.appendChild(updateHeader);

                        // 삭제 권한 열
                        const deleteHeader = document.createElement('th');
                        deleteHeader.innerHTML = `
                    <label class="chk_input">
                        <input type="checkbox" name="delete_${menuIndex}" id="all_delete_${menuIndex}">
                        <span>Delete<br>(삭제)</span>
                    </label>
                `;
                        headerRow.appendChild(deleteHeader);

                        thead.appendChild(headerRow);
                        table.appendChild(thead);

                        // 테이블 바디 생성
                        const tbody = document.createElement('tbody');
                        const outerRow = document.createElement('tr');
                        const outerCell = document.createElement('td');
                        outerCell.setAttribute('colspan', '5');

                        // 내부 테이블 생성
                        const innerTable = document.createElement('table');
                        const innerTbody = document.createElement('tbody');

                        // 첫 번째 행 생성 (상위 메뉴명과 첫 번째 하위 메뉴)
                        const firstRow = document.createElement('tr');
                        firstRow.classList.add('menu-row');

                        // 상위 메뉴 셀
                        const parentCell = document.createElement('td');
                        parentCell.classList.add('depth_1');
                        parentCell.setAttribute('rowspan', childCount);
                        parentCell.innerHTML = `
                    <div>
                        <span class="icon">
                            <img src="/src/assets/icons/${menu.icon}" alt="${menu.name} 아이콘">
                        </span>
                        ${menu.name}
                    </div>
                `;
                        firstRow.appendChild(parentCell);

                        if (menu.children.length > 0) {
                            // 첫 번째 하위 메뉴 처리
                            const firstChild = menu.children[0];
                            const firstChildPath = firstChild.link || '';
                            const firstChildId = firstChild.code || '';

                            firstRow.setAttribute('data-path', firstChildPath);
                            if (firstChildId) {
                                firstRow.setAttribute('data-menu-id', firstChildId);
                            }

                            // 하위 메뉴명 셀
                            const childNameCell = document.createElement('td');
                            childNameCell.classList.add('depth_2');
                            childNameCell.innerHTML = `<div>${firstChild.name}</div>`;
                            firstRow.appendChild(childNameCell);

                            // 권한 데이터 가져오기
                            let menuPerms = { read: 0, write: 0, delete: 0 };

                            // 메뉴 ID 또는 경로로 권한 매핑 시도
                            if (firstChildId && permissionByMenuId[firstChildId]) {
                                menuPerms = permissionByMenuId[firstChildId];
                            } else if (firstChildPath && permissions[firstChildPath]) {
                                menuPerms = permissions[firstChildPath];
                            } else if (firstChildPath && permissions['/' + firstChildPath]) {
                                menuPerms = permissions['/' + firstChildPath];
                            }

                            // 읽기 권한 체크박스
                            const readCell = document.createElement('td');
                            readCell.innerHTML = `
                        <label class="chk_input">
                            <input type="checkbox" name="read_${menuIndex}" class="permission-read" ${menuPerms.read == 1 ? 'checked' : ''} data-menu-index="${menuIndex}">
                            <span></span>
                        </label>
                    `;
                            firstRow.appendChild(readCell);

                            // 수정 권한 체크박스
                            const writeCell = document.createElement('td');
                            writeCell.innerHTML = `
                        <label class="chk_input">
                            <input type="checkbox" name="update_${menuIndex}" class="permission-write" ${menuPerms.write == 1 ? 'checked' : ''} data-menu-index="${menuIndex}">
                            <span></span>
                        </label>
                    `;
                            firstRow.appendChild(writeCell);

                            // 삭제 권한 체크박스
                            const deleteCell = document.createElement('td');
                            deleteCell.innerHTML = `
                        <label class="chk_input">
                            <input type="checkbox" name="delete_${menuIndex}" class="permission-delete" ${menuPerms.delete == 1 ? 'checked' : ''} data-menu-index="${menuIndex}">
                            <span></span>
                        </label>
                    `;
                            firstRow.appendChild(deleteCell);

                            innerTbody.appendChild(firstRow);

                            // 나머지 하위 메뉴 처리
                            for (let i = 1; i < menu.children.length; i++) {
                                const subMenu = menu.children[i];
                                const subMenuPath = subMenu.link || '';
                                const subMenuId = subMenu.code || '';

                                const subRow = document.createElement('tr');
                                subRow.classList.add('menu-row');
                                subRow.setAttribute('data-path', subMenuPath);
                                if (subMenuId) {
                                    subRow.setAttribute('data-menu-id', subMenuId);
                                }

                                // 하위 메뉴 셀 (colspan=2로 설정)
                                const subMenuCell = document.createElement('td');
                                subMenuCell.classList.add('depth_2');
                                //subMenuCell.setAttribute('colspan', '2');
                                subMenuCell.innerHTML = `<div>${subMenu.name}</div>`;
                                subRow.appendChild(subMenuCell);

                                // 권한 데이터 가져오기
                                let subMenuPerms = { read: 0, write: 0, delete: 0 };

                                // 메뉴 ID 또는 경로로 권한 매핑 시도
                                if (subMenuId && permissionByMenuId[subMenuId]) {
                                    subMenuPerms = permissionByMenuId[subMenuId];
                                } else if (subMenuPath && permissions[subMenuPath]) {
                                    subMenuPerms = permissions[subMenuPath];
                                } else if (subMenuPath && permissions['/' + subMenuPath]) {
                                    subMenuPerms = permissions['/' + subMenuPath];
                                }

                                // 읽기 권한 체크박스
                                const subReadCell = document.createElement('td');
                                subReadCell.innerHTML = `
                            <label class="chk_input">
                                <input type="checkbox" name="read_${menuIndex}" class="permission-read" ${subMenuPerms.read == 1 ? 'checked' : ''} data-menu-index="${menuIndex}">
                                <span></span>
                            </label>
                        `;
                                subRow.appendChild(subReadCell);

                                // 수정 권한 체크박스
                                const subWriteCell = document.createElement('td');
                                subWriteCell.innerHTML = `
                            <label class="chk_input">
                                <input type="checkbox" name="update_${menuIndex}" class="permission-write" ${subMenuPerms.write == 1 ? 'checked' : ''} data-menu-index="${menuIndex}">
                                <span></span>
                            </label>
                        `;
                                subRow.appendChild(subWriteCell);

                                // 삭제 권한 체크박스
                                const subDeleteCell = document.createElement('td');
                                subDeleteCell.innerHTML = `
                            <label class="chk_input">
                                <input type="checkbox" name="delete_${menuIndex}" class="permission-delete" ${subMenuPerms.delete == 1 ? 'checked' : ''} data-menu-index="${menuIndex}">
                                <span></span>
                            </label>
                        `;
                                subRow.appendChild(subDeleteCell);

                                innerTbody.appendChild(subRow);
                            }
                        }

                        innerTable.appendChild(innerTbody);
                        outerCell.appendChild(innerTable);
                        outerRow.appendChild(outerCell);
                        tbody.appendChild(outerRow);
                        table.appendChild(tbody);
                        tableContainer.appendChild(table);
                        permissionTableBody.appendChild(tableContainer);

                        // 이벤트 연결
                        setupCheckboxEvents(menuIndex);
                    }
                });
            }

            /**
             * 특정 메뉴의 체크박스 이벤트 설정
             * @param {number} menuIndex - 메뉴 인덱스
             */
            function setupCheckboxEvents(menuIndex) {
                // 전체 체크박스
                const allReadCheckbox = document.getElementById(`all_read_${menuIndex}`);
                const allUpdateCheckbox = document.getElementById(`all_update_${menuIndex}`);
                const allDeleteCheckbox = document.getElementById(`all_delete_${menuIndex}`);

                // 권한별 체크박스
                const readCheckboxes = document.querySelectorAll(`input[name="read_${menuIndex}"].permission-read`);
                const updateCheckboxes = document.querySelectorAll(`input[name="update_${menuIndex}"].permission-write`);
                const deleteCheckboxes = document.querySelectorAll(`input[name="delete_${menuIndex}"].permission-delete`);

                // 초기 헤더 체크박스 상태 설정
                if (allReadCheckbox && readCheckboxes.length > 0) {
                    const allReadChecked = Array.from(readCheckboxes).every(checkbox => checkbox.checked);
                    allReadCheckbox.checked = allReadChecked;
                }

                if (allUpdateCheckbox && updateCheckboxes.length > 0) {
                    const allUpdateChecked = Array.from(updateCheckboxes).every(checkbox => checkbox.checked);
                    allUpdateCheckbox.checked = allUpdateChecked;
                }

                if (allDeleteCheckbox && deleteCheckboxes.length > 0) {
                    const allDeleteChecked = Array.from(deleteCheckboxes).every(checkbox => checkbox.checked);
                    allDeleteCheckbox.checked = allDeleteChecked;
                }

                // 전체 읽기 체크박스 이벤트
                if (allReadCheckbox) {
                    allReadCheckbox.addEventListener('change', function() {
                        const isChecked = this.checked;

                        readCheckboxes.forEach(checkbox => {
                            checkbox.checked = isChecked;

                            // 읽기 권한이 해제되면 수정/삭제 권한도 해제
                            if (!isChecked) {
                                const row = checkbox.closest('tr');
                                if (row) {
                                    const writeCheckbox = row.querySelector(`.permission-write[name="update_${menuIndex}"]`);
                                    const deleteCheckbox = row.querySelector(`.permission-delete[name="delete_${menuIndex}"]`);

                                    if (writeCheckbox) writeCheckbox.checked = false;
                                    if (deleteCheckbox) deleteCheckbox.checked = false;
                                }
                            }
                        });

                        // 해당 메뉴의 모든 체크박스 업데이트
                        updateMenuCheckboxes(menuIndex);
                    });
                }

                // 전체 수정 체크박스 이벤트
                if (allUpdateCheckbox) {
                    allUpdateCheckbox.addEventListener('change', function() {
                        const isChecked = this.checked;

                        updateCheckboxes.forEach(checkbox => {
                            checkbox.checked = isChecked;

                            // 수정 권한이 체크되면 읽기 권한도 체크
                            if (isChecked) {
                                const row = checkbox.closest('tr');
                                if (row) {
                                    const readCheckbox = row.querySelector(`.permission-read[name="read_${menuIndex}"]`);
                                    if (readCheckbox) readCheckbox.checked = true;
                                }
                            }
                        });

                        // 해당 메뉴의 모든 체크박스 업데이트
                        updateMenuCheckboxes(menuIndex);
                    });
                }

                // 전체 삭제 체크박스 이벤트
                if (allDeleteCheckbox) {
                    allDeleteCheckbox.addEventListener('change', function() {
                        const isChecked = this.checked;

                        deleteCheckboxes.forEach(checkbox => {
                            checkbox.checked = isChecked;

                            // 삭제 권한이 체크되면 읽기 권한도 체크
                            if (isChecked) {
                                const row = checkbox.closest('tr');
                                if (row) {
                                    const readCheckbox = row.querySelector(`.permission-read[name="read_${menuIndex}"]`);
                                    if (readCheckbox) readCheckbox.checked = true;
                                }
                            }
                        });

                        // 해당 메뉴의 모든 체크박스 업데이트
                        updateMenuCheckboxes(menuIndex);
                    });
                }

                // 개별 체크박스 이벤트
                readCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const isChecked = this.checked;
                        const row = this.closest('tr');

                        // 읽기 권한이 해제되면 수정/삭제 권한도 해제
                        if (!isChecked && row) {
                            const writeCheckbox = row.querySelector(`.permission-write[name="update_${menuIndex}"]`);
                            const deleteCheckbox = row.querySelector(`.permission-delete[name="delete_${menuIndex}"]`);

                            if (writeCheckbox) writeCheckbox.checked = false;
                            if (deleteCheckbox) deleteCheckbox.checked = false;
                        }

                        // 해당 메뉴의 모든 체크박스 업데이트
                        updateMenuCheckboxes(menuIndex);
                    });
                });

                updateCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const isChecked = this.checked;
                        const row = this.closest('tr');

                        // 수정 권한이 체크되면 읽기 권한도 체크
                        if (isChecked && row) {
                            const readCheckbox = row.querySelector(`.permission-read[name="read_${menuIndex}"]`);
                            if (readCheckbox) readCheckbox.checked = true;
                        }

                        // 해당 메뉴의 모든 체크박스 업데이트
                        updateMenuCheckboxes(menuIndex);
                    });
                });

                deleteCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const isChecked = this.checked;
                        const row = this.closest('tr');

                        // 삭제 권한이 체크되면 읽기 권한도 체크
                        if (isChecked && row) {
                            const readCheckbox = row.querySelector(`.permission-read[name="read_${menuIndex}"]`);
                            if (readCheckbox) readCheckbox.checked = true;
                        }

                        // 해당 메뉴의 모든 체크박스 업데이트
                        updateMenuCheckboxes(menuIndex);
                    });
                });
            }

            /**
             * 특정 메뉴의 헤더 체크박스 상태 업데이트
             * @param {number} menuIndex - 메뉴 인덱스
             */
            function updateMenuCheckboxes(menuIndex) {
                const readCheckboxes = document.querySelectorAll(`input[name="read_${menuIndex}"].permission-read`);
                const updateCheckboxes = document.querySelectorAll(`input[name="update_${menuIndex}"].permission-write`);
                const deleteCheckboxes = document.querySelectorAll(`input[name="delete_${menuIndex}"].permission-delete`);

                const allReadCheckbox = document.getElementById(`all_read_${menuIndex}`);
                const allUpdateCheckbox = document.getElementById(`all_update_${menuIndex}`);
                const allDeleteCheckbox = document.getElementById(`all_delete_${menuIndex}`);

                if (allReadCheckbox && readCheckboxes.length > 0) {
                    const allReadChecked = Array.from(readCheckboxes).every(checkbox => checkbox.checked);
                    allReadCheckbox.checked = allReadChecked;
                }

                if (allUpdateCheckbox && updateCheckboxes.length > 0) {
                    const allUpdateChecked = Array.from(updateCheckboxes).every(checkbox => checkbox.checked);
                    allUpdateCheckbox.checked = allUpdateChecked;
                }

                if (allDeleteCheckbox && deleteCheckboxes.length > 0) {
                    const allDeleteChecked = Array.from(deleteCheckboxes).every(checkbox => checkbox.checked);
                    allDeleteCheckbox.checked = allDeleteChecked;
                }
            }

            /**
             * 권한 저장
             */
            function savePermissions() {
                if (!currentGradeId) {
                    alert('등급을 선택해주세요.');
                    return;
                }

                // 권한 값 유효성 검사
                const invalidPermissions = validatePermissions();
                if (invalidPermissions.length > 0) {
                    alert('읽기 권한이 없는 메뉴에 수정 또는 삭제 권한이 설정되어 있습니다.\n권한을 확인해주세요.');
                    return;
                }

                const permissionData = {};
                const menuRows = document.querySelectorAll('.menu-row');

                menuRows.forEach(row => {
                    // 메뉴 ID와 경로 가져오기
                    const menuId = row.getAttribute('data-menu-id');
                    const menuPath = row.getAttribute('data-path');

                    // 메뉴 ID가 있으면 ID를 키로 사용, 없으면 경로 사용
                    const key = menuId || menuPath;

                    if (key) {
                        permissionData[key] = {
                            path: menuPath,
                            read: row.querySelector('.permission-read').checked ? 1 : 0,
                            write: row.querySelector('.permission-write').checked ? 1 : 0,
                            delete: row.querySelector('.permission-delete').checked ? 1 : 0
                        };
                    }
                });
                disable_button(mainForm_submit);

                // 권한 업데이트 AJAX 요청
                fetch('{{ route("auth.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        grade_id: currentGradeId,
                        permissions: permissionData
                    })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('서버 응답이 정상적이지 않습니다.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message || '권한이 성공적으로 업데이트되었습니다.');
                        } else {
                            alert(data.message || '권한 업데이트 중 오류가 발생했습니다.');
                        }
                        restore_button(mainForm_submit);
                    })
                    .catch(error => {
                        restore_button(mainForm_submit);
                        console.error('권한 저장 오류:', error);
                        alert('서버 통신 중 오류가 발생했습니다: ' + error.message);
                    });
            }

            /**
             * 권한 설정 유효성 검사
             * 읽기 권한이 없는데 수정/삭제 권한이 있는 메뉴가 있는지 확인
             * @returns {Array} - 유효하지 않은 메뉴 목록
             */
            function validatePermissions() {
                const invalidMenus = [];
                const menuRows = document.querySelectorAll('.menu-row');

                menuRows.forEach(row => {
                    const menuPath = row.getAttribute('data-path');
                    const readCheckbox = row.querySelector('.permission-read');
                    const writeCheckbox = row.querySelector('.permission-write');
                    const deleteCheckbox = row.querySelector('.permission-delete');

                    // 읽기 권한이 없는데 수정 또는 삭제 권한이 있는 경우
                    if (readCheckbox && !readCheckbox.checked) {
                        if ((writeCheckbox && writeCheckbox.checked) ||
                            (deleteCheckbox && deleteCheckbox.checked)) {
                            invalidMenus.push(menuPath);
                        }
                    }
                });

                return invalidMenus;
            }

            /**
             * 초기화 함수
             * URL 파라미터 또는 기본값을 사용하여 등급 선택
             */
            function initialize() {
                // URL에서 grade_id 파라미터 확인
                const urlGradeId = getGradeIdFromURL();

                if (urlGradeId) {
                    const targetLi = Array.from($selectList).find(li => li.dataset.value === urlGradeId);
                    if (targetLi) {
                        document.getElementById('select_text').value = targetLi.textContent;
                        // 권한 정보 로드
                        getGradeSelect(urlGradeId);
                    } else {
                        // 일치하는 요소가 없을 경우 첫 번째 옵션 사용
                        const firstLi = $selectList[0];
                        const defaultGradeId = firstLi.dataset.value;
                        document.getElementById('select_text').value = firstLi.textContent;
                        getGradeSelect(defaultGradeId);
                    }
                } else {
                    // URL에 grade_id가 없으면 기본값 사용
                    const firstLi = $selectList[0];
                    const defaultGradeId = firstLi.dataset.value;
                    document.getElementById('select_text').value = firstLi.textContent;
                    // 권한 정보 로드
                    getGradeSelect(defaultGradeId);
                }
            }

            // 페이지 로드 시 초기화
            initialize();
        });

        function disable_button(element){
            element.disabled = true;
            element.classList.add('loading');
        }

        function restore_button(element){
            element.disabled = false;
            element.classList.remove('loading');
        }
    </script>
@endsection
