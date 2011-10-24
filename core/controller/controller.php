<?php

App::import("Core", array("controller/component"));

/**
 * Controllers are the core of a web request. They provide actions that
 * will be executed and (generally) render a view that will be sent
  back to the user.

  An action is just a public method on your controller. They're available
  automatically to the user throgh the <Mapper>. Any protected or private
  method will NOT be accessible to requests.

  By default, only your <AppController> will inherit Controller directly.
  All other controllers will inherit AppController, that can contain
  specific rules such as filtering and access control.

  A typical controller will look something like this

  (start code)
  class ArticlesController extends AppController {
  public function index() {
  $this->articles = $this->Articles->all();
  }

  public function view($id = null) {
  $this->article = $this->Articles->firstById($id);
  }
  }
  (end)

  By default, all actions render a view in app/views. A call to the
  index action in the ArticlesController, for example, will render
  the view app/views/articles/index.htm.php.

  All controllers also can load models for you. By default, the
  controller loads the model with the same. Be aware that, if the
  model does not exist, the controller will throw an exception.
  If you don't want the controller to load models, or if you want
  to specific models, use <Controller::$uses>.

  @package easy.controller
 *
 * @todo Remove all current non-common dependencies. Controller should
 * be model and view agnostic.
 */
abstract class Controller extends Hookable {

    /**
      Defines which models the controller will load. When null, the
      controller will load only the model with the same name of the
      controller. When an empty array, the controller won't load any
      model.

      You can load as many models as you want, but be aware that this
      can decrease your application's performance. So the rule is to
      include here only models you need in all (or almost all)
      actions, and manually load less used models.

      Be aware that, when we start using autoload, this feature will
      be removed, so don't rely on this.

      @see loadModel(), Model::load
     */
    public $uses = null;

    /**
     *  Componentes a serem carregados no controller.
     */
    public $components = array();

    /**
      Defines the name of the controller. Shouldn't be used directly.
      It is used just for loading a default model if none is provided
      and will be removed in the near future.
     */
    protected $name = null;

    /**
      Contains $_POST and $_FILES data, merged into a single array.
      This is what you should use when getting data from the user.
      A common pattern is checking if there is data in this variable
      like this

     * Exemplo:
      <code>
      if(!empty($this->data)) {
      new Articles($this->data)->save();
      }
      </code>
     */
    public $data = array();

    /**
      Data to be sent to views. Should not be used directly. Use the
      appropriate methods for this.

      @see
      Controller::__get, Controller::__set, Controller::get,
      Controller::set
     */
    protected $view;

    /**
      Specifies if the controller should render output automatically.
      Usually this will be true, but if you want to generate custom
      output you can set this to false.
     */
    protected $autoRender = true;

    /**
      Layout used for rendering the current view. By default, 'default'
      layout will be rendered. If you don't want a layout rendered
      with your view, set this to false.
     */
    protected $layout = null;

    /**
      beforeFilters are methods run before a controller action. They
      may stop a action from running, for example when a user does not
      have permission to access certain actions.

      <code>
      protected $beforeFilter = array('requireLogin');

      protected function requireLogin() {
      if(!$this->loggedIn()) {
      // Controller::redirect stops the action from running
      // and redirects the user to a login page
      $this->redirect('/users/login');
      }
      }
      </code>
     */
    protected $beforeFilter = array();

    /**
      beforeRenders are methods run after a controller action has been
      executed, but before they render any output. You can use it to
      suppress output for some reason.
     */
    protected $beforeRender = array();

    /**
      afterFilters are methods run after the controller executed an
      action and sent output to the browser.
     */
    protected $afterFilter = array();

    /**
      Keeps the models attached to the controller. Shouldn't be used
      directly. Use the appropriate methods for this. This will be
      removed when we start using autoload.

      @see
      Controller::__get, Controller::loadModel, Model::load
     */
    protected $models = array();

    /**
      Keeps the components attached to the controller. Shouldn't be used
      directly. Use the appropriate methods for this. This will be
      removed when we start using autoload.

      @see
      Controller::__get, Controller::loadComponent, Model::load
     */
    protected $loadedComponents = array();

