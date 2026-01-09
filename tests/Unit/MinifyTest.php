<?php
/**
 * Test class for Blitz_Cache_Minify
 */

namespace BlitzCache\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use BlitzCache\Blitz_Cache_Minify;

/**
 * Test suite for Blitz_Cache_Minify class
 */
class MinifyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey::setUp();

        Functions\when('apply_filters')->returnArg();
    }

    protected function tearDown(): void
    {
        Monkey::tearDown();
        parent::tearDown();
    }

    /**
     * Test minify removes whitespace between tags
     */
    public function testMinifyRemovesWhitespaceBetweenTags()
    {
        $html = '<div>Hello</div>   <span>World</span>';
        $expected = '<div>Hello</div> <span>World</span>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify removes multiple spaces
     */
    public function testMinifyRemovesMultipleSpaces()
    {
        $html = '<div>Hello    World    Test</div>';
        $expected = '<div>Hello World Test</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify removes HTML comments
     */
    public function testMinifyRemovesHtmlComments()
    {
        $html = '<!-- This is a comment --><div>Content</div><!-- Another comment -->';
        $expected = '<div>Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify preserves inline scripts
     */
    public function testMinifyPreservesInlineScripts()
    {
        $html = '<script>var x = 1;   var y = 2;</script><div>Content</div>';
        $expected = '<script>var x = 1;   var y = 2;</script><div>Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify preserves inline styles
     */
    public function testMinifyPreservesInlineStyles()
    {
        $html = '<style>body {    margin:   0;    padding:   0;   }</style><div>Content</div>';
        $expected = '<style>body {    margin:   0;    padding:   0;   }</style><div>Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify preserves pre tags
     */
    public function testMinifyPreservesPreTags()
    {
        $html = '<pre>  Code    with   spaces  </pre><div>Content</div>';
        $expected = '<pre>  Code    with   spaces  </pre><div>Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify preserves code tags
     */
    public function testMinifyPreservesCodeTags()
    {
        $html = '<code>  some   code  </code><div>Content</div>';
        $expected = '<code>  some   code  </code><div>Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify preserves textarea tags
     */
    public function testMinifyPreservesTextareaTags()
    {
        $html = '<textarea>  Multiple    spaces  </textarea><div>Content</div>';
        $expected = '<textarea>  Multiple    spaces  </textarea><div>Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify handles complex HTML
     */
    public function testMinifyHandlesComplexHtml()
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Test</title>
        </head>
        <body>
            <div class="container">
                <h1>Hello World</h1>
                <p>This is a test.</p>
            </div>
        </body>
        </html>
        ';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        // Should be on one line or have minimal newlines
        $this->assertNotFalse(strpos($result, '<!DOCTYPE html>'));
        $this->assertNotFalse(strpos($result, '<html>'));
        $this->assertNotFalse(strpos($result, '<div class="container">'));
    }

    /**
     * Test minify handles nested tags
     */
    public function testMinifyHandlesNestedTags()
    {
        $html = '<div><span>Nested</span>   <span>Content</span></div>';
        $expected = '<div><span>Nested</span> <span>Content</span></div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify removes newlines and tabs
     */
    public function testMinifyRemovesNewlinesAndTabs()
    {
        $html = "<div>\n\t\tHello\t\tWorld\n\t</div>";
        $expected = '<div> Hello World </div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify filter hook can disable minification
     */
    public function testMinifyFilterHookCanDisableMinification()
    {
        // Set up filter to return false
        Functions\when('apply_filters')
            ->with('blitz_cache_should_minify', true, 'test html')
            ->return(false);

        $html = '<div>   Test   </div>';
        $expected = '<div>   Test   </div>'; // Should remain unchanged

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify handles mixed preserve tags
     */
    public function testMinifyHandlesMixedPreserveTags()
    {
        $html = '
        <script>
            var x = 1;
        </script>
        <style>
            body { margin: 0; }
        </style>
        <pre>
            Code block
        </pre>
        <div>Normal content</div>
        ';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        // Script and style should be preserved
        $this->assertNotFalse(strpos($result, '<script>'));
        $this->assertNotFalse(strpos($result, '<style>'));
        $this->assertNotFalse(strpos($result, '<pre>'));

        // But whitespace between tags should be removed
        $this->assertNotContains("\n", $result);
    }

    /**
     * Test minify handles IE conditional comments
     */
    public function testMinifyHandlesIeConditionalComments()
    {
        $html = '<!--[if IE]><div>IE Only</div><![endif]--><div>Content</div>';
        $expected = '<!--[if IE]><div>IE Only</div><![endif]--><div>Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify returns trimmed result
     */
    public function testMinifyReturnsTrimmedResult()
    {
        $html = '   <div>Content</div>   ';
        $expected = '<div>Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify handles empty input
     */
    public function testMinifyHandlesEmptyInput()
    {
        $html = '';
        $expected = '';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify handles whitespace-only input
     */
    public function testMinifyHandlesWhitespaceOnlyInput()
    {
        $html = '   \n\t   ';
        $expected = '';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify handles block elements
     */
    public function testMinifyHandlesBlockElements()
    {
        $html = '<p>Paragraph</p>   <div>Div</div>   <h1>Heading</h1>';
        $expected = '<p>Paragraph</p><div>Div</div><h1>Heading</h1>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test minify removes spaces around attributes
     */
    public function testMinifyRemovesSpacesAroundAttributes()
    {
        $html = '<div   class="test"   id="myid">Content</div>';
        $expected = '<div class="test" id="myid">Content</div>';

        $minifier = new Blitz_Cache_Minify();
        $result = $minifier->minify($html);

        $this->assertEquals($expected, $result);
    }
}
