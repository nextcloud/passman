<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Passman\Service;


class IconService {
	// URL types
	const URL_TYPE_ABSOLUTE = 1;
	const URL_TYPE_ABSOLUTE_SCHEME = 2;
	const URL_TYPE_ABSOLUTE_PATH = 3;
	const URL_TYPE_RELATIVE = 4;
	const URL_TYPE_EMBED_BASE64 = 5;

	/**
	 * @var string Page URL
	 */
	public $url;

	/**
	 * @var string Page URL, after prospective redirects
	 */
	public $pageUrl;

	/**
	 * @var string Site root URL (homepage), based on $pageUrl
	 */
	public $siteUrl;

	/**
	 * @var string full URI to favicon
	 */
	public $icoUrl;

	/**
	 * @var string favicon type (file extension, ex: ico|gif|png)
	 */
	public $icoType;

	/**
	 * @var string favicon url determination method (default /favicon.ico or found in head>link tag)
	 */
	public $findMethod;

	/**
	 * @var string details, in case of failure
	 */
	public $error;

	/**
	 * @var bool tell if the favicon exists (set after calling IconService)
	 */
	public $icoExists;

	/**
	 * @var string md5 of $icoData
	 */
	public $icoMd5;

	/**
	 * @var string favicon binary data
	 */
	public $icoData;

	/**
	 * @var array Additional debug info
	 */
	public $debugInfo;

	/**
	 * @var string HTTP proxy (ex: localhost:8888)
	 */
	protected $httpProxy;

	/**
	 * @var bool SSL verify peer (default: true)
	 */
	protected $sslVerify;

	/**
	 * Create a new IconService object, search & download favicon if $auto is true
	 *
	 * @param string $url Page URL
	 * @param array $options Optional settings
	 * @param bool $auto Search & download favicon on instantiation
	 */
	public function __construct($url, $options = null, $auto = true) {
		if (!$url) {
			throw new \InvalidArgumentException("url is empty");
		}
		if (self::urlType($url) != self::URL_TYPE_ABSOLUTE) {
			throw new \InvalidArgumentException("'" . $url . "' is not an absolute url");
		}
		$this->url = $url;
		$this->httpProxy = isset($options['httpProxy']) ? $options['httpProxy'] : null;
		$this->sslVerify = isset($options['sslVerify']) && $options['sslVerify'] === false ? false : true;
		if ($auto) {
			$this->getFaviconUrl();
			$this->downloadFavicon();
		}
	}

	/**
	 * Download page and search html to find favicon URL. Returns favicon URL.
	 * HTML parsing is achieved using regular expressions (http://blog.codinghorror.com/parsing-html-the-cthulhu-way/)
	 * to get it work on all kinds of web documents (including non w3c compliance), which an XML parser can't do.
	 */
	public function getFaviconUrl() {
		// If already executed, don't need to search again
		if (!empty($this->icoUrl)) {
			return $this->icoUrl;
		}

		// Check URL to search
		if (empty($this->url)) {
			throw new \Exception("url is empty");
		}

		// Removing fragment (hash) from URL
		$url = $this->url;
		$urlInfo = parse_url($this->url);
		if (isset($urlInfo['fragment'])) {
			$url = str_replace('#' . $urlInfo['fragment'], '', $url);
		}

		// Downloading the page
		$info = [];
		$html = $this->downloadAs($url, $info);
		if ($info['curl_errno'] !== CURLE_OK) {
			$this->error = $info['curl_error'];
			$this->debugInfo['document_curl_errno'] = $info['curl_errno'];
			return false;
		}

		// Saving final URL (after prospective redirects) and get root URL
		$this->pageUrl = $info['effective_url'];
		$pageUrlInfo = parse_url($this->pageUrl);
		if (!empty($pageUrlInfo['scheme']) && !empty($pageUrlInfo['host'])) {
			$this->siteUrl = $pageUrlInfo['scheme'] . '://' . $pageUrlInfo['host'] . '/';
		}

		// Default favicon URL
		$this->icoUrl = $this->siteUrl . 'favicon.ico';
		$this->findMethod = 'default';

		// HTML <head> tag extraction
		preg_match('#^(.*)<\s*body#isU', $html, $matches);
		$htmlHead = isset($matches[1]) ? $matches[1] : $html;

		// HTML <base> tag href extraction
		$base_href = null;
		if (preg_match('#<base[^>]+href=(["\'])([^>]+)\1#i', $htmlHead, $matches)) {
			$base_href = rtrim($matches[2], '/') . '/';
			$this->debugInfo['base_href'] = $base_href;
		}

		// HTML <link> icon tag analysis
		$this->parseLinkElement($htmlHead, $pageUrlInfo, $base_href);

		return $this->icoUrl;
	}


