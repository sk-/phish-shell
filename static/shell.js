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
 TODO(skreft): add codesite url.
 * Part of ....
 *
 * Includes a function (shell.runStatement) that sends the current php
 * statement in the shell prompt text box to the server, and a callback
 * (shell.done) that displays the results when the XmlHttpRequest returns.
 *
 * Also includes cross-browser code (shell.getXmlHttpRequest) to get an
 * XmlHttpRequest.
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


/**
 * This is the prompt textarea's onkeydown handler. Depending on the key that
 * was pressed, it will run the statement, navigate the history, or update the
 * current statement in the history.
 *
 * @this {shell}
 * @param {Event} event the keypress event.
 * @return {Boolean} false to tell the browser not to submit the form.
 */
shell.onPromptKeyDown = function(event) {
  var statement = document.getElementById('statement');

  if (this.historyCursor == this.history.length - 1) {
    // we're on the current statement. update it in the history before doing
    // anything.
    this.history[this.historyCursor] = statement.value;
  }

  // should we pull something from the history?
  if (event.ctrlKey && event.keyCode == 38 /* up arrow */) {
    if (this.historyCursor > 0) {
      statement.value = this.history[--this.historyCursor];
    }
    event.preventDefault();
  } else if (event.ctrlKey && event.keyCode == 40 /* down arrow */) {
    if (this.historyCursor < this.history.length - 1) {
      statement.value = this.history[++this.historyCursor];
    }
    event.preventDefault();
  } else if (!event.altKey) {
    // probably changing the statement. update it in the history.
    this.historyCursor = this.history.length - 1;
    this.history[this.historyCursor] = statement.value;
  }

  // should we submit?
  if (event.keyCode == 13 /* enter */ && !event.altKey && !event.shiftKey) {
    event.preventDefault();
    this.runStatement();
  }
};

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

  var value = statement.val().trim();
  var last_char = value[value.length - 1];
  if (last_char != ';' && last_char != '}') {
    value += ';';
  }
  output.value += '\n>>> ' + value;
  statement.val('');

  // add a new history element
  this.history.push('');
  this.historyCursor = this.history.length - 1;

  // add the command's result
  var result = data.trim();
  if (result !== '')
    output.value += '\n' + result;

  // scroll to the bottom
  output.scrollTop = output.scrollHeight;
  if (output.createTextRange) {
    var range = output.createTextRange();
    range.collapse(false);
    range.select();
  }
};

/**
 * This is the form's onsubmit handler. It sends the php statement to the
 * server, and registers shell.done() as the callback to run when it returns.
 *
 * @this {shell}
 * @return {Boolean} false to tell the browser not to submit the form.
 */
shell.runStatement = function() {
  var form = document.getElementById('form');

  var data = {};
  for (i = 0; i < form.elements.length; i++) {
    var elem = form.elements[i];
    if (elem.id != 'caret') {
      data[elem.name] = elem.value;
    }
  }

  // send the request and tell the user.
  $('#statement').addClass('processing');
  $.ajax({
    type: form.method.toUpperCase(),
    url: form.action,
    success: this.done,
    context: this,
    data: data
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
