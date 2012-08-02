<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sandric
 * Date: 23.11.11
 * Time: 06:05
 * To change this template use File | Settings | File Templates.
 */

    const SEARCHING_FILE = "buf.txt";
    const FOUND_FILE = "found.txt";
    const SLEEPTIME = 50000;

    //main search function
    //taking as params path to starting directory, search string pattern, and bool variables for file, text and subdirectories search
    function search($path, $searchString, $bFileSearch, $bTextSearch, $bSubDirectories, $bDynamicSearch = null)
    {

        $matches = array();
        $dHandle = opendir($path);  //opening base dir
        if(!is_resource($dHandle))
            throw new Exception("Error: can't open directory $path!");

        while( false !== ($fName = readdir($dHandle)) )
        {

            if($fName != "." && $fName != "..") //script was made under linux, this may not be need in other OSes
            {

                if($bDynamicSearch)
                {

                    usleep(SLEEPTIME);  // this is only for seeing how script works, to show the differences in searching files in html

                    $hSearching = fopen(SEARCHING_FILE, "a");
                    if(!is_resource($hSearching))
                        throw new Exception("Error: can't open file " . SEARCHING_FILE . "!");

                    if(!fwrite($hSearching, "$path/$fName\n"))  //opening and writing current searching file to buffer file (for it could be readen from searchData2JSON.php)
                        throw new Exception("Error: can't write to file $path/$fName!");


                    fclose($hSearching);
                }

                if(is_dir("$path/$fName"))
                {
                    if($bSubDirectories)    //recursively searching in subdirectories, if asked
                        $matches += search("$path/$fName", $searchString, $bFileSearch, $bTextSearch, $bSubDirectories, $bDynamicSearch);
                }
                else
                {

                    if($bFileSearch)
                    {

                        if(preg_match("/^$searchString$/", $fName, $fileMatches) === 1) //searching in file name
                        {

                            $matches["$path/$fName"] = $fName;

                            if($bDynamicSearch)
                            {

                                $hFound = fopen(FOUND_FILE, "a");
                                if(!is_resource($hFound))
                                    throw new Exception("Error: can't open file " . FOUND_FILE . "!");

                                if(!fwrite($hFound, "$path/$fName\n"))  //and if success, write path to file to found buffer file
                                    throw new Exception("Error: can't write to file $path/$fName!");

                                fclose($hFound);
                            }
                        }
                    }
                    if($bTextSearch && !isset($matches["$path/$fName"]))    //searching in file text, if no result in file searching step before
                    {

                        if(filesize("$path/$fName") > 0 && filesize("$path/$fName") < 4000000)  //I got some strange error due to no differences in linux in text and binary files, so its prevent such bugs
                        {
                            $hFile = fopen("$path/$fName", "r");
                            if(!is_resource($hFile))
                                throw new Exception("Error: can't open file $path/$fName!");

                            if(false === ($fileText = fread($hFile, filesize("$path/$fName")))) //reading searching file
                                throw new Exception("Error: can't read file $path/$fName!");

                            if(preg_match("/$searchString/", $fileText, $fileMatches) === 1)    //searching in content
                            {
                                $matches["$path/$fName"] = $fName;

                                if($bDynamicSearch)
                                {
                                    
                                    $hFound = fopen(FOUND_FILE, "a");
                                    if(!is_resource($hFound))
                                        throw new Exception("Error: can't open file " . FOUND_FILE . "!");

                                    if(!fwrite($hFound, "$path/$fName\n"))  //trying to write result to FOUND_FILES buffer
                                        throw new Exception("Error: can't write to file $path/$fName!");

                                    fclose($hFound);
                                }
                            }

                            fclose($hFile);
                        }
                    }
                }
            }
        }

        closedir($dHandle);

        return $matches;
    }

try
{
    
    $fh = fopen( SEARCHING_FILE, 'w' ); //truncating current file and found files buffers
    if(!is_resource($fh))
        throw new Exception("Error: can't open file " . SEARCHING_FILE . "!");
    fclose($fh);

    $fh = fopen( FOUND_FILE, 'w' );
    if(!is_resource($fh))
        throw new Exception("Error: can't open file " . FOUND_FILE . "!");
    fclose($fh);

    $bFileNameSearch = false;
    $bTextNameSearch = false;
    $bSubDirectories = false;

    if(isset($_POST["fileNameSearch"]))
        $bFileNameSearch = true;
    if(isset($_POST["fileTextSearch"]))
        $bTextNameSearch = true;
    if(isset($_POST["subDirectoriesSearch"]))
        $bSubDirectories = true;

    if(!$bFileNameSearch && !$bTextNameSearch)
        throw new Exception("Error: need to select at least one search!");
    $stringPattern = str_replace(array("\\*", "\\?"), array(".*", ".?"), quotemeta($_POST["stringPattern"]));   //clearing (as possible) input string search patters
    $files = search(".", $stringPattern, $bFileNameSearch, $bTextNameSearch, $bSubDirectories, true);

    /*!!!if you dont want all this dynamic stuff, just comment 4 upper lines of code, and uncomment 4 below until try block ends

    $files = search(".", "ololo", true, true, true);
    print "<pre>";
        print_r($files);
    print "</pre>";
    */
}
catch(Exception $e)
{
    print $e;
}
?>