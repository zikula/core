<div class="z-errormsg">
    <h2>{gt text="Sorry! Your session has expired."}</h2>
    {modurl modname='Users' type='user' func='login' returnpage=$returnpage assign='loginurl'}
    <p>{gt text='For your security, this session has expired because you have been inactive for too long. Please <a href="%s">log in</a> again to access services.' tag1=$loginurl|safetext}</p>
</div>
