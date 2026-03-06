<?php

namespace App\Service;

use App\Entity\Posts;

class PostsManager
{
    public function validate(Posts $post): bool
    {
        if ($post->getTitre() === null || trim($post->getTitre()) === '') {
            throw new \InvalidArgumentException('Post title is required.');
        }

        if (mb_strlen($post->getTitre()) > 30) {
            throw new \InvalidArgumentException('Post title cannot exceed 30 characters.');
        }

        if ($post->getDescription() === null || trim($post->getDescription()) === '') {
            throw new \InvalidArgumentException('Post description is required.');
        }

        if (mb_strlen($post->getDescription()) < 5) {
            throw new \InvalidArgumentException('Post description must be at least 5 characters.');
        }

        if ($post->getCreatedAt() === null) {
            throw new \InvalidArgumentException('Post creation date is required.');
        }

        if ($post->getLikesCounter() === null || $post->getLikesCounter() < 0) {
            throw new \InvalidArgumentException('Post likes counter cannot be negative.');
        }

        if ($post->getAuthorId() === null) {
            throw new \InvalidArgumentException('Post author is required.');
        }

        return true;
    }
}
