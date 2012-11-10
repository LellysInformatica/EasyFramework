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

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\Component;
use Easy\Mvc\Controller\ComponentCollection;
use Easy\Storage;

/**
 * Cookie handling for controller.
 * 
 * @since 1.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Cookie extends Component
{

    private $cookie;

    public function __construct(ComponentCollection $components, $settings = array())
    {
        parent::__construct($components, $settings);
        $this->cookie = new Storage\Cookie();
    }

    public function delete($name)
    {
        return Storage\Cookie::retrieve($name)->delete();
    }

    public function read($name)
    {
        return Storage\Cookie::retrieve($name)->get();
    }

    public function write($name, $value, $expires = Storage\Cookie::SESSION)
    {
        if ($expires === null) {
            $expires = Storage\Cookie::SESSION;
        }
        $this->cookie->setName($name);
        $this->cookie->setValue($value);
        $this->cookie->setTime($expires);
        return $this;
    }

    public function create()
    {
        return $this->cookie->create();
    }

}