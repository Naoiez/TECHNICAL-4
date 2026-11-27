<?php
declare(strict_types=1);
// InfinityFree standalone mode. Keeps tasks in the current browser's PHP session.
ini_set('display_errors', '0');
error_reporting(E_ALL);
if (session_status() !== PHP_SESSION_ACTIVE && !session_start()) {
    http_response_code(500);
    exit('PHP sessions are unavailable on this hosting account.');
}
$_SESSION['tasks_today'] ??= [];
$_SESSION['tasks_next_id'] ??= 1;
$_SESSION['tasks_csrf'] ??= bin2hex(random_bytes(24));
$_SESSION['tasks_notifications'] ??= [];
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirectTo(string $route): never { header('Location: '.$route, true, 303); exit; }
function errorPage(int $status, string $message): never {
    http_response_code($status);
    echo '<!doctype html><meta charset="utf-8"><title>Error</title><h1>'.e($message).'</h1><p><a href="/tasks">Tasks</a></p>';
    exit;
}
function csrfInput(): string {
    return '<input type="hidden" name="_token" value="'.e($_SESSION['tasks_csrf']).'">';
}
function render(string $title, string $body): never {
    $flash = $_SESSION['tasks_flash'] ?? null;
    unset($_SESSION['tasks_flash']);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($title).' | Tasks for Today</title><style>body{font:16px system-ui;background:#f6f8fc;color:#243047;max-width:850px;margin:2rem auto;padding:0 1rem}nav,main{background:#fff;border-radius:12px;padding:1.2rem;margin-bottom:1rem}nav a{margin-right:1rem}a{color:#2455ad}input,select,textarea{display:block;width:100%;box-sizing:border-box;padding:.65rem;margin:.3rem 0 1rem;border:1px solid #abb7c8;border-radius:5px}button,.button{display:inline-block;background:#2455ad;color:#fff;padding:.65rem 1rem;border:0;border-radius:6px;text-decoration:none;cursor:pointer}label{font-weight:600}.error{color:#a11}.notice{color:#176532}.inline{display:inline}article{border-top:1px solid #eee;padding:.5rem 0}</style></head><body><nav><a href="/">Home</a><a href="/tasks">Tasks</a><a href="/profile">Profile</a><a href="/about">About</a></nav><main>'.($flash?'<p class="notice">'.e($flash).'</p>':'').'<h1>'.e($title).'</h1>'.$body.'</main></body></html>';
    exit;
}
function taskById(int $id): array {
    $task = $_SESSION['tasks_today'][$id] ?? null;
    if (!is_array($task) || ($task['is_archived'] ?? false)) errorPage(404, 'Task not found.');
    return $task;
}
function formFields(array $task = [], array $errors = []): string {
    $title = e($_POST['title'] ?? $task['title'] ?? '');
    $description = e($_POST['description'] ?? $task['description'] ?? '');
    $status = $_POST['status'] ?? $task['status'] ?? 'pending';
    $date = e($_POST['due_date'] ?? $task['due_date'] ?? '');
    $html = '<label>Title<input name="title" maxlength="100" required value="'.$title.'"></label>';
    if (isset($errors['title'])) $html .= '<p class="error">'.e($errors['title']).'</p>';
    $html .= '<label>Description<textarea name="description" maxlength="2000">'.$description.'</textarea></label>';
    if (isset($errors['description'])) $html .= '<p class="error">'.e($errors['description']).'</p>';
    $html .= '<label>Status<select name="status">';
    foreach (['pending'=>'Pending','in_progress'=>'In progress','done'=>'Done'] as $value=>$label) {
        $html .= '<option value="'.$value.'"'.($value===$status?' selected':'').'>'.$label.'</option>';
    }
    $html .= '</select></label>';
    if (isset($errors['status'])) $html .= '<p class="error">'.e($errors['status']).'</p>';
    $html .= '<label>Due date<input type="date" name="due_date" value="'.$date.'"></label>';
    if (isset($errors['due_date'])) $html .= '<p class="error">'.e($errors['due_date']).'</p>';
    return $html;
}
function validateTask(): array {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $status = (string)($_POST['status'] ?? '');
    $date = (string)($_POST['due_date'] ?? '');
    $errors = [];
    if ($title==='' || strlen($title)>100) $errors['title']='A title of up to 100 characters is required.';
    if (strlen($description)>2000) $errors['description']='Description is too long.';
    if (!in_array($status,['pending','in_progress','done'],true)) $errors['status']='Choose a valid status.';
    if ($date !== '') {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) $errors['due_date']='Enter a valid date.';
    }
    return [['title'=>$title,'description'=>$description,'status'=>$status,'due_date'=>$date],$errors];
}
if ($method==='POST') {
    if (!hash_equals($_SESSION['tasks_csrf'], (string)($_POST['_token'] ?? ''))) errorPage(419,'Invalid form token. Refresh and try again.');
    if ($path==='/tasks') {
        [$values,$errors] = validateTask();
        if ($errors) render('Create Task','<form method="post" action="/tasks">'.csrfInput().formFields([], $errors).'<button>Save task</button></form>');
        $id = $_SESSION['tasks_next_id']++;
        $_SESSION['tasks_today'][$id] = $values + ['id'=>$id,'is_archived'=>false];
        $_SESSION['tasks_flash'] = 'Task created.';
        redirectTo('/tasks');
    }
    if (preg_match('#^/tasks/([1-9][0-9]*)/(edit|archive)$#D', $path, $match)) {
        $id = (int)$match[1];
        $task = taskById($id);
        if ($match[2]==='archive') {
            $_SESSION['tasks_today'][$id]['is_archived'] = true;
            $_SESSION['tasks_flash'] = 'Task archived.';
            redirectTo('/tasks');
        }
        [$values,$errors] = validateTask();
        if ($errors) render('Edit Task','<form method="post" action="/tasks/'.$id.'/edit">'.csrfInput().formFields($task,$errors).'<button>Update task</button></form>');
        $completed = $task['status']!=='done' && $values['status']==='done';
        $_SESSION['tasks_today'][$id] = array_replace($task,$values);
        if ($completed) {
            $_SESSION['tasks_notifications'][] = ['task_id'=>$id,'title'=>$values['title'],'time'=>date('Y-m-d H:i:s')];
        }
        $_SESSION['tasks_flash'] = $completed ? 'Task updated. Completion notification recorded.' : 'Task updated.';
        redirectTo('/tasks/'.$id);
    }
    errorPage(404,'Page not found.');
}
if ($path==='/') render('Tasks for Today','<p>Keep track of today’s work.</p><a class="button" href="/tasks">View tasks</a>');
if ($path==='/about') render('About','<p>Create, update, and archive tasks. A completion notification is recorded whenever a task changes to Done.</p><p>This temporary version stores tasks in your current browser session.</p>');
if ($path==='/profile') render('Profile','<p>Login and registration are disabled for this temporary version. Tasks are saved only for this browser session.</p>');
if ($path==='/notifications') {
    $body = '';
    foreach (array_reverse($_SESSION['tasks_notifications']) as $entry) {
        $body .= '<article><strong>Task Completed:</strong> '.e($entry['title']).' <small>('.e($entry['time']).')</small></article>';
    }
    render('Completion notifications',$body ?: '<p>No completed tasks yet.</p>');
}
if ($path==='/tasks/create') render('Create Task','<form method="post" action="/tasks">'.csrfInput().formFields().'<button>Save task</button></form>');
if ($path==='/tasks') {
    $body = '<a class="button" href="/tasks/create">Create Task</a> <a href="/notifications">View completion notifications</a>';
    foreach (array_reverse($_SESSION['tasks_today'],true) as $task) {
        if ($task['is_archived']) continue;
        $body .= '<article><h2><a href="/tasks/'.(int)$task['id'].'">'.e($task['title']).'</a></h2><p>Status: '.e(str_replace('_',' ',$task['status'])).'</p></article>';
    }
    render('Tasks',$body);
}
if (preg_match('#^/tasks/([1-9][0-9]*)(/edit)?$#D',$path,$match)) {
    $id = (int)$match[1];
    $task = taskById($id);
    if (($match[2] ?? '')==='/edit') render('Edit Task','<form method="post" action="/tasks/'.$id.'/edit">'.csrfInput().formFields($task).'<button>Update task</button></form>');
    $body = '<p>'.nl2br(e($task['description'])).'</p><p>Status: '.e(str_replace('_',' ',$task['status'])).'</p>';
    if ($task['due_date']!=='') $body .= '<p>Due: '.e($task['due_date']).'</p>';
    $body .= '<a class="button" href="/tasks/'.$id.'/edit">Edit</a> <form class="inline" method="post" action="/tasks/'.$id.'/archive" onsubmit="return confirm(\'Archive this task?\')">'.csrfInput().'<button>Archive</button></form>';
    render($task['title'],$body);
}
errorPage(404,'Page not found.');
