<?php
$dataFile = 'users.json';

function loadUsers() {
    global $dataFile;
    if (file_exists($dataFile)) {
        $jsonData = file_get_contents($dataFile);   
        return json_decode($jsonData, true) ?? [];
    }
    return [];
}

function saveUsers($users) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($users));
}

// Handle form submission to add or edit a user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['email'], $_POST['place'], $_POST['dob'], $_POST['college'], $_POST['departments'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $place = htmlspecialchars($_POST['place']);
    $dob = htmlspecialchars($_POST['dob']);
    $college = htmlspecialchars($_POST['college']);
    $departments = implode(", ", $_POST['departments']);
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    $users = loadUsers();

    if ($id) {
        foreach ($users as &$user) {
            if ($user['id'] === $id) {
                $user['name'] = $name;
                $user['email'] = $email;
                $user['place'] = $place;
                $user['dob'] = $dob;
                $user['college'] = $college;
                $user['departments'] = $departments;
                break;
            }
        }
    } else {
        $newUser = [
            'id' => uniqid(),
            'name' => $name,
            'email' => $email,
            'place' => $place,
            'dob' => $dob,
            'college' => $college,
            'departments' => $departments
        ];
        $users[] = $newUser;
    }

    saveUsers($users);
}

// Handle AJAX delete operation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteId'])) {
    $idToDelete = $_POST['deleteId'];
    $users = loadUsers();

    $updatedUsers = array_filter($users, function ($user) use ($idToDelete) {
        return $user['id'] !== $idToDelete;
    });

    if (count($updatedUsers) !== count($users)) {
        saveUsers($updatedUsers);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }
    exit;
}

$users = loadUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Unique Input Styles</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="form-container">
    <form method="POST" id="userForm">
        <input type="hidden" name="id" id="userId">
        
        <label>Name:
            <input type="text" name="name" id="userName" required>
        </label><br>

        <label>Email:
            <input type="email" name="email" id="userEmail" required>
        </label><br>

        <label>Place:
            <select name="place" id="userPlace" required>
                <option value="">Select Place</option>
                <option value="New York">New York</option>
                <option value="Los Angeles">Los Angeles</option>
                <option value="Chicago">Chicago</option>
                <!-- Add more options as needed -->
            </select>
        </label><br>

        <label>Date of Birth:
            <input type="date" name="dob" id="userDob" required>
        </label><br>

        <label>College:
            <input type="text" name="college" id="userCollege" required>
        </label><br>

        <label>Departments:
            <label><input type="checkbox" name="departments[]" value="Science"> Science</label>
            <label><input type="checkbox" name="departments[]" value="Engineering"> Engineering</label>
            <label><input type="checkbox" name="departments[]" value="Arts"> Arts</label>
            <!-- Add more options as needed -->
        </label><br>

        <input type="submit" value="Save">
    </form>
</div>

<div class="users-container">
    <h3>User List:</h3>
    <div id="userData">
        <?php foreach ($users as $user): ?>
            <div class="user" data-id="<?= $user['id']; ?>">
                <strong>Name:</strong> <?= htmlspecialchars($user['name']); ?><br>
                <strong>Email:</strong> <?= htmlspecialchars($user['email']); ?><br>
                <strong>Place:</strong> <?= htmlspecialchars($user['place']); ?><br>
                <strong>Date of Birth:</strong> <?= htmlspecialchars($user['dob']); ?><br>
                <strong>College:</strong> <?= htmlspecialchars($user['college']); ?><br>
                <strong>Departments:</strong> <?= htmlspecialchars($user['departments']); ?><br>
                <button onclick="editUser('<?= $user['id']; ?>', '<?= htmlspecialchars($user['name']); ?>', '<?= htmlspecialchars($user['email']); ?>', '<?= htmlspecialchars($user['place']); ?>', '<?= htmlspecialchars($user['dob']); ?>', '<?= htmlspecialchars($user['college']); ?>', '<?= htmlspecialchars($user['departments']); ?>')">Edit</button>
                <button onclick="deleteUser('<?= $user['id']; ?>')">Delete</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Edit function
function editUser(id, name, email, place, dob, college, departments) {
    document.getElementById('userId').value = id;
    document.getElementById('userName').value = name;
    document.getElementById('userEmail').value = email;
    document.getElementById('userPlace').value = place;
    document.getElementById('userDob').value = dob;
    document.getElementById('userCollege').value = college;
    document.getElementById('userDepartments').value = departments;
}

// AJAX delete operation
function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    document.querySelector(`.user[data-id="${id}"]`).remove();
                } else {
                    alert('User not found.');
                }
            } else {
                alert('Error communicating with server.');
            }
        };

        xhr.send('deleteId=' + encodeURIComponent(id));
    }
}
</script>

</body>
</html>
