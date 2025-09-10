<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Config;

class MailConfigurationService
{
    public function setBusinessMailConfig(int $businessId): void
    {
        $business = Business::with('emailConfiguration')->findOrFail($businessId);
        $config = $business->emailConfiguration;

        if (!$config) {
            throw new \Exception('Email configuration for this business not found.');
        }

        Config::set('mail.mailers.smtp.host', $config->host);
        Config::set('mail.mailers.smtp.port', $config->port);
        Config::set('mail.mailers.smtp.encryption', $config->encryption);
        Config::set('mail.mailers.smtp.username', $config->username);
        Config::set('mail.mailers.smtp.password', $config->password);
        Config::set('mail.from.address', $config->from_address);
        Config::set('mail.from.name', $config->from_name);
    }
}