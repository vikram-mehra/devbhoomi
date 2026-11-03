<script>
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (!csrf) return;

            var csrfToken = csrf.getAttribute('content');

            window.globalCartMap = @json(($layoutCartItems ?? collect())->mapWithKeys(fn($item) => [$item->product_variant_id => ['id' => $item->id, 'qty' => $item->qty]]));

            function formatMoney(n) {
                return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function formatMoneyInt(n) {
                return '₹' + Math.round(n).toLocaleString('en-IN');
            }

            function updateCartBadges(count) {
                var mobileBadges = document.querySelectorAll('.pro-mobile-nav__badge');
                var desktopBadges = document.querySelectorAll('.mk-myntra-bag-badge');
                var drawerBadges = document.querySelectorAll('.js-drawer-count-badge');

                mobileBadges.forEach(function (badge) {
                    if (count > 0) {
                        badge.textContent = count > 9 ? '9+' : count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });

                desktopBadges.forEach(function (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });

                drawerBadges.forEach(function (badge) {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });
            }

            function updateWishlistBadges(count) {
                var n = parseInt(count, 10) || 0;
                document.querySelectorAll('.mk-myntra-wish-badge').forEach(function (badge) {
                    if (n > 0) {
                        badge.textContent = n > 99 ? '99+' : n;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });
            }

            function applyWishlistState(productId, wishlisted) {
                document.querySelectorAll('.js-ajax-wishlist').forEach(function (form) {
                    var idInput = form.querySelector('input[name="product_id"]');
                    if (!idInput || String(idInput.value) !== String(productId)) return;
                    var btn = form.querySelector('button[type="submit"]');
                    if (!btn) return;
                    btn.classList.toggle('is-wishlisted', !!wishlisted);
                    btn.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');
                    btn.title = wishlisted ? 'Remove from wishlist' : 'Wishlist';
                    var icon = btn.querySelector('.bi');
                    if (icon) {
                        icon.classList.toggle('bi-heart-fill', !!wishlisted);
                        icon.classList.toggle('bi-heart', !wishlisted);
                    }
                    var label = btn.querySelector('.js-wish-label');
                    if (label) {
                        label.textContent = wishlisted ? 'Wishlisted' : 'Wishlist';
                    }
                });
            }

            function updateDrawerTotals(data, totalQty) {
                var subtotalEl = document.getElementById('drawerCartSubtotal');
                var shippingEl = document.getElementById('drawerCartShipping');
                var totalEl = document.getElementById('drawerCartTotal');

                if (subtotalEl) subtotalEl.textContent = formatMoney(data.subtotal);
                if (shippingEl) {
                    if (data.is_free_shipping) {
                        shippingEl.textContent = 'FREE';
                        shippingEl.className = 'text-success fw-semibold';
                    } else {
                        shippingEl.textContent = formatMoney(data.shipping_charge);
                        shippingEl.className = 'text-muted';
                    }
                }
                if (totalEl) totalEl.textContent = formatMoney(data.total);

                // Calculate count dynamically from UI quantities if not provided
                if (typeof totalQty === 'undefined') {
                    totalQty = 0;
                    document.querySelectorAll('.js-drawer-qty-val').forEach(function (el) {
                        totalQty += parseInt(el.textContent, 10) || 0;
                    });
                }

                updateCartBadges(totalQty);

                var summaryBlock = document.getElementById('drawerCartSummaryBlock');
                var emptyState = document.getElementById('drawerCartEmptyState');

                if (totalQty === 0) {
                    if (summaryBlock) summaryBlock.style.display = 'none';
                    if (emptyState) {
                        emptyState.style.display = 'block';
                    } else {
                        var itemsList = document.getElementById('drawerCartItemsList');
                        if (itemsList) {
                            itemsList.innerHTML = '<div class="text-center py-5" id="drawerCartEmptyState">' +
                                '<i class="bi bi-bag-x text-muted mb-2" style="font-size: 3rem; opacity: 0.4; display: block;"></i>' +
                                '<p class="text-muted small mb-0">Your cart is empty.</p>' +
                                '</div>';
                        }
                    }
                } else {
                    if (summaryBlock) summaryBlock.style.display = '';
                    if (emptyState) emptyState.style.display = 'none';
                }
            }

            window.msgOut = @json(__('Out of stock'));

            window.syncCartCTAContainers = function (cartMap) {
                document.querySelectorAll('.js-cart-add-container').forEach(function (container) {
                    var variantId = parseInt(container.getAttribute('data-variant-id'), 10);
                    if (!variantId) return;

                    var isHoverIcon = container.classList.contains('d-inline-block'); // Hover icon wrapper in card
                    var isPdp = container.getAttribute('data-pdp') === '1';
                    var isSticky = container.getAttribute('data-sticky') === '1';
                    var buyable = container.getAttribute('data-buyable') !== '0';
                    var item = cartMap[variantId];

                    // Remove existing selector if any
                    var oldSelector = container.querySelector('.js-qty-pill-selector');
                    if (oldSelector) oldSelector.remove();

                    var oldOos = container.querySelector('.js-pdp-oos-pill');
                    if (oldOos) oldOos.remove();

                    var form = container.querySelector('form');
                    var pdpCtas = container.querySelector('.js-default-pdp-ctas');
                    var pdpQtyCol = document.getElementById('pdpQtyCol');

                    // If not buyable (out of stock)
                    if (!buyable) {
                        if (form) form.classList.add('d-none');
                        if (pdpCtas) pdpCtas.classList.add('d-none');
                        if (isPdp && pdpQtyCol) pdpQtyCol.classList.add('d-none');

                        // Show "Out of stock" button inside the container
                        var oosHtml = '';
                        if (isPdp) {
                            oosHtml = '<div class="js-pdp-oos-pill pro-pdp-oos-cta">' +
                                '<button type="button" class="zm-btn btn-secondary" disabled style="min-height: 48px; background-color: #6c757d; color: #fff; border-color: #6c757d; padding: 0.85rem 1.85rem; font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">' + window.msgOut + '</button>' +
                                '</div>';
                        } else if (isSticky) {
                            oosHtml = '<div class="js-pdp-oos-pill pro-sticky-oos">' +
                                '<button type="button" class="btn btn-secondary rounded-pill" disabled>' + window.msgOut + '</button>' +
                                '</div>';
                        } else {
                            oosHtml = '<div class="js-pdp-oos-pill w-100">' +
                                '<button type="button" class="btn btn-secondary w-100" disabled>' + window.msgOut + '</button>' +
                                '</div>';
                        }
                        container.insertAdjacentHTML('beforeend', oosHtml);
                        return;
                    }

                    if (item && (isPdp || isSticky)) {
                        if (form) form.classList.add('d-none');
                        if (pdpCtas) pdpCtas.classList.add('d-none');
                        if (isPdp && pdpQtyCol) pdpQtyCol.classList.add('d-none');

                        var html = '';
                        if (isHoverIcon) {
                            html = '<div class="js-qty-pill-selector d-inline-flex align-items-center bg-light border rounded-pill px-1" style="height: 38px;" data-variant-id="' + variantId + '" data-item-id="' + item.id + '">' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 0.95rem; width: 24px; line-height: 1;"><i class="bi bi-dash"></i></button>' +
                                '<span class="fw-semibold px-1 js-selector-qty-val" style="font-size: 0.85rem; min-width: 16px; text-align: center;">' + item.qty + '</span>' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 0.95rem; width: 24px; line-height: 1;"><i class="bi bi-plus"></i></button>' +
                                '</div>';
                        } else if (isPdp) {
                            html = '<div class="js-qty-pill-selector d-flex gap-2" data-variant-id="' + variantId + '" data-item-id="' + item.id + '">' +
                                '<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2" style="height: 48px; min-width: 130px;">' +
                                '<button type="button" class="btn btn-lg p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 1.3rem; width: 35px; height: 100%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-dash"></i></button>' +
                                '<span class="fw-bold px-2 js-selector-qty-val" style="font-size: 1.1rem; min-width: 30px; text-align: center;">' + item.qty + '</span>' +
                                '<button type="button" class="btn btn-lg p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 1.3rem; width: 35px; height: 100%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-plus"></i></button>' +
                                '</div>' +
                                '</div>';
                        } else if (isSticky) {
                            html = '<div class="js-qty-pill-selector d-flex gap-2 align-items-center" data-variant-id="' + variantId + '" data-item-id="' + item.id + '">' +
                                '<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2" style="height: 38px; min-width: 100px;">' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 1.1rem; width: 30px; height: 100%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-dash"></i></button>' +
                                '<span class="fw-bold px-2 js-selector-qty-val" style="font-size: 0.95rem; min-width: 20px; text-align: center;">' + item.qty + '</span>' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 1.1rem; width: 30px; height: 100%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-plus"></i></button>' +
                                '</div>' +
                                '</div>';
                        } else {
                            html = '<div class="js-qty-pill-selector d-flex gap-2 w-100" data-variant-id="' + variantId + '" data-item-id="' + item.id + '">' +
                                '<div class="d-flex align-items-center justify-content-between border rounded-pill bg-light px-2" style="height: 38px; width: 100%;">' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 1.1rem; line-height: 1; height: 100%; display: flex; align-items: center; justify-content: center; width: 30px;"><i class="bi bi-dash"></i></button>' +
                                '<span class="fw-semibold js-selector-qty-val" style="font-size: 0.95rem; min-width: 24px; text-align: center;">' + item.qty + '</span>' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 1.1rem; line-height: 1; height: 100%; display: flex; align-items: center; justify-content: center; width: 30px;"><i class="bi bi-plus"></i></button>' +
                                '</div>' +
                                '</div>';
                        }

                        container.insertAdjacentHTML('beforeend', html);
                    } else {
                        if (form) form.classList.remove('d-none');
                        if (pdpCtas) pdpCtas.classList.remove('d-none');
                        if (isPdp && pdpQtyCol) pdpQtyCol.classList.remove('d-none');
                    }
                });
            };

            function patchDrawerCart(itemId, qty, card) {
                var url = '/cart/' + itemId;
                return fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ qty: qty })
                })
                    .then(function (r) {
                        if (!r.ok) throw new Error('Failed to update quantity');
                        return r.json();
                    })
                    .then(function (data) {
                        window.globalCartMap = {};
                        var totalQty = 0;
                        if (data.items) {
                            data.items.forEach(function (it) {
                                window.globalCartMap[it.product_variant_id] = { id: it.id, qty: parseInt(it.qty, 10) || 0 };
                                totalQty += parseInt(it.qty, 10) || 0;
                            });
                        }
                        window.syncCartCTAContainers(window.globalCartMap);

                        // Trigger Google Analytics events from AJAX response if defined
                        if (data.ga_events && Array.isArray(data.ga_events) && typeof gtag === 'function') {
                            data.ga_events.forEach(function (event) {
                                gtag('event', event.name, event.data);
                            });
                        }

                        // If updated from outside the drawer (e.g. PDP or product card listing), replace the HTML
                        var isFromDrawer = card && card.classList.contains('js-drawer-item-card');
                        if (!isFromDrawer) {
                            var itemsList = document.getElementById('drawerCartItemsList');
                            if (itemsList && data.html) {
                                itemsList.innerHTML = data.html;
                            }
                        }

                        updateDrawerTotals(data.totals, totalQty);

                        var mainCard = document.querySelector('.js-cart-item-card[data-item-id="' + itemId + '"]');
                        if (mainCard) {
                            var mainInput = mainCard.querySelector('.js-cart-qty-input');
                            if (mainInput) {
                                mainInput.value = qty;
                                var unitPriceMain = parseFloat(mainCard.querySelector('.js-item-unit').getAttribute('data-unit-price')) || 0;
                                var mainItemTotalEl = mainCard.querySelector('.js-item-total');
                                if (mainItemTotalEl) mainItemTotalEl.textContent = '₹' + Math.round(unitPriceMain * qty).toLocaleString('en-IN');
                                var subtotalEl = document.getElementById('cartSubtotal');
                                var shippingEl = document.getElementById('cartShipping');
                                var totalEl = document.getElementById('cartTotal');
                                if (subtotalEl) subtotalEl.textContent = formatMoney(data.totals.subtotal);
                                if (shippingEl) shippingEl.textContent = data.totals.is_free_shipping ? 'FREE' : formatMoney(data.totals.shipping_charge);
                                if (totalEl) totalEl.textContent = formatMoney(data.totals.total);
                                var minusBtn = mainCard.querySelector('.js-qty-minus');
                                if (minusBtn) {
                                    minusBtn.style.visibility = qty < 2 ? 'hidden' : 'visible';
                                }
                            }
                        }
                    })
                    .catch(function (err) {
                        console.error(err);
                    });
            }

            function deleteDrawerCart(itemId, card) {
                if (card) {
                    card.classList.add('js-drawer-item-fadeout');
                }
                var url = '/cart/' + itemId;
                return fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (r) {
                        if (!r.ok) {
                            if (card) card.classList.remove('js-drawer-item-fadeout');
                            throw new Error('Failed to delete item');
                        }
                        return r.json();
                    })
                    .then(function (data) {
                        if (card) {
                            card.remove();
                        }
                        window.globalCartMap = {};
                        var totalQty = 0;
                        if (data.items) {
                            data.items.forEach(function (it) {
                                window.globalCartMap[it.product_variant_id] = { id: it.id, qty: parseInt(it.qty, 10) || 0 };
                                totalQty += parseInt(it.qty, 10) || 0;
                            });
                        }
                        window.syncCartCTAContainers(window.globalCartMap);

                        // If deleted from outside the drawer, replace the HTML
                        var isFromDrawer = card && card.classList.contains('js-drawer-item-card');
                        if (!isFromDrawer) {
                            var itemsList = document.getElementById('drawerCartItemsList');
                            if (itemsList && data.html) {
                                itemsList.innerHTML = data.html;
                            }
                        }

                        updateDrawerTotals(data.totals, totalQty);

                        var mainCard = document.querySelector('.js-cart-item-card[data-item-id="' + itemId + '"]');
                        if (mainCard) {
                            window.location.reload();
                        }
                    })
                    .catch(function (err) {
                        console.error(err);
                    });
            }

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form.classList.contains('js-ajax-add-to-cart')) return;

                if (e.submitter && e.submitter.getAttribute('name') === 'buy_now') {
                    return;
                }

                e.preventDefault();

                var variantInput = form.querySelector('[name="product_variant_id"]');
                var qtyInput = form.querySelector('[name="qty"]');
                if (!variantInput) return;

                var variantId = parseInt(variantInput.value, 10);
                var qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;

                var submitBtn = e.submitter || form.querySelector('[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(form.getAttribute('action') || '/cart', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_variant_id: variantId,
                        qty: qty
                    })
                })
                    .then(function (r) {
                        if (submitBtn) submitBtn.disabled = false;
                        if (!r.ok) return r.json().then(function (err) { throw new Error(err.error || 'Failed to add to cart'); });
                        return r.json();
                    })
                    .then(function (data) {
                        window.globalCartMap = {};
                        var totalQty = 0;
                        if (data.items) {
                            data.items.forEach(function (it) {
                                window.globalCartMap[it.product_variant_id] = { id: it.id, qty: parseInt(it.qty, 10) || 0 };
                                totalQty += parseInt(it.qty, 10) || 0;
                            });
                        }
                        window.syncCartCTAContainers(window.globalCartMap);

                        var itemsList = document.getElementById('drawerCartItemsList');
                        if (itemsList && data.html) {
                            itemsList.innerHTML = data.html;
                        }

                        updateDrawerTotals(data.totals, totalQty);

                        // Trigger Google Analytics events from AJAX response if defined
                        if (data.ga_events && Array.isArray(data.ga_events) && typeof gtag === 'function') {
                            data.ga_events.forEach(function (event) {
                                gtag('event', event.name, event.data);
                            });
                        }

                        // Close quickViewModal if open
                        var quickViewEl = document.getElementById('quickViewModal');
                        if (quickViewEl && window.bootstrap) {
                            var modal = bootstrap.Modal.getInstance(quickViewEl);
                            if (modal) {
                                modal.hide();
                            }
                        }

                        if (typeof window.zmShowFlashToast === 'function') {
                            window.zmShowFlashToast(data.message || @json(__('Item added to cart')), 'success');
                        }
                    })
                    .catch(function (err) {
                        if (typeof window.zmShowFlashToast === 'function') {
                            window.zmShowFlashToast(err.message || @json(__('Error adding to cart')), 'danger');
                            return;
                        }
                        alert(err.message || 'Error adding to cart');
                    });
            });

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form.classList.contains('js-ajax-wishlist')) return;
                e.preventDefault();

                var idInput = form.querySelector('input[name="product_id"]');
                var btn = form.querySelector('button[type="submit"]');
                if (!idInput) return;
                if (form.getAttribute('data-busy') === '1') return;
                form.setAttribute('data-busy', '1');
                if (btn) btn.disabled = true;

                fetch(form.getAttribute('action') || '/wishlist', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ product_id: parseInt(idInput.value, 10) })
                })
                    .then(function (r) {
                        if (r.status === 401 || r.status === 419) {
                            window.location.href = '{{ route('login') }}';
                            return Promise.reject();
                        }
                        if (!r.ok) {
                            return r.json().catch(function () { return {}; }).then(function (err) {
                                throw new Error((err && (err.message || err.error)) || 'Could not update wishlist');
                            });
                        }
                        return r.json();
                    })
                    .then(function (data) {
                        if (!data) return;
                        applyWishlistState(data.product_id || idInput.value, !!data.wishlisted);
                        if (typeof data.count !== 'undefined') {
                            updateWishlistBadges(data.count);
                        }
                        if (data.message && typeof window.zmShowFlashToast === 'function') {
                            window.zmShowFlashToast(data.message, 'success');
                        }
                    })
                    .catch(function (err) {
                        if (err && err.message && typeof window.zmShowFlashToast === 'function') {
                            window.zmShowFlashToast(err.message, 'danger');
                        } else if (err && err.message) {
                            alert(err.message);
                        }
                    })
                    .finally(function () {
                        form.removeAttribute('data-busy');
                        if (btn) btn.disabled = false;
                    });
            });

            document.addEventListener('click', function (e) {
                var target = e.target;

                var cardOpt = target.closest('.js-card-variant-pill');
                if (cardOpt) {
                    if (cardOpt.disabled || cardOpt.getAttribute('aria-disabled') === 'true') return;
                    var card = cardOpt.closest('.zm-pro-card');
                    if (!card) return;
                    card.querySelectorAll('.js-card-variant-pill').forEach(function (btn) {
                        btn.classList.toggle('is-active', btn === cardOpt);
                    });
                    var optId = cardOpt.getAttribute('data-id') || '';
                    var optPrice = cardOpt.getAttribute('data-price');
                    var optCompare = cardOpt.getAttribute('data-compare') || '';
                    var optBuyable = cardOpt.getAttribute('data-buyable') === '1';
                    var optImage = cardOpt.getAttribute('data-image') || '';
                    var container = card.querySelector('.js-cart-add-container');
                    if (container) {
                        container.setAttribute('data-variant-id', optId);
                        container.setAttribute('data-buyable', optBuyable ? '1' : '0');
                        var variantInput = container.querySelector('input[name="product_variant_id"]');
                        if (variantInput) variantInput.value = optId;
                    }
                    var nowEl = card.querySelector('.js-card-price-now');
                    var cmpEl = card.querySelector('.js-card-price-compare');
                    var offEl = card.querySelector('.js-card-price-off');
                    if (nowEl && optPrice !== null && optPrice !== '') {
                        nowEl.textContent = '₹' + Math.round(Number(optPrice)).toLocaleString('en-IN');
                    }
                    if (cmpEl) {
                        if (optCompare) {
                            cmpEl.textContent = '₹' + Math.round(Number(optCompare)).toLocaleString('en-IN');
                            cmpEl.hidden = false;
                        } else {
                            cmpEl.hidden = true;
                        }
                    }
                    if (offEl) {
                        if (optCompare && Number(optCompare) > Number(optPrice)) {
                            offEl.textContent = Math.round(100 - (Number(optPrice) / Number(optCompare)) * 100) + '% Off';
                            offEl.hidden = false;
                        } else {
                            offEl.hidden = true;
                        }
                    }
                    if (optImage) {
                        var cardImg = card.querySelector('.zm-pro-card__img--primary');
                        if (cardImg) cardImg.src = optImage;
                    }
                    if (typeof window.syncCartCTAContainers === 'function') {
                        window.syncCartCTAContainers(window.globalCartMap || {});
                    }
                    return;
                }

                var buyNowBtn = target.closest('.js-pdp-direct-buynow');
                if (buyNowBtn) {
                    window.location.href = '/checkout';
                    return;
                }

                var plusBtn = target.closest('.js-selector-qty-plus');
                if (plusBtn) {
                    var selector = plusBtn.closest('.js-qty-pill-selector');
                    var itemId = selector.getAttribute('data-item-id');
                    var valEl = selector.querySelector('.js-selector-qty-val');
                    if (valEl) {
                        var qty = parseInt(valEl.textContent, 10) || 1;
                        if (qty < 99) {
                            qty++;
                            valEl.textContent = qty;
                            patchDrawerCart(itemId, qty, selector);
                        }
                    }
                    return;
                }

                var minusBtn = target.closest('.js-selector-qty-minus');
                if (minusBtn) {
                    var selector = minusBtn.closest('.js-qty-pill-selector');
                    var itemId = selector.getAttribute('data-item-id');
                    var valEl = selector.querySelector('.js-selector-qty-val');
                    if (valEl) {
                        var qty = parseInt(valEl.textContent, 10) || 1;
                        if (qty > 1) {
                            qty--;
                            valEl.textContent = qty;
                            patchDrawerCart(itemId, qty, selector);
                        } else {
                            deleteDrawerCart(itemId, selector);
                        }
                    }
                    return;
                }
            });

            var drawerList = document.getElementById('drawerCartItemsList');
            if (drawerList) {
                drawerList.addEventListener('click', function (e) {
                    var target = e.target;

                    var plusBtn = target.closest('.js-drawer-qty-plus');
                    if (plusBtn) {
                        var card = plusBtn.closest('.js-drawer-item-card');
                        var valEl = card.querySelector('.js-drawer-qty-val');
                        var minusBtn = card.querySelector('.js-drawer-qty-minus');
                        if (valEl) {
                            var qty = parseInt(valEl.textContent, 10) || 1;
                            if (qty < 99) {
                                qty++;
                                valEl.textContent = qty;
                                if (minusBtn) minusBtn.style.visibility = 'visible';

                                var unitPrice = parseFloat(card.getAttribute('data-unit-price')) || 0;
                                var totalEl = card.querySelector('.js-drawer-item-total');
                                if (totalEl) {
                                    totalEl.textContent = '₹' + Math.round(unitPrice * qty).toLocaleString('en-IN');
                                }
                                var compareEl = card.querySelector('.js-drawer-item-compare');
                                if (compareEl) {
                                    var compareUnit = parseFloat(compareEl.getAttribute('data-compare-unit')) || 0;
                                    compareEl.textContent = '₹' + Math.round(compareUnit * qty).toLocaleString('en-IN');
                                }

                                patchDrawerCart(card.getAttribute('data-item-id'), qty, card);
                            }
                        }
                        return;
                    }

                    var minusBtn = target.closest('.js-drawer-qty-minus');
                    if (minusBtn) {
                        var card = minusBtn.closest('.js-drawer-item-card');
                        var valEl = card.querySelector('.js-drawer-qty-val');
                        if (valEl) {
                            var qty = parseInt(valEl.textContent, 10) || 1;
                            if (qty > 1) {
                                qty--;
                                valEl.textContent = qty;
                                if (qty < 2 && minusBtn) minusBtn.style.visibility = 'hidden';

                                var unitPrice = parseFloat(card.getAttribute('data-unit-price')) || 0;
                                var totalEl = card.querySelector('.js-drawer-item-total');
                                if (totalEl) {
                                    totalEl.textContent = '₹' + Math.round(unitPrice * qty).toLocaleString('en-IN');
                                }
                                var compareEl = card.querySelector('.js-drawer-item-compare');
                                if (compareEl) {
                                    var compareUnit = parseFloat(compareEl.getAttribute('data-compare-unit')) || 0;
                                    compareEl.textContent = '₹' + Math.round(compareUnit * qty).toLocaleString('en-IN');
                                }

                                patchDrawerCart(card.getAttribute('data-item-id'), qty, card);
                            }
                        }
                        return;
                    }

                    var removeBtn = target.closest('.js-drawer-remove-btn');
                    if (removeBtn) {
                        var card = removeBtn.closest('.js-drawer-item-card');
                        deleteDrawerCart(card.getAttribute('data-item-id'), card);
                        return;
                    }
                });
            }

            window.syncCartCTAContainers(window.globalCartMap);
        })();
    </script>
