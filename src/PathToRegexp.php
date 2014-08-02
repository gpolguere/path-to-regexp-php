<?php


class PathToRegexp {

	private static function getRegexpSource($regexp) {
		$delimiter = substr($regexp, 0, 1);
		$endDelimiterPos = strrpos($regexp, $delimiter);
		$source = substr($regexp, 1, $endDelimiterPos - 1);
		return $source;
	}

	/**
	* Normalize the given path string, returning a regular expression.
	*
	* An empty array should be passed in, which will contain the placeholder key
	* names. For example `/user/:id` will then contain `["id"]`.
	*
	* @param  {(String)} path
	* @param  {Array}                 keys
	* @param  {Object}                options
	* @return {RegExp}
	*/
	public static function convert($path, &$keys = array(), $options = array()) {
		$strict = is_array($options) && array_key_exists("strict", $options) ? $options["strict"] : false;
		$end = is_array($options) && array_key_exists("end", $options) ? $options["end"] : true;
		$flags = is_array($options) && !empty($options["sensitive"]) ? "" : "i";
		$index = 0;

		if(is_array($path)) {
			// Map array parts into regexps and return their source. We also pass
			// the same keys and options instance into every generation to get
			// consistent matching groups before we join the sources together.

			$path = array_map(function($value) use(&$keys, &$options) {
				return self::getRegexpSource(self::convert($value, $keys, $options));
			}, $path);

			// Generate a new regexp instance by joining all the parts together.
			return '/(?:' . implode("|", $path) . ')/' . $flags;
		}

		$pathRegexps = array(
			// Match already escaped characters that would otherwise incorrectly appear
			// in future matches. This allows the user to escape special characters that
			// shouldn't be transformed.
			'(\\\\.)',
			// Match Express-style parameters and un-named parameters with a prefix
			// and optional suffixes. Matches appear as:
			//
			// "/:test(\\d+)?" => ["/", "test", "\d+", undefined, "?"]
			// "/route(\\d+)" => [undefined, undefined, undefined, "\d+", undefined]
			'([\\/.])?(?:\\:(\\w+)(?:\\(((?:\\\\.|[^)])*)\\))?|\\(((?:\\\\.|[^)])*)\\))([+*?])?',
			// Match regexp special characters that should always be escaped.
			'([.+*?=^!:${}()[\\]|\\/])'
		);
		$pathRegexp = "/" . implode("|", $pathRegexps) . "/";

		// Alter the path string into a usable regexp.
		$path = preg_replace_callback($pathRegexp, function($matches) use(&$keys, &$index) {
			if(count($matches) > 1) {
				$escaped = $matches[1];
			}
			if(count($matches) > 2) {
				$prefix = $matches[2];
			}
			if(count($matches) > 3) {
				$key = $matches[3];
			}
			if(count($matches) > 4) {
				$capture = $matches[4];
			}
			if(count($matches) > 5) {
				$group = $matches[5];
			}
			if(count($matches) > 6) {
				$suffix = $matches[6];
			} else {
				$suffix = "";
			}
			if(count($matches) > 7) {
				$escape = $matches[7];
			}

			// Avoiding re-escaping escaped characters.
			if(!empty($escaped)) {
				return $escaped;
			}

			// Escape regexp special characters.
			if(!empty($escape)) {
				return '\\' . $escape;
			}

			$repeat   = $suffix === '+' || $suffix === '*';
			$optional = $suffix === '?' || $suffix === '*';

			array_push($keys, array(
				"name" => (string) (!empty($key) ? $key : $index++),
				"delimiter" => !empty($prefix) ? $prefix : '/',
				"optional" => $optional,
				"repeat" => $repeat
			));

			// Escape the prefix character.
			$prefix = !empty($prefix) ? '\\' . $prefix : '';

			// Match using the custom capturing group, or fallback to capturing
			// everything up to the next slash (or next period if the param was
			// prefixed with a period).
			$subject = (!empty($capture) ? $capture : (!empty($group) ? $group : '[^' . (!empty($prefix) ? $prefix : '\\/') . ']+?'));
			$capture = preg_replace('/([=!:$\/()])/', '\1', $subject);

			// Allow parameters to be repeated more than once.
			if(!empty($repeat)) {
				$capture = $capture . '(?:' . $prefix . $capture . ')*';
			}

			// Allow a parameter to be optional.
			if(!empty($optional)) {
				return '(?:' . $prefix . '(' . $capture . '))?';
			}

			// Basic parameter support.
			return $prefix . '(' . $capture . ')';
		}, $path);

		// Check whether the path ends in a slash as it alters some match behaviour.
		$endsWithSlash = substr($path, -1, 1) === "/";

		// In non-strict mode we allow an optional trailing slash in the match. If
		// the path to match already ended with a slash, we need to remove it for
		// consistency. The slash is only valid at the very end of a path match, not
		// anywhere in the middle. This is important for non-ending mode, otherwise
		// "/test/" will match "/test//route".
		if(!$strict) {
			$path = ($endsWithSlash ? substr($path, 0, -2) : $path) . '(?:\\/(?=$))?';
		}

		// In non-ending mode, we need prompt the capturing groups to match as much
		// as possible by using a positive lookahead for the end or next path segment.
		if(!$end) {
			$path .= $strict && $endsWithSlash ? '' : '(?=\\/|$)';
		}

		return '/^' . $path . ($end ? '$' : '') . '/' . $flags;
	}

	public static function match($regexp, $route) {
		preg_match_all($regexp, $route, $matches);
		if(count($matches) == 0) {
			$matches = null;
		} else {
			$areValuesNull = true;
			foreach($matches as $key => $match) {
				if(!empty($match)) {
					$matches[$key] = $match[0];
					$areValuesNull = false;
				} else {
					$matches[$key] = null;
				}
			}
			if($areValuesNull) {
				$matches = null;
			}
		}
		return $matches;
	}
}

?>