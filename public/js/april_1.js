var secretKeys = [
    'trybot',
    'jake is the best',
    'impeach trump'
];

var knownUnlocks = 0;
localStorage.getItem('knownUnlocks');


$(document).ready(function() {
    $('.div-input input').focus();
    $("#frmGuess").on("submit", function(event) {
        event.preventDefault();

        $('#guessResult').html('').removeClass();

        if ($('#frmGuess input').val().length == 0) {
            return;
        }
        $.post('/guess', {
            guess: $('#frmGuess input').val()
        }, function(data, textStatus, xhr) {
            console.debug(data);
            // Clear stuff
            $('.char').html('&nbsp;');
            $('#frmGuess input').val('');

            // Set stuff
            if (typeof data.blocks != 'undefined' && data.correctGuess == true) {
                $('#guessResult').html('Correct!').addClass('right');
                if (data.alreadyGuessed == true) {
                    $('#guessResult').append(" (but you weren't first)");
                }
                else {
                    lightFuse();
                }
            }
            else {
                $('#guessResult').html(':( nope').addClass('wrong');
            }
            if (typeof data.blocks != 'undefined') {
                $.each(data.blocks, function(k, v) {
                    var chars = v.split('');
                    $.each(chars, function(i, c) {
                        $('#block' + k + ' .char.' + (i + 1)).html(c);
                    });
                });
            }
            if (typeof data.url != 'undefined') {
                console.debug(data.url);
                console.debug('<a href="' + data.url + '" target="_blank">' + data.url + '</a>');
                $('#extraInfo').html('<a href="' + data.url + '" target="_blank">' + data.url + '</a>');
            }

        });
    });
});


var Konami = function(callback) {
    var konami = {
        addEvent: function(obj, type, fn, ref_obj) {
            if (obj.addEventListener)
                obj.addEventListener(type, fn, false);
            else if (obj.attachEvent) {
                // IE
                obj["e" + type + fn] = fn;
                obj[type + fn] = function() {
                    obj["e" + type + fn](window.event, ref_obj);
                };
                obj.attachEvent("on" + type, obj[type + fn]);
            }
        },
        input: "",
        pattern: "38384040373937396665",
        load: function(link) {
            this.addEvent(document, "keydown", function(e, ref_obj) {
                if (ref_obj) konami = ref_obj; // IE
                konami.input += e ? e.keyCode : event.keyCode;
                if (konami.input.length > konami.pattern.length)
                    konami.input = konami.input.substr((konami.input.length - konami.pattern.length));
                if (konami.input == konami.pattern) {
                    konami.code(link);
                    konami.input = "";
                    e.preventDefault();
                    return false;
                }
            }, this);
            this.iphone.load(link);
        },
        code: function(link) {
            window.location = link;
        },
        iphone: {
            start_x: 0,
            start_y: 0,
            stop_x: 0,
            stop_y: 0,
            tap: false,
            capture: false,
            orig_keys: "",
            keys: ["UP", "UP", "DOWN", "DOWN", "LEFT", "RIGHT", "LEFT", "RIGHT", "TAP", "TAP"],
            code: function(link) {
                konami.code(link);
            },
            load: function(link) {
                this.orig_keys = this.keys;
                konami.addEvent(document, "touchmove", function(e) {
                    if (e.touches.length == 1 && konami.iphone.capture == true) {
                        var touch = e.touches[0];
                        konami.iphone.stop_x = touch.pageX;
                        konami.iphone.stop_y = touch.pageY;
                        konami.iphone.tap = false;
                        konami.iphone.capture = false;
                        konami.iphone.check_direction();
                    }
                });
                konami.addEvent(document, "touchend", function(evt) {
                    if (konami.iphone.tap == true) konami.iphone.check_direction(link);
                }, false);
                konami.addEvent(document, "touchstart", function(evt) {
                    konami.iphone.start_x = evt.changedTouches[0].pageX;
                    konami.iphone.start_y = evt.changedTouches[0].pageY;
                    konami.iphone.tap = true;
                    konami.iphone.capture = true;
                });
            },
            check_direction: function(link) {
                x_magnitude = Math.abs(this.start_x - this.stop_x);
                y_magnitude = Math.abs(this.start_y - this.stop_y);
                x = ((this.start_x - this.stop_x) < 0) ? "RIGHT" : "LEFT";
                y = ((this.start_y - this.stop_y) < 0) ? "DOWN" : "UP";
                result = (x_magnitude > y_magnitude) ? x : y;
                result = (this.tap == true) ? "TAP" : result;

                if (result == this.keys[0]) this.keys = this.keys.slice(1, this.keys.length);
                if (this.keys.length == 0) {
                    this.keys = this.orig_keys;
                    this.code(link);
                }
            }
        }
    }

    typeof callback === "string" && konami.load(callback);
    if (typeof callback === "function") {
        konami.code = callback;
        konami.load();
    }

    return konami;
};

var c = new Konami('https://reddit.com/r/place');