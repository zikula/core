# NOTES ON USING DOCTRINE 2

Please note that you should not use print_r() for debugging Doctrine 2 entities.
The reason for that is that these objects contain too many references which will
result in a very huge output.

Instead use the Doctrine\Common\Util\Debug::dump($entity) method which reduces
the output to reasonable information. In Zikula 1.4+ there is also
a shortcut method available in System::dump($var, $maxDepth = 2, $stripTags = true).

Read more about Doctrine at http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/index.html
