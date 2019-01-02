<?php
namespace App\Libary\Updater;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Updater {

    private const currentVersion = 2;
    private $client;

    public function __construct() {
        $this->client = new Client();
    }

    public function checkVersion() {
        try {
            $request = $this->client->request('GET', 'https://update.henrybrink.de/schuelerblog/info.json');

            if($request->getStatusCode() == 200) {
                $json = \json_decode($request->getBody(), true);

                if($json['currentVersion'] > self::currentVersion) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (ClientException $e) {
            return false;
        }
    }

}