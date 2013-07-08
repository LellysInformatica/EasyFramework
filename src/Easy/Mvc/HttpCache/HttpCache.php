<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\HttpCache;

use Easy\HttpKernel\HttpCache\Esi;
use Easy\HttpKernel\HttpCache\HttpCache as BaseHttpCache;
use Easy\HttpKernel\HttpCache\Store;
use Easy\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manages HTTP cache objects in a Container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class HttpCache extends BaseHttpCache
{

    protected $cacheDir;
    protected $kernel;

    /**
     * Constructor.
     *
     * @param HttpKernelInterface $kernel   An HttpKernelInterface instance
     * @param string              $cacheDir The cache directory (default used if null)
     */
    public function __construct(HttpKernelInterface $kernel, $cacheDir = null)
    {
        $this->kernel = $kernel;
        $this->cacheDir = $cacheDir;

        parent::__construct($kernel, $this->createStore(), $this->createEsi(), array_merge(array('debug' => $kernel->isDebug()), $this->getOptions()));
    }

    /**
     * Forwards the Request to the backend and returns the Response.
     *
     * @param Request  $request A Request instance
     * @param Boolean  $raw     Whether to catch exceptions or not
     * @param Response $entry   A Response instance (the stale entry if present, null otherwise)
     *
     * @return Response A Response instance
     */
    protected function forward(Request $request, $raw = false, Response $entry = null)
    {
        $this->getKernel()->boot();
        $this->getKernel()->getContainer()->set('cache', $this);
        $this->getKernel()->getContainer()->set('esi', $this->getEsi());

        return parent::forward($request, $raw, $entry);
    }

    /**
     * Returns an array of options to customize the Cache configuration.
     *
     * @return array An array of options
     */
    protected function getOptions()
    {
        return array();
    }

    protected function createEsi()
    {
        return new Esi();
    }

    protected function createStore()
    {
        return new Store($this->cacheDir ? : $this->kernel->getCacheDir() . '/http_cache');
    }

}
