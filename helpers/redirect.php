<?php
function redirect(string $route = '')
{
    header('Location: ' . base_url($route));
    exit;
}

function flash(string $key, $message = null)
{
    if ($message !== null) {
        if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
            $_SESSION['_flash'] = array();
        }
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $msg = isset($_SESSION['_flash'][$key]) ? $_SESSION['_flash'][$key] : null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}
