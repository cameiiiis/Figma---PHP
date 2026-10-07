// Show or hide the password when its button is clicked.
var buttons = document.querySelectorAll('.password-toggle');

buttons.forEach(function (button) {
    button.hidden = false;

    button.addEventListener('click', function () {
        var input = document.getElementById(button.getAttribute('aria-controls'));
        var showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
        var label = document.querySelector('label[for="' + input.id + '"]').textContent;
        button.setAttribute('aria-label', (showPassword ? 'Hide ' : 'Show ') + label.toLowerCase());
    });
});
