let captchaMainWrapper = document.querySelector('.wur-g-captcha');

if (captchaMainWrapper.classList.contains('recaptcha-v3')) {
    
    let siteKey = document.getElementById('recaptcha_site_key_v3').getAttribute("data-sitekey");
   
    grecaptcha.ready(function() {
        grecaptcha.execute(siteKey, { action: 'submit' }).then(function(token) {
            document.getElementById('g-recaptcha-response-v3').value = token;
        });
    });
    
}else{
    function recaptchaCallback() {
        document.getElementById('recaptcha-completed').value = 'true';
    }
    
    document.getElementById('xs_review_form_public_data').addEventListener('submit', function(event) {
        let recaptchaCompleted = document.getElementById('recaptcha-completed').value;
    
        if (recaptchaCompleted !== 'true') {
            event.preventDefault();
            document.querySelector('.g-recaptcha-required-message').style.display = 'block';
        }else{
            document.querySelector('.g-recaptcha-required-message').style.display = 'none';
        }
    });
}
