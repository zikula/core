</div> {* closing div.z-admin-content *}
{modgetinfo modname=$toplevelmodule info='all' assign=toplevelinfo}
<div class="z-admin-coreversion right">{$toplevelinfo.name} {$toplevelinfo.version} / Zikula {$coredata.version_num}</div>