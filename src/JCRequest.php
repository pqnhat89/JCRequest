<?php
/**
 * Created by PhpStorm.
 * User: jaredchu
 * Date: 31/05/2017
 * Time: 09:40
 */

namespace JC;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JC\Enums\Method;
use Purl\Url;

class JCRequest implements iJCRequest
{
    public static function request($method, $url, $guzzleOptions)
    {
        $client = new Client();
        try {
            return new JCResponse($client->request($method, $url, static::combineParams($guzzleOptions)));
        } catch (RequestException $exception) {
            return new JCResponse($exception->getResponse());
        }
    }

    public static function get($url, $params = null, $headers = [], $options = [])
    {
        return static::request(Method::GET, is_array($params) ? static::combineUrl($url, $params) : $url, [
            'headers' => $headers
        ]);
    }

    public static function post($url, $params = null, $headers = [], $options = [])
    {
        return static::request(Method::POST, $url, [
            'headers' => $headers,
            'params' => $params
        ]);
    }

    public static function put($url, $params = null, $headers = [], $options = [])
    {
        return static::request(Method::PUT, $url, [
            'headers' => $headers,
            'params' => $params
        ]);
    }

    public static function patch($url, $params = null, $headers = [], $options = [])
    {
        return static::request(Method::PATCH, $url, [
            'headers' => $headers,
            'params' => $params
        ]);
    }

    public static function delete($url, $params = null, $headers = [], $options = [])
    {
        return static::request(Method::DELETE, $url, [
            'headers' => $headers,
            'params' => $params
        ]);
    }

    public static function head($url, $headers = [], $options = [])
    {
        return static::request(Method::HEAD, $url, [
            'headers' => $headers,
        ]);
    }

    /**
     * Manipulate the url & params for GET request
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    protected static function combineUrl($url, $params = [])
    {
        $urlObject = Url::parse($url);
        $queryData = array_merge($urlObject->query->getData(), $params);
        $urlObject->query->setData($queryData);

        return $urlObject->getUrl();
    }

    /**
     * @param array $guzzleOptions
     * @return array
     */
    protected static function combineParams($guzzleOptions)
    {
        if (isset($guzzleOptions['params'])) {
            $params = $guzzleOptions['params'];
            unset($guzzleOptions['params']);

            if (is_array($params)) {
                $guzzleOptions['form_params'] = $params;
            } elseif (is_string($params) && $jsonObject = self::jsonDecode($params)) {
                $guzzleOptions['json'] = $jsonObject;
            } else {
                $guzzleOptions['body'] = $params;
            }
        }

        return $guzzleOptions;
    }

    /**
     * @param $string
     * @return bool|mixed
     */
    private static function jsonDecode($string)
    {
        $jsonObject = json_decode($string);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $jsonObject;
        } else {
            return false;
        }
    }
}