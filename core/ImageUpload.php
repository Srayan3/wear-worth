<?php

/** Validates and stores an uploaded product image. Never trusts the client's filename or MIME claim. */
class ImageUpload
{
    /** @return array{success:bool, path?:string, message?:string} */
    public static function handleProductImage(array $file): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => 'Invalid upload.'];
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'message' => 'No file was selected.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload failed. Please try again.'];
        }
        if ($file['size'] > MAX_UPLOAD_BYTES) {
            return ['success' => false, 'message' => 'Image is too large (max ' . (MAX_UPLOAD_BYTES / 1024 / 1024) . 'MB).'];
        }

        // Verify the actual file content, never trust the client-supplied MIME type.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($realMime, ALLOWED_IMAGE_MIME, true)) {
            return ['success' => false, 'message' => 'Only JPG, PNG, or WEBP images are allowed.'];
        }

        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $extMap[$realMime];

        // Fully random filename — never derived from user input.
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = UPLOAD_PATH . '/' . $filename;

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'Could not save the uploaded file.'];
        }

        return ['success' => true, 'path' => 'uploads/products/' . $filename];
    }
}
