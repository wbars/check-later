<?php

namespace CheckLaterBot;

class Classifier
{
    /**
     * Classify content into predefined categories
     *
     * @param string $content The content to classify
     * @return string The determined category
     */
    public static function classify(string $content): string
    {
        $content = trim($content);
        
        // Check if it's a YouTube video
        if (self::isYouTubeUrl($content)) {
            return 'youtube';
        }
        
        // Check if it's a book (ISBN, Goodreads, etc.)
        if (self::isBookReference($content)) {
            return 'book';
        }
        
        // Check if it's a movie (IMDB, movie title patterns, etc.)
        if (self::isMovieReference($content)) {
            return 'movie';
        }
        
        // Default category
        return 'other';
    }
    
    /**
     * Check if the content is a YouTube URL
     *
     * @param string $content The content to check
     * @return bool Whether it's a YouTube URL
     */
    private static function isYouTubeUrl(string $content): bool
    {
        // Match common YouTube URL patterns
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if the content is a book reference
     *
     * @param string $content The content to check
     * @return bool Whether it's a book reference
     */
    private static function isBookReference(string $content): bool
    {
        // Check for ISBN patterns
        if (preg_match('/ISBN[-]?1[03]?[:]?\s?[0-9]{3}[-]?[0-9]{1,5}[-]?[0-9]{1,7}[-]?[0-9]{1,7}[-]?[0-9X]/', $content)) {
            return true;
        }
        
        // Check for Goodreads URLs
        if (preg_match('/goodreads\.com\/book\/show\//', $content)) {
            return true;
        }
        
        // Check for Amazon book URLs
        if (preg_match('/amazon\.com\/.*\/dp\/[0-9A-Z]{10}/', $content) && 
            (stripos($content, 'book') !== false || stripos($content, 'read') !== false)) {
            return true;
        }
        
        // Check for common book title patterns (e.g., "Title by Author")
        if (preg_match('/\s+by\s+[A-Z][a-z]+(\s+[A-Z][a-z]+)*/', $content)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if the content is a movie reference
     *
     * @param string $content The content to check
     * @return bool Whether it's a movie reference
     */
    private static function isMovieReference(string $content): bool
    {
        // Check for IMDB URLs
        if (preg_match('/imdb\.com\/title\/tt[0-9]{7,8}/', $content)) {
            return true;
        }
        
        // Check for Rotten Tomatoes URLs
        if (preg_match('/rottentomatoes\.com\/m\//', $content)) {
            return true;
        }
        
        // Check for common movie title patterns (e.g., "Movie Title (YYYY)")
        if (preg_match('/\([12][0-9]{3}\)/', $content)) {
            return true;
        }
        
        // Check for streaming service URLs with movie/tv indicators
        $streamingServices = ['netflix.com/title', 'hulu.com/movie', 'amazon.com/gp/video', 
                             'disneyplus.com', 'hbomax.com', 'primevideo.com'];
        
        foreach ($streamingServices as $service) {
            if (stripos($content, $service) !== false) {
                return true;
            }
        }
        
        return false;
    }
}