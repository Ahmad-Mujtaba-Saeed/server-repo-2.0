<?php

// app/Helpers/CustomHelper.php

if (!function_exists('ReturnData')) {
    function ReturnData($boolean , $data , $message)
    {
        return response()->json(['success'=> $boolean , 'data' => $data , 'message' => $message]);
    }
}
