<?php
/**
 * Copyright 2007 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
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
 */
/**
 * Template for rendering the frontpage of the PHP shell.
 * TODO: get rid of the logic and use a templating system, like Smarty.
 */
session_start();

function csp()
{
    $headers = array('Content-Security-Policy',
                     'X-WebKit-CSP',
                     'X-Content-Security-Policy');
    foreach ($headers as $header) {
        header($header . ": default-src 'self'; script-src 'self' ajax.googleapis.com; style-src 'self' 'unsafe-inline' netdna.bootstrapcdn.com; font-src netdna.bootstrapcdn.com");
    }
}
csp();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title> Phish: PHP Interactive Shell </title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/static/style.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script type="text/javascript" src="/static/shell.js"></script>
  </head>

  <body>
    <div class="container">
      <h1>PHISH: PHP Interactive Shell</h1>

      <textarea id="output" rows="22" readonly="readonly">
<?php echo "$_SERVER[SERVER_SOFTWARE]\n"; ?>
PHP <?php echo phpversion(); ?>
      </textarea>

      <?php
        $salt = sprintf("%s%d", getenv("HTTP_X_APPENGINE_CITY"), mt_rand());
        $token = md5(uniqid($salt, true));
        $_SESSION['token'] = $token;
      ?>

      <form id="form" action="shell.do" method="get">
        <textarea class="prompt" id="caret" readonly="readonly" rows="4">&gt;&gt;&gt;</textarea>
        <textarea class="prompt" name="statement" id="statement" rows="4"></textarea>
        <input type="hidden" name="token" value="<?php echo $token; ?>" />
      </form>

      <p id="toolbar">
         <a href="reset.do">Reset Session</a>
       | Shift-Enter for newline
       | Ctrl-Up/Down for history
      </p>
    </div>
  </body>
</html>
