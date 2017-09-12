<?php

class Request
{

    public $url = false;
    public $final_url = false;
    public $ch = null;
    public $cacert = 'https://curl.haxx.se/ca/cacert.pem';
    public $headers = false;
    public $last_options_to_send = false;
    public $using_cacert = 'No verefy ssl';
    private $ioptions = [
        'ssl_version' => 3,
        'headers' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36'
        ],
        'content_type' => 'text/html',
        'charset' => 'utf-8',
        'type' => 'get',
        'no_headers' => false,
        'check_ssl' => true,
    ];

    public function __construct($url = false, $params = [], $ssl = false, $options = [])
    {
        $this->build($url, $params, $ssl, $options);
    }

    public function build($url = false, $params = [], $ssl = false, $options = [])
    {

        if ($this->ch !== null) {
            curl_close($this->ch);
        }

        foreach ($options as $key => $option) {
            if (get_value_by_key($key, $this->ioptions) !== null) {
                $this->ioptions[$key] = $option;
            }
        }

        if (!function_exists('curl_init')) {
            $warn = Winged::push_warning(__CLASS__, "cURL extension not found on this server.", true);
            winged_error_handler("8", $warn["error_description"], __FILE__, "in class : " . __LINE__, $warn["real_backtrace"]);
            Winged::get_errors(__LINE__, __FILE__);
        } else {
            if ($url) {
                $this->url = $url;
                $this->ch = curl_init($this->url);
                curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($this->ch, CURLOPT_FILETIME, true);
                if ($ssl && get_value_by_key('check_ssl', $this->ioptions) === true) {
                    if (!file_exists('./cacert.pem')) {
                        ini_set('allow_url_fopen', 1);
                        $content = @file_get_contents($this->cacert);
                        $handle = fopen('./cacert.pem', 'w+');
                        fwrite($handle, $content);
                        fclose($handle);
                        curl_setopt($this->ch, CURLOPT_CAINFO, getcwd() . '/cacert.pem');
                        $this->using_cacert = 'Created and using.';
                    } else {
                        $this->using_cacert = 'Found and using.';
                        curl_setopt($this->ch, CURLOPT_CAINFO, getcwd() . '/cacert.pem');
                    }
                    curl_setopt($this->ch, CURLOPT_SSLVERSION, 3);
                }
                if (get_value_by_key('type', $this->ioptions) == 'post') {
                    if ($this->ioptions['content_type'] == 'application/json') {
                        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
                        if (get_value_by_key('json_option', $this->ioptions) != null) {
                            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params, $this->ioptions['json_option']));
                        } else {
                            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));
                        }
                        $this->final_url = $url;
                    } else {
                        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));
                        $this->final_url = $url;
                    }
                } else if (get_value_by_key('type', $this->ioptions) == 'get') {
                    if ($params && is_array($params) && !empty($params)) {
                        $this->final_url = $url . '?' . http_build_query($params);
                        curl_setopt($this->ch, CURLOPT_URL, $this->final_url);
                    } else {
                        $this->final_url = $url;
                        curl_setopt($this->ch, CURLOPT_URL, $url);
                    }
                }

                if (get_value_by_key('no_headers', $this->ioptions) !== null) {
                    if (get_value_by_key('no_headers', $this->ioptions) === false) {
                        $content_type = 'Content-type: ' . $this->ioptions['content_type'] . '; charset=' . $this->ioptions['charset'] . '';
                        $add = [$content_type];
                        if ($this->ioptions['content_type'] == 'aplication/json') {
                            if (get_value_by_key('json_option', $this->ioptions) != null) {
                                $add[] = 'Content-Length: ' . strlen(json_encode($params, $this->ioptions['json_option']));
                            } else {
                                $add[] = 'Content-Length: ' . strlen(json_encode($params));
                            }
                        }
                        $this->headers = array_merge($this->ioptions['headers'], $add);
                        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
                    } else {
                        $this->headers = ['cURL without headers'];
                        curl_setopt($this->ch, CURLOPT_HEADER, 0);
                    }
                }

                if (get_value_by_key('check_ssl', $this->ioptions) === true) {
                    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
                } else {
                    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
                }


            } else {
                Winged::push_warning(__CLASS__, "You can't make a resquest without a invalid ou empty URL", true);
            }
        }
        $this->last_options_to_send = $this->ioptions;
        return $this;
    }

    public function info(){
        return curl_getinfo($this->ch);
    }

    public function send()
    {
        return new Response($this->ch, $this);
    }

}