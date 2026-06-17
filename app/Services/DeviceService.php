<?php

declare (strict_types = 1);

namespace App\Services;

use Detection\MobileDetect;

class DeviceService
{
    private MobileDetect $detect;

    public function __construct()
    {
        $this->detect = new MobileDetect();
    }

    public function isTablet(): bool
    {
        return $this->detect->isTablet();
    }

    public function isMobile(): bool
    {
        return $this->detect->isMobile() && ! $this->detect->isTablet();
    }

    public function isDesktop(): bool
    {
        return ! $this->detect->isMobile();
    }

    public function getDeviceType(): string
    {
        return match (true) {
            $this->isTablet() => 'tablet',
            $this->isMobile() => 'mobile',
            default           => 'desktop',
        };
    }
}
