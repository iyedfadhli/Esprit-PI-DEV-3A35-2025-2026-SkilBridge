<?php

namespace App\Service;

/**
 * Pre-publish text moderation service for posts.
 */
class ModerationService
{
    public function __construct(
        private readonly NeutrinoService $neutrino,
        private readonly PerspectiveService $perspective,
        private readonly FightImageModerationService $fightImageModeration,
    ) {}

    /**
     * @return bool true = safe, false = unsafe
     */
    public function checkText(string $text): bool
    {
        $text = trim($text);
        if ($text === '') {
            return true;
        }

        $neutrinoResult = $this->neutrino->verifyText($text);
        if (($neutrinoResult['status'] ?? 'SAFE') !== 'SAFE') {
            return false;
        }

        $perspectiveResult = $this->perspective->verifyText($text);
        return ($perspectiveResult['status'] ?? 'SAFE') === 'SAFE';
    }

    /**
     * @return array{safe: bool, reason: string, warning?: string}
     */
    public function moderatePost(string $text, ?string $absoluteImagePath = null): array
    {
        $text = trim($text);
        if ($text === '') {
            // Text can be empty; keep checking image below when present.
        } else {
            $neutrinoResult = $this->neutrino->verifyText($text);
            if (($neutrinoResult['status'] ?? 'SAFE') !== 'SAFE') {
                return ['safe' => false, 'reason' => 'profanity'];
            }

            $perspectiveResult = $this->perspective->verifyText($text);
            if (($perspectiveResult['status'] ?? 'SAFE') !== 'SAFE') {
                return ['safe' => false, 'reason' => 'hate_or_abuse'];
            }
        }

        if ($absoluteImagePath !== null && is_file($absoluteImagePath)) {
            $imageResult = $this->fightImageModeration->verifyImage($absoluteImagePath);
            $status = (string) ($imageResult['status'] ?? 'SAFE');
            $reason = (string) ($imageResult['reason'] ?? '');
            if ($status === 'UNSAFE') {
                return ['safe' => false, 'reason' => $reason !== '' ? $reason : 'violence'];
            }
            if ($status === 'UNKNOWN') {
                return [
                    'safe' => true,
                    'reason' => '',
                ];
            }
        }

        return ['safe' => true, 'reason' => ''];
    }
}
