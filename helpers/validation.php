<?php
function old(string $key, $default = ''): string
{
    return e(isset($_POST[$key]) ? $_POST[$key] : $default);
}

function validate_required(array $data, array $fields): array
{
    $errors = array();
    foreach ($fields as $field => $label) {
        if (trim((string)(isset($data[$field]) ? $data[$field] : '')) === '') {
            $errors[$field] = $label . ' wajib diisi.';
        }
    }
    return $errors;
}

function validate_email_field(string $email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? null : 'Format email tidak valid.';
}

function valid_upload(array $file)
{
    $error = isset($file['error']) ? (int)$file['error'] : UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        return 'Upload foto gagal.';
    }

    $size = isset($file['size']) ? (int)$file['size'] : 0;
    if ($size < 1 || $size > 2 * 1024 * 1024) {
        return 'Ukuran foto maksimal 2 MB.';
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return 'File upload tidak valid.';
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || empty($info['mime'])) {
        return 'File harus berupa gambar JPG, PNG, atau WEBP.';
    }

    $allowed = array('image/jpeg', 'image/png', 'image/webp');
    if (!in_array(strtolower((string)$info['mime']), $allowed, true)) {
        return 'Foto harus JPG, PNG, atau WEBP.';
    }

    return null;
}

function validate_date_range(string $from, string $to)
{
    if ($from === '' || $to === '') {
        return null;
    }
    return $from <= $to ? null : 'Tanggal awal tidak boleh melebihi tanggal akhir.';
}
