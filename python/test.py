from recaptchanonjs import RecaptchaNonJS;

if __name__ == '__main__':
    x = RecaptchaNonJS('your_public_key', 'your_private_key')
    x.verify('123', 'aaa', '127.0.0.1')
    print x.error_message