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

namespace RoachPHP\Testing;

use Psr\Log\LoggerInterface;

final class FakeLogger implements LoggerInterface
{
    /**
     * @var array<string, list<array{message: string|\Stringable, context: array}>>
     */
    private array $logs = [
        'emergency' => [],
        'alert' => [],
        'critical' => [],
        'error' => [],
        'warning' => [],
        'notice' => [],
        'info' => [],
        'debug' => [],
    ];

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function emergency($message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function alert($message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function critical($message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function error($message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function warning($message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function notice($message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function info($message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function debug($message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     */
    #[\Override()]
    public function log(mixed $level, $message, array $context = []): void
    {
        $level = (string) $level;

        $this->logs[$level][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function messageWasLogged(string $level, string $message, ?array $context = null): bool
    {
        if (!isset($this->logs[$level]) || [] === $this->logs[$level]) {
            return false;
        }

        return [] !== \array_filter($this->logs[$level], static function (array $log) use ($message, $context) {
            return $log['message'] === $message
                && (null === $context || $log['context'] === $context);
        });
    }
}
