<?php
/**
 * Copyright (c) 2017 Chinaway ltd.
 *     Developed By Team-Link
 *
 * PHP Version 7.1
 *
 * @author chenhaibin <chenhaibin@huoyunren.com>
 * @since  2017/6/27
 */

namespace nic;


class restClient
{
    /**
     * @var string
     */
    protected $endPoint;
    /**
     * @var array
     */
    protected $httpOptions;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var object
     */
    protected $response;
    /**
     * @var array
     */
    protected $clientOptions;

    // -- End fields

    protected $responseCode;
    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
    /**
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param array $options 请求的客户端设置
     */
    public function __construct($options = array())
    {
        $defaultOptions = array(
            'baseUrl' => '',
            'requestFormat' => 'x-www-form',
            'responseFormat' => 'JSON'
        );
        $this->clientOptions = array_merge($defaultOptions, $options);
        $this->httpOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 10
        );
    }

    public function setClientOptions($options = array())
    {
        $this->clientOptions = array_merge($this->clientOptions , $options);

    }

    /**
     * @param string $endPoint
     * @throws \LogicException
     */
    public function setEndPoint($endPoint)
    {
        if (! empty($endPoint)) {
            $this->endPoint = $endPoint;
        }

        if (! isset($this->endPoint)) {
            throw new \LogicException("No endpoint was set.");
        }
    }

    /**
     * @param $options
     */
    public function setHttpOptions($options)
    {
        //$options = $this->setRestHeader($options);
        $this->httpOptions = $this->httpOptions + $options;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        if (! is_array($params)) {
            $params = array($params);
        }

        $this->params = $params;
    }

    /**
     * @param string $endPoint
     * @return object
     */
    public function get($endPoint = '')
    {
        $this->setEndPoint($endPoint);

        if (! empty($this->params)) {
            $this->endPoint .= '?' . http_build_query($this->params);
        }

        $this->sendRequest();

        return $this->response;
    }

    /**
     * @param string $endPoint
     * @return object
     */
    public function delete($endPoint = '')
    {
        $this->setEndPoint($endPoint);
        $this->setHttpOptions(array(CURLOPT_CUSTOMREQUEST => 'DELETE'));
        $this->sendRequest();

        return $this->response;
    }

    /**
     * @param string $endPoint
     * @return object
     */
    public function post($endPoint = '')
    {
        $this->setEndPoint($endPoint);

        $options = array(CURLOPT_POST => TRUE);

        if (! empty($this->params)) {
            if ($this->clientOptions['requestFormat'] == "x-www-form") {
                //$jsonString                  = json_encode($this->params);
                $options[CURLOPT_POSTFIELDS] = http_build_query($this->params);

            } else {
                $options[CURLOPT_POSTFIELDS] = $this->params;
                // $options[CURLOPT_HTTPHEADER] = array('Content-Type: multipart/form-data');
            }
        }

        $this->setHttpOptions($options);
        $this->sendRequest();

        return $this->response;
    }

    /**
     * @param string $endPoint
     * @return object
     */
    public function put($endPoint = '')
    {
        $this->setEndPoint($endPoint);

        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $options[CURLOPT_POSTFIELDS] = http_build_query($this->params);;

        $this->setHttpOptions($options);
        $this->sendRequest();

        return $this->response;
    }

    // -- End public methods

    /**
     * @throws \RuntimeException
     */
    protected function sendRequest()
    {
        $handle = curl_init($this->clientOptions['baseUrl'] . $this->endPoint);

        if (! curl_setopt_array($handle, $this->httpOptions)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException("Error setting cURL request options");
            // @codeCoverageIgnoreEnd
        }

        $this->response = curl_exec($handle);
        $this->validateResponse($handle);
        curl_close($handle);
    }

    /**
     * @param $handle
     * @throws \RuntimeException
     */
    protected function validateResponse($handle)
    {
        //if (! $this->response) {
        //    throw new \RuntimeException(curl_error($handle), - 1);
        //}

        $response_info = curl_getinfo($handle);
        $this->responseCode = $response_info['http_code'];
        if (curl_errno($handle))
            throw new \RuntimeException(curl_errno($handle));

        if (! in_array($this->responseCode, range(200, 207))) {
            throw new \RuntimeException($this->response, $this->responseCode);
        }
    }
}