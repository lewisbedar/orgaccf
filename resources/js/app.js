document.querySelectorAll('[data-menu-button]').forEach((button) => {
    button.addEventListener('click', () => button.parentElement.classList.toggle('open'));
});

document.querySelectorAll('[data-grade-input]').forEach((input) => {
    const field = document.querySelector(`[data-absence-field="${input.dataset.studentId}"]`);
    const toggleAbsenceField = () => {
        if (!field) return;
        const isAbsent = input.value.trim().toUpperCase() === 'AB';
        field.hidden = !isAbsent;
        field.querySelector('select')?.toggleAttribute('required', isAbsent);
    };

    input.addEventListener('input', toggleAbsenceField);
    toggleAbsenceField();
});

const examTypeSelect = document.querySelector('[data-exam-type]');
const oralBreaks = document.querySelector('[data-oral-breaks]');
const toggleOralBreaks = () => {
    if (!examTypeSelect || !oralBreaks) return;
    oralBreaks.hidden = examTypeSelect.value !== 'oral';
};

examTypeSelect?.addEventListener('change', toggleOralBreaks);
toggleOralBreaks();
