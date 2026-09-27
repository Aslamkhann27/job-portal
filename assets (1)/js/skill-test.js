document.addEventListener('DOMContentLoaded', function () {
    var timerEl = document.getElementById('testTimer');
    var form = document.getElementById('skillTestForm');

    if (!timerEl || !form) {
        return;
    }

    var remaining = parseInt(timerEl.dataset.seconds || '300', 10);
    if (!remaining || remaining < 1) {
        remaining = 300;
    }

    function formatTime(seconds) {
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    function render() {
        timerEl.textContent = 'Time Left: ' + formatTime(remaining);
    }

    render();

    var interval = window.setInterval(function () {
        remaining -= 1;
        if (remaining <= 0) {
            clearInterval(interval);
            timerEl.textContent = 'Time Left: 00:00';
            form.submit();
            return;
        }
        render();
    }, 1000);
});
