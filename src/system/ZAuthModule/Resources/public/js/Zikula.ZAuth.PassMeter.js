// Copyright Zikula Foundation, licensed MIT.

var ZikulaZAuthPassMeter = {};
var ZikulaZAuthPassCalc = {};

var currentCalculator;

( function($) {

    ZikulaZAuthPassCalc.initialize = function(options) {
        this.score = {};
        this.options = $.extend({
            minLength: 8,
            scores: [20, 35, 45, 60],
            verdicts: ['Weak', 'Normal', 'Medium', 'Strong', 'Very Strong'],
            raisePower: 1.4,
            dseq: '123456789'.split(''),
            lseq: 'abcdefghijklmnopqrstuvwxyz'.split('')
        }, options || { });

        return this;
    };
    ZikulaZAuthPassCalc.dictionary = ['password', 'qwerty'];
    ZikulaZAuthPassCalc.restrictions = {
        minLength: {
            test: function(word) {
                return word.length === 0 || word.length >= this.options.minLength;
            },
            msg: 'Password is too short'
        }
    };
    ZikulaZAuthPassCalc.ruleScores = {
        length: 0,
        repetitions: -1,
        sequences: 1/*-100*/,
        dictionary: 1/*-100*/,
        lowercase: 1,
        uppercase: 3,
        one_number: 3,
        three_numbers: 5,
        one_special_char: 3,
        two_special_char: 5,
        upper_lower_combo: 2,
        letter_number_combo: 2,
        letter_number_char_combo: 2
    };
    ZikulaZAuthPassCalc.rules = {
        length: true,
        repetitions: true,
        sequences: true,
        dictionary: true,
        lowercase: true,
        uppercase: true,
        one_number: true,
        three_numbers: true,
        one_special_char: true,
        two_special_char: true,
        upper_lower_combo: true,
        letter_number_combo: true,
        letter_number_char_combo: true
    };
    ZikulaZAuthPassCalc.validationRules = {
        length: function (word, score) {
            return Math.pow(word.length, this.options.raisePower);
        },
        repetitions: function (word, score) {
            return Math.pow(word.length-word.replace(/(.+)(?=\1+)/g,'').length, this.options.raisePower*0.9) * score;
        },
        sequences: function (word, score) {
            return ($.inArray(word, this.options.dseq) || $.inArray(word, this.options.lseq)) && score;
        },
        dictionary: function (word, score) {
            return $.inArray(word, this.dictionary) && score;
        },
        lowercase: function (word, score) {
            return word != word.toLocaleUpperCase() && score;
        },
        uppercase: function (word, score) {
            return word != word.toLocaleLowerCase() && score;
        },
        one_number: function (word, score) {
            return word.match(/\d+/) && score;
        },
        three_numbers: function (word, score) {
            return word.match(/(.*\d.*\d.*\d)/) && score;
        },
        one_special_char: function (word, score) {
            return word.match(/.[!,@,#,$,%,\^,&,*,?,_,~]/) && score;
        },
        two_special_char: function (word, score) {
            return word.match(/(.*[!,@,#,$,%,\^,&,*,?,_,~].*[!,@,#,$,%,\^,&,*,?,_,~])/) && score;
        },
        upper_lower_combo: function (word, score) {
            return word != word.toLocaleUpperCase() && word != word.toLocaleLowerCase() && score;
        },
        letter_number_combo: function (word, score) {
            return word.match(/([a-zA-Z])/) && word.match(/([0-9])/) && score;
        },
        letter_number_char_combo : function (word, score) {
            return word.match(/([a-zA-Z0-9].*[!,@,#,$,%,\^,&,*,?,_,~])|([!,@,#,$,%,\^,&,*,?,_,~].*[a-zA-Z0-9])/) && score;
        }
    };
    ZikulaZAuthPassCalc.calculate = function (word) {
        var score = {
            totalscore: 0,
            level: 0,
            percent: 0,
            verdict: null,
            word: word,
            messages: {}
        };
        for (var rule in this.rules) {
            if (ZikulaZAuthPassCalc.rules.hasOwnProperty(rule)) {
                if (ZikulaZAuthPassCalc.rules[rule] === true) {
                    var scoreTmp = ZikulaZAuthPassCalc.ruleScores[rule];
                    var result = ZikulaZAuthPassCalc.validationRules[rule].bind(this)(word, scoreTmp);
                    if (!isNaN(result)) {
                        score.totalscore += result;
                    }
                }
            }
        }
        for (var restriction in ZikulaZAuthPassCalc.restrictions) {
            if (ZikulaZAuthPassCalc.restrictions.hasOwnProperty(restriction)) {
                if (typeof ZikulaZAuthPassCalc.restrictions[restriction].test === 'function' && !ZikulaZAuthPassCalc.restrictions[restriction].test.bind(this)(word)) {
                    score.messages[restriction] = ZikulaZAuthPassCalc.restrictions[restriction].msg || true;
                } else {
                    score.messages[restriction] = false;
                }
            }
        }
        score.totalscore = score.totalscore < 0 ? 0 : Math.round(score.totalscore);

        var maxValue = 0;
        $.each(this.options.scores, function(i, e) {
            if (score.totalscore > e) {
                score.level = i + 1;
            }
            if (e > maxValue) {
                maxValue = e;
            }
        }.bind(this));

        score.verdict = this.options.verdicts[score.level];
        score.percent = 0;
        if (maxValue > 0) {
            score.percent = (score.totalscore / maxValue) * 100;
        }
        score.percent = score.percent > 100 ? 100 : Math.round(score.percent);

        return score;
    };

    ZikulaZAuthPassMeter.init = function(passwordElementId, visualizationElementId, options) {
        var passwordInput = $('#' + passwordElementId);
        var visualizationDiv = false;
        if ($('#' + visualizationElementId).length > 0) {
            visualizationDiv = $('#' + visualizationElementId);
        } else {
            options = visualizationElementId;
        }
        options = $.extend({
            username: false,
            onChange: false,
            messages: {},
            colors:  ['#ff0000', '#FFCC33', '#00FF00', '#008000'],
            scores: [20, 40, 60],
//             verdicts: [Zikula.__('Weak'), Zikula.__('Normal'), Zikula.__('Strong'), Zikula.__('Very Strong')],
            verdicts: ['Weak', 'Normal', 'Strong', 'Very Strong']
        }, options || { });
        options.messages = $.extend({
//             minLength: Zikula.__f('The minimum length for user passwords is %s characters.', options.minLength)
            minLength: 'The minimum length for user passwords is ' + options.minLength + ' characters.'
        }, options.messages);
        currentCalculator = ZikulaZAuthPassCalc.initialize(options);
        if ($('#' + options.username).length > 0) {
            ZikulaZAuthPassCalc.restrictions.username = {
                test: function(word) {
                    return word ==='' || word != $('#' + options.username).val();
                },
//                 msg: Zikula.__('Password can not match the username, choose a different password.')
                msg: 'Password can not match the username, choose a different password.'
            };
        }
        if (!options.onChange) {
            // prepare visualisation
            var passindicatorContainer = $('<div>').attr('class', 'help-block passindicator').hide();
            var passindicatorBarContainer = $('<div>').attr('class', 'passindicatorbarcontainer').css({ width: '200px' });
            var passindicatorBar = $('<div>').attr('class', 'passindicatorbar').css({
                    backgroundColor: options.colors[0],
                    backgroundPosition: '0 0',
                    height: '3px'
            });
            var passindicatorScore = $('<div>').attr('class', 'passindicatorscore');
            var passindicatorMsg = $('<div>').attr('class', 'passindicatormsg');

            var content = passindicatorContainer.append(passindicatorScore).append(passindicatorBarContainer).append(passindicatorMsg);

            if (visualizationDiv) {
                visualizationDiv.prepend(content);
            } else {
                passwordInput.insertAfter(content);
            }
            passindicatorBarContainer.append(passindicatorBar);
        }
        passwordInput.on('keyup', function() {
            var score = currentCalculator.calculate(passwordInput.val());
            score.messagesStr = [];
            for (var msg in score.messages) {
                if (score.messages.hasOwnProperty(msg)) {
                    if (score.messages[msg]) {
                        if (options.messages[msg]) {
                            score.messages[msg] = options.messages[msg];
                        }
                        score.messagesStr.push(score.messages[msg]);
                    }
                }
            }
            score.messagesStr.join();
            if (typeof options.onChange === 'function') {
                options.onChange(score);
            } else {
                passindicatorContainer.show();
                passindicatorBar.css({
                    width: (score.percent < 5 ? 5 : score.percent) + '%',
                    backgroundColor: options.colors[score.level],
                    backgroundPosition: '0 ' + score.percent+ '%'
                });
                passindicatorScore.html(score.verdict + ' (' + score.percent + '%)');
                passindicatorMsg.html(score.messagesStr);
            }
        });
        passwordInput.trigger('keyup');
    };
})(jQuery);
