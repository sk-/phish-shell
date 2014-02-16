<?php
/**
 * Provides class \Phish\Utils\AppengineRouter.
 *
 * PHP version 5
 *
 * LICENSE: Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Code
 * @package   Phish
 * @author    Sebastian Kreft
 * @copyright 2014 Sebastian Kreft
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link      http://github.com/sk-/phish-shell
 */

namespace Phish\Utils;

/**
 * Serves files or executes scripts according to Appengine configuration.
 *
 * This helper class is to be used in a router script to be used with the
 * Development Server.
 */
class AppengineRouter
{
    /**
     * Routes the incoming reuest according to the defined rules.
     *
     * @param array  $handlers rules for serving the request.
     * @param string $url      the url of the requested resource, not including
     *                         the domain name.
     *
     * @return void
     */
    public static function route($handlers, $url)
    {
        foreach ($handlers as $handler) {
            if (self::_handle($handler, $url)) {
                break;
            }
        }
    }

    /**
     * Tries to handle a request using only one handler.
     *
     * @param array  $handler rule to test.
     * @param string $url     the url of the requested resource, not including
     *                        the domain name.
     *
     * @return true if succeeded, false otherwise.
     */
    private static function _handle($handler, $url)
    {
        $url_matcher = '{^' . $handler['url'];
        if (array_key_exists('static_dir', $handler)) {
            $url_matcher .= '/.*';
        }
        $url_matcher .= '}';
        if (preg_match($url_matcher, $url)) {
            if (array_key_exists('script', $handler)) {
                $script = preg_replace($url_matcher, $handler['script'], $url);
                require $script;
            } elseif (array_key_exists('upload', $handler)) {
                $file = preg_replace(
                    $url_matcher, $handler['static_files'], $url
                );
                // TODO(skreft): check that file exists!
                self::_serveFile($file);
            } elseif (array_key_exists('static_dir', $handler)) {
                $filename = preg_replace(
                    '{^' . $handler['url'] . '}',
                    $handler['static_dir'],
                    $url
                );
                self::_serveFile($filename);
            } else {
                echo 'Something went terribly wrong: Unhandled case';
            }
            return true;
        }
        return false;
    }

    /**
     * Serves a local file.
     *
     * @param string $filename The filename to serve.
     *
     * @return void
     */
    private static function _serveFile($filename)
    {
        header('Content-Type: ' . self::_getMimeType($filename));
        readfile($filename);
    }

    /**
     * Returns the mieme type of a filename.
     *
     * Taken from http://www.php.net/manual/en/function.mime-content-type.php
     *
     * @param string $filename The filename for which we want to extract the
     *        mime type.
     *
     * @return the mime type, or application/octet-stream if not found.
     */
    private static function _getMimeType($filename)
    {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
}