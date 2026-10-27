(() => {
    const menuToggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('#primary-navigation');
    if (menuToggle && navigation) {
        menuToggle.addEventListener('click', () => {
            const open = menuToggle.getAttribute('aria-expanded') !== 'true';
            menuToggle.setAttribute('aria-expanded', String(open));
            navigation.classList.toggle('open', open);
        });
        navigation.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                menuToggle.setAttribute('aria-expanded', 'false');
                navigation.classList.remove('open');
            }
        });
    }

    document.querySelectorAll('form[data-booking-form]').forEach((form) => {
        const checkIn = form.querySelector('[name="check_in_date"]');
        const checkOut = form.querySelector('[name="check_out_date"]');
        const roomSelect = form.querySelector('[data-rate-select]');
        const nightsOutput = document.querySelector('[data-night-count]');
        const roomOutput = document.querySelector('[data-room-name]');
        const priceOutput = document.querySelector('[data-price-total]');
        const currency = form.dataset.currency || 'TZS';

        const formatMoney = (amount) => {
            try {
                return new Intl.NumberFormat(undefined, { style: 'currency', currency, maximumFractionDigits: 0 }).format(amount);
            } catch (_) {
                return `${Math.round(amount).toLocaleString()} ${currency}`;
            }
        };
        const updateSummary = () => {
            const selected = roomSelect?.selectedOptions?.[0];
            const rate = Number(selected?.dataset.rate || 0);
            const room = selected?.value || '';
            if (roomOutput) roomOutput.textContent = room || 'Choose a room';

            let nights = 0;
            if (checkIn?.value && checkOut?.value) {
                const start = new Date(`${checkIn.value}T12:00:00`);
                const end = new Date(`${checkOut.value}T12:00:00`);
                nights = Math.round((end - start) / 86400000);
            }
            if (nightsOutput) nightsOutput.textContent = nights > 0 ? String(nights) : 'Choose dates';
            if (priceOutput) priceOutput.textContent = nights > 0 && rate > 0 ? formatMoney(nights * rate) : '—';
        };

        checkIn?.addEventListener('change', () => {
            if (checkOut) {
                const date = new Date(`${checkIn.value}T12:00:00`);
                if (checkIn.value) date.setDate(date.getDate() + 1);
                const minimum = checkIn.value ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}` : '';
                checkOut.min = minimum;
                if (checkOut.value && minimum && checkOut.value < minimum) checkOut.value = '';
            }
            updateSummary();
        });
        checkOut?.addEventListener('change', updateSummary);
        roomSelect?.addEventListener('change', updateSummary);
        updateSummary();
    });

    document.querySelectorAll('input[type="date"][name="check_in_date"]').forEach((checkIn) => {
        const form = checkIn.closest('form');
        const checkOut = form?.querySelector('input[type="date"][name="check_out_date"]');
        if (!checkOut) return;
        checkIn.addEventListener('change', () => {
            if (!checkIn.value) return;
            const date = new Date(`${checkIn.value}T12:00:00`);
            date.setDate(date.getDate() + 1);
            const min = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            checkOut.min = min;
            if (checkOut.value && checkOut.value < min) checkOut.value = '';
        });
    });

    const dialog = document.querySelector('.gallery-dialog');
    const galleryItems = [...document.querySelectorAll('.gallery-item')];
    document.querySelectorAll('[data-gallery-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.galleryFilter;
            document.querySelectorAll('[data-gallery-filter]').forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
            galleryItems.forEach((item) => {
                item.hidden = filter !== 'all' && item.dataset.galleryCategory !== filter;
            });
        });
    });
    if (dialog) {
        const image = dialog.querySelector('img');
        const caption = dialog.querySelector('p');
        const close = dialog.querySelector('.dialog-close');
        document.querySelectorAll('[data-gallery-image]').forEach((button) => {
            button.addEventListener('click', () => {
                image.src = button.dataset.galleryImage;
                image.alt = button.dataset.galleryCaption || 'Lodge photograph';
                caption.textContent = button.dataset.galleryCaption || '';
                dialog.showModal();
                close.focus();
            });
        });
        close.addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
        dialog.addEventListener('close', () => { image.removeAttribute('src'); });
    }
})();
