<?php
namespace GoatCore\Http;

class Requests {

    protected $curlOptions = [];

    protected $content = null;

    protected $curlInfo = '';

    protected $curlError = 0;

    protected $curlErrorMessage = '';


    /**
    * Init
    * @param array $config
    * @return void
    */
    public function __construct($config = []) {

        $this->defaultCurlOptions();
    }


    /**
    * Property getter
    * @param void
    * @return array
    */
    public function response()
    {
        $curl                        = curl_init($url);
        curl_setopt_array($curl, $this->curlOptions);
        $this->content               = curl_exec($curl);
        $this->curlError             = curl_errno($curl);
        $this->curlErrorMessage      = curl_error($curl);
        $this->curlInfo              = curl_getinfo($curl);
        curl_close($curl);

        return [
            'content' => $this->content,
            'curlError' => $this->curlError,
            'curlErrorMessage' => $this->curlErrorMessage,
            'curlInfo' => $this->curlInfo,
        ];
    }


    /**
    * Http/s request
    * @param string $url
    * @return object
    */
    protected function request($url)
    {
        $curl                        = curl_init($url);
        curl_setopt_array($curl, $this->curlOptions);
        $this->content               = curl_exec($curl);
        $this->curlError             = curl_errno($curl);
        $this->curlErrorMessage      = curl_error($curl);
        $this->curlInfo              = curl_getinfo($curl);
        curl_close($curl);

        return $this;
    }


    /**
    * Http/s GET request
    * @param string $url
    * @return array
    */
    public function get($url)
    {
        $this->curlOptions[CURLOPT_POST] = 0;
        unset($this->curlOptions[CURLOPT_POSTFIELDS]);

        return $this->request($url);
    }


    /**
    * Http/s POST request
    * @param string $url
    * @param array $data
    * @return array
    */
    public function post($url, $data = [])
    {
        if (ark($config, 'CURLOPT_POST', 0)==1) {
            $this->curlOptions[CURLOPT_POST] = 1;
            $this->curlOptions[CURLOPT_POSTFIELDS] = is_array(ark($this->curlOptions, 'CURLOPT_POSTFIELDS', '')) ? http_build_query($this->curlOptions['CURLOPT_POSTFIELDS']): $this->curlOptions['CURLOPT_POSTFIELDS'];
        }

        return $this->request($url);
    }


    /**
    * Downloads file data from specified path to local file
    * @param string $pathFrom
    * @param string $pathTo
    * @return void
    */
    public function downloadFile($pathFrom, $pathTo)
    {
        set_time_limit(0);

        //This is the file where we save the information
        $handler = fopen ($pathTo, 'w+');

        $this->curlOptions = [
            CURLOPT_TIMEOUT => 50,
            CURLOPT_FILE => $handler,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ];

        //Here is the file we are downloading, replace spaces with %20
        $curl = curl_init(str_replace(" ","%20", $pathFrom));
        curl_setopt_array($curl, $this->curlOptions);

        // get curl response
        $this->content               = curl_exec($curl);
        $this->curlError             = curl_errno($curl);
        $this->curlErrorMessage      = curl_error($curl);
        $this->curlInfo              = curl_getinfo($curl);

        curl_close($curl);
        fclose($handler);

        $this->defaultCurlOptions();

        return $this;
    }


    /**
    * Most usefull curl options for common requests GET/POST etc...
    * @param void
    * @return array
    */
    protected function defaultCurlOptions()
    {
        $this->curlOptions = [
            CURLOPT_RETURNTRANSFER => ark($config, 'CURLOPT_RETURNTRANSFER', true),                 // return content, no direct output
            CURLOPT_HEADER         => ark($config, 'CURLOPT_HEADER', false),                        // don't return headers
            CURLOPT_HTTPHEADER     => ark($config, 'CURLOPT_HTTPHEADER', []),                       // HTTP headers
            CURLINFO_HEADER_OUT    => ark($config, 'CURLINFO_HEADER_OUT', true),                    // Track the handle's request string
            CURLOPT_FOLLOWLOCATION => ark($config, 'CURLOPT_FOLLOWLOCATION', true),                 // follow redirects
            CURLOPT_ENCODING       => ark($config, 'CURLOPT_ENCODING', ''),                         // handle all encodings
            CURLOPT_USERAGENT      => ark($config, 'CURLOPT_USERAGENT', 'GoatCore\Http\Requests'),    // who am i
            CURLOPT_AUTOREFERER    => ark($config, 'CURLOPT_AUTOREFERER', true),                    // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => ark($config, 'CURLOPT_CONNECTTIMEOUT', 30),                   // timeout on connect
            CURLOPT_TIMEOUT        => ark($config, 'CURLOPT_TIMEOUT', 60),                          // timeout on response
            CURLOPT_MAXREDIRS      => ark($config, 'CURLOPT_MAXREDIRS', 10),                        // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => ark($config, 'CURLOPT_SSL_VERIFYPEER', false),                // Disabled SSL Cert checks
        ];

        return $this->defaultCurlOptions;
    }
}