(function () {
    document.querySelectorAll('form[data-goods-limit]').forEach((form) => {
        const limit = Number.parseInt(form.dataset.goodsLimit, 10);
        const inputs = [...form.querySelectorAll('input[name^="items["]')];
        const counter = form.querySelector('[data-goods-count]');

        if (!Number.isInteger(limit) || limit < 1 || inputs.length === 0 || !counter) {
            return;
        }

        const selectedGoodsCount = () => inputs.filter((input) => Number.parseInt(input.value, 10) > 0).length;

        const updateCounter = () => {
            const selected = selectedGoodsCount();
            counter.textContent = `${selected} / ${limit} selected`;
            counter.classList.toggle('text-danger', selected > limit);
            counter.classList.toggle('text-muted', selected <= limit);
        };

        inputs.forEach((input) => {
            input.addEventListener('input', updateCounter);
            input.addEventListener('change', updateCounter);
        });

        form.addEventListener('submit', (event) => {
            const selected = selectedGoodsCount();
            if (selected === 0 || selected > limit) {
                event.preventDefault();
                counter.textContent = selected === 0
                    ? 'Select at least one good.'
                    : `Limit is ${limit} different goods per household.`;
                counter.classList.add('text-danger');
            }
        });

        updateCounter();
    });
})();
