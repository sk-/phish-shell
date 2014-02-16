<?php
/**
 * Router for the Dev Server that handles Appengine app.yaml files.
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
 * @category  Web
 * @package   Phish
 * @author    Sebastian Kreft
 * @copyright 2014 Sebastian Kreft
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link      http://github.com/sk-/phish-shell
 */
require 'vendor/autoload.php';

use \Phish\Utils\AppengineRouter;

$config = yaml_parse_file('app.yaml');
$location = $_SERVER['SCRIPT_NAME'];

AppengineRouter::route($config['handlers'], $location);
