<?php
// src/Variables/Session.php
namespace Blinkfair\Variables;

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
class Session
{
    /**
     * @var Globals
     */
    private $globals;

    /**
     * The constructor.
     *
     * @param Globals $globals
     */
    public function __construct(Globals $globals)
    {
        session_start();
        $this->globals = $globals;

        // generate form token key once
        if (!$this->hasKey('form_token')) {
            $this->setKey('form_token', bin2hex(random_bytes(22)));
        }
    }

    /**
     * Set new session key
     *
     * @param string|array $index
     * @param mixed value
     */
    public function setKey($index, $value)
    {
        $this->globals->setGlobal('session', $index, $value);
    }

    /**
     * Get session key
     *
     * @param string|array $index
     * @return mixed
     */
    public function getKey($index)
    {
        return $this->globals->getGlobal('session', $index);
    }

    /**
     * Checks existence of session key
     *
     * @param string|array $index
     * @return boolean
     */
    public function hasKey($index)
    {
        return $this->globals->hasGlobal('session', $index);
    }

    /**
     * Get form security token
     *
     * @return string
     */
    public function getFormToken()
    {
        return $this->getKey('form_token');
    }
}