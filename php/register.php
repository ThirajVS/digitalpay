<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (wants_json()) {
        json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
    }
    render_message_page('Method not allowed', 'Submit the DigitalPay registration form to use this endpoint.', false, '../register.html');
}

$errors = [];
$name = post_string('name');
$email = filter_var(post_string('email'), FILTER_SANITIZE_EMAIL);
$phone = post_string('phone');
$dob = post_string('dob');
$gender = post_string('gender');
$address = post_string('address');
$course = post_string('course_or_department') ?: 'Academic Demo';
$password = (string)($_POST['password'] ?? '');

if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
    $errors[] = 'Name must contain 3 to 100 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = 'Phone number must contain exactly 10 digits.';
}
$date = DateTimeImmutable::createFromFormat('Y-m-d', $dob);
if (!$date || $date->format('Y-m-d') !== $dob) {
    $errors[] = 'Enter a valid date of birth.';
}
if (!in_array($gender, ['Female', 'Male', 'Other', 'Non-binary', 'Prefer not to say'], true)) {
    $errors[] = 'Choose a valid gender option.';
}
if (mb_strlen($address) < 10 || mb_strlen($address) > 500) {
    $errors[] = 'Address must contain 10 to 500 characters.';
}
if (mb_strlen($course) > 120) {
    $errors[] = 'Course or department is too long.';
}
if (strlen($password) < 8 || strlen($password) > 72) {
    $errors[] = 'Password must contain 8 to 72 characters.';
}

$profilePath = null;
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
    $upload = $_FILES['profile_pic'];
    if ($upload['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Profile image upload failed.';
    } elseif ($upload['size'] > 2 * 1024 * 1024) {
        $errors[] = 'Profile image must be no larger than 2 MB.';
    } else {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        if (!isset($allowed[$mime])) {
            $errors[] = 'Profile image must be a valid JPG, PNG or WebP file.';
        } else {
            $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
            $destination = __DIR__ . '/uploads/' . $filename;
            if (!move_uploaded_file($upload['tmp_name'], $destination)) {
                $errors[] = 'The validated profile image could not be stored.';
            } else {
                $profilePath = 'uploads/' . $filename;
            }
        }
    }
}

if ($errors) {
    if (wants_json()) {
        json_response(['ok' => false, 'errors' => $errors], 422);
    }
    render_message_page('Registration needs attention', implode(' ', $errors), false, '../register.html');
}

try {
    $database = db();
    $duplicate = $database->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $duplicate->execute(['email' => $email]);
    if ($duplicate->fetch()) {
        if ($profilePath) {
            @unlink(__DIR__ . '/' . $profilePath);
        }
        if (wants_json()) {
            json_response(['ok' => false, 'error' => 'That email is already registered.'], 409);
        }
        render_message_page('Email already registered', 'Use a different fictional email or edit the existing demo user.', false, '../register.html');
    }

    $statement = $database->prepare(
        'INSERT INTO users (name, email, phone, password_hash, dob, gender, address, course_or_department, profile_pic)
         VALUES (:name, :email, :phone, :password_hash, :dob, :gender, :address, :course, :profile_pic)'
    );
    $statement->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'dob' => $dob,
        'gender' => $gender,
        'address' => $address,
        'course' => $course,
        'profile_pic' => $profilePath,
    ]);

    if (wants_json()) {
        json_response(['ok' => true, 'id' => (int)$database->lastInsertId()], 201);
    }
    render_message_page('Registration successful', 'Your fictional DigitalPay profile was stored successfully.', true, 'users.php');
} catch (PDOException $exception) {
    error_log('DigitalPay registration error: ' . $exception->getMessage());
    if ($profilePath) {
        @unlink(__DIR__ . '/' . $profilePath);
    }
    if (wants_json()) {
        json_response(['ok' => false, 'error' => 'The database is unavailable. Try again later.'], 503);
    }
    render_message_page('Database unavailable', 'DigitalPay could not save the profile. Check the database configuration and try again.', false, '../register.html');
}
