<?php

  //LINE VALUES
  define("COMPARE_TO_REDIRECT","Redirect");
  define("REDIRECT_TYPE", "301");

  //URL CHECKS
  define("SCHEME_CHECK","https");
  define("HOST_CHECK","www.imforza.com");

  //FILE NAMES
  define("IN_FILE", "input.txt");
  define("ERR_FILE", "error.txt");
  define("NEW_FILE", "redirects.txt");

  //EXAMPLES
  define("BROKEN_LINK_EXAMPLE","/broken/link/");
  define("NEW_PATH_EXAMPLE", "/new/link/path/");
  define("NEW_LINK_EXAMPLE", SCHEME_CHECK . "://" . HOST_CHECK . NEW_PATH_EXAMPLE);

  //Error log values
  $line_num = 1;
  $num_errors = 0;
  $error_log = "";

  //URL values
  $scheme = "https";
  $host = "www.domain.com";
  $path = "/page/subpage";

  //New URL values
  $n_scheme = null;
  $n_host = null;
  $n_path = null;

  //Line values
  $compare_redirect = "Redirect";
  $redirect_type = 301;
  $broken_link = "/broken/link";
  $new_link = "https://newlink.com/newlink/";

  //New Line values
  $n_compare_redirect = null;
  $n_redirect_type = null;
  $n_broken_link = null;
  $n_new_link = null;

  echo "\n///////////////////////////Running Redirect Checker/////////////////////////////\n\n";

  // Open file to read
  $infile = fopen(IN_FILE, "r") or die("Unable to open: " . IN_FILE);

  // Open file to write errors to
  $errorfile = fopen(ERR_FILE, "w") or die("Unable to open: " . ERR_FILE);

  // Open file to write redirects to
  $newfile = fopen(NEW_FILE, "w" ) or die("Unable to open: " . NEW_FILE);

  //Look for the format the redirect should be in
  //| => OR
  //\R => return
  //\S => not empty space

  $regex = "/(?:|\R)(\S+)\s+([0-9]+)\s+(\S+)\s+(\S+)(?:$|\R)/";

  //Check for end of file
  //For Every Line
  while(!feof($infile)){
    $line = fgets($infile);
    //echo $line;
    if( preg_match($regex, $line, $matches)){
      $compare_redirect = $matches[1];

      // Check redirect
      if(COMPARE_TO_REDIRECT != $compare_redirect){
        $error_log .= log_line($line_num, "Redirect Misspelled: " ,COMPARE_TO_REDIRECT,$compare_redirect,$num_errors);
      }
      $n_compare_redirect = COMPARE_TO_REDIRECT;
      $redirect_type = $matches[2];

      // Check redirect type
      if(REDIRECT_TYPE!=$redirect_type){
        $error_log .= log_line($line_num,"Incorrect Redirect Type: ",REDIRECT_TYPE,$redirect_type,$num_errors);
      }
      $n_redirect_type = REDIRECT_TYPE;

      $broken_link = $matches[3];

      // Check broken link
      if("/" != substr($broken_link,0,1)){
        $error_log .= log_line($line_num,"Missing Opening Slash: ","/".$broken_link,$broken_link,$num_errors);
        $n_broken_link = "/" . $broken_link;
      }
      else{
        $n_broken_link = $broken_link;
      }
      if("/" != substr($n_broken_link,strlen($n_broken_link)-1,1)){
        $error_log .= log_line($line_num,"Missing Trailing Slash: ",$n_broken_link."/",$n_broken_link,$num_errors);
        $n_broken_link .= "/";
      }

      // Check new link
      $new_link = $matches[4];
      $scheme = parse_url($new_link, PHP_URL_SCHEME);
      $host = parse_url($new_link, PHP_URL_HOST);
      $path = parse_url($new_link, PHP_URL_PATH);

      if(SCHEME_CHECK != $scheme){
        $error_log .= log_line($line_num,"Incorrect Scheme: ",SCHEME_CHECK,$scheme,$num_errors);
      }
      $n_scheme = SCHEME_CHECK;

      if(HOST_CHECK != $host){
        $error_log .= log_line($line_num,"Incorrect Host: ",HOST_CHECK,$host,$num_errors);
        //$path = substr($host,strlen(HOST_CHECK)-1,strlen($host)) . $path;
      }
      $n_host = HOST_CHECK;
      if("/" != substr($path,0,1)){
        $n_path = "/" . $path;
        $error_log .= log_line($line_num,"Missing Opening Slash: ",$n_path,$path,$num_errors);

      }
      else{
        $n_path = $path;
      }
      if("/" != substr($n_path,strlen($n_path)-1,1)){
        $n_path .= "/";
        $error_log .= log_line($line_num,"Missing Closing Slash: ",$n_path,$path,$num_errors);

      }


      $n_new_link = $n_scheme . "://" . $n_host . $n_path;

      fwrite($newfile, $n_compare_redirect . " ");
      fwrite($newfile, $n_redirect_type . " ");
      fwrite($newfile, $n_broken_link . " ");
      fwrite($newfile, $n_new_link . "\n");

    }
    //If the line is nit formatted properly
    else{
      //For last line
      if("" != $line){
        // What if there's a missing space
        // what if something else that we can fix
        $n_line = trim($line);
        $error_log .= log_line($line_num,"Line Improperly Formatted: ",COMPARE_TO_REDIRECT . " " . REDIRECT_TYPE . " " . BROKEN_LINK_EXAMPLE . " " . NEW_LINK_EXAMPLE,$n_line,$num_errors);
      }
    }

    //Next line
    $line_num++;
  }

  //Print error information
  fwrite($errorfile, "Number of Errors:" . $num_errors . "\n");
  fwrite($errorfile, "Number of Lines:" . ($line_num-2) . "\n");
  fwrite($errorfile, $error_log);

  //Close files
  fclose($infile);
  fclose($errorfile);
  fclose($newfile);

  //Print ending statement to console
  echo "\nPlease take a look at " . ERR_FILE . " and " . NEW_FILE . " for the results of the Redirect Check\n";

  //Returns a string that can be used for error logging
  function log_line($line_number, $err_mess ,$expect, $found, &$num_err){
    $num_err++;
    return "Error:Line:" . $line_number . "\n\t" . $err_mess . "Expected: " . $expect . " Received: " . $found . "\n";
  }

?>
