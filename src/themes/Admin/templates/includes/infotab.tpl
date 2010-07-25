<div class="z-form">
<fieldset>
    <legend>{gt text='Informations'}</legend>
	<strong>{gt text="Zikula version:"}</strong> {version}<br />
    <strong>{gt text="Server information:"}</strong> {$serversig|strip_tags}<br />
    <strong>{gt text="PHP version:"}</strong> {$phpversion}<br />
	<strong>{gt text="Database version"}:</strong> {$dbinfo}<br />
</fieldset>
</div>
