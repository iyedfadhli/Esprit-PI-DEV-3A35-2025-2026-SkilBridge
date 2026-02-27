<?php

namespace App\Event;

use App\Entity\CvApplication;
use Symfony\Contracts\EventDispatcher\Event;

class ApplicationStatusChangedEvent extends Event
{
    public const NAME = 'application.status_changed';

    private CvApplication $application;

    public function __construct(CvApplication $application)
    {
        $this->application = $application;
    }

    public function getApplication(): CvApplication
    {
        return $this->application;
    }
}
