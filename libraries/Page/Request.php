<?php

namespace Page;

class Request
{
	public const BOTH = 0;
	public const GET = 1;
	public const POST = 2;

	/**
	 * Get client IP address.
	 *
	 * @return string
	 */
	public function getClientIp()
	{
		if (filter_var(($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			return $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		if (filter_var(($_SERVER['X-Real-IP'] ?? ''), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			return $_SERVER['X-Real-IP'];
		}

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = trim(current(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));

			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				return $ip;
			}
		}

		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Get current page URL.
	 *
	 * @return string
	 */
	public function getCurrentUrl()
	{
		return 'http' . (($this->isHttps()) ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . (($_SERVER['SERVER_PORT'] == '80' || ($_SERVER['SERVER_PORT'] == '443' && $this->isHttps())) ? '' : (':' . $_SERVER['SERVER_PORT'])) . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Get client user agent.
	 *
	 * @return string
	 */
	public function getUserAgent()
	{
		return $_SERVER['HTTP_USER_AGENT'] ?? '';
	}

	/**
	 * Get page referer if available.
	 *
	 * @return string
	 */
	public function getReferer()
	{
		return $_SERVER['HTTP_REFERER'] ?? '';
	}

	/**
	 * Check if current page is HTTPS.
	 *
	 * @return bool
	 */
	public function isHttps()
	{
		return (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') == 'https') ? true : ((($_SERVER['HTTPS'] ?? '') == 'on') ? true : false);
	}

	/**
	 * Rewrite a URL with new query string.
	 *
	 * @param string $url
	 * @param array  $queries
	 *
	 * @return string
	 */
	public function rewrite($url, $queries = [])
	{
		if (($parser = @parse_url($url)) === false) {
			return false;
		}

		if (!isset($parser['scheme'])) {
			return false;
		}

		if (isset($parser['query'])) {
			parse_str($parser['query'], $query);
			$queries = array_merge($query, $queries);
		}

		return $parser['scheme'] . '://' . $parser['host'] . ($parser['path'] ?? '') . (!empty($queries) ? ('?' . http_build_query($queries)) : '');
	}

	/**
	 * Get value from a GET or POST request.
	 *
	 * @param string $key
	 * @param int    $method
	 * @param mixed  $sanitize
	 *
	 * @return string
	 */
	public function request($key, $method = self::BOTH, $sanitize = true)
	{
		switch ($method) {
			case self::GET:
				$value = $_GET[$key] ?? null;

				break;

			case self::POST:
				$value = $_POST[$key] ?? null;

				break;

			default:
			case self::BOTH:
				$value = $_GET[$key] ?? null;

				if ($value === null) {
					$value = $_POST[$key] ?? null;
				}
		}

		return ($sanitize) ? $this->sanitize($value) : $value;
	}

	/**
	 * Check if this is a form post request.
	 *
	 * @return bool
	 */
	public function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	/**
	 * Clean up string to display safely.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function sanitize($text)
	{
		if (!\is_array($text) && !\is_object($text)) {
			return htmlspecialchars(stripslashes(trim($text)));
		}

		foreach ($text as $key => $value) {
			(\is_array($text)) ? $text[$key] = $this->sanitize($value) : $text->{$key} = $this->sanitize($value);
		}

		return $text;
	}
}
