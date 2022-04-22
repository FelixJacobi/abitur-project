<?php
// src/Blinkfair/init.php
namespace Blinkfair\Init;

// load composer
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Blinkfair\Variables\Globals;
use Blinkfair\Variables\Session;
use Swift_Mailer;
use Swift_SmtpTransport;
use Twig_Environment;
use Twig_Loader_Filesystem;

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

class Container
{
    const DS = DIRECTORY_SEPARATOR;
    const PARENT_DIR = self::DS . '..';
    const ROOT_DIR = __DIR__  . self::PARENT_DIR . self::PARENT_DIR . self::DS;

    /** STATIC PART */

    /**
     * service container class
     *
     * @var Container
     */
    private static $container = null;

    /**
     * Get container
     *
     * @return Container
     */
    public static function get()
    {
        if (self::$container === null) {
            self::$container = new self();
        }

        return self::$container;
    }

    /** NON-STATIC PART */

    /**
     * @var object
     */
    private $appParameter;

    /**
     * @var Switft_Mailer
     */
    private $mailer;

    /**
     * @var Twig_Environment
     */
    private $template;

    /**
     * @var Globals
     */
    private $globals;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->fireUpGlobals();
        $this->fireUpSession();
        $this->fireUpAppParameter();
        $this->fireUpMailer();
        $this->fireUpTemplate();
        $this->fireUpPDO();
    }

    /**
     * Creates PDO database connection
     */
    private function fireUpPDO()
    {
        $dsn = sprintf('%s:dbname=%s;host=%s', $this->appParameter['database']['type'], $this->appParameter['database']['name'], $this->appParameter['database']['host']);

        $this->pdo = new \PDO($dsn, $this->appParameter['database']['user'], $this->appParameter['database']['password']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Get pdo
     *
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Decodes parameters.json
     */
    private function fireUpAppParameter()
    {
        $this->appParameter = json_decode(file_get_contents(Container::ROOT_DIR . Container::DS . 'config' . Container::DS . 'parameters.json'), true);
    }

    /**
     * Get parameter
     */
    public function getAppParameter()
    {
        return $this->appParameter;
    }

    /**
     * Creates Globals instance
     */
    private function fireUpGlobals()
    {
        $this->globals = new Globals();
    }

    /**
     * Get globals
     *
     * @return Globals
     */
    public function getGlobals()
    {
        return $this->globals;
    }

    /**
     * Creates Session instance
     */
    private function fireUpSession()
    {
        $this->session = new Session($this->globals);
    }

    /**
     * Get session
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Creates and configures Swift_Mailer.
     */
    private function fireUpMailer()
    {
        $transport = new Swift_SmtpTransport($this->appParameter['mailer']['host'], $this->appParameter['mailer']['port']);

        $transport
            ->setUsername($this->appParameter['mailer']['username'])
            ->setPassword($this->appParameter['mailer']['password']);

        $this->mailer = new Swift_Mailer($transport);
    }

    /**
     * Returns configured mailer instance
     *
     * @return Swift_Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * Creates and configures Twig_Environment
     */
    private function fireUpTemplate()
    {
        $loader = new Twig_Loader_Filesystem(Container::ROOT_DIR.'templates'.Container::DS);

        if (!is_dir($this->appParameter['template']['cache'])) {
            mkdir($this->appParameter['template']['cache']);
        }

        $this->template = new Twig_Environment($loader, [
            'cache' => $this->appParameter['template']['cache'],
            'auto_reload' => true,
            'autoescape' => 'html'
        ]);
    }

    /**
     * Returns configured template instance
     *
     * @return Twig_Environment
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
