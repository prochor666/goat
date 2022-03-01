<?php
namespace Goat;

class Session
{
    protected $session;

    public function start(): void
    {
        $authHeader = $this->requestHeader('Authorization');

        if ($authHeader !== false) {

            session_id($authHeader);
        }

        session_start();
    }


    protected function requestHeader($header)
    {
        return ark($this->allHeaders(), $header, false);
    }


    protected function allHeaders(): array
    {
        return apache_request_headers();
    }
}
