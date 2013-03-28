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

namespace Easy\Network;

use DateTime;
use Easy\Network\File\Exception\FileException;
use Easy\Network\File\File;
use Easy\Network\Request;
use Easy\Network\Response;
use SplFileInfo;

/**
 * BinaryFileResponse represents an HTTP response delivering a file.
 *
 * @author Niklas Fiekas <niklas.fiekas@tu-clausthal.de>
 * @author stealth35 <stealth35-php@live.fr>
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Jordan Alliot <jordan.alliot@gmail.com>
 * @author Sergey Linnik <linniksa@gmail.com>
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class BinaryFileResponse extends Response {

    protected static $trustXSendfileTypeHeader = false;
    protected $file;
    protected $offset;
    protected $maxlen;

    /**
     * Constructor.
     *
     * @param SplFileInfo|string $file               The file to stream
     * @param integer            $status             The response status code
     * @param array              $headers            An array of response headers
     * @param boolean            $public             Files are public by default
     * @param null|string        $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param boolean            $autoEtag           Whether the ETag header should be automatically set
     * @param boolean            $autoLastModified   Whether the Last-Modified header should be automatically set
     */
    public function __construct($file, $status = 200, $headers = array(), $public = true, $contentDisposition = null, $autoEtag = false, $autoLastModified = true) {
        parent::__construct(null, $status, $headers);

        $this->setFile($file, $contentDisposition, $autoEtag, $autoLastModified);

        if ($public) {
            $this->setPublic();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function create($file = null, $status = 200, $headers = array(), $public = true, $contentDisposition = null, $autoEtag = false, $autoLastModified = true) {
        return new static($file, $status, $headers, $public, $contentDisposition, $autoEtag, $autoLastModified);
    }

    /**
     * Sets the file to stream.
     *
     * @param SplFileInfo|string $file The file to stream
     * @param string             $contentDisposition
     * @param Boolean            $autoEtag
     * @param Boolean            $autoLastModified
     *
     * @return BinaryFileResponse
     *
     * @throws FileException
     */
    public function setFile($file, $contentDisposition = null, $autoEtag = false, $autoLastModified = true) {
        $file = new File((string) $file);

        if (!$file->isReadable()) {
            throw new FileException('File must be readable.');
        }

        $this->file = $file;

        if ($autoEtag) {
            $this->setAutoEtag();
        }

        if ($autoLastModified) {
            $this->setAutoLastModified();
        }

        if ($contentDisposition) {
            $this->setContentDisposition($contentDisposition);
        }

        return $this;
    }

    /**
     * Gets the file.
     *
     * @return File The file to stream
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Automatically sets the Last-Modified header according the file modification date.
     */
    public function setAutoLastModified() {
        $this->setLastModified(DateTime::createFromFormat('U', $this->file->getMTime()));

        return $this;
    }

    /**
     * Automatically sets the ETag header according to the checksum of the file.
     */
    public function setAutoEtag() {
        $this->setEtag(sha1_file($this->file->getPathname()));

        return $this;
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition      ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename         Optionally use this filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @return BinaryFileResponse
     */
    public function setContentDisposition($disposition, $filename = '', $filenameFallback = '') {
        if ($filename === '') {
            $filename = $this->file->getFilename();
        }

        $dispositionHeader = $this->headers->makeDisposition($disposition, $filename, $filenameFallback);
        $this->headers->set('Content-Disposition', $dispositionHeader);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Request $request) {
        $this->headers->set('Content-Length', $this->file->getSize());
        $this->headers->set('Accept-Ranges', 'bytes');
        $this->headers->set('Content-Transfer-Encoding', 'binary');

        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', $this->file->getMimeType() ? : 'application/octet-stream');
        }

        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        $this->offset = 0;
        $this->maxlen = -1;

        if (self::$trustXSendfileTypeHeader && $request->headers->has('X-Sendfile-Type')) {
            // Use X-Sendfile, do not send any content.
            $type = $request->headers->get('X-Sendfile-Type');
            $path = $this->file->getRealPath();
            if (strtolower($type) == 'x-accel-redirect') {
                // Do X-Accel-Mapping substitutions.
                foreach (explode(',', $request->headers->get('X-Accel-Mapping', '')) as $mapping) {
                    $mapping = explode('=', $mapping, 2);

                    if (2 == count($mapping)) {
                        $location = trim($mapping[0]);
                        $pathPrefix = trim($mapping[1]);

                        if (substr($path, 0, strlen($pathPrefix)) == $pathPrefix) {
                            $path = $location . substr($path, strlen($pathPrefix));
                            break;
                        }
                    }
                }
            }
            $this->headers->set($type, $path);
            $this->maxlen = 0;
        } elseif ($request->headers->has('Range')) {
            // Process the range headers.
            if (!$request->headers->has('If-Range') || $this->getEtag() == $request->headers->get('If-Range')) {
                $range = $request->headers->get('Range');

                list($start, $end) = array_map('intval', explode('-', substr($range, 6), 2)) + array(0);

                if ('' !== $end) {
                    $this->maxlen = $end - $start;
                } else {
                    $end = $this->file->getSize() - 1;
                }

                $this->offset = $start;

                $this->setStatusCode(206);
                $this->headers->set('Content-Range', sprintf('bytes %s-%s/%s', $start, $end, $this->file->getSize()));
            }
        }

        return $this;
    }

    /**
     * Sends the file.
     */
    public function sendContent() {
        if (!$this->isSuccessful()) {
            parent::sendContent();

            return;
        }

        if (0 === $this->maxlen) {
            return;
        }

        $out = fopen('php://output', 'wb');
        $file = fopen($this->file->getPathname(), 'rb');

        stream_copy_to_stream($file, $out, $this->maxlen, $this->offset);

        fclose($out);
        fclose($file);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException when the content is not null
     */
    public function setContent($content) {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a BinaryFileResponse instance.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return false
     */
    public function getContent() {
        return false;
    }

    /**
     * Trust X-Sendfile-Type header.
     */
    public static function trustXSendfileTypeHeader() {
        self::$trustXSendfileTypeHeader = true;
    }

}