<?php

function obfuscate($obf_file_location, $proj_file_location){
    shell_exec(getcwd() . "\obfuscator\Confuser.CLI.exe " . $proj_file_location);

    $obf_file = realpath("uploads/obfuscated/" . pathinfo($obf_file_location)['basename']);

    unlink(realpath($obf_file_location));

    download_file($obf_file);

    unlink($obf_file);

    unlink($proj_file_location);
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
    header("Pragma: public");
    header("Expires: 0"); // set expiration time
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment; filename=".basename($filename).";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".filesize($filename));

    @readfile($filename);
}