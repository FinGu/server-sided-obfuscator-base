<?php
include("api/c_api.php");
include("functions/functions.php");

if(!isset($_SESSION["logged_in"]))
    header("Location: index.php");

if(isset($_POST["submit"])){
    $file_type = strtolower(pathinfo($_FILES["file_to_upload"]["name"], PATHINFO_EXTENSION));
    if($file_type == "exe" || $file_type == "dll"){
        if($_FILES["file_to_upload"]["size"] <= 10485760) { //10 mb
            $ft = ($file_type == 'exe') ? '.exe' : '.dll';

            $file_new_location = "uploads/" . uniqid() . $ft;

            move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $file_new_location);

            $xml_file = create_temp_xml($file_new_location, $_POST["opts"]);

            obfuscate($file_new_location, $xml_file);
        }
        else{
            die("file size is too big ( more than 10mb )");
        }
    }
    else{
        die("not supported file type");
    }
}

?>
hello <?php echo $_SESSION["username"]; ?>
<br> <br>
<form method="post" enctype="multipart/form-data">
    options : <br>
    anti tamper : <input type="checkbox" name="opts[]" value="anti tamper"> <br>
    constants : <input type="checkbox" name="opts[]" value="constants"> <br>
    control flow : <input type="checkbox" name="opts[]" value="ctrl flow"> <br> <br>
    <!-- add more protections here if you want !-->

    select your file : (only exe and dll files are allowed) <br>
    <input type="file" name="file_to_upload"> <br> <br>
    <button type="submit" name="submit"> submit </button>
</form>

