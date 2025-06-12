export const gnbHandler = () => {
    const header = document.querySelector('header');
    const gnb = header?.querySelector('.gnb');
    const modeChangeBtn = header?.querySelector('.change_mode input');

    const savedTheme = localStorage.getItem('theme');

    if (savedTheme === 'dark') {
        modeChangeBtn.checked = true;
        document.documentElement.classList.add('dark');
    } else {
        modeChangeBtn.checked = false;
        document.documentElement.classList.remove('dark');
    }

    modeChangeBtn.addEventListener('change', () => {
        const isDark = modeChangeBtn.checked;
        document.documentElement.classList.toggle('dark', isDark);
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    gnb.addEventListener('click', (e) => {
        const btn = e.target.closest('.js_gnb_open_button');
        if (!btn) return;

        const targetItem = btn.closest('.gnb_1depth');
        if (!targetItem) return;

        const allItems = gnb.querySelectorAll('.gnb_1depth');
        allItems.forEach(item => item.classList.remove('on'));
        targetItem.classList.add('on');
    });
};