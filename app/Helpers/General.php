<?php

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

if (! function_exists('collectRecursively')) {
    /**
     * Helps to convert array into collection with its nested arrays
     * https://gist.github.com/brunogaspar/154fb2f99a7f83003ef35fd4b5655935
     * @param array $data
     * @return Collection
     */
    function collectRecursively(array $data)
    {
        $c = collect($data);
        return $c->map(function ($value) {
            if (is_array($value) || is_object($value)) {
                return collectRecursively($value);
            }
            return $value;
        });
   }
}

if (! function_exists('array_to_object')) {
    /**
     * Helps to convert array into collection with its nested arrays
     * https://gist.github.com/brunogaspar/154fb2f99a7f83003ef35fd4b5655935
     * @param array $data
     * @return stdClass
     */
    function array_to_object(array $data)
    {
        $obj = new stdClass;
        foreach($data as $k => $v) {
            if(strlen($k)) {
                if(is_array($v)) {
                    $obj->{$k} = array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }
}

if (! function_exists('isMultiDimensionalArray')) {
    /**
     * Checks if an array is nested using first element
     * @param array $arr
     * @return bool
     */
    function isMultiDimensionalArray( array $arr )
    {
        return count($arr) && is_array( Arr::first( $arr ) );
    }
}

if (! function_exists('expectsMultiDimensionalArray')) {
    /**
     * Wrap array into nested using first element if not nested
     * @param array $arr
     * @return array|array[]
     */
    function expectsMultiDimensionalArray( array $arr )
    {
        return isMultiDimensionalArray( $arr ) ? $arr : [ $arr ];
    }
}

if (! function_exists('message')) {
    /**
     * converts a message to array with message name
     * @param null|string $data
     * @return array
     */
    function message($data = null)
    {
        return array("message"=>$data);
    }
}

if (! function_exists('success')) {
	/**
	 * converts a message to array with message name
	 * @param null|string $data
	 * @return array
	 */
	function success($data = null)
	{
		return array("success"=>$data);
	}
}

if (! function_exists('errorKeyMessage')) {
	/**
	 * converts a message to array with message name
	 * @param null|string $data
	 * @return array
	 */
	function errorKeyMessage($data = null)
	{
		return array("error"=>$data);
	}
}

if (! function_exists('uiAppUrl')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function uiAppUrl($path = '')
    {
        return Str::of( env( 'UI_APP_URL' ) )
                ->finish( '/' ) .
            Str::of( $path )->ltrim( "/" ) ;
    }
}

if (! function_exists('copilotAppUrl')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function copilotAppUrl($path = '')
    {
        return Str::of( env( 'COPILOT_APP_URL' ) )
                ->finish( '/' ) .
            Str::of( $path )->ltrim( "/" ) ;
    }
}

if (! function_exists('adminAppUrl')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function adminAppUrl($path = '')
    {
        return Str::of( env( 'ADMIN_APP_URL' ) )
                ->finish( '/' ) .
            Str::of( $path )->ltrim( "/" ) ;
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function app_path($path = '')
    {
        return app()->basePath( 'app/' . $path );
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (! function_exists('isAppInDebugMode')) {
    /**
     * Check if application is in Debug Mode
     * @return bool
     */
    function isAppInDebugMode()
    {
        return (bool) env('APP_DEBUG', false);
    }
}

if (! function_exists('is_true')) {
    /**
     * better than boolval for php
     * @param $val
     * @param bool $return_null
     * @return bool|mixed|null
     */
    function is_true($val, $return_null=false){
        $boolval = ( is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val );
        return ( $boolval===null && !$return_null ? false : $boolval );
    }
}

if (! function_exists('extractNumbers')) {
    /**
     * better than boolval for php
     * @param $val
     * @param bool $return_null
     * @return bool|mixed|null
     */
    function extractNumbers($val){
        if(!$val) return null;
        return (int) filter_var($val, FILTER_SANITIZE_NUMBER_INT);
    }
}

if (! function_exists('myAssetUrl')) {
    /**
     * Get the path using env set value
     *
     * @param  string  $path
     * @return string
     */
    function myAssetUrl($path = '')
    {
        return Str::of( env( 'APP_URL' ) )
                ->finish( '/' ) .
            Str::of( $path )->ltrim( "/" ) ;
    }
}



if (! function_exists('fromAppTimezoneToUserPreferredTimezone')) {
    /**
     *
     * @param Carbon $carbon
     * @param \App\ModelsExtended\User $user
     * @return Carbon
     */
    function fromAppTimezoneToUserPreferredTimezone(Carbon $carbon, \App\ModelsExtended\User $user):Carbon
    {
        return $carbon->fromAppTimezoneToUserPreferredTimezone($user);
    }
}

if (! function_exists('presentCollectionForDev')) {
    /**
     * @param Collection|\App\ModelsExtended\Interfaces\IDeveloperPresentationInterface[]| null $collection
     * @return null|Collection
     */
    function presentCollectionForDev($collection)
    {
        if(!$collection) return null;
        return $collection->map->presentForDev();
    }
}


if (! function_exists('getIpAddressOverlookProxy')) {
    /**
     * This will return the first real ip address found.
     * Works as well if it is behind load balancers
     *
     * @return string
     */
    function getIpAddressOverlookProxy(): string
    {
        // https://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
        // this works. HTTP_X_FORWARDED_FOR
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return server ip when no client ip found
    }
}


if (! function_exists('sendAlertMessageToSlack')) {
    /**
     * This function will be used to send alert message
     * to specific slack group or channel
     *
     * @return string eg "ok"
     * @param string channelName
     * @param string SlackHookUrl
     * @param string userName
     * @param string message
     */
    function sendAlertMessageToSlack($message, $channelName = "#lucia-dev" , $slackHookUrl = "https://hooks.slack.com/services/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", $userName = "lucia-staging-app"): string
    {

        $client = new Client();
        $headers = [
        'Content-type' => 'application/json'
        ];
        $body = array(
            "channel" => $channelName,
            "username" => $userName,
            "text" => $message,
        );
        // $body = '{
        // "channel": "#lucia-dev",
        // "username": "lucia-staging-app",
        // "text": "Staging App is down for maintenance and will be back in the next 6 minutes!"
        // }';
        $request =  new \GuzzleHttp\Psr7\Request('POST', $slackHookUrl, $headers, json_encode($body));
        $res = $client->sendAsync($request)->wait();
        return $res->getBody();
            }
}














