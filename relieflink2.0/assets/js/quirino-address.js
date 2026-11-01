(async function () {
    const addressGroups = document.querySelectorAll('[data-quirino-address]');
    if (addressGroups.length === 0) {
        return;
    }

    const datasetUrl = new URL('../data/quirino-address.json', document.currentScript.src);

    try {
        const response = await fetch(datasetUrl);
        if (!response.ok) {
            throw new Error('Address data could not be loaded.');
        }
        const addressData = await response.json();

        addressGroups.forEach((group) => {
            const provinceSelect = group.querySelector('[name="province"]');
            const citySelect = group.querySelector('[name="city"]');
            const barangaySelect = group.querySelector('[name="barangay"]');
            const savedProvince = group.dataset.currentProvince || '';
            const savedCity = group.dataset.currentCity || '';
            const savedBarangay = group.dataset.currentBarangay || '';
            const municipalities = addressData.municipalities;

            const setPlaceholder = (select, label) => {
                select.replaceChildren(new Option(label, ''));
            };

            const fillBarangays = (city, selectedBarangay = '') => {
                setPlaceholder(barangaySelect, city ? 'Select barangay' : 'Select municipality first');
                const barangays = municipalities[city] || [];
                barangays.forEach((barangay) => barangaySelect.add(new Option(barangay, barangay)));
                barangaySelect.disabled = barangays.length === 0;
                if (barangays.includes(selectedBarangay)) {
                    barangaySelect.value = selectedBarangay;
                }
            };

            const fillMunicipalities = (province, selectedCity = '', selectedBarangay = '') => {
                setPlaceholder(citySelect, province ? 'Select municipality' : 'Select province first');
                const cityNames = province === addressData.province ? Object.keys(municipalities) : [];
                cityNames.forEach((city) => citySelect.add(new Option(city, city)));
                citySelect.disabled = cityNames.length === 0;
                if (cityNames.includes(selectedCity)) {
                    citySelect.value = selectedCity;
                }
                fillBarangays(citySelect.value, selectedBarangay);
            };

            provinceSelect.addEventListener('change', () => fillMunicipalities(provinceSelect.value));
            citySelect.addEventListener('change', () => fillBarangays(citySelect.value));

            setPlaceholder(provinceSelect, 'Select province');
            provinceSelect.add(new Option(addressData.province, addressData.province));
            const hasQuirinoAddress = savedProvince.toLowerCase().startsWith('quirino') || Boolean(municipalities[savedCity]);
            if (hasQuirinoAddress) {
                provinceSelect.value = addressData.province;
            }
            fillMunicipalities(provinceSelect.value, savedCity, savedBarangay);
        });
    } catch (error) {
        addressGroups.forEach((group) => {
            group.querySelectorAll('select').forEach((select) => {
                select.disabled = true;
                select.replaceChildren(new Option('Address list unavailable', ''));
            });
        });
        console.error(error);
    }
})();
