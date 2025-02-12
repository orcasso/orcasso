<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppVersionExtension extends AbstractExtension
{
    protected $commit;
    protected $releaseTag;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_version', [$this, 'get']),
        ];
    }

    /**
     * @return string
     */
    public function get()
    {
        if (null === $this->releaseTag) {
            $this->releaseTag = trim(@exec('git describe --abbrev=0 --tags --always HEAD'));
        }

        return $this->releaseTag;
    }
}
