Phish Shell
===========

An interactive shell for PHP.

Description
-----------

This shell is a fork of the original code I wrote while working at the Appengine team (See http://php-minishell.appspot.com). That version, was conceived as a quick hack to test the new runtime. However, I wanted to have some new features and learn some more recent stuff about PHP.

New Features/Changes
--------------------

* PSR-4 Compatible
* Syntax highlighting using [Code Mirror](http://codemirror.net/).
* CSRF token generated using [symfony/security-csrf](https://github.com/symfony/security-csrf).
* appengine-router.php script that allows to run the app in the standalone php development server.
* Removed all appengine dependencies.
* Refactored Shell class, and included the tests.
* Simplified a lot the JS by using Jquery.

Future
------

This project still has some very rough edges, specially in the JS part. If you find some issues or have some ideas please open a ticket.
