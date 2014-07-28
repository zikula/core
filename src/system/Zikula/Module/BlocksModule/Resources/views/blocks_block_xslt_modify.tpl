<div class="form-group">
    <label class="col-lg-3 control-label" for="xslt_docurl">{gt text="Document URL"}</label>
    <div class="col-lg-9">
        <input id="xslt_docurl" class="form-control" name="docurl" type="text" size="32" maxlength="255" value="{$docurl|default:''}" />
        <strong>{gt text="or" domain='zikula'}</strong>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label" for="xslt_docurl">{gt text="Document contents" domain='zikula'}</label>
    <div class="col-lg-9">
        <textarea id="xslt_doccontents" class="form-control" rows="20" cols="50" name="doccontents">{$doccontents|default:''|safetext}</textarea>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label" for="xslt_styleurl">{gt text="Style sheet URL" domain='zikula'}</label>
    <div class="col-lg-9">
        <input id="xslt_styleurl" class="form-control" name="styleurl" type="text" size="32" maxlength="255" value="{$styleurl|default:''}" />
        <strong>{gt text="or"}</strong>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label" for="xslt_stylecontents">{gt text="Style sheet contents" domain='zikula'}</label>
    <div class="col-lg-9">
        <textarea id="xslt_stylecontents" class="form-control" rows="20" cols="50" name="stylecontents">{$stylecontents|default:''|safetext}</textarea>
    </div>
</div>
