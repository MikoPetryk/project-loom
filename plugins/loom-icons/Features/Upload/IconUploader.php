<?php
/**
 * Icon Uploader
 *
 * Handles SVG file validation and upload.
 *
 * @package IconManager\Features\Upload
 * @since 2.1.0
 */



namespace IconManager\Features\Upload;

use IconManager\Features\Packs\IconPackManager;
use IconManager\Features\Icons\IconRenderer;

class IconUploader {
    private const MAX_FILE_SIZE = 512000; // 500KB
    private const ALLOWED_MIME_TYPE = 'image/svg+xml';

    private array $errors = [];

    public function upload(string $pack, array $files): bool {
        $this->errors = [];

        if (!IconPackManager::packExists($pack)) {
            $this->errors[] = __('Icon pack does not exist.', 'icon-manager');
            return false;
        }

        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack);
        $uploadedCount = 0;

        foreach ($files['name'] as $index => $filename) {
            $tmpName = $files['tmp_name'][$index];
            $fileSize = $files['size'][$index];
            $fileError = $files['error'][$index];

            if ($fileError !== UPLOAD_ERR_OK) {
                $this->errors[] = sprintf(
                    __('Error uploading %s: %s', 'icon-manager'),
                    esc_html($filename),
                    $this->getUploadErrorMessage($fileError)
                );
                continue;
            }

            if (!$this->validateFile($tmpName, $fileSize, $filename)) {
                continue;
            }

            $safeFilename = $this->sanitizeFilename($filename);
            $destination = $packDir . '/' . $safeFilename;

            if (file_exists($destination)) {
                $this->errors[] = sprintf(
                    __('File %s already exists.', 'icon-manager'),
                    esc_html($safeFilename)
                );
                continue;
            }

            if (move_uploaded_file($tmpName, $destination)) {
                chmod($destination, 0644);
                $uploadedCount++;
            } else {
                $this->errors[] = sprintf(
                    __('Failed to save %s.', 'icon-manager'),
                    esc_html($safeFilename)
                );
            }
        }

        if ($uploadedCount > 0) {
            IconRenderer::clearCache();
        }

        return $uploadedCount > 0;
    }

    private function validateFile(string $tmpName, int $fileSize, string $filename): bool {
        if ($fileSize > self::MAX_FILE_SIZE) {
            $this->errors[] = sprintf(
                __('File %s exceeds maximum size of %s.', 'icon-manager'),
                esc_html($filename),
                size_format(self::MAX_FILE_SIZE)
            );
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if ($mimeType !== self::ALLOWED_MIME_TYPE) {
            $this->errors[] = sprintf(
                __('File %s is not a valid SVG file.', 'icon-manager'),
                esc_html($filename)
            );
            return false;
        }

        if (!$this->validateSvgContent($tmpName, $filename)) {
            return false;
        }

        return true;
    }

    private function validateSvgContent(string $filePath, string $filename): bool {
        $content = file_get_contents($filePath);

        $dangerousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/<foreignObject/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->errors[] = sprintf(
                    __('File %s contains potentially dangerous code.', 'icon-manager'),
                    esc_html($filename)
                );
                return false;
            }
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        $xmlErrors = libxml_get_errors();
        libxml_clear_errors();

        if ($xml === false || !empty($xmlErrors)) {
            $this->errors[] = sprintf(
                __('File %s is not valid XML/SVG.', 'icon-manager'),
                esc_html($filename)
            );
            return false;
        }

        if ($xml->getName() !== 'svg') {
            $this->errors[] = sprintf(
                __('File %s does not contain an SVG root element.', 'icon-manager'),
                esc_html($filename)
            );
            return false;
        }

        return true;
    }

    private function sanitizeFilename(string $filename): string {
        $filename = basename($filename);
        $filename = sanitize_file_name($filename);

        if (substr($filename, -4) !== '.svg') {
            $filename .= '.svg';
        }

        return $filename;
    }

    private function getUploadErrorMessage(int $errorCode): string {
        $messages = [
            UPLOAD_ERR_INI_SIZE => __('File exceeds upload_max_filesize directive.', 'icon-manager'),
            UPLOAD_ERR_FORM_SIZE => __('File exceeds MAX_FILE_SIZE directive.', 'icon-manager'),
            UPLOAD_ERR_PARTIAL => __('File was only partially uploaded.', 'icon-manager'),
            UPLOAD_ERR_NO_FILE => __('No file was uploaded.', 'icon-manager'),
            UPLOAD_ERR_NO_TMP_DIR => __('Missing temporary folder.', 'icon-manager'),
            UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk.', 'icon-manager'),
            UPLOAD_ERR_EXTENSION => __('File upload stopped by extension.', 'icon-manager'),
        ];

        return $messages[$errorCode] ?? __('Unknown upload error.', 'icon-manager');
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function hasErrors(): bool {
        return !empty($this->errors);
    }
}
