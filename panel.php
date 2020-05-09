<?php
include("api/c_api.php");
include("functions/functions.php");

if(!isset($_SESSION["logged_in"]))
    header("Location: index.php");

if(isset($_POST["submit"])) {
    $file_type = strtolower(pathinfo($_FILES["file_to_upload"]["name"], PATHINFO_EXTENSION));
    if ($file_type == "exe" || $file_type == "dll" || $file_type == "zip") {
        if ($_FILES["file_to_upload"]["size"] <= 10485760) { //10 mb
            $file_new_location = "uploads/" . uniqid() . '.' . $file_type;

            move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $file_new_location);

            if ($file_type == 'zip') {
                $obfuscation_data = unpack_to_return_the_exe($file_new_location);

                $xml_file = create_temp_xml($obfuscation_data["file_to_obfuscate"], $_POST["opts"]);

                obfuscate($obfuscation_data["file_to_obfuscate"], $xml_file, $obfuscation_data["dependencies"]); //file to obfuscate ( in the zip file )

                foreach($obfuscation_data["dependencies"] as &$val)
                    unlink(realpath($val)); //delete the dependencies that was uploaded with the file

            } else {
                $xml_file = create_temp_xml($file_new_location, $_POST["opts"]);

                obfuscate($file_new_location, $xml_file);
            }
        } else {
            die("file size is too big ( more than 10mb )");
        }
    } else {
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

    select your file : (only zip, exe and dll files are allowed) <br>
    <input type="file" name="file_to_upload"> <br> <br>
    <button type="submit" name="submit"> submit </button>
</form>

