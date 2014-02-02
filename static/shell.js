// Copyright 2007 Google Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

/**
 * @fileoverview
 * Javascript code for the interactive AJAX shell.
 *
 * Includes a function (shell.runStatement) that sends the current php
 * statement in the shell prompt text box to the server, and a callback
 * (shell.done) that displays the results when the XmlHttpRequest returns.
 *
 */

/**
 * Shell namespace.
 * @type {Object}
 */
var shell = {};

/**
 * The shell history. history is an array of strings, ordered oldest to
 * newest. historyCursor is the current history element that the user is on.
 *
 * The last history element is the statement that the user is currently
 * typing. When a statement is run, it's frozen in the history, a new history
 * element is added to the end of the array for the new statement, and
 * historyCursor is updated to point to the new element.
 *
 * @type {Array}
 */
shell.history = [''];

/**
 * See {shell.history}
 * @type {number}
 */
shell.historyCursor = 0;
shell.statements = 0;

shell.historyUp = function() {
  if (this.historyCursor > 0) {
    stmt_editor.setValue(this.history[--this.historyCursor]);
  }
}

shell.historyDown = function() {
  if (this.historyCursor < this.history.length - 1) {
    stmt_editor.setValue(this.history[++this.historyCursor]);
  }
}

shell.execute = function() {
  this.history[this.history.length - 1] = stmt_editor.getValue();
  this.runStatement();
}

/**
 * The Ajax success callback. It adds the command and its resulting output to
 * the shell history div.
 *
 * @this {shell}
 * @param {Object} data the response.
 * @param {String} textStatus the response status.
 * @param {XmlHttpRequest} jqXHR the XmlHttpRequest used.
 */
shell.done = function(data, textStatus, jqXHR) {
  var statement = $('#statement');
  statement.removeClass('processing');

  // add the command to the shell output
  var output = document.getElementById('output');

  var value = stmt_editor.getValue().trim();
  var last_char = value[value.length - 1];
  if (last_char != ';' && last_char != '}') {
    value += ';';
  }
  this.statements += 1;
  output.value += '\n\nIn [' + this.statements + ']: ' + value;
  stmt_editor.setValue('');

  // add a new history element
  this.history.push('');
  this.historyCursor = this.history.length - 1;

  // add the command's result
  var result = data.trim();
  if (result !== '') {
    output.value += '\nOut[' + this.statements + ']: ' + result;
  }
  output_editor.setValue(output.value);
  output_editor.scrollTo(0, output_editor.lastLine()*15 + 1000);
};

/**
 * This is the form's onsubmit handler. It sends the php statement to the
 * server, and registers shell.done() as the callback to run when it returns.
 *
 * @this {shell}
 * @return {Boolean} false to tell the browser not to submit the form.
 */
shell.runStatement = function() {
  var form = $('#form');
  $('#statement').addClass('processing');
  $.ajax({
    type: form.attr('method').toUpperCase(),
    url: form.attr('action'),
    success: this.done,
    context: this,
    data: {
      statement: stmt_editor.getValue(),
      token: $('#token').val()
    }
  });
};

$(document).ready(function() {
  $('#statement').focus();
  $('#caret').bind('focus', function() {
    $('#statement').focus();
  });
  $('#statement').bind('keydown', function() {
    shell.onPromptKeyDown(event);
  });
});
