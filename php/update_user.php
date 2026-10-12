<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    render_message_page('Invalid user', 'A valid user ID is required.', false, 'users.php');
}

try {
    $database = db();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $name = post_string('name');
        $email = filter_var(post_string('email'), FILTER_SANITIZE_EMAIL);
        $phone = post_string('phone');
        $gender = post_string('gender');
        $course = post_string('course_or_department');
        if (mb_strlen($name) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9]{10}$/', $phone)) {
            throw new InvalidArgumentException('Enter a valid name, email and 10-digit phone number.');
        }
        $statement = $database->prepare(
            'UPDATE users SET name = :name, email = :email, phone = :phone, gender = :gender,
             course_or_department = :course WHERE id = :id'
        );
        $statement->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'gender' => $gender, 'course' => $course, 'id' => $id]);
        header('Location: users.php?updated=1');
        exit;
    }

    $statement = $database->prepare('SELECT id, name, email, phone, gender, course_or_department, dob, address, profile_pic, created_at FROM users WHERE id = :id');
    $statement->execute(['id' => $id]);
    $user = $statement->fetch();
    if (!$user) {
        render_message_page('User not found', 'The selected DigitalPay user does not exist.', false, 'users.php');
    }
} catch (InvalidArgumentException $exception) {
    render_message_page('Update needs attention', $exception->getMessage(), false, 'update_user.php?id=' . $id);
} catch (PDOException $exception) {
    error_log('DigitalPay update error: ' . $exception->getMessage());
    render_message_page('Database unavailable', 'The user record could not be loaded or updated.', false, 'users.php');
}

$viewOnly = isset($_GET['view']);
$disabled = $viewOnly ? ' disabled' : '';
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $viewOnly ? 'View' : 'Edit' ?> User — DigitalPay</title><link rel="stylesheet" href="../css/style.css"><link rel="stylesheet" href="../css/responsive.css"></head>
<body><div class="demo-banner"><strong>Demo / Academic Payment Environment</strong></div><main class="section"><div class="container"><form class="form-card" method="post" action="update_user.php"><a class="brand" href="../index.html"><img src="../images/logo-mark.svg" alt=""><span>Digital<span>Pay</span></span></a><div class="section-heading" style="margin-top:1.5rem"><span class="eyebrow">User management</span><h1 style="font-size:2.4rem"><?= $viewOnly ? 'View user' : 'Edit user' ?></h1></div><input type="hidden" name="id" value="<?= (int)$user['id'] ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-grid"><div class="field"><label for="name">Name</label><input class="input" id="name" name="name" value="<?= e($user['name']) ?>" required<?= $disabled ?>></div><div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" value="<?= e($user['email']) ?>" required<?= $disabled ?>></div><div class="field"><label for="phone">Phone</label><input class="input" id="phone" name="phone" pattern="[0-9]{10}" value="<?= e($user['phone']) ?>" required<?= $disabled ?>></div><div class="field"><label for="gender">Gender</label><select class="select" id="gender" name="gender"<?= $disabled ?>><?php foreach (['Female','Male','Other','Non-binary','Prefer not to say'] as $option): ?><option<?= $user['gender'] === $option ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div><div class="field field-full"><label for="course">Course / Department</label><input class="input" id="course" name="course_or_department" value="<?= e($user['course_or_department']) ?>"<?= $disabled ?>></div><?php if ($viewOnly): ?><div class="field"><span class="fieldset-label">Date of birth</span><strong><?= e($user['dob']) ?></strong></div><div class="field"><span class="fieldset-label">Created</span><strong><?= e($user['created_at']) ?></strong></div><div class="field field-full"><span class="fieldset-label">Address</span><p><?= e($user['address']) ?></p></div><?php endif; ?></div><div class="form-actions"><a class="btn btn-secondary" href="users.php">Back</a><?php if (!$viewOnly): ?><button class="btn btn-primary" type="submit">Save changes</button><?php endif; ?></div></form></div></main></body></html>
