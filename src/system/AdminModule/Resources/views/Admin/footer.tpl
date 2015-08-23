</div> {* closing div.z-admin-content *}
{modgetinfo modname=$toplevelmodule info='all' assign=toplevelinfo}
<div class="z-admin-coreversion text-right">{$toplevelinfo.name} {$toplevelinfo.version} / Zikula {$coredata.version_num} / Symfony {$symfonyversion} / PHP {const name="PHP_VERSION"}</div>