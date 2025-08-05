<?php declare(strict_types=1);

namespace Topdata\TopdataDevelopmentHelperSW6\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DevelopmentHelperExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('print_r', [$this, 'printR'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Takes any variable, gets the print_r output, and wraps it in <pre><code> tags.
     * The output is escaped to prevent security issues.
     *
     * @param mixed $variable The variable to debug.
     * @return string The HTML-formatted output.
     */
    public function printR($variable): string
    {
        $output = print_r($variable, true);
        $escapedOutput = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

        return '<pre><code>' . $escapedOutput . '</code></pre>';
    }
}