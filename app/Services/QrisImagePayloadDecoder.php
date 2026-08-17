<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Zxing\QrReader;

class QrisImagePayloadDecoder
{
    public function decode(UploadedFile $image): string
    {
        $payload = trim((string) (new QrReader($image->getRealPath()))->text());

        if (blank($payload)) {
            throw new InvalidArgumentException('QRIS pada gambar tidak dapat dibaca. Unggah gambar yang lebih jelas atau isi payload secara manual.');
        }

        return $payload;
    }
}
