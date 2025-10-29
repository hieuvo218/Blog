<?php
// IMPORTANT: no output (whitespace/BOM) before <?php

// Send JSON header
header('Content-Type: application/json; charset=utf-8');

// Don't let PHP print HTML errors (use logging instead)
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Use output buffering to prevent accidental output breaking JSON
ob_start();

// Convert PHP errors to exceptions so we can return JSON consistently
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
set_exception_handler(function($e) {
    // Clean any buffered output and return a JSON error
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
});

session_start();

require 'db_connect.php'; // make sure this file doesn't echo anything

try {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    // Optional: include stored password hash in response for debugging.
    // To enable, send POST parameter show_hash=1. Disabled by default.
    $show_hash = (isset($_POST['show_hash']) && $_POST['show_hash'] === '1');

    if ($username === '' || $password === '') {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT userid, username, email, password_hash FROM users WHERE username=? OR email=?");
    if (! $stmt) throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // If caller requested the stored hash for debugging, capture it now
        $stored_hash = $user['password_hash'] ?? null;

        // Diagnostics to help debug mismatches (only included when show_hash=1)
        $hash_prefix = is_string($stored_hash) && strlen($stored_hash) >= 4 ? substr($stored_hash,0,4) : null;
        $hash_len = is_string($stored_hash) ? strlen($stored_hash) : 0;
            // Determine whether stored value is a password_hash()-style hash
            $pwd_info = is_string($stored_hash) ? password_get_info($stored_hash) : ['algo' => 0];
            $is_password_hash = isset($pwd_info['algo']) && $pwd_info['algo'] !== 0;

            // Prepare diagnostics
            $password_verify_ok = false;
            $md5_match = false;
            $sha1_match = false;
            $trimmed_match = false;
            $migrated = false; // whether we'll migrate legacy/plaintext to password_hash()

            if ($is_password_hash) {
                // Normal, modern flow
                $password_verify_ok = password_verify($password, $stored_hash);
                $trimmed_match = password_verify(trim($password), $stored_hash);
            } else {
                // Legacy stored value (likely plaintext or legacy md5/sha1). Try common comparisons.
                if (is_string($stored_hash)) {
                    if ($password === $stored_hash) {
                        $password_verify_ok = true; // plaintext match
                        $migrated = true;
                    } elseif (md5($password) === $stored_hash) {
                        $password_verify_ok = true;
                        $md5_match = true;
                        $migrated = true;
                    } elseif (sha1($password) === $stored_hash) {
                        $password_verify_ok = true;
                        $sha1_match = true;
                        $migrated = true;
                    }
                }
            }

            // If we matched a legacy/plaintext credential, migrate to a secure hash now
            if ($password_verify_ok && $migrated && is_string($stored_hash)) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                if ($new_hash !== false) {
                    $up = $conn->prepare("UPDATE users SET password_hash=? WHERE userid=?");
                    if ($up) {
                        $up->bind_param("si", $new_hash, $user['userid']);
                        $up->execute();
                        // ignore update failures silently (still allow login)
                    }
                }
            }

            // Also expose the legacy checks in diagnostics
            // (recompute md5/sha1 flags if not already set)
            if (! $md5_match && is_string($stored_hash)) $md5_match = (md5($password) === $stored_hash);
            if (! $sha1_match && is_string($stored_hash)) $sha1_match = (sha1($password) === $stored_hash);

            if ($password_verify_ok) {
            $_SESSION['user_id'] = $user['userid'];
            $_SESSION['username'] = $user['username'];

            // Clean buffer then output JSON
            if (ob_get_length()) ob_end_clean();
            $respUser = [
                "id" => $user['userid'],
                "username" => $user['username'],
                "email" => $user['email'],
            ];
            if ($show_hash) {
                $respUser['password_hash'] = $stored_hash;
                $respUser['hash_prefix'] = $hash_prefix;
                $respUser['hash_length'] = $hash_len;
                $respUser['password_verify'] = true;
            }

            echo json_encode(["success" => true, "message" => "Login successful.", "user" => $respUser]);
            exit;
        } else {
            if (ob_get_length()) ob_end_clean();
            $payload = ["success" => false, "message" => "Invalid password."];
            if ($show_hash) {
                $payload['stored_hash'] = $stored_hash ?? null;
                $payload['hash_prefix'] = $hash_prefix;
                $payload['hash_length'] = $hash_len;
                $payload['password_verify'] = $password_verify_ok;
                $payload['md5_match'] = $md5_match;
                $payload['sha1_match'] = $sha1_match;
                $payload['trimmed_match'] = $trimmed_match;
            }
            echo json_encode($payload);
            exit;
        }
    } else {
        if (ob_get_length()) ob_end_clean();
        echo json_encode(["success" => false, "message" => "User not found."]);
        exit;
    }
} catch (Throwable $e) {
    // Exception handler will handle this, but just in case
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
