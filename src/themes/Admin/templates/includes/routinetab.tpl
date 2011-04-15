<div class="dashboard-content-head">
    <h2 class="left">{gt text='Routines'}</h2>
</div>
<div class="z-form" style="margin-top:8px;">
    <fieldset>
        <legend>{gt text='Compilation'}</legend>
            <ul>
                <li><a href="index.php?module=theme&amp;type=admin&amp;func=clear_compiled&amp;csrftoken={insert name='csrftoken'}}">{gt text='Delete compiled theme templates'}</a></li>
                <li><a href="index.php?module=theme&amp;type=admin&amp;func=render_clear_compiled&amp;csrftoken={insert name='csrftoken'}}">{gt text='Delete compiled render templates'}</a></li>
            </ul>
    </fieldset>
    <fieldset>
        <legend>{gt text='Caching'}</legend>
            <ul>
                <li><a href="index.php?module=theme&amp;type=admin&amp;func=clear_cache&amp;csrftoken={insert name='csrftoken'}}">{gt text='Delete cached theme pages'}</a></li>
                <li><a href="index.php?module=theme&amp;type=admin&amp;func=render_clear_cache&amp;csrftoken={insert name='csrftoken'}}">{gt text='Delete cached render pages'}</a></li>
            </ul>
    </fieldset>
    <fieldset>
        <legend>{gt text='JS/CSS'}</legend>
            <ul>
                <li><a href="index.php?module=theme&amp;type=admin&amp;func=clear_cssjscombinecache&amp;csrftoken={insert name='csrftoken'}}">{gt text='Delete combination cache'}</a></li>
            </ul>
    </fieldset>
</div>