    function __construct() {
        if (is_null($this->name)) {
            $this->name = $this->name();
        }

        if (is_null($this->uses)) {
            if ($this->name === 'App') {
                $this->uses = array();
            } else {
                $this->uses = array($this->name);
            }
        }

        array_map(array($this, 'loadModel'), $this->uses);
        array_map(array($this, 'loadComponent'), $this->components);
        $this->view = new View();
        $this->data = array_merge_recursive($_POST, $_FILES);
    }

    /**
      Magic method to set values to be sent to the view. It enables
      you to send values by setting instance variables in the
      controller

      <code>
      public function index() {
      $this->articles = $this->Articles->all();
      // will be available to the view as $articles
      }
      </code>

      @param $name Name of the variable to be sent to the view.
      @param $value Value to be sent to the view.
     */
    public function __set($name, $value) {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->view->set($name, $value);
            }
        } else {
            $this->view->set($name, $value);
        }
    }

    /**
      Magic methods enables you to get values by using instance variables in the controller

      This method also allows you to use the $this->Model syntax, but
      this will be removed in the future.

      <code>
      public function edit($id = null) {
      $this->user = $this->Users->firstById($id);

      if(!empty($this->data)) {
      $this->user->updateAttributes($this->data);
      $this->user->save();
      }
      }
      </code>

      @param $name Name of the value to be read.

      @return The value sent to the view, or the model instance.

      Throws:
      - Runtime exception if the attribute does not exist.
     */
    public function __get($name) {
        $attrs = array('models', 'loadedComponents');

        foreach ($attrs as $attr) {
            if (array_key_exists($name, $this->{$attr})) {
                return $this->{$attr}[$name];
            }
        }

        //throw new RuntimeException(get_class($this) . '->' . $name . ' does not exist.');
    }

    /**
      Loads a model and attaches it to the controller. It is not
      considered a good practice to include all models you will ever
      need in <Controller::$uses>. If you need models that are not
      used throughout your controller, you can load them using this
      method.

      Be aware, though, that is generally better to use <Model::load>
      itself if you don't need to use the instance more than once in
      your action, because it does not have the overhead to attach
      the model to the controller. Also, this method will be removed
      in the next versions in favor of autloading, so don't rely on
      this.

      @param $model - camel-cased name of the model to be loaded.

      @return The model's instance.
     */
    protected function loadModel($model) {
        return $this->models[$model] = Model::load($model);
    }

    /**
      Display a view template

      @param $view  - o nome do template a ser exibido
      @param $ext  - a extenção do arquivo a ser exibido. O padrão é '.tpl'

      @return The view's instance
     */
    function display($view, $ext = ".tpl") {
        $this->view->setLayout($this->layout);
        $this->view->setAutoRender($this->autoRender);
        return $this->view->display($view);
    }

    /**
      Sets a value to be sent to the view. It is not commonly used
      anymore, and was abandoned in favor of <Controller::__set>,
      which is much more convenient and readable. Use this only if
      you need extra performance.

      @param $name - name of the variable to be sent to the view. Can
      also be an array where the keys are the name of the
      variables. In this case, $value will be ignored.
      @param $value - value to be sent to the view.
     */
    function set($var, $value = null) {
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $this->view->set($key, $value);
            }
        } else {
            $this->view->set($var, $value);
        }
    }

    public function getAutoRender() {
        return $this->autoRender;
    }

    public function setAutoRender($autoRender) {
        $this->autoRender = $autoRender;
    }

    public function getLayout() {
        return $this->layout;
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }

    public function name() {
        $classname = get_class($this);
        $lenght = strpos($classname, 'Controller');

        return substr($classname, 0, $lenght);
    }

    public static function hasViewForAction($request) {
        return Filesystem::exists('app/views/' . $request['controller'] . '/' . $request['action'] . '.tpl');
    }

    public function callAction($request) {
        if ($this->hasAction($request['action']) || self::hasViewForAction($request)) {
            return $this->dispatch($request);
        } else {
            throw new MissingActionException($request);
        }
    }

    public function hasAction($action) {
        $class = new ReflectionClass(get_class($this));
        if ($class->hasMethod($action)) {
            $method = $class->getMethod($action);
            return $method->class != 'Controller' && $method->isPublic();
        } else {
            return false;
        }
    }

    protected function dispatch($request) {
        //Chamamos o evento initialize dos componentes
        $this->componentEvent("initialize");
        //Chamamos o evento beforeFilter dos controllers
        $this->fireAction('beforeFilter');
        //Chamamos o evento startup dos componentes
        $this->componentEvent("startup");
        if ($this->hasAction($request['action'])) {
            call_user_func_array(array($this, $request['action']), $request['params']);
        }
        //Se o autorender está habilitado
        if ($this->autoRender) {
            //Mostramos a view
            $this->fireAction('beforeRender');
            $this->display("{$request["controller"]}/{$request["action"]}");
        }
        //Chamamos o evento shutdown dos componentes
        $this->componentEvent("shutdown");
        //Chamamos o evento afterFilter dos controllers
        $this->fireAction('afterFilter');
    }

    /**
      Re-route the controller to execute another action. Note that it
      does NOT stop the current action, so every statement after the
      call to setAction will still be executed.

      @param $action - new action to be executed.
     */
    public function setAction($action) {
        $args = func_get_args();
        return call_user_func_array(array($this, $action), $args);
    }

    /**
      Loads a controller. Typically used by the Dispatcher.

      @param $name Class name of the controller to be loaded.
      @param $instance True to return an instance of the controller, false if you just want the class loaded.

      @return
      If $instance == false, returns true if the controller was
      loaded. If $instance == true, returns an instance of the
      controller.

      Throws:
      - MissingControllerException if the controller can't be
      found.

      @todo Replace by auto-loading.
     */
    public static function load($name, $instance = false) {
        if (!class_exists($name) && App::path("Controller", Inflector::underscore($name))) {
            App::import("Controller", Inflector::underscore($name));
        }

        if (class_exists($name)) {
            if ($instance) {
                return $controller = & ClassRegistry::load($name, "Controller");
            } else {
                return true;
            }
        } else {
            throw new MissingControllerException(array("controller" => $name));
        }
    }

    /**
     *  Carrega todos os componentes associados ao controller.
     *
     *  @return boolean Verdadeiro se todos os componentes foram carregados
     */
    public function loadComponent($component) {
        $component = "{$component}Component";
        if (!$this->loadedComponents[$component] = ClassRegistry::load($component, "Component")) {
            throw new MissingComponentException(array("component" => $component));
            return false;
        }
    }

    /**
     *  Executa um evento em todos os componentes do controller.
     *
     *  @param string $event Evento a ser executado
     */
    public function componentEvent($event) {
        foreach ($this->components as $component):
            $className = "{$component}Component";
            if (method_exists($this->$className, $event)):
                $this->$className->{$event}($this);
            else:
                trigger_error("O método {$event} não pode ser chamado na classe {$className}", E_USER_WARNING);
            endif;
        endforeach;
    }

    /**
      Redirects the user to another location.

      @param $url Location to be redirected to.
      @param $status HTTP status code to be sent with the redirect header.
      @param $exit If true, stops the execution of the controller.
     */
    public function redirect($url, $status = null, $exit = true) {
        $this->autoRender = false;
        $codes = array(
            100 => "Continue",
            101 => "Switching Protocols",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            307 => "Temporary Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Time-out",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Large",
            415 => "Unsupported Media Type",
            416 => "Requested range not satisfiable",
            417 => "Expectation Failed",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Time-out"
        );
        if (!is_null($status) && isset($codes[$status])):
            header("HTTP/1.1 {$status} {$codes[$status]}");
        endif;

        header('Location: ' . Mapper::url($url, true));

        if ($exit)
            $this->stop();
    }

}

?>
