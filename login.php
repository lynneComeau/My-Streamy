<?php
/**
 * Created by PhpStorm.
 * User: Skaze
 * Date: 3/14/18
 * Time: 6:44 PM
 */

function GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

if(isset($_COOKIE['9af8dbf2-2a75-11e8-b467-0ed5f89f718b'])) {
    header('Location: index.php');
}

$username = $password = "";
$usernameErr = $passwordErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['login']) || isset($_POST['create']))){
    if (empty($_POST['username'])) {
        $usernameErr = "Please enter a username";
    } else {
        $username = test_input($_POST['username']);
    }

    if (empty($_POST['password'])) {
        $passwordErr = "Please enter a password";
    } else {
        $password = test_input($_POST['password']);
    }

    print_r($_POST);

    if (isset($username) && isset($password)) {
        $pdo = new PDO('mysql:host=localhost;dbname=my_streamy', 'root', 'Canadien22');
        if (isset($_POST['login'])) {
            $userQuery = $pdo->prepare("select * from USER where Username = ?");
            $userQuery -> execute([$username]);
            $user = $userQuery->fetch(PDO::FETCH_ASSOC);

            print($username);
            print($password);
            print_r($user);

            if(isset($user)) {
                print($user['Password']);
                if(!password_verify($password, $user['Password'])) {
                    $usernameErr = "The username and password do not match up";
                }
                else {
                    setcookie('9af8dbf2-2a75-11e8-b467-0ed5f89f718b', $user["GUID"]);
                    header('Location: index.php');
                }
            }
        } else {
            echo "Sorry, creating accounts is not allowed at the moment.";

            // I wrote the functionality to add new users, but I don't want
            // anyone browsing the web to make an account, I don't have the space.
            /* $userGuid = GUID();
             * $pdo->prepare("insert into User (Username, Password, GUID) VALUES (?, ?, ?)")->execute([$username, $password, $userGuid]);
             */

        }
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<html>
<head>
    <title>My Streamy</title>
</head>
<body>
<?php require('partials/header.php');?>
<div>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <label for="username">Username:</label><br/>
        <input type="text" id="username" name="username" value="<?php echo $username;?>">
        <span class="error"> <?php echo $usernameErr;?></span><br/>
        <label for="password">Password</label><br/>
        <input type="password" name="password" id="password">
        <span class="error"> <?php echo $passwordErr;?></span><br/>
        <input type="submit" value="Login" name="login">
        <input type="button" value="Create Account" name="create">
    </form>
</div>
<?php require('partials/footer.php');?>
</body>
</html>
