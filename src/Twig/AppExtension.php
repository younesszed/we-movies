<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('truncate', [$this, 'truncateFilter']),
        ];
    }

    public function truncateFilter(string $text, int $length = 100, string $ellipsis = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - strlen($ellipsis)) . $ellipsis;
    }
}
