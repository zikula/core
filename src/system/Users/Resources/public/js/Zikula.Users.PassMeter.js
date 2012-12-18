// Copyright Zikula Foundation 2010 - license GNU/LGPLv3 (or at your option, any later version).
Zikula.define('Users');

Zikula.Users._PassMeter = Class.create({
    initialize: function(options) {
        this.score = {};
        this.options = Object.extend({
            minLength: 8,
            scores: [20, 35, 45, 60],
            verdicts: ['Weak', 'Normal', 'Medium', 'Strong', 'Very Strong'],
            raisePower: 1.4,
            dseq: $A($R(1,9)).join(''),
            lseq: $A($R('a','z')).join('')
        }, options || { });
    },
    dictionary: ['password','qwerty'],
    restrictions: {
        minLength: {
            test: function(word){return word.length === 0 || word.length>=this.options.minLength;},
            msg:'Password is to short'
        }
    },
    ruleScores: {
        length: 0,
        repetitions: -1,
        sequences: -100,
        dictionary: -100,
        lowercase: 1,
        uppercase: 3,
        one_number: 3,
        three_numbers: 5,
        one_special_char: 3,
        two_special_char: 5,
        upper_lower_combo: 2,
        letter_number_combo: 2,
        letter_number_char_combo: 2
    },
    rules: {
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
    },
    validationRules: {
        length: function (word, score) {
            return Math.pow(word.length, this.options.raisePower);
        },
        repetitions: function (word, score) {
            return Math.pow(word.length-word.replace(/(.+)(?=\1+)/g,'').length, this.options.raisePower*0.9) * score;
        },
        sequences: function (word, score) {
            return (this.options.dseq.include(word) || this.options.lseq.include(word)) && score;
        },
        dictionary: function (word, score) {
            return $A(this.dictionary).include(word) && score;
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
    },
    addRule: function (name, method, score, active) {
        this.rules[name] = active;
        this.ruleScores[name] = score;
        this.validationRules[name] = method;
        return true;
    },
    calculate: function (word) {
        this.score = {
            totalscore: 0,
            level: 0,
            percent: 0,
            verdict: null,
            word: word,
            messages: {}
        };
        for (var rule in this.rules) if (this.rules.hasOwnProperty(rule)) {
            if (this.rules[rule] === true) {
                var score = this.ruleScores[rule],
                    result = this.validationRules[rule].bind(this)(word, score);
                if (!isNaN(result)) {
                    this.score.totalscore += result;
                }
            }
        }
        for (var restriction in this.restrictions) if (this.restrictions.hasOwnProperty(restriction)) {
            if(Object.isFunction(this.restrictions[restriction].test) && !this.restrictions[restriction].test.bind(this)(word)) {
                this.score.messages[restriction] = this.restrictions[restriction].msg || true;
            } else{
                this.score.messages[restriction] = false;
            }
        }
        this.score.totalscore = this.score.totalscore < 0 ? 0 : this.score.totalscore.round();
        this.options.scores.each(function(e,i){
            if(this.score.totalscore>e) {
                this.score.level = i+1;
            }
        }.bind(this));
        this.score.verdict = this.options.verdicts[this.score.level];
        this.score.percent = (this.score.totalscore/this.options.scores.max())*100;
        this.score.percent = this.score.percent > 100 ? 100 : this.score.percent.round();

        return this.score;
    }
});

Zikula.Users.PassMeter = Class.create({
    initialize: function(passwordElementId, visualizationElementId, options) {
        this.passwordInput = $(passwordElementId);
        if (Object.isElement($(visualizationElementId))) {
            this.visualizationDiv = $(visualizationElementId);
        } else {
            this.visualizationDiv = false;
            options = visualizationElementId;
        }
        this.options = Object.extend({
            username: false,
            onChange: false,
            messages: {},
            colors:  ["#ff0000", "#FFCC33", "#00FF00", "#008000"],
            scores: [20, 40, 60],
            verdicts: [Zikula.__('Weak'), Zikula.__('Normal'), Zikula.__('Strong'), Zikula.__('Very Strong')],
            autoRun: true
        }, options || { });
        this.options.messages = Object.extend({
            minLength: Zikula.__f('The minimum length for user passwords is %s characters.', this.options.minLength)
        },this.options.messages);
        this.calulator = new Zikula.Users._PassMeter(this.options);
        if(Object.isElement($(this.options.username))) {
            this.calulator.restrictions.username = {
                test: function(word) {return word ==='' || word != $F(this.options.username);},
                msg: Zikula.__('Password can not match the username, choose a different password.')
            };
        }
        if (this.options.autoRun) {
            this.start();
        }
    },
    start: function() {
        if(!this.options.onChange) {
            this.prepareVisualisation();
        }
        this.passwordInput.observe('keyup',this.onChange.bindAsEventListener(this));
        this.onChange();
    },
    onChange: function() {
        this.score = this.calulator.calculate(this.passwordInput.getValue());
        this.score.messagesStr = [];
        for (var msg in this.score.messages) if (this.score.messages.hasOwnProperty(msg)) {
            if (this.score.messages[msg]) {
                if(this.options.messages[msg]) {
                    this.score.messages[msg] = this.options.messages[msg];
                }
                this.score.messagesStr.push(this.score.messages[msg]);
            }
        }
        this.score.messagesStr.join();
        if(Object.isFunction(this.options.onChange)) {
            this.options.onChange(this.score);
        } else {
            this.passindicatorContainer.show();
            this.passindicatorBar.setStyle({
                width: (this.score.percent < 5 ? 5 : this.score.percent) + '%',
                backgroundColor: this.options.colors[this.score.level],
                backgroundPosition: '0 ' + this.score.percent+ '%'
            });
            this.passindicatorScore.update(this.score.verdict + ' ('+this.score.percent+'%)');
            this.passindicatorMsg.update(this.score.messagesStr);
        }
    },
    prepareVisualisation: function() {
        this.passindicatorContainer = new Element('div',{'class':'z-formnote passindicator'}).hide();
        this.passindicatorBarContainer = new Element('div',{'class':'passindicatorbarcontainer'}).setStyle({width:'200px'});
        this.passindicatorBar = new Element('div',{'class':'passindicatorbar'}).setStyle({
                backgroundColor: this.options.colors[0],
                backgroundPosition: '0 0'
        });
        this.passindicatorScore = new Element('div',{'class':'passindicatorscore'});
        this.passindicatorMsg = new Element('div',{'class':'passindicatormsg'});
        if (this.visualizationDiv) {
            this.visualizationDiv.insert({
                top: this.passindicatorContainer.insert(this.passindicatorScore).insert(this.passindicatorBarContainer).insert(this.passindicatorMsg)
            });
        } else {
            this.passwordInput.insert({
                after: this.passindicatorContainer.insert(this.passindicatorScore).insert(this.passindicatorBarContainer).insert(this.passindicatorMsg)
            });
        }
        this.passindicatorBarContainer.insert(this.passindicatorBar);
    }
});