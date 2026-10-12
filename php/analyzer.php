<?php
declare(strict_types=1);

/**
 * Guarded website analyzer used by ../analyzer.php.
 * Only public HTTP(S) targets are accepted to reduce SSRF risk.
 */

function analyzer_absolute_url(string $base, string $relative): string
{
    $relative = trim($relative);
    if ($relative === '' || str_starts_with($relative, 'data:') || str_starts_with($relative, 'javascript:') || str_starts_with($relative, 'mailto:') || str_starts_with($relative, 'tel:')) {
        return $relative;
    }
    if (preg_match('#^https?://#i', $relative)) {
        return $relative;
    }
    $baseParts = parse_url($base);
    if (!$baseParts || empty($baseParts['host'])) {
        return $relative;
    }
    $scheme = $baseParts['scheme'] ?? 'https';
    $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';
    $origin = $scheme . '://' . $baseParts['host'] . $port;
    if (str_starts_with($relative, '//')) {
        return $scheme . ':' . $relative;
    }
    if (str_starts_with($relative, '/')) {
        return $origin . $relative;
    }
    $path = $baseParts['path'] ?? '/';
    $directory = preg_replace('#/[^/]*$#', '/', $path);
    $combined = $directory . $relative;
    $segments = [];
    foreach (explode('/', $combined) as $segment) {
        if ($segment === '' || $segment === '.') {
            continue;
        }
        if ($segment === '..') {
            array_pop($segments);
        } else {
            $segments[] = $segment;
        }
    }
    return $origin . '/' . implode('/', $segments);
}

function analyzer_validate_public_url(string $url): string
{
    $url = trim($url);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Enter a complete, valid URL such as https://example.com.');
    }
    $parts = parse_url($url);
    if (!is_array($parts) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || empty($parts['host'])) {
        throw new InvalidArgumentException('Only public HTTP and HTTPS URLs can be analyzed.');
    }
    if (isset($parts['user']) || isset($parts['pass'])) {
        throw new InvalidArgumentException('URLs containing credentials are not accepted.');
    }
    $host = strtolower(rtrim($parts['host'], '.'));
    if ($host === 'localhost' || str_ends_with($host, '.local')) {
        throw new InvalidArgumentException('Local and private-network hosts cannot be analyzed.');
    }

    $addresses = [];
    $ipv4 = gethostbynamel($host);
    if (is_array($ipv4)) {
        $addresses = array_merge($addresses, $ipv4);
    }
    if (function_exists('dns_get_record')) {
        $ipv6 = @dns_get_record($host, DNS_AAAA);
        if (is_array($ipv6)) {
            foreach ($ipv6 as $record) {
                if (!empty($record['ipv6'])) {
                    $addresses[] = $record['ipv6'];
                }
            }
        }
    }
    if (!$addresses) {
        throw new RuntimeException('The website host could not be resolved.');
    }
    foreach (array_unique($addresses) as $address) {
        if (!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new InvalidArgumentException('Local, private and reserved network targets are blocked.');
        }
    }
    return $url;
}

function analyzer_fetch(string $url): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is required for website analysis.');
    }
    $current = analyzer_validate_public_url($url);
    for ($redirects = 0; $redirects <= 4; $redirects++) {
        $body = '';
        $tooLarge = false;
        $headers = [];
        $curl = curl_init($current);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT => 'DigitalPay-Academic-Analyzer/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$headers): int {
                $length = strlen($line);
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return $length;
            },
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$body, &$tooLarge): int {
                if (strlen($body) + strlen($chunk) > 2 * 1024 * 1024) {
                    $tooLarge = true;
                    return 0;
                }
                $body .= $chunk;
                return strlen($chunk);
            },
        ]);
        $ok = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $contentType = (string)curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        if ($tooLarge) {
            throw new RuntimeException('The webpage is larger than the 2 MB analyzer limit.');
        }
        if ($ok === false) {
            throw new RuntimeException('The webpage could not be reached: ' . ($error ?: 'network error') . '.');
        }
        if ($status >= 300 && $status < 400 && !empty($headers['location'])) {
            $current = analyzer_validate_public_url(analyzer_absolute_url($current, $headers['location']));
            continue;
        }
        if ($status < 200 || $status >= 400) {
            throw new RuntimeException("The website returned HTTP status {$status}.");
        }
        if ($body === '') {
            throw new RuntimeException('The website returned an empty response.');
        }
        if ($contentType && !str_contains(strtolower($contentType), 'text/html')) {
            throw new RuntimeException('The URL did not return an HTML webpage.');
        }
        return ['html' => $body, 'url' => $current, 'content_type' => $contentType];
    }
    throw new RuntimeException('The website redirected too many times.');
}

