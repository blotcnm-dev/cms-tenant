const headerClassHandler = () => {
    const gnbList = document.querySelector('.gnb > ul');

    gnbList.addEventListener('mouseover', (e) => {
        const li = e.target.closest('li');
        if (!li || !gnbList.contains(li)) return;

        const related = e.relatedTarget;
        if (li.contains(related)) return;

        li.classList.add('active');
    });

    gnbList.addEventListener('mouseout', (e) => {
        const li = e.target.closest('li');
        if (!li || !gnbList.contains(li)) return;

        const related = e.relatedTarget;
        if (li.contains(related)) return;

        li.classList.remove('active');
    });
};

const commonController =()=> {
    headerClassHandler();
}

document.addEventListener('DOMContentLoaded', commonController);