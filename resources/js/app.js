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

const sourceInputs = document.querySelectorAll('[data-class-source]');
const sourcePanels = document.querySelectorAll('[data-source-panel]');
const toggleClassSource = () => {
    const selected = document.querySelector('[data-class-source]:checked')?.value;
    sourcePanels.forEach((panel) => {
        panel.hidden = panel.dataset.sourcePanel !== selected;
    });
};

sourceInputs.forEach((input) => input.addEventListener('change', toggleClassSource));
toggleClassSource();

const manualTable = document.querySelector('[data-manual-table]');
document.querySelector('[data-add-manual-row]')?.addEventListener('click', () => {
    if (!manualTable) return;

    const index = manualTable.querySelectorAll('tbody tr').length;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input name="manual_students[${index}][last_name]"></td>
        <td><input name="manual_students[${index}][first_name]"></td>
        <td><input type="date" name="manual_students[${index}][birth_date]"></td>
        <td><input type="email" name="manual_students[${index}][email]"></td>
        <td>
            <input type="hidden" name="manual_students[${index}][extra_time]" value="0">
            <input type="checkbox" name="manual_students[${index}][extra_time]" value="1">
        </td>
        <td><button type="button" class="icon-action danger-text" data-remove-row title="Supprimer la ligne">×</button></td>
    `;
    manualTable.querySelector('tbody').append(row);
});

document.addEventListener('click', (event) => {
    const removeButton = event.target.closest('[data-remove-row], [data-remove-import-row]');
    if (!removeButton) return;

    const row = removeButton.closest('tr');
    row?.querySelector('input[type="checkbox"][name$="[include]"]')?.click();
    row?.remove();
});
