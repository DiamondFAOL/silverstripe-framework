<?php
/**
 * Tests the {@link HTTP} class
 *
 * @package sapphire
 * @subpackage tests
 */
class HTTPTest extends SapphireTest {
	
	/**
	 * Tests {@link HTTP::getLinksIn()}
	 */
	public function testGetLinksIn() {
		$content = '
			<h2>My page</h2>
			<p>A boy went <a href="home/">home</a> to see his <span><a href="mother/">mother</a></span>.</p>
		';
		
		$links = HTTP::getLinksIn($content);
		
		$this->assertTrue(is_array($links));
		$this->assertTrue(count($links) == 2);
	}
	
	/**
	 * Tests {@link HTTP::setGetVar()}
	 */
	public function testSetGetVar() {
		// Hackery to work around volatile URL formats in test invocation,
		// and the inability of Director::absoluteBaseURL() to produce consistent URLs.
		$expectedPath = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		
		// TODO This should test the absolute URL, but we can't get it reliably
		// with port and auth URI parts.
		$expectedBasePath = Director::baseURL();
		
		foreach(array($expectedPath, 'foo=bar') as $e) {
			$this->assertContains(
				$e,
				HTTP::setGetVar('foo', 'bar'),
				'Omitting a URL falls back to current URL'
			);
		}

		foreach(array($expectedBasePath, '/relative/url?foo=bar') as $e) {
			$this->assertContains(
				$e,
				HTTP::setGetVar('foo', 'bar', 'relative/url'),
				'Relative URL without slash prefix returns URL with absolute base'
			);
		}
		
		foreach(array($expectedBasePath, '/relative/url?baz=buz&foo=bar') as $e) {
			$this->assertContains(
				$e,
				HTTP::setGetVar('foo', 'bar', '/relative/url?baz=buz'),
				'Relative URL with existing query params, and new added key'
			);
		}

		$this->assertEquals(
			'http://test.com/?foo=new&buz=baz',
			HTTP::setGetVar('foo', 'new', 'http://test.com/?foo=old&buz=baz'),
			'Absolute URL without path and multipe existing query params, overwriting an existing parameter'
		);

		$this->assertContains(
			'http://test.com/?foo=new',
			HTTP::setGetVar('foo', 'new', 'http://test.com/?foo=&foo=old'),
			'Absolute URL and empty query param'
		);

		$this->assertEquals(
			// http_build_query() escapes angular brackets, they should be correctly urldecoded by the browser client
			'http://test.com/?foo%5Btest%5D=one&foo%5Btest%5D=two',
			HTTP::setGetVar('foo[test]', 'two', 'http://test.com/?foo[test]=one'),
			'Absolute URL and PHP array query string notation'
		);
	}
	
}