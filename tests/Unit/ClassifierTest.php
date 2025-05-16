<?php

namespace CheckLaterBot\Tests\Unit;

use CheckLaterBot\Classifier;
use PHPUnit\Framework\TestCase;

class ClassifierTest extends TestCase
{
    private Classifier $classifier;

    protected function setUp(): void
    {
        $this->classifier = new Classifier();
    }

    /**
     * @dataProvider youtubeUrlProvider
     */
    public function testClassifyYoutubeUrls(string $url): void
    {
        $category = $this->classifier->classify($url);
        $this->assertEquals('youtube', $category);
    }

    /**
     * @dataProvider bookContentProvider
     */
    public function testClassifyBookContent(string $content): void
    {
        $category = $this->classifier->classify($content);
        $this->assertEquals('book', $category);
    }

    /**
     * @dataProvider movieContentProvider
     */
    public function testClassifyMovieContent(string $content): void
    {
        $category = $this->classifier->classify($content);
        $this->assertEquals('movie', $category);
    }

    public function testClassifyOtherContent(): void
    {
        $content = 'This is some random text that should be classified as other';
        $category = $this->classifier->classify($content);
        $this->assertEquals('other', $category);
    }

    public function youtubeUrlProvider(): array
    {
        return [
            ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            ['https://youtu.be/dQw4w9WgXcQ'],
            ['https://youtube.com/shorts/dQw4w9WgXcQ'],
            ['Check out this video: https://www.youtube.com/watch?v=dQw4w9WgXcQ it\'s great!'],
        ];
    }

    public function bookContentProvider(): array
    {
        return [
            ['I want to read this book: "The Great Gatsby" by F. Scott Fitzgerald'],
            ['Book recommendation: 1984 by George Orwell'],
            ['Have you read the book "To Kill a Mockingbird"?'],
            ['This is a great book: Harry Potter and the Philosopher\'s Stone'],
        ];
    }

    public function movieContentProvider(): array
    {
        return [
            ['I want to watch this movie: "The Shawshank Redemption"'],
            ['Movie recommendation: Inception (2010)'],
            ['Have you seen the film "The Godfather"?'],
            ['This is a great movie: Pulp Fiction directed by Quentin Tarantino'],
        ];
    }
}