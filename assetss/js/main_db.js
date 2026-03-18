document.querySelectorAll('.menu-toggle').forEach(menu => {
    menu.addEventListener('click', e => {
        e.preventDefault();

        menu.classList.toggle('open');
        const submenu = menu.nextElementSibling;
        submenu.classList.toggle('show');
    });
});