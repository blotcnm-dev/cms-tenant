@extends('admin.layout.master')

@section('required-page-title', '사이트메뉴 관리')

@section('required-page-header-css')
    <link rel="stylesheet" href="/src/style/siteManagement/menuManagement.css">
@stop

@section('required-page-header-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
@stop

@section('required-page-main-content')
    <main>
        <div id="wrap" class="white">
            <!-- 페이지 타이틀 S -->
            <div class="page_title with_btn">
                <h2 class="title">메뉴 설정</h2>
                <button type="button" class="fill_btn blue plus posi_right">
                    <span>메뉴 추가</span>
                </button>
            </div>
            <!-- 페이지 타이틀 E -->

            <!-- 컨텐츠 S -->
            <div class="container">
                <div class="menu_management">
                    <div class="common_table">
                        <div class="thead">
                            <div class="menu_name">메뉴 구조</div>
                            <div class="en">영문명</div>
                            <div class="path">경로</div>
                            <div class="info">설명</div>
                            <div class="use">사용여부</div>
                        </div>
                        <ul id="menu_container" class="menu"></ul>
                    </div>
                </div>

                <!-- 하단 버튼 S -->
                <div class="common_bottom_btn fixed">
                    <a href="{{route('menus.index')}}" class="border_btn cancel">
                        <span>취소</span>
                    </a>
                    <button  class="border_btn save"  id="mainForm_submit">
                        <span>저장</span>
                    </button>
                </div>
                <!-- 하단 버튼 E -->

            </div>
            <!-- 컨텐츠 E -->
        </div>
    </main>
@stop

@section('required-page-add-content')
    <script type="module">
        // import { gnbHandler } from "/src/js/navigation/gnbClassController.js";
        // gnbHandler(1, 0);

        document.addEventListener("DOMContentLoaded", () => {


            fetch('{{ route('menus.data') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.menus) {
                        renderMenu(data.menus, menuContainer);
                        if (window.innerWidth > 820) makeSortable(menuContainer);
                    }
                })
                .catch(error => {
                    console.error('메뉴 데이터를 불러오는 중 오류 발생:', error);
                });






            const menuContainer = document.querySelector("#menu_container");
            const menuData = [];

            function createMenuItem(item) {
                const li = document.createElement("li");
                li.classList.add("menu_item");
                li.setAttribute("data-depth", item.depth);

                const inner = document.createElement("div");
                inner.classList.add("inner");

                inner.innerHTML = `
                <div class="menu_name">
                    <input type="text" class="common_input" value="${
                    item.title
                }" draggable="false" placeholder="메뉴명">
                    <button type="button" class="plus_btn" draggable="false"><span>하위메뉴</span></button>
                    <button type="button" class="edit_btn" draggable="false">수정</button>
                    <button type="button" class="del_btn" draggable="false">삭제</button>
                    <button type="button" class="accordion_btn mob_block" draggable="false"></button>
                </div>
                <div class="accordion" draggable="false">
                    <div class="en">
                        <input type="text" class="common_input" value="${
                    item.enTitle
                }" placeholder="영문명">
                        <button type="button" class="edit_btn">수정</button>
                    </div>
                    <div class="path">
                        <input type="text" class="common_input" value="${
                    item.path
                }" placeholder="경로">
                        <button type="button" class="edit_btn">수정</button>
                    </div>
                    <div class="info">
                        <input type="text" class="common_input" value="${
                    item.desc
                }" placeholder="설명">
                        <button type="button" class="edit_btn">수정</button>
                    </div>
                </div>
                <div class="use" draggable="false">
                    <div class="common_switch">
                        <input type="checkbox" id="${
                    item.id
                }" aria-label="활성화" ${item.use ? "checked" : ""}>
                        <label for="${item.id}"></label>
                    </div>
                </div>
                `;

                li.appendChild(inner);

                if (item.children?.length) {
                    const ul = document.createElement("ul");
                    ul.classList.add("menu_children");
                    item.children.forEach((child) => {
                        ul.appendChild(createMenuItem(child));
                    });
                    li.appendChild(ul);
                }

                return li;
            }

            function renderMenu(data, container) {
                container.innerHTML = "";
                data.forEach((item) => container.appendChild(createMenuItem(item)));
            }

            function makeSortable(container) {
                if (window.innerWidth <= 820) return;
                container._sortable = new Sortable(container, {
                    group: "nested",
                    animation: 150,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    handle: ".menu_name",
                    draggable: ".menu_item",
                    onEnd: (evt) => {
                        const item = evt.item;
                        const originalDepth = parseInt(item.dataset.depth);
                        const toContainer = evt.to;
                        const toParentItem = toContainer.closest(".menu_item");

                        if (!toParentItem && originalDepth > 1) {
                            evt.from.insertBefore(item, evt.from.children[evt.oldIndex]);
                            return;
                        }

                        if (toParentItem) {
                            const parentDepth = parseInt(toParentItem.dataset.depth);
                            const newDepth = Math.min(parentDepth + 1, 3);

                            if (originalDepth === 1 && newDepth > 1) {
                                evt.from.insertBefore(item, evt.from.children[evt.oldIndex]);
                                return;
                            }

                            item.dataset.depth = newDepth;
                        } else {
                            item.dataset.depth = "1";
                        }

                        const childList = item.querySelector("ul.menu_children");
                        if (childList) makeSortable(childList);
                    },

                    onMove: (evt, originalEvent) => {
                        const dragged = evt.dragged;
                        const related = evt.related;
                        if (!dragged || !related) return true;

                        const draggedDepth = parseInt(dragged.dataset.depth);
                        const targetDepth = parseInt(related.dataset.depth);

                        if (draggedDepth === 2 && targetDepth === 2) {
                            const rect = related.getBoundingClientRect();
                            const dropX = originalEvent.clientX;

                            if (dropX > rect.left + rect.width / 2) {
                                let ul = related.querySelector("ul.menu_children");
                                if (!ul) {
                                    ul = document.createElement("ul");
                                    ul.classList.add("menu_children");
                                    related.appendChild(ul);
                                }
                                ul.appendChild(dragged);
                                dragged.dataset.depth = "3";
                                makeSortable(ul);
                                return false; // prevent Sortable's default move
                            }
                        }
                        return true;
                    },
                });

                container
                    .querySelectorAll("ul.menu_children")
                    .forEach((child) => makeSortable(child));
            }


            window.addEventListener("resize", () => {
                const allSortables = document.querySelectorAll(
                    ".menu_children, #menu_container"
                );
                allSortables.forEach((el) => {
                    if (el._sortable) {
                        el._sortable.destroy();
                        delete el._sortable;
                    }
                });

                if (window.innerWidth > 820) makeSortable(menuContainer);
            });

            const addMenuBtn = document.querySelector(".page_title .plus");

            addMenuBtn.addEventListener("click", () => {
                const newItem = {
                    id: "new_" + Date.now(),
                    title: "",
                    enTitle: "",
                    depth: 1,
                    path: "",
                    desc: "",
                    use: true,
                    children: [],
                };

                const newElement = createMenuItem(newItem);
                menuContainer.appendChild(newElement);
                makeSortable(menuContainer);
            });

            document.addEventListener("click", (e) => {
                const delBtn = e.target.closest(".del_btn");
                if (delBtn) {
                    const item = delBtn.closest(".menu_item");
                    if (item) item.remove();
                    return;
                }

                const plusBtn = e.target.closest(".plus_btn");
                if (plusBtn) {
                    const currentItem = plusBtn.closest(".menu_item");
                    const currentDepth = parseInt(currentItem.dataset.depth);
                    const newDepth = Math.min(currentDepth + 1, 3);

                    const newItem = {
                        id: "new_" + Date.now(),
                        title: "",
                        enTitle: "",
                        depth: newDepth,
                        path: "",
                        desc: "",
                        use: true,
                        children: [],
                    };

                    const newElement = createMenuItem(newItem);

                    if (newDepth > currentDepth) {
                        let childList = currentItem.querySelector("ul.menu_children");

                        if (!childList) {
                            childList = document.createElement("ul");
                            childList.classList.add("menu_children");
                            currentItem.appendChild(childList);
                        }
                        childList.appendChild(newElement);
                    } else {
                        currentItem.parentNode.insertBefore(
                            newElement,
                            currentItem.nextSibling
                        );
                    }

                    makeSortable(menuContainer);
                    return;
                }

                const clickedAccordionBtn = e.target.closest(".accordion_btn");
                const clickedInsideAccordion = e.target.closest(".accordion");
                const clickedInner = e.target.closest(".inner");

                document.querySelectorAll(".inner.on").forEach((openInner) => {
                    if (clickedInsideAccordion || openInner === clickedInner) return;
                    openInner.classList.remove("on");
                });

                if (clickedAccordionBtn && clickedInner) {
                    clickedInner.classList.toggle("on");
                }
            });
        });
    </script>
    <!-- 개발용 스크립트 S -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            document.querySelector('.common_bottom_btn .border_btn.save').addEventListener('click', function() {
                // 메뉴 데이터 수집
                const menuData = collectMenuData();
                disable_button(mainForm_submit);
                // AJAX로 서버에 전송
                fetch('{{ route('menus.index') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ menus: menuData })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('메뉴가 성공적으로 저장되었습니다.');
                            location.reload();
                        } else {
                            alert('저장 중 오류가 발생했습니다.');
                            restore_button(mainForm_submit);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('저장 중 오류가 발생했습니다.');
                        restore_button(mainForm_submit);
                    });
            });

            // 메뉴 데이터 수집 함수
            function collectMenuData() {
                const menuItems = document.querySelectorAll('#menu_container > .menu_item');
                return Array.from(menuItems).map(item => collectItemData(item));
            }

            // 개별 메뉴 항목 데이터 수집 (재귀적)
            function collectItemData(menuItem) {
                const inner = menuItem.querySelector('.inner');

                const id = menuItem.querySelector('.use input[type="checkbox"]').id;
                const title = inner.querySelector('.menu_name input').value;
                const enTitle = inner.querySelector('.en input').value;
                const path = inner.querySelector('.path input').value;
                const desc = inner.querySelector('.info input').value;
                const use = inner.querySelector('.use input[type="checkbox"]').checked;
                const depth = parseInt(menuItem.dataset.depth);

                const data = { id, title, enTitle, path, desc, use, depth };

                // 하위 메뉴 처리
                const childrenContainer = menuItem.querySelector('.menu_children');
                if (childrenContainer) {
                    const children = Array.from(childrenContainer.querySelectorAll(':scope > .menu_item'))
                        .map(childItem => collectItemData(childItem));

                    if (children.length > 0) {
                        data.children = children;
                    }
                }

                return data;
            }

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
    <!-- 개발용 스크립트 E -->
@stop
