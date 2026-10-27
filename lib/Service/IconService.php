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

use OCP\Http\Client\IClient;

class IconService {
	/**
	 * Only these schemes may be fetched. Anything else (ftp, file, gopher, ...) is rejected before a request is made.
	 */
	private const ALLOWED_SCHEMES = ['http', 'https'];

	// URL types
	const URL_TYPE_ABSOLUTE = 1;
	const URL_TYPE_ABSOLUTE_SCHEME = 2;
	const URL_TYPE_ABSOLUTE_PATH = 3;
	const URL_TYPE_RELATIVE = 4;
	const URL_TYPE_EMBED_BASE64 = 5;

	/**
	 * @var string Page URL
	 */
	public string $url;

	/**
	 * @var string Page URL, after prospective redirects
	 */
	public string $pageUrl;

	/**
	 * @var string Site root URL (homepage), based on $pageUrl
	 */
	public string $siteUrl;

	/**
	 * @var string full URI to favicon
	 */
	public string $icoUrl;

	/**
	 * @var string favicon type (file extension, ex: ico|gif|png)
	 */
	public ?string $icoType = null;

	/**
	 * @var string favicon url determination method (default /favicon.ico or found in head>link tag)
	 */
	public string $findMethod;

	/**
	 * @var string details, in case of failure
	 */
	public string $error;

	/**
	 * @var bool tell if the favicon exists (set after calling IconService)
	 */
	public bool $icoExists = false;

	/**
	 * @var string md5 of $icoData
	 */
	public ?string $icoMd5 = null;

	/**
	 * @var string favicon binary data
	 */
	public ?string $icoData = null;

	/**
	 * @var array Additional debug info
	 */
	public array $debugInfo;

	/**
	 * Create a new IconService object, search & download favicon if $auto is true
	 *
	 * All requests go through the Nextcloud HTTP client, which applies the server's SSRF protection
	 * (no requests to local/private addresses unless allow_local_remote_servers is enabled, DNS pinning,
	 * re-check on redirects), proxy and TLS settings.
	 *
	 * @param IClient $client Nextcloud HTTP client (IClientService::newClient())
	 * @param string $url Page URL
	 * @param bool $auto Search & download favicon on instantiation
	 */
	public function __construct(
		private readonly IClient $client,
		string                   $url,
		bool                     $auto = true,
	) {
		if (empty($url)) {
			throw new \InvalidArgumentException("url is empty");
		}
		if (self::urlType($url) != self::URL_TYPE_ABSOLUTE) {
			throw new \InvalidArgumentException("'" . $url . "' is not an absolute url");
		}
		if (!self::hasAllowedScheme($url)) {
			throw new \InvalidArgumentException("'" . $url . "' does not use an allowed scheme (http, https)");
		}
		$this->url = $url;
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
		if ($info['error'] !== null) {
			$this->error = $info['error'];
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
		preg_match('#^(.*)<\s*body#isU', (string) $html, $matches);
		$htmlHead = $matches[1] ?? $html;

		// HTML <base> tag href extraction
		$base_href = null;
		if (preg_match('#<base[^>]+href=(["\'])([^>]+)\1#i', (string) $htmlHead, $matches)) {
			$base_href = rtrim($matches[2], '/') . '/';
			$this->debugInfo['base_href'] = $base_href;
		}

		// HTML <link> icon tag analysis
		$this->parseLinkElement($htmlHead, $pageUrlInfo, $base_href);

		return $this->icoUrl;
	}


	private function parseLinkElement($htmlHead, $pageUrlInfo, $base_href) {
		if (preg_match('#<\s*link[^>]*(rel=(["\'])[^>\2]*icon[^>\2]*\2)[^>]*>#i', (string) $htmlHead, $matches)) {
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
								$this->icoUrl = $pageUrlInfo['scheme'] . '://' . $pageUrlInfo['host'] . $ico_href;
								$this->findMethod .= ' with page href';
							} else {
								$baseUrlInfo = parse_url($base_href);
								$this->icoUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . $ico_href;
								$this->findMethod .= ' with base href';
							}
						}
						$this->icoType = self::getExtension($this->icoUrl);
						break;
					case self::URL_TYPE_RELATIVE:
						$this->findMethod .= ' relative';
						$path = preg_replace('#/[^/]+?$#i', '/', (string) $pageUrlInfo['path']);
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
		if (strlen((string) $content) == 0) {
			$this->error = "Empty content";
			return false;
		}
		// Only ever hand out image data: this keeps the fetcher from being usable to read
		// arbitrary (e.g. internal JSON/XML) responses, even where local servers are allowed.
		if (!self::isImage((string) $content)) {
			$this->error = "Not an image"; // (probably)
			return false;
		}
		if (self::isSvg((string) $content)) {
			// the frontend builds data:image/<type> URIs, which need the full svg+xml subtype
			$this->icoType = 'svg+xml';
		}

		if (empty($this->icoType)) {
			$this->icoType = self::getExtension($this->icoUrl);
		}

		// All right baby !
		$this->icoData = $content;
		$this->icoMd5 = md5((string) $content);
		$this->icoExists = true;
		return true;
	}

