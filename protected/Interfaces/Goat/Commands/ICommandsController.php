<?php
namespace Goat\Commands;

interface ICommandsController {

    public function setup();

    public function getData();
}