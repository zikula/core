{sform form=$form}
    <fieldset>
        <legend>{gt text='New user'}</legend>

        {sform_all_errors form=$form}

        {sform_widget form=$form}

        <input type="submit" />
    </fieldset>
{/sform}