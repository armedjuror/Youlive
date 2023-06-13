<?php
class Request {
	public string $method = 'GET';
	public array $params;
	public array $body = [];
    public array $query = [];
    public array $files = [];
	public mysqli $connection;
	public function __construct(string $method, array $params=array())
	{
		$this->method = strtoupper($method);
		$this->params = $params;
        $this->body = $_POST;
        $this->query = $_GET;
        $this->files = $_FILES;
        if (isset($_SERVER['CONTENT_TYPE']) && !in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])){
            $this::parse_raw_http_request();
        }
    }

    public static function make_request($method, $url, $data = false, $headers = false): bool|string
    {
        $curl = curl_init();
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        if ($headers)curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

//        // Optional Authentication:
//        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    public function verify_method(array $allowed_methods): array
    {
        if (!in_array($this->method, $allowed_methods)){
            return array(
                'status_code'=>0,
                'error_code'=>403,
                'error_desc'=>'Route with specified method not found!',
                'error_msg'=>'Oops, request is forbidden!'
            );
        }
        return array('status_code'=>1);
    }

    public function parse_raw_http_request(): void
    {
        // read incoming data
        $input = file_get_contents('php://input');

        if ($_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded'){
            parse_str($input, $this->body);
            return;
        }

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block)
        {
            if (empty($block))
                continue;

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE)
            {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);
            }
            // parse all other fields
            else
            {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $this->body[$matches[1]] = $matches[2]??null;
        }
    }
}