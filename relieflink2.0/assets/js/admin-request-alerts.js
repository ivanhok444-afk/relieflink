(function () {
    const countElement = document.querySelector('[data-pending-requests-count]');
    const badgeElement = document.querySelector('[data-pending-requests-badge]');
    const requestsLink = document.querySelector('[data-pending-requests-link]');
    if (!countElement || !badgeElement) {
        return;
    }

    const initialCount = Number.parseInt(countElement.textContent, 10) || 0;
    const adminId = document.querySelector('[data-admin-user-id]')?.dataset.adminUserId || 'admin';
    const storageKey = `relieflink-pending-requests-${adminId}`;
    let previousCount = initialCount;
    try {
        const savedCount = window.localStorage.getItem(storageKey);
        previousCount = savedCount === null ? 0 : (Number.parseInt(savedCount, 10) || 0);
    } catch (error) {
        console.warn('Could not read saved request notification count.', error);
    }

    function renderCount(count) {
        countElement.textContent = String(count);
        badgeElement.textContent = String(count);
        badgeElement.hidden = count < 1;
        if (requestsLink) {
            requestsLink.setAttribute('aria-label', count
                ? `Requests: ${count} pending review`
                : 'Requests');
        }
    }

    function notify(count) {
        if (count <= previousCount) {
            previousCount = count;
            try {
                window.localStorage.setItem(storageKey, String(count));
            } catch (error) {
                console.warn('Could not save request notification count.', error);
            }
            return;
        }

        if (window.Swal) {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `${count} relief request${count === 1 ? '' : 's'} need review`,
                showConfirmButton: true,
                confirmButtonText: 'Review requests',
                timer: 8000,
                timerProgressBar: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.assign('requests.php#pending');
                }
            });
        }

        previousCount = count;
        try {
            window.localStorage.setItem(storageKey, String(count));
        } catch (error) {
            console.warn('Could not save request notification count.', error);
        }
    }

    renderCount(initialCount);
    notify(initialCount);

    async function refreshPendingCount() {
        try {
            const response = await fetch('../../controllers/PendingRequestCountController.php', {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            });
            if (!response.ok) {
                throw new Error(`Request count lookup failed with HTTP ${response.status}.`);
            }

            const result = await response.json();
            if (!Number.isInteger(result.count) || result.count < 0) {
                throw new Error('Request count response was invalid.');
            }

            renderCount(result.count);
            notify(result.count);
        } catch (error) {
            console.error('Unable to refresh pending relief request notifications.', error);
        }
    }

    window.setInterval(refreshPendingCount, 30000);
})();
