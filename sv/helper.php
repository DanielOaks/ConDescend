<?
    /* Convenience functions, mostly
    */
    
    
    /* string json_pretty_encode(string $json)
    **  Takes a json string/php object and
    **  outputs a prettier version, for
    **  human-readable json file output
    **
    **  modified from http://recursive-design.com/blog/2008/03/11/format-json-with-php/
    */
    function json_pretty_encode($json) {
    
        // this allows us to call this function with object directly
        if (gettype($json) == "string") {
            // all's good, no conversion required
        }
        else {
            $json = json_encode($json);
        }
        
        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '    ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {
            // puts a space between label and var
            // turns this:   'username':'Danneh',
            // into:         'username': 'Danneh',
            if (($prevChar == ':') and $outOfQuotes) {
                $result .= ' ';
            }

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
            
            // If this character is the end of an element, 
            // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }
            
            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element, 
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }
                
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            
            $prevChar = $char;
        }

        return $result;
    }
    
    /* indent handler, for html output
    **  I enjoy having my html look hand-coded.
    **  That includes having proper indentation.
    **  $indent_level keeps track of the current
    **  tab-level of the indentation, and
    **  $indent_single defines what a single tab
    **  level is printed as
    */
    $indent_single = '    ';
    $indent_level = 0;
    
    // returns the current html-indent
    function indent() {
        global $indent_single, $indent_level;
        
        $output = "";
        $i = 0;
        while ($i < $indent_level) {
            $output .= $indent_single;
            $i++;
        }
        
        return $output;
    }
?>