$(document).ready(function() {
    output_editor = CodeMirror.fromTextArea($("#output")[0], {
        matchBrackets: true,
        mode: {"name": "php", "startOpen": true},
        readOnly: "nocursor",
        indentUnit: 4,
        indentWithTabs: true,
        lineNumbers: false,
        enterMode: "keep",
        tabMode: "shift",
        theme: "monokai"
    });
    stmt_editor = CodeMirror.fromTextArea($("#statement")[0], {
        matchBrackets: true,
        mode: {"name": "php", "startOpen": true},
        indentUnit: 4,
        indentWithTabs: true,
        lineNumbers: false,
        enterMode: "keep",
        tabMode: "shift",
        theme: "monokai",
        extraKeys: {
            'Enter': function() { shell.execute()},
            'Ctrl-Up': function() { shell.historyUp()},
            'Ctrl-Down': function() { shell.historyDown()}
        }
    });
});
