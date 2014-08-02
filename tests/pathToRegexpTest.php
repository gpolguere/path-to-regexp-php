<?php

require_once dirname(__FILE__) . "/../src/PathToRegexp.php";

class PathToRegexpTest extends PHPUnit_Framework_TestCase {
	/**
	 * ABOUT $tests:
	 * An array of test cases with expected inputs and outputs. The format of each
	 * array item is:
	 *
	 * ["path", "expected params", "route", "expected output", "options"]
	 *
	 * @type {Array}
	 */

	//
	// Simple paths.
	//
	public function testSimplePaths() {
		$tests = array(
			array('/', array(), '/', array('/')),
			array('/test', array(), '/test', array('/test')),
			array('/test', array(), '/route', null),
			array('/test', array(), '/test/route', null),
			array('/test', array(), '/test/', array('/test/')),
			array('/test/', array(), '/test', array('/test')),
			array('/test/', array(), '/test/', array('/test/')),
			array('/test/', array(), '/test//', null)
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Case-sensitive paths.
	//
	public function testCaseSensitivePaths() {
		$tests = array(
			array('/test', array(), '/test', array('/test'), array("sensitive" => true )),
			array('/test', array(), '/TEST', null, array("sensitive" => true )),
			array('/TEST', array(), '/test', null, array("sensitive" => true ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Strict mode.
	//
	public function testStrictMode() {
		$tests = array(
			array('/test', array(), '/test', array('/test'), array("strict" => true )),
			array('/test', array(), '/test/', null, array("strict" => true )),
			array('/test/', array(), '/test', null, array("strict" => true )),
			array('/test/', array(), '/test/', array('/test/'), array("strict" => true )),
			array('/test/', array(), '/test//', null, array("strict" => true ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Non-ending mode.
	//
	public function testNonEndingMode() {
		$tests = array(
			array('/test', array(), '/test', array('/test'), array("end" => false )),
			array('/test', array(), '/test/', array('/test/'), array("end" => false )),
			array('/test', array(), '/test/route', array('/test'), array("end" => false )),
			array('/test/', array(), '/test/route', array('/test'), array("end" => false )),
			array('/test/', array(), '/test//', array('/test'), array("end" => false )),
			array('/test/', array(), '/test//route', array('/test'), array("end" => false )),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route'), array("end" => false )),
			array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route'), array("end" => false ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Combine modes.
	//
	public function testCombineModes() {
		$tests = array(
			array('/test', array(), '/test', array('/test'), array("end" => false, "strict" => true )),
			array('/test', array(), '/test/', array('/test'), array("end" => false, "strict" => true )),
			array('/test', array(), '/test/route', array('/test'), array("end" => false, "strict" => true )),
			array('/test/', array(), '/test', null, array("end" => false, "strict" => true )),
			array('/test/', array(), '/test/', array('/test/'), array("end" => false, "strict" => true )),
			array('/test/', array(), '/test//', array('/test/'), array("end" => false, "strict" => true )),
			array('/test/', array(), '/test/route', array('/test/'), array("end" => false, "strict" => true )),
			array('/test.json', array(), '/test.json', array('/test.json'), array("end" => false, "strict" => true )),
			array('/test.json', array(), '/test.json.hbs', null, array("end" => false, "strict" => true )),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route'), array("end" => false, "strict" => true )),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/', array('/route', 'route'), array("end" => false, "strict" => true )),
			array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/', array('/route/', 'route'), array("end" => false, "strict" => true )),
			array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', null, array("end" => false, "strict" => true ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Arrays of simple paths.
	//
	public function testArraysOfSimplePaths() {
		$tests = array(
			array(array('/one', '/two'), array(), '/one', array('/one')),
			array(array('/one', '/two'), array(), '/two', array('/two')),
			array(array('/one', '/two'), array(), '/three', null),
			array(array('/one', '/two'), array(), '/one/two', null)
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Non-ending simple path.
	//
	public function testNonEndingSimplePath() {
		$tests = array(
			array('/test', array(), '/test/route', array('/test'), array("end" => false ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Single named parameter.
	//
	public function testSingleNamedParameter() {
		$tests = array(
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route')),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/another', array('/another', 'another')),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/something/else', null),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json', array('/route.json', 'route.json')),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route'), array("strict" => true )),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/', null, array("strict" => true )),
			array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/', array('/route/', 'route'), array("strict" => true )),
			array('/:test/', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route//', null, array("strict" => true )),
			array('/:test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json', array('/route.json', 'route.json'), array("end" => false ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Optional named parameter.
	//
	public function testOptionalNamedParameter() {
		$tests = array(
			array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/route', array('/route', 'route')),
			array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/route/nested', null),
			array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/', array('/', null)),
			array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/route', array('/route', 'route'), array("strict" => true )),
			array('/:test?', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/', null, array("strict" => true )), // Questionable behaviour.
			array('/:test?/', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '/', array('/', null),array("strict" => true )),
			array('/:test?/', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '//', null),
			array('/:test?/', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => false )), '//', null,array("strict" => true ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Repeated once or more times parameters.
	//
	public function testOptionalNamedParameterRepeatedOnceOrMore() {
		$tests = array(
			array('/:test+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/', null),
			array('/:test+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/route', array('/route', 'route')),
			array('/:test+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/some/basic/route', array('/some/basic/route', 'some/basic/route')),
			array('/:test(\\d+)+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/abc/456/789', null),
			array('/:test(\\d+)+', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => true )), '/123/456/789', array('/123/456/789', '123/456/789')),
			array('/route.:ext(json|xml)+', array(array("name" => 'ext', "delimiter" => '.', "optional" => false, "repeat" => true )), '/route.json', array('/route.json', 'json')),
			array('/route.:ext(json|xml)+', array(array("name" => 'ext', "delimiter" => '.', "optional" => false, "repeat" => true )), '/route.xml.json', array('/route.xml.json', 'xml.json')),
			array('/route.:ext(json|xml)+', array(array("name" => 'ext', "delimiter" => '.', "optional" => false, "repeat" => true )), '/route.html', null)
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Repeated zero or more times parameters.
	//
	public function testRepeatedZeroOrMoreParameters() {
		$tests = array(
			array('/:test*', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => true )), '/', array('/', null)),
			array('/:test*', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => true )), '//', null),
			array('/:test*', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => true )), '/route', array('/route', 'route')),
			array('/:test*', array(array("name" => 'test', "delimiter" => '/', "optional" => true, "repeat" => true )), '/some/basic/route', array('/some/basic/route', 'some/basic/route')),
			array('/route.:ext([a-z]+)*', array(array("name" => 'ext', "delimiter" => '.', "optional" => true, "repeat" => true )), '/route', array('/route', null)),
			array('/route.:ext([a-z]+)*', array(array("name" => 'ext', "delimiter" => '.', "optional" => true, "repeat" => true )), '/route.json', array('/route.json', 'json')),
			array('/route.:ext([a-z]+)*', array(array("name" => 'ext', "delimiter" => '.', "optional" => true, "repeat" => true )), '/route.xml.json', array('/route.xml.json', 'xml.json')),
			array('/route.:ext([a-z]+)*', array(array("name" => 'ext', "delimiter" => '.', "optional" => true, "repeat" => true )), '/route.123', null)
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Custom named parameters.
	//
	public function testCustomNamedParameters() {
		$tests = array(
			array('/:test(\\d+)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123', array('/123', '123')),
			array('/:test(\\d+)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/abc', null),
			array('/:test(\\d+)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123/abc', null),
			array('/:test(\\d+)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123/abc', array('/123', '123'), array("end" => false )),
			array('/:test(.*)', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )),'/anything/goes/here', array('/anything/goes/here', 'anything/goes/here')),
			array('/:route([a-z]+)', array(array("name" => 'route', "delimiter" => '/', "optional" => false, "repeat" => false )), '/abcde', array('/abcde', 'abcde')),
			array('/:route([a-z]+)', array(array("name" => 'route', "delimiter" => '/', "optional" => false, "repeat" => false )), '/12345', null),
			array('/:route(this|that)', array(array("name" => 'route', "delimiter" => '/', "optional" => false, "repeat" => false )), '/this', array('/this', 'this')),
			array('/:route(this|that)', array(array("name" => 'route', "delimiter" => '/', "optional" => false, "repeat" => false )), '/that', array('/that', 'that'))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Prefixed slashes could be omitted.
	//
	public function testPrefixedSlashed() {
		$tests = array(
			array('test', array(), 'test', array('test')),
			array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), 'route', array('route', 'route')),
			array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', null),
			array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), 'route/', array('route/', 'route')),
			array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), 'route/', null, array("strict" => true )),
			array(':test', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), 'route/', array('route/', 'route'), array("end" => false ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Formats.
	//
	public function testFormats() {
		$tests = array(
			array('/test.json', array(), '/test.json', array('/test.json')),
			array('/test.json', array(), '/route.json', null),
			array('/:test.json', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json', array('/route.json', 'route')),
			array('/:test.json', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json.json', array('/route.json.json', 'route.json')),
			array('/:test.json', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json', array('/route.json', 'route'), array("end" => false )),
			array('/:test.json', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route.json.json', array('/route.json.json', 'route.json'), array("end" => false ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Format params.
	//
	public function testFormatParams() {
		$tests = array(
			array('/test.:format', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.html', array('/test.html', 'html')),
			array('/test.:format', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.hbs.html', null),
			array('/test.:format.:format', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ),   array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ) ), '/test.hbs.html', array('/test.hbs.html', 'hbs', 'html')),
			array('/test.:format+', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => true ) ), '/test.hbs.html', array('/test.hbs.html', 'hbs.html')),
			array('/test.:format', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.hbs.html', null, array("end" => false )),
			array('/test.:format.', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.hbs.html', null, array("end" => false ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Format and path params.
	//
	public function testFormatAndPathParams() {
		$tests = array(
			array( '/:test.:format', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ) ), '/route.html', array('/route.html', 'route', 'html')),
			array( '/:test.:format', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ) ), '/route', null),
			array( '/:test.:format', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false ) ), '/route', null),
			array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route', array('/route', 'route', null)),
			array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route.json', array('/route.json', 'route', 'json')),
			array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route', array('/route', 'route', null), array("end" => false )),
			array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route.json', array('/route.json', 'route', 'json'), array("end" => false )),
			array( '/:test.:format?', array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'format', "delimiter" => '.', "optional" => true, "repeat" => false ) ), '/route.json.html', array('/route.json.html', 'route.json', 'html'), array("end" => false )),
			array( '/test.:format(.*)z', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.abc', null, array("end" => false )),
			array( '/test.:format(.*)z', array(array("name" => 'format', "delimiter" => '.', "optional" => false, "repeat" => false )), '/test.abcz', array('/test.abcz', 'abc'), array("end" => false ))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Unnamed params.
	//
	public function testUnnamedParams() {
		$tests = array(
			array( '/(\\d+)', array(array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123', array('/123', '123')),
			array( '/(\\d+)', array(array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )), '/abc', null),
			array( '/(\\d+)', array(array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123/abc', null),
			array( '/(\\d+)', array(array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )), '/123/abc', array('/123', '123'), array("end" => false )),
			array( '/(\\d+)', array(array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )), '/abc', null, array("end" => false )),
			array( '/(\\d+)?', array(array("name" => '0', "delimiter" => '/', "optional" => true, "repeat" => false )), '/', array('/', null)),
			array( '/(\\d+)?', array(array("name" => '0', "delimiter" => '/', "optional" => true, "repeat" => false )), '/123', array('/123', '123')),
			array( '/(.*)', array(array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route', array('/route', 'route')),
			array( '/(.*)', array(array("name" => '0', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/nested', array('/route/nested', 'route/nested'))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Correct names and indexes.
	//
	public function testCorrectNamesAndIndexes() {
		$tests = array(
			array( array('/:test', '/route/:test'), array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/test', array('/test', 'test', null)),
			array( array('/:test', '/route/:test'), array(array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'test', "delimiter" => '/', "optional" => false, "repeat" => false )), '/route/test', array('/route/test', null, 'test'))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Respect escaped characters.
	//
	public function testRespectEscapedCharacters() {
		$tests = array(
			// array('/\\(testing\\)', array(), '/testing', null),
			// array('/\\(testing\\)', array(), '/(testing)', array('/(testing)')),
			array('/.+*?=^!:${}[]|', array(), '/.+*?=^!:${}[]|', array('/.+*?=^!:${}[]|'))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	//
	// Regressions.
	//
	public function testRegressions() {
		$tests = array(
			array( '/:remote([\\w-.]+)/:user([\\w-]+)', array(array("name" => 'remote', "delimiter" => '/', "optional" => false, "repeat" => false ), array("name" => 'user', "delimiter" => '/', "optional" => false, "repeat" => false ) ), '/endpoint/user', array('/endpoint/user', 'endpoint', 'user'))
		);

		$this->genericTest($tests, __FUNCTION__);
	}

	private function genericTest($tests, $name = "") {
		// error_log("----- " . $name . " -----");
		foreach($tests as $id => $test) {
			// error_log("    ----- " . $id . " -----");
			$params = array();
			if(count($test) > 4) {
				$options = $test[4];
			} else {
				$options = array();
			}
			$regexp = PathToRegexp::convert($test[0], $params, $options);

			// Check the params are as expected.
			$this->assertEquals($test[1], $params);

			// Run the regexp and check the result is expected.
			$matches = PathToRegexp::match($regexp, $test[2]);
			$this->assertEquals($test[3], $matches);
		}
	}
}

?>