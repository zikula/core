{* Purpose of this template: Display a popup selector of routes for scribite integration *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" lang="{lang}">
<head>
    <title>{gt text='Search and select route'}</title>
    <link type="text/css" rel="stylesheet" href="{$baseurl}style/core.css" />
    <link type="text/css" rel="stylesheet" href="{$baseurl}system/Resources/public/css/style.css" />
    <link type="text/css" rel="stylesheet" href="{$baseurl}system/Resources/public/css/finder.css" />
    {assign var='ourEntry' value=$modvars.ZConfig.entrypoint}
    <script type="text/javascript">/* <![CDATA[ */
        if (typeof(Zikula) == 'undefined') {var Zikula = {};}
        Zikula.Config = {'entrypoint': '{{$ourEntry|default:'index.php'}}', 'baseURL': '{{$baseurl}}'}; /* ]]> */</script>
        <link rel="stylesheet" href="web/bootstrap/css/bootstrap.min.css" type="text/css" />
        <link rel="stylesheet" href="web/bootstrap/css/bootstrap-theme.css" type="text/css" />
        <script type="text/javascript" src="web/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="web/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{$baseurl}system/RoutesModule/Resources/public/js/ZikulaRoutesModule.Finder.js"></script>
</head>
<body>
    <form action="{$ourEntry|default:'index.php'}" id="zikulaRoutesModuleSelectorForm" method="get" class="form-horizontal" role="form">
    <div>
        <input type="hidden" name="module" value="ZikulaRoutesModule" />
        <input type="hidden" name="type" value="external" />
        <input type="hidden" name="func" value="finder" />
        <input type="hidden" name="objectType" value="{$objectType}" />
        <input type="hidden" name="editor" id="editorName" value="{$editorName}" />

        <fieldset>
            <legend>{gt text='Search and select route'}</legend>

            <div class="form-group">
                <label for="zikulaRoutesModulePasteAs" class="col-lg-3 control-label">{gt text='Paste as'}:</label>
                <div class="col-lg-9">
                    <select id="zikulaRoutesModulePasteAs" name="pasteas" class="form-control">
                        <option value="1">{gt text='Link to the route'}</option>
                        <option value="2">{gt text='ID of route'}</option>
                    </select>
                </div>
            </div>
            <br />

            <div class="form-group">
                <label for="zikulaRoutesModuleObjectId" class="col-lg-3 control-label">{gt text='Route'}:</label>
                <div class="col-lg-9">
                    <div id="zikularoutesmoduleItemContainer">
                        <ul>
                        {foreach item='route' from=$items}
                            <li>
                                <a href="#" onclick="zikulaRoutesModule.finder.selectItem({$route.id})" onkeypress="zikulaRoutesModule.finder.selectItem({$route.id})">{$route->getTitleFromDisplayPattern()}</a>
                                <input type="hidden" id="url{$route.id}" value="" />
                                <input type="hidden" id="title{$route.id}" value="{$route->getTitleFromDisplayPattern()|replace:"\"":""}" />
                                <input type="hidden" id="desc{$route.id}" value="{capture assign='description'}{if $route.name ne ''}{$route.name}{/if}
                                {/capture}{$description|strip_tags|replace:"\"":""}" />
                            </li>
                        {foreachelse}
                            <li>{gt text='No entries found.'}</li>
                        {/foreach}
                        </ul>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="zikulaRoutesModuleSort" class="col-lg-3 control-label">{gt text='Sort by'}:</label>
                <div class="col-lg-9">
                    <select id="zikulaRoutesModuleSort" name="sort" style="width: 150px" class="pull-left" style="margin-right: 10px">
                    <option value="id"{if $sort eq 'id'} selected="selected"{/if}>{gt text='Id'}</option>
                    <option value="workflowState"{if $sort eq 'workflowState'} selected="selected"{/if}>{gt text='Workflow state'}</option>
                    <option value="name"{if $sort eq 'name'} selected="selected"{/if}>{gt text='Name'}</option>
                    <option value="bundle"{if $sort eq 'bundle'} selected="selected"{/if}>{gt text='Bundle'}</option>
                    <option value="controller"{if $sort eq 'controller'} selected="selected"{/if}>{gt text='Controller'}</option>
                    <option value="action"{if $sort eq 'action'} selected="selected"{/if}>{gt text='Action'}</option>
                    <option value="path"{if $sort eq 'path'} selected="selected"{/if}>{gt text='Path'}</option>
                    <option value="host"{if $sort eq 'host'} selected="selected"{/if}>{gt text='Host'}</option>
                    <option value="schemes"{if $sort eq 'schemes'} selected="selected"{/if}>{gt text='Schemes'}</option>
                    <option value="methods"{if $sort eq 'methods'} selected="selected"{/if}>{gt text='Methods'}</option>
                    <option value="defaults"{if $sort eq 'defaults'} selected="selected"{/if}>{gt text='Defaults'}</option>
                    <option value="requirements"{if $sort eq 'requirements'} selected="selected"{/if}>{gt text='Requirements'}</option>
                    <option value="options"{if $sort eq 'options'} selected="selected"{/if}>{gt text='Options'}</option>
                    <option value="condition"{if $sort eq 'condition'} selected="selected"{/if}>{gt text='Condition'}</option>
                    <option value="description"{if $sort eq 'description'} selected="selected"{/if}>{gt text='Description'}</option>
                    <option value="userRoute"{if $sort eq 'userRoute'} selected="selected"{/if}>{gt text='User route'}</option>
                    <option value="sort"{if $sort eq 'sort'} selected="selected"{/if}>{gt text='Sort'}</option>
                    <option value="group"{if $sort eq 'group'} selected="selected"{/if}>{gt text='Group'}</option>
                    <option value="createdDate"{if $sort eq 'createdDate'} selected="selected"{/if}>{gt text='Creation date'}</option>
                    <option value="createdUserId"{if $sort eq 'createdUserId'} selected="selected"{/if}>{gt text='Creator'}</option>
                    <option value="updatedDate"{if $sort eq 'updatedDate'} selected="selected"{/if}>{gt text='Update date'}</option>
                    </select>
                    <select id="zikulaRoutesModuleSortDir" name="sortdir" style="width: 100px" class="form-control">
                        <option value="asc"{if $sortdir eq 'asc'} selected="selected"{/if}>{gt text='ascending'}</option>
                        <option value="desc"{if $sortdir eq 'desc'} selected="selected"{/if}>{gt text='descending'}</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="zikulaRoutesModulePageSize" class="col-lg-3 control-label">{gt text='Page size'}:</label>
                <div class="col-lg-9">
                    <select id="zikulaRoutesModulePageSize" name="num" style="width: 50px; text-align: right" class="form-control">
                        <option value="5"{if $pager.itemsperpage eq 5} selected="selected"{/if}>5</option>
                        <option value="10"{if $pager.itemsperpage eq 10} selected="selected"{/if}>10</option>
                        <option value="15"{if $pager.itemsperpage eq 15} selected="selected"{/if}>15</option>
                        <option value="20"{if $pager.itemsperpage eq 20} selected="selected"{/if}>20</option>
                        <option value="30"{if $pager.itemsperpage eq 30} selected="selected"{/if}>30</option>
                        <option value="50"{if $pager.itemsperpage eq 50} selected="selected"{/if}>50</option>
                        <option value="100"{if $pager.itemsperpage eq 100} selected="selected"{/if}>100</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="zikulaRoutesModuleSearchTerm" class="col-lg-3 control-label">{gt text='Search for'}:</label>
            <div class="col-lg-9">
                    <input type="text" id="zikulaRoutesModuleSearchTerm" name="q" style="width: 150px" class="form-control pull-left" style="margin-right: 10px" />
                    <input type="button" id="zikulaRoutesModuleSearchGo" name="gosearch" value="{gt text='Filter'}" style="width: 80px" class="btn btn-default" />
            </div>
            </div>
            
            <div style="margin-left: 6em">
                {pager display='page' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='pos' template='pagercss.tpl' maxpages='10' route='zikularoutesmodule_external_finder'}
            </div>
            <input type="submit" id="zikulaRoutesModuleSubmit" name="submitButton" value="{gt text='Change selection'}" class="btn btn-success" />
            <input type="button" id="zikulaRoutesModuleCancel" name="cancelButton" value="{gt text='Cancel'}" class="btn btn-default" />
            <br />
        </fieldset>
    </div>
    </form>

    <script type="text/javascript">
    /* <![CDATA[ */
        ( function($) {
            $(document).ready(function() {
                zikulaRoutesModule.finder.onLoad();
            });
        })(jQuery);
    /* ]]> */
    </script>

    {*
    <div class="zikularoutesmodule-finderform">
        <fieldset>
            {modfunc modname='ZikulaRoutesModule' type='admin' func='edit'}
        </fieldset>
    </div>
    *}
</body>
</html>
