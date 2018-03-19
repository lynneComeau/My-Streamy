<?php
/**
 * Created by PhpStorm.
 * User: Skaze
 * Date: 2/20/18
 * Time: 9:53 AM
 */

if (!isset($_COOKIE['9af8dbf2-2a75-11e8-b467-0ed5f89f718b'])) {
    header('Location: login.php');
}

$fileMessage = "";

$pdo = new PDO('mysql:host=localhost;dbname=my_streamy', 'root', 'Canadien22');

$userQuery = $pdo->prepare("select * from USER where GUID = ?");
$userQuery -> execute([$_COOKIE['9af8dbf2-2a75-11e8-b467-0ed5f89f718b']]);

$user = $userQuery->fetch(PDO::FETCH_ASSOC);

if (!isset($user)){
    header('Location: login.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    print_r($_POST);
    if (isset($_POST['delete'])) {
        $videoID = $_POST['id'];

        $videoQuery = $pdo->prepare("select * from Video where ID = ?");
        $videoQuery->execute([$videoID]);

        $video = $videoQuery->fetch(PDO::FETCH_ASSOC);
        $videoFileName = $video['FileName'];

        $targetDir = "videos/";
        $targetDir = "videos\\";

        $filePath = $targetDir . $videoFileName;

        print_r($filePath);

        unlink($filePath);

        $deleteVideo = $pdo->prepare("Delete from Video where ID = ?");
        $deleteVideo->execute([$videoID]);
    }
    else if (isset($_POST['file'])) {
        echo 1;
        $mime = $_FILES['fileToUpload']['type'];

        if (strstr($mime, "video/")) {
            echo 2;
            $targetDir = "videos/";
            $targetDir = "videos\\";
            $targetFile = $targetDir . $user["GUID"] . "." . basename($_FILES["fileToUpload"]["name"]);

            if (file_exists($targetFile)) {
                echo 7;
                $fileMessage = "File already exists";
            }
            // If file is larger than 50 mbs
            else if ($_FILES["fileToUpload"]["size"] > 50000000) {
                echo 8;
                $fileMessage = "File is too large, 50MB limit";
            }

            if ($fileMessage == "") {
                echo 3;
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
                    echo 4;
                    $fileMessage = "The file has been uploaded.";
                    $videoToInsert = $pdo->prepare("insert into Video (User_ID, FileName, Name) VALUES (?, ?, ?)");
                    $videoName = "";

                    if (!empty($_POST['filename'])) {
                        echo 5;
                        $videoName = $_POST['filename'];
                    }
                    else {
                        echo 6;
                        $filename = explode(".", $_FILES['fileToUpload']['name']);
                        $sliced = array_slice($filename, 0, -1);
                        $videoName = implode(".", $sliced);
                    }

                    $videoToInsert->execute([$user['ID'], $user["GUID"] . "." . basename($_FILES["fileToUpload"]["name"]), $videoName]);
                } else {
                    $fileMessage = "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            $fileMessage = "Uploaded file was not a video";
        }
    }
}

$videoQuery = $pdo->prepare("select * from Video where User_ID = ?");
$videoQuery->execute([$user['ID']]);

$videoList = array();

if ($videoQuery) {
    $videoList = $videoQuery->fetchAll(PDO::FETCH_ASSOC);
}
?>

<html>
<head>
    <title>My Streamy</title>
    <style>
        #fileSubmitButton {
            margin-top: 5px;
        }
        .parent {
            overflow: hidden;
        }
        .file, .delete {
            float: left;
        }
        .delete {
            margin-left: 25px;
        }
        /* Grabbed from somewhere else */
        .submit {
            background: none !important;
            border: none;
            padding: 0 !important;
            /*optional*/
            font-family: arial, sans-serif;
            /*input has OS specific font-family*/
            color: #069;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php require('partials/header.php');?>
<div>
    <?php
    $fileUpload = "<h3>Upload File</h3><form action='index.php' method='post' enctype='multipart/form-data'>";
    $fileUpload .= sprintf("<input type='hidden' name='id' value='%s'>", $user['ID']);
    $fileUpload .= "<label for='filename'>Filename:</label><br/>";
    $fileUpload .= "<input type='text' name='filename' id='filename'><br/>";
    $fileUpload .= "<input type='file' name='fileToUpload'>";
    $fileUpload .= "<span>$fileMessage</span>";
    $fileUpload .= "<br/>";
    $fileUpload .= "<input type='submit' value='Upload' name='file' id='fileSubmitButton'>";
    $fileUpload .= "</form>";

    echo $fileUpload;

    if (empty($videoList)) {
        echo "You have no videos uploaded";
    } else {
        echo "<h3>Videos uploaded</h3>";
        echo "<ul>";

        foreach ($videoList as $video) {
            $listItem = "<li><div class='parent'><div class='file'><form action='watch.php' method='post'>";
            $listItem .= sprintf("<input type='hidden' name='id' value='%s'>", $video['ID']);
            $listItem .= sprintf("<input type='submit' class='submit' name='watch' value='%s'>", $video['Name']);
            $listItem .= "</form></div><span>     </span>";
            $listItem .= "<div class='delete'><form action='index.php' method='post'>";
            $listItem .= sprintf("<input type='hidden' name='id' value='%s'>", $video['ID']);
            $listItem .= "<input type='submit' name='delete' value='Delete'>";
            $listItem .= "</form></div></div></li>";
            echo $listItem;
        }

        echo "</ul>";
    }
    ?>
</div>
<?php require('partials/footer.php');?>
</body>
</html>
