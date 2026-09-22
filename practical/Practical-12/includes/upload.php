<?php
/**
 * StudentHub — Secure File Upload & Poster Handler (Practical 12)
 * 
 * Implements:
 * - File size restriction (Max 2MB)
 * - Strict MIME type inspection using PHP fileinfo
 * - Image integrity validation using getimagesize()
 * - Secure cryptographically randomized renaming (Advanced Extension)
 * - Safe file deletion helper for updates & deletions
 * 

 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
defined('MAX_POSTER_SIZE') or define('MAX_POSTER_SIZE', 2 * 1024 * 1024); // 2 MB
defined('UPLOAD_DIR')       or define('UPLOAD_DIR', __DIR__ . '/../uploads/');
/**
 * Validates and stores an uploaded event poster image.
 * 
 * @param array $file $_FILES['poster'] array
 * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function handlePosterUpload(array $file): array {
    // 1. Check for upload error code
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded poster exceeds server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded poster exceeds form MAX_FILE_SIZE directive.',
            UPLOAD_ERR_PARTIAL    => 'The poster file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No poster file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write poster file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension halted file upload.',
        ];
        return [
            'success' => false,
            'path'    => null,
            'error'   => $uploadErrors[$file['error']] ?? 'Unknown file upload error.',
        ];
    }
    // 2. Validate File Size
    if ($file['size'] > MAX_POSTER_SIZE) {
        $sizeMB = round($file['size'] / (1024 * 1024), 2);
        return [
            'success' => false,
            'path'    => null,
            'error'   => "Uploaded image is too large ({$sizeMB}MB). Maximum allowed size is 2MB.",
        ];
    }
    // 3. Inspect Extension
    $originalName = $file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $allowedExtensions, true)) {
        return [
            'success' => false,
            'path'    => null,
            'error'   => 'Invalid file extension. Only JPG, JPEG, PNG, and WEBP formats are accepted.',
        ];
    }
    // 4. Inspect Real MIME Type using fileinfo (mitigates spoofed Content-Type header)
    $tmpPath = $file['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];
    if (!in_array($detectedMime, $allowedMimes, true)) {
        return [
            'success' => false,
            'path'    => null,
            'error'   => "Security violation: Detected invalid MIME type '{$detectedMime}'. Allowed: JPEG, PNG, WEBP.
",
        ];
    }
    // 5. Verify Image Integrity using getimagesize()
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        return [
            'success' => false,
            'path'    => null,
            'error'   => 'Corrupted or non-image file detected. File failed image integrity check.',
        ];
    }
    // 6. Advanced Extension: Cryptographically Randomized Renaming
    $uniqueName = sprintf(
        "event_%s_%s.%s",
        date('Ymd_His'),
        bin2hex(random_bytes(6)),
        $extension === 'jpeg' ? 'jpg' : $extension
    );
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $targetFile = UPLOAD_DIR . $uniqueName;
    // 7. Move from temporary storage to uploads/
    if (!move_uploaded_file($tmpPath, $targetFile)) {

        return [
            'success' => false,
            'path'    => null,
            'error'   => 'Failed to move uploaded poster to destination directory.',
        ];
    }
    return [
        'success' => true,
        'path'    => 'uploads/' . $uniqueName,
        'error'   => null,
    ];
}
/**
 * Safely removes a poster image file from the uploads directory.
 * 
 * @param string|null $relativePath Relative path stored in DB (e.g. 'uploads/event_123.jpg')
 */
function deletePosterFile(?string $relativePath): void {
    if (empty($relativePath)) {
        return;
    }
    $filename = basename($relativePath);
    $fullPath = UPLOAD_DIR . $filename;
    if (file_exists($fullPath) && is_file($fullPath)) {
        @unlink($fullPath);
    }
}
