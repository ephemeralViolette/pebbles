<?php
require '../../app/vendor/autoload.php';
require '../../app/protected/hardcoded.php';
require '../../app/protected/config.inc.php';
require '../../app/protected/fetch.php';
require '../../app/protected/update.php';
require '../../app/protected/delete.php';
require '../../app/protected/insert.php';
require '../../app/protected/formatting.php';

$router = new \Bramus\Router\Router();
$formatter = new \Misc\Formatter\Formatter();
$fetch = new \Database\Fetch\Fetcher($__db);
$insert = new \Database\Insert\Inserter($__db);

//header("Content-type: application/json");

$request = (object) [
    "username" => $_SESSION['domainname'],
    "filename" => "",

    "error"    => (object) [
        "message" => ""
    ],
];

$target_dir = "../assets/img/";
$imageFileType = strtolower(pathinfo($_FILES["file_upload"]["name"],PATHINFO_EXTENSION));
$target_name = md5_file($_FILES["file_upload"]["tmp_name"]) . "." . $imageFileType;
$target_file = $target_dir . $target_name;

if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["file_upload"]["tmp_name"]);
    if($check == false) {
        $request->error->message = "File is not an image.";
    }
}

if (file_exists($target_file)) {
    $request->error->message = "Sorry, file already exists.";
}

if ($_FILES["file_upload"]["size"] > 10000000) {
    $request->error->message = "Sorry, your file is too large.";
}

if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    $request->error->message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
}

// $request->mime_type = image_type_to_mime_type(exif_imagetype($target_file));

if ($request->error->message == "") {
    if (!move_uploaded_file($_FILES["file_upload"]["tmp_name"], $target_file)) {
        $request->error->message = "Sorry, there was an error uploading your file.";
    } else {
        $stmt = $__db->prepare("INSERT INTO files (file_name, contents, belongs_to) VALUES (:file_name, :contents, :belongs_to)");
        $stmt->bindParam(":file_name", $target_name);
        $stmt->bindParam(":contents", $request->filename);
        // $stmt->bindParam(":mime_type", $request->mime_type);
        $stmt->bindParam(":belongs_to", $request->username);
        $stmt->execute();
    }
} else {
    $_SESSION['error'] = $request->error;
}

header("Location: /edit_site");