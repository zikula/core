{adminheader}
<h3>
    <span class="fa fa-exclamation-triangle"></span>
    {gt text='Error! Could not regenerate the modules list.'}
</h3>

<p class="alert alert-danger">{gt text='Error! The modules list could not be regenerated because there are one or more problems in the Zikula file system. You need to correct them before you can proceed. Please read this explanation:'}</p>
{if $errors_modulenames}
<p>{gt text="In the table below, the left-hand column lists the internal name of a module that has duplicate instances present in different directories (the module is seen as being 'present more than one time' in the file system). The center and right-hand columns list the directories concerned (which are inside Zikula's '/modules' directory). Please delete any duplicate copy of the listed module(s), leaving only one instance. Each module internal name can only be associated with one single instance of a module."}</p>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text='Name'}</th>
            <th>{gt text='Directory'}</th>
            <th>{gt text='Directory'}</th>
        </tr>
    </thead>
    <tbody>
        {section name='modules' loop=$errors_modulenames}
        <tr>
            <td>{$errors_modulenames[modules].name|safetext}</td>
            <td>{$errors_modulenames[modules].dir1|safetext}</td>
            <td>{$errors_modulenames[modules].dir2|safetext}</td>
        </tr>
        {/section}
    </tbody>
</table>
{/if}

{if $errors_displaynames}
<p>{gt text="In the table below, the left-hand column lists the display name of a module that has duplicate instances present in different directories (the module is seen as being 'present more than one time' in the file system). The center and right-hand columns list the directories concerned (which are inside Zikula's '/modules' directory). Please delete any duplicate copy of the listed module(s), leaving only one instance. Each module display name can only be associated with one single instance of a module."}</p>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text='Display name'}</th>
            <th>{gt text='Directory'}</th>
            <th>{gt text='Directory'}</th>
        </tr>
    </thead>
    <tbody>
        {section name='modules' loop=$errors_displaynames}
        <tr>
            <td>{$errors_displaynames[modules].name|safetext}</td>
            <td>{$errors_displaynames[modules].dir1|safetext}</td>
            <td>{$errors_displaynames[modules].dir2|safetext}</td>
        </tr>
        {/section}
    </tbody>
</table>
{/if}

{adminfooter}
