<?
    include '../sv/helper.php';
    
    $paths = json_decode(file_get_contents("../sv/defaults/paths.json"), true);
    
    /* config file */
    if (file_exists('../'.$paths["config"])) {
        $config = json_decode(file_get_contents('../'.$paths["config"]), true);
        if ($config == null) {
            $config = array();
        }
    }
    else {
        $config = array();
    }
    
    function save_config() {
        global $paths, $config;
        
        $file = fopen('../'.$paths["config"], 'w') or die("Cannot open config file for writing");
        fwrite($file, json_pretty_encode($config));
        fclose($file);
    }
    
    
    /* Config base keys */
    if (!array_key_exists("db", $config)) {
        $config["db"] = array();
    }
    if (!array_key_exists("site", $config)) {
        $config["site"] = array();
    }
    
    
    /* POST data handling */
    
    // database type
    if (array_key_exists("db-type", $_POST)) {
        $config["db"]["type"] = $_POST["db-type"];
        
        if ($config["db"]["type"] == "sqlite") {
            $config["db"]["info"] = "not required";
            // required for simple cascading checks below
            //  ...funny how "not required" is required
        }
    }
    
    // database info
    if (array_key_exists("type", $config["db"])) {
        if ($config["db"]["type"] == 'mysql') {
            if (array_key_exists("db-host", $_POST) and
                array_key_exists("db-user", $_POST) and
                array_key_exists("db-pass", $_POST)
                ) {
                $config["db"]["info"] = array(
                    $_POST["db-host"],
                    $_POST["db-user"],
                    $_POST["db-pass"]
                );
            }
        }
    }
    
    // site url
    if (array_key_exists("site-url", $_POST)) {
        $config["site"]["url"] = $_POST["site-url"];
    }
    
    // site url
    if (array_key_exists("site-title", $_POST)) {
        $config["site"]["title"] = $_POST["site-title"];
    }
    
    
    save_config();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>ConDescend Install</title>
        
        <!-- Style -->
        <link rel="stylesheet" type="text/css" href="cl/css/clear.css" />
        <link rel="stylesheet" type="text/css" href="cl/fonts/deliciousr/stylesheet.css" />
        <link rel="stylesheet" type="text/css" href="cl/css/default.css" />
        
        <!-- Meta-Stuff -->
        <link rel="shortcut icon" href="cl/favicon.png" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div id="container">
            
            <div id="header">
                <a id="headera" href="."></a>
            </div>
            
            <div id="content">
                
                
                <div id="col-l">
<?php
    $indent_level = 5;
    
    echo indent() . "<h1>Installation: ";
    
    // following section follows a cascading if/else if structure
    //  so if a previous step is not completed, the next step will
    //  not be shown
    
    /* database selection */
    if (!array_key_exists("type", $config["db"])) {
        echo "Database Selection</h1>\n";
        
        echo indent() . "Please select a database type:<br />\n";
        echo indent() . "<form action=\"install.php\" method=\"post\">\n";
        $indent_level++;
        echo indent() . "<input type=\"radio\" name=\"db-type\" value=\"mysql\" />MySQL<br />\n";
        echo indent() . "<input type=\"radio\" name=\"db-type\" value=\"sqlite\" />SQLite<br />\n";
        
        echo indent() . "<input type=\"submit\" value=\"Continue\">\n";
        $indent_level--;
        echo indent() . "</form>\n";
    }
    
    /* database login details */
    else if (!array_key_exists("info", $config["db"])) {
        
        // mysql details
        if ($config["db"]["type"] == "mysql") {
            echo "MySQL Connection Info</h1>\n";
            
            echo indent() . "Please enter mysql connection details:<br />\n";
            echo indent() . "<form action=\"install.php\" method=\"post\">\n";
            $indent_level++;
            echo indent() . "Hostname: <input type=\"text\" name=\"db-host\" /><br />\n";
            echo indent() . "Username: <input type=\"text\" name=\"db-user\" /><br />\n";
            echo indent() . "Password: <input type=\"password\" name=\"db-pass\" /><br />\n";
            
            echo indent() . "<input type=\"submit\" value=\"Continue\">\n";
            $indent_level--;
            echo indent() . "</form>\n";
        }
    }
    
    /* site url */
    else if (!array_key_exists("url", $config["site"])) {
        echo "Site URL</h1>\n";
        
        echo indent() . "Please enter site's url<br />\n";
        echo indent() . "<form action=\"install.php\" method=\"post\">\n";
        $indent_level++;
        echo indent() . "Url: <input type=\"text\" name=\"site-url\" /><br />\n";
        
        echo indent() . "<input type=\"submit\" value=\"Continue\">\n";
        $indent_level--;
        echo indent() . "</form>\n";
    }
    
    /* site title */
    else if (!array_key_exists("title", $config["site"])) {
        echo "Site Title</h1>\n";
        
        echo indent() . "Please enter site's title<br />\n";
        echo indent() . "<form action=\"install.php\" method=\"post\">\n";
        $indent_level++;
        echo indent() . "Title: <input type=\"text\" name=\"site-title\" /><br />\n";
        
        echo indent() . "<input type=\"submit\" value=\"Continue\">\n";
        $indent_level--;
        echo indent() . "</form>\n";
    }
    
    /* finished */
    else {
        echo "Finished!</h1>\n";
        
        echo indent() . "You are now finished installation<br />\n";
    }
