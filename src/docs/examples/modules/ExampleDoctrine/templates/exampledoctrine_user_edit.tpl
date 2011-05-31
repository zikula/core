{form cssClass="z-form"}
    <fieldset>
        <legend>{gt text='New user'}</legend>

        {formvalidationsummary}

        <div class="z-formrow">
            {formlabel for="username" __text="User name"}
            {formtextinput id="username" mandatory=true maxLength=255}
        </div>

        <div class="z-formrow">
            {formlabel for="password" __text="Password"}
            {formtextinput id="password" mandatory=true maxLength=255}
        </div>
<!--
        <fieldset>
            <legend>{*gt text='Meta data'}</legend>

            <div class="z-formrow">
                {formlabel for="metaComment" __text="Comment"}
                {formtextinput id="metaComment" group="__META__" dataField="dc_comment" maxLength=255}
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text='Attributes'}</legend>

            <div class="z-formrow">
                {formlabel for="attributesField1" __text="Field 1"}
                {formtextinput id="attributesField1" group="__ATTRIBUTES__" dataField="field1" maxLength=255}
            </div>

            <div class="z-formrow">
                {formlabel for="attributesField2" __text="Field 2"}
                {formtextinput id="attributesField2" group="__ATTRIBUTES__" dataField="field2" maxLength=255}
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text='Categories'}</legend>

            {foreach from=$registries item="registryCid" key="property"}
                <div class="z-formrow">
                    {formlabel for="category_`$property`" __text="Category"}
                    {formcategoryselector id="category_`$property`" category=$registryCid dataField=$property enableDoctrine=true}
                </div>
            {/foreach*}
        </fieldset>

-->
        <div class="z-formbuttons">
            {formimagebutton id="create" commandName="create" __text="Save" imageUrl="images/icons/small/button_ok.png"}
        </div>
    </fieldset>
{/form}