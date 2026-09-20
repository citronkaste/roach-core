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

namespace RoachPHP\Downloader\Middleware;

use Psr\Log\LoggerInterface;
use RoachPHP\Http\Request;
use RoachPHP\Support\Configurable;

final class RequestDeduplicationMiddleware implements RequestMiddlewareInterface
{
    use Configurable;

    /**
     * @var list<string>
     */
    private array $seenUris = [];

    public function __construct(private LoggerInterface $logger)
    {
    }

    #[\Override()]
    public function handleRequest(Request $request): Request
    {
        $uri = $request->getPsrRequest()->getUri();

        if ($this->option('ignore_url_fragments')) {
            $uri = $uri->withFragment('');
        }

        if ($this->option('ignore_trailing_slashes')) {
            $uri = $uri->withPath(\rtrim($uri->getPath(), '/'));
        }

        if ($this->option('ignore_query_string')) {
            $uri = $uri->withQuery('');
        }

        $normalizedUri = (string) $uri;

        if (\in_array($normalizedUri, $this->seenUris, true)) {
            $this->logger->info(
                '[RequestDeduplicationMiddleware] Dropping duplicate request',
                ['uri' => $request->getUri()],
            );

            return $request->drop('Duplicate request');
        }

        $this->seenUris[] = $normalizedUri;

        return $request;
    }

    private static function defaultOptions(): array
    {
        return [
            'ignore_url_fragments' => false,
            'ignore_trailing_slashes' => true,
            'ignore_query_string' => false,
        ];
    }
}