function analyzer_meta(DOMXPath $xpath, string $name, string $attribute = 'name'): string
{
    $nodes = $xpath->query('//meta[translate(@' . $attribute . ', "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="' . strtolower($name) . '"]/@content');
    return $nodes && $nodes->length ? trim((string)$nodes->item(0)->nodeValue) : '';
}

function analyzer_node_values(DOMXPath $xpath, string $query, ?string $base = null): array
{
    $values = [];
    $nodes = $xpath->query($query);
    if ($nodes) {
        foreach ($nodes as $node) {
            $value = trim((string)$node->nodeValue);
            if ($value !== '') {
                $values[] = $base ? analyzer_absolute_url($base, $value) : preg_replace('/\s+/u', ' ', $value);
            }
        }
    }
    return array_values(array_unique($values));
}

function analyze_website(string $inputUrl): array
{
    $fetched = analyzer_fetch($inputUrl);
    $html = $fetched['html'];
    $finalUrl = $fetched['url'];
    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument();
    $loaded = $document->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) {
        throw new RuntimeException('The returned HTML could not be parsed.');
    }
    $xpath = new DOMXPath($document);
    $titles = analyzer_node_values($xpath, '//title');
    $language = trim((string)$document->documentElement?->getAttribute('lang'));
    $charsetNodes = $xpath->query('//meta[@charset]/@charset');
    $charset = $charsetNodes && $charsetNodes->length ? trim((string)$charsetNodes->item(0)->nodeValue) : '';
    if (!$charset && preg_match('/charset=([^;\s]+)/i', $fetched['content_type'], $match)) {
        $charset = $match[1];
    }
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags(preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is', ' ', $html))));
    $wordCount = $text === '' ? 0 : count(preg_split('/\s+/u', $text));
    $links = analyzer_node_values($xpath, '//a[@href]/@href', $finalUrl);
    $pageHost = strtolower((string)parse_url($finalUrl, PHP_URL_HOST));
    $internal = [];
    $external = [];
    foreach ($links as $link) {
        $host = strtolower((string)parse_url($link, PHP_URL_HOST));
        if (!$host || $host === $pageHost) {
            $internal[] = $link;
        } elseif (preg_match('#^https?://#i', $link)) {
            $external[] = $link;
        }
    }
    $favicon = analyzer_node_values($xpath, '//link[contains(translate(@rel,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"icon")]/@href', $finalUrl);
    $canonical = analyzer_node_values($xpath, '//link[contains(concat(" ",normalize-space(translate(@rel,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"))," ")," canonical ")]/@href', $finalUrl);

    return [
        'url' => $finalUrl,
        'title' => $titles[0] ?? 'Not provided',
        'description' => analyzer_meta($xpath, 'description') ?: 'Not provided',
        'keywords' => analyzer_meta($xpath, 'keywords') ?: 'Not provided',
        'language' => $language ?: 'Not specified',
        'charset' => $charset ?: 'Not specified',
        'word_count' => $wordCount,
        'page_size' => strlen($html),
        'h1' => analyzer_node_values($xpath, '//h1'),
        'h2' => analyzer_node_values($xpath, '//h2'),
        'h3' => analyzer_node_values($xpath, '//h3'),
        'links' => $links,
        'internal_links' => array_values(array_unique($internal)),
        'external_links' => array_values(array_unique($external)),
        'images' => analyzer_node_values($xpath, '//img[@src]/@src', $finalUrl),
        'favicon' => $favicon[0] ?? (parse_url($finalUrl, PHP_URL_SCHEME) . '://' . $pageHost . '/favicon.ico'),
        'og_title' => analyzer_meta($xpath, 'og:title', 'property') ?: 'Not provided',
        'og_description' => analyzer_meta($xpath, 'og:description', 'property') ?: 'Not provided',
        'og_image' => analyzer_meta($xpath, 'og:image', 'property') ?: 'Not provided',
        'canonical' => $canonical[0] ?? 'Not provided',
        'scripts' => analyzer_node_values($xpath, '//script[@src]/@src', $finalUrl),
        'stylesheets' => analyzer_node_values($xpath, '//link[contains(concat(" ",normalize-space(translate(@rel,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"))," ")," stylesheet ")]/@href', $finalUrl),
    ];
}
