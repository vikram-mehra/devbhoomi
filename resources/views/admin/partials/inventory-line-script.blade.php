@push('scripts')
<script>
(function () {
    var searchUrl = @json(route('admin.inventory.api.variants'));
    var tbody = document.getElementById('invLineRows');
    var addBtn = document.getElementById('invAddLine');
    if (!tbody || !addBtn) return;

    var lineIndex = tbody.querySelectorAll('tr').length;

    function rowHtml(index) {
        return ''
            + '<td class="position-relative">'
            + '<input type="hidden" name="items[' + index + '][product_variant_id]" class="js-variant-id" value="">'
            + '<div class="input-group">'
            + '<input type="search" class="form-control js-variant-search" placeholder="' + @json(__('Search SKU, name, barcode…')) + '" autocomplete="off">'
            + '<button type="button" class="btn btn-outline-secondary js-barcode-scan" title="' + @json(__('Barcode')) + '"><i class="bi bi-upc-scan"></i></button>'
            + '</div>'
            + '<div class="list-group position-absolute w-100 shadow-sm js-variant-results d-none" style="z-index:5;max-height:220px;overflow:auto;"></div>'
            + '</td>'
            + '<td class="js-variant-stock text-muted">—</td>'
            + '<td><input type="number" min="1" name="items[' + index + '][quantity]" class="form-control" value="1" required></td>'
            + '<td><input type="number" step="0.01" min="0" name="items[' + index + '][unit_price]" class="form-control js-line-price" required></td>'
            + '<td><input type="number" step="0.01" min="0" name="items[' + index + '][tax_amount]" class="form-control" value="0"></td>'
            + '<td><button type="button" class="btn btn-sm btn-outline-danger js-remove-line"><i class="bi bi-trash"></i></button></td>';
    }

    function bindRow(row) {
        var search = row.querySelector('.js-variant-search');
        var hidden = row.querySelector('.js-variant-id');
        var results = row.querySelector('.js-variant-results');
        var stockEl = row.querySelector('.js-variant-stock');
        var priceEl = row.querySelector('.js-line-price');
        var timer;

        function pick(item) {
            hidden.value = item.id;
            search.value = item.label;
            if (stockEl) stockEl.textContent = item.stock_qty;
            if (priceEl && !priceEl.value) priceEl.value = item.unit_price;
            results.classList.add('d-none');
            results.innerHTML = '';
        }

        search.addEventListener('input', function () {
            clearTimeout(timer);
            var q = search.value.trim();
            hidden.value = '';
            if (q.length < 2) {
                results.classList.add('d-none');
                return;
            }
            timer = setTimeout(function () {
                fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(function (r) { return r.json(); }).then(function (payload) {
                    results.innerHTML = '';
                    (payload.data || []).forEach(function (item) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action small text-start';
                        btn.textContent = item.label + ' · SKU ' + item.sku + ' · Stock ' + item.stock_qty;
                        btn.addEventListener('click', function () { pick(item); });
                        results.appendChild(btn);
                    });
                    results.classList.toggle('d-none', results.children.length === 0);
                });
            }, 250);
        });

        var barcodeBtn = row.querySelector('.js-barcode-scan');
        if (barcodeBtn) {
            barcodeBtn.addEventListener('click', function () {
                var code = window.prompt(@json(__('Scan or enter barcode')));
                if (!code) return;
                fetch(searchUrl + '?barcode=' + encodeURIComponent(code.trim()), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(function (r) { return r.json(); }).then(function (payload) {
                    if ((payload.data || []).length) pick(payload.data[0]);
                });
            });
        }

        row.querySelector('.js-remove-line')?.addEventListener('click', function () {
            if (tbody.querySelectorAll('tr').length > 1) row.remove();
        });
    }

    addBtn.addEventListener('click', function () {
        var row = document.createElement('tr');
        row.innerHTML = rowHtml(lineIndex++);
        tbody.appendChild(row);
        bindRow(row);
    });

    tbody.querySelectorAll('tr').forEach(bindRow);
})();
</script>
@endpush
