// Copyright Zikula Foundation, licensed MIT.

jQuery( document ).ready(function( $ ) {
    // the `stages` array is declared in the template
    var route;
    var progressbar = 0;
    var percentage = (1 / stages.length) * 100;

    $('#begininstall').click(function() {
        route = 'ajaxinstall';
        $(this).addClass('disabled');
        $(this).bind('click', false);
        processStage(getNextStage())
    });
    $('#beginupgrade').click(function() {
        route = 'ajaxupgrade';
        $(this).addClass('disabled');
        $(this).bind('click', false);
        processStage(getNextStage())
    });

    function processStage(stagename) {
        if ('finish' === stagename) {
            finalizeUI();
            return;
        }
        var stageitem = $('#' + stagename);

        indicateStageStarted(stageitem);
        $.ajax({
            type: 'POST',
            url: Routing.generate(route),
            data: {
                stage: stagename
            }
        }).done(function (data, textStatus, jqXHR) {
            if (1 === data.status) {
                indicateStageSuccessful(stageitem);
            } else {
                indicateStageFailure(stageitem);
            }
            if ((typeof data.results !== 'undefined') && (data.results.length > 0)) {
                stageitem.append(getResultTable(data.results));
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            indicateStageFailure(stageitem);
            alert(jqXHR.responseText);
        }).always(function(jqXHR, textStatus) {
            indicateStageComplete(stageitem);
            var nextstage = getNextStage(stagename);
            updateProgressBar(nextstage);
            processStage(nextstage)
        });
    }

    function indicateStageStarted(listitem) {
        listitem.removeClass('text-muted').addClass('text-primary');
        listitem.children('.pre').hide();
        listitem.children('.during').show();
        listitem.find('i').removeClass('fa-circle-o').addClass('fa-cog fa-spin'); // spinner
    }

    function indicateStageComplete(listitem) {
        listitem.find('i').removeClass('fa-cog fa-spin'); // spinner
        listitem.children('.during').hide();
    }

    function indicateStageSuccessful(listitem) {
        listitem.removeClass('text-primary').addClass('text-success');
        listitem.children('.success').show();
        listitem.find('i').addClass('fa-check-circle'); // spinner
    }

    function indicateStageFailure(listitem) {
        listitem.removeClass('text-primary').addClass('text-danger');
        listitem.children('.fail').show();
        listitem.find('i').addClass('fa-times-circle'); // spinner
    }

    function getNextStage(stagename) {
        if (typeof stagename == 'undefined') return stages[0];
        var key = stages.indexOf(stagename);
        return (-1 === key) ? stages[0] : stages[++key];
    }

    function updateProgressBar(stagename) {
        progressbar = ('finish' === stagename) ? 100 : progressbar + percentage;
        $('#progress-bar').css('width', progressbar + '%');
        if ('finish' === stagename) {
            $('#progress-bar').removeClass('progress-bar-striped active');
        }
    }

    function finalizeUI() {
        $('li#finish').removeClass('text-muted').addClass('text-success');
        $('li#finish').children('i').removeClass('fa-circle-o').addClass('fa-check-circle');
        $('#continuebutton').show();
    }

    function getResultTable(resultArray) {
        var table = '<table><thead><tr><th>Item</th><th>Value</th></tr></thead><tbody>';
        var index;
        for (index = 0; index < resultArray.length; ++index) {
            table += '<tr><td>'+resultArray[index][0]+'</td><td>'+resultArray[index][1]+'</td></tr>';
        }
        table += '</tbody></table>';

        return table;
    }
});
