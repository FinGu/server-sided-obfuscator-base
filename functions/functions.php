<?php

function obfuscate($obf_file_location, $proj_file_location, $dependencies = array())
{
    //here i perform the obfuscation task and save the output of cfex's console to '$data' ⬇
    $data = shell_exec(getcwd() . "\obfuscator\Confuser.CLI.exe " . escapeshellarg($proj_file_location));

    $obf_file = realpath("uploads/obfuscated/" . pathinfo($obf_file_location)['basename']);

    unlink(realpath($obf_file_location));

    $zip_file = pack_assembly_with_info($obf_file, $data, $dependencies);
    //pack the obf file + dependencies (if there are) with the cfex console output

    download_file($zip_file);

    unlink($obf_file);

    unlink(realpath($zip_file));

    unlink($proj_file_location);
}

function pack_assembly_with_info($exe_file, $obf_info, $dependencies = array()){
    $zip = new ZipArchive;

    if(!empty(pathinfo($exe_file)['filename'])) //this is in case the obfuscation wasnt successfull
        $zip_output = "uploads/obfuscated/" . pathinfo($exe_file)['filename'] . '.zip';
    else
        $zip_output = "uploads/obfuscated/" . uniqid() . '.zip';

    $zip->open($zip_output, ZipArchive::CREATE); //creates a zip file with the exe's name.zip

    $zip->addFile($exe_file, pathinfo($exe_file)['basename']); //add the real exe

    $zip->addFromString("obf_info.txt", $obf_info); //add the cfex output here

    if(!empty($dependencies)) //if there are dependencies
        foreach($dependencies as &$deps) //for each dependencies in the dependency array
            $zip->addFile($deps, pathinfo($deps)['basename']); //add the dependency to the zip file

    $zip->close();

    return $zip_output;
}

function unpack_to_return_the_exe($zip_location){ //CAN ONLY CONTAINS ONE EXE
    $zip = new ZipArchive;
    $zip->open(realpath($zip_location)); //opens the zip file

    $result_array = array("file_to_obfuscate" => '', "dependencies" => array());
    //defines the array return ^

    for ($i = 0; $i < $zip->numFiles; $i++) { //get all the files in the zip file
        $filename = $zip->getNameIndex($i); //get the name of the current file
        $info = pathinfo($filename); //path info of the file

        if($info["extension"] == "exe"){ //in case the file is an exe, gen a random name to it and save it and link the name to the array return
            $file_to_obfuscate = "uploads/" . uniqid() . '.' . $info["extension"];
            fwrite(fopen($file_to_obfuscate, "w"), $zip->getFromName($filename));
            $result_array["file_to_obfuscate"] = $file_to_obfuscate;
        }
        else if($info["extension"] == "dll"){ //add data foreach dll dependency
            $dll_dependency = "uploads/" . $filename;
            fwrite(fopen($dll_dependency, "w"), $zip->getFromName($filename));

            array_push($result_array["dependencies"], $dll_dependency);
        }
        else {
            $zip->close();

            foreach(@$result_array["dependencies"] as &$val)
                @unlink(realpath($val));
            //im not sure if the arrays are really defined, so i use @ to not throw exceptions

            @unlink($zip_location);
            @unlink($result_array["file_to_obfuscate"]);
            @unlink("uploads/" . $filename);

            die("there are files that arent .exe/.dll in the zip");
        }
    }
    $zip->close();
    unlink($zip_location);

    return $result_array;
}

//creates a xml project file to be used with confuser ex cli
function create_temp_xml($file_path, $protection_options){
    $path_info = pathinfo($file_path);
    $dir_name = realpath($path_info["dirname"]);

    $xml = new SimpleXMLElement("<project></project>");

    $xml->addAttribute("outputDir", $dir_name . "\obfuscated");
    $xml->addAttribute("baseDir", $dir_name);
    $xml->addAttribute("xmlns", "http://confuser.codeplex.com"); // <- i dont think thats needed

    $rules = $xml->addChild("rule");
    $rules->addAttribute("pattern", "true");
    $rules->addAttribute("inherit", "false");

    foreach ($protection_options as &$opt) {
        $protections = $rules->addChild("protection");
        $protections->addAttribute("id", $opt);
    }

    $mdl = $xml->addChild("module");
    $mdl->addAttribute("path", $path_info["basename"]);

    $xml_output_path = "projects/" . uniqid() . ".crproj";

    $output = fopen($xml_output_path, "w");
    fwrite($output, explode("\n", $xml->asXML(), 2)[1]);
    fclose($output);

    return realpath($xml_output_path);
}

function download_file($filename){
    header("Content-Type: application/octet-stream");
    header("Content-Transfer-Encoding: Binary");
    header("Pragma: public");
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-Disposition: attachment; filename=".basename($filename).";");
    header("Content-Length: ".filesize($filename));

    @readfile($filename);
}
