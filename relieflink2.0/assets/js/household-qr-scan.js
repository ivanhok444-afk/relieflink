(function () {
    const form = document.querySelector('[data-household-scan-form]');
    if (!form) {
        return;
    }

    const input = form.querySelector('[name="qr_code"]');
    const cameraButton = document.getElementById('start-qr-camera');
    const stopButton = document.getElementById('stop-qr-camera');
    const reader = document.getElementById('qr-reader');
    const status = document.getElementById('qr-scan-status');
    let scanner = null;

    async function stopScanner() {
        if (!scanner) {
            return;
        }

        try {
            if (scanner.isScanning) {
                await scanner.stop();
            }
            await scanner.clear();
        } catch (error) {
            console.warn('Could not stop the QR scanner cleanly.', error);
        }
        scanner = null;
        reader.hidden = true;
        stopButton.hidden = true;
        cameraButton.hidden = false;
        cameraButton.disabled = false;
    }

    cameraButton.addEventListener('click', async () => {
        if (typeof window.Html5Qrcode !== 'function') {
            status.textContent = 'Camera scanner unavailable. Enter the Household ID manually.';
            return;
        }

        cameraButton.disabled = true;
        status.textContent = 'Requesting camera access...';
        reader.hidden = false;
        scanner = new window.Html5Qrcode('qr-reader');

        try {
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 }, aspectRatio: 1 },
                (decodedText) => {
                    input.value = decodedText.trim();
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    status.textContent = 'QR captured. Confirm the Household ID, then identify the household.';
                    stopScanner();
                    input.focus();
                },
                () => {}
            );
            cameraButton.hidden = true;
            stopButton.hidden = false;
            status.textContent = 'Point the camera at the household QR code.';
        } catch (error) {
            await stopScanner();
            status.textContent = 'Camera could not start. Allow camera access or enter the Household ID manually.';
        }
    });

    stopButton.addEventListener('click', async () => {
        await stopScanner();
        status.textContent = 'Camera stopped. You can still enter the Household ID manually.';
    });
})();