	private function parseLinkElement($htmlHead, $pageUrlInfo, $base_href){
		if (preg_match('#<\s*link[^>]*(rel=(["\'])[^>\2]*icon[^>\2]*\2)[^>]*>#i', $htmlHead, $matches)) {
			$link_tag = $matches[0];
			$this->debugInfo['link_tag'] = $link_tag;

			// HTML <link> icon tag href analysis
			if (preg_match('#href\s*=\s*(["\'])(.*?)\1#i', $link_tag, $matches)) {
				$ico_href = trim($matches[2]);
				$this->debugInfo['ico_href'] = $ico_href;
				$this->findMethod = 'head';

				// Building full absolute URL
				$urlType = self::urlType($ico_href);
				switch ($urlType) {
					case self::URL_TYPE_ABSOLUTE:
						$this->findMethod .= ' absolute';
						$this->icoUrl = $ico_href;
						$this->icoType = self::getExtension($this->icoUrl);
						break;
					case self::URL_TYPE_ABSOLUTE_SCHEME:
						$this->findMethod .= ' absolute_scheme';
						$this->icoUrl = $pageUrlInfo['scheme'] . ':' . $ico_href;
						$this->icoType = self::getExtension($this->icoUrl);
						break;
					case self::URL_TYPE_ABSOLUTE_PATH:
						$this->findMethod .= ' absolute_path';
						$this->icoUrl = rtrim($this->siteUrl, '/') . '/' . ltrim($ico_href, '/');
						$this->findMethod .= ' without base href';
						if (isset($base_href)) {
							$baseHrefType = self::urlType($base_href);
							if ($baseHrefType != self::URL_TYPE_ABSOLUTE) {
								throw new \Exception("Base href is not an absolute URL");
							}
							$baseUrlInfo = parse_url($base_href);
							$this->icoUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . $ico_href;
							$this->findMethod .= ' with base href';
						}
						$this->icoType = self::getExtension($this->icoUrl);
						break;
					case self::URL_TYPE_RELATIVE:
						$this->findMethod .= ' relative';
						$path = preg_replace('#/[^/]+?$#i', '/', $pageUrlInfo['path']);
						$this->icoUrl = $pageUrlInfo['scheme'] . '://' . $pageUrlInfo['host'] . $path . $ico_href;
						$this->findMethod .= ' without base href';
						if (isset($base_href)) {
							$this->icoUrl = $base_href . $ico_href;
							$this->findMethod .= ' with base href';
						}
						$this->icoType = self::getExtension($this->icoUrl);
						break;
					case self::URL_TYPE_EMBED_BASE64:
						$this->findMethod .= ' base64';
						$this->icoUrl = $ico_href;
						break;
				}
			}
		}
	}

	/**
	 * Download the favicon if available
	 */
	public function downloadFavicon() {
		// Check params
		if (empty($this->icoUrl)) {
			return false;
		}

		// Prevent useless re-download
		if (!empty($this->icoData)) {
			return false;
		}

		// Base64 embed favicon
		if (preg_match('/^\s*data:(.*?);base64,(.*)/i', $this->icoUrl, $matches)) {
			$content = base64_decode($matches[2]);
			if ($content === false) {
				$this->error = "base64 decode error";
				return false;
			}
			$this->icoData = $content;
			$this->icoMd5 = md5($content);
			$this->icoExists = true;
			$this->icoType = self::getExtensionFromMimeType($matches[1]);
			return true;
		}

		// Download favicon
		$info = [];
		$content = $this->downloadAs($this->icoUrl, $info);
		$this->debugInfo['favicon_download_metadata'] = $info;

		// Failover : if getting a 404 with favicon URL found in HTML source, trying with the default favicon URL
		$doFailover = $content === false
			&& $info['http_code'] == 404
			&& $this->findMethod != 'default'
			&& !isset($this->debugInfo['failover']);
		if ($doFailover) {
			$this->debugInfo['failoverBefore_icoUrl'] = $this->icoUrl;
			$this->debugInfo['failoverBefore_findMethod'] = $this->findMethod;
			$this->icoUrl = $this->siteUrl . 'favicon.ico';
			$this->findMethod = 'default';
			$this->icoType = self::getExtension($this->icoUrl);
			$this->debugInfo['failover'] = true;
			return $this->downloadFavicon();
		}

		// Download error
		if ($content === false) {
			$this->error = 'Favicon download error (HTTP ' . $info['http_code'] . ')';
			return false;
		}

		// Check favicon content
		if (strlen($content) == 0) {
			$this->error = "Empty content";
			return false;
		}
		$textTypes = array('text/html', 'text/plain');
		if (in_array($info['content_type'], $textTypes) || preg_match('#(</html>|</b>)#i', $content)) {
			$this->error = "Seems to be a text document";
			return false;
		}

		// All right baby !
		$this->icoData = $content;
		$this->icoMd5 = md5($content);
		$this->icoExists = true;
		return true;
	}

