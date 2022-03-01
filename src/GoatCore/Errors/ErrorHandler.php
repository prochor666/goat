<?php
namespace GoatCore\Errors;


/**
* ErrorHandler - error handler library
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class ErrorHandler
{
    protected $backtraceBlock = "<p class=\"backtrace-bar\">
            <b>&#8675;&nbsp;Backtrace&nbsp;&#8675;</b>
        </p>";

    protected $sapiHTML = false;


    /**
    * Initialize error handler
    * @return void
    */
    public function __construct()
    {
        $this->sapiHTML = false; //PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg' ? false: true;
    }


    protected function debugPanel($message) {
        $this->notify($this->styler() . "
        <div class=\"iogate-debug-bar-header iogate-debug-bar-common iogate-debug-css-reset\">
        <p class=\"title\">&#128640; Errors</p></div>
        <div class=\"iogate-debug-bar iogate-debug-bar-common iogate-debug-css-reset\">{$message}</div>");
    }


    /**
    * PHP error handler
    * @param int $errno
    * @param string $errstr
    * @param string $errfile
    * @param int $errline
    * @param array $errcontext
    * @return void
    */
    public function handleError($error_level, $error_message, $error_file = '', $error_line = 0, $error_context = [])
    {
        if (0 === error_reporting()) {
            // This error code is not included in error_reporting
            return;
        }else{
            $message = "<p class=\"error-descriptor\">
                <b class=\"error\">Error level:</b> {$error_level}<br>
                <b class=\"error\">Message:</b> {$error_message}<br>
                <b class=\"file\">File:</b> {$error_file}&nbsp;<span class=\"line\">(line: {$error_line})</span><br>
            </p >
            {$this->backtraceBlock}
            <p class=\"backtrace-descriptor\">
                {$this->backTrace(debug_backtrace())}
            </p>
            ";
            $this->debugPanel($message);
        }
    }


    /**
    * PHP exception handler
    * @param mixed $exception
    * @return void
    */
    public function handleException($e)
    {
        $message = "<p class=\"error-descriptor\">
            <b class=\"error\">Exception:</b> {$e->getMessage()}<br>
            <b class=\"file\">File:</b> {$e->getFile()}&nbsp;<span class=\"line\">(line: {$e->getLine()})</span><br>
        </p>
        {$this->backtraceBlock}
        <p  class=\"backtrace-descriptor\">
            {$this->backTrace($e->getTrace())}
        </p>
        ";
        $this->debugPanel($message);
    }


    /**
    * PHP fatal error handler
    * @param void
    * @return void
    */
    public function handleFatalError()
    {
        if (null === $error = error_get_last()) {
            return;
        }

        $e = new \ErrorException(
            @$error['message'], 0, @$error['type'],
            @$error['file'], @$error['line']
        );

        $message = "<p class=\"error-descriptor\">
            <b class=\"error\">Exception (fatal error handler):</b> {$e->getMessage()}
            <b class=\"file\">File:</b> {$e->getFile()}&nbsp;<span class=\"line\">(line: {$e->getLine()})</span><br>
        </p>
        {$this->backtraceBlock}
        <p class=\"backtrace-descriptor\">
            {$this->backTrace($e->getTrace())}
        </p>
        ";
        $this->debugPanel($message);
    }


    /**
    * Exception/error traceback
    * @param void
    * @return string
    */
    protected function backTrace($trace = false)
    {
        $stack = '';
        $i = 1;
        $trace = $trace === false ? debug_backtrace(): $trace;
        //unset($trace[0]); //Remove call to this function from stack trace

        foreach ($trace as $node) {
            $stack .= "<b>#{$i}&nbsp;</b>";

            if (ark($node, 'file') !== false) {
                $stack .= "<span class=\"file\">File:&nbsp;</span>{$node['file']}";
            }

            if (ark($node, 'line') !== false) {
                $stack .= "&nbsp;<span class=\"line\">(line: " .$node['line'].")</span>: <br>";
            }

            if (ark($node, 'class') !== false) {
                $stack .= "<span class=\"class\">{$node['class']}-></span>";
            }

            if (ark($node, 'function') !== false) {
                $stack .= "<span class=\"function\">{$node['function']}()</span><br><br>";
            }

            $i++;
        }

        return $stack;
    }



    /**
    * Set css for browser
    * @param void
    * @return string
    */
    protected function styler()
    {
        $css = "<style type=\"text/css\">
        body {
            margin-bottom: 25vh;
        }
        .iogate-debug-css-reset {
            all: initial;
        }
        .iogate-debug-css-reset * {
            all: unset;
        }
        .iogate-debug-bar-common {
            background-color: #FFFFFF;
            color: #000000;
            font-family: monospace;
        }
        .iogate-debug-bar-header {
            position: fixed;
            bottom: 20vh;
            left: 0;
            right: 0;
            z-index: 99999;
            height: 26px;
            padding: 4px 10px;
            -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;
        }
        .iogate-debug-bar-header>.title {
            font-size: 17px;
            padding: 0;
            margin: 0;
        }
        .iogate-debug-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 99998;
            height: 20vh;
            overflow: auto;
        }
        .iogate-debug-bar>.error-descriptor,
        .iogate-debug-bar>.backtrace-bar {
            font-size: 15px;
            display: block;
            padding: 10px 10px 5px 10px;
        }
        .iogate-debug-bar>.backtrace-descriptor {
            font-size: 14px;
            display: block;
            padding: 10px 10px 5px 10px;
        }
        .iogate-debug-bar>.error-descriptor>.error {
            color: #DD0000
        }
        .iogate-debug-bar>.error-descriptor>.file,
        .iogate-debug-bar>.backtrace-descriptor>.file {
            color: #AA7700
        }
        .iogate-debug-bar>.error-descriptor>.line,
        .iogate-debug-bar>.backtrace-descriptor>.line {
            color: #009900;
        }
        .iogate-debug-bar>.backtrace-descriptor>.class {
            color: #AA00DD;
        }
        .iogate-debug-bar>.backtrace-descriptor>.function {
            color: #0000DD;
        }
        @media (prefers-color-scheme: dark) {
            .iogate-debug-bar-common {
                background-color: #232323;
                color: #EEEEEE;
            }
            .iogate-debug-bar>.error-descriptor>.error {
                color: #FF0000
            }
            .iogate-debug-bar>.error-descriptor>.file,
            .iogate-debug-bar>.backtrace-descriptor>.file {
                color: #FFCC00
            }
            .iogate-debug-bar>.error-descriptor>.line,
            .iogate-debug-bar>.backtrace-descriptor>.line {
                color: #00DD00;
            }
            .iogate-debug-bar>.backtrace-descriptor>.class {
                color: #EE00EE;
            }
            .iogate-debug-bar>.backtrace-descriptor>.function {
                color: #00AAFF;
            }
        }
        </style>
        ";

        return $this->sapiHTML ? $css: '';
    }


    /**
    * Format string for browser/CLI
    * @param string $index
    * @param string $value
    * @param string $wrapper
    * @return string
    */
    protected function format($value, $wrapper)
    {
        return $this->sapiHTML ? str_replace('%s', $value, $wrapper): $value . PHP_EOL;
    }


    protected function notify($message = "") {

        if ($this->sapiHTML) {

            die($message);
        }

        die(strip_tags(nl2br(html_entity_decode($message))));
    }
}
