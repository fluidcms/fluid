<?php

namespace Fluid;

/**
 * Output a static file to the browser
 */
class StaticFile
{
    /** @var string $file */
    private $file;

    /** @var null|string $fileType */
    private $fileType = '';

    /** @var string $contentType */
    private $contentType = '';

    /** @var string $content */
    private $content = '';

    /**
     * Load a static file
     *
     * @param string $file
     * @param string|null $type
     * @param bool $contentProvided
     */
    public function __construct($file, $type = null, $contentProvided = false)
    {
        if (!$contentProvided) {
            $this->file = $file;
        } else {
            $this->content = $file;
        }

        if (!isset($type)) {
            $this->fileType = $this->getFileType($this->file);
        } else {
            $this->fileType = $type;
        }

        $this->contentType = $this->getContentType($this->fileType);

        $this->output();
    }

    /**
     * Get file type
     *
     * @param string $file
     * @return string|null
     */
    public static function getFileType($file)
    {
        preg_match(
            "/.*\\.+([txt|js|css|gif|png|jpe?g|pdf|xml|oga|ogg|m4a|ogv|mp4|m4v|webm|svg|svgz|eot|ttf|otf|woff|ico|webp|appcache|manifest|htc|crx|xpi|safariextz|vcf|txt|html|rss|atom|json|ejs]+)$/i",
            $file,
            $matches
        );

        if (isset($matches[1])) {
            return strtolower($matches[1]);
        }

        return null;
    }

    /**
     * Get content type string
     *
     * @param string $fileType
     * @return string
     */
    public static function getContentType($fileType)
    {
        $mimetype = '';

        switch ($fileType) {
            // JavaScript
            case 'js':
                $mimetype = 'application/x-javascript';
                break;
            // CSS
            case 'css':
                $mimetype = 'text/css';
                break;
            // Images
            case 'gif':
                $mimetype = 'image/gif';
                break;
            case 'png':
                $mimetype = 'image/png';
                break;
            case 'jpg':
                $mimetype = 'image/jpg';
                break;
            case 'jpeg':
                $mimetype = 'image/jpeg';
                break;
            // Silverlight
            case 'xap':
                $mimetype = 'application/x-silverlight';
                break;
            // EJS
            case 'ejs':
                $mimetype = 'text/plain';
                break;
            // HTML
            case 'html':
                $mimetype = 'text/html';
                break;
        }

        // Use UTF-8 encoding for anything served text/plain or text/html
        // Force UTF-8 for a number of file formats
        if (preg_match('/(html|css|txt|xml|json|xml|rss|atom|ejs)/i', $fileType)) {
            $mimetype .= '; charset=utf-8';
        }

        return $mimetype;
    }

    /**
     * Output cache control headers
     */
    private function cacheControl()
    {
        switch ($this->fileType) {
            // No cache
            case 'html':
            case 'appcache':
            case 'xml':
            case 'json':
                $cacheControl = 0;
                break;

            // 1 hour cache
            case 'rss':
            case 'atom':
                $cacheControl = 86400;
                break;

            // 1 year cache
            case 'css':
            case 'js':
            case 'ico':
                $cacheControl = 3153600;
                break;

            // Default to 1 month
            default:
                $cacheControl = 2592000;
                break;
        }

        $expires = gmdate('D, d M Y H:i:s', time() + $cacheControl) . ' GMT';

        header("Expires: {$expires}");
        header("Cache-Control: max-age={$cacheControl}, public");
    }

    /**
     * Get the content of the file
     */
    private function getFileContent()
    {
        if (empty($this->content)) {
            $this->content = file_get_contents($this->file);
        }
    }

    /**
     * Output file to the browser
     */
    private function output()
    {
        $this->getFileContent();
        $this->cacheControl();

        header_remove('ETag');

        header("Content-type: {$this->contentType}");

        // If file content is not provided, use file time for last modified;
        if (isset($this->file)) {
            $lastModified = gmdate('D, d M Y H:i:s', filemtime($this->file)) . ' GMT';
        } else {
            $lastModified = gmdate('D, d M Y H:i:s') . ' GMT';
        }

        header("Last-Modified: {$lastModified}");

        header('Content-Length: ' . strlen($this->content));

        echo $this->content;
    }
}