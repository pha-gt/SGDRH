$(document).ready(function () {
    $('.input_group').on('click', '.togglePassword', function () {
        const $passwordInput = $(this).prev('input');
        const type = $passwordInput.attr('type') === 'password' ? 'text' : 'password';
        $passwordInput.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });
});