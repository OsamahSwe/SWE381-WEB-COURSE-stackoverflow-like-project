document.addEventListener('DOMContentLoaded', function () {
    // 1) Toggle active class on filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });

    // 2) Toggle active class on sidebar nav items
    document.querySelectorAll('.nav-links li').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.nav-links li').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
        });
    });
});