?>
                </div>
                
                
                <div id="col-r">
<?php
    $indent_level = 5;
    
    echo indent() . "<h3>Installation Progress</h3>\n";
    echo indent() . "<ul>\n";
    $indent_level++;
    
    
    // database type
    echo indent() . "<li>Database Type: ";
    if (array_key_exists("type", $config["db"])) {
        echo "<span class=\"install-finished\">";
        echo $config["db"]["type"];
        echo "</span>";
    }
    else {
        echo "<span class=\"install-notfinished\">";
        echo "not entered";
        echo "</span>";
    }
    echo "</li>\n";
    
    // database info
    echo indent() . "<li>Database Info: ";
    if (array_key_exists("type", $config["db"]) and ($config["db"]["type"] == 'sqlite')) {
        echo "<span class=\"install-finished\">";
        echo "not required";
        echo "</span>";
    }
    else if (array_key_exists("info", $config["db"])) {
        echo "<span class=\"install-finished\">";
        echo "entered";
        echo "</span>";
    }
    else {
        echo "<span class=\"install-notfinished\">";
        echo "not entered";
        echo "</span>";
    }
    echo "</li>\n";
    
    echo indent() . "<div class=\"col-r-header-space\"></div>\n";
    
    // site url
    echo indent() . "<li>Site Url: ";
    if (array_key_exists("url", $config["site"])) {
        echo "<span class=\"install-finished\">";
        echo "entered";
        echo "</span>";
    }
    else {
        echo "<span class=\"install-notfinished\">";
        echo "not entered";
        echo "</span>";
    }
    echo "</li>\n";
    
    // site title
    echo indent() . "<li>Site Title: ";
    if (array_key_exists("title", $config["site"])) {
        echo "<span class=\"install-finished\">";
        echo "entered";
        echo "</span>";
    }
    else {
        echo "<span class=\"install-notfinished\">";
        echo "not entered";
        echo "</span>";
    }
    echo "</li>\n";
    
    
    $indent_level--;
    echo indent() . "</ul>\n";
?>
                    <div class="col-r-header-space"></div>
                    
                    <h3>Credits</h3>
                    <ul>
                        <li><a href="http://www.famfamfam.com/lab/icons/silk/">Silk Icons</a></li>
                        <li><a href="http://www.exljbris.com/delicious.html">Delicious font</a></li>
                    </ul>
                </div>
                
                <div class="col-clear"></div>
            </div>
            
            <div id="footer">
                Written by <a href="http://danneh.net">Danneh</a> - <a href="https://github.com/Danneh/ConDescend">ConDescend Source Code</a>
            </div>
        </div>
    </body>
</html>