export const customSelectHandler = (e) => {
    const $customSelectBox = e.target.closest('.js_custom_select');
    const $allSelects = document.querySelectorAll('.js_custom_select');

    $allSelects.forEach(select => {
        if (select !== $customSelectBox) {
            select.classList.remove('on');
        }
    })    

    if ($customSelectBox) {
        $customSelectBox.classList.toggle('on');
    }

    if (e.target.tagName === 'LI' && e.target.closest('.js_custom_select')) {
        const value = e.target.innerHTML;
        const dataValue = e.target.dataset.value;
        const selectInput = e.target.closest('.js_custom_select').querySelector('.select_value');
        
        if (selectInput.tagName === 'INPUT') {
            selectInput.value = value;
            selectInput.dataset.value = dataValue;
        } else if (selectInput.tagName === 'DIV') {
            selectInput.innerHTML = value;
            selectInput.dataset.value = dataValue;
        }
        $customSelectBox.classList.remove('on');
    }
}