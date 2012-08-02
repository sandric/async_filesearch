<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sandric
 * Date: 23.11.11
 * Time: 16:29
 * To change this template use File | Settings | File Templates.
 */

const SEARCHING_FILE = "buf.txt";
const FOUND_FILE = "found.txt";


//function for reading last line of found files from file SEARCHING_FILE
function getCurrentFileSearching()
{

    $line = '';

    $f = fopen(SEARCHING_FILE, 'r');
    if(!is_resource($f))
        throw new Exception("Error: can't open file " . SEARCHING_FILE . "!");

    $cursor = -1;

    fseek($f, $cursor, SEEK_END);
    $char = fgetc($f);

    while ($char === "\n" || $char === "\r")
    {
        fseek($f, $cursor--, SEEK_END);
        $char = fgetc($f);
    }

    while ($char !== false && $char !== "\n" && $char !== "\r")
    {
        $line = $char . $line;
        fseek($f, $cursor--, SEEK_END);
        $char = fgetc($f);
    }

    fclose($f);

    return $line;
}

//function for reading array of found files from file FOUND_FILE
function getLastFound($lastFound)
{

    $f = fopen(FOUND_FILE, 'r');
    if(!is_resource($f))
        throw new Exception("Error: can't open file " . FOUND_FILE . "!");

    $matches = array();
    while( ($line = fgets($f)) !== false)
        $matches[] = $line;

    fclose($f);

    if(count($matches) < $lastFound)
        return false;
    else
        return $matches;
}

try
{
    $line = getCurrentFileSearching();

    $matches = getLastFound($_POST["lastFound"]); //comparing if there is no need to send found files via json

    $data["currentFileSearching"] = $line;
    $data["matches"] = $matches;
    $data["lastFound"] = count($matches);

    print json_encode($data); //sending encoded data back to html file
}
catch(Exception $e)
{
    print $e->getMessage();
}
?>