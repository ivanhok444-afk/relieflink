(function () {
    document.querySelectorAll('[data-household-qr]').forEach((container) => {
        const value = container.dataset.qrValue;
        if (!value || typeof window.QRCode !== 'function') {
            container.textContent = 'QR code could not be generated. Use the Household ID below.';
            container.classList.add('text-muted');
            return;
        }

        try {
            new window.QRCode(container, {
                text: value,
                width: 220,
                height: 220,
                colorDark: '#173b35',
                colorLight: '#ffffff',
                correctLevel: window.QRCode.CorrectLevel.M
            });
        } catch (error) {
            container.textContent = 'QR code could not be generated. Use the Household ID below.';
            container.classList.add('text-muted');
            console.error('Household QR generation failed.', error);
        }
    });
})();
