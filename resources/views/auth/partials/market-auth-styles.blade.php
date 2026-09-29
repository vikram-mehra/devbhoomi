<style>
    .auth-login-page { padding: 1.5rem 0 3.5rem; font-family: 'Poppins', sans-serif; }
    .auth-login-shell { max-width: 960px; margin: 0 auto; }
    .auth-login-panel {
        display: flex;
        flex-wrap: wrap;
        min-height: min(520px, calc(100vh - 220px));
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06), 0 24px 48px rgba(236, 137, 81, 0.08);
        background: #fff;
    }
    .auth-login-art {
        flex: 1 1 100%;
        position: relative;
        background: linear-gradient(152deg, #ec8951 0%, #d67840 42%, #b8642e 100%);
        color: #fff;
        padding: 2.25rem 1.75rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
    }
    @media (min-width: 992px) {
        .auth-login-art { flex: 0 0 42%; max-width: 42%; min-height: 480px; padding: 2.75rem 2.25rem; }
    }
    .auth-login-art::before,
    .auth-login-art::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.14);
        pointer-events: none;
    }
    .auth-login-art::before {
        width: 220px; height: 220px;
        top: -72px; right: -48px;
        animation: authFloat 14s ease-in-out infinite;
    }
    .auth-login-art::after {
        width: 160px; height: 160px;
        bottom: 10%; left: -40px;
        animation: authFloat 11s ease-in-out infinite reverse;
    }
    .auth-login-blob {
        position: absolute;
        width: 120px; height: 120px;
        background: rgba(255,255,255,0.08);
        border-radius: 40% 60% 55% 45%;
        top: 45%; right: 12%;
        animation: authMorph 12s ease-in-out infinite;
    }
    @keyframes authFloat {
        0%, 100% { transform: translate(0, 0) rotate(0deg); opacity: 0.9; }
        50% { transform: translate(-12px, 14px) rotate(6deg); opacity: 1; }
    }
    @keyframes authMorph {
        0%, 100% { border-radius: 40% 60% 55% 45%; transform: rotate(0deg) scale(1); }
        50% { border-radius: 55% 45% 40% 60%; transform: rotate(12deg) scale(1.06); }
    }
    @media (prefers-reduced-motion: reduce) {
        .auth-login-art::before, .auth-login-art::after, .auth-login-blob { animation: none; }
    }
    .auth-login-brand {
        position: relative;
        z-index: 1;
        font-weight: 700;
        font-size: 1.35rem;
        letter-spacing: -0.02em;
        margin-bottom: 0.75rem;
    }
    .auth-login-brand span { opacity: 0.92; font-weight: 500; }
    .auth-login-tagline {
        position: relative;
        z-index: 1;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.25;
        margin-bottom: 1.25rem;
        text-shadow: 0 2px 20px rgba(0,0,0,0.12);
    }
    .auth-login-perks {
        position: relative;
        z-index: 1;
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.875rem;
        opacity: 0.95;
        line-height: 1.85;
    }
    .auth-login-perks li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }
    .auth-login-perks li::before {
        content: '✓';
        font-weight: 700;
        flex-shrink: 0;
        opacity: 0.85;
    }
    .auth-login-form-wrap {
        flex: 1 1 100%;
        padding: 2rem 1.5rem 2.25rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #fff;
    }
    @media (min-width: 992px) {
        .auth-login-form-wrap { flex: 1 1 58%; max-width: 58%; padding: 2.75rem 2.5rem 3rem; }
    }
    .auth-login-form-wrap h1 {
        font-size: 1.65rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 0.35rem;
    }
    .auth-login-sub { color: #777; font-size: 0.9rem; margin-bottom: 1.75rem; }
    .auth-login-form-wrap .form-label { font-weight: 600; font-size: 0.8125rem; color: #444; margin-bottom: 0.4rem; }
    .auth-login-form-wrap .form-control {
        border-radius: 8px;
        border: 1px solid #e8e8e8;
        padding: 0.65rem 0.9rem;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .auth-login-form-wrap .form-control:focus {
        border-color: #ec8951;
        box-shadow: 0 0 0 3px rgba(236, 137, 81, 0.12);
    }
    .auth-login-submit {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.7rem 1.25rem;
        background: #ec8951;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .auth-login-submit:hover {
        background: #d67840;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(236, 137, 81, 0.35);
        color: #fff;
    }
    .auth-login-links a { color: #ec8951; text-decoration: none; font-weight: 500; font-size: 0.875rem; }
    .auth-login-links a:hover { text-decoration: underline; }
    .auth-login-alert {
        border-radius: 10px;
        border: none;
        background: rgba(236, 137, 81, 0.1);
        color: #8a4a24;
        font-size: 0.875rem;
        padding: 0.85rem 1rem;
        margin-bottom: 1.25rem;
    }
</style>
