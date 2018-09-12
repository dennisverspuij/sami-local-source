# Sami local source
The PHP API documentation generator [Sami](https://github.com/FriendsOfPHP/Sami) can be [configured](https://github.com/FriendsOfPHP/Sami/pull/91) to embed hyperlinks to the source code. So far only remote repositories at Github are supported. This library adds support for exposing the source code together with the generated documentation itself. Currently using [GeSHi](http://qbnz.com/highlighter/), though other highlighters like [FSHL](http://fshl.kukulich.cz/) or [scrivo/highlight](https://github.com/scrivo/highlight.php) could be easily supported by extending [Base](Base.php).

## Installation
### In combination with `sami.phar`
Create a directory e.g. `/some/path/sami-local-source` with a `composer.json` containing:
```javascript
{
    "repositories": [
      { "type": "vcs", "url": "https://github.com/dennisverspuij/sami-local-source.git" }
    ],
    "require": {
        "dennisverspuij/sami-local-source": "dev-master",
        "geshi/geshi": "^1"
    }
}
```
Install using [`composer`](https://getcomposer.org/download/):
```bash
cd /some/path/sami-local-source
composer install
```
And prepend your Sami project configuration file with:
```php
require_once('/some/path/sami-local-source/vendor/autoload.php');
```

### Install sami together with this library at once
Create a directory e.g. `/some/path/sami` with a `composer.json` containing:
```js
{
    "repositories": [
      { "type": "vcs", "url": "https://github.com/dennisverspuij/sami-local-source.git" }
    ],
    "require": {
        "sami/sami": "^4",
        "dennisverspuij/sami-local-source": "dev-master",
        "geshi/geshi": "^1"
    }
}
```
Install using [`composer`](https://getcomposer.org/download/):
```bash
cd /some/path/sami
composer install
# You can now invoke sami as follows:
php /some/path/sami/vendor/bin/sami.php ...
```


## Configuration
Complement your Sami project configuration file like:
```php
<?php
  # ...
  # $sami = new \Sami\Sami('/path/to/src', ...);  # or
  # $sami = new \Sami\Sami(\Symfony\Component\Finder\Finder::create()->in(array('/path/to/src/a', '/path/to/src/b', '...')), ...);
  $sami['remote_repository'] = function() use($sami) {
    return new \DennisVerspuij\SamiLocalSource\GeSHi($sami, '/path/to/src', array('b','...'));
  };
  # ...
  # return $sami;
```
Note that you need to have a common **`/path/to/src`** root for the input source files, otherwise it won't work. The third array parameter is a list of relative paths to this root to exclude, just omit the array if you want all sources to be visitable.
