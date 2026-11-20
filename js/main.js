//University Management System (main.js)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

document.addEventListener('DOMContentLoaded', () => {

    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(t => setTimeout(() => t.classList.remove('show'), 5000));

    document.querySelectorAll('[data-enrolled][data-capacity]').forEach(el => {
        const pct = Math.min((el.dataset.enrolled / el.dataset.capacity) * 100, 100);
        const fill = el.querySelector('.capacity-fill');
        if (fill) {
        fill.style.width = pct + '%';
        fill.style.background = pct >= 100 ? '#dc3545' : pct >= 80 ? '#fd7e14' : '#198754';
        }
    });

    document.querySelectorAll('.gpa-ring').forEach(ring => {
        const gpa = parseFloat(ring.dataset.gpa || 0);
        const pct = (gpa / 4.0) * 100;
        ring.style.setProperty('--pct', pct.toFixed(1));
    });

    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
        });
    });

    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
        const q = searchInput.value.toLowerCase();
        document.querySelectorAll('.searchable-row').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
        });
    }

    const tickerInner = document.querySelector('.ticker-inner');
    if (tickerInner) {
        tickerInner.innerHTML += tickerInner.innerHTML; // duplicate for loop
    }

    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') && currentPath.endsWith(link.getAttribute('href').split('/').pop())) {
        link.classList.add('active');
        }
    });

    const gradeSelect = document.getElementById('gradeSelect');
    if (gradeSelect) {
        const gradeMap = {'A':4.0,'A-':3.7,'B+':3.3,'B':3.0,'B-':2.7,'C+':2.3,'C':2.0,'C-':1.7,'D+':1.3,'D':1.0,'D-':0.7,'F':0.0};
        gradeSelect.addEventListener('change', () => {
        const pts = gradeMap[gradeSelect.value];
        const display = document.getElementById('gradePointsDisplay');
        if (display) display.textContent = pts !== undefined ? `(${pts.toFixed(2)} grade points)` : '';
        });
    }

});
