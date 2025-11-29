let time;
let message = document.getElementById("request-message");
let countdown = document.getElementById("countdown");
let button = document.getElementById("request-button");
let timerId;

function update(){
    if(time<=0){
        
        button.style.backgroundColor="white"
        message.innerHTML=""
        countdown.innerHTML=""
        clearInterval(timerId)
        button.disabled=false
        return;
    }
    time--
    countdown.innerHTML=time
}

function startTimer(){
    time=30;
    button.style.backgroundColor="rgba(194,181,173,255)"
    button.disabled=true
    message.innerHTML="you can re-send a message after:"
    countdown.innerHTML=time

    timerId=setInterval(update,1000)
}

// Check if form was successfully submitted when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Check if success indicator exists (set by PHP when send is successful)
    if(typeof sendSuccess !== 'undefined' && sendSuccess === true){
        startTimer();
    }
    
    // Also check if success message div exists
    var successIndicator = document.getElementById('success-indicator');
    // if(successIndicator && successIndicator.textContent.trim() !== ''){
    //     startTimer();
    // }
});

