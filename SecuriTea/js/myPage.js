const tabs = document.querySelectorAll('.sidebar li');
tabs.forEach(tab => {
    tab.addEventListener('click', () => {

        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');


        const target = tab.dataset.target;
        document.querySelectorAll('.tab-content').forEach(card => {
            card.style.display = card.id === target ? 'block' : 'none';
        });
    });
});