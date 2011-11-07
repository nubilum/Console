<?php

class Console {
    
    private static $conrolCode;
    
    private static $colorMap = array(
        'black'  => '30m',
        'red'    => '31m',
        'green'  => '32m',
        'yellow' => '33m',
        'blue'   => '34m',
        'purple' => '35m',
        'cyan'   => '36m',
        'white'  => '37m',
    );
    
    private static $bgColorMap = array(
        'black'  => '40m',
        'red'    => '41m',
        'green'  => '42m',
        'yellow' => '43m',
        'blue'   => '44m',
        'purple' => '45m',
        'cyan'   => '46m',
        'white'  => '47m',
    );
    
    /**
     * print text as html notation.
     * @param string $text
     */
    public static function printAsHTML( $text ) {
        /*
        ESC[x;yH	move to x,y
        ESC[2J	clear
        ESC[0J	line clear
        ESC[0m	normal mode
        ESC[1m	bold
        ESC[4m	underbar
        ESC[5m	blink
        ESC[7m	color reverse
         */
        $control = self::getControlCode(); 
        $result  = "";
        $lines   = explode("\n", $text);
        $tags    = array();
        foreach ( $lines as $line ) {
            $tmpLine = $line;
            $length  = mb_strlen($line);
            for ( $i = 0; $i < $length; $i++ ) {
                $result = array();
                if ( preg_match('/<([\/]*[a-zA-Z0-9\="\' ]+)>/', $tmpLine, $result, 0, $i) ) {
                    $code    = strtolower($result[1]);
                    $content = $code;
                    $offset  = mb_strpos($tmpLine, '<'.$code.'>', $i);
                    $i       = $offset - 1;
                    $colors  = "";
                    if ( $offset === false ) break;
                    //FONT?
                    if ( mb_strpos($code, 'font') === 0 ) {
                        if ( mb_strpos($code, 'bgcolor') !== false ) {
                            preg_match('/bgcolor\=["|\']([a-zA-Z]+)["|\']/', $code, $result2);
                            $bgKey    = $result2[1];
                            $color    =  isset(self::$bgColorMap[$bgKey]) ? self::$bgColorMap[$bgKey] : null;
                            if ($color) {
                                $color   = $control . '[' . $color;
                            }
                            $colors  = $color;
                            $code    = str_replace('bgcolor', '', $code);
                            $tags['font'][] = $color;
                        }
                        if ( mb_strpos($code, 'color') !== false ) {
                            preg_match('/color\=["|\']([a-zA-Z]+)["|\']/', $code, $result2);
                            $coKey   = $result2[1];
                            $color   =  isset(self::$colorMap[$coKey]) ? self::$colorMap[$coKey] : null;
                            if ($color) {
                                $color   = $control . '[' . $color;
                            }
                            $colors .= $color;
                            $tags['font'][] = $color;
                        }
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $colors . mb_substr($tmpLine, $offset + mb_strlen('<>' . $content));
                    }
                    //B?
                    else if ( $code === 'b' || $code === 'strong' ) {
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[1m' . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                        $tags['b'][] = $control . '[1m';
                    }
                    //U?
                    else if ( $code === 'u' ) {
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[4m' . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                        $tags['u'][] = $control . '[4m';
                    }
                    //BLINK?
                    else if ( $code === 'blink' ) {
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[5m' . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                        $tags['blink'][] = $control . '[5m';
                    }
                    //REVERSE? (No exists html tags for color reversal, it's be temporary tag)
                    else if ( $code === 'r' || $code === 'reverse' ) {
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[7m' . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                        $tags['reverse'][] = $control . '[7m';
                    }
                    //tag close.
                    else if ( $code === '/font' ) {
                        $next = "";
                        unset($tags['font']);
                        foreach ( $tags as $tag => $vals) {
                            foreach ( $vals as $val ) {
                                $next .= $val;
                            }
                        }
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[0m' . $next . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                    }
                    else if ( $code === '/b' || $code === '/strong' ) {
                        $next = "";
                        unset($tags['b']);
                        foreach ( $tags as $tag => $vals) {
                            foreach ( $vals as $val ) {
                                $next .= $val;
                            }
                        }
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[0m' . $next . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                    }
                    else if ( $code === '/u' ) {
                        $next = "";
                        unset($tags['u']);
                        foreach ( $tags as $tag => $vals) {
                            foreach ( $vals as $val ) {
                                $next .= $val;
                            }
                        }
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[0m' . $next . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                    }
                    else if ( $code === '/blink' ) {
                        $next = "";
                        unset($tags['blink']);
                        foreach ( $tags as $tag => $vals) {
                            foreach ( $vals as $val ) {
                                $next .= $val;
                            }
                        }
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[0m' . $next . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                    }
                    else if ( $code === '/r' || $code === '/reverse' ) {
                        $next = "";
                        unset($tags['reverse']);
                        foreach ( $tags as $tag => $vals) {
                            foreach ( $vals as $val ) {
                                $next .= $val;
                            }
                        }
                        $tmpLine = mb_substr($tmpLine, 0, $offset) . $control . '[0m' . $next . mb_substr($tmpLine, $offset + mb_strlen('<>' . $code));
                        $tmpLine = str_replace('<' . $code . '>', '', $tmpLine);
                    }
                    else {
                        $i = $offset + mb_strlen('<>' . $code) - 1;
                    }
                }
                $length  = mb_strlen($tmpLine);
            }
            $result = $tmpLine;
        }
        echo $result . $control . "[0m\n";
    }
    
    /**
     * display confirm message, and receive Y/N command.
     * @param string $message confirmText
     * @return boolean if user typed Y or Yes, return true.
     */
    public static function confirm($message) {
        //display confirm message.
        echo $message . '[Y/N]';
        
        $command = "";
        
        $stdin = fopen("php://stdin", "r");
        if ( !$stdin ) {
            exit("[error] STDIN failure.\n");
        }
        while (true) {
            $command = trim(fgets($stdin, 64));
            if ($command == '') continue;
            break;
        }
        fclose($stdin);
        
        $command = strtolower($command);
        return $command === 'yes' || $command === 'y';
    }
    
    /**
     * print line message, by specified color and background color.
     * @param string $message    echo text.
     * @param string $colorId    using $colorMap key. 
     * @param string $bgColorId  using $bgColorMap key.
     */
    public static function println( $message, $colorId = null, $bgColorId = null ) {
        //prepare.
        $code    = self :: getControlCode();
        $color   = isset(self :: $colorMap[$colorId])     ? self :: $colorMap[$colorId]     : null;
        $bgColor = isset(self :: $bgColorMap[$bgColorId]) ? self :: $bgColorMap[$bgColorId] : null;
        
        //create color code.
        if ( $color ) {
            $color   = $code . "[" . $color;
        }
        if ( $bgColor ) {
            $bgColor = $code . "[" . $bgColor;
        }
        
        //print.
        echo $bgColor . $color . $message . $code . '[0m' . "\n";
    }
    
    /**
     * get ESC control code.
     * @return string  escape sequence .
     */
    private static function getControlCode() {
        if ( self :: $conrolCode === null ) {
            self :: $conrolCode = pack('c', 0x1B);
        }
        return self:: $conrolCode;
    }
    
}
