const questionsAccordion = () => {
    const questionList = document.querySelector('.questions_list');

    if (!questionList) {
        console.warn('questions_list 요소를 찾을 수 없습니다.');
        return;
    }

    questionList.addEventListener('click', (e) => {
        const target = e.target.closest('.acc_btn');
        if (!target) return;

        questionList.querySelectorAll('li').forEach(li => {
            li.classList.remove('block');
        });

        const li = target.closest('li');
        if (li) li.classList.add('block');
    });
}

const questionsListController =()=> {
    questionsAccordion();
}

document.addEventListener('DOMContentLoaded', questionsListController);
