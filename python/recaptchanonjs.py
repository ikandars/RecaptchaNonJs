"""
 A "Do-It-Yourself" reCAPTCHA builder to displaying
 reCAPTCHA image without need of javascript or iframe
 html tag.
 :author Iskandar Soesman <k4ndar@yahoo.com>
 :link http://kandar.info/
 :license http://www.opensource.org/licenses/bsd-license.php
 
"""
import httplib, urllib, re, json

class RecaptchaNonJS:
    
    def __init__(self, public_key, private_key):
        
        self.__public_key   = public_key
        self.__private_key  = private_key
        self.__base_api_url = 'www.google.com'
        self.error_message  = None
    
    def get_challenge(self):
        
        """
        Get Challenge code from http://www.google.com/recaptcha/api/challenge?k=your_public_key
        Natively, its a javascript, but lets reverse it so we can convert the "RecaptchaState" javascript
        variable into a json string and change it into PHP array.
        
        :Returns dictionary
        
        """
        
        conn = httplib.HTTPConnection(self.__base_api_url)
        conn.request('GET', '/recaptcha/api/challenge?k=' + self.__public_key)
        read = conn.getresponse()
        response = read.read()
        conn.close()
        
        js = re.search(r"\{([^}]+)\}", response)
        js = '{' + js.group(1) + '}'
        js = js.replace("'", '"')
        js = js.replace("\n", '')
        js = js.replace(' ', '')
        js = re.sub('([{,])([^{:\s"]*):', lambda m: '%s"%s":'%(m.group(1),m.group(2)),js)
        js = json.loads(js)
        js['image'] = 'http://www.google.com/recaptcha/api/challenge?k=' + js['challenge']
        
        return js
     
    def verify(self, challenge, response, remoteip):
        
        """
        Verify user input against the http://www.google.com/recaptcha/api/verify service
        through HTTP POST method.
        
        :param string challenge The chalange code given by reCAPTCHA.
        :param string response A string type by user to verified.
        :param string remoteip Client remote IP.
        :return boolean
     
        """
        
        params = urllib.urlencode({'@privatekey':self.__private_key, '@remoteip':remoteip, '@challenge':challenge, '@response':response})
        headers = {"Content-type": "application/x-www-form-urlencoded",
                   "Accept": "text/plain"}
        conn = httplib.HTTPConnection("www.google.com")
        conn.request("POST", "/recaptcha/api/verify", params, headers)
        response = conn.getresponse()
        conn.close()
        
        response = response.read().rsplit("\n")
        
        if response[0] == 'true' :
            return True
        
        self.error_message = response[1]
