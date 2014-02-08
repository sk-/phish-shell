<?php
/**
 * Provides class \Phish\Phish\Shell.
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
 * @category  Code
 * @package   Phish
 * @author    Sebastian Kreft
 * @copyright 2014 Sebastian Kreft
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link      http://github.com/sk-/phish-shell
 */

namespace Phish\Phish;

/**
 * Allows to save the execution state of a serie of statements, so to implement
 * an online shell.
 *
 * Currently it saves the following:
 *   - Locals: variables defined in the shell context will be added to the
 *       locals.
 *   - Constants: defined constants will be persisted.
 *   - Globals: variables written to $GLOBALS will be persisted as well.
 *       Note that using global is not supported by the underlying eval method.
 *   - Unserializable statements: functions and class definitions, requires and
 *       includes. These statements will be reexecuted everytime before the new
 *       statement. These are executed in the global context.
 *   - Use statements: any use statement, will be reexecuted in the context of
 *       the statement evaluation.
 *
 * Note that we group some of the results, meaning that reexecuting a statement
 * may fail.
 *
 * This class is serializable, so it can be saved in a Session.
 */
class Shell
{
    private $_locals = '';
    private $_constants = '';
    private $_globals = '';
    private $_statements = array();
    private $_useStatements = array();

    /**
     * Creates a new shell.
     *
     * @return Shell a new Shell object.
     */
    public function __construct()
    {
        $this->_locals = serialize(array());
        $this->_constants = serialize(array());
        $this->_globals = serialize(array());
        $this->_statements = array();
        $this->_useStatements = array();
    }

    /**
     * Stores the given local variables.
     *
     * @param array $locals the locals to set.
     *
     * @return void
     */
    public function setLocals($locals)
    {
        $this->_locals = serialize($locals);
    }

    /**
     * Returns an array with the user defined locals.
     *
     * @return array An array with the local variables in scope.
     */
    public function getLocals()
    {
        return unserialize($this->_locals);
    }

    /**
     * Stores the given constants.
     *
     * @param array $constants the constants to set.
     *
     * @return void
     */
    public function setConstants($constants)
    {
        $this->_constants = serialize($constants);
    }

    /**
     * Returns an array with the defined constants.
     *
     * @return array An array with the defined constants.
     */
    public function getConstants()
    {
        return unserialize($this->_constants);
    }

    /**
     * Load the constants that are not already defined.
     *
     * @return void
     */
    private function _loadConstants()
    {
        $constants = unserialize($this->_constants);
        foreach (array_diff_key($constants, get_defined_constants()) as
                $constant => $value) {
            define($constant, $value);
        }
    }

    /**
     * Stores the given globals.
     *
     * @param array $globals the globals to set.
     *
     * @return void
     */
    public function setGlobals($globals)
    {
        $this->_globals = serialize($globals);
    }

    /**
     * Returns an array with the defined globals.
     *
     * @return array An array with the defined globals.
     */
    public function getGlobals()
    {
        return unserialize($this->_globals);
    }

    /**
     * Load the globals, overwriting existing values and not modifying those
     * that are not defined in our scope.
     *
     * @return void
     */
    private function _loadGlobals()
    {
        $GLOBALS = array_merge($GLOBALS, $this->getGlobals());
    }

    /**
     * Adds a statement to the list of statements that need to be executed every
     * time.
     *
     * @param string $statement the statement to add.
     *
     * @return void
     */
    public function addStatement($statement)
    {
        $this->_statements[] = $statement;
    }

    /**
     * Depending on the statement it will save it to the list of statements
     * with side effects, to the list of use statements, or do nothing.
     *
     * @param string $statement The statement to check if it needs to be saved.
     *
     * @return void
     */
    private function _saveStatementIfNeeded($statement)
    {
        $nonSerializableTokens = array(T_CLASS, T_FUNCTION,
                                       T_REQUIRE, T_REQUIRE_ONCE,
                                       T_INCLUDE, T_INCLUDE_ONCE);
        foreach (token_get_all('<?php ' . $statement . ' ?>') as $token) {
            if (in_array($token[0], $nonSerializableTokens)) {
                $this->addStatement($statement);
            } elseif ($token[0] == T_USE) {
                $this->addUseStatement($statement);
            }
        }
    }

    /**
     * Returns an array with all the statements with side effect.
     * Those include function and class declarations, and require/includes.
     *
     * @return array An array with the saved statements.
     */
    public function getStatements()
    {
        return $this->_statements;
    }

    /**
     * Execute all the statements with side effects, once more.
     * Function and class declarations, requires and includes needs to be
     * executed every time.
     *
     * @return void
     */
    private function _loadStatements()
    {
        ob_start();
        foreach ($this->_statements as $statement) {
            eval($statement);
        }
        ob_end_clean();
    }

    /**
     * Adds a use statement to the list of use statements that need to be
     * executed before executing the actual statement, in the same context.
     *
     * @param string $statement the statement to add.
     *
     * @return void
     */
    public function addUseStatement($statement)
    {
        $this->_useStatements[] = $statement;
    }

    /**
     * Returns an array with all the use statements.
     *
     * @return array An array with the saved use statements.
     */
    public function getUseStatements()
    {
        return $this->_useStatements;
    }

    /**
     * Executes one or more php statements, in the context of this shell.
     *
     * Note: all local variables (including parameters) in this function, must
     * be prepended with '_shell' so to minimize collisions with user defined
     * variables.
     *
     * @param string $_shell_statement The statement(s) to execute.
     *
     * @return string The output of the executed statement.
     */
    public function execute($_shell_statement)
    {
        $_shell_statement = implode('', $this->getUseStatements()). $_shell_statement . ';';

        //error_reporting(0);
        //register_shutdown_function('\Phish\Phish\Shell::shutdown_handler');
        set_error_handler('\Phish\Phish\Shell::errorHandler');

        ob_start();
        // Populate the context with the saved locals. All collisions will be
        // ignored, beacuse of the EXTR_SKIP argument.
        extract($this->getLocals(), EXTR_SKIP);
        $this->_loadStatements();
        $this->_loadConstants();
        $this->_loadGlobals();
        $_shell_success = eval($_shell_statement);
        // Get the local variables and store them in the shell.
        $this->setLocals(get_defined_vars());
        $this->setConstants(get_defined_constants());
        $this->setGlobals($GLOBALS);
        $this->_saveStatementIfNeeded($_shell_statement);

        if ($_shell_success === false) {
            $_shell_response = $this->getFatalError();
        } else {
            $_shell_response = trim(ob_get_contents());
        }
        ob_end_clean();

        return $_shell_response;
    }

    /**
     * Returns the last Parse/Fatal error.
     *
     * @return string The error that was last generated
     */
    public static function getFatalError()
    {
        $error = error_get_last();
        if ($error !== null) {
            return $error['message'];
        }
    }

    /**
     * Handler to catch exceptions raised when evaluating the code.
     * We just print the error and not the line nor file, as they are not
     * meaningful in this context.
     *
     * @param int    $errno   Code describing the error
     * @param string $errstr  The error
     * @param string $errfile File where the error was generated
     * @param int    $errline Line where the error was generated
     *
     * @return void It does not reutrn as there's no one to get the result,
     *   however, it does print the error, so it can be caught by an output
     *   buffer.
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        echo $errstr, "\n";
    }
}
