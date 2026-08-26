<?php
// BDMovieHub - Admin Users Manager
require_once __DIR__ . '/../config.php';
requireAdmin(); // Admin-only: user & role management
$adminPage = 'users';
$pageTitle = 'Users';

$users = getData(FILE_USERS);
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'add') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role     = isset($_POST['role']) ? $_POST['role'] : 'editor';
        if ($username === '' || $password === '') {
            $errors[] = 'Username and password are required.';
        } else {
            // Check uniqueness
            foreach ($users as $u) {
                if ((isset($u['username']) ? $u['username'] : '') === $username) {
                    $errors[] = 'Username already exists.';
                    break;
                }
            }
        }
        if (empty($errors)) {
            $newUser = array(
                'id'         => generateId($users, 'u'),
                'username'   => $username,
                'password'   => hashPassword($password), // bcrypt, never plaintext
                'role'       => $role,
                'created_at' => date('Y-m-d'),
            );
            $users[] = $newUser;
            saveData(FILE_USERS, $users);
            setFlash('success', 'User added.');
            adminRedirect('users.php');
        }
    } elseif ($action === 'update') {
        $uid      = isset($_POST['id']) ? $_POST['id'] : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role     = isset($_POST['role']) ? $_POST['role'] : 'editor';
        foreach ($users as &$u) {
            if ($u['id'] === $uid) {
                $u['username'] = $username;
                if ($password !== '') { $u['password'] = hashPassword($password); } // bcrypt on change
                $u['role'] = $role;
                break;
            }
        }
        unset($u);
        saveData(FILE_USERS, $users);
        setFlash('success', 'User updated.');
        adminRedirect('users.php');
    } elseif ($action === 'delete') {
        $del_id = isset($_POST['id']) ? $_POST['id'] : '';
        $current = currentUser();
        // Prevent self-delete and prevent deleting last admin
        if ($current && $current['id'] === $del_id) {
            setFlash('error', 'You cannot delete your own account.');
        } else {
            $admins = 0;
            foreach ($users as $u) {
                if (isset($u['role']) && $u['role'] === 'admin') { $admins++; }
            }
            $target = getById($users, $del_id);
            if ($target && $target['role'] === 'admin' && $admins <= 1) {
                setFlash('error', 'Cannot delete the last admin user.');
            } else {
                $newList = array();
                foreach ($users as $u) {
                    if ($u['id'] === $del_id) { continue; }
                    $newList[] = $u;
                }
                saveData(FILE_USERS, $newList);
                setFlash('success', 'User deleted.');
            }
        }
        adminRedirect('users.php');
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">Add New User</h2>

    <?php if (!empty($errors)): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?php e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php e($adminUrl); ?>/users.php">
        <?php echo csrfField(); ?>
        <input type="hidden" name="action" value="add">
        <div class="form-row-3">
            <div class="form-group">
                <label>Username <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="admin">Admin</option>
                    <option value="editor" selected>Editor</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add User</button>
    </form>
</div>

<div class="admin-card">
    <h2 style="font-size:20px; margin-bottom:20px;">All Users (<?php echo count($users); ?>)</h2>
    <div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <strong><?php e($u['username']); ?></strong>
                    <?php $cur = currentUser(); ?>
                    <?php if ($cur && $cur['id'] === $u['id']): ?>
                        <span style="margin-left:6px; padding:1px 6px; background:rgba(70,154,255,0.15); color:#469AFF; font-size:10px; border-radius:3px;">YOU</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="padding:2px 8px; border-radius:4px; font-size:11px; background:<?php echo $u['role'] === 'admin' ? 'rgba(231,76,60,0.15)' : 'rgba(70,154,255,0.15)'; ?>; color:<?php echo $u['role'] === 'admin' ? '#e74c3c' : '#469AFF'; ?>;">
                        <?php e(ucfirst($u['role'])); ?>
                    </span>
                </td>
                <td><?php e(isset($u['created_at']) ? $u['created_at'] : '-'); ?></td>
                <td>
                    <form method="POST" action="<?php e($adminUrl); ?>/users.php" style="display:inline-flex; gap:6px; align-items:center;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php e($u['id']); ?>">
                        <input type="text" name="username" value="<?php e($u['username']); ?>" style="background:#0a0a0f; border:1px solid #2a2a3e; color:#fff; padding:5px 8px; border-radius:4px; font-size:12px; width:100px;">
                        <input type="text" name="password" placeholder="new pass" style="background:#0a0a0f; border:1px solid #2a2a3e; color:#fff; padding:5px 8px; border-radius:4px; font-size:12px; width:90px;">
                        <select name="role" style="background:#0a0a0f; border:1px solid #2a2a3e; color:#fff; padding:5px 8px; border-radius:4px; font-size:12px;">
                            <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="editor" <?php echo $u['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                        </select>
                        <button type="submit" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-save"></i></button>
                    </form>
                    <form method="POST" action="<?php e($adminUrl); ?>/users.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php e($u['id']); ?>">
                        <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm" data-confirm="Delete user '<?php e($u['username']); ?>'?"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
