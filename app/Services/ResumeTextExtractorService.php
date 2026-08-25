<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ResumeTextExtractorService
{
    /**
     * Extract plain text or image data from a resume file.
     *
     * @return array{text: string, image_base64: ?string, mime_type: ?string}
     */
    public function extract(UploadedFile|string $file): array
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        $extension = strtolower(is_string($file) ? pathinfo($file, PATHINFO_EXTENSION) : ($file->getClientOriginalExtension() ?: $file->extension()));

        if (empty($filePath) || !file_exists($filePath)) {
            return ['text' => '', 'image_base64' => null, 'mime_type' => null];
        }

        return match ($extension) {
            'docx' => ['text' => $this->extractFromDocx($filePath), 'image_base64' => null, 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'doc' => ['text' => $this->extractFromDoc($filePath), 'image_base64' => null, 'mime_type' => 'application/msword'],
            'pdf' => $this->extractFromPdf($filePath),
            'jpg', 'jpeg' => ['text' => '', 'image_base64' => base64_encode(@file_get_contents($filePath)), 'mime_type' => 'image/jpeg'],
            'png' => ['text' => '', 'image_base64' => base64_encode(@file_get_contents($filePath)), 'mime_type' => 'image/png'],
            'txt' => ['text' => $this->cleanText(@file_get_contents($filePath) ?: ''), 'image_base64' => null, 'mime_type' => 'text/plain'],
            default => $this->extractFromPdf($filePath),
        };
    }

    /**
     * Extract text directly (helper wrapper).
     */
    public function extractText(UploadedFile|string $file): string
    {
        $result = $this->extract($file);
        return $result['text'] ?? '';
    }

    /**
     * Extract text from Microsoft Word (.docx) file.
     */
    public function extractFromDocx(string $filePath): string
    {
        try {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                return $this->extractFromDoc($filePath);
            }

            $content = '';
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xml = $zip->getFromIndex($index);
                $zip->close();

                if ($xml) {
                    $xml = str_replace(['</w:p>', '</w:tr>', '<w:br/>', '<w:br />'], "\n", $xml);
                    $content = strip_tags($xml);
                    $content = html_entity_decode($content, ENT_QUOTES | ENT_XML1, 'UTF-8');
                }
            } else {
                $zip->close();
            }

            return $this->cleanText($content);
        } catch (\Throwable $e) {
            Log::warning('Docx text extraction failed', ['error' => $e->getMessage()]);
            return $this->extractFromDoc($filePath);
        }
    }

    /**
     * Extract text from legacy Microsoft Word (.doc) binary file.
     */
    public function extractFromDoc(string $filePath): string
    {
        try {
            $data = @file_get_contents($filePath);
            if (!$data) return '';

            // Extract ASCII / UTF-16 strings
            $extracted = '';
            if (preg_match_all('/[a-zA-Z0-9\s.,@:+\/-]{4,}/', $data, $matches)) {
                $extracted = implode(' ', $matches[0]);
            }

            return $this->cleanText($extracted);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Extract text and/or embedded image from PDF document.
     */
    public function extractFromPdf(string $filePath): array
    {
        try {
            $data = @file_get_contents($filePath);
            if (!$data) {
                return ['text' => '', 'image_base64' => null, 'mime_type' => null];
            }

            $extractedText = '';
            $imageBase64 = null;
            $imageMime = null;

            // Step 1: Detect Image Dimensions from PDF dictionary
            $imageWidth = 0;
            $imageHeight = 0;
            if (preg_match('/\/Subtype\s*\/Image\s*\/Width\s*(\d+)\s*\/Height\s*(\d+)/', $data, $dimMatches)) {
                $imageWidth = (int)$dimMatches[1];
                $imageHeight = (int)$dimMatches[2];
            } elseif (preg_match('/\/Width\s*(\d+)\s*\/Height\s*(\d+)\s*\/Subtype\s*\/Image/', $data, $dimMatches)) {
                $imageWidth = (int)$dimMatches[1];
                $imageHeight = (int)$dimMatches[2];
            }

            // Step 2: Parse binary streams with offsets
            $offset = 0;
            while (($pos = strpos($data, 'stream', $offset)) !== false) {
                $start = $pos + 6;
                if (substr($data, $start, 2) === "\r\n") {
                    $start += 2;
                } elseif (substr($data, $start, 1) === "\n" || substr($data, $start, 1) === "\r") {
                    $start += 1;
                }

                $end = strpos($data, 'endstream', $start);
                if ($end === false) break;

                $rawStream = substr($data, $start, $end - $start);

                // Case A: Direct JPEG stream
                if (str_starts_with($rawStream, "\xFF\xD8\xFF") && empty($imageBase64)) {
                    $imageBase64 = base64_encode($rawStream);
                    $imageMime = 'image/jpeg';
                }

                // Decompress stream
                $uncompressed = @gzuncompress($rawStream);
                if ($uncompressed === false) {
                    $uncompressed = @gzinflate($rawStream);
                }
                if ($uncompressed === false && strlen($rawStream) > 2) {
                    $uncompressed = @gzinflate(substr($rawStream, 2));
                }

                if ($uncompressed !== false) {
                    // Case B: Extract Text Operators
                    $text = $this->extractPdfTextObjects($uncompressed);
                    if (!empty($text)) {
                        $extractedText .= $text . "\n";
                    }

                    // Case C: Uncompressed raw RGB Image stream
                    if (empty($imageBase64) && $imageWidth > 100 && $imageHeight > 100 && extension_loaded('gd')) {
                        $expectedRgbSize = $imageWidth * $imageHeight * 3;
                        if (abs(strlen($uncompressed) - $expectedRgbSize) < 1000) {
                            $jpeg = $this->convertRgbToJpeg($uncompressed, $imageWidth, $imageHeight);
                            if ($jpeg) {
                                $imageBase64 = base64_encode($jpeg);
                                $imageMime = 'image/jpeg';
                            }
                        }
                    }
                }

                $offset = $end + 9;
            }

            // Step 3: Fallback text scan across whole PDF if stream text is minimal
            if (strlen(trim($extractedText)) < 30) {
                $clean = preg_replace('/[^\x20-\x7E\r\n\t]/', ' ', $data);
                // Extract strings from Tj or plain sentences
                preg_match_all('/\((.*?)\)[\r\n\t ]*T[jJ]/', $clean, $tjMatches);
                if (!empty($tjMatches[1])) {
                    $extractedText = implode(' ', $tjMatches[1]);
                }

                // If still empty, grab all valid word tokens
                if (strlen(trim($extractedText)) < 20) {
                    preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}|(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}|[A-Za-z]{3,}(?:\s+[A-Za-z]{3,})*/', $clean, $wordMatches);
                    if (!empty($wordMatches[0])) {
                        $extractedText = implode(' ', array_slice($wordMatches[0], 0, 100));
                    }
                }
            }

            return [
                'text' => $this->cleanText($extractedText),
                'image_base64' => $imageBase64,
                'mime_type' => $imageMime,
            ];
        } catch (\Throwable $e) {
            Log::warning('PDF text extraction failed', ['error' => $e->getMessage()]);
            return ['text' => '', 'image_base64' => null, 'mime_type' => null];
        }
    }

    /**
     * Convert raw RGB bytes to a compressed JPEG string using PHP GD.
     */
    protected function convertRgbToJpeg(string $rawRgb, int $width, int $height): ?string
    {
        try {
            $img = @imagecreatetruecolor($width, $height);
            if (!$img) return null;

            $len = strlen($rawRgb);
            $ptr = 0;

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    if ($ptr + 2 >= $len) break 2;
                    $r = ord($rawRgb[$ptr]);
                    $g = ord($rawRgb[$ptr + 1]);
                    $b = ord($rawRgb[$ptr + 2]);
                    imagesetpixel($img, $x, $y, ($r << 16) | ($g << 8) | $b);
                    $ptr += 3;
                }
            }

            ob_start();
            imagejpeg($img, null, 85);
            $jpeg = ob_get_clean();
            imagedestroy($img);

            return $jpeg ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Parse text operators inside decompressed PDF streams (BT ... ET).
     */
    protected function extractPdfTextObjects(string $content): string
    {
        $text = '';
        preg_match_all('/BT[\r\n\t ]+(.*?)[\r\n\t ]+ET/s', $content, $btMatches);

        if (!empty($btMatches[1])) {
            foreach ($btMatches[1] as $block) {
                // Tj operator: (string) Tj
                if (preg_match_all('/\((.*?)\)[\r\n\t ]*Tj/s', $block, $tjMatches)) {
                    foreach ($tjMatches[1] as $str) {
                        $text .= $this->decodePdfString($str) . ' ';
                    }
                    $text .= "\n";
                }

                // Hex string Tj operator: <00480065006C006C006F> Tj
                if (preg_match_all('/<([0-9a-fA-F\s]+)>[\r\n\t ]*Tj/s', $block, $hexMatches)) {
                    foreach ($hexMatches[1] as $hexStr) {
                        $text .= $this->decodeHexPdfString($hexStr) . ' ';
                    }
                    $text .= "\n";
                }

                // TJ operator: [(str) -12 (str2)] TJ
                if (preg_match_all('/\[(.*?)\][\r\n\t ]*TJ/s', $block, $tjArrayMatches)) {
                    foreach ($tjArrayMatches[1] as $arrayContent) {
                        preg_match_all('/\((.*?)\)/s', $arrayContent, $stringElements);
                        if (!empty($stringElements[1])) {
                            foreach ($stringElements[1] as $str) {
                                $text .= $this->decodePdfString($str);
                            }
                            $text .= ' ';
                        }

                        // Also check hex strings inside array
                        preg_match_all('/<([0-9a-fA-F\s]+)>/s', $arrayContent, $hexElements);
                        if (!empty($hexElements[1])) {
                            foreach ($hexElements[1] as $hexStr) {
                                $text .= $this->decodeHexPdfString($hexStr);
                            }
                            $text .= ' ';
                        }
                    }
                    $text .= "\n";
                }

                // Quote operator
                if (preg_match_all('/[\'"][\r\n\t ]*\((.*?)\)/s', $block, $quoteMatches)) {
                    foreach ($quoteMatches[1] as $str) {
                        $text .= "\n" . $this->decodePdfString($str);
                    }
                }
            }
        }

        return $text;
    }

    /**
     * Decode hex-encoded strings in PDF.
     */
    protected function decodeHexPdfString(string $hex): string
    {
        $clean = preg_replace('/\s+/', '', $hex);
        if (strlen($clean) % 2 !== 0) $clean .= '0';

        $binary = @hex2bin($clean);
        if ($binary === false) return '';

        // If UTF-16BE (every second char is null or printable)
        if (strlen($binary) >= 2 && ord($binary[0]) === 0) {
            $converted = @mb_convert_encoding($binary, 'UTF-8', 'UTF-16BE');
            if ($converted) return $converted;
        }

        return preg_replace('/[^\x20-\x7E]/', '', $binary);
    }

    /**
     * Decode PDF escaped characters.
     */
    protected function decodePdfString(string $str): string
    {
        $str = preg_replace_callback('/\\\\([0-7]{1,3})/', function ($m) {
            return chr(octdec($m[1]));
        }, $str);

        $replacements = [
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            '\\b' => "\b",
            '\\f' => "\f",
            '\\(' => '(',
            '\\)' => ')',
            '\\\\' => '\\',
        ];

        return strtr($str, $replacements);
    }

    /**
     * Clean and normalize raw extracted text.
     */
    protected function cleanText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }
}
