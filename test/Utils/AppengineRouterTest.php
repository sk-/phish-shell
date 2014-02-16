<?php
/**
 * Tests for the class \Phish\Phish\Shell.
 *
 * PHP version 5
 *
 * LICENSE: Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Test
 * @package   Phish
 * @author    Sebastian Kreft <author@example.com>
 * @copyright 2014 Sebastian Kreft
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link      http://github.com/sk-/phish-shell
 */

namespace Phish\Utils;


class AppengineRouterTest extends \PHPUnit_Framework_TestCase
{
    public static function getOutput($handlers, $url)
    {
        ob_start();
        AppengineRouter::route($handlers, $url);
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    public function assertRouteWorks($expected_output, $handlers, $url)
    {
        $this->assertEquals($expected_output, $this->getOutput($handlers, $url));
    }

    public function testScript()
    {
        $handlers = array(
            array(
                'script' => 'test/Utils/data/foo_data.php',
                'url' => '/foo/bar[12]'
            )
        );
        $this->assertRouteWorks('Test PHP', $handlers, '/foo/bar1');
        $this->assertRouteWorks('Test PHP', $handlers, '/foo/bar2');
    }

    public function testScriptGroupRegex()
    {
        $handlers = array(
            array(
                'script' => 'test/Utils/data/\1_bar.php',
                'url' => '/(v[12])/bar'
            )
        );
        $this->assertRouteWorks('V1 bar', $handlers, '/v1/bar');
        $this->assertRouteWorks('V2 bar', $handlers, '/v2/bar');
    }

    public function testMultipleHandlers()
    {
        $handlers = array(
            array(
                'script' => 'test/Utils/data/v1_bar.php',
                'url' => '/v1/bar'
            ),
            array(
                'script' => 'test/Utils/data/v2_bar.php',
                'url' => '/v2/bar'
            )
        );
        $this->assertRouteWorks('V1 bar', $handlers, '/v1/bar');
        $this->assertRouteWorks('V2 bar', $handlers, '/v2/bar');
    }

    /**
     * @runInSeparateProcess
     */
    public function testStaticDir()
    {
        $handlers = array(
            array(
                'static_dir' => 'test/Utils/data',
                'url' => '/static'
            )
        );
        $this->assertRouteWorks(
            "<?php\necho 'V1 bar';", $handlers, '/static/v1_bar.php'
        );
        $this->assertRouteWorks(
            "<?php\necho 'V2 bar';", $handlers, '/static/v2_bar.php'
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testStaticFile()
    {
        $handlers = array(
            array(
                'static_files' => 'test/Utils/data/v1_bar.php',
                'upload' => 'test/Utils/data/v1_bar.php',
                'url' => '/static/foo'
            )
        );
        $this->assertRouteWorks("<?php\necho 'V1 bar';", $handlers, '/static/foo');
    }

    /**
     * @runInSeparateProcess
     */
    public function testStaticFileUploadRegex()
    {
        $handlers = array(
            array(
                'static_files' => 'test/Utils/data/v1_bar.php',
                'upload' => 'test/Utils/data/.*',
                'url' => '/static/foo'
            )
        );
        $this->assertRouteWorks("<?php\necho 'V1 bar';", $handlers, '/static/foo');
    }

    /**
     * @runInSeparateProcess
     */
    public function testStaticFileGroupRegex()
    {
        $handlers = array(
            array(
                'static_files' => 'test/Utils/data/v\1_bar.php',
                'upload' => 'test/Utils/data/.*',
                'url' => '/static/foo([12])'
            )
        );
        $this->assertRouteWorks("<?php\necho 'V1 bar';", $handlers, '/static/foo1');
        $this->assertRouteWorks("<?php\necho 'V2 bar';", $handlers, '/static/foo2');
    }
}