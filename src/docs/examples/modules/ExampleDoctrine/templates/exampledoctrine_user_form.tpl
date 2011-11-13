todo symfony form:
    
{sform form=$form}
    <fieldset>
        <legend>Contact</legend>
        {sform_all_errors form=$form}

        {sform_widget form=$form}

        <input type="submit" />
    </fieldset>
{/sform}