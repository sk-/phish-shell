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
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title> Phish: PHP Interactive Shell </title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/static/style.css">
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
        <textarea class="prompt" id="caret" readonly="readonly" rows="4"
                  onfocus="document.getElementById('statement').focus()"
                  >&gt;&gt;&gt;</textarea>
        <textarea class="prompt" name="statement" id="statement" rows="4"
                  onkeydown="return shell.onPromptKeyDown(event);"></textarea>
        <input type="hidden" name="token" value="<?php echo $token; ?>" />
        <input type="submit" style="display: none" />
      </form>

      <p id="ajax-status"></p>

      <p id="toolbar">
         <a href="reset.do">Reset Session</a>
       | Shift-Enter for newline
       | Ctrl-Up/Down for history
      </p>
    </div>

    <script type="text/javascript">
      document.getElementById('statement').focus();
    </script>
  </body>
</html>
