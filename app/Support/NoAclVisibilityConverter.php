<?php

namespace App\Support;

use League\Flysystem\AwsS3V3\VisibilityConverter;
use League\Flysystem\Visibility;

class NoAclVisibilityConverter implements VisibilityConverter
{
    public function visibilityToAcl(string $visibility): string
    {
        // Return empty string to disable ACL completely
        return '';
    }

    public function aclToVisibility(array $grants): string
    {
        // Always return public since bucket is public
        return Visibility::PUBLIC;
    }

    public function defaultForDirectories(): string
    {
        return Visibility::PUBLIC;
    }
}
