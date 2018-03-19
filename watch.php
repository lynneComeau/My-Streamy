<?php
/**
 * Created by PhpStorm.
 * User: Skaze
 * Date: 3/18/18
 * Time: 1:18 PM
 */

if (!isset($_COOKIE['9af8dbf2-2a75-11e8-b467-0ed5f89f718b'])) {
    header('Location: login.php');
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "<script>alert('Redirecting');</script>";
    header('Location: index.php');
}

$videoName = "";
$videoID = $_POST['id'];
$targetDir = "videos/";
$targetDir = "videos\\";
$pdo = new PDO('mysql:host=localhost;dbname=my_streamy', 'root', 'Canadien22');

$videoQuery = $pdo->prepare("select * from Video where ID = ?");
$videoQuery->execute([$videoID]);

$video = $videoQuery->fetch(PDO::FETCH_ASSOC);
$videoName = $video['Name'];

$videoFilePath = $targetDir . $video['FileName'];
$videoFileSize = (string)(filesize($videoFilePath));
?>

<html>
<head>
    <title>My Streamy</title>
</head>
<body>
<?php require('partials/header.php');?>
<div>
    <?php
    echo "<h1>$videoName</h1>";

    $width = 640;
    $height = 360;
    if (isset($_POST['res'])){
        $rezes = explode("x", $_POST['res']);
        $height = $rezes[1];
        $width = $rezes[0];
    }
    ?>
    <video width="<?php echo $width; ?>" height="<?php echo $height; ?>" controls controlsList="nodownload">
        <source src='<?php echo $videoFilePath; ?>' type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>
<form action="watch.php" method="post">
    <?php echo "<input type='hidden' value='" . $_POST['id'] . "' name='id'>"?>
    <select name="res" title="Resolution">
        <?php
        $resolutions = array("640x360 nHD", "768x432", "800x450", "896x504", "960x540 qHD", "1024x576", "1152x576", "1280x720 HD", "1366x768 WXGA", "1600x900 HD+", "1920x1080 Full HD", "2000x1125", "2048x1152", "2304x1296", "2560x1440 QHD", "2880x1620", "3200x1800 QHD+", "3200x1800", "3520x1980", "3840x2160 4K UHD", "4096x2304 Full 4K UHD", "4480x2520", "5120x2280 5K UHD", "5760x3240", "6400x3600", "7040x3960", "7680x4320 8K UHD");

        foreach ($resolutions as $res){
            $whitespaceCheck = strpos($res, " ");
            $option = "<option value=";

            $rawRes = "";

            if (!$whitespaceCheck) {
                $option .= $rawRes = $res;
            }
            else {
                $option .= $rawRes = substr($res, 0, $whitespaceCheck);
            }

            if (isset($_POST['res'])) {
                if ($_POST['res'] == $rawRes) {
                    $option .= " selected";
                }
            }

            $option .= ">$res</opinion>";
            echo $option;
        }
        ?>
        <input type="submit" value="Change">
    </select>
</form>
<?php require('partials/footer.php');?>
</body>
</html>
