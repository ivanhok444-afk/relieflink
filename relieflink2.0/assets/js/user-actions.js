(function () {
    if (typeof window.Swal === 'undefined') {
        return;
    }

    const toast = window.Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4200,
        timerProgressBar: true
    });

    document.querySelectorAll('.alert').forEach((alert) => {
        const message = alert.innerText.trim();
        if (!message) {
            return;
        }

        let icon = 'info';
        if (alert.classList.contains('alert-danger') || alert.getAttribute('role') === 'alert') {
            icon = 'error';
        } else if (alert.classList.contains('alert-success')) {
            icon = 'success';
        } else if (alert.classList.contains('alert-warning')) {
            icon = 'warning';
        }

        toast.fire({ icon, title: message });
        alert.remove();
    });

    document.querySelectorAll('form[data-swal-confirm]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            if (form.dataset.swalConfirmed === 'true') {
                delete form.dataset.swalConfirmed;
                return;
            }

            event.preventDefault();
            const result = await window.Swal.fire({
                title: form.dataset.swalConfirm,
                text: form.dataset.swalText || 'Please confirm this change.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: form.dataset.swalConfirmButton || 'Confirm',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            });

            if (result.isConfirmed) {
                form.dataset.swalConfirmed = 'true';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        });
    });

    document.querySelectorAll('a[href*="/controllers/LogoutController.php"]').forEach((link) => {
        link.addEventListener('click', async (event) => {
            event.preventDefault();
            const result = await window.Swal.fire({
                title: 'Log out of your account?',
                text: 'You can sign in again at any time.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Log out',
                cancelButtonText: 'Stay signed in',
                reverseButtons: true,
                focusCancel: true
            });

            if (result.isConfirmed) {
                window.location.assign(link.href);
            }
        });
    });
})();
