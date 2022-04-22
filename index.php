<?php
// index.php
require_once 'src/Init/Container.php';

use Blinkfair\Init\Container;
use Blinkfair\Form\OrderHandler;

/*
 * The MIT License
 *
 * Copyright 2017 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */

$container = Container::get();

/**
 * Check if post variable exists and is not empty.
 *
 * @param $index
 * @return boolean
 */
if (!$container->getGlobals()->hasGlobal('server', 'PATH_INFO'))
{
    throw new \RuntimeException('PATH_INFO not given');
}
$paths = explode('/', Container::get()->getGlobals()->getGlobal('server', 'PATH_INFO'));

if (empty($paths[0])) {
    array_shift($paths);
}

if (count($paths) === 1 && $paths[0] === 'order') {
    echo $container->getTemplate()->render('order.html.twig', ['container' => Container::get()]);
} else if (count($paths) === 2 && $paths[0] === 'order' && $paths[1] === 'submit') {
    header('Content-Type: application/json');

    $handler = new OrderHandler($container);
    $result = $handler->handle();

    if (isset($result['error_code'])) {
        http_response_code($result['error_code']);
        unset($result['error_code']);
    }

    echo json_encode($result);
} else {
    http_response_code(404);
    echo '<h1>Error 404/Not found</h1>';
}