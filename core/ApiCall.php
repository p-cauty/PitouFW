<?php


namespace PitouFW\Core;


use stdClass;

class ApiCall {
    private string $method = 'GET';
    private ?string $url = null;
    private ?array $params = null;
    private ?array $custom_header = null;
    private bool $trust_any_ssl = false;
    private bool $json_content = false;
    private ?array $response_header = null;
    private ?string $response_body = null;

    private function parseResponseHeader(array $header): array {
        $headers = ['Status' => $header[0]];
        unset($header[0]);

        foreach ($header as $value) {
            $split = explode(': ', $value);
            $headers[$split[0]] = $split[1];
        }

        return $headers;
    }

    /**
     * @return ApiCall
     */
    public function exec(): ApiCall {
        if (function_exists('curl_version')) {
            $ch = curl_init($this->url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            switch ($this->method) {
                case 'GET':
                    break;
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    break;
                default:
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
            }

            if ($this->params !== null) {
                $postdata = $this->json_content ? json_encode($this->params) : http_build_query($this->params);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            }

            if ($this->params !== null) {
                curl_setopt($ch,
                    CURLOPT_HTTPHEADER,
                    $this->json_content ?
                        array_merge($this->custom_header, ['Content-Type: application/json']) :
                        $this->custom_header
                );
            }

            if ($this->trust_any_ssl) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }

            $result = curl_exec($ch);

            if ($result !== false) {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($result, 0, $header_size);
                $body = substr($result, $header_size);

                $header = str_replace("\r\n", "\n", trim($header));
                $header = str_replace("\r", "\n", $header);
                $header = explode("\n", $header);

                $this->response_header = $this->parseResponseHeader($header);
                $this->response_body = $body;
            }
        } else {
            $opts = ['http' => [
                'method' => $this->method,
                'ignore_errors' => true
            ]];
            
            if ($this->params !== null) {
                $postdata = $this->json_content ? json_encode($this->params) : http_build_query($this->params);
                $opts['http']['content'] = $postdata;
            }
            
            if ($this->custom_header !== null) {
                $opts['http']['header'] = ($this->json_content ? 'Content-Type: application/json' . "\n" : '') .
                    implode("\n", $this->custom_header);
            }
            
            $context = stream_context_create($opts);
            $result = file_get_contents($this->url, false, $context);

            if ($result !== false) {
                $this->response_header = $this->parseResponseHeader($http_response_header);
                $this->response_body = $result;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * @param string $method
     * @return ApiCall
     */
    public function setMethod(string $method): ApiCall {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return ApiCall
     */
    public function setUrl(?string $url): ApiCall {
        $this->url = $url;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array {
        return $this->params;
    }

    /**
     * @param array|null $params
     * @return ApiCall
     */
    public function setParams(?array $params): ApiCall {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getCustomHeader(): ?array {
        return $this->custom_header;
    }

    /**
     * @param array|null $custom_headers
     * @return ApiCall
     */
    public function setCustomHeader(?array $custom_headers): ApiCall {
        $this->custom_header = $custom_headers;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTrustingAnySsl(): bool {
        return $this->trust_any_ssl;
    }

    /**
     * @return bool
     */
    public function isJsonContent(): bool {
        return $this->json_content;
    }

    /**
     * @return ApiCall
     */
    public function jsonContent(): ApiCall {
        $this->json_content = true;
        return $this;
    }

    /**
     * @return ApiCall
     */
    public function trustAnySsl(): ApiCall {
        $this->trust_any_ssl = true;
        return $this;
    }

    public function responseHeader(): array {
        return $this->response_header;
    }

    /**
     * @return string|null
     */
    public function responseText(): ?string {
        return $this->response_body;
    }

    /**
     * @return stdClass|null
     */
    public function responseJson(): ?stdClass {
        return json_decode($this->response_body);
    }
}