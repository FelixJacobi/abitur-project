<?php
// src/Variables/Globals.php
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
class Globals
{
    /**
     * Check global type
     *
     * @param string $type
     * @throws \InvalidArgumentException
     */
    private function checkGlobalType(string $type)
    {
        if (!preg_match('(server|session|get|post|cookie)', $type)) {
            throw new \InvalidArgumentException(sprintf('Unknown global variable type %s.', $type));
        }
    }

    /**
     * Get global variables
     *
     * @return array
     */
    public function getGlobals()
    {
        return [
            'server' => &$_SERVER,
            'session' => &$_SESSION,
            'get' => &$_GET,
            'post' => &$_POST,
            'cookie' => &$_COOKIE
        ];
    }

    /**
     * Set new global value
     *
     * @param string $type
     * @param array|string $index
     * @param mixed $value
     */
    public function setGlobal(string $type, $index, $value)
    {
        if (is_string($index)) {
            $index = [$index];
        }

        $this->checkGlobalType($type);

        // we need references as we will modify the first parameter
        $destination = &$this->getGlobals()[$type];
        $finalKey = array_pop($index);

        foreach ($index as $key) {
            $destination = &$destination[$key];
        }

        $destination[$finalKey] = $value;
    }

    /**
     * Get global value
     *
     * @param string $type
     * @param array|string $index
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getGlobal(string $type, $index)
    {
        if (is_string($index)) {
            $index = [$index];
        }

        $this->checkGlobalType($type);

        // we need references as we will modify the first parameter
        $destination = &$this->getGlobals()[$type];
        $finalKey = array_pop($index);


        if (count($index) > 1) {
            foreach ($index as $key) {
                if (!isset($destination[$key])) {
                    /*throw new \InvalidArgumentException(sprintf('Global %s has no key %s. Use %s::hasGlobal to pre-check existence.',
                            $type,
                            json_encode($index),
                            get_class($this))
                    );*/

                    return null;
                }

                $destination = &$destination[$key];
            }
        }

        if (!isset($destination[$finalKey])) {
            /*throw new \InvalidArgumentException(sprintf('Global %s has no key %s. Use %s::hasGlobal to pre-check existence.',
                $type,
                json_encode($index),
                get_class($this))
            );*/
            return null;
        }

        return $destination[$finalKey];
    }

    /**
     * Check existence of global value
     *
     * @param string $type
     * @param array|string $index
     * @return boolean
     */
    public function hasGlobal(string $type, $index)
    {
        if (is_string($index)) {
            $index = [$index];
        }

        $this->checkGlobalType($type);

        // we need references as we will modify the first parameter
        $destination = &$this->getGlobals()[$type];
        $finalKey = array_pop($index);

        foreach ($index as $key) {
            if (!isset($destination[$key])) {
                return false;
            }

            $destination = &$destination[$key];
        }

        if (!isset($destination[$finalKey])) {
            return false;
        }

        return true;
    }
}