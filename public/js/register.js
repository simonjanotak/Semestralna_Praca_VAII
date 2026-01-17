document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    console.log('[register.js] loaded');

    // --- DOM elementy ---
    const form = document.getElementById('registerForm');
    const pwd = document.getElementById('password');
    const pwdConfirm = document.getElementById('passwordConfirm');
    const pwdMeter = document.getElementById('pwdMeter');
    const usernameInput = document.getElementById('username');
    const statusEl = document.getElementById('usernameStatus');
    const confirmError = document.getElementById('confirmError');

    if (!form || !pwd || !pwdConfirm || !pwdMeter || !usernameInput || !statusEl) {
        console.warn('[register.js] niektorý element chýba, JS sa nebude spúšťať');
        return;
    }

    // --- Password strength meter ---
    function scorePassword(p) {
        let score = 0;
        if (!p) return 0;
        if (p.length >= 6) score++;
        if (p.length >= 10) score++;
        if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++;
        if (/\d/.test(p)) score++;
        if (/[^A-Za-z0-9]/.test(p)) score++;
        return Math.min(score, 4);
    }

    function updateMeter() {
        const s = scorePassword(pwd.value);
        const labels = ['velmi slabe','slabe','ok','dobre','silne'];
        const colors = ['#c00', '#d66', '#e6a', '#3a9', '#0a0'];
        pwdMeter.textContent = 'Sila: ' + labels[s];
        pwdMeter.style.color = colors[s];
    }

    pwd.addEventListener('input', updateMeter);
    updateMeter();

    function passwordsMatch() {
        return pwd.value === pwdConfirm.value && pwdConfirm.value.length >= 6;
    }

    // --- Form submit validation ---
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity() || !passwordsMatch()) {
            event.preventDefault();
            event.stopPropagation();

            if (!passwordsMatch()) {
                pwdConfirm.classList.add('is-invalid');
                if (confirmError) confirmError.style.display = 'block';
            } else {
                pwdConfirm.classList.remove('is-invalid');
                if (confirmError) confirmError.style.display = 'none';
            }
        }

        form.classList.add('was-validated');
    });

    pwdConfirm.addEventListener('input', function() {
        if (passwordsMatch()) {
            pwdConfirm.classList.remove('is-invalid');
            pwdConfirm.classList.add('is-valid');
            if (confirmError) confirmError.style.display = 'none';
        } else {
            pwdConfirm.classList.remove('is-valid');
        }
    });

    // --- Username availability AJAX (debounced) ---
    console.log('[register.js] username availability check initialized');

    // debug expose
    console.log('[register.js] window.registerAvailabilityUrl =', window.registerAvailabilityUrl);

    let timer = null;

    function clearStatus() {
        statusEl.textContent = '';
        statusEl.style.color = '';
    }
});