	/**
	 * Download URL through the Nextcloud HTTP client
	 * Details available in $info if provided
	 *
	 * @param string $url URL to download
	 * @param array $info Download metadata: error (null on success), http_code, effective_url, content_type
	 * @return false|string
	 */
	public function downloadAs($url, &$info = null) {
		$info = [
			'error'         => null,
			'http_code'     => 0,
			'effective_url' => $url,
			'content_type'  => '',
		];

		// URLs found in the fetched HTML (link/base tags) could be attacker controlled as well
		if (!self::hasAllowedScheme((string) $url)) {
			$info['error'] = 'URL scheme not allowed';
			return false;
		}

		try {
			$response = $this->client->get($url, [
				'timeout'     => 3,
				'http_errors' => false,
				'headers'     => [
					'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0',
				],
				// track the final URL after redirects (called for every hop, the last one wins);
				// $stats is a GuzzleHttp\TransferStats
				'on_stats'    => static function ($stats) use (&$info): void {
					$info['effective_url'] = (string) $stats->getEffectiveUri();
				},
			]);
		} catch (\Exception $e) {
			// includes LocalServerException for blocked local/private addresses
			$info['error'] = $e->getMessage();
			return false;
		}

		$info['http_code'] = $response->getStatusCode();
		$info['content_type'] = $response->getHeader('Content-Type');

		if (in_array($info['http_code'], [403, 404, 500, 503])) {
			return false;
		}
		return (string) $response->getBody();
	}

	/**
	 * @param string $url
	 * @return bool true if the URL uses http or https
	 */
	public static function hasAllowedScheme(string $url): bool {
		$scheme = parse_url($url, PHP_URL_SCHEME);
		return is_string($scheme) && in_array(strtolower($scheme), self::ALLOWED_SCHEMES, true);
	}

	/**
	 * Try to detect the content type from the data itself (the Content-Type header is server controlled)
	 *
	 * @param string $content
	 * @return bool true if the data is an image
	 */
	public static function isImage(string $content): bool {
		$mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
		return (is_string($mimeType) && str_starts_with($mimeType, 'image/')) || self::isSvg($content);
	}

	/**
	 * Let's check that the document's root element is <svg>.
	 * Other XML documents or HTML that merely contains an <svg> are not accepted.
	 *
	 * @param string $content
	 * @return bool true if the data is an SVG document
	 */
	public static function isSvg(string $content): bool {
		if (empty($content)) {
			return false;
		}
		$reader = new \XMLReader();
		$previousErrorConfig = libxml_use_internal_errors(true);
		try {
			// LIBXML_NONET: never load external resources (DTDs/entities)
			if (!$reader->XML($content, null, LIBXML_NONET)) {
				return false;
			}
			// Stream up to the first element only; entities are not substituted and the rest of the document is never parsed.
			while ($reader->read()) {
				if ($reader->nodeType === \XMLReader::ELEMENT) {
					return $reader->localName === 'svg';
				}
			}
			return false;
		} finally {
			$reader->close();
			libxml_clear_errors();
			libxml_use_internal_errors($previousErrorConfig);
		}
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
		$typeMapping = [
			'ico' => '#image/(x-icon|ico)#i',
			'png' => '#image/png#i',
			'gif' => '#image/gif#i',
			'jpg' => '#image/jpe?g#i',
		];
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
	 * @param string $url
	 * @return false|int
	 */
	public static function urlType(string $url): false|int {
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
