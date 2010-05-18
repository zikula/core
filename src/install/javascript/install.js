function dbtype_onchange()
{
	var dbtype = document.getElementById('dbtype')
	var dbtabletype_container = document.getElementById('dbtabletype_container')

	if ( dbtype.value == 'mysqli' || dbtype.value == 'mysql') {
		dbtabletype_container.className = 'z-show';
	} else {
		dbtabletype_container.className = 'z-hide';
	}
}