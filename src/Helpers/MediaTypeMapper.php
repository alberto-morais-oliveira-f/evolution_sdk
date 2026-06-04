<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Helpers;

class MediaTypeMapper
{
    public static function guessMime(string $mediaType): string
    {
        return match ($mediaType) {
            'image' => 'image/jpeg',
            'video' => 'video/mp4',
            'audio' => 'audio/ogg',
            'document' => 'application/octet-stream',
            default => 'application/octet-stream',
        };
    }
}
