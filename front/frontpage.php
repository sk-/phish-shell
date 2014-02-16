<?php
/**
 * Handles the homepage (/) url of the Shell.
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
 * @author    Google AppEngine
 * @copyright 2014 Sebastian Kreft
 * @copyright 2007 Google Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link      http://github.com/sk-/phish-shell
 */

require 'vendor/autoload.php';
use Symfony\Component\Security\Csrf\CsrfTokenManager;

session_start();

// Security Headers
$csp_rules = "default-src 'self'; script-src 'self' ajax.googleapis.com; style-src 'self' 'unsafe-inline' netdna.bootstrapcdn.com; font-src netdna.bootstrapcdn.com";
header('Content-Security-Policy: '. $csp_rules);
header('X-Content-Security-Policy: '. $csp_rules);
header('X-WebKit-CSP: '. $csp_rules);

header('X-Frame-Options: DENY');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title> Phish: PHP Interactive Shell </title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/static/style.css">
    <link rel="stylesheet" href="/static/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="/static/codemirror/theme/monokai.css">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script type="text/javascript" src="/static/shell.js"></script>

    <script src="/static/codemirror/lib/codemirror.js"></script>
    <script src="/static/codemirror/addon/edit/matchbrackets.js"></script>
    <script src="/static/codemirror/mode/clike/clike.js"></script>
    <script src="/static/codemirror/mode/php/php.js"></script>
    <script type="text/javascript" src="/static/codemirror.js"></script>
  </head>

  <body>
    <div class="container">
      <h1>PHISH: PHP Interactive Shell</h1>
      <div id="shell">
        <textarea id="output" rows="22" readonly="readonly">
<?php echo $_SERVER[SERVER_SOFTWARE]; ?>

PHP <?php echo phpversion(); ?></textarea>
        <form id="form" action="shell.do" method="post">
          <textarea class="prompt" name="statement" id="statement" rows="4"></textarea>
          <input type="hidden" name="token" id="token" value="<?php echo (new CsrfTokenManager())->refreshToken('shell'); ?>" />
        </form>

        <p id="toolbar">
           <a href="reset.do">Reset Session</a>
         | Shift-Enter for newline
         | Ctrl-Up/Down for history
         | <a href="https://github.com/sk-/phish-shell">Star us on Github <img src="/static/github.png" alt="GitHub logo" height="16" width="16" /></a>
        </p>
      </div>
    </div>
  </body>
</html>
