<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2015 Vanilla Forums Inc.
 * @license Proprietary
 */

namespace Nbbc\Tests;

use Nbbc\BBCode;
use PHPUnit\Framework\TestCase;

/**
 * Contains all the tests that were part of the default NBBC test page (test_nbbc.php).
 */
class ConformanceTest extends TestCase {
    /**
     * Provide input validation test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideInputValidationTests() {
        $result = [
            [[
                'descr' => "Unknown tags like [foo] get ignored.",
                'bbcode' => "This is [foo]a tag[/foo].",
                'html' => "This is [foo]a tag[/foo].",
            ]],
            [[
                'descr' => "Broken tags like [foo get ignored.",
                'bbcode' => "This is [foo a tag.",
                'html' => "This is [foo a tag.",
            ]],
            [[
                'descr' => "Broken tags like [/foo get ignored.",
                'bbcode' => "This is [/foo a tag.",
                'html' => "This is [/foo a tag.",
            ]],
            [[
                'descr' => "Broken tags like [] get ignored.",
                'bbcode' => "This is [] a tag.",
                'html' => "This is [] a tag.",
            ]],
            [[
                'descr' => "Broken tags like [/  ] get ignored.",
                'bbcode' => "This is [/  ] a tag.",
                'html' => "This is [/  ] a tag.",
            ]],
            [[
                'descr' => "Broken tags like [/ get ignored.",
                'bbcode' => "This is [/ a tag.",
                'html' => "This is [/ a tag.",
            ]],
            [[
                'descr' => "Broken [ tags before [b]real tags[/b] do not break the real tags.",
                'bbcode' => "Broken [ tags before [b]real tags[/b] do not break the real tags.",
                'html' => "Broken [ tags before <b>real tags</b> do not break the real tags.",
            ]],
            [[
                'descr' => "Broken [tags before [b]real tags[/b] do not break the real tags.",
                'bbcode' => "Broken [tags before [b]real tags[/b] do not break the real tags.",
                'html' => "Broken [tags before <b>real tags</b> do not break the real tags.",
            ]],
            [[
                'descr' => "[i][b]Mis-ordered nesting[/i][/b] gets fixed.",
                'bbcode' => "[i][b]Mis-ordered nesting[/i][/b] gets fixed.",
                'html' => "<i><b>Mis-ordered nesting</b></i> gets fixed.",
            ]],
            [[
                'descr' => "[url=][b]Mis-ordered nesting[/url][/b] gets fixed.",
                'bbcode' => "[url=http://www.google.com][b]Mis-ordered nesting[/url][/b] gets fixed.",
                'html' => "<a href=\"http://www.google.com\" class=\"bbcode_url\"><b>Mis-ordered nesting</b></a> gets fixed.",
            ]],
            [[
                'descr' => "[i]Unended blocks are automatically ended.",
                'bbcode' => "[i]Unended blocks are automatically ended.",
                'html' => "<i>Unended blocks are automatically ended.</i>",
            ]],
            [[
                'descr' => "Unstarted blocks[/i] have their end tags ignored.",
                'bbcode' => "Unstarted blocks[/i] have their end tags ignored.",
                'html' => "Unstarted blocks[/i] have their end tags ignored.",
            ]],
            [[
                'descr' => "[b]Mismatched tags[/i] are not matched to each other.",
                'bbcode' => "[b]Mismatched tags[/i] are not matched to each other.",
                'html' => "<b>Mismatched tags[/i] are not matched to each other.</b>",
            ]],
            [[
                'descr' => "[center]Inlines and [b]blocks get[/b] nested correctly[/center].",
                'bbcode' => "[center]Inlines and [b]blocks get[/b] nested correctly[/center].",
                'html' => "\n<div class=\"bbcode_center\" style=\"text-align:center\">\nInlines and <b>blocks get</b> nested correctly\n</div>\n.",
            ]],
            [[
                'descr' => "[b]Inlines and [center]blocks get[/center] nested correctly[/b].",
                'bbcode' => "[b]Inlines and [center]blocks get[/center] nested correctly[/b].",
                'html' => "<b>Inlines and </b>\n<div class=\"bbcode_center\" style=\"text-align:center\">\nblocks get\n</div>\nnested correctly.",
            ]],
            [[
                'descr' => "BBCode is [B]case-insensitive[/b].",
                'bbcode' => "[cEnTeR][b]This[/B] is a [I]test[/i].[/CeNteR]",
                'html' => "\n<div class=\"bbcode_center\" style=\"text-align:center\">\n<b>This</b> is a <i>test</i>.\n</div>\n",
            ]],
            [[
                'descr' => "Plain text gets passed through unchanged.",
                'bbcode' => "Plain text gets passed through unchanged.  b is not a tag and i is not a tag and neither is /i and neither is (b).",
                'html' => "Plain text gets passed through unchanged.  b is not a tag and i is not a tag and neither is /i and neither is (b).",
            ]],
        ];

        return $result;
    }

    /**
     * Provide special character test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideSpecialCharacterTests() {
        $result = [
            [[
                'descr' => "& and < and > and \" and ' get replaced with HTML-safe equivalents.",
                'bbcode' => "This <woo!> &\"yeah!\" 'sizzle'",
                'html' => "This &lt;woo!&gt; &amp;&quot;yeah!&quot; &#039;sizzle&#039;",
            ]],
            [[
                'descr' => "& and < and > and \" and ' do NOT get replaced with HTML-safe equivalents if setEscapeContent(false).",
                'bbcode' => "This <woo!> &\"yeah!\" 'sizzle'",
                'html' => "This <woo!> &\"yeah!\" 'sizzle'",
                'escape_content' => false,
            ]],
            [[
                'descr' => "Single quotes in tags are NOT considered special characters.",
                'bbcode' => "[wiki='foo' title='bar']",
                'html' => "<a href=\"/?page=foo\" class=\"bbcode_wiki\">bar</a>",
            ]],
            [[
                'descr' => "Double quotes in tags are NOT considered special characters.",
                'bbcode' => "[wiki=\"foo\" title=\"bar\"]",
                'html' => "<a href=\"/?page=foo\" class=\"bbcode_wiki\">bar</a>",
            ]],
            [[
                'descr' => ":-) produces a smiley <img> element.",
                'bbcode' => "This is a test of the emergency broadcasting system :-)",
                'regex' => <<<'REGEX'
`This is a test of the emergency broadcasting system <img src="smileys/smile.gif" alt=":-\)" title=":-\)" class="bbcode_smiley" />`
REGEX
,
            ]],
            [[
                'descr' => "--- does *not* produce a [rule] tag.",
                'bbcode' => "This is a test of the --- emergency broadcasting system.",
                'html' => "This is a test of the --- emergency broadcasting system.",
            ]],
            [[
                'descr' => "---- does *not* produce a [rule] tag.",
                'bbcode' => "This is a test of the ---- emergency broadcasting system.",
                'html' => "This is a test of the ---- emergency broadcasting system.",
            ]],
            [[
                'descr' => "----- produces a [rule] tag.",
                'bbcode' => "This is a test of the ----- emergency broadcasting system.",
                'html' => "This is a test of the\n<hr class=\"bbcode_rule\" />\nemergency broadcasting system.",
            ]],
            [[
                'descr' => "--------- produces a [rule] tag.",
                'bbcode' => "This is a test of the --------- emergency broadcasting system.",
                'html' => "This is a test of the\n<hr class=\"bbcode_rule\" />\nemergency broadcasting system.",
            ]],
            [[
                'descr' => "[-] does *not* produce a comment.",
                'bbcode' => "This is a test of the [- emergency broadcasting] system.",
                'html' => "This is a test of the [- emergency broadcasting] system.",
            ]],
            [[
                'descr' => "[--] produces a comment.",
                'bbcode' => "This is a test of the [-- emergency broadcasting] system.",
                'html' => "This is a test of the  system.",
            ]],
            [[
                'descr' => "[----] produces a comment.",
                'bbcode' => "This is a test of the [---- emergency broadcasting] system.",
                'html' => "This is a test of the  system.",
            ]],
            [[
                'descr' => "[--] comments may contain - and [ and \" and ' characters.",
                'bbcode' => "This is a test of the [-- emergency - [ \" ' broadcasting] system.",
                'html' => "This is a test of the  system.",
            ]],
            [[
                'descr' => "[--] comments may *not* contain newlines.",
                'bbcode' => "This is a test of the [-- emergency\n\rbroadcasting] system.",
                'html' => "This is a test of the [-- emergency<br>\nbroadcasting] system.",
            ]],
            [[
                'descr' => "['] produces a comment.",
                'bbcode' => "This is a test of the ['emergency broadcasting] system.",
                'html' => "This is a test of the  system.",
            ]],
            [[
                'descr' => "['] comments may contain [ and \" and ' characters.",
                'bbcode' => "This is a test of the ['emergency [ \" ' broadcasting] system.",
                'html' => "This is a test of the  system.",
            ]],
            [[
                'descr' => "['] comments may *not* contain newlines.",
                'bbcode' => "This is a test of the [' emergency\n\rbroadcasting] system.",
                'html' => "This is a test of the [&#039; emergency<br>\nbroadcasting] system.",
            ]],
            [[
                'descr' => "[!-- --] produces a comment.",
                'bbcode' => "This is a test of the [!-- emergency broadcasting --] system.",
                'html' => "This is a test of the  system.",
            ]],
            [[
                'descr' => "[!-- ] does *not* produce a viable comment.",
                'bbcode' => "This is a test of the [!-- emergency broadcasting ] system.",
                'html' => "This is a test of the [!-- emergency broadcasting ] system.",
            ]],
            [[
                'descr' => "[!-- - -- ] [ --] produces a comment.",
                'bbcode' => "This is a test of the [!-- emergency - broadcasting -- system ] thingy --].",
                'html' => "This is a test of the .",
            ]],
            [[
                'descr' => "[!-- - -- ] [ --] --] produces a comment with a --] left over.",
                'bbcode' => "This is a test of the [!-- emergency - broadcasting -- system ] thingy --] and other --] stuff.",
                'html' => "This is a test of the  and other --] stuff.",
            ]],
            [[
                'descr' => "[!-- --] does not break any following tags outside it.",
                'bbcode' => "The [!-- quick brown --]fox jumps over the [b]lazy[/b] [i]dog[/i].",
                'html' => "The fox jumps over the <b>lazy</b> <i>dog</i>.",
            ]],
            [[
                'descr' => "Tag marker mode '<' works correctly.",
                'bbcode' => "This is <b>a <i>test</b></i>.",
                'html' => "This is <b>a <i>test</i></b>.",
                'tag_marker' => '<',
            ]],
            [[
                'descr' => "Tag marker mode '{' works correctly.",
                'bbcode' => "This is {b}a {i}test{/b}{/i}.",
                'html' => "This is <b>a <i>test</i></b>.",
                'tag_marker' => '{',
            ]],
            [[
                'descr' => "Tag marker mode '(' works correctly.",
                'bbcode' => "This is (b)a (i)test(/b)(/i).",
                'html' => "This is <b>a <i>test</i></b>.",
                'tag_marker' => '(',
            ]],
            [[
                'descr' => "Ampersand pass-through mode works correctly.",
                'bbcode' => "This is <b>a <i>test</b></i> &amp; some junk.",
                'html' => "This is <b>a <i>test</i></b> &amp; some junk.",
                'tag_marker' => '<',
            ]],
        ];
        return $result;
    }

    /**
     * Provide white space test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideWhitespaceTests() {
        $result = [
            [[
                'descr' => "Newlines get replaced with <br> tags.",
                'bbcode' => "This\nis\r\na\n\rtest.",
                'html' => "This<br>\nis<br>\na<br>\ntest.",
            ]],
            [[
                'descr' => "Newlines *don't* get replaced with <br> tags in ignore-newline mode.",
                'bbcode' => "This\nis\r\na\n\rtest.",
                'html' => "This\nis\na\ntest.",
                'newline_ignore' => true,
            ]],
            [[
                'descr' => "Space before and after newlines gets removed.",
                'bbcode' => "This \n \t is \na\n \x08test.",
                'html' => "This<br>\nis<br>\na<br>\ntest.",
            ]],
            [[
                'descr' => "Whitespace doesn't matter inside tags after the tag name.",
                'bbcode' => "This [size = 4  ]is a test[/size ].",
                'html' => "This <span style=\"font-size:1.17em\">is a test</span>.",
            ]],
            [[
                'descr' => "Whitespace does matter inside \"quotes\" in tags.",
                'bbcode' => "This [wstest=\"  Courier   New  \"]is a test[/wstest].",
                'html' => "This <span style=\"wstest:  Courier   New  \">is a test</span>.",
            ]],
            [[
                'descr' => "Whitespace does matter inside 'quotes' in tags.",
                'bbcode' => "This [wstest='  Courier   New  ']is a test[/wstest].",
                'html' => "This <span style=\"wstest:  Courier   New  \">is a test</span>.",
            ]],
            [[
                'descr' => "Whitespace is properly collapsed near block tags like [center].",
                'bbcode' => <<<BBCODE
Not centered.

[center]

    A bold stone gathers no italics.

[/center]

Not centered.
BBCODE
,
                'html' => "Not centered.<br>\n"
                    . "\n<div class=\"bbcode_center\" style=\"text-align:center\">\n"
                    . "<br>\n"
                    . "A bold stone gathers no italics.<br>\n"
                    . "\n</div>\n"
                    . "<br>\n"
                    . "Not centered.",
            ]],
            [[
                'descr' => "[code]...[/code] should strip whitespace outside it but not inside it.",
                'bbcode' => "Not\ncode.\n"
                    . "[code]    \n\n    This is a test.    \n\n    [/code]\n"
                    . "Also not code.\n",
                'html' => "Not<br>\ncode.\n"
                    . "<div class=\"bbcode_code\">\n"
                    . "<div class=\"bbcode_code_head\">Code:</div>\n"
                    . "<div class=\"bbcode_code_body\" style=\"white-space:pre\">\n    This is a test.    \n</div>\n"
                    . "</div>\n"
                    . "Also not code.<br>\n",
            ]],
            [[
                'descr' => "[list] and [*] must consume correct quantities of whitespace.",
                'bbcode' => "[list]\n\n\t[*] One Box\n\n\t[*] Two Boxes\n\t[*] \n Three Boxes\n\n[/list]\n",
                'html' => "\n<ul class=\"bbcode_list\">\n<br>\n<li>One Box<br>\n</li>\n<li>Two Boxes</li>\n<li><br>\nThree Boxes<br>\n</li>\n</ul>\n",
            ]],
        ];
        return $result;
    }

    /**
     * Provide inline tag conversion test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideInlineConversionTests() {
        $result = [
            [[
                'descr' => "[i] gets correctly converted.",
                'bbcode' => "This is a test of the [i]emergency broadcasting system[/i].",
                'html' => "This is a test of the <i>emergency broadcasting system</i>.",
            ]],
            [[
                'descr' => "[b] gets correctly converted.",
                'bbcode' => "This is a test of the [b]emergency broadcasting system[/b].",
                'html' => "This is a test of the <b>emergency broadcasting system</b>.",
            ]],
            [[
                'descr' => "[u] gets correctly converted.",
                'bbcode' => "This is a test of the [u]emergency broadcasting system[/u].",
                'html' => "This is a test of the <u>emergency broadcasting system</u>.",
            ]],
            [[
                'descr' => "[s] gets correctly converted.",
                'bbcode' => "This is a test of the [s]emergency broadcasting system[/s].",
                'html' => "This is a test of the <strike>emergency broadcasting system</strike>.",
            ]],
            [[
                'descr' => "[sup] gets correctly converted.",
                'bbcode' => "This is a test of the [sup]emergency broadcasting system[/sup].",
                'html' => "This is a test of the <sup>emergency broadcasting system</sup>.",
            ]],
            [[
                'descr' => "[sub] gets correctly converted.",
                'bbcode' => "This is a test of the [sub]emergency broadcasting system[/sub].",
                'html' => "This is a test of the <sub>emergency broadcasting system</sub>.",
            ]],
            [[
                'descr' => "[font=Arial] gets correctly converted (simple font name).",
                'bbcode' => "This is a test of the [font=Arial]emergency broadcasting system[/font].",
                'html' => "This is a test of the <span style=\"font-family:'Arial'\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[font=Times New Roman] gets correctly converted (unquoted default value).",
                'bbcode' => "This is a test of the [font=Times New Roman]emergency broadcasting system[/font].",
                'html' => "This is a test of the <span style=\"font-family:'Times New Roman'\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[font=Times New Roman size=1] gets converted (trailing parameter identified).",
                'bbcode' => "This is a test of the [font=Times New Roman size=1]emergency broadcasting system[/font].",
                'html' => "This is a test of the <span style=\"font-family:'Times New Roman'\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[font=\"Courier New\"] gets correctly converted (double quoted default value).",
                'bbcode' => "This is a test of the [font=\"Courier New\"]emergency broadcasting system[/font].",
                'html' => "This is a test of the <span style=\"font-family:'Courier New'\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[font='Courier New'] gets correctly converted (single quoted default value).",
                'bbcode' => "This is a test of the [font='Courier New']emergency broadcasting system[/font].",
                'html' => "This is a test of the <span style=\"font-family:'Courier New'\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[font=\"Courier New\" blarg size=1] gets converted (floating parameter ignored).",
                'bbcode' => "This is a test of the [font=\"Courier New\" blarg size=1]emergency broadcasting system[/font].",
                'html' => "This is a test of the <span style=\"font-family:'Courier New'\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[size=6] gets correctly converted.",
                'bbcode' => "This is a test of the [size=6]emergency broadcasting system[/size].",
                'html' => "This is a test of the <span style=\"font-size:2.0em\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[size=10] gets correctly converted.",
                'bbcode' => "This is a test of the [size=10]emergency broadcasting system[/size].",
                'html' => "This is a test of the <span style=\"font-size:1.0em\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[size=blah] gets ignored.",
                'bbcode' => "This is a test of the [size=blah]emergency broadcasting system[/size].",
                'html' => "This is a test of the [size=blah]emergency broadcasting system[/size].",
            ]],
            [[
                'descr' => "[color=red] gets correctly converted.",
                'bbcode' => "This is a test of the [color=red]emergency broadcasting system[/color].",
                'html' => "This is a test of the <span style=\"color:red\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[color=gronk] gets correctly converted.",
                'bbcode' => "This is a test of the [color=gronk]emergency broadcasting system[/color].",
                'html' => "This is a test of the <span style=\"color:gronk\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[color=#FFF] gets correctly converted.",
                'bbcode' => "This is a test of the [color=#FFF]emergency broadcasting system[/color].",
                'html' => "This is a test of the <span style=\"color:#FFF\">emergency broadcasting system</span>.",
            ]],
            [[
                'descr' => "[color=*#\$] is prohibited.",
                'bbcode' => "This is a test of the [color=*#\$]emergency broadcasting system[/color].",
                'html' => "This is a test of the [color=*#\$]emergency broadcasting system[/color].",
            ]],
            [[
                'descr' => "[spoiler] gets converted.",
                'bbcode' => "Ssh, don't tell, but [spoiler]Darth is Luke's father[/spoiler]!",
                'html' => "Ssh, don&#039;t tell, but <span class=\"bbcode_spoiler\">Darth is Luke&#039;s father</span>!",
            ]],
            [[
                'descr' => "[acronym] gets converted.",
                'bbcode' => "The [acronym=\"British Broadcasting Company\"]BBC[/acronym] airs [i]Doctor Who[/i] on Saturdays.",
                'html' => "The <span class=\"bbcode_acronym\" title=\"British Broadcasting Company\">BBC</span> airs <i>Doctor Who</i> on Saturdays.",
            ]],
            [[
                'descr' => "[acronym] safely encodes its content.",
                'bbcode' => "The [acronym=_\"><script>alert(/XSS/.source)</script><x]Foo[/acronym] is safe.",
                'html' => "The <span class=\"bbcode_acronym\" title=\"_&quot;&gt;&lt;script&gt;alert(/XSS/.source)&lt;/script&gt;&lt;x\">Foo</span> is safe.",
            ]],
        ];
        return $result;
    }

    /**
     * Provide **[url]** tag test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideUrlTests() {
        $result = [
            [[
                'descr' => "[url=...] (with no protocol given) gets converted.",
                'bbcode' => "This is a test of the [url=fleeb.html]emergency broadcasting system[/url].",
                'html' => "This is a test of the <a href=\"fleeb.html\" class=\"bbcode_url\">emergency broadcasting system</a>.",
            ]],
            [[
                'descr' => "[url=http:...] gets converted.",
                'bbcode' => "This is a test of the [url=http://www.google.com]emergency broadcasting system[/url].",
                'html' => "This is a test of the <a href=\"http://www.google.com\" class=\"bbcode_url\">emergency broadcasting system</a>.",
            ]],
            [[
                'descr' => "[url=http:...] gets converted correctly in plain mode.",
                'bbcode' => "This is a test of the [url=http://www.google.com]emergency broadcasting system[/url].",
                'html' => "This is a test of the <a href=\"http://www.google.com\">emergency broadcasting system</a>.",
                'plainmode' => true,
            ]],
            [[
                'descr' => "Unquoted [url=http:...] with parameters gets converted.",
                'bbcode' => "This is a test of the [url=http://www.google.com?q=broadcasting&y=foo&x=bar]emergency broadcasting system[/url].",
                'html' => "This is a test of the <a href=\"http://www.google.com?q=broadcasting&amp;y=foo&amp;x=bar\" class=\"bbcode_url\">emergency broadcasting system</a>.",
            ]],
            [[
                'descr' => "[url=https:...] gets converted.",
                'bbcode' => "This is a test of the [url=https://www.google.com]emergency broadcasting system[/url].",
                'html' => "This is a test of the <a href=\"https://www.google.com\" class=\"bbcode_url\">emergency broadcasting system</a>.",
            ]],
            [[
                'descr' => "[url=ftp:...] gets converted.",
                'bbcode' => "This is a test of the [url=ftp://www.google.com]emergency broadcasting system[/url].",
                'html' => "This is a test of the <a href=\"ftp://www.google.com\" class=\"bbcode_url\">emergency broadcasting system</a>.",
            ]],
            [[
                'descr' => "[url=mailto:...] gets converted.",
                'bbcode' => "This is a test of the [url=mailto:john@example.com]emergency broadcasting system[/url].",
                'html' => "This is a test of the <a href=\"mailto:john@example.com\" class=\"bbcode_url\">emergency broadcasting system</a>.",
            ]],
            [[
                'descr' => "[url=javascript:...] is prohibited.",
                'bbcode' => "This is a test of the [url=javascript:alert()]emergency broadcasting system[/url].",
                'html' => "This is a test of the [url=javascript:alert()]emergency broadcasting system[/url].",
            ]],
            [[
                'descr' => "[url=(unknown protocol):...] is prohibited.",
                'bbcode' => "This is a test of the [url=flooble:blarble]emergency broadcasting system[/url].",
                'html' => "This is a test of the [url=flooble:blarble]emergency broadcasting system[/url].",
            ]],
            [[
                'descr' => "The [url]http://...[/url] form works correctly.",
                'bbcode' => "The [url]http://www.google.com[/url] form works correctly.",
                'html' => "The <a href=\"http://www.google.com\" class=\"bbcode_url\">http://www.google.com</a> form works correctly.",
            ]],
            [[
                'descr' => "The [url]http://...[/url] form works correctly in plain mode.",
                'bbcode' => "The [url]http://www.google.com[/url] form works correctly.",
                'html' => "The <a href=\"http://www.google.com\">http://www.google.com</a> form works correctly.",
                'plainmode' => true,
            ]],
            [[
                'descr' => "The [url]malformed...url...[/url] form is fully unprocessed.",
                'bbcode' => "The [url]a.imagehost.org/view/egdgdo[/url] form is fully unprocessed.",
                'html' => "The <a href=\"a.imagehost.org/view/egdgdo\" class=\"bbcode_url\">a.imagehost.org/view/egdgdo</a> form is fully unprocessed.",
            ]],
            [[
                'descr' => "[url=\"...=...\"] contains an embedded equal sign (quotes work correctly).",
                'bbcode' => "The [url=\"http://www.google.com/?foo=bar&baz=frob\" bar=foo]link[/url] works correctly.",
                'html' => "The <a href=\"http://www.google.com/?foo=bar&amp;baz=frob\" class=\"bbcode_url\">link</a> works correctly.",
            ]],
            [[
                'descr' => "[url=\"...=...\"] contains an embedded equal sign (test #2).",
                'bbcode' => "The [url=\"http://www.demourl.com/opinion.php?idopinion=234\"]Opinion[/url] is funny.",
                'html' => "The <a href=\"http://www.demourl.com/opinion.php?idopinion=234\" class=\"bbcode_url\">Opinion</a> is funny.",
            ]],
            [[
                'descr' => "[url=\"...\" target=\"...\"] has its target ignored by default.",
                'bbcode' => "The [url=\"http://www.demourl.com/opinion.php?idopinion=234\" target=_blank]Opinion[/url] is funny.",
                'html' => "The <a href=\"http://www.demourl.com/opinion.php?idopinion=234\" class=\"bbcode_url\">Opinion</a> is funny.",
            ]],
            [[
                'descr' => "[url=\"...\" target=\"...\"] has its target used when URL targeting is enabled.",
                'bbcode' => "The [url=\"http://www.demourl.com/opinion.php?idopinion=234\" target=_blank]Opinion[/url] is funny.",
                'html' => "The <a href=\"http://www.demourl.com/opinion.php?idopinion=234\" class=\"bbcode_url\" target=\"_blank\">Opinion</a> is funny.",
                'urltarget' => true,
            ]],
            [[
                'descr' => "[url] has a target applied when forced URL targeting is enabled.",
                'bbcode' => "The [url=\"http://www.demourl.com/opinion.php?idopinion=234\"]Opinion[/url] is funny.",
                'html' => "The <a href=\"http://www.demourl.com/opinion.php?idopinion=234\" class=\"bbcode_url\" target=\"somewhere\">Opinion</a> is funny.",
                'urlforcetarget' => "somewhere",
            ]],
            [[
                'descr' => "[url target=\"...\"] has its target ignored when forced URL targeting is enabled.",
                'bbcode' => "The [url=\"http://www.demourl.com/opinion.php?idopinion=234\" target=\"_blank\"]Opinion[/url] is funny.",
                'html' => "The <a href=\"http://www.demourl.com/opinion.php?idopinion=234\" class=\"bbcode_url\" target=\"somewhere\">Opinion</a> is funny.",
                'urlforcetarget' => "somewhere",
            ]],
            [[
                'descr' => "[url] has a target applied even with URL target overriding.",
                'bbcode' => "The [url=\"http://www.demourl.com/opinion.php?idopinion=234\"]Opinion[/url] is funny.",
                'html' => "The <a href=\"http://www.demourl.com/opinion.php?idopinion=234\" class=\"bbcode_url\" target=\"somewhere\">Opinion</a> is funny.",
                'urlforcetarget' => "somewhere",
                'urltarget' => 'override',
            ]],
            [[
                'descr' => "[url target=\"...\"] has its target applied with URL target overriding.",
                'bbcode' => "The [url=\"http://www.demourl.com/opinion.php?idopinion=234\" target=\"_blank\"]Opinion[/url] is funny.",
                'html' => "The <a href=\"http://www.demourl.com/opinion.php?idopinion=234\" class=\"bbcode_url\" target=\"_blank\">Opinion</a> is funny.",
                'urlforcetarget' => "somewhere",
                'urltarget' => 'override',
            ]],
            [[
                'descr' => "[url=(includes a smiley)] is not converted into a smiley.",
                'bbcode' => "This is a test of the [url=http://www.google.com/foo:-P]emergency broadcasting system[/url].",
                'html' => "This is a test of the <a href=\"http://www.google.com/foo:-P\" class=\"bbcode_url\">emergency broadcasting system</a>.",
            ]],
        ];
        return $result;
    }

    /**
     * Provide auto-embedded URL test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideEmbeddedUrlTests() {
        $result = [
            [[
                'descr' => "Embedded URLs get detected and converted.",
                'bbcode' => "Go to http://www.google.com for your search needs!",
                'html' => "Go to <a href=\"http://www.google.com\">http://www.google.com</a> for your search needs!",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded HTTPS URLs get detected and converted.",
                'bbcode' => "Go to https://www.google.com for your search needs!",
                'html' => "Go to <a href=\"https://www.google.com\">https://www.google.com</a> for your search needs!",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded FTP URLs get detected and converted.",
                'bbcode' => "Go to ftp://www.google.com for your search needs!",
                'html' => "Go to <a href=\"ftp://www.google.com\">ftp://www.google.com</a> for your search needs!",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded Javascript URLs are properly ignored.",
                'bbcode' => "Go to javascript:foo.com;alert(); for your search needs!",
                'html' => "Go to javascript:foo.com;alert(); for your search needs!",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded domain names get detected and converted.",
                'bbcode' => "Go to www.google.com for your search needs!",
                'html' => "Go to <a href=\"http://www.google.com\">www.google.com</a> for your search needs!",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded IPs get detected and converted.",
                'bbcode' => "Go to 127.0.0.1:667/flarb for your own computer!",
                'html' => "Go to <a href=\"http://127.0.0.1:667/flarb\">127.0.0.1:667/flarb</a> for your own computer!",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded addresses are smart about being inside parentheses.",
                'bbcode' => "I love Google! (google.com)",
                'html' => "I love Google! (<a href=\"http://google.com\">google.com</a>)",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded-URL detector disallows junk that only seems like a URL.",
                'bbcode' => "I browse alt.net.screw-you:80/flarb all the time.",
                'html' => "I browse alt.net.screw-you:80/flarb all the time.",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded-URL detector also detects e-mail addresses.",
                'bbcode' => "Send complaints to complaints@whitehouse.gov .",
                'html' => "Send complaints to <a href=\"mailto:complaints@whitehouse.gov\">complaints@whitehouse.gov</a> .",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded-URL detector takes precedence over the smiley detector.",
                'bbcode' => "This is a good dictionary:  http://www.amazon.com/Oxford-Dictionary-American-Usage-Style/dp/0195135083/ref=pd_bbs_sr_1?ie=UTF8&s=books&qid=1217890161&sr=8-1&x=p",
                'html' => "This is a good dictionary:  <a href=\"http://www.amazon.com/Oxford-Dictionary-American-Usage-Style/dp/0195135083/ref=pd_bbs_sr_1?ie=UTF8&amp;s=books&amp;qid=1217890161&amp;sr=8-1&amp;x=p\">http://www.amazon.com/Oxford-Dictionary-American-Usage-Style/dp/0195135083/ref=pd_bbs_sr_1?ie=UTF8&amp;s=books&amp;qid=1217890161&amp;sr=8-1&amp;x=p</a>",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded-URL detector handles single subdomain names.",
                'bbcode' => "m.example.com is a mobile site.",
                'html' => "<a href=\"http://m.example.com\">m.example.com</a> is a mobile site.",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded domain names at the end of sentences get detected and converted.",
                'bbcode' => "Go to www.google.com.",
                'html' => "Go to <a href=\"http://www.google.com\">www.google.com</a>.",
                'detect_urls' => true,
            ]],
            [[
                'descr' => "Embedded links to an invalid TLD with a scheme should work.",
                'bbcode' => 'Go to http://foo.bar',
                'html' => 'Go to <a href="http://foo.bar">http://foo.bar</a>',
                'detect_urls' => true
            ]],
            [[
                'descr' => "Embedded links to an invalid TLD without a scheme should not work.",
                'bbcode' => 'Go to foo.bar',
                'html' => 'Go to foo.bar',
                'detect_urls' => true
            ]],
        ];

        return $result;
    }

    /**
     * Provide test data for URL-like tags.
     *
     * These tags include **[email]** and **[[wiki]]** links.
     *
     * @return array Returns the test function arguments.
     */
    public function provideUrlLikeTagTests() {
        $result = [
            [[
                'descr' => "[email] gets converted.",
                'bbcode' => "Send complaints to [email]john@example.com[/email].",
                'html' => "Send complaints to <a href=\"mailto:john@example.com\" class=\"bbcode_email\">john@example.com</a>.",
            ]],
            [[
                'descr' => "[email] supports both forms.",
                'bbcode' => "Send complaints to [email=john@example.com]John Smith[/email].",
                'html' => "Send complaints to <a href=\"mailto:john@example.com\" class=\"bbcode_email\">John Smith</a>.",
            ]],
            [[
                'descr' => "Bad addresses in [email] are ignored.",
                'bbcode' => "Send complaints to [email]jo\"hn@@@exa:mple.com[/email].",
                'html' => "Send complaints to [email]jo&quot;hn@@@exa:mple.com[/email].",
            ]],
            /*
                    [[
                        'descr' => "[video=youtube] gets converted.",
                        'bbcode' => "Watch this cute doggy!!! [video=youtube]dQw4w9WgXcQ[/video]",
                        'html' => "Watch this cute doggy!!! <object width=\"480\" height=\"385\"><param name=\"movie\" value=\"http://www.youtube.com/v/dQw4w9WgXcQ&hl=en_US&fs=1&\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://www.youtube.com/v/dQw4w9WgXcQ&hl=en_US&fs=1&\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"480\" height=\"385\"></embed></object>",
                    ]],
                    [[
                        'descr' => "[video=hulu] gets converted.",
                        'bbcode' => "Gleeks: [video=hulu]yuo37ilvL7pUlsKJmA6R0g[/video]",
                        'html' => "Gleeks: <object width=\"512\" height=\"288\"><param name=\"movie\" value=\"http://www.hulu.com/embed/yuo37ilvL7pUlsKJmA6R0g\"></param><param name=\"allowFullScreen\" value=\"true\"></param><embed src=\"http://www.hulu.com/embed/yuo37ilvL7pUlsKJmA6R0g\" type=\"application/x-shockwave-flash\"  width=\"512\" height=\"288\" allowFullScreen=\"true\"></embed></object>",
                    ]],
                    [[
                        'descr' => "[video] ignores unknown video services.",
                        'bbcode' => "Watch this cute doggy!!! [video=flarb]abcdefg[/video]",
                        'html' => "Watch this cute doggy!!! [video=flarb]abcdefg[/video]",
                    ]],
                    [[
                        'descr' => "[video] ignores bad video IDs.",
                        'bbcode' => "Watch this cute doggy!!! [video=youtube]b!:=9_?[/video]",
                        'html' => "Watch this cute doggy!!! [video=youtube]b!:=9_?[/video]",
                    ]],
                    [[
                        'descr' => "[video] correctly supports width= and height= modifiers.",
                        'bbcode' => "Watch this cute doggy!!! [video=youtube width=320 height=240]dQw4w9WgXcQ[/video]",
                        'html' => "Watch this cute doggy!!! <object width=\"320\" height=\"240\"><param name=\"movie\" value=\"http://www.youtube.com/v/dQw4w9WgXcQ&hl=en_US&fs=1&\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://www.youtube.com/v/dQw4w9WgXcQ&hl=en_US&fs=1&\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"320\" height=\"240\"></embed></object>",
                    ]],
            */
            [[
                'descr' => "The [[wiki]] special tag produces a wiki link.",
                'bbcode' => "This is a test of the [[wiki]] tag.",
                'html' => "This is a test of the <a href=\"/?page=wiki\" class=\"bbcode_wiki\">wiki</a> tag.",
            ]],
            [[
                'descr' => "The [[wiki]] special tag does not convert [a-zA-Z0-9'\".:_-].",
                'bbcode' => "This is a test of the [[\"Ab1cd'Ef2gh_Ij3kl.,Mn4op:Qr9st-Uv0wx\"]] tag.",
                'html' => "This is a test of the <a href=\"/?page=%22Ab1cd%27Ef2gh_Ij3kl.%2CMn4op%3AQr9st_Uv0wx%22\" class=\"bbcode_wiki\">&quot;Ab1cd&#039;Ef2gh_Ij3kl.,Mn4op:Qr9st-Uv0wx&quot;</a> tag.",
            ]],
            [[
                'descr' => "The [[wiki]] special tag can contain spaces.",
                'bbcode' => "This is a test of the [[northwestern salmon]].",
                'html' => "This is a test of the <a href=\"/?page=northwestern_salmon\" class=\"bbcode_wiki\">northwestern salmon</a>.",
            ]],
            [[
                'descr' => "The [[wiki]] special tag cannot contain newlines.",
                'bbcode' => "This is a test of the [[northwestern\nsalmon]].",
                'html' => "This is a test of the [[northwestern<br>\nsalmon]].",
            ]],
            [[
                'descr' => "The [[wiki]] special tag can contain a title after a | character.",
                'bbcode' => "This is a test of the [[northwestern salmon|Northwestern salmon are yummy!]].",
                'html' => "This is a test of the <a href=\"/?page=northwestern_salmon\" class=\"bbcode_wiki\">Northwestern salmon are yummy!</a>.",
            ]],
            [[
                'descr' => "The [[wiki]] special tag doesn't damage anything outside it.",
                'bbcode' => "I really loved reading [[arc 1|the first story arc]] because it was more entertaining than [[arc 2|the second story arc]] was.",
                'html' => "I really loved reading <a href=\"/?page=arc_1\" class=\"bbcode_wiki\">the first story arc</a> because it was more entertaining than <a href=\"/?page=arc_2\" class=\"bbcode_wiki\">the second story arc</a> was.",
            ]],
            [[
                'descr' => "The [[wiki]] special tag condenses and trims internal whitespace.",
                'bbcode' => "This is a test of the [[  northwestern \t salmon   |   Northwestern   salmon are   yummy!  ]].",
                'html' => "This is a test of the <a href=\"/?page=northwestern_salmon\" class=\"bbcode_wiki\">Northwestern   salmon are   yummy!</a>.",
            ]],
        ];
        return $result;
    }

