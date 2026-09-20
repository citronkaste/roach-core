<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/roach-php/roach
 */

namespace RoachPHP\Http;

use GuzzleHttp\Psr7\Uri;

final class URL
{
    public function __construct(
        public readonly ?string $scheme,
        public readonly ?string $host,
        public readonly ?int $port,
        public readonly ?string $username,
        public readonly ?string $password,
        public readonly ?string $path,
        public readonly Query $query,
        public readonly ?string $fragment,
    ) {
    }

    /**
     * @throws MalformedUriException
     */
    public static function parse(string $url): self
    {
        /**
         * @var array{
         *     host?: string,
         *     user?: string,
         *     pass?: string,
         *     port?: int,
         *     scheme?: string,
         *     path?: string,
         *     query?: string,
         *     fragment?: string,
         * }|false $parts
         */
        $parts = \parse_url($url);

        if (false === $parts) {
            throw MalformedUriException::forUri($url);
        }

        return new self(
            $parts['scheme'] ?? null,
            $parts['host'] ?? null,
            $parts['port'] ?? null,
            $parts['user'] ?? null,
            $parts['pass'] ?? null,
            $parts['path'] ?? null,
            Query::parse($parts['query'] ?? ''),
            $parts['fragment'] ?? null,
        );
    }

    public function toString(): string
    {
        $authority = $this->host ?? '';

        if (null !== $this->port) {
            $authority .= ':' . $this->port;
        }

        if (null !== $this->username) {
            $userInfo = $this->username;

            if (null !== $this->password) {
                $userInfo .= ':' . $this->password;
            }

            $authority = $userInfo . '@' . $authority;
        }

        return Uri::composeComponents(
            $this->scheme,
            $authority,
            $this->path ?? '',
            $this->query->toString(),
            $this->fragment,
        );
    }

    /**
     * Checks if two URLs are equal.
     *
     * URLs are considered equal if they contain all the same parts with all the
     * same values. Note that if the URLs have a query string, the order of the
     * query parameters does not matter.
     *
     * If a string is provided, it will be converted to a URL object internally.
     *
     * @throws MalformedUriException thrown if the provided URL is a string and cannot be parsed to a valid URL object
     */
    public function equals(self|string $other): bool
    {
        if (\is_string($other)) {
            $other = self::parse($other);
        }

        return $this->scheme === $other->scheme
            && $this->host === $other->host
            && $this->port === $other->port
            && $this->username === $other->username
            && $this->password === $other->password
            && $this->path === $other->path
            && $this->query->equals($other->query)
            && $this->fragment === $other->fragment;
    }
}
