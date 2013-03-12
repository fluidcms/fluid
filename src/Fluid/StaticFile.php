<?php namespace Fluid;

class StaticFile {
	/**
	 * The file to load
	 * 
	 * @var	string
	 */
	private $file;
	
	/**
	 * The file type
	 * 
	 * @var	string
	 */
	private $fileType = '';
	
	/**
	 * Load a static file
	 * 
	 * @param	string
	 * @return	void
	 */
	public function __construct( $file ) {
		$this->file = $file;
		$this->fileType();
		$this->cacheControl();
		$this->gzipCompression();
		$this->output();
		
		exit;
	}
	
	/**
	 * Get file type and output mime type header
	 * 
	 * @return	void
	 */
	private function fileType() {
		preg_match(
			"/.*\.+([txt|js|css|gif|png|jpe?g|pdf|xml|oga|ogg|m4a|ogv|mp4|m4v|webm|svg|svgz|eot|ttf|otf|woff|ico|webp|appcache|manifest|htc|crx|xpi|safariextz|vcf|txt|html|rss|atom|json|ejs]+)$/i", 
			$this->file, 
			$matches
		);
		
		if (isset($matches[1])) {
			$this->fileType = strtolower($matches[1]);
			switch($this->fileType) {
				/**
				 * JavaScript
				 * Normalize to standard type (it's sniffed in IE anyways) 
				 * tools.ietf.org/html/rfc4329#section-7.2
				 */
				case 'js':					$mimetype = 'application/x-javascript'; break;
				/* CSS */
				case 'css': 				$mimetype = 'text/css'; break;
				/* Images */
				case 'gif': 				$mimetype = 'image/gif'; break;
				case 'png': 				$mimetype = 'image/png'; break;
				case 'jpg':				 	$mimetype = 'image/jpg'; break;
				case 'jpeg':			 	$mimetype = 'image/jpeg'; break;
				/* Silverlight */
				case 'xap': 				$mimetype = 'application/x-silverlight'; break;
				/* EJS */
				case 'ejs': 				$mimetype = 'text/plain'; break;
			}
				
			/**
			 * # ----------------------------------------------------------------------
			 * # UTF-8 encoding
			 * # ----------------------------------------------------------------------
			 * 
			 * # Use UTF-8 encoding for anything served text/plain or text/html
			 * # Force UTF-8 for a number of file formats
			 */
			if(preg_match('/(html|css|txt|xml|json|xml|rss|atom)/i', $this->fileType)) $mimetype .= '; charset=utf-8';
			
			header("Content-type: {$mimetype}");
		}
	}
	
	/**
	 * Output cache control headers
	 * 
	 * @return	void
	 */
	private function cacheControl() {
		/**
		 * ----------------------------------------------------------------------
		 * Expires headers (for better cache control)
		 * ----------------------------------------------------------------------
		 *
		 * These are pretty far-future expires headers.
		 * They assume you control versioning with cachebusting query params like
		 *   <script src="application.js?20100608">
		 * Additionally, consider that outdated proxies may miscache 
		 *   www.stevesouders.com/blog/2008/08/23/revving-filenames-dont-use-querystring/
		 *
		 * If you don't use filenames to version, lower the CSS  and JS to something like
		 *   "access plus 1 week" or so.
		 */
		switch($this->fileType) {
			
			/** No cache
			 *  Media: html
			 */
	  		case 'html': case 'appcache': case 'xml': case 'json':	$cacheControl = 0; break;
			
			
			/** 1 hour cache
			 *  Media:
			 */
			 case 'rss': case 'atom':	$cacheControl = 86400; break;
			
			/** 1 week cache
			 *  Media:
			 */
			 case 'ico':	$cacheControl = 604800; break;
			
			/** 1 month cache
			 *  Media: images, video, audio, 1 month
			 */
			case 'gif': case 'png': case 'jpg': case 'jpeg': case 'ogg': case 'mp4': case 'webm': case 'htc': case 'ttf': case 'otf': case 'ttc': case 'svg': case 'svgz': case 'eot':	$cacheControl = 2592000; break;
			
			/** 1 year cache
			 *  Media:
			 */
			 case 'css': case 'js':	$cacheControl = 3153600; break;
			 
			/**  Default to 1 month
			 *  
			 */
			 default: $cacheControl = 2592000; break;
		}
		
		$expires = gmdate('D, d M Y H:i:s', time() + $cacheControl).' GMT';
			
		header("Expires: {$expires}");
		header("Cache-Control: max-age={$cacheControl}, public");
	}
	
	/**
	 * Compress the output to gzip if possible
	 * 
	 * @return	void
	 */
	private function gzipCompression() {
		/**
		 * ----------------------------------------------------------------------
		 * Gzip compression
		 * ----------------------------------------------------------------------
		 */
		if(preg_match('/(html|css|txt|xml|htc|js|json|xml|rss|atom|eot|svg|ttf|otf)/i', $this->fileType) && preg_match('/(gzip|deflate).*(gzip|deflate)/i', $_SERVER['HTTP_ACCEPT_ENCODING'])) {		
			$this->output = gzencode(file_get_contents($this->file));
			header('Content-Encoding: gzip');
		} else {
			$this->output = file_get_contents($this->file);
		}
	}
		
	/**
	 * Output file
	 * 
	 * @return	void
	 */
	private function output() {
		/** 
		 * ----------------------------------------------------------------------
		 * ETag removal
		 * ----------------------------------------------------------------------
		 */
		header_remove('ETag');
		
		$lastModified = gmdate('D, d M Y H:i:s', filemtime($this->file)).' GMT';
		header("Last-Modified: {$lastModified}");
	
		header('Content-Length: '.strlen($this->output));	
	
		echo $this->output;
		exit;
	}
}