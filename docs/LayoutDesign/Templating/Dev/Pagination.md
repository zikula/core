---
currentMenu: templating
---
# Pagination

Large result sets should be paginated. Doctrine provides a method to limit its result sets to do this, but it doesn't
provide an UI for proper display. Zikula provides a 'wrapper' class in order to facilitate easier UI. 
In order to utilize Zikula's paginator, the following steps should be followed:

### In the repository class

```php
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
// ...

$qb = $this->createQueryBuilder('m')
    ->select('m');
return (new Paginator($qb, $pageSize))->paginate($page); // returns Paginator object
```

### In the controller

```php
$latestPosts = $repository->getLatestPosts($criteria, $pageSize);
$latestPosts->setRoute('mycustomroute');
$latestPosts->setRouteParameters(['foo' => 'bar']);
return $this->render('blog/index.'.$_format.'.twig', [
    'paginator' => $latestPosts,
]);
```

### In the template

```twig
{% for post in paginator.results %}
    {{ post.title }}
{% endfor %}
{{ include(paginator.template) }}
```

### Customization

The template can be customized by overriding `@Core/Paginator/Paginator.html.twig` in all the normal ways.
You can also simply set your own custom template in the controller `$latestPosts->setTemplate('@MyBundle/Custom/Template.html.twig');`
