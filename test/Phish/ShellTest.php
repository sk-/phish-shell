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

namespace Phish\Phish;


class ShellSessionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->shell = new Shell();
    }

    public function testSimpleStatements()
    {
        $this->assertEquals('1', $this->shell->execute('echo 1;'));
        $this->assertEquals('Foo', $this->shell->execute('echo "Foo";'));
    }

    public function testSimpleStatementsWithoutTrailingSemicolon()
    {
        $this->assertEquals('1', $this->shell->execute('echo 1'));
        $this->assertEquals('Foo', $this->shell->execute('echo "Foo"'));
    }

    public function testSyntaxError()
    {
        $this->assertEquals(
            "syntax error, unexpected ';'",
            $this->shell->execute('echo')
        );
        $this->assertEquals(
            'syntax error, unexpected end of file, expecting variable (T_VARIABLE) or ${ (T_DOLLAR_OPEN_CURLY_BRACES) or {$ (T_CURLY_OPEN)',
            $this->shell->execute('echo "')
        );
    }

    public function testStatementRaisesException()
    {
        $this->assertEquals(
            "Division by zero",
            $this->shell->execute('echo 1/0')
        );
    }

    public function testLocalVariables()
    {
        $this->assertEquals('', $this->shell->execute('$a = 1'));
        $this->assertEquals('1', $this->shell->execute('echo $a'));
        $this->assertEquals('1', $this->shell->getLocals()['a']);
    }

    public function testConstant()
    {
        $this->assertEquals(
            "Use of undefined constant A - assumed 'A'\nA",
            $this->shell->execute('echo A')
        );
        $this->shell->setConstants(array('A' => 1));
        $this->assertEquals('1', $this->shell->execute('echo A'));
        $this->assertEquals('1', $this->shell->getConstants()['A']);
    }

    public function testGlobal()
    {
        $this->assertEquals(
            "Undefined index: foo",
            $this->shell->execute('echo $GLOBALS["foo"]')
        );
        $this->shell->setGlobals(array('foo' => 1));
        $this->assertEquals('1', $this->shell->execute('echo $GLOBALS["foo"]'));
        $this->assertEquals('1', $this->shell->getGlobals()['foo']);
    }

    public function testLoadFunction()
    {
        /* Undefined function raises a fatal error, which cannot be caught.
         * That's why we don't check that calling the function fails.
         */
        $this->shell->addStatement('function load_foo() {return "Fooo"; };');
        $this->assertEquals('Fooo', $this->shell->execute('echo load_foo()'));
    }

    public function testSaveFunction()
    {
        $this->assertEquals(
            '',
            $this->shell->execute('function save_foo() {return "Fooo"; }')
        );
        $this->assertEquals(
            array('function save_foo() {return "Fooo"; };'),
            $this->shell->getStatements()
        );
    }

    public function testLoadNamespaces()
    {
        $this->shell->addUseStatement('use \Phish\Phish;');
        $this->assertEquals('', $this->shell->execute('new Phish\Shell()'));
    }

    public function testSaveNamespaces()
    {
        $this->assertEquals('', $this->shell->execute('use \Foo'));
        $this->assertEquals(
            array('use \Foo;'),
            $this->shell->getUseStatements()
        );
    }
}
