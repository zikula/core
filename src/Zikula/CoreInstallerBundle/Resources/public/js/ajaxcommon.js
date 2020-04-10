// Copyright Zikula, licensed MIT.

jQuery(document).ready(function ($) {
    var stages = $('#stageDefinitions').data('stages');

    var route;
    var progressbar = 0;
    var percentage = (1 / stages.length) * 100;

    $('#beginInstall').click(function() {
        route = 'ajaxinstall';
        $(this).addClass('disabled');
        $(this).bind('click', false);
        processStage(getNextStage())
    });
    $('#beginUpgrade').click(function() {
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
            if (true === data.status) {
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
        listitem.children('.pre').addClass('d-none');
        listitem.children('.during').removeClass('d-none');
        listitem.find('i').removeClass('fa-circle').addClass('fa-cog fa-spin'); // spinner
    }

    function indicateStageComplete(listitem) {
        listitem.find('i').removeClass('fa-cog fa-spin'); // spinner
        listitem.children('.during').addClass('d-none');
    }

    function indicateStageSuccessful(listitem) {
        listitem.removeClass('text-primary').addClass('text-success');
        listitem.children('.success').removeClass('d-none');
        listitem.find('i').addClass('fa-check-circle'); // complete & successful
    }

    function indicateStageFailure(listitem) {
        listitem.removeClass('text-primary').addClass('text-danger');
        listitem.children('.fail').removeClass('d-none');
        listitem.find('i').addClass('fa-times-circle'); // complete with failure
    }

    function getNextStage(stagename) {
        if ('undefined' == typeof stagename) return stages[0];
        var key = stages.indexOf(stagename);
        return -1 === key ? stages[0] : stages[++key];
    }

    function updateProgressBar(stagename) {
        progressbar = 'finish' === stagename ? 100 : progressbar + percentage;
        $('#progress-bar').css('width', progressbar + '%');
        if ('finish' === stagename) {
            $('#progress-bar').removeClass('progress-bar-striped active');
        }
    }

    function finalizeUI() {
        $('li#finish').removeClass('text-muted').addClass('text-success');
        $('li#finish').children('i').removeClass('fa-circle').addClass('fa-check-circle');
        $('#continueButton').removeClass('d-none');
    }

    function getResultTable(resultArray) {
        var table = '<table class="table"><thead><tr><th>Item</th><th>Value</th></tr></thead><tbody>';
        var index;
        for (index = 0; index < resultArray.length; ++index) {
            table += '<tr><td>' + resultArray[index][0] + '</td><td>' + resultArray[index][1] + '</td></tr>';
        }
        table += '</tbody></table>';

        return table;
    }
});
