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


/** Convert HTML datetime-local values into MySQL DATETIME values. */
function normalize_datetime_local(string $value): string
{
    $value = trim(str_replace('T', ' ', $value));
    if ($value === '') {
        return '';
    }
    // datetime-local controls normally submit YYYY-MM-DDTHH:MM.
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
        $value .= ':00';
    }
    return $value;
}

function valid_datetime_value(string $value): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
        return false;
    }
    $date = DateTime::createFromFormat('!Y-m-d H:i:s', $value);
    $errors = DateTime::getLastErrors();
    return $date !== false
        && ($errors === false || ((int)$errors['warning_count'] === 0 && (int)$errors['error_count'] === 0))
        && $date->format('Y-m-d H:i:s') === $value;
}


/** Validate an HTML time input (HH:MM, optionally HH:MM:SS). */
function valid_time_value(string $value): bool
{
    return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', trim($value)) === 1;
}

/** Convert a time-only input to a DATETIME while retaining dates in the database. */
function time_on_date(string $time, string $date): string
{
    $time = trim($time);
    if (!valid_time_value($time)) {
        return '';
    }
    if (strlen($time) === 5) {
        $time .= ':00';
    } elseif (strlen($time) > 8) {
        return '';
    }
    return $date . ' ' . $time;
}