    /**
     * Provide **[img]** tag test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideImageTests() {
        $result = [
            [[
                'descr' => "[img] produces an image.",
                'bbcode' => "This is the Google logo: [img]http://www.google.com/intl/en_ALL/images/logo.gif[/img].",
                'html' => "This is the Google logo: <img src=\"http://www.google.com/intl/en_ALL/images/logo.gif\" alt=\"logo.gif\" class=\"bbcode_img\" />.",
            ]],
            [[
                'descr' => "[img] disallows a javascript: URL.",
                'bbcode' => "This is the Google logo: [img]javascript:alert()[/img].",
                'html' => "This is the Google logo: [img]javascript:alert()[/img].",
            ]],
            [[
                'descr' => "[img] disallows a URL with an unknown protocol type.",
                'bbcode' => "This is the Google logo: [img]foobar:bar.jpg[/img].",
                'html' => "This is the Google logo: [img]foobar:bar.jpg[/img].",
            ]],
            [[
                'descr' => "[img] disallows HTML content.",
                'bbcode' => "This is the Google logo: [img]<a href='javascript:alert(\"foo\")'>click me</a>[/img].",
                'html' => "This is the Google logo: [img]&lt;a href=&#039;javascript:alert(&quot;foo&quot;)&#039;&gt;click me&lt;/a&gt;[/img].",
            ]],
            [[
                'descr' => "[img] can produce a local image.",
                'bbcode' => "This is a smiley: [img]smile.gif[/img].",
                'html' => "This is a smiley: <img src=\"smileys/smile.gif\" alt=\"smile.gif\" class=\"bbcode_img\" />.",
            ]],
            [[
                'descr' => "[img] can produce a local rooted URL.",
                'bbcode' => "This is a smiley: [img]/smile.gif[/img].",
                'html' => "This is a smiley: <img src=\"/smile.gif\" alt=\"smile.gif\" class=\"bbcode_img\" />.",
            ]],
            [[
                'descr' => "[img] can produce a local relative URL.",
                'bbcode' => "This is a smiley: [img]../smile.gif[/img].",
                'html' => "This is a smiley: <img src=\"../smile.gif\" alt=\"smile.gif\" class=\"bbcode_img\" />.",
            ]],
            [[
                'descr' => "[img=src] should produce an image.",
                'bbcode' => 'This is a smiley: [img=smile.gif?f=1&b=2][/img] okay?',
                'html' => 'This is a smiley: <img src="smileys/smile.gif" alt="smile.gif?f=1&amp;b=2" class="bbcode_img" /> okay?',
            ]],
            [[
                'descr' => "Large embedded image",
                'bbcode' => '[img src="data:image/png;base64,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AAEIQAACEIDA0gTwDwEIQAACEIDAvARIBszLE28QgAAEIAABCMxDAC8QgAAEIAABCCxIgGTAgnBxDQEIQAACEIDAGALYQgACEIAABCBwKwIkA25FmnkgAAEIQAACELgkgAYCEIAABCAAgbsQIBlwF+xM+v+zYyfJiQQxFEDvf+sOAje2wwU15SAp38KAq3KQnnafAAECBAgQIECAAAECBAjMExAGzLNf7Wb9EiBAgAABAgQIECBAgEAQAWFAkEHULENXBAgQIECAAAECBAgQIBBRQBgQcSqZa1I7AQIECBAgQIAAAQIECIQXEAaEH1H8AlVIgAABAgQIECBAgAABArkEhAG55hWlWnUQIECAAAECBAgQIECAQGIBYUDi4Y0t3W0ECBAgQIAAAQIECBAgUEVAGFBlkj36cCYBAgQIECBAgAABAgQIlBQQBpQc6/Wm7CRAgAABAgQIECBAgACB+gLCgPoz3uvQewIECBAgQIAAAQIECBBYTEAYsNjAn+36JECAAAECBAgQIECAAIGVBYQBq0xfnwQIECBAgAABAgQIECBA4EtAGPAFUfFLTwQIECBAgAABAgQIECBAYEtAGLClkveZygkQIECAAAECBAgQIECAwK6AMGCXKPoC9REgQIAAAQIECBAgQIAAgXMCwoBzXjFWq4IAAQIECBAgQIAAAQIECNwQEAbcwBu51V0ECBAgQIAAAQIECBAgQKCVgDCglWT7c5xIgAABAgQIECBAgAABAgS6CAgDurBePdQ+AgQIECBAgAABAgQIECDQX0AY0N/48w3eEiBAgAABAgQI+3YaaAAABXlJREFUECBAgACBwQLCgMHgj+v8ESBAgAABAgQIECBAgACBmQLCgDH6biFAgAABAgQIECBAgAABAmEEhAHdRuFgAgQIECBAgAABAgQIECAQU0AY0HIuziJAgAABAgQIECBAgAABAgkEhAE3h2Q7AQIECBAgQIAAAQIECBDIJiAMOD8xOwgQIECAAAECBAgQIECAQGoBYcCh8VlEgAABAgQIECBAgAABAgTqCAgD3s3ScwIECBAgQIAAAQIECBAgUFRAGPBjsH4SIECAAAECBAgQIECAAIEVBFYPA1aYsR4JECBAgAABAgQIECBAgMAvgQXDgF/9+4cAAQIECBAgQIAAAQIECCwnsEYYsNxYNUyAAAECBAgQIECAAAECBN4LlA0D3rfsDQECBAgQIECAAAECBAgQWFugUhiw9iR1T4AAAQIECBAgQIAAAQIEDgokDwMOdmkZAQIECBAgQIAAAQIECBAg8BLIFwa8SveDAAECBAgQIECAAAECBAgQuCKQIgy40pg9BAgQIECAAAECBAgQIECAwLZA1DBgu1pPCRAgQIAAAQIECBAgQIAAgdsCgcKA2704gAABAgQIECBAgAABAgQIEDggMDcMOFCgJQQIECBAgAABAgQIECBAgEBbgeFhQNvynUaAAAECBAgQIECAAAECBAicFRgRBpytyXoCBAgQIECAAAECBAgQIECgo0CnMKBjxY4mQIAAAQIECBAgQIAAAQIEbgm0CwNulWEzAQIECBAgQIAAAQIECBAgMErgVhgwqkj3ECBAgAABAgQIECBAgAABAu0EzoYB7W52EgECBAgQIECAAAECBAgQIDBF4EAYMKUulxIgQIAAAQIECBAgQIAAAQKdBLbDgE6XOZYAAQIECBAgQIAAAQIECBCYL/AKA+aXogICBAgQIECAAAECBAgQIECgt8DjfGHAQ8EfAQIECBAgQIAAAQIECBCoK/CnM2HAHxIPCBAgQIAAAQIECBAgQIBAdoHP9QsDPvt4S4AAAQIECBAgQIAAAQIEcgicqFIYcALLUgIECBAgQIAAAQIECBAgEEngai3CgKty9hEgQIAAAQIECBAgQIAAgfECTW4UBjRhdAgBAgQIECBAgAABAgQIEOgl0P5cYUB7UycSIECAAAECBAgQIECAAIF7Ap13CwM6AzueAAECBAgQIECAAAECBAgcERi5RhgwUttdBAgQIECAAAECBAgQIEDgW2DaL2HANHoXEyBAgAABAgQIECBAgMB6AjE6FgbEmIMqCBAgQIAAAQIECBAgQKCqQMC+hAEBh6IkAgQIECBAgAABAgQIEMgtEL16YUD0CamPAAECBAgQIECAAAECBDIIpKpRGJBqXIolQIAAAQIECBAgQIAAgTgCeSsRBuSdncoJECBAgAABAgQIECBAYLRAkfuEAUUGqQ0CBAgQIECAAAECBAgQ6CNQ8VRhQMWp6okAAQIECBAgQIAAAQIE7giU3ysMKD9iDRIgQIAAAQIECBAgQIDAvsBaK4QBa81btwQIECBAgAABAgQIECDwX2Dhb2HAwsPXOgECBAgQIECAAAECBFYT0O9TQBjwdPBJgAABAgQIECBAgAABAjUFdLUhIAzYQPGIAAECBAgQIECAAAECBDILqH1PQBiwJ+Q9AQIECBAgQIAAAQIECMQXUOEpAWHAKS6LCRAgQIAAAQIECBAgQCCKgDquCwgDrtvZSYAAAQIECBAgQIAAAQJjBdzWSEAY0AjSMQQIECBAgAABAgQIECDQQ8CZPQSEAT1UnUmAAAECBAgQIECAAAEC1wXs7C4gDOhO7AICBAgQIECAAAECBAgQ2BPwfqyAMGCst9sIECBAgAABAgQIECBA4Cngc6LAPwAAAP//Ma10agAAAAZJREFUAwC9cvZC1DsnkAAAAABJRU5ErkJggg=="][/img]',
                /* The tag appears unparsed because the URL is detected as invalid by isValidURL(). However, this test
                   is merely to ensure the lexer no longer crashes on large input with, "JIT stack limit exhausted". */
                'html' => '[img src=&quot;data:image/png;base64,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AAEIQAACEIDA0gTwDwEIQAACEIDAvARIBszLE28QgAAEIAABCMxDAC8QgAAEIAABCCxIgGTAgnBxDQEIQAACEIDAGALYQgACEIAABCBwKwIkA25FmnkgAAEIQAACELgkgAYCEIAABCAAgbsQIBlwF+xM+v+zYyfJiQQxFEDvf+sOAje2wwU15SAp38KAq3KQnnafAAECBAgQIECAAAECBAjMExAGzLNf7Wb9EiBAgAABAgQIECBAgEAQAWFAkEHULENXBAgQIECAAAECBAgQIBBRQBgQcSqZa1I7AQIECBAgQIAAAQIECIQXEAaEH1H8AlVIgAABAgQIECBAgAABArkEhAG55hWlWnUQIECAAAECBAgQIECAQGIBYUDi4Y0t3W0ECBAgQIAAAQIECBAgUEVAGFBlkj36cCYBAgQIECBAgAABAgQIlBQQBpQc6/Wm7CRAgAABAgQIECBAgACB+gLCgPoz3uvQewIECBAgQIAAAQIECBBYTEAYsNjAn+36JECAAAECBAgQIECAAIGVBYQBq0xfnwQIECBAgAABAgQIECBA4EtAGPAFUfFLTwQIECBAgAABAgQIECBAYEtAGLClkveZygkQIECAAAECBAgQIECAwK6AMGCXKPoC9REgQIAAAQIECBAgQIAAgXMCwoBzXjFWq4IAAQIECBAgQIAAAQIECNwQEAbcwBu51V0ECBAgQIAAAQIECBAgQKCVgDCglWT7c5xIgAABAgQIECBAgAABAgS6CAgDurBePdQ+AgQIECBAgAABAgQIECDQX0AY0N/48w3eEiBAgAABAgQI+3YaaAAABXlJREFUECBAgACBwQLCgMHgj+v8ESBAgAABAgQIECBAgACBmQLCgDH6biFAgAABAgQIECBAgAABAmEEhAHdRuFgAgQIECBAgAABAgQIECAQU0AY0HIuziJAgAABAgQIECBAgAABAgkEhAE3h2Q7AQIECBAgQIAAAQIECBDIJiAMOD8xOwgQIECAAAECBAgQIECAQGoBYcCh8VlEgAABAgQIECBAgAABAgTqCAgD3s3ScwIECBAgQIAAAQIECBAgUFRAGPBjsH4SIECAAAECBAgQIECAAIEVBFYPA1aYsR4JECBAgAABAgQIECBAgMAvgQXDgF/9+4cAAQIECBAgQIAAAQIECCwnsEYYsNxYNUyAAAECBAgQIECAAAECBN4LlA0D3rfsDQECBAgQIECAAAECBAgQWFugUhiw9iR1T4AAAQIECBAgQIAAAQIEDgokDwMOdmkZAQIECBAgQIAAAQIECBAg8BLIFwa8SveDAAECBAgQIECAAAECBAgQuCKQIgy40pg9BAgQIECAAAECBAgQIECAwLZA1DBgu1pPCRAgQIAAAQIECBAgQIAAgdsCgcKA2704gAABAgQIECBAgAABAgQIEDggMDcMOFCgJQQIECBAgAABAgQIECBAgEBbgeFhQNvynUaAAAECBAgQIECAAAECBAicFRgRBpytyXoCBAgQIECAAAECBAgQIECgo0CnMKBjxY4mQIAAAQIECBAgQIAAAQIEbgm0CwNulWEzAQIECBAgQIAAAQIECBAgMErgVhgwqkj3ECBAgAABAgQIECBAgAABAu0EzoYB7W52EgECBAgQIECAAAECBAgQIDBF4EAYMKUulxIgQIAAAQIECBAgQIAAAQKdBLbDgE6XOZYAAQIECBAgQIAAAQIECBCYL/AKA+aXogICBAgQIECAAAECBAgQIECgt8DjfGHAQ8EfAQIECBAgQIAAAQIECBCoK/CnM2HAHxIPCBAgQIAAAQIECBAgQIBAdoHP9QsDPvt4S4AAAQIECBAgQIAAAQIEcgicqFIYcALLUgIECBAgQIAAAQIECBAgEEngai3CgKty9hEgQIAAAQIECBAgQIAAgfECTW4UBjRhdAgBAgQIECBAgAABAgQIEOgl0P5cYUB7UycSIECAAAECBAgQIECAAIF7Ap13CwM6AzueAAECBAgQIECAAAECBAgcERi5RhgwUttdBAgQIECAAAECBAgQIEDgW2DaL2HANHoXEyBAgAABAgQIECBAgMB6AjE6FgbEmIMqCBAgQIAAAQIECBAgQKCqQMC+hAEBh6IkAgQIECBAgAABAgQIEMgtEL16YUD0CamPAAECBAgQIECAAAECBDIIpKpRGJBqXIolQIAAAQIECBAgQIAAgTgCeSsRBuSdncoJECBAgAABAgQIECBAYLRAkfuEAUUGqQ0CBAgQIECAAAECBAgQ6CNQ8VRhQMWp6okAAQIECBAgQIAAAQIE7giU3ysMKD9iDRIgQIAAAQIECBAgQIDAvsBaK4QBa81btwQIECBAgAABAgQIECDwX2Dhb2HAwsPXOgECBAgQIECAAAECBFYT0O9TQBjwdPBJgAABAgQIECBAgAABAjUFdLUhIAzYQPGIAAECBAgQIECAAAECBDILqH1PQBiwJ+Q9AQIECBAgQIAAAQIECMQXUOEpAWHAKS6LCRAgQIAAAQIECBAgQCCKgDquCwgDrtvZSYAAAQIECBAgQIAAAQJjBdzWSEAY0AjSMQQIECBAgAABAgQIECDQQ8CZPQSEAT1UnUmAAAECBAgQIECAAAEC1wXs7C4gDOhO7AICBAgQIECAAAECBAgQ2BPwfqyAMGCst9sIECBAgAABAgQIECBA4Cngc6LAPwAAAP//Ma10agAAAAZJREFUAwC9cvZC1DsnkAAAAABJRU5ErkJggg==&quot;][/img]',
            ]],
        ];
        return $result;
    }

    /**
     * Provide block tag conversion test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideBlockTagConversionTests() {
        $result = [
            [[
                'descr' => "[rule] produces a horizontal rule.",
                'bbcode' => "This is a test of the [rule] emergency broadcasting system.",
                'html' => "This is a test of the\n<hr class=\"bbcode_rule\" />\nemergency broadcasting system.",
            ]],
            [[
                'descr' => "[br] is equivalent to a newline.",
                'bbcode' => "This is a newline.    [br]    And here we are!    \n  And more!",
                'html' => "This is a newline.<br>\nAnd here we are!<br>\nAnd more!",
            ]],
            [[
                'descr' => "[center]...[/center] should produce centered alignment.",
                'bbcode' => "Not centered.[center]A [b]bold[/b] stone gathers no italics.[/center]Not centered.",
                'html' => "Not centered.\n"
                    . "<div class=\"bbcode_center\" style=\"text-align:center\">\n"
                    . "A <b>bold</b> stone gathers no italics.\n"
                    . "</div>\n"
                    . "Not centered.",
            ]],
            [[
                'descr' => "[left]...[/left] should produce left alignment.",
                'bbcode' => "Not left.[left]A [b]bold[/b] stone gathers no italics.[/left]Not left.",
                'html' => "Not left.\n"
                    . "<div class=\"bbcode_left\" style=\"text-align:left\">\n"
                    . "A <b>bold</b> stone gathers no italics.\n"
                    . "</div>\n"
                    . "Not left.",
            ]],
            [[
                'descr' => "[right]...[/right] should produce right alignment.",
                'bbcode' => "Not right.[right]A [b]bold[/b] stone gathers no italics.[/right]Not right.",
                'html' => "Not right.\n"
                    . "<div class=\"bbcode_right\" style=\"text-align:right\">\n"
                    . "A <b>bold</b> stone gathers no italics.\n"
                    . "</div>\n"
                    . "Not right.",
            ]],
            [[
                'descr' => "[indent]...[/indent] should produce indented content.",
                'bbcode' => "Not indented.[indent]A [b]bold[/b] stone gathers no italics.[/indent]Not indented.",
                'html' => "Not indented.\n"
                    . "<div class=\"bbcode_indent\" style=\"margin-left:4em\">\n"
                    . "A <b>bold</b> stone gathers no italics.\n"
                    . "</div>\n"
                    . "Not indented.",
            ]],
            [[
                'descr' => "[code]...[/code] should reproduce its contents exactly as they're given.",
                'bbcode' => "Not code."
                    . "[code]A [b]and[/b] & <woo>!\n\tAnd a ['hey'] and a [/nonny] and a ho ho ho![/code]"
                    . "Also not code.",
                'html' => "Not code."
                    . "\n<div class=\"bbcode_code\">\n"
                    . "<div class=\"bbcode_code_head\">Code:</div>\n"
                    . "<div class=\"bbcode_code_body\" style=\"white-space:pre\">A [b]and[/b] &amp; &lt;woo&gt;!\n"
                    . "\tAnd a [&#039;hey&#039;] and a [/nonny] and a ho ho ho!</div>\n"
                    . "</div>\n"
                    . "Also not code.",
            ]],
            [[
                'descr' => "[code]...[/code] should reproduce PHP source code undamaged.",
                'bbcode' => "Not code.\n"
                    . "[code]\n"
                    . "\$foo['bar'] = 42;\n"
                    . "if (\$foo[\"bar\"] < 42) \$foo[] = 0;\n"
                    . "[/code]\n"
                    . "Also not code.\n",
                'html' => "Not code."
                    . "\n<div class=\"bbcode_code\">\n"
                    . "<div class=\"bbcode_code_head\">Code:</div>\n"
                    . "<div class=\"bbcode_code_body\" style=\"white-space:pre\">\$foo[&#039;bar&#039;] = 42;\n"
                    . "if (\$foo[&quot;bar&quot;] &lt; 42) \$foo[] = 0;</div>\n"
                    . "</div>\n"
                    . "Also not code.<br>\n",
            ]],
            [[
                'descr' => "<code>...</code> should not misbehave in '<' tag marker mode.",
                'bbcode' => "Not code."
                    . "<code>A <b>and</b> & <woo>!\n\tAnd a [hey] and a [/nonny] and a ho ho ho!</code>"
                    . "Also not code.",
                'html' => "Not code."
                    . "\n<div class=\"bbcode_code\">\n"
                    . "<div class=\"bbcode_code_head\">Code:</div>\n"
                    . "<div class=\"bbcode_code_body\" style=\"white-space:pre\">A &lt;b&gt;and&lt;/b&gt; &amp; &lt;woo&gt;!\n"
                    . "\tAnd a [hey] and a [/nonny] and a ho ho ho!</div>\n"
                    . "</div>\n"
                    . "Also not code.",
                'tag_marker' => '<',
            ]],
            [[
                'descr' => "[quote]...[/quote] should produce a plain quote.",
                'bbcode' => "Outside the quote."
                    . "[quote]A [b]and[/b] & <woo>!\n\tAnd a [hey] and a [/nonny] and a ho ho ho![/quote]"
                    . "Also outside the quote.",
                'html' => "Outside the quote."
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">Quote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">A <b>and</b> &amp; &lt;woo&gt;!<br>\n"
                    . "And a [hey] and a [/nonny] and a ho ho ho!</div>\n"
                    . "</div>\n"
                    . "Also outside the quote.",
            ]],
            [[
                'descr' => "Multiple nested [quote]...[/quote] tags should produce nested quotes.",
                'bbcode' => "text0\n[quote]\n[quote]\n[quote]text1[/quote]\ntext2[/quote]\ntext3[/quote]\ntext4",
                'html' => "text0"
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">Quote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">"
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">Quote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">"
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">Quote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">"
                    . "text1"
                    . "</div>\n"
                    . "</div>\n"
                    . "text2"
                    . "</div>\n"
                    . "</div>\n"
                    . "text3"
                    . "</div>\n"
                    . "</div>\n"
                    . "text4",
            ]],
            [[
                'descr' => "Multiple nested [quote]...[/quote] tags should produce nested quotes.",
                'bbcode' => "[quote]\n[quote]\n[quote]text1[/quote]\ntext2[/quote]\ntext3[/quote]\ntext4 :) text5 :o text6 :o",
                'html' => "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">Quote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">"
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">Quote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">"
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">Quote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">"
                    . "text1"
                    . "</div>\n"
                    . "</div>\n"
                    . "text2"
                    . "</div>\n"
                    . "</div>\n"
                    . "text3"
                    . "</div>\n"
                    . "</div>\n"
                    . "text4 <img src=\"smileys/smile.gif\" alt=\":)\" title=\":)\" class=\"bbcode_smiley\" /> text5 :o text6 :o",
            ]],
            [[
                'descr' => "[quote=John]...[/quote] should produce a quote from John.",
                'bbcode' => "Outside the quote."
                    . "[quote=John]A [b]and[/b] & <woo>!\n\tAnd a [hey] and a [/nonny] and a ho ho ho![/quote]"
                    . "Also outside the quote.",
                'html' => "Outside the quote."
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">John wrote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">A <b>and</b> &amp; &lt;woo&gt;!<br>\n"
                    . "And a [hey] and a [/nonny] and a ho ho ho!</div>\n"
                    . "</div>\n"
                    . "Also outside the quote.",
            ]],
            [[
                'descr' => "[quote=\"John Smith\"]...[/quote] should produce a quote from John Smith.",
                'bbcode' => "Outside the quote."
                    . "[quote=\"John Smith\"]A [b]and[/b] & <woo>!\n\tAnd a [hey] and a [/nonny] and a ho ho ho![/quote]"
                    . "Also outside the quote.",
                'html' => "Outside the quote."
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">John Smith wrote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">A <b>and</b> &amp; &lt;woo&gt;!<br>\n"
                    . "And a [hey] and a [/nonny] and a ho ho ho!</div>\n"
                    . "</div>\n"
                    . "Also outside the quote.",
            ]],
            [[
                'descr' => "[quote name= date= url=]...[/quote] should produce a detailed quote.",
                'bbcode' => "Outside the quote."
                    . "[quote name=\"John Smith\" date=\"July 4, 1776\" url=\"http://www.constitution.gov\"]We hold these truths to be self-evident...[/quote]"
                    . "Also outside the quote.",
                'html' => "Outside the quote."
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\"><a href=\"http://www.constitution.gov\">John Smith wrote on July 4, 1776:</a></div>\n"
                    . "<div class=\"bbcode_quote_body\">We hold these truths to be self-evident...</div>\n"
                    . "</div>\n"
                    . "Also outside the quote.",
            ]],
            [[
                'descr' => "[quote name= date= url=]...[/quote] should disallow bad URLs.",
                'bbcode' => "Outside the quote."
                    . "[quote name=\"John Smith\" date=\"July 4, 1776\" url=\"javascript:alert()\"]We hold these truths to be self-evident...[/quote]"
                    . "Also outside the quote.",
                'html' => "Outside the quote."
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">John Smith wrote on July 4, 1776:</div>\n"
                    . "<div class=\"bbcode_quote_body\">We hold these truths to be self-evident...</div>\n"
                    . "</div>\n"
                    . "Also outside the quote.",
            ]],
            [[
                'descr' => "[quote=\"<script>javascript:alert()</script>\"] should not produce Javascript.",
                'bbcode' => "Outside the quote."
                    . "[quote=\"<script>javascript:alert()</script>\"]A [b]and[/b] & <woo>!\n\tAnd a [hey] and a [/nonny] and a ho ho ho![/quote]"
                    . "Also outside the quote.",
                'html' => "Outside the quote."
                    . "\n<div class=\"bbcode_quote\">\n"
                    . "<div class=\"bbcode_quote_head\">&lt;script&gt;javascript:alert()&lt;/script&gt; wrote:</div>\n"
                    . "<div class=\"bbcode_quote_body\">A <b>and</b> &amp; &lt;woo&gt;!<br>\n"
                    . "And a [hey] and a [/nonny] and a ho ho ho!</div>\n"
                    . "</div>\n"
                    . "Also outside the quote.",
            ]],
            [[
                'descr' => "[columns] should produce columns.",
                'bbcode' => "Before the columns."
                    . "[columns]This is a test.[nextcol]This is [b]beside[/b] it.[nextcol]This is [i]also[/i] beside it.[/columns]"
                    . "After the columns.",
                'html' => "Before the columns."
                    . "\n<table class=\"bbcode_columns\"><tbody><tr><td class=\"bbcode_column bbcode_firstcolumn\">\n"
                    . "This is a test."
                    . "\n</td><td class=\"bbcode_column\">\n"
                    . "This is <b>beside</b> it."
                    . "\n</td><td class=\"bbcode_column\">\n"
                    . "This is <i>also</i> beside it."
                    . "\n</td></tr></tbody></table>\n"
                    . "After the columns.",
            ]],
            [[
                'descr' => "[nextcol] doesn't do anything outside a [columns] block.",
                'bbcode' => "Here is some text.[nextcol]\nHere is some more.\n",
                'html' => "Here is some text.[nextcol]<br>\nHere is some more.<br>\n",
            ]],
            [[
                'descr' => "Bad column misuse doesn't break layouts.",
                'bbcode' => "[center][columns]This is a test.[nextcol]This is also a [b]test[/b].[/center][/columns]",
                'html' => "\n<div class=\"bbcode_center\" style=\"text-align:center\">\n"
                    . "\n<table class=\"bbcode_columns\"><tbody><tr><td class=\"bbcode_column bbcode_firstcolumn\">\n"
                    . "This is a test."
                    . "\n</td><td class=\"bbcode_column\">\n"
                    . "This is also a <b>test</b>."
                    . "\n</td></tr></tbody></table>\n"
                    . "\n</div>\n",
            ]],
        ];
        return $result;
    }

    /**
     * Provide list and list-item test data.
     *
     * @return array Returns the test function arguments.
     */
    public function provideListAndListItemTests() {
        $result = [
            [[
                'descr' => "[list] and [*] should produce an unordered list.",
                'bbcode' => "[list][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ul class=\"bbcode_list\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ul>\n",
            ]],
            [[
                'descr' => "[list] and [*] should produce an unordered list even without [/list].",
                'bbcode' => "[list][*]One Box[*]Two Boxes[*]Three Boxes",
                'html' => "\n<ul class=\"bbcode_list\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ul>\n",
            ]],
            [[
                'descr' => "[list=circle] should produce an unordered list.",
                'bbcode' => "[list=circle][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ul class=\"bbcode_list\" style=\"list-style-type:circle\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ul>\n",
            ]],
            [[
                'descr' => "[list=disc] should produce an unordered list.",
                'bbcode' => "[list=disc][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ul class=\"bbcode_list\" style=\"list-style-type:disc\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ul>\n",
            ]],
            [[
                'descr' => "[list=square] should produce an unordered list.",
                'bbcode' => "[list=square][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ul class=\"bbcode_list\" style=\"list-style-type:square\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ul>\n",
            ]],
            [[
                'descr' => "[list=1] should produce an ordered list.",
                'bbcode' => "[list=1][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ol class=\"bbcode_list\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ol>\n",
            ]],
            [[
                'descr' => "[list=A] should produce an ordered list.",
                'bbcode' => "[list=A][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ol class=\"bbcode_list\" style=\"list-style-type:upper-alpha\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ol>\n",
            ]],
            [[
                'descr' => "[list=a] should produce an ordered list.",
                'bbcode' => "[list=a][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ol class=\"bbcode_list\" style=\"list-style-type:lower-alpha\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ol>\n",
            ]],
            [[
                'descr' => "[list=I] should produce an ordered list.",
                'bbcode' => "[list=I][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ol class=\"bbcode_list\" style=\"list-style-type:upper-roman\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ol>\n",
            ]],
            [[
                'descr' => "[list=i] should produce an ordered list.",
                'bbcode' => "[list=i][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ol class=\"bbcode_list\" style=\"list-style-type:lower-roman\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ol>\n",
            ]],
            [[
                'descr' => "[list=greek] should produce an ordered list.",
                'bbcode' => "[list=greek][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ol class=\"bbcode_list\" style=\"list-style-type:lower-greek\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ol>\n",
            ]],
            [[
                'descr' => "[list=georgian] should produce an ordered list.",
                'bbcode' => "[list=georgian][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ol class=\"bbcode_list\" style=\"list-style-type:georgian\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ol>\n",
            ]],
            [[
                'descr' => "[list=armenian] should produce an ordered list.",
                'bbcode' => "[list=armenian][*]One Box[*]Two Boxes[*]Three Boxes[/list]",
                'html' => "\n<ol class=\"bbcode_list\" style=\"list-style-type:armenian\">\n<li>One Box</li>\n<li>Two Boxes</li>\n<li>Three Boxes</li>\n</ol>\n",
            ]],
        ];
        return $result;
    }

    /**
     * Run a given test array.
     *
     * @param array $test The test to run.
     */
    protected function performTest($test) {
        $testDefaults = [
            'newline_ignore' => false,
            'detect_urls' => false,
            'urltarget' => false,
            'urlforcetarget' => false,
            'plainmode' => false,
            'tag_marker' => '',
            'skip' => false

        ];
        $test = array_replace($testDefaults, $test);

        if ($test['skip']) {
            $this->markTestSkipped('Skipped test: '.$test['descr']);
            return;
        }

        $bbcode = new BBCode();

        $bbcode->addRule('wstest', [
            'mode' => BBCode::BBCODE_MODE_ENHANCED,
            'allow' => ['_default' => '/^[a-zA-Z0-9._ -]+$/'],
            'template' => '<span style="wstest:{$_default}">{$_content}</span>',
            'class' => 'inline',
            'allow_in' => ['listitem', 'block', 'columns', 'inline', 'link'],
        ]);

        $bbcode->setLocalImgDir(__DIR__.'/../smileys');
        $bbcode->setLocalImgURL('smileys');
        $bbcode->setTagMarker('[');
        $bbcode->setAllowAmpersand(false);
        $bbcode->setIgnoreNewlines((bool)$test['newline_ignore']);
        $bbcode->setDetectURLs((bool)$test['detect_urls']);
        $bbcode->setURLTargetable($test['urltarget'] == true);
        $bbcode->setURLTarget($test['urlforcetarget']);
        $bbcode->setPlainMode($test['plainmode']);
        $bbcode->setEscapeContent($test['escape_content'] ?? true);

        if ($test['tag_marker'] === '<') {
            $bbcode->setTagMarker('<');
            $bbcode->setAllowAmpersand(true);
        } elseif (!empty($test['tag_marker'])) {
            $bbcode->setTagMarker($test['tag_marker']);
        }

        $result = $bbcode->parse($test['bbcode']);
        if (!empty($test['regex'])) {
            $this->assertRegExp($test['regex'], $result, $test['descr']);
        } else {
            $this->assertSame($test['html'], $result, $test['descr']);
        }
    }

    /**
     * Test input validation.
     *
     * @param array $test The test to perform.
     * @dataProvider provideInputValidationTests
     */
    public function testInputValidation(array $test) {
        $this->performTest($test);
    }

    /**
     * Test special characters.
     *
     * @param array $test The test to perform.
     * @dataProvider provideSpecialCharacterTests
     */
    public function testSpecialCharacters(array $test) {
        $this->performTest($test);
    }

    /**
     * Test whitespace edge cases.
     *
     * @param array $test The test to perform.
     * @dataProvider provideWhitespaceTests
     */
    public function testWhitespace(array $test) {
        $this->performTest($test);
    }

    /**
     * Test inline tag conversions.
     *
     * @param array $test The test to perform.
     * @dataProvider provideInlineConversionTests
     */
    public function testInlineConversions(array $test) {
        $this->performTest($test);
    }

    /**
     * Test **[url]** tags.
     *
     * @param array $test The test to perform.
     * @dataProvider provideUrlTests
     */
    public function testUrls(array $test) {
        $this->performTest($test);
    }

    /**
     * Test auto-generated URLs.
     *
     * @param array $test The test to perform.
     * @dataProvider provideEmbeddedUrlTests
     */
    public function testEmbeddedUrls(array $test) {
        $this->performTest($test);
    }

    /**
     * Test tags that generate links.
     *
     * @param array $test The test to perform.
     * @dataProvider provideUrlLikeTagTests
     */
    public function testUrlLikeTags(array $test) {
        $this->performTest($test);
    }

    /**
     * Test **[img]** tags.
     *
     * @param array $test The test to perform.
     * @dataProvider provideImageTests
     */
    public function testImages(array $test) {
        $this->performTest($test);
    }

    /**
     * Test block tag conversions.
     *
     * @param array $test The test to perform.
     * @dataProvider provideBlockTagConversionTests
     */
    public function testBlockTagConversions(array $test) {
        $this->performTest($test);
    }

    /**
     * Test lists and list items.
     *
     * @param array $test The test to perform.
     * @dataProvider provideListAndListItemTests
     */
    public function testListAndListItems(array $test) {
        $this->performTest($test);
    }
}
