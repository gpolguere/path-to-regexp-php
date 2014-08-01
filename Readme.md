# Path-to-RegExp

Turn an Express-style path string such as `/user/:name` into a regular expression.

This is a PHP port of the JS library [component/path-to-regexp](https://github.com/component/path-to-regexp) **without the support of JS native regexp** (couldn't check the usage of the path).

## Usage

```php
require_once "pathToRegexp.php";

pathToRegexp($path, $keys, $options);
```

- **path** A string in the express format, an array of strings, or a regular expression.
- **keys** An array to be populated with the keys present in the url.
- **options**
  - **options.sensitive** When set to `true` the route will be case sensitive.
  - **options.strict** When set to `true` a slash is allowed to be trailing the path.
  - **options.end** When set to `false` the path will match at the beginning.

```php
$keys = [];
$re = pathToRegexp('/foo/:bar', $keys);
// $re = '/^\/foo\/([^\/]+?)\/?$/i'
// $keys = array(array("name" => 'bar', "delimiter" => '/', "repeat" => false, "optional" => false))
```

### Parameters

The path has the ability to define parameters and automatically populate the keys array.

#### Named Parameters

Named parameters are defined by prefixing a colon to the parameter name (`:foo`). By default, this parameter will match up to the next path segment.

```php
$re = pathToRegexp('/:foo/:bar', $keys);
// $keys = array(array("name" => 'foo', ... ), array("name" => 'bar', ... ))

preg_match_all($re, '/test/route', $matches);
// $matches = array('/test/route', 'test', 'route')
```

#### Suffixed Parameters

##### Optional

Parameters can be suffixed with a question mark (`?`) to make the entire parameter optional. This will also make any prefixed path delimiter optional (`/` or `.`).

```php
$re = pathToRegexp('/:foo/:bar?', $keys);
// $keys = array(array("name" => 'foo', ... ), array("name" => 'bar', "delimiter" => '/', "optional" => true, "repeat" => false ))

preg_match_all($re, '/test', $matches);
// $matches = array('/test', 'test', null)

preg_match_all($re, '/test/route', $matches);
// $matches = array('/test', 'test', 'route')
```

##### Zero or more

Parameters can be suffixed with an asterisk (`*`) to denote a zero or more parameter match. The prefixed path delimiter is also taken into account for the match.

```php
$re = pathToRegexp('/:foo*', $keys);
// $keys = array(array("name" => 'foo', "delimiter" => '/', "optional" => true, "repeat" => true))

preg_match_all($re, '/', $matches);
// $matches = array('/', null)

preg_match_all($re, '/bar/baz', $matches);
// $matches = array('/bar/baz', 'bar/baz')
```

##### One or more

Parameters can be suffixed with a plus sign (`+`) to denote a one or more parameters match. The prefixed path delimiter is included in the match.

```php
$re = pathToRegexp('/:foo+', $keys);
// $keys = array(array("name" => 'foo', "delimiter" => '/', "optional" => false, "repeat" => true))

preg_match_all($re, '/', $matches);
// $matches = null

preg_match_all($re, '/bar/baz', $matches);
// $matches = array('/bar/baz', 'bar/baz')
```

#### Custom Match Parameters

All parameters can be provided a custom matching regexp and override the default. Please note: Backslashes need to be escaped in strings.

```php
$re = pathToRegexp('/:foo(\\d+)', $keys);
// $keys = array(array("name" => 'foo', ... ))

preg_match_all($re, '/123', $matches);
// $matches = array('/123', '123')

preg_match_all($re, '/abc', $matches);
// $matches = null
```

#### Unnamed Parameters

It is possible to write an unnamed parameter that is only a matching group. It works the same as a named parameter, except it will be numerically indexed.

```php
$re = pathToRegexp('/:foo/(.*)', $keys);
// $keys = array(array("name" => 'foo', ... ), array("name": '0', ... ))

preg_match_all($re, '/test/route', $matches);
// $matches = array('/test/route', 'test', 'route')
```

## Compatibility with Express <= 4.x

Path-To-RegExp breaks compatibility with Express 3.x in a few ways:

* RegExp special characters can now be used in the regular path. E.g. `/user[(\\d+)]`
* All RegExp special characters can now be used inside the custom match. E.g. `/:user(.*)`
* No more support for asterisk matching - use an explicit parameter instead. E.g. `/(.*)`
* Parameters can have suffixes that augment meaning - `*`, `+` and `?`. E.g. `/:user*`

## Live Demo

You can see a live demo of this library in use at [express-route-tester](http://forbeslindesay.github.com/express-route-tester/).

## License

MIT
