document.addEventListener('DOMContentLoaded', function () {
    var isSignUp = false;
    var title    = document.getElementById('auth-title');
    var subtitle = document.getElementById('auth-subtitle');
    var form     = document.getElementById('auth-form');
    var toggle   = document.getElementById('auth-toggle');
    var submit   = document.getElementById('auth-submit');
    var nameField   = document.getElementById('name-field');
    var phoneField  = document.getElementById('phone-field');
    var confirmField = document.getElementById('confirm-field');
    var loginOptions = document.getElementById('auth-options');
    var switchText = document.getElementById('switch-text');
    var errorBox   = document.getElementById('login-error');
    var successBox = document.getElementById('login-success');
    var errorMsg   = document.getElementById('error-message');
    var successMsg = document.getElementById('success-message');

    toggle.addEventListener('click', function () {
        isSignUp = !isSignUp;

        if (isSignUp) {
            title.textContent    = 'Create Account';
            subtitle.textContent = 'Join BDC Music Studio today';
            submit.textContent   = 'Sign Up';
            nameField.classList.remove('d-none');
            phoneField.classList.remove('d-none');
            confirmField.classList.remove('d-none');
            loginOptions.classList.add('d-none');
            switchText.textContent = 'Already have an account?';
            toggle.textContent = 'Sign In';
        } else {
            title.textContent    = 'Welcome Back';
            subtitle.textContent = 'Sign in to your account to continue';
            submit.textContent   = 'Sign In';
            nameField.classList.add('d-none');
            phoneField.classList.add('d-none');
            confirmField.classList.add('d-none');
            loginOptions.classList.remove('d-none');
            switchText.textContent = "Don't have an account?";
            toggle.textContent = 'Sign Up';
        }

        errorBox.classList.add('d-none');
        successBox.classList.add('d-none');
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var email    = document.getElementById('auth-email').value.trim();
        var password = document.getElementById('auth-password').value.trim();

        errorBox.classList.add('d-none');
        successBox.classList.add('d-none');

        if (!email || !password) {
            errorMsg.textContent   = 'Please fill in all fields.';
            errorBox.classList.remove('d-none');
            return;
        }

        var formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);

        if (isSignUp) {
            var name    = document.getElementById('auth-name').value.trim();
            var phone   = document.getElementById('auth-phone').value.trim();
            var confirm = document.getElementById('auth-confirm').value.trim();

            if (!name) {
                errorMsg.textContent   = 'Please enter your full name.';
                errorBox.classList.remove('d-none');
                return;
            }

            if (!phone) {
                errorMsg.textContent   = 'Contact number is required.';
                errorBox.classList.remove('d-none');
                return;
            }

            var cleanPhone = phone.replace(/[\s\-\(\)]/g, '');
            if (!/^\d{10,15}$/.test(cleanPhone)) {
                errorMsg.textContent   = 'Please enter a valid contact number (10-15 digits).';
                errorBox.classList.remove('d-none');
                return;
            }

            if (password !== confirm) {
                errorMsg.textContent   = 'Passwords do not match.';
                errorBox.classList.remove('d-none');
                return;
            }

            if (password.length < 6) {
                errorMsg.textContent   = 'Password must be at least 6 characters.';
                errorBox.classList.remove('d-none');
                return;
            }

            formData.append('action', 'signup');
            formData.append('name', name);
            formData.append('phone', cleanPhone);
        } else {
            formData.append('action', 'login');
        }

        submit.disabled = true;
        submit.textContent = isSignUp ? 'Creating Account...' : 'Signing In...';

        fetch(loginPageConfig.authUrl, {
            method: 'POST',
            body: formData
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                successMsg.textContent   = data.message;
                successBox.classList.remove('d-none');
                errorBox.classList.add('d-none');

                setTimeout(function () {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                errorMsg.textContent   = data.message;
                errorBox.classList.remove('d-none');
                successBox.classList.add('d-none');
                submit.disabled = false;
                submit.textContent = isSignUp ? 'Sign Up' : 'Sign In';
            }
        })
        .catch(function () {
            errorMsg.textContent   = 'Something went wrong. Please try again.';
            errorBox.classList.remove('d-none');
            submit.disabled = false;
            submit.textContent = isSignUp ? 'Sign Up' : 'Sign In';
        });
    });

    var togglePass = document.querySelector('.toggle-pass');
    if (togglePass) {
        togglePass.addEventListener('click', function () {
            var input  = document.getElementById('auth-password');
            var icon   = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    }
});
