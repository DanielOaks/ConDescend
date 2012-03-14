<?php
    include 'helper.php';
    $paths = json_decode(file_get_contents("defaults/paths.json"), true);
    
    if (file_exists('../'.$paths["config"])) {
        $config = json_decode(file_get_contents('../'.$paths["config"]), true);
        if ($config == null) {
            $config = array();
        }
    }
    else {
        $config = array();
    }
    
    
    class database
    {
        /* if array exists, return true */
        public static function exists()
        {
            global $config;
            
            if (array_key_exists("db", $config) and
                array_key_exists("type", $config["db"])) {
                
                if ($config["db"]["type"] == "json") {
                    if (file_exists("databases/users.sqlite") and
                        file_exists("databases/page-items.sqlite")) {
                        return true;
                    }
                    else {
                        return false;
                    }
                }
            }
        }
        
        public static function initialise()
        {
            
        }
    }
?>