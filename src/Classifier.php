<?php

namespace CheckLaterBot;

class Classifier
{
    /**
     * Classify content into a category
     *
     * @param string $content The content to classify
     * @return string The category name
     */
    public static function classify(string $content): string
    {
        try {
            // Normalize content for classification
            $normalizedContent = strtolower(trim($content));

            // YouTube video detection
            if (self::isYouTubeVideo($normalizedContent)) {
                return 'youtube';
            }

            // Book detection
            if (self::isBook($normalizedContent)) {
                return 'book';
            }

            // Movie detection
            if (self::isMovie($normalizedContent)) {
                return 'movie';
            }

            // Default category
            return 'other';
        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->warning('Error during content classification: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'content_preview' => substr($content, 0, 100) . (strlen($content) > 100 ? '...' : '')
            ]);

            // Return default category on error
            return 'other';
        }
    }

    /**
     * Check if content is a YouTube video
     *
     * @param string $content The content to check
     * @return bool True if content is a YouTube video
     */
    private static function isYouTubeVideo(string $content): bool
    {
        // YouTube URL patterns
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        // Check for YouTube-related keywords
        $youtubeKeywords = ['youtube video', 'youtube channel', 'youtube tutorial'];
        foreach ($youtubeKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if content is a book
     *
     * @param string $content The content to check
     * @return bool True if content is a book
     */
    private static function isBook(string $content): bool
    {
        // Book-related patterns
        $bookPatterns = [
            '/book titled/i',
            '/book by/i',
            '/author/i',
            '/novel/i',
            '/read the book/i',
            '/isbn/i',
            '/published by/i',
            '/publication date/i'
        ];

        foreach ($bookPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        // Book-related keywords
        $bookKeywords = ['book', 'reading', 'novel', 'author', 'chapter', 'paperback', 'ebook'];
        foreach ($bookKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if content is a movie
     *
     * @param string $content The content to check
     * @return bool True if content is a movie
     */
    private static function isMovie(string $content): bool
    {
        // Movie-related patterns
        $moviePatterns = [
            '/movie titled/i',
            '/film by/i',
            '/directed by/i',
            '/starring/i',
            '/watch the movie/i',
            '/imdb/i',
            '/released in/i',
            '/release date/i'
        ];

        foreach ($moviePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        // Movie-related keywords
        $movieKeywords = ['movie', 'film', 'cinema', 'director', 'actor', 'actress', 'trailer', 'watch'];
        foreach ($movieKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
