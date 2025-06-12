export const layerHandler = (title, contentUrl, onLoaded) => {
    document.documentElement.classList.add('fixed');

    document.getElementById("layer")?.remove();
    document.getElementById("overlay")?.remove();

    const overlay = document.createElement('div');
    overlay.classList.add('overlay');
    overlay.id = 'overlay';

    const layer = document.createElement('div');
    layer.classList.add('layer_wrap');
    layer.id = 'layer';
    const layerName = contentUrl.split('/').pop().split('.').shift();
    layer.setAttribute('data-name', layerName);

    layer.innerHTML = `
        <div class="layer_top">
            <h2 class="layer_title" id="layerTitle">${title}</h2>
            <button type="button" class="close_btn js_remove_btn">닫기</button>
        </div>
        <div class="layer_content" id="layerContent"></div>
    `;

    document.body.appendChild(overlay);
    document.body.appendChild(layer);

    const layerContent = document.getElementById("layerContent");
    if (!layerContent) {
        console.error("layerContent가 생성되지 않았습니다.");
        return;
    }

    fetch(contentUrl)
        .then(res => res.text())
        .then(html => {
            layerContent.innerHTML = html;

            if (typeof onLoaded === 'function') onLoaded();
        })
        .catch(err => console.error(err));

    const closeLayerHandler = () => {
        document.documentElement.classList.remove('fixed');
        document.getElementById("layer")?.remove();
        document.getElementById("overlay")?.remove();
        document.removeEventListener('click', outsideClickHandler);
        // 레이어 닫힐 때 부모창 새로고침
        window.location.reload();
    };

    layer.addEventListener('click', (event) => {
        if (event.target.classList.contains('js_remove_btn')) {
            closeLayerHandler();
        }
    });

    const outsideClickHandler = (event) => {
        if (!layer.contains(event.target) && !event.target.closest('.del_btn')) {
            closeLayerHandler();
        }
    };

    document.addEventListener('click', outsideClickHandler);
};

window.layerHandler = layerHandler;
