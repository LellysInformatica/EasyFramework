<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Controller;

use Easy\Core\Object;

/**
 * Base class for an individual Component.  Components provide reusable bits of
 * controller logic that can be composed into a controller.  Components also
 * provide request life-cycle callbacks for injecting logic at specific points.
 *
 * ## Life cycle callbacks
 *
 * Components can provide several callbacks that are fired at various stages of the request
 * cycle.  The available callbacks are:
 *
 * - `initialize()` - Fired before the controller's beforeFilter method.
 * - `startup()` - Fired after the controller's beforeFilter method.
 * - `beforeRender()` - Fired before the view + layout are rendered.
 * - `shutdown()` - Fired after the action is complete and the view has been rendered
 *    but before Controller::afterFilter().
 * - `beforeRedirect()` - Fired before a redirect() is done.
 *
 * @package       Easy.Controller
 * @see Controller::$components
 */
class Component extends Object
{

    /**
     * The controller object
     * @var Controller 
     */
    protected $controller;

    /**
     * Collection of components
     * @var ComponentCollection 
     */
    protected $Components = null;

    /**
     * Settings for this Component
     *
     * @var array
     */
    public $settings = array();

    public function __construct(ComponentCollection $components, $settings = array())
    {
        $this->Components = $components;
        $this->settings = $settings;
        $this->_set($settings);
    }

    /**
     * Allows setting of multiple properties of the object in a single line of code.  Will only set
     * properties that are part of a class declaration.
     *
     * @param array $properties An associative array containing properties and corresponding values.
     * @return void
     */
    protected function _set($properties = array())
    {
        if (is_array($properties) && !empty($properties)) {
            $vars = get_object_vars($this);
            foreach ($properties as $key => $val) {
                if (array_key_exists($key, $vars)) {
                    $this->{$key} = $val;
                }
            }
        }
    }

    /**
     * Called before the Controller::beforeFilter().
     *
     * @param Controller $controller Controller with components to initialize
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::initialize
     */
    public function initialize(Controller $controller)
    {
        
    }

    /**
     * Called after the Controller::beforeFilter() and before the controller action
     *
     * @param Controller $controller Controller with components to startup
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::startup
     */
    public function startup(Controller $controller)
    {
        
    }

    /**
     * Called before the Controller::beforeRender(), and before 
     * the view class is loaded, and before Controller::render()
     *
     * @param Controller $controller Controller with components to beforeRender
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::beforeRender
     */
    public function beforeRender(Controller $controller)
    {
        
    }

    /**
     * Called after Controller::render() and before the output is printed to the browser.
     *
     * @param Controller $controller Controller with components to shutdown
     * @return void
     * @link @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::shutdown
     */
    public function shutdown(Controller $controller)
    {
        
    }

    /**
     * Called before Controller::redirect().  Allows you to replace the url that will
     * be redirected to with a new url. The return of this method can either be an array or a string.
     *
     * If the return is an array and contains a 'url' key.  You may also supply the following:
     *
     * - `status` The status code for the redirect
     * - `exit` Whether or not the redirect should exit.
     *
     * If your response is a string or an array that does not contain a 'url' key it will
     * be used as the new url to redirect to.
     *
     * @param Controller $controller Controller with components to beforeRedirect
     * @param string|array $url Either the string or url array that is being redirected to.
     * @param integer $status The status code of the redirect
     * @param boolean $exit Will the script exit.
     * @return array|null Either an array or null.
     * @link @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::beforeRedirect
     */
    public function beforeRedirect(Controller $controller, $url, $status = null, $exit = true)
    {
        
    }

}