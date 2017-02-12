$('#modern').bind('input', function() {
    $(this).next().stop(true, true).fadeIn(0).html('[input event fired!]: ' + $(this).val()).fadeOut(2000);
});

$('#kbInput').keyup(function(e) {
    $(this).next().stop(true, true).fadeIn(0).html('[keyup event fired (keycode: ' + e.keyCode + ', char: ' + String.fromCharCode(e.keyCode) + ')!]: ' + $(this).val()).fadeOut(2000);
});

$('#timer').focus(function() {
    // turn on timer
    startTimer();
}).blur(function() {
    // turn off timer
    endTimer();
});

var lastValue = "",
    $timer = $('#timer'),
    timerCheckCount = 0,
    checkInputChange = function() {
        timerCheckCount += 1;
        if (lastValue !== $timer.val()) {
            $timer.next().stop(true, true).fadeIn(0).html('[timer detected change (timer has fired ' + timerCheckCount + ' times!]: ' + $timer.val()).fadeOut(2000);
            lastValue = $timer.val();
        }
    },
    timer = undefined,
    startTimer = function() {
        timer = setInterval(checkInputChange, 200); // check input field every 200 ms (1/5 sec)
    },
    endTimer = function() {
        clearInterval(timer);
        timerCheckCount = 0;
    };
