document.querySelectorAll('[data-menu-button]').forEach((button) => {
    button.addEventListener('click', () => button.parentElement.classList.toggle('open'));
});