	/**
	 * Download URL as Firefox with cURL
	 * Details available in $info if provided
	 *
	 * @param string $url URL to download
	 * @param array $info Download metadata
	 * @return bool|mixed
	 */
	public function downloadAs($url, &$info = null) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 20); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		// Don't check SSL certificate to allow autosigned certificate
		if ($this->sslVerify === false) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		// Set HTTP proxy
		if ($this->httpProxy) {
			curl_setopt($ch, CURLOPT_PROXY, $this->httpProxy);
		}

		$content = curl_exec($ch);
		$info['curl_errno'] = curl_errno($ch);
		$info['curl_error'] = curl_error($ch);
		$info['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$info['effective_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$info['redirect_count'] = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
		$info['content_type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);

		if ($info['curl_errno'] !== CURLE_OK || in_array($info['http_code'], array(403, 404, 500, 503))) {
			return false;
		}
		return $content;
	}

	/**
	 * Return file extension from an URL or a file path
	 *
	 * @param string $url
	 * @return string
	 */
	public static function getExtension($url) {
		if (preg_match('#^(https?|ftp)#i', $url)) {
			$purl = parse_url($url);
			$url = $purl['path'];
		}
		$info = pathinfo($url);
		return $info['extension'];
	}

	/**
	 * Return file extension from MIME type
	 *
	 * @param string $mimeType
	 * @return string
	 */
	public static function getExtensionFromMimeType($mimeType) {
		$typeMapping = array(
			'ico' => '#image/(x-icon|ico)#i',
			'png' => '#image/png#i',
			'gif' => '#image/gif#i',
			'jpg' => '#image/jpe?g#i',
		);
		foreach ($typeMapping as $key => $val) {
			if (preg_match($val, $mimeType)) {
				return $key;
			}
		}
		return 'ico';
	}

	/**
	 * Return URL type, either :
	 * - URL_TYPE_ABSOLUTE        ex: http://www.domain.com/images/fav.ico
	 * - URL_TYPE_ABSOLUTE_SCHEME ex: //www.domain.com/images/fav.ico
	 * - URL_TYPE_ABSOLUTE_PATH   ex: /images/fav.ico
	 * - URL_TYPE_RELATIVE        ex: ../images/fav.ico
	 * - URL_TYPE_EMBED_BASE64    ex: data:image/x-icon;base64,AAABAA...
	 *
	 * @return int
	 */
	public static function urlType($url) {
		if (empty($url)) {
			return false;
		}
		$urlInfo = parse_url($url);
		if (!empty($urlInfo['scheme'])) {
			return $urlInfo['scheme'] === 'data' ? self::URL_TYPE_EMBED_BASE64 : self::URL_TYPE_ABSOLUTE;
		} elseif (preg_match('#^//#i', $url)) {
			return self::URL_TYPE_ABSOLUTE_SCHEME;
		} elseif (preg_match('#^/[^/]#i', $url)) {
			return self::URL_TYPE_ABSOLUTE_PATH;
		}
		return self::URL_TYPE_RELATIVE;
	}

	/**
	 * Show object printable properties, or return it if $return is true
	 *
	 * @param boolean $return
	 * @return IconService
	 */
	public function debug($return = false) {
		$dump = clone $this;
		if (!empty($dump->icoData) && is_string($dump->icoData)) {
			$dump->icoData = substr(bin2hex($dump->icoData), 0, 16) . ' ...';
		}
		if ($return) {
			return $dump;
		}
		print_r($dump);
	}
}