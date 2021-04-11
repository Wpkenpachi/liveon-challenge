<?php

namespace App\Services;
use Mailgun\Mailgun;
use Mailgun\HttpClient\HttpClientConfigurator;
use Mailgun\Hydrator\NoopHydrator;

class SendEmailService {
    protected static $mg;
    protected static $domain;

    private static function MountClient($key)
    {
        $configurator = new HttpClientConfigurator();
        $configurator->setApiKey($key);
        self::$mg = new Mailgun($configurator, new NoopHydrator());
        $exploded_domain = explode('/', env('MAILGUN_DOMAIN'));
        self::$domain = $exploded_domain[ count($exploded_domain)-1 ];
    }

    public static function validate_key($key) {
        try {
            self::MountClient($key);
            $queryString = array(
                'begin'        => time(),
                'limit'        => 1,
                'ascending'    => 'yes',
                'pretty'       => 'yes',
                'recipient'    => 'example@email.com'
            );
            $response = (self::$mg)->events()->get(self::$domain, $queryString);
            $status_code = $response->getStatusCode();
            return $status_code;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}