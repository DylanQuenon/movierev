<?php

// src/Twig/NotificationExtension.php
namespace App\Twig;

use Twig\TwigFunction;
use App\Repository\ReportRepository;
use Twig\Extension\AbstractExtension;
use App\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Security;

class NotificationExtension extends AbstractExtension
{
    private $notificationRepository;
    private $security;
    private $reportRepository;

    public function __construct(NotificationRepository $notificationRepository, ReportRepository $reportRepository, Security $security)
    {
        $this->notificationRepository = $notificationRepository;
        $this->reportRepository = $reportRepository;
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('countUnreadNotifications', [$this, 'countUnreadNotifications']),
            new TwigFunction('countReports', [$this, 'countReports']),
        ];
    }

    public function countUnreadNotifications(): int
    {
        $user = $this->security->getUser();
        if (!$user) {
            return 0; // Aucun utilisateur connecté, donc 0 notifications non lues
        }

        return $this->notificationRepository->countUnreadNotifications($user);
    }

    public function countReports(): int
    {
        return $this->reportRepository->countReports();
    }
}
