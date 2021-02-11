<?php

namespace App\Image;

use Exception;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Urls\UrlBuilderFactory;

class Glide
{
    private $signKey;

    public function __construct() {
        $this->signKey = $_ENV['GLIDE_SIGN_KEY'];
    }

    public function validateRequest(string $path, array $data) {
        try {
            // Validate HTTP signature
            SignatureFactory::create($this->signKey)->validateRequest($path, $data);

            return true;
        } catch (SignatureException $e) {
            // Handle error
            throw new Exception('Cannot generate image url ' . $e->getMessage());
        }
    }

    public function generateUrl(string $path, string $img, array $data = []) {
        $urlBuilder = UrlBuilderFactory::create($path, $this->signKey);

        // Generate a URL
        return $urlBuilder-> getUrl($img, $data);
    }
}
