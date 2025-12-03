(function () {
    'use strict';

    const form = document.getElementById('registerForm');
    const pwd = document.getElementById('password');
    const pwdConfirm = document.getElementById('passwordConfirm');
    const pwdMeter = document.getElementById('pwdMeter');
    const submitBtn = document.getElementById('submitBtn');

    // super duper vypocet gpt
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
        const colors = ['#c00', '#d66', '#e6a', '#3a9', '#0a0']; //krasne farby
        pwdMeter.textContent = 'Sila: ' + labels[s];
        pwdMeter.style.color = colors[s];
    }

    pwd.addEventListener('input', updateMeter);

    // skontroluje ci su hesla rovnake
    function passwordsMatch() {
        return pwd.value === pwdConfirm.value && pwdConfirm.value.length >= 6;
    }

    form.addEventListener('submit', function (event) {

        if (!form.checkValidity() || !passwordsMatch()) {
            event.preventDefault();
            event.stopPropagation();
            if (!passwordsMatch()) {
                pwdConfirm.classList.add('is-invalid');
                document.getElementById('confirmError').style.display = 'block';
            } else {
                pwdConfirm.classList.remove('is-invalid');
                document.getElementById('confirmError').style.display = '';
            }
        } else {
            // submit
        }
        form.classList.add('was-validated');
    }, false);

    pwdConfirm.addEventListener('input', function () {
        if (passwordsMatch()) {
            pwdConfirm.classList.remove('is-invalid');
            pwdConfirm.classList.add('is-valid');
            document.getElementById('confirmError').style.display = '';
        } else {
            pwdConfirm.classList.remove('is-valid');
        }
    });

    updateMeter();
})();
