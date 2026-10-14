<?php

namespace App\Services\Website;

final class SafeArticleMarkup
{
    /**
     * Render CMS rich text using formatting tags only. All tag attributes are
     * removed to prevent stored event handlers or unsafe link/image URLs.
     * Attached article photos are rendered separately via Spatie MediaLibrary.
     */
    public static function render(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') return '';

        // Remove executable/style blocks together with their contents.
        $html = preg_replace('~<\s*(script|style|iframe|object|embed|svg|form|textarea)\b[^>]*>.*?<\s*/\s*\1\s*>~isu', '', $html) ?? '';
        $tags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><hr><pre><code><span><table><thead><tbody><tfoot><tr><th><td>';
        $html = strip_tags($html, $tags);

        // Drop ALL attributes, including unquoted onclick/style and unsafe URLs.
        $html = preg_replace('~<([a-z][a-z0-9]*)(?:\s+[^<>]*?)?\s*(/?)>~iu', '<$1$2>', $html) ?? '';
        return trim($html);
    }
}
