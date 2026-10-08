(() => {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('menuToggle');
    const overlay = document.getElementById('mobileOverlay');
    const close = () => { sidebar?.classList.remove('open'); overlay?.classList.remove('visible'); };
    toggle?.addEventListener('click', () => {sidebar?.classList.toggle('open');overlay?.classList.toggle('visible');});
    overlay?.addEventListener('click', close);

    const currency = n => new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 2}).format(n);
    const root = document.querySelector('[data-purchase-form]');
    if (root) {
        const container = document.getElementById('purchaseItems');
        const template = document.getElementById('purchaseItemTemplate');
        const existingIndexes = [...container.querySelectorAll('[data-row-index]')]
            .map(el => Number(el.dataset.rowIndex)).filter(Number.isFinite);
        let nextIndex = Math.max(-1, ...existingIndexes) + 1;
        const value = (node) => Math.max(0, Number(node?.value) || 0);
        const adjustment = name => value(root.querySelector(`[name="${name}"]`));
        const put = (id, result) => { const node = document.getElementById(id); if (node) node.textContent = result; };
        const recalculate = () => {
            let gross = 0, discountItems = 0, invalid = false;
            container.querySelectorAll('[data-item-row]').forEach((row, idx) => {
                row.querySelector('[data-item-number]').textContent = idx + 1;
                const qty = value(row.querySelector('[data-quantity]'));
                const multiplier = value(row.querySelector('[data-multiplier]'));
                const cost = value(row.querySelector('[data-cost]'));
                const percent = value(row.querySelector('[data-line-percent]'));
                const extraDiscount = value(row.querySelector('[data-line-discount]'));
                const lineGross = Math.round(qty * cost * 100) / 100;
                const lineDiscount = Math.round((Math.round(lineGross * percent / 100 * 100) / 100 + extraDiscount) * 100) / 100;
                const rowInvalid = lineDiscount > lineGross || percent > 100;
                invalid = invalid || rowInvalid;
                gross += lineGross;
                discountItems += lineDiscount;
                row.querySelector('[data-base-qty]').textContent = (qty * multiplier).toLocaleString('id-ID');
                row.querySelector('[data-line-gross]').textContent = currency(lineGross);
                row.querySelector('[data-line-discount-total]').textContent = currency(lineDiscount);
                row.querySelector('[data-line-total]').textContent = currency(lineGross - lineDiscount);
                row.querySelector('[data-line-warning]').hidden = !rowInvalid;
            });
            gross = Math.round(gross * 100) / 100;
            discountItems = Math.round(discountItems * 100) / 100;
            const discountInvoice = adjustment('discount');
            const dpp = Math.round((gross - discountItems - discountInvoice) * 100) / 100;
            const mode = document.getElementById('taxMode')?.value || 'manual';
            const rate = value(document.getElementById('taxRate'));
            const otherDpp = Math.round(dpp * 11 / 12 * 100) / 100;
            const tax = mode === 'none' ? 0 : mode === 'standard'
                ? Math.round(dpp * rate / 100 * 100) / 100
                : mode === 'nilai_lain' ? Math.round(otherDpp * rate / 100 * 100) / 100
                : value(document.getElementById('taxManual'));
            const shipping = adjustment('shipping');
            const total = Math.round((dpp + tax + shipping) * 100) / 100;
            put('calcGross', currency(gross));
            put('calcLineDiscount', '− ' + currency(discountItems));
            put('calcInvoiceDiscount', '− ' + currency(discountInvoice));
            put('calcDpp', currency(dpp));
            put('calcOtherDpp', mode === 'nilai_lain' ? currency(otherDpp) : '—');
            put('calcTax', currency(tax));
            put('calcShipping', currency(shipping));
            put('invoiceTotal', currency(total));
            const documentInput = root.querySelector('[name="document_total"]');
            const hasDocumentTotal = documentInput?.value !== '';
            const difference = total - value(documentInput);
            const matchesDocument = !hasDocumentTotal || Math.abs(difference) < 0.01;
            const docMatch = document.getElementById('invoiceDocumentMatch');
            if (docMatch) {
                docMatch.hidden = !hasDocumentTotal;
                docMatch.textContent = matchesDocument ? '✓ Total perhitungan cocok dengan faktur asli.' : '⚠ Selisih dengan faktur asli: ' + currency(Math.abs(difference)) + '. Periksa diskon / PPN.';
                docMatch.classList.toggle('doc-mismatch', !matchesDocument);
            }
            const manualTax = document.getElementById('taxManual');
            const taxRate = document.getElementById('taxRate');
            if (manualTax) manualTax.readOnly = mode !== 'manual';
            if (taxRate) taxRate.readOnly = (mode === 'none' || mode === 'manual');
            const submit = root.querySelector('button[type="submit"]');
            if (submit) {
                submit.disabled = invalid || !matchesDocument || dpp < 0 || total < 0 || !Number.isFinite(total) || container.querySelectorAll('[data-item-row]').length < 1;
            }
        };
        document.getElementById('addItem')?.addEventListener('click', () => {
            if (container.querySelectorAll('[data-item-row]').length >= 50) { alert('Maksimal 50 barang per faktur.'); return; }
            const fragment = template.content.cloneNode(true);
            fragment.querySelectorAll('[name]').forEach(el => el.name = el.name.replaceAll('__INDEX__', nextIndex));
            fragment.querySelector('[data-row-index]').dataset.rowIndex = String(nextIndex);
            nextIndex += 1;
            container.appendChild(fragment);
            recalculate();
        });
        root.addEventListener('input', recalculate);
        root.addEventListener('change', recalculate);
        root.addEventListener('click', e => {
            if (e.target.closest('[data-remove-item]')) {
                if (container.querySelectorAll('[data-item-row]').length <= 1) { alert('Minimal satu barang dalam faktur.'); return; }
                e.target.closest('[data-item-row]').remove();
                recalculate();
            }
        });
        if (!container.querySelector('[data-item-row]')) document.getElementById('addItem')?.click();
        recalculate();
    }
    const outProduct = document.getElementById('outProduct');
    const outQty = document.getElementById('outQuantity');
    if(outProduct && outQty) {
        const recalc = () => {
            const opt = outProduct.selectedOptions[0];
            const stock = Math.max(0, Number(opt?.dataset.stock) || 0);
            const unit = opt?.dataset.unit || 'unit';
            const qty = Math.max(0, Number(outQty.value) || 0);
            document.getElementById('currentStock').textContent = `${stock} ${unit}`;
            document.getElementById('outPreview').textContent = `${qty} ${unit}`;
            const remaining = document.getElementById('stockAfter');
            remaining.textContent = `${stock - qty} ${unit}`;
            remaining.classList.toggle('negative', qty > stock);
        };
        outProduct.addEventListener('change', recalc);
        outQty.addEventListener('input', recalc); recalc();
    }
})();
