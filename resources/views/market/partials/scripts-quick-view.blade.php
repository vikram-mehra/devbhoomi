{{-- Quick View Modal Scripts (Slider, Variant Selector, Stock, Add to Cart) --}}
<script>
    (function () {
        var QV_SLIDE_MS = 3500;
        var qvAutoTimer = null;

        function qvStopAuto() {
            if (qvAutoTimer) {
                clearInterval(qvAutoTimer);
                qvAutoTimer = null;
            }
        }

        function qvStartAuto(m) {
            qvStopAuto();
            if (!m) return;
            var gallery = m.querySelector('.js-qv-gallery');
            var slides = m.querySelectorAll('.qv-gallery__slide');
            if (!gallery || slides.length < 2) return;
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            qvAutoTimer = setInterval(function () {
                if (!m.classList.contains('show')) {
                    qvStopAuto();
                    return;
                }
                var total = m.querySelectorAll('.qv-gallery__slide').length;
                if (total < 2) {
                    qvStopAuto();
                    return;
                }
                var next = ((gallery._qvIndex || 0) + 1) % total;
                qvGoSlide(m, next);
            }, QV_SLIDE_MS);
        }

        var qvModal = document.getElementById('quickViewModal');
        if (qvModal) {
            qvModal.addEventListener('shown.bs.modal', function () {
                var slides = qvModal.querySelectorAll('.qv-gallery__slide');
                var active = qvModal.querySelector('.qv-gallery__slide.is-active');
                var idx = Array.prototype.indexOf.call(slides, active);
                qvGoSlide(qvModal, idx >= 0 ? idx : 0, true);
                qvStartAuto(qvModal);
            });
            qvModal.addEventListener('hidden.bs.modal', qvStopAuto);
        }

        function qvUniqueUrls(list) {
            var out = [];
            var seen = {};
            (list || []).forEach(function (u) {
                u = String(u || '').trim();
                if (!u || seen[u]) return;
                seen[u] = 1;
                out.push(u);
            });
            return out;
        }

        function qvFileKey(u) {
            return String(u || '').split('?')[0].split('/').pop().toLowerCase();
        }

        function qvFindSlideIndex(urls, url) {
            if (!url) return -1;
            var exact = urls.indexOf(url);
            if (exact >= 0) return exact;
            var key = qvFileKey(url);
            if (!key) return -1;
            return urls.findIndex(function (u) {
                var other = qvFileKey(u);
                return other === key || other.indexOf(key.replace(/\.[^.]+$/, '')) !== -1 || key.indexOf(other.replace(/\.[^.]+$/, '')) !== -1;
            });
        }

        function qvGoSlide(m, index, instant) {
            var gallery = m.querySelector('.js-qv-gallery');
            var track = m.querySelector('.js-qv-track');
            var slides = m.querySelectorAll('.qv-gallery__slide');
            if (!gallery || !track || !slides[index]) return;
            var width = gallery.clientWidth || slides[0].offsetWidth || 0;
            track.style.transition = instant ? 'none' : '';
            track.style.transform = 'translate3d(-' + (index * width) + 'px, 0, 0)';
            if (instant) {
                track.offsetHeight;
                track.style.transition = '';
            }
            m.querySelectorAll('.qv-gallery__dot').forEach(function (dot, idx) {
                dot.classList.toggle('is-active', idx === index);
            });
            slides.forEach(function (slide, idx) {
                slide.classList.toggle('is-active', idx === index);
            });
            gallery._qvIndex = index;
        }

        function qvRenderGallery(m, urls, alt, activeUrl) {
            var gallery = m.querySelector('.js-qv-gallery');
            var track = m.querySelector('.js-qv-track');
            var dots = m.querySelector('.js-qv-dots');
            if (!gallery || !track || !dots) return;
            urls = qvUniqueUrls(urls);
            if (!urls.length) urls = [''];
            gallery._qvUrls = urls;
            track.innerHTML = '';
            dots.innerHTML = '';
            urls.forEach(function (src, i) {
                var slide = document.createElement('div');
                slide.className = 'qv-gallery__slide' + (i === 0 ? ' is-active' : '');
                var img = document.createElement('img');
                img.src = src;
                img.alt = alt || '';
                img.className = 'qv-gallery__img' + (i === 0 ? ' js-qv-img' : '');
                img.width = 400;
                img.height = 480;
                img.decoding = 'async';
                slide.appendChild(img);
                track.appendChild(slide);

                var dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'qv-gallery__dot' + (i === 0 ? ' is-active' : '');
                dot.setAttribute('aria-label', 'Image ' + (i + 1));
                dot.setAttribute('role', 'tab');
                dot.addEventListener('click', function () {
                    qvGoSlide(m, i);
                    qvStartAuto(m);
                });
                dots.appendChild(dot);
            });
            gallery.classList.toggle('qv-gallery--single', urls.length < 2);
            var start = qvFindSlideIndex(urls, activeUrl);
            qvGoSlide(m, start >= 0 ? start : 0, true);
            qvStartAuto(m);
        }

        function qvShowImage(m, url) {
            var gallery = m.querySelector('.js-qv-gallery');
            var urls = (gallery && gallery._qvUrls) || [];
            var idx = qvFindSlideIndex(urls, url);
            if (idx >= 0) {
                qvGoSlide(m, idx);
                qvStartAuto(m);
                return;
            }
            var img = m.querySelector('.qv-gallery__slide.is-active .qv-gallery__img') || m.querySelector('.js-qv-img');
            if (img && url) img.src = url;
        }

        function qvParseJsonAttr(el, name) {
            var raw = el.getAttribute(name);
            if (!raw) return null;
            try { return JSON.parse(raw); } catch (e) {}
            try {
                return JSON.parse(String(raw).replace(/&quot;/g, '"').replace(/&amp;/g, '&'));
            } catch (e2) {}
            return null;
        }

        document.addEventListener('click', function (evt) {
            var btn = evt.target.closest ? evt.target.closest('.js-quick-view') : null;
            if (!btn) return;
            var m = document.getElementById('quickViewModal');
            if (!m) return;
            
            // Reset quantity
            var qtyInput = m.querySelector('.js-qv-qty-input');
            if (qtyInput) qtyInput.value = 1;

            var qvName = btn.getAttribute('data-qv-name') || '';
            var parsedImages = qvParseJsonAttr(btn, 'data-qv-images');
            var qvImages = Array.isArray(parsedImages) ? parsedImages : [];
            if (!qvImages.length) {
                qvImages = [btn.getAttribute('data-qv-img') || ''];
            }
            qvRenderGallery(m, qvImages, qvName, btn.getAttribute('data-qv-img') || '');
            m.querySelector('.js-qv-brand').textContent = btn.getAttribute('data-qv-brand') || '';
            m.querySelector('.js-qv-name').textContent = qvName;

            var descEl = m.querySelector('.js-qv-description');
            if (descEl) {
                var desc = (btn.getAttribute('data-qv-desc') || '').trim();
                descEl.textContent = desc;
                descEl.hidden = !desc;
            }
            
            var price = btn.getAttribute('data-qv-price');
            var cmp = btn.getAttribute('data-qv-compare');
            var el = m.querySelector('.js-qv-price');
            
            function updatePriceDisplay(p, c) {
                el.innerHTML = '';
                if (p !== null && p !== undefined && p !== '') {
                    el.appendChild(document.createTextNode('₹' + Number(p).toLocaleString()));
                    if (c) {
                        var d = document.createElement('del');
                        d.className = 'text-muted fs-6 fw-normal ms-2';
                        d.textContent = '₹' + Number(c).toLocaleString();
                        el.appendChild(d);
                    }
                }
            }
            updatePriceDisplay(price, cmp);

            var link = m.querySelector('.js-qv-link');
            link.href = btn.getAttribute('data-qv-url') || '#';
            
            var variantsData = qvParseJsonAttr(btn, 'data-qv-variants');
            var labelName = btn.getAttribute('data-qv-label') || 'Option';
            var variantsContainer = m.querySelector('.js-qv-variants-container');
            var variantsList = m.querySelector('.js-qv-variants-list');
            var labelEl = m.querySelector('.js-qv-variants-label');
            var varIdInput = m.querySelector('.js-qv-variant-id-input');
            
            var btnAdd = m.querySelector('.js-qv-add-to-cart-btn');
            var btnBuy = m.querySelector('.js-qv-buy-now-btn');
            var qtyWrap = m.querySelector('.js-qv-qty-wrap');

            function qvIsBuyable(v) {
                if (!v) return false;
                var stock = parseInt(v.stock, 10);
                if (isNaN(stock)) stock = v.buyable ? 1 : 0;
                return !!v.buyable && stock > 0;
            }

            function applyQvStockUi(buyable, stock) {
                var max = buyable ? Math.max(1, parseInt(stock, 10) || 1) : 1;
                if (qtyInput) {
                    qtyInput.setAttribute('max', String(max));
                    var cur = parseInt(qtyInput.value, 10) || 1;
                    if (cur < 1) cur = 1;
                    if (cur > max) cur = max;
                    qtyInput.value = cur;
                }
                if (qtyWrap) qtyWrap.classList.toggle('d-none', !buyable);
                if (btnAdd) {
                    btnAdd.disabled = !buyable;
                    btnAdd.textContent = buyable ? 'Add to cart' : 'Out of Stock';
                }
                if (btnBuy) btnBuy.classList.toggle('d-none', !buyable);
            }

            function selectQvVariant(v, pill) {
                variantsList.querySelectorAll('button').forEach(function(b) {
                    b.classList.remove('active', 'btn-primary');
                    b.classList.add('btn-outline-secondary');
                });
                if (pill) {
                    pill.classList.add('active', 'btn-primary');
                    pill.classList.remove('btn-outline-secondary');
                }
                varIdInput.value = v.id;
                updatePriceDisplay(v.effectivePrice, v.unitPrice > v.effectivePrice ? v.unitPrice : null);
                qvShowImage(m, v.image || btn.getAttribute('data-qv-img') || '');
                applyQvStockUi(qvIsBuyable(v), v.stock);
            }
            
            if (Array.isArray(variantsData) && variantsData.length) {
                var variants = variantsData;
                variantsContainer.classList.remove('d-none');
                labelEl.textContent = 'Select ' + labelName;
                variantsList.innerHTML = '';
                
                variants.forEach(function(v) {
                    var pill = document.createElement('button');
                    pill.type = 'button';
                    pill.className = 'btn btn-outline-secondary btn-sm rounded-pill px-3 py-1';
                    pill.textContent = v.size || v.color;
                    var buyable = qvIsBuyable(v);
                    if (!buyable) {
                        pill.disabled = true;
                        pill.classList.add('is-disabled');
                        pill.setAttribute('aria-disabled', 'true');
                        pill.title = 'Out of stock';
                    }

                    pill.addEventListener('click', function() {
                        if (pill.disabled) return;
                        selectQvVariant(v, pill);
                    });
                    
                    variantsList.appendChild(pill);
                });
                
                var defaultSelected = variants.find(qvIsBuyable) || variants[0];
                if (defaultSelected) {
                    var pills = variantsList.querySelectorAll('button');
                    var defIdx = variants.indexOf(defaultSelected);
                    selectQvVariant(defaultSelected, pills[defIdx] || null);
                }
            } else {
                variantsContainer.classList.add('d-none');
                varIdInput.value = btn.getAttribute('data-qv-variant') || '';
                
                var isBuyable = btn.closest('.js-cart-add-container')?.getAttribute('data-buyable') !== '0';
                applyQvStockUi(!!isBuyable, isBuyable ? 99 : 0);
            }
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(m).show();
            }
        });

        // Quick view quantity selectors
        var qvMinus = document.querySelector('.js-qv-qty-minus');
        var qvPlus = document.querySelector('.js-qv-qty-plus');
        var qvQtyInput = document.querySelector('.js-qv-qty-input');
        if (qvMinus && qvPlus && qvQtyInput) {
            qvMinus.addEventListener('click', function() {
                var val = parseInt(qvQtyInput.value, 10) || 1;
                if (val > 1) {
                    qvQtyInput.value = val - 1;
                }
            });
            qvPlus.addEventListener('click', function() {
                var val = parseInt(qvQtyInput.value, 10) || 1;
                var max = parseInt(qvQtyInput.getAttribute('max'), 10) || 99;
                if (val < max) {
                    qvQtyInput.value = val + 1;
                }
            });
        }
    })();
</script>
