<?php

if(!function_exists('dd')){
    function dd($_vars){
        echo '<pre>';
        var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
        call_user_func_array('var_dump', func_get_args());
        exit;
    }
}