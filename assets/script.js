document.addEventListener('DOMContentLoaded', () => {

    // --- Logika untuk Kursor Kustom ---
    const cursorDot = document.querySelector('.cursor-dot');
    const cursorOutline = document.querySelector('.cursor-outline');

    window.addEventListener('mousemove', function (e) {
        const posX = e.clientX;
        const posY = e.clientY;

        cursorDot.style.left = `${posX}px`;
        cursorDot.style.top = `${posY}px`;

        cursorOutline.style.left = `${posX}px`;
        cursorOutline.style.top = `${posY}px`;
    });
    
    // Efek kursor saat hover di atas link atau button
    const interactiveElements = document.querySelectorAll('a, .cta-button, .card, button, input[type="submit"], .mini-button');
    interactiveElements.forEach(el => {
        el.addEventListener('mouseover', () => {
            cursorOutline.style.transform = 'translate(-50%, -50%) scale(1.5)';
            cursorOutline.style.borderColor = 'var(--primary-color)';
        });
        el.addEventListener('mouseout', () => {
            cursorOutline.style.transform = 'translate(-50%, -50%) scale(1)';
            cursorOutline.style.borderColor = 'var(--highlight-color)';
        });
    });


    // --- Logika untuk Animasi Muncul saat Scroll ---
    const sections = document.querySelectorAll('.content-section');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1 // Muncul saat 10% bagian terlihat
    });

    sections.forEach(section => {
        observer.observe(section);
    });

    // --- Logika untuk Notifikasi Login Gagal (Simple Alert) ---
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        alert('Login Gagal!\nUsername atau password yang Anda masukkan salah.');
        // Menghapus parameter 'error' dari URL agar tidak muncul lagi saat refresh
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.pushState({path: newUrl}, '', newUrl);
    }
    // --- Utility untuk modal CRUD ---
    function setupModal(modalId, triggerSelector, onOpen) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }
        const closeBtn = modal.querySelector('.close-btn');
        const triggers = document.querySelectorAll(triggerSelector);
        triggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                if (typeof onOpen === 'function') {
                    onOpen(modal, trigger);
                }
                modal.style.display = 'block';
            });
        });
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Modal buku
    setupModal('editModal', '.book-edit-btn', (modal, trigger) => {
        modal.querySelector('#editBukuID').value = trigger.getAttribute('data-id');
        modal.querySelector('#editJudul').value = trigger.getAttribute('data-judul');
        modal.querySelector('#editPenulis').value = trigger.getAttribute('data-penulis');
        modal.querySelector('#editPenerbit').value = trigger.getAttribute('data-penerbit');
        modal.querySelector('#editTahun').value = trigger.getAttribute('data-tahun');
        const kategoriSelect = modal.querySelector('#editKategori');
        if (kategoriSelect) {
            kategoriSelect.value = trigger.getAttribute('data-kategori');
        }
    });

    // Modal user
    setupModal('userEditModal', '.user-edit-btn', (modal, trigger) => {
        modal.querySelector('#userEditID').value = trigger.getAttribute('data-id');
        modal.querySelector('#userEditUsername').value = trigger.getAttribute('data-username');
        modal.querySelector('#userEditPassword').value = trigger.getAttribute('data-password');
        modal.querySelector('#userEditEmail').value = trigger.getAttribute('data-email');
        modal.querySelector('#userEditNama').value = trigger.getAttribute('data-nama');
        modal.querySelector('#userEditAlamat').value = trigger.getAttribute('data-alamat');
        modal.querySelector('#userEditLevel').value = trigger.getAttribute('data-level');
    });

    // Modal kategori
    setupModal('kategoriEditModal', '.kategori-edit-btn', (modal, trigger) => {
        modal.querySelector('#kategoriEditID').value = trigger.getAttribute('data-id');
        modal.querySelector('#kategoriEditNama').value = trigger.getAttribute('data-nama');
    });

    // Modal peminjaman
    setupModal('peminjamanEditModal', '.peminjaman-edit-btn', (modal, trigger) => {
        modal.querySelector('#peminjamanEditID').value = trigger.getAttribute('data-id');
        modal.querySelector('#peminjamanEditStatus').value = trigger.getAttribute('data-status');
        modal.querySelector('#peminjamanEditTanggal').value = trigger.getAttribute('data-kembali');
    });
});
