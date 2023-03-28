<?php

class User_Input {
    public static function filtered_input($input, $type='', $length=0, $name='', $required=false): array
    {
        $input = trim($input);
        $input = addslashes($input);
        $input = htmlspecialchars($input);

        if ($input){
            if ($length){
                if (strlen($input) > $length){
                    return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>$name." is so long", 'error_msg'=>'Oops, '.$name.' is so long!');
                }
            }

            if ($type == 'email'){
                if (!filter_var($input, FILTER_VALIDATE_EMAIL)){
                    return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid Email", 'error_msg'=>'Oops, Email does not seems right!');
                }
            }
            elseif ($type == 'number'){
                if (!is_numeric($input)){
                    return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid Input", 'error_msg'=>'Oops, Some input seems invalid!');
                }
            }
            elseif ($type == 'mobile'){
                $without_plus = explode('+', $input);
                if (!is_numeric($without_plus[1]??$input) && $without_plus[0] != '+'){
                    return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid Input", 'error_msg'=>'Oops, Some Mobile Number seems invalid!');
                }
            }
            elseif ($type == 'url'){
                if (!filter_var($input, FILTER_VALIDATE_URL)){
                    return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid URL", 'error_msg'=>'Oops, URL does not seems right!');
                }
                $input = urlencode($input);
            }
            elseif ($type == 'ip'){
                if (!filter_var($input, FILTER_VALIDATE_IP)){
                    return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid IP", 'error_msg'=>'Oops, IP does not seems right!');
                }
            }
            elseif ($type == 'lower'){
                if (!ctype_lower($input)){
                    return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Only lowercase alphabetic characters allowed", 'error_msg'=>'Oops, Only lowercase alphabetic characters are permitted!');
                }
            }
            elseif ($type == 'json'){
                json_decode($input);
                $not_json = (json_last_error() === JSON_ERROR_NONE);
                if($not_json)return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Input should be a JSON", 'error_msg'=>'Oops, input should be JSON!');
                $input = json_decode($input);
            }
            else{
                if ($length){
                    if (strlen($input) > $length){
                        if ($name){
                            $error_msg = "Oops, ".ucfirst($name).' input is so long! Max Character Limit : '.$length;
                        }else{
                            $error_msg = 'Oops, Some Input is so long!';
                        }
                        return array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Input is so long", 'error_msg'=>$error_msg);
                    }else{
                        return array('status_code'=>1, 'result'=>$input);
                    }
                }
            }
        }
		else{
			if ($required){
				return Standard::response(
					0,
					'PLE_USR',
					$name.' is required.',
					'Oops, '.$name.' is required.',
					'',
					true,
					array(),
					false
				);
			}
		}
        return array('status_code'=>1, 'result'=>$input);
    }

    public static function check_file($file, $allowed_extensions, $max_size, $size_error): array
    {
        $result = array('error_code'=>'');
        if ($file['error'] != 4) {
            $array = explode(".", strtolower($file["name"]));
            $extension = end($array);
            if (!in_array($extension, $allowed_extensions)) {
//            ERR: ex_err_030001
                $result['error_msg'] = 'Unsupported file format!';
                $result['error_code'] = 'ex_err_030001';
            }
            if ($file['size'] > $max_size) {
//            ERR: ex_err_030002
                $result['error_msg'] = $size_error;
                $result['error_code'] = 'ex_err_030002';
            }
            if (!$result['error_code']) {
                $file_og = addslashes(file_get_contents($file['tmp_name']));
                return array('status_code'=>1, 'file'=>$file_og);
            }else{
                $result['status_code'] = 0;
                return $result;
            }
        }else {
            return array('status_code'=>1, 'file'=>'');
        }
    }

    public static function filter_input($input, string $name, array $config = array()): mixed
    {
        $length = $config['length'] ?? 0;
        $type = $config['type'] ?? '';
        $required = $config['required'] ?? 0;
        $success = $config['success'] ?? 'input'; // input|return|json
        $error = $config['error'] ?? 'json_exit'; // json_exit|json|return
        $return = null;

        $input = trim($input);
        $input = addslashes($input);
        $input = htmlspecialchars($input);

        if ($input){
            if ($length){
                if (strlen($input) > $length){
                    $return = array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>ucfirst($name)." is so long", 'error_msg'=>'Oops, '.ucfirst($name).' is so long!');
                }
            }

            if ($type == 'email'){
                if (!filter_var($input, FILTER_VALIDATE_EMAIL)){
                    $return = array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid Email", 'error_msg'=>'Oops, Email does not seems right!');
                }
            }
            elseif ($type == 'number'){
                if (!is_numeric($input)){
                    $return = array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid Input", 'error_msg'=>'Oops, Some input seems invalid!');
                }
            }
            elseif ($type == 'mobile'){
                $without_plus = explode('+', $input);
                if (!is_numeric($without_plus[1]??$input) && $without_plus[0] != '+'){
                    $return = array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid Input", 'error_msg'=>'Oops, Some Mobile Number seems invalid!');
                }
            }
            elseif ($type == 'url'){
                if (!filter_var($input, FILTER_VALIDATE_URL)){
                    $return = array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid URL", 'error_msg'=>'Oops, URL does not seems right!');
                }
                $input = urlencode($input);
            }
            elseif ($type == 'ip'){
                if (!filter_var($input, FILTER_VALIDATE_IP)){
                    $return = array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Invalid IP", 'error_msg'=>'Oops, IP does not seems right!');
                }
            }
            elseif ($type == 'lower'){
                if (!ctype_lower($input)){
                    $return = array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Only lowercase alphabetic characters allowed", 'error_msg'=>'Oops, Only lowercase alphabetic characters are permitted!');
                }
            }
            elseif ($type == 'json'){
                json_decode($input);
                $not_json = (json_last_error() === JSON_ERROR_NONE);
                if($not_json)$return = array('status_code'=>0, 'error_code'=>'PLE_USR', 'error_desc'=>"Input should be a JSON", 'error_msg'=>'Oops, input should be JSON!');
                $input = json_decode($input);
            }

        }
        else{
            if ($required){
                $return = Standard::response(
                    0,
                    'PLE_USR',
                    $name.' is required.',
                    'Oops, '.$name.' is required.',
                    '',
                    true,
                    array(),
                    false
                );
            }
        }

        // Success No Error
        if ($return == null){
            if ($success == 'input'){
                return $input;
            }elseif ($success == 'return'){
                return array('status_code'=>1, 'result'=>$input);
            }else{
                echo json_encode(array('status_code'=>1, 'result'=>$input));
                return true;
            }
        }else{
            if ($error == 'json_exit'){
                echo json_encode($return);
                exit();
            }elseif ($error == 'json'){
                echo json_encode($return);
                return false;
            }else{
                return $return;
            }
        }
    }

}