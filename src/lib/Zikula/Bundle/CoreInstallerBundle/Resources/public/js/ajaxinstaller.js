/**
 * Zikula.ZikulaCoreInstallerBundle:Install:ajaxinstaller.html.twig
 * ajaxinstaller.js
 *
 * jQuery based JS
 */

jQuery( document ).ready(function( $ ) {
    // the `stages` array is declared in the template
    var progressbar = 0;
    var percentage = (1 / stages.length) * 100;

    $("#begininstall").click(function() {
        $(this).addClass('disabled');
        $(this).bind('click', false);
        processStage(getNextStage())
    });

    function processStage(stagename) {
        if (stagename == 'finish') {
            finalizeUI();
            return;
        }
        var stageitem = $('#'+stagename);

        indicateStageStarted(stageitem);
        $.ajax({
            type: "POST",
            data: {
                stage: stagename
            },
            url: Routing.generate('ajaxinstall'),
            success: function(data, textStatus, jqXHR) {
                if (data.status == 1) {
                    indicateStageSuccessful(stageitem);
                } else {
                    indicateStageFailure(stageitem);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            },
            complete: function(jqXHR, textStatus) {
                indicateStageComplete(stageitem);
                var nextstage = getNextStage(stagename);
                updateProgressBar(nextstage);
                processStage(nextstage)
            }
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
        listitem.removeClass("text-primary").addClass("text-success");
        listitem.children('.success').show();
        listitem.find('i').addClass('fa-check-circle'); // spinner
    }

    function indicateStageFailure(listitem) {
        listitem.removeClass("text-primary").addClass("text-danger");
        listitem.children('.fail').show();
        listitem.find('i').addClass('fa-times-circle'); // spinner
    }

    function getNextStage(stagename) {
        if (typeof stagename == 'undefined') return stages[0];
        var key = stages.indexOf(stagename);
        return (key == -1) ? stages[0] : stages[++key];
    }

    function updateProgressBar(stagename) {
        progressbar = (stagename == 'finish') ? 100 : progressbar + percentage;
        $('#progress-bar').css('width', progressbar+'%');
        if (stagename == 'finish') {
            $('#progress-bar').removeClass('progress-bar-striped active');
        }
    }

    function finalizeUI() {
        $('li#finish').removeClass('text-muted').addClass('text-success');
        $('li#finish').children('i').removeClass('fa-circle-o').addClass('fa-check-circle');
        $('li#finish').children('.installed').show();
        $('#continueinstall').show();
    }
});