{gt text='Welcome to %1$s!' tag1=$sitename assign='subject'}
<h3>{gt text='Welcome to %1$s (%2$s)!' tag1=$sitename tag2=$siteurl}</h3>

<p>{gt text='Hello! This e-mail address (\'%1$s\') has been used to register an account on the \'%2$s\' site.' tag1=$email tag2=$sitename}</p>
<p>{gt text="Your account details are as follows:"}</p>
<p>{gt text="User name: %s" tag1=$uname}</p>
<p>{gt text="Password: %s" tag1=$pass}</p>
