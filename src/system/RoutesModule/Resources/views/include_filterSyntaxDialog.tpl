{* purpose of this template: show a modal dialog for explaining FilterUtil syntax *}
<div class="modal fade" id="filterSyntaxModal" tabindex="-1" role="dialog" aria-labelledby="filterSyntaxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                <h4 class="modal-title" id="filterSyntaxModalLabel">{gt text='Filter syntax explained}</h4>
            </div>
            <div class="modal-body">
                <p>{gt text='It is possible to filter the list of retrieved items by specifying arbitrary filter expressions. This page shows how to create these expressions.'}</p>
                <h4>{gt text='Example'}</h4>
                <p>{gt text='The following filters assume that you have a list of persons whereby each person can have many addresses.'}</p>
                <hr />
                <h4>{gt text='Table aliases'}</h4>
                <p>{gt text='The main table, that is the entity being the primary subject of the query, is always known directly. That means, you can prepend "tbl.", but you do not need to.'}
                   {gt text='All additional joined tables can be referenced by "tblFoo".'}
                   {gt text='Thereby "Foo" does not mean the name of the joined entity, but the source or target alias of the corresponding relationship.'}
                   {gt text='This has the advantage that it is possible to join a certain table multiple times. For example instead of having many addresses for a person one could also create two relationships for business and home addresses this way.'}
                </p>
                <p>{gt text='So in our example we can reference Persons with "tbl" and addresses with "tblAddresses".'}</p>
                <hr />
                <h4>{gt text='General syntax'}</h4>
                <h5>{gt text='Filter statements'}</h5>
                <p>{gt text='A filter consists of several statements.'} {gt text='Each statement is a set of field name, operator and value connected by a colon.'} {gt text='The operator defines the condition type (equal, lower than, etc.).'}</p>
                <ul>
                    <li><code>firstName:eq:Peter</code> {gt text='The first name is Peter'}</li>
                    <li><code>tblAddresses.houseNumber:lt:100</code> {gt text='The house number of any address is lower than 100'}</li>
                </ul>
                <h5>{gt text='Combining statements'}</h5>
                <p>{gt text='Several statements can be connected by a comma , (AND) or an asterisk * (OR).'}</p>
                <ul>
                    <li><code>firstName:eq:Peter,tblAddresses.houseNumber:lt:100</code> {gt text='The first name is Peter and the house number of any address is below 100.'}</li>
                    <li><code>firstName:eq:Peter*firstName:eq:Chris</code> {gt text='The first name equals either Peter or Chris'}</li>
                </ul>
                <p>{gt text='Furthermore you can use brackets to group statements.'}</p>
                <ul>
                    <li><code>(firstName:eq:Peter,tblAddresses.houseNumber:lt:100)*(firstName:eq:Chris,tblAddresses.houseNumber:lt:150)</code> {gt text='Either the first name is Peter and the house number of any address is below 100 or the first name is Chris and the house number of any address is below 150.'}</li>
                </ul>
                <h4>{gt text='Operators'}</h4>
                <ul>
                    <li><code>eq</code> {gt text='is equal.'}</li>
                    <li><code>ne</code> {gt text='is not equal.'}</li>
                    <li><code>lt</code> {gt text='is lower than.'}</li>
                    <li><code>le</code> {gt text='is lower or equal than.'}</li>
                    <li><code>gt</code> {gt text='is greater than.'}</li>
                    <li><code>ge</code> {gt text='is greater or equal than.'}</li>
                    <li><code>search</code> {gt text='is any middle coincidence.'} {gt text='The term "bar" will match "foobarthis", but not "foobar" nor "barfoo".'}</li>
                    <li><code>like</code> {gt text='is like.'} {gt text='The value is scanned case insensitive.'} {gt text='Also you can use "\%" as placeholder.'} {gt text='"\%bar" will match "FOObar", "Blubbar" and so on, but not "BarFoo".'}</li>
                    <li><code>likefirst</code> {'is for coincidences at the beginning.'} {gt text='The term "foo" will match "fOo", "FOObar", "FooBlub" and so on, but not "AnotherFoo".'}</li>
                    <li><code>likelast</code> {'is for coincidences at the end.'} {gt text='The term "foo" will match "fOo", "AnotherFoo" and so on, but not "FoObar" or "FooBlub".'}</li>
                    <li><code>null</code> {gt text='is Empty or NULL.'}</li>
                    <li><code>notnull</code> {gt text='is not empty and not NULL.'}</li>
                </ul>
                <hr />
                <h4>{gt text='Special field types'}</h4>
                <h5>{gt text='Categories'}</h5>
                <p>{gt text='If a "Person" has Categories support then you can also filter by category id or name.'}</p>
                <ul>
                    <li><code>categories:eq:4</code> {gt text='Person is assigned to category with id "4".'}</li>
                    <li><code>categories:eq:Sports</code> {gt text='Person is assigned to category with name "Sports".'}</li>
                    <li><code>categories:ne:4</code> {gt text='Person is not assigned to category with id "4".'}</li>
                    <li><code>categories:sub:Sports</code> {gt text='Person is assigned to category with name "Sports" or one of it\'s sub categories.'}</li>
                </ul>
                <p>{gt text='The field name "categories" is the default name for category fields. However, in practice we need to define different field names for possibly several registries.'}</p>
                <p>{gt text='Therefore we create a virtual fields for each registry property.'} {gt text='So instead of "categories" use "categoriesFoo" whereby "Foo" is the property name for the desired registry, for example "Main".'}</p>
                <h5>{gt text='Dates'}</h5>
                <p>{gt text='When filtering for dates you can use convenient extensions and even time periods.'}</p>
                <ul>
                    <li>{gt text='Prepend one of the keywords "year", "month", "week", "day", "hour", "min" followed by a colon to search for a time period.'}
                        <ul>
                            <li><code>date:eq:year:15.07.2013</code> {gt text='All items with a date in year 2013.'}</li>
                            <li><code>date:eq:month:15.07.2013</code> {gt text='All items with a date in July 2013.'}</li>
                        </ul>
                    </li>
                    <li>{gt text='You can use relative time information according to the GNU Date Input Formats syntax.'}
                        <ul>
                            <li><code>date:eq:today</code> {gt text='All items with date of today.'}</li>
                            <li><code>date:ge:24 hours</code> {gt text='All items with date up from 24 hours ago.'}</li>
                            <li><code>date:eq:last year</code> {gt text='All items with date in the last year.'}</li>
                        </ul>
                    </li>
                </ul>
                <h6>{gt text='Date operators'}</h6>
                <ul>
                    <li><code>eq</code> {gt text='is equal.'}</li>
                    <li><code>ne</code> {gt text='is not equal.'}</li>
                    <li><code>gt</code> {gt text='is greater than.'} {gt text='For time periods: End of the given period. "Date:gt:today" matches all items with date of tomorrow or later.'}</li>
                    <li><code>ge</code> {gt text='is greater or equal than.'} {gt text='For time periods: Begin of the given period. "Date:ge:today" matches all items with date of today or later.'}</li>
                    <li><code>lt</code> {gt text='is lower than.'} {gt text='For time periods: Begin of the given period. "Date:lt:today" matches all items with date of yesterday or before.'}</li>
                    <li><code>le</code> {gt text='is lower or equal than.'} {gt text='For time periods: End of the given period. "Date:le:today" matches all items with date of today or before.'}</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{gt text='Close'}</button>
            </div>
        </div>
    </div>
</div>
