<?php
namespace Goat\Api;

interface IApiController {

    public function setup();

    public function getHeaders();

    public function getData();
}