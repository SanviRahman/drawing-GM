<?php

namespace App\Support\Media;

use App\Models\Media as AppMedia;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class ModelSectionPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->basePath($media);
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->basePath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->basePath($media) . 'responsive-images/';
    }

    private function basePath(Media $media): string
    {
        if (! $this->usesModelSectionPath($media)) {
            return $media->getKey() . '/';
        }

        $model = Str::kebab(class_basename((string) $media->model_type));
        $collection = Str::kebab((string) ($media->collection_name ?: 'default'));
        $uuid = trim((string) $media->uuid);

        if ($uuid === '') {
            $uuid = substr(hash('sha256', implode('|', [
                (string) $media->model_type,
                (string) $media->model_id,
                (string) $media->collection_name,
                (string) $media->file_name,
            ])), 0, 32);
        }

        return $model . '/' . $collection . '/' . $uuid . '/';
    }

    private function usesModelSectionPath(Media $media): bool
    {
        return $media->getCustomProperty(AppMedia::PATH_SCHEME_PROPERTY) === AppMedia::PATH_SCHEME_MODEL_SECTION;
    }
}
