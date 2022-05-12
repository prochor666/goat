<?php
namespace Goat\Tests;


class ColorTest
{
    use \GoatCore\Traits\Validator;

    public function run($color)
    {
        return $this->color($color);
    }
}


$colors = [
    '#A12C',
    '#FF00AA22',
    '#FF00AA',
    '#DF0',
    '#aaa000',
    '#fh0',
    '#123',
    'rgb(11, 20, 10)',
    'RGB(0,0,255)',
    'rgb(0,0,256)',
    'rgb(a,d,c)',
    'RGBA(101, 20, 150, 0.5)',
    'rgba(1,1,1, 2)',
    'RGBA(99, 189, 12, 0.67)',
    'RGBA(10%,20%,150%, 2.5)',
    'hsl(235, 100%, 20%)',
    'HSL(235, 100%, .5)',
    'hsl(220, 500, 20%)',
    'hsl(13%, 100%, 20%)',
    'hsla(235, 100%, 13, .05)',
    'hsla(235, 100%, 20%, .15)',
    'hsla(112, 100%, 90%, 2)',
    'hsla(235, 100%, 20%, 100%)',
    'adsasd',
];


$test = new ColorTest;

foreach ($colors as $color) {

    echo $test->run($color) ? "{$color} is valid color \n": "{$color} is not valid color \n";
}