<?php

namespace App\Tests\Service;

use App\Entity\Posts;
use App\Entity\User;
use App\Service\PostsManager;
use PHPUnit\Framework\TestCase;

class PostsManagerTest extends TestCase
{
    public function testValidPost(): void
    {
        $post = $this->createValidPost();
        $manager = new PostsManager();

        $this->assertTrue($manager->validate($post));
    }

    public function testPostWithoutTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $post = $this->createValidPost();
        $post->setTitre('');

        $manager = new PostsManager();
        $manager->validate($post);
    }

    public function testPostWithShortDescription(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $post = $this->createValidPost();
        $post->setDescription('abcd');

        $manager = new PostsManager();
        $manager->validate($post);
    }

    public function testPostWithoutCreationDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $post = new Posts();
        $post->setTitre('Tech Post');
        $post->setDescription('This is a valid post description.');
        $post->setStatus('active');
        $post->setVisibility('public');
        $post->setLikesCounter(0);
        $post->setAuthorId(new User());

        $manager = new PostsManager();
        $manager->validate($post);
    }

    public function testPostWithNegativeLikes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $post = $this->createValidPost();
        $post->setLikesCounter(-1);

        $manager = new PostsManager();
        $manager->validate($post);
    }

    public function testPostWithoutAuthor(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $post = $this->createValidPost();
        $post->setAuthorId(null);

        $manager = new PostsManager();
        $manager->validate($post);
    }

    private function createValidPost(): Posts
    {
        $post = new Posts();
        $post->setTitre('Tech Post');
        $post->setDescription('This is a valid post description.');
        $post->setStatus('active');
        $post->setVisibility('public');
        $post->setCreatedAt(new \DateTimeImmutable('2026-03-10 10:00:00'));
        $post->setUpdatedAt(new \DateTimeImmutable('2026-03-10 11:00:00'));
        $post->setLikesCounter(0);
        $post->setAuthorId(new User());

        return $post;
    }
}
