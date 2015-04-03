{pageaddvar name='javascript' value='jquery'}
{pageaddvar name="javascript" value='system/Zikula/Module/SecurityCenterModule/Resources/public/js/securitycenter_admin_allowedhtm.js'}
{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text='Allowed HTML settings'}
</h3>

<p class="alert alert-info">{gt text='Filtering of allowed HTML occurs when a template string or variable is modified with the \'safehtml\' modifier, or when a module asks for similar processing from within its functions.'}</p>
<form class="form-horizontal" role="form" action="{route name='zikulasecuritycentermodule_admin_updateallowedhtml'}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='HTML entities'}</legend>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <div class="form-group" id="securitycenter_htmlentities">
            <div class="col-sm-12">
                <label>{gt text='Translate embedded HTML entities into real characters'}</label>
                <input id="securitycenter_htmlentities_yes" type="radio" name="xhtmlentities" value="1"{if $htmlentities eq 1} checked="checked"{/if} />
                <label for="securitycenter_htmlentities_yes">{gt text='Yes'}</label>
                <input id="securitycenter_htmlentities_n0" type="radio" name="xhtmlentities" value="0"{if $htmlentities ne 1} checked="checked"{/if} />
                <label for="securitycenter_htmlentities_n0">{gt text='No'}</label>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='HTML tags'}</legend>
        <div class="alert alert-warning">
            <p>{gt text="Warning! Enabling the &lt;img&gt;, &lt;span&gt;, &lt;marquee&gt;, &lt;script&gt;, &lt;embed&gt;, &lt;object&gt; or &lt;iframe&gt; tags increases the opportunity for attacks against your users that might reveal their personal information. Therefore, you are recommended to keep these tags set to 'Not allowed' unless you are sure that you really understand the consequences of enabling them."}</p>
            {if $htmlpurifier}<p>{gt text='Warning! Using the <a href="%s">HTML Purifier output filter</a> will override settings for some HTML tags (such as &lt;object&gt; and &lt;embed&gt;).' tag1=$configurl|safetext}</p>{/if}
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{gt text='Tag'}</th>
                    <th>
                        <label for="toggle_notallowed" title="{gt text='Check all'}">{gt text='Not allowed'}</label>
                        <input name="radiotoggle" id="toggle_notallowed" type="radio" value="0" />
                    </th>
                    <th>
                        <label for="toggle_allowed" title="{gt text='Check all'}">{gt text='Allowed'}</label>
                        <input name="radiotoggle" id="toggle_allowed" type="radio" value="1" />
                    </th>
                    <th>
                        <label for="toggle_allowedwith" title="{gt text='Check all'}">{gt text='Allowed with attributes'}</label>
                        <input name="radiotoggle" id="toggle_allowedwith" type="radio" value="2" />
                    </th>
                    <th>{gt text='Tag usage (from <a href="http://www.w3schools.com">W3Schools</a>)'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach key='htmltag' item='usagetag' from=$htmltags}
                <tr>
                    <td>&lt;{$htmltag|safetext}&gt;</td>
                    <td><input class="notallowed_radio" type="radio" value="0" name="htmlallow{$htmltag|safetext}tag" {if (isset($currenthtmltags.$htmltag) && $currenthtmltags.$htmltag eq 0) or !isset($currenthtmltags.$htmltag)} checked="checked"{/if} /></td>
                    <td><input class="allowed_radio" type="radio" value="1" name="htmlallow{$htmltag|safetext}tag" {if isset($currenthtmltags.$htmltag) && $currenthtmltags.$htmltag eq 1} checked="checked"{/if} /></td>
                    <td><input class="allowedwith_radio" type="radio" value="2" name="htmlallow{$htmltag|safetext}tag" {if isset($currenthtmltags.$htmltag) && $currenthtmltags.$htmltag eq 2} checked="checked"{/if} /></td>
                    <td>{if !empty($usagetag)}<a href="{$usagetag}">{gt text='About "&lt;%s&gt;"' tag1=$htmltag}</a>{/if}</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
            <a class="btn btn-danger" href="{route name='zikulasecuritycentermodule_admin_index'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}
