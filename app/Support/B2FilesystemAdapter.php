<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;

class B2FilesystemAdapter extends FilesystemAdapter
{
    protected $baseUrl;

    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = rtrim($url, '/');
    }

    public function url($path): string
    {
        if ($this->baseUrl) {
            return $this->baseUrl.'/'.ltrim($path, '/');
        }

        return parent::url($path);
    }
}
