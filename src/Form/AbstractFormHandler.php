<?php
// AbstractFormHandler.php
namespace Blinkfair\Form;

use Exception;
use Blinkfair\Init\Container;

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
abstract class AbstractFormHandler
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Checks form token.
     *
     * @throws Exception
     */
    protected function checkFormToken()
    {
        if (!$this->container->getGlobals()->hasGlobal('post', '_form_token')) {
            throw new Exception('Form token is missing!');
        } else if ($this->container->getGlobals()->getGlobal('post', '_form_token') !== $this->container->getSession()->getFormToken()) {
            throw new Exception('Invalid form token!');
        }
    }

    /**
     * The constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Check existence of post variable.
     *
     * @param $index
     * @return bool
     */
    protected function checkPostVariable($index)
    {
        return $this->container->getGlobals()->hasGlobal('post', $index) &&
            !empty($this->container->getGlobals()->getGlobal('post', $index));
    }

    /**
     * Handle form request
     *
     * @return array
     */
    abstract public function handle();
}