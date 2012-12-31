(function($){
    // admin help and 'Messages you might see'
    Zikula.Core.when('admin-admin-help').then(function(){
        $('.admin-help').zPanels();
    });

    Zikula.Core.when('admin-admin-modifyconfig').then(function(){
        $('#admin_ignoreinstallercheck_warning').zDisplayWhen('#admin_ignoreinstallercheck', true);
    });

    Zikula.Core.when('admin-admin-view').then(function(){
        $('.tooltips').tooltip()
    });

})(jQuery);