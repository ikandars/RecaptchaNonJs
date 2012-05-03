<?php
/**
 * A "Do-It-Yourself" reCAPTCHA builder to displaying
 * reCAPTCHA image without need of javascript or iframe
 * html tag.
 *
 * @author Iskandar Soesman <k4ndar@yahoo.com>
 * @link http://kandar.info/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

class RecaptchaNonJs {
    
    private
        $publicKey,
        $privateKey,
        $baseApiUrl = 'http://www.google.com/recaptcha/api/';
    
    public
        $errorMessage;
    
    public function __construct($publicKey, $privateKey){
        
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }
    
    /**
     * Get Challenge code from http://www.google.com/recaptcha/api/challenge?k=your_public_key
     * Natively, its a javascript, but lets reverse it so we can convert the "RecaptchaState" javascript
     * variable into a json string and change it into PHP array.
     * 
     * @return array
     */
    public function getChallenge(){
        
        $resourceURL    = $this->baseApiUrl.'challenge?k='.$this->publicKey;
        $response       = file_get_contents($resourceURL);
        
        preg_match( '/{([^}]+)}/', $response, $matches);
        
        $json = str_replace("'", '"', $matches[0]);
        $json = substr($json, strpos($json,'{')+1, strlen($json)); 
        $json = substr($json, 0, strrpos($json,'}')); 
        $json = '{'.preg_replace('/(^|,)([\\s\\t]*)([^:]*) (([\\s\\t]*)):(([\\s\\t]*))/s', '$1"$3"$4:', trim($json)).'}';
        $json = json_decode($json, true);
        $json['image'] = $this->baseApiUrl.'image?c='.$json['challenge'];
        
        return $json;
        
    }
    
    /**
     * Verify user input against the http://www.google.com/recaptcha/api/verify service
     * through HTTP POST method.
     *
     * @param string $challenge The chalange code given by reCAPTCHA.
     * @param string a string type by user to verified.
     * @return boolean
     */
    public function verify($challenge, $response){
        
        $resourceURL = $this->baseApiUrl.'verify';
        
        $data = array(
            'privatekey'    => $this->privateKey,
            'remoteip'      => $_SERVER['REMOTE_ADDR'],
            'challenge'     => $challenge,
            'response'      => $response,
        );
        
        $data = http_build_query($data);
        
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $data
            )
        );
        
        $context  = stream_context_create($opts);
        $response = file_get_contents($resourceURL, false, $context);
        
        $response = explode("\n", $response);
        
        if($response[0] == 'true')
            return true;
        
        $this->errorMessage = $response[1];
    }
}