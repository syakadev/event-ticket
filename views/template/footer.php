    </main>

    <footer class="mt-auto py-3 bg-white border-top">
        <div class="container text-center small text-muted">
            Event Tiket // Clean Design Edition
        </div>
    </footer>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1090">
        <div id="appToast" class="toast border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="appToastTitle">Info</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="appToastBody"></div>
        </div>
    </div>

    <div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="globalConfirmTitle">Konfirmasi Aksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="globalConfirmMessage">
                    Apakah Anda yakin ingin melanjutkan?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="globalConfirmOk">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var flash = document.getElementById('app-flash');
            if (flash) {
                var map = {
                    success: { title: 'Berhasil', bg: 'text-bg-success' },
                    danger: { title: 'Gagal', bg: 'text-bg-danger' },
                    warning: { title: 'Perhatian', bg: 'text-bg-warning' },
                    info: { title: 'Info', bg: 'text-bg-info' }
                };
                var type = flash.dataset.flashType || 'info';
                var message = flash.dataset.flashMessage || '';
                var config = map[type] || map.info;
                var toastEl = document.getElementById('appToast');
                var titleEl = document.getElementById('appToastTitle');
                var bodyEl = document.getElementById('appToastBody');
                if (toastEl && titleEl && bodyEl && message) {
                    toastEl.classList.add(config.bg);
                    titleEl.textContent = config.title;
                    bodyEl.textContent = message;
                    var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
                    toast.show();
                }
            }

            var confirmModalEl = document.getElementById('globalConfirmModal');
            if (!confirmModalEl) {
                return;
            }

            var confirmModal = new bootstrap.Modal(confirmModalEl);
            var titleElConfirm = document.getElementById('globalConfirmTitle');
            var messageElConfirm = document.getElementById('globalConfirmMessage');
            var okButton = document.getElementById('globalConfirmOk');
            var activeTarget = null;

            document.addEventListener('submit', function (event) {
                var form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }
                if (form.dataset.confirmed === '1') {
                    form.dataset.confirmed = '0';
                    return;
                }
                if (!form.dataset.confirmMessage) {
                    return;
                }
                event.preventDefault();
                activeTarget = form;
                titleElConfirm.textContent = form.dataset.confirmTitle || 'Konfirmasi Aksi';
                messageElConfirm.textContent = form.dataset.confirmMessage;
                okButton.textContent = form.dataset.confirmOkText || 'Ya, Lanjutkan';
                okButton.className = form.dataset.confirmOkClass || 'btn btn-danger';
                confirmModal.show();
            }, true);

            okButton.addEventListener('click', function () {
                if (!activeTarget) {
                    return;
                }
                if (activeTarget instanceof HTMLFormElement) {
                    activeTarget.dataset.confirmed = '1';
                    activeTarget.submit();
                }
                activeTarget = null;
                confirmModal.hide();
            });

            // Dark Mode Toggle Logic
            var themeToggleBtn = document.getElementById('darkModeToggle');
            var iconDark = document.getElementById('themeIconDark');
            var iconLight = document.getElementById('themeIconLight');

            function updateThemeUI(theme) {
                if (theme === 'dark') {
                    iconDark.style.display = 'none';
                    iconLight.style.display = 'block';
                } else {
                    // light theme
                    iconDark.style.display = 'block';
                    iconLight.style.display = 'none';
                }
            }

            if (themeToggleBtn) {
                var currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                updateThemeUI(currentTheme);

                themeToggleBtn.addEventListener('click', function() {
                    var theme = document.documentElement.getAttribute('data-bs-theme');
                    var newTheme = theme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateThemeUI(newTheme);
                    
                    // trigger event for chart js to update
                    window.dispatchEvent(new Event('themeChanged'));
                });
            }

        })();
    </script>
</body>
</html>
