{{--
  Session / validation flash as centered screen toasts.
  Optional: includeValidationErrors (bool), extraMessages (array of ['type'=>'success|danger|warning','body'=>string]),
  omitSessionStatus (bool) — default skips status when mk_cart_toast is set.
--}}
@php
    $omitStatus = $omitSessionStatus ?? (bool) session('mk_cart_toast');
    $flashStack = is_array($extraMessages ?? null) ? $extraMessages : [];
    if (session('status') && ! $omitStatus) {
        $flashStack[] = ['type' => 'success', 'body' => (string) session('status')];
    }
    if (session('error')) {
        $flashStack[] = ['type' => 'danger', 'body' => (string) session('error')];
    }
    if (session('warning')) {
        $flashStack[] = ['type' => 'warning', 'body' => (string) session('warning')];
    }
    if (session('resent')) {
        $flashStack[] = ['type' => 'success', 'body' => __('A fresh verification link has been sent to your email address.')];
    }
    if (! empty($includeValidationErrors) && isset($errors) && $errors->any()) {
        foreach ($errors->all() as $message) {
            $flashStack[] = ['type' => 'danger', 'body' => (string) $message];
        }
    }
    $toastMountId = 'zmFlashToastRoot-'.substr(str_replace('.', '', uniqid('', true)), -8);
@endphp
@if(count($flashStack))
<div id="{{ $toastMountId }}" class="zm-flash-toast-container" aria-live="polite"></div>
<style>
.zm-flash-toast-container {
  position: fixed;
  inset: 0;
  z-index: 10900;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  pointer-events: none;
  padding: max(1rem, env(safe-area-inset-top, 0px)) max(1rem, env(safe-area-inset-right, 0px)) max(1rem, env(safe-area-inset-bottom, 0px)) max(1rem, env(safe-area-inset-left, 0px));
  box-sizing: border-box;
}
.zm-flash-toast {
  pointer-events: auto;
  width: 100%;
  max-width: min(22rem, calc(100vw - 2rem));
  padding: 0.8rem 0.95rem;
  border-radius: 10px;
  box-shadow: 0 8px 32px rgba(15, 23, 42, 0.12);
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  animation: zmFlashToastIn 0.38s ease;
  background: #fff;
  border: 1px solid #e8e8ec;
}
.zm-flash-toast--success { border-left: 4px solid #16a34a; }
.zm-flash-toast--danger { border-left: 4px solid #dc2626; }
.zm-flash-toast--warning { border-left: 4px solid #d97706; }
.zm-flash-toast__body {
  flex: 1;
  font-size: 0.875rem;
  line-height: 1.45;
  color: #1e293b;
  font-weight: 400;
}
.zm-flash-toast__close {
  flex-shrink: 0;
  border: none;
  background: transparent;
  opacity: 0.55;
  cursor: pointer;
  font-size: 1.35rem;
  line-height: 1;
  padding: 0 0.15rem;
  margin: -0.15rem 0 0;
  color: #64748b;
}
.zm-flash-toast__close:hover { opacity: 1; }
@keyframes zmFlashToastIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
@media (prefers-reduced-motion: reduce) {
  .zm-flash-toast { animation: none; }
}
</style>
<script>
(function () {
    var items = @json($flashStack);
    var rootId = @json($toastMountId);
    var root = document.getElementById(rootId);
    if (!root || !items.length) return;
    function removeEl(el) {
        el.style.opacity = '0';
        el.style.transform = 'scale(0.96)';
        el.style.transition = 'opacity 0.28s ease, transform 0.28s ease';
        setTimeout(function () { if (el.parentNode) el.remove(); }, 300);
    }
    items.forEach(function (item, i) {
        var type = item.type || 'success';
        var el = document.createElement('div');
        el.className = 'zm-flash-toast zm-flash-toast--' + type;
        el.setAttribute('role', type === 'danger' ? 'alert' : 'status');
        var body = document.createElement('div');
        body.className = 'zm-flash-toast__body';
        body.textContent = item.body || '';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'zm-flash-toast__close';
        btn.setAttribute('aria-label', @json(__('Close')));
        btn.innerHTML = '&times;';
        btn.addEventListener('click', function () { removeEl(el); });
        el.appendChild(body);
        el.appendChild(btn);
        root.appendChild(el);
        setTimeout(function () { removeEl(el); }, 6500 + i * 450);
    });
})();
</script>
@endif